<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Support\VerifactuSchema;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'email_verified_at',
        'business_name',
        'tax_id',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'iban',
        'logo_path',
        'default_vat_rate',
        'invoice_prefix',
        'quote_prefix',
        'invoice_counter',
        'quote_counter',
        'default_due_days',
        'reminder_day_1',
        'reminder_day_2',
        'reminder_day_3',
        'owner_reminder_day',
        'plan',
        'stripe_customer_id',
        'stripe_subscription_id',
        'plan_expires_at',
        'onboarding_completed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'plan_expires_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'password' => 'hashed',
            'default_vat_rate' => 'decimal:2',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class)->orderByDesc('is_default')->orderBy('legal_name');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserFeedback::class);
    }

    public function bugReports(): HasMany
    {
        return $this->hasMany(BugReport::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class);
    }

    public function sifEvents(): HasMany
    {
        return $this->hasMany(SifEvent::class);
    }

    public function sifConfigs(): HasMany
    {
        return $this->hasMany(UserSifConfig::class);
    }

    public function sifConfig(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserSifConfig::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(LegalConsent::class);
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->ensureDefaultCompany();
        });
    }

    public function ensureDefaultCompany(): Company
    {
        $existing = $this->companies()->first();
        if ($existing) {
            return $existing;
        }

        return $this->companies()->create([
            'legal_name' => $this->business_name ?: $this->name,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country ?: 'ES',
            'email' => $this->email,
            'phone' => $this->phone,
            'iban' => $this->iban,
            'logo_path' => $this->logo_path,
            'default_vat_rate' => $this->default_vat_rate ?? 21,
            'default_due_days' => $this->default_due_days ?? 30,
            'invoice_prefix' => $this->invoice_prefix ?: 'FAC',
            'quote_prefix' => $this->quote_prefix ?: 'PRE',
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function defaultCompany(): ?Company
    {
        $this->loadMissing('companies');

        return $this->companies->firstWhere('is_default', true)
            ?? $this->companies->first();
    }

    public function currentCompany(): Company
    {
        $this->ensureDefaultCompany();
        $this->unsetRelation('companies');
        $this->loadMissing('companies');

        $sessionId = session('current_company_id');
        if ($sessionId) {
            $selected = $this->companies->firstWhere('id', (int) $sessionId);
            if ($selected && $selected->is_active) {
                return $selected;
            }
        }

        return $this->defaultCompany() ?? $this->ensureDefaultCompany();
    }

    public function setCurrentCompany(Company $company): void
    {
        abort_unless($company->user_id === $this->id, 403);
        session(['current_company_id' => $company->id]);
    }

    public function ensureDefaultVerifactu(): void
    {
        $this->ensureDefaultCompany()->ensureSifConfig();
    }

    public function hasVerifactuEnabled(): bool
    {
        return $this->currentCompany()->hasVerifactuEnabled();
    }

    public function canEmitFiscalInvoices(): bool
    {
        return $this->currentCompany()->canEmitFiscalInvoices();
    }

    public function isDemoAdmin(): bool
    {
        $email = config('demo.admin_email');

        return filled($email)
            && strcasecmp((string) $this->email, $email) === 0;
    }

    public function usesVerifactuSandbox(): bool
    {
        if (! $this->isDemoAdmin()) {
            return false;
        }

        return $this->currentCompany()->usesVerifactuSandbox();
    }

    /**
     * Historicamente distinguía plan Pro. La app es 100% gratuita: todas las funciones están incluidas.
     */
    public function isPro(): bool
    {
        return true;
    }

    public function documentsThisMonthCount(): int
    {
        return $this->currentCompany()->documents()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    public function canCreateDocument(): bool
    {
        return true;
    }

    public function isProfileComplete(): bool
    {
        $company = $this->defaultCompany();
        $hasFiscal = filled($company?->legal_name) && filled($company?->tax_id) && filled($company?->iban);

        if (! $hasFiscal) {
            $hasFiscal = filled($this->business_name) && filled($this->tax_id) && filled($this->iban);
        }

        if (! $hasFiscal) {
            return false;
        }

        if (self::hasOnboardingColumn()) {
            return $this->onboarding_completed_at !== null;
        }

        return session('onboarding_step3_done', false);
    }

    public function onboardingStep(): int
    {
        $company = $this->defaultCompany();
        $name = $company?->legal_name ?: $this->business_name;
        $taxId = $company?->tax_id ?: $this->tax_id;
        $iban = $company?->iban ?: $this->iban;

        if (! filled($name) || ! filled($taxId)) {
            return 1;
        }

        if (! filled($iban)) {
            return 2;
        }

        if (self::hasOnboardingColumn()) {
            return $this->onboarding_completed_at ? 0 : 3;
        }

        return session('onboarding_step3_done', false) ? 0 : 3;
    }

    protected static function hasOnboardingColumn(): bool
    {
        static $hasColumn;

        return $hasColumn ??= Schema::hasColumn('users', 'onboarding_completed_at');
    }

    public function hasFiscalRecords(): bool
    {
        if (! VerifactuSchema::hasBillingRecordsTable()) {
            return false;
        }

        return $this->billingRecords()->exists();
    }
}
