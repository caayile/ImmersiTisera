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
        @foreach($types as $type)<option>{{ $type }}</option> @endforeach
    </select>
    <textarea name="description" rows="3" placeholder="Deskripsi" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm"></textarea>
    <input name="link" placeholder="Tautan hasil/dokumen (opsional, cth. https://...)" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm">
    <input type="file" name="file" class="text-sm">
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_main_output" value="1"> Jadikan main output</label>
    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Upload evidence</button>
</form>

<div class="mt-10">
    <h2 class="text-lg font-semibold">Riwayat hasil lintas magang</h2>
    <p class="mt-1 text-sm text-muted">Semua bukti kerja dari setiap siklus magang, terbaru di atas.</p>
    <div class="mt-4 grid gap-3 rounded-2xl border border-line bg-white p-4 md:grid-cols-[1fr_180px_150px]">
        <input id="output-search" type="search" placeholder="Cari judul atau deskripsi..." class="w-full rounded-lg border border-line px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
        <select id="output-type-filter" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm">
            <option value="">Semua tipe</option>
        </select>
        <select id="output-sort" class="w-full rounded-lg border border-line px-3 py-2.5 text-sm">
            <option value="newest">Terbaru dulu</option>
            <option value="oldest">Terlama dulu</option>
        </select>
    </div>

    @php $hasItems = $programs->contains(fn ($cycle) => $cycle->outputs->where('is_final_report', false)->isNotEmpty()); @endphp
    @if(! $hasItems)
        <x-empty class="mt-6" title="Belum ada hasil" />
    @endif

    <div id="output-cycles" class="mt-4 space-y-6">
        @foreach($programs as $cycle)
            @php $items = $cycle->outputs->where('is_final_report', false)->sortByDesc('created_at')->values(); @endphp
            @if($items->isNotEmpty())
                <section class="output-cycle overflow-hidden rounded-2xl border border-line bg-white" data-cycle="{{ $cycle->businessUnit?->name }}">
                    <header class="border-b border-line bg-bg/60 px-5 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-semibold">{{ $cycle->businessUnit?->name ?: 'Unit Bisnis' }}</h3>
                            <x-badge :status="$cycle->status" />
                        </div>
                        <p class="mt-1 text-xs text-muted">
                            {{ $cycle->department?->name }}
                            @if($cycle->start_date || $cycle->end_date)
                                · {{ $cycle->start_date?->format('M Y') ?: '—' }} – {{ $cycle->end_date?->format('M Y') ?: '—' }}
                            @endif
                            · {{ $items->count() }} hasil
                        </p>
                    </header>
                    <ul class="divide-y divide-line">
                        @foreach($items as $output)
                            <li class="output-item px-5 py-4"
                                data-title="{{ strtolower($output->title.' '.$output->description) }}"
                                data-type="{{ $output->type }}"
                                data-status="{{ $output->status }}"
                                data-ts="{{ $output->created_at?->timestamp ?: 0 }}">
                                <div class="flex items-start justify-between gap-3">
                                    <h4 class="flex min-w-0 items-center gap-2 font-medium">
                                        @if($output->is_main_output)
                                            <span class="material-symbols-outlined shrink-0 text-[18px] text-amber-500" title="Main output">star</span>
                                        @endif
                                        <span class="min-w-0 truncate">{{ $output->title }}</span>
                                    </h4>
                                    <x-badge :status="$output->status" :label="\App\Support\Status::outputLabel($output->status)" />
                                </div>
                                <p class="mt-1 text-xs text-muted">
                                    {{ $output->type }} · {{ $cycle->businessUnit?->name }} · diunggah {{ $output->created_at?->format('d M Y') }}
                                    @if($output->is_main_output)
                                        · <span class="font-semibold text-amber-600">Main output</span>
                                    @endif
                                </p>
                                <details class="mt-2 text-sm">
                                    <summary class="cursor-pointer font-semibold text-primary-dark">Lihat detail</summary>
                                    <p class="mt-2">{{ $output->description }}</p>
                                    @if($output->mentor_feedback)
                                        <p class="mt-2 text-primary-dark">Feedback mentor: {{ $output->mentor_feedback }}</p>
                                    @endif
                                    @if($output->linkUrl())
                                        <a href="{{ $output->linkUrl() }}" target="_blank" rel="noopener" class="mt-2 inline-block font-semibold text-primary-dark">Buka tautan</a>
                                    @endif
                                    @if($output->file_path)
                                        <a href="{{ asset('storage/'.$output->file_path) }}" class="mt-2 inline-block font-semibold text-primary-dark">Unduh file</a>
                                    @endif
                                </details>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endforeach
    </div>

    <p id="output-empty-search" class="mt-6 hidden text-center text-sm text-muted">Tidak ada hasil yang cocok dengan pencarian.</p>
    <div class="mt-6 text-center">
        <button id="output-more" type="button" class="hidden rounded-xl border border-line bg-white px-5 py-2.5 text-sm font-semibold text-ink transition hover:border-primary hover:text-primary-dark">Muat lebih banyak</button>
    </div>
</div>

<script>
(function () {
    var search = document.getElementById('output-search');
    var typeFilter = document.getElementById('output-type-filter');
    var sort = document.getElementById('output-sort');
    var moreBtn = document.getElementById('output-more');
    var emptyMsg = document.getElementById('output-empty-search');
    var items = Array.prototype.slice.call(document.querySelectorAll('.output-item'));
    if (!items.length) return;

    var PAGE = 8;
    var shown = PAGE;

    var types = {};
    items.forEach(function (item) {
        if (item.dataset.type && !types[item.dataset.type]) {
            types[item.dataset.type] = true;
            var option = document.createElement('option');
            option.value = item.dataset.type;
            option.textContent = item.dataset.type;
            typeFilter.appendChild(option);
        }
    });

    function matches(item) {
        var query = search.value.trim().toLowerCase();
        if (query && item.dataset.title.indexOf(query) === -1) return false;
        if (typeFilter.value && item.dataset.type !== typeFilter.value) return false;
        return true;
    }

    function render() {
        var visible = items.filter(matches);
        visible.sort(function (a, b) {
            var diff = Number(a.dataset.ts) - Number(b.dataset.ts);
            return sort.value === 'oldest' ? diff : -diff;
        });

        document.querySelectorAll('.output-cycle').forEach(function (cycle) {
            var list = cycle.querySelector('ul');
            if (list) list.innerHTML = '';
        });

        visible.forEach(function (item, index) {
            item.classList.toggle('hidden', index >= shown);
            var section = item.closest('.output-cycle');
            var list = section ? section.querySelector('ul') : null;
            if (list) list.appendChild(item);
        });

        document.querySelectorAll('.output-cycle').forEach(function (cycle) {
            var hasVisible = cycle.querySelectorAll('.output-item:not(.hidden)').length > 0;
            cycle.classList.toggle('hidden', !hasVisible);
        });

        emptyMsg.classList.toggle('hidden', visible.length > 0);
        moreBtn.classList.toggle('hidden', visible.length <= shown);
    }

    search.addEventListener('input', function () { shown = PAGE; render(); });
    typeFilter.addEventListener('change', function () { shown = PAGE; render(); });
    sort.addEventListener('change', render);
    moreBtn.addEventListener('click', function () { shown += PAGE; render(); });

    render();
})();
</script>
@endunless
@endsection
