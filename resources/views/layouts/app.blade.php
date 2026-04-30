<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'GestQuartel' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    @auth
        <div class="min-h-screen lg:flex">
            <aside class="bg-slate-950 p-6 text-white lg:w-72">
                <div class="mb-8">
                    <p class="text-sm uppercase tracking-[0.35em] text-red-300">Quartel</p>
                    <h1 class="mt-2 text-2xl font-bold">GestQuartel</h1>
                </div>

                <nav class="space-y-2">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="nav-link {{ request()->routeIs('people.*') ? 'active' : '' }}" href="{{ route('people.index') }}">Pessoas</a>
                    <a class="nav-link {{ request()->routeIs('equipment.*') ? 'active' : '' }}" href="{{ route('equipment.index') }}">Equipamentos</a>
                    <a class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}" href="{{ route('assignments.index') }}">Atribuições</a>
                </nav>

                <form class="mt-8" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full rounded-xl bg-white/10 px-4 py-3 text-left text-sm font-semibold text-slate-100 hover:bg-white/20">
                        Sair
                    </button>
                </form>
            </aside>

            <main class="flex-1 p-6 lg:p-10">
                @include('layouts.partials.flash')
                @yield('content')
            </main>
        </div>
    @else
        <main class="flex min-h-screen items-center justify-center bg-slate-950 p-6">
            @yield('content')
        </main>
    @endauth
</body>
</html>
