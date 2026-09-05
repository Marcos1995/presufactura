<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingSubmissionAttempt extends Model
{
    protected $fillable = [
        'billing_record_id',
        'status',
        'permanent',
        'idempotency_key',
        'response',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'permanent' => 'boolean',
            'response' => 'array',
        ];
    }

    public function billingRecord(): BelongsTo
    {
        return $this->belongsTo(BillingRecord::class);
    }
}
