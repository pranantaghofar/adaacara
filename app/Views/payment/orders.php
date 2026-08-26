<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Saya - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <?= view('components/app_ui_assets') ?>
</head>
<body class="aa-app-ui flex min-h-screen flex-col text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex min-h-16 max-w-[1850px] items-center justify-between px-4 sm:px-6">
            <a href="<?= site_url('dashboard') ?>" class="inline-flex items-center">
                <img class="h-10 w-auto object-contain" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            </a>
            <?= view('components/user_nav_dropdown', ['active' => 'orders']) ?>
        </div>
    </header>

    <main class="mx-auto w-full max-w-[1850px] flex-1 px-4 py-8 sm:px-6">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>

        <p class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-amber-700">
            <a class="no-underline transition hover:text-amber-900" href="<?= site_url('dashboard') ?>">Dashboard</a>
            <span aria-hidden="true">&gt;</span>
            <span>Order Saya</span>
        </p>
        <h1 class="text-3xl font-semibold tracking-tight">Order Saya</h1>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <?php if ($orders === []): ?>
                <div class="p-8 text-slate-600">Belum ada order.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Invoice</th>
                                <th class="px-5 py-4">Paket</th>
                                <th class="px-5 py-4">Amount</th>
                                <th class="px-5 py-4">Metode</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($orders as $order): ?>
                                <?php
                                    $paymentMethodLabel = match ((string) ($order['payment_method'] ?? '')) {
                                        'Midtrans' => 'Pembayaran Otomatis',
                                        'Lynk' => 'Lynk',
                                        default => (string) ($order['payment_method'] ?? '-'),
                                    };
                                ?>
                                <tr>
                                    <td class="px-5 py-4 font-semibold"><?= esc($order['invoice_number']) ?></td>
                                    <td class="px-5 py-4"><?= esc($order['plan_name'] ?? '-') ?></td>
                                    <td class="px-5 py-4">Rp <?= number_format((int) $order['amount'], 0, ',', '.') ?></td>
                                    <td class="px-5 py-4"><?= esc($paymentMethodLabel) ?></td>
                                    <td class="px-5 py-4"><?= esc($order['status']) ?></td>
                                    <td class="px-5 py-4"><a class="font-semibold text-amber-700" href="<?= site_url('orders/' . $order['id']) ?>">Detail</a></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php endif ?>
        </div>

        <?php $photoboothDomainOrders = $photoboothDomainOrders ?? []; ?>
        <section class="mt-8 overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
            <div class="border-b border-emerald-100 bg-emerald-50/70 px-5 py-4">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Add-on Photobooth</p>
                <h2 class="mt-1 text-xl font-black text-slate-950">Order Custom Domain Photobooth</h2>
                <p class="mt-1 text-sm font-semibold text-slate-600">Invoice custom domain terpisah dari membership dan tidak mengubah paket akun.</p>
            </div>
            <?php if ($photoboothDomainOrders === []): ?>
                <div class="p-8 text-slate-600">Belum ada order custom domain Photobooth.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Invoice</th>
                                <th class="px-5 py-4">Domain</th>
                                <th class="px-5 py-4">Undangan</th>
                                <th class="px-5 py-4">Amount</th>
                                <th class="px-5 py-4">Metode</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($photoboothDomainOrders as $order): ?>
                                <?php
                                    $paymentMethodLabel = match ((string) ($order['payment_method'] ?? '')) {
                                        'Midtrans' => 'Pembayaran Otomatis',
                                        'Lynk' => 'Lynk',
                                        default => (string) ($order['payment_method'] ?? '-'),
                                    };
                                    $statusLabel = match ((string) ($order['status'] ?? '')) {
                                        'pending' => 'Invoice dibuat',
                                        'pending_payment' => 'Menunggu pembayaran',
                                        'waiting_approval' => 'Menunggu konfirmasi admin',
                                        'paid' => 'Pembayaran terkonfirmasi',
                                        'rejected' => 'Bukti pembayaran ditolak',
                                        'failed' => 'Pembayaran gagal',
                                        'expired' => 'Invoice kedaluwarsa',
                                        'refunded' => 'Pembayaran direfund',
                                        default => (string) ($order['status'] ?? '-'),
                                    };
                                ?>
                                <tr>
                                    <td class="px-5 py-4 font-semibold"><?= esc((string) ($order['invoice_number'] ?? '-')) ?></td>
                                    <td class="px-5 py-4 break-all"><?= esc((string) ($order['domain'] ?? '-')) ?></td>
                                    <td class="px-5 py-4"><?= esc((string) ($order['page_title'] ?? '-')) ?></td>
                                    <td class="px-5 py-4">Rp <?= number_format((int) ($order['amount'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="px-5 py-4"><?= esc($paymentMethodLabel) ?></td>
                                    <td class="px-5 py-4"><?= esc($statusLabel) ?></td>
                                    <td class="px-5 py-4"><a class="font-semibold text-emerald-700" href="<?= site_url('photobooth-domain-orders/' . (int) ($order['id'] ?? 0)) ?>">Detail</a></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php endif ?>
        </section>

        <?php $businessProfileOrders = $businessProfileOrders ?? []; ?>
        <section class="mt-8 overflow-hidden rounded-2xl border border-pink-100 bg-white shadow-sm">
            <div class="border-b border-pink-100 bg-pink-50/70 px-5 py-4">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-pink-700">Website Business Profile</p>
                <h2 class="mt-1 text-xl font-black text-slate-950">Order Business Profile</h2>
                <p class="mt-1 text-sm font-semibold text-slate-600">Invoice Rp79.000 untuk satu website, terpisah dari membership akun.</p>
            </div>
            <?php if ($businessProfileOrders === []): ?>
                <div class="p-8 text-slate-600">Belum ada order Business Profile.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Invoice</th>
                                <th class="px-5 py-4">Website</th>
                                <th class="px-5 py-4">Amount</th>
                                <th class="px-5 py-4">Metode</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($businessProfileOrders as $order): ?>
                                <?php
                                    $statusLabel = match ((string) ($order['status'] ?? '')) {
                                        'pending' => 'Invoice dibuat',
                                        'pending_payment' => 'Menunggu pembayaran',
                                        'waiting_approval' => 'Menunggu konfirmasi admin',
                                        'paid' => 'Aktif',
                                        'rejected' => 'Bukti pembayaran ditolak',
                                        'failed' => 'Pembayaran gagal',
                                        'expired' => 'Invoice kedaluwarsa',
                                        default => (string) ($order['status'] ?? '-'),
                                    };
                                ?>
                                <tr>
                                    <td class="px-5 py-4 font-semibold"><?= esc((string) ($order['invoice_number'] ?? '-')) ?></td>
                                    <td class="px-5 py-4"><?= esc((string) ($order['page_title'] ?? '-')) ?></td>
                                    <td class="px-5 py-4">Rp <?= number_format((int) ($order['amount'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="px-5 py-4"><?= esc((string) ($order['payment_method'] ?? '-')) ?></td>
                                    <td class="px-5 py-4"><?= esc($statusLabel) ?></td>
                                    <td class="px-5 py-4"><a class="font-semibold text-pink-700" href="<?= site_url('business-profile-orders/' . (int) ($order['id'] ?? 0)) ?>">Detail</a></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php endif ?>
        </section>
    </main>

    <?= view('components/site_footer') ?>
</body>
</html>
