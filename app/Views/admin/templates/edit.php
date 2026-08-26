<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Template - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-900 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Edit Template', 'adminIcon' => 'template', 'adminActive' => 'templates']) ?>
    <main class="mx-auto max-w-[1700px] px-4 py-8 sm:px-6">
        <div class="mb-6 flex justify-end">
            <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold" href="<?= site_url('admin/templates') ?>">Kembali</a>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <form class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" action="<?= site_url('admin/templates/update/' . $template['id']) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?= view('admin/templates/form', [
                'template' => $template,
                'categories' => $categories,
                'templateSubcategories' => $templateSubcategories ?? [],
                'selectedSubcategoryIds' => $selectedSubcategoryIds ?? [],
                'businessCategoryOptions' => $businessCategoryOptions ?? [],
                'selectedBusinessCategory' => $selectedBusinessCategory ?? '',
                'templateSubcategorySetupReady' => $templateSubcategorySetupReady ?? false,
                'templateSubcategoryTableReady' => $templateSubcategoryTableReady ?? false,
                'templateProjectTypeReady' => $templateProjectTypeReady ?? false,
                'submitLabel' => 'Update Template',
            ]) ?>
        </form>
    </main>
</body>
</html>
