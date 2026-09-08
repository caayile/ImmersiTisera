@extends('layouts.app')
@section('title', 'Departemen')
@section('content')
<h1 class="text-2xl font-semibold">Departemen</h1>
<p class="mt-1 text-sm text-muted">Kelola departemen di tiap unit bisnis.</p>
<form method="POST" enctype="multipart/form-data" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-2">
    @csrf
    <select name="department_id" class="rounded-lg border border-line px-3 py-2 text-sm">
        @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
    </select>
    <input name="name" placeholder="Nama departemen" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
    <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="">Assign mentor</option>
        @foreach($mentors as $mentor)<option value="{{ $mentor->id }}">{{ $mentor->user->name }}</option>@endforeach
    </select>
    <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah departemen</button>
</form>

@php $unitsByDepartment = $units->groupBy('department_id'); @endphp

<div class="mt-8 space-y-8">
    @foreach($departments as $department)
        <section>
            <div class="flex items-baseline justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">{{ $department->name }}</h2>
                    <p class="text-xs text-muted">{{ $department->subtitle ?: 'Unit Bisnis' }}{{ $department->area ? ' · '.$department->area : '' }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-semibold ring-1 ring-line">{{ $unitsByDepartment[$department->id]->count() ?? 0 }} departemen</span>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @forelse($unitsByDepartment[$department->id] ?? collect() as $unit)
                    <article class="rounded-2xl border border-line bg-white p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold">{{ $unit->name }}</h3>
                                <p class="mt-1 text-xs text-muted">
                                    Mentor: {{ $unit->mentors->pluck('user.name')->join(', ') ?: '—' }}
                                    <span class="mx-1">·</span>
                                    <span class="{{ $unit->status === 'open' ? 'text-primary-dark' : 'text-red-500' }}">{{ $unit->status === 'open' ? 'Lowongan dibuka' : 'Lowongan ditutup' }}</span>
                                </p>
                            </div>
                            @if($unit->imageUrl())
                                <img src="{{ $unit->imageUrl() }}" alt="{{ $unit->name }}" class="h-16 w-24 shrink-0 rounded-lg object-cover">
                            @endif
                        </div>
                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-muted">{{ $unit->description ?: 'Belum ada deskripsi.' }}</p>

                        <form method="POST" action="{{ route('admin.units.update', $unit) }}" enctype="multipart/form-data" class="mt-4 grid gap-2 md:grid-cols-2">
                            @csrf @method('PUT')
                            <input name="name" value="{{ $unit->name }}" class="col-span-full rounded-lg border border-line px-3 py-2 text-sm" required>
                            <input name="period" value="{{ $unit->period }}" placeholder="Periode lowongan" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <textarea name="description" placeholder="Deskripsi" class="col-span-full rounded-lg border border-line px-3 py-2 text-sm">{{ $unit->description }}</textarea>
                            <textarea name="work_done" placeholder="Yang dikerjakan" class="rounded-lg border border-line px-3 py-2 text-sm">{{ $unit->work_done }}</textarea>
                            <textarea name="example_activities" placeholder="Contoh aktivitas" class="rounded-lg border border-line px-3 py-2 text-sm">{{ $unit->example_activities }}</textarea>
                            <textarea name="requirements" placeholder="Persyaratan" class="col-span-full rounded-lg border border-line px-3 py-2 text-sm">{{ $unit->requirements }}</textarea>
                            <input name="relevant_programs" value="{{ implode(', ', $unit->relevant_programs ?? []) }}" placeholder="Prodi relevan, pisahkan dengan koma" class="col-span-full rounded-lg border border-line px-3 py-2 text-sm">
                            <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                                <option value="open" @selected($unit->status === 'open')>Lowongan dibuka</option>
                                <option value="closed" @selected($unit->status === 'closed')>Lowongan ditutup</option>
                            </select>
                            <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
                                <option value="">Tanpa perubahan mentor</option>
                                @foreach($mentors as $mentor)<option value="{{ $mentor->id }}">{{ $mentor->user->name }}</option>@endforeach
                            </select>
                            <div class="col-span-full flex items-center justify-between gap-3">
                                <a href="{{ route('units.show', $unit) }}" class="text-xs font-semibold text-primary-dark">Lihat publik ↗</a>
                                <div class="flex items-center gap-4">
                                    <button class="text-sm font-semibold text-primary-dark">Simpan</button>
                                    <button type="button" class="text-sm font-semibold text-red-600" onclick="if (confirm('Hapus departemen ini?')) document.getElementById('delete-unit-{{ $unit->id }}').submit()">Hapus</button>
                                </div>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.units.destroy', $unit) }}" id="delete-unit-{{ $unit->id }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    </article>
                @empty
                    <p class="text-sm text-muted md:col-span-2">Belum ada departemen.</p>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
@endsection