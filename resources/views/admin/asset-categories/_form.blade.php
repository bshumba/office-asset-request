@props([
    'action',
    'method' => 'POST',
    'submitLabel',
    'assetCategory' => null,
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="text-sm font-extrabold text-slate-700">Category name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $assetCategory?->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('name')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="text-sm font-extrabold text-slate-700">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('description', $assetCategory?->description) }}</textarea>
        @error('description')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400" @checked(old('is_active', $assetCategory?->is_active ?? true))>
        Active category
    </label>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="primary-button">{{ $submitLabel }}</button>
        <a href="{{ route('admin.asset-categories.index') }}" class="secondary-button">Cancel</a>
    </div>
</form>
