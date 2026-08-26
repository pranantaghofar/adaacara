<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Templates - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-slate-50 text-slate-950 antialiased">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-amber-700">
                    <a class="no-underline transition hover:text-amber-900" href="<?= site_url('creator/dashboard') ?>">Creator Studio</a>
                    <span aria-hidden="true">&gt;</span>
                    <span>My Templates</span>
                </p>
                <h1 class="text-3xl font-black tracking-tight">My Templates</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <?= view('components/public_theme_toggle') ?>
                <a class="rounded-2xl border border-amber-100 bg-white px-4 py-2 text-sm font-bold" href="<?= site_url('creator/dashboard') ?>">Overview</a>
                <a class="rounded-2xl border border-amber-100 bg-white px-4 py-2 text-sm font-bold" href="<?= site_url('creator/earnings') ?>">Earnings</a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="overflow-hidden rounded-[28px] border border-amber-100 bg-white/85 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-left text-sm">
                    <thead class="bg-amber-50/70 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-4">Template</th>
                            <th class="px-5 py-4">Review</th>
                            <th class="px-5 py-4">Public</th>
                            <th class="px-5 py-4">Dipakai</th>
                            <th class="px-5 py-4">Publish</th>
                            <th class="px-5 py-4">Submitted</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($templates as $template): ?>
                            <tr class="align-middle hover:bg-amber-50/60">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img class="h-16 w-12 rounded-xl object-cover ring-1 ring-slate-200" src="<?= base_url($template['thumbnail'] ?: 'assets/img/logo2.png') ?>" alt="" loading="lazy">
                                        <div>
                                            <p class="font-black"><?= esc($template['name'] ?? '-') ?></p>
                                            <p class="text-xs text-slate-500"><?= esc($template['slug'] ?? '-') ?></p>
                                            <?php if (! empty($template['rejection_reason'])): ?>
                                                <p class="mt-1 text-xs font-bold text-rose-600">Alasan: <?= esc($template['rejection_reason']) ?></p>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><?= esc($template['review_status'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= esc($template['public_status'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= esc((string) ($template['usage_count'] ?? 0)) ?></td>
                                <td class="px-5 py-4"><?= esc((string) ($template['publish_count'] ?? 0)) ?></td>
                                <td class="px-5 py-4 text-slate-500"><?= esc($template['submitted_at'] ?? '-') ?></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold" href="<?= site_url('creator/templates/' . $template['id']) ?>">Detail</a>
                                        <?php if (($template['review_status'] ?? '') === 'approved'): ?>
                                            <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold" href="<?= site_url('templates/preview/' . $template['id']) ?>" target="_blank" rel="noopener">Preview</a>
                                        <?php endif ?>
                                        <?php if (in_array((string) ($template['review_status'] ?? ''), ['rejected', 'pending'], true)): ?>
                                            <form method="post" action="<?= site_url('creator/templates/' . $template['id'] . '/resubmit') ?>">
                                                <?= csrf_field() ?>
                                                <button class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-black text-white" type="submit">Resubmit</button>
                                            </form>
                                        <?php endif ?>
                                        <form method="post" action="<?= site_url('creator/templates/' . $template['id'] . '/archive') ?>" onsubmit="return aaConfirmSubmit(event, 'Arsipkan template ini?', {title: 'Arsipkan Template', okText: 'Arsipkan', cancelText: 'Batal', danger: false});">
                                            <?= csrf_field() ?>
                                            <button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold" type="submit">Archive</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($templates === []): ?>
                            <tr><td class="px-5 py-8 text-slate-500" colspan="7">Belum ada template creator. Simpan desain dari editor dengan tombol Save as Template.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
