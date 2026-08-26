<?php

return [
    'enabled' => (bool) env('ANALYTICS_ENABLED', true),

    /*
     * IPs propias (oficina, staging) separadas por coma. No se guardan IPs en BD.
     */
    'internal_ips' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ANALYTICS_INTERNAL_IPS', ''))
    ))),

    'funnel' => [
        'landing_view',
        'signup_cta_click',
        'registration_started',
        'registration_completed',
        'first_quote_created',
        'quote_to_invoice',
        'first_invoice_created',
        'pdf_generated',
        'invoice_email_sent',
        'verifactu_enabled',
    ],
];
