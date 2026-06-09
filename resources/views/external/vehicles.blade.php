@extends('layouts.app', ['title' => 'Veiculos API - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Preventech</p>
            <h2 class="page-title">Veiculos API</h2>
        </div>
    </div>

    @if ($error)
        <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $error }}
        </div>
    @endif

    <div class="card overflow-x-auto p-0">
        <table class="table min-w-full">
            <thead>
                <tr>
                    <th>Matricula</th>
                    <th>Nome</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vehicles as $vehicle)
                    <tr>
                        <td class="font-semibold">{{ data_get($vehicle, 'license_plate') ?? data_get($vehicle, 'matricula') ?? data_get($vehicle, 'plate') ?? '-' }}</td>
                        <td>{{ data_get($vehicle, 'name') ?? data_get($vehicle, 'nome') ?? '-' }}</td>
                        <td>{{ data_get($vehicle, 'brand') ?? data_get($vehicle, 'marca') ?? '-' }}</td>
                        <td>{{ data_get($vehicle, 'model') ?? data_get($vehicle, 'modelo') ?? '-' }}</td>
                        <td>{{ data_get($vehicle, 'type') ?? data_get($vehicle, 'tipo') ?? '-' }}</td>
                        <td>
                            <span class="badge">
                                {{ data_get($vehicle, 'status') ?? data_get($vehicle, 'estado') ?? '-' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            {{ $error ? 'Nao foi possivel carregar dados da Preventech.' : 'Sem veiculos devolvidos pela Preventech.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
