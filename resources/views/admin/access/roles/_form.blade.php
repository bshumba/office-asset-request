@php
    $selectedPermissions = collect(old('permissions', isset($role) ? $role->permissions->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="name" class="text-sm font-extrabold text-slate-700">Role name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $role->name ?? '') }}"
            @readonly($isSystemRole ?? false)
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 disabled:cursor-not-allowed disabled:bg-slate-100"
        >
        @error('name')
            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
        @if (($isSystemRole ?? false) === true)
            <p class="mt-2 text-sm text-slate-500">
                This role name is locked because system routing and access rules rely on it.
            </p>
        @endif
    </div>

    <div class="space-y-4">
        <div>
            <p class="text-sm font-extrabold text-slate-700">Permissions</p>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                Select the permissions that should be assigned to this role.
            </p>
        </div>

        @foreach ($permissionGroups as $group => $permissions)
            <section class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">{{ str($group)->headline() }}</p>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ $permissions->count() }} permission(s)</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($permissions as $permission)
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                @checked(in_array($permission->id, $selectedPermissions, true))
                                class="mt-1 rounded border-slate-300 text-orange-500 focus:ring-orange-400"
                            >
                            <span>
                                <span class="block font-bold text-slate-900">{{ $permission->name }}</span>
                                <span class="mt-1 block text-xs uppercase tracking-[0.16em] text-slate-400">{{ str($permission->name)->headline() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach

        @error('permissions')
            <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="primary-button">{{ $submitLabel }}</button>
        <a href="{{ route('admin.roles.index') }}" class="secondary-button">Back to Roles</a>
    </div>
</form>
