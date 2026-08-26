<?php
    helper('seo');

    $companyEmail = (string) ($companyEmail ?? 'hello@adaacara.com');
    $mailtoVerify = 'mailto:' . $companyEmail . '?subject=' . rawurlencode('Permintaan Verifikasi Legalitas PT Shagania Labs Indonesia');
    $featureChips = [
        'Editor Visual',
        'Template Siap Edit',
        'Remove BG',
        'AdaAcara AI',
        'Magic Layer',
        'RSVP & Ucapan',
        'Stiker',
        'Publish Website',
        'Public URL',
        'Creator Template',
    ];
    $legalDocuments = is_array($legalDocuments ?? null) ? $legalDocuments : [
        'deed' => ['label' => 'Akta Pendirian', 'path' => '', 'updated_at' => ''],
        'ahu' => ['label' => 'SK Kemenkumham / AHU', 'path' => '', 'updated_at' => ''],
        'nib' => ['label' => 'NIB', 'path' => '', 'updated_at' => ''],
        'npwp' => ['label' => 'NPWP Perusahaan', 'path' => '', 'updated_at' => ''],
        'oss' => ['label' => 'Sertifikat OSS / Perizinan Berusaha', 'path' => '', 'updated_at' => ''],
        'trademark' => ['label' => 'Sertifikat Merek / HAKI', 'path' => '', 'updated_at' => ''],
        'supporting' => ['label' => 'Dokumen pendukung lain', 'path' => '', 'updated_at' => ''],
    ];
    $publicLegalDocuments = array_values(array_filter($legalDocuments, static function ($document): bool {
        return is_array($document) && trim((string) ($document['path'] ?? '')) !== '';
    }));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->title('About Us | PT Shagania Labs Indonesia')
        ->description('PT Shagania Labs Indonesia adalah creative digital company di balik AdaAcara Design Studio, platform visual untuk membuat, mengedit, mem-publish, dan menjual undangan acara digital.')
        ->canonical(site_url('about-us'))
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'About Us', 'url' => site_url('about-us')],
        ])
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <style>
        body.aa-about-page {
            margin: 0;
            background: #f6f9fc;
            color: #101828;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .aa-about-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .aa-about-header {
            position: sticky;
            top: 0;
            z-index: 20;
            border-bottom: 1px solid rgba(16, 24, 40, .08);
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .aa-about-nav {
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .aa-about-logo img {
            display: block;
            width: 128px;
            height: auto;
        }

        .aa-about-nav-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .aa-about-hero {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(360px, 1.05fr);
            gap: 44px;
            align-items: center;
            padding: 72px 0 48px;
        }

        .aa-about-eyebrow {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            border: 1px solid rgba(0, 168, 138, .18);
            border-radius: 999px;
            background: rgba(0, 168, 138, .09);
            color: #007f6a;
            padding: 0 13px;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .aa-about-hero h1,
        .aa-about-section-title {
            margin: 0;
            color: #101828;
            letter-spacing: 0;
        }

        .aa-about-hero h1 {
            max-width: 720px;
            margin-top: 18px;
            font-size: clamp(42px, 6vw, 78px);
            line-height: .95;
        }

        .aa-about-hero p,
        .aa-about-section-copy {
            color: #475467;
            font-weight: 650;
            line-height: 1.7;
        }

        .aa-about-hero p {
            max-width: 650px;
            margin: 20px 0 0;
            font-size: clamp(16px, 2vw, 19px);
        }

        .aa-about-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .aa-about-btn {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0 20px;
            font-size: 14px;
            font-weight: 950;
            text-decoration: none;
            transition: transform .18s ease, border-color .18s ease, background .18s ease;
        }

        .aa-about-btn:hover {
            transform: translateY(-1px);
        }

        .aa-about-btn-primary {
            border: 1px solid #00a88a;
            background: #00a88a;
            color: #ffffff;
            box-shadow: 0 18px 38px rgba(0, 168, 138, .22);
        }

        .aa-about-btn-secondary {
            border: 1px solid rgba(16, 24, 40, .14);
            background: #ffffff;
            color: #101828;
        }

        .aa-about-visual {
            position: relative;
            min-height: 520px;
        }

        .aa-about-editor-mockup {
            overflow: hidden;
            border: 1px solid rgba(16, 24, 40, .1);
            border-radius: 30px;
            background: #ffffff;
            box-shadow: 0 30px 80px rgba(16, 24, 40, .16);
        }

        .aa-about-editor-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid rgba(16, 24, 40, .08);
            padding: 14px 16px;
        }

        .aa-about-dots {
            display: flex;
            gap: 7px;
        }

        .aa-about-dots span {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #e4e7ec;
        }

        .aa-about-publish {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            border-radius: 999px;
            background: #101828;
            color: #ffffff;
            padding: 0 13px;
            font-size: 12px;
            font-weight: 950;
        }

        .aa-about-editor-body {
            display: grid;
            grid-template-columns: 142px minmax(0, 1fr);
            min-height: 392px;
        }

        .aa-about-sidebar {
            display: grid;
            align-content: start;
            gap: 10px;
            border-right: 1px solid rgba(16, 24, 40, .08);
            background: #fff8ea;
            padding: 16px;
        }

        .aa-about-sidebar span {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, .82);
            color: #667085;
            padding: 0 11px;
            font-size: 12px;
            font-weight: 900;
        }

        .aa-about-canvas {
            display: grid;
            gap: 14px;
            background: #f6f9fc;
            padding: 18px;
        }

        .aa-about-preview {
            overflow: hidden;
            min-height: 254px;
            border: 1px solid rgba(16, 24, 40, .08);
            border-radius: 22px;
            background: #ffffff;
        }

        .aa-about-preview img {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 254px;
            object-fit: cover;
            object-position: center top;
        }

        .aa-about-ai-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .aa-about-ai-card {
            border: 1px solid rgba(0, 168, 138, .15);
            border-radius: 18px;
            background: #ffffff;
            padding: 13px;
            color: #344054;
            font-size: 12px;
            font-weight: 900;
        }

        .aa-about-ai-card strong {
            display: block;
            margin-bottom: 5px;
            color: #00a88a;
            font-size: 13px;
        }

        .aa-about-mascot {
            position: absolute;
            right: -18px;
            bottom: -18px;
            width: min(170px, 30vw);
            filter: drop-shadow(0 18px 28px rgba(16, 24, 40, .16));
        }

        .aa-about-section {
            padding: 44px 0;
        }

        .aa-about-panel {
            border: 1px solid rgba(16, 24, 40, .08);
            border-radius: 30px;
            background: rgba(255, 255, 255, .9);
            padding: clamp(24px, 4vw, 44px);
            box-shadow: 0 18px 50px rgba(16, 24, 40, .06);
        }

        .aa-about-section-title {
            font-size: clamp(30px, 4vw, 48px);
            line-height: 1.04;
        }

        .aa-about-section-copy {
            max-width: 780px;
            margin: 14px 0 0;
            font-size: 16px;
        }

        .aa-about-product {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 30px;
            align-items: start;
        }

        .aa-about-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .aa-about-chip {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            border: 1px solid rgba(0, 168, 138, .18);
            border-radius: 999px;
            background: rgba(0, 168, 138, .08);
            color: #006b5a;
            padding: 0 13px;
            font-size: 13px;
            font-weight: 900;
        }

        .aa-about-creator-grid,
        .aa-about-build-grid,
        .aa-about-doc-grid,
        .aa-about-trust-grid {
            display: grid;
            gap: 14px;
        }

        .aa-about-creator-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-top: 24px;
        }

        .aa-about-build-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-top: 24px;
        }

        .aa-about-doc-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 24px;
        }

        .aa-about-trust-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .aa-about-mini-card,
        .aa-about-doc-card,
        .aa-about-trust-card {
            border: 1px solid rgba(16, 24, 40, .08);
            border-radius: 22px;
            background: #ffffff;
            padding: 18px;
        }

        .aa-about-mini-card strong,
        .aa-about-doc-card strong,
        .aa-about-trust-card strong {
            display: block;
            color: #101828;
            font-size: 15px;
            font-weight: 950;
        }

        .aa-about-mini-card p,
        .aa-about-doc-card p,
        .aa-about-trust-card p {
            margin: 8px 0 0;
            color: #667085;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.55;
        }

        .aa-about-mini-icon {
            display: inline-flex;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #fff8ea;
            color: #c8872d;
            margin-bottom: 14px;
        }

        .aa-about-doc-card {
            display: grid;
            gap: 14px;
        }

        .aa-about-doc-status {
            display: inline-flex;
            width: fit-content;
            min-height: 28px;
            align-items: center;
            border-radius: 999px;
            background: #f6f9fc;
            color: #667085;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 900;
        }

        .aa-about-doc-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .aa-about-doc-link {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(16, 24, 40, .12);
            padding: 0 13px;
            color: #101828;
            font-size: 12px;
            font-weight: 950;
            text-decoration: none;
        }

        .aa-about-doc-link.is-disabled {
            color: #98a2b3;
            cursor: not-allowed;
        }

        .aa-about-doc-link.is-primary {
            border-color: rgba(0, 168, 138, .24);
            background: rgba(0, 168, 138, .08);
            color: #007f6a;
        }

        button.aa-about-doc-link {
            cursor: pointer;
            font: inherit;
        }

        .aa-legal-document-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(2, 6, 23, .86);
        }

        .aa-legal-document-modal.is-open {
            display: flex;
        }

        .aa-legal-document-modal img {
            display: block;
            max-width: 94vw;
            max-height: min(88vh, 900px);
            object-fit: contain;
            box-shadow: 0 26px 90px rgba(0, 0, 0, .38);
        }

        .aa-legal-document-close {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 81;
            display: inline-flex;
            width: 46px;
            height: 46px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 999px;
            background: rgba(15, 23, 42, .72);
            color: #ffffff;
            cursor: pointer;
            font-size: 24px;
            font-weight: 500;
            line-height: 1;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .aa-about-trust-strip {
            padding: 28px 0 8px;
        }

        .aa-about-contact {
            margin: 44px 0 70px;
            background: #101828;
            color: #ffffff;
        }

        .aa-about-contact .aa-about-section-title,
        .aa-about-contact .aa-about-mini-card strong {
            color: #ffffff;
        }

        .aa-about-contact .aa-about-section-copy {
            color: #cbd5e1;
        }

        @media (max-width: 980px) {
            .aa-about-hero,
            .aa-about-product {
                grid-template-columns: 1fr;
            }

            .aa-about-visual {
                min-height: auto;
            }

            .aa-about-creator-grid,
            .aa-about-build-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .aa-about-shell {
                width: min(100% - 22px, 1180px);
            }

            .aa-about-hero {
                padding-top: 46px;
                gap: 28px;
            }

            .aa-about-editor-body {
                grid-template-columns: 1fr;
            }

            .aa-about-sidebar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                border-right: 0;
                border-bottom: 1px solid rgba(16, 24, 40, .08);
            }

            .aa-about-ai-grid,
            .aa-about-creator-grid,
            .aa-about-build-grid,
            .aa-about-doc-grid,
            .aa-about-trust-grid {
                grid-template-columns: 1fr;
            }

            .aa-about-panel {
                border-radius: 24px;
            }

            .aa-about-mascot {
                right: 6px;
                bottom: -26px;
                width: 112px;
            }
        }

        html[data-aa-public-theme="dark"] body.aa-about-page {
            background: #071018 !important;
            color: #e5edf6 !important;
        }

        html[data-aa-public-theme="dark"] .aa-about-header {
            border-bottom-color: rgba(148, 163, 184, .18);
            background: rgba(7, 16, 24, .86);
        }

        html[data-aa-public-theme="dark"] .aa-about-hero h1,
        html[data-aa-public-theme="dark"] .aa-about-section-title,
        html[data-aa-public-theme="dark"] .aa-about-mini-card strong,
        html[data-aa-public-theme="dark"] .aa-about-doc-card strong,
        html[data-aa-public-theme="dark"] .aa-about-trust-card strong {
            color: #f8fafc;
        }

        html[data-aa-public-theme="dark"] .aa-about-hero p,
        html[data-aa-public-theme="dark"] .aa-about-section-copy,
        html[data-aa-public-theme="dark"] .aa-about-mini-card p,
        html[data-aa-public-theme="dark"] .aa-about-doc-card p,
        html[data-aa-public-theme="dark"] .aa-about-trust-card p {
            color: #a8b5c7;
        }

        html[data-aa-public-theme="dark"] .aa-about-btn-secondary,
        html[data-aa-public-theme="dark"] .aa-about-editor-mockup,
        html[data-aa-public-theme="dark"] .aa-about-panel,
        html[data-aa-public-theme="dark"] .aa-about-ai-card,
        html[data-aa-public-theme="dark"] .aa-about-mini-card,
        html[data-aa-public-theme="dark"] .aa-about-doc-card,
        html[data-aa-public-theme="dark"] .aa-about-trust-card {
            border-color: rgba(148, 163, 184, .18);
            background: rgba(15, 23, 42, .9);
            color: #e5edf6;
        }

        html[data-aa-public-theme="dark"] .aa-about-sidebar {
            border-color: rgba(148, 163, 184, .18);
            background: rgba(120, 53, 15, .18);
        }

        html[data-aa-public-theme="dark"] .aa-about-sidebar span,
        html[data-aa-public-theme="dark"] .aa-about-preview,
        html[data-aa-public-theme="dark"] .aa-about-canvas,
        html[data-aa-public-theme="dark"] .aa-about-doc-status {
            border-color: rgba(148, 163, 184, .18);
            background: rgba(30, 41, 59, .82);
            color: #cbd5e1;
        }

        html[data-aa-public-theme="dark"] .aa-about-editor-top {
            border-bottom-color: rgba(148, 163, 184, .18);
        }

        html[data-aa-public-theme="dark"] .aa-about-chip {
            border-color: rgba(45, 212, 191, .28);
            background: rgba(20, 184, 166, .13);
            color: #99f6e4;
        }

        html[data-aa-public-theme="dark"] .aa-about-doc-link {
            border-color: rgba(148, 163, 184, .24);
            color: #e5edf6;
        }

        html[data-aa-public-theme="dark"] .aa-about-doc-link.is-disabled {
            color: #64748b;
        }

        html[data-aa-public-theme="dark"] .aa-about-doc-link.is-primary {
            border-color: rgba(45, 212, 191, .3);
            background: rgba(20, 184, 166, .14);
            color: #99f6e4;
        }
    </style>
</head>
<body class="aa-app-ui aa-public-theme-page aa-about-page">
    <header class="aa-about-header">
        <div class="aa-about-shell aa-about-nav">
            <a class="aa-about-logo" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                <img class="aa-public-logo" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            </a>
            <div class="aa-about-nav-actions">
                <?= view('components/public_theme_toggle') ?>
                <?= view('components/user_nav_dropdown', ['active' => '']) ?>
            </div>
        </div>
    </header>

    <main>
        <section class="aa-about-shell aa-about-hero">
            <div>
                <span class="aa-about-eyebrow">PT Shagania Labs Indonesia</span>
                <h1>Creative digital lab di balik AdaAcara.</h1>
                <p>PT Shagania Labs Indonesia membangun design studio modern untuk membantu siapa pun membuat, mengedit, mem-publish, dan menjual undangan acara digital dengan lebih mudah.</p>
                <div class="aa-about-actions">
                    <a class="aa-about-btn aa-about-btn-primary" href="#adaacara">Kenali AdaAcara</a>
                    <a class="aa-about-btn aa-about-btn-secondary" href="#legalitas">Lihat Legalitas</a>
                </div>
            </div>
            <div class="aa-about-visual" aria-label="Mockup editor AdaAcara">
                <div class="aa-about-editor-mockup">
                    <div class="aa-about-editor-top">
                        <div class="aa-about-dots" aria-hidden="true"><span></span><span></span><span></span></div>
                        <strong>AdaAcara Design Studio</strong>
                        <span class="aa-about-publish">Publish</span>
                    </div>
                    <div class="aa-about-editor-body">
                        <aside class="aa-about-sidebar" aria-label="Panel editor">
                            <span>Templates</span>
                            <span>Ornaments</span>
                            <span>Elements</span>
                            <span>Cover</span>
                            <span>Assets</span>
                        </aside>
                        <div class="aa-about-canvas">
                            <div class="aa-about-preview">
                                <img src="<?= aa_asset_url('assets/img/adaacara-design-studio-preview.png') ?>" alt="Preview visual AdaAcara Design Studio" loading="eager">
                            </div>
                            <div class="aa-about-ai-grid" aria-label="Fitur AI">
                                <div class="aa-about-ai-card"><strong>Remove BG</strong>Foto lebih rapi.</div>
                                <div class="aa-about-ai-card"><strong>AdaAcara AI</strong>Bantu ide desain.</div>
                                <div class="aa-about-ai-card"><strong>Magic Layer</strong>Edit layer cepat.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <img class="aa-about-mascot" src="<?= aa_asset_url('assets/img/2.png') ?>" alt="Maskot kuning AdaAcara" loading="lazy">
            </div>
        </section>

        <section class="aa-about-shell aa-about-section">
            <div class="aa-about-panel">
                <h2 class="aa-about-section-title">Kami membuat teknologi kreatif terasa lebih mudah.</h2>
                <p class="aa-about-section-copy">Shagania Labs menggabungkan desain, teknologi, dan otomasi untuk membangun produk digital yang membantu pengguna menciptakan pengalaman online yang lebih visual, cepat, dan bernilai.</p>
            </div>
        </section>

        <section id="adaacara" class="aa-about-shell aa-about-section">
            <div class="aa-about-panel aa-about-product">
                <div>
                    <span class="aa-about-eyebrow">Product</span>
                    <h2 class="aa-about-section-title">AdaAcara Design Studio</h2>
                    <p class="aa-about-section-copy">Platform editor visual untuk membuat undangan dan website acara. Mulai dari desain kosong, edit template, tambah elemen, pakai AI tools, lalu publish ke URL publik.</p>
                    <div class="aa-about-actions">
                        <a class="aa-about-btn aa-about-btn-primary" href="<?= site_url('templates') ?>">Mulai Desain</a>
                        <a class="aa-about-btn aa-about-btn-secondary" href="<?= site_url('templates') ?>">Lihat Template</a>
                    </div>
                </div>
                <div class="aa-about-chip-list" aria-label="Fitur AdaAcara">
                    <?php foreach ($featureChips as $chip): ?>
                        <span class="aa-about-chip"><?= esc($chip) ?></span>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <section class="aa-about-shell aa-about-section">
            <div class="aa-about-panel">
                <span class="aa-about-eyebrow">Creator Opportunity</span>
                <h2 class="aa-about-section-title">Bukan hanya membuat. Kamu juga bisa menjual.</h2>
                <p class="aa-about-section-copy">AdaAcara membuka peluang untuk kreator yang ingin membuat template undangan acara dan menjualnya sebagai produk digital.</p>
                <div class="aa-about-creator-grid">
                    <div class="aa-about-mini-card"><span class="aa-about-mini-icon">0%</span><strong>0% modal awal</strong><p>Mulai dari skill desain dan ide kreatif.</p></div>
                    <div class="aa-about-mini-card"><span class="aa-about-mini-icon">IP</span><strong>Hak cipta kreator</strong><p>Desain tetap menjadi karya milik kreator.</p></div>
                    <div class="aa-about-mini-card"><span class="aa-about-mini-icon">Go</span><strong>Template bisa dijual</strong><p>Ubah desain menjadi produk digital siap pakai.</p></div>
                    <div class="aa-about-mini-card"><span class="aa-about-mini-icon">All</span><strong>Banyak kategori acara</strong><p>Wedding, aqiqah, ulang tahun, seminar, dan lainnya.</p></div>
                </div>
            </div>
        </section>

        <section class="aa-about-shell aa-about-section">
            <h2 class="aa-about-section-title">What We Build</h2>
            <div class="aa-about-build-grid">
                <div class="aa-about-mini-card"><strong>Creative Design Studio</strong><p>Ruang kerja visual untuk membuat desain acara dari nol.</p></div>
                <div class="aa-about-mini-card"><strong>AI-assisted Tools</strong><p>Tools kreatif yang membantu proses edit jadi lebih cepat.</p></div>
                <div class="aa-about-mini-card"><strong>Digital Event Website</strong><p>Publish undangan menjadi website acara dengan URL publik.</p></div>
                <div class="aa-about-mini-card"><strong>Creator Template Ecosystem</strong><p>Tempat kreator membuat dan menjual template acara.</p></div>
            </div>
        </section>

        <section id="legalitas" class="aa-about-shell aa-about-section">
            <div class="aa-about-panel">
                <span class="aa-about-eyebrow">Legalitas</span>
                <h2 class="aa-about-section-title">Legalitas Perusahaan</h2>
                <p class="aa-about-section-copy">Transparansi adalah bagian dari cara kami membangun kepercayaan. Informasi legalitas PT Shagania Labs Indonesia tersedia untuk kebutuhan verifikasi, kemitraan, dan kerja sama resmi.</p>
                <?php if ($publicLegalDocuments !== []): ?>
                <div class="aa-about-doc-grid">
                    <?php foreach ($publicLegalDocuments as $document): ?>
                        <?php
                            $docLabel = (string) ($document['label'] ?? '');
                            $docPath = (string) ($document['path'] ?? '');
                            $docUrl = base_url($docPath);
                        ?>
                        <article class="aa-about-doc-card">
                            <div>
                                <strong><?= esc($docLabel) ?></strong>
                                <p>Data sensitif tidak ditampilkan publik.</p>
                            </div>
                            <span class="aa-about-doc-status">Dokumen publik tersedia</span>
                            <div class="aa-about-doc-actions">
                                <button class="aa-about-doc-link" type="button" data-aa-legal-document="<?= esc($docUrl, 'attr') ?>" data-aa-legal-title="<?= esc($docLabel, 'attr') ?>">Lihat Dokumen</button>
                                <a class="aa-about-doc-link is-primary" href="<?= esc($mailtoVerify, 'attr') ?>">Minta Verifikasi</a>
                            </div>
                        </article>
                    <?php endforeach ?>
                </div>
                <?php endif ?>
            </div>
        </section>

        <section class="aa-about-shell aa-about-trust-strip">
            <div class="aa-about-trust-grid">
                <div class="aa-about-trust-card"><strong>Legal company</strong><p>Dibangun oleh PT Shagania Labs Indonesia.</p></div>
                <div class="aa-about-trust-card"><strong>Creative digital product</strong><p>Fokus pada produk kreatif visual untuk event online.</p></div>
                <div class="aa-about-trust-card"><strong>AI-assisted workflow</strong><p>Mendukung proses desain dengan tools AI yang praktis.</p></div>
            </div>
        </section>

        <section class="aa-about-shell">
            <div class="aa-about-panel aa-about-contact">
                <h2 class="aa-about-section-title">Mau verifikasi atau bekerja sama?</h2>
                <p class="aa-about-section-copy">Hubungi PT Shagania Labs Indonesia untuk kebutuhan legalitas, kolaborasi, kemitraan, atau pengembangan produk digital.</p>
                <div class="aa-about-actions">
                    <a class="aa-about-btn aa-about-btn-primary" href="<?= esc($mailtoVerify, 'attr') ?>">Hubungi Kami</a>
                    <a class="aa-about-btn aa-about-btn-secondary" href="mailto:<?= esc($companyEmail, 'attr') ?>">Email Perusahaan</a>
                </div>
            </div>
        </section>
    </main>

    <div class="aa-legal-document-modal" data-aa-legal-modal aria-hidden="true">
        <button class="aa-legal-document-close" type="button" data-aa-legal-close aria-label="Tutup dokumen">⛌</button>
        <img data-aa-legal-image src="" alt="">
    </div>

    <?= view('components/site_footer') ?>
    <script>
        (function () {
            const modal = document.querySelector('[data-aa-legal-modal]');
            const image = document.querySelector('[data-aa-legal-image]');
            const close = document.querySelector('[data-aa-legal-close]');
            if (!modal || !image || !close) return;

            const closeModal = function () {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                image.removeAttribute('src');
                image.removeAttribute('alt');
            };

            document.querySelectorAll('[data-aa-legal-document]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const src = button.getAttribute('data-aa-legal-document') || '';
                    if (!src) return;
                    image.src = src;
                    image.alt = button.getAttribute('data-aa-legal-title') || 'Dokumen legal perusahaan';
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                });
            });

            close.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });
        })();
    </script>
</body>
</html>
