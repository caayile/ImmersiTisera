@extends('layouts.app')
@section('title', 'Unit Bisnis')
@section('content')
<h1 class="text-2xl font-semibold">Departemen</h1>
<form method="POST" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-2">
    @csrf
    <select name="department_id" class="rounded-lg border border-line px-3 py-2 text-sm">
        @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
    </select>
    <input name="name" placeholder="Nama departemen" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input name="function" placeholder="Fungsi" class="rounded-lg border border-line px-3 py-2 text-sm">
    <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="">Assign mentor</option>
        @foreach($mentors as $mentor)<option value="{{ $mentor->id }}">{{ $mentor->user->name }}</option>@endforeach
    </select>
    <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <textarea name="work_done" placeholder="Yang dikerjakan" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <textarea name="example_activities" placeholder="Contoh aktivitas" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <textarea name="requirements" placeholder="Persyaratan" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <input name="relevant_programs" placeholder="Prodi relevan, pisahkan dengan koma" class="rounded-lg border border-line px-3 py-2 text-sm">
    <input name="period" placeholder="Periode lowongan" class="rounded-lg border border-line px-3 py-2 text-sm">
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah departemen</button>
</form>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Departemen</th><th class="px-4 py-3">Unit Bisnis</th><th class="px-4 py-3">Mentor</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Edit</th></tr></thead>
        <tbody>
        @foreach($units as $unit)
            <tr class="border-t border-line">
                <td colspan="5" class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.units.update', $unit) }}" class="grid gap-2 md:grid-cols-6">
                        @csrf @method('PUT')
                        <input name="name" value="{{ $unit->name }}" class="rounded-lg border border-line px-3 py-2 text-sm" required>
                        <select name="department_id" class="rounded-lg border border-line px-3 py-2 text-sm">
                            @foreach($departments as $department)<option value="{{ $department->id }}" @selected($unit->department_id === $department->id)>{{ $department->name }}</option>@endforeach
                        </select>
                        <input name="function" value="{{ $unit->function }}" placeholder="Fungsi" class="rounded-lg border border-line px-3 py-2 text-sm">
                        <input name="description" value="{{ $unit->description }}" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">
                        <textarea name="work_done" placeholder="Yang dikerjakan" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-3">{{ $unit->work_done }}</textarea>
                        <textarea name="example_activities" placeholder="Contoh aktivitas" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-3">{{ $unit->example_activities }}</textarea>
                        <textarea name="requirements" placeholder="Persyaratan" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-3">{{ $unit->requirements }}</textarea>
                        <input name="relevant_programs" value="{{ implode(', ', $unit->relevant_programs ?? []) }}" placeholder="Prodi relevan, pisahkan dengan koma" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-3">
                        <input name="period" value="{{ $unit->period }}" placeholder="Periode lowongan" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-3">
                        <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <option value="open" @selected($unit->status === 'open')>Lowongan dibuka</option>
                            <option value="closed" @selected($unit->status === 'closed')>Lowongan ditutup</option>
                        </select>
                        <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <option value="">Tanpa perubahan mentor</option>
                            @foreach($mentors as $mentor)<option value="{{ $mentor->id }}">{{ $mentor->user->name }}</option>@endforeach
                        </select>
                        <button class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white">Simpan</button>
                        </form>
                </td>
                    </tr>
                    <tr class="border-t border-line bg-bg/40">
                    <td colspan="5" class="px-4 pb-3 pt-0 text-xs text-muted">Mentor saat ini: {{ $unit->mentors->pluck('user.name')->join(', ') ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
