<?php

namespace App\Domain\Customer\Validators;

use App\Domain\Customer\Models\Customer;

/**
 * ZatcaCustomerValidator — gate-checks whether a Customer's data is
 * complete enough to issue a ZATCA-compliant tax invoice.
 *
 * Returns a structured ['ok' => bool, 'errors' => string[]] response
 * so callers can surface every blocker at once.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-010)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 4.2)
 */
class ZatcaCustomerValidator
{
    public const VAT_PATTERN = '/^3\d{13}3$/';

    /**
     * @return array{ok: bool, errors: list<string>}
     */
    public static function isReadyForInvoicing(Customer $customer): array
    {
        $errors = [];

        // VAT only required for taxable companies.
        $needsVat = $customer->type === Customer::TYPE_COMPANY && ! $customer->is_tax_exempt;
        if ($needsVat) {
            if (empty($customer->vat_number)) {
                $errors[] = 'VAT number is required for taxable companies.';
            } elseif (! preg_match(self::VAT_PATTERN, (string) $customer->vat_number)) {
                $errors[] = 'VAT number must be 15 digits and start and end with 3.';
            }
        }

        // REGA address completeness — ZATCA requires these for any invoice.
        $requiredAddress = [
            'street_name'     => 'Street name is required.',
            'building_number' => 'Building number is required.',
            'district'        => 'District is required.',
            'city'            => 'City is required.',
            'postal_code'     => 'Postal code is required.',
        ];

        foreach ($requiredAddress as $field => $message) {
            if (empty($customer->{$field})) {
                $errors[] = $message;
            }
        }

        return [
            'ok'     => $errors === [],
            'errors' => $errors,
        ];
    }
}
