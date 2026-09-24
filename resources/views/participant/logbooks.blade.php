@extends('layouts.app')
@section('title', 'Logbook')
@section('content')
<x-back-link />
<div class="mt-4">
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Immersion · Rekam jejak</p>
    <h1 class="text-2xl font-semibold">Logbook</h1>
    <p class="mt-1 text-sm text-muted">Refleksi harian selama program magang dosen. Diisi oleh peserta, lalu diverifikasi mentor.</p>
</div>

@unless($program)
    <x-empty class="mt-6" title="Logbook terbuka setelah program aktif">
        Belum ada program magang dosen untuk Anda. Lengkapi profil lalu ajukan minat ke unit bisnis.
    </x-empty>
@else
    <div class="mt-6 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        @if($program->status === 'active')
            <form method="POST" class="h-fit rounded-2xl border border-line bg-white p-6">
                @csrf
                <h2 class="font-semibold">Daily logbook</h2>
                <div class="mt-4 space-y-3">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Tanggal
                        <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">What I did
                        <textarea name="what_did" rows="2" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('what_did') }}</textarea>
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">What I learned
                        <textarea name="what_learned" rows="2" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('what_learned') }}</textarea>
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">What I found
                        <textarea name="what_found" rows="2" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('what_found') }}</textarea>
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Kendala
                        <textarea name="obstacles" rows="2" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm">{{ old('obstacles') }}</textarea>
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Output hari ini
                        <textarea name="output" rows="2" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm">{{ old('output') }}</textarea>
                    </label>
                    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Submit</button>
                </div>
            </form>
        @else
            <div class="h-fit rounded-2xl border border-line bg-white p-6">
                <h2 class="font-semibold">Belum bisa diisi</h2>
                <p class="mt-2 text-sm text-muted">
                    Logbook hanya terbuka saat program status <b>Aktif</b>. Status saat ini: <b>{{ \App\Support\Status::label($program->status) }}</b>.
                </p>
            </div>
        @endif

        <div class="space-y-3">
            @forelse($program->logbooks as $log)
                <article class="rounded-2xl border border-line bg-white p-5 text-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold">{{ $log->date->format('d M Y') }}<span class="ml-2 font-normal text-muted">{{ $log->activity }}</span></p>
                        <div class="flex items-center gap-2">
                            @if($log->status === 'approved')
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Terverifikasi</span>
                            @endif
                            <x-badge :status="$log->status" />
                        </div>
                    </div>
                    <p class="mt-2"><b>Did:</b> {{ $log->what_i_did }}</p>
                    <p><b>Learned:</b> {{ $log->what_i_learned }}</p>
                    <p><b>Found:</b> {{ $log->what_i_found }}</p>
                    @if($log->value)<p class="mt-2"><b>Kendala:</b> {{ $log->value }}</p>@endif
                    @if($log->next_action)<p><b>Output hari ini:</b> {{ $log->next_action }}</p>@endif
                    @if($log->mentor_feedback)<p class="mt-2 text-primary-dark"><b>Feedback mentor:</b> {{ $log->mentor_feedback }}</p>@endif
                </article>
            @empty
                <div class="rounded-2xl border border-line bg-white p-5 text-sm text-muted">Belum ada entri logbook.</div>
            @endforelse
        </div>
    </div>
@endunless
@endsection