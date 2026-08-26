<?php

namespace App\Libraries;

use InvalidArgumentException;

class OcrTextDetectionService
{
    private const MAX_BLOCKS = 80;
    private const MAX_FRAMES = 20;
    private const MAX_DECORATIONS = 12;
    private const MAX_SHAPES = 40;
    private const MAX_SECTIONS = 8;
    private const MAX_GROUPS = 24;
    private OcrProviderInterface $provider;

    public function __construct(?OcrProviderInterface $provider = null)
    {
        $this->provider = $provider ?? new NullOcrProvider();
    }

    public function detect(string $absolutePath, array $context = []): array
    {
        $raw = $this->provider->detectText($absolutePath, $context);

        return $this->normalizeBlueprint($raw);
    }

    public function normalizeGeneratedBlueprint(array $raw): array
    {
        return $this->normalizeBlueprint($raw);
    }

    private function normalizeBlueprint(array $raw): array
    {
        $imageWidth = $this->positiveInt($raw['imageWidth'] ?? 0, 'imageWidth');
        $imageHeight = $this->positiveInt($raw['imageHeight'] ?? 0, 'imageHeight');
        $blocks = [];

        foreach (array_slice((array) ($raw['blocks'] ?? []), 0, self::MAX_BLOCKS) as $block) {
            if (! is_array($block)) {
                continue;
            }

            $text = trim(mb_substr((string) ($block['text'] ?? ''), 0, 500));
            if ($text === '') {
                continue;
            }

            $x = $this->number($block['x'] ?? null);
            $y = $this->number($block['y'] ?? null);
            $width = $this->number($block['width'] ?? null);
            $height = $this->number($block['height'] ?? null);
            if ($width <= 0 || $height <= 0 || $x < 0 || $y < 0 || $x > $imageWidth || $y > $imageHeight) {
                continue;
            }

            $blocks[] = [
                'text' => $text,
                'confidence' => max(0, min(1, $this->number($block['confidence'] ?? 0))),
                'x' => max(0, min($imageWidth, $x)),
                'y' => max(0, min($imageHeight, $y)),
                'width' => max(1, min($imageWidth, $width)),
                'height' => max(1, min($imageHeight, $height)),
                'fontSize' => max(0, min(520, $this->number($block['fontSize'] ?? 0))),
                'angle' => max(-45, min(45, $this->number($block['angle'] ?? 0))),
                'color' => $this->color($block['color'] ?? '#111827'),
                'align' => in_array(($block['align'] ?? ''), ['left', 'center', 'right'], true) ? $block['align'] : 'center',
                'styleHint' => preg_replace('/[^a-z0-9_-]/i', '', (string) ($block['styleHint'] ?? '')),
                'weightHint' => $this->fontWeight($block['weightHint'] ?? 400),
                'role' => $this->enum((string) ($block['role'] ?? 'other'), ['heading', 'subheading', 'body', 'caption', 'button', 'date', 'name', 'location', 'other'], 'other'),
                'sectionId' => $this->identifier($block['sectionId'] ?? ''),
                'groupId' => $this->identifier($block['groupId'] ?? ''),
                'hierarchyLevel' => max(1, min(5, (int) ($block['hierarchyLevel'] ?? 3))),
                'spacingHint' => $this->enum((string) ($block['spacingHint'] ?? 'normal'), ['tight', 'normal', 'airy'], 'normal'),
                'italic' => ($block['italic'] ?? false) === true,
                'backgroundColor' => $this->optionalColor($block['backgroundColor'] ?? ''),
                'coverOpacity' => max(0, min(1, $this->number($block['coverOpacity'] ?? 0))),
            ];
        }

        $sections = [];
        foreach (array_slice((array) ($raw['sections'] ?? []), 0, self::MAX_SECTIONS) as $section) {
            if (! is_array($section)) {
                continue;
            }

            $id = $this->identifier($section['id'] ?? '');
            $x = $this->number($section['x'] ?? null);
            $y = $this->number($section['y'] ?? null);
            $width = $this->number($section['width'] ?? null);
            $height = $this->number($section['height'] ?? null);
            if ($id === '' || $width <= 0 || $height <= 0 || $x < 0 || $y < 0 || $x > $imageWidth || $y > $imageHeight) {
                continue;
            }

            $sections[] = [
                'id' => $id,
                'kind' => $this->enum((string) ($section['kind'] ?? 'other'), ['hero', 'details', 'location', 'rsvp', 'footer', 'other'], 'other'),
                'confidence' => max(0, min(1, $this->number($section['confidence'] ?? 0))),
                'x' => max(0, min($imageWidth, $x)),
                'y' => max(0, min($imageHeight, $y)),
                'width' => max(1, min($imageWidth, $width)),
                'height' => max(1, min($imageHeight, $height)),
            ];
        }

        $groups = [];
        foreach (array_slice((array) ($raw['groups'] ?? []), 0, self::MAX_GROUPS) as $group) {
            if (! is_array($group)) {
                continue;
            }

            $id = $this->identifier($group['id'] ?? '');
            $x = $this->number($group['x'] ?? null);
            $y = $this->number($group['y'] ?? null);
            $width = $this->number($group['width'] ?? null);
            $height = $this->number($group['height'] ?? null);
            if ($id === '' || $width <= 0 || $height <= 0 || $x < 0 || $y < 0 || $x > $imageWidth || $y > $imageHeight) {
                continue;
            }

            $groups[] = [
                'id' => $id,
                'kind' => $this->enum((string) ($group['kind'] ?? 'other'), ['title_group', 'text_group', 'date_group', 'address_group', 'media_group', 'ornament_group', 'cta_group', 'other'], 'other'),
                'confidence' => max(0, min(1, $this->number($group['confidence'] ?? 0))),
                'x' => max(0, min($imageWidth, $x)),
                'y' => max(0, min($imageHeight, $y)),
                'width' => max(1, min($imageWidth, $width)),
                'height' => max(1, min($imageHeight, $height)),
            ];
        }

        $frames = [];
        foreach (array_slice((array) ($raw['frames'] ?? []), 0, self::MAX_FRAMES) as $frame) {
            if (! is_array($frame)) {
                continue;
            }

            $x = $this->number($frame['x'] ?? null);
            $y = $this->number($frame['y'] ?? null);
            $width = $this->number($frame['width'] ?? null);
            $height = $this->number($frame['height'] ?? null);
            if ($width <= 0 || $height <= 0 || $x < 0 || $y < 0 || $x > $imageWidth || $y > $imageHeight) {
                continue;
            }

            $shape = (string) ($frame['shape'] ?? 'rect');
            if (! in_array($shape, ['rect', 'rounded-rect', 'circle', 'arch'], true)) {
                $shape = 'rect';
            }

            $frames[] = [
                'confidence' => max(0, min(1, $this->number($frame['confidence'] ?? 0))),
                'x' => max(0, min($imageWidth, $x)),
                'y' => max(0, min($imageHeight, $y)),
                'width' => max(1, min($imageWidth, $width)),
                'height' => max(1, min($imageHeight, $height)),
                'angle' => max(-45, min(45, $this->number($frame['angle'] ?? 0))),
                'shape' => $shape,
                'needsReview' => ($frame['needsReview'] ?? false) === true,
            ];
        }

        $decorations = [];
        foreach (array_slice((array) ($raw['decorations'] ?? []), 0, self::MAX_DECORATIONS) as $decoration) {
            if (! is_array($decoration)) {
                continue;
            }

            $x = $this->number($decoration['x'] ?? null);
            $y = $this->number($decoration['y'] ?? null);
            $width = $this->number($decoration['width'] ?? null);
            $height = $this->number($decoration['height'] ?? null);
            if ($width <= 8 || $height <= 8 || $x < 0 || $y < 0 || $x > $imageWidth || $y > $imageHeight) {
                continue;
            }

            $kind = (string) ($decoration['kind'] ?? 'other');
            if (! in_array($kind, ['flower', 'foliage', 'ornament', 'frame', 'divider', 'illustration', 'logo', 'other'], true)) {
                $kind = 'other';
            }

            $decorations[] = [
                'kind' => $kind,
                'confidence' => max(0, min(1, $this->number($decoration['confidence'] ?? 0))),
                'x' => max(0, min($imageWidth, $x)),
                'y' => max(0, min($imageHeight, $y)),
                'width' => max(1, min($imageWidth, $width)),
                'height' => max(1, min($imageHeight, $height)),
                'angle' => max(-45, min(45, $this->number($decoration['angle'] ?? 0))),
                'needsReview' => ($decoration['needsReview'] ?? false) === true,
                'needsBackgroundRemoval' => ($decoration['needsBackgroundRemoval'] ?? false) === true,
            ];
        }

        $shapes = [];
        foreach (array_slice((array) ($raw['shapes'] ?? []), 0, self::MAX_SHAPES) as $shape) {
            if (! is_array($shape)) {
                continue;
            }

            $x = $this->number($shape['x'] ?? null);
            $y = $this->number($shape['y'] ?? null);
            $width = $this->number($shape['width'] ?? null);
            $height = $this->number($shape['height'] ?? null);
            if ($width <= 0 || $height <= 0 || $x < 0 || $y < 0 || $x > $imageWidth || $y > $imageHeight) {
                continue;
            }

            $kind = (string) ($shape['kind'] ?? 'rect');
            if (! in_array($kind, ['rect', 'rounded-rect', 'circle', 'oval', 'polygon', 'line', 'divider'], true)) {
                $kind = 'rect';
            }

            $points = [];
            foreach ((array) ($shape['points'] ?? []) as $point) {
                if (! is_array($point)) {
                    continue;
                }
                $points[] = [
                    'x' => max(0, min($imageWidth, $this->number($point['x'] ?? 0))),
                    'y' => max(0, min($imageHeight, $this->number($point['y'] ?? 0))),
                ];
            }

            $shapes[] = [
                'kind' => $kind,
                'confidence' => max(0, min(1, $this->number($shape['confidence'] ?? 0.9))),
                'x' => max(0, min($imageWidth, $x)),
                'y' => max(0, min($imageHeight, $y)),
                'width' => max(1, min($imageWidth, $width)),
                'height' => max(1, min($imageHeight, $height)),
                'angle' => max(-45, min(45, $this->number($shape['angle'] ?? 0))),
                'fill' => $this->optionalColor($shape['fill'] ?? ''),
                'stroke' => $this->optionalColor($shape['stroke'] ?? ''),
                'strokeWidth' => max(0, min(80, $this->number($shape['strokeWidth'] ?? 0))),
                'opacity' => max(0, min(1, $this->number($shape['opacity'] ?? 1))),
                'points' => $points,
                'needsReview' => ($shape['needsReview'] ?? false) === true,
            ];
        }

        $canvasOverlay = null;
        if (is_array($raw['canvasOverlay'] ?? null) && ($raw['canvasOverlay']['enabled'] ?? false) === true) {
            $overlay = $raw['canvasOverlay'];
            $x = $this->number($overlay['x'] ?? 0);
            $y = $this->number($overlay['y'] ?? 0);
            $width = $this->number($overlay['width'] ?? $imageWidth);
            $height = $this->number($overlay['height'] ?? $imageHeight);
            $assetSrc = trim((string) ($overlay['assetSrc'] ?? ''));
            if ($assetSrc !== '' && $width > 0 && $height > 0 && $x >= 0 && $y >= 0 && $x <= $imageWidth && $y <= $imageHeight) {
                $canvasOverlay = [
                    'enabled' => true,
                    'confidence' => max(0, min(1, $this->number($overlay['confidence'] ?? 0.9))),
                    'x' => max(0, min($imageWidth, $x)),
                    'y' => max(0, min($imageHeight, $y)),
                    'width' => max(1, min($imageWidth, $width)),
                    'height' => max(1, min($imageHeight, $height)),
                    'assetSrc' => mb_substr($assetSrc, 0, 1000),
                    'assetName' => mb_substr((string) ($overlay['assetName'] ?? 'adaacara-ai-overlay.png'), 0, 160),
                    'needsReview' => ($overlay['needsReview'] ?? false) === true,
                ];
            }
        }

        return [
            'imageWidth' => $imageWidth,
            'imageHeight' => $imageHeight,
            'backgroundColor' => $this->optionalColor($raw['backgroundColor'] ?? ''),
            'blocks' => $blocks,
            'sections' => $sections,
            'groups' => $groups,
            'frames' => $frames,
            'decorations' => $decorations,
            'shapes' => $shapes,
            'style' => $this->styleSummary($raw['style'] ?? []),
            'canvasOverlay' => $canvasOverlay,
        ];
    }

    private function positiveInt(mixed $value, string $field): int
    {
        $number = (int) $value;
        if ($number <= 0 || $number > 12000) {
            throw new InvalidArgumentException('Blueprint OCR tidak valid: ' . $field);
        }

        return $number;
    }

    private function number(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function color(mixed $value): string
    {
        $color = strtoupper((string) $value);

        return preg_match('/^#[0-9A-F]{6}$/', $color) ? $color : '#111827';
    }

    private function optionalColor(mixed $value): string
    {
        $color = strtoupper((string) $value);

        return preg_match('/^#[0-9A-F]{6}$/', $color) ? $color : '';
    }

    private function identifier(mixed $value): string
    {
        $id = preg_replace('/[^a-z0-9_-]/i', '', (string) $value) ?? '';

        return mb_substr($id, 0, 48);
    }

    private function enum(string $value, array $allowed, string $fallback): string
    {
        $value = strtolower(trim($value));

        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private function styleSummary(mixed $value): array
    {
        if (! is_array($value)) {
            return [
                'tone' => '',
                'palette' => [],
                'alignment' => 'mixed',
                'spacing' => 'normal',
            ];
        }

        $palette = [];
        foreach (array_slice((array) ($value['palette'] ?? []), 0, 8) as $color) {
            $safeColor = $this->optionalColor($color);
            if ($safeColor !== '') {
                $palette[] = $safeColor;
            }
        }

        return [
            'tone' => mb_substr(preg_replace('/[^a-z0-9 _-]/i', '', (string) ($value['tone'] ?? '')) ?? '', 0, 80),
            'palette' => array_values(array_unique($palette)),
            'alignment' => $this->enum((string) ($value['alignment'] ?? 'mixed'), ['left', 'center', 'right', 'mixed'], 'mixed'),
            'spacing' => $this->enum((string) ($value['spacing'] ?? 'normal'), ['tight', 'normal', 'airy'], 'normal'),
        ];
    }

    private function fontWeight(mixed $value): int
    {
        $weight = (int) $value;

        return in_array($weight, [100, 200, 300, 400, 500, 600, 700, 800, 900], true) ? $weight : 400;
    }
}
