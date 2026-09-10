@extends('layouts.app')
@section('title', 'Pendaftaran')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold">Program / Pendaftaran</h1>
        <p class="mt-1 text-sm text-muted">Form pendaftaran dan surat persetujuan: dosen → admin → mentor → admin → dosen.</p>
    </div>
    <a href="{{ route('departments.index') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Daftar program</a>
</div>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase tracking-wide text-muted">
            <tr>
                <th class="px-4 py-3">Nomor surat</th>
                <th class="px-4 py-3">Unit Bisnis</th>
                <th class="px-4 py-3">Departemen</th>
                <th class="px-4 py-3">Mentor</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($apps as $app)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $app->letter_number ?? '—' }}</td>
                <td class="px-4 py-3">{{ $app->department->name }}</td>
                <td class="px-4 py-3">{{ $app->businessUnit->name }}</td>
                <td class="px-4 py-3">{{ $app->mentor?->user?->name ?? '—' }}</td>
                <td class="px-4 py-3"><x-badge :status="$app->status" /></td>
                <td class="px-4 py-3">
                    <a href="{{ route('participant.applications.show', $app) }}" class="font-semibold text-primary-dark">Lihat surat</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-10 text-center text-muted">Belum ada pengajuan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
