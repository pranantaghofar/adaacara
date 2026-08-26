<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Template Creator - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-slate-50 text-slate-950 antialiased">
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a class="text-sm font-bold text-amber-700" href="<?= site_url('creator/templates') ?>">← Kembali ke My Templates</a>
            <?= view('components/public_theme_toggle') ?>
        </div>
        <section class="mt-5 grid gap-6 lg:grid-cols-[280px_1fr]">
            <img class="w-full rounded-[28px] border border-amber-100 bg-white object-cover shadow-sm" src="<?= base_url($template['thumbnail'] ?: 'assets/img/logo2.png') ?>" alt="" loading="lazy">
            <div class="rounded-[28px] border border-amber-100 bg-white/85 p-6 shadow-sm">
                <h1 class="text-3xl font-black tracking-tight"><?= esc($template['name'] ?? '-') ?></h1>
                <p class="mt-2 text-sm text-slate-600"><?= esc($template['description'] ?? '-') ?></p>
                <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                    <?php foreach ([
                        'Review Status' => $template['review_status'] ?? '-',
                        'Public Status' => $template['public_status'] ?? '-',
                        'Dipakai' => $template['usage_count'] ?? 0,
                        'Publish' => $template['publish_count'] ?? 0,
                        'Submitted' => $template['submitted_at'] ?? '-',
                        'Approved' => $template['approved_at'] ?? '-',
                    ] as $label => $value): ?>
                        <div class="rounded-2xl bg-amber-50/70 p-4">
                            <dt class="text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($label) ?></dt>
                            <dd class="mt-1 font-black"><?= esc((string) $value) ?></dd>
                        </div>
                    <?php endforeach ?>
                </dl>
                <?php if (! empty($template['rejection_reason'])): ?>
                    <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700"><?= esc($template['rejection_reason']) ?></div>
                <?php endif ?>
            </div>
        </section>

        <section class="mt-8 rounded-[28px] border border-amber-100 bg-white/85 p-6 shadow-sm">
            <h2 class="text-xl font-black">Komisi dari template ini</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <thead class="text-xs font-black uppercase tracking-wide text-slate-500">
                        <tr><th class="py-3">Tanggal</th><th>Jenis</th><th>Status</th><th>Nominal</th><th>Catatan</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($ledger as $row): ?>
                            <tr><td class="py-3"><?= esc($row['created_at'] ?? '-') ?></td><td><?= esc($row['type'] ?? '-') ?></td><td><?= esc($row['status'] ?? '-') ?></td><td>Rp <?= number_format((int) ($row['amount'] ?? 0), 0, ',', '.') ?></td><td><?= esc($row['note'] ?? '-') ?></td></tr>
                        <?php endforeach ?>
                        <?php if ($ledger === []): ?>
                            <tr><td class="py-4 text-slate-500" colspan="5">Belum ada komisi.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
