<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\IssueReportRequest;
use App\Http\Requests\Reports\RequestReportRequest;
use App\Http\Requests\Reports\StockReportRequest;
use App\Services\Reports\ReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the global stock report.
     */
    public function stock(StockReportRequest $request, ReportService $reportService): View
    {
        return view('reports.stock', [
            ...$reportService->stockReportForAdmin($request->filters()),
            'routePrefix' => 'admin.reports',
            'pageEyebrow' => 'Admin Reporting',
            'pageTitle' => 'Stock Report',
            'pageDescription' => 'A global stock view for checking inventory volume, search results, and department-level asset distribution.',
            'scopeLabel' => 'All departments',
            'showDepartmentFilter' => true,
        ]);
    }

    /**
     * Export the global stock report as CSV.
     */
    public function exportStock(StockReportRequest $request, ReportService $reportService): StreamedResponse
    {
        return $reportService->exportStockCsvForAdmin($request->filters());
    }

    /**
     * Display the global request report.
     */
    public function requests(RequestReportRequest $request, ReportService $reportService): View
    {
        return view('reports.requests', [
            ...$reportService->requestReportForAdmin($request->filters()),
            'routePrefix' => 'admin.reports',
            'pageEyebrow' => 'Admin Reporting',
            'pageTitle' => 'Request Report',
            'pageDescription' => 'A global request view for following volume, approval states, priorities, and search-driven request tracing.',
            'scopeLabel' => 'All departments',
            'showDepartmentFilter' => true,
        ]);
    }

    /**
     * Export the global request report as CSV.
     */
    public function exportRequests(RequestReportRequest $request, ReportService $reportService): StreamedResponse
    {
        return $reportService->exportRequestCsvForAdmin($request->filters());
    }

    /**
     * Display the global issue report.
     */
    public function issues(IssueReportRequest $request, ReportService $reportService): View
    {
        return view('reports.issues', [
            ...$reportService->issueReportForAdmin($request->filters()),
            'routePrefix' => 'admin.reports',
            'pageEyebrow' => 'Admin Reporting',
            'pageTitle' => 'Issue Report',
            'pageDescription' => 'A global issue view for tracking what has been issued, what is still outstanding, and which returns are already complete.',
            'scopeLabel' => 'All departments',
            'showDepartmentFilter' => true,
        ]);
    }

    /**
     * Export the global issue report as CSV.
     */
    public function exportIssues(IssueReportRequest $request, ReportService $reportService): StreamedResponse
    {
        return $reportService->exportIssueCsvForAdmin($request->filters());
    }

    /**
     * Display the global low-stock report.
     */
    public function lowStock(StockReportRequest $request, ReportService $reportService): View
    {
        return view('reports.low-stock', [
            ...$reportService->lowStockReportForAdmin($request->filters()),
            'routePrefix' => 'admin.reports',
            'pageEyebrow' => 'Admin Reporting',
            'pageTitle' => 'Low Stock Report',
            'pageDescription' => 'A focused stock warning view for assets that are already at or below their reorder threshold.',
            'scopeLabel' => 'All departments',
            'showDepartmentFilter' => true,
        ]);
    }

    /**
     * Export the global low-stock report as CSV.
     */
    public function exportLowStock(StockReportRequest $request, ReportService $reportService): StreamedResponse
    {
        return $reportService->exportLowStockCsvForAdmin($request->filters());
    }
}
