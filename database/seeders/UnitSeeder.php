<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // وحدات الوزن
            ['name' => 'كيلوجرام', 'code' => 'KG', 'symbol' => 'كجم', 'is_active' => true],
            ['name' => 'جرام', 'code' => 'G', 'symbol' => 'جم', 'is_active' => true],
            ['name' => 'طن', 'code' => 'TON', 'symbol' => 'طن', 'is_active' => true],

            // وحدات الطول
            ['name' => 'متر', 'code' => 'M', 'symbol' => 'م', 'is_active' => true],
            ['name' => 'سنتيمتر', 'code' => 'CM', 'symbol' => 'سم', 'is_active' => true],

            // وحدات الحجم
            ['name' => 'لتر', 'code' => 'L', 'symbol' => 'لتر', 'is_active' => true],
            ['name' => 'ملليلتر', 'code' => 'ML', 'symbol' => 'مل', 'is_active' => true],

            // وحدات العد
            ['name' => 'قطعة', 'code' => 'PC', 'symbol' => 'قطعة', 'is_active' => true],
            ['name' => 'علبة', 'code' => 'BOX', 'symbol' => 'علبة', 'is_active' => true],
            ['name' => 'كرتونة', 'code' => 'CTN', 'symbol' => 'كرتونة', 'is_active' => true],
            ['name' => 'صندوق', 'code' => 'CASE', 'symbol' => 'صندوق', 'is_active' => true],
            ['name' => 'دزينة', 'code' => 'DOZ', 'symbol' => 'دزينة', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
