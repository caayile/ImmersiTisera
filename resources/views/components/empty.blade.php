@props(['title' => 'Belum ada data', 'action' => null, 'label' => 'Lanjutkan'])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center']) }}>
    <p class="font-medium">{{ $title }}</p>
    <p class="mt-1 text-sm text-muted">{{ $slot }}</p>
    @if($action)
        <a href="{{ $action }}" class="mt-4 inline-block rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">{{ $label }}</a>
    @endif
</div>
