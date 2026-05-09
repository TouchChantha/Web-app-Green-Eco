<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'sequence',
        'address',
        'lat',
        'lng',
        'type',            // pickup | delivery | waypoint
        'status',          // pending | reached | completed | skipped
        'arrived_at',
        'departed_at',
        'stop_duration',   // minutes actually spent at stop
        'notes',
    ];

    protected $casts = [
        'lat'           => 'float',
        'lng'           => 'float',
        'arrived_at'    => 'datetime',
        'departed_at'   => 'datetime',
        'stop_duration' => 'integer',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }
}
