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
>
    <div class="departments-hero__backdrop" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-6xl px-5 pb-14 pt-10">
        <div class="mb-6 max-w-2xl text-white">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Mitra Imersi</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight md:text-4xl">{{ $hero->title ?: 'Unit Bisnis' }}</h1>
            <p class="mt-2 text-sm leading-6 text-white/80">{{ $hero->subtitle ?: 'Pilih unit bisnis mitra imersi.' }}</p>
        </div>

        <x-hero-carousel
            :slides="$slides"
            height="h-[380px] md:h-[400px] lg:h-[420px]"
            :interval="2000"
            card-width="w-[72%] md:w-[74%]"
            radius="rounded-[22px]"
            empty-message="Belum ada banner mitra. Admin dapat menambahkannya di Hero Unit Bisnis."
        />
    </div>
</section>

<div class="mx-auto max-w-7xl px-5 py-12">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold">Daftar unit bisnis</h2>
            <p class="mt-1 text-muted">Tujuh unit bisnis mitra TSU.</p>
        </div>
        <div class="relative w-full max-w-sm sm:min-w-[300px]" 
             x-data="{ query: '{{ request('q') }}', results: [], showDropdown: false, loading: false }" 
             @click.away="showDropdown = false">
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
                    class="w-full rounded-xl border border-line bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                <svg class="absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </form>
            
            <!-- Dropdown Live Search -->
            <div x-show="showDropdown" x-cloak x-transition.opacity
                 class="absolute left-0 right-0 top-full mt-2 overflow-hidden rounded-xl border border-line bg-white shadow-lg z-50 max-h-[400px] overflow-y-auto">
                <div x-show="loading" class="p-4 text-center text-sm text-muted">Mencari...</div>
                <div x-show="!loading && results.length === 0" class="p-4 text-center text-sm text-muted">Tidak ada hasil ditemukan.</div>
                <ul x-show="!loading && results.length > 0" class="divide-y divide-line">
                    <template x-for="item in results" :key="item.type + item.id">
                        <li>
                            <a :href="item.url" class="flex items-center gap-3 p-3 hover:bg-bg transition group">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition">
                                    <span class="material-symbols-outlined" x-text="item.type === 'Departemen' ? 'domain' : 'storefront'"></span>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold text-ink" x-text="item.name"></div>
                                    <div class="text-[11px] font-medium text-muted uppercase tracking-wider mt-0.5" x-text="item.type"></div>
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