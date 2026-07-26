<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    public const TYPE_QUOTE = 'quote';
    public const TYPE_INVOICE = 'invoice';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_PAID = 'paid';
    public const STATUS_PAYMENT_PENDING = 'payment_pending';

    protected $fillable = [
        'user_id',
        'client_id',
        'type',
        'number',
        'status',
        'issue_date',
        'due_date',
        'valid_until',
        'subtotal',
        'vat_amount',
        'total',
        'currency',
        'notes',
        'public_token',
        'converted_from_id',
        'paid_at',
        'sent_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(LineItem::class)->orderBy('sort_order');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DocumentEvent::class);
    }

    public function convertedFrom(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'converted_from_id');
    }

    public function isQuote(): bool
    {
        return $this->type === self::TYPE_QUOTE;
    }

    public function isInvoice(): bool
    {
        return $this->type === self::TYPE_INVOICE;
    }
}
