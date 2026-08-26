<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ada Acara - Sedang Dalam Perbaikan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="relative grid min-h-screen place-items-center overflow-hidden px-5 py-12">
        <section class="relative w-full max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12">
            <div class="mx-auto mb-7 grid h-16 w-16 place-items-center rounded-2xl bg-teal-700 text-2xl font-bold text-white">A</div>
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Ada Acara</p>
            <h1 class="text-3xl font-semibold tracking-tight sm:text-5xl">Sedang dalam perbaikan</h1>
            <p class="mx-auto mt-5 max-w-xl text-sm leading-7 text-slate-600 sm:text-base">
                Kami sedang merapikan tampilan dan pengalaman penggunaan. Halaman utama akan segera kembali dengan versi yang lebih baik.
            </p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="<?= site_url('login') ?>" class="inline-flex h-11 items-center justify-center rounded-xl bg-teal-700 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800">Login</a>
                <a href="<?= site_url('register') ?>" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-900 transition hover:border-teal-700 hover:text-teal-700">Daftar</a>
            </div>
        </section>
    </main>
</body>
</html>
