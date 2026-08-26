<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UserSifConfig extends Model
{
    use HasFactory;
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

    public function certificateStatus(): string
    {
        if (! filled($this->cert_path)) {
            return 'missing';
        }

        if (! $this->cert_expires_at) {
            return 'unknown';
        }

        if ($this->cert_expires_at->isPast()) {
            return 'expired';
        }

        if ($this->cert_expires_at->lte(now()->addDays(30))) {
            return 'expiring';
        }

        return 'valid';
    }

    public function certPasswordPath(): string
    {
        return 'sif/certs/user_'.$this->user_id.'.pass.enc';
    }

    public function storeCertPassword(string $password): void
    {
        Storage::disk('local')->put($this->certPasswordPath(), encrypt($password));
        Cache::put("verifactu:cert_password:{$this->user_id}", $password, now()->addDays(30));
    }

    public function certPassword(): ?string
    {
        $path = $this->certPasswordPath();
        if (Storage::disk('local')->exists($path)) {
            try {
                $password = decrypt(Storage::disk('local')->get($path));
                if (filled($password)) {
                    return $password;
                }
            } catch (\Throwable) {
                // fallback to cache
            }
        }

        $cached = Cache::get("verifactu:cert_password:{$this->user_id}");

        return filled($cached) ? $cached : null;
    }

    public function certificateStatusLabel(): string
    {
        return match ($this->certificateStatus()) {
            'missing' => 'Sin certificado',
            'expired' => 'Certificado caducado',
            'expiring' => 'Caduca pronto',
            'valid' => 'Certificado válido',
            default => 'Estado desconocido',
        };
    }

    public function certificateStatusBadgeClass(): string
    {
        return match ($this->certificateStatus()) {
            'valid' => 'badge-cert-valid',
            'expired' => 'badge-cert-expired',
            'expiring' => 'badge-cert-expiring',
            default => 'badge-cert-missing',
        };
    }
}
