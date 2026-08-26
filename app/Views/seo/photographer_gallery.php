<?php
helper(['seo', 'aa_asset']);

$pageUrl = site_url('fitur/galeri-klien-fotografer');
$previewUrl = site_url('gallery/kalia-juansyah-wedding');
$createUrl = (session()->get('isLoggedIn') ?? session()->get('userId')) ? site_url('photographer-galleries/create') : site_url('login');
$features = [
    ['Project klien', 'Buat galeri per client atau acara agar file tidak bercampur.'],
    ['Album fleksibel', 'Pisahkan Highlight, Ceremony, Reception, Family, atau album custom.'],
    ['PIN private', 'Bagikan galeri private hanya ke client yang menerima PIN.'],
    ['Client selection', 'Client bisa memilih foto untuk dicetak atau diedit sesuai batas pilihan.'],
    ['Komentar revisi', 'Catatan client masuk ke dashboard fotografer per foto.'],
    ['Halaman keluarga', 'Client bisa menyiapkan pilihan foto khusus untuk dibagikan ke keluarga.'],
];
$steps = [
    ['01', 'Buat Project', 'Isi nama client, tanggal, studio, cover, privacy, dan batas pilihan foto.'],
    ['02', 'Upload Foto', 'Masukkan ratusan foto, pilih album aktif, lalu atur status foto.'],
    ['03', 'Client Memilih', 'Client membuka link, memberi favorit, memilih foto cetak, dan menulis revisi.'],
    ['04', 'Bagikan Aman', 'Foto pilihan bisa dibagikan ke keluarga dengan atau tanpa PIN.'],
];
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->website()
        ->title('Galeri Klien Fotografer AdaAcara - Website Galeri Private untuk Usaha Fotografer')
        ->description('Galeri Klien Fotografer AdaAcara membantu usaha fotografer membuat project client, upload foto, mengatur album, PIN, pilihan cetak, komentar revisi, download, dan halaman keluarga.')
        ->canonical($pageUrl)
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Galeri Klien Fotografer', 'url' => $pageUrl],
        ])
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <style>
        body.aa-photographer-feature{margin:0;background:linear-gradient(180deg,#fbf9ff 0%,#ffffff 42%,#f8fbff 100%);color:#142033}
        .aa-pg-shell{width:min(1160px,calc(100% - 32px));margin:0 auto}
        .aa-pg-hero{padding:clamp(72px,9vw,118px) 0 48px}
        .aa-pg-hero-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(320px,.9fr);gap:46px;align-items:center}
        .aa-pg-eyebrow{display:inline-flex;min-height:32px;align-items:center;border:1px solid rgba(143,101,223,.24);border-radius:999px;background:#f5f3ff;color:#7c4fe0;padding:0 13px;font-size:11px;font-weight:950;letter-spacing:.16em;text-transform:uppercase}
        .aa-pg-hero h1{max-width:760px;margin:18px 0 0;font-size:clamp(42px,6vw,72px);line-height:.98;font-weight:950;letter-spacing:-.045em}
        .aa-pg-hero p{max-width:680px;margin:20px 0 0;color:#53627a;font-size:clamp(16px,2vw,19px);font-weight:700;line-height:1.8}
        .aa-pg-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:30px}
        .aa-pg-action-stack{display:inline-flex;flex-direction:column;align-items:center;gap:8px}
        .aa-pg-btn{display:inline-flex;min-height:48px;align-items:center;justify-content:center;border-radius:999px;padding:0 20px;font-size:14px;font-weight:950;text-decoration:none;transition:transform .18s ease,box-shadow .18s ease}
        .aa-pg-btn:hover{transform:translateY(-1px)}
        .aa-pg-btn-primary{background:#142033;color:#fff;box-shadow:0 16px 34px rgba(15,23,42,.18)}
        .aa-pg-btn-secondary{border:1px solid #dfe7f1;background:#fff;color:#142033}
        .aa-pg-preview-hint{display:block;color:#7c4fe0;font-size:12px;font-weight:900}
        .aa-pg-demo{position:relative;border:1px solid rgba(148,163,184,.16);border-radius:32px;background:rgba(255,255,255,.88);box-shadow:0 26px 80px rgba(79,70,229,.14);padding:18px;overflow:hidden}
        .aa-pg-demo::before{position:absolute;inset:auto -40px -60px auto;width:210px;height:210px;border-radius:999px;background:rgba(143,101,223,.16);content:"";filter:blur(10px)}
        .aa-pg-window{position:relative;border:1px solid rgba(148,163,184,.16);border-radius:24px;background:#fff;overflow:hidden}
        .aa-pg-window-head{display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eef2f7;padding:14px 16px}
        .aa-pg-dot{display:inline-flex;width:9px;height:9px;border-radius:999px;background:#8f65df;box-shadow:16px 0 0 #f472b6,32px 0 0 #14b8a6}
        .aa-pg-window-title{font-size:12px;font-weight:950;color:#64748b}
        .aa-pg-cover{display:grid;grid-template-columns:120px minmax(0,1fr);gap:16px;padding:18px}
        .aa-pg-cover-img{height:150px;border-radius:22px;background:linear-gradient(135deg,#d9c7ff,#fff1f2);overflow:hidden}
        .aa-pg-cover-img img{width:100%;height:100%;object-fit:cover;opacity:.86}
        .aa-pg-mini-title{margin:0;font-size:26px;line-height:1.08;font-weight:950}
        .aa-pg-mini-meta{margin:8px 0 0;color:#64748b;font-size:13px;font-weight:850}
        .aa-pg-pills{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}.aa-pg-pill{border:1px solid #e2e8f0;border-radius:999px;background:#fff;padding:8px 10px;font-size:11px;font-weight:950;color:#475569}
        .aa-pg-tabs,.aa-pg-grid{display:grid;gap:10px;padding:0 18px 18px}.aa-pg-tabs{grid-template-columns:repeat(4,minmax(0,1fr))}.aa-pg-tab{border-radius:999px;background:#f5f3ff;padding:9px 8px;text-align:center;font-size:10px;font-weight:950;color:#7c4fe0}.aa-pg-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
        .aa-pg-photo{min-height:92px;border-radius:18px;background:linear-gradient(135deg,#e7eef9,#ffffff);box-shadow:inset 0 -34px 0 rgba(255,255,255,.82)}.aa-pg-photo:nth-child(2){background:linear-gradient(135deg,#dbeafe,#fff7ed)}.aa-pg-photo:nth-child(3){background:linear-gradient(135deg,#fce7f3,#f0fdfa)}.aa-pg-photo:nth-child(4){background:linear-gradient(135deg,#e0f2fe,#faf5ff)}.aa-pg-photo:nth-child(5){background:linear-gradient(135deg,#fef3c7,#fff)}.aa-pg-photo:nth-child(6){background:linear-gradient(135deg,#dcfce7,#eef2ff)}
        .aa-pg-section{padding:62px 0}.aa-pg-section-head{max-width:760px}.aa-pg-section-head h2{margin:0;font-size:clamp(30px,4vw,48px);line-height:1.05;font-weight:950;letter-spacing:-.035em}.aa-pg-section-head p{margin:14px 0 0;color:#64748b;font-size:16px;font-weight:700;line-height:1.75}
        .aa-pg-card-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:28px}.aa-pg-card{border:1px solid rgba(148,163,184,.14);border-radius:24px;background:#fff;padding:22px;box-shadow:0 18px 44px rgba(15,23,42,.06)}.aa-pg-card strong{display:block;font-size:17px;font-weight:950}.aa-pg-card p{margin:9px 0 0;color:#64748b;font-size:13px;font-weight:700;line-height:1.65}
        .aa-pg-flow{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:28px}.aa-pg-step{border:1px solid rgba(143,101,223,.18);border-radius:24px;background:#fbf9ff;padding:20px}.aa-pg-step span{display:inline-flex;width:38px;height:38px;align-items:center;justify-content:center;border-radius:14px;background:#8f65df;color:#fff;font-size:12px;font-weight:950}.aa-pg-step strong{display:block;margin-top:14px;font-size:16px;font-weight:950}.aa-pg-step p{margin:8px 0 0;color:#64748b;font-size:13px;font-weight:700;line-height:1.6}
        .aa-pg-cta{margin:50px auto 80px;border:1px solid rgba(143,101,223,.18);border-radius:30px;background:linear-gradient(135deg,#142033,#36235f);padding:34px;color:#fff}.aa-pg-cta h2{margin:0;font-size:34px;font-weight:950}.aa-pg-cta p{max-width:720px;margin:12px 0 0;color:#dbe3f0;font-weight:700;line-height:1.7}
        html[data-aa-public-theme="dark"] body.aa-photographer-feature{background:linear-gradient(180deg,#111827 0%,#151827 48%,#0f172a 100%);color:#f8fafc}html[data-aa-public-theme="dark"] .aa-pg-demo,html[data-aa-public-theme="dark"] .aa-pg-card,html[data-aa-public-theme="dark"] .aa-pg-window{background:#182034;border-color:rgba(148,163,184,.22)}html[data-aa-public-theme="dark"] .aa-pg-btn-secondary,html[data-aa-public-theme="dark"] .aa-pg-pill{background:#111827;border-color:rgba(148,163,184,.22);color:#f8fafc}html[data-aa-public-theme="dark"] .aa-pg-hero p,html[data-aa-public-theme="dark"] .aa-pg-section-head p,html[data-aa-public-theme="dark"] .aa-pg-card p,html[data-aa-public-theme="dark"] .aa-pg-step p{color:#b7c2d4}
        @media(max-width:900px){.aa-pg-hero-grid,.aa-pg-card-grid,.aa-pg-flow{grid-template-columns:1fr}.aa-pg-hero{padding-top:58px}.aa-pg-cover{grid-template-columns:100px minmax(0,1fr)}.aa-pg-tabs{grid-template-columns:repeat(2,minmax(0,1fr))}.aa-pg-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

        body.aa-photographer-feature {
            overflow-x: hidden;
            font-family: "Plus Jakarta Sans", "Manrope", ui-sans-serif, system-ui, sans-serif;
        }

        .aa-photographer-feature main {
            position: relative;
            isolation: isolate;
        }

        .aa-photographer-feature main::before {
            position: absolute;
            inset: 0 0 auto;
            z-index: -1;
            height: 680px;
            background:
                radial-gradient(circle at 18% 12%, rgba(143, 101, 223, .18), transparent 26rem),
                radial-gradient(circle at 82% 10%, rgba(20, 184, 166, .12), transparent 24rem);
            content: "";
            pointer-events: none;
        }

        .aa-pg-shell {
            width: min(1180px, calc(100% - 32px));
        }

        .aa-pg-hero {
            padding: clamp(96px, 10vw, 136px) 0 58px;
        }

        .aa-pg-hero-grid {
            grid-template-columns: minmax(0, 1.04fr) minmax(0, 500px);
            gap: clamp(28px, 4vw, 54px);
        }

        .aa-pg-hero h1 {
            max-width: 790px;
            color: #101827;
            letter-spacing: 0;
        }

        .aa-pg-hero p {
            color: #526179;
        }

        .aa-pg-demo {
            width: 100%;
            max-width: 520px;
            justify-self: end;
            border-color: rgba(143, 101, 223, .18);
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .94), rgba(250, 247, 255, .9));
            box-shadow: 0 28px 80px rgba(58, 38, 118, .16);
        }

        .aa-pg-window {
            border-radius: 22px;
        }

        .aa-pg-window-head {
            background: rgba(248, 250, 252, .86);
        }

        .aa-pg-cover {
            grid-template-columns: 136px minmax(0, 1fr);
            align-items: center;
            gap: 18px;
        }

        .aa-pg-cover-img {
            position: relative;
            height: 172px;
            border: 1px solid rgba(143, 101, 223, .2);
            background:
                linear-gradient(160deg, rgba(255, 255, 255, .26), transparent 42%),
                linear-gradient(135deg, #f8c8df 0%, #eadcff 44%, #b7e7f1 100%);
            box-shadow: inset 0 -42px 0 rgba(255, 255, 255, .32);
        }

        .aa-pg-cover-img::before,
        .aa-pg-cover-img::after {
            position: absolute;
            border-radius: 18px;
            background: rgba(255, 255, 255, .78);
            box-shadow: 0 12px 24px rgba(58, 38, 118, .16);
            content: "";
        }

        .aa-pg-cover-img::before {
            width: 58px;
            height: 78px;
            left: 18px;
            top: 24px;
            transform: rotate(-8deg);
        }

        .aa-pg-cover-img::after {
            width: 62px;
            height: 84px;
            right: 16px;
            top: 34px;
            transform: rotate(8deg);
        }

        .aa-pg-cover-badge {
            position: absolute;
            right: 14px;
            bottom: 14px;
            z-index: 1;
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            border-radius: 999px;
            background: #ffffff;
            color: #7c4fe0;
            padding: 0 11px;
            font-size: 11px;
            font-weight: 950;
            box-shadow: 0 10px 24px rgba(58, 38, 118, .16);
        }

        .aa-pg-mini-title {
            color: #132033;
            letter-spacing: 0;
        }

        .aa-pg-pills {
            align-items: center;
        }

        .aa-pg-pill,
        .aa-pg-tab {
            white-space: nowrap;
        }

        .aa-pg-tabs {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .aa-pg-grid {
            gap: 12px;
        }

        .aa-pg-photo {
            position: relative;
            min-height: 108px;
            border: 1px solid rgba(148, 163, 184, .16);
            overflow: hidden;
        }

        .aa-pg-photo::before {
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: 10px;
            height: 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .78);
            content: "";
        }

        .aa-pg-section {
            padding: clamp(46px, 6vw, 72px) 0;
        }

        .aa-pg-card-grid,
        .aa-pg-flow {
            align-items: stretch;
        }

        .aa-pg-card,
        .aa-pg-step {
            min-width: 0;
        }

        .aa-pg-cta {
            overflow: hidden;
        }

        html[data-aa-public-theme="dark"] body.aa-photographer-feature {
            background:
                radial-gradient(circle at 15% 0%, rgba(143, 101, 223, .18), transparent 28rem),
                linear-gradient(180deg, #07111d 0%, #0a0f1b 54%, #070b12 100%);
        }

        html[data-aa-public-theme="dark"] .aa-pg-hero h1,
        html[data-aa-public-theme="dark"] .aa-pg-section-head h2,
        html[data-aa-public-theme="dark"] .aa-pg-card strong,
        html[data-aa-public-theme="dark"] .aa-pg-step strong,
        html[data-aa-public-theme="dark"] .aa-pg-mini-title {
            color: #f8fafc;
        }

        html[data-aa-public-theme="dark"] .aa-pg-demo,
        html[data-aa-public-theme="dark"] .aa-pg-window,
        html[data-aa-public-theme="dark"] .aa-pg-card,
        html[data-aa-public-theme="dark"] .aa-pg-step {
            border-color: rgba(196, 181, 253, .18);
            background: rgba(15, 23, 42, .86);
            box-shadow: 0 24px 64px rgba(0, 0, 0, .24);
        }

        html[data-aa-public-theme="dark"] .aa-pg-window-head {
            border-color: rgba(196, 181, 253, .14);
            background: rgba(7, 11, 18, .7);
        }

        html[data-aa-public-theme="dark"] .aa-pg-window-title,
        html[data-aa-public-theme="dark"] .aa-pg-mini-meta {
            color: #94a3b8;
        }

        html[data-aa-public-theme="dark"] .aa-pg-photo {
            border-color: rgba(196, 181, 253, .14);
            box-shadow: inset 0 -34px 0 rgba(15, 23, 42, .72);
        }

        @media (max-width: 980px) {
            .aa-pg-hero-grid {
                grid-template-columns: 1fr;
            }

            .aa-pg-demo {
                max-width: 640px;
                justify-self: stretch;
            }

            .aa-pg-card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .aa-pg-shell {
                width: min(100% - 24px, 1180px);
            }

            .aa-pg-hero {
                padding: 82px 0 38px;
            }

            .aa-pg-hero h1 {
                font-size: clamp(34px, 12vw, 48px);
                line-height: 1.02;
            }

            .aa-pg-actions {
                display: grid;
                grid-template-columns: 1fr;
            }

            .aa-pg-action-stack {
                width: 100%;
            }

            .aa-pg-btn {
                width: 100%;
            }

            .aa-pg-cover {
                grid-template-columns: 1fr;
            }

            .aa-pg-cover-img {
                height: 190px;
            }

            .aa-pg-tabs,
            .aa-pg-grid,
            .aa-pg-card-grid,
            .aa-pg-flow {
                grid-template-columns: 1fr;
            }

            .aa-pg-tab {
                text-align: left;
                padding-inline: 14px;
            }

            .aa-pg-photo {
                min-height: 138px;
            }

            .aa-pg-cta {
                margin-bottom: 52px;
                padding: 24px;
            }

            .aa-pg-cta h2 {
                font-size: 28px;
                line-height: 1.12;
            }
        }
    </style>
</head>
<body class="aa-photographer-feature">
    <?= view('components/public_site_header', ['active' => 'photographer-gallery']) ?>
    <main>
        <section class="aa-pg-hero">
            <div class="aa-pg-shell aa-pg-hero-grid">
                <div>
                    <span class="aa-pg-eyebrow">Untuk Usaha Fotografer</span>
                    <h1>Masih pakai Google Drive buat share foto ke klien kamu?</h1>
                    <p>Buat galeri client yang lebih rapi, private, dan profesional. Upload foto, atur album, kasih PIN, lalu biarkan klien memilih foto favorit, foto cetak, dan revisi langsung dari satu link.</p>
                    <div class="aa-pg-actions">
                        <a class="aa-pg-btn aa-pg-btn-primary" href="<?= esc($createUrl, 'attr') ?>">Buat Galeri</a>
                        <span class="aa-pg-action-stack">
                            <a class="aa-pg-btn aa-pg-btn-secondary" href="<?= esc($previewUrl, 'attr') ?>" target="_blank" rel="noopener">Lihat Halaman Klien</a>
                            <span class="aa-pg-preview-hint">*gunakan PIN : 1234</span>
                        </span>
                    </div>
                </div>
                <div class="aa-pg-demo" aria-label="Preview Galeri Klien Fotografer">
                    <div class="aa-pg-window">
                        <div class="aa-pg-window-head"><span class="aa-pg-dot"></span><span class="aa-pg-window-title">studio.adaacara.com/gallery</span></div>
                        <div class="aa-pg-cover">
                            <div class="aa-pg-cover-img" aria-hidden="true"><span class="aa-pg-cover-badge">Private Gallery</span></div>
                            <div>
                                <h2 class="aa-pg-mini-title">Wedding Dimas & Anggi</h2>
                                <p class="aa-pg-mini-meta">Studio AdaAcara · Private PIN</p>
                                <div class="aa-pg-pills"><span class="aa-pg-pill">245 Foto</span><span class="aa-pg-pill">0 / 30 dipilih</span><span class="aa-pg-pill">Download aktif</span></div>
                            </div>
                        </div>
                        <div class="aa-pg-tabs"><span class="aa-pg-tab">Semua</span><span class="aa-pg-tab">Highlight</span><span class="aa-pg-tab">Family</span><span class="aa-pg-tab">Cetak</span></div>
                        <div class="aa-pg-grid"><i class="aa-pg-photo"></i><i class="aa-pg-photo"></i><i class="aa-pg-photo"></i><i class="aa-pg-photo"></i><i class="aa-pg-photo"></i><i class="aa-pg-photo"></i></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="aa-pg-section">
            <div class="aa-pg-shell">
                <div class="aa-pg-section-head">
                    <h2>Satu tempat untuk project, album, pilihan client, dan halaman keluarga.</h2>
                    <p>Fitur ini dibuat khusus untuk alur kerja usaha fotografer setelah sesi pemotretan selesai.</p>
                </div>
                <div class="aa-pg-card-grid">
                    <?php foreach ($features as $feature): ?>
                        <article class="aa-pg-card">
                            <strong><?= esc($feature[0]) ?></strong>
                            <p><?= esc($feature[1]) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="aa-pg-section">
            <div class="aa-pg-shell">
                <div class="aa-pg-section-head">
                    <h2>Flow sederhana untuk kerja fotografer harian.</h2>
                    <p>Dari booking selesai sampai client memilih foto, semua tetap berada di dashboard yang ringan.</p>
                </div>
                <div class="aa-pg-flow">
                    <?php foreach ($steps as $step): ?>
                        <article class="aa-pg-step">
                            <span><?= esc($step[0]) ?></span>
                            <strong><?= esc($step[1]) ?></strong>
                            <p><?= esc($step[2]) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="aa-pg-shell aa-pg-cta">
            <h2>Siapkan galeri klien pertama kamu.</h2>
            <p>Mulai dari satu project client, lalu kembangkan menjadi sistem galeri private untuk wedding, prewedding, family session, wisuda, dan event.</p>
            <div class="aa-pg-actions">
                <a class="aa-pg-btn aa-pg-btn-primary" style="background:#fff;color:#142033" href="<?= esc($createUrl, 'attr') ?>">Buat Galeri</a>
                <span class="aa-pg-action-stack">
                    <a class="aa-pg-btn aa-pg-btn-secondary" href="<?= esc($previewUrl, 'attr') ?>" target="_blank" rel="noopener">Lihat Halaman Klien</a>
                    <span class="aa-pg-preview-hint" style="color:#e9d5ff">*gunakan PIN : 1234</span>
                </span>
            </div>
        </section>
    </main>
    <?= view('components/site_footer') ?>
</body>
</html>
