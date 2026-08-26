<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Akses Terbatas') ?> - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-900 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Akses Terbatas', 'adminIcon' => 'dashboard', 'adminActive' => (string) ($feature ?? 'dashboard')]) ?>
    <main class="mx-auto flex min-h-[calc(100vh-88px)] max-w-4xl items-center px-4 py-10 sm:px-6">
        <section class="w-full rounded-3xl border border-amber-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                    <path d="M8 11V8a4 4 0 0 1 8 0v3"></path>
                </svg>
            </div>
            <p class="mt-6 text-xs font-black uppercase tracking-[0.22em] text-amber-700">Admin Access</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950"><?= esc($title ?? 'Akses Terbatas') ?></h1>
            <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-slate-600"><?= esc($message ?? 'Anda tidak memiliki izin untuk membuka fitur admin ini.') ?></p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-700 px-5 text-sm font-black text-white transition hover:bg-emerald-800" href="<?= site_url('admin') ?>">Kembali ke Dashboard</a>
                <a class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-black text-slate-700 transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('dashboard') ?>">User Dashboard</a>
            </div>
        </section>
    </main>
</body>
</html>
