<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="page-header">
                <div>
                    <p class="eyebrow">Operacionais e equipa</p>
                    <h2 class="page-title">Veículos</h2>
                </div>

                <a href="{{ route('vehicles.create') }}" class="btn-primary">
                    Adicionar veículo
                </a>
            </div>

            @if (session('success'))
                <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form class="mb-5 flex gap-3" method="GET">
                <input
                    class="input"
                    name="search"
                    placeholder="Pesquisar por nome, matrícula ou tipo"
                    value="{{ request('search') }}"
                >
                <button class="btn-secondary">Pesquisar</button>
            </form>

            <div class="card overflow-hidden p-0">
                <table class="table">
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
                                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-rose-600">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        @if ($vehicles->isEmpty())
                            <tr>
                                <td colspan="6" class="empty">
                                    Ainda não existem veículos registados.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
