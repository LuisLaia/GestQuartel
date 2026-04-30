@extends('layouts.app', ['title' => 'Editar equipamento - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Editar item</p>
            <h2 class="page-title">{{ $equipment->name }}</h2>
        </div>
    </div>
    <form class="card" method="POST" action="{{ route('equipment.update', $equipment) }}">
        @method('PUT')
        @include('equipment.form')
    </form>
@endsection
