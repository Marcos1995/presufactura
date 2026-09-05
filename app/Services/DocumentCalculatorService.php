<?php

namespace App\Services;

use App\Support\Money;

class DocumentCalculatorService
{
    /**
     * @param  array{quantity?: mixed, unit_price?: mixed, vat_rate?: mixed, discount_rate?: mixed, irpf_rate?: mixed, recargo_rate?: mixed}  $line
     * @return array{line_subtotal: float, line_discount: float, line_vat: float, line_irpf: float, line_recargo: float, line_total: float}
     */
    public function calculateLine(float $quantity, float $unitPrice, float $vatRate, float $discountRate = 0, float $irpfRate = 0, float $recargoRate = 0): array
    {
        return $this->calculateLineFromArray([
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'vat_rate' => $vatRate,
            'discount_rate' => $discountRate,
            'irpf_rate' => $irpfRate,
            'recargo_rate' => $recargoRate,
        ]);
    }

    /**
     * @param  array{quantity?: mixed, unit_price?: mixed, vat_rate?: mixed, discount_rate?: mixed, irpf_rate?: mixed, recargo_rate?: mixed}  $line
     * @return array{line_subtotal: float, line_discount: float, line_vat: float, line_irpf: float, line_recargo: float, line_total: float}
     */
    public function calculateLineFromArray(array $line): array
    {
        $gross = Money::mul($line['quantity'] ?? 0, $line['unit_price'] ?? 0);
        $discount = Money::percent($gross, $line['discount_rate'] ?? 0);
        $base = Money::sub($gross, $discount);
        $vat = Money::percent($base, $line['vat_rate'] ?? 0);
        $recargo = Money::percent($base, $line['recargo_rate'] ?? 0);
        $irpf = Money::percent($base, $line['irpf_rate'] ?? 0);
        $total = Money::sub(Money::add(Money::add($base, $vat), $recargo), $irpf);

        return [
            'line_subtotal' => Money::toFloat($base),
            'line_discount' => Money::toFloat($discount),
            'line_vat' => Money::toFloat($vat),
            'line_irpf' => Money::toFloat($irpf),
            'line_recargo' => Money::toFloat($recargo),
            'line_total' => Money::toFloat($total),
        ];
    }

    /**
     * @param  array<int, array{quantity?: mixed, unit_price?: mixed, vat_rate?: mixed, discount_rate?: mixed, irpf_rate?: mixed, recargo_rate?: mixed}>  $lines
     * @return array{subtotal: float, discount_amount: float, vat_amount: float, irpf_amount: float, recargo_amount: float, total: float, lines: array<int, array{line_subtotal: float, line_discount: float, line_vat: float, line_irpf: float, line_recargo: float, line_total: float}>}
     */
    public function calculateDocument(array $lines): array
    {
        $subtotal = '0.00';
        $discount = '0.00';
        $vat = '0.00';
        $irpf = '0.00';
        $recargo = '0.00';
        $calculatedLines = [];

        foreach ($lines as $line) {
            $calc = $this->calculateLineFromArray($line);
            $calculatedLines[] = $calc;
            $subtotal = Money::add($subtotal, $calc['line_subtotal']);
            $discount = Money::add($discount, $calc['line_discount']);
            $vat = Money::add($vat, $calc['line_vat']);
            $irpf = Money::add($irpf, $calc['line_irpf']);
            $recargo = Money::add($recargo, $calc['line_recargo']);
        }

        $total = Money::sub(Money::add(Money::add($subtotal, $vat), $recargo), $irpf);

        return [
            'subtotal' => Money::toFloat($subtotal),
            'discount_amount' => Money::toFloat($discount),
            'vat_amount' => Money::toFloat($vat),
            'irpf_amount' => Money::toFloat($irpf),
            'recargo_amount' => Money::toFloat($recargo),
            'total' => Money::toFloat($total),
            'lines' => $calculatedLines,
        ];
    }
}
