@extends('layouts.app')
@section('title', 'Output & Evidence')
@section('content')
<h1 class="text-2xl font-semibold">Output & Evidence</h1>
@unless($program)
    <x-empty class="mt-6" title="Output belum tersedia" />
@else
<p class="mt-2 text-sm text-muted">Setiap program wajib memiliki satu main output.</p>
<form method="POST" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-2xl border border-line bg-white p-6">
    @csrf
    <input name="title" placeholder="Judul output" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    <select name="type" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm">
        @foreach($types as $type)<option>{{ $type }}</option>@endforeach
    </select>
    <textarea name="description" rows="3" placeholder="Deskripsi" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm"></textarea>
    <input type="file" name="file" class="text-sm">
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_main_output" value="1"> Jadikan main output</label>
    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Upload evidence</button>
</form>
<div class="mt-6 grid gap-4 md:grid-cols-2">
    @foreach($program->outputs->where('is_final_report', false) as $output)
        <article class="rounded-2xl border border-line bg-white p-5 text-sm">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-medium">{{ $output->title }}</h2>
                <x-badge :status="$output->status" />
            </div>
            <p class="mt-2 text-muted">{{ $output->type }} @if($output->is_main_output)· Main output@endif</p>
            <p class="mt-2">{{ $output->description }}</p>
            @if($output->mentor_feedback)<p class="mt-2 text-primary-dark">{{ $output->mentor_feedback }}</p>@endif
            @if($output->file_path)<a href="{{ asset('storage/'.$output->file_path) }}" class="mt-2 inline-block text-primary-dark">Unduh file</a>@endif
        </article>
    @endforeach
</div>
@endif
@endsection
