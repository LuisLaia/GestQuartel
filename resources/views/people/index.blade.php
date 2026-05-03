@extends('layouts.app', ['title' => 'Pessoas - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Operacionais e equipa</p>
            <h2 class="page-title">Pessoas</h2>
        </div>
        <a class="btn-primary" href="{{ route('personnel.create') }}">Adicionar pessoa</a>
    </div>

    <form class="mb-5 flex gap-3" method="GET">
        <input class="input" name="search" placeholder="Pesquisar por nome, número, email ou posto" value="{{ $search }}">
        <button class="btn-secondary">Pesquisar</button>
    </form>

    <div class="card overflow-hidden p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Número</th>
                    <th>Posto</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($people as $person)
                    <tr>
                        <td class="font-semibold">{{ $person->name }}</td>
                        <td>{{ $person->number ?: '—' }}</td>
                        <td>{{ $person->rank ?: '—' }}</td>
                        <td>{{ $person->phone ?: $person->email ?: '—' }}</td>
                        <td><span class="badge">{{ ucfirst($person->status) }}</span></td>
                        <td class="text-right"><a class="link" href="{{ route('personnel.show', $person) }}">Abrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Sem pessoas registadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $people->links() }}</div>
@endsection
