<?php

use App\Enums\AssetRequestStatusEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use App\Enums\RequestPriorityEnum;
use App\Enums\UserStatusEnum;
use App\Models\Asset;
use App\Models\AssetCategory;
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
        'name' => 'Laptops',
        'slug' => 'laptops',
        'description' => 'Portable devices',
        'is_active' => true,
    ]);

    $this->asset = Asset::create([
        'asset_category_id' => $category->id,
        'department_id' => null,
        'name' => 'Dell Latitude 5440',
        'slug' => 'dell-latitude-5440',
        'asset_code' => 'AST-LAP-TEST',
        'brand' => 'Dell',
        'model' => 'Latitude 5440',
        'serial_number' => 'AST-LAP-TEST-001',
        'description' => 'Test asset',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 7,
        'reorder_level' => 2,
        'track_serial' => true,
        'status' => AssetStatusEnum::ACTIVE,
        'purchase_date' => '2025-09-10',
        'notes' => 'Test inventory item.',
    ]);

    $this->admin = User::factory()->create([
        'department_id' => null,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->admin->assignRole('Super Admin');

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

it('allows staff to view only their own requests', function () {
    $ownRequest = AssetRequest::create([
        'request_number' => 'REQ-1001',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a laptop for work.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $otherRequest = AssetRequest::create([
        'request_number' => 'REQ-1002',
        'user_id' => $this->hrStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a monitor for onboarding.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    expect($this->itStaff->can('view', $ownRequest))->toBeTrue();
    expect($this->itStaff->can('view', $otherRequest))->toBeFalse();
});

it('allows managers to approve pending requests only inside their own department', function () {
    $itRequest = AssetRequest::create([
        'request_number' => 'REQ-2001',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a laptop for a replacement.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $hrRequest = AssetRequest::create([
        'request_number' => 'REQ-2002',
        'user_id' => $this->hrStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a laptop for recruitment.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    expect($this->itManager->can('managerApprove', $itRequest))->toBeTrue();
    expect($this->itManager->can('managerApprove', $hrRequest))->toBeFalse();
});

it('allows staff to cancel only their own pending requests', function () {
    $pendingRequest = AssetRequest::create([
        'request_number' => 'REQ-3001',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a keyboard for work.',
        'priority' => RequestPriorityEnum::LOW,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $reviewedRequest = AssetRequest::create([
        'request_number' => 'REQ-3002',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a monitor for work.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
    ]);

    expect($this->itStaff->can('cancel', $pendingRequest))->toBeTrue();
    expect($this->itStaff->can('cancel', $reviewedRequest))->toBeFalse();
});

it('requires the correct request state for admin approval when using direct permissions', function () {
    $approvalOfficer = User::factory()->create([
        'department_id' => null,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $approvalOfficer->givePermissionTo('requests.admin-approve');

    $pendingRequest = AssetRequest::create([
        'request_number' => 'REQ-4001',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a desk monitor.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $readyForAdminRequest = AssetRequest::create([
        'request_number' => 'REQ-4002',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a laptop upgrade.',
        'priority' => RequestPriorityEnum::URGENT,
        'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
    ]);

    expect($approvalOfficer->can('adminApprove', $pendingRequest))->toBeFalse();
    expect($approvalOfficer->can('adminApprove', $readyForAdminRequest))->toBeTrue();
});

it('lets super admins bypass asset request policy checks', function () {
    $hrRequest = AssetRequest::create([
        'request_number' => 'REQ-5001',
        'user_id' => $this->hrStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a replacement printer.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    expect($this->admin->can('managerApprove', $hrRequest))->toBeTrue();
    expect($this->admin->can('reject', $hrRequest))->toBeTrue();
});
