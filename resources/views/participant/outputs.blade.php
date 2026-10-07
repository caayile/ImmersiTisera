@extends('layouts.app')
@section('title', 'Hasil dan Bukti')
@section('content')
@php
    $hasMainOutput = $program
        ? $program->outputs->where('is_final_report', false)->contains(fn ($item) => $item->is_main_output)
        : false;
    $currentOutputs = $program
        ? $program->outputs->where('is_final_report', false)->sortByDesc('created_at')->values()
        : collect();
@endphp

<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold">Hasil dan Bukti</h1>
        <p class="mt-1 max-w-2xl text-sm text-muted">Unggah bukti kerja magang Anda. Setiap program wajib punya satu <b>hasil utama</b> yang akan dinilai mentor.</p>
    </div>
    @if($program)
        <div class="rounded-xl border border-line bg-white px-4 py-3 text-sm shadow-sm">
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Program aktif</p>
            <p class="mt-0.5 font-medium">{{ $program->businessUnit?->name ?? '—' }}</p>
            <p class="text-xs text-muted">{{ $program->department?->name }}</p>
        </div>
    @endif
</div>

@unless($program)
    <x-empty class="mt-6" title="Hasil belum tersedia">
        Hasil dan bukti muncul setelah program magang Anda aktif.
    </x-empty>
@else
    @if($errors->any())
        <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</p>
    @endif

    <div class="mt-6 grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-line bg-white px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Langkah 1</p>
            <p class="mt-1 text-sm font-semibold">Isi judul & jenis</p>
            <p class="mt-0.5 text-xs text-muted">Jelaskan apa yang dihasilkan.</p>
        </div>
        <div class="rounded-2xl border border-line bg-white px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Langkah 2</p>
            <p class="mt-1 text-sm font-semibold">Lampirkan bukti</p>
            <p class="mt-0.5 text-xs text-muted">File atau tautan dokumen.</p>
        </div>
        <div class="rounded-2xl border border-line bg-white px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Langkah 3</p>
            <p class="mt-1 text-sm font-semibold">Tandai hasil utama</p>
            <p class="mt-0.5 text-xs text-muted">Wajib satu per program.</p>
        </div>
    </div>

    @if($hasMainOutput)
        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900">
            <span class="material-symbols-outlined mt-0.5 text-[20px] text-emerald-700">check_circle</span>
            <div>
                <p class="font-semibold">Hasil utama sudah ditetapkan</p>
                <p class="mt-0.5 text-xs text-emerald-800/80">Anda masih bisa mengunggah bukti tambahan. Centang “Jadikan hasil utama” hanya jika ingin mengganti hasil utama.</p>
            </div>
        </div>
    @else
        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-950">
            <span class="material-symbols-outlined mt-0.5 text-[20px] text-amber-700">star</span>
            <div>
                <p class="font-semibold">Belum ada hasil utama</p>
                <p class="mt-0.5 text-xs text-amber-900/80">Centang “Jadikan hasil utama” pada unggahan yang paling mewakili capaian magang Anda.</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ url('/participant/outputs') }}" enctype="multipart/form-data" class="mt-6 space-y-5 rounded-2xl border border-line bg-white p-6 shadow-sm md:p-8" x-data="{ fileName: '' }">
        @csrf

        <div class="flex items-center gap-2.5 border-b border-line pb-4">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/12 text-primary-dark">
                <span class="material-symbols-outlined text-[20px]">upload_file</span>
            </span>
            <div>
                <h2 class="text-lg font-semibold">Form unggah bukti</h2>
                <p class="text-xs text-muted">Lengkapi field bertanda wajib, lalu kirim untuk ditinjau mentor.</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="text-xs font-medium text-muted">Judul hasil <span class="text-red-500">*</span></label>
                <input name="title" value="{{ old('title') }}" maxlength="180" placeholder="Contoh: Ringkasan observasi alur kerja Digital Business" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                <p class="mt-1 text-[11px] text-muted">Pakai nama yang mudah dikenali mentor.</p>
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs font-medium text-muted">Jenis hasil <span class="text-red-500">*</span></label>
                <select name="type" class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" required>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected(old('type', 'Bukti Dokumentasi') === $type)>{{ \App\Support\Status::outputTypeLabel($type) }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px] text-muted">Pilih kategori yang paling dekat dengan bukti Anda.</p>
            </div>

            <div>
                <label class="text-xs font-medium text-muted">Tautan bukti (opsional)</label>
                <input name="link" value="{{ old('link') }}" placeholder="https://drive.google.com/..." class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
                <p class="mt-1 text-[11px] text-muted">Isi tautan <b>atau</b> unggah file di bawah — tidak keduanya.</p>
                @error('link')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="text-xs font-medium text-muted">Deskripsi singkat</label>
            <textarea name="description" rows="4" placeholder="Jelaskan isi bukti, konteks pengerjaan, dan manfaatnya bagi unit bisnis." class="mt-1 w-full rounded-lg border border-line px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">{{ old('description') }}</textarea>
        </div>

        <div class="rounded-xl border border-dashed border-line bg-bg/50 p-4">
            <label class="text-xs font-medium text-muted">Berkas bukti (opsional)</label>
            <p class="mt-0.5 text-[11px] text-muted">PDF, Word, PowerPoint, gambar, atau ZIP · maks. 10 MB</p>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <input id="output-file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.ppt,.pptx,.zip" class="peer sr-only" @change="fileName = $event.target.files[0]?.name ?? ''">
                <label for="output-file" class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:border-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary">
                    <span class="material-symbols-outlined text-[18px]">attach_file</span>
                    Pilih file
                </label>
                <span class="min-w-[12rem] flex-1 rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-muted" x-text="fileName || 'Belum ada file dipilih'">Belum ada file dipilih</span>
            </div>
            @error('file')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 transition hover:border-amber-300">
            <input type="checkbox" name="is_main_output" value="1" @checked(old('is_main_output')) class="mt-1 h-4 w-4 rounded border-line text-primary focus:ring-primary">
            <span>
                <span class="flex items-center gap-1.5 text-sm font-semibold text-ink">
                    <span class="material-symbols-outlined text-[18px] text-amber-600">star</span>
                    Jadikan hasil utama
                </span>
                <span class="mt-0.5 block text-xs text-muted">Hasil utama adalah bukti inti program. Mencentang ini akan mengganti hasil utama sebelumnya (jika ada).</span>
            </span>
        </label>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
            <p class="text-xs text-muted">Setelah dikirim, status menjadi “Menunggu mentor” sampai disahkan.</p>
            <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                <span class="material-symbols-outlined text-[18px]">cloud_upload</span>
                Unggah bukti
            </button>
        </div>
    </form>

    @if($currentOutputs->isNotEmpty())
        <section class="mt-8">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold">Hasil program ini</h2>
                    <p class="mt-0.5 text-sm text-muted">{{ $currentOutputs->count() }} unggahan pada siklus berjalan.</p>
                </div>
            </div>
            <ul class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-white">
                @foreach($currentOutputs as $output)
                    <li class="px-5 py-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="flex items-center gap-2 font-medium">
                                    @if($output->is_main_output)
                                        <span class="material-symbols-outlined shrink-0 text-[18px] text-amber-500" title="Hasil utama">star</span>
                                    @endif
                                    <span class="truncate">{{ $output->title }}</span>
                                </h3>
                                <p class="mt-1 text-xs text-muted">
                                    {{ \App\Support\Status::outputTypeLabel($output->type) }}
                                    · diunggah {{ $output->created_at?->format('d M Y') }}
                                    @if($output->is_main_output)
                                        · <span class="font-semibold text-amber-600">Hasil utama</span>
                                    @endif
                                </p>
                            </div>
                            <x-badge :status="$output->status" :label="\App\Support\Status::outputLabel($output->status)" />
                        </div>
                        @if($output->description)
                            <p class="mt-2 text-sm text-muted">{{ \Illuminate\Support\Str::limit($output->description, 160) }}</p>
                        @endif
                        <div class="mt-2 flex flex-wrap gap-3 text-sm font-semibold text-primary-dark">
                            @if($output->linkUrl())
                                <a href="{{ $output->linkUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 hover:underline">
                                    <span class="material-symbols-outlined text-[16px]">link</span> Buka tautan
                                </a>
                            @endif
                            @if($output->file_path)
                                <a href="{{ asset('storage/'.$output->file_path) }}" class="inline-flex items-center gap-1 hover:underline">
                                    <span class="material-symbols-outlined text-[16px]">download</span> Unduh berkas
                                </a>
                            @endif
                        </div>
                        @if($output->mentor_feedback)
                            <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900"><b>Masukan mentor:</b> {{ $output->mentor_feedback }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

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
            <x-empty class="mt-6" title="Belum ada hasil">
                Setelah Anda mengunggah bukti, riwayatnya akan muncul di sini.
            </x-empty>
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
                                    data-type-label="{{ \App\Support\Status::outputTypeLabel($output->type) }}"
                                    data-status="{{ $output->status }}"
                                    data-ts="{{ $output->created_at?->timestamp ?: 0 }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <h4 class="flex min-w-0 items-center gap-2 font-medium">
                                            @if($output->is_main_output)
                                                <span class="material-symbols-outlined shrink-0 text-[18px] text-amber-500" title="Hasil utama">star</span>
                                            @endif
                                            <span class="min-w-0 truncate">{{ $output->title }}</span>
                                        </h4>
                                        <x-badge :status="$output->status" :label="\App\Support\Status::outputLabel($output->status)" />
                                    </div>
                                    <p class="mt-1 text-xs text-muted">
                                        {{ \App\Support\Status::outputTypeLabel($output->type) }} · {{ $cycle->businessUnit?->name }} · diunggah {{ $output->created_at?->format('d M Y') }}
                                        @if($output->is_main_output)
                                            · <span class="font-semibold text-amber-600">Hasil utama</span>
                                        @endif
                                    </p>
                                    <details class="mt-2 text-sm">
                                        <summary class="cursor-pointer font-semibold text-primary-dark">Lihat rincian</summary>
                                        <p class="mt-2">{{ $output->description }}</p>
                                        @if($output->mentor_feedback)
                                            <p class="mt-2 text-primary-dark">Masukan mentor: {{ $output->mentor_feedback }}</p>
                                        @endif
                                        @if($output->linkUrl())
                                            <a href="{{ $output->linkUrl() }}" target="_blank" rel="noopener" class="mt-2 inline-block font-semibold text-primary-dark">Buka tautan</a>
                                        @endif
                                        @if($output->file_path)
                                            <a href="{{ asset('storage/'.$output->file_path) }}" class="mt-2 inline-block font-semibold text-primary-dark">Unduh berkas</a>
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
            option.textContent = item.dataset.typeLabel || item.dataset.type;
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
