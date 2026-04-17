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
                    Create the people who will actually exercise your role and permission rules.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    New accounts created here immediately become usable inside the workflow, which makes this a great place to learn
                    how admin-side setup supports the rest of the system.
                </p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="secondary-button">
                Back to Team
            </a>
        </div>
    </section>

    <x-ui.panel
        title="Account details"
        description="Validation lives in a Form Request, while the actual account creation and role assignment live in a dedicated service."
    >
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-6 lg:grid-cols-2">
            @csrf

            <div>
                <label for="name" class="text-sm font-extrabold text-slate-700">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                @error('name')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="text-sm font-extrabold text-slate-700">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                @error('email')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="text-sm font-extrabold text-slate-700">Role</label>
                <select id="role" name="role" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    <option value="">Select a role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
                @error('role')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="department_id" class="text-sm font-extrabold text-slate-700">Department</label>
                <select id="department_id" name="department_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    <option value="">Select a department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="text-sm font-extrabold text-slate-700">Status</label>
                <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', \App\Enums\UserStatusEnum::ACTIVE->value) === $status->value)>{{ str($status->value)->headline() }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="text-sm font-extrabold text-slate-700">Password</label>
                <input id="password" name="password" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                @error('password')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="text-sm font-extrabold text-slate-700">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            </div>

            <div class="lg:col-span-2">
                <label for="notes" class="text-sm font-extrabold text-slate-700">Notes</label>
                <textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="primary-button">Create account</button>
                <a href="{{ route('admin.users.index') }}" class="secondary-button">Cancel</a>
            </div>
        </form>
    </x-ui.panel>
@endsection
