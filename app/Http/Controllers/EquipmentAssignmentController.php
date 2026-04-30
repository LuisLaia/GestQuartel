<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentAssignment;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EquipmentAssignmentController extends Controller
{
    public function index(): View
    {
        $assignments = EquipmentAssignment::with(['person', 'equipment'])
            ->latest('assigned_at')
            ->latest()
            ->paginate(12);

        return view('assignments.index', compact('assignments'));
    }

    public function create(): View
    {
        return view('assignments.create', [
            'people' => Person::where('status', 'ativo')->orderBy('name')->get(),
            'equipment' => Equipment::where('available_quantity', '>', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'person_id' => ['required', Rule::exists('people', 'id')->where('status', 'ativo')],
            'equipment_id' => ['required', 'exists:equipment,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'assigned_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data): void {
            $equipment = Equipment::whereKey($data['equipment_id'])->lockForUpdate()->firstOrFail();

            if ($equipment->available_quantity < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "Só existem {$equipment->available_quantity} unidades disponíveis.",
                ]);
            }

            $equipment->decrement('available_quantity', $data['quantity']);
            EquipmentAssignment::create($data);
        });

        return redirect()->route('assignments.index')->with('status', 'Equipamento atribuído e stock atualizado.');
    }

    public function returnEquipment(EquipmentAssignment $assignment): RedirectResponse
    {
        if (! $assignment->isActive()) {
            return back()->with('status', 'Esta atribuição já tinha sido devolvida.');
        }

        DB::transaction(function () use ($assignment): void {
            $equipment = Equipment::whereKey($assignment->equipment_id)->lockForUpdate()->firstOrFail();
            $equipment->increment('available_quantity', $assignment->quantity);

            $assignment->update(['returned_at' => now()->toDateString()]);
        });

        return redirect()->route('assignments.index')->with('status', 'Devolução registada e stock reposto.');
    }
}
