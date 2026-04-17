@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <div class="auth-panel">
        <div class="absolute right-0 top-0 h-36 w-36 rounded-full bg-orange-100 blur-3xl"></div>

        <div class="relative space-y-8">
            <div class="space-y-4">
                <p class="page-eyebrow">Workspace Access</p>
                <h2 class="page-title">Sign in to continue.</h2>
                <p class="page-copy">
                    Use your email address and password to access the asset request workspace.
                </p>
            </div>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf

                <x-auth.input
                    name="email"
                    type="email"
                    label="Email address"
                    placeholder="name@office.test"
                    autocomplete="username"
                />

                <x-auth.input
                    name="password"
                    type="password"
                    label="Password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                />

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-600">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                            class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-200"
                        >
                        Remember this device
                    </label>

                    <a href="{{ route('password.request') }}" class="text-sm font-bold text-orange-600 transition hover:text-orange-700">
                        Forgot your password?
                    </a>
                </div>

                <button type="submit" class="primary-button w-full">
                    Sign In
                </button>
            </form>

            <div class="shell-card-muted space-y-4">
                <div>
                    <p class="text-sm font-extrabold text-slate-900">Available accounts</p>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Current local accounts use the password <span class="font-extrabold text-slate-700">password</span>.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="min-w-0 rounded-3xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-orange-500">Admin</p>
                        <p class="mt-2 break-all text-xs font-bold leading-5 text-slate-900 sm:text-sm">admin@office.test</p>
                    </div>
                    <div class="min-w-0 rounded-3xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-orange-500">Manager</p>
                        <p class="mt-2 break-all text-xs font-bold leading-5 text-slate-900 sm:text-sm">manager.it@office.test</p>
                    </div>
                    <div class="min-w-0 rounded-3xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-orange-500">Staff</p>
                        <p class="mt-2 break-all text-xs font-bold leading-5 text-slate-900 sm:text-sm">staff1@office.test</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
