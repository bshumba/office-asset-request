@props([
    'title',
    'description',
    'actionLabel' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center']) }}>
    <p class="text-lg font-extrabold text-slate-900">{{ $title }}</p>
    <p class="mt-3 text-sm leading-6 text-slate-500">
        {{ $description }}
    </p>

    @if ($actionLabel && $actionUrl)
        <div class="mt-5">
            <a href="{{ $actionUrl }}" class="secondary-button">
                {{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
