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
        $this->forceFill([
            'total_km' => $this->trips()
                ->where('distance_km', '>', 0)
                ->sum('distance_km'),
            'average_consumption' => $this->trips()
                ->where('distance_km', '>', 0)
                ->whereNotNull('consumption')
                ->avg('consumption'),
        ])->saveQuietly();
    }

    public function refreshTripTotals(): void
    {
        $this->updateMetrics();
    }
}
