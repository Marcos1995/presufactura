<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingRecord extends Model
{
    use HasFactory;
    public const TYPE_ALTA = 'alta';

    public const TYPE_ANULACION = 'anulacion';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'document_id',
        'user_id',
        'record_type',
        'xml_path',
        'hash_current',
        'hash_previous',
        'aeat_status',
        'aeat_response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'aeat_response' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAlta(): bool
    {
        return $this->record_type === self::TYPE_ALTA;
    }

    public function isAnulacion(): bool
    {
        return $this->record_type === self::TYPE_ANULACION;
    }

    public function aeatStatusLabel(): string
    {
        return match ($this->aeat_status) {
            self::STATUS_PENDING => 'Pendiente AEAT',
            self::STATUS_ACCEPTED => 'Aceptada AEAT',
            self::STATUS_REJECTED => 'Rechazada AEAT',
            default => ucfirst($this->aeat_status),
        };
    }
}
