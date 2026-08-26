<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice <?= esc($order['invoice_number']) ?></title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/google_ads_tag') ?>
    <?= view('components/google_ads_conversion_event') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .aa-payment-file-input {
            width: 100%;
            min-height: 48px;
            cursor: pointer;
            border-style: dashed;
            border-color: #cbd5e1;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .aa-payment-file-input::file-selector-button {
            min-height: 34px;
            margin-right: 12px;
            border: 0;
            border-radius: 11px;
            background: #0f766e;
            color: #ffffff;
            padding: 0 14px;
            font: inherit;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            transition: background .16s ease, transform .16s ease;
        }

        .aa-payment-file-input::-webkit-file-upload-button {
            min-height: 34px;
            margin-right: 12px;
            border: 0;
            border-radius: 11px;
            background: #0f766e;
            color: #ffffff;
            padding: 0 14px;
            font: inherit;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            transition: background .16s ease, transform .16s ease;
        }

        .aa-payment-file-input:hover {
            border-color: #14b8a6;
            background: #ffffff;
            box-shadow: 0 12px 26px rgba(15, 118, 110, .1);
        }

        .aa-payment-file-input:hover::file-selector-button,
        .aa-payment-file-input:hover::-webkit-file-upload-button {
            background: #115e59;
            transform: translateY(-1px);
        }

        .aa-payment-file-input:focus-visible {
            border-color: #0f766e;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, .16);
            outline: none;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <?php helper('aa_datetime'); ?>

    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6">
        <a href="<?= site_url('orders') ?>" class="text-sm font-semibold text-violet-700">Kembali ke order</a>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mt-5 rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm text-slate-500">Invoice</p>
                    <h1 class="text-2xl font-semibold"><?= esc($order['invoice_number']) ?></h1>
                </div>
                <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold"><?= esc($order['status']) ?></span>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">Paket</dt><dd class="mt-1 font-semibold"><?= esc($order['plan_name'] ?? '-') ?></dd></div>
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">Total</dt><dd class="mt-1 font-semibold">Rp <?= number_format((int) $order['amount'], 0, ',', '.') ?></dd></div>
                <?php
                    $paymentMethodLabel = match ((string) ($order['payment_method'] ?? '')) {
                        'Midtrans' => 'Pembayaran Otomatis',
                        'Lynk' => 'Lynk',
                        default => (string) ($order['payment_method'] ?? '-'),
                    };
                ?>
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">Metode</dt><dd class="mt-1 font-semibold"><?= esc($paymentMethodLabel) ?></dd></div>
                <div class="rounded-xl bg-slate-50 p-4"><dt class="text-sm text-slate-500">Tanggal</dt><dd class="mt-1 font-semibold"><?= esc(aa_format_wib_datetime($order['created_at'] ?? '')) ?></dd></div>
            </dl>

            <?php if (! empty($order['payment_proof'])): ?>
                <div class="mt-6">
                    <p class="mb-2 text-sm font-semibold">Bukti pembayaran</p>
                    <a class="text-violet-700" href="<?= base_url($order['payment_proof']) ?>" target="_blank" rel="noopener">Lihat bukti</a>
                </div>
            <?php endif ?>

            <?php if (! empty($order['admin_note'])): ?>
                <div class="mt-6 rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-800"><?= esc($order['admin_note']) ?></div>
            <?php endif ?>

            <?php if (($order['payment_method'] ?? '') === 'Midtrans' && ! empty($order['midtrans_redirect_url']) && ! in_array($order['status'], ['paid', 'failed'], true)): ?>
                <div class="mt-6 rounded-xl border border-violet-200 bg-violet-50 px-4 py-4">
                    <p class="text-sm font-semibold text-violet-800">Pembayaran otomatis siap dilanjutkan. Paket akan aktif otomatis setelah pembayaran berhasil.</p>
                    <a class="mt-3 inline-flex h-11 items-center justify-center rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800" href="<?= esc($order['midtrans_redirect_url'], 'attr') ?>">Lanjut Pembayaran Otomatis</a>
                </div>
            <?php endif ?>

            <?php if (($order['payment_method'] ?? '') === 'Lynk' && ! empty($order['lynk_payment_url']) && in_array((string) ($order['status'] ?? ''), ['pending', 'pending_payment'], true)): ?>
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                    <p class="text-sm font-semibold text-emerald-900">Pembayaran Lynk siap dilanjutkan. Gunakan invoice <span class="font-black"><?= esc($order['invoice_number']) ?></span> jika form Lynk meminta Invoice AdaAcara.</p>
                    <a class="mt-3 inline-flex h-11 items-center justify-center rounded-xl bg-emerald-700 px-5 text-sm font-semibold text-white hover:bg-emerald-800" href="<?= esc($order['lynk_payment_url'], 'attr') ?>" target="_blank" rel="noopener">Bayar via Lynk</a>
                </div>
            <?php endif ?>
            <?php if (($order['payment_method'] ?? '') === 'Lynk' && ($order['status'] ?? '') === 'expired'): ?>
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm font-semibold text-amber-800">
                    Invoice Lynk sudah expired. Silakan buat order baru dari halaman paket jika masih ingin melanjutkan pembelian.
                </div>
            <?php endif ?>
            <?php if (($order['payment_method'] ?? '') === 'Lynk' && ($order['status'] ?? '') === 'failed'): ?>
                <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm font-semibold text-rose-800">
                    Pembayaran Lynk gagal atau dibatalkan. Silakan buat order baru dari halaman paket jika ingin mencoba lagi.
                </div>
            <?php endif ?>
        </section>

        <?php if (! in_array(($order['payment_method'] ?? ''), ['Midtrans', 'Lynk'], true) && in_array($order['status'], ['pending', 'rejected'], true)): ?>
            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold">Upload bukti pembayaran</h2>
                <p class="mt-2 text-sm text-slate-600">Maksimal 2MB. Format: jpg, jpeg, png, webp.</p>
                <form class="mt-5 space-y-4" action="<?= site_url('orders/upload-proof/' . $order['id']) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input class="aa-payment-file-input block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                    <button class="inline-flex h-11 items-center justify-center rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800" type="submit">Upload Bukti</button>
                </form>
            </section>
        <?php endif ?>
    </main>
</body>
</html>
