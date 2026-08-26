<?php
    $pageTitle = trim((string) ($page['title'] ?? 'AdaAcara'));
    $slug = (string) ($page['slug'] ?? '');
    $currentLang = (string) ($currentLang ?? aa_current_lang());
    $useCustomDomainEndpoints = ! empty($useCustomDomainEndpoints);
    $gmText = static fn (string $key, string $fallback, array $replace = []): string => aa_t($key, $fallback, $replace, $currentLang);
    $gmEndpointUrl = static function (string $path) use ($slug, $currentLang, $useCustomDomainEndpoints): string {
        $basePath = $useCustomDomainEndpoints
            ? '/memories' . $path
            : site_url('u/' . $slug . '/memories' . $path);

        return aa_lang_url($basePath, $currentLang);
    };
    $eventDate = (string) ($page['event_date'] ?? '');
    $eventDateLabel = $eventDate !== '' && strtotime($eventDate) !== false ? strtoupper(date('d F Y', strtotime($eventDate))) : '';
    $csrfName = csrf_token();
    $csrfHash = csrf_hash();
    $heroImage = trim((string) ($page['og_image'] ?? ''));
    if ($heroImage !== '' && ! str_starts_with($heroImage, 'http://') && ! str_starts_with($heroImage, 'https://')) {
        $heroImage = base_url(ltrim($heroImage, '/'));
    }
    $guestMemoryCssPath = FCPATH . 'public/assets/guest-memory/guest-memory.css';
    $guestMemoryJsPath = FCPATH . 'public/assets/guest-memory/guest-memory.js';
    $guestMemoryCssUrl = aa_asset_url('public/assets/guest-memory/guest-memory.css');
    $guestMemoryJsUrl = aa_asset_url('public/assets/guest-memory/guest-memory.js');
    if (is_file($guestMemoryCssPath)) {
        $guestMemoryCssUrl .= (str_contains($guestMemoryCssUrl, '?') ? '&' : '?') . 'v=' . filemtime($guestMemoryCssPath);
    }
    if (is_file($guestMemoryJsPath)) {
        $guestMemoryJsUrl .= (str_contains($guestMemoryJsUrl, '?') ? '&' : '?') . 'v=' . filemtime($guestMemoryJsPath);
    }
    $gmI18n = [
        'common.close' => $gmText('common.close', 'Tutup'),
        'common.back' => $gmText('common.back', 'Kembali'),
        'common.language' => $gmText('common.language', 'Bahasa'),
        'gm.toast.close' => $gmText('gm.toast.close', 'Tutup pemberitahuan'),
        'gm.toast.success' => $gmText('gm.toast.success', 'Momen berhasil ditambahkan.'),
        'gm.toast.code_title' => $gmText('gm.toast.code_title', 'Kode Cetak Kamu'),
        'gm.toast.code_email' => $gmText('gm.toast.code_email', 'Simpan kode ini untuk cetak/unduh. Kode juga sudah dikirim ke email kamu.'),
        'gm.toast.code_manual' => $gmText('gm.toast.code_manual', 'Simpan kode ini untuk cetak/unduh. Jika email diisi tetapi belum masuk, cek inbox/spam atau gunakan kode yang tampil di sini.'),
        'gm.error.request' => $gmText('gm.error.request', 'Permintaan belum berhasil.'),
        'gm.error.print_form' => $gmText('gm.error.print_form', 'Form kode cetak belum tersedia.'),
        'gm.error.photo_unavailable' => $gmText('gm.error.photo_unavailable', 'Foto belum tersedia.'),
        'gm.error.print_photo_unavailable' => $gmText('gm.error.print_photo_unavailable', 'Foto belum tersedia untuk dicetak.'),
        'gm.error.print_photo_load' => $gmText('gm.error.print_photo_load', 'Foto belum berhasil dimuat untuk cetak.'),
        'gm.error.mobile_print' => $gmText('gm.error.mobile_print', 'Cetak foto tersedia dari komputer/meja printer. Gunakan Unduh Foto jika memakai HP.'),
        'gm.error.popup' => $gmText('gm.error.popup', 'Izinkan pop-up untuk membuka cetak foto.'),
        'gm.error.print_access' => $gmText('gm.error.print_access', 'Akses cetak belum tersedia untuk foto ini.'),
        'gm.error.print_failed' => $gmText('gm.error.print_failed', 'Foto ini belum bisa dicetak.'),
        'gm.error.validate_print' => $gmText('gm.error.validate_print', 'Validasi kode cetak terlebih dahulu.'),
        'gm.print.prepare_title' => $gmText('gm.print.prepare_title', 'Menyiapkan Cetak'),
        'gm.print.prepare' => $gmText('gm.print.prepare', 'Menyiapkan cetak...'),
        'gm.print.layout' => $gmText('gm.print.layout', 'Menyiapkan layout cetak...'),
        'gm.print.approved' => $gmText('gm.print.approved', 'Cetak disetujui. Foto ini tidak bisa dicetak lagi dari kode yang sama.'),
        'gm.print.enter_code' => $gmText('gm.print.enter_code', 'Masukkan kode cetak terlebih dahulu.'),
        'gm.print.wrong_code' => $gmText('gm.print.wrong_code', 'Kode cetak belum sesuai.'),
        'gm.print.code_ok' => $gmText('gm.print.code_ok', 'Kode cocok. Pilih Unduh atau Cetak Foto.'),
        'gm.delete.unavailable' => $gmText('gm.delete.unavailable', 'Foto ini belum bisa dihapus.'),
        'gm.delete.prompt' => $gmText('gm.delete.prompt', 'Tulis nama yang kamu pakai saat upload foto ini:'),
        'gm.delete.name_required' => $gmText('gm.delete.name_required', 'Nama wajib diisi untuk menghapus foto.'),
        'gm.delete.name_mismatch' => $gmText('gm.delete.name_mismatch', 'Nama tidak cocok. Gunakan nama yang sama saat upload foto.'),
        'gm.delete.confirm' => $gmText('gm.delete.confirm', 'Apakah kamu yakin ingin menghapus foto kamu dari galeri photobooth?'),
        'gm.delete.failed' => $gmText('gm.delete.failed', 'Foto belum berhasil dihapus.'),
        'gm.delete.success' => $gmText('gm.delete.success', 'Foto memories berhasil dihapus dari galeri.'),
        'gm.card.guest' => $gmText('gm.card.guest', 'Tamu'),
        'gm.card.alt' => $gmText('gm.card.alt', 'Momen dari {name}'),
        'gm.card.menu' => $gmText('gm.card.menu', 'Menu memories'),
        'gm.card.enlarge' => $gmText('gm.card.enlarge', 'Perbesar'),
        'gm.card.delete' => $gmText('gm.card.delete', 'Hapus'),
        'gm.gallery.empty_title' => $gmText('gm.gallery.empty_title', 'Belum ada memories.'),
        'gm.gallery.empty_text' => $gmText('gm.gallery.empty_text', 'Jadilah tamu pertama yang membagikan momen.'),
        'gm.gallery.not_found_title' => $gmText('gm.gallery.not_found_title', 'Nama tersebut tidak ditemukan.'),
        'gm.gallery.not_found_text' => $gmText('gm.gallery.not_found_text', 'Coba gunakan kata kunci nama yang lain.'),
        'gm.audio.unsupported' => $gmText('gm.audio.unsupported', 'Browser ini belum mendukung rekam suara.'),
        'gm.audio.permission' => $gmText('gm.audio.permission', 'Izin microphone belum diberikan.'),
        'gm.audio.deleted' => $gmText('gm.audio.deleted', 'Voice wish dihapus.'),
        'gm.camera.loading_frame' => $gmText('gm.camera.loading_frame', 'Mohon tunggu, sedang mengakses frame...'),
        'gm.camera.opening' => $gmText('gm.camera.opening', 'Mengakses kamera...'),
        'gm.camera.preparing' => $gmText('gm.camera.preparing', 'MENYIAPKAN FRAME'),
        'gm.camera.wait' => $gmText('gm.camera.wait', 'Mohon tunggu...'),
        'gm.photo.title' => $gmText('gm.photo.title', 'AMBIL FOTO'),
        'gm.photo.saved' => $gmText('gm.photo.saved', 'FOTO TERSIMPAN'),
        'gm.photo.status' => $gmText('gm.photo.status', 'Foto {current} dari {max}'),
        'gm.photo.camera' => $gmText('gm.photo.camera', 'AMBIL FOTO'),
        'gm.photo.retake' => $gmText('gm.photo.retake', 'AMBIL FOTO ULANG'),
        'gm.photo.next' => $gmText('gm.photo.next', 'LANJUT FOTO {number}'),
        'gm.photo.next_default' => $gmText('gm.photo.next_default', 'LANJUT'),
        'gm.photo.slot' => $gmText('gm.photo.slot', 'Foto {number}'),
        'gm.frame.info' => $gmText('gm.frame.info', '{count} foto'),
        'gm.frame.info_plural' => $gmText('gm.frame.info_plural', '{count} foto'),
        'gm.frame.missing' => $gmText('gm.frame.missing', 'Silahkan buat tampilan Frame di adaAcara Studio dahulu'),
        'gm.frame.unavailable' => $gmText('gm.frame.unavailable', 'belum tersedia'),
        'gm.upload.invalid_type' => $gmText('gm.upload.invalid_type', 'Gunakan foto JPG, PNG, atau WEBP.'),
        'gm.upload.too_large' => $gmText('gm.upload.too_large', 'Ukuran foto terlalu besar. Gunakan foto maksimal 20MB.'),
        'gm.upload.installing' => $gmText('gm.upload.installing', 'Memasang foto...'),
        'gm.upload.read_failed' => $gmText('gm.upload.read_failed', 'Foto tidak bisa dibaca.'),
        'gm.upload.not_ready' => $gmText('gm.upload.not_ready', 'Foto belum siap.'),
        'gm.upload.saved_next' => $gmText('gm.upload.saved_next', 'Foto tersimpan. Lanjut ambil foto {number}.'),
        'gm.upload.cannot_use' => $gmText('gm.upload.cannot_use', 'Foto belum bisa dipakai.'),
        'gm.upload.process_failed' => $gmText('gm.upload.process_failed', 'Foto tidak bisa diproses.'),
        'gm.upload.complete_all' => $gmText('gm.upload.complete_all', 'Lengkapi semua foto terlebih dahulu.'),
        'gm.upload.name_required' => $gmText('gm.upload.name_required', 'Tulis nama kamu terlebih dahulu.'),
        'gm.upload.email_invalid' => $gmText('gm.upload.email_invalid', 'Format email belum valid.'),
        'gm.upload.wish_limit' => $gmText('gm.upload.wish_limit', 'Ucapan maksimal 500 karakter.'),
        'gm.upload.failed' => $gmText('gm.upload.failed', 'Upload belum berhasil.'),
        'gm.upload.name_taken' => $gmText('gm.upload.name_taken', 'Gunakan nama lain atau tambahkan inisial. Nama ini sudah dipakai.'),
        'gm.upload.connection_lost' => $gmText('gm.upload.connection_lost', 'Koneksi upload terputus. Coba lagi.'),
        'gm.audio.play_failed' => $gmText('gm.audio.play_failed', 'Audio belum bisa diputar.'),
    ];
?>
<!doctype html>
<html lang="<?= esc($gmText('gm.html_lang', 'id'), 'attr') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token-name" content="<?= esc($csrfName, 'attr') ?>">
    <meta name="csrf-token" content="<?= esc($csrfHash, 'attr') ?>">
    <title><?= esc($metaTitle ?? ('Kenangan Tamu - ' . $pageTitle)) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo2.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/logo2.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="<?= esc(site_url('custom-fonts.css'), 'attr') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= esc($guestMemoryCssUrl, 'attr') ?>">
    <style>
        .aa-gm-lang-switcher{position:absolute;z-index:35;top:calc(14px + env(safe-area-inset-top));left:14px;font-family:Inter,system-ui,sans-serif}
        .aa-gm-lang-switcher summary{display:inline-flex;align-items:center;gap:7px;min-width:54px;height:40px;padding:0 13px;border:1px solid rgba(15,23,42,.08);border-radius:999px;background:rgba(255,255,255,.86);box-shadow:0 14px 32px rgba(15,23,42,.14);color:#111827;cursor:pointer;font-size:12px;font-weight:900;letter-spacing:.08em;list-style:none;backdrop-filter:blur(14px)}
        .aa-gm-lang-switcher summary::-webkit-details-marker{display:none}
        .aa-gm-lang-switcher summary::after{content:"";width:6px;height:6px;border-right:2px solid currentColor;border-bottom:2px solid currentColor;transform:translateY(-2px) rotate(45deg);opacity:.58}
        .aa-gm-lang-switcher[open] summary::after{transform:translateY(2px) rotate(225deg)}
        .aa-gm-lang-switcher__menu{display:grid;gap:6px;width:92px;margin-top:8px;padding:7px;border:1px solid rgba(15,23,42,.08);border-radius:18px;background:rgba(255,255,255,.94);box-shadow:0 18px 38px rgba(15,23,42,.18);backdrop-filter:blur(14px)}
        .aa-gm-lang-switcher a{display:flex;align-items:center;justify-content:center;border-radius:13px;padding:9px 10px;color:#64748b;font-size:11px;font-weight:900;letter-spacing:.08em;text-align:center;text-decoration:none}
        .aa-gm-lang-switcher a.is-active{background:#111827;color:#fff}
    </style>
</head>
<body class="aa-gm-page">
    <main class="aa-gm-app" data-gm-app data-ready="<?= ! empty($isReady) ? '1' : '0' ?>" data-enabled="<?= ! empty($isEnabled) ? '1' : '0' ?>">
        <details class="aa-gm-lang-switcher">
            <summary aria-label="<?= esc($gmText('common.language', 'Bahasa'), 'attr') ?>"><?= esc(strtoupper($currentLang)) ?></summary>
            <nav class="aa-gm-lang-switcher__menu" aria-label="<?= esc($gmText('common.language', 'Bahasa'), 'attr') ?>">
                <?php foreach (aa_supported_langs() as $lang): ?>
                    <a class="<?= $lang === $currentLang ? 'is-active' : '' ?>" href="<?= esc(aa_lang_url(current_url(), $lang), 'attr') ?>" lang="<?= esc($lang, 'attr') ?>"><?= esc(strtoupper($lang)) ?></a>
                <?php endforeach; ?>
            </nav>
        </details>
        <div class="aa-gm-page-transition" data-gm-transition aria-hidden="true"></div>
        <section class="aa-gm-screen aa-gm-opening" data-gm-screen="opening" aria-labelledby="aaGmOpeningTitle">
            <div class="aa-gm-opening__brand"><?= esc($gmText('gm.brand', 'KENANGAN PHOTOBOOTH')) ?></div>
            <h1 id="aaGmOpeningTitle" style="text-align: center;"><?= esc($pageTitle) ?></h1>
            <button class="aa-gm-envelope" type="button" data-gm-open-experience aria-label="<?= esc($gmText('gm.open', 'Buka Kenangan Tamu'), 'attr') ?>">
                <span class="aa-gm-envelope__flap"></span>
                <span class="aa-gm-envelope__body"></span>
                <span class="aa-gm-envelope__seal" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="30" height="30" role="img" focusable="false">
                        <path d="M8.25 6.25 9.55 4.6h4.9l1.3 1.65h2.05c1.35 0 2.45 1.1 2.45 2.45v7.85A2.45 2.45 0 0 1 17.8 19H6.2a2.45 2.45 0 0 1-2.45-2.45V8.7c0-1.35 1.1-2.45 2.45-2.45h2.05Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <circle cx="12" cy="12.55" r="3.35" fill="none" stroke="currentColor" stroke-width="1.7"/>
                        <circle cx="17.3" cy="9.1" r=".75" fill="currentColor"/>
                    </svg>
                </span>
            </button>
            <p class="aa-gm-tap"><?= esc($gmText('gm.tap_to_open', 'KETUK UNTUK MEMBUKA')) ?></p>
        </section>

        <section class="aa-gm-screen aa-gm-experience" data-gm-screen="experience" hidden>
            <header class="aa-gm-topbar">
                <button class="aa-gm-icon-btn aa-gm-icon-btn--ghost" type="button" data-gm-back hidden aria-label="<?= esc($gmText('common.back', 'Kembali'), 'attr') ?>">←</button>
                <button class="aa-gm-icon-btn aa-gm-icon-btn--right" type="button" data-gm-close-experience aria-label="<?= esc($gmText('common.close', 'Tutup'), 'attr') ?>">ㄨ</button>
            </header>

            <?php if (empty($isReady)): ?>
                <section class="aa-gm-locked">
                    <p class="aa-gm-title-small"><?= esc($gmText('gm.disabled.ready_title', 'Kenangan tamu belum siap')) ?></p>
                    <h2><?= esc($gmText('gm.disabled.ready_heading', 'Setup database diperlukan.')) ?></h2>
                    <p><?= esc($gmText('gm.disabled.ready_text', 'Jalankan file database/alter_guest_memories.sql lebih dulu.')) ?></p>
                </section>
            <?php elseif (empty($isEnabled)): ?>
                <section class="aa-gm-locked">
                    <p class="aa-gm-title-small"><?= esc($gmText('gm.disabled.feature_title', 'Kenangan Tamu')) ?></p>
                    <h2><?= esc($gmText('gm.disabled.feature_heading', 'Fitur belum aktif untuk undangan ini.')) ?></h2>
                    <p><?= esc($gmText('gm.disabled.feature_text', 'Admin dapat mengaktifkan Kenangan Tamu dari halaman admin/users pada pemilik undangan.')) ?></p>
                </section>
            <?php else: ?>
                <section class="aa-gm-panel aa-gm-home" data-gm-panel="home">
                    <div class="aa-gm-cover">
                        <?php if ($heroImage !== ''): ?>
                            <img src="<?= esc($heroImage, 'attr') ?>" alt="" loading="eager" decoding="async">
                        <?php endif; ?>
                        <div class="aa-gm-cover__shade"></div>
                        <div class="aa-gm-cover__content">
                            <p><?= esc($gmText('gm.brand', 'KENANGAN PHOTOBOOTH')) ?></p>
                            <h2><?= esc($pageTitle) ?></h2>
                            <?php if ($eventDateLabel !== ''): ?>
                                <span><?= esc($eventDateLabel) ?></span>
                            <?php endif; ?>
                            <strong><?= esc($gmText('gm.home.subtitle', 'ABADIKAN MOMEN INDAH DAN BUAT KENANGAN BERSAMA')) ?></strong>
                        </div>
                    </div>
                    <div class="aa-gm-home__body">
                        <span class="aa-gm-ornament" aria-hidden="true"></span>
                        <p><?= $gmText('gm.home.note', 'RAYAKAN HARI INI<br>LEWAT CERITAMU') ?></p>
                        <button class="aa-gm-action aa-gm-action--filled" type="button" data-gm-go-upload><?= esc($gmText('gm.home.add', 'TAMBAH PHOTOBOOTH')) ?></button>
                        <button class="aa-gm-link-action" type="button" data-gm-go-gallery><?= esc($gmText('gm.home.gallery', 'LIHAT GALERI')) ?></button>
                        </button>
                    </div>
                </section>

                <section class="aa-gm-panel aa-gm-gallery" data-gm-panel="gallery" hidden>
                    <div class="aa-gm-page-title">
                        <p><?= esc($gmText('gm.brand', 'KENANGAN PHOTOBOOTH')) ?></p>
                        <h2><?= esc($pageTitle) ?></h2>
                        <?php if ($eventDateLabel !== ''): ?>
                            <span><?= esc($eventDateLabel) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="aa-gm-floating-wishes" data-gm-floating-wishes hidden aria-label="<?= esc($gmText('gm.wishes.floating_label', 'Ucapan tamu'), 'attr') ?>">
                        <div class="aa-gm-floating-wishes__track" data-gm-floating-wishes-track></div>
                    </div>
                    <label class="aa-gm-gallery-search">
                        <svg style="width: 24px;height: 24px;position: absolute;right: 30px;top: 222px;" 
                        class="gm-search-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0z"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"/>
                        </svg>
                        <input type="search" placeholder="<?= esc($gmText('gm.gallery.search', 'Cari Foto Kamu...'), 'attr') ?>" autocomplete="off" data-gm-search>
                        <small><?= esc($gmText('gm.gallery.search_note', 'Input nama yang kamu pakai saat upload.')) ?></small>        
                    </label>
                    <div class="aa-gm-grid" data-gm-grid></div>
                    <div class="aa-gm-empty" data-gm-empty hidden>
                        <strong><?= esc($gmText('gm.gallery.empty_title', 'Belum ada memories.')) ?></strong>
                        <span><?= esc($gmText('gm.gallery.empty_text', 'Jadilah tamu pertama yang membagikan momen.')) ?></span>
                    </div>
                    <div class="aa-gm-loader" data-gm-loader hidden><?= esc($gmText('gm.gallery.loading', 'Memuat memories...')) ?></div>
                    <div class="aa-gm-sentinel" data-gm-sentinel aria-hidden="true"></div>
                </section>

                <section class="aa-gm-panel aa-gm-upload" data-gm-panel="upload" hidden>
                    <div class="aa-gm-step" data-gm-step="frame">
                        <div class="aa-gm-frame-copy">
                            <p><?= esc($gmText('gm.frame.title', 'PILIH FRAME PHOTOBOOTH')) ?></p>
                            <span data-gm-frame-info><?= esc($gmText('gm.frame.info', '1 foto', ['count' => 1])) ?></span>
                        </div>
                        <div class="aa-gm-frame-stage">
                            <button class="aa-gm-frame-arrow" type="button" data-gm-prev-frame aria-label="<?= esc($gmText('gm.frame.previous', 'Frame sebelumnya'), 'attr') ?>">‹</button>
                            <div class="aa-gm-frame-preview" data-gm-frame-preview></div>
                            <button class="aa-gm-frame-arrow" type="button" data-gm-next-frame aria-label="<?= esc($gmText('gm.frame.next', 'Frame berikutnya'), 'attr') ?>">›</button>
                        </div>
                        <div class="aa-gm-frame-dots" data-gm-frame-dots></div>
                        <button class="aa-gm-action aa-gm-action--light" type="button" data-gm-select-frame><?= esc($gmText('gm.frame.select', 'PILIH FRAME')) ?></button>
                    </div>

                    <div class="aa-gm-step" data-gm-step="photo" hidden>
                        <div class="aa-gm-capture-copy">
                            <p data-gm-capture-title><?= esc($gmText('gm.photo.title', 'AMBIL FOTO')) ?></p>
                            <span data-gm-slot-status><?= esc($gmText('gm.photo.status', 'Foto {current} dari {max}', ['current' => 1, 'max' => 1])) ?></span>
                        </div>
                        <div class="aa-gm-slot-list" data-gm-slot-list></div>
                        <div class="aa-gm-live-card">
                            <canvas class="aa-gm-preview" data-gm-canvas width="1080" height="1350"></canvas>
                        </div>
                        <button class="aa-gm-action aa-gm-action--light" type="button" data-gm-camera><?= esc($gmText('gm.photo.camera', 'AMBIL FOTO')) ?></button>
                        <button class="aa-gm-action aa-gm-action--outline" type="button" data-gm-gallery><?= esc($gmText('gm.photo.gallery', 'UNGGAH DARI GALERI')) ?></button>
                        <button class="aa-gm-link-action aa-gm-link-action--light" type="button" data-gm-next-slot hidden><?= esc($gmText('gm.photo.next_slot', 'LANJUT FOTO BERIKUTNYA')) ?></button>
                    </div>

                    <div class="aa-gm-step" data-gm-step="crop" hidden>
                        <div class="aa-gm-capture-copy">
                            <p><?= esc($gmText('gm.crop.title', 'ATUR FOTO')) ?></p>
                            <span data-gm-crop-status><?= esc($gmText('gm.crop.status', 'Geser foto agar pas di frame')) ?></span>
                        </div>
                        <div class="aa-gm-crop-card">
                            <canvas class="aa-gm-crop-canvas" data-gm-crop-canvas width="750" height="560"></canvas>
                            <span class="aa-gm-crop-hint"><?= esc($gmText('gm.crop.hint', 'Geser foto')) ?></span>
                        </div>
                        <label class="aa-gm-crop-zoom">
                            <span><?= esc($gmText('gm.crop.zoom', 'ZOOM')) ?></span>
                            <input type="range" min="1" max="2.4" step="0.01" value="1" data-gm-crop-zoom>
                        </label>
                        <button class="aa-gm-action aa-gm-action--light" type="button" data-gm-use-crop><?= esc($gmText('gm.crop.use', 'GUNAKAN FOTO')) ?></button>
                        <button class="aa-gm-link-action aa-gm-link-action--light" type="button" data-gm-retake><?= esc($gmText('gm.crop.retake', 'AMBIL ULANG')) ?></button>
                    </div>

                    <div class="aa-gm-step" data-gm-step="details" hidden>
                        <div class="aa-gm-live-card aa-gm-live-card--final">
                            <canvas class="aa-gm-final-preview" data-gm-final-canvas width="1080" height="1350"></canvas>
                        </div>
                        <label class="aa-gm-name-field">
                            <span><?= esc($gmText('gm.details.name', 'NAMA KAMU')) ?></span>
                            <input type="text" maxlength="120" placeholder="<?= esc($gmText('gm.details.name_placeholder', 'Nama kamu'), 'attr') ?>" data-gm-name>
                        </label>
                        <label class="aa-gm-name-field">
                            <span><?= esc($gmText('gm.details.email', 'EMAIL')) ?></span>
                            <input type="email" maxlength="190" placeholder="<?= esc($gmText('gm.details.email_placeholder', 'nama@email.com'), 'attr') ?>" data-gm-email>
                            <small><?= esc($gmText('gm.details.email_note', '*email digunakan untuk kode akses cetak/unduh.')) ?></small>
                        </label>
                        <label class="aa-gm-name-field aa-gm-wish-field">
                            <span><?= esc($gmText('gm.details.wish', 'UCAPAN SINGKAT')) ?></span>
                            <textarea maxlength="500" rows="3" placeholder="<?= esc($gmText('gm.details.wish_placeholder', 'Tulis ucapan singkat untuk pengantin...'), 'attr') ?>" data-gm-wish></textarea>
                            <small><span data-gm-wish-count>0</span>/500 <?= esc($gmText('gm.details.wish_note', 'karakter, opsional.')) ?></small>
                        </label>
                        <!-- <div class="aa-gm-recorder" data-gm-recorder>
                            <p>FOR THE COUPLE</p>
                            <span>Share your best wishes for the couple</span>
                            <div class="aa-gm-record-wave" aria-hidden="true">
                                <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                            </div>
                            <strong data-gm-record-time>00:00</strong>
                            <button class="aa-gm-record-button" type="button" data-gm-toggle-record aria-label="Record a wish"></button>
                            <button class="aa-gm-record-again" type="button" data-gm-clear-audio hidden>RECORD AGAIN</button>
                        </div> -->
                        <div class="aa-gm-progress" data-gm-progress hidden>
                            <span data-gm-progress-bar></span>
                        </div>
                        <button class="aa-gm-action aa-gm-action--light" type="button" data-gm-submit><?= esc($gmText('gm.details.submit', 'BAGIKAN MOMEN')) ?></button>
                    </div>

                    <input type="file" accept="image/jpeg,image/png,image/webp" capture="environment" data-gm-camera-input hidden>
                    <input type="file" accept="image/jpeg,image/png,image/webp" data-gm-file-input hidden>
                </section>
            <?php endif; ?>
        </section>

        <div class="aa-gm-printing" data-gm-printing hidden>
            <div class="aa-gm-printer">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <img src="" alt="" data-gm-printing-photo>
            <p><?= esc($gmText('gm.printing', 'Mencetak momenmu...')) ?></p>
        </div>

        <div class="aa-gm-detail" data-gm-detail-modal hidden>
            <button class="aa-gm-detail__close" type="button" data-gm-close-detail><?= esc($gmText('gm.detail.close', 'TUTUP')) ?></button>
            <img src="" alt="" data-gm-detail-photo>
            <div class="aa-gm-audio-pill" data-gm-detail-audio hidden>
                <button class="aa-gm-audio-play" type="button" data-gm-detail-audio-play aria-label="<?= esc($gmText('gm.detail.audio_play', 'Putar voice wish'), 'attr') ?>">▶</button>
                <span>00:11</span>
                <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
            </div>
            <h2 data-gm-detail-name></h2>
            <p><span data-gm-detail-date></span></p>
            <blockquote class="aa-gm-detail-wish" data-gm-detail-wish hidden></blockquote>
            <button type="button" data-gm-detail-download><?= esc($gmText('gm.detail.print_download', 'CETAK / UNDUH')) ?></button>
        </div>

        <div class="aa-gm-print-code" data-gm-print-code-modal hidden>
            <div class="aa-gm-print-code__card">
                <button class="aa-gm-print-code__close" type="button" data-gm-print-code-close aria-label="<?= esc($gmText('common.close', 'Tutup'), 'attr') ?>">×</button>
                <p><?= esc($gmText('gm.print_access.title', 'AKSES CETAK')) ?></p>
                <h2 data-gm-print-code-name><?= esc($gmText('gm.print_access.name', 'Momen')) ?></h2>
                <span><?= esc($gmText('gm.print_access.instruction', 'Masukkan kode cetak yang kamu dapat setelah upload foto. Jika email diisi saat upload, cek inbox atau spam untuk melihat kode aksesnya.')) ?></span>
                <input type="text" maxlength="32" placeholder="<?= esc($gmText('gm.print_access.placeholder', 'ANDI-4832'), 'attr') ?>" autocomplete="one-time-code" data-gm-print-code-input>
                <button type="button" data-gm-print-code-submit><?= esc($gmText('gm.print_access.submit', 'CETAK / UNDUH')) ?></button>
                <p class="aa-gm-print-code__status" data-gm-print-code-status></p>
                <div class="aa-gm-print-code__actions" data-gm-print-code-actions hidden>
                    <a class="aa-gm-print-code__download" href="" download data-gm-print-code-download><?= esc($gmText('gm.print_access.download', 'UNDUH FOTO')) ?></a>
                    <button class="aa-gm-print-code__print" type="button" data-gm-print-code-print><?= esc($gmText('gm.print_access.print', 'CETAK FOTO')) ?></button>
                </div>
            </div>
        </div>

        <div class="aa-gm-toast" data-gm-toast hidden></div>
    </main>

    <script>
        window.AdaAcaraGuestMemory = {
            isReady: <?= ! empty($isReady) ? 'true' : 'false' ?>,
            isEnabled: <?= ! empty($isEnabled) ? 'true' : 'false' ?>,
            lang: <?= json_encode($currentLang) ?>,
            i18n: <?= json_encode($gmI18n, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            title: <?= json_encode($pageTitle) ?>,
            eventDate: <?= json_encode($eventDateLabel) ?>,
            listUrl: <?= json_encode($gmEndpointUrl('/list')) ?>,
            framesUrl: <?= json_encode($gmEndpointUrl('/frames')) ?>,
            uploadUrl: <?= json_encode($gmEndpointUrl('/upload')) ?>,
            printAccessUrlTemplate: <?= json_encode($gmEndpointUrl('/{id}/print-access')) ?>,
            markPrintedUrlTemplate: <?= json_encode($gmEndpointUrl('/{id}/mark-printed')) ?>,
            deleteUrlTemplate: <?= json_encode($gmEndpointUrl('/{id}/delete')) ?>,
            frames: [],
            csrfName: <?= json_encode($csrfName) ?>,
            csrfHash: <?= json_encode($csrfHash) ?>
        };
    </script>
    <script src="<?= esc($guestMemoryJsUrl, 'attr') ?>" defer></script>
    <script type="application/json" data-aa-gm-disabled-inline-renderer>
        (function () {
            'use strict';

            var cfg = window.AdaAcaraGuestMemory || {};
            var frames = Array.isArray(cfg.frames) ? cfg.frames : [];
            var fabricPromise = null;
            var renderMarks = new WeakMap();

            function loadFabric() {
                if (window.fabric) {
                    return Promise.resolve(window.fabric);
                }
                if (fabricPromise) {
                    return fabricPromise;
                }
                fabricPromise = new Promise(function (resolve, reject) {
                    var script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js';
                    script.onload = function () {
                        window.fabric ? resolve(window.fabric) : reject(new Error('Fabric tidak tersedia.'));
                    };
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
                return fabricPromise;
            }

            function collectFonts(objects, output) {
                (objects || []).forEach(function (object) {
                    if (!object || typeof object !== 'object') {
                        return;
                    }
                    var type = String(object.type || '').toLowerCase();
                    if (type === 'textbox' || type === 'i-text' || type === 'text') {
                        var family = String(object.fontFamily || 'Inter').replace(/^["']|["']$/g, '');
                        var weight = String(object.fontWeight || '400').toLowerCase() === 'bold' ? '700' : String(object.fontWeight || '400');
                        if (!/^[1-9]00$/.test(weight)) {
                            weight = Number(weight) >= 600 ? '700' : '400';
                        }
                        var style = String(object.fontStyle || 'normal').toLowerCase() === 'italic' ? 'italic' : 'normal';
                        output[family + '|' + weight + '|' + style] = {
                            family: family,
                            weight: weight,
                            style: style
                        };
                    }
                    if (Array.isArray(object.objects)) {
                        collectFonts(object.objects, output);
                    }
                });
            }

            function waitFonts(frame) {
                if (!document.fonts || !document.fonts.load) {
                    return Promise.resolve();
                }
                var variants = {};
                collectFonts(frame && frame.fabric ? frame.fabric.objects : [], variants);
                var jobs = Object.keys(variants).map(function (key) {
                    var font = variants[key];
                    return document.fonts.load(font.style + ' ' + font.weight + ' 32px "' + font.family + '"').catch(function () {
                        return null;
                    });
                });
                return Promise.all(jobs).then(function () {
                    return document.fonts.ready;
                }).catch(function () {
                    return null;
                });
            }

            function frameObjects(frame) {
                var objects = frame && frame.fabric && Array.isArray(frame.fabric.objects) ? frame.fabric.objects : [];
                return objects.filter(Boolean);
            }

            function cloneFrameObjects(frame) {
                try {
                    return JSON.parse(JSON.stringify(frameObjects(frame)));
                } catch (error) {
                    return [];
                }
            }

            function normalizeFabricObject(object) {
                if (!object || typeof object !== 'object') {
                    return;
                }
                var type = String(object.type || '').toLowerCase();
                if ((type === 'textbox' || type === 'i-text' || type === 'text') && Array.isArray(object.styles)) {
                    object.styles = {};
                }
                if (type === 'image' && object.src && !object.crossOrigin) {
                    object.crossOrigin = 'anonymous';
                }
                if (Array.isArray(object.objects)) {
                    object.objects.forEach(normalizeFabricObject);
                }
            }

            function relaxFrameCanvasHeight(staticCanvas, frame) {
                if (!staticCanvas) {
                    return;
                }
                var canvasEl = staticCanvas.lowerCanvasEl || staticCanvas.getElement && staticCanvas.getElement();
                if (canvasEl && canvasEl.classList && canvasEl.classList.contains('aa-gm-frame-canvas')) {
                    canvasEl.style.removeProperty('width');
                    canvasEl.style.removeProperty('height');
                    canvasEl.style.removeProperty('max-height');
                }
                if (canvasEl && canvasEl.classList && canvasEl.classList.contains('aa-gm-final-preview')) {
                    canvasEl.style.setProperty('width', '-webkit-fill-available', 'important');
                    canvasEl.style.removeProperty('height');
                    canvasEl.style.setProperty('height', 'auto', 'important');
                    canvasEl.style.removeProperty('max-height');
                }
                if (canvasEl && canvasEl.classList && canvasEl.classList.contains('aa-gm-preview')) {
                    canvasEl.style.removeProperty('width');
                    canvasEl.style.removeProperty('height');
                    canvasEl.style.setProperty('height', 'auto', 'important');
                    canvasEl.style.removeProperty('max-height');
                }
                var wrapper = staticCanvas.wrapperEl || (canvasEl ? canvasEl.parentElement : null);
                if (wrapper && wrapper.classList && wrapper.classList.contains('canvas-container')) {
                    if (canvasEl && canvasEl.classList && canvasEl.classList.contains('aa-gm-frame-canvas')) {
                        wrapper.style.removeProperty('width');
                    }
                    if (canvasEl && canvasEl.classList && canvasEl.classList.contains('aa-gm-final-preview')) {
                        wrapper.style.setProperty('width', '-webkit-fill-available', 'important');
                    }
                    if (canvasEl && canvasEl.classList && canvasEl.classList.contains('aa-gm-preview')) {
                        wrapper.style.removeProperty('width');
                    }
                    wrapper.style.removeProperty('height');
                    wrapper.style.height = 'auto';
                    wrapper.style.overflow = 'visible';
                }
            }

            function activeFrameIndex() {
                var dots = Array.prototype.slice.call(document.querySelectorAll('[data-gm-frame-dots] span'));
                var index = dots.findIndex(function (dot) {
                    return dot.classList.contains('is-active');
                });
                return index >= 0 ? index : 0;
            }

            function frameIndexForCard(card) {
                var current = activeFrameIndex();
                var offset = card.classList.contains('is-prev') ? -1 : (card.classList.contains('is-next') ? 1 : 0);
                return (current + offset + frames.length) % frames.length;
            }

            function slotOrderNumber(object, fallback) {
                var value = Number(object && object.aaPhotoboothSlotIndex);
                return Number.isFinite(value) && value > 0 ? value : fallback;
            }

            function slotMetric(object, rawIndex) {
                var scaleX = Math.max(0.01, Number(object.scaleX || 1) || 1);
                var scaleY = Math.max(0.01, Number(object.scaleY || 1) || 1);
                var width = typeof object.getScaledWidth === 'function'
                    ? object.getScaledWidth()
                    : (Number(object.width || 0) || 0) * scaleX;
                var height = typeof object.getScaledHeight === 'function'
                    ? object.getScaledHeight()
                    : (Number(object.height || 0) || 0) * scaleY;
                return {
                    object: object,
                    rawIndex: rawIndex,
                    orderNumber: slotOrderNumber(object, rawIndex + 1),
                    x: Number(object.left || 0) || 0,
                    y: Number(object.top || 0) || 0,
                    width: Math.max(40, width),
                    height: Math.max(40, height),
                    radius: Math.max(0, Number(object.rx || object.ry || 0) || 0) * Math.max(scaleX, scaleY),
                    angle: Number(object.angle || 0) || 0,
                    opacity: object.opacity == null ? 1 : Math.max(0, Math.min(1, Number(object.opacity) || 0))
                };
            }

            function sortSlotsByVisualPosition(slots) {
                var counts = {};
                slots.forEach(function (slot) {
                    counts[slot.orderNumber] = (counts[slot.orderNumber] || 0) + 1;
                });
                var hasDuplicateIndex = slots.some(function (slot) {
                    return counts[slot.orderNumber] > 1;
                });
                slots.sort(function (a, b) {
                    if (!hasDuplicateIndex) {
                        var indexDiff = (a.orderNumber || 0) - (b.orderNumber || 0);
                        if (indexDiff !== 0) {
                            return indexDiff;
                        }
                    }
                    return (a.y - b.y) || (a.x - b.x) || (a.rawIndex - b.rawIndex);
                });
                return slots;
            }

            function extractPhotoSlots(frame) {
                var objects = frame && frame.fabric && Array.isArray(frame.fabric.objects) ? frame.fabric.objects : [];
                var slots = objects.filter(function (object) {
                    return object && object.customType === 'photobooth-photo-slot';
                }).map(slotMetric);
                return sortSlotsByVisualPosition(slots).slice(0, 3).map(function (slot, index) {
                    slot.index = index;
                    return slot;
                });
            }

            function roundRect(ctx, x, y, width, height, radius) {
                var r = Math.min(Math.max(0, Number(radius || 0)), width / 2, height / 2);
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.lineTo(x + width - r, y);
                ctx.quadraticCurveTo(x + width, y, x + width, y + r);
                ctx.lineTo(x + width, y + height - r);
                ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
                ctx.lineTo(x + r, y + height);
                ctx.quadraticCurveTo(x, y + height, x, y + height - r);
                ctx.lineTo(x, y + r);
                ctx.quadraticCurveTo(x, y, x + r, y);
                ctx.closePath();
            }

            function drawCover(ctx, img, x, y, width, height) {
                var iw = img && (img.naturalWidth || img.videoWidth || img.width) || 1;
                var ih = img && (img.naturalHeight || img.videoHeight || img.height) || 1;
                var scale = Math.max(width / iw, height / ih);
                var sw = width / scale;
                var sh = height / scale;
                var sx = Math.max(0, (iw - sw) / 2);
                var sy = Math.max(0, (ih - sh) / 2);
                ctx.drawImage(img, sx, sy, sw, sh, x, y, width, height);
            }

            function coverCanvasForSlot(img, slot) {
                var width = Math.max(1, Math.round(slot.width));
                var height = Math.max(1, Math.round(slot.height));
                var coverCanvas = document.createElement('canvas');
                var ctx = coverCanvas.getContext('2d');
                coverCanvas.width = width;
                coverCanvas.height = height;
                ctx.clearRect(0, 0, width, height);
                ctx.save();
                if (slot.radius > 0) {
                    roundRect(ctx, 0, 0, width, height, slot.radius);
                    ctx.clip();
                }
                drawCover(ctx, img, 0, 0, width, height);
                ctx.restore();
                return coverCanvas;
            }

            function staticCanvasSlots(staticCanvas, customType) {
                var rawIndex = 0;
                var objects = staticCanvas && typeof staticCanvas.getObjects === 'function' ? staticCanvas.getObjects() : [];
                return sortSlotsByVisualPosition(objects.map(function (object) {
                    if (!object || object.customType !== customType) {
                        return null;
                    }
                    return slotMetric(object, rawIndex++);
                }).filter(Boolean));
            }

            function canonicalFrameSlots(frame, staticCanvas) {
                var objectSlots = staticCanvasSlots(staticCanvas, 'photobooth-photo-slot').slice(0, 3);
                var metadataSlots = Array.isArray(frame && frame.photo_slots) ? frame.photo_slots : [];
                if (!metadataSlots.length) {
                    return objectSlots;
                }
                return metadataSlots.slice(0, 3).map(function (slot, index) {
                    var styleSource = objectSlots[index] || objectSlots[0] || {};
                    return {
                        object: styleSource.object || null,
                        rawIndex: index,
                        orderNumber: index + 1,
                        x: Number(slot.x || slot.left || 0) || 0,
                        y: Number(slot.y || slot.top || 0) || 0,
                        width: Math.max(40, Number(slot.width || 0) || 0),
                        height: Math.max(40, Number(slot.height || 0) || 0),
                        radius: Math.max(0, Number(slot.radius || slot.rx || slot.ry || 0) || 0),
                        angle: Number(styleSource.angle || 0) || 0,
                        opacity: styleSource.opacity == null ? 1 : styleSource.opacity,
                        styleSource: styleSource.object || null
                    };
                });
            }

            function placeholderForSlot(slot) {
                var source = slot.styleSource || {};
                return new fabric.Rect({
                    left: slot.x,
                    top: slot.y,
                    width: slot.width,
                    height: slot.height,
                    rx: slot.radius,
                    ry: slot.radius,
                    originX: 'left',
                    originY: 'top',
                    angle: slot.angle,
                    opacity: source.opacity == null ? .95 : source.opacity,
                    fill: source.fill || '#e5e7eb',
                    stroke: source.stroke || '#cbd5e1',
                    strokeWidth: source.strokeWidth == null ? 2 : source.strokeWidth,
                    strokeDashArray: source.strokeDashArray || [8, 8],
                    selectable: false,
                    evented: false,
                    hasControls: false,
                    hasBorders: false,
                    customType: 'photobooth-photo-slot'
                });
            }

            function replaceSlotsWithImages(staticCanvas, frame, images) {
                var sourceImages = Array.isArray(images) ? images : [];
                if (!staticCanvas || typeof fabric === 'undefined' || !fabric.Image) {
                    return;
                }
                var originalSlots = staticCanvasSlots(staticCanvas, 'photobooth-photo-slot').slice(0, 3);
                var slots = canonicalFrameSlots(frame, staticCanvas).slice(0, 3);
                var labels = staticCanvasSlots(staticCanvas, 'photobooth-photo-slot-label').slice(0, 3);
                var baseIndex = originalSlots.length && originalSlots[0].object
                    ? Math.max(0, staticCanvas.getObjects().indexOf(originalSlots[0].object))
                    : staticCanvas.getObjects().length;
                originalSlots.forEach(function (slot) {
                    if (slot.object) {
                        staticCanvas.remove(slot.object);
                    }
                });
                labels.forEach(function (label) {
                    if (label.object) {
                        staticCanvas.remove(label.object);
                    }
                });
                slots.forEach(function (slot, index) {
                    var img = sourceImages[index];
                    var object = img
                        ? new fabric.Image(coverCanvasForSlot(img, slot), {
                            left: slot.x,
                            top: slot.y,
                            originX: 'left',
                            originY: 'top',
                            scaleX: slot.width / Math.max(1, Math.round(slot.width)),
                            scaleY: slot.height / Math.max(1, Math.round(slot.height)),
                            angle: slot.angle,
                            opacity: slot.opacity,
                            selectable: false,
                            evented: false,
                            hasControls: false,
                            hasBorders: false,
                            customType: 'photobooth-rendered-photo'
                        })
                        : placeholderForSlot(slot);
                    staticCanvas.insertAt(object, baseIndex + index, false);
                    if (typeof object.setCoords === 'function') {
                        object.setCoords();
                    }
                });
            }

            function renderFabricFrame(canvas, frame, images) {
                if (!canvas || !frame || frame.renderer !== 'fabric-page' || !frame.fabric) {
                    return Promise.resolve(false);
                }
                var artboard = frame.fabric && frame.fabric.artboard ? frame.fabric.artboard : {};
                var width = Math.max(1, Number(frame.width || artboard.width || 1080));
                var height = Math.max(1, Number(frame.height || artboard.height || 1350));
                var imageMark = (Array.isArray(images) ? images : []).map(function (image) {
                    return image ? [image.dataset && image.dataset.gmImageId || '', image.width || image.naturalWidth || 0, image.height || image.naturalHeight || 0].join('x') : '';
                }).join('|');
                var mark = JSON.stringify([frame.id, frame.source_id, width, height, frame.fabric.objects && frame.fabric.objects.length, imageMark]);
                var cachedRender = renderMarks.get(canvas);
                if (cachedRender && cachedRender.mark === mark) {
                    return cachedRender.promise || Promise.resolve(true);
                }
                canvas.width = width;
                canvas.height = height;

                var renderPromise = loadFabric().then(function () {
                    return waitFonts(frame);
                }).then(function () {
                    return new Promise(function (resolve) {
                        var settled = false;
                        var staticCanvas = new fabric.StaticCanvas(canvas, {
                            width: width,
                            height: height,
                            renderOnAddRemove: false,
                            enableRetinaScaling: true
                        });
                        staticCanvas.requestRenderAll();
                        relaxFrameCanvasHeight(staticCanvas, frame);
                        var finish = function (value) {
                            if (settled) {
                                return;
                            }
                            settled = true;
                            window.clearTimeout(timer);
                            resolve(value);
                        };
                        var timer = window.setTimeout(function () {
                            finish(false);
                        }, 3500);
                        var objects = cloneFrameObjects(frame);
                        objects.forEach(normalizeFabricObject);
                        var payload = {
                            version: frame.fabric.version || '5.3.0',
                            objects: objects,
                            background: frame.fabric.backgroundColor || frame.fabric.background || '#ffffff',
                            backgroundColor: frame.fabric.backgroundColor || frame.fabric.background || '#ffffff',
                            backgroundImage: frame.fabric.backgroundImage || null
                        };
                        staticCanvas.loadFromJSON(payload, function () {
                            if (settled) {
                                return;
                            }
                            staticCanvas.getObjects().forEach(function (object) {
                                object.selectable = false;
                                object.evented = false;
                                object.dirty = true;
                                if (typeof object.setCoords === 'function') {
                                    object.setCoords();
                                }
                            });
                            replaceSlotsWithImages(staticCanvas, frame, Array.isArray(images) ? images : []);
                            staticCanvas.requestRenderAll();
                            relaxFrameCanvasHeight(staticCanvas, frame);
                            window.setTimeout(function () {
                                if (settled) {
                                    return;
                                }
                                staticCanvas.requestRenderAll();
                                relaxFrameCanvasHeight(staticCanvas, frame);
                                finish(true);
                            }, 80);
                        });
                    });
                }).then(function (rendered) {
                    if (!rendered) {
                        renderMarks.delete(canvas);
                    }
                    return rendered;
                }).catch(function () {
                    renderMarks.delete(canvas);
                    return false;
                });
                renderMarks.set(canvas, {
                    mark: mark,
                    promise: renderPromise
                });
                return renderPromise;
            }

            window.AdaAcaraGuestMemoryRenderFrame = renderFabricFrame;
            window.AdaAcaraGuestMemoryClearFrameRender = function (canvas) {
                if (canvas) {
                    renderMarks.delete(canvas);
                }
            };

            function refreshFramePickerCanvases() {
                if (!frames.length) {
                    return;
                }
                document.querySelectorAll('.aa-gm-frame-card').forEach(function (card) {
                    var canvas = card.querySelector('.aa-gm-frame-canvas');
                    var frame = frames[frameIndexForCard(card)];
                    renderFabricFrame(canvas, frame);
                });
            }

            window.addEventListener('load', function () {
                refreshFramePickerCanvases();
                var target = document.querySelector('[data-gm-frame-preview]');
                if (!target || !window.MutationObserver) {
                    return;
                }
                var observer = new MutationObserver(function () {
                    window.requestAnimationFrame(refreshFramePickerCanvases);
                });
                observer.observe(target, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class']
                });
            });
        })();
    </script>
</body>
</html>
