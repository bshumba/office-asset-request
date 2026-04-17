@extends('layouts.dashboard')

@section('title', 'Stock Adjustments')
@section('page-eyebrow', 'Admin Stock')
@section('page-title', 'Stock Adjustments')
@section('page-description', 'Increase or decrease stock deliberately, keep an audit trail, and make inventory corrections without bypassing the workflow.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Inventory Movement</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Stock adjustments are the controlled path for inventory corrections.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Use this workspace for restocks, damage, loss, and corrections. Unlike issuing and returns, these changes do not come from a request lifecycle and should always leave an audit trail.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.requests.index') }}" class="secondary-button">Request Inbox</a>
                <a href="{{ route('admin.issues.index') }}" class="secondary-button">Issued Assets</a>
            </div>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <x-ui.panel
            title="New adjustment"
            description="Increases add stock to both total and available quantities, while decreases remove only from currently available inventory."
        >
            <form method="POST" action="{{ route('admin.adjustments.store') }}" class="grid gap-5">
                @csrf

                <div>
                    <label for="asset_id" class="text-sm font-bold text-slate-700">Asset</label>
                    <select id="asset_id" name="asset_id" class="form-input-shell">
                        <option value="">Select an asset</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}" @selected(old('asset_id') == $asset->id)>
                                {{ $asset->name }} · {{ $asset->quantity_available }} available
                            </option>
                        @endforeach
                    </select>
                    @error('asset_id')
                        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="type" class="text-sm font-bold text-slate-700">Adjustment type</label>
                        <select id="type" name="type" class="form-input-shell">
                            @foreach ($types as $type)
                                <option value="{{ $type->value }}" @selected(old('type', \App\Enums\StockAdjustmentTypeEnum::INCREASE->value) === $type->value)>
                                    {{ str($type->value)->headline() }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="quantity" class="text-sm font-bold text-slate-700">Quantity</label>
                        <input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" class="form-input-shell">
                        @error('quantity')
                            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="reason" class="text-sm font-bold text-slate-700">Reason</label>
                    <select id="reason" name="reason" class="form-input-shell">
                        @foreach ($reasons as $reason)
                            <option value="{{ $reason->value }}" @selected(old('reason') === $reason->value)>
                                {{ str($reason->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                    @error('reason')
                        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reference" class="text-sm font-bold text-slate-700">Reference</label>
                    <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="form-input-shell" placeholder="Optional PO, ticket, or audit reference">
                    @error('reference')
                        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="note" class="text-sm font-bold text-slate-700">Note</label>
                    <textarea id="note" name="note" rows="4" class="form-input-shell" placeholder="Optional explanation for this adjustment.">{{ old('note') }}</textarea>
                    @error('note')
                        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit" class="primary-button">Save Adjustment</button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel
            title="Recent adjustments"
            description="This audit trail helps explain why stock changed outside of request issuing and returns."
        >
            @if ($adjustments->isEmpty())
                <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                    <p class="text-lg font-extrabold text-slate-900">No adjustments yet</p>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Record a stock movement and it will appear here with its reason and creator.
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($adjustments as $adjustment)
                        @php
                            $typeClasses = $adjustment->type === \App\Enums\StockAdjustmentTypeEnum::INCREASE
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-rose-200 bg-rose-50 text-rose-700';
                        @endphp

                        <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="text-lg font-extrabold text-slate-950">{{ $adjustment->asset->name }}</p>
                                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $typeClasses }}">
                                            {{ str($adjustment->type->value)->headline() }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-5 text-sm text-slate-500">
                                        <span>Qty: {{ $adjustment->quantity }}</span>
                                        <span>Reason: {{ str($adjustment->reason->value)->headline() }}</span>
                                        <span>By: {{ $adjustment->createdBy->name }}</span>
                                    </div>
                                    <p class="text-sm leading-6 text-slate-500">{{ $adjustment->note ?: 'No extra note recorded.' }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $adjustments->links() }}
                </div>
            @endif
        </x-ui.panel>
    </section>
@endsection
