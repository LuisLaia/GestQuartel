<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'admin@gestquartel.test',
        ], [
            'name' => 'Administrador',
            'password' => Hash::make('password'),
        ]);

        Person::firstOrCreate([
            'number' => 'B001',
        ], [
            'name' => 'Afonso Martins',
            'email' => 'afonso@example.test',
            'phone' => '910000001',
            'rank' => 'Bombeiro',
            'status' => 'ativo',
            'joined_at' => now()->subYear()->toDateString(),
        ]);

        Equipment::firstOrCreate([
            'name' => 'Camisola de intervenção',
            'size' => 'M',
        ], [
            'category' => 'Fardamento',
            'condition' => 'operacional',
            'total_quantity' => 5,
            'available_quantity' => 5,
            'minimum_quantity' => 1,
        ]);

        Equipment::firstOrCreate([
            'name' => 'Capacete',
            'size' => 'Único',
        ], [
            'category' => 'EPI',
            'condition' => 'operacional',
            'total_quantity' => 3,
            'available_quantity' => 3,
            'minimum_quantity' => 1,
        ]);
    }
}
