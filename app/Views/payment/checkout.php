<?php
    $planKey = strtolower((string) ($plan['slug'] ?? $plan['name'] ?? ''));
    $planName = match ($planKey) {
        'basic', 'starter' => 'Buat Acara Sendiri',
        'premium' => 'Buat Coba Jualan',
        'business', 'busseniss' => 'Buat Niat Jualan',
        'business-profile-lifetime' => 'Business Profile',
        'digital-photobooth-yearly' => 'Digital Photobooth',
        'photographer-gallery-lifetime' => 'Galeri Klien Fotografer',
        'creator' => 'Daftar Creator',
        default => (string) ($plan['name'] ?? 'Paket'),
    };
    $productType = strtolower(trim((string) ($plan['product_type'] ?? 'membership'))) ?: 'membership';
    $isProductCheckout = ! in_array($productType, ['membership', 'creator'], true);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - <?= esc($planName) ?></title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/google_ads_tag') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto grid w-full max-w-5xl flex-1 items-center px-4 py-8 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-[1fr_420px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <a href="<?= site_url('plans') ?>" class="text-sm font-semibold text-teal-700">Kembali ke paket</a>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight">Checkout <?= esc($planName) ?></h1>
                <p class="mt-3 text-slate-600"><?= esc($plan['description'] ?: ($isProductCheckout ? 'Produk AdaAcara.' : 'Paket undangan event.')) ?></p>

                <div class="mt-6 rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm text-slate-500">Total pembayaran</p>
                    <p class="mt-1 text-4xl font-bold">Rp <?= number_format((int) $plan['price'], 0, ',', '.') ?></p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold">Metode pembayaran</h2>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <div><?= esc($error) ?></div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <?php if ($paymentMethods === []): ?>
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                        Metode pembayaran belum tersedia. Silakan hubungi admin.
                    </div>
                <?php endif ?>

                <form class="mt-5 space-y-4" action="<?= site_url('checkout/' . $plan['slug']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="grid gap-2">
                        <?php foreach ($paymentMethods as $method): ?>
                            <?php
                                $methodLabel = match ($method) {
                                    'Midtrans' => 'Pembayaran Otomatis',
                                    'Lynk' => 'Lynk',
                                    default => $method,
                                };
                            ?>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:border-teal-600">
                                <input type="radio" name="payment_method" value="<?= esc($method) ?>" <?= old('payment_method') === $method ? 'checked' : '' ?> required>
                                <span>
                                    <span class="block font-semibold"><?= esc($methodLabel) ?></span>
                                    <?php if ($method === 'Midtrans'): ?>
                                        <span class="text-xs font-medium text-slate-500"><?= $isProductCheckout ? 'Akses produk aktif otomatis setelah pembayaran berhasil melalui VA, QRIS, e-wallet, atau kartu.' : 'Aktif otomatis setelah pembayaran berhasil melalui VA, QRIS, e-wallet, atau kartu.' ?></span>
                                    <?php elseif ($method === 'Lynk'): ?>
                                        <span class="text-xs font-medium text-slate-500">Bayar melalui Lynk. <?= $isProductCheckout ? 'Akses produk' : 'Paket' ?> aktif otomatis setelah webhook Lynk terverifikasi.</span>
                                    <?php endif ?>
                                </span>
                            </label>
                        <?php endforeach ?>
                    </div>

                    <button class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-teal-700 px-5 text-sm font-semibold text-white hover:bg-teal-800" type="submit" <?= $paymentMethods === [] ? 'disabled' : '' ?>>Lanjut Pembayaran</button>
                </form>
            </section>
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex min-h-20 max-w-5xl flex-col gap-3 px-4 py-5 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-3">
                <img class="h-10 w-auto object-contain" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="lazy">
                <span>© <?= date('Y') ?> AdaAcara. All rights reserved.</span>
            </div>
            <div class="flex flex-wrap gap-4 font-semibold">
                <a class="inline-flex items-center gap-1.5 transition hover:text-amber-700" href="<?= site_url('plans') ?>">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 7 4.5 4L12 4l4.5 7L21 7l-2 12H5L3 7Z"/><path d="M5 19h14"/></svg>
                    <span>Go Premium</span>
                </a>
                <a class="transition hover:text-teal-700" href="<?= site_url('dashboard') ?>">Dashboard</a>
            </div>
        </div>
    </footer>
</body>
</html>
