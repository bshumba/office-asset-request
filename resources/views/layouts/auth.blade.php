<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ trim($__env->yieldContent('title', 'Sign In')).' | '.config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden">
        <div class="relative min-h-screen">
            <div class="glow-orb absolute left-0 top-16 h-64 w-64 rounded-full bg-orange-300"></div>
            <div class="glow-orb absolute bottom-0 right-0 h-72 w-72 rounded-full bg-sky-200"></div>

            <div class="relative mx-auto grid min-h-screen max-w-7xl items-center gap-10 px-6 py-10 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
                <section class="relative overflow-hidden rounded-[36px] bg-[linear-gradient(145deg,#0f172a_0%,#111827_40%,#1e293b_100%)] p-8 text-white shadow-[0_30px_80px_-45px_rgba(15,23,42,0.9)] sm:p-10 lg:min-h-[760px] lg:p-12">
                    <div class="grid-pattern absolute inset-0 opacity-40"></div>
                    <div class="absolute -right-12 top-16 h-48 w-48 rounded-full bg-orange-500/20 blur-3xl"></div>
                    <div class="absolute bottom-12 left-0 h-44 w-44 rounded-full bg-sky-400/15 blur-3xl"></div>

                    <div class="relative flex h-full flex-col justify-between gap-10">
                        <div class="space-y-8">
                            <x-app-logo class="[&_p:first-child]:text-slate-300 [&_p:last-child]:text-white" />

                            <div class="max-w-xl space-y-5">
                                <p class="page-eyebrow text-orange-300">Operations Hub</p>
                                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl">
                                    Asset requests, approvals, and inventory flow in one clean workspace.
                                </h1>
                                <p class="max-w-lg text-base leading-8 text-slate-300 sm:text-lg">
                                    Manage request intake, approvals, issued assets, stock movement, and reporting from a single connected platform.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-sm font-bold text-white">Approvals</p>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Track requests from submission through manager and admin review.</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-sm font-bold text-white">Inventory</p>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Monitor issues, returns, stock adjustments, and low-stock pressure.</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-sm font-bold text-white">Reporting</p>
                                <p class="mt-2 text-sm leading-6 text-slate-300">Review operational summaries across assets, requests, and departments.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <main class="relative">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
