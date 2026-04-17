@props([
    'actionHref' => null,
    'actionLabel' => null,
    'description' => null,
    'title',
])

<section class="shell-card">
    <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
            <h2 class="text-xl font-extrabold tracking-tight text-slate-950">{{ $title }}</h2>
            @if ($description)
                <p class="max-w-2xl text-sm leading-6 text-slate-500">{{ $description }}</p>
            @endif
        </div>

        @if ($actionLabel && $actionHref)
            <a href="{{ $actionHref }}" class="secondary-button">
                {{ $actionLabel }}
            </a>
        @endif
    </div>

    <div class="pt-6">
        {{ $slot }}
    </div>
</section>
