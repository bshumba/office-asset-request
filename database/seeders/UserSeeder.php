<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's demo users.
     */
    public function run(): void
    {
        $it = Department::query()->where('code', 'IT')->firstOrFail();
        $hr = Department::query()->where('code', 'HR')->firstOrFail();

        $admin = User::updateOrCreate(
            ['email' => 'admin@office.test'],
            [
                'name' => 'Office Admin',
                'department_id' => null,
                'password' => 'password',
                'status' => UserStatusEnum::ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'notes' => 'Primary super admin demo account.',
            ],
        );
        $admin->syncRoles(['Super Admin']);

        $itManager = User::updateOrCreate(
            ['email' => 'manager.it@office.test'],
            [
                'name' => 'IT Department Manager',
                'department_id' => $it->id,
                'password' => 'password',
                'status' => UserStatusEnum::ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'notes' => 'IT manager demo account.',
            ],
        );
        $itManager->syncRoles(['Department Manager']);

        $hrManager = User::updateOrCreate(
            ['email' => 'manager.hr@office.test'],
            [
                'name' => 'HR Department Manager',
                'department_id' => $hr->id,
                'password' => 'password',
                'status' => UserStatusEnum::ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'notes' => 'HR manager demo account.',
            ],
        );
        $hrManager->syncRoles(['Department Manager']);

        $staffOne = User::updateOrCreate(
            ['email' => 'staff1@office.test'],
            [
                'name' => 'IT Staff Member',
                'department_id' => $it->id,
                'password' => 'password',
                'status' => UserStatusEnum::ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'notes' => 'IT staff demo account.',
            ],
        );
        $staffOne->syncRoles(['Staff']);

        $staffTwo = User::updateOrCreate(
            ['email' => 'staff2@office.test'],
            [
                'name' => 'HR Staff Member',
                'department_id' => $hr->id,
                'password' => 'password',
                'status' => UserStatusEnum::ACTIVE,
                'email_verified_at' => now(),
                'last_login_at' => null,
                'notes' => 'HR staff demo account.',
            ],
        );
        $staffTwo->syncRoles(['Staff']);

        $it->forceFill(['manager_user_id' => $itManager->id])->save();
        $hr->forceFill(['manager_user_id' => $hrManager->id])->save();
    }
}
