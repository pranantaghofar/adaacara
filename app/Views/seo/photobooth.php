<?php
helper('seo');

$pageUrl = site_url('fitur/photobooth-digital');
$steps = [
    ['01', 'Scan QR', 'Tamu membuka link photobooth dari QR yang ditempel di meja tamu, pintu masuk, atau area photobooth.', 'assets/img/plans/showcase-1.mp4', 'video'],
    ['02', 'Pilih frame', 'Frame photobooth dibuat dari editor AdaAcara, lalu tampil sebagai pilihan di halaman memories acara.', 'assets/img/plans/showcase-4.png', 'image'],
    ['03', 'Ambil atau upload foto', 'Tamu bisa foto langsung atau upload dari galeri, lalu mengisi nama dan email untuk menerima kode akses print/download.', 'assets/img/plans/showcase-5.png', 'image'],
    ['04', 'Masuk galeri memories', 'Hasil foto terkumpul otomatis di galeri, bisa dicari, dicetak, atau didownload memakai kode akses.', 'assets/img/plans/showcase-7.png', 'image'],
];
$benefits = [
    ['Untuk seller', 'Jual undangan dengan pengalaman acara yang lebih lengkap: undangan, RSVP, QR photobooth, galeri memories, print/download, dan dashboard.'],
    ['Untuk pengantin', 'Tidak perlu aplikasi tambahan. Tamu cukup membuka link memories dari undangan atau QR yang sudah disediakan.'],
    ['Untuk meja printer', 'Galeri memories bisa standby di monitor dan auto-refresh, sehingga foto baru lebih cepat muncul untuk proses print.'],
];
$features = [
    'QR khusus memories',
    'Frame photobooth dari editor',
    'Upload/foto dari HP',
    'Kode akses aman dikirim ke email',
    'Galeri auto-load',
    'Batas print per foto',
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->website()
        ->title('Photobooth Digital AdaAcara - QR, Frame, Galeri Memories')
        ->description('Photobooth digital AdaAcara membantu tamu scan QR, pilih frame, foto atau upload, lalu hasilnya masuk ke galeri memories acara untuk print dan download.')
        ->canonical($pageUrl)
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Photobooth Digital', 'url' => $pageUrl],
        ])
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <style>
        body.aa-photobooth-page {
            margin: 0;
            background:
                radial-gradient(circle at 16% 0%, rgba(143, 101, 223, .18), transparent 30rem),
                radial-gradient(circle at 90% 8%, rgba(20, 184, 166, .12), transparent 30rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 48%, #ffffff 100%);
            color: #0f172a;
        }

        .aa-photobooth-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .aa-photobooth-hero {
            padding: clamp(64px, 9vw, 112px) 0 46px;
        }

        .aa-photobooth-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, .82fr);
            gap: 44px;
            align-items: center;
        }

        .aa-photobooth-eyebrow {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            border: 1px solid rgba(117, 80, 196, .2);
            border-radius: 999px;
            background: #f6f0ff;
            color: #7550c4;
            padding: 0 13px;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .aa-photobooth-hero h1 {
            max-width: 780px;
            margin: 18px 0 0;
            color: #111827;
            font-size: clamp(42px, 6.4vw, 76px);
            line-height: .98;
            letter-spacing: -.04em;
        }

        .aa-photobooth-hero p {
            max-width: 680px;
            margin: 20px 0 0;
            color: #475569;
            font-size: clamp(16px, 2vw, 19px);
            font-weight: 650;
            line-height: 1.8;
        }

        .aa-photobooth-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .aa-photobooth-btn {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0 18px;
            font-size: 14px;
            font-weight: 950;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .aa-photobooth-btn:hover {
            transform: translateY(-1px);
        }

        .aa-photobooth-btn-primary {
            border: 1px solid #111827;
            background: #111827;
            color: #ffffff;
            box-shadow: 0 16px 34px rgba(15, 23, 42, .18);
        }

        .aa-photobooth-btn-secondary {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
        }

        .aa-photobooth-modal {
            position: fixed;
            inset: 0;
            z-index: 90;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .aa-photobooth-modal.is-open {
            display: flex;
        }

        .aa-photobooth-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, .52);
            backdrop-filter: blur(8px);
        }

        .aa-photobooth-modal-card {
            position: relative;
            z-index: 1;
            width: min(460px, 100%);
            border: 1px solid rgba(117, 80, 196, .18);
            border-radius: 24px;
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 28px 80px rgba(15, 23, 42, .24);
            overflow: hidden;
        }

        .aa-photobooth-modal-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            border-bottom: 1px solid #e2e8f0;
            padding: 22px 24px 18px;
        }

        .aa-photobooth-modal-head h3 {
            margin: 0;
            color: #21163f;
            font-size: 22px;
            font-weight: 950;
        }

        .aa-photobooth-modal-head p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.5;
        }

        .aa-photobooth-modal-close {
            width: 38px;
            height: 38px;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
            font-size: 22px;
            line-height: 1;
        }

        .aa-photobooth-modal-body {
            padding: 22px 24px 24px;
        }

        .aa-photobooth-create-form {
            display: grid;
            gap: 16px;
        }

        .aa-photobooth-field {
            display: grid;
            gap: 7px;
        }

        .aa-photobooth-field label {
            color: #334155;
            font-size: 12px;
            font-weight: 900;
        }

        .aa-photobooth-field input {
            width: 100%;
            min-height: 48px;
            border: 1px solid #dbe4ef;
            border-radius: 16px;
            background: #ffffff;
            color: #0f172a;
            padding: 0 14px;
            font: inherit;
            font-weight: 700;
            outline: none;
        }

        .aa-photobooth-field input:focus {
            border-color: rgba(117, 80, 196, .56);
            box-shadow: 0 0 0 4px rgba(117, 80, 196, .12);
        }

        .aa-photobooth-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 6px;
        }

        html[data-aa-public-theme="dark"] .aa-photobooth-modal-card {
            border-color: rgba(168, 85, 247, .24);
            background: #111827;
            box-shadow: 0 28px 80px rgba(0, 0, 0, .45);
        }

        html[data-aa-public-theme="dark"] .aa-photobooth-modal-head {
            border-color: rgba(148, 163, 184, .18);
        }

        html[data-aa-public-theme="dark"] .aa-photobooth-modal-head h3 {
            color: #f8fafc;
        }

        html[data-aa-public-theme="dark"] .aa-photobooth-modal-head p,
        html[data-aa-public-theme="dark"] .aa-photobooth-field label {
            color: #a8b5c7;
        }

        html[data-aa-public-theme="dark"] .aa-photobooth-modal-close,
        html[data-aa-public-theme="dark"] .aa-photobooth-field input {
            border-color: rgba(148, 163, 184, .24);
            background: rgba(15, 23, 42, .92);
            color: #f8fafc;
        }

        .aa-photobooth-visual {
            position: relative;
            min-height: 520px;
        }

        .aa-photobooth-phone {
            position: relative;
            width: min(330px, 100%);
            margin: 0 auto;
            border: 10px solid #111827;
            border-radius: 38px;
            background: #111827;
            box-shadow: 0 30px 80px rgba(15, 23, 42, .2);
            overflow: hidden;
        }

        .aa-photobooth-screen {
            display: grid;
            min-height: 500px;
            align-content: start;
            gap: 16px;
            border-radius: 28px;
            background: #f8fafc;
            padding: 22px;
        }

        .aa-photobooth-screen-media {
            aspect-ratio: 3 / 4;
            border-radius: 22px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .aa-photobooth-screen-media video,
        .aa-photobooth-screen-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .aa-photobooth-screen-title {
            color: #0f172a;
            font-size: 22px;
            font-weight: 950;
            line-height: 1.15;
        }

        .aa-photobooth-screen-text {
            color: #64748b;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.6;
        }

        .aa-photobooth-chip {
            position: absolute;
            display: grid;
            gap: 4px;
            max-width: 220px;
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 22px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 20px 54px rgba(15, 23, 42, .12);
            padding: 16px;
        }

        .aa-photobooth-chip strong {
            color: #111827;
            font-size: 14px;
            font-weight: 950;
        }

        .aa-photobooth-chip span {
            color: #64748b;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.45;
        }

        .aa-photobooth-chip.is-qr {
            top: 46px;
            left: 0;
        }

        .aa-photobooth-chip.is-gallery {
            right: 0;
            bottom: 36px;
        }

        .aa-photobooth-qr {
            display: grid;
            width: 72px;
            aspect-ratio: 1;
            place-items: center;
            border-radius: 18px;
            background: #0f172a;
            color: #ffffff;
        }

        .aa-photobooth-qr img {
            width: 48px;
            height: 48px;
            display: block;
            object-fit: contain;
        }

        .aa-photobooth-section {
            padding: 42px 0;
        }

        .aa-photobooth-section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
        }

        .aa-photobooth-section-head h2 {
            max-width: 720px;
            margin: 0;
            color: #111827;
            font-size: clamp(30px, 4.6vw, 48px);
            line-height: 1.05;
            letter-spacing: -.035em;
        }

        .aa-photobooth-section-head p {
            max-width: 430px;
            margin: 0;
            color: #64748b;
            font-size: 15px;
            font-weight: 650;
            line-height: 1.75;
        }

        .aa-photobooth-step-grid,
        .aa-photobooth-benefit-grid {
            display: grid;
            gap: 16px;
        }

        .aa-photobooth-step-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .aa-photobooth-benefit-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .aa-photobooth-step,
        .aa-photobooth-benefit,
        .aa-photobooth-feature-panel {
            border: 1px solid rgba(226, 232, 240, .92);
            border-radius: 24px;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 18px 48px rgba(15, 23, 42, .06);
        }

        .aa-photobooth-step {
            overflow: hidden;
        }

        .aa-photobooth-step-media {
            display: block;
            aspect-ratio: 4 / 5;
            background: #e2e8f0;
            overflow: hidden;
        }

        .aa-photobooth-step-media img,
        .aa-photobooth-step-media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .aa-photobooth-step-copy,
        .aa-photobooth-benefit {
            padding: 18px;
        }

        .aa-photobooth-step-number {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            border-radius: 999px;
            background: #f6f0ff;
            color: #7550c4;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 950;
        }

        .aa-photobooth-step h3,
        .aa-photobooth-benefit h3 {
            margin: 12px 0 0;
            color: #111827;
            font-size: 18px;
            line-height: 1.2;
        }

        .aa-photobooth-step p,
        .aa-photobooth-benefit p {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 14px;
            font-weight: 650;
            line-height: 1.7;
        }

        .aa-photobooth-feature-panel {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(280px, 1.05fr);
            gap: 24px;
            align-items: center;
            padding: clamp(22px, 4vw, 36px);
        }

        .aa-photobooth-feature-panel h2 {
            margin: 0;
            color: #111827;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.08;
            letter-spacing: -.03em;
        }

        .aa-photobooth-feature-panel p {
            margin: 14px 0 0;
            color: #64748b;
            font-size: 15px;
            font-weight: 650;
            line-height: 1.75;
        }

        .aa-photobooth-feature-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .aa-photobooth-feature-list li {
            display: flex;
            min-height: 44px;
            align-items: center;
            border-radius: 16px;
            background: #f8fafc;
            color: #334155;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 850;
        }

        .aa-photobooth-cta {
            padding: 42px 0 76px;
        }

        .aa-photobooth-cta-card {
            border-radius: 30px;
            background:
                radial-gradient(circle at 18% 0%, rgba(196, 181, 253, .28), transparent 24rem),
                linear-gradient(135deg, #111827 0%, #312e81 100%);
            color: #ffffff;
            padding: clamp(28px, 5vw, 48px);
            text-align: center;
        }

        .aa-photobooth-cta-card h2 {
            max-width: 760px;
            margin: 0 auto;
            font-size: clamp(30px, 5vw, 54px);
            line-height: 1.02;
            letter-spacing: -.035em;
        }

        .aa-photobooth-cta-card p {
            max-width: 680px;
            margin: 16px auto 0;
            color: rgba(255, 255, 255, .78);
            font-size: 16px;
            font-weight: 650;
            line-height: 1.75;
        }

        .aa-photobooth-cta-card .aa-photobooth-actions {
            justify-content: center;
        }

        .aa-photobooth-cta-card .aa-photobooth-btn-secondary {
            border-color: rgba(255, 255, 255, .24);
            background: rgba(255, 255, 255, .1);
            color: #ffffff;
        }

        @media (max-width: 980px) {
            .aa-photobooth-hero-grid,
            .aa-photobooth-feature-panel,
            .aa-photobooth-step-grid,
            .aa-photobooth-benefit-grid {
                grid-template-columns: 1fr;
            }

            .aa-photobooth-visual {
                min-height: 460px;
            }

            .aa-photobooth-chip.is-qr {
                left: 8px;
            }

            .aa-photobooth-chip.is-gallery {
                right: 8px;
            }

            .aa-photobooth-section-head {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 620px) {
            .aa-photobooth-shell {
                width: min(100% - 24px, 1180px);
            }

            .aa-photobooth-hero {
                padding-top: 48px;
            }

            .aa-photobooth-visual {
                min-height: auto;
            }

            .aa-photobooth-phone {
                width: min(300px, 100%);
            }

            .aa-photobooth-screen {
                min-height: 430px;
                padding: 18px;
            }

            .aa-photobooth-chip {
                position: static;
                max-width: none;
                margin-top: 12px;
            }

            .aa-photobooth-feature-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="aa-app-ui aa-public-theme-page aa-photobooth-page">
    <?= view('components/public_site_header', ['active' => 'photobooth']) ?>

    <main>
        <section class="aa-photobooth-hero">
            <div class="aa-photobooth-shell aa-photobooth-hero-grid">
                <div>
                    <span class="aa-photobooth-eyebrow">Photobooth Digital</span>
                    <h1>QR photobooth untuk membuat galeri memories acara.</h1>
                    <p>Tamu cukup scan QR, pilih frame, ambil foto atau upload dari HP, lalu hasilnya otomatis masuk ke galeri memories. Cocok untuk seller undangan yang ingin memberi pengalaman acara lebih profesional.</p>
                    <div class="aa-photobooth-actions">
                        <a class="aa-photobooth-btn aa-photobooth-btn-primary" href="<?= site_url('u/garden-flyrin/memories') ?>" target="_blank" rel="noopener">Coba Preview Photobooth</a>
                        <button class="aa-photobooth-btn aa-photobooth-btn-secondary" type="button" data-photobooth-create-open>Buat Photobooth</button>
                    </div>
                </div>

                <aside class="aa-photobooth-visual" aria-label="Preview Photobooth AdaAcara">
                    <div class="aa-photobooth-phone">
                        <div class="aa-photobooth-screen">
                            <span class="aa-photobooth-screen-media">
                                <video src="<?= esc(aa_asset_url('assets/img/plans/showcase-1.mp4'), 'attr') ?>" autoplay muted loop playsinline preload="metadata"></video>
                            </span>
                            <strong class="aa-photobooth-screen-title">Photobooth Memories</strong>
                            <span class="aa-photobooth-screen-text">Foto tamu terkumpul dalam satu galeri acara dan bisa diproses untuk print atau download.</span>
                        </div>
                    </div>
                    <div class="aa-photobooth-chip is-qr">
                        <strong>QR siap cetak</strong>
                        <div class="aa-photobooth-qr" aria-hidden="true">
                            <img src="<?= esc(aa_asset_url('assets/home/galeri/qr.png'), 'attr') ?>" alt="" loading="lazy" decoding="async">
                        </div>
                        <span>Bisa dipasang di area acara supaya tamu langsung masuk ke halaman memories.</span>
                    </div>
                    <div class="aa-photobooth-chip is-gallery">
                        <strong>Galeri live</strong>
                        <span>Foto baru bisa muncul otomatis untuk kebutuhan meja printer.</span>
                    </div>
                </aside>
            </div>
        </section>

        <section class="aa-photobooth-section">
            <div class="aa-photobooth-shell">
                <div class="aa-photobooth-section-head">
                    <h2>Alur sederhana dari QR sampai galeri.</h2>
                    <p>Kontennya mengikuti konsep yang sudah berjalan di halaman memories dan QR: ringan untuk tamu, tetap rapi untuk pengelola acara.</p>
                </div>
                <div class="aa-photobooth-step-grid">
                    <?php foreach ($steps as $step): ?>
                    <article class="aa-photobooth-step">
                        <span class="aa-photobooth-step-media" aria-hidden="true">
                            <?php if ($step[4] === 'video'): ?>
                                <video src="<?= esc(aa_asset_url($step[3]), 'attr') ?>" autoplay muted loop playsinline preload="metadata"></video>
                            <?php else: ?>
                                <img src="<?= esc(aa_asset_url($step[3]), 'attr') ?>" alt="" loading="lazy" decoding="async">
                            <?php endif ?>
                        </span>
                        <div class="aa-photobooth-step-copy">
                            <span class="aa-photobooth-step-number"><?= esc($step[0]) ?></span>
                            <h3><?= esc($step[1]) ?></h3>
                            <p><?= esc($step[2]) ?></p>
                        </div>
                    </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <section class="aa-photobooth-section">
            <div class="aa-photobooth-shell aa-photobooth-feature-panel">
                <div>
                    <span class="aa-photobooth-eyebrow">Memories + Print</span>
                    <h2>Dibuat untuk acara yang butuh dokumentasi tamu lebih hidup.</h2>
                    <p>Halaman memories mendukung pencarian nama tamu, kode akses print/download yang bisa dikirim ke email, tombol print browser, dan auto-load galeri untuk monitor standby.</p>
                </div>
                <ul class="aa-photobooth-feature-list">
                    <?php foreach ($features as $feature): ?>
                    <li><?= esc($feature) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        </section>

        <section class="aa-photobooth-section">
            <div class="aa-photobooth-shell">
                <div class="aa-photobooth-section-head">
                    <h2>Lebih profesional untuk seller, pengantin, dan tamu.</h2>
                    <p>Photobooth menjadi fitur tambahan yang bisa dijelaskan saat menyerahkan undangan ke customer.</p>
                </div>
                <div class="aa-photobooth-benefit-grid">
                    <?php foreach ($benefits as $benefit): ?>
                    <article class="aa-photobooth-benefit">
                        <h3><?= esc($benefit[0]) ?></h3>
                        <p><?= esc($benefit[1]) ?></p>
                    </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <section class="aa-photobooth-cta">
            <div class="aa-photobooth-shell">
                <div class="aa-photobooth-cta-card">
                    <h2>Jadikan undangan digital terasa seperti pengalaman acara.</h2>
                    <p>Mulai dari template, aktifkan kebutuhan memories, lalu gunakan QR photobooth sebagai bagian dari flow acara.</p>
                    <div class="aa-photobooth-actions">
                        <a class="aa-photobooth-btn aa-photobooth-btn-primary" href="<?= site_url('templates') ?>">Pilih Template</a>
                        <a class="aa-photobooth-btn aa-photobooth-btn-secondary" href="<?= site_url('plans') ?>">Lihat Paket</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div id="aaPhotoboothCreateModal" class="aa-photobooth-modal" aria-hidden="true">
        <div class="aa-photobooth-modal-backdrop" data-photobooth-create-close></div>
        <div class="aa-photobooth-modal-card" role="dialog" aria-modal="true" aria-labelledby="aaPhotoboothCreateTitle">
            <div class="aa-photobooth-modal-head">
                <div>
                    <h3 id="aaPhotoboothCreateTitle">Buat Photobooth Baru</h3>
                    <p>Isi detail dasar dulu. Setelah itu kamu masuk Studio untuk mulai menyiapkan desain frame.</p>
                </div>
                <button class="aa-photobooth-modal-close" type="button" data-photobooth-create-close aria-label="Tutup">&times;</button>
            </div>
            <div class="aa-photobooth-modal-body">
                <form class="aa-photobooth-create-form" action="<?= site_url('templates/create') ?>" method="post">
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <input type="hidden" name="blank_template" value="1">
                    <input type="hidden" name="project_intent" value="photobooth">
                    <div class="aa-photobooth-field">
                        <label for="aaPhotoboothCreateTitleInput">Nama Photobooth / Nama Acara</label>
                        <input id="aaPhotoboothCreateTitleInput" name="title" type="text"
                            placeholder="Contoh: Photobooth Sarah & Dimas" required>
                    </div>
                    <div class="aa-photobooth-field">
                        <label for="aaPhotoboothCreateSlugInput">Slug URL</label>
                        <input id="aaPhotoboothCreateSlugInput" name="slug" type="text" placeholder="contoh: photobooth-sarah-dimas">
                    </div>
                    <div class="aa-photobooth-field">
                        <label for="aaPhotoboothCreateDateInput">Tanggal Acara</label>
                        <input id="aaPhotoboothCreateDateInput" name="event_date" type="date">
                    </div>
                    <div class="aa-photobooth-modal-actions">
                        <button class="aa-photobooth-btn aa-photobooth-btn-secondary" type="button"
                            data-photobooth-create-close>Batal</button>
                        <button class="aa-photobooth-btn aa-photobooth-btn-primary" type="submit">Buat Photobooth</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?= view('components/site_footer') ?>
    <script>
        (function () {
            const modal = document.getElementById('aaPhotoboothCreateModal');
            const titleInput = document.getElementById('aaPhotoboothCreateTitleInput');
            const openButtons = document.querySelectorAll('[data-photobooth-create-open]');
            const closeButtons = document.querySelectorAll('[data-photobooth-create-close]');

            function setModalOpen(isOpen) {
                if (!modal) return;
                modal.classList.toggle('is-open', isOpen);
                modal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                document.body.style.overflow = isOpen ? 'hidden' : '';
                if (isOpen) {
                    setTimeout(function () {
                        titleInput?.focus?.();
                    }, 80);
                }
            }

            openButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    setModalOpen(true);
                });
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setModalOpen(false);
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                    setModalOpen(false);
                }
            });
        }());
    </script>
</body>
</html>
