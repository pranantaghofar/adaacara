<?php

namespace App\Libraries;

use RuntimeException;

class OpenAiCompatibleVisionBlueprintService implements OcrProviderInterface
{
    private const MAX_IMAGE_BYTES = 8_388_608;

    public function detectText(string $absolutePath, array $context = []): array
    {
        $endpoint = trim((string) env('ADAACARA_AI_API_URL', env('GEMMA_API_URL', '')));
        $apiKey = trim((string) env('ADAACARA_AI_API_KEY', env('GEMMA_API_KEY', '')));
        $model = trim((string) env('ADAACARA_AI_DESIGN_MODEL', env('ADAACARA_AI_MODEL', env('GEMMA_MODEL', ''))));

        if ($endpoint === '' || $model === '') {
            throw new RuntimeException('AdaAcara AI provider belum dikonfigurasi di server.');
        }

        if (! is_file($absolutePath) || filesize($absolutePath) === false || filesize($absolutePath) > self::MAX_IMAGE_BYTES) {
            throw new RuntimeException('Gambar referensi tidak valid untuk AdaAcara AI.');
        }

        $imageBytes = file_get_contents($absolutePath);
        if ($imageBytes === false) {
            throw new RuntimeException('Gambar referensi gagal dibaca.');
        }

        $mime = (string) ($context['mime'] ?? '');
        if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Format gambar referensi tidak didukung AdaAcara AI.');
        }

        $info = @getimagesize($absolutePath);
        $imageWidth = (int) ($info[0] ?? 0);
        $imageHeight = (int) ($info[1] ?? 0);
        if ($imageWidth <= 0 || $imageHeight <= 0) {
            throw new RuntimeException('Dimensi gambar referensi tidak terbaca.');
        }

        $payload = $this->payload($model, $imageBytes, $mime, $imageWidth, $imageHeight, (string) ($context['creative_prompt'] ?? ''));
        $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
        ];
        if ($apiKey !== '') {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        $client = service('curlrequest', [
            'timeout' => (int) env('ADAACARA_AI_TIMEOUT', env('GEMMA_TIMEOUT', 60)),
            'http_errors' => false,
        ]);

        $response = $client->post($endpoint, [
            'headers' => $headers,
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status < 200 || $status >= 300) {
            log_message('error', 'AdaAcara AI compatible request gagal: status={status}, body={body}', [
                'status' => (string) $status,
                'body' => mb_substr($body, 0, 1000),
            ]);
            throw new RuntimeException('AdaAcara AI gagal membaca gambar referensi.');
        }

        $json = json_decode($body, true);
        $text = $this->extractText(is_array($json) ? $json : []);
        $blueprint = json_decode($this->extractJsonObject($text), true);
        if (! is_array($blueprint)) {
            log_message('error', 'AdaAcara AI compatible mengembalikan JSON tidak valid: {body}', [
                'body' => mb_substr($text, 0, 1000),
            ]);
            throw new RuntimeException('AdaAcara AI mengembalikan blueprint tidak valid.');
        }

        $blueprint['imageWidth'] = $imageWidth;
        $blueprint['imageHeight'] = $imageHeight;

        return $blueprint;
    }

    private function payload(string $model, string $imageBytes, string $mime, int $imageWidth, int $imageHeight, string $creativePrompt = ''): array
    {
        $dataUrl = 'data:' . ($mime === 'image/jpg' ? 'image/jpeg' : $mime) . ';base64,' . base64_encode($imageBytes);
        $payload = [
            'model' => $model,
            'messages' => [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => GeminiVisionBlueprintService::blueprintPrompt($imageWidth, $imageHeight, $creativePrompt),
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $dataUrl,
                        ],
                    ],
                ],
            ]],
            'temperature' => 0.1,
            'max_tokens' => (int) env('ADAACARA_AI_MAX_TOKENS', env('GEMMA_MAX_TOKENS', 8192)),
        ];

        $responseFormat = strtolower(trim((string) env('ADAACARA_AI_RESPONSE_FORMAT', env('GEMMA_RESPONSE_FORMAT', 'json_object'))));
        if ($responseFormat === 'json_object') {
            $payload['response_format'] = ['type' => 'json_object'];
        } elseif ($responseFormat === 'json_schema') {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'adaacara_design_blueprint',
                    'strict' => false,
                    'schema' => GeminiVisionBlueprintService::blueprintSchema(),
                ],
            ];
        }

        return $payload;
    }

    private function extractText(array $json): string
    {
        $content = $json['choices'][0]['message']['content'] ?? '';
        if (is_string($content) && trim($content) !== '') {
            return trim($content);
        }

        if (is_array($content)) {
            $parts = [];
            foreach ($content as $item) {
                if (isset($item['text']) && is_string($item['text'])) {
                    $parts[] = $item['text'];
                }
            }
            $text = trim(implode("\n", $parts));
            if ($text !== '') {
                return $text;
            }
        }

        throw new RuntimeException('AdaAcara AI tidak mengembalikan teks JSON.');
    }

    private function extractJsonObject(string $text): string
    {
        $text = trim($text);
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```$/', '', $text) ?? $text;
            $text = trim($text);
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end >= $start) {
            return substr($text, $start, $end - $start + 1);
        }

        return $text;
    }
}
