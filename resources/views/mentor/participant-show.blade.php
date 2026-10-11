@extends('layouts.app')
@section('title', $program->participant->user->name)
@section('content')
<p class="text-sm text-primary-dark">Monitoring peserta</p>
<div class="mt-4 flex flex-wrap gap-3 text-sm">
    <x-badge :status="$program->status" />
    <span>{{ $program->businessUnit->name }}</span>
    <span>Minggu {{ $program->current_week }}</span>
</div>
<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <article class="rounded-2xl border border-line bg-white p-5 text-sm">
        <h2 class="font-semibold">Perjanjian</h2>
        <p class="mt-2">{{ $program->agreement?->objective }}</p>
        <x-badge class="mt-3" :status="$program->agreement?->status ?? 'draft'" />
    </article>
    <article class="rounded-2xl border border-line bg-white p-5 text-sm">
        <h2 class="font-semibold">Logbook terbaru</h2>
        @foreach($program->logbooks->take(3) as $log)
            <p class="mt-2">{{ $log->date->format('d M') }} · {{ $log->activity }} · {{ \App\Support\Status::label($log->status) }}</p>
        @endforeach
    </article>
</div>

<section class="mt-4 rounded-2xl border border-line bg-white p-5 text-sm">
    <h2 class="font-semibold">Laporan Akhir</h2>
    @forelse($program->outputs->where('is_final_report', true)->sortByDesc('created_at') as $report)
        <article class="mt-3 rounded-xl border border-line p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $report->title }}</p>
                <x-badge :status="$report->status" :label="$report->status === 'approved' ? 'Diterima Mentor' : \App\Support\Status::outputLabel($report->status)" />
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
                @if($report->linkUrl() ?? $report->hasilFileUrl())
                    <a href="{{ $report->linkUrl() ?? $report->hasilFileUrl() }}" target="_blank" rel="noopener" class="inline-block whitespace-nowrap rounded-lg bg-[#22b07d] px-3.5 py-1.5 text-xs font-semibold text-white">Lihat Hasil</a>
                @endif
                @if($report->laporanFileUrl() ?? $report->laporanLinkUrl())
                    <a href="{{ $report->laporanFileUrl() ?? $report->laporanLinkUrl() }}" target="_blank" rel="noopener" class="inline-block whitespace-nowrap rounded-lg bg-[#22b07d] px-3.5 py-1.5 text-xs font-semibold text-white">Lihat Laporan</a>
                @endif
            </div>
            <form method="POST" action="{{ route('mentor.outputs.review', $report) }}" class="mt-3 grid gap-2 border-t border-line pt-3 md:grid-cols-[1fr_160px_auto]">
                @csrf
                <input name="mentor_feedback" value="{{ $report->mentor_feedback }}" placeholder="Masukan (wajib jika revisi)" class="rounded-lg border border-line px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="approved" @selected($report->status === 'approved')>Terima</option>
                    <option value="revision" @selected($report->status !== 'approved')>Minta revisi</option>
                </select>
                <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </form>
        </article>
    @empty
        <p class="mt-2 text-muted">Belum ada laporan akhir dari peserta.</p>
    @endforelse
</section>
@endsection
