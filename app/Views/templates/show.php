<?php
helper('seo');

$template = $template ?? [];
$templateName = trim((string) ($template['name'] ?? 'Template AdaAcara'));
$templateSlug = trim((string) ($template['slug'] ?? ''));
$canonical = site_url('templates/' . $templateSlug);
$categoryName = trim((string) ($template['category_name'] ?? 'Template Undangan'));
$description = trim((string) ($template['description'] ?? 'Template undangan digital siap edit di AdaAcara.'));
$thumbnail = trim((string) ($template['thumbnail'] ?? ''));
if ($thumbnail !== '' && ! preg_match('#^https?://#i', $thumbnail)) {
    $thumbnail = base_url(ltrim($thumbnail, '/'));
}
$thumbnail = $thumbnail !== '' ? $thumbnail : 'https://adaacara.com/assets/img/og-default.png';
$isPremiumTemplate = ! empty($isPremiumTemplate);
$canUseTemplate = ! empty($canUseTemplate);
$loginUrl = (string) ($loginUrl ?? site_url('login'));
$plansUrl = (string) ($plansUrl ?? site_url('plans'));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->title('Template ' . $templateName . ' - AdaAcara')
        ->description($description)
        ->canonical($canonical)
        ->image($thumbnail)
        ->type('website')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Templates', 'url' => site_url('templates')],
            ['name' => $templateName, 'url' => $canonical],
        ])
        ->schema([
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $templateName,
            'description' => $description,
            'image' => $thumbnail,
            'url' => $canonical,
            'isAccessibleForFree' => ! $isPremiumTemplate,
            'creator' => [
                '@type' => 'Organization',
                'name' => 'AdaAcara',
                'url' => site_url('/'),
            ],
        ])
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            margin: 0;
            background:
                radial-gradient(circle at 16% 0%, rgba(251, 191, 36, .22), transparent 30rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            color: #0f172a;
        }

        .aa-template-detail-shell {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .aa-template-detail-nav {
            border-bottom: 1px solid rgba(143, 101, 223, .18);
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(18px);
        }

        .aa-template-detail-nav-inner {
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .aa-template-detail-logo img {
            display: block;
            width: 142px;
            height: auto;
        }

        .aa-template-detail-nav-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .aa-template-detail-nav-links a,
        .aa-template-detail-btn {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0 15px;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            transition: .18s ease;
        }

        .aa-template-detail-nav-links a {
            color: #475569;
        }

        .aa-template-detail-nav-links a:hover {
            background: #fff9f5;
            color: #7550c4;
        }

        .aa-template-premium-link {
            border: 1px solid #d9ccf4;
            background: linear-gradient(135deg, #fff9f5 0%, #f1e9ff 100%);
            color: #7550c4 !important;
            box-shadow: 0 10px 24px rgba(91, 67, 118, .10);
        }

        .aa-template-premium-link:hover {
            background: linear-gradient(135deg, #8f65df 0%, #7550c4 100%) !important;
            color: #ffffff !important;
        }

        .aa-template-premium-link svg {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
        }

        .aa-template-detail-btn-primary {
            border: 1px solid #111827;
            background: linear-gradient(135deg, #4b5563, #030712);
            color: #ffffff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .18);
        }

        .aa-template-detail-btn-secondary {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
        }

        .aa-template-detail-hero {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 38px;
            align-items: center;
            padding: clamp(48px, 8vw, 86px) 0;
        }

        .aa-template-detail-preview {
            overflow: hidden;
            border: 1px solid rgba(143, 101, 223, .18);
            border-radius: 32px;
            background: #ffffff;
            box-shadow: 0 26px 80px rgba(15, 23, 42, .12);
        }

        .aa-template-detail-preview img {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 5;
            object-fit: cover;
        }

        .aa-template-detail-eyebrow {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            border-radius: 999px;
            background: #fff9f5;
            color: #7550c4;
            padding: 0 13px;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .aa-template-detail-hero h1 {
            margin: 18px 0 0;
            font-size: clamp(38px, 6vw, 64px);
            line-height: 1.04;
            letter-spacing: -.04em;
        }

        .aa-template-detail-hero p {
            max-width: 680px;
            margin: 20px 0 0;
            color: #475569;
            font-size: 17px;
            font-weight: 650;
            line-height: 1.8;
        }

        .aa-template-detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .aa-template-detail-meta span {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            border-radius: 999px;
            background: #f8fafc;
            color: #475569;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 850;
        }

        .aa-template-detail-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .aa-template-detail-form {
            display: grid;
            gap: 12px;
            margin-top: 28px;
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 24px;
            background: rgba(255, 255, 255, .88);
            padding: 18px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, .06);
        }

        .aa-template-detail-form label {
            display: grid;
            gap: 7px;
            color: #334155;
            font-size: 12px;
            font-weight: 950;
        }

        .aa-template-detail-form input {
            width: 100%;
            min-height: 46px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 0 14px;
            color: #0f172a;
            font: inherit;
            font-size: 14px;
            font-weight: 750;
            outline: none;
        }

        .aa-template-detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            padding-bottom: 64px;
        }

        .aa-template-detail-card {
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 24px;
            background: rgba(255, 255, 255, .88);
            padding: 22px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, .06);
        }

        .aa-template-detail-card h2 {
            margin: 0;
            font-size: 20px;
            letter-spacing: -.02em;
        }

        .aa-template-detail-card p {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 14px;
            font-weight: 650;
            line-height: 1.7;
        }

        @media (max-width: 900px) {
            .aa-template-detail-hero,
            .aa-template-detail-grid {
                grid-template-columns: 1fr;
            }

            .aa-template-detail-nav-inner {
                align-items: flex-start;
                flex-direction: column;
                padding: 18px 0;
            }

            .aa-template-detail-nav-links {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body class="aa-app-ui aa-public-theme-page">
    <header class="aa-template-detail-nav">
        <div class="aa-template-detail-shell aa-template-detail-nav-inner">
            <a class="aa-template-detail-logo" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                <img class="aa-public-logo" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            </a>
            <nav class="aa-template-detail-nav-links" aria-label="Navigasi utama">
                <a href="<?= site_url('templates') ?>">Template</a>
                <a class="aa-template-premium-link" href="<?= site_url('plans') ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 7 4.5 4L12 4l4.5 7L21 7l-2 12H5L3 7Z"/><path d="M5 19h14"/></svg>
                    <span>Go Premium</span>
                </a>
                <a href="<?= site_url('login') ?>">Login</a>
                <?= view('components/public_theme_toggle') ?>
            </nav>
        </div>
    </header>

    <main>
        <section class="aa-template-detail-shell aa-template-detail-hero">
            <div class="aa-template-detail-preview">
                <img src="<?= esc($thumbnail, 'attr') ?>" alt="<?= esc($templateName, 'attr') ?>" loading="eager">
            </div>
            <div>
                <span class="aa-template-detail-eyebrow"><?= esc($categoryName) ?></span>
                <h1><?= esc($templateName) ?></h1>
                <p><?= esc($description) ?></p>
                <div class="aa-template-detail-meta">
                    <span><?= $isPremiumTemplate ? 'Premium' : 'Gratis' ?></span>
                    <span>Template undangan digital</span>
                    <span>Bisa diedit di AdaAcara Design Studio</span>
                </div>

                <?php if ($canUseTemplate): ?>
                <form class="aa-template-detail-form" action="<?= site_url('templates/create') ?>" method="post">
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <input type="hidden" name="template_id" value="<?= esc((string) ($template['id'] ?? ''), 'attr') ?>">
                    <label>Judul Acara
                        <input name="title" type="text" placeholder="Contoh: Wedding Sarah & Dimas" required>
                    </label>
                    <label>Slug URL
                        <input name="slug" type="text" placeholder="contoh: wedding-sarah-dimas">
                    </label>
                    <label>Tanggal Acara
                        <input name="event_date" type="date">
                    </label>
                    <button class="aa-template-detail-btn aa-template-detail-btn-primary" type="submit">Pakai Template Ini</button>
                </form>
                <?php else: ?>
                <div class="aa-template-detail-actions">
                    <a class="aa-template-detail-btn aa-template-detail-btn-primary" href="<?= esc($plansUrl, 'attr') ?>">Aktifkan Paket</a>
                    <a class="aa-template-detail-btn aa-template-detail-btn-secondary" href="<?= esc($loginUrl, 'attr') ?>">Login untuk Pakai Template</a>
                </div>
                <?php endif ?>

                <div class="aa-template-detail-actions">
                    <a class="aa-template-detail-btn aa-template-detail-btn-secondary" href="<?= site_url('templates/preview/' . (int) ($template['id'] ?? 0)) ?>" target="_blank" rel="noopener">Lihat Preview</a>
                    <a class="aa-template-detail-btn aa-template-detail-btn-secondary" href="<?= site_url('templates') ?>">Lihat Template Lain</a>
                </div>
            </div>
        </section>

        <section class="aa-template-detail-shell aa-template-detail-grid">
            <article class="aa-template-detail-card">
                <h2>Siap diedit</h2>
                <p>Ganti teks, foto, warna, font, halaman, musik, dan elemen undangan langsung dari editor visual AdaAcara.</p>
            </article>
            <article class="aa-template-detail-card">
                <h2>Untuk website acara</h2>
                <p>Setelah selesai, desain dapat dipublish menjadi link undangan digital yang mudah dibagikan ke tamu.</p>
            </article>
            <article class="aa-template-detail-card">
                <h2>Fitur interaktif</h2>
                <p>Tambahkan RSVP, guestbook, ucapan tamu, countdown, gift, social media, dan fitur pendukung acara lainnya.</p>
            </article>
        </section>
    </main>

    <?= view('components/site_footer') ?>
</body>
</html>
