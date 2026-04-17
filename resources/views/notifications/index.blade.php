@extends('layouts.dashboard')

@section('title', 'Notifications')
@section('page-eyebrow', 'Notification Center')
@section('page-title', 'Notifications')
@section('page-description', 'Review account activity and open related records directly from one inbox.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Activity Feed</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Stay on top of requests, approvals, issues, and returns.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Open any notification to jump straight to the related record and clear it from your unread list.
                </p>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="primary-button">Mark All Read</button>
                </form>
            @endif
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-5 md:grid-cols-3">
        <x-ui.stat-card label="Unread" :value="$unreadCount" meta="Notifications that still need your attention." />
        <x-ui.stat-card label="Total" :value="$notifications->total()" meta="All notifications in your personal activity feed." tone="slate" />
        <x-ui.stat-card label="Current Page" :value="$notifications->count()" meta="Messages loaded on this page right now." tone="emerald" />
    </section>

    <x-ui.panel
        title="Recent notifications"
        description="Only notifications for your account appear here."
    >
        @if ($notifications->isEmpty())
            <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                <p class="text-lg font-extrabold text-slate-900">No notifications yet</p>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Workflow activity will start appearing here as soon as requests and approvals move.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($notifications as $notification)
                    <article class="rounded-[28px] border {{ $notification->read_at ? 'border-slate-200 bg-slate-50' : 'border-orange-200 bg-orange-50/60' }} p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="text-lg font-extrabold text-slate-950">{{ $notification->title }}</p>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $notification->read_at ? 'border-slate-200 bg-white text-slate-600' : 'border-orange-200 bg-white text-orange-600' }}">
                                        {{ $notification->read_at ? 'Read' : 'Unread' }}
                                    </span>
                                </div>
                                <p class="text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                                <div class="flex flex-wrap gap-4 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">
                                    <span>{{ str($notification->type)->headline() }}</span>
                                    <span>{{ $notification->created_at?->format('d M Y, H:i') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('notifications.open', $notification->id) }}" class="secondary-button">
                                Open
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </x-ui.panel>
@endsection
