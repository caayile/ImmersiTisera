@extends('layouts.public')
@section('title', 'Unit Bisnis')
@section('content')
@php
    $background = $hero->backgroundUrl() ?: asset('images/hero/campus.jpg');
    $slides = $heroSlides->all();
    $slideImages = collect($slides)
        ->mapWithKeys(fn ($slide) => [collect(explode('/', $slide['url']))->last() => $slide['image']]);
@endphp

<section
    class="departments-hero relative overflow-hidden"
    style="--hero-campus: url('{{ $background }}')"
    x-data="{
        slides: @js($slides),
        index: 0,
        timer: null,
        get count() { return this.slides.length; },
        prev() { if (! this.count) return; this.index = (this.index - 1 + this.count) % this.count; },
        next() { if (! this.count) return; this.index = (this.index + 1) % this.count; },
        go(i) { this.index = i; },
        offset(i) {
            if (! this.count) return 0;
            let d = i - this.index;
            if (d > this.count / 2) d -= this.count;
            if (d < -this.count / 2) d += this.count;
            return d;
        },
        start() {
            this.stop();
            if (this.count < 2) return;
            this.timer = setInterval(() => this.next(), 5200);
        },
        stop() {
            if (this.timer) clearInterval(this.timer);
            this.timer = null;
        }
    }"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
>
    <div class="departments-hero__backdrop" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-6xl px-5 pb-14 pt-10">
        <div class="mb-6 max-w-2xl text-white">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Mitra Magang Dosen</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight md:text-4xl">{{ $hero->title ?: 'Unit Bisnis' }}</h1>
            <p class="mt-2 text-sm leading-6 text-white/80">{{ $hero->subtitle ?: 'Pilih unit bisnis mitra magang dosen.' }}</p>
        </div>

        <div class="relative mx-auto max-w-5xl px-10 md:px-14">
            <button type="button" @click="prev()" class="partner-nav left-0" aria-label="Sebelumnya">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <button type="button" @click="next()" class="partner-nav right-0" aria-label="Berikutnya">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>

            <div class="relative mx-auto h-[240px] sm:h-[320px] md:h-[400px]">
                <template x-for="(slide, i) in slides" :key="slide.id">
                    <a
                        :href="slide.url"
                        class="partner-card absolute inset-y-0 left-1/2 flex w-[88%] max-w-3xl items-center justify-center overflow-hidden rounded-3xl bg-[#0d241e] shadow-2xl transition-all duration-500 ease-out sm:w-[78%]"
                        :style="`
                            transform: translateX(calc(-50% + ${offset(i) * 58}%)) scale(${offset(i) === 0 ? 1 : 0.86});
                            z-index: ${20 - Math.abs(offset(i))};
                            opacity: ${Math.abs(offset(i)) > 1 ? 0 : (offset(i) === 0 ? 1 : 0.55)};
                            pointer-events: ${offset(i) === 0 ? 'auto' : 'none'};
                        `"
                    >
                        <template x-if="slide.image">
                            <img :src="slide.image" :alt="slide.title" class="absolute inset-0 h-full w-full object-cover">
                        </template>
                        <div class="pointer-events-none absolute inset-0 z-10 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 z-20 p-5 sm:p-7">
                            <h2 class="text-2xl font-semibold text-white sm:text-3xl" x-text="slide.title"></h2>
                            <p class="mt-2 line-clamp-2 max-w-xl text-sm text-white/85" x-text="slide.subtitle"></p>
                        </div>
                    </a>
                </template>

                <template x-if="! count">
                    <div class="flex h-full items-center justify-center rounded-3xl border border-white/20 bg-black/25 text-sm text-white/85 backdrop-blur">
                        Belum ada banner mitra. Admin dapat menambahkannya di Hero Unit Bisnis.
                    </div>
                </template>
            </div>

            <div class="mt-5 flex justify-center gap-2">
                <template x-for="(slide, i) in slides" :key="'dot-'+slide.id">
                    <button
                        type="button"
                        class="h-2.5 rounded-full transition-all"
                        :class="i === index ? 'w-7 bg-secondary' : 'w-2.5 bg-white/40 hover:bg-white/70'"
                        @click="go(i)"
                        :aria-label="'Banner ' + slide.title"
                    ></button>
                </template>
            </div>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-5 py-12">
    <div>
        <h2 class="text-2xl font-semibold">Daftar unit bisnis</h2>
        <p class="mt-1 text-muted">Tujuh unit bisnis mitra TSU.</p>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($departments as $department)
            @php
                $image = $department->imageUrl() ?? ($slideImages[$department->slug] ?? $background);
            @endphp
            <article class="flex flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-primary hover:shadow-md">
                <a href="{{ route('departments.show', $department) }}">
                    <x-fill-image :src="$image" :alt="$department->name" class="h-52 w-full">
                        <span class="absolute bottom-3 left-4 z-20 rounded-md bg-black/55 px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-white">{{ $department->area ?: 'Unit Bisnis' }}</span>
                    </x-fill-image>
                </a>
                <div class="flex flex-1 flex-col p-5">
                    <h3 class="text-lg font-semibold">{{ $department->name }}</h3>
                    <p class="mt-2 flex-1 text-sm leading-6 text-muted">{{ \Illuminate\Support\Str::limit($department->description, 110) }}</p>
                    <a href="{{ route('departments.show', $department) }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-[#eef4f1] px-4 py-2.5 text-sm font-semibold text-ink hover:bg-primary hover:text-white">Lihat Departemen</a>
                </div>
            </article>
        @empty
            <p class="text-muted sm:col-span-2 xl:col-span-3">Belum ada unit bisnis.</p>
        @endforelse
    </div>
</div>
@endsection