<?php

namespace App\Models;

use App\Support\VerifactuSchema;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;

    public const VAT_GENERAL = 'general';

    public const VAT_RECARGO = 'recargo';

    public const VAT_EXENTO = 'exento';

    protected $fillable = [
        'user_id',
        'legal_name',
        'tax_id',
        'address',
        'city',
        'postal_code',
        'province',
        'country',
        'email',
        'phone',
        'iban',
        'logo_path',
        'vat_regime',
        'default_vat_rate',
        'default_irpf_rate',
        'default_recargo_rate',
        'default_due_days',
        'invoice_prefix',
        'quote_prefix',
        'rectificativa_prefix',
        'timezone',
        'invoice_footer',
        'is_default',
        'is_active',
        'legal_terms_version',
        'legal_terms_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'default_vat_rate' => 'decimal:2',
            'default_irpf_rate' => 'decimal:2',
            'default_recargo_rate' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'legal_terms_accepted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Company $company) {
            $company->ensureSeries();
            $company->ensureSifConfig();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function series(): HasMany
    {
        return $this->hasMany(InvoiceSeries::class);
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class);
    }

    public function sifConfig(): HasOne
    {
        return $this->hasOne(UserSifConfig::class);
    }

    public function displayName(): string
    {
        return $this->legal_name ?: (string) $this->user?->name;
    }

    public function issuerEmail(): string
    {
        return $this->email ?: (string) $this->user?->email;
    }

    public function ensureSeries(): void
    {
        $year = (int) now($this->timezone ?: 'Europe/Madrid')->year;

        foreach ([
            InvoiceSeries::KIND_INVOICE => $this->invoice_prefix ?: 'FAC',
            InvoiceSeries::KIND_QUOTE => $this->quote_prefix ?: 'PRE',
            InvoiceSeries::KIND_RECTIFICATIVA => $this->rectificativa_prefix ?: 'R',
        ] as $kind => $prefix) {
            InvoiceSeries::firstOrCreate(
                ['company_id' => $this->id, 'kind' => $kind, 'year' => $year],
                ['prefix' => $prefix, 'next_sequence' => 1]
            );
        }
    }

    public function ensureSifConfig(): void
    {
        if (! VerifactuSchema::hasSifConfigTable()) {
            return;
        }

        UserSifConfig::firstOrCreate(
            ['company_id' => $this->id],
            [
                'user_id' => $this->user_id,
                'mode' => UserSifConfig::MODE_VERIFACTU,
                'enabled' => true,
            ]
        );
    }

    public function hasVerifactuEnabled(): bool
    {
        if (! VerifactuSchema::hasSifConfigTable()) {
            return false;
        }

        return $this->sifConfig?->enabled === true;
    }

    public function canEmitFiscalInvoices(): bool
    {
        return $this->hasVerifactuEnabled()
            && $this->sifConfig?->hasValidCertificate() === true;
    }

    public function usesVerifactuSandbox(): bool
    {
        return $this->sifConfig?->is_dev_cert === true;
    }

    public function syncLegacyUserFields(): void
    {
        if (! $this->is_default) {
            return;
        }

        $this->user?->forceFill([
            'business_name' => $this->legal_name,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'phone' => $this->phone,
            'iban' => $this->iban,
            'logo_path' => $this->logo_path,
            'default_vat_rate' => $this->default_vat_rate,
            'invoice_prefix' => $this->invoice_prefix,
            'quote_prefix' => $this->quote_prefix,
            'default_due_days' => $this->default_due_days,
        ])->save();
    }

    public function deleteLogo(): void
    {
        if ($this->logo_path) {
            Storage::disk('public')->delete($this->logo_path);
        }
    }
}
