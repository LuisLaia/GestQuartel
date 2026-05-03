<?php

namespace App\Http\Controllers;

use App\Models\UniformItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UniformItemController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $uniformItems = UniformItem::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('uniform.items.index', compact('uniformItems', 'search'));
    }

    public function create(): View
    {
        return view('uniform.items.create', ['uniformItem' => new UniformItem()]);
    }

    public function store(Request $request): RedirectResponse
    {
        UniformItem::create($this->validatedData($request));

        return redirect()->route('uniform.items.index')->with('status', 'Peca de fardamento criada com sucesso.');
    }

    public function show(UniformItem $uniformItem): View
    {
        $uniformItem->load(['assignments.person']);

        return view('uniform.items.show', compact('uniformItem'));
    }

    public function edit(UniformItem $uniformItem): View
    {
        return view('uniform.items.edit', compact('uniformItem'));
    }

    public function update(Request $request, UniformItem $uniformItem): RedirectResponse
    {
        $data = $this->validatedData($request, $uniformItem);
        $assignedQuantity = $uniformItem->total_quantity - $uniformItem->available_quantity;

        if ($data['total_quantity'] < $assignedQuantity) {
            throw ValidationException::withMessages([
                'total_quantity' => 'O total nao pode ser inferior a quantidade ja atribuida.',
            ]);
        }

        $data['available_quantity'] = $data['total_quantity'] - $assignedQuantity;
        $uniformItem->update($data);

        return redirect()->route('uniform.items.show', $uniformItem)->with('status', 'Peca de fardamento atualizada com sucesso.');
    }

    public function destroy(UniformItem $uniformItem): RedirectResponse
    {
        if ($uniformItem->assignments()->whereNull('returned_at')->exists()) {
            return back()->withErrors([
                'uniform_item' => 'Nao e possivel remover fardamento ainda atribuido.',
            ]);
        }

        $uniformItem->delete();

        return redirect()->route('uniform.items.index')->with('status', 'Peca de fardamento removida com sucesso.');
    }

    private function validatedData(Request $request, ?UniformItem $uniformItem = null): array
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

        if ($uniformItem === null) {
            $rules['available_quantity'] = ['required', 'integer', 'min:0', 'lte:total_quantity'];
        }

        return $request->validate($rules);
    }
}
