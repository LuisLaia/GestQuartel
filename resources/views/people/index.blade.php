@extends('layouts.app', ['title' => 'Pessoas - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Operacionais e equipa</p>
                <h1 class="page-title">Pessoas</h1>
            </div>
            <div class="flex gap-3">
                <a class="btn-primary" href="{{ route('personnel.create') }}">Adicionar pessoa</a>
            </div>
        </div>

        <form class="flex gap-3" method="GET">
            <input class="input" name="search" placeholder="Pesquisar por nome, número, email ou posto" value="{{ $search }}">
            <button class="btn-secondary">Pesquisar</button>
        </form>

        <div class="card overflow-hidden p-0">
            <table class="table w-full">
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
                            <td>{{ $person->mechanographic_number ?: $person->number ?: '-' }}</td>
                            <td>{{ $person->rank ?: '-' }}</td>
                            <td>{{ $person->phone ?: $person->email ?: '-' }}</td>
                            <td><span class="badge">{{ ucfirst($person->status) }}</span></td>
                            <td class="text-right"><a class="link" href="{{ route('personnel.show', $person) }}">Abrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Sem pessoas registadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $people->links() }}</div>
    </div>
@endsection
