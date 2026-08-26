<?php

$root = dirname(__DIR__);
$baseDir = $root . '/public/assets/editor';
$libraryDir = $baseDir . '/library';
$categories = ['Floral', 'Islamic', 'Wedding', 'Birthday', 'Corporate', 'Abstract', 'Minimalist', 'Luxury'];
$categorySlugs = array_map('slugify', $categories);
$accentPalette = [
    ['#0f766e', '#14b8a6', '#ccfbf1'],
    ['#7c2d12', '#f97316', '#ffedd5'],
    ['#831843', '#db2777', '#fce7f3'],
    ['#1e3a8a', '#3b82f6', '#dbeafe'],
    ['#422006', '#d97706', '#fef3c7'],
    ['#312e81', '#8b5cf6', '#ede9fe'],
    ['#111827', '#64748b', '#f8fafc'],
    ['#713f12', '#d4af37', '#fff7ed'],
];

ensureDir($libraryDir);

$all = [];
$groups = [
    'ornament' => generateAssets('ornament', 500, $categories, $categorySlugs, $accentPalette, $baseDir),
    'shape' => generateAssets('shape', 200, $categories, $categorySlugs, $accentPalette, $baseDir),
    'background' => generateAssets('background', 100, $categories, $categorySlugs, $accentPalette, $baseDir),
    'pattern' => generateAssets('pattern', 100, $categories, $categorySlugs, $accentPalette, $baseDir),
];

foreach ($groups as $type => $items) {
    $all = array_merge($all, $items);
    file_put_contents(
        $libraryDir . '/' . $type . 's.json',
        json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

file_put_contents(
    $libraryDir . '/assets.json',
    json_encode([
        'version' => 1,
        'generated_at' => gmdate('c'),
        'categories' => $categories,
        'counts' => [
            'ornament' => count($groups['ornament']),
            'shape' => count($groups['shape']),
            'background' => count($groups['background']),
            'pattern' => count($groups['pattern']),
            'total' => count($all),
        ],
        'items' => $all,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);

echo 'Generated ' . count($all) . " editor assets.\n";

function generateAssets(string $type, int $count, array $categories, array $categorySlugs, array $palette, string $baseDir): array
{
    $items = [];
    for ($i = 1; $i <= $count; $i++) {
        $categoryIndex = ($i - 1) % count($categories);
        $category = $categories[$categoryIndex];
        $categorySlug = $categorySlugs[$categoryIndex];
        $variant = (int) floor(($i - 1) / count($categories));
        $style = $variant % 24;
        $colors = $palette[($categoryIndex + $variant) % count($palette)];
        $id = sprintf('%s-%s-%03d', $type, $categorySlug, $i);
        $relativeDir = 'assets/editor/' . $type . 's/' . $categorySlug;
        $targetDir = $baseDir . '/' . $type . 's/' . $categorySlug;
        ensureDir($targetDir);
        $file = $id . '.svg';
        $svg = match ($type) {
            'ornament' => ornamentSvg($categorySlug, $style, $variant, $colors),
            'shape' => shapeSvg($categorySlug, $style, $variant, $colors),
            'background' => backgroundSvg($categorySlug, $style, $variant, $colors),
            'pattern' => patternSvg($categorySlug, $style, $variant, $colors),
            default => '',
        };
        file_put_contents($targetDir . '/' . $file, $svg);
        $items[] = [
            'id' => $id,
            'type' => $type,
            'category' => $category,
            'name' => titleFromId($id),
            'src' => '/' . $relativeDir . '/' . $file,
            'tags' => tagsFor($type, $categorySlug, $style),
            'premium' => $type !== 'shape' && (($i + $variant) % 7 === 0),
        ];
    }

    return $items;
}

function ornamentSvg(string $category, int $style, int $variant, array $colors): string
{
    [$primary, $secondary, $soft] = $colors;
    $stroke = 4 + ($variant % 4);
    $petals = 5 + ($style % 6);
    $corner = 26 + (($variant * 7) % 34);
    $motif = match ($category) {
        'floral' => flowerMotif($primary, $secondary, $soft, $petals, $stroke),
        'islamic' => islamicMotif($primary, $secondary, $soft, $style, $stroke),
        'wedding' => weddingMotif($primary, $secondary, $soft, $style, $stroke),
        'birthday' => birthdayMotif($primary, $secondary, $soft, $style, $stroke),
        'corporate' => corporateMotif($primary, $secondary, $soft, $style, $stroke),
        'abstract' => abstractMotif($primary, $secondary, $soft, $style, $stroke),
        'minimalist' => minimalistMotif($primary, $secondary, $soft, $style, $stroke),
        'luxury' => luxuryMotif($primary, $secondary, $soft, $style, $stroke),
        default => abstractMotif($primary, $secondary, $soft, $style, $stroke),
    };

    return svgWrap(512, 512, <<<SVG
<path d="M{$corner} 256 C120 80 392 80 486 256 C392 432 120 432 {$corner} 256Z" fill="none" stroke="{$soft}" stroke-width="{$stroke}" opacity=".55"/>
<g transform="translate(256 256)">{$motif}</g>
SVG);
}

function shapeSvg(string $category, int $style, int $variant, array $colors): string
{
    [$primary, $secondary, $soft] = $colors;
    $stroke = 3 + ($variant % 8);
    $rotation = ($style * 11) % 180;
    $body = match ($style % 10) {
        0 => '<rect x="76" y="116" width="360" height="280" rx="' . (20 + $variant % 70) . '" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/>',
        1 => '<circle cx="256" cy="256" r="' . (116 + $variant % 70) . '" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/>',
        2 => '<polygon points="256,72 438,376 74,376" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/>',
        3 => '<path d="M104 256 C104 126 408 126 408 256 S104 386 104 256Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/>',
        4 => '<path d="M92 132 H420 L360 380 H152Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/>',
        5 => '<path d="M256 68 L308 202 L452 202 L336 288 L382 430 L256 348 L130 430 L176 288 L60 202 L204 202Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/>',
        6 => '<path d="M80 312 C132 140 372 72 430 224 C488 374 216 444 80 312Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/>',
        7 => '<line x1="72" y1="256" x2="440" y2="256" stroke="' . $primary . '" stroke-width="' . (8 + $variant % 18) . '" stroke-linecap="round"/><circle cx="256" cy="256" r="18" fill="' . $secondary . '"/>',
        8 => '<path d="M96 96 H416 V416 H96Z M156 156 H356 V356 H156Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '" fill-rule="evenodd"/>',
        default => '<path d="M256 72 C366 72 440 146 440 256 C440 366 366 440 256 440 C146 440 72 366 72 256 C72 146 146 72 256 72Z M256 140 C190 140 140 190 140 256 C140 322 190 372 256 372 C322 372 372 322 372 256 C372 190 322 140 256 140Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '" fill-rule="evenodd"/>',
    };

    return svgWrap(512, 512, '<g transform="rotate(' . $rotation . ' 256 256)">' . $body . '<path d="M128 424 C210 390 302 390 384 424" fill="none" stroke="' . $secondary . '" stroke-width="4" opacity=".45"/></g>');
}

function backgroundSvg(string $category, int $style, int $variant, array $colors): string
{
    [$primary, $secondary, $soft] = $colors;
    $opacity = .10 + (($variant % 6) * .035);
    $motif = ornamentSvg($category, $style, $variant, [$primary, $secondary, $soft]);
    preg_match('/<svg[^>]*>(.*)<\/svg>/s', $motif, $match);
    $inner = $match[1] ?? '';

    return svgWrap(1080, 1920, <<<SVG
<rect width="1080" height="1920" fill="#ffffff"/>
<rect width="1080" height="1920" fill="{$soft}" opacity=".42"/>
<circle cx="170" cy="250" r="260" fill="{$secondary}" opacity="{$opacity}"/>
<circle cx="930" cy="1610" r="320" fill="{$primary}" opacity="{$opacity}"/>
<g transform="translate(284 160) scale(1.0)" opacity=".58">{$inner}</g>
<g transform="translate(796 1760) rotate(180) scale(.72)" opacity=".42">{$inner}</g>
<path d="M0 1460 C260 1320 520 1580 1080 1400 V1920 H0Z" fill="{$primary}" opacity=".08"/>
SVG);
}

function patternSvg(string $category, int $style, int $variant, array $colors): string
{
    [$primary, $secondary, $soft] = $colors;
    $gap = 86 + (($variant % 5) * 18);
    $halfGap = $gap / 2;
    $dot = 6 + ($style % 8);
    $motif = match ($style % 6) {
        0 => '<circle cx="0" cy="0" r="' . $dot . '" fill="' . $primary . '"/><circle cx="' . ($gap / 2) . '" cy="' . ($gap / 2) . '" r="' . max(3, $dot - 2) . '" fill="' . $secondary . '"/>',
        1 => '<path d="M0 -18 L18 0 L0 18 L-18 0Z" fill="none" stroke="' . $primary . '" stroke-width="4"/><circle cx="' . ($gap / 2) . '" cy="' . ($gap / 2) . '" r="8" fill="' . $secondary . '"/>',
        2 => '<path d="M-24 0 C-8 -18 8 -18 24 0 C8 18 -8 18 -24 0Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="3"/>',
        3 => '<path d="M-22 -22 H22 V22 H-22Z" fill="none" stroke="' . $primary . '" stroke-width="3"/><path d="M-22 22 L22 -22" stroke="' . $secondary . '" stroke-width="3"/>',
        4 => '<path d="M0 -26 C26 -26 26 26 0 26 C-26 26 -26 -26 0 -26Z" fill="none" stroke="' . $primary . '" stroke-width="3"/><path d="M-26 0 H26" stroke="' . $secondary . '" stroke-width="3"/>',
        default => '<path d="M-30 0 Q0 -30 30 0 Q0 30 -30 0Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="3"/>',
    };

    return svgWrap(512, 512, <<<SVG
<rect width="512" height="512" fill="#ffffff"/>
<defs><pattern id="p" width="{$gap}" height="{$gap}" patternUnits="userSpaceOnUse"><g transform="translate({$halfGap} {$halfGap})">{$motif}</g></pattern></defs>
<rect width="512" height="512" fill="url(#p)" opacity=".82"/>
SVG);
}

function flowerMotif(string $primary, string $secondary, string $soft, int $petals, int $stroke): string
{
    $parts = '';
    for ($i = 0; $i < $petals; $i++) {
        $angle = round(360 / $petals * $i, 2);
        $parts .= '<ellipse cx="0" cy="-62" rx="24" ry="58" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '" transform="rotate(' . $angle . ')"/>';
    }
    return $parts . '<circle r="36" fill="' . $secondary . '" opacity=".9"/><path d="M-142 80 C-80 38 -32 38 0 92 C32 38 80 38 142 80" fill="none" stroke="' . $primary . '" stroke-width="' . ($stroke + 1) . '" stroke-linecap="round"/>';
}

function islamicMotif(string $primary, string $secondary, string $soft, int $style, int $stroke): string
{
    $points = [];
    $r1 = 112;
    $r2 = 54 + ($style % 18);
    for ($i = 0; $i < 16; $i++) {
        $r = $i % 2 === 0 ? $r1 : $r2;
        $a = deg2rad(($i * 22.5) - 90);
        $points[] = round(cos($a) * $r, 2) . ',' . round(sin($a) * $r, 2);
    }
    return '<path d="M-138 132 V-10 C-138 -112 138 -112 138 -10 V132" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/><polygon points="' . implode(' ', $points) . '" fill="none" stroke="' . $secondary . '" stroke-width="' . ($stroke + 1) . '"/><circle r="20" fill="' . $primary . '"/>';
}

function weddingMotif(string $primary, string $secondary, string $soft, int $style, int $stroke): string
{
    return '<circle cx="-42" cy="0" r="78" fill="none" stroke="' . $primary . '" stroke-width="' . ($stroke + 7) . '"/><circle cx="42" cy="0" r="78" fill="none" stroke="' . $secondary . '" stroke-width="' . ($stroke + 7) . '"/><path d="M-150 -110 C-50 -170 50 -170 150 -110 M-150 110 C-50 170 50 170 150 110" fill="none" stroke="' . $primary . '" stroke-width="' . $stroke . '" stroke-linecap="round"/><path d="M0 -138 L28 -94 L-28 -94Z" fill="' . $soft . '" stroke="' . $secondary . '" stroke-width="' . $stroke . '"/>';
}

function birthdayMotif(string $primary, string $secondary, string $soft, int $style, int $stroke): string
{
    return '<ellipse cx="-72" cy="-30" rx="44" ry="62" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/><ellipse cx="58" cy="-50" rx="52" ry="72" fill="' . $secondary . '" opacity=".82" stroke="' . $primary . '" stroke-width="' . $stroke . '"/><path d="M-72 32 C-96 92 -46 120 -70 164 M58 22 C34 92 90 120 58 164" fill="none" stroke="' . $primary . '" stroke-width="' . $stroke . '"/><path d="M-142 -142 L-108 -108 M120 -132 L152 -98 M-8 -162 V-116" stroke="' . $secondary . '" stroke-width="' . ($stroke + 2) . '" stroke-linecap="round"/>';
}

function corporateMotif(string $primary, string $secondary, string $soft, int $style, int $stroke): string
{
    return '<rect x="-132" y="-110" width="264" height="220" rx="26" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/><path d="M-80 -42 H80 M-80 10 H80 M-80 62 H28" stroke="' . $primary . '" stroke-width="' . ($stroke + 4) . '" stroke-linecap="round"/><rect x="54" y="36" width="58" height="58" rx="12" fill="' . $secondary . '"/>';
}

function abstractMotif(string $primary, string $secondary, string $soft, int $style, int $stroke): string
{
    return '<path d="M-144 34 C-120 -112 46 -156 136 -48 C222 56 48 178 -86 126 C-138 106 -156 74 -144 34Z" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . $stroke . '"/><path d="M-98 -34 C-10 -92 74 -74 126 18" fill="none" stroke="' . $secondary . '" stroke-width="' . ($stroke + 4) . '" stroke-linecap="round"/><circle cx="-60" cy="70" r="20" fill="' . $secondary . '"/>';
}

function minimalistMotif(string $primary, string $secondary, string $soft, int $style, int $stroke): string
{
    return '<path d="M-168 0 H168" stroke="' . $primary . '" stroke-width="' . $stroke . '" stroke-linecap="round"/><circle cx="-72" cy="0" r="12" fill="' . $secondary . '"/><circle cx="0" cy="0" r="18" fill="' . $soft . '" stroke="' . $primary . '" stroke-width="' . max(2, $stroke - 1) . '"/><circle cx="72" cy="0" r="12" fill="' . $secondary . '"/><path d="M-48 -42 H48 M-48 42 H48" stroke="' . $primary . '" stroke-width="' . max(2, $stroke - 2) . '" opacity=".55"/>';
}

function luxuryMotif(string $primary, string $secondary, string $soft, int $style, int $stroke): string
{
    return '<path d="M0 -150 C52 -82 126 -72 160 0 C126 72 52 82 0 150 C-52 82 -126 72 -160 0 C-126 -72 -52 -82 0 -150Z" fill="' . $soft . '" stroke="' . $secondary . '" stroke-width="' . ($stroke + 1) . '"/><path d="M-104 0 C-56 -48 56 -48 104 0 C56 48 -56 48 -104 0Z" fill="none" stroke="' . $primary . '" stroke-width="' . $stroke . '"/><circle r="22" fill="' . $secondary . '"/>';
}

function svgWrap(int $width, int $height, string $content): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">' . $content . '</svg>' . "\n";
}

function tagsFor(string $type, string $category, int $style): array
{
    $base = [$type, $category];
    $styleTags = ['frame', 'corner', 'divider', 'badge', 'line', 'soft', 'bold', 'classic', 'modern', 'decorative'];
    $base[] = $styleTags[$style % count($styleTags)];
    return array_values(array_unique($base));
}

function titleFromId(string $id): string
{
    return ucwords(str_replace('-', ' ', preg_replace('/-\d+$/', '', $id)));
}

function slugify(string $value): string
{
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));
}

function ensureDir(string $dir): void
{
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}
