<?php

namespace App\Libraries;

use RuntimeException;

class GeminiVisionBlueprintService implements OcrProviderInterface
{
    private const MAX_IMAGE_BYTES = 8_388_608;

    public function detectText(string $absolutePath, array $context = []): array
    {
        $apiKey = trim((string) env('GEMINI_API_KEY', env('GOOGLE_GEMINI_API_KEY', '')));
        if ($apiKey === '') {
            throw new RuntimeException('AdaAcara AI belum dikonfigurasi di server.');
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

        $payload = $this->buildPayload($imageBytes, $mime, $imageWidth, $imageHeight, (string) ($context['creative_prompt'] ?? ''));
        $model = trim((string) env('GEMINI_DESIGN_MODEL', env('GEMINI_VISION_MODEL', 'gemini-3.5-flash')));
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);

        $client = service('curlrequest', [
            'timeout' => (int) env('GEMINI_VISION_TIMEOUT', 45),
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
            log_message('error', 'AdaAcara AI request gagal: status={status}, body={body}', [
                'status' => (string) $status,
                'body' => mb_substr($body, 0, 1000),
            ]);
            throw new RuntimeException('AdaAcara AI gagal membaca gambar referensi.');
        }

        $json = json_decode($body, true);
        $text = $this->extractResponseText(is_array($json) ? $json : []);
        $blueprint = json_decode($this->extractJsonObject($text), true);
        if (! is_array($blueprint)) {
            log_message('error', 'AdaAcara AI mengembalikan JSON tidak valid: {body}', [
                'body' => mb_substr($text, 0, 1000),
            ]);
            throw new RuntimeException('AdaAcara AI mengembalikan blueprint tidak valid.');
        }

        $blueprint['imageWidth'] = $imageWidth;
        $blueprint['imageHeight'] = $imageHeight;

        return $blueprint;
    }

    private function buildPayload(string $imageBytes, string $mime, int $imageWidth, int $imageHeight, string $creativePrompt = ''): array
    {
        return [
            'contents' => [[
                'role' => 'user',
                'parts' => [
                    ['text' => $this->prompt($imageWidth, $imageHeight, $creativePrompt)],
                    [
                        'inlineData' => [
                            'mimeType' => $mime === 'image/jpg' ? 'image/jpeg' : $mime,
                            'data' => base64_encode($imageBytes),
                        ],
                    ],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.1,
                'topP' => 0.8,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
                'responseSchema' => $this->schema(),
            ],
        ];
    }

    public static function blueprintPrompt(int $imageWidth, int $imageHeight, string $creativePrompt = ''): string
    {
        return (new self())->prompt($imageWidth, $imageHeight, $creativePrompt);
    }

    public static function blueprintSchema(): array
    {
        return (new self())->schema();
    }

    private function prompt(int $imageWidth, int $imageHeight, string $creativePrompt = ''): string
        {
            $manualPrompt = trim(mb_substr($creativePrompt, 0, 1200));
            $manualSection = $manualPrompt !== ''
                ? "\n\nPROMPT MANUAL USER UNTUK ACARA AI:\n- Ikuti arahan kreatif user berikut sepanjang tetap aman dan masih menghasilkan blueprint editor yang valid.\n- Jika arahan user bertentangan dengan gambar referensi, prioritaskan arahan user untuk style/layout/copy, tetapi jangan invent data pribadi yang tidak diberikan.\n\"\"\"\n{$manualPrompt}\n\"\"\"\n"
                : "\n\nPROMPT MANUAL USER UNTUK ACARA AI:\n- Tidak ada prompt manual. Gunakan gambar referensi sebagai arahan utama.\n";

            return <<<PROMPT
    Anda adalah AI AdaAcara untuk mengubah gambar referensi undangan menjadi blueprint objek editor FabricJS. Tugas Anda membaca desain secara visual dan mengembalikan struktur JSON yang bisa dikonversi menjadi elemen editor.
    Anda boleh mengikuti prompt manual user untuk arahan kreatif seperti tema, gaya, copywriting, warna, komposisi, dan elemen yang ingin dibuat.
    {$manualSection}
    
    DIMENSI KANVAS ACUAN MUTLAK:
    - Lebar: {$imageWidth}px
    - Tinggi: {$imageHeight}px

    ATURAN OUTPUT:
    - Kembalikan JSON murni sesuai schema. Jangan markdown, jangan komentar, jangan penjelasan.
    - Titik koordinat (0,0) ada di kiri atas gambar.
    - Deteksi semua elemen utama yang terlihat: teks, frame/foto, ornamen, shape solid, garis, dan overlay transparan.
    - Jangan membuat FabricJS JSON langsung. Kembalikan blueprint netral sesuai schema saja.
    - Isi backgroundColor dengan warna dominan halaman/desain. Contoh: merah marun, putih, krem, atau warna polos utama.

    TEKS:
    - Masukkan teks ke "blocks". Gabungkan baris yang satu fungsi/paragraph bila font, warna, ukuran, dan alignment mirip.
    - Pisahkan judul, nama, tanggal, alamat, paragraph, dan teks dengan font/warna/ukuran berbeda.
    - Sertakan backgroundColor dan coverOpacity agar editor bisa menutup teks lama sebelum membuat textbox editable.
    - Gunakan styleHint: script, serif, sans-serif, display.

    FRAME/FOTO:
    - Jika ada area foto kosong yang harus bisa diganti user, masukkan ke "frames".
    - Jika ada foto asli yang harus dipertahankan dari desain, masukkan ke "photos".

    SHAPE:
    - Masukkan banner, kotak warna, polygon, garis diagonal, frame garis, divider, dan background geometric ke "shapes".
    - Untuk polygon sederhana, isi "points" bila bentuk tidak bisa diwakili rect.

    ORNAMEN:
    - Masukkan bunga, ilustrasi, line-art, dekorasi sudut, logo, dan divider dekoratif ke "decorations".
    - Gunakan bounding box makro untuk satu rangkaian visual yang menyatu. Jangan pecah bunga/sudut menjadi serpihan kecil.
    - Jika ornamen menyatu dengan teks, set containsText true dan extractable false.

    OVERLAY:
    - Jika gambar adalah PNG/WebP transparan atau desain ornamen siap pakai yang lebih baik dipertahankan penuh, set canvasOverlay.enabled true.
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
                        'description' => 'Daftar teks yang terlihat pada gambar.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'text' => ['type' => 'string'],
                                'confidence' => ['type' => 'number'],
                                'x' => ['type' => 'number'],
                                'y' => ['type' => 'number'],
                                'width' => ['type' => 'number'],
                                'height' => ['type' => 'number'],
                                'angle' => ['type' => 'number'],
                                'color' => ['type' => 'string'],
                                'align' => ['type' => 'string', 'enum' => ['center', 'left', 'right']],
                                'styleHint' => ['type' => 'string', 'enum' => ['script', 'serif', 'sans-serif', 'display']],
                                'weightHint' => ['type' => 'integer'],
                                'italic' => ['type' => 'boolean'],
                                'needsReview' => ['type' => 'boolean'],
                                'backgroundColor' => ['type' => 'string'],
                                'coverOpacity' => ['type' => 'number'],
                            ],
                            'required' => ['text', 'confidence', 'x', 'y', 'width', 'height'],
                        ],
                    ],
                    'frames' => [
                        'type' => 'array',
                        'description' => 'Area placeholder foto yang sebaiknya editable/dapat diganti user.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'confidence' => ['type' => 'number'],
                                'x' => ['type' => 'number'],
                                'y' => ['type' => 'number'],
                                'width' => ['type' => 'number'],
                                'height' => ['type' => 'number'],
                                'angle' => ['type' => 'number'],
                                'shape' => ['type' => 'string', 'enum' => ['rect', 'rounded-rect', 'circle', 'oval', 'arch']],
                                'needsReview' => ['type' => 'boolean'],
                            ],
                            'required' => ['confidence', 'x', 'y', 'width', 'height'],
                        ],
                    ],
                    'decorations' => [
                        'type' => 'array',
                        'description' => 'Ornamen visual seperti bunga, ilustrasi, line-art, logo, divider dekoratif.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'kind' => ['type' => 'string', 'enum' => ['floral', 'line-art', 'geometric', 'abstract', 'ornament', 'background']],
                                'confidence' => ['type' => 'number'],
                                'x' => ['type' => 'number'],
                                'y' => ['type' => 'number'],
                                'width' => ['type' => 'number'],
                                'height' => ['type' => 'number'],
                                'angle' => ['type' => 'number'],
                                'needsReview' => ['type' => 'boolean'],
                                'needsBackgroundRemoval' => ['type' => 'boolean'],
                                'needsSegmentation' => ['type' => 'boolean'],
                                'containsText' => ['type' => 'boolean'],
                                'extractable' => ['type' => 'boolean'],
                            ],
                            'required' => ['kind', 'confidence', 'x', 'y', 'width', 'height'],
                        ],
                    ],
                    'photos' => [
                        'type' => 'array',
                        'description' => 'Foto bitmap asli yang harus dipertahankan sebagai image object.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'confidence' => ['type' => 'number'],
                                'x' => ['type' => 'number'],
                                'y' => ['type' => 'number'],
                                'width' => ['type' => 'number'],
                                'height' => ['type' => 'number'],
                                'angle' => ['type' => 'number'],
                                'shape' => ['type' => 'string', 'enum' => ['rect', 'rounded-rect', 'circle', 'oval', 'arch']],
                                'needsReview' => ['type' => 'boolean'],
                            ],
                            'required' => ['confidence', 'x', 'y', 'width', 'height'],
                        ],
                    ],
                    'shapes' => [
                        'type' => 'array',
                        'description' => 'Bentuk dasar seperti rect, rounded-rect, circle, polygon, line, divider.',
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
                                'points' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'x' => ['type' => 'number'],
                                            'y' => ['type' => 'number'],
                                        ],
                                    ],
                                ],
                                'needsReview' => ['type' => 'boolean'],
                            ],
                            'required' => ['kind', 'confidence', 'x', 'y', 'width', 'height'],
                        ],
                    ],
                    'canvasOverlay' => [
                        'type' => 'object',
                        'description' => 'Gunakan true jika gambar sebaiknya dipertahankan sebagai overlay penuh.',
                        'properties' => [
                            'enabled' => ['type' => 'boolean'],
                            'confidence' => ['type' => 'number'],
                            'reason' => ['type' => 'string'],
                        ],
                    ],
                ],
                'required' => ['blocks'],
            ];
        }

    private function extractResponseText(array $json): string
    {
        $parts = $json['candidates'][0]['content']['parts'] ?? [];
        foreach ((array) $parts as $part) {
            if (isset($part['text']) && is_string($part['text'])) {
                return trim($part['text']);
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
