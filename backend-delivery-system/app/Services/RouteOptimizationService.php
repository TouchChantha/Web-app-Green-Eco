<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\DeliveryRoute;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteOptimizationService
{
    /**
     * Create or update the optimized route for a delivery order.
     * Uses Google Maps Directions API when a key is available,
     * falls back to Haversine straight-line calculation.
     */
    public function optimizeRoute(DeliveryOrder $order): DeliveryRoute
    {
        // Only call Google Maps if we have valid coordinates
        $hasCoords = $order->pickup_lat && $order->pickup_lng
                  && $order->delivery_lat && $order->delivery_lng;

        if ($hasCoords && $this->hasGoogleKey()) {
            $result = $this->fetchGoogleDirections(
                $order->pickup_lat,  $order->pickup_lng,
                $order->delivery_lat, $order->delivery_lng
            );

            if ($result) {
                return $this->saveRoute($order, $result);
            }
        }

        // Fallback: Haversine straight-line
        return $this->saveRouteFallback($order);
    }

    /**
     * Calculate ETA from driver's current position to delivery point.
     * Uses Google Maps Directions API when available.
     */
    public function calculateETA(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        if ($this->hasGoogleKey()) {
            $result = $this->fetchGoogleDirections($fromLat, $fromLng, $toLat, $toLng);
            if ($result) {
                return [
                    'arrival_time'     => now()->addMinutes($result['duration_minutes'])->toIso8601String(),
                    'duration_minutes' => $result['duration_minutes'],
                    'distance_km'      => $result['distance_km'],
                ];
            }
        }

        // Fallback
        $distanceKm      = $this->haversineDistance($fromLat, $fromLng, $toLat, $toLng);
        $durationMinutes = $distanceKm > 0 ? (int) round(($distanceKm / 40) * 60) : 5;

        return [
            'arrival_time'     => now()->addMinutes($durationMinutes)->toIso8601String(),
            'duration_minutes' => $durationMinutes,
            'distance_km'      => round($distanceKm, 2),
        ];
    }

    // ─── Private: Google Maps ─────────────────────────────────────────────────

    private function hasGoogleKey(): bool
    {
        $key = config('services.google_maps.key');
        return ! empty($key);
    }

    /**
     * Call Google Maps Directions API and return normalized result.
     * Returns null on failure so caller can fall back.
     */
    private function fetchGoogleDirections(
        float $originLat, float $originLng,
        float $destLat,   float $destLng
    ): ?array {
        $key = config('services.google_maps.key');

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/directions/json', [
                'origin'      => "{$originLat},{$originLng}",
                'destination' => "{$destLat},{$destLng}",
                'mode'        => 'driving',
                'key'         => $key,
                'language'    => 'en',
                'region'      => 'KH',
            ]);

            if (! $response->successful()) {
                Log::warning('[RouteOpt] Google Directions HTTP error', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'OK' || empty($data['routes'])) {
                Log::warning('[RouteOpt] Google Directions API error', ['status' => $data['status'] ?? 'unknown']);
                return null;
            }

            $leg      = $data['routes'][0]['legs'][0];
            $polyline = $data['routes'][0]['overview_polyline']['points'] ?? null;

            $distanceKm      = round(($leg['distance']['value'] ?? 0) / 1000, 2);
            $durationMinutes = (int) round(($leg['duration']['value'] ?? 0) / 60);

            // Build waypoints from steps for the route display
            $waypoints = [
                ['lat' => $originLat, 'lng' => $originLng, 'type' => 'pickup'],
            ];
            foreach ($data['routes'][0]['legs'][0]['steps'] ?? [] as $step) {
                $waypoints[] = [
                    'lat'  => $step['end_location']['lat'],
                    'lng'  => $step['end_location']['lng'],
                    'type' => 'waypoint',
                ];
            }
            $waypoints[] = ['lat' => $destLat, 'lng' => $destLng, 'type' => 'delivery'];

            return [
                'polyline'         => $polyline,
                'waypoints'        => $waypoints,
                'distance_km'      => $distanceKm,
                'duration_minutes' => $durationMinutes,
                'google_route_id'  => $data['routes'][0]['summary'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('[RouteOpt] Google Directions exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function saveRoute(DeliveryOrder $order, array $result): DeliveryRoute
    {
        $route = DeliveryRoute::updateOrCreate(
            ['delivery_order_id' => $order->id],
            [
                'polyline'           => $result['polyline'],
                'waypoints'          => $result['waypoints'],
                'total_distance_km'  => $result['distance_km'],
                'estimated_duration' => $result['duration_minutes'],
                'optimized'          => true,
                'google_route_id'    => $result['google_route_id'],
            ]
        );

        $order->update([
            'distance_km'        => $result['distance_km'],
            'estimated_duration' => $result['duration_minutes'],
        ]);

        Log::info('[RouteOpt] Google route saved', [
            'order_id'    => $order->id,
            'distance_km' => $result['distance_km'],
            'duration'    => $result['duration_minutes'],
        ]);

        return $route;
    }

    // ─── Private: Haversine fallback ──────────────────────────────────────────

    private function saveRouteFallback(DeliveryOrder $order): DeliveryRoute
    {
        $distanceKm = $this->haversineDistance(
            $order->pickup_lat   ?? 0, $order->pickup_lng   ?? 0,
            $order->delivery_lat ?? 0, $order->delivery_lng ?? 0
        );

        // Urban average 40 km/h, minimum 5 min
        $estimatedMinutes = $distanceKm > 0
            ? max(5, (int) round(($distanceKm / 40) * 60))
            : 30;

        $waypoints = [
            ['lat' => $order->pickup_lat,   'lng' => $order->pickup_lng,   'type' => 'pickup'],
            ['lat' => $order->delivery_lat, 'lng' => $order->delivery_lng, 'type' => 'delivery'],
        ];

        $route = DeliveryRoute::updateOrCreate(
            ['delivery_order_id' => $order->id],
            [
                'polyline'           => null,
                'waypoints'          => $waypoints,
                'total_distance_km'  => round($distanceKm, 2),
                'estimated_duration' => $estimatedMinutes,
                'optimized'          => true,
                'google_route_id'    => null,
            ]
        );

        $order->update([
            'distance_km'        => round($distanceKm, 2),
            'estimated_duration' => $estimatedMinutes,
        ]);

        return $route;
    }

    /**
     * Haversine formula — straight-line distance between two GPS coordinates (km).
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        if ($lat1 === 0.0 && $lng1 === 0.0 && $lat2 === 0.0 && $lng2 === 0.0) {
            return 0.0;
        }

        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
