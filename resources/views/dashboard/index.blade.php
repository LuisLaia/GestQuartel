@extends('layouts.app', ['title' => 'Dashboard - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Resumo operacional</p>
                <h1 class="page-title">Painel de Controlo</h1>
            </div>
            <div class="flex gap-3"></div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="card bg-rose-600 text-white">
                <p>Total pessoal</p>
                <h2 class="text-3xl font-bold">{{ $peopleCount }}</h2>
            </div>

            <div class="card">
                <p>Total veículos</p>
                <h2 class="text-3xl font-bold">{{ $vehiclesCount }}</h2>
            </div>

            <div class="card">
                <p>Total peças de fardamento</p>
                <h2 class="text-3xl font-bold">{{ $uniformItemCount }}</h2>
            </div>

            <div class="card">
                <p>Stock baixo</p>
                <h2 class="text-3xl font-bold">{{ $lowStockCount }}</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="card">
                <h3 class="section-title mb-4">Estado dos Veículos</h3>
                <div class="relative h-72">
                    <canvas id="vehiclesChart"></canvas>
                </div>
            </div>

            <div class="card">
                <h3 class="section-title mb-4">Stock por Peça</h3>
                <div class="relative h-72">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="section-title">Motoristas mais eficientes</h3>

                    @if ($bestDriver)
                        <p class="mt-2 text-sm font-semibold text-emerald-700">
                            Motorista mais eficiente:
                            {{ $bestDriver->driver_name }}
                            ({{ number_format((float) $bestDriver->consumption, 1, ',', ' ') }} L/100km)
                        </p>
                    @else
                        <p class="mt-2 text-sm text-slate-500">
                            Sem viagens válidas para calcular eficiência.
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Km</th>
                                <th>Combustível</th>
                                <th>L/100km</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($ecoDrivers as $driver)
                                <tr>
                                    <td>{{ $driver->driver_name }}</td>
                                    <td>{{ number_format((float) $driver->total_km, 0, ',', ' ') }} km</td>
                                    <td>{{ number_format((float) $driver->total_fuel, 1, ',', ' ') }} L</td>
                                    <td class="font-semibold text-emerald-700">
                                        {{ number_format((float) $driver->consumption, 1, ',', ' ') }} L/100km
                                    </td>
                                </tr>
                            @endforeach

                            @if ($ecoDrivers->isEmpty())
                                <tr>
                                    <td colspan="4" class="empty">
                                        Sem motoristas com viagens válidas.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="relative h-72">
                    <canvas id="ecoDriversChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="section-title mb-4">Stock baixo</h3>

            @forelse ($lowStockUniformItems as $item)
                <div class="flex justify-between border-b py-2">
                    <span>{{ $item->name }}</span>
                    <span class="font-bold text-red-600">
                        {{ $item->available_quantity }}
                    </span>
                </div>
            @empty
                <p class="empty">Sem alertas de stock.</p>
            @endforelse
        </div>

        <div class="card">
            <h3 class="section-title mb-4">Atividade recente</h3>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <h4 class="mb-3 font-semibold text-slate-700">Últimos veículos criados</h4>

                    @forelse ($recentVehicles as $vehicle)
                        <div class="border-b py-3">
                            <p class="font-semibold">{{ $vehicle->name }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $vehicle->plate ?: 'Sem matrícula' }} · {{ $vehicle->type ?: 'Sem tipo' }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $vehicle->created_at?->format('d/m/Y') }}
                            </p>
                        </div>
                    @empty
                        <p class="empty">Sem veículos encontrados.</p>
                    @endforelse
                </div>

                <div>
                    <h4 class="mb-3 font-semibold text-slate-700">Últimas entradas de stock</h4>

                    @forelse ($recentStockEntries as $item)
                        <div class="border-b py-3">
                            <p class="font-semibold">{{ $item->name }}</p>
                            <p class="text-sm text-gray-500">
                                Stock total: {{ $item->total_quantity }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $item->created_at?->format('d/m/Y') }}
                            </p>
                        </div>
                    @empty
                        <p class="empty">Sem histórico disponível.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textColor = '#1f2937';
            const gridColor = '#e5e7eb';

            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                            font: {
                                family: 'Figtree, sans-serif',
                            },
                        },
                    },
                },
            };

            new Chart(document.getElementById('vehiclesChart'), {
                type: 'pie',
                data: {
                    labels: ['Operacionais', 'Manutenção', 'Inativos'],
                    datasets: [{
                        data: [
                            {{ $vehiclesOperational }},
                            {{ $vehiclesMaintenance }},
                            {{ $vehiclesInactive }},
                        ],
                        backgroundColor: ['#059669', '#d97706', '#64748b'],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                    }],
                },
                options: chartDefaults,
            });

            new Chart(document.getElementById('stockChart'), {
                type: 'bar',
                data: {
                    labels: @json($uniformStock->pluck('name')->values()),
                    datasets: [{
                        label: 'Quantidade disponível',
                        data: @json($uniformStock->pluck('available_quantity')->values()),
                        backgroundColor: '#dc2626',
                        borderRadius: 8,
                    }],
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        x: {
                            ticks: { color: textColor },
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: textColor, precision: 0 },
                            grid: { color: gridColor },
                        },
                    },
                },
            });

            new Chart(document.getElementById('ecoDriversChart'), {
                type: 'bar',
                data: {
                    labels: @json($ecoDrivers->pluck('driver_name')->values()),
                    datasets: [{
                        label: 'L/100km',
                        data: @json($ecoDrivers->pluck('consumption')->values()),
                        backgroundColor: '#059669',
                        borderRadius: 8,
                    }],
                },
                options: {
                    ...chartDefaults,
                    indexAxis: 'y',
                    plugins: {
                        ...chartDefaults.plugins,
                        legend: {
                            display: false,
                            labels: {
                                color: textColor,
                            },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { color: textColor },
                            grid: { color: gridColor },
                        },
                        y: {
                            ticks: { color: textColor },
                            grid: { display: false },
                        },
                    },
                },
            });

        });
    </script>
@endsection
