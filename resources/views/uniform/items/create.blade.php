@extends('layouts.app', ['title' => 'Adicionar fardamento - GestQuartel'])

@section('content')
<div class="space-y-6">
    <div class="page-header">
        <div>
            <p class="eyebrow">Novo item</p>
            <h1 class="page-title">Adicionar fardamento</h1>
        </div>
        <div class="flex gap-3"></div>
    </div>
    <form class="card" method="POST" action="{{ route('uniform.items.store') }}">
        @include('uniform.items.form')
    </form>
</div>
@endsection
