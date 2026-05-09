<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\DriverPerformance;
use App\Services\PerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private PerformanceService $performanceService) {}

    public function dashboard(): JsonResponse
    {
        $this->adminOnly();

        $today = now()->toDateString();

        $summary = [
            'orders' => [
                'total'      => DeliveryOrder::count(),
                'today'      => DeliveryOrder::whereDate('created_at', $today)->count(),
                'pending'    => DeliveryOrder::where('status', 'pending')->count(),
                'in_transit' => DeliveryOrder::where('status', 'in_transit')->count(),
                'delivered'  => DeliveryOrder::where('status', 'delivered')->count(),
                'failed'     => DeliveryOrder::where('status', 'failed')->count(),
            ],
            'drivers' => [
                'total'       => Driver::count(),
                'available'   => Driver::where('status', 'available')->count(),
                'on_delivery' => Driver::where('status', 'on_delivery')->count(),
                'offline'     => Driver::where('status', 'offline')->count(),
            ],
            'kpis' => [
                'avg_delivery_time' => DeliveryOrder::whereNotNull('actual_duration')->avg('actual_duration'),
                'on_time_rate'      => $this->performanceService->globalOnTimeRate(),
                'today_completed'   => DeliveryOrder::whereDate('delivered_at', $today)->count(),
                'delayed_orders'    => DeliveryOrder::where('status', 'in_transit')
                    ->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '<', now())
                    ->count(),
            ],
        ];

        return response()->json(['success' => true, 'data' => $summary]);
    }

    public function ordersReport(Request $request): JsonResponse
    {
        $this->adminOnly();

        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'driver_id' => 'nullable|exists:drivers,id',
            'status'    => 'nullable|in:pending,assigned,in_transit,delivered,failed,cancelled',
        ]);

        $query = DeliveryOrder::with(['driver.user', 'creator'])
            ->whereBetween('created_at', [$request->date_from, $request->date_to . ' 23:59:59']);

        if ($request->driver_id) $query->where('driver_id', $request->driver_id);
        if ($request->status)    $query->where('status', $request->status);

        $orders = $query->get();

        $stats = [
            'total'             => $orders->count(),
            'delivered'         => $orders->where('status', 'delivered')->count(),
            'failed'            => $orders->where('status', 'failed')->count(),
            'avg_delivery_time' => $orders->whereNotNull('actual_duration')->avg('actual_duration'),
            'on_time_rate'      => $this->performanceService->onTimeRateFromCollection($orders),
            'total_distance_km' => $orders->sum('distance_km'),
        ];

        return response()->json(['success' => true, 'data' => ['stats' => $stats, 'orders' => $orders]]);
    }

    public function driverPerformance(Request $request, Driver $driver): JsonResponse
    {
        $user = auth()->user();

        if ($user->role === 'driver' && $user->driver?->id !== $driver->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate(['date_from' => 'nullable|date', 'date_to' => 'nullable|date']);

        $dateFrom = $request->date_from ?? now()->subDays(30)->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();

        $reports = DriverPerformance::where('driver_id', $driver->id)
            ->whereBetween('period_date', [$dateFrom, $dateTo])
            ->orderBy('period_date')
            ->get();

        $orders = DeliveryOrder::where('driver_id', $driver->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->get();

        $summary = [
            'total_orders'      => $orders->count(),
            'completed'         => $orders->where('status', 'delivered')->count(),
            'failed'            => $orders->where('status', 'failed')->count(),
            'avg_delivery_time' => $orders->whereNotNull('actual_duration')->avg('actual_duration'),
            'on_time_rate'      => $this->performanceService->onTimeRateFromCollection($orders),
            'total_distance_km' => $orders->sum('distance_km'),
        ];

        return response()->json([
            'success' => true,
            'data'    => ['driver' => $driver->load('user'), 'summary' => $summary, 'daily' => $reports],
        ]);
    }

    public function generateDailyPerformance(Request $request): JsonResponse
    {
        $this->adminOnly();

        $date = $request->date ?? now()->toDateString();
        $this->performanceService->generateDailyReport($date);

        return response()->json(['success' => true, 'message' => "Performance report generated for {$date}."]);
    }

    private function adminOnly(): void
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin' || $user->hasRole('admin');
        abort_if(! $isAdmin, 403, 'Admin access required.');
    }
}
