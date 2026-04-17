<?php

namespace App\Services\Organization;

use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DepartmentManagementService
{
    /**
     * Paginate departments with their manager and related counts.
     */
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return Department::query()
            ->with('manager')
            ->withCount(['users', 'assets'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get all departments for selects.
     *
     * @return Collection<int, Department>
     */
    public function options(): Collection
    {
        return Department::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a department record.
     */
    public function create(StoreDepartmentRequest $request): Department
    {
        $department = Department::query()->create($request->departmentData());

        return $department->fresh(['manager']) ?? $department;
    }

    /**
     * Update a department record.
     */
    public function update(Department $department, UpdateDepartmentRequest $request): Department
    {
        $department->forceFill($request->departmentData())->save();

        return $department->fresh(['manager']) ?? $department;
    }

    /**
     * Delete a department when it has no dependent records.
     */
    public function delete(Department $department): void
    {
        if (
            $department->users()->exists()
            || $department->assets()->exists()
            || $department->assetRequests()->exists()
            || $department->assetIssues()->exists()
        ) {
            throw ValidationException::withMessages([
                'department' => 'This department already has related records. Set it inactive instead of deleting it.',
            ]);
        }

        $department->delete();
    }
}
