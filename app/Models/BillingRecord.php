<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingRecord extends Model
{
    use HasFactory;

    public const TYPE_ALTA = 'alta';

    public const TYPE_ANULACION = 'anulacion';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'document_id',
        'user_id',
        'company_id',
        'record_type',
        'invoice_type',
        'schema_version',
        'idempotency_key',
        'xml_path',
        'hash_current',
        'hash_previous',
        'aeat_status',
        'retry_count',
        'aeat_response',
        'sent_at',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'aeat_response' => 'array',
            'sent_at' => 'datetime',
            'generated_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BillingRecord $record) {
            if (! $record->company_id && $record->document_id) {
                $record->company_id = Document::query()->find($record->document_id)?->company_id;
            }
            if (! $record->idempotency_key) {
                $record->idempotency_key = implode(':', [
                    $record->company_id,
                    $record->document_id,
                    $record->record_type,
                ]);
            }
        });
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(BillingSubmissionAttempt::class);
    }

    public function isAlta(): bool
    {
        return $this->record_type === self::TYPE_ALTA;
    }

    public function isAnulacion(): bool
    {
        return $this->record_type === self::TYPE_ANULACION;
    }

    public function canRetry(): bool
    {
        return in_array($this->aeat_status, [self::STATUS_PENDING, self::STATUS_ERROR], true);
    }

    public function aeatStatusLabel(): string
    {
        if ($this->aeat_status === self::STATUS_ACCEPTED && ($this->aeat_response['sandbox'] ?? false)) {
            return 'Aceptada (pruebas)';
        }

        return match ($this->aeat_status) {
            self::STATUS_PENDING => 'Pendiente AEAT',
            self::STATUS_SUBMITTED => 'Enviada, pendiente de respuesta',
            self::STATUS_ACCEPTED => 'Aceptada AEAT',
            self::STATUS_REJECTED => 'Rechazada AEAT',
            self::STATUS_ERROR => 'Error de envío',
            default => ucfirst((string) $this->aeat_status),
        };
    }
}
