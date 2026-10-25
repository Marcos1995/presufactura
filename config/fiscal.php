<?php

/**
 * Reglas deterministas del calendario fiscal (España, autónomos típicos).
 * Revisar cada enero frente al calendario del contribuyente AEAT / BOE.
 * Las fechas son el último día hábil orientativo del plazo habitual (día natural de fin de plazo oficial típico).
 */
return [
    'year' => 2026,
    'aeat_calendar_url' => 'https://sede.agenciatributaria.gob.es/Sede/ayuda/calendario-contribuyente.html',
    'models' => [
        '303' => [
            'name' => 'Modelo 303 — IVA trimestral',
            'when' => 'Si estás en régimen general de IVA (no exento completo).',
            'url' => 'https://sede.agenciatributaria.gob.es/',
        ],
        '130' => [
            'name' => 'Modelo 130 — IRPF pago fraccionado',
            'when' => 'Estimación directa (normal u objetiva simplificada según caso); no módulos clásicos en el mismo sentido.',
            'url' => 'https://sede.agenciatributaria.gob.es/',
        ],
        '111' => [
            'name' => 'Modelo 111 — Retenciones trabajo/profesionales',
            'when' => 'Si practicas retenciones a trabajadores o profesionales.',
            'url' => 'https://sede.agenciatributaria.gob.es/',
        ],
        '115' => [
            'name' => 'Modelo 115 — Retenciones alquileres',
            'when' => 'Si tienes local/u otro inmueble alquilado sujeto a retención.',
            'url' => 'https://sede.agenciatributaria.gob.es/',
        ],
        '390' => [
            'name' => 'Modelo 390 — Resumen anual IVA',
            'when' => 'Si presentas 303 en el ejercicio.',
            'url' => 'https://sede.agenciatributaria.gob.es/',
        ],
        '190' => [
            'name' => 'Modelo 190 — Resumen anual retenciones',
            'when' => 'Si presentas 111 (u otras retenciones anuales asociadas).',
            'url' => 'https://sede.agenciatributaria.gob.es/',
        ],
        '347' => [
            'name' => 'Modelo 347 — Operaciones con terceros',
            'when' => 'Si superas umbrales de operaciones con un mismo tercero (comprueba el umbral vigente en AEAT).',
            'url' => 'https://sede.agenciatributaria.gob.es/',
        ],
    ],
    /**
     * deadlines[year][model][] = ['period' => '1T', 'due' => 'Y-m-d']
     */
    'deadlines' => [
        2026 => [
            '303' => [
                ['period' => '1T 2026', 'due' => '2026-04-20'],
                ['period' => '2T 2026', 'due' => '2026-07-20'],
                ['period' => '3T 2026', 'due' => '2026-10-20'],
                ['period' => '4T 2026', 'due' => '2027-01-30'],
            ],
            '130' => [
                ['period' => '1T 2026', 'due' => '2026-04-20'],
                ['period' => '2T 2026', 'due' => '2026-07-20'],
                ['period' => '3T 2026', 'due' => '2026-10-20'],
                ['period' => '4T 2026', 'due' => '2027-01-30'],
            ],
            '111' => [
                ['period' => '1T 2026', 'due' => '2026-04-20'],
                ['period' => '2T 2026', 'due' => '2026-07-20'],
                ['period' => '3T 2026', 'due' => '2026-10-20'],
                ['period' => '4T 2026', 'due' => '2027-01-20'],
            ],
            '115' => [
                ['period' => '1T 2026', 'due' => '2026-04-20'],
                ['period' => '2T 2026', 'due' => '2026-07-20'],
                ['period' => '3T 2026', 'due' => '2026-10-20'],
                ['period' => '4T 2026', 'due' => '2027-01-20'],
            ],
            '390' => [
                ['period' => 'Ejercicio 2026', 'due' => '2027-01-30'],
            ],
            '190' => [
                ['period' => 'Ejercicio 2026', 'due' => '2027-01-31'],
            ],
            '347' => [
                ['period' => 'Ejercicio 2026', 'due' => '2027-02-28'],
            ],
        ],
        2027 => [
            '303' => [
                ['period' => '1T 2027', 'due' => '2027-04-20'],
                ['period' => '2T 2027', 'due' => '2027-07-20'],
                ['period' => '3T 2027', 'due' => '2027-10-20'],
                ['period' => '4T 2027', 'due' => '2028-01-30'],
            ],
            '130' => [
                ['period' => '1T 2027', 'due' => '2027-04-20'],
                ['period' => '2T 2027', 'due' => '2027-07-20'],
                ['period' => '3T 2027', 'due' => '2027-10-20'],
                ['period' => '4T 2027', 'due' => '2028-01-30'],
            ],
            '111' => [
                ['period' => '1T 2027', 'due' => '2027-04-20'],
                ['period' => '2T 2027', 'due' => '2027-07-20'],
                ['period' => '3T 2027', 'due' => '2027-10-20'],
                ['period' => '4T 2027', 'due' => '2028-01-20'],
            ],
            '115' => [
                ['period' => '1T 2027', 'due' => '2027-04-20'],
                ['period' => '2T 2027', 'due' => '2027-07-20'],
                ['period' => '3T 2027', 'due' => '2027-10-20'],
                ['period' => '4T 2027', 'due' => '2028-01-20'],
            ],
            '390' => [
                ['period' => 'Ejercicio 2027', 'due' => '2028-01-30'],
            ],
            '190' => [
                ['period' => 'Ejercicio 2027', 'due' => '2028-01-31'],
            ],
            '347' => [
                ['period' => 'Ejercicio 2027', 'due' => '2028-02-28'],
            ],
        ],
    ],
];
