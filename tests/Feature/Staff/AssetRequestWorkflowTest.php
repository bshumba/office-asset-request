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

    $this->department = Department::query()->create([
        'name' => 'Information Technology',
        'code' => 'IT',
        'is_active' => true,
    ]);

    $this->otherDepartment = Department::query()->create([
        'name' => 'Human Resources',
        'code' => 'HR',
        'is_active' => true,
    ]);

    $this->category = AssetCategory::query()->create([
        'name' => 'Laptops',
        'slug' => 'laptops',
        'is_active' => true,
    ]);

    $this->staff = User::factory()->create([
        'department_id' => $this->department->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->staff->assignRole('Staff');

    $this->otherStaff = User::factory()->create([
        'department_id' => $this->otherDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->otherStaff->assignRole('Staff');

    $this->availableAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->department->id,
        'name' => 'Dell Latitude 5440',
        'slug' => 'dell-latitude-5440',
        'asset_code' => 'AST-LAP-100',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 8,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $this->inactiveAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->department->id,
        'name' => 'Inactive Laptop',
        'slug' => 'inactive-laptop',
        'asset_code' => 'AST-LAP-101',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 3,
        'quantity_available' => 1,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::INACTIVE,
    ]);

    $this->otherDepartmentAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->otherDepartment->id,
        'name' => 'HR Workstation',
        'slug' => 'hr-workstation',
        'asset_code' => 'AST-LAP-102',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 4,
        'quantity_available' => 2,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);
});

it('allows staff to open the request creation page', function () {
    $this->actingAs($this->staff)
        ->get(route('staff.requests.create'))
        ->assertOk()
        ->assertSeeText('Create Asset Request')
        ->assertSeeText($this->availableAsset->name);
});

it('allows staff to submit a valid asset request', function () {
    $this->actingAs($this->staff)
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 2,
            'needed_by_date' => now()->addDays(5)->toDateString(),
            'priority' => RequestPriorityEnum::HIGH->value,
            'reason' => 'Need a laptop for onboarding and client demos.',
        ])
        ->assertRedirect();

    expect(AssetRequest::query()->count())->toBe(1);

    $request = AssetRequest::query()->firstOrFail();

    expect($request->user_id)->toBe($this->staff->id)
        ->and($request->asset_id)->toBe($this->availableAsset->id)
        ->and($request->quantity_requested)->toBe(2)
        ->and($request->reason)->toBe('Need a laptop for onboarding and client demos.');
});

it('creates a unique request number for each new request', function () {
    $this->actingAs($this->staff)
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 1,
            'needed_by_date' => now()->addDays(3)->toDateString(),
            'priority' => RequestPriorityEnum::NORMAL->value,
            'reason' => 'Need a laptop for project work.',
        ]);

    $this->actingAs($this->staff)
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 1,
            'needed_by_date' => now()->addDays(7)->toDateString(),
            'priority' => RequestPriorityEnum::URGENT->value,
            'reason' => 'Need another device for testing.',
        ]);

    $requestNumbers = AssetRequest::query()
        ->orderBy('id')
        ->pluck('request_number');

    expect($requestNumbers)->toHaveCount(2)
        ->and($requestNumbers[0])->not->toBe($requestNumbers[1]);
});

it('stores the requester department automatically when a request is created', function () {
    $this->actingAs($this->staff)
        ->post(route('staff.requests.store'), [
            'department_id' => $this->otherDepartment->id,
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 1,
            'needed_by_date' => now()->addDays(2)->toDateString(),
            'priority' => RequestPriorityEnum::LOW->value,
            'reason' => 'Need a spare workstation.',
        ]);

    $request = AssetRequest::query()->firstOrFail();

    expect($request->department_id)->toBe($this->staff->department_id);
});

it('starts new requests in the pending state', function () {
    $this->actingAs($this->staff)
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 1,
            'needed_by_date' => now()->addDays(2)->toDateString(),
            'priority' => RequestPriorityEnum::NORMAL->value,
            'reason' => 'Need a standard issue device.',
        ]);

    $request = AssetRequest::query()->firstOrFail();

    expect($request->status)->toBe(AssetRequestStatusEnum::PENDING);
});

it('writes an initial status history entry when a request is created', function () {
    $this->actingAs($this->staff)
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 2,
            'needed_by_date' => now()->addDays(4)->toDateString(),
            'priority' => RequestPriorityEnum::HIGH->value,
            'reason' => 'Need a device for a temporary assignment.',
        ]);

    $request = AssetRequest::query()->firstOrFail();

    expect(AssetRequestStatusHistory::query()->count())->toBe(1);

    $history = AssetRequestStatusHistory::query()->firstOrFail();

    expect($history->asset_request_id)->toBe($request->id)
        ->and($history->status)->toBe(AssetRequestStatusEnum::PENDING)
        ->and($history->acted_by)->toBe($this->staff->id);
});

it('requires the requested asset to exist', function () {
    $this->actingAs($this->staff)
        ->from(route('staff.requests.create'))
        ->post(route('staff.requests.store'), [
            'asset_id' => 999999,
            'quantity_requested' => 1,
            'needed_by_date' => now()->addDays(2)->toDateString(),
            'priority' => RequestPriorityEnum::NORMAL->value,
            'reason' => 'Need a device.',
        ])
        ->assertRedirect(route('staff.requests.create'))
        ->assertSessionHasErrors('asset_id');
});

it('requires the requested asset to be active', function () {
    $this->actingAs($this->staff)
        ->from(route('staff.requests.create'))
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->inactiveAsset->id,
            'quantity_requested' => 1,
            'needed_by_date' => now()->addDays(2)->toDateString(),
            'priority' => RequestPriorityEnum::NORMAL->value,
            'reason' => 'Need a backup device.',
        ])
        ->assertRedirect(route('staff.requests.create'))
        ->assertSessionHasErrors('asset_id');
});

it('requires the requested asset to belong to the user department', function () {
    $this->actingAs($this->staff)
        ->from(route('staff.requests.create'))
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->otherDepartmentAsset->id,
            'quantity_requested' => 1,
            'needed_by_date' => now()->addDays(2)->toDateString(),
            'priority' => RequestPriorityEnum::NORMAL->value,
            'reason' => 'Need an out-of-scope device.',
        ])
        ->assertRedirect(route('staff.requests.create'))
        ->assertSessionHasErrors('asset_id');
});

it('requires the requested quantity to be at least one', function () {
    $this->actingAs($this->staff)
        ->from(route('staff.requests.create'))
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 0,
            'needed_by_date' => now()->addDays(2)->toDateString(),
            'priority' => RequestPriorityEnum::NORMAL->value,
            'reason' => 'Need a device.',
        ])
        ->assertRedirect(route('staff.requests.create'))
        ->assertSessionHasErrors('quantity_requested');
});

it('requires the needed by date to be today or later', function () {
    $this->actingAs($this->staff)
        ->from(route('staff.requests.create'))
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 1,
            'needed_by_date' => now()->subDay()->toDateString(),
            'priority' => RequestPriorityEnum::NORMAL->value,
            'reason' => 'Need a device.',
        ])
        ->assertRedirect(route('staff.requests.create'))
        ->assertSessionHasErrors('needed_by_date');
});

it('requires a reason when submitting a request', function () {
    $this->actingAs($this->staff)
        ->from(route('staff.requests.create'))
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->availableAsset->id,
            'quantity_requested' => 1,
            'needed_by_date' => now()->addDays(2)->toDateString(),
            'priority' => RequestPriorityEnum::NORMAL->value,
            'reason' => '',
        ])
        ->assertRedirect(route('staff.requests.create'))
        ->assertSessionHasErrors('reason');
});

it('shows only the authenticated staffs own requests on the index page', function () {
    $myRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-100001',
        'user_id' => $this->staff->id,
        'department_id' => $this->department->id,
        'asset_id' => $this->availableAsset->id,
        'quantity_requested' => 1,
        'reason' => 'My own request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $otherRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-100002',
        'user_id' => $this->otherStaff->id,
        'department_id' => $this->otherDepartment->id,
        'asset_id' => $this->otherDepartmentAsset->id,
        'quantity_requested' => 1,
        'reason' => 'Another users request.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->staff)
        ->get(route('staff.requests.index'))
        ->assertOk()
        ->assertSeeText($myRequest->request_number)
        ->assertDontSeeText($otherRequest->request_number);
});

it('prevents staff from viewing another users request details page', function () {
    $otherRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-200001',
        'user_id' => $this->otherStaff->id,
        'department_id' => $this->otherDepartment->id,
        'asset_id' => $this->otherDepartmentAsset->id,
        'quantity_requested' => 1,
        'reason' => 'Another users request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->staff)
        ->get(route('staff.requests.show', $otherRequest))
        ->assertForbidden();
});

it('allows staff to cancel their own pending request', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-300001',
        'user_id' => $this->staff->id,
        'department_id' => $this->department->id,
        'asset_id' => $this->availableAsset->id,
        'quantity_requested' => 1,
        'reason' => 'A pending request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->staff)
        ->patch(route('staff.requests.cancel', $request))
        ->assertRedirect(route('staff.requests.show', $request));

    $request->refresh();

    expect($request->status)->toBe(AssetRequestStatusEnum::CANCELLED)
        ->and($request->cancelled_by)->toBe($this->staff->id)
        ->and($request->cancelled_at)->not->toBeNull();
});

it('writes a cancelled status history record when a request is cancelled', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-300002',
        'user_id' => $this->staff->id,
        'department_id' => $this->department->id,
        'asset_id' => $this->availableAsset->id,
        'quantity_requested' => 1,
        'reason' => 'Another pending request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    AssetRequestStatusHistory::query()->create([
        'asset_request_id' => $request->id,
        'status' => AssetRequestStatusEnum::PENDING,
        'comment' => 'Request created.',
        'acted_by' => $this->staff->id,
        'created_at' => now(),
    ]);

    $this->actingAs($this->staff)
        ->patch(route('staff.requests.cancel', $request));

    $historyEntries = AssetRequestStatusHistory::query()
        ->where('asset_request_id', $request->id)
        ->orderBy('id')
        ->get();

    expect($historyEntries)->toHaveCount(2)
        ->and($historyEntries->last()->status)->toBe(AssetRequestStatusEnum::CANCELLED)
        ->and($historyEntries->last()->acted_by)->toBe($this->staff->id);
});
