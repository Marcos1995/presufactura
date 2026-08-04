<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SifEvent extends Model
{
    public const TYPE_STARTUP = 'startup';

    public const TYPE_EXPORT = 'export';

    public const TYPE_ANULACION = 'anulacion';

    public const TYPE_ALTA = 'alta';

    protected $fillable = [
        'user_id',
        'event_type',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
