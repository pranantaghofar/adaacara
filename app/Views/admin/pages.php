<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Pages - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-900 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Pages', 'adminIcon' => 'pages', 'adminActive' => 'pages']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php if (! empty($setupError)): ?>
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-medium text-amber-900">
                <?= esc($setupError) ?>
            </div>
        <?php endif ?>

        <?php $filters = $filters ?? ['q' => '', 'status' => '']; ?>
        <?php $projectTypeReady = ! empty($projectTypeReady); ?>
        <?php
            $projectTypeLabels = [
                'invitation' => 'Undangan',
                'photobooth' => 'Photobooth',
                'business_profile' => 'Business Profile',
            ];
        ?>
        <?php if (! $projectTypeReady): ?>
            <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-bold leading-6 text-amber-900">
                Pengaturan tipe project belum aktif. Jalankan <code>database/alter_business_profile_project_type.sql</code> agar admin bisa mengaktifkan Business Profile untuk halaman.
            </div>
        <?php endif ?>
        <form class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto]" method="get" action="<?= site_url('admin/pages') ?>">
            <input class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" type="search" name="q" value="<?= esc($filters['q'] ?? '', 'attr') ?>" placeholder="Cari judul, slug, user, email">
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="status">
                <option value="">Semua status</option>
                <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'expired' => 'Expired'] as $value => $label): ?>
                    <option value="<?= esc($value, 'attr') ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach ?>
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-xl bg-teal-700 px-4 text-sm font-semibold text-white transition hover:bg-teal-800" type="submit">Cari</button>
                <a class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:border-teal-700 hover:text-teal-700" href="<?= site_url('admin/pages') ?>">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Judul</th>
                            <th class="px-5 py-3">Slug</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Tipe Project</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Tanggal Event</th>
                            <th class="px-5 py-3">Link</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach (($pages ?? []) as $page): ?>
                            <tr>
                                <td class="px-5 py-4 font-semibold"><?= esc($page['title'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= esc($page['slug'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= esc($page['user_name'] ?? '-') ?><br><span class="text-xs text-slate-500"><?= esc($page['user_email'] ?? '-') ?></span></td>
                                <td class="px-5 py-4">
                                    <?php $projectType = (string) ($page['project_type'] ?? 'invitation'); ?>
                                    <?php if ($projectTypeReady && admin_can('admin.pages.manage')): ?>
                                        <form class="flex min-w-[210px] items-center gap-2" method="post" action="<?= site_url('admin/pages/' . (int) ($page['id'] ?? 0) . '/project-type') ?>">
                                            <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                                            <select class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="project_type">
                                                <?php foreach ($projectTypeLabels as $value => $label): ?>
                                                    <option value="<?= esc($value, 'attr') ?>" <?= $projectType === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                            <button class="h-10 rounded-xl bg-slate-900 px-3 text-xs font-black text-white transition hover:bg-slate-700" type="submit">Simpan</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                            <?= esc($projectTypeLabels[$projectType] ?? $projectType ?: 'Undangan') ?>
                                        </span>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4"><?= esc($page['status'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= esc(($page['event_date'] ?? '') ?: '-') ?></td>
                                <td class="px-5 py-4">
                                    <?php if (($page['status'] ?? '') === 'published' && ! empty($page['slug'])): ?>
                                        <a class="font-semibold text-teal-700" href="<?= site_url('u/' . $page['slug']) ?>" target="_blank" rel="noopener">Buka</a>
                                    <?php else: ?>
                                        <span class="text-slate-500">-</span>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if (empty($pages)): ?>
                            <tr>
                                <td class="px-5 py-10 text-center text-slate-500" colspan="7">
                                    Belum ada data undangan yang bisa ditampilkan.
                                </td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
