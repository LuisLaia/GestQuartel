<x-app-layout>
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Cartrack e consumo</p>
                <h1 class="page-title">Viagens</h1>
            </div>
        </div>

        @include('layouts.partials.flash')

        <div class="card">
            <form class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_1fr_auto]" method="POST" action="{{ route('trips.sync') }}">
                @csrf

                <div>
                    <label class="label" for="start_date">Data inicial</label>
                    <input
                        id="start_date"
                        class="input"
                        type="date"
                        name="start_date"
                        value="{{ old('start_date', now()->subDays(7)->toDateString()) }}"
                    >
                </div>

                <div>
                    <label class="label" for="end_date">Data final</label>
                    <input
                        id="end_date"
                        class="input"
                        type="date"
                        name="end_date"
                        value="{{ old('end_date', now()->toDateString()) }}"
                    >
                </div>

                <div class="flex items-end">
                    <button class="btn-primary w-full lg:w-auto" type="submit">
                        Sincronizar Cartrack
                    </button>
                </div>
            </form>
        </div>

        <form class="flex gap-3" method="GET">
            <input
                class="input"
                name="search"
                placeholder="Pesquisar por viatura, matrícula ou motorista"
                value="{{ $search }}"
            >
            <button class="btn-secondary">Pesquisar</button>
        </form>

        <div class="card overflow-hidden p-0">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Viatura</th>
                        <th>Motorista</th>
                        <th>Distância</th>
                        <th>Combustível</th>
                        <th>Consumo</th>
                        <th>Data</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($trips as $trip)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $trip->vehicle?->name ?? 'Viatura removida' }}</div>
                                <div class="text-xs text-slate-500">{{ $trip->vehicle?->plate }}</div>
                            </td>
                            <td>{{ $trip->driver_name ?: 'Sem motorista' }}</td>
                            <td>{{ number_format((float) $trip->distance_km, 2, ',', ' ') }} km</td>
                            <td>{{ number_format((float) $trip->fuel_used_liters, 2, ',', ' ') }} L</td>
                            <td>
                                @if ($trip->consumption !== null)
                                    {{ number_format((float) $trip->consumption, 2, ',', ' ') }} L/100km
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $trip->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach

                    @if ($trips->isEmpty())
                        <tr>
                            <td colspan="6" class="empty">
                                Sem viagens registadas.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
