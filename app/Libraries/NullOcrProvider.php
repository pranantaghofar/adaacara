<?php

namespace App\Libraries;

use RuntimeException;

class NullOcrProvider implements OcrProviderInterface
{
    public function detectText(string $absolutePath, array $context = []): array
    {
        throw new RuntimeException('Provider OCR belum dikonfigurasi. Set adapter server-side sebelum memakai Deteksi Teks AI.');
    }
}
