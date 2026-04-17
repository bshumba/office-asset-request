@extends('layouts.dashboard')

@section('title', 'Edit Role')
@section('page-eyebrow', 'Access Control')
@section('page-title', 'Edit Role: '.$role->name)
@section('page-description', 'Adjust the permissions attached to this role and keep the authorization matrix aligned with the actual workflow.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Role Editor</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Fine-tune how {{ $role->name }} behaves in the application.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Update the role name and permission set to match the access rules you need.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.permissions.index') }}" class="secondary-button">Manage Permissions</a>
                <a href="{{ route('admin.roles.index') }}" class="secondary-button">Back to Roles</a>
            </div>
        </div>
    </section>

    <x-ui.panel
        title="Role configuration"
        description="System roles keep their names, but their permission sets can still be reviewed and adjusted here."
    >
        @include('admin.access.roles._form', [
            'action' => route('admin.roles.update', $role),
            'method' => 'PATCH',
            'submitLabel' => 'Save Changes',
            'isSystemRole' => $isSystemRole,
            'role' => $role,
        ])
    </x-ui.panel>
@endsection
