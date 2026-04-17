@extends('layouts.dashboard')

@section('title', 'Department Requests')
@section('page-eyebrow', 'Manager Reviews')
@section('page-title', 'Department Request Inbox')
@section('page-description', 'Review requests from your department, approve valid ones, and reject requests that should not continue to the admin stage.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Review Queue</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Managers only see requests from their own department here.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    This is the first place where department-scoped authorization becomes very visible. The inbox should show only relevant requests and block everything else.
                </p>
            </div>

            <a href="{{ route('manager.dashboard') }}" class="secondary-button">
                Back To Dashboard
            </a>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <x-ui.panel
        title="Department requests"
        description="Pending requests are the main focus, but the manager can also see previously reviewed requests from the same department."
    >
        @if ($requests->isEmpty())
            <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                <p class="text-lg font-extrabold text-slate-900">No department requests yet</p>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Once staff submit requests, they will appear here for department review.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($requests as $requestItem)
                    @php
                        $statusClasses = match ($requestItem->status) {
                            \App\Enums\AssetRequestStatusEnum::PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
                            \App\Enums\AssetRequestStatusEnum::MANAGER_APPROVED => 'border-sky-200 bg-sky-50 text-sky-700',
                            \App\Enums\AssetRequestStatusEnum::REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
                            \App\Enums\AssetRequestStatusEnum::ADMIN_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
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
                                    <span>Qty: {{ $requestItem->quantity_requested }}</span>
                                    <span>Priority: {{ str($requestItem->priority->value)->headline() }}</span>
                                    <span>Needed: {{ $requestItem->needed_by_date?->format('d M Y') ?? 'Not specified' }}</span>
                                    <span>Submitted: {{ $requestItem->created_at->format('d M Y') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('manager.requests.show', $requestItem) }}" class="secondary-button">
                                Review Request
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        @endif
    </x-ui.panel>
@endsection
