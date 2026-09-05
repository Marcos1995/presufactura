<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Document;
use App\Models\InvoiceSeries;
use App\Models\User;
use Illuminate\Support\Str;

class DocumentNumberService
{
    public function draftNumber(string $type): string
    {
        $prefix = $type === Document::TYPE_QUOTE ? 'BOR-P' : 'BOR-F';

        return $prefix.'-'.strtoupper((string) Str::ulid());
    }

    public function nextInvoiceNumber(User $user): string
    {
        return $this->peekNext($user->currentCompany(), InvoiceSeries::KIND_INVOICE);
    }

    public function nextRectificativaNumber(User $user): string
    {
        return $this->peekNext($user->currentCompany(), InvoiceSeries::KIND_RECTIFICATIVA);
    }

    public function nextQuoteNumber(User $user): string
    {
        return $this->peekNext($user->currentCompany(), InvoiceSeries::KIND_QUOTE);
    }

    public function assignFiscalNumber(Document $document): string
    {
        if (! $document->isDraftNumber() && filled($document->number)) {
            if (! $document->number_assigned_at) {
                $document->update(['number_assigned_at' => now()]);
            }

            return $document->number;
        }

        $document->loadMissing('company');
        $company = $document->company ?? $document->user->ensureDefaultCompany();
        $kind = $this->kindFor($document);
        $series = $this->lockSeries($company, $kind);
        $number = $series->formatNumber($series->next_sequence);
        $series->increment('next_sequence');

        $document->update([
            'number' => $number,
            'number_assigned_at' => now(),
        ]);

        return $number;
    }

    private function kindFor(Document $document): string
    {
        if ($document->isQuote()) {
            return InvoiceSeries::KIND_QUOTE;
        }

        return $document->isRectificativa()
            ? InvoiceSeries::KIND_RECTIFICATIVA
            : InvoiceSeries::KIND_INVOICE;
    }

    private function peekNext(Company $company, string $kind): string
    {
        $series = $this->lockSeries($company, $kind);

        return $series->formatNumber($series->next_sequence);
    }

    private function lockSeries(Company $company, string $kind): InvoiceSeries
    {
        $company->ensureSeries();
        $year = (int) now($company->timezone ?: 'Europe/Madrid')->year;
        $prefix = match ($kind) {
            InvoiceSeries::KIND_QUOTE => $company->quote_prefix ?: 'PRE',
            InvoiceSeries::KIND_RECTIFICATIVA => $company->rectificativa_prefix ?: 'R',
            default => $company->invoice_prefix ?: 'FAC',
        };

        $series = InvoiceSeries::query()
            ->where('company_id', $company->id)
            ->where('kind', $kind)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        if ($series) {
            return $series;
        }

        return InvoiceSeries::query()->create([
            'company_id' => $company->id,
            'kind' => $kind,
            'prefix' => $prefix,
            'year' => $year,
            'next_sequence' => 1,
        ]);
    }
}
