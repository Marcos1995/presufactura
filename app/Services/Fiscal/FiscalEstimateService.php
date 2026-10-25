<?php

namespace App\Services\Fiscal;

use App\Models\Company;
use App\Models\Document;
use Carbon\Carbon;

class FiscalEstimateService
{
    /**
     * @return array{subtotal: float, vat: float, irpf: float, total: float, invoices: int, note: string}
     */
    public function forPeriod(Company $company, string $periodLabel, string $dueDate): array
    {
        [$from, $to] = $this->periodBounds($periodLabel, $dueDate);

        $query = Document::query()
            ->where('company_id', $company->id)
            ->where('type', Document::TYPE_INVOICE)
            ->whereNotIn('status', [Document::STATUS_CANCELLED, Document::STATUS_DRAFT])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()]);

        $agg = (clone $query)->selectRaw('COUNT(*) as c, COALESCE(SUM(subtotal),0) as s, COALESCE(SUM(vat_amount),0) as v, COALESCE(SUM(irpf_amount),0) as i, COALESCE(SUM(total),0) as t')->first();

        return [
            'subtotal' => round((float) ($agg->s ?? 0), 2),
            'vat' => round((float) ($agg->v ?? 0), 2),
            'irpf' => round((float) ($agg->i ?? 0), 2),
            'total' => round((float) ($agg->t ?? 0), 2),
            'invoices' => (int) ($agg->c ?? 0),
            'note' => 'Estimación orientativa solo a partir de facturas en PresuFactura en el periodo. No es el importe a ingresar ni un borrador oficial del modelo.',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodBounds(string $periodLabel, string $dueDate): array
    {
        if (preg_match('/([1-4])T\s*(\d{4})/', $periodLabel, $m)) {
            $q = (int) $m[1];
            $y = (int) $m[2];
            $startMonth = ($q - 1) * 3 + 1;
            $from = Carbon::create($y, $startMonth, 1)->startOfDay();
            $to = (clone $from)->addMonths(3)->subDay()->endOfDay();

            return [$from, $to];
        }

        if (preg_match('/(\d{4})/', $periodLabel, $m)) {
            $y = (int) $m[1];

            return [Carbon::create($y, 1, 1)->startOfDay(), Carbon::create($y, 12, 31)->endOfDay()];
        }

        $due = Carbon::parse($dueDate);

        return [(clone $due)->subMonths(3)->startOfDay(), (clone $due)->subDay()->endOfDay()];
    }
}
