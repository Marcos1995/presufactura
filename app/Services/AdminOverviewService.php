<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Models\UserFeedback;
use App\Models\UserSifConfig;
use App\Support\VerifactuSchema;
use Illuminate\Support\Facades\Schema;

class AdminOverviewService
{
    public function __construct(private AnalyticsService $analytics) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $from = now()->subDays(30);
        $hasAnalytics = Schema::hasTable('analytics_events');

        return [
            'from' => $from,
            'stats' => $this->stats(),
            'users' => $this->users(),
            'documents' => $this->documents(),
            'funnel' => $hasAnalytics ? $this->analytics->funnelCounts($from) : [],
            'funnelLabels' => $this->funnelLabels(),
            'traffic' => $hasAnalytics ? $this->analytics->trafficSplit($from) : [
                'landing' => 0, 'authenticated' => 0, 'internal' => 0, 'bot' => 0, 'http_4xx' => 0, 'http_5xx' => 0,
            ],
            'uniqueSessions' => $hasAnalytics ? $this->analytics->uniqueSessions($from) : 0,
            'daily' => $hasAnalytics ? $this->analytics->dailyLandingViews(now()->subDays(13)->startOfDay()) : [],
            'bugReports' => Schema::hasTable('bug_reports')
                ? \App\Models\BugReport::query()->with('user')->latest()->limit(30)->get()
                : collect(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function stats(): array
    {
        $users = User::query();

        $sifEnabled = 0;
        $sifCert = 0;
        if (VerifactuSchema::hasSifConfigTable()) {
            $sifEnabled = UserSifConfig::query()->where('enabled', true)->count();
            $sifCert = UserSifConfig::query()
                ->where('enabled', true)
                ->whereNotNull('cert_path')
                ->where('cert_expires_at', '>', now())
                ->count();
        }

        $feedback = Schema::hasTable('user_feedback')
            ? UserFeedback::query()->where('dismissed', false)->count()
            : 0;

        return [
            'users' => (clone $users)->count(),
            'verified' => (clone $users)->whereNotNull('email_verified_at')->count(),
            'onboarded' => (clone $users)->whereNotNull('onboarding_completed_at')->count(),
            'new_7d' => (clone $users)->where('created_at', '>=', now()->subDays(7))->count(),
            'new_30d' => (clone $users)->where('created_at', '>=', now()->subDays(30))->count(),
            'clients' => Client::query()->count(),
            'quotes' => Document::query()->where('type', Document::TYPE_QUOTE)->count(),
            'invoices' => Document::query()->where('type', Document::TYPE_INVOICE)->count(),
            'paid_invoices' => Document::query()
                ->where('type', Document::TYPE_INVOICE)
                ->where('status', Document::STATUS_PAID)
                ->count(),
            'sif_enabled' => $sifEnabled,
            'sif_cert' => $sifCert,
            'feedback' => $feedback,
            'bug_reports' => Schema::hasTable('bug_reports') ? \App\Models\BugReport::query()->count() : 0,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function users()
    {
        $query = User::query()->withCount(['documents', 'clients'])->orderByDesc('id')->limit(200);

        if (VerifactuSchema::hasSifConfigTable()) {
            $query->with('sifConfig');
        }

        return $query->get();
    }

    /**
     * @return array{quotes: array<string, int>, invoices: array<string, int>}
     */
    private function documents(): array
    {
        $rows = Document::query()
            ->selectRaw('type, status, COUNT(*) as total')
            ->groupBy('type', 'status')
            ->get();

        $out = ['quotes' => [], 'invoices' => []];
        foreach ($rows as $row) {
            $bucket = $row->type === Document::TYPE_QUOTE ? 'quotes' : 'invoices';
            $out[$bucket][(string) $row->status] = (int) $row->total;
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private function funnelLabels(): array
    {
        return [
            'landing_view' => 'Visitas portada',
            'signup_cta_click' => 'Clic en registrarse',
            'registration_started' => 'Abrió el registro',
            'registration_completed' => 'Cuentas creadas',
            'first_quote_created' => 'Primer presupuesto',
            'quote_to_invoice' => 'Presupuesto → factura',
            'first_invoice_created' => 'Primera factura',
            'pdf_generated' => 'PDFs generados',
            'invoice_email_sent' => 'Emails de factura',
            'verifactu_enabled' => 'Activó Veri*Factu',
        ];
    }
}
