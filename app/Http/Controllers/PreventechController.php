<?php

namespace App\Http\Controllers;

use App\Services\PreventechService;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class PreventechController extends Controller
{
    public function syncPersonnel(PreventechService $preventechService): RedirectResponse
    {
        return $this->sync(
            fn () => $preventechService->syncPersonnel(),
            'pessoal',
            'registo(s) de pessoal sincronizado(s).'
        );
    }

    public function syncVehicles(PreventechService $preventechService): RedirectResponse
    {
        return $this->sync(
            fn () => $preventechService->syncVehicles(),
            'veiculos',
            'veiculo(s) sincronizado(s).'
        );
    }

    private function sync(callable $callback, string $type, string $message): RedirectResponse
    {
        try {
            $synced = $callback();
        } catch (RuntimeException $exception) {
            return back()
                ->with('error', $exception->getMessage())
                ->with('last_sync_at', now()->format('d/m/Y H:i'))
                ->with('last_sync_result', "Erro ao sincronizar {$type}: {$exception->getMessage()}");
        }

        return back()
            ->with('success', "{$synced} {$message}")
            ->with('last_sync_at', now()->format('d/m/Y H:i'))
            ->with('last_sync_result', "{$synced} {$message}");
    }
}
