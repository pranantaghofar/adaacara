<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WhatsApp Templates - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-slate-50 text-slate-950 antialiased">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a class="text-sm font-black text-amber-700" href="<?= site_url('seller') ?>">Dashboard Penjual</a>
            <?= view('components/public_theme_toggle') ?>
        </div>
        <h1 class="mt-4 text-3xl font-black">WhatsApp Follow-up Templates</h1>
        <div class="mt-6 grid gap-4">
            <?php foreach ($templates as $template): ?>
                <article class="rounded-[24px] border border-amber-100 bg-white/90 p-5 shadow-sm">
                    <h2 class="text-lg font-black"><?= esc($template['title']) ?></h2>
                    <textarea class="mt-3 min-h-28 w-full rounded-2xl border border-slate-200 p-4 text-sm leading-6"><?= esc($template['body']) ?></textarea>
                </article>
            <?php endforeach ?>
        </div>
    </main>
</body>
</html>
