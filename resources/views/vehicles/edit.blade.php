<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Editar Veículo</h2>
    </x-slot>

    <div class="card mt-6">
        <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block md:col-span-2">
                    <span class="label">Nome</span>
                    <input class="input" name="name" value="{{ old('name', $vehicle->name) }}" required>
                </label>

                <label class="block">
                    <span class="label">Matrícula</span>
                    <input class="input" name="plate" value="{{ old('plate', $vehicle->plate) }}">
                </label>

                <label class="block">
                    <span class="label">Tipo</span>
                    <input class="input" name="type" value="{{ old('type', $vehicle->type) }}">
                </label>

                <label class="block">
                    <span class="label">Ano</span>
                    <input class="input" type="number" name="year" value="{{ old('year', $vehicle->year) }}">
                </label>

                <label class="block">
                    <span class="label">Estado</span>
                    <select class="input" name="status" required>
                        @foreach (['operacional' => 'Operacional', 'manutencao' => 'Manutenção', 'inativo' => 'Inativo'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $vehicle->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="mt-6 flex gap-3">
                <button class="btn-primary">Guardar</button>
                <a class="btn-secondary" href="{{ route('vehicles.index') }}">Cancelar</a>
            </div>
        </form>

        <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" class="mt-4">
            @csrf
            @method('DELETE')

            <button class="btn-danger" onclick="return confirm('Eliminar veículo?')">
                Eliminar veículo
            </button>
        </form>
    </div>
</x-app-layout>
