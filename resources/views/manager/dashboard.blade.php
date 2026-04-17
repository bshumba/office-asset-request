@extends('layouts.dashboard')

@section('title', 'Manager Dashboard')
@section('page-eyebrow', 'Department Oversight')
@section('page-title', 'Manager Dashboard')
@section('page-description', 'Monitor department requests, stock pressure, and approval activity from one workspace.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Department Overview</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Review and manage {{ $departmentName }} requests from one place.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Keep approvals moving, monitor current demand, and stay aware of stock pressure across your department.
                </p>
            </div>

            <div class="rounded-[28px] border border-white/60 bg-white/75 p-5 shadow-sm backdrop-blur">
                <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-orange-600">Reporting</p>
                <p class="mt-3 max-w-xs text-sm leading-6 text-slate-600">
                    Open department reports to review stock availability, request demand, and low-stock risk.
                </p>
                <div class="mt-5">
                    <a href="{{ route('manager.reports.stock') }}" class="primary-button w-full">
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
                :tone="$index === 0 ? 'slate' : ($index === 3 ? 'emerald' : 'brand')"
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <x-ui.panel
            title="Approval flow snapshot"
            description="Track pending, approved, rejected, and issued activity across your department."
        >
            <div class="space-y-4">
                @foreach ($workflow as $item)
                    <div class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4">
                        <div>
                            <p class="text-sm font-bold text-slate-700">{{ $item['label'] }}</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Department status</p>
                        </div>
                        <span class="text-2xl font-extrabold tracking-tight text-slate-950">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Manager workspace"
            description="Use this area for review actions and day-to-day department monitoring."
        >
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Approval tools</p>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <li>Department request inbox with detail pages</li>
                        <li>Manager approve flow with quantity validation</li>
                        <li>Manager reject flow with required rejection reasons</li>
                    </ul>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Reports</p>
                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        Department stock, request, issue, and low-stock reports are available from this dashboard.
                    </p>
                </div>
            </div>
        </x-ui.panel>
    </section>
@endsection
