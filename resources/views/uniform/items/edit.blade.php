@extends('layouts.app', ['title' => 'Editar fardamento - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Editar item</p>
            <h2 class="page-title">{{ $uniformItem->name }}</h2>
        </div>
    </div>
    <form class="card" method="POST" action="{{ route('uniform.items.update', $uniformItem) }}">
        @method('PUT')
        @include('uniform.items.form')
    </form>
@endsection
