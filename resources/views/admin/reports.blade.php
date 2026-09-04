@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Reports</h1>
    <a href="{{ route('admin.reports', ['export' => 'csv']) }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Export CSV</a>
</div>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Mentor</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Unit</th><th class="px-4 py-3">Status</th></tr></thead>
        <tbody>
        @foreach($programs as $program)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $program->participant->user->name }}</td>
                <td class="px-4 py-3">{{ $program->mentor->user->name }}</td>
                <td class="px-4 py-3">{{ $program->department->name }}</td>
                <td class="px-4 py-3">{{ $program->businessUnit->name }}</td>
                <td class="px-4 py-3"><x-badge :status="$program->status" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
