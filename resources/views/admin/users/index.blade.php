@extends('layouts.dashboard')

@section('title', 'Team Management')
@section('page-eyebrow', 'Admin Users')
@section('page-title', 'Team Management')
@section('page-description', 'Create staff and manager accounts, review role assignments, and keep department membership visible from one admin workspace.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Workforce Setup</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Manage staff and manager accounts from one admin workspace.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Create users, assign roles, link departments, and review account status without leaving the application.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.users.create') }}" class="primary-button">Add Team Member</a>
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
        title="Existing accounts"
        description="Review role assignment, department membership, and account status before creating more users."
    >
        @if ($users->isEmpty())
            <x-ui.empty-state
                title="No workforce accounts yet"
                description="Create your first staff or manager account to start building the real approval workflow."
                action-label="Add Team Member"
                action-url="{{ route('admin.users.create') }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Notes</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($users as $userItem)
                            <tr class="bg-white">
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-slate-950">{{ $userItem->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $userItem->email }}</p>
                                </td>
                                <td class="px-4 py-4">{{ $userItem->getRoleNames()->join(', ') ?: 'No role' }}</td>
                                <td class="px-4 py-4">{{ $userItem->department?->name ?? 'No department' }}</td>
                                <td class="px-4 py-4">{{ str($userItem->status->value)->headline() }}</td>
                                <td class="px-4 py-4">{{ $userItem->notes ?: 'No notes' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.users.edit', $userItem) }}" class="secondary-button px-4 py-2 text-xs">
                                            Edit
                                        </a>
                                        @if ($userItem->status !== \App\Enums\UserStatusEnum::INACTIVE)
                                            <form method="POST" action="{{ route('admin.users.deactivate', $userItem) }}" onsubmit="return confirm('Deactivate this account?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="secondary-button border-rose-200 px-4 py-2 text-xs text-rose-700 hover:bg-rose-50">
                                                    Deactivate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        @endif
    </x-ui.panel>
@endsection
