@extends('layouts.app', ['title' => 'Editar pessoa - GestQuartel'])

@section('content')
<div class="space-y-6">
    <div class="page-header">
        <div>
            <p class="eyebrow">Editar registo</p>
            <h1 class="page-title">{{ $person->name }}</h1>
        </div>
        <div class="flex gap-3"></div>
    </div>
    <form class="card" method="POST" action="{{ route('personnel.update', $person) }}">
        @method('PUT')
        @include('people.form')
    </form>
</div>
@endsection
