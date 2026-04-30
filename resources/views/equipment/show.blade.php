@extends('layouts.app', ['title' => $equipment->name . ' - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Ficha de inventário</p>
            <h2 class="page-title">{{ $equipment->name }}</h2>
        </div>
        <div class="flex gap-3">
            <a class="btn-secondary" href="{{ route('equipment.edit', $equipment) }}">Editar</a>
            <form method="POST" action="{{ route('equipment.destroy', $equipment) }}" onsubmit="return confirm('Remover este equipamento?')">
                @csrf @method('DELETE')
                <button class="btn-danger">Remover</button>
            </form>
        </div>
    </div>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-1">
            <h3 class="section-title">Stock</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="label">Categoria</dt><dd>{{ $equipment->category ?: '—' }}</dd></div>
                <div><dt class="label">Tamanho</dt><dd>{{ $equipment->size ?: '—' }}</dd></div>
                <div><dt class="label">Condição</dt><dd>{{ ucfirst($equipment->condition) }}</dd></div>
                <div><dt class="label">Disponível</dt><dd class="text-2xl font-bold">{{ $equipment->available_quantity }}</dd></div>
                <div><dt class="label">Total</dt><dd>{{ $equipment->total_quantity }}</dd></div>
                <div><dt class="label">Mínimo</dt><dd>{{ $equipment->minimum_quantity }}</dd></div>
            </dl>
        </div>

        <div class="card xl:col-span-2">
            <h3 class="section-title">Histórico de atribuições</h3>
            <div class="mt-4 space-y-3">
                @forelse ($equipment->assignments as $assignment)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="font-semibold">{{ $assignment->person->name }} × {{ $assignment->quantity }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $assignment->assigned_at->format('d/m/Y') }}
                            · {{ $assignment->returned_at ? 'Devolvido em '.$assignment->returned_at->format('d/m/Y') : 'Ativo' }}
                        </p>
                    </div>
                @empty
                    <p class="empty">Ainda não há histórico.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
