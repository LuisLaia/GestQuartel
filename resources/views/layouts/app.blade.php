<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'GestQuartel' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">

    <script>
        localStorage.removeItem('dark');
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">

    <!-- NAVBAR FULL WIDTH -->
    <div class="w-full">
        @include('layouts.navigation')
    </div>

    <!-- CONTEÚDO CENTRAL -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <main class="mt-6">
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>

    </div>

</body>
</html>
