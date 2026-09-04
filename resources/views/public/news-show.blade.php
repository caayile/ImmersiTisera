@extends('layouts.public')
@section('title', $news->title)
@section('content')
<article class="bg-bg py-12">
    <div class="mx-auto max-w-3xl px-5">
        <a href="{{ route('news.index') }}" class="text-sm font-medium text-primary-dark">← Kembali ke berita</a>
        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">{{ $news->category }} · {{ $news->published_at?->translatedFormat('d M Y') }}</p>
        <h1 class="mt-3 text-3xl font-semibold leading-tight md:text-4xl">{{ $news->title }}</h1>
        @if($news->excerpt)
            <p class="mt-4 text-lg text-muted">{{ $news->excerpt }}</p>
        @endif
        <div class="prose mt-8 max-w-none whitespace-pre-line rounded-3xl border border-line bg-white p-6 text-sm leading-7 md:p-8">{{ $news->body }}</div>
    </div>
</article>
@endsection
