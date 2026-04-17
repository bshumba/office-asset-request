@extends('layouts.dashboard')

@section('title', 'Admin Request Review')
@section('page-eyebrow', 'Admin Review')
@section('page-title', 'Request Review')
@section('page-description', 'Approve the request for issuing, reject it with a reason, or issue the asset once admin approval is complete.')

@section('content')
    @php
        $statusClasses = match ($assetRequest->status) {
            \App\Enums\AssetRequestStatusEnum::PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
            \App\Enums\AssetRequestStatusEnum::MANAGER_APPROVED => 'border-sky-200 bg-sky-50 text-sky-700',
            \App\Enums\AssetRequestStatusEnum::ADMIN_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            \App\Enums\AssetRequestStatusEnum::ISSUED => 'border-violet-200 bg-violet-50 text-violet-700',
            \App\Enums\AssetRequestStatusEnum::RETURNED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            \App\Enums\AssetRequestStatusEnum::REJECTED => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-50 text-slate-700',
        };
    @endphp

    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">{{ $assetRequest->request_number }}</span>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                        {{ $assetRequest->user->name }} · {{ $assetRequest->asset->name }}
                    </h2>
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $statusClasses }}">
                        {{ str($assetRequest->status->value)->headline() }}
                    </span>
                </div>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review the request, record an admin decision, and issue the asset when it is ready.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.requests.index') }}" class="secondary-button">Back To Inbox</a>
                <a href="{{ route('admin.issues.index') }}" class="secondary-button">Issued Assets</a>
            </div>
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
                description="Use the manager-approved quantity, available stock, and the business reason together before making an admin decision."
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Department</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->department->name }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Available stock</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->asset->quantity_available }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Requested quantity</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->quantity_requested }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Manager-approved quantity</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetRequest->quantity_approved ?? 'Pending admin decision' }}</p>
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

            @if ($assetRequest->status === \App\Enums\AssetRequestStatusEnum::MANAGER_APPROVED)
                <x-ui.panel
                    title="Approve for issue"
                    description="Admin approval confirms that stock can be reserved and the request is ready to become an issue record."
                >
                    <form method="POST" action="{{ route('admin.requests.approve', $assetRequest) }}" class="grid gap-5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="quantity_approved" class="text-sm font-bold text-slate-700">Approved quantity</label>
                            <input id="quantity_approved" name="quantity_approved" type="number" min="1" max="{{ $assetRequest->quantity_requested }}" value="{{ old('quantity_approved', $assetRequest->quantity_approved ?? $assetRequest->quantity_requested) }}" class="form-input-shell">
                            @error('quantity_approved')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="admin_comment" class="text-sm font-bold text-slate-700">Admin comment</label>
                            <textarea id="admin_comment" name="admin_comment" rows="4" class="form-input-shell" placeholder="Optional notes for issuing or later review.">{{ old('admin_comment') }}</textarea>
                            @error('admin_comment')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="primary-button">Approve Request</button>
                        </div>
                    </form>
                </x-ui.panel>
            @endif

            @if ($assetRequest->status === \App\Enums\AssetRequestStatusEnum::MANAGER_APPROVED)
                <x-ui.panel
                    title="Reject request"
                    description="Rejection at the admin stage should explain why the request stopped after manager review."
                >
                    <form method="POST" action="{{ route('admin.requests.reject', $assetRequest) }}" class="grid gap-5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="rejection_reason" class="text-sm font-bold text-slate-700">Rejection reason</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="4" class="form-input-shell" placeholder="Explain why the request is being rejected.">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="admin_reject_comment" class="text-sm font-bold text-slate-700">Admin comment</label>
                            <textarea id="admin_reject_comment" name="admin_comment" rows="3" class="form-input-shell" placeholder="Optional extra context for the requester.">{{ old('admin_comment') }}</textarea>
                            @error('admin_comment')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="secondary-button border-rose-200 text-rose-700 hover:border-rose-300 hover:bg-rose-50">Reject Request</button>
                        </div>
                    </form>
                </x-ui.panel>
            @endif

            @if ($assetRequest->status === \App\Enums\AssetRequestStatusEnum::ADMIN_APPROVED && ! $assetRequest->issue)
                <x-ui.panel
                    title="Issue Asset"
                    description="Once admin approval is complete, issuing the asset creates the issue record and deducts the stock."
                >
                    <form method="POST" action="{{ route('admin.issues.store', $assetRequest) }}" class="grid gap-5">
                        @csrf

                        <div>
                            <label for="quantity_issued" class="text-sm font-bold text-slate-700">Quantity issued</label>
                            <input id="quantity_issued" name="quantity_issued" type="number" min="1" max="{{ $assetRequest->quantity_approved }}" value="{{ old('quantity_issued', $assetRequest->quantity_approved) }}" class="form-input-shell">
                            @error('quantity_issued')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="expected_return_date" class="text-sm font-bold text-slate-700">Expected return date</label>
                            <input id="expected_return_date" name="expected_return_date" type="date" value="{{ old('expected_return_date') }}" class="form-input-shell">
                            @error('expected_return_date')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="issue_notes" class="text-sm font-bold text-slate-700">Issue notes</label>
                            <textarea id="issue_notes" name="notes" rows="4" class="form-input-shell" placeholder="Optional context for the issue record.">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="primary-button">Issue Asset</button>
                        </div>
                    </form>
                </x-ui.panel>
            @elseif ($assetRequest->issue)
                <x-ui.panel title="Issue record" description="This request already has an issue record linked to it.">
                    <a href="{{ route('admin.issues.show', $assetRequest->issue) }}" class="secondary-button">
                        Open Issue Record
                    </a>
                </x-ui.panel>
            @endif
        </div>

        <x-ui.panel
            title="Status timeline"
            description="The request history shows how the record moved from staff submission through manager review into admin approval and issue stages."
        >
            <div class="space-y-4">
                @foreach ($assetRequest->statusHistories as $history)
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm font-extrabold text-slate-900">{{ str($history->status->value)->headline() }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $history->comment ?: 'No extra comment recorded for this step.' }}</p>
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
