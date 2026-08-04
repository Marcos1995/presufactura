<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSifConfig extends Model
{
    protected $table = 'user_sif_config';

    public const MODE_VERIFACTU = 'verifactu';

    public const MODE_NO_VERIFACTU = 'no_verifactu';

    protected $fillable = [
        'user_id',
        'mode',
        'cert_path',
        'cert_expires_at',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'cert_expires_at' => 'datetime',
            'enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerifactuMode(): bool
    {
        return $this->mode === self::MODE_VERIFACTU;
    }

    public function hasValidCertificate(): bool
    {
        return filled($this->cert_path)
            && $this->cert_expires_at
            && $this->cert_expires_at->isFuture();
    }
}
