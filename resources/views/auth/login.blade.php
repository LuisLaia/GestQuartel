@extends('layouts.app', ['title' => 'Login - GestQuartel'])

@section('content')
    <section class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
        <div class="mb-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-red-600">GestQuartel</p>
            <h1 class="mt-3 text-3xl font-bold text-slate-950">Entrar no quartel</h1>
            <p class="mt-2 text-sm text-slate-500">Autentique-se para gerir pessoas, equipamentos e stock.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <label class="block">
                <span class="label">Email</span>
                <input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>

            <label class="block">
                <span class="label">Password</span>
                <input class="input" type="password" name="password" required>
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                Manter sessão iniciada
            </label>

            <button class="btn-primary w-full">Entrar</button>
        </form>
    </section>
@endsection
