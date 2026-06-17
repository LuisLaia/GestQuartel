@extends('layouts.app', ['title' => 'Fardamento - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Inventário</p>
                <h1 class="page-title">Fardamento</h1>
            </div>
            <div class="flex gap-3">
                <a class="btn-secondary" href="{{ route('uniform.assignments.index') }}">Ver atribuições</a>
                <a class="btn-primary" href="{{ route('uniform.items.create') }}">Adicionar peça</a>
            </div>
        </div>

        <form class="flex gap-3" method="GET">
            <input class="input" name="search" placeholder="Pesquisar por nome, categoria ou tamanho" value="{{ $search }}">
            <button class="btn-secondary">Pesquisar</button>
        </form>

        <div class="card overflow-hidden p-0">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Peça</th>
                        <th>Categoria</th>
                        <th>Tamanhos</th>
                        <th>Disponível</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($uniformItems as $item)
                        <tr>
                            <td class="font-semibold">{{ $item->name }}</td>
                            <td>{{ $item->category ?: '-' }}</td>
                            <td>
                                {{ $item->sizes->pluck('size')->join(', ') ?: ($item->size ?: '-') }}
                            </td>
                            <td>
                                <span class="badge {{ $item->isLowStock() ? 'danger' : '' }}">{{ $item->availableStock() }}</span>
                            </td>
                            <td>{{ $item->total_quantity }}</td>
                            <td>{{ ucfirst($item->condition) }}</td>
                            <td class="text-right"><a class="link" href="{{ route('uniform.items.show', $item) }}">Abrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Sem fardamento registado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $uniformItems->links() }}</div>
    </div>
@endsection
