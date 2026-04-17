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
        'name' => 'Laptops',
        'slug' => 'laptops',
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
});

it('shows global dashboard counts for admins', function () {
    Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Dell Latitude',
        'slug' => 'dell-latitude',
        'asset_code' => 'AST-REP-100',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 8,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->hrDepartment->id,
        'name' => 'HP EliteBook',
        'slug' => 'hp-elitebook',
        'asset_code' => 'AST-REP-101',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 5,
        'quantity_available' => 1,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    AssetRequest::query()->create([
        'request_number' => 'REQ-REP-1001',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => 1,
        'quantity_requested' => 1,
        'reason' => 'Pending request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Active Departments')
        ->assertSeeText('Team Members')
        ->assertSeeText('Tracked Assets')
        ->assertSeeText('Pending Requests')
        ->assertSeeText('2')
        ->assertSeeText('4')
        ->assertSeeText('1');
});

it('shows department-scoped dashboard counts for managers', function () {
    $assetOne = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Dell Latitude',
        'slug' => 'dell-latitude',
        'asset_code' => 'AST-REP-200',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 8,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Dell Monitor',
        'slug' => 'dell-monitor',
        'asset_code' => 'AST-REP-201',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 6,
        'quantity_available' => 3,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Logitech Keyboard',
        'slug' => 'logitech-keyboard',
        'asset_code' => 'AST-REP-202',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 12,
        'quantity_available' => 7,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    AssetRequest::query()->create([
        'request_number' => 'REQ-REP-2001',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $assetOne->id,
        'quantity_requested' => 1,
        'reason' => 'Pending request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    AssetRequest::query()->create([
        'request_number' => 'REQ-REP-2002',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $assetOne->id,
        'quantity_requested' => 1,
        'reason' => 'Manager approved request.',
        'priority' => RequestPriorityEnum::HIGH,
        'quantity_approved' => 1,
        'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
    ]);

    AssetIssue::query()->create([
        'asset_request_id' => AssetRequest::query()->where('request_number', 'REQ-REP-2002')->value('id'),
        'asset_id' => $assetOne->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->admin->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now()->subDay(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $this->actingAs($this->manager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSeeText('Department Staff')
        ->assertSeeText('Department Assets')
        ->assertSeeText('Pending Review')
        ->assertSeeText('Issued Items')
        ->assertSeeText('3')
        ->assertSeeText('1');
});

it('shows own request and issue counts for staff users', function () {
    $assetOne = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Dell Latitude',
        'slug' => 'dell-latitude',
        'asset_code' => 'AST-REP-300',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 8,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Monitor',
        'slug' => 'monitor',
        'asset_code' => 'AST-REP-301',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 6,
        'quantity_available' => 4,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Keyboard',
        'slug' => 'keyboard',
        'asset_code' => 'AST-REP-302',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 8,
        'quantity_available' => 5,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Dock',
        'slug' => 'dock',
        'asset_code' => 'AST-REP-303',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 5,
        'quantity_available' => 5,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $pending = AssetRequest::query()->create([
        'request_number' => 'REQ-REP-3001',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $assetOne->id,
        'quantity_requested' => 1,
        'reason' => 'Pending request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    AssetRequest::query()->create([
        'request_number' => 'REQ-REP-3002',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $assetOne->id,
        'quantity_requested' => 1,
        'reason' => 'Manager approved request.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
    ]);

    $issuedRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-REP-3003',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $assetOne->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Issued request.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    AssetIssue::query()->create([
        'asset_request_id' => $issuedRequest->id,
        'asset_id' => $assetOne->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->admin->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 1,
        'issued_at' => now()->subDay(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    AssetRequest::query()->create([
        'request_number' => 'REQ-REP-3999',
        'user_id' => $this->otherStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $assetOne->id,
        'quantity_requested' => 1,
        'reason' => 'Another user request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->staff)
        ->get(route('staff.dashboard'))
        ->assertOk()
        ->assertSeeText('My Requests')
        ->assertSeeText('Awaiting Decision')
        ->assertSeeText('Issued To Me')
        ->assertSeeText('Dept Inventory')
        ->assertSeeText('4')
        ->assertSeeText('3')
        ->assertSeeText('2')
        ->assertSeeText('1');
});
