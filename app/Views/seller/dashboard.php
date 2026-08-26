<!doctype html>
<?php
    $creatorStatus = $creatorStatus ?? ['status' => 'none', 'display_name' => null];
    $creatorFlowStatus = (string) ($creatorStatus['status'] ?? 'none');
    $creatorDisplayName = (string) ($creatorStatus['display_name'] ?? ($userName ?? 'Creator AdaAcara'));
    $creatorStatusLabel = match ($creatorFlowStatus) {
        'active' => 'Creator Aktif',
        'pending' => 'Menunggu Approve Admin',
        'rejected' => 'Pengajuan Ditolak',
        default => 'Belum Terdaftar',
    };
    $creatorTemplates = array_values($templates ?? []);
    $creatorTemplateCards = array_slice($creatorTemplates, 0, 6);
    $creatorTotalTemplates = (int) ($summary['total'] ?? count($creatorTemplates));
    $creatorApprovedTemplates = (int) ($summary['approved'] ?? 0);
    $creatorPendingTemplates = (int) ($summary['pending'] ?? 0);
    $creatorRejectedTemplates = (int) ($summary['rejected'] ?? 0);
    $creatorUsageCount = (int) ($summary['usage_count'] ?? 0);
    $creatorPublishCount = (int) ($summary['publish_count'] ?? 0);
    $creatorAvailableBalance = (int) ($balance['available'] ?? 0);
    $creatorPendingBalance = (int) ($balance['pending'] ?? 0);
    $aaSellerIcon = static function (string $name, string $class = 'h-5 w-5'): string {
        $icons = [
            'sparkles' => '<path d="M12 3l1.4 3.6L17 8l-3.6 1.4L12 13l-1.4-3.6L7 8l3.6-1.4L12 3Z"/><path d="M19 13l.8 2.2L22 16l-2.2.8L19 19l-.8-2.2L16 16l2.2-.8L19 13Z"/><path d="M5 15l.8 2.2L8 18l-2.2.8L5 21l-.8-2.2L2 18l2.2-.8L5 15Z"/>',
            'grid' => '<rect x="4" y="4" width="6" height="6" rx="1.5"/><rect x="14" y="4" width="6" height="6" rx="1.5"/><rect x="4" y="14" width="6" height="6" rx="1.5"/><rect x="14" y="14" width="6" height="6" rx="1.5"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.7"/><path d="M16 3.2a4 4 0 0 1 0 7.6"/>',
            'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="m5.5 5.1-3.3 9.8A2 2 0 0 0 4.1 17h15.8a2 2 0 0 0 1.9-2.1l-3.3-9.8A2 2 0 0 0 16.6 4H7.4a2 2 0 0 0-1.9 1.1Z"/>',
            'check' => '<circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-5"/>',
            'wallet' => '<path d="M20 7V6a2 2 0 0 0-2-2H5a3 3 0 0 0 0 6h15v8a2 2 0 0 1-2 2H5a3 3 0 0 1-3-3V7"/><path d="M16 13h.01"/>',
            'message' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/>',
            'megaphone' => '<path d="m3 11 18-5v12L3 14v-3Z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/>',
            'arrow' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
            'trend' => '<path d="M3 17 9 11l4 4 8-8"/><path d="M14 7h7v7"/>',
            'empty' => '<path d="M21 15V8a2 2 0 0 0-2-2h-5l-2-2H5a2 2 0 0 0-2 2v9"/><path d="M3 15h6l2 3h2l2-3h6v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>',
            'chevron' => '<path d="m9 18 6-6-6-6"/>',
        ];
        $path = $icons[$name] ?? $icons['sparkles'];
        return '<svg class="' . esc($class, 'attr') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
    };
?>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= ! empty($isCreatorDashboard) ? 'Dashboard Creator' : 'Dashboard Penjual' ?> - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?php if (! empty($isCreatorDashboard)): ?>
        <header class="sticky top-0 z-20 border-b border-violet-100/80 bg-white/85 backdrop-blur-xl">
            <div class="mx-auto flex min-h-16 w-full max-w-[1850px] items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="<?= site_url('creator/dashboard') ?>" class="shrink-0">
                        <img class="h-10 w-auto object-contain drop-shadow-sm" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <?= view('components/public_theme_toggle') ?>
                    <?= view('components/user_nav_dropdown', ['active' => 'creator']) ?>
                </div>
            </div>
        </header>
    <?php else: ?>
        <header class="sticky top-0 z-20 border-b border-violet-100/80 bg-white/85 backdrop-blur-xl">
            <div class="mx-auto flex min-h-16 w-full max-w-[1850px] items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="<?= site_url('dashboard') ?>" class="shrink-0" aria-label="Dashboard AdaAcara">
                        <img class="h-10 w-auto object-contain drop-shadow-sm" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <?= view('components/public_theme_toggle') ?>
                    <?= view('components/user_nav_dropdown', ['active' => 'dashboard']) ?>
                </div>
            </div>
        </header>
    <?php endif ?>

    <main class="mx-auto max-w-[1850px] px-4 py-8 sm:px-6">
        <nav class="<?= ! empty($isCreatorDashboard) ? 'mb-6 flex flex-wrap items-center justify-between gap-3' : 'mb-7 grid gap-4 lg:flex lg:items-end lg:justify-between' ?>">
            <div class="min-w-0">
                <?php if (! empty($isCreatorDashboard)): ?>
                    <p class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-violet-700">
                        <a class="no-underline transition hover:text-violet-900" href="<?= site_url('dashboard') ?>">Dashboard</a>
                        <span aria-hidden="true">&gt;</span>
                        <span>Creator Studio</span>
                    </p>
                    <h1 class="text-3xl font-black tracking-tight">Dashboard Creator</h1>
                    <p class="mt-1 text-sm text-slate-600">Pantau template, komisi, dan withdraw dari sini.</p>
                <?php else: ?>
                    <p class="inline-flex items-center gap-2 rounded-full bg-violet-100/70 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-violet-700">
                        <?= $aaSellerIcon('sparkles', 'h-4 w-4') ?>
                        <a class="no-underline transition hover:text-violet-900" href="<?= site_url('dashboard') ?>">Dashboard</a>
                        <span aria-hidden="true">&gt;</span>
                        <span>Seller Tools</span>
                    </p>
                    <h1 class="mt-4 text-3xl font-black tracking-tight text-[#061f14] sm:text-5xl">Dashboard Penjual</h1>
                    <p class="mt-3 max-w-3xl text-base font-semibold leading-7 text-slate-600">Kelola fitur jualan, promosi, leads, dan operasional event dari satu tempat yang rapi.</p>
                <?php endif ?>
            </div>
            <div class="<?= ! empty($isCreatorDashboard) ? 'flex flex-wrap gap-2' : 'grid gap-2 sm:flex sm:flex-wrap lg:justify-end' ?>">
                <?php if (! empty($isCreatorDashboard)): ?>
                    <a class="rounded-2xl border border-violet-100 bg-white px-4 py-2 text-sm font-bold" href="<?= site_url('dashboard') ?>">Dashboard User</a>
                    <a class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-black text-white" href="<?= site_url('creator/templates') ?>">My Templates</a>
                    <a class="rounded-2xl border border-violet-100 bg-white px-4 py-2 text-sm font-bold" href="<?= site_url('creator/earnings') ?>">Earnings</a>
                <?php else: ?>
                    <a class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-[18px] border border-violet-200 bg-white/70 px-5 text-sm font-black text-slate-800 shadow-sm transition hover:border-violet-300 hover:bg-white sm:w-auto" href="<?= site_url('templates') ?>"><?= $aaSellerIcon('grid', 'h-4 w-4') ?> Kustom Layout</a>
                    <a class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-[18px] bg-[#031b11] px-5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-950 sm:w-auto" href="<?= site_url('seller/leads') ?>"><?= $aaSellerIcon('plus', 'h-4 w-4') ?> Tambah Lead</a>
                <?php endif ?>
            </div>
        </nav>

        <?php if (! empty($isCreatorDashboard)): ?>
        <section class="overflow-hidden rounded-[32px] border border-emerald-900/10 bg-gradient-to-br from-emerald-950 via-emerald-800 to-teal-600 p-6 text-white shadow-[0_22px_70px_rgba(15,118,110,.22)] sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-2xl">
                    <span class="inline-flex rounded-full bg-white/12 px-3 py-1 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-50 ring-1 ring-white/15">Komisi creator 70%</span>
                    <h2 class="mt-4 text-3xl font-black leading-tight tracking-tight sm:text-4xl">Hai <?= esc($creatorDisplayName) ?>, dashboard creator kamu siap dipakai.</h2>
                    <p class="mt-3 max-w-xl text-sm font-semibold leading-6 text-emerald-50/82">Pantau template, status review, performa publish, dan saldo komisi dari satu halaman.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a class="inline-flex h-11 items-center justify-center rounded-2xl bg-white px-5 text-sm font-black text-emerald-950 shadow-sm transition hover:bg-emerald-50" href="<?= site_url('templates') ?>">+ Buat Template Baru</a>
                    <a class="inline-flex h-11 items-center justify-center rounded-2xl border border-white/25 bg-white/10 px-5 text-sm font-black text-white transition hover:bg-white/15" href="<?= site_url('creator/earnings') ?>">Tarik Saldo</a>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ([
                ['label' => 'Saldo Tersedia', 'value' => 'Rp ' . number_format($creatorAvailableBalance, 0, ',', '.'), 'caption' => 'Siap withdraw', 'tone' => 'emerald'],
                ['label' => 'Saldo Pending', 'value' => 'Rp ' . number_format($creatorPendingBalance, 0, ',', '.'), 'caption' => 'Menunggu validasi', 'tone' => 'amber'],
                ['label' => 'Template Aktif', 'value' => $creatorApprovedTemplates, 'caption' => $creatorTotalTemplates . ' total template', 'tone' => 'sky'],
                ['label' => 'Total Publish', 'value' => $creatorPublishCount, 'caption' => $creatorUsageCount . ' kali dipakai', 'tone' => 'rose'],
            ] as $stat): ?>
                <?php
                    $toneClasses = [
                        'emerald' => 'bg-emerald-600 text-white',
                        'amber' => 'bg-amber-500 text-white',
                        'sky' => 'bg-sky-500 text-white',
                        'rose' => 'bg-rose-500 text-white',
                    ];
                ?>
                <article class="rounded-[26px] border border-emerald-900/10 bg-white/82 p-5 shadow-sm ring-1 ring-white/60">
                    <div class="flex items-start justify-between gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl <?= esc($toneClasses[$stat['tone']] ?? $toneClasses['emerald'], 'attr') ?> text-sm font-black"><?= esc(substr((string) $stat['label'], 0, 1)) ?></span>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-emerald-700">Aktif</span>
                    </div>
                    <p class="mt-4 text-xs font-black uppercase tracking-[0.12em] text-slate-500"><?= esc($stat['label']) ?></p>
                    <p class="mt-2 text-2xl font-black tracking-tight"><?= esc((string) $stat['value']) ?></p>
                    <p class="mt-1 text-xs font-bold text-slate-500"><?= esc((string) $stat['caption']) ?></p>
                </article>
            <?php endforeach ?>
        </section>

        <section class="mt-6 grid gap-5 lg:grid-cols-[1.25fr_.75fr]">
            <article class="rounded-[28px] border border-emerald-900/10 bg-white/82 p-6 shadow-sm ring-1 ring-white/60">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-black tracking-tight">Overview Template</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Ringkasan performa dari data template kamu.</p>
                    </div>
                    <a class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-sm font-black text-emerald-800 transition hover:border-emerald-400" href="<?= site_url('creator/templates') ?>">Lihat semua</a>
                </div>
                <?php
                    $overviewBars = [
                        ['label' => 'Approved/Public', 'value' => $creatorApprovedTemplates, 'max' => max(1, $creatorTotalTemplates), 'class' => 'from-emerald-700 to-teal-400'],
                        ['label' => 'Pending Review', 'value' => $creatorPendingTemplates, 'max' => max(1, $creatorTotalTemplates), 'class' => 'from-amber-500 to-yellow-300'],
                        ['label' => 'Rejected', 'value' => $creatorRejectedTemplates, 'max' => max(1, $creatorTotalTemplates), 'class' => 'from-rose-500 to-pink-300'],
                        ['label' => 'Dipakai', 'value' => $creatorUsageCount, 'max' => max(1, $creatorUsageCount, $creatorPublishCount), 'class' => 'from-sky-600 to-cyan-300'],
                        ['label' => 'Publish', 'value' => $creatorPublishCount, 'max' => max(1, $creatorUsageCount, $creatorPublishCount), 'class' => 'from-violet-600 to-fuchsia-300'],
                    ];
                ?>
                <div class="mt-6 grid gap-4">
                    <?php foreach ($overviewBars as $bar): ?>
                        <?php $width = max(4, min(100, (int) round(((int) $bar['value'] / max(1, (int) $bar['max'])) * 100))); ?>
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-xs font-black">
                                <span class="text-slate-600"><?= esc($bar['label']) ?></span>
                                <span class="text-slate-950"><?= esc((string) $bar['value']) ?></span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r <?= esc($bar['class'], 'attr') ?>" style="width: <?= $width ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </article>

            <aside class="grid gap-5">
                <article class="rounded-[28px] border border-emerald-900/10 bg-white/82 p-6 shadow-sm ring-1 ring-white/60">
                    <h2 class="text-xl font-black tracking-tight">Goals Bulan Ini</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Target ringan berbasis data yang tersedia.</p>
                    <?php foreach ([
                        ['label' => 'Template Approved', 'value' => $creatorApprovedTemplates, 'target' => max(5, $creatorApprovedTemplates)],
                        ['label' => 'Total Publish', 'value' => $creatorPublishCount, 'target' => max(20, $creatorPublishCount)],
                        ['label' => 'Template Baru', 'value' => $creatorTotalTemplates, 'target' => max(5, $creatorTotalTemplates)],
                    ] as $goal): ?>
                        <?php $goalWidth = max(4, min(100, (int) round(((int) $goal['value'] / max(1, (int) $goal['target'])) * 100))); ?>
                        <div class="mt-4">
                            <div class="mb-2 flex items-center justify-between text-xs font-black">
                                <span class="text-slate-600"><?= esc($goal['label']) ?></span>
                                <span><?= esc((string) $goal['value']) ?> / <?= esc((string) $goal['target']) ?></span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-700 via-teal-400 to-violet-300" style="width: <?= $goalWidth ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </article>

                <article class="rounded-[28px] border border-emerald-900/10 bg-white/82 p-5 shadow-sm ring-1 ring-white/60">
                    <h2 class="text-lg font-black tracking-tight">Aksi Cepat</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <a class="grid min-h-24 place-items-center rounded-2xl border border-emerald-100 bg-emerald-50/70 px-3 text-center text-sm font-black text-emerald-900 transition hover:border-emerald-400" href="<?= site_url('templates') ?>">Editor</a>
                        <a class="grid min-h-24 place-items-center rounded-2xl border border-violet-100 bg-violet-50/70 px-3 text-center text-sm font-black text-violet-900 transition hover:border-violet-300" href="<?= site_url('creator/templates') ?>">Template</a>
                        <a class="grid min-h-24 place-items-center rounded-2xl border border-sky-100 bg-sky-50/70 px-3 text-center text-sm font-black text-sky-900 transition hover:border-sky-400" href="<?= site_url('creator/earnings') ?>">Earnings</a>
                        <a class="grid min-h-24 place-items-center rounded-2xl border border-rose-100 bg-rose-50/70 px-3 text-center text-sm font-black text-rose-900 transition hover:border-rose-400" href="<?= site_url('dashboard') ?>">Dashboard User</a>
                    </div>
                </article>
            </aside>
        </section>

        <section class="mt-6 rounded-[30px] border border-emerald-900/10 bg-white/82 p-5 shadow-sm ring-1 ring-white/60 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black tracking-tight">Template Saya</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Kelola, cek status review, dan pantau pemakaian template.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:border-emerald-400" href="<?= site_url('creator/templates') ?>">Semua Template</a>
                    <a class="rounded-2xl bg-emerald-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800" href="<?= site_url('templates') ?>">+ Template Baru</a>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($creatorTemplateCards as $template): ?>
                    <?php
                        $review = (string) ($template['review_status'] ?? 'pending');
                        $public = (string) ($template['public_status'] ?? 'private');
                        $badgeClass = match ($review) {
                            'approved' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                            'rejected' => 'bg-rose-50 text-rose-700 ring-rose-100',
                            default => 'bg-amber-50 text-amber-800 ring-amber-100',
                        };
                        $thumb = (string) ($template['thumbnail'] ?? '');
                    ?>
                    <article class="overflow-hidden rounded-[26px] border border-slate-200/70 bg-white shadow-sm">
                        <div class="relative aspect-[4/5] bg-gradient-to-br from-emerald-50 via-amber-50 to-sky-50">
                            <?php if ($thumb !== ''): ?>
                                <img class="h-full w-full object-cover" src="<?= base_url($thumb) ?>" alt="<?= esc($template['name'] ?? 'Template', 'attr') ?>" loading="lazy">
                            <?php endif ?>
                            <span class="absolute left-3 top-3 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wide ring-1 <?= esc($badgeClass, 'attr') ?>"><?= esc($review) ?></span>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400"><?= esc($public) ?></p>
                            <h3 class="mt-1 line-clamp-2 min-h-10 text-base font-black leading-tight"><?= esc($template['name'] ?? '-') ?></h3>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-2xl bg-slate-50 p-3 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Dipakai</p>
                                    <p class="mt-1 text-lg font-black"><?= esc((string) ($template['usage_count'] ?? 0)) ?></p>
                                </div>
                                <div class="rounded-2xl bg-emerald-50 p-3 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-wide text-emerald-600">Publish</p>
                                    <p class="mt-1 text-lg font-black text-emerald-900"><?= esc((string) ($template['publish_count'] ?? 0)) ?></p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a class="inline-flex flex-1 items-center justify-center rounded-2xl bg-emerald-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800" href="<?= site_url('creator/templates/' . $template['id']) ?>">Detail</a>
                                <?php if ($review === 'approved'): ?>
                                    <a class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700" href="<?= site_url('templates/preview/' . $template['id']) ?>" target="_blank" rel="noopener">Preview</a>
                                <?php endif ?>
                                <?php if (in_array($review, ['rejected', 'pending'], true)): ?>
                                    <form method="post" action="<?= site_url('creator/templates/' . $template['id'] . '/resubmit') ?>">
                                        <?= csrf_field() ?>
                                        <button class="rounded-2xl border border-amber-100 bg-amber-50 px-3 py-2 text-xs font-black text-amber-800" type="submit">Resubmit</button>
                                    </form>
                                <?php endif ?>
                                <form method="post" action="<?= site_url('creator/templates/' . $template['id'] . '/archive') ?>" onsubmit="return aaConfirmSubmit(event, 'Arsipkan template ini?', {title: 'Arsipkan Template', okText: 'Arsipkan', cancelText: 'Batal', danger: false});">
                                    <?= csrf_field() ?>
                                    <button class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600" type="submit">Archive</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach ?>
                <?php if ($creatorTemplateCards === []): ?>
                    <div class="rounded-[26px] border border-dashed border-emerald-200 bg-emerald-50/50 p-6 text-sm font-semibold text-slate-600 sm:col-span-2 xl:col-span-3">
                        Belum ada template creator. Mulai dari halaman template, buka editor, lalu gunakan tombol Save as Template.
                    </div>
                <?php endif ?>
            </div>
        </section>

        <section class="mt-6 grid gap-5 lg:grid-cols-[1fr_.72fr]">
            <article class="rounded-[28px] border border-emerald-900/10 bg-white/82 p-6 shadow-sm ring-1 ring-white/60">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-black tracking-tight">Status Review</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Pantau progres template yang sudah dikirim.</p>
                    </div>
                    <a class="text-sm font-black text-emerald-800" href="<?= site_url('creator/templates') ?>">Lihat semua</a>
                </div>
                <div class="mt-5 grid gap-3">
                    <?php foreach (array_slice($creatorTemplates, 0, 5) as $template): ?>
                        <a class="grid gap-2 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-emerald-200 sm:grid-cols-[1fr_auto] sm:items-center" href="<?= site_url('creator/templates/' . $template['id']) ?>">
                            <div>
                                <p class="font-black"><?= esc($template['name'] ?? '-') ?></p>
                                <p class="mt-1 text-xs font-semibold text-slate-500"><?= esc($template['submitted_at'] ?? $template['updated_at'] ?? '-') ?></p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-slate-700 ring-1 ring-slate-200"><?= esc($template['review_status'] ?? '-') ?> / <?= esc($template['public_status'] ?? '-') ?></span>
                        </a>
                    <?php endforeach ?>
                    <?php if ($creatorTemplates === []): ?>
                        <p class="rounded-2xl border border-dashed border-slate-200 p-5 text-sm text-slate-500">Belum ada status review.</p>
                    <?php endif ?>
                </div>
            </article>

            <aside class="rounded-[28px] border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-6 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-700">Tips Creator</p>
                <h2 class="mt-3 text-xl font-black leading-tight">Publish template secara rutin untuk menaikkan peluang approved dan dipakai.</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Gunakan thumbnail yang jelas, nama template spesifik, dan cek preview sebelum kirim review.</p>
                <a class="mt-5 inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-black text-white" href="<?= site_url('templates') ?>">Mulai sekarang</a>
            </aside>
        </section>
        <?php else: ?>
        <?php
            $sellerPipeline = $sellerStats['pipeline'] ?? [];
            $sellerTotal = (int) ($sellerStats['total'] ?? 0);
            $sellerNew = (int) ($sellerStats['new'] ?? 0);
            $sellerDeal = (int) ($sellerStats['deal'] ?? 0);
            $sellerRevenue = (int) ($sellerStats['estimated_revenue'] ?? 0);
            $sellerStatsCards = [
                ['label' => 'Total Lead', 'value' => $sellerTotal, 'caption' => '+' . $sellerNew . ' minggu ini', 'icon' => 'users', 'tone' => 'sky'],
                ['label' => 'Lead Baru', 'value' => $sellerNew, 'caption' => $sellerNew > 0 ? 'Perlu follow-up' : 'Belum ada', 'icon' => 'inbox', 'tone' => 'violet'],
                ['label' => 'Deal Closed', 'value' => $sellerDeal, 'caption' => 'Konversi ' . ($sellerTotal > 0 ? round(($sellerDeal / max(1, $sellerTotal)) * 100) : 0) . '%', 'icon' => 'check', 'tone' => 'emerald'],
                ['label' => 'Estimasi Omzet', 'value' => 'Rp ' . number_format($sellerRevenue, 0, ',', '.'), 'caption' => 'Bulan berjalan', 'icon' => 'wallet', 'tone' => 'orange'],
            ];
            $sellerToneClasses = [
                'sky' => ['wrap' => 'bg-sky-50 text-sky-500 ring-sky-100', 'dot' => 'bg-sky-500'],
                'violet' => ['wrap' => 'bg-violet-50 text-violet-500 ring-violet-100', 'dot' => 'bg-violet-500'],
                'emerald' => ['wrap' => 'bg-emerald-50 text-emerald-500 ring-emerald-100', 'dot' => 'bg-emerald-500'],
                'orange' => ['wrap' => 'bg-orange-50 text-orange-500 ring-orange-100', 'dot' => 'bg-orange-500'],
                'amber' => ['wrap' => 'bg-amber-50 text-amber-500 ring-amber-100', 'dot' => 'bg-amber-500'],
                'teal' => ['wrap' => 'bg-teal-50 text-teal-500 ring-teal-100', 'dot' => 'bg-teal-500'],
                'rose' => ['wrap' => 'bg-rose-50 text-rose-500 ring-rose-100', 'dot' => 'bg-rose-500'],
            ];
            $sellerPipelineCards = [
                ['key' => 'new', 'label' => 'New', 'tone' => 'sky'],
                ['key' => 'contacted', 'label' => 'Contacted', 'tone' => 'violet'],
                ['key' => 'negotiation', 'label' => 'Negotiation', 'tone' => 'violet'],
                ['key' => 'deal', 'label' => 'Deal', 'tone' => 'emerald'],
                ['key' => 'production', 'label' => 'Production', 'tone' => 'orange'],
                ['key' => 'done', 'label' => 'Done', 'tone' => 'teal'],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'tone' => 'rose'],
            ];
        ?>
        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($sellerStatsCards as $card): ?>
                <?php $tone = $sellerToneClasses[$card['tone']] ?? $sellerToneClasses['emerald']; ?>
                <article class="rounded-[28px] border border-violet-100/80 bg-[#fbf8ff]/88 p-6 shadow-[0_12px_32px_rgba(15,23,42,.07)] ring-1 ring-white/55">
                    <div class="flex items-start justify-between gap-3">
                        <span class="grid h-14 w-14 place-items-center rounded-[24px] ring-4 <?= esc($tone['wrap'], 'attr') ?>"><?= $aaSellerIcon($card['icon'], 'h-6 w-6') ?></span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-[#eef1e6] px-3 py-1 text-[11px] font-black text-slate-500"><?= $aaSellerIcon('trend', 'h-3.5 w-3.5') ?> Live</span>
                    </div>
                    <p class="mt-5 text-[12px] font-black uppercase tracking-[0.22em] text-slate-500"><?= esc($card['label']) ?></p>
                    <p class="mt-3 text-4xl font-black tracking-tight text-[#061f14]"><?= esc((string) $card['value']) ?></p>
                    <p class="mt-2 text-sm font-semibold text-slate-600"><?= esc($card['caption']) ?></p>
                </article>
            <?php endforeach ?>
        </section>

        <section class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(320px,.7fr)]">
            <article class="min-w-0 rounded-[30px] border border-violet-100/80 bg-[#fbf8ff]/88 p-5 shadow-[0_14px_36px_rgba(15,23,42,.07)] ring-1 ring-white/55 sm:p-7">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-black tracking-tight text-[#061f14] sm:text-2xl">Lead Inbox & Pipeline</h2>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-black text-emerald-700">Realtime</span>
                        </div>
                        <p class="mt-2 text-base font-semibold text-slate-600">Kelola calon customer, status follow-up, dan order cepat.</p>
                    </div>
                    <a class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-[18px] bg-[#031b11] px-5 text-sm font-black text-white transition hover:bg-emerald-950 sm:w-auto" href="<?= site_url('seller/leads') ?>">Buka Lead Inbox <?= $aaSellerIcon('chevron', 'h-4 w-4') ?></a>
                </div>

                <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <?php foreach ($sellerPipelineCards as $pipe): ?>
                        <?php $tone = $sellerToneClasses[$pipe['tone']] ?? $sellerToneClasses['emerald']; ?>
                        <div class="min-h-[128px] rounded-[22px] border border-[#ded6ee] bg-[#fbf8ff]/80 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="h-2.5 w-2.5 rounded-full <?= esc($tone['dot'], 'attr') ?>"></span>
                                <span class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-500"><?= esc($pipe['label']) ?></span>
                            </div>
                            <p class="mt-7 text-4xl font-black tracking-tight text-[#061f14]"><?= esc((string) ($sellerPipeline[$pipe['key']] ?? 0)) ?></p>
                        </div>
                    <?php endforeach ?>
                </div>

                <div class="mt-7 grid gap-4 rounded-[22px] border border-dashed border-violet-200 bg-[#fbf8ff]/72 p-5 sm:flex sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-start gap-4 sm:items-center">
                        <span class="grid h-12 w-12 place-items-center rounded-[18px] bg-violet-500 text-white"><?= $aaSellerIcon('trend', 'h-6 w-6') ?></span>
                        <div class="min-w-0">
                            <h3 class="font-black text-[#061f14]">Tips: balas lead &lt; 5 menit</h3>
                            <p class="mt-1 text-sm font-semibold text-slate-600">Lead yang dibalas cepat punya peluang closing 3x lebih besar.</p>
                        </div>
                    </div>
                    <a class="text-sm font-black text-violet-700" href="<?= site_url('seller/whatsapp-templates') ?>">Pelajari</a>
                </div>
            </article>

            <aside class="min-w-0 rounded-[30px] border border-violet-100/80 bg-[#fbf8ff]/88 p-5 shadow-[0_14px_36px_rgba(15,23,42,.07)] ring-1 ring-white/55 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black tracking-tight text-[#061f14]">Tools Jualan Cepat</h2>
                        <p class="mt-2 text-base font-semibold text-slate-600">Shortcut yang sering dipakai.</p>
                    </div>
                    <span class="text-violet-500"><?= $aaSellerIcon('sparkles', 'h-7 w-7') ?></span>
                </div>

                <div class="mt-7 grid gap-3">
                    <a class="flex min-h-[76px] items-center justify-between gap-3 rounded-[22px] border border-[#e8dfcc] bg-[#faf7ea]/80 px-4 py-4 transition hover:border-emerald-300 hover:bg-white sm:gap-4 sm:px-5" href="<?= site_url('seller/whatsapp-templates') ?>">
                        <span class="flex min-w-0 items-center gap-3 sm:gap-4">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-[18px] bg-emerald-100 text-emerald-600"><?= $aaSellerIcon('message', 'h-5 w-5') ?></span>
                            <span class="min-w-0">
                                <strong class="block text-sm font-black leading-tight text-[#061f14] sm:text-base">WhatsApp Follow-up Templates</strong>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-slate-600 sm:text-sm">Pesan siap pakai untuk semua status lead</span>
                            </span>
                        </span>
                        <?= $aaSellerIcon('chevron', 'hidden h-5 w-5 shrink-0 text-slate-500 sm:block') ?>
                    </a>
                    <a class="flex min-h-[76px] items-center justify-between gap-3 rounded-[22px] border border-[#e8dfcc] bg-[#faf7ea]/80 px-4 py-4 transition hover:border-rose-300 hover:bg-white sm:gap-4 sm:px-5" href="<?= site_url('seller/promo-assets') ?>">
                        <span class="flex min-w-0 items-center gap-3 sm:gap-4">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-[18px] bg-rose-100 text-rose-500"><?= $aaSellerIcon('megaphone', 'h-5 w-5') ?></span>
                            <span class="min-w-0">
                                <strong class="block text-sm font-black leading-tight text-[#061f14] sm:text-base">Promo Assets</strong>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-slate-600 sm:text-sm">Gambar & caption promo siap upload</span>
                            </span>
                        </span>
                        <?= $aaSellerIcon('chevron', 'hidden h-5 w-5 shrink-0 text-slate-500 sm:block') ?>
                    </a>
                    <a class="flex min-h-[76px] items-center justify-between gap-3 rounded-[22px] border border-[#e8dfcc] bg-[#faf7ea]/80 px-4 py-4 transition hover:border-indigo-300 hover:bg-white sm:gap-4 sm:px-5" href="<?= site_url('templates') ?>">
                        <span class="flex min-w-0 items-center gap-3 sm:gap-4">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-[18px] bg-indigo-100 text-indigo-500"><?= $aaSellerIcon('file', 'h-5 w-5') ?></span>
                            <span class="min-w-0">
                                <strong class="block text-sm font-black leading-tight text-[#061f14] sm:text-base">Buat Draft Undangan</strong>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-slate-600 sm:text-sm">Mulai dari template, lalu edit di editor</span>
                            </span>
                        </span>
                        <?= $aaSellerIcon('chevron', 'hidden h-5 w-5 shrink-0 text-slate-500 sm:block') ?>
                    </a>
                </div>

                <div class="mt-6 rounded-[24px] bg-gradient-to-br from-[#052414] to-[#2d4336] p-6 text-white">
                    <p class="text-[12px] font-black uppercase tracking-[0.22em] text-white/65">Upgrade</p>
                    <h3 class="mt-3 text-xl font-black leading-tight">Auto follow-up + AI reply</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-white/70">Bebas balas lead pakai template AI, hemat waktu 4 jam/minggu.</p>
                    <a class="mt-5 inline-flex h-10 items-center justify-center gap-2 rounded-full bg-white px-4 text-sm font-black text-[#061f14]" href="<?= site_url('plans') ?>">Aktifkan <?= $aaSellerIcon('arrow', 'h-4 w-4') ?></a>
                </div>
            </aside>
        </section>

        <section class="mt-7 rounded-[30px] border border-violet-100/80 bg-[#fbf8ff]/88 p-6 shadow-[0_14px_36px_rgba(15,23,42,.07)] ring-1 ring-white/55 sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-[#061f14]">Customer Terbaru</h2>
                    <p class="mt-2 text-base font-semibold text-slate-600">Lead yang baru masuk akan tampil di sini.</p>
                </div>
                <a class="text-sm font-black text-violet-700" href="<?= site_url('seller/leads') ?>">Lihat semua</a>
            </div>

            <?php if (($recentLeads ?? []) !== []): ?>
                <div class="mt-6 grid gap-3">
                    <?php foreach (($recentLeads ?? []) as $lead): ?>
                        <a class="grid gap-3 rounded-[22px] border border-[#ded6ee] bg-[#fbf8ff]/80 p-4 transition hover:border-violet-300 hover:bg-white sm:grid-cols-[1fr_auto] sm:items-center" href="<?= site_url('seller/leads/' . $lead['id']) ?>">
                            <span>
                                <strong class="text-base font-black text-[#061f14]"><?= esc($lead['customer_name']) ?></strong>
                                <span class="mt-1 block text-sm font-semibold text-slate-600"><?= esc($lead['event_type'] ?? '-') ?> · <?= esc($lead['whatsapp'] ?? '-') ?></span>
                            </span>
                            <span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-[#ded6ee]"><?= esc($lead['status'] ?? 'new') ?></span>
                        </a>
                    <?php endforeach ?>
                </div>
            <?php else: ?>
                <div class="mt-7 grid min-h-[260px] place-items-center rounded-[24px] border border-dashed border-[#d9cfb8] bg-[#faf7ea]/50 p-6 text-center">
                    <div class="max-w-md">
                        <span class="mx-auto grid h-16 w-16 place-items-center rounded-[24px] bg-[#f4efe0] text-slate-600 shadow-sm"><?= $aaSellerIcon('empty', 'h-7 w-7') ?></span>
                        <h3 class="mt-5 text-lg font-black text-[#061f14]">Belum ada lead</h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Tambahkan calon customer pertama dari Lead Inbox untuk mulai melacak follow-up & estimasi omzet.</p>
                        <a class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-[18px] bg-[#031b11] px-5 text-sm font-black text-white transition hover:bg-emerald-950" href="<?= site_url('seller/leads') ?>"><?= $aaSellerIcon('plus', 'h-4 w-4') ?> Tambah Lead Pertama</a>
                    </div>
                </div>
            <?php endif ?>
        </section>
        <?php endif ?>
    </main>
    <?= view('components/site_footer') ?>
</body>
</html>
