<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVP - <?= esc($page['title'] ?? 'Undangan') ?></title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
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
        $rsvpLocked = (bool) ($rsvpLocked ?? false);
        $accessError = (string) ($accessError ?? '');
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
    ?>
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex min-h-16 w-full max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <img class="h-10 w-auto object-contain" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            <?php if (($page['status'] ?? '') === 'published' && ! empty($page['slug'])): ?>
                <a class="inline-flex h-10 items-center rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-800" href="<?= site_url('u/' . $page['slug']) ?>" target="_blank" rel="noopener">Buka Undangan</a>
            <?php endif ?>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="mb-3 inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-violet-700">Dashboard RSVP</p>
            <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl"><?= esc($page['title'] ?? 'Undangan') ?></h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Akses khusus untuk melihat ringkasan RSVP dan guestbook undangan ini.</p>
        </section>

        <?php if ($rsvpLocked): ?>
            <section class="mx-auto mt-6 max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Kode akses diperlukan</p>
                <h2 class="mt-2 text-xl font-black tracking-tight text-slate-950">Masukkan kode RSVP</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan kode akses yang dibagikan bersama link RSVP oleh pemilik undangan.</p>

                <?php if ($accessError !== ''): ?>
                    <div class="mt-4 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                        <?= esc($accessError) ?>
                    </div>
                <?php endif ?>

                <form class="mt-5 space-y-4" action="<?= esc(current_url(), 'attr') ?>" method="post">
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <label class="block">
                        <span class="text-sm font-black text-slate-800">Kode akses</span>
                        <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-center text-lg font-black uppercase tracking-[0.14em] text-slate-950 outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" name="access_code" type="text" inputmode="text" autocomplete="one-time-code" placeholder="D94FF6-75A200" required autofocus>
                    </label>
                    <button class="inline-flex h-12 w-full items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-black text-white transition hover:bg-slate-800" type="submit">Buka Dashboard RSVP</button>
                </form>
            </section>
        <?php else: ?>
            <section class="mt-6 grid gap-3 md:grid-cols-3">
                <article class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 text-emerald-800 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.16em] opacity-75">Hadir</p>
                    <p class="mt-3 text-3xl font-black tracking-tight"><?= esc((string) ($attendanceSummary['hadir'] ?? 0)) ?></p>
                    <p class="mt-1 text-sm font-semibold opacity-80">Tamu konfirmasi hadir</p>
                </article>
                <article class="rounded-2xl border border-rose-100 bg-rose-50/70 p-5 text-rose-800 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.16em] opacity-75">Tidak hadir</p>
                    <p class="mt-3 text-3xl font-black tracking-tight"><?= esc((string) ($attendanceSummary['tidak_hadir'] ?? 0)) ?></p>
                    <p class="mt-1 text-sm font-semibold opacity-80">Tamu tidak bisa hadir</p>
                </article>
                <article class="rounded-2xl border border-amber-100 bg-amber-50/70 p-5 text-amber-800 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.16em] opacity-75">Ragu</p>
                    <p class="mt-3 text-3xl font-black tracking-tight"><?= esc((string) ($attendanceSummary['ragu'] ?? 0)) ?></p>
                    <p class="mt-1 text-sm font-semibold opacity-80">Belum memastikan</p>
                </article>
            </section>

            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <?php if ($guestbookEntries === []): ?>
                    <div class="p-8">
                        <h2 class="text-lg font-semibold tracking-tight">Belum ada RSVP</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Data RSVP akan tampil di sini setelah tamu mengirim ucapan.</p>
                    </div>
                <?php else: ?>
                    <div class="hidden md:block">
                        <table class="w-full min-w-[820px] border-collapse text-left">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                                <tr>
                                    <th class="px-5 py-4">Nama</th>
                                    <th class="px-5 py-4">Kehadiran</th>
                                    <th class="px-5 py-4">Ucapan</th>
                                    <th class="px-5 py-4">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-sm">
                                <?php foreach ($guestbookEntries as $entry): ?>
                                    <?php
                                        $guestName = $entry['guest_name'] ?? $entry['name'] ?? '-';
                                        $attendance = $normalizeAttendance($entry['attendance'] ?? $entry['attendance_status'] ?? 'ragu');
                                        $message = $entry['message'] ?? '-';
                                    ?>
                                    <tr class="align-top transition hover:bg-slate-50/70">
                                        <td class="px-5 py-4 font-semibold text-slate-900"><?= esc($guestName) ?></td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex h-7 items-center rounded-full px-3 text-xs font-semibold <?= esc($attendanceBadgeClasses[$attendance] ?? $attendanceBadgeClasses['ragu'], 'attr') ?>">
                                                <?= esc($attendanceLabels[$attendance] ?? 'Ragu') ?>
                                            </span>
                                        </td>
                                        <td class="max-w-md px-5 py-4 leading-6 text-slate-700"><?= nl2br(esc($message)) ?></td>
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
                            ?>
                            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <h2 class="truncate text-sm font-black text-slate-950"><?= esc($guestName) ?></h2>
                                    <span class="inline-flex h-7 shrink-0 items-center rounded-full px-3 text-xs font-semibold <?= esc($attendanceBadgeClasses[$attendance] ?? $attendanceBadgeClasses['ragu'], 'attr') ?>">
                                        <?= esc($attendanceLabels[$attendance] ?? 'Ragu') ?>
                                    </span>
                                </div>
                                <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-700"><?= esc($message) ?></p>
                                <p class="mt-4 text-xs font-semibold text-slate-500"><?= esc(aa_format_wib_datetime($entry['created_at'] ?? '')) ?></p>
                            </article>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </section>
        <?php endif ?>
    </main>
</body>
</html>
