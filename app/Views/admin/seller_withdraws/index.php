<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Withdraw Seller - Admin</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Withdraw Seller', 'adminIcon' => 'money', 'adminActive' => 'withdraws']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper('admin_permission');
            $canApproveWithdraw = admin_can('admin.withdraw.approve');
            $canRejectWithdraw = admin_can('admin.withdraw.reject');
            $canManageWithdraw = admin_can('admin.withdraw.manage');
        ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <?php $filters = $filters ?? ['q' => '', 'status' => '']; ?>
        <form class="mb-4 grid gap-3 rounded-[24px] border border-emerald-100 bg-white/90 p-4 shadow-sm md:grid-cols-[1fr_220px_auto]" method="get" action="<?= site_url('admin/seller-withdraw-requests') ?>">
            <input class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="search" name="q" value="<?= esc($filters['q'] ?? '', 'attr') ?>" placeholder="Cari seller, email, bank, rekening">
            <select class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="status">
                <option value="">Semua status</option>
                <?php foreach (['pending', 'approved', 'paid', 'rejected'] as $item): ?>
                    <option value="<?= esc($item, 'attr') ?>" <?= ($filters['status'] ?? '') === $item ? 'selected' : '' ?>><?= esc(ucfirst($item)) ?></option>
                <?php endforeach ?>
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white" type="submit">Cari</button>
                <a class="inline-flex h-11 items-center rounded-2xl border border-emerald-100 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('admin/seller-withdraw-requests') ?>">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-[28px] border border-emerald-100 bg-white/85 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] text-left text-sm">
                    <thead class="bg-emerald-50/70 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr><th class="px-5 py-4">Seller</th><th>Nominal</th><th>Bank</th><th>Status</th><th>Requested</th><th>Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($withdraws as $row): ?>
                            <tr class="align-middle hover:bg-emerald-50/60">
                                <td class="px-5 py-4"><p class="font-black"><?= esc($row['user_name'] ?? '-') ?></p><p class="text-xs text-slate-500"><?= esc($row['user_email'] ?? '-') ?></p></td>
                                <td>Rp <?= number_format((int) ($row['amount'] ?? 0), 0, ',', '.') ?></td>
                                <td><?= esc($row['bank_name'] ?? '-') ?><br><span class="text-xs text-slate-500"><?= esc($row['account_number'] ?? '-') ?> · <?= esc($row['account_holder_name'] ?? '-') ?></span></td>
                                <td><?= esc($row['status'] ?? '-') ?></td>
                                <td><?= esc($row['requested_at'] ?? $row['created_at'] ?? '-') ?></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <?php if ($canApproveWithdraw): ?>
                                        <form method="post" action="<?= site_url('admin/seller-withdraw-requests/' . $row['id'] . '/approve') ?>">
                                            <?= csrf_field() ?>
                                            <button class="rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-bold text-emerald-700" type="submit">Approve</button>
                                        </form>
                                        <?php endif ?>
                                        <?php if ($canManageWithdraw): ?>
                                        <form method="post" action="<?= site_url('admin/seller-withdraw-requests/' . $row['id'] . '/mark-paid') ?>">
                                            <?= csrf_field() ?>
                                            <button class="rounded-xl bg-emerald-700 px-3 py-2 text-xs font-black text-white" type="submit">Mark Paid</button>
                                        </form>
                                        <?php endif ?>
                                        <?php if ($canRejectWithdraw): ?>
                                        <form class="flex gap-1" method="post" action="<?= site_url('admin/seller-withdraw-requests/' . $row['id'] . '/reject') ?>">
                                            <?= csrf_field() ?>
                                            <input class="w-36 rounded-xl border border-slate-200 px-2 py-2 text-xs" type="text" name="admin_note" placeholder="Alasan" required>
                                            <button class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-black text-white" type="submit">Reject</button>
                                        </form>
                                        <?php endif ?>
                                        <?php if (! $canApproveWithdraw && ! $canRejectWithdraw && ! $canManageWithdraw): ?>
                                            <span class="text-xs font-bold text-slate-500">Read-only</span>
                                        <?php endif ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($withdraws === []): ?><tr><td class="px-5 py-8 text-slate-500" colspan="6">Belum ada request withdraw.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
