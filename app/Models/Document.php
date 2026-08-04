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
    public const STATUS_CANCELLED = 'cancelled';

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
        'rectifies_document_id',
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

    public function rectifiesDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'rectifies_document_id');
    }

    public function billingRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BillingRecord::class)->where('record_type', BillingRecord::TYPE_ALTA);
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class);
    }

    public function hasSifRecord(): bool
    {
        return $this->billingRecords()->where('record_type', BillingRecord::TYPE_ALTA)->exists();
    }

    public function isImmutable(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_PAID,
            self::STATUS_EXPIRED,
            self::STATUS_PAYMENT_PENDING,
            self::STATUS_CANCELLED,
        ], true) && $this->isInvoice();
    }

    public function canCancel(): bool
    {
        return $this->isInvoice()
            && in_array($this->status, [self::STATUS_SENT, self::STATUS_PAID, self::STATUS_EXPIRED, self::STATUS_PAYMENT_PENDING], true)
            && $this->billingRecord?->aeat_status === BillingRecord::STATUS_ACCEPTED
            && ! $this->billingRecords()->where('record_type', BillingRecord::TYPE_ANULACION)->exists();
    }

    public function isQuote(): bool
    {
        return $this->type === self::TYPE_QUOTE;
    }

    public function isInvoice(): bool
    {
        return $this->type === self::TYPE_INVOICE;
    }

    public function canSend(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canMarkPaid(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_EXPIRED,
            self::STATUS_PAYMENT_PENDING,
        ], true);
    }

    public function canAccept(): bool
    {
        return $this->isQuote()
            && $this->status === self::STATUS_SENT
            && $this->valid_until
            && $this->valid_until->copy()->startOfDay()->gte(now()->startOfDay());
    }

    public function canConvert(): bool
    {
        return $this->isQuote()
            && $this->status === self::STATUS_ACCEPTED
            && ! Document::where('converted_from_id', $this->id)->exists();
    }

    public function canClaimPaid(): bool
    {
        return $this->isInvoice()
            && in_array($this->status, [self::STATUS_SENT, self::STATUS_EXPIRED], true);
    }

    public function publicUrl(): string
    {
        return url('/p/'.$this->public_token);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_SENT => 'Enviada',
            self::STATUS_EXPIRED => 'Vencida',
            self::STATUS_PAID => 'Pagada',
            self::STATUS_ACCEPTED => 'Aceptada',
            self::STATUS_PAYMENT_PENDING => 'Pago pendiente',
            self::STATUS_CANCELLED => 'Anulada',
            default => ucfirst($this->status),
        };
    }
}
