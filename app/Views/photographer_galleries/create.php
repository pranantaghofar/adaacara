<?php
    helper(['url', 'form', 'aa_asset']);
    $title = $title ?? 'Buat Photographer Gallery';
    $errors = session('errors') ?? [];
    $flatErrors = is_array($errors) ? array_values(array_filter(array_map('strval', $errors))) : [];
    $flashError = session('error');
    $oldPin = preg_replace('/\D+/', '', (string) old('pin', ''));
    $icon = static function (string $name, string $class = 'h-5 w-5'): string {
        $icons = [
            'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
            'camera' => '<path d="M4 8a2 2 0 0 1 2-2h2l1.5-2h5L16 6h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z"/><circle cx="12" cy="13" r="4"/>',
            'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
            'download' => '<path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 21h16"/>',
        ];

        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['camera']) . '</svg>';
    };
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> - AdaAcara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *,*::before,*::after{box-sizing:border-box}
        html{min-height:100%}
        body{margin:0;min-height:100vh;overflow-x:hidden}
        a{color:inherit;text-decoration:none}
        button,input,select,textarea{font:inherit}
        .pg-page svg{display:block;flex:0 0 auto;width:20px;height:20px}
        .pg-page .h-4{width:16px;height:16px}.pg-page .w-4{width:16px}.pg-page .h-5{width:20px;height:20px}.pg-page .w-5{width:20px}
        .pg-page .mt-2{margin-top:.5rem}.pg-page .mt-3{margin-top:.75rem}.pg-page .mt-4{margin-top:1rem}.pg-page .mt-5{margin-top:1.25rem}.pg-page .mt-7{margin-top:1.75rem}
        .pg-page .flex{display:flex}.pg-page .grid{display:grid}.pg-page .block{display:block}.pg-page .items-center{align-items:center}.pg-page .gap-3{gap:.75rem}.pg-page .gap-5{gap:1.25rem}
        .pg-page .w-full{width:100%}.pg-page .justify-self-start{justify-self:start}.pg-page .rounded-2xl{border-radius:1rem}.pg-page .border{border-width:1px;border-style:solid}.pg-page .border-dashed{border-style:dashed}.pg-page .border-slate-300{border-color:#cbd5e1}.pg-page .bg-white\/80{background:rgba(255,255,255,.8)}.pg-page .p-4{padding:1rem}.pg-page .text-sm{font-size:14px}.pg-page .font-bold{font-weight:700}.pg-page .text-slate-600{color:#475569}
        .pg-page{min-height:100vh;background:linear-gradient(135deg,#f8fbff 0%,#fff7fb 48%,#f4fffb 100%);padding:24px;color:#172033}
        .pg-form-shell{width:min(100%,1040px);margin:0 auto}
        .pg-card{border:1px solid rgba(148,163,184,.22);background:rgba(255,255,255,.86);box-shadow:0 18px 50px rgba(79,70,229,.08)}
        .pg-create-card{overflow:hidden;border-radius:30px}
        .pg-create-head{border-bottom:1px solid rgba(221,214,254,.72);background:linear-gradient(135deg,rgba(143,101,223,.10),rgba(20,184,166,.08));padding:28px 30px}
        .pg-create-body{padding:28px 30px}
        .pg-create-title{margin:8px 0 0;font-size:32px;line-height:1.1;font-weight:950;letter-spacing:-.02em;color:#142033}
        .pg-create-copy{margin:10px 0 0;max-width:680px;font-size:14px;font-weight:700;line-height:1.65;color:#5b6880}
        .pg-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
        .pg-section-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .pg-option-card{border:1px solid rgba(148,163,184,.22);border-radius:24px;background:rgba(255,255,255,.72);padding:18px}
        .pg-input{height:48px;width:100%;border-radius:16px;border:1px solid rgba(148,163,184,.28);background:rgba(255,255,255,.9);padding:0 16px;font-size:14px;font-weight:700;color:#172033;outline:none}
        .pg-input:focus{border-color:#8f65df;box-shadow:0 0 0 4px rgba(143,101,223,.12)}
        .pg-pin-field{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
        .pg-pin-boxes{display:grid;grid-template-columns:repeat(4,48px);gap:10px}
        .pg-pin-box{width:48px;height:52px;border:1px solid rgba(143,101,223,.28);border-radius:16px;background:rgba(255,255,255,.92);font-size:24px;font-weight:950;text-align:center;color:#8f65df;outline:none}
        .pg-pin-box:focus{border-color:#8f65df;box-shadow:0 0 0 4px rgba(143,101,223,.12)}
        .pg-pin-hint{margin-top:8px;font-size:11px;font-weight:800;color:#7b879c}
        .pg-label{font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.14em;color:#64748b}
        .pg-btn{display:inline-flex;min-height:44px;align-items:center;justify-content:center;gap:10px;border-radius:16px;padding:10px 18px;font-size:14px;font-weight:900;line-height:1.15;text-decoration:none;transition:.18s ease}
        .pg-btn-primary{background:#8f65df;color:white;box-shadow:0 14px 32px rgba(143,101,223,.22)}
        .pg-btn-muted{border:1px solid rgba(148,163,184,.28);background:rgba(255,255,255,.74);color:#46556f}
        html[data-aa-public-theme="dark"] .pg-page{background:linear-gradient(135deg,#08111f 0%,#17111d 52%,#071b17 100%);color:#edf2f7}
        html[data-aa-public-theme="dark"] .pg-card{border-color:rgba(148,163,184,.18);background:rgba(15,23,42,.82)}
        html[data-aa-public-theme="dark"] .pg-create-title{color:#edf2f7}
        html[data-aa-public-theme="dark"] .pg-option-card{border-color:rgba(148,163,184,.18);background:rgba(255,255,255,.06)}
        html[data-aa-public-theme="dark"] .pg-input{border-color:rgba(148,163,184,.24);background:rgba(15,23,42,.7);color:#edf2f7}
        @media(max-width:820px){.pg-page{padding:16px}.pg-form-grid,.pg-section-grid{grid-template-columns:1fr}.pg-create-head,.pg-create-body{padding:22px}}
    </style>
</head>
<body class="aa-app-ui aa-dashboard-theme-page aa-dashboard-pastel antialiased">
<main class="pg-page">
    <div class="pg-form-shell">
        <a class="pg-btn pg-btn-muted" href="<?= site_url('photographer-galleries') ?>"><?= $icon('arrow-left', 'h-4 w-4') ?>Kembali</a>
        <section class="pg-card pg-create-card">
            <div class="pg-create-head">
                <p class="pg-label" style="color:#7c4fd3">Create Gallery</p>
                <h1 class="pg-create-title">Buat project foto klien.</h1>
                <p class="pg-create-copy">Isi identitas gallery dulu, setelah itu kamu akan masuk ke halaman upload.</p>
            </div>
            <div class="pg-create-body">

            <?php if ($errors !== []): ?>
                <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                    <?php foreach ($errors as $error): ?>
                        <p><?= esc($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="mt-7 grid gap-5" action="<?= site_url('photographer-galleries') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="pg-form-grid">
                    <label>
                        <span class="pg-label">Nama Project</span>
                        <input class="pg-input mt-2" name="title" value="<?= esc(old('title', ''), 'attr') ?>" required placeholder="The Wedding of Dimas & Anggi">
                    </label>
                    <label>
                        <span class="pg-label">Tanggal</span>
                        <input class="pg-input mt-2" type="date" name="event_date" value="<?= esc(old('event_date', ''), 'attr') ?>">
                    </label>
                    <label>
                        <span class="pg-label">Nama Photographer / Studio</span>
                        <input class="pg-input mt-2" name="studio_name" value="<?= esc(old('studio_name', ''), 'attr') ?>" placeholder="Studio ABC">
                    </label>
                    <label>
                        <span class="pg-label">Slug Link</span>
                        <input class="pg-input mt-2" name="slug" value="<?= esc(old('slug', ''), 'attr') ?>" placeholder="rina-budi">
                    </label>
                </div>

                <label>
                    <span class="pg-label">Cover Photo</span>
                    <input class="mt-2 block w-full rounded-2xl border border-dashed border-slate-300 bg-white/80 p-4 text-sm font-bold text-slate-600" type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp">
                </label>

                <div class="pg-section-grid">
                    <div class="pg-option-card">
                        <span class="pg-label">Privacy</span>
                        <label class="mt-4 flex items-center gap-3 text-sm font-black"><input type="radio" name="privacy_mode" value="public" <?= old('privacy_mode') === 'public' ? 'checked' : '' ?>>Public</label>
                        <label class="mt-3 flex items-center gap-3 text-sm font-black"><input type="radio" name="privacy_mode" value="pin" <?= old('privacy_mode', 'pin') === 'pin' ? 'checked' : '' ?>>PIN Protected</label>
                        <div class="mt-4 flex items-center gap-3">
                            <?= $icon('lock', 'h-4 w-4 text-slate-500') ?>
                            <input class="pg-pin-field" name="pin" value="<?= esc($oldPin, 'attr') ?>" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" data-admin-pin-hidden>
                            <div class="pg-pin-boxes" data-admin-pin-boxes>
                                <?php for ($pinIndex = 0; $pinIndex < 4; $pinIndex++): ?>
                                    <input class="pg-pin-box" type="text" inputmode="numeric" maxlength="1" value="<?= esc($oldPin[$pinIndex] ?? '', 'attr') ?>" aria-label="Digit PIN <?= $pinIndex + 1 ?>" data-admin-pin-box>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="pg-pin-hint">Gunakan tepat 4 digit angka untuk PIN client.</p>
                    </div>
                    <div class="pg-option-card">
                        <span class="pg-label">Akses Klien</span>
                        <label class="mt-4 flex items-center gap-3 text-sm font-black"><input type="checkbox" name="selection_enabled" value="1" <?= old('selection_enabled', '1') ? 'checked' : '' ?>><?= $icon('heart', 'h-4 w-4 text-rose-500') ?>Izinkan client memilih foto</label>
                        <label class="mt-4 block">
                            <span class="text-xs font-black text-slate-500">Maximum pilihan</span>
                            <input class="pg-input mt-2" type="number" min="1" max="500" name="selection_limit" value="<?= esc(old('selection_limit', '30'), 'attr') ?>">
                        </label>
                        <label class="mt-4 flex items-center gap-3 text-sm font-black"><input type="checkbox" name="download_enabled" value="1" <?= old('download_enabled') ? 'checked' : '' ?>><?= $icon('download', 'h-4 w-4 text-emerald-600') ?>Izinkan download</label>
                    </div>
                </div>

                <button class="pg-btn pg-btn-primary justify-self-start" type="submit"><?= $icon('camera', 'h-4 w-4') ?>Create Gallery</button>
            </form>
            </div>
        </section>
    </div>
</main>
<script>
(function () {
    const flashError = <?= json_encode($flashError ?: '') ?>;
    const firstError = <?= json_encode($flatErrors[0] ?? '') ?>;
    const message = flashError || firstError;
    if (message && typeof window.aaToast === 'function') {
        window.aaToast(message, 'error');
    }
    const hidden = document.querySelector('[data-admin-pin-hidden]');
    const boxes = Array.from(document.querySelectorAll('[data-admin-pin-box]'));
    if (!hidden || boxes.length === 0) return;
    const syncPin = () => {
        hidden.value = boxes.map((box) => box.value.trim()).join('');
    };
    boxes.forEach((box, index) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncPin();
            if (box.value && boxes[index + 1]) boxes[index + 1].focus();
        });
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !box.value && boxes[index - 1]) boxes[index - 1].focus();
        });
        box.addEventListener('paste', (event) => {
            const pasted = (event.clipboardData || window.clipboardData)?.getData('text') || '';
            const digits = pasted.replace(/\D/g, '').slice(0, boxes.length).split('');
            if (digits.length === 0) return;
            event.preventDefault();
            boxes.forEach((target, targetIndex) => {
                target.value = digits[targetIndex] || '';
            });
            syncPin();
            boxes[Math.min(digits.length, boxes.length) - 1]?.focus();
        });
    });
    syncPin();
})();
</script>
</body>
</html>
