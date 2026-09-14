@extends('layouts.app')
@section('title', $department->name)
@section('content')
<a href="{{ route('admin.departments') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted transition hover:text-primary-dark">
    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
    Daftar unit bisnis
</a>

<div class="mt-3 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        @if($department->imageUrl())
            <img src="{{ $department->imageUrl() }}" alt="{{ $department->name }}" class="h-14 w-20 rounded-xl object-cover">
        @endif
        <div>
            <h1 class="text-2xl font-semibold">{{ $department->name }}</h1>
            <p class="text-sm text-muted">{{ $department->subtitle }}{{ $department->subtitle && $department->area ? ' · ' : '' }}{{ $department->area }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('departments.show', $department) }}" title="Lihat publik" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-line px-3 text-sm font-medium text-muted transition hover:border-primary hover:text-primary-dark">
            <span class="material-symbols-outlined text-[18px]">open_in_new</span>
            Lihat Publik
        </a>
        <a href="{{ route('admin.units.show', $department) }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-primary px-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
            <span class="material-symbols-outlined text-[18px]">apartment</span>
            Kelola Departemen
        </a>
    </div>
</div>

{{-- ===== INFORMASI UNIT BISNIS ===== --}}
<section class="mt-6 rounded-2xl border border-line bg-white p-5">
    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
        <span class="material-symbols-outlined text-[18px] text-primary-dark">business_center</span>
        Informasi Unit Bisnis
    </h2>
    <form method="POST" action="{{ route('admin.departments.update', $department) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
        @csrf @method('PUT')
        <input name="name" value="{{ $department->name }}" placeholder="Nama unit bisnis" class="rounded-lg border border-line px-3 py-2 text-sm" required>
        <input name="subtitle" value="{{ $department->subtitle }}" placeholder="Subtitle (nama lengkap, mis. Tiga Serangkai Pustaka Mandiri)" class="rounded-lg border border-line px-3 py-2 text-sm">
        <input name="area" value="{{ $department->area }}" placeholder="Area / Lokasi (mis. Surakarta)" class="rounded-lg border border-line px-3 py-2 text-sm">
        <input name="map_url" value="{{ $department->map_url }}" placeholder="Link Google Maps / peta (opsional)" class="rounded-lg border border-line px-3 py-2 text-sm">
        <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">{{ $department->description }}</textarea>
        <div class="flex items-center gap-3">
            <input type="file" name="image" accept="image/*" class="flex-1 rounded-lg border border-line px-3 py-2 text-sm">
            <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                <option value="active" @selected($department->status === 'active')>Aktif</option>
                <option value="disabled" @selected($department->status === 'disabled')>Nonaktif</option>
            </select>
        </div>
        <div class="flex items-center justify-between gap-3 md:col-span-2">
            <button type="button" onclick="if (confirm('Hapus unit bisnis ini?')) document.getElementById('delete-department-{{ $department->id }}').submit()" class="text-sm font-semibold text-red-600">Hapus unit bisnis</button>
            <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                Simpan perubahan
                <span class="material-symbols-outlined text-[18px]">save</span>
            </button>
        </div>
    </form>
</section>

{{-- ===== PENGATURAN LOKASI ===== --}}
<section class="mt-6 rounded-2xl border border-line bg-white p-5">
    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
        <span class="material-symbols-outlined text-[18px] text-primary-dark">location_on</span>
        Pengaturan Lokasi
    </h2>
    <p class="mt-1 text-xs text-muted">Isi lokasi yang ditampilkan di peta halaman publik. Kosongkan peta untuk memakai pencarian area otomatis.</p>
    <form method="POST" action="{{ route('admin.departments.update', $department) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-[1fr_2fr_auto]">
        @csrf @method('PUT')
        <input type="hidden" name="name" value="{{ $department->name }}">
        <input type="hidden" name="status" value="{{ $department->status }}">
        <input name="area" value="{{ $department->area }}" placeholder="Area / Lokasi (mis. Surakarta)" class="rounded-lg border border-line px-3 py-2 text-sm">
        <input name="map_url" value="{{ $department->map_url }}" placeholder="Link Google Maps / peta (opsional)" class="rounded-lg border border-line px-3 py-2 text-sm">
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan lokasi</button>
    </form>
</section>

{{-- ===== DEPARTEMEN DI UNIT BISNIS INI ===== --}}
<section class="mt-6">
    <div class="flex items-center justify-between gap-3">
        <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
            <span class="material-symbols-outlined text-[18px] text-primary-dark">apartment</span>
            Departemen di {{ $department->name }}
        </h2>
        <a href="{{ route('admin.units.show', $department) }}" class="text-sm font-semibold text-primary-dark transition hover:text-primary-dark/80">Kelola →</a>
    </div>
    <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-white">
        @forelse($units as $unit)
            <div class="flex items-center gap-4 border-b border-line px-5 py-4 last:border-0">
                @if($unit->imageUrl())
                    <img src="{{ $unit->imageUrl() }}" alt="{{ $unit->name }}" class="h-14 w-24 shrink-0 rounded-lg object-cover">
                @else
                    <span class="flex h-14 w-24 shrink-0 items-center justify-center rounded-lg bg-primary/12 text-primary-dark">
                        <span class="material-symbols-outlined text-[24px]">business_center</span>
                    </span>
                @endif
                <div class="min-w-0 flex-1">
                    <h3 class="font-semibold text-ink">{{ $unit->name }}</h3>
                    <p class="mt-0.5 truncate text-xs text-muted">{{ $unit->description ?: 'Belum ada deskripsi.' }}</p>
                </div>
                <span class="hidden shrink-0 rounded-full bg-[#f6fbf8] px-3 py-1 text-xs font-semibold text-muted sm:inline-block">{{ $unit->status }}</span>
            </div>
        @empty
            <p class="px-5 py-8 text-sm text-muted">Belum ada departemen di unit bisnis ini.</p>
        @endforelse
    </div>
</section>

<form method="POST" action="{{ route('admin.departments.destroy', $department) }}" id="delete-department-{{ $department->id }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endsection