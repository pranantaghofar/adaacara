<?php

namespace App\Libraries;

use RuntimeException;

class PollinationsAcaraAiPromptService
{
    private const MAX_IMAGE_BYTES = 8_388_608;

    public function generate(string $prompt, array $context = []): array
    {
        $prompt = trim(mb_substr($prompt, 0, 2000));
        if ($prompt === '') {
            throw new RuntimeException('Prompt ACARA AI tidak boleh kosong.');
        }

        $apiKey = trim((string) env('ACARA_AI_API_KEY', env('POLLINATIONS_API_KEY', '')));
        if ($apiKey === '') {
            throw new RuntimeException('ACARA AI Pollinations belum dikonfigurasi di server.');
        }

        $imageWidth = max(320, min(6000, (int) ($context['imageWidth'] ?? 1080)));
        $imageHeight = max(320, min(6000, (int) ($context['imageHeight'] ?? 1920)));
        $imageDataUrl = '';

        $imagePath = (string) ($context['imagePath'] ?? '');
        if ($imagePath !== '' && is_file($imagePath)) {
            if (filesize($imagePath) === false || filesize($imagePath) > self::MAX_IMAGE_BYTES) {
                throw new RuntimeException('Gambar referensi ACARA AI tidak valid.');
            }

            $mime = (string) ($context['mime'] ?? '');
            if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
                throw new RuntimeException('Format gambar referensi ACARA AI tidak didukung.');
            }

            $imageBytes = file_get_contents($imagePath);
            if ($imageBytes === false) {
                throw new RuntimeException('Gambar referensi ACARA AI gagal dibaca.');
            }

            $imageDataUrl = 'data:' . ($mime === 'image/jpg' ? 'image/jpeg' : $mime) . ';base64,' . base64_encode($imageBytes);
        }

        $endpoint = $this->endpoint();
        $model = trim((string) env('ACARA_AI_MODEL', 'openai'));
        if ($model === '') {
            throw new RuntimeException('Model ACARA AI Pollinations belum dikonfigurasi.');
        }

        $payload = $this->payload($model, $prompt, $imageWidth, $imageHeight, $imageDataUrl, [
            'intent' => (string) ($context['intent'] ?? 'new_design'),
            'history' => is_array($context['history'] ?? null) ? $context['history'] : [],
            'pageContext' => is_array($context['pageContext'] ?? null) ? $context['pageContext'] : [],
        ]);

        $client = service('curlrequest', [
            'timeout' => (int) env('ACARA_AI_TIMEOUT', 60),
            'http_errors' => false,
        ]);

        $response = $client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json; charset=utf-8',
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status < 200 || $status >= 300) {
            log_message('error', 'ACARA AI Pollinations request gagal: status={status}, body={body}', [
                'status' => (string) $status,
                'body' => mb_substr($body, 0, 1000),
            ]);
            throw new RuntimeException($this->errorMessage($status));
        }

        $json = json_decode($body, true);
        $text = $this->extractResponseText(is_array($json) ? $json : []);
        $blueprint = $this->decodeBlueprintText($text);
        if (! is_array($blueprint)) {
            log_message('error', 'ACARA AI Pollinations mengembalikan JSON tidak valid: {body}', [
                'body' => mb_substr($text, 0, 1000),
            ]);
            throw new RuntimeException('ACARA AI mengembalikan JSON tidak valid.');
        }

        $blueprint['imageWidth'] = $imageWidth;
        $blueprint['imageHeight'] = $imageHeight;

        return $blueprint;
    }

    private function payload(string $model, string $prompt, int $imageWidth, int $imageHeight, string $imageDataUrl, array $context): array
    {
        $userContent = [[
            'type' => 'text',
            'text' => $this->userPrompt($prompt, $imageDataUrl !== ''),
        ]];

        if ($imageDataUrl !== '') {
            $userContent[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $imageDataUrl,
                    'detail' => 'high',
                ],
            ];
        }

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt($imageWidth, $imageHeight, $context),
                ],
                [
                    'role' => 'user',
                    'content' => $userContent,
                ],
            ],
            'temperature' => (float) env('ACARA_AI_TEMPERATURE', 0.55),
            'max_tokens' => (int) env('ACARA_AI_MAX_TOKENS', 8192),
            'safe' => env('ACARA_AI_SAFE', 'privacy,secrets'),
        ];

        $responseFormat = strtolower(trim((string) env('ACARA_AI_RESPONSE_FORMAT', 'json_object')));
        if ($responseFormat === 'json_schema') {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'adaacara_acara_ai_blueprint',
                    'strict' => false,
                    'schema' => GeminiVisionBlueprintService::blueprintSchema(),
                ],
            ];
        } elseif ($responseFormat !== 'none') {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        return $payload;
    }

    private function endpoint(): string
    {
        $baseUrl = rtrim(trim((string) env('ACARA_AI_BASE_URL', 'https://gen.pollinations.ai/v1')), '/');
        if ($baseUrl === '') {
            $baseUrl = 'https://gen.pollinations.ai/v1';
        }

        if (preg_match('#/chat/completions$#', $baseUrl) === 1) {
            return $baseUrl;
        }

        return $baseUrl . '/chat/completions';
    }

    private function systemPrompt(int $imageWidth, int $imageHeight, array $context): string
    {
        $intent = in_array(($context['intent'] ?? ''), ['new_design', 'redesign_current_page'], true)
            ? (string) $context['intent']
            : 'new_design';
        $intentRule = $intent === 'redesign_current_page'
            ? "- MODE: redesign_current_page.\n- Gunakan konteks halaman aktif sebagai sumber isi dan gaya awal.\n- Buat versi baru yang lebih rapi di halaman baru. Jangan menimpa desain asli."
            : "- MODE: new_design.\n- Buat desain baru dari prompt user.\n- Jangan meniru page lama jika konteks ada; gunakan canvas secara seimbang.";

        $historyJson = $this->safeJsonForPrompt($this->compactHistory((array) ($context['history'] ?? [])), 3000);
        $pageContextJson = $this->safeJsonForPrompt(
            $intent === 'redesign_current_page'
                ? $this->compactPageContext((array) ($context['pageContext'] ?? []))
                : ['available' => false, 'reason' => 'new_design_ignores_page_context'],
            9000
        );

        return <<<ACARA_SYSTEM_PROMPT
Anda adalah ACARA AI, creative director dan layout engine untuk editor undangan digital AdaAcara.

TUGAS UTAMA:
- Ubah instruksi user menjadi blueprint halaman editor yang bisa diedit.
- Output harus berupa JSON murni sesuai schema blueprint, bukan HTML, bukan FabricJS JSON.
- Buat desain yang lengkap: backgroundColor, shapes dekoratif, dan blocks teks.
- Photos, frames, decorations, dan canvasOverlay boleh kosong jika tidak ada asset valid.

DIMENSI CANVAS:
- Lebar {$imageWidth}px.
- Tinggi {$imageHeight}px.
- Koordinat (0,0) ada di kiri atas.
- Semua x,y,width,height wajib pixel canvas.
- x,y untuk teks adalah pojok kiri atas bounding box, bukan titik tengah.

MODE:
{$intentRule}

ATURAN DESAIN:
- Gunakan komposisi seimbang, jangan menumpuk semua teks di kanan.
- Area utama sebaiknya berada di tengah dengan margin 8%-12% dari kiri/kanan.
- Buat hierarchy jelas: pembuka, nama utama, tanggal, lokasi/detail, penutup.
- Jika user tidak memberi nama/tanggal, gunakan teks contoh undangan yang wajar.
- Gunakan blocks untuk teks editable.
- Gunakan shapes untuk panel, divider, aksen, frame sederhana, atau ornamen geometris.
- Jangan memakai URL asset eksternal.
- confidence untuk item desain 0.82 sampai 0.98.
- coverOpacity untuk blocks prompt-only adalah 0.
- color, fill, stroke harus hex #RRGGBB.
- align hanya left, center, right.
- styleHint hanya script, serif, sans-serif, display, handwriting.
- weightHint hanya 100,200,300,400,500,600,700,800,900.

KONTEKS HALAMAN AKTIF:
{$pageContextJson}

RIWAYAT CHAT:
{$historyJson}

OUTPUT:
- JSON murni.
- Jangan markdown.
- Jangan komentar.
- Jangan penjelasan.
ACARA_SYSTEM_PROMPT;
    }

    private function userPrompt(string $prompt, bool $hasImage): string
    {
        $imageRule = $hasImage
            ? 'Ada gambar referensi. Wajib gunakan gambar tersebut sebagai elemen visual utama di hasil desain, kecuali prompt user secara eksplisit menyebut gambar hanya sebagai referensi/inspirasi.'
            : 'Tidak ada gambar referensi. Buat desain dari prompt user saja.';

        return <<<ACARA_USER_PROMPT
{$imageRule}

PROMPT USER:
"""
{$prompt}
"""
ACARA_USER_PROMPT;
    }

    private function compactHistory(array $history): array
    {
        $items = [];
        foreach (array_slice($history, -8) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $text = trim(mb_substr((string) ($item['text'] ?? ''), 0, 600));
            if ($text === '') {
                continue;
            }
            $items[] = [
                'role' => (($item['role'] ?? '') === 'user') ? 'user' : 'assistant',
                'text' => $text,
            ];
        }

        return $items;
    }

    private function compactPageContext(array $context): array
    {
        if (empty($context)) {
            return ['available' => false];
        }

        $objects = [];
        foreach (array_slice((array) ($context['objects'] ?? []), 0, 80) as $object) {
            if (! is_array($object)) {
                continue;
            }
            $objects[] = [
                'type' => mb_substr((string) ($object['type'] ?? ''), 0, 40),
                'customType' => mb_substr((string) ($object['customType'] ?? ''), 0, 60),
                'text' => mb_substr((string) ($object['text'] ?? ''), 0, 220),
                'left' => (float) ($object['left'] ?? 0),
                'top' => (float) ($object['top'] ?? 0),
                'width' => (float) ($object['width'] ?? 0),
                'height' => (float) ($object['height'] ?? 0),
                'fill' => mb_substr((string) ($object['fill'] ?? ''), 0, 40),
                'fontFamily' => mb_substr((string) ($object['fontFamily'] ?? ''), 0, 80),
                'fontSize' => (float) ($object['fontSize'] ?? 0),
                'textAlign' => mb_substr((string) ($object['textAlign'] ?? ''), 0, 20),
            ];
        }

        return [
            'available' => true,
            'mode' => 'duplicate_and_redesign_current_page',
            'title' => mb_substr((string) ($context['title'] ?? ''), 0, 120),
            'artboard' => $context['artboard'] ?? [],
            'backgroundColor' => mb_substr((string) ($context['backgroundColor'] ?? '#ffffff'), 0, 40),
            'objectCount' => (int) ($context['objectCount'] ?? count($objects)),
            'objects' => $objects,
        ];
    }

    private function safeJsonForPrompt(array $value, int $limit): string
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($json) || $json === '') {
            return '{}';
        }

        return mb_substr($json, 0, $limit);
    }

    private function extractResponseText(array $json): string
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

        throw new RuntimeException('ACARA AI tidak mengembalikan JSON.');
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

    private function decodeBlueprintText(string $text): ?array
    {
        $candidates = [
            trim($text),
            $this->extractJsonObject($text),
        ];

        $decodedText = json_decode(trim($text), true);
        if (is_string($decodedText)) {
            $candidates[] = $decodedText;
            $candidates[] = $this->extractJsonObject($decodedText);
        } elseif (is_array($decodedText)) {
            return $decodedText;
        }

        foreach ($candidates as $candidate) {
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function errorMessage(int $status): string
    {
        if ($status === 401 || $status === 403) {
            return 'API key ACARA AI tidak valid.';
        }
        if ($status === 402) {
            return 'Credit ACARA AI tidak cukup.';
        }
        if ($status === 429) {
            return 'ACARA AI sedang terlalu ramai. Coba lagi sebentar.';
        }

        return 'ACARA AI gagal membuat desain.';
    }
}
