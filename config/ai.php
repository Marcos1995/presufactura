<?php

return [
    /*
    | Proveedor: gemini (gratis AI Studio) | anthropic | openai | none
    | Sin clave, los explicadores usan heurísticas locales (sin llamar a red).
    */
    'provider' => env('AI_PROVIDER', 'gemini'),
    'api_key' => env('GEMINI_API_KEY', env('AI_API_KEY', env('GOOGLE_AI_API_KEY'))),
    'model' => env('AI_MODEL', 'gemini-3.8-flash'),
    'gemini_base' => env('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta'),
    'openai_base' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'timeout' => (int) env('AI_TIMEOUT', 60),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 2048),
];
