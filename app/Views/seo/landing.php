<?php
helper('seo');

$page = $page ?? [];
$relatedTemplates = array_slice(array_values($relatedTemplates ?? []), 0, 6);
$path = trim((string) ($page['path'] ?? ''), '/');
$canonical = site_url($path);
$title = (string) ($page['title'] ?? 'AdaAcara');
$seoTitle = (string) ($page['seo_title'] ?? $title);
$description = (string) ($page['description'] ?? '');
$faqs = array_values((array) ($page['faqs'] ?? []));
$keywords = array_values((array) ($page['keywords'] ?? []));
$sections = array_values((array) ($page['sections'] ?? []));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->website()
        ->title($seoTitle)
        ->description($description)
        ->canonical($canonical)
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => $title, 'url' => $canonical],
        ])
        ->faq($faqs)
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            margin: 0;
            background:
                radial-gradient(circle at 14% 0%, rgba(251, 191, 36, .24), transparent 28rem),
                radial-gradient(circle at 88% 8%, rgba(15, 118, 110, .12), transparent 28rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 54%, #ffffff 100%);
            color: #0f172a;
        }

        .aa-seo-shell {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .aa-seo-nav {
            position: sticky;
            top: 0;
            z-index: 20;
            border-bottom: 1px solid rgba(143, 101, 223, .18);
            background: rgba(255, 255, 255, .84);
            backdrop-filter: blur(18px);
        }

        .aa-seo-nav-inner {
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .aa-seo-logo img {
            display: block;
            width: 142px;
            height: auto;
        }

        .aa-seo-nav-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .aa-seo-nav-actions {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .aa-seo-nav-links a,
        .aa-seo-btn {
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

        .aa-seo-nav-links a {
            color: #475569;
        }

        .aa-seo-nav-links a:hover {
            background: #fff9f5;
            color: #7550c4;
        }

        .aa-seo-btn-primary {
            border: 1px solid #111827;
            background: linear-gradient(135deg, #4b5563, #030712);
            color: #ffffff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .18);
        }

        .aa-seo-btn-secondary {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
        }

        .aa-seo-hero {
            padding: clamp(54px, 9vw, 92px) 0 40px;
        }

        .aa-seo-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(300px, .95fr);
            gap: 36px;
            align-items: center;
        }

        .aa-seo-eyebrow {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            border: 1px solid rgba(143, 101, 223, .18);
            border-radius: 999px;
            background: #fff9f5;
            color: #7550c4;
            padding: 0 13px;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .aa-seo-hero h1 {
            max-width: 760px;
            margin: 18px 0 0;
            font-size: clamp(38px, 6vw, 64px);
            line-height: 1.04;
            letter-spacing: -.04em;
        }

        .aa-seo-hero p {
            max-width: 680px;
            margin: 20px 0 0;
            color: #475569;
            font-size: clamp(16px, 2vw, 19px);
            font-weight: 650;
            line-height: 1.8;
        }

        .aa-seo-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .aa-seo-visual {
            border: 1px solid rgba(143, 101, 223, .2);
            border-radius: 32px;
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 26px 80px rgba(15, 23, 42, .12);
            padding: 18px;
        }

        .aa-seo-visual-card {
            display: grid;
            min-height: 360px;
            align-content: center;
            gap: 14px;
            border-radius: 24px;
            background:
                linear-gradient(135deg, rgba(15, 118, 110, .08), transparent),
                linear-gradient(180deg, #ffffff, #fff9f5);
            padding: 28px;
            text-align: center;
        }

        .aa-seo-visual-card img {
            width: min(170px, 52%);
            height: auto;
            margin: 0 auto;
            object-fit: contain;
        }

        .aa-seo-visual-card strong {
            font-size: 24px;
            line-height: 1.2;
        }

        .aa-seo-section {
            padding: 36px 0;
        }

        .aa-seo-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .aa-seo-card,
        .aa-seo-template-card,
        .aa-seo-faq-item {
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 24px;
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 18px 48px rgba(15, 23, 42, .06);
        }

        .aa-seo-card {
            padding: 22px;
        }

        .aa-seo-card h2,
        .aa-seo-card h3,
        .aa-seo-template-card h3,
        .aa-seo-faq-item h3 {
            margin: 0;
            letter-spacing: -.02em;
        }

        .aa-seo-card p,
        .aa-seo-template-card p,
        .aa-seo-faq-item p {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 14px;
            font-weight: 650;
            line-height: 1.7;
        }

        .aa-seo-keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 22px;
        }

        .aa-seo-keywords span {
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

        .aa-seo-section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }

        .aa-seo-section-head h2 {
            margin: 0;
            font-size: clamp(28px, 4vw, 42px);
            letter-spacing: -.035em;
        }

        .aa-seo-template-grid,
        .aa-seo-faq {
            display: grid;
            gap: 14px;
        }

        .aa-seo-template-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .aa-seo-template-card {
            overflow: hidden;
        }

        .aa-seo-template-thumb {
            display: block;
            aspect-ratio: 4 / 5;
            background: #f1f5f9;
            overflow: hidden;
        }

        .aa-seo-template-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .aa-seo-template-copy {
            padding: 16px;
        }

        .aa-seo-template-copy a {
            color: #0f172a;
            text-decoration: none;
        }

        .aa-seo-faq {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-seo-faq-item {
            padding: 20px;
        }

        @media (max-width: 900px) {
            .aa-seo-hero-grid,
            .aa-seo-grid,
            .aa-seo-template-grid,
            .aa-seo-faq {
                grid-template-columns: 1fr;
            }

            .aa-seo-section-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .aa-seo-nav-links {
                justify-content: flex-start;
            }
        }

        @media (max-width: 560px) {
            .aa-seo-shell {
                width: min(100% - 24px, 1120px);
            }

            .aa-seo-nav-inner {
                gap: 12px;
            }

            .aa-seo-logo img {
                width: 124px;
            }

            .aa-seo-nav-actions {
                gap: 8px;
            }
        }
    </style>
</head>
<body class="aa-app-ui aa-public-theme-page">
    <header class="aa-seo-nav">
        <div class="aa-seo-shell aa-seo-nav-inner">
            <a class="aa-seo-logo" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                <img class="aa-public-logo" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            </a>
            <div class="aa-seo-nav-actions" aria-label="Navigasi utama">
                <?= view('components/public_theme_toggle') ?>
                <?= view('components/user_nav_dropdown', ['active' => '']) ?>
            </div>
        </div>
    </header>

    <main>
        <section class="aa-seo-hero">
            <div class="aa-seo-shell aa-seo-hero-grid">
                <div>
                    <span class="aa-seo-eyebrow"><?= esc((string) ($page['eyebrow'] ?? 'AdaAcara')) ?></span>
                    <h1><?= esc((string) ($page['hero'] ?? $title)) ?></h1>
                    <p><?= esc((string) ($page['intro'] ?? $description)) ?></p>
                    <div class="aa-seo-actions">
                        <a class="aa-seo-btn aa-seo-btn-primary" href="<?= site_url('templates') ?>">Pilih Template</a>
                        <a class="aa-seo-btn aa-seo-btn-secondary" href="<?= site_url('plans') ?>">Lihat Paket</a>
                    </div>
                    <?php if ($keywords !== []): ?>
                    <div class="aa-seo-keywords" aria-label="Topik terkait">
                        <?php foreach ($keywords as $keyword): ?>
                        <span><?= esc((string) $keyword) ?></span>
                        <?php endforeach ?>
                    </div>
                    <?php endif ?>
                </div>
                <aside class="aa-seo-visual" aria-label="Ringkasan AdaAcara">
                    <div class="aa-seo-visual-card">
                        <img src="<?= aa_asset_url('assets/img/2.png') ?>" alt="" loading="lazy" decoding="async">
                        <strong><?= esc($title) ?></strong>
                        <p><?= esc($description) ?></p>
                    </div>
                </aside>
            </div>
        </section>

        <?php if ($sections !== []): ?>
        <section class="aa-seo-section">
            <div class="aa-seo-shell">
                <div class="aa-seo-grid">
                    <?php foreach ($sections as $section): ?>
                    <article class="aa-seo-card">
                        <h2><?= esc((string) ($section['title'] ?? 'Fitur AdaAcara')) ?></h2>
                        <p><?= esc((string) ($section['body'] ?? '')) ?></p>
                    </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section>
        <?php endif ?>

        <?php if ($relatedTemplates !== []): ?>
        <section class="aa-seo-section">
            <div class="aa-seo-shell">
                <div class="aa-seo-section-head">
                    <h2>Template undangan yang bisa kamu mulai pakai</h2>
                    <a class="aa-seo-btn aa-seo-btn-secondary" href="<?= site_url('templates') ?>">Lihat Semua</a>
                </div>
                <div class="aa-seo-template-grid">
                    <?php foreach ($relatedTemplates as $template): ?>
                    <?php
                        $templateSlug = trim((string) ($template['slug'] ?? ''));
                        $templateUrl = $templateSlug !== '' ? site_url('templates/' . $templateSlug) : site_url('templates');
                        $thumbnail = trim((string) ($template['thumbnail'] ?? ''));
                        if ($thumbnail !== '' && ! preg_match('#^https?://#i', $thumbnail)) {
                            $thumbnail = base_url(ltrim($thumbnail, '/'));
                        }
                    ?>
                    <article class="aa-seo-template-card">
                        <a class="aa-seo-template-thumb" href="<?= esc($templateUrl, 'attr') ?>" aria-label="Lihat <?= esc((string) ($template['name'] ?? 'template'), 'attr') ?>">
                            <?php if ($thumbnail !== ''): ?>
                            <img src="<?= esc($thumbnail, 'attr') ?>" alt="<?= esc((string) ($template['name'] ?? 'Template undangan'), 'attr') ?>" loading="lazy">
                            <?php endif ?>
                        </a>
                        <div class="aa-seo-template-copy">
                            <h3><a href="<?= esc($templateUrl, 'attr') ?>"><?= esc((string) ($template['name'] ?? 'Template AdaAcara')) ?></a></h3>
                            <p><?= esc((string) ($template['description'] ?? 'Template undangan digital siap edit di AdaAcara.')) ?></p>
                        </div>
                    </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section>
        <?php endif ?>

        <?php if ($faqs !== []): ?>
        <section class="aa-seo-section">
            <div class="aa-seo-shell">
                <div class="aa-seo-section-head">
                    <h2>Pertanyaan yang sering ditanyakan</h2>
                </div>
                <div class="aa-seo-faq">
                    <?php foreach ($faqs as $faq): ?>
                    <article class="aa-seo-faq-item">
                        <h3><?= esc((string) ($faq[0] ?? $faq['question'] ?? 'Pertanyaan')) ?></h3>
                        <p><?= esc((string) ($faq[1] ?? $faq['answer'] ?? '')) ?></p>
                    </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section>
        <?php endif ?>
    </main>

    <?= view('components/site_footer') ?>
</body>
</html>
