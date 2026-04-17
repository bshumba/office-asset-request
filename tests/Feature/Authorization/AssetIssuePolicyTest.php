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

    $this->itDepartment = Department::create([
        'name' => 'IT',
        'code' => 'IT',
        'description' => 'Information technology',
        'is_active' => true,
    ]);

    $this->hrDepartment = Department::create([
        'name' => 'HR',
        'code' => 'HR',
        'description' => 'Human resources',
        'is_active' => true,
    ]);

    $category = AssetCategory::create([
        'name' => 'Monitors',
        'slug' => 'monitors',
        'description' => 'Display devices',
        'is_active' => true,
    ]);

    $this->asset = Asset::create([
        'asset_category_id' => $category->id,
        'department_id' => null,
        'name' => 'Dell 24 Monitor',
        'slug' => 'dell-24-monitor',
        'asset_code' => 'AST-MON-TEST',
        'brand' => 'Dell',
        'model' => 'P2422H',
        'serial_number' => 'AST-MON-TEST-001',
        'description' => 'Test monitor',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 7,
        'reorder_level' => 2,
        'track_serial' => true,
        'status' => AssetStatusEnum::ACTIVE,
        'purchase_date' => '2025-09-10',
        'notes' => 'Test inventory item.',
    ]);

    $this->itManager = User::factory()->create([
        'department_id' => $this->itDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->itManager->assignRole('Department Manager');

    $this->hrManager = User::factory()->create([
        'department_id' => $this->hrDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->hrManager->assignRole('Department Manager');

    $this->itStaff = User::factory()->create([
        'department_id' => $this->itDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->itStaff->assignRole('Staff');

    $this->hrStaff = User::factory()->create([
        'department_id' => $this->hrDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->hrStaff->assignRole('Staff');
});

it('allows staff to view only their own issued assets', function () {
    $itRequest = AssetRequest::create([
        'request_number' => 'ISS-1001',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a monitor for work.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $ownIssue = AssetIssue::create([
        'asset_request_id' => $itRequest->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->itStaff->id,
        'issued_by_user_id' => $this->itManager->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $hrRequest = AssetRequest::create([
        'request_number' => 'ISS-1002',
        'user_id' => $this->hrStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a monitor for onboarding.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $otherIssue = AssetIssue::create([
        'asset_request_id' => $hrRequest->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->hrStaff->id,
        'issued_by_user_id' => $this->hrManager->id,
        'department_id' => $this->hrDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    expect($this->itStaff->can('view', $ownIssue))->toBeTrue();
    expect($this->itStaff->can('view', $otherIssue))->toBeFalse();
});

it('allows managers to view issue records only inside their own department', function () {
    $itRequest = AssetRequest::create([
        'request_number' => 'ISS-2001',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a replacement monitor.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $itIssue = AssetIssue::create([
        'asset_request_id' => $itRequest->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->itStaff->id,
        'issued_by_user_id' => $this->itManager->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $hrRequest = AssetRequest::create([
        'request_number' => 'ISS-2002',
        'user_id' => $this->hrStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a second monitor.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $hrIssue = AssetIssue::create([
        'asset_request_id' => $hrRequest->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->hrStaff->id,
        'issued_by_user_id' => $this->hrManager->id,
        'department_id' => $this->hrDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    expect($this->itManager->can('view', $itIssue))->toBeTrue();
    expect($this->itManager->can('view', $hrIssue))->toBeFalse();
});

it('requires direct return permission and an open issue state to create a return', function () {
    $storeOfficer = User::factory()->create([
        'department_id' => $this->itDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $storeOfficer->givePermissionTo('requests.view-department', 'returns.create');

    $openRequest = AssetRequest::create([
        'request_number' => 'ISS-3001',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a temporary monitor.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $openIssue = AssetIssue::create([
        'asset_request_id' => $openRequest->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->itStaff->id,
        'issued_by_user_id' => $this->itManager->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $closedRequest = AssetRequest::create([
        'request_number' => 'ISS-3002',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a spare monitor.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::RETURNED,
    ]);

    $closedIssue = AssetIssue::create([
        'asset_request_id' => $closedRequest->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->itStaff->id,
        'issued_by_user_id' => $this->itManager->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now(),
        'status' => AssetIssueStatusEnum::RETURNED,
    ]);

    expect($storeOfficer->can('createReturn', $openIssue))->toBeTrue();
    expect($storeOfficer->can('createReturn', $closedIssue))->toBeFalse();
});
