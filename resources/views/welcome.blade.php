<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>GestQuartel</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-gray-100 text-gray-900">
        <main class="flex min-h-screen items-center justify-center px-6 py-12">
            <section class="w-full max-w-xl rounded-xl bg-white p-8 text-center shadow">
                <h1 class="text-3xl font-bold text-gray-900">GestQuartel</h1>
                <p class="mt-3 text-gray-600">
                    Sistema de gestao operacional para quartel.
                </p>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Entrar no painel
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                Iniciar sessao
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                                Criar conta
                            </a>
                        @endif
                    @endauth
                </div>
            </section>
        </main>
    </body>
</html>
