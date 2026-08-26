<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Domain Photobooth - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Domain Photobooth', 'adminKicker' => 'Photobooth', 'adminIcon' => 'globe', 'adminActive' => 'photoboothDomains']) ?>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper(['admin_permission', 'aa_datetime']);
            $items = $items ?? [];
            $filters = $filters ?? ['status' => '', 'payment_status' => '', 'q' => ''];
            $statusOptions = $statusOptions ?? [];
            $availabilityOptions = $availabilityOptions ?? [];
            $paymentOptions = $paymentOptions ?? [];
            $canManage = admin_can('admin.photobooth_domains.manage');
            $statusLabels = [
                'checking' => 'Pengecekan',
                'available' => 'Domain tersedia',
                'unavailable' => 'Tidak tersedia',
                'waiting_payment' => 'Menunggu bayar',
                'waiting_activation' => 'Menunggu aktivasi',
                'active' => 'Aktif',
                'disabled' => 'Nonaktif',
            ];
            $availabilityLabels = [
                'checking' => 'Checking',
                'available' => 'Available',
                'unavailable' => 'Unavailable',
                'manual_review' => 'Manual review',
            ];
            $paymentLabels = [
                'unpaid' => 'Belum bayar',
                'waiting_confirmation' => 'Menunggu konfirmasi',
                'paid' => 'Paid',
                'expired' => 'Expired',
                'refunded' => 'Refunded',
            ];
            $statusBadgeClass = static function (string $status): string {
                return match ($status) {
                    'active' => 'bg-emerald-100 text-emerald-800',
                    'available', 'waiting_payment', 'waiting_activation' => 'bg-amber-100 text-amber-800',
                    'unavailable', 'disabled' => 'bg-rose-100 text-rose-700',
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

        <?php if (! empty(session()->getFlashdata('success'))): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (! empty(session()->getFlashdata('error'))): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="mb-5 rounded-[28px] border border-emerald-100 bg-white/90 p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Review manual</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight">Request custom domain Photobooth</h1>
                    <p class="mt-1 text-sm font-semibold text-slate-600">Cek ketersediaan domain, status pembayaran, dan aktivasi tanpa mengubah routing public.</p>
                </div>
                <a class="inline-flex h-11 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-black text-emerald-800" href="<?= site_url('admin/guest-memories') ?>">Guest Memories</a>
            </div>

            <form class="grid gap-3 lg:grid-cols-[1fr_190px_220px_auto]" method="get" action="<?= site_url('admin/photobooth-domains') ?>">
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="search" name="q" value="<?= esc((string) ($filters['q'] ?? ''), 'attr') ?>" placeholder="Cari domain, undangan, slug, user">
                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="status">
                    <option value="">Semua status</option>
                    <?php foreach ($statusOptions as $value): ?>
                        <option value="<?= esc((string) $value, 'attr') ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= esc($statusLabels[$value] ?? $value) ?></option>
                    <?php endforeach ?>
                </select>
                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="payment_status">
                    <option value="">Semua pembayaran</option>
                    <?php foreach ($paymentOptions as $value): ?>
                        <option value="<?= esc((string) $value, 'attr') ?>" <?= ($filters['payment_status'] ?? '') === $value ? 'selected' : '' ?>><?= esc($paymentLabels[$value] ?? $value) ?></option>
                    <?php endforeach ?>
                </select>
                <div class="flex gap-2">
                    <button class="h-12 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white" type="submit">Cari</button>
                    <a class="inline-flex h-12 items-center rounded-2xl border border-slate-200 px-4 text-sm font-black text-slate-700" href="<?= site_url('admin/photobooth-domains') ?>">Reset</a>
                </div>
            </form>

            <div class="mt-4 grid gap-2 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-xs font-bold leading-5 text-emerald-900 md:grid-cols-4">
                <div><span class="font-black">1.</span> Cek domain di registrar.</div>
                <div><span class="font-black">2.</span> Tandai tersedia atau tidak tersedia.</div>
                <div><span class="font-black">3.</span> Jika tersedia, lanjutkan instruksi pembayaran.</div>
                <div><span class="font-black">4.</span> Aktivasi DNS/SSL tetap manual di tahap berikutnya.</div>
            </div>
        </section>

        <?php if (empty($isReady)): ?>
            <section class="rounded-[28px] border border-amber-200 bg-amber-50 p-6 text-sm font-bold leading-6 text-amber-800">
                Tabel custom domain Photobooth belum tersedia. Jalankan SQL:
                <div class="mt-2 rounded-2xl bg-white/70 px-3 py-2 font-mono text-xs">database/alter_photobooth_custom_domains.sql</div>
            </section>
        <?php elseif ($items === []): ?>
            <section class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 p-10 text-center text-sm font-bold text-slate-500">Belum ada request custom domain Photobooth.</section>
        <?php else: ?>
            <?php
                $quickActions = [
                    'checking' => ['label' => 'Sedang dicek', 'class' => 'border-slate-200 bg-white text-slate-700 hover:border-slate-400'],
                    'available' => ['label' => 'Domain tersedia', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-500'],
                    'unavailable' => ['label' => 'Tidak tersedia', 'class' => 'border-rose-200 bg-rose-50 text-rose-700 hover:border-rose-400'],
                    'waiting-payment' => ['label' => 'Menunggu pembayaran', 'class' => 'border-amber-200 bg-amber-50 text-amber-800 hover:border-amber-400'],
                    'paid' => ['label' => 'Pembayaran dikonfirmasi', 'class' => 'border-sky-200 bg-sky-50 text-sky-800 hover:border-sky-400'],
                ];
            ?>
            <section class="overflow-hidden rounded-[28px] border border-emerald-100 bg-white/90 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1280px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-5 py-3">Domain</th>
                                <th class="px-5 py-3">Undangan</th>
                                <th class="px-5 py-3">User</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Pembayaran</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Update Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $id = (int) ($item['id'] ?? 0);
                                    $domain = (string) ($item['domain'] ?? '');
                                    $status = (string) ($item['status'] ?? 'checking');
                                    $availability = (string) ($item['availability_status'] ?? 'checking');
                                    $paymentStatus = (string) ($item['payment_status'] ?? 'unpaid');
                                    $slug = (string) ($item['page_slug'] ?? '');
                                ?>
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-black text-slate-950"><?= esc($domain !== '' ? $domain : '-') ?></div>
                                        <div class="mt-1 text-xs font-bold text-slate-500">Rp<?= esc(number_format((int) ($item['price'] ?? 250000), 0, ',', '.')) ?> / tahun</div>
                                        <div class="mt-2 max-w-xs text-xs font-semibold leading-5 text-slate-500"><?= esc((string) ($item['notes'] ?? '')) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold"><?= esc((string) ($item['page_title'] ?? '-')) ?></div>
                                        <?php if ($slug !== ''): ?>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <a class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700" href="<?= site_url('u/' . $slug) ?>" target="_blank" rel="noopener">Website</a>
                                                <a class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700" href="<?= site_url('u/' . $slug . '/memories') ?>" target="_blank" rel="noopener">Memories</a>
                                            </div>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold"><?= esc((string) ($item['user_name'] ?? '-')) ?></div>
                                        <div class="mt-1 text-xs font-semibold text-slate-500"><?= esc((string) ($item['user_email'] ?? '-')) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black <?= esc($statusBadgeClass($status), 'attr') ?>"><?= esc($statusLabels[$status] ?? $status) ?></span>
                                        <div class="mt-2 text-xs font-bold text-slate-500">Availability: <?= esc($availabilityLabels[$availability] ?? $availability) ?></div>
                                        <?php if (! empty($item['active_until'])): ?>
                                            <div class="mt-1 text-xs font-bold text-emerald-700">Aktif sampai <?= esc((string) $item['active_until']) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700"><?= esc($paymentLabels[$paymentStatus] ?? $paymentStatus) ?></span>
                                        <?php if (! empty($item['payment_proof'])): ?>
                                            <div class="mt-2">
                                                <a class="text-xs font-black text-emerald-700" href="<?= esc(base_url((string) $item['payment_proof']), 'attr') ?>" target="_blank" rel="noopener">Lihat bukti</a>
                                            </div>
                                        <?php endif ?>
                                        <?php if (! empty($item['payment_note'])): ?>
                                            <div class="mt-2 max-w-xs text-xs font-semibold leading-5 text-slate-500"><?= esc((string) $item['payment_note']) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4 text-xs font-semibold leading-5 text-slate-500">
                                        <div>Request: <?= esc($formatDateTime($item['requested_at'] ?? $item['created_at'] ?? '')) ?></div>
                                        <div>Checked: <?= esc($formatDateTime($item['checked_at'] ?? '')) ?></div>
                                        <div>Active: <?= esc($formatDateTime($item['activated_at'] ?? '')) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php if ($canManage): ?>
                                            <div class="mb-3 rounded-2xl border border-slate-100 bg-slate-50/80 p-3">
                                                <p class="mb-2 text-xs font-black uppercase tracking-[.14em] text-slate-500">Aksi cepat validasi</p>
                                                <div class="flex flex-wrap gap-2">
                                                    <?php foreach ($quickActions as $action => $meta): ?>
                                                        <form method="post" action="<?= site_url('admin/photobooth-domains/' . $id . '/quick/' . $action) ?>">
                                                            <?= csrf_field() ?>
                                                            <button class="h-9 rounded-xl border px-3 text-xs font-black transition <?= esc($meta['class'], 'attr') ?>" type="submit"><?= esc($meta['label']) ?></button>
                                                        </form>
                                                    <?php endforeach ?>
                                                </div>
                                            </div>
                                            <form class="grid min-w-[360px] gap-3" method="post" action="<?= site_url('admin/photobooth-domains/' . $id . '/update') ?>">
                                                <?= csrf_field() ?>
                                                <div class="grid gap-2 sm:grid-cols-3">
                                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                                        Status
                                                        <select class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold" name="status">
                                                            <?php foreach ($statusOptions as $value): ?>
                                                                <option value="<?= esc((string) $value, 'attr') ?>" <?= $status === $value ? 'selected' : '' ?>><?= esc($statusLabels[$value] ?? $value) ?></option>
                                                            <?php endforeach ?>
                                                        </select>
                                                    </label>
                                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                                        Availability
                                                        <select class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold" name="availability_status">
                                                            <?php foreach ($availabilityOptions as $value): ?>
                                                                <option value="<?= esc((string) $value, 'attr') ?>" <?= $availability === $value ? 'selected' : '' ?>><?= esc($availabilityLabels[$value] ?? $value) ?></option>
                                                            <?php endforeach ?>
                                                        </select>
                                                    </label>
                                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                                        Payment
                                                        <select class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold" name="payment_status">
                                                            <?php foreach ($paymentOptions as $value): ?>
                                                                <option value="<?= esc((string) $value, 'attr') ?>" <?= $paymentStatus === $value ? 'selected' : '' ?>><?= esc($paymentLabels[$value] ?? $value) ?></option>
                                                            <?php endforeach ?>
                                                        </select>
                                                    </label>
                                                </div>
                                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                                    Aktif sampai
                                                    <input class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold" type="date" name="active_until" value="<?= esc((string) ($item['active_until'] ?? ''), 'attr') ?>">
                                                </label>
                                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                                    Catatan admin
                                                    <textarea class="min-h-20 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold leading-5" name="notes" maxlength="500"><?= esc((string) ($item['notes'] ?? '')) ?></textarea>
                                                </label>
                                                <button class="h-10 rounded-xl bg-slate-900 px-4 text-xs font-black text-white" type="submit">Update Status</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-500">Mode lihat</span>
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
