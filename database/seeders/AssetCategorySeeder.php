<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    /**
     * Seed the application's asset categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'description' => 'Portable computing devices for staff and managers.',
            ],
            [
                'name' => 'Monitors',
                'slug' => 'monitors',
                'description' => 'Desktop display screens for office workstations.',
            ],
            [
                'name' => 'Chairs',
                'slug' => 'chairs',
                'description' => 'Office and ergonomic seating.',
            ],
            [
                'name' => 'Keyboards',
                'slug' => 'keyboards',
                'description' => 'Input peripherals and accessories.',
            ],
            [
                'name' => 'Printers',
                'slug' => 'printers',
                'description' => 'Office printing hardware and devices.',
            ],
        ];

        foreach ($categories as $category) {
            AssetCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    ...$category,
                    'is_active' => true,
                ],
            );
        }
    }
}
