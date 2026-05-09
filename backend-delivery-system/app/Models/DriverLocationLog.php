<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLocationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'delivery_order_id',
        'lat',
        'lng',
        'speed_kmh',
        'heading',       // degrees 0-360
        'accuracy',      // GPS accuracy in meters
        'is_stopped',
        'logged_at',
    ];

    protected $casts = [
        'lat'        => 'float',
        'lng'        => 'float',
        'speed_kmh'  => 'float',
        'heading'    => 'float',
        'accuracy'   => 'float',
        'is_stopped' => 'boolean',
        'logged_at'  => 'datetime',
    ];

    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }
}
