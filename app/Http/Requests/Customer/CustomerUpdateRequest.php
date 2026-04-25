<?php

namespace App\Http\Requests\Customer;

use App\Domain\Customer\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation rules for updating a Customer.
 *
 * Reference: .kiro/specs/ammrk-platform/requirements.md (US-010)
 *            .kiro/specs/ammrk-platform/tasks.md (Task 4.2)
 */
class CustomerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $customer instanceof Customer
            && $this->user()?->can('update', $customer) === true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'type'               => ['required', Rule::in([
                Customer::TYPE_COMPANY,
                Customer::TYPE_GOVERNMENT,
                Customer::TYPE_INDIVIDUAL,
            ])],
            'vat_number'         => [
                Rule::requiredIf(fn () => $this->input('type') === Customer::TYPE_COMPANY
                    && ! $this->boolean('is_tax_exempt')),
                'nullable',
                'string',
                'regex:/^3\d{13}3$/',
            ],
            'cr_number'          => ['nullable', 'string', 'max:20'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'email'              => ['nullable', 'email', 'max:255'],
            'is_tax_exempt'      => ['nullable', 'boolean'],
            'account_manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'branch_id'          => ['nullable', 'integer', 'exists:branches,id'],

            // REGA address fields
            'street_name'        => ['nullable', 'string', 'max:127'],
            'building_number'    => ['nullable', 'string', 'regex:/^\d{4}$/'],
            'secondary_number'   => ['nullable', 'string', 'regex:/^\d{4}$/'],
            'district'           => ['nullable', 'string', 'max:127'],
            'city'               => ['nullable', 'string', 'max:127'],
            'postal_code'        => ['nullable', 'string', 'regex:/^\d{5}$/'],
            'country_code'       => ['nullable', 'string', 'size:2'],

            'notes'              => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vat_number.regex'       => 'الرقم الضريبي يجب أن يتكون من 15 رقماً ويبدأ وينتهي بـ 3.',
            'building_number.regex'  => 'رقم المبنى يجب أن يكون 4 أرقام.',
            'secondary_number.regex' => 'الرقم الفرعي يجب أن يكون 4 أرقام.',
            'postal_code.regex'      => 'الرمز البريدي يجب أن يتكون من 5 أرقام.',
        ];
    }
}
