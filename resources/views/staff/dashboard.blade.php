@extends('layouts.dashboard')

@section('title', 'Staff Dashboard')
@section('page-eyebrow', 'Personal Workspace')
@section('page-title', 'Staff Dashboard')
@section('page-description', 'A personal view focused on creating requests, tracking approvals, and understanding what is currently assigned to this account.')

@section('content')
    @include('partials.dashboard.date-filters', ['routeName' => 'staff.dashboard'])

    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">My Workspace</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Manage your requests and assigned assets in {{ $departmentName }}.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Submit new requests, follow approval progress, and track the assets currently issued to your account.
                </p>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                    Showing metrics for {{ $rangeLabel }}
                </p>
            </div>

            <div class="rounded-[28px] border border-white/60 bg-white/75 p-5 shadow-sm backdrop-blur">
                <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-orange-600">Notifications</p>
                <p class="mt-3 max-w-xs text-sm leading-6 text-slate-600">
                    Use the notification center to keep up with approvals, rejections, issues, and return updates.
                </p>
            </div>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-4 md:grid-cols-2">
        @foreach ($stats as $index => $stat)
            <x-ui.stat-card
                :label="$stat['label']"
                :value="$stat['value']"
                :meta="$stat['meta']"
                :tone="$index === 2 ? 'emerald' : ($index === 3 ? 'slate' : 'brand')"
            />
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <x-ui.panel
            title="Personal request breakdown"
            description="Track how your requests are moving through the workflow."
        >
            <div class="space-y-4">
                @foreach ($requestBreakdown as $item)
                    <div class="flex items-center justify-between rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4">
                        <div>
                            <p class="text-sm font-bold text-slate-700">{{ $item['label'] }}</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">My requests</p>
                        </div>
                        <span class="text-2xl font-extrabold tracking-tight text-slate-950">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Workspace highlights"
            description="Use this page to monitor personal activity and move quickly to the next task."
        >
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Available now</p>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <li>Create requests with validated stock-aware rules</li>
                        <li>Track approval states from pending through issued</li>
                        <li>See personal issued-item counts and assigned asset records from the same workspace</li>
                    </ul>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Alerts</p>
                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        Open notifications to jump straight into request and issue updates tied to your account.
                    </p>
                </div>
            </div>
        </x-ui.panel>
    </section>
@endsection
