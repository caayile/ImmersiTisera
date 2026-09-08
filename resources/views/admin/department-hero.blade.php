@extends('layouts.app')
@section('title', 'Hero Unit Bisnis')
@section('content')
<div class="max-w-5xl">
    <div>
        <h1 class="text-2xl font-semibold">Hero Unit Bisnis</h1>
        <p class="mt-1 text-sm text-muted">Atur gambar, judul, dan deskripsi unit bisnis pada carousel di halaman Unit Bisnis.</p>
    </div>

    <form method="POST" action="{{ route('admin.department-hero.background') }}" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-2xl border border-line bg-white p-6">
        @csrf
        <h2 class="font-semibold">Background kampus</h2>
        @if($setting->backgroundUrl())
            <img src="{{ $setting->backgroundUrl() }}" alt="Background kampus" class="h-40 w-full rounded-xl object-cover">
        @endif
        <div class="grid gap-3 md:grid-cols-2">
            <label class="text-xs font-semibold uppercase tracking-wide text-muted">Judul
                <input name="title" value="{{ old('title', $setting->title) }}" class="mt-2 w-full rounded-lg border border-line px-3 py-2 text-sm" required>
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-muted">Ganti foto background
                <input type="file" name="background" accept="image/*" class="mt-2 w-full rounded-lg border border-line px-3 py-2 text-sm">
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-muted md:col-span-2">Subtitle
                <input name="subtitle" value="{{ old('subtitle', $setting->subtitle) }}" class="mt-2 w-full rounded-lg border border-line px-3 py-2 text-sm">
            </label>
        </div>
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan background</button>
    </form>

    <form method="POST" action="{{ route('admin.department-hero.slides.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-6 md:grid-cols-2">
        @csrf
        <h2 class="font-semibold md:col-span-2">Tambah banner mitra (slide atas)</h2>
        <input name="title" placeholder="Judul slide" class="rounded-lg border border-line px-3 py-2 text-sm" required>
        <input name="sort_order" type="number" min="0" placeholder="Urutan" class="rounded-lg border border-line px-3 py-2 text-sm">
        <textarea name="subtitle" placeholder="Deskripsi singkat" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
        <input name="link_url" placeholder="Link (opsional, mis. /departments/k33)" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">
        <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2" required>
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah slide</button>
    </form>

    <div class="mt-6 space-y-4">
        @forelse($slides as $slide)
            <article class="rounded-2xl border border-line bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex gap-4">
                        @if($slide->imageUrl())
                            <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->title }}" class="h-24 w-36 rounded-xl object-cover">
                        @endif
                        <div>
                            <h3 class="font-semibold">{{ $slide->title }}</h3>
                            <p class="mt-1 text-sm text-muted">{{ $slide->subtitle }}</p>
                            <p class="mt-1 text-xs text-muted">Urutan {{ $slide->sort_order }} · {{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.department-hero.slides.destroy', $slide) }}" onsubmit="return confirm('Hapus slide ini?')">
                        @csrf @method('DELETE')
                        <button class="text-sm text-red-600">Hapus</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.department-hero.slides.update', $slide) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
                    @csrf @method('PUT')
                    <input name="title" value="{{ $slide->title }}" class="rounded-lg border border-line px-3 py-2 text-sm" required>
                    <input name="sort_order" type="number" min="0" value="{{ $slide->sort_order }}" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <textarea name="subtitle" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">{{ $slide->subtitle }}</textarea>
                    <input name="link_url" value="{{ $slide->link_url }}" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">
                    <label class="flex items-center gap-2 text-sm md:col-span-2">
                        <input type="checkbox" name="is_active" value="1" @checked($slide->is_active)>
                        Tampilkan di halaman Unit Bisnis
                    </label>
                    <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">
                    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan slide</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-muted">Belum ada slide. Tambahkan banner mitra di atas.</p>
        @endforelse
    </div>
</div>
@endsection
