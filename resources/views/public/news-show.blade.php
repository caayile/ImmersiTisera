@extends('layouts.public')
@section('title', $news->title)
@section('content')
@php
    $coverImage = \App\Models\HeroSetting::resolveMediaUrl($news->cover_image)
        ?: asset('images/hero/campus.jpg');
    $shareUrl = urlencode(route('news.show', $news));
    $shareTitle = urlencode($news->title);
@endphp

<section class="relative isolate overflow-hidden bg-[#15201d] py-8 sm:py-10">
    <img src="{{ $coverImage }}" alt="" aria-hidden="true" class="absolute inset-0 -z-10 h-full w-full scale-110 object-cover opacity-35 blur-sm">
    <div class="absolute inset-0 -z-10 bg-[#101915]/75"></div>

    <article class="mx-auto w-full max-w-5xl overflow-hidden rounded-lg bg-[#fbfbfa] shadow-2xl" data-reveal>
        <header class="px-6 pb-5 pt-6 sm:px-8 sm:pt-8">
            <span class="inline-flex rounded-full border border-[#dbe6d7] bg-[#eef3e9] px-3 py-1.5 text-[10px] font-medium text-[#334b36]">{{ $news->category }}</span>
            <p class="mt-4 flex items-center gap-2 text-[13px] text-[#66735f]">
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">calendar_today</span>
                Dipublikasikan pada {{ $news->published_at?->translatedFormat('d F Y') }}
            </p>
            <h1 class="mt-4 font-display text-[27px] font-bold leading-[1.08] text-[#18233a] sm:text-[29px]">{{ $news->title }}</h1>
            <div class="mt-4 h-0.5 w-14 bg-[#a7c99d]" aria-hidden="true"></div>
        </header>

        <figure class="mx-6 overflow-hidden rounded-lg shadow-md sm:mx-8">
            <img src="{{ $coverImage }}" alt="{{ $news->title }}" class="aspect-[1.48] w-full object-cover">
        </figure>

        <div class="whitespace-pre-line px-6 pb-6 pt-6 text-base leading-relaxed text-[#37433e] sm:px-8">{{ $news->body }}</div>

        <footer class="mx-6 flex flex-col gap-5 border-t border-[#e4e9e4] px-0 py-6 sm:mx-8 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('news.index') }}" class="tap-feedback inline-flex w-fit items-center gap-2 rounded-md bg-white px-3 py-2 text-[13px] font-medium text-[#52645a] shadow-sm ring-1 ring-[#e9ede8] hover:text-[#243b2f]">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                Kembali ke Daftar Berita
            </a>
            <div class="flex flex-col gap-2 sm:items-end">
                <span class="text-[13px] text-[#778274]">Bagikan artikel ini:</span>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" id="copy-article-link" aria-label="Salin tautan artikel" title="Salin tautan artikel" class="tap-feedback flex size-10 items-center justify-center rounded-full bg-white text-[#52645a] shadow-sm ring-1 ring-[#e9ede8] hover:text-[#243b2f]">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">content_copy</span>
                    </button>
                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke WhatsApp" title="WhatsApp" class="tap-feedback flex size-10 items-center justify-center rounded-full bg-[#20bd65] text-white">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.1 11.9a8.1 8.1 0 0 1-12 7.1L4 20l1-4a8.1 8.1 0 1 1 15.1-4.1Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.5c.2-.5.5-.5.8-.5h.5c.2 0 .4.1.5.4l.7 1.7c.1.2 0 .4-.1.6l-.5.6c-.2.2-.2.4-.1.6.5.9 1.2 1.6 2.1 2.1.2.1.4.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.6.8c.2.1.3.3.3.5 0 .4-.2 1-.6 1.3-.4.4-1 .6-1.6.5-1-.1-2.2-.6-3.6-1.8-1.1-1-2-2.3-2.3-3.3-.3-.9-.1-1.7.4-2.5Z" />
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke Facebook" title="Facebook" class="tap-feedback flex size-10 items-center justify-center rounded-full bg-[#1877f2] text-white">
                        <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true">
                            <path d="M13.5 21v-8.2h2.8l.4-3.2h-3.2V7.6c0-.9.3-1.6 1.6-1.6h1.7V3.1c-.3 0-1.3-.1-2.5-.1-2.6 0-4.3 1.6-4.3 4.5v2.1H7v3.2h3V21h3.5Z" />
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Buka Instagram" title="Instagram" class="tap-feedback flex size-10 items-center justify-center rounded-full bg-[#c13584] text-white">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5" />
                            <circle cx="12" cy="12" r="4" />
                            <circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none" />
                        </svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke X" title="X" class="tap-feedback flex size-10 items-center justify-center rounded-full bg-[#111827] text-white">
                        <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true">
                            <path d="M18.9 2h3.1l-6.8 7.8L23.2 22H17l-4.9-7.4L5.5 22H2.4l7.2-8.3L1.8 2h6.4l4.4 6.8L18.9 2Zm-1.1 18h1.7L7.3 3.9H5.4L17.8 20Z" />
                        </svg>
                    </a>
                </div>
                <span id="article-share-status" class="text-[13px] text-[#52645a]" role="status" aria-live="polite"></span>
            </div>
        </footer>
    </article>
</section>

<script>
(() => {
    const shareUrl = @json(route('news.show', $news));
    const status = document.getElementById('article-share-status');

    async function copyShareUrl(message = 'Tautan berhasil disalin.') {
        try {
            await navigator.clipboard.writeText(shareUrl);
        } catch {
            const input = document.createElement('textarea');
            input.value = shareUrl;
            input.setAttribute('readonly', '');
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.append(input);
            input.select();
            const copied = document.execCommand('copy');
            input.remove();

            if (!copied) {
                status.textContent = 'Salin tautan dari bilah alamat browser.';
                return;
            }
        }

        status.textContent = message;
    }

    document.getElementById('copy-article-link')?.addEventListener('click', () => copyShareUrl());
})();
</script>
@endsection
