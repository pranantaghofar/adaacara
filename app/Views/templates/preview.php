<?php
$previewLoaderBg = '#0f172a';
$previewLoaderText = '#ffffff';
$previewSource = (string) ($previewDocument ?? '');
$previewHeadHtml = '';
$previewBodyHtml = $previewSource;
$previewBodyClass = 'aa-app-ui aa-template-preview-body';
if (preg_match('/<head\b[^>]*>(.*?)<\/head>/is', $previewSource, $previewHeadMatch)) {
    $previewHeadHtml = trim($previewHeadMatch[1]);
}
if (preg_match('/<body\b[^>]*class=(["\'])(.*?)\1/is', $previewSource, $previewBodyClassMatch)) {
    $previewBodyClass = trim($previewBodyClass . ' ' . $previewBodyClassMatch[2]);
}
$previewBodyClass = trim(implode(' ', array_unique(preg_split('/\s+/', $previewBodyClass) ?: [])));
if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $previewSource, $previewBodyMatch)) {
    $previewBodyHtml = trim($previewBodyMatch[1]);
}
if (preg_match('/--aa-page-bg\s*:\s*([^;"\']+)/i', (string) ($previewDocument ?? ''), $previewBgMatch)) {
    $candidateBg = trim($previewBgMatch[1]);
    if (preg_match('/^#[0-9a-f]{3,8}$/i', $candidateBg) || preg_match('/^(rgba?|hsla?)\([0-9.,%\s-]+\)$/i', $candidateBg)) {
        $previewLoaderBg = $candidateBg;
    }
}
if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $previewLoaderBg, $previewHexMatch)) {
    $hex = $previewHexMatch[1];
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $red = hexdec(substr($hex, 0, 2));
    $green = hexdec(substr($hex, 2, 2));
    $blue = hexdec(substr($hex, 4, 2));
    $previewLoaderText = ((($red * 299) + ($green * 587) + ($blue * 114)) / 1000) < 145 ? '#ffffff' : '#0f172a';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview <?= esc($template['name'] ?? 'Template') ?> - AdaAcara</title>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= $previewHeadHtml ?>
    <style>        
        html,
        body {
            margin: 0;
            min-height: 100%;
            background: #f8fafc;
            color: #0f172a;
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .aa-template-preview-page {
            min-height: 100vh;
            padding: 0;
        }

        .aa-template-preview-shell {
            min-height: 100vh;
            background: #ffffff;
        }

        .aa-template-preview-stage {
            min-height: 100vh;
            overflow: visible;
            background: #ffffff;
            padding: 0;
        }

        .aa-template-preview-live {
            min-height: 100vh;
        }

        html.aa-template-preview-mobile {
            scroll-behavior: auto !important;
            overscroll-behavior-y: auto;
        }

        html.aa-template-preview-mobile,
        html.aa-template-preview-mobile body {
            touch-action: pan-y pinch-zoom;
        }

    </style>
</head>
<body class="<?= esc($previewBodyClass, 'attr') ?>">
    <main class="aa-template-preview-page">
        <section class="aa-template-preview-shell">
            <div class="aa-template-preview-stage">
                <div class="aa-template-preview-live" data-template-preview-live style="--aa-preview-bg: <?= esc($previewLoaderBg, 'attr') ?>; --aa-preview-loader-text: <?= esc($previewLoaderText, 'attr') ?>;">
                    <script>
                    window.AdaAcaraTemplatePreview = true;
                    window.AdaAcaraDisablePublicAutoReload = true;
                    (function () {
                        var ua = navigator.userAgent || '';
                        var isMobilePreview = /Android|iPhone|iPad|iPod/i.test(ua);
                        if (!isMobilePreview) return;
                        window.AdaAcaraTemplatePreviewMobile = true;
                        window.AdaAcaraDisableSmoothScroll = true;
                        document.documentElement.classList.add('aa-template-preview-mobile');
                    })();
                    </script>
                    <?= $previewBodyHtml ?>
                </div>
            </div>

        </section>
    </main>
</body>
</html>
