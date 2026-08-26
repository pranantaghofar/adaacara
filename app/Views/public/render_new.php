<?php
    if (! function_exists('aa_public_render_clean_string')) {
        function aa_public_render_clean_string(mixed $value, string $fallback = ''): string
        {
            $value = trim(strip_tags((string) ($value ?? '')));

            return $value !== '' ? $value : $fallback;
        }
    }

    if (! function_exists('aa_public_render_asset')) {
        function aa_public_render_asset(string $path): string
        {
            return function_exists('aa_asset_url') ? aa_asset_url($path) : site_url($path);
        }
    }

    if (! function_exists('aa_public_render_sanitize_fabric')) {
        function aa_public_render_sanitize_fabric(mixed $value): mixed
        {
            if (! is_array($value)) {
                return $value;
            }

            $type = (string) ($value['type'] ?? '');
            if (in_array($type, ['i-text', 'textbox', 'text'], true)) {
                unset($value['clipPath']);
            }

            foreach ($value as $key => $item) {
                if ($key === 'textBaseline' && $item === 'alphabetical') {
                    $value[$key] = 'alphabetic';
                    continue;
                }

                $value[$key] = aa_public_render_sanitize_fabric($item);
            }

            return $value;
        }
    }

    if (! function_exists('aa_public_render_payload')) {
        function aa_public_render_payload(string $json): ?array
        {
            $data = json_decode($json, true);
            if (! is_array($data)) {
                return null;
            }

            if (($data['renderer'] ?? '') !== 'fabric' && empty($data['pages']) && empty($data['objects'])) {
                return null;
            }

            if (($data['renderer'] ?? '') !== 'fabric') {
                $data['renderer'] = 'fabric';
            }

            return aa_public_render_sanitize_fabric($data);
        }
    }

    if (! function_exists('aa_public_render_pages')) {
        function aa_public_render_pages(?array $payload): array
        {
            if (! is_array($payload)) {
                return [];
            }

            if (isset($payload['pages']) && is_array($payload['pages'])) {
                return array_values(array_filter($payload['pages'], static fn ($page): bool => is_array($page) && ($page['hidden'] ?? false) !== true));
            }

            if (isset($payload['objects']) && is_array($payload['objects'])) {
                return [$payload];
            }

            return [];
        }
    }

    if (! function_exists('aa_public_render_color')) {
        function aa_public_render_color(mixed $value, string $fallback = '#ffffff'): string
        {
            $value = trim((string) ($value ?? ''));
            if (preg_match('/^#[0-9a-f]{3,8}$/i', $value) || preg_match('/^(rgba?|hsla?)\([0-9.,%\s-]+\)$/i', $value)) {
                return $value;
            }

            return $fallback;
        }
    }

    if (! function_exists('aa_public_render_font_url')) {
        function aa_public_render_font_url(array $payload): string
        {
            $families = [];
            $walk = static function ($value) use (&$walk, &$families): void {
                if (! is_array($value)) {
                    return;
                }

                if (isset($value['fontFamily'])) {
                    $family = trim((string) $value['fontFamily'], " \t\n\r\0\x0B'\"");
                    if ($family !== '') {
                        $families[$family] = true;
                    }
                }

                foreach ($value as $item) {
                    if (is_array($item)) {
                        $walk($item);
                    }
                }
            };
            $walk($payload);
            $families['Inter'] = true;

            $knownGoogle = [
                'Abril Fatface', 'Alice', 'Allura', 'Amiri', 'Arimo', 'Averia Serif Libre', 'Barlow',
                'Bebas Neue', 'Bellefair', 'Bitter', 'Black Ops One', 'Bodoni Moda', 'Bonheur Royale',
                'Cabin', 'Caudex', 'Caveat', 'Changa One', 'Cinzel', 'Cookie', 'Cormorant Garamond',
                'Cormorant Infant', 'Cormorant Upright', 'Courgette', 'Crimson Text', 'DM Sans',
                'DM Serif Display', 'Dancing Script', 'Dosis', 'EB Garamond', 'Elsie', 'Ephesis',
                'Figtree', 'Fira Sans', 'Fleur De Leah', 'Forum', 'Fraunces', 'Great Vibes',
                'Heebo', 'IBM Plex Sans', 'Imperial Script', 'Inconsolata', 'Instrument Serif',
                'Inter', 'Inter Tight', 'Italiana', 'Italianno', 'JetBrains Mono', 'Josefin Sans',
                'Jost', 'Kanit', 'Karla', 'Lavishly Yours', 'Libre Baskerville', 'Libre Franklin',
                'Lobster Two', 'Lora', 'Manrope', 'Marcellus', 'Mea Culpa', 'Merriweather',
                'Monsieur La Doulaise', 'Montserrat', 'Mulish', 'Noto Naskh Arabic', 'Noto Sans',
                'Noto Serif', 'Nunito', 'Nunito Sans', 'Open Sans', 'Oswald', 'Outfit', 'Oxygen',
                'PT Serif', 'Pacifico', 'Parisienne', 'Petit Formal Script', 'Philosopher',
                'Playfair Display', 'Plus Jakarta Sans', 'Poiret One', 'Poppins', 'Prata', 'Prompt',
                'Public Sans', 'Questrial', 'Quicksand', 'Quintessential', 'Raleway',
                'Red Hat Display', 'Roboto', 'Roboto Mono', 'Roboto Slab', 'Rubik', 'Sacramento',
                'Satisfy', 'Sora', 'Sorts Mill Goudy', 'Source Code Pro', 'Source Sans 3',
                'Space Grotesk', 'Tangerine', 'The Nautigal', 'Titillium Web', 'Ubuntu', 'Unna',
                'Urbanist', 'Viaoda Libre', 'WindSong', 'Work Sans', 'Yeseva One',
            ];

            $query = [];
            foreach (array_keys($families) as $family) {
                if (! in_array($family, $knownGoogle, true)) {
                    continue;
                }
                $query[] = 'family=' . str_replace('%20', '+', rawurlencode($family)) . ':wght@300;400;500;600;700;800;900';
            }

            if ($query === []) {
                $query[] = 'family=Inter:wght@300;400;500;600;700;800;900';
            }

            return 'https://fonts.googleapis.com/css2?' . implode('&', $query) . '&display=swap';
        }
    }

    $page = is_array($page ?? null) ? $page : [];
    $isPreviewPage = ! empty($isPreview);
    $title = aa_public_render_clean_string($page['seo_title'] ?? $page['title'] ?? $page['name'] ?? null, 'AdaAcara');
    $description = aa_public_render_clean_string($page['seo_description'] ?? $page['description'] ?? null, 'Undangan digital dari AdaAcara.');
    $hasPublishedJson = ! empty($page['published_editor_json']);
    $legacyHtml = $hasPublishedJson
        ? (string) ($page['published_html'] ?? $page['html'] ?? '')
        : (string) ($page['html'] ?? $page['published_html'] ?? '');
    $legacyCss = $hasPublishedJson
        ? (string) ($page['published_css'] ?? $page['css'] ?? '')
        : (string) ($page['css'] ?? $page['published_css'] ?? '');
    $editorJson = $hasPublishedJson
        ? (string) ($page['published_editor_json'] ?? $page['editor_json'] ?? $page['grapesjs_json'] ?? '')
        : (string) ($page['editor_json'] ?? $page['grapesjs_json'] ?? $page['published_editor_json'] ?? '');
    $payload = aa_public_render_payload($editorJson);
    $pages = aa_public_render_pages($payload);
    $projectIntent = strtolower(trim((string) (
        $payload['projectIntent']
        ?? $payload['project_intent']
        ?? $page['project_type']
        ?? ''
    )));
    $isBusinessProfile = in_array($projectIntent, ['business_profile', 'business-profile'], true);
    $opening = is_array($payload['opening'] ?? null) ? $payload['opening'] : [];
    $openingObjects = is_array($opening['objects'] ?? null) ? $opening['objects'] : [];
    $openingArtboard = is_array($opening['artboard'] ?? null) ? $opening['artboard'] : [];
    $openingPayload = [
        'renderer' => 'fabric',
        'objects' => $openingObjects,
        'background' => aa_public_render_color($opening['background'] ?? '#0f766e', '#0f766e'),
        'backgroundColor' => aa_public_render_color($opening['backgroundColor'] ?? $opening['background'] ?? '#0f766e', '#0f766e'),
        'artboard' => [
            'width' => max(1, (int) ($openingArtboard['width'] ?? 1080)),
            'height' => max(1, (int) ($openingArtboard['height'] ?? 1920)),
        ],
    ];
    $hasOpeningButton = false;
    foreach ($openingObjects as $openingObject) {
        if (is_array($openingObject) && ($openingObject['customType'] ?? '') === 'opening-button') {
            $hasOpeningButton = true;
            break;
        }
    }
	    $showOpening = ! $isPreviewPage
	        && ! $isBusinessProfile
	        && (($opening['enabled'] ?? true) !== false)
	        && (($opening['mode'] ?? 'default') === 'custom')
	        && $openingObjects !== []
	        && $hasOpeningButton;
	    $fontUrl = $payload ? aa_public_render_font_url($payload) : 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap';
	    $guestbookEntriesForRender = is_array($guestbookEntries ?? null) ? array_values($guestbookEntries) : [];
	    $guestbookEndpoint = isset($page['slug']) && trim((string) $page['slug']) !== ''
	        ? site_url('u/' . trim((string) $page['slug']) . '/guestbook')
	        : '';
	?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="<?= esc($description, 'attr') ?>">
    <title><?= esc($title) ?></title>
    <link rel="icon" type="image/png" href="<?= esc(aa_public_render_asset('assets/img/logo2.png'), 'attr') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link id="aaPublicFontStylesheet" rel="stylesheet" href="<?= esc($fontUrl, 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(site_url('custom-fonts.css'), 'attr') ?>">
    <script src="<?= esc(aa_public_render_asset('assets/js/fabric.min.js'), 'attr') ?>"></script>
    <style>
        <?= $payload === null && trim($legacyCss) !== '' ? $legacyCss : '' ?>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: #f8fafc; color: #0f172a; font-family: Inter, Arial, sans-serif; }
        body.aa-public-no-scroll { overflow: hidden; }
        .aa-public-empty { min-height: 100vh; display: grid; place-items: center; padding: 24px; text-align: center; }
        .aa-public-empty-card { max-width: 420px; border: 1px solid #e2e8f0; border-radius: 22px; background: #fff; padding: 28px; box-shadow: 0 24px 80px rgba(15, 23, 42, .08); }
        .aa-public-empty-card h1 { margin: 0 0 10px; font-size: 24px; }
        .aa-public-empty-card p { margin: 0; color: #64748b; line-height: 1.6; }
        .aa-fabric-page { display: grid; justify-items: center; gap: 0; width: 100%; }
        .aa-fabric-page-section { width: 100%; display: grid; justify-items: center; margin: 0; padding: 0; }
        .aa-fabric-artboard {
            position: relative;
            width: min(100%, var(--aa-artboard-max-width, 520px));
            max-width: 100%;
            aspect-ratio: var(--aa-artboard-width, 1080) / var(--aa-artboard-height, 1920);
            background: var(--aa-page-bg, #fff);
            overflow: hidden;
            isolation: isolate;
        }
	        .aa-fabric-artboard canvas,
	        .aa-fabric-artboard .canvas-container { position: relative; z-index: 1; width: 100% !important; height: 100% !important; display: block; }
        .aa-fabric-artboard.is-rendering::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 20;
            background: linear-gradient(105deg, transparent, rgba(226, 232, 240, .72), transparent), var(--aa-page-bg, #fff);
            background-size: 240% 100%, 100% 100%;
            animation: aaRenderShimmer 1.1s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes aaRenderShimmer { from { background-position: 180% 0, 0 0; } to { background-position: -80% 0, 0 0; } }
        @media (min-width: 680px) {
            body:not(.aa-business-profile-public) .aa-fabric-artboard { width: min(100%, var(--aa-artboard-max-width, 560px)); }
        }
	        .aa-fabric-click-layer,
	        .aa-fabric-interactive-layer { position: absolute; inset: 0; z-index: 30; pointer-events: none; }
	        .aa-fabric-guestbook-layer { position: absolute; inset: 0; z-index: 28; pointer-events: none; }
	        .aa-fabric-guestbook-control { position: absolute; box-sizing: border-box; pointer-events: auto; font-family: Inter, Arial, sans-serif; line-height: 1.15; }
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
	        .aa-fabric-guestbook-control input::placeholder,
	        .aa-fabric-guestbook-control textarea::placeholder { color: currentColor; opacity: .72; }
	        .aa-fabric-guestbook-control textarea { resize: none; }
	        .aa-fabric-guestbook-control button { display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: 800; }
	        .aa-fabric-guestbook-control button:disabled { cursor: wait; opacity: .72; }
	        .aa-fabric-guestbook-control[data-guestbook-role="guest-name-input"] input,
	        .aa-fabric-guestbook-control[data-guestbook-role="guest-attendance-select"] select,
	        .aa-fabric-guestbook-control[data-guestbook-role="guest-sticker-picker"] button,
	        .aa-fabric-guestbook-control[data-guestbook-role="guest-submit-button"] button { overflow: hidden; white-space: nowrap; }
	        .aa-fabric-sticker-popover {
	            position: absolute;
	            left: 0;
	            bottom: calc(100% + 8px);
	            z-index: 5;
	            display: none;
	            grid-template-columns: auto minmax(0, 1fr) auto;
	            align-items: center;
	            gap: 8px;
	            width: min(380px, 88vw);
	            max-width: calc(100vw - 28px);
	            border: 1px solid #e2e8f0;
	            border-radius: 18px;
	            background: rgba(255, 255, 255, .96);
	            padding: 10px;
	            box-shadow: 0 18px 48px rgba(15, 23, 42, .18);
	            backdrop-filter: blur(12px);
	            -webkit-backdrop-filter: blur(12px);
	        }
	        .aa-fabric-sticker-popover.is-open { display: grid; }
	        .aa-fabric-sticker-track { display: flex; min-width: 0; gap: 8px; overflow-x: auto; overscroll-behavior-x: contain; scroll-behavior: smooth; scroll-snap-type: x proximity; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
	        .aa-fabric-sticker-track::-webkit-scrollbar { display: none; }
	        .aa-fabric-sticker-choice { display: inline-grid; width: 68px; height: 68px; flex: 0 0 68px; place-items: center; border: 1px solid rgba(226, 232, 240, .88); border-radius: 16px; background: rgba(255, 255, 255, .48); padding: 6px; cursor: pointer; scroll-snap-align: center; box-shadow: inset 0 1px 0 rgba(255, 255, 255, .72); }
	        .aa-fabric-sticker-choice.is-selected { border-color: #0f766e; background: rgba(204, 251, 241, .72); box-shadow: inset 0 0 0 1px rgba(15, 118, 110, .24), 0 10px 20px rgba(15, 118, 110, .12); }
	        .aa-fabric-sticker-choice img { display: block; width: 54px; height: 54px; object-fit: contain; }
	        .aa-fabric-sticker-nav { display: inline-grid; width: 34px; height: 52px; flex: 0 0 34px; place-items: center; border: 1px solid rgba(226, 232, 240, .86); border-radius: 999px; background: rgba(255, 255, 255, .72); color: #0f766e; cursor: pointer; box-shadow: 0 10px 24px rgba(15, 23, 42, .12); }
	        .aa-fabric-sticker-nav svg { width: 17px; height: 17px; }
	        .aa-fabric-selected-sticker { position: absolute; top: calc(100% + 8px); left: 0; display: none; align-items: center; gap: 7px; border-radius: 999px; background: rgba(255, 255, 255, .96); padding: 5px 8px; color: inherit; font: 700 11px Inter, Arial, sans-serif; box-shadow: 0 10px 24px rgba(15, 23, 42, .16); }
	        .aa-fabric-selected-sticker.is-visible { display: inline-flex; }
	        .aa-fabric-selected-sticker img { width: 28px; height: 28px; object-fit: contain; }
	        .aa-fabric-selected-sticker button { width: auto; height: auto; min-height: 0; border: 0; border-radius: 999px; background: #fee2e2; color: #be123c; padding: 5px 8px; font: 800 10px Inter, Arial, sans-serif; }
	        .aa-fabric-comment-list { width: 100%; height: 100%; overflow-y: auto; display: grid; gap: 8px; padding: 10px; border: 1px solid #e2e8f0; border-radius: inherit; background: inherit; color: inherit; }
	        .aa-fabric-comment-card,
	        .aa-fabric-comment-empty { border: 1px solid #e2e8f0; border-radius: 12px; background: rgba(255, 255, 255, .82); color: inherit; padding: 10px; }
	        .aa-fabric-comment-card strong,
	        .aa-fabric-comment-card p,
	        .aa-fabric-comment-empty { color: inherit; }
	        .aa-fabric-bg-gif-layer,
	        .aa-fabric-gif-under-layer,
	        .aa-fabric-gif-layer { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
	        .aa-fabric-bg-gif-layer,
	        .aa-fabric-gif-under-layer { z-index: 0; }
	        .aa-fabric-gif-layer { z-index: 26; }
	        .aa-fabric-bg-gif-layer img,
	        .aa-fabric-gif-under-layer img,
	        .aa-fabric-gif-layer img,
	        .aa-fabric-gif-crop-frame { position: absolute; display: block; transform-origin: center center; pointer-events: none; }
	        .aa-fabric-gif-crop-frame { overflow: hidden; }
	        .aa-fabric-gif-crop-frame img { max-width: none; max-height: none; }
	        .aa-fabric-hotspot,
	        .aa-fabric-interactive-control { position: absolute; pointer-events: auto; }
        .aa-fabric-hotspot { display: block; border: 0; background: transparent; color: transparent; padding: 0; cursor: pointer; }
        .aa-fabric-interactive-control { display: grid; place-items: center; transform-origin: center center; }
        .aa-fabric-music-button {
            width: 100%; height: 100%; display: grid; place-items: center; border: var(--aa-control-border-width, 0) solid var(--aa-control-border-color, transparent);
            border-radius: inherit; background: var(--aa-control-bg, rgba(255,255,255,.88)); color: var(--aa-control-color, #0f172a);
            cursor: pointer; padding: 0; font: inherit;
        }
        .aa-fabric-music-icon { display: grid; place-items: center; width: 100%; height: 100%; }
        .aa-fabric-music-icon svg { width: 1.08em; height: 1.08em; display: block; }
        .aa-fabric-social-box { width: 100%; height: 100%; display: grid; align-content: center; gap: .45em; padding: .6em; overflow: hidden; }
        .aa-fabric-social-box strong { display: block; font-size: .78em; line-height: 1.1; }
	        .aa-fabric-social-row { display: flex; flex-wrap: wrap; justify-content: center; gap: .42em; }
	        .aa-fabric-social-link { width: 1.9em; height: 1.9em; display: inline-grid; place-items: center; border-radius: 999px; background: #0f766e; color: #fff; text-decoration: none; box-shadow: 0 .4em 1.2em rgba(15,23,42,.12); }
		        .aa-fabric-social-link svg { width: 1.05em; height: 1.05em; display: block; fill: currentColor; }
	        .aa-social-instagram { background: #e1306c; }
	        .aa-social-tiktok { background: #111827; }
	        .aa-social-threads { background: #000000; }
	        .aa-social-x { background: #0f172a; }
	        .aa-social-facebook { background: #1877f2; }
	        .aa-social-youtube { background: #ff0000; }
	        .aa-social-whatsapp { background: #25d366; }
	        .aa-fabric-social-empty { color: #64748b; font-size: .72em; font-weight: 800; text-align: center; }
		        .aa-fabric-scroll-button { width: 100%; height: 100%; display: grid; place-items: center; border: var(--aa-control-border-width, 0) solid var(--aa-control-border-color, transparent); border-radius: inherit; background: var(--aa-control-bg, rgba(255,255,255,.88)); color: var(--aa-control-color, #0f172a); cursor: pointer; padding: 0; font: inherit; }
		        .aa-fabric-countdown { width: 100%; height: 100%; display: grid; grid-template-columns: repeat(var(--aa-countdown-columns, 4), minmax(0, 1fr)); align-items: stretch; gap: var(--aa-countdown-gap, 8px); padding: .28em; overflow: hidden; }
		        .aa-fabric-countdown span { display: grid; place-items: center; align-content: center; min-width: 0; min-height: 0; border-radius: var(--aa-countdown-card-radius, inherit); background: var(--aa-control-bg, rgba(255,255,255,.84)); color: var(--aa-control-color, #0f172a); line-height: 1; }
		        .aa-fabric-countdown strong,
		        .aa-fabric-countdown small { display: block; color: inherit; line-height: 1; }
		        .aa-fabric-countdown strong { font-size: 1em; font-weight: 900; }
		        .aa-fabric-countdown small { margin-top: .35em; font-size: .34em; font-weight: 800; letter-spacing: 0; opacity: .74; }
		        .aa-fabric-gallery { width: 100%; height: 100%; display: grid; overflow: hidden; }
		        .aa-fabric-gallery button { display: block; min-width: 0; min-height: 0; width: 100%; height: 100%; border: 0; padding: 0; background: transparent; overflow: hidden; cursor: zoom-in; }
		        .aa-fabric-gallery img { display: block; width: 100%; height: 100%; object-fit: cover; }
		        .aa-fabric-youtube-frame,
		        .aa-fabric-youtube-placeholder { width: 100%; height: 100%; border: 0; border-radius: inherit; overflow: hidden; background: #0f172a; color: #fff; }
		        .aa-fabric-youtube-placeholder { display: grid; place-items: center; padding: 12px; text-align: center; font: 800 12px/1.35 Inter, Arial, sans-serif; }
		        .aa-fabric-overlay-animated { --aa-overlay-base-transform: rotate(0deg); --aa-overlay-animation-duration: 900ms; --aa-overlay-animation-delay: 0ms; --aa-overlay-final-opacity: 1; animation-duration: var(--aa-overlay-animation-duration); animation-delay: var(--aa-overlay-animation-delay); animation-fill-mode: both; animation-timing-function: cubic-bezier(.22, 1, .36, 1); transform-origin: center center; }
		        .aa-fabric-overlay-animation-waiting { animation-play-state: paused; }
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
		        .aa-overlay-animation-drift-loop { animation-duration: var(--aa-overlay-loop-duration, var(--aa-overlay-animation-duration)); animation-delay: var(--aa-overlay-animation-delay); animation-iteration-count: infinite; animation-timing-function: ease-in-out; }
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
		        .aa-copy-toast { position: fixed; left: 50%; bottom: 24px; z-index: 10050; transform: translateX(-50%) translateY(16px); opacity: 0; transition: .24s ease; border-radius: 999px; background: rgba(15, 23, 42, .94); color: #fff; padding: 10px 16px; font: 800 13px Inter, Arial, sans-serif; pointer-events: none; }
	        .aa-copy-toast.is-visible { opacity: 1; transform: translateX(-50%) translateY(0); }
	        .aa-fabric-lightbox { position: fixed; inset: 0; z-index: 10040; display: grid; place-items: center; padding: 22px; background: rgba(15, 23, 42, .58); backdrop-filter: blur(10px); opacity: 0; visibility: hidden; pointer-events: none; transition: opacity .22s ease, visibility .22s ease; }
	        .aa-fabric-lightbox.is-open { opacity: 1; visibility: visible; pointer-events: auto; }
	        .aa-fabric-lightbox img { display: block; max-width: min(94vw, 980px); max-height: 86vh; border-radius: 18px; box-shadow: 0 24px 90px rgba(15, 23, 42, .35); object-fit: contain; }
	        .aa-fabric-lightbox button { position: fixed; top: max(16px, env(safe-area-inset-top)); right: max(16px, env(safe-area-inset-right)); width: 44px; height: 44px; border: 0; border-radius: 999px; background: rgba(255, 255, 255, .95); color: #0f172a; font: 900 20px/1 Inter, Arial, sans-serif; cursor: pointer; box-shadow: 0 12px 32px rgba(15, 23, 42, .22); }
	        .aa-opening-modal { position: fixed; inset: 0; z-index: 10000; display: grid; place-items: center; padding: 22px; background: rgba(15, 23, 42, .34); backdrop-filter: blur(2px); opacity: 0; pointer-events: none; transition: opacity .82s ease; }
        .aa-opening-modal.is-visible { opacity: 1; pointer-events: auto; }
        .aa-opening-modal.is-leaving { opacity: 0; pointer-events: none; }
        .aa-opening-card { position: relative; width: min(88vw, 430px); max-height: min(88vh, 760px); aspect-ratio: var(--aa-opening-width, 1080) / var(--aa-opening-height, 1920); overflow: hidden; border-radius: 30px; background: rgba(15,23,42,.18); box-shadow: 0 30px 90px rgba(15,23,42,.32); transform: translateY(18px) scale(.96); transition: transform .82s cubic-bezier(.2,.8,.2,1), opacity .82s ease; }
        .aa-opening-modal.is-visible .aa-opening-card { transform: translateY(0) scale(1); }
        .aa-opening-modal.is-leaving .aa-opening-card { opacity: 0; transform: translateY(10px) scale(.97); }
        .aa-opening-custom-stage { position: absolute; inset: 0; }
        .aa-opening-custom-stage canvas { display: block; width: 100% !important; height: 100% !important; }
        .aa-opening-custom-hotspot { position: absolute; z-index: 5; border: 0; background: transparent; padding: 0; cursor: pointer; }
    </style>
</head>
<body class="<?= $isBusinessProfile ? 'aa-business-profile-public' : 'aa-invitation-public' ?>">
<?php if ($pages === [] && trim($legacyHtml) !== ''): ?>
    <?= $legacyHtml ?>
<?php elseif ($pages === []): ?>
    <main class="aa-public-empty">
        <section class="aa-public-empty-card">
            <h1>Halaman belum siap</h1>
            <p>Data desain belum tersedia atau sedang dipulihkan.</p>
        </section>
    </main>
<?php else: ?>
    <?php if ($showOpening): ?>
        <div id="aaOpeningModal" class="aa-opening-modal" role="dialog" aria-modal="true" aria-label="Opening undangan">
            <section class="aa-opening-card" style="--aa-opening-width:<?= (int) $openingPayload['artboard']['width'] ?>;--aa-opening-height:<?= (int) $openingPayload['artboard']['height'] ?>;">
                <div class="aa-opening-custom-stage">
                    <canvas id="aaOpeningFabricCanvas" aria-label="Opening undangan"></canvas>
                </div>
                <button id="aaOpeningButton" class="aa-opening-custom-hotspot" type="button" aria-label="Buka Undangan"></button>
            </section>
        </div>
    <?php endif ?>
    <main class="aa-fabric-page" id="aaFabricPage">
        <?php foreach ($pages as $index => $pageData): ?>
            <?php
                $artboard = is_array($pageData['artboard'] ?? null) ? $pageData['artboard'] : [];
                $width = max(1, (int) ($artboard['width'] ?? 1080));
                $height = max(1, (int) ($artboard['height'] ?? 1920));
                $ratio = $width / $height;
                $maxWidth = $ratio >= 1 ? 960 : ($height > 2600 ? 720 : 560);
                $background = aa_public_render_color($pageData['background'] ?? $pageData['backgroundColor'] ?? '#ffffff', '#ffffff');
            ?>
            <section class="aa-fabric-page-section" data-aa-page-index="<?= $index ?>">
                <div class="aa-fabric-artboard is-rendering" style="--aa-artboard-width:<?= $width ?>;--aa-artboard-height:<?= $height ?>;--aa-artboard-max-width:<?= $maxWidth ?>px;--aa-page-bg:<?= esc($background, 'attr') ?>;">
                    <canvas id="aaFabricPublicCanvas<?= $index ?>" aria-label="<?= esc((string) ($pageData['title'] ?? 'Halaman ' . ($index + 1)), 'attr') ?>"></canvas>
                </div>
            </section>
        <?php endforeach ?>
    </main>
<?php endif ?>
<script>
(function() {
    'use strict';

	    var fabricData = <?= json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: 'null' ?>;
	    var openingData = <?= $showOpening ? (json_encode($openingPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: 'null') : 'null' ?>;
	    window.AdaAcaraGuestbookEndpoint = <?= json_encode($guestbookEndpoint, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '""' ?>;
	    window.AdaAcaraGuestbookEntries = <?= json_encode($guestbookEntriesForRender, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '[]' ?>;
	    window.AdaAcaraGuestbookCsrf = {
	        name: <?= json_encode(function_exists('csrf_token') ? csrf_token() : '') ?>,
	        hash: <?= json_encode(function_exists('csrf_hash') ? csrf_hash() : '') ?>
	    };
	    window.AdaAcaraStickerBase = <?= json_encode(aa_public_render_asset('assets/stiker') . '/') ?>;
	    var fontLoadCache = {};
    var pendingOpeningCallbacks = [];
    var hasOpening = Boolean(document.getElementById('aaOpeningModal') && document.getElementById('aaOpeningButton'));

    window.AdaAcaraPublicInvitationOpened = !hasOpening;
    window.AdaAcaraRunWhenInvitationOpened = function(callback) {
        if (typeof callback !== 'function') return;
        if (window.AdaAcaraPublicInvitationOpened) {
            callback();
            return;
        }
        pendingOpeningCallbacks.push(callback);
    };

    function releaseOpeningCallbacks() {
        var callbacks = pendingOpeningCallbacks.slice();
        pendingOpeningCallbacks = [];
        callbacks.forEach(function(callback) {
            try { callback(); } catch (error) { console.error(error); }
        });
    }

    function pagesFromData(data) {
        if (!data) return [];
        if (Array.isArray(data.pages)) return data.pages.filter(function(page) { return page && page.hidden !== true; });
        if (Array.isArray(data.objects)) return [data];
        return [];
    }

	    function isTextObject(object) {
	        return object && ['i-text', 'textbox', 'text'].indexOf(String(object.type || '')) !== -1;
	    }

	    function getObjectAnimationName(object) {
	        return String((object && (object.aaAnimation || object.customAnimation || object.animationPreset || object.animation || object.animationName)) || 'none');
	    }

	    function hasObjectAnimation(object) {
	        var animationName = getObjectAnimationName(object);
	        return Boolean(animationName && animationName !== 'none');
	    }

	    function walkObjects(objects, callback) {
	        (objects || []).forEach(function(object) {
            callback(object);
            if (object && typeof object.getObjects === 'function') walkObjects(object.getObjects(), callback);
        });
    }

    function walkRawObjects(value, callback) {
        if (!value || typeof value !== 'object') return;
        callback(value);
        var children = Array.isArray(value.objects) ? value.objects : [];
        children.forEach(function(child) { walkRawObjects(child, callback); });
    }

    function cleanFontFamily(family) {
        return String(family || 'Inter').replace(/^["']|["']$/g, '').trim() || 'Inter';
    }

    function normalizeWeight(weight) {
        weight = String(weight || '400').toLowerCase() === 'bold' ? '700' : String(weight || '400');
        if (!/^[1-9]00$/.test(weight)) return Number(weight) >= 600 ? '700' : '400';
        return weight;
    }

    function collectFontVariants(raw) {
        var variants = [];
        function add(family, weight, style) {
            family = cleanFontFamily(family);
            weight = normalizeWeight(weight);
            style = String(style || 'normal').toLowerCase() === 'italic' ? 'italic' : 'normal';
            var key = family + '|' + weight + '|' + style;
            if (!variants.some(function(item) { return item.key === key; })) {
                variants.push({ key: key, family: family, weight: weight, style: style });
            }
        }
        walkRawObjects(raw, function(object) {
            if (object && ['i-text', 'textbox', 'text'].indexOf(String(object.type || '')) !== -1) {
                add(object.fontFamily, object.fontWeight, object.fontStyle);
            }
        });
        add('Inter', '400', 'normal');
        return variants;
    }

    function waitFonts(raw) {
        if (!document.fonts || typeof document.fonts.load !== 'function') return Promise.resolve();
        var loads = collectFontVariants(raw).map(function(variant) {
            if (fontLoadCache[variant.key]) return fontLoadCache[variant.key];
            fontLoadCache[variant.key] = document.fonts.load(variant.style + ' ' + variant.weight + ' 32px "' + variant.family.replace(/"/g, '') + '"').catch(function() { return null; });
            return fontLoadCache[variant.key];
        });
        return Promise.all(loads).then(function() {
            return document.fonts.ready || null;
        }).then(function() {
            return new Promise(function(resolve) { setTimeout(resolve, 80); });
        }).catch(function() { return null; });
    }

    function sanitizeRawObject(object) {
        if (!object || typeof object !== 'object') return;
        if (object.textBaseline === 'alphabetical') object.textBaseline = 'alphabetic';
        if (isTextObject(object)) delete object.clipPath;
        if (object.styles && typeof object.styles === 'object') {
            Object.keys(object.styles).forEach(function(lineKey) {
                var line = object.styles[lineKey];
                if (!line || typeof line !== 'object') return;
                Object.keys(line).forEach(function(charKey) {
                    if (line[charKey] && line[charKey].textBaseline === 'alphabetical') line[charKey].textBaseline = 'alphabetic';
                });
            });
        }
        if (Array.isArray(object.objects)) object.objects.forEach(sanitizeRawObject);
    }

    function sanitizePage(page) {
        page = JSON.parse(JSON.stringify(page || {}));
        if (Array.isArray(page.objects)) page.objects.forEach(sanitizeRawObject);
        return page;
    }

	    function installRoundedImageRenderer() {
	        if (!window.fabric || fabric.Image.prototype.__aaPublicStage1RoundedRenderer) return;
	        var originalRender = fabric.Image.prototype._render;
        function roundedPath(ctx, width, height, radius) {
            var r = Math.min(Math.max(0, Number(radius) || 0), width / 2, height / 2);
            var x = -width / 2;
            var y = -height / 2;
            ctx.beginPath();
            if (!r) { ctx.rect(x, y, width, height); return; }
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
	        }
	        function drawImageStroke(ctx, image, width, height, radius) {
	            var strokeWidth = Math.max(0, Number(image.strokeWidth) || 0);
	            if (!strokeWidth || !image.stroke || image.stroke === 'transparent') return;
	            ctx.save();
	            roundedPath(ctx, width, height, radius);
	            ctx.lineWidth = strokeWidth;
	            ctx.strokeStyle = image.stroke;
	            ctx.lineJoin = 'round';
	            ctx.lineCap = image.imageStrokeStyle === 'dotted' ? 'round' : 'butt';
	            if (Array.isArray(image.strokeDashArray)) ctx.setLineDash(image.strokeDashArray);
	            ctx.stroke();
	            ctx.restore();
	        }
	        function imageEffectCanvasFilter(image) {
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
	        }
	        function renderImageWithCanvasEffect(image, ctx) {
	            var filter = imageEffectCanvasFilter(image);
	            if (!filter) {
	                originalRender.call(image, ctx);
	                return;
	            }
	            var previousFilter = ctx.filter;
	            ctx.filter = filter;
	            originalRender.call(image, ctx);
	            ctx.filter = previousFilter;
	        }
	        fabric.Image.prototype._render = function(ctx) {
	            var radius = Math.max(0, Number(this.borderRadius) || 0);
	            var width = Math.max(1, this.width || 1);
	            var height = Math.max(1, this.height || 1);
	            if (radius) {
	                ctx.save();
	                roundedPath(ctx, width, height, radius);
	                ctx.clip();
	                renderImageWithCanvasEffect(this, ctx);
	                ctx.restore();
	            } else {
	                renderImageWithCanvasEffect(this, ctx);
	            }
	            drawImageStroke(ctx, this, width, height, radius);
	        };
	        fabric.Image.prototype.__aaPublicStage1RoundedRenderer = true;
	    }

    function recalculateText(canvas) {
        if (!canvas || !canvas.getObjects) return;
        if (window.fabric) {
            if (fabric.charWidthsCache) fabric.charWidthsCache = {};
            if (fabric.Text && fabric.Text.charWidthsCache) fabric.Text.charWidthsCache = {};
        }
	        walkObjects(canvas.getObjects(), function(object) {
	            if (object && object.type === 'image' && object.borderRadius && object.clipPath && (object.clipPath.rx || object.clipPath.ry)) {
	                object.clipPath = null;
	                object.dirty = true;
	                if (typeof object.setCoords === 'function') object.setCoords();
	            }
	            if (object && object.type === 'image' && object.aaImageEffectPreset && Array.isArray(object.filters) && object.filters.length) {
	                object.filters = [];
	                object.dirty = true;
	                if (typeof object.setCoords === 'function') object.setCoords();
	            }
	            if (!isTextObject(object)) return;
            object.objectCaching = false;
            object.dirty = true;
            if (typeof object.initDimensions === 'function') object.initDimensions();
            if (typeof object.setCoords === 'function') object.setCoords();
        });
    }

    function guestName() {
        var params = new URLSearchParams(window.location.search || '');
        var value = params.get('to') || params.get('tamu') || params.get('invite') || params.get('guest') || '';
        value = String(value || '').replace(/\+/g, ' ').trim();
        return value || 'Tamu Undangan';
    }

	    function applyGuestName(canvas) {
	        if (!canvas || !canvas.getObjects) return;
	        var name = guestName();
        walkObjects(canvas.getObjects(), function(object) {
            var candidate = object && (object.isGuestName === true || object.customType === 'guest_name' || object.dynamicKey === 'guest_name');
            if (!candidate) return;
            var target = object;
            if (object.getObjects) {
                var children = object.getObjects();
                target = children.find(function(child) { return child.name === 'guest-name-text'; }) || children.find(isTextObject) || object;
            }
            if (!isTextObject(target)) return;
            var template = String(target.templateText || target.text || 'Kepada Yth.\n{{guest_name}}');
            if (!/\{\{\s*guest_name\s*\}\}/i.test(template)) template = template.replace(/Nama Tamu|Tamu Undangan/gi, '{{guest_name}}');
            target.set('text', template.replace(/\{\{\s*guest_name\s*\}\}/gi, name));
            target.dirty = true;
            if (typeof target.initDimensions === 'function') target.initDimensions();
	            if (typeof target.setCoords === 'function') target.setCoords();
	        });
	    }

	    function isAnimatedGifObject(object) {
	        if (!object || object.type !== 'image') return false;
	        var src = String(object.aaAnimatedSrc || object.src || (object._element && object._element.src) || '');
	        var cleanSrc = src.split('?')[0].toLowerCase();
	        return object.aaMediaKind === 'gif' || cleanSrc.endsWith('.gif');
	    }

	    function isAnimatedLayerBlocker(object) {
	        if (!object || object.visible === false || object.__aaSkipObject === true) return false;
	        if (object.customType === 'background' || object.excludeFromAnimation === true) return false;
	        if (hasObjectAnimation(object)) return true;
	        var children = typeof object.getObjects === 'function' ? object.getObjects() : [];
	        for (var i = 0; i < children.length; i += 1) {
	            if (isAnimatedLayerBlocker(children[i])) return true;
	        }
	        return false;
	    }

	    function clampRectToCanvas(rect, canvasWidth, canvasHeight) {
	        if (!rect) return null;
	        var left = Math.max(0, Number(rect.left) || 0);
	        var top = Math.max(0, Number(rect.top) || 0);
	        var right = Math.min(canvasWidth, (Number(rect.left) || 0) + Math.max(0, Number(rect.width) || 0));
	        var bottom = Math.min(canvasHeight, (Number(rect.top) || 0) + Math.max(0, Number(rect.height) || 0));
	        if (right <= left || bottom <= top) return null;
	        return { left: left, top: top, width: right - left, height: bottom - top };
	    }

	    function animatedGifObjectOpacity(object) {
	        var value = object && object.__aaAnimationOriginal && object.__aaAnimationOriginal.opacity != null
	            ? object.__aaAnimationOriginal.opacity
	            : (object && object.opacity != null ? object.opacity : 1);
	        value = Number(value);
	        return Math.max(0, Math.min(1, isFinite(value) ? value : 1));
	    }

	    function animatedGifObjectGeometry(object, canvasWidth, canvasHeight, imageWidth, imageHeight) {
	        var isCoverBackground = object && object.customType === 'background' && (
	            object.name === 'Background Image' || object.aaBgOffsetX != null || object.aaBgOffsetY != null
	        );
	        var sourceWidth = Math.max(1, Number(imageWidth) || 0);
	        var sourceHeight = Math.max(1, Number(imageHeight) || 0);
	        var center = object.getCenterPoint ? object.getCenterPoint() : {
	            x: Number(object.left) || canvasWidth / 2,
	            y: Number(object.top) || canvasHeight / 2
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
	                center: { x: (canvasWidth / 2) + ((canvasWidth * offsetX) / 100), y: (canvasHeight / 2) + ((canvasHeight * offsetY) / 100) },
	                width: Math.max(1, sourceWidth * coverScale),
	                height: Math.max(1, sourceHeight * coverScale),
	                angle: Number(object.angle) || 0,
	                opacity: animatedGifObjectOpacity(object),
	                flipX: object.flipX ? ' scaleX(-1)' : '',
	                flipY: object.flipY ? ' scaleY(-1)' : ''
	            };
	        }
	        return {
	            center: center,
	            width: width,
	            height: height,
	            angle: Number(object.angle) || 0,
	            opacity: animatedGifObjectOpacity(object),
	            flipX: object.flipX ? ' scaleX(-1)' : '',
	            flipY: object.flipY ? ' scaleY(-1)' : ''
	        };
	    }

	    function setupAnimatedGifBackground(canvasEl, canvas) {
	        var artboard = canvasEl.closest('.aa-fabric-artboard') || canvasEl.closest('.aa-opening-card');
	        if (!artboard || !canvas || !canvas.getObjects) return;
	        var oldLayer = artboard.querySelector('.aa-fabric-bg-gif-layer');
	        if (oldLayer) oldLayer.remove();
	        var background = canvas.getObjects().find(function(object) {
	            return object && object.customType === 'background' && isAnimatedGifObject(object);
	        });
	        if (!background) return;
	        var src = background.aaAnimatedSrc || background.src || (background._element && background._element.src) || '';
	        if (!src) return;
	        background.visible = false;
	        background.evented = false;
	        background.selectable = false;
	        canvas.backgroundColor = '';
	        var canvasWidth = canvas.getWidth() || 1;
	        var canvasHeight = canvas.getHeight() || 1;
	        var layer = document.createElement('div');
	        var img = document.createElement('img');
	        layer.className = 'aa-fabric-bg-gif-layer';
	        img.alt = '';
	        img.loading = 'eager';
	        img.decoding = 'async';
	        function applyGeometry() {
	            var geometry = animatedGifObjectGeometry(background, canvasWidth, canvasHeight, img.naturalWidth, img.naturalHeight);
	            img.style.left = (geometry.center.x / canvasWidth * 100) + '%';
	            img.style.top = (geometry.center.y / canvasHeight * 100) + '%';
	            img.style.width = (geometry.width / canvasWidth * 100) + '%';
	            img.style.height = (geometry.height / canvasHeight * 100) + '%';
	            img.style.opacity = String(geometry.opacity);
	            img.style.transform = 'translate(-50%, -50%) rotate(' + geometry.angle + 'deg)' + geometry.flipX + geometry.flipY;
	        }
	        img.addEventListener('load', applyGeometry, { once: true });
	        img.addEventListener('error', function() {
	            background.visible = true;
	            canvas.requestRenderAll();
	            layer.remove();
	        }, { once: true });
	        img.src = src;
	        applyGeometry();
	        layer.appendChild(img);
	        artboard.insertBefore(layer, artboard.firstChild);
	    }

	    function setupAnimatedGifOverlay(canvasEl, canvas) {
	        var artboard = canvasEl.closest('.aa-fabric-artboard') || canvasEl.closest('.aa-opening-card');
	        if (!artboard || !canvas || !canvas.getObjects) return;
	        var oldLayer = artboard.querySelector('.aa-fabric-gif-layer');
	        if (oldLayer) oldLayer.remove();
	        var oldUnderLayer = artboard.querySelector('.aa-fabric-gif-under-layer');
	        if (oldUnderLayer) oldUnderLayer.remove();
	        var allObjects = canvas.getObjects();
	        var objects = allObjects.filter(isAnimatedGifObject);
	        if (!objects.length) return;
	        var layer = null;
	        var underLayer = null;
	        var canvasWidth = canvas.getWidth() || 1;
	        var canvasHeight = canvas.getHeight() || 1;
	        function ensureUpperLayer() {
	            if (layer) return layer;
	            layer = document.createElement('div');
	            layer.className = 'aa-fabric-gif-layer';
	            artboard.appendChild(layer);
	            return layer;
	        }
	        function ensureUnderLayer() {
	            if (underLayer) return underLayer;
	            underLayer = document.createElement('div');
	            underLayer.className = 'aa-fabric-gif-under-layer';
	            artboard.insertBefore(underLayer, artboard.firstChild);
	            canvas.backgroundColor = '';
	            return underLayer;
	        }
	        function rectsOverlap(a, b) {
	            return a && b && a.left < b.left + b.width && a.left + a.width > b.left && a.top < b.top + b.height && a.top + a.height > b.top;
	        }
	        function hasVisibleObjectAbove(object, rect) {
	            var index = allObjects.indexOf(object);
	            for (var i = index + 1; i < allObjects.length; i += 1) {
	                var above = allObjects[i];
	                if (!above || above.visible === false || ((above.opacity === 0) && !isAnimatedLayerBlocker(above)) || isAnimatedGifObject(above) || isInteractiveObject(above)) continue;
	                var aboveRect = clampRectToCanvas(above.getBoundingRect(true, true), canvasWidth, canvasHeight);
	                if (rectsOverlap(rect, aboveRect)) return true;
	            }
	            return false;
	        }
	        function cropGeometry(object) {
	            var element = object && (object._element || (typeof object.getElement === 'function' ? object.getElement() : null));
	            var sourceWidth = Math.max(1, Number(element && (element.naturalWidth || element.width)) || Number(object.width) || 1);
	            var sourceHeight = Math.max(1, Number(element && (element.naturalHeight || element.height)) || Number(object.height) || 1);
	            var cropX = Math.max(0, Math.min(Number(object.cropX) || 0, sourceWidth - 1));
	            var cropY = Math.max(0, Math.min(Number(object.cropY) || 0, sourceHeight - 1));
	            var cropWidth = Math.max(1, Math.min(Number(object.width) || sourceWidth, sourceWidth - cropX));
	            var cropHeight = Math.max(1, Math.min(Number(object.height) || sourceHeight, sourceHeight - cropY));
	            return {
	                cropped: cropX > 0.5 || cropY > 0.5 || cropWidth < sourceWidth - cropX - 0.5 || cropHeight < sourceHeight - cropY - 0.5,
	                sourceWidth: sourceWidth,
	                sourceHeight: sourceHeight,
	                cropX: cropX,
	                cropY: cropY,
	                cropWidth: cropWidth,
	                cropHeight: cropHeight
	            };
	        }
	        function restoreFabricGifObject(object, node) {
	            if (node && node.parentNode) node.parentNode.removeChild(node);
	            object.visible = true;
	            object.evented = false;
	            object.selectable = false;
	            canvas.requestRenderAll();
	        }
	        function hideFabricGifObject(object) {
	            requestAnimationFrame(function() {
	                object.visible = false;
	                object.evented = false;
	                object.selectable = false;
	                canvas.requestRenderAll();
	            });
	        }
	        objects.forEach(function(object) {
	            if (object.customType === 'background') return;
	            var src = object.aaAnimatedSrc || object.src || (object._element && object._element.src) || '';
	            if (!src) return;
	            object.visible = true;
	            object.evented = false;
	            object.selectable = false;
	            var objectRect = clampRectToCanvas(object.getBoundingRect(true, true), canvasWidth, canvasHeight);
	            if (!objectRect) return;
	            var targetLayer = hasVisibleObjectAbove(object, objectRect) ? ensureUnderLayer() : ensureUpperLayer();
	            var center = object.getCenterPoint ? object.getCenterPoint() : { x: Number(object.left) || 0, y: Number(object.top) || 0 };
	            var width = Math.max(1, Math.abs((Number(object.width) || 1) * (Number(object.scaleX) || 1)));
	            var height = Math.max(1, Math.abs((Number(object.height) || 1) * (Number(object.scaleY) || 1)));
	            var radius = Math.max(0, Number(object.borderRadius) || 0) * Math.max(Math.abs(Number(object.scaleX) || 1), Math.abs(Number(object.scaleY) || 1));
	            var crop = cropGeometry(object);
	            var img = document.createElement('img');
	            img.alt = '';
	            img.loading = 'eager';
	            img.decoding = 'async';
	            if (crop.cropped) {
	                var frame = document.createElement('span');
	                frame.className = 'aa-fabric-gif-crop-frame';
	                frame.style.left = (center.x / canvasWidth * 100) + '%';
	                frame.style.top = (center.y / canvasHeight * 100) + '%';
	                frame.style.width = (width / canvasWidth * 100) + '%';
	                frame.style.height = (height / canvasHeight * 100) + '%';
	                frame.style.zIndex = String(Math.max(1, allObjects.indexOf(object) + 1));
	                frame.style.opacity = String(animatedGifObjectOpacity(object));
	                frame.style.borderRadius = radius ? (radius / Math.max(width, height) * 100) + '%' : '0';
	                frame.style.transform = 'translate(-50%, -50%) rotate(' + (Number(object.angle) || 0) + 'deg)' + (object.flipX ? ' scaleX(-1)' : '') + (object.flipY ? ' scaleY(-1)' : '');
	                img.style.left = (-crop.cropX / crop.cropWidth * 100) + '%';
	                img.style.top = (-crop.cropY / crop.cropHeight * 100) + '%';
	                img.style.width = (crop.sourceWidth / crop.cropWidth * 100) + '%';
	                img.style.height = (crop.sourceHeight / crop.cropHeight * 100) + '%';
	                img.addEventListener('load', function() { hideFabricGifObject(object); }, { once: true });
	                img.addEventListener('error', function() { restoreFabricGifObject(object, frame); }, { once: true });
	                img.src = src;
	                frame.appendChild(img);
	                targetLayer.appendChild(frame);
	                return;
	            }
	            img.style.left = (center.x / canvasWidth * 100) + '%';
	            img.style.top = (center.y / canvasHeight * 100) + '%';
	            img.style.width = (width / canvasWidth * 100) + '%';
	            img.style.height = (height / canvasHeight * 100) + '%';
	            img.style.zIndex = String(Math.max(1, allObjects.indexOf(object) + 1));
	            img.style.opacity = String(animatedGifObjectOpacity(object));
	            img.style.borderRadius = radius ? (radius / Math.max(width, height) * 100) + '%' : '0';
	            img.style.transform = 'translate(-50%, -50%) rotate(' + (Number(object.angle) || 0) + 'deg)' + (object.flipX ? ' scaleX(-1)' : '') + (object.flipY ? ' scaleY(-1)' : '');
	            img.addEventListener('load', function() { hideFabricGifObject(object); }, { once: true });
	            img.addEventListener('error', function() { restoreFabricGifObject(object, img); }, { once: true });
	            img.src = src;
	            targetLayer.appendChild(img);
	        });
	    }

	    function isInteractiveObject(object) {
	        return object && ['music-player', 'social-media', 'scroll-next-button', 'countdown-timer', 'photo-gallery', 'youtube-video'].indexOf(String(object.customType || '')) !== -1;
	    }

	    function isGuestbookObject(object) {
	        return object && [
	            'guest-name-input',
	            'guest-attendance-select',
	            'guest-message-textarea',
	            'guest-sticker-picker',
	            'guest-submit-button',
	            'guest-comment-list'
	        ].indexOf(String(object.customType || '')) !== -1;
	    }

	    function countdownColumns(object) {
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

	    function normalizeUrl(url) {
	        url = String(url || '').trim();
        if (!url) return '';
        if (/^(https?:|mailto:|tel:|sms:|whatsapp:)/i.test(url)) return url;
        return 'https://' + url.replace(/^\/+/, '');
    }

    function showToast(message) {
        var toast = document.querySelector('.aa-copy-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'aa-copy-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message || 'Tersalin';
        toast.classList.add('is-visible');
        clearTimeout(toast.__timer);
        toast.__timer = setTimeout(function() { toast.classList.remove('is-visible'); }, 1800);
    }

	    function copyText(value, feedback) {
	        value = String(value || '');
	        if (!value) return;
	        if (navigator.clipboard && navigator.clipboard.writeText) {
	            navigator.clipboard.writeText(value).then(function() { showToast(feedback || 'Tersalin'); }).catch(function() { showToast(value); });
	            return;
	        }
	        showToast(value);
	    }

	    function openGalleryLightbox(url) {
	        url = String(url || '').trim();
	        if (!url) return;
	        var lightbox = document.querySelector('.aa-fabric-lightbox');
	        if (!lightbox) {
	            lightbox = document.createElement('div');
	            lightbox.className = 'aa-fabric-lightbox';
	            lightbox.innerHTML = '<button type="button" aria-label="Tutup preview">⛌</button><img src="" alt="Preview foto">';
	            document.body.appendChild(lightbox);
	            lightbox.addEventListener('click', function(event) {
	                if (event.target === lightbox || event.target.tagName === 'BUTTON') {
	                    lightbox.classList.remove('is-open');
	                }
	            });
	            document.addEventListener('keydown', function(event) {
	                if (event.key === 'Escape') lightbox.classList.remove('is-open');
	            });
	        }
	        var img = lightbox.querySelector('img');
	        if (img) img.src = url;
	        lightbox.classList.add('is-open');
	    }

	    function boxStyle(object, canvas) {
	        var rect = object.getBoundingRect(true, true);
	        var parts = object.getObjects ? object.getObjects() : [];
	        var box = parts.find(function(child) { return child && (child.name === 'interactive-box' || child.type === 'rect'); }) || {};
	        var text = parts.find(function(child) { return child && (child.name === 'interactive-text' || isTextObject(child)); }) || {};
	        var canvasWidth = Math.max(1, canvas.getWidth() || 1080);
	        var canvasHeight = Math.max(1, canvas.getHeight() || 1920);
	        var canvasEl = canvas.lowerCanvasEl || (typeof canvas.getElement === 'function' ? canvas.getElement() : null);
	        var artboardEl = canvasEl && typeof canvasEl.closest === 'function' ? canvasEl.closest('.aa-fabric-artboard') : null;
	        var renderedWidth = artboardEl ? artboardEl.clientWidth : 0;
	        if (!renderedWidth && canvasEl && typeof canvasEl.getBoundingClientRect === 'function') {
	            renderedWidth = canvasEl.getBoundingClientRect().width;
	        }
	        var artboardScale = renderedWidth > 0 ? Math.max(0.05, Math.min(1, renderedWidth / canvasWidth)) : 1;
	        var radius = Math.max(0, Number(object.controlRadius != null ? object.controlRadius : (box.rx || box.ry || 0)));
	        var isCountdown = object.customType === 'countdown-timer';
	        var countdownFontSize = Math.max(8, Number(object.countdownFontSize || text.fontSize || 36));
	        var countdownGap = Math.max(0, Number(object.countdownGap) || 0);
	        return {
	            left: (rect.left / canvasWidth * 100) + '%',
	            top: (rect.top / canvasHeight * 100) + '%',
	            width: (rect.width / canvasWidth * 100) + '%',
	            height: (rect.height / canvasHeight * 100) + '%',
	            radius: radius + 'px',
	            bg: object.controlBackground || box.fill || 'rgba(255,255,255,.86)',
	            color: object.controlTextColor || text.fill || '#0f172a',
	            border: object.customType === 'music-player' ? 'transparent' : (box.stroke || 'transparent'),
	            borderWidth: object.customType === 'music-player' ? '0px' : (Math.max(0, Number(box.strokeWidth || 0)) + 'px'),
	            fontFamily: cleanFontFamily(text.fontFamily || object.fontFamily || 'Inter'),
	            fontSize: isCountdown
	                ? Math.max(8, Math.min(50, countdownFontSize * artboardScale)) + 'px'
	                : Math.max(10, Math.min(24, Number(text.fontSize || object.fontSize || 34) * Math.abs(object.scaleY || 1) * artboardScale)) + 'px',
	            fontWeight: text.fontWeight || object.fontWeight || '800',
	            angle: Number(object.angle) || 0,
	            countdownGap: isCountdown ? Math.max(0, Math.min(10, countdownGap * artboardScale)) + 'px' : countdownGap + 'px',
	            countdownColumns: countdownColumns(object),
	            countdownCardRadius: isCountdown ? Math.max(0, radius * artboardScale) + 'px' : radius + 'px'
	        };
    }

    function applyControlStyle(el, style) {
        el.style.left = style.left;
        el.style.top = style.top;
        el.style.width = style.width;
        el.style.height = style.height;
        el.style.borderRadius = style.radius;
        el.style.fontFamily = style.fontFamily;
	        el.style.fontSize = style.fontSize;
        el.style.fontWeight = style.fontWeight;
        el.style.setProperty('--aa-control-bg', style.bg);
        el.style.setProperty('--aa-control-color', style.color);
	        el.style.setProperty('--aa-control-border-color', style.border);
	        el.style.setProperty('--aa-control-border-width', style.borderWidth);
	        el.style.setProperty('--aa-countdown-gap', style.countdownGap || '8px');
	        el.style.setProperty('--aa-countdown-columns', String(style.countdownColumns || 4));
	        el.style.setProperty('--aa-countdown-card-radius', style.countdownCardRadius || style.radius);
	        el.style.transform = 'rotate(' + style.angle + 'deg)';
	    }

    function setupMusic(control, object) {
        var url = String(object.audioUrl || '').trim();
        var audio = document.createElement('audio');
        var button = document.createElement('button');
        var icon = document.createElement('span');
        var playIcon = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.6 5.42v13.16c0 .66.72 1.06 1.27.7l10.02-6.58a.84.84 0 0 0 0-1.4L9.87 4.72a.83.83 0 0 0-1.27.7Z"/></svg>';
        var pauseIcon = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.25 5.5A1.25 1.25 0 0 1 8.5 4.25h1.75A1.25 1.25 0 0 1 11.5 5.5v13a1.25 1.25 0 0 1-1.25 1.25H8.5a1.25 1.25 0 0 1-1.25-1.25v-13Zm5.25 0a1.25 1.25 0 0 1 1.25-1.25h1.75a1.25 1.25 0 0 1 1.25 1.25v13a1.25 1.25 0 0 1-1.25 1.25h-1.75a1.25 1.25 0 0 1-1.25-1.25v-13Z"/></svg>';
        audio.preload = 'auto';
        audio.loop = object.loopAudio !== false && object.loopAudio !== 'false';
        audio.setAttribute('playsinline', 'playsinline');
        audio.setAttribute('webkit-playsinline', 'webkit-playsinline');
        if (url) audio.src = url;
        button.type = 'button';
        button.className = 'aa-fabric-music-button';
        icon.className = 'aa-fabric-music-icon';
        icon.innerHTML = playIcon;
        button.appendChild(icon);
        function setPlaying(playing) {
            icon.innerHTML = playing ? pauseIcon : playIcon;
            button.setAttribute('aria-label', playing ? 'Jeda musik' : 'Putar musik');
        }
        function play() {
            if (!url) return Promise.reject(new Error('Audio URL kosong.'));
            return audio.play().then(function() { setPlaying(true); });
        }
        button.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            if (!audio.paused) {
                audio.pause();
                setPlaying(false);
                return;
            }
            play().catch(function() { showToast('Musik belum bisa diputar.'); });
        });
        if (object.autoplayAfterInteraction !== false && object.autoplayAfterInteraction !== 'false' && url) {
            var tried = false;
            var autoplay = function() {
                if (tried) return;
                tried = true;
                play().catch(function() { setPlaying(false); });
                document.removeEventListener('click', autoplay);
                document.removeEventListener('touchstart', autoplay);
                window.removeEventListener('adaacara:invitation-opened', autoplay);
            };
            document.addEventListener('click', autoplay, { once: true });
            document.addEventListener('touchstart', autoplay, { once: true });
            window.addEventListener('adaacara:invitation-opened', autoplay, { once: true });
            if (window.AdaAcaraPublicInvitationOpened) setTimeout(autoplay, 150);
        }
        control.append(audio, button);
    }

	    function setupSocial(control, object) {
	        var icons = {
	            instagram: ['Instagram', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.75 2h8.5A5.76 5.76 0 0 1 22 7.75v8.5A5.76 5.76 0 0 1 16.25 22h-8.5A5.76 5.76 0 0 1 2 16.25v-8.5A5.76 5.76 0 0 1 7.75 2Zm0 2A3.75 3.75 0 0 0 4 7.75v8.5A3.75 3.75 0 0 0 7.75 20h8.5A3.75 3.75 0 0 0 20 16.25v-8.5A3.75 3.75 0 0 0 16.25 4h-8.5ZM12 7.25A4.75 4.75 0 1 1 7.25 12 4.75 4.75 0 0 1 12 7.25Zm0 2A2.75 2.75 0 1 0 14.75 12 2.75 2.75 0 0 0 12 9.25Zm5.35-2.35a1.15 1.15 0 1 1-1.15 1.15 1.15 1.15 0 0 1 1.15-1.15Z"/></svg>'],
	            tiktok: ['TikTok', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.4 2c.35 2.55 1.78 4.07 4.25 4.23v3.05a7.62 7.62 0 0 1-4.16-1.21v6.38c0 4.12-2.48 6.72-6.19 6.72a5.97 5.97 0 0 1-5.95-5.96 5.93 5.93 0 0 1 7.12-5.82v3.31a2.74 2.74 0 0 0-1.05-.2 2.78 2.78 0 1 0 2.78 2.78V2h3.2Z"/></svg>'],
	            threads: ['Threads', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.05 2C6.66 2 3.16 5.7 3.16 12.06c0 6.26 3.43 9.94 8.9 9.94 4.87 0 8.18-2.72 8.18-6.62 0-2.76-1.46-4.48-4.18-5.1-.41-2.47-1.96-3.8-4.38-3.8-2.18 0-3.92 1.1-4.74 3.02l2.3 1.03c.5-1.1 1.27-1.66 2.33-1.66 1.12 0 1.83.6 2.05 1.75h-1.99c-3.18 0-5.03 1.5-5.03 3.83 0 2.24 1.78 3.75 4.45 3.75 2.86 0 4.66-1.77 5.08-4.91 1.07.42 1.62 1.15 1.62 2.21 0 2.25-2.21 3.78-5.6 3.78-4.03 0-6.35-2.67-6.35-7.22 0-4.67 2.36-7.34 6.26-7.34 2.47 0 4.27.98 5.47 2.98l2.24-1.34C18.1 3.47 15.5 2 12.05 2Zm1.68 11.01c-.23 1.91-1.14 2.9-2.58 2.9-1.14 0-1.86-.56-1.86-1.44 0-.95.82-1.46 2.35-1.46h2.09Z"/></svg>'],
	            x: ['X', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.42 10.23 21.45 2h-1.67l-6.1 7.13L8.8 2H3.18l7.37 10.78L3.18 22h1.67l6.44-7.57L16.44 22h5.62l-7.64-11.77Zm-2.28 2.68-.75-1.07L5.45 3.26H8l4.79 6.91.75 1.07 6.24 9.02h-2.55l-5.09-7.35Z"/></svg>'],
	            facebook: ['Facebook', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.3 22v-8.15h2.74l.41-3.18H14.3V8.64c0-.92.26-1.55 1.58-1.55h1.69V4.25A22.7 22.7 0 0 0 15.1 4c-2.44 0-4.11 1.49-4.11 4.23v2.44H8.23v3.18h2.76V22h3.31Z"/></svg>'],
	            youtube: ['YouTube', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.58 7.19a2.74 2.74 0 0 0-1.93-1.94C17.95 4.8 12 4.8 12 4.8s-5.95 0-7.65.45a2.74 2.74 0 0 0-1.93 1.94A28.53 28.53 0 0 0 2 12a28.53 28.53 0 0 0 .42 4.81 2.74 2.74 0 0 0 1.93 1.94c1.7.45 7.65.45 7.65.45s5.95 0 7.65-.45a2.74 2.74 0 0 0 1.93-1.94A28.53 28.53 0 0 0 22 12a28.53 28.53 0 0 0-.42-4.81ZM10 15.2V8.8l5.33 3.2L10 15.2Z"/></svg>'],
	            whatsapp: ['WhatsApp', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2a9.88 9.88 0 0 0-8.5 14.9L2.4 22l5.23-1.38A9.9 9.9 0 1 0 12.04 2Zm0 2a7.9 7.9 0 0 1 6.69 12.08 7.88 7.88 0 0 1-9.96 2.7l-.37-.18-3.1.82.83-3.02-.2-.39A7.9 7.9 0 0 1 12.04 4Zm-3.36 3.9c-.18 0-.47.07-.72.34-.25.27-.95.93-.95 2.27s.98 2.64 1.11 2.82c.14.18 1.9 3.04 4.68 4.14 2.31.91 2.78.73 3.28.69.5-.04 1.61-.66 1.84-1.3.23-.64.23-1.19.16-1.3-.07-.12-.25-.18-.53-.32-.27-.14-1.61-.8-1.86-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.16-.43-2.2-1.36-.81-.73-1.36-1.62-1.52-1.9-.16-.27-.02-.42.12-.56.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.84-2.01-.22-.53-.44-.46-.61-.47h-.56Z"/></svg>']
        };
        var box = document.createElement('div');
        var row = document.createElement('div');
        box.className = 'aa-fabric-social-box';
        row.className = 'aa-fabric-social-row';
        if (object.socialTitle) {
            var title = document.createElement('strong');
            title.textContent = object.socialTitle;
            box.appendChild(title);
        }
        Object.keys(object.socialLinks || {}).forEach(function(key) {
            var url = normalizeUrl(object.socialLinks[key]);
            if (!url) return;
            var link = document.createElement('a');
            var iconData = icons[key] || [key, ''];
            link.className = 'aa-fabric-social-link aa-social-' + key;
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.setAttribute('aria-label', iconData[0]);
            link.innerHTML = iconData[1] || '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.06 5.06a3.75 3.75 0 0 1 5.3 5.3l-2.12 2.12-1.42-1.42 2.12-2.12a1.75 1.75 0 0 0-2.47-2.47l-2.12 2.12-1.42-1.42 2.13-2.11Zm-2.12 13.88a3.75 3.75 0 0 1-5.3-5.3l2.12-2.12 1.42 1.42-2.12 2.12a1.75 1.75 0 0 0 2.47 2.47l2.12-2.12 1.42 1.42-2.13 2.11Zm-2.12-5.88 4.24-4.24 1.42 1.42-4.24 4.24-1.42-1.42Z"/></svg>';
            row.appendChild(link);
        });
        if (!row.children.length) {
            var empty = document.createElement('span');
            empty.className = 'aa-fabric-social-empty';
            empty.textContent = 'Social media belum diatur';
            row.appendChild(empty);
        }
        box.appendChild(row);
	        control.appendChild(box);
	    }

	    function sanitizeYoutubeVideoId(value) {
	        var match = String(value || '').match(/[A-Za-z0-9_-]{6,20}/);
	        return match ? match[0] : '';
	    }

	    function extractYoutubeIdFromText(value) {
	        var source = String(value || '').trim();
	        var markers = ['youtu.be/', 'watch?v=', 'embed/', 'shorts/', 'live/'];
	        for (var i = 0; i < markers.length; i += 1) {
	            var index = source.indexOf(markers[i]);
	            if (index !== -1) return sanitizeYoutubeVideoId(source.slice(index + markers[i].length));
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
	                for (var i = 0; i < parts.length - 1; i += 1) {
	                    if (['embed', 'shorts', 'live'].indexOf(parts[i]) !== -1) return sanitizeYoutubeVideoId(parts[i + 1]);
	                }
	            }
	        } catch (error) {
	            return extractYoutubeIdFromText(source);
	        }
	        return extractYoutubeIdFromText(source);
	    }

	    function youtubeEmbedUrl(id, options) {
	        var params = ['controls=1', 'modestbranding=1', 'rel=0', 'playsinline=1', 'iv_load_policy=3'];
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

	    function setupYoutube(control, object) {
	        var id = sanitizeYoutubeVideoId(object.youtubeVideoId) || parseYoutubeVideoId(object.youtubeUrl);
	        if (!id) {
	            var placeholder = document.createElement('div');
	            placeholder.className = 'aa-fabric-youtube-placeholder';
	            placeholder.textContent = 'Video Youtube belum diatur';
	            control.appendChild(placeholder);
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
	        control.appendChild(iframe);
	        if (object.youtubeAutoplayOnView === false || object.youtubeAutoplayOnView === 'false' || !('IntersectionObserver' in window)) return;
	        var started = false;
	        var observer = new IntersectionObserver(function(entries) {
	            entries.forEach(function(entry) {
	                if (started || !entry.isIntersecting) return;
	                started = true;
	                iframe.src = youtubeEmbedUrl(id, {
	                    autoplay: true,
	                    loop: object.youtubeLoop !== false && object.youtubeLoop !== 'false'
	                });
	                observer.unobserve(control);
	                observer.disconnect();
	            });
	        }, { threshold: 0.35 });
	        observer.observe(control);
	    }

	    function setupScrollNext(control, canvasEl, object) {
	        var button = document.createElement('button');
	        button.type = 'button';
	        button.className = 'aa-fabric-scroll-button';
	        button.textContent = object.buttonText || object.label || '↓';
	        button.addEventListener('click', function() {
	            var section = canvasEl.closest('.aa-fabric-page-section');
	            var next = section ? section.nextElementSibling : null;
	            if (next) next.scrollIntoView({ behavior: 'smooth', block: 'start' });
	        });
	        control.appendChild(button);
	    }

	    function setupCountdown(control, object) {
	        var box = document.createElement('div');
	        var labels = ['Hari', 'Jam', 'Menit', 'Detik'];
	        var target = new Date(object.countdownTarget || ((object.countdownDate || '') + 'T' + (object.countdownTime || '00:00') + ':00')).getTime();
	        if (!Number.isFinite(target)) target = Date.now();
	        box.className = 'aa-fabric-countdown';
	        labels.forEach(function(label) {
	            var item = document.createElement('span');
	            item.innerHTML = '<strong>00</strong><small>' + label + '</small>';
	            box.appendChild(item);
	        });
	        function tick() {
	            var diff = Math.max(0, target - Date.now());
	            var values = [
	                Math.floor(diff / 86400000),
	                Math.floor((diff % 86400000) / 3600000),
	                Math.floor((diff % 3600000) / 60000),
	                Math.floor((diff % 60000) / 1000)
	            ];
	            box.querySelectorAll('strong').forEach(function(node, index) {
	                node.textContent = String(values[index] || 0).padStart(2, '0');
	            });
	        }
	        tick();
	        window.setInterval(tick, 1000);
	        control.appendChild(box);
	    }

	    function setupGallery(control, object) {
	        var items = Array.isArray(object.galleryItems) && object.galleryItems.length
	            ? object.galleryItems
	            : (Array.isArray(object.galleryImages) ? object.galleryImages : []).map(function(src) { return { src: src }; });
	        var gallery = document.createElement('div');
	        var columns = Math.max(1, Math.min(6, Number(object.galleryColumns) || 2));
	        var gap = Math.max(0, Number(object.galleryGap) || 0);
	        var itemRadius = Math.max(0, Number(object.galleryRadius) || 0) * Math.max(Math.abs(object.scaleX || 1), Math.abs(object.scaleY || 1));
	        gallery.className = 'aa-fabric-gallery';
	        gallery.style.gridTemplateColumns = 'repeat(' + columns + ', minmax(0, 1fr))';
	        gallery.style.gap = gap + 'px';
	        items.filter(function(item) { return item && item.src; }).forEach(function(item) {
	            var button = document.createElement('button');
	            var img = document.createElement('img');
	            button.type = 'button';
	            button.style.borderRadius = itemRadius + 'px';
	            if (item.aspectRatio) button.style.aspectRatio = String(item.aspectRatio);
	            img.src = item.src;
	            img.alt = item.name || 'Gallery';
	            img.loading = 'lazy';
	            img.decoding = 'async';
	            button.appendChild(img);
	            button.addEventListener('click', function() { openGalleryLightbox(item.src); });
	            gallery.appendChild(button);
	        });
	        control.appendChild(gallery);
	    }

	    function guestbookParts(object) {
	        var children = object && object.getObjects ? object.getObjects() : [];
	        var box = null;
	        var text = null;
	        children.forEach(function(child) {
	            if (!box && (child.name === 'guestbook-box' || child.type === 'rect')) box = child;
	            if (!text && (child.name === 'guestbook-text' || isTextObject(child))) text = child;
	        });
	        return { box: box, text: text };
	    }

	    function guestbookEndpoint() {
	        if (window.AdaAcaraGuestbookEndpoint) return window.AdaAcaraGuestbookEndpoint;
	        if (fabricData && fabricData.guestbookEndpoint) return fabricData.guestbookEndpoint;
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

	    function commentCard(comment) {
	        var card = document.createElement('article');
	        var name = document.createElement('strong');
	        var meta = document.createElement('div');
	        var body = document.createElement('div');
	        var message = document.createElement('p');
	        card.className = 'aa-fabric-comment-card';
	        name.textContent = comment.guest_name || '';
	        meta.style.cssText = 'font-size:12px;opacity:.72;margin:3px 0 8px;';
	        meta.textContent = comment.attendance === 'tidak_hadir' ? 'Tidak hadir' : comment.attendance === 'hadir' ? 'Hadir' : 'Ragu';
	        body.style.cssText = 'display:grid;gap:8px;white-space:pre-wrap;';
	        if (comment.sticker_url || comment.sticker) {
	            var img = document.createElement('img');
	            img.src = comment.sticker_url || stickerUrl(comment.sticker);
	            img.alt = 'Sticker';
	            img.loading = 'lazy';
	            img.style.cssText = 'width:48px;height:48px;object-fit:contain;';
	            body.appendChild(img);
	        }
	        message.textContent = comment.message || '';
	        message.style.margin = '0';
	        body.appendChild(message);
	        card.append(name, meta, body);
	        return card;
	    }

	    function populateCommentLists(layer) {
	        var comments = Array.isArray(window.AdaAcaraGuestbookEntries) ? window.AdaAcaraGuestbookEntries : [];
	        layer.querySelectorAll('[data-aa-comment-list]').forEach(function(list) {
	            list.innerHTML = '';
	            if (!comments.length) {
	                var empty = document.createElement('div');
	                empty.className = 'aa-fabric-comment-empty';
	                empty.textContent = 'Belum ada ucapan. Jadilah yang pertama mengisi guestbook.';
	                list.appendChild(empty);
	                return;
	            }
	            comments.forEach(function(comment) {
	                list.appendChild(commentCard(comment));
	            });
	        });
	    }

	    function setupStickerPicker(wrapper, hiddenInput, preview) {
	        var popover = document.createElement('div');
	        var prevButton = document.createElement('button');
	        var track = document.createElement('div');
	        var nextButton = document.createElement('button');
	        popover.className = 'aa-fabric-sticker-popover';
	        prevButton.type = 'button';
	        prevButton.className = 'aa-fabric-sticker-nav';
	        prevButton.setAttribute('aria-label', 'Stiker sebelumnya');
	        prevButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';
	        track.className = 'aa-fabric-sticker-track';
	        nextButton.type = 'button';
	        nextButton.className = 'aa-fabric-sticker-nav';
	        nextButton.setAttribute('aria-label', 'Stiker berikutnya');
	        nextButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';
	        function scrollStickerTrack(direction) {
	            track.scrollBy({ left: direction * Math.max(86, Math.round(track.clientWidth * .75)), behavior: 'smooth' });
	        }
	        prevButton.addEventListener('click', function(event) {
	            event.preventDefault();
	            event.stopPropagation();
	            scrollStickerTrack(-1);
	        });
	        nextButton.addEventListener('click', function(event) {
	            event.preventDefault();
	            event.stopPropagation();
	            scrollStickerTrack(1);
	        });
	        function setSelected(file, src) {
	            hiddenInput.value = file || '';
	            track.querySelectorAll('[data-sticker]').forEach(function(button) {
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
	            var img = document.createElement('img');
	            choice.type = 'button';
	            choice.className = 'aa-fabric-sticker-choice';
	            choice.dataset.sticker = file;
	            img.src = stickerUrl(file);
	            img.alt = 'Sticker';
	            choice.appendChild(img);
	            choice.addEventListener('click', function(event) {
	                var selected = event.currentTarget.querySelector('img');
	                var selectedFile = event.currentTarget.dataset.sticker || '';
	                setSelected(selectedFile, selected ? selected.src : '');
	                popover.classList.remove('is-open');
	            });
	            track.appendChild(choice);
	        }
	        popover.append(prevButton, track, nextButton);
	        popover.__aaSetSelected = setSelected;
	        wrapper.appendChild(popover);
	        return popover;
	    }

	    function guestbookControlStyle(object, canvas) {
	        var parts = guestbookParts(object);
	        var rect = object.getBoundingRect(true, true);
	        var box = parts.box || {};
	        var text = parts.text || {};
	        var scaleX = Math.abs(object.scaleX || 1);
	        var scaleY = Math.abs(object.scaleY || 1);
	        var canvasWidth = Math.max(1, canvas.getWidth() || 1080);
	        var isCenteredButton = object.customType === 'guest-submit-button';
	        var fontSize = Math.max(6, Number(text.fontSize || 32) * scaleY);
	        var paddingX = isCenteredButton ? 0 : Math.max(0, (Number(text.left || 0) - Number(box.left || 0)) * scaleX);
	        var paddingY = isCenteredButton ? 0 : Math.max(0, (Number(text.top || 0) - Number(box.top || 0)) * scaleY);
	        var radius = Math.max(0, Number(box.rx || box.ry || 0) * Math.max(scaleX, scaleY));
	        var borderWidth = Math.max(0, Number(box.strokeWidth || 1) * Math.max(scaleX, scaleY));
	        if (!paddingX) paddingX = isCenteredButton ? 0 : 26 * scaleX;
	        if (!paddingY) paddingY = isCenteredButton ? 0 : 18 * scaleY;
	        return {
	            left: (rect.left / canvas.getWidth() * 100) + '%',
	            top: (rect.top / canvas.getHeight() * 100) + '%',
	            width: (rect.width / canvas.getWidth() * 100) + '%',
	            height: (rect.height / canvas.getHeight() * 100) + '%',
	            borderRadius: 'clamp(4px, ' + Math.max(0, radius / canvasWidth * 100) + 'vw, ' + Math.max(4, radius) + 'px)',
	            background: box.fill || '#ffffff',
	            borderColor: isCenteredButton ? 'transparent' : (box.stroke || '#cbd5e1'),
	            borderWidth: isCenteredButton ? '0px' : ('clamp(1px, ' + Math.max(0, borderWidth / canvasWidth * 100) + 'vw, ' + Math.max(1, Math.min(8, borderWidth)) + 'px)'),
	            color: text.fill || '#334155',
	            fontFamily: cleanFontFamily(text.fontFamily),
	            fontSize: 'clamp(10px, ' + Math.max(2, fontSize / canvasWidth * 100) + 'vw, 18px)',
	            fontWeight: text.fontWeight || 'normal',
	            textAlign: text.textAlign || 'left',
	            lineHeight: Number(text.lineHeight || 1.14),
	            paddingX: isCenteredButton ? '0px' : 'clamp(8px, ' + Math.max(0, paddingX / canvasWidth * 100) + 'vw, 16px)',
	            paddingY: isCenteredButton ? '0px' : 'clamp(5px, ' + Math.max(0, paddingY / canvasWidth * 100) + 'vw, 12px)',
	            angle: Number(object.angle) || 0
	        };
	    }

	    function applyGuestbookControlStyle(el, style) {
	        el.style.left = style.left;
	        el.style.top = style.top;
	        el.style.width = style.width;
	        el.style.height = style.height;
	        el.style.borderRadius = style.borderRadius;
	        el.style.background = style.background;
	        el.style.color = style.color;
	        el.style.fontFamily = style.fontFamily;
	        el.style.fontSize = style.fontSize;
	        el.style.fontWeight = style.fontWeight;
	        el.style.textAlign = style.textAlign;
	        el.style.setProperty('--aa-field-line-height', style.lineHeight);
	        el.style.setProperty('--aa-field-padding-x', style.paddingX);
	        el.style.setProperty('--aa-field-padding-y', style.paddingY);
	        el.style.setProperty('--aa-field-border-color', style.borderColor);
	        el.style.setProperty('--aa-field-border-width', style.borderWidth);
	        el.style.transform = style.angle ? 'rotate(' + style.angle + 'deg)' : 'rotate(0deg)';
	        el.style.transformOrigin = 'center center';
	    }

	    function setupGuestbookOverlay(canvasEl, canvas) {
	        var artboard = canvasEl.closest('.aa-fabric-artboard');
	        if (!artboard || !canvas || !canvas.getObjects) return;
	        var oldLayer = artboard.querySelector('.aa-fabric-guestbook-layer');
	        if (oldLayer) oldLayer.remove();
	        var guestObjects = canvas.getObjects().filter(isGuestbookObject);
	        if (!guestObjects.length) return;
	        var layer = document.createElement('form');
	        var stickerInput = document.createElement('input');
	        layer.className = 'aa-fabric-guestbook-layer';
	        layer.action = guestbookEndpoint() || '#';
	        layer.method = 'post';
	        layer.noValidate = true;
	        stickerInput.type = 'hidden';
	        stickerInput.name = 'sticker';
	        layer.appendChild(stickerInput);
	        guestObjects.forEach(function(object) {
	            object.visible = false;
	            object.evented = false;
	            object.selectable = false;
	            var control = document.createElement('div');
	            var placeholder = object.placeholder || object.label || '';
	            control.className = 'aa-fabric-guestbook-control';
	            control.dataset.guestbookRole = object.customType || '';
	            applyGuestbookControlStyle(control, guestbookControlStyle(object, canvas));
	            if (object.customType === 'guest-name-input') {
	                var input = document.createElement('input');
	                input.name = object.fieldName || 'guest_name';
	                input.placeholder = placeholder || 'Nama';
	                input.maxLength = Number(object.maxLength) || 120;
	                input.required = object.required !== false;
	                control.appendChild(input);
	            } else if (object.customType === 'guest-attendance-select') {
	                var select = document.createElement('select');
	                var empty = document.createElement('option');
	                select.name = object.fieldName || 'attendance';
	                select.required = object.required !== false;
	                empty.value = '';
	                empty.textContent = placeholder || 'Pilih Kehadiran';
	                select.appendChild(empty);
	                (Array.isArray(object.options) && object.options.length ? object.options : ['hadir:Hadir', 'tidak_hadir:Tidak hadir', 'ragu:Ragu']).forEach(function(item) {
	                    var parts = String(item).split(':');
	                    var option = document.createElement('option');
	                    option.value = parts[0] || item;
	                    option.textContent = parts[1] || item;
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
	                var selectedPreview = document.createElement('span');
	                var clearSticker = document.createElement('button');
	                stickerButton.type = 'button';
	                stickerButton.textContent = placeholder || 'Stiker';
	                selectedPreview.className = 'aa-fabric-selected-sticker';
	                selectedPreview.innerHTML = '<img src="" alt="Stiker terpilih"><span>Stiker dipilih</span>';
	                clearSticker.type = 'button';
	                clearSticker.textContent = 'X';
	                selectedPreview.appendChild(clearSticker);
	                control.appendChild(selectedPreview);
	                var popover = setupStickerPicker(control, stickerInput, selectedPreview);
	                stickerButton.addEventListener('click', function() { popover.classList.toggle('is-open'); });
	                clearSticker.addEventListener('click', function() {
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
	        layer.addEventListener('submit', function(event) {
	            event.preventDefault();
	            if (!layer.action || layer.action === '#') {
	                showToast('Form aktif setelah halaman dipublish.');
	                return;
	            }
	            var formData = new FormData(layer);
	            if (!formData.get('attendance')) formData.set('attendance', 'ragu');
	            if (!String(formData.get('guest_name') || '').trim()) {
	                showToast('Nama wajib diisi.');
	                return;
	            }
	            if (!String(formData.get('message') || '').trim()) {
	                showToast('Ucapan wajib diisi.');
	                return;
	            }
	            addGuestbookCsrf(formData);
	            var submit = layer.querySelector('button[type="submit"]');
	            var originalText = submit ? submit.textContent : '';
	            if (submit) {
	                submit.disabled = true;
	                submit.textContent = 'Mengirim...';
	            }
	            fetch(layer.action, {
	                method: 'POST',
	                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
	                body: formData
	            }).then(function(response) {
	                return response.json().then(function(data) {
	                    if (!response.ok || data.success === false) throw new Error(data.message || 'Ucapan gagal dikirim.');
	                    return data;
	                });
	            }).then(function(data) {
	                updateGuestbookCsrf(data.csrf_hash);
	                window.AdaAcaraGuestbookEntries = window.AdaAcaraGuestbookEntries || [];
	                window.AdaAcaraGuestbookEntries.unshift(data.comment || {});
	                layer.reset();
	                stickerInput.value = '';
	                layer.querySelectorAll('.aa-fabric-selected-sticker').forEach(function(preview) { preview.classList.remove('is-visible'); });
	                layer.querySelectorAll('.aa-fabric-sticker-choice').forEach(function(button) { button.classList.remove('is-selected'); });
	                populateCommentLists(layer);
	                showToast(data.message || 'Ucapan berhasil dikirim.');
	            }).catch(function(error) {
	                showToast(error.message || 'Ucapan gagal dikirim.');
	            }).finally(function() {
	                if (submit) {
	                    submit.disabled = false;
	                    submit.textContent = originalText;
	                }
	            });
	        });
	        artboard.appendChild(layer);
	        populateCommentLists(layer);
	        if (typeof canvas.requestRenderAll === 'function') canvas.requestRenderAll();
	    }

	    function objectSnapshot(object) {
	        if (object.__aaAnimationOriginal) {
	            return {
	                left: object.__aaAnimationOriginal.left,
	                top: object.__aaAnimationOriginal.top,
	                originX: object.__aaAnimationOriginal.originX || object.originX || 'left',
	                originY: object.__aaAnimationOriginal.originY || object.originY || 'top',
	                opacity: object.__aaAnimationOriginal.opacity == null ? 1 : object.__aaAnimationOriginal.opacity,
	                scaleX: object.__aaAnimationOriginal.scaleX || 1,
	                scaleY: object.__aaAnimationOriginal.scaleY || 1,
	                angle: object.__aaAnimationOriginal.angle || 0
	            };
	        }
	        return {
	            left: object.left,
	            top: object.top,
	            originX: object.originX || 'left',
	            originY: object.originY || 'top',
	            opacity: object.opacity == null ? 1 : object.opacity,
	            scaleX: object.scaleX || 1,
	            scaleY: object.scaleY || 1,
	            angle: object.angle || 0
	        };
	    }

	    function animationDuration(object, fallback) {
	        var value = Number(object && (object.aaAnimationDuration != null ? object.aaAnimationDuration : object.animationDuration));
	        return isFinite(value) && value > 0 ? value : fallback;
	    }

	    function isAnimationObject(object) {
	        if (!object || object.visible === false || object.__aaSkipObject === true) return false;
	        if (object.customType === 'background' || object.excludeFromAnimation === true) return false;
	        if (isInteractiveObject(object) || isGuestbookObject(object) || isAnimatedGifObject(object)) return false;
	        return hasObjectAnimation(object);
	    }

	    function animationObjects(canvas) {
	        return (canvas && canvas.getObjects ? canvas.getObjects() : []).filter(isAnimationObject).sort(function(a, b) {
	            var rectA = a.getBoundingRect(true, true);
	            var rectB = b.getBoundingRect(true, true);
	            if (Math.abs(rectA.top - rectB.top) > 12) return rectA.top - rectB.top;
	            return rectA.left - rectB.left;
	        });
	    }

	    function prepareObjectAnimations(canvas) {
	        canvas.__aaHasObjectAnimations = false;
	        animationObjects(canvas).forEach(function(object) {
	            canvas.__aaHasObjectAnimations = true;
	            if (!object.__aaAnimationOriginal) object.__aaAnimationOriginal = objectSnapshot(object);
	            object.set({ opacity: 0 });
	            object.dirty = true;
	        });
	        if (typeof canvas.requestRenderAll === 'function') canvas.requestRenderAll();
	    }

	    function runSingleObjectAnimation(canvas, object, animationName) {
	        var original = objectSnapshot(object);
	        var render = function() { canvas.requestRenderAll(); };
	        var finish = function() {
	            object.set(original);
	            canvas.requestRenderAll();
	        };
	        function animate(prop, value, duration, easing, onComplete) {
	            object.animate(prop, value, {
	                duration: animationDuration(object, duration),
	                easing: easing || fabric.util.ease.easeOutCubic,
	                onChange: render,
	                onComplete: onComplete
	            });
	        }
	        if (animationName === 'fade-in') {
	            object.set({ opacity: 0 });
	            animate('opacity', original.opacity, 650, fabric.util.ease.easeOutCubic, finish);
	            return;
	        }
	        if (animationName === 'rise' || animationName === 'fade-up' || animationName === 'fade-down' || animationName === 'fade-left' || animationName === 'fade-right') {
	            var from = { opacity: 0, top: original.top, left: original.left };
	            var offset = animationName === 'rise' ? 70 : 86;
	            if (animationName === 'rise' || animationName === 'fade-up') from.top = original.top + offset;
	            if (animationName === 'fade-down') from.top = original.top - offset;
	            if (animationName === 'fade-left') from.left = original.left + offset;
	            if (animationName === 'fade-right') from.left = original.left - offset;
	            object.set(from);
	            animate('left', original.left, 720);
	            animate('top', original.top, 720);
	            animate('opacity', original.opacity, 650, fabric.util.ease.easeOutCubic, finish);
	            return;
	        }
	        if (animationName === 'slide-up' || animationName === 'slide-down' || animationName === 'slide-left' || animationName === 'slide-right') {
	            var slideFrom = { top: original.top, left: original.left, opacity: original.opacity };
	            var slideOffset = 130;
	            if (animationName === 'slide-up') slideFrom.top = original.top + slideOffset;
	            if (animationName === 'slide-down') slideFrom.top = original.top - slideOffset;
	            if (animationName === 'slide-left') slideFrom.left = original.left + slideOffset;
	            if (animationName === 'slide-right') slideFrom.left = original.left - slideOffset;
	            object.set(slideFrom);
	            animate('left', original.left, 760, fabric.util.ease.easeOutBack);
	            animate('top', original.top, 760, fabric.util.ease.easeOutBack, finish);
	            return;
	        }
	        if (animationName === 'zoom-in' || animationName === 'zoom-out' || animationName === 'flip-in') {
	            var startScale = animationName === 'zoom-out' ? 1.36 : (animationName === 'flip-in' ? .08 : .72);
	            object.set({ scaleX: Math.max(.01, original.scaleX * startScale), scaleY: animationName === 'flip-in' ? original.scaleY : Math.max(.01, original.scaleY * startScale), opacity: 0 });
	            animate('scaleX', original.scaleX, 700, fabric.util.ease.easeOutBack);
	            animate('scaleY', original.scaleY, 700, fabric.util.ease.easeOutBack);
	            animate('opacity', original.opacity, 540, fabric.util.ease.easeOutCubic, finish);
	            return;
	        }
	        if (animationName === 'bounce') {
	            object.set({ top: original.top - 50, opacity: original.opacity });
	            animate('top', original.top, 780, fabric.util.ease.easeOutBounce, finish);
	            return;
	        }
	        if (animationName === 'pulse') {
	            object.set({ opacity: original.opacity, scaleX: original.scaleX, scaleY: original.scaleY });
	            animate('scaleX', original.scaleX * 1.14, 270, fabric.util.ease.easeOutCubic, function() { animate('scaleX', original.scaleX, 300); });
	            animate('scaleY', original.scaleY * 1.14, 270, fabric.util.ease.easeOutCubic, function() { animate('scaleY', original.scaleY, 300, fabric.util.ease.easeOutCubic, finish); });
	            return;
	        }
	        if (animationName === 'swing') {
	            object.set({ angle: original.angle - 10, opacity: original.opacity });
	            animate('angle', original.angle + 10, 290, fabric.util.ease.easeInOutSine, function() { animate('angle', original.angle, 310, fabric.util.ease.easeInOutSine, finish); });
	            return;
	        }
	        if (animationName === 'spin') {
	            object.set({ opacity: original.opacity, angle: original.angle });
	            animate('angle', original.angle + 360, 900, fabric.util.ease.easeInOutCubic, finish);
	            return;
	        }
	        if (animationName === 'float-loop' || animationName === 'sway-loop' || animationName === 'pulse-loop' || animationName === 'heartbeat-loop' || animationName === 'drift-loop' || animationName === 'spin-loop') {
	            object.set({ opacity: original.opacity, left: original.left, top: original.top, angle: original.angle, scaleX: original.scaleX, scaleY: original.scaleY });
	            if (animationName === 'float-loop') {
	                (function loop() {
	                    animate('top', original.top - 34, 1300, fabric.util.ease.easeInOutSine, function() { animate('top', original.top + 18, 1300, fabric.util.ease.easeInOutSine, loop); });
	                })();
	            } else if (animationName === 'sway-loop') {
	                (function loop() {
	                    animate('angle', original.angle + 8, 950, fabric.util.ease.easeInOutSine, function() { animate('angle', original.angle - 8, 950, fabric.util.ease.easeInOutSine, loop); });
	                })();
	            } else if (animationName === 'drift-loop') {
	                (function loop() {
	                    animate('left', original.left + 28, 1200, fabric.util.ease.easeInOutSine, function() { animate('left', original.left - 18, 1200, fabric.util.ease.easeInOutSine, loop); });
	                })();
	            } else if (animationName === 'spin-loop') {
	                (function loop() {
	                    animate('angle', original.angle + 360, 4200, fabric.util.ease.easeInOutSine, function() { object.set({ angle: original.angle }); loop(); });
	                })();
	            } else {
	                var amount = animationName === 'heartbeat-loop' ? 1.18 : 1.1;
	                var duration = animationName === 'heartbeat-loop' ? 360 : 780;
	                (function loop() {
	                    animate('scaleX', original.scaleX * amount, duration, fabric.util.ease.easeInOutSine, function() { animate('scaleX', original.scaleX, duration); });
	                    animate('scaleY', original.scaleY * amount, duration, fabric.util.ease.easeInOutSine, function() { animate('scaleY', original.scaleY, duration, fabric.util.ease.easeInOutSine, loop); });
	                })();
	            }
	            return;
	        }
	        object.set({ opacity: original.opacity });
	        canvas.requestRenderAll();
	    }

	    function runObjectAnimations(canvas) {
	        if (!canvas || canvas.__aaAnimationsStarted) return;
	        canvas.__aaAnimationsStarted = true;
	        animationObjects(canvas).forEach(function(object, index) {
	            var manualDelay = object.animationDelay != null ? object.animationDelay : object.aaAnimationDelay;
	            var delay = Number(manualDelay);
	            if (object.animationOrderMode !== 'manual' || !isFinite(delay)) delay = index * 120;
	            setTimeout(function() {
	                runSingleObjectAnimation(canvas, object, getObjectAnimationName(object));
	            }, Math.max(0, delay));
	        });
	    }

	    function setupObjectAnimationTrigger(canvasEl, canvas) {
	        if (!canvas || !canvas.__aaHasObjectAnimations) return;
	        var section = canvasEl.closest('.aa-fabric-page-section') || canvasEl;
	        var runWhenOpeningReady = function() {
	            if (window.AdaAcaraRunWhenInvitationOpened) {
	                window.AdaAcaraRunWhenInvitationOpened(function() { runObjectAnimations(canvas); });
	                return;
	            }
	            runObjectAnimations(canvas);
	        };
	        if (!('IntersectionObserver' in window)) {
	            runWhenOpeningReady();
	            return;
	        }
	        var observer = new IntersectionObserver(function(entries) {
	            entries.forEach(function(entry) {
	                if (!entry.isIntersecting) return;
	                runWhenOpeningReady();
	                observer.disconnect();
	            });
	        }, { threshold: 0.28, rootMargin: '0px 0px -8% 0px' });
	        observer.observe(section);
	    }

	    function setupOverlays(canvasEl, canvas) {
	        var artboard = canvasEl.closest('.aa-fabric-artboard');
	        if (!artboard) return;
	        artboard.querySelectorAll('.aa-fabric-click-layer,.aa-fabric-interactive-layer').forEach(function(layer) { layer.remove(); });
	        var clickLayer = document.createElement('div');
	        var interactiveLayer = document.createElement('div');
	        var canvasWidth = Math.max(1, canvas.getWidth() || 1080);
	        var canvasHeight = Math.max(1, canvas.getHeight() || 1920);
	        clickLayer.className = 'aa-fabric-click-layer';
	        interactiveLayer.className = 'aa-fabric-interactive-layer';
        canvas.getObjects().forEach(function(object) {
            if (!object) return;
            if (isInteractiveObject(object)) {
                var control = document.createElement('div');
                control.className = 'aa-fabric-interactive-control';
                applyControlStyle(control, boxStyle(object, canvas));
                object.visible = false;
	                object.dirty = true;
	                if (object.customType === 'music-player') setupMusic(control, object);
	                if (object.customType === 'social-media') setupSocial(control, object);
	                if (object.customType === 'youtube-video') setupYoutube(control, object);
	                if (object.customType === 'scroll-next-button') setupScrollNext(control, canvasEl, object);
	                if (object.customType === 'countdown-timer') setupCountdown(control, object);
	                if (object.customType === 'photo-gallery') setupGallery(control, object);
	                interactiveLayer.appendChild(control);
	            }
	            if (object.customType === 'gallery-photo' || object.isGalleryPhoto === true || object.galleryZoom === true) {
	                var galleryRect = object.getBoundingRect(true, true);
	                var galleryHotspot = document.createElement('button');
	                galleryHotspot.type = 'button';
	                galleryHotspot.className = 'aa-fabric-hotspot';
	                galleryHotspot.style.left = (galleryRect.left / canvasWidth * 100) + '%';
	                galleryHotspot.style.top = (galleryRect.top / canvasHeight * 100) + '%';
	                galleryHotspot.style.width = (galleryRect.width / canvasWidth * 100) + '%';
	                galleryHotspot.style.height = (galleryRect.height / canvasHeight * 100) + '%';
	                galleryHotspot.setAttribute('aria-label', 'Zoom foto gallery');
	                galleryHotspot.addEventListener('click', function() {
	                    openGalleryLightbox(object.galleryImageSrc || object.src || (object._element && object._element.src) || '');
	                });
	                clickLayer.appendChild(galleryHotspot);
	            }
	            if (object.link || object.copyText) {
	                var rect = object.getBoundingRect(true, true);
                var hotspot = object.link ? document.createElement('a') : document.createElement('button');
                hotspot.className = 'aa-fabric-hotspot';
                hotspot.style.left = (rect.left / canvasWidth * 100) + '%';
                hotspot.style.top = (rect.top / canvasHeight * 100) + '%';
                hotspot.style.width = (rect.width / canvasWidth * 100) + '%';
                hotspot.style.height = (rect.height / canvasHeight * 100) + '%';
                if (object.link) {
                    hotspot.href = normalizeUrl(object.link);
                    hotspot.target = '_blank';
                    hotspot.rel = 'noopener';
                    hotspot.setAttribute('aria-label', 'Buka link');
                } else {
                    hotspot.type = 'button';
                    hotspot.setAttribute('aria-label', 'Copy text');
                    hotspot.addEventListener('click', function() { copyText(object.copyText, object.copyFeedback || 'Tersalin'); });
                }
                clickLayer.appendChild(hotspot);
            }
        });
        artboard.append(clickLayer, interactiveLayer);
        if (typeof canvas.requestRenderAll === 'function') canvas.requestRenderAll();
    }

    function renderCanvas(canvasEl, pageData, index) {
        if (!canvasEl || !window.fabric || !pageData) return;
        var artboardEl = canvasEl.closest('.aa-fabric-artboard');
        var artboard = pageData.artboard || {};
        var width = Math.max(1, Number(artboard.width) || 1080);
        var height = Math.max(1, Number(artboard.height) || 1920);
        var data = sanitizePage(pageData);
        installRoundedImageRenderer();
        if (artboardEl) artboardEl.classList.add('is-rendering');
        waitFonts(data).then(function() {
            var canvas = new fabric.StaticCanvas(canvasEl, {
                width: width,
                height: height,
                renderOnAddRemove: false,
                enableRetinaScaling: true,
                skipOffscreen: true
            });
            canvasEl.__aaFabricCanvas = canvas;
            canvasEl.__aaFabricOriginalWidth = width;
            canvasEl.__aaFabricOriginalHeight = height;
            canvas.loadFromJSON(data, function() {
                canvas.backgroundColor = data.background || data.backgroundColor || '#ffffff';
	                canvas.getObjects().forEach(function(object) {
	                    object.selectable = false;
	                    object.evented = false;
	                    object.dirty = true;
	                    if (isGuestbookObject(object)) object.visible = false;
	                });
	                prepareObjectAnimations(canvas);
	                function refreshCanvas() {
	                    applyGuestName(canvas);
	                    recalculateText(canvas);
	                    canvas.setDimensions({ width: width, height: height });
                    canvasEl.style.width = '100%';
                    canvasEl.style.height = '100%';
                    if (canvas.wrapperEl) {
                        canvas.wrapperEl.style.width = '100%';
                        canvas.wrapperEl.style.height = '100%';
	                    }
	                    canvas.calcOffset();
	                    if (typeof canvas.requestRenderAll === 'function') canvas.requestRenderAll();
	                }
	                function mountLayers() {
	                    setupAnimatedGifBackground(canvasEl, canvas);
	                    setupAnimatedGifOverlay(canvasEl, canvas);
	                    setupGuestbookOverlay(canvasEl, canvas);
	                    setupOverlays(canvasEl, canvas);
	                    setupObjectAnimationTrigger(canvasEl, canvas);
	                    if (artboardEl) artboardEl.classList.remove('is-rendering');
	                }
	                refreshCanvas();
	                mountLayers();
	                var delays = index === 0 ? [220, 720, 1400] : [420];
	                delays.forEach(function(delay) {
	                    setTimeout(function() {
	                        refreshCanvas();
	                        if (artboardEl) artboardEl.classList.remove('is-rendering');
	                    }, delay);
	                });
	                window.addEventListener('resize', function() {
	                    refreshCanvas();
	                }, { passive: true });
	            });
        }).catch(function() {
            if (artboardEl) artboardEl.classList.remove('is-rendering');
        });
    }

    function lazyRenderPages() {
        var pages = pagesFromData(fabricData);
        if (!pages.length) return;
        var rendered = {};
        function renderAt(index) {
            if (rendered[index]) return;
            rendered[index] = true;
            renderCanvas(document.getElementById('aaFabricPublicCanvas' + index), pages[index], index);
        }
        renderAt(0);
        if (!('IntersectionObserver' in window)) {
            pages.forEach(function(_page, index) {
                if (index > 0) setTimeout(function() { renderAt(index); }, index * 220);
            });
            return;
        }
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) return;
                var section = entry.target;
                var index = Number(section.getAttribute('data-aa-page-index') || 0);
                renderAt(index);
                observer.unobserve(section);
            });
        }, { rootMargin: '420px 0px 420px 0px', threshold: 0.01 });
        document.querySelectorAll('.aa-fabric-page-section').forEach(function(section, index) {
            if (index === 0) return;
            observer.observe(section);
        });
    }

    function renderOpening() {
        var modal = document.getElementById('aaOpeningModal');
        var button = document.getElementById('aaOpeningButton');
        var canvasEl = document.getElementById('aaOpeningFabricCanvas');
        if (!modal || !button || !canvasEl || !openingData || !window.fabric) {
            releaseOpeningCallbacks();
            return;
        }
	        document.body.classList.add('aa-public-no-scroll');
	        var artboard = openingData.artboard || {};
	        var width = Math.max(1, Number(artboard.width) || 1080);
	        var height = Math.max(1, Number(artboard.height) || 1920);
	        installRoundedImageRenderer();
	        waitFonts(openingData).then(function() {
            var canvas = new fabric.StaticCanvas(canvasEl, {
                width: width,
                height: height,
                renderOnAddRemove: false,
                enableRetinaScaling: true
            });
            canvas.loadFromJSON(sanitizePage(openingData), function() {
                canvas.backgroundColor = openingData.background || openingData.backgroundColor || '#0f766e';
                var buttonObject = null;
                canvas.getObjects().forEach(function(object) {
                    object.selectable = false;
                    object.evented = false;
                    if (!buttonObject && object.customType === 'opening-button') buttonObject = object;
                });
	                applyGuestName(canvas);
	                recalculateText(canvas);
	                setupAnimatedGifBackground(canvasEl, canvas);
	                setupAnimatedGifOverlay(canvasEl, canvas);
	                if (buttonObject && typeof buttonObject.getBoundingRect === 'function') {
                    var rect = buttonObject.getBoundingRect(true, true);
                    button.style.left = (rect.left / width * 100) + '%';
                    button.style.top = (rect.top / height * 100) + '%';
                    button.style.width = (rect.width / width * 100) + '%';
                    button.style.height = (rect.height / height * 100) + '%';
                    button.style.borderRadius = Math.max(8, Math.min(rect.width, rect.height) / 2) + 'px';
                } else {
                    button.textContent = 'Buka Undangan';
                    button.className = 'aa-fabric-music-button';
                    button.style.left = '50%';
                    button.style.top = '82%';
                    button.style.width = '54%';
                    button.style.height = '44px';
                    button.style.transform = 'translateX(-50%)';
                }
                canvas.requestRenderAll();
                setTimeout(function() { recalculateText(canvas); canvas.requestRenderAll(); }, 260);
                setTimeout(function() { modal.classList.add('is-visible'); }, 120);
            });
        });
        var closed = false;
        button.addEventListener('click', function() {
            if (closed) return;
            closed = true;
            window.AdaAcaraPublicInvitationOpened = true;
            window.dispatchEvent(new CustomEvent('adaacara:invitation-opened'));
            releaseOpeningCallbacks();
            modal.classList.add('is-leaving');
            modal.classList.remove('is-visible');
            document.body.classList.remove('aa-public-no-scroll');
            setTimeout(function() { modal.remove(); }, 900);
        });
    }

    lazyRenderPages();
    if (hasOpening) {
        renderOpening();
    } else {
        releaseOpeningCallbacks();
    }
})();
</script>
</body>
</html>
