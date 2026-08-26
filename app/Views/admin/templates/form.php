<?php
    $template = $template ?? [];
    $status = old('status', $template['status'] ?? 'active');
    $isPremium = (string) old('is_premium', (string) ($template['is_premium'] ?? '0'));
    $editorType = (string) old('editor_type', $template['editor_type'] ?? 'fabric');
    $projectType = (string) old('project_type', $template['project_type'] ?? 'invitation');
    $templateProjectTypeReady = ! empty($templateProjectTypeReady);
    $templateSubcategories = $templateSubcategories ?? [];
    $selectedSubcategoryIds = array_map('intval', $selectedSubcategoryIds ?? []);
    $businessCategoryOptions = is_array($businessCategoryOptions ?? null) ? $businessCategoryOptions : [];
    $selectedBusinessCategory = (string) old('business_category', $selectedBusinessCategory ?? '');
    $templateSubcategorySetupReady = (bool) ($templateSubcategorySetupReady ?? false);
    $templateSubcategoryTableReady = (bool) ($templateSubcategoryTableReady ?? false);
    $canUploadAnimatedThumbnail = function_exists('current_admin_role')
        && in_array(current_admin_role(), ['superadmin', 'content_admin'], true);
    $thumbnailAccept = $canUploadAnimatedThumbnail
        ? 'image/jpeg,image/png,image/webp,image/gif'
        : 'image/jpeg,image/png,image/webp';
    $thumbnailHelp = $canUploadAnimatedThumbnail
        ? 'JPG, PNG, WEBP, atau GIF animasi maksimal 2MB.'
        : 'JPG, PNG, atau WEBP maksimal 2MB.';
    $subcategoriesByCategory = [];
    foreach ($templateSubcategories as $subcategory) {
        $categoryName = trim((string) ($subcategory['category_name'] ?? 'Kategori'));
        if ($categoryName === '') {
            $categoryName = 'Kategori';
        }
        $subcategoriesByCategory[$categoryName][] = $subcategory;
    }
?>

<style>
    .aa-template-form input[type="file"] {
        width: 100%;
        min-height: 48px;
        cursor: pointer;
        border-style: dashed;
        border-color: #cbd5e1;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .aa-template-form input[type="file"]::file-selector-button {
        min-height: 34px;
        margin-right: 12px;
        border: 0;
        border-radius: 11px;
        background: #0f766e;
        color: #ffffff;
        padding: 0 14px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: background .16s ease, transform .16s ease;
    }

    .aa-template-form input[type="file"]::-webkit-file-upload-button {
        min-height: 34px;
        margin-right: 12px;
        border: 0;
        border-radius: 11px;
        background: #0f766e;
        color: #ffffff;
        padding: 0 14px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: background .16s ease, transform .16s ease;
    }

    .aa-template-form input[type="file"]:hover {
        border-color: #14b8a6;
        background: #ffffff;
        box-shadow: 0 12px 26px rgba(15, 118, 110, .1);
    }

    .aa-template-form input[type="file"]:hover::file-selector-button,
    .aa-template-form input[type="file"]:hover::-webkit-file-upload-button {
        background: #115e59;
        transform: translateY(-1px);
    }

    .aa-template-form input[type="file"]:focus-visible {
        border-color: #0f766e;
        box-shadow: 0 0 0 4px rgba(20, 184, 166, .16);
        outline: none;
    }
</style>

<div class="aa-template-form grid gap-5 md:grid-cols-2">
    <label class="grid gap-2 text-sm font-semibold" data-aa-template-invitation-category>
        Kategori Undangan Digital
        <select class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="category_id" data-aa-template-invitation-category-input>
            <option value="">Pilih kategori undangan</option>
            <?php foreach ($categories as $category): ?>
                <?php $selected = (string) old('category_id', (string) ($template['category_id'] ?? '')) === (string) $category['id']; ?>
                <option value="<?= esc((string) $category['id'], 'attr') ?>" <?= $selected ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
            <?php endforeach ?>
        </select>
        <span class="text-xs font-normal text-slate-500">Hanya dipakai untuk template Undangan Digital.</span>
    </label>

    <label class="grid gap-2 text-sm font-semibold">
        Nama Template
        <input class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="name" value="<?= esc(old('name', $template['name'] ?? ''), 'attr') ?>" required>
    </label>

    <label class="grid gap-2 text-sm font-semibold">
        Slug
        <input class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="slug" value="<?= esc(old('slug', $template['slug'] ?? ''), 'attr') ?>" placeholder="otomatis dari nama jika kosong">
    </label>

    <label class="grid gap-2 text-sm font-semibold">
        Preview URL
        <input class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="preview_url" value="<?= esc(old('preview_url', $template['preview_url'] ?? ''), 'attr') ?>" placeholder="/u/slug-preview">
        <span class="text-xs font-normal text-slate-500">Opsional. Hanya link internal /u/slug yang dipakai untuk preview ringan.</span>
    </label>

    <label class="grid gap-2 text-sm font-semibold">
        Thumbnail
        <input class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="thumbnail" type="file" accept="<?= esc($thumbnailAccept, 'attr') ?>">
        <span class="text-xs font-normal text-slate-500"><?= esc($thumbnailHelp) ?></span>
        <?php if (! empty($template['thumbnail'])): ?>
            <span class="text-xs font-normal text-slate-500">Saat ini: <?= esc($template['thumbnail']) ?></span>
        <?php endif ?>
    </label>

    <label class="grid gap-2 text-sm font-semibold">
        Premium
        <select class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="is_premium">
            <option value="0" <?= $isPremium === '0' ? 'selected' : '' ?>>Tidak</option>
            <option value="1" <?= $isPremium === '1' ? 'selected' : '' ?>>Ya</option>
        </select>
    </label>

    <label class="grid gap-2 text-sm font-semibold">
        Tipe Project
        <?php if ($templateProjectTypeReady): ?>
        <select class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="project_type" data-aa-template-project-type>
            <option value="invitation" <?= $projectType === 'invitation' ? 'selected' : '' ?>>Undangan Digital</option>
            <option value="photobooth" <?= $projectType === 'photobooth' ? 'selected' : '' ?>>Digital Photobooth</option>
            <option value="business_profile" <?= $projectType === 'business_profile' ? 'selected' : '' ?>>Business Profile</option>
        </select>
        <?php else: ?>
        <input class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 font-normal text-slate-500" value="Undangan Digital" disabled>
        <span class="text-xs font-normal text-amber-700">Jalankan database/alter_business_profile_project_type.sql agar tipe project template bisa diatur.</span>
        <?php endif ?>
    </label>

    <label class="grid gap-2 text-sm font-semibold">
        Status
        <select class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="status" required>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </label>

    <label class="grid gap-2 text-sm font-semibold">
        Editor Type
        <select class="rounded-xl border border-slate-200 px-4 py-3 font-normal" name="editor_type">
            <option value="fabric" <?= $editorType === 'fabric' ? 'selected' : '' ?>>Fabric.js</option>
            <option value="grapesjs" <?= $editorType === 'grapesjs' ? 'selected' : '' ?>>GrapesJS Legacy</option>
        </select>
    </label>
</div>

<?php if ($businessCategoryOptions !== []): ?>
    <section class="rounded-2xl border border-pink-200 bg-pink-50/70 p-4" data-aa-template-business-subcategory>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-black text-slate-900">Subkategori Business Profile</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Pilih kategori bisnis agar template muncul di list Business Profile yang tepat.</p>
            </div>
            <span class="rounded-full border border-pink-200 bg-white px-3 py-1 text-xs font-black text-pink-700">Business Profile</span>
        </div>
        <div class="mt-4 grid gap-2 sm:grid-cols-3 lg:grid-cols-5">
            <?php foreach ($businessCategoryOptions as $businessSlug => $businessLabel): ?>
                <?php $checked = $selectedBusinessCategory === (string) $businessSlug; ?>
                <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-2xl border px-3 py-2 text-sm font-black transition <?= $checked ? 'border-pink-500 bg-white text-pink-800 shadow-sm' : 'border-pink-100 bg-white/70 text-slate-700 hover:border-pink-300 hover:bg-white' ?>">
                    <input class="h-4 w-4 rounded border-slate-300 text-pink-600 focus:ring-pink-500" type="radio" name="business_category" value="<?= esc((string) $businessSlug, 'attr') ?>" <?= $checked ? 'checked' : '' ?> data-aa-template-business-category-input>
                    <span class="min-w-0 truncate"><?= esc((string) $businessLabel) ?></span>
                </label>
            <?php endforeach ?>
        </div>
    </section>
<?php endif ?>

<?php if ($templateSubcategoryTableReady): ?>
    <section class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4" data-aa-template-invitation-subcategories>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-black text-slate-900">Subkategori Undangan Digital</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Pilih menu header mana saja yang akan memanggil template ini.</p>
            </div>
            <?php if (! $templateSubcategorySetupReady): ?>
                <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">SQL relasi belum aktif</span>
            <?php endif ?>
        </div>

        <?php if (! $templateSubcategorySetupReady): ?>
            <div class="mt-4 rounded-2xl border border-amber-200 bg-white px-4 py-3 text-xs font-bold leading-relaxed text-amber-700">
                Jalankan update SQL subkategori terlebih dahulu agar pilihan ini bisa tersimpan.
            </div>
        <?php elseif ($subcategoriesByCategory === []): ?>
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-3 text-xs font-bold text-slate-500">
                Belum ada subkategori aktif.
            </div>
        <?php else: ?>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <?php foreach ($subcategoriesByCategory as $categoryName => $items): ?>
                    <div class="rounded-2xl border border-slate-200 bg-white p-3">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-xs font-black uppercase tracking-[.08em] text-slate-500"><?= esc($categoryName) ?></h3>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-700"><?= count($items) ?> opsi</span>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <?php foreach ($items as $subcategory): ?>
                                <?php
                                    $subcategoryId = (int) ($subcategory['id'] ?? 0);
                                    $checked = in_array($subcategoryId, $selectedSubcategoryIds, true);
                                ?>
                                <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-2xl border px-3 py-2 text-sm font-black transition <?= $checked ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-sm' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-emerald-200 hover:bg-white' ?>">
                                    <input class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500" type="checkbox" name="subcategory_ids[]" value="<?= esc((string) $subcategoryId, 'attr') ?>" <?= $checked ? 'checked' : '' ?>>
                                    <span class="min-w-0">
                                        <span class="block truncate"><?= esc((string) ($subcategory['name'] ?? 'Subkategori')) ?></span>
                                        <?php if (! empty($subcategory['group_title'])): ?>
                                            <span class="mt-0.5 block truncate text-[11px] font-bold text-slate-400"><?= esc((string) $subcategory['group_title']) ?></span>
                                        <?php endif ?>
                                    </span>
                                </label>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </section>
<?php endif ?>

<label class="grid gap-2 text-sm font-semibold">
    Deskripsi
    <textarea class="min-h-28 rounded-xl border border-slate-200 px-4 py-3 text-sm font-normal" name="description" maxlength="500" placeholder="Deskripsi singkat template untuk user"><?= esc(old('description', $template['description'] ?? '')) ?></textarea>
</label>

<label class="grid gap-2 text-sm font-semibold">
    HTML
    <textarea class="min-h-64 rounded-xl border border-slate-200 px-4 py-3 font-mono text-sm font-normal" name="html" spellcheck="false"><?= esc(old('html', $template['html'] ?? '')) ?></textarea>
</label>

<label class="grid gap-2 text-sm font-semibold">
    CSS
    <textarea class="min-h-48 rounded-xl border border-slate-200 px-4 py-3 font-mono text-sm font-normal" name="css" spellcheck="false"><?= esc(old('css', $template['css'] ?? '')) ?></textarea>
</label>

<label class="grid gap-2 text-sm font-semibold">
    JS
    <textarea class="min-h-48 rounded-xl border border-slate-200 px-4 py-3 font-mono text-sm font-normal" name="js" spellcheck="false"><?= esc(old('js', $template['js'] ?? '')) ?></textarea>
</label>

<label class="grid gap-2 text-sm font-semibold">
    Editor JSON
    <textarea class="min-h-48 rounded-xl border border-slate-200 px-4 py-3 font-mono text-sm font-normal" name="editor_json" spellcheck="false"><?= esc(old('editor_json', $template['editor_json'] ?? $template['grapesjs_json'] ?? '')) ?></textarea>
</label>

<div class="flex flex-wrap justify-end gap-2">
    <a class="inline-flex h-11 items-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold" href="<?= site_url('admin/templates') ?>">Batal</a>
    <button class="inline-flex h-11 items-center rounded-xl bg-teal-700 px-5 text-sm font-semibold text-white" type="submit"><?= esc($submitLabel ?? 'Simpan') ?></button>
</div>

<script>
    (function () {
        const projectInput = document.querySelector('[data-aa-template-project-type]');
        const invitationCategory = document.querySelector('[data-aa-template-invitation-category]');
        const invitationCategoryInput = document.querySelector('[data-aa-template-invitation-category-input]');
        const invitationSubcategories = document.querySelector('[data-aa-template-invitation-subcategories]');
        const businessSubcategory = document.querySelector('[data-aa-template-business-subcategory]');
        const businessInputs = Array.from(document.querySelectorAll('[data-aa-template-business-category-input]'));

        function syncProjectFields() {
            const projectType = projectInput ? projectInput.value : 'invitation';
            const isInvitation = projectType === 'invitation';
            const isBusiness = projectType === 'business_profile';

            invitationCategory?.classList.toggle('hidden', !isInvitation);
            invitationSubcategories?.classList.toggle('hidden', !isInvitation);
            businessSubcategory?.classList.toggle('hidden', !isBusiness);

            if (invitationCategoryInput) {
                invitationCategoryInput.required = isInvitation;
                invitationCategoryInput.disabled = !isInvitation;
            }

            businessInputs.forEach((input, index) => {
                input.disabled = !isBusiness;
                input.required = isBusiness && index === 0;
            });
        }

        projectInput?.addEventListener('change', syncProjectFields);
        syncProjectFields();
    })();
</script>
