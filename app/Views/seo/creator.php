<?php
helper(['seo', 'aa_icon']);

$pageUrl = site_url('creator');
$applyUrl = site_url('creator/apply');
$templatesUrl = site_url('templates');
$plansUrl = site_url('plans');
$isLoggedIn = (bool) (session()->get('isLoggedIn') ?? session()->get('userId'));
$startUrl = $isLoggedIn ? $applyUrl : site_url('login');
$steps = [
    ['01', 'Buat template', 'Desain template undangan atau aset pengalaman acara memakai AdaAcara Studio.'],
    ['02', 'Submit untuk review', 'Template dicek admin agar kualitas, kelengkapan, dan keamanan tetap terjaga.'],
    ['03', 'Publish marketplace', 'Template yang disetujui bisa muncul untuk digunakan user AdaAcara.'],
    ['04', 'Dapat royalty', 'Saat template memenuhi qualified publish, creator mendapat bagian dari nilai lisensi template.'],
];
$metrics = [
    ['Total Penggunaan', 'Berapa kali template mulai dipakai user.'],
    ['Published', 'Penggunaan yang benar-benar menjadi project publish.'],
    ['Conversion', 'Rasio dari views, use, sampai publish.'],
    ['Earnings', 'Royalty pending dan tersedia untuk penarikan.'],
];
$rules = [
    'Creator mendapat hingga 90% dari nilai/lisensi template, bukan dari seluruh harga membership.',
    'Klik Use Template hanya menjadi statistik, bukan langsung komisi.',
    'Royalty dibuat saat penggunaan memenuhi qualified publish.',
    'Pembayaran yang sudah dikonfirmasi membuat royalty masuk ke saldo tersedia.',
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->website()
        ->title('AdaAcara Creator - Buat Template dan Dapat Royalty')
        ->description('Program AdaAcara Creator membantu desainer membuat template, submit review, publish ke marketplace, dan mendapatkan royalty dari nilai lisensi template.')
        ->canonical($pageUrl)
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Creator', 'url' => $pageUrl],
        ])
        ->render() ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <style>
        body.aa-creator-page {
            margin: 0;
            background:
                radial-gradient(circle at 12% 2%, rgba(126, 58, 242, .16), transparent 28rem),
                radial-gradient(circle at 88% 8%, rgba(245, 158, 11, .14), transparent 28rem),
                linear-gradient(180deg, #fff 0%, #fff7ed 46%, #fff 100%);
            color: #17132f;
        }

        .aa-creator-shell {
            width: min(1160px, calc(100% - 32px));
            margin: 0 auto;
        }

        .aa-creator-hero {
            padding: clamp(64px, 9vw, 112px) 0 42px;
        }

        .aa-creator-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, .78fr);
            gap: 42px;
            align-items: center;
        }

        .aa-creator-eyebrow {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            border: 1px solid rgba(126, 58, 242, .2);
            border-radius: 999px;
            background: #f6f0ff;
            color: #6d28d9;
            padding: 0 13px;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .aa-creator-hero h1 {
            max-width: 800px;
            margin: 18px 0 0;
            color: #17132f;
            font-size: clamp(42px, 6.2vw, 74px);
            line-height: .98;
            letter-spacing: -.035em;
        }

        .aa-creator-gradient-text {
            background: linear-gradient(100deg, #6d28d9, #d97706);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .aa-creator-hero p {
            max-width: 680px;
            margin: 20px 0 0;
            color: #5b5870;
            font-size: clamp(16px, 2vw, 19px);
            font-weight: 650;
            line-height: 1.8;
        }

        .aa-creator-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .aa-creator-btn {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0 18px;
            font-size: 14px;
            font-weight: 950;
            text-decoration: none;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .aa-creator-btn:hover {
            transform: translateY(-1px);
        }

        .aa-creator-btn-primary {
            border: 1px solid #6d28d9;
            background: linear-gradient(100deg, #6d28d9, #f59e0b);
            color: #fff;
            box-shadow: 0 18px 40px rgba(109, 40, 217, .2);
        }

        .aa-creator-btn-secondary {
            border: 1px solid #eadcfb;
            background: rgba(255, 255, 255, .88);
            color: #17132f;
        }

        .aa-creator-visual {
            border: 1px solid rgba(234, 220, 251, .9);
            border-radius: 34px;
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 28px 90px rgba(88, 28, 135, .13);
            padding: 22px;
        }

        .aa-creator-card-preview {
            display: grid;
            gap: 16px;
            border-radius: 26px;
            background:
                linear-gradient(135deg, rgba(109, 40, 217, .1), rgba(245, 158, 11, .13)),
                #fff;
            padding: 22px;
        }

        .aa-creator-preview-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .aa-creator-avatar {
            display: grid;
            width: 58px;
            aspect-ratio: 1;
            place-items: center;
            border-radius: 20px;
            background: #fff;
            color: #6d28d9;
            box-shadow: inset 0 0 0 1px rgba(109, 40, 217, .12);
        }

        .aa-creator-avatar svg {
            width: 28px;
            height: 28px;
        }

        .aa-creator-preview-badge {
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            padding: 8px 11px;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .aa-creator-card-preview h2 {
            margin: 4px 0 0;
            color: #17132f;
            font-size: 30px;
            line-height: 1.08;
            letter-spacing: -.02em;
        }

        .aa-creator-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .aa-creator-stat {
            border: 1px solid rgba(109, 40, 217, .12);
            border-radius: 18px;
            background: rgba(255, 255, 255, .82);
            padding: 14px;
        }

        .aa-creator-stat strong {
            display: block;
            color: #17132f;
            font-size: 22px;
            font-weight: 950;
        }

        .aa-creator-stat span {
            display: block;
            margin-top: 4px;
            color: #6b6680;
            font-size: 12px;
            font-weight: 800;
        }

        .aa-creator-section {
            padding: 38px 0;
        }

        .aa-creator-section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
        }

        .aa-creator-section-head h2 {
            max-width: 720px;
            margin: 0;
            color: #17132f;
            font-size: clamp(30px, 4.4vw, 48px);
            line-height: 1.06;
            letter-spacing: -.03em;
        }

        .aa-creator-section-head p {
            max-width: 430px;
            margin: 0;
            color: #6b6680;
            font-size: 15px;
            font-weight: 650;
            line-height: 1.75;
        }

        .aa-creator-step-grid,
        .aa-creator-metric-grid {
            display: grid;
            gap: 14px;
        }

        .aa-creator-step-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .aa-creator-metric-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .aa-creator-step,
        .aa-creator-metric,
        .aa-creator-rule-panel {
            border: 1px solid rgba(234, 220, 251, .9);
            border-radius: 28px;
            background: rgba(255, 255, 255, .84);
            box-shadow: 0 18px 48px rgba(88, 28, 135, .08);
        }

        .aa-creator-step,
        .aa-creator-metric {
            padding: 20px;
        }

        .aa-creator-step-number {
            display: inline-flex;
            min-height: 30px;
            align-items: center;
            border-radius: 999px;
            background: #fff7ed;
            color: #b45309;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .12em;
        }

        .aa-creator-step h3,
        .aa-creator-metric h3 {
            margin: 14px 0 0;
            color: #17132f;
            font-size: 18px;
            line-height: 1.2;
        }

        .aa-creator-step p,
        .aa-creator-metric p {
            margin: 10px 0 0;
            color: #6b6680;
            font-size: 14px;
            font-weight: 650;
            line-height: 1.65;
        }

        .aa-creator-rule-panel {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(0, 1fr);
            gap: 28px;
            align-items: center;
            padding: clamp(24px, 4vw, 36px);
        }

        .aa-creator-rule-panel h2 {
            margin: 12px 0 0;
            color: #17132f;
            font-size: clamp(30px, 4.4vw, 48px);
            line-height: 1.05;
            letter-spacing: -.03em;
        }

        .aa-creator-rule-panel p {
            margin: 14px 0 0;
            color: #6b6680;
            font-size: 15px;
            font-weight: 650;
            line-height: 1.75;
        }

        .aa-creator-rule-list {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .aa-creator-rule-list li {
            display: flex;
            gap: 10px;
            border: 1px solid rgba(245, 158, 11, .18);
            border-radius: 18px;
            background: #fff;
            padding: 13px 14px;
            color: #17132f;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.5;
        }

        .aa-creator-rule-list li::before {
            content: "";
            flex: 0 0 auto;
            width: 18px;
            height: 18px;
            margin-top: 2px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #f59e0b);
            box-shadow: inset 0 0 0 5px #fff;
        }

        .aa-creator-cta {
            padding: 38px 0 70px;
        }

        .aa-creator-cta-card {
            border: 1px solid rgba(234, 220, 251, .9);
            border-radius: 34px;
            background:
                radial-gradient(circle at 12% 0%, rgba(126, 58, 242, .16), transparent 24rem),
                radial-gradient(circle at 90% 0%, rgba(245, 158, 11, .16), transparent 24rem),
                #fff;
            padding: clamp(28px, 5vw, 46px);
            text-align: center;
            box-shadow: 0 28px 80px rgba(88, 28, 135, .1);
        }

        .aa-creator-cta-card h2 {
            max-width: 720px;
            margin: 0 auto;
            color: #17132f;
            font-size: clamp(30px, 4.6vw, 52px);
            line-height: 1.04;
            letter-spacing: -.03em;
        }

        .aa-creator-cta-card p {
            max-width: 660px;
            margin: 14px auto 0;
            color: #6b6680;
            font-size: 16px;
            font-weight: 650;
            line-height: 1.75;
        }

        .aa-creator-cta-card .aa-creator-actions {
            justify-content: center;
        }

        @media (max-width: 980px) {
            .aa-creator-hero-grid,
            .aa-creator-rule-panel {
                grid-template-columns: 1fr;
            }

            .aa-creator-step-grid,
            .aa-creator-metric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .aa-creator-shell {
                width: min(100% - 24px, 1160px);
            }

            .aa-creator-hero {
                padding-top: 44px;
            }

            .aa-creator-hero h1 {
                font-size: 40px;
            }

            .aa-creator-section-head {
                display: block;
            }

            .aa-creator-section-head p {
                margin-top: 12px;
            }

            .aa-creator-step-grid,
            .aa-creator-metric-grid,
            .aa-creator-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="aa-app-ui aa-public-theme-page aa-creator-page">
    <?= view('components/public_site_header', ['active' => 'creator']) ?>

    <main>
        <section class="aa-creator-hero">
            <div class="aa-creator-shell aa-creator-hero-grid">
                <div>
                    <span class="aa-creator-eyebrow">AdaAcara Creator</span>
                    <h1>Buat template, publish, lalu dapat <span class="aa-creator-gradient-text">royalty</span>.</h1>
                    <p>Creator Marketplace adalah tempat desainer membuat template untuk AdaAcara Studio. User memakai template kamu untuk membuat undangan, photobooth, atau pengalaman digital acara lainnya.</p>
                    <div class="aa-creator-actions">
                        <a class="aa-creator-btn aa-creator-btn-primary" href="<?= esc($startUrl, 'attr') ?>"><?= $isLoggedIn ? 'Apply Creator' : 'Login untuk Apply' ?></a>
                        <a class="aa-creator-btn aa-creator-btn-secondary" href="<?= esc($templatesUrl, 'attr') ?>">Lihat Template</a>
                    </div>
                </div>
                <aside class="aa-creator-visual" aria-label="Ringkasan Creator Marketplace">
                    <div class="aa-creator-card-preview">
                        <div class="aa-creator-preview-top">
                            <span class="aa-creator-avatar" aria-hidden="true"><?= aa_phosphor_icon('crown') ?></span>
                            <span class="aa-creator-preview-badge">90% lisensi</span>
                        </div>
                        <h2>Template creator yang benar-benar dipublish user akan masuk perhitungan royalty.</h2>
                        <div class="aa-creator-stats">
                            <div class="aa-creator-stat"><strong>Views</strong><span>Statistik marketplace</span></div>
                            <div class="aa-creator-stat"><strong>Uses</strong><span>User mulai desain</span></div>
                            <div class="aa-creator-stat"><strong>Publish</strong><span>Qualified usage</span></div>
                            <div class="aa-creator-stat"><strong>Royalty</strong><span>Pending ke tersedia</span></div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="aa-creator-section">
            <div class="aa-creator-shell">
                <div class="aa-creator-section-head">
                    <h2>Alur creator dibuat jelas dari desain sampai penghasilan.</h2>
                    <p>Tidak dihitung dari sekadar klik. Sistem diarahkan ke penggunaan yang benar-benar menghasilkan publish.</p>
                </div>
                <div class="aa-creator-step-grid">
                    <?php foreach ($steps as $step): ?>
                        <article class="aa-creator-step">
                            <span class="aa-creator-step-number"><?= esc($step[0]) ?></span>
                            <h3><?= esc($step[1]) ?></h3>
                            <p><?= esc($step[2]) ?></p>
                        </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <section class="aa-creator-section">
            <div class="aa-creator-shell aa-creator-rule-panel">
                <div>
                    <span class="aa-creator-eyebrow">Model Royalty</span>
                    <h2>90% dari nilai template, bukan 90% dari seluruh membership.</h2>
                    <p>Contoh: jika nilai lisensi template Rp20.000, creator mendapat Rp18.000 dan AdaAcara mendapat Rp2.000 dari komponen template tersebut.</p>
                </div>
                <ul class="aa-creator-rule-list">
                    <?php foreach ($rules as $rule): ?>
                        <li><?= esc($rule) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        </section>

        <section class="aa-creator-section">
            <div class="aa-creator-shell">
                <div class="aa-creator-section-head">
                    <h2>Dashboard creator fokus pada angka yang penting.</h2>
                    <p>Creator bisa memahami performa template tanpa harus membaca laporan yang rumit.</p>
                </div>
                <div class="aa-creator-metric-grid">
                    <?php foreach ($metrics as $metric): ?>
                        <article class="aa-creator-metric">
                            <h3><?= esc($metric[0]) ?></h3>
                            <p><?= esc($metric[1]) ?></p>
                        </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <section class="aa-creator-cta">
            <div class="aa-creator-shell">
                <div class="aa-creator-cta-card">
                    <h2>Siap ikut membangun marketplace template AdaAcara?</h2>
                    <p>Daftar creator gratis. Setelah disetujui admin, kamu bisa submit template dan mulai memantau performanya dari dashboard creator.</p>
                    <div class="aa-creator-actions">
                        <a class="aa-creator-btn aa-creator-btn-primary" href="<?= esc($startUrl, 'attr') ?>"><?= $isLoggedIn ? 'Mulai Apply Creator' : 'Login untuk Apply Creator' ?></a>
                        <a class="aa-creator-btn aa-creator-btn-secondary" href="<?= esc($plansUrl, 'attr') ?>">Lihat Paket AdaAcara</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?= view('components/site_footer') ?>
</body>
</html>
