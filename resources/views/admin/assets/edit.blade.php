@extends('layouts.dashboard')

@section('title', 'Edit Asset')
@section('page-eyebrow', 'Inventory Catalog')
@section('page-title', 'Edit Asset')
@section('page-description', 'Update inventory details without leaving the catalog workspace.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Asset Maintenance</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Update {{ $asset->name }}.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Keep stock quantities, assignment, and asset metadata aligned with the real inventory state.
                </p>
            </div>

            <a href="{{ route('admin.assets.index') }}" class="secondary-button">Back to Assets</a>
        </div>
    </section>

    @if (session('status'))
        <x-ui.alert>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <x-ui.panel
        title="Asset details"
        description="Update quantities, classification, department assignment, and notes."
    >
        @include('admin.assets._form', [
            'action' => route('admin.assets.update', $asset),
            'method' => 'PATCH',
            'submitLabel' => 'Save Changes',
            'asset' => $asset,
            'categories' => $categories,
            'departments' => $departments,
            'statuses' => $statuses,
            'unitTypes' => $unitTypes,
        ])
    </x-ui.panel>
@endsection
