<?php
    if (! function_exists('aa_t')) {
        helper('aa_i18n');
    }

    $currentLang = (string) ($currentLang ?? aa_current_lang());
    $pageTitle = trim((string) ($page['title'] ?? 'AdaAcara'));
    $eventDate = (string) ($page['event_date'] ?? '');
    $eventDateLabel = $eventDate !== '' && strtotime($eventDate) !== false ? date('d • m • Y', strtotime($eventDate)) : '';
    $safeQrUrl = (string) ($qrImageUrl ?? '');
    $safeMemoriesUrl = (string) ($memoriesUrl ?? '');
    $safeDownloadUrl = (string) ($downloadUrl ?? $qrImageUrl ?? '');
    $selectedStyle = (string) ($selectedStyle ?? 'floral-rose');
    $qrStyles = [
        'floral-rose' => ['label' => 'Floral Rose', 'class' => 'is-rose'],
        'luxury-navy' => ['label' => 'Luxury Navy', 'class' => 'is-navy'],
        'botanical-sage' => ['label' => 'Botanical Sage', 'class' => 'is-sage'],
        'blue-blossom' => ['label' => 'Blue Blossom', 'class' => 'is-blue'],
    ];
    if (! isset($qrStyles[$selectedStyle])) {
        $selectedStyle = 'floral-rose';
    }
    $qrStyleUrl = static function (string $url, string $style, bool $print = false): string {
        $separator = str_contains($url, '?') ? '&' : '?';
        $result = $url . $separator . 'style=' . rawurlencode($style);
        if ($print) {
            $result .= '&print=1';
        }

        return $result;
    };
    $qrAlt = aa_t('gm_qr.alt', 'QR Photobooth {title}', ['title' => $pageTitle], $currentLang);
?>
<!doctype html>
<html lang="<?= esc(aa_t('gm_qr.html_lang', 'id', [], $currentLang), 'attr') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= esc($metaTitle ?? aa_t('gm_qr.meta_title', 'QR Photobooth - {title}', ['title' => $pageTitle], $currentLang)) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo2.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/logo2.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            min-height: 100dvh;
            background:
                radial-gradient(circle at 12% 8%, rgba(144, 213, 255, .22), transparent 32%),
                radial-gradient(circle at 92% 10%, rgba(255, 197, 211, .34), transparent 34%),
                linear-gradient(135deg, #eef8ff 0%, #fff8fb 48%, #fff7ed 100%);
            color: #111827;
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
        }
        .aa-gm-qr-page {
            display: grid;
            min-height: 100dvh;
            place-items: center;
            padding: max(20px, env(safe-area-inset-top)) 18px max(20px, env(safe-area-inset-bottom));
        }
        .aa-gm-qr-card {
            width: min(100%, 560px);
            border: 1px solid rgba(148, 163, 184, .24);
            border-radius: 30px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 26px 70px rgba(15, 23, 42, .10), inset 0 1px 0 rgba(255, 255, 255, .8);
            padding: clamp(28px, 5vw, 42px) clamp(22px, 4.8vw, 34px) 34px;
            text-align: center;
        }
        .aa-gm-qr-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 16px;
            border-radius: 999px;
            background: rgba(20, 184, 166, .12);
            color: #08756e;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            padding: 8px 14px;
            text-transform: uppercase;
        }
        .aa-gm-qr-card h1 {
            margin: 0;
            color: #101827;
            font-size: clamp(34px, 7vw, 48px);
            font-weight: 800;
            line-height: .95;
            letter-spacing: -.065em;
        }
        .aa-gm-qr-card p {
            margin: 18px auto 0;
            max-width: 420px;
            color: #66758c;
            font-size: 14px;
            font-weight: 750;
            line-height: 1.7;
        }
        .aa-gm-qr-box {
            display: grid;
            width: min(72vw, 258px);
            aspect-ratio: 1;
            place-items: center;
            margin: 26px auto 14px;
            border: 1px solid rgba(15, 23, 42, .06);
            border-radius: 24px;
            background: #fff;
            padding: 15px;
            box-shadow: 0 16px 48px rgba(15, 23, 42, .10);
        }
        .aa-gm-qr-box img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .aa-gm-qr-link {
            display: block;
            max-width: 100%;
            overflow-wrap: anywhere;
            color: #00796f;
            font-size: 12px;
            font-weight: 800;
        }
        .aa-gm-lang-switcher {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 14px;
            border: 1px solid rgba(15, 118, 110, .14);
            border-radius: 999px;
            background: rgba(255, 255, 255, .74);
            padding: 5px;
        }
        .aa-gm-lang-switcher a {
            display: inline-flex;
            min-width: 34px;
            min-height: 28px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #08756e;
            font-size: 11px;
            font-weight: 900;
            text-decoration: none;
        }
        .aa-gm-lang-switcher a.is-active {
            background: #12887d;
            color: #fff;
            box-shadow: 0 8px 18px rgba(18, 136, 125, .18);
        }
        .aa-gm-qr-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 22px;
        }
        .aa-gm-qr-style-title {
            margin: 24px 0 12px;
            color: #101827;
            font-size: 13px;
            font-weight: 900;
        }
        .aa-gm-qr-style-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        .aa-gm-qr-style-card {
            position: relative;
            display: grid;
            min-height: 260px;
            align-content: space-between;
            gap: 10px;
            border: 2px solid transparent;
            border-radius: 22px;
            cursor: pointer;
            padding: 14px;
            text-align: center;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }
        .aa-gm-qr-style-card:hover,
        .aa-gm-qr-style-card.is-active {
            transform: translateY(-1px);
            border-color: #12887d;
            box-shadow: 0 16px 42px rgba(15, 23, 42, .12);
        }
        .aa-gm-qr-style-card.is-rose {
            background: linear-gradient(180deg, #fff8f4, #fff0ec);
            color: #955052;
        }
        .aa-gm-qr-style-card.is-navy {
            background: linear-gradient(180deg, #061729, #030d1c);
            color: #efbf67;
        }
        .aa-gm-qr-style-card.is-sage {
            background: linear-gradient(180deg, #fffdf6, #eef4ea);
            color: #36503e;
        }
        .aa-gm-qr-style-card.is-blue {
            background: linear-gradient(180deg, #fafdff, #dceef9);
            color: #113458;
        }
        .aa-gm-qr-style-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .aa-gm-qr-style-frame {
            display: grid;
            min-height: 220px;
            align-content: center;
            justify-items: center;
            gap: 8px;
            border: 1px solid currentColor;
            border-radius: 18px;
            padding: 14px 10px;
        }
        .aa-gm-qr-style-badge {
            display: inline-flex;
            min-height: 24px;
            align-items: center;
            border: 1px solid currentColor;
            border-radius: 999px;
            padding: 0 10px;
            font-size: 9px;
            font-weight: 950;
            letter-spacing: .14em;
        }
        .aa-gm-qr-style-heading {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 27px;
            font-weight: 700;
            line-height: .95;
        }
        .aa-gm-qr-style-event {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: inherit;
            font-size: 10px;
            font-weight: 800;
            opacity: .82;
        }
        .aa-gm-qr-style-qr {
            width: 94px;
            border-radius: 13px;
            background: #fff;
            padding: 8px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .12);
        }
        .aa-gm-qr-style-qr img {
            display: block;
            width: 100%;
        }
        .aa-gm-qr-style-scan {
            font-size: 14px;
            font-weight: 950;
        }
        .aa-gm-qr-style-name {
            color: #101827;
            font-size: 12px;
            font-weight: 900;
        }
        .aa-gm-qr-btn {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(15, 118, 110, .18);
            border-radius: 999px;
            background: #12887d;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 850;
            text-decoration: none;
            box-shadow: 0 12px 24px rgba(18, 136, 125, .16);
        }
        .aa-gm-qr-btn.is-soft {
            background: rgba(15, 118, 110, .055);
            color: #08756e;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
        }
        .aa-gm-qr-tip {
            margin-top: 22px;
            border: 1px solid rgba(245, 158, 11, .24);
            border-radius: 18px;
            background: rgba(255, 251, 235, .68);
            color: #9a3f12;
            padding: 14px 18px;
            font-size: 12px;
            font-weight: 850;
            line-height: 1.55;
        }
        .aa-gm-qr-preview[hidden] { display: none; }
        .aa-gm-qr-preview {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 15% 10%, rgba(144, 213, 255, .22), transparent 32%),
                radial-gradient(circle at 92% 12%, rgba(255, 197, 211, .32), transparent 34%),
                rgba(236, 247, 255, .96);
            color: #101827;
            padding: max(22px, env(safe-area-inset-top)) 22px max(22px, env(safe-area-inset-bottom));
        }
        .aa-gm-qr-preview-card {
            display: grid;
            width: min(100%, 475px);
            max-height: min(620px, 88dvh);
            align-content: center;
            justify-items: center;
            gap: 10px;
            overflow: auto;
            border: 1px solid rgba(148, 163, 184, .24);
            border-radius: 28px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 26px 70px rgba(15, 23, 42, .16), inset 0 1px 0 rgba(255, 255, 255, .82);
            padding: clamp(28px, 5vw, 38px) clamp(22px, 4.8vw, 34px) 28px;
            text-align: center;
        }
        .aa-gm-qr-close {
            position: fixed;
            top: max(14px, env(safe-area-inset-top));
            right: 16px;
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border: 0;
            border-radius: 999px;
            background: rgba(15, 23, 42, .10);
            color: #111827;
            cursor: pointer;
            font-size: 22px;
        }
        .aa-gm-qr-preview-card h2,
        .aa-gm-qr-preview-card h3,
        .aa-gm-qr-preview-card p { margin: 0; }
        .aa-gm-qr-preview-card h2 {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(20, 184, 166, .12);
            color: #08756e;
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            padding: 8px 14px;
            text-transform: uppercase;
        }
        .aa-gm-qr-preview-card h3 {
            max-width: 400px;
            color: #101827;
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
            font-size: clamp(34px, 7vw, 42px);
            font-weight: 800;
            letter-spacing: -.08em;
            line-height: .95;
        }
        .aa-gm-qr-preview-date {
            color: #66758c;
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: .04em;
        }
        .aa-gm-qr-preview-box {
            width: min(60vw, 220px);
            border: 1px solid rgba(15, 23, 42, .06);
            border-radius: 20px;
            background: #fff;
            margin-top: 8px;
            padding: 13px;
            box-shadow: 0 14px 42px rgba(15, 23, 42, .10);
        }
        .aa-gm-qr-preview-box img { display: block; width: 100%; }
        .aa-gm-qr-scan {
            color: #101827;
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
            font-size: clamp(26px, 5.4vw, 31px);
            font-weight: 800;
            letter-spacing: -.075em;
            line-height: 1.04;
            margin-top: 6px !important;
        }
        .aa-gm-qr-preview-card > p:not(.aa-gm-qr-preview-date):not(.aa-gm-qr-scan):not(.aa-gm-qr-powered):not(.aa-gm-qr-preview-tip) {
            color: #66758c;
            font-size: 12px;
            font-weight: 850;
            line-height: 1.55;
        }
        .aa-gm-qr-powered {
            margin-top: 2px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
        }
        .aa-gm-qr-preview-tip {
            width: min(100%, 390px);
            border: 1px solid rgba(245, 158, 11, .24);
            border-radius: 15px;
            background: rgba(255, 251, 235, .68);
            color: #9a3f12;
            margin-top: 6px !important;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 850;
            line-height: 1.55;
        }
        @media (max-width: 520px) {
            .aa-gm-qr-actions { grid-template-columns: 1fr; }
            .aa-gm-qr-style-grid { grid-template-columns: 1fr; }
            .aa-gm-qr-card { border-radius: 26px; }
            .aa-gm-qr-card h1 { font-size: clamp(31px, 10vw, 42px); }
            .aa-gm-qr-preview-card h3 { font-size: clamp(31px, 10vw, 40px); }
            .aa-gm-qr-preview-card { max-height: 90dvh; }
        }
    </style>
</head>
<body>
    <main class="aa-gm-qr-page">
        <section class="aa-gm-qr-card">
            <span class="aa-gm-qr-eyebrow"><?= esc(aa_t('gm_qr.eyebrow', 'QR Photobooth', [], $currentLang)) ?></span>
            <h1><?= esc(aa_t('gm_qr.title', 'Sebarkan Photobooth di Lokasi Acara', [], $currentLang)) ?></h1>
            <p><?= esc(aa_t('gm_qr.subtitle', 'Biarkan tamu scan QR untuk langsung membuka Photobooth dari HP mereka.', [], $currentLang)) ?></p>
            <div class="aa-gm-qr-box">
                <img src="<?= esc($safeQrUrl, 'attr') ?>" alt="<?= esc($qrAlt, 'attr') ?>" loading="eager" decoding="async">
            </div>
            <span class="aa-gm-qr-link"><?= esc($safeMemoriesUrl) ?></span>
            <nav class="aa-gm-lang-switcher" aria-label="<?= esc(aa_t('common.language', 'Bahasa', [], $currentLang), 'attr') ?>">
                <?php foreach (aa_supported_langs() as $lang): ?>
                    <a class="<?= $lang === $currentLang ? 'is-active' : '' ?>" href="<?= esc(aa_lang_url(current_url(), $lang), 'attr') ?>" lang="<?= esc($lang, 'attr') ?>"><?= esc(strtoupper($lang)) ?></a>
                <?php endforeach; ?>
            </nav>
            <h2 class="aa-gm-qr-style-title"><?= esc(aa_t('gm_qr.choose_style', 'Pilih tampilan kartu QR', [], $currentLang)) ?></h2>
            <div class="aa-gm-qr-style-grid" data-aa-qr-style-grid>
                <?php foreach ($qrStyles as $styleKey => $style): ?>
                    <label class="aa-gm-qr-style-card <?= esc($style['class'], 'attr') ?> <?= $styleKey === $selectedStyle ? 'is-active' : '' ?>" data-aa-qr-style-card>
                        <input type="radio" name="qr_style" value="<?= esc($styleKey, 'attr') ?>" <?= $styleKey === $selectedStyle ? 'checked' : '' ?>>
                        <span class="aa-gm-qr-style-frame">
                            <span class="aa-gm-qr-style-badge">QR PHOTOBOOTH</span>
                            <span class="aa-gm-qr-style-heading">Digital<br>Photobooth</span>
                            <span class="aa-gm-qr-style-event"><?= esc($pageTitle) ?><?= $eventDateLabel !== '' ? ' · ' . esc($eventDateLabel) : '' ?></span>
                            <span class="aa-gm-qr-style-qr"><img src="<?= esc($safeQrUrl, 'attr') ?>" alt="<?= esc($qrAlt, 'attr') ?>" loading="lazy" decoding="async"></span>
                            <span class="aa-gm-qr-style-scan">Scan & capture</span>
                        </span>
                        <span class="aa-gm-qr-style-name"><?= esc($style['label']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="aa-gm-qr-actions">
                <a class="aa-gm-qr-btn" href="<?= esc($qrStyleUrl($safeDownloadUrl, $selectedStyle), 'attr') ?>" data-aa-qr-download><?= esc(aa_t('gm_qr.download', 'Download QR', [], $currentLang)) ?></a>
                <button class="aa-gm-qr-btn is-soft" type="button" data-aa-qr-print data-print-base="<?= esc($safeDownloadUrl, 'attr') ?>"><?= esc(aa_t('common.print', 'Print', [], $currentLang)) ?></button>
            </div>
            <div class="aa-gm-qr-tip"><?= esc(aa_t('gm_qr.tip', 'Cetak QR ini dan pasang di meja tamu, pintu masuk, photobooth, atau area acara.', [], $currentLang)) ?></div>
        </section>
    </main>

    <section class="aa-gm-qr-preview" data-aa-qr-modal hidden>
        <button class="aa-gm-qr-close" type="button" data-aa-qr-close aria-label="<?= esc(aa_t('common.close', 'Tutup', [], $currentLang), 'attr') ?>">×</button>
        <div class="aa-gm-qr-preview-card">
            <h2><?= esc(aa_t('gm_qr.card_title', 'QR PHOTOBOOTH', [], $currentLang)) ?></h2>
            <h3><?= esc(aa_t('gm_qr.card_subtitle', 'Photobooth Digital', [], $currentLang)) ?></h3>
            <?php if ($eventDateLabel !== ''): ?>
                <p class="aa-gm-qr-preview-date"><?= esc($pageTitle) ?> · <?= esc($eventDateLabel) ?></p>
            <?php else: ?>
                <p class="aa-gm-qr-preview-date"><?= esc($pageTitle) ?></p>
            <?php endif; ?>
            <div class="aa-gm-qr-preview-box">
                <img src="<?= esc($safeQrUrl, 'attr') ?>" alt="<?= esc($qrAlt, 'attr') ?>">
            </div>
            <p class="aa-gm-qr-scan"><?= esc(aa_t('gm_qr.scan', 'Scan & abadikan momen', [], $currentLang)) ?></p>
            <p><?= esc(aa_t('gm_qr.description', 'Abadikan fotomu di acara kami', [], $currentLang)) ?></p>
            <p class="aa-gm-qr-preview-tip"><?= esc(aa_t('gm_qr.instruction', 'Buka kamera HP → Pilih Frame → Foto → Upload → Download', [], $currentLang)) ?></p>
            <p class="aa-gm-qr-powered"><?= esc(aa_t('gm_qr.powered', 'Powered by adaAcara.com', [], $currentLang)) ?></p>
        </div>
    </section>
    <script>
        (function () {
            var cards = document.querySelectorAll('[data-aa-qr-style-card]');
            var download = document.querySelector('[data-aa-qr-download]');
            var print = document.querySelector('[data-aa-qr-print]');
            var downloadBase = download ? download.href.split('&style=')[0].split('?style=')[0] : '';
            var printBase = print ? (print.getAttribute('data-print-base') || '') : '';

            function selectedStyle() {
                var checked = document.querySelector('input[name="qr_style"]:checked');
                return checked ? checked.value : 'floral-rose';
            }

            function styleUrl(base, style, isPrint) {
                if (!base) return '';
                var separator = base.indexOf('?') === -1 ? '?' : '&';
                return base + separator + 'style=' + encodeURIComponent(style) + (isPrint ? '&print=1' : '');
            }

            function refreshActions() {
                var style = selectedStyle();
                cards.forEach(function (card) {
                    var input = card.querySelector('input[name="qr_style"]');
                    card.classList.toggle('is-active', input && input.value === style);
                });
                if (download) {
                    download.href = styleUrl(downloadBase, style, false);
                }
            }

            cards.forEach(function (card) {
                card.addEventListener('click', function () {
                    var input = card.querySelector('input[name="qr_style"]');
                    if (input) {
                        input.checked = true;
                        refreshActions();
                    }
                });
            });

            if (print) {
                print.addEventListener('click', function () {
                    var url = styleUrl(printBase, selectedStyle(), true);
                    if (!url) return;
                    var win = window.open('', '_blank');
                    if (!win) {
                        window.location.href = url;
                        return;
                    }
                    win.document.open();
                    win.document.write('<!doctype html><html><head><title>Print QR Photobooth</title><style>html,body{margin:0;min-height:100%;background:#f8fafc}body{display:grid;place-items:center;padding:0}img{display:block;width:min(100vw,720px);height:auto}@media print{@page{margin:0}html,body{background:#fff}img{width:100%;max-width:none}}</style></head><body><img src="' + url.replace(/"/g, '&quot;') + '" alt="QR Photobooth"></body></html>');
                    win.document.close();
                    var img = win.document.querySelector('img');
                    if (img) {
                        img.addEventListener('load', function () {
                            win.focus();
                            win.print();
                        }, { once: true });
                    }
                });
            }

            refreshActions();
        })();
    </script>
</body>
</html>
