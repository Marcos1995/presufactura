<?php

namespace Tests\Unit;

use App\Services\DocumentCalculatorService;
use PHPUnit\Framework\TestCase;

class DocumentCalculatorServiceTest extends TestCase
{
    public function test_calculate_document_totals(): void
    {
        $calculator = new DocumentCalculatorService;

        $result = $calculator->calculateDocument([
            ['quantity' => 2, 'unit_price' => 50, 'vat_rate' => 21],
        ]);

        $this->assertSame(100.0, $result['subtotal']);
        $this->assertSame(21.0, $result['vat_amount']);
        $this->assertSame(121.0, $result['total']);
    }

    public function test_discount_irpf_and_recargo(): void
    {
        $calculator = new DocumentCalculatorService;

        $result = $calculator->calculateDocument([
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21, 'discount_rate' => 10, 'irpf_rate' => 15, 'recargo_rate' => 0],
        ]);

        $this->assertSame(90.0, $result['subtotal']);
        $this->assertSame(10.0, $result['discount_amount']);
        $this->assertSame(18.9, $result['vat_amount']);
        $this->assertSame(13.5, $result['irpf_amount']);
        $this->assertSame(95.4, $result['total']);
    }

    public function test_recargo_is_added_to_total(): void
    {
        $calculator = new DocumentCalculatorService;

        $result = $calculator->calculateDocument([
            ['quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21, 'recargo_rate' => 5.2],
        ]);

        $this->assertSame(21.0, $result['vat_amount']);
        $this->assertSame(5.2, $result['recargo_amount']);
        $this->assertSame(126.2, $result['total']);
    }
}
