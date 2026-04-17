<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    Route::middleware(['web', 'auth', 'permission:users.view'])
        ->get('/__test/permissions/users-view', fn () => 'ok')
        ->name('test.permissions.users-view');
});

it('allows a user with the required permission through the permission middleware alias', function () {
    $admin = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)
        ->get('/__test/permissions/users-view')
        ->assertOk()
        ->assertSeeText('ok');
});

it('blocks a user without the required permission', function () {
    $staff = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $staff->assignRole('Staff');

    $this->actingAs($staff)
        ->get('/__test/permissions/users-view')
        ->assertForbidden();
});
