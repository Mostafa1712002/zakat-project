<?php

namespace App\Domain\Sales\Policies;

use App\Domain\Sales\Models\Invoice;
use App\Models\User;

/**
 * InvoicePolicy — Spatie permissions plus issuance-state enforcement.
 *
 * Account Manager: can view their own invoices (read-only).
 * Accountant / Admin: full lifecycle (create, issue, send-zatca, cancel).
 *
 * Critical rule: invoices are immutable after issuance. `update` is denied
 * once status leaves `draft`.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 5.5)
 */
class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny(['invoices.view-own', 'invoices.view-all']);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->can('invoices.view-all')) {
            return true;
        }

        if ($user->can('invoices.view-own')) {
            return $invoice->created_by === $user->getAuthIdentifier();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.edit')
            && $invoice->status === Invoice::STATUS_DRAFT;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->cancel($user, $invoice);
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.issue')
            && $invoice->status === Invoice::STATUS_DRAFT;
    }

    public function sendZatca(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.send-zatca')
            && in_array($invoice->status, [Invoice::STATUS_ISSUED, Invoice::STATUS_PAID_PARTIAL, Invoice::STATUS_PAID], true)
            && $invoice->zatca_status !== Invoice::ZATCA_CLEARED;
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->can('invoices.cancel')
            && in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED], true);
    }
}
