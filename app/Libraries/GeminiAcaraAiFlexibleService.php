<?php

namespace App\Libraries;

use RuntimeException;

class GeminiAcaraAiFlexibleService
{
    private const MAX_REFERENCE_IMAGE_BYTES = 8_388_608;

    public function generate(string $prompt, array $context = []): array
    {
        $prompt = trim(mb_substr($prompt, 0, 2000));
        if ($prompt === '') {
            throw new RuntimeException('Prompt ACARA AI tidak boleh kosong.');
        }

        $model = $this->model();
        $outputMode = $this->outputMode($model);
        if ($outputMode === 'blueprint') {
            return (new GeminiAcaraAiPromptService())->generate($prompt, $context);
        }

        return $this->generateImageBlueprint($prompt, $context, $model);
    }

    private function generateImageBlueprint(string $prompt, array $context, string $model): array
    {
        $apiKey = trim((string) env('ACARA_AI_GEMINI_API_KEY', env('GEMINI_API_KEY', env('GOOGLE_GEMINI_API_KEY', ''))));
        if ($apiKey === '') {
            throw new RuntimeException('ACARA AI Gemini belum dikonfigurasi di server.');
        }

        $imageWidth = max(320, min(6000, (int) ($context['imageWidth'] ?? 1080)));
        $imageHeight = max(320, min(6000, (int) ($context['imageHeight'] ?? 1920)));
        [$referenceBytes, $referenceMime] = $this->referenceImage($context);
        if ($this->isImagenModel($model)) {
            return $this->generateImagenBlueprint($prompt, $context, $model, $apiKey, $imageWidth, $imageHeight);
        }

        $payload = $this->imagePayload($model, $prompt, $imageWidth, $imageHeight, $referenceBytes, $referenceMime, [
            'intent' => (string) ($context['intent'] ?? 'new_design'),
            'history' => is_array($context['history'] ?? null) ? $context['history'] : [],
            'pageContext' => is_array($context['pageContext'] ?? null) ? $context['pageContext'] : [],
        ]);

        $client = service('curlrequest', [
            'timeout' => (int) env('ACARA_AI_GEMINI_TIMEOUT', env('GEMINI_ACARA_AI_TIMEOUT', env('GEMINI_VISION_TIMEOUT', 90))),
            'http_errors' => false,
        ]);

        $response = $client->post('https://generativelanguage.googleapis.com/v1beta/interactions', [
            'headers' => [
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json; charset=utf-8',
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status < 200 || $status >= 300) {
            log_message('error', 'ACARA AI Gemini image request gagal: status={status}, body={body}', [
                'status' => (string) $status,
                'body' => mb_substr($body, 0, 1000),
            ]);
            throw new RuntimeException('ACARA AI gagal membuat desain gambar.');
        }

        $json = json_decode($body, true);
        $image = $this->extractImage(is_array($json) ? $json : []);
        $asset = $this->storeGeneratedImage($image['data'], $image['mime'], $context);

        return [
            'imageWidth' => $imageWidth,
            'imageHeight' => $imageHeight,
            'backgroundColor' => '#ffffff',
            'blocks' => [],
            'frames' => [],
            'decorations' => [],
            'photos' => [],
            'shapes' => [],
            'canvasOverlay' => [
                'enabled' => true,
                'confidence' => 0.96,
                'x' => 0,
                'y' => 0,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'assetSrc' => $asset['src'],
                'assetName' => $asset['name'],
                'mime' => $asset['mime'],
                'needsReview' => true,
            ],
        ];
    }

    private function generateImagenBlueprint(string $prompt, array $context, string $model, string $apiKey, int $imageWidth, int $imageHeight): array
    {
        $payload = $this->imagenPayload($prompt, $imageWidth, $imageHeight, [
            'intent' => (string) ($context['intent'] ?? 'new_design'),
            'history' => is_array($context['history'] ?? null) ? $context['history'] : [],
            'pageContext' => is_array($context['pageContext'] ?? null) ? $context['pageContext'] : [],
        ]);

        $client = service('curlrequest', [
            'timeout' => (int) env('ACARA_AI_GEMINI_TIMEOUT', env('GEMINI_ACARA_AI_TIMEOUT', env('GEMINI_VISION_TIMEOUT', 90))),
            'http_errors' => false,
        ]);

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':predict';
        $response = $client->post($endpoint, [
            'headers' => [
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json; charset=utf-8',
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status < 200 || $status >= 300) {
            log_message('error', 'ACARA AI Imagen request gagal: status={status}, body={body}', [
                'status' => (string) $status,
                'body' => mb_substr($body, 0, 1000),
            ]);
            throw new RuntimeException('ACARA AI gagal membuat desain gambar.');
        }

        $json = json_decode($body, true);
        $image = $this->extractImagenImage(is_array($json) ? $json : []);
        $asset = $this->storeGeneratedImage($image['data'], $image['mime'], $context);

        return [
            'imageWidth' => $imageWidth,
            'imageHeight' => $imageHeight,
            'backgroundColor' => '#ffffff',
            'blocks' => [],
            'frames' => [],
            'decorations' => [],
            'photos' => [],
            'shapes' => [],
            'canvasOverlay' => [
                'enabled' => true,
                'confidence' => 0.96,
                'x' => 0,
                'y' => 0,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'assetSrc' => $asset['src'],
                'assetName' => $asset['name'],
                'mime' => $asset['mime'],
                'needsReview' => true,
            ],
        ];
    }

    private function model(): string
    {
        $model = trim((string) env('ACARA_AI_GEMINI_MODEL', env('GEMINI_ACARA_AI_MODEL', 'gemini-2.5-flash-image')));
        return $model !== '' ? $model : 'gemini-2.5-flash-image';
    }

    private function outputMode(string $model): string
    {
        if ($this->isImagenModel($model)) {
            return 'image';
        }

        $mode = strtolower(trim((string) env('ACARA_AI_GEMINI_OUTPUT', 'auto')));
        if (in_array($mode, ['image', 'blueprint'], true)) {
            return $mode;
        }

        return str_contains(strtolower($model), 'image') ? 'image' : 'blueprint';
    }

    private function isImagenModel(string $model): bool
    {
        return str_starts_with(strtolower(trim($model)), 'imagen-');
    }

    private function referenceImage(array $context): array
    {
        $imagePath = (string) ($context['imagePath'] ?? '');
        if ($imagePath === '' || ! is_file($imagePath)) {
            return [null, ''];
        }

        $size = filesize($imagePath);
        if ($size === false || $size <= 0 || $size > self::MAX_REFERENCE_IMAGE_BYTES) {
            throw new RuntimeException('Gambar referensi ACARA AI tidak valid.');
        }

        $mime = (string) ($context['mime'] ?? '');
        if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Format gambar referensi ACARA AI tidak didukung.');
        }

        $bytes = file_get_contents($imagePath);
        if ($bytes === false) {
            throw new RuntimeException('Gambar referensi ACARA AI gagal dibaca.');
        }

        return [$bytes, $mime === 'image/jpg' ? 'image/jpeg' : $mime];
    }

    private function imagePayload(string $model, string $prompt, int $imageWidth, int $imageHeight, ?string $referenceBytes, string $referenceMime, array $context): array
    {
        $input = [[
            'type' => 'text',
            'text' => $this->imagePrompt($prompt, $imageWidth, $imageHeight, $referenceBytes !== null, $context),
        ]];

        if ($referenceBytes !== null) {
            $input[] = [
                'type' => 'image',
                'mime_type' => $referenceMime,
                'data' => base64_encode($referenceBytes),
            ];
        }

        $payload = [
            'model' => $model,
            'input' => $input,
            'response_format' => [
                'type' => 'image',
                'mime_type' => strtolower((string) env('ACARA_AI_GEMINI_IMAGE_MIME', 'image/png')) === 'image/jpeg' ? 'image/jpeg' : 'image/png',
                'aspect_ratio' => $this->aspectRatio($imageWidth, $imageHeight),
            ],
        ];

        $imageSize = trim((string) env('ACARA_AI_GEMINI_IMAGE_SIZE', ''));
        if ($imageSize !== '') {
            $payload['response_format']['image_size'] = $imageSize;
        }

        return $payload;
    }

    private function imagenPayload(string $prompt, int $imageWidth, int $imageHeight, array $context): array
    {
        $parameters = [
            'sampleCount' => 1,
            'aspectRatio' => $this->aspectRatio($imageWidth, $imageHeight),
        ];

        $imageSize = trim((string) env('ACARA_AI_GEMINI_IMAGE_SIZE', ''));
        if ($imageSize !== '') {
            $parameters['imageSize'] = $imageSize;
        }

        $personGeneration = trim((string) env('ACARA_AI_IMAGEN_PERSON_GENERATION', 'allow_adult'));
        if (in_array($personGeneration, ['dont_allow', 'allow_adult', 'allow_all'], true)) {
            $parameters['personGeneration'] = $personGeneration;
        }

        return [
            'instances' => [[
                'prompt' => $this->imagenPrompt($prompt, $imageWidth, $imageHeight, $context),
            ]],
            'parameters' => $parameters,
        ];
    }

    private function imagePrompt(string $prompt, int $imageWidth, int $imageHeight, bool $hasReference, array $context): string
    {
        $intent = ($context['intent'] ?? '') === 'redesign_current_page' ? 'redesign_current_page' : 'new_design';
        $referenceRule = $hasReference
            ? 'Ada gambar referensi. Gunakan gambar tersebut sebagai elemen visual utama atau sumber komposisi utama dalam hasil akhir, kecuali prompt user secara eksplisit meminta gambar hanya sebagai inspirasi. Karena mode ini menghasilkan gambar final, jangan menempel file asli mentah jika tidak menyatu dengan desain; buat hasil akhir generated yang tetap jelas mengikuti gambar referensi.'
            : 'Tidak ada gambar referensi. Buat desain dari prompt user.';
        $modeRule = $intent === 'redesign_current_page'
            ? 'Redesign halaman visual dengan semua konten penting dari konteks halaman aktif tetap dipertahankan, namun visual dibuat lebih rapi dan sesuai instruksi user.'
            : 'Buat satu halaman desain visual lengkap dari prompt user.';
        $historyJson = $this->safeJson($this->compactHistory((array) ($context['history'] ?? [])), 1800);
        $pageContextJson = $this->safeJson($intent === 'redesign_current_page' ? $this->compactPageContext((array) ($context['pageContext'] ?? [])) : ['available' => false], 5000);

        return <<<ACARA_IMAGE_PROMPT
Anda adalah ACARA AI, creative director desain visual AdaAcara.

TUGAS:
- {$modeRule}
- Output harus berupa SATU gambar final berorientasi vertikal untuk canvas {$imageWidth}px x {$imageHeight}px.
- {$referenceRule}
- Gunakan komposisi yang jelas, profesional, dan sesuai jenis desain yang diminta user.
- Jangan otomatis membuat undangan pernikahan. Buat undangan/event hanya jika prompt user meminta undangan, wedding, pernikahan, acara, invitation, birthday, aqiqah, seminar, launching, atau event serupa.
- Jika prompt user bersifat umum, ikuti kategori yang diminta user seperti poster, flyer, landing page section, quote card, promo, announcement, menu, jadwal, kartu ucapan, atau desain visual lain.
- Pastikan desain memakai area canvas secara seimbang, tidak berkumpul di kanan saja.
- Jika user tidak memberikan detail, gunakan teks contoh yang relevan dengan prompt user, bukan otomatis nama pasangan atau tanggal pernikahan.
- Jangan memakai template generik "The Wedding of Romeo & Juliet", "Romeo & Juliet", atau "Save the Date 12.12.2024" kecuali user secara eksplisit menulisnya.
- Untuk desain undangan/event yang detailnya belum lengkap, gunakan placeholder Indonesia seperti "Judul Acara", "Nama / Tamu", "Tanggal Acara", dan "Lokasi Acara" agar mudah ditinjau.
- Jika ini redesign halaman aktif, teks/konten penting dari Active page context wajib tetap muncul. Jangan mengganti isi halaman menjadi konten baru yang tidak ada hubungannya.
- Teks yang terlihat harus tajam, terbaca, dan tidak terlalu kecil.
- Jangan membuat mockup di atas meja, jangan ada watermark, jangan ada UI editor.

KONTEKS HALAMAN AKTIF:
{$pageContextJson}

RIWAYAT CHAT SINGKAT:
{$historyJson}

PROMPT USER:
"""
{$prompt}
"""
ACARA_IMAGE_PROMPT;
    }

    private function imagenPrompt(string $prompt, int $imageWidth, int $imageHeight, array $context): string
    {
        $intent = ($context['intent'] ?? '') === 'redesign_current_page' ? 'redesign_current_page' : 'new_design';
        $modeRule = $intent === 'redesign_current_page'
            ? 'Redesign the current visual page while preserving all important text and content from the active page context and improving the layout.'
            : 'Create one complete visual design page from the user request.';
        $historyJson = $this->safeJson($this->compactHistory((array) ($context['history'] ?? [])), 1200);
        $pageContextJson = $this->safeJson($intent === 'redesign_current_page' ? $this->compactPageContext((array) ($context['pageContext'] ?? [])) : ['available' => false], 3500);

        return <<<IMAGEN_PROMPT
Create a single vertical design image for an editable AdaAcara canvas sized {$imageWidth}px by {$imageHeight}px.

Task:
- {$modeRule}
- Follow the user prompt as the main instruction.
- Do not automatically create a wedding invitation unless the user explicitly asks for wedding, invitation, event, birthday, seminar, launch, aqiqah, or a similar event design.
- If the prompt is general, follow the requested category such as poster, flyer, announcement, quote card, promotion, menu, schedule, greeting card, landing page section, or another visual design.
- Use balanced composition across the canvas, not clustered on only one side.
- Make any visible text short, sharp, readable, and placed intentionally.
- Do not use the generic template "The Wedding of Romeo & Juliet", "Romeo & Juliet", or "Save the Date 12.12.2024" unless the user explicitly asks for it.
- If this is a redesign of the active page, important text/content from Active page context must remain visible. Do not replace it with unrelated new content.
- Do not show editor UI, mockup table scenes, watermarks, or browser/app chrome.

Active page context:
{$pageContextJson}

Short chat history:
{$historyJson}

User prompt:
"""
{$prompt}
"""
IMAGEN_PROMPT;
    }

    private function aspectRatio(int $width, int $height): string
    {
        $ratio = $width / max(1, $height);
        if ($ratio > 0.92 && $ratio < 1.08) {
            return '1:1';
        }
        if ($ratio < 0.62) {
            return '9:16';
        }
        if ($ratio < 0.72) {
            return '3:4';
        }
        if ($ratio > 1.65) {
            return '16:9';
        }

        return '4:5';
    }

    private function extractImage(array $json): array
    {
        $image = $json['output_image'] ?? null;
        if (is_array($image) && ! empty($image['data'])) {
            return [
                'data' => (string) $image['data'],
                'mime' => (string) ($image['mime_type'] ?? $image['mimeType'] ?? 'image/png'),
            ];
        }

        foreach ((array) ($json['steps'] ?? []) as $step) {
            foreach ((array) ($step['output'] ?? $step['outputs'] ?? []) as $part) {
                if (is_array($part) && ! empty($part['data']) && (($part['type'] ?? '') === 'image' || isset($part['mime_type']))) {
                    return [
                        'data' => (string) $part['data'],
                        'mime' => (string) ($part['mime_type'] ?? $part['mimeType'] ?? 'image/png'),
                    ];
                }
            }
        }

        log_message('error', 'ACARA AI Gemini image response tanpa output_image: {body}', [
            'body' => mb_substr(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '', 0, 1000),
        ]);
        throw new RuntimeException('ACARA AI tidak mengembalikan gambar.');
    }

    private function extractImagenImage(array $json): array
    {
        foreach ((array) ($json['predictions'] ?? []) as $prediction) {
            if (! is_array($prediction)) {
                continue;
            }

            $data = $prediction['bytesBase64Encoded']
                ?? $prediction['image']['imageBytes']
                ?? $prediction['imageBytes']
                ?? $prediction['data']
                ?? '';
            if (is_string($data) && $data !== '') {
                return [
                    'data' => $data,
                    'mime' => (string) ($prediction['mimeType'] ?? $prediction['mime_type'] ?? 'image/png'),
                ];
            }
        }

        foreach ((array) ($json['generatedImages'] ?? []) as $generatedImage) {
            if (! is_array($generatedImage)) {
                continue;
            }

            $data = $generatedImage['image']['imageBytes']
                ?? $generatedImage['imageBytes']
                ?? $generatedImage['bytesBase64Encoded']
                ?? '';
            if (is_string($data) && $data !== '') {
                return [
                    'data' => $data,
                    'mime' => (string) ($generatedImage['image']['mimeType'] ?? $generatedImage['mimeType'] ?? 'image/png'),
                ];
            }
        }

        log_message('error', 'ACARA AI Imagen response tanpa gambar: {body}', [
            'body' => mb_substr(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '', 0, 1000),
        ]);
        throw new RuntimeException('ACARA AI tidak mengembalikan gambar.');
    }

    private function storeGeneratedImage(string $base64Data, string $mime, array $context): array
    {
        $binary = base64_decode($base64Data, true);
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Gambar ACARA AI tidak valid.');
        }

        $imageInfo = @getimagesizefromstring($binary);
        $mime = strtolower((string) ($imageInfo['mime'] ?? $mime ?: 'image/png'));
        $extensions = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/webp' => 'webp',
        ];
        if (! isset($extensions[$mime])) {
            throw new RuntimeException('Format gambar ACARA AI tidak didukung.');
        }

        $userId = max(0, (int) ($context['userId'] ?? 0));
        $pageId = max(0, (int) ($context['pageId'] ?? 0));
        $targetDir = FCPATH . 'uploads/editor-ai/' . $userId . '/' . $pageId;
        if (! is_dir($targetDir) && ! @mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            throw new RuntimeException('Folder hasil ACARA AI tidak bisa dibuat.');
        }
        if (! is_writable($targetDir)) {
            throw new RuntimeException('Folder hasil ACARA AI tidak bisa ditulis.');
        }

        $extension = $extensions[$mime];
        $fileName = 'acara-ai-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $absolutePath = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
        if (@file_put_contents($absolutePath, $binary, LOCK_EX) === false) {
            throw new RuntimeException('Gambar ACARA AI gagal disimpan.');
        }

        $relative = 'uploads/editor-ai/' . $userId . '/' . $pageId . '/' . $fileName;

        return [
            'src' => base_url($relative),
            'name' => $fileName,
            'mime' => $mime,
        ];
    }

    private function compactHistory(array $history): array
    {
        $items = [];
        foreach (array_slice($history, -8) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $text = trim(mb_substr((string) ($item['text'] ?? ''), 0, 500));
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
        foreach (array_slice((array) ($context['objects'] ?? []), 0, 45) as $object) {
            if (! is_array($object)) {
                continue;
            }
            $objects[] = [
                'type' => mb_substr((string) ($object['type'] ?? ''), 0, 40),
                'customType' => mb_substr((string) ($object['customType'] ?? ''), 0, 60),
                'text' => mb_substr((string) ($object['text'] ?? ''), 0, 180),
                'left' => (float) ($object['left'] ?? 0),
                'top' => (float) ($object['top'] ?? 0),
                'width' => (float) ($object['width'] ?? 0),
                'height' => (float) ($object['height'] ?? 0),
            ];
        }

        return [
            'available' => true,
            'title' => mb_substr((string) ($context['title'] ?? ''), 0, 120),
            'artboard' => $context['artboard'] ?? [],
            'backgroundColor' => mb_substr((string) ($context['backgroundColor'] ?? '#ffffff'), 0, 40),
            'objectCount' => (int) ($context['objectCount'] ?? count($objects)),
            'objects' => $objects,
        ];
    }

    private function safeJson(array $value, int $limit): string
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($json) || $json === '') {
            return '{}';
        }

        return mb_substr($json, 0, $limit);
    }
}
