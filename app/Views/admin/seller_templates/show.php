<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Template Seller - Admin</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Detail Template Seller', 'adminIcon' => 'review', 'adminActive' => 'sellerTemplates']) ?>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <a class="text-sm font-bold text-emerald-700" href="<?= site_url('admin/seller-templates') ?>">← Kembali ke review</a>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>
        <section class="mt-5 grid gap-6 lg:grid-cols-[280px_1fr]">
            <img class="w-full rounded-[28px] border border-emerald-100 bg-white object-cover shadow-sm" src="<?= base_url($template['thumbnail'] ?: 'assets/img/logo2.png') ?>" alt="" loading="lazy">
            <div class="rounded-[28px] border border-emerald-100 bg-white/85 p-6 shadow-sm">
                <h1 class="text-3xl font-black tracking-tight"><?= esc($template['name'] ?? '-') ?></h1>
                <p class="mt-1 text-sm text-slate-500">Owner: <?= esc($template['owner_name'] ?? '-') ?> · <?= esc($template['owner_email'] ?? '-') ?></p>
                <p class="mt-4 text-sm leading-6 text-slate-600"><?= esc($template['description'] ?? '-') ?></p>
                <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                    <?php foreach ([
                        'Review' => $template['review_status'] ?? '-',
                        'Public' => $template['public_status'] ?? '-',
                        'Plan' => $template['seller_plan_name'] ?? '-',
                        'Submitted' => $template['submitted_at'] ?? '-',
                        'Usage' => $template['usage_count'] ?? 0,
                        'Publish' => $template['publish_count'] ?? 0,
                    ] as $label => $value): ?>
                        <div class="rounded-2xl bg-emerald-50/70 p-4"><dt class="text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($label) ?></dt><dd class="mt-1 font-black"><?= esc((string) $value) ?></dd></div>
                    <?php endforeach ?>
                </dl>
                <?php if (! empty($template['rejection_reason'])): ?>
                    <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700"><?= esc($template['rejection_reason']) ?></div>
                <?php endif ?>
                <?php
                    helper('admin_permission');
                    $canReviewTemplates = admin_can('admin.templates.review');
                    $canManageTemplates = admin_can('admin.templates.manage');
                ?>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold" href="<?= site_url('templates/preview/' . $template['id']) ?>" target="_blank" rel="noopener">Preview</a>
                    <?php if ($canReviewTemplates): ?>
                    <form method="post" action="<?= site_url('admin/seller-templates/' . $template['id'] . '/approve') ?>">
                        <?= csrf_field() ?>
                        <button class="rounded-2xl bg-emerald-700 px-4 py-2 text-sm font-black text-white" type="submit">Approve</button>
                    </form>
                    <form class="flex flex-wrap gap-2" method="post" action="<?= site_url('admin/seller-templates/' . $template['id'] . '/reject') ?>">
                        <?= csrf_field() ?>
                        <input class="rounded-2xl border border-slate-200 px-4 py-2 text-sm" type="text" name="rejection_reason" placeholder="Alasan reject" required>
                        <button class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-black text-white" type="submit">Reject</button>
                    </form>
                    <?php endif ?>
                    <?php if ($canManageTemplates): ?>
                    <form method="post" action="<?= site_url('admin/seller-templates/' . $template['id'] . '/archive') ?>">
                        <?= csrf_field() ?>
                        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold" type="submit">Archive</button>
                    </form>
                    <?php endif ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
