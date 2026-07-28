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
}
