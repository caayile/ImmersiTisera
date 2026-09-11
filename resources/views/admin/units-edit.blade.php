@extends('layouts.app')
@section('title', 'Edit '.$businessUnit->name)
@section('content')
@php $dept = $businessUnit->department; @endphp
<a href="{{ route('admin.units.show', $dept) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted transition hover:text-primary-dark">
    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
    Kembali ke departemen {{ $dept->name }}
</a>

<div class="mt-3 flex items-center gap-3">
    @if($businessUnit->imageUrl())
        <img src="{{ $businessUnit->imageUrl() }}" alt="{{ $businessUnit->name }}" class="h-14 w-20 rounded-xl object-cover">
    @endif
    <div>
        <h1 class="text-2xl font-semibold">Edit Departemen</h1>
        <p class="text-sm text-muted">{{ $businessUnit->name }} · {{ $dept->name }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.units.update', $businessUnit) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
    @csrf @method('PUT')
    <input type="hidden" name="department_id" value="{{ $businessUnit->department_id }}">

    {{-- ===== INFORMASI PROGRAM ===== --}}
    <section class="rounded-2xl border border-line bg-white p-5">
        <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
            <span class="material-symbols-outlined text-[18px] text-primary-dark">menu_book</span>
            Informasi Program
        </h2>
        <div class="mt-4 grid gap-4">
            <div>
                <label class="text-xs font-medium text-muted">Nama departemen</label>
                <input name="name" value="{{ $businessUnit->name }}" placeholder="Nama departemen" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm" required>
            </div>
            <div>
                <label class="text-xs font-medium text-muted">Deskripsi / Tentang program</label>
                <div class="mt-1">
                    <x-wysiwyg name="description" :value="$businessUnit->description" placeholder="Tuliskan deskripsi program di sini..." />
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-muted">Fungsi unit</label>
                <div class="mt-1">
                    <x-wysiwyg name="function" :value="$businessUnit->function" placeholder="Jelaskan fungsi unit ini..." />
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-muted">Yang dikerjakan</label>
                <div class="mt-1">
                    <x-wysiwyg name="work_done" :value="$businessUnit->work_done" placeholder="Apa saja yang dikerjakan..." />
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-muted">Contoh aktivitas</label>
                <div class="mt-1">
                    <x-wysiwyg name="example_activities" :value="$businessUnit->example_activities" placeholder="Contoh aktivitas di unit ini..." />
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-muted">Persyaratan</label>
                <div class="mt-1">
                    <x-wysiwyg name="requirements" :value="$businessUnit->requirements" placeholder="Persyaratan mengikuti unit ini..." />
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-3">
                <input name="relevant_programs" value="{{ implode(', ', $businessUnit->relevant_programs ?? []) }}" placeholder="Prodi relevan, pisahkan dengan koma" class="rounded-lg border border-line px-3 py-2 text-sm">
                <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
                <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="">Tanpa perubahan mentor</option>
                    @foreach($mentors as $mentor)<option value="{{ $mentor->id }}" @selected($businessUnit->mentors->contains('id', $mentor->id))>{{ $mentor->user->name }}</option>@endforeach
                </select>
            </div>
        </div>
    </section>

    {{-- ===== PERIODE PENDAFTARAN ===== --}}
    <section class="rounded-2xl border border-line bg-white p-5">
        <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
            <span class="material-symbols-outlined text-[18px] text-primary-dark">event</span>
            Periode Pendaftaran
        </h2>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div>
                <label class="text-xs font-medium text-muted">Dibuka pada</label>
                <input type="datetime-local" name="registration_start" value="{{ $businessUnit->registration_start?->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-muted">Ditutup pada</label>
                <input type="datetime-local" name="registration_deadline" value="{{ $businessUnit->registration_deadline?->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-muted">Status lowongan</label>
                <select name="status" class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="open" @selected($businessUnit->status === 'open')>Lowongan dibuka</option>
                    <option value="closed" @selected($businessUnit->status === 'closed')>Lowongan ditutup</option>
                </select>
            </div>
        </div>
        <p class="mt-3 text-xs text-muted">Pendaftaran otomatis terbuka sejak waktu dibuka dan tertutup setelah waktu ditutup. Jika tanggal penutupan kosong, lowongan berstatus <b>Akan diumumkan</b> (tertutup) sampai ditetapkan.</p>
    </section>

    <div class="flex items-center justify-end gap-4">
        <a href="{{ route('admin.units.show', $dept) }}" class="text-sm font-semibold text-muted">Batal</a>
        <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
            Simpan Perubahan
            <span class="material-symbols-outlined text-[18px]">save</span>
        </button>
    </div>
</form>
@endsection