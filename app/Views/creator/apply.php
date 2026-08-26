<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Apply Creator - Ada Acara') ?></title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <?= view('components/public_theme_assets') ?>
</head>
<body class="aa-public-theme-page min-h-screen bg-slate-50 text-slate-950 antialiased">
    <header class="border-b border-amber-100 bg-white/85 backdrop-blur-xl">
        <div class="mx-auto flex min-h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-amber-700">Creator Program</p>
                <h1 class="text-xl font-black tracking-tight">Apply Creator</h1>
            </div>
            <div class="flex items-center gap-3">
                <?= view('components/public_theme_toggle') ?>
                <a class="rounded-2xl border border-amber-100 bg-white px-4 py-2 text-sm font-bold shadow-sm transition hover:border-amber-600 hover:text-amber-700" href="<?= site_url('dashboard') ?>">Dashboard</a>
            </div>
        </div>
    </header>

    <main class="mx-auto grid max-w-5xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[1.1fr_.9fr]">
        <section class="rounded-[28px] border border-amber-100 bg-white/90 p-6 shadow-sm">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach ?>
                </div>
            <?php endif ?>

            <?php if ($profile !== null): ?>
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Creator Aktif</p>
                    <h2 class="mt-2 text-2xl font-black"><?= esc($profile['display_name']) ?></h2>
                    <p class="mt-2 text-sm text-slate-600">Profile creator kamu sudah aktif dengan slug <span class="font-bold text-slate-900"><?= esc($profile['slug']) ?></span>.</p>
                </div>
            <?php else: ?>
                <div class="mb-6">
                    <p class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-amber-700">Tahap 1</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight">Daftar sebagai creator template.</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Pengajuan creator gratis dan akan direview admin. Setelah disetujui, profile creator dibuat otomatis dan akun kamu mendapat akses creator.</p>
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-black text-amber-800">
                        Menjadi creator tidak dikenakan biaya pendaftaran.
                    </div>
                </div>

                <?php if (($application['status'] ?? null) === 'pending'): ?>
                    <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Menunggu Review</p>
                        <h3 class="mt-2 text-xl font-black"><?= esc($application['display_name']) ?></h3>
                        <p class="mt-2 text-sm text-amber-800">Aplikasi kamu sedang menunggu approval admin. Kamu belum bisa mengirim aplikasi baru sampai status ini berubah.</p>
                    </div>
                <?php else: ?>
                    <?php if (($application['status'] ?? null) === 'rejected'): ?>
                        <div class="mb-5 rounded-3xl border border-rose-200 bg-rose-50 p-5">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-700">Ditolak</p>
                            <p class="mt-2 text-sm text-rose-800"><?= esc($application['reason'] ?: 'Aplikasi sebelumnya ditolak admin.') ?></p>
                        </div>
                    <?php endif ?>

                    <form action="<?= site_url('creator/apply') ?>" method="post" class="space-y-5">
                        <?= csrf_field() ?>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Display Name</span>
                            <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-amber-600 focus:ring-4 focus:ring-amber-100" name="display_name" value="<?= esc(old('display_name', $application['display_name'] ?? '')) ?>" maxlength="80" required>
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Bio</span>
                            <textarea class="mt-2 min-h-36 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-600 focus:ring-4 focus:ring-amber-100" name="bio" maxlength="1000" required><?= esc(old('bio', $application['bio'] ?? '')) ?></textarea>
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Portfolio URL <span class="font-medium text-slate-400">(opsional)</span></span>
                            <input class="mt-2 h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-amber-600 focus:ring-4 focus:ring-amber-100" name="portfolio_url" value="<?= esc(old('portfolio_url', $application['portfolio_url'] ?? '')) ?>" placeholder="https://..." maxlength="255">
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Social Links JSON <span class="font-medium text-slate-400">(opsional)</span></span>
                            <textarea class="mt-2 min-h-28 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 font-mono text-xs outline-none transition focus:border-amber-600 focus:ring-4 focus:ring-amber-100" name="social_links" maxlength="2000" placeholder='{"instagram":"https://instagram.com/username"}'><?= esc(old('social_links', $application['social_links'] ?? '')) ?></textarea>
                        </label>

                        <button class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-900 px-6 text-sm font-black text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800" type="submit">Kirim Pengajuan</button>
                    </form>
                <?php endif ?>
            <?php endif ?>
        </section>

        <aside class="rounded-[28px] border border-amber-100 bg-white/80 p-6 shadow-sm">
            <div class="mb-6 rounded-3xl border border-amber-100 bg-amber-50/70 p-5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Detail Program</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight">Pengajuan Creator Gratis</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Pendaftaran creator template gratis. Setelah diapprove admin, creator aktif permanen dan bisa submit template untuk review.</p>
                <p class="mt-4 rounded-2xl border border-amber-200 bg-white px-4 py-3 text-sm font-bold leading-6 text-amber-800">Creator aktif bisa submit template untuk direview admin. Jika template creator dipakai user lain dan memenuhi aturan royalty, creator mendapat 90% dari nilai lisensi template.</p>
                <dl class="mt-5 grid gap-2 text-sm">
                    <div class="flex justify-between gap-4 rounded-2xl bg-white px-4 py-3">
                        <dt class="font-semibold text-slate-500">Biaya daftar</dt>
                        <dd class="text-right font-black text-slate-950">Gratis</dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-white px-4 py-3">
                        <dt class="font-semibold text-slate-500">Akses</dt>
                        <dd class="text-right font-black text-slate-950">Creator aktif permanen</dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-white px-4 py-3">
                        <dt class="font-semibold text-slate-500">Royalty</dt>
                        <dd class="text-right font-black text-slate-950">90% nilai lisensi template</dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-white px-4 py-3">
                        <dt class="font-semibold text-slate-500">Template publik</dt>
                        <dd class="text-right font-black text-slate-950">Wajib approve admin</dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-white px-4 py-3">
                        <dt class="font-semibold text-slate-500">Publish undangan</dt>
                        <dd class="text-right font-black text-slate-950">Tidak termasuk</dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-white px-4 py-3">
                        <dt class="font-semibold text-slate-500">Earnings</dt>
                        <dd class="text-right font-black text-slate-950">Dashboard Creator</dd>
                    </div>
                </dl>
            </div>

            <h2 class="text-lg font-black">Status Aplikasi</h2>
            <p class="mt-2 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-600">Daftar creator gratis. Pembayaran hanya berlaku untuk paket publish atau paket seller sesuai kebutuhan akun.</p>
            <?php if ($application === null): ?>
                <p class="mt-3 text-sm leading-6 text-slate-600">Belum ada aplikasi creator. Isi form untuk mulai proses review admin.</p>
            <?php else: ?>
                <?php
                    $status = (string) ($application['status'] ?? 'pending');
                    $badgeClass = match ($status) {
                        'approved' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
                        default => 'bg-amber-50 text-amber-700 ring-amber-200',
                    };
                ?>
                <span class="mt-4 inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide ring-1 <?= esc($badgeClass) ?>"><?= esc($status) ?></span>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-bold text-slate-500">Nama</dt>
                        <dd class="mt-1 font-semibold text-slate-900"><?= esc($application['display_name']) ?></dd>
                    </div>
                    <div>
                        <dt class="font-bold text-slate-500">Dikirim</dt>
                        <dd class="mt-1 text-slate-700"><?= esc($application['created_at'] ?? '-') ?></dd>
                    </div>
                    <?php if (($application['reason'] ?? '') !== ''): ?>
                        <div>
                            <dt class="font-bold text-slate-500">Alasan</dt>
                            <dd class="mt-1 text-slate-700"><?= esc($application['reason']) ?></dd>
                        </div>
                    <?php endif ?>
                </dl>
            <?php endif ?>
        </aside>
    </main>
</body>
</html>
