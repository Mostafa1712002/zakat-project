<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin Settings controller for AMMRK platform.
 *
 * Provides simple admin pages to view and update general invoicing
 * defaults and ZATCA environment configuration.
 *
 * Reference: .kiro/specs/ammrk-platform/design.md#Settings
 */
class SettingsController extends Controller
{
    /**
     * General invoicing settings page.
     */
    public function general(): View
    {
        $settings = [
            'default_tax_rate' => Setting::get('default_tax_rate', 15),
            'invoice_number_prefix' => Setting::get('invoice_number_prefix', 'INV-'),
            'quote_number_prefix' => Setting::get('quote_number_prefix', 'Q-'),
            'payment_number_prefix' => Setting::get('payment_number_prefix', 'PAY-'),
            'invoice_due_days' => Setting::get('invoice_due_days', 30),
            'quote_validity_days' => Setting::get('quote_validity_days', 14),
        ];

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Update general invoicing settings.
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_number_prefix' => ['required', 'string', 'max:20'],
            'quote_number_prefix' => ['required', 'string', 'max:20'],
            'payment_number_prefix' => ['required', 'string', 'max:20'],
            'invoice_due_days' => ['required', 'integer', 'min:0', 'max:365'],
            'quote_validity_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        Setting::put('default_tax_rate', $validated['default_tax_rate'], 'decimal', 'general');
        Setting::put('invoice_number_prefix', $validated['invoice_number_prefix'], 'string', 'general');
        Setting::put('quote_number_prefix', $validated['quote_number_prefix'], 'string', 'general');
        Setting::put('payment_number_prefix', $validated['payment_number_prefix'], 'string', 'general');
        Setting::put('invoice_due_days', $validated['invoice_due_days'], 'int', 'general');
        Setting::put('quote_validity_days', $validated['quote_validity_days'], 'int', 'general');

        return redirect()
            ->route('admin.settings.general')
            ->with('success', 'تم حفظ الإعدادات العامة بنجاح');
    }

    /**
     * ZATCA settings page.
     */
    public function zatca(): View
    {
        $settings = [
            'zatca_environment' => Setting::get('zatca_environment', 'production'),
        ];

        return view('admin.settings.zatca', compact('settings'));
    }

    /**
     * Update ZATCA settings.
     */
    public function updateZatca(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'zatca_environment' => ['required', 'in:sandbox,simulation,production'],
        ]);

        Setting::put('zatca_environment', $validated['zatca_environment'], 'string', 'zatca');

        return redirect()
            ->route('admin.settings.zatca')
            ->with('success', 'تم حفظ إعدادات ZATCA بنجاح');
    }
}
