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
            ['name' => 'كيلوجرام', 'symbol' => 'كجم', 'type' => 'weight', 'is_active' => true],
            ['name' => 'جرام', 'symbol' => 'جم', 'type' => 'weight', 'is_active' => true],
            ['name' => 'طن', 'symbol' => 'طن', 'type' => 'weight', 'is_active' => true],
            
            // وحدات الطول
            ['name' => 'متر', 'symbol' => 'م', 'type' => 'length', 'is_active' => true],
            ['name' => 'سنتيمتر', 'symbol' => 'سم', 'type' => 'length', 'is_active' => true],
            
            // وحدات الحجم
            ['name' => 'لتر', 'symbol' => 'لتر', 'type' => 'volume', 'is_active' => true],
            ['name' => 'ملليلتر', 'symbol' => 'مل', 'type' => 'volume', 'is_active' => true],
            
            // وحدات العد
            ['name' => 'قطعة', 'symbol' => 'قطعة', 'type' => 'piece', 'is_active' => true],
            ['name' => 'علبة', 'symbol' => 'علبة', 'type' => 'package', 'is_active' => true],
            ['name' => 'كرتونة', 'symbol' => 'كرتونة', 'type' => 'package', 'is_active' => true],
            ['name' => 'صندوق', 'symbol' => 'صندوق', 'type' => 'package', 'is_active' => true],
            ['name' => 'دزينة', 'symbol' => 'دزينة', 'type' => 'piece', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
