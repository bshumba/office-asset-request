@extends('layouts.dashboard')

@section('title', 'Admin Requests')
@section('page-eyebrow', 'Admin Review')
@section('page-title', 'Admin Request Inbox')
@section('page-description', 'Review every request in the system, approve manager-approved requests for issuing, and reject requests that should not proceed.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Global Review</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Admins see the full request pipeline before assets are issued.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    This inbox is intentionally global. Unlike the manager phase, admin review works across the whole organization and decides whether approved requests can move into stock movement.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.issues.index') }}" class="secondary-button">View Issues</a>
                <a href="{{ route('admin.adjustments.index') }}" class="secondary-button">View Stock</a>
            </div>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <x-ui.panel
        title="All requests"
        description="Pending, manager-approved, issued, returned, and rejected records all appear here so admins can oversee the full workflow."
    >
        <div class="space-y-4">
            @foreach ($requests as $requestItem)
                @php
                    $statusClasses = match ($requestItem->status) {
                        \App\Enums\AssetRequestStatusEnum::PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
                        \App\Enums\AssetRequestStatusEnum::MANAGER_APPROVED => 'border-sky-200 bg-sky-50 text-sky-700',
                        \App\Enums\AssetRequestStatusEnum::ADMIN_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        \App\Enums\AssetRequestStatusEnum::ISSUED => 'border-violet-200 bg-violet-50 text-violet-700',
                        \App\Enums\AssetRequestStatusEnum::RETURNED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        \App\Enums\AssetRequestStatusEnum::REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
                        default => 'border-slate-200 bg-slate-50 text-slate-700',
                    };
                @endphp

                <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="text-lg font-extrabold text-slate-950">{{ $requestItem->request_number }}</p>
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $statusClasses }}">
                                    {{ str($requestItem->status->value)->headline() }}
                                </span>
                            </div>

                            <p class="text-sm font-bold text-slate-800">{{ $requestItem->user->name }} requested {{ $requestItem->asset->name }}</p>
                            <div class="flex flex-wrap gap-5 text-sm text-slate-500">
                                <span>Department: {{ $requestItem->department->name }}</span>
                                <span>Qty: {{ $requestItem->quantity_requested }}</span>
                                <span>Priority: {{ str($requestItem->priority->value)->headline() }}</span>
                            </div>
                        </div>

                        <a href="{{ route('admin.requests.show', $requestItem) }}" class="secondary-button">
                            Open Request
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    </x-ui.panel>
@endsection
