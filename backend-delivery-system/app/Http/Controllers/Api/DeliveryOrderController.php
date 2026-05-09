<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Models\DeliveryOrder;
use App\Models\OrderStatusHistory;
use App\Services\RouteOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryOrderController extends Controller
{
    public function __construct(private RouteOptimizationService $routeService) {}

    public function index(Request $request): JsonResponse
    {
        $user  = auth('api')->user();
        $query = DeliveryOrder::with(['driver.user', 'creator', 'stops', 'route']);

        // Drivers only see their own orders
        if ($user->role === 'driver' || $user->hasRole('driver')) {
            $query->where('driver_id', $user->driver?->id);
        }

        if ($request->status)    $query->where('status', $request->status);
        if ($request->driver_id) $query->where('driver_id', $request->driver_id);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to)   $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->priority)  $query->where('priority', $request->priority);

        return response()->json([
            'success' => true,
            'data'    => $query->latest()->paginate($request->per_page ?? 15),
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $this->adminOnly();

        $order = DeliveryOrder::create([
            ...$request->validated(),
            'created_by' => auth('api')->id(),
            'status'     => 'pending',
        ]);

        $order->stops()->createMany([
            ['sequence' => 1, 'address' => $order->pickup_address,   'lat' => $order->pickup_lat,   'lng' => $order->pickup_lng,   'type' => 'pickup',   'status' => 'pending'],
            ['sequence' => 2, 'address' => $order->delivery_address, 'lat' => $order->delivery_lat, 'lng' => $order->delivery_lng, 'type' => 'delivery', 'status' => 'pending'],
        ]);

        OrderStatusHistory::create([
            'delivery_order_id' => $order->id,
            'changed_by'        => auth('api')->id(),
            'from_status'       => null,
            'to_status'         => 'pending',
            'notes'             => 'Order created',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data'    => $order->load(['stops', 'creator']),
        ], 201);
    }

    public function show(DeliveryOrder $deliveryOrder): JsonResponse
    {
        $this->authorizeOrderAccess($deliveryOrder);
        $deliveryOrder->load(['driver.user', 'creator', 'stops', 'route', 'statusHistory.changedBy']);

        return response()->json(['success' => true, 'data' => $deliveryOrder]);
    }

    public function update(UpdateOrderRequest $request, DeliveryOrder $deliveryOrder): JsonResponse
    {
        $this->adminOnly();

        if (in_array($deliveryOrder->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update a completed or cancelled order.',
            ], 422);
        }

        $deliveryOrder->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'data'    => $deliveryOrder->fresh(['stops', 'driver']),
        ]);
    }

    public function assignDriver(Request $request, DeliveryOrder $deliveryOrder): JsonResponse
    {
        $this->adminOnly();
        $request->validate(['driver_id' => 'required|exists:drivers,id']);

        if ($deliveryOrder->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be assigned.',
            ], 422);
        }

        $oldStatus = $deliveryOrder->status;
        $deliveryOrder->update([
            'driver_id'   => $request->driver_id,
            'status'      => 'assigned',
            'assigned_at' => now(),
        ]);

        $route = $this->routeService->optimizeRoute($deliveryOrder);

        OrderStatusHistory::create([
            'delivery_order_id' => $deliveryOrder->id,
            'changed_by'        => auth('api')->id(),
            'from_status'       => $oldStatus,
            'to_status'         => 'assigned',
            'notes'             => 'Driver assigned. Route optimized.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Driver assigned and route optimized.',
            'data'    => [
                'order' => $deliveryOrder->fresh(['driver.user', 'route']),
                'route' => $route,
            ],
        ]);
    }

    public function updateStatus(Request $request, DeliveryOrder $deliveryOrder): JsonResponse
    {
        $this->authorizeOrderAccess($deliveryOrder);

        $request->validate([
            'status'       => 'required|in:in_transit,delivered,failed,cancelled',
            'notes'        => 'nullable|string',
            'lat'          => 'nullable|numeric',
            'lng'          => 'nullable|numeric',
            'delay_reason' => 'nullable|string',
        ]);

        $allowedTransitions = [
            'assigned'   => ['in_transit', 'cancelled'],
            'in_transit' => ['delivered', 'failed'],
            'pending'    => ['cancelled'],
        ];

        $allowed = $allowedTransitions[$deliveryOrder->status] ?? [];
        if (! in_array($request->status, $allowed)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot transition from '{$deliveryOrder->status}' to '{$request->status}'.",
            ], 422);
        }

        $oldStatus = $deliveryOrder->status;
        $updates   = ['status' => $request->status];

        if ($request->status === 'in_transit') {
            $updates['pickup_at'] = now();
        }
        if ($request->status === 'delivered') {
            $updates['delivered_at']    = now();
            $updates['actual_duration'] = $deliveryOrder->calculateActualDuration();
        }
        if ($request->delay_reason) {
            $updates['delay_reason'] = $request->delay_reason;
        }

        $deliveryOrder->update($updates);

        OrderStatusHistory::create([
            'delivery_order_id' => $deliveryOrder->id,
            'changed_by'        => auth('api')->id(),
            'from_status'       => $oldStatus,
            'to_status'         => $request->status,
            'notes'             => $request->notes,
            'lat'               => $request->lat,
            'lng'               => $request->lng,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated.',
            'data'    => $deliveryOrder->fresh(),
        ]);
    }

    public function cancel(Request $request, DeliveryOrder $deliveryOrder): JsonResponse
    {
        $this->adminOnly();

        if (in_array($deliveryOrder->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order is already completed or cancelled.',
            ], 422);
        }

        $oldStatus = $deliveryOrder->status;
        $deliveryOrder->update(['status' => 'cancelled']);

        OrderStatusHistory::create([
            'delivery_order_id' => $deliveryOrder->id,
            'changed_by'        => auth('api')->id(),
            'from_status'       => $oldStatus,
            'to_status'         => 'cancelled',
            'notes'             => $request->reason ?? 'Order cancelled by admin.',
        ]);

        return response()->json(['success' => true, 'message' => 'Order cancelled.']);
    }

    // -------------------------------------------------------------------------

    private function authorizeOrderAccess(DeliveryOrder $order): void
    {
        $user = auth('api')->user();
        if ($user->role === 'driver' || $user->hasRole('driver')) {
            abort_if(
                $order->driver_id !== $user->driver?->id,
                403,
                'Unauthorized access to this order.'
            );
        }
    }

    private function adminOnly(): void
    {
        $user    = auth('api')->user();
        $isAdmin = $user->role === 'admin' || $user->hasRole('admin');
        abort_if(! $isAdmin, 403, 'Admin access required.');
    }
}
