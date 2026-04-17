<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\Organization\DepartmentManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display the department workspace.
     */
    public function index(DepartmentManagementService $departmentManagementService): View
    {
        return view('admin.departments.index', [
            'departments' => $departmentManagementService->paginate(),
        ]);
    }

    /**
     * Show the department creation form.
     */
    public function create(): View
    {
        return view('admin.departments.create');
    }

    /**
     * Store a new department.
     */
    public function store(
        StoreDepartmentRequest $request,
        DepartmentManagementService $departmentManagementService,
    ): RedirectResponse {
        $department = $departmentManagementService->create($request);

        return redirect()
            ->route('admin.departments.edit', $department)
            ->with('status', $department->name.' was created successfully.');
    }

    /**
     * Show the department edit form.
     */
    public function edit(Department $department): View
    {
        return view('admin.departments.edit', [
            'department' => $department->load('manager'),
        ]);
    }

    /**
     * Update a department.
     */
    public function update(
        UpdateDepartmentRequest $request,
        Department $department,
        DepartmentManagementService $departmentManagementService,
    ): RedirectResponse {
        $updatedDepartment = $departmentManagementService->update($department, $request);

        return redirect()
            ->route('admin.departments.edit', $updatedDepartment)
            ->with('status', $updatedDepartment->name.' was updated successfully.');
    }

    /**
     * Delete a department.
     */
    public function destroy(
        Department $department,
        DepartmentManagementService $departmentManagementService,
    ): RedirectResponse {
        $departmentName = $department->name;
        $departmentManagementService->delete($department);

        return redirect()
            ->route('admin.departments.index')
            ->with('status', $departmentName.' was deleted.');
    }
}
