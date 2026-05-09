<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\DriverPerformance;
use Illuminate\Support\Collection;

class PerformanceService
{
    /**
     * Calculate the global on-time delivery rate (all drivers, all time).
     * An order is "on time" if it was delivered before or at its scheduled_at time.
     */
    public function globalOnTimeRate(): float
    {
        $delivered = DeliveryOrder::where('status', 'delivered')
            ->whereNotNull('scheduled_at')
            ->whereNotNull('delivered_at')
            ->get();

        return $this->calculateOnTimeRate($delivered);
    }

    /**
     * Calculate on-time rate from an already-fetched collection.
     */
    public function onTimeRateFromCollection(Collection $orders): float
    {
        $delivered = $orders->where('status', 'delivered')
            ->filter(fn ($o) => $o->scheduled_at && $o->delivered_at);

        return $this->calculateOnTimeRate($delivered);
    }

    /**
     * Generate (or refresh) the daily performance record for every driver.
     */
    public function generateDailyReport(string $date): void
    {
        $drivers = Driver::all();

        foreach ($drivers as $driver) {
            $orders = DeliveryOrder::where('driver_id', $driver->id)
                ->whereDate('created_at', $date)
                ->get();

            $total     = $orders->count();
            $completed = $orders->where('status', 'delivered')->count();
            $failed    = $orders->where('status', 'failed')->count();

            $onTimeOrders = $orders->where('status', 'delivered')
                ->filter(fn ($o) => $o->scheduled_at && $o->delivered_at);

            $onTime = $onTimeOrders->filter(
                fn ($o) => $o->delivered_at <= $o->scheduled_at
            )->count();

            $late = $onTimeOrders->count() - $onTime;

            $avgTime   = $orders->whereNotNull('actual_duration')->avg('actual_duration') ?? 0;
            $totalDist = $orders->sum('distance_km');

            $onTimeRate      = $onTimeOrders->count() > 0 ? ($onTime / $onTimeOrders->count()) * 100 : 0;
            $completionRate  = $total > 0 ? ($completed / $total) * 100 : 0;
            $performanceScore = ($onTimeRate * 0.5) + ($completionRate * 0.5);

            DriverPerformance::updateOrCreate(
                ['driver_id' => $driver->id, 'period_date' => $date],
                [
                    'total_orders'       => $total,
                    'completed_orders'   => $completed,
                    'failed_orders'      => $failed,
                    'on_time_deliveries' => $onTime,
                    'late_deliveries'    => $late,
                    'avg_delivery_time'  => round($avgTime, 2),
                    'total_distance_km'  => round($totalDist, 2),
                    'total_idle_time'    => 0,
                    'on_time_rate'       => round($onTimeRate, 2),
                    'completion_rate'    => round($completionRate, 2),
                    'performance_score'  => round($performanceScore, 2),
                ]
            );
        }
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function calculateOnTimeRate($orders): float
    {
        $total = $orders->count();
        if ($total === 0) return 0.0;

        $onTime = $orders->filter(
            fn ($o) => $o->delivered_at && $o->scheduled_at && $o->delivered_at <= $o->scheduled_at
        )->count();

        return round(($onTime / $total) * 100, 2);
    }
}
