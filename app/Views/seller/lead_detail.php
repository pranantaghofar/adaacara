<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Detail - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="aa-app-ui aa-dashboard-theme-page min-h-screen bg-slate-50 text-slate-950 antialiased">
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a class="text-sm font-black text-amber-700" href="<?= site_url('seller/leads') ?>">Kembali ke Lead Inbox</a>
            <?= view('components/public_theme_toggle') ?>
        </div>
        <div class="mt-4 grid gap-5 lg:grid-cols-[1fr_.8fr]">
            <form class="rounded-[28px] border border-amber-100 bg-white/90 p-6 shadow-sm" method="post" action="<?= site_url('seller/leads/' . $lead['id']) ?>">
                <?= csrf_field() ?>
                <h1 class="text-2xl font-black">Customer Detail</h1>
                <div class="mt-5 grid gap-3">
                    <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="customer_name" value="<?= esc($lead['customer_name'], 'attr') ?>" required>
                    <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="whatsapp" value="<?= esc((string) ($lead['whatsapp'] ?? ''), 'attr') ?>">
                    <select class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="status">
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= esc($key, 'attr') ?>" <?= ($lead['status'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach ?>
                    </select>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="event_type" value="<?= esc((string) ($lead['event_type'] ?? ''), 'attr') ?>">
                        <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" type="date" name="event_date" value="<?= esc((string) ($lead['event_date'] ?? ''), 'attr') ?>">
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="package_name" value="<?= esc((string) ($lead['package_name'] ?? ''), 'attr') ?>">
                        <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="budget" value="<?= esc((string) ($lead['budget'] ?? 0), 'attr') ?>">
                    </div>
                    <input class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="source" value="<?= esc((string) ($lead['source'] ?? ''), 'attr') ?>">
                    <textarea class="min-h-32 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold" name="notes"><?= esc((string) ($lead['notes'] ?? '')) ?></textarea>
                    <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white" type="submit">Update Customer</button>
                </div>
            </form>
            <aside class="rounded-[28px] border border-amber-100 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-black">Follow-up WhatsApp</h2>
                <div class="mt-4 grid gap-3">
                    <?php foreach ($whatsappTemplates as $template): ?>
                        <?php $wa = preg_replace('/\D+/', '', (string) ($lead['whatsapp'] ?? '')); ?>
                        <a class="rounded-2xl border border-amber-100 bg-slate-50 p-4 text-sm transition hover:border-amber-600" href="<?= $wa ? 'https://wa.me/' . esc($wa, 'attr') . '?text=' . rawurlencode($template['body']) : '#' ?>" target="_blank" rel="noopener">
                            <strong><?= esc($template['title']) ?></strong>
                            <p class="mt-2 leading-6 text-slate-600"><?= esc($template['body']) ?></p>
                        </a>
                    <?php endforeach ?>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
