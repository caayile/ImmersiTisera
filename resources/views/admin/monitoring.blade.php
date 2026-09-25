@extends('layouts.app')
@section('title', 'Monitoring')
@section('content')
<h1 class="text-2xl font-semibold">Monitoring Program</h1>
<p class="mt-1 text-sm text-muted">Klik nama peserta untuk melihat seluruh isi logbook.</p>
<form method="GET" class="mt-5 grid gap-3 rounded-2xl border border-line bg-white p-4 md:grid-cols-4">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama peserta" class="rounded-lg border border-line px-3 py-2 text-sm">
    <select name="business_unit_id" class="rounded-lg border border-line px-3 py-2 text-sm"><option value="">Semua unit bisnis</option>@foreach($businessUnits as $businessUnit)<option value="{{ $businessUnit->id }}" @selected(request('business_unit_id') == $businessUnit->id)>{{ $businessUnit->name }}</option>@endforeach</select>
    <select name="department_id" class="rounded-lg border border-line px-3 py-2 text-sm"><option value="">Semua departemen</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>@endforeach</select>
    <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm"><option value="">Semua status logbook</option>@foreach(['submitted' => 'Menunggu review', 'reviewed' => 'Reviewed', 'revision' => 'Perlu revisi', 'approved' => 'Disetujui'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white md:col-span-4 md:justify-self-end">Terapkan filter</button>
</form>
<div class="mt-6 space-y-3">
@forelse($programs as $program)
<a href="{{ route('admin.monitoring.show', $program) }}" class="block rounded-2xl border border-line bg-white p-4 text-sm transition hover:border-primary hover:shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="font-semibold">{{ $program->participant->user->name }}</p><p class="mt-1 text-xs text-muted">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</p></div><span class="text-xs font-semibold text-primary-dark">{{ $program->logbooks->count() }} entri · Buka detail</span></div>
</a>
@empty
    <x-empty title="Tidak ada logbook" />
@endforelse
</div>
@endsection
