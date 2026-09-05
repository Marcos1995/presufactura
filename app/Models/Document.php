<?php

namespace App\Models;

use App\Support\VerifactuSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    public const TYPE_QUOTE = 'quote';
    public const TYPE_INVOICE = 'invoice';

    public const KIND_F1 = 'F1';
    public const KIND_F2 = 'F2';
    public const KIND_R1 = 'R1';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_PAID = 'paid';
    public const STATUS_PAYMENT_PENDING = 'payment_pending';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'company_id',
        'client_id',
        'type',
        'invoice_kind',
        'number',
        'number_assigned_at',
        'status',
        'issue_date',
        'operation_date',
        'due_date',
        'valid_until',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'irpf_amount',
        'recargo_amount',
        'total',
        'currency',
        'notes',
        'pdf_path',
        'public_token',
        'converted_from_id',
        'rectifies_document_id',
        'paid_at',
        'sent_at',
        'accepted_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'operation_date' => 'date',
            'due_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'irpf_amount' => 'decimal:2',
            'recargo_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'number_assigned_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Document $document) {
            if (! $document->company_id && $document->user_id) {
                $user = User::query()->find($document->user_id);
                $document->company_id = $user?->defaultCompany()?->id
                    ?? $user?->ensureDefaultCompany()->id;
            }
            if (! $document->user_id && $document->company_id) {
                $document->user_id = Company::query()->find($document->company_id)?->user_id;
            }
            if (! $document->created_by && $document->user_id) {
                $document->created_by = $document->user_id;
            }
            if (! $document->invoice_kind) {
                $document->invoice_kind = $document->rectifies_document_id ? self::KIND_R1 : self::KIND_F1;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function convertedFrom(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'converted_from_id');
    }

    public function rectifiesDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'rectifies_document_id');
    }

    public function rectifyingInvoices(): HasMany
    {
        return $this->hasMany(Document::class, 'rectifies_document_id');
    }

    public function billingRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BillingRecord::class)->where('record_type', BillingRecord::TYPE_ALTA);
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class);
    }

    public function issuer(): Company|User
    {
        $this->loadMissing(['company.user', 'user']);

        return $this->company ?? $this->user;
    }

    public function issuerName(): string
    {
        $this->loadMissing(['company', 'user']);

        return $this->company?->legal_name ?: ($this->user->business_name ?: $this->user->name);
    }

    public function issuerIban(): ?string
    {
        $this->loadMissing(['company', 'user']);

        return $this->company?->iban ?: $this->user->iban;
    }

    public function hasSifRecord(): bool
    {
        if (! VerifactuSchema::hasBillingRecordsTable()) {
            return false;
        }

        return $this->billingRecords()->where('record_type', BillingRecord::TYPE_ALTA)->exists();
    }

    public function isDraftNumber(): bool
    {
        return str_starts_with((string) $this->number, 'BOR-');
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
        if (! VerifactuSchema::hasBillingRecordsTable()) {
            return false;
        }

        return $this->isInvoice()
            && in_array($this->status, [self::STATUS_SENT, self::STATUS_PAID, self::STATUS_EXPIRED, self::STATUS_PAYMENT_PENDING], true)
            && $this->billingRecord?->aeat_status === BillingRecord::STATUS_ACCEPTED
            && ! $this->billingRecords()->where('record_type', BillingRecord::TYPE_ANULACION)->exists();
    }

    public function canCreateRectificativa(): bool
    {
        if (! VerifactuSchema::hasBillingRecordsTable()) {
            return false;
        }

        return $this->isInvoice()
            && in_array($this->status, [self::STATUS_SENT, self::STATUS_PAID], true)
            && $this->billingRecord?->aeat_status === BillingRecord::STATUS_ACCEPTED;
    }

    public function isRectificativa(): bool
    {
        return $this->rectifies_document_id !== null;
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

    public function canReject(): bool
    {
        return $this->isQuote() && $this->status === self::STATUS_SENT;
    }

    public function canConvert(): bool
    {
        return $this->isQuote()
            && in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_ACCEPTED], true)
            && ! Document::where('converted_from_id', $this->id)->exists();
    }

    public function isFiscal(): bool
    {
        if (! $this->isInvoice() || ! VerifactuSchema::hasBillingRecordsTable()) {
            return false;
        }

        $record = $this->relationLoaded('billingRecord')
            ? $this->billingRecord
            : $this->billingRecord()->first();

        return $record
            && $record->isAlta()
            && in_array($this->status, [
                self::STATUS_SENT,
                self::STATUS_PAID,
                self::STATUS_EXPIRED,
                self::STATUS_PAYMENT_PENDING,
            ], true);
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

    public function fiscalInvoiceType(): string
    {
        if ($this->isRectificativa()) {
            return self::KIND_R1;
        }

        return $this->invoice_kind ?: self::KIND_F1;
    }

    public function statusLabel(): string
    {
        $quote = $this->isQuote();

        return match ($this->status) {
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_SENT => $quote ? 'Enviado' : 'Enviada',
            self::STATUS_EXPIRED => $quote ? 'Caducado' : 'Vencida',
            self::STATUS_PAID => 'Pagada',
            self::STATUS_ACCEPTED => 'Aceptado',
            self::STATUS_REJECTED => 'Rechazado',
            self::STATUS_PAYMENT_PENDING => 'Pago pendiente',
            self::STATUS_CANCELLED => 'Anulada',
            default => ucfirst($this->status),
        };
    }
}
