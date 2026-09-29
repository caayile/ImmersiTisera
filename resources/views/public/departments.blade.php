@extends('layouts.public')
@section('title', 'Unit Bisnis')
@section('content')
@php
    $background = $hero->backgroundUrl() ?: asset('images/hero/campus.jpg');
    $slides = $heroSlides->all();
@endphp

<section
    class="departments-hero relative overflow-hidden"
    style="--hero-campus: url('{{ $background }}')"
>
    <div class="departments-hero__backdrop" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-6xl px-5 pb-14 pt-10">
        <div class="mb-6 max-w-2xl text-white">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Mitra Magang Dosen</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight md:text-4xl">{{ $hero->title ?: 'Unit Bisnis' }}</h1>
            <p class="mt-2 text-sm leading-6 text-white/80">{{ $hero->subtitle ?: 'Pilih unit bisnis mitra magang dosen.' }}</p>
        </div>

        <div id="dept-hero-carousel" class="relative mx-auto max-w-5xl px-10 md:px-14">
            <button type="button" id="dept-hero-prev" class="partner-nav left-0" aria-label="Sebelumnya">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <button type="button" id="dept-hero-next" class="partner-nav right-0" aria-label="Berikutnya">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>

            <div class="relative mx-auto h-[240px] sm:h-[320px] md:h-[400px]">
                @forelse($slides as $i => $slide)
                    @php
                        $slideOffset = $i <= count($slides) / 2 ? $i : $i - count($slides);
                        $isActive = $i === 0;
                    @endphp
                    <a
                        href="{{ $slide['url'] }}"
                        data-slide="{{ $i }}"
                        data-slide-id="{{ $slide['id'] }}"
                        class="partner-card absolute inset-y-0 left-1/2 flex w-[88%] max-w-3xl items-center justify-center overflow-hidden rounded-3xl bg-[#0d241e] shadow-2xl transition-all duration-500 ease-out sm:w-[78%]"
                        style="transform: translateX(calc(-50% + {{ $slideOffset * 58 }}%)) scale({{ $isActive ? 1 : 0.86 }}); z-index: {{ 20 - abs($slideOffset) }}; opacity: {{ abs($slideOffset) > 1 ? 0 : ($isActive ? 1 : 0.55) }}; pointer-events: {{ $isActive ? 'auto' : 'none' }};"
                    >
                        <img src="{{ $slide['image'] ?? $background }}" alt="{{ $slide['title'] }}" class="absolute inset-0 h-full w-full object-cover">
                        <div class="pointer-events-none absolute inset-0 z-10 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 z-20 p-5 sm:p-7">
                            <h2 class="text-2xl font-semibold text-white sm:text-3xl">{{ $slide['title'] }}</h2>
                            <p class="mt-2 line-clamp-2 max-w-xl text-sm text-white/85">{{ $slide['subtitle'] }}</p>
                        </div>
                    </a>
                @empty
                    <div class="flex h-full items-center justify-center rounded-3xl border border-white/20 bg-black/25 text-sm text-white/85 backdrop-blur">
                        Belum ada banner mitra. Admin dapat menambahkannya di Hero Unit Bisnis.
                    </div>
                @endforelse
            </div>

            @if(count($slides))
                <div class="mt-5 flex justify-center gap-2">
                    @foreach($slides as $i => $slide)
                        <button
                            type="button"
                            data-dot="{{ $i }}"
                            aria-label="Banner {{ $slide['title'] }}"
                            class="hero-dot h-2.5 rounded-full transition-all {{ $i === 0 ? 'w-7 bg-secondary' : 'w-2.5 bg-white/40 hover:bg-white/70' }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

<script>
(function () {
    var root = document.getElementById('dept-hero-carousel');
    if (!root) return;
    var slides = Array.prototype.slice.call(root.querySelectorAll('[data-slide]'));
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-dot]'));
    var count = slides.length;
    if (!count) return;

    var index = 0;
    var timer = null;

    function offset(i) {
        var distance = i - index;
        if (distance > count / 2) distance -= count;
        if (distance < -count / 2) distance += count;
        return distance;
    }

    function apply() {
        slides.forEach(function (slide, i) {
            var distance = offset(i);
            slide.style.transform = 'translateX(calc(-50% + ' + (distance * 58) + '%)) scale(' + (distance === 0 ? 1 : 0.86) + ')';
            slide.style.zIndex = 20 - Math.abs(distance);
            slide.style.opacity = Math.abs(distance) > 1 ? 0 : (distance === 0 ? 1 : 0.55);
            slide.style.pointerEvents = distance === 0 ? 'auto' : 'none';
        });
        dots.forEach(function (dot, i) {
            var active = i === index;
            dot.classList.toggle('w-7', active);
            dot.classList.toggle('bg-secondary', active);
            dot.classList.toggle('w-2.5', !active);
            dot.classList.toggle('bg-white/40', !active);
        });
    }

    function go(nextIndex) {
        index = ((nextIndex % count) + count) % count;
        apply();
    }

    function stop() {
        if (timer) clearInterval(timer);
        timer = null;
    }

    function start() {
        stop();
        if (count > 1) timer = setInterval(function () { go(index + 1); }, 5200);
    }

    var previous = document.getElementById('dept-hero-prev');
    var next = document.getElementById('dept-hero-next');
    if (previous) previous.addEventListener('click', function () { go(index - 1); });
    if (next) next.addEventListener('click', function () { go(index + 1); });
    dots.forEach(function (dot) {
        dot.addEventListener('click', function () { go(Number(dot.dataset.dot)); });
    });
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);

    apply();
    start();
})();
</script>

<div class="mx-auto max-w-7xl px-5 py-12">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold">Daftar unit bisnis</h2>
            <p class="mt-1 text-muted">Tujuh unit bisnis mitra TSU.</p>
        </div>
        <div
            class="relative w-full max-w-sm sm:min-w-[300px]"
            x-data="{ query: '{{ request('q') }}', results: [], showDropdown: false, loading: false }"
            @click.away="showDropdown = false"
        >
            <form action="{{ route('departments.index') }}" method="GET" class="relative">
                <input
                    type="search"
                    name="q"
                    x-model="query"
                    @input.debounce.300ms="
                        if(query.length > 0) {
                            loading = true;
                            showDropdown = true;
                            fetch('{{ route('api.search') }}?q=' + encodeURIComponent(query))
                                .then(res => res.json())
                                .then(data => { results = data; loading = false; });
                        } else {
                            showDropdown = false;
                            results = [];
                        }
                    "
                    @focus="if(query.length > 0) showDropdown = true"
                    placeholder="Cari departemen atau unit bisnis..."
                    autocomplete="off"
                    class="w-full rounded-xl border border-line bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                <svg class="absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </form>

            <div x-show="showDropdown" x-cloak x-transition.opacity class="absolute left-0 right-0 top-full z-50 mt-2 max-h-[400px] overflow-y-auto rounded-xl border border-line bg-white shadow-lg">
                <div x-show="loading" class="p-4 text-center text-sm text-muted">Mencari...</div>
                <div x-show="!loading && results.length === 0" class="p-4 text-center text-sm text-muted">Tidak ada hasil ditemukan.</div>
                <ul x-show="!loading && results.length > 0" class="divide-y divide-line">
                    <template x-for="item in results" :key="item.type + item.id">
                        <li>
                            <a :href="item.url" class="group flex items-center gap-3 p-3 transition hover:bg-bg">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-white">
                                    <span class="material-symbols-outlined" x-text="item.type === 'Departemen' ? 'domain' : 'storefront'"></span>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold text-ink" x-text="item.name"></div>
                                    <div class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-muted" x-text="item.type"></div>
                                </div>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($departments as $department)
            @php
                $image = $department->imageUrl() ?? $background;
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