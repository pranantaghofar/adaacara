<?php

namespace App\Libraries;

interface OcrProviderInterface
{
    public function detectText(string $absolutePath, array $context = []): array;
}
