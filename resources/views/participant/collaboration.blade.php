@extends('layouts.app')
@section('title', 'Kolaborasi')
@section('content')
<h1 class="text-2xl font-semibold">Kolaborasi Lanjutan</h1>
@unless($program)
    <x-empty class="mt-6" title="Pipeline kolaborasi belum ada" />
@else
@php $item = $program->collaboration; @endphp
<div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="w-full min-w-[860px] text-left text-sm">
        <thead>
            <tr class="border-b border-line text-sm font-semibold">
                <th class="px-4 py-3">No.</th>
                <th class="px-4 py-3">Level</th>
                <th class="px-4 py-3">Jenis Kolaborasi</th>
                <th class="px-4 py-3">Deskripsi</th>
                <th class="px-4 py-3">Tindak Lanjut</th>
                <th class="px-4 py-3">Penanggung Jawab</th>
                <th class="px-4 py-3">Tanggal Target</th>
            </tr>
        </thead>
        <tbody>
            @if($item)
                <tr class="border-b border-line last:border-0">
                    <td class="px-4 py-3 align-top">1</td>
                    <td class="px-4 py-3 align-top font-semibold">{{ \App\Support\Status::collaborationLevelLabel((int) ($item->level ?? 0)) }}</td>
                    <td class="px-4 py-3 align-top">{{ $item->collaboration_type ? \App\Support\Status::collaborationTypeLabel($item->collaboration_type) : '—' }}</td>
                    <td class="px-4 py-3 align-top text-muted">{{ $item->description ?? '—' }}</td>
                    <td class="px-4 py-3 align-top">{{ $item->next_action ?? '—' }}</td>
                    <td class="px-4 py-3 align-top">{{ $item->responsible_person ?? '—' }}</td>
                    <td class="px-4 py-3 align-top">{{ $item->target_date?->format('d M Y') ?? '—' }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-muted">Belum ada tindak lanjut kolaborasi.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endif
@endsection
