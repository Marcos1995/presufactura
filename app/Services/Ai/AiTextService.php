<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiTextService
{
    public function enabled(): bool
    {
        return filled(config('ai.api_key')) && config('ai.provider') !== 'none';
    }

    /**
     * @return array<string, mixed>|null JSON decodificado o null si falla / no hay API
     */
    public function completeJson(string $system, string $user, int $maxTokens = 0): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        $maxTokens = $maxTokens > 0 ? $maxTokens : (int) config('ai.max_tokens', 2048);
        $provider = (string) config('ai.provider', 'gemini');

        try {
            $raw = match ($provider) {
                'openai' => $this->openai($system, $user, $maxTokens),
                'anthropic' => $this->anthropic($system, $user, $maxTokens),
                default => $this->gemini($system, $user, $maxTokens),
            };

            if ($raw === null || $raw === '') {
                return null;
            }

            if (preg_match('/\{.*\}/s', $raw, $m)) {
                $decoded = json_decode($m[0], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (Throwable $e) {
            Log::warning('AiTextService failed', ['message' => $e->getMessage()]);
        }

        return null;
    }

    private function gemini(string $system, string $user, int $maxTokens): ?string
    {
        $model = (string) config('ai.model', 'gemini-3.8-flash');
        $base = rtrim((string) config('ai.gemini_base'), '/');
        $url = $base.'/models/'.$model.':generateContent';

        $response = Http::timeout((int) config('ai.timeout', 60))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => (string) config('ai.api_key'),
            ])
            ->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $system]],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => $user]],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => $maxTokens,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('Gemini error', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        $parts = $response->json('candidates.0.content.parts') ?? [];
        $text = '';
        foreach ($parts as $part) {
            $text .= $part['text'] ?? '';
        }

        return $text !== '' ? $text : null;
    }

    private function anthropic(string $system, string $user, int $maxTokens): ?string
    {
        $response = Http::timeout((int) config('ai.timeout', 60))
            ->withHeaders([
                'x-api-key' => config('ai.api_key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => config('ai.model'),
                'max_tokens' => $maxTokens,
                'system' => $system,
                'messages' => [
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('Anthropic error', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        $blocks = $response->json('content') ?? [];
        $text = '';
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'] ?? '';
            }
        }

        return $text !== '' ? $text : null;
    }

    private function openai(string $system, string $user, int $maxTokens): ?string
    {
        $base = rtrim((string) config('ai.openai_base'), '/');
        $response = Http::timeout((int) config('ai.timeout', 60))
            ->withToken((string) config('ai.api_key'))
            ->post($base.'/chat/completions', [
                'model' => config('ai.model', 'gpt-4o-mini'),
                'max_tokens' => $maxTokens,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('OpenAI error', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return $response->json('choices.0.message.content');
    }
}
