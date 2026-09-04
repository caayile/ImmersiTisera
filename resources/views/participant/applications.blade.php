@extends('layouts.app')
@section('title', 'Pendaftaran')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold">Program / Pendaftaran</h1>
        <p class="mt-1 text-sm text-muted">Prodi → kompetensi → department → unit bisnis → mentor.</p>
    </div>
    <a href="{{ route('participant.applications.create') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Ajukan program</a>
</div>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase tracking-wide text-muted">
            <tr>
                <th class="px-4 py-3">Department</th>
                <th class="px-4 py-3">Unit Bisnis</th>
                <th class="px-4 py-3">Mentor</th>
                <th class="px-4 py-3">Skor</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($apps as $app)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $app->department->name }}</td>
                <td class="px-4 py-3">{{ $app->businessUnit->name }}</td>
                <td class="px-4 py-3">{{ $app->mentor?->user?->name ?? '—' }}</td>
                <td class="px-4 py-3">{{ $app->match_score }}% @if($app->relevance_warning)<span class="text-amber-700">warning</span>@endif</td>
                <td class="px-4 py-3"><x-badge :status="$app->status" /></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-10 text-center text-muted">Belum ada pengajuan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
