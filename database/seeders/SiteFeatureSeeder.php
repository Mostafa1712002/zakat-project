<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class SiteFeatureSeeder extends Seeder
{
    /**
     * Site profiles: which features are ON/OFF per site
     */
    protected array $profiles = [
        'rogence' => [
            'customer_target'        => true,
            'tile_area_tracking'     => false,
            'grade_system'           => false,
            'per_item_discount'      => false,
            'auto_cash_payment'      => false,
            'color_palette'          => false,
            'invoice_customization'  => false,
            'simple_invoice_numbers' => false,
        ],
        'syramik' => [
            'customer_target'        => false,
            'tile_area_tracking'     => true,
            'grade_system'           => true,
            'per_item_discount'      => true,
            'auto_cash_payment'      => true,
            'color_palette'          => true,
            'invoice_customization'  => true,
            'simple_invoice_numbers' => true,
        ],
        'demo-sibakuh' => [
            'customer_target'        => false,
            'tile_area_tracking'     => true,
            'grade_system'           => true,
            'per_item_discount'      => true,
            'auto_cash_payment'      => true,
            'color_palette'          => true,
            'invoice_customization'  => true,
            'simple_invoice_numbers' => true,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siteProfile = env('SITE_PROFILE', 'rogence');

        if (!isset($this->profiles[$siteProfile])) {
            $this->command->warn("Unknown SITE_PROFILE: {$siteProfile}. Using 'rogence' as default.");
            $siteProfile = 'rogence';
        }

        $this->command->info("Applying feature profile: {$siteProfile}");

        foreach ($this->profiles[$siteProfile] as $featureName => $enabled) {
            $feature = Feature::where('name', $featureName)->first();

            if ($feature) {
                $feature->update(['is_enabled' => $enabled]);
                $status = $enabled ? '✅ ON' : '❌ OFF';
                $this->command->line("  {$status} {$featureName}");
            } else {
                $this->command->warn("  ⚠️  Feature not found: {$featureName}");
            }
        }

        // Clear feature cache
        Feature::clearAllCache();

        $this->command->info("Feature profile '{$siteProfile}' applied successfully.");
    }
}
