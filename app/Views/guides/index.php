<?php
helper('seo');

$articles = array_values($articles ?? []);
$categories = array_values(array_unique(array_map(static fn (array $article): string => (string) ($article['category'] ?? 'Panduan'), $articles)));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->website()
        ->title('Panduan Undangan Digital - AdaAcara')
        ->description('Panduan praktis AdaAcara tentang cara membuat undangan digital, memilih template, menulis kata-kata undangan, memakai RSVP, Magic Layer, Remove BG, dan tips kreator.')
        ->canonical(site_url('panduan'))
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Panduan', 'url' => site_url('panduan')],
        ])
        ->schema([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Panduan Undangan Digital AdaAcara',
            'itemListElement' => array_map(static function (array $article, int $index): array {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => (string) ($article['title'] ?? 'Panduan AdaAcara'),
                    'url' => site_url('panduan/' . (string) ($article['slug'] ?? '')),
                ];
            }, $articles, array_keys($articles)),
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
                radial-gradient(circle at 14% 0%, rgba(251, 191, 36, .23), transparent 28rem),
                radial-gradient(circle at 88% 10%, rgba(15, 118, 110, .12), transparent 30rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 56%, #ffffff 100%);
            color: #0f172a;
        }

        .aa-guide-shell {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .aa-guide-nav {
            border-bottom: 1px solid rgba(143, 101, 223, .18);
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(18px);
        }

        .aa-guide-nav-inner {
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .aa-guide-logo img {
            display: block;
            width: 142px;
            height: auto;
        }

        .aa-guide-nav-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .aa-guide-nav-actions {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .aa-guide-nav-links a,
        .aa-guide-btn {
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

        .aa-guide-nav-links a {
            color: #475569;
        }

        .aa-guide-nav-links a:hover {
            background: #fff9f5;
            color: #7550c4;
        }

        .aa-guide-hero {
            padding: clamp(54px, 9vw, 88px) 0 30px;
        }

        .aa-guide-eyebrow {
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

        .aa-guide-hero h1 {
            max-width: 850px;
            margin: 18px 0 0;
            font-size: clamp(38px, 6vw, 64px);
            line-height: 1.04;
            letter-spacing: -.04em;
        }

        .aa-guide-hero p {
            max-width: 720px;
            margin: 20px 0 0;
            color: #475569;
            font-size: 18px;
            font-weight: 650;
            line-height: 1.8;
        }

        .aa-guide-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 24px;
        }

        .aa-guide-categories span {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, .86);
            color: #475569;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 850;
            box-shadow: inset 0 0 0 1px #e2e8f0;
        }

        .aa-guide-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            padding: 32px 0 72px;
        }

        .aa-guide-card {
            display: grid;
            min-height: 310px;
            align-content: space-between;
            gap: 18px;
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 28px;
            background: rgba(255, 255, 255, .9);
            padding: 22px;
            color: inherit;
            text-decoration: none;
            box-shadow: 0 18px 48px rgba(15, 23, 42, .06);
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .aa-guide-card:hover {
            border-color: rgba(143, 101, 223, .38);
            box-shadow: 0 24px 60px rgba(15, 23, 42, .10);
            transform: translateY(-3px);
        }

        .aa-guide-card small {
            color: #8f65df;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .aa-guide-card h2 {
            margin: 12px 0 0;
            font-size: 24px;
            line-height: 1.15;
            letter-spacing: -.03em;
        }

        .aa-guide-card p {
            margin: 12px 0 0;
            color: #64748b;
            font-size: 14px;
            font-weight: 650;
            line-height: 1.7;
        }

        .aa-guide-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .aa-guide-card-meta span {
            display: inline-flex;
            min-height: 30px;
            align-items: center;
            border-radius: 999px;
            background: #f8fafc;
            color: #64748b;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 850;
        }

        @media (max-width: 920px) {
            .aa-guide-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 680px) {
            .aa-guide-nav-inner {
                gap: 12px;
                padding: 14px 0;
            }

            .aa-guide-logo img {
                width: 124px;
            }

            .aa-guide-nav-actions {
                gap: 8px;
            }

            .aa-guide-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="aa-app-ui aa-public-theme-page">
    <header class="aa-guide-nav">
        <div class="aa-guide-shell aa-guide-nav-inner">
            <a class="aa-guide-logo" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                <img class="aa-public-logo" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            </a>
            <div class="aa-guide-nav-actions" aria-label="Navigasi utama">
                <?= view('components/public_theme_toggle') ?>
                <?= view('components/user_nav_dropdown', ['active' => '']) ?>
            </div>
        </div>
    </header>

    <main>
        <section class="aa-guide-hero">
            <div class="aa-guide-shell">
                <span class="aa-guide-eyebrow">Panduan AdaAcara</span>
                <h1>Panduan praktis untuk membuat undangan digital yang rapi, jelas, dan siap dibagikan.</h1>
                <p>Kumpulan artikel pendek yang fokus pada hal yang benar-benar dipakai: memilih template, menulis isi undangan, mengatur RSVP, memakai fitur AI, dan mengecek undangan sebelum publish.</p>
                <div class="aa-guide-categories" aria-label="Kategori panduan">
                    <?php foreach ($categories as $category): ?>
                    <span><?= esc($category) ?></span>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <section class="aa-guide-shell aa-guide-grid">
            <?php foreach ($articles as $article): ?>
            <a class="aa-guide-card" href="<?= site_url('panduan/' . (string) ($article['slug'] ?? '')) ?>">
                <span>
                    <small><?= esc((string) ($article['category'] ?? 'Panduan')) ?></small>
                    <h2><?= esc((string) ($article['title'] ?? 'Panduan AdaAcara')) ?></h2>
                    <p><?= esc((string) ($article['description'] ?? 'Panduan praktis AdaAcara.')) ?></p>
                </span>
                <span class="aa-guide-card-meta">
                    <span><?= esc((string) ($article['reading_time'] ?? '4 menit')) ?></span>
                    <span>Diperbarui <?= esc(date('d M Y', strtotime((string) ($article['updated_at'] ?? 'now')))) ?></span>
                </span>
            </a>
            <?php endforeach ?>
        </section>
    </main>

    <?= view('components/site_footer') ?>
</body>
</html>
