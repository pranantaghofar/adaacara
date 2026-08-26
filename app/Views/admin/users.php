<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Users - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-900 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Users', 'adminIcon' => 'users', 'adminActive' => 'users']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper(['admin_permission', 'aa_datetime']);

            $filters = $filters ?? ['q' => '', 'role' => ''];
            $userSummaries = $userSummaries ?? [];
            $roleOptions = [
                'user' => 'User',
                'creator' => 'Creator',
                'admin' => 'Admin',
                'finance_admin' => 'Finance Admin',
                'content_admin' => 'Content Admin',
                'support_admin' => 'Support Admin',
            ];
            $filterRoleOptions = ['superadmin' => 'Superadmin'] + $roleOptions;
            $canChangeRole = admin_can('admin.users.change_role');
            $canManageUsers = admin_can('admin.users.manage');
            $guestMemoryReady = ! empty($guestMemoryReady);
            $guestMemorySettings = is_array($guestMemorySettings ?? null) ? $guestMemorySettings : [];
            $currentAdminId = (int) (session()->get('userId') ?? 0);
            $formatCurrency = static fn (int $value): string => 'Rp ' . number_format($value, 0, ',', '.');
            $formatAdminDateTime = static function ($value): array {
                return [
                    'date' => aa_format_wib_date($value),
                    'time' => aa_format_wib_time($value),
                ];
            };
            $formatSubscriptionExpired = static function ($subscription): string {
                if (! is_array($subscription)) {
                    return '-';
                }

                if (((int) ($subscription['is_lifetime'] ?? 0)) === 1) {
                    return 'Selamanya';
                }

                $expiredAt = strtotime((string) ($subscription['expired_at'] ?? ''));
                if ($expiredAt !== false && $expiredAt >= strtotime('9999-01-01 00:00:00')) {
                    return 'Selamanya';
                }

                return aa_format_wib_datetime($subscription['expired_at'] ?? '');
            };
            $formatBytes = static function (int $bytes): string {
                if ($bytes >= 1073741824) {
                    return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
                }
                if ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
                }
                if ($bytes >= 1024) {
                    return number_format($bytes / 1024, 1, ',', '.') . ' KB';
                }
                return number_format($bytes, 0, ',', '.') . ' B';
            };
            $statusBadge = static function (string $status): string {
                $status = strtolower(trim($status)) ?: 'none';
                $class = match ($status) {
                    'active', 'paid', 'published', 'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                    'pending', 'waiting_approval', 'pending_payment' => 'bg-amber-50 text-amber-700 ring-amber-100',
                    'failed', 'rejected', 'expired' => 'bg-rose-50 text-rose-700 ring-rose-100',
                    default => 'bg-slate-50 text-slate-600 ring-slate-100',
                };

                return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black ring-1 ' . $class . '">' . esc($status) . '</span>';
            };
        ?>
        <?php if (! empty(session()->getFlashdata('success'))): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (! empty(session()->getFlashdata('error'))): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>
        <form class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto]" method="get" action="<?= site_url('admin/users') ?>">
            <input class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" type="search" name="q" value="<?= esc($filters['q'] ?? '', 'attr') ?>" placeholder="Cari nama atau email">
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="role">
                <option value="">Semua role</option>
                <?php foreach ($filterRoleOptions as $value => $label): ?>
                    <option value="<?= esc($value, 'attr') ?>" <?= ($filters['role'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach ?>
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-xl bg-teal-700 px-4 text-sm font-semibold text-white transition hover:bg-teal-800" type="submit">Cari</button>
                <a class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:border-teal-700 hover:text-teal-700" href="<?= site_url('admin/users') ?>">Reset</a>
            </div>
        </form>

        <section class="mb-4 grid gap-3 md:grid-cols-4">
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">User Terlihat</p>
                <p class="mt-2 text-2xl font-black"><?= esc((string) count($users)) ?></p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total Pages</p>
                <p class="mt-2 text-2xl font-black"><?= esc((string) array_sum(array_map(static fn ($item): int => (int) ($item['pages']['total'] ?? 0), $userSummaries))) ?></p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Orders Paid</p>
                <p class="mt-2 text-2xl font-black"><?= esc((string) array_sum(array_map(static fn ($item): int => (int) ($item['orders']['paid'] ?? 0), $userSummaries))) ?></p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Storage Media</p>
                <p class="mt-2 text-2xl font-black"><?= esc($formatBytes(array_sum(array_map(static fn ($item): int => (int) ($item['media']['bytes'] ?? 0), $userSummaries)))) ?></p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-3">ID</th>
                            <th class="px-5 py-3">Profil</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Paket</th>
                            <th class="px-5 py-3">Pages</th>
                            <th class="px-5 py-3">Orders</th>
                            <th class="px-5 py-3">Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($users as $user): ?>
                            <?php
                                $userId = (int) ($user['id'] ?? 0);
                                $summary = $userSummaries[$userId] ?? [];
                                $subscription = $summary['subscription'] ?? null;
                                $pages = $summary['pages'] ?? [];
                                $orders = $summary['orders'] ?? [];
                                $creator = $summary['creator'] ?? [];
                                $seller = $summary['seller'] ?? [];
                                $guestbooks = $summary['guestbooks'] ?? [];
                                $media = $summary['media'] ?? [];
                                $latestPage = is_array($pages['latest'] ?? null) ? $pages['latest'] : null;
                                $latestOrder = is_array($orders['latest'] ?? null) ? $orders['latest'] : null;
                                $userRole = strtolower(trim((string) ($user['role'] ?? 'user'))) ?: 'user';
                                $roleCanBeChanged = $canChangeRole && $userRole !== 'superadmin' && $userId !== $currentAdminId;
                                $guestMemoryEnabled = ! empty($guestMemorySettings[$userId]);
                            ?>
                            <tr>
                                <td class="px-5 py-4"><?= esc((string) $user['id']) ?></td>
                                <td class="px-5 py-4">
                                    <div class="font-black"><?= esc($user['name']) ?></div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500"><?= esc($user['email']) ?></div>
                                    <?php if (! empty($user['email_verified_at'])): ?>
                                        <div class="mt-2 text-xs font-bold text-emerald-700">Email verified</div>
                                    <?php else: ?>
                                        <div class="mt-2 text-xs font-bold text-amber-700">Email belum verified</div>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4">
                                    <?= $statusBadge($userRole) ?>
                                    <?php if ($roleCanBeChanged): ?>
                                        <form class="mt-3 flex min-w-[210px] items-center gap-2" method="post" action="<?= site_url('admin/users/' . $userId . '/role') ?>">
                                            <?= csrf_field() ?>
                                            <select class="h-9 min-w-0 flex-1 rounded-xl border border-slate-200 px-3 text-xs font-bold outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="role" aria-label="Ubah role user">
                                                <?php foreach ($roleOptions as $value => $label): ?>
                                                    <option value="<?= esc($value, 'attr') ?>" <?= $userRole === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                            <button class="h-9 rounded-xl bg-emerald-700 px-3 text-xs font-black text-white transition hover:bg-emerald-800" type="submit">Simpan</button>
                                        </form>
                                    <?php elseif ($canChangeRole && $userRole === 'superadmin'): ?>
                                        <p class="mt-2 text-xs font-bold text-slate-500">Superadmin tidak bisa diubah dari UI biasa.</p>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if (is_array($subscription)): ?>
                                        <div class="font-black"><?= esc((string) ($subscription['plan_name'] ?? 'Paket')) ?></div>
                                        <div class="mt-1 text-xs font-semibold text-slate-500"><?= esc((string) ($subscription['status'] ?? '-')) ?> sampai <?= esc($formatSubscriptionExpired($subscription)) ?></div>
                                    <?php else: ?>
                                        <span class="text-sm font-semibold text-slate-500">Tidak ada paket aktif</span>
                                    <?php endif ?>
                                    <div class="mt-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-[10px] font-black uppercase tracking-[.16em] text-emerald-700">Guest Memories</p>
                                                <p class="mt-1 text-xs font-bold text-slate-500">Aktifkan halaman /memories.</p>
                                            </div>
                                            <?php if ($guestMemoryReady && $canManageUsers): ?>
                                                <form method="post" action="<?= site_url('admin/users/' . $userId . '/guest-memories') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="is_enabled" value="<?= $guestMemoryEnabled ? '0' : '1' ?>">
                                                    <button class="inline-flex h-9 items-center rounded-xl px-3 text-xs font-black transition <?= $guestMemoryEnabled ? 'bg-emerald-700 text-white hover:bg-emerald-800' : 'border border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' ?>" type="submit">
                                                        <?= $guestMemoryEnabled ? 'Aktif' : 'Nonaktif' ?>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="inline-flex h-8 items-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-500">
                                                    <?= $guestMemoryReady ? ($guestMemoryEnabled ? 'Aktif' : 'Nonaktif') : 'Setup SQL' ?>
                                                </span>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-black"><?= esc((string) ($pages['total'] ?? 0)) ?> total</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500"><?= esc((string) ($pages['published'] ?? 0)) ?> published · <?= esc((string) ($pages['draft'] ?? 0)) ?> draft</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-black"><?= esc((string) ($orders['total'] ?? 0)) ?> order</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500"><?= esc((string) ($orders['paid'] ?? 0)) ?> paid · <?= esc((string) ($orders['pending'] ?? 0)) ?> pending</div>
                                </td>
                                <?php $createdAtParts = $formatAdminDateTime($user['created_at'] ?? ''); ?>
                                <td class="px-5 py-4">
                                    <div class="font-black text-slate-800"><?= esc($createdAtParts['date']) ?></div>
                                    <?php if ($createdAtParts['time'] !== ''): ?>
                                        <div class="mt-1 text-xs font-bold text-slate-500">Jam <?= esc($createdAtParts['time']) ?> WIB</div>
                                    <?php endif ?>
                                </td>
                            </tr>
                            <tr class="bg-slate-50/70">
                                <td class="px-5 py-4" colspan="7">
                                    <details class="group rounded-2xl border border-slate-200 bg-white p-4">
                                        <summary class="cursor-pointer select-none text-sm font-black text-emerald-700">Detail 360 User #<?= esc((string) $userId) ?></summary>
                                        <div class="mt-4 grid gap-4 lg:grid-cols-3">
                                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Profil User</p>
                                                <dl class="mt-3 grid gap-2 text-sm">
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Nama</dt><dd class="text-right font-bold"><?= esc($user['name']) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Email</dt><dd class="text-right font-bold"><?= esc($user['email']) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Role</dt><dd><?= $statusBadge((string) ($user['role'] ?? 'user')) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Updated</dt><dd class="text-right font-bold"><?= esc(aa_format_wib_datetime($user['updated_at'] ?? '')) ?></dd></div>
                                                </dl>
                                            </div>
                                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Paket / Subscription</p>
                                                <?php if (is_array($subscription)): ?>
                                                    <dl class="mt-3 grid gap-2 text-sm">
                                                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Plan</dt><dd class="text-right font-bold"><?= esc((string) ($subscription['plan_name'] ?? '-')) ?></dd></div>
                                                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Status</dt><dd><?= $statusBadge((string) ($subscription['status'] ?? '-')) ?></dd></div>
                                                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Mulai</dt><dd class="text-right font-bold"><?= esc(aa_format_wib_datetime($subscription['started_at'] ?? '')) ?></dd></div>
                                                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Expired</dt><dd class="text-right font-bold"><?= esc($formatSubscriptionExpired($subscription)) ?></dd></div>
                                                    </dl>
                                                <?php else: ?>
                                                    <p class="mt-3 text-sm font-semibold text-slate-500">Belum ada subscription aktif/tercatat.</p>
                                                <?php endif ?>
                                            </div>
                                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Pages & Guestbook</p>
                                                <dl class="mt-3 grid gap-2 text-sm">
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Total Pages</dt><dd class="font-bold"><?= esc((string) ($pages['total'] ?? 0)) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Published</dt><dd class="font-bold"><?= esc((string) ($pages['published'] ?? 0)) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Draft</dt><dd class="font-bold"><?= esc((string) ($pages['draft'] ?? 0)) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Guestbook</dt><dd class="font-bold"><?= esc((string) ($guestbooks['total'] ?? 0)) ?></dd></div>
                                                </dl>
                                                <?php if ($latestPage !== null): ?>
                                                    <p class="mt-3 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600">Terakhir: <?= esc((string) ($latestPage['title'] ?? '-')) ?> · <?= esc((string) ($latestPage['status'] ?? '-')) ?></p>
                                                <?php endif ?>
                                            </div>
                                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Orders / Payment</p>
                                                <dl class="mt-3 grid gap-2 text-sm">
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Total Order</dt><dd class="font-bold"><?= esc((string) ($orders['total'] ?? 0)) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Paid</dt><dd class="font-bold"><?= esc((string) ($orders['paid'] ?? 0)) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Pending</dt><dd class="font-bold"><?= esc((string) ($orders['pending'] ?? 0)) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Revenue Paid</dt><dd class="font-bold"><?= esc($formatCurrency((int) ($orders['amount_paid'] ?? 0))) ?></dd></div>
                                                </dl>
                                                <?php if ($latestOrder !== null): ?>
                                                    <p class="mt-3 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600">Terakhir: <?= esc((string) ($latestOrder['invoice_number'] ?? '#')) ?> · <?= esc((string) ($latestOrder['status'] ?? '-')) ?></p>
                                                <?php endif ?>
                                            </div>
                                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Creator / Seller</p>
                                                <dl class="mt-3 grid gap-2 text-sm">
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Creator</dt><dd><?= $statusBadge((string) ($creator['status'] ?? 'none')) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Display</dt><dd class="text-right font-bold"><?= esc((string) ($creator['display_name'] ?? '-')) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Seller</dt><dd><?= $statusBadge((string) ($seller['status'] ?? 'none')) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Template</dt><dd class="font-bold"><?= esc((string) ($seller['templates'] ?? 0)) ?> total</dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Wallet</dt><dd class="font-bold"><?= esc($formatCurrency((int) ($seller['wallet_balance'] ?? 0))) ?></dd></div>
                                                </dl>
                                            </div>
                                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Media / Storage</p>
                                                <dl class="mt-3 grid gap-2 text-sm">
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">File Media</dt><dd class="font-bold"><?= esc((string) ($media['total'] ?? 0)) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Storage</dt><dd class="font-bold"><?= esc($formatBytes((int) ($media['bytes'] ?? 0))) ?></dd></div>
                                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Withdraw Pending</dt><dd class="font-bold"><?= esc((string) ($seller['pending_withdraws'] ?? 0)) ?></dd></div>
                                                </dl>
                                            </div>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($users === []): ?>
                            <tr>
                                <td class="px-5 py-8 text-center font-semibold text-slate-500" colspan="7">Tidak ada user yang cocok dengan filter.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
