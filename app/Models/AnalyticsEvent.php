<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    public const SOURCE_LANDING = 'landing';
    public const SOURCE_AUTHENTICATED = 'authenticated';
    public const SOURCE_INTERNAL = 'internal';
    public const SOURCE_BOT = 'bot';

    public const LANDING_VIEW = 'landing_view';
    public const SIGNUP_CTA_CLICK = 'signup_cta_click';
    public const REGISTRATION_STARTED = 'registration_started';
    public const REGISTRATION_COMPLETED = 'registration_completed';
    public const FIRST_QUOTE_CREATED = 'first_quote_created';
    public const QUOTE_TO_INVOICE = 'quote_to_invoice';
    public const FIRST_INVOICE_CREATED = 'first_invoice_created';
    public const PDF_GENERATED = 'pdf_generated';
    public const INVOICE_EMAIL_SENT = 'invoice_email_sent';
    public const VERIFACTU_ENABLED = 'verifactu_enabled';
    public const HTTP_4XX = 'http_4xx';
    public const HTTP_5XX = 'http_5xx';

    public const ALLOWED = [
        self::LANDING_VIEW,
        self::SIGNUP_CTA_CLICK,
        self::REGISTRATION_STARTED,
        self::REGISTRATION_COMPLETED,
        self::FIRST_QUOTE_CREATED,
        self::QUOTE_TO_INVOICE,
        self::FIRST_INVOICE_CREATED,
        self::PDF_GENERATED,
        self::INVOICE_EMAIL_SENT,
        self::VERIFACTU_ENABLED,
        self::HTTP_4XX,
        self::HTTP_5XX,
    ];

    public const CLIENT_ALLOWED = [
        self::SIGNUP_CTA_CLICK,
    ];

    public const ONCE_PER_USER = [
        self::FIRST_QUOTE_CREATED,
        self::FIRST_INVOICE_CREATED,
        self::VERIFACTU_ENABLED,
    ];

    protected $fillable = [
        'name',
        'source',
        'http_status',
        'route_name',
        'visitor_hash',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
