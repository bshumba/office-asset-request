@props([
    'action',
    'method' => 'POST',
    'submitLabel',
    'managedUser' => null,
    'departments',
    'roles',
    'statuses',
])

<form method="POST" action="{{ $action }}" class="grid gap-6 lg:grid-cols-2">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="text-sm font-extrabold text-slate-700">Full name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $managedUser?->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('name')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="text-sm font-extrabold text-slate-700">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email', $managedUser?->email) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @error('email')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="role" class="text-sm font-extrabold text-slate-700">Role</label>
        <select id="role" name="role" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            <option value="">Select a role</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $managedUser?->getRoleNames()->first()) === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @error('role')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="department_id" class="text-sm font-extrabold text-slate-700">Department</label>
        <select id="department_id" name="department_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            <option value="">Select a department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((string) old('department_id', $managedUser?->department_id) === (string) $department->id)>
                    {{ $department->name }}{{ $department->is_active ? '' : ' (Inactive)' }}
                </option>
            @endforeach
        </select>
        @error('department_id')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="text-sm font-extrabold text-slate-700">Status</label>
        <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $managedUser?->status->value ?? \App\Enums\UserStatusEnum::ACTIVE->value) === $status->value)>{{ str($status->value)->headline() }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="text-sm font-extrabold text-slate-700">
            {{ $managedUser ? 'New password' : 'Password' }}
        </label>
        <input id="password" name="password" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        @if ($managedUser)
            <p class="mt-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Leave blank to keep the current password.</p>
        @endif
        @error('password')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="text-sm font-extrabold text-slate-700">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
    </div>

    <div class="lg:col-span-2">
        <label for="notes" class="text-sm font-extrabold text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('notes', $managedUser?->notes) }}</textarea>
        @error('notes')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="lg:col-span-2 flex flex-wrap gap-3">
        <button type="submit" class="primary-button">{{ $submitLabel }}</button>
        <a href="{{ route('admin.users.index') }}" class="secondary-button">Cancel</a>
    </div>
</form>
