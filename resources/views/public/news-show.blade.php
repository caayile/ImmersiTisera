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

    <article class="mx-auto w-full max-w-[504px] overflow-hidden rounded-lg bg-[#fbfbfa] shadow-2xl" data-reveal>
        <header class="px-6 pb-5 pt-6 sm:px-8 sm:pt-8">
            <span class="inline-flex rounded-full border border-[#dbe6d7] bg-[#eef3e9] px-3 py-1.5 text-[10px] font-medium text-[#334b36]">{{ $news->category }}</span>
            <p class="mt-4 flex items-center gap-2 text-[10px] text-[#66735f]">
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">calendar_today</span>
                Dipublikasikan pada {{ $news->published_at?->translatedFormat('d F Y') }}
            </p>
            <h1 class="mt-4 font-display text-[27px] font-bold leading-[1.08] text-[#18233a] sm:text-[29px]">{{ $news->title }}</h1>
            <div class="mt-4 h-0.5 w-14 bg-[#a7c99d]" aria-hidden="true"></div>
        </header>

        <figure class="mx-6 overflow-hidden rounded-lg shadow-md sm:mx-8">
            <img src="{{ $coverImage }}" alt="{{ $news->title }}" class="aspect-[1.48] w-full object-cover">
        </figure>

        <div class="whitespace-pre-line px-6 pb-6 pt-6 text-[13px] leading-[1.65] text-[#37433e] sm:px-8">{{ $news->body }}</div>

        <footer class="mx-6 flex flex-col gap-5 border-t border-[#e4e9e4] px-0 py-6 sm:mx-8 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('news.index') }}" class="tap-feedback inline-flex w-fit items-center gap-2 rounded-md bg-white px-3 py-2 text-[11px] font-medium text-[#52645a] shadow-sm ring-1 ring-[#e9ede8] hover:text-[#243b2f]">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                Kembali ke Daftar Berita
            </a>
            <div class="flex flex-col gap-2 sm:items-end">
                <span class="text-[10px] text-[#778274]">Bagikan artikel ini:</span>
                <div class="flex items-center gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke Facebook" title="Facebook" class="tap-feedback flex size-7 items-center justify-center rounded-full bg-[#1877f2] text-xs font-bold text-white">f</a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke X" title="X" class="tap-feedback flex size-7 items-center justify-center rounded-full bg-[#111827] text-[11px] font-semibold text-white">X</a>
                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke WhatsApp" title="WhatsApp" class="tap-feedback flex size-7 items-center justify-center rounded-full bg-[#20bd65] text-[9px] font-bold text-white">WA</a>
                </div>
            </div>
        </footer>
    </article>
</section>
@endsection
