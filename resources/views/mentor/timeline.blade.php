@extends('layouts.app')
@section('title', 'Timeline')
@section('content')
<h1 class="text-2xl font-semibold">Timeline & Checkpoint</h1>
@foreach($programs as $program)
    <section class="mt-6 rounded-2xl border border-line bg-white p-5">
        <h2 class="font-medium">{{ $program->participant->user->name }}</h2>
        <div class="mt-3 grid gap-2 md:grid-cols-4">
            @foreach($program->timelines as $item)
                <div class="rounded-lg border border-line p-3 text-sm {{ $item->week == $program->current_week ? 'bg-secondary/20' : '' }}">
                    <p class="text-xs text-primary-dark">Week {{ $item->week }}</p>
                    <p>{{ $item->title }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endforeach
@endsection
