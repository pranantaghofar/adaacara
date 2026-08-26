<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Feedback - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-700">Creator</p>
                <h1 class="text-3xl font-black tracking-tight">Review Feedback</h1>
                <p class="mt-2 text-sm text-slate-600">Template yang perlu diperbaiki oleh <?= esc($creator['display_name']) ?>.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?= view('components/public_theme_toggle') ?>
                <a class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-sm font-bold shadow-sm transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('creator/marketplace-templates') ?>">Kembali</a>
            </div>
        </div>

        <section class="space-y-4">
            <?php foreach ($templates as $template): ?>
                <article class="rounded-[28px] border border-rose-100 bg-white/90 p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-black text-rose-700 ring-1 ring-rose-200"><?= esc($template['marketplace_status']) ?></span>
                            <h2 class="mt-3 text-xl font-black"><?= esc($template['title']) ?></h2>
                            <p class="mt-2 text-sm leading-6 text-rose-700"><?= esc($template['rejection_reason'] ?: 'Admin meminta revisi pada template ini.') ?></p>
                        </div>
                        <a class="rounded-2xl bg-emerald-700 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800" href="<?= site_url('creator/marketplace-templates/' . $template['id']) ?>">Perbaiki</a>
                    </div>
                </article>
            <?php endforeach ?>
            <?php if ($templates === []): ?>
                <div class="rounded-[28px] border border-emerald-100 bg-white/90 p-8 text-center text-slate-600 shadow-sm">Tidak ada feedback yang perlu dikerjakan.</div>
            <?php endif ?>
        </section>
    </main>
</body>
</html>
