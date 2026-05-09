<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Services\RouteOptimizationService;
use Illuminate\Http\JsonResponse;

class RouteController extends Controller
{
    public function __construct(private RouteOptimizationService $routeService) {}

    public function show(DeliveryOrder $deliveryOrder): JsonResponse
    {
        $route = $deliveryOrder->route;

        if (!$route) {
            return response()->json(['success' => false, 'message' => 'No route found for this order.'], 404);
        }

        return response()->json(['success' => true, 'data' => ['route' => $route, 'stops' => $deliveryOrder->stops]]);
    }

    public function reOptimize(DeliveryOrder $deliveryOrder): JsonResponse
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        if (!in_array($deliveryOrder->status, ['assigned', 'in_transit'])) {
            return response()->json(['success' => false, 'message' => 'Route can only be re-optimized for assigned or in-transit orders.'], 422);
        }

        $route = $this->routeService->optimizeRoute($deliveryOrder);
        return response()->json(['success' => true, 'message' => 'Route re-optimized successfully.', 'data' => $route]);
    }

    public function eta(DeliveryOrder $deliveryOrder): JsonResponse
    {
        $driver = $deliveryOrder->driver;

        if (!$driver || !$driver->current_lat) {
            return response()->json(['success' => false, 'message' => 'Driver location not available.'], 422);
        }

        $eta = $this->routeService->calculateETA(
            $driver->current_lat, $driver->current_lng,
            $deliveryOrder->delivery_lat, $deliveryOrder->delivery_lng
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'estimated_arrival' => $eta['arrival_time'],
                'remaining_minutes' => $eta['duration_minutes'],
                'remaining_km'      => $eta['distance_km'],
                'driver_location'   => ['lat' => $driver->current_lat, 'lng' => $driver->current_lng],
            ],
        ]);
    }
}
