<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Creator Applications - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Creator Applications', 'adminIcon' => 'review', 'adminActive' => 'creatorApplications']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php helper('aa_datetime'); ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <section class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-2">
                <?php foreach ([null => 'Semua', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $status => $label): ?>
                    <?php
                        $active = $status === $statusFilter;
                        $query = [];
                        if ($status) {
                            $query['status'] = $status;
                        }
                        if (($search ?? '') !== '') {
                            $query['q'] = $search;
                        }
                    ?>
                    <a class="rounded-2xl px-4 py-2 text-sm font-black ring-1 transition <?= $active ? 'bg-emerald-700 text-white ring-emerald-700' : 'bg-white text-slate-700 ring-emerald-100 hover:text-emerald-700 hover:ring-emerald-500' ?>" href="<?= site_url('admin/creator-applications' . ($query ? '?' . http_build_query($query) : '')) ?>"><?= esc($label) ?></a>
                <?php endforeach ?>
            </div>
            <form class="grid gap-2 sm:grid-cols-[1fr_auto_auto]" method="get" action="<?= site_url('admin/creator-applications') ?>">
                <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= esc($statusFilter, 'attr') ?>"><?php endif ?>
                <input class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="search" name="q" value="<?= esc($search ?? '', 'attr') ?>" placeholder="Cari creator, user, email">
                <button class="h-11 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white" type="submit">Cari</button>
                <a class="inline-flex h-11 items-center justify-center rounded-2xl border border-emerald-100 bg-white px-4 text-sm font-black text-slate-700 transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('admin/creator-applications') ?>">Reset</a>
            </form>
        </section>

        <section class="overflow-hidden rounded-[28px] border border-emerald-100 bg-white/90 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-emerald-50/70 text-xs font-black uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-4">Creator</th>
                            <th class="px-5 py-4">User</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Reviewer</th>
                            <th class="px-5 py-4">Tanggal</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($applications as $application): ?>
                            <?php
                                $status = (string) ($application['status'] ?? 'pending');
                                $badgeClass = match ($status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                    'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                    default => 'bg-amber-50 text-amber-700 ring-amber-200',
                                };
                            ?>
                            <tr class="transition hover:bg-emerald-50/60">
                                <td class="px-5 py-4 font-black"><?= esc($application['display_name']) ?></td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold"><?= esc($application['user_name'] ?? '-') ?></div>
                                    <div class="text-xs text-slate-500"><?= esc($application['user_email'] ?? '-') ?></div>
                                </td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ring-1 <?= esc($badgeClass) ?>"><?= esc($status) ?></span></td>
                                <td class="px-5 py-4 text-slate-600"><?= esc($application['reviewer_name'] ?? '-') ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= esc(aa_format_wib_datetime($application['created_at'] ?? '')) ?></td>
                                <td class="px-5 py-4"><a class="inline-flex h-9 items-center rounded-xl border border-emerald-100 bg-white px-3 text-xs font-black text-slate-900 transition hover:border-emerald-600 hover:text-emerald-700" href="<?= site_url('admin/creator-applications/' . $application['id']) ?>">Detail</a></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($applications === []): ?>
                            <tr><td class="px-5 py-8 text-center text-slate-500" colspan="6">Belum ada aplikasi creator.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
