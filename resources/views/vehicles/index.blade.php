<x-app-layout>
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Operacionais e equipa</p>
                <h1 class="page-title">Veículos</h1>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('vehicles.create') }}" class="btn-primary">
                    Adicionar veículo
                </a>
            </div>
        </div>

        <form class="flex gap-3" method="GET">
            <input
                class="input"
                name="search"
                placeholder="Pesquisar por nome, matrícula ou tipo"
                value="{{ request('search') }}"
            >
            <button class="btn-secondary">Pesquisar</button>
        </form>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="card">
                <p class="label">Total veículos</p>
                <p class="mt-1 text-2xl font-bold">{{ $stats['total'] }}</p>
            </div>
            <div class="card">
                <p class="label">Operacionais</p>
                <p class="mt-1 text-2xl font-bold">{{ $stats['operational'] }}</p>
            </div>
            <div class="card">
                <p class="label">Em manutenção</p>
                <p class="mt-1 text-2xl font-bold">{{ $stats['maintenance'] }}</p>
            </div>
            <div class="card">
                <p class="label">Inativos</p>
                <p class="mt-1 text-2xl font-bold">{{ $stats['inactive'] }}</p>
            </div>
        </div>

        <div class="card overflow-hidden p-0">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Matrícula</th>
                        <th>Tipo</th>
                        <th>Ano</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->name }}</td>
                            <td>{{ $vehicle->plate }}</td>
                            <td>{{ $vehicle->type }}</td>
                            <td>{{ $vehicle->year }}</td>
                            <td>
                                <span class="{{ $vehicle->status === 'operacional'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-slate-200 text-slate-600' }} inline-flex rounded-full px-3 py-1 text-xs font-semibold">
                                    {{ ucfirst($vehicle->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="text-rose-600">
                                    Abrir
                                </a>
                            </td>
                        </tr>
                    @endforeach

                    @if ($vehicles->isEmpty())
                        <tr>
                            <td colspan="6" class="empty">
                                Sem veículos encontrados.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
