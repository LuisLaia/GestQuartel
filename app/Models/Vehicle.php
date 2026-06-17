<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plate',
        'type',
        'status',
        'year',
        'notes',
        'total_km',
        'average_consumption',
    ];

    protected function casts(): array
    {
        return [
            'total_km' => 'decimal:2',
            'average_consumption' => 'decimal:2',
        ];
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function updateMetrics(): void
    {
        $totalKm = (float) $this->trips()
            ->where('distance_km', '>', 0)
            ->sum('distance_km');

        $totalFuel = (float) $this->trips()
            ->where('distance_km', '>', 0)
            ->where('fuel_used_liters', '>', 0)
            ->sum('fuel_used_liters');

        $this->forceFill([
            'total_km' => $totalKm,
            'average_consumption' => $totalKm > 0 && $totalFuel > 0
                ? round(($totalFuel / $totalKm) * 100, 2)
                : null,
        ])->saveQuietly();
    }

    public function refreshTripTotals(): void
    {
        $this->updateMetrics();
    }
}
