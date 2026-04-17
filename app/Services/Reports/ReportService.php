<?php

namespace App\Services\Reports;

use App\Enums\AssetIssueStatusEnum;
use App\Enums\AssetRequestStatusEnum;
use App\Models\Asset;
use App\Models\AssetIssue;
use App\Models\AssetRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Build the admin stock report data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     assets: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    public function stockReportForAdmin(array $filters): array
    {
        return $this->buildStockReport(
            filters: $filters,
            departmentId: isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            forceDepartmentScope: false,
            includeDepartments: true,
        );
    }

    /**
     * Build the manager stock report data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     assets: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    public function stockReportForManager(User $manager, array $filters): array
    {
        return $this->buildStockReport(
            filters: $filters,
            departmentId: $manager->department_id,
            forceDepartmentScope: true,
            includeDepartments: false,
        );
    }

    /**
     * Build the admin request report data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     requests: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    public function requestReportForAdmin(array $filters): array
    {
        return $this->buildRequestReport(
            filters: $filters,
            departmentId: isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            forceDepartmentScope: false,
            includeDepartments: true,
        );
    }

    /**
     * Build the manager request report data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     requests: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    public function requestReportForManager(User $manager, array $filters): array
    {
        return $this->buildRequestReport(
            filters: $filters,
            departmentId: $manager->department_id,
            forceDepartmentScope: true,
            includeDepartments: false,
        );
    }

    /**
     * Build the admin issue report data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     issues: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    public function issueReportForAdmin(array $filters): array
    {
        return $this->buildIssueReport(
            filters: $filters,
            departmentId: isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            forceDepartmentScope: false,
            includeDepartments: true,
        );
    }

    /**
     * Build the manager issue report data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     issues: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    public function issueReportForManager(User $manager, array $filters): array
    {
        return $this->buildIssueReport(
            filters: $filters,
            departmentId: $manager->department_id,
            forceDepartmentScope: true,
            includeDepartments: false,
        );
    }

    /**
     * Build the admin low-stock report data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     assets: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    public function lowStockReportForAdmin(array $filters): array
    {
        return $this->buildLowStockReport(
            filters: $filters,
            departmentId: isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            forceDepartmentScope: false,
            includeDepartments: true,
        );
    }

    /**
     * Build the manager low-stock report data.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     assets: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    public function lowStockReportForManager(User $manager, array $filters): array
    {
        return $this->buildLowStockReport(
            filters: $filters,
            departmentId: $manager->department_id,
            forceDepartmentScope: true,
            includeDepartments: false,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     assets: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    private function buildStockReport(
        array $filters,
        ?int $departmentId,
        bool $forceDepartmentScope,
        bool $includeDepartments,
    ): array {
        $query = Asset::query()->with(['category', 'department']);

        $this->applyDepartmentScope($query, $departmentId, $forceDepartmentScope);
        $this->applyAssetFilters($query, $filters);

        return [
            'assets' => (clone $query)
                ->orderBy('department_id')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'summary' => [
                [
                    'label' => 'Tracked Assets',
                    'value' => (clone $query)->count(),
                    'meta' => 'Inventory records currently visible in this report',
                ],
                [
                    'label' => 'Available Units',
                    'value' => (int) (clone $query)->sum('quantity_available'),
                    'meta' => 'Units still available for future requests',
                ],
                [
                    'label' => 'Low Stock',
                    'value' => (clone $query)->whereColumn('quantity_available', '<=', 'reorder_level')->count(),
                    'meta' => 'Assets at or below their reorder threshold',
                ],
            ],
            'departments' => $includeDepartments ? $this->departments() : collect(),
            'filters' => $filters,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     requests: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    private function buildRequestReport(
        array $filters,
        ?int $departmentId,
        bool $forceDepartmentScope,
        bool $includeDepartments,
    ): array {
        $query = AssetRequest::query()->with(['asset.category', 'department', 'user']);

        $this->applyDepartmentScope($query, $departmentId, $forceDepartmentScope);
        $this->applyRequestFilters($query, $filters);

        return [
            'requests' => (clone $query)
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'summary' => [
                [
                    'label' => 'Total Requests',
                    'value' => (clone $query)->count(),
                    'meta' => 'Requests currently matching the selected filters',
                ],
                [
                    'label' => 'Pending',
                    'value' => (clone $query)->where('status', AssetRequestStatusEnum::PENDING)->count(),
                    'meta' => 'Requests still waiting for manager review',
                ],
                [
                    'label' => 'Issued',
                    'value' => (clone $query)->where('status', AssetRequestStatusEnum::ISSUED)->count(),
                    'meta' => 'Requests that have already turned into issued assets',
                ],
            ],
            'departments' => $includeDepartments ? $this->departments() : collect(),
            'filters' => $filters,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     issues: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    private function buildIssueReport(
        array $filters,
        ?int $departmentId,
        bool $forceDepartmentScope,
        bool $includeDepartments,
    ): array {
        $query = AssetIssue::query()->with(['asset', 'assetRequest', 'department', 'issuedToUser', 'returns']);

        $this->applyDepartmentScope($query, $departmentId, $forceDepartmentScope);
        $this->applyIssueFilters($query, $filters);

        return [
            'issues' => (clone $query)
                ->latest('issued_at')
                ->paginate(10)
                ->withQueryString(),
            'summary' => [
                [
                    'label' => 'Total Issues',
                    'value' => (clone $query)->count(),
                    'meta' => 'Issue records currently matching this report scope',
                ],
                [
                    'label' => 'Currently Issued',
                    'value' => (clone $query)->where('status', AssetIssueStatusEnum::ISSUED)->count(),
                    'meta' => 'Assets still fully checked out',
                ],
                [
                    'label' => 'Returned',
                    'value' => (clone $query)->where('status', AssetIssueStatusEnum::RETURNED)->count(),
                    'meta' => 'Issue records that have been fully closed out',
                ],
            ],
            'departments' => $includeDepartments ? $this->departments() : collect(),
            'filters' => $filters,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     assets: LengthAwarePaginator,
     *     summary: array<int, array{label: string, value: int, meta: string}>,
     *     departments: Collection<int, Department>,
     *     filters: array<string, mixed>
     * }
     */
    private function buildLowStockReport(
        array $filters,
        ?int $departmentId,
        bool $forceDepartmentScope,
        bool $includeDepartments,
    ): array {
        $query = Asset::query()
            ->with(['category', 'department'])
            ->whereColumn('quantity_available', '<=', 'reorder_level');

        $this->applyDepartmentScope($query, $departmentId, $forceDepartmentScope);
        $this->applyAssetFilters($query, $filters);

        return [
            'assets' => (clone $query)
                ->orderBy('quantity_available')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'summary' => [
                [
                    'label' => 'Low-Stock Items',
                    'value' => (clone $query)->count(),
                    'meta' => 'Assets already at or below the reorder level',
                ],
                [
                    'label' => 'Available Units Left',
                    'value' => (int) (clone $query)->sum('quantity_available'),
                    'meta' => 'Combined units remaining across low-stock items',
                ],
                [
                    'label' => 'Out of Stock',
                    'value' => (clone $query)->where('quantity_available', '<=', 0)->count(),
                    'meta' => 'Low-stock items with no available units left',
                ],
            ],
            'departments' => $includeDepartments ? $this->departments() : collect(),
            'filters' => $filters,
        ];
    }

    private function applyDepartmentScope(Builder $query, ?int $departmentId, bool $forceDepartmentScope): void
    {
        if ($forceDepartmentScope && $departmentId === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($departmentId !== null) {
            $query->where('department_id', $departmentId);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyAssetFilters(Builder $query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = (string) $filters['search'];

            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('asset_code', 'like', '%'.$search.'%')
                    ->orWhere('brand', 'like', '%'.$search.'%')
                    ->orWhere('model', 'like', '%'.$search.'%')
                    ->orWhereHas('category', function (Builder $categoryQuery) use ($search): void {
                        $categoryQuery->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyRequestFilters(Builder $query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = (string) $filters['search'];

            $query->where(function (Builder $query) use ($search): void {
                $query->where('request_number', 'like', '%'.$search.'%')
                    ->orWhere('reason', 'like', '%'.$search.'%')
                    ->orWhereHas('asset', function (Builder $assetQuery) use ($search): void {
                        $assetQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('asset_code', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyIssueFilters(Builder $query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = (string) $filters['search'];

            $query->where(function (Builder $query) use ($search): void {
                $query->whereHas('asset', function (Builder $assetQuery) use ($search): void {
                    $assetQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('asset_code', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('assetRequest', function (Builder $requestQuery) use ($search): void {
                        $requestQuery->where('request_number', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('issuedToUser', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('issued_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('issued_at', '<=', $filters['to']);
        }
    }

    /**
     * @return Collection<int, Department>
     */
    private function departments(): Collection
    {
        return Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
