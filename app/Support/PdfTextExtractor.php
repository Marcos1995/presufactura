<?php

namespace App\Support;

class PdfTextExtractor
{
    /**
     * Extracción tosca sin dependencias: útil en PDF de texto.
     * Escaneos/imagen devolverán poco; en ese caso la IA/heurística verá poco.
     */
    public static function fromPath(string $path): string
    {
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return '';
        }

        $chunks = [];
        if (preg_match_all('/\((?:\\\\.|[^\\\\)]){2,}\)/s', $raw, $m)) {
            foreach ($m[0] as $token) {
                $inner = substr($token, 1, -1);
                $inner = str_replace(['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'], ["\n", '', "\t", '(', ')', '\\'], $inner);
                if (preg_match('/[A-Za-zÁÉÍÓÚáéíóúÑñ0-9]/u', $inner)) {
                    $chunks[] = $inner;
                }
            }
        }

        $text = trim(implode(' ', $chunks));
        if (mb_strlen($text) < 40) {
            $ascii = preg_replace('/[^\P{C}\n\t]+/u', ' ', $raw) ?? '';
            $ascii = preg_replace('/[^\x20-\x7EÁÉÍÓÚáéíóúÑñüÜ\n]+/u', ' ', $ascii) ?? '';
            $text = trim(preg_replace('/\s+/', ' ', $ascii) ?? '');
        }

        return mb_substr($text, 0, 20000);
    }
}
