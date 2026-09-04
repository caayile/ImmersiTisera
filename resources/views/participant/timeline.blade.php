@extends('layouts.app')
@section('title', 'Timeline')
@section('content')
@php
    $timelineStep = 1;
    if ($program?->status === 'completed') {
        $timelineStep = 5;
    } elseif ($program?->status === 'active') {
        $week = $program->current_week ?? 1;
        $timelineStep = $week <= 2 ? 1 : ($week <= 4 ? 2 : ($week <= 7 ? 3 : 4));
    }
@endphp
<x-framework-phases />
@unless($program)
    <x-empty class="mt-10" title="Timeline belum aktif">Timeline muncul setelah program ACTIVE.</x-empty>
@else
    <x-program-timeline :current="$timelineStep" :current-week="$program->current_week" class="mt-14" />
@endunless
@endsection
