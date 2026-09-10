@props(['application'])

<ol class="grid gap-3 sm:grid-cols-5">
    @foreach($application->approvalSteps() as $index => $step)
        <li class="relative rounded-2xl border px-3 py-3 {{ $step['current'] ? 'border-primary bg-primary/10' : ($step['done'] ? 'border-emerald-200 bg-emerald-50' : 'border-line bg-white') }}">
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">{{ $index + 1 }}. {{ $step['actor'] }}</p>
            <p class="mt-1 text-sm font-semibold {{ $step['current'] ? 'text-primary-dark' : 'text-ink' }}">{{ $step['label'] }}</p>
        </li>
    @endforeach
</ol>
