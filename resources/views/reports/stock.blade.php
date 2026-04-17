@extends('layouts.dashboard')

@section('title', $pageTitle)
@section('page-eyebrow', $pageEyebrow)
@section('page-title', $pageTitle)
@section('page-description', $pageDescription)

@section('content')
    <section class="dashboard-hero space-y-6">
        @include('partials.reports.navigation', ['routePrefix' => $routePrefix, 'scopeLabel' => $scopeLabel])

        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Inventory Visibility</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Search assets, inspect stock levels, and spot reorder pressure before it becomes a workflow problem.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review what is in stock, what is available, and which assets are nearing reorder pressure.
                </p>
            </div>

            <div class="rounded-[28px] border border-white/60 bg-white/75 p-5 shadow-sm backdrop-blur">
                <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-orange-600">Report Scope</p>
                <p class="mt-3 max-w-xs text-sm leading-6 text-slate-600">
                    {{ $showDepartmentFilter ? 'Use filters to move across departments and status states.' : 'This report is already locked to your department scope.' }}
                </p>
            </div>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-3">
        @foreach ($summary as $index => $card)
            <x-ui.stat-card
                :label="$card['label']"
                :value="$card['value']"
                :meta="$card['meta']"
                :tone="$index === 1 ? 'emerald' : ($index === 2 ? 'slate' : 'brand')"
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <x-ui.panel
            title="Filters and search"
            description="Use filters to narrow the stock view without leaving the report."
        >
            <form method="GET" action="{{ route($routePrefix.'.stock') }}" class="space-y-4">
                <div>
                    <label for="search" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Asset name, code, brand, or category"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                </div>

                @if ($showDepartmentFilter)
                    <div>
                        <label for="department_id" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Department</label>
                        <select
                            id="department_id"
                            name="department_id"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                        >
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label for="status" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Status</label>
                    <select
                        id="status"
                        name="status"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                        <option value="">All statuses</option>
                        @foreach (\App\Enums\AssetStatusEnum::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                                {{ str($status->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="primary-button">Apply filters</button>
                    <a href="{{ route($routePrefix.'.stock') }}" class="secondary-button">Reset</a>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel
            title="Stock table"
            description="Review current stock levels across the selected scope."
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Asset</th>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Available</th>
                            <th class="px-4 py-3">Reorder</th>
                            <th class="px-4 py-3">Category</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($assets as $asset)
                            <tr class="bg-white">
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-slate-950">{{ $asset->name }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $asset->asset_code }}</p>
                                </td>
                                <td class="px-4 py-4">{{ $asset->department?->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-4">{{ str($asset->status->value)->headline() }}</td>
                                <td class="px-4 py-4 font-bold">{{ $asset->quantity_available }} / {{ $asset->quantity_total }}</td>
                                <td class="px-4 py-4">{{ $asset->reorder_level }}</td>
                                <td class="px-4 py-4">{{ $asset->category?->name ?? 'Uncategorized' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                    No assets matched the current stock filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $assets->links() }}
            </div>
        </x-ui.panel>
    </section>
@endsection
