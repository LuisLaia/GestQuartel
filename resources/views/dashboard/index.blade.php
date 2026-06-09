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

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="card">
                <h3 class="section-title mb-4">Veículos</h3>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span>Operacionais</span>
                        <span class="font-semibold text-emerald-700">{{ $activeVehicles }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Manutenção</span>
                        <span class="font-semibold text-amber-700">{{ $maintenanceVehicles }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Inativos</span>
                        <span class="font-semibold text-slate-600">{{ $inactiveVehicles }}</span>
                    </div>
                </div>
            </div>

            <div class="card lg:col-span-2">
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
        </div>

        <div class="card">
            <h3 class="section-title mb-4">Atividade recente</h3>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div>
                    <h4 class="mb-3 font-semibold text-slate-700">Últimas atribuições</h4>

                    @forelse ($recentAssignments as $assignment)
                        <div class="border-b py-3">
                            <p class="font-semibold">
                                {{ $assignment->person?->name ?? 'Pessoa removida' }}
                            </p>

                            <p class="text-sm text-gray-500">
                                {{ $assignment->uniformItem?->name ?? 'Peça removida' }} ·
                                {{ $assignment->quantity }} unidade(s)
                            </p>

                            <p class="text-xs text-gray-400">
                                {{ $assignment->assigned_at?->format('d/m/Y') }}
                            </p>
                        </div>
                    @empty
                        <p class="empty">Sem atribuições registadas.</p>
                    @endforelse
                </div>

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
@endsection
