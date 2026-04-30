@extends('layouts.app', ['title' => 'Dashboard - GestQuartel'])

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <p class="eyebrow">Visão geral</p>
            <h2 class="page-title">Dashboard</h2>
        </div>
        <a href="{{ route('assignments.create') }}" class="btn-primary">Nova atribuição</a>
    </div>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
        <x-stat-card label="Pessoas" :value="$peopleCount" />
        <x-stat-card label="Pessoas ativas" :value="$activePeopleCount" />
        <x-stat-card label="Equipamentos" :value="$equipmentCount" />
        <x-stat-card label="Atribuições ativas" :value="$activeAssignmentsCount" />
        <x-stat-card label="Stock baixo" :value="$lowStockCount" tone="danger" />
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2">
        <div class="card">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="section-title">Últimas atribuições</h3>
                <a class="text-sm font-semibold text-red-700" href="{{ route('assignments.index') }}">Ver todas</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentAssignments as $assignment)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="font-semibold">{{ $assignment->equipment->name }} × {{ $assignment->quantity }}</p>
                        <p class="text-sm text-slate-500">{{ $assignment->person->name }} · {{ $assignment->assigned_at->format('d/m/Y') }}</p>
                    </div>
                @empty
                    <p class="empty">Ainda não existem atribuições.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="section-title">Equipamento com stock baixo</h3>
                <a class="text-sm font-semibold text-red-700" href="{{ route('equipment.index') }}">Inventário</a>
            </div>
            <div class="space-y-3">
                @forelse ($lowStockEquipment as $item)
                    <div class="flex items-center justify-between rounded-2xl border border-red-100 bg-red-50 p-4">
                        <div>
                            <p class="font-semibold">{{ $item->name }}</p>
                            <p class="text-sm text-red-700">{{ $item->category ?: 'Sem categoria' }}</p>
                        </div>
                        <span class="badge danger">{{ $item->available_quantity }} disp.</span>
                    </div>
                @empty
                    <p class="empty">Sem alertas de stock. Pequena vitória logística.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
