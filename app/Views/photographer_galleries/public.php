<?php
    helper(['url', 'aa_asset']);
    $gallery = is_array($gallery ?? null) ? $gallery : [];
    $photos = is_array($photos ?? null) ? $photos : [];
    $albums = is_array($albums ?? null) ? $albums : [];
    $hasAccess = ! empty($hasAccess);
    $accessError = (string) ($accessError ?? '');
    $selectedPhotoIds = array_map('intval', is_array($selectedPhotoIds ?? null) ? $selectedPhotoIds : []);
    $sharePhotoIds = array_map('intval', is_array($sharePhotoIds ?? null) ? $sharePhotoIds : []);
    $submittedPrintPhotoIds = array_map('intval', is_array($submittedPrintPhotoIds ?? null) ? $submittedPrintPhotoIds : []);
    $submittedSharePhotoIds = array_map('intval', is_array($submittedSharePhotoIds ?? null) ? $submittedSharePhotoIds : []);
    $familyShareUrl = trim((string) ($familyShareUrl ?? ''));
    $selectedLookup = array_fill_keys($selectedPhotoIds, true);
    $shareLookup = array_fill_keys($sharePhotoIds, true);
    $submittedPrintLookup = array_fill_keys($submittedPrintPhotoIds, true);
    $submittedShareLookup = array_fill_keys($submittedSharePhotoIds, true);
    $selectionReady = ! empty($selectionReady);
    $shareReady = ! empty($shareReady);
    $selectionEnabled = ! empty($gallery['selection_enabled']) && $selectionReady;
    $selectionLimit = max(1, (int) ($gallery['selection_limit'] ?? 30));
    $downloadEnabled = ! empty($gallery['download_enabled']);
    $isPrivateGallery = (string) ($gallery['privacy_mode'] ?? 'pin') === 'pin';
    $cover = trim((string) ($gallery['cover_photo'] ?? ''));
    $coverUrl = $cover !== '' ? base_url($cover) : '';
    $gallerySlug = (string) ($gallery['slug'] ?? '');
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
            'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
            'download' => '<path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 21h16"/>',
            'image' => '<rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="8.5" cy="10" r="1.5"/><path d="m21 16-5-5L5 19"/>',
            'check' => '<path d="m5 12 4 4L19 6"/>',
            'send' => '<path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4 20-7Z"/>',
            'grid' => '<rect x="4" y="4" width="6" height="6" rx="1.5"/><rect x="14" y="4" width="6" height="6" rx="1.5"/><rect x="4" y="14" width="6" height="6" rx="1.5"/><rect x="14" y="14" width="6" height="6" rx="1.5"/>',
            'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/>',
            'printer' => '<path d="M7 8V4h10v4"/><rect x="6" y="14" width="12" height="7" rx="1"/><path d="M6 17H4a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-2"/>',
            'message' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/>',
            'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.6 6.8-4.2M8.6 13.4l6.8 4.2"/>',
            'more' => '<circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>',
        ];

        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['image']) . '</svg>';
    };
?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc((string) ($gallery['title'] ?? 'Client Gallery')) ?> - AdaAcara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <style>
        *,*::before,*::after{box-sizing:border-box}
        html{min-height:100%}
        body{margin:0;min-height:100vh;background:#f8fbff;color:#152033;font-family:"Plus Jakarta Sans",ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        a{color:inherit;text-decoration:none}
        button,input{font:inherit}
        .pg-public svg{display:block;flex:0 0 auto;width:20px;height:20px}
        .pg-public .h-4{width:16px;height:16px}.pg-public .w-4{width:16px}.pg-public .h-5{width:20px;height:20px}.pg-public .w-5{width:20px}
        .pg-public .h-7{width:28px;height:28px}.pg-public .w-7{width:28px}.pg-public .h-10{width:40px;height:40px}.pg-public .w-10{width:40px}
        .pg-public{min-height:100vh;background:radial-gradient(circle at 8% 8%,rgba(234,221,255,.62),transparent 28%),radial-gradient(circle at 94% 18%,rgba(255,222,235,.52),transparent 30%),linear-gradient(135deg,#f8fbff 0%,#fff7fb 48%,#f3fffb 100%);padding:30px}
        .pg-wrap{width:min(100%,1480px);margin:0 auto}
        .pg-hero{display:grid;grid-template-columns:250px minmax(0,1fr) 280px;align-items:center;gap:36px;position:relative;overflow:hidden;border:1px solid rgba(148,163,184,.16);border-radius:28px;background:radial-gradient(circle at 42% 34%,rgba(255,228,236,.42),transparent 22%),radial-gradient(circle at 75% 32%,rgba(231,219,255,.55),transparent 26%),rgba(255,255,255,.88);box-shadow:0 24px 70px rgba(79,70,229,.10);padding:28px 42px}
        .pg-hero::after{content:"";position:absolute;left:28px;top:86px;width:72px;height:44px;border:2px dashed rgba(167,139,250,.34);border-right:0;border-bottom:0;border-radius:50%;transform:rotate(-18deg);pointer-events:none}
        .pg-hero-cover{position:relative;height:260px;border-radius:24px;overflow:hidden;background:linear-gradient(135deg,#f5f3ff,#fff,#fff1f2);box-shadow:0 20px 40px rgba(15,23,42,.11)}
        .pg-hero-cover img{width:100%;height:100%;object-fit:cover}
        .pg-cover-count{position:absolute;left:16px;bottom:16px;display:grid;min-width:64px;min-height:64px;place-items:center;border-radius:18px;background:rgba(255,255,255,.94);box-shadow:0 10px 26px rgba(15,23,42,.14);padding:8px;text-align:center;text-transform:uppercase}
        .pg-cover-count strong{display:block;font-size:25px;line-height:1;font-weight:950;color:#142033}
        .pg-cover-count span{display:block;margin-top:4px;font-size:10px;font-weight:950;letter-spacing:.08em;color:#7c88a3}
        .pg-hero-cover-empty{display:grid;height:100%;place-items:center;color:#a78bfa}
        .pg-hero-cover-empty svg{width:64px;height:64px}
        .pg-hero-content{position:relative;z-index:1;padding:0}
        .pg-eyebrow{margin:0;font-size:12px;font-weight:950;letter-spacing:.16em;text-transform:uppercase;color:#8f65df}
        .pg-title{margin:14px 0 0;font-size:42px;line-height:1.08;font-weight:950;letter-spacing:-.02em;color:#142033}
        .pg-meta{margin:14px 0 0;font-size:14px;font-weight:900;line-height:1.6;color:#64748b}
        .pg-statbar{display:flex;flex-wrap:wrap;gap:14px;margin-top:26px}
        .pg-pill{display:inline-flex;min-height:44px;align-items:center;justify-content:center;gap:8px;border:1px solid rgba(148,163,184,.20);border-radius:16px;background:rgba(255,255,255,.90);box-shadow:0 12px 26px rgba(15,23,42,.05);padding:10px 16px;font-size:13px;font-weight:950;color:#475569}
        .pg-date-pill{display:none}
        .pg-download-pill{min-width:220px;border:0;background:linear-gradient(135deg,#b979ff,#7953dc);box-shadow:0 15px 30px rgba(143,101,223,.24);color:#fff}
        .pg-privacy-note{position:relative;z-index:1;display:grid;gap:8px;justify-self:end;width:100%;max-width:260px;border:1px dashed rgba(143,101,223,.42);border-radius:20px;background:rgba(255,255,255,.72);padding:18px 20px;color:#64748b}
        .pg-privacy-note strong{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:950;color:#64748b}
        .pg-privacy-note p{margin:0;font-size:12px;font-weight:800;line-height:1.55}
        .pg-pin-card{position:relative;display:grid;min-height:calc(100vh - 44px);place-items:center;overflow:hidden}
        .pg-pin-card::before,.pg-pin-card::after{content:"";position:absolute;border-radius:999px;filter:blur(18px);opacity:.72;pointer-events:none}
        .pg-pin-card::before{width:280px;height:280px;left:-90px;top:8%;background:#ffd6e6}
        .pg-pin-card::after{width:320px;height:320px;right:-110px;bottom:4%;background:#e9dcff}
        .pg-pin-inner{position:relative;width:min(100%,720px);min-height:620px;border:2px solid rgba(161,122,235,.72);border-radius:46px;background:radial-gradient(circle at 20% 8%,rgba(255,222,235,.95),transparent 30%),radial-gradient(circle at 88% 18%,rgba(233,220,255,.86),transparent 32%),linear-gradient(180deg,rgba(255,255,255,.96),rgba(255,252,254,.94));padding:0 36px 30px;box-shadow:0 24px 76px rgba(143,101,223,.18);text-align:center;overflow:hidden}
        .pg-pin-inner::before{content:"";position:absolute;inset:auto -20px -8px -20px;height:92px;background:radial-gradient(circle at 12% 80%,#eadfff 0 18%,transparent 19%),radial-gradient(circle at 86% 82%,#efe6ff 0 22%,transparent 23%);pointer-events:none}
        .pg-pin-sparkles{position:absolute;inset:0;pointer-events:none;color:#f79cbd;font-size:28px;font-weight:900}
        .pg-pin-sparkles span{position:absolute;opacity:.75}
        .pg-pin-sparkles span:nth-child(1){left:12%;top:17%;color:#f985b0}.pg-pin-sparkles span:nth-child(2){right:14%;top:22%;color:#c49cff}.pg-pin-sparkles span:nth-child(3){left:20%;bottom:22%;color:#9b75e9}.pg-pin-sparkles span:nth-child(4){right:20%;bottom:20%;color:#f985b0}
        .pg-pin-art{position:relative;height:170px;margin:0 auto -2px}
        .pg-polaroid{position:absolute;top:30px;width:100px;height:126px;border:11px solid #fff;border-bottom-width:28px;border-radius:15px;background:linear-gradient(135deg,#ffd5e2,#f8f2ff);box-shadow:0 14px 30px rgba(89,64,126,.13)}
        .pg-polaroid::before{content:"";position:absolute;left:50%;top:22px;width:46px;height:46px;border-radius:999px;background:#2c2a35;transform:translateX(-50%)}
        .pg-polaroid::after{content:"";position:absolute;left:25px;right:25px;bottom:18px;height:36px;border-radius:22px 22px 8px 8px;background:#293246}
        .pg-polaroid-left{left:calc(50% - 132px);transform:rotate(-10deg)}
        .pg-polaroid-right{right:calc(50% - 132px);transform:rotate(11deg)}
        .pg-polaroid-right::before{background:#68313a}.pg-polaroid-right::after{background:#ffd3df}
        .pg-camera{position:absolute;left:50%;top:72px;width:180px;height:106px;border-radius:30px;background:linear-gradient(135deg,#cdb8ff,#8f65df);box-shadow:0 18px 38px rgba(143,101,223,.28);transform:translateX(-50%)}
        .pg-camera::before{content:"";position:absolute;left:58px;top:-18px;width:68px;height:28px;border-radius:16px 16px 8px 8px;background:#d8caff}
        .pg-camera::after{content:"";position:absolute;left:50%;top:24px;width:64px;height:64px;border-radius:999px;border:10px solid #f4edff;background:radial-gradient(circle at 34% 30%,#fff 0 7px,transparent 8px),radial-gradient(circle at 50% 50%,#15192a 0 24px,#3f365d 25px);transform:translateX(-50%);box-shadow:inset 0 0 0 6px rgba(255,255,255,.28)}
        .pg-camera-dot{position:absolute;left:24px;top:34px;width:22px;height:10px;border-radius:999px;background:#dd7797;box-shadow:102px 0 0 rgba(255,255,255,.35)}
        .pg-pin-badge{position:relative;z-index:1;display:inline-flex;align-items:center;justify-content:center;gap:10px;margin-top:6px;border:1px solid rgba(196,169,240,.72);border-radius:999px;background:rgba(255,255,255,.82);box-shadow:0 8px 22px rgba(143,101,223,.15);padding:11px 28px;font-size:16px;font-weight:950;letter-spacing:.16em;text-transform:uppercase;color:#8f65df}
        .pg-pin-heading{position:relative;z-index:1;margin:22px 0 0;font-size:42px;line-height:1.08;font-weight:950;letter-spacing:-.02em;color:#8f65df}
        .pg-pin-line{position:relative;z-index:1;width:min(100%,360px);height:22px;margin:10px auto 0;border-bottom:4px solid #ffa9ca;border-radius:50%;opacity:.72}
        .pg-pin-copy{position:relative;z-index:1;margin:24px auto 0;max-width:430px;font-size:19px;font-weight:800;line-height:1.5;color:#53627f}
        .pg-pin-copy strong{color:#8f65df}
        .pg-pin-form{position:relative;z-index:1;display:grid;justify-items:center;gap:22px;margin-top:24px}
        .pg-pin-fields{display:grid;grid-template-columns:repeat(4,72px);gap:14px}
        .pg-pin-box{width:72px;height:78px;border:2px solid #e4d2ff;border-radius:18px;background:rgba(255,255,255,.82);box-shadow:inset 0 0 0 1px rgba(255,255,255,.72);font-size:40px;font-weight:950;text-align:center;color:#8f65df;outline:none}
        .pg-pin-box:focus{border-color:#9d73ec;box-shadow:0 0 0 5px rgba(143,101,223,.14)}
        .pg-pin-box::placeholder{color:#b999ed}
        .pg-pin-hidden{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
        .pg-pin-submit{width:min(100%,420px);min-height:60px;border-radius:999px;font-size:18px;background:linear-gradient(135deg,#955ce8,#7f4bd7);box-shadow:0 18px 34px rgba(143,101,223,.26)}
        .pg-pin-note{position:relative;z-index:1;display:inline-flex;align-items:center;justify-content:center;gap:8px;margin-top:56px;font-size:14px;font-weight:800;color:#7780ae}
        .pg-pin-tip{position:absolute;right:50px;top:374px;z-index:2;width:182px;border-radius:22px;background:#ffc3d6;color:#ef6b96;padding:14px 16px;font-size:15px;font-weight:950;line-height:1.28;box-shadow:0 12px 24px rgba(239,107,150,.18);transform:rotate(8deg)}
        .pg-pin-tip::after{content:"";position:absolute;left:44px;bottom:-18px;border-width:20px 14px 0 0;border-style:solid;border-color:#ffc3d6 transparent transparent transparent}
        .pg-pin-icon{display:inline-grid;place-items:center;color:currentColor}
        .pg-input{width:100%;height:50px;border:1px solid rgba(148,163,184,.30);border-radius:18px;background:#fff;padding:0 16px;font-size:18px;font-weight:900;text-align:center;letter-spacing:.12em;color:#142033;outline:none}
        .pg-input:focus{border-color:#8f65df;box-shadow:0 0 0 4px rgba(143,101,223,.13)}
        .pg-btn{display:inline-flex;min-height:44px;align-items:center;justify-content:center;gap:9px;border:0;border-radius:16px;padding:10px 16px;font-size:13px;font-weight:950;line-height:1.15;cursor:pointer;transition:.16s ease}
        .pg-btn:disabled{cursor:not-allowed;opacity:.62}
        .pg-btn.is-loading{pointer-events:none}
        .pg-btn-primary{background:#8f65df;color:#fff;box-shadow:0 14px 32px rgba(143,101,223,.22)}
        .pg-btn-muted{border:1px solid rgba(148,163,184,.24);background:#fff;color:#475569}
        .pg-tabs{display:flex;flex-wrap:wrap;gap:18px;margin-top:28px}
        .pg-tab{display:inline-flex;min-width:124px;min-height:52px;align-items:center;justify-content:center;gap:10px;border:1px solid rgba(148,163,184,.20);border-radius:999px;background:rgba(255,255,255,.90);box-shadow:0 12px 26px rgba(15,23,42,.05);padding:12px 20px;font-size:13px;font-weight:950;color:#223047;cursor:pointer}
        .pg-tab[hidden]{display:none!important}
        .pg-tab.is-active{border-color:#8f65df;background:#8f65df;color:#fff;box-shadow:0 12px 26px rgba(143,101,223,.18)}
        .pg-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:5px;margin-top:30px;opacity:1;transition:opacity .16s ease,transform .16s ease}
        .pg-grid.is-switching{opacity:.38;transform:translateY(6px);pointer-events:none}
        .pg-photo{position:relative;overflow:visible;border:1px solid rgba(148,163,184,.16);border-radius:18px;background:#fff;box-shadow:0 18px 42px rgba(15,23,42,.07)}
        .pg-photo.is-hidden-by-tab,.pg-photo.is-paginated-hidden{display:none}
        .pg-photo img{display:block;width:100%;aspect-ratio:1.46/1;object-fit:cover;border-radius:18px 18px 0 0;background:#eef2f7;cursor:zoom-in}
        .pg-photo-badge{position:absolute;left:12px;top:12px;z-index:1;display:inline-flex;max-width:calc(100% - 64px);align-items:center;gap:6px;border-radius:999px;background:linear-gradient(135deg,#9b6df0,#764dd8);box-shadow:0 12px 24px rgba(118,77,216,.24);padding:7px 10px;font-size:11px;font-weight:950;line-height:1;color:#fff}
        .pg-photo-badge span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .pg-photo-statuses{position:absolute;left:12px;top:46px;z-index:2;display:flex;max-width:calc(100% - 64px);flex-wrap:wrap;gap:6px;pointer-events:none}
        .pg-photo:not(.has-album-badge) .pg-photo-statuses{top:12px}
        .pg-photo-status-pill{display:inline-flex;align-items:center;gap:5px;border:1px solid rgba(255,255,255,.55);border-radius:999px;background:rgba(255,255,255,.92);box-shadow:0 10px 20px rgba(15,23,42,.12);padding:5px 8px;font-size:10px;font-weight:950;line-height:1;color:#475569}
        .pg-photo-status-pill.is-favorite{color:#e11d48}
        .pg-photo-status-pill.is-print{color:#047857}
        .pg-photo-status-pill.is-share{color:#2563eb}
        .pg-photo-status-pill.is-sent{background:rgba(143,101,223,.92);color:#fff}
        .pg-photo-check{position:absolute;right:12px;top:12px;z-index:2;display:grid;width:34px;height:34px;place-items:center;border:1px solid rgba(148,163,184,.22);border-radius:12px;background:rgba(255,255,255,.92);box-shadow:0 10px 24px rgba(15,23,42,.12);cursor:pointer}
        .pg-photo-check input{width:16px;height:16px;margin:0;accent-color:#8f65df;cursor:pointer}
        .pg-photo-actions{display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:10px;padding:12px}
        .pg-photo-actions .pg-btn span,.pg-tab{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .pg-photo-menu-wrap{position:relative;min-width:0}
        .pg-photo-menu-wrap .pg-btn{width:100%}
        .pg-photo-menu{position:absolute;right:0;bottom:calc(100% + 8px);z-index:8;display:grid;width:220px;border:1px solid rgba(148,163,184,.20);border-radius:18px;background:#fff;box-shadow:0 18px 42px rgba(15,23,42,.14);padding:8px;opacity:0;pointer-events:none;transform:translateY(8px) scale(.98);transform-origin:bottom right;visibility:hidden;transition:opacity .18s ease,transform .18s ease,visibility 0s linear .18s}
        .pg-photo-menu.is-open{opacity:1;pointer-events:auto;transform:translateY(0) scale(1);visibility:visible;transition-delay:0s}
        .pg-photo-menu button,.pg-photo-menu a{display:flex;width:100%;align-items:center;gap:9px;border:0;border-radius:13px;background:transparent;padding:10px 11px;font-size:12px;font-weight:900;color:#334155;text-align:left;cursor:pointer}
        .pg-photo-menu button:hover,.pg-photo-menu a:hover{background:#f5f3ff;color:#7c4fe0}
        .pg-photo-menu button:disabled{cursor:not-allowed;opacity:.48}
        .pg-photo-menu button:disabled:hover{background:transparent;color:#334155}
        .pg-photo-menu button.is-selected{background:#ecfdf5;color:#047857}
        .pg-photo-menu button.is-share-selected{background:#eff6ff;color:#2563eb}
        .pg-select-btn,.pg-favorite-btn{min-width:0}
        .pg-select-btn.is-selected{background:#ecfdf5;color:#047857}
        .pg-favorite-btn.is-favorite{background:#fff1f2;color:#e11d48}
        .pg-photo-actions .pg-btn{min-height:38px;border-radius:14px;padding:8px 10px;color:#7c4fe0}
        .pg-submit-bar{position:sticky;bottom:16px;z-index:5;display:flex;max-height:0;align-items:center;justify-content:space-between;gap:12px;margin-top:0;overflow:hidden;border:1px solid rgba(196,181,253,0);border-radius:24px;background:rgba(255,255,255,.92);box-shadow:0 16px 40px rgba(79,70,229,0);padding:0 12px;opacity:0;pointer-events:none;transform:translateY(12px) scale(.98);visibility:hidden;transition:opacity .22s ease,transform .22s ease,max-height .24s ease,margin-top .22s ease,padding .22s ease,border-color .22s ease,box-shadow .22s ease,visibility 0s linear .24s}
        .pg-submit-bar.is-visible{max-height:220px;margin-top:18px;border-color:rgba(196,181,253,.5);box-shadow:0 16px 40px rgba(79,70,229,.14);padding:12px;opacity:1;pointer-events:auto;transform:translateY(0) scale(1);visibility:visible;transition-delay:0s}
        .pg-share-bar{bottom:86px}
        .pg-submit-actions{display:flex;flex-wrap:wrap;gap:10px}
        .pg-bulk-bar{position:relative;z-index:4;display:flex;max-height:0;align-items:center;justify-content:space-between;gap:12px;margin-top:0;overflow:hidden;border:1px solid rgba(143,101,223,0);border-radius:22px;background:rgba(255,255,255,.92);box-shadow:0 16px 38px rgba(79,70,229,0);padding:0 12px;opacity:0;pointer-events:none;transform:translateY(-8px) scale(.985);visibility:hidden;transition:opacity .22s ease,transform .22s ease,max-height .24s ease,margin-top .22s ease,padding .22s ease,border-color .22s ease,box-shadow .22s ease,visibility 0s linear .24s}
        .pg-bulk-bar.is-visible{z-index:30;max-height:180px;margin-top:18px;overflow:visible;border-color:rgba(143,101,223,.22);box-shadow:0 16px 38px rgba(79,70,229,.10);padding:12px;opacity:1;pointer-events:auto;transform:translateY(0) scale(1);visibility:visible;transition-delay:0s}
        .pg-bulk-left,.pg-bulk-actions{display:flex;align-items:center;flex-wrap:wrap;gap:10px}
        .pg-bulk-check{display:inline-flex;align-items:center;gap:9px;border:1px solid rgba(148,163,184,.22);border-radius:999px;background:#fff;padding:10px 13px;font-size:12px;font-weight:950;color:#475569}
        .pg-bulk-check input{accent-color:#8f65df}
        .pg-bulk-menu-wrap{position:relative}
        .pg-bulk-menu{position:absolute;right:0;top:calc(100% + 8px);z-index:10;display:grid;width:230px;border:1px solid rgba(148,163,184,.20);border-radius:18px;background:#fff;box-shadow:0 18px 42px rgba(15,23,42,.14);padding:8px;opacity:0;pointer-events:none;transform:translateY(-8px) scale(.98);transform-origin:top right;visibility:hidden;transition:opacity .18s ease,transform .18s ease,visibility 0s linear .18s}
        .pg-bulk-menu.is-open{opacity:1;pointer-events:auto;transform:translateY(0) scale(1);visibility:visible;transition-delay:0s}
        .pg-bulk-menu button{display:flex;width:100%;align-items:center;gap:9px;border:0;border-radius:13px;background:transparent;padding:10px 11px;font-size:12px;font-weight:900;color:#334155;text-align:left;cursor:pointer}
        .pg-bulk-menu button:hover{background:#f5f3ff;color:#7c4fe0}
        .pg-bulk-menu button:disabled{cursor:not-allowed;opacity:.48}
        .pg-bulk-menu button:disabled:hover{background:transparent;color:#334155}
        .pg-pagination{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:26px;border:1px solid rgba(148,163,184,.12);border-radius:20px;background:rgba(255,255,255,.90);box-shadow:0 18px 42px rgba(15,23,42,.06);padding:16px 20px;opacity:1;transition:opacity .16s ease,transform .16s ease}
        .pg-pagination.is-switching{opacity:.48;transform:translateY(4px)}
        .pg-pagination-info{font-size:13px;font-weight:900;color:#64748b}
        .pg-pagination-actions{display:flex;align-items:center;gap:8px}
        .pg-page-number{display:inline-flex;min-width:46px;height:46px;align-items:center;justify-content:center;border:1px solid rgba(148,163,184,.18);border-radius:14px;background:#8f65df;font-size:13px;font-weight:950;color:#fff}
        .pg-modal-backdrop{position:fixed;inset:0;z-index:60;display:grid;place-items:center;background:rgba(15,23,42,0);padding:18px;opacity:0;pointer-events:none;visibility:hidden;transition:opacity .2s ease,background-color .2s ease,visibility 0s linear .2s}
        .pg-modal-backdrop.is-open{background:rgba(15,23,42,.42);opacity:1;pointer-events:auto;visibility:visible;transition-delay:0s}
        .pg-modal{width:min(100%,430px);border:1px solid rgba(148,163,184,.24);border-radius:28px;background:#fff;padding:24px;box-shadow:0 26px 80px rgba(15,23,42,.22);transform:translateY(12px) scale(.97);transition:transform .22s ease}
        .pg-modal-backdrop.is-open .pg-modal{transform:translateY(0) scale(1)}
        .pg-modal h2{margin:0;font-size:22px;font-weight:950;color:#142033}
        .pg-modal p{margin:10px 0 0;font-size:14px;font-weight:700;line-height:1.6;color:#64748b}
        .pg-lightbox-backdrop{position:fixed;inset:0;z-index:80;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,0);backdrop-filter:blur(0);padding:22px;opacity:0;pointer-events:none;visibility:hidden;transition:opacity .22s ease,background-color .22s ease,backdrop-filter .22s ease,visibility 0s linear .22s}
        .pg-lightbox-backdrop.is-open{background:rgba(15,23,42,.42);backdrop-filter:blur(14px);opacity:1;pointer-events:auto;visibility:visible;transition-delay:0s}
        .pg-lightbox{position:relative;width:min(100%,980px);max-height:calc(100vh - 44px);display:grid;place-items:center}
        .pg-lightbox img{display:block;max-width:100%;max-height:calc(100vh - 44px);border-radius:24px;box-shadow:0 28px 90px rgba(15,23,42,.34);object-fit:contain;background:#fff;transform:translateY(10px) scale(.98);transition:transform .22s ease}
        .pg-lightbox-backdrop.is-open .pg-lightbox img{transform:translateY(0) scale(1)}
        .pg-lightbox-close{position:absolute;right:12px;top:12px;z-index:2;width:42px;height:42px;border:0;border-radius:999px;background:rgba(255,255,255,.92);box-shadow:0 12px 30px rgba(15,23,42,.18);font-size:24px;font-weight:900;line-height:1;color:#142033;cursor:pointer}
        .pg-modal textarea{width:100%;min-height:140px;margin-top:14px;resize:vertical;border:1px solid rgba(148,163,184,.28);border-radius:18px;background:#fff;padding:14px 16px;font-size:14px;font-weight:700;color:#142033;outline:none}
        .pg-modal textarea:focus{border-color:#8f65df;box-shadow:0 0 0 4px rgba(143,101,223,.12)}
        .pg-choice-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:16px}
        .pg-choice{display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid rgba(148,163,184,.24);border-radius:16px;background:#fff;padding:12px;font-size:13px;font-weight:950;color:#475569;cursor:pointer}
        .pg-choice input{accent-color:#8f65df}
        .pg-share-pin-fields{display:none;grid-template-columns:repeat(4,48px);justify-content:center;gap:10px;margin-top:16px}
        .pg-share-pin-fields.is-visible{display:grid}
        .pg-share-pin-box{width:48px;height:52px;border:1px solid rgba(143,101,223,.30);border-radius:15px;background:#fff;font-size:24px;font-weight:950;text-align:center;color:#8f65df;outline:none}
        .pg-share-pin-box:focus{border-color:#8f65df;box-shadow:0 0 0 4px rgba(143,101,223,.12)}
        .pg-share-pin-note{display:none;margin:8px 0 0;text-align:center;font-size:11px;font-weight:900;line-height:1.45;color:#7c3aed}
        .pg-share-pin-note.is-visible{display:block}
        .pg-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px}
        .pg-confirm-share-btn svg{width:20px!important;height:20px!important}
        .pg-confirm-share-btn{gap:8px}
        .pg-empty{display:grid;min-height:280px;place-items:center;margin-top:22px;border:1px dashed rgba(148,163,184,.32);border-radius:28px;background:rgba(255,255,255,.62);text-align:center;color:#64748b}
        .pg-error{margin-top:14px;border:1px solid #fecdd3;border-radius:18px;background:#fff1f2;padding:12px 14px;font-size:13px;font-weight:800;color:#be123c}
        @media(max-width:1180px){.pg-hero{grid-template-columns:220px minmax(0,1fr);gap:28px}.pg-privacy-note{grid-column:1/-1;justify-self:start;max-width:100%}.pg-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
        @media(max-width:940px){.pg-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:760px){.pg-pin-inner{min-height:570px;border-radius:34px;padding:0 18px 22px}.pg-pin-art{height:140px}.pg-polaroid{top:26px;width:78px;height:100px;border-width:9px;border-bottom-width:23px}.pg-polaroid-left{left:calc(50% - 98px)}.pg-polaroid-right{right:calc(50% - 98px)}.pg-camera{top:60px;width:140px;height:86px;border-radius:24px}.pg-camera::before{left:46px;top:-15px;width:54px;height:22px}.pg-camera::after{width:52px;height:52px;border-width:8px;top:19px}.pg-camera-dot{left:18px;top:27px;width:18px;height:8px;box-shadow:80px 0 0 rgba(255,255,255,.35)}.pg-pin-badge{padding:10px 18px;font-size:13px}.pg-pin-heading{font-size:30px}.pg-pin-copy{font-size:16px}.pg-pin-fields{grid-template-columns:repeat(4,56px);gap:9px}.pg-pin-box{width:56px;height:62px;font-size:30px}.pg-pin-submit{min-height:56px;font-size:17px}.pg-pin-tip{display:none}.pg-pin-note{margin-top:42px;font-size:12px}}
        @media(max-width:680px){.pg-public{padding:0;background:linear-gradient(180deg,#fbf9ff 0%,#fff 34%,#fff 100%)}.pg-wrap{width:100%;margin:0}.pg-hero{display:grid;grid-template-columns:112px minmax(0,1fr);gap:18px;margin:0;border:0;border-bottom:1px solid rgba(196,181,253,.35);border-radius:0 0 28px 28px;background:radial-gradient(circle at 88% 32%,rgba(198,169,255,.34),transparent 26%),linear-gradient(135deg,#fff 0%,#fbf7ff 52%,#f8fbff 100%);box-shadow:0 18px 42px rgba(79,70,229,.10);padding:28px 18px 26px}.pg-hero::after{content:"";position:absolute;right:44px;top:88px;width:62px;height:42px;border:2px dashed rgba(167,139,250,.42);border-left:0;border-bottom:0;border-radius:50%;transform:rotate(20deg);pointer-events:none}.pg-privacy-note{display:none}.pg-hero-cover{position:relative;height:172px;border-radius:24px;overflow:hidden;box-shadow:0 18px 36px rgba(15,23,42,.12)}.pg-cover-count{position:absolute;left:12px;bottom:12px;display:grid;min-width:58px;min-height:58px;place-items:center;border-radius:18px;background:rgba(255,255,255,.94);box-shadow:0 10px 26px rgba(15,23,42,.14);padding:8px;color:#7f62ca;text-align:center;text-transform:uppercase}.pg-cover-count strong{display:block;font-size:24px;line-height:1;font-weight:950;color:#142033}.pg-cover-count span{display:block;margin-top:3px;font-size:10px;font-weight:950;letter-spacing:.08em;color:#7c88a3}.pg-hero-content{padding:0;align-self:center}.pg-eyebrow{display:flex;align-items:center;gap:7px;font-size:11px;letter-spacing:.12em}.pg-title{margin-top:12px;font-size:29px;line-height:1.12;letter-spacing:-.01em}.pg-meta{margin-top:12px;font-size:14px;font-weight:900}.pg-statbar{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-top:14px}.pg-pill{min-height:38px;justify-content:center;background:#fff;border-color:rgba(148,163,184,.22);box-shadow:0 10px 22px rgba(15,23,42,.06);padding:8px 10px;font-size:11px}.pg-date-pill{display:inline-flex}.pg-download-pill{grid-column:1/-1;background:linear-gradient(135deg,#b979ff,#7953dc);border:0;color:#fff;box-shadow:0 14px 28px rgba(143,101,223,.23);font-size:14px}.pg-tabs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:24px 18px 0}.pg-tab{min-width:0;min-height:50px;gap:9px;background:#fff;border-color:rgba(148,163,184,.20);box-shadow:0 10px 24px rgba(15,23,42,.05);padding:10px 12px;font-size:13px}.pg-tab.is-active{background:linear-gradient(135deg,#9d6af2,#764dd8);border-color:transparent;box-shadow:0 14px 30px rgba(143,101,223,.23)}.pg-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:5px;margin:24px 18px 0}.pg-photo{border-radius:20px;box-shadow:0 16px 34px rgba(15,23,42,.10)}.pg-photo img{aspect-ratio:1.18/1}.pg-photo-badge{left:9px;top:9px;max-width:calc(100% - 18px);padding:7px 9px;font-size:10px}.pg-photo-statuses{left:9px;top:42px;max-width:calc(100% - 58px);gap:4px}.pg-photo:not(.has-album-badge) .pg-photo-statuses{top:9px}.pg-photo-status-pill{padding:4px 6px;font-size:9px}.pg-photo-actions{grid-template-columns:1fr 1fr 38px;gap:7px;padding:9px}.pg-photo-actions .pg-btn{min-height:36px;border-radius:14px;padding:7px 8px;font-size:11px}.pg-download{width:38px;background:linear-gradient(135deg,#9d6af2,#764dd8);border:0;color:#fff}.pg-submit-bar{left:14px;right:14px;align-items:stretch;flex-direction:column;margin:18px;border-radius:22px}.pg-pagination{margin:24px 18px 0;border:0;border-radius:24px;background:#fff;box-shadow:0 16px 36px rgba(15,23,42,.08);padding:16px}.pg-pagination-actions{width:100%;justify-content:space-between}.pg-pagination-actions .pg-btn{width:46px;min-height:42px;padding:0;border-radius:999px;font-size:0}.pg-pagination-actions .pg-btn::before{font-size:18px;line-height:1}.pg-pagination-actions [data-client-photo-page-prev]::before{content:"<"}.pg-pagination-actions [data-client-photo-page-next]::before{content:">"}.pg-page-number{min-width:78px;border-radius:999px;background:#f5f3ff;color:#142033}.pg-modal{border-radius:24px}}
        @media(max-width:680px){.pg-photo-actions{grid-template-columns:1fr 1fr}.pg-photo-menu{right:0;width:min(230px,calc(100vw - 32px))}.pg-photo-menu.is-mobile-fixed{position:fixed;right:auto;bottom:auto;z-index:50;transform-origin:bottom right}.pg-photo-actions .pg-btn span{display:inline;max-width:72px}.pg-share-bar{bottom:112px}.pg-submit-actions{display:grid;grid-template-columns:1fr;width:100%}}
        @media(max-width:680px){.pg-bulk-bar{align-items:stretch;flex-direction:column;margin:18px}.pg-bulk-left,.pg-bulk-actions{display:grid;grid-template-columns:1fr;width:100%}.pg-bulk-menu{right:0;width:min(240px,calc(100vw - 40px))}}
        @media(max-width:680px){.pg-submit-bar{position:static;bottom:auto;left:auto;right:auto;z-index:1;width:auto;margin:16px 18px 0}.pg-share-bar{bottom:auto}.pg-submit-bar+.pg-submit-bar{margin-top:12px}.pg-submit-bar .pg-pill{width:100%}.pg-submit-bar .pg-btn{width:100%}}
        @media(max-width:680px){.pg-submit-bar{position:sticky;left:auto;right:auto;bottom:12px;z-index:12;width:calc(100% - 36px);margin:12px 18px 0;gap:8px;border-radius:18px;padding:9px;box-shadow:0 14px 30px rgba(79,70,229,.15);backdrop-filter:blur(12px)}.pg-share-bar{bottom:96px}.pg-submit-bar .pg-pill{min-height:32px;padding:6px 10px;font-size:10px}.pg-submit-bar .pg-btn{min-height:38px;border-radius:14px;padding:8px 10px;font-size:11px}.pg-submit-actions{gap:8px}.pg-submit-bar svg{width:15px;height:15px}}
        @media(max-width:680px){.pg-lightbox-backdrop{padding:14px}.pg-lightbox img{border-radius:18px}.pg-lightbox-close{right:8px;top:8px;width:38px;height:38px;font-size:22px}}
        @media(max-width:680px){.pg-submit-bar:not(.is-visible),.pg-bulk-bar:not(.is-visible){margin-top:0;padding-top:0;padding-bottom:0;border-color:transparent;box-shadow:none}.pg-submit-bar.is-visible{padding:9px}.pg-bulk-bar.is-visible{padding:12px}}
        @media(max-width:680px){.pg-bulk-bar.is-visible{max-height:260px;padding:10px}.pg-bulk-left,.pg-bulk-actions{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.pg-bulk-check,.pg-bulk-bar .pg-pill,.pg-bulk-bar .pg-btn{width:100%;min-height:36px;padding:7px 9px;font-size:10px}.pg-bulk-check{justify-content:center}.pg-bulk-menu-wrap{min-width:0}.pg-bulk-menu-wrap>.pg-btn{width:100%}.pg-bulk-menu{top:auto;right:0;bottom:calc(100% + 8px);transform-origin:bottom right}}
        @media(prefers-reduced-motion:reduce){.pg-grid,.pg-pagination,.pg-photo-menu,.pg-bulk-menu,.pg-submit-bar,.pg-bulk-bar,.pg-modal-backdrop,.pg-modal,.pg-lightbox-backdrop,.pg-lightbox img{transition:none!important}}
    </style>
</head>
<body>
<main class="pg-public">
    <div class="pg-wrap">
        <?php if (! $hasAccess): ?>
            <section class="pg-pin-card">
                <div class="pg-pin-inner">
                    <div class="pg-pin-sparkles" aria-hidden="true">
                        <span>&hearts;</span>
                        <span>&#10022;</span>
                        <span>&#10023;</span>
                        <span>&hearts;</span>
                    </div>
                    <div class="pg-pin-art" aria-hidden="true">
                        <span class="pg-polaroid pg-polaroid-left"></span>
                        <span class="pg-polaroid pg-polaroid-right"></span>
                        <span class="pg-camera"><span class="pg-camera-dot"></span></span>
                    </div>
                    <span class="pg-pin-badge"><span class="pg-pin-icon"><?= $icon('lock', 'h-5 w-5') ?></span>Private Gallery</span>
                    <h1 class="pg-pin-heading"><?= esc((string) ($gallery['title'] ?? 'Client Gallery')) ?></h1>
                    <div class="pg-pin-line" aria-hidden="true"></div>
                    <p class="pg-pin-copy">Masukkan <strong>PIN</strong> dari fotografer untuk membuka gallery.</p>
                    <div class="pg-pin-tip">psst... PIN ada di kartu foto ya!</div>
                    <?php if ($accessError !== ''): ?>
                        <div class="pg-error"><?= esc($accessError) ?></div>
                    <?php endif; ?>
                    <form class="pg-pin-form" action="<?= site_url('gallery/' . $gallerySlug) ?>" method="post" data-pin-form>
                        <?= csrf_field() ?>
                        <input class="pg-pin-hidden" name="pin" inputmode="numeric" autocomplete="one-time-code" required data-pin-hidden>
                        <div class="pg-pin-fields" aria-label="Masukkan PIN">
                            <input class="pg-pin-box" type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" placeholder="&bull;" aria-label="Digit PIN 1" data-pin-box>
                            <input class="pg-pin-box" type="text" inputmode="numeric" maxlength="1" placeholder="&bull;" aria-label="Digit PIN 2" data-pin-box>
                            <input class="pg-pin-box" type="text" inputmode="numeric" maxlength="1" placeholder="&bull;" aria-label="Digit PIN 3" data-pin-box>
                            <input class="pg-pin-box" type="text" inputmode="numeric" maxlength="1" placeholder="&bull;" aria-label="Digit PIN 4" data-pin-box>
                        </div>
                        <button class="pg-btn pg-btn-primary pg-pin-submit" type="submit"><?= $icon('lock', 'h-5 w-5') ?>Buka Gallery</button>
                    </form>
                    <p class="pg-pin-note"><?= $icon('lock', 'h-4 w-4') ?>Aman &amp; hanya untuk tamu undangan</p>
                </div>
            </section>
        <?php else: ?>
            <section class="pg-hero">
                <div class="pg-hero-cover">
                    <?php if ($coverUrl !== ''): ?>
                        <img src="<?= esc($coverUrl, 'attr') ?>" alt="<?= esc((string) ($gallery['title'] ?? 'Client Gallery'), 'attr') ?>" loading="eager">
                    <?php else: ?>
                        <div class="pg-hero-cover-empty"><?= $icon('image') ?></div>
                    <?php endif; ?>
                    <span class="pg-cover-count"><strong><?= count($photos) ?></strong><span>Foto</span></span>
                </div>
                <div class="pg-hero-content">
                    <p class="pg-eyebrow"><?= $icon('image', 'h-4 w-4') ?>Client Photo Gallery</p>
                    <h1 class="pg-title"><?= esc((string) ($gallery['title'] ?? 'Client Gallery')) ?></h1>
                    <p class="pg-meta"><?= esc((string) ($gallery['studio_name'] ?? '')) ?><?= $eventDateText !== '' ? ' · ' . esc($eventDateText) : '' ?></p>
                    <div class="pg-statbar">
                        <span class="pg-pill"><?= count($photos) ?> Photos</span>
                        <?php if ($selectionEnabled): ?>
                            <span class="pg-pill"><?= $icon('printer', 'h-4 w-4') ?><span data-selected-summary><?= count($selectedPhotoIds) ?> / <?= $selectionLimit ?> dipilih</span></span>
                        <?php endif; ?>
                        <?php if ($eventDateText !== ''): ?>
                            <span class="pg-pill pg-date-pill"><?= $icon('calendar', 'h-4 w-4') ?><?= esc($eventDateText) ?></span>
                        <?php endif; ?>
                        <?php if ($downloadEnabled): ?>
                            <span class="pg-pill pg-download-pill"><?= $icon('download', 'h-4 w-4') ?>Download aktif</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($isPrivateGallery): ?>
                    <aside class="pg-privacy-note" aria-label="Status gallery">
                        <strong><?= $icon('lock', 'h-4 w-4') ?>Gallery ini bersifat privat</strong>
                        <p>Bagikan PIN kepada tamu untuk melihat dan mengunduh foto.</p>
                    </aside>
                <?php endif; ?>
            </section>

            <?php if ($photos === []): ?>
                <section class="pg-empty">
                    <div>
                        <?= $icon('image', 'h-10 w-10') ?>
                        <p style="margin:12px 0 0;font-weight:900">Belum ada foto di gallery ini.</p>
                    </div>
                </section>
            <?php else: ?>
                <nav class="pg-tabs" aria-label="Filter gallery">
                    <button class="pg-tab is-active" type="button" data-gallery-tab="all"><?= $icon('grid', 'h-4 w-4') ?>Semua</button>
                    <?php foreach ($albums as $album): ?>
                        <?php $albumId = (int) ($album['id'] ?? 0); ?>
                        <button class="pg-tab" type="button" data-gallery-tab="album:<?= $albumId ?>" data-album-tab-id="<?= $albumId ?>"><?= $icon('image', 'h-4 w-4') ?><?= esc((string) ($album['name'] ?? 'Album')) ?></button>
                    <?php endforeach; ?>
                    <button class="pg-tab" type="button" data-gallery-tab="favorites"><?= $icon('heart', 'h-4 w-4') ?>Favorit</button>
                    <?php if ($selectionEnabled): ?>
                        <button class="pg-tab" type="button" data-gallery-tab="print"><?= $icon('printer', 'h-4 w-4') ?>Untuk Dicetak</button>
                    <?php endif; ?>
                    <?php if ($shareReady): ?>
                        <button class="pg-tab" type="button" data-gallery-tab="share"><?= $icon('share', 'h-4 w-4') ?>Untuk Disebar</button>
                    <?php endif; ?>
                </nav>
                <div class="pg-bulk-bar" data-bulk-bar>
                    <div class="pg-bulk-left">
                        <label class="pg-bulk-check"><input type="checkbox" data-bulk-select-visible> Pilih semua yang tampil</label>
                        <span class="pg-pill"><?= $icon('check', 'h-4 w-4') ?><span data-bulk-summary>0 foto dipilih</span></span>
                    </div>
                    <div class="pg-bulk-actions">
                        <button class="pg-btn pg-btn-muted" type="button" data-bulk-favorite><?= $icon('heart', 'h-4 w-4') ?>Favorit</button>
                        <div class="pg-bulk-menu-wrap">
                            <button class="pg-btn pg-btn-primary" type="button" data-bulk-menu-toggle aria-expanded="false"><?= $icon('more', 'h-4 w-4') ?>Menu Pilihan</button>
                            <div class="pg-bulk-menu" data-bulk-menu>
                                <?php if ($selectionEnabled): ?>
                                    <button type="button" data-bulk-print><?= $icon('printer', 'h-4 w-4') ?>Pilih Untuk Dicetak</button>
                                <?php endif; ?>
                                <button type="button" data-bulk-share <?= $shareReady ? '' : 'disabled' ?>><?= $icon('share', 'h-4 w-4') ?>Pilih Untuk Disebar</button>
                                <button type="button" data-bulk-clear><?= $icon('check', 'h-4 w-4') ?>Bersihkan Pilihan</button>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="pg-grid" data-gallery-grid data-selection-url="<?= esc(site_url('gallery/' . $gallerySlug . '/selection'), 'attr') ?>" data-submit-url="<?= esc(site_url('gallery/' . $gallerySlug . '/selection/submit'), 'attr') ?>" data-share-url="<?= esc(site_url('gallery/' . $gallerySlug . '/share-selection'), 'attr') ?>" data-share-submit-url="<?= esc(site_url('gallery/' . $gallerySlug . '/share-selection/submit'), 'attr') ?>" data-comment-url="<?= esc(site_url('gallery/' . $gallerySlug . '/comments'), 'attr') ?>" data-selection-limit="<?= $selectionLimit ?>" data-gallery-key="<?= esc($gallerySlug, 'attr') ?>" data-share-ready="<?= $shareReady ? '1' : '0' ?>">
                    <?php foreach ($photos as $photo): ?>
                        <?php
                            $photoId = (int) ($photo['id'] ?? 0);
                            $thumb = trim((string) ($photo['thumb_path'] ?? $photo['file_path'] ?? ''));
                            $file = trim((string) ($photo['file_path'] ?? ''));
                            $isSelected = isset($selectedLookup[$photoId]);
                            $isShareSelected = isset($shareLookup[$photoId]);
                            $isPrintSubmitted = isset($submittedPrintLookup[$photoId]);
                            $isShareSubmitted = isset($submittedShareLookup[$photoId]);
                            $albumId = (int) ($photo['album_id'] ?? 0);
                            $albumName = $albumNames[$albumId] ?? '';
                        ?>
                        <article class="pg-photo<?= $albumName !== '' ? ' has-album-badge' : '' ?>" data-photo-id="<?= $photoId ?>" data-album-id="<?= $albumId ?>" data-print-selected="<?= $isSelected ? '1' : '0' ?>" data-share-selected="<?= $isShareSelected ? '1' : '0' ?>" data-print-submitted="<?= $isPrintSubmitted ? '1' : '0' ?>" data-share-submitted="<?= $isShareSubmitted ? '1' : '0' ?>" data-favorite="0">
                            <label class="pg-photo-check" aria-label="Pilih foto">
                                <input type="checkbox" data-bulk-photo-check>
                            </label>
                            <?php if ($albumName !== ''): ?>
                                <span class="pg-photo-badge"><?= $icon('image', 'h-4 w-4') ?><span><?= esc($albumName) ?></span></span>
                            <?php endif; ?>
                            <div class="pg-photo-statuses" data-photo-statuses aria-label="Status foto"></div>
                            <img src="<?= esc(base_url($thumb), 'attr') ?>" alt="<?= esc((string) ($photo['original_name'] ?? 'Foto'), 'attr') ?>" loading="lazy" data-zoom-photo data-full-src="<?= esc(base_url($file !== '' ? $file : $thumb), 'attr') ?>" data-photo-title="<?= esc((string) ($photo['original_name'] ?? 'Foto'), 'attr') ?>">
                            <div class="pg-photo-actions">
                                <button class="pg-btn pg-btn-muted pg-favorite-btn" type="button" data-favorite-photo>
                                    <?= $icon('heart', 'h-4 w-4') ?>
                                    <span>Favorit</span>
                                </button>
                                <div class="pg-photo-menu-wrap">
                                    <button class="pg-btn pg-btn-muted" type="button" data-photo-menu-toggle aria-expanded="false">
                                        <?= $icon('more', 'h-4 w-4') ?>
                                        <span>Menu</span>
                                    </button>
                                    <div class="pg-photo-menu" data-photo-menu>
                                        <button type="button" data-comment-photo><?= $icon('message', 'h-4 w-4') ?>Komentar / Revisi Foto</button>
                                        <?php if ($selectionEnabled): ?>
                                            <button class="<?= $isSelected ? 'is-selected' : '' ?>" type="button" data-select-photo><?= $isSelected ? $icon('check', 'h-4 w-4') : $icon('printer', 'h-4 w-4') ?><span><?= $isSelected ? 'Hapus dari Cetak' : 'Pilih Untuk Dicetak' ?></span></button>
                                        <?php endif; ?>
                                        <button class="<?= $isShareSelected ? 'is-share-selected' : '' ?>" type="button" data-share-photo <?= $shareReady ? '' : 'disabled' ?>><?= $isShareSelected ? $icon('check', 'h-4 w-4') : $icon('share', 'h-4 w-4') ?><span><?= $isShareSelected ? 'Hapus dari Sebar' : 'Pilih Untuk Disebar' ?></span></button>
                                        <?php if ($downloadEnabled && $file !== ''): ?>
                                            <a href="<?= esc(site_url('gallery/' . $gallerySlug . '/photos/' . $photoId . '/download'), 'attr') ?>"><?= $icon('download', 'h-4 w-4') ?><span>Download Foto</span></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
                <div class="pg-pagination" data-client-photo-pagination>
                    <span class="pg-pagination-info" data-client-photo-page-info>Menampilkan foto</span>
                    <div class="pg-pagination-actions">
                        <button class="pg-btn pg-btn-muted" type="button" data-client-photo-page-prev>Sebelumnya</button>
                        <span class="pg-page-number" data-client-photo-page-number>1 / 1</span>
                        <button class="pg-btn pg-btn-muted" type="button" data-client-photo-page-next>Berikutnya</button>
                    </div>
                </div>
                <?php if ($selectionEnabled): ?>
                    <div class="pg-submit-bar <?= count($selectedPhotoIds) > 0 ? 'is-visible' : '' ?>" data-submit-bar>
                        <span class="pg-pill"><?= $icon('check', 'h-4 w-4') ?><span data-print-summary><?= count($selectedPhotoIds) ?> foto siap dikirim untuk dicetak</span></span>
                        <button class="pg-btn pg-btn-primary" type="button" data-open-submit-selection><?= $icon('send', 'h-4 w-4') ?><span data-print-submit-label>Kirim Pilihan Cetak</span></button>
                    </div>
                <?php endif; ?>
                <?php if ($shareReady): ?>
                    <div class="pg-submit-bar pg-share-bar <?= count($sharePhotoIds) > 0 ? 'is-visible' : '' ?>" data-share-bar>
                        <span class="pg-pill"><?= $icon('share', 'h-4 w-4') ?><span data-share-summary><?= count($sharePhotoIds) ?> foto dipilih untuk disebar</span></span>
                        <div class="pg-submit-actions">
                            <button class="pg-btn pg-btn-primary" type="button" data-open-share-family><?= $icon('share', 'h-4 w-4') ?><span data-share-submit-label>Sebar ke Keluarga</span></button>
                            <button class="pg-btn pg-btn-muted" type="button" data-open-family-page data-family-url="<?= esc($familyShareUrl, 'attr') ?>"><?= $icon('image', 'h-4 w-4') ?>Halaman Keluarga</button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<?php if ($hasAccess && $selectionEnabled): ?>
<div class="pg-modal-backdrop" data-submit-modal aria-hidden="true">
    <div class="pg-modal" role="dialog" aria-modal="true" aria-labelledby="pg-submit-title">
        <h2 id="pg-submit-title">Kirim pilihan foto untuk dicetak?</h2>
        <p><span data-submit-modal-count><?= count($selectedPhotoIds) ?></span> foto akan dikirim ke fotografer. Kamu masih bisa mengubah pilihan setelah ini sebelum fotografer memprosesnya.</p>
        <div class="pg-modal-actions">
            <button class="pg-btn pg-btn-muted" type="button" data-close-submit-modal>Batal</button>
            <button class="pg-btn pg-btn-primary" type="button" data-confirm-submit-selection><?= $icon('send', 'h-4 w-4') ?>Kirim</button>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if ($hasAccess): ?>
<div class="pg-lightbox-backdrop" data-photo-lightbox aria-hidden="true">
    <div class="pg-lightbox" role="dialog" aria-modal="true" aria-label="Preview foto">
        <button class="pg-lightbox-close" type="button" data-close-lightbox aria-label="Tutup preview">⛌</button>
        <img src="" alt="Preview foto" data-lightbox-image>
    </div>
</div>
<div class="pg-modal-backdrop" data-comment-modal aria-hidden="true">
    <div class="pg-modal" role="dialog" aria-modal="true" aria-labelledby="pg-comment-title">
        <h2 id="pg-comment-title">Komentar / revisi foto</h2>
        <p>Tulis catatan untuk fotografer pada foto yang dipilih.</p>
        <textarea data-comment-text maxlength="1000" placeholder="Contoh: tolong edit warna kulit lebih natural, crop sedikit bagian kanan."></textarea>
        <div class="pg-modal-actions">
            <button class="pg-btn pg-btn-muted" type="button" data-close-comment-modal>Batal</button>
            <button class="pg-btn pg-btn-primary" type="button" data-confirm-comment><?= $icon('send', 'h-4 w-4') ?>Kirim</button>
        </div>
    </div>
</div>
<?php if ($shareReady): ?>
<div class="pg-modal-backdrop" data-share-modal aria-hidden="true">
    <div class="pg-modal" role="dialog" aria-modal="true" aria-labelledby="pg-share-title">
        <h2 id="pg-share-title">Sebar ke keluarga</h2>
        <p><span data-share-modal-count><?= count($sharePhotoIds) ?></span> foto akan disiapkan untuk halaman keluarga.</p>
        <div class="pg-choice-grid">
            <label class="pg-choice"><input type="radio" name="share_mode" value="public" checked data-share-mode> Tanpa PIN</label>
            <label class="pg-choice"><input type="radio" name="share_mode" value="pin" data-share-mode> Dengan PIN</label>
        </div>
        <div class="pg-share-pin-fields" data-share-pin-fields aria-label="PIN sebar">
            <input class="pg-share-pin-box" type="text" inputmode="numeric" maxlength="1" aria-label="Digit PIN sebar 1" data-share-pin-box>
            <input class="pg-share-pin-box" type="text" inputmode="numeric" maxlength="1" aria-label="Digit PIN sebar 2" data-share-pin-box>
            <input class="pg-share-pin-box" type="text" inputmode="numeric" maxlength="1" aria-label="Digit PIN sebar 3" data-share-pin-box>
            <input class="pg-share-pin-box" type="text" inputmode="numeric" maxlength="1" aria-label="Digit PIN sebar 4" data-share-pin-box>
        </div>
        <p class="pg-share-pin-note" data-share-pin-note>* PIN aktif: isi 4 digit.</p>
        <div class="pg-modal-actions">
            <button class="pg-btn pg-btn-muted" type="button" data-close-share-modal>Batal</button>
            <button class="pg-btn pg-btn-primary pg-confirm-share-btn" type="button" data-confirm-share><?= $icon('share', 'h-4 w-4') ?>Sebar ke Keluarga</button>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php if (! $hasAccess): ?>
<script>
(function () {
    const form = document.querySelector('[data-pin-form]');
    const hidden = document.querySelector('[data-pin-hidden]');
    const boxes = Array.from(document.querySelectorAll('[data-pin-box]'));
    if (!form || !hidden || boxes.length === 0) return;

    const syncPin = () => {
        hidden.value = boxes.map((box) => box.value.trim()).join('');
    };

    boxes.forEach((box, index) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncPin();
            if (box.value && boxes[index + 1]) {
                boxes[index + 1].focus();
            }
        });
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !box.value && boxes[index - 1]) {
                boxes[index - 1].focus();
            }
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
})();
</script>
<?php endif; ?>
<?php if ($hasAccess): ?>
<script>
(function () {
    const grid = document.querySelector('[data-gallery-grid]');
    const summary = document.querySelector('[data-selected-summary]');
    const printSummary = document.querySelector('[data-print-summary]');
    const submitBar = document.querySelector('[data-submit-bar]');
    const printSubmitLabel = document.querySelector('[data-print-submit-label]');
    const submitModal = document.querySelector('[data-submit-modal]');
    const submitModalCount = document.querySelector('[data-submit-modal-count]');
    const lightbox = document.querySelector('[data-photo-lightbox]');
    const lightboxImage = document.querySelector('[data-lightbox-image]');
    const commentModal = document.querySelector('[data-comment-modal]');
    const commentText = document.querySelector('[data-comment-text]');
    const shareBar = document.querySelector('[data-share-bar]');
    const shareSummary = document.querySelector('[data-share-summary]');
    const shareSubmitLabel = document.querySelector('[data-share-submit-label]');
    const familyPageButton = document.querySelector('[data-open-family-page]');
    const shareModal = document.querySelector('[data-share-modal]');
    const shareModalCount = document.querySelector('[data-share-modal-count]');
    const sharePinFields = document.querySelector('[data-share-pin-fields]');
    const sharePinNote = document.querySelector('[data-share-pin-note]');
    const sharePinBoxes = Array.from(document.querySelectorAll('[data-share-pin-box]'));
    const bulkBar = document.querySelector('[data-bulk-bar]');
    const bulkSummary = document.querySelector('[data-bulk-summary]');
    const bulkSelectVisible = document.querySelector('[data-bulk-select-visible]');
    const bulkMenu = document.querySelector('[data-bulk-menu]');
    const bulkMenuToggle = document.querySelector('[data-bulk-menu-toggle]');
    const clientPagination = document.querySelector('[data-client-photo-pagination]');
    const clientPageInfo = document.querySelector('[data-client-photo-page-info]');
    const clientPageNumber = document.querySelector('[data-client-photo-page-number]');
    const clientPagePrev = document.querySelector('[data-client-photo-page-prev]');
    const clientPageNext = document.querySelector('[data-client-photo-page-next]');
    const favoriteKey = `aa_pg_favorites_${grid?.dataset.galleryKey || 'gallery'}`;
    let activeTab = 'all';
    let clientPage = 1;
    const clientPageSize = 20;
    let activeCommentPhotoId = '';
    let csrfName = <?= json_encode(csrf_token()) ?>;
    let csrfHash = <?= json_encode(csrf_hash()) ?>;
    let submittedPrintIds = new Set(<?= json_encode(array_map('strval', $submittedPrintPhotoIds)) ?>);
    let submittedShareIds = new Set(<?= json_encode(array_map('strval', $submittedSharePhotoIds)) ?>);

    if (!grid) return;

    const toast = (message, tone = 'success', title = '') => {
        if (typeof window.aaToast === 'function') {
            window.aaToast(message, tone, title || undefined);
        }
    };

    const cards = () => Array.from(grid.querySelectorAll('[data-photo-id]'));
    const visibleCards = () => cards().filter((card) => !card.classList.contains('is-hidden-by-tab') && !card.classList.contains('is-paginated-hidden'));
    const checkedCards = () => cards().filter((card) => card.querySelector('[data-bulk-photo-check]')?.checked);
    const selectedCards = () => cards().filter((card) => card.dataset.printSelected === '1');
    const shareCards = () => cards().filter((card) => card.dataset.shareSelected === '1');
    const favoriteCards = () => cards().filter((card) => card.dataset.favorite === '1');
    const countNewSinceSubmit = (items, submittedIds) => items.filter((card) => !submittedIds.has(String(card.dataset.photoId || ''))).length;
    const setLoading = (button, loading, label = 'Mengirim...') => {
        if (!button) return;
        if (loading) {
            button.dataset.idleHtml = button.innerHTML;
            button.classList.add('is-loading');
            button.disabled = true;
            button.innerHTML = <?= json_encode($icon('send', 'h-4 w-4')) ?> + `<span>${label}</span>`;
            return;
        }
        button.classList.remove('is-loading');
        button.disabled = false;
        if (button.dataset.idleHtml) {
            button.innerHTML = button.dataset.idleHtml;
            delete button.dataset.idleHtml;
        }
    };
    const readFavorites = () => {
        try {
            return new Set(JSON.parse(localStorage.getItem(favoriteKey) || '[]').map(String));
        } catch (error) {
            return new Set();
        }
    };
    const writeFavorites = (favorites) => {
        try {
            localStorage.setItem(favoriteKey, JSON.stringify(Array.from(favorites)));
        } catch (error) {}
    };

    const resetPhotoMenuPosition = (menu) => {
        menu.classList.remove('is-mobile-fixed');
        menu.style.left = '';
        menu.style.top = '';
        menu.style.right = '';
        menu.style.bottom = '';
        menu.style.width = '';
    };

    const positionPhotoMenu = (menu, toggle) => {
        if (!menu || !toggle || !window.matchMedia('(max-width: 680px)').matches) {
            if (menu) resetPhotoMenuPosition(menu);
            return;
        }

        const viewportPadding = 12;
        const toggleRect = toggle.getBoundingClientRect();
        const menuWidth = Math.min(230, window.innerWidth - (viewportPadding * 2));
        menu.classList.add('is-mobile-fixed');
        menu.style.width = `${menuWidth}px`;
        const menuRect = menu.getBoundingClientRect();
        const preferredLeft = toggleRect.right - menuWidth;
        const left = Math.min(Math.max(viewportPadding, preferredLeft), window.innerWidth - menuWidth - viewportPadding);
        let top = toggleRect.top - menuRect.height - 8;
        if (top < viewportPadding) {
            top = toggleRect.bottom + 8;
        }

        menu.style.left = `${left}px`;
        menu.style.top = `${Math.max(viewportPadding, top)}px`;
        menu.style.right = 'auto';
        menu.style.bottom = 'auto';
    };

    const closePhotoMenus = (except = null) => {
        document.querySelectorAll('[data-photo-menu]').forEach((menu) => {
            if (menu !== except) {
                menu.classList.remove('is-open');
                resetPhotoMenuPosition(menu);
            }
        });
        document.querySelectorAll('[data-photo-menu-toggle]').forEach((button) => {
            const menu = button.closest('.pg-photo-menu-wrap')?.querySelector('[data-photo-menu]');
            button.setAttribute('aria-expanded', menu?.classList.contains('is-open') ? 'true' : 'false');
        });
    };

    const closeBulkMenu = () => {
        bulkMenu?.classList.remove('is-open');
        bulkMenuToggle?.setAttribute('aria-expanded', 'false');
    };

    const openLightbox = (image) => {
        const src = image?.dataset.fullSrc || image?.currentSrc || image?.src || '';
        if (!src || !lightboxImage) return;
        lightboxImage.src = src;
        lightboxImage.alt = image?.dataset.photoTitle || image?.alt || 'Preview foto';
        lightbox?.classList.add('is-open');
        lightbox?.setAttribute('aria-hidden', 'false');
    };

    const closeLightbox = () => {
        lightbox?.classList.remove('is-open');
        lightbox?.setAttribute('aria-hidden', 'true');
        if (lightboxImage) {
            lightboxImage.src = '';
        }
    };

    const setButtonState = (button, selected) => {
        button.classList.toggle('is-selected', selected);
        button.innerHTML = selected
            ? <?= json_encode($icon('check', 'h-4 w-4') . '<span>Hapus dari Cetak</span>') ?>
            : <?= json_encode($icon('printer', 'h-4 w-4') . '<span>Pilih Untuk Dicetak</span>') ?>;
    };

    const setShareButtonState = (button, selected) => {
        button.classList.toggle('is-share-selected', selected);
        button.innerHTML = selected
            ? <?= json_encode($icon('check', 'h-4 w-4') . '<span>Hapus dari Sebar</span>') ?>
            : <?= json_encode($icon('share', 'h-4 w-4') . '<span>Pilih Untuk Disebar</span>') ?>;
    };

    const setFavoriteButtonState = (button, favorite) => {
        button.classList.toggle('is-favorite', favorite);
        button.innerHTML = favorite
            ? <?= json_encode($icon('heart', 'h-4 w-4') . '<span>Favorit</span>') ?>
            : <?= json_encode($icon('heart', 'h-4 w-4') . '<span>Favorit</span>') ?>;
    };

    const renderPhotoStatuses = (card) => {
        const holder = card?.querySelector('[data-photo-statuses]');
        if (!holder) return;
        const photoId = String(card.dataset.photoId || '');
        const statuses = [];
        if (card.dataset.favorite === '1') {
            statuses.push(['is-favorite', <?= json_encode($icon('heart', 'h-4 w-4')) ?>, 'Favorit']);
        }
        if (card.dataset.printSelected === '1') {
            statuses.push([
                submittedPrintIds.has(photoId) ? 'is-sent' : 'is-print',
                <?= json_encode($icon('printer', 'h-4 w-4')) ?>,
                submittedPrintIds.has(photoId) ? 'Cetak terkirim' : 'Cetak'
            ]);
        }
        if (card.dataset.shareSelected === '1') {
            statuses.push([
                submittedShareIds.has(photoId) ? 'is-sent' : 'is-share',
                <?= json_encode($icon('share', 'h-4 w-4')) ?>,
                submittedShareIds.has(photoId) ? 'Disebar' : 'Sebar'
            ]);
        }
        holder.innerHTML = statuses.map(([className, iconHtml, label]) => `<span class="pg-photo-status-pill ${className}">${iconHtml}<span>${label}</span></span>`).join('');
    };

    const renderAllPhotoStatuses = () => {
        cards().forEach(renderPhotoStatuses);
    };

    const updateBulkSummary = () => {
        const checked = checkedCards();
        if (bulkSummary) {
            bulkSummary.textContent = `${checked.length} foto dipilih`;
        }
        bulkBar?.classList.toggle('is-visible', checked.length > 0);
        if (bulkSelectVisible) {
            const visible = visibleCards();
            const visibleChecked = visible.filter((card) => card.querySelector('[data-bulk-photo-check]')?.checked);
            bulkSelectVisible.checked = visible.length > 0 && visibleChecked.length === visible.length;
            bulkSelectVisible.indeterminate = visibleChecked.length > 0 && visibleChecked.length < visible.length;
        }
    };

    const clearBulkChecks = () => {
        cards().forEach((card) => {
            const checkbox = card.querySelector('[data-bulk-photo-check]');
            if (checkbox) checkbox.checked = false;
        });
        closeBulkMenu();
        updateBulkSummary();
    };

    const updatePrintSummary = (count, limit) => {
        const selectionLimit = limit || grid.dataset.selectionLimit || <?= (int) $selectionLimit ?>;
        const selected = selectedCards();
        const submittedCount = selected.filter((card) => submittedPrintIds.has(String(card.dataset.photoId || ''))).length;
        const newCount = countNewSinceSubmit(selected, submittedPrintIds);
        if (summary) {
            summary.textContent = `${count} / ${selectionLimit} dipilih`;
        }
        if (printSummary) {
            if (submittedCount > 0 && newCount <= 0) {
                printSummary.textContent = `${submittedCount} foto dikirim pilihan cetak`;
            } else if (submittedCount > 0 && newCount > 0) {
                printSummary.textContent = `Tambah ${newCount} foto dicetak`;
            } else {
                printSummary.textContent = `${count} foto siap dikirim untuk dicetak`;
            }
        }
        if (printSubmitLabel) {
            printSubmitLabel.textContent = submittedCount > 0 && newCount > 0 ? `Kirim Tambahan Cetak` : 'Kirim Pilihan Cetak';
        }
        if (submitModalCount) {
            submitModalCount.textContent = String(submittedCount > 0 && newCount > 0 ? newCount : count);
        }
        submitBar?.classList.toggle('is-visible', count > 0);
        renderAllPhotoStatuses();
    };

    const updateShareSummary = (count) => {
        const selected = shareCards();
        const submittedCount = selected.filter((card) => submittedShareIds.has(String(card.dataset.photoId || ''))).length;
        const newCount = countNewSinceSubmit(selected, submittedShareIds);
        if (shareSummary) {
            if (submittedCount > 0 && newCount <= 0) {
                shareSummary.textContent = `${submittedCount} foto sudah disebar`;
            } else if (submittedCount > 0 && newCount > 0) {
                shareSummary.textContent = `Tambah ${newCount} foto disebar`;
            } else {
                shareSummary.textContent = `${count} foto dipilih untuk disebar`;
            }
        }
        if (shareSubmitLabel) {
            shareSubmitLabel.textContent = submittedCount > 0 && newCount > 0 ? 'Sebar Tambahan' : 'Sebar ke Keluarga';
        }
        if (shareModalCount) {
            shareModalCount.textContent = String(submittedCount > 0 && newCount > 0 ? newCount : count);
        }
        shareBar?.classList.toggle('is-visible', count > 0);
        renderAllPhotoStatuses();
    };

    const cardMatchesActiveTab = (card) => {
        if (activeTab.startsWith('album:')) {
            return card.dataset.albumId === activeTab.split(':')[1];
        }
        if (activeTab === 'favorites') {
            return card.dataset.favorite === '1';
        }
        if (activeTab === 'print') {
            return card.dataset.printSelected === '1';
        }
        if (activeTab === 'share') {
            return card.dataset.shareSelected === '1';
        }

        return true;
    };

    const syncAlbumTabs = () => {
        const usedAlbums = new Set(cards().map((card) => String(card.dataset.albumId || '')).filter(Boolean));
        document.querySelectorAll('[data-album-tab-id]').forEach((tab) => {
            const albumId = String(tab.dataset.albumTabId || '');
            const hasPhotos = usedAlbums.has(albumId);
            tab.hidden = !hasPhotos;
            if (!hasPhotos && activeTab === `album:${albumId}`) {
                activeTab = 'all';
                document.querySelectorAll('[data-gallery-tab]').forEach((item) => {
                    item.classList.toggle('is-active', item.dataset.galleryTab === 'all');
                });
            }
        });
    };

    const renderClientPagination = () => {
        syncAlbumTabs();
        const visibleCards = cards().filter(cardMatchesActiveTab);
        const total = visibleCards.length;
        const totalPages = Math.max(1, Math.ceil(total / clientPageSize));
        clientPage = Math.min(Math.max(1, clientPage), totalPages);
        cards().forEach((card) => {
            const visibleByTab = cardMatchesActiveTab(card);
            const index = visibleCards.indexOf(card);
            const page = index >= 0 ? Math.floor(index / clientPageSize) + 1 : 0;
            card.classList.toggle('is-hidden-by-tab', !visibleByTab);
            card.classList.toggle('is-paginated-hidden', visibleByTab && page !== clientPage);
        });
        if (clientPagination) clientPagination.style.display = 'flex';
        if (clientPageInfo) {
            const start = total === 0 ? 0 : ((clientPage - 1) * clientPageSize) + 1;
            const end = Math.min(total, clientPage * clientPageSize);
            clientPageInfo.textContent = total === 0 ? 'Tidak ada foto di tab ini' : `Menampilkan ${start}-${end} dari ${total} foto`;
        }
        if (clientPageNumber) clientPageNumber.textContent = `${clientPage} / ${totalPages}`;
        if (clientPagePrev) clientPagePrev.disabled = clientPage <= 1;
        if (clientPageNext) clientPageNext.disabled = clientPage >= totalPages;
        updateBulkSummary();
    };

    let gridSwitchTimer = null;
    const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const setGridSwitching = (switching) => {
        grid?.classList.toggle('is-switching', switching);
        clientPagination?.classList.toggle('is-switching', switching);
    };

    const applyFilter = (resetPage = false, animate = false) => {
        if (resetPage) clientPage = 1;
        const render = () => {
            cards().forEach((card) => {
                let visible = true;
                if (activeTab.startsWith('album:')) {
                    visible = card.dataset.albumId === activeTab.split(':')[1];
                } else if (activeTab === 'favorites') {
                    visible = card.dataset.favorite === '1';
                } else if (activeTab === 'print') {
                    visible = card.dataset.printSelected === '1';
                } else if (activeTab === 'share') {
                    visible = card.dataset.shareSelected === '1';
                }
                card.classList.toggle('is-hidden-by-tab', !visible);
            });
            renderClientPagination();
        };

        if (!animate || prefersReducedMotion()) {
            window.clearTimeout(gridSwitchTimer);
            setGridSwitching(false);
            render();
            return;
        }

        window.clearTimeout(gridSwitchTimer);
        setGridSwitching(true);
        gridSwitchTimer = window.setTimeout(() => {
            render();
            window.requestAnimationFrame(() => setGridSwitching(false));
        }, 110);
    };

    const hydrateFavorites = () => {
        const favorites = readFavorites();
        cards().forEach((card) => {
            const favorite = favorites.has(String(card.dataset.photoId || ''));
            card.dataset.favorite = favorite ? '1' : '0';
            const button = card.querySelector('[data-favorite-photo]');
            if (button) setFavoriteButtonState(button, favorite);
        });
        applyFilter();
    };

    document.querySelectorAll('[data-gallery-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            activeTab = tab.dataset.galleryTab || 'all';
            document.querySelectorAll('[data-gallery-tab]').forEach((item) => item.classList.toggle('is-active', item === tab));
            clearBulkChecks();
            applyFilter(true, true);
        });
    });
    clientPagePrev?.addEventListener('click', () => {
        clearBulkChecks();
        clientPage -= 1;
        renderClientPagination();
    });
    clientPageNext?.addEventListener('click', () => {
        clearBulkChecks();
        clientPage += 1;
        renderClientPagination();
    });

    const postTypedSelection = async (card, type) => {
        const photoId = card?.dataset.photoId || '';
        if (!photoId) return null;
        const url = type === 'share' ? grid.dataset.shareUrl : grid.dataset.selectionUrl;
        const data = new FormData();
        data.append(csrfName, csrfHash);
        data.append('photo_id', photoId);
        const response = await fetch(url, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: data,
        });
        const json = await response.json().catch(() => ({}));
        if (json.csrf_hash) csrfHash = json.csrf_hash;
        if (!response.ok || !json.ok) {
            throw new Error(json.message || 'Pilihan foto belum bisa disimpan.');
        }

        if (type === 'share') {
            card.dataset.shareSelected = json.selected ? '1' : '0';
            if (!json.selected) submittedShareIds.delete(String(card.dataset.photoId || ''));
            const button = card.querySelector('[data-share-photo]');
            if (button) setShareButtonState(button, !!json.selected);
            updateShareSummary(json.selected_count ?? shareCards().length);
        } else {
            card.dataset.printSelected = json.selected ? '1' : '0';
            if (!json.selected) submittedPrintIds.delete(String(card.dataset.photoId || ''));
            const button = card.querySelector('[data-select-photo]');
            if (button) setButtonState(button, !!json.selected);
            updatePrintSummary(json.selected_count ?? selectedCards().length, json.selection_limit);
        }

        return json;
    };

    const bulkApplySelection = async (type) => {
        const targets = checkedCards().filter((card) => type === 'share' ? card.dataset.shareSelected !== '1' : card.dataset.printSelected !== '1');
        if (targets.length === 0) {
            toast(type === 'share' ? 'Semua foto terpilih sudah masuk Untuk Disebar.' : 'Semua foto terpilih sudah masuk Untuk Dicetak.', 'info');
            return;
        }
        closeBulkMenu();
        for (const card of targets) {
            await postTypedSelection(card, type);
        }
        applyFilter();
        clearBulkChecks();
        toast(type === 'share' ? 'Foto terpilih masuk Untuk Disebar.' : 'Foto terpilih masuk Untuk Dicetak.', 'success');
    };

    bulkSelectVisible?.addEventListener('change', () => {
        visibleCards().forEach((card) => {
            const checkbox = card.querySelector('[data-bulk-photo-check]');
            if (checkbox) checkbox.checked = bulkSelectVisible.checked;
        });
        updateBulkSummary();
    });

    bulkMenuToggle?.addEventListener('click', () => {
        const willOpen = !bulkMenu?.classList.contains('is-open');
        closePhotoMenus();
        bulkMenu?.classList.toggle('is-open', willOpen);
        bulkMenuToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    document.querySelector('[data-bulk-favorite]')?.addEventListener('click', () => {
        const checked = checkedCards();
        if (checked.length === 0) {
            toast('Pilih minimal satu foto dahulu.', 'warning', 'Belum ada pilihan');
            return;
        }
        const favorites = readFavorites();
        checked.forEach((card) => {
            const photoId = String(card.dataset.photoId || '');
            if (!photoId) return;
            favorites.add(photoId);
            card.dataset.favorite = '1';
            const button = card.querySelector('[data-favorite-photo]');
            if (button) setFavoriteButtonState(button, true);
            renderPhotoStatuses(card);
        });
        writeFavorites(favorites);
        applyFilter();
        clearBulkChecks();
        toast(`${checked.length} foto masuk Favorit.`, 'success');
    });

    document.querySelector('[data-bulk-print]')?.addEventListener('click', async () => {
        if (checkedCards().length === 0) {
            toast('Pilih minimal satu foto dahulu.', 'warning', 'Belum ada pilihan');
            return;
        }
        try {
            await bulkApplySelection('print');
        } catch (error) {
            toast(error.message, 'error', 'Gagal menyimpan');
        }
    });

    document.querySelector('[data-bulk-share]')?.addEventListener('click', async (event) => {
        if (event.currentTarget.disabled) {
            toast('Pilihan untuk disebar belum siap. Jalankan update SQL Photographer Gallery dahulu.', 'warning', 'Belum siap');
            return;
        }
        if (checkedCards().length === 0) {
            toast('Pilih minimal satu foto dahulu.', 'warning', 'Belum ada pilihan');
            return;
        }
        try {
            await bulkApplySelection('share');
        } catch (error) {
            toast(error.message, 'error', 'Gagal menyimpan');
        }
    });

    document.querySelector('[data-bulk-clear]')?.addEventListener('click', () => {
        clearBulkChecks();
    });

    grid?.addEventListener('click', async (event) => {
        const zoomImage = event.target.closest('[data-zoom-photo]');
        if (zoomImage) {
            closePhotoMenus();
            closeBulkMenu();
            openLightbox(zoomImage);
            return;
        }

        const menuToggle = event.target.closest('[data-photo-menu-toggle]');
        if (menuToggle) {
            event.stopPropagation();
            const menu = menuToggle.closest('.pg-photo-menu-wrap')?.querySelector('[data-photo-menu]');
            if (!menu) return;
            const willOpen = !menu.classList.contains('is-open');
            closePhotoMenus(menu);
            menu.classList.toggle('is-open', willOpen);
            menuToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (willOpen) {
                positionPhotoMenu(menu, menuToggle);
            } else {
                resetPhotoMenuPosition(menu);
            }
            return;
        }

        const favoriteButton = event.target.closest('[data-favorite-photo]');
        if (favoriteButton) {
            closePhotoMenus();
            const card = favoriteButton.closest('[data-photo-id]');
            const photoId = String(card?.dataset.photoId || '');
            if (!card || !photoId) return;
            const favorites = readFavorites();
            const willFavorite = !favorites.has(photoId);
            if (willFavorite) {
                favorites.add(photoId);
            } else {
                favorites.delete(photoId);
            }
            writeFavorites(favorites);
            card.dataset.favorite = willFavorite ? '1' : '0';
            setFavoriteButtonState(favoriteButton, willFavorite);
            renderPhotoStatuses(card);
            applyFilter();
            toast(willFavorite ? 'Foto masuk Favorit.' : 'Foto dihapus dari Favorit.', 'success');
            return;
        }

        const commentButton = event.target.closest('[data-comment-photo]');
        if (commentButton) {
            event.stopPropagation();
            const card = commentButton.closest('[data-photo-id]');
            activeCommentPhotoId = String(card?.dataset.photoId || '');
            closePhotoMenus();
            if (!activeCommentPhotoId) return;
            if (commentText) commentText.value = '';
            commentModal?.classList.add('is-open');
            commentModal?.setAttribute('aria-hidden', 'false');
            setTimeout(() => commentText?.focus(), 40);
            return;
        }

        const shareButton = event.target.closest('[data-share-photo]');
        if (shareButton) {
            event.stopPropagation();
            closePhotoMenus();
            if (shareButton.disabled) {
                toast('Pilihan untuk disebar belum siap. Jalankan update SQL Photographer Gallery dahulu.', 'warning', 'Belum siap');
                return;
            }
            const card = shareButton.closest('[data-photo-id]');
            const photoId = card?.dataset.photoId || '';
            if (!photoId) return;
            const data = new FormData();
            data.append(csrfName, csrfHash);
            data.append('photo_id', photoId);
            shareButton.disabled = true;
            try {
                const response = await fetch(grid.dataset.shareUrl, {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    body: data,
                });
                const json = await response.json().catch(() => ({}));
                if (json.csrf_hash) csrfHash = json.csrf_hash;
                if (!response.ok || !json.ok) {
                    throw new Error(json.message || 'Pilihan sebar belum bisa disimpan.');
                }
                setShareButtonState(shareButton, !!json.selected);
                card.dataset.shareSelected = json.selected ? '1' : '0';
                if (!json.selected) submittedShareIds.delete(String(card.dataset.photoId || ''));
                updateShareSummary(json.selected_count ?? shareCards().length);
                applyFilter();
                toast(json.selected ? 'Foto ditambahkan untuk disebar.' : 'Foto dihapus dari pilihan sebar.', 'success');
            } catch (error) {
                toast(error.message, 'error', 'Gagal menyimpan');
            } finally {
                shareButton.disabled = false;
            }
            return;
        }

        const button = event.target.closest('[data-select-photo]');
        if (!button || button.disabled) return;
        event.stopPropagation();
        closePhotoMenus();
        const card = button.closest('[data-photo-id]');
        const photoId = card?.dataset.photoId || '';
        if (!photoId) return;

        const data = new FormData();
        data.append(csrfName, csrfHash);
        data.append('photo_id', photoId);
        button.disabled = true;

        try {
            const response = await fetch(grid.dataset.selectionUrl, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: data,
            });
            const json = await response.json().catch(() => ({}));
            if (json.csrf_hash) csrfHash = json.csrf_hash;
            if (!response.ok || !json.ok) {
                throw new Error(json.message || 'Pilihan foto belum bisa disimpan.');
            }
            setButtonState(button, !!json.selected);
            card.dataset.printSelected = json.selected ? '1' : '0';
            if (!json.selected) submittedPrintIds.delete(String(card.dataset.photoId || ''));
            updatePrintSummary(json.selected_count ?? selectedCards().length, json.selection_limit);
            applyFilter();
            toast(json.selected ? 'Foto ditambahkan untuk dicetak.' : 'Foto dihapus dari pilihan cetak.', 'success');
        } catch (error) {
            toast(error.message, 'error', 'Gagal menyimpan');
        } finally {
            button.disabled = false;
        }
    });

    grid?.addEventListener('change', (event) => {
        if (event.target.matches('[data-bulk-photo-check]')) {
            updateBulkSummary();
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.pg-photo-menu-wrap') && !event.target.closest('[data-photo-menu]')) {
            closePhotoMenus();
        }
        if (!event.target.closest('.pg-bulk-menu-wrap')) {
            closeBulkMenu();
        }
    });

    document.querySelector('[data-close-lightbox]')?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });

    document.querySelector('[data-close-comment-modal]')?.addEventListener('click', () => {
        commentModal?.classList.remove('is-open');
        commentModal?.setAttribute('aria-hidden', 'true');
    });
    commentModal?.addEventListener('click', (event) => {
        if (event.target === commentModal) {
            commentModal.classList.remove('is-open');
            commentModal.setAttribute('aria-hidden', 'true');
        }
    });
    document.querySelector('[data-confirm-comment]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const comment = (commentText?.value || '').trim();
        if (!activeCommentPhotoId) return;
        if (!comment) {
            toast('Tulis komentar/revisi terlebih dahulu.', 'warning', 'Komentar kosong');
            return;
        }
        const data = new FormData();
        data.append(csrfName, csrfHash);
        data.append('photo_id', activeCommentPhotoId);
        data.append('comment', comment);
        button.disabled = true;
        try {
            const response = await fetch(grid.dataset.commentUrl, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: data,
            });
            const json = await response.json().catch(() => ({}));
            if (json.csrf_hash) csrfHash = json.csrf_hash;
            if (!response.ok || !json.ok) {
                throw new Error(json.message || 'Komentar belum bisa dikirim.');
            }
            commentModal?.classList.remove('is-open');
            commentModal?.setAttribute('aria-hidden', 'true');
            toast(json.message || 'Komentar/revisi sudah dikirim.', 'success', 'Terkirim');
        } catch (error) {
            toast(error.message, 'error', 'Gagal mengirim');
        } finally {
            button.disabled = false;
        }
    });

    const selectedShareMode = () => document.querySelector('[data-share-mode]:checked')?.value || 'public';
    const sharePinValue = () => sharePinBoxes.map((box) => box.value.trim()).join('');
    const syncSharePinVisibility = () => {
        const usePin = selectedShareMode() === 'pin';
        sharePinFields?.classList.toggle('is-visible', usePin);
        sharePinNote?.classList.toggle('is-visible', usePin);
        if (sharePinNote && usePin) {
            const pin = sharePinValue();
            sharePinNote.textContent = pin.length > 0 ? `* PIN aktif: ${pin.padEnd(4, '•')}` : '* PIN aktif: isi 4 digit.';
        }
    };
    document.querySelectorAll('[data-share-mode]').forEach((input) => {
        input.addEventListener('change', syncSharePinVisibility);
    });
    sharePinBoxes.forEach((box, index) => {
        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncSharePinVisibility();
            if (box.value && sharePinBoxes[index + 1]) {
                sharePinBoxes[index + 1].focus();
            }
        });
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !box.value && sharePinBoxes[index - 1]) {
                sharePinBoxes[index - 1].focus();
            }
        });
    });
    document.querySelector('[data-close-share-modal]')?.addEventListener('click', () => {
        shareModal?.classList.remove('is-open');
        shareModal?.setAttribute('aria-hidden', 'true');
    });
    document.querySelector('[data-open-share-family]')?.addEventListener('click', () => {
        const total = shareCards().length;
        if (total <= 0) {
            toast('Pilih minimal satu foto untuk disebar dahulu.', 'warning', 'Belum ada pilihan');
            return;
        }
        updateShareSummary(total);
        shareModal?.classList.add('is-open');
        shareModal?.setAttribute('aria-hidden', 'false');
    });
    familyPageButton?.addEventListener('click', () => {
        const familyUrl = familyPageButton.dataset.familyUrl || '';
        if (!familyUrl) {
            toast('Klik Sebar ke Keluarga dahulu untuk membuat link halaman keluarga.', 'info', 'Belum ada link');
            return;
        }
        window.open(familyUrl, '_blank', 'noopener');
    });
    shareModal?.addEventListener('click', (event) => {
        if (event.target === shareModal) {
            shareModal.classList.remove('is-open');
            shareModal.setAttribute('aria-hidden', 'true');
        }
    });
    document.querySelector('[data-confirm-share]')?.addEventListener('click', async (event) => {
        const total = shareCards().length;
        if (total <= 0) {
            toast('Pilih minimal satu foto untuk disebar dahulu.', 'warning', 'Belum ada pilihan');
            return;
        }
        const mode = selectedShareMode();
        const pin = sharePinValue();
        if (mode === 'pin' && pin.length !== 4) {
            toast('PIN sebar harus tepat 4 digit.', 'warning', 'PIN belum lengkap');
            sharePinBoxes[0]?.focus();
            return;
        }
        const button = event.currentTarget;
        const data = new FormData();
        data.append(csrfName, csrfHash);
        data.append('share_mode', mode);
        data.append('share_pin', pin);
        setLoading(button, true, 'Menyiapkan...');
        try {
            const response = await fetch(grid.dataset.shareSubmitUrl, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: data,
            });
            const json = await response.json().catch(() => ({}));
            if (json.csrf_hash) csrfHash = json.csrf_hash;
            if (!response.ok || !json.ok) {
                throw new Error(json.message || 'Pilihan sebar belum bisa disiapkan.');
            }
            shareModal?.classList.remove('is-open');
            shareModal?.setAttribute('aria-hidden', 'true');
            if (json.family_url && familyPageButton) {
                familyPageButton.dataset.familyUrl = json.family_url;
            }
            submittedShareIds = new Set(shareCards().map((card) => String(card.dataset.photoId || '')).filter(Boolean));
            updateShareSummary(json.selected_count ?? shareCards().length);
            applyFilter();
            toast(json.message || 'Pilihan sebar sudah disiapkan.', 'success', 'Tersimpan');
        } catch (error) {
            toast(error.message, 'error', 'Gagal menyimpan');
        } finally {
            setLoading(button, false);
        }
    });

    document.querySelector('[data-open-submit-selection]')?.addEventListener('click', () => {
        const total = selectedCards().length;
        if (total <= 0) {
            toast('Pilih minimal satu foto untuk dicetak dahulu.', 'warning', 'Belum ada pilihan');
            return;
        }
        const newTotal = countNewSinceSubmit(selectedCards(), submittedPrintIds);
        if (submitModalCount) submitModalCount.textContent = String(submittedPrintIds.size > 0 && newTotal > 0 ? newTotal : total);
        submitModal?.classList.add('is-open');
        submitModal?.setAttribute('aria-hidden', 'false');
    });
    document.querySelector('[data-close-submit-modal]')?.addEventListener('click', () => {
        submitModal?.classList.remove('is-open');
        submitModal?.setAttribute('aria-hidden', 'true');
    });
    submitModal?.addEventListener('click', (event) => {
        if (event.target === submitModal) {
            submitModal.classList.remove('is-open');
            submitModal.setAttribute('aria-hidden', 'true');
        }
    });
    document.querySelector('[data-confirm-submit-selection]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const total = selectedCards().length;
        if (total <= 0) return;
        const data = new FormData();
        data.append(csrfName, csrfHash);
        setLoading(button, true, 'Mengirim...');
        try {
            const response = await fetch(grid.dataset.submitUrl, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: data,
            });
            const json = await response.json().catch(() => ({}));
            if (json.csrf_hash) csrfHash = json.csrf_hash;
            if (!response.ok || !json.ok) {
                throw new Error(json.message || 'Pilihan cetak belum bisa dikirim.');
            }
            submitModal?.classList.remove('is-open');
            submitModal?.setAttribute('aria-hidden', 'true');
            submittedPrintIds = new Set(selectedCards().map((card) => String(card.dataset.photoId || '')).filter(Boolean));
            updatePrintSummary(json.selected_count ?? selectedCards().length, json.selection_limit);
            applyFilter();
            toast(json.message || 'Pilihan cetak sudah dikirim.', 'success', 'Terkirim');
        } catch (error) {
            toast(error.message, 'error', 'Gagal mengirim');
        } finally {
            setLoading(button, false);
        }
    });

    hydrateFavorites();
    updatePrintSummary(selectedCards().length, grid.dataset.selectionLimit);
    updateShareSummary(shareCards().length);
    syncSharePinVisibility();
})();
</script>
<?php endif; ?>
</body>
</html>
