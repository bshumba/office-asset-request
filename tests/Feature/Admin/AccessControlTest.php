<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->admin = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->admin->assignRole('Super Admin');

    $this->staff = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->staff->assignRole('Staff');
});

it('lets admins create a new role with selected permissions', function () {
    $permissions = Permission::query()
        ->whereIn('name', ['reports.view', 'issues.view'])
        ->pluck('id')
        ->all();

    $this->actingAs($this->admin)
        ->post(route('admin.roles.store'), [
            'name' => 'Store Officer',
            'permissions' => $permissions,
        ])
        ->assertRedirect();

    $role = Role::query()->where('name', 'Store Officer')->firstOrFail();

    expect($role->hasPermissionTo('reports.view'))->toBeTrue();
    expect($role->hasPermissionTo('issues.view'))->toBeTrue();
});

it('lets admins create a permission and assign it to roles immediately', function () {
    $staffRole = Role::findByName('Staff', 'web');

    $this->actingAs($this->admin)
        ->post(route('admin.permissions.store'), [
            'name' => 'support.tickets-view',
            'roles' => [$staffRole->id],
        ])
        ->assertRedirect(route('admin.permissions.index'));

    $permission = Permission::query()->where('name', 'support.tickets-view')->firstOrFail();

    expect($staffRole->fresh()->hasPermissionTo($permission))->toBeTrue();
});

it('lets admins update an existing roles permission assignment', function () {
    $role = Role::findByName('Staff', 'web');
    $permission = Permission::findByName('reports.view', 'web');

    $this->actingAs($this->admin)
        ->patch(route('admin.roles.update', $role), [
            'name' => $role->name,
            'permissions' => array_merge(
                $role->permissions->pluck('id')->all(),
                [$permission->id],
            ),
        ])
        ->assertRedirect(route('admin.roles.edit', $role));

    expect($role->fresh()->hasPermissionTo('reports.view'))->toBeTrue();
});

it('keeps system role names unchanged when updating their permissions', function () {
    $role = Role::findByName('Staff', 'web');

    $this->actingAs($this->admin)
        ->patch(route('admin.roles.update', $role), [
            'name' => 'Renamed Staff Role',
            'permissions' => $role->permissions->pluck('id')->all(),
        ])
        ->assertRedirect(route('admin.roles.edit', $role));

    expect($role->fresh()->name)->toBe('Staff');
});

it('blocks staff users from opening the access control workspace', function () {
    $this->actingAs($this->staff)
        ->get(route('admin.roles.index'))
        ->assertForbidden();
});
