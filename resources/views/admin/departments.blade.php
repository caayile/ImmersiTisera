@extends('layouts.app')
@section('title', 'Unit Bisnis')
@section('content')
<div x-data="{ showAdd: false }">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Unit Bisnis</h1>
            <p class="mt-1 text-sm text-muted">Pilih unit bisnis untuk mengelola informasi, lokasi, dan departemen di dalamnya.</p>
        </div>
        <button type="button" @click="showAdd = !showAdd" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Tambah Unit Bisnis
        </button>
    </div>

    <section x-show="showAdd" x-cloak class="mt-6 rounded-2xl border border-line bg-white p-5">
        <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
            <span class="material-symbols-outlined text-[18px] text-primary-dark">apartment</span>
            Tambah unit bisnis baru
        </h2>
        <form method="POST" action="{{ route('admin.departments') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
            @csrf
            <input name="name" placeholder="Nama unit bisnis" class="rounded-lg border border-line px-3 py-2 text-sm" required>
            <input name="subtitle" placeholder="Subtitle (nama lengkap, mis. Tiga Serangkai Pustaka Mandiri)" class="rounded-lg border border-line px-3 py-2 text-sm">
            <input name="area" placeholder="Area / Lokasi (mis. Surakarta)" class="rounded-lg border border-line px-3 py-2 text-sm">
            <input name="map_url" placeholder="Link Google Maps / peta (opsional)" class="rounded-lg border border-line px-3 py-2 text-sm">
            <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
            <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
            <button class="justify-self-start rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah Unit Bisnis</button>
        </form>
    </section>
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white">
    @forelse($departments as $department)
        <div class="flex items-center gap-4 border-b border-line px-5 py-4 last:border-0 transition hover:bg-bg/50">
            @if($department->imageUrl())
                <img src="{{ $department->imageUrl() }}" alt="{{ $department->name }}" class="h-14 w-20 shrink-0 rounded-lg object-cover">
            @else
                <span class="flex h-14 w-20 shrink-0 items-center justify-center rounded-lg bg-primary/12 text-primary-dark">
                    <span class="material-symbols-outlined text-[26px]">apartment</span>
                </span>
            @endif
            <div class="min-w-0 flex-1">
                <h3 class="font-semibold text-ink">{{ $department->name }}</h3>
                <p class="mt-0.5 truncate text-xs text-muted">{{ $department->subtitle ?: ($department->area ?: 'Unit Bisnis Mitra') }}</p>
            </div>
            <span class="hidden shrink-0 rounded-full bg-[#f6fbf8] px-3 py-1 text-xs font-semibold text-muted sm:inline-block">
                {{ $department->business_units_count }} departemen
            </span>
            <a href="{{ route('admin.departments.show', $department) }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                Lihat Unit Bisnis
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>
    @empty
        <p class="px-5 py-8 text-sm text-muted">Belum ada unit bisnis. Klik "Tambah Unit Bisnis" untuk menambahkan.</p>
    @endforelse
</div>
@endsection