<?php
    helper(['url', 'aa_asset']);
    $gallery = is_array($gallery ?? null) ? $gallery : [];
    $share = is_array($share ?? null) ? $share : [];
    $photos = is_array($photos ?? null) ? $photos : [];
    $albums = is_array($albums ?? null) ? $albums : [];
    $hasAccess = ! empty($hasAccess);
    $accessError = (string) ($accessError ?? '');
    $cover = trim((string) ($gallery['cover_photo'] ?? ''));
    $coverUrl = $cover !== '' ? base_url($cover) : '';
    $gallerySlug = (string) ($gallery['slug'] ?? '');
    $shareToken = (string) ($share['share_token'] ?? '');
    $eventDateText = ! empty($gallery['event_date']) ? date('d M Y', strtotime((string) $gallery['event_date'])) : '';
    $albumNames = [];
    foreach ($albums as $album) {
        $albumId = (int) ($album['id'] ?? 0);
        if ($albumId > 0) {
            $albumNames[$albumId] = (string) ($album['name'] ?? 'Album');
        }
    }
    $icon = static function (string $name, string $class = 'h-5 w-5'): string {
        $icons = [
            'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'image' => '<rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 16-5-5L5 19"/>',
            'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.6 6.8-4.2M8.6 13.4l6.8 4.2"/>',
            'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/>',
            'download' => '<path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 21h16"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        ];

        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['image']) . '</svg>';
    };
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc((string) ($gallery['title'] ?? 'Family Gallery')) ?> - Family Gallery</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{margin:0;min-height:100vh;background:linear-gradient(135deg,#faf7ff 0%,#fff7fb 48%,#f7fffb 100%);color:#142033;font-family:"Plus Jakarta Sans",ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        button,input{font:inherit}
        .fg svg{display:block;flex:0 0 auto;width:20px;height:20px}.fg .h-4{width:16px;height:16px}.fg .w-4{width:16px}.fg .h-5{width:20px;height:20px}.fg .w-5{width:20px}.fg .h-8{width:32px;height:32px}.fg .w-8{width:32px}
        .fg{min-height:100vh;padding:28px}
        .fg-wrap{width:min(100%,1180px);margin:0 auto}
        .fg-hero{display:grid;grid-template-columns:220px minmax(0,1fr);gap:28px;align-items:center;overflow:hidden;border:1px solid rgba(148,163,184,.16);border-radius:30px;background:rgba(255,255,255,.88);box-shadow:0 24px 70px rgba(79,70,229,.10);padding:28px}
        .fg-cover{height:230px;overflow:hidden;border-radius:24px;background:linear-gradient(135deg,#f5f3ff,#fff,#fff1f2);box-shadow:0 18px 40px rgba(15,23,42,.12)}
        .fg-cover img{display:block;width:100%;height:100%;object-fit:cover}
        .fg-cover-empty{display:grid;height:100%;place-items:center;color:#a78bfa}
        .fg-eyebrow{display:inline-flex;align-items:center;gap:8px;margin:0;font-size:12px;font-weight:950;letter-spacing:.16em;text-transform:uppercase;color:#8f65df}
        .fg-title{margin:12px 0 0;font-size:42px;line-height:1.08;font-weight:950;letter-spacing:-.02em}
        .fg-meta{margin:14px 0 0;font-size:14px;font-weight:900;color:#64748b}
        .fg-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px}
        .fg-pill{display:inline-flex;min-height:42px;align-items:center;justify-content:center;gap:8px;border:1px solid rgba(148,163,184,.20);border-radius:999px;background:#fff;padding:9px 14px;font-size:12px;font-weight:950;color:#475569}
        .fg-tabs{display:flex;flex-wrap:wrap;gap:12px;margin-top:26px}
        .fg-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:24px}
        .fg-search{position:relative;flex:1;max-width:420px}
        .fg-search svg{position:absolute;left:14px;top:50%;color:#8f65df;transform:translateY(-50%)}
        .fg-search input{width:100%;height:46px;border:1px solid rgba(148,163,184,.24);border-radius:999px;background:rgba(255,255,255,.92);box-shadow:0 12px 28px rgba(15,23,42,.05);padding:0 16px 0 44px;font-size:13px;font-weight:850;color:#142033;outline:none}
        .fg-search input:focus{border-color:#8f65df;box-shadow:0 0 0 5px rgba(143,101,223,.12)}
        .fg-tab{display:inline-flex;min-height:44px;align-items:center;justify-content:center;border:1px solid rgba(148,163,184,.22);border-radius:999px;background:#fff;padding:10px 16px;font-size:13px;font-weight:950;color:#475569;cursor:pointer}
        .fg-tab.is-active{border-color:#8f65df;background:#8f65df;color:#fff;box-shadow:0 12px 26px rgba(143,101,223,.18)}
        .fg-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:24px}
        .fg-photo{position:relative;overflow:hidden;border:1px solid rgba(148,163,184,.14);border-radius:22px;background:#fff;box-shadow:0 16px 36px rgba(15,23,42,.08)}
        .fg-photo.is-hidden,.fg-photo.is-paginated-hidden{display:none}
        .fg-photo img{display:block;width:100%;aspect-ratio:1.25/1;object-fit:cover;background:#eef2f7;cursor:zoom-in}
        .fg-badge{position:absolute;left:10px;top:10px;max-width:calc(100% - 20px);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;border-radius:999px;background:linear-gradient(135deg,#9d6af2,#764dd8);padding:7px 10px;font-size:10px;font-weight:950;color:#fff}
        .fg-photo-actions{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px}
        .fg-caption{min-width:0;font-size:12px;font-weight:900;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .fg-action-btn{display:inline-flex;width:38px;height:38px;align-items:center;justify-content:center;border:0;border-radius:14px;background:linear-gradient(135deg,#9d6af2,#764dd8);box-shadow:0 12px 24px rgba(143,101,223,.22);color:#fff;cursor:pointer}
        .fg-action-btn svg{width:17px;height:17px}
        .fg [hidden]{display:none!important}
        .fg-empty{display:grid;min-height:280px;place-items:center;margin-top:24px;border:1px dashed rgba(148,163,184,.34);border-radius:28px;background:rgba(255,255,255,.68);text-align:center;color:#64748b}
        .fg-grid,.fg-pagination{opacity:1;transition:opacity .16s ease,transform .16s ease}
        .fg-grid.is-switching,.fg-pagination.is-switching{opacity:.48;transform:translateY(4px)}
        .fg-pagination{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:26px;border:1px solid rgba(148,163,184,.12);border-radius:20px;background:rgba(255,255,255,.90);box-shadow:0 18px 42px rgba(15,23,42,.06);padding:16px 20px}
        .fg-pagination-info{font-size:13px;font-weight:900;color:#64748b}
        .fg-pagination-actions{display:flex;align-items:center;gap:8px}
        .fg-page-number{display:inline-flex;min-width:74px;height:44px;align-items:center;justify-content:center;border:1px solid rgba(148,163,184,.18);border-radius:999px;background:#f5f3ff;font-size:13px;font-weight:950;color:#142033}
        .fg-modal-backdrop,.fg-lightbox-backdrop{position:fixed;inset:0;z-index:60;display:grid;place-items:center;background:rgba(15,23,42,0);backdrop-filter:blur(0);padding:18px;opacity:0;pointer-events:none;visibility:hidden;transition:opacity .2s ease,background-color .2s ease,backdrop-filter .2s ease,visibility 0s linear .2s}
        .fg-modal-backdrop.is-open,.fg-lightbox-backdrop.is-open{background:rgba(15,23,42,.42);backdrop-filter:blur(14px);opacity:1;pointer-events:auto;visibility:visible;transition-delay:0s}
        .fg-modal{width:min(100%,420px);border:1px solid rgba(148,163,184,.22);border-radius:28px;background:#fff;box-shadow:0 26px 80px rgba(15,23,42,.22);padding:24px;text-align:center}
        .fg-modal h2{margin:0;font-size:22px;font-weight:950;color:#142033}.fg-modal p{margin:10px 0 0;font-size:13px;font-weight:800;line-height:1.55;color:#64748b}
        .fg-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}
        .fg-btn-muted{background:#fff;border:1px solid rgba(148,163,184,.24);box-shadow:none;color:#475569}
        .fg-lightbox{position:relative;width:min(100%,980px);display:grid;place-items:center}
        .fg-lightbox img{display:block;max-width:100%;max-height:calc(100vh - 44px);border-radius:24px;background:#fff;box-shadow:0 28px 90px rgba(15,23,42,.34);object-fit:contain}
        .fg-lightbox-close{position:absolute;right:12px;top:12px;z-index:2;width:42px;height:42px;border:0;border-radius:999px;background:rgba(255,255,255,.92);box-shadow:0 12px 30px rgba(15,23,42,.18);font-size:22px;font-weight:900;line-height:1;color:#142033;cursor:pointer}
        .fg-pin{display:grid;min-height:100vh;place-items:center;padding:24px}
        .fg-pin-card{width:min(100%,520px);border:1px solid rgba(167,139,250,.45);border-radius:32px;background:rgba(255,255,255,.92);box-shadow:0 24px 70px rgba(79,70,229,.14);padding:30px;text-align:center}
        .fg-pin-card h1{margin:16px 0 0;font-size:32px;line-height:1.15;font-weight:950;color:#142033}
        .fg-pin-card p{margin:10px 0 0;font-size:14px;font-weight:800;line-height:1.6;color:#64748b}
        .fg-pin-fields{display:grid;grid-template-columns:repeat(4,56px);justify-content:center;gap:10px;margin-top:24px}
        .fg-pin-box{width:56px;height:62px;border:2px solid #e4d2ff;border-radius:18px;background:#fff;font-size:30px;font-weight:950;text-align:center;color:#8f65df;outline:none}
        .fg-pin-box:focus{border-color:#8f65df;box-shadow:0 0 0 5px rgba(143,101,223,.14)}
        .fg-pin-hidden{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
        .fg-btn{display:inline-flex;min-height:50px;align-items:center;justify-content:center;gap:9px;border:0;border-radius:999px;background:#8f65df;box-shadow:0 14px 32px rgba(143,101,223,.22);padding:12px 22px;font-size:14px;font-weight:950;color:#fff;cursor:pointer}
        .fg-error{margin-top:14px;border:1px solid #fecdd3;border-radius:16px;background:#fff1f2;padding:10px 12px;font-size:13px;font-weight:800;color:#be123c}
        @media(max-width:860px){.fg-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.fg-hero{grid-template-columns:180px minmax(0,1fr)}.fg-title{font-size:34px}}
        @media(max-width:680px){.fg{padding:0}.fg-hero{grid-template-columns:112px minmax(0,1fr);gap:16px;border:0;border-radius:0 0 28px 28px;padding:26px 18px}.fg-cover{height:160px;border-radius:22px}.fg-title{font-size:28px}.fg-meta{font-size:13px}.fg-pills{margin-top:14px}.fg-pill{min-height:36px;font-size:11px}.fg-toolbar{display:grid;gap:14px;margin:22px 18px 0}.fg-search{max-width:none}.fg-tabs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:0}.fg-tab{min-width:0}.fg-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:22px 18px 0}.fg-photo{border-radius:20px}.fg-photo img{aspect-ratio:1.12/1}.fg-caption{font-size:11px}.fg-pagination{margin:24px 18px 0;border:0;border-radius:24px;background:#fff;box-shadow:0 16px 36px rgba(15,23,42,.08);padding:16px}.fg-pagination-actions{width:100%;justify-content:space-between}.fg-pagination-actions .fg-btn{width:46px;min-height:42px;padding:0;border-radius:999px;font-size:0}.fg-pagination-actions [data-family-page-prev]::before{content:"<";font-size:18px}.fg-pagination-actions [data-family-page-next]::before{content:">";font-size:18px}.fg-empty{margin:22px 18px 0}.fg-pin-card{border-radius:28px;padding:24px 18px}.fg-pin-card h1{font-size:26px}.fg-pin-fields{grid-template-columns:repeat(4,50px);gap:8px}.fg-pin-box{width:50px;height:56px;font-size:26px}.fg-lightbox-backdrop{padding:14px}.fg-lightbox img{border-radius:18px}.fg-lightbox-close{right:8px;top:8px;width:38px;height:38px}}
        @media(prefers-reduced-motion:reduce){.fg-grid,.fg-pagination,.fg-modal-backdrop,.fg-lightbox-backdrop{transition:none!important}}
    </style>
</head>
<body>
<main class="fg">
    <?php if (! $hasAccess): ?>
        <section class="fg-pin">
            <div class="fg-pin-card">
                <span class="fg-pill" style="color:#8f65df"><?= $icon('lock', 'h-5 w-5') ?>Family Gallery</span>
                <h1><?= esc((string) ($gallery['title'] ?? 'Family Gallery')) ?></h1>
                <p>Masukkan PIN yang dibagikan untuk membuka foto pilihan keluarga.</p>
                <?php if ($accessError !== ''): ?>
                    <div class="fg-error"><?= esc($accessError) ?></div>
                <?php endif; ?>
                <form action="<?= site_url('gallery/' . $gallerySlug . '/family/' . $shareToken) ?>" method="post" data-family-pin-form>
                    <?= csrf_field() ?>
                    <input class="fg-pin-hidden" name="pin" inputmode="numeric" autocomplete="one-time-code" required data-family-pin-hidden>
                    <div class="fg-pin-fields" aria-label="Masukkan PIN">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <input class="fg-pin-box" type="text" inputmode="numeric" maxlength="1" placeholder="&bull;" aria-label="Digit PIN <?= $i + 1 ?>" data-family-pin-box>
                        <?php endfor; ?>
                    </div>
                    <button class="fg-btn" style="margin-top:24px" type="submit"><?= $icon('lock', 'h-5 w-5') ?>Buka Foto</button>
                </form>
            </div>
        </section>
    <?php else: ?>
        <div class="fg-wrap">
            <section class="fg-hero">
                <div class="fg-cover">
                    <?php if ($coverUrl !== ''): ?>
                        <img src="<?= esc($coverUrl, 'attr') ?>" alt="<?= esc((string) ($gallery['title'] ?? 'Family Gallery'), 'attr') ?>">
                    <?php else: ?>
                        <div class="fg-cover-empty"><?= $icon('image', 'h-8 w-8') ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="fg-eyebrow"><?= $icon('share', 'h-4 w-4') ?>Family Photo Gallery</p>
                    <h1 class="fg-title"><?= esc((string) ($gallery['title'] ?? 'Family Gallery')) ?></h1>
                    <p class="fg-meta"><?= esc((string) ($gallery['studio_name'] ?? '')) ?><?= $eventDateText !== '' ? ' · ' . esc($eventDateText) : '' ?></p>
                    <div class="fg-pills">
                        <span class="fg-pill"><?= $icon('image', 'h-4 w-4') ?><?= count($photos) ?> Foto</span>
                        <?php if ($eventDateText !== ''): ?>
                            <span class="fg-pill"><?= $icon('calendar', 'h-4 w-4') ?><?= esc($eventDateText) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <?php if ($photos !== []): ?>
                <div class="fg-toolbar">
                    <div class="fg-search">
                        <?= $icon('search', 'h-4 w-4') ?>
                        <input type="search" placeholder="Cari nama foto atau album" aria-label="Cari foto keluarga" data-family-search>
                    </div>
                    <nav class="fg-tabs" aria-label="Album keluarga">
                        <button class="fg-tab is-active" type="button" data-family-tab="all">Semua</button>
                        <?php foreach ($albums as $album): ?>
                            <?php $albumId = (int) ($album['id'] ?? 0); ?>
                            <button class="fg-tab" type="button" data-family-tab="album:<?= $albumId ?>"><?= esc((string) ($album['name'] ?? 'Album')) ?></button>
                        <?php endforeach; ?>
                    </nav>
                </div>
                <section class="fg-grid" data-family-grid>
                    <?php foreach ($photos as $photo): ?>
                        <?php
                            $photoPath = trim((string) ($photo['file_path'] ?? $photo['thumb_path'] ?? ''));
                            $photoAlbumId = (int) ($photo['album_id'] ?? 0);
                            $albumName = $albumNames[$photoAlbumId] ?? '';
                            $photoName = (string) ($photo['original_name'] ?? 'Foto');
                            $photoId = (int) ($photo['id'] ?? 0);
                            $searchText = trim(strtolower($photoName . ' ' . $albumName));
                        ?>
                        <article class="fg-photo" data-family-photo data-album-id="<?= $photoAlbumId ?>" data-search-text="<?= esc($searchText, 'attr') ?>" data-photo-id="<?= $photoId ?>">
                            <?php if ($albumName !== ''): ?>
                                <span class="fg-badge"><?= esc($albumName) ?></span>
                            <?php endif; ?>
                            <img src="<?= esc(base_url($photoPath), 'attr') ?>" alt="<?= esc($photoName, 'attr') ?>" loading="lazy" data-zoom-photo data-full-src="<?= esc(base_url($photo['file_path'] ?? $photoPath), 'attr') ?>">
                            <div class="fg-photo-actions">
                                <div class="fg-caption"><?= esc($photoName) ?></div>
                                <button class="fg-action-btn" type="button" data-download-family-photo data-photo-id="<?= $photoId ?>" data-photo-title="<?= esc($photoName, 'attr') ?>" data-download-url="<?= esc(site_url('gallery/' . $gallerySlug . '/family/' . $shareToken . '/photos/' . $photoId . '/download'), 'attr') ?>" aria-label="Download foto"><?= $icon('download', 'h-4 w-4') ?></button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
                <div class="fg-pagination" data-family-pagination>
                    <span class="fg-pagination-info" data-family-page-info>Menampilkan foto</span>
                    <div class="fg-pagination-actions">
                        <button class="fg-btn fg-btn-muted" type="button" data-family-page-prev>Sebelumnya</button>
                        <span class="fg-page-number" data-family-page-number>1 / 1</span>
                        <button class="fg-btn fg-btn-muted" type="button" data-family-page-next>Berikutnya</button>
                    </div>
                </div>
                <section class="fg-empty" data-family-empty-search hidden>
                    <div>
                        <?= $icon('search', 'h-8 w-8') ?>
                        <p style="margin:12px 0 0;font-weight:950">Foto tidak ditemukan.</p>
                        <p style="margin:6px 0 0;font-size:13px;font-weight:750">Coba cari nama file atau nama album yang lain.</p>
                    </div>
                </section>
            <?php else: ?>
                <section class="fg-empty">
                    <div>
                        <?= $icon('image', 'h-8 w-8') ?>
                        <p style="margin:12px 0 0;font-weight:950">Belum ada foto keluarga.</p>
                        <p style="margin:6px 0 0;font-size:13px;font-weight:750">Foto akan muncul setelah pilihan untuk disebar dikirim.</p>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
<?php if ($hasAccess): ?>
<div class="fg-lightbox-backdrop" data-family-lightbox aria-hidden="true">
    <div class="fg-lightbox">
        <button class="fg-lightbox-close" type="button" data-close-lightbox aria-label="Tutup preview">⛌</button>
        <img src="" alt="Preview foto keluarga" data-lightbox-image>
    </div>
</div>
<div class="fg-modal-backdrop" data-download-modal aria-hidden="true">
    <div class="fg-modal" role="dialog" aria-modal="true" aria-labelledby="fg-download-title">
        <h2 id="fg-download-title">Download Foto</h2>
        <p>Masukkan PIN keluarga lagi untuk mengunduh foto ini.</p>
        <form method="post" target="_blank" data-download-form>
            <?= csrf_field() ?>
            <input class="fg-pin-hidden" name="download_pin" inputmode="numeric" autocomplete="one-time-code" required data-download-pin-hidden>
            <div class="fg-pin-fields" aria-label="PIN download">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <input class="fg-pin-box" type="text" inputmode="numeric" maxlength="1" placeholder="&bull;" aria-label="Digit PIN download <?= $i + 1 ?>" data-download-pin-box>
                <?php endfor; ?>
            </div>
            <div class="fg-modal-actions">
                <button class="fg-btn fg-btn-muted" type="button" data-close-download-modal>Batal</button>
                <button class="fg-btn" type="submit"><?= $icon('download', 'h-4 w-4') ?>Download</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<script>
(() => {
    const form = document.querySelector('[data-family-pin-form]');
    const hidden = document.querySelector('[data-family-pin-hidden]');
    const boxes = Array.from(document.querySelectorAll('[data-family-pin-box]'));
    if (form && hidden && boxes.length > 0) {
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
        form.addEventListener('submit', syncPin);
        boxes[0]?.focus();
    }

    const familySearch = document.querySelector('[data-family-search]');
    const emptySearch = document.querySelector('[data-family-empty-search]');
    const familyGrid = document.querySelector('[data-family-grid]');
    const familyPagination = document.querySelector('[data-family-pagination]');
    const familyPageInfo = document.querySelector('[data-family-page-info]');
    const familyPageNumber = document.querySelector('[data-family-page-number]');
    const familyPagePrev = document.querySelector('[data-family-page-prev]');
    const familyPageNext = document.querySelector('[data-family-page-next]');
    let activeTab = 'all';
    let familyPage = 1;
    const familyPageSize = 20;
    const normalize = (value) => String(value || '').toLowerCase().trim();
    const familyCards = () => Array.from(document.querySelectorAll('[data-family-photo]'));
    const familyMatchesFilter = (photo) => {
        const query = normalize(familySearch?.value || '');
        const tabHidden = activeTab.startsWith('album:') && photo.dataset.albumId !== activeTab.split(':')[1];
        const searchHidden = query !== '' && !normalize(photo.dataset.searchText || '').includes(query);

        return !tabHidden && !searchHidden;
    };
    const renderFamilyPagination = () => {
        const matched = familyCards().filter(familyMatchesFilter);
        const total = matched.length;
        const totalPages = Math.max(1, Math.ceil(total / familyPageSize));
        familyPage = Math.min(Math.max(1, familyPage), totalPages);
        familyCards().forEach((photo) => {
            const visibleByFilter = familyMatchesFilter(photo);
            const index = matched.indexOf(photo);
            const page = index >= 0 ? Math.floor(index / familyPageSize) + 1 : 0;
            photo.classList.toggle('is-hidden', !visibleByFilter);
            photo.classList.toggle('is-paginated-hidden', visibleByFilter && page !== familyPage);
        });
        if (emptySearch) emptySearch.hidden = total > 0;
        if (familyPagination) familyPagination.style.display = 'flex';
        if (familyPageInfo) {
            const start = total === 0 ? 0 : ((familyPage - 1) * familyPageSize) + 1;
            const end = Math.min(total, familyPage * familyPageSize);
            familyPageInfo.textContent = total === 0 ? 'Foto tidak ditemukan' : `Menampilkan ${start}-${end} dari ${total} foto`;
        }
        if (familyPageNumber) familyPageNumber.textContent = `${familyPage} / ${totalPages}`;
        if (familyPagePrev) familyPagePrev.disabled = familyPage <= 1;
        if (familyPageNext) familyPageNext.disabled = familyPage >= totalPages;
    };
    let familySwitchTimer = null;
    const setFamilySwitching = (switching) => {
        familyGrid?.classList.toggle('is-switching', switching);
        familyPagination?.classList.toggle('is-switching', switching);
    };
    const applyFamilyFilter = (resetPage = false) => {
        if (resetPage) familyPage = 1;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            renderFamilyPagination();
            return;
        }
        window.clearTimeout(familySwitchTimer);
        setFamilySwitching(true);
        familySwitchTimer = window.setTimeout(() => {
            renderFamilyPagination();
            window.requestAnimationFrame(() => setFamilySwitching(false));
        }, 90);
    };

    document.querySelectorAll('[data-family-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            activeTab = tab.dataset.familyTab || 'all';
            document.querySelectorAll('[data-family-tab]').forEach((item) => item.classList.toggle('is-active', item === tab));
            applyFamilyFilter(true);
        });
    });
    familySearch?.addEventListener('input', () => applyFamilyFilter(true));
    familyPagePrev?.addEventListener('click', () => {
        if (familyPage <= 1) return;
        familyPage -= 1;
        applyFamilyFilter(false);
    });
    familyPageNext?.addEventListener('click', () => {
        familyPage += 1;
        applyFamilyFilter(false);
    });
    renderFamilyPagination();

    const lightbox = document.querySelector('[data-family-lightbox]');
    const lightboxImage = document.querySelector('[data-lightbox-image]');
    const closeLightbox = () => {
        lightbox?.classList.remove('is-open');
        lightbox?.setAttribute('aria-hidden', 'true');
        if (lightboxImage) lightboxImage.src = '';
    };
    document.querySelector('[data-family-grid]')?.addEventListener('click', (event) => {
        const downloadButton = event.target.closest('[data-download-family-photo]');
        if (downloadButton) return;
        const image = event.target.closest('[data-zoom-photo]');
        if (!image || !lightboxImage) return;
        lightboxImage.src = image.dataset.fullSrc || image.currentSrc || image.src || '';
        lightboxImage.alt = image.alt || 'Preview foto keluarga';
        lightbox?.classList.add('is-open');
        lightbox?.setAttribute('aria-hidden', 'false');
    });
    document.querySelector('[data-close-lightbox]')?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox) closeLightbox();
    });

    const downloadModal = document.querySelector('[data-download-modal]');
    const downloadForm = document.querySelector('[data-download-form]');
    const downloadHidden = document.querySelector('[data-download-pin-hidden]');
    const downloadBoxes = Array.from(document.querySelectorAll('[data-download-pin-box]'));
    const syncDownloadPin = () => {
        if (downloadHidden) downloadHidden.value = downloadBoxes.map((box) => box.value.trim()).join('');
    };
    const closeDownloadModal = () => {
        downloadModal?.classList.remove('is-open');
        downloadModal?.setAttribute('aria-hidden', 'true');
        downloadBoxes.forEach((box) => {
            box.value = '';
        });
        syncDownloadPin();
    };
    document.querySelectorAll('[data-download-family-photo]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!downloadForm) return;
            downloadForm.action = button.dataset.downloadUrl || '';
            downloadModal?.classList.add('is-open');
            downloadModal?.setAttribute('aria-hidden', 'false');
            setTimeout(() => downloadBoxes[0]?.focus(), 40);
        });
    });
    downloadBoxes.forEach((box, index) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncDownloadPin();
            if (box.value && downloadBoxes[index + 1]) downloadBoxes[index + 1].focus();
        });
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !box.value && downloadBoxes[index - 1]) downloadBoxes[index - 1].focus();
        });
        box.addEventListener('paste', (event) => {
            const pasted = (event.clipboardData || window.clipboardData)?.getData('text') || '';
            const digits = pasted.replace(/\D/g, '').slice(0, downloadBoxes.length).split('');
            if (digits.length === 0) return;
            event.preventDefault();
            downloadBoxes.forEach((target, targetIndex) => {
                target.value = digits[targetIndex] || '';
            });
            syncDownloadPin();
            downloadBoxes[Math.min(digits.length, downloadBoxes.length) - 1]?.focus();
        });
    });
    downloadForm?.addEventListener('submit', (event) => {
        syncDownloadPin();
        if ((downloadHidden?.value || '').length !== 4) {
            event.preventDefault();
            downloadBoxes[0]?.focus();
            return;
        }
        setTimeout(closeDownloadModal, 260);
    });
    document.querySelector('[data-close-download-modal]')?.addEventListener('click', closeDownloadModal);
    downloadModal?.addEventListener('click', (event) => {
        if (event.target === downloadModal) closeDownloadModal();
    });
})();
</script>
</body>
</html>
