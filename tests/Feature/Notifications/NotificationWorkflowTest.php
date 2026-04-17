<?php

use App\Enums\AssetRequestStatusEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use App\Enums\RequestPriorityEnum;
use App\Enums\UserStatusEnum;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetRequest;
use App\Models\NotificationLog;
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

    $this->category = AssetCategory::query()->create([
        'name' => 'Monitors',
        'slug' => 'monitors',
        'is_active' => true,
    ]);

    $this->asset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->department->id,
        'name' => 'Dell Monitor',
        'slug' => 'dell-monitor',
        'asset_code' => 'AST-NTF-100',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 8,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $this->admin = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->admin->assignRole('Super Admin');

    $this->manager = User::factory()->create([
        'department_id' => $this->department->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->manager->assignRole('Department Manager');

    $this->staff = User::factory()->create([
        'department_id' => $this->department->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->staff->assignRole('Staff');

    $this->otherUser = User::factory()->create([
        'department_id' => $this->department->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->otherUser->assignRole('Staff');
});

it('writes a manager notification when staff submit a request', function () {
    $this->actingAs($this->staff)
        ->post(route('staff.requests.store'), [
            'asset_id' => $this->asset->id,
            'quantity_requested' => 1,
            'reason' => 'Need a monitor for work.',
            'priority' => RequestPriorityEnum::NORMAL->value,
            'needed_by_date' => now()->addDay()->toDateString(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('notification_logs', [
        'user_id' => $this->manager->id,
        'type' => 'request.submitted',
        'title' => 'New asset request submitted',
    ]);
});

it('writes a staff notification when admin issues an approved request', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-NTF-1001',
        'user_id' => $this->staff->id,
        'department_id' => $this->department->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a monitor for work.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ADMIN_APPROVED,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.issues.store', $request), [
            'quantity_issued' => 1,
            'notes' => 'Issued from stores.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('notification_logs', [
        'user_id' => $this->staff->id,
        'type' => 'request.issued',
        'title' => 'An asset was issued to you',
    ]);
});

it('shows only the signed in users notifications and opens the related destination while marking it as read', function () {
    $ownNotification = NotificationLog::query()->create([
        'user_id' => $this->staff->id,
        'type' => 'request.submitted',
        'title' => 'My notification',
        'message' => 'This belongs to the staff member.',
        'action_url' => route('staff.dashboard'),
    ]);

    NotificationLog::query()->create([
        'user_id' => $this->otherUser->id,
        'type' => 'request.submitted',
        'title' => 'Other notification',
        'message' => 'This belongs to someone else.',
    ]);

    $this->actingAs($this->staff)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSeeText('My notification')
        ->assertDontSeeText('Other notification');

    $this->actingAs($this->staff)
        ->get(route('notifications.open', $ownNotification->id))
        ->assertRedirect(route('staff.dashboard'));

    expect($ownNotification->fresh()->read_at)->not->toBeNull();
});

it('marks a request notification as read when the related request is opened directly', function () {
    $request = AssetRequest::query()->create([
        'request_number' => 'REQ-NTF-2001',
        'user_id' => $this->staff->id,
        'department_id' => $this->department->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a monitor for testing.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
    ]);

    $notification = NotificationLog::query()->create([
        'user_id' => $this->staff->id,
        'type' => 'request.manager-approved',
        'title' => 'Request approved',
        'message' => 'This request was approved by a manager.',
        'action_url' => route('staff.requests.show', $request),
        'resource_type' => NotificationLog::RESOURCE_ASSET_REQUEST,
        'resource_id' => $request->id,
    ]);

    $this->actingAs($this->staff)
        ->get(route('staff.requests.show', $request))
        ->assertOk();

    expect($notification->fresh()->read_at)->not->toBeNull();
});
