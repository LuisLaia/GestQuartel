@csrf
<div class="grid gap-5 md:grid-cols-2">
    <label class="block md:col-span-2">
        <span class="label">Nome</span>
        <input class="input" name="name" value="{{ old('name', $uniformItem->name) }}" required>
    </label>
    <label class="block">
        <span class="label">Categoria</span>
        <input class="input" name="category" placeholder="Ex.: polo, bone, casaco" value="{{ old('category', $uniformItem->category) }}">
    </label>
    <label class="block">
        <span class="label">Tamanho inicial</span>
        <input class="input" name="size" placeholder="Ex.: M, L, 42" value="{{ old('size', $uniformItem->size) }}">
    </label>
    <label class="block">
        <span class="label">Condicao</span>
        <input class="input" name="condition" value="{{ old('condition', $uniformItem->condition ?: 'operacional') }}" required>
    </label>
    <label class="block">
        <span class="label">Quantidade total</span>
        <input class="input" type="number" min="0" name="total_quantity" value="{{ old('total_quantity', $uniformItem->total_quantity ?? 0) }}" required>
    </label>
    @if (! $uniformItem->exists)
        <label class="block">
            <span class="label">Quantidade disponivel</span>
            <input class="input" type="number" min="0" name="available_quantity" value="{{ old('available_quantity', $uniformItem->available_quantity ?? 0) }}" required>
        </label>
    @else
        <div class="rounded-2xl bg-slate-100 p-4">
            <span class="label">Quantidade disponivel</span>
            <p class="mt-1 text-2xl font-bold">{{ $uniformItem->available_quantity }}</p>
            <p class="text-sm text-slate-500">Ajustada automaticamente pelas atribuicoes/devolucoes.</p>
        </div>
    @endif
    <label class="block">
        <span class="label">Alerta de stock minimo</span>
        <input class="input" type="number" min="0" name="minimum_quantity" value="{{ old('minimum_quantity', $uniformItem->minimum_quantity ?? 0) }}" required>
    </label>
    <label class="block md:col-span-2">
        <span class="label">Notas</span>
        <textarea class="input min-h-28" name="notes">{{ old('notes', $uniformItem->notes) }}</textarea>
    </label>
</div>

<div class="mt-6 flex gap-3">
    <button class="btn-primary">Guardar</button>
    <a class="btn-secondary" href="{{ route('uniform.items.index') }}">Cancelar</a>
</div>
