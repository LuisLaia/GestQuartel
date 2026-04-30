<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $equipment = Equipment::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('equipment.index', compact('equipment', 'search'));
    }

    public function create(): View
    {
        return view('equipment.create', ['equipment' => new Equipment()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Equipment::create($this->validatedData($request));

        return redirect()->route('equipment.index')->with('status', 'Equipamento criado com sucesso.');
    }

    public function show(Equipment $equipment): View
    {
        $equipment->load(['assignments.person']);

        return view('equipment.show', compact('equipment'));
    }

    public function edit(Equipment $equipment): View
    {
        return view('equipment.edit', compact('equipment'));
    }

    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        $data = $this->validatedData($request, $equipment);
        $assignedQuantity = $equipment->total_quantity - $equipment->available_quantity;

        if ($data['total_quantity'] < $assignedQuantity) {
            throw ValidationException::withMessages([
                'total_quantity' => 'O total não pode ser inferior à quantidade já atribuída.',
            ]);
        }

        $data['available_quantity'] = $data['total_quantity'] - $assignedQuantity;
        $equipment->update($data);

        return redirect()->route('equipment.show', $equipment)->with('status', 'Equipamento atualizado com sucesso.');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        if ($equipment->assignments()->whereNull('returned_at')->exists()) {
            return back()->withErrors([
                'equipment' => 'Não é possível remover equipamento ainda atribuído.',
            ]);
        }

        $equipment->delete();

        return redirect()->route('equipment.index')->with('status', 'Equipamento removido com sucesso.');
    }

    private function validatedData(Request $request, ?Equipment $equipment = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:50'],
            'condition' => ['required', 'string', 'max:100'],
            'total_quantity' => ['required', 'integer', 'min:0'],
            'minimum_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];

        if ($equipment === null) {
            $rules['available_quantity'] = ['required', 'integer', 'min:0', 'lte:total_quantity'];
        }

        return $request->validate($rules);
    }
}
