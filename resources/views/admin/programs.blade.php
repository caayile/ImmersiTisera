@extends('layouts.app')
@section('title', 'Program')
@section('content')
<h1 class="text-2xl font-semibold">Program Management</h1>
<form class="mt-4 flex flex-wrap gap-3">
    <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="">Semua status</option>
        @foreach(['draft','submitted','revision','agreed','active','completed'] as $status)
            <option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>
        @endforeach
    </select>
    <select name="department_id" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="">Semua department</option>
        @foreach($departments as $department)
            <option value="{{ $department->id }}" @selected(request('department_id')==$department->id)>{{ $department->name }}</option>
        @endforeach
    </select>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Filter</button>
</form>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Mentor</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Unit</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr></thead>
        <tbody>
        @foreach($programs as $program)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $program->participant->user->name }}</td>
                <td class="px-4 py-3">{{ $program->mentor->user->name }}</td>
                <td class="px-4 py-3">{{ $program->department->name }}</td>
                <td class="px-4 py-3">{{ $program->businessUnit->name }}</td>
                <td class="px-4 py-3"><x-badge :status="$program->status" /></td>
                <td class="px-4 py-3">
                    @if($program->status === 'active')
                    <form method="POST" action="{{ route('admin.programs.complete', $program) }}" onsubmit="return confirm('Tandai completed?')">
                        @csrf
                        <button class="text-primary-dark">Complete</button>
                    </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
