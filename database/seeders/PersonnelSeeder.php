<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\UniformAssignment;
use App\Models\UniformItem;
use App\Models\UniformItemSize;
use App\Services\UniformSizeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $people = [
            'Afonso Martins' => [
                'number' => 'B001',
                'email' => 'afonso@example.test',
                'phone' => '910000001',
                'waist_cm' => 82,
                'chest_cm' => 98,
                'height_cm' => 175,
            ],
            'Joao Silva' => [
                'number' => 'B002',
                'email' => 'joao@example.test',
                'phone' => '910000002',
                'waist_cm' => 90,
                'chest_cm' => 104,
                'height_cm' => 180,
            ],
            'Maria Costa' => [
                'number' => 'B003',
                'email' => 'maria.costa@example.test',
                'phone' => '910000003',
                'waist_cm' => 70,
                'chest_cm' => 88,
                'height_cm' => 165,
            ],
            'Pedro Sousa' => [
                'number' => 'B004',
                'email' => 'pedro.sousa@example.test',
                'phone' => '910000004',
                'waist_cm' => 95,
                'chest_cm' => 110,
                'height_cm' => 185,
            ],
            'Ana Ferreira' => [
                'number' => 'B005',
                'email' => 'ana.ferreira@example.test',
                'phone' => '910000005',
                'waist_cm' => 75,
                'chest_cm' => 92,
                'height_cm' => 168,
            ],
        ];

        foreach ($people as $name => $data) {
            $data['number'] = $this->safePersonnelNumber($data['email'], $data['number']);

            Person::updateOrCreate(
                ['email' => $data['email']],
                [
                    ...$data,
                    'name' => $name,
                    'rank' => 'Bombeiro',
                    'status' => 'ativo',
                    'joined_at' => now()->subMonths(18),
                ]
            );
        }

        $this->ensureAllPeopleHaveMeasurements();

        $items = [
            [
                'name' => 'Camisola de intervencao',
                'category' => 'Camisola',
                'minimum_quantity' => 2,
                'sizes' => ['S' => 5, 'M' => 4, 'L' => 3],
            ],
            [
                'name' => 'Polo',
                'category' => 'Polo',
                'minimum_quantity' => 2,
                'sizes' => ['S' => 5, 'M' => 4, 'L' => 3],
            ],
            [
                'name' => 'Calcas operacionais',
                'category' => 'Calcas',
                'minimum_quantity' => 2,
                'sizes' => ['XS' => 2, 'S' => 4, 'M' => 5, 'L' => 4, 'XL' => 2, 'XXL' => 1],
            ],
            [
                'name' => 'Botas',
                'category' => 'Botas',
                'minimum_quantity' => 1,
                'sizes' => ['40' => 2, '41' => 3, '42' => 4, '43' => 3, '44' => 2],
            ],
        ];

        foreach ($items as $item) {
            $uniformItem = UniformItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category' => $item['category'],
                    'size' => null,
                    'condition' => 'operacional',
                    'total_quantity' => array_sum($item['sizes']),
                    'available_quantity' => array_sum($item['sizes']),
                    'minimum_quantity' => $item['minimum_quantity'],
                ]
            );

            foreach ($item['sizes'] as $size => $stock) {
                UniformItemSize::updateOrCreate(
                    [
                        'uniform_item_id' => $uniformItem->id,
                        'size' => $size,
                    ],
                    ['stock' => $stock]
                );
            }
        }

        $this->ensureAllPeopleHaveMeasurements();
        $this->createDemoAssignments();
        $this->refreshAssignmentSizes();
        $this->refreshUniformStock();
    }

    private function safePersonnelNumber(string $email, string $preferredNumber): string
    {
        $existingWithNumber = Person::where('number', $preferredNumber)->first();

        if (! $existingWithNumber || $existingWithNumber->email === $email) {
            return $preferredNumber;
        }

        do {
            $number = 'B'.random_int(100, 999);
        } while (Person::where('number', $number)->exists());

        return $number;
    }

    private function ensureAllPeopleHaveMeasurements(): void
    {
        $profiles = [
            ['waist_cm' => 78, 'chest_cm' => 94, 'height_cm' => 172],
            ['waist_cm' => 84, 'chest_cm' => 100, 'height_cm' => 176],
            ['waist_cm' => 88, 'chest_cm' => 102, 'height_cm' => 178],
            ['waist_cm' => 92, 'chest_cm' => 106, 'height_cm' => 182],
            ['waist_cm' => 74, 'chest_cm' => 90, 'height_cm' => 168],
        ];

        Person::query()
            ->whereNull('waist_cm')
            ->orWhereNull('chest_cm')
            ->orWhereNull('height_cm')
            ->orderBy('id')
            ->get()
            ->each(function (Person $person, int $index) use ($profiles): void {
                $profile = $profiles[$index % count($profiles)];

                $person->forceFill([
                    'waist_cm' => $person->waist_cm ?? $profile['waist_cm'],
                    'chest_cm' => $person->chest_cm ?? $profile['chest_cm'],
                    'height_cm' => $person->height_cm ?? $profile['height_cm'],
                ])->save();
            });
    }

    private function createDemoAssignments(): void
    {
        $assignments = [
            ['Afonso Martins', 'Camisola de intervencao', 'M', 'M'],
            ['Joao Silva', 'Botas', '42', '42'],
            ['Maria Costa', 'Camisola de intervencao', 'S', 'S'],
            ['Pedro Sousa', 'Calcas operacionais', 'L', 'L'],
            ['Ana Ferreira', 'Polo', 'S', 'S'],
        ];

        foreach ($assignments as [$personName, $itemName, $itemSize, $chosenSize]) {
            $person = Person::where('name', $personName)->first();
            $item = UniformItem::where('name', $itemName)->first();

            if (! $person || ! $item) {
                continue;
            }

            UniformAssignment::updateOrCreate(
                [
                    'person_id' => $person->id,
                    'uniform_item_id' => $item->id,
                    'size' => $itemSize,
                    'assigned_at' => '2026-06-15',
                ],
                [
                    'quantity' => 1,
                    'size' => $itemSize,
                    'chosen_size' => $chosenSize,
                    'notes' => 'Atribuicao de demonstracao para recomendacao de tamanhos.',
                ]
            );
        }
    }

    private function refreshAssignmentSizes(): void
    {
        $sizeService = app(UniformSizeService::class);

        UniformAssignment::with(['person', 'uniformItem'])->each(function (UniformAssignment $assignment) use ($sizeService): void {
            if (! $assignment->person || ! $assignment->uniformItem) {
                return;
            }

            $suggestedSize = $sizeService->suggestSize($assignment->person, $assignment->uniformItem);

            $assignment->forceFill([
                'size' => $assignment->size ?: $assignment->chosen_size ?: $suggestedSize,
                'suggested_size' => $suggestedSize,
                'chosen_size' => $assignment->chosen_size ?: $suggestedSize,
            ])->save();
        });
    }

    private function refreshUniformStock(): void
    {
        UniformItem::with('sizes')->each(function (UniformItem $item): void {
            foreach ($item->sizes as $itemSize) {
                $assigned = UniformAssignment::where('uniform_item_id', $item->id)
                    ->where('size', $itemSize->size)
                    ->whereNull('returned_at')
                    ->sum('quantity');

                $itemSize->forceFill([
                    'stock' => max(0, $itemSize->stock - $assigned),
                ])->save();
            }

            $activeAssigned = UniformAssignment::where('uniform_item_id', $item->id)
                ->whereNull('returned_at')
                ->sum('quantity');

            $item->forceFill([
                'available_quantity' => $item->sizes()->sum('stock'),
                'total_quantity' => max((int) $item->total_quantity, (int) $item->sizes()->sum('stock') + (int) $activeAssigned),
            ])->save();
        });
    }
}
