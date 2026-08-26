<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IndexNow - Admin</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui min-h-screen bg-[#eef8f5] text-slate-950 antialiased">
    <?= view('admin/partials/header', ['adminTitle' => 'IndexNow', 'adminIcon' => 'search', 'adminActive' => 'indexnow']) ?>

    <main class="mx-auto grid max-w-5xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[1fr_360px]">
        <section class="rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Bing IndexNow</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">Submit URL Aman</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">Kirim URL publik utama AdaAcara ke IndexNow. URL private seperti admin, editor, dashboard, /u/, dan templates preview otomatis ditolak.</p>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif ?>

            <form class="mt-6 grid gap-4" method="post" action="<?= site_url('admin/indexnow/submit') ?>">
                <?= csrf_field() ?>
                <label class="grid gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                    URL yang dikirim
                    <textarea class="min-h-48 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold normal-case tracking-normal outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" name="urls" spellcheck="false"><?= esc(old('urls', $defaultUrls ?? '')) ?></textarea>
                    <span class="text-[11px] font-semibold normal-case tracking-normal text-slate-500">Satu URL per baris. Maksimal 100 URL per submit.</span>
                </label>

                <button class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-lg shadow-emerald-700/20" type="submit">Kirim ke IndexNow</button>
            </form>

            <?php $result = session()->getFlashdata('indexnow_result'); ?>
            <?php if (is_array($result)): ?>
                <section class="mt-6 rounded-3xl border border-slate-100 bg-slate-50 p-4">
                    <h3 class="text-sm font-black text-slate-900">Hasil terakhir</h3>
                    <p class="mt-2 text-sm font-bold text-slate-600">HTTP Status: <?= esc((string) ($result['status'] ?? 0)) ?></p>
                    <p class="mt-1 text-sm font-bold text-slate-600">Dikirim: <?= esc((string) count((array) ($result['submitted'] ?? []))) ?> URL</p>
                    <p class="mt-1 text-sm font-bold text-slate-600">Ditolak lokal: <?= esc((string) count((array) ($result['rejected'] ?? []))) ?> URL</p>
                    <?php if (! empty($result['rejected'])): ?>
                        <div class="mt-3 rounded-2xl bg-white p-3 text-xs font-bold text-rose-700">
                            <?php foreach ((array) $result['rejected'] as $url): ?>
                                <div class="break-all"><?= esc((string) $url) ?></div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                </section>
            <?php endif ?>
        </section>

        <aside class="h-fit rounded-[28px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[.18em] text-emerald-700">Verifikasi</p>
            <h2 class="mt-1 text-xl font-black tracking-tight">IndexNow Key</h2>
            <dl class="mt-4 grid gap-4 text-sm">
                <div>
                    <dt class="font-black text-slate-500">Key</dt>
                    <dd class="mt-1 break-all rounded-2xl bg-slate-50 p-3 font-bold text-slate-800"><?= esc($key ?? '') ?></dd>
                </div>
                <div>
                    <dt class="font-black text-slate-500">Key Location</dt>
                    <dd class="mt-1 break-all rounded-2xl bg-slate-50 p-3 font-bold text-slate-800"><?= esc($keyLocation ?? '') ?></dd>
                </div>
            </dl>
        </aside>
    </main>
</body>
</html>
