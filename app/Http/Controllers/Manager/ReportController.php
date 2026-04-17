<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\IssueReportRequest;
use App\Http\Requests\Reports\RequestReportRequest;
use App\Http\Requests\Reports\StockReportRequest;
use App\Models\User;
use App\Services\Reports\ReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the department stock report.
     */
    public function stock(StockReportRequest $request, ReportService $reportService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('reports.stock', [
            ...$reportService->stockReportForManager($user, $request->filters()),
            'routePrefix' => 'manager.reports',
            'pageEyebrow' => 'Department Reporting',
            'pageTitle' => 'Stock Report',
            'pageDescription' => 'A department-only stock view that helps managers understand their available inventory before approving more requests.',
            'scopeLabel' => $user->department?->name ?? 'Unassigned Department',
            'showDepartmentFilter' => false,
        ]);
    }

    /**
     * Export the department stock report as CSV.
     */
    public function exportStock(StockReportRequest $request, ReportService $reportService): StreamedResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $reportService->exportStockCsvForManager($user, $request->filters());
    }

    /**
     * Display the department request report.
     */
    public function requests(RequestReportRequest $request, ReportService $reportService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('reports.requests', [
            ...$reportService->requestReportForManager($user, $request->filters()),
            'routePrefix' => 'manager.reports',
            'pageEyebrow' => 'Department Reporting',
            'pageTitle' => 'Request Report',
            'pageDescription' => 'A department request view for reviewing pending demand, approval flow, and recent request history in one place.',
            'scopeLabel' => $user->department?->name ?? 'Unassigned Department',
            'showDepartmentFilter' => false,
        ]);
    }

    /**
     * Export the department request report as CSV.
     */
    public function exportRequests(RequestReportRequest $request, ReportService $reportService): StreamedResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $reportService->exportRequestCsvForManager($user, $request->filters());
    }

    /**
     * Display the department issue report.
     */
    public function issues(IssueReportRequest $request, ReportService $reportService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('reports.issues', [
            ...$reportService->issueReportForManager($user, $request->filters()),
            'routePrefix' => 'manager.reports',
            'pageEyebrow' => 'Department Reporting',
            'pageTitle' => 'Issue Report',
            'pageDescription' => 'A department issue view for tracking which assets are still issued, partially returned, or fully closed out.',
            'scopeLabel' => $user->department?->name ?? 'Unassigned Department',
            'showDepartmentFilter' => false,
        ]);
    }

    /**
     * Export the department issue report as CSV.
     */
    public function exportIssues(IssueReportRequest $request, ReportService $reportService): StreamedResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $reportService->exportIssueCsvForManager($user, $request->filters());
    }

    /**
     * Display the department low-stock report.
     */
    public function lowStock(StockReportRequest $request, ReportService $reportService): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('reports.low-stock', [
            ...$reportService->lowStockReportForManager($user, $request->filters()),
            'routePrefix' => 'manager.reports',
            'pageEyebrow' => 'Department Reporting',
            'pageTitle' => 'Low Stock Report',
            'pageDescription' => 'A department warning view for assets that are nearing stock exhaustion and may affect upcoming approvals.',
            'scopeLabel' => $user->department?->name ?? 'Unassigned Department',
            'showDepartmentFilter' => false,
        ]);
    }

    /**
     * Export the department low-stock report as CSV.
     */
    public function exportLowStock(StockReportRequest $request, ReportService $reportService): StreamedResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $reportService->exportLowStockCsvForManager($user, $request->filters());
    }
}
