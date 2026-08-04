<?php

namespace Tests\Unit;

use App\Services\Verifactu\HashChainService;
use Tests\TestCase;

class HashChainServiceTest extends TestCase
{
    private HashChainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HashChainService;
    }

    /** @dataProvider aeatHuellaFixtures */
    public function test_official_aeat_hash_vectors(string $tipo, array $campos, string $cadena, string $expectedHuella): void
    {
        $built = implode('&', array_map(
            fn (array $c) => $c['nombre'].'='.$c['valor'],
            $campos
        ));
        $this->assertSame($cadena, $built);

        $hash = match ($tipo) {
            'alta' => $this->service->computeAltaHash([
                'nif' => $this->fieldValue($campos, 'IDEmisorFactura'),
                'number' => $this->fieldValue($campos, 'NumSerieFactura'),
                'issue_date' => $this->fieldValue($campos, 'FechaExpedicionFactura'),
                'invoice_type' => $this->fieldValue($campos, 'TipoFactura'),
                'vat_amount' => $this->fieldValue($campos, 'CuotaTotal'),
                'total' => $this->fieldValue($campos, 'ImporteTotal'),
                'previous_hash' => $this->fieldValue($campos, 'Huella'),
                'timestamp' => $this->fieldValue($campos, 'FechaHoraHusoGenRegistro'),
            ]),
            'anulacion' => $this->service->computeAnulacionHash([
                'nif' => $this->fieldValue($campos, 'IDEmisorFacturaAnulada'),
                'number' => $this->fieldValue($campos, 'NumSerieFacturaAnulada'),
                'issue_date' => $this->fieldValue($campos, 'FechaExpedicionFacturaAnulada'),
                'previous_hash' => $this->fieldValue($campos, 'Huella'),
                'timestamp' => $this->fieldValue($campos, 'FechaHoraHusoGenRegistro'),
            ]),
            default => $this->fail("Tipo desconocido: {$tipo}"),
        };

        $this->assertSame($expectedHuella, $hash);
    }

    public static function aeatHuellaFixtures(): array
    {
        $json = json_decode(
            file_get_contents(__DIR__.'/../Fixtures/verifactu/huella.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return array_map(
            fn (array $caso) => [$caso['tipo'], $caso['campos'], $caso['cadena'], $caso['huella']],
            $json['casos']
        );
    }

    public function test_format_amount(): void
    {
        $this->assertSame('121.00', $this->service->formatAmount(121));
        $this->assertSame('121.50', $this->service->formatAmount(121.5));
    }

    /** @param  array<int, array{nombre: string, valor: string}>  $campos */
    private function fieldValue(array $campos, string $nombre): string
    {
        foreach ($campos as $campo) {
            if ($campo['nombre'] === $nombre) {
                return $campo['valor'];
            }
        }

        return '';
    }
}
