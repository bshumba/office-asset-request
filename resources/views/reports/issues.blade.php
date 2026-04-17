@extends('layouts.dashboard')

@section('title', $pageTitle)
@section('page-eyebrow', $pageEyebrow)
@section('page-title', $pageTitle)
@section('page-description', $pageDescription)

@section('content')
    <section class="dashboard-hero space-y-6">
        @include('partials.reports.navigation', ['routePrefix' => $routePrefix, 'scopeLabel' => $scopeLabel])

        @if (session('status'))
            <x-ui.alert>
                {{ session('status') }}
            </x-ui.alert>
        @endif

        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Issue Visibility</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Track issued assets, outstanding quantities, and return progress without leaving the dashboard shell.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review issue records, outstanding quantities, and return progress from one accountability view.
                </p>
            </div>

            <div class="rounded-[28px] border border-white/60 bg-white/75 p-5 shadow-sm backdrop-blur">
                <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-orange-600">Issue Lens</p>
                <p class="mt-3 max-w-xs text-sm leading-6 text-slate-600">
                    Search by asset, request number, or assignee to see where issued inventory is sitting right now.
                </p>
                @can('reports.export')
                    <div class="mt-5">
                        <a href="{{ route($routePrefix.'.issues.export', request()->query()) }}" class="secondary-button w-full justify-center">
                            Export CSV
                        </a>
                    </div>
                @endcan
            </div>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-3">
        @foreach ($summary as $index => $card)
            <x-ui.stat-card
                :label="$card['label']"
                :value="$card['value']"
                :meta="$card['meta']"
                :tone="$index === 0 ? 'brand' : ($index === 2 ? 'slate' : 'emerald')"
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.88fr_1.12fr]">
        <x-ui.panel
            title="Issue filters"
            description="Filter by asset, assignee, date, and issue status without leaving the report."
        >
            <form method="GET" action="{{ route($routePrefix.'.issues') }}" class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="search" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Asset, request number, or assignee"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                </div>

                @if ($showDepartmentFilter)
                    <div class="md:col-span-2">
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
                        @foreach (\App\Enums\AssetIssueStatusEnum::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                                {{ str($status->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="from" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Issued from</label>
                    <input
                        id="from"
                        name="from"
                        type="date"
                        value="{{ $filters['from'] ?? '' }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                </div>

                <div>
                    <label for="to" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Issued to</label>
                    <input
                        id="to"
                        name="to"
                        type="date"
                        value="{{ $filters['to'] ?? '' }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                </div>

                <div class="md:col-span-2 flex flex-wrap gap-3">
                    <button type="submit" class="primary-button">Apply filters</button>
                    <a href="{{ route($routePrefix.'.issues') }}" class="secondary-button">Reset</a>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel
            title="Issue table"
            description="Review issued quantities and open return balances at a glance."
        >
            @if ($issues->isEmpty())
                <x-ui.empty-state
                    title="No issue records matched these filters"
                    description="Reset the filters or widen the date range to review more issue activity."
                    action-label="Reset Filters"
                    action-url="{{ route($routePrefix.'.issues') }}"
                />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Request</th>
                                <th class="px-4 py-3">Asset</th>
                                <th class="px-4 py-3">Issued To</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Qty</th>
                                <th class="px-4 py-3">Issued At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($issues as $issue)
                                <tr class="bg-white">
                                    <td class="px-4 py-4">
                                        <p class="font-extrabold text-slate-950">{{ $issue->assetRequest?->request_number ?? 'No request' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $issue->department?->name ?? 'Unassigned' }}</p>
                                    </td>
                                    <td class="px-4 py-4">{{ $issue->asset?->name ?? 'Missing asset' }}</td>
                                    <td class="px-4 py-4">{{ $issue->issuedToUser?->name ?? 'Unknown user' }}</td>
                                    <td class="px-4 py-4">{{ str($issue->status->value)->headline() }}</td>
                                    <td class="px-4 py-4 font-bold">{{ $issue->outstandingQuantity() }} / {{ $issue->quantity_issued }}</td>
                                    <td class="px-4 py-4">{{ $issue->issued_at?->format('d M Y') ?? 'Not set' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $issues->links() }}
                </div>
            @endif
        </x-ui.panel>
    </section>
@endsection
