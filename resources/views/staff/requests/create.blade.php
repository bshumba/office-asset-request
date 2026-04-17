@extends('layouts.dashboard')

@section('title', 'Create Request')
@section('page-eyebrow', 'Staff Requests')
@section('page-title', 'Create Asset Request')
@section('page-description', 'Submit a new request that will start in the pending state and enter the approval workflow for your department.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">New Request</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Choose an available asset and explain why you need it.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Requests are submitted under your account and department automatically.
                </p>
            </div>

            <a href="{{ route('staff.requests.index') }}" class="secondary-button">
                Back To My Requests
            </a>
        </div>
    </section>

    <x-ui.panel
        title="Request details"
        description="Only active assets from your department are available here. Request number and status history are created automatically."
    >
        <form method="POST" action="{{ route('staff.requests.store') }}" class="grid gap-6 lg:grid-cols-2">
            @csrf

            <div class="lg:col-span-2">
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

            <div>
                <label for="quantity_requested" class="text-sm font-bold text-slate-700">Quantity requested</label>
                <input
                    id="quantity_requested"
                    name="quantity_requested"
                    type="number"
                    min="1"
                    value="{{ old('quantity_requested', 1) }}"
                    class="form-input-shell"
                >
                @error('quantity_requested')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="priority" class="text-sm font-bold text-slate-700">Priority</label>
                <select id="priority" name="priority" class="form-input-shell">
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(old('priority', \App\Enums\RequestPriorityEnum::NORMAL->value) === $priority->value)>
                            {{ str($priority->value)->headline() }}
                        </option>
                    @endforeach
                </select>
                @error('priority')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg:col-span-2">
                <label for="needed_by_date" class="text-sm font-bold text-slate-700">Needed by date</label>
                <input
                    id="needed_by_date"
                    name="needed_by_date"
                    type="date"
                    value="{{ old('needed_by_date') }}"
                    class="form-input-shell"
                >
                @error('needed_by_date')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg:col-span-2">
                <label for="reason" class="text-sm font-bold text-slate-700">Reason</label>
                <textarea
                    id="reason"
                    name="reason"
                    rows="6"
                    class="form-input-shell"
                    placeholder="Explain the business reason for this request."
                >{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg:col-span-2 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="primary-button">
                    Submit Request
                </button>
                <a href="{{ route('staff.requests.index') }}" class="secondary-button">
                    Cancel
                </a>
            </div>
        </form>
    </x-ui.panel>
@endsection
