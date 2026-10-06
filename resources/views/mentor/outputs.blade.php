@extends('layouts.app')
@section('title', 'Hasil')
@section('content')
<h1 class="text-2xl font-semibold">Validasi Hasil</h1>
<p class="mt-1 text-sm text-muted">Pilih peserta untuk meninjau hasil dan bukti yang dikirim.</p>
<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white">
    @forelse($programs as $program)
        @php $pendingCount = $program->outputs->where('status', 'submitted')->count(); @endphp
        <a href="{{ route('mentor.outputs.show', $program) }}" class="flex items-center gap-3 border-b border-line px-4 py-3 text-sm transition last:border-0 hover:bg-bg/60">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary-dark">{{ mb_strtoupper(mb_substr(trim($program->participant->user->name), 0, 1)) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-semibold">{{ $program->participant->user->name }}</span>
                <span class="mt-0.5 block truncate text-xs text-muted">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</span>
            </span>
            @if($pendingCount)
                <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">{{ $pendingCount }} menunggu</span>
            @else
                <span class="shrink-0 rounded-full bg-bg px-2.5 py-1 text-[11px] font-semibold text-muted">{{ $program->outputs->count() }} hasil</span>
            @endif
            <span class="shrink-0 rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white">Lihat hasil</span>
        </a>
    @empty
        <x-empty title="Belum ada peserta dengan hasil" />
    @endforelse
</div>
@endsection
