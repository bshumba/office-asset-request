@extends('layouts.dashboard')

@section('title', 'Assigned Asset Record')
@section('page-eyebrow', 'Staff Assets')
@section('page-title', $assetIssue->asset?->name ?? 'Assigned Asset Record')
@section('page-description', 'Review your issued asset details, outstanding quantity, and any recorded return history.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Issued Record</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Review the current issue record for this assigned asset.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Track issued quantity, outstanding quantity, and any recorded returns from one page.
                </p>
            </div>

            <a href="{{ route('staff.assigned-assets.index') }}" class="secondary-button">
                Back to Assigned Assets
            </a>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <x-ui.panel
            title="Issue snapshot"
            description="This is the inventory record created after your request was fully approved and issued."
        >
            <div class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Asset</p>
                    <p class="mt-2 text-lg font-extrabold text-slate-950">{{ $assetIssue->asset?->name ?? 'Missing asset' }}</p>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Request</p>
                        <p class="mt-2 text-base font-extrabold text-slate-950">{{ $assetIssue->assetRequest?->request_number ?? 'No request' }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Status</p>
                        <p class="mt-2 text-base font-extrabold text-slate-950">{{ str($assetIssue->status->value)->headline() }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Issued Quantity</p>
                        <p class="mt-2 text-base font-extrabold text-slate-950">{{ $assetIssue->quantity_issued }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-400">Outstanding Quantity</p>
                        <p class="mt-2 text-base font-extrabold text-slate-950">{{ $assetIssue->outstandingQuantity() }}</p>
                    </div>
                </div>
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Return history"
            description="Returns are still recorded by admins, but staff can now see the history and current outcome from their side."
        >
            @if ($assetIssue->returns->isEmpty())
                <div class="rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                    <p class="text-lg font-extrabold text-slate-900">No return activity yet</p>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Once a return is processed, the history will appear here.
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($assetIssue->returns as $assetReturn)
                        <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="text-sm font-extrabold text-slate-900">
                                        {{ $assetReturn->quantity_returned }} item(s) returned in {{ str($assetReturn->condition_on_return->value)->headline() }} condition
                                    </p>
                                    <p class="mt-2 text-sm text-slate-500">
                                        Received by {{ $assetReturn->receivedByUser?->name ?? 'Unknown user' }} on {{ $assetReturn->returned_at?->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <p class="text-sm text-slate-500">{{ $assetReturn->remarks ?: 'No remarks' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-ui.panel>
    </section>
@endsection
