<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Perfil do Utilizador
        </h2>
    </x-slot>

    <div class="mt-6 bg-white p-6 rounded-xl shadow">
        <p><strong>Nome:</strong> {{ Auth::user()->name }}</p>
        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
    </div>
</x-app-layout>
