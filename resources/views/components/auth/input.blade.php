@props([
    'icon' => null,
])

<div class="relative">
    @if($icon)
        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-zinc-400">
            {!! $icon !!}
        </span>
    @endif
    <input {{ $attributes->class(['auth-input', 'auth-input-plain' => ! $icon]) }}>
</div>
