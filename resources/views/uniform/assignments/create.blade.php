@extends('layouts.app', ['title' => 'Nova atribuicao - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Saida de stock</p>
            <h2 class="page-title">Nova atribuicao</h2>
        </div>
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
                <span class="label">Peca de fardamento</span>
                <select class="input" name="uniform_item_id" required>
                    <option value="">Selecionar peca</option>
                    @foreach ($uniformItems as $item)
                        <option value="{{ $item->id }}" @selected(old('uniform_item_id') == $item->id)>
                            {{ $item->name }} - {{ $item->available_quantity }} disponivel
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="label">Quantidade</span>
                <input class="input" type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" required>
            </label>
            <label class="block">
                <span class="label">Data de atribuicao</span>
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
@endsection
