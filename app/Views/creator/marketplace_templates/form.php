<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Marketplace Metadata - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?php
        $status = (string) ($marketplace['marketplace_status'] ?? 'draft');
        $canEdit = in_array($status, ['draft', 'rejected', 'changes_requested'], true);
        $tags = '';
        if (! empty($marketplace['tags'])) {
            $decodedTags = json_decode((string) $marketplace['tags'], true);
            $tags = is_array($decodedTags) ? implode(', ', $decodedTags) : '';
        }
    ?>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-700">Creator Metadata</p>
                <h1 class="text-3xl font-black tracking-tight"><?= esc($marketplace['title']) ?></h1>
                <p class="mt-2 text-sm text-slate-600">Status: <?= esc($marketplace['marketplace_status']) ?> · Approval: <?= esc($marketplace['approval_status']) ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?= view('components/public_theme_toggle') ?>
                <a class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-sm font-bold shadow-sm transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('creator/marketplace-templates') ?>">Kembali</a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <?php if (! $canEdit): ?>
            <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Template yang sudah disubmit tidak dapat diedit sampai direview admin. Template approved perlu revisi baru atau bantuan admin.</div>
        <?php endif ?>
        <?php if (($marketplace['rejection_reason'] ?? '') !== ''): ?>
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><strong>Alasan rejection:</strong> <?= esc($marketplace['rejection_reason']) ?></div>
        <?php endif ?>

        <section class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <form action="<?= site_url('creator/marketplace-templates/' . $marketplace['id'] . '/update') ?>" method="post" class="grid gap-5 lg:grid-cols-2">
                <?= csrf_field() ?>
                <label class="block lg:col-span-2">
                    <span class="text-sm font-bold text-slate-700">Title</span>
                    <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="title" value="<?= esc(old('title', $marketplace['title'])) ?>" maxlength="120" required <?= $canEdit ? '' : 'disabled' ?>>
                </label>
                <label class="block lg:col-span-2">
                    <span class="text-sm font-bold text-slate-700">Short Description</span>
                    <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="short_description" value="<?= esc(old('short_description', $marketplace['short_description'] ?? '')) ?>" maxlength="180" <?= $canEdit ? '' : 'disabled' ?>>
                </label>
                <label class="block lg:col-span-2">
                    <span class="text-sm font-bold text-slate-700">Description</span>
                    <textarea class="mt-2 min-h-32 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="description" maxlength="2000" <?= $canEdit ? '' : 'disabled' ?>><?= esc(old('description', $marketplace['description'] ?? '')) ?></textarea>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">Category</span>
                    <select class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="category" <?= $canEdit ? '' : 'disabled' ?>>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['name']) ?>" <?= old('category', $marketplace['category'] ?? '') === $category['name'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">Tags</span>
                    <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="tags" value="<?= esc(old('tags', $tags)) ?>" placeholder="wedding, elegant" <?= $canEdit ? '' : 'disabled' ?>>
                </label>
                <label class="block lg:col-span-2">
                    <span class="text-sm font-bold text-slate-700">Thumbnail URL</span>
                    <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="thumbnail_url" value="<?= esc(old('thumbnail_url', $marketplace['thumbnail_url'] ?? '')) ?>" maxlength="500" <?= $canEdit ? '' : 'disabled' ?>>
                </label>
                <label class="block lg:col-span-2">
                    <span class="text-sm font-bold text-slate-700">Preview URL</span>
                    <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="preview_url" value="<?= esc(old('preview_url', $marketplace['preview_url'] ?? '')) ?>" maxlength="500" <?= $canEdit ? '' : 'disabled' ?>>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">Tipe Harga</span>
                    <select class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="is_free" <?= $canEdit ? '' : 'disabled' ?>>
                        <option value="1" <?= (int) old('is_free', $marketplace['is_free'] ?? 1) === 1 ? 'selected' : '' ?>>Gratis</option>
                        <option value="0" <?= (int) old('is_free', $marketplace['is_free'] ?? 1) === 0 ? 'selected' : '' ?>>Berbayar</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">Harga</span>
                    <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="price_amount" type="number" min="0" value="<?= esc(old('price_amount', (string) ($marketplace['price_amount'] ?? 0))) ?>" <?= $canEdit ? '' : 'disabled' ?>>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">Currency</span>
                    <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="price_currency" value="<?= esc(old('price_currency', $marketplace['price_currency'] ?? 'IDR')) ?>" maxlength="10" <?= $canEdit ? '' : 'disabled' ?>>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">License</span>
                    <select class="mt-2 h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 disabled:bg-slate-100" name="license_type" <?= $canEdit ? '' : 'disabled' ?>>
                        <?php foreach ($licenseTypes as $licenseType): ?>
                            <option value="<?= esc($licenseType) ?>" <?= old('license_type', $marketplace['license_type']) === $licenseType ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $licenseType)) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
                <?php if ($canEdit): ?>
                    <label class="block lg:col-span-2">
                        <span class="text-sm font-bold text-slate-700">Pesan untuk Admin <span class="font-medium text-slate-400">(opsional saat submit)</span></span>
                        <textarea class="mt-2 min-h-24 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="creator_message" maxlength="1000" placeholder="Contoh: Saya sudah memperbaiki thumbnail dan kategori sesuai feedback."></textarea>
                    </label>
                    <div class="flex flex-wrap gap-3 lg:col-span-2">
                        <button class="inline-flex h-11 items-center justify-center rounded-2xl border border-emerald-100 bg-white px-5 text-sm font-black shadow-sm transition hover:border-emerald-600 hover:text-emerald-700" type="submit">Save Draft</button>
                        <button class="inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800" formaction="<?= site_url('creator/marketplace-templates/' . $marketplace['id'] . '/submit') ?>" type="submit">Submit for Review</button>
                    </div>
                <?php endif ?>
            </form>
            <?php if ($canEdit): ?>
                <form action="<?= site_url('creator/marketplace-templates/' . $marketplace['id'] . '/archive') ?>" method="post" class="mt-4" onsubmit="return confirm('Archive draft marketplace ini?');">
                    <?= csrf_field() ?>
                    <button class="rounded-2xl border border-rose-200 bg-white px-5 py-2 text-sm font-black text-rose-700 transition hover:bg-rose-50" type="submit">Archive</button>
                </form>
            <?php elseif ($status === 'submitted'): ?>
                <form action="<?= site_url('creator/marketplace-templates/' . $marketplace['id'] . '/withdraw') ?>" method="post" class="mt-4" onsubmit="return confirm('Tarik submission ini kembali ke draft?');">
                    <?= csrf_field() ?>
                    <button class="rounded-2xl border border-amber-200 bg-white px-5 py-2 text-sm font-black text-amber-700 transition hover:bg-amber-50" type="submit">Withdraw Submission</button>
                </form>
            <?php elseif ($status === 'archived'): ?>
                <form action="<?= site_url('creator/marketplace-templates/' . $marketplace['id'] . '/restore') ?>" method="post" class="mt-4" onsubmit="return confirm('Restore template ini ke draft?');">
                    <?= csrf_field() ?>
                    <button class="rounded-2xl border border-emerald-200 bg-white px-5 py-2 text-sm font-black text-emerald-700 transition hover:bg-emerald-50" type="submit">Restore to Draft</button>
                </form>
            <?php endif ?>
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
                            <?php if (($review['creator_message'] ?? '') !== ''): ?>
                                <p class="mt-2 text-xs leading-5 text-slate-600"><strong>Creator:</strong> <?= esc($review['creator_message']) ?></p>
                            <?php endif ?>
                            <?php if (($review['admin_notes'] ?? '') !== ''): ?>
                                <p class="mt-2 text-xs leading-5 text-slate-600"><strong>Admin:</strong> <?= esc($review['admin_notes']) ?></p>
                            <?php endif ?>
                            <?php if (($review['rejection_reason'] ?? '') !== ''): ?>
                                <p class="mt-2 text-xs leading-5 text-rose-700"><strong>Reason:</strong> <?= esc($review['rejection_reason']) ?></p>
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                    <?php if ($reviews === []): ?>
                        <p class="text-sm text-slate-500">Belum ada review.</p>
                    <?php endif ?>
                </div>
            </article>
            <article class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
                <h2 class="text-lg font-black">Activity Log</h2>
                <div class="mt-4 space-y-3">
                    <?php foreach ($activityLogs as $log): ?>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-sm font-black"><?= esc($log['action']) ?> <span class="font-medium text-slate-500"><?= esc($log['from_status'] ?? '-') ?> → <?= esc($log['to_status'] ?? '-') ?></span></p>
                            <p class="mt-1 text-xs text-slate-500"><?= esc($log['actor_name'] ?? $log['actor_role'] ?? '-') ?> · <?= esc($log['created_at'] ?? '-') ?></p>
                            <?php if (($log['note'] ?? '') !== ''): ?>
                                <p class="mt-2 text-xs leading-5 text-slate-600"><?= esc($log['note']) ?></p>
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                    <?php if ($activityLogs === []): ?>
                        <p class="text-sm text-slate-500">Belum ada aktivitas.</p>
                    <?php endif ?>
                </div>
            </article>
        </section>
    </main>
</body>
</html>
