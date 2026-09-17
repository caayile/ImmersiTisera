@props([
    'step',
])

<section {{ $attributes->merge(['class' => 'border-b border-line bg-white']) }} data-reveal>
    <div class="mx-auto max-w-6xl px-5 py-8">
        <div class="flex flex-col gap-5 rounded-3xl border border-line bg-bg p-6 sm:flex-row sm:items-center sm:justify-between md:p-8">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">{{ $step->eyebrow }}</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight">{{ $step->title }}</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-muted">{{ $step->message }}</p>
            </div>
            <a href="{{ $step->url }}" class="inline-flex shrink-0 items-center justify-center gap-1 rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
                {{ $step->cta }}
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>
    </div>
</section>
