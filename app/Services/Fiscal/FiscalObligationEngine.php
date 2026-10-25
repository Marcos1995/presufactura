<?php

namespace App\Services\Fiscal;

use App\Models\Company;

class FiscalObligationEngine
{
    /**
     * @return list<array{model: string, name: string, period: string, due: string, url: string}>
     */
    public function forCompany(Company $company, ?int $year = null): array
    {
        $year = $year ?: (int) config('fiscal.year', now()->year);
        $modelsNeeded = $this->modelsForProfile($company);
        $deadlines = config("fiscal.deadlines.$year", []);
        $catalog = config('fiscal.models', []);
        $out = [];

        foreach ($modelsNeeded as $code) {
            foreach ($deadlines[$code] ?? [] as $row) {
                $out[] = [
                    'model' => $code,
                    'name' => $catalog[$code]['name'] ?? "Modelo $code",
                    'period' => $row['period'],
                    'due' => $row['due'],
                    'url' => $catalog[$code]['url'] ?? config('fiscal.aeat_calendar_url'),
                ];
            }
        }

        usort($out, fn ($a, $b) => strcmp($a['due'], $b['due']));

        return $out;
    }

    /**
     * @return list<string>
     */
    public function modelsForProfile(Company $company): array
    {
        $models = [];
        $vat = $company->vat_regime ?: Company::VAT_GENERAL;
        $method = $company->fiscal_income_method ?: 'estimacion_directa';

        if ($vat !== Company::VAT_EXENTO) {
            $models[] = '303';
            $models[] = '390';
        }

        if ($method === 'estimacion_directa') {
            $models[] = '130';
        }

        if ($company->fiscal_has_employees) {
            $models[] = '111';
            $models[] = '190';
        }

        if ($company->fiscal_has_rented_local) {
            $models[] = '115';
        }

        // 347: se sugiere siempre con nota de umbral; el usuario decide
        $models[] = '347';

        return array_values(array_unique($models));
    }
}
