<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentEvent extends Model
{
    public $timestamps = false;

    public const CREATED = 'created';
    public const SENT = 'sent';
    public const ACCEPTED = 'accepted';
    public const PAID = 'paid';
    public const REMINDER_SENT = 'reminder_sent';
    public const MARKED_PAID = 'marked_paid';
    public const CLIENT_CLAIMED_PAID = 'client_claimed_paid';

    protected $fillable = [
        'document_id',
        'event_type',
        'meta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
