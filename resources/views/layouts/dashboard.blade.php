<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ trim($__env->yieldContent('title', 'Dashboard')).' | '.config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden">
        <div class="min-h-screen lg:flex">
            <div data-sidebar-backdrop class="fixed inset-0 z-40 hidden bg-slate-950/55 backdrop-blur-sm lg:hidden"></div>

            @include('partials.app.sidebar')

            <div class="flex min-h-screen flex-1 flex-col">
                @include('partials.app.header')

                <main class="flex-1 px-4 pb-8 pt-6 sm:px-6 lg:px-8">
                    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
