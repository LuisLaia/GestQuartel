<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniformItem extends Model
{
    use HasFactory;

    protected $table = 'uniform_items';

    protected $fillable = [
        'name',
        'category',
        'size',
        'condition',
        'total_quantity',
        'available_quantity',
        'minimum_quantity',
        'notes',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(UniformAssignment::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(UniformItemSize::class);
    }

    public function availableStock(): int
    {
        if ($this->relationLoaded('sizes')) {
            return (int) $this->sizes->sum('stock');
        }

        return (int) $this->sizes()->sum('stock');
    }

    public function isLowStock(): bool
    {
        return $this->availableStock() <= $this->minimum_quantity;
    }
}
