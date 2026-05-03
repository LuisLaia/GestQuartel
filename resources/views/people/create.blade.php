@extends('layouts.app', ['title' => 'Adicionar pessoa - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Novo registo</p>
            <h2 class="page-title">Adicionar pessoa</h2>
        </div>
    </div>
    <form class="card" method="POST" action="{{ route('personnel.store') }}">
        @include('people.form')
    </form>
@endsection
