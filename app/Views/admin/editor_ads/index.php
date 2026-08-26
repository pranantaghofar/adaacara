<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iklan Editor - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Iklan Editor', 'adminIcon' => 'ads', 'adminActive' => 'editorAds']) ?>

    <main class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[420px_1fr]">
        <section class="h-fit rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <div class="mb-5">
                <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Editor Ads</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight">Tambah Iklan</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Editor menampilkan maksimal 3 iklan yang paling cocok untuk tiap user.</p>
            </div>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                    <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach ?>
                </div>
            <?php endif ?>

            <form class="grid gap-4" method="post" action="<?= site_url('admin/editor-ads/store') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                    Judul
                    <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="title" value="<?= esc(old('title')) ?>" maxlength="120" required>
                </label>

                <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                    Gambar
                    <input class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm font-bold normal-case tracking-normal" type="file" name="image" accept="image/png,image/jpeg,image/webp" required>
                    <span class="text-[11px] font-semibold normal-case tracking-normal text-slate-500">Rekomendasi vertical banner 600x900, maksimal 2MB.</span>
                </label>

                <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                    Link Opsional
                    <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="link_url" value="<?= esc(old('link_url')) ?>" placeholder="https://..." maxlength="500">
                </label>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Target
                        <select id="editorAdTargetType" class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="target_type">
                            <?php foreach (($targetOptions ?? []) as $value => $label): ?>
                                <option value="<?= esc($value, 'attr') ?>" <?= old('target_type', 'all') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>

                    <label id="editorAdUserTargetWrap" class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        User
                        <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="target_user_id">
                            <option value="">Pilih user</option>
                            <?php foreach (($users ?? []) as $user): ?>
                                <?php $userId = (string) ($user['id'] ?? ''); ?>
                                <option value="<?= esc($userId, 'attr') ?>" <?= old('target_user_id') === $userId ? 'selected' : '' ?>>
                                    <?= esc(($user['name'] ?? 'User') . ' - ' . ($user['email'] ?? '')) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </label>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Priority
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="number" name="priority" value="<?= esc(old('priority', '10')) ?>">
                    </label>
                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Urutan
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="number" name="sort_order" value="<?= esc(old('sort_order', '0')) ?>">
                    </label>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Mulai
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="datetime-local" name="starts_at" value="<?= esc(old('starts_at')) ?>">
                    </label>
                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Berakhir
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="datetime-local" name="ends_at" value="<?= esc(old('ends_at')) ?>">
                    </label>
                </div>

                <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700">
                    Aktifkan iklan
                    <input class="h-5 w-5 accent-emerald-700" type="checkbox" name="is_active" value="1" <?= old('is_active', '1') === '1' ? 'checked' : '' ?>>
                </label>

                <button class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-lg shadow-emerald-700/20" type="submit">Simpan Iklan</button>
            </form>
        </section>

        <section class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Aktif <?= esc((string) ($activeCount ?? 0)) ?></p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight">Daftar Iklan</h2>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif ?>

            <div class="grid gap-4">
                <?php foreach (($ads ?? []) as $ad): ?>
                    <?php $imageUrl = ! empty($ad['image_path']) ? base_url((string) $ad['image_path']) : ''; ?>
                    <article class="grid gap-4 rounded-3xl border border-slate-100 bg-slate-50 p-4 md:grid-cols-[160px_1fr_auto]">
                        <div class="overflow-hidden rounded-2xl bg-white">
                            <?php if ($imageUrl !== ''): ?>
                                <img class="aspect-[3/4] h-full w-full object-cover" src="<?= esc($imageUrl, 'attr') ?>" alt="<?= esc($ad['title'] ?? 'Iklan', 'attr') ?>" loading="lazy">
                            <?php endif ?>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate text-lg font-black"><?= esc($ad['title'] ?? 'Iklan') ?></h3>
                                <span class="rounded-full px-3 py-1 text-xs font-black <?= ! empty($ad['is_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' ?>">
                                    <?= ! empty($ad['is_active']) ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-600"><?= esc($ad['target_label'] ?? '-') ?></p>
                            <p class="mt-1 text-xs font-bold text-slate-500">Priority <?= esc((string) ($ad['priority'] ?? 0)) ?> · Urutan <?= esc((string) ($ad['sort_order'] ?? 0)) ?></p>
                            <?php if (! empty($ad['link_url'])): ?>
                                <a class="mt-2 block break-all text-xs font-bold text-emerald-700 hover:underline" href="<?= esc((string) $ad['link_url'], 'attr') ?>" target="_blank" rel="noopener"><?= esc((string) $ad['link_url']) ?></a>
                            <?php endif ?>
                        </div>
                        <div class="flex shrink-0 flex-row gap-2 md:flex-col">
                            <form method="post" action="<?= site_url('admin/editor-ads/' . $ad['id'] . '/toggle') ?>">
                                <?= csrf_field() ?>
                                <button class="h-10 rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700" type="submit"><?= ! empty($ad['is_active']) ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                            </form>
                            <form method="post" action="<?= site_url('admin/editor-ads/' . $ad['id'] . '/delete') ?>" onsubmit="return confirm('Hapus iklan editor ini?');">
                                <?= csrf_field() ?>
                                <button class="h-10 rounded-2xl bg-rose-600 px-4 text-xs font-black text-white" type="submit">Hapus</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach ?>

                <?php if (($ads ?? []) === []): ?>
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm font-bold text-slate-500">Belum ada iklan editor.</div>
                <?php endif ?>
            </div>
        </section>
    </main>

    <script>
    (function() {
        var target = document.getElementById('editorAdTargetType');
        var userWrap = document.getElementById('editorAdUserTargetWrap');
        function syncTargetUser() {
            if (!target || !userWrap) return;
            userWrap.style.display = target.value === 'user_specific' ? 'grid' : 'none';
        }
        target?.addEventListener('change', syncTargetUser);
        syncTargetUser();
    })();
    </script>
</body>
</html>
