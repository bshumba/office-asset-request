@extends('layouts.dashboard')

@section('title', 'Profile Settings')
@section('page-eyebrow', 'Account')
@section('page-title', 'Profile & Settings')
@section('page-description', 'Review your account details, update profile information, and change your password.')

@section('content')
    <section class="dashboard-hero">
        <div class="max-w-3xl space-y-4">
            <span class="shell-chip">Personal Settings</span>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                Manage your account details from one place.
            </h2>
            <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                Keep your profile accurate and update your password whenever you need to.
            </p>
        </div>
    </section>

    @if (session('status'))
        <x-ui.alert>
            {{ session('status') }}
        </x-ui.alert>
    @endif

    <section class="grid gap-6 xl:grid-cols-[1fr_0.42fr]">
        <x-ui.panel
            title="Profile details"
            description="Update the account name and email shown across the application."
        >
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="text-sm font-extrabold text-slate-700">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    @error('name')
                        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="text-sm font-extrabold text-slate-700">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    @error('email')
                        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="primary-button">Save Profile</button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel
            title="Account summary"
            description="Current role and department assignment for this account."
        >
            <div class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Role</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $user->getRoleNames()->join(', ') ?: 'No role' }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Department</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $user->department?->name ?? 'No department assigned' }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm font-extrabold text-slate-900">Status</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ str($user->status->value)->headline() }}</p>
                </div>
            </div>
        </x-ui.panel>
    </section>

    <x-ui.panel
        title="Security"
        description="Change your password using your current credentials."
    >
        <form method="POST" action="{{ route('profile.password.update') }}" class="grid gap-6 lg:grid-cols-3">
            @csrf
            @method('PATCH')

            <div>
                <label for="current_password" class="text-sm font-extrabold text-slate-700">Current password</label>
                <input id="current_password" name="current_password" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                @error('current_password')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="text-sm font-extrabold text-slate-700">New password</label>
                <input id="password" name="password" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                @error('password')
                    <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="text-sm font-extrabold text-slate-700">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            </div>

            <div class="lg:col-span-3 flex flex-wrap gap-3">
                <button type="submit" class="primary-button">Update Password</button>
            </div>
        </form>
    </x-ui.panel>
@endsection
