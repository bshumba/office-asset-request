@extends('layouts.dashboard')

@section('title', 'Create Role')
@section('page-eyebrow', 'Access Control')
@section('page-title', 'Create Role')
@section('page-description', 'Define a new role and assign the permissions it should receive from the start.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Role Builder</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Create a new role and choose its permissions.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Add roles for additional teams, workflows, or restricted admin areas.
                </p>
            </div>

            <a href="{{ route('admin.roles.index') }}" class="secondary-button">
                Back to Roles
            </a>
        </div>
    </section>

    <x-ui.panel
        title="Role details"
        description="Choose a role name and the permissions it should receive."
    >
        @include('admin.access.roles._form', [
            'action' => route('admin.roles.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Role',
            'isSystemRole' => false,
        ])
    </x-ui.panel>
@endsection
