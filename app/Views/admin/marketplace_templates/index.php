<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Marketplace Templates - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Marketplace Templates', 'adminIcon' => 'template', 'adminActive' => 'templates']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <p class="mb-6 text-sm text-slate-600">Review queue, metadata, dan ownership template creator.</p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="mb-6 rounded-[28px] border border-emerald-100 bg-white/90 p-5 shadow-sm">
            <h2 class="text-lg font-black">Assign Template ke Creator</h2>
            <p class="mt-1 text-sm text-slate-600">Template existing tidak otomatis dimiliki creator. Admin perlu assign secara eksplisit.</p>
            <form action="<?= site_url('admin/marketplace-templates/assign') ?>" method="post" class="mt-4 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                <?= csrf_field() ?>
                <select class="h-11 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="template_id" required>
                    <option value="">Pilih template belum dimiliki</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?= esc((string) $template['id']) ?>"><?= esc($template['name']) ?> (#<?= esc((string) $template['id']) ?>)</option>
                    <?php endforeach ?>
                </select>
                <select class="h-11 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="creator_id" required>
                    <option value="">Pilih creator aktif</option>
                    <?php foreach ($creators as $creator): ?>
                        <option value="<?= esc((string) $creator['id']) ?>"><?= esc($creator['display_name']) ?> (<?= esc($creator['slug']) ?>)</option>
                    <?php endforeach ?>
                </select>
                <button class="h-11 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800" type="submit">Assign</button>
            </form>
        </section>

        <section class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-2">
                <?php foreach ([null => 'All', 'submitted' => 'Pending Review', 'changes_requested' => 'Changes Requested', 'approved' => 'Approved', 'rejected' => 'Rejected', 'archived' => 'Archived', 'draft' => 'Draft'] as $status => $label): ?>
                    <?php $active = $status === $statusFilter; ?>
                    <a class="rounded-2xl px-4 py-2 text-sm font-black ring-1 transition <?= $active ? 'bg-emerald-700 text-white ring-emerald-700' : 'bg-white text-slate-700 ring-emerald-100 hover:text-emerald-700 hover:ring-emerald-500' ?>" href="<?= site_url('admin/marketplace-templates' . ($status ? '?status=' . $status : '')) ?>"><?= esc($label) ?></a>
                <?php endforeach ?>
            </div>
            <form class="grid gap-2 md:grid-cols-5" method="get" action="<?= site_url('admin/marketplace-templates') ?>">
                <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= esc($statusFilter) ?>"><?php endif ?>
                <input class="h-11 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="q" value="<?= esc($search) ?>" placeholder="Search title/creator/category">
                <input class="h-11 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="category" value="<?= esc($categoryFilter) ?>" placeholder="Kategori">
                <select class="h-11 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="price_type">
                    <option value="">Free/Paid</option>
                    <option value="free" <?= $priceType === 'free' ? 'selected' : '' ?>>Free</option>
                    <option value="paid" <?= $priceType === 'paid' ? 'selected' : '' ?>>Paid</option>
                </select>
                <select class="h-11 rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="sort">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Submitted terbaru</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Submitted terlama</option>
                </select>
                <button class="h-11 rounded-2xl border border-emerald-100 bg-white px-4 text-sm font-black transition hover:border-emerald-600 hover:text-emerald-700" type="submit">Search</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-[28px] border border-emerald-100 bg-white/90 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="bg-emerald-50/70 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-4">Title</th>
                            <th class="px-5 py-4">Creator</th>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Harga</th>
                            <th class="px-5 py-4">Submitted</th>
                            <th class="px-5 py-4">Priority</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Approval</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($marketplaceTemplates as $marketplace): ?>
                            <?php
                                $submittedAt = $marketplace['submitted_at'] ?? null;
                                $waitingDays = $submittedAt ? floor((time() - strtotime((string) $submittedAt)) / 86400) : 0;
                                $priority = $waitingDays >= 3 ? 'Long Waiting' : (($marketplace['marketplace_status'] ?? '') === 'submitted' ? 'New Submission' : '-');
                                if (($marketplace['rejection_reason'] ?? '') !== '' && ($marketplace['marketplace_status'] ?? '') === 'submitted') {
                                    $priority = 'Resubmission';
                                }
                            ?>
                            <tr class="transition hover:bg-emerald-50/60">
                                <td class="px-5 py-4">
                                    <div class="font-black"><?= esc($marketplace['title']) ?></div>
                                    <div class="text-xs text-slate-500"><?= esc($marketplace['template_name'] ?? '-') ?></div>
                                </td>
                                <td class="px-5 py-4"><?= esc($marketplace['creator_name'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= esc($marketplace['category'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= (int) ($marketplace['is_free'] ?? 1) === 1 ? 'Gratis' : 'Rp ' . number_format((int) $marketplace['price_amount'], 0, ',', '.') ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= esc($submittedAt ?: '-') ?></td>
                                <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-200"><?= esc($priority) ?></span></td>
                                <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700"><?= esc($marketplace['marketplace_status']) ?></span></td>
                                <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700"><?= esc($marketplace['approval_status']) ?></span></td>
                                <td class="px-5 py-4"><a class="rounded-xl border border-emerald-100 bg-white px-3 py-2 text-xs font-black transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('admin/marketplace-templates/' . $marketplace['id']) ?>">Review</a></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($marketplaceTemplates === []): ?>
                            <tr><td class="px-5 py-8 text-center text-slate-500" colspan="9">Belum ada metadata marketplace.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
