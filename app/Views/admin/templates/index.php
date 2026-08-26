<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <title>Admin Templates - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-900 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Templates', 'adminIcon' => 'template', 'adminActive' => 'templates']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php
            helper(['admin_permission', 'aa_datetime']);
            $canManageTemplates = admin_can('admin.templates.manage');
            $canDeleteTemplates = admin_can('admin.templates.delete');
        ?>
        <?php if ($canManageTemplates): ?>
        <div class="mb-6 flex justify-end">
            <a class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-semibold text-white" href="<?= site_url('admin/templates/create') ?>">Tambah Template</a>
        </div>
        <?php endif ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <?php $filters = $filters ?? ['q' => '', 'project_type' => '', 'project_category' => '', 'category' => '', 'premium' => '', 'status' => '']; ?>
        <?php $categories = $categories ?? []; ?>
        <?php $projectCategories = $projectCategories ?? []; ?>
        <?php $templateProjectTypeReady = (bool) ($templateProjectTypeReady ?? false); ?>
        <?php $templateTagsReady = (bool) ($templateTagsReady ?? false); ?>
        <form class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:grid-cols-[1.25fr_190px_210px_210px_170px_170px_auto]" method="get" action="<?= site_url('admin/templates') ?>">
            <input class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" type="search" name="q" value="<?= esc($filters['q'] ?? '', 'attr') ?>" placeholder="Cari nama, slug, kategori">
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="project_type" <?= $templateProjectTypeReady ? '' : 'disabled' ?>>
                <option value="">Semua project</option>
                <option value="invitation" <?= ($filters['project_type'] ?? '') === 'invitation' ? 'selected' : '' ?>>Undangan Digital</option>
                <option value="photobooth" <?= ($filters['project_type'] ?? '') === 'photobooth' ? 'selected' : '' ?>>Digital Photobooth</option>
                <option value="business_profile" <?= ($filters['project_type'] ?? '') === 'business_profile' ? 'selected' : '' ?>>Business Profile</option>
            </select>
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="category">
                <option value="">Kategori undangan</option>
                <?php foreach ($categories as $category): ?>
                    <?php $categoryId = (string) ($category['id'] ?? ''); ?>
                    <?php if ($categoryId === '') continue; ?>
                    <option value="<?= esc($categoryId, 'attr') ?>" <?= (string) ($filters['category'] ?? '') === $categoryId ? 'selected' : '' ?>><?= esc($category['name'] ?? '-') ?></option>
                <?php endforeach ?>
            </select>
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="project_category" <?= $templateTagsReady ? '' : 'disabled' ?>>
                <option value="">Kategori project</option>
                <?php foreach ($projectCategories as $categoryValue => $categoryLabel): ?>
                    <option value="<?= esc($categoryValue, 'attr') ?>" <?= (string) ($filters['project_category'] ?? '') === (string) $categoryValue ? 'selected' : '' ?>><?= esc($categoryLabel) ?></option>
                <?php endforeach ?>
            </select>
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="premium">
                <option value="">Free/Premium</option>
                <option value="free" <?= ($filters['premium'] ?? '') === 'free' ? 'selected' : '' ?>>Free</option>
                <option value="premium" <?= ($filters['premium'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option>
            </select>
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="status">
                <option value="">Semua status</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-xl bg-teal-700 px-4 text-sm font-semibold text-white transition hover:bg-teal-800" type="submit">Cari</button>
                <a class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:border-teal-700 hover:text-teal-700" href="<?= site_url('admin/templates') ?>">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1120px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Thumbnail</th>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Slug</th>
                            <th class="px-5 py-3">Project</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3">Premium</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Update</th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($templates as $template): ?>
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <?php if (! empty($template['thumbnail'])): ?>
                                        <img class="h-16 w-24 rounded-xl object-cover" src="<?= base_url($template['thumbnail']) ?>" alt="<?= esc($template['name'], 'attr') ?>">
                                    <?php else: ?>
                                        <div class="grid h-16 w-24 place-items-center rounded-xl bg-slate-100 text-xs text-slate-500">No image</div>
                                    <?php endif ?>
                                </td>
                                <td class="px-5 py-4 font-semibold"><?= esc($template['name']) ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= esc($template['slug']) ?></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                        <?= esc($template['project_type_label'] ?? 'Undangan Digital') ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4"><?= esc($template['project_category_label'] ?? $template['category_name'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= ((int) ($template['is_premium'] ?? 0)) === 1 ? 'Ya' : 'Tidak' ?></td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold <?= ($template['status'] ?? 'inactive') === 'active' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' ?>">
                                        <?= esc($template['status'] ?? 'inactive') ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600"><?= esc(aa_format_wib_datetime($template['updated_at'] ?? $template['created_at'] ?? '')) ?></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <?php if ($canManageTemplates): ?>
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold hover:border-teal-700 hover:text-teal-700" href="<?= site_url('admin/templates/edit/' . $template['id']) ?>">Edit</a>
                                        <form action="<?= site_url('admin/templates/toggle/' . $template['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <?php $isActive = ($template['status'] ?? 'inactive') === 'active'; ?>
                                            <button class="rounded-xl border px-3 py-2 text-xs font-semibold <?= $isActive ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' ?>" type="submit">
                                                <?= $isActive ? 'Hide / OFF' : 'Unhide / ON' ?>
                                            </button>
                                        </form>
                                        <?php endif ?>
                                        <?php if ($canDeleteTemplates): ?>
                                        <form action="<?= site_url('admin/templates/delete/' . $template['id']) ?>" method="post" onsubmit="return aaConfirmSubmit(event, 'Hapus template ini?', {title: 'Hapus Template', okText: 'Hapus', cancelText: 'Batal', danger: true});">
                                            <?= csrf_field() ?>
                                            <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700" type="submit">Delete</button>
                                        </form>
                                        <?php endif ?>
                                        <?php if (! $canManageTemplates && ! $canDeleteTemplates): ?>
                                            <span class="text-xs font-bold text-slate-500">Read-only</span>
                                        <?php endif ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if ($templates === []): ?>
                            <tr><td class="px-5 py-6 text-slate-600" colspan="9">Belum ada template.</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
