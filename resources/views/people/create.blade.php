@extends('layouts.app', ['title' => 'Adicionar pessoa - GestQuartel'])

@section('content')
<div class="space-y-6">
    <div class="page-header">
        <div>
            <p class="eyebrow">Novo registo</p>
            <h1 class="page-title">Adicionar pessoa</h1>
        </div>
        <div class="flex gap-3"></div>
    </div>
    <form class="card" method="POST" action="{{ route('personnel.store') }}">
        @include('people.form')
    </form>
</div>
@endsection
