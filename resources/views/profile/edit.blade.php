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
                <h3 class="text-lg font-semibold mb-4">Integrações</h3>

                <p class="text-sm text-gray-500 mb-4">
                    Sincronização com sistema externo Preventech.
                </p>

                <div class="mb-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
                    <p>
                        <strong>Última sincronização:</strong>
                        {{ session('last_sync_at', 'Sem sincronização registada.') }}
                    </p>
                    <p class="mt-1">
                        <strong>Resultado:</strong>
                        {{ session('last_sync_result', 'Sem resultado disponível.') }}
                    </p>
                </div>

                <div class="flex gap-4">
                    <form method="POST" action="{{ route('profile.sync.firefighters') }}">
                        @csrf
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Sincronizar Bombeiros
                        </button>
                    </form>

                    <form method="POST" action="{{ route('profile.sync.vehicles') }}">
                        @csrf
                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg">
                            Sincronizar Veículos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
