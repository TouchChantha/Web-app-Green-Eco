<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'period_date',           // Date of the report (daily)
        'total_orders',
        'completed_orders',
        'failed_orders',
        'on_time_deliveries',
        'late_deliveries',
        'avg_delivery_time',     // minutes
        'total_distance_km',
        'total_idle_time',       // minutes
        'on_time_rate',          // percentage
        'completion_rate',       // percentage
        'performance_score',     // 0-100
    ];

    protected $casts = [
        'period_date'        => 'date',
        'avg_delivery_time'  => 'float',
        'total_distance_km'  => 'float',
        'total_idle_time'    => 'integer',
        'on_time_rate'       => 'float',
        'completion_rate'    => 'float',
        'performance_score'  => 'float',
    ];

    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
