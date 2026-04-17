@extends('layouts.dashboard')

@section('title', 'Asset Categories')
@section('page-eyebrow', 'Inventory Catalog')
@section('page-title', 'Asset Categories')
@section('page-description', 'Maintain the category structure used by asset records and stock reporting.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Category Catalog</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Keep your asset taxonomy clear and reusable.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Categories help keep the asset catalog clean, searchable, and easier to report on.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.asset-categories.create') }}" class="primary-button">Add Category</a>
                <a href="{{ route('admin.assets.index') }}" class="secondary-button">View Assets</a>
            </div>
        </div>
    </section>

    @if (session('status'))
        <x-ui.alert>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <x-ui.panel
        title="Category records"
        description="Review current categories, usage counts, and active state."
    >
        @if ($categories->isEmpty())
            <x-ui.empty-state
                title="No categories yet"
                description="Create a category before adding assets to the catalog."
                action-label="Add Category"
                action-url="{{ route('admin.asset-categories.create') }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Assets</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($categories as $category)
                            <tr class="bg-white">
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-slate-950">{{ $category->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $category->slug }}</p>
                                </td>
                                <td class="px-4 py-4">{{ $category->description ?: 'No description' }}</td>
                                <td class="px-4 py-4 font-bold">{{ $category->assets_count }}</td>
                                <td class="px-4 py-4">{{ $category->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.asset-categories.edit', $category) }}" class="secondary-button px-4 py-2 text-xs">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.asset-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
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
                {{ $categories->links() }}
            </div>
        @endif
    </x-ui.panel>
@endsection
