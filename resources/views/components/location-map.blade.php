@props([
    'embed',
    'open',
    'name' => 'unit bisnis',
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm']) }}>
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
        <h3 class="flex items-center gap-2 text-sm font-bold text-ink">
            <span class="material-symbols-outlined text-[18px] text-primary-dark">location_on</span>
            Lokasi Magang
        </h3>
        <a href="{{ $open }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-dark hover:text-primary">
            Buka di Google Maps
            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
        </a>
    </div>
    <iframe
        src="{{ $embed }}"
        title="Lokasi {{ $name }}"
        class="h-72 w-full border-0 lg:h-80"
        loading="lazy"
        allowfullscreen
    ></iframe>
</div>
