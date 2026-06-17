<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUniformAssignmentRequest;
use App\Models\Person;
use App\Models\UniformAssignment;
use App\Models\UniformItem;
use App\Services\UniformService;
use App\Services\UniformSizeService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UniformAssignmentController extends Controller
{
    public function index(Request $request, UniformService $uniformService): View
    {
        $filters = $request->only(['person_id', 'item_id', 'status', 'date_from', 'date_to', 'search']);

        return view('uniform.assignments.index', [
            'assignments' => $uniformService->assignments($filters),
            'people' => $uniformService->peopleForFilter(),
            'uniformItems' => $uniformService->itemsForFilter(),
            'hasFilters' => $request->filled(['person_id', 'item_id', 'status', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function create(UniformService $uniformService): View
    {
        return view('uniform.assignments.create', [
            'people' => $uniformService->activePeople(),
            'uniformItems' => $uniformService->availableItems(),
        ]);
    }

    public function suggestSize(Request $request, UniformSizeService $uniformSizeService): JsonResponse
    {
        $data = $request->validate([
            'person_id' => ['required', 'exists:people,id'],
            'uniform_item_id' => ['required', 'exists:uniform_items,id'],
        ]);

        $person = Person::findOrFail($data['person_id']);
        $item = UniformItem::findOrFail($data['uniform_item_id']);

        if (! $uniformSizeService->hasEnoughData($person, $item)) {
            return response()->json([
                'size' => null,
                'message' => 'Sem dados suficientes para recomendacao.',
            ]);
        }

        $size = $uniformSizeService->suggestSize($person, $item);

        return response()->json([
            'size' => $size,
            'message' => $size
                ? "Tamanho sugerido com base nas medidas: {$size}"
                : 'Sem dados suficientes para recomendacao.',
        ]);
    }

    public function store(StoreUniformAssignmentRequest $request, UniformService $uniformService): RedirectResponse
    {
        try {
            $uniformService->assign($request->validated());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            Log::error('Uniform assignment store error', ['message' => $exception->getMessage()]);

            return back()->withInput()->with('error', 'Erro ao registar atribuicao.');
        }

        return redirect()->route('uniform.assignments.index')->with('status', 'Fardamento atribuido e stock atualizado.');
    }

    public function returnUniformItem(UniformAssignment $uniformAssignment, UniformService $uniformService): RedirectResponse
    {
        if (! $uniformAssignment->isActive()) {
            return back()->with('status', 'Esta atribuicao ja tinha sido devolvida.');
        }

        try {
            $uniformService->returnAssignment($uniformAssignment);
        } catch (Exception $exception) {
            Log::error('Uniform assignment return error', [
                'assignment_id' => $uniformAssignment->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Erro ao registar devolucao.');
        }

        return redirect()->route('uniform.assignments.index')->with('status', 'Devolucao registada e stock reposto.');
    }
}
