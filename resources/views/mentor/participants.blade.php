@extends('layouts.app')
@section('title', 'Peserta')
@section('content')
<h1 class="text-2xl font-semibold">Peserta</h1>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted">
            <tr>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">Prodi</th>
                <th class="px-4 py-3">Unit</th>
                <th class="px-4 py-3">Periode</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Progress</th>
                <th class="px-4 py-3">Logbook</th>
                <th class="px-4 py-3">Output</th>
            </tr>
        </thead>
        <tbody>
        @forelse($programs as $program)
            <tr class="border-t border-line">
                <td class="px-4 py-3"><a class="font-medium text-primary-dark" href="{{ route('mentor.participants.show', $program) }}">{{ $program->participant->user->name }}</a></td>
                <td class="px-4 py-3">{{ $program->participant->study_program }}</td>
                <td class="px-4 py-3">{{ $program->businessUnit->name }}</td>
                <td class="px-4 py-3">{{ $program->start_date?->format('d M') ?? '—' }}</td>
                <td class="px-4 py-3"><x-badge :status="$program->status" /></td>
                <td class="px-4 py-3">{{ $program->progress }}%</td>
                <td class="px-4 py-3">{{ $program->logbooks->where('status','submitted')->count() }} pending</td>
                <td class="px-4 py-3">{{ $program->outputs->where('status','submitted')->count() }} pending</td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-muted">Belum ada peserta pada unit Anda.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
