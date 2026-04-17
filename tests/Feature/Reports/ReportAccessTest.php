<?php

use App\Enums\AssetIssueStatusEnum;
use App\Enums\AssetRequestStatusEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use App\Enums\RequestPriorityEnum;
use App\Enums\UserStatusEnum;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetIssue;
use App\Models\AssetRequest;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->itDepartment = Department::query()->create([
        'name' => 'Information Technology',
        'code' => 'IT',
        'is_active' => true,
    ]);

    $this->hrDepartment = Department::query()->create([
        'name' => 'Human Resources',
        'code' => 'HR',
        'is_active' => true,
    ]);

    $this->category = AssetCategory::query()->create([
        'name' => 'Equipment',
        'slug' => 'equipment',
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->admin->assignRole('Super Admin');

    $this->manager = User::factory()->create([
        'department_id' => $this->itDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->manager->assignRole('Department Manager');

    $this->staff = User::factory()->create([
        'department_id' => $this->itDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->staff->assignRole('Staff');

    $this->lowStockAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Low Stock Laptop',
        'slug' => 'low-stock-laptop',
        'asset_code' => 'AST-LOW-100',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 5,
        'quantity_available' => 1,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $this->healthyAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Healthy Laptop',
        'slug' => 'healthy-laptop',
        'asset_code' => 'AST-LOW-101',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 8,
        'quantity_available' => 7,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $this->request = AssetRequest::query()->create([
        'request_number' => 'REQ-RPT-1001',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->lowStockAsset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Report test request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $this->issue = AssetIssue::query()->create([
        'asset_request_id' => $this->request->id,
        'asset_id' => $this->lowStockAsset->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->admin->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now()->subDay(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);
});

it('requires report permission for manager stock reports', function () {
    $managerRole = Role::findByName('Department Manager', 'web');
    $managerRole->syncPermissions([
        'dashboard.view-manager',
        'requests.view-department',
        'requests.manager-approve',
        'requests.reject',
        'assets.view',
        'issues.view',
        'returns.view',
    ]);

    $this->actingAs($this->manager)
        ->get(route('manager.reports.stock'))
        ->assertForbidden();
});

it('allows users with report permission to access stock, request, and issue reports', function () {
    $this->actingAs($this->manager)
        ->get(route('manager.reports.stock'))
        ->assertOk()
        ->assertSeeText('Stock Report');

    $this->actingAs($this->manager)
        ->get(route('manager.reports.requests'))
        ->assertOk()
        ->assertSeeText('Request Report');

    $this->actingAs($this->manager)
        ->get(route('manager.reports.issues'))
        ->assertOk()
        ->assertSeeText('Issue Report');
});

it('blocks staff users from opening manager reports', function () {
    $this->actingAs($this->staff)
        ->get(route('manager.reports.stock'))
        ->assertForbidden();
});

it('shows only low stock items on the low stock report', function () {
    $this->actingAs($this->manager)
        ->get(route('manager.reports.low-stock'))
        ->assertOk()
        ->assertSeeText($this->lowStockAsset->name)
        ->assertDontSeeText($this->healthyAsset->name);
});
