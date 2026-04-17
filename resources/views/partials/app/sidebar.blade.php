@php
    $user = auth()->user();

    if ($user?->isAdmin()) {
        $roleLabel = 'Super Admin';
        $roleRoute = 'admin.dashboard';
        $items = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard'],
            ['label' => 'Team', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*'],
            ['label' => 'Access', 'route' => 'admin.roles.index', 'pattern' => ['admin.roles.*', 'admin.permissions.*']],
            ['label' => 'Requests', 'route' => 'admin.requests.index', 'pattern' => 'admin.requests.*'],
            ['label' => 'Issues', 'route' => 'admin.issues.index', 'pattern' => 'admin.issues.*'],
            ['label' => 'Stock', 'route' => 'admin.adjustments.index', 'pattern' => 'admin.adjustments.*'],
            ['label' => 'Reports', 'route' => 'admin.reports.stock', 'pattern' => 'admin.reports.*'],
        ];
    } elseif ($user?->isManager()) {
        $roleLabel = 'Department Manager';
        $roleRoute = 'manager.dashboard';
        $items = [
            ['label' => 'Dashboard', 'route' => 'manager.dashboard', 'pattern' => 'manager.dashboard'],
            ['label' => 'Approvals', 'route' => 'manager.requests.index', 'pattern' => 'manager.requests.*'],
            ['label' => 'Reports', 'route' => 'manager.reports.stock', 'pattern' => 'manager.reports.*'],
            ['label' => 'Low Stock', 'route' => 'manager.reports.low-stock', 'pattern' => 'manager.reports.low-stock'],
        ];
    } else {
        $roleLabel = 'Staff';
        $roleRoute = 'staff.dashboard';
        $items = [
            ['label' => 'Dashboard', 'route' => 'staff.dashboard', 'pattern' => 'staff.dashboard'],
            ['label' => 'My Requests', 'route' => 'staff.requests.index', 'pattern' => 'staff.requests.*'],
            ['label' => 'Assigned Assets', 'route' => 'staff.assigned-assets.index', 'pattern' => 'staff.assigned-assets.*'],
            ['label' => 'Notifications', 'route' => 'notifications.index', 'pattern' => 'notifications.*'],
        ];
    }
@endphp

<aside data-sidebar-panel class="fixed inset-y-0 left-0 z-50 flex w-[290px] -translate-x-full flex-col bg-[linear-gradient(180deg,#0f172a_0%,#111827_52%,#1e293b_100%)] px-5 py-5 text-white transition duration-300 lg:static lg:translate-x-0">
    <div class="flex items-center justify-between">
        <a href="{{ route($roleRoute) }}">
            <x-app-logo class="[&_p:first-child]:text-slate-300 [&_p:last-child]:text-white" />
        </a>

        <button type="button" class="rounded-2xl border border-white/10 p-2 text-slate-300 lg:hidden" data-sidebar-close aria-label="Close sidebar">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 011.06 0L10 8.94l4.72-4.72a.75.75 0 111.06 1.06L11.06 10l4.72 4.72a.75.75 0 11-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 11-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div class="mt-8 rounded-[30px] border border-white/8 bg-white/5 p-5 backdrop-blur">
        <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-orange-300">Signed In As</p>
        <p class="mt-3 text-xl font-extrabold text-white">{{ $roleLabel }}</p>
        <p class="mt-2 text-sm leading-6 text-slate-300">
            {{ $user?->name }}
            @if ($user?->department)
                · {{ $user->department->name }}
            @endif
        </p>
    </div>

    <nav class="mt-8 flex-1 space-y-2">
        @foreach ($items as $item)
            @if (! empty($item['soon']))
                <span class="nav-item nav-item-disabled">
                    <span>{{ $item['label'] }}</span>
                    <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-orange-300">Soon</span>
                </span>
            @else
                <a
                    href="{{ route($item['route']) }}"
                    class="{{ request()->routeIs($item['pattern']) ? 'nav-item nav-item-active' : 'nav-item' }}"
                >
                    <span>{{ $item['label'] }}</span>
                    <span class="text-xs font-black">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <div class="rounded-[28px] border border-orange-500/20 bg-orange-500/10 p-5">
        <p class="text-sm font-extrabold text-white">Workspace</p>
        <p class="mt-2 text-sm leading-6 text-slate-300">
            Use this menu to move between the modules available to your role.
        </p>
    </div>
</aside>
