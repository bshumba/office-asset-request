<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the application's departments.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'IT',
                'code' => 'IT',
                'description' => 'Information technology and infrastructure support.',
            ],
            [
                'name' => 'HR',
                'code' => 'HR',
                'description' => 'Human resources and people operations.',
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Finance and accounting operations.',
            ],
            [
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'Office operations and facilities support.',
            ],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                [
                    ...$department,
                    'is_active' => true,
                ],
            );
        }
    }
}
