<!doctype html>
<?php
    $availableBalance = (int) ($balance['available'] ?? 0);
    $pendingBalance = (int) ($balance['pending'] ?? 0);
    $withdrawnBalance = (int) ($balance['withdrawn'] ?? 0);
    $royaltyReady = (bool) ($royaltyReady ?? false);
    $royaltySummary = is_array($royaltySummary ?? null) ? $royaltySummary : [];
    $royalties = array_values($royalties ?? []);
    $royaltyPending = (int) ($royaltySummary['pending'] ?? 0);
    $royaltyAvailable = (int) ($royaltySummary['available'] ?? 0);
    $royaltyTotal = (int) ($royaltySummary['earnings_total'] ?? 0);
    $royaltyReversed = (int) ($royaltySummary['reversed'] ?? 0);
    $royaltyUses = (int) ($royaltySummary['uses'] ?? 0);
    $royaltyPublished = (int) ($royaltySummary['published'] ?? 0);
    $minimumWithdraw = (int) ($limits['minimum_withdraw_amount'] ?? 0);
    $withdrawFee = 2500;
    $chartValues = [];

    foreach (array_slice(array_reverse($ledger ?? []), -12) as $row) {
        $amount = (int) ($row['amount'] ?? 0);
        if ((string) ($row['direction'] ?? '') === 'credit') {
            $chartValues[] = $amount;
        }
    }

    if ($chartValues === []) {
        $chartValues = [18, 28, 22, 38, 31, 51, 42, 60, 47, 66, 58, 76];
    }

    $chartMax = max(1, max($chartValues));
    $statusClass = static function (string $status): string {
        return match (strtolower($status)) {
            'paid', 'approved', 'withdrawn', 'success', 'cleared', 'available' => 'bg-emerald-100 text-emerald-800',
            'pending', 'processed', 'processing' => 'bg-amber-100 text-amber-800',
            'rejected', 'cancelled', 'failed' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
        };
    };
    $formatDate = static function (?string $value): string {
        if ($value === null || trim($value) === '') {
            return '-';
        }

        $time = strtotime($value);
        return $time === false ? $value : date('d M Y', $time);
    };
?>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Earnings Creator - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-[#f3f0e6] text-slate-950 antialiased">
    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6">
        <div class="mb-6 flex min-w-0 flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-emerald-800">
                    <a class="no-underline transition hover:text-emerald-950" href="<?= site_url('creator/dashboard') ?>">Creator Studio</a>
                    <span aria-hidden="true">&gt;</span>
                    <span>Earnings</span>
                </p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-emerald-950 sm:text-4xl">Earnings & Withdraw</h1>
                <p class="mt-2 text-base font-semibold text-slate-600">Tarik komisi kapan saja. Dana cair setelah admin memproses request withdraw kamu.</p>
            </div>
            <div class="flex min-w-0 flex-wrap gap-2">
                <?= view('components/public_theme_toggle') ?>
                <button class="rounded-2xl border border-emerald-900/10 bg-white/75 px-4 py-2 text-sm font-black text-slate-700 shadow-sm transition hover:border-emerald-500" type="button" data-aa-export-ledger>Export CSV</button>
                <button class="rounded-2xl border border-emerald-900/10 bg-white/75 px-4 py-2 text-sm font-black text-slate-700 shadow-sm transition hover:border-emerald-500" type="button" data-aa-focus-bank>Kelola Rekening</button>
                <a class="rounded-2xl bg-emerald-950 px-4 py-2 text-sm font-black text-white shadow-sm transition hover:bg-emerald-900" href="<?= site_url('creator/dashboard') ?>">Overview</a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                <?php foreach ((array) session()->getFlashdata('errors') as $message): ?>
                    <p><?= esc($message) ?></p>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <section class="grid min-w-0 gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(280px,400px)]">
            <article class="min-w-0 overflow-hidden rounded-[30px] bg-gradient-to-br from-emerald-950 via-emerald-900 to-lime-800 p-5 text-white shadow-[0_24px_70px_rgba(6,78,59,.2)] sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.26em] text-emerald-100/75">Saldo Tersedia</p>
                        <p class="mt-4 break-words text-4xl font-black tracking-tight [overflow-wrap:anywhere] sm:text-6xl">Rp <?= number_format($availableBalance, 0, ',', '.') ?></p>
                        <span class="mt-3 inline-flex rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-black text-emerald-50 ring-1 ring-white/10">Minimum Rp <?= number_format($minimumWithdraw, 0, ',', '.') ?></span>
                    </div>
                    <span class="rounded-full bg-white/12 px-3 py-1 text-xs font-black uppercase tracking-wide text-emerald-50 ring-1 ring-white/10">Live</span>
                </div>

                <div class="mt-12 flex h-24 items-end gap-2">
                    <?php foreach ($chartValues as $value): ?>
                        <?php $height = max(18, min(100, (int) round(((int) $value / $chartMax) * 100))); ?>
                        <span class="flex-1 rounded-t-lg bg-gradient-to-t from-emerald-100/35 to-amber-200/90 shadow-sm" style="height: <?= $height ?>%"></span>
                    <?php endforeach ?>
                </div>

                <div class="mt-7 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-3">
                        <button class="inline-flex h-12 items-center justify-center rounded-2xl bg-white px-5 text-sm font-black text-emerald-950 shadow-sm transition hover:bg-emerald-50" type="button" data-aa-focus-withdraw>Tarik Saldo</button>
                        <button class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/25 bg-white/10 px-5 text-sm font-black text-white transition hover:bg-white/15" type="button" data-aa-focus-bank>Atur Rekening</button>
                    </div>
                    <p class="text-sm font-bold text-emerald-50/75">Aman & terenkripsi</p>
                </div>
            </article>

            <aside class="grid min-w-0 gap-5">
                <article class="min-w-0 rounded-[28px] border border-emerald-900/10 bg-white/80 p-6 shadow-sm ring-1 ring-white/60">
                    <div class="flex items-start justify-between gap-4">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-100 text-lg font-black text-amber-700">P</span>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Saldo Pending</p>
                    </div>
                    <p class="mt-6 break-words text-3xl font-black [overflow-wrap:anywhere]">Rp <?= number_format($pendingBalance, 0, ',', '.') ?></p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Menunggu status komisi tersedia.</p>
                </article>
                <article class="min-w-0 rounded-[28px] border border-emerald-900/10 bg-white/80 p-6 shadow-sm ring-1 ring-white/60">
                    <div class="flex items-start justify-between gap-4">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-100 text-lg font-black text-emerald-700">W</span>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Total Ditarik</p>
                    </div>
                    <p class="mt-6 break-words text-3xl font-black [overflow-wrap:anywhere]">Rp <?= number_format($withdrawnBalance, 0, ',', '.') ?></p>
                    <p class="mt-1 text-sm font-semibold text-slate-500"><?= count($withdraws ?? []) ?> request withdraw tercatat.</p>
                </article>
            </aside>
        </section>

        <section class="mt-6 min-w-0 rounded-[30px] border border-violet-100 bg-white/86 p-5 shadow-sm ring-1 ring-white/70 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-700">Creator Royalty v1</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight">Royalty Template 90%</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 text-slate-500">Panel QA read-only untuk model baru: creator mendapat 90% dari nilai/lisensi template, bukan dari total membership user.</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-black <?= $royaltyReady ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>"><?= $royaltyReady ? 'SQL siap' : 'SQL belum diterapkan' ?></span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                <?php foreach ([
                    ['label' => 'Penggunaan', 'value' => number_format($royaltyUses, 0, ',', '.'), 'caption' => 'template used'],
                    ['label' => 'Publish', 'value' => number_format($royaltyPublished, 0, ',', '.'), 'caption' => 'qualified publish'],
                    ['label' => 'Total Royalty', 'value' => 'Rp ' . number_format($royaltyTotal, 0, ',', '.'), 'caption' => 'available total'],
                    ['label' => 'Pending', 'value' => 'Rp ' . number_format($royaltyPending, 0, ',', '.'), 'caption' => 'menunggu validasi'],
                    ['label' => 'Tersedia', 'value' => 'Rp ' . number_format($royaltyAvailable, 0, ',', '.'), 'caption' => 'siap dihitung'],
                    ['label' => 'Reversed', 'value' => 'Rp ' . number_format($royaltyReversed, 0, ',', '.'), 'caption' => 'dibalik/cancel'],
                ] as $item): ?>
                    <article class="min-w-0 rounded-3xl border border-slate-100 bg-slate-50/70 p-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500"><?= esc($item['label']) ?></p>
                        <p class="mt-2 break-words text-xl font-black [overflow-wrap:anywhere]"><?= esc($item['value']) ?></p>
                        <p class="mt-1 text-xs font-bold text-slate-500"><?= esc($item['caption']) ?></p>
                    </article>
                <?php endforeach ?>
            </div>

            <div class="mt-5 max-w-full overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="text-xs font-black uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="py-3">Template</th>
                            <th>Project</th>
                            <th>Buyer</th>
                            <th>Lisensi</th>
                            <th>Creator</th>
                            <th>Platform</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($royalties as $royalty): ?>
                            <?php $royaltyStatus = (string) ($royalty['status'] ?? '-'); ?>
                            <tr>
                                <td class="py-4">
                                    <p class="font-black"><?= esc((string) ($royalty['template_name'] ?? ('Template #' . ($royalty['template_id'] ?? '-')))) ?></p>
                                    <p class="text-xs font-semibold text-slate-500">ID <?= esc((string) ($royalty['template_id'] ?? '-')) ?></p>
                                </td>
                                <td>
                                    <p class="font-bold"><?= esc((string) ($royalty['invitation_title'] ?? '-')) ?></p>
                                    <p class="text-xs text-slate-500"><?= esc((string) ($royalty['invitation_slug'] ?? ('#' . ($royalty['invitation_id'] ?? '-')))) ?></p>
                                </td>
                                <td>
                                    <p class="font-bold"><?= esc((string) ($royalty['buyer_name'] ?? '-')) ?></p>
                                    <p class="text-xs text-slate-500"><?= esc((string) ($royalty['buyer_email'] ?? '')) ?></p>
                                </td>
                                <td class="font-black">Rp <?= number_format((int) ($royalty['license_value'] ?? 0), 0, ',', '.') ?></td>
                                <td class="font-black text-emerald-700">Rp <?= number_format((int) ($royalty['creator_amount'] ?? 0), 0, ',', '.') ?></td>
                                <td>Rp <?= number_format((int) ($royalty['platform_amount'] ?? 0), 0, ',', '.') ?></td>
                                <td><span class="rounded-full px-3 py-1 text-xs font-black <?= esc($statusClass($royaltyStatus), 'attr') ?>"><?= esc($royaltyStatus) ?></span></td>
                                <td class="font-semibold text-slate-500"><?= esc($formatDate($royalty['created_at'] ?? null)) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($royalties === []): ?>
                            <tr><td class="py-8 text-center text-slate-500" colspan="8"><?= $royaltyReady ? 'Belum ada royalty v1.' : 'Jalankan SQL royalty v1 untuk mulai QA.' ?></td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-6 grid min-w-0 gap-6 xl:grid-cols-[minmax(360px,480px)_minmax(0,1fr)]">
            <form id="aaWithdrawForm" class="min-w-0 rounded-[30px] border border-emerald-900/10 bg-white/82 p-5 shadow-sm ring-1 ring-white/60 sm:p-6" method="post" action="<?= site_url('creator/withdraw-requests') ?>">
                <?= csrf_field() ?>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-2xl font-black tracking-tight">Tarik Saldo</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Minimum Rp <?= number_format($minimumWithdraw, 0, ',', '.') ?>.</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">Realtime</span>
                </div>

                <div class="mt-6 grid gap-5">
                    <label class="grid min-w-0 gap-2">
                        <span class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Nominal</span>
                        <div class="flex min-w-0 items-center rounded-[22px] border border-slate-200 bg-white px-3 py-3 shadow-inner sm:px-4">
                            <span class="text-xl font-black text-slate-600 sm:text-2xl">Rp</span>
                            <input id="aaWithdrawAmount" class="min-w-0 flex-1 border-0 bg-transparent px-3 text-2xl font-black outline-none" type="number" name="amount" min="<?= max(1, $minimumWithdraw) ?>" max="<?= max(0, $availableBalance) ?>" placeholder="0" value="<?= esc((string) old('amount'), 'attr') ?>" required>
                            <button class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700" type="button" data-aa-max-amount="<?= esc((string) $availableBalance, 'attr') ?>">MAX</button>
                        </div>
                    </label>

                    <div class="flex flex-wrap gap-2">
                        <?php foreach ([100000, 250000, 500000, 1000000] as $quickAmount): ?>
                            <button class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:border-emerald-500 hover:text-emerald-800" type="button" data-aa-add-amount="<?= $quickAmount ?>">+Rp <?= number_format($quickAmount, 0, ',', '.') ?></button>
                        <?php endforeach ?>
                    </div>

                    <div id="aaBankSection" class="grid gap-3">
                        <span class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Rekening Tujuan</span>
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
                            <?php foreach (['BCA', 'MDR', 'BNI', 'BRI', 'GP', 'OVO'] as $bank): ?>
                                <button class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-center text-xs font-black text-slate-600 transition hover:border-emerald-700 hover:bg-emerald-50 hover:text-emerald-900" type="button" data-aa-bank="<?= esc($bank, 'attr') ?>"><?= esc($bank) ?></button>
                            <?php endforeach ?>
                        </div>
                        <input id="aaBankName" class="min-w-0 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none transition focus:border-emerald-600" type="text" name="bank_name" placeholder="Nama bank/e-wallet" value="<?= esc((string) old('bank_name'), 'attr') ?>" required>
                        <div class="grid min-w-0 gap-3 sm:grid-cols-2">
                            <input class="min-w-0 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none transition focus:border-emerald-600" type="text" name="account_number" placeholder="Nomor rekening" value="<?= esc((string) old('account_number'), 'attr') ?>" required>
                            <input class="min-w-0 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none transition focus:border-emerald-600" type="text" name="account_holder_name" placeholder="Nama pemilik rekening" value="<?= esc((string) old('account_holder_name'), 'attr') ?>" required>
                        </div>
                    </div>

                    <label class="grid min-w-0 gap-2">
                        <span class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Konfirmasi Password</span>
                        <input class="min-w-0 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none transition focus:border-emerald-600" type="password" name="account_password" placeholder="Password login kamu" autocomplete="current-password" required>
                        <span class="text-[11px] font-bold leading-relaxed text-slate-500">Password hanya untuk verifikasi keamanan dan tidak disimpan.</span>
                    </label>

                    <div class="rounded-[22px] border border-dashed border-slate-200 bg-slate-50/70 p-4 text-sm font-bold text-slate-600">
                        <div class="flex justify-between gap-3"><span>Nominal</span><strong id="aaSummaryAmount" class="break-words text-right [overflow-wrap:anywhere]">Rp 0</strong></div>
                        <div class="mt-2 flex justify-between gap-3"><span>Biaya admin estimasi</span><strong class="break-words text-right [overflow-wrap:anywhere]">- Rp <?= number_format($withdrawFee, 0, ',', '.') ?></strong></div>
                        <div class="mt-3 flex justify-between gap-3 border-t border-slate-200 pt-3 text-emerald-900"><span>Diterima</span><strong id="aaSummaryReceive" class="break-words text-right [overflow-wrap:anywhere]">Rp 0</strong></div>
                    </div>

                    <button class="rounded-[22px] bg-emerald-950 px-4 py-4 text-sm font-black text-white transition hover:bg-emerald-900 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500" type="submit">Ajukan Withdraw</button>
                </div>
            </form>

            <article class="min-w-0 rounded-[30px] border border-emerald-900/10 bg-white/82 p-5 shadow-sm ring-1 ring-white/60 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-2xl font-black tracking-tight">Riwayat Withdraw</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Semua penarikan dana kamu.</p>
                    </div>
                    <label class="flex h-11 w-full min-w-0 items-center rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-500 sm:w-auto sm:min-w-[220px]">
                        <span class="mr-2">Cari</span>
                        <input class="min-w-0 flex-1 border-0 bg-transparent outline-none" type="search" placeholder="ref / bank" data-aa-withdraw-search>
                    </label>
                </div>

                <div class="mt-5 max-w-full overflow-x-auto">
                    <table class="w-full min-w-[660px] text-left text-sm">
                        <thead class="text-xs font-black uppercase tracking-wide text-slate-500">
                            <tr><th class="py-3">Tanggal</th><th>Nominal</th><th>Bank</th><th>Ref</th><th>Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200" data-aa-withdraw-rows>
                            <?php foreach ($withdraws as $row): ?>
                                <?php $withdrawStatus = (string) ($row['status'] ?? '-'); ?>
                                <tr data-aa-search-row="<?= esc(strtolower(($row['bank_name'] ?? '') . ' wd-' . ($row['id'] ?? '') . ' ' . $withdrawStatus), 'attr') ?>">
                                    <td class="py-4 font-semibold text-slate-600"><?= esc($formatDate($row['created_at'] ?? $row['requested_at'] ?? null)) ?></td>
                                    <td class="font-black">Rp <?= number_format((int) ($row['amount'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="font-semibold text-slate-700"><?= esc($row['bank_name'] ?? '-') ?></td>
                                    <td><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-500">WD-<?= esc((string) ($row['id'] ?? '')) ?></span></td>
                                    <td><span class="rounded-full px-3 py-1 text-xs font-black <?= esc($statusClass($withdrawStatus), 'attr') ?>"><?= esc($withdrawStatus) ?></span></td>
                                </tr>
                            <?php endforeach ?>
                            <?php if ($withdraws === []): ?><tr><td class="py-8 text-center text-slate-500" colspan="5">Belum ada withdraw.</td></tr><?php endif ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="mt-6 min-w-0 rounded-[30px] border border-emerald-900/10 bg-white/82 p-5 shadow-sm ring-1 ring-white/60 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-2xl font-black tracking-tight">Ledger Komisi</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Detail tiap transaksi yang masuk dan keluar dari saldo kamu.</p>
                </div>
                <div class="max-w-full overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50 p-1 text-xs font-black text-slate-600">
                    <span class="inline-flex rounded-xl bg-white px-3 py-2 shadow-sm">Semua</span>
                    <span class="inline-flex px-3 py-2">Masuk</span>
                    <span class="inline-flex px-3 py-2">Keluar</span>
                </div>
            </div>

            <div class="mt-5 divide-y divide-slate-200" data-aa-ledger-export>
                <?php foreach ($ledger as $row): ?>
                    <?php
                        $direction = (string) ($row['direction'] ?? '');
                        $isCredit = $direction === 'credit';
                        $ledgerStatus = (string) ($row['status'] ?? '-');
                    ?>
                    <article class="grid min-w-0 gap-3 py-4 sm:grid-cols-[44px_minmax(0,1fr)_auto] sm:items-center">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl <?= $isCredit ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?> text-lg font-black"><?= $isCredit ? '+' : '-' ?></span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="break-words font-black [overflow-wrap:anywhere]"><?= esc(ucwords(str_replace('_', ' ', (string) ($row['type'] ?? 'Komisi')))) ?></p>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-black <?= esc($statusClass($ledgerStatus), 'attr') ?>"><?= esc($ledgerStatus) ?></span>
                            </div>
                            <p class="mt-1 break-words text-sm font-semibold text-slate-500 [overflow-wrap:anywhere]"><?= esc($row['note'] ?? '-') ?></p>
                        </div>
                        <div class="min-w-0 text-left sm:text-right">
                            <p class="text-sm font-semibold text-slate-500"><?= esc($formatDate($row['created_at'] ?? null)) ?></p>
                            <p class="mt-1 break-words text-lg font-black [overflow-wrap:anywhere] <?= $isCredit ? 'text-emerald-700' : 'text-slate-900' ?>"><?= $isCredit ? '+' : '-' ?> Rp <?= number_format((int) ($row['amount'] ?? 0), 0, ',', '.') ?></p>
                        </div>
                    </article>
                <?php endforeach ?>
                <?php if ($ledger === []): ?>
                    <p class="py-8 text-center text-slate-500">Belum ada ledger.</p>
                <?php endif ?>
            </div>
        </section>

        <section class="mt-6 grid min-w-0 gap-4 rounded-[30px] border border-emerald-900/10 bg-emerald-50/60 p-5 sm:grid-cols-3">
            <div class="flex min-w-0 gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-900 text-sm font-black text-white">1</span>
                <p class="min-w-0 text-sm font-semibold text-slate-600"><strong class="block text-slate-950">Kapan saldo cair?</strong>Withdraw diproses admin setelah request masuk dan data rekening valid.</p>
            </div>
            <div class="flex min-w-0 gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-900 text-sm font-black text-white">2</span>
                <p class="min-w-0 text-sm font-semibold text-slate-600"><strong class="block text-slate-950">Aman & terverifikasi</strong>Semua request butuh konfirmasi password akun.</p>
            </div>
            <div class="flex min-w-0 gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-900 text-sm font-black text-white">3</span>
                <p class="min-w-0 text-sm font-semibold text-slate-600"><strong class="block text-slate-950">Naikkan komisi</strong>Tambah template berkualitas agar peluang dipakai semakin besar.</p>
            </div>
        </section>
    </main>

    <script>
        (function () {
            var amountInput = document.getElementById('aaWithdrawAmount');
            var bankInput = document.getElementById('aaBankName');
            var summaryAmount = document.getElementById('aaSummaryAmount');
            var summaryReceive = document.getElementById('aaSummaryReceive');
            var withdrawFee = <?= json_encode($withdrawFee) ?>;

            function formatRupiah(value) {
                return 'Rp ' + Math.max(0, value || 0).toLocaleString('id-ID');
            }

            function updateSummary() {
                if (!amountInput) {
                    return;
                }
                var amount = parseInt(amountInput.value || '0', 10) || 0;
                if (summaryAmount) {
                    summaryAmount.textContent = formatRupiah(amount);
                }
                if (summaryReceive) {
                    summaryReceive.textContent = formatRupiah(Math.max(0, amount - withdrawFee));
                }
            }

            document.querySelectorAll('[data-aa-add-amount]').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!amountInput) {
                        return;
                    }
                    var current = parseInt(amountInput.value || '0', 10) || 0;
                    var add = parseInt(button.getAttribute('data-aa-add-amount') || '0', 10) || 0;
                    amountInput.value = current + add;
                    updateSummary();
                    amountInput.focus();
                });
            });

            document.querySelectorAll('[data-aa-max-amount]').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!amountInput) {
                        return;
                    }
                    amountInput.value = parseInt(button.getAttribute('data-aa-max-amount') || '0', 10) || 0;
                    updateSummary();
                    amountInput.focus();
                });
            });

            document.querySelectorAll('[data-aa-bank]').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (bankInput) {
                        bankInput.value = button.getAttribute('data-aa-bank') || '';
                        bankInput.focus();
                    }
                    document.querySelectorAll('[data-aa-bank]').forEach(function (item) {
                        item.classList.remove('border-emerald-700', 'bg-emerald-50', 'text-emerald-900');
                    });
                    button.classList.add('border-emerald-700', 'bg-emerald-50', 'text-emerald-900');
                });
            });

            document.querySelectorAll('[data-aa-focus-withdraw]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var form = document.getElementById('aaWithdrawForm');
                    if (form) {
                        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    if (amountInput) {
                        amountInput.focus();
                    }
                });
            });

            document.querySelectorAll('[data-aa-focus-bank]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var section = document.getElementById('aaBankSection');
                    if (section) {
                        section.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    if (bankInput) {
                        bankInput.focus();
                    }
                });
            });

            var searchInput = document.querySelector('[data-aa-withdraw-search]');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    var keyword = searchInput.value.trim().toLowerCase();
                    document.querySelectorAll('[data-aa-search-row]').forEach(function (row) {
                        row.hidden = keyword !== '' && (row.getAttribute('data-aa-search-row') || '').indexOf(keyword) === -1;
                    });
                });
            }

            var exportButton = document.querySelector('[data-aa-export-ledger]');
            if (exportButton) {
                exportButton.addEventListener('click', function () {
                    var rows = [['Tanggal', 'Tipe', 'Arah', 'Status', 'Nominal', 'Catatan']];
                    <?php foreach ($ledger as $row): ?>
                    rows.push([
                        <?= json_encode($formatDate($row['created_at'] ?? null)) ?>,
                        <?= json_encode((string) ($row['type'] ?? '-')) ?>,
                        <?= json_encode((string) ($row['direction'] ?? '-')) ?>,
                        <?= json_encode((string) ($row['status'] ?? '-')) ?>,
                        <?= json_encode((string) ((int) ($row['amount'] ?? 0))) ?>,
                        <?= json_encode((string) ($row['note'] ?? '-')) ?>
                    ]);
                    <?php endforeach ?>
                    var csv = rows.map(function (row) {
                        return row.map(function (cell) {
                            return '"' + String(cell).replace(/"/g, '""') + '"';
                        }).join(',');
                    }).join('\n');
                    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    var url = URL.createObjectURL(blob);
                    var link = document.createElement('a');
                    link.href = url;
                    link.download = 'adaacara-creator-ledger.csv';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(url);
                });
            }

            if (amountInput) {
                amountInput.addEventListener('input', updateSummary);
            }
            updateSummary();
        })();
    </script>
</body>
</html>
