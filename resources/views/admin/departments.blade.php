@extends('layouts.app')
@section('title', 'Unit Bisnis')
@section('content')
<h1 class="text-2xl font-semibold">Unit Bisnis</h1>
<p class="mt-1 text-sm text-muted">Kelola unit bisnis dan departemen di dalamnya.</p>
<form method="POST" enctype="multipart/form-data" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-2">
    @csrf
    <input name="name" placeholder="Nama" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input name="subtitle" placeholder="Subtitle (nama lengkap, mis. Tiga Serangkai Pustaka Mandiri)" class="rounded-lg border border-line px-3 py-2 text-sm">
    <input name="area" placeholder="Area" class="rounded-lg border border-line px-3 py-2 text-sm">
    <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
    <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah</button>
</form>
<div class="mt-6 grid gap-4 md:grid-cols-2">
    @foreach($departments as $department)
        <article class="rounded-2xl border border-line bg-white p-5" x-data="{ open: false }">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold">{{ $department->name }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ $department->description }}</p>
                    <p class="mt-2 text-sm">{{ $department->business_units_count }} departemen · {{ $department->status }}</p>
                </div>
                @if($department->imageUrl())
                    <img src="{{ $department->imageUrl() }}" alt="{{ $department->name }}" class="h-20 w-28 shrink-0 rounded-lg object-cover">
                @endif
            </div>
            <form method="POST" action="{{ route('admin.departments.update', $department) }}" enctype="multipart/form-data" class="mt-4 grid gap-2">
                @csrf @method('PUT')
                <input name="name" value="{{ $department->name }}" class="rounded-lg border border-line px-3 py-2 text-sm" required>
                <input name="subtitle" value="{{ $department->subtitle }}" placeholder="Subtitle (nama lengkap)" class="rounded-lg border border-line px-3 py-2 text-sm">
                <input name="area" value="{{ $department->area }}" placeholder="Area" class="rounded-lg border border-line px-3 py-2 text-sm">
                <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm">{{ $department->description }}</textarea>
                <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
                <div class="flex items-center gap-2">
                    <select name="status" class="flex-1 rounded-lg border border-line px-3 py-2 text-sm">
                        <option value="active" @selected($department->status === 'active')>Aktif</option>
                        <option value="disabled" @selected($department->status === 'disabled')>Nonaktif</option>
                    </select>
                    <button class="text-sm font-semibold text-primary-dark">Simpan unit bisnis</button>
                    <button type="button" class="text-sm font-semibold text-red-600" onclick="if (confirm('Hapus unit bisnis ini?')) this.closest('article').querySelector('form.delete-form').submit()">Hapus</button>
                </div>
            </form>

            <button type="button" @click="open = !open" class="mt-4 flex w-full items-center justify-between rounded-xl border border-line bg-bg px-4 py-3 text-sm font-semibold transition hover:border-primary">
                <span>Departemen</span>
                <span class="flex items-center gap-2">
                    <span class="rounded-full bg-white px-2.5 py-0.5 text-xs text-muted ring-1 ring-line">{{ $department->business_units_count }}</span>
                    <span class="material-symbols-outlined text-base transition transform" :class="open && 'rotate-180'">expand_more</span>
                </span>
            </button>

            <div x-show="open" x-cloak class="mt-4 space-y-4">
                <form method="POST" action="{{ route('admin.units') }}" enctype="multipart/form-data" class="grid gap-2 rounded-xl border border-line bg-bg/40 p-4">
                    @csrf
                    <input type="hidden" name="department_id" value="{{ $department->id }}">
                    <input name="name" placeholder="Nama departemen baru" class="rounded-lg border border-line px-3 py-2 text-sm" required>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
                        <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <option value="">Assign mentor</option>
                            @foreach($mentors as $mentor)<option value="{{ $mentor->id }}">{{ $mentor->user->name }}</option>@endforeach
                        </select>
                    </div>
                    <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm"></textarea>
                    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah departemen</button>
                </form>

                @forelse($department->businessUnits as $unit)
                    <div class="rounded-xl border border-line bg-bg/40 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="font-semibold">{{ $unit->name }}</h3>
                                <p class="mt-1 text-xs text-muted">
                                    Mentor: {{ $unit->mentors->pluck('user.name')->join(', ') ?: '—' }}
                                    <span class="mx-1">·</span>
                                    <span class="{{ $unit->status === 'open' ? 'text-primary-dark' : 'text-red-500' }}">{{ $unit->status === 'open' ? 'Lowongan dibuka' : 'Lowongan ditutup' }}</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($unit->imageUrl())
                                    <img src="{{ $unit->imageUrl() }}" alt="{{ $unit->name }}" class="h-12 w-20 rounded-lg object-cover">
                                @endif
                                <a href="{{ route('units.show', $unit) }}" class="text-xs font-semibold text-primary-dark">Lihat publik ↗</a>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.units.update', $unit) }}" enctype="multipart/form-data" class="mt-3 grid gap-2 md:grid-cols-2">
                            @csrf @method('PUT')
                            <input name="name" value="{{ $unit->name }}" class="rounded-lg border border-line px-3 py-2 text-sm" required>
                            <input name="period" value="{{ $unit->period }}" placeholder="Periode lowongan" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <textarea name="description" placeholder="Deskripsi" class="col-span-full rounded-lg border border-line px-3 py-2 text-sm">{{ $unit->description }}</textarea>
                            <textarea name="work_done" placeholder="Yang dikerjakan" class="rounded-lg border border-line px-3 py-2 text-sm">{{ $unit->work_done }}</textarea>
                            <textarea name="example_activities" placeholder="Contoh aktivitas" class="rounded-lg border border-line px-3 py-2 text-sm">{{ $unit->example_activities }}</textarea>
                            <textarea name="requirements" placeholder="Persyaratan" class="col-span-full rounded-lg border border-line px-3 py-2 text-sm">{{ $unit->requirements }}</textarea>
                            <input name="relevant_programs" value="{{ implode(', ', $unit->relevant_programs ?? []) }}" placeholder="Prodi relevan, pisahkan dengan koma" class="col-span-full rounded-lg border border-line px-3 py-2 text-sm">
                            <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
                                <option value="">Tanpa perubahan mentor</option>
                                @foreach($mentors as $mentor)<option value="{{ $mentor->id }}">{{ $mentor->user->name }}</option>@endforeach
                            </select>
                            <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                                <option value="open" @selected($unit->status === 'open')>Lowongan dibuka</option>
                                <option value="closed" @selected($unit->status === 'closed')>Lowongan ditutup</option>
                            </select>
                            <div class="col-span-full flex items-center justify-between gap-3">
                                <span></span>
                                <div class="flex items-center gap-4">
                                    <button class="text-sm font-semibold text-primary-dark">Simpan</button>
                                    <button type="button" class="text-sm font-semibold text-red-600" onclick="if (confirm('Hapus departemen ini?')) document.getElementById('delete-unit-{{ $unit->id }}').submit()">Hapus</button>
                                </div>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.units.destroy', $unit) }}" id="delete-unit-{{ $unit->id }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-muted">Belum ada departemen.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="delete-form hidden">
                @csrf @method('DELETE')
            </form>
        </article>
    @endforeach
</div>
@endsection