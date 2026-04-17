@props([
    'tone' => 'success',
    'title' => null,
])

@php
    $styles = match ($tone) {
        'error' => 'border-rose-200 bg-rose-50 text-rose-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
        'info' => 'border-sky-200 bg-sky-50 text-sky-700',
        default => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-[24px] border px-5 py-4 text-sm '.$styles]) }}>
    @if ($title)
        <p class="font-extrabold">{{ $title }}</p>
    @endif

    <div @class(['leading-6', 'mt-1' => $title])>
        {{ $slot }}
    </div>
</div>
