@extends('layouts.dashboard')

@section('title', 'Roles')
@section('page-eyebrow', 'Access Control')
@section('page-title', 'Role Management')
@section('page-description', 'Create new roles, inspect system roles, and control which permissions each role receives.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Roles</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Manage roles and their permission sets from one place.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Create new roles, review system roles, and keep access aligned with the workflow.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.roles.create') }}" class="primary-button">Create Role</a>
                <a href="{{ route('admin.permissions.index') }}" class="secondary-button">Manage Permissions</a>
            </div>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <x-ui.panel
        title="Roles in this system"
        description="System roles support the current workflow, and additional roles can be added as needed."
    >
        <div class="grid gap-4 xl:grid-cols-2">
            @foreach ($roles as $role)
                <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="text-lg font-extrabold text-slate-950">{{ $role->name }}</p>
                                @if (in_array($role->name, $systemRoles, true))
                                    <span class="inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-orange-600">
                                        System Role
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-slate-500">{{ $role->permissions->count() }} permission(s) assigned</p>

                            <div class="flex flex-wrap gap-2">
                                @forelse ($role->permissions->take(6) as $permission)
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-600">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-500">No permissions assigned yet.</span>
                                @endforelse

                                @if ($role->permissions->count() > 6)
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-600">
                                        +{{ $role->permissions->count() - 6 }} more
                                    </span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('admin.roles.edit', $role) }}" class="secondary-button">
                            Edit Role
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $roles->links() }}
        </div>
    </x-ui.panel>
@endsection
