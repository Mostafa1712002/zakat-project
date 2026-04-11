<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class ZatcaController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'pending_clearance' => Sale::where('zatca_status', Sale::ZATCA_STATUS_PENDING_CLEARANCE)->count(),
            'pending_reporting' => Sale::where('zatca_status', Sale::ZATCA_STATUS_PENDING_REPORTING)->count(),
            'cleared' => Sale::where('zatca_status', Sale::ZATCA_STATUS_CLEARED)->count(),
            'reported' => Sale::where('zatca_status', Sale::ZATCA_STATUS_REPORTED)->count(),
            'failed' => Sale::where('zatca_status', Sale::ZATCA_STATUS_FAILED)->count(),
        ];

        $failedInvoices = Sale::where('zatca_status', Sale::ZATCA_STATUS_FAILED)
            ->with('customer')
            ->latest('zatca_issued_at')
            ->limit(20)
            ->get();

        $pendingInvoices = Sale::whereIn('zatca_status', [
            Sale::ZATCA_STATUS_PENDING_CLEARANCE,
            Sale::ZATCA_STATUS_PENDING_REPORTING,
        ])
            ->with('customer')
            ->latest('zatca_issued_at')
            ->limit(20)
            ->get();

        $recentInvoices = Sale::whereIn('zatca_status', [
            Sale::ZATCA_STATUS_CLEARED,
            Sale::ZATCA_STATUS_REPORTED,
        ])
            ->with('customer')
            ->latest('zatca_cleared_at')
            ->limit(10)
            ->get();

        return view('zatca.dashboard', compact('stats', 'failedInvoices', 'pendingInvoices', 'recentInvoices'));
    }
}
