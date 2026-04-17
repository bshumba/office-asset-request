<?php

use App\Enums\AssetIssueStatusEnum;
use App\Enums\AssetRequestStatusEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use App\Enums\RequestPriorityEnum;
use App\Enums\ReturnConditionEnum;
use App\Enums\StockAdjustmentReasonEnum;
use App\Enums\StockAdjustmentTypeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Asset;
use App\Models\AssetAdjustment;
use App\Models\AssetCategory;
use App\Models\AssetIssue;
use App\Models\AssetRequest;
use App\Models\AssetRequestStatusHistory;
use App\Models\AssetReturn;
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

    $this->asset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->itDepartment->id,
        'name' => 'Dell Latitude 5440',
        'slug' => 'dell-latitude-5440',
        'asset_code' => 'AST-ADM-100',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 10,
        'quantity_available' => 8,
        'reorder_level' => 2,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $this->otherAsset = Asset::query()->create([
        'asset_category_id' => $this->category->id,
        'department_id' => $this->hrDepartment->id,
        'name' => 'HR Workstation',
        'slug' => 'hr-workstation',
        'asset_code' => 'AST-ADM-101',
        'unit_type' => AssetUnitTypeEnum::PIECE,
        'quantity_total' => 6,
        'quantity_available' => 4,
        'reorder_level' => 1,
        'track_serial' => false,
        'status' => AssetStatusEnum::ACTIVE,
    ]);

    $this->createRequest = function (
        AssetRequestStatusEnum $status = AssetRequestStatusEnum::MANAGER_APPROVED,
        ?int $quantityApproved = 2,
    ): AssetRequest {
        return AssetRequest::query()->create([
            'request_number' => 'REQ-ADM-'.fake()->unique()->numerify('####'),
            'user_id' => $this->staff->id,
            'department_id' => $this->itDepartment->id,
            'asset_id' => $this->asset->id,
            'quantity_requested' => 3,
            'quantity_approved' => $quantityApproved,
            'reason' => 'Need equipment for project work.',
            'priority' => RequestPriorityEnum::HIGH,
            'status' => $status,
            'manager_reviewed_by' => $this->manager->id,
            'manager_reviewed_at' => now()->subHour(),
            'manager_comment' => 'Approved by manager for admin review.',
        ]);
    };
});

it('shows admins all requests in the request inbox', function () {
    $requestOne = ($this->createRequest)(AssetRequestStatusEnum::MANAGER_APPROVED, 2);

    $requestTwo = AssetRequest::query()->create([
        'request_number' => 'REQ-ADM-9999',
        'user_id' => $this->staff->id,
        'department_id' => $this->itDepartment->id,
        'asset_id' => $this->asset->id,
        'quantity_requested' => 1,
        'quantity_approved' => null,
        'reason' => 'A pending request.',
        'priority' => RequestPriorityEnum::NORMAL,
        'status' => AssetRequestStatusEnum::PENDING,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.requests.index'))
        ->assertOk()
        ->assertSeeText($requestOne->request_number)
        ->assertSeeText($requestTwo->request_number);
});

it('allows an admin to approve a manager approved request', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::MANAGER_APPROVED, 2);

    $this->actingAs($this->admin)
        ->patch(route('admin.requests.approve', $request), [
            'quantity_approved' => 2,
            'admin_comment' => 'Approved for issue.',
        ])
        ->assertRedirect(route('admin.requests.show', $request));

    $request->refresh();

    expect($request->status)->toBe(AssetRequestStatusEnum::ADMIN_APPROVED)
        ->and($request->quantity_approved)->toBe(2)
        ->and($request->admin_reviewed_by)->toBe($this->admin->id)
        ->and($request->admin_comment)->toBe('Approved for issue.')
        ->and($request->admin_reviewed_at)->not->toBeNull();
});

it('blocks an admin from approving a request that is still pending', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::PENDING, null);

    $this->actingAs($this->admin)
        ->patch(route('admin.requests.approve', $request), [
            'quantity_approved' => 1,
        ])
        ->assertForbidden();
});

it('requires the approved quantity not to exceed current available stock', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::MANAGER_APPROVED, 2);

    $this->asset->forceFill([
        'quantity_available' => 1,
    ])->save();

    $this->actingAs($this->admin)
        ->from(route('admin.requests.show', $request))
        ->patch(route('admin.requests.approve', $request), [
            'quantity_approved' => 2,
        ])
        ->assertRedirect(route('admin.requests.show', $request))
        ->assertSessionHasErrors('quantity_approved');
});

it('writes a status history entry when an admin approves a request', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::MANAGER_APPROVED, 2);

    AssetRequestStatusHistory::query()->create([
        'asset_request_id' => $request->id,
        'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
        'comment' => 'Approved by manager.',
        'acted_by' => $this->manager->id,
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($this->admin)
        ->patch(route('admin.requests.approve', $request), [
            'quantity_approved' => 2,
            'admin_comment' => 'Admin approved for issue.',
        ]);

    $latestHistory = AssetRequestStatusHistory::query()
        ->where('asset_request_id', $request->id)
        ->latest('id')
        ->firstOrFail();

    expect($latestHistory->status)->toBe(AssetRequestStatusEnum::ADMIN_APPROVED)
        ->and($latestHistory->acted_by)->toBe($this->admin->id)
        ->and($latestHistory->comment)->toBe('Admin approved for issue.');
});

it('allows an admin to reject a manager approved request', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::MANAGER_APPROVED, 2);

    $this->actingAs($this->admin)
        ->patch(route('admin.requests.reject', $request), [
            'rejection_reason' => 'Stock must be reserved for current operations.',
            'admin_comment' => 'Please revisit this request later.',
        ])
        ->assertRedirect(route('admin.requests.show', $request));

    $request->refresh();

    expect($request->status)->toBe(AssetRequestStatusEnum::REJECTED)
        ->and($request->admin_reviewed_by)->toBe($this->admin->id)
        ->and($request->admin_reviewed_at)->not->toBeNull()
        ->and($request->rejection_reason)->toBe('Stock must be reserved for current operations.')
        ->and($request->admin_comment)->toBe('Please revisit this request later.');
});

it('writes a status history entry when an admin rejects a request', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::MANAGER_APPROVED, 2);

    AssetRequestStatusHistory::query()->create([
        'asset_request_id' => $request->id,
        'status' => AssetRequestStatusEnum::MANAGER_APPROVED,
        'comment' => 'Approved by manager.',
        'acted_by' => $this->manager->id,
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($this->admin)
        ->patch(route('admin.requests.reject', $request), [
            'rejection_reason' => 'Budget is not available for this request.',
            'admin_comment' => 'Try again next cycle.',
        ]);

    $latestHistory = AssetRequestStatusHistory::query()
        ->where('asset_request_id', $request->id)
        ->latest('id')
        ->firstOrFail();

    expect($latestHistory->status)->toBe(AssetRequestStatusEnum::REJECTED)
        ->and($latestHistory->acted_by)->toBe($this->admin->id)
        ->and($latestHistory->comment)->toBe('Try again next cycle.');
});

it('shows the issue form on an admin approved request', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ADMIN_APPROVED, 2);

    $this->actingAs($this->admin)
        ->get(route('admin.requests.show', $request))
        ->assertOk()
        ->assertSeeText('Issue Asset');
});

it('requires a request to be admin approved before it can be issued', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::MANAGER_APPROVED, 2);

    $this->actingAs($this->admin)
        ->post(route('admin.issues.store', $request), [
            'quantity_issued' => 1,
            'expected_return_date' => now()->addDays(10)->toDateString(),
            'notes' => 'Trying to issue too early.',
        ])
        ->assertForbidden();
});

it('requires the issued quantity not to exceed the approved quantity', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ADMIN_APPROVED, 2);

    $this->actingAs($this->admin)
        ->from(route('admin.requests.show', $request))
        ->post(route('admin.issues.store', $request), [
            'quantity_issued' => 3,
            'expected_return_date' => now()->addDays(10)->toDateString(),
        ])
        ->assertRedirect(route('admin.requests.show', $request))
        ->assertSessionHasErrors('quantity_issued');
});

it('requires the issued quantity not to exceed available stock', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ADMIN_APPROVED, 2);

    $this->asset->forceFill([
        'quantity_available' => 1,
    ])->save();

    $this->actingAs($this->admin)
        ->from(route('admin.requests.show', $request))
        ->post(route('admin.issues.store', $request), [
            'quantity_issued' => 2,
            'expected_return_date' => now()->addDays(10)->toDateString(),
        ])
        ->assertRedirect(route('admin.requests.show', $request))
        ->assertSessionHasErrors('quantity_issued');
});

it('issues an asset, reduces stock, updates the request, and writes history', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ADMIN_APPROVED, 2);

    AssetRequestStatusHistory::query()->create([
        'asset_request_id' => $request->id,
        'status' => AssetRequestStatusEnum::ADMIN_APPROVED,
        'comment' => 'Approved by admin.',
        'acted_by' => $this->admin->id,
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.issues.store', $request), [
            'quantity_issued' => 2,
            'expected_return_date' => now()->addDays(14)->toDateString(),
            'notes' => 'Issued to user for current project.',
        ])
        ->assertRedirect();

    $issue = AssetIssue::query()->firstOrFail();
    $request->refresh();
    $this->asset->refresh();

    expect($issue->asset_request_id)->toBe($request->id)
        ->and($issue->quantity_issued)->toBe(2)
        ->and($issue->issued_to_user_id)->toBe($this->staff->id)
        ->and($issue->issued_by_user_id)->toBe($this->admin->id)
        ->and($issue->status)->toBe(AssetIssueStatusEnum::ISSUED)
        ->and($this->asset->quantity_available)->toBe(6)
        ->and($request->status)->toBe(AssetRequestStatusEnum::ISSUED);

    $latestHistory = AssetRequestStatusHistory::query()
        ->where('asset_request_id', $request->id)
        ->latest('id')
        ->firstOrFail();

    expect($latestHistory->status)->toBe(AssetRequestStatusEnum::ISSUED)
        ->and($latestHistory->acted_by)->toBe($this->admin->id);
});

it('blocks unauthorized users from issuing assets', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ADMIN_APPROVED, 2);

    $this->actingAs($this->manager)
        ->post(route('admin.issues.store', $request), [
            'quantity_issued' => 1,
        ])
        ->assertForbidden();
});

it('allows an admin to record a partial return and updates issue status and stock', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ISSUED, 2);

    $issue = AssetIssue::query()->create([
        'asset_request_id' => $request->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->admin->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 2,
        'issued_at' => now()->subDays(2),
        'expected_return_date' => now()->addDays(10),
        'notes' => 'Issued for project work.',
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $this->asset->forceFill([
        'quantity_available' => 6,
    ])->save();

    $this->actingAs($this->admin)
        ->post(route('admin.returns.store', $issue), [
            'quantity_returned' => 1,
            'condition_on_return' => ReturnConditionEnum::GOOD->value,
            'remarks' => 'Returned in good condition.',
        ])
        ->assertRedirect(route('admin.issues.show', $issue));

    $issue->refresh();
    $this->asset->refresh();

    $return = AssetReturn::query()->firstOrFail();

    expect($return->quantity_returned)->toBe(1)
        ->and($return->condition_on_return)->toBe(ReturnConditionEnum::GOOD)
        ->and($issue->status)->toBe(AssetIssueStatusEnum::PARTIALLY_RETURNED)
        ->and($this->asset->quantity_available)->toBe(7);
});

it('blocks returns above the outstanding quantity', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ISSUED, 2);

    $issue = AssetIssue::query()->create([
        'asset_request_id' => $request->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->admin->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 2,
        'issued_at' => now()->subDays(2),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $this->actingAs($this->admin)
        ->from(route('admin.issues.show', $issue))
        ->post(route('admin.returns.store', $issue), [
            'quantity_returned' => 3,
            'condition_on_return' => ReturnConditionEnum::GOOD->value,
        ])
        ->assertRedirect(route('admin.issues.show', $issue))
        ->assertSessionHasErrors('quantity_returned');
});

it('allows an admin to complete a full return and updates the request and issue statuses', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ISSUED, 2);

    AssetRequestStatusHistory::query()->create([
        'asset_request_id' => $request->id,
        'status' => AssetRequestStatusEnum::ISSUED,
        'comment' => 'Issued by admin.',
        'acted_by' => $this->admin->id,
        'created_at' => now()->subMinute(),
    ]);

    $issue = AssetIssue::query()->create([
        'asset_request_id' => $request->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->admin->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 2,
        'issued_at' => now()->subDays(2),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $this->asset->forceFill([
        'quantity_available' => 6,
    ])->save();

    $this->actingAs($this->admin)
        ->post(route('admin.returns.store', $issue), [
            'quantity_returned' => 2,
            'condition_on_return' => ReturnConditionEnum::DAMAGED->value,
            'remarks' => 'Returned with minor scratches.',
        ])
        ->assertRedirect(route('admin.issues.show', $issue));

    $issue->refresh();
    $request->refresh();
    $this->asset->refresh();

    $return = AssetReturn::query()->firstOrFail();

    expect($return->condition_on_return)->toBe(ReturnConditionEnum::DAMAGED)
        ->and($issue->status)->toBe(AssetIssueStatusEnum::RETURNED)
        ->and($request->status)->toBe(AssetRequestStatusEnum::RETURNED)
        ->and($this->asset->quantity_available)->toBe(8);

    $latestHistory = AssetRequestStatusHistory::query()
        ->where('asset_request_id', $request->id)
        ->latest('id')
        ->firstOrFail();

    expect($latestHistory->status)->toBe(AssetRequestStatusEnum::RETURNED)
        ->and($latestHistory->acted_by)->toBe($this->admin->id);
});

it('blocks unauthorized users from recording returns', function () {
    $request = ($this->createRequest)(AssetRequestStatusEnum::ISSUED, 2);

    $issue = AssetIssue::query()->create([
        'asset_request_id' => $request->id,
        'asset_id' => $this->asset->id,
        'issued_to_user_id' => $this->staff->id,
        'issued_by_user_id' => $this->admin->id,
        'department_id' => $this->itDepartment->id,
        'quantity_issued' => 2,
        'issued_at' => now()->subDays(2),
        'status' => AssetIssueStatusEnum::ISSUED,
    ]);

    $this->actingAs($this->staff)
        ->post(route('admin.returns.store', $issue), [
            'quantity_returned' => 1,
            'condition_on_return' => ReturnConditionEnum::GOOD->value,
        ])
        ->assertForbidden();
});

it('allows an admin to increase stock through an adjustment', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.adjustments.store'), [
            'asset_id' => $this->asset->id,
            'type' => StockAdjustmentTypeEnum::INCREASE->value,
            'quantity' => 3,
            'reason' => StockAdjustmentReasonEnum::RESTOCK->value,
            'reference' => 'PO-1001',
            'note' => 'Restocked from supplier.',
        ])
        ->assertRedirect(route('admin.adjustments.index'));

    $this->asset->refresh();
    $adjustment = AssetAdjustment::query()->firstOrFail();

    expect($this->asset->quantity_total)->toBe(13)
        ->and($this->asset->quantity_available)->toBe(11)
        ->and($adjustment->type)->toBe(StockAdjustmentTypeEnum::INCREASE)
        ->and($adjustment->reason)->toBe(StockAdjustmentReasonEnum::RESTOCK)
        ->and($adjustment->created_by)->toBe($this->admin->id);
});

it('allows an admin to decrease stock through an adjustment', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.adjustments.store'), [
            'asset_id' => $this->asset->id,
            'type' => StockAdjustmentTypeEnum::DECREASE->value,
            'quantity' => 2,
            'reason' => StockAdjustmentReasonEnum::DAMAGE->value,
            'note' => 'Two units damaged in storage.',
        ])
        ->assertRedirect(route('admin.adjustments.index'));

    $this->asset->refresh();
    $adjustment = AssetAdjustment::query()->firstOrFail();

    expect($this->asset->quantity_total)->toBe(8)
        ->and($this->asset->quantity_available)->toBe(6)
        ->and($adjustment->type)->toBe(StockAdjustmentTypeEnum::DECREASE)
        ->and($adjustment->quantity)->toBe(2);
});

it('blocks stock decreases that would reduce available stock below zero', function () {
    $this->asset->forceFill([
        'quantity_total' => 4,
        'quantity_available' => 1,
    ])->save();

    $this->actingAs($this->admin)
        ->from(route('admin.adjustments.index'))
        ->post(route('admin.adjustments.store'), [
            'asset_id' => $this->asset->id,
            'type' => StockAdjustmentTypeEnum::DECREASE->value,
            'quantity' => 2,
            'reason' => StockAdjustmentReasonEnum::LOSS->value,
        ])
        ->assertRedirect(route('admin.adjustments.index'))
        ->assertSessionHasErrors('quantity');
});

it('blocks unauthorized users from adjusting stock', function () {
    $this->actingAs($this->manager)
        ->post(route('admin.adjustments.store'), [
            'asset_id' => $this->asset->id,
            'type' => StockAdjustmentTypeEnum::INCREASE->value,
            'quantity' => 1,
            'reason' => StockAdjustmentReasonEnum::CORRECTION->value,
        ])
        ->assertForbidden();
});
