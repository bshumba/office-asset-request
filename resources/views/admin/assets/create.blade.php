@extends('layouts.dashboard')

@section('title', 'Add Asset')
@section('page-eyebrow', 'Inventory Catalog')
@section('page-title', 'Add Asset')
@section('page-description', 'Create a new inventory record with the right quantities, category, and department.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Asset Setup</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Add an asset to the inventory catalog.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Define the stock levels, category, and department assignment before the asset enters the workflow.
                </p>
            </div>

            <a href="{{ route('admin.assets.index') }}" class="secondary-button">Back to Assets</a>
        </div>
    </section>

    <x-ui.panel
        title="Asset details"
        description="Capture the inventory fields needed by requests, reports, and stock monitoring."
    >
        @include('admin.assets._form', [
            'action' => route('admin.assets.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Asset',
            'asset' => null,
            'categories' => $categories,
            'departments' => $departments,
            'statuses' => $statuses,
            'unitTypes' => $unitTypes,
        ])
    </x-ui.panel>
@endsection
