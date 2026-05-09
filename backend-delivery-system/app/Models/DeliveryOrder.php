<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'created_by',
        'driver_id',
        'recipient_name',
        'recipient_phone',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'status',               // pending | assigned | in_transit | delivered | failed | cancelled
        'priority',             // low | normal | high | urgent
        'notes',
        'scheduled_at',
        'assigned_at',
        'pickup_at',
        'delivered_at',
        'estimated_duration',   // minutes
        'actual_duration',      // minutes
        'distance_km',
        'delay_reason',
    ];

    protected $casts = [
        'pickup_lat'         => 'float',
        'pickup_lng'         => 'float',
        'delivery_lat'       => 'float',
        'delivery_lng'       => 'float',
        'scheduled_at'       => 'datetime',
        'assigned_at'        => 'datetime',
        'pickup_at'          => 'datetime',
        'delivered_at'       => 'datetime',
        'estimated_duration' => 'integer',
        'actual_duration'    => 'integer',
        'distance_km'        => 'float',
    ];

    // Auto-generate order number
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(uniqid());
        });
    }

    // Relationships
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function route(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DeliveryRoute::class);
    }

    public function stops(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DeliveryStop::class)->orderBy('sequence');
    }

    public function statusHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    // Helpers
    public function isDelayed(): bool
    {
        if (!$this->scheduled_at || in_array($this->status, ['delivered', 'cancelled'])) {
            return false;
        }
        return now()->gt($this->scheduled_at);
    }

    public function calculateActualDuration(): ?int
    {
        if ($this->pickup_at && $this->delivered_at) {
            return (int) $this->pickup_at->diffInMinutes($this->delivered_at);
        }
        return null;
    }
}
