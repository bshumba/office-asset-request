@extends('layouts.dashboard')

@section('title', 'Edit Asset Category')
@section('page-eyebrow', 'Inventory Catalog')
@section('page-title', 'Edit Asset Category')
@section('page-description', 'Update the category metadata used throughout the inventory catalog.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Category Maintenance</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Update {{ $assetCategory->name }}.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Keep category naming and descriptions aligned with the current inventory structure.
                </p>
            </div>

            <a href="{{ route('admin.asset-categories.index') }}" class="secondary-button">Back to Categories</a>
        </div>
    </section>

    @if (session('status'))
        <x-ui.alert>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <x-ui.panel
        title="Category details"
        description="Update category naming, description, and availability."
    >
        @include('admin.asset-categories._form', [
            'action' => route('admin.asset-categories.update', $assetCategory),
            'method' => 'PATCH',
            'submitLabel' => 'Save Changes',
            'assetCategory' => $assetCategory,
        ])
    </x-ui.panel>
@endsection
