<?php
    if (! function_exists('aa_normalize_editor_html')) {
        function aa_normalize_editor_html(string $html): string
        {
            $source = trim($html);

            if ($source === '' || ! preg_match('/<(html|body)\b/i', $source) || ! class_exists(DOMDocument::class)) {
                return $html;
            }

            $previous = libxml_use_internal_errors(true);
            $document = new DOMDocument();
            $loaded = $document->loadHTML('<?xml encoding="UTF-8">' . $source, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            if (! $loaded) {
                return $html;
            }

            // Optimasi Performa: Auto Lazy-Load & Async Decode untuk semua gambar
            $images = $document->getElementsByTagName('img');
            foreach ($images as $img) {
                if (! $img->hasAttribute('loading')) {
                    $img->setAttribute('loading', 'lazy');
                    $img->setAttribute('decoding', 'async');
                }
            }

            $body = $document->getElementsByTagName('body')->item(0);
            if (! $body) {
                return $html;
            }

            $inner = '';
            foreach ($body->childNodes as $child) {
                $inner .= $document->saveHTML($child);
            }

            return trim($inner) !== '' ? $inner : $html;
        }
    }

    if (! function_exists('aa_get_fabric_payload')) {
        function aa_get_fabric_payload(string $json): ?array
        {
            $data = json_decode($json, true);

            if (! is_array($data) || ($data['renderer'] ?? '') !== 'fabric') {
                return null;
            }

            return aa_sanitize_fabric_payload($data);
        }
    }

    if (! function_exists('aa_sanitize_fabric_payload')) {
        function aa_sanitize_fabric_payload(mixed $value): mixed
        {
            if (! is_array($value)) {
                return $value;
            }

            if (in_array(($value['type'] ?? null), ['i-text', 'textbox', 'text'], true)) {
                unset($value['clipPath']);
            }

            foreach ($value as $key => $item) {
                if ($key === 'textBaseline' && $item === 'alphabetical') {
                    $value[$key] = 'alphabetic';
                    continue;
                }

                $value[$key] = aa_sanitize_fabric_payload($item);
            }

            return $value;
        }
    }

    if (! function_exists('aa_fabric_pages')) {
        function aa_fabric_pages(array $data): array
        {
            if (! empty($data['pages']) && is_array($data['pages'])) {
                return array_values($data['pages']);
            }

            if (! empty($data['objects']) && is_array($data['objects'])) {
                return [$data];
            }

            return [];
        }
    }

    if (! function_exists('aa_guestbook_config')) {
        function aa_guestbook_config(?array $fabricPayload): array
        {
            $defaults = [
                'enabled' => true,
                'eyebrow' => 'Guestbook',
                'title' => 'Ucapan dan Doa',
                'subtitle' => 'Tinggalkan ucapan dan konfirmasi kehadiran kamu untuk acara ini.',
                'buttonText' => 'Kirim ucapan',
                'backgroundColor' => '#f8fafc',
                'cardColor' => '#ffffff',
                'textColor' => '#101828',
                'mutedColor' => '#667085',
                'accentColor' => '#0f766e',
                'borderRadius' => 22,
                'maxHeight' => 380,
                'showSticker' => true,
                'showAttendance' => true,
            ];

            $source = is_array($fabricPayload['guestbook'] ?? null) ? $fabricPayload['guestbook'] : [];
            $hasConfig = $source !== [];

            $color = static function (mixed $value, string $fallback): string {
                $value = (string) ($value ?? '');
                return preg_match('/^#[0-9a-f]{6}$/i', $value) ? $value : $fallback;
            };

            return [
                'hasConfig' => $hasConfig,
                'enabled' => $hasConfig ? (($source['enabled'] ?? false) === true) : true,
                'eyebrow' => mb_substr(strip_tags((string) ($source['eyebrow'] ?? $defaults['eyebrow'])), 0, 40),
                'title' => mb_substr(strip_tags((string) ($source['title'] ?? $defaults['title'])), 0, 80),
                'subtitle' => mb_substr(strip_tags((string) ($source['subtitle'] ?? $defaults['subtitle'])), 0, 180),
                'buttonText' => mb_substr(strip_tags((string) ($source['buttonText'] ?? $defaults['buttonText'])), 0, 40),
                'backgroundColor' => $color($source['backgroundColor'] ?? null, $defaults['backgroundColor']),
                'cardColor' => $color($source['cardColor'] ?? null, $defaults['cardColor']),
                'textColor' => $color($source['textColor'] ?? null, $defaults['textColor']),
                'mutedColor' => $color($source['mutedColor'] ?? null, $defaults['mutedColor']),
                'accentColor' => $color($source['accentColor'] ?? null, $defaults['accentColor']),
                'borderRadius' => max(0, min(40, (int) ($source['borderRadius'] ?? $defaults['borderRadius']))),
                'maxHeight' => max(180, min(720, (int) ($source['maxHeight'] ?? $defaults['maxHeight']))),
                'showSticker' => ($source['showSticker'] ?? true) !== false,
                'showAttendance' => ($source['showAttendance'] ?? true) !== false,
            ];
        }
    }

    if (! function_exists('aa_opening_slug')) {
        function aa_opening_slug(string $value): string
        {
            $value = strtolower(trim($value));
            $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?: '';
            return trim($value, '-') ?: 'default';
        }
    }

    if (! function_exists('aa_opening_config')) {
        function aa_opening_config(?array $fabricPayload, array $page = []): array
        {
            $opening = is_array($fabricPayload['opening'] ?? null) ? $fabricPayload['opening'] : [];
            $category = (string) (
                $opening['category']
                ?? $page['category_slug']
                ?? $page['category']
                ?? $page['template_category_slug']
                ?? $page['template_category']
                ?? 'default'
            );
            $exitAnimation = aa_opening_slug((string) ($opening['exitAnimation'] ?? 'fade'));
            $allowedExitAnimations = ['fade', 'slide-up', 'zoom-out', 'blur-fade', 'curtain', 'elegant-lift'];
            if (! in_array($exitAnimation, $allowedExitAnimations, true)) {
                $exitAnimation = 'fade';
            }

            return [
                'enabled' => ($opening['enabled'] ?? true) !== false,
                'mode' => (string) ($opening['mode'] ?? 'default'),
                'category' => aa_opening_slug($category),
                'exitAnimation' => $exitAnimation,
            ];
        }
    }

    if (! function_exists('aa_fabric_css_color_value')) {
        function aa_fabric_css_color_value($value): string
        {
            $value = trim((string) $value);
            if ($value === '') {
                return '#ffffff';
            }

            if (preg_match('/^#[0-9a-f]{3,8}$/i', $value)) {
                return $value;
            }

            if (preg_match('/^(rgba?|hsla?)\([0-9.,%\s-]+\)$/i', $value)) {
                return $value;
            }

            return '#ffffff';
        }
    }

    if (! function_exists('aa_fabric_loader_text_color')) {
        function aa_fabric_loader_text_color(string $color): string
        {
            if (! preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color, $matches)) {
                return '#0f172a';
            }

            $hex = $matches[1];
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }

            $red = hexdec(substr($hex, 0, 2));
            $green = hexdec(substr($hex, 2, 2));
            $blue = hexdec(substr($hex, 4, 2));
            $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

            return $luminance < 145 ? '#ffffff' : '#0f172a';
        }
    }

    if (! function_exists('aa_fabric_fallback_html')) {
        function aa_fabric_fallback_html(array $data): string
        {
            $pages = aa_fabric_pages($data);

            if ($pages === []) {
                return '';
            }

            $html = '';
            foreach ($pages as $index => $pageData) {
                $artboard = is_array($pageData['artboard'] ?? null) ? $pageData['artboard'] : [];
                $width = max(1, (int) ($artboard['width'] ?? 1080));
                $height = max(1, (int) ($artboard['height'] ?? 1920));
                $title = esc((string) ($pageData['title'] ?? 'Halaman ' . ($index + 1)), 'attr');
                $ratio = $height > 0 ? $width / $height : 0.5625;
                $maxWidth = $ratio >= 1 ? 860 : 520;
                $pageBg = aa_fabric_css_color_value($pageData['background'] ?? $pageData['backgroundColor'] ?? '#ffffff');
                $loaderText = aa_fabric_loader_text_color($pageBg);
                $html .= '<section class="aa-fabric-page-section" data-page-index="' . $index . '">'
                    . '<div class="aa-fabric-artboard" style="--aa-artboard-ratio:' . $ratio . ';--aa-artboard-max-width:' . $maxWidth . 'px;--aa-page-bg:' . esc($pageBg, 'attr') . ';--aa-loader-text:' . esc($loaderText, 'attr') . ';aspect-ratio:' . $width . '/' . $height . ';">'
                    . '<canvas id="aaFabricPublicCanvas' . $index . '" aria-label="' . $title . '"></canvas>'
                    . '</div>'
                    . '</section>';
            }

            return '<main class="aa-fabric-page">' . $html . '</main>';
        }
    }

    if (! function_exists('aa_normalize_fabric_artboard_html')) {
        function aa_normalize_fabric_artboard_html(string $html): string
        {
            if (stripos($html, 'aa-fabric-artboard') === false) {
                return $html;
            }

            return preg_replace_callback(
                '/(<div\b[^>]*class=(["\'])(?=[^"\']*\baa-fabric-artboard\b)[^"\']*\2[^>]*style=(["\'])([^"\']*)\3[^>]*>)/i',
                static function (array $matches): string {
                    $tag = $matches[1];
                    $style = $matches[4];

                    if (stripos($style, '--aa-artboard-ratio') !== false) {
                        return $tag;
                    }

                    if (! preg_match('/aspect-ratio\s*:\s*([0-9.]+)\s*\/\s*([0-9.]+)/i', $style, $ratioMatch)) {
                        return $tag;
                    }

                    $width = max(1.0, (float) $ratioMatch[1]);
                    $height = max(1.0, (float) $ratioMatch[2]);
                    $ratio = $width / $height;
                    $maxWidth = $ratio >= 1 ? 860 : 520;
                    $addition = '--aa-artboard-ratio:' . rtrim(rtrim(number_format($ratio, 6, '.', ''), '0'), '.') . ';--aa-artboard-max-width:' . $maxWidth . 'px;';

                    return preg_replace('/style=(["\'])/i', 'style=$1' . $addition, $tag, 1) ?: $tag;
                },
                $html
            ) ?? $html;
        }
    }

    if (! function_exists('aa_fabric_scale_override_css')) {
        function aa_fabric_scale_override_css(): string
        {
            return '
.aa-fabric-page {
    justify-items: center !important;
    gap: 0 !important;
}
.aa-fabric-page-section {
    width: 100% !important;
    min-height: 0 !important;
    box-sizing: border-box !important;
    padding: 0 !important;
    margin: 0 !important;
}
.aa-fabric-artboard {
    position: relative !important;
    width: min(100%, var(--aa-artboard-max-width, 520px)) !important;
    max-width: 100% !important;
    margin: 0 auto !important;
    background: var(--aa-page-bg, #ffffff) !important;
}
.aa-fabric-artboard.is-rendering::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 6;
    display: block;
    background-color: var(--aa-page-bg, #ffffff);
    background:
        linear-gradient(105deg, transparent 0%, rgba(200, 135, 45, .18) 34%, rgba(226, 232, 240, .64) 48%, rgba(200, 135, 45, .18) 62%, transparent 78%),
        var(--aa-page-bg, #ffffff);
    background-size: 240% 100%, 100% 100%;
    animation: aa-public-render-shimmer 1.18s ease-in-out infinite;
    transition: opacity .24s ease, visibility .24s ease;
}
@keyframes aa-public-render-shimmer {
    0% { background-position: 180% 0, 0 0; }
    100% { background-position: -80% 0, 0 0; }
}
@media (prefers-reduced-motion: reduce) {
    .aa-fabric-artboard.is-rendering::after {
        animation: none;
        background-position: 0 0;
    }
}
.aa-fabric-artboard.is-rendering .canvas-container,
.aa-fabric-artboard.is-rendering canvas,
.aa-fabric-artboard.is-rendering .upper-canvas,
.aa-fabric-artboard.is-rendering .lower-canvas,
.aa-fabric-artboard.is-rendering .aa-fabric-click-layer,
.aa-fabric-artboard.is-rendering .aa-fabric-guestbook-layer,
.aa-fabric-artboard.is-rendering .aa-fabric-interactive-layer,
body.aa-public-stabilizing .aa-fabric-artboard .canvas-container,
body.aa-public-stabilizing .aa-fabric-artboard canvas,
body.aa-public-stabilizing .aa-fabric-artboard .upper-canvas,
body.aa-public-stabilizing .aa-fabric-artboard .lower-canvas,
body.aa-public-stabilizing .aa-fabric-artboard .aa-fabric-click-layer,
body.aa-public-stabilizing .aa-fabric-artboard .aa-fabric-guestbook-layer,
body.aa-public-stabilizing .aa-fabric-artboard .aa-fabric-interactive-layer {
    opacity: 0 !important;
    pointer-events: none !important;
}
.aa-fabric-artboard .canvas-container,
.aa-fabric-artboard canvas,
.aa-fabric-artboard .upper-canvas,
.aa-fabric-artboard .lower-canvas {
    position: relative !important;
    z-index: 1 !important;
    width: 100% !important;
    height: 100% !important;
    touch-action: pan-y pinch-zoom !important;
}
.aa-fabric-click-layer,
.aa-fabric-bg-gif-layer,
.aa-fabric-guestbook-layer,
.aa-fabric-interactive-layer {
    position: absolute !important;
    inset: 0 !important;
    pointer-events: none !important;
}
.aa-fabric-bg-gif-layer {
    z-index: 0 !important;
    overflow: hidden !important;
}
.aa-fabric-bg-gif-layer img {
    position: absolute !important;
    display: block !important;
    object-fit: fill !important;
    max-width: none !important;
    max-height: none !important;
    transform-origin: center center !important;
    pointer-events: none !important;
}
.aa-fabric-gif-under-layer {
    position: absolute !important;
    inset: 0 !important;
    z-index: 0 !important;
    pointer-events: none !important;
    overflow: hidden !important;
}
.aa-fabric-gif-under-layer img {
    position: absolute !important;
    display: block !important;
    object-fit: fill !important;
    max-width: none !important;
    max-height: none !important;
    transform-origin: center center !important;
    pointer-events: none !important;
}
.aa-fabric-click-layer {
    z-index: 3 !important;
}
.aa-fabric-guestbook-layer {
    z-index: 4 !important;
}
.aa-fabric-interactive-layer {
    z-index: 5 !important;
}
.aa-fabric-hotspot,
.aa-fabric-guestbook-control,
.aa-fabric-interactive-control {
    position: absolute !important;
    box-sizing: border-box !important;
    pointer-events: auto !important;
}
.aa-fabric-overlay-animated {
    --aa-overlay-base-transform: rotate(0deg);
    --aa-overlay-animation-duration: 900ms;
    --aa-overlay-animation-delay: 0ms;
    --aa-overlay-final-opacity: 1;
    animation-duration: var(--aa-overlay-animation-duration);
    animation-delay: var(--aa-overlay-animation-delay);
    animation-fill-mode: both;
    animation-timing-function: cubic-bezier(.22, 1, .36, 1);
    transform-origin: center center;
}
.aa-fabric-overlay-animation-waiting {
    animation-play-state: paused;
}
.aa-overlay-animation-fade-in { animation-name: aaOverlayFadeIn; }
.aa-overlay-animation-rise,
.aa-overlay-animation-fade-up { animation-name: aaOverlayFadeUp; }
.aa-overlay-animation-fade-down { animation-name: aaOverlayFadeDown; }
.aa-overlay-animation-fade-left { animation-name: aaOverlayFadeLeft; }
.aa-overlay-animation-fade-right { animation-name: aaOverlayFadeRight; }
.aa-overlay-animation-slide-up { animation-name: aaOverlaySlideUp; }
.aa-overlay-animation-slide-down { animation-name: aaOverlaySlideDown; }
.aa-overlay-animation-slide-left { animation-name: aaOverlaySlideLeft; }
.aa-overlay-animation-slide-right { animation-name: aaOverlaySlideRight; }
.aa-overlay-animation-zoom-in { animation-name: aaOverlayZoomIn; }
.aa-overlay-animation-zoom-out { animation-name: aaOverlayZoomOut; }
.aa-overlay-animation-flip-in { animation-name: aaOverlayFlipIn; }
.aa-overlay-animation-bounce { animation-name: aaOverlayBounce; }
.aa-overlay-animation-pulse { animation-name: aaOverlayPulse; }
.aa-overlay-animation-swing { animation-name: aaOverlaySwing; }
.aa-overlay-animation-spin { animation-name: aaOverlaySpin; }
.aa-overlay-animation-float-loop,
.aa-overlay-animation-sway-loop,
.aa-overlay-animation-pulse-loop,
.aa-overlay-animation-spin-loop,
.aa-overlay-animation-heartbeat-loop,
.aa-overlay-animation-drift-loop {
    animation-duration: var(--aa-overlay-loop-duration, var(--aa-overlay-animation-duration));
    animation-iteration-count: infinite;
    animation-timing-function: ease-in-out;
}
.aa-overlay-animation-float-loop { --aa-overlay-loop-duration: 2600ms; animation-name: aaOverlayFloatLoop; }
.aa-overlay-animation-sway-loop { --aa-overlay-loop-duration: 1900ms; animation-name: aaOverlaySwayLoop; }
.aa-overlay-animation-pulse-loop { --aa-overlay-loop-duration: 1560ms; animation-name: aaOverlayPulseLoop; }
.aa-overlay-animation-spin-loop { --aa-overlay-loop-duration: 4200ms; animation-name: aaOverlaySpinLoop; }
.aa-overlay-animation-heartbeat-loop { --aa-overlay-loop-duration: 720ms; animation-name: aaOverlayHeartbeatLoop; }
.aa-overlay-animation-drift-loop { --aa-overlay-loop-duration: 2400ms; animation-name: aaOverlayDriftLoop; }
@keyframes aaOverlayFadeIn { from { opacity: 0; transform: var(--aa-overlay-base-transform); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform); } }
@keyframes aaOverlayFadeUp { from { opacity: 0; transform: var(--aa-overlay-base-transform) translateY(36px); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlayFadeDown { from { opacity: 0; transform: var(--aa-overlay-base-transform) translateY(-36px); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlayFadeLeft { from { opacity: 0; transform: var(--aa-overlay-base-transform) translateX(42px); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) translateX(0); } }
@keyframes aaOverlayFadeRight { from { opacity: 0; transform: var(--aa-overlay-base-transform) translateX(-42px); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) translateX(0); } }
@keyframes aaOverlaySlideUp { from { transform: var(--aa-overlay-base-transform) translateY(58px); } to { transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlaySlideDown { from { transform: var(--aa-overlay-base-transform) translateY(-58px); } to { transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlaySlideLeft { from { transform: var(--aa-overlay-base-transform) translateX(64px); } to { transform: var(--aa-overlay-base-transform) translateX(0); } }
@keyframes aaOverlaySlideRight { from { transform: var(--aa-overlay-base-transform) translateX(-64px); } to { transform: var(--aa-overlay-base-transform) translateX(0); } }
@keyframes aaOverlayZoomIn { from { opacity: 0; transform: var(--aa-overlay-base-transform) scale(.72); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) scale(1); } }
@keyframes aaOverlayZoomOut { from { opacity: 0; transform: var(--aa-overlay-base-transform) scale(1.28); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) scale(1); } }
@keyframes aaOverlayFlipIn { from { opacity: 0; transform: var(--aa-overlay-base-transform) rotateY(82deg); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) rotateY(0); } }
@keyframes aaOverlayBounce { 0% { transform: var(--aa-overlay-base-transform) translateY(-42px); } 65% { transform: var(--aa-overlay-base-transform) translateY(8px); } 100% { transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlayPulse { 0%, 100% { transform: var(--aa-overlay-base-transform) scale(1); } 50% { transform: var(--aa-overlay-base-transform) scale(1.12); } }
@keyframes aaOverlaySwing { 0% { transform: var(--aa-overlay-base-transform) rotate(-10deg); } 50% { transform: var(--aa-overlay-base-transform) rotate(10deg); } 100% { transform: var(--aa-overlay-base-transform) rotate(0); } }
@keyframes aaOverlaySpin { from { transform: var(--aa-overlay-base-transform) rotate(0); } to { transform: var(--aa-overlay-base-transform) rotate(360deg); } }
@keyframes aaOverlayFloatLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) translateY(0); } 50% { transform: var(--aa-overlay-base-transform) translateY(-18px); } }
@keyframes aaOverlaySwayLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) rotate(-6deg); } 50% { transform: var(--aa-overlay-base-transform) rotate(6deg); } }
@keyframes aaOverlayPulseLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) scale(1); } 50% { transform: var(--aa-overlay-base-transform) scale(1.08); } }
@keyframes aaOverlaySpinLoop { from { transform: var(--aa-overlay-base-transform) rotate(0); } to { transform: var(--aa-overlay-base-transform) rotate(360deg); } }
@keyframes aaOverlayHeartbeatLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) scale(1); } 35% { transform: var(--aa-overlay-base-transform) scale(1.16); } 60% { transform: var(--aa-overlay-base-transform) scale(.98); } }
@keyframes aaOverlayDriftLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) translateX(0); } 50% { transform: var(--aa-overlay-base-transform) translateX(18px); } }
';
        }
    }

    if (! function_exists('aa_fabric_fallback_css')) {
        function aa_fabric_fallback_css(): string
        {
            return '
.aa-fabric-page {
    min-height: 100vh;
    display: grid;
    margin: 0;
    background: #f1f5f9;
    box-sizing: border-box;
    justify-items: center;
    gap: 0;
}
.aa-fabric-page-section {
    display: grid;
    place-items: center;
    width: 100%;
    min-height: 0;
    box-sizing: border-box;
    padding: 0;
    margin: 0;
}
.aa-fabric-artboard {
    position: relative;
    width: min(100%, var(--aa-artboard-max-width, 520px));
    margin: 0 auto;
    background: var(--aa-page-bg, #ffffff);
    overflow: hidden;
    box-shadow: 0 20px 70px rgba(15, 23, 42, .14);
}
.aa-fabric-artboard.is-rendering::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 6;
    display: block;
    background-color: var(--aa-page-bg, #ffffff);
    background:
        linear-gradient(105deg, transparent 0%, rgba(200, 135, 45, .18) 34%, rgba(226, 232, 240, .64) 48%, rgba(200, 135, 45, .18) 62%, transparent 78%),
        var(--aa-page-bg, #ffffff);
    background-size: 240% 100%, 100% 100%;
    animation: aa-public-render-shimmer 1.18s ease-in-out infinite;
    transition: opacity .24s ease, visibility .24s ease;
}
@keyframes aa-public-render-shimmer {
    0% { background-position: 180% 0, 0 0; }
    100% { background-position: -80% 0, 0 0; }
}
@media (prefers-reduced-motion: reduce) {
    .aa-fabric-artboard.is-rendering::after {
        animation: none;
        background-position: 0 0;
    }
}
.aa-fabric-artboard.is-rendering .canvas-container,
.aa-fabric-artboard.is-rendering canvas,
.aa-fabric-artboard.is-rendering .upper-canvas,
.aa-fabric-artboard.is-rendering .lower-canvas,
.aa-fabric-artboard.is-rendering .aa-fabric-click-layer,
.aa-fabric-artboard.is-rendering .aa-fabric-guestbook-layer,
.aa-fabric-artboard.is-rendering .aa-fabric-interactive-layer,
body.aa-public-stabilizing .aa-fabric-artboard .canvas-container,
body.aa-public-stabilizing .aa-fabric-artboard canvas,
body.aa-public-stabilizing .aa-fabric-artboard .upper-canvas,
body.aa-public-stabilizing .aa-fabric-artboard .lower-canvas,
body.aa-public-stabilizing .aa-fabric-artboard .aa-fabric-click-layer,
body.aa-public-stabilizing .aa-fabric-artboard .aa-fabric-guestbook-layer,
body.aa-public-stabilizing .aa-fabric-artboard .aa-fabric-interactive-layer {
    opacity: 0 !important;
    pointer-events: none !important;
}
.aa-fabric-artboard .canvas-container,
.aa-fabric-artboard canvas,
.aa-fabric-artboard .upper-canvas,
.aa-fabric-artboard .lower-canvas {
    position: relative;
    z-index: 1;
    display: block;
    width: 100% !important;
    height: 100% !important;
    touch-action: pan-y pinch-zoom !important;
    transition: opacity .24s ease;
}
.aa-fabric-click-layer {
    position: absolute;
    inset: 0;
    z-index: 3;
    pointer-events: none;
}
.aa-fabric-bg-gif-layer {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}
.aa-fabric-bg-gif-layer img {
    position: absolute;
    display: block;
    object-fit: fill;
    max-width: none;
    max-height: none;
    transform-origin: center center;
    pointer-events: none;
}
.aa-fabric-gif-under-layer {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}
.aa-fabric-gif-under-layer img {
    position: absolute;
    display: block;
    object-fit: fill;
    max-width: none;
    max-height: none;
    transform-origin: center center;
    pointer-events: none;
}
.aa-fabric-guestbook-layer,
.aa-fabric-interactive-layer {
    position: absolute;
    inset: 0;
    z-index: 4;
    pointer-events: none;
}
.aa-fabric-interactive-layer {
    z-index: 5;
}
.aa-fabric-hotspot {
    position: absolute;
    display: block;
    border: 0;
    background: transparent;
    color: transparent;
    font-size: 0;
    line-height: 0;
    padding: 0;
    cursor: pointer;
    pointer-events: auto;
    touch-action: manipulation pan-y;
}
.aa-fabric-gallery-hotspot {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
    -webkit-user-select: none;
}
.aa-fabric-guestbook-control,
.aa-fabric-interactive-control {
    position: absolute;
    box-sizing: border-box;
    pointer-events: auto;
    font-family: Inter, Arial, sans-serif;
    line-height: 1.15;
}
.aa-fabric-guestbook-control input,
.aa-fabric-guestbook-control select,
.aa-fabric-guestbook-control textarea,
.aa-fabric-guestbook-control button {
    width: 100%;
    height: 100%;
    min-height: 0;
    box-sizing: border-box;
    border: var(--aa-field-border-width, 1px) solid var(--aa-field-border-color, #cbd5e1);
    border-radius: inherit;
    background: inherit;
    color: inherit;
    font: inherit;
    text-align: inherit;
    line-height: var(--aa-field-line-height, 1.25);
    padding: var(--aa-field-padding-y, 10px) var(--aa-field-padding-x, 14px);
    margin: 0;
    outline: none;
    box-shadow: none;
    appearance: none;
    -webkit-appearance: none;
}
.aa-fabric-guestbook-control textarea {
    resize: none;
}
.aa-fabric-guestbook-control input::placeholder,
.aa-fabric-guestbook-control textarea::placeholder {
    color: currentColor;
    opacity: .72;
}
.aa-fabric-guestbook-control button,
.aa-fabric-music-button,
.aa-fabric-scroll-button {
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
}
.aa-fabric-sticker-popover {
    position: absolute;
    left: 0;
    bottom: calc(100% + 8px);
    z-index: 7;
    display: none;
    width: min(250px, 82vw);
    max-height: 40vh;
    overflow-y: auto;
    grid-template-columns: repeat(5, 1fr);
    gap: 6px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    padding: 10px;
    box-shadow: 0 18px 48px rgba(15, 23, 42, .18);
}
.aa-fabric-sticker-popover.is-open {
    display: grid;
}
.aa-fabric-sticker-popover button {
    height: auto;
    border-radius: 10px;
    padding: 4px;
}
.aa-fabric-sticker-popover img {
    display: block;
    width: 42px;
    height: 42px;
    object-fit: contain;
}
.aa-fabric-selected-sticker {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    display: none;
    align-items: center;
    gap: 7px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .96);
    padding: 5px 8px;
    color: inherit;
    font: 700 11px Inter, Arial, sans-serif;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .16);
}
.aa-fabric-selected-sticker.is-visible {
    display: inline-flex;
}
.aa-fabric-selected-sticker img {
    width: 28px;
    height: 28px;
    object-fit: contain;
}
.aa-fabric-comment-list {
    width: 100%;
    height: 100%;
    overflow-y: auto;
    display: grid;
    gap: 8px;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: inherit;
    background: inherit;
    color: inherit;
}
.aa-fabric-comment-card,
.aa-fabric-comment-empty {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: rgba(255, 255, 255, .82);
    color: inherit;
    padding: 10px;
}
.aa-fabric-comment-card strong,
.aa-fabric-comment-card p,
.aa-fabric-comment-empty {
    color: inherit;
}
.aa-fabric-music-button,
.aa-fabric-scroll-button {
    display: flex;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: var(--aa-control-border-width, 1px) solid var(--aa-control-border-color, transparent);
    border-radius: inherit;
    background: var(--aa-control-bg, #ffffff);
    color: var(--aa-control-color, #0f172a);
    font: inherit;
    font-weight: 900;
    line-height: 1;
    overflow: hidden;
}
.aa-fabric-music-button {
    display: grid;
    place-items: center;
    gap: 0;
    padding: 0;
    text-align: center;
}
.aa-fabric-music-icon {
    display: block;
    font-size: 25px;
    line-height: 1;
    top: -1px;
    position: relative;
    left: -1px;
}
.aa-fabric-social-box,
.aa-fabric-story-box {
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    overflow: auto;
    border: 1px solid rgba(15, 118, 110, .16);
    border-radius: inherit;
    background: var(--aa-control-bg, rgba(255, 255, 255, .92));
    color: var(--aa-control-color, #0f172a);
    padding: clamp(10px, 3vw, 18px);
    font-family: Inter, ui-sans-serif, system-ui, sans-serif;
}
.aa-fabric-social-box strong,
.aa-fabric-story-box > strong {
    display: block;
    margin-bottom: 10px;
    font-size: clamp(14px, 4vw, 22px);
    font-weight: 950;
    line-height: 1.15;
    text-align: center;
}
.aa-fabric-social-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}
.aa-fabric-social-link {
    display: inline-grid;
    min-width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 999px;
    background: #0f766e;
    color: #ffffff;
    padding: 0 10px;
    font-size: 12px;
    font-weight: 950;
    text-decoration: none;
}
.aa-social-instagram { background: #e1306c; }
.aa-social-tiktok { background: #111827; }
.aa-social-threads { background: #000000; }
.aa-social-x { background: #0f172a; }
.aa-social-facebook { background: #1877f2; }
.aa-social-youtube { background: #ff0000; }
.aa-fabric-social-empty,
.aa-fabric-story-empty {
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    text-align: center;
}
.aa-fabric-story-list {
    display: grid;
    gap: 10px;
}
.aa-fabric-story-item {
    display: grid;
    gap: 4px;
    border-left: 3px solid #0f766e;
    border-radius: 12px;
    background: rgba(255, 255, 255, .72);
    padding: 9px 10px;
}
.aa-fabric-story-item small {
    color: #0f766e;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.aa-fabric-story-item b {
    font-size: 13px;
    font-weight: 950;
}
.aa-fabric-story-item p {
    margin: 0;
    color: #475569;
    font-size: 11px;
    font-weight: 650;
    line-height: 1.45;
}
.aa-fabric-countdown {
    display: grid;
    width: 100%;
    height: 100%;
    grid-template-columns: repeat(var(--aa-countdown-columns, 4), 1fr);
    gap: var(--aa-countdown-gap, 8px);
    align-items: center;
    border-radius: inherit;
}
.aa-fabric-countdown span {
    position: relative;
    display: grid;
    min-width: 0;
    min-height: 0;
    height: 100%;
    border: var(--aa-control-border-width, 1px) solid var(--aa-control-border-color, transparent);
    border-radius: var(--aa-countdown-card-radius, inherit);
    background: var(--aa-control-bg, #ffffff);
    color: var(--aa-control-color, #0f172a);
    padding: 0;
    text-align: center;
    font-weight: 900;
}
.aa-fabric-countdown strong,
.aa-fabric-countdown small {
    position: absolute;
    left: 50%;
    width: 100%;
    transform: translate(-50%, -50%);
    line-height: 1;
}
.aa-fabric-countdown strong {
    top: 38%;
}
.aa-fabric-countdown small {
    top: 76%;
    font-size: .36em;
    opacity: .72;
    text-transform: uppercase;
}
.aa-fabric-gallery {
    display: grid;
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.aa-fabric-gallery button {
    overflow: hidden;
    border: 0;
    background: #e2e8f0;
    padding: 0;
    cursor: pointer;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
    -webkit-user-select: none;
}
.aa-fabric-gallery img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.aa-fabric-youtube-frame,
.aa-fabric-youtube-placeholder {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
    border-radius: inherit;
    overflow: hidden;
    background: #111827;
}
.aa-fabric-youtube-frame {
    pointer-events: auto;
}
.aa-fabric-youtube-placeholder {
    display: grid;
    place-items: center;
    color: #ffffff;
    font: 900 14px Inter, Arial, sans-serif;
    text-align: center;
    padding: 14px;
}
.aa-fabric-lightbox {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    place-items: center;
    background: rgba(15, 23, 42, .82);
    padding: 18px;
}
.aa-fabric-lightbox.is-open {
    display: grid;
}
.aa-fabric-lightbox img {
    display: block;
    max-width: min(94vw, 980px);
    max-height: 88vh;
    border-radius: 18px;
    object-fit: contain;
    background: #ffffff;
}
.aa-fabric-lightbox button {
    position: fixed;
    top: 18px;
    right: 18px;
    border: 0;
    border-radius: 999px;
    background: #ffffff;
    color: #0f172a;
    padding: 10px 14px;
    font: 900 14px Inter, Arial, sans-serif;
    cursor: pointer;
}
.aa-fabric-hybrid-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transform-origin: top left;
    overflow: hidden;
    pointer-events: none;
    z-index: 2;
}
.aa-hybrid-text {
    position: absolute;
    pointer-events: auto;
    box-sizing: border-box;
}
.aa-hybrid-image {
    position: absolute;
    pointer-events: auto;
    box-sizing: border-box;
    max-width: none;
    max-height: none;
}
.aa-hybrid-rect, .aa-hybrid-circle {
    position: absolute;
    pointer-events: auto;
    box-sizing: border-box;
}
.aa-hybrid-path {
    position: absolute;
    pointer-events: auto;
    box-sizing: border-box;
    overflow: visible;
}
.aa-hybrid-path path {
    vector-effect: non-scaling-stroke;
}
.aa-hybrid-webgl-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}
';
        }
    }

    if (! function_exists('aa_sanitize_render_js')) {
        function aa_sanitize_render_js(string $js): string
        {
            $replacements = [
                "url.replace(/^/+/, '')" => "url.replace(/^\\/+/, '')",
                'url.replace(/^/+/, "")' => 'url.replace(/^\\/+/, "")',
                "url.replace(/^\\/+, '')" => "url.replace(/^\\/+/, '')",
                'url.replace(/^\\/+, "")' => 'url.replace(/^\\/+/, "")',
                "replace(/+/g, ' ')" => "replace(/\\+/g, ' ')",
                'replace(/+/g, " ")' => 'replace(/\\+/g, " ")',
                'new fabric.Canvas(canvasEl,' => 'new fabric.StaticCanvas(canvasEl,',
                'new fabric.Canvas(canvasEl, {' => 'new fabric.StaticCanvas(canvasEl, {',
                '"textBaseline":"alphabetical"' => '"textBaseline":"alphabetic"',
                '"textBaseline": "alphabetical"' => '"textBaseline": "alphabetic"',
                "'textBaseline':'alphabetical'" => "'textBaseline':'alphabetic'",
                "'textBaseline': 'alphabetical'" => "'textBaseline': 'alphabetic'",
                'textBaseline:"alphabetical"' => 'textBaseline:"alphabetic"',
                'textBaseline: "alphabetical"' => 'textBaseline: "alphabetic"',
                "textBaseline:'alphabetical'" => "textBaseline:'alphabetic'",
                "textBaseline: 'alphabetical'" => "textBaseline: 'alphabetic'",
            ];

            $js = strtr($js, $replacements);
            
            $js = str_replace(
                'if (object.shadow) object.shadow = null;',
                "if (!(['i-text', 'textbox', 'text'].indexOf(object.type) !== -1) && object.shadow) object.shadow = null;",
                $js
            );
            $js = preg_replace(
                '/var\s+script\s*=\s*document\.createElement\([\'"]script[\'"]\);\s*script\.src\s*=\s*[\'"]https:\/\/cdn\.jsdelivr\.net\/npm\/fabric[^;]+;/i',
                "var existing = document.querySelector('script[src*=\"fabric.min.js\"]');\n        if (existing) {\n            existing.addEventListener('load', function() { if (window.fabric) callback(); });\n            return;\n        }\n        var script = document.createElement('script');\n        script.src = 'https://adaacara.com/assets/js/fabric.min.js';\n        script.async = true;",
                $js
            );
            $js = str_replace(
                "    function loadFabric(callback) {",
                "    var aaPublicPerf = window.AdaAcaraPublicPerf || (window.AdaAcaraPublicPerf = (function () {\n        var cores = Number(navigator.hardwareConcurrency || 4);\n        var memory = Number(navigator.deviceMemory || 4);\n        var width = Math.min(window.innerWidth || 1024, screen.width || 1024);\n        var constrainedDevice = memory > 0 && memory <= 2;\n        var weakCpuAndMemory = cores <= 2 && memory <= 3;\n        var lowEnd = window.AdaAcaraLiteMode === true || constrainedDevice || weakCpuAndMemory;\n        return {\n            lowEnd: lowEnd,\n            safeDpr: lowEnd ? 1 : Math.min(window.devicePixelRatio || 1, 1.5),\n            maxCanvasWidth: lowEnd ? 720 : 1080,\n            maxCanvasHeight: lowEnd ? 1280 : 1920,\n            rootMargin: lowEnd ? '180px 0px 260px 0px' : '360px 0px 520px 0px'\n        };\n    })());\n    function loadFabric(callback) {",
                $js
            );
            $js = str_replace(
                "safeDpr: lowEnd ? 1 : Math.min(window.devicePixelRatio || 1, 1.5),\n            maxCanvasWidth: lowEnd ? 720 : 1080,\n            maxCanvasHeight: lowEnd ? 1280 : 1920,",
                "safeDpr: lowEnd ? Math.min(window.devicePixelRatio || 1, 1.25) : (width <= 768 ? Math.min(window.devicePixelRatio || 1, 1.5) : Math.min(window.devicePixelRatio || 1, 2)),",
                $js
            );
            $js = str_replace(
                "        if (['i-text', 'textbox', 'text'].indexOf(object.type) !== -1) {",
                "        var isTextEffectObject = ['i-text', 'textbox', 'text'].indexOf(object.type) !== -1;\n        if (isTextEffectObject) { object.objectCaching = false; }\n        if (aaPublicPerf.lowEnd) {\n            if (!isTextEffectObject && object.shadow) object.shadow = null;\n            if (!object.aaImageEffectPreset && Array.isArray(object.filters)) object.filters = [];\n            if (!isTextEffectObject && object.objectCaching !== false) object.objectCaching = true;\n            object.noScaleCache = true;\n        }\n\n        if (object.visible === false) {\n            object.__aaSkipObject = true;\n            return object;\n        }\n\n        if (isTextEffectObject) {",
                $js
            );
            $js = str_replace(
                "            pageData.objects.forEach(sanitizeFabricObject);\n        }\n        return pageData;",
                "            pageData.objects.forEach(sanitizeFabricObject);\n            pageData.objects = pageData.objects.filter(function (object) {\n                return object && object.__aaSkipObject !== true;\n            });\n        }\n        return pageData;",
                $js
            );
            $js = str_replace(
                "        var width = pageData.artboard && pageData.artboard.width ? pageData.artboard.width : 1080;\n        var height = pageData.artboard && pageData.artboard.height ? pageData.artboard.height : 1920;",
                "        var width = pageData.artboard && pageData.artboard.width ? pageData.artboard.width : 1080;\n        var height = pageData.artboard && pageData.artboard.height ? pageData.artboard.height : 1920;\n        if (window.fabric) fabric.devicePixelRatio = aaPublicPerf.safeDpr;",
                $js
            );
            $js = str_replace(
                "                width: width,\n                height: height\n            });",
                "                width: width,\n                height: height\n            });",
                $js
            );
            $js = str_replace(
                "            width: width,\n            height: height,\n            renderOnAddRemove: false\n        });",
                "            width: width,\n            height: height,\n            renderOnAddRemove: false,\n            enableRetinaScaling: true,\n            skipOffscreen: true\n        });",
                $js
            );
            $js = str_replace(
                "        canvasEl.__aaFabricCanvas = canvas;",
                "        canvasEl.__aaFabricCanvas = canvas;\n        canvasEl.__aaFabricOriginalWidth = width;\n        canvasEl.__aaFabricOriginalHeight = height;\n        canvasEl.__aaFabricScale = 1;\n        canvasEl.setAttribute('data-aa-rendered', 'true');",
                $js
            );
            $js = str_replace(
                "            rootMargin: '320px 0px 420px 0px',",
                "            rootMargin: aaPublicPerf.rootMargin,",
                $js
            );
            // $js = str_replace(
            //     "        if (document.fonts && document.fonts.ready) {\n            document.fonts.ready.then(startRender).catch(startRender);\n            return;\n        }\n        startRender();",
            //     "        startRender();",
            //     $js
            // );
            $js = str_replace(
                "    function getAnimationObjects(canvas) {\n        var objects = canvas && canvas.getObjects ? canvas.getObjects().filter(isAnimationObject) : [];\n        var hasManualOrder = objects.some(function (object) {\n            return object.animationOrderMode === 'manual';\n        });\n        return hasManualOrder ? objects : getAnimationSortedObjects(canvas);\n    }",
                "    function getAnimationObjects(canvas) {\n        return getAnimationSortedObjects(canvas);\n    }",
                $js
            );
            $js = str_replace(
                "        return isFinite(value) && value > 0 ? value : fallback;",
                "        var duration = isFinite(value) && value > 0 ? value : fallback;\n        if (window.AdaAcaraLiteMode === true || document.documentElement.classList.contains('aa-lite-mode')) {\n            return Math.max(140, Math.min(420, Math.round(duration * .55)));\n        }\n        return duration;",
                $js
            );
            $js = str_replace(
                "    function isTextObject(object) {\n        return object && ['i-text', 'textbox', 'text'].indexOf(object.type) !== -1;\n    }\n    function walkObjects(objects, callback) {",
                "    function isTextObject(object) {\n        return object && ['i-text', 'textbox', 'text'].indexOf(object.type) !== -1;\n    }\n    function isAnimatedTextLayerBlocker(object) {\n        return isTextObject(object) &&\n            object.visible !== false &&\n            object.__aaSkipObject !== true &&\n            aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled;\n    }\n    function isAnimatedLayerBlocker(object) {\n        if (!object || object.visible === false || object.__aaSkipObject === true) return false;\n        if (object.customType === 'background' || object.excludeFromAnimation === true) return false;\n        if (isAnimatedTextLayerBlocker(object)) return true;\n        var animationName = getObjectAnimationName(object);\n        if (animationName && animationName !== 'none') return true;\n        var children = typeof object.getObjects === 'function'\n            ? object.getObjects()\n            : (Array.isArray(object.objects) ? object.objects : []);\n        for (var i = 0; i < children.length; i += 1) {\n            if (isAnimatedLayerBlocker(children[i])) return true;\n        }\n        return false;\n    }\n    function walkObjects(objects, callback) {",
                $js
            );
            $js = str_replace(
                "    function isAnimatedTextLayerBlocker(object) {\n        return isTextObject(object) &&\n            object.visible !== false &&\n            object.__aaSkipObject !== true &&\n            aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled;\n    }\n    function walkObjects(objects, callback) {",
                "    function isAnimatedTextLayerBlocker(object) {\n        return isTextObject(object) &&\n            object.visible !== false &&\n            object.__aaSkipObject !== true &&\n            aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled;\n    }\n    function isAnimatedLayerBlocker(object) {\n        if (!object || object.visible === false || object.__aaSkipObject === true) return false;\n        if (object.customType === 'background' || object.excludeFromAnimation === true) return false;\n        if (isAnimatedTextLayerBlocker(object)) return true;\n        var animationName = getObjectAnimationName(object);\n        if (animationName && animationName !== 'none') return true;\n        var children = typeof object.getObjects === 'function'\n            ? object.getObjects()\n            : (Array.isArray(object.objects) ? object.objects : []);\n        for (var i = 0; i < children.length; i += 1) {\n            if (isAnimatedLayerBlocker(children[i])) return true;\n        }\n        return false;\n    }\n    function walkObjects(objects, callback) {",
                $js
            );
            $js = str_replace(
                'above.opacity === 0 || isGuestbookObject(above) || isInteractiveObject(above)',
                '(above.opacity === 0 && !isAnimatedLayerBlocker(above)) || isGuestbookObject(above) || isInteractiveObject(above)',
                $js
            );
            $js = str_replace(
                '(above.opacity === 0 && !isAnimatedTextLayerBlocker(above)) || isGuestbookObject(above) || isInteractiveObject(above)',
                '(above.opacity === 0 && !isAnimatedLayerBlocker(above)) || isGuestbookObject(above) || isInteractiveObject(above)',
                $js
            );
            if (str_contains($js, 'function isAnimatedGifObject') && ! str_contains($js, 'function animatedGifObjectOpacity')) {
                $gifAnimationHelper = <<<'JS'
    function isAnimatedGifObject(object) {
        if (!object || object.type !== 'image') return false;
        var src = String(object.aaAnimatedSrc || object.src || (object._element && object._element.src) || '');
        var cleanSrc = src.split('?')[0].toLowerCase();
        return object.aaMediaKind === 'gif' || cleanSrc.endsWith('.gif');
    }
    function animatedGifObjectOpacity(object) {
        var original = object && object.__aaAnimationOriginal;
        var value = original && original.opacity != null
            ? original.opacity
            : (object && object.opacity != null ? object.opacity : 1);
        value = Number(value);
        return Math.max(0, Math.min(1, isFinite(value) ? value : 1));
    }
    function applyAnimatedGifOverlayAnimation(node, object, baseTransform) {
        if (!node || !object) return false;
        var animationName = getObjectAnimationName(object);
        if (!animationName || animationName === 'none') return false;
        var finalOpacity = animatedGifObjectOpacity(object);
        node.style.opacity = String(finalOpacity);
        node.style.setProperty('--aa-overlay-base-transform', baseTransform || node.style.transform || 'rotate(0deg)');
        node.style.setProperty('--aa-overlay-final-opacity', String(finalOpacity));
        applyOverlayAnimation(node, object);
        return node.classList.contains('aa-fabric-overlay-animated');
    }
JS;
                $js = str_replace(
                    "    function isAnimatedGifObject(object) {\n        if (!object || object.type !== 'image') return false;\n        var src = String(object.aaAnimatedSrc || object.src || (object._element && object._element.src) || '');\n        var cleanSrc = src.split('?')[0].toLowerCase();\n        return object.aaMediaKind === 'gif' || cleanSrc.endsWith('.gif');\n    }",
                    $gifAnimationHelper,
                    $js
                );
            }
            if (str_contains($js, 'function isAnimationObject') && ! str_contains($js, 'if (isAnimatedGifObject(object)) return false;')) {
                $js = str_replace(
                    "        if (isGuestbookObject(object) || isInteractiveObject(object)) return false;\n        if (isTextObject(object) && aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled) return false;",
                    "        if (isGuestbookObject(object) || isInteractiveObject(object)) return false;\n        if (isAnimatedGifObject(object)) return false;\n        if (isTextObject(object) && aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled) return false;",
                    $js
                );
            }
            if (str_contains($js, 'function applyAnimatedGifOverlayAnimation') && ! str_contains($js, 'applyAnimatedGifOverlayAnimation(frame, object, frameTransform)')) {
                $js = str_replace(
                    '                frame.style.opacity = String(Math.max(0, Math.min(1, Number(object.opacity == null ? 1 : object.opacity))));',
                    '                frame.style.opacity = String(animatedGifObjectOpacity(object));',
                    $js
                );
                $js = str_replace(
                    "                frame.style.transform = 'translate(-50%, -50%) rotate(' + (Number(object.angle) || 0) + 'deg)' + (object.flipX ? ' scaleX(-1)' : '') + (object.flipY ? ' scaleY(-1)' : '');",
                    "                var frameTransform = 'translate(-50%, -50%) rotate(' + (Number(object.angle) || 0) + 'deg)' + (object.flipX ? ' scaleX(-1)' : '') + (object.flipY ? ' scaleY(-1)' : '');\n                frame.style.transform = frameTransform;\n                if (applyAnimatedGifOverlayAnimation(frame, object, frameTransform)) {\n                    canvas.__aaHasOverlayAnimations = true;\n                }",
                    $js
                );
                $js = str_replace(
                    '            img.style.opacity = String(Math.max(0, Math.min(1, Number(object.opacity == null ? 1 : object.opacity))));',
                    '            img.style.opacity = String(animatedGifObjectOpacity(object));',
                    $js
                );
                $js = str_replace(
                    "            img.style.transform = 'translate(-50%, -50%) rotate(' + (Number(object.angle) || 0) + 'deg)' + (object.flipX ? ' scaleX(-1)' : '') + (object.flipY ? ' scaleY(-1)' : '');",
                    "            var imgTransform = 'translate(-50%, -50%) rotate(' + (Number(object.angle) || 0) + 'deg)' + (object.flipX ? ' scaleX(-1)' : '') + (object.flipY ? ' scaleY(-1)' : '');\n            img.style.transform = imgTransform;\n            if (applyAnimatedGifOverlayAnimation(img, object, imgTransform)) {\n                canvas.__aaHasOverlayAnimations = true;\n            }",
                    $js
                );
            }
            if (str_contains($js, 'function aaPublicGlitterFill') && ! str_contains($js, 'function aaPublicMaterialPatternSize')) {
                $js = str_replace(
                    "    function aaPublicGlitterFill(spec, object) {\n        var sourceWidth = Math.max(144, Math.min(720, Math.ceil(Math.abs(Number(object && object.width) || 0) || 144)));\n        var sourceHeight = Math.max(144, Math.min(720, Math.ceil(Math.abs(Number(object && object.height) || 0) || 144)));",
                    "    function aaPublicIsMaterialTextObject(object) {\n        return object && ['i-text', 'textbox', 'text'].indexOf(String(object.type || '')) !== -1;\n    }\n\n    function aaPublicMaterialPatternSize(object) {\n        var isText = aaPublicIsMaterialTextObject(object);\n        var padding = isText ? Math.max(24, Math.round((Number(object && object.fontSize) || 32) * 0.55)) : 0;\n        var width = Math.abs(Number(object && object.width) || 0) || 144;\n        var height = Math.abs(Number(object && object.height) || 0) || 144;\n        return {\n            width: Math.max(144, Math.min(760, Math.ceil(width + padding * 2))),\n            height: Math.max(144, Math.min(760, Math.ceil(height + padding * 2))),\n            padding: padding\n        };\n    }\n\n    function aaPublicGlitterFill(spec, object) {\n        var patternSize = aaPublicMaterialPatternSize(object);\n        var sourceWidth = patternSize.width;\n        var sourceHeight = patternSize.height;",
                    $js
                );
                $js = str_replace(
                    "        return new fabric.Pattern({ source: canvas, repeat: 'no-repeat' });",
                    "        return new fabric.Pattern({\n            source: canvas,\n            repeat: 'no-repeat',\n            offsetX: -patternSize.padding,\n            offsetY: -patternSize.padding\n        });",
                    $js
                );
                $js = str_replace(
                    "            object.aaMaterialFallback = spec.fallback;\n            object.dirty = true;\n            if (typeof object.initDimensions === 'function') object.initDimensions();",
                    "            object.aaMaterialFallback = spec.fallback;\n            if (aaPublicIsMaterialTextObject(object)) {\n                object.objectCaching = false;\n                object.noScaleCache = true;\n            }\n            object.dirty = true;\n            if (!aaPublicIsMaterialTextObject(object) && typeof object.initDimensions === 'function') object.initDimensions();",
                    $js
                );
            }
            if (str_contains($js, 'function setupAnimatedGifOverlay')) {
                $backgroundGifHelper = <<<'JS'
    function setupAnimatedGifBackground(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard || !canvas || !canvas.getObjects) return;
        var oldLayer = artboard.querySelector('.aa-fabric-bg-gif-layer');
        if (oldLayer) oldLayer.remove();
        var background = canvas.getObjects().find(function (object) {
            return object && object.customType === 'background' && isAnimatedGifObject(object);
        });
        if (!background) return;
        background.visible = false;
        background.evented = false;
        background.selectable = false;
        canvas.backgroundColor = '';
        var canvasWidth = canvas.getWidth() || 1;
        var canvasHeight = canvas.getHeight() || 1;
        var src = background.aaAnimatedSrc || background.src || (background._element && background._element.src) || '';
        if (!src) return;
        var layer = document.createElement('div');
        layer.className = 'aa-fabric-bg-gif-layer';
        var img = document.createElement('img');
        img.alt = '';
        img.loading = 'eager';
        img.decoding = 'async';
        var applyGeometry = function () {
            var isCoverBackground = background.name === 'Background Image' || background.aaBgOffsetX != null || background.aaBgOffsetY != null;
            var sourceWidth = Math.max(1, Number(img.naturalWidth) || 0);
            var sourceHeight = Math.max(1, Number(img.naturalHeight) || 0);
            var center = background.getCenterPoint ? background.getCenterPoint() : {
                x: (Number(background.left) || canvasWidth / 2),
                y: (Number(background.top) || canvasHeight / 2)
            };
            var storedWidth = Math.abs((Number(background.width) || 0) * (Number(background.scaleX) || 1));
            var storedHeight = Math.abs((Number(background.height) || 0) * (Number(background.scaleY) || 1));
            var width = storedWidth > 1 ? storedWidth : canvasWidth;
            var height = storedHeight > 1 ? storedHeight : canvasHeight;
            if (isCoverBackground && sourceWidth > 1 && sourceHeight > 1 && (storedWidth <= 1 || storedHeight <= 1)) {
                var offsetX = Number(background.aaBgOffsetX || 0);
                var offsetY = Number(background.aaBgOffsetY || 0);
                var coverScale = Math.max(canvasWidth / sourceWidth, canvasHeight / sourceHeight);
                center = {
                    x: (canvasWidth / 2) + ((canvasWidth * offsetX) / 100),
                    y: (canvasHeight / 2) + ((canvasHeight * offsetY) / 100)
                };
                width = Math.max(1, sourceWidth * coverScale);
                height = Math.max(1, sourceHeight * coverScale);
            }
            img.style.left = (center.x / canvasWidth * 100) + '%';
            img.style.top = (center.y / canvasHeight * 100) + '%';
            img.style.width = (width / canvasWidth * 100) + '%';
            img.style.height = (height / canvasHeight * 100) + '%';
            img.style.maxWidth = 'none';
            img.style.maxHeight = 'none';
            img.style.opacity = String(Math.max(0, Math.min(1, Number(background.opacity == null ? 1 : background.opacity))));
            img.style.transform = 'translate(-50%, -50%) rotate(' + (Number(background.angle) || 0) + 'deg)' + (background.flipX ? ' scaleX(-1)' : '') + (background.flipY ? ' scaleY(-1)' : '');
        };
        img.addEventListener('load', applyGeometry, { once: true });
        img.src = src;
        applyGeometry();
        layer.appendChild(img);
        artboard.insertBefore(layer, artboard.firstChild);
    }

JS;
                if (str_contains($js, 'function setupAnimatedGifBackground')) {
                    $patchedJs = preg_replace(
                        '/    function setupAnimatedGifBackground\(canvasEl,\s*canvas\)\s*\{[\s\S]*?\n    function setupAnimatedGifOverlay\(canvasEl,\s*canvas\)\s*\{/',
                        $backgroundGifHelper . '    function setupAnimatedGifOverlay(canvasEl, canvas) {',
                        $js,
                        1
                    );
                    if (is_string($patchedJs)) {
                        $js = $patchedJs;
                    }
                } else {
                    $js = str_replace('    function setupAnimatedGifOverlay(canvasEl, canvas) {', $backgroundGifHelper . '    function setupAnimatedGifOverlay(canvasEl, canvas) {', $js);
                }
            }
            if (str_contains($js, 'function setupAnimatedGifBackground') && str_contains($js, 'setupAnimatedGifOverlay(canvasEl, canvas);') && ! str_contains($js, "setupAnimatedGifBackground(canvasEl, canvas);\n                setupAnimatedGifOverlay(canvasEl, canvas);")) {
                $js = str_replace(
                    'setupAnimatedGifOverlay(canvasEl, canvas);',
                    "setupAnimatedGifBackground(canvasEl, canvas);\n                setupAnimatedGifOverlay(canvasEl, canvas);",
                    $js
                );
            }
            if (! str_contains($js, 'function ensureTextAnimationsVisibleFallback')) {
                $js = str_replace(
                    "    function objectSnapshot(object) {",
                    "    function ensureTextAnimationsVisibleFallback(canvas) {\n        var objects = getTextAnimationObjects(canvas);\n        if (!objects.length) return;\n        objects.forEach(function (object) {\n            var config = aaNormalizeTextAnimationConfig(object.aaTextAnimation);\n            var original = aaTextAnimationOriginal(object);\n            var timeout = Math.max(1800, config.delay + config.duration + Math.min(String(original.text || '').length, 80) * config.stagger + 950);\n            window.setTimeout(function () {\n                if (!object || object.visible === false || object.__aaSkipObject === true) return;\n                var currentText = String(object.text || '');\n                var originalText = String(original.text || '');\n                var invisible = Number(object.opacity == null ? 1 : object.opacity) <= 0.02;\n                var incompleteReveal = originalText && currentText.length < originalText.length && ['typewriter', 'letter-fade-up', 'word-reveal'].indexOf(config.type) !== -1;\n                if (!invisible && !incompleteReveal) return;\n                object.set({\n                    opacity: original.opacity == null ? 1 : original.opacity,\n                    top: original.top,\n                    text: originalText,\n                    fill: object.fill || original.fill\n                });\n                if (typeof object.initDimensions === 'function') object.initDimensions();\n                if (typeof object.setCoords === 'function') object.setCoords();\n                object.dirty = true;\n                canvas.requestRenderAll();\n            }, timeout);\n        });\n    }\n\n    function objectSnapshot(object) {",
                    $js
                );
                $js = str_replace(
                    "            runTextAnimations(canvas);\n            if (clickLayer) clickLayer.style.display = '';",
                    "            runTextAnimations(canvas);\n            ensureTextAnimationsVisibleFallback(canvas);\n            if (clickLayer) clickLayer.style.display = '';",
                    $js
                );
            }
            $js = str_replace(
                "        var oldLayer = artboard.querySelector('.aa-fabric-gif-layer');\n        if (oldLayer) oldLayer.remove();\n\n        var allObjects = canvas.getObjects();\n        var objects = allObjects.filter(isAnimatedGifObject);\n        if (!objects.length) return;\n\n        var layer = document.createElement('div');\n        layer.className = 'aa-fabric-gif-layer';\n        artboard.appendChild(layer);",
                "        var oldLayer = artboard.querySelector('.aa-fabric-gif-layer');\n        if (oldLayer) oldLayer.remove();\n        var oldUnderLayer = artboard.querySelector('.aa-fabric-gif-under-layer');\n        if (oldUnderLayer) oldUnderLayer.remove();\n\n        var allObjects = canvas.getObjects();\n        var objects = allObjects.filter(isAnimatedGifObject);\n        if (!objects.length) return;\n\n        var layer = null;\n        var underLayer = null;\n        var ensureUpperLayer = function () {\n            if (layer) return layer;\n            layer = document.createElement('div');\n            layer.className = 'aa-fabric-gif-layer';\n            artboard.appendChild(layer);\n            return layer;\n        };\n        var ensureUnderLayer = function () {\n            if (underLayer) return underLayer;\n            underLayer = document.createElement('div');\n            underLayer.className = 'aa-fabric-gif-under-layer';\n            artboard.insertBefore(underLayer, artboard.firstChild);\n            canvas.backgroundColor = '';\n            return underLayer;\n        };",
                $js
            );
            $js = str_replace(
                '            if (hasVisibleObjectAbove(object, objectRect)) return;',
                '            var targetLayer = hasVisibleObjectAbove(object, objectRect) ? ensureUnderLayer() : ensureUpperLayer();',
                $js
            );
            $js = str_replace('                layer.appendChild(frame);', '                targetLayer.appendChild(frame);', $js);
            $js = str_replace('            layer.appendChild(img);', '            targetLayer.appendChild(img);', $js);
            $js = str_replace(
                "        function renderHotspotGuestNameTemplate(template, guestName) {\n            template = String(template || 'Kepada Yth.\\n{{guest_name}}');\n            return template.replace(/\\{\\{\\s*guest_name\\s*\\}\\}/gi, guestName || 'Tamu Undangan');\n        }",
                "        function renderHotspotGuestNameTemplate(template, guestName) {\n            template = String(template || 'Kepada Yth.\\n{{guest_name}}');\n            if (!/\\{\\{\\s*guest_name\\s*\\}\\}/i.test(template)) {\n                template = template.replace(/Nama Tamu|Tamu Undangan/gi, '{{guest_name}}');\n            }\n            return template.replace(/\\{\\{\\s*guest_name\\s*\\}\\}/gi, guestName || 'Tamu Undangan');\n        }",
                $js
            );
            $js = str_replace(
                "                var nextText = renderHotspotGuestNameTemplate(object.templateText || object.text,\n                    guestName);",
                "                var currentText = object.templateText || object.text || '';\n                if (!currentText && object && typeof object.getObjects === 'function') {\n                    var currentChildren = object.getObjects();\n                    var currentTarget = currentChildren.find(function(child) {\n                        return child.name === 'guest-name-text';\n                    }) || currentChildren.find(isTextHotspotObject);\n                    currentText = currentTarget ? currentTarget.text : '';\n                }\n                var nextText = renderHotspotGuestNameTemplate(currentText, guestName);",
                $js
            );
            $js = str_replace(
                "                var nextText = renderHotspotGuestNameTemplate(object.templateText || object.text, guestName);",
                "                var currentText = object.templateText || object.text || '';\n                if (!currentText && object && typeof object.getObjects === 'function') {\n                    var currentChildren = object.getObjects();\n                    var currentTarget = currentChildren.find(function(child) {\n                        return child.name === 'guest-name-text';\n                    }) || currentChildren.find(isTextHotspotObject);\n                    currentText = currentTarget ? currentTarget.text : '';\n                }\n                var nextText = renderHotspotGuestNameTemplate(currentText, guestName);",
                $js
            );
            $js = str_replace(
                "                if (!(object && (object.isGuestName === true || object.customType === 'guest_name' || object\n                        .dynamicKey === 'guest_name'))) return;",
                "                var objectText = String(object && object.text || '');\n                var normalizedObjectText = objectText.trim().replace(/\\s+/g, ' ');\n                var isGuestNameCandidate = object && (object.isGuestName === true || object.customType === 'guest_name' || object\n                        .dynamicKey === 'guest_name' || (isTextHotspotObject(object) && (/\\{\\{\\s*guest_name\\s*\\}\\}/i.test(objectText) || /\\bNama\\s+Tamu\\b/i.test(objectText) || /^(Kepada\\s+(Yth\\.?|Yang\\s+Terhormat)\\s*)?Tamu\\s+Undangan$/i.test(normalizedObjectText))));\n                if (!isGuestNameCandidate) return;",
                $js
            );
            $js = str_replace(
                "                var isGuestNameCandidate = object && (object.isGuestName === true || object.customType === 'guest_name' || object\n                        .dynamicKey === 'guest_name' || (isTextHotspotObject(object) && /\\bNama\\s+Tamu\\b/i.test(String(object.text || ''))));\n                if (!isGuestNameCandidate) return;",
                "                var objectText = String(object && object.text || '');\n                var normalizedObjectText = objectText.trim().replace(/\\s+/g, ' ');\n                var isGuestNameCandidate = object && (object.isGuestName === true || object.customType === 'guest_name' || object\n                        .dynamicKey === 'guest_name' || (isTextHotspotObject(object) && (/\\{\\{\\s*guest_name\\s*\\}\\}/i.test(objectText) || /\\bNama\\s+Tamu\\b/i.test(objectText) || /^(Kepada\\s+(Yth\\.?|Yang\\s+Terhormat)\\s*)?Tamu\\s+Undangan$/i.test(normalizedObjectText))));\n                if (!isGuestNameCandidate) return;",
                $js
            );

            $renderAllBlock = <<<'JS'
            pages.forEach(function (pageData, index) {
                renderPage(pageData, index);
            });
JS;
            $lazyRenderBlock = <<<'JS'
            lazyRenderPages(pages);
JS;
            $js = str_replace($renderAllBlock, $lazyRenderBlock, $js);
            $js = str_replace('pages.forEach(renderPage);', 'lazyRenderPages(pages);', $js);

            if (str_contains($js, 'lazyRenderPages(pages)') && ! str_contains($js, 'function lazyRenderPages(pages)')) {
                $lazyHelper = <<<'JS'
    function lazyRenderPages(pages) {
        pages = (pages || []).filter(function (pageData) {
            return pageData && pageData.hidden !== true;
        });
        if (!pages.length) return;
        var rendered = {};
        var renderAt = function (index) {
            if (!pages[index]) return;
            if (rendered[index]) return;
            rendered[index] = true;
            var canvasEl = document.getElementById('aaFabricPublicCanvas' + index);
            var section = canvasEl ? canvasEl.closest('.aa-fabric-page-section') : null;
            if (section) {
                section.setAttribute('data-aa-render-state', 'rendering');
            }
            renderPage(pages[index], index);
            if (section) {
                section.setAttribute('data-aa-render-state', 'rendered');
                if (window.AdaAcaraPublicRenderer) {
                    window.AdaAcaraPublicRenderer.preloadSectionAssets(section);
                }
            }
        };
        renderAt(0);
        if (!('IntersectionObserver' in window)) {
            pages.forEach(function (_pageData, index) {
                if (index === 0) return;
                window.setTimeout(function () { renderAt(index); }, index * 260);
            });
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var index = Number(entry.target.getAttribute('data-aa-page-index') || 0);
                renderAt(index);
                if (window.requestIdleCallback) {
                    window.requestIdleCallback(function () {
                        renderAt(index);
                        renderAt(index + 1);
                    }, { timeout: 1000 });
                } else {
                    window.setTimeout(function () {
                        renderAt(index);
                        renderAt(index + 1);
                    }, 20);
                }
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: (window.AdaAcaraPublicRenderer && window.AdaAcaraPublicRenderer.profile && window.AdaAcaraPublicRenderer.profile.rootMargin) || '320px 0px 420px 0px',
            threshold: 0.01
        });
        pages.forEach(function (_pageData, index) {
            if (index === 0) return;
            var canvasEl = document.getElementById('aaFabricPublicCanvas' + index);
            var section = canvasEl ? canvasEl.closest('.aa-fabric-page-section') : null;
            if (!section) {
                window.setTimeout(function () { renderAt(index); }, index * 260);
                return;
            }
            section.setAttribute('data-aa-page-index', String(index));
            observer.observe(section);
        });
    }

JS;
                $js = str_replace('    function renderFabric() {', $lazyHelper . '    function renderFabric() {', $js);
            }

            return $js;
        }
    }

    if (! function_exists('aa_fabric_fallback_js')) {
        function aa_fabric_fallback_js(array $data): string
        {
            $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

            return <<<JS
(function () {
    var fabricData = {$json};
    var perf = (function () {
        var cores = Number(navigator.hardwareConcurrency || 4);
        var memory = Number(navigator.deviceMemory || 4);
        var viewportWidth = Math.min(window.innerWidth || 1024, screen.width || 1024);
        var deviceDpr = window.devicePixelRatio || 1;
        var constrainedDevice = memory > 0 && memory <= 2;
        var weakCpuAndMemory = cores <= 2 && memory <= 3;
        var lowEnd = window.AdaAcaraLiteMode === true || constrainedDevice || weakCpuAndMemory;

        return {
            lowEnd: lowEnd,
            safeDpr: lowEnd ? Math.min(deviceDpr, 1.25) : (viewportWidth <= 768 ? Math.min(deviceDpr, 1.5) : Math.min(deviceDpr, 2)),
            rootMargin: lowEnd ? '180px 0px 260px 0px' : '360px 0px 520px 0px'
        };
    })();
    var fontLoadCache = {};

        var aaRawFontLoadCache = {};

    function aaSleep(ms) {
        return new Promise(function (resolve) {
            window.setTimeout(resolve, ms);
        });
    }

    function aaCleanFontFamily(fontFamily) {
        return String(fontFamily || 'Inter')
            .replace(/["']/g, '')
            .trim() || 'Inter';
    }

   function aaCollectFontVariantsFromRawObject(object, variants) {
    if (!object || typeof object !== 'object') return;

    if (object.fontFamily) {
        variants.push({
            family: aaCleanFontFamily(object.fontFamily),
            weight: object.fontWeight || '400',
            style: object.fontStyle || 'normal'
        });
    }

    if (object.styles && typeof object.styles === 'object') {
        Object.keys(object.styles).forEach(function(lineKey) {
            var line = object.styles[lineKey];
            if (!line || typeof line !== 'object') return;

            Object.keys(line).forEach(function(charKey) {
                var style = line[charKey];
                if (!style || typeof style !== 'object') return;

                variants.push({
                    family: aaCleanFontFamily(style.fontFamily || object.fontFamily),
                    weight: style.fontWeight || object.fontWeight || '400',
                    style: style.fontStyle || object.fontStyle || 'normal'
                });
            });
        });
    }

    if (Array.isArray(object.objects)) {
        object.objects.forEach(function(child) {
            aaCollectFontVariantsFromRawObject(child, variants);
        });
    }
}

function aaCollectFontVariantsFromPageData(pageData) {
    var variants = [];

    variants.push({
        family: 'Inter',
        weight: '400',
        style: 'normal'
    });

    if (pageData && pageData.fontFamily) {
        variants.push({
            family: aaCleanFontFamily(pageData.fontFamily),
            weight: pageData.fontWeight || '400',
            style: pageData.fontStyle || 'normal'
        });
    }

    if (pageData && Array.isArray(pageData.objects)) {
        pageData.objects.forEach(function(object) {
            aaCollectFontVariantsFromRawObject(object, variants);
        });
    }

    var seen = {};
    return variants.filter(function(item) {
        var weight = String(item.weight || '400').toLowerCase() === 'bold' ? '700' : String(item.weight || '400');
        var style = String(item.style || 'normal').toLowerCase() === 'italic' ? 'italic' : 'normal';

        if (!/^[1-9]00$/.test(weight)) {
            weight = Number(weight) >= 600 ? '700' : '400';
        }

        item.weight = weight;
        item.style = style;

        var key = item.family + '|' + item.weight + '|' + item.style;
        if (seen[key]) return false;
        seen[key] = true;
        return true;
    });
}

    function aaCollectFontsFromPageData(pageData) {
        var fonts = new Set();

        fonts.add('Inter');

        if (pageData && pageData.fontFamily) {
            fonts.add(aaCleanFontFamily(pageData.fontFamily));
        }

        if (pageData && Array.isArray(pageData.objects)) {
            pageData.objects.forEach(function (object) {
                aaCollectFontsFromRawObject(object, fonts);
            });
        }

        if (pageData && pageData.canvas && Array.isArray(pageData.canvas.objects)) {
            pageData.canvas.objects.forEach(function (object) {
                aaCollectFontsFromRawObject(object, fonts);
            });
        }

        return Array.from(fonts).filter(Boolean);
    }

    function aaClearFabricFontCache() {
        if (!window.fabric) return;

        try {
            if (fabric.charWidthsCache) {
                fabric.charWidthsCache = {};
            }

            if (fabric.Text && fabric.Text.charWidthsCache) {
                fabric.Text.charWidthsCache = {};
            }
        } catch (error) {
            console.warn('[AA Font Stable] clear cache failed', error);
        }
    }

    function aaIsScriptFont(fontFamily) {
        var font = aaCleanFontFamily(fontFamily).toLowerCase();

        return font.indexOf('script') !== -1 ||
            font.indexOf('vibes') !== -1 ||
            font.indexOf('allura') !== -1 ||
            font.indexOf('dancing') !== -1 ||
            font.indexOf('parisienne') !== -1 ||
            font.indexOf('tangerine') !== -1 ||
            font.indexOf('nautigal') !== -1 ||
            font.indexOf('windsong') !== -1 ||
            font.indexOf('ephesis') !== -1 ||
            font.indexOf('italia') !== -1 ||
            font.indexOf('brush') !== -1 ||
            font.indexOf('lavishly') !== -1 ||
            font.indexOf('culpa') !== -1 ||
            font.indexOf('fleur') !== -1 ||
            font.indexOf('bonheur') !== -1 ||
            font.indexOf('monsieur') !== -1;
    }

    function aaPrimeFontVariant(family, weight, style) {
        if (!document.fonts || typeof document.fonts.load !== 'function') {
            return Promise.resolve(null);
        }

        family = aaCleanFontFamily(family);
        weight = String(weight || '400').toLowerCase() === 'bold' ? '700' : String(weight || '400');
        style = String(style || 'normal').toLowerCase() === 'italic' ? 'italic' : 'normal';

        if (!/^[1-9]00$/.test(weight)) {
            weight = Number(weight) >= 600 ? '700' : '400';
        }

        var key = family + '|' + weight + '|' + style;

        if (aaRawFontLoadCache[key]) {
            return aaRawFontLoadCache[key];
        }

        aaRawFontLoadCache[key] = document.fonts
            .load(style + ' ' + weight + ' 64px "' + family.replace(/"/g, '') + '"')
            .catch(function () {
                return null;
            });

        return aaRawFontLoadCache[key];
    }

    function aaWaitFontsBeforeJsonLoad(pageData) {
        if (!document.fonts || typeof document.fonts.load !== 'function') {
            return aaSleep(350);
        }

        var variants = aaCollectFontVariantsFromPageData(pageData);

        return document.fonts.ready
            .catch(function () {
                return null;
            })
            .then(function () {
                return Promise.all(variants.map(function(variant) {
                return aaPrimeFontVariant(variant.family, variant.weight, variant.style);
            }));
            })
            .then(function () {
                return document.fonts.ready.catch(function () {
                    return null;
                });
            })
            .then(function () {
                aaClearFabricFontCache();

                // Delay kecil penting untuk font handwritten di Safari / Android lama.
                return aaSleep(260);
            })
            .catch(function (error) {
                console.warn('[AA Font Stable] wait fonts failed', error);
                return aaSleep(420);
            });
    }

    function aaPublicPageHidden() {
        return document.hidden === true || document.visibilityState === 'hidden';
    }

    function aaWhenPublicPageVisible(callback) {
        if (!aaPublicPageHidden()) {
            callback();
            return;
        }

        var resume = function () {
            if (aaPublicPageHidden()) return;
            document.removeEventListener('visibilitychange', resume);
            callback();
        };

        document.addEventListener('visibilitychange', resume);
    }

    // --- Viewport visibility helpers untuk pause loop animation saat keluar layar ---
    function aaIsSectionVisible(canvas) {
        return canvas.__aaSectionVisible !== false;
    }

    function aaCanRunLoop(canvas) {
        return !aaPublicPageHidden() && aaIsSectionVisible(canvas);
    }

    function aaWhenLoopReady(canvas, callback) {
        if (aaPublicPageHidden()) {
            aaWhenPublicPageVisible(function () { aaWhenLoopReady(canvas, callback); });
            return;
        }
        if (!aaIsSectionVisible(canvas)) {
            window.setTimeout(function () { aaWhenLoopReady(canvas, callback); }, 400);
            return;
        }
        callback();
    }
    // ---------------------------------------------------------------------------------

    function aaStabilizeTextObject(object) {
        if (!object) return;

        if (isTextObject(object)) {
            object.dirty = true;

            if (aaIsScriptFont(object.fontFamily)) {
                object.objectCaching = false;
                object.noScaleCache = true;
                object.splitByGrapheme = false;
            }

            if (typeof object.initDimensions === 'function') {
                object.initDimensions();
            }

            if (typeof object.setCoords === 'function') {
                object.setCoords();
            }
        }

        if (object && typeof object.getObjects === 'function') {
            object.getObjects().forEach(aaStabilizeTextObject);
        }
    }

    function aaStabilizeCanvasText(canvas) {
        if (!canvas || !canvas.getObjects) return;

        aaClearFabricFontCache();

        canvas.getObjects().forEach(function (object) {
            aaStabilizeTextObject(object);
        });

        if (typeof canvas.requestRenderAll === 'function') {
            canvas.requestRenderAll();
        } else if (typeof canvas.renderAll === 'function') {
            canvas.renderAll();
        }
    }

    function loadFabric(callback) {
        if (window.fabric) {
            callback();
            return;
        }

        var existing = document.querySelector('script[src*="fabric.min.js"]');
        if (existing) {
            existing.addEventListener('load', function() { if (window.fabric) callback(); });
            return;
        }

        var script = document.createElement('script');
        script.src = 'https://adaacara.com/assets/js/fabric.min.js';
        script.async = true;
        script.onload = callback;
        document.head.appendChild(script);
    }

    function nextFrame(callback) {
        if (window.requestAnimationFrame) {
            requestAnimationFrame(callback);
            return;
        }

        window.setTimeout(callback, 16);
    }

    function pagesFromData(data) {
        return Array.isArray(data.pages) && data.pages.length ? data.pages : [data];
    }

    function isRendererOverlayObject(object) {
        return isGuestbookControlObject(object) || isInteractiveObject(object);
    }

    function sanitizeObject(object) {
        if (!object || typeof object !== 'object') return object;

        if (object.visible === false) {
            if (isRendererOverlayObject(object)) {
                object.visible = true;
            } else {
                object.__aaSkipObject = true;
                return object;
            }
        }

        var isTextObj = isTextObject(object);
        if (isTextObj) {
            object.objectCaching = false;
            delete object.clipPath;
        }

        if (perf.lowEnd) {
            if (!isTextObj && object.shadow) object.shadow = null;
            if (!object.aaImageEffectPreset && Array.isArray(object.filters)) object.filters = [];
            if (!isTextObj && object.objectCaching !== false) object.objectCaching = true;
            object.noScaleCache = true;
        }

        Object.keys(object).forEach(function (key) {
            if (key === 'textBaseline' && object[key] === 'alphabetical') {
                object[key] = 'alphabetic';
                return;
            }

            if (object[key] && typeof object[key] === 'object') {
                sanitizeObject(object[key]);
            }
        });

        if (Array.isArray(object)) {
            object.forEach(sanitizeObject);
        }

        return object;
    }

    function normalizeUrl(url) {
        url = String(url || '').trim();
        if (!url) return '';
        if (/^(https?:|mailto:|tel:|sms:|whatsapp:)/i.test(url)) return url;
        return 'https://' + url.replace(/^\/+/, '');
    }

    function isTextObject(object) {
        return object && ['i-text', 'textbox', 'text'].indexOf(object.type) !== -1;
    }

    function walkObjects(objects, callback) {
        (objects || []).forEach(function (object) {
            callback(object);
            if (object && typeof object.getObjects === 'function') {
                walkObjects(object.getObjects(), callback);
            }
        });
    }

    function normalizeFontFamily(fontFamily) {
        return String(fontFamily || 'Inter').replace(/^["']|["']$/g, '').trim() || 'Inter';
    }

    function getPublicGuestName() {
        var params = new URLSearchParams(window.location.search || '');
        var value = params.get('to') || params.get('tamu') || params.get('invite') || params.get('guest') || '';
        value = String(value || '').replace(/\+/g, ' ').trim();
        return value || 'Tamu Undangan';
    }

    function normalizeTextEffectObject(object) {
        if (!isTextObject(object) || typeof object.set !== 'function') return;

        if (object.strokeWidth && object.stroke) {
            object.set('paintFirst', object.paintFirst || 'stroke');
            object.aaTextEffectOutlineColor = object.aaTextEffectOutlineColor || object.stroke;
        } else if (!object.strokeWidth) {
            object.set({
                stroke: null,
                strokeWidth: 0
            });
        }

        if (object.shadow && !(object.shadow instanceof fabric.Shadow)) {
            object.set('shadow', new fabric.Shadow(object.shadow));
        }

        if (object.shadow && object.shadow.color) {
            object.aaTextEffectShadowColor = object.aaTextEffectShadowColor || object.shadow.color;
        }

        if (object.charSpacing != null) {
            object.set('charSpacing', Math.max(-100, Math.min(800, Math.round(Number(object.charSpacing) || 0))));
        }

        if (object.lineHeight != null) {
            object.set('lineHeight', Math.max(.8, Math.min(2.4, Number(object.lineHeight) || 1.14)));
        }
    }

    function renderGuestNameTemplate(template, guestName) {
        template = String(template || 'Kepada Yth.\n{{guest_name}}');
        if (!/\{\{\s*guest_name\s*\}\}/i.test(template)) {
            template = template.replace(/Nama Tamu|Tamu Undangan/gi, '{{guest_name}}');
        }
        return template.replace(/\{\{\s*guest_name\s*\}\}/gi, guestName || 'Tamu Undangan');
    }

    function isGuestNamePlaceholderText(value) {
        var text = String(value || '').trim();
        var normalized = text.replace(/\s+/g, ' ');
        return /\{\{\s*guest_name\s*\}\}/i.test(text) ||
            /\bNama\s+Tamu\b/i.test(text) ||
            /^(Kepada\s+(Yth\.?|Yang\s+Terhormat)\s*)?Tamu\s+Undangan$/i.test(normalized);
    }

    function applyGuestNameObjects(canvas) {
        if (!canvas || !canvas.getObjects) return;
        var guestName = getPublicGuestName();
        var decorativeNames = [
            'guest-name-glass-card',
            'guest-name-inner-glow',
            'guest-name-edge-reflection',
            'guest-name-top-sheen',
            'guest-name-close-circle',
            'guest-name-close-text'
        ];
        walkObjects(canvas.getObjects(), function (object) {
            var isGuestNameCandidate = object && (object.isGuestName === true || object.customType === 'guest_name' ||
                object.dynamicKey === 'guest_name' || (isTextObject(object) && isGuestNamePlaceholderText(object.text)));
            if (!isGuestNameCandidate) return;
            if (typeof object.set === 'function') {
                object.set({
                    showCloseButton: false,
                    glassCard: false
                });
            } else {
                object.showCloseButton = false;
                object.glassCard = false;
            }
            if (typeof object.getObjects === 'function' && typeof object.remove === 'function') {
                object.getObjects().slice().forEach(function (child) {
                    if (decorativeNames.indexOf(child && child.name) !== -1) {
                        object.remove(child);
                    }
                });
            }
            var currentText = object.templateText || object.text || '';
            if (!currentText && object && typeof object.getObjects === 'function') {
                var currentChildren = object.getObjects();
                var currentTarget = currentChildren.find(function (child) { return child.name === 'guest-name-text'; }) ||
                    currentChildren.find(isTextObject);
                currentText = currentTarget ? currentTarget.text : '';
            }
            var nextText = renderGuestNameTemplate(currentText, guestName);
            var target = object;
            if (object && typeof object.getObjects === 'function') {
                var children = object.getObjects();
                target = children.find(function (child) { return child.name === 'guest-name-text'; }) ||
                    children.find(isTextObject) || object;
            }
            if (typeof target.set === 'function') {
                target.set('text', nextText);
            } else {
                target.text = nextText;
            }
            target.dirty = true;
            if (typeof target.initDimensions === 'function') target.initDimensions();
            object.dirty = true;
            if (typeof object.setCoords === 'function') object.setCoords();
        });
    }

    function loadFontsForCanvas(canvas) {
        if (!document.fonts || !document.fonts.load || !canvas || !canvas.getObjects) {
            return Promise.resolve();
        }

        var variants = [];
        var normalizeWeight = function (weight) {
            weight = String(weight || '400').toLowerCase() === 'bold' ? '700' : String(weight || '400');
            if (!/^[1-9]00$/.test(weight)) return Number(weight) >= 600 ? '700' : '400';
            return weight;
        };
        var addVariant = function (family, weight, style) {
            family = normalizeFontFamily(family);
            weight = normalizeWeight(weight);
            style = String(style || 'normal').toLowerCase() === 'italic' ? 'italic' : 'normal';
            var key = family + '|' + weight + '|' + style;
            if (!variants.some(function (item) { return item.key === key; })) {
                variants.push({ key: key, family: family, weight: weight, style: style });
            }
        };
        walkObjects(canvas.getObjects(), function (object) {
            if (!isTextObject(object)) return;
            addVariant(object.fontFamily, object.fontWeight, object.fontStyle);
        });
        addVariant('Inter', '400', 'normal');

        return Promise.all(variants.map(function (variant) {
            if (fontLoadCache[variant.key]) return fontLoadCache[variant.key];
            fontLoadCache[variant.key] = document.fonts.load(variant.style + ' ' + variant.weight + ' 32px "' + variant.family.replace(/"/g, '') + '"').catch(function () { return null; });
            return fontLoadCache[variant.key];
        })).then(function () {
            return document.fonts.ready;
        }).catch(function () { return null; });
    }

    function recalculateTextObjects(canvas) {
        if (!canvas || !canvas.getObjects) return;
        if (window.fabric) {
            if (fabric.charWidthsCache) fabric.charWidthsCache = {};
            if (fabric.Text && fabric.Text.charWidthsCache) fabric.Text.charWidthsCache = {};
        }
        walkObjects(canvas.getObjects(), function (object) {
            if (object.type === 'image' && object.borderRadius && object.clipPath && (object.clipPath.rx || object.clipPath.ry)) {
                object.clipPath = null;
                object.dirty = true;
                object.setCoords();
            }
            if (!isTextObject(object)) return;
            if (object.clipPath) {
                object.clipPath = null;
            }
            object.dirty = true;
            if (typeof object.initDimensions === 'function') object.initDimensions();
            object.setCoords();
        });
    }

    function stabilizeAnimatedTextObject(object) {
    if (!isTextObject(object)) return;

    object.objectCaching = false;
    object.noScaleCache = true;
    object.dirty = true;

    if (object.textBaseline === 'alphabetical') {
        object.textBaseline = 'alphabetic';
    }

    if (!object.__aaStableTextBox) {
        object.__aaStableTextBox = {
            text: object.text || '',
            left: object.left,
            top: object.top,
            width: object.width,
            height: object.height,
            scaleX: object.scaleX || 1,
            scaleY: object.scaleY || 1,
            fontSize: object.fontSize,
            lineHeight: object.lineHeight,
            charSpacing: object.charSpacing || 0,
            opacity: object.opacity == null ? 1 : object.opacity
        };
    }

    if (typeof object.initDimensions === 'function') {
        object.initDimensions();
    }

    if (typeof object.setCoords === 'function') {
        object.setCoords();
    }
}

function stabilizeAnimatedTextBeforeAnimation(canvas) {
    if (!canvas || !canvas.getObjects) return;

    walkObjects(canvas.getObjects(), function(object) {
        var hasObjectAnimation = isAnimationObject(object);
        var hasTextAnimation = isTextAnimationObject(object);

        if (isTextObject(object) && (hasObjectAnimation || hasTextAnimation)) {
            stabilizeAnimatedTextObject(object);
        }
    });

    canvas.requestRenderAll();
}

    function installRoundedImageRenderer() {
        if (!window.fabric || fabric.Image.prototype.__aaRoundedRendererInstalled) return;
        var originalRender = fabric.Image.prototype._render;
        var drawImagePath = function (ctx, width, height, radius) {
            var r = Math.min(Math.max(0, Number(radius) || 0), width / 2, height / 2);
            var x = -width / 2;
            var y = -height / 2;
            ctx.beginPath();
            if (!r) {
                ctx.rect(x, y, width, height);
                return;
            }
            ctx.moveTo(x + r, y);
            ctx.lineTo(x + width - r, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + r);
            ctx.lineTo(x + width, y + height - r);
            ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
            ctx.lineTo(x + r, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - r);
            ctx.lineTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.closePath();
        };
        var drawImageStroke = function (ctx, image, width, height, radius) {
            var strokeWidth = Math.max(0, Number(image.strokeWidth) || 0);
            if (!strokeWidth || !image.stroke || image.stroke === 'transparent') return;
            ctx.save();
            drawImagePath(ctx, width, height, radius);
            ctx.lineWidth = strokeWidth;
            ctx.strokeStyle = image.stroke;
            ctx.lineJoin = 'round';
            ctx.lineCap = image.imageStrokeStyle === 'dotted' ? 'round' : 'butt';
            if (Array.isArray(image.strokeDashArray)) {
                ctx.setLineDash(image.strokeDashArray);
            }
            ctx.stroke();
            ctx.restore();
        };
        var imageEffectCanvasFilter = function (image) {
            var preset = String((image && image.aaImageEffectPreset) || 'none');
            if (!preset || preset === 'none' || preset === 'opacity' || preset === 'shadow') return '';
            if (Array.isArray(image.filters) && image.filters.length) return '';
            if (preset === 'brightness') return 'brightness(1.16)';
            if (preset === 'contrast') return 'contrast(1.22)';
            if (preset === 'saturation') return 'saturate(1.38)';
            if (preset === 'grayscale') return 'grayscale(1)';
            if (preset === 'sepia') return 'sepia(1)';
            if (preset === 'blur') return 'blur(2px)';
            if (preset === 'sharpen') return 'contrast(1.28) saturate(1.12)';
            if (preset === 'vintage') return 'sepia(.55) contrast(1.08) saturate(.82)';
            if (preset === 'soft-wedding') return 'brightness(1.08) contrast(.96) saturate(1.18) sepia(.08)';
            if (preset === 'clean-bright') return 'brightness(1.14) contrast(1.08) saturate(1.08)';
            if (preset === 'warm-editorial') return 'sepia(.18) brightness(1.06) contrast(1.12) saturate(1.14)';
            if (preset === 'film-matte') return 'sepia(.2) contrast(.92) saturate(.78) brightness(1.04)';
            if (preset === 'pastel-bloom') return 'brightness(1.1) contrast(.94) saturate(1.32) hue-rotate(-6deg)';
            if (preset === 'moody-luxe') return 'brightness(.88) contrast(1.22) saturate(.9) sepia(.08)';
            if (preset === 'classic-bw') return 'grayscale(1) contrast(1.18) brightness(1.04)';
            if (preset === 'dreamy-soft') return 'brightness(1.12) contrast(.9) saturate(1.12) blur(.75px)';
            if (preset === 'recolor-white') return 'grayscale(.35) brightness(1.34) contrast(.86) saturate(.68)';
            if (preset === 'recolor-black') return 'grayscale(1) brightness(.72) contrast(1.28)';
            if (preset === 'recolor-gold') return 'sepia(.55) saturate(1.45) hue-rotate(4deg) brightness(1.08) contrast(1.04)';
            if (preset === 'recolor-teal') return 'sepia(.18) saturate(1.35) hue-rotate(135deg) brightness(.96) contrast(1.06)';
            if (preset === 'recolor-rose') return 'sepia(.22) saturate(1.35) hue-rotate(300deg) brightness(1.04) contrast(.98)';
            if (preset === 'recolor-slate') return 'grayscale(.65) sepia(.12) saturate(.7) hue-rotate(170deg) brightness(.92) contrast(1.08)';
            if (preset === 'remove-color') return 'saturate(.2) contrast(1.12)';
            return '';
        };
        var renderImageWithCanvasEffect = function (image, ctx) {
            var filter = imageEffectCanvasFilter(image);
            if (!filter) {
                originalRender.call(image, ctx);
                return;
            }
            var previousFilter = ctx.filter;
            ctx.filter = filter;
            originalRender.call(image, ctx);
            ctx.filter = previousFilter;
        };
        fabric.Image.prototype._render = function (ctx) {
            var radius = Math.max(0, Number(this.borderRadius) || 0);
            var width = Math.max(1, this.width || 1);
            var height = Math.max(1, this.height || 1);
            if (radius) {
                ctx.save();
                drawImagePath(ctx, width, height, radius);
                ctx.clip();
                renderImageWithCanvasEffect(this, ctx);
                ctx.restore();
            } else {
                renderImageWithCanvasEffect(this, ctx);
            }
            drawImageStroke(ctx, this, width, height, radius);
        };
        fabric.Image.prototype.__aaRoundedRendererInstalled = true;
    }

    function showCopyToast(message) {
        var toast = document.createElement('div');
        toast.textContent = message || 'Tersalin';
        toast.style.cssText = 'position:fixed;left:50%;bottom:24px;z-index:99999;transform:translateX(-50%);border-radius:999px;background:rgba(17,24,39,.94);color:#fff;padding:10px 16px;font:700 13px Inter,Arial,sans-serif;box-shadow:0 14px 36px rgba(15,23,42,.24);pointer-events:none;';
        document.body.appendChild(toast);
        window.setTimeout(function () { toast.remove(); }, 1400);
    }

    function openGalleryLightbox(url) {
        url = String(url || '').trim();
        if (!url) return;
        var lightbox = document.querySelector('.aa-fabric-lightbox');
        if (!lightbox) {
            lightbox = document.createElement('div');
            lightbox.className = 'aa-fabric-lightbox';
            lightbox.innerHTML = '<button type="button">Close</button><img src="" alt="Preview gallery">';
            document.body.appendChild(lightbox);
            lightbox.addEventListener('click', function (event) {
                if (event.target === lightbox || event.target.tagName === 'BUTTON') lightbox.classList.remove('is-open');
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') lightbox.classList.remove('is-open');
            });
        }
        lightbox.querySelector('img').src = url;
        lightbox.classList.add('is-open');
    }
    window.openGalleryLightbox = openGalleryLightbox;

    function bindGalleryLightboxTrigger(el, getUrl) {
        if (!el || typeof getUrl !== 'function' || el.__aaGalleryTapBound) return;
        el.__aaGalleryTapBound = true;

        var startX = 0;
        var startY = 0;
        var startTime = 0;
        var recentOpenUntil = 0;
        var TAP_MOVE_LIMIT = 14;
        var TAP_TIME_LIMIT = 850;

        function eventPoint(event) {
            var point = event.changedTouches && event.changedTouches[0] || event.touches && event.touches[0] || event;
            return {
                x: Number(point.clientX) || 0,
                y: Number(point.clientY) || 0
            };
        }

        function rememberStart(event) {
            var point = eventPoint(event);
            startX = point.x;
            startY = point.y;
            startTime = Date.now();
        }

        function isTap(event) {
            var point = eventPoint(event);
            return Math.abs(point.x - startX) <= TAP_MOVE_LIMIT &&
                Math.abs(point.y - startY) <= TAP_MOVE_LIMIT &&
                (Date.now() - startTime) <= TAP_TIME_LIMIT;
        }

        function openFromTap(event) {
            if (!isTap(event)) return;
            var url = getUrl();
            if (!url) return;
            recentOpenUntil = Date.now() + 700;
            if (event.cancelable) event.preventDefault();
            event.stopPropagation();
            openGalleryLightbox(url);
        }

        if (window.PointerEvent) {
            el.addEventListener('pointerdown', rememberStart, { passive: true });
            el.addEventListener('pointerup', openFromTap);
        } else {
            el.addEventListener('touchstart', rememberStart, { passive: true });
            el.addEventListener('touchend', openFromTap);
        }

        el.addEventListener('click', function(event) {
            if (Date.now() < recentOpenUntil) {
                if (event.cancelable) event.preventDefault();
                event.stopPropagation();
                return;
            }
            var url = getUrl();
            if (!url) return;
            openGalleryLightbox(url);
        });
    }
    window.aaBindGalleryLightboxTrigger = bindGalleryLightboxTrigger;

    function copyToClipboard(value, message) {
        value = String(value || '');
        if (!value) return;

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(function () {
                showCopyToast(message);
            }).catch(function () {
                showCopyToast('Tidak bisa copy otomatis');
            });
            return;
        }

        var input = document.createElement('textarea');
        input.value = value;
        input.setAttribute('readonly', 'readonly');
        input.style.cssText = 'position:fixed;left:-9999px;top:0;font-size:16px;';
        document.body.appendChild(input);
        input.select();
        input.setSelectionRange(0, input.value.length);
        try {
            document.execCommand('copy');
            showCopyToast(message);
        } catch (error) {
            showCopyToast('Tidak bisa copy otomatis');
        }
        input.remove();
    }

    // function renderPage(pageData, index) {
    //     if (!pageData || pageData.__aaRendered) return;
    //     pageData.__aaRendered = true;

    //     if (Array.isArray(pageData.objects)) {
    //         pageData.objects.forEach(sanitizeObject);
    //         pageData.objects = pageData.objects.filter(function (object) {
    //             return object && object.__aaSkipObject !== true;
    //         });
    //     }

    //     var canvasEl = document.getElementById('aaFabricPublicCanvas' + index);
    //     if (!canvasEl || !window.fabric) return;
    //     installRoundedImageRenderer();
    //     var artboardEl = canvasEl.closest('.aa-fabric-artboard');
    //     if (artboardEl) artboardEl.classList.add('is-rendering');

    //     var artboard = pageData.artboard || {};
    //     var width = artboard.width || 1080;
    //     var height = artboard.height || 1920;

    //     fabric.devicePixelRatio = perf.safeDpr;
    //     var canvas = new fabric.StaticCanvas(canvasEl, {
    //         width: width,
    //         height: height,
    //         renderOnAddRemove: false,
    //         enableRetinaScaling: true,
    //         skipOffscreen: true
    //     });

    //     canvasEl.__aaFabricCanvas = canvas;
    //     canvasEl.__aaFabricOriginalWidth = width;
    //     canvasEl.__aaFabricOriginalHeight = height;
    //     canvasEl.__aaFabricScale = 1;
    //     canvasEl.setAttribute('data-aa-rendered', 'true');

    //     function resizeCanvas() {
    //         canvas.setDimensions({ width: width, height: height });
    //         canvasEl.style.width = '100%';
    //         canvasEl.style.height = '100%';
    //         if (canvas.wrapperEl) {
    //             canvas.wrapperEl.style.width = '100%';
    //             canvas.wrapperEl.style.height = '100%';
    //         }
    //         canvas.calcOffset();
    //         nextFrame(function () {
    //             canvas.requestRenderAll();
    //         });
    //     }

    //     canvas.loadFromJSON(pageData, function () {
    //         canvas.backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';
    //         canvas.getObjects().forEach(function (object) {
    //             object.selectable = false;
    //             object.evented = false;
    //             if (perf.lowEnd) {
    //                 if (!isTextObject(object) && object.shadow) object.shadow = null;
    //                 if (Array.isArray(object.filters)) object.filters = [];
    //                 object.noScaleCache = true;
    //             }
    //             normalizeTextEffectObject(object);
    //         });
    //         walkObjects(canvas.getObjects(), normalizeTextEffectObject);
    //         prepareScrollAnimatedObjects(canvas);
    //         loadFontsForCanvas(canvas).then(function () {
    //             recalculateTextObjects(canvas);
    //             resizeCanvas();
    //             canvas.renderAll();
    //             var finalize = function () {
    //                 applyGuestNameObjects(canvas);
    //                 recalculateTextObjects(canvas);
    //                 resizeCanvas();
    //                 canvas.renderAll();
    //                 setupActionHotspots(canvasEl, canvas);
    //                 setupHybridOverlays(canvasEl, canvas);
    //                 setupScrollAnimations(canvasEl, canvas);
    //                 if (artboardEl) artboardEl.classList.remove('is-rendering');
    //             };
    //             if (window.requestAnimationFrame) {
    //                 requestAnimationFrame(function () {
    //                     requestAnimationFrame(function () {
    //                         requestAnimationFrame(finalize);
    //                     });
    //                 });
    //             } else {
    //                 window.setTimeout(finalize, 80);
    //             }
    //             window.addEventListener('resize', resizeCanvas);
    //         });
    //     });

    // }

        function aaDebounce(fn, delay) {
            var timer = null;

            return function () {
                var args = arguments;
                var ctx = this;

                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    fn.apply(ctx, args);
                }, delay || 160);
            };
        }

        function renderPage(pageData, index) {
        if (!pageData || pageData.__aaRendered) return;
        pageData.__aaRendered = true;

        if (Array.isArray(pageData.objects)) {
            pageData.objects.forEach(sanitizeObject);
            pageData.objects = pageData.objects.filter(function (object) {
                return object && object.__aaSkipObject !== true;
            });
        }

        // Coba render menggunakan Hybrid DOM/SVG/WebGL renderer terlebih dahulu
        try {
            if (window.tryRenderHybrid && window.tryRenderHybrid(pageData, index)) {
                return;
            }
        } catch (error) {
            console.warn('Hybrid rendering failed, falling back to Fabric.js:', error);
        }

        var guestbookSourceObjects = Array.isArray(pageData.objects)
            ? pageData.objects.filter(isGuestbookControlObject)
            : [];
        var canvasPageData = pageData;
        if (guestbookSourceObjects.length) {
            canvasPageData = JSON.parse(JSON.stringify(pageData));
            canvasPageData.objects = (canvasPageData.objects || []).filter(function (object) {
                return !isGuestbookControlObject(object);
            });
        }

        var canvasEl = document.getElementById('aaFabricPublicCanvas' + index);
        if (!canvasEl || !window.fabric) return;

        installRoundedImageRenderer();

        var artboardEl = canvasEl.closest('.aa-fabric-artboard');
        if (artboardEl) artboardEl.classList.add('is-rendering');

        var artboard = pageData.artboard || {};
        var width = artboard.width || 1080;
        var height = artboard.height || 1920;

        var canvas = null;
        var resizeHandlerInstalled = false;

        function startCanvasRender() {
            aaClearFabricFontCache();

            fabric.devicePixelRatio = perf.safeDpr;
            fabric.Object.prototype.statefulCache = false;

            canvas = new fabric.StaticCanvas(canvasEl, {
                width: width,
                height: height,
                renderOnAddRemove: false,
                enableRetinaScaling: true,
                skipOffscreen: true,
                skipOffscreen: true,
                statefulCache: false
            });

            canvasEl.__aaFabricCanvas = canvas;
            canvasEl.__aaFabricOriginalWidth = width;
            canvasEl.__aaFabricOriginalHeight = height;
            canvasEl.__aaFabricScale = 1;
            canvasEl.setAttribute('data-aa-rendered', 'true');

            function resizeCanvas(skipRender) {
                canvas.setDimensions({ width: width, height: height });
                canvasEl.style.width = '100%';
                canvasEl.style.height = '100%';

                if (canvas.wrapperEl) {
                    canvas.wrapperEl.style.width = '100%';
                    canvas.wrapperEl.style.height = '100%';
                }

                canvas.calcOffset();

                nextFrame(function () {
                    if (typeof canvas.requestRenderAll === 'function') {
                        canvas.requestRenderAll();
                    } else {
                        canvas.renderAll();
                    }
                });
                if (skipRender !== true) {
                    nextFrame(function () {
                        if (typeof canvas.requestRenderAll === 'function') {
                            canvas.requestRenderAll();
                        } else {
                            canvas.renderAll();
                        }
                    });
                }
            }

            function sanitizeFabricObjectSafe(obj) {
    if (!obj || typeof obj !== 'object') return obj;

    if (obj.textBaseline === 'alphabetical') {
        obj.textBaseline = 'alphabetic';
    }

    if (obj.styles && typeof obj.styles === 'object') {
        Object.keys(obj.styles).forEach(function (lineKey) {
            var line = obj.styles[lineKey];
            if (!line || typeof line !== 'object') return;

            Object.keys(line).forEach(function (charKey) {
                var style = line[charKey];
                if (style && style.textBaseline === 'alphabetical') {
                    style.textBaseline = 'alphabetic';
                }
            });
        });
    }

    if (Array.isArray(obj.objects)) {
        obj.objects.forEach(sanitizeFabricObjectSafe);
    }

    return obj;
}

function sanitizeFabricJsonSafe(json) {
    if (!json || typeof json !== 'object') return json;

    if (Array.isArray(json.objects)) {
        json.objects.forEach(sanitizeFabricObjectSafe);
    }

    if (Array.isArray(json.pages)) {
        json.pages.forEach(function (page) {
            if (page && Array.isArray(page.objects)) {
                page.objects.forEach(sanitizeFabricObjectSafe);
            }
        });
    }

    return json;
}

sanitizeFabricJsonSafe(canvasPageData); // Cukup proses halaman yang sedang dirender, hentikan O(N^2) array mapping

            canvas.loadFromJSON(canvasPageData, function () {
    canvas.backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';

    var objects = canvas.getObjects();

    objects.forEach(function (object) {
        sanitizeFabricObjectSafe(object);

        object.selectable = false;
        object.evented = false;

        if (object.textBaseline === 'alphabetical') {
            object.textBaseline = 'alphabetic';
        }

        if (perf.lowEnd) {
            if (!isTextObject(object) && object.shadow) object.shadow = null;
            if (!object.aaImageEffectPreset && Array.isArray(object.filters)) object.filters = [];
            object.noScaleCache = true;
        }

        object.dirty = true;
    });

    // Pecah eksekusi JS yang sangat berat agar tidak menahan Main Thread (Yielding)
    var processAfterLoad = function () {
        walkObjects(objects, normalizeTextEffectObject);
        loadFontsForCanvas(canvas).then(function () {
        resizeCanvas(true);

        var finalizeStep3 = function () {
            setupAnimatedGifBackground(canvasEl, canvas);
            setupAnimatedGifOverlay(canvasEl, canvas);
            setupActionHotspots(canvasEl, canvas);
            setupHybridOverlays(canvasEl, canvas, guestbookSourceObjects);
            setupScrollAnimations(canvasEl, canvas);
            setupLoopAnimationVisibilityTracking(canvasEl, canvas);

            if (typeof canvas.requestRenderAll === 'function') {
                canvas.requestRenderAll();
            } else {
                canvas.renderAll();
            }

            if (artboardEl) artboardEl.classList.remove('is-rendering');
        };

        var finalizeStep2 = function () {
            stabilizeAnimatedTextBeforeAnimation(canvas);
            prepareScrollAnimatedObjects(canvas);
            prepareTextAnimatedObjects(canvas);
            window.setTimeout(finalizeStep3, 0);
        };

        var finalize = function () {
            applyGuestNameObjects(canvas);
            recalculateTextObjects(canvas);
            window.setTimeout(finalizeStep2, 0);
        };

        if (window.requestAnimationFrame) {
            requestAnimationFrame(finalize);
        } else {
            window.setTimeout(finalize, 80);
        }

        if (!resizeHandlerInstalled) {
            resizeHandlerInstalled = true;
            window.addEventListener('resize', aaDebounce(resizeCanvas, 180));
        }
    });
    };

    if (window.requestIdleCallback) {
        window.requestIdleCallback(processAfterLoad, { timeout: 300 });
    } else {
        window.setTimeout(processAfterLoad, 10);
    }
});
        }

        // Ini bagian paling penting:
        // Font ditunggu SEBELUM Fabric loadFromJSON menghitung ukuran teks.
        aaWaitFontsBeforeJsonLoad(pageData).then(startCanvasRender).catch(startCanvasRender);
    }

    function lazyRenderPages(pages) {
        pages = (pages || []).filter(function (pageData) {
            return pageData && pageData.hidden !== true;
        });

        if (!pages.length) return;

        var rendered = {};
        var renderAt = function (index) {
            if (rendered[index]) return;
            rendered[index] = true;
            renderPage(pages[index], index);
        };

        renderAt(0);

        if (!('IntersectionObserver' in window)) {
            pages.forEach(function (_pageData, index) {
                if (index === 0) return;
                window.setTimeout(function () {
                    renderAt(index);
                }, index * 260);
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var index = Number(entry.target.getAttribute('data-aa-page-index') || 0);
                if (window.requestIdleCallback) {
                    window.requestIdleCallback(function () { renderAt(index); }, { timeout: 1000 });
                } else {
                    window.setTimeout(function () { renderAt(index); }, 20);
                }
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: perf.rootMargin,
            threshold: 0.01
        });

        pages.forEach(function (_pageData, index) {
            if (index === 0) return;
            var canvasEl = document.getElementById('aaFabricPublicCanvas' + index);
            var section = canvasEl ? canvasEl.closest('.aa-fabric-page-section') : null;
            if (!section) {
                window.setTimeout(function () {
                    renderAt(index);
                }, index * 260);
                return;
            }
            section.setAttribute('data-aa-page-index', String(index));
            observer.observe(section);
        });
    }

    function setupActionHotspots(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard) return;

        var oldLayer = artboard.querySelector('.aa-fabric-click-layer');
        if (oldLayer) oldLayer.remove();

        var layer = document.createElement('div');
        layer.className = 'aa-fabric-click-layer';
        artboard.appendChild(layer);

        var canvasWidth = canvas.getWidth() || 1;
        var canvasHeight = canvas.getHeight() || 1;

        canvas.getObjects().forEach(function (object, index) {
            if (object && (object.customType === 'gallery-photo' || object.isGalleryPhoto === true || object.galleryZoom === true)) {
                var galleryRect = object.getBoundingRect(true, true);
                var galleryHotspot = document.createElement('button');
                galleryHotspot.type = 'button';
                galleryHotspot.className = 'aa-fabric-hotspot aa-fabric-gallery-hotspot';
                galleryHotspot.style.left = (galleryRect.left / canvasWidth * 100) + '%';
                galleryHotspot.style.top = (galleryRect.top / canvasHeight * 100) + '%';
                galleryHotspot.style.width = (galleryRect.width / canvasWidth * 100) + '%';
                galleryHotspot.style.height = (galleryRect.height / canvasHeight * 100) + '%';
                galleryHotspot.setAttribute('aria-label', 'Zoom foto gallery');
                bindGalleryLightboxTrigger(galleryHotspot, function () {
                    return object.galleryImageSrc || object.src || (object._element && object._element.src) || '';
                });
                layer.appendChild(galleryHotspot);
            }

            if (!object.link && !object.copyText) return;

            var rect = object.getBoundingRect(true, true);
            var hotspot = object.link ? document.createElement('a') : document.createElement('button');
            hotspot.className = 'aa-fabric-hotspot';
            hotspot.style.left = (rect.left / canvasWidth * 100) + '%';
            hotspot.style.top = (rect.top / canvasHeight * 100) + '%';
            hotspot.style.width = (rect.width / canvasWidth * 100) + '%';
            hotspot.style.height = (rect.height / canvasHeight * 100) + '%';
            hotspot.setAttribute('aria-label', object.link ? 'Buka link' : 'Copy text');

            if (object.link) {
                hotspot.href = normalizeUrl(object.link);
                hotspot.target = '_blank';
                hotspot.rel = 'noopener';
            } else {
                hotspot.type = 'button';
                hotspot.addEventListener('click', function () {
                    copyToClipboard(object.copyText, object.copyFeedback || 'Tersalin');
                });
            }

            layer.appendChild(hotspot);
        });
    }

    function isGuestbookObject(object) {
        return object && [
            'guest-name-input',
            'guest-attendance-select',
            'guest-message-textarea',
            'guest-sticker-picker',
            'guest-submit-button',
            'guest-comment-list'
        ].indexOf(object.customType) !== -1;
    }

    function isGuestbookControlObject(object) {
        if (!isGuestbookObject(object)) return false;
        if (object.formGroupId || object.guestbookRole || object.fieldName || object.buttonText || object.stickerSource || object.maxLength || object.options) {
            return true;
        }
        var children = object && typeof object.getObjects === 'function'
            ? object.getObjects()
            : (Array.isArray(object && object.objects) ? object.objects : []);
        return children.some(function (child) {
            return child && (child.name === 'guestbook-box' || child.name === 'guestbook-text');
        });
    }

    function isInteractiveObject(object) {
        return object && ['music-player', 'scroll-next-button', 'countdown-timer', 'photo-gallery', 'youtube-video'].indexOf(object.customType) !== -1;
    }

    function getObjectAnimationName(object) {
        return String(
            (object && (object.aaAnimation || object.customAnimation || object.animationPreset || object.animation || object.animationName)) ||
            'none'
        );
    }

    function isAnimationObject(object) {
        if (!object || object.visible === false) return false;
        if (object.__aaSkipObject === true) return false;
        if (object.customType === 'background') return false;
        if (object.excludeFromAnimation === true) return false;
        if (isGuestbookControlObject(object) || isInteractiveObject(object)) return false;
        if (isAnimatedGifObject(object)) return false;
        if (isTextObject(object) && aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled) return false;
        var animationName = getObjectAnimationName(object);
        return Boolean(animationName && animationName !== 'none');
    }

    function getAnimationSortedObjects(canvas) {
        if (!canvas || !canvas.getObjects) return [];
        return canvas.getObjects()
            .filter(isAnimationObject)
            .sort(function (a, b) {
                var rectA = a.getBoundingRect(true, true);
                var rectB = b.getBoundingRect(true, true);

                if (Math.abs(rectA.top - rectB.top) > 12) {
                    return rectA.top - rectB.top;
                }

                return rectA.left - rectB.left;
            });
    }

    function objectSnapshot(object) {
        if (object.__aaAnimationOriginal) {
            return {
                left: object.__aaAnimationOriginal.left,
                top: object.__aaAnimationOriginal.top,
                opacity: object.__aaAnimationOriginal.opacity == null ? 1 : object.__aaAnimationOriginal.opacity,
                scaleX: object.__aaAnimationOriginal.scaleX || 1,
                scaleY: object.__aaAnimationOriginal.scaleY || 1,
                angle: object.__aaAnimationOriginal.angle || 0,
                shadow: object.__aaAnimationOriginal.shadow || null,
                clipPath: object.__aaAnimationOriginal.clipPath || null
            };
        }
        return {
            left: object.left,
            top: object.top,
            opacity: object.opacity == null ? 1 : object.opacity,
            scaleX: object.scaleX || 1,
            scaleY: object.scaleY || 1,
            angle: object.angle || 0,
            shadow: object.shadow || null,
            clipPath: object.clipPath || null
        };
    }

    function aaGetAnimationDuration(object, fallback) {
        var value = object && (object.aaAnimationDuration != null ? object.aaAnimationDuration : object.animationDuration);
        value = Number(value);
        var duration = isFinite(value) && value > 0 ? value : fallback;
        if (window.AdaAcaraLiteMode === true || document.documentElement.classList.contains('aa-lite-mode')) {
            return Math.max(140, Math.min(420, Math.round(duration * .55)));
        }
        return duration;
    }

    function prepareScrollAnimatedObjects(canvas) {
        canvas.__aaHasScrollAnimations = false;
        getAnimationSortedObjects(canvas).forEach(function (object) {
            canvas.__aaHasScrollAnimations = true;
            if (!object.__aaAnimationOriginal) {
                object.__aaAnimationOriginal = objectSnapshot(object);
            }
            object.set({ opacity: 0 });
            object.dirty = true;
        });
        canvas.requestRenderAll();
    }

    function aaNormalizeTextAnimationConfig(value) {
        var source = value && typeof value === 'object' ? value : {};
        var allowed = ['typewriter', 'letter-fade-up', 'letter-wave', 'word-reveal', 'text-glow', 'shine-text'];
        var type = allowed.indexOf(source.type) !== -1 ? source.type : 'none';
        var enabled = source.enabled === true && type !== 'none';
        var clamp = function (number, min, max, fallback) {
            number = Number(number);
            return isFinite(number) ? Math.max(min, Math.min(max, Math.round(number))) : fallback;
        };
        return {
            enabled: enabled,
            type: enabled ? type : 'none',
            delay: window.AdaAcaraLiteMode === true ? Math.min(clamp(source.delay, 0, 5000, 0), 220) : clamp(source.delay, 0, 5000, 0),
            duration: window.AdaAcaraLiteMode === true ? Math.min(clamp(source.duration, 200, 8000, 1200), 520) : clamp(source.duration, 200, 8000, 1200),
            stagger: window.AdaAcaraLiteMode === true ? Math.min(clamp(source.stagger, 0, 300, 40), 18) : clamp(source.stagger, 0, 300, 40),
            loop: source.loop === true || type === 'text-glow' || type === 'shine-text'
        };
    }

    function isTextAnimationObject(object) {
        if (!isTextObject(object) || object.visible === false || object.__aaSkipObject === true) return false;
        return aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled;
    }

    function getTextAnimationObjects(canvas) {
        if (!canvas || !canvas.getObjects) return [];
        return canvas.getObjects().filter(isTextAnimationObject);
    }

    function aaTextAnimationOriginal(object) {
        if (!object.__aaTextAnimationOriginal) {
            object.__aaTextAnimationOriginal = {
                text: object.text || '',
                top: object.top,
                opacity: object.opacity == null ? 1 : object.opacity,
                fill: object.fill,
                shadow: object.shadow || null,
                charSpacing: object.charSpacing || 0
            };
        }
        return object.__aaTextAnimationOriginal;
    }

    function prepareTextAnimatedObjects(canvas) {
        canvas.__aaHasTextAnimations = false;
        getTextAnimationObjects(canvas).forEach(function (object) {
            var config = aaNormalizeTextAnimationConfig(object.aaTextAnimation);
            var original = aaTextAnimationOriginal(object);
            canvas.__aaHasTextAnimations = true;
            if (['typewriter', 'letter-fade-up', 'word-reveal'].indexOf(config.type) !== -1) {
                object.set({ opacity: 0, text: original.text });
            }
            object.dirty = true;
        });
        canvas.requestRenderAll();
    }

    function aaSetTextAnimationText(object, text) {
        object.set('text', text);
        if (typeof object.initDimensions === 'function') object.initDimensions();
        object.setCoords();
        object.dirty = true;
    }

    function runTextRevealAnimation(canvas, object, config, byWord) {
        var original = aaTextAnimationOriginal(object);
        var text = String(original.text || '');
        var units = byWord ? text.split(/(\s+)/) : Array.from(text);
        if (!byWord && units.length > 180) {
            runTextRevealAnimation(canvas, object, Object.assign({}, config, { type: 'word-reveal' }), true);
            return;
        }
        var duration = Math.max(200, config.duration + Math.min(units.length, 80) * config.stagger);
        var start = null;
        var render = function () {
            if (!aaPublicPageHidden()) canvas.requestRenderAll();
        };
        object.set({ opacity: config.type === 'letter-fade-up' ? 0 : original.opacity, top: config.type === 'letter-fade-up' ? original.top + 24 : original.top });
        aaSetTextAnimationText(object, '');
        var step = function (time) {
            if (aaPublicPageHidden()) {
                aaWhenPublicPageVisible(function () {
                    requestAnimationFrame(step);
                });
                return;
            }
            if (start === null) start = time;
            var progress = Math.min(1, (time - start) / duration);
            var count = Math.min(units.length, Math.ceil(progress * units.length));
            aaSetTextAnimationText(object, units.slice(0, count).join(''));
            if (config.type === 'letter-fade-up') {
                object.set({ opacity: original.opacity * progress, top: original.top + (24 * (1 - progress)) });
            }
            render();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                object.set({ opacity: original.opacity, top: original.top });
                aaSetTextAnimationText(object, text);
                render();
            }
        };
        requestAnimationFrame(step);
    }

    function runTextMotionAnimation(canvas, object, config) {
        var original = aaTextAnimationOriginal(object);
        var start = null;
        var render = function () {
            if (!aaPublicPageHidden()) canvas.requestRenderAll();
        };
        object.set({ opacity: original.opacity });
        var step = function (time) {
            if (aaPublicPageHidden()) {
                aaWhenPublicPageVisible(function () {
                    requestAnimationFrame(step);
                });
                return;
            }
            if (start === null) start = time;
            var progress = Math.min(1, (time - start) / Math.max(200, config.duration));
            var wave = Math.sin(progress * Math.PI * 4);
            object.set({
                top: original.top + wave * 7,
                charSpacing: original.charSpacing + Math.round(wave * 18)
            });
            render();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                object.set({ top: original.top, charSpacing: original.charSpacing });
                render();
            }
        };
        requestAnimationFrame(step);
    }

    function runTextGlowAnimation(canvas, object, config, shine) {
        var original = aaTextAnimationOriginal(object);
        var start = null;
        var baseFill = original.fill || object.fill || '#111827';
        var glowColor = shine ? '#ffffff' : baseFill;
        var render = function () {
            if (!aaPublicPageHidden()) canvas.requestRenderAll();
        };
        object.set({ opacity: original.opacity, text: original.text });
        var step = function (time) {
            if (aaPublicPageHidden()) {
                aaWhenPublicPageVisible(function () {
                    requestAnimationFrame(step);
                });
                return;
            }
            if (start === null) start = time;
            var duration = Math.max(400, config.duration);
            var progress = ((time - start) % duration) / duration;
            var pulse = (Math.sin(progress * Math.PI * 2 - Math.PI / 2) + 1) / 2;
            object.set({
                fill: shine && pulse > .52 ? glowColor : baseFill,
                shadow: new fabric.Shadow({
                    color: shine ? 'rgba(255,255,255,.72)' : String(glowColor),
                    blur: Math.round(4 + pulse * (shine ? 18 : 16)),
                    offsetX: 0,
                    offsetY: 0
                })
            });
            render();
            if (config.loop || time - start < duration) {
                requestAnimationFrame(step);
            } else {
                object.set({ fill: baseFill, shadow: original.shadow });
                render();
            }
        };
        requestAnimationFrame(step);
    }

   function runTextAnimations(canvas) {
    recalculateTextObjects(canvas);

    if (typeof canvas.requestRenderAll === 'function') {
        canvas.requestRenderAll();
    }

    window.setTimeout(function() {
        getTextAnimationObjects(canvas).forEach(function (object) {
            var config = aaNormalizeTextAnimationConfig(object.aaTextAnimation);
            window.setTimeout(function () {
                if (config.type === 'word-reveal') runTextRevealAnimation(canvas, object, config, true);
                else if (config.type === 'typewriter' || config.type === 'letter-fade-up') runTextRevealAnimation(canvas, object, config, false);
                else if (config.type === 'letter-wave') runTextMotionAnimation(canvas, object, config);
                else if (config.type === 'text-glow') runTextGlowAnimation(canvas, object, config, false);
                else if (config.type === 'shine-text') runTextGlowAnimation(canvas, object, config, true);
            }, config.delay);
                });
    }, 540);
}

function ensureTextAnimationsVisibleFallback(canvas) {
    var objects = getTextAnimationObjects(canvas);
    if (!objects.length) return;
    objects.forEach(function (object) {
        var config = aaNormalizeTextAnimationConfig(object.aaTextAnimation);
        var original = aaTextAnimationOriginal(object);
        var timeout = Math.max(1800, config.delay + config.duration + Math.min(String(original.text || '').length, 80) * config.stagger + 950);
        window.setTimeout(function () {
            if (!object || object.visible === false || object.__aaSkipObject === true) return;
            var currentText = String(object.text || '');
            var originalText = String(original.text || '');
            var invisible = Number(object.opacity == null ? 1 : object.opacity) <= 0.02;
            var incompleteReveal = originalText && currentText.length < originalText.length && ['typewriter', 'letter-fade-up', 'word-reveal'].indexOf(config.type) !== -1;
            if (!invisible && !incompleteReveal) return;
            object.set({
                opacity: original.opacity == null ? 1 : original.opacity,
                top: original.top,
                text: originalText,
                fill: object.fill || original.fill
            });
            if (typeof object.initDimensions === 'function') object.initDimensions();
            if (typeof object.setCoords === 'function') object.setCoords();
            object.dirty = true;
            canvas.requestRenderAll();
        }, timeout);
    });
}

    function runObjectAnimations(canvas) {
        getAnimationSortedObjects(canvas).forEach(function (object, index) {
            var animationName = getObjectAnimationName(object);
            var manualDelay = object.animationDelay != null ? object.animationDelay : object.aaAnimationDelay;
            var delay = Number(manualDelay);
            if (object.animationOrderMode !== 'manual' || !isFinite(delay)) {
                object.aaAnimationOrder = index;
                object.aaAnimationDelay = index * 120;
                delay = object.aaAnimationDelay;
            }
            window.setTimeout(function () {
                runSingleAnimation(canvas, object, animationName);
            }, Math.max(0, delay));
        });
    }

    function runSingleAnimation(canvas, object, animationName) {
        var original = objectSnapshot(object);
        var durationFor = function (fallback) { return aaGetAnimationDuration(object, fallback); };
        var render = function () {
            if (!aaPublicPageHidden()) canvas.requestRenderAll();
        };
        var finish = function () {
            object.set(original);
            canvas.requestRenderAll();
        };
        if (animationName === 'fade-in') {
            object.set({ opacity: 0 });
            object.animate('opacity', original.opacity, { duration: durationFor(650), easing: fabric.util.ease.easeOutCubic, onChange: render, onComplete: finish });
            return;
        }
        if (animationName === 'rise' || animationName === 'fade-up') {
            object.set({ top: original.top + 70, opacity: 0 });
            object.animate('top', original.top, { duration: durationFor(720), easing: fabric.util.ease.easeOutCubic, onChange: render });
            object.animate('opacity', original.opacity, { duration: durationFor(650), easing: fabric.util.ease.easeOutCubic, onChange: render, onComplete: finish });
            return;
        }
        if (['fade-down', 'fade-left', 'fade-right'].indexOf(animationName) !== -1) {
            var fadeOffset = 86;
            var fadeFrom = { opacity: 0, top: original.top, left: original.left };
            if (animationName === 'fade-down') fadeFrom.top = original.top - fadeOffset;
            if (animationName === 'fade-left') fadeFrom.left = original.left + fadeOffset;
            if (animationName === 'fade-right') fadeFrom.left = original.left - fadeOffset;
            object.set(fadeFrom);
            object.animate('left', original.left, { duration: durationFor(720), easing: fabric.util.ease.easeOutCubic, onChange: render });
            object.animate('top', original.top, { duration: durationFor(720), easing: fabric.util.ease.easeOutCubic, onChange: render });
            object.animate('opacity', original.opacity, { duration: durationFor(650), easing: fabric.util.ease.easeOutCubic, onChange: render, onComplete: finish });
            return;
        }
        if (['slide-up', 'slide-down', 'slide-left', 'slide-right'].indexOf(animationName) !== -1) {
            var slideOffset = 130;
            var slideFrom = { top: original.top, left: original.left, opacity: original.opacity };
            if (animationName === 'slide-up') slideFrom.top = original.top + slideOffset;
            if (animationName === 'slide-down') slideFrom.top = original.top - slideOffset;
            if (animationName === 'slide-left') slideFrom.left = original.left + slideOffset;
            if (animationName === 'slide-right') slideFrom.left = original.left - slideOffset;
            object.set(slideFrom);
            object.animate('left', original.left, { duration: durationFor(760), easing: fabric.util.ease.easeOutBack, onChange: render });
            object.animate('top', original.top, { duration: durationFor(760), easing: fabric.util.ease.easeOutBack, onChange: render, onComplete: finish });
            return;
        }
        if (animationName === 'zoom-in' || animationName === 'zoom-out') {
            var scale = animationName === 'zoom-in' ? .72 : 1.36;
            object.set({ scaleX: original.scaleX * scale, scaleY: original.scaleY * scale, opacity: 0 });
            object.animate('scaleX', original.scaleX, { duration: durationFor(700), easing: fabric.util.ease.easeOutBack, onChange: render });
            object.animate('scaleY', original.scaleY, { duration: durationFor(700), easing: fabric.util.ease.easeOutBack, onChange: render });
            object.animate('opacity', original.opacity, { duration: durationFor(540), onChange: render, onComplete: finish });
            return;
        }
        if (animationName === 'flip-in') {
            object.set({ scaleX: Math.max(.01, original.scaleX * .08), opacity: 0 });
            object.animate('scaleX', original.scaleX, { duration: durationFor(720), easing: fabric.util.ease.easeOutBack, onChange: render });
            object.animate('opacity', original.opacity, { duration: durationFor(520), onChange: render, onComplete: finish });
            return;
        }
        if (animationName === 'bounce') {
            object.set({ top: original.top - 50, opacity: original.opacity });
            object.animate('top', original.top, { duration: durationFor(780), easing: fabric.util.ease.easeOutBounce, onChange: render, onComplete: finish });
            return;
        }
        if (animationName === 'pulse') {
            object.set({ opacity: original.opacity });
            object.animate('scaleX', original.scaleX * 1.14, {
                duration: durationFor(280),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render,
                onComplete: function () {
                    object.animate('scaleX', original.scaleX, { duration: durationFor(320), easing: fabric.util.ease.easeOutCubic, onChange: render, onComplete: finish });
                }
            });
            object.animate('scaleY', original.scaleY * 1.14, {
                duration: durationFor(280),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render,
                onComplete: function () {
                    object.animate('scaleY', original.scaleY, { duration: durationFor(320), easing: fabric.util.ease.easeOutCubic, onChange: render });
                }
            });
            return;
        }
        if (animationName === 'swing') {
            object.set({ opacity: original.opacity, angle: original.angle - 10 });
            object.animate('angle', original.angle + 10, {
                duration: durationFor(360),
                easing: fabric.util.ease.easeInOutSine,
                onChange: render,
                onComplete: function () {
                    object.animate('angle', original.angle, { duration: durationFor(360), easing: fabric.util.ease.easeOutCubic, onChange: render, onComplete: finish });
                }
            });
            return;
        }
        if (animationName === 'spin') {
            object.set({ opacity: original.opacity, angle: original.angle });
            object.animate('angle', original.angle + 360, { duration: durationFor(760), easing: fabric.util.ease.easeOutCubic, onChange: render, onComplete: finish });
            return;
        }
        if (animationName === 'float-loop') {
            object.set({ opacity: original.opacity, top: original.top });
            var floatLoop = function () {
                if (!aaCanRunLoop(canvas)) {
                    aaWhenLoopReady(canvas, floatLoop);
                    return;
                }
                object.animate('top', original.top - 34, {
                    duration: durationFor(1300),
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('top', original.top + 18, { duration: durationFor(1300), easing: fabric.util.ease.easeInOutSine, onChange: render, onComplete: floatLoop });
                    }
                });
            };
            floatLoop();
            return;
        }
        if (animationName === 'sway-loop') {
            object.set({ opacity: original.opacity, angle: original.angle });
            var swayLoop = function () {
                if (!aaCanRunLoop(canvas)) {
                    aaWhenLoopReady(canvas, swayLoop);
                    return;
                }
                object.animate('angle', original.angle + 8, {
                    duration: durationFor(950),
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('angle', original.angle - 8, { duration: durationFor(950), easing: fabric.util.ease.easeInOutSine, onChange: render, onComplete: swayLoop });
                    }
                });
            };
            swayLoop();
            return;
        }
        if (animationName === 'pulse-loop' || animationName === 'heartbeat-loop') {
            object.set({ opacity: original.opacity, scaleX: original.scaleX, scaleY: original.scaleY });
            var pulseAmount = animationName === 'heartbeat-loop' ? 1.18 : 1.1;
            var pulseDuration = durationFor(animationName === 'heartbeat-loop' ? 360 : 780);
            var pulseLoop = function () {
                if (!aaCanRunLoop(canvas)) {
                    aaWhenLoopReady(canvas, pulseLoop);
                    return;
                }
                object.animate('scaleX', original.scaleX * pulseAmount, { duration: pulseDuration, easing: fabric.util.ease.easeInOutSine, onChange: render });
                object.animate('scaleY', original.scaleY * pulseAmount, {
                    duration: pulseDuration,
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('scaleX', original.scaleX, { duration: pulseDuration, easing: fabric.util.ease.easeInOutSine, onChange: render });
                        object.animate('scaleY', original.scaleY, { duration: pulseDuration, easing: fabric.util.ease.easeInOutSine, onChange: render, onComplete: pulseLoop });
                    }
                });
            };
            pulseLoop();
            return;
        }
        if (animationName === 'drift-loop') {
            object.set({ opacity: original.opacity, left: original.left });
            var driftLoop = function () {
                if (!aaCanRunLoop(canvas)) {
                    aaWhenLoopReady(canvas, driftLoop);
                    return;
                }
                object.animate('left', original.left + 28, {
                    duration: durationFor(1600),
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('left', original.left - 18, { duration: durationFor(1600), easing: fabric.util.ease.easeInOutSine, onChange: render, onComplete: driftLoop });
                    }
                });
            };
            driftLoop();
            return;
        }
        if (animationName === 'spin-loop') {
            object.set({ opacity: original.opacity, angle: original.angle });
            var spinLoop = function () {
                if (!aaCanRunLoop(canvas)) {
                    aaWhenLoopReady(canvas, spinLoop);
                    return;
                }
                object.animate('angle', object.angle + 360, { duration: durationFor(2600), easing: fabric.util.ease.linear, onChange: render, onComplete: spinLoop });
            };
            spinLoop();
            return;
        }
        object.set({ opacity: original.opacity });
        canvas.requestRenderAll();
    }

    function setupLoopAnimationVisibilityTracking(canvasEl, canvas) {
        canvas.__aaSectionVisible = true;
        if (!('IntersectionObserver' in window)) return;
        var section = canvasEl.closest('.aa-fabric-page-section') || canvasEl.closest('.aa-fabric-artboard');
        if (!section) return;
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                canvas.__aaSectionVisible = entry.isIntersecting;
            });
        }, { rootMargin: '100px 0px 100px 0px', threshold: 0 });
        observer.observe(section);
    }

    function setupScrollAnimations(canvasEl, canvas) {
        var section = canvasEl.closest('.aa-fabric-page-section') || canvasEl;
        var clickLayer = canvasEl.closest('.aa-fabric-artboard') ? canvasEl.closest('.aa-fabric-artboard').querySelector('.aa-fabric-click-layer') : null;
        if ((canvas.__aaHasScrollAnimations || canvas.__aaHasTextAnimations || canvas.__aaHasOverlayAnimations) && clickLayer) {
            clickLayer.style.display = 'none';
        }
        var runOnce = function () {
            if (canvas.__aaAnimationsStarted) return;
            canvas.__aaAnimationsStarted = true;
            section.querySelectorAll('.aa-fabric-overlay-animation-waiting').forEach(function (node) {
                node.classList.remove('aa-fabric-overlay-animation-waiting');
            });
            runObjectAnimations(canvas);
            runTextAnimations(canvas);
            ensureTextAnimationsVisibleFallback(canvas);
            if (clickLayer) clickLayer.style.display = '';
        };
        var runWhenOpeningReady = function () {
            if (window.AdaAcaraRunWhenInvitationOpened) {
                window.AdaAcaraRunWhenInvitationOpened(runOnce);
                return;
            }
            runOnce();
        };
        if (!(canvas.__aaHasScrollAnimations || canvas.__aaHasTextAnimations || canvas.__aaHasOverlayAnimations) || !('IntersectionObserver' in window)) {
            runWhenOpeningReady();
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                runWhenOpeningReady();
                observer.disconnect();
            });
        }, {
            threshold: 0.28,
            rootMargin: '0px 0px -8% 0px'
        });
        observer.observe(section);
    }

    function objectParts(object, boxName, textName) {
        var children = object && object.getObjects
            ? object.getObjects()
            : (Array.isArray(object && object.objects) ? object.objects : []);
        var box = null;
        var text = null;
        children.forEach(function (child) {
            if (!box && (child.name === boxName || child.type === 'rect')) box = child;
            if (!text && (child.name === textName || isTextObject(child))) text = child;
        });
        return { box: box || {}, text: text || {} };
    }

    function countdownOverlayColumns(object) {
        object = object || {};
        var width = Math.max(80, Number(object.width) || 620);
        var gap = Math.max(0, Number(object.countdownGap) || 0);
        var fontSize = Math.max(8, Number(object.countdownFontSize || object.fontSize) || 36);
        var minCardWidth = Math.max(64, fontSize * 1.8);
        var fourColumnWidth = (minCardWidth * 4) + (gap * 3);
        var twoColumnWidth = (minCardWidth * 2) + gap;

        if (width < twoColumnWidth * 1.05) return 1;
        if (width < fourColumnWidth * 0.96) return 2;
        return 4;
    }

    function overlayBoxStyle(object, canvas, mode) {
        var parts = mode === 'guestbook'
            ? objectParts(object, 'guestbook-box', 'guestbook-text')
            : objectParts(object, 'interactive-box', 'interactive-text');
        var rect = object.getBoundingRect();
        var box = parts.box;
        var text = parts.text;
        var scaleX = Math.abs(object.scaleX || 1);
        var scaleY = Math.abs(object.scaleY || 1);
        var canvasWidth = Math.max(1, canvas.getWidth() || 1080);
        var canvasEl = canvas.lowerCanvasEl || (typeof canvas.getElement === 'function' ? canvas.getElement() : null);
        var artboardEl = canvasEl && typeof canvasEl.closest === 'function' ? canvasEl.closest('.aa-fabric-artboard') : null;
        var renderedWidth = artboardEl ? artboardEl.clientWidth : 0;
        if (!renderedWidth && canvasEl && typeof canvasEl.getBoundingClientRect === 'function') {
            renderedWidth = canvasEl.getBoundingClientRect().width;
        }
        var artboardScale = renderedWidth > 0 ? Math.max(0.05, Math.min(1, renderedWidth / canvasWidth)) : 1;
        var fontSize = Math.max(8, Number(text.fontSize || 32) * scaleY);
        var fontViewport = Math.max(2, fontSize / canvasWidth * 100);
        var radius = Math.max(0, Number(object.controlRadius != null ? object.controlRadius : (box.rx || box.ry || 0)) * Math.max(scaleX, scaleY));
        var borderWidth = Math.max(0, Number(box.strokeWidth || 1) * Math.max(scaleX, scaleY));
        var isCountdown = object.customType === 'countdown-timer';
        var countdownFontSize = Math.max(8, Number(object.countdownFontSize || text.fontSize || 36));
        var countdownRadius = Math.max(0, Number(object.controlRadius != null ? object.controlRadius : (box.rx || box.ry || 0)));
        var countdownGap = Math.max(0, Number(object.countdownGap) || 0);
        var countdownScaledFontSize = Math.max(8, Math.min(50, countdownFontSize * artboardScale));
        var countdownScaledRadius = Math.max(0, countdownRadius * artboardScale);
        var countdownScaledGap = Math.max(0, Math.min(10, countdownGap * artboardScale));

        return {
            left: (rect.left / canvas.getWidth() * 100) + '%',
            top: (rect.top / canvas.getHeight() * 100) + '%',
            width: (rect.width / canvas.getWidth() * 100) + '%',
            height: (rect.height / canvas.getHeight() * 100) + '%',
            radius: Math.max(0, isCountdown ? countdownRadius : radius) + 'px',
            background: object.controlBackground || box.fill || '#ffffff',
            borderColor: box.stroke || '#cbd5e1',
            borderWidth: Math.max(1, Math.min(8, borderWidth)) + 'px',
            color: isCountdown ? (object.countdownTextColor || text.fill || '#0f172a') : (text.fill || '#334155'),
            fontFamily: normalizeFontFamily(isCountdown ? (object.countdownFontFamily || text.fontFamily) : text.fontFamily),
            fontSize: isCountdown
                ? countdownScaledFontSize + 'px'
                : 'clamp(10px, ' + fontViewport + 'vw, 18px)',
            fontWeight: text.fontWeight || (mode === 'interactive' ? 'bold' : 'normal'),
            textAlign: text.textAlign || 'left',
            lineHeight: Number(text.lineHeight || 1.14),
            countdownGap: isCountdown
                ? countdownScaledGap + 'px'
                : countdownGap + 'px',
            countdownColumns: isCountdown ? countdownOverlayColumns(object) : 4,
            countdownCardRadius: isCountdown
                ? countdownScaledRadius + 'px'
                : Math.max(0, radius) + 'px',
            angle: object.angle || 0
        };
    }

    function applyOverlayStyle(el, style) {
        el.style.left = style.left;
        el.style.top = style.top;
        el.style.width = style.width;
        el.style.height = style.height;
        el.style.borderRadius = style.radius;
        el.style.background = style.background;
        el.style.color = style.color;
        el.style.fontFamily = style.fontFamily;
        el.style.fontSize = style.fontSize;
        el.style.fontWeight = style.fontWeight;
        el.style.textAlign = style.textAlign;
        el.style.setProperty('--aa-field-line-height', style.lineHeight);
        el.style.setProperty('--aa-field-border-color', style.borderColor);
        el.style.setProperty('--aa-field-border-width', style.borderWidth);
        el.style.setProperty('--aa-control-bg', style.background);
        el.style.setProperty('--aa-control-color', style.color);
        el.style.setProperty('--aa-control-border-color', style.borderColor);
        el.style.setProperty('--aa-control-border-width', style.borderWidth);
        el.style.setProperty('--aa-countdown-gap', style.countdownGap || '8px');
        el.style.setProperty('--aa-countdown-columns', String(style.countdownColumns || 4));
        el.style.setProperty('--aa-countdown-card-radius', style.countdownCardRadius || style.radius);
        var baseTransform = style.angle ? 'rotate(' + style.angle + 'deg)' : 'rotate(0deg)';
        el.style.setProperty('--aa-overlay-base-transform', baseTransform);
        el.style.transform = baseTransform;
        el.style.transformOrigin = 'center center';
    }

    function overlayObjectFromJson(source) {
        source = source || {};
        var children = Array.isArray(source.objects) ? source.objects : [];
        var scaleX = Math.abs(Number(source.scaleX) || 1);
        var scaleY = Math.abs(Number(source.scaleY) || 1);
        var width = Math.max(1, Number(source.width) || 1) * scaleX;
        var height = Math.max(1, Number(source.height) || 1) * scaleY;
        var left = Number(source.left) || 0;
        var top = Number(source.top) || 0;

        if (source.originX === 'center') left -= width / 2;
        if (source.originX === 'right') left -= width;
        if (source.originY === 'center') top -= height / 2;
        if (source.originY === 'bottom') top -= height;

        return Object.assign({}, source, {
            scaleX: scaleX,
            scaleY: scaleY,
            getObjects: function () {
                return children;
            },
            getBoundingRect: function () {
                return {
                    left: left,
                    top: top,
                    width: width,
                    height: height
                };
            }
        });
    }

    function applyOverlayAnimation(el, object) {
        var animationName = getObjectAnimationName(object);
        if (!animationName || animationName === 'none') return;
        var safeName = String(animationName).toLowerCase().replace(/[^a-z0-9-]/g, '');
        var duration = Number(object.aaAnimationDuration != null ? object.aaAnimationDuration : object.animationDuration);
        var delay = Number(object.aaAnimationDelay != null ? object.aaAnimationDelay : object.animationDelay);
        el.classList.add('aa-fabric-overlay-animated', 'aa-fabric-overlay-animation-waiting', 'aa-overlay-animation-' + safeName);
        el.dataset.aaOverlayAnimation = safeName;
        el.style.setProperty('--aa-overlay-animation-duration', (isFinite(duration) && duration > 0 ? duration : 900) + 'ms');
        el.style.setProperty('--aa-overlay-animation-delay', (isFinite(delay) && delay > 0 ? delay : 0) + 'ms');
    }

    function getGuestbookEndpoint() {
        if (window.AdaAcaraGuestbookEndpoint) return window.AdaAcaraGuestbookEndpoint;
        if (fabricData.guestbookEndpoint) return fabricData.guestbookEndpoint;
        var match = window.location.pathname.match(/\/u\/([^\/]+)/);
        return match ? '/u/' + match[1] + '/guestbook' : '';
    }

    function addGuestbookCsrf(formData) {
        var csrf = window.AdaAcaraGuestbookCsrf || {};
        if (csrf.name && csrf.hash) formData.append(csrf.name, csrf.hash);
    }

    function updateGuestbookCsrf(hash) {
        if (!hash || !window.AdaAcaraGuestbookCsrf) return;
        window.AdaAcaraGuestbookCsrf.hash = hash;
    }

    function stickerUrl(file) {
        file = String(file || '').replace(/[^a-z0-9.]/gi, '');
        return file ? (window.AdaAcaraStickerBase || '/assets/stiker/') + file : '';
    }

    function populateCommentLists(layer) {
        var comments = Array.isArray(window.AdaAcaraGuestbookEntries) ? window.AdaAcaraGuestbookEntries : [];
        layer.querySelectorAll('[data-aa-comment-list]').forEach(function (list) {
            list.innerHTML = '';
            if (!comments.length) {
                var empty = document.createElement('div');
                empty.className = 'aa-fabric-comment-empty';
                empty.textContent = 'Belum ada ucapan. Jadilah yang pertama mengisi guestbook.';
                list.appendChild(empty);
                return;
            }
            comments.forEach(function (comment) {
                var card = document.createElement('article');
                card.className = 'aa-fabric-comment-card';
                var name = document.createElement('strong');
                name.textContent = comment.guest_name || '';
                var body = document.createElement('p');
                body.textContent = comment.message || '';
                body.style.margin = '8px 0 0';
                card.append(name, body);
                if (comment.sticker_url) {
                    var img = document.createElement('img');
                    img.src = comment.sticker_url;
                    img.alt = 'Sticker';
                    img.loading = 'lazy';
                    img.style.cssText = 'display:block;width:48px;height:48px;object-fit:contain;margin-top:8px;';
                    card.appendChild(img);
                }
                list.appendChild(card);
            });
        });
    }

    function setupStickerPicker(wrapper, hiddenInput, preview) {
        var popover = document.createElement('div');
        popover.className = 'aa-fabric-sticker-popover';
        function setSelected(file, src) {
            hiddenInput.value = file || '';
            popover.querySelectorAll('button').forEach(function (button) {
                button.classList.toggle('is-selected', button.dataset.sticker === file);
            });
            if (!preview) return;
            var img = preview.querySelector('img');
            if (img) img.src = src || '';
            preview.classList.toggle('is-visible', Boolean(file));
        }
        for (var i = 1; i <= 34; i += 1) {
            var file = 'sticker' + String(i).padStart(3, '0') + '.gif';
            var choice = document.createElement('button');
            choice.type = 'button';
            choice.dataset.sticker = file;
            var img = document.createElement('img');
            img.src = stickerUrl(file);
            img.alt = 'Sticker';
            choice.appendChild(img);
            choice.addEventListener('click', function (event) {
                var selected = event.currentTarget.querySelector('img');
                var selectedFile = event.currentTarget.dataset.sticker || '';
                setSelected(selectedFile, selected ? selected.src : '');
                popover.classList.remove('is-open');
            });
            popover.appendChild(choice);
        }
        popover.__aaSetSelected = setSelected;
        wrapper.appendChild(popover);
        return popover;
    }

    function setupGuestbookOverlay(canvasEl, canvas, sourceObjects) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard) return;
        var old = artboard.querySelector('.aa-fabric-guestbook-layer');
        if (old) old.remove();
        var guestObjects = Array.isArray(sourceObjects) && sourceObjects.length
            ? sourceObjects.map(overlayObjectFromJson)
            : canvas.getObjects().filter(isGuestbookControlObject);
        if (!guestObjects.length) return;

        var layer = document.createElement('form');
        layer.className = 'aa-fabric-guestbook-layer';
        layer.action = getGuestbookEndpoint() || '#';
        layer.method = 'post';
        layer.noValidate = true;
        var stickerInput = document.createElement('input');
        stickerInput.type = 'hidden';
        stickerInput.name = 'sticker';
        layer.appendChild(stickerInput);

        guestObjects.forEach(function (object) {
            object.visible = false;
            var control = document.createElement('div');
            control.className = 'aa-fabric-guestbook-control';
            control.dataset.guestbookRole = object.customType || '';
            applyOverlayStyle(control, overlayBoxStyle(object, canvas, 'guestbook'));
            applyOverlayAnimation(control, object);
            var placeholder = object.placeholder || object.label || '';

            if (object.customType === 'guest-name-input') {
                var input = document.createElement('input');
                input.name = object.fieldName || 'guest_name';
                input.placeholder = placeholder || 'Nama';
                input.maxLength = Number(object.maxLength) || 120;
                input.required = object.required !== false;
                control.appendChild(input);
            } else if (object.customType === 'guest-attendance-select') {
                var select = document.createElement('select');
                select.name = object.fieldName || 'attendance';
                select.required = object.required !== false;
                ['','hadir:Hadir','tidak_hadir:Tidak hadir','ragu:Ragu'].forEach(function (item) {
                    var parts = item.split(':');
                    var option = document.createElement('option');
                    option.value = parts[0] || '';
                    option.textContent = parts[1] || placeholder || 'Pilih Kehadiran';
                    select.appendChild(option);
                });
                control.appendChild(select);
            } else if (object.customType === 'guest-message-textarea') {
                var textarea = document.createElement('textarea');
                textarea.name = object.fieldName || 'message';
                textarea.placeholder = placeholder || 'Tulis ucapan...';
                textarea.maxLength = Number(object.maxLength) || 800;
                textarea.required = object.required !== false;
                control.appendChild(textarea);
            } else if (object.customType === 'guest-sticker-picker') {
                var stickerButton = document.createElement('button');
                stickerButton.type = 'button';
                stickerButton.textContent = placeholder || 'Stiker';
                var selectedPreview = document.createElement('span');
                selectedPreview.className = 'aa-fabric-selected-sticker';
                selectedPreview.innerHTML = '<img src="" alt="Stiker terpilih"><span>Stiker dipilih</span>';
                var clearSticker = document.createElement('button');
                clearSticker.type = 'button';
                clearSticker.textContent = 'X';
                selectedPreview.appendChild(clearSticker);
                control.appendChild(selectedPreview);
                var popover = setupStickerPicker(control, stickerInput, selectedPreview);
                stickerButton.addEventListener('click', function () { popover.classList.toggle('is-open'); });
                clearSticker.addEventListener('click', function () {
                    if (popover.__aaSetSelected) popover.__aaSetSelected('', '');
                    popover.classList.remove('is-open');
                });
                control.appendChild(stickerButton);
            } else if (object.customType === 'guest-submit-button') {
                var submit = document.createElement('button');
                submit.type = 'submit';
                submit.textContent = object.buttonText || placeholder || 'Kirim Ucapan';
                control.appendChild(submit);
            } else if (object.customType === 'guest-comment-list') {
                var list = document.createElement('div');
                list.className = 'aa-fabric-comment-list';
                list.dataset.aaCommentList = 'true';
                control.appendChild(list);
            }
            layer.appendChild(control);
        });

        layer.addEventListener('submit', function (event) {
            event.preventDefault();
            var formData = new FormData(layer);
            if (!formData.get('attendance')) formData.set('attendance', 'ragu');
            if (!String(formData.get('guest_name') || '').trim()) return showCopyToast('Nama wajib diisi.');
            if (!String(formData.get('message') || '').trim()) return showCopyToast('Ucapan wajib diisi.');
            addGuestbookCsrf(formData);
            fetch(layer.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || data.success === false) throw new Error(data.message || 'Ucapan gagal dikirim.');
                    return data;
                });
            }).then(function (data) {
                updateGuestbookCsrf(data.csrf_hash);
                window.AdaAcaraGuestbookEntries = window.AdaAcaraGuestbookEntries || [];
                window.AdaAcaraGuestbookEntries.unshift(data.comment || {});
                layer.reset();
                stickerInput.value = '';
                populateCommentLists(layer);
                showCopyToast(data.message || 'Ucapan berhasil dikirim.');
            }).catch(function (error) {
                showCopyToast(error.message || 'Ucapan gagal dikirim.');
            });
        });

        artboard.appendChild(layer);
        if (layer.querySelector('.aa-fabric-overlay-animated')) {
            canvas.__aaHasOverlayAnimations = true;
        }
        populateCommentLists(layer);
    }

    function setupMusicControl(wrapper, object) {
        var button = document.createElement('button');
        var audio = document.createElement('audio');
        var icon = document.createElement('span');
        button.type = 'button';
        button.className = 'aa-fabric-music-button';
        button.setAttribute('aria-label', 'Putar musik');
        button.setAttribute('title', 'Putar musik');
        icon.className = 'aa-fabric-music-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = '▶';
        button.appendChild(icon);
        audio.preload = 'auto';
        audio.loop = object.loopAudio !== false && object.loopAudio !== 'false';
        audio.setAttribute('playsinline', 'playsinline');
        if (object.audioUrl) audio.src = object.audioUrl;
        button.addEventListener('click', function () {
            if (!audio.paused) {
                audio.pause();
                icon.textContent = '▶';
                return;
            }
            audio.play().then(function () {
                icon.textContent = '❚❚';
            }).catch(function () {
                showCopyToast('Musik belum bisa diputar.');
            });
        });
        wrapper.append(audio, button);
    }

    function sanitizeYoutubeVideoId(value) {
        var match = String(value || '').match(/[A-Za-z0-9_-]{6,20}/);
        return match ? match[0] : '';
    }

    function extractYoutubeIdFromText(value) {
        var source = String(value || '').trim();
        var markers = ['youtu.be/', 'watch?v=', 'embed/', 'shorts/', 'live/'];
        for (var i = 0; i < markers.length; i++) {
            var marker = markers[i];
            var index = source.indexOf(marker);
            if (index !== -1) return sanitizeYoutubeVideoId(source.slice(index + marker.length));
        }
        return '';
    }

    function parseYoutubeVideoId(value) {
        var source = String(value || '').trim();
        if (!source) return '';
        if (/^[A-Za-z0-9_-]{6,20}$/.test(source)) return source;
        try {
            var url = new URL(source);
            var host = url.hostname.replace(/^www\./, '');
            if (host === 'youtu.be') return sanitizeYoutubeVideoId(url.pathname.split('/').filter(Boolean)[0] || '');
            if (host.indexOf('youtube.com') !== -1 || host.indexOf('youtube-nocookie.com') !== -1) {
                var watchId = url.searchParams.get('v');
                if (watchId) return sanitizeYoutubeVideoId(watchId);
                var parts = url.pathname.split('/').filter(Boolean);
                for (var i = 0; i < parts.length - 1; i++) {
                    if (['embed', 'shorts', 'live'].indexOf(parts[i]) !== -1) return sanitizeYoutubeVideoId(parts[i + 1]);
                }
            }
        } catch (error) {
            return extractYoutubeIdFromText(source);
        }
        return '';
    }

    function youtubeEmbedUrl(id, options) {
        var params = [
            'controls=1',
            'modestbranding=1',
            'rel=0',
            'playsinline=1',
            'disablekb=1',
            'iv_load_policy=3',
            'cc_load_policy=0'
        ];
        if (options && options.autoplay) {
            params.push('autoplay=1');
            params.push('mute=1');
        }
        if (options && options.loop) {
            params.push('loop=1');
            params.push('playlist=' + encodeURIComponent(id));
        }
        return 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?' + params.join('&');
    }

    function setupYoutubeAutoplayOnView(wrapper, iframe, id, object) {
        var shouldAutoplay = object.youtubeAutoplayOnView !== false && object.youtubeAutoplayOnView !== 'false';
        if (!shouldAutoplay || !('IntersectionObserver' in window)) return;
        var autoplaySrc = youtubeEmbedUrl(id, {
            autoplay: true,
            loop: object.youtubeLoop !== false && object.youtubeLoop !== 'false'
        });
        var started = false;
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (started || !entry.isIntersecting) return;
                started = true;
                iframe.src = autoplaySrc;
                observer.unobserve(wrapper);
                observer.disconnect();
            });
        }, { threshold: 0.35 });
        observer.observe(wrapper);
    }

    function setupYoutubeControl(wrapper, object) {
        var id = sanitizeYoutubeVideoId(object.youtubeVideoId) || parseYoutubeVideoId(object.youtubeUrl);
        if (!id) {
            var placeholder = document.createElement('div');
            placeholder.className = 'aa-fabric-youtube-placeholder';
            placeholder.textContent = 'Video Youtube belum diatur';
            wrapper.appendChild(placeholder);
            return;
        }
        var iframe = document.createElement('iframe');
        iframe.className = 'aa-fabric-youtube-frame';
        iframe.src = youtubeEmbedUrl(id, {
            autoplay: false,
            loop: object.youtubeLoop !== false && object.youtubeLoop !== 'false'
        });
        iframe.title = object.label || 'Youtube Video';
        iframe.loading = 'lazy';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        wrapper.appendChild(iframe);
        setupYoutubeAutoplayOnView(wrapper, iframe, id, object);
    }

    function safeUrl(value) {
        var source = String(value || '').trim();
        if (!source) return '';
        try {
            var url = new URL(source, window.location.origin);
            if (['http:', 'https:', 'mailto:', 'tel:'].indexOf(url.protocol) === -1) return '';
            return url.href;
        } catch (error) {
            return '';
        }
    }

    function setupSocialMediaControl(wrapper, object) {
        var links = object.socialLinks || {};
        var icons = {
            instagram: ['Instagram', 'IG'],
            tiktok: ['TikTok', 'TT'],
            threads: ['Threads', 'TH'],
            x: ['X', 'X'],
            facebook: ['Facebook', 'FB'],
            youtube: ['YouTube', 'YT']
        };
        var box = document.createElement('div');
        box.className = 'aa-fabric-social-box';
        var title = document.createElement('strong');
        var titleObject = object.getObjects ? object.getObjects().find(function (child) {
            return child && (child.name === 'interactive-title' || child.name === 'social-title' || child.name === 'title');
        }) : null;
        title.textContent = object.socialTitle || 'Ikuti Kami';
        if (titleObject) {
            if (titleObject.fontFamily) title.style.fontFamily = '"' + String(titleObject.fontFamily).replace(/"/g, '') + '", Inter, sans-serif';
            if (titleObject.fontWeight) title.style.fontWeight = titleObject.fontWeight;
            if (titleObject.fontStyle) title.style.fontStyle = titleObject.fontStyle;
            if (titleObject.fill) title.style.color = titleObject.fill;
            if (titleObject.underline) title.style.textDecoration = 'underline';
        }
        var row = document.createElement('div');
        row.className = 'aa-fabric-social-row';
        Object.keys(icons).forEach(function (key) {
            var url = safeUrl(links[key]);
            if (!url) return;
            var link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.className = 'aa-fabric-social-link aa-social-' + key;
            link.setAttribute('aria-label', icons[key][0]);
            link.textContent = icons[key][1];
            row.appendChild(link);
        });
        if (!row.children.length) {
            var empty = document.createElement('span');
            empty.className = 'aa-fabric-social-empty';
            empty.textContent = 'Social media belum diatur';
            row.appendChild(empty);
        }
        box.append(title, row);
        wrapper.appendChild(box);
    }

    function setupStoryMakerControl(wrapper, object) {
        var box = document.createElement('div');
        box.className = 'aa-fabric-story-box';
        var title = document.createElement('strong');
        title.textContent = object.storyTitle || 'Our Story';
        var list = document.createElement('div');
        list.className = 'aa-fabric-story-list';
        var items = Array.isArray(object.storyItems) ? object.storyItems : [];
        items.forEach(function (item) {
            var card = document.createElement('article');
            card.className = 'aa-fabric-story-item';
            var date = document.createElement('small');
            date.textContent = item.date || '';
            var heading = document.createElement('b');
            heading.textContent = item.title || 'Cerita';
            var text = document.createElement('p');
            text.textContent = item.description || '';
            card.append(date, heading, text);
            list.appendChild(card);
        });
        if (!items.length) {
            var empty = document.createElement('p');
            empty.className = 'aa-fabric-story-empty';
            empty.textContent = 'Cerita belum diatur';
            list.appendChild(empty);
        }
        box.append(title, list);
        wrapper.appendChild(box);
    }

    function setupInteractiveOverlay(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard) return;
        var old = artboard.querySelector('.aa-fabric-interactive-layer');
        if (old) old.remove();
        var objects = canvas.getObjects().filter(isInteractiveObject);
        if (!objects.length) return;
        var layer = document.createElement('div');
        layer.className = 'aa-fabric-interactive-layer';
        objects.forEach(function (object) {
            var control = document.createElement('div');
            try {
                control.className = 'aa-fabric-interactive-control';
                applyOverlayStyle(control, overlayBoxStyle(object, canvas, 'interactive'));
                applyOverlayAnimation(control, object);
                if (object.customType === 'music-player') setupMusicControl(control, object);
                if (object.customType === 'youtube-video') setupYoutubeControl(control, object);
                if (object.customType === 'social-media') setupSocialMediaControl(control, object);
                if (object.customType === 'scroll-next-button') {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'aa-fabric-scroll-button';
                button.textContent = object.buttonText || object.label || 'Scroll Down';
                button.addEventListener('click', function () {
                    var section = canvasEl.closest('.aa-fabric-page-section');
                    var next = section ? section.nextElementSibling : null;
                    if (next) next.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
                control.appendChild(button);
            }
            if (object.customType === 'countdown-timer') {
                var box = document.createElement('div');
                box.className = 'aa-fabric-countdown';
                ['Hari', 'Jam', 'Menit', 'Detik'].forEach(function (label) {
                    var item = document.createElement('span');
                    item.innerHTML = '<strong>00</strong><small>' + label + '</small>';
                    box.appendChild(item);
                });
                var target = new Date(object.countdownTarget || ((object.countdownDate || '') + 'T' + (object.countdownTime || '00:00') + ':00')).getTime();
                var tick = function () {
                    if (aaPublicPageHidden()) return;
                    var diff = Math.max(0, (Number.isFinite(target) ? target : Date.now()) - Date.now());
                    var values = [Math.floor(diff / 86400000), Math.floor((diff % 86400000) / 3600000), Math.floor((diff % 3600000) / 60000), Math.floor((diff % 60000) / 1000)];
                    box.querySelectorAll('strong').forEach(function (node, index) {
                        node.textContent = String(values[index] || 0).padStart(2, '0');
                    });
                };
                tick();
                window.setInterval(tick, 1000);
                control.appendChild(box);
            }
            if (object.customType === 'photo-gallery') {
                var gallery = document.createElement('div');
                gallery.className = 'aa-fabric-gallery';
                gallery.style.gridTemplateColumns = 'repeat(' + Math.max(1, Math.min(6, Number(object.galleryColumns) || 2)) + ', 1fr)';
                gallery.style.gap = Math.max(0, Number(object.galleryGap) || 0) + 'px';
                var itemRadius = Math.max(0, Number(object.galleryRadius) || 0) * Math.max(Math.abs(object.scaleX || 1), Math.abs(object.scaleY || 1));
                var galleryItems = Array.isArray(object.galleryItems) && object.galleryItems.length ? object.galleryItems : (Array.isArray(object.galleryImages) ? object.galleryImages : []).map(function (src) { return { src: src }; });
                galleryItems.filter(function (item) { return item && item.src; }).forEach(function (item) {
                    var button = document.createElement('button');
                    var img = document.createElement('img');
                    button.type = 'button';
                    button.style.borderRadius = itemRadius + 'px';
                    if (item.aspectRatio) button.style.aspectRatio = String(item.aspectRatio);
                    img.src = item.src;
                    img.alt = item.name || 'Gallery';
                    img.loading = 'lazy';
                    button.appendChild(img);
                    bindGalleryLightboxTrigger(button, function () {
                        return item.src;
                    });
                    gallery.appendChild(button);
                });
                control.appendChild(gallery);
            }
                object.visible = false;
                object.dirty = true;
            layer.appendChild(control);
            } catch (error) {
                object.visible = true;
                object.dirty = true;
                if (canvas && typeof canvas.requestRenderAll === 'function') canvas.requestRenderAll();
                console.warn('Interactive overlay gagal:', error);
            }
        });
        artboard.appendChild(layer);
        if (layer.querySelector('.aa-fabric-overlay-animated')) {
            canvas.__aaHasOverlayAnimations = true;
        }
    }

    function setupHybridOverlays(canvasEl, canvas, guestbookSourceObjects) {
        try {
            setupGuestbookOverlay(canvasEl, canvas, guestbookSourceObjects);
        } catch (error) {
            console.warn('Guestbook overlay gagal:', error);
        }
        try {
            setupInteractiveOverlay(canvasEl, canvas);
        } catch (error) {
            console.warn('Interactive overlay gagal:', error);
        }
        canvas.requestRenderAll();
    }

    function isAnimatedGifObject(object) {
        if (!object || object.type !== 'image') return false;
        var src = String(object.aaAnimatedSrc || object.src || (object._element && object._element.src) || '');
        var cleanSrc = src.split('?')[0].toLowerCase();
        return object.aaMediaKind === 'gif' || cleanSrc.endsWith('.gif');
    }

    function animatedGifObjectGeometry(object, canvasWidth, canvasHeight, imageWidth, imageHeight) {
        var isCoverBackground = object && object.customType === 'background' && (
            object.name === 'Background Image' || object.aaBgOffsetX != null || object.aaBgOffsetY != null
        );
        var sourceWidth = Math.max(1, Number(imageWidth) || 0);
        var sourceHeight = Math.max(1, Number(imageHeight) || 0);
        var center = object.getCenterPoint ? object.getCenterPoint() : {
            x: (Number(object.left) || canvasWidth / 2),
            y: (Number(object.top) || canvasHeight / 2)
        };
        var storedWidth = Math.abs((Number(object.width) || 0) * (Number(object.scaleX) || 1));
        var storedHeight = Math.abs((Number(object.height) || 0) * (Number(object.scaleY) || 1));
        var width = storedWidth > 1 ? storedWidth : canvasWidth;
        var height = storedHeight > 1 ? storedHeight : canvasHeight;
        if (isCoverBackground && sourceWidth > 1 && sourceHeight > 1 && (storedWidth <= 1 || storedHeight <= 1)) {
            var offsetX = Number(object.aaBgOffsetX || 0);
            var offsetY = Number(object.aaBgOffsetY || 0);
            var coverScale = Math.max(canvasWidth / sourceWidth, canvasHeight / sourceHeight);
            return {
                center: {
                    x: (canvasWidth / 2) + ((canvasWidth * offsetX) / 100),
                    y: (canvasHeight / 2) + ((canvasHeight * offsetY) / 100)
                },
                width: Math.max(1, sourceWidth * coverScale),
                height: Math.max(1, sourceHeight * coverScale),
                angle: Number(object.angle) || 0,
                opacity: Math.max(0, Math.min(1, Number(object.opacity == null ? 1 : object.opacity))),
                flipX: object.flipX ? ' scaleX(-1)' : '',
                flipY: object.flipY ? ' scaleY(-1)' : ''
            };
        }
        return {
            center: center,
            width: width,
            height: height,
            angle: Number(object.angle) || 0,
            opacity: Math.max(0, Math.min(1, Number(object.opacity == null ? 1 : object.opacity))),
            flipX: object.flipX ? ' scaleX(-1)' : '',
            flipY: object.flipY ? ' scaleY(-1)' : ''
        };
    }

    function setupAnimatedGifBackground(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard || !canvas || !canvas.getObjects) return;

        var oldLayer = artboard.querySelector('.aa-fabric-bg-gif-layer');
        if (oldLayer) oldLayer.remove();

        var background = canvas.getObjects().find(function (object) {
            return object && object.customType === 'background' && isAnimatedGifObject(object);
        });
        if (!background) return;

        background.visible = false;
        background.evented = false;
        background.selectable = false;
        canvas.backgroundColor = '';

        var canvasWidth = canvas.getWidth() || 1;
        var canvasHeight = canvas.getHeight() || 1;
        var src = background.aaAnimatedSrc || background.src || (background._element && background._element.src) || '';
        if (!src) return;

        var layer = document.createElement('div');
        layer.className = 'aa-fabric-bg-gif-layer';
        var img = document.createElement('img');
        img.alt = '';
        img.loading = 'eager';
        img.decoding = 'async';
        var applyGeometry = function () {
            var geometry = animatedGifObjectGeometry(background, canvasWidth, canvasHeight, img.naturalWidth, img.naturalHeight);
            img.style.left = (geometry.center.x / canvasWidth * 100) + '%';
            img.style.top = (geometry.center.y / canvasHeight * 100) + '%';
            img.style.width = (geometry.width / canvasWidth * 100) + '%';
            img.style.height = (geometry.height / canvasHeight * 100) + '%';
            img.style.maxWidth = 'none';
            img.style.maxHeight = 'none';
            img.style.opacity = String(geometry.opacity);
            img.style.transform = 'translate(-50%, -50%) rotate(' + geometry.angle + 'deg)' + geometry.flipX + geometry.flipY;
        };
        img.addEventListener('load', applyGeometry, { once: true });
        img.src = src;
        applyGeometry();
        layer.appendChild(img);
        artboard.insertBefore(layer, artboard.firstChild);
    }

    function renderFabric() {
        var pages = pagesFromData(fabricData);
        lazyRenderPages(pages);
    }

    loadFabric(renderFabric);
})();
JS;
        }
    }

    if (! function_exists('aa_public_font_url')) {
        function aa_public_font_url(string $html, string $css, string $editorJson): string
        {
            $fontWeights = [
                'Aboreto' => '400',
                'Abril Fatface' => '400',
                'Adamina' => '400',
                'Alex Brush' => '400',
                'Alfa Slab One' => '400',
                'Allura' => '400',
                'Amarante' => '400',
                'Amiri' => '400;700',
                'Anton' => '400',
                'Archivo' => '400;500;700',
                'Archivo Black' => '400',
                'Arizonia' => '400',
                'Assistant' => '200;300;400;500;600;700;800',
                'Barlow' => '100;200;300;400;500;600;700;800;900',
                'Bebas Neue' => '400',
                'Bellefair' => '400',
                'Bitter' => '100;200;300;400;500;600;700;800;900',
                'Black Ops One' => '400',
                'Bodoni Moda' => '400;500;600;700;800;900',
                'Bonheur Royale' => '400',
                'Cabin' => '400;500;600;700',
                'Caudex' => '400;700',
                'Caveat' => '400;700',
                'Changa One' => '400',
                'Cinzel' => '400;500;600;700;800;900',
                'Cookie' => '400',
                'Cormorant Garamond' => '300;400;500;600;700',
                'Cormorant Infant' => '300;400;500;600;700',
                'Cormorant Upright' => '300;400;500;600;700',
                'Courgette' => '400',
                'Crimson Text' => '400;600;700',
                'DM Sans' => '100;200;300;400;500;600;700;800;900;1000',
                'DM Serif Display' => '400',
                'Dancing Script' => '400;500;600;700',
                'Dosis' => '200;300;400;500;600;700;800',
                'EB Garamond' => '400;500;600;700;800',
                'Elsie' => '400;900',
                'Ephesis' => '400',
                'Figtree' => '400;500;700',
                'Fira Sans' => '100;200;300;400;500;600;700;800;900',
                'Fleur De Leah' => '400',
                'Forum' => '400',
                'Fraunces' => '100;200;300;400;500;600;700;800;900',
                'Google Sans' => '400;500;600;700',
                'Great Vibes' => '400',
                'Heebo' => '100;200;300;400;500;600;700;800;900',
                'IBM Plex Sans' => '100;200;300;400;500;600;700',
                'Imperial Script' => '400',
                'Inconsolata' => '200;300;400;500;600;700;800;900',
                'Instrument Serif' => '400',
                'Inter' => '100;200;300;400;500;600;700;800;900',
                'Inter Tight' => '100;200;300;400;500;600;700;800;900',
                'Italiana' => '400',
                'Italianno' => '400',
                'JetBrains Mono' => '100;200;300;400;500;600;700;800',
                'Josefin Sans' => '400;500;700',
                'Jost' => '100;200;300;400;500;600;700;800;900',
                'Kanit' => '100;200;300;400;500;600;700;800;900',
                'Karla' => '400;700',
                'Lavishly Yours' => '400',
                'Libre Baskerville' => '400;500;600;700',
                'Libre Franklin' => '100;200;300;400;500;600;700;800;900',
                'Lobster Two' => '400;700',
                'Lora' => '400;500;600;700',
                'Manrope' => '400;500;700',
                'Marcellus' => '400',
                'Mea Culpa' => '400',
                'Merriweather' => '400;700',
                'Monsieur La Doulaise' => '400',
                'Montserrat' => '100;200;300;400;500;600;700;800;900',
                'Mulish' => '400;500;700',
                'Noto Naskh Arabic' => '400;500;600;700',
                'Noto Sans' => '400;500;700',
                'Noto Serif' => '400;700',
                'Nunito' => '400;600;700',
                'Nunito Sans' => '200;300;400;500;600;700;800;900;1000',
                'Open Sans' => '400;500;700',
                'Oswald' => '400;500;700',
                'Outfit' => '100;200;300;400;500;600;700;800;900',
                'Oxygen' => '400;700',
                'PT Serif' => '400;700',
                'Pacifico' => '400',
                'Parisienne' => '400',
                'Petit Formal Script' => '400',
                'Philosopher' => '400;700',
                'Playfair Display' => '400;500;600;700;800;900',
                'Plus Jakarta Sans' => '400;500;700',
                'Poiret One' => '400',
                'Poppins' => '100;200;300;400;500;600;700;800;900',
                'Prata' => '400',
                'Prompt' => '100;200;300;400;500;600;700;800;900',
                'Public Sans' => '100;200;300;400;500;600;700;800;900',
                'Questrial' => '400',
                'Quicksand' => '400;500;700',
                'Quintessential' => '400',
                'Raleway' => '400;500;700',
                'Red Hat Display' => '300;400;500;600;700;800;900',
                'Roboto' => '400;500;700',
                'Roboto Mono' => '100;200;300;400;500;600;700',
                'Roboto Slab' => '100;200;300;400;500;600;700;800;900',
                'Rubik' => '400;500;700',
                'Sacramento' => '400',
                'Satisfy' => '400',
                'Sora' => '100;200;300;400;500;600;700;800',
                'Sorts Mill Goudy' => '400',
                'Source Code Pro' => '200;300;400;500;600;700;800;900',
                'Source Sans 3' => '200;300;400;500;600;700;800;900',
                'Space Grotesk' => '400;500;700',
                'Tangerine' => '400;700',
                'The Nautigal' => '400;700',
                'Titillium Web' => '200;300;400;600;700;900',
                'Ubuntu' => '300;400;500;700',
                'Unna' => '400;700',
                'Urbanist' => '400;500;700',
                'Viaoda Libre' => '400',
                'WindSong' => '400;500',
                'Work Sans' => '400;500;700',
                'Yeseva One' => '400',
            ];

            $source = html_entity_decode($html . "\n" . $css . "\n" . $editorJson, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $selected = ['Inter'];

            foreach ($fontWeights as $family => $weights) {
                if ($family === 'Inter') {
                    continue;
                }

                if (stripos($source, $family) !== false) {
                    $selected[] = $family;
                }

                if (count($selected) >= 100) {
                    break;
                }
            }

            $families = array_map(
                static fn (string $family): string => 'family=' . str_replace('%20', '+', rawurlencode($family)) . ':wght@' . $fontWeights[$family],
                array_values(array_unique($selected))
            );

            return 'https://fonts.googleapis.com/css2?' . implode('&', $families) . '&display=swap';
        }
    }

    if (! function_exists('aa_public_bunny_font_urls')) {
        function aa_public_bunny_font_urls(string $html, string $css, string $editorJson): array
        {
            return [];
        }
    }

    $isPreviewPage = ! empty($isPreview);
    $hasPublishedSnapshot = ! $isPreviewPage
        && array_key_exists('published_html', $page)
        && $page['published_html'] !== null
        && $page['published_html'] !== '';

    $renderHtml = aa_normalize_editor_html($hasPublishedSnapshot ? (string) ($page['published_html'] ?? '') : (string) ($page['html'] ?? ''));
    $renderCss = $hasPublishedSnapshot ? (string) ($page['published_css'] ?? '') : (string) ($page['css'] ?? '');
    $renderJs = aa_sanitize_render_js($hasPublishedSnapshot ? (string) ($page['published_js'] ?? '') : (string) ($page['js'] ?? ''));
    $renderEditorJson = $hasPublishedSnapshot
        ? (string) ($page['published_editor_json'] ?? $page['editor_json'] ?? $page['grapesjs_json'] ?? '')
        : (string) ($page['editor_json'] ?? $page['grapesjs_json'] ?? $page['published_editor_json'] ?? '');
    $fabricPayload = aa_get_fabric_payload($renderEditorJson);
    $guestbookConfig = aa_guestbook_config($fabricPayload);
    $guestbookSampleEntries = [
        [
            'guest_name' => 'Tamu Undangan',
            'attendance' => 'hadir',
            'message' => 'Selamat menempuh hidup baru, semoga menjadi keluarga sakinah mawaddah warahmah.',
            'sticker' => 'sticker003.gif',
            'sticker_url' => aa_asset_url('assets/stiker/sticker003.gif'),
            'created_at' => 'Preview',
        ],
        [
            'guest_name' => 'Sahabat',
            'attendance' => 'hadir',
            'message' => 'Happy wedding! Semoga lancar sampai hari H.',
            'sticker' => 'sticker007.gif',
            'sticker_url' => aa_asset_url('assets/stiker/sticker007.gif'),
            'created_at' => 'Preview',
        ],
        [
            'guest_name' => 'Keluarga',
            'attendance' => 'ragu',
            'message' => 'Turut berbahagia, semoga selalu diberkahi.',
            'sticker' => 'sticker008.gif',
            'sticker_url' => aa_asset_url('assets/stiker/sticker008.gif'),
            'created_at' => 'Preview',
        ],
    ];
    $guestbookEntriesForRender = ($isPreviewPage && empty($guestbookEntries ?? []))
        ? $guestbookSampleEntries
        : ($guestbookEntries ?? []);
    $openingConfig = aa_opening_config($fabricPayload, $page ?? []);
    $openingSource = is_array($fabricPayload['opening'] ?? null) ? $fabricPayload['opening'] : [];
    $openingObjects = is_array($openingSource['objects'] ?? null) ? $openingSource['objects'] : [];
    $openingArtboard = is_array($openingSource['artboard'] ?? null) ? $openingSource['artboard'] : [];
    $openingCanvasPayload = [
        'objects' => $openingObjects,
        'background' => (string) ($openingSource['background'] ?? '#0f766e'),
        'backgroundColor' => (string) ($openingSource['background'] ?? '#0f766e'),
        'artboard' => [
            'width' => max(1, (int) ($openingArtboard['width'] ?? 1080)),
            'height' => max(1, (int) ($openingArtboard['height'] ?? 1920)),
        ],
    ];
    $hasCustomOpeningCanvas = ($openingConfig['mode'] === 'custom')
        && ! empty($openingCanvasPayload['objects']);
    $hasCustomOpeningButton = false;
    if ($hasCustomOpeningCanvas) {
        foreach ($openingCanvasPayload['objects'] as $openingObject) {
            if (($openingObject['customType'] ?? '') === 'opening-button') {
                $hasCustomOpeningButton = true;
                break;
            }
        }
    }
    $showOpeningModal = empty($isPreview)
        && ! empty($openingConfig['enabled'])
        && $hasCustomOpeningCanvas
        && $hasCustomOpeningButton;

    if ($fabricPayload !== null) {
        if (trim($renderHtml) === '') {
            $renderHtml = aa_fabric_fallback_html($fabricPayload);
        }

        if (trim($renderCss) === '') {
            $renderCss = aa_fabric_fallback_css();
        }

        if (trim($renderJs) === '') {
            $renderJs = aa_fabric_fallback_js($fabricPayload);
        }
    }
    $renderHtml = aa_normalize_fabric_artboard_html($renderHtml);
    $hasFabricArtboard = stripos($renderHtml, 'aa-fabric-artboard') !== false;
    $fabricScaleCss = $hasFabricArtboard ? aa_fabric_scale_override_css() : '';
    $fontUrl = aa_public_font_url($renderHtml, $renderCss, $renderEditorJson);
    $request = function_exists('service') ? service('request') : null;
    $inviteName = trim((string) (
        ($request ? $request->getGet('to') : null)
        ?? ($request ? $request->getGet('tamu') : null)
        ?? ($request ? $request->getGet('invite') : null)
        ?? ($request ? $request->getGet('guest') : null)
        ?? ''
    ));
    $inviteName = $inviteName !== '' ? mb_substr(strip_tags($inviteName), 0, 120) : 'Tamu Undangan';
    $eventTitle = trim((string) (($page['title'] ?? '') ?: ($page['seo_title'] ?? '') ?: 'Ada Acara'));
    $eventTitle = $eventTitle !== '' ? mb_substr(strip_tags($eventTitle), 0, 140) : 'Ada Acara';
    $metaTitle = trim((string) (($page['seo_title'] ?? '') ?: ($page['title'] ?? '') ?: 'Ada Acara'));
    $metaTitle = $metaTitle !== '' ? mb_substr(strip_tags($metaTitle), 0, 140) : 'Ada Acara';
    $metaDescription = trim(strip_tags((string) ($page['seo_description'] ?? '')));
    if ($metaDescription === '') {
        $metaDescription = 'Dengan penuh hormat, kami mengundang Anda untuk hadir dan berbagi doa. Buka undangan untuk melihat detail acara.';
    }
    $metaDescription = mb_substr($metaDescription, 0, 220);
    $metaSlug = trim((string) ($page['slug'] ?? ''));
    $metaUrl = $metaSlug !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $metaSlug)
        ? site_url('u/' . $metaSlug)
        : current_url();
    $metaImage = trim((string) ($page['og_image'] ?? ''));
    $metaImageIsFallback = false;
    if ($metaImage === '') {
        $metaImage = aa_asset_url('assets/img/og-default.png');
        $metaImageIsFallback = true;
    } elseif (! preg_match('#^https?://#i', $metaImage)) {
        $metaImage = site_url(ltrim($metaImage, '/'));
    }
    $metaImagePlain = preg_replace('/[?#].*$/', '', $metaImage) ?? $metaImage;
    $metaImageType = match (strtolower(pathinfo($metaImagePlain, PATHINFO_EXTENSION))) {
        'jpg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        default => 'image/png',
    };
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
    (function () {
        try {
            var nav = window.navigator || {};
            var connection = nav.connection || nav.mozConnection || nav.webkitConnection || null;
            var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var cores = Number(nav.hardwareConcurrency || 4);
            var memory = Number(nav.deviceMemory || 4);
            var connectionType = connection && String(connection.effectiveType || '').toLowerCase();
            var slowConnection = connectionType === 'slow-2g' || connectionType === '2g';
            var constrainedDevice = memory > 0 && memory <= 2;
            var weakCpuAndMemory = cores <= 2 && memory <= 3;
            var liteMode = reducedMotion || slowConnection || constrainedDevice || weakCpuAndMemory;

            window.AdaAcaraLiteMode = liteMode;
            if (liteMode) {
                document.documentElement.classList.add('aa-lite-mode');
            }
        } catch (error) {
            window.AdaAcaraLiteMode = false;
        }
    })();
    </script>
    <script src="<?= esc(aa_asset_url('assets/js/lightweight-public-renderer.js'), 'attr') ?>"></script>
    <script src="https://unpkg.com/lenis@1.1.20/dist/lenis.min.js"></script>
    <title><?= esc($metaTitle) ?></title>
    <link rel="canonical" href="<?= esc($metaUrl, 'attr') ?>">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php if ($hasFabricArtboard): ?>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <?php endif ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link href="<?= esc($fontUrl, 'attr') ?>" rel="stylesheet" media="print" onload="this.media='all'">
    <link href="<?= esc(site_url('custom-fonts.css'), 'attr') ?>" rel="stylesheet">
    <noscript>
        <link href="<?= esc($fontUrl, 'attr') ?>" rel="stylesheet">
    </noscript>
    <meta name="description" content="<?= esc($metaDescription, 'attr') ?>">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AdaAcara">
    <meta property="og:url" content="<?= esc($metaUrl, 'attr') ?>">
    <meta property="og:title" content="<?= esc($metaTitle, 'attr') ?>">
    <meta property="og:description" content="<?= esc($metaDescription, 'attr') ?>">
    <meta property="og:image" content="<?= esc($metaImage, 'attr') ?>">
    <meta property="og:image:secure_url" content="<?= esc($metaImage, 'attr') ?>">
    <meta property="og:image:type" content="<?= esc($metaImageType, 'attr') ?>">
    <?php if ($metaImageIsFallback): ?>
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif ?>
    <meta property="og:image:alt" content="<?= esc($metaTitle, 'attr') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($metaTitle, 'attr') ?>">
    <meta name="twitter:description" content="<?= esc($metaDescription, 'attr') ?>">
    <meta name="twitter:image" content="<?= esc($metaImage, 'attr') ?>">
    <style>
    html,
    body {
        overscroll-behavior-y: none;
    }
    @media (pointer: coarse), (max-width: 820px) {
            html,
            body {
                overflow-x: hidden;
                overscroll-behavior-y: auto !important;
                touch-action: auto !important;
                scroll-behavior: smooth;
            }

            .aa-fabric-page {
                scroll-snap-type: y proximity;
            }

            .aa-fabric-page-section {
                scroll-snap-align: start;
                scroll-snap-stop: normal;
            }

            .aa-fabric-artboard,
            .aa-fabric-artboard canvas,
            .aa-fabric-artboard .canvas-container {
                touch-action: pan-y pinch-zoom !important;
            }
        }  
    .aa-fabric-guestbook-control {
        box-sizing: border-box;
        min-height: 5vh;
    }

    .aa-fabric-selected-sticker {
        top: 0px !important;
        left: 0vw !important;
    }

    html {
        scroll-behavior: smooth;
    }

    html.aa-lite-mode {
        scroll-behavior: auto;
    }

    body {
        margin: 0;
        min-width: 320px;
        overflow-x: hidden;
    }

    img,
    video,
    iframe {
        max-width: 100%;
    }

    .aa-fabric-watermark {
        display: flex;
        align-items: center;
        justify-content: center;
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 18px 16px;
        font: 800 12px Inter, Arial, sans-serif;
    }

    .aa-fabric-watermark img {
        width: 168px;
        max-height: 42px;
        object-fit: cover;
    }

    @media (max-width: 420px) {
        .aa-fabric-watermark {
            padding: 16px 14px;
        }
    }

    @keyframes aaFloatSoft {

        0%,
        100% {
            transform: translateY(0) rotate(var(--aa-rotate, 0deg));
        }

        50% {
            transform: translateY(-10px) rotate(var(--aa-rotate, 0deg));
        }
    }

    @keyframes aaPulseSoft {

        0%,
        100% {
            transform: scale(1) rotate(var(--aa-rotate, 0deg));
        }

        50% {
            transform: scale(1.06) rotate(var(--aa-rotate, 0deg));
        }
    }

    @keyframes aaFadeUp {
        from {
            opacity: 0;
            transform: translateY(18px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes aaZoomIn {
        from {
            opacity: 0;
            transform: scale(.94);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .free-layer {
        touch-action: none;
    }

    section:has(.free-layer),
    div:has(.free-layer),
    header:has(.free-layer),
    main:has(.free-layer),
    article:has(.free-layer),
    footer:has(.free-layer) {
        position: relative;
        overflow: hidden;
    }

    .aa-guestbook {
        padding: 72px 20px;
        background: var(--aa-gb-bg, #f8fafc);
        color: var(--aa-gb-text, #101828);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .aa-guestbook-wrap {
        max-width: 920px;
        margin: 0 auto;
    }

    .aa-guestbook-head {
        margin-bottom: 32px;
        text-align: center;
    }

    .aa-guestbook-head p {
        margin: 0 0 10px;
        color: var(--aa-gb-accent, #0f766e);
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .aa-guestbook-head h2 {
        margin: 0;
        font-size: clamp(30px, 5vw, 46px);
        line-height: 1.1;
    }

    .aa-guestbook-head .aa-guestbook-subtitle {
        max-width: 580px;
        margin: 14px auto 0;
        color: var(--aa-gb-muted, #667085);
        line-height: 1.7;
        text-transform: none;
        letter-spacing: 0;
        font-size: 16px;
        font-weight: 500;
    }

    .aa-guestbook-form {
        display: grid;
        gap: 14px;
        border: 1px solid #e4e7ec;
        border-radius: var(--aa-gb-radius, 22px);
        background: var(--aa-gb-card, #ffffff);
        padding: 22px;
        box-shadow: 0 18px 50px rgba(16, 24, 40, .06);
    }

    .aa-guestbook-label {
        display: grid;
        gap: 8px;
        font-weight: 700;
    }

    .aa-guestbook-input,
    .aa-guestbook-select,
    .aa-guestbook-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d0d5dd;
        border-radius: 12px;
        padding: 12px 14px;
        background: #ffffff;
        color: #101828;
        font: inherit;
    }

    .aa-guestbook-textarea {
        min-height: 116px;
        resize: vertical;
    }

    .aa-sticker-control {
        position: relative;
    }

    .aa-sticker-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .aa-sticker-button,
    .aa-guestbook-submit {
        border: 0;
        border-radius: 14px;
        font: inherit;
        font-weight: 800;
        cursor: pointer;
    }

    .aa-sticker-button {
        background: #ecfdf5;
        color: var(--aa-gb-accent, #0f766e);
        padding: 10px 14px;
    }

    .aa-guestbook-submit {
        background: var(--aa-gb-accent, #0f766e);
        color: #ffffff;
        padding: 14px 18px;
    }

    .aa-sticker-popover {
        position: absolute;
        left: 0;
        bottom: calc(100% + 10px);
        z-index: 10;
        display: none;
        width: min(360px, 92vw);
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
        border: 1px solid #e4e7ec;
        border-radius: 18px;
        background: #ffffff;
        padding: 12px;
        box-shadow: 0 20px 60px rgba(16, 24, 40, .16);
    }

    .aa-sticker-popover.is-open {
        display: grid;
    }

    .aa-sticker-choice {
        border: 1px solid #e4e7ec;
        border-radius: 14px;
        background: #f8fafc;
        padding: 6px;
        cursor: pointer;
    }

    .aa-sticker-choice.is-active {
        border-color: #0f766e;
        background: #ccfbf1;
    }

    .aa-sticker-choice img,
    .aa-selected-sticker img,
    .aa-comment-sticker {
        display: block;
        width: 54px;
        height: 54px;
        object-fit: contain;
    }

    .aa-selected-sticker {
        display: none;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        background: #f1f5f9;
        padding: 6px 10px;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
    }

    .aa-selected-sticker.is-visible {
        display: inline-flex;
    }

    .aa-comment-list {
        display: grid;
        gap: 12px;
        max-height: var(--aa-gb-max-height, 380px);
        overflow-y: auto;
        margin-top: 24px;
        padding-right: 6px;
        scroll-behavior: smooth;
    }

    .aa-comment-card,
    .aa-comment-empty {
        border: 1px solid #e4e7ec;
        border-radius: 18px;
        background: var(--aa-gb-card, #ffffff);
        padding: 18px;
    }

    .aa-comment-empty {
        border-style: dashed;
        color: var(--aa-gb-muted, #667085);
        text-align: center;
    }

    .aa-comment-meta {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .aa-comment-meta h3 {
        margin: 0;
        font-size: 18px;
    }

    .aa-comment-meta p,
    .aa-comment-card time {
        margin: 6px 0 0;
        color: var(--aa-gb-muted, #667085);
        font-size: 14px;
    }

    .aa-comment-card time {
        margin: 0;
        color: #98a2b3;
        font-size: 13px;
    }

    .aa-comment-body {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }

    .aa-comment-body p {
        margin: 0;
        color: var(--aa-gb-text, #344054);
        line-height: 1.7;
        white-space: pre-wrap;
    }

    .aa-guestbook-alert {
        display: none;
        border-radius: 14px;
        padding: 12px 14px;
        font-weight: 700;
    }

    .aa-guestbook-alert.is-visible {
        display: block;
    }

    .aa-guestbook-alert.is-error {
        border: 1px solid #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .aa-guestbook-alert.is-success {
        border: 1px solid #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .aa-opening-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: grid;
        place-items: center;
        padding: 22px;
        background:
            radial-gradient(circle at 18% 22%, rgba(255, 255, 255, .18), transparent 28%),
            radial-gradient(circle at 84% 78%, rgba(255, 255, 255, .12), transparent 30%),
            rgba(0, 0, 0, .34);
        backdrop-filter: blur(1px) saturate(115%);
        -webkit-backdrop-filter: blur(1px) saturate(115%);
        opacity: 0;
        pointer-events: none;
        transition: opacity 1.2s ease;
    }

    .aa-opening-modal.is-visible {
        opacity: 1;
        pointer-events: auto;
    }

    .aa-opening-modal.is-leaving {
        opacity: 0;
        pointer-events: none;
    }

    .aa-opening-modal.is-leaving.aa-opening-exit-slide-up .aa-opening-card,
    .aa-opening-modal.is-leaving.aa-opening-exit-elegant-lift .aa-opening-card {
        transform: translateY(-42px) scale(.98);
    }

    .aa-opening-modal.is-leaving.aa-opening-exit-zoom-out .aa-opening-card {
        transform: scale(.86);
    }

    .aa-opening-modal.is-leaving.aa-opening-exit-blur-fade .aa-opening-card {
        filter: blur(10px);
        transform: scale(.98);
    }

    .aa-opening-modal.is-leaving.aa-opening-exit-curtain .aa-opening-card {
        transform: translateY(-18px) scaleY(.92);
        transform-origin: top center;
    }

    .aa-opening-card {
        position: relative;
        width: min(88vw, 430px);
        min-height: 630px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .34);
        border-radius: 30px;
        background:
            linear-gradient(145deg, rgba(255, 255, 255, .24), rgba(255, 255, 255, .08)),
            rgba(17, 24, 39, .18);
        box-shadow: 0 30px 90px rgba(0, 0, 0, .34);
        backdrop-filter: blur(24px) saturate(135%);
        -webkit-backdrop-filter: blur(24px) saturate(135%);
        color: #ffffff;
        font-family: "Philosopher";
        transform: translateY(18px) scale(.96);
        transition: opacity 1.2s ease, transform 1.2s cubic-bezier(.2, .8, .2, 1);
    }

    .aa-opening-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .26), transparent 34%),
            linear-gradient(0deg, rgba(0, 0, 0, .32), transparent 58%);
        pointer-events: none;
    }

    .aa-opening-card.is-custom {
        display: grid;
        place-items: stretch;
        background: rgba(17, 24, 39, .16);
    }

    .aa-opening-card.is-custom::before {
        display: none;
    }

    .aa-opening-custom-stage {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
    }

    .aa-opening-custom-stage canvas {
        display: block;
        width: 100% !important;
        height: 100% !important;
    }

    .aa-opening-button.is-custom-overlay {
        position: absolute;
        left: 50%;
        bottom: 28px;
        z-index: 3;
        transform: translateX(-50%);
        background: rgba(15, 118, 110, .92);
        box-shadow: 0 18px 44px rgba(15, 23, 42, .22);
    }

    .aa-opening-button.is-custom-overlay:hover {
        transform: translateX(-50%) translateY(-1px);
    }

    .aa-opening-custom-hotspot {
        position: absolute;
        z-index: 4;
        display: block;
        border: 0;
        border-radius: 999px;
        background: transparent;
        padding: 0;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .aa-opening-custom-hotspot:focus-visible {
        outline: 3px solid rgba(255, 255, 255, .72);
        outline-offset: 3px;
    }

    .aa-opening-category-wedding .aa-opening-card,
    .aa-opening-category-pernikahan .aa-opening-card {
        background:
            radial-gradient(circle at 80% 16%, rgba(251, 191, 36, .22), transparent 30%),
            linear-gradient(145deg, rgba(255, 247, 237, .32), rgba(255, 255, 255, .09)),
            rgba(89, 37, 37, .2);
    }

    .aa-opening-category-islamic .aa-opening-card,
    .aa-opening-category-ramadhan .aa-opening-card,
    .aa-opening-category-eid .aa-opening-card {
        background:
            radial-gradient(circle at 82% 18%, rgba(45, 212, 191, .2), transparent 28%),
            linear-gradient(145deg, rgba(236, 253, 245, .26), rgba(255, 255, 255, .08)),
            rgba(6, 78, 59, .28);
    }

    .aa-opening-category-birthday .aa-opening-card,
    .aa-opening-category-ulang-tahun .aa-opening-card {
        background:
            radial-gradient(circle at 18% 24%, rgba(244, 114, 182, .24), transparent 28%),
            radial-gradient(circle at 82% 18%, rgba(96, 165, 250, .18), transparent 30%),
            rgba(76, 29, 149, .2);
    }

    .aa-opening-category-corporate .aa-opening-card,
    .aa-opening-category-seminar .aa-opening-card {
        background:
            linear-gradient(145deg, rgba(240, 249, 255, .24), rgba(255, 255, 255, .08)),
            rgba(15, 23, 42, .24);
    }

    .aa-opening-modal.is-visible .aa-opening-card {
        transform: translateY(0) scale(1);
    }

    .aa-opening-modal.is-leaving .aa-opening-card {
        opacity: 0;
        transform: translateY(10px) scale(.97);
    }

    .aa-opening-event {
        position: absolute;
        top: 24px;
        right: 24px;
        z-index: 1;
        max-width: 58%;
        margin: 0;
        color: rgba(255, 255, 255, .92);
        font-size: 13px;
        font-weight: 900;
        line-height: 1.35;
        text-align: right;
        letter-spacing: .08em;
        text-transform: uppercase;
        text-shadow: 0 10px 30px rgba(0, 0, 0, .32);
    }

    .aa-opening-main {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: grid;
        place-items: center;
        padding: 32px;
    }

    .aa-opening-button {
        display: inline-flex;
        min-height: 35px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, .48);
        border-radius: 999px;
        background: rgba(255, 255, 255, .22);
        color: #ffffff;
        padding: 0 20px;
        font: inherit;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: capitalize;
        cursor: pointer;
        box-shadow: 0 18px 44px rgba(0, 0, 0, .28), inset 0 1px 0 rgba(255, 255, 255, .32);
        transition: transform .2s ease, background .2s ease, border-color .2s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .aa-opening-button:hover {
        border-color: rgba(255, 255, 255, .72);
        background: rgba(255, 255, 255, .3);
        transform: translateY(-1px);
    }

    .aa-opening-guest {
        position: absolute;
        left: 28px;
        bottom: 28px;
        z-index: 1;
        max-width: calc(100% - 56px);
    }

    .aa-opening-label {
        margin: 0 0 8px;
        color: rgba(255, 255, 255, .72);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .aa-opening-name {
        display: block;
        color: #ffffff;
        font-size: clamp(18px, 3vw, 55px);
        font-weight: 900;
        line-height: .98;
        text-shadow: 0 12px 42px rgba(0, 0, 0, .36);
        word-break: break-word;
    }

    html.aa-lite-mode .aa-opening-modal {
        background: rgba(0, 0, 0, .34);
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        transition-duration: .16s;
    }

    html.aa-lite-mode .aa-opening-card {
        box-shadow: 0 16px 42px rgba(0, 0, 0, .24);
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        transition-duration: .18s;
        transition-timing-function: ease-out;
    }

    html.aa-lite-mode .aa-opening-card::before {
        background: linear-gradient(0deg, rgba(0, 0, 0, .26), transparent 58%);
    }

    html.aa-lite-mode .aa-opening-modal.is-leaving.aa-opening-exit-blur-fade .aa-opening-card {
        filter: none;
    }

    html.aa-lite-mode .aa-opening-name {
        text-shadow: 0 6px 18px rgba(0, 0, 0, .28);
    }

    html.aa-lite-mode .aa-opening-button {
        transition-duration: .12s;
    }

    @media (max-width: 480px) {
        .aa-opening-card {
            border-radius: 28px;
        }

        .aa-opening-event {
            top: 22px;
            right: 22px;
            max-width: 64%;
            font-size: 11px;
        }

        .aa-opening-guest {
            left: 24px;
            bottom: 24px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .aa-opening-modal,
        .aa-opening-card,
        .aa-opening-button {
            transition: none;
        }
    }

    <?=$renderCss ?><?=$fabricScaleCss ?>
    </style>
    <script>
        (function () {
            'use strict';

            if (!('serviceWorker' in navigator)) {
                return;
            }

            navigator.serviceWorker.getRegistrations()
                .then(function (registrations) {
                    return Promise.all(registrations.map(function (registration) {
                        return registration.unregister();
                    }));
                })
                .then(function () {
                    if (!window.caches || !caches.keys) {
                        return null;
                    }

                    return caches.keys().then(function (keys) {
                        return Promise.all(keys.map(function (key) {
                            return caches.delete(key);
                        }));
                    });
                });
        })();
    </script>
    <script>
    (function () {
        function isTextObject(obj) {
            return obj && ['text', 'i-text', 'textbox'].indexOf(obj.type) !== -1;
        }
        function isGuestbookObject(obj) {
            return obj && [
                'guest-name-input',
                'guest-attendance-select',
                'guest-message-textarea',
                'guest-sticker-picker',
                'guest-submit-button',
                'guest-comment-list'
            ].indexOf(obj.customType) !== -1;
        }
        function isGuestbookControlObject(obj) {
            if (!isGuestbookObject(obj)) return false;
            if (obj.formGroupId || obj.guestbookRole || obj.fieldName || obj.buttonText || obj.stickerSource || obj.maxLength || obj.options) {
                return true;
            }
            var children = obj && Array.isArray(obj.objects) ? obj.objects : [];
            return children.some(function (child) {
                return child && (child.name === 'guestbook-box' || child.name === 'guestbook-text');
            });
        }
        function isInteractiveObject(obj) {
            return obj && ['music-player', 'scroll-next-button', 'countdown-timer', 'photo-gallery', 'youtube-video'].indexOf(obj.customType) !== -1;
        }
        function getObjectAnimationName(obj) {
            return String(
                (obj && (obj.aaAnimation || obj.customAnimation || obj.animationPreset || obj.animation || obj.animationName)) ||
                'none'
            );
        }
        function aaGetAnimationDuration(obj, fallback) {
            var d = obj ? (obj.animationDuration != null ? obj.animationDuration : obj.aaAnimationDuration) : null;
            return d != null && isFinite(Number(d)) ? Math.max(50, Number(d)) : (fallback || 650);
        }
        function imageEffectCanvasFilter(image) {
            var preset = String((image && image.aaImageEffectPreset) || 'none');
            if (!preset || preset === 'none' || preset === 'opacity' || preset === 'shadow') return '';
            if (preset === 'brightness') return 'brightness(1.16)';
            if (preset === 'contrast') return 'contrast(1.22)';
            if (preset === 'saturation') return 'saturate(1.38)';
            if (preset === 'grayscale') return 'grayscale(1)';
            if (preset === 'sepia') return 'sepia(1)';
            if (preset === 'blur') return 'blur(2px)';
            if (preset === 'sharpen') return 'contrast(1.28) saturate(1.12)';
            if (preset === 'vintage') return 'sepia(.55) contrast(1.08) saturate(.82)';
            if (preset === 'soft-wedding') return 'brightness(1.08) contrast(.96) saturate(1.18) sepia(.08)';
            if (preset === 'clean-bright') return 'brightness(1.14) contrast(1.08) saturate(1.08)';
            if (preset === 'warm-editorial') return 'sepia(.18) brightness(1.06) contrast(1.12) saturate(1.14)';
            if (preset === 'film-matte') return 'sepia(.2) contrast(.92) saturate(.78) brightness(1.04)';
            if (preset === 'pastel-bloom') return 'brightness(1.1) contrast(.94) saturate(1.32) hue-rotate(-6deg)';
            if (preset === 'moody-luxe') return 'brightness(.88) contrast(1.22) saturate(.9) sepia(.08)';
            if (preset === 'classic-bw') return 'grayscale(1) contrast(1.18) brightness(1.04)';
            if (preset === 'dreamy-soft') return 'brightness(1.12) contrast(.9) saturate(1.12) blur(.75px)';
            if (preset === 'recolor-white') return 'grayscale(.35) brightness(1.34) contrast(.86) saturate(.68)';
            if (preset === 'recolor-black') return 'grayscale(1) brightness(.72) contrast(1.28)';
            if (preset === 'recolor-gold') return 'sepia(.55) saturate(1.45) hue-rotate(4deg) brightness(1.08) contrast(1.04)';
            if (preset === 'recolor-teal') return 'sepia(.18) saturate(1.35) hue-rotate(135deg) brightness(.96) contrast(1.06)';
            if (preset === 'recolor-rose') return 'sepia(.22) saturate(1.35) hue-rotate(300deg) brightness(1.04) contrast(.98)';
            if (preset === 'recolor-slate') return 'grayscale(.65) sepia(.12) saturate(.7) hue-rotate(170deg) brightness(.92) contrast(1.08)';
            if (preset === 'remove-color') return 'saturate(.2) contrast(1.12)';
            return '';
        }

        if (!window.openGalleryLightbox) {
            window.openGalleryLightbox = function(url) {
                url = String(url || '').trim();
                if (!url) return;
                var lightbox = document.querySelector('.aa-fabric-lightbox');
                if (!lightbox) {
                    lightbox = document.createElement('div');
                    lightbox.className = 'aa-fabric-lightbox';
                    lightbox.innerHTML = '<button type="button">Close</button><img src="" alt="Preview gallery">';
                    document.body.appendChild(lightbox);
                    lightbox.addEventListener('click', function(event) {
                        if (event.target === lightbox || event.target.tagName === 'BUTTON') lightbox.classList.remove('is-open');
                    });
                    document.addEventListener('keydown', function(event) {
                        if (event.key === 'Escape') lightbox.classList.remove('is-open');
                    });
                }
                lightbox.querySelector('img').src = url;
                lightbox.classList.add('is-open');
            };
        }

        if (!window.aaBindGalleryLightboxTrigger) {
            window.aaBindGalleryLightboxTrigger = function(el, getUrl) {
                if (!el || typeof getUrl !== 'function' || el.__aaGalleryTapBound) return;
                el.__aaGalleryTapBound = true;

                var startX = 0;
                var startY = 0;
                var startTime = 0;
                var recentOpenUntil = 0;
                var TAP_MOVE_LIMIT = 14;
                var TAP_TIME_LIMIT = 850;

                function eventPoint(event) {
                    var point = event.changedTouches && event.changedTouches[0] || event.touches && event.touches[0] || event;
                    return {
                        x: Number(point.clientX) || 0,
                        y: Number(point.clientY) || 0
                    };
                }

                function rememberStart(event) {
                    var point = eventPoint(event);
                    startX = point.x;
                    startY = point.y;
                    startTime = Date.now();
                }

                function isTap(event) {
                    var point = eventPoint(event);
                    return Math.abs(point.x - startX) <= TAP_MOVE_LIMIT &&
                        Math.abs(point.y - startY) <= TAP_MOVE_LIMIT &&
                        (Date.now() - startTime) <= TAP_TIME_LIMIT;
                }

                function openFromTap(event) {
                    if (!isTap(event)) return;
                    var url = getUrl();
                    if (!url) return;
                    recentOpenUntil = Date.now() + 700;
                    if (event.cancelable) event.preventDefault();
                    event.stopPropagation();
                    window.openGalleryLightbox(url);
                }

                if (window.PointerEvent) {
                    el.addEventListener('pointerdown', rememberStart, { passive: true });
                    el.addEventListener('pointerup', openFromTap);
                } else {
                    el.addEventListener('touchstart', rememberStart, { passive: true });
                    el.addEventListener('touchend', openFromTap);
                }

                el.addEventListener('click', function(event) {
                    if (Date.now() < recentOpenUntil) {
                        if (event.cancelable) event.preventDefault();
                        event.stopPropagation();
                        return;
                    }
                    var url = getUrl();
                    if (!url) return;
                    window.openGalleryLightbox(url);
                });
            };
        }

        function getBoundingRectForObject(obj) {
            var scaleX = obj.scaleX || 1;
            var scaleY = obj.scaleY || 1;
            var w = obj.width * scaleX;
            var h = obj.height * scaleY;
            var left = obj.left - (obj.originX === 'center' ? w / 2 : 0);
            var top = obj.top - (obj.originY === 'center' ? h / 2 : 0);
            return {
                left: left,
                top: top,
                width: w,
                height: h
            };
        }

        function applyOverlayAnimation(el, object) {
            var animationName = getObjectAnimationName(object);
            if (!animationName || animationName === 'none') return;
            var safeName = String(animationName).toLowerCase().replace(/[^a-z0-9-]/g, '');
            var duration = Number(object.aaAnimationDuration != null ? object.aaAnimationDuration : object.animationDuration);
            var delay = Number(object.aaAnimationDelay != null ? object.aaAnimationDelay : object.animationDelay);
            el.classList.add('aa-fabric-overlay-animated', 'aa-fabric-overlay-animation-waiting', 'aa-overlay-animation-' + safeName);
            el.dataset.aaOverlayAnimation = safeName;
            el.style.setProperty('--aa-overlay-animation-duration', (isFinite(duration) && duration > 0 ? duration : 900) + 'ms');
            el.style.setProperty('--aa-overlay-animation-delay', (isFinite(delay) && delay > 0 ? delay : 0) + 'ms');
        }

        window.tryRenderHybrid = function (pageData, index) {
            var canvasEl = document.getElementById('aaFabricPublicCanvas' + index);
            if (!canvasEl) return false;
            if (!pageData || !Array.isArray(pageData.objects)) return false;

            var artboardEl = canvasEl.closest('.aa-fabric-artboard');
            if (!artboardEl) return false;

            // Check compatibility
            var canRender = true;
            var supportedHybridTypes = {
                image: true,
                rect: true,
                circle: true,
                path: true,
                text: true,
                'i-text': true,
                textbox: true
            };
            if (Array.isArray(pageData.objects)) {
                for (var i = 0; i < pageData.objects.length; i++) {
                    var obj = pageData.objects[i];
                    if (!obj) continue;
                    if (obj.visible === false || obj.__aaSkipObject === true || isGuestbookControlObject(obj) || isInteractiveObject(obj)) {
                        continue;
                    }
                    if (!supportedHybridTypes[obj.type]) {
                        canRender = false;
                        break;
                    }
                    if (obj.clipPath || (obj.styles && Object.keys(obj.styles).length > 0)) {
                        canRender = false;
                        break;
                    }
                }
            }
            if (!canRender) return false;

            // Hide canvas
            canvasEl.style.display = 'none';

            // Set up mock canvas object for the overlays
            var width = (pageData.artboard && pageData.artboard.width) || 1080;
            var height = (pageData.artboard && pageData.artboard.height) || 1920;

            var mockCanvas = {
                lowerCanvasEl: canvasEl,
                getObjects: function () { return pageData.objects; },
                getWidth: function () { return width; },
                getHeight: function () { return height; },
                getElement: function () { return canvasEl; },
                requestRenderAll: function () {},
                renderAll: function () {}
            };

            // Prepare JSON objects with mock methods
            pageData.objects.forEach(function (obj) {
                if (!obj) return;
                obj.getBoundingRect = function () { return getBoundingRectForObject(obj); };
                if (obj.objects && Array.isArray(obj.objects)) {
                    obj.getObjects = function () { return obj.objects; };
                    obj.objects.forEach(function (child) {
                        if (child) child.getBoundingRect = function () { return getBoundingRectForObject(child); };
                    });
                } else {
                    obj.getObjects = function () { return []; };
                }
            });

            // Create hybrid container
            var container = document.createElement('div');
            container.className = 'aa-fabric-hybrid-container';
            container.style.position = 'absolute';
            container.style.top = '0';
            container.style.left = '0';
            container.style.width = width + 'px';
            container.style.height = height + 'px';
            container.style.transformOrigin = 'top left';
            container.style.overflow = 'hidden';
            container.style.pointerEvents = 'none';

            // Render page objects
            pageData.objects.forEach(function (obj) {
                if (!obj || obj.visible === false || obj.__aaSkipObject === true) return;
                if (isGuestbookControlObject(obj) || isInteractiveObject(obj)) {
                    return;
                }

                var el = null;

                if (isTextObject(obj)) {
                    el = document.createElement('div');
                    el.className = 'aa-hybrid-text';
                    el.textContent = obj.text || '';
                    el.style.fontFamily = obj.fontFamily || 'sans-serif';
                    el.style.fontSize = (obj.fontSize || 40) + 'px';
                    el.style.fontWeight = obj.fontWeight || 'normal';
                    el.style.fontStyle = obj.fontStyle || 'normal';
                    el.style.color = obj.fill || '#000000';
                    el.style.textAlign = obj.textAlign || 'left';
                    el.style.lineHeight = obj.lineHeight || 1.16;
                    el.style.whiteSpace = 'pre-wrap';
                    el.style.wordBreak = 'break-word';
                    el.style.letterSpacing = obj.charSpacing ? (obj.charSpacing / 1000) + 'em' : 'normal';
                    if (obj.shadow) {
                        var s = obj.shadow;
                        el.style.textShadow = (s.offsetX || 0) + 'px ' + (s.offsetY || 0) + 'px ' + (s.blur || 0) + 'px ' + (s.color || 'rgba(0,0,0,0.3)');
                    }
                } else if (obj.type === 'image') {
                    el = document.createElement('img');
                    el.className = 'aa-hybrid-image';
                    el.src = obj.src || (obj._element && obj._element.src) || '';
                    el.style.objectFit = 'cover';
                    el.style.maxWidth = 'none';
                    el.style.maxHeight = 'none';
                    el.loading = 'lazy';
                    el.decoding = 'async';
                    if (obj.borderRadius) {
                        el.style.borderRadius = obj.borderRadius + 'px';
                    }
                    var filter = imageEffectCanvasFilter(obj);
                    if (filter) {
                        el.style.filter = filter;
                    }
                } else if (obj.type === 'rect') {
                    el = document.createElement('div');
                    el.className = 'aa-hybrid-rect';
                    el.style.backgroundColor = obj.fill || 'transparent';
                    if (obj.stroke && obj.stroke !== 'transparent') {
                        el.style.border = (obj.strokeWidth || 1) + 'px solid ' + obj.stroke;
                    }
                    if (obj.rx) el.style.borderRadius = obj.rx + 'px';
                } else if (obj.type === 'circle') {
                    el = document.createElement('div');
                    el.className = 'aa-hybrid-circle';
                    el.style.backgroundColor = obj.fill || 'transparent';
                    if (obj.stroke && obj.stroke !== 'transparent') {
                        el.style.border = (obj.strokeWidth || 1) + 'px solid ' + obj.stroke;
                    }
                    el.style.borderRadius = '50%';
                } else if (obj.type === 'path') {
                    el = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    el.setAttribute('class', 'aa-hybrid-path');
                    var pathOffset = obj.pathOffset || { x: 0, y: 0 };
                    var minX = pathOffset.x - obj.width/2;
                    var minY = pathOffset.y - obj.height/2;
                    el.setAttribute('viewBox', minX + ' ' + minY + ' ' + obj.width + ' ' + obj.height);
                    var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.setAttribute('d', obj.path.map(function(p) { return p.join(' '); }).join(' '));
                    path.setAttribute('fill', obj.fill || 'none');
                    if (obj.stroke && obj.stroke !== 'transparent') {
                        path.setAttribute('stroke', obj.stroke);
                        path.setAttribute('stroke-width', obj.strokeWidth || 1);
                    }
                    el.appendChild(path);
                }

                if (el) {
                    el.style.position = 'absolute';
                    el.style.pointerEvents = 'auto';

                    var scaleX = obj.scaleX || 1;
                    var scaleY = obj.scaleY || 1;
                    var w = obj.width * scaleX;
                    var h = obj.height * scaleY;
                    var left = obj.left - (obj.originX === 'center' ? w / 2 : 0);
                    var top = obj.top - (obj.originY === 'center' ? h / 2 : 0);

                    el.style.left = left + 'px';
                    el.style.top = top + 'px';
                    el.style.width = w + 'px';
                    el.style.height = h + 'px';
                    if (obj.opacity !== undefined) el.style.opacity = obj.opacity;

                    var transformStr = obj.angle ? 'rotate(' + obj.angle + 'deg)' : 'rotate(0deg)';
                    el.style.transform = transformStr;
                    el.style.setProperty('--aa-overlay-base-transform', transformStr);

                    // Handle animations
                    applyOverlayAnimation(el, obj);

                    // Handle loop animations (CSS classes)
                    if (obj.aaLoopAnimation) {
                        el.classList.add('aa-overlay-animation-' + obj.aaLoopAnimation + '-loop');
                    }

                    // Interactivity (Hotspots)
                    if (obj.customType === 'gallery-photo' || obj.isGalleryPhoto === true || obj.galleryZoom === true) {
                        el.style.cursor = 'pointer';
                        el.classList.add('aa-fabric-gallery-hotspot');
                        window.aaBindGalleryLightboxTrigger(el, function () {
                            return obj.galleryImageSrc || obj.src || (obj._element && obj._element.src) || '';
                        });
                    }
                    if (obj.link) {
                        el.style.cursor = 'pointer';
                        el.addEventListener('click', function () {
                            window.open(obj.link, '_blank');
                        });
                    }

                    container.appendChild(el);
                }
            });

            // Add background WebGL particles if configured
            if (pageData.effects || pageData.aaHasBackgroundEffects) {
                var glCanvas = document.createElement('canvas');
                glCanvas.className = 'aa-hybrid-webgl-canvas';
                container.insertBefore(glCanvas, container.firstChild);
                initLiteWebGL(glCanvas);
            }

            artboardEl.appendChild(container);

            // Run native overlay builders
            if (window.setupGuestbookOverlay) {
                try { setupGuestbookOverlay(canvasEl, mockCanvas); } catch(e){}
            }
            if (window.setupInteractiveOverlay) {
                try { setupInteractiveOverlay(canvasEl, mockCanvas); } catch(e){}
            }

            // Trigger entrance animations when visible
            var triggerAnimations = function () {
                container.querySelectorAll('.aa-fabric-overlay-animation-waiting').forEach(function (node) {
                    node.classList.remove('aa-fabric-overlay-animation-waiting');
                });
            };

            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            triggerAnimations();
                            observer.disconnect();
                        }
                    });
                }, { threshold: 0.1 });
                observer.observe(artboardEl);
            } else {
                setTimeout(triggerAnimations, 500);
            }

            // Scale container dynamically
            var debounce = function (fn, ms) {
                var timer = null;
                return function () {
                    clearTimeout(timer);
                    timer = setTimeout(fn, ms);
                };
            };
            var resizeHybrid = function () {
                var currentWidth = artboardEl.clientWidth || artboardEl.offsetWidth;
                var scale = currentWidth / width;
                container.style.transform = 'scale(' + scale + ')';
            };
            resizeHybrid();
            window.addEventListener('resize', debounce(resizeHybrid, 100));

            if (artboardEl.classList.contains('is-rendering')) {
                artboardEl.classList.remove('is-rendering');
            }

            return true;
        };

        function initLiteWebGL(canvas) {
            var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) return;

            var ratio = window.devicePixelRatio || 1;
            var resize = function () {
                var w = canvas.clientWidth * ratio;
                var h = canvas.clientHeight * ratio;
                if (canvas.width !== w || canvas.height !== h) {
                    canvas.width = w;
                    canvas.height = h;
                    gl.viewport(0, 0, w, h);
                }
            };
            resize();
            window.addEventListener('resize', resize);

            var vsSource = 'attribute vec2 a_pos; void main() { gl_Position = vec4(a_pos, 0.0, 1.0); }';
            var fsSource = 'precision mediump float; uniform vec2 u_res; uniform float u_time; void main() { vec2 uv = gl_FragCoord.xy / u_res; float p = sin(uv.x * 20.0 + u_time) * cos(uv.y * 20.0 + u_time) * 0.5 + 0.5; gl_FragColor = vec4(1.0, 1.0, 1.0, p * 0.08); }';

            var createShader = function (type, src) {
                var shader = gl.createShader(type);
                gl.shaderSource(shader, src);
                gl.compileShader(shader);
                return shader;
            };

            var vs = createShader(gl.VERTEX_SHADER, vsSource);
            var fs = createShader(gl.FRAGMENT_SHADER, fsSource);
            var program = gl.createProgram();
            gl.attachShader(program, vs);
            gl.attachShader(program, fs);
            gl.linkProgram(program);

            var posAttr = gl.getAttribLocation(program, 'a_pos');
            var resUniform = gl.getUniformLocation(program, 'u_res');
            var timeUniform = gl.getUniformLocation(program, 'u_time');

            var buffer = gl.createBuffer();
            gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
            gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, -1, 1, 1, -1, 1, 1]), gl.STATIC_DRAW);

            var start = Date.now();
            var render = function () {
                if (!document.body.contains(canvas)) return;
                var time = (Date.now() - start) * 0.001;
                gl.useProgram(program);
                gl.enableVertexAttribArray(posAttr);
                gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
                gl.vertexAttribPointer(posAttr, 2, gl.FLOAT, false, 0, 0);
                gl.uniform2f(resUniform, canvas.width, canvas.height);
                gl.uniform1f(timeUniform, time);
                gl.drawArrays(gl.TRIANGLES, 0, 6);
                requestAnimationFrame(render);
            };
            render();
        }
    })();
    </script>
</head>

<body class="<?= $hasFabricArtboard ? 'aa-public-stabilizing' : '' ?>">
    <?php if ($showOpeningModal): ?>
    <div id="aaOpeningModal"
        class="aa-opening-modal aa-opening-category-<?= esc($openingConfig['category'], 'attr') ?> aa-opening-exit-<?= esc($openingConfig['exitAnimation'], 'attr') ?>"
        data-opening-exit="<?= esc($openingConfig['exitAnimation'], 'attr') ?>" role="dialog" aria-modal="true"
        <?= $hasCustomOpeningCanvas ? 'aria-label="Opening undangan"' : 'aria-labelledby="aaOpeningEventTitle"' ?>>
        <section class="aa-opening-card<?= $hasCustomOpeningCanvas ? ' is-custom' : '' ?>"
            <?= $hasCustomOpeningCanvas ? 'style="aspect-ratio:' . (int) $openingCanvasPayload['artboard']['width'] . '/' . (int) $openingCanvasPayload['artboard']['height'] . '"' : '' ?>>
            <?php if ($hasCustomOpeningCanvas): ?>
            <div class="aa-opening-custom-stage">
                <canvas id="aaOpeningFabricCanvas" aria-label="Opening undangan"></canvas>
            </div>
            <button id="aaOpeningButton"
                class="<?= $hasCustomOpeningButton ? 'aa-opening-custom-hotspot' : 'aa-opening-button is-custom-overlay' ?>"
                type="button" aria-label="Buka Undangan"><?= $hasCustomOpeningButton ? '' : 'Buka Undangan' ?></button>
            <?php else: ?>
            <p id="aaOpeningEventTitle" class="aa-opening-event"><?= esc($eventTitle) ?></p>
            <div class="aa-opening-main">
                <button id="aaOpeningButton" class="aa-opening-button" type="button">Buka Undangan</button>
            </div>
            <div class="aa-opening-guest">
                <p class="aa-opening-label">Undangan untuk</p>
                <strong class="aa-opening-name"><?= esc($inviteName) ?></strong>
            </div>
            <?php endif ?>
        </section>
    </div>
    <?php endif ?>

    <?= $renderHtml ?>
    <?php if (stripos($renderHtml, 'aa-fabric-watermark') === false): ?>
    <footer class="aa-fabric-watermark">
        <img src="<?= esc(aa_asset_url('assets/img/adaacara-logo.png'), 'attr') ?>" alt="AdaAcara.com" width="168"
            height="52" loading="lazy" decoding="async">
    </footer>
    <?php endif ?>

    <?php $showGuestbook = false; ?>

    <?php if ($showGuestbook): ?>
    <?php
        $statusLabels = [
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak hadir',
            'ragu' => 'Ragu',
        ];
        $stickerFiles = [];
        for ($i = 1; $i <= 34; $i++) {
            $stickerFiles[] = 'sticker' . str_pad((string) $i, 3, '0', STR_PAD_LEFT) . '.gif';
        }
    ?>
    <section id="guestbook" class="aa-guestbook"
        style="--aa-gb-bg: <?= esc($guestbookConfig['backgroundColor'], 'attr') ?>; --aa-gb-card: <?= esc($guestbookConfig['cardColor'], 'attr') ?>; --aa-gb-text: <?= esc($guestbookConfig['textColor'], 'attr') ?>; --aa-gb-muted: <?= esc($guestbookConfig['mutedColor'], 'attr') ?>; --aa-gb-accent: <?= esc($guestbookConfig['accentColor'], 'attr') ?>; --aa-gb-radius: <?= (int) $guestbookConfig['borderRadius'] ?>px; --aa-gb-max-height: <?= (int) $guestbookConfig['maxHeight'] ?>px;">
        <div class="aa-guestbook-wrap">
            <div class="aa-guestbook-head">
                <p><?= esc($guestbookConfig['eyebrow']) ?></p>
                <h2><?= esc($guestbookConfig['title']) ?></h2>
                <p class="aa-guestbook-subtitle"><?= esc($guestbookConfig['subtitle']) ?></p>
            </div>

            <form id="aaGuestbookForm" class="aa-guestbook-form"
                action="<?= site_url('u/' . $page['slug'] . '/guestbook') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input id="aaSelectedStickerInput" type="hidden" name="sticker" value="">

                <div id="aaGuestbookAlert" class="aa-guestbook-alert" role="alert"></div>

                <label class="aa-guestbook-label">
                    Nama
                    <input class="aa-guestbook-input" name="guest_name" type="text" maxlength="120" autocomplete="name"
                        required>
                </label>

                <?php if ($guestbookConfig['showAttendance']): ?>
                <label class="aa-guestbook-label">
                    Kehadiran
                    <select class="aa-guestbook-select" name="attendance" required>
                        <option value="">Pilih kehadiran</option>
                        <option value="hadir">Hadir</option>
                        <option value="tidak_hadir">Tidak hadir</option>
                        <option value="ragu">Ragu</option>
                    </select>
                </label>
                <?php else: ?>
                <input type="hidden" name="attendance" value="ragu">
                <?php endif ?>

                <label class="aa-guestbook-label">
                    Komentar / ucapan
                    <textarea class="aa-guestbook-textarea" name="message" rows="4" maxlength="800" required
                        placeholder="Tulis ucapan terbaikmu..."></textarea>
                </label>

                <?php if ($guestbookConfig['showSticker']): ?>
                <div class="aa-sticker-control">
                    <div id="aaStickerPopover" class="aa-sticker-popover" aria-label="Stiker">
                        <?php foreach ($stickerFiles as $file): ?>
                        <button class="aa-sticker-choice" type="button" data-sticker="<?= esc($file, 'attr') ?>"
                            data-src="<?= esc(aa_asset_url('assets/stiker/' . $file), 'attr') ?>">
                            <img src="<?= esc(aa_asset_url('assets/stiker/' . $file), 'attr') ?>" alt="Sticker"
                                loading="lazy">
                        </button>
                        <?php endforeach ?>
                    </div>

                    <div class="aa-sticker-row">
                        <button id="aaStickerToggle" class="aa-sticker-button" type="button">Pilih stiker GIF</button>
                        <span id="aaSelectedStickerPreview" class="aa-selected-sticker">
                            <img src="" alt="Stiker terpilih">
                            <span>Stiker dipilih</span>
                            <button id="aaClearSticker" class="aa-sticker-button" type="button">Hapus</button>
                        </span>
                    </div>
                </div>
                <?php endif ?>

                <button id="aaGuestbookSubmit" class="aa-guestbook-submit"
                    type="submit"><?= esc($guestbookConfig['buttonText']) ?></button>
            </form>

            <div id="aaCommentList" class="aa-comment-list" aria-live="polite">
                <?php if ($guestbookEntriesForRender === []): ?>
                <div id="aaCommentEmpty" class="aa-comment-empty">Belum ada ucapan. Jadilah yang pertama mengisi
                    guestbook.</div>
                <?php else: ?>
                <?php foreach ($guestbookEntriesForRender as $entry): ?>
                <?php
                            $attendance = $entry['attendance'] ?? 'ragu';
                            $sticker = basename((string) ($entry['sticker'] ?? ''));
                        ?>
                <article class="aa-comment-card">
                    <div class="aa-comment-meta">
                        <div>
                            <h3><?= esc($entry['guest_name'] ?? '') ?></h3>
                            <p><?= esc($statusLabels[$attendance] ?? 'Ragu') ?></p>
                        </div>
                        <time><?= esc($entry['created_at'] ?? '') ?></time>
                    </div>
                    <div class="aa-comment-body">
                        <?php if ($sticker !== ''): ?>
                        <img class="aa-comment-sticker" src="<?= esc(aa_asset_url('assets/stiker/' . $sticker), 'attr') ?>"
                            alt="Sticker" loading="lazy">
                        <?php endif ?>
                        <p><?= esc($entry['message'] ?? '') ?></p>
                    </div>
                </article>
                <?php endforeach ?>
                <?php endif ?>
            </div>
        </div>
    </section>
    <?php endif ?>

    <script>
    (function() {
        if (window.__aaCanvasTextBaselineGuard) return;
        window.__aaCanvasTextBaselineGuard = true;

        function installTextBaselineGuard(proto) {
            if (!proto) return;

            var owner = proto;
            var descriptor = null;

            while (owner && !descriptor) {
                descriptor = Object.getOwnPropertyDescriptor(owner, 'textBaseline');
                owner = Object.getPrototypeOf(owner);
            }

            if (!descriptor || typeof descriptor.set !== 'function' || typeof descriptor.get !== 'function') {
                return;
            }

            try {
                Object.defineProperty(proto, 'textBaseline', {
                    configurable: descriptor.configurable,
                    enumerable: descriptor.enumerable,
                    get: descriptor.get,
                    set: function(value) {
                        descriptor.set.call(this, value === 'alphabetical' ? 'alphabetic' : value);
                    }
                });
            } catch (error) {
                // Some browsers may lock native canvas descriptors.
            }
        }

        installTextBaselineGuard(window.CanvasRenderingContext2D && window.CanvasRenderingContext2D.prototype);
        installTextBaselineGuard(window.OffscreenCanvasRenderingContext2D && window
            .OffscreenCanvasRenderingContext2D.prototype);
    })();
    </script>

    <script>
    function initCountdowns() {
        document.querySelectorAll('[data-countdown]').forEach(function(root) {
            if (root.dataset.ready === 'true') return;
            root.dataset.ready = 'true';

            const target = new Date(root.getAttribute('data-countdown')).getTime();
            if (!target) return;

            function tick() {
                if (document.hidden === true || document.visibilityState === 'hidden') return;
                const diff = Math.max(0, target - Date.now());
                const days = Math.floor(diff / 86400000);
                const hours = Math.floor((diff % 86400000) / 3600000);
                const minutes = Math.floor((diff % 3600000) / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);

                const set = (selector, value) => {
                    const element = root.querySelector(selector);
                    if (element) element.textContent = String(value).padStart(2, '0');
                };

                set('[data-days]', days);
                set('[data-hours]', hours);
                set('[data-minutes]', minutes);
                set('[data-seconds]', seconds);
            }

            tick();
            setInterval(tick, 1000);
        });
    }

    initCountdowns();
    </script>

    <script>
    window.AdaAcaraGuestbookEndpoint = <?= json_encode(site_url('u/' . $page['slug'] . '/guestbook')) ?>;
    window.AdaAcaraGuestbookCsrf = {
        name: <?= json_encode(function_exists('csrf_token') ? csrf_token() : '') ?>,
        hash: <?= json_encode(function_exists('csrf_hash') ? csrf_hash() : '') ?>
    };
    window.AdaAcaraGuestbookEntries =
        <?= json_encode($guestbookEntriesForRender, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.AdaAcaraStickerBase = <?= json_encode(aa_asset_url('assets/stiker') . '/') ?>;
    </script>

    <?php if ($showGuestbook): ?>
    <script>
    (function() {
        'use strict';

        if (window.AdaAcaraGuestbook && window.AdaAcaraGuestbook.initialized) {
            return;
        }

        const form = document.getElementById('aaGuestbookForm');
        const alertBox = document.getElementById('aaGuestbookAlert');
        const commentList = document.getElementById('aaCommentList');
        const stickerInput = document.getElementById('aaSelectedStickerInput');
        const stickerToggle = document.getElementById('aaStickerToggle');
        const stickerPopover = document.getElementById('aaStickerPopover');
        const selectedStickerPreview = document.getElementById('aaSelectedStickerPreview');
        const selectedStickerImage = selectedStickerPreview ? selectedStickerPreview.querySelector('img') : null;
        const clearSticker = document.getElementById('aaClearSticker');
        const submitButton = document.getElementById('aaGuestbookSubmit');
        const attendanceLabels = {
            hadir: 'Hadir',
            tidak_hadir: 'Tidak hadir',
            ragu: 'Ragu',
        };

        function showAlert(message, type) {
            if (!alertBox) return;
            alertBox.textContent = message;
            alertBox.className = 'aa-guestbook-alert is-visible ' + (type === 'error' ? 'is-error' : 'is-success');
        }

        function hideAlert() {
            if (!alertBox) return;
            alertBox.textContent = '';
            alertBox.className = 'aa-guestbook-alert';
        }

        function setSticker(fileName, src) {
            if (!stickerInput || !selectedStickerPreview || !selectedStickerImage) return;
            stickerInput.value = fileName || '';
            selectedStickerImage.src = src || '';
            selectedStickerPreview.classList.toggle('is-visible', Boolean(fileName));
            document.querySelectorAll('.aa-sticker-choice').forEach(button => {
                button.classList.toggle('is-active', button.dataset.sticker === fileName);
            });
        }

        function resetSticker() {
            setSticker('', '');
            if (stickerPopover) {
                stickerPopover.classList.remove('is-open');
            }
        }

        function createCommentCard(comment) {
            const card = document.createElement('article');
            card.className = 'aa-comment-card';

            const meta = document.createElement('div');
            meta.className = 'aa-comment-meta';

            const identity = document.createElement('div');
            const name = document.createElement('h3');
            name.textContent = comment.guest_name || '';
            const attendance = document.createElement('p');
            attendance.textContent = attendanceLabels[comment.attendance] || 'Ragu';
            identity.append(name, attendance);

            const time = document.createElement('time');
            time.textContent = comment.created_at || 'Baru saja';
            meta.append(identity, time);

            const body = document.createElement('div');
            body.className = 'aa-comment-body';

            if (comment.sticker_url) {
                const sticker = document.createElement('img');
                sticker.className = 'aa-comment-sticker';
                sticker.src = comment.sticker_url;
                sticker.alt = 'Sticker';
                sticker.loading = 'lazy';
                body.appendChild(sticker);
            }

            const message = document.createElement('p');
            message.textContent = comment.message || '';
            body.appendChild(message);

            card.append(meta, body);
            return card;
        }

        function prependComment(comment) {
            if (!commentList) return;
            const empty = document.getElementById('aaCommentEmpty');
            if (empty) empty.remove();
            commentList.prepend(createCommentCard(comment));
        }

        function updateCsrf(hash) {
            if (!hash || !form) return;
            const csrfInput = form.querySelector('input[type="hidden"][name^="csrf"]');
            if (csrfInput) {
                csrfInput.value = hash;
            }
        }

        if (stickerToggle && stickerPopover) {
            stickerToggle.addEventListener('click', function() {
                stickerPopover.classList.toggle('is-open');
            });
        }

        document.querySelectorAll('.aa-sticker-choice').forEach(button => {
            button.addEventListener('click', function() {
                setSticker(this.dataset.sticker || '', this.dataset.src || '');
                if (stickerPopover) {
                    stickerPopover.classList.remove('is-open');
                }
            });
        });

        if (clearSticker) {
            clearSticker.addEventListener('click', resetSticker);
        }

        document.addEventListener('click', function(event) {
            if (!stickerPopover || !stickerToggle) return;
            if (stickerPopover.contains(event.target) || stickerToggle.contains(event.target)) return;
            stickerPopover.classList.remove('is-open');
        });

        if (form) {
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                hideAlert();

                const guestName = form.elements.guest_name.value.trim();
                const attendance = form.elements.attendance.value;
                const message = form.elements.message.value.trim();

                if (!guestName) {
                    showAlert('Nama wajib diisi.', 'error');
                    form.elements.guest_name.focus();
                    return;
                }

                if (!attendance) {
                    showAlert('Kehadiran wajib dipilih.', 'error');
                    form.elements.attendance.focus();
                    return;
                }

                if (!message) {
                    showAlert('Komentar / ucapan wajib diisi.', 'error');
                    form.elements.message.focus();
                    return;
                }

                if (message.length > 800) {
                    showAlert('Komentar maksimal 800 karakter.', 'error');
                    form.elements.message.focus();
                    return;
                }

                const formData = new FormData(form);
                submitButton.disabled = true;
                submitButton.textContent = 'Mengirim...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await response.json().catch(() => ({}));
                    updateCsrf(data.csrf_hash);

                    if (!response.ok || data.success === false) {
                        const errors = data.errors ? Object.values(data.errors).join(' ') : '';
                        throw new Error(errors || data.message || 'Ucapan gagal dikirim.');
                    }

                    prependComment(data.comment || {
                        guest_name: guestName,
                        attendance,
                        message,
                        sticker: stickerInput ? stickerInput.value : '',
                        sticker_url: selectedStickerImage ? selectedStickerImage.src : '',
                        created_at: 'Baru saja',
                    });

                    form.reset();
                    resetSticker();
                    showAlert(data.message || 'Ucapan kamu berhasil dikirim.', 'success');
                } catch (error) {
                    showAlert(error.message || 'Ucapan gagal dikirim.', 'error');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = <?= json_encode($guestbookConfig['buttonText']) ?>;
                }
            });
        }

        window.AdaAcaraGuestbook = {
            initialized: true,
        };
    })();
    </script>
    <?php endif ?>

    <script>
    (function() {
        var modal = document.getElementById('aaOpeningModal');
        var button = document.getElementById('aaOpeningButton');
        var openingCanvasEl = document.getElementById('aaOpeningFabricCanvas');
        var openingData =
            <?= $hasCustomOpeningCanvas ? json_encode($openingCanvasPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : 'null' ?>;
        var pendingCallbacks = [];
        var hasOpeningGate = Boolean(modal && button);

        window.AdaAcaraPublicInvitationOpened = !hasOpeningGate;
        window.AdaAcaraRunWhenInvitationOpened = function(callback) {
            if (typeof callback !== 'function') return;
            if (window.AdaAcaraPublicInvitationOpened) {
                callback();
                return;
            }
            pendingCallbacks.push(callback);
        };

        function releasePendingCallbacks() {
            var callbacks = pendingCallbacks.slice();
            pendingCallbacks = [];
            callbacks.forEach(function(callback) {
                try {
                    callback();
                } catch (error) {
                    console.error('AdaAcara opening callback gagal:', error);
                }
            });
        }

        if (!hasOpeningGate) {
            releasePendingCallbacks();
            return;
        }

        function loadOpeningFabric(callback) {
            if (!openingCanvasEl || !openingData) return;
            if (window.fabric) {
                callback();
                return;
            }
            var existing = document.querySelector('script[src*="fabric.min.js"]');
            if (existing) {
                existing.addEventListener('load', function() {
                    if (window.fabric) callback();
                });
                return;
            }
            var script = document.createElement('script');
            script.src = 'https://adaacara.com/assets/js/fabric.min.js';
            script.async = true;
            script.onload = callback;
            document.head.appendChild(script);
        }

        function installOpeningRoundedImageRenderer() {
            if (!window.fabric || fabric.Image.prototype.__aaRoundedRendererInstalled) return;
            var originalRender = fabric.Image.prototype._render;
            var drawPath = function(ctx, width, height, radius) {
                var r = Math.min(Math.max(0, Number(radius) || 0), width / 2, height / 2);
                var x = -width / 2;
                var y = -height / 2;
                ctx.beginPath();
                if (!r) {
                    ctx.rect(x, y, width, height);
                    return;
                }
                ctx.moveTo(x + r, y);
                ctx.lineTo(x + width - r, y);
                ctx.quadraticCurveTo(x + width, y, x + width, y + r);
                ctx.lineTo(x + width, y + height - r);
                ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
                ctx.lineTo(x + r, y + height);
                ctx.quadraticCurveTo(x, y + height, x, y + height - r);
                ctx.lineTo(x, y + r);
                ctx.quadraticCurveTo(x, y, x + r, y);
                ctx.closePath();
            };
            var drawStroke = function(ctx, image, width, height, radius) {
                var strokeWidth = Math.max(0, Number(image.strokeWidth) || 0);
                if (!strokeWidth || !image.stroke || image.stroke === 'transparent') return;
                ctx.save();
                drawPath(ctx, width, height, radius);
                ctx.lineWidth = strokeWidth;
                ctx.strokeStyle = image.stroke;
                ctx.lineJoin = 'round';
                ctx.lineCap = image.imageStrokeStyle === 'dotted' ? 'round' : 'butt';
                if (Array.isArray(image.strokeDashArray)) ctx.setLineDash(image.strokeDashArray);
                ctx.stroke();
                ctx.restore();
            };
            var imageEffectCanvasFilter = function(image) {
                var preset = String((image && image.aaImageEffectPreset) || 'none');
                if (!preset || preset === 'none' || preset === 'opacity' || preset === 'shadow') return '';
                if (Array.isArray(image.filters) && image.filters.length) return '';
                if (preset === 'brightness') return 'brightness(1.16)';
                if (preset === 'contrast') return 'contrast(1.22)';
                if (preset === 'saturation') return 'saturate(1.38)';
                if (preset === 'grayscale') return 'grayscale(1)';
                if (preset === 'sepia') return 'sepia(1)';
                if (preset === 'blur') return 'blur(2px)';
                if (preset === 'sharpen') return 'contrast(1.28) saturate(1.12)';
                if (preset === 'vintage') return 'sepia(.55) contrast(1.08) saturate(.82)';
                if (preset === 'soft-wedding') return 'brightness(1.08) contrast(.96) saturate(1.18) sepia(.08)';
                if (preset === 'clean-bright') return 'brightness(1.14) contrast(1.08) saturate(1.08)';
                if (preset === 'warm-editorial') return 'sepia(.18) brightness(1.06) contrast(1.12) saturate(1.14)';
                if (preset === 'film-matte') return 'sepia(.2) contrast(.92) saturate(.78) brightness(1.04)';
                if (preset === 'pastel-bloom') return 'brightness(1.1) contrast(.94) saturate(1.32) hue-rotate(-6deg)';
                if (preset === 'moody-luxe') return 'brightness(.88) contrast(1.22) saturate(.9) sepia(.08)';
                if (preset === 'classic-bw') return 'grayscale(1) contrast(1.18) brightness(1.04)';
                if (preset === 'dreamy-soft') return 'brightness(1.12) contrast(.9) saturate(1.12) blur(.75px)';
                if (preset === 'recolor-white') return 'grayscale(.35) brightness(1.34) contrast(.86) saturate(.68)';
                if (preset === 'recolor-black') return 'grayscale(1) brightness(.72) contrast(1.28)';
                if (preset === 'recolor-gold') return 'sepia(.55) saturate(1.45) hue-rotate(4deg) brightness(1.08) contrast(1.04)';
                if (preset === 'recolor-teal') return 'sepia(.18) saturate(1.35) hue-rotate(135deg) brightness(.96) contrast(1.06)';
                if (preset === 'recolor-rose') return 'sepia(.22) saturate(1.35) hue-rotate(300deg) brightness(1.04) contrast(.98)';
                if (preset === 'recolor-slate') return 'grayscale(.65) sepia(.12) saturate(.7) hue-rotate(170deg) brightness(.92) contrast(1.08)';
                if (preset === 'remove-color') return 'saturate(.2) contrast(1.12)';
                return '';
            };
            var renderImageWithCanvasEffect = function(image, ctx) {
                var filter = imageEffectCanvasFilter(image);
                if (!filter) {
                    originalRender.call(image, ctx);
                    return;
                }
                var previousFilter = ctx.filter;
                ctx.filter = filter;
                originalRender.call(image, ctx);
                ctx.filter = previousFilter;
            };
            fabric.Image.prototype._render = function(ctx) {
                var radius = Math.max(0, Number(this.borderRadius) || 0);
                var width = Math.max(1, this.width || 1);
                var height = Math.max(1, this.height || 1);
                if (radius) {
                    ctx.save();
                    drawPath(ctx, width, height, radius);
                    ctx.clip();
                    renderImageWithCanvasEffect(this, ctx);
                    ctx.restore();
                } else {
                    renderImageWithCanvasEffect(this, ctx);
                }
                drawStroke(ctx, this, width, height, radius);
            };
            fabric.Image.prototype.__aaRoundedRendererInstalled = true;
        }

        function refreshOpeningImageStyles(canvas) {
            if (!canvas || !canvas.getObjects) return;
            canvas.getObjects().forEach(function(object) {
                var objects = object && object.type === 'group' && object.getObjects ? object.getObjects() :
                    [object];
                objects.forEach(function(item) {
                    if (!item || item.type !== 'image') return;
                    if (item.borderRadius && item.clipPath && (item.clipPath.rx || item.clipPath
                            .ry)) {
                        item.clipPath = null;
                    }
                    if (item.aaImageEffectPreset && Array.isArray(item.filters)) {
                        item.filters = [];
                    }
                    item.dirty = true;
                    item.setCoords();
                });
                if (object && object.type === 'group') {
                    object.dirty = true;
                    object.setCoords();
                }
            });
        }

        function getOpeningGuestName() {
            var params = new URLSearchParams(window.location.search || '');
            var value = params.get('to') || params.get('tamu') || params.get('invite') || params.get('guest') || '';
            value = String(value || '').replace(/\+/g, ' ').trim();
            return value || 'Tamu Undangan';
        }

        function isOpeningTextObject(object) {
            return object && ['i-text', 'textbox', 'text'].indexOf(object.type) !== -1;
        }

        function walkOpeningObjects(objects, callback) {
            (objects || []).forEach(function(object) {
                callback(object);
                if (object && typeof object.getObjects === 'function') {
                    walkOpeningObjects(object.getObjects(), callback);
                }
            });
        }

        function renderOpeningGuestNameTemplate(template, guestName) {
            template = String(template || 'Kepada Yth.\n{{guest_name}}');
            if (!/\{\{\s*guest_name\s*\}\}/i.test(template)) {
                template = template.replace(/Nama Tamu|Tamu Undangan/gi, '{{guest_name}}');
            }
            return template.replace(/\{\{\s*guest_name\s*\}\}/gi, guestName || 'Tamu Undangan');
        }

        function applyOpeningGuestName(canvas) {
            if (!canvas || !canvas.getObjects) return;
            var guestName = getOpeningGuestName();
            walkOpeningObjects(canvas.getObjects(), function(object) {
                var isGuestName = object && (object.isGuestName === true || object.customType ===
                    'guest_name' ||
                    object.dynamicKey === 'guest_name');
                if (!isGuestName) return;

                var currentText = object.templateText || object.text || '';
                if (!currentText && object && typeof object.getObjects === 'function') {
                    var children = object.getObjects();
                    var targetChild = children.find(function(child) {
                        return child.name === 'guest-name-text';
                    }) || children.find(isOpeningTextObject);
                    currentText = targetChild ? targetChild.text : '';
                }

                var nextText = renderOpeningGuestNameTemplate(currentText, guestName);
                var target = object;
                if (object && typeof object.getObjects === 'function') {
                    var objectChildren = object.getObjects();
                    target = objectChildren.find(function(child) {
                        return child.name === 'guest-name-text';
                    }) || objectChildren.find(isOpeningTextObject) || object;
                }

                if (target && isOpeningTextObject(target) && typeof target.set === 'function') {
                    target.set('text', nextText);
                    target.dirty = true;
                    if (typeof target.initDimensions === 'function') target.initDimensions();
                    target.setCoords();
                }
                if (object && typeof object.setCoords === 'function') object.setCoords();
            });
        }

        function renderOpeningCanvas() {
            if (!openingCanvasEl || !openingData || !window.fabric) return;
            var artboard = openingData.artboard || {};
            var width = Math.max(1, Number(artboard.width) || 1080);
            var height = Math.max(1, Number(artboard.height) || 1920);
            installOpeningRoundedImageRenderer();
            var canvas = new fabric.StaticCanvas(openingCanvasEl, {
                width: width,
                height: height,
                renderOnAddRemove: false,
                enableRetinaScaling: true
            });

            function sanitizeOpeningObject(obj) {
                if (!obj || typeof obj !== 'object') return;
                if (obj.textBaseline === 'alphabetical') obj.textBaseline = 'alphabetic';
                if (obj.styles && typeof obj.styles === 'object') Object.keys(obj.styles).forEach(function(l) {
                    if (obj.styles[l]) Object.keys(obj.styles[l]).forEach(function(c) {
                        if (obj.styles[l][c] && obj.styles[l][c].textBaseline === 'alphabetical')
                            obj.styles[l][c].textBaseline = 'alphabetic';
                    })
                });
                if (Array.isArray(obj.objects)) obj.objects.forEach(sanitizeOpeningObject);
            }
            if (Array.isArray(openingData.objects)) {
                openingData.objects.forEach(sanitizeOpeningObject);
            }

            canvas.backgroundColor = openingData.background || openingData.backgroundColor || '#0f766e';
            canvas.loadFromJSON(openingData, function() {
                canvas.getObjects().forEach(function(object) {
                    object.selectable = false;
                    object.evented = false;
                });
                refreshOpeningImageStyles(canvas);
                applyOpeningGuestName(canvas);
                positionOpeningHotspot(canvas, width, height);
                canvas.requestRenderAll();
            });
        }

        function positionOpeningHotspot(canvas, width, height) {
            if (!button || !button.classList.contains('aa-opening-custom-hotspot')) return;
            var target = null;
            canvas.getObjects().forEach(function(object) {
                if (!target && object && object.customType === 'opening-button') target = object;
            });
            if (!target || typeof target.getBoundingRect !== 'function') {
                button.className = 'aa-opening-button is-custom-overlay';
                button.textContent = 'Buka Undangan';
                button.removeAttribute('style');
                return;
            }
            var rect = target.getBoundingRect(true, true);
            button.style.left = (rect.left / width * 100) + '%';
            button.style.top = (rect.top / height * 100) + '%';
            button.style.width = (rect.width / width * 100) + '%';
            button.style.height = (rect.height / height * 100) + '%';
            button.style.borderRadius = Math.max(8, Math.min(rect.width, rect.height) / 2) + 'px';
        }

        loadOpeningFabric(renderOpeningCanvas);

        var closed = false;

        function openInvitation() {
            if (closed) return;
            closed = true;
            modal.classList.add('is-leaving');
            modal.classList.remove('is-visible');
            window.AdaAcaraPublicInvitationOpened = true;

            var release = function() {
                window.dispatchEvent(new CustomEvent('adaacara:invitation-opened'));
                releasePendingCallbacks();
            };

            if (window.requestAnimationFrame) {
                requestAnimationFrame(function() {
                    requestAnimationFrame(release);
                });
            } else {
                window.setTimeout(release, 80);
            }

            window.setTimeout(function() {
                modal.remove();
            }, 1200);
        }

        window.setTimeout(function() {
            modal.classList.add('is-visible');
        }, 220);

        button.addEventListener('click', openInvitation);
    })();
    </script>

    <?php if (empty($isPreview) && trim($renderJs) !== ''): ?>
    <script type="application/json" id="aaDeferredPublicJs">
    <?= json_encode($renderJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
    <script>
    (function() {
        var executed = false;

        function runPublishedScript() {
            if (executed) return;
            executed = true;

            var sourceEl = document.getElementById('aaDeferredPublicJs');
            if (!sourceEl) return;

            var code = '';
            try {
                code = JSON.parse(sourceEl.textContent || '""');
            } catch (error) {
                code = '';
            }

            if (!code) return;

            try {
                var script = document.createElement('script');
                script.textContent = code;
                document.body.appendChild(script);
                sourceEl.remove();
            } catch (error) {
                executed = false;
                console.error('AdaAcara public script gagal dijalankan:', error);
            }
        }

        runPublishedScript();
    })();
    </script>
    <?php else: ?>
    <script>
    <?= $renderJs ?>
    </script>
    <?php endif ?>
    <?php if ($hasFabricArtboard): ?>
    <script>
    (function() {
        function allowPageTouchScroll() {
            document.documentElement.style.overflowY = 'auto';
            document.body.style.overflowY = 'auto';
            document.body.style.touchAction = 'pan-y';

            document.querySelectorAll(
                '.aa-fabric-artboard .canvas-container, .aa-fabric-artboard canvas, .aa-fabric-artboard .upper-canvas, .aa-fabric-artboard .lower-canvas'
            ).forEach(function(el) {
                el.style.touchAction = 'pan-y pinch-zoom';
                el.style.webkitUserSelect = 'auto';
            });

            document.querySelectorAll('.aa-fabric-artboard canvas').forEach(function(canvasEl) {
                var instance = canvasEl.__aaFabricCanvas || canvasEl.fabric || canvasEl.__fabric;
                if (instance) {
                    if (instance.upperCanvasEl) {
                        instance.upperCanvasEl.style.touchAction = 'pan-y pinch-zoom';
                    }
                    if (instance.lowerCanvasEl) {
                        instance.lowerCanvasEl.style.touchAction = 'pan-y pinch-zoom';
                    }
                    if (instance.wrapperEl) {
                        instance.wrapperEl.style.touchAction = 'pan-y pinch-zoom';
                    }
                }
            });
        }

        var aaStableFontLoadCache = {};

        function normalizeHotspotUrl(url) {
            url = String(url || '').trim();
            if (!url) return '';
            if (/^(https?:|mailto:|tel:|sms:|whatsapp:)/i.test(url)) return url;
            return 'https://' + url.replace(/^\/+/, '');
        }

        function showHotspotToast(message) {
            var toast = document.createElement('div');
            toast.textContent = message || 'Tersalin';
            toast.style.cssText =
                'position:fixed;left:50%;bottom:24px;z-index:99999;transform:translateX(-50%);border-radius:999px;background:rgba(17,24,39,.94);color:#fff;padding:10px 16px;font:700 13px Inter,Arial,sans-serif;box-shadow:0 14px 36px rgba(15,23,42,.24);pointer-events:none;';
            document.body.appendChild(toast);
            window.setTimeout(function() {
                toast.remove();
            }, 1400);
        }

        function copyHotspotText(value, message) {
            value = String(value || '');
            if (!value) return;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(function() {
                    showHotspotToast(message);
                }).catch(function() {
                    showHotspotToast('Tidak bisa copy otomatis');
                });
                return;
            }

            var input = document.createElement('textarea');
            input.value = value;
            input.setAttribute('readonly', 'readonly');
            input.style.cssText = 'position:fixed;left:-9999px;top:0;font-size:16px;';
            document.body.appendChild(input);
            input.select();
            input.setSelectionRange(0, input.value.length);
            try {
                document.execCommand('copy');
                showHotspotToast(message);
            } catch (error) {
                showHotspotToast('Tidak bisa copy otomatis');
            }
            input.remove();
        }

        function setupActionHotspotsForCanvas(canvasEl, instance) {
            var artboard = canvasEl.closest('.aa-fabric-artboard');
            if (!artboard || !instance || !instance.getObjects) return;

            var oldLayer = artboard.querySelector('.aa-fabric-click-layer');
            if (oldLayer) oldLayer.remove();

            var layer = document.createElement('div');
            layer.className = 'aa-fabric-click-layer';
            artboard.appendChild(layer);

            var canvasWidth = instance.getWidth() || 1;
            var canvasHeight = instance.getHeight() || 1;

            instance.getObjects().forEach(function(object) {
                if (object && (object.customType === 'gallery-photo' || object.isGalleryPhoto === true ||
                        object
                        .galleryZoom === true)) {
                    var galleryRect = object.getBoundingRect(true, true);
                    var galleryHotspot = document.createElement('button');
                    galleryHotspot.type = 'button';
                    galleryHotspot.className = 'aa-fabric-hotspot aa-fabric-gallery-hotspot';
                    galleryHotspot.style.left = (galleryRect.left / canvasWidth * 100) + '%';
                    galleryHotspot.style.top = (galleryRect.top / canvasHeight * 100) + '%';
                    galleryHotspot.style.width = (galleryRect.width / canvasWidth * 100) + '%';
                    galleryHotspot.style.height = (galleryRect.height / canvasHeight * 100) + '%';
                    galleryHotspot.setAttribute('aria-label', 'Zoom foto gallery');
                    window.aaBindGalleryLightboxTrigger(galleryHotspot, function() {
                        return object.galleryImageSrc || object.src || (object
                            ._element && object
                            ._element.src) || '';
                    });
                    layer.appendChild(galleryHotspot);
                }

                if (!object.link && !object.copyText) return;

                var rect = object.getBoundingRect(true, true);
                var hotspot = object.link ? document.createElement('a') : document.createElement('button');
                hotspot.className = 'aa-fabric-hotspot';
                hotspot.style.left = (rect.left / canvasWidth * 100) + '%';
                hotspot.style.top = (rect.top / canvasHeight * 100) + '%';
                hotspot.style.width = (rect.width / canvasWidth * 100) + '%';
                hotspot.style.height = (rect.height / canvasHeight * 100) + '%';
                hotspot.setAttribute('aria-label', object.link ? 'Buka link' : 'Copy text');

                if (object.link) {
                    hotspot.href = normalizeHotspotUrl(object.link);
                    hotspot.target = '_blank';
                    hotspot.rel = 'noopener';
                } else {
                    hotspot.type = 'button';
                    hotspot.addEventListener('click', function() {
                        copyHotspotText(object.copyText, object.copyFeedback || 'Tersalin');
                    });
                }

                layer.appendChild(hotspot);
            });
        }

        function isTextHotspotObject(object) {
            return object && ['i-text', 'textbox', 'text'].indexOf(object.type) !== -1;
        }

        function walkHotspotObjects(objects, callback) {
            (objects || []).forEach(function(object) {
                callback(object);
                if (object && typeof object.getObjects === 'function') {
                    walkHotspotObjects(object.getObjects(), callback);
                }
            });
        }

        function normalizeHotspotFontFamily(fontFamily) {
            return String(fontFamily || 'Inter').replace(/^["']|["']$/g, '').trim() || 'Inter';
        }

        function getHotspotGuestName() {
            var params = new URLSearchParams(window.location.search || '');
            var value = params.get('to') || params.get('tamu') || params.get('invite') || params.get('guest') || '';
            value = String(value || '').replace(/\+/g, ' ').trim();
            return value || 'Tamu Undangan';
        }

        function renderHotspotGuestNameTemplate(template, guestName) {
            template = String(template || 'Kepada Yth.\n{{guest_name}}');
            if (!/\{\{\s*guest_name\s*\}\}/i.test(template)) {
                template = template.replace(/Nama Tamu|Tamu Undangan/gi, '{{guest_name}}');
            }
            return template.replace(/\{\{\s*guest_name\s*\}\}/gi, guestName || 'Tamu Undangan');
        }

        function isHotspotGuestNamePlaceholderText(value) {
            var text = String(value || '').trim();
            var normalized = text.replace(/\s+/g, ' ');
            return /\{\{\s*guest_name\s*\}\}/i.test(text) ||
                /\bNama\s+Tamu\b/i.test(text) ||
                /^(Kepada\s+(Yth\.?|Yang\s+Terhormat)\s*)?Tamu\s+Undangan$/i.test(normalized);
        }

        function applyGuestNameToInstance(instance) {
            if (!instance || !instance.getObjects) return;
            var guestName = getHotspotGuestName();
            var decorativeNames = [
                'guest-name-glass-card',
                'guest-name-inner-glow',
                'guest-name-edge-reflection',
                'guest-name-top-sheen',
                'guest-name-close-circle',
                'guest-name-close-text'
            ];
            walkHotspotObjects(instance.getObjects(), function(object) {
                var isGuestNameCandidate = object && (object.isGuestName === true || object.customType ===
                    'guest_name' || object
                    .dynamicKey === 'guest_name' || (isTextHotspotObject(object) &&
                        isHotspotGuestNamePlaceholderText(object.text)));
                if (!isGuestNameCandidate) return;
                if (typeof object.set === 'function') {
                    object.set({
                        showCloseButton: false,
                        glassCard: false
                    });
                } else {
                    object.showCloseButton = false;
                    object.glassCard = false;
                }
                if (typeof object.getObjects === 'function' && typeof object.remove === 'function') {
                    object.getObjects().slice().forEach(function(child) {
                        if (decorativeNames.indexOf(child && child.name) !== -1) {
                            object.remove(child);
                        }
                    });
                }
                var currentText = object.templateText || object.text || '';
                if (!currentText && object && typeof object.getObjects === 'function') {
                    var currentChildren = object.getObjects();
                    var currentTarget = currentChildren.find(function(child) {
                        return child.name === 'guest-name-text';
                    }) || currentChildren.find(isTextHotspotObject);
                    currentText = currentTarget ? currentTarget.text : '';
                }
                var nextText = renderHotspotGuestNameTemplate(currentText, guestName);
                var target = object;
                if (object && typeof object.getObjects === 'function') {
                    var children = object.getObjects();
                    target = children.find(function(child) {
                        return child.name === 'guest-name-text';
                    }) || children.find(isTextHotspotObject) || object;
                }
                if (typeof target.set === 'function') {
                    target.set('text', nextText);
                } else {
                    target.text = nextText;
                }
                target.dirty = true;
                if (typeof target.initDimensions === 'function') target.initDimensions();
                object.dirty = true;
                if (typeof object.setCoords === 'function') object.setCoords();
            });
        }

        function loadFontsForInstance(instance) {
            if (!document.fonts || !document.fonts.load || !instance || !instance.getObjects) {
                return Promise.resolve();
            }

            var variants = [];
            var normalizeWeight = function(weight) {
                weight = String(weight || '400').toLowerCase() === 'bold' ? '700' : String(weight || '400');
                if (!/^[1-9]00$/.test(weight)) return Number(weight) >= 600 ? '700' : '400';
                return weight;
            };
            var addVariant = function(family, weight, style) {
                family = normalizeHotspotFontFamily(family);
                weight = normalizeWeight(weight);
                style = String(style || 'normal').toLowerCase() === 'italic' ? 'italic' : 'normal';
                var key = family + '|' + weight + '|' + style;
                if (!variants.some(function(item) {
                        return item.key === key;
                    })) {
                    variants.push({
                        key: key,
                        family: family,
                        weight: weight,
                        style: style
                    });
                }
            };
            walkHotspotObjects(instance.getObjects(), function(object) {
                if (object.type === 'image' && object.borderRadius && object.clipPath && (object.clipPath
                        .rx || object.clipPath.ry)) {
                    object.clipPath = null;
                    object.dirty = true;
                    object.setCoords();
                }
                if (!isTextHotspotObject(object)) return;
                addVariant(object.fontFamily, object.fontWeight, object.fontStyle);
            });
            addVariant('Inter', '400', 'normal');

            return Promise.all(variants.map(function(variant) {
                if (aaStableFontLoadCache[variant.key]) return aaStableFontLoadCache[variant.key];
                aaStableFontLoadCache[variant.key] = document.fonts.load(variant.style + ' ' + variant
                    .weight + ' 32px "' + variant.family
                    .replace(/"/g, '') + '"').catch(function() {
                    return null;
                });
                return aaStableFontLoadCache[variant.key];
            })).then(function() {
                return document.fonts.ready;
            }).catch(function() {
                return null;
            });
        }

        function recalculateInstanceText(instance) {
            if (!instance || !instance.getObjects) return;
            if (window.fabric) {
                if (fabric.charWidthsCache) fabric.charWidthsCache = {};
                if (fabric.Text && fabric.Text.charWidthsCache) fabric.Text.charWidthsCache = {};
            }

            walkHotspotObjects(instance.getObjects(), function(object) {
                if (!isTextHotspotObject(object)) return;
                if (object.clipPath) {
                    object.clipPath = null;
                }
                object.dirty = true;
                if (typeof object.initDimensions === 'function') object.initDimensions();
                object.setCoords();
            });
        }

        function markPublicArtboardsRendering() {
            document.querySelectorAll('.aa-fabric-artboard').forEach(function(artboard) {
                if (!artboard.querySelector('canvas[data-aa-rendered="true"]')) return;
                artboard.classList.add('aa-is-stable-rerendering');
            });
        }

        function finishPublicArtboardsRendering() {
            var finish = function() {
                document.querySelectorAll('.aa-fabric-artboard.is-rendering').forEach(function(artboard) {
                    artboard.classList.remove('is-rendering');
                });
                document.querySelectorAll('.aa-fabric-artboard.aa-is-stable-rerendering').forEach(function(artboard) {
                    artboard.classList.remove('aa-is-stable-rerendering');
                });
                document.body.classList.remove('aa-public-stabilizing');
            };

            if (window.requestAnimationFrame) {
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        requestAnimationFrame(finish);
                    });
                });
                return;
            }

            window.setTimeout(finish, 120);
        }

        function installPublicRoundedImageRenderer() {
            if (!window.fabric || fabric.Image.prototype.__aaRoundedRendererInstalled) return;
            var originalRender = fabric.Image.prototype._render;
            fabric.Image.prototype._render = function(ctx) {
                var radius = Math.max(0, Number(this.borderRadius) || 0);
                if (!radius) {
                    originalRender.call(this, ctx);
                    return;
                }
                var width = Math.max(1, this.width || 1);
                var height = Math.max(1, this.height || 1);
                var r = Math.min(radius, width / 2, height / 2);
                var x = -width / 2;
                var y = -height / 2;
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.lineTo(x + width - r, y);
                ctx.quadraticCurveTo(x + width, y, x + width, y + r);
                ctx.lineTo(x + width, y + height - r);
                ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
                ctx.lineTo(x + r, y + height);
                ctx.quadraticCurveTo(x, y + height, x, y + height - r);
                ctx.lineTo(x, y + r);
                ctx.quadraticCurveTo(x, y, x + r, y);
                ctx.closePath();
                ctx.clip();
                originalRender.call(this, ctx);
                ctx.restore();
            };
            fabric.Image.prototype.__aaRoundedRendererInstalled = true;
        }

        function rerenderFabricCanvases(onComplete) {
            if (!window.fabric) {
                if (onComplete) onComplete();
                return;
            }
            installPublicRoundedImageRenderer();

            var canvases = Array.from(document.querySelectorAll('.aa-fabric-artboard canvas'));
            var index = 0;

            function processNext() {
                if (index >= canvases.length) {
                    allowPageTouchScroll();
                    if (onComplete) onComplete();
                    return;
                }

                var canvasEl = canvases[index];
                index++;

                var instance = canvasEl.__aaFabricCanvas || canvasEl.fabric || canvasEl.__fabric;
                if (!instance) {
                    processNext();
                    return;
                }

                var originalWidth = Number(canvasEl.__aaFabricOriginalWidth || instance.getWidth());
                var originalHeight = Number(canvasEl.__aaFabricOriginalHeight || instance.getHeight());
                var data = instance.toJSON ? instance.toJSON() : null;

                if (data && data.artboard) {
                    originalWidth = data.artboard.width || originalWidth;
                    originalHeight = data.artboard.height || originalHeight;
                }

                instance.setDimensions({
                    width: originalWidth,
                    height: originalHeight
                });
                canvasEl.style.width = '100%';
                canvasEl.style.height = '100%';
                applyGuestNameToInstance(instance);
                recalculateInstanceText(instance);

                var finalizeStep = function() {
                    if (window.fabric) {
                        if (fabric.charWidthsCache) fabric.charWidthsCache = {};
                        if (fabric.Text && fabric.Text.charWidthsCache) fabric.Text.charWidthsCache = {};
                    }
                    instance.getObjects().forEach(function(o) {
                        if (o && ['i-text', 'textbox', 'text'].indexOf(o.type) !== -1) {
                            o.objectCaching = false;
                            if (typeof o.initDimensions === 'function') o.initDimensions();
                        }
                    });
                    instance.calcOffset();
                    instance.requestRenderAll();
                    setupActionHotspotsForCanvas(canvasEl, instance);
                    if (typeof setupInteractiveOverlay === 'function') {
                        try {
                            setupInteractiveOverlay(canvasEl, instance);
                        } catch (error) {
                            console.warn('Interactive overlay rerender gagal:', error);
                        }
                    }
                    window.setTimeout(processNext, 20);
                };

                if (window.requestAnimationFrame) {
                    requestAnimationFrame(finalizeStep);
                } else {
                    window.setTimeout(finalizeStep, 20);
                }
            }

            processNext();
        }

        var rerenderTimer = null;
        var stableAttempts = 0;
        var isRerendering = false;

        function runRerenderWhenIdle(delay) {
            window.setTimeout(function() {
                if (typeof aaPublicPageHidden === 'function' && aaPublicPageHidden()) {
                    aaWhenPublicPageVisible(function() {
                        runRerenderWhenIdle(120);
                    });
                    return;
                }

                if (window.requestIdleCallback) {
                    window.requestIdleCallback(function() {
                        scheduleRerender();
                    }, {
                        timeout: 1200
                    });
                    return;
                }

                window.setTimeout(scheduleRerender, 80);
            }, Math.max(0, Number(delay) || 0));
        }

        function scheduleRerender() {
            if (typeof aaPublicPageHidden === 'function' && aaPublicPageHidden()) {
                aaWhenPublicPageVisible(function() {
                    scheduleRerender();
                });
                return;
            }
            if (isRerendering) return;
            if (rerenderTimer) {
                window.clearTimeout(rerenderTimer);
            }
            markPublicArtboardsRendering();
            rerenderTimer = window.setTimeout(function() {
                rerenderTimer = null;
                isRerendering = true;
                runStableRerender();
            }, 160);
        }

        function runStableRerender() {
            var instances = [];
            document.querySelectorAll('.aa-fabric-artboard canvas').forEach(function(canvasEl) {
                var instance = canvasEl.__aaFabricCanvas || canvasEl.fabric || canvasEl.__fabric;
                if (instance) instances.push(instance);
            });
            if (!instances.length && document.querySelector('.aa-fabric-artboard') && stableAttempts < 30) {
                stableAttempts += 1;
                isRerendering = false;
                window.setTimeout(scheduleRerender, 150);
                return;
            }
            stableAttempts = 0;
            Promise.all(instances.map(loadFontsForInstance)).then(function() {
                rerenderFabricCanvases(function() {
                    finishPublicArtboardsRendering();
                    isRerendering = false;
                });
            }).catch(function() {
                rerenderFabricCanvases(function() {
                    finishPublicArtboardsRendering();
                    isRerendering = false;
                });
            });
        }

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function() {
                runRerenderWhenIdle(0);
            }).catch(function() {
                runRerenderWhenIdle(0);
            });
        } else {
            window.addEventListener('load', function() {
                runRerenderWhenIdle(80);
            }, {
                once: true
            });
        }

        window.addEventListener('resize', scheduleRerender);
        window.addEventListener('adaacara:invitation-opened', function() {
            window.setTimeout(function() {
                scheduleRerender();
            }, 120);
        });
        window.addEventListener('load', function() {
            runRerenderWhenIdle(80);
        }, {
            once: true
        });
        runRerenderWhenIdle(500);
    })();
    </script>
    <style>
    .aa-fabric-music-icon svg {
        display: block;
        width: 1em;
        height: 1em;
    }

    .aa-fabric-sticker-popover.aa-public-sticker-enhanced {
        grid-template-columns: auto minmax(0, 1fr) auto !important;
        align-items: center !important;
        gap: 8px !important;
        width: min(380px, 88vw) !important;
        max-width: calc(100vw - 28px) !important;
        height: auto !important;
        border-radius: 18px !important;
        background: rgba(255, 255, 255, .96) !important;
        padding: 10px !important;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .18) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .aa-fabric-sticker-popover.aa-public-sticker-enhanced.is-open {
        display: grid !important;
    }

    .aa-public-sticker-enhanced .aa-fabric-sticker-track {
        display: flex;
        min-width: 0;
        gap: 8px;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        scroll-behavior: smooth;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .aa-public-sticker-enhanced .aa-fabric-sticker-track::-webkit-scrollbar {
        display: none;
    }

    .aa-public-sticker-enhanced .aa-fabric-sticker-choice {
        display: inline-grid;
        width: 68px;
        height: 68px;
        flex: 0 0 68px;
        place-items: center;
        border: 1px solid rgba(226, 232, 240, .88);
        border-radius: 16px;
        background: rgba(255, 255, 255, .48);
        padding: 6px;
        cursor: pointer;
        scroll-snap-align: center;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .72);
    }

    .aa-public-sticker-enhanced .aa-fabric-sticker-choice.is-selected {
        border-color: #0f766e;
        background: rgba(204, 251, 241, .72);
        box-shadow: inset 0 0 0 1px rgba(15, 118, 110, .24), 0 10px 20px rgba(15, 118, 110, .12);
    }

    .aa-public-sticker-enhanced .aa-fabric-sticker-choice img {
        display: block;
        width: 54px;
        height: 54px;
        object-fit: contain;
    }

    .aa-public-sticker-enhanced .aa-fabric-sticker-nav {
        display: inline-grid;
        width: 34px;
        height: 52px;
        flex: 0 0 34px;
        place-items: center;
        border: 1px solid rgba(226, 232, 240, .86);
        border-radius: 999px;
        background: rgba(255, 255, 255, .72);
        color: #0f766e;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
    }

    .aa-public-sticker-enhanced .aa-fabric-sticker-nav svg {
        width: 17px;
        height: 17px;
    }
    </style>
    <script>
    (function() {
        if (window.__aaPublicMusicStickerPatchReady) return;
        window.__aaPublicMusicStickerPatchReady = true;

        var playSvg = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.6 5.42v13.16c0 .66.72 1.06 1.27.7l10.02-6.58a.84.84 0 0 0 0-1.4L9.87 4.72a.83.83 0 0 0-1.27.7Z"/></svg>';
        var pauseSvg = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.25 5.5A1.25 1.25 0 0 1 8.5 4.25h1.75A1.25 1.25 0 0 1 11.5 5.5v13a1.25 1.25 0 0 1-1.25 1.25H8.5a1.25 1.25 0 0 1-1.25-1.25v-13Zm5.25 0a1.25 1.25 0 0 1 1.25-1.25h1.75a1.25 1.25 0 0 1 1.25 1.25v13a1.25 1.25 0 0 1-1.25 1.25h-1.75a1.25 1.25 0 0 1-1.25-1.25v-13Z"/></svg>';
        var prevSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';
        var nextSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';

        function enhanceMusicIcon(icon) {
            if (!icon) return;
            var text = String(icon.textContent || '').trim();
            var currentState = icon.dataset.aaPublicMusicState || '';
            var nextState = text ? (/❚|\|\||pause|jeda/i.test(text) ? 'pause' : 'play') : (currentState || 'play');
            if (icon.querySelector('svg') && currentState === nextState) return;
            icon.dataset.aaPublicMusicState = nextState;
            icon.innerHTML = nextState === 'pause' ? pauseSvg : playSvg;
        }

        function scrollStickerTrack(track, direction) {
            if (!track) return;
            var amount = Math.max(86, Math.round(track.clientWidth * .75));
            track.scrollBy({
                left: direction * amount,
                behavior: 'smooth'
            });
        }

        function createStickerNav(label, svg, direction, track) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'aa-fabric-sticker-nav';
            button.setAttribute('aria-label', label);
            button.innerHTML = svg;
            button.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                scrollStickerTrack(track, direction);
            });
            return button;
        }

        function enhanceStickerPopover(popover) {
            if (!popover || popover.classList.contains('aa-public-sticker-enhanced')) return;

            var track = popover.querySelector('.aa-fabric-sticker-track');
            if (!track) {
                track = document.createElement('div');
                track.className = 'aa-fabric-sticker-track';
                Array.from(popover.querySelectorAll('button[data-sticker]')).forEach(function(choice) {
                    choice.classList.add('aa-fabric-sticker-choice');
                    track.appendChild(choice);
                });
            } else {
                track.querySelectorAll('button[data-sticker]').forEach(function(choice) {
                    choice.classList.add('aa-fabric-sticker-choice');
                });
            }

            if (!track.parentElement || track.parentElement !== popover) {
                popover.appendChild(track);
            }

            if (!popover.querySelector('.aa-fabric-sticker-nav')) {
                popover.insertBefore(createStickerNav('Stiker sebelumnya', prevSvg, -1, track), track);
                popover.appendChild(createStickerNav('Stiker berikutnya', nextSvg, 1, track));
            }

            popover.classList.add('aa-public-sticker-enhanced');
        }

        var scheduled = false;
        function enhancePublicControls() {
            scheduled = false;
            document.querySelectorAll('.aa-fabric-music-icon').forEach(enhanceMusicIcon);
            document.querySelectorAll('.aa-fabric-sticker-popover').forEach(enhanceStickerPopover);
        }

        function scheduleEnhance() {
            if (scheduled) return;
            scheduled = true;
            window.requestAnimationFrame ? requestAnimationFrame(enhancePublicControls) : setTimeout(enhancePublicControls, 16);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', scheduleEnhance, { once: true });
        } else {
            scheduleEnhance();
        }

        document.addEventListener('click', function(event) {
            if (event.target.closest('.aa-fabric-music-button, .aa-fabric-sticker-popover, [data-guestbook-role="guest-sticker-picker"]')) {
                setTimeout(scheduleEnhance, 0);
                setTimeout(scheduleEnhance, 160);
                setTimeout(scheduleEnhance, 420);
            }
        }, true);

        if (window.MutationObserver && document.body) {
            var observer = new MutationObserver(scheduleEnhance);
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    })();
    </script>
   <script>
(function() {
    if (window.AdaAcaraDisablePublicAutoReload === true || window.AdaAcaraTemplatePreview === true) return;
    if (window.location.protocol === 'about:' || window.location.pathname.indexOf('/srcdoc') !== -1) return;
    if (window.self !== window.top && window.location.pathname.indexOf('/templates/preview/') !== -1) return;

    var key = 'page_auto_reloaded:' + window.location.pathname;
    var now = Date.now();
    var lastReload = localStorage.getItem(key);
    
    if (lastReload && (now - parseInt(lastReload, 10) < 5000)) return;

    localStorage.setItem(key, String(now));

    window.setTimeout(function() {
        var params = new URLSearchParams(window.location.search || '');
        params.set('t', String(now));

        var nextUrl = window.location.pathname + '?' + params.toString() + window.location.hash;
        
        window.location.replace(nextUrl);
    }, 50);
})();
</script>
    <?php endif ?>
</body>

</html>
