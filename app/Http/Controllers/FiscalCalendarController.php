<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\Fiscal\FiscalEstimateService;
use App\Services\Fiscal\FiscalObligationEngine;
use App\Services\Fiscal\IcalBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FiscalCalendarController extends Controller
{
    public function show(Request $request, FiscalObligationEngine $engine, FiscalEstimateService $estimates): View
    {
        /** @var Company $company */
        $company = $request->attributes->get('currentCompany')
            ?? $request->user()->defaultCompany()
            ?? $request->user()->ensureDefaultCompany();

        $year = (int) $request->query('year', config('fiscal.year', now()->year));
        $obligations = $company->fiscal_profile_completed_at
            ? $engine->forCompany($company, $year)
            : [];

        $withEstimates = [];
        foreach ($obligations as $row) {
            $row['estimate'] = in_array($row['model'], ['303', '130'], true)
                ? $estimates->forPeriod($company, $row['period'], $row['due'])
                : null;
            $withEstimates[] = $row;
        }

        return view('fiscal.calendar', [
            'company' => $company,
            'year' => $year,
            'obligations' => $withEstimates,
            'aeatUrl' => config('fiscal.aeat_calendar_url'),
        ]);
    }

    public function storeProfile(Request $request, FiscalObligationEngine $engine): RedirectResponse
    {
        /** @var Company $company */
        $company = $request->attributes->get('currentCompany')
            ?? $request->user()->defaultCompany()
            ?? $request->user()->ensureDefaultCompany();

        $data = $request->validate([
            'fiscal_activity' => 'required|string|max:120',
            'vat_regime' => 'required|in:general,recargo,exento',
            'fiscal_income_method' => 'required|in:estimacion_directa,modulos',
            'fiscal_has_employees' => 'nullable|boolean',
            'fiscal_has_rented_local' => 'nullable|boolean',
            'fiscal_reminders_opt_in' => 'nullable|boolean',
        ]);

        $company->fill([
            'fiscal_activity' => $data['fiscal_activity'],
            'vat_regime' => $data['vat_regime'],
            'fiscal_income_method' => $data['fiscal_income_method'],
            'fiscal_has_employees' => $request->boolean('fiscal_has_employees'),
            'fiscal_has_rented_local' => $request->boolean('fiscal_has_rented_local'),
            'fiscal_reminders_opt_in' => $request->boolean('fiscal_reminders_opt_in'),
            'fiscal_profile_completed_at' => now(),
            'fiscal_calendar_token' => $company->fiscal_calendar_token ?: Str::random(48),
        ]);
        $company->save();

        return redirect()->route('fiscal.calendar')->with('status', 'Calendario fiscal actualizado (orientativo).');
    }

    public function ics(Request $request, string $token, FiscalObligationEngine $engine, IcalBuilder $ical): Response
    {
        $company = Company::query()->where('fiscal_calendar_token', $token)->firstOrFail();
        $year = (int) $request->query('year', config('fiscal.year', now()->year));
        $events = $engine->forCompany($company, $year);
        $body = $ical->build($events, 'PresuFactura — '.$company->legal_name);

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="calendario-fiscal.ics"',
        ]);
    }
}
