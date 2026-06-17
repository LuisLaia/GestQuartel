<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request, VehicleService $vehicleService): View
    {
        $search = $request->string('search')->toString();
        $vehicles = $vehicleService->search($search);
        $stats = $vehicleService->stats();

        return view('vehicles.index', compact('vehicles', 'stats'));
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(UpdateVehicleRequest $request, VehicleService $vehicleService): RedirectResponse
    {
        try {
            $vehicleService->create($request->validated());
        } catch (Exception $exception) {
            Log::error('Vehicle store error', ['message' => $exception->getMessage()]);

            return back()->withInput()->with('error', 'Erro ao guardar veiculo.');
        }

        return redirect()->route('vehicles.index')->with('success', 'Veiculo criado com sucesso.');
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load('trips');

        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle, VehicleService $vehicleService): RedirectResponse
    {
        try {
            $vehicleService->update($vehicle, $request->validated());
        } catch (Exception $exception) {
            Log::error('Vehicle update error', [
                'vehicle_id' => $vehicle->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Erro ao atualizar veiculo.');
        }

        return redirect()->route('vehicles.index')->with('success', 'Veiculo atualizado.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        try {
            $vehicle->delete();
        } catch (Exception $exception) {
            Log::error('Vehicle delete error', [
                'vehicle_id' => $vehicle->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Erro ao eliminar veiculo.');
        }

        return redirect()->route('vehicles.index')->with('success', 'Veiculo eliminado.');
    }
}
