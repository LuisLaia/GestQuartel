<?php

namespace App\Services;

use App\Models\Person;
use App\Models\UniformAssignment;
use App\Models\UniformItem;
use App\Models\UniformItemSize;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UniformService
{
    public function assignments(array $filters): LengthAwarePaginator
    {
        return UniformAssignment::query()
            ->with(['person', 'uniformItem'])
            ->when($filters['person_id'] ?? null, fn ($query, $personId) => $query->where('person_id', $personId))
            ->when($filters['item_id'] ?? null, fn ($query, $itemId) => $query->where('uniform_item_id', $itemId))
            ->when(($filters['status'] ?? null) === 'ativo', fn ($query) => $query->whereNull('returned_at'))
            ->when(($filters['status'] ?? null) === 'devolvido', fn ($query) => $query->whereNotNull('returned_at'))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('person', fn ($personQuery) => $personQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('uniformItem', fn ($itemQuery) => $itemQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('assigned_at')
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function peopleForFilter(): Collection
    {
        return Person::orderBy('name')->get();
    }

    public function itemsForFilter(): Collection
    {
        return UniformItem::orderBy('name')->get();
    }

    public function activePeople(): Collection
    {
        return Person::where('status', 'ativo')->orderBy('name')->get();
    }

    public function availableItems(): Collection
    {
        return UniformItem::whereHas('sizes', fn ($query) => $query->where('stock', '>', 0))
            ->with(['sizes' => fn ($query) => $query->orderBy('size')])
            ->orderBy('name')
            ->get();
    }

    public function assign(array $data): UniformAssignment
    {
        return DB::transaction(function () use ($data): UniformAssignment {
            $itemSize = UniformItemSize::where('uniform_item_id', $data['uniform_item_id'])
                ->where('size', $data['size'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($itemSize->stock < $data['quantity']) {
                throw ValidationException::withMessages(['quantity' => 'Stock insuficiente.']);
            }

            $itemSize->decrement('stock', $data['quantity']);
            $this->syncLegacyStock($itemSize->uniformItem);

            return UniformAssignment::create($data);
        });
    }

    public function returnAssignment(UniformAssignment $assignment): void
    {
        if (! $assignment->isActive()) {
            return;
        }

        DB::transaction(function () use ($assignment): void {
            $size = $assignment->size ?: $assignment->chosen_size ?: $assignment->suggested_size;

            if ($size) {
                $itemSize = UniformItemSize::firstOrCreate(
                    [
                        'uniform_item_id' => $assignment->uniform_item_id,
                        'size' => $size,
                    ],
                    ['stock' => 0]
                );

                $itemSize->increment('stock', $assignment->quantity);
                $this->syncLegacyStock($itemSize->uniformItem);
            }

            $assignment->update(['returned_at' => now()->toDateString()]);
        });
    }

    private function syncLegacyStock(UniformItem $item): void
    {
        $item->forceFill([
            'available_quantity' => $item->sizes()->sum('stock'),
            'total_quantity' => max((int) $item->total_quantity, (int) $item->sizes()->sum('stock')),
        ])->save();
    }
}
