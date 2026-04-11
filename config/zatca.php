<?php

return [
    'environment' => env('ZATCA_ENVIRONMENT', 'sandbox'),

    'endpoints' => [
        'sandbox' => [
            'base_url' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal',
            'compliance' => '/compliance/invoices',
            'clearance' => '/invoices/clearance/single',
            'reporting' => '/invoices/reporting/single',
        ],
        'simulation' => [
            'base_url' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation',
            'compliance' => '/compliance/invoices',
            'clearance' => '/invoices/clearance/single',
            'reporting' => '/invoices/reporting/single',
        ],
        'production' => [
            'base_url' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core',
            'compliance' => '/compliance/invoices',
            'clearance' => '/invoices/clearance/single',
            'reporting' => '/invoices/reporting/single',
        ],
    ],

    'timeout' => 30,
    'retry_attempts' => 3,
    'retry_delay' => 5,
];
