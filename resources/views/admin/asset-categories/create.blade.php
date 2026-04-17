@extends('layouts.dashboard')

@section('title', 'Add Asset Category')
@section('page-eyebrow', 'Inventory Catalog')
@section('page-title', 'Add Asset Category')
@section('page-description', 'Create a reusable category for the inventory catalog.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Category Setup</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Add a new category to the asset catalog.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Categories make inventory easier to manage, search, and report on.
                </p>
            </div>

            <a href="{{ route('admin.asset-categories.index') }}" class="secondary-button">Back to Categories</a>
        </div>
    </section>

    <x-ui.panel
        title="Category details"
        description="Define the name, description, and active state of the new category."
    >
        @include('admin.asset-categories._form', [
            'action' => route('admin.asset-categories.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Category',
            'assetCategory' => null,
        ])
    </x-ui.panel>
@endsection
