<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Document;
use App\Services\AnalyticsService;
use App\Services\DocumentCalculatorService;
use App\Services\DocumentNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuickStartController extends Controller
{
    public function sampleQuote(
        DocumentCalculatorService $calculator,
        DocumentNumberService $numbers,
        AnalyticsService $analytics,
    ): RedirectResponse {
        $user = auth()->user();

        $quote = DB::transaction(function () use ($user, $calculator, $numbers) {
            $client = $user->clients()->firstOrCreate(
                ['email' => $user->email],
                [
                    'name' => 'Cliente de prueba',
                    'tax_id' => null,
                    'address' => null,
                    'phone' => null,
                ]
            );

            $lines = [[
                'description' => 'Servicio de prueba',
                'quantity' => 1.0,
                'unit_price' => 100.0,
                'vat_rate' => (float) $user->default_vat_rate,
            ]];
            $totals = $calculator->calculateDocument($lines);

            $quote = $user->documents()->create([
                'client_id' => $client->id,
                'type' => Document::TYPE_QUOTE,
                'number' => $numbers->nextQuoteNumber($user),
                'status' => Document::STATUS_DRAFT,
                'issue_date' => now()->toDateString(),
                'valid_until' => now()->addDays(15)->toDateString(),
                'subtotal' => $totals['subtotal'],
                'vat_amount' => $totals['vat_amount'],
                'total' => $totals['total'],
                'notes' => 'Presupuesto de prueba. Puedes editarlo, convertirlo en factura, descargar el PDF o enviarlo por email.',
                'public_token' => Str::random(32),
            ]);

            $quote->lineItems()->create([
                'description' => $lines[0]['description'],
                'quantity' => $lines[0]['quantity'],
                'unit_price' => $lines[0]['unit_price'],
                'vat_rate' => $lines[0]['vat_rate'],
                'line_subtotal' => $totals['lines'][0]['line_subtotal'],
                'line_vat' => $totals['lines'][0]['line_vat'],
                'line_total' => $totals['lines'][0]['line_total'],
                'sort_order' => 0,
            ]);

            return $quote;
        });

        $analytics->record(AnalyticsEvent::FIRST_QUOTE_CREATED);

        return redirect()->route('quotes.show', $quote)
            ->with('status', 'Presupuesto de prueba creado. Conviértelo en factura, descarga el PDF o envíalo por email. Veri*Factu se activa después, cuando quieras.');
    }
}
