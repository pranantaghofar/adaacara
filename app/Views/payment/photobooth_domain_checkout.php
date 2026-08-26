<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout Custom Domain Photobooth</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto grid w-full max-w-5xl flex-1 items-center px-4 py-8 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-[1fr_420px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <a href="<?= site_url('dashboard') ?>" class="text-sm font-semibold text-teal-700">Kembali ke dashboard</a>
                <p class="mt-4 text-xs font-black uppercase tracking-[.18em] text-emerald-700">Add-on Photobooth</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">Custom Domain Photobooth</h1>
                <p class="mt-3 text-slate-600">Pembayaran ini terpisah dari membership adaAcara dan berlaku tahunan untuk domain yang dipilih.</p>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-sm text-slate-500">Domain</dt>
                        <dd class="mt-1 break-all font-semibold"><?= esc((string) ($domain['domain'] ?? '-')) ?></dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-sm text-slate-500">Total</dt>
                        <dd class="mt-1 font-semibold">Rp <?= number_format((int) ($domain['price'] ?? 250000), 0, ',', '.') ?></dd>
                    </div>
                </dl>
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

                <form class="mt-5 space-y-4" action="<?= site_url('photobooth-domain-orders/' . (int) ($domain['id'] ?? 0) . '/checkout') ?>" method="post">
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
                                        <span class="text-xs font-medium text-slate-500">Status pembayaran dikonfirmasi otomatis. Aktivasi DNS/SSL tetap dilakukan admin.</span>
                                    <?php elseif ($method === 'Lynk'): ?>
                                        <span class="text-xs font-medium text-slate-500">Bayar melalui Lynk dan gunakan invoice AdaAcara jika diminta.</span>
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
</body>
</html>
