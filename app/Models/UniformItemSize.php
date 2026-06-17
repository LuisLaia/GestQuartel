<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformItemSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'uniform_item_id',
        'size',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
        ];
    }

    public function uniformItem(): BelongsTo
    {
        return $this->belongsTo(UniformItem::class);
    }
}
