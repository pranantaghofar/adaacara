<?php
helper(['seo', 'aa_asset']);

$pageUrl = site_url('fitur/galeri-klien-fotografer/preview');
$featureUrl = site_url('fitur/galeri-klien-fotografer');
$photos = [
    ['Highlight', 'Foto-001.jpg', 'linear-gradient(135deg,#dbeafe,#fff7ed)'],
    ['Ceremony', 'Foto-002.jpg', 'linear-gradient(135deg,#fce7f3,#eef2ff)'],
    ['Reception', 'Foto-003.jpg', 'linear-gradient(135deg,#dcfce7,#f8fafc)'],
    ['Family', 'Foto-004.jpg', 'linear-gradient(135deg,#fef3c7,#f0fdfa)'],
    ['Highlight', 'Foto-005.jpg', 'linear-gradient(135deg,#e0f2fe,#faf5ff)'],
    ['Custom Album', 'Foto-006.jpg', 'linear-gradient(135deg,#f3e8ff,#fff1f2)'],
    ['Ceremony', 'Foto-007.jpg', 'linear-gradient(135deg,#fee2e2,#e0f2fe)'],
    ['Reception', 'Foto-008.jpg', 'linear-gradient(135deg,#e2e8f0,#ffffff)'],
];
$albums = ['Semua', 'Highlight', 'Ceremony', 'Reception', 'Family', 'Custom Album', 'Favorit', 'Untuk Dicetak'];
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->website()
        ->title('Preview Galeri Klien Fotografer AdaAcara')
        ->description('Preview demo Galeri Klien Fotografer AdaAcara dengan PIN, album, favorit, pilihan cetak, dan download demo.')
        ->canonical($pageUrl)
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <style>
        *,*::before,*::after{box-sizing:border-box}body{margin:0;background:linear-gradient(135deg,#faf7ff,#fff7fb 48%,#f7fffb);color:#142033;font-family:"Plus Jakarta Sans",ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}button{font:inherit}
        .pgp{min-height:100vh;padding:28px}.pgp-wrap{width:min(1180px,100%);margin:0 auto}.pgp-top{display:flex;justify-content:space-between;gap:12px;margin-bottom:18px}.pgp-back,.pgp-icon{display:inline-flex;min-height:44px;align-items:center;justify-content:center;border:1px solid rgba(148,163,184,.18);border-radius:999px;background:#fff;padding:0 16px;color:#142033;text-decoration:none;font-size:13px;font-weight:950;box-shadow:0 12px 28px rgba(15,23,42,.06)}.pgp-actions{display:flex;gap:10px}.pgp-icon{width:44px;padding:0}
        .pgp-hero{display:grid;grid-template-columns:240px minmax(0,1fr) 250px;gap:28px;align-items:center;border:1px solid rgba(148,163,184,.14);border-radius:30px;background:rgba(255,255,255,.88);box-shadow:0 24px 70px rgba(79,70,229,.10);padding:28px}.pgp-cover{position:relative;height:240px;border-radius:26px;background:linear-gradient(135deg,#d9c7ff,#fff1f2);overflow:hidden}.pgp-cover img{width:100%;height:100%;object-fit:cover;opacity:.72}.pgp-count{position:absolute;left:14px;bottom:14px;display:grid;min-width:64px;min-height:64px;place-items:center;border-radius:18px;background:#fff;box-shadow:0 12px 28px rgba(15,23,42,.12);font-weight:950}.pgp-count strong{font-size:24px}.pgp-eyebrow{margin:0;color:#8f65df;font-size:12px;font-weight:950;letter-spacing:.16em;text-transform:uppercase}.pgp-title{margin:12px 0 0;font-size:46px;line-height:1.02;font-weight:950;letter-spacing:-.04em}.pgp-meta{margin:12px 0 0;color:#64748b;font-size:14px;font-weight:900}.pgp-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}.pgp-pill{display:inline-flex;min-height:40px;align-items:center;border:1px solid #e2e8f0;border-radius:999px;background:#fff;padding:9px 14px;font-size:12px;font-weight:950;color:#475569}.pgp-private{border:1px dashed rgba(143,101,223,.36);border-radius:20px;background:#fff;padding:18px;color:#64748b;font-size:13px;font-weight:750;line-height:1.55}.pgp-private strong{display:block;color:#7c4fe0;margin-bottom:5px}
        .pgp-tabs{display:flex;flex-wrap:wrap;gap:12px;margin:26px 0}.pgp-tab{min-height:46px;border:1px solid rgba(148,163,184,.20);border-radius:999px;background:#fff;padding:0 18px;color:#334155;font-size:13px;font-weight:950;cursor:pointer}.pgp-tab.is-active{border-color:transparent;background:#8f65df;color:#fff;box-shadow:0 14px 30px rgba(143,101,223,.22)}.pgp-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.pgp-card{position:relative;border:1px solid rgba(148,163,184,.14);border-radius:22px;background:#fff;box-shadow:0 16px 34px rgba(15,23,42,.08);overflow:hidden}.pgp-image{display:block;width:100%;aspect-ratio:1.35/1;background:#eef2f7}.pgp-badge{position:absolute;left:12px;top:12px;border-radius:999px;background:#8f65df;color:#fff;padding:7px 10px;font-size:10px;font-weight:950}.pgp-love{position:absolute;right:12px;top:12px;width:36px;height:36px;border:0;border-radius:999px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.14);font-size:18px;cursor:pointer}.pgp-love.is-on{color:#e11d48}.pgp-card-foot{display:grid;grid-template-columns:1fr 1fr 38px;gap:8px;padding:10px}.pgp-card-foot button{min-height:36px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:#7c4fe0;font-size:12px;font-weight:950;cursor:pointer}.pgp-card-foot .pgp-download{border:0;background:#8f65df;color:#fff}
        .pgp-toast{position:fixed;left:50%;bottom:22px;z-index:20;transform:translateX(-50%) translateY(20px);opacity:0;border-radius:999px;background:#142033;color:#fff;padding:12px 16px;font-size:13px;font-weight:900;transition:.2s ease}.pgp-toast.is-show{opacity:1;transform:translateX(-50%) translateY(0)}.pgp-pin{position:fixed;inset:0;z-index:30;display:grid;place-items:center;background:linear-gradient(135deg,#fde7ef,#f4edff 55%,#ffffff);padding:18px}.pgp-pin-card{width:min(100%,520px);border:1px solid rgba(143,101,223,.45);border-radius:32px;background:rgba(255,255,255,.92);box-shadow:0 30px 90px rgba(79,70,229,.18);padding:28px;text-align:center}.pgp-pin-art{width:130px;height:92px;margin:0 auto 16px;border-radius:28px;background:linear-gradient(135deg,#b993ff,#7c4fe0);box-shadow:0 18px 40px rgba(143,101,223,.22);position:relative}.pgp-pin-art::after{position:absolute;left:39px;top:22px;width:52px;height:52px;border:9px solid #ede9fe;border-radius:999px;background:#142033;content:""}.pgp-pin-card h1{margin:0;font-size:34px;font-weight:950;letter-spacing:-.03em}.pgp-pin-card p{margin:10px 0 0;color:#64748b;font-weight:800;line-height:1.6}.pgp-pin-fields{display:grid;grid-template-columns:repeat(4,58px);justify-content:center;gap:10px;margin-top:24px}.pgp-pin-box{width:58px;height:64px;border:2px solid #e4d2ff;border-radius:18px;background:#fff;color:#8f65df;font-size:30px;font-weight:950;text-align:center;outline:none}.pgp-pin-box:focus{border-color:#8f65df;box-shadow:0 0 0 5px rgba(143,101,223,.14)}.pgp-pin-submit{display:inline-flex;min-height:54px;align-items:center;justify-content:center;margin-top:22px;border:0;border-radius:999px;background:#8f65df;color:#fff;padding:0 28px;font-size:15px;font-weight:950;cursor:pointer}.pgp-pin-note{margin-top:12px;color:#7c4fe0;font-size:12px;font-weight:950}.pgp-error{display:none;margin-top:12px;color:#be123c;font-size:13px;font-weight:900}.pgp-error.is-show{display:block}
        @media(max-width:900px){.pgp{padding:0}.pgp-top{padding:16px 18px;margin:0}.pgp-hero{grid-template-columns:112px minmax(0,1fr);border:0;border-radius:0 0 28px 28px;padding:22px 18px}.pgp-cover{height:166px}.pgp-private{grid-column:1/-1}.pgp-title{font-size:30px}.pgp-tabs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:24px 18px}.pgp-tab{min-width:0}.pgp-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:0 18px 28px}.pgp-card-foot{grid-template-columns:1fr 1fr 38px}.pgp-card-foot button{font-size:11px}.pgp-pin-card{border-radius:28px}.pgp-pin-card h1{font-size:28px}.pgp-pin-fields{grid-template-columns:repeat(4,52px);gap:8px}.pgp-pin-box{width:52px;height:58px}}
    </style>
</head>
<body>
    <div class="pgp-pin" data-pin-gate>
        <form class="pgp-pin-card" data-pin-form>
            <div class="pgp-pin-art" aria-hidden="true"></div>
            <h1>Private Gallery Demo</h1>
            <p>Masukkan PIN demo untuk membuka preview Galeri Klien Fotografer.</p>
            <div class="pgp-pin-fields">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <input class="pgp-pin-box" type="text" inputmode="numeric" maxlength="1" placeholder="&bull;" aria-label="Digit PIN <?= $i + 1 ?>" data-pin-box>
                <?php endfor; ?>
            </div>
            <button class="pgp-pin-submit" type="submit">Buka Preview</button>
            <div class="pgp-pin-note">*gunakan PIN : 1234</div>
            <div class="pgp-error" data-pin-error>PIN demo belum sesuai.</div>
        </form>
    </div>
    <main class="pgp" aria-label="Preview Galeri Klien Fotografer">
        <div class="pgp-wrap">
            <div class="pgp-top">
                <a class="pgp-back" href="<?= esc($featureUrl, 'attr') ?>">Kembali ke fitur</a>
                <div class="pgp-actions"><button class="pgp-icon" type="button" data-demo-toast="Link demo disalin.">↗</button><button class="pgp-icon" type="button" data-demo-toast="Menu demo.">⋮</button></div>
            </div>
            <section class="pgp-hero">
                <div class="pgp-cover"><img src="<?= esc(aa_asset_url('assets/img/adaacara-design-studio-preview.png'), 'attr') ?>" alt=""><span class="pgp-count"><strong>8</strong>FOTO</span></div>
                <div>
                    <p class="pgp-eyebrow">Client Photo Gallery</p>
                    <h1 class="pgp-title">Pernikahan Dimas & Anggi</h1>
                    <p class="pgp-meta">Studio AdaAcara · 29 Agu 2026</p>
                    <div class="pgp-pills"><span class="pgp-pill">♡ <span data-fav-count>0</span> favorit</span><span class="pgp-pill"><span data-print-count>0</span> / 30 dipilih</span><span class="pgp-pill">Download aktif</span></div>
                </div>
                <aside class="pgp-private"><strong>Gallery private</strong>Contoh halaman ini memakai PIN demo. Di produk asli, fotografer mengatur PIN per project atau per halaman keluarga.</aside>
            </section>
            <nav class="pgp-tabs" aria-label="Album demo">
                <?php foreach ($albums as $index => $album): ?>
                    <button class="pgp-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" data-demo-tab="<?= esc($album, 'attr') ?>"><?= esc($album) ?></button>
                <?php endforeach; ?>
            </nav>
            <section class="pgp-grid" data-demo-grid>
                <?php foreach ($photos as $photo): ?>
                    <article class="pgp-card" data-demo-card data-album="<?= esc($photo[0], 'attr') ?>">
                        <span class="pgp-badge"><?= esc($photo[0]) ?></span>
                        <button class="pgp-love" type="button" aria-label="Favorit" data-demo-fav>♡</button>
                        <span class="pgp-image" style="background:<?= esc($photo[2], 'attr') ?>"></span>
                        <div class="pgp-card-foot">
                            <button type="button" data-demo-fav>Favorit</button>
                            <button type="button" data-demo-print>Cetak</button>
                            <button class="pgp-download" type="button" data-demo-toast="Download demo membutuhkan PIN di produk asli.">↓</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </div>
    </main>
    <div class="pgp-toast" data-toast></div>
    <script>
    (() => {
        const gate = document.querySelector('[data-pin-gate]');
        const form = document.querySelector('[data-pin-form]');
        const boxes = Array.from(document.querySelectorAll('[data-pin-box]'));
        const error = document.querySelector('[data-pin-error]');
        const toastEl = document.querySelector('[data-toast]');
        const cards = () => Array.from(document.querySelectorAll('[data-demo-card]'));
        const showToast = (message) => {
            if (!toastEl) return;
            toastEl.textContent = message;
            toastEl.classList.add('is-show');
            window.clearTimeout(showToast.timer);
            showToast.timer = window.setTimeout(() => toastEl.classList.remove('is-show'), 1900);
        };
        const pinValue = () => boxes.map((box) => box.value.trim()).join('');
        boxes.forEach((box, index) => {
            box.addEventListener('input', () => {
                box.value = box.value.replace(/\D/g, '').slice(0, 1);
                if (box.value && boxes[index + 1]) boxes[index + 1].focus();
            });
            box.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && !box.value && boxes[index - 1]) boxes[index - 1].focus();
            });
            box.addEventListener('paste', (event) => {
                const text = (event.clipboardData || window.clipboardData)?.getData('text') || '';
                const digits = text.replace(/\D/g, '').slice(0, 4).split('');
                if (digits.length === 0) return;
                event.preventDefault();
                boxes.forEach((target, targetIndex) => target.value = digits[targetIndex] || '');
                boxes[Math.min(digits.length, 4) - 1]?.focus();
            });
        });
        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            if (pinValue() !== '1234') {
                error?.classList.add('is-show');
                boxes[0]?.focus();
                return;
            }
            error?.classList.remove('is-show');
            gate?.remove();
            showToast('Preview terbuka.');
        });
        document.querySelectorAll('[data-demo-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                const label = tab.dataset.demoTab || 'Semua';
                document.querySelectorAll('[data-demo-tab]').forEach((item) => item.classList.toggle('is-active', item === tab));
                cards().forEach((card) => {
                    const show = label === 'Semua' || label === 'Favorit'
                        ? (label === 'Semua' || card.dataset.favorite === '1')
                            : label === 'Untuk Dicetak'
                            ? card.dataset.printed === '1'
                            : card.dataset.album === label;
                    card.hidden = !show;
                });
            });
        });
        document.addEventListener('click', (event) => {
            const toastButton = event.target.closest('[data-demo-toast]');
            if (toastButton) showToast(toastButton.dataset.demoToast || 'Demo');
            const favButton = event.target.closest('[data-demo-fav]');
            const printButton = event.target.closest('[data-demo-print]');
            if (!favButton && !printButton) return;
            const card = (favButton || printButton).closest('[data-demo-card]');
            if (!card) return;
            if (favButton) {
                const favorite = card.dataset.favorite !== '1';
                card.dataset.favorite = favorite ? '1' : '0';
                card.querySelector('.pgp-love')?.classList.toggle('is-on', favorite);
                card.querySelector('.pgp-love').textContent = favorite ? '♥' : '♡';
                document.querySelector('[data-fav-count]').textContent = String(cards().filter((item) => item.dataset.favorite === '1').length);
                showToast(favorite ? 'Foto masuk favorit.' : 'Foto dihapus dari favorit.');
                return;
            }
            const printed = card.dataset.printed !== '1';
            card.dataset.printed = printed ? '1' : '0';
            printButton.textContent = printed ? 'Dipilih' : 'Cetak';
            document.querySelector('[data-print-count]').textContent = String(cards().filter((item) => item.dataset.printed === '1').length);
            showToast(printed ? 'Foto demo masuk daftar cetak.' : 'Foto demo dihapus dari daftar cetak.');
        });
        boxes[0]?.focus();
    })();
    </script>
</body>
</html>
