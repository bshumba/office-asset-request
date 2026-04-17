<?php

use App\Enums\AssetRequestStatusEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use App\Enums\RequestPriorityEnum;
use App\Enums\UserStatusEnum;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetRequest;
use App\Models\AssetRequestStatusHistory;
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

    $this->itAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Dell Latitude 5440',
        'slug' => 'dell-latitude-5440',
        'asset_code' => 'AST-MGR-100',
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
        'name' => 'HR Workstation',
        'slug' => 'hr-workstation',
        'asset_code' => 'AST-MGR-101',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 6,
        'quantity_available' => 4,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);
});

it('shows managers only requests from their own department on the review index', function () {
    $sameDepartmentRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0001',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 2,
        'reason' => 'IT request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $otherDepartmentRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-HR-0001',
        'user_id' => $this->hrStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->hrAsset->id,
        'quantity_requested' => 1,
        'reason' => 'HR request.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->get(route('manager.requests.index'))
        ->assertOk()
        ->assertSeeText($sameDepartmentRequest->request_number)
        ->assertDontSeeText($otherDepartmentRequest->request_number);
});

it('allows a manager to view request details only from their own department', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0002',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a laptop.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->get(route('manager.requests.show', $request))
        ->assertOk()
        ->assertSeeText($request->request_number)
        ->assertSeeText($this->itStaff->name);
});

it('blocks a manager from viewing another departments request details', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-HR-0002',
        'user_id' => $this->hrStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->hrAsset->id,
        'quantity_requested' => 1,
        'reason' => 'Need a workstation.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->get(route('manager.requests.show', $request))
        ->assertForbidden();
});

it('allows a manager to approve a pending request from their own department', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0003',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 3,
        'reason' => 'Need equipment for a project.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->patch(route('manager.requests.approve', $request), [
            'quantity_approved' => 2,
            'manager_comment' => 'Approved for the current project phase.',
        ])
        ->assertRedirect(route('manager.requests.show', $request));

    $request->refresh();

    expect($request->status)->toBe(AssetRequestStatusEnum::MANAGER_APPROVED)
        ->and($request->quantity_approved)->toBe(2)
        ->and($request->manager_reviewed_by)->toBe($this->itManager->id)
        ->and($request->manager_comment)->toBe('Approved for the current project phase.')
        ->and($request->manager_reviewed_at)->not->toBeNull();
});

it('blocks a manager from approving a request outside their department', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-HR-0003',
        'user_id' => $this->hrStaff->id,
        'department_id' => $this->hrDepartment->id,
        'asset_id' => $this->hrAsset->id,
        'quantity_requested' => 2,
        'reason' => 'Need a workstation.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->patch(route('manager.requests.approve', $request), [
            'quantity_approved' => 1,
            'manager_comment' => 'Trying to approve another department.',
        ])
        ->assertForbidden();
});

it('blocks a manager from reviewing a request that is no longer pending', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0004',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 2,
        'quantity_approved' => 2,
        'reason' => 'Already reviewed request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
        'manager_reviewed_by' => $this->itManager->id,
        'manager_reviewed_at' => now(),
    ]);

    $this->actingAs($this->itManager)
        ->patch(route('manager.requests.approve', $request), [
            'quantity_approved' => 1,
        ])
        ->assertForbidden();
});

it('requires the approved quantity to be greater than zero', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0005',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 2,
        'reason' => 'Need equipment.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->from(route('manager.requests.show', $request))
        ->patch(route('manager.requests.approve', $request), [
            'quantity_approved' => 0,
        ])
        ->assertRedirect(route('manager.requests.show', $request))
        ->assertSessionHasErrors('quantity_approved');
});

it('requires the approved quantity not to exceed the requested quantity', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0006',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 2,
        'reason' => 'Need equipment.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->from(route('manager.requests.show', $request))
        ->patch(route('manager.requests.approve', $request), [
            'quantity_approved' => 3,
        ])
        ->assertRedirect(route('manager.requests.show', $request))
        ->assertSessionHasErrors('quantity_approved');
});

it('writes a status history entry when a manager approves a request', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0007',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 2,
        'reason' => 'Need equipment.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    AssetRequestStatusHistory::query()->create([
        'asset_request_id' => $request->id,
        'status' => AssetRequestStatusEnum::PENDING,
        'comment' => 'Request submitted by staff.',
        'acted_by' => $this->itStaff->id,
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($this->itManager)
        ->patch(route('manager.requests.approve', $request), [
            'quantity_approved' => 2,
            'manager_comment' => 'Approved by manager.',
        ]);

    $latestHistory = AssetRequestStatusHistory::query()
        ->where('asset_request_id', $request->id)
        ->latest('id')
        ->firstOrFail();

    expect($latestHistory->status)->toBe(AssetRequestStatusEnum::MANAGER_APPROVED)
        ->and($latestHistory->acted_by)->toBe($this->itManager->id)
        ->and($latestHistory->comment)->toBe('Approved by manager.');
});

it('allows a manager to reject a pending request from their own department', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0008',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 2,
        'reason' => 'Need equipment.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->patch(route('manager.requests.reject', $request), [
            'rejection_reason' => 'Current stock should be reserved for shared lab use.',
            'manager_comment' => 'Please resubmit next month if still needed.',
        ])
        ->assertRedirect(route('manager.requests.show', $request));

    $request->refresh();

    expect($request->status)->toBe(AssetRequestStatusEnum::REJECTED)
        ->and($request->manager_reviewed_by)->toBe($this->itManager->id)
        ->and($request->manager_reviewed_at)->not->toBeNull()
        ->and($request->rejection_reason)->toBe('Current stock should be reserved for shared lab use.')
        ->and($request->manager_comment)->toBe('Please resubmit next month if still needed.');
});

it('requires a rejection reason when a manager rejects a request', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0009',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 1,
        'reason' => 'Need equipment.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->itManager)
        ->from(route('manager.requests.show', $request))
        ->patch(route('manager.requests.reject', $request), [
            'rejection_reason' => '',
        ])
        ->assertRedirect(route('manager.requests.show', $request))
        ->assertSessionHasErrors('rejection_reason');
});

it('writes a status history entry when a manager rejects a request', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-IT-0010',
        'user_id' => $this->itStaff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->itAsset->id,
        'quantity_requested' => 1,
        'reason' => 'Need equipment.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    AssetRequestStatusHistory::query()->create([
        'asset_request_id' => $request->id,
        'status' => AssetRequestStatusEnum::PENDING,
        'comment' => 'Request submitted by staff.',
        'acted_by' => $this->itStaff->id,
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($this->itManager)
        ->patch(route('manager.requests.reject', $request), [
            'rejection_reason' => 'Budget is currently frozen for this request type.',
            'manager_comment' => 'Please revisit next quarter.',
        ]);

    $latestHistory = AssetRequestStatusHistory::query()
        ->where('asset_request_id', $request->id)
        ->latest('id')
        ->firstOrFail();

    expect($latestHistory->status)->toBe(AssetRequestStatusEnum::REJECTED)
        ->and($latestHistory->acted_by)->toBe($this->itManager->id)
        ->and($latestHistory->comment)->toBe('Please revisit next quarter.');
});
