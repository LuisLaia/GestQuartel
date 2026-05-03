@extends('layouts.app', ['title' => $uniformItem->name . ' - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Ficha de fardamento</p>
            <h2 class="page-title">{{ $uniformItem->name }}</h2>
        </div>
        <div class="flex gap-3">
            <a class="btn-secondary" href="{{ route('uniform.items.edit', $uniformItem) }}">Editar</a>
            <form method="POST" action="{{ route('uniform.items.destroy', $uniformItem) }}" onsubmit="return confirm('Remover esta peca?')">
                @csrf @method('DELETE')
                <button class="btn-danger">Remover</button>
            </form>
        </div>
    </div>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-1">
            <h3 class="section-title">Stock</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="label">Categoria</dt><dd>{{ $uniformItem->category ?: '-' }}</dd></div>
                <div><dt class="label">Tamanho</dt><dd>{{ $uniformItem->size ?: '-' }}</dd></div>
                <div><dt class="label">Condicao</dt><dd>{{ ucfirst($uniformItem->condition) }}</dd></div>
                <div><dt class="label">Disponivel</dt><dd class="text-2xl font-bold">{{ $uniformItem->available_quantity }}</dd></div>
                <div><dt class="label">Total</dt><dd>{{ $uniformItem->total_quantity }}</dd></div>
                <div><dt class="label">Minimo</dt><dd>{{ $uniformItem->minimum_quantity }}</dd></div>
            </dl>
        </div>

        <div class="card xl:col-span-2">
            <h3 class="section-title">Historico de atribuicoes</h3>
            <div class="mt-4 space-y-3">
                @forelse ($uniformItem->assignments as $assignment)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="font-semibold">{{ $assignment->person->name }} x {{ $assignment->quantity }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $assignment->assigned_at->format('d/m/Y') }}
                            - {{ $assignment->returned_at ? 'Devolvido em '.$assignment->returned_at->format('d/m/Y') : 'Ativo' }}
                        </p>
                    </div>
                @empty
                    <p class="empty">Ainda nao ha historico.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
