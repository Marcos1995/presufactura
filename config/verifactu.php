<?php

return [
    'env' => env('VERIFACTU_ENV', 'preprod'),

    'env_labels' => [
        'preprod' => 'Entorno de pruebas AEAT',
        'prod' => 'Entorno real AEAT',
    ],

    'mode' => env('VERIFACTU_MODE', 'verifactu'),

    'software' => [
        'name' => env('VERIFACTU_SOFTWARE_NAME', 'PresuFactura'),
        'version' => env('VERIFACTU_SOFTWARE_VERSION', '2.0.0'),
        'nif' => env('VERIFACTU_SOFTWARE_NIF', ''),
        'usage_type' => 'S',
    ],

    'qr_urls' => [
        'preprod' => [
            'verifactu' => 'https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR',
            'no_verifactu' => 'https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR',
        ],
        'prod' => [
            'verifactu' => 'https://www2.agenciatributaria.gob.es/wlpl/TIKE-CONT/ValidarQR',
            'no_verifactu' => 'https://www2.agenciatributaria.gob.es/wlpl/TIKE-CONT/ValidarQR',
        ],
    ],

    'wsdl' => [
        'preprod' => 'https://prewww2.aeat.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SistemaFacturacion.wsdl',
        'prod' => 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SistemaFacturacion.wsdl',
    ],

    'xsd_path' => storage_path('app/sif/xsd/SuministroLR.xsd'),
];
