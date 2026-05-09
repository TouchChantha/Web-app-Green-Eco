<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'vehicle_type',
        'vehicle_plate',
        'status',        // available | on_delivery | offline
        'current_lat',
        'current_lng',
        'last_location_at',
    ];

    protected $casts = [
        'current_lat'      => 'float',
        'current_lng'      => 'float',
        'last_location_at' => 'datetime',
    ];

    // Relationships
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function locationLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DriverLocationLog::class);
    }

    public function performanceReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DriverPerformance::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOnDelivery($query)
    {
        return $query->where('status', 'on_delivery');
    }
}
