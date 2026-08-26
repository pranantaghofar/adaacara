<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Ada Acara') ?></title>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1fr_520px]">
        <section class="hidden border-r border-slate-200 bg-white lg:block">
            <div class="flex h-full flex-col justify-between p-12">
                <a href="<?= site_url('/') ?>" class="inline-flex w-fit items-center gap-3 text-lg font-semibold tracking-tight">
                    <img class="h-12 w-auto object-contain" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                </a>

                <div class="max-w-md">
                    <p class="mb-5 text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Event page builder</p>
                    <h1 class="text-5xl font-semibold leading-tight tracking-tight">Bangun undangan acara yang rapi dan siap dibagikan.</h1>
                    <p class="mt-5 text-base leading-7 text-slate-600">Kelola template, tamu, RSVP, dan halaman event dari satu dashboard yang ringan.</p>
                </div>

                <p class="text-sm text-slate-500">adaacara.com</p>
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <a href="<?= site_url('/') ?>" class="mb-9 inline-flex items-center gap-3 text-lg font-semibold tracking-tight text-slate-900 lg:hidden">
                    <img class="h-12 w-auto object-contain" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                </a>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <?= $this->renderSection('content') ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
