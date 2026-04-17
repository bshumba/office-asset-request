@extends('layouts.dashboard')

@section('title', 'Add Team Member')
@section('page-eyebrow', 'Admin Users')
@section('page-title', 'Add Team Member')
@section('page-description', 'Create a new staff or manager account and connect it to the correct department from the start.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">User Creation</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Add the staff and managers who keep the workflow moving.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Create workforce accounts with the right role, department, and status from the start.
                </p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="secondary-button">
                Back to Team
            </a>
        </div>
    </section>

    @if (session('status'))
        <x-ui.alert>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <x-ui.panel
        title="Account details"
        description="Create a new staff or manager account with the correct department assignment and access level."
    >
        @include('admin.users._form', [
            'action' => route('admin.users.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Account',
            'managedUser' => null,
            'departments' => $departments,
            'roles' => $roles,
            'statuses' => $statuses,
        ])
    </x-ui.panel>
@endsection
