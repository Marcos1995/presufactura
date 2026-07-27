<?php

namespace App\Services;

class DocumentCalculatorService
{
    /**
     * @return array{line_subtotal: float, line_vat: float, line_total: float}
     */
    public function calculateLine(float $quantity, float $unitPrice, float $vatRate): array
    {
        $lineSubtotal = round($quantity * $unitPrice, 2);
        $lineVat = round($lineSubtotal * ($vatRate / 100), 2);
        $lineTotal = round($lineSubtotal + $lineVat, 2);

        return [
            'line_subtotal' => $lineSubtotal,
            'line_vat' => $lineVat,
            'line_total' => $lineTotal,
        ];
    }

    /**
     * @param  array<int, array{quantity: float, unit_price: float, vat_rate: float}>  $lines
     * @return array{subtotal: float, vat_amount: float, total: float, lines: array<int, array{line_subtotal: float, line_vat: float, line_total: float}>}
     */
    public function calculateDocument(array $lines): array
    {
        $subtotal = 0;
        $vatAmount = 0;
        $calculatedLines = [];

        foreach ($lines as $line) {
            $calc = $this->calculateLine(
                (float) $line['quantity'],
                (float) $line['unit_price'],
                (float) $line['vat_rate']
            );
            $calculatedLines[] = $calc;
            $subtotal += $calc['line_subtotal'];
            $vatAmount += $calc['line_vat'];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'vat_amount' => round($vatAmount, 2),
            'total' => round($subtotal + $vatAmount, 2),
            'lines' => $calculatedLines,
        ];
    }
}
