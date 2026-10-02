@extends('layouts.app')
@section('title', 'Dashboard Program')
@section('content')
<h1 class="text-2xl font-semibold">Dashboard Program</h1>
<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Peserta aktif', $programs->where('status','active')->count()],
        ['Program aktif', $programs->where('status','active')->count()],
        ['Pendaftaran menunggu', $pendingApplications],
        ['Perjanjian menunggu persetujuan', $pendingAgreements],
        ['Logbook perlu diperiksa', $pendingLogbooks],
        ['Hasil perlu divalidasi', $pendingOutputs],
    ] as [$label, $value])
        <article class="rounded-2xl border border-line bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold">{{ $value }}</p>
        </article>
    @endforeach
</div>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Unit</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Minggu</th></tr></thead>
        <tbody>
        @foreach($programs as $program)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $program->participant->user->name }}</td>
                <td class="px-4 py-3">{{ $program->businessUnit->name }}</td>
                <td class="px-4 py-3"><x-badge :status="$program->status" /></td>
                <td class="px-4 py-3">{{ $program->current_week }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
