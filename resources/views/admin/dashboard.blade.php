@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')
@section('page-eyebrow', 'Admin Workspace')
@section('page-title', 'Super Admin Dashboard')
@section('page-description', 'Monitor the full asset workflow, access control, and reporting from one workspace.')

@section('content')
    @php
        $pipelineMax = max(1, collect($requestPipeline)->max('value'));
        $departmentMax = max(
            1,
            collect($departmentActivity)->max(function (array $item): int {
                return max($item['requests'], $item['issues'], $item['users']);
            }) ?? 1,
        );
    @endphp

    @include('partials.dashboard.date-filters', ['routeName' => 'admin.dashboard'])

    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Operations Overview</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Monitor the asset request lifecycle across the full organization.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review requests, stock movement, issued assets, team setup, permissions, and reporting from a single admin view.
                </p>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                    Showing metrics for {{ $rangeLabel }}
                </p>
            </div>

            <div class="rounded-[28px] border border-white/60 bg-white/75 p-5 shadow-sm backdrop-blur">
                <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-orange-600">Operations</p>
                <p class="mt-3 max-w-xs text-sm leading-6 text-slate-600">
                    Move from approval into issuing, returns, stock adjustments, and reporting from this workspace.
                </p>
                <div class="mt-5">
                    <a href="{{ route('admin.reports.stock') }}" class="primary-button w-full">
                        Open Reports
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-4 md:grid-cols-2">
        @foreach ($stats as $index => $stat)
            <x-ui.stat-card
                :label="$stat['label']"
                :value="$stat['value']"
                :meta="$stat['meta']"
                :tone="$index === 1 ? 'slate' : ($index === 2 ? 'emerald' : 'brand')"
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <x-ui.panel
            title="Core admin areas"
            description="Move quickly between team setup, access control, and operational review."
        >
            <div class="grid gap-4 md:grid-cols-3">
                <a href="{{ route('admin.users.index') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200 hover:bg-orange-50/50">
                    <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-orange-500">Team</p>
                    <p class="mt-3 text-lg font-extrabold text-slate-950">User Management</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Create staff and manager accounts, assign departments, and review account status.</p>
                </a>
                <a href="{{ route('admin.assets.index') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200 hover:bg-orange-50/50">
                    <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-orange-500">Inventory</p>
                    <p class="mt-3 text-lg font-extrabold text-slate-950">Assets & Categories</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Maintain departments, categories, and asset records from the same admin workspace.</p>
                </a>
                <a href="{{ route('admin.roles.index') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200 hover:bg-orange-50/50">
                    <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-orange-500">Access</p>
                    <p class="mt-3 text-lg font-extrabold text-slate-950">Roles & Permissions</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Manage roles, create permissions, and keep authorization aligned with the workflow.</p>
                </a>
                <a href="{{ route('admin.requests.index') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200 hover:bg-orange-50/50">
                    <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-orange-500">Workflow</p>
                    <p class="mt-3 text-lg font-extrabold text-slate-950">Request Operations</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Review approved requests, issue assets, and track the next operational step.</p>
                </a>
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="System health snapshot"
            description="A quick summary of current activity across requests, issues, and inventory."
        >
            <div class="space-y-4">
                @foreach ($health as $item)
                    <div class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4">
                        <span class="text-sm font-bold text-slate-600">{{ $item['label'] }}</span>
                        <span class="text-2xl font-extrabold tracking-tight text-slate-950">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <x-ui.panel
            title="Request pipeline graph"
            description="Compare request volume across each workflow stage."
        >
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
                @foreach ($requestPipeline as $item)
                    <div class="flex flex-col items-center gap-3 rounded-[28px] border border-slate-200 bg-slate-50 p-4">
                        <div class="flex h-48 w-full items-end justify-center rounded-[22px] bg-white px-3 py-4">
                            <div
                                class="w-full rounded-t-[18px] {{ $item['tone'] }}"
                                style="height: {{ $item['value'] > 0 ? max(12, (int) round(($item['value'] / $pipelineMax) * 100)) : 0 }}%;"
                            ></div>
                        </div>
                        <p class="text-center text-sm font-bold text-slate-700">{{ $item['label'] }}</p>
                        <p class="text-2xl font-extrabold tracking-tight text-slate-950">{{ $item['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Department activity graph"
            description="This side-by-side graph compares request volume, issue volume, and team size across active departments."
        >
            <div class="space-y-5">
                @forelse ($departmentActivity as $item)
                    <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-base font-extrabold text-slate-950">{{ $item['label'] }}</p>
                                <div class="flex flex-wrap gap-3 text-xs font-extrabold uppercase tracking-[0.16em] text-slate-400">
                                    <span>Requests: {{ $item['requests'] }}</span>
                                    <span>Issues: {{ $item['issues'] }}</span>
                                    <span>Users: {{ $item['users'] }}</span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-xs font-bold text-slate-500">
                                        <span>Requests</span>
                                        <span>{{ $item['requests'] }}</span>
                                    </div>
                                    <div class="h-3 rounded-full bg-white">
                                        <div class="h-3 rounded-full bg-orange-400" style="width: {{ $item['requests'] > 0 ? max(8, (int) round(($item['requests'] / $departmentMax) * 100)) : 0 }}%;"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-xs font-bold text-slate-500">
                                        <span>Issues</span>
                                        <span>{{ $item['issues'] }}</span>
                                    </div>
                                    <div class="h-3 rounded-full bg-white">
                                        <div class="h-3 rounded-full bg-sky-400" style="width: {{ $item['issues'] > 0 ? max(8, (int) round(($item['issues'] / $departmentMax) * 100)) : 0 }}%;"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-xs font-bold text-slate-500">
                                        <span>Users</span>
                                        <span>{{ $item['users'] }}</span>
                                    </div>
                                    <div class="h-3 rounded-full bg-white">
                                        <div class="h-3 rounded-full bg-slate-900" style="width: {{ $item['users'] > 0 ? max(8, (int) round(($item['users'] / $departmentMax) * 100)) : 0 }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                        <p class="text-lg font-extrabold text-slate-900">No department activity yet</p>
                        <p class="mt-3 text-sm leading-6 text-slate-500">
                            Department comparisons will appear here as request and issue activity grows.
                        </p>
                    </div>
                @endforelse
            </div>
        </x-ui.panel>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <x-ui.panel
            title="Current admin responsibilities"
            description="The admin shell now spans both operations and reporting."
        >
            <div class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Request review</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Approve or reject manager-approved requests before issuing.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Issue and return</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Track assets assigned to users and record their return state.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Stock movement</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Apply inventory adjustments when stock is restocked, damaged, or corrected.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Reporting</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Review stock, request, issue, and low-stock reports with department-aware filtering.</p>
                </div>
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Quick links"
            description="The admin shell now links into the real operational screens."
        >
            <div class="grid gap-3">
                <a href="{{ route('admin.users.index') }}" class="secondary-button justify-start">Team Management</a>
                <a href="{{ route('admin.departments.index') }}" class="secondary-button justify-start">Departments</a>
                <a href="{{ route('admin.assets.index') }}" class="secondary-button justify-start">Asset Catalog</a>
                <a href="{{ route('admin.roles.index') }}" class="secondary-button justify-start">Access Control</a>
                <a href="{{ route('admin.requests.index') }}" class="secondary-button justify-start">Request Inbox</a>
                <a href="{{ route('admin.issues.index') }}" class="secondary-button justify-start">Issued Assets</a>
                <a href="{{ route('admin.adjustments.index') }}" class="secondary-button justify-start">Stock Adjustments</a>
                <a href="{{ route('admin.reports.stock') }}" class="secondary-button justify-start">Stock Report</a>
            </div>
        </x-ui.panel>
    </section>
@endsection
