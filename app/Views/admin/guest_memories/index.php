<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guest Memories - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Guest Memories', 'adminKicker' => 'Memories', 'adminIcon' => 'image', 'adminActive' => 'guestMemories']) ?>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper(['admin_permission', 'aa_datetime']);
            $items = $items ?? [];
            $filters = $filters ?? ['status' => '', 'q' => ''];
            $canManage = admin_can('admin.guest_memories.manage');
            $canDelete = admin_can('admin.guest_memories.delete');
            $assetUrl = static function (?string $path): string {
                $path = trim((string) $path);
                if ($path === '') {
                    return '';
                }
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }
                return base_url(ltrim($path, '/'));
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
                    <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Moderasi</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight">Upload tamu</h1>
                    <p class="mt-1 text-sm font-semibold text-slate-600">Kelola foto Guest Memories tanpa menghapus file fisik.</p>
                </div>
                <a class="inline-flex h-11 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-black text-emerald-800" href="<?= site_url('admin/users') ?>">Atur Member</a>
            </div>

            <form class="grid gap-3 md:grid-cols-[1fr_190px_auto]" method="get" action="<?= site_url('admin/guest-memories') ?>">
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="search" name="q" value="<?= esc((string) ($filters['q'] ?? ''), 'attr') ?>" placeholder="Cari tamu, undangan, slug">
                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="status">
                    <option value="">Semua status</option>
                    <?php foreach (['approved' => 'Approved', 'pending' => 'Pending', 'hidden' => 'Hidden', 'rejected' => 'Rejected'] as $value => $label): ?>
                        <option value="<?= esc($value, 'attr') ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach ?>
                </select>
                <div class="flex gap-2">
                    <button class="h-12 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white" type="submit">Cari</button>
                    <a class="inline-flex h-12 items-center rounded-2xl border border-slate-200 px-4 text-sm font-black text-slate-700" href="<?= site_url('admin/guest-memories') ?>">Reset</a>
                </div>
            </form>
        </section>

        <?php if (empty($isReady)): ?>
            <section class="rounded-[28px] border border-amber-200 bg-amber-50 p-6 text-sm font-bold leading-6 text-amber-800">
                Tabel Guest Memories belum tersedia. Jalankan SQL:
                <div class="mt-2 rounded-2xl bg-white/70 px-3 py-2 font-mono text-xs">database/alter_guest_memories.sql</div>
            </section>
        <?php elseif ($items === []): ?>
            <section class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 p-10 text-center text-sm font-bold text-slate-500">Belum ada upload Guest Memories.</section>
        <?php else: ?>
            <section class="overflow-hidden rounded-[28px] border border-emerald-100 bg-white/90 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1040px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-5 py-3">Foto</th>
                                <th class="px-5 py-3">Tamu</th>
                                <th class="px-5 py-3">Undangan</th>
                                <th class="px-5 py-3">Frame</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $photo = $assetUrl($item['thumbnail'] ?? $item['photo'] ?? '');
                                    $status = (string) ($item['status'] ?? 'approved');
                                ?>
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <?php if ($photo !== ''): ?>
                                            <a href="<?= esc($assetUrl($item['photo'] ?? ''), 'attr') ?>" target="_blank" rel="noopener">
                                                <img class="h-24 w-20 rounded-2xl border border-slate-100 object-cover" src="<?= esc($photo, 'attr') ?>" alt="">
                                            </a>
                                        <?php else: ?>
                                            <span class="inline-flex h-24 w-20 rounded-2xl bg-slate-100"></span>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4 font-black"><?= esc((string) ($item['guest_name'] ?? '-')) ?></td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold"><?= esc((string) ($item['page_title'] ?? '-')) ?></div>
                                        <a class="text-xs font-bold text-emerald-700" href="<?= site_url('u/' . ($item['page_slug'] ?? '') . '/memories') ?>" target="_blank" rel="noopener"><?= esc((string) ($item['page_slug'] ?? '-')) ?></a>
                                    </td>
                                    <td class="px-5 py-4"><?= esc('Frame ' . (string) ((int) ($item['frame_id'] ?? 1))) ?></td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black <?= $status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($status === 'hidden' ? 'bg-slate-100 text-slate-600' : 'bg-rose-100 text-rose-700') ?>"><?= esc($status) ?></span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600"><?= esc(function_exists('aa_format_wib_datetime') ? aa_format_wib_datetime($item['created_at'] ?? '') : (string) ($item['created_at'] ?? '')) ?></td>
                                    <td class="px-5 py-4">
                                        <?php if ($canManage): ?>
                                            <div class="flex flex-wrap gap-2">
                                                <form method="post" action="<?= site_url('admin/guest-memories/' . (int) $item['id'] . '/approve') ?>"><?= csrf_field() ?><button class="h-9 rounded-xl bg-emerald-700 px-3 text-xs font-black text-white" type="submit">Approve</button></form>
                                                <form method="post" action="<?= site_url('admin/guest-memories/' . (int) $item['id'] . '/hide') ?>"><?= csrf_field() ?><button class="h-9 rounded-xl border border-slate-200 px-3 text-xs font-black text-slate-700" type="submit">Hide</button></form>
                                                <form method="post" action="<?= site_url('admin/guest-memories/' . (int) $item['id'] . '/reject') ?>"><?= csrf_field() ?><button class="h-9 rounded-xl border border-rose-200 bg-rose-50 px-3 text-xs font-black text-rose-700" type="submit">Reject</button></form>
                                                <?php if ($canDelete): ?>
                                                    <form method="post" action="<?= site_url('admin/guest-memories/' . (int) $item['id'] . '/delete') ?>"><?= csrf_field() ?><button class="h-9 rounded-xl bg-slate-900 px-3 text-xs font-black text-white" type="submit">Delete</button></form>
                                                <?php endif ?>
                                            </div>
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
