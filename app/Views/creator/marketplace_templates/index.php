<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Creator Marketplace Templates - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?php
        $badgeClass = static function (?string $status): string {
            return match ((string) $status) {
                'submitted' => 'bg-blue-50 text-blue-700 ring-blue-200',
                'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'rejected', 'changes_requested' => 'bg-rose-50 text-rose-700 ring-rose-200',
                'archived' => 'bg-slate-800 text-white ring-slate-800',
                default => 'bg-slate-100 text-slate-700 ring-slate-200',
            };
        };
    ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-700">Creator Dashboard</p>
                <h1 class="text-3xl font-black tracking-tight">Marketplace Templates</h1>
                <p class="mt-2 text-sm text-slate-600">Kelola metadata dan review marketplace untuk <?= esc($creator['display_name']) ?>.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?= view('components/public_theme_toggle') ?>
                <a class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-sm font-bold shadow-sm transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('creator/review-feedback') ?>">Review Feedback</a>
                <a class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-sm font-bold shadow-sm transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('creator/notifications') ?>">Notifikasi</a>
                <a class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-sm font-bold shadow-sm transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('dashboard') ?>">Dashboard</a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="grid gap-4 md:grid-cols-4 xl:grid-cols-8">
            <?php foreach ([
                'Total Template' => $summary['total'] ?? 0,
                'Draft' => $summary['draft'] ?? 0,
                'Submitted' => $summary['submitted'] ?? 0,
                'Approved' => $summary['approved'] ?? 0,
                'Rejected' => $summary['rejected'] ?? 0,
                'Archived' => $summary['archived'] ?? 0,
                'Perlu Revisi' => $summary['needs_revision'] ?? 0,
                'Updated' => $summary['last_updated'] ? date('d M', strtotime((string) $summary['last_updated'])) : '-',
            ] as $label => $value): ?>
                <article class="rounded-3xl border border-emerald-100 bg-white/90 p-4 shadow-sm">
                    <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500"><?= esc($label) ?></p>
                    <p class="mt-2 text-2xl font-black"><?= esc((string) $value) ?></p>
                </article>
            <?php endforeach ?>
        </section>

        <section class="mt-6 grid gap-5 lg:grid-cols-[1fr_.85fr]">
            <article class="rounded-[28px] border border-emerald-100 bg-white/90 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black">Profil Creator</h2>
                        <p class="mt-1 text-sm text-slate-600">Kelengkapan profil membantu admin menilai template.</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700 ring-1 ring-emerald-200"><?= esc((string) $profileCompletion['percent']) ?>%</span>
                </div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-emerald-600" style="width: <?= esc((string) $profileCompletion['percent']) ?>%"></div>
                </div>
                <p class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">Pendapatan akan tersedia setelah fitur marketplace transaksi aktif.</p>
            </article>

            <article class="rounded-[28px] border border-emerald-100 bg-white/90 p-5 shadow-sm">
                <h2 class="text-lg font-black">Notifikasi Terbaru</h2>
                <div class="mt-4 space-y-3">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                            <p class="text-sm font-black"><?= esc($notification['title']) ?></p>
                            <p class="mt-1 text-xs leading-5 text-slate-600"><?= esc($notification['message']) ?></p>
                        </div>
                    <?php endforeach ?>
                    <?php if ($notifications === []): ?>
                        <p class="text-sm text-slate-500">Belum ada notifikasi.</p>
                    <?php endif ?>
                </div>
            </article>
        </section>

        <section class="mt-6 overflow-hidden rounded-[28px] border border-emerald-100 bg-white/90 shadow-sm">
            <div class="border-b border-emerald-100 px-5 py-4">
                <h2 class="text-lg font-black">Template Milik Creator</h2>
                <p class="mt-1 text-sm text-slate-600">Buat draft metadata dari template yang sudah diassign admin.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] text-left text-sm">
                    <thead class="bg-emerald-50/70 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-4">Template</th>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Approval</th>
                            <th class="px-5 py-4">Harga</th>
                            <th class="px-5 py-4">License</th>
                            <th class="px-5 py-4">Updated</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($marketplaceTemplates as $template): ?>
                            <?php $status = (string) ($template['marketplace_status'] ?? 'draft'); ?>
                            <tr class="transition hover:bg-emerald-50/60">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-14 w-11 overflow-hidden rounded-xl bg-slate-100">
                                            <?php if (! empty($template['thumbnail_url'])): ?>
                                                <img class="h-full w-full object-cover" src="<?= esc($template['thumbnail_url']) ?>" alt="">
                                            <?php endif ?>
                                        </div>
                                        <div>
                                            <div class="font-black"><?= esc($template['title']) ?></div>
                                            <div class="text-xs text-slate-500"><?= esc($template['template_name'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4"><?= esc($template['category'] ?: '-') ?></td>
                                <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-black ring-1 <?= esc($badgeClass($status)) ?>"><?= esc($status) ?></span></td>
                                <td class="px-5 py-4"><?= esc($template['approval_status']) ?></td>
                                <td class="px-5 py-4"><?= (int) $template['is_free'] === 1 ? 'Gratis' : 'Rp ' . number_format((int) $template['price_amount'], 0, ',', '.') ?></td>
                                <td class="px-5 py-4"><?= esc(str_replace('_', ' ', (string) $template['license_type'])) ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= esc($template['updated_at'] ?? '-') ?></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a class="rounded-xl border border-emerald-100 bg-white px-3 py-2 text-xs font-black transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('creator/marketplace-templates/' . $template['id']) ?>"><?= in_array($status, ['submitted', 'approved', 'archived'], true) ? 'View' : 'Edit' ?></a>
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black transition hover:border-emerald-600 hover:text-emerald-700" href="<?= esc($template['preview_url'] ?: site_url('templates/preview/' . $template['template_id'])) ?>" target="_blank" rel="noopener">Preview</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($marketplaceTemplates === []): ?>
                            <tr><td class="px-5 py-8 text-center text-slate-500" colspan="8">Belum ada draft metadata. Buat dari template yang sudah diassign di bawah ini.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-[28px] border border-emerald-100 bg-white/90 shadow-sm">
            <div class="border-b border-emerald-100 px-5 py-4">
                <h2 class="text-lg font-black">Template Belum Punya Metadata</h2>
            </div>
            <div class="grid gap-4 p-5 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($ownedTemplates as $row): ?>
                    <?php if ((int) ($row['marketplace_id'] ?? 0) > 0) continue; ?>
                    <form action="<?= site_url('creator/marketplace-templates') ?>" method="post" class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="template_id" value="<?= esc((string) $row['template_id']) ?>">
                        <p class="font-black"><?= esc($row['template_name']) ?></p>
                        <p class="mt-1 text-xs text-slate-500"><?= esc($row['category_name'] ?? '-') ?></p>
                        <button class="mt-4 rounded-2xl bg-emerald-700 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-800" type="submit">Buat Draft Metadata</button>
                    </form>
                <?php endforeach ?>
            </div>
        </section>
    </main>
</body>
</html>
