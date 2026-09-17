@props([
    'href' => null,
    'label' => 'Kembali',
])

<a href="{{ $href ?? route(auth()->user()->homeRoute()) }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-sm font-semibold text-primary-dark']) }}>
    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
    {{ $label }}
</a>
