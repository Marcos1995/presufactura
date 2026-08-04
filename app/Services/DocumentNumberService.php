<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;

class DocumentNumberService
{
    public function nextInvoiceNumber(User $user): string
    {
        $year = now()->year;
        $prefix = $user->invoice_prefix;

        $lastNumber = Document::query()
            ->where('user_id', $user->id)
            ->where('type', Document::TYPE_INVOICE)
            ->where('number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('number')
            ->value('number');

        $sequence = 1;
        if ($lastNumber && preg_match('/-(\d+)$/', $lastNumber, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%d-%03d', $prefix, $year, $sequence);
    }

    public function nextRectificativaNumber(User $user): string
    {
        $year = now()->year;
        $prefix = 'R';

        $lastNumber = Document::query()
            ->where('user_id', $user->id)
            ->where('type', Document::TYPE_INVOICE)
            ->whereNotNull('rectifies_document_id')
            ->where('number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('number')
            ->value('number');

        $sequence = 1;
        if ($lastNumber && preg_match('/-(\d+)$/', $lastNumber, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%d-%03d', $prefix, $year, $sequence);
    }

    public function nextQuoteNumber(User $user): string
    {
        $year = now()->year;
        $prefix = $user->quote_prefix;

        $lastNumber = Document::query()
            ->where('user_id', $user->id)
            ->where('type', Document::TYPE_QUOTE)
            ->where('number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('number')
            ->value('number');

        $sequence = 1;
        if ($lastNumber && preg_match('/-(\d+)$/', $lastNumber, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%d-%03d', $prefix, $year, $sequence);
    }
}
