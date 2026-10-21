@extends('layouts.app')
@section('title', 'Manajemen Program')
@section('content')
<div x-data="{ open: false, ready: false, name: '', action: '', blockers: [], ask(name, action, blockers) { this.name = name; this.action = action; this.blockers = blockers; this.ready = blockers.length === 0; this.open = true } }">
@if(session('error'))
    <p class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</p>
@endif
<form class="flex flex-wrap gap-3">
    <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="">Semua status</option>
        @foreach(['draft','submitted','revision','agreed','active','completed'] as $status)
            <option value="{{ $status }}" @selected(request('status')===$status)>{{ \App\Support\Status::label($status) }}</option>
        @endforeach
    </select>
    <select name="department_id" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="">Semua unit bisnis</option>
        @foreach($departments as $department)
            <option value="{{ $department->id }}" @selected(request('department_id')==$department->id)>{{ $department->name }}</option>
        @endforeach
    </select>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
</form>
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Peserta</th><th class="px-4 py-3">Mentor</th><th class="px-4 py-3">Unit Bisnis</th><th class="px-4 py-3">Departemen</th><th class="px-4 py-3">Status</th></tr></thead>
        <tbody>
        @foreach($programs as $program)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $program->participant->user->name }}</td>
                <td class="px-4 py-3">{{ $program->mentor->user->name }}</td>
                <td class="px-4 py-3">{{ $program->department->name }}</td>
                <td class="px-4 py-3">{{ $program->businessUnit->name }}</td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge :status="$program->status" />
                        @if($program->status === 'active')
                            <button type="button" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold whitespace-nowrap text-white" @click="ask(@js($program->participant->user->name), @js(route('admin.programs.complete', $program)), @js($program->completionBlockers()))">Tandai selesai</button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div x-show="open" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="open && 'flex'" @click.self="open = false" @keydown.escape.window="open = false" role="dialog" aria-modal="true" aria-labelledby="complete-program-title">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h3 id="complete-program-title" class="text-lg font-bold text-ink" x-text="ready ? 'Tandai program selesai?' : 'Belum bisa ditandai selesai'"></h3>
        <p class="mt-2 text-sm text-muted" x-text="name"></p>
        <p class="mt-3 text-sm" x-show="ready">Hasil utama, laporan akhir, dan evaluasi mentor sudah lengkap. Tandai program ini selesai?</p>
        <ul class="mt-3 grid gap-2 text-sm text-red-700" x-show="!ready">
            <template x-for="item in blockers" :key="item">
                <li x-text="item"></li>
            </template>
        </ul>
        <div class="mt-5 flex justify-end gap-3">
            <button type="button" class="rounded-lg border border-line px-4 py-2 text-sm font-semibold text-ink" @click="open = false" x-text="ready ? 'Tidak' : 'Tutup'"></button>
            <form method="POST" x-show="ready" x-bind:action="action">
                @csrf
                <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Ya</button>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
