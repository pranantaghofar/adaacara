<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Promo Assets - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a class="text-sm font-black text-emerald-700" href="<?= site_url('seller') ?>">Dashboard Penjual</a>
            <?= view('components/public_theme_toggle') ?>
        </div>
        <h1 class="mt-4 text-3xl font-black">Promo Assets</h1>
        <p class="mt-2 text-sm text-slate-600">Materi cepat untuk promosi paket undangan digital.</p>
        <section class="mt-6 grid gap-4 md:grid-cols-3">
            <?php foreach ([
                ['Caption Story', 'Undangan digital siap share, desain bisa custom, dan link aktif untuk tamu. Chat sekarang untuk lihat contoh template.'],
                ['Penawaran Singkat', 'Paket undangan digital mulai dari harga hemat. Cocok untuk wedding, ulang tahun, khitanan, gathering, dan event bisnis.'],
                ['Promo Closing', 'Deal hari ini, kami bantu setup awal dan kirim preview pertama lebih cepat. Slot pengerjaan terbatas.'],
            ] as [$title, $copy]): ?>
                <article class="rounded-[24px] border border-emerald-100 bg-white/90 p-5 shadow-sm">
                    <h2 class="text-lg font-black"><?= esc($title) ?></h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600"><?= esc($copy) ?></p>
                </article>
            <?php endforeach ?>
        </section>
    </main>
</body>
</html>
