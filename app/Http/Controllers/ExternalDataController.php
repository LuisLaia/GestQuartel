<?php

namespace App\Http\Controllers;

use App\Services\PreventechService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

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
        try {
            $synced = $preventechService->syncFirefighters();
        } catch (RuntimeException $exception) {
            return back()
                ->with('error', $exception->getMessage())
                ->with('last_sync_at', now()->format('d/m/Y H:i'))
                ->with('last_sync_result', $exception->getMessage());
        }

        return back()
            ->with('success', "{$synced} bombeiro(s) sincronizado(s) com sucesso.")
            ->with('last_sync_at', now()->format('d/m/Y H:i'))
            ->with('last_sync_result', "{$synced} bombeiro(s) sincronizado(s).");
    }

    public function syncVehicles(PreventechService $preventechService): RedirectResponse
    {
        try {
            $synced = $preventechService->syncVehicles();
        } catch (RuntimeException $exception) {
            return back()
                ->with('error', $exception->getMessage())
                ->with('last_sync_at', now()->format('d/m/Y H:i'))
                ->with('last_sync_result', $exception->getMessage());
        }

        return back()
            ->with('success', "{$synced} veiculo(s) sincronizado(s) com sucesso.")
            ->with('last_sync_at', now()->format('d/m/Y H:i'))
            ->with('last_sync_result', "{$synced} veiculo(s) sincronizado(s).");
    }
}
