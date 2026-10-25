<?php

namespace App\Services\VerifactuAssistant;

use App\Services\Ai\AiTextService;

class InvoiceFieldChecker
{
    public function __construct(private AiTextService $ai) {}

    /**
     * @return array{found: array<string, bool>, missing: list<string>, notes: string, used_ai: bool}
     */
    public function check(string $text): array
    {
        $labels = config('verifactu_quiz.checklist_fields', []);
        $found = [];

        $aiResult = $this->ai->completeJson(
            'Eres un asistente que solo extrae si una factura española parece incluir ciertos campos. Responde ÚNICAMENTE JSON con claves booleanas: issuer_name, issuer_tax_id, receiver_name, receiver_tax_id, invoice_number, issue_date, description, tax_base, vat_rate, vat_amount, total. true = parece presente. No inventes. No digas que está certificada.',
            "Texto de factura (puede ser incompleto):\n".mb_substr($text, 0, 12000)
        );

        $usedAi = is_array($aiResult);
        foreach (array_keys($labels) as $key) {
            $found[$key] = $usedAi
                ? (bool) ($aiResult[$key] ?? false)
                : $this->heuristic($key, $text);
        }

        $missing = [];
        foreach ($found as $key => $ok) {
            if (! $ok) {
                $missing[] = $labels[$key] ?? $key;
            }
        }

        return [
            'found' => $found,
            'missing' => $missing,
            'notes' => 'Checklist orientativo. No certifica el cumplimiento Veri*Factu ni sustituye la normativa. Un PDF de Word/Excel puede verse “completo” y aun así no generar registro SIF.',
            'used_ai' => $usedAi,
        ];
    }

    private function heuristic(string $key, string $text): bool
    {
        $t = mb_strtolower($text);
        return match ($key) {
            'issuer_name' => (bool) preg_match('/(emisor|raz[oó]n social|nombre comercial)/u', $t),
            'issuer_tax_id', 'receiver_tax_id' => (bool) preg_match('/\b([a-z]\d{7}[a-z]|\d{8}[a-z]|[a-z]\d{8})\b/iu', $t) || str_contains($t, 'nif') || str_contains($t, 'cif'),
            'receiver_name' => (bool) preg_match('/(cliente|destinatario|receptor)/u', $t),
            'invoice_number' => (bool) preg_match('/(factura\s*n|n[ºo°]\s*factura|n[uú]mero)/u', $t),
            'issue_date' => (bool) preg_match('/(fecha|/\d{2}/\d{4}|\d{4}-\d{2}-\d{2})/u', $t),
            'description' => (bool) preg_match('/(concepto|descripci[oó]n|servicio|producto)/u', $t),
            'tax_base' => str_contains($t, 'base') || str_contains($t, 'imponible'),
            'vat_rate' => str_contains($t, 'iva') || str_contains($t, '%'),
            'vat_amount' => str_contains($t, 'cuota') || (str_contains($t, 'iva') && preg_match('/\d+[.,]\d{2}/', $t)),
            'total' => str_contains($t, 'total'),
            default => false,
        };
    }
}
