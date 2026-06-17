<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'mechanographic_number',
        'number',
        'email',
        'phone',
        'rank',
        'status',
        'joined_at',
        'waist_cm',
        'chest_cm',
        'height_cm',
        'leg_cm',
        'arm_cm',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'waist_cm' => 'integer',
            'chest_cm' => 'integer',
            'height_cm' => 'integer',
            'leg_cm' => 'integer',
            'arm_cm' => 'integer',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(UniformAssignment::class);
    }
}
