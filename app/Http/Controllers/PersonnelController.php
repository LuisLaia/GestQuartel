<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Services\PreventechService;
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
        $person->load(['assignments.uniformItem']);

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

    public function syncPreventech(PreventechService $service): RedirectResponse
    {
        $result = $service->getFirefighters();

        if ($result['error']) {
            return back()->with('error', $result['error']);
        }

        $synced = 0;

        foreach ($result['data'] as $firefighter) {
            $externalId = $this->stringValue(data_get($firefighter, 'external_id') ?? data_get($firefighter, 'id'));
            $mechanographicNumber = $this->stringValue(
                data_get($firefighter, 'mechanographic_number')
                    ?? data_get($firefighter, 'numero_mecanografico')
                    ?? data_get($firefighter, 'number')
                    ?? data_get($firefighter, 'numero')
            );
            $email = $this->stringValue(data_get($firefighter, 'email'));
            $name = $this->stringValue(data_get($firefighter, 'name') ?? data_get($firefighter, 'nome'));

            if ($name === null) {
                continue;
            }

            $identity = $this->preventechIdentity($externalId, $mechanographicNumber, $email);

            if ($identity === null) {
                continue;
            }

            Person::updateOrCreate($identity, array_filter([
                'external_id' => $externalId,
                'name' => $name,
                'mechanographic_number' => $mechanographicNumber,
                'email' => $email,
                'phone' => $this->stringValue(
                    data_get($firefighter, 'phone')
                        ?? data_get($firefighter, 'telefone')
                        ?? data_get($firefighter, 'telemovel')
                ),
                'rank' => $this->stringValue(data_get($firefighter, 'rank') ?? data_get($firefighter, 'posto')),
                'status' => $this->normalizePreventechStatus(data_get($firefighter, 'status') ?? data_get($firefighter, 'estado')),
            ], fn ($value) => $value !== null));

            $synced++;
        }

        return redirect()
            ->route('personnel.index')
            ->with('success', "{$synced} registo(s) sincronizado(s) da Preventech.");
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
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function preventechIdentity(?string $externalId, ?string $mechanographicNumber, ?string $email): ?array
    {
        return match (true) {
            $externalId !== null => ['external_id' => $externalId],
            $mechanographicNumber !== null => ['mechanographic_number' => $mechanographicNumber],
            $email !== null => ['email' => $email],
            default => null,
        };
    }

    private function normalizePreventechStatus(mixed $status): string
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'inativo', 'inactive', '0' => 'inativo',
            'suspenso', 'suspended' => 'suspenso',
            default => 'ativo',
        };
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
