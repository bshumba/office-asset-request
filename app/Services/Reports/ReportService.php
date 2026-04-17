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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Export the admin stock report as CSV.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportStockCsvForAdmin(array $filters): StreamedResponse
    {
        $assets = $this->stockQuery(
            filters: $filters,
            departmentId: isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            forceDepartmentScope: false,
        )->orderBy('department_id')->orderBy('name')->get();

        return $this->csvResponse(
            'stock-report.csv',
            ['Asset', 'Code', 'Department', 'Status', 'Available', 'Total', 'Reorder Level', 'Category', 'Brand', 'Model'],
            $assets->map(static fn (Asset $asset): array => [
                $asset->name,
                $asset->asset_code,
                $asset->department?->name ?? 'Unassigned',
                (string) str($asset->status->value)->headline(),
                $asset->quantity_available,
                $asset->quantity_total,
                $asset->reorder_level,
                $asset->category?->name ?? 'Uncategorized',
                $asset->brand ?? '',
                $asset->model ?? '',
            ])->all(),
        );
    }

    /**
     * Export the manager stock report as CSV.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportStockCsvForManager(User $manager, array $filters): StreamedResponse
    {
        $assets = $this->stockQuery(
            filters: $filters,
            departmentId: $manager->department_id,
            forceDepartmentScope: true,
        )->orderBy('name')->get();

        return $this->csvResponse(
            'department-stock-report.csv',
            ['Asset', 'Code', 'Department', 'Status', 'Available', 'Total', 'Reorder Level', 'Category', 'Brand', 'Model'],
            $assets->map(static fn (Asset $asset): array => [
                $asset->name,
                $asset->asset_code,
                $asset->department?->name ?? 'Unassigned',
                (string) str($asset->status->value)->headline(),
                $asset->quantity_available,
                $asset->quantity_total,
                $asset->reorder_level,
                $asset->category?->name ?? 'Uncategorized',
                $asset->brand ?? '',
                $asset->model ?? '',
            ])->all(),
        );
    }

    /**
     * Export the admin request report as CSV.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportRequestCsvForAdmin(array $filters): StreamedResponse
    {
        $requests = $this->requestQuery(
            filters: $filters,
            departmentId: isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            forceDepartmentScope: false,
        )->latest()->get();

        return $this->csvResponse(
            'request-report.csv',
            ['Request Number', 'Requester', 'Department', 'Asset', 'Status', 'Priority', 'Requested Qty', 'Approved Qty', 'Created At', 'Needed By', 'Reason'],
            $requests->map(static fn (AssetRequest $request): array => [
                $request->request_number,
                $request->user?->name ?? 'Unknown user',
                $request->department?->name ?? 'Unassigned',
                $request->asset?->name ?? 'Missing asset',
                (string) str($request->status->value)->headline(),
                (string) str($request->priority->value)->headline(),
                $request->quantity_requested,
                $request->quantity_approved ?? '',
                $request->created_at?->format('Y-m-d H:i:s') ?? '',
                $request->needed_by_date?->format('Y-m-d') ?? '',
                $request->reason,
            ])->all(),
        );
    }

    /**
     * Export the manager request report as CSV.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportRequestCsvForManager(User $manager, array $filters): StreamedResponse
    {
        $requests = $this->requestQuery(
            filters: $filters,
            departmentId: $manager->department_id,
            forceDepartmentScope: true,
        )->latest()->get();

        return $this->csvResponse(
            'department-request-report.csv',
            ['Request Number', 'Requester', 'Department', 'Asset', 'Status', 'Priority', 'Requested Qty', 'Approved Qty', 'Created At', 'Needed By', 'Reason'],
            $requests->map(static fn (AssetRequest $request): array => [
                $request->request_number,
                $request->user?->name ?? 'Unknown user',
                $request->department?->name ?? 'Unassigned',
                $request->asset?->name ?? 'Missing asset',
                (string) str($request->status->value)->headline(),
                (string) str($request->priority->value)->headline(),
                $request->quantity_requested,
                $request->quantity_approved ?? '',
                $request->created_at?->format('Y-m-d H:i:s') ?? '',
                $request->needed_by_date?->format('Y-m-d') ?? '',
                $request->reason,
            ])->all(),
        );
    }

    /**
     * Export the admin issue report as CSV.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportIssueCsvForAdmin(array $filters): StreamedResponse
    {
        $issues = $this->issueQuery(
            filters: $filters,
            departmentId: isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            forceDepartmentScope: false,
        )->latest('issued_at')->get();

        return $this->csvResponse(
            'issue-report.csv',
            ['Request Number', 'Asset', 'Issued To', 'Department', 'Status', 'Quantity Issued', 'Outstanding Quantity', 'Issued At', 'Expected Return'],
            $issues->map(static fn (AssetIssue $issue): array => [
                $issue->assetRequest?->request_number ?? '',
                $issue->asset?->name ?? 'Missing asset',
                $issue->issuedToUser?->name ?? 'Unknown user',
                $issue->department?->name ?? 'Unassigned',
                (string) str($issue->status->value)->headline(),
                $issue->quantity_issued,
                $issue->outstandingQuantity(),
                $issue->issued_at?->format('Y-m-d H:i:s') ?? '',
                $issue->expected_return_date?->format('Y-m-d') ?? '',
            ])->all(),
        );
    }

    /**
     * Export the manager issue report as CSV.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportIssueCsvForManager(User $manager, array $filters): StreamedResponse
    {
        $issues = $this->issueQuery(
            filters: $filters,
            departmentId: $manager->department_id,
            forceDepartmentScope: true,
        )->latest('issued_at')->get();

        return $this->csvResponse(
            'department-issue-report.csv',
            ['Request Number', 'Asset', 'Issued To', 'Department', 'Status', 'Quantity Issued', 'Outstanding Quantity', 'Issued At', 'Expected Return'],
            $issues->map(static fn (AssetIssue $issue): array => [
                $issue->assetRequest?->request_number ?? '',
                $issue->asset?->name ?? 'Missing asset',
                $issue->issuedToUser?->name ?? 'Unknown user',
                $issue->department?->name ?? 'Unassigned',
                (string) str($issue->status->value)->headline(),
                $issue->quantity_issued,
                $issue->outstandingQuantity(),
                $issue->issued_at?->format('Y-m-d H:i:s') ?? '',
                $issue->expected_return_date?->format('Y-m-d') ?? '',
            ])->all(),
        );
    }

    /**
     * Export the admin low-stock report as CSV.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportLowStockCsvForAdmin(array $filters): StreamedResponse
    {
        $assets = $this->lowStockQuery(
            filters: $filters,
            departmentId: isset($filters['department_id']) ? (int) $filters['department_id'] : null,
            forceDepartmentScope: false,
        )->orderBy('quantity_available')->orderBy('name')->get();

        return $this->csvResponse(
            'low-stock-report.csv',
            ['Asset', 'Code', 'Department', 'Status', 'Available', 'Reorder Level', 'Category'],
            $assets->map(static fn (Asset $asset): array => [
                $asset->name,
                $asset->asset_code,
                $asset->department?->name ?? 'Unassigned',
                (string) str($asset->status->value)->headline(),
                $asset->quantity_available,
                $asset->reorder_level,
                $asset->category?->name ?? 'Uncategorized',
            ])->all(),
        );
    }

    /**
     * Export the manager low-stock report as CSV.
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportLowStockCsvForManager(User $manager, array $filters): StreamedResponse
    {
        $assets = $this->lowStockQuery(
            filters: $filters,
            departmentId: $manager->department_id,
            forceDepartmentScope: true,
        )->orderBy('quantity_available')->orderBy('name')->get();

        return $this->csvResponse(
            'department-low-stock-report.csv',
            ['Asset', 'Code', 'Department', 'Status', 'Available', 'Reorder Level', 'Category'],
            $assets->map(static fn (Asset $asset): array => [
                $asset->name,
                $asset->asset_code,
                $asset->department?->name ?? 'Unassigned',
                (string) str($asset->status->value)->headline(),
                $asset->quantity_available,
                $asset->reorder_level,
                $asset->category?->name ?? 'Uncategorized',
            ])->all(),
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
        $query = $this->stockQuery($filters, $departmentId, $forceDepartmentScope);

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
        $query = $this->requestQuery($filters, $departmentId, $forceDepartmentScope);

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
        $query = $this->issueQuery($filters, $departmentId, $forceDepartmentScope);

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
        $query = $this->lowStockQuery($filters, $departmentId, $forceDepartmentScope);

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

    /**
     * @param  array<string, mixed>  $filters
     */
    private function stockQuery(array $filters, ?int $departmentId, bool $forceDepartmentScope): Builder
    {
        $query = Asset::query()->with(['category', 'department']);

        $this->applyDepartmentScope($query, $departmentId, $forceDepartmentScope);
        $this->applyAssetFilters($query, $filters);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function requestQuery(array $filters, ?int $departmentId, bool $forceDepartmentScope): Builder
    {
        $query = AssetRequest::query()->with(['asset.category', 'department', 'user']);

        $this->applyDepartmentScope($query, $departmentId, $forceDepartmentScope);
        $this->applyRequestFilters($query, $filters);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function issueQuery(array $filters, ?int $departmentId, bool $forceDepartmentScope): Builder
    {
        $query = AssetIssue::query()->with(['asset', 'assetRequest', 'department', 'issuedToUser', 'returns']);

        $this->applyDepartmentScope($query, $departmentId, $forceDepartmentScope);
        $this->applyIssueFilters($query, $filters);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function lowStockQuery(array $filters, ?int $departmentId, bool $forceDepartmentScope): Builder
    {
        $query = Asset::query()
            ->with(['category', 'department'])
            ->whereColumn('quantity_available', '<=', 'reorder_level');

        $this->applyDepartmentScope($query, $departmentId, $forceDepartmentScope);
        $this->applyAssetFilters($query, $filters);

        return $query;
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

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string|int>>  $rows
     */
    private function csvResponse(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $stream = fopen('php://output', 'w');

            if ($stream === false) {
                return;
            }

            fputcsv($stream, $headers);

            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
