<!doctype html>
<?php
    $royaltyReady = (bool) ($royaltyReady ?? false);
    $royalties = array_values($royalties ?? []);
    $events = array_values($events ?? []);
    $summary = is_array($summary ?? null) ? $summary : [];
    $filters = is_array($filters ?? null) ? $filters : [];
    $statusClass = static function (string $status): string {
        return match (strtolower($status)) {
            'available', 'paid', 'confirmed' => 'bg-emerald-100 text-emerald-800',
            'pending' => 'bg-amber-100 text-amber-800',
            'reversed', 'cancelled', 'failed' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
        };
    };
    $formatDate = static function (?string $value): string {
        if ($value === null || trim($value) === '') {
            return '-';
        }

        $time = strtotime($value);
        return $time === false ? $value : date('d M Y H:i', $time);
    };
?>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Creator Royalty - Admin</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Creator Royalty', 'adminIcon' => 'money', 'adminActive' => 'creatorRoyalties']) ?>
    <main class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6">
        <section class="mb-5 rounded-[28px] border border-violet-100 bg-white/90 p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-700">QA Read-only</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight">Creator Royalty v1</h1>
                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-slate-600">Audit model royalty baru: creator mendapat 90% dari nilai/lisensi template. Halaman ini tidak mengubah saldo, withdraw, order, atau status royalty.</p>
                </div>
                <span class="rounded-full px-4 py-2 text-xs font-black <?= $royaltyReady ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>"><?= $royaltyReady ? 'SQL royalty siap' : 'SQL royalty belum diterapkan' ?></span>
            </div>
        </section>

        <section class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-7">
            <?php foreach ([
                ['label' => 'Rows', 'value' => number_format((int) ($summary['total'] ?? 0), 0, ',', '.')],
                ['label' => 'Pending', 'value' => number_format((int) ($summary['pending'] ?? 0), 0, ',', '.')],
                ['label' => 'Available', 'value' => number_format((int) ($summary['available'] ?? 0), 0, ',', '.')],
                ['label' => 'Reversed', 'value' => number_format((int) ($summary['reversed'] ?? 0), 0, ',', '.')],
                ['label' => 'Nilai Lisensi', 'value' => 'Rp ' . number_format((int) ($summary['license_value'] ?? 0), 0, ',', '.')],
                ['label' => 'Creator', 'value' => 'Rp ' . number_format((int) ($summary['creator_amount'] ?? 0), 0, ',', '.')],
                ['label' => 'Platform', 'value' => 'Rp ' . number_format((int) ($summary['platform_amount'] ?? 0), 0, ',', '.')],
            ] as $card): ?>
                <article class="rounded-3xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                    <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500"><?= esc($card['label']) ?></p>
                    <p class="mt-2 break-words text-xl font-black [overflow-wrap:anywhere]"><?= esc($card['value']) ?></p>
                </article>
            <?php endforeach ?>
        </section>

        <form class="mb-5 grid gap-3 rounded-[24px] border border-violet-100 bg-white/90 p-4 shadow-sm lg:grid-cols-[minmax(0,1fr)_180px_220px_auto]" method="get" action="<?= site_url('admin/creator-royalties') ?>">
            <input class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-violet-600 focus:ring-4 focus:ring-violet-100" type="search" name="q" value="<?= esc((string) ($filters['q'] ?? ''), 'attr') ?>" placeholder="Cari creator, buyer, template, invoice, slug">
            <select class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-violet-600 focus:ring-4 focus:ring-violet-100" name="status">
                <option value="">Semua royalty</option>
                <?php foreach (['pending', 'available', 'reversed', 'cancelled'] as $status): ?>
                    <option value="<?= esc($status, 'attr') ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= esc(ucfirst($status)) ?></option>
                <?php endforeach ?>
            </select>
            <select class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-violet-600 focus:ring-4 focus:ring-violet-100" name="event_type">
                <option value="">Semua event</option>
                <?php foreach (['template_used', 'template_published', 'royalty_created', 'royalty_confirmed', 'royalty_reversed', 'royalty_cancelled'] as $eventType): ?>
                    <option value="<?= esc($eventType, 'attr') ?>" <?= ($filters['event_type'] ?? '') === $eventType ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $eventType)) ?></option>
                <?php endforeach ?>
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-2xl bg-violet-700 px-4 text-sm font-black text-white" type="submit">Filter</button>
                <a class="inline-flex h-11 items-center rounded-2xl border border-violet-100 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-violet-600 hover:text-violet-700" href="<?= site_url('admin/creator-royalties') ?>">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-[28px] border border-violet-100 bg-white/90 shadow-sm">
            <div class="border-b border-violet-100 px-5 py-4">
                <h2 class="text-lg font-black">Royalty Rows</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">Data komisi v1 yang dibuat dari qualified publish.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1320px] text-left text-sm">
                    <thead class="bg-violet-50/80 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr><th class="px-5 py-4">Creator</th><th>Buyer</th><th>Template</th><th>Project</th><th>Order</th><th>Lisensi</th><th>Creator</th><th>Platform</th><th>Status</th><th>Tanggal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($royalties as $row): ?>
                            <?php $status = (string) ($row['status'] ?? '-'); ?>
                            <tr class="align-top hover:bg-violet-50/50">
                                <td class="px-5 py-4"><strong><?= esc((string) ($row['creator_name'] ?? '-')) ?></strong><br><span class="text-xs text-slate-500"><?= esc((string) ($row['creator_email'] ?? '')) ?></span></td>
                                <td><strong><?= esc((string) ($row['buyer_name'] ?? '-')) ?></strong><br><span class="text-xs text-slate-500"><?= esc((string) ($row['buyer_email'] ?? '')) ?></span></td>
                                <td><strong><?= esc((string) ($row['template_name'] ?? '-')) ?></strong><br><span class="text-xs text-slate-500">#<?= esc((string) ($row['template_id'] ?? '-')) ?></span></td>
                                <td><strong><?= esc((string) ($row['invitation_title'] ?? '-')) ?></strong><br><span class="text-xs text-slate-500"><?= esc((string) ($row['invitation_slug'] ?? ('#' . ($row['invitation_id'] ?? '-')))) ?></span></td>
                                <td><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-600"><?= esc((string) ($row['invoice_number'] ?? ('#' . ($row['order_id'] ?? '-')))) ?></span><br><span class="mt-1 inline-block text-xs text-slate-500"><?= esc((string) ($row['order_status'] ?? '')) ?></span></td>
                                <td class="font-black">Rp <?= number_format((int) ($row['license_value'] ?? 0), 0, ',', '.') ?></td>
                                <td class="font-black text-emerald-700">Rp <?= number_format((int) ($row['creator_amount'] ?? 0), 0, ',', '.') ?></td>
                                <td>Rp <?= number_format((int) ($row['platform_amount'] ?? 0), 0, ',', '.') ?></td>
                                <td><span class="rounded-full px-3 py-1 text-xs font-black <?= esc($statusClass($status), 'attr') ?>"><?= esc($status) ?></span></td>
                                <td class="font-semibold text-slate-500"><?= esc($formatDate($row['created_at'] ?? null)) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($royalties === []): ?><tr><td class="px-5 py-8 text-center text-slate-500" colspan="10"><?= $royaltyReady ? 'Belum ada royalty sesuai filter.' : 'SQL royalty belum diterapkan.' ?></td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-[28px] border border-violet-100 bg-white/90 shadow-sm">
            <div class="border-b border-violet-100 px-5 py-4">
                <h2 class="text-lg font-black">Event Log</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">Jejak event royalty untuk membantu QA.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr><th class="px-5 py-4">Event</th><th>Template</th><th>Project</th><th>Creator</th><th>Buyer</th><th>Order</th><th>Metadata</th><th>Tanggal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($events as $event): ?>
                            <tr class="align-top hover:bg-slate-50">
                                <td class="px-5 py-4"><span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-black text-violet-800"><?= esc((string) ($event['event_type'] ?? '-')) ?></span></td>
                                <td><?= esc((string) ($event['template_name'] ?? ('#' . ($event['template_id'] ?? '-')))) ?></td>
                                <td><?= esc((string) ($event['invitation_title'] ?? ($event['invitation_slug'] ?? ('#' . ($event['invitation_id'] ?? '-'))))) ?></td>
                                <td><?= esc((string) ($event['creator_name'] ?? '-')) ?></td>
                                <td><?= esc((string) ($event['buyer_name'] ?? '-')) ?></td>
                                <td><?= esc((string) ($event['invoice_number'] ?? ('#' . ($event['order_id'] ?? '-')))) ?></td>
                                <td><code class="block max-w-[280px] truncate rounded-xl bg-slate-100 px-2 py-1 text-xs"><?= esc((string) ($event['metadata'] ?? '')) ?></code></td>
                                <td class="font-semibold text-slate-500"><?= esc($formatDate($event['created_at'] ?? null)) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($events === []): ?><tr><td class="px-5 py-8 text-center text-slate-500" colspan="8"><?= $royaltyReady ? 'Belum ada event sesuai filter.' : 'SQL royalty belum diterapkan.' ?></td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
