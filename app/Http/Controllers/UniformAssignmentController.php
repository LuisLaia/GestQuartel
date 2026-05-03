<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\UniformAssignment;
use App\Models\UniformItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UniformAssignmentController extends Controller
{
    public function index(): View
    {
        $assignments = UniformAssignment::with(['person', 'uniformItem'])
            ->latest('assigned_at')
            ->latest()
            ->paginate(12);

        return view('uniform.assignments.index', compact('assignments'));
    }

    public function create(): View
    {
        return view('uniform.assignments.create', [
            'people' => Person::where('status', 'ativo')->orderBy('name')->get(),
            'uniformItems' => UniformItem::where('available_quantity', '>', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'person_id' => ['required', Rule::exists('people', 'id')->where('status', 'ativo')],
            'uniform_item_id' => ['required', 'exists:uniform_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'assigned_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data): void {
            $uniformItem = UniformItem::whereKey($data['uniform_item_id'])->lockForUpdate()->firstOrFail();

            if ($uniformItem->available_quantity < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "So existem {$uniformItem->available_quantity} unidades disponiveis.",
                ]);
            }

            $uniformItem->decrement('available_quantity', $data['quantity']);
            UniformAssignment::create($data);
        });

        return redirect()->route('uniform.assignments.index')->with('status', 'Fardamento atribuido e stock atualizado.');
    }

    public function returnUniformItem(UniformAssignment $uniformAssignment): RedirectResponse
    {
        if (! $uniformAssignment->isActive()) {
            return back()->with('status', 'Esta atribuicao ja tinha sido devolvida.');
        }

        DB::transaction(function () use ($uniformAssignment): void {
            $uniformItem = UniformItem::whereKey($uniformAssignment->uniform_item_id)->lockForUpdate()->firstOrFail();
            $uniformItem->increment('available_quantity', $uniformAssignment->quantity);

            $uniformAssignment->update(['returned_at' => now()->toDateString()]);
        });

        return redirect()->route('uniform.assignments.index')->with('status', 'Devolucao registada e stock reposto.');
    }
}
