<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Guestbooks - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-900 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'Guestbooks', 'adminIcon' => 'book', 'adminActive' => 'guestbooks']) ?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <?php helper('aa_datetime'); ?>

        <?php if (! empty($setupError)): ?>
            <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800">
                <?= esc($setupError) ?>
            </div>
        <?php endif ?>

        <?php if (! empty($legacyWarning)): ?>
            <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800">
                <?= esc($legacyWarning) ?>
            </div>
        <?php endif ?>

        <?php $filters = $filters ?? ['q' => '', 'approval' => '', 'attendance' => '']; ?>
        <form class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_190px_190px_auto]" method="get" action="<?= site_url('admin/guestbooks') ?>">
            <input class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" type="search" name="q" value="<?= esc($filters['q'] ?? '', 'attr') ?>" placeholder="Cari tamu, ucapan, undangan, slug">
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="approval">
                <option value="">Semua approval</option>
                <option value="approved" <?= ($filters['approval'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="pending" <?= ($filters['approval'] ?? '') === 'pending' ? 'selected' : '' ?>>Belum approved</option>
            </select>
            <select class="h-11 rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-100" name="attendance">
                <option value="">Semua kehadiran</option>
                <?php foreach (['hadir' => 'Hadir', 'tidak_hadir' => 'Tidak hadir', 'ragu' => 'Ragu'] as $value => $label): ?>
                    <option value="<?= esc($value, 'attr') ?>" <?= ($filters['attendance'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach ?>
            </select>
            <div class="flex gap-2">
                <button class="h-11 rounded-xl bg-teal-700 px-4 text-sm font-semibold text-white transition hover:bg-teal-800" type="submit">Cari</button>
                <a class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:border-teal-700 hover:text-teal-700" href="<?= site_url('admin/guestbooks') ?>">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Tamu</th>
                            <th class="px-5 py-3">Ucapan</th>
                            <th class="px-5 py-3">Kehadiran</th>
                            <th class="px-5 py-3">Undangan</th>
                            <th class="px-5 py-3">Approved</th>
                            <th class="px-5 py-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($guestbooks as $guestbook): ?>
                            <tr class="align-top">
                                <td class="px-5 py-4 font-semibold"><?= esc($guestbook['guest_name'] ?? '-') ?></td>
                                <td class="px-5 py-4 max-w-md"><?= esc($guestbook['message'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= esc($guestbook['attendance'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= esc($guestbook['page_title'] ?? '-') ?><br><span class="text-xs text-slate-500"><?= esc($guestbook['page_slug'] ?? '-') ?></span></td>
                                <td class="px-5 py-4"><?= ((int) ($guestbook['is_approved'] ?? 0)) === 1 ? 'Ya' : 'Tidak' ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= esc(aa_format_wib_datetime($guestbook['created_at'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
