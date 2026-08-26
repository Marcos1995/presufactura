<?php

return [
    /*
     * Único usuario que puede generar y ver el catálogo de ejemplo.
     * Déjalo vacío hasta que indiques el email; el comando no hará nada.
     */
    'admin_email' => strtolower(trim((string) env('DEMO_ADMIN_EMAIL', ''))),
];
