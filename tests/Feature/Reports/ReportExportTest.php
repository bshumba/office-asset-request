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

    $this->otherStaff = User::factory()->create([
        'department_id' => $this->hrDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->otherStaff->assignRole('Staff');

    $this->itAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Dell Latitude',
        'slug' => 'dell-latitude',
        'asset_code' => 'AST-EXP-100',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 8,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $this->hrAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->hrDepartment->id,
        'name' => 'Office Monitor',
        'slug' => 'office-monitor',
        'asset_code' => 'AST-EXP-200',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 5,
        'quantity_available' => 4,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $this->itRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-EXP-1001',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Team export request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $this->hrRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-EXP-2001',
        'user_id' => $this->otherStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->hrAsset->id,
        'quantity_requested' => 1,
        'reason' => 'Other department request.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    AssetIssue::query()->create([
        'asset_request_id' => $this->itRequest->id,
        'asset_id' => $this->itAsset->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->admin->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now()->subDay(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);
});

it('lets admins export the stock report to csv', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.stock.export'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('Asset,Code,Department');
    expect($response->streamedContent())->toContain('Dell Latitude');
    expect($response->streamedContent())->toContain('Office Monitor');
});

it('scopes manager csv exports to the manager department only', function () {
    $response = $this->actingAs($this->manager)
        ->get(route('manager.reports.requests.export'));

    $response->assertOk();

    expect($response->streamedContent())->toContain('REQ-EXP-1001');
    expect($response->streamedContent())->not->toContain('REQ-EXP-2001');
});
