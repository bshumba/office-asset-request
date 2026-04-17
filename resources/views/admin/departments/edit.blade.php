@extends('layouts.dashboard')

@section('title', 'Edit Department')
@section('page-eyebrow', 'Admin Directory')
@section('page-title', 'Edit Department')
@section('page-description', 'Update the department record used across users, assets, workflow, and reports.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Department Maintenance</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Update {{ $department->name }}.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Keep the department directory accurate so account assignment and reporting remain reliable.
                </p>
            </div>

            <a href="{{ route('admin.departments.index') }}" class="secondary-button">Back to Departments</a>
        </div>
    </section>

    @if (session('status'))
        <x-ui.alert>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <section class="grid gap-6 xl:grid-cols-[1fr_0.4fr]">
        <x-ui.panel
            title="Department details"
            description="Update department naming, code, and active state."
        >
            @include('admin.departments._form', [
                'action' => route('admin.departments.update', $department),
                'method' => 'PATCH',
                'submitLabel' => 'Save Changes',
                'department' => $department,
            ])
        </x-ui.panel>

        <x-ui.panel
            title="Current assignment"
            description="A quick look at the current manager assignment for this department."
        >
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-extrabold text-slate-900">Manager</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $department->manager?->name ?? 'No manager assigned yet' }}</p>
            </div>
        </x-ui.panel>
    </section>
@endsection
