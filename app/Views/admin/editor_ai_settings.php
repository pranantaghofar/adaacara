<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Editor AI - Admin</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<?php
    $settings = $settings ?? [];
    $providerStatus = $providerStatus ?? [];
    $removeBgProvider = (string) ($settings['remove_bg_provider'] ?? 'poof');
    $magicLayerProvider = (string) ($settings['magic_layer_provider'] ?? 'inherit');
    $removeBgFallbackProvider = (string) ($settings['remove_bg_fallback_provider'] ?? 'none');
    $magicLayerFallbackProvider = (string) ($settings['magic_layer_fallback_provider'] ?? 'inherit');
    $providerOptions = [
        'poof' => ['Poof.bg', 'Provider ringan yang sudah dipakai sebelumnya.'],
        'removebg' => ['Remove.bg', 'Provider alternatif untuk hasil cutout yang lebih rapi pada beberapa gambar.'],
        'rembg' => ['Self-hosted rembg', 'Service mandiri jika ingin memakai server sendiri.'],
    ];
?>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Pengaturan Editor AI', 'adminIcon' => 'ai', 'adminActive' => 'editorAi']) ?>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <p class="mb-6 max-w-3xl text-sm font-semibold leading-6 text-slate-600">
            Pilih provider Remove BG dan Magic Layer tanpa mengubah file .env. API key tetap dibaca dari .env agar aman dan tidak tampil di browser.
        </p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <form class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm" method="post" action="<?= site_url('admin/editor-ai-settings') ?>">
            <?= csrf_field() ?>

            <section>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black">Provider Remove BG</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-600">Dipakai oleh tombol Remove BG biasa di editor.</p>
                    </div>
                    <button id="aaEditorAiTestBtn" class="inline-flex h-10 items-center rounded-2xl border border-emerald-200 bg-white px-4 text-xs font-black text-emerald-700 transition hover:border-emerald-600" type="button">Cek Provider</button>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <?php foreach ($providerOptions as $value => [$label, $desc]): ?>
                        <?php $status = $providerStatus[$value] ?? ['ready' => false, 'details' => 'Status tidak tersedia.']; ?>
                        <label class="cursor-pointer rounded-3xl border p-4 transition <?= $removeBgProvider === $value ? 'border-emerald-500 bg-emerald-50 text-emerald-900' : 'border-slate-200 bg-white hover:border-emerald-300' ?>">
                            <input class="sr-only" type="radio" name="remove_bg_provider" value="<?= esc($value, 'attr') ?>" <?= $removeBgProvider === $value ? 'checked' : '' ?>>
                            <span class="flex items-start justify-between gap-3">
                                <span>
                                    <span class="block text-sm font-black"><?= esc($label) ?></span>
                                    <span class="mt-1 block text-xs font-semibold text-slate-600"><?= esc($desc) ?></span>
                                </span>
                                <span class="mt-0.5 inline-flex rounded-full px-2 py-1 text-[10px] font-black <?= ! empty($status['ready']) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' ?>">
                                    <?= ! empty($status['ready']) ? 'READY' : 'SETUP' ?>
                                </span>
                            </span>
                            <span class="mt-3 block text-xs font-semibold text-slate-500"><?= esc((string) ($status['details'] ?? '')) ?></span>
                        </label>
                    <?php endforeach ?>
                </div>
                <label class="mt-4 grid gap-2 md:max-w-md">
                    <span class="text-xs font-black uppercase tracking-wide text-slate-600">Fallback Remove BG</span>
                    <select class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="remove_bg_fallback_provider">
                        <option value="none" <?= $removeBgFallbackProvider === 'none' ? 'selected' : '' ?>>Tidak pakai fallback</option>
                        <?php foreach ($providerOptions as $value => [$label]): ?>
                            <option value="<?= esc($value, 'attr') ?>" <?= $removeBgFallbackProvider === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach ?>
                    </select>
                    <span class="text-xs font-semibold text-slate-500">Fallback dipakai otomatis jika provider utama gagal saat proses.</span>
                </label>
            </section>

            <section class="mt-6 rounded-3xl border border-slate-100 bg-slate-50 p-5">
                <h2 class="text-base font-black">Provider Magic Layer</h2>
                <p class="mt-1 text-sm font-semibold text-slate-600">Dipakai ketika Magic Layer memproses subject hasil remove background.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2">
                        <span class="text-xs font-black uppercase tracking-wide text-slate-600">Mode Magic Layer</span>
                        <select class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="magic_layer_provider">
                            <option value="inherit" <?= $magicLayerProvider === 'inherit' ? 'selected' : '' ?>>Ikuti Provider Remove BG</option>
                            <?php foreach ($providerOptions as $value => [$label]): ?>
                                <option value="<?= esc($value, 'attr') ?>" <?= $magicLayerProvider === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>
                    <label class="grid gap-2">
                        <span class="text-xs font-black uppercase tracking-wide text-slate-600">Fallback Magic Layer</span>
                        <select class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="magic_layer_fallback_provider">
                            <option value="inherit" <?= $magicLayerFallbackProvider === 'inherit' ? 'selected' : '' ?>>Ikuti Fallback Remove BG</option>
                            <option value="none" <?= $magicLayerFallbackProvider === 'none' ? 'selected' : '' ?>>Tidak pakai fallback</option>
                            <?php foreach ($providerOptions as $value => [$label]): ?>
                                <option value="<?= esc($value, 'attr') ?>" <?= $magicLayerFallbackProvider === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>
                </div>
                <div class="mt-4 rounded-3xl border border-emerald-100 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wide text-emerald-700">Catatan Aman</p>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Jika pilih provider yang belum READY, editor akan menolak proses dengan pesan konfigurasi. Fallback hanya berjalan jika provider fallback READY dan berbeda dari provider utama.
                    </p>
                </div>
                <div id="aaEditorAiTestResult" class="mt-4 hidden whitespace-pre-line rounded-3xl border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-700" role="status" aria-live="polite"></div>
            </section>

            <section class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5">
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-amber-800">Password Login Admin</label>
                <input class="h-12 w-full rounded-2xl border border-amber-200 bg-white px-4 text-sm font-bold outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-100" type="password" name="admin_password" required autocomplete="current-password" placeholder="Masukkan password admin untuk menyimpan">
            </section>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <a class="inline-flex h-12 items-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black" href="<?= site_url('admin') ?>">Batal</a>
                <button class="inline-flex h-12 items-center rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-lg shadow-emerald-700/20" type="submit">Simpan Pengaturan</button>
            </div>
        </form>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('aaEditorAiTestBtn');
            const result = document.getElementById('aaEditorAiTestResult');
            if (!button || !result) return;

            button.addEventListener('click', async function () {
                const originalText = button.textContent;
                button.disabled = true;
                button.textContent = 'Mengecek...';
                result.classList.remove('hidden', 'border-rose-200', 'bg-rose-50');
                result.textContent = 'Mengecek konfigurasi provider...';

                const form = new FormData();
                form.append(<?= json_encode(csrf_token()) ?>, <?= json_encode(csrf_hash()) ?>);

                try {
                    const response = await fetch(<?= json_encode(site_url('admin/editor-ai-settings/test')) ?>, {
                        method: 'POST',
                        body: form,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await response.json().catch(() => ({}));
                    const providers = data.providers || {};
                    const active = data.active || {};
                    const providerLines = Object.keys(providers).map(key => {
                        const item = providers[key] || {};
                        return (item.label || key) + ': ' + (item.ready ? 'READY' : 'SETUP') + ' - ' + (item.details || '');
                    });

                    result.textContent = [
                        'Remove BG aktif: ' + (active.remove_bg_provider || '-'),
                        'Magic Layer aktif: ' + (active.magic_layer_provider || '-'),
                        'Fallback Remove BG: ' + (active.remove_bg_fallback_provider || 'none'),
                        'Fallback Magic Layer: ' + (active.magic_layer_fallback_provider || 'none'),
                        '',
                        providerLines.join('\n'),
                    ].join('\n');

                    if (!response.ok || !data.success) {
                        result.classList.add('border-rose-200', 'bg-rose-50');
                    }
                } catch (error) {
                    result.textContent = 'Gagal mengecek provider. Coba refresh halaman admin.';
                    result.classList.add('border-rose-200', 'bg-rose-50');
                } finally {
                    button.disabled = false;
                    button.textContent = originalText;
                }
            });
        });
    </script>
</body>
</html>
