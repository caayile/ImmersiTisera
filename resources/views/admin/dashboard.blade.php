@extends('layouts.app')
@section('title', 'Admin Overview')
@section('content')
<h1 class="text-2xl font-semibold">Ringkasan Pengelola Program</h1>
<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($stats as $label => $value)
        <article class="rounded-2xl border border-line bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold">{{ $value }}</p>
        </article>
    @endforeach
</div>
<div class="mt-6 grid gap-4 lg:grid-cols-2">
    <article class="rounded-2xl border border-line bg-white p-5">
        <h2 class="font-semibold">Distribusi status program</h2>
        <canvas id="statusChart" class="mt-4"></canvas>
    </article>
    <article class="rounded-2xl border border-line bg-white p-5">
        <h2 class="font-semibold">Alur kolaborasi</h2>
        <canvas id="collabChart" class="mt-4"></canvas>
        <p class="mt-4 text-sm text-muted">Indeks kepatuhan buku catatan: {{ $logbookCompliance }}</p>
    </article>
</div>
<script>
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($statusCounts->toArray())),
            datasets: [{ data: @json(array_values($statusCounts->toArray())), backgroundColor: ['#5EC69D','#7DD8B5','#f59e0b','#38bdf8','#94a3b8','#ef4444'] }]
        }
    });
    new Chart(document.getElementById('collabChart'), {
        type: 'bar',
        data: {
            labels: ['Tutup','Tindak lanjut','Kolaborasi','Kembangkan','Perluas'],
            datasets: [{ data: [{{ $collab[0] ?? 0 }},{{ $collab[1] ?? 0 }},{{ $collab[2] ?? 0 }},{{ $collab[3] ?? 0 }},{{ $collab[4] ?? 0 }}], backgroundColor: '#5EC69D' }]
        },
        options: { plugins: { legend: { display: false } } }
    });
</script>
@endsection
