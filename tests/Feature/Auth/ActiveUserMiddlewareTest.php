<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('blocks inactive users during login', function () {
    $inactiveUser = User::factory()->create([
        'email' => 'inactive.test@example.com',
        'status' => UserStatusEnum::INACTIVE,
    ]);
    $inactiveUser->assignRole('Staff');

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $inactiveUser->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out inactive users who already have an authenticated session', function () {
    $inactiveUser = User::factory()->create([
        'status' => UserStatusEnum::SUSPENDED,
    ]);
    $inactiveUser->assignRole('Staff');

    $this->actingAs($inactiveUser)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
