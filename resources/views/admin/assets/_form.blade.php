@props([
    'action',
    'method' => 'POST',
    'submitLabel',
    'asset' => null,
    'categories',
    'departments',
    'statuses',
    'unitTypes',
])

<form method="POST" action="{{ $action }}" class="grid gap-6 lg:grid-cols-2">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="text-sm font-extrabold text-slate-700">Asset name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $asset?->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('name')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="asset_code" class="text-sm font-extrabold text-slate-700">Asset code</label>
        <input id="asset_code" name="asset_code" type="text" value="{{ old('asset_code', $asset?->asset_code) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('asset_code')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="asset_category_id" class="text-sm font-extrabold text-slate-700">Category</label>
        <select id="asset_category_id" name="asset_category_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            <option value="">Select a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('asset_category_id', $asset?->asset_category_id) === (string) $category->id)>
                    {{ $category->name }}{{ $category->is_active ? '' : ' (Inactive)' }}
                </option>
            @endforeach
        </select>
        @error('asset_category_id')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="department_id" class="text-sm font-extrabold text-slate-700">Department</label>
        <select id="department_id" name="department_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            <option value="">Select a department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((string) old('department_id', $asset?->department_id) === (string) $department->id)>
                    {{ $department->name }}{{ $department->is_active ? '' : ' (Inactive)' }}
                </option>
            @endforeach
        </select>
        @error('department_id')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="unit_type" class="text-sm font-extrabold text-slate-700">Unit type</label>
        <select id="unit_type" name="unit_type" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            @foreach ($unitTypes as $unitType)
                <option value="{{ $unitType->value }}" @selected(old('unit_type', $asset?->unit_type?->value ?? \App\Enums\AssetUnitTypeEnum::PIECE->value) === $unitType->value)>
                    {{ str($unitType->value)->headline() }}
                </option>
            @endforeach
        </select>
        @error('unit_type')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="text-sm font-extrabold text-slate-700">Status</label>
        <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $asset?->status?->value ?? \App\Enums\AssetStatusEnum::ACTIVE->value) === $status->value)>
                    {{ str($status->value)->headline() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="quantity_total" class="text-sm font-extrabold text-slate-700">Quantity total</label>
        <input id="quantity_total" name="quantity_total" type="number" min="1" value="{{ old('quantity_total', $asset?->quantity_total) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('quantity_total')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="quantity_available" class="text-sm font-extrabold text-slate-700">Quantity available</label>
        <input id="quantity_available" name="quantity_available" type="number" min="0" value="{{ old('quantity_available', $asset?->quantity_available) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('quantity_available')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="reorder_level" class="text-sm font-extrabold text-slate-700">Reorder level</label>
        <input id="reorder_level" name="reorder_level" type="number" min="0" value="{{ old('reorder_level', $asset?->reorder_level) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('reorder_level')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="purchase_date" class="text-sm font-extrabold text-slate-700">Purchase date</label>
        <input id="purchase_date" name="purchase_date" type="date" value="{{ old('purchase_date', $asset?->purchase_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('purchase_date')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="brand" class="text-sm font-extrabold text-slate-700">Brand</label>
        <input id="brand" name="brand" type="text" value="{{ old('brand', $asset?->brand) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('brand')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="model" class="text-sm font-extrabold text-slate-700">Model</label>
        <input id="model" name="model" type="text" value="{{ old('model', $asset?->model) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('model')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="serial_number" class="text-sm font-extrabold text-slate-700">Serial number</label>
        <input id="serial_number" name="serial_number" type="text" value="{{ old('serial_number', $asset?->serial_number) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('serial_number')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
        <input type="checkbox" name="track_serial" value="1" class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400" @checked(old('track_serial', $asset?->track_serial ?? false))>
        Track serial numbers
    </label>

    <div class="lg:col-span-2">
        <label for="description" class="text-sm font-extrabold text-slate-700">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('description', $asset?->description) }}</textarea>
        @error('description')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="lg:col-span-2">
        <label for="notes" class="text-sm font-extrabold text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('notes', $asset?->notes) }}</textarea>
        @error('notes')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="lg:col-span-2 flex flex-wrap gap-3">
        <button type="submit" class="primary-button">{{ $submitLabel }}</button>
        <a href="{{ route('admin.assets.index') }}" class="secondary-button">Cancel</a>
    </div>
</form>
