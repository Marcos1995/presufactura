<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceSeries extends Model
{
    public const KIND_INVOICE = 'invoice';

    public const KIND_QUOTE = 'quote';

    public const KIND_RECTIFICATIVA = 'rectificativa';

    protected $fillable = [
        'company_id',
        'kind',
        'prefix',
        'year',
        'next_sequence',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'next_sequence' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function formatNumber(int $sequence): string
    {
        return sprintf('%s-%d-%03d', $this->prefix, $this->year, $sequence);
    }
}
