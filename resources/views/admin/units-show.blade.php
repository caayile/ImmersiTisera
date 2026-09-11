@extends('layouts.app')
@section('title', $department->name)
@section('content')
<a href="{{ route('admin.units') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted transition hover:text-primary-dark">
    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
    Daftar unit bisnis
</a>

<div class="mt-3 flex items-center gap-3">
    @if($department->imageUrl())
        <img src="{{ $department->imageUrl() }}" alt="{{ $department->name }}" class="h-14 w-20 rounded-xl object-cover">
    @endif
    <div>
        <h1 class="text-2xl font-semibold">{{ $department->name }}</h1>
        <p class="text-sm text-muted">{{ $department->subtitle }}{{ $department->subtitle && $department->area ? ' · ' : '' }}{{ $department->area }}</p>
    </div>
</div>

{{-- ===== PENGATURAN LOKASI ===== --}}
<section class="mt-6 rounded-2xl border border-line bg-white p-5">
    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
        <span class="material-symbols-outlined text-[18px] text-primary-dark">location_on</span>
        Pengaturan Lokasi
    </h2>
    <p class="mt-1 text-xs text-muted">Isi lokasi yang ditampilkan di peta halaman peserta. Perubahan langsung otomatis tampil di publik.</p>
    <form method="POST" action="{{ route('admin.departments.update', $department) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
        @csrf @method('PUT')
        <input type="hidden" name="name" value="{{ $department->name }}">
        <input type="hidden" name="status" value="{{ $department->status }}">
        <input name="area" value="{{ $department->area }}" placeholder="Area / Lokasi (mis. Surakarta)" class="rounded-lg border border-line px-3 py-2 text-sm">
        <input name="map_url" value="{{ $department->map_url }}" placeholder="Link Google Maps / peta (opsional)" class="rounded-lg border border-line px-3 py-2 text-sm">
        <p class="col-span-full text-xs text-muted">Link peta diambil dari Google Maps (tombol <b>Bagikan</b> → Salin link). Kosongkan untuk memakai pencarian area otomatis.</p>
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white md:col-span-2">Simpan pengaturan lokasi</button>
    </form>
</section>

{{-- ===== LIST VIEW DEPARTEMEN ===== --}}
<section class="mt-6">
    <div class="flex items-center justify-between gap-3">
        <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
            <span class="material-symbols-outlined text-[18px] text-primary-dark">apartment</span>
            Departemen di {{ $department->name }}
        </h2>
        <div class="flex items-center gap-3">
            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-line">{{ $units->count() }} departemen</span>
            <div x-data="{ showAdd: false }">
                <button type="button" @click="showAdd = !showAdd" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Departemen
                </button>
                <div x-show="showAdd" x-cloak class="absolute z-20 mt-3 w-[min(92vw,560px)] rounded-2xl border border-line bg-white p-5 shadow-xl">
                    <h3 class="text-sm font-semibold">Tambah departemen baru</h3>
                    <form method="POST" action="{{ route('admin.units') }}" enctype="multipart/form-data" class="mt-4 grid gap-3">
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $department->id }}">
                        <input name="name" placeholder="Nama departemen" class="w-full rounded-lg border border-line px-3 py-2 text-sm" required>

                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted">Informasi Program</p>
                        <textarea name="description" placeholder="Deskripsi" class="w-full rounded-lg border border-line px-3 py-2 text-sm"></textarea>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <textarea name="function" placeholder="Fungsi unit" class="w-full rounded-lg border border-line px-3 py-2 text-sm"></textarea>
                            <textarea name="work_done" placeholder="Yang dikerjakan" class="w-full rounded-lg border border-line px-3 py-2 text-sm"></textarea>
                            <textarea name="example_activities" placeholder="Contoh aktivitas" class="w-full rounded-lg border border-line px-3 py-2 text-sm"></textarea>
                            <textarea name="requirements" placeholder="Persyaratan" class="w-full rounded-lg border border-line px-3 py-2 text-sm"></textarea>
                        </div>
                        <input name="relevant_programs" placeholder="Prodi relevan, pisahkan dengan koma" class="w-full rounded-lg border border-line px-3 py-2 text-sm">

                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted">Periode Pendaftaran</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <input type="datetime-local" name="registration_start" placeholder="Waktu dibuka" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
                            <input type="datetime-local" name="registration_deadline" placeholder="Waktu ditutup" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
                            <select name="status" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
                                <option value="open">Lowongan dibuka</option>
                                <option value="closed">Lowongan ditutup</option>
                            </select>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input type="file" name="image" accept="image/*" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
                            <select name="mentor_id" class="w-full rounded-lg border border-line px-3 py-2 text-sm">
                                <option value="">Assign mentor</option>
                                @foreach($mentors as $mentor)<option value="{{ $mentor->id }}">{{ $mentor->user->name }}</option>@endforeach
                            </select>
                        </div>
                        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah departemen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-white">
        @forelse($units as $unit)
            <div class="border-b border-line px-5 py-4 last:border-0">
                <div class="flex items-center gap-4">
                    @if($unit->imageUrl())
                        <img src="{{ $unit->imageUrl() }}" alt="{{ $unit->name }}" class="h-14 w-24 shrink-0 rounded-lg object-cover">
                    @else
                        <span class="flex h-14 w-24 shrink-0 items-center justify-center rounded-lg bg-primary/12 text-primary-dark">
                            <span class="material-symbols-outlined text-[24px]">business_center</span>
                        </span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-ink">{{ $unit->name }}</h3>
                        <p class="mt-0.5 text-xs font-medium {{ $unit->isOpen() ? 'text-primary-dark' : ($unit->isScheduled() ? 'text-red-500' : 'text-muted') }}">
                        {{ $unit->isOpen() ? 'Lowongan dibuka' : ($unit->isScheduled() ? 'Lowongan ditutup' : 'Belum dijadwalkan') }}
                    </p>
                        <p class="mt-0.5 truncate text-xs text-muted">{{ $unit->description ?: 'Belum ada deskripsi.' }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <a href="{{ route('units.show', $unit) }}" title="Lihat publik" class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-muted transition hover:border-primary hover:text-primary-dark">
                            <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                        </a>
                        <a href="{{ route('admin.units.edit', $unit) }}" title="Edit" class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-muted transition hover:border-primary hover:text-primary-dark">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </a>
                        <button type="button" title="Hapus" onclick="if (confirm('Hapus departemen ini?')) document.getElementById('delete-unit-{{ $unit->id }}').submit()" class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-muted transition hover:border-red-300 hover:text-red-600">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <p class="px-5 py-8 text-sm text-muted">Belum ada departemen di unit bisnis ini. Klik "Tambah Departemen" untuk menambahkan.</p>
        @endforelse
    </div>
</section>

@foreach($units as $unit)
    <form method="POST" action="{{ route('admin.units.destroy', $unit) }}" id="delete-unit-{{ $unit->id }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endforeach
@endsection