<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed AMMRK platform default settings.
     *
     * Reference: .kiro/specs/ammrk-platform/design.md#Settings
     */
    public function run(): void
    {
        $defaults = [
            // General — invoicing
            ['key' => 'default_tax_rate', 'value' => '15', 'type' => 'decimal', 'group' => 'general'],
            ['key' => 'invoice_number_prefix', 'value' => 'INV-', 'type' => 'string', 'group' => 'general'],
            ['key' => 'quote_number_prefix', 'value' => 'Q-', 'type' => 'string', 'group' => 'general'],
            ['key' => 'payment_number_prefix', 'value' => 'PAY-', 'type' => 'string', 'group' => 'general'],
            ['key' => 'invoice_due_days', 'value' => '30', 'type' => 'int', 'group' => 'general'],
            ['key' => 'quote_validity_days', 'value' => '14', 'type' => 'int', 'group' => 'general'],

            // ZATCA
            ['key' => 'zatca_environment', 'value' => 'production', 'type' => 'string', 'group' => 'zatca'],
        ];

        foreach ($defaults as $row) {
            Setting::put($row['key'], $row['value'], $row['type'], $row['group']);
        }
    }
}
