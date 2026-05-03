<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">
            Painel de Controlo
        </h2>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- KPIs --}}
        <div class="grid grid-cols-4 gap-6">
            <div class="card bg-rose-600 text-white">
                <p>Pessoal Ativo</p>
                <h2 class="text-3xl font-bold">{{ $activePeopleCount }}</h2>
            </div>

            <div class="card">
                <p>Pessoal Inativo</p>
                <h2 class="text-3xl font-bold">
                    {{ $peopleCount - $activePeopleCount }}
                </h2>
            </div>

            <div class="card">
                <p>Fardamento</p>
                <h2 class="text-3xl font-bold">{{ $uniformItemCount }}</h2>
            </div>

            <div class="card">
                <p>Atribuições Ativas</p>
                <h2 class="text-3xl font-bold">{{ $activeAssignmentsCount }}</h2>
            </div>
        </div>

        {{-- STOCK BAIXO --}}
        <div class="card">
            <h3 class="section-title mb-4">Stock baixo</h3>

            @forelse ($lowStockUniformItems as $item)
                <div class="flex justify-between border-b py-2">
                    <span>{{ $item->name }}</span>
                    <span class="text-red-600 font-bold">
                        {{ $item->available_quantity }}
                    </span>
                </div>
            @empty
                <p class="empty">Sem alertas de stock.</p>
            @endforelse
        </div>

        {{-- RECENTES --}}
        <div class="grid grid-cols-2 gap-6">

            {{-- PESSOAL --}}
            <div class="card">
                <h3 class="section-title mb-4">Elementos recentes</h3>

                @foreach ($recentPersonnel as $person)
                    <div class="flex justify-between py-2 border-b">
                        <div>
                            <p class="font-semibold">{{ $person->name }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $person->number }} · {{ $person->rank }}
                            </p>
                        </div>

                        <span class="{{ $person->status === 'ativo' ? 'badge' : 'badge muted' }}">
                            {{ ucfirst($person->status) }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- ATRIBUIÇÕES --}}
            <div class="card">
                <h3 class="section-title mb-4">Últimas atribuições</h3>

                @forelse ($recentAssignments as $assignment)
                    <div class="border-b py-3">
                        <p class="font-semibold">
                            {{ $assignment->person->name }}
                        </p>

                        <p class="text-sm text-gray-500">
                            {{ $assignment->uniformItem->name }} ·
                            {{ $assignment->quantity }} unidade(s)
                        </p>

                        <p class="text-xs text-gray-400">
                            {{ $assignment->assigned_at?->format('d/m/Y') }}
                        </p>
                    </div>
                @empty
                    <p class="empty">Sem atribuições.</p>
                @endforelse
            </div>

        </div>

    </div>
</x-app-layout>
