<?php

use App\Enums\UserStatusEnum;
use App\Models\Department;
use App\Models\NotificationLog;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->itDepartment = Department::query()->create([
        'name' => 'Information Technology',
        'code' => 'IT',
        'is_active' => true,
    ]);

    $this->operationsDepartment = Department::query()->create([
        'name' => 'Operations',
        'code' => 'OPS',
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->admin->assignRole('Super Admin');

    $this->staff = User::factory()->create([
        'department_id' => $this->itDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $this->staff->assignRole('Staff');
});

it('lets admins open the user creation workspace', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSeeText('Add Team Member');
});

it('lets admins create a staff account and writes an account notification', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'New Staff Member',
            'email' => 'new.staff@office.test',
            'role' => 'Staff',
            'department_id' => $this->itDepartment->id,
            'status' => UserStatusEnum::ACTIVE->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'notes' => 'Created by admin for onboarding.',
        ])
        ->assertRedirect(route('admin.users.index'));

    $createdUser = User::query()->where('email', 'new.staff@office.test')->firstOrFail();

    expect($createdUser->department_id)->toBe($this->itDepartment->id);
    expect($createdUser->status)->toBe(UserStatusEnum::ACTIVE);
    expect($createdUser->hasRole('Staff'))->toBeTrue();

    $this->assertDatabaseHas('notification_logs', [
        'user_id' => $createdUser->id,
        'type' => 'account.created',
        'title' => 'Your account is ready',
    ]);
});

it('lets admins create a manager account and connects the department manager slot when empty', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Ops Manager',
            'email' => 'ops.manager@office.test',
            'role' => 'Department Manager',
            'department_id' => $this->operationsDepartment->id,
            'status' => UserStatusEnum::ACTIVE->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'notes' => 'Operations lead.',
        ])
        ->assertRedirect(route('admin.users.index'));

    $manager = User::query()->where('email', 'ops.manager@office.test')->firstOrFail();

    expect($manager->hasRole('Department Manager'))->toBeTrue();
    expect($this->operationsDepartment->fresh()->manager_user_id)->toBe($manager->id);
});

it('lets admins update a managed account and reassign its manager slot', function () {
    $manager = User::factory()->create([
        'department_id' => $this->itDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $manager->assignRole('Department Manager');

    $this->itDepartment->forceFill([
        'manager_user_id' => $manager->id,
    ])->save();

    $this->actingAs($this->admin)
        ->patch(route('admin.users.update', $manager), [
            'name' => 'Operations Lead',
            'email' => $manager->email,
            'role' => 'Department Manager',
            'department_id' => $this->operationsDepartment->id,
            'status' => UserStatusEnum::ACTIVE->value,
            'password' => '',
            'password_confirmation' => '',
            'notes' => 'Transferred to operations.',
        ])
        ->assertRedirect(route('admin.users.edit', $manager));

    $manager->refresh();

    expect($manager->name)->toBe('Operations Lead');
    expect($manager->department_id)->toBe($this->operationsDepartment->id);
    expect($manager->hasRole('Department Manager'))->toBeTrue();
    expect($this->itDepartment->fresh()->manager_user_id)->toBeNull();
    expect($this->operationsDepartment->fresh()->manager_user_id)->toBe($manager->id);

    $this->assertDatabaseHas('notification_logs', [
        'user_id' => $manager->id,
        'type' => 'account.updated',
        'title' => 'Your account was updated',
    ]);
});

it('lets admins deactivate a managed account and clears any manager slot', function () {
    $manager = User::factory()->create([
        'department_id' => $this->operationsDepartment->id,
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $manager->assignRole('Department Manager');

    $this->operationsDepartment->forceFill([
        'manager_user_id' => $manager->id,
    ])->save();

    $this->actingAs($this->admin)
        ->patch(route('admin.users.deactivate', $manager))
        ->assertRedirect(route('admin.users.index'));

    expect($manager->fresh()->status)->toBe(UserStatusEnum::INACTIVE);
    expect($this->operationsDepartment->fresh()->manager_user_id)->toBeNull();
});

it('does not let admins deactivate their own account from team management', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.users.index'))
        ->patch(route('admin.users.deactivate', $this->admin))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasErrors('user');
});

it('blocks staff users from opening the admin user workspace', function () {
    $this->actingAs($this->staff)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});
