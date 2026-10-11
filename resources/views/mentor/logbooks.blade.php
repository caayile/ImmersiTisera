@extends('layouts.app')
@section('title', 'Pemeriksaan Logbook')
@section('content')
<form method="GET" class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-4 sm:flex-row">
    <select name="status" onchange="this.form.submit()" class="w-full flex-1 rounded-lg border border-line px-3 py-2 text-sm"><option value="">Semua status logbook</option>@foreach(['submitted' => 'Menunggu pemeriksaan', 'reviewed' => 'Sudah diperiksa', 'revision' => 'Perlu revisi', 'approved' => 'Disetujui'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
    <button class="shrink-0 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Terapkan filter</button>
</form>
<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white">
    @forelse($programs as $program)
        @php $pendingCount = $program->logbooks->whereIn('status', ['submitted', 'reviewed', 'draft'])->count(); @endphp
        <a href="{{ route('mentor.logbooks.show', $program) }}" class="flex items-center gap-3 border-b border-line px-4 py-3 text-sm transition last:border-0 hover:bg-bg/60">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary-dark">{{ mb_strtoupper(mb_substr(trim($program->participant->user->name), 0, 1)) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-semibold">{{ $program->participant->user->name }}</span>
                <span class="mt-0.5 block truncate text-xs text-muted">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</span>
            </span>
            @if($pendingCount)
                <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">{{ $pendingCount }} menunggu pemeriksaan</span>
            @endif
            <span class="shrink-0 rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white">Lihat rincian</span>
        </a>
    @empty
        <x-empty title="Tidak ada logbook" />
    @endforelse
</div>
@endsection
