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

    public function isLowStock(): bool
    {
        return $this->available_quantity <= $this->minimum_quantity;
    }
}
