<?php

namespace Database\Seeders;

use App\Enums\AssetStatusEnum;
use App\Enums\AssetUnitTypeEnum;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Seed the application's assets.
     */
    public function run(): void
    {
        $it = Department::query()->where('code', 'IT')->first();
        $ops = Department::query()->where('code', 'OPS')->first();
        $finance = Department::query()->where('code', 'FIN')->first();

        $assets = [
            [
                'category_slug' => 'laptops',
                'department_id' => $it?->id,
                'name' => 'Dell Latitude 5440',
                'slug' => 'dell-latitude-5440',
                'asset_code' => 'AST-LAP-001',
                'brand' => 'Dell',
                'model' => 'Latitude 5440',
                'serial_number' => 'DL5440-001',
                'description' => 'Standard issue business laptop for office staff.',
                'unit_type' => AssetUnitTypeEnum::PIECE,
                'quantity_total' => 12,
                'quantity_available' => 9,
                'reorder_level' => 3,
                'track_serial' => true,
                'status' => AssetStatusEnum::ACTIVE,
                'purchase_date' => '2025-09-10',
                'notes' => 'Primary laptop model for IT and admin teams.',
            ],
            [
                'category_slug' => 'laptops',
                'department_id' => $it?->id,
                'name' => 'HP EliteBook 840',
                'slug' => 'hp-elitebook-840',
                'asset_code' => 'AST-LAP-002',
                'brand' => 'HP',
                'model' => 'EliteBook 840',
                'serial_number' => 'HPE840-001',
                'description' => 'Alternate business laptop model for management requests.',
                'unit_type' => AssetUnitTypeEnum::PIECE,
                'quantity_total' => 6,
                'quantity_available' => 4,
                'reorder_level' => 2,
                'track_serial' => true,
                'status' => AssetStatusEnum::ACTIVE,
                'purchase_date' => '2025-10-05',
                'notes' => 'Reserved for manager and executive requests.',
            ],
            [
                'category_slug' => 'monitors',
                'department_id' => $it?->id,
                'name' => 'Dell 24 Monitor',
                'slug' => 'dell-24-monitor',
                'asset_code' => 'AST-MON-001',
                'brand' => 'Dell',
                'model' => 'P2422H',
                'serial_number' => null,
                'description' => '24-inch office monitor for standard workstations.',
                'unit_type' => AssetUnitTypeEnum::PIECE,
                'quantity_total' => 20,
                'quantity_available' => 11,
                'reorder_level' => 5,
                'track_serial' => false,
                'status' => AssetStatusEnum::ACTIVE,
                'purchase_date' => '2025-08-18',
                'notes' => 'Shared monitor inventory across the office.',
            ],
            [
                'category_slug' => 'chairs',
                'department_id' => $ops?->id,
                'name' => 'Ergonomic Chair',
                'slug' => 'ergonomic-chair',
                'asset_code' => 'AST-CHR-001',
                'brand' => 'ErgoFlex',
                'model' => 'Comfort Pro',
                'serial_number' => null,
                'description' => 'Adjustable ergonomic chair for office staff.',
                'unit_type' => AssetUnitTypeEnum::PIECE,
                'quantity_total' => 15,
                'quantity_available' => 8,
                'reorder_level' => 4,
                'track_serial' => false,
                'status' => AssetStatusEnum::ACTIVE,
                'purchase_date' => '2025-07-02',
                'notes' => 'Managed by operations for facilities-related requests.',
            ],
            [
                'category_slug' => 'keyboards',
                'department_id' => $it?->id,
                'name' => 'Logitech Keyboard',
                'slug' => 'logitech-keyboard',
                'asset_code' => 'AST-KEY-001',
                'brand' => 'Logitech',
                'model' => 'K120',
                'serial_number' => null,
                'description' => 'Standard USB keyboard for office desks.',
                'unit_type' => AssetUnitTypeEnum::PIECE,
                'quantity_total' => 30,
                'quantity_available' => 18,
                'reorder_level' => 10,
                'track_serial' => false,
                'status' => AssetStatusEnum::ACTIVE,
                'purchase_date' => '2025-06-15',
                'notes' => 'Common accessory item with higher request volume.',
            ],
            [
                'category_slug' => 'printers',
                'department_id' => $finance?->id,
                'name' => 'HP LaserJet Printer',
                'slug' => 'hp-laserjet-printer',
                'asset_code' => 'AST-PRN-001',
                'brand' => 'HP',
                'model' => 'LaserJet Pro M404',
                'serial_number' => 'HPLJ404-001',
                'description' => 'Department printer for shared document workflows.',
                'unit_type' => AssetUnitTypeEnum::PIECE,
                'quantity_total' => 4,
                'quantity_available' => 2,
                'reorder_level' => 1,
                'track_serial' => true,
                'status' => AssetStatusEnum::ACTIVE,
                'purchase_date' => '2025-05-20',
                'notes' => 'Assigned primarily to finance and admin operations.',
            ],
        ];

        foreach ($assets as $asset) {
            $category = AssetCategory::query()->where('slug', $asset['category_slug'])->firstOrFail();

            Asset::updateOrCreate(
                ['asset_code' => $asset['asset_code']],
                [
                    'asset_category_id' => $category->id,
                    'department_id' => $asset['department_id'],
                    'name' => $asset['name'],
                    'slug' => $asset['slug'],
                    'brand' => $asset['brand'],
                    'model' => $asset['model'],
                    'serial_number' => $asset['serial_number'],
                    'description' => $asset['description'],
                    'unit_type' => $asset['unit_type'],
                    'quantity_total' => $asset['quantity_total'],
                    'quantity_available' => $asset['quantity_available'],
                    'reorder_level' => $asset['reorder_level'],
                    'track_serial' => $asset['track_serial'],
                    'status' => $asset['status'],
                    'purchase_date' => $asset['purchase_date'],
                    'notes' => $asset['notes'],
                ],
            );
        }
    }
}
