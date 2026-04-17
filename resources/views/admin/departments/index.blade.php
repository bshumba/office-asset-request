@extends('layouts.dashboard')

@section('title', 'Departments')
@section('page-eyebrow', 'Admin Directory')
@section('page-title', 'Departments')
@section('page-description', 'Maintain department records, review assigned managers, and keep the structure used by the workflow clean.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Department Directory</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Keep departments structured and ready for account assignment.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review current managers, account counts, and active status from one place.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.departments.create') }}" class="primary-button">Add Department</a>
                <a href="{{ route('admin.dashboard') }}" class="secondary-button">Back to Dashboard</a>
            </div>
        </div>
    </section>

    @if (session('status'))
        <x-ui.alert>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <x-ui.panel
        title="Department records"
        description="Edit, deactivate, or review the departments available across the platform."
    >
        @if ($departments->isEmpty())
            <x-ui.empty-state
                title="No departments yet"
                description="Create your first department before assigning staff, managers, and assets."
                action-label="Add Department"
                action-url="{{ route('admin.departments.create') }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Manager</th>
                            <th class="px-4 py-3">Users</th>
                            <th class="px-4 py-3">Assets</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($departments as $department)
                            <tr class="bg-white">
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-slate-950">{{ $department->name }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $department->code }}</p>
                                </td>
                                <td class="px-4 py-4">{{ $department->manager?->name ?? 'No manager assigned' }}</td>
                                <td class="px-4 py-4 font-bold">{{ $department->users_count }}</td>
                                <td class="px-4 py-4 font-bold">{{ $department->assets_count }}</td>
                                <td class="px-4 py-4">{{ $department->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.departments.edit', $department) }}" class="secondary-button px-4 py-2 text-xs">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('Delete this department?');">
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
                {{ $departments->links() }}
            </div>
        @endif
    </x-ui.panel>
@endsection
