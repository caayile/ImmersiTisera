@extends('layouts.app')
@section('title', 'Mentoring')
@section('content')
<h1 class="text-2xl font-semibold">Weekly Mentoring</h1>
<form method="POST" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-6 md:grid-cols-2">
    @csrf
    <select name="program_id" class="rounded-lg border border-line px-3 py-2 text-sm" required>
        @foreach($programs as $program)
            <option value="{{ $program->id }}">{{ $program->participant->user->name }}</option>
        @endforeach
    </select>
    <input type="number" name="week" min="1" max="8" value="1" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input type="date" name="session_date" value="{{ now()->toDateString() }}" class="rounded-lg border border-line px-3 py-2 text-sm">
    <select name="checkpoint_status" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="on_track">On track</option>
        <option value="need_improvement">Need improvement</option>
    </select>
    <textarea name="findings" placeholder="Apa yang sudah ditemukan?" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <textarea name="current_work" placeholder="Apa yang sedang dikerjakan?" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <textarea name="next_action" placeholder="Apa langkah berikutnya?" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <textarea name="feedback" placeholder="Feedback" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan sesi</button>
</form>
<div class="mt-6 space-y-3">
    @foreach($sessions as $session)
        <article class="rounded-2xl border border-line bg-white p-5 text-sm">
            <p class="font-medium">Minggu {{ $session->week }} · {{ $session->program->participant->user->name }}</p>
            <p class="mt-2">{{ $session->findings }}</p>
        </article>
    @endforeach
</div>
@endsection
