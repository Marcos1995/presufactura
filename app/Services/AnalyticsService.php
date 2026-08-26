<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AnalyticsService
{
    public function record(string $name, ?Request $request = null, ?int $httpStatus = null): void
    {
        if (! config('analytics.enabled', true)) {
            return;
        }

        if (! in_array($name, AnalyticsEvent::ALLOWED, true)) {
            return;
        }

        if (! Schema::hasTable('analytics_events')) {
            return;
        }

        try {
            $request ??= request();
            $userId = $request->user()?->id;

            if ($userId && in_array($name, AnalyticsEvent::ONCE_PER_USER, true)) {
                $exists = AnalyticsEvent::query()
                    ->where('name', $name)
                    ->where('user_id', $userId)
                    ->exists();

                if ($exists) {
                    return;
                }
            }

            AnalyticsEvent::create([
                'name' => $name,
                'source' => $this->source($request),
                'http_status' => $httpStatus,
                'route_name' => $request->route()?->getName(),
                'visitor_hash' => $this->visitorHash($request),
                'user_id' => $userId,
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // La medición no debe romper el producto.
        }
    }

    public function recordHttpStatus(Request $request, int $status): void
    {
        if ($status < 400) {
            return;
        }

        $this->record(
            $status >= 500 ? AnalyticsEvent::HTTP_5XX : AnalyticsEvent::HTTP_4XX,
            $request,
            $status
        );
    }

    /**
     * @return array<string, int>
     */
    public function funnelCounts(?\DateTimeInterface $from = null): array
    {
        $query = AnalyticsEvent::query()
            ->whereIn('name', config('analytics.funnel'))
            ->whereNotIn('source', [AnalyticsEvent::SOURCE_BOT, AnalyticsEvent::SOURCE_INTERNAL]);

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        $counts = $query
            ->selectRaw('name, COUNT(*) as total')
            ->groupBy('name')
            ->pluck('total', 'name')
            ->all();

        $funnel = [];
        foreach (config('analytics.funnel') as $name) {
            $funnel[$name] = (int) ($counts[$name] ?? 0);
        }

        return $funnel;
    }

    /**
     * @return array{landing: int, authenticated: int, internal: int, bot: int, http_4xx: int, http_5xx: int}
     */
    public function trafficSplit(?\DateTimeInterface $from = null): array
    {
        $query = AnalyticsEvent::query();
        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        $bySource = (clone $query)
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source')
            ->all();

        return [
            'landing' => (int) ($bySource[AnalyticsEvent::SOURCE_LANDING] ?? 0),
            'authenticated' => (int) ($bySource[AnalyticsEvent::SOURCE_AUTHENTICATED] ?? 0),
            'internal' => (int) ($bySource[AnalyticsEvent::SOURCE_INTERNAL] ?? 0),
            'bot' => (int) ($bySource[AnalyticsEvent::SOURCE_BOT] ?? 0),
            'http_4xx' => (int) (clone $query)->where('name', AnalyticsEvent::HTTP_4XX)->count(),
            'http_5xx' => (int) (clone $query)->where('name', AnalyticsEvent::HTTP_5XX)->count(),
        ];
    }

    private function source(Request $request): string
    {
        if ($this->isInternal($request)) {
            return AnalyticsEvent::SOURCE_INTERNAL;
        }

        if ($this->isBot($request)) {
            return AnalyticsEvent::SOURCE_BOT;
        }

        if ($request->user()) {
            return AnalyticsEvent::SOURCE_AUTHENTICATED;
        }

        return AnalyticsEvent::SOURCE_LANDING;
    }

    private function isInternal(Request $request): bool
    {
        $ips = config('analytics.internal_ips', []);

        return $ips !== [] && in_array($request->ip(), $ips, true);
    }

    private function isBot(Request $request): bool
    {
        $ua = strtolower((string) $request->userAgent());

        if ($ua === '') {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|embedly|quora|whatsapp|telegram|preview|lighthouse|pingdom|uptimerobot|headless|phantom|selenium/i',
            $ua
        );
    }

    private function visitorHash(Request $request): ?string
    {
        $sessionId = $request->hasSession() ? (string) $request->session()->getId() : '';

        if ($sessionId === '') {
            return null;
        }

        return hash('sha256', $sessionId.'|analytics');
    }
}
