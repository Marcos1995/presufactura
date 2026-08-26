<?php

return [
    /*
     * Único usuario que puede generar y ver el catálogo de ejemplo.
     */
    'admin_email' => strtolower(trim((string) env('DEMO_ADMIN_EMAIL', 'marcospc1995@gmail.com'))),
];
