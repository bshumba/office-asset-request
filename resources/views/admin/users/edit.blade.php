@extends('layouts.dashboard')

@section('title', 'Edit Team Member')
@section('page-eyebrow', 'Admin Users')
@section('page-title', 'Edit Team Member')
@section('page-description', 'Update account details, role assignment, department membership, or status from one admin workspace.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Account Management</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Keep {{ $managedUser->name }} aligned with the right access and department.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Update the account profile, change role assignment, or deactivate the account if the user should no longer access the system.
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

    <section class="grid gap-6 xl:grid-cols-[1fr_0.4fr]">
        <x-ui.panel
            title="Account details"
            description="Use this form to keep the workforce directory accurate and the assigned access current."
        >
            @include('admin.users._form', [
                'action' => route('admin.users.update', $managedUser),
                'method' => 'PATCH',
                'submitLabel' => 'Save Changes',
                'managedUser' => $managedUser,
                'departments' => $departments,
                'roles' => $roles,
                'statuses' => $statuses,
            ])
        </x-ui.panel>

        <x-ui.panel
            title="Account status"
            description="Deactivate accounts that should no longer sign in."
        >
            <div class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Current role</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $managedUser->getRoleNames()->join(', ') ?: 'No role' }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Current status</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ str($managedUser->status->value)->headline() }}</p>
                </div>

                <form method="POST" action="{{ route('admin.users.deactivate', $managedUser) }}" onsubmit="return confirm('Deactivate this account?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="secondary-button w-full justify-center border-rose-200 text-rose-700 hover:bg-rose-50">
                        Deactivate Account
                    </button>
                </form>
            </div>
        </x-ui.panel>
    </section>
@endsection
