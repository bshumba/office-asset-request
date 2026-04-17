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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardMetricsService
{
    /**
     * Get the admin dashboard data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     stats: array<int, array{label: string, value: int, meta: string}>,
     *     health: array<int, array{label: string, value: int}>,
     *     requestPipeline: array<int, array{label: string, value: int, tone: string}>,
     *     departmentActivity: array<int, array{label: string, requests: int, issues: int, users: int}>,
     *     filters: array<string, mixed>,
     *     rangeLabel: string
     * }
     */
    public function forAdmin(array $filters = []): array
    {
        $departmentQuery = Department::query()->where('is_active', true);
        $userQuery = User::query();
        $assetQuery = Asset::query();
        $pendingRequestQuery = AssetRequest::query()->where('status', AssetRequestStatusEnum::PENDING);
        $activeAssetQuery = Asset::query()->where('status', AssetStatusEnum::ACTIVE);
        $lowStockAssetQuery = Asset::query()->whereColumn('quantity_available', '<=', 'reorder_level');
        $issuedItemQuery = AssetIssue::query()->where('status', AssetIssueStatusEnum::ISSUED);

        $this->applyDateRange($departmentQuery, $filters);
        $this->applyDateRange($userQuery, $filters);
        $this->applyDateRange($assetQuery, $filters);
        $this->applyDateRange($pendingRequestQuery, $filters);
        $this->applyDateRange($activeAssetQuery, $filters);
        $this->applyDateRange($lowStockAssetQuery, $filters);
        $this->applyDateRange($issuedItemQuery, $filters, 'issued_at');

        return [
            'stats' => [
                [
                    'label' => 'Active Departments',
                    'value' => (clone $departmentQuery)->count(),
                    'meta' => 'Departments in the selected activity window',
                ],
                [
                    'label' => 'Team Members',
                    'value' => (clone $userQuery)->count(),
                    'meta' => 'Accounts created in the selected activity window',
                ],
                [
                    'label' => 'Tracked Assets',
                    'value' => (clone $assetQuery)->count(),
                    'meta' => 'Assets added in the selected activity window',
                ],
                [
                    'label' => 'Pending Requests',
                    'value' => (clone $pendingRequestQuery)->count(),
                    'meta' => 'Pending requests created in the selected activity window',
                ],
            ],
            'health' => [
                [
                    'label' => 'Active assets',
                    'value' => (clone $activeAssetQuery)->count(),
                ],
                [
                    'label' => 'Low-stock assets',
                    'value' => (clone $lowStockAssetQuery)->count(),
                ],
                [
                    'label' => 'Issued items',
                    'value' => (clone $issuedItemQuery)->count(),
                ],
            ],
            'requestPipeline' => [
                [
                    'label' => 'Pending',
                    'value' => $this->requestCountForStatus(AssetRequestStatusEnum::PENDING, $filters),
                    'tone' => 'bg-amber-400',
                ],
                [
                    'label' => 'Manager Approved',
                    'value' => $this->requestCountForStatus(AssetRequestStatusEnum::MANAGER_APPROVED, $filters),
                    'tone' => 'bg-sky-400',
                ],
                [
                    'label' => 'Admin Approved',
                    'value' => $this->requestCountForStatus(AssetRequestStatusEnum::ADMIN_APPROVED, $filters),
                    'tone' => 'bg-emerald-400',
                ],
                [
                    'label' => 'Issued',
                    'value' => $this->requestCountForStatus(AssetRequestStatusEnum::ISSUED, $filters),
                    'tone' => 'bg-violet-400',
                ],
                [
                    'label' => 'Returned',
                    'value' => $this->requestCountForStatus(AssetRequestStatusEnum::RETURNED, $filters),
                    'tone' => 'bg-teal-400',
                ],
                [
                    'label' => 'Rejected',
                    'value' => $this->requestCountForStatus(AssetRequestStatusEnum::REJECTED, $filters),
                    'tone' => 'bg-rose-400',
                ],
            ],
            'departmentActivity' => Department::query()
                ->where('is_active', true)
                ->withCount([
                    'assetIssues as issues_count' => function (Builder $query) use ($filters): void {
                        $this->applyDateRange($query, $filters, 'issued_at');
                    },
                    'assetRequests as requests_count' => function (Builder $query) use ($filters): void {
                        $this->applyDateRange($query, $filters);
                    },
                    'users as users_count' => function (Builder $query) use ($filters): void {
                        $this->applyDateRange($query, $filters);
                    },
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
            'filters' => $filters,
            'rangeLabel' => $this->rangeLabel($filters),
        ];
    }

    /**
     * Get the manager dashboard data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     departmentName: string,
     *     stats: array<int, array{label: string, value: int, meta: string}>,
     *     workflow: array<int, array{label: string, value: int}>,
     *     filters: array<string, mixed>,
     *     rangeLabel: string
     * }
     */
    public function forManager(User $manager, array $filters = []): array
    {
        $departmentId = $manager->department_id;

        $departmentStaffQuery = User::query()->where('department_id', $departmentId);
        $departmentAssetQuery = Asset::query()->where('department_id', $departmentId);
        $pendingReviewQuery = AssetRequest::query()
            ->where('department_id', $departmentId)
            ->where('status', AssetRequestStatusEnum::PENDING);
        $issuedItemQuery = AssetIssue::query()
            ->where('department_id', $departmentId)
            ->where('status', AssetIssueStatusEnum::ISSUED);

        $this->applyDateRange($departmentStaffQuery, $filters);
        $this->applyDateRange($departmentAssetQuery, $filters);
        $this->applyDateRange($pendingReviewQuery, $filters);
        $this->applyDateRange($issuedItemQuery, $filters, 'issued_at');

        return [
            'departmentName' => $manager->department?->name ?? 'Unassigned Department',
            'stats' => [
                [
                    'label' => 'Department Staff',
                    'value' => (clone $departmentStaffQuery)->count(),
                    'meta' => 'Team accounts created in the selected activity window',
                ],
                [
                    'label' => 'Department Assets',
                    'value' => (clone $departmentAssetQuery)->count(),
                    'meta' => 'Department assets added in the selected activity window',
                ],
                [
                    'label' => 'Pending Review',
                    'value' => (clone $pendingReviewQuery)->count(),
                    'meta' => 'Requests waiting for a manager decision',
                ],
                [
                    'label' => 'Issued Items',
                    'value' => (clone $issuedItemQuery)->count(),
                    'meta' => 'Department issues created in the selected activity window',
                ],
            ],
            'workflow' => [
                [
                    'label' => 'Manager approved',
                    'value' => $this->departmentRequestCount($departmentId, AssetRequestStatusEnum::MANAGER_APPROVED, $filters),
                ],
                [
                    'label' => 'Admin approved',
                    'value' => $this->departmentRequestCount($departmentId, AssetRequestStatusEnum::ADMIN_APPROVED, $filters),
                ],
                [
                    'label' => 'Low-stock items',
                    'value' => $this->departmentLowStockCount($departmentId, $filters),
                ],
            ],
            'filters' => $filters,
            'rangeLabel' => $this->rangeLabel($filters),
        ];
    }

    /**
     * Get the staff dashboard data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     departmentName: string,
     *     stats: array<int, array{label: string, value: int, meta: string}>,
     *     requestBreakdown: array<int, array{label: string, value: int}>,
     *     filters: array<string, mixed>,
     *     rangeLabel: string
     * }
     */
    public function forStaff(User $staff, array $filters = []): array
    {
        $departmentId = $staff->department_id;

        $myRequestQuery = AssetRequest::query()->where('user_id', $staff->id);
        $awaitingDecisionQuery = AssetRequest::query()
            ->where('user_id', $staff->id)
            ->whereIn('status', [
                AssetRequestStatusEnum::PENDING,
                AssetRequestStatusEnum::MANAGER_APPROVED,
            ]);
        $issuedToMeQuery = AssetIssue::query()
            ->where('issued_to_user_id', $staff->id)
            ->where('status', AssetIssueStatusEnum::ISSUED);
        $departmentInventoryQuery = Asset::query()->where('department_id', $departmentId);

        $this->applyDateRange($myRequestQuery, $filters);
        $this->applyDateRange($awaitingDecisionQuery, $filters);
        $this->applyDateRange($issuedToMeQuery, $filters, 'issued_at');
        $this->applyDateRange($departmentInventoryQuery, $filters);

        return [
            'departmentName' => $staff->department?->name ?? 'Unassigned Department',
            'stats' => [
                [
                    'label' => 'My Requests',
                    'value' => (clone $myRequestQuery)->count(),
                    'meta' => 'Requests created in the selected activity window',
                ],
                [
                    'label' => 'Awaiting Decision',
                    'value' => (clone $awaitingDecisionQuery)->count(),
                    'meta' => 'Requests still moving through approval',
                ],
                [
                    'label' => 'Issued To Me',
                    'value' => (clone $issuedToMeQuery)->count(),
                    'meta' => 'Issues created in the selected activity window',
                ],
                [
                    'label' => 'Dept Inventory',
                    'value' => (clone $departmentInventoryQuery)->count(),
                    'meta' => 'Department assets added in the selected activity window',
                ],
            ],
            'requestBreakdown' => [
                [
                    'label' => 'Approved',
                    'value' => $this->userRequestCount($staff->id, AssetRequestStatusEnum::ADMIN_APPROVED, $filters),
                ],
                [
                    'label' => 'Issued',
                    'value' => $this->userRequestCount($staff->id, AssetRequestStatusEnum::ISSUED, $filters),
                ],
                [
                    'label' => 'Cancelled',
                    'value' => $this->userRequestCount($staff->id, AssetRequestStatusEnum::CANCELLED, $filters),
                ],
            ],
            'filters' => $filters,
            'rangeLabel' => $this->rangeLabel($filters),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyDateRange(Builder $query, array $filters, string $column = 'created_at'): void
    {
        if (isset($filters['from'])) {
            $query->whereDate($column, '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate($column, '<=', $filters['to']);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function rangeLabel(array $filters): string
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        if ($from === null && $to === null) {
            return 'All time';
        }

        if ($from !== null && $to !== null) {
            return Carbon::parse($from)->format('d M Y').' - '.Carbon::parse($to)->format('d M Y');
        }

        if ($from !== null) {
            return 'From '.Carbon::parse($from)->format('d M Y');
        }

        return 'Up to '.Carbon::parse((string) $to)->format('d M Y');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function requestCountForStatus(AssetRequestStatusEnum $status, array $filters): int
    {
        $query = AssetRequest::query()->where('status', $status);

        $this->applyDateRange($query, $filters);

        return $query->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function departmentRequestCount(?int $departmentId, AssetRequestStatusEnum $status, array $filters): int
    {
        $query = AssetRequest::query()
            ->where('department_id', $departmentId)
            ->where('status', $status);

        $this->applyDateRange($query, $filters);

        return $query->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function departmentLowStockCount(?int $departmentId, array $filters): int
    {
        $query = Asset::query()
            ->where('department_id', $departmentId)
            ->whereColumn('quantity_available', '<=', 'reorder_level');

        $this->applyDateRange($query, $filters);

        return $query->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function userRequestCount(int $userId, AssetRequestStatusEnum $status, array $filters): int
    {
        $query = AssetRequest::query()
            ->where('user_id', $userId)
            ->where('status', $status);

        $this->applyDateRange($query, $filters);

        return $query->count();
    }
}
