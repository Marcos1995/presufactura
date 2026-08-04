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

    public function test_compute_hash_is_deterministic(): void
    {
        $data = [
            'nif' => 'B12345678',
            'number' => 'F2026-001',
            'issue_date' => '04-08-2026',
            'invoice_type' => 'F1',
            'vat_amount' => '21.00',
            'total' => '121.00',
            'previous_hash' => '',
            'timestamp' => '2026-08-04T12:00:00+02:00',
        ];

        $hash1 = $this->service->computeHash($data);
        $hash2 = $this->service->computeHash($data);

        $this->assertSame($hash1, $hash2);
        $this->assertSame(64, strlen($hash1));
        $this->assertMatchesRegularExpression('/^[A-F0-9]{64}$/', $hash1);
    }

    public function test_compute_hash_changes_with_previous_hash(): void
    {
        $base = [
            'nif' => 'B12345678',
            'number' => 'F2026-001',
            'issue_date' => '04-08-2026',
            'invoice_type' => 'F1',
            'vat_amount' => '21.00',
            'total' => '121.00',
            'timestamp' => '2026-08-04T12:00:00+02:00',
        ];

        $hashWithout = $this->service->computeHash(array_merge($base, ['previous_hash' => '']));
        $hashWith = $this->service->computeHash(array_merge($base, ['previous_hash' => 'ABC123']));

        $this->assertNotSame($hashWithout, $hashWith);
    }

    public function test_format_amount(): void
    {
        $this->assertSame('121.00', $this->service->formatAmount(121));
        $this->assertSame('121.50', $this->service->formatAmount(121.5));
    }
}
