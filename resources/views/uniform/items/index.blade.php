@extends('layouts.app', ['title' => 'Fardamento - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Inventario</p>
            <h2 class="page-title">Fardamento</h2>
        </div>
        <a class="btn-primary" href="{{ route('uniform.items.create') }}">Adicionar peca</a>
    </div>

    <form class="mb-5 flex gap-3" method="GET">
        <input class="input" name="search" placeholder="Pesquisar por nome, categoria ou tamanho" value="{{ $search }}">
        <button class="btn-secondary">Pesquisar</button>
    </form>

    <div class="card overflow-hidden p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Peca</th>
                    <th>Categoria</th>
                    <th>Tamanho</th>
                    <th>Disponivel</th>
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
                        <td>{{ $item->size ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $item->isLowStock() ? 'danger' : '' }}">{{ $item->available_quantity }}</span>
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

    <div class="mt-5">{{ $uniformItems->links() }}</div>
@endsection
