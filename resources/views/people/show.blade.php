@extends('layouts.app', ['title' => $person->name . ' - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Ficha individual</p>
                <h1 class="page-title">{{ $person->name }}</h1>
            </div>
            <div class="flex gap-3">
                <a class="btn-secondary" href="{{ route('personnel.edit', $person) }}">Editar</a>
                <form method="POST" action="{{ route('personnel.destroy', $person) }}" onsubmit="return confirm('Remover esta pessoa?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-danger">Remover</button>
                </form>
            </div>
        </div>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="card xl:col-span-1">
                <h3 class="section-title">Dados</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="label">Número</dt><dd>{{ $person->mechanographic_number ?: $person->number ?: '-' }}</dd></div>
                    <div><dt class="label">Posto/Função</dt><dd>{{ $person->rank ?: '-' }}</dd></div>
                    <div><dt class="label">Email</dt><dd>{{ $person->email ?: '-' }}</dd></div>
                    <div><dt class="label">Telefone</dt><dd>{{ $person->phone ?: '-' }}</dd></div>
                    <div><dt class="label">Estado</dt><dd>{{ ucfirst($person->status) }}</dd></div>
                    <div><dt class="label">Cintura</dt><dd>{{ $person->waist_cm ? $person->waist_cm . ' cm' : '-' }}</dd></div>
                    <div><dt class="label">Peito</dt><dd>{{ $person->chest_cm ? $person->chest_cm . ' cm' : '-' }}</dd></div>
                    <div><dt class="label">Altura</dt><dd>{{ $person->height_cm ? $person->height_cm . ' cm' : '-' }}</dd></div>
                </dl>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-slate-100 p-3">
                        <p class="label">Peças atribuídas</p>
                        <p class="text-2xl font-bold">{{ $person->assignments->sum('quantity') }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-100 p-3">
                        <p class="label">Devoluções</p>
                        <p class="text-2xl font-bold">{{ $person->assignments->whereNotNull('returned_at')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="card xl:col-span-2">
                <h3 class="section-title">Equipamento Atual</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($person->assignments->whereNull('returned_at') as $assignment)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="font-semibold">{{ $assignment->uniformItem?->name ?? 'Peça removida' }} x {{ $assignment->quantity }}</p>
                            <p class="text-sm text-slate-500">{{ $assignment->assigned_at->format('d/m/Y') }} · Ativo</p>
                        </div>
                    @empty
                        <p class="empty">Sem atribuições ativas.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="card overflow-hidden p-0">
            <div class="p-6 pb-0">
                <h3 class="section-title">Histórico</h3>
            </div>
            <table class="table w-full mt-4">
                <thead>
                    <tr>
                        <th>Peça</th>
                        <th>Tamanho</th>
                        <th>Quantidade</th>
                        <th>Data de atribuição</th>
                        <th>Data de devolução</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($person->assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->uniformItem?->name ?? 'Peça removida' }}</td>
                            <td>{{ $assignment->chosen_size ?: $assignment->suggested_size ?: '-' }}</td>
                            <td>{{ $assignment->quantity }}</td>
                            <td>{{ $assignment->assigned_at->format('d/m/Y') }}</td>
                            <td>{{ $assignment->returned_at?->format('d/m/Y') ?: '-' }}</td>
                            <td><span class="badge {{ $assignment->returned_at ? 'muted' : '' }}">{{ $assignment->returned_at ? 'Devolvido' : 'Ativo' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Sem histórico disponível.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection
