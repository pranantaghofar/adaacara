<?php

namespace App\Libraries;

use App\Models\CustomFontModel;

class CustomFontService
{
    private CustomFontModel $fontModel;

    public function __construct(?CustomFontModel $fontModel = null)
    {
        $this->fontModel = $fontModel ?? new CustomFontModel();
    }

    public function activeFonts(): array
    {
        try {
            return $this->fontModel->activeFonts();
        } catch (\Throwable) {
            return [];
        }
    }

    public function fontOptions(): array
    {
        $families = [];

        foreach ($this->activeFonts() as $font) {
            $family = $this->cleanFamily((string) ($font['font_family'] ?? ''));
            if ($family === '') {
                continue;
            }

            $weight = $this->cleanWeight((string) ($font['font_weight'] ?? '400'));
            if (! isset($families[$family])) {
                $families[$family] = [
                    'family' => $family,
                    'weights' => [],
                ];
            }

            if (! in_array($weight, $families[$family]['weights'], true)) {
                $families[$family]['weights'][] = $weight;
            }
        }

        foreach ($families as &$font) {
            sort($font['weights'], SORT_NUMERIC);
        }
        unset($font);

        return array_values($families);
    }

    public function fontCss(): string
    {
        $rules = [];

        foreach ($this->activeFonts() as $font) {
            $family = $this->cleanFamily((string) ($font['font_family'] ?? ''));
            $path = $this->cleanPath((string) ($font['file_path'] ?? ''));
            if ($family === '' || $path === '') {
                continue;
            }

            $url = site_url($path);
            $rules[] = '@font-face{' .
                'font-family:"' . $this->escapeCssString($family) . '";' .
                'src:url("' . $this->escapeCssUrl($url) . '") format("' . $this->fontFormat($path) . '");' .
                'font-weight:' . $this->cleanWeight((string) ($font['font_weight'] ?? '400')) . ';' .
                'font-style:' . $this->cleanStyle((string) ($font['font_style'] ?? 'normal')) . ';' .
                'font-display:swap;' .
                '}';
        }

        return $rules ? implode("\n", $rules) . "\n" : "/* AdaAcara custom fonts: empty */\n";
    }

    private function cleanFamily(string $family): string
    {
        $family = trim(strip_tags($family));
        $family = preg_replace('/\s+/', ' ', $family) ?? '';

        return substr($family, 0, 120);
    }

    private function cleanPath(string $path): string
    {
        $path = ltrim(trim($path), '/');
        if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'uploads/fonts/')) {
            return '';
        }

        return $path;
    }

    private function cleanWeight(string $weight): string
    {
        $weight = preg_replace('/[^0-9]/', '', $weight) ?? '400';
        $weight = (int) ($weight ?: 400);
        if ($weight < 100 || $weight > 900) {
            return '400';
        }

        return (string) (round($weight / 100) * 100);
    }

    private function cleanStyle(string $style): string
    {
        return strtolower(trim($style)) === 'italic' ? 'italic' : 'normal';
    }

    private function fontFormat(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'woff2' => 'woff2',
            'woff' => 'woff',
            'otf' => 'opentype',
            default => 'truetype',
        };
    }

    private function escapeCssString(string $value): string
    {
        return str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\"', '', ''], $value);
    }

    private function escapeCssUrl(string $value): string
    {
        return str_replace(['\\', '"', "\n", "\r"], ['\\\\', '%22', '', ''], $value);
    }
}
