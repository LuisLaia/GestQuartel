<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonnelController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $people = Person::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%")
                    ->orWhere('mechanographic_number', 'like', "%{$search}%")
                    ->orWhere('rank', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('people.index', compact('people', 'search'));
    }

    public function create(): View
    {
        return view('people.create', ['person' => new Person()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Person::create($this->validatedData($request));

        return redirect()->route('personnel.index')->with('status', 'Pessoa criada com sucesso.');
    }

    public function show(Person $person): View
    {
        $person->load(['assignments' => fn ($query) => $query->with('uniformItem')->latest('assigned_at')->latest()]);

        return view('people.show', compact('person'));
    }

    public function edit(Person $person): View
    {
        return view('people.edit', compact('person'));
    }

    public function update(Request $request, Person $person): RedirectResponse
    {
        $person->update($this->validatedData($request, $person));

        return redirect()->route('personnel.show', $person)->with('status', 'Pessoa atualizada com sucesso.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        if ($person->assignments()->whereNull('returned_at')->exists()) {
            return back()->withErrors([
                'person' => 'Nao e possivel remover uma pessoa com fardamento ainda atribuido.',
            ]);
        }

        $person->delete();

        return redirect()->route('personnel.index')->with('status', 'Pessoa removida com sucesso.');
    }

    private function validatedData(Request $request, ?Person $person = null): array
    {
        $numberRule = Rule::unique('people', 'number');
        $emailRule = Rule::unique('people', 'email');

        if ($person !== null) {
            $numberRule->ignore($person);
            $emailRule->ignore($person);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'mechanographic_number' => ['nullable', 'string', 'max:50'],
            'number' => ['nullable', 'string', 'max:50', $numberRule],
            'email' => ['nullable', 'email', 'max:255', $emailRule],
            'phone' => ['nullable', 'string', 'max:50'],
            'rank' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['ativo', 'inativo', 'suspenso'])],
            'joined_at' => ['nullable', 'date'],
            'waist_cm' => ['nullable', 'integer', 'min:1', 'max:300'],
            'chest_cm' => ['nullable', 'integer', 'min:1', 'max:300'],
            'height_cm' => ['nullable', 'integer', 'min:1', 'max:250'],
            'leg_cm' => ['nullable', 'integer', 'min:1', 'max:200'],
            'arm_cm' => ['nullable', 'integer', 'min:1', 'max:200'],
            'notes' => ['nullable', 'string'],
        ]);
    }

}
