<?php

namespace App\Libraries;

use RuntimeException;

class GeminiMagicLayerOcrService implements OcrProviderInterface
{
    private const MAX_IMAGE_BYTES = 8_388_608;

    public function detectText(string $absolutePath, array $context = []): array
    {
        $apiKey = trim((string) env('GEMINI_API_KEY', env('GOOGLE_GEMINI_API_KEY', '')));
        if ($apiKey === '') {
            throw new RuntimeException('Magic Layer OCR belum dikonfigurasi di server.');
        }

        if (! is_file($absolutePath) || filesize($absolutePath) === false || filesize($absolutePath) > self::MAX_IMAGE_BYTES) {
            throw new RuntimeException('Gambar Magic Layer tidak valid untuk OCR.');
        }

        $imageBytes = file_get_contents($absolutePath);
        if ($imageBytes === false) {
            throw new RuntimeException('Gambar Magic Layer gagal dibaca.');
        }

        $mime = (string) ($context['mime'] ?? '');
        if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Format gambar Magic Layer tidak didukung OCR.');
        }

        $info = @getimagesize($absolutePath);
        $imageWidth = (int) ($info[0] ?? 0);
        $imageHeight = (int) ($info[1] ?? 0);
        if ($imageWidth <= 0 || $imageHeight <= 0) {
            throw new RuntimeException('Dimensi gambar Magic Layer tidak terbaca.');
        }

        $payload = $this->buildPayload($imageBytes, $mime, $imageWidth, $imageHeight);
        $model = trim((string) env('GEMINI_MAGIC_LAYER_MODEL', env('GEMINI_VISION_MODEL', 'gemini-3.5-flash')));
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);

        $client = service('curlrequest', [
            'timeout' => (int) env('GEMINI_MAGIC_LAYER_TIMEOUT', env('GEMINI_VISION_TIMEOUT', 45)),
            'http_errors' => false,
        ]);

        $response = $client->post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status < 200 || $status >= 300) {
            log_message('error', 'Magic Layer OCR request gagal: status={status}, body={body}', [
                'status' => (string) $status,
                'body' => mb_substr($body, 0, 1000),
            ]);
            throw new RuntimeException('Magic Layer OCR gagal membaca teks gambar.');
        }

        $json = json_decode($body, true);
        $text = $this->extractResponseText(is_array($json) ? $json : []);
        $blueprint = $this->decodeBlueprintJson($text);
        if (! is_array($blueprint)) {
            log_message('error', 'Magic Layer OCR mengembalikan JSON tidak valid: {body}', [
                'body' => mb_substr($text, 0, 1000),
            ]);
            throw new RuntimeException('Magic Layer OCR mengembalikan JSON tidak valid.');
        }

        $blueprint['imageWidth'] = $imageWidth;
        $blueprint['imageHeight'] = $imageHeight;

        return $blueprint;
    }

    private function buildPayload(string $imageBytes, string $mime, int $imageWidth, int $imageHeight): array
    {
        $generationConfig = [
            'temperature' => 0,
            'topP' => 0.4,
            'maxOutputTokens' => (int) env('GEMINI_MAGIC_LAYER_MAX_OUTPUT_TOKENS', 8192),
            'responseMimeType' => 'application/json',
        ];

        if (filter_var(env('GEMINI_MAGIC_LAYER_RESPONSE_SCHEMA', false), FILTER_VALIDATE_BOOLEAN)) {
            $generationConfig['responseSchema'] = $this->schema();
        }

        return [
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => $this->prompt($imageWidth, $imageHeight)],
                    [
                        'inlineData' => [
                            'mimeType' => $mime === 'image/jpg' ? 'image/jpeg' : $mime,
                            'data' => base64_encode($imageBytes),
                        ],
                    ],
                ],
            ]],
            'generationConfig' => $generationConfig,
        ];
    }

    private function prompt(int $imageWidth, int $imageHeight): string
    {
        return <<<PROMPT
Anda adalah mesin OCR layout teknis untuk fitur Magic Layer editor gambar.

TUGAS:
- Ekstrak HANYA teks yang benar-benar terlihat pada gambar.
- Analisis struktur visual secara pasif agar hasil Magic Layer terasa lebih rapi di editor.
- Jangan membuat desain baru.
- Jangan mendeskripsikan gambar.
- Jangan menambahkan teks yang tidak terlihat.
- Jangan mengarang ornamen, frame, section, atau object yang tidak terlihat.
- Ornamen, frame, shape, section, alignment, spacing, dan hierarchy hanya boleh dikembalikan sebagai metadata teknis.

KOORDINAT WAJIB:
- Gambar asli berukuran {$imageWidth}px x {$imageHeight}px.
- Titik (0,0) adalah kiri atas gambar asli.
- Semua x, y, width, height wajib dalam pixel gambar asli, bukan persen dan bukan ukuran canvas editor.
- x,y adalah pojok kiri atas bounding box teks.
- width,height harus mencakup area visual teks dengan ketat, jangan terlalu besar.

PENGELOMPOKAN TEKS:
- Gabungkan multi-line hanya jika baris-barisnya satu blok visual yang sama, jaraknya dekat, font/warna/alignment mirip.
- Pisahkan judul, nama, tanggal, alamat, paragraf, dan detail kecil bila ukuran/fungsi/jarak berbeda.
- Jika kata berjauhan, pisahkan menjadi blok berbeda.

ATRIBUT:
- fontSize adalah estimasi ukuran font visual dalam pixel gambar asli.
- color adalah warna teks hex terdekat.
- align hanya left, center, atau right.
- styleHint hanya script, serif, sans-serif, display, handwriting.
- weightHint angka 100-900.
- role hanya: heading, subheading, body, caption, button, date, name, location, other.
- hierarchyLevel angka 1-5: 1 paling dominan, 5 paling kecil.
- spacingHint hanya tight, normal, airy.
- sectionId dan groupId boleh string pendek bila block terkait section/group yang sama.
- confidence 0-1.

ANALISIS LAYOUT PASIF:
- section mewakili area besar seperti hero, details, location, rsvp, footer, other.
- group mewakili elemen yang secara visual dekat dan sefungsi, misalnya title_group, date_group, address_group.
- frame hanya untuk area foto/bingkai yang jelas terlihat.
- decorations hanya untuk ornament dekoratif seperti floral, foliage, divider, border, illustration.
- shapes hanya untuk bentuk dasar yang jelas terlihat.
- Jangan membuat terlalu banyak metadata. Prioritaskan yang confidence tinggi.

OUTPUT:
- Kembalikan JSON murni sesuai schema.
- Jangan markdown, jangan komentar, jangan penjelasan.
- Output harus berupa satu object JSON yang diawali "{" dan diakhiri "}".
- Jika tidak ada teks yang terbaca, tetap kembalikan object valid dengan {"blocks":[]}.
PROMPT;
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'backgroundColor' => ['type' => 'string'],
                'blocks' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'text' => ['type' => 'string'],
                            'confidence' => ['type' => 'number'],
                            'x' => ['type' => 'number'],
                            'y' => ['type' => 'number'],
                            'width' => ['type' => 'number'],
                            'height' => ['type' => 'number'],
                            'fontSize' => ['type' => 'number'],
                            'angle' => ['type' => 'number'],
                            'color' => ['type' => 'string'],
                            'align' => ['type' => 'string', 'enum' => ['center', 'left', 'right']],
                            'styleHint' => ['type' => 'string', 'enum' => ['script', 'serif', 'sans-serif', 'display', 'handwriting']],
                            'weightHint' => ['type' => 'integer'],
                            'role' => ['type' => 'string', 'enum' => ['heading', 'subheading', 'body', 'caption', 'button', 'date', 'name', 'location', 'other']],
                            'sectionId' => ['type' => 'string'],
                            'groupId' => ['type' => 'string'],
                            'hierarchyLevel' => ['type' => 'integer'],
                            'spacingHint' => ['type' => 'string', 'enum' => ['tight', 'normal', 'airy']],
                            'italic' => ['type' => 'boolean'],
                            'backgroundColor' => ['type' => 'string'],
                            'coverOpacity' => ['type' => 'number'],
                            'needsReview' => ['type' => 'boolean'],
                        ],
                        'required' => ['text', 'confidence', 'x', 'y', 'width', 'height'],
                    ],
                ],
                'sections' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string'],
                            'kind' => ['type' => 'string', 'enum' => ['hero', 'details', 'location', 'rsvp', 'footer', 'other']],
                            'confidence' => ['type' => 'number'],
                            'x' => ['type' => 'number'],
                            'y' => ['type' => 'number'],
                            'width' => ['type' => 'number'],
                            'height' => ['type' => 'number'],
                        ],
                    ],
                ],
                'groups' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string'],
                            'kind' => ['type' => 'string', 'enum' => ['title_group', 'text_group', 'date_group', 'address_group', 'media_group', 'ornament_group', 'cta_group', 'other']],
                            'confidence' => ['type' => 'number'],
                            'x' => ['type' => 'number'],
                            'y' => ['type' => 'number'],
                            'width' => ['type' => 'number'],
                            'height' => ['type' => 'number'],
                        ],
                    ],
                ],
                'frames' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'confidence' => ['type' => 'number'],
                            'x' => ['type' => 'number'],
                            'y' => ['type' => 'number'],
                            'width' => ['type' => 'number'],
                            'height' => ['type' => 'number'],
                            'angle' => ['type' => 'number'],
                            'shape' => ['type' => 'string', 'enum' => ['rect', 'rounded-rect', 'circle', 'arch']],
                            'needsReview' => ['type' => 'boolean'],
                        ],
                    ],
                ],
                'decorations' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'kind' => ['type' => 'string', 'enum' => ['flower', 'foliage', 'ornament', 'frame', 'divider', 'illustration', 'logo', 'other']],
                            'confidence' => ['type' => 'number'],
                            'x' => ['type' => 'number'],
                            'y' => ['type' => 'number'],
                            'width' => ['type' => 'number'],
                            'height' => ['type' => 'number'],
                            'angle' => ['type' => 'number'],
                            'needsReview' => ['type' => 'boolean'],
                            'needsBackgroundRemoval' => ['type' => 'boolean'],
                        ],
                    ],
                ],
                'shapes' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'kind' => ['type' => 'string', 'enum' => ['rect', 'rounded-rect', 'circle', 'oval', 'polygon', 'line', 'divider']],
                            'confidence' => ['type' => 'number'],
                            'x' => ['type' => 'number'],
                            'y' => ['type' => 'number'],
                            'width' => ['type' => 'number'],
                            'height' => ['type' => 'number'],
                            'angle' => ['type' => 'number'],
                            'fill' => ['type' => 'string'],
                            'stroke' => ['type' => 'string'],
                            'strokeWidth' => ['type' => 'number'],
                            'opacity' => ['type' => 'number'],
                            'needsReview' => ['type' => 'boolean'],
                        ],
                    ],
                ],
                'style' => [
                    'type' => 'object',
                    'properties' => [
                        'tone' => ['type' => 'string'],
                        'palette' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                        'alignment' => ['type' => 'string', 'enum' => ['left', 'center', 'right', 'mixed']],
                        'spacing' => ['type' => 'string', 'enum' => ['tight', 'normal', 'airy']],
                    ],
                ],
            ],
            'required' => ['blocks'],
        ];
    }

    private function extractResponseText(array $json): string
    {
        $parts = $json['candidates'][0]['content']['parts'] ?? [];
        $text = '';
        foreach ((array) $parts as $part) {
            if (isset($part['text']) && is_string($part['text'])) {
                $text .= $part['text'];
            }
        }

        $text = trim($text);
        if ($text === '') {
            throw new RuntimeException('Magic Layer OCR tidak mengembalikan JSON.');
        }

        return $text;
    }

    private function decodeBlueprintJson(string $text): ?array
    {
        $candidates = [];
        $cleanText = $this->cleanJsonText($text);
        $candidates[] = $cleanText;
        $candidates[] = $this->extractJsonObject($cleanText);

        foreach (array_unique(array_filter($candidates, static fn (string $value): bool => trim($value) !== '')) as $candidate) {
            $decoded = $this->decodeJsonCandidate($candidate);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function decodeJsonCandidate(string $candidate)
    {
        $candidate = $this->cleanJsonText($candidate);
        $decoded = json_decode($candidate, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (is_string($decoded)) {
            $nested = json_decode($this->cleanJsonText($decoded), true);
            if (is_array($nested)) {
                return $nested;
            }
        }

        $withoutTrailingCommas = preg_replace('/,\s*([}\]])/', '$1', $candidate) ?? $candidate;
        if ($withoutTrailingCommas !== $candidate) {
            $decoded = json_decode($withoutTrailingCommas, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function cleanJsonText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text) ?? $text;
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text) ?? $text;

        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```$/', '', $text) ?? $text;
        }

        return trim($text);
    }

    private function extractJsonObject(string $text): string
    {
        $text = $this->cleanJsonText($text);

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end >= $start) {
            return substr($text, $start, $end - $start + 1);
        }

        return $text;
    }
}
