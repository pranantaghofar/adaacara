<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<?php
    $aaDashboardIcon = static function (string $name, string $class = 'h-5 w-5'): string {
        $icons = [
            'orders' => '<path d="M7 7h14l-2 8H8L6 3H3"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'pages' => '<path d="M6 2h9l5 5v15H6V2Z"/><path d="M14 2v6h6M9 13h6M9 17h6"/>',
            'book' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21V5.5Z"/><path d="M4 5.5V21"/>',
        ];

        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['pages']) . '</svg>';
    };
?>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Dashboard', 'adminIcon' => 'dashboard', 'adminActive' => 'dashboard', 'adminBadges' => $adminBadges ?? []]) ?>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ([
                ['Total User', $totalUsers, 'users'],
                ['Total Undangan', $totalPages, 'pages'],
                ['Total Order', $totalOrders, 'orders'],
                ['Total Guestbook', $totalGuestbooks, 'book'],
            ] as [$label, $value, $icon]): ?>
                <article class="rounded-[28px] border border-emerald-100 bg-white/85 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <p class="text-sm font-bold text-slate-600"><?= esc($label) ?></p>
                        <span class="inline-grid h-10 w-10 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><?= $aaDashboardIcon($icon, 'h-5 w-5') ?></span>
                    </div>
                    <p class="mt-3 text-3xl font-black"><?= esc((string) $value) ?></p>
                </article>
            <?php endforeach ?>
        </section>

        <section class="mt-8 overflow-hidden rounded-[28px] border border-emerald-100 bg-white/85 shadow-sm">
            <div class="border-b border-emerald-100 bg-emerald-50/60 px-5 py-4">
                <h2 class="text-lg font-black">Order Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-white/70 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Paket</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($latestOrders as $order): ?>
                            <tr class="transition hover:bg-emerald-50/60">
                                <td class="px-5 py-4 font-semibold"><?= esc($order['invoice_number']) ?></td>
                                <td class="px-5 py-4"><?= esc($order['user_name'] ?? '-') ?><br><span class="text-xs text-slate-500"><?= esc($order['user_email'] ?? '-') ?></span></td>
                                <td class="px-5 py-4"><?= esc($order['plan_name'] ?? '-') ?></td>
                                <td class="px-5 py-4">Rp <?= number_format((int) $order['amount'], 0, ',', '.') ?></td>
                                <td class="px-5 py-4"><?= esc($order['status']) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($latestOrders === []): ?>
                            <tr><td class="px-5 py-6 text-slate-600" colspan="5">Belum ada order.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
