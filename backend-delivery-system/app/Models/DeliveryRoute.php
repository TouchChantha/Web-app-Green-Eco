<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'polyline',           // Encoded Google Maps polyline
        'waypoints',          // JSON array of waypoints
        'total_distance_km',
        'estimated_duration', // minutes
        'optimized',
        'google_route_id',
    ];

    protected $casts = [
        'waypoints'          => 'array',
        'total_distance_km'  => 'float',
        'estimated_duration' => 'integer',
        'optimized'          => 'boolean',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }
}
