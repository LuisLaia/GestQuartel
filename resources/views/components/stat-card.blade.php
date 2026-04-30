@props(['label', 'value', 'tone' => 'default'])

<div class="card">
    <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
    <p class="mt-3 text-4xl font-black {{ $tone === 'danger' ? 'text-red-700' : 'text-slate-950' }}">{{ $value }}</p>
</div>
