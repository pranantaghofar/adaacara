<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Publish Requests - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Publish Requests', 'adminKicker' => 'Domain', 'adminIcon' => 'globe', 'adminActive' => 'publishDomains']) ?>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper('admin_permission');
            $items = is_array($items ?? null) ? $items : [];
            $filters = is_array($filters ?? null) ? $filters : ['q' => '', 'status' => '', 'project_type' => ''];
            $statusOptions = is_array($statusOptions ?? null) ? $statusOptions : [];
            $projectTypeOptions = is_array($projectTypeOptions ?? null) ? $projectTypeOptions : [];
            $documentRoot = (string) ($documentRoot ?? '');
            $canManage = admin_can('admin.publish_domains.manage');
            $statusLabels = [
                'pending_activation' => 'Menunggu aktivasi',
                'activating' => 'Sedang diaktifkan',
                'active' => 'Aktif',
                'failed' => 'Gagal',
                'suspended' => 'Suspend',
                'disabled' => 'Nonaktif',
            ];
            $projectTypeLabels = [
                'invitation' => 'Undangan Digital',
                'photobooth' => 'Photobooth',
                'business_profile' => 'Business Profile',
            ];
            $statusBadgeClass = static function (string $status): string {
                return match ($status) {
                    'active' => 'bg-emerald-100 text-emerald-800',
                    'pending_activation', 'activating' => 'bg-amber-100 text-amber-800',
                    'failed', 'suspended', 'disabled' => 'bg-rose-100 text-rose-700',
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
                    <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Aktivasi manual</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight">Publish Requests</h1>
                    <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 text-slate-600">Daftar alamat publish yang sudah dipilih user. Buat subdomain di hosting dengan document root yang sama, lalu tandai aktif setelah alamat benar-benar siap.</p>
                </div>
                <a class="inline-flex h-11 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-black text-emerald-800" href="<?= site_url('admin/pages') ?>">Pages</a>
            </div>

            <form class="grid gap-3 lg:grid-cols-[1fr_220px_220px_auto]" method="get" action="<?= site_url('admin/publish-requests') ?>">
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" type="search" name="q" value="<?= esc((string) ($filters['q'] ?? ''), 'attr') ?>" placeholder="Cari subdomain, project, slug, user">
                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="status">
                    <option value="">Semua status</option>
                    <?php foreach ($statusOptions as $value): ?>
                        <option value="<?= esc((string) $value, 'attr') ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= esc($statusLabels[$value] ?? $value) ?></option>
                    <?php endforeach ?>
                </select>
                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="project_type">
                    <option value="">Semua tipe</option>
                    <?php foreach ($projectTypeOptions as $value): ?>
                        <option value="<?= esc((string) $value, 'attr') ?>" <?= ($filters['project_type'] ?? '') === $value ? 'selected' : '' ?>><?= esc($projectTypeLabels[$value] ?? $value) ?></option>
                    <?php endforeach ?>
                </select>
                <div class="flex gap-2">
                    <button class="h-12 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white" type="submit">Cari</button>
                    <a class="inline-flex h-12 items-center rounded-2xl border border-slate-200 px-4 text-sm font-black text-slate-700" href="<?= site_url('admin/publish-requests') ?>">Reset</a>
                </div>
            </form>

            <div class="mt-4 grid gap-2 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-xs font-bold leading-5 text-emerald-900 md:grid-cols-4">
                <div><span class="font-black">1.</span> Buat subdomain di cPanel.</div>
                <div><span class="font-black">2.</span> Gunakan share document root.</div>
                <div><span class="font-black">3.</span> Document root: <span class="font-mono"><?= esc($documentRoot !== '' ? $documentRoot : 'public_html') ?></span></div>
                <div><span class="font-black">4.</span> Klik Aktif setelah URL benar-benar siap.</div>
            </div>
        </section>

        <?php if (empty($isReady)): ?>
            <section class="rounded-[28px] border border-amber-200 bg-amber-50 p-6 text-sm font-bold leading-6 text-amber-800">
                Tabel published_domains belum tersedia. Jalankan SQL:
                <div class="mt-2 rounded-2xl bg-white/70 px-3 py-2 font-mono text-xs">database/alter_published_domains.sql</div>
            </section>
        <?php elseif ($items === []): ?>
            <section class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 p-10 text-center text-sm font-bold text-slate-500">Belum ada publish request.</section>
        <?php else: ?>
            <section class="overflow-hidden rounded-[28px] border border-emerald-100 bg-white/90 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1120px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-5 py-3">Alamat</th>
                                <th class="px-5 py-3">Project</th>
                                <th class="px-5 py-3">User</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Waktu</th>
                                <th class="px-5 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $id = (int) ($item['id'] ?? 0);
                                    $status = (string) ($item['status'] ?? 'pending_activation');
                                    $projectType = (string) ($item['project_type'] ?? 'invitation');
                                    $fullDomain = (string) ($item['full_domain'] ?? '');
                                    $slug = (string) ($item['page_slug'] ?? '');
                                ?>
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-black text-slate-950"><?= esc($fullDomain !== '' ? $fullDomain : '-') ?></div>
                                        <div class="mt-1 text-xs font-bold text-slate-500">Subdomain: <?= esc((string) ($item['subdomain'] ?? '-')) ?></div>
                                        <div class="mt-2 rounded-2xl bg-slate-50 px-3 py-2 text-xs font-mono text-slate-600"><?= esc($documentRoot !== '' ? $documentRoot : 'public_html') ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700"><?= esc($projectTypeLabels[$projectType] ?? $projectType) ?></span>
                                        <div class="mt-3 font-bold"><?= esc((string) ($item['page_title'] ?? '-')) ?></div>
                                        <?php if ($slug !== ''): ?>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <a class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700" href="<?= site_url('u/' . $slug) ?>" target="_blank" rel="noopener">/u/<?= esc($slug) ?></a>
                                                <?php if ($status === 'active' && $fullDomain !== ''): ?>
                                                    <a class="rounded-full bg-sky-50 px-3 py-1 text-xs font-black text-sky-700" href="https://<?= esc($fullDomain, 'attr') ?>" target="_blank" rel="noopener">Buka Subdomain</a>
                                                <?php endif ?>
                                            </div>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold"><?= esc((string) ($item['user_name'] ?? '-')) ?></div>
                                        <div class="mt-1 text-xs font-semibold text-slate-500"><?= esc((string) ($item['user_email'] ?? '-')) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black <?= esc($statusBadgeClass($status), 'attr') ?>"><?= esc($statusLabels[$status] ?? $status) ?></span>
                                        <?php if (! empty($item['activation_notes'])): ?>
                                            <div class="mt-3 max-w-xs text-xs font-semibold leading-5 text-slate-500"><?= esc((string) $item['activation_notes']) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4 text-xs font-semibold leading-5 text-slate-500">
                                        <div>Reserved: <?= esc($formatDateTime($item['reserved_at'] ?? $item['created_at'] ?? '')) ?></div>
                                        <div>Activated: <?= esc($formatDateTime($item['activated_at'] ?? '')) ?></div>
                                        <div>Failed: <?= esc($formatDateTime($item['failed_at'] ?? '')) ?></div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php if ($canManage): ?>
                                            <div class="mb-3 flex flex-wrap gap-2">
                                                <?php foreach ([
                                                    'activating' => 'Mengaktifkan',
                                                    'active' => 'Aktif',
                                                    'failed' => 'Gagal',
                                                    'suspended' => 'Suspend',
                                                ] as $action => $label): ?>
                                                    <form method="post" action="<?= site_url('admin/publish-requests/' . $id . '/quick/' . $action) ?>">
                                                        <?= csrf_field() ?>
                                                        <button class="h-9 rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-700 transition hover:border-emerald-500 hover:text-emerald-700" type="submit"><?= esc($label) ?></button>
                                                    </form>
                                                <?php endforeach ?>
                                            </div>
                                            <form class="grid min-w-[320px] gap-2" method="post" action="<?= site_url('admin/publish-requests/' . $id . '/update') ?>">
                                                <?= csrf_field() ?>
                                                <select class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold" name="status">
                                                    <?php foreach ($statusOptions as $value): ?>
                                                        <option value="<?= esc((string) $value, 'attr') ?>" <?= $status === $value ? 'selected' : '' ?>><?= esc($statusLabels[$value] ?? $value) ?></option>
                                                    <?php endforeach ?>
                                                </select>
                                                <textarea class="min-h-20 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold" name="activation_notes" placeholder="Catatan aktivasi untuk admin/internal"><?= esc((string) ($item['activation_notes'] ?? '')) ?></textarea>
                                                <button class="h-10 rounded-xl bg-slate-950 px-4 text-xs font-black text-white" type="submit">Simpan Status</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-500">Mode lihat saja</span>
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
