@php
    $tabs = [
        ['label' => 'Stock', 'route' => $routePrefix.'.stock'],
        ['label' => 'Requests', 'route' => $routePrefix.'.requests'],
        ['label' => 'Issues', 'route' => $routePrefix.'.issues'],
        ['label' => 'Low Stock', 'route' => $routePrefix.'.low-stock'],
    ];
@endphp

<div class="flex flex-wrap items-center gap-3">
    @foreach ($tabs as $tab)
        <a
            href="{{ route($tab['route']) }}"
            class="{{ request()->routeIs($tab['route']) ? 'primary-button' : 'secondary-button' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach

    <span class="rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] text-orange-600">
        {{ $scopeLabel }}
    </span>
</div>
