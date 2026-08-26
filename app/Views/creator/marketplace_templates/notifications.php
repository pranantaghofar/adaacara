<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifikasi Creator - Ada Acara</title>
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
                <h1 class="text-3xl font-black tracking-tight">Notifikasi</h1>
                <p class="mt-2 text-sm text-slate-600">Update internal untuk <?= esc($creator['display_name']) ?>.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?= view('components/public_theme_toggle') ?>
                <a class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-sm font-bold shadow-sm transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('creator/marketplace-templates') ?>">Kembali</a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>

        <section class="space-y-4">
            <?php foreach ($notifications as $notification): ?>
                <article class="rounded-[28px] border <?= empty($notification['read_at']) ? 'border-emerald-200 bg-white' : 'border-slate-100 bg-white/80' ?> p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-black"><?= esc($notification['title']) ?></p>
                            <p class="mt-2 text-sm leading-6 text-slate-600"><?= esc($notification['message']) ?></p>
                            <p class="mt-2 text-xs text-slate-400"><?= esc($notification['created_at'] ?? '-') ?></p>
                        </div>
                        <?php if (empty($notification['read_at'])): ?>
                            <form action="<?= site_url('creator/notifications/' . $notification['id'] . '/read') ?>" method="post">
                                <?= csrf_field() ?>
                                <button class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-xs font-black text-emerald-700 transition hover:border-emerald-600" type="submit">Tandai Dibaca</button>
                            </form>
                        <?php endif ?>
                    </div>
                </article>
            <?php endforeach ?>
            <?php if ($notifications === []): ?>
                <div class="rounded-[28px] border border-emerald-100 bg-white/90 p-8 text-center text-slate-600 shadow-sm">Belum ada notifikasi.</div>
            <?php endif ?>
        </section>
    </main>
</body>
</html>
