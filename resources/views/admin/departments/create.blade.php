@extends('layouts.dashboard')

@section('title', 'Add Department')
@section('page-eyebrow', 'Admin Directory')
@section('page-title', 'Add Department')
@section('page-description', 'Create a department record that can be used by users, assets, and reports.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Department Setup</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Add a department to the organization directory.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Departments help scope users, assets, requests, and reporting.
                </p>
            </div>

            <a href="{{ route('admin.departments.index') }}" class="secondary-button">Back to Departments</a>
        </div>
    </section>

    <x-ui.panel
        title="Department details"
        description="Set the code, name, and availability of the new department."
    >
        @include('admin.departments._form', [
            'action' => route('admin.departments.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Department',
            'department' => null,
        ])
    </x-ui.panel>
@endsection
