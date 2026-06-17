@csrf
<div class="grid gap-5 md:grid-cols-2">
    <label class="block md:col-span-2">
        <span class="label">Nome</span>
        <input class="input" name="name" value="{{ old('name', $person->name) }}" required>
    </label>
    <label class="block">
        <span class="label">Número interno</span>
        <input class="input" name="number" value="{{ old('number', $person->number) }}">
    </label>
    <label class="block">
        <span class="label">Posto/Função</span>
        <input class="input" name="rank" value="{{ old('rank', $person->rank) }}">
    </label>
    <label class="block">
        <span class="label">Email</span>
        <input class="input" type="email" name="email" value="{{ old('email', $person->email) }}">
    </label>
    <label class="block">
        <span class="label">Telefone</span>
        <input class="input" name="phone" value="{{ old('phone', $person->phone) }}">
    </label>
    <label class="block">
        <span class="label">Estado</span>
        <select class="input" name="status" required>
            @foreach (['ativo' => 'Ativo', 'inativo' => 'Inativo', 'suspenso' => 'Suspenso'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $person->status ?: 'ativo') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="block">
        <span class="label">Data de entrada</span>
        <input class="input" type="date" name="joined_at" value="{{ old('joined_at', optional($person->joined_at)->format('Y-m-d')) }}">
    </label>
    <div class="md:col-span-2">
        <p class="label">Medidas corporais</p>
        <div class="grid gap-5 md:grid-cols-5">
            <label class="block">
                <span class="label">Cintura cm</span>
                <input class="input" type="number" min="1" max="300" name="waist_cm" value="{{ old('waist_cm', $person->waist_cm) }}">
            </label>
            <label class="block">
                <span class="label">Peito cm</span>
                <input class="input" type="number" min="1" max="300" name="chest_cm" value="{{ old('chest_cm', $person->chest_cm) }}">
            </label>
            <label class="block">
                <span class="label">Altura cm</span>
                <input class="input" type="number" min="1" max="250" name="height_cm" value="{{ old('height_cm', $person->height_cm) }}">
            </label>
            <label class="block">
                <span class="label">Perna cm</span>
                <input class="input" type="number" min="1" max="200" name="leg_cm" value="{{ old('leg_cm', $person->leg_cm) }}">
            </label>
            <label class="block">
                <span class="label">Braco cm</span>
                <input class="input" type="number" min="1" max="200" name="arm_cm" value="{{ old('arm_cm', $person->arm_cm) }}">
            </label>
        </div>
    </div>
    <label class="block md:col-span-2">
        <span class="label">Notas</span>
        <textarea class="input min-h-28" name="notes">{{ old('notes', $person->notes) }}</textarea>
    </label>
</div>

<div class="mt-6 flex gap-3">
    <button class="btn-primary">Guardar</button>
    <a class="btn-secondary" href="{{ route('personnel.index') }}">Cancelar</a>
</div>
