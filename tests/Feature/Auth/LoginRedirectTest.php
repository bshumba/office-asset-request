<?php

use App\Enums\UserStatusEnum;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('redirects super admins to the admin dashboard after login', function () {
    $admin = User::factory()->create([
        'email' => 'admin.test@example.com',
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $admin->assignRole('Super Admin');

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Super Admin Dashboard');
});

it('redirects department managers to the manager dashboard after login', function () {
    $manager = User::factory()->create([
        'email' => 'manager.test@example.com',
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $manager->assignRole('Department Manager');

    $department = Department::query()->create([
        'name' => 'Testing Department',
        'code' => 'TST',
        'manager_user_id' => $manager->id,
        'is_active' => true,
    ]);

    $manager->forceFill([
        'department_id' => $department->id,
    ])->save();

    $this->post(route('login.store'), [
        'email' => $manager->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertRedirect(route('manager.dashboard'));

    $this->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSeeText('Manager Dashboard')
        ->assertSeeText('Testing Department');
});

it('redirects staff users to the staff dashboard after login', function () {
    $staff = User::factory()->create([
        'email' => 'staff.test@example.com',
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $staff->assignRole('Staff');

    $department = Department::query()->create([
        'name' => 'People Operations',
        'code' => 'POP',
        'is_active' => true,
    ]);

    $staff->forceFill([
        'department_id' => $department->id,
    ])->save();

    $this->post(route('login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertRedirect(route('staff.dashboard'));

    $this->get(route('staff.dashboard'))
        ->assertOk()
        ->assertSeeText('Staff Dashboard')
        ->assertSeeText('People Operations');
});
