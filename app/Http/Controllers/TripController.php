<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequest;
use App\Services\TripService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TripController extends Controller
{
    public function index(Request $request, TripService $tripService): View
    {
        $search = $request->string('search')->toString();
        $trips = $tripService->search($search);

        return view('trips.index', compact('trips', 'search'));
    }

    public function store(StoreTripRequest $request, TripService $tripService): RedirectResponse
    {
        try {
            $tripService->create($request->validated());
        } catch (Exception $exception) {
            Log::error('Trip store error', ['message' => $exception->getMessage()]);

            return back()->withInput()->with('error', 'Erro ao registar viagem.');
        }

        return redirect()->route('trips.index')->with('success', 'Viagem registada com sucesso.');
    }

    public function sync(Request $request, TripService $tripService): RedirectResponse
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $data['start_date'] ?? now()->subDays(7)->toDateString();
        $endDate = $data['end_date'] ?? now()->toDateString();

        try {
            $result = $tripService->sync($startDate, $endDate);
        } catch (Exception $exception) {
            Log::error('Cartrack sync error', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Erro ao sincronizar API.');
        }

        if ($result['error']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', "{$result['synced']} viagem(ns) sincronizada(s) com sucesso.");
    }
}
