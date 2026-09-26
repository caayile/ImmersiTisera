@extends('layouts.app')
@section('title', 'Output')
@section('content')
<h1 class="text-2xl font-semibold">Validasi Output</h1>
<div class="mt-6 space-y-4">
    @forelse($outputs as $output)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $output->title }} · {{ $output->program->participant->user->name }}</p>
                <x-badge :status="$output->status" />
            </div>
<<<<<<< HEAD
            <p class="mt-2 text-sm text-muted">{{ $output->type }} @if($output->is_main_output)· Main @endif @if($output->is_final_report)· Final report @endif</p>
=======
            <p class="mt-2 text-sm text-muted">{{ $output->type }}
                @if($output->is_main_output)
                    · Main
                @endif
                @if($output->is_final_report)
                    · Final report
                @endif
            </p>
            <p class="mt-2 text-sm">{{ $output->description }}</p>
            @if($output->file_path)<a href="{{ asset('storage/'.$output->file_path) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm font-semibold text-primary-dark">Lihat / unduh bukti</a>@else<p class="mt-2 text-xs text-muted">Belum ada file dilampirkan.</p>@endif
>>>>>>> 1cbc5e81bcaa8d3eb41e83d8653ce4c0b777ef9f
            <form method="POST" action="{{ route('mentor.outputs.review', $output) }}" class="mt-3 grid gap-2 md:grid-cols-[1fr_160px_auto]">
                @csrf
                <input name="mentor_feedback" value="{{ $output->mentor_feedback }}" class="rounded-lg border border-line px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="approved">Approve</option>
                    <option value="revision">Revision</option>
                </select>
                <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </form>
        </article>
    @empty
        <x-empty title="Tidak ada output" />
    @endforelse
</div>
@endsection
