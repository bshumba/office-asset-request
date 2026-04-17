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

    $this->department = Department::query()->create([
        'name' => 'Information Technology',
        'code' => 'IT',
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
        'department_id' => $this->department->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->otherStaff->assignRole('Staff');

    $this->issuer = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->issuer->assignRole('Super Admin');
});

it('shows only the authenticated staffs assigned assets', function () {
    $ownAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->department->id,
        'name' => 'Dell Latitude',
        'slug' => 'dell-latitude',
        'asset_code' => 'AST-STF-100',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 7,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $otherAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->department->id,
        'name' => 'HP EliteBook',
        'slug' => 'hp-elitebook',
        'asset_code' => 'AST-STF-101',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 8,
        'quantity_available' => 5,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $ownRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-STF-1001',
        'user_id' => $this->staff->id,
        'department_id' => $this->department->id,
        'asset_id' => $ownAsset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a work laptop.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    AssetIssue::query()->create([
        'asset_request_id' => $ownRequest->id,
        'asset_id' => $ownAsset->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->issuer->id,
        'department_id' => $this->department->id,
        'quantity_issued' => 1,
        'issued_at' => now()->subDay(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $otherRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-STF-1002',
        'user_id' => $this->otherStaff->id,
        'department_id' => $this->department->id,
        'asset_id' => $otherAsset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need another work laptop.',
        'priority' => RequestPriorityEnum::HIGH,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    AssetIssue::query()->create([
        'asset_request_id' => $otherRequest->id,
        'asset_id' => $otherAsset->id,
        'issued_to_user_id' => $this->otherStaff->id,
        'issued_by_user_id' => $this->issuer->id,
        'department_id' => $this->department->id,
        'quantity_issued' => 1,
        'issued_at' => now()->subDay(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $this->actingAs($this->staff)
        ->get(route('staff.assigned-assets.index'))
        ->assertOk()
        ->assertSeeText('Assigned Assets')
        ->assertSeeText('Dell Latitude')
        ->assertDontSeeText('HP EliteBook');
});

it('allows staff to open their own assigned asset record but blocks other users records', function () {
    $asset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->department->id,
        'name' => 'Lenovo ThinkPad',
        'slug' => 'lenovo-thinkpad',
        'asset_code' => 'AST-STF-200',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 6,
        'quantity_available' => 4,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $ownRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-STF-2001',
        'user_id' => $this->staff->id,
        'department_id' => $this->department->id,
        'asset_id' => $asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a personal laptop.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $ownIssue = AssetIssue::query()->create([
        'asset_request_id' => $ownRequest->id,
        'asset_id' => $asset->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->issuer->id,
        'department_id' => $this->department->id,
        'quantity_issued' => 1,
        'issued_at' => now()->subDay(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $otherRequest = AssetRequest::query()->create([
        'request_number' => 'REQ-STF-2002',
        'user_id' => $this->otherStaff->id,
        'department_id' => $this->department->id,
        'asset_id' => $asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => 1,
        'reason' => 'Need a second laptop.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::ISSUED,
    ]);

    $otherIssue = AssetIssue::query()->create([
        'asset_request_id' => $otherRequest->id,
        'asset_id' => $asset->id,
        'issued_to_user_id' => $this->otherStaff->id,
        'issued_by_user_id' => $this->issuer->id,
        'department_id' => $this->department->id,
        'quantity_issued' => 1,
        'issued_at' => now()->subDay(),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $this->actingAs($this->staff)
        ->get(route('staff.assigned-assets.show', $ownIssue))
        ->assertOk()
        ->assertSeeText('Lenovo ThinkPad')
        ->assertSeeText('REQ-STF-2001');

    $this->actingAs($this->staff)
        ->get(route('staff.assigned-assets.show', $otherIssue))
        ->assertForbidden();
});
