@extends('layouts.dashboard')

@section('title', 'Permissions')
@section('page-eyebrow', 'Access Control')
@section('page-title', 'Permission Management')
@section('page-description', 'Create new permissions and optionally assign them to roles immediately from the same workspace.')

@section('content')
    <section class="dashboard-hero">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-4">
                <span class="shell-chip">Permission Builder</span>
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
                    Create permissions and assign them to roles from the same workspace.
                </h2>
                <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Keep permission naming consistent and attach new capabilities to roles immediately.
                </p>
            </div>

            <a href="{{ route('admin.roles.index') }}" class="secondary-button">
                Back to Roles
            </a>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
        <x-ui.panel
            title="Create permission"
            description="Use clear action-oriented names such as `tickets.resolve` or `assets.transfer` so future policy checks stay readable."
        >
            <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="text-sm font-extrabold text-slate-700">Permission name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        placeholder="example.permission-name"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                    @error('name')
                        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <p class="text-sm font-extrabold text-slate-700">Assign to roles now</p>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        @foreach ($roles as $role)
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $role->id }}"
                                    @checked(in_array($role->id, old('roles', []), true))
                                    class="mt-1 rounded border-slate-300 text-orange-500 focus:ring-orange-400"
                                >
                                <span class="font-bold text-slate-900">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="primary-button">Create Permission</button>
                    <a href="{{ route('admin.roles.index') }}" class="secondary-button">Cancel</a>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel
            title="Existing permissions"
            description="This list makes it easy to inspect naming consistency and see which roles currently own each permission."
        >
            <div class="space-y-4">
                @foreach ($permissions as $permission)
                    <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
                        <div class="space-y-3">
                            <p class="text-lg font-extrabold text-slate-950">{{ $permission->name }}</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse ($permission->roles as $role)
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-600">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-500">Not assigned to any roles yet.</span>
                                @endforelse
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $permissions->links() }}
            </div>
        </x-ui.panel>
    </section>
@endsection
