<?php

namespace App\Services\VerifactuAssistant;

class VerifactuQuizEvaluator
{
    /**
     * @param  array<string, string>  $answers
     * @return array{applies: string, summary: string, steps: list<string>, cta: string}
     */
    public function evaluate(array $answers): array
    {
        $emite = ($answers['emite_facturas'] ?? '') === 'si';
        $word = ($answers['soporte'] ?? '') === 'word_excel';
        $sif = $answers['sif_actual'] ?? 'nose';
        $cert = $answers['certificado'] ?? 'nose';
        $exencion = $answers['exencion_crees'] ?? 'nose';

        if (! $emite) {
            return [
                'applies' => 'probable_no',
                'summary' => 'Si no emites facturas por actividad económica, Veri*Factu / SIF normalmente no te aplica como obligado a facturar. Confirma tu caso en la AEAT si tienes dudas.',
                'steps' => [
                    'Revisa si en el futuro vas a emitir facturas.',
                    'Consulta la página oficial de sistemas informáticos de facturación de la AEAT.',
                ],
                'cta' => 'Si empiezas a facturar, PresuFactura es gratis y admite Veri*Factu opcional.',
            ];
        }

        $applies = 'probable_si';
        if ($exencion === 'si') {
            $applies = 'revisar';
        }

        $steps = [
            'Confirma en la AEAT si tu actividad y tamaño te obligan al SIF / Veri*Factu y en qué fecha.',
            'Deja de depender solo de Word/Excel si es tu caso: necesitas un sistema que registre la facturación conforme al reglamento.',
            'Obtén o renueva un certificado electrónico válido para firmar/enviar si usas Veri*Factu.',
            'Prueba en entorno de pruebas antes de 2027; no afirmamos certificación oficial del software ante la AEAT.',
        ];

        if ($word) {
            array_unshift($steps, 'Prioridad alta: Word/Excel no cubren por sí solos el registro verificable exigido.');
        }
        if ($sif === 'no' || $sif === 'nose') {
            $steps[] = 'Tu sistema actual no parece generar el registro/hash/QR: planifica el cambio con margen.';
        }
        if ($cert === 'no' || $cert === 'nose') {
            $steps[] = 'Tramita el certificado electrónico (FNMT u otro reconocido) con tiempo.';
        }

        return [
            'applies' => $applies,
            'summary' => $applies === 'revisar'
                ? 'Puede que tengas particularidades. Este test es orientativo: verifica excepciones en la AEAT o con un profesional.'
                : 'Con los datos que has marcado, es probable que debas adaptar tu facturación al SIF / Veri*Factu. Esto no es un dictamen oficial.',
            'steps' => $steps,
            'cta' => 'PresuFactura es gratuita e incluye Veri*Factu opcional (certificado .p12, hash, QR y envío SOAP). No sustituye asesoramiento ni afirma homologación oficial.',
        ];
    }
}
