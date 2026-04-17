@extends('layouts.dashboard')

@section('title', 'Assigned Assets')
@section('page-eyebrow', 'Staff Assets')
@section('page-title', 'Assigned Assets')
@section('page-description', 'Review the assets currently or previously issued to this account, along with their outstanding return state.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">My Inventory</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Staff can now see the actual assets issued to them, not just request counts.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    This closes the staff-side loop: request something, watch it move through approvals, and then confirm the issued record from a personal workspace.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('staff.requests.index') }}" class="secondary-button">My Requests</a>
                <a href="{{ route('notifications.index') }}" class="secondary-button">Notifications</a>
            </div>
        </div>
    </section>

    <x-ui.panel
        title="Assigned records"
        description="Only issue records belonging to the authenticated staff user appear here."
    >
        @if ($issues->isEmpty())
            <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                <p class="text-lg font-extrabold text-slate-900">No assets assigned yet</p>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Once an approved request is issued to you, it will appear here.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($issues as $issue)
                    <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="text-lg font-extrabold text-slate-950">{{ $issue->asset?->name ?? 'Missing asset' }}</p>
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-slate-700">
                                        {{ str($issue->status->value)->headline() }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-5 text-sm text-slate-500">
                                    <span>Request: {{ $issue->assetRequest?->request_number ?? 'No request' }}</span>
                                    <span>Issued qty: {{ $issue->quantity_issued }}</span>
                                    <span>Outstanding: {{ $issue->outstandingQuantity() }}</span>
                                </div>
                            </div>

                            <a href="{{ route('staff.assigned-assets.show', $issue) }}" class="secondary-button">
                                Open Record
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
