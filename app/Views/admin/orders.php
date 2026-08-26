<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Orders - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-value" content="<?= csrf_hash() ?>">
</head>
<body class="aa-app-ui min-h-screen bg-slate-50 text-slate-900 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Orders', 'adminIcon' => 'orders', 'adminActive' => 'orders']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper('admin_permission');
            $canManageOrders = admin_can('admin.orders.manage');
            $canApproveOrders = admin_can('admin.orders.approve');
            $canDeleteOrders = admin_can('admin.orders.delete');
            $canEditOrderStatus = in_array(current_admin_role(), ['superadmin', 'finance_admin'], true);
            $manualOrderStatuses = [
                'pending' => 'Pending',
                'pending_payment' => 'Pending Payment',
                'waiting_approval' => 'Waiting Approval',
                'paid' => 'Paid',
                'rejected' => 'Rejected',
                'failed' => 'Failed',
                'expired' => 'Expired',
            ];
        ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <?php $plans = $plans ?? []; ?>
        <?php $plansHaveLynkPaymentUrl = (bool) ($plansHaveLynkPaymentUrl ?? false); ?>
        <?php $plansHaveCompareAtPrice = (bool) ($plansHaveCompareAtPrice ?? false); ?>
        <?php $plansHaveLifetime = (bool) ($plansHaveLifetime ?? false); ?>
        <?php $plansHaveProductType = (bool) ($plansHaveProductType ?? false); ?>
        <?php
            $productTypeLabels = [
                'membership' => 'Membership',
                'business_profile' => 'Business Profile',
                'photobooth_standalone' => 'Photobooth Saja',
                'photographer_gallery' => 'Galeri Fotografer',
                'creator' => 'Creator',
            ];
            $isLifetimePlanKey = static function (array $plan): bool {
                helper('url');
                $keys = [
                    (string) ($plan['slug'] ?? ''),
                    (string) ($plan['name'] ?? ''),
                ];

                foreach ($keys as $key) {
                    if (in_array(url_title(strtolower(trim($key)), '-', true), ['business', 'busseniss', 'buat-niat-jualan'], true)) {
                        return true;
                    }
                }

                return false;
            };
        ?>
        <?php if ($canManageOrders): ?>
        <section class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">Paket Member</p>
                    <h2 class="text-xl font-semibold tracking-tight">Kelola paket yang bisa dipilih user</h2>
                </div>
                <p class="text-xs font-medium text-slate-500">Paket OFF tidak tampil di halaman pilihan paket user.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] text-left text-sm">
                    <thead class="bg-white text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Paket</th>
                            <th class="px-5 py-3">Harga</th>
                            <th class="px-5 py-3">Limit</th>
                            <th class="px-5 py-3">Fitur</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($plans as $plan): ?>
                            <?php
                                $planId = (int) $plan['id'];
                                $isActive = ($plan['status'] ?? 'active') === 'active';
                                $isUnlimitedPages = ((int) ($plan['is_unlimited_pages'] ?? 0)) === 1;
                                $planPrice = (int) ($plan['price'] ?? 0);
                                $compareAtPrice = (int) ($plan['compare_at_price'] ?? 0);
                                $hasPlanDiscount = $compareAtPrice > $planPrice && $planPrice > 0;
                                $isLifetimeEligible = $isLifetimePlanKey($plan);
                                $isLifetime = $plansHaveLifetime && $isLifetimeEligible && ((int) ($plan['is_lifetime'] ?? 0)) === 1;
                            ?>
                            <tr class="align-top" data-plan-row="<?= esc((string) $planId) ?>">
                                <td class="px-5 py-4">
                                    <div class="font-semibold"><?= esc($plan['name']) ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?= esc($plan['slug'] ?? '-') ?></div>
                                    <?php if ($plansHaveProductType): ?>
                                        <?php $productType = (string) ($plan['product_type'] ?? 'membership'); ?>
                                        <div class="mt-2 inline-flex rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-black text-violet-700 ring-1 ring-violet-100">
                                            <?= esc($productTypeLabels[$productType] ?? $productType) ?>
                                        </div>
                                    <?php endif ?>
                                    <p class="mt-2 max-w-sm text-xs leading-5 text-slate-600"><?= esc($plan['description'] ?: 'Tidak ada deskripsi.') ?></p>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if ($hasPlanDiscount): ?>
                                        <div class="text-xs font-black text-slate-400 line-through">Rp <?= number_format($compareAtPrice, 0, ',', '.') ?></div>
                                    <?php endif ?>
                                    <div class="font-semibold">Rp <?= number_format($planPrice, 0, ',', '.') ?></div>
                                    <?php if ($plansHaveCompareAtPrice && $hasPlanDiscount): ?>
                                        <div class="mt-1 inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-black text-amber-700 ring-1 ring-amber-100">
                                            Harga diskon aktif
                                        </div>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div>
                                        <?php if ($isUnlimitedPages): ?>
                                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700 ring-1 ring-emerald-100">Unlimited</span>
                                        <?php else: ?>
                                            <?= esc((string) ($plan['max_pages'] ?? 0)) ?> halaman
                                        <?php endif ?>
                                    </div>
                                    <div class="mt-2">
                                        <?php if ($isLifetime): ?>
                                            <span class="rounded-full bg-violet-50 px-2.5 py-1 text-xs font-black text-violet-700 ring-1 ring-violet-100">Selamanya</span>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-500"><?= esc((string) ($plan['active_days'] ?? 0)) ?> hari aktif</span>
                                        <?php endif ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs leading-6 text-slate-600">-</td>
                                <td class="px-5 py-4">
                                    <button
                                        type="button"
                                        class="inline-flex min-w-24 items-center justify-center rounded-full px-3 py-2 text-xs font-bold transition <?= $isActive ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>"
                                        data-plan-toggle
                                        data-plan-id="<?= esc((string) $planId) ?>"
                                        data-plan-status="<?= $isActive ? 'active' : 'inactive' ?>"
                                        data-toggle-url="<?= site_url('admin/orders/plans/toggle/' . $planId) ?>">
                                        <?= $isActive ? 'ON' : 'OFF' ?>
                                    </button>
                                </td>
                                <td class="px-5 py-4">
                                    <details class="group">
                                        <summary class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-900 transition hover:border-teal-700 hover:text-teal-700">
                                            Edit
                                        </summary>
                                        <form class="mt-3 grid w-[360px] gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4" action="<?= site_url('admin/orders/plans/update/' . $planId) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div>
                                                <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-name-<?= esc((string) $planId) ?>">Nama Paket</label>
                                                <input id="plan-name-<?= esc((string) $planId) ?>" name="name" type="text" value="<?= esc($plan['name']) ?>" required class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100">
                                            </div>
                                            <?php if ($plansHaveProductType): ?>
                                                <div>
                                                    <?php $currentProductType = (string) ($plan['product_type'] ?? 'membership'); ?>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-product-type-<?= esc((string) $planId) ?>">Jenis Paket</label>
                                                    <select id="plan-product-type-<?= esc((string) $planId) ?>" name="product_type" class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100">
                                                        <?php foreach ($productTypeLabels as $value => $label): ?>
                                                            <option value="<?= esc($value, 'attr') ?>" <?= $currentProductType === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                                        <?php endforeach ?>
                                                    </select>
                                                    <p class="mt-1 text-[11px] font-semibold leading-4 text-slate-500">Membership masuk subscription. Produk tools masuk entitlement terpisah.</p>
                                                </div>
                                            <?php endif ?>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-price-<?= esc((string) $planId) ?>">Harga bayar</label>
                                                    <input id="plan-price-<?= esc((string) $planId) ?>" name="price" type="number" min="0" value="<?= esc((string) $planPrice) ?>" required class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100">
                                                    <p class="mt-1 text-[11px] font-semibold text-slate-500">Dipakai checkout dan nominal order.</p>
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-status-<?= esc((string) $planId) ?>">Status</label>
                                                    <select id="plan-status-<?= esc((string) $planId) ?>" name="status" class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100">
                                                        <option value="active" <?= $isActive ? 'selected' : '' ?>>ON / Aktif</option>
                                                        <option value="inactive" <?= ! $isActive ? 'selected' : '' ?>>OFF / Nonaktif</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <?php if ($plansHaveCompareAtPrice): ?>
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-compare-price-<?= esc((string) $planId) ?>">Harga sebelum diskon</label>
                                                    <input id="plan-compare-price-<?= esc((string) $planId) ?>" name="compare_at_price" type="number" min="0" value="<?= esc((string) $compareAtPrice) ?>" class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100">
                                                    <p class="mt-1 text-xs font-semibold text-slate-500">Opsional. Tampil sebagai harga coret jika lebih besar dari Harga bayar.</p>
                                                </div>
                                            <?php endif ?>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-max-pages-<?= esc((string) $planId) ?>">Limit Halaman</label>
                                                    <input id="plan-max-pages-<?= esc((string) $planId) ?>" name="max_pages" type="number" min="0" value="<?= esc((string) ($plan['max_pages'] ?? 0)) ?>" required data-plan-max-pages-input class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100 read-only:bg-slate-100 read-only:text-slate-500">
                                                    <label class="mt-2 flex items-center gap-2 rounded-xl border border-emerald-100 bg-white px-3 py-2 text-xs font-black text-slate-700">
                                                        <input class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" type="checkbox" name="is_unlimited_pages" value="1" data-plan-unlimited-toggle <?= $isUnlimitedPages ? 'checked' : '' ?>>
                                                        <span>Unlimited halaman</span>
                                                    </label>
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-active-days-<?= esc((string) $planId) ?>">Masa Aktif</label>
                                                    <input id="plan-active-days-<?= esc((string) $planId) ?>" name="active_days" type="number" min="1" value="<?= esc((string) ($plan['active_days'] ?? 30)) ?>" required class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100">
                                                    <?php if ($plansHaveLifetime && $isLifetimeEligible): ?>
                                                        <label class="mt-2 flex items-center gap-2 rounded-xl border border-violet-100 bg-white px-3 py-2 text-xs font-black text-slate-700">
                                                            <input class="h-4 w-4 rounded border-slate-300 text-violet-700 focus:ring-violet-600" type="checkbox" name="is_lifetime" value="1" <?= $isLifetime ? 'checked' : '' ?>>
                                                            <span>Aktif selamanya</span>
                                                        </label>
                                                        <p class="mt-1 text-[11px] font-semibold leading-4 text-slate-500">Khusus paket Business. Masa aktif hari tetap disimpan sebagai fallback.</p>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-description-<?= esc((string) $planId) ?>">Deskripsi</label>
                                                <textarea id="plan-description-<?= esc((string) $planId) ?>" name="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100"><?= esc($plan['description'] ?? '') ?></textarea>
                                            </div>
                                            <?php if ($plansHaveLynkPaymentUrl): ?>
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600" for="plan-lynk-url-<?= esc((string) $planId) ?>">Link Checkout Lynk</label>
                                                    <input id="plan-lynk-url-<?= esc((string) $planId) ?>" name="lynk_payment_url" type="url" value="<?= esc((string) ($plan['lynk_payment_url'] ?? ''), 'attr') ?>" placeholder="https://lynk.id/..." class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100">
                                                    <p class="mt-1 text-xs font-semibold text-slate-500">Opsional. Jika kosong, checkout Lynk memakai link global di Pengaturan Pembayaran.</p>
                                                </div>
                                            <?php endif ?>
                                            <input type="hidden" name="remove_branding" value="<?= ((int) ($plan['remove_branding'] ?? 0)) === 1 ? '1' : '0' ?>">
                                            <input type="hidden" name="custom_domain" value="<?= ((int) ($plan['custom_domain'] ?? 0)) === 1 ? '1' : '0' ?>">
                                            <button class="h-10 rounded-xl bg-teal-700 px-4 text-sm font-semibold text-white transition hover:bg-teal-800" type="submit">Simpan Paket</button>
                                        </form>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($plans === []): ?>
                            <tr><td class="px-5 py-6 text-slate-600" colspan="6">Belum ada paket member.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif ?>

        <?php $orderFilters = $orderFilters ?? ['q' => '', 'status' => '', 'method' => '', 'plan' => '']; ?>
        <form class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1.4fr_1fr_1fr_1fr_auto]" method="get" action="<?= site_url('admin/orders') ?>">
            <input class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" type="search" name="q" value="<?= esc($orderFilters['q'] ?? '', 'attr') ?>" placeholder="Cari invoice, user, email, paket">
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="status">
                <option value="">Semua status</option>
                <?php foreach (['pending_payment' => 'Pending Payment', 'waiting_approval' => 'Waiting approval', 'paid' => 'Paid', 'rejected' => 'Rejected', 'pending' => 'Pending', 'failed' => 'Failed', 'expired' => 'Expired'] as $value => $label): ?>
                    <option value="<?= esc($value, 'attr') ?>" <?= ($orderFilters['status'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach ?>
            </select>
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="method">
                <option value="">Semua metode</option>
                <?php foreach (['BCA' => 'BCA', 'BRI' => 'BRI', 'Mandiri' => 'Mandiri', 'BNI' => 'BNI', 'QRIS' => 'QRIS', 'DANA' => 'DANA', 'OVO' => 'OVO', 'GoPay' => 'GoPay', 'ShopeePay' => 'ShopeePay', 'Midtrans' => 'Midtrans', 'Lynk' => 'Lynk'] as $value => $label): ?>
                    <option value="<?= esc($value, 'attr') ?>" <?= ($orderFilters['method'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach ?>
            </select>
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="plan">
                <option value="">Semua paket</option>
                <?php foreach ($plans as $plan): ?>
                    <?php $planSlug = (string) ($plan['slug'] ?? ''); ?>
                    <?php if ($planSlug === '') continue; ?>
                    <option value="<?= esc($planSlug, 'attr') ?>" <?= ($orderFilters['plan'] ?? '') === $planSlug ? 'selected' : '' ?>><?= esc($plan['name'] ?? $planSlug) ?></option>
                <?php endforeach ?>
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-xl bg-teal-700 px-4 text-sm font-semibold text-white transition hover:bg-teal-800" type="submit">Cari</button>
                <a class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:border-teal-700 hover:text-teal-700" href="<?= site_url('admin/orders') ?>">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Paket</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Metode</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Bukti</th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($orders as $order): ?>
                            <tr class="align-top">
                                <td class="px-5 py-4 font-semibold"><?= esc($order['invoice_number']) ?></td>
                                <td class="px-5 py-4"><?= esc($order['user_name'] ?? '-') ?><br><span class="text-xs text-slate-500"><?= esc($order['user_email'] ?? '-') ?></span></td>
                                <td class="px-5 py-4">
                                    <?= esc($order['plan_name'] ?? '-') ?>
                                    <?php if ($plansHaveProductType): ?>
                                        <?php $orderProductType = (string) ($order['product_type'] ?? 'membership'); ?>
                                        <div class="mt-1 text-xs font-black text-violet-700"><?= esc($productTypeLabels[$orderProductType] ?? $orderProductType) ?></div>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4">Rp <?= number_format((int) $order['amount'], 0, ',', '.') ?></td>
                                <?php
                                    $paymentMethodLabel = match ((string) ($order['payment_method'] ?? '')) {
                                        'Midtrans' => 'Midtrans',
                                        'Lynk' => 'Lynk',
                                        default => (string) ($order['payment_method'] ?? '-'),
                                    };
                                ?>
                                <td class="px-5 py-4">
                                    <?= esc($paymentMethodLabel) ?>
                                    <?php if (($order['payment_method'] ?? '') === 'Lynk' && ! empty($order['lynk_status'])): ?>
                                        <div class="mt-1 max-w-40 truncate text-xs font-semibold text-slate-500"><?= esc((string) $order['lynk_status']) ?></div>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-semibold"><?= esc($order['status']) ?></span>
                                    <?php if (($order['payment_method'] ?? '') === 'Lynk' && ! empty($order['lynk_match_note'])): ?>
                                        <div class="mt-1 max-w-40 truncate text-xs text-slate-500"><?= esc((string) $order['lynk_match_note']) ?></div>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if (! empty($order['payment_proof'])): ?>
                                        <a class="font-semibold text-teal-700" href="<?= base_url($order['payment_proof']) ?>" target="_blank" rel="noopener">Lihat</a>
                                    <?php else: ?>
                                        <span class="text-slate-500">-</span>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4">
                                    <?php
                                        $orderStatus = (string) ($order['status'] ?? '');
                                        $canDeleteThisOrder = $canDeleteOrders && $orderStatus !== 'paid';
                                    ?>
                                    <?php if (($orderStatus === 'waiting_approval' && ($canApproveOrders || $canManageOrders)) || $canDeleteThisOrder || $canEditOrderStatus): ?>
                                        <div class="flex max-w-xs flex-wrap gap-2">
                                            <?php if ($orderStatus === 'waiting_approval' && $canApproveOrders): ?>
                                            <form action="<?= site_url('admin/orders/approve/' . $order['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <button class="rounded-xl bg-teal-700 px-3 py-2 text-xs font-semibold text-white" type="submit">Approve</button>
                                            </form>
                                            <?php endif ?>
                                            <?php if ($orderStatus === 'waiting_approval' && $canManageOrders): ?>
                                            <form action="<?= site_url('admin/orders/reject/' . $order['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700" type="submit">Reject</button>
                                            </form>
                                            <?php endif ?>
                                            <?php if ($canDeleteThisOrder): ?>
                                            <form action="<?= site_url('admin/orders/delete/' . $order['id']) ?>" method="post" onsubmit="return confirm('Hapus invoice <?= esc((string) ($order['invoice_number'] ?? ''), 'js') ?>? Aksi ini tidak bisa dibatalkan.');">
                                                <?= csrf_field() ?>
                                                <button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:border-rose-200 hover:bg-rose-50" type="submit">Delete</button>
                                            </form>
                                            <?php endif ?>
                                            <?php if ($canEditOrderStatus): ?>
                                            <form class="flex w-full flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2" action="<?= site_url('admin/orders/status/' . $order['id']) ?>" method="post" onsubmit="return confirm('Ubah status invoice <?= esc((string) ($order['invoice_number'] ?? ''), 'js') ?> secara manual?');">
                                                <?= csrf_field() ?>
                                                <select class="h-9 min-w-40 flex-1 rounded-lg border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700 outline-none focus:border-teal-700 focus:ring-2 focus:ring-teal-100" name="status" aria-label="Ubah status invoice">
                                                    <?php foreach ($manualOrderStatuses as $statusValue => $statusLabel): ?>
                                                        <option value="<?= esc($statusValue, 'attr') ?>" <?= $orderStatus === $statusValue ? 'selected' : '' ?>><?= esc($statusLabel) ?></option>
                                                    <?php endforeach ?>
                                                </select>
                                                <button class="h-9 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-black text-amber-700 transition hover:bg-amber-100" type="submit">Ubah</button>
                                            </form>
                                            <?php endif ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-500">-</span>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($orders === []): ?>
                            <tr><td class="px-5 py-6 text-slate-600" colspan="8">Belum ada order.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
    (function() {
        if (window.AdaAcaraAdminPlanToggleReady) return;
        window.AdaAcaraAdminPlanToggleReady = true;

        const csrfNameMeta = document.querySelector('meta[name="csrf-token-name"]');
        const csrfValueMeta = document.querySelector('meta[name="csrf-token-value"]');

        function setButtonState(button, status) {
            const isActive = status === 'active';
            button.dataset.planStatus = status;
            button.textContent = isActive ? 'ON' : 'OFF';
            button.classList.toggle('bg-emerald-100', isActive);
            button.classList.toggle('text-emerald-700', isActive);
            button.classList.toggle('hover:bg-emerald-200', isActive);
            button.classList.toggle('bg-slate-100', !isActive);
            button.classList.toggle('text-slate-500', !isActive);
            button.classList.toggle('hover:bg-slate-200', !isActive);
        }

        document.addEventListener('click', async function(event) {
            const button = event.target.closest('[data-plan-toggle]');
            if (!button || button.disabled) return;

            const currentStatus = button.dataset.planStatus === 'active' ? 'active' : 'inactive';
            const nextStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const originalText = button.textContent;
            const formData = new FormData();
            formData.append('status', nextStatus);

            if (csrfNameMeta && csrfValueMeta) {
                formData.append(csrfNameMeta.content, csrfValueMeta.content);
            }

            button.disabled = true;
            button.textContent = '...';

            try {
                const response = await fetch(button.dataset.toggleUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json().catch(function() {
                    return {};
                });

                if (data.csrf_hash && csrfValueMeta) {
                    csrfValueMeta.content = data.csrf_hash;
                }

                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Status paket gagal diubah.');
                }

                setButtonState(button, data.status || nextStatus);
            } catch (error) {
                button.textContent = originalText;
                setButtonState(button, currentStatus);
                aaToast(error.message || 'Status paket gagal diubah.', 'error');
            } finally {
                button.disabled = false;
            }
        });

        function syncUnlimitedToggle(toggle) {
            const form = toggle.closest('form');
            const input = form ? form.querySelector('[data-plan-max-pages-input]') : null;
            if (!input) return;

            input.readOnly = toggle.checked;
            input.required = true;
            input.setAttribute('aria-disabled', toggle.checked ? 'true' : 'false');
        }

        document.querySelectorAll('[data-plan-unlimited-toggle]').forEach(function(toggle) {
            syncUnlimitedToggle(toggle);
            toggle.addEventListener('change', function() {
                syncUnlimitedToggle(toggle);
            });
        });
    })();
    </script>
</body>
</html>
