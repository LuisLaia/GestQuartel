@extends('layouts.app', ['title' => 'Nova atribuicao - GestQuartel'])

@section('content')
    <div class="space-y-6">
        <div class="page-header">
            <div>
                <p class="eyebrow">Saida de stock</p>
                <h1 class="page-title">Nova atribuicao</h1>
            </div>
            <div class="flex gap-3"></div>
        </div>

        <form class="card" method="POST" action="{{ route('uniform.assignments.store') }}">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="label">Pessoa</span>
                    <select id="person_id" class="input" name="person_id" required>
                        <option value="">Selecionar pessoa</option>
                        @foreach ($people as $person)
                            <option value="{{ $person->id }}" @selected(old('person_id') == $person->id)>{{ $person->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="label">Peca de fardamento</span>
                    <select id="uniform_item_id" class="input" name="uniform_item_id" required>
                        <option value="">Selecionar peca</option>
                        @foreach ($uniformItems as $item)
                            <option value="{{ $item->id }}" @selected(old('uniform_item_id') == $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="label">Tamanho</span>
                    <select id="size" class="input" name="size" required data-old-value="{{ old('size') }}">
                        <option value="">Selecionar tamanho</option>
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

                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <button id="suggest_size_button" class="btn-secondary" type="button">
                            Sugerir tamanho
                        </button>
                    </div>

                    <input id="suggested_size" type="hidden" name="suggested_size" value="{{ old('suggested_size') }}">
                    <input id="chosen_size" type="hidden" name="chosen_size" value="{{ old('chosen_size') }}">
                    <p id="size_suggestion_message" class="mt-3 text-sm text-slate-500">
                        Selecione pessoa e peca para obter uma recomendacao.
                    </p>
                </div>

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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const personSelect = document.getElementById('person_id');
            const itemSelect = document.getElementById('uniform_item_id');
            const sizeSelect = document.getElementById('size');
            const suggestedInput = document.getElementById('suggested_size');
            const chosenInput = document.getElementById('chosen_size');
            const message = document.getElementById('size_suggestion_message');
            const button = document.getElementById('suggest_size_button');
            const suggestEndpoint = @json(route('uniform.assignments.suggest-size'));
            const sizesEndpointTemplate = @json(route('uniform.items.sizes', ['uniformItem' => '__ID__']));

            function selectSize(size) {
                if (!size) {
                    return false;
                }

                const option = Array.from(sizeSelect.options).find((option) => option.value === size && !option.disabled);

                if (!option) {
                    return false;
                }

                sizeSelect.value = size;
                chosenInput.value = size;

                return true;
            }

            async function loadSizes() {
                const itemId = itemSelect.value;
                sizeSelect.innerHTML = '<option value="">Selecionar tamanho</option>';
                chosenInput.value = '';

                if (!itemId) {
                    return;
                }

                const response = await fetch(sizesEndpointTemplate.replace('__ID__', itemId), {
                    headers: { 'Accept': 'application/json' },
                });
                const result = await response.json();

                result.sizes.forEach((itemSize) => {
                    const option = document.createElement('option');
                    option.value = itemSize.size;
                    option.textContent = itemSize.label;
                    option.disabled = itemSize.stock <= 0;
                    sizeSelect.appendChild(option);
                });

                const oldValue = sizeSelect.dataset.oldValue;
                if (oldValue) {
                    selectSize(oldValue);
                    sizeSelect.dataset.oldValue = '';
                }
            }

            async function suggestSize() {
                const personId = personSelect.value;
                const itemId = itemSelect.value;

                if (!personId || !itemId) {
                    message.textContent = 'Selecione pessoa e peca para obter uma recomendacao.';
                    suggestedInput.value = '';
                    return;
                }

                button.disabled = true;
                button.textContent = 'A sugerir...';

                try {
                    const url = new URL(suggestEndpoint);
                    url.searchParams.set('person_id', personId);
                    url.searchParams.set('uniform_item_id', itemId);

                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const result = await response.json();

                    suggestedInput.value = result.size || '';
                    message.textContent = result.message || 'Sem dados suficientes para recomendacao.';

                    if (result.size) {
                        selectSize(result.size);
                    }
                } catch (error) {
                    message.textContent = 'Nao foi possivel obter recomendacao.';
                } finally {
                    button.disabled = false;
                    button.textContent = 'Sugerir tamanho';
                }
            }

            button.addEventListener('click', suggestSize);
            personSelect.addEventListener('change', suggestSize);
            itemSelect.addEventListener('change', async function () {
                await loadSizes();
                await suggestSize();
            });
            sizeSelect.addEventListener('change', function () {
                chosenInput.value = sizeSelect.value;
            });

            if (itemSelect.value) {
                loadSizes().then(suggestSize);
            }
        });
    </script>
@endsection
