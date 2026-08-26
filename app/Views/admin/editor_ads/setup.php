<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Iklan Editor - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Iklan Editor', 'adminIcon' => 'ads', 'adminActive' => 'editorAds']) ?>
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6">
        <section class="rounded-[28px] border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <h2 class="text-xl font-black text-amber-900">Tabel editor_ads belum tersedia</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-amber-800">Jalankan file SQL <code>database/alter_editor_ads.sql</code> di database production, lalu kembali ke halaman ini.</p>
            <a class="mt-5 inline-flex h-11 items-center rounded-2xl bg-amber-600 px-5 text-sm font-black text-white" href="<?= site_url('admin/editor-ads') ?>">Coba Lagi</a>
        </section>
    </main>
</body>
</html>
