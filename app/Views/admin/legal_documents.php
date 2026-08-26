<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Legalitas Perusahaan - Admin</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<?php $documents = $documents ?? []; ?>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Legalitas Perusahaan', 'adminIcon' => 'legal', 'adminActive' => 'legalDocuments']) ?>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <div class="mb-6 rounded-[28px] border border-amber-200 bg-amber-50 p-5">
            <h2 class="text-lg font-black text-amber-950">Upload dokumen PNG saja</h2>
            <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-amber-900">Dokumen legal akan ditampilkan di halaman About Us jika file tersedia. Jangan upload PDF atau data yang belum boleh dipublikasikan.</p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="grid gap-4 md:grid-cols-2">
            <?php foreach ($documents as $key => $document): ?>
                <?php
                    $path = (string) ($document['path'] ?? '');
                    $hasFile = $path !== '';
                ?>
                <article class="rounded-[28px] border border-emerald-100 bg-white/90 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-base font-black"><?= esc((string) ($document['label'] ?? $key)) ?></h2>
                            <p class="mt-1 text-xs font-bold text-slate-500"><?= $hasFile ? 'Tersedia di About Us' : 'Belum ada file publik' ?></p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-black <?= $hasFile ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>"><?= $hasFile ? 'Aktif' : 'Kosong' ?></span>
                    </div>

                    <?php if ($hasFile): ?>
                        <a class="mt-4 block overflow-hidden rounded-3xl border border-slate-100 bg-slate-50" href="<?= base_url($path) ?>" target="_blank" rel="noopener">
                            <img class="h-52 w-full object-contain" src="<?= base_url($path) ?>" alt="<?= esc((string) ($document['label'] ?? $key), 'attr') ?>" loading="lazy">
                        </a>
                        <p class="mt-3 text-xs font-semibold text-slate-500">Update: <?= esc((string) ($document['updated_at'] ?? '-')) ?></p>
                    <?php else: ?>
                        <div class="mt-4 grid h-52 place-items-center rounded-3xl border border-dashed border-slate-200 bg-slate-50 text-sm font-bold text-slate-400">Preview PNG akan muncul di sini</div>
                    <?php endif ?>

                    <form class="mt-4 grid gap-3" action="<?= site_url('admin/legal-documents/upload') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="document_key" value="<?= esc((string) $key, 'attr') ?>">
                        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-black file:text-emerald-700" type="file" name="document" accept="image/png" required>
                        <button class="inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-lg shadow-emerald-700/20" type="submit"><?= $hasFile ? 'Ganti PNG' : 'Upload PNG' ?></button>
                    </form>

                    <?php if ($hasFile): ?>
                        <form class="mt-3" action="<?= site_url('admin/legal-documents/delete/' . $key) ?>" method="post" onsubmit="return confirm('Hapus dokumen legal ini dari About Us?');">
                            <?= csrf_field() ?>
                            <button class="inline-flex h-10 items-center justify-center rounded-2xl border border-rose-200 bg-white px-4 text-sm font-black text-rose-700" type="submit">Hapus Dokumen</button>
                        </form>
                    <?php endif ?>
                </article>
            <?php endforeach ?>
        </section>
    </main>
</body>
</html>
