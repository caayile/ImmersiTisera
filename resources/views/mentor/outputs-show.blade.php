@extends('layouts.app')
@section('title', 'Hasil')
@section('content')
<div>
    <a href="{{ route('mentor.outputs') }}" class="text-sm font-medium text-primary-dark">&larr; Kembali ke daftar hasil</a>
    <h1 class="mt-3 text-2xl font-semibold">Hasil {{ $program->participant->user->name }}</h1>
    <p class="mt-1 text-sm text-muted">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</p>
</div>
<div class="mt-6 space-y-4">
    @forelse($program->outputs->sortByDesc('created_at') as $output)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $output->title }}</p>
                <x-badge :status="$output->status" :label="\App\Support\Status::outputLabel($output->status)" />
            </div>
            <p class="mt-2 text-sm text-muted">{{ \App\Support\Status::outputTypeLabel($output->type) }}
                @if($output->is_main_output)
                    · Hasil utama
                @endif
                @if($output->is_final_report)
                    · Laporan akhir
                @endif
            </p>
            <p class="mt-2 text-sm">{{ $output->description }}</p>
            @if($output->linkUrl())<a href="{{ $output->linkUrl() }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm font-semibold text-primary-dark">Buka tautan hasil</a>@endif
            @if($output->hasilFileUrl())<a href="{{ $output->hasilFileUrl() }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm font-semibold text-primary-dark">Unduh berkas hasil</a>@endif
            @if($output->laporanLinkUrl())<a href="{{ $output->laporanLinkUrl() }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm font-semibold text-primary-dark">Buka tautan laporan</a>@endif
            @if($output->file_path)<a href="{{ asset('storage/'.$output->file_path) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm font-semibold text-primary-dark">Lihat / unduh bukti</a>@else<p class="mt-2 text-xs text-muted">Belum ada berkas dilampirkan.</p>@endif
            <form method="POST" action="{{ route('mentor.outputs.review', $output) }}" class="mt-3 grid gap-2 md:grid-cols-[1fr_160px_auto]">
                @csrf
                <input name="mentor_feedback" value="{{ $output->mentor_feedback }}" placeholder="Catatan mentor" class="rounded-lg border border-line px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="approved" @selected($output->status === 'approved')>Setujui</option>
                    <option value="revision" @selected($output->status === 'revision')>Minta revisi</option>
                </select>
                <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </form>
        </article>
    @empty
        <x-empty title="Belum ada hasil dari peserta ini" />
    @endforelse
</div>
@endsection
