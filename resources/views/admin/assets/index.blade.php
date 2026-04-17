@extends('layouts.dashboard')

@section('title', 'Assets')
@section('page-eyebrow', 'Inventory Catalog')
@section('page-title', 'Assets')
@section('page-description', 'Maintain inventory records, stock levels, and category assignment from the admin catalog.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Asset Catalog</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Keep inventory records complete and ready for workflow use.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review asset counts, department assignment, and stock state before the next request is created.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.assets.create') }}" class="primary-button">Add Asset</a>
                <a href="{{ route('admin.asset-categories.index') }}" class="secondary-button">Manage Categories</a>
            </div>
        </div>
    </section>

    @if (session('status'))
        <x-ui.alert>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <x-ui.panel
        title="Asset records"
        description="Review category, department, quantity, and status in one table."
    >
        @if ($assets->isEmpty())
            <x-ui.empty-state
                title="No assets in the catalog yet"
                description="Add your first asset record so requests and stock reporting have real inventory to work with."
                action-label="Add Asset"
                action-url="{{ route('admin.assets.create') }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Asset</th>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Available</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($assets as $asset)
                            <tr class="bg-white">
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-slate-950">{{ $asset->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $asset->asset_code }}</p>
                                </td>
                                <td class="px-4 py-4">{{ $asset->department?->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-4">{{ $asset->category?->name ?? 'Uncategorized' }}</td>
                                <td class="px-4 py-4">{{ str($asset->status->value)->headline() }}</td>
                                <td class="px-4 py-4 font-bold">{{ $asset->quantity_available }} / {{ $asset->quantity_total }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.assets.edit', $asset) }}" class="secondary-button px-4 py-2 text-xs">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}" onsubmit="return confirm('Delete this asset?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="secondary-button border-rose-200 px-4 py-2 text-xs text-rose-700 hover:bg-rose-50">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $assets->links() }}
            </div>
        @endif
    </x-ui.panel>
@endsection
