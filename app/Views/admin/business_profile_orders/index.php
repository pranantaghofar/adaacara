<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Business Profile - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Order Business Profile', 'adminKicker' => 'Business Profile', 'adminIcon' => 'briefcase', 'adminActive' => 'businessProfileOrders']) ?>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper(['admin_permission', 'aa_datetime']);
            $items = $items ?? [];
            $filters = $filters ?? ['status' => '', 'q' => ''];
            $statusOptions = $statusOptions ?? [];
            $canManage = admin_can('admin.orders.approve');
            $statusLabels = [
                'pending' => 'Pending',
                'pending_payment' => 'Menunggu bayar',
                'waiting_approval' => 'Menunggu approval',
                'paid' => 'Paid',
                'rejected' => 'Rejected',
                'failed' => 'Failed',
                'expired' => 'Expired',
            ];
            $statusBadgeClass = static function (string $status): string {
                return match ($status) {
                    'paid' => 'bg-emerald-100 text-emerald-800',
                    'waiting_approval', 'pending_payment', 'pending' => 'bg-amber-100 text-amber-800',
                    'rejected', 'failed', 'expired' => 'bg-rose-100 text-rose-700',
                    default => 'bg-slate-100 text-slate-700',
                };
            };
            $formatDateTime = static function (?string $value): string {
                $value = trim((string) $value);
                if ($value === '') {
                    return '-';
                }

                return function_exists('aa_format_wib_datetime') ? aa_format_wib_datetime($value) : $value;
            };
        ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="mb-5 rounded-[28px] border border-emerald-100 bg-white/90 p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Produk terpisah</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight">Order website Business Profile</h1>
                    <p class="mt-1 text-sm font-semibold text-slate-600">Konfirmasi pembayaran Rp79.000 untuk mengaktifkan satu website Business Profile tanpa membuat subscription.</p>
                </div>
            </div>

            <form class="grid gap-3 lg:grid-cols-[1fr_220px_auto]" method="get" action="<?= site_url('admin/business-profile-orders') ?>">
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="search" name="q" value="<?= esc((string) ($filters['q'] ?? ''), 'attr') ?>" placeholder="Cari invoice, website, slug, user">
                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="status">
                    <option value="">Semua status</option>
                    <?php foreach ($statusOptions as $value): ?>
                        <option value="<?= esc((string) $value, 'attr') ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= esc($statusLabels[$value] ?? $value) ?></option>
                    <?php endforeach ?>
                </select>
                <div class="flex gap-2">
                    <button class="h-12 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white" type="submit">Cari</button>
                    <a class="inline-flex h-12 items-center rounded-2xl border border-slate-200 px-4 text-sm font-black text-slate-700" href="<?= site_url('admin/business-profile-orders') ?>">Reset</a>
                </div>
            </form>
        </section>

        <?php if (empty($isReady)): ?>
            <section class="rounded-[28px] border border-amber-200 bg-amber-50 p-6 text-sm font-bold leading-6 text-amber-800">
                Tabel Business Profile belum tersedia. Jalankan SQL:
                <div class="mt-2 rounded-2xl bg-white/70 px-3 py-2 font-mono text-xs">database/alter_business_profile_website_entitlements.sql</div>
            </section>
        <?php elseif ($items === []): ?>
            <section class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 p-10 text-center text-sm font-bold text-slate-500">Belum ada order Business Profile.</section>
        <?php else: ?>
            <section class="overflow-hidden rounded-[28px] border border-emerald-100 bg-white/90 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1080px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-5 py-3">Invoice</th>
                                <th class="px-5 py-3">Website</th>
                                <th class="px-5 py-3">User</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $id = (int) ($item['id'] ?? 0);
                                    $status = (string) ($item['status'] ?? 'pending');
                                    $slug = (string) ($item['page_slug'] ?? '');
                                ?>
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-mono text-xs font-black"><?= esc((string) ($item['invoice_number'] ?? '-')) ?></div>
                                        <div class="mt-1 text-sm font-black text-slate-950">Rp<?= esc(number_format((int) ($item['amount'] ?? 79000), 0, ',', '.')) ?></div>
                                        <?php if (! empty($item['payment_proof'])): ?>
                                            <a class="mt-2 inline-flex text-xs font-black text-emerald-700" href="<?= esc(base_url((string) $item['payment_proof']), 'attr') ?>" target="_blank" rel="noopener">Lihat bukti</a>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold"><?= esc((string) ($item['page_title'] ?? '-')) ?></div>
                                        <?php if ($slug !== ''): ?>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <a class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700" href="<?= site_url('u/' . $slug) ?>" target="_blank" rel="noopener">Website</a>
                                                <a class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700" href="<?= site_url('editor/' . (int) ($item['landing_page_id'] ?? 0)) ?>">Editor</a>
                                            </div>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold"><?= esc((string) ($item['user_name'] ?? '-')) ?></div>
                                        <div class="mt-1 text-xs font-semibold text-slate-500"><?= esc((string) ($item['user_email'] ?? '-')) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black <?= esc($statusBadgeClass($status), 'attr') ?>"><?= esc($statusLabels[$status] ?? $status) ?></span>
                                    </td>
                                    <td class="px-5 py-4 text-xs font-semibold leading-5 text-slate-500">
                                        <div>Dibuat: <?= esc($formatDateTime($item['created_at'] ?? '')) ?></div>
                                        <div>Dibayar: <?= esc($formatDateTime($item['paid_at'] ?? '')) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php if ($canManage): ?>
                                            <div class="flex flex-wrap gap-2">
                                                <form method="post" action="<?= site_url('admin/business-profile-orders/' . $id . '/quick/paid') ?>">
                                                    <?= csrf_field() ?>
                                                    <button class="h-9 rounded-xl border border-emerald-200 bg-emerald-50 px-3 text-xs font-black text-emerald-800" type="submit">Konfirmasi paid</button>
                                                </form>
                                                <form method="post" action="<?= site_url('admin/business-profile-orders/' . $id . '/quick/rejected') ?>">
                                                    <?= csrf_field() ?>
                                                    <button class="h-9 rounded-xl border border-rose-200 bg-rose-50 px-3 text-xs font-black text-rose-700" type="submit">Tolak</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-400">Read only</span>
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif ?>
    </main>
</body>
</html>
