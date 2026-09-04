@extends('layouts.app')
@section('title', 'Final Report')
@section('content')
<h1 class="text-2xl font-semibold">Final Report</h1>
@unless($program)
    <x-empty class="mt-6" title="Final report belum tersedia" />
@else
@php $report = $program->outputs->firstWhere('is_final_report', true); @endphp
<p class="mt-2 text-sm text-muted">Final report terkait agreement dan output utama program.</p>
@if($report)
    <article class="mt-6 rounded-2xl border border-line bg-white p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-medium">{{ $report->title }}</h2>
            <x-badge :status="$report->status" />
        </div>
        <p class="mt-2 text-sm">{{ $report->description }}</p>
        @if($report->mentor_feedback)<p class="mt-2 text-sm text-primary-dark">Feedback: {{ $report->mentor_feedback }}</p>@endif
        @if($report->file_path)<a href="{{ asset('storage/'.$report->file_path) }}" class="mt-2 inline-block text-sm text-primary-dark">Unduh laporan</a>@endif
    </article>
@endif
@if(!$report || $report->status === 'revision')
<form method="POST" action="{{ route('participant.final-report') }}" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-2xl border border-line bg-white p-6">
    @csrf
    <input type="hidden" name="is_final_report" value="1">
    <input type="hidden" name="type" value="Research Report">
    <input name="title" value="Final Report — {{ $program->businessUnit->name }}" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    <textarea name="description" rows="4" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm" placeholder="Ringkasan laporan akhir"></textarea>
    <input type="file" name="file" class="text-sm">
    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Kirim final report</button>
</form>
@endif
@endif
@endsection
