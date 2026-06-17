<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'vehicle_id',
        'driver_name',
        'distance_km',
        'fuel_used_liters',
        'consumption',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'fuel_used_liters' => 'decimal:2',
            'consumption' => 'decimal:2',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Trip $trip): void {
            $trip->consumption = static::calculateConsumption(
                (float) $trip->fuel_used_liters,
                (float) $trip->distance_km
            );
        });

        static::saved(function (Trip $trip): void {
            if ($trip->wasChanged('vehicle_id') && $trip->getOriginal('vehicle_id')) {
                Vehicle::find($trip->getOriginal('vehicle_id'))?->updateMetrics();
            }

            $trip->vehicle?->updateMetrics();
        });

        static::deleted(function (Trip $trip): void {
            $trip->vehicle?->updateMetrics();
        });
    }

    public static function calculateConsumption(float $fuel, float $distance): ?float
    {
        if ($distance <= 0) {
            return null;
        }

        return round(($fuel / $distance) * 100, 2);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
