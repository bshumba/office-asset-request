<?php

use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use App\Enums\UserStatusEnum;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->admin = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->admin->assignRole('Super Admin');

    $this->department = Department::query()->create([
        'name' => 'Information Technology',
        'code' => 'IT',
        'is_active' => true,
    ]);

    $this->assetCategory = AssetCategory::query()->create([
        'name' => 'Laptops',
        'slug' => 'laptops',
        'is_active' => true,
    ]);
});

it('lets admins create, update, and delete departments', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.departments.store'), [
            'name' => 'Operations',
            'code' => 'OPS',
            'description' => 'Operations department',
            'is_active' => '1',
        ])
        ->assertRedirect();

    $department = Department::query()->where('code', 'OPS')->firstOrFail();

    $this->actingAs($this->admin)
        ->patch(route('admin.departments.update', $department), [
            'name' => 'Operations & Logistics',
            'code' => 'OPS',
            'description' => 'Updated department',
        ])
        ->assertRedirect(route('admin.departments.edit', $department));

    expect($department->fresh()->name)->toBe('Operations & Logistics');
    expect($department->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)
        ->delete(route('admin.departments.destroy', $department))
        ->assertRedirect(route('admin.departments.index'));

    expect(Department::query()->find($department->id))->toBeNull();
    expect(Department::withTrashed()->whereKey($department->id)->exists())->toBeTrue();
});

it('blocks deleting a department when related records exist', function () {
    User::factory()->create([
        'department_id' => $this->department->id,
        'status' => UserStatusEnum::ACTIVE,
    ])->assignRole('Staff');

    $this->actingAs($this->admin)
        ->from(route('admin.departments.index'))
        ->delete(route('admin.departments.destroy', $this->department))
        ->assertRedirect(route('admin.departments.index'))
        ->assertSessionHasErrors('department');
});

it('lets admins create, update, and delete asset categories', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.asset-categories.store'), [
            'name' => 'Monitors',
            'description' => 'Display equipment',
            'is_active' => '1',
        ])
        ->assertRedirect();

    $category = AssetCategory::query()->where('name', 'Monitors')->firstOrFail();

    $this->actingAs($this->admin)
        ->patch(route('admin.asset-categories.update', $category), [
            'name' => 'Screens',
            'description' => 'Updated category',
        ])
        ->assertRedirect(route('admin.asset-categories.edit', $category));

    expect($category->fresh()->name)->toBe('Screens');
    expect($category->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)
        ->delete(route('admin.asset-categories.destroy', $category))
        ->assertRedirect(route('admin.asset-categories.index'));

    expect(AssetCategory::withTrashed()->whereKey($category->id)->exists())->toBeTrue();
});

it('lets admins create, update, and delete assets', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.assets.store'), [
            'asset_category_id' => $this->assetCategory->id,
            'department_id' => $this->department->id,
            'name' => 'Dell Latitude',
            'asset_code' => 'AST-CRUD-100',
            'unit_type' => AssetUnitTypeEnum::PIECE->value,
            'quantity_total' => 10,
            'quantity_available' => 8,
            'reorder_level' => 2,
            'track_serial' => '1',
            'status' => AssetStatusEnum::ACTIVE->value,
            'brand' => 'Dell',
            'model' => 'Latitude',
        ])
        ->assertRedirect();

    $asset = Asset::query()->where('asset_code', 'AST-CRUD-100')->firstOrFail();

    $this->actingAs($this->admin)
        ->patch(route('admin.assets.update', $asset), [
            'asset_category_id' => $this->assetCategory->id,
            'department_id' => $this->department->id,
            'name' => 'Dell Latitude 7450',
            'asset_code' => 'AST-CRUD-100',
            'unit_type' => AssetUnitTypeEnum::PIECE->value,
            'quantity_total' => 12,
            'quantity_available' => 9,
            'reorder_level' => 3,
            'status' => AssetStatusEnum::ACTIVE->value,
            'brand' => 'Dell',
            'model' => '7450',
        ])
        ->assertRedirect(route('admin.assets.edit', $asset));

    expect($asset->fresh()->name)->toBe('Dell Latitude 7450');
    expect($asset->fresh()->quantity_total)->toBe(12);
    expect($asset->fresh()->track_serial)->toBeFalse();

    $this->actingAs($this->admin)
        ->delete(route('admin.assets.destroy', $asset))
        ->assertRedirect(route('admin.assets.index'));

    expect(Asset::withTrashed()->whereKey($asset->id)->exists())->toBeTrue();
});
