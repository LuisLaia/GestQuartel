<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <div class="mt-10 bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg font-semibold mb-4">Integracoes</h3>

                <p class="text-sm text-gray-500 mb-4">
                    Sincronizacao com sistema externo Preventech.
                </p>

                @if (config('services.preventech.mode', 'sandbox') === 'sandbox')
                    <div class="mb-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        Modo Sandbox Preventech ativo &mdash; dados simulados
                    </div>
                @endif

                <div class="mb-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
                    <p>
                        <strong>Ultima sincronizacao:</strong>
                        {{ session('last_sync_at', 'Sem sincronizacao registada.') }}
                    </p>
                    <p class="mt-1">
                        <strong>Resultado:</strong>
                        {{ session('last_sync_result', 'Sem resultado disponivel.') }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-4">
                    <form method="POST" action="{{ route('preventech.sync.personnel') }}">
                        @csrf
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg" type="submit">
                            Sincronizar Pessoal
                        </button>
                    </form>

                    <form method="POST" action="{{ route('preventech.sync.vehicles') }}">
                        @csrf
                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg" type="submit">
                            Sincronizar Veiculos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
