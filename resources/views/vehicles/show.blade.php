@extends('layouts.app', ['title' => $vehicle->name . ' - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Ficha de veículo</p>
                <h1 class="page-title">{{ $vehicle->name }}</h1>
            </div>
            <div class="flex gap-3">
                <a class="btn-secondary" href="{{ route('vehicles.edit', $vehicle) }}">Editar</a>
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Eliminar veículo?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-danger">Eliminar</button>
                </form>
            </div>
        </div>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="card xl:col-span-1">
                <h3 class="section-title">Dados</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="label">Nome</dt><dd>{{ $vehicle->name }}</dd></div>
                    <div><dt class="label">Matrícula</dt><dd>{{ $vehicle->plate ?: '-' }}</dd></div>
                    <div><dt class="label">Tipo</dt><dd>{{ $vehicle->type ?: '-' }}</dd></div>
                    <div><dt class="label">Ano</dt><dd>{{ $vehicle->year ?: '-' }}</dd></div>
                    <div><dt class="label">Estado</dt><dd>{{ ucfirst($vehicle->status) }}</dd></div>
                    <div>
                        <dt class="label">Total km</dt>
                        <dd>{{ number_format((float) $vehicle->total_km, 0, ',', ' ') }} km</dd>
                    </div>
                    <div>
                        <dt class="label">Consumo médio</dt>
                        <dd>
                            @if ($vehicle->average_consumption !== null)
                                {{ number_format((float) $vehicle->average_consumption, 2, ',', ' ') }} L/100km
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="card xl:col-span-2">
                <h3 class="section-title">Observações</h3>
                <p class="mt-4 text-sm text-slate-600">{{ $vehicle->notes ?: 'Sem observações registadas.' }}</p>
            </div>
        </section>
    </div>
@endsection
