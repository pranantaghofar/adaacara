<?php helper('seo'); ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->title((string) ($title ?? 'Legal - AdaAcara'))
        ->description((string) ($description ?? 'Informasi legal AdaAcara.'))
        ->canonical(current_url())
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => (string) ($title ?? 'Legal'), 'url' => current_url()],
        ])
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <style>
        body {
            margin: 0;
            background:
                radial-gradient(circle at 12% 4%, rgba(204, 251, 241, .55), transparent 26rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            color: #0f172a;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .aa-legal-header {
            border-bottom: 1px solid rgba(15, 118, 110, .12);
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .aa-legal-shell {
            width: min(980px, calc(100% - 32px));
            margin: 0 auto;
        }
        .aa-legal-nav {
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }
        .aa-legal-logo img {
            width: 126px;
            height: auto;
            display: block;
        }
        .aa-legal-nav-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }
        .aa-legal-nav-actions {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
        .aa-legal-nav-links a {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            border-radius: 999px;
            padding: 0 14px;
            color: #475569;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
        }
        .aa-legal-nav-links a:hover {
            background: rgba(15, 118, 110, .08);
            color: #0f766e;
        }
        .aa-legal-hero {
            padding: 54px 0 28px;
        }
        .aa-legal-eyebrow {
            display: inline-flex;
            min-height: 30px;
            align-items: center;
            border-radius: 999px;
            background: rgba(15, 118, 110, .1);
            color: #0f766e;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        .aa-legal-hero h1 {
            max-width: 760px;
            margin: 18px 0 12px;
            color: #07151b;
            font-size: clamp(34px, 5vw, 58px);
            line-height: 1.04;
            letter-spacing: 0;
        }
        .aa-legal-hero p {
            max-width: 760px;
            margin: 0;
            color: #475569;
            font-size: 17px;
            font-weight: 650;
            line-height: 1.7;
        }
        .aa-legal-updated {
            margin-top: 18px;
            color: #64748b;
            font-size: 13px;
            font-weight: 800;
        }
        .aa-legal-card {
            margin: 8px 0 54px;
            border: 1px solid rgba(15, 118, 110, .12);
            border-radius: 28px;
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .08);
            overflow: hidden;
        }
        .aa-legal-section {
            padding: 28px;
            border-top: 1px solid rgba(15, 118, 110, .1);
        }
        .aa-legal-section:first-child {
            border-top: 0;
        }
        .aa-legal-section h2 {
            margin: 0 0 12px;
            color: #0f172a;
            font-size: 21px;
            line-height: 1.25;
            letter-spacing: 0;
        }
        .aa-legal-section p {
            margin: 10px 0 0;
            color: #475569;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.8;
        }
        .aa-legal-note {
            margin: 0 0 28px;
            border: 1px solid rgba(245, 158, 11, .24);
            border-radius: 22px;
            background: #fff9f5;
            color: #7550c4;
            padding: 16px 18px;
            font-size: 14px;
            font-weight: 750;
            line-height: 1.6;
        }
        @media (max-width: 680px) {
            .aa-legal-nav {
                gap: 12px;
                padding: 14px 0;
            }
            .aa-legal-logo img {
                width: 124px;
            }
            .aa-legal-nav-actions {
                gap: 8px;
            }
            .aa-legal-hero {
                padding-top: 38px;
            }
            .aa-legal-section {
                padding: 22px;
            }
        }
    </style>
</head>
<body class="aa-app-ui aa-public-theme-page">
    <header class="aa-legal-header">
        <div class="aa-legal-shell aa-legal-nav">
            <a class="aa-legal-logo" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                <img class="aa-public-logo" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            </a>
            <div class="aa-legal-nav-actions" aria-label="Navigasi legal">
                <?= view('components/public_theme_toggle') ?>
                <?= view('components/user_nav_dropdown', ['active' => '']) ?>
            </div>
        </div>
    </header>

    <main class="aa-legal-shell">
        <section class="aa-legal-hero">
            <span class="aa-legal-eyebrow"><?= esc($eyebrow ?? 'Legal') ?></span>
            <h1><?= esc($heading ?? 'Legal AdaAcara') ?></h1>
            <p><?= esc($description ?? '') ?></p>
            <div class="aa-legal-updated">Terakhir diperbarui: <?= esc($updatedAt ?? date('d F Y')) ?></div>
        </section>

        <p class="aa-legal-note">Catatan: halaman ini adalah versi informasi awal untuk membantu transparansi layanan AdaAcara dan bukan pengganti nasihat hukum profesional.</p>

        <article class="aa-legal-card">
            <?php foreach (($sections ?? []) as $section): ?>
                <section class="aa-legal-section">
                    <h2><?= esc($section['title'] ?? '') ?></h2>
                    <?php foreach (($section['body'] ?? []) as $paragraph): ?>
                        <p><?= esc($paragraph) ?></p>
                    <?php endforeach ?>
                </section>
            <?php endforeach ?>
        </article>
    </main>

    <?= view('components/site_footer') ?>
</body>
</html>
