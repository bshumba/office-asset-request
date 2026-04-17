<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>403 | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,#fff7ed_0%,#f8fafc_42%,#e2e8f0_100%)] text-slate-900">
        <main class="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-16">
            <div class="grid w-full gap-8 rounded-[36px] border border-white/70 bg-white/80 p-8 shadow-2xl backdrop-blur md:grid-cols-[0.9fr_1.1fr] md:p-12">
                <div class="rounded-[30px] bg-slate-950 p-8 text-white">
                    <p class="text-xs font-extrabold uppercase tracking-[0.32em] text-orange-300">Access Restricted</p>
                    <p class="mt-6 text-6xl font-black tracking-tight">403</p>
                    <p class="mt-4 text-sm leading-7 text-slate-300">
                        Your account does not currently have access to this area.
                    </p>
                </div>

                <div class="flex flex-col justify-center">
                    <span class="shell-chip w-max">Permission Required</span>
                    <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                        You do not have permission to open this page.
                    </h1>
                    <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                        The request reached the application, but your current role or permission set does not allow this action.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="primary-button">Back to dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="primary-button">Go to login</a>
                        @endauth
                        <a href="{{ url()->previous() }}" class="secondary-button">Go back</a>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
