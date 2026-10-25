<?php

namespace App\Services\LetterExplainer;

use App\Services\Ai\AiTextService;
use Carbon\Carbon;

class LetterExplainerService
{
    public function __construct(private AiTextService $ai) {}

    /**
     * @return array{
     *   type_key: string,
     *   title: string,
     *   what: string,
     *   actions: string,
     *   deadline: ?string,
     *   deadline_iso: ?string,
     *   official_url: string,
     *   used_ai: bool,
     *   disclaimer: string
     * }
     */
    public function explain(string $text): array
    {
        $types = config('letter_types');
        $allowedKeys = array_keys($types);

        $ai = $this->ai->completeJson(
            'Eres un explicador neutral de notificaciones AEAT/Seguridad Social en España. NO eres asesor. Responde SOLO JSON con: type_key (uno de: '.implode(',', $allowedKeys).'), deadline_iso (YYYY-MM-DD o null), deadline_label (texto corto o null), what (2 frases claras), actions (2-4 frases, pasos). En embargos habla SOLO del trámite administrativo; NUNCA de bancos, IBAN, productos financieros ni cómo pagar deudas. No inventes URLs.',
            "Texto de la carta (efímero, no se guardará):\n".mb_substr($text, 0, 14000)
        );

        $typeKey = 'desconocido';
        $what = null;
        $actions = null;
        $deadlineIso = null;
        $deadlineLabel = null;
        $usedAi = false;

        if (is_array($ai)) {
            $usedAi = true;
            $candidate = (string) ($ai['type_key'] ?? 'desconocido');
            if (in_array($candidate, $allowedKeys, true)) {
                $typeKey = $candidate;
            }
            $what = isset($ai['what']) ? (string) $ai['what'] : null;
            $actions = isset($ai['actions']) ? (string) $ai['actions'] : null;
            $deadlineIso = $this->normalizeDate($ai['deadline_iso'] ?? null);
            $deadlineLabel = isset($ai['deadline_label']) ? (string) $ai['deadline_label'] : null;
        }

        if ($typeKey === 'desconocido') {
            $typeKey = $this->heuristicType($text);
        }

        $meta = $types[$typeKey] ?? $types['desconocido'];
        $deadlineIso = $deadlineIso ?: $this->heuristicDeadline($text);

        if ($deadlineIso && ! $deadlineLabel) {
            $deadlineLabel = Carbon::parse($deadlineIso)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        }

        return [
            'type_key' => $typeKey,
            'title' => $meta['label'],
            'what' => $what ?: $meta['what'],
            'actions' => $actions ?: $meta['actions'],
            'deadline' => $deadlineLabel,
            'deadline_iso' => $deadlineIso,
            'official_url' => $meta['url'],
            'used_ai' => $usedAi,
            'disclaimer' => 'Orientativo. No es asesoramiento fiscal ni jurídico. Ante dudas, consulta la administración o un profesional. No almacenamos el texto de tu carta.',
        ];
    }

    private function heuristicType(string $text): string
    {
        $t = mb_strtolower($text);
        if (str_contains($t, 'embargo')) {
            return 'embargo_creditos';
        }
        if (str_contains($t, 'apremio')) {
            return 'apremio';
        }
        if (str_contains($t, 'regularizaci') && (str_contains($t, 'cuota') || str_contains($t, 'seguridad social') || str_contains($t, 'tgss'))) {
            return 'regularizacion_cuota_ss';
        }
        if (str_contains($t, 'comprobación limitada') || str_contains($t, 'comprobacion limitada')) {
            return 'comprobacion_limitada';
        }
        if (str_contains($t, 'dehú') || str_contains($t, 'dehu') || str_contains($t, 'puesta a disposición')) {
            return 'notificacion_dehu';
        }
        if (str_contains($t, 'requerimiento') || str_contains($t, 'agencia tributaria') || str_contains($t, 'aeat')) {
            return 'requerimiento_aeat';
        }

        return 'desconocido';
    }

    private function heuristicDeadline(string $text): ?string
    {
        if (preg_match('/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})\b/', $text, $m)) {
            $d = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
            try {
                $c = Carbon::parse($d);
                if ($c->greaterThan(now()->subYear()) && $c->lessThan(now()->addYears(2))) {
                    return $c->toDateString();
                }
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '' || $value === 'null') {
            return null;
        }
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
