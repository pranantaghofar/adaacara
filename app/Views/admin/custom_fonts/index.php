<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Font Custom - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link href="<?= esc(site_url('custom-fonts.css'), 'attr') ?>" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Font Custom', 'adminIcon' => 'fonts', 'adminActive' => 'customFonts']) ?>

    <main class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[420px_1fr]">
        <section class="h-fit rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <div class="mb-5">
                <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Admin Fonts</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight">Upload Font</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Gunakan WOFF2 jika tersedia supaya editor dan halaman publish tetap ringan. TTF/OTF tetap bisa dipakai.</p>
            </div>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                    <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach ?>
                </div>
            <?php endif ?>

            <form class="grid gap-4" method="post" action="<?= site_url('admin/custom-fonts/store') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                    Nama Font Family
                    <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="font_family" value="<?= esc(old('font_family')) ?>" maxlength="120" placeholder="Contoh: AdaAcara Serif" required>
                </label>

                <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                    File Font
                    <input class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm font-bold normal-case tracking-normal" type="file" name="font_file" accept=".woff2,.woff,.ttf,.otf" required>
                    <span class="text-[11px] font-semibold normal-case tracking-normal text-slate-500">Format: WOFF2, WOFF, TTF, OTF. Maksimal 4MB.</span>
                </label>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Weight
                        <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="font_weight">
                            <?php foreach ([100 => 'Thin', 200 => 'Extra Light', 300 => 'Light', 400 => 'Regular', 500 => 'Medium', 600 => 'Semi Bold', 700 => 'Bold', 800 => 'Extra Bold', 900 => 'Black'] as $weight => $label): ?>
                                <option value="<?= esc((string) $weight, 'attr') ?>" <?= old('font_weight', '400') === (string) $weight ? 'selected' : '' ?>><?= esc($weight . ' - ' . $label) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>

                    <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                        Style
                        <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="font_style">
                            <option value="normal" <?= old('font_style', 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                            <option value="italic" <?= old('font_style') === 'italic' ? 'selected' : '' ?>>Italic</option>
                        </select>
                    </label>
                </div>

                <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                    Urutan
                    <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="number" name="sort_order" value="<?= esc(old('sort_order', '0')) ?>">
                </label>

                <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700">
                    Aktifkan font
                    <input class="h-5 w-5 accent-emerald-700" type="checkbox" name="is_active" value="1" <?= old('is_active', '1') === '1' ? 'checked' : '' ?>>
                </label>

                <button class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-lg shadow-emerald-700/20" type="submit">Simpan Font</button>
            </form>
        </section>

        <section class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <div class="mb-5">
                <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Aktif <?= esc((string) ($activeCount ?? 0)) ?></p>
                <h2 class="mt-1 text-2xl font-black tracking-tight">Daftar Font</h2>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif ?>

            <div class="grid gap-3">
                <?php foreach (($fonts ?? []) as $font): ?>
                    <article class="grid gap-3 rounded-3xl border border-slate-100 bg-slate-50 p-4 md:grid-cols-[1fr_auto]">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate text-xl font-black" style="font-family: '<?= esc((string) ($font['font_family'] ?? ''), 'attr') ?>', sans-serif;"><?= esc($font['font_family'] ?? 'Font') ?></h3>
                                <span class="rounded-full px-3 py-1 text-xs font-black <?= ! empty($font['is_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' ?>">
                                    <?= ! empty($font['is_active']) ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-600">Weight <?= esc((string) ($font['font_weight'] ?? 400)) ?> · <?= esc((string) ($font['font_style'] ?? 'normal')) ?></p>
                            <p class="mt-1 break-all text-xs font-bold text-slate-500"><?= esc((string) ($font['original_name'] ?? $font['file_path'] ?? '')) ?></p>
                        </div>
                        <div class="flex shrink-0 flex-row gap-2 md:flex-col">
                            <form method="post" action="<?= site_url('admin/custom-fonts/' . $font['id'] . '/toggle') ?>">
                                <?= csrf_field() ?>
                                <button class="h-10 rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700" type="submit"><?= ! empty($font['is_active']) ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                            </form>
                            <form method="post" action="<?= site_url('admin/custom-fonts/' . $font['id'] . '/delete') ?>" onsubmit="return confirm('Hapus font custom ini?');">
                                <?= csrf_field() ?>
                                <button class="h-10 rounded-2xl bg-rose-600 px-4 text-xs font-black text-white" type="submit">Hapus</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach ?>

                <?php if (($fonts ?? []) === []): ?>
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm font-bold text-slate-500">Belum ada font custom.</div>
                <?php endif ?>
            </div>
        </section>
    </main>
</body>
</html>
