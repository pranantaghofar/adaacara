<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guestbook - <?= esc($page['title'] ?? 'Undangan') ?></title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-slate-50 text-slate-900 antialiased">
    <?php
        helper('aa_datetime');

        $attendanceSummary = array_merge([
            'hadir' => 0,
            'tidak_hadir' => 0,
            'ragu' => 0,
        ], (array) ($attendanceSummary ?? []));
        $attendanceLabels = [
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak hadir',
            'ragu' => 'Ragu',
            'attending' => 'Hadir',
            'not_attending' => 'Tidak hadir',
            'pending' => 'Ragu',
        ];
        $attendanceBadgeClasses = [
            'hadir' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
            'tidak_hadir' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-100',
            'ragu' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        ];
        $normalizeAttendance = static function ($value): string {
            $attendance = strtolower(trim((string) $value));
            $aliases = [
                'attending' => 'hadir',
                'present' => 'hadir',
                'yes' => 'hadir',
                'not_attending' => 'tidak_hadir',
                'not-attending' => 'tidak_hadir',
                'absent' => 'tidak_hadir',
                'no' => 'tidak_hadir',
                'pending' => 'ragu',
                'maybe' => 'ragu',
                'unknown' => 'ragu',
                '' => 'ragu',
            ];

            return $aliases[$attendance] ?? (in_array($attendance, ['hadir', 'tidak_hadir', 'ragu'], true) ? $attendance : 'ragu');
        };
        $summaryCards = [
            ['key' => 'hadir', 'label' => 'Hadir', 'caption' => 'Tamu konfirmasi hadir', 'class' => 'border-emerald-100 bg-emerald-50/70 text-emerald-800'],
            ['key' => 'tidak_hadir', 'label' => 'Tidak hadir', 'caption' => 'Tamu tidak bisa hadir', 'class' => 'border-rose-100 bg-rose-50/70 text-rose-800'],
            ['key' => 'ragu', 'label' => 'Ragu', 'caption' => 'Belum memastikan', 'class' => 'border-amber-100 bg-amber-50/70 text-amber-800'],
        ];
    ?>
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex min-h-16 w-full max-w-[1850px] items-center justify-between gap-4 px-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <a href="<?= site_url('dashboard') ?>" class="shrink-0" aria-label="Dashboard AdaAcara">
                    <img class="h-10 w-auto object-contain drop-shadow-sm" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                </a>
            </div>

            <div class="flex items-center gap-2">
                <?= view('components/public_theme_toggle') ?>
                <?= view('components/user_nav_dropdown', ['active' => 'dashboard']) ?>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-[1850px] px-4 py-8 sm:px-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-violet-700">
                <a class="no-underline transition hover:text-violet-900" href="<?= site_url('dashboard') ?>">Dashboard</a>
                <span aria-hidden="true">&gt;</span>
                <span>Guestbook</span>
            </p>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl"><?= esc($page['title'] ?? 'Undangan') ?></h2>
                    <p class="mt-2 text-sm text-slate-600">
                        ID #<?= esc((string) ($page['id'] ?? '-')) ?> ·
                        <?= esc($page['slug'] ?? '-') ?> ·
                        <?= count($guestbookEntries) ?> ucapan
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 transition hover:border-violet-500 hover:text-violet-700" href="<?= site_url('editor/' . $page['id']) ?>">Edit</a>
                    <a class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 transition hover:border-violet-500 hover:text-violet-700" href="<?= site_url('preview/' . $page['id']) ?>" target="_blank" rel="noopener">Preview</a>
                    <?php if (($page['status'] ?? 'draft') === 'published'): ?>
                        <a class="inline-flex h-10 items-center rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-800" href="<?= site_url('u/' . $page['slug']) ?>" target="_blank" rel="noopener">Buka Link</a>
                    <?php endif ?>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-3 md:grid-cols-3">
            <?php foreach ($summaryCards as $card): ?>
                <article class="rounded-2xl border p-5 shadow-sm <?= esc($card['class'], 'attr') ?>">
                    <p class="text-xs font-black uppercase tracking-[0.16em] opacity-75"><?= esc($card['label']) ?></p>
                    <p class="mt-3 text-3xl font-black tracking-tight"><?= esc((string) ($attendanceSummary[$card['key']] ?? 0)) ?></p>
                    <p class="mt-1 text-sm font-semibold opacity-80"><?= esc($card['caption']) ?></p>
                </article>
            <?php endforeach ?>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <?php if ($guestbookEntries === []): ?>
                <div class="p-8">
                    <h3 class="text-lg font-semibold tracking-tight">Belum ada ucapan</h3>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Data guestbook untuk undangan ini akan tampil di sini setelah tamu mengirim ucapan.</p>
                </div>
            <?php else: ?>
                <div class="hidden md:block">
                    <table class="w-full min-w-[920px] border-collapse text-left">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-5 py-4">Nama</th>
                                <th class="px-5 py-4">Kehadiran</th>
                                <th class="px-5 py-4">Ucapan</th>
                                <th class="px-5 py-4">Stiker</th>
                                <th class="px-5 py-4">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            <?php foreach ($guestbookEntries as $entry): ?>
                                <?php
                                    $guestName = $entry['guest_name'] ?? $entry['name'] ?? '-';
                                    $attendance = $normalizeAttendance($entry['attendance'] ?? $entry['attendance_status'] ?? 'ragu');
                                    $message = $entry['message'] ?? '-';
                                    $sticker = $entry['sticker'] ?? '';
                                ?>
                                <tr class="align-top transition hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-900"><?= esc($guestName) ?></div>
                                        <?php if (! empty($entry['email'])): ?>
                                            <div class="mt-1 text-xs text-slate-500"><?= esc($entry['email']) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex h-7 items-center rounded-full px-3 text-xs font-semibold <?= esc($attendanceBadgeClasses[$attendance] ?? $attendanceBadgeClasses['ragu'], 'attr') ?>">
                                            <?= esc($attendanceLabels[$attendance] ?? 'Ragu') ?>
                                        </span>
                                    </td>
                                    <td class="max-w-md px-5 py-4 leading-6 text-slate-700"><?= nl2br(esc($message)) ?></td>
                                    <td class="px-5 py-4">
                                        <?php if ($sticker !== ''): ?>
                                            <img class="h-14 w-14 rounded-xl object-contain" src="<?= aa_asset_url('assets/stiker/' . basename((string) $sticker)) ?>" alt="Sticker">
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif ?>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600"><?= esc(aa_format_wib_datetime($entry['created_at'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <div class="grid gap-3 p-4 md:hidden">
                    <?php foreach ($guestbookEntries as $entry): ?>
                        <?php
                            $guestName = $entry['guest_name'] ?? $entry['name'] ?? '-';
                            $attendance = $normalizeAttendance($entry['attendance'] ?? $entry['attendance_status'] ?? 'ragu');
                            $message = $entry['message'] ?? '-';
                            $sticker = $entry['sticker'] ?? '';
                        ?>
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-black text-slate-950"><?= esc($guestName) ?></h3>
                                    <?php if (! empty($entry['email'])): ?>
                                        <p class="mt-1 truncate text-xs font-semibold text-slate-500"><?= esc($entry['email']) ?></p>
                                    <?php endif ?>
                                </div>
                                <span class="inline-flex h-7 shrink-0 items-center rounded-full px-3 text-xs font-semibold <?= esc($attendanceBadgeClasses[$attendance] ?? $attendanceBadgeClasses['ragu'], 'attr') ?>">
                                    <?= esc($attendanceLabels[$attendance] ?? 'Ragu') ?>
                                </span>
                            </div>
                            <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-700"><?= esc($message) ?></p>
                            <div class="mt-4 flex items-end justify-between gap-3">
                                <p class="text-xs font-semibold text-slate-500"><?= esc(aa_format_wib_datetime($entry['created_at'] ?? '')) ?></p>
                                <?php if ($sticker !== ''): ?>
                                    <img class="h-12 w-12 rounded-xl object-contain" src="<?= aa_asset_url('assets/stiker/' . basename((string) $sticker)) ?>" alt="Sticker">
                                <?php endif ?>
                            </div>
                        </article>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </section>
    </main>
</body>
</html>
