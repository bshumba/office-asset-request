@extends('layouts.dashboard')

@section('title', 'My Requests')
@section('page-eyebrow', 'Staff Requests')
@section('page-title', 'My Asset Requests')
@section('page-description', 'Track the requests you have submitted, their current statuses, and the next step in the approval flow.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Request Tracker</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Track your submitted requests from one personal queue.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Review current statuses, open request details, and follow each request through review, approval, and issuing.
                </p>
            </div>

            <a href="{{ route('staff.requests.create') }}" class="primary-button">
                Create Request
            </a>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <x-ui.panel
        title="Request history"
        description="Only requests created from your account appear here."
    >
        @if ($requests->isEmpty())
            <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                <p class="text-lg font-extrabold text-slate-900">No requests yet</p>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Start with your first asset request to see the status timeline and approval workflow take shape.
                </p>
                <div class="mt-6">
                    <a href="{{ route('staff.requests.create') }}" class="primary-button">
                        Create Your First Request
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($requests as $requestItem)
                    @php
                        $statusClasses = match ($requestItem->status) {
                            \App\Enums\AssetRequestStatusEnum::PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
                            \App\Enums\AssetRequestStatusEnum::CANCELLED => 'border-rose-200 bg-rose-50 text-rose-700',
                            \App\Enums\AssetRequestStatusEnum::MANAGER_APPROVED => 'border-sky-200 bg-sky-50 text-sky-700',
                            \App\Enums\AssetRequestStatusEnum::ADMIN_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            default => 'border-slate-200 bg-slate-50 text-slate-700',
                        };
                    @endphp

                    <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="text-lg font-extrabold text-slate-950">{{ $requestItem->request_number }}</p>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $statusClasses }}">
                                        {{ str($requestItem->status->value)->headline() }}
                                    </span>
                                </div>

                                <p class="text-sm font-bold text-slate-800">{{ $requestItem->asset->name }}</p>
                                <div class="flex flex-wrap gap-5 text-sm text-slate-500">
                                    <span>Qty: {{ $requestItem->quantity_requested }}</span>
                                    <span>Priority: {{ str($requestItem->priority->value)->headline() }}</span>
                                    <span>Needed: {{ $requestItem->needed_by_date?->format('d M Y') ?? 'Not specified' }}</span>
                                    <span>Submitted: {{ $requestItem->created_at->format('d M Y') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('staff.requests.show', $requestItem) }}" class="secondary-button">
                                View Details
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
        @endif
    </x-ui.panel>
@endsection
