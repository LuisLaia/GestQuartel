<?php

use App\Models\Equipment;
use App\Models\EquipmentAssignment;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login', function () {
    $this->get('/')->assertRedirect('/login');
});

test('authenticated users can see dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

test('assigning equipment decreases available stock', function () {
    $user = User::factory()->create();
    $person = Person::create(['name' => 'Bombeiro Teste', 'status' => 'ativo']);
    $equipment = Equipment::create([
        'name' => 'Camisola',
        'category' => 'Fardamento',
        'condition' => 'operacional',
        'total_quantity' => 5,
        'available_quantity' => 5,
        'minimum_quantity' => 1,
    ]);

    $this->actingAs($user)->post('/assignments', [
        'person_id' => $person->id,
        'equipment_id' => $equipment->id,
        'quantity' => 1,
        'assigned_at' => now()->toDateString(),
    ])->assertRedirect('/assignments');

    expect($equipment->fresh()->available_quantity)->toBe(4);
});

test('returning equipment restores available stock', function () {
    $user = User::factory()->create();
    $person = Person::create(['name' => 'Bombeiro Teste', 'status' => 'ativo']);
    $equipment = Equipment::create([
        'name' => 'Camisola',
        'category' => 'Fardamento',
        'condition' => 'operacional',
        'total_quantity' => 5,
        'available_quantity' => 4,
        'minimum_quantity' => 1,
    ]);
    $assignment = EquipmentAssignment::create([
        'person_id' => $person->id,
        'equipment_id' => $equipment->id,
        'quantity' => 1,
        'assigned_at' => now()->toDateString(),
    ]);

    $this->actingAs($user)
        ->patch(route('assignments.return', $assignment))
        ->assertRedirect('/assignments');

    expect($equipment->fresh()->available_quantity)->toBe(5)
        ->and($assignment->fresh()->returned_at)->not->toBeNull();
});
