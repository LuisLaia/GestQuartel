@extends('layouts.app', ['title' => 'Nova atribuição - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Saída de stock</p>
                <h1 class="page-title">Nova atribuição</h1>
            </div>
            <div class="flex gap-3"></div>
        </div>

        <form class="card" method="POST" action="{{ route('uniform.assignments.store') }}">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="label">Pessoa</span>
                    <select class="input" name="person_id" required>
                        <option value="">Selecionar pessoa</option>
                        @foreach ($people as $person)
                            <option value="{{ $person->id }}" @selected(old('person_id') == $person->id)>{{ $person->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="label">Peça de fardamento</span>
                    <select class="input" name="uniform_item_id" required>
                        <option value="">Selecionar peça</option>
                        @foreach ($uniformItems as $item)
                            <option value="{{ $item->id }}" @selected(old('uniform_item_id') == $item->id)>
                                {{ $item->name }} - {{ $item->available_quantity }} disponível
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="label">Quantidade</span>
                    <input class="input" type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" required>
                </label>
                <label class="block">
                    <span class="label">Data de atribuição</span>
                    <input class="input" type="date" name="assigned_at" value="{{ old('assigned_at', now()->toDateString()) }}" required>
                </label>
                <label class="block md:col-span-2">
                    <span class="label">Notas</span>
                    <textarea class="input min-h-28" name="notes">{{ old('notes') }}</textarea>
                </label>
            </div>

            <div class="mt-6 flex gap-3">
                <button class="btn-primary">Atribuir e baixar stock</button>
                <a class="btn-secondary" href="{{ route('uniform.assignments.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
