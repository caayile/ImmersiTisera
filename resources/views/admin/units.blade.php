@extends('layouts.app')
@section('title', 'Unit Bisnis')
@section('content')
<h1 class="text-2xl font-semibold">Unit Bisnis</h1>
<form method="POST" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-2">
    @csrf
    <select name="department_id" class="rounded-lg border border-line px-3 py-2 text-sm">
        @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
    </select>
    <input name="name" placeholder="Nama unit" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input name="function" placeholder="Fungsi" class="rounded-lg border border-line px-3 py-2 text-sm">
    <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="">Assign mentor</option>
        @foreach($mentors as $mentor)<option value="{{ $mentor->id }}">{{ $mentor->user->name }}</option>@endforeach
    </select>
    <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <textarea name="requirements" placeholder="Persyaratan" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah unit</button>
</form>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Unit</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Mentor</th><th class="px-4 py-3">Status</th></tr></thead>
        <tbody>
        @foreach($units as $unit)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $unit->name }}</td>
                <td class="px-4 py-3">{{ $unit->department->name }}</td>
                <td class="px-4 py-3">{{ $unit->mentors->pluck('user.name')->join(', ') ?: '—' }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.units.update', $unit) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="name" value="{{ $unit->name }}">
                        <input type="hidden" name="status" value="{{ $unit->status === 'open' ? 'closed' : 'open' }}">
                        <button class="text-primary-dark">{{ $unit->status }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
