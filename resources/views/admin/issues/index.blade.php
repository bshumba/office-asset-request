@extends('layouts.dashboard')

@section('title', 'Issued Assets')
@section('page-eyebrow', 'Admin Issues')
@section('page-title', 'Issued Assets')
@section('page-description', 'Track assets that have already been issued, monitor their current status, and open individual issue records for return processing.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Issue Records</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Issued assets become their own tracked records here.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Monitor issued assets, outstanding quantities, and return activity from one operational queue.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.requests.index') }}" class="secondary-button">Request Inbox</a>
                <a href="{{ route('admin.adjustments.index') }}" class="secondary-button">Stock Adjustments</a>
            </div>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <x-ui.panel
        title="Issue records"
        description="Open an issue record to see return history, outstanding quantity, and the return form."
    >
        @if ($issues->isEmpty())
            <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                <p class="text-lg font-extrabold text-slate-900">No issued assets yet</p>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Issue an admin-approved request and it will appear here.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($issues as $issue)
                    @php
                        $statusClasses = match ($issue->status) {
                            \App\Enums\AssetIssueStatusEnum::ISSUED => 'border-violet-200 bg-violet-50 text-violet-700',
                            \App\Enums\AssetIssueStatusEnum::PARTIALLY_RETURNED => 'border-amber-200 bg-amber-50 text-amber-700',
                            \App\Enums\AssetIssueStatusEnum::RETURNED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        };
                    @endphp

                    <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="text-lg font-extrabold text-slate-950">{{ $issue->assetRequest->request_number }}</p>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] {{ $statusClasses }}">
                                        {{ str($issue->status->value)->headline() }}
                                    </span>
                                </div>
                                <p class="text-sm font-bold text-slate-800">{{ $issue->issuedToUser->name }} · {{ $issue->asset->name }}</p>
                                <div class="flex flex-wrap gap-5 text-sm text-slate-500">
                                    <span>Issued qty: {{ $issue->quantity_issued }}</span>
                                    <span>Outstanding: {{ $issue->outstandingQuantity() }}</span>
                                    <span>Issued: {{ $issue->issued_at->format('d M Y') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('admin.issues.show', $issue) }}" class="secondary-button">
                                Open Issue
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $issues->links() }}
            </div>
        @endif
    </x-ui.panel>
@endsection
