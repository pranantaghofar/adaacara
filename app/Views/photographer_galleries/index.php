<?php
    helper(['url', 'aa_asset']);
    $title = $title ?? 'Photographer Gallery';
    $galleries = is_array($galleries ?? null) ? $galleries : [];
    $isReady = ! empty($isReady);
    $userName = (string) ($userName ?? 'User');
    $firstName = trim(explode(' ', $userName)[0] ?? 'User');
    $flashSuccess = session('success');
    $flashError = session('error');
    $icon = static function (string $name, string $class = 'h-5 w-5'): string {
        $icons = [
            'dashboard' => '<path d="M4 13h7V4H4v9Zm9 7h7V4h-7v16ZM4 20h7v-5H4v5Z"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'camera' => '<path d="M4 8a2 2 0 0 1 2-2h2l1.5-2h5L16 6h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z"/><circle cx="12" cy="13" r="4"/>',
            'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'image' => '<rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 16-5-5L5 19"/>',
            'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
            'logout' => '<path d="M14 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/><path d="M9 12h12m0 0-3-3m3 3-3 3"/>',
        ];

        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['camera']) . '</svg>';
    };
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> - AdaAcara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *,*::before,*::after{box-sizing:border-box}
        html{min-height:100%}
        body{margin:0;min-height:100vh;overflow-x:hidden}
        a{color:inherit}
        button,input,select,textarea{font:inherit}
        .pg-shell svg{display:block;flex:0 0 auto;width:20px;height:20px}
        .pg-shell .h-3\.5{width:14px;height:14px}.pg-shell .w-3\.5{width:14px}.pg-shell .h-4{width:16px;height:16px}.pg-shell .w-4{width:16px}.pg-shell .h-5{width:20px;height:20px}.pg-shell .w-5{width:20px}.pg-shell .h-7{width:28px;height:28px}.pg-shell .w-7{width:28px}.pg-shell .h-12{width:48px;height:48px}.pg-shell .w-12{width:48px}
        .pg-shell{display:grid;grid-template-columns:280px minmax(0,1fr);min-height:100vh;background:linear-gradient(135deg,#f8fbff 0%,#fff7fb 48%,#f4fffb 100%);color:#172033}
        .pg-sidebar{border-right:1px solid rgba(148,163,184,.22);background:rgba(255,255,255,.72);backdrop-filter:blur(18px);padding:24px}
        .pg-logo{display:inline-flex;align-items:center;width:124px;max-width:100%;overflow:hidden}
        .pg-logo img{display:block;width:124px;height:auto;max-width:124px;object-fit:contain}
        .pg-profile{margin-top:32px;border:1px solid rgba(255,255,255,.7);border-radius:24px;background:rgba(255,255,255,.72);padding:16px;box-shadow:0 10px 24px rgba(79,70,229,.06)}
        .pg-profile-title{margin:0;font-size:14px;font-weight:900;color:#49365f}
        .pg-profile-text{margin:6px 0 0;font-size:12px;font-weight:700;line-height:1.55;color:#7e728b}
        .pg-nav{display:grid;gap:10px;margin-top:20px}
        .pg-main{padding:24px}
        .pg-content{width:min(100%,1240px);margin:0 auto}
        .pg-card{border:1px solid rgba(148,163,184,.22);background:rgba(255,255,255,.84);box-shadow:0 18px 50px rgba(79,70,229,.08)}
        .pg-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;border-radius:28px;padding:28px}
        .pg-eyebrow{margin:0;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.16em;color:#7c4fd3}
        .pg-title{margin:10px 0 0;font-size:34px;line-height:1.1;font-weight:950;letter-spacing:-.02em;color:#142033}
        .pg-subtitle{margin:12px 0 0;max-width:680px;font-size:14px;font-weight:700;line-height:1.65;color:#5b6880}
        .pg-btn{display:inline-flex;min-height:44px;align-items:center;justify-content:center;gap:10px;border-radius:16px;padding:10px 18px;font-size:14px;font-weight:900;line-height:1.15;text-decoration:none;transition:.18s ease}
        .pg-btn svg{width:18px;height:18px;flex:0 0 auto}
        .pg-nav .pg-btn{justify-content:flex-start;width:100%;box-sizing:border-box}
        .pg-btn-primary{background:#8f65df;color:white;box-shadow:0 14px 32px rgba(143,101,223,.22)}
        .pg-btn-muted{border:1px solid rgba(148,163,184,.28);background:rgba(255,255,255,.74);color:#46556f}
        .pg-alert{margin-top:16px;border-radius:18px;padding:13px 16px;font-size:14px;font-weight:800}
        .pg-alert-success{border:1px solid #a7f3d0;background:#ecfdf5;color:#047857}
        .pg-alert-error{border:1px solid #fecdd3;background:#fff1f2;color:#be123c}
        .pg-setup{margin-top:24px;border-radius:28px;padding:32px}
        .pg-setup-title{margin:8px 0 0;font-size:24px;line-height:1.2;font-weight:950}
        .pg-setup-text{margin:10px 0 0;font-size:14px;font-weight:700;line-height:1.65;color:#5b6880}
        .pg-empty{display:grid;min-height:360px;place-items:center;margin-top:24px;border-radius:28px;padding:32px;text-align:center}
        .pg-empty-icon{display:grid;width:64px;height:64px;margin:0 auto;place-items:center;border-radius:24px;background:#ede9fe;color:#7c3aed}
        .pg-empty h2{margin:20px 0 0;font-size:24px;line-height:1.2;font-weight:950}
        .pg-empty p{margin:10px auto 0;max-width:420px;font-size:14px;font-weight:700;line-height:1.65;color:#5b6880}
        .pg-gallery-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin-top:24px}
        .pg-gallery-card{overflow:hidden;border-radius:28px}
        .pg-cover{height:176px;background:#f1f5f9}
        .pg-cover img{width:100%;height:100%;object-fit:cover}
        .pg-cover-empty{display:grid;height:100%;place-items:center;background:linear-gradient(135deg,#f5f3ff,#fff,#fff1f2);color:#c4b5fd}
        .pg-cover-empty svg{width:48px;height:48px}
        .pg-card-body{padding:20px}
        .pg-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
        .pg-card-name{min-width:0}
        .pg-card-name h2{margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:18px;font-weight:950}
        .pg-card-name p{margin:6px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;font-weight:800;color:#64748b}
        .pg-badge{display:inline-flex;flex:0 0 auto;align-items:center;gap:5px;border-radius:999px;background:#f1f5f9;padding:6px 10px;font-size:12px;font-weight:950;color:#475569}
        .pg-badge svg{width:14px;height:14px}
        .pg-stat-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-top:20px;text-align:center;font-size:12px;font-weight:950;color:#64748b}
        .pg-stat{border-radius:18px;background:rgba(255,255,255,.7);padding:12px 8px}
        .pg-stat strong{display:block;font-size:18px;color:#142033}
        .pg-card-action{width:100%;margin-top:20px;box-sizing:border-box}
        html[data-aa-public-theme="dark"] .pg-shell{background:linear-gradient(135deg,#08111f 0%,#17111d 52%,#071b17 100%);color:#edf2f7}
        html[data-aa-public-theme="dark"] .pg-sidebar,html[data-aa-public-theme="dark"] .pg-card{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.76)}
        html[data-aa-public-theme="dark"] .pg-btn-muted{background:rgba(15,23,42,.6);color:#d8e0ec}
        html[data-aa-public-theme="dark"] .pg-title,html[data-aa-public-theme="dark"] .pg-card-name h2,html[data-aa-public-theme="dark"] .pg-stat strong{color:#edf2f7}
        html[data-aa-public-theme="dark"] .pg-profile,html[data-aa-public-theme="dark"] .pg-stat{background:rgba(255,255,255,.06);border-color:rgba(148,163,184,.18)}
        @media(max-width:1100px){.pg-gallery-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:900px){.pg-shell{display:block}.pg-sidebar{position:static}.pg-main{padding:16px}.pg-hero{display:block}.pg-hero .pg-btn{margin-top:20px}.pg-gallery-grid{grid-template-columns:1fr}}
    </style>
</head>
<body class="aa-app-ui aa-dashboard-theme-page aa-dashboard-pastel min-h-screen antialiased">
<div class="pg-shell">
    <aside class="pg-sidebar">
        <a class="pg-logo" href="<?= site_url('dashboard') ?>" aria-label="AdaAcara Dashboard">
            <img src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara">
        </a>
        <div class="pg-profile">
            <p class="pg-profile-title">Hai, <?= esc($firstName) ?>!</p>
            <p class="pg-profile-text">Kelola delivery foto klien dengan rapi.</p>
        </div>
        <nav class="pg-nav" aria-label="Menu Photographer Gallery">
            <a class="pg-btn pg-btn-muted justify-start" href="<?= site_url('dashboard') ?>"><?= $icon('dashboard', 'h-4 w-4') ?>Dashboard</a>
            <a class="pg-btn pg-btn-primary justify-start" href="<?= site_url('photographer-galleries') ?>"><?= $icon('camera', 'h-4 w-4') ?>Photo Gallery</a>
            <a class="pg-btn pg-btn-muted justify-start" href="<?= site_url('templates') ?>"><?= $icon('plus', 'h-4 w-4') ?>Buat Project Baru</a>
        </nav>
        <form style="margin-top:20px" action="<?= site_url('logout') ?>" method="post">
            <?= csrf_field() ?>
            <button class="pg-btn pg-btn-muted w-full justify-start" type="submit"><?= $icon('logout', 'h-4 w-4') ?>Logout</button>
        </form>
    </aside>

    <main class="pg-main">
        <div class="pg-content">
            <header class="pg-card pg-hero">
                <div>
                    <p class="pg-eyebrow">Photographer Admin</p>
                    <h1 class="pg-title">Client Photo Gallery</h1>
                    <p class="pg-subtitle">Buat gallery klien, atur privacy, pilihan foto, dan upload foto dalam satu dashboard ringan.</p>
                </div>
                <a class="pg-btn pg-btn-primary" href="<?= site_url('photographer-galleries/create') ?>"><?= $icon('plus', 'h-4 w-4') ?>Create Gallery</a>
            </header>

            <?php if (session('success')): ?>
                <div class="pg-alert pg-alert-success" data-pg-inline-flash><?= esc(session('success')) ?></div>
            <?php endif; ?>
            <?php if (session('error')): ?>
                <div class="pg-alert pg-alert-error" data-pg-inline-flash><?= esc(session('error')) ?></div>
            <?php endif; ?>

            <?php if (! $isReady): ?>
                <section class="pg-card pg-setup">
                    <p class="pg-eyebrow" style="color:#e11d48">Database belum siap</p>
                    <h2 class="pg-setup-title">Jalankan SQL Photographer Gallery dahulu.</h2>
                    <p class="pg-setup-text">File yang perlu dijalankan: <code>database/alter_photographer_galleries.sql</code>. Halaman ini sengaja tidak membuat tabel otomatis agar aman di hosting.</p>
                </section>
            <?php elseif ($galleries === []): ?>
                <section class="pg-card pg-empty">
                    <div>
                        <span class="pg-empty-icon"><?= $icon('image', 'h-7 w-7') ?></span>
                        <h2>Belum ada gallery.</h2>
                        <p>Mulai dari satu project klien, lalu upload foto setelah gallery dibuat.</p>
                        <a class="pg-btn pg-btn-primary" style="margin-top:20px" href="<?= site_url('photographer-galleries/create') ?>"><?= $icon('plus', 'h-4 w-4') ?>Create Gallery</a>
                    </div>
                </section>
            <?php else: ?>
                <section class="pg-gallery-grid">
                    <?php foreach ($galleries as $gallery): ?>
                        <?php
                            $cover = trim((string) ($gallery['cover_photo'] ?? ''));
                            $coverUrl = $cover !== '' ? base_url($cover) : '';
                            $isPin = (string) ($gallery['privacy_mode'] ?? 'pin') === 'pin';
                        ?>
                        <article class="pg-card pg-gallery-card">
                            <div class="pg-cover">
                                <?php if ($coverUrl !== ''): ?>
                                    <img src="<?= esc($coverUrl, 'attr') ?>" alt="<?= esc((string) ($gallery['title'] ?? 'Gallery'), 'attr') ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="pg-cover-empty"><?= $icon('camera', 'h-12 w-12') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="pg-card-body">
                                <div class="pg-card-head">
                                    <div class="pg-card-name">
                                        <h2><?= esc((string) ($gallery['title'] ?? 'Gallery')) ?></h2>
                                        <p><?= esc((string) ($gallery['studio_name'] ?? 'Studio')) ?></p>
                                    </div>
                                    <span class="pg-badge"><?= $isPin ? $icon('lock', 'h-3.5 w-3.5') : '' ?><?= $isPin ? 'PIN' : 'Public' ?></span>
                                </div>
                                <div class="pg-stat-grid">
                                    <div class="pg-stat"><strong><?= (int) ($gallery['photo_count'] ?? 0) ?></strong>Foto</div>
                                    <div class="pg-stat"><strong><?= ! empty($gallery['selection_enabled']) ? (int) ($gallery['selection_limit'] ?? 30) : 0 ?></strong>Pilihan</div>
                                    <div class="pg-stat"><strong><?= ! empty($gallery['download_enabled']) ? 'On' : 'Off' ?></strong>Download</div>
                                </div>
                                <a class="pg-btn pg-btn-primary pg-card-action" href="<?= site_url('photographer-galleries/' . (int) $gallery['id']) ?>">Kelola Gallery<?= $icon('arrow', 'h-4 w-4') ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
(function () {
    const flashSuccess = <?= json_encode($flashSuccess ?: '') ?>;
    const flashError = <?= json_encode($flashError ?: '') ?>;
    const toast = (message, tone) => {
        if (!message || typeof window.aaToast !== 'function') return;
        window.aaToast(message, tone);
        document.querySelectorAll('[data-pg-inline-flash]').forEach((el) => {
            el.style.display = 'none';
        });
    };
    toast(flashSuccess, 'success');
    toast(flashError, 'error');
})();
</script>
</body>
</html>
