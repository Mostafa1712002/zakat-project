<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\ServiceType;
use Illuminate\Database\Seeder;

/**
 * Phase 3 service-type seeder — 6 default categories for AMMRK.
 *
 * Single name column (Arabic) — no name_en/name_ar split per user spec.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 3.2)
 *            .kiro/specs/ammrk-platform/requirements.md (US-001)
 */
class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'المعارض',       'icon' => 'fa-building',     'sort_order' => 1],
            ['name' => 'المؤتمرات',     'icon' => 'fa-microphone',   'sort_order' => 2],
            ['name' => 'الفعاليات',     'icon' => 'fa-calendar-star','sort_order' => 3],
            ['name' => 'الحلول التقنية', 'icon' => 'fa-laptop-code',  'sort_order' => 4],
            ['name' => 'الضيافة',       'icon' => 'fa-utensils',     'sort_order' => 5],
            ['name' => 'الإعلام',        'icon' => 'fa-video',        'sort_order' => 6],
        ];

        foreach ($types as $type) {
            ServiceType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'icon' => $type['icon'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
