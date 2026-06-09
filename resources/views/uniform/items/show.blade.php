@extends('layouts.app', ['title' => $uniformItem->name . ' - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Ficha de fardamento</p>
                <h1 class="page-title">{{ $uniformItem->name }}</h1>
            </div>
            <div class="flex gap-3">
                <a class="btn-secondary" href="{{ route('uniform.items.edit', $uniformItem) }}">Editar</a>
                <form method="POST" action="{{ route('uniform.items.destroy', $uniformItem) }}" onsubmit="return confirm('Remover esta peça?')">
                    @csrf
                    @method('DELETE')
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
                    <div><dt class="label">Condição</dt><dd>{{ ucfirst($uniformItem->condition) }}</dd></div>
                    <div><dt class="label">Stock disponível</dt><dd class="text-2xl font-bold">{{ $uniformItem->available_quantity }}</dd></div>
                    <div><dt class="label">Stock total</dt><dd>{{ $uniformItem->total_quantity }}</dd></div>
                    <div><dt class="label">Atualmente atribuído</dt><dd>{{ $assignedQuantity }}</dd></div>
                    <div><dt class="label">Mínimo</dt><dd>{{ $uniformItem->minimum_quantity }}</dd></div>
                </dl>
            </div>

            <div class="card xl:col-span-2">
                <h3 class="section-title">Atribuições ativas</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($uniformItem->assignments->whereNull('returned_at') as $assignment)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="font-semibold">{{ $assignment->person?->name ?? 'Pessoa removida' }} x {{ $assignment->quantity }}</p>
                            <p class="text-sm text-slate-500">{{ $assignment->assigned_at->format('d/m/Y') }}</p>
                        </div>
                    @empty
                        <p class="empty">Sem atribuições registadas.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="card overflow-hidden p-0">
            <div class="p-6 pb-0">
                <h3 class="section-title">Histórico de Movimentos</h3>
            </div>
            <table class="table w-full mt-4">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Movimento</th>
                        <th>Descrição</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td>{{ optional($movement['date'])->format('d/m/Y') ?: '-' }}</td>
                            <td>{{ $movement['type'] }}</td>
                            <td>{{ $movement['description'] }}</td>
                            <td>{{ $movement['quantity'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Sem histórico disponível.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-6 pt-0">
                {{ $movements->links() }}
            </div>
        </section>
    </div>
@endsection
