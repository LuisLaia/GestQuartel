@extends('layouts.app', ['title' => 'Adicionar fardamento - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Novo item</p>
            <h2 class="page-title">Adicionar fardamento</h2>
        </div>
    </div>
    <form class="card" method="POST" action="{{ route('uniform.items.store') }}">
        @include('uniform.items.form')
    </form>
@endsection
