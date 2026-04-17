@extends('layouts.dashboard')

@section('title', 'Issue Record')
@section('page-eyebrow', 'Admin Issues')
@section('page-title', 'Issue Record')
@section('page-description', 'Record returns, monitor outstanding quantity, and review the original request history from the issue record.')

@section('content')
    @php
        $statusClasses = match ($assetIssue->status) {
            \App\Enums\AssetIssueStatusEnum::ISSUED => 'border-violet-200 bg-violet-50 text-violet-700',
            \App\Enums\AssetIssueStatusEnum::PARTIALLY_RETURNED => 'border-amber-200 bg-amber-50 text-amber-700',
            \App\Enums\AssetIssueStatusEnum::RETURNED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        };
    @endphp

    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">{{ $assetIssue->assetRequest->request_number }}</span>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                        {{ $assetIssue->issuedToUser->name }} · {{ $assetIssue->asset->name }}
                    </h2>
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $statusClasses }}">
                        {{ str($assetIssue->status->value)->headline() }}
                    </span>
                </div>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Issue records track what left stock, who received it, and how much remains outstanding before the request lifecycle is complete.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.issues.index') }}" class="secondary-button">Back To Issues</a>
                <a href="{{ route('admin.requests.show', $assetIssue->assetRequest) }}" class="secondary-button">View Request</a>
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
                title="Issue summary"
                description="These values show what left stock and how much is still expected back."
            >
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Issued quantity</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetIssue->quantity_issued }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Outstanding quantity</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetIssue->outstandingQuantity() }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Issued at</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetIssue->issued_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Expected return</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-950">{{ $assetIssue->expected_return_date?->format('d M Y') ?? 'Not specified' }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 sm:col-span-2">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Issue notes</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $assetIssue->notes ?: 'No issue notes recorded.' }}</p>
                    </div>
                </div>
            </x-ui.panel>

            @if (in_array($assetIssue->status, [\App\Enums\AssetIssueStatusEnum::ISSUED, \App\Enums\AssetIssueStatusEnum::PARTIALLY_RETURNED], true))
                <x-ui.panel
                    title="Record Return"
                    description="Returns restore stock and update the issue lifecycle. A full return will also complete the related request."
                >
                    <form method="POST" action="{{ route('admin.returns.store', $assetIssue) }}" class="grid gap-5">
                        @csrf

                        <div>
                            <label for="quantity_returned" class="text-sm font-bold text-slate-700">Quantity returned</label>
                            <input id="quantity_returned" name="quantity_returned" type="number" min="1" max="{{ $assetIssue->outstandingQuantity() }}" value="{{ old('quantity_returned', $assetIssue->outstandingQuantity()) }}" class="form-input-shell">
                            @error('quantity_returned')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="condition_on_return" class="text-sm font-bold text-slate-700">Condition on return</label>
                            <select id="condition_on_return" name="condition_on_return" class="form-input-shell">
                                @foreach (\App\Enums\ReturnConditionEnum::cases() as $condition)
                                    <option value="{{ $condition->value }}" @selected(old('condition_on_return', \App\Enums\ReturnConditionEnum::GOOD->value) === $condition->value)>
                                        {{ str($condition->value)->headline() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('condition_on_return')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="return_remarks" class="text-sm font-bold text-slate-700">Return remarks</label>
                            <textarea id="return_remarks" name="remarks" rows="4" class="form-input-shell" placeholder="Optional condition notes or return details.">{{ old('remarks') }}</textarea>
                            @error('remarks')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="primary-button">Record Return</button>
                        </div>
                    </form>
                </x-ui.panel>
            @endif

            <x-ui.panel
                title="Return history"
                description="Every return entry records how much came back and in what condition."
            >
                @if ($assetIssue->returns->isEmpty())
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center text-sm text-slate-500">
                        No returns recorded yet.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($assetIssue->returns->sortByDesc('returned_at') as $return)
                            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-sm font-extrabold text-slate-900">
                                            {{ $return->quantity_returned }} returned · {{ str($return->condition_on_return->value)->headline() }}
                                        </p>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $return->remarks ?: 'No extra remarks recorded.' }}</p>
                                    </div>
                                    <div class="text-sm text-slate-500 sm:text-right">
                                        <p class="font-bold text-slate-700">{{ $return->receivedByUser->name }}</p>
                                        <p class="mt-1">{{ $return->returned_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-ui.panel>
        </div>

        <x-ui.panel
            title="Request status timeline"
            description="The request timeline shows the workflow state changes tied to this issue record."
        >
            <div class="space-y-4">
                @foreach ($assetIssue->assetRequest->statusHistories as $history)
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
