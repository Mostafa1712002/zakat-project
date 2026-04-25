<?php

namespace App\Http\Controllers;

class ZatcaController extends Controller
{
    /**
     * NOTE: Phase 1 cleanup stub.
     * Original ZATCA dashboard read from `sales.zatca_status`.
     * Will be re-wired against the new `invoices` table in Phase 5 (Sales / ZATCA submission).
     */
    public function dashboard()
    {
        $stats = [
            'pending_clearance' => 0,
            'pending_reporting' => 0,
            'cleared' => 0,
            'reported' => 0,
            'failed' => 0,
        ];

        $failedInvoices = collect();
        $pendingInvoices = collect();
        $recentInvoices = collect();

        return view('zatca.dashboard', compact('stats', 'failedInvoices', 'pendingInvoices', 'recentInvoices'));
    }
}
