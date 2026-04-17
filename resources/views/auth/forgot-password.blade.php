@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="auth-panel">
        <div class="relative space-y-8">
            <div class="space-y-4">
                <p class="page-eyebrow">Recovery</p>
                <h2 class="page-title">Send yourself a reset link.</h2>
                <p class="page-copy">
                    Enter your email address and we will send a password reset link.
                </p>
            </div>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <x-auth.input
                    name="email"
                    type="email"
                    label="Email address"
                    placeholder="name@office.test"
                    autocomplete="username"
                />

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit" class="primary-button flex-1">
                        Email Reset Link
                    </button>
                    <a href="{{ route('login') }}" class="secondary-button flex-1">
                        Back To Login
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
