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
                <span class="shell-chip">Request Visibility</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Follow request demand from search results to approval state without digging through multiple screens.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review request demand, priority, and approval status from one report.
                </p>
            </div>

            <div class="rounded-[28px] border border-white/60 bg-white/75 p-5 shadow-sm backdrop-blur">
                <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-orange-600">Useful Filters</p>
                <p class="mt-3 max-w-xs text-sm leading-6 text-slate-600">
                    Combine search, dates, priorities, and statuses to narrow the view.
                </p>
                @can('reports.export')
                    <div class="mt-5">
                        <a href="{{ route($routePrefix.'.requests.export', request()->query()) }}" class="secondary-button w-full justify-center">
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
                :tone="$index === 2 ? 'emerald' : ($index === 1 ? 'slate' : 'brand')"
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <x-ui.panel
            title="Request filters"
            description="Filter requests by date, status, priority, and search terms."
        >
            <form method="GET" action="{{ route($routePrefix.'.requests') }}" class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="search" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Request number, requester, asset, or reason"
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
                        @foreach (\App\Enums\AssetRequestStatusEnum::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                                {{ str($status->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Priority</label>
                    <select
                        id="priority"
                        name="priority"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                        <option value="">All priorities</option>
                        @foreach (\App\Enums\RequestPriorityEnum::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? null) === $priority->value)>
                                {{ str($priority->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="from" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">From</label>
                    <input
                        id="from"
                        name="from"
                        type="date"
                        value="{{ $filters['from'] ?? '' }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                </div>

                <div>
                    <label for="to" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">To</label>
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
                    <a href="{{ route($routePrefix.'.requests') }}" class="secondary-button">Reset</a>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel
            title="Request table"
            description="Review requester, asset, status, and quantity at a glance."
        >
            @if ($requests->isEmpty())
                <x-ui.empty-state
                    title="No requests matched these filters"
                    description="Reset the filters or expand the date range to review more request activity."
                    action-label="Reset Filters"
                    action-url="{{ route($routePrefix.'.requests') }}"
                />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Request</th>
                                <th class="px-4 py-3">Requester</th>
                                <th class="px-4 py-3">Asset</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Priority</th>
                                <th class="px-4 py-3">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($requests as $assetRequest)
                                <tr class="bg-white">
                                    <td class="px-4 py-4">
                                        <p class="font-extrabold text-slate-950">{{ $assetRequest->request_number }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $assetRequest->created_at?->format('d M Y') }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-bold">{{ $assetRequest->user?->name ?? 'Unknown user' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $assetRequest->department?->name ?? 'Unassigned' }}</p>
                                    </td>
                                    <td class="px-4 py-4">{{ $assetRequest->asset?->name ?? 'Missing asset' }}</td>
                                    <td class="px-4 py-4">{{ str($assetRequest->status->value)->headline() }}</td>
                                    <td class="px-4 py-4">{{ str($assetRequest->priority->value)->headline() }}</td>
                                    <td class="px-4 py-4 font-bold">{{ $assetRequest->quantity_approved ?? $assetRequest->quantity_requested }} / {{ $assetRequest->quantity_requested }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $requests->links() }}
                </div>
            @endif
        </x-ui.panel>
    </section>
@endsection
