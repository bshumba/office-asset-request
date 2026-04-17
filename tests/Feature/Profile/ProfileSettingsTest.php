<?php

use App\Enums\UserStatusEnum;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $department = Department::query()->create([
        'name' => 'Information Technology',
        'code' => 'IT',
        'is_active' => true,
    ]);

    $this->user = User::factory()->create([
        'department_id' => $department->id,
        'status' => UserStatusEnum::ACTIVE,
        'password' => 'password123',
    ]);
    $this->user->assignRole('Staff');
});

it('shows the profile settings page to signed-in users', function () {
    $this->actingAs($this->user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSeeText('Profile & Settings');
});

it('lets signed-in users update their profile details', function () {
    $this->actingAs($this->user)
        ->patch(route('profile.update'), [
            'name' => 'Updated Staff Member',
            'email' => 'updated.staff@office.test',
        ])
        ->assertRedirect(route('profile.edit'));

    expect($this->user->fresh()->name)->toBe('Updated Staff Member');
    expect($this->user->fresh()->email)->toBe('updated.staff@office.test');
});

it('lets signed-in users update their password', function () {
    $this->actingAs($this->user)
        ->patch(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])
        ->assertRedirect(route('profile.edit'));

    expect(Hash::check('new-password123', $this->user->fresh()->password))->toBeTrue();
});
