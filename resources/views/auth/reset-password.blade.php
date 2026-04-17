@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <div class="auth-panel">
        <div class="relative space-y-8">
            <div class="space-y-4">
                <p class="page-eyebrow">Reset Access</p>
                <h2 class="page-title">Choose a new password.</h2>
                <p class="page-copy">
                    After this is saved, the user can sign back in and the role-based dashboard redirect flow will continue to work normally.
                </p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <x-auth.input
                    name="email"
                    type="email"
                    label="Email address"
                    :value="$request->email"
                    autocomplete="username"
                />

                <x-auth.input
                    name="password"
                    type="password"
                    label="New password"
                    placeholder="Create a strong password"
                    autocomplete="new-password"
                />

                <x-auth.input
                    name="password_confirmation"
                    type="password"
                    label="Confirm password"
                    placeholder="Repeat your password"
                    autocomplete="new-password"
                />

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit" class="primary-button flex-1">
                        Reset Password
                    </button>
                    <a href="{{ route('login') }}" class="secondary-button flex-1">
                        Back To Login
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
