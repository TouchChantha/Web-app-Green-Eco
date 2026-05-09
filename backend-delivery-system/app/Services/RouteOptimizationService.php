<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\DeliveryRoute;

class RouteOptimizationService
{
    /**
     * Create or update the route for a delivery order.
     * Uses a simple straight-line distance calculation as a fallback
     * when no Google Maps API key is configured.
     */
    public function optimizeRoute(DeliveryOrder $order): DeliveryRoute
    {
        $distanceKm  = $this->haversineDistance(
            $order->pickup_lat  ?? 0,
            $order->pickup_lng  ?? 0,
            $order->delivery_lat ?? 0,
            $order->delivery_lng ?? 0
        );

        // Assume average speed of 40 km/h in urban areas
        $estimatedMinutes = $distanceKm > 0
            ? (int) round(($distanceKm / 40) * 60)
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

        // Also update the order's distance and estimated duration
        $order->update([
            'distance_km'        => round($distanceKm, 2),
            'estimated_duration' => $estimatedMinutes,
        ]);

        return $route;
    }

    /**
     * Calculate a simple ETA based on straight-line distance.
     */
    public function calculateETA(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $distanceKm = $this->haversineDistance($fromLat, $fromLng, $toLat, $toLng);
        $durationMinutes = $distanceKm > 0 ? (int) round(($distanceKm / 40) * 60) : 5;

        return [
            'arrival_time'     => now()->addMinutes($durationMinutes)->toIso8601String(),
            'duration_minutes' => $durationMinutes,
            'distance_km'      => round($distanceKm, 2),
        ];
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * Haversine formula — straight-line distance between two GPS coordinates (km).
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        if ($lat1 === 0.0 && $lng1 === 0.0 && $lat2 === 0.0 && $lng2 === 0.0) {
            return 0.0;
        }

        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
