@props([
    'label',
    'meta' => null,
    'tone' => 'brand',
    'value',
])

@php
    $accentClasses = match ($tone) {
        'slate' => 'bg-slate-900 text-white',
        'emerald' => 'bg-emerald-500 text-white',
        default => 'bg-gradient-to-br from-orange-500 to-amber-300 text-slate-950',
    };
@endphp

<article class="shell-card">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
            <p class="mt-4 text-4xl font-extrabold tracking-tight text-slate-950">{{ $value }}</p>
            @if ($meta)
                <p class="mt-3 text-sm leading-6 text-slate-500">{{ $meta }}</p>
            @endif
        </div>

        <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $accentClasses }}">
            <span class="text-lg font-black">+</span>
        </div>
    </div>
</article>
