@extends('layouts.app', ['title' => 'Adicionar equipamento - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Novo item</p>
            <h2 class="page-title">Adicionar equipamento</h2>
        </div>
    </div>
    <form class="card" method="POST" action="{{ route('equipment.store') }}">
        @include('equipment.form')
    </form>
@endsection
