@props(['status' => 'draft'])
<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide '.\App\Support\Status::badge($status)]) }}>{{ \App\Support\Status::label($status) }}</span>
