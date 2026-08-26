<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Template Seller - Admin</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Review Template Seller', 'adminIcon' => 'review', 'adminActive' => 'sellerTemplates']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper('admin_permission');
            $canReviewTemplates = admin_can('admin.templates.review');
        ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <form class="mb-4 grid gap-3 rounded-[24px] border border-emerald-100 bg-white/90 p-4 shadow-sm lg:grid-cols-[1.3fr_190px_190px_auto]" method="get" action="<?= site_url('admin/seller-templates') ?>">
            <input class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="search" name="q" value="<?= esc($search ?? '', 'attr') ?>" placeholder="Cari template, slug, owner, email">
            <select class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="status">
                <option value="">Semua status</option>
                <?php foreach (['pending', 'approved', 'rejected'] as $item): ?>
                    <option value="<?= esc($item, 'attr') ?>" <?= ($status ?? '') === $item ? 'selected' : '' ?>><?= esc(ucfirst($item)) ?></option>
                <?php endforeach ?>
            </select>
            <select class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="public_status">
                <option value="">Semua public</option>
                <?php foreach (['private', 'public', 'archived'] as $item): ?>
                    <option value="<?= esc($item, 'attr') ?>" <?= ($publicStatus ?? '') === $item ? 'selected' : '' ?>><?= esc(ucfirst($item)) ?></option>
                <?php endforeach ?>
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white" type="submit">Cari</button>
                <a class="inline-flex h-11 items-center rounded-2xl border border-emerald-100 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('admin/seller-templates') ?>">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-[28px] border border-emerald-100 bg-white/85 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] text-left text-sm">
                    <thead class="bg-emerald-50/70 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr><th class="px-5 py-4">Template</th><th>Owner</th><th>Plan</th><th>Review</th><th>Public</th><th>Submitted</th><th>Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($templates as $template): ?>
                            <tr class="align-middle hover:bg-emerald-50/60">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img class="h-16 w-12 rounded-xl object-cover ring-1 ring-slate-200" src="<?= base_url($template['thumbnail'] ?: 'assets/img/logo2.png') ?>" alt="" loading="lazy">
                                        <div><p class="font-black"><?= esc($template['name'] ?? '-') ?></p><p class="text-xs text-slate-500"><?= esc($template['slug'] ?? '-') ?></p></div>
                                    </div>
                                </td>
                                <td><?= esc($template['owner_name'] ?? '-') ?><br><span class="text-xs text-slate-500"><?= esc($template['owner_email'] ?? '-') ?></span></td>
                                <td><?= esc($template['seller_plan_name'] ?? '-') ?></td>
                                <td><?= esc($template['review_status'] ?? '-') ?></td>
                                <td><?= esc($template['public_status'] ?? '-') ?></td>
                                <td><?= esc($template['submitted_at'] ?? '-') ?></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold" href="<?= site_url('admin/seller-templates/' . $template['id']) ?>">Detail</a>
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold" href="<?= site_url('templates/preview/' . $template['id']) ?>" target="_blank" rel="noopener">Preview</a>
                                        <?php if ($canReviewTemplates): ?>
                                        <form method="post" action="<?= site_url('admin/seller-templates/' . $template['id'] . '/approve') ?>">
                                            <?= csrf_field() ?>
                                            <button class="rounded-xl bg-emerald-700 px-3 py-2 text-xs font-black text-white" type="submit">Approve</button>
                                        </form>
                                        <?php endif ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($templates === []): ?><tr><td class="px-5 py-8 text-slate-500" colspan="7">Belum ada template seller.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
