<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Pembayaran - Admin</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<?php
    $settings = $settings ?? [];
    $paymentMode = (string) ($settings['payment_mode'] ?? 'manual');
    $isProduction = (string) ($settings['midtrans_is_production'] ?? '1') === '1';
?>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Pengaturan Pembayaran', 'adminIcon' => 'money', 'adminActive' => 'payment']) ?>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <p class="mb-6 max-w-2xl text-sm font-semibold text-slate-600">Aktifkan pembayaran manual, Midtrans, Lynk, atau kombinasi yang dibutuhkan. Perubahan wajib memakai password login admin.</p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <form class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm" method="post" action="<?= site_url('admin/payment-settings') ?>">
            <?= csrf_field() ?>
            <section class="grid gap-4 md:grid-cols-3">
                <?php foreach ([
                    'manual' => ['Manual', 'User upload bukti, admin approve.'],
                    'midtrans' => ['Midtrans', 'User bayar otomatis via Midtrans.'],
                    'lynk' => ['Lynk', 'User bayar via link Lynk dan webhook.'],
                    'both' => ['Manual + Midtrans', 'Manual dan Midtrans aktif.'],
                    'manual_lynk' => ['Manual + Lynk', 'Manual dan Lynk aktif.'],
                    'midtrans_lynk' => ['Midtrans + Lynk', 'Dua pembayaran otomatis aktif.'],
                    'all' => ['Semua Metode', 'Manual, Midtrans, dan Lynk aktif.'],
                ] as $value => [$label, $desc]): ?>
                    <label class="cursor-pointer rounded-3xl border p-4 transition <?= $paymentMode === $value ? 'border-emerald-500 bg-emerald-50 text-emerald-900' : 'border-slate-200 bg-white hover:border-emerald-300' ?>">
                        <input class="sr-only" type="radio" name="payment_mode" value="<?= esc($value, 'attr') ?>" <?= $paymentMode === $value ? 'checked' : '' ?>>
                        <span class="block text-sm font-black"><?= esc($label) ?></span>
                        <span class="mt-1 block text-xs font-semibold text-slate-600"><?= esc($desc) ?></span>
                    </label>
                <?php endforeach ?>
            </section>

            <section class="mt-6 grid gap-4 rounded-3xl border border-slate-100 bg-slate-50 p-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600">Environment Midtrans</label>
                    <select class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="midtrans_environment">
                        <option value="production" <?= $isProduction ? 'selected' : '' ?>>Production</option>
                        <option value="sandbox" <?= ! $isProduction ? 'selected' : '' ?>>Sandbox</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600">Client Key</label>
                    <input class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="midtrans_client_key" value="<?= esc((string) ($settings['midtrans_client_key'] ?? '')) ?>" autocomplete="off">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600">Server Key</label>
                    <input class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="midtrans_server_key" value="<?= esc((string) ($settings['midtrans_server_key'] ?? '')) ?>" autocomplete="off">
                    <p class="mt-2 text-xs font-semibold text-slate-500">Notification URL Midtrans: <code><?= esc(site_url('payment/midtrans/notification')) ?></code></p>
                </div>
            </section>

            <section class="mt-6 grid gap-4 rounded-3xl border border-slate-100 bg-slate-50 p-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600">Link Pembayaran Lynk</label>
                    <input class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="lynk_payment_url" value="<?= esc((string) ($settings['lynk_payment_url'] ?? '')) ?>" autocomplete="off" placeholder="https://lynk.id/...">
                    <p class="mt-2 text-xs font-semibold text-slate-500">Gunakan link produk/store Lynk yang meminta user mengisi Invoice AdaAcara jika tersedia.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600">Merchant Key Lynk</label>
                    <input class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="lynk_merchant_key" value="<?= esc((string) ($settings['lynk_merchant_key'] ?? '')) ?>" autocomplete="off">
                    <p class="mt-2 text-xs font-semibold text-slate-500">Webhook URL Lynk: <code><?= esc(site_url('payment/lynk/notification')) ?></code></p>
                </div>
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
</body>
</html>
