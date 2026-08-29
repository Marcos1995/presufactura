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

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserFeedback::class);
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

    public function sifConfig(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserSifConfig::class);
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->ensureDefaultVerifactu();
        });
    }

    public function ensureDefaultVerifactu(): void
    {
        if (! VerifactuSchema::hasSifConfigTable()) {
            return;
        }

        UserSifConfig::firstOrCreate(
            ['user_id' => $this->id],
            [
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

        $this->loadMissing('sifConfig');

        return $this->sifConfig?->is_dev_cert === true;
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
        return $this->documents()
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
        if (self::hasOnboardingColumn()) {
            return $this->onboarding_completed_at !== null;
        }

        return filled($this->business_name)
            && filled($this->tax_id)
            && filled($this->iban)
            && session('onboarding_step3_done', false);
    }

    public function onboardingStep(): int
    {
        if (! filled($this->business_name) || ! filled($this->tax_id)) {
            return 1;
        }

        if (! filled($this->iban)) {
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
}
