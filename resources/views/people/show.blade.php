@extends('layouts.app', ['title' => $person->name . ' - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Ficha individual</p>
            <h2 class="page-title">{{ $person->name }}</h2>
        </div>
        <div class="flex gap-3">
            <a class="btn-secondary" href="{{ route('people.edit', $person) }}">Editar</a>
            <form method="POST" action="{{ route('people.destroy', $person) }}" onsubmit="return confirm('Remover esta pessoa?')">
                @csrf @method('DELETE')
                <button class="btn-danger">Remover</button>
            </form>
        </div>
    </div>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-1">
            <h3 class="section-title">Dados</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="label">Número</dt><dd>{{ $person->number ?: '—' }}</dd></div>
                <div><dt class="label">Posto/Função</dt><dd>{{ $person->rank ?: '—' }}</dd></div>
                <div><dt class="label">Email</dt><dd>{{ $person->email ?: '—' }}</dd></div>
                <div><dt class="label">Telefone</dt><dd>{{ $person->phone ?: '—' }}</dd></div>
                <div><dt class="label">Estado</dt><dd>{{ ucfirst($person->status) }}</dd></div>
            </dl>
        </div>

        <div class="card xl:col-span-2">
            <h3 class="section-title">Equipamento atribuído</h3>
            <div class="mt-4 space-y-3">
                @forelse ($person->assignments as $assignment)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="font-semibold">{{ $assignment->equipment->name }} × {{ $assignment->quantity }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $assignment->assigned_at->format('d/m/Y') }}
                            · {{ $assignment->returned_at ? 'Devolvido em '.$assignment->returned_at->format('d/m/Y') : 'Ativo' }}
                        </p>
                    </div>
                @empty
                    <p class="empty">Sem equipamento atribuído.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
