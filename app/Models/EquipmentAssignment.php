<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'equipment_id',
        'quantity',
        'assigned_at',
        'returned_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'date',
            'returned_at' => 'date',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function isActive(): bool
    {
        return $this->returned_at === null;
    }
}
