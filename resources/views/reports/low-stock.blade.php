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
                <span class="shell-chip">Reorder Watch</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Low-stock assets deserve their own screen because they directly affect request approvals and operational confidence.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review items at or below reorder level before they begin to block fulfilment.
                </p>
            </div>

            <div class="rounded-[28px] border border-white/60 bg-white/75 p-5 shadow-sm backdrop-blur">
                <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-orange-600">Alert Focus</p>
                <p class="mt-3 max-w-xs text-sm leading-6 text-slate-600">
                    Only assets already at or below their reorder level are shown here.
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
                :tone="$index === 2 ? 'slate' : ($index === 1 ? 'emerald' : 'brand')"
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.82fr_1.18fr]">
        <x-ui.panel
            title="Low-stock filters"
            description="Use filters to focus the low-stock view on the assets that matter right now."
        >
            <form method="GET" action="{{ route($routePrefix.'.low-stock') }}" class="space-y-4">
                <div>
                    <label for="search" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Asset name, code, or category"
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

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="primary-button">Apply filters</button>
                    <a href="{{ route($routePrefix.'.low-stock') }}" class="secondary-button">Reset</a>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel
            title="Low-stock table"
            description="Review the assets that currently need replenishment attention."
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Asset</th>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Available</th>
                            <th class="px-4 py-3">Reorder</th>
                            <th class="px-4 py-3">Status</th>
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
                                <td class="px-4 py-4 font-extrabold text-orange-600">{{ $asset->quantity_available }}</td>
                                <td class="px-4 py-4">{{ $asset->reorder_level }}</td>
                                <td class="px-4 py-4">{{ str($asset->status->value)->headline() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                    No assets matched the current low-stock filters.
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
