<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Marketplace Template - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Review Marketplace Template', 'adminIcon' => 'template', 'adminActive' => 'templates']) ?>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="mb-6 flex flex-col gap-2">
            <a class="text-sm font-bold text-emerald-700" href="<?= site_url('admin/marketplace-templates') ?>">← Kembali</a>
            <p class="text-sm text-slate-600">Creator: <?= esc($marketplace['creator_name'] ?? '-') ?> · Status: <?= esc($marketplace['marketplace_status']) ?></p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
            <article class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
                <dl class="space-y-5 text-sm">
                    <div>
                        <dt class="font-black text-slate-500">Template Source</dt>
                        <dd class="mt-1 font-semibold"><?= esc($marketplace['template_name'] ?? '-') ?> (#<?= esc((string) $marketplace['template_id']) ?>)</dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Deskripsi</dt>
                        <dd class="mt-1 whitespace-pre-wrap leading-6 text-slate-700"><?= esc($marketplace['description'] ?: '-') ?></dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Kategori</dt>
                        <dd class="mt-1"><?= esc($marketplace['category'] ?: '-') ?></dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Harga & Lisensi</dt>
                        <dd class="mt-1"><?= (int) $marketplace['is_free'] === 1 ? 'Gratis' : 'Rp ' . number_format((int) $marketplace['price_amount'], 0, ',', '.') ?> · <?= esc($marketplace['license_type']) ?></dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Thumbnail</dt>
                        <dd class="mt-1 break-all"><?= esc($marketplace['thumbnail_url'] ?: '-') ?></dd>
                    </div>
                <div>
                    <dt class="font-black text-slate-500">Preview</dt>
                        <dd class="mt-1">
                            <a class="font-bold text-emerald-700 hover:underline" href="<?= esc($marketplace['preview_url'] ?: site_url('templates/preview/' . $marketplace['template_id'])) ?>" target="_blank" rel="noopener">Buka preview template</a>
                        </dd>
                    </div>
                    <?php if (($marketplace['rejection_reason'] ?? '') !== ''): ?>
                        <div>
                            <dt class="font-black text-slate-500">Alasan Rejection</dt>
                            <dd class="mt-1 whitespace-pre-wrap text-rose-700"><?= esc($marketplace['rejection_reason']) ?></dd>
                        </div>
                    <?php endif ?>
                </dl>
            </article>

            <aside class="space-y-5">
                <div class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Status</p>
                    <p class="mt-3 text-sm font-bold">Marketplace: <?= esc($marketplace['marketplace_status']) ?></p>
                    <p class="mt-1 text-sm font-bold">Approval: <?= esc($marketplace['approval_status']) ?></p>
                    <p class="mt-4 text-xs leading-5 text-slate-500">Approval hanya berlaku untuk template submitted/pending. Draft tidak bisa diapprove langsung.</p>
                </div>
                    <div>
                        <dt class="font-black text-slate-500">Tags</dt>
                        <dd class="mt-1 break-all"><?= esc($marketplace['tags'] ?: '-') ?></dd>
                    </div>

                <?php if (($marketplace['marketplace_status'] ?? '') === 'submitted' && ($marketplace['approval_status'] ?? '') === 'pending'): ?>
                    <form id="reviewForm" action="<?= site_url('admin/marketplace-templates/' . $marketplace['id'] . '/approve') ?>" method="post" class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm" onsubmit="return confirm('Kirim keputusan review ini?');">
                        <?= csrf_field() ?>
                        <h2 class="text-lg font-black">Review Checklist</h2>
                        <div class="mt-4 space-y-3">
                            <?php foreach ($reviewChecklist as $key => $label): ?>
                                <label class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3 text-sm font-semibold text-slate-700">
                                    <input class="mt-1" type="checkbox" name="checklist_<?= esc($key) ?>" value="1">
                                    <span><?= esc($label) ?></span>
                                </label>
                            <?php endforeach ?>
                        </div>
                        <label class="mt-4 block">
                            <span class="text-sm font-bold text-slate-700">Admin Notes</span>
                            <textarea class="mt-2 min-h-24 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" name="admin_notes" maxlength="1000"><?= esc(old('admin_notes')) ?></textarea>
                        </label>
                        <button class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800" type="submit">Approve</button>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Alasan Reject</span>
                            <textarea class="mt-2 min-h-28 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-rose-500 focus:ring-4 focus:ring-rose-100" name="rejection_reason" maxlength="1000"><?= esc(old('rejection_reason')) ?></textarea>
                        </label>
                        <div class="mt-4 grid gap-3">
                            <button class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-amber-500 px-4 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-600" formaction="<?= site_url('admin/marketplace-templates/' . $marketplace['id'] . '/request-changes') ?>" type="submit">Request Changes</button>
                            <button class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-rose-600 px-4 text-sm font-black text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-700" formaction="<?= site_url('admin/marketplace-templates/' . $marketplace['id'] . '/reject') ?>" type="submit">Reject</button>
                        </div>
                    </form>
                <?php endif ?>

                <?php if (($marketplace['marketplace_status'] ?? '') !== 'archived'): ?>
                    <form action="<?= site_url('admin/marketplace-templates/' . $marketplace['id'] . '/archive') ?>" method="post" class="rounded-[28px] border border-slate-200 bg-white/90 p-6 shadow-sm" onsubmit="return confirm('Archive marketplace template ini?');">
                        <?= csrf_field() ?>
                        <button class="inline-flex h-11 w-full items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-slate-500" type="submit">Archive</button>
                    </form>
                <?php endif ?>
            </aside>
        </section>

        <section class="mt-6 grid gap-5 lg:grid-cols-2">
            <article class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
                <h2 class="text-lg font-black">Review History</h2>
                <div class="mt-4 space-y-3">
                    <?php foreach ($reviews as $review): ?>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-black"><?= esc($review['status']) ?></p>
                                <p class="text-xs text-slate-500"><?= esc($review['created_at'] ?? '-') ?></p>
                            </div>
                            <p class="mt-1 text-xs text-slate-500"><?= esc($review['reviewer_name'] ?? 'Creator/System') ?></p>
                            <?php if (($review['admin_notes'] ?? '') !== ''): ?><p class="mt-2 text-xs leading-5 text-slate-600"><strong>Notes:</strong> <?= esc($review['admin_notes']) ?></p><?php endif ?>
                            <?php if (($review['rejection_reason'] ?? '') !== ''): ?><p class="mt-2 text-xs leading-5 text-rose-700"><strong>Reason:</strong> <?= esc($review['rejection_reason']) ?></p><?php endif ?>
                        </div>
                    <?php endforeach ?>
                    <?php if ($reviews === []): ?><p class="text-sm text-slate-500">Belum ada review history.</p><?php endif ?>
                </div>
            </article>
            <article class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
                <h2 class="text-lg font-black">Activity Log</h2>
                <div class="mt-4 space-y-3">
                    <?php foreach ($activityLogs as $log): ?>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-sm font-black"><?= esc($log['action']) ?> <span class="font-medium text-slate-500"><?= esc($log['from_status'] ?? '-') ?> → <?= esc($log['to_status'] ?? '-') ?></span></p>
                            <p class="mt-1 text-xs text-slate-500"><?= esc($log['actor_name'] ?? $log['actor_role'] ?? '-') ?> · <?= esc($log['created_at'] ?? '-') ?></p>
                            <?php if (($log['note'] ?? '') !== ''): ?><p class="mt-2 text-xs leading-5 text-slate-600"><?= esc($log['note']) ?></p><?php endif ?>
                        </div>
                    <?php endforeach ?>
                    <?php if ($activityLogs === []): ?><p class="text-sm text-slate-500">Belum ada activity log.</p><?php endif ?>
                </div>
            </article>
        </section>
    </main>
</body>
</html>
