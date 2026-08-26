<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Template - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-900 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Setup Template', 'adminIcon' => 'template', 'adminActive' => 'templates']) ?>
    <main class="mx-auto max-w-3xl px-4 py-10">
        <section class="w-full rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-700">Setup diperlukan</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Admin Template Belum Siap</h1>
            <p class="mt-4 text-slate-600"><?= esc($message ?? 'Tabel template belum tersedia.') ?></p>
            <p class="mt-3 text-sm text-slate-500">Jalankan SQL modul admin template di phpMyAdmin, lalu buka halaman ini kembali.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold" href="<?= site_url('admin/templates') ?>">Coba Lagi</a>
            </div>
        </section>
    </main>
</body>
</html>
