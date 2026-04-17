@extends('layouts.dashboard')

@section('title', 'Request Details')
@section('page-eyebrow', 'Staff Requests')
@section('page-title', 'Request Details')
@section('page-description', 'See the exact request information, current status, and status history entries written by the workflow.')

@section('content')
    @php
        $statusClasses = match ($assetRequest->status) {
            \App\Enums\AssetRequestStatusEnum::PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
            \App\Enums\AssetRequestStatusEnum::CANCELLED => 'border-rose-200 bg-rose-50 text-rose-700',
            \App\Enums\AssetRequestStatusEnum::MANAGER_APPROVED => 'border-sky-200 bg-sky-50 text-sky-700',
            \App\Enums\AssetRequestStatusEnum::ADMIN_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            \App\Enums\AssetRequestStatusEnum::ISSUED => 'border-violet-200 bg-violet-50 text-violet-700',
            \App\Enums\AssetRequestStatusEnum::RETURNED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            \App\Enums\AssetRequestStatusEnum::REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
        };
    @endphp

    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">{{ $assetRequest->request_number }}</span>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                        {{ $assetRequest->asset->name }}
                    </h2>
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $statusClasses }}">
                        {{ str($assetRequest->status->value)->headline() }}
                    </span>
                </div>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review the request details, current status, and full approval timeline.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('staff.requests.index') }}" class="secondary-button">
                    Back To My Requests
                </a>

                @can('cancel', $assetRequest)
                    <form method="POST" action="{{ route('staff.requests.cancel', $assetRequest) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="secondary-button border-rose-200 text-rose-700 hover:border-rose-300 hover:bg-rose-50">
                            Cancel Request
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <x-ui.panel
            title="Request summary"
            description="These values travel with the request throughout review and issuing."
        >
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Department</p>
                    <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->department->name }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Quantity</p>
                    <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->quantity_requested }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Priority</p>
                    <p class="mt-3 text-lg font-extrabold text-slate-950">{{ str($assetRequest->priority->value)->headline() }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Needed By</p>
                    <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->needed_by_date?->format('d M Y') ?? 'Not specified' }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 sm:col-span-2">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Reason</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $assetRequest->reason }}</p>
                </div>
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Status timeline"
            description="Every workflow update is recorded here for easy follow-up."
        >
            <div class="space-y-4">
                @foreach ($assetRequest->statusHistories as $history)
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm font-extrabold text-slate-900">{{ str($history->status->value)->headline() }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    {{ $history->comment ?: 'No extra comment recorded for this step.' }}
                                </p>
                            </div>
                            <div class="text-sm text-slate-500 sm:text-right">
                                <p class="font-bold text-slate-700">{{ $history->actor?->name ?? 'System' }}</p>
                                <p class="mt-1">{{ $history->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </x-ui.panel>
    </section>
@endsection
