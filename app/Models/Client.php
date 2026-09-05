<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'person_type',
        'name',
        'email',
        'tax_id',
        'address',
        'country',
        'city',
        'postal_code',
        'phone',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (! $client->company_id && $client->user_id) {
                $user = User::query()->find($client->user_id);
                $client->company_id = $user?->defaultCompany()?->id
                    ?? $user?->ensureDefaultCompany()->id;
            }
            if (! $client->user_id && $client->company_id) {
                $client->user_id = Company::query()->find($client->company_id)?->user_id;
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

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function isSpanish(): bool
    {
        return strtoupper((string) $this->country) === 'ES';
    }
}
