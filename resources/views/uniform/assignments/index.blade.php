@extends('layouts.app', ['title' => 'Atribuicoes - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Fardamento entregue</p>
            <h2 class="page-title">Atribuicoes</h2>
        </div>
        <a class="btn-primary" href="{{ route('uniform.assignments.create') }}">Nova atribuicao</a>
    </div>

    <div class="card overflow-hidden p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Pessoa</th>
                    <th>Peca</th>
                    <th>Quantidade</th>
                    <th>Atribuido em</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td class="font-semibold">{{ $assignment->person->name }}</td>
                        <td>{{ $assignment->uniformItem->name }}</td>
                        <td>{{ $assignment->quantity }}</td>
                        <td>{{ $assignment->assigned_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $assignment->isActive() ? '' : 'muted' }}">
                                {{ $assignment->isActive() ? 'Ativo' : 'Devolvido' }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if ($assignment->isActive())
                                <form method="POST" action="{{ route('uniform.assignments.return', $assignment) }}">
                                    @csrf @method('PATCH')
                                    <button class="link">Registar devolucao</button>
                                </form>
                            @else
                                <span class="text-sm text-slate-400">{{ $assignment->returned_at->format('d/m/Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Sem atribuicoes registadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $assignments->links() }}</div>
@endsection
