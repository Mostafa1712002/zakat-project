<?php

namespace Database\Seeders;

use App\Domain\Treasury\Models\Treasury;
use Illuminate\Database\Seeder;

class TreasurySeeder extends Seeder
{
    public function run(): void
    {
        Treasury::firstOrCreate(
            ['name' => 'الخزينة الرئيسية'],
            ['type' => 'cash', 'balance' => 0, 'is_active' => true]
        );
    }
}
