<?php

namespace App\Http\Controllers;

use App\Services\PreventechService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExternalDataController extends Controller
{
    public function firefighters(PreventechService $preventechService): View
    {
        $result = $preventechService->getFirefighters();

        return view('external.firefighters', [
            'firefighters' => $result['data'],
            'error' => $result['error'],
        ]);
    }

    public function vehicles(PreventechService $preventechService): View
    {
        $result = $preventechService->getVehicles();

        return view('external.vehicles', [
            'vehicles' => $result['data'],
            'error' => $result['error'],
        ]);
    }

    public function syncFirefighters(PreventechService $preventechService): RedirectResponse
    {
        $preventechService->syncFirefighters();

        return back()->with('success', 'Bombeiros sincronizados com sucesso.');
    }

    public function syncVehicles(PreventechService $preventechService): RedirectResponse
    {
        $preventechService->syncVehicles();

        return back()->with('success', 'Veiculos sincronizados com sucesso.');
    }
}
