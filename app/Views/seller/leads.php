<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lead Inbox - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-slate-50 text-slate-950 antialiased">
    <main class="mx-auto max-w-[1850px] px-4 py-8 sm:px-6">
        <nav class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-amber-700">
                    <a class="no-underline transition hover:text-amber-900" href="<?= site_url('seller') ?>">Seller Tools</a>
                    <span aria-hidden="true">&gt;</span>
                    <span>Lead Inbox</span>
                </p>
                <h1 class="text-3xl font-black tracking-tight">Lead Inbox</h1>
                <p class="mt-1 text-sm text-slate-600">Tambah lead, follow-up, dan ubah status pipeline dari satu tempat.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?= view('components/public_theme_toggle') ?>
                <a class="rounded-2xl border border-amber-100 bg-white px-4 py-2 text-sm font-bold" href="<?= site_url('seller') ?>">Dashboard Penjual</a>
                <a class="rounded-2xl border border-amber-100 bg-white px-4 py-2 text-sm font-bold" href="<?= site_url('seller/whatsapp-templates') ?>">WA Templates</a>
            </div>
        </nav>

        <?php if (session()->getFlashdata('success')): ?><div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700"><?= esc(session()->getFlashdata('success')) ?></div><?php endif ?>
        <?php if (session()->getFlashdata('error')): ?><div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div><?php endif ?>

        <section class="grid gap-5 lg:grid-cols-[.85fr_1.15fr]">
            <form class="rounded-[28px] border border-amber-100 bg-white/90 p-6 shadow-sm" method="post" action="<?= site_url('seller/leads') ?>">
                <?= csrf_field() ?>
                <h2 class="text-xl font-black">Form Order Cepat</h2>
                <div class="mt-5 grid gap-3">
                    <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="customer_name" placeholder="Nama customer" value="<?= esc((string) old('customer_name'), 'attr') ?>" required>
                    <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="whatsapp" placeholder="WhatsApp, contoh 62812..." value="<?= esc((string) old('whatsapp'), 'attr') ?>">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="event_type" placeholder="Jenis acara" value="<?= esc((string) old('event_type'), 'attr') ?>">
                        <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" type="date" name="event_date" value="<?= esc((string) old('event_date'), 'attr') ?>">
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="package_name" placeholder="Paket yang ditawarkan" value="<?= esc((string) old('package_name'), 'attr') ?>">
                        <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="budget" placeholder="Nilai order / budget" value="<?= esc((string) old('budget'), 'attr') ?>">
                    </div>
                    <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="source" placeholder="Sumber lead, contoh IG/WA/Referral" value="<?= esc((string) old('source'), 'attr') ?>">
                    <textarea class="min-h-28 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="notes" placeholder="Catatan kebutuhan customer"><?= esc((string) old('notes')) ?></textarea>
                    <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white" type="submit">Simpan Lead</button>
                </div>
            </form>

            <div class="grid gap-4">
                <?php foreach ($statuses as $statusKey => $statusLabel): ?>
                    <section class="rounded-[24px] border border-amber-100 bg-white/85 p-4 shadow-sm">
                        <h3 class="text-sm font-black uppercase tracking-[0.16em] text-amber-700"><?= esc($statusLabel) ?></h3>
                        <div class="mt-3 grid gap-2">
                            <?php $hasLead = false; ?>
                            <?php foreach ($leads as $lead): ?>
                                <?php if (($lead['status'] ?? 'new') !== $statusKey) continue; $hasLead = true; ?>
                                <a class="rounded-2xl bg-slate-50 p-4 transition hover:bg-amber-50" href="<?= site_url('seller/leads/' . $lead['id']) ?>">
                                    <div class="flex flex-wrap justify-between gap-2">
                                        <strong><?= esc($lead['customer_name']) ?></strong>
                                        <span class="text-xs font-black text-slate-500"><?= $lead['event_date'] ? esc($lead['event_date']) : 'Tanggal belum diisi' ?></span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600"><?= esc($lead['event_type'] ?: '-') ?> · <?= esc($lead['package_name'] ?: '-') ?> · Rp <?= number_format((int) ($lead['budget'] ?? 0), 0, ',', '.') ?></p>
                                </a>
                            <?php endforeach ?>
                            <?php if (! $hasLead): ?><p class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">Belum ada lead di tahap ini.</p><?php endif ?>
                        </div>
                    </section>
                <?php endforeach ?>
            </div>
        </section>
    </main>
</body>
</html>
