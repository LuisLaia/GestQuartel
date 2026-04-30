@csrf
<div class="grid gap-5 md:grid-cols-2">
    <label class="block md:col-span-2">
        <span class="label">Nome</span>
        <input class="input" name="name" value="{{ old('name', $equipment->name) }}" required>
    </label>
    <label class="block">
        <span class="label">Categoria</span>
        <input class="input" name="category" placeholder="Ex.: EPI, fardamento, ferramenta" value="{{ old('category', $equipment->category) }}">
    </label>
    <label class="block">
        <span class="label">Tamanho</span>
        <input class="input" name="size" placeholder="Ex.: M, L, 42" value="{{ old('size', $equipment->size) }}">
    </label>
    <label class="block">
        <span class="label">Condição</span>
        <input class="input" name="condition" value="{{ old('condition', $equipment->condition ?: 'operacional') }}" required>
    </label>
    <label class="block">
        <span class="label">Quantidade total</span>
        <input class="input" type="number" min="0" name="total_quantity" value="{{ old('total_quantity', $equipment->total_quantity ?? 0) }}" required>
    </label>
    @if (! $equipment->exists)
        <label class="block">
            <span class="label">Quantidade disponível</span>
            <input class="input" type="number" min="0" name="available_quantity" value="{{ old('available_quantity', $equipment->available_quantity ?? 0) }}" required>
        </label>
    @else
        <div class="rounded-2xl bg-slate-100 p-4">
            <span class="label">Quantidade disponível</span>
            <p class="mt-1 text-2xl font-bold">{{ $equipment->available_quantity }}</p>
            <p class="text-sm text-slate-500">É ajustada automaticamente pelas atribuições/devoluções.</p>
        </div>
    @endif
    <label class="block">
        <span class="label">Alerta de stock mínimo</span>
        <input class="input" type="number" min="0" name="minimum_quantity" value="{{ old('minimum_quantity', $equipment->minimum_quantity ?? 0) }}" required>
    </label>
    <label class="block md:col-span-2">
        <span class="label">Notas</span>
        <textarea class="input min-h-28" name="notes">{{ old('notes', $equipment->notes) }}</textarea>
    </label>
</div>

<div class="mt-6 flex gap-3">
    <button class="btn-primary">Guardar</button>
    <a class="btn-secondary" href="{{ route('equipment.index') }}">Cancelar</a>
</div>
