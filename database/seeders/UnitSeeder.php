<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Phase 3 unit seeder — service measurement units.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 3.2)
 */
class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'يوم'],
            ['name' => 'فعالية'],
            ['name' => 'باقة'],
            ['name' => 'شهر'],
            ['name' => 'ساعة'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['name' => $unit['name']],
                ['is_active' => true]
            );
        }
    }
}
