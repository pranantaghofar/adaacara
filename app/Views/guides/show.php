<?php
helper('seo');

use App\Libraries\GuideArticleCatalog;
use App\Libraries\SeoLandingPageCatalog;

$article = $article ?? [];
$articles = array_values($articles ?? []);
$slug = (string) ($article['slug'] ?? '');
$title = (string) ($article['title'] ?? 'Panduan AdaAcara');
$description = (string) ($article['description'] ?? 'Panduan praktis AdaAcara.');
$updatedAt = (string) ($article['updated_at'] ?? date('Y-m-d'));
$cta = $article['cta'] ?? ['label' => 'Mulai desain', 'url' => 'templates'];

$toList = static function ($value): array {
    if (is_array($value)) {
        return array_values($value);
    }

    $value = trim((string) $value);
    return $value === '' ? [] : [$value];
};

$intro = $toList($article['intro'] ?? []);
$sections = array_values(array_filter($toList($article['sections'] ?? []), static fn ($section): bool => is_array($section)));
$checklist = $toList($article['checklist'] ?? []);

$resolveRelated = static function (string $item): array {
    $item = trim($item, '/');
    $guide = GuideArticleCatalog::find($item);
    if ($guide !== null) {
        return [
            'label' => (string) ($guide['title'] ?? 'Panduan AdaAcara'),
            'url' => site_url('panduan/' . $item),
            'type' => 'Panduan',
        ];
    }

    $landing = class_exists(SeoLandingPageCatalog::class) && method_exists(SeoLandingPageCatalog::class, 'find')
        ? SeoLandingPageCatalog::find($item)
        : null;
    if ($landing !== null) {
        return [
            'label' => (string) ($landing['title'] ?? 'AdaAcara'),
            'url' => site_url($item),
            'type' => 'Halaman',
        ];
    }

    $fallbacks = [
        'templates' => ['Template AdaAcara', site_url('templates'), 'Template'],
        'creator/apply' => ['Daftar Kreator AdaAcara', site_url('creator/apply'), 'Kreator'],
    ];

    if (isset($fallbacks[$item])) {
        return [
            'label' => $fallbacks[$item][0],
            'url' => $fallbacks[$item][1],
            'type' => $fallbacks[$item][2],
        ];
    }

    return [
        'label' => ucwords(str_replace(['-', '/'], ' ', $item)),
        'url' => site_url($item),
        'type' => 'Link',
    ];
};

$related = array_map($resolveRelated, $toList($article['related'] ?? []));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->type('article')
        ->title((string) ($article['seo_title'] ?? $title))
        ->description($description)
        ->canonical(site_url('panduan/' . $slug))
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Panduan', 'url' => site_url('panduan')],
            ['name' => $title, 'url' => site_url('panduan/' . $slug)],
        ])
        ->schema([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'dateModified' => $updatedAt,
            'datePublished' => $updatedAt,
            'inLanguage' => 'id-ID',
            'author' => [
                '@type' => 'Organization',
                'name' => 'AdaAcara',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'AdaAcara',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://adaacara.com/assets/img/adaacara-logo.png',
                ],
            ],
            'mainEntityOfPage' => site_url('panduan/' . $slug),
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
                radial-gradient(circle at 12% 0%, rgba(251, 191, 36, .20), transparent 28rem),
                radial-gradient(circle at 88% 8%, rgba(15, 118, 110, .12), transparent 30rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 54%, #ffffff 100%);
            color: #0f172a;
        }

        .aa-guide-shell {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
        }

        .aa-guide-nav {
            border-bottom: 1px solid rgba(143, 101, 223, .18);
            background: rgba(255, 255, 255, .88);
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
        .aa-guide-cta,
        .aa-guide-related a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: .18s ease;
        }

        .aa-guide-nav-links a {
            min-height: 40px;
            border-radius: 999px;
            padding: 0 15px;
            color: #475569;
            font-size: 13px;
            font-weight: 900;
        }

        .aa-guide-nav-links a:hover {
            background: #fff9f5;
            color: #7550c4;
        }

        .aa-guide-hero {
            padding: clamp(46px, 8vw, 78px) 0 28px;
        }

        .aa-guide-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #8f65df;
            font-size: 13px;
            font-weight: 950;
            text-decoration: none;
        }

        .aa-guide-hero h1 {
            max-width: 860px;
            margin: 18px 0 0;
            font-size: clamp(34px, 6vw, 60px);
            line-height: 1.05;
            letter-spacing: -.04em;
        }

        .aa-guide-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 20px;
        }

        .aa-guide-meta span {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            border-radius: 999px;
            background: #ffffff;
            color: #64748b;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 850;
            box-shadow: inset 0 0 0 1px #e2e8f0;
        }

        .aa-guide-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 28px;
            align-items: start;
            padding: 24px 0 72px;
        }

        .aa-guide-article {
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 30px;
            background: rgba(255, 255, 255, .92);
            padding: clamp(22px, 5vw, 44px);
            box-shadow: 0 20px 58px rgba(15, 23, 42, .06);
        }

        .aa-guide-article p {
            margin: 0;
            color: #334155;
            font-size: 17px;
            font-weight: 620;
            line-height: 1.9;
        }

        .aa-guide-article p + p {
            margin-top: 16px;
        }

        .aa-guide-article h2 {
            margin: 34px 0 12px;
            color: #0f172a;
            font-size: clamp(24px, 3.5vw, 34px);
            line-height: 1.15;
            letter-spacing: -.03em;
        }

        .aa-guide-checklist {
            display: grid;
            gap: 10px;
            margin: 34px 0 0;
            padding: 0;
            list-style: none;
        }

        .aa-guide-checklist li {
            display: flex;
            gap: 10px;
            border-radius: 18px;
            background: #f8fafc;
            color: #334155;
            padding: 13px 14px;
            font-size: 15px;
            font-weight: 750;
            line-height: 1.5;
        }

        .aa-guide-checklist li::before {
            content: "";
            flex: 0 0 auto;
            width: 9px;
            height: 9px;
            margin-top: 7px;
            border-radius: 999px;
            background: #0f766e;
        }

        .aa-guide-side {
            display: grid;
            gap: 14px;
            position: sticky;
            top: 92px;
        }

        .aa-guide-box {
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 24px;
            background: rgba(255, 255, 255, .94);
            padding: 18px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .05);
        }

        .aa-guide-box h2 {
            margin: 0 0 10px;
            color: #7550c4;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .aa-guide-box p {
            margin: 0;
            color: #475569;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.65;
        }

        .aa-guide-cta {
            width: 100%;
            min-height: 48px;
            margin-top: 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0f766e, #8f65df);
            color: #ffffff;
            font-size: 14px;
            font-weight: 950;
        }

        .aa-guide-related {
            display: grid;
            gap: 9px;
        }

        .aa-guide-related a {
            align-items: flex-start;
            flex-direction: column;
            gap: 4px;
            border-radius: 18px;
            background: #f8fafc;
            color: #0f172a;
            padding: 12px;
            font-size: 14px;
            font-weight: 850;
            line-height: 1.35;
        }

        .aa-guide-related a:hover {
            background: #fff9f5;
            color: #7550c4;
        }

        .aa-guide-related small {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        @media (max-width: 900px) {
            .aa-guide-layout {
                grid-template-columns: 1fr;
            }

            .aa-guide-side {
                position: static;
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
                <a class="aa-guide-back" href="<?= site_url('panduan') ?>">← Semua panduan</a>
                <h1><?= esc($title) ?></h1>
                <div class="aa-guide-meta">
                    <span><?= esc((string) ($article['category'] ?? 'Panduan')) ?></span>
                    <span><?= esc((string) ($article['reading_time'] ?? '4 menit')) ?></span>
                    <span>Diperbarui <?= esc(date('d M Y', strtotime($updatedAt))) ?></span>
                </div>
            </div>
        </section>

        <section class="aa-guide-shell aa-guide-layout">
            <article class="aa-guide-article">
                <?php foreach ($intro as $paragraph): ?>
                <p><?= esc((string) $paragraph) ?></p>
                <?php endforeach ?>

                <?php foreach ($sections as $section): ?>
                <h2><?= esc((string) ($section['heading'] ?? 'Panduan')) ?></h2>
                    <?php foreach ($toList($section['body'] ?? []) as $paragraph): ?>
                <p><?= esc((string) $paragraph) ?></p>
                    <?php endforeach ?>
                <?php endforeach ?>

                <?php if ($checklist !== []): ?>
                <h2>Checklist cepat</h2>
                <ul class="aa-guide-checklist">
                    <?php foreach ($checklist as $item): ?>
                    <li><?= esc((string) $item) ?></li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </article>

            <aside class="aa-guide-side">
                <section class="aa-guide-box">
                    <h2>Mulai dari sini</h2>
                    <p>Kalau sudah punya gambaran, lanjutkan ke template atau editor agar idenya langsung jadi undangan yang bisa dibagikan.</p>
                    <a class="aa-guide-cta" href="<?= site_url((string) ($cta['url'] ?? 'templates')) ?>"><?= esc((string) ($cta['label'] ?? 'Mulai desain')) ?></a>
                </section>

                <?php if ($related !== []): ?>
                <section class="aa-guide-box">
                    <h2>Link terkait</h2>
                    <div class="aa-guide-related">
                        <?php foreach ($related as $item): ?>
                        <a href="<?= esc($item['url'], 'attr') ?>">
                            <small><?= esc($item['type']) ?></small>
                            <span><?= esc($item['label']) ?></span>
                        </a>
                        <?php endforeach ?>
                    </div>
                </section>
                <?php endif ?>
            </aside>
        </section>
    </main>

    <?= view('components/site_footer') ?>
</body>
</html>
