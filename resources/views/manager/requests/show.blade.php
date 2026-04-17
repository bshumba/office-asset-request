@extends('layouts.dashboard')

@section('title', 'Review Request')
@section('page-eyebrow', 'Manager Reviews')
@section('page-title', 'Review Department Request')
@section('page-description', 'Approve requests that should move to admin review, or reject requests that should stop at the manager stage.')

@section('content')
    @php
        $statusClasses = match ($assetRequest->status) {
            \App\Enums\AssetRequestStatusEnum::PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
            \App\Enums\AssetRequestStatusEnum::MANAGER_APPROVED => 'border-sky-200 bg-sky-50 text-sky-700',
            \App\Enums\AssetRequestStatusEnum::REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
            \App\Enums\AssetRequestStatusEnum::ADMIN_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            \App\Enums\AssetRequestStatusEnum::ISSUED => 'border-violet-200 bg-violet-50 text-violet-700',
            \App\Enums\AssetRequestStatusEnum::RETURNED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            \App\Enums\AssetRequestStatusEnum::CANCELLED => 'border-slate-200 bg-slate-50 text-slate-700',
        };
    @endphp

    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">{{ $assetRequest->request_number }}</span>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                        {{ $assetRequest->user->name }}
                    </h2>
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $statusClasses }}">
                        {{ str($assetRequest->status->value)->headline() }}
                    </span>
                </div>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    This review page makes the department policy concrete: the manager can act only on pending requests from their own department.
                </p>
            </div>

            <a href="{{ route('manager.requests.index') }}" class="secondary-button">
                Back To Inbox
            </a>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="space-y-6">
            <x-ui.panel
                title="Request summary"
                description="These are the details the manager should use to decide whether the request is valid for the department."
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Requester</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->user->name }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Department</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->department->name }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Asset</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->asset->name }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Quantity requested</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->quantity_requested }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Priority</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ str($assetRequest->priority->value)->headline() }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Needed by</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->needed_by_date?->format('d M Y') ?? 'Not specified' }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 sm:col-span-2">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Reason</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $assetRequest->reason }}</p>
                    </div>
                    @if ($assetRequest->rejection_reason)
                        <div class="rounded-3xl border border-rose-200 bg-rose-50 p-5 sm:col-span-2">
                            <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-rose-500">Rejection reason</p>
                            <p class="mt-3 text-sm leading-7 text-rose-700">{{ $assetRequest->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            </x-ui.panel>

            @can('managerApprove', $assetRequest)
                <x-ui.panel
                    title="Approve request"
                    description="Approving here moves the request to the manager-approved state so admins can review it next."
                >
                    <form method="POST" action="{{ route('manager.requests.approve', $assetRequest) }}" class="grid gap-5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="quantity_approved" class="text-sm font-bold text-slate-700">Quantity approved</label>
                            <input
                                id="quantity_approved"
                                name="quantity_approved"
                                type="number"
                                min="1"
                                max="{{ $assetRequest->quantity_requested }}"
                                value="{{ old('quantity_approved', $assetRequest->quantity_requested) }}"
                                class="form-input-shell"
                            >
                            @error('quantity_approved')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="manager_comment" class="text-sm font-bold text-slate-700">Manager comment</label>
                            <textarea
                                id="manager_comment"
                                name="manager_comment"
                                rows="4"
                                class="form-input-shell"
                                placeholder="Add context for the next approval stage."
                            >{{ old('manager_comment') }}</textarea>
                            @error('manager_comment')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="primary-button">
                                Approve Request
                            </button>
                        </div>
                    </form>
                </x-ui.panel>
            @endcan

            @can('reject', $assetRequest)
                <x-ui.panel
                    title="Reject request"
                    description="Rejection requires a reason so the staff member and admins can understand why the request stopped here."
                >
                    <form method="POST" action="{{ route('manager.requests.reject', $assetRequest) }}" class="grid gap-5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="rejection_reason" class="text-sm font-bold text-slate-700">Rejection reason</label>
                            <textarea
                                id="rejection_reason"
                                name="rejection_reason"
                                rows="4"
                                class="form-input-shell"
                                placeholder="Explain why this request is being rejected."
                            >{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="manager_reject_comment" class="text-sm font-bold text-slate-700">Manager comment</label>
                            <textarea
                                id="manager_reject_comment"
                                name="manager_comment"
                                rows="3"
                                class="form-input-shell"
                                placeholder="Optional extra context for the requester."
                            >{{ old('manager_comment') }}</textarea>
                            @error('manager_comment')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="secondary-button border-rose-200 text-rose-700 hover:border-rose-300 hover:bg-rose-50">
                                Reject Request
                            </button>
                        </div>
                    </form>
                </x-ui.panel>
            @endcan
        </div>

        <x-ui.panel
            title="Status timeline"
            description="Manager review should add a timestamped history entry so the workflow is auditable as it moves to later phases."
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
