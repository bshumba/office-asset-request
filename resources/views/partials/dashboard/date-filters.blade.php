<section class="rounded-[28px] border border-slate-200 bg-white/80 p-5 shadow-sm backdrop-blur">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div class="space-y-2">
            <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-orange-600">Activity Window</p>
            <p class="text-sm leading-6 text-slate-600">
                Filter dashboard metrics by the period when records were created or issued.
            </p>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                Current range: {{ $rangeLabel }}
            </p>
        </div>

        <form method="GET" action="{{ route($routeName) }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto]">
            <div>
                <label for="from" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">From</label>
                <input
                    id="from"
                    name="from"
                    type="date"
                    value="{{ $filters['from'] ?? '' }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label for="to" class="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">To</label>
                <input
                    id="to"
                    name="to"
                    type="date"
                    value="{{ $filters['to'] ?? '' }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div class="flex items-end">
                <button type="submit" class="primary-button w-full sm:w-auto">Apply</button>
            </div>

            <div class="flex items-end">
                <a href="{{ route($routeName) }}" class="secondary-button w-full justify-center sm:w-auto">Reset</a>
            </div>
        </form>
    </div>
</section>
