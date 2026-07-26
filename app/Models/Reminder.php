<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    public $timestamps = false;

    public const TYPE_CLIENT_DAY_3 = 'client_day_3';
    public const TYPE_CLIENT_DAY_7 = 'client_day_7';
    public const TYPE_CLIENT_DAY_14 = 'client_day_14';
    public const TYPE_OWNER_DAY_10 = 'owner_day_10';
    public const TYPE_CLIENT_CUSTOM = 'client_custom';

    protected $fillable = [
        'document_id',
        'type',
        'recipient_email',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
