<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Business Profile</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6">
        <a href="<?= site_url('dashboard') ?>" class="text-sm font-semibold text-teal-700">Kembali ke dashboard</a>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Business Profile</p>
            <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight"><?= esc((string) ($order['invoice_number'] ?? 'Invoice')) ?></h1>
                    <p class="mt-2 text-sm text-slate-500"><?= esc((string) ($order['page_title'] ?? '-')) ?></p>
                </div>
                <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-black uppercase text-slate-700"><?= esc((string) ($order['status'] ?? '-')) ?></span>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-sm text-slate-500">Total</dt>
                    <dd class="mt-1 font-semibold">Rp <?= number_format((int) ($order['amount'] ?? 79000), 0, ',', '.') ?></dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-sm text-slate-500">Metode</dt>
                    <dd class="mt-1 font-semibold"><?= esc((string) ($order['payment_method'] ?? 'Manual')) ?></dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-sm text-slate-500">Website</dt>
                    <dd class="mt-1 break-all font-semibold"><?= esc(site_url('u/' . (string) ($order['page_slug'] ?? ''))) ?></dd>
                </div>
            </dl>

            <?php if (! empty($order['payment_proof'])): ?>
                <a class="mt-5 inline-flex rounded-xl bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700" href="<?= esc(base_url((string) $order['payment_proof']), 'attr') ?>" target="_blank" rel="noopener">Lihat bukti pembayaran</a>
            <?php endif ?>
        </section>

        <?php if (in_array((string) ($order['status'] ?? ''), ['pending', 'pending_payment', 'rejected'], true)): ?>
            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold">Upload bukti pembayaran</h2>
                <form class="mt-5 grid gap-4" action="<?= site_url('business-profile-orders/' . (int) ($order['id'] ?? 0) . '/upload-proof') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" type="file" name="payment_proof" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                    <button class="inline-flex h-11 items-center justify-center rounded-xl bg-teal-700 px-5 text-sm font-semibold text-white hover:bg-teal-800" type="submit">Kirim Bukti Pembayaran</button>
                </form>
            </section>
        <?php elseif ((string) ($order['status'] ?? '') === 'paid'): ?>
            <a class="mt-6 inline-flex h-11 items-center justify-center rounded-xl bg-teal-700 px-5 text-sm font-semibold text-white hover:bg-teal-800" href="<?= site_url('editor/' . (int) ($order['landing_page_id'] ?? 0)) ?>">Buka Editor</a>
        <?php endif ?>
    </main>
</body>
</html>
