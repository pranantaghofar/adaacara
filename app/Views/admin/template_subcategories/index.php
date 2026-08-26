<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subkategori Template - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Subkategori Template', 'adminIcon' => 'template', 'adminActive' => 'templateSubcategories']) ?>

    <main class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[420px_1fr]">
        <?php
            helper('admin_permission');
            $tableReady = ! empty($tableReady);
            $canManage = admin_can('admin.templates.manage');
            $canDelete = admin_can('admin.templates.delete');
            $categories = $categories ?? [];
            $subcategories = $subcategories ?? [];
        ?>

        <section class="h-fit rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <div class="mb-5">
                <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Header & Search</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight">Tambah Subkategori</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Keyword di sini dipakai saat user klik subkategori di header publik.</p>
            </div>

            <?php if (! $tableReady): ?>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm font-bold leading-6 text-amber-800">
                    Tabel <code>template_subcategories</code> belum tersedia. Jalankan file SQL:
                    <div class="mt-2 rounded-2xl bg-white/70 px-3 py-2 font-mono text-xs">database/alter_template_subcategories.sql</div>
                </div>
            <?php elseif (! $canManage): ?>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 text-sm font-bold text-slate-600">Akun ini hanya bisa melihat subkategori.</div>
            <?php else: ?>
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                        <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                            <div><?= esc($error) ?></div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <form class="grid gap-4" method="post" action="<?= site_url('admin/template-subcategories/store') ?>">
                    <?= csrf_field() ?>
                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Kategori Utama
                        <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="category_id" required>
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= esc((string) ($category['id'] ?? ''), 'attr') ?>" <?= old('category_id') === (string) ($category['id'] ?? '') ? 'selected' : '' ?>><?= esc((string) ($category['name'] ?? 'Kategori')) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>

                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Nama Subkategori
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="name" value="<?= esc(old('name')) ?>" maxlength="120" required>
                    </label>

                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Slug
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="slug" value="<?= esc(old('slug')) ?>" maxlength="140" placeholder="otomatis dari nama">
                    </label>

                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Judul Kolom Header
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="group_title" value="<?= esc(old('group_title')) ?>" maxlength="120" placeholder="Contoh: Undangan pernikahan">
                    </label>

                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Keyword Pemanggil
                        <textarea class="min-h-28 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="search_keywords" maxlength="1000" placeholder="Contoh: rustik rustic kayu garden earth tone"><?= esc(old('search_keywords')) ?></textarea>
                    </label>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                            Urutan
                            <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="number" name="sort_order" value="<?= esc(old('sort_order', '0')) ?>">
                        </label>
                        <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700">
                            Aktif
                            <input class="h-5 w-5 accent-emerald-700" type="checkbox" name="is_active" value="1" <?= old('is_active', '1') === '1' ? 'checked' : '' ?>>
                        </label>
                    </div>

                    <button class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-lg shadow-emerald-700/20" type="submit">Simpan Subkategori</button>
                </form>
            <?php endif ?>
        </section>

        <section class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <div class="mb-5">
                <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Daftar</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight">Subkategori Header</h2>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif ?>

            <?php if (! $tableReady): ?>
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm font-bold text-slate-500">Jalankan SQL setup dahulu untuk mengaktifkan fitur ini.</div>
            <?php elseif ($subcategories === []): ?>
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm font-bold text-slate-500">Belum ada subkategori.</div>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach ($subcategories as $item): ?>
                        <article class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                            <?php if ($canManage): ?>
                                <form class="grid gap-3" method="post" action="<?= site_url('admin/template-subcategories/update/' . (int) $item['id']) ?>">
                                    <?= csrf_field() ?>
                                    <div class="grid gap-3 md:grid-cols-[1fr_1fr_110px]">
                                        <select class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold" name="category_id">
                                            <?php foreach ($categories as $category): ?>
                                                <?php $selected = (int) ($category['id'] ?? 0) === (int) ($item['category_id'] ?? 0); ?>
                                                <option value="<?= esc((string) ($category['id'] ?? ''), 'attr') ?>" <?= $selected ? 'selected' : '' ?>><?= esc((string) ($category['name'] ?? 'Kategori')) ?></option>
                                            <?php endforeach ?>
                                        </select>
                                        <input class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold" name="name" value="<?= esc((string) ($item['name'] ?? ''), 'attr') ?>" maxlength="120" required>
                                        <input class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold" type="number" name="sort_order" value="<?= esc((string) ($item['sort_order'] ?? 0), 'attr') ?>">
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <input class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold" name="slug" value="<?= esc((string) ($item['slug'] ?? ''), 'attr') ?>" maxlength="140">
                                        <input class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold" name="group_title" value="<?= esc((string) ($item['group_title'] ?? ''), 'attr') ?>" maxlength="120" placeholder="Judul kolom">
                                    </div>
                                    <textarea class="min-h-20 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold" name="search_keywords" maxlength="1000"><?= esc((string) ($item['search_keywords'] ?? '')) ?></textarea>
                                    <div>
                                        <button class="h-10 rounded-2xl bg-emerald-700 px-4 text-xs font-black text-white" type="submit">Update Subkategori</button>
                                    </div>
                                </form>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form method="post" action="<?= site_url('admin/template-subcategories/toggle/' . (int) $item['id']) ?>">
                                        <?= csrf_field() ?>
                                        <button class="h-10 rounded-2xl border px-4 text-xs font-black <?= ! empty($item['is_active']) ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' ?>" type="submit"><?= ! empty($item['is_active']) ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                    </form>
                                    <?php if ($canDelete): ?>
                                        <form method="post" action="<?= site_url('admin/template-subcategories/delete/' . (int) $item['id']) ?>" onsubmit="return aaConfirmSubmit(event, 'Hapus subkategori ini?', {title: 'Hapus Subkategori', okText: 'Hapus', cancelText: 'Batal', danger: true});">
                                            <?= csrf_field() ?>
                                            <button class="h-10 rounded-2xl bg-rose-600 px-4 text-xs font-black text-white" type="submit">Hapus</button>
                                        </form>
                                    <?php endif ?>
                                </div>
                            <?php else: ?>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-black"><?= esc((string) ($item['name'] ?? '-')) ?></h3>
                                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-black text-slate-700"><?= esc((string) ($item['category_name'] ?? '-')) ?></span>
                                    <span class="rounded-full px-3 py-1 text-xs font-black <?= ! empty($item['is_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' ?>"><?= ! empty($item['is_active']) ? 'Aktif' : 'Nonaktif' ?></span>
                                </div>
                                <p class="mt-2 text-sm font-semibold text-slate-600"><?= esc((string) ($item['search_keywords'] ?? '')) ?></p>
                            <?php endif ?>
                        </article>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </section>
    </main>
</body>
</html>
