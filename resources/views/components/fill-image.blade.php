@props([
    'src',
    'alt' => '',
])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-[#16352c]']) }}>
    <img src="{{ $src }}" alt="{{ $alt }}" class="block h-full w-full object-cover">
    {{ $slot }}
</div>
