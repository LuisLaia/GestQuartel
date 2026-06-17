@extends('layouts.app', ['title' => 'Atribuicoes - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Fardamento entregue</p>
                <h1 class="page-title">Atribuicoes</h1>
            </div>
            <div class="flex gap-3">
                <a class="btn-primary" href="{{ route('uniform.assignments.create') }}">Nova atribuicao</a>
            </div>
        </div>

        <form method="GET" action="{{ route('uniform.assignments.index') }}" class="card">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div>
                    <label class="label" for="person_id">Pessoa</label>
                    <select id="person_id" name="person_id" class="input">
                        <option value="">Todas</option>
                        @foreach ($people as $person)
                            <option value="{{ $person->id }}" @selected((string) request('person_id') === (string) $person->id)>
                                {{ $person->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="item_id">Peca</label>
                    <select id="item_id" name="item_id" class="input">
                        <option value="">Todas</option>
                        @foreach ($uniformItems as $item)
                            <option value="{{ $item->id }}" @selected((string) request('item_id') === (string) $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="status">Estado</label>
                    <select id="status" name="status" class="input">
                        <option value="">Todos</option>
                        <option value="ativo" @selected(request('status') === 'ativo')>Ativo</option>
                        <option value="devolvido" @selected(request('status') === 'devolvido')>Devolvido</option>
                    </select>
                </div>

                <div>
                    <label class="label" for="date_from">Data inicio</label>
                    <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="input">
                </div>

                <div>
                    <label class="label" for="date_to">Data fim</label>
                    <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="input">
                </div>

                <div>
                    <label class="label" for="search">Pesquisa</label>
                    <input id="search" name="search" type="search" value="{{ request('search') }}" class="input" placeholder="Pessoa ou peca">
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-end gap-3">
                <a class="btn-secondary" href="{{ route('uniform.assignments.index') }}">Limpar filtros</a>
                <button class="btn-primary" type="submit">Filtrar</button>
            </div>
        </form>

        <div class="card overflow-hidden p-0">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Pessoa</th>
                        <th>Peca</th>
                        <th>Tamanho</th>
                        <th>Quantidade</th>
                        <th>Atribuido em</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td class="font-semibold">{{ $assignment->person?->name ?? 'Pessoa removida' }}</td>
                            <td>{{ $assignment->uniformItem?->name ?? 'Peca removida' }}</td>
                            <td>{{ $assignment->size ?: $assignment->chosen_size ?: '-' }}</td>
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
                                        @csrf
                                        @method('PATCH')
                                        <button class="link">Registar devolucao</button>
                                    </form>
                                @else
                                    <span class="text-sm text-slate-400">{{ $assignment->returned_at->format('d/m/Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">
                                {{ $hasFilters ? 'Sem resultados para os filtros selecionados.' : 'Sem atribuicoes registadas.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $assignments->links() }}</div>
    </div>
@endsection
