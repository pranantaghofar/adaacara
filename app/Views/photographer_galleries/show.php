<?php
    helper(['url', 'form', 'aa_asset']);
    $gallery = is_array($gallery ?? null) ? $gallery : [];
    $photos = is_array($photos ?? null) ? $photos : [];
    $photoCount = (int) ($photoCount ?? count($photos));
    $cover = trim((string) ($gallery['cover_photo'] ?? ''));
    $coverUrl = $cover !== '' ? base_url($cover) : '';
    $privacyMode = (string) ($gallery['privacy_mode'] ?? 'pin');
    $hasActivePin = $privacyMode === 'pin' && trim((string) ($gallery['pin_hash'] ?? '')) !== '';
    $errors = session('errors') ?? [];
    $albums = is_array($albums ?? null) ? $albums : [];
    $albumsReady = ! empty($albumsReady);
    $photoStatuses = is_array($photoStatuses ?? null) ? $photoStatuses : ['uploaded', 'hidden', 'selected', 'delivered'];
    $printSelections = is_array($printSelections ?? null) ? $printSelections : [];
    $comments = is_array($comments ?? null) ? $comments : [];
    $commentsReady = ! empty($commentsReady);
    $statusLabels = [
        'visible' => 'Uploaded',
        'uploaded' => 'Uploaded',
        'hidden' => 'Hidden',
        'selected' => 'Selected',
        'delivered' => 'Delivered',
    ];
    $uploadUrl = site_url('photographer-galleries/' . (int) ($gallery['id'] ?? 0) . '/photos');
    $settingsUrl = site_url('photographer-galleries/' . (int) ($gallery['id'] ?? 0) . '/settings');
    $bulkDeleteUrl = site_url('photographer-galleries/' . (int) ($gallery['id'] ?? 0) . '/photos/delete-selected');
    $customerUrl = site_url('gallery/' . (string) ($gallery['slug'] ?? ''));
    $albumCreateUrl = site_url('photographer-galleries/' . (int) ($gallery['id'] ?? 0) . '/albums');
    $oldPin = preg_replace('/\D+/', '', (string) old('pin', ''));
    $flashSuccess = session('success');
    $flashError = session('error');
    $icon = static function (string $name, string $class = 'h-5 w-5'): string {
        $icons = [
            'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
            'upload' => '<path d="M12 3v12m0-12 4 4m-4-4-4 4"/><path d="M4 21h16"/>',
            'camera' => '<path d="M4 8a2 2 0 0 1 2-2h2l1.5-2h5L16 6h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z"/><circle cx="12" cy="13" r="4"/>',
            'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
            'image' => '<rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 16-5-5L5 19"/>',
            'external' => '<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/>',
        ];

        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['camera']) . '</svg>';
    };
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc((string) ($gallery['title'] ?? 'Photographer Gallery')) ?> - AdaAcara</title>
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
        a{color:inherit;text-decoration:none}
        button,input,select,textarea{font:inherit}
        .pg-page svg{display:block;flex:0 0 auto;width:20px;height:20px}
        .pg-page .h-4{width:16px;height:16px}.pg-page .h-6{width:24px;height:24px}.pg-page .h-16{width:64px;height:64px}
        .pg-page .w-4{width:16px}.pg-page .w-6{width:24px}.pg-page .w-16{width:64px}
        .mx-auto{margin-left:auto;margin-right:auto}.max-w-7xl{max-width:80rem}.min-h-\[170px\]{min-height:170px}
        .mt-1{margin-top:.25rem}.mt-2{margin-top:.5rem}.mt-4{margin-top:1rem}.mt-5{margin-top:1.25rem}.mt-6{margin-top:1.5rem}
        .p-3{padding:.75rem}.p-4{padding:1rem}.p-5{padding:1.25rem}.p-6{padding:1.5rem}.p-8{padding:2rem}
        .px-3{padding-left:.75rem;padding-right:.75rem}.px-4{padding-left:1rem;padding-right:1rem}.py-2{padding-top:.5rem;padding-bottom:.5rem}.py-3{padding-top:.75rem;padding-bottom:.75rem}
        .flex{display:flex}.grid{display:grid}.hidden{display:none}.block{display:block}.inline-flex{display:inline-flex}
        .flex-wrap{flex-wrap:wrap}.items-center{align-items:center}.justify-between{justify-content:space-between}.justify-center{justify-content:center}.place-items-center{place-items:center}
        .gap-0{gap:0}.gap-2{gap:.5rem}.gap-3{gap:.75rem}.gap-4{gap:1rem}.gap-6{gap:1.5rem}
        .w-full{width:100%}.h-full{height:100%}.h-72{height:18rem}
        .overflow-hidden{overflow:hidden}.relative{position:relative}.truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .rounded-2xl{border-radius:1rem}.rounded-3xl,.rounded-\[28px\]{border-radius:28px}.rounded-full{border-radius:999px}
        .border{border-width:1px;border-style:solid}.border-dashed{border-style:dashed}.border-slate-200{border-color:#e2e8f0}.border-slate-300{border-color:#cbd5e1}.border-emerald-200{border-color:#a7f3d0}.border-rose-200{border-color:#fecdd3}
        .bg-white{background:#fff}.bg-white\/70{background:rgba(255,255,255,.7)}.bg-white\/80{background:rgba(255,255,255,.8)}.bg-slate-100{background:#f1f5f9}.bg-emerald-50{background:#ecfdf5}.bg-rose-50{background:#fff1f2}
        .text-center{text-align:center}.text-xs{font-size:12px}.text-sm{font-size:14px}.text-lg{font-size:18px}.text-2xl{font-size:24px}.text-3xl{font-size:30px}.text-4xl{font-size:36px}
        .font-semibold{font-weight:600}.font-bold{font-weight:700}.font-black{font-weight:950}.uppercase{text-transform:uppercase}.tracking-tight{letter-spacing:-.02em}.tracking-\[0\.12em\]{letter-spacing:.12em}.tracking-\[0\.18em\]{letter-spacing:.18em}
        .text-slate-500{color:#64748b}.text-slate-600{color:#475569}.text-emerald-700{color:#047857}.text-rose-700{color:#be123c}.text-violet-300{color:#c4b5fd}.text-violet-600{color:#7c3aed}
        .object-cover{object-fit:cover}.aspect-square{aspect-ratio:1/1}.shadow-sm{box-shadow:0 1px 2px rgba(15,23,42,.08)}.cursor-pointer{cursor:pointer}
        .pg-page .grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}
        .pg-page{min-height:100vh;background:linear-gradient(135deg,#f8fbff 0%,#fff7fb 48%,#f4fffb 100%);padding:24px;color:#172033}
        .pg-card{border:1px solid rgba(148,163,184,.22);background:rgba(255,255,255,.86);box-shadow:0 18px 50px rgba(79,70,229,.08)}
        .pg-input{height:46px;width:100%;border-radius:16px;border:1px solid rgba(148,163,184,.28);background:rgba(255,255,255,.9);padding:0 14px;font-size:14px;font-weight:700;color:#172033;outline:none}
        .pg-select{height:42px;width:100%;min-width:0;border-radius:14px;border:1px solid rgba(148,163,184,.28);background:rgba(255,255,255,.92);padding:0 10px;font-size:12px;font-weight:900;color:#334155;outline:none;text-overflow:ellipsis}
        .pg-select:focus{border-color:#8f65df;box-shadow:0 0 0 4px rgba(143,101,223,.10)}
        .pg-input:focus{border-color:#8f65df;box-shadow:0 0 0 4px rgba(143,101,223,.12)}
        .pg-pin-field{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
        .pg-pin-boxes{display:grid;grid-template-columns:repeat(4,48px);gap:10px}
        .pg-pin-box{width:48px;height:52px;border:1px solid rgba(143,101,223,.28);border-radius:16px;background:rgba(255,255,255,.92);font-size:24px;font-weight:950;text-align:center;color:#8f65df;outline:none}
        .pg-pin-box:focus{border-color:#8f65df;box-shadow:0 0 0 4px rgba(143,101,223,.12)}
        .pg-pin-hint{margin:8px 0 0;font-size:11px;font-weight:800;line-height:1.45;color:#7b879c}
        .pg-pin-active{margin:8px 0 0;font-size:11px;font-weight:900;line-height:1.45;color:#7c3aed}
        .pg-label{font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.14em;color:#64748b}
        .pg-btn{display:inline-flex;height:42px;align-items:center;justify-content:center;gap:9px;border-radius:16px;padding:0 16px;font-size:13px;font-weight:900;transition:.18s ease}
        .pg-btn-primary{background:#8f65df;color:white;box-shadow:0 14px 32px rgba(143,101,223,.22)}
        .pg-btn-muted{border:1px solid rgba(148,163,184,.28);background:rgba(255,255,255,.74);color:#46556f}
        .pg-btn-danger{background:#e11d48;color:white;box-shadow:0 14px 32px rgba(225,29,72,.18)}
        .pg-btn[disabled]{cursor:not-allowed;opacity:.48;box-shadow:none}
        .pg-drop{border:1.5px dashed rgba(143,101,223,.42);background:linear-gradient(135deg,rgba(143,101,223,.08),rgba(244,114,182,.08))}
        .pg-drop.is-dragging{border-color:#8f65df;background:rgba(143,101,223,.12)}
        .pg-bulk-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;border:1px solid rgba(148,163,184,.20);border-radius:22px;background:rgba(255,255,255,.70);padding:12px}
        .pg-album-panel{margin-top:18px;border:1px solid rgba(148,163,184,.20);border-radius:24px;background:rgba(255,255,255,.70);padding:14px}
        .pg-album-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
        .pg-album-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
        .pg-album-chip{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid rgba(148,163,184,.26);border-radius:999px;background:#fff;padding:9px 12px;font-size:12px;font-weight:950;color:#475569;cursor:pointer}
        .pg-album-chip.is-active{border-color:#8f65df;background:#8f65df;color:#fff;box-shadow:0 12px 24px rgba(143,101,223,.18)}
        .pg-album-create-wrap{display:none;margin-top:12px;border-top:1px solid rgba(226,232,240,.85);padding-top:12px}
        .pg-album-create-wrap.is-open{display:block}
        .pg-album-create{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px}
        .pg-quick-add{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
        .pg-quick-add button[hidden]{display:none}
        .pg-quick-add button{min-height:34px;border:1px solid rgba(143,101,223,.22);border-radius:999px;background:#f7f3ff;padding:0 11px;font-size:11px;font-weight:950;color:#7c3aed;cursor:pointer}
        .pg-tabs{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
        .pg-tab{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid rgba(148,163,184,.24);border-radius:999px;background:rgba(255,255,255,.82);padding:10px 14px;font-size:12px;font-weight:950;color:#475569;cursor:pointer}
        .pg-tab.is-active{border-color:#8f65df;background:#8f65df;color:#fff;box-shadow:0 12px 26px rgba(143,101,223,.18)}
        .pg-panel[hidden]{display:none!important}
        .pg-check-label{display:inline-flex;align-items:center;gap:10px;font-size:13px;font-weight:950;color:#475569}
        .pg-check{width:18px;height:18px;accent-color:#8f65df;cursor:pointer}
        .pg-photo-select{position:absolute;left:10px;top:10px;z-index:2;display:grid;place-items:center;border-radius:14px}
        .pg-photo-delete{position:absolute;right:10px;top:10px;z-index:2;display:grid;width:34px;height:34px;place-items:center;border:0;border-radius:14px;background:rgba(255,255,255,.94);color:#e11d48;box-shadow:0 10px 22px rgba(15,23,42,.14);cursor:pointer;opacity:0;transition:opacity .16s ease,transform .16s ease}
        .pg-photo-card:hover .pg-photo-delete,.pg-photo-delete:focus-visible{opacity:1}
        .pg-photo-delete:hover{transform:translateY(-1px)}
        .pg-photo-delete svg{width:16px;height:16px}
        .pg-photo-card{transition:border-color .16s ease,box-shadow .16s ease,transform .16s ease}
        .pg-photo-card:has(.pg-photo-checkbox:checked){border-color:#8f65df;box-shadow:0 0 0 3px rgba(143,101,223,.16),0 18px 38px rgba(79,70,229,.12)}
        .pg-photo-meta{display:grid;gap:8px;border-top:1px solid rgba(226,232,240,.82);padding:10px;background:rgba(248,250,252,.72)}
        .pg-photo-meta-row{display:grid;grid-template-columns:1fr;gap:8px}
        .pg-photo-card.is-paginated-hidden{display:none}
        .pg-pagination{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;border:1px solid rgba(148,163,184,.20);border-radius:22px;background:rgba(255,255,255,.70);padding:12px}
        .pg-pagination-info{font-size:12px;font-weight:900;color:#64748b}
        .pg-pagination-actions{display:flex;align-items:center;gap:8px}
        .pg-page-number{display:inline-flex;min-width:42px;height:38px;align-items:center;justify-content:center;border:1px solid rgba(148,163,184,.26);border-radius:14px;background:#fff;font-size:12px;font-weight:950;color:#475569}
        .pg-print-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:16px}
        .pg-print-card{display:grid;grid-template-columns:82px minmax(0,1fr);gap:12px;align-items:center;border:1px solid rgba(148,163,184,.22);border-radius:20px;background:rgba(255,255,255,.78);padding:10px}
        .pg-print-card img{width:82px;height:82px;border-radius:16px;object-fit:cover;background:#eef2f7}
        .pg-print-card strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:950;color:#172033}
        .pg-print-card span{display:block;margin-top:4px;font-size:11px;font-weight:800;color:#64748b}
        .pg-comment-list{display:grid;gap:12px;margin-top:16px}
        .pg-comment-card{display:grid;grid-template-columns:86px minmax(0,1fr);gap:14px;border:1px solid rgba(148,163,184,.22);border-radius:22px;background:rgba(255,255,255,.78);padding:12px}
        .pg-comment-card img,.pg-comment-thumb-empty{width:86px;height:86px;border-radius:18px;object-fit:cover;background:#eef2f7}
        .pg-comment-thumb-empty{display:grid;place-items:center;color:#a78bfa}
        .pg-comment-meta{display:flex;flex-wrap:wrap;gap:7px;margin-top:6px}
        .pg-comment-pill{display:inline-flex;align-items:center;border:1px solid rgba(148,163,184,.20);border-radius:999px;background:#fff;padding:5px 8px;font-size:10px;font-weight:950;color:#64748b}
        .pg-comment-text{margin:8px 0 0;font-size:13px;font-weight:750;line-height:1.55;color:#334155;white-space:pre-wrap}
        .pg-empty{display:grid;place-items:center;border:1px dashed rgba(148,163,184,.32);border-radius:24px;background:rgba(255,255,255,.64);padding:24px;text-align:center;color:#64748b}
        .pg-selected-count{font-size:12px;font-weight:900;color:#64748b}
        .pg-hero-grid{display:grid;grid-template-columns:1fr}
        .pg-detail-grid{display:grid;grid-template-columns:1fr;gap:24px}
        .pg-stats-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .pg-photo-grid,#pg-photo-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
        html[data-aa-public-theme="dark"] .pg-page{background:linear-gradient(135deg,#08111f 0%,#17111d 52%,#071b17 100%);color:#edf2f7}
        html[data-aa-public-theme="dark"] .pg-card{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.82)}
        html[data-aa-public-theme="dark"] .pg-bulk-toolbar,html[data-aa-public-theme="dark"] .pg-photo-delete{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.86)}
        html[data-aa-public-theme="dark"] .pg-album-panel{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.58)}
        html[data-aa-public-theme="dark"] .pg-album-chip{border-color:rgba(148,163,184,.24);background:rgba(15,23,42,.76);color:#d8e0ec}
        html[data-aa-public-theme="dark"] .pg-quick-add button{border-color:rgba(167,139,250,.26);background:rgba(124,58,237,.16);color:#ddd6fe}
        html[data-aa-public-theme="dark"] .pg-input,html[data-aa-public-theme="dark"] .pg-select{border-color:rgba(148,163,184,.24);background:rgba(15,23,42,.7);color:#edf2f7}
        html[data-aa-public-theme="dark"] .pg-photo-meta{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.52)}
        html[data-aa-public-theme="dark"] .pg-pagination{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.58)}
        html[data-aa-public-theme="dark"] .pg-page-number{border-color:rgba(148,163,184,.22);background:rgba(15,23,42,.72);color:#d8e0ec}
        html[data-aa-public-theme="dark"] .pg-print-card{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.58)}
        html[data-aa-public-theme="dark"] .pg-print-card strong{color:#edf2f7}
        html[data-aa-public-theme="dark"] .pg-comment-card{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.58)}
        html[data-aa-public-theme="dark"] .pg-comment-pill{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.72);color:#cbd5e1}
        html[data-aa-public-theme="dark"] .pg-comment-text{color:#d8e0ec}
        @media(min-width:640px){.pg-stats-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.pg-page .sm\:p-8{padding:2rem}.pg-page .sm\:text-4xl{font-size:36px}}
        @media(min-width:1024px){.pg-hero-grid{grid-template-columns:360px minmax(0,1fr)}.pg-page .lg\:h-full{height:100%}}
        @media(min-width:1180px){.pg-detail-grid{grid-template-columns:minmax(0,380px) minmax(0,1fr)}}
        @media(max-width:980px){.pg-print-list{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:820px){.pg-page{padding:16px}.pg-photo-grid,#pg-photo-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:520px){.pg-stats-grid,.pg-photo-grid,#pg-photo-grid,.pg-print-list{grid-template-columns:1fr}.pg-comment-card{grid-template-columns:70px minmax(0,1fr);gap:10px}.pg-comment-card img,.pg-comment-thumb-empty{width:70px;height:70px;border-radius:16px}}
    </style>
</head>
<body class="aa-app-ui aa-dashboard-theme-page aa-dashboard-pastel antialiased">
<main class="pg-page">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a class="pg-btn pg-btn-muted" href="<?= site_url('photographer-galleries') ?>"><?= $icon('arrow-left', 'h-4 w-4') ?>Semua Gallery</a>
            <div class="flex flex-wrap items-center gap-2">
                <a class="pg-btn pg-btn-primary" href="<?= esc($customerUrl, 'attr') ?>" target="_blank" rel="noopener"><?= $icon('external', 'h-4 w-4') ?>Halaman Customer</a>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-xs font-black text-slate-600 shadow-sm"><?= $privacyMode === 'pin' ? $icon('lock', 'h-4 w-4') : '' ?><?= $privacyMode === 'pin' ? 'PIN Protected' : 'Public' ?></span>
            </div>
        </div>

        <section class="pg-card mt-5 overflow-hidden rounded-[28px]">
            <div class="pg-hero-grid">
                <div class="h-72 bg-slate-100 lg:h-full">
                    <?php if ($coverUrl !== ''): ?>
                        <img class="h-full w-full object-cover" src="<?= esc($coverUrl, 'attr') ?>" alt="<?= esc((string) ($gallery['title'] ?? 'Gallery'), 'attr') ?>">
                    <?php else: ?>
                        <div class="grid h-full place-items-center bg-gradient-to-br from-violet-50 via-white to-rose-50 text-violet-300"><?= $icon('camera', 'h-16 w-16') ?></div>
                    <?php endif; ?>
                </div>
                <div class="p-6 sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-600">Photographer Gallery</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl"><?= esc((string) ($gallery['title'] ?? 'Gallery')) ?></h1>
                    <p class="mt-2 text-sm font-semibold text-slate-600"><?= esc((string) ($gallery['studio_name'] ?? '')) ?><?= ! empty($gallery['event_date']) ? ' · ' . esc(date('d M Y', strtotime((string) $gallery['event_date']))) : '' ?></p>
                    <div class="pg-stats-grid mt-6">
                        <div class="rounded-3xl bg-white/70 p-4"><p class="text-2xl font-black"><?= $photoCount ?></p><p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Foto</p></div>
                        <div class="rounded-3xl bg-white/70 p-4"><p class="text-2xl font-black"><?= ! empty($gallery['selection_enabled']) ? 'On' : 'Off' ?></p><p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Selection</p></div>
                        <div class="rounded-3xl bg-white/70 p-4"><p class="text-2xl font-black"><?= (int) ($gallery['selection_limit'] ?? 30) ?></p><p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Max Pilih</p></div>
                        <div class="rounded-3xl bg-white/70 p-4"><p class="text-2xl font-black"><?= ! empty($gallery['download_enabled']) ? 'On' : 'Off' ?></p><p class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Download</p></div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (session('success')): ?>
            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700" data-pg-inline-flash><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700" data-pg-inline-flash><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if ($errors !== []): ?>
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                <?php foreach ($errors as $error): ?>
                    <p><?= esc($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="pg-detail-grid mt-6">
            <section class="pg-card rounded-[28px] p-5">
                <h2 class="text-lg font-black">Pengaturan</h2>
                <form class="mt-5 grid gap-4" action="<?= esc($settingsUrl, 'attr') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <label><span class="pg-label">Nama Project</span><input class="pg-input mt-2" name="title" value="<?= esc(old('title', (string) ($gallery['title'] ?? '')), 'attr') ?>" required></label>
                    <label><span class="pg-label">Tanggal</span><input class="pg-input mt-2" type="date" name="event_date" value="<?= esc(old('event_date', (string) ($gallery['event_date'] ?? '')), 'attr') ?>"></label>
                    <label><span class="pg-label">Studio</span><input class="pg-input mt-2" name="studio_name" value="<?= esc(old('studio_name', (string) ($gallery['studio_name'] ?? '')), 'attr') ?>"></label>
                    <label><span class="pg-label">Slug</span><input class="pg-input mt-2" name="slug" value="<?= esc(old('slug', (string) ($gallery['slug'] ?? '')), 'attr') ?>"></label>
                    <label><span class="pg-label">Cover Baru</span><input class="mt-2 block w-full rounded-2xl border border-dashed border-slate-300 bg-white/80 p-3 text-xs font-bold text-slate-600" type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp"></label>
                    <div class="grid gap-3 rounded-3xl border border-slate-200 bg-white/70 p-4">
                        <span class="pg-label">Privacy</span>
                        <label class="flex items-center gap-3 text-sm font-black"><input type="radio" name="privacy_mode" value="public" <?= old('privacy_mode', $privacyMode) === 'public' ? 'checked' : '' ?>>Public</label>
                        <label class="flex items-center gap-3 text-sm font-black"><input type="radio" name="privacy_mode" value="pin" <?= old('privacy_mode', $privacyMode) === 'pin' ? 'checked' : '' ?>>PIN Protected</label>
                        <input class="pg-pin-field" name="pin" value="<?= esc($oldPin, 'attr') ?>" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" data-admin-pin-hidden>
                        <div class="pg-pin-boxes" data-admin-pin-boxes>
                            <?php for ($pinIndex = 0; $pinIndex < 4; $pinIndex++): ?>
                                <input class="pg-pin-box" type="text" inputmode="numeric" maxlength="1" value="<?= esc($oldPin[$pinIndex] ?? '', 'attr') ?>" aria-label="Digit PIN <?= $pinIndex + 1 ?>" data-admin-pin-box>
                            <?php endfor; ?>
                        </div>
                        <?php if ($hasActivePin): ?>
                            <p class="pg-pin-active">* PIN aktif: tersimpan. Isi 4 digit baru hanya jika ingin mengganti PIN.</p>
                        <?php endif; ?>
                        <p class="pg-pin-hint">Isi 4 digit hanya jika ingin membuat/mengganti PIN. Kosongkan jika tidak diubah.</p>
                    </div>
                    <label class="flex items-center gap-3 text-sm font-black"><input type="checkbox" name="selection_enabled" value="1" <?= old('selection_enabled', ! empty($gallery['selection_enabled'])) ? 'checked' : '' ?>>Izinkan client memilih foto</label>
                    <label><span class="pg-label">Maximum pilihan</span><input class="pg-input mt-2" type="number" min="1" max="500" name="selection_limit" value="<?= esc(old('selection_limit', (string) ($gallery['selection_limit'] ?? 30)), 'attr') ?>"></label>
                    <label class="flex items-center gap-3 text-sm font-black"><input type="checkbox" name="download_enabled" value="1" <?= old('download_enabled', ! empty($gallery['download_enabled'])) ? 'checked' : '' ?>>Izinkan download</label>
                    <button class="pg-btn pg-btn-primary" type="submit">Simpan Pengaturan</button>
                </form>
            </section>

            <section class="pg-card rounded-[28px] p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black">Upload Photos</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-600">Upload dibuat per-file agar stabil untuk ratusan foto.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="pg-btn pg-btn-primary cursor-pointer">
                            <?= $icon('upload', 'h-4 w-4') ?>Pilih Foto
                            <input id="pg-photo-input" class="hidden" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                        </label>
                    </div>
                </div>

                <?php if ($albumsReady): ?>
                    <section class="pg-album-panel" data-album-panel data-album-create-url="<?= esc($albumCreateUrl, 'attr') ?>">
                        <div class="pg-album-head">
                            <div>
                                <p class="pg-label">Album</p>
                                <p class="mt-1 text-xs font-bold text-slate-500">Pilih album aktif sebelum upload, atau buat album baru sesuai kebutuhan project.</p>
                            </div>
                            <button class="pg-btn pg-btn-muted" type="button" data-toggle-album-create>+ Buat Album</button>
                        </div>
                        <div class="pg-album-chips" data-album-chips>
                            <button class="pg-album-chip is-active" type="button" data-upload-album-id="">Tanpa Album</button>
                            <?php foreach ($albums as $album): ?>
                                <button class="pg-album-chip" type="button" data-upload-album-id="<?= (int) ($album['id'] ?? 0) ?>"><?= esc((string) ($album['name'] ?? 'Album')) ?></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="pg-album-create-wrap" data-album-create-wrap>
                            <form class="pg-album-create" data-album-create-form>
                                <input class="pg-input" name="name" placeholder="Nama Album, contoh: Wedding Ceremony" maxlength="140" autocomplete="off">
                                <button class="pg-btn pg-btn-primary" type="submit">Upload Foto</button>
                            </form>
                            <div class="pg-quick-add" data-quick-add-albums>
                                <button type="button" data-quick-album="Highlight">Highlight</button>
                                <button type="button" data-quick-album="Ceremony">Ceremony</button>
                                <button type="button" data-quick-album="Reception">Reception</button>
                                <button type="button" data-quick-album="Family">Family</button>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <nav class="pg-tabs" aria-label="Tab foto gallery">
                    <button class="pg-tab is-active" type="button" data-admin-photo-tab="all">Semua Foto</button>
                    <button class="pg-tab" type="button" data-admin-photo-tab="print">Pilihan untuk dicetak <span data-print-admin-count>(<?= count($printSelections) ?>)</span></button>
                    <button class="pg-tab" type="button" data-admin-photo-tab="comments">Komentar/Revisi <span>(<?= count($comments) ?>)</span></button>
                </nav>

                <div id="pg-dropzone" class="pg-drop mt-5 grid min-h-[170px] place-items-center rounded-[28px] p-8 text-center">
                    <div>
                        <span class="mx-auto grid h-14 w-14 place-items-center rounded-3xl bg-white text-violet-700 shadow-sm"><?= $icon('upload', 'h-6 w-6') ?></span>
                        <p class="mt-4 text-sm font-black">Drag foto ke sini</p>
                        <p id="pg-upload-status" class="mt-1 text-xs font-bold text-slate-500">JPG, PNG, atau WebP. Maksimal 20MB per foto.</p>
                    </div>
                </div>

                <form id="pg-bulk-delete-form" class="pg-panel" action="<?= esc($bulkDeleteUrl, 'attr') ?>" method="post" data-bulk-delete-form data-admin-photo-panel="all">
                    <?= csrf_field() ?>
                    <div class="pg-bulk-toolbar">
                        <label class="pg-check-label">
                            <input id="pg-select-all" class="pg-check" type="checkbox" data-select-all>
                            Select all
                        </label>
                        <div class="flex items-center gap-3">
                            <span class="pg-selected-count" data-selected-count>0 foto dipilih</span>
                            <button class="pg-btn pg-btn-danger" type="submit" data-delete-selected disabled><?= $icon('trash', 'h-4 w-4') ?>Hapus Terpilih</button>
                        </div>
                    </div>

                <div id="pg-photo-grid" class="pg-photo-grid mt-6">
                    <?php foreach ($photos as $photo): ?>
                        <?php
                            $thumb = trim((string) ($photo['thumb_path'] ?? $photo['file_path'] ?? ''));
                            $deleteUrl = site_url('photographer-galleries/' . (int) ($gallery['id'] ?? 0) . '/photos/' . (int) ($photo['id'] ?? 0) . '/delete');
                            $metaUrl = site_url('photographer-galleries/' . (int) ($gallery['id'] ?? 0) . '/photos/' . (int) ($photo['id'] ?? 0) . '/meta');
                            $photoAlbumId = (int) ($photo['album_id'] ?? 0);
                            $photoStatus = (string) ($photo['status'] ?? 'uploaded');
                            if ($photoStatus === 'visible') {
                                $photoStatus = 'uploaded';
                            }
                        ?>
                        <figure class="pg-photo-card group relative overflow-hidden rounded-3xl border border-slate-200 bg-white" data-photo-card data-delete-url="<?= esc($deleteUrl, 'attr') ?>" data-meta-url="<?= esc($metaUrl, 'attr') ?>">
                            <label class="pg-photo-select" aria-label="Pilih foto">
                                <input class="pg-check pg-photo-checkbox" type="checkbox" name="photo_ids[]" value="<?= (int) ($photo['id'] ?? 0) ?>" data-photo-checkbox>
                            </label>
                            <button class="pg-photo-delete" type="button" aria-label="Hapus foto" data-delete-photo>
                                <?= $icon('trash', 'h-4 w-4') ?>
                            </button>
                            <img class="aspect-square w-full object-cover" src="<?= esc(base_url($thumb), 'attr') ?>" alt="<?= esc((string) ($photo['original_name'] ?? 'Foto'), 'attr') ?>" loading="lazy">
                            <figcaption class="truncate px-3 py-2 text-xs font-bold text-slate-500"><?= esc((string) ($photo['original_name'] ?? 'Foto')) ?></figcaption>
                            <div class="pg-photo-meta">
                                <div class="pg-photo-meta-row">
                                    <select class="pg-select" data-photo-album aria-label="Album foto">
                                        <option value="">Tanpa Album</option>
                                        <?php foreach ($albums as $album): ?>
                                            <?php $albumId = (int) ($album['id'] ?? 0); ?>
                                            <option value="<?= $albumId ?>" <?= $photoAlbumId === $albumId ? 'selected' : '' ?>><?= esc((string) ($album['name'] ?? 'Album')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select class="pg-select" data-photo-status aria-label="Status foto">
                                        <?php foreach ($photoStatuses as $statusKey): ?>
                                            <option value="<?= esc((string) $statusKey, 'attr') ?>" <?= $photoStatus === (string) $statusKey ? 'selected' : '' ?>><?= esc($statusLabels[(string) $statusKey] ?? ucfirst((string) $statusKey)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <div class="pg-pagination" data-photo-pagination>
                    <span class="pg-pagination-info" data-photo-page-info>Menampilkan foto</span>
                    <div class="pg-pagination-actions">
                        <button class="pg-btn pg-btn-muted" type="button" data-photo-page-prev>Sebelumnya</button>
                        <span class="pg-page-number" data-photo-page-number>1 / 1</span>
                        <button class="pg-btn pg-btn-muted" type="button" data-photo-page-next>Berikutnya</button>
                    </div>
                </div>
                </form>

                <section class="pg-panel" data-admin-photo-panel="print" hidden>
                    <?php if ($printSelections === []): ?>
                        <div class="pg-empty" style="min-height:220px">
                            <div>
                                <?= $icon('image', 'h-10 w-10') ?>
                                <p style="margin:12px 0 0;font-weight:900">Belum ada pilihan untuk dicetak.</p>
                                <p style="margin:6px 0 0;font-size:13px;font-weight:700;color:#64748b">Pilihan client akan muncul di sini setelah mereka memilih foto.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="pg-print-list">
                            <?php foreach ($printSelections as $selection): ?>
                                <?php
                                    $thumb = trim((string) ($selection['thumb_path'] ?? $selection['file_path'] ?? ''));
                                    $albumName = 'Tanpa Album';
                                    $selectionAlbumId = (int) ($selection['album_id'] ?? 0);
                                    foreach ($albums as $album) {
                                        if ((int) ($album['id'] ?? 0) === $selectionAlbumId) {
                                            $albumName = (string) ($album['name'] ?? 'Album');
                                            break;
                                        }
                                    }
                                    $submittedAt = trim((string) ($selection['submitted_at'] ?? ''));
                                ?>
                                <article class="pg-print-card">
                                    <img src="<?= esc(base_url($thumb), 'attr') ?>" alt="<?= esc((string) ($selection['original_name'] ?? 'Foto'), 'attr') ?>" loading="lazy">
                                    <div>
                                        <strong><?= esc((string) ($selection['original_name'] ?? 'Foto')) ?></strong>
                                        <span><?= esc($albumName) ?></span>
                                        <span><?= $submittedAt !== '' ? 'Dikirim: ' . esc(date('d M Y H:i', strtotime($submittedAt))) : 'Belum dikirim final' ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="pg-panel" data-admin-photo-panel="comments" hidden>
                    <?php if (! $commentsReady): ?>
                        <div class="pg-empty" style="min-height:220px">
                            <div>
                                <?= $icon('image', 'h-10 w-10') ?>
                                <p style="margin:12px 0 0;font-weight:900">Tabel komentar belum siap.</p>
                                <p style="margin:6px 0 0;font-size:13px;font-weight:700;color:#64748b">Jalankan bagian `photographer_gallery_comments` dari SQL Photographer Gallery.</p>
                            </div>
                        </div>
                    <?php elseif ($comments === []): ?>
                        <div class="pg-empty" style="min-height:220px">
                            <div>
                                <?= $icon('image', 'h-10 w-10') ?>
                                <p style="margin:12px 0 0;font-weight:900">Belum ada komentar/revisi.</p>
                                <p style="margin:6px 0 0;font-size:13px;font-weight:700;color:#64748b">Notes dari client akan muncul di sini setelah dikirim dari halaman gallery.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="pg-comment-list">
                            <?php foreach ($comments as $comment): ?>
                                <?php
                                    $thumb = trim((string) ($comment['thumb_path'] ?? $comment['file_path'] ?? ''));
                                    $albumName = 'Tanpa Album';
                                    $commentAlbumId = (int) ($comment['album_id'] ?? 0);
                                    foreach ($albums as $album) {
                                        if ((int) ($album['id'] ?? 0) === $commentAlbumId) {
                                            $albumName = (string) ($album['name'] ?? 'Album');
                                            break;
                                        }
                                    }
                                    $createdAt = trim((string) ($comment['created_at'] ?? ''));
                                    $photoName = trim((string) ($comment['original_name'] ?? 'Foto'));
                                ?>
                                <article class="pg-comment-card">
                                    <?php if ($thumb !== ''): ?>
                                        <img src="<?= esc(base_url($thumb), 'attr') ?>" alt="<?= esc($photoName, 'attr') ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="pg-comment-thumb-empty"><?= $icon('image', 'h-6 w-6') ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="block truncate text-sm font-black"><?= esc($photoName) ?></strong>
                                        <div class="pg-comment-meta">
                                            <span class="pg-comment-pill"><?= esc($albumName) ?></span>
                                            <?php if ($createdAt !== ''): ?>
                                                <span class="pg-comment-pill"><?= esc(date('d M Y H:i', strtotime($createdAt))) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="pg-comment-text"><?= esc((string) ($comment['comment'] ?? '')) ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </section>
        </div>
    </div>
</main>

<script>
(() => {
    const input = document.getElementById('pg-photo-input');
    const dropzone = document.getElementById('pg-dropzone');
    const grid = document.getElementById('pg-photo-grid');
    const status = document.getElementById('pg-upload-status');
    const albumPanel = document.querySelector('[data-album-panel]');
    const albumChips = document.querySelector('[data-album-chips]');
    const albumCreateWrap = document.querySelector('[data-album-create-wrap]');
    const albumCreateForm = document.querySelector('[data-album-create-form]');
    const toggleAlbumCreate = document.querySelector('[data-toggle-album-create]');
    const bulkForm = document.querySelector('[data-bulk-delete-form]');
    const selectAll = document.querySelector('[data-select-all]');
    const selectedCount = document.querySelector('[data-selected-count]');
    const deleteSelected = document.querySelector('[data-delete-selected]');
    const photoPagination = document.querySelector('[data-photo-pagination]');
    const photoPageInfo = document.querySelector('[data-photo-page-info]');
    const photoPageNumber = document.querySelector('[data-photo-page-number]');
    const photoPagePrev = document.querySelector('[data-photo-page-prev]');
    const photoPageNext = document.querySelector('[data-photo-page-next]');
    const uploadUrl = <?= json_encode($uploadUrl) ?>;
    const singleDeleteUrlBase = <?= json_encode(site_url('photographer-galleries/' . (int) ($gallery['id'] ?? 0) . '/photos/')) ?>;
    const photoMetaUrlBase = <?= json_encode(site_url('photographer-galleries/' . (int) ($gallery['id'] ?? 0) . '/photos/')) ?>;
    const albumOptions = <?= json_encode(array_map(static fn (array $album): array => ['id' => (int) ($album['id'] ?? 0), 'name' => (string) ($album['name'] ?? 'Album')], $albums)) ?>;
    const statusOptions = <?= json_encode(array_map(static fn (string $status): array => ['value' => $status, 'label' => $statusLabels[$status] ?? ucfirst($status)], array_values($photoStatuses))) ?>;
    let csrfName = <?= json_encode(csrf_token()) ?>;
    let csrfHash = <?= json_encode(csrf_hash()) ?>;
    const flashSuccess = <?= json_encode($flashSuccess ?: '') ?>;
    const flashError = <?= json_encode($flashError ?: '') ?>;
    const adminPinHidden = document.querySelector('[data-admin-pin-hidden]');
    const adminPinBoxes = Array.from(document.querySelectorAll('[data-admin-pin-box]'));
    let activeUploadAlbumId = '';
    let photoPage = 1;
    const photoPageSize = 12;

    const setStatus = (message) => {
        if (status) status.textContent = message;
    };

    const showToast = (message, tone = 'success', title = '') => {
        if (!message) return;
        if (typeof window.aaToast === 'function') {
            window.aaToast(message, tone, title || undefined);
            return;
        }
        setStatus(message);
    };

    const updateCsrf = (hash) => {
        if (!hash) return;
        csrfHash = hash;
        document.querySelectorAll(`input[name="${csrfName}"]`).forEach((inputEl) => {
            inputEl.value = hash;
        });
    };

    const addPhoto = (photo) => {
        if (!grid || !photo || !photo.thumb_url) return;
        const figure = document.createElement('figure');
        figure.className = 'pg-photo-card group relative overflow-hidden rounded-3xl border border-slate-200 bg-white';
        figure.setAttribute('data-photo-card', '');
        figure.dataset.deleteUrl = singleDeleteUrlBase + encodeURIComponent(String(photo.id || '')) + '/delete';
        figure.dataset.metaUrl = photoMetaUrlBase + encodeURIComponent(String(photo.id || '')) + '/meta';
        const label = document.createElement('label');
        label.className = 'pg-photo-select';
        label.setAttribute('aria-label', 'Pilih foto');
        const checkbox = document.createElement('input');
        checkbox.className = 'pg-check pg-photo-checkbox';
        checkbox.type = 'checkbox';
        checkbox.name = 'photo_ids[]';
        checkbox.value = photo.id || '';
        checkbox.setAttribute('data-photo-checkbox', '');
        label.appendChild(checkbox);
        const deleteButton = document.createElement('button');
        deleteButton.className = 'pg-photo-delete';
        deleteButton.type = 'button';
        deleteButton.setAttribute('aria-label', 'Hapus foto');
        deleteButton.setAttribute('data-delete-photo', '');
        deleteButton.innerHTML = <?= json_encode($icon('trash', 'h-4 w-4')) ?>;
        const img = document.createElement('img');
        img.className = 'aspect-square w-full object-cover';
        img.src = photo.thumb_url;
        img.alt = photo.name || 'Foto';
        const caption = document.createElement('figcaption');
        caption.className = 'truncate px-3 py-2 text-xs font-bold text-slate-500';
        caption.textContent = photo.name || 'Foto';
        const meta = document.createElement('div');
        meta.className = 'pg-photo-meta';
        const metaRow = document.createElement('div');
        metaRow.className = 'pg-photo-meta-row';
        const albumSelect = document.createElement('select');
        albumSelect.className = 'pg-select';
        albumSelect.setAttribute('data-photo-album', '');
        albumSelect.setAttribute('aria-label', 'Album foto');
        albumSelect.append(new Option('Tanpa Album', ''));
        albumOptions.forEach((album) => {
            albumSelect.append(new Option(album.name, String(album.id)));
        });
        albumSelect.value = photo.album_id ? String(photo.album_id) : '';
        const statusSelect = document.createElement('select');
        statusSelect.className = 'pg-select';
        statusSelect.setAttribute('data-photo-status', '');
        statusSelect.setAttribute('aria-label', 'Status foto');
        statusOptions.forEach((statusOption) => {
            statusSelect.append(new Option(statusOption.label, statusOption.value));
        });
        statusSelect.value = photo.status || 'uploaded';
        metaRow.appendChild(albumSelect);
        metaRow.appendChild(statusSelect);
        meta.appendChild(metaRow);
        figure.appendChild(label);
        figure.appendChild(deleteButton);
        figure.appendChild(img);
        figure.appendChild(caption);
        figure.appendChild(meta);
        grid.prepend(figure);
        photoPage = 1;
        renderPhotoPagination();
        updateSelectionState();
    };

    const setActiveAlbum = (albumId) => {
        activeUploadAlbumId = String(albumId || '');
        document.querySelectorAll('[data-upload-album-id]').forEach((chip) => {
            chip.classList.toggle('is-active', String(chip.dataset.uploadAlbumId || '') === activeUploadAlbumId);
        });
    };

    const appendAlbumOptionToSelects = (album) => {
        if (!album || !album.id) return;
        document.querySelectorAll('[data-photo-album]').forEach((select) => {
            if (Array.from(select.options).some((option) => option.value === String(album.id))) return;
            select.append(new Option(album.name || 'Album', String(album.id)));
        });
    };

    const appendAlbumChip = (album) => {
        if (!albumChips || !album || !album.id) return;
        if (Array.from(albumChips.querySelectorAll('[data-upload-album-id]')).some((chip) => String(chip.dataset.uploadAlbumId || '') === String(album.id))) return;
        const chip = document.createElement('button');
        chip.className = 'pg-album-chip';
        chip.type = 'button';
        chip.dataset.uploadAlbumId = String(album.id);
        chip.textContent = album.name || 'Album';
        albumChips.appendChild(chip);
    };

    const albumNameExists = (name) => {
        const normalized = String(name || '').trim().toLowerCase();
        if (!normalized) return false;
        return albumOptions.some((album) => String(album.name || '').trim().toLowerCase() === normalized);
    };

    const refreshQuickAdd = () => {
        document.querySelectorAll('[data-quick-album]').forEach((button) => {
            button.hidden = albumNameExists(button.dataset.quickAlbum || '');
        });
    };

    const createAlbum = async (name) => {
        const trimmed = String(name || '').trim();
        if (trimmed.length < 2) {
            showToast('Nama album minimal 2 karakter.', 'warning', 'Album belum dibuat');
            return null;
        }
        const data = new FormData();
        data.append(csrfName, csrfHash);
        data.append('name', trimmed);
        const response = await fetch(albumPanel?.dataset.albumCreateUrl || '', {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: data,
        });
        const json = await response.json().catch(() => ({}));
        updateCsrf(json.csrf_hash);
        if (!response.ok || !json.ok || !json.album) {
            throw new Error(json.message || 'Album belum bisa dibuat.');
        }
        albumOptions.push(json.album);
        appendAlbumChip(json.album);
        appendAlbumOptionToSelects(json.album);
        setActiveAlbum(json.album.id);
        refreshQuickAdd();
        showToast(`Album "${json.album.name}" dibuat dan aktif untuk upload.`, 'success', 'Album dibuat');
        return json.album;
    };

    const photoCards = () => Array.from(document.querySelectorAll('[data-photo-card]'));
    const photoCheckboxes = () => Array.from(document.querySelectorAll('[data-photo-checkbox]'));
    const currentPageCheckboxes = () => photoCards()
        .filter((card) => !card.classList.contains('is-paginated-hidden'))
        .map((card) => card.querySelector('[data-photo-checkbox]'))
        .filter(Boolean);

    const renderPhotoPagination = () => {
        const cards = photoCards();
        const total = cards.length;
        const totalPages = Math.max(1, Math.ceil(total / photoPageSize));
        photoPage = Math.min(Math.max(1, photoPage), totalPages);
        cards.forEach((card, index) => {
            const page = Math.floor(index / photoPageSize) + 1;
            card.classList.toggle('is-paginated-hidden', page !== photoPage);
        });
        if (photoPagination) photoPagination.style.display = 'flex';
        if (photoPageInfo) {
            const start = total === 0 ? 0 : ((photoPage - 1) * photoPageSize) + 1;
            const end = Math.min(total, photoPage * photoPageSize);
            photoPageInfo.textContent = total === 0 ? 'Belum ada foto' : `Menampilkan ${start}-${end} dari ${total} foto`;
        }
        if (photoPageNumber) {
            photoPageNumber.textContent = `${photoPage} / ${totalPages}`;
        }
        if (photoPagePrev) {
            photoPagePrev.disabled = photoPage <= 1;
        }
        if (photoPageNext) {
            photoPageNext.disabled = photoPage >= totalPages;
        }
        updateSelectionState();
    };

    const updateSelectionState = () => {
        const boxes = photoCheckboxes();
        const checked = boxes.filter((box) => box.checked);
        const pageBoxes = currentPageCheckboxes();
        const pageChecked = pageBoxes.filter((box) => box.checked);
        if (selectedCount) {
            selectedCount.textContent = `${checked.length} foto dipilih`;
        }
        if (deleteSelected) {
            deleteSelected.disabled = checked.length === 0;
        }
        if (selectAll) {
            selectAll.checked = pageBoxes.length > 0 && pageChecked.length === pageBoxes.length;
            selectAll.indeterminate = pageChecked.length > 0 && pageChecked.length < pageBoxes.length;
        }
    };

    const syncAdminPin = () => {
        if (!adminPinHidden) return;
        adminPinHidden.value = adminPinBoxes.map((box) => box.value.trim()).join('');
    };

    adminPinBoxes.forEach((box, index) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncAdminPin();
            if (box.value && adminPinBoxes[index + 1]) {
                adminPinBoxes[index + 1].focus();
            }
        });
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !box.value && adminPinBoxes[index - 1]) {
                adminPinBoxes[index - 1].focus();
            }
        });
        box.addEventListener('paste', (event) => {
            const pasted = (event.clipboardData || window.clipboardData)?.getData('text') || '';
            const digits = pasted.replace(/\D/g, '').slice(0, adminPinBoxes.length).split('');
            if (digits.length === 0) return;
            event.preventDefault();
            adminPinBoxes.forEach((target, targetIndex) => {
                target.value = digits[targetIndex] || '';
            });
            syncAdminPin();
            adminPinBoxes[Math.min(digits.length, adminPinBoxes.length) - 1]?.focus();
        });
    });

    document.querySelectorAll('[data-admin-photo-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.adminPhotoTab || 'all';
            document.querySelectorAll('[data-admin-photo-tab]').forEach((item) => {
                item.classList.toggle('is-active', item === tab);
            });
            document.querySelectorAll('[data-admin-photo-panel]').forEach((panel) => {
                panel.hidden = panel.dataset.adminPhotoPanel !== target;
            });
        });
    });

    albumChips?.addEventListener('click', (event) => {
        const chip = event.target.closest('[data-upload-album-id]');
        if (!chip) return;
        setActiveAlbum(chip.dataset.uploadAlbumId || '');
    });
    toggleAlbumCreate?.addEventListener('click', () => {
        albumCreateWrap?.classList.toggle('is-open');
        albumCreateForm?.querySelector('input[name="name"]')?.focus();
    });
    albumCreateForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const albumNameInput = albumCreateForm.querySelector('input[name="name"]');
        const albumCreateButton = albumCreateForm.querySelector('button[type="submit"]');
        if (albumCreateButton) albumCreateButton.disabled = true;
        try {
            const album = await createAlbum(albumNameInput?.value || '');
            if (album && albumNameInput) {
                albumNameInput.value = '';
                albumCreateWrap?.classList.remove('is-open');
                input?.click();
            }
        } catch (error) {
            showToast(error.message, 'error', 'Gagal membuat album');
        } finally {
            if (albumCreateButton) albumCreateButton.disabled = false;
        }
    });
    refreshQuickAdd();
    document.querySelector('[data-quick-add-albums]')?.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-quick-album]');
        if (!button || button.disabled) return;
        button.disabled = true;
        try {
            await createAlbum(button.dataset.quickAlbum || '');
        } catch (error) {
            showToast(error.message, 'error', 'Gagal membuat album');
        } finally {
            button.disabled = false;
        }
    });
    photoPagePrev?.addEventListener('click', () => {
        photoPage -= 1;
        renderPhotoPagination();
    });
    photoPageNext?.addEventListener('click', () => {
        photoPage += 1;
        renderPhotoPagination();
    });

    const uploadFile = async (file, index, total) => {
        const data = new FormData();
        data.append('photo', file);
        if (activeUploadAlbumId) {
            data.append('album_id', activeUploadAlbumId);
        }
        data.append(csrfName, csrfHash);
        setStatus(`Upload ${index} dari ${total}: ${file.name}`);
        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: data,
        });
        const json = await response.json().catch(() => ({}));
        updateCsrf(json.csrf_hash);
        if (!response.ok || !json.ok) {
            throw new Error(json.message || `Upload gagal: ${file.name}`);
        }
        addPhoto(json.photo);
    };

    const uploadFiles = async (files) => {
        const queue = Array.from(files || []).filter((file) => file.type.startsWith('image/'));
        if (queue.length === 0) return;
        for (let i = 0; i < queue.length; i++) {
            await uploadFile(queue[i], i + 1, queue.length);
        }
        setStatus(`${queue.length} foto selesai diupload.`);
        showToast(`${queue.length} foto selesai diupload.`, 'success', 'Upload selesai');
        if (input) input.value = '';
    };

    const deleteSinglePhoto = async (button) => {
        const card = button.closest('[data-photo-card]');
        const deleteUrl = card?.dataset.deleteUrl || '';
        if (!card || !deleteUrl) {
            showToast('Link hapus foto tidak ditemukan.', 'error', 'Gagal menghapus');
            return;
        }
        if (typeof window.aaConfirm !== 'function') {
            showToast('Dialog konfirmasi belum siap. Muat ulang halaman lalu coba lagi.', 'error', 'Gagal menghapus');
            return;
        }
        const ok = await window.aaConfirm('Hapus foto ini dari gallery?', {
            title: 'Hapus foto',
            okText: 'Hapus',
            cancelText: 'Batal',
            danger: true,
        });
        if (!ok) return;

        const data = new FormData();
        data.append(csrfName, csrfHash);
        button.disabled = true;
        try {
            const response = await fetch(deleteUrl, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: data,
            });
            const json = await response.json().catch(() => ({}));
            updateCsrf(json.csrf_hash);
            if (!response.ok || !json.ok) {
                throw new Error(json.message || 'Foto belum bisa dihapus.');
            }
            card.remove();
            renderPhotoPagination();
            updateSelectionState();
            setStatus('Foto dihapus dari gallery.');
            showToast('Foto dihapus dari gallery.', 'success', 'Foto dihapus');
        } catch (error) {
            button.disabled = false;
            setStatus(error.message);
            showToast(error.message, 'error', 'Gagal menghapus');
        }
    };

    input?.addEventListener('change', () => uploadFiles(input.files).catch((error) => {
        setStatus(error.message);
        showToast(error.message, 'error', 'Upload gagal');
    }));
    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('is-dragging');
        });
    });
    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('is-dragging');
        });
    });
    dropzone?.addEventListener('drop', (event) => {
        uploadFiles(event.dataTransfer?.files).catch((error) => {
            setStatus(error.message);
            showToast(error.message, 'error', 'Upload gagal');
        });
    });
    selectAll?.addEventListener('change', () => {
        currentPageCheckboxes().forEach((box) => {
            box.checked = selectAll.checked;
        });
        updateSelectionState();
    });
    document.addEventListener('change', (event) => {
        if (event.target && event.target.matches('[data-photo-checkbox]')) {
            updateSelectionState();
        }
    });
    document.addEventListener('click', (event) => {
        const button = event.target?.closest?.('[data-delete-photo]');
        if (button) {
            deleteSinglePhoto(button);
        }
    });
    document.addEventListener('change', async (event) => {
        if (!event.target?.matches?.('[data-photo-album], [data-photo-status]')) return;
        const card = event.target.closest('[data-photo-card]');
        const metaUrl = card?.dataset.metaUrl || '';
        const albumSelect = card?.querySelector('[data-photo-album]');
        const statusSelect = card?.querySelector('[data-photo-status]');
        if (!card || !metaUrl || !statusSelect) return;

        const data = new FormData();
        data.append(csrfName, csrfHash);
        data.append('album_id', albumSelect?.value || '');
        data.append('status', statusSelect.value || 'uploaded');

        event.target.disabled = true;
        try {
            const response = await fetch(metaUrl, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: data,
            });
            const json = await response.json().catch(() => ({}));
            updateCsrf(json.csrf_hash);
            if (!response.ok || !json.ok) {
                throw new Error(json.message || 'Metadata foto belum bisa disimpan.');
            }
            showToast('Album/status foto disimpan.', 'success');
        } catch (error) {
            showToast(error.message, 'error', 'Gagal menyimpan');
        } finally {
            event.target.disabled = false;
        }
    });
    bulkForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const total = photoCheckboxes().filter((box) => box.checked).length;
        if (total === 0) {
            updateSelectionState();
            showToast('Pilih minimal satu foto dahulu.', 'warning', 'Belum ada pilihan');
            return;
        }
        if (typeof window.aaConfirm !== 'function') {
            showToast('Dialog konfirmasi belum siap. Muat ulang halaman lalu coba lagi.', 'error', 'Gagal menghapus');
            return;
        }
        const ok = await window.aaConfirm(`Hapus ${total} foto terpilih dari gallery?`, {
                title: 'Hapus foto terpilih',
                okText: 'Hapus',
                cancelText: 'Batal',
                danger: true,
            });
        if (!ok) {
            return;
        }

        const data = new FormData();
        data.append(csrfName, csrfHash);
        photoCheckboxes().filter((box) => box.checked).forEach((box) => {
            data.append('photo_ids[]', box.value);
        });

        deleteSelected.disabled = true;
        setStatus(`Menghapus ${total} foto terpilih...`);
        try {
            const response = await fetch(bulkForm.action, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: data,
            });
            const json = await response.json().catch(() => ({}));
            updateCsrf(json.csrf_hash);
            if (!response.ok || !json.ok) {
                throw new Error(json.message || 'Foto terpilih belum bisa dihapus.');
            }
            photoCheckboxes().filter((box) => box.checked).forEach((box) => {
                box.closest('.pg-photo-card')?.remove();
            });
            renderPhotoPagination();
            updateSelectionState();
            setStatus(json.message || `${total} foto dihapus dari gallery.`);
            showToast(json.message || `${total} foto dihapus dari gallery.`, 'success', 'Foto dihapus');
        } catch (error) {
            updateSelectionState();
            setStatus(error.message);
            showToast(error.message, 'error', 'Gagal menghapus');
        }
    });
    if (flashSuccess) {
        showToast(flashSuccess, 'success');
    }
    if (flashError) {
        showToast(flashError, 'error');
    }
    if (flashSuccess || flashError) {
        document.querySelectorAll('[data-pg-inline-flash]').forEach((el) => {
            el.style.display = 'none';
        });
    }
    renderPhotoPagination();
    syncAdminPin();
})();
</script>
</body>
</html>
