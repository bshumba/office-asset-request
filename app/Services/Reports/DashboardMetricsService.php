<?php

namespace App\Services\Reports;

use App\Enums\AssetIssueStatusEnum;
use App\Enums\AssetRequestStatusEnum;
use App\Enums\AssetStatusEnum;
use App\Models\Asset;
use App\Models\AssetIssue;
use App\Models\AssetRequest;
use App\Models\Department;
use App\Models\User;

class DashboardMetricsService
{
    /**
     * Get the admin dashboard data.
     *
     * @return array{
     *     stats: array<int, array{label: string, value: int, meta: string}>,
     *     health: array<int, array{label: string, value: int}>,
     *     requestPipeline: array<int, array{label: string, value: int, tone: string}>,
     *     departmentActivity: array<int, array{label: string, requests: int, issues: int, users: int}>
     * }
     */
    public function forAdmin(): array
    {
        return [
            'stats' => [
                [
                    'label' => 'Active Departments',
                    'value' => Department::query()->where('is_active', true)->count(),
                    'meta' => 'Organization units currently using the system',
                ],
                [
                    'label' => 'Team Members',
                    'value' => User::query()->count(),
                    'meta' => 'All active accounts available across the platform',
                ],
                [
                    'label' => 'Tracked Assets',
                    'value' => Asset::query()->count(),
                    'meta' => 'All inventory records currently managed in the system',
                ],
                [
                    'label' => 'Pending Requests',
                    'value' => AssetRequest::query()
                        ->where('status', AssetRequestStatusEnum::PENDING)
                        ->count(),
                    'meta' => 'Requests still waiting for the first approval touchpoint',
                ],
            ],
            'health' => [
                [
                    'label' => 'Active assets',
                    'value' => Asset::query()->where('status', AssetStatusEnum::ACTIVE)->count(),
                ],
                [
                    'label' => 'Low-stock assets',
                    'value' => Asset::query()->whereColumn('quantity_available', '<=', 'reorder_level')->count(),
                ],
                [
                    'label' => 'Issued items',
                    'value' => AssetIssue::query()->where('status', AssetIssueStatusEnum::ISSUED)->count(),
                ],
            ],
            'requestPipeline' => [
                [
                    'label' => 'Pending',
                    'value' => AssetRequest::query()->where('status', AssetRequestStatusEnum::PENDING)->count(),
                    'tone' => 'bg-amber-400',
                ],
                [
                    'label' => 'Manager Approved',
                    'value' => AssetRequest::query()->where('status', AssetRequestStatusEnum::MANAGER_APPROVED)->count(),
                    'tone' => 'bg-sky-400',
                ],
                [
                    'label' => 'Admin Approved',
                    'value' => AssetRequest::query()->where('status', AssetRequestStatusEnum::ADMIN_APPROVED)->count(),
                    'tone' => 'bg-emerald-400',
                ],
                [
                    'label' => 'Issued',
                    'value' => AssetRequest::query()->where('status', AssetRequestStatusEnum::ISSUED)->count(),
                    'tone' => 'bg-violet-400',
                ],
                [
                    'label' => 'Returned',
                    'value' => AssetRequest::query()->where('status', AssetRequestStatusEnum::RETURNED)->count(),
                    'tone' => 'bg-teal-400',
                ],
                [
                    'label' => 'Rejected',
                    'value' => AssetRequest::query()->where('status', AssetRequestStatusEnum::REJECTED)->count(),
                    'tone' => 'bg-rose-400',
                ],
            ],
            'departmentActivity' => Department::query()
                ->where('is_active', true)
                ->withCount([
                    'assetIssues as issues_count',
                    'assetRequests as requests_count',
                    'users as users_count',
                ])
                ->orderByDesc('requests_count')
                ->orderBy('name')
                ->get()
                ->map(fn (Department $department): array => [
                    'label' => $department->name,
                    'requests' => (int) $department->requests_count,
                    'issues' => (int) $department->issues_count,
                    'users' => (int) $department->users_count,
                ])
                ->all(),
        ];
    }

    /**
     * Get the manager dashboard data.
     *
     * @return array{
     *     departmentName: string,
     *     stats: array<int, array{label: string, value: int, meta: string}>,
     *     workflow: array<int, array{label: string, value: int}>
     * }
     */
    public function forManager(User $manager): array
    {
        $departmentId = $manager->department_id;

        return [
            'departmentName' => $manager->department?->name ?? 'Unassigned Department',
            'stats' => [
                [
                    'label' => 'Department Staff',
                    'value' => User::query()->where('department_id', $departmentId)->count(),
                    'meta' => 'People this manager will review requests for',
                ],
                [
                    'label' => 'Department Assets',
                    'value' => Asset::query()->where('department_id', $departmentId)->count(),
                    'meta' => 'Inventory currently scoped to this department',
                ],
                [
                    'label' => 'Pending Review',
                    'value' => AssetRequest::query()
                        ->where('department_id', $departmentId)
                        ->where('status', AssetRequestStatusEnum::PENDING)
                        ->count(),
                    'meta' => 'Requests waiting for a manager decision',
                ],
                [
                    'label' => 'Issued Items',
                    'value' => AssetIssue::query()
                        ->where('department_id', $departmentId)
                        ->where('status', AssetIssueStatusEnum::ISSUED)
                        ->count(),
                    'meta' => 'Assets currently checked out by the department',
                ],
            ],
            'workflow' => [
                [
                    'label' => 'Manager approved',
                    'value' => AssetRequest::query()
                        ->where('department_id', $departmentId)
                        ->where('status', AssetRequestStatusEnum::MANAGER_APPROVED)
                        ->count(),
                ],
                [
                    'label' => 'Admin approved',
                    'value' => AssetRequest::query()
                        ->where('department_id', $departmentId)
                        ->where('status', AssetRequestStatusEnum::ADMIN_APPROVED)
                        ->count(),
                ],
                [
                    'label' => 'Low-stock items',
                    'value' => Asset::query()
                        ->where('department_id', $departmentId)
                        ->whereColumn('quantity_available', '<=', 'reorder_level')
                        ->count(),
                ],
            ],
        ];
    }

    /**
     * Get the staff dashboard data.
     *
     * @return array{
     *     departmentName: string,
     *     stats: array<int, array{label: string, value: int, meta: string}>,
     *     requestBreakdown: array<int, array{label: string, value: int}>
     * }
     */
    public function forStaff(User $staff): array
    {
        $departmentId = $staff->department_id;

        return [
            'departmentName' => $staff->department?->name ?? 'Unassigned Department',
            'stats' => [
                [
                    'label' => 'My Requests',
                    'value' => AssetRequest::query()->where('user_id', $staff->id)->count(),
                    'meta' => 'Everything this staff member has requested',
                ],
                [
                    'label' => 'Awaiting Decision',
                    'value' => AssetRequest::query()
                        ->where('user_id', $staff->id)
                        ->whereIn('status', [
                            AssetRequestStatusEnum::PENDING,
                            AssetRequestStatusEnum::MANAGER_APPROVED,
                        ])->count(),
                    'meta' => 'Requests still moving through approval',
                ],
                [
                    'label' => 'Issued To Me',
                    'value' => AssetIssue::query()
                        ->where('issued_to_user_id', $staff->id)
                        ->where('status', AssetIssueStatusEnum::ISSUED)
                        ->count(),
                    'meta' => 'Assets currently assigned to this account',
                ],
                [
                    'label' => 'Dept Inventory',
                    'value' => Asset::query()->where('department_id', $departmentId)->count(),
                    'meta' => 'Available catalog that will drive future request forms',
                ],
            ],
            'requestBreakdown' => [
                [
                    'label' => 'Approved',
                    'value' => AssetRequest::query()
                        ->where('user_id', $staff->id)
                        ->where('status', AssetRequestStatusEnum::ADMIN_APPROVED)
                        ->count(),
                ],
                [
                    'label' => 'Issued',
                    'value' => AssetRequest::query()
                        ->where('user_id', $staff->id)
                        ->where('status', AssetRequestStatusEnum::ISSUED)
                        ->count(),
                ],
                [
                    'label' => 'Cancelled',
                    'value' => AssetRequest::query()
                        ->where('user_id', $staff->id)
                        ->where('status', AssetRequestStatusEnum::CANCELLED)
                        ->count(),
                ],
            ],
        ];
    }
}
