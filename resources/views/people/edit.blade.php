@extends('layouts.app', ['title' => 'Editar pessoa - GestQuartel'])

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">Editar registo</p>
            <h2 class="page-title">{{ $person->name }}</h2>
        </div>
    </div>
    <form class="card" method="POST" action="{{ route('personnel.update', $person) }}">
        @method('PUT')
        @include('people.form')
    </form>
@endsection
