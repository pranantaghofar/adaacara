<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Creator Application - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Detail Creator Application', 'adminIcon' => 'review', 'adminActive' => 'sellerTemplates']) ?>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <a class="mb-6 inline-flex text-sm font-bold text-emerald-700" href="<?= site_url('admin/creator-applications') ?>">← Kembali</a>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <?php
            $status = (string) ($application['status'] ?? 'pending');
            $badgeClass = match ($status) {
                'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
                default => 'bg-amber-50 text-amber-700 ring-amber-200',
            };
        ?>

        <section class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
            <article class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ring-1 <?= esc($badgeClass) ?>"><?= esc($status) ?></span>
                <dl class="mt-6 space-y-5 text-sm">
                    <div>
                        <dt class="font-black text-slate-500">User</dt>
                        <dd class="mt-1 font-semibold"><?= esc($application['user_name'] ?? '-') ?> · <?= esc($application['user_email'] ?? '-') ?></dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Bio</dt>
                        <dd class="mt-1 whitespace-pre-wrap leading-6 text-slate-700"><?= esc($application['bio']) ?></dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Portfolio</dt>
                        <dd class="mt-1"><?= ($application['portfolio_url'] ?? '') !== '' ? '<a class="font-bold text-emerald-700 hover:underline" href="' . esc($application['portfolio_url']) . '" target="_blank" rel="noopener">' . esc($application['portfolio_url']) . '</a>' : '-' ?></dd>
                    </div>
                    <div>
                        <dt class="font-black text-slate-500">Social Links</dt>
                        <dd class="mt-1 rounded-2xl bg-slate-50 p-4 font-mono text-xs text-slate-700"><?= esc($application['social_links'] ?: '-') ?></dd>
                    </div>
                    <?php if (($application['reason'] ?? '') !== ''): ?>
                        <div>
                            <dt class="font-black text-slate-500">Alasan Rejection</dt>
                            <dd class="mt-1 whitespace-pre-wrap text-rose-700"><?= esc($application['reason']) ?></dd>
                        </div>
                    <?php endif ?>
                </dl>
            </article>

            <aside class="space-y-5">
                <?php if ($profile !== null): ?>
                    <div class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Creator Profile</p>
                        <h2 class="mt-2 text-xl font-black"><?= esc($profile['display_name']) ?></h2>
                        <p class="mt-1 text-sm text-slate-600">Slug: <span class="font-bold"><?= esc($profile['slug']) ?></span></p>
                    </div>
                <?php endif ?>

                <?php helper('admin_permission'); ?>
                <?php if ($status === 'pending' && admin_can('admin.templates.review')): ?>
                    <form action="<?= site_url('admin/creator-applications/' . $application['id'] . '/approve') ?>" method="post" class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm" onsubmit="return confirm('Approve aplikasi creator ini?');">
                        <?= csrf_field() ?>
                        <h2 class="text-lg font-black">Approve Creator</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Profile creator akan dibuat otomatis dan role user akan diubah menjadi creator, kecuali user adalah admin.</p>
                        <button class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800" type="submit">Approve</button>
                    </form>

                    <form action="<?= site_url('admin/creator-applications/' . $application['id'] . '/reject') ?>" method="post" class="rounded-[28px] border border-rose-100 bg-white/90 p-6 shadow-sm">
                        <?= csrf_field() ?>
                        <h2 class="text-lg font-black">Reject Creator</h2>
                        <label class="mt-4 block">
                            <span class="text-sm font-bold text-slate-700">Alasan</span>
                            <textarea class="mt-2 min-h-28 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-rose-500 focus:ring-4 focus:ring-rose-100" name="reason" maxlength="1000" required><?= esc(old('reason')) ?></textarea>
                        </label>
                        <button class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl bg-rose-600 px-4 text-sm font-black text-white shadow-lg shadow-rose-600/20 transition hover:bg-rose-700" type="submit">Reject</button>
                    </form>
                <?php else: ?>
                    <div class="rounded-[28px] border border-slate-200 bg-white/90 p-6 text-sm text-slate-600 shadow-sm">
                        Aplikasi ini sudah direview oleh <?= esc($application['reviewer_name'] ?? 'admin') ?> pada <?= esc($application['reviewed_at'] ?? '-') ?>.
                    </div>
                <?php endif ?>
            </aside>
        </section>
    </main>
</body>
</html>
