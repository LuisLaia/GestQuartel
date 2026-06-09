@extends('layouts.app', ['title' => 'Bombeiros API - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Preventech</p>
            <h2 class="page-title">Bombeiros API</h2>
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
                    <th>Nome</th>
                    <th>Numero</th>
                    <th>NIF</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($firefighters as $firefighter)
                    <tr>
                        <td class="font-semibold">{{ data_get($firefighter, 'name') ?? data_get($firefighter, 'nome') ?? '-' }}</td>
                        <td>{{ data_get($firefighter, 'number') ?? data_get($firefighter, 'numero') ?? data_get($firefighter, 'id') ?? '-' }}</td>
                        <td>{{ data_get($firefighter, 'nif') ?? '-' }}</td>
                        <td>{{ data_get($firefighter, 'email') ?? '-' }}</td>
                        <td>{{ data_get($firefighter, 'phone') ?? data_get($firefighter, 'telefone') ?? data_get($firefighter, 'telemovel') ?? '-' }}</td>
                        <td>
                            <span class="badge">
                                {{ data_get($firefighter, 'status') ?? data_get($firefighter, 'estado') ?? '-' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            {{ $error ? 'Nao foi possivel carregar dados da Preventech.' : 'Sem bombeiros devolvidos pela Preventech.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
