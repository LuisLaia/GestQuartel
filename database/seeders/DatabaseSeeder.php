<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\UniformItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gestquartel.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        Person::firstOrCreate(
            ['number' => 'B001'],
            [
                'name' => 'Afonso Martins',
                'email' => 'afonso@example.test',
                'phone' => '910000001',
                'rank' => 'Bombeiro',
                'status' => 'ativo',
                'joined_at' => now()->subYear(),
            ]
        );

        Person::firstOrCreate(
            ['number' => 'B002'],
            [
                'name' => 'Joao Silva',
                'email' => 'joao@example.test',
                'phone' => '910000002',
                'rank' => 'Subchefe',
                'status' => 'ativo',
                'joined_at' => now()->subMonths(6),
            ]
        );

        foreach ([
            ['Camisola de intervencao', 'M', 5, 1],
            ['Botas', '42', 6, 2],
            ['Polo', 'L', 3, 1],
            ['Bone', 'Unico', 10, 3],
            ['Casaco', 'M', 4, 1],
            ['Calcas', '42', 2, 1],
            ['Impermeavel', 'L', 1, 1],
        ] as [$name, $size, $totalQuantity, $minimumQuantity]) {
            UniformItem::firstOrCreate(
                ['name' => $name, 'size' => $size],
                [
                    'category' => 'Fardamento',
                    'condition' => 'operacional',
                    'total_quantity' => $totalQuantity,
                    'available_quantity' => $totalQuantity,
                    'minimum_quantity' => $minimumQuantity,
                ]
            );
        }

        Vehicle::firstOrCreate(
            ['plate' => '00-AA-00'],
            [
                'name' => 'ABSC 01',
                'type' => 'Ambulancia',
                'year' => 2018,
            ]
        );

        Vehicle::firstOrCreate(
            ['plate' => '11-BB-11'],
            [
                'name' => 'VLCI 02',
                'type' => 'Combate Incendios',
                'year' => 2020,
            ]
        );

        Vehicle::firstOrCreate(
            ['plate' => '22-CC-22'],
            [
                'name' => 'VUCI 03',
                'type' => 'Comando',
                'year' => 2016,
            ]
        );
    }
}
