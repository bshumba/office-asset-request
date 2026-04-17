@php
    $pageEyebrow = trim($__env->yieldContent('page-eyebrow', 'Workspace'));
    $pageTitle = trim($__env->yieldContent('page-title', 'Dashboard'));
    $pageDescription = trim($__env->yieldContent('page-description', 'Review your current dashboard state.'));
    $user = auth()->user();
    $roleName = $user?->getRoleNames()->first() ?? 'User';
    $unreadNotifications = $user?->notificationsLog()->whereNull('read_at')->count() ?? 0;
@endphp

<header class="border-b border-slate-200/80 bg-white/75 backdrop-blur">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-5 px-4 py-5 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden" data-sidebar-open aria-label="Open sidebar">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 5.75A.75.75 0 013.75 5h12.5a.75.75 0 010 1.5H3.75A.75.75 0 013 5.75zm0 4.25a.75.75 0 01.75-.75h12.5a.75.75 0 010 1.5H3.75A.75.75 0 013 10zm.75 3.5a.75.75 0 000 1.5h12.5a.75.75 0 000-1.5H3.75z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div>
                    <p class="page-eyebrow">{{ $pageEyebrow }}</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ $pageTitle }}</h1>
                </div>
            </div>

            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('notifications.index') }}" class="relative inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-slate-700 shadow-sm">
                    Notifications
                    @if ($unreadNotifications > 0)
                        <span class="ml-3 inline-flex min-w-7 items-center justify-center rounded-full bg-orange-500 px-2 py-1 text-[11px] font-black text-white">
                            {{ $unreadNotifications }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('profile.edit') }}" class="secondary-button h-11 px-4">
                    Settings
                </a>

                <div class="flex items-center gap-3 rounded-[24px] border border-slate-200 bg-white px-4 py-2 shadow-sm">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-sm font-extrabold text-white">
                        {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">{{ $user?->name }}</p>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">{{ $roleName }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="secondary-button px-4 py-2 text-xs">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <p class="page-copy max-w-3xl">{{ $pageDescription }}</p>
    </div>
</header>
