<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $vehicles = Vehicle::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('plate', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            }))
            ->latest()
            ->get();

        $stats = [
            'total' => Vehicle::count(),
            'operational' => Vehicle::where('status', 'operacional')->count(),
            'maintenance' => Vehicle::where('status', 'manutencao')->count(),
            'inactive' => Vehicle::where('status', 'inativo')->count(),
        ];

        return view('vehicles.index', compact('vehicles', 'stats'));
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plate' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'status' => ['required', 'in:operacional,manutencao,inativo'],
            'notes' => ['nullable', 'string'],
        ]);

        Vehicle::create($data);

        return redirect()->route('vehicles.index')->with('success', 'Veiculo criado com sucesso.');
    }

    public function show(Vehicle $vehicle): View
    {
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plate' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'status' => ['required', 'in:operacional,manutencao,inativo'],
            'notes' => ['nullable', 'string'],
        ]);

        $vehicle->update($data);

        return redirect()->route('vehicles.index')->with('success', 'Veiculo atualizado.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Veiculo eliminado.');
    }
}
