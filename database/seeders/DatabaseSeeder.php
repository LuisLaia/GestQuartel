<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN
        User::firstOrCreate(
            ['email' => 'admin@gestquartel.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        // BOMBEIROS
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
                'name' => 'João Silva',
                'email' => 'joao@example.test',
                'phone' => '910000002',
                'rank' => 'Subchefe',
                'status' => 'ativo',
                'joined_at' => now()->subMonths(6),
            ]
        );

        // EQUIPAMENTO

        // Fardamento
        Equipment::firstOrCreate(
            ['name' => 'Camisola de intervenção', 'size' => 'M'],
            [
                'category' => 'Fardamento',
                'condition' => 'operacional',
                'total_quantity' => 5,
                'available_quantity' => 5,
                'minimum_quantity' => 1,
            ]
        );

        Equipment::firstOrCreate(
            ['name' => 'Botas', 'size' => '42'],
            [
                'category' => 'Fardamento',
                'condition' => 'operacional',
                'total_quantity' => 6,
                'available_quantity' => 6,
                'minimum_quantity' => 2,
            ]
        );

        // EPI
        Equipment::firstOrCreate(
            ['name' => 'Capacete', 'size' => 'Único'],
            [
                'category' => 'EPI',
                'condition' => 'operacional',
                'total_quantity' => 3,
                'available_quantity' => 3,
                'minimum_quantity' => 1,
            ]
        );

        Equipment::firstOrCreate(
            ['name' => 'Luvas', 'size' => 'L'],
            [
                'category' => 'EPI',
                'condition' => 'operacional',
                'total_quantity' => 10,
                'available_quantity' => 10,
                'minimum_quantity' => 3,
            ]
        );

        // Ferramentas
        Equipment::firstOrCreate(
            ['name' => 'Mangueira', 'size' => '30m'],
            [
                'category' => 'Ferramenta',
                'condition' => 'operacional',
                'total_quantity' => 4,
                'available_quantity' => 4,
                'minimum_quantity' => 1,
            ]
        );

        Equipment::firstOrCreate(
            ['name' => 'Extintor', 'size' => '6kg'],
            [
                'category' => 'Ferramenta',
                'condition' => 'operacional',
                'total_quantity' => 2,
                'available_quantity' => 2,
                'minimum_quantity' => 1,
            ]
        );

        // Exemplo em manutenção
        Equipment::firstOrCreate(
            ['name' => 'Veículo ABSC 01', 'size' => '-'],
            [
                'category' => 'Veículo',
                'condition' => 'manutencao',
                'total_quantity' => 1,
                'available_quantity' => 0,
                'minimum_quantity' => 1,
            ]
        );
    }
}