@extends('layouts.app', ['title' => 'Editar fardamento - GestQuartel'])

@section('content')
<div class="space-y-6">
    <div class="page-header">
        <div>
            <p class="eyebrow">Editar item</p>
            <h1 class="page-title">{{ $uniformItem->name }}</h1>
        </div>
        <div class="flex gap-3"></div>
    </div>
    <form class="card" method="POST" action="{{ route('uniform.items.update', $uniformItem) }}">
        @method('PUT')
        @include('uniform.items.form')
    </form>
</div>
@endsection
