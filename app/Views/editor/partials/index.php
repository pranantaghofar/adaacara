<?php
    $pageId = (int) ($page['id'] ?? 0);
    $pageTitle = (string) ($page['title'] ?? 'Undangan');
    $pageSlug = (string) ($page['slug'] ?? '');
    $pageStatus = (string) ($page['status'] ?? 'draft');
    $editorJsonColumn = $editorJsonColumn ?? 'editor_json';
    $initialEditorJson = (string) ($page[$editorJsonColumn] ?? $page['editor_json'] ?? $page['grapesjs_json'] ?? '');
    $initialEditorData = json_decode($initialEditorJson, true);
    $pageProjectType = strtolower(trim((string) ($page['project_type'] ?? '')));
    $initialProjectIntent = $pageProjectType !== '' && $pageProjectType !== 'invitation'
        ? $pageProjectType
        : (is_array($initialEditorData) ? strtolower(trim((string) ($initialEditorData['projectIntent'] ?? $initialEditorData['project_intent'] ?? ''))) : '');
    $isPhotoboothIntentProject = in_array($initialProjectIntent, ['photobooth', 'digital_photobooth'], true);
    $isBusinessProfileIntentProject = in_array($initialProjectIntent, ['business_profile', 'business-profile'], true);
    $hasPhotoboothFrameData = is_array($initialEditorData)
        && is_array($initialEditorData['photoboothFrames'] ?? null)
        && count($initialEditorData['photoboothFrames']) > 0;
    $hasPhotoboothProjectSurface = ! $isBusinessProfileIntentProject && ($isPhotoboothIntentProject || $hasPhotoboothFrameData);
    $isPurePhotoboothProject = $hasPhotoboothProjectSurface && $isPhotoboothIntentProject;
    $isHybridPhotoboothProject = $hasPhotoboothProjectSurface && ! $isPurePhotoboothProject;
    $showPhotoboothEditorTab = $hasPhotoboothProjectSurface && ! empty($canUseGuestMemories);
    $hasActiveMembership = ! empty($hasActiveMembership);
    $isLoggedIn = ! empty($isLoggedIn);
    $isActiveCreator = ! empty($isActiveCreator);
    $hasEditorPremiumAccess = $isLoggedIn && ! empty($canUseEditorPremiumFeatures);
    $hasAiPremiumAccess = $isLoggedIn && ! empty($canUseAiPremiumFeatures);
    $canPublishCurrentPage = ! empty($canPublishCurrentPage);
    $importReferenceEnabled = filter_var(env('editor_import_reference_enabled', false), FILTER_VALIDATE_BOOLEAN);
    $referenceMapperEnabled = filter_var(env('editor_reference_mapper_enabled', false), FILTER_VALIDATE_BOOLEAN);
    $ocrTextEnabled = filter_var(env('editor_ocr_text_enabled', false), FILTER_VALIDATE_BOOLEAN);
    $canImportReference = $importReferenceEnabled && $hasAiPremiumAccess;
    $canUseReferenceMapper = $referenceMapperEnabled && ! empty($canUseReferenceMapper);
    $canUseOcrTextDetection = $ocrTextEnabled && ! empty($canUseOcrTextDetection);
    $canUseMagicLayerAi = $ocrTextEnabled && $hasAiPremiumAccess;
    $showMagicLayerAiPanel = $ocrTextEnabled;
    $showImportReferencePanel = $importReferenceEnabled || $referenceMapperEnabled || $ocrTextEnabled;
    $showPublishButton = ! $isActiveCreator;
    $plansUrl = $plansUrl ?? site_url('plans');
    $businessProfilePaymentReady = ! empty($businessProfilePaymentReady);
    $hasBusinessProfileEntitlement = ! empty($hasBusinessProfileEntitlement);
    $businessProfileCheckoutUrl = (string) ($businessProfileCheckoutUrl ?? '');
    $publishButtonTitle = $isLoggedIn
        ? ($canPublishCurrentPage ? 'Publish' : 'Aktifkan paket untuk publish')
        : 'Login untuk publish';
    $premiumCrownState = $hasEditorPremiumAccess ? 'is-unlocked' : 'is-locked';
    $premiumCrownSvg = '<span class="aa-premium-crown ' . $premiumCrownState . '" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.91 4.91a1.91 1.91 0 0 1-1.044 1.701c.942 2.366 1.928 3.53 2.795 3.622.982.104 1.88-.323 2.76-1.377a.977.977 0 0 1 .072-.078 1.91 1.91 0 1 1 1.468.873l-1.423 5.42c-.297 1.13-1.363 1.922-2.586 1.922H8.066c-1.223 0-2.29-.792-2.586-1.922L4.063 9.675a1.91 1.91 0 1 1 1.46-.898c.03.028.059.06.086.093.837 1.048 1.727 1.471 2.748 1.363.908-.096 1.888-1.253 2.793-3.614a1.91 1.91 0 1 1 2.76-1.71ZM6.561 19.008h10.875c.518 0 .938.448.938 1s-.42 1-.938 1H6.563c-.517 0-.937-.448-.937-1s.42-1 .937-1Z" fill="currentColor"></path></svg></span>';
    $aiPremiumCrownState = $hasAiPremiumAccess ? 'is-unlocked' : 'is-locked';
    $aiPremiumCrownSvg = '<span class="aa-premium-crown ' . $aiPremiumCrownState . '" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.91 4.91a1.91 1.91 0 0 1-1.044 1.701c.942 2.366 1.928 3.53 2.795 3.622.982.104 1.88-.323 2.76-1.377a.977.977 0 0 1 .072-.078 1.91 1.91 0 1 1 1.468.873l-1.423 5.42c-.297 1.13-1.363 1.922-2.586 1.922H8.066c-1.223 0-2.29-.792-2.586-1.922L4.063 9.675a1.91 1.91 0 1 1 1.46-.898c.03.028.059.06.086.093.837 1.048 1.727 1.471 2.748 1.363.908-.096 1.888-1.253 2.793-3.614a1.91 1.91 0 1 1 2.76-1.71ZM6.561 19.008h10.875c.518 0 .938.448.938 1s-.42 1-.938 1H6.563c-.517 0-.937-.448-.937-1s.42-1 .937-1Z" fill="currentColor"></path></svg></span>';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? ('Editor - ' . $pageTitle)) ?></title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Great+Vibes&family=Montserrat:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Aboreto&family=Abril+Fatface&family=Adamina&family=Alex+Brush&family=Allura&family=Amarante&family=Amiri:wght@400;700&family=Arizonia&family=Bellefair&family=Bodoni+Moda:wght@400;500;600;700&family=Bonheur+Royale&family=Caudex:wght@400;700&family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600;700&family=Cormorant+Infant:wght@400;500;600;700&family=Cormorant+Upright:wght@400;500;600;700&family=DM+Serif+Display&family=Dancing+Script:wght@400;500;600;700&family=Elsie:wght@400;900&family=Ephesis&family=Fleur+De+Leah&family=Forum&family=Fraunces:wght@400;500;600;700&family=Great+Vibes&family=Imperial+Script&family=Italiana&family=Italianno&family=Lavishly+Yours&family=Libre+Baskerville:wght@400;700&family=Lora:wght@400;500;600;700&family=Marcellus&family=Mea+Culpa&family=Monsieur+La+Doulaise&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Parisienne&family=Petit+Formal+Script&family=Philosopher:wght@400;700&family=Playfair+Display:wght@400;500;600;700;800;900&family=Poiret+One&family=Prata&family=Questrial&family=Quintessential&family=Sorts+Mill+Goudy&family=Tangerine:wght@400;700&family=The+Nautigal:wght@400;700&family=Unna:wght@400;700&family=Viaoda+Libre&family=WindSong:wght@400;500&family=Yeseva+One&display=swap"
        rel="stylesheet">
    <!-- <link
        href="https://fonts.googleapis.com/css2?family=Aboreto&family=Abril+Fatface&family=Adamina&family=Alex+Brush&family=Allura&family=Amarante&family=Amiri:wght@400;700&family=Arizonia&family=Bellefair&family=Bodoni+Moda:wght@400;500;600;700&family=Bonheur+Royale&family=Caudex:wght@400;700&family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600;700&family=Cormorant+Infant:wght@400;500;600;700&family=Cormorant+Upright:wght@400;500;600;700&family=DM+Serif+Display&family=Dancing+Script:wght@400;500;600;700&family=Elsie:wght@400;900&family=Ephesis&family=Fleur+De+Leah&family=Forum&family=Fraunces:wght@400;500;600;700&family=Great+Vibes&family=Imperial+Script&family=Italiana&family=Italianno&family=Lavishly+Yours&family=Libre+Baskerville:wght@400;700&family=Lora:wght@400;500;600;700&family=Marcellus&family=Mea+Culpa&family=Monsieur+La+Doulaise&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Parisienne&family=Petit+Formal+Script&family=Philosopher:wght@400;700&family=Playfair+Display:wght@400;500;600;700;800;900&family=Poiret+One&family=Prata&family=Questrial&family=Quintessential&family=Sorts+Mill+Goudy&family=Tangerine:wght@400;700&family=The+Nautigal:wght@400;700&family=Unna:wght@400;700&family=Viaoda+Libre&family=WindSong:wght@400;500&family=Yeseva+One&display=swap"
        rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }

            var resizeDropdown = document.querySelector('[data-aa-resize-dropdown]');
            var resizeToggle = document.getElementById('aaResizeMenuBtn');
            var resizePanel = document.getElementById('aaResizeMenuPanel');

            function setResizeMenu(open) {
                if (!resizeDropdown || !resizeToggle || !resizePanel) {
                    return;
                }

                resizeDropdown.classList.toggle('is-open', open);
                resizeToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                resizePanel.hidden = !open;
            }

            resizeToggle?.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                setResizeMenu(!resizeDropdown?.classList.contains('is-open'));
            });

            resizePanel?.addEventListener('click', function (event) {
                if (event.target.closest('button')) {
                    setResizeMenu(false);
                }
            });

            document.addEventListener('click', function (event) {
                if (resizeDropdown && !resizeDropdown.contains(event.target)) {
                    setResizeMenu(false);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setResizeMenu(false);
                }
            });
        });
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
    <style>
        .aa-recent-wrap {
            margin-top: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 14px;
            padding: 8px;
        }

        .aa-recent-wrap.hidden {
            display: none !important;
        }

        #aaRecentFontWrap.hidden {
            display: none !important;
        }

        #aaRecentFontWrap:not(.hidden) {
            display: block !important;
        }
        .aa-recent-title {
            margin-bottom: 6px;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #64748b;
        }

        .aa-recent-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .aa-recent-font-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
            max-width: 145px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .aa-recent-font-btn:hover {
            border-color: #0f766e;
            color: #0f766e;
        }

        .aa-recent-color-btn {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 1px #cbd5e1;
            cursor: pointer;
            padding: 0;
        }

        .aa-recent-color-btn:hover {
            box-shadow: 0 0 0 2px #0f766e;
        }
        #aaActiveArtboardFrame {
        position: relative;
        }

       .aa-history-freeze-overlay {
            position: absolute;
            inset: 0;
            z-index: 9999;
            pointer-events: none;
            background: #fff;
            opacity: 1;
            transition: opacity .38s cubic-bezier(.16, 1, .3, 1);
            overflow: hidden;
        }

        .aa-history-freeze-overlay img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        .aa-history-freeze-overlay.is-fading {
            opacity: 0;
        }
        #aaFabricCanvas,
        .canvas-container,
        .upper-canvas,
        .lower-canvas {
            transition: opacity .16s ease;
        }

        body.aa-history-restoring #aaFabricCanvas,
        body.aa-history-restoring .canvas-container {
            opacity: .98;
        }

        body.aa-business-profile-editor #aaEditPhotoboothBtn,
        body.aa-business-profile-editor #aaPhotoboothEntryToast,
        body.aa-business-profile-editor #aaPublishChoicePhotoboothBtn,
        body.aa-business-profile-editor #aaGuestbookPanel,
        body.aa-business-profile-editor #aaGuestbookEditorPreview {
            display: none !important;
        }

        body.aa-editor-tool-limited-mode [data-aa-limited-editor-tab="true"] {
            display: none !important;
        }

        .aa-editor-brand-type-pill {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            max-width: 100%;
            margin-top: 3px;
            border-radius: 999px;
            background: rgba(20, 184, 166, .14);
            color: #99f6e4;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: 900;
            line-height: 1.35;
            text-transform: uppercase;
            letter-spacing: .08em;
            white-space: nowrap;
        }
    </style>

    <link href="<?= esc($customFontCssUrl ?? site_url('custom-fonts.css'), 'attr') ?>" rel="stylesheet">
    <?= view('editor/partials/styles') ?>
</head>

<body class="aa-app-ui<?= $isBusinessProfileIntentProject ? ' aa-business-profile-editor' : '' ?>">
    <div id="aaDesktopOnlyModal" class="aa-desktop-only-modal" role="dialog" aria-modal="true"
        aria-labelledby="aaDesktopOnlyTitle">
        <div class="aa-desktop-only-card">
            <div class="aa-desktop-only-icon"><i class="fa fa-desktop"></i></div>
            <h2 id="aaDesktopOnlyTitle">Buka lewat laptop atau PC dulu ya</h2>
            <p>Silakan buka AdaAcara Design Studio melalui laptop atau PC agar canvas, drag, resize, dan publish
                berjalan stabil. Dukungan mobile sedang dalam pengembangan.</p>
        </div>
    </div>

    <?= view('editor/partials/context_toolbar', get_defined_vars()) ?>

    <div class="aa-studio-shell">
        <header class="aa-topbar">
            <div class="aa-topbar-brand min-w-0">
                <p class="m-0 text-[11px] font-black uppercase tracking-[.22em] text-teal-200">AdaAcara Design Studio
                </p>
                <h1 class="m-0 truncate text-base font-black"><?= esc($pageTitle) ?></h1>
                <?php if ($isBusinessProfileIntentProject): ?>
                <span class="aa-editor-brand-type-pill">Business Profile</span>
                <?php endif ?>
            </div>

            <div class="aa-topbar-controls flex min-w-0 flex-1 items-center justify-center gap-2">
                <div class="aa-topbar-group aa-topbar-history" aria-label="Riwayat perubahan">
                    <button id="aaUndoBtn" class="aa-action-btn" type="button"><i
                            class="fa fa-rotate-left"></i>Undo</button>
                    <button id="aaRedoBtn" class="aa-action-btn" type="button"><i
                            class="fa fa-rotate-right"></i>Redo</button>
                </div>
                <div class="aa-topbar-size-controls" aria-label="Resize halaman" data-aa-resize-dropdown>
                    <button id="aaResizeMenuBtn" class="aa-action-btn aa-resize-menu-btn" type="button"
                        aria-haspopup="menu" aria-expanded="false" aria-controls="aaResizeMenuPanel">
                        RESIZE <i class="fa fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div id="aaResizeMenuPanel" class="aa-resize-menu-panel" role="menu" aria-label="Pilihan resize"
                        hidden>
                        <button id="aaPortraitBtn" class="aa-action-btn" type="button" role="menuitem">9:16</button>
                        <button id="aaTallPortraitBtn" class="aa-action-btn" type="button" role="menuitem">9:19</button>
                        <button id="aaSquareBtn" class="aa-action-btn" type="button" role="menuitem">Square</button>
                    </div>
                </div>
                <div class="aa-topbar-group aa-topbar-zoom" aria-label="Zoom canvas">
                    <button id="aaZoomOutBtn" class="aa-action-btn" type="button"><i class="fa fa-minus"></i></button>
                    <span id="aaZoomLabel" class="min-w-14 text-center text-xs font-black text-slate-300">100%</span>
                    <button id="aaZoomInBtn" class="aa-action-btn" type="button"><i class="fa fa-plus"></i></button>
                    <button id="aaFitBtn" class="aa-action-btn" type="button"><i class="fa fa-expand"></i>Fit</button>
                </div>
                <span id="aaSaveState" class="aa-editor-status-pill" role="status" aria-live="polite">Siap</span>
            </div>

            <nav class="aa-topbar-actions flex shrink-0 items-center gap-2">
                <button id="aaPreviewBtn" class="aa-action-btn" type="button">Preview</button>
                <button id="aaSaveBtn" class="aa-action-btn aa-primary" type="button">Save Draft</button>
                <?php if (! empty($canSaveTemplate)): ?>
                <button id="saveTemplateBtn" class="aa-action-btn" type="button">Save as Template</button>
                <?php endif ?>
                <?php if ($showPublishButton): ?>
                <button id="aaPublishBtn" class="aa-action-btn aa-publish" type="button"
                    title="<?= esc($publishButtonTitle, 'attr') ?>">Publish</button>
                <?php endif ?>
            </nav>
        </header>

        <main class="aa-workspace">
            <?= view('editor/partials/left_drawer', get_defined_vars()) ?>

            <section id="aaStageWrap" class="aa-stage-wrap">
                <div id="aaStageViewport" class="aa-stage-viewport">
                    <div id="aaStage" class="aa-stage">
                        <div class="aa-editor-mode-strip">
                            <div class="aa-editor-mode-toggle" aria-label="Mode editor">
                                <?php if ($isBusinessProfileIntentProject): ?>
                                <button id="aaEditBusinessProfilePagesBtn" class="aa-editor-mode-btn is-active" type="button">Business Profile</button>
                                <?php elseif ($isPurePhotoboothProject): ?>
                                <?php if ($showPhotoboothEditorTab): ?>
                                <button id="aaEditPhotoboothBtn" class="aa-editor-mode-btn is-active" type="button">Photobooth</button>
                                <?php else: ?>
                                <button id="aaEditPhotoboothLockedBtn" class="aa-editor-mode-btn is-active is-locked" type="button" aria-disabled="true" title="Photobooth membutuhkan paket Plus dan aktivasi admin.">Photobooth</button>
                                <?php endif ?>
                                <?php else: ?>
                                <?php if ($showPhotoboothEditorTab): ?>
                                <button id="aaEditPhotoboothBtn" class="aa-editor-mode-btn" type="button">Photobooth</button>
                                <?php endif ?>
                                <button id="aaEditOpeningBtn" class="aa-editor-mode-btn" type="button">Opening</button>
                                <button id="aaEditPagesBtn" class="aa-editor-mode-btn is-active" type="button">Halaman</button>
                                <?php endif ?>
                            </div>
                        </div>
                        <div id="aaPageList" class="editor-pages-scroll"></div>
                        <div id="aaActiveArtboardFrame" class="aa-artboard-frame">
                            <canvas id="aaFabricCanvas" width="1080" height="1920"></canvas>
                            <div id="aaCanvasLoading" class="aa-canvas-loading" role="status" aria-live="polite">
                                <span class="aa-canvas-loading-label" data-aa-canvas-loading-label><i class="fa fa-circle-notch"></i> Memuat desain...</span>
                            </div>
                        </div>
                        <div id="aaGuestbookEditorPreview" class="aa-editor-guestbook-preview is-hidden"></div>
                    </div>
                </div>
            </section>

            <aside class="aa-rightbar p-4">
                <section class="aa-panel-card">
                    <h2 class="aa-panel-title">Properties</h2>
                    <p id="aaSelectionHint" class="mb-3 text-sm font-bold text-slate-500">Pilih elemen di canvas untuk
                        mengedit.</p>

                    <div id="aaObjectControls" class="grid gap-3">
                        <label class="grid gap-1 text-xs font-black text-slate-600">
                            Text
                            <textarea id="aaTextInput" class="aa-field min-h-24 py-2" rows="3"></textarea>
                        </label>
                        <!-- <label class="grid gap-1 text-xs font-black text-slate-600">
                            Font
                            <select id="aaFontInput" class="aa-field">
                                <option value="Playfair Display">Playfair Display</option>
                                <option value="Cormorant Garamond">Cormorant Garamond</option>
                                <option value="Great Vibes">Great Vibes</option>
                                <option value="Montserrat">Montserrat</option>
                                <option value="Poppins">Poppins</option>
                                <option value="Inter">Inter</option>
                                <option value="Arial">Arial</option>
                                <option value="Georgia">Georgia</option>
                            </select>
                        </label> -->
                        <label style="display: none;" class="grid gap-1 text-xs font-black text-slate-600">
                            Font
                            <select id="aaFontInput" class="aa-field">

                                <optgroup label="Wedding Script / Kaligrafi">
                                    <option style="" value="Great Vibes">Great Vibes</option>
                                    <option value="Allura">Allura</option>
                                    <option value="Alex Brush">Alex Brush</option>
                                    <option value="Parisienne">Parisienne</option>
                                    <option value="Dancing Script">Dancing Script</option>
                                    <option value="Imperial Script">Imperial Script</option>
                                    <option value="Fleur De Leah">Fleur De Leah</option>
                                    <option value="Lavishly Yours">Lavishly Yours</option>
                                    <option value="Italianno">Italianno</option>
                                    <option value="Arizonia">Arizonia</option>
                                    <option value="Bonheur Royale">Bonheur Royale</option>
                                    <option value="Ephesis">Ephesis</option>
                                    <option value="Mea Culpa">Mea Culpa</option>
                                    <option value="Monsieur La Doulaise">Monsieur La Doulaise</option>
                                    <option value="Petit Formal Script">Petit Formal Script</option>
                                    <option value="Tangerine">Tangerine</option>
                                    <option value="The Nautigal">The Nautigal</option>
                                    <option value="WindSong">WindSong</option>
                                </optgroup>

                                <optgroup label="Luxury Serif / Elegan">
                                    <option value="Playfair Display">Playfair Display</option>
                                    <option value="Cormorant Garamond">Cormorant Garamond</option>
                                    <option value="Cormorant Infant">Cormorant Infant</option>
                                    <option value="Cormorant Upright">Cormorant Upright</option>
                                    <option value="Bodoni Moda">Bodoni Moda</option>
                                    <option value="Prata">Prata</option>
                                    <option value="DM Serif Display">DM Serif Display</option>
                                    <option value="Libre Baskerville">Libre Baskerville</option>
                                    <option value="Lora">Lora</option>
                                    <option value="Fraunces">Fraunces</option>
                                    <option value="Cinzel">Cinzel</option>
                                    <option value="Marcellus">Marcellus</option>
                                    <option value="Forum">Forum</option>
                                    <option value="Italiana">Italiana</option>
                                    <option value="Bellefair">Bellefair</option>
                                    <option value="Caudex">Caudex</option>
                                    <option value="Adamina">Adamina</option>
                                    <option value="Unna">Unna</option>
                                    <option value="Sorts Mill Goudy">Sorts Mill Goudy</option>
                                    <option value="Yeseva One">Yeseva One</option>
                                </optgroup>

                                <optgroup label="Unik / Artistik">
                                    <option value="Aboreto">Aboreto</option>
                                    <option value="Abril Fatface">Abril Fatface</option>
                                    <option value="Amarante">Amarante</option>
                                    <option value="Elsie">Elsie</option>
                                    <option value="Poiret One">Poiret One</option>
                                    <option value="Quintessential">Quintessential</option>
                                    <option value="Viaoda Libre">Viaoda Libre</option>
                                    <option value="Philosopher">Philosopher</option>
                                </optgroup>

                                <optgroup label="Clean Modern">
                                    <option value="Inter">Inter</option>
                                    <option value="Poppins">Poppins</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Questrial">Questrial</option>
                                    <option value="Arial">Arial</option>
                                    <option value="Georgia">Georgia</option>
                                </optgroup>

                                <optgroup label="Google Modern Sans">
                                    <option value="Google Sans">Google Sans</option>
                                    <option value="Nunito Sans">Nunito Sans</option>
                                    <option value="DM Sans">DM Sans</option>
                                    <option value="Ubuntu">Ubuntu</option>
                                    <option value="Kanit">Kanit</option>
                                    <option value="Outfit">Outfit</option>
                                    <option value="Prompt">Prompt</option>
                                    <option value="IBM Plex Sans">IBM Plex Sans</option>
                                    <option value="Source Sans 3">Source Sans 3</option>
                                    <option value="Barlow">Barlow</option>
                                    <option value="Jost">Jost</option>
                                    <option value="Fira Sans">Fira Sans</option>
                                    <option value="Titillium Web">Titillium Web</option>
                                    <option value="Heebo">Heebo</option>
                                    <option value="Libre Franklin">Libre Franklin</option>
                                    <option value="Public Sans">Public Sans</option>
                                    <option value="Sora">Sora</option>
                                    <option value="Inter Tight">Inter Tight</option>
                                    <option value="Red Hat Display">Red Hat Display</option>
                                    <option value="Dosis">Dosis</option>
                                    <option value="Cabin">Cabin</option>
                                    <option value="Assistant">Assistant</option>
                                </optgroup>

                                <optgroup label="Google Serif / Editorial">
                                    <option value="Roboto Slab">Roboto Slab</option>
                                    <option value="PT Serif">PT Serif</option>
                                    <option value="EB Garamond">EB Garamond</option>
                                    <option value="Bitter">Bitter</option>
                                    <option value="Instrument Serif">Instrument Serif</option>
                                    <option value="Crimson Text">Crimson Text</option>
                                </optgroup>

                                <optgroup label="Google Display / Mono">
                                    <option value="Bebas Neue">Bebas Neue</option>
                                    <option value="Anton">Anton</option>
                                    <option value="Archivo Black">Archivo Black</option>
                                    <option value="Alfa Slab One">Alfa Slab One</option>
                                    <option value="Black Ops One">Black Ops One</option>
                                    <option value="Changa One">Changa One</option>
                                    <option value="Lobster Two">Lobster Two</option>
                                    <option value="Roboto Mono">Roboto Mono</option>
                                    <option value="Inconsolata">Inconsolata</option>
                                    <option value="Source Code Pro">Source Code Pro</option>
                                    <option value="JetBrains Mono">JetBrains Mono</option>
                                </optgroup>

                                <?php if (! empty($customFonts) && is_array($customFonts)): ?>
                                <optgroup label="Custom Fonts Admin">
                                    <?php foreach ($customFonts as $customFont): ?>
                                        <?php
                                            $customFamily = (string) ($customFont['family'] ?? '');
                                            $customWeights = array_values(array_filter((array) ($customFont['weights'] ?? ['400'])));
                                        ?>
                                        <?php if ($customFamily !== ''): ?>
                                            <option value="<?= esc($customFamily, 'attr') ?>" data-font-source="custom" data-font-weights="<?= esc(implode(';', $customWeights), 'attr') ?>"><?= esc($customFamily) ?></option>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </optgroup>
                                <?php endif ?>

                                <!-- <optgroup label="Bunny Fonts Tambahan">
                                    <option value="Roboto" data-font-source="bunny">Roboto</option>
                                    <option value="Open Sans" data-font-source="bunny">Open Sans</option>
                                    <option value="Lato" data-font-source="bunny">Lato</option>
                                    <option value="Oswald" data-font-source="bunny">Oswald</option>
                                    <option value="Raleway" data-font-source="bunny">Raleway</option>
                                    <option value="Nunito" data-font-source="bunny">Nunito</option>
                                    <option value="Merriweather" data-font-source="bunny">Merriweather</option>
                                    <option value="Work Sans" data-font-source="bunny">Work Sans</option>
                                    <option value="Rubik" data-font-source="bunny">Rubik</option>
                                    <option value="Manrope" data-font-source="bunny">Manrope</option>
                                    <option value="Plus Jakarta Sans" data-font-source="bunny">Plus Jakarta Sans
                                    </option>
                                    <option value="Urbanist" data-font-source="bunny">Urbanist</option>
                                    <option value="Josefin Sans" data-font-source="bunny">Josefin Sans</option>
                                    <option value="Quicksand" data-font-source="bunny">Quicksand</option>
                                    <option value="Noto Sans" data-font-source="bunny">Noto Sans</option>
                                    <option value="Noto Serif" data-font-source="bunny">Noto Serif</option>
                                    <option value="Mulish" data-font-source="bunny">Mulish</option>
                                    <option value="Karla" data-font-source="bunny">Karla</option>
                                    <option value="Oxygen" data-font-source="bunny">Oxygen</option>
                                    <option value="Archivo" data-font-source="bunny">Archivo</option>
                                    <option value="Figtree" data-font-source="bunny">Figtree</option>
                                    <option value="Space Grotesk" data-font-source="bunny">Space Grotesk</option>
                                    <option value="Sacramento" data-font-source="bunny">Sacramento</option>
                                    <option value="Pacifico" data-font-source="bunny">Pacifico</option>
                                    <option value="Caveat" data-font-source="bunny">Caveat</option>
                                    <option value="Courgette" data-font-source="bunny">Courgette</option>
                                    <option value="Satisfy" data-font-source="bunny">Satisfy</option>
                                    <option value="Cookie" data-font-source="bunny">Cookie</option>
                                </optgroup> -->

                                <optgroup label="Arabic / Islami">
                                    <option value="Amiri">Amiri</option>
                                    <option value="Noto Naskh Arabic">Noto Naskh Arabic</option>
                                </optgroup>

                            </select>
                        </label>
                        <div style="display: none;" class="grid grid-cols-2 gap-2">
                            <label class="grid gap-1 text-xs font-black text-slate-600">
                                Size
                                <input id="aaFontSizeInput" class="aa-field" type="number" min="8" max="260">
                            </label>
                            <label class="grid gap-1 text-xs font-black text-slate-600">
                                Color
                                <input id="aaColorInput" class="aa-field" type="color" value="#111827">
                            </label>
                        </div>
                        <div style="display: none;" id="aaGuestFieldBgPanel" class="hidden">
                            <label class="grid gap-1 text-xs font-black text-slate-600">
                                Background Field
                                <input id="aaGuestFieldBgInput" class="aa-field" type="color" value="#ffffff">
                            </label>
                        </div>
                        <div style="display: none;" class="grid grid-cols-4 gap-2">
                            <button data-aa-align="left" class="aa-panel-btn" type="button"><i
                                    class="fa fa-align-left"></i></button>
                            <button data-aa-align="center" class="aa-panel-btn" type="button"><i
                                    class="fa fa-align-center"></i></button>
                            <button data-aa-align="right" class="aa-panel-btn" type="button"><i
                                    class="fa fa-align-right"></i></button>
                            <button data-aa-align="justify" class="aa-panel-btn" type="button"><i
                                    class="fa fa-align-justify"></i></button>
                        </div>
                        <div style="display: none;" class="grid grid-cols-3 gap-2">
                            <button id="aaBoldBtn" class="aa-panel-btn" type="button" title="Bold"><i
                                    class="fa fa-bold"></i></button>
                            <button id="aaItalicBtn" class="aa-panel-btn" type="button" title="Italic"><i
                                    class="fa fa-italic"></i></button>
                            <button id="aaUnderlineBtn" class="aa-panel-btn" type="button" title="Underline"><i
                                    class="fa fa-underline"></i></button>
                        </div>
                        <div style="display: none;" class="grid grid-cols-2 gap-2">
                            <button id="aaReplaceImageBtn" class="aa-panel-btn" type="button">Replace Image</button>
                            <button id="aaDuplicateBtn" class="aa-panel-btn" type="button">Duplicate</button>
                            <button id="aaForwardBtn" class="aa-panel-btn" type="button">Forward</button>
                            <button id="aaBackwardBtn" class="aa-panel-btn" type="button">Backward</button>
                            <button id="aaDeleteBtn" class="aa-panel-btn border-rose-200 text-rose-700"
                                type="button">Delete</button>
                        </div>
                        <p style="display: none;" class="text-[11px] font-bold leading-relaxed text-slate-500">Resize,
                            rotate, dan drag memakai
                            kontrol bawaan Fabric seperti biasa. Crop gambar diatur dari panel Crop di bawah.</p>

                        <div style="display: none;" id="aaLinkPanel"
                            class="aa-toolbar-owned-panel hidden rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <h3 class="aa-panel-title !mb-2">Link Text</h3>
                            <label class="grid gap-1 text-xs font-black text-slate-600">
                                Link tujuan
                                <input id="aaLinkUrlInput" class="aa-field" type="url"
                                    placeholder="https://maps.google.com/...">
                            </label>
                            <p class="mt-2 text-[11px] font-bold leading-relaxed text-slate-500">Link aktif di
                                preview/publish. Cocok untuk Google Maps, link meeting, atau halaman website.</p>
                        </div>

                        <div style="display: none;" id="aaCopyPanel"
                            class="aa-toolbar-owned-panel hidden rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <h3 class="aa-panel-title !mb-2">Copy Text</h3>
                            <label class="mt-2 grid gap-1 text-xs font-black text-slate-600">
                                Teks yang dicopy
                                <textarea id="aaCopyTextInput" class="aa-field min-h-20 py-2" rows="3"
                                    placeholder="Teks, nomor rekening, alamat, atau link yang akan dicopy"></textarea>
                            </label>
                            <label class="mt-2 grid gap-1 text-xs font-black text-slate-600">
                                Pesan setelah dicopy
                                <input id="aaCopyFeedbackInput" class="aa-field" type="text" placeholder="Tersalin">
                            </label>
                            <p class="mt-2 text-[11px] font-bold leading-relaxed text-slate-500">Copy aktif di
                                preview/publish. Cocok untuk nomor rekening, kode voucher, alamat, atau teks pendek.</p>
                        </div>

                        <div style="display: none;" id="aaGuestNameFormatPanel"
                            class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <h3 class="aa-panel-title !mb-2">Nama Tamu Dinamis</h3>
                            <label class="grid gap-1 text-xs font-black text-slate-600">
                                Format Nama Tamu
                                <textarea id="aaGuestNameFormatInput" class="aa-field min-h-20 py-2" rows="3"
                                    placeholder="Kepada Yth.&#10;{{guest_name}}"></textarea>
                            </label>
                            <p class="mt-2 text-[11px] font-bold leading-relaxed text-slate-500">Gunakan
                                {{guest_name}} untuk nama dari URL public.</p>
                        </div>

                        <div id="aaInteractivePanel" class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <h3 class="aa-panel-title !mb-2">Interactive Element</h3>
                            <div class="mb-2 grid grid-cols-2 gap-2">
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Background
                                    <input id="aaInteractiveBgInput" class="aa-field" type="color" value="#ffffff">
                                </label>
                                <label id="aaInteractiveRadiusWrap"
                                    class="grid gap-1 text-xs font-black text-slate-600">
                                    Border Radius
                                    <input id="aaInteractiveRadiusInput" class="aa-field" type="number" min="0"
                                        max="120" step="1" value="22">
                                </label>
                            </div>
                            <div style="display: none;" id="aaMusicSettings" class="hidden grid gap-2">
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Audio URL
                                    <input id="aaAudioUrlInput" class="aa-field" type="url"
                                        placeholder="https://.../musik.mp3">
                                </label>
                                <label class="flex items-center gap-2 text-xs font-black text-slate-600">
                                    <input id="aaAudioAutoplayInput" type="checkbox">
                                    Autoplay setelah interaksi
                                </label>
                                <label class="flex items-center gap-2 text-xs font-black text-slate-600">
                                    <input id="aaAudioLoopInput" type="checkbox">
                                    Loop audio
                                </label>
                                <label class="flex items-center gap-2 text-xs font-black text-slate-600">
                                    <input id="aaAudioShowButtonInput" type="checkbox">
                                    Tampilkan tombol player
                                </label>
                            </div>
                            <div style="display: none;" id="aaCountdownSettings"
                                class="aa-toolbar-owned-panel hidden grid gap-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                        Event Date
                                        <span class="aa-date-field">
                                            <input id="aaCountdownDateInput" class="aa-field" type="text"
                                                inputmode="numeric" maxlength="10" autocomplete="off"
                                                placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}">
                                            <button id="aaCountdownDatePickerBtn" class="aa-date-button" type="button"
                                                aria-label="Pilih tanggal"><i class="fa fa-calendar-days"></i></button>
                                        </span>
                                    </label>
                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                        Event Time
                                        <input id="aaCountdownTimeInput" class="aa-field" type="time">
                                    </label>
                                </div>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Gap Satuan Waktu
                                    <input id="aaCountdownGapInput" class="aa-field" type="number" min="0" max="60"
                                        step="1" value="10">
                                </label>
                            </div>
                            <label id="aaScrollLockWrap"
                                class="hidden mt-2 flex items-center gap-2 text-xs font-black text-slate-600">
                                <input id="aaScrollLockInput" type="checkbox">
                                Mode slide aman untuk halaman ini
                            </label>
                            <div id="aaGallerySettings" class="hidden grid gap-2">
                                <div class="aa-gallery-toolbar">
                                    <button id="aaGalleryUploadBtn" class="aa-panel-btn" type="button"><i
                                            class="fa fa-upload"></i>Upload
                                        Foto<?= $premiumCrownSvg ?></button>
                                    <button id="aaGalleryPickMediaBtn" class="aa-panel-btn" type="button"><i
                                            class="fa fa-photo-film"></i>Pilih Media</button>
                                </div>
                                <div id="aaGalleryItemList" class="aa-gallery-list"></div>
                                <textarea id="aaGalleryImagesInput" class="hidden" rows="4"></textarea>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                        Columns
                                        <input id="aaGalleryColumnsInput" class="aa-field" type="number" min="1" max="6"
                                            step="1">
                                    </label>
                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                        Gap
                                        <input id="aaGalleryGapInput" class="aa-field" type="number" min="0" max="80"
                                            step="1">
                                    </label>
                                </div>
                                <label class="grid gap-1 text-xs font-black text-slate-600 aa-range-field">
                                    Border Radius
                                    <input id="aaGalleryRadiusInput" class="aa-gallery-radius-slider" type="range"
                                        min="0" max="80" step="1">
                                </label>
                            </div>
                            <div id="aaSocialSettings" class="hidden grid gap-2">
                                <h4 class="m-0 text-xs font-black uppercase tracking-[.12em] text-slate-500">Social Media</h4>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Judul
                                    <input id="aaSocialTitleInput" class="aa-field" type="text" maxlength="80">
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="grid gap-1 text-xs font-black text-slate-600">Instagram<input id="aaSocialInstagramInput" class="aa-field" type="url" placeholder="https://instagram.com/..."></label>
                                    <label class="grid gap-1 text-xs font-black text-slate-600">TikTok<input id="aaSocialTiktokInput" class="aa-field" type="url" placeholder="https://tiktok.com/@..."></label>
                                    <label class="grid gap-1 text-xs font-black text-slate-600">Threads<input id="aaSocialThreadsInput" class="aa-field" type="url" placeholder="https://threads.net/@..."></label>
                                    <label class="grid gap-1 text-xs font-black text-slate-600">X<input id="aaSocialXInput" class="aa-field" type="url" placeholder="https://x.com/..."></label>
                                    <label class="grid gap-1 text-xs font-black text-slate-600">Facebook<input id="aaSocialFacebookInput" class="aa-field" type="url" placeholder="https://facebook.com/..."></label>
                                    <label class="grid gap-1 text-xs font-black text-slate-600">YouTube<input id="aaSocialYoutubeInput" class="aa-field" type="url" placeholder="https://youtube.com/..."></label>
                                </div>
                            </div>
                            <div id="aaStorySettings" class="hidden grid gap-2">
                                <h4 class="m-0 text-xs font-black uppercase tracking-[.12em] text-slate-500">Story Maker</h4>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Judul Section
                                    <input id="aaStoryTitleInput" class="aa-field" type="text" maxlength="90">
                                </label>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Isi Cerita
                                    <textarea id="aaStoryItemsInput" class="aa-field min-h-32 py-2" rows="7" placeholder="Judul | YYYY-MM-DD | Deskripsi cerita&#10;Pertama Bertemu | 2021-03-12 | Kami bertemu untuk pertama kalinya..."></textarea>
                                </label>
                                <p class="m-0 text-[11px] font-bold leading-relaxed text-slate-500">Satu cerita per baris. Format: Judul | Tanggal | Deskripsi.</p>
                            </div>
                        </div>

                        <div style="display: none;" id="aaImageRadiusPanel"
                            class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <h3 class="aa-panel-title !mb-2">Image Radius</h3>
                            <label class="grid gap-1 text-xs font-black text-slate-600 aa-range-field">
                                <span class="flex items-center justify-between gap-2">
                                    <span>Border Radius</span>
                                    <span id="aaImageRadiusValue"
                                        class="text-[11px] font-black text-slate-500">0px</span>
                                </span>
                                <input id="aaImageRadiusInput" class="aa-gallery-radius-slider" type="range" min="0"
                                    max="540" step="1" value="0">
                            </label>
                        </div>

                        <div style="display: none;" id="aaCropPanel"
                            class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <h3 class="aa-panel-title !mb-2">Crop Image</h3>
                            <div id="aaCropFields" class="hidden grid grid-cols-2 gap-2">
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    X
                                    <input id="aaCropXInput" class="aa-field" type="number" min="0" step="1">
                                </label>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Y
                                    <input id="aaCropYInput" class="aa-field" type="number" min="0" step="1">
                                </label>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Width
                                    <input id="aaCropWidthInput" class="aa-field" type="number" min="1" step="1">
                                </label>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Height
                                    <input id="aaCropHeightInput" class="aa-field" type="number" min="1" step="1">
                                </label>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <button id="aaStartCropBtn" class="aa-panel-btn" type="button">Crop</button>
                                <button id="aaApplyCropBoxBtn" class="aa-panel-btn hidden" type="button">Apply</button>
                                <button id="aaCancelCropBtn" class="aa-panel-btn hidden" type="button">Cancel</button>
                                <button id="aaResetCropBtn" class="aa-panel-btn" type="button">Reset Crop</button>
                            </div>
                            <p id="aaCropHint" class="mt-2 text-[11px] font-bold leading-relaxed text-slate-500"></p>
                        </div>

                        <div id="aaGuestbookPanel" class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <h3 class="aa-panel-title !mb-0">Guestbook Widget</h3>
                                <button id="aaGuestbookHideBtn" class="aa-panel-btn !min-h-8 px-2 text-[11px]"
                                    type="button">Hide</button>
                            </div>
                            <label class="grid gap-1 text-xs font-black text-slate-600">
                                Judul
                                <input id="aaGuestbookTitleInput" class="aa-field" type="text" maxlength="80">
                            </label>
                            <label class="mt-2 grid gap-1 text-xs font-black text-slate-600">
                                Subtitle
                                <textarea id="aaGuestbookSubtitleInput" class="aa-field min-h-20 py-2" rows="3"
                                    maxlength="180"></textarea>
                            </label>
                            <label class="mt-2 grid gap-1 text-xs font-black text-slate-600">
                                Tombol
                                <input id="aaGuestbookButtonInput" class="aa-field" type="text" maxlength="40">
                            </label>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Background
                                    <input id="aaGuestbookBgInput" class="aa-field" type="color">
                                </label>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Card
                                    <input id="aaGuestbookCardInput" class="aa-field" type="color">
                                </label>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Text
                                    <input id="aaGuestbookTextInput" class="aa-field" type="color">
                                </label>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Accent
                                    <input id="aaGuestbookAccentInput" class="aa-field" type="color">
                                </label>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Radius
                                    <input id="aaGuestbookRadiusInput" class="aa-field" type="number" min="0" max="40"
                                        step="1">
                                </label>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Max Height
                                    <input id="aaGuestbookMaxHeightInput" class="aa-field" type="number" min="180"
                                        max="720" step="10">
                                </label>
                            </div>
                            <label class="mt-2 flex items-center gap-2 text-xs font-black text-slate-600">
                                <input id="aaGuestbookStickerInput" type="checkbox">
                                Tampilkan stiker GIF
                            </label>
                            <label class="mt-2 flex items-center gap-2 text-xs font-black text-slate-600">
                                <input id="aaGuestbookAttendanceInput" type="checkbox">
                                Tampilkan pilihan kehadiran
                            </label>
                            <p class="mt-2 text-[11px] font-bold leading-relaxed text-slate-500">Guestbook ini adalah
                                section HTML public, bukan object bebas di canvas. Data komentar tetap memakai sistem
                                guestbook yang sudah berjalan.</p>
                        </div>

                    </div>
                </section>

                <?= view('editor/partials/right_ads', ['editorAds' => $editorAds ?? []]) ?>

            </aside>
        </main>
    </div>

    <?= view('editor/partials/modals', get_defined_vars()) ?>

    <script>
    (function() {
        'use strict';

        if (window.FabricWebsiteEditor && window.FabricWebsiteEditor.initialized) {
            return;
        }

        <?= view('editor/partials/scripts/state', get_defined_vars()) ?>

        function defaultGuestbookConfig() {
            return {
                enabled: false,
                eyebrow: 'Guestbook',
                title: 'Ucapan dan Doa',
                subtitle: 'Tinggalkan ucapan dan konfirmasi kehadiran kamu untuk acara ini.',
                buttonText: 'Kirim ucapan',
                backgroundColor: '#f8fafc',
                cardColor: '#ffffff',
                textColor: '#101828',
                mutedColor: '#667085',
                accentColor: '#0f766e',
                borderRadius: 22,
                maxHeight: 380,
                showSticker: true,
                showAttendance: true,
            };
        }

        function normalizeGuestbookConfig(value) {
            const defaults = defaultGuestbookConfig();
            const source = value && typeof value === 'object' ? value : {};
            const color = (candidate, fallback) => /^#[0-9a-f]{6}$/i.test(String(candidate || '')) ? String(
                candidate) : fallback;
            return {
                ...defaults,
                enabled: source.enabled === true,
                eyebrow: String(source.eyebrow || defaults.eyebrow).slice(0, 40),
                title: String(source.title || defaults.title).slice(0, 80),
                subtitle: String(source.subtitle || defaults.subtitle).slice(0, 180),
                buttonText: String(source.buttonText || defaults.buttonText).slice(0, 40),
                backgroundColor: color(source.backgroundColor, defaults.backgroundColor),
                cardColor: color(source.cardColor, defaults.cardColor),
                textColor: color(source.textColor, defaults.textColor),
                mutedColor: color(source.mutedColor, defaults.mutedColor),
                accentColor: color(source.accentColor, defaults.accentColor),
                borderRadius: Math.max(0, Math.min(40, Number(source.borderRadius) || defaults.borderRadius)),
                maxHeight: Math.max(180, Math.min(720, Number(source.maxHeight) || defaults.maxHeight)),
                showSticker: source.showSticker !== false,
                showAttendance: source.showAttendance !== false,
            };
        }

        function syncGuestbookPanel() {
            const guestbook = normalizeGuestbookConfig(state.guestbook);
            state.guestbook = guestbook;
            els.aaGuestbookPanel?.classList.toggle('hidden', !guestbook.enabled);
            if (els.aaGuestbookTitleInput) els.aaGuestbookTitleInput.value = guestbook.title;
            if (els.aaGuestbookSubtitleInput) els.aaGuestbookSubtitleInput.value = guestbook.subtitle;
            if (els.aaGuestbookButtonInput) els.aaGuestbookButtonInput.value = guestbook.buttonText;
            if (els.aaGuestbookBgInput) els.aaGuestbookBgInput.value = guestbook.backgroundColor;
            if (els.aaGuestbookCardInput) els.aaGuestbookCardInput.value = guestbook.cardColor;
            if (els.aaGuestbookTextInput) els.aaGuestbookTextInput.value = guestbook.textColor;
            if (els.aaGuestbookAccentInput) els.aaGuestbookAccentInput.value = guestbook.accentColor;
            if (els.aaGuestbookRadiusInput) els.aaGuestbookRadiusInput.value = guestbook.borderRadius;
            if (els.aaGuestbookMaxHeightInput) els.aaGuestbookMaxHeightInput.value = guestbook.maxHeight;
            if (els.aaGuestbookStickerInput) els.aaGuestbookStickerInput.checked = guestbook.showSticker;
            if (els.aaGuestbookAttendanceInput) els.aaGuestbookAttendanceInput.checked = guestbook.showAttendance;
            if (els.aaGuestbookEditorPreview) {
                els.aaGuestbookEditorPreview.classList.toggle('is-hidden', !guestbook.enabled);
                els.aaGuestbookEditorPreview.innerHTML = guestbook.enabled ? guestbookPreviewHtml()
                    .replace('id="guestbook"', 'id="aaEditorGuestbook"') : '';
            }
        }

        function setGuestbookField(key, value) {
            state.guestbook = normalizeGuestbookConfig({
                ...state.guestbook,
                enabled: true,
                [key]: value,
            });
            snapshot();
        }

        function enableGuestbookWidget() {
            state.guestbook = normalizeGuestbookConfig({
                ...state.guestbook,
                enabled: true,
            });
            syncGuestbookPanel();
            snapshot();
            setStatus('Guestbook widget ditambahkan');
        }

        function setStatus(message, tone) {
            if (!els.aaSaveState) return;
            els.aaSaveState.textContent = message;
            els.aaSaveState.style.color = tone === 'error' ? '#be123c' : tone === 'saving' ? '#0068d8' : '#0f766e';
            els.aaSaveState.style.background = tone === 'error' ? '#fff1f2' : tone === 'saving' ? '#e8f2ff' :
                '#ecfdf5';
            els.aaSaveState.style.border = tone === 'error' ? '1px solid #fecdd3' : tone === 'saving' ?
                '1px solid #bfdbfe' : '1px solid #bbf7d0';
            els.aaSaveState.style.borderRadius = '999px';
            els.aaSaveState.style.padding = els.aaSaveState.closest('.aa-topbar') ? '6px 10px' : '7px 12px';
            els.aaSaveState.classList.remove('is-status-pulse');
            void els.aaSaveState.offsetWidth;
            els.aaSaveState.classList.add('is-status-pulse');
        }

        function showEditorToast(message, tone = 'success', title = '') {
            const toast = els.aaEditorToast;
            if (!toast) return;
            const isError = tone === 'error';
            const isSaving = tone === 'saving';
            toast.classList.toggle('is-error', isError);
            toast.classList.toggle('is-saving', isSaving);
            if (els.aaEditorToastIcon) {
                els.aaEditorToastIcon.innerHTML = isError ? '<i class="fa fa-triangle-exclamation"></i>' :
                    isSaving ? '<i class="fa fa-circle-notch fa-spin"></i>' : '<i class="fa fa-check"></i>';
            }
            if (els.aaEditorToastTitle) {
                els.aaEditorToastTitle.textContent = title || (isError ? 'Terjadi masalah' : isSaving ?
                    'Memproses' :
                    'Berhasil');
            }
            if (els.aaEditorToastMessage) {
                els.aaEditorToastMessage.textContent = message;
            }
            toast.classList.add('is-visible');
            clearTimeout(state.toastTimer);
            if (!isSaving) {
                state.toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 3200);
            }
        }

        function hideEditorToast() {
            els.aaEditorToast?.classList.remove('is-visible');
        }

        function setButtonLoading(button, loading, loadingText = 'Loading...') {
            if (!button) return;
            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }
            button.disabled = loading;
            button.classList.toggle('is-loading', loading);
            button.innerHTML = loading ? `<span class="aa-loading-dot" aria-hidden="true"></span>${loadingText}` :
                button.dataset.originalHtml;
        }

        function setMediaUploadState(message = '', tone = 'loading') {
            if (!els.aaMediaUploadState) return;
            const label = els.aaMediaUploadState.querySelector('span');
            if (label) label.textContent = message;
            els.aaMediaUploadState.classList.toggle('is-visible', message !== '');
            els.aaMediaUploadState.classList.toggle('is-error', tone === 'error');
        }

        function showCanvasLoading(message = 'Memuat desain...') {
            if (state.__aaHistoryRestoring) return;

            if (!els.aaCanvasLoading) return;

            const label = els.aaCanvasLoading.querySelector('[data-aa-canvas-loading-label]') || els.aaCanvasLoading.querySelector('span');

            if (label) {
                label.innerHTML = '<i class="fa fa-circle-notch"></i> ' + escapeHtml(message);
            }

            els.aaCanvasLoading.classList.add('is-visible');
        }

        function hideCanvasLoading() {
            els.aaCanvasLoading?.classList.remove('is-visible');
        }

        function normalizeSlug(value) {
            return String(value || '')
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9-]/g, '')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }

        function syncZoomViewport() {
            if (!els.aaStage || !els.aaStageViewport) return;
            const stageWidth = Math.max(1, els.aaStage.scrollWidth || els.aaStage.offsetWidth || 1);
            const stageHeight = Math.max(1, els.aaStage.scrollHeight || els.aaStage.offsetHeight || 1);
            els.aaStageViewport.style.width = `${Math.ceil(stageWidth * state.zoom)}px`;
            els.aaStageViewport.style.height = `${Math.ceil(stageHeight * state.zoom)}px`;
        }

        function updateZoom() {
            els.aaStage.style.transform = `scale(${state.zoom})`;
            els.aaStage.style.setProperty('--aa-page-control-scale', String(Math.min(1.65, Math.max(1, 0.82 / Math
                .max(0.01, state.zoom)))));
            syncZoomViewport();
            els.aaZoomLabel.textContent = `${Math.round(state.zoom * 100)}%`;
            requestAnimationFrame(syncObjectFloatingToolbar);
            requestAnimationFrame(syncInteractionPopover);
            requestAnimationFrame(syncCropUi);
        }

        function fitZoom() {
            const wrapWidth = Math.max(320, els.aaStageWrap.clientWidth - 90);
            const wrapHeight = Math.max(320, els.aaStageWrap.clientHeight - 90);
            const scaleX = wrapWidth / state.canvas.getWidth();
            const scaleY = wrapHeight / state.canvas.getHeight();
            state.zoom = Math.min(0.8, Math.max(0.18, Math.min(scaleX, scaleY)));
            updateZoom();
        }

        function normalizeTextBaseline(value) {
            return value === 'alphabetical' ? 'alphabetic' : value;
        }

        function sanitizeFabricObject(object) {
            if (!object || typeof object !== 'object') return object;

            if (['i-text', 'textbox', 'text'].includes(object.type)) {
                delete object.clipPath;
            }
            if (object.clipPath && typeof object.clipPath.toObject === 'function') {
                object.clipPath = object.clipPath.toObject(serializedObjectProps);
            }

            Object.keys(object).forEach(key => {
                if (['canvas', 'group', '_objects', 'parent'].includes(key)) {
                    return;
                }
                if (key === 'textBaseline') {
                    object[key] = normalizeTextBaseline(object[key]);
                    return;
                }

                if (object[key] && typeof object[key] === 'object') {
                    sanitizeFabricObject(object[key]);
                }
            });

            if (Array.isArray(object)) {
                object.forEach(sanitizeFabricObject);
            }

            aaRepairLegacyGuestNameJsonObject(object);

            return object;
        }

        function sanitizeFabricPageData(pageData) {
            if (!pageData || typeof pageData !== 'object') return pageData;

            if (Array.isArray(pageData.objects)) {
                pageData.objects = pageData.objects.filter(object => object?.customType !== 'crop-helper' &&
                    object?.excludeFromExport !== true);
                pageData.objects.forEach(sanitizeFabricObject);
            }

            return pageData;
        }

        const AA_BROKEN_IMAGE_PLACEHOLDER_SRC =
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
        const aaLocalMediaAvailabilityCache = new Map();

        function aaNormalizeMaybeUrl(src) {
            if (!src || typeof src !== 'string') return null;
            const trimmed = src.trim();
            if (!trimmed || trimmed.startsWith('data:') || trimmed.startsWith('blob:')) return null;

            try {
                return new URL(trimmed, window.location.origin);
            } catch (error) {
                return null;
            }
        }

        function aaShouldPreflightLocalMedia(src) {
            const url = aaNormalizeMaybeUrl(src);
            if (!url) return false;
            if (url.origin !== window.location.origin) return false;
            return url.pathname.startsWith('/uploads/media/');
        }

        async function aaLocalMediaExists(src) {
            const url = aaNormalizeMaybeUrl(src);
            if (!url) return true;

            const cacheKey = url.href;
            if (aaLocalMediaAvailabilityCache.has(cacheKey)) {
                return aaLocalMediaAvailabilityCache.get(cacheKey);
            }

            try {
                let response = await fetch(url.href, {
                    method: 'HEAD',
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (response.status === 405) {
                    response = await fetch(url.href, {
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: {
                            Range: 'bytes=0-0',
                        },
                    });
                }

                const exists = response.ok || response.status === 206;
                aaLocalMediaAvailabilityCache.set(cacheKey, exists);
                return exists;
            } catch (error) {
                // Kalau jaringan/preflight bermasalah, jangan blokir render. Biarkan Fabric mencoba seperti biasa.
                aaLocalMediaAvailabilityCache.set(cacheKey, true);
                return true;
            }
        }

        function aaWalkFabricJsonNodes(value, callback, seen = new WeakSet()) {
            if (!value || typeof value !== 'object') return;
            if (seen.has(value)) return;
            seen.add(value);

            if (Array.isArray(value)) {
                value.forEach(item => aaWalkFabricJsonNodes(item, callback, seen));
                return;
            }

            callback(value);

            ['objects', '_objects'].forEach(key => {
                if (Array.isArray(value[key])) {
                    value[key].forEach(item => aaWalkFabricJsonNodes(item, callback, seen));
                }
            });

            if (value.clipPath && typeof value.clipPath === 'object') {
                aaWalkFabricJsonNodes(value.clipPath, callback, seen);
            }
            if (value.backgroundImage && typeof value.backgroundImage === 'object') {
                aaWalkFabricJsonNodes(value.backgroundImage, callback, seen);
            }
        }

        async function prepareFabricPageDataImages(pageData) {
            if (!pageData || typeof pageData !== 'object') return pageData;

            const imageNodes = [];
            aaWalkFabricJsonNodes(pageData, object => {
                if (object?.type === 'image' && aaShouldPreflightLocalMedia(object.src)) {
                    imageNodes.push(object);
                }
            });

            if (!imageNodes.length) return pageData;

            let replacedCount = 0;
            await Promise.all(imageNodes.map(async object => {
                const exists = await aaLocalMediaExists(object.src);
                if (exists) return;

                object.aaBrokenImageSrc = object.src;
                object.src = AA_BROKEN_IMAGE_PLACEHOLDER_SRC;
                object.crossOrigin = null;
                replacedCount += 1;
            }));

            if (replacedCount > 0) {
                console.warn('[AdaAcara] Media lokal hilang diganti placeholder sebelum render:', replacedCount);
            }

            return pageData;
        }

        window.prepareFabricPageDataImages = prepareFabricPageDataImages;

        <?= view('editor/partials/scripts/opening', get_defined_vars()) ?>

        function isFabricTextObject(object) {
            return object && ['i-text', 'textbox', 'text'].includes(object.type);
        }

        function aaIsTextboxObject(object) {
            return object && object.type === 'textbox';
        }

        function aaIsLegacyTextObject(object) {
            return object && ['i-text', 'text'].includes(object.type);
        }

        function aaShouldUpgradeTextToTextbox(object) {
            if (!object || !window.fabric) return false;
            if (!aaIsLegacyTextObject(object)) return false;
            if (object.group) return false;
            if (object === state.cropBox) return false;
            if (object.excludeFromExport === true) return false;
            return true;
        }

        function aaIsTextSideCorner(corner) {
            return ['ml', 'mr', 'mt', 'mb'].includes(String(corner || ''));
        }

        function aaIsTextHorizontalSideCorner(corner) {
            return ['ml', 'mr'].includes(String(corner || ''));
        }

        function aaIsTextVerticalSideCorner(corner) {
            return ['mt', 'mb'].includes(String(corner || ''));
        }

        function aaIsTextCornerScale(corner) {
            return ['tl', 'tr', 'bl', 'br'].includes(String(corner || ''));
        }

        function aaGetFabricTransformCorner(event) {
            return String(
                event?.transform?.corner ||
                event?.target?.__corner ||
                state.canvas?._currentTransform?.corner ||
                state.__aaTextResizeCorner ||
                ''
            );
        }

        function aaClampTextboxWidth(width) {
            const canvasWidth = Math.max(1, state.canvas?.getWidth?.() || 1080);
            width = Number(width);

            if (!Number.isFinite(width)) {
                width = 120;
            }

            return Math.max(32, Math.min(canvasWidth * 3, width));
        }

        function aaRefreshTextboxDimensions(object) {
            if (!object) return;

            object.dirty = true;

            if (typeof object.initDimensions === 'function') {
                object.initDimensions();
            }

            if (typeof object.setCoords === 'function') {
                object.setCoords();
            }
        }

        function aaIsCountdownObject(object) {
            return Boolean(object && object.customType === 'countdown-timer');
        }

        function aaApplyCountdownResizeControls(object) {
            if (!aaIsCountdownObject(object)) return object;

            object.set({
                lockScalingFlip: true,
                centeredScaling: false,
                centeredRotation: false
            });

            if (typeof object.setControlsVisibility === 'function') {
                object.setControlsVisibility({
                    ml: true,
                    mr: true,
                    mt: false,
                    mb: false,
                    tl: true,
                    tr: true,
                    bl: true,
                    br: true,
                    mtr: true
                });
            }

            return object;
        }

        function aaClampCountdownWidth(width, object) {
            const canvasWidth = Math.max(1, state.canvas?.getWidth?.() || 1080);
            const gap = Math.max(0, Number(object?.countdownGap) || 0);
            const fontSize = Math.max(8, Number(object?.countdownFontSize) || 36);
            const minCardWidth = Math.max(64, fontSize * 1.8);
            const minWidth = Math.ceil(Math.max(80, minCardWidth));
            const maxWidth = Math.max(minWidth, Math.ceil(canvasWidth * 1.2));

            width = Number(width);

            if (!Number.isFinite(width)) {
                width = Math.max(minWidth, Number(object?.width) || 620);
            }

            return Math.max(minWidth, Math.min(maxWidth, width));
        }

        function aaGetCountdownLayout(options = {}) {
            const width = Math.max(80, Number(options.width) || 620);
            const gap = Math.max(0, Number(options.countdownGap) || 0);
            const fontSize = Math.max(8, Number(options.countdownFontSize || options.fontSize) || 36);
            const minCardWidth = Math.max(64, fontSize * 1.8);
            const fourColumnWidth = (minCardWidth * 4) + (gap * 3);
            const twoColumnWidth = (minCardWidth * 2) + gap;
            const requestedColumns = Number(options.columns);
            let columns = [1, 2, 4].includes(requestedColumns) ? requestedColumns : 4;

            if (![1, 2, 4].includes(requestedColumns)) {
                if (width < twoColumnWidth * 1.05) {
                    columns = 1;
                } else if (width < fourColumnWidth * 0.96) {
                    columns = 2;
                }
            }

            const rows = Math.ceil(4 / columns);
            const rawHeight = Math.max(40, Number(options.height) || 130);
            const storedCardHeight = Number(options.countdownCardHeight || options.__aaCountdownCardHeight);
            let cardHeight = Number.isFinite(storedCardHeight) && storedCardHeight > 0 ? storedCardHeight : 0;

            if (!cardHeight) {
                cardHeight = rows > 1 && rawHeight > 180
                    ? (rawHeight - gap * (rows - 1)) / rows
                    : rawHeight;
            }

            cardHeight = Math.max(72, Math.min(Math.max(130, fontSize * 4), cardHeight));
            const cardWidth = Math.max(1, (width - gap * (columns - 1)) / columns);
            const totalHeight = Math.max(cardHeight, (cardHeight * rows) + (gap * (rows - 1)));
            const items = Array.from({ length: 4 }, (_, index) => {
                const column = index % columns;
                const row = Math.floor(index / columns);
                const left = -width / 2 + column * (cardWidth + gap);
                const top = -totalHeight / 2 + row * (cardHeight + gap);

                return {
                    left,
                    top,
                    width: cardWidth,
                    height: cardHeight,
                    centerX: left + cardWidth / 2,
                    centerY: top + cardHeight / 2,
                };
            });

            return {
                width,
                gap,
                columns,
                rows,
                cardWidth,
                cardHeight,
                totalHeight,
                items,
            };
        }

        function aaResizeCountdownWidthFromSide(object, corner, options = {}) {
            if (!aaIsCountdownObject(object) || !aaIsTextHorizontalSideCorner(corner)) {
                return false;
            }

            const scaleX = Math.abs(Number(object.scaleX) || 1);
            const currentWidth = aaClampCountdownWidth(Number(object.width) || 620, object);
            const nextWidth = aaClampCountdownWidth(currentWidth * scaleX, object);
            const anchorOriginX = corner === 'ml' ? 'right' : 'left';
            const anchorPoint = typeof object.getPointByOrigin === 'function'
                ? object.getPointByOrigin(anchorOriginX, 'center')
                : null;

            object.set({
                width: nextWidth,
                scaleX: 1
            });

            if (anchorPoint && typeof object.setPositionByOrigin === 'function') {
                object.setPositionByOrigin(anchorPoint, anchorOriginX, 'center');
            }

            if (options.refresh === false) {
                state.canvas?.requestRenderAll?.();
            } else {
                refreshCountdownPreviewObject(object, false);
            }
            aaApplyCountdownResizeControls(object);

            object.dirty = true;
            object.setCoords?.();

            return true;
        }

        function aaHandleCountdownSideResizeLive(event) {
            const object = event?.target;

            if (!aaIsCountdownObject(object)) {
                return false;
            }

            const corner = aaGetFabricTransformCorner(event);

            if (!aaIsTextHorizontalSideCorner(corner)) {
                return false;
            }

            aaBeginSelectionTransformUi();
            return aaResizeCountdownWidthFromSide(object, corner, {
                refresh: false
            });
        }

        function aaFinalizeCountdownResize(eventOrObject, forcedCorner = '') {
            const object = eventOrObject?.target || eventOrObject;

            if (!aaIsCountdownObject(object)) {
                return false;
            }

            const corner = forcedCorner || aaGetFabricTransformCorner(eventOrObject);
            let changed = false;

            if (aaIsTextHorizontalSideCorner(corner)) {
                changed = aaResizeCountdownWidthFromSide(object, corner);
            } else {
                aaApplyCountdownResizeControls(object);
            }

            object.set({
                scaleX: aaIsTextHorizontalSideCorner(corner) ? 1 : object.scaleX,
                scaleY: object.scaleY
            });

            if (!changed) {
                refreshCountdownPreviewObject(object, false);
            }

            if (state.canvas) {
                state.canvas.setActiveObject(object);
                state.canvas.requestRenderAll?.();
            }

            return changed;
        }

        function aaApplyTextboxResizeControls(object) {
            if (!aaIsTextboxObject(object)) return object;

            object.set({
                lockScalingFlip: true,
                centeredScaling: false,
                centeredRotation: false
            });

            // Textbox:
            // kiri/kanan = ubah width/wrap
            // pojok = scale font
            // atas/bawah disembunyikan supaya tidak gepeng seperti gambar
            if (typeof object.setControlsVisibility === 'function') {
                object.setControlsVisibility({
                    ml: true,
                    mr: true,
                    mt: false,
                    mb: false,
                    tl: true,
                    tr: true,
                    bl: true,
                    br: true,
                    mtr: true
                });
            }

            return object;
        }

        function aaConvertTextObjectToTextbox(object, options = {}) {
            if (!aaShouldUpgradeTextToTextbox(object)) {
                if (aaIsTextboxObject(object)) {
                    aaApplyTextboxResizeControls(object);
                }
                return object;
            }

            const canvas = object.canvas || state.canvas;

            if (!canvas || !window.fabric) {
                return object;
            }

            const keepActive = options.keepActive !== false;
            const objectIndex = canvas.getObjects().indexOf(object);
            const active = canvas.getActiveObject();

            const data = object.toObject(serializedObjectProps);
            const textValue = String(object.text ?? data.text ?? 'Tulis teks di sini');

            delete data.type;
            delete data.version;
            delete data.objects;

            // Width IText biasanya hanya selebar teks. Ini cukup aman agar tampilan awal tidak berubah jauh.
            // Nanti saat handle kanan/kiri ditarik, width ini yang dipakai untuk wrap.
            data.width = aaClampTextboxWidth(
                Math.max(
                    80,
                    Number(object.width) || 0,
                    Math.abs((Number(object.width) || 0) * (Number(object.scaleX) || 1))
                )
            );

            data.left = Number(object.left) || 0;
            data.top = Number(object.top) || 0;
            data.scaleX = Number(object.scaleX) || 1;
            data.scaleY = Number(object.scaleY) || 1;
            data.angle = Number(object.angle) || 0;
            data.originX = object.originX || 'center';
            data.originY = object.originY || 'center';

            const textbox = new fabric.Textbox(textValue, data);

            aaApplyTextboxResizeControls(textbox);
            textbox.dirty = true;
            textbox.setCoords();

            const previousRestoring = state.isRestoring;
            state.isRestoring = true;

            try {
                canvas.remove(object);

                if (objectIndex >= 0 && typeof canvas.insertAt === 'function') {
                    canvas.insertAt(textbox, objectIndex);
                } else {
                    canvas.add(textbox);
                }
            } finally {
                state.isRestoring = previousRestoring;
            }

            if (keepActive || active === object) {
                canvas.setActiveObject(textbox);
            }

            canvas.requestRenderAll();

            return textbox;
        }

        function aaUpgradeCanvasTextObjectsToTextbox(canvas) {
            if (!canvas || typeof canvas.getObjects !== 'function') {
                return false;
            }

            let changed = false;

            canvas.getObjects().slice().forEach(function(object) {
                if (aaShouldUpgradeTextToTextbox(object)) {
                    aaConvertTextObjectToTextbox(object, {
                        keepActive: false
                    });
                    changed = true;
                } else if (aaIsTextboxObject(object)) {
                    aaApplyTextboxResizeControls(object);
                }
            });

            return changed;
        }

        function aaNormalizeActiveTextSelection() {
            if (!state.canvas) return null;

            const active = state.canvas.getActiveObject();

            if (!active || active.type === 'activeSelection') {
                return active;
            }

            if (aaShouldUpgradeTextToTextbox(active)) {
                return aaConvertTextObjectToTextbox(active, {
                    keepActive: true
                });
            }

            if (aaIsTextboxObject(active)) {
                aaApplyTextboxResizeControls(active);
            }

            return active;
        }

        function aaNormalizeActiveCountdownSelection() {
            if (!state.canvas) return null;

            const active = state.canvas.getActiveObject();

            if (aaIsCountdownObject(active)) {
                aaApplyCountdownResizeControls(active);
            }

            return active;
        }

        function aaRememberTextResizeCorner(event) {
            const target = event?.target;

            if (!target || !isFabricTextObject(target)) {
                return;
            }

            const corner = String(
                event?.target?.__corner ||
                state.canvas?._currentTransform?.corner ||
                ''
            );

            if (corner) {
                state.__aaTextResizeCorner = corner;
                if (aaIsTextHorizontalSideCorner(corner) || aaIsTextCornerScale(corner)) {
                    aaBeginSelectionTransformUi();
                }
            }
        }

        function aaResizeTextboxWidthFromSide(object, corner) {
            if (!aaIsTextboxObject(object) || object.isEditing) {
                return false;
            }

            if (!aaIsTextHorizontalSideCorner(corner)) {
                return false;
            }

            const scaleX = Math.abs(Number(object.scaleX) || 1);
            const currentWidth = Math.max(1, Number(object.width) || 1);
            const nextWidth = aaClampTextboxWidth(currentWidth * scaleX);

            // Saat tarik kiri, kanan harus diam.
            // Saat tarik kanan, kiri harus diam.
            const anchorOriginX = corner === 'ml' ? 'right' : 'left';
            const anchorPoint = object.getPointByOrigin(anchorOriginX, 'top');

            object.set({
                width: nextWidth,
                scaleX: 1
            });

            aaRefreshTextboxDimensions(object);

            if (typeof object.setPositionByOrigin === 'function') {
                object.setPositionByOrigin(anchorPoint, anchorOriginX, 'top');
            }

            aaRefreshTextboxDimensions(object);

            return true;
        }

        function aaCancelTextboxVerticalSideScale(object, corner) {
            if (!aaIsTextboxObject(object) || object.isEditing) {
                return false;
            }

            if (!aaIsTextVerticalSideCorner(corner)) {
                return false;
            }

            const anchorOriginY = corner === 'mt' ? 'bottom' : 'top';
            const anchorPoint = object.getPointByOrigin('center', anchorOriginY);

            object.set({
                scaleY: 1
            });

            aaRefreshTextboxDimensions(object);

            if (typeof object.setPositionByOrigin === 'function') {
                object.setPositionByOrigin(anchorPoint, 'center', anchorOriginY);
            }

            aaRefreshTextboxDimensions(object);

            return true;
        }

        function aaHandleTextboxSideResizeLive(event) {
            let object = event?.target;

            if (!object || object.isEditing) {
                return false;
            }

            if (aaShouldUpgradeTextToTextbox(object)) {
                object = aaConvertTextObjectToTextbox(object, {
                    keepActive: true
                });
            }

            if (!aaIsTextboxObject(object)) {
                return false;
            }

            const corner = aaGetFabricTransformCorner(event);
            state.__aaTextResizeCorner = corner;

            if (aaIsTextHorizontalSideCorner(corner)) {
                return aaResizeTextboxWidthFromSide(object, corner);
            }

            if (aaIsTextVerticalSideCorner(corner)) {
                return aaCancelTextboxVerticalSideScale(object, corner);
            }

            return false;
        }

        function aaBakeTextboxCornerScale(object, corner = '') {
            if (!object || object.isEditing) {
                return false;
            }

            if (aaShouldUpgradeTextToTextbox(object)) {
                object = aaConvertTextObjectToTextbox(object, {
                    keepActive: true
                });
            }

            if (!aaIsTextboxObject(object)) {
                return false;
            }

            if (corner && !aaIsTextCornerScale(corner)) {
                return false;
            }

            const scaleX = Math.abs(Number(object.scaleX) || 1);
            const scaleY = Math.abs(Number(object.scaleY) || 1);

            if (Math.abs(scaleX - 1) < 0.001 && Math.abs(scaleY - 1) < 0.001) {
                return false;
            }

            const centerPoint = object.getCenterPoint();

            const currentWidth = Math.max(1, Number(object.width) || 1);
            const currentFontSize = Math.max(1, Number(object.fontSize) || 32);

            // Pojok tetap membesarkan/mengecilkan teks.
            // Setelah dilepas, scale dibersihkan ke fontSize agar stabil.
            const fontScale = Math.max(0.05, Math.min(20, (scaleX + scaleY) / 2));

            const nextWidth = aaClampTextboxWidth(currentWidth * scaleX);
            const nextFontSize = Math.max(4, Math.min(520, currentFontSize * fontScale));

            object.set({
                width: nextWidth,
                fontSize: nextFontSize,
                scaleX: 1,
                scaleY: 1
            });

            aaApplyTextboxResizeControls(object);
            aaRefreshTextboxDimensions(object);

            if (typeof object.setPositionByOrigin === 'function') {
                object.setPositionByOrigin(centerPoint, 'center', 'center');
            } else {
                object.set({
                    left: centerPoint.x,
                    top: centerPoint.y,
                    originX: 'center',
                    originY: 'center'
                });
            }

            aaRefreshTextboxDimensions(object);

            return true;
        }

function aaHardReleaseFabricTransform() {
    if (!state.canvas) return;

    if (typeof aaEndSelectionTransformUi === 'function') {
        aaEndSelectionTransformUi();
    }

    state.canvas._currentTransform = null;
    state.canvas._groupSelector = null;

    if (state.canvas.upperCanvasEl) {
        state.canvas.upperCanvasEl.style.cursor = '';
    }

    document.body.style.userSelect = '';
    document.body.style.cursor = '';
}

function aaFinalizeTextboxResize(eventOrObject, forcedCorner = '') {
    let object = eventOrObject?.target || eventOrObject;

    if (!object || object.isEditing) {
        state.__aaTextResizeCorner = '';
        aaHardReleaseFabricTransform();
        return false;
    }

    if (aaShouldUpgradeTextToTextbox(object)) {
        object = aaConvertTextObjectToTextbox(object, {
            keepActive: true
        });
    }

    if (!aaIsTextboxObject(object)) {
        state.__aaTextResizeCorner = '';
        aaHardReleaseFabricTransform();
        return false;
    }

    const corner = forcedCorner || aaGetFabricTransformCorner(eventOrObject);

    let changed = false;

    if (aaIsTextHorizontalSideCorner(corner)) {
        changed = aaResizeTextboxWidthFromSide(object, corner);
    } else if (aaIsTextVerticalSideCorner(corner)) {
        changed = aaCancelTextboxVerticalSideScale(object, corner);
    } else if (aaIsTextCornerScale(corner)) {
        changed = aaBakeTextboxCornerScale(object, corner);
    } else {
        aaApplyTextboxResizeControls(object);
    }

    state.__aaTextResizeCorner = '';

    object.set({
        scaleX: 1,
        scaleY: 1
    });

    aaApplyTextboxResizeControls(object);
    aaRefreshTextboxDimensions(object);

    if (state.canvas) {
        state.canvas.setActiveObject(object);

        if (typeof state.canvas.requestRenderAll === 'function') {
            state.canvas.requestRenderAll();
        } else {
            state.canvas.renderAll();
        }
    }

    aaHardReleaseFabricTransform();

    return changed;
}

        function walkFabricObjects(objects, callback) {
            (objects || []).forEach(object => {
                callback(object);
                if (object && typeof object.getObjects === 'function') {
                    walkFabricObjects(object.getObjects(), callback);
                }
            });
        }

        function clearTextClipPaths(canvas) {
            if (!canvas || !canvas.getObjects) return false;
            let changed = false;

            walkFabricObjects(canvas.getObjects(), object => {
                if (!isFabricTextObject(object) || !object.clipPath) return;
                object.clipPath = null;
                object.dirty = true;
                object.setCoords();
                changed = true;
            });

            return changed;
        }

        function normalizeFontFamily(fontFamily) {
            return String(fontFamily || 'Inter').replace(/^["']|["']$/g, '').trim() || 'Inter';
        }

        const bunnyFontRegistry = {
            'Roboto': 'https://fonts.bunny.net/css?family=roboto:400,500,700&display=swap',
            'Open Sans': 'https://fonts.bunny.net/css?family=open-sans:400,500,700&display=swap',
            'Lato': 'https://fonts.bunny.net/css?family=lato:400,700&display=swap',
            'Oswald': 'https://fonts.bunny.net/css?family=oswald:400,500,700&display=swap',
            'Raleway': 'https://fonts.bunny.net/css?family=raleway:400,500,700&display=swap',
            'Nunito': 'https://fonts.bunny.net/css?family=nunito:400,600,700&display=swap',
            'Merriweather': 'https://fonts.bunny.net/css?family=merriweather:400,700&display=swap',
            'Work Sans': 'https://fonts.bunny.net/css?family=work-sans:400,500,700&display=swap',
            'Rubik': 'https://fonts.bunny.net/css?family=rubik:400,500,700&display=swap',
            'Manrope': 'https://fonts.bunny.net/css?family=manrope:400,500,700&display=swap',
            'Plus Jakarta Sans': 'https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,700&display=swap',
            'Urbanist': 'https://fonts.bunny.net/css?family=urbanist:400,500,700&display=swap',
            'Josefin Sans': 'https://fonts.bunny.net/css?family=josefin-sans:400,500,700&display=swap',
            'Quicksand': 'https://fonts.bunny.net/css?family=quicksand:400,500,700&display=swap',
            'Noto Sans': 'https://fonts.bunny.net/css?family=noto-sans:400,500,700&display=swap',
            'Noto Serif': 'https://fonts.bunny.net/css?family=noto-serif:400,700&display=swap',
            'Mulish': 'https://fonts.bunny.net/css?family=mulish:400,500,700&display=swap',
            'Karla': 'https://fonts.bunny.net/css?family=karla:400,700&display=swap',
            'Oxygen': 'https://fonts.bunny.net/css?family=oxygen:400,700&display=swap',
            'Archivo': 'https://fonts.bunny.net/css?family=archivo:400,500,700&display=swap',
            'Figtree': 'https://fonts.bunny.net/css?family=figtree:400,500,700&display=swap',
            'Space Grotesk': 'https://fonts.bunny.net/css?family=space-grotesk:400,500,700&display=swap',
            'Sacramento': 'https://fonts.bunny.net/css?family=sacramento:400&display=swap',
            'Pacifico': 'https://fonts.bunny.net/css?family=pacifico:400&display=swap',
            'Caveat': 'https://fonts.bunny.net/css?family=caveat:400,700&display=swap',
            'Courgette': 'https://fonts.bunny.net/css?family=courgette:400&display=swap',
            'Satisfy': 'https://fonts.bunny.net/css?family=satisfy:400&display=swap',
            'Cookie': 'https://fonts.bunny.net/css?family=cookie:400&display=swap',
        };

        const googleFontWeights = {
            'Aboreto': '400',
            'Abril Fatface': '400',
            'Adamina': '400',
            'Alex Brush': '400',
            'Alfa Slab One': '400',
            'Allura': '400',
            'Amarante': '400',
            'Amiri': '400;700',
            'Anton': '400',
            'Archivo Black': '400',
            'Arizonia': '400',
            'Assistant': '200;300;400;500;600;700;800',
            'Barlow': '100;200;300;400;500;600;700;800;900',
            'Bebas Neue': '400',
            'Bellefair': '400',
            'Bitter': '100;200;300;400;500;600;700;800;900',
            'Black Ops One': '400',
            'Bodoni Moda': '400;500;600;700;800;900',
            'Bonheur Royale': '400',
            'Cabin': '400;500;600;700',
            'Caudex': '400;700',
            'Changa One': '400',
            'Cinzel': '400;500;600;700;800;900',
            'Cormorant Garamond': '300;400;500;600;700',
            'Cormorant Infant': '300;400;500;600;700',
            'Cormorant Upright': '300;400;500;600;700',
            'Crimson Text': '400;600;700',
            'DM Sans': '100;200;300;400;500;600;700;800;900;1000',
            'DM Serif Display': '400',
            'Dancing Script': '400;500;600;700',
            'Dosis': '200;300;400;500;600;700;800',
            'EB Garamond': '400;500;600;700;800',
            'Elsie': '400;900',
            'Ephesis': '400',
            'Fira Sans': '100;200;300;400;500;600;700;800;900',
            'Fleur De Leah': '400',
            'Forum': '400',
            'Fraunces': '100;200;300;400;500;600;700;800;900',
            'Google Sans': '400;500;600;700',
            'Great Vibes': '400',
            'Heebo': '100;200;300;400;500;600;700;800;900',
            'IBM Plex Sans': '100;200;300;400;500;600;700',
            'Imperial Script': '400',
            'Inconsolata': '200;300;400;500;600;700;800;900',
            'Instrument Serif': '400',
            'Inter': '100;200;300;400;500;600;700;800;900',
            'Inter Tight': '100;200;300;400;500;600;700;800;900',
            'Italiana': '400',
            'Italianno': '400',
            'JetBrains Mono': '100;200;300;400;500;600;700;800',
            'Jost': '100;200;300;400;500;600;700;800;900',
            'Kanit': '100;200;300;400;500;600;700;800;900',
            'Lavishly Yours': '400',
            'Libre Baskerville': '400;500;600;700',
            'Libre Franklin': '100;200;300;400;500;600;700;800;900',
            'Lobster Two': '400;700',
            'Lora': '400;500;600;700',
            'Marcellus': '400',
            'Mea Culpa': '400',
            'Monsieur La Doulaise': '400',
            'Montserrat': '100;200;300;400;500;600;700;800;900',
            'Noto Naskh Arabic': '400;500;600;700',
            'Nunito Sans': '200;300;400;500;600;700;800;900;1000',
            'Outfit': '100;200;300;400;500;600;700;800;900',
            'PT Serif': '400;700',
            'Parisienne': '400',
            'Petit Formal Script': '400',
            'Philosopher': '400;700',
            'Playfair Display': '400;500;600;700;800;900',
            'Poiret One': '400',
            'Poppins': '100;200;300;400;500;600;700;800;900',
            'Prata': '400',
            'Prompt': '100;200;300;400;500;600;700;800;900',
            'Public Sans': '100;200;300;400;500;600;700;800;900',
            'Questrial': '400',
            'Quintessential': '400',
            'Red Hat Display': '300;400;500;600;700;800;900',
            'Roboto Mono': '100;200;300;400;500;600;700',
            'Roboto Slab': '100;200;300;400;500;600;700;800;900',
            'Sora': '100;200;300;400;500;600;700;800',
            'Source Code Pro': '200;300;400;500;600;700;800;900',
            'Source Sans 3': '200;300;400;500;600;700;800;900',
            'Sorts Mill Goudy': '400',
            'Tangerine': '400;700',
            'The Nautigal': '400;700',
            'Titillium Web': '200;300;400;600;700;900',
            'Ubuntu': '300;400;500;700',
            'Unna': '400;700',
            'Viaoda Libre': '400',
            'WindSong': '400;500',
            'Yeseva One': '400',
        };

        const customFontRegistry = <?= json_encode($customFonts ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const customFontWeights = {};
        (Array.isArray(customFontRegistry) ? customFontRegistry : []).forEach(font => {
            const family = normalizeFontFamily(font?.family || '');
            if (!family) return;
            const weights = Array.isArray(font?.weights) ? font.weights : ['400'];
            customFontWeights[family] = weights
                .map(weight => String(weight || '').replace(/[^0-9]/g, ''))
                .filter(Boolean)
                .join(';') || '400';
        });
        Object.assign(googleFontWeights, customFontWeights);

        function bunnyFontUrlForFamily(fontFamily) {
            return bunnyFontRegistry[normalizeFontFamily(fontFamily)] || '';
        }

        function isCustomFontFamily(fontFamily) {
            return !!customFontWeights[normalizeFontFamily(fontFamily)];
        }

        function googleFontUrlForFamily(fontFamily) {
            const family = normalizeFontFamily(fontFamily);
            if (customFontWeights[family]) return '';
            if (!googleFontWeights[family]) return '';
            const encodedFamily = encodeURIComponent(family).replace(/%20/g, '+');
            return 'https://fonts.googleapis.com/css2?family=' + encodedFamily + ':wght@' + googleFontWeights[family] +
                '&display=swap';
        }

        function ensureGoogleFontCss(fontFamily) {
            const url = googleFontUrlForFamily(fontFamily);
            if (!url) {
                return Promise.resolve();
            }
            if (state.googleFontLoadPromises[url]) {
                return state.googleFontLoadPromises[url];
            }

            const existing = Array.from(document.querySelectorAll('link[data-aa-google-font-css]'))
                .find(link => link.dataset.aaGoogleFontCss === url);
            if (existing) {
                state.googleFontLoadPromises[url] = document.fonts?.ready?.catch(() => null) || Promise.resolve();
                return state.googleFontLoadPromises[url];
            }

            state.googleFontLoadPromises[url] = new Promise(resolve => {
                const link = document.createElement('link');
                const done = () => {
                    if (document.fonts?.ready) {
                        document.fonts.ready.then(resolve).catch(resolve);
                        return;
                    }
                    resolve();
                };
                link.rel = 'stylesheet';
                link.href = url;
                link.dataset.aaGoogleFontCss = url;
                link.onload = done;
                link.onerror = done;
                document.head.appendChild(link);
            });

            return state.googleFontLoadPromises[url];
        }

        function ensureBunnyFontCss(fontFamily) {
            const url = bunnyFontUrlForFamily(fontFamily);
            if (!url) {
                return Promise.resolve();
            }
            if (state.bunnyFontLoadPromises[url]) {
                return state.bunnyFontLoadPromises[url];
            }

            const existing = Array.from(document.querySelectorAll('link[data-aa-bunny-font-css]'))
                .find(link => link.dataset.aaBunnyFontCss === url);
            if (existing) {
                state.bunnyFontLoadPromises[url] = document.fonts?.ready?.catch(() => null) || Promise.resolve();
                return state.bunnyFontLoadPromises[url];
            }

            state.bunnyFontLoadPromises[url] = new Promise(resolve => {
                const link = document.createElement('link');
                const done = () => {
                    if (document.fonts?.ready) {
                        document.fonts.ready.then(resolve).catch(resolve);
                        return;
                    }
                    resolve();
                };
                link.rel = 'stylesheet';
                link.href = url;
                link.dataset.aaBunnyFontCss = url;
                link.onload = done;
                link.onerror = done;
                document.head.appendChild(link);
            });

            return state.bunnyFontLoadPromises[url];
        }

        function ensureCustomFontCss(fontFamily) {
            if (!isCustomFontFamily(fontFamily) || !config.customFontCssUrl) {
                return Promise.resolve();
            }

            const url = config.customFontCssUrl;
            if (state.customFontLoadPromises[url]) {
                return state.customFontLoadPromises[url];
            }

            const existing = Array.from(document.querySelectorAll('link[data-aa-custom-font-css]'))
                .find(link => link.dataset.aaCustomFontCss === url);
            if (existing) {
                state.customFontLoadPromises[url] = document.fonts?.ready?.catch(() => null) || Promise.resolve();
                return state.customFontLoadPromises[url];
            }

            state.customFontLoadPromises[url] = new Promise(resolve => {
                const link = document.createElement('link');
                const done = () => {
                    if (document.fonts?.ready) {
                        document.fonts.ready.then(resolve).catch(resolve);
                        return;
                    }
                    resolve();
                };
                link.rel = 'stylesheet';
                link.href = url;
                link.dataset.aaCustomFontCss = url;
                link.onload = done;
                link.onerror = done;
                document.head.appendChild(link);
            });

            return state.customFontLoadPromises[url];
        }

        function collectTextFontFamilies(objects) {
            const families = [];
            const push = fontFamily => {
                const family = normalizeFontFamily(fontFamily);
                if (family && !families.includes(family)) families.push(family);
            };

            walkFabricObjects(objects || [], object => {
                if (isFabricTextObject(object)) {
                    push(object.fontFamily);
                }
                if (object?.countdownFontFamily) {
                    push(object.countdownFontFamily);
                }
            });

            return families;
        }

        function getUsedBunnyFontUrls(data = null) {
            const source = data || getCanvasData();
            const pages = Array.isArray(source?.pages) ? source.pages : [source];
            const urls = [];

            pages.forEach(pageData => {
                collectTextFontFamilies(pageData?.objects || []).forEach(fontFamily => {
                    const url = bunnyFontUrlForFamily(fontFamily);
                    if (url && !urls.includes(url)) urls.push(url);
                });
            });

            return urls;
        }

        function bunnyFontHeadLinks(data = null) {
            const urls = getUsedBunnyFontUrls(data);
            if (!urls.length) return '';
            return '<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>' + urls.map(url =>
                '<link href="' + escapeHtml(url) + '" rel="stylesheet">').join('');
        }

        function loadFontsForObjects(objects) {
            if (!document.fonts || !document.fonts.load) {
                return Promise.resolve();
            }

            const families = collectTextFontFamilies(objects || []);

            if (!families.includes('Inter')) {
                families.push('Inter');
            }

            return Promise.all(families.map(fontFamily => Promise.all([
                    ensureBunnyFontCss(fontFamily),
                    ensureGoogleFontCss(fontFamily),
                    ensureCustomFontCss(fontFamily),
                ])))
                .then(() => Promise.all(families.map(fontFamily => document.fonts
                    .load('32px "' + fontFamily.replace(/"/g, '') + '"')
                    .catch(() => null))))
                .then(() => document.fonts.ready).catch(() => null);
        }

        function recalculateTextObjects(canvas) {
            clearTextClipPaths(canvas);
            walkFabricObjects(canvas.getObjects(), object => {
                if (!isFabricTextObject(object)) return;
                object.dirty = true;
                if (typeof object.initDimensions === 'function') {
                    object.initDimensions();
                }
                object.setCoords();
            });
            canvas.requestRenderAll();
        }

        function withFontTimeout(promise, timeout = 4500) {
            return Promise.race([
                promise,
                new Promise(resolve => window.setTimeout(resolve, timeout)),
            ]);
        }

        function preloadEditorFonts() {
            if (!document.fonts || !document.fonts.load) {
                return Promise.resolve();
            }

            const optionFonts = Array.from(els.aaFontInput?.options || [])
                .filter(option => option.dataset.fontSource !== 'bunny')
                .map(option => option.value)
                .filter(Boolean);
            const families = [...new Set(['Inter', 'Arial', 'Georgia', ...optionFonts])];
            const styles = ['400', '700', 'italic 400'];
            const requests = [];

            families.forEach(fontFamily => {
                const safeFamily = normalizeFontFamily(fontFamily).replace(/"/g, '');
                styles.forEach(style => {
                    requests.push(Promise.all([
                            ensureBunnyFontCss(fontFamily),
                            ensureGoogleFontCss(fontFamily),
                            ensureCustomFontCss(fontFamily),
                        ])
                        .then(() => document.fonts.load(`${style} 32px "${safeFamily}"`))
                        .catch(() => null));
                });
            });

            return withFontTimeout(Promise.all(requests).then(() => document.fonts.ready));
        }

        function scheduleInitialFontCanvasRefresh() {
            if (state.fontRefreshDone || !document.fonts || !document.fonts.ready) return;
            state.fontRefreshDone = true;

            document.fonts.ready.then(async () => {
                await waitForCanvasReady();
                showCanvasLoading('Menyesuaikan font...');
                await loadFontsForObjects(state.canvas.getObjects());
                recalculateTextObjects(state.canvas);
                refreshImageBorderRadius(state.canvas);
                state.canvas.renderAll();
                fitZoom();
                window.setTimeout(() => {
                    recalculateTextObjects(state.canvas);
                    state.canvas.renderAll();
                    hideCanvasLoading();
                }, 120);
            }).catch(() => {});
        }

        function applyImageBorderRadius(image, radius = 0) {
            if (!image || image.type !== 'image') return;

            const value = Math.max(0, Number(radius) || 0);
            image.set('borderRadius', value);
            if (image.clipPath && image.clipPath.type === 'rect' && (image.clipPath.rx || image.clipPath.ry)) {
                image.set('clipPath', null);
            }
            image.dirty = true;
            image.setCoords();
        }

        function installRoundedImageRenderer() {
            if (!window.fabric || fabric.Image.prototype.__aaRoundedOverlayRendererInstalled) return;

            const originalRender = fabric.Image.prototype._render;
            const drawImagePath = (ctx, width, height, radius) => {
                const r = Math.min(Math.max(0, Number(radius) || 0), width / 2, height / 2);
                const x = -width / 2;
                const y = -height / 2;
                ctx.beginPath();
                if (!r) {
                    ctx.rect(x, y, width, height);
                    return;
                }
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
            };
            const drawImageStroke = (ctx, image, width, height, radius) => {
                const strokeWidth = Math.max(0, Number(image.strokeWidth) || 0);
                if (!strokeWidth || !image.stroke || image.stroke === 'transparent') return;
                ctx.save();
                drawImagePath(ctx, width, height, radius);
                ctx.lineWidth = strokeWidth;
                ctx.strokeStyle = image.stroke;
                ctx.lineJoin = 'round';
                ctx.lineCap = image.imageStrokeStyle === 'dotted' ? 'round' : 'butt';
                if (Array.isArray(image.strokeDashArray)) {
                    ctx.setLineDash(image.strokeDashArray);
                }
                ctx.stroke();
                ctx.restore();
            };
            const createImageOverlayFill = (ctx, preset, width, height) => {
                const name = String(preset || '');
                const left = -width / 2;
                const top = -height / 2;
                const right = width / 2;
                const bottom = height / 2;
                let gradient;
                if (name === 'dark-bottom') {
                    gradient = ctx.createLinearGradient(0, top, 0, bottom);
                    gradient.addColorStop(0, 'rgba(15, 23, 42, 0)');
                    gradient.addColorStop(0.58, 'rgba(15, 23, 42, 0.08)');
                    gradient.addColorStop(1, 'rgba(15, 23, 42, 0.68)');
                    return gradient;
                }
                if (name === 'dark-top') {
                    gradient = ctx.createLinearGradient(0, top, 0, bottom);
                    gradient.addColorStop(0, 'rgba(15, 23, 42, 0.66)');
                    gradient.addColorStop(0.72, 'rgba(15, 23, 42, 0)');
                    return gradient;
                }
                if (name === 'vignette') {
                    gradient = ctx.createRadialGradient(0, 0, Math.min(width, height) * 0.18, 0, 0, Math.max(width, height) * 0.72);
                    gradient.addColorStop(0, 'rgba(15, 23, 42, 0)');
                    gradient.addColorStop(1, 'rgba(15, 23, 42, 0.62)');
                    return gradient;
                }
                if (name === 'gold') {
                    gradient = ctx.createLinearGradient(left, top, right, bottom);
                    gradient.addColorStop(0, 'rgba(180, 126, 35, 0.48)');
                    gradient.addColorStop(0.48, 'rgba(255, 255, 255, 0)');
                    gradient.addColorStop(1, 'rgba(15, 23, 42, 0.18)');
                    return gradient;
                }
                if (name === 'sunset') {
                    gradient = ctx.createLinearGradient(left, top, right, bottom);
                    gradient.addColorStop(0, 'rgba(244, 114, 23, 0.44)');
                    gradient.addColorStop(0.52, 'rgba(236, 72, 153, 0.34)');
                    gradient.addColorStop(1, 'rgba(30, 41, 59, 0.32)');
                    return gradient;
                }
                if (name === 'rose') {
                    gradient = ctx.createRadialGradient(left + width * 0.28, top + height * 0.24, 0, left + width * 0.28, top + height * 0.24, Math.max(width, height) * 0.68);
                    gradient.addColorStop(0, 'rgba(244, 114, 182, 0.52)');
                    gradient.addColorStop(0.52, 'rgba(190, 24, 93, 0.16)');
                    gradient.addColorStop(1, 'rgba(15, 23, 42, 0.24)');
                    return gradient;
                }
                if (name === 'ocean') {
                    gradient = ctx.createLinearGradient(left, top, right, bottom);
                    gradient.addColorStop(0, 'rgba(14, 116, 144, 0.4)');
                    gradient.addColorStop(0.54, 'rgba(37, 99, 235, 0.34)');
                    gradient.addColorStop(1, 'rgba(15, 23, 42, 0.28)');
                    return gradient;
                }
                if (name === 'slate') {
                    gradient = ctx.createLinearGradient(left, top, right, bottom);
                    gradient.addColorStop(0, 'rgba(15, 23, 42, 0.52)');
                    gradient.addColorStop(0.55, 'rgba(71, 85, 105, 0.18)');
                    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
                    return gradient;
                }
                return null;
            };
            const drawImageOverlayGradient = (ctx, image, width, height) => {
                const fill = createImageOverlayFill(ctx, image.aaImageOverlayGradient, width, height);
                if (!fill) return;
                ctx.save();
                ctx.fillStyle = fill;
                ctx.fillRect(-width / 2, -height / 2, width, height);
                ctx.restore();
            };
            const imageEffectCanvasFilter = image => {
                const preset = String(image?.aaImageEffectPreset || 'none');
                if (!preset || preset === 'none' || preset === 'opacity' || preset === 'shadow') return '';
                if (Array.isArray(image.filters) && image.filters.length) return '';
                if (preset === 'brightness') return 'brightness(1.16)';
                if (preset === 'contrast') return 'contrast(1.22)';
                if (preset === 'saturation') return 'saturate(1.38)';
                if (preset === 'grayscale') return 'grayscale(1)';
                if (preset === 'sepia') return 'sepia(1)';
                if (preset === 'blur') return 'blur(2px)';
                if (preset === 'sharpen') return 'contrast(1.28) saturate(1.12)';
                if (preset === 'vintage') return 'sepia(.55) contrast(1.08) saturate(.82)';
                if (preset === 'soft-wedding') return 'brightness(1.08) contrast(.96) saturate(1.18) sepia(.08)';
                if (preset === 'clean-bright') return 'brightness(1.14) contrast(1.08) saturate(1.08)';
                if (preset === 'warm-editorial') return 'sepia(.18) brightness(1.06) contrast(1.12) saturate(1.14)';
                if (preset === 'film-matte') return 'sepia(.2) contrast(.92) saturate(.78) brightness(1.04)';
                if (preset === 'pastel-bloom') return 'brightness(1.1) contrast(.94) saturate(1.32) hue-rotate(-6deg)';
                if (preset === 'moody-luxe') return 'brightness(.88) contrast(1.22) saturate(.9) sepia(.08)';
                if (preset === 'classic-bw') return 'grayscale(1) contrast(1.18) brightness(1.04)';
                if (preset === 'dreamy-soft') return 'brightness(1.12) contrast(.9) saturate(1.12) blur(.75px)';
                if (preset === 'recolor-white') return 'grayscale(.35) brightness(1.34) contrast(.86) saturate(.68)';
                if (preset === 'recolor-black') return 'grayscale(1) brightness(.72) contrast(1.28)';
                if (preset === 'recolor-gold') return 'sepia(.55) saturate(1.45) hue-rotate(4deg) brightness(1.08) contrast(1.04)';
                if (preset === 'recolor-teal') return 'sepia(.18) saturate(1.35) hue-rotate(135deg) brightness(.96) contrast(1.06)';
                if (preset === 'recolor-rose') return 'sepia(.22) saturate(1.35) hue-rotate(300deg) brightness(1.04) contrast(.98)';
                if (preset === 'recolor-slate') return 'grayscale(.65) sepia(.12) saturate(.7) hue-rotate(170deg) brightness(.92) contrast(1.08)';
                if (preset === 'remove-color') return 'saturate(.2) contrast(1.12)';
                return '';
            };
            const renderImageWithCanvasEffect = (image, ctx) => {
                const filter = imageEffectCanvasFilter(image);
                if (!filter) {
                    originalRender.call(image, ctx);
                    return;
                }
                const previousFilter = ctx.filter;
                ctx.filter = filter;
                originalRender.call(image, ctx);
                ctx.filter = previousFilter;
            };
            fabric.Image.prototype._render = function(ctx) {
                const radius = Math.max(0, Number(this.borderRadius) || 0);
                const width = Math.max(1, this.width || 1);
                const height = Math.max(1, this.height || 1);

                if (radius) {
                    ctx.save();
                    drawImagePath(ctx, width, height, radius);
                    ctx.clip();
                    renderImageWithCanvasEffect(this, ctx);
                    drawImageOverlayGradient(ctx, this, width, height);
                    ctx.restore();
                } else {
                    renderImageWithCanvasEffect(this, ctx);
                    drawImageOverlayGradient(ctx, this, width, height);
                }
                drawImageStroke(ctx, this, width, height, radius);
            };
            fabric.Image.prototype.__aaRoundedRendererInstalled = true;
            fabric.Image.prototype.__aaRoundedOverlayRendererInstalled = true;
        }

        function refreshImageBorderRadius(canvas) {
            canvas.getObjects().forEach(object => {
                if (object.type === 'image') {
                    applyImageBorderRadius(object, object.borderRadius || 0);
                } else if (object.type === 'group' && object.getObjects) {
                    object.getObjects().forEach(child => {
                        if (child.type === 'image') {
                            applyImageBorderRadius(child, child.borderRadius || 0);
                        }
                    });
                    object.dirty = true;
                    object.setCoords();
                }
            });
        }

        <?= view('editor/partials/scripts/pages', get_defined_vars()) ?>
        <?= view('editor/partials/scripts/import_reference', get_defined_vars()) ?>
        <?= view('editor/partials/scripts/reference_mapper', get_defined_vars()) ?>
        <?= view('editor/partials/scripts/ocr_text', get_defined_vars()) ?>
        
  function aaGetActiveSelectionObjectsForHistory() {
    if (!state.canvas || !window.fabric) return null;

    const active = state.canvas.getActiveObject?.();

    if (
        active &&
        active !== state.cropBox &&
        active.type === 'activeSelection' &&
        typeof active.getObjects === 'function'
    ) {
        return active.getObjects().filter(Boolean);
    }

    return null;
}

function aaWithStableSelectionForHistory(callback) {
    if (!state.canvas || typeof callback !== 'function') {
        return callback?.();
    }

    const canvas = state.canvas;
    const selectionObjects = aaGetActiveSelectionObjectsForHistory();

    if (!selectionObjects || !selectionObjects.length) {
        return callback();
    }

    // Penting: paksa semua object keluar dari mode transform activeSelection dulu.
    canvas.discardActiveObject();

    selectionObjects.forEach(object => {
        object.setCoords?.();
        object.dirty = true;
    });

    canvas.requestRenderAll?.();

    let result;

    try {
        result = callback();
    } finally {
        // Kembalikan selection agar user tidak merasa selection hilang.
        const stillExists = selectionObjects.filter(object => {
            return object && canvas.getObjects().includes(object);
        });

        if (stillExists.length > 1) {
            const selection = new fabric.ActiveSelection(stillExists, {
                canvas: canvas,
            });

            canvas.setActiveObject(selection);
            selection.setCoords();
        } else if (stillExists.length === 1) {
            canvas.setActiveObject(stillExists[0]);
            stillExists[0].setCoords?.();
        }

        canvas.requestRenderAll?.();
    }

    return result;
}

        function aaRunHistoryBatch(callback, afterSnapshot = true) {
            state.__aaHistoryBatch = true;

            try {
                if (typeof callback === 'function') {
                    callback();
                }
            } finally {
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        state.__aaHistoryBatch = false;

                        if (afterSnapshot) {
                            snapshot();
                        }
                    });
                });
            }
        }
       function snapshot() {
            if (
                state.isRestoring ||
                state.__aaHistoryRestoring ||
                state.__aaHistoryBatch ||
                state.__aaOcrReviewActive ||
                state.isCropping ||
                !state.canvas
            ) {
                return;
            }

            let json = '';

            try {
                json = aaWithStableSelectionForHistory(function () {
                    storeCurrentPage();

                    const data = getCanvasData();

                    data.editMode = state.editMode === 'opening' ? 'opening' : 'pages';

                    return JSON.stringify(data);
                });
            } catch (error) {
                console.error('[AdaAcara Editor] Snapshot gagal:', error);
                setStatus('Snapshot gagal. Coba klik Save Draft setelah melepas crop/selection.', 'error');
                return;
            }

            if (!json) return;

            if (!Array.isArray(state.history)) {
                state.history = [];
            }

            if (!Array.isArray(state.redo)) {
                state.redo = [];
            }

            const last = state.history[state.history.length - 1];

            if (json === last) return;

            state.history.push(json);

            if (state.history.length > 60) {
                state.history.shift();
            }

            state.redo = [];
            state.hasUnsavedChanges = true;

            setStatus('Ada perubahan');
        }
        
        function aaShowHistoryFreezeOverlay() {
            if (!state.canvas || !els.aaActiveArtboardFrame) return null;

            let dataUrl = '';

            try {
                dataUrl = state.canvas.toDataURL({
                    format: 'png',
                    multiplier: 1
                });
            } catch (error) {
                console.warn('[AA HISTORY] Gagal membuat freeze overlay:', error);
                return null;
            }

            const oldOverlay = els.aaActiveArtboardFrame.querySelector('.aa-history-freeze-overlay');

            if (oldOverlay) {
                oldOverlay.remove();
            }

            const overlay = document.createElement('div');
            overlay.className = 'aa-history-freeze-overlay';

            const img = document.createElement('img');
            img.alt = '';
            img.src = dataUrl;

            overlay.appendChild(img);
            els.aaActiveArtboardFrame.appendChild(overlay);

            return overlay;
        }
        
        function aaAfterCanvasStable(callback, frameCount = 4) {
            let count = 0;

            function next() {
                count += 1;

                if (count >= frameCount) {
                    callback?.();
                    return;
                }

                requestAnimationFrame(next);
            }

            requestAnimationFrame(next);
        }
        function aaGetSnapshotPageData(json, options = {}) {
    const data = JSON.parse(json);

    const restoreMode = options.mode === 'opening' || options.mode === 'pages'
        ? options.mode
        : (data.editMode === 'opening' ? 'opening' : 'pages');

    if (restoreMode === 'opening') {
        const opening = normalizeOpeningConfig(data.opening || state.opening);

        return {
            data,
            mode: 'opening',
            pageData: openingToPageData(opening),
            opening
        };
    }

    const pages = normalizeProjectPages(data);
    const activePageIndex = Math.max(0, Math.min(data.activePageIndex || 0, pages.length - 1));
    const guestbook = normalizeGuestbookConfig(data.guestbook || state.guestbook);

    return {
        data,
        mode: 'pages',
        pageData: pages[activePageIndex] || createBlankPageData('Halaman 1'),
        pages,
        activePageIndex,
        guestbook
    };
}

function aaApplyPageDataToCanvasAtomic(pageData, done) {
    if (!state.canvas || !window.fabric || !pageData) {
        done?.();
        return;
    }

    const canvas = state.canvas;
    const previousRenderOnAddRemove = canvas.renderOnAddRemove;

    const targetData = JSON.parse(JSON.stringify(pageData));
    sanitizeFabricPageData?.(targetData);

    const targetObjectsJson = Array.isArray(targetData.objects) ? targetData.objects : [];

    state.__aaHistoryRestoring = true;
    state.isRestoring = true;

    canvas.discardActiveObject();
    canvas._currentTransform = null;
    canvas._groupSelector = null;

    fabric.util.enlivenObjects(targetObjectsJson, function (objects) {
        canvas.renderOnAddRemove = false;

        try {
            const oldObjects = canvas.getObjects().slice();

            oldObjects.forEach(function (object) {
                canvas.remove(object);
            });

            canvas.backgroundColor =
                targetData.background ||
                targetData.backgroundColor ||
                '#ffffff';

            const width =
                targetData?.artboard?.width ||
                targetData.width ||
                canvas.getWidth() ||
                1080;

            const height =
                targetData?.artboard?.height ||
                targetData.height ||
                canvas.getHeight() ||
                1920;

            if (canvas.getWidth() !== width || canvas.getHeight() !== height) {
                canvas.setWidth(width);
                canvas.setHeight(height);
            }

            objects.filter(Boolean).forEach(function (object) {
                aaApplySafeImageHitTesting?.(object);
                object.setCoords?.();
                object.dirty = true;
                canvas.add(object);
            });

            applyStoredObjectLocks?.(canvas);
            aaUpgradeCanvasTextObjectsToTextbox?.(canvas);
            ensureBackgroundImageBack?.();

            canvas.getObjects().forEach(function (object) {
                aaApplySafeImageHitTesting?.(object);
                object.setCoords?.();
                object.dirty = true;
            });

            canvas.renderOnAddRemove = previousRenderOnAddRemove;

            canvas.requestRenderAll();

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    canvas.requestRenderAll();

                    syncInspector?.();
                    syncObjectFloatingToolbar?.();
                    hideObjectFloatingToolbar?.();
                    hideObjectOverflowOverlay?.();
                    hideCountdownContextToolbar?.();
                    hideInteractionPopover?.();
                    closeToolbarPopovers?.();

                    done?.();
                });
            });
        } catch (error) {
            canvas.renderOnAddRemove = previousRenderOnAddRemove;
            console.error('[AA HISTORY] Atomic restore gagal:', error);
            done?.(error);
        }
    });
}

       function restoreFromSnapshot(json, options = {}) {
            if (!json || state.__aaHistoryRestoring) return;

            let parsed;

            try {
                parsed = aaGetSnapshotPageData(json, options);
            } catch (error) {
                console.error('[AA HISTORY] Snapshot tidak valid:', error);
                setStatus?.('Undo gagal membaca snapshot', 'error');
                return;
            }

            state.isRestoring = true;
            state.__aaHistoryRestoring = true;

            state.editMode = parsed.mode;

            if (parsed.mode === 'opening') {
                state.opening = parsed.opening;
            } else {
                state.pages = parsed.pages;
                state.activePageIndex = parsed.activePageIndex;
                state.guestbook = parsed.guestbook;
                syncGuestbookPanel?.();
                renderPageList?.();
            }

            aaApplyPageDataToCanvasAtomic(parsed.pageData, function (error) {
                state.isRestoring = false;
                state.__aaHistoryRestoring = false;

                if (error) {
                    setStatus?.('Undo gagal memuat canvas', 'error');
                    return;
                }

                storeCurrentPage?.();

                if (options.preserveZoom !== true) {
                    fitZoom?.();
                } else {
                    updateZoom?.();
                }
            });
        }

        function snapshotScopeValue(json, mode) {
            try {
                const data = JSON.parse(json);
                if (mode === 'opening') {
                    return JSON.stringify(normalizeOpeningConfig(data.opening || state.opening));
                }

                const pages = normalizeProjectPages(data);
                const activePageIndex = Math.max(0, Math.min(data.activePageIndex || 0, pages.length - 1));
                return JSON.stringify({
                    pages,
                    activePageIndex,
                    guestbook: normalizeGuestbookConfig(data.guestbook || state.guestbook),
                });
            } catch (error) {
                return '';
            }
        }

        function findHistoryIndexForScope(history, mode, currentValue) {
            for (let index = history.length - 1; index >= 0; index -= 1) {
                const value = snapshotScopeValue(history[index], mode);
                if (value && value !== currentValue) return index;
            }
            return -1;
        }

        function undo() {
            if (!state.history || state.history.length < 2) return;
            if (state.isRestoring || state.__aaHistoryRestoring) return;

            if (state.isCropping) {
                finishCropMode(false);
            }

            const mode = state.editMode === 'opening' ? 'opening' : 'pages';

            if (!Array.isArray(state.redo)) {
                state.redo = [];
            }

            if (state.canvas) {
                state.canvas.discardActiveObject();
                state.canvas._currentTransform = null;
                state.canvas._groupSelector = null;
                state.canvas.requestRenderAll?.();
            }

            const current = state.history.pop();
            state.redo.push(current);

            const previous = state.history[state.history.length - 1];

            if (!previous) return;

            restoreFromSnapshot(previous, {
                mode,
                preserveZoom: true,
            });

            setStatus?.('Undo');
        }

        function redo() {
            if (!state.redo || !state.redo.length) return;
            if (state.isRestoring || state.__aaHistoryRestoring) return;

            if (state.isCropping) {
                finishCropMode(false);
            }

            const mode = state.editMode === 'opening' ? 'opening' : 'pages';

            if (!Array.isArray(state.history)) {
                state.history = [];
            }

            if (state.canvas) {
                state.canvas.discardActiveObject();
                state.canvas._currentTransform = null;
                state.canvas._groupSelector = null;
                state.canvas.requestRenderAll?.();
            }

            const next = state.redo.pop();

            if (!next) return;

            state.history.push(next);

            restoreFromSnapshot(next, {
                mode,
                preserveZoom: true,
            });

            setStatus?.('Redo');
        }

        function centerObject(object) {
            object.set({
                left: state.canvas.getWidth() / 2,
                top: state.canvas.getHeight() / 2,
                originX: 'center',
                originY: 'center',
            });
            state.canvas.add(object);
            state.canvas.setActiveObject(object);
            state.canvas.renderAll();
            snapshot();
        }

        function addText(kind) {
            const isHeading = kind === 'heading';

            const text = new fabric.Textbox(isHeading ? 'Nama Acara' : 'Tulis teks di sini', {
                width: isHeading ? 760 : 560,
                fontFamily: isHeading ? 'Playfair Display' : 'Inter',
                fontSize: isHeading ? 94 : 44,
                fill: '#111827',
                textAlign: 'center',
                lineHeight: 1.14,
                customType: isHeading ? 'heading' : 'text',
                originX: 'center',
                originY: 'center'
            });

            aaApplyTextboxResizeControls(text);
            centerObject(text);
        }

        const aaBusinessProfileLaunchElements = [
            { key: 'book_now_whatsapp', title: 'Book Now', icon: 'send', body: 'Teks booking langsung ke WhatsApp dengan pesan konsultasi default.' },
            { key: 'mua_social_media', title: 'Social Media', icon: 'share-2', body: 'Link Instagram, TikTok, YouTube, dan channel social media lainnya.' },
            { key: 'zoomable_photo', title: 'Zoomable Photo', icon: 'zoom-in', body: 'Foto yang bisa diklik untuk preview besar saat publish.' },
        ];

        const aaBusinessProfileElementCatalog = {
            mua: {
                title: 'MUA',
                description: 'Elemen khusus untuk Make Up Artist',
                icon: 'sparkles',
                accent: '#ec4899',
                note: 'Cocok untuk menampilkan hasil makeup, paket pernikahan, dan ketersediaan tanggal.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'makeup_portfolio', title: 'Makeup Portfolio', icon: 'image', body: 'Tampilkan beberapa hasil makeup terbaik dengan judul singkat dan gaya makeup.' },
                    { key: 'before_after', title: 'Before / After', icon: 'scan-eye', body: 'Bandingkan foto sebelum dan sesudah makeup dalam layout dua kolom.' },
                    { key: 'makeup_packages', title: 'Makeup Packages', icon: 'package', body: 'Susun paket makeup, benefit utama, dan harga mulai.' },
                    { key: 'makeup_style', title: 'Makeup Style', icon: 'brush', body: 'Highlight gaya makeup seperti natural, bold, akad, atau reception look.' },
                    { key: 'bridal_packages', title: 'Bridal Packages', icon: 'user-round-check', body: 'Blok penawaran paket bridal lengkap dengan detail layanan.' },
                    { key: 'artist_profile', title: 'Artist Profile', icon: 'user-round', body: 'Perkenalkan artist, pengalaman, dan ciri khas layanan.' },
                    { key: 'team_mua', title: 'Team MUA', icon: 'users-round', body: 'Tampilkan anggota tim dan peran masing-masing.' },
                    { key: 'products_used', title: 'Products Used', icon: 'badge-check', body: 'Cantumkan brand atau produk makeup yang sering digunakan.' },
                    { key: 'service_location', title: 'Service Location', icon: 'map-pin', body: 'Area layanan, studio, dan opsi panggilan ke lokasi.' },
                    { key: 'available_date', title: 'Available Date', icon: 'calendar', body: 'Tampilkan informasi tanggal tersedia dan jadwal booking.' },
                    { key: 'booking_makeup', title: 'Booking Makeup', icon: 'messages-square', body: 'CTA untuk konsultasi, booking jadwal, dan kontak WhatsApp.' },
                    { key: 'client_review', title: 'Client Review', icon: 'star', body: 'Kutip testimoni klien dan rating layanan.' },
                ],
            },
            wedding_organizer: {
                title: 'Wedding Organizer',
                description: 'Elemen khusus untuk jasa perencana acara',
                icon: 'handshake',
                accent: '#14b8a6',
                note: 'Cocok untuk paket WO, rundown, portfolio acara, dan konsultasi.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'service_packages', title: 'WO Packages', icon: 'package', body: 'Paket wedding organizer, scope kerja, dan harga mulai.' },
                    { key: 'event_timeline', title: 'Event Timeline', icon: 'calendar-clock', body: 'Rundown persiapan dan alur koordinasi acara.' },
                    { key: 'portfolio_event', title: 'Event Portfolio', icon: 'images', body: 'Dokumentasi acara yang pernah ditangani.' },
                    { key: 'vendor_network', title: 'Vendor Network', icon: 'network', body: 'Partner vendor yang siap mendukung acara.' },
                    { key: 'consultation', title: 'Consultation CTA', icon: 'message-circle', body: 'Ajakan konsultasi dan briefing kebutuhan acara.' },
                    { key: 'client_review', title: 'Client Review', icon: 'star', body: 'Testimoni pasangan atau klien sebelumnya.' },
                ],
            },
            decor: {
                title: 'Dekorasi',
                description: 'Elemen khusus untuk vendor dekorasi',
                icon: 'flower-2',
                accent: '#f97316',
                note: 'Cocok untuk moodboard, tema dekorasi, katalog paket, dan portfolio setup.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'decor_portfolio', title: 'Decor Portfolio', icon: 'images', body: 'Galeri setup dekorasi terbaik untuk berbagai konsep acara.' },
                    { key: 'theme_style', title: 'Theme Style', icon: 'palette', body: 'Tema dekorasi, warna utama, dan material yang digunakan.' },
                    { key: 'package_list', title: 'Decor Packages', icon: 'package', body: 'Paket dekorasi lengkap dengan area dan item.' },
                    { key: 'venue_setup', title: 'Venue Setup', icon: 'layout-template', body: 'Contoh layout pelaminan, entrance, table, dan backdrop.' },
                    { key: 'booking_decor', title: 'Booking Decor', icon: 'messages-square', body: 'CTA untuk cek tanggal dan konsultasi konsep.' },
                    { key: 'client_review', title: 'Client Review', icon: 'star', body: 'Ulasan klien tentang hasil dekorasi.' },
                ],
            },
            venue: {
                title: 'Venue',
                description: 'Elemen khusus untuk tempat acara',
                icon: 'building-2',
                accent: '#0ea5e9',
                note: 'Cocok untuk kapasitas, fasilitas, paket venue, lokasi, dan jadwal kunjungan.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'venue_gallery', title: 'Venue Gallery', icon: 'images', body: 'Foto area indoor, outdoor, ballroom, dan fasilitas.' },
                    { key: 'capacity', title: 'Capacity', icon: 'users-round', body: 'Kapasitas tamu dan opsi layout ruangan.' },
                    { key: 'facilities', title: 'Facilities', icon: 'badge-check', body: 'Fasilitas venue seperti parkir, sound, ruang VIP, dan mushola.' },
                    { key: 'venue_packages', title: 'Venue Packages', icon: 'package', body: 'Paket sewa venue dan benefit yang termasuk.' },
                    { key: 'location_map', title: 'Location', icon: 'map-pin', body: 'Alamat, akses, dan penanda lokasi venue.' },
                    { key: 'visit_booking', title: 'Visit Booking', icon: 'calendar', body: 'CTA untuk jadwal survey lokasi.' },
                ],
            },
            catering: {
                title: 'Catering',
                description: 'Elemen khusus untuk layanan catering',
                icon: 'utensils',
                accent: '#22c55e',
                note: 'Cocok untuk menu, paket prasmanan, test food, dan pemesanan.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'menu_showcase', title: 'Menu Showcase', icon: 'utensils', body: 'Tampilkan menu unggulan dan kategori hidangan.' },
                    { key: 'package_menu', title: 'Catering Packages', icon: 'package', body: 'Paket catering berdasarkan jumlah porsi dan pilihan menu.' },
                    { key: 'food_gallery', title: 'Food Gallery', icon: 'images', body: 'Foto hidangan dan display buffet.' },
                    { key: 'test_food', title: 'Test Food', icon: 'calendar-check', body: 'Informasi jadwal test food dan konsultasi menu.' },
                    { key: 'service_area', title: 'Service Area', icon: 'map-pin', body: 'Area layanan pengiriman dan onsite.' },
                    { key: 'order_cta', title: 'Order CTA', icon: 'messages-square', body: 'Ajakan order atau cek paket catering.' },
                ],
            },
            photographer: {
                title: 'Photographer',
                description: 'Elemen khusus untuk foto dan dokumentasi',
                icon: 'camera',
                accent: '#8b5cf6',
                note: 'Cocok untuk portfolio foto, paket dokumentasi, style, dan booking tanggal.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'photo_portfolio', title: 'Photo Portfolio', icon: 'images', body: 'Galeri karya terbaik untuk wedding, event, atau portrait.' },
                    { key: 'shoot_packages', title: 'Photo Packages', icon: 'package', body: 'Paket dokumentasi, durasi, jumlah fotografer, dan output.' },
                    { key: 'editing_style', title: 'Editing Style', icon: 'sliders-horizontal', body: 'Gaya warna, tone, dan pendekatan visual.' },
                    { key: 'album_output', title: 'Album Output', icon: 'book-open', body: 'Deliverables seperti album, cetak, soft file, dan highlight.' },
                    { key: 'available_date', title: 'Available Date', icon: 'calendar', body: 'Cek tanggal tersedia untuk sesi atau event.' },
                    { key: 'booking_photo', title: 'Booking Photo', icon: 'messages-square', body: 'CTA konsultasi dan booking dokumentasi.' },
                ],
            },
            freelancer: {
                title: 'Freelancer',
                description: 'Elemen khusus untuk jasa profesional individu',
                icon: 'pen-tool',
                accent: '#6366f1',
                note: 'Cocok untuk jasa, portfolio, proses kerja, harga, dan kontak cepat.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'service_list', title: 'Service List', icon: 'list-checks', body: 'Daftar layanan utama dan benefit untuk klien.' },
                    { key: 'portfolio', title: 'Portfolio', icon: 'images', body: 'Karya pilihan, project terakhir, dan hasil kerja.' },
                    { key: 'work_process', title: 'Work Process', icon: 'workflow', body: 'Tahapan kerja dari brief, revisi, sampai delivery.' },
                    { key: 'pricing', title: 'Pricing', icon: 'badge-dollar-sign', body: 'Paket harga, scope, dan estimasi pengerjaan.' },
                    { key: 'profile', title: 'Profile', icon: 'user-round', body: 'Profil singkat, pengalaman, dan spesialisasi.' },
                    { key: 'contact_cta', title: 'Contact CTA', icon: 'messages-square', body: 'Ajakan diskusi project dan kontak WhatsApp/email.' },
                ],
            },
            umkm: {
                title: 'UMKM',
                description: 'Elemen khusus untuk bisnis kecil dan toko',
                icon: 'store',
                accent: '#f59e0b',
                note: 'Cocok untuk produk unggulan, katalog, promo, lokasi toko, dan order.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'featured_products', title: 'Featured Products', icon: 'shopping-bag', body: 'Produk unggulan, foto, dan deskripsi singkat.' },
                    { key: 'catalog', title: 'Catalog', icon: 'grid-3x3', body: 'Katalog produk atau layanan dalam beberapa kategori.' },
                    { key: 'promo', title: 'Promo', icon: 'badge-percent', body: 'Promo aktif, bundling, atau penawaran terbatas.' },
                    { key: 'store_location', title: 'Store Location', icon: 'map-pin', body: 'Alamat toko, jam buka, dan area layanan.' },
                    { key: 'customer_review', title: 'Customer Review', icon: 'star', body: 'Testimoni pelanggan dan social proof.' },
                    { key: 'order_cta', title: 'Order CTA', icon: 'messages-square', body: 'Ajakan order cepat melalui WhatsApp atau marketplace.' },
                ],
            },
            agency: {
                title: 'Agency',
                description: 'Elemen khusus untuk bisnis layanan tim',
                icon: 'globe-2',
                accent: '#0891b2',
                note: 'Cocok untuk layanan, case study, klien, proses kerja, dan proposal.',
                elements: [
                    ...aaBusinessProfileLaunchElements,
                    { key: 'agency_services', title: 'Agency Services', icon: 'layers-3', body: 'Daftar layanan utama dan hasil yang ditawarkan.' },
                    { key: 'case_study', title: 'Case Study', icon: 'presentation', body: 'Ringkasan project, tantangan, solusi, dan hasil.' },
                    { key: 'client_logo', title: 'Client Logo', icon: 'badge-check', body: 'Logo klien atau brand yang pernah dilayani.' },
                    { key: 'team_profile', title: 'Team Profile', icon: 'users-round', body: 'Profil tim, role, dan kapabilitas.' },
                    { key: 'workflow', title: 'Workflow', icon: 'workflow', body: 'Tahapan kerja dari discovery sampai reporting.' },
                    { key: 'proposal_cta', title: 'Proposal CTA', icon: 'send', body: 'Ajakan minta proposal atau jadwalkan meeting.' },
                ],
            },
        };

        function addBusinessProfileElement(categoryKey, elementKey) {
            if (!state.canvas) return;
            const category = aaBusinessProfileElementCatalog[categoryKey];
            const spec = category?.elements?.find(item => item.key === elementKey);
            if (!category || !spec) return;
            if (['book_now_whatsapp', 'mua_social_media', 'zoomable_photo'].includes(elementKey)) {
                addBusinessProfileLaunchElement(categoryKey, elementKey, spec);
                return;
            }
            if (categoryKey === 'mua') {
                addMuaBusinessProfileElement(elementKey, spec);
                return;
            }

            addGenericBusinessProfileElement(categoryKey, elementKey, spec);
        }

        function addBusinessProfileLaunchElement(categoryKey, elementKey, spec = {}) {
            const cx = state.canvas.getWidth() / 2;
            const cy = state.canvas.getHeight() / 2;
            const category = aaBusinessProfileElementCatalog[categoryKey] || aaBusinessProfileElementCatalog.mua;
            const accent = category.accent || '#ec4899';
            const title = spec.title || 'Element';
            const whatsappUrl = 'https://wa.me/6281234567890?text=Halo%2C%20saya%20ingin%20konsultasi%20layanan.';

            if (elementKey === 'book_now_whatsapp') {
                aaBusinessAddObjects([aaBusinessText('Book Now via WhatsApp', {
                    left: cx,
                    top: cy,
                    width: 560,
                    fontSize: 34,
                    fontWeight: '900',
                    fill: accent,
                    textAlign: 'center',
                    customType: 'link-text',
                    link: whatsappUrl,
                    aaBusinessSection: true,
                    aaBusinessCategory: categoryKey,
                    aaBusinessElement: elementKey,
                    categoryKey,
                    elementKey,
                    label: 'Book Now WhatsApp',
                })], title);
                return;
            }

            if (elementKey === 'mua_social_media') {
                if (typeof addSocialMediaElement === 'function') {
                    addSocialMediaElement();
                    const active = state.canvas?.getActiveObject?.();
                    if (active) {
                        aaBusinessMark(active, elementKey, `${category.title} Social Media`, categoryKey);
                        snapshot();
                    }
                    return;
                }
            }

            if (elementKey === 'zoomable_photo') {
                aaBusinessAddObjects([aaBusinessPhotoFrame({
                    left: cx,
                    top: cy,
                    width: 520,
                    height: 310,
                    rx: 34,
                    ry: 34,
                    elementKey,
                    categoryKey,
                    label: `${category.title} Photo`,
                    galleryZoom: true,
                    galleryImageSrc: '',
                    galleryImageName: `${category.title} Photo`,
                })], title);
            }
        }

        function aaBusinessMark(object, elementKey, label, categoryKey = 'mua') {
            if (!object) return object;
            object.set({
                aaBusinessSection: true,
                aaBusinessCategory: categoryKey,
                aaBusinessElement: elementKey,
                label: label || object.label || '',
                objectCaching: false,
            });
            return object;
        }

        function aaBusinessText(text, options = {}) {
            const object = new fabric.Textbox(text, {
                width: options.width || 520,
                fontFamily: options.fontFamily || 'Inter',
                fontSize: options.fontSize || 28,
                fontWeight: options.fontWeight || '600',
                fill: options.fill || '#334155',
                textAlign: options.textAlign || 'left',
                lineHeight: options.lineHeight || 1.22,
                left: options.left || 0,
                top: options.top || 0,
                originX: options.originX || 'center',
                originY: options.originY || 'center',
                customType: options.customType || 'text',
                link: options.link || '',
                copyText: options.copyText || '',
                copyFeedback: options.copyFeedback || '',
                underline: options.underline === true,
            });
            aaApplyTextboxResizeControls(object);
            return aaBusinessMark(object, options.elementKey || '', options.label || text, options.categoryKey || 'mua');
        }

        function aaBusinessRect(options = {}) {
            const object = new fabric.Rect({
                left: options.left || 0,
                top: options.top || 0,
                originX: options.originX || 'center',
                originY: options.originY || 'center',
                width: options.width || 260,
                height: options.height || 140,
                rx: options.rx ?? 24,
                ry: options.ry ?? 24,
                fill: options.fill || '#ffffff',
                stroke: options.stroke || '#ffd6e7',
                strokeWidth: options.strokeWidth ?? 2,
                strokeDashArray: options.strokeDashArray || null,
                customType: options.customType || 'shape',
                shadow: options.shadow || '',
                objectCaching: false,
            });
            return aaBusinessMark(object, options.elementKey || '', options.label || '', options.categoryKey || 'mua');
        }

        function aaBusinessPhotoFrame(options = {}) {
            return aaBusinessRect({
                ...options,
                customType: 'photo-frame',
                fill: options.fill || '#fff7fb',
                stroke: options.stroke || '#f9a8d4',
                strokeDashArray: options.strokeDashArray || [12, 10],
            }).set({
                aaSource: 'business-profile',
                aaImageFrameShape: options.aaImageFrameShape || 'rounded',
                borderRadius: options.rx ?? 24,
                galleryZoom: options.galleryZoom === true,
                galleryImageSrc: options.galleryImageSrc || '',
                galleryImageName: options.galleryImageName || options.label || 'Foto',
            });
        }

        function aaBusinessAddObjects(objects, title) {
            const safeObjects = objects.filter(Boolean);
            if (!safeObjects.length) return;
            state.canvas.add(...safeObjects);
            safeObjects.forEach(object => {
                object.setCoords?.();
            });
            if (safeObjects.length === 1) {
                state.canvas.setActiveObject(safeObjects[0]);
            } else {
                state.canvas.setActiveObject(new fabric.ActiveSelection(safeObjects, {
                    canvas: state.canvas,
                }));
            }
            state.canvas.requestRenderAll();
            snapshot();
            syncInspector();
            setStatus(`${title} ditambahkan`);
        }

        function aaBusinessSectionBase(elementKey, title, subtitle = '') {
            const cx = state.canvas.getWidth() / 2;
            const cy = state.canvas.getHeight() / 2;
            const width = 720;
            return {
                cx,
                cy,
                width,
                bg: aaBusinessRect({
                    left: cx,
                    top: cy,
                    width,
                    height: 520,
                    rx: 32,
                    ry: 32,
                    fill: '#ffffff',
                    stroke: '#ffe4ef',
                    shadow: '0 18px 42px rgba(15, 23, 42, 0.10)',
                    elementKey,
                    label: title,
                }),
                eyebrow: aaBusinessText('MUA SERVICE', {
                    left: cx,
                    top: cy - 218,
                    width: width - 80,
                    fontSize: 20,
                    fontWeight: '900',
                    fill: '#ec4899',
                    textAlign: 'center',
                    elementKey,
                    label: title,
                }),
                title: aaBusinessText(title, {
                    left: cx,
                    top: cy - 174,
                    width: width - 80,
                    fontFamily: 'Playfair Display',
                    fontSize: 48,
                    fontWeight: '700',
                    fill: '#111827',
                    textAlign: 'center',
                    elementKey,
                    label: title,
                }),
                subtitle: subtitle ? aaBusinessText(subtitle, {
                    left: cx,
                    top: cy - 122,
                    width: width - 120,
                    fontSize: 24,
                    fontWeight: '600',
                    fill: '#64748b',
                    textAlign: 'center',
                    elementKey,
                    label: title,
                }) : null,
            };
        }

        function aaBusinessCategoryBase(categoryKey, elementKey, title, subtitle = '') {
            const category = aaBusinessProfileElementCatalog[categoryKey] || aaBusinessProfileElementCatalog.mua;
            const cx = state.canvas.getWidth() / 2;
            const cy = state.canvas.getHeight() / 2;
            const width = 720;
            const accent = category.accent || '#ec4899';
            return {
                category,
                cx,
                cy,
                width,
                accent,
                bg: aaBusinessRect({
                    left: cx,
                    top: cy,
                    width,
                    height: 520,
                    rx: 32,
                    ry: 32,
                    fill: '#ffffff',
                    stroke: accent,
                    strokeWidth: 2,
                    shadow: '0 18px 42px rgba(15, 23, 42, 0.10)',
                    elementKey,
                    categoryKey,
                    label: title,
                }),
                eyebrow: aaBusinessText(category.title.toUpperCase(), {
                    left: cx,
                    top: cy - 218,
                    width: width - 80,
                    fontSize: 20,
                    fontWeight: '900',
                    fill: accent,
                    textAlign: 'center',
                    elementKey,
                    categoryKey,
                    label: title,
                }),
                title: aaBusinessText(title, {
                    left: cx,
                    top: cy - 174,
                    width: width - 80,
                    fontFamily: 'Playfair Display',
                    fontSize: 48,
                    fontWeight: '700',
                    fill: '#111827',
                    textAlign: 'center',
                    elementKey,
                    categoryKey,
                    label: title,
                }),
                subtitle: subtitle ? aaBusinessText(subtitle, {
                    left: cx,
                    top: cy - 122,
                    width: width - 120,
                    fontSize: 24,
                    fontWeight: '600',
                    fill: '#64748b',
                    textAlign: 'center',
                    elementKey,
                    categoryKey,
                    label: title,
                }) : null,
            };
        }

        function addGenericBusinessProfileElement(categoryKey, elementKey, spec) {
            const title = spec?.title || 'Business Element';
            const base = aaBusinessCategoryBase(categoryKey, elementKey, title, spec?.body || '');
            const { category, cx, cy, accent } = base;
            const objects = [base.bg, base.eyebrow, base.title, base.subtitle];
            const whatsappUrl = 'https://wa.me/6281234567890?text=Halo%2C%20saya%20ingin%20konsultasi%20layanan.';

            const addText = (text, left, top, options = {}) => objects.push(aaBusinessText(text, {
                left,
                top,
                width: options.width || 260,
                fontFamily: options.fontFamily || 'Inter',
                fontSize: options.fontSize || 21,
                fontWeight: options.fontWeight || '700',
                fill: options.fill || '#334155',
                textAlign: options.textAlign || 'center',
                lineHeight: options.lineHeight || 1.22,
                customType: options.customType || 'text',
                link: options.link || '',
                copyText: options.copyText || '',
                copyFeedback: options.copyFeedback || '',
                underline: options.underline === true,
                elementKey,
                categoryKey,
                label: options.label || title,
            }));

            const addCard = (cardTitle, body, left, top, width = 200, height = 126) => {
                objects.push(aaBusinessRect({
                    left,
                    top,
                    width,
                    height,
                    fill: '#ffffff',
                    stroke: accent,
                    strokeWidth: 2,
                    elementKey,
                    categoryKey,
                    label: cardTitle,
                }));
                addText(cardTitle, left, top - 26, {
                    width: width - 28,
                    fontSize: 22,
                    fontWeight: '900',
                    fill: accent,
                    label: cardTitle,
                });
                addText(body, left, top + 30, {
                    width: width - 30,
                    fontSize: 18,
                    fontWeight: '600',
                    fill: '#475569',
                    lineHeight: 1.18,
                    label: cardTitle,
                });
            };

            const addPhoto = (label, left, top, width = 210, height = 160) => {
                objects.push(aaBusinessPhotoFrame({
                    left,
                    top,
                    width,
                    height,
                    elementKey,
                    categoryKey,
                    label,
                    galleryZoom: true,
                    galleryImageSrc: '',
                    galleryImageName: label,
                    stroke: accent,
                }));
                addText(label, left, top + (height / 2) + 34, {
                    width,
                    fontSize: 19,
                    fontWeight: '900',
                    fill: '#334155',
                    label,
                });
            };

            const addButton = (label, left = cx, top = cy + 156) => {
                objects.push(aaBusinessRect({
                    left,
                    top,
                    width: 360,
                    height: 74,
                    rx: 37,
                    ry: 37,
                    fill: accent,
                    stroke: accent,
                    elementKey,
                    categoryKey,
                    label,
                }));
                addText(label, left, top, {
                    width: 320,
                    fontSize: 27,
                    fontWeight: '900',
                    fill: '#ffffff',
                    customType: 'link-text',
                    link: whatsappUrl,
                    label,
                });
            };

            const layoutByElement = {
                service_packages: 'pricing',
                package_list: 'pricing',
                venue_packages: 'pricing',
                package_menu: 'pricing',
                shoot_packages: 'pricing',
                pricing: 'pricing',
                decor_portfolio: 'gallery',
                venue_gallery: 'gallery',
                food_gallery: 'gallery',
                photo_portfolio: 'gallery',
                portfolio_event: 'gallery',
                portfolio: 'gallery',
                featured_products: 'gallery',
                catalog: 'gallery',
                event_timeline: 'timeline',
                work_process: 'timeline',
                workflow: 'timeline',
                vendor_network: 'logo_grid',
                facilities: 'feature_grid',
                menu_showcase: 'feature_grid',
                service_list: 'feature_grid',
                agency_services: 'feature_grid',
                client_logo: 'logo_grid',
                team_profile: 'team',
                profile: 'profile',
                capacity: 'stats',
                service_area: 'location',
                store_location: 'location',
                location_map: 'location',
                test_food: 'schedule',
                visit_booking: 'schedule',
                available_date: 'schedule',
                consultation: 'cta',
                booking_decor: 'cta',
                order_cta: 'cta',
                booking_photo: 'cta',
                contact_cta: 'cta',
                proposal_cta: 'cta',
                promo: 'promo',
                case_study: 'case_study',
                editing_style: 'style_grid',
                theme_style: 'style_grid',
                venue_setup: 'style_grid',
                album_output: 'feature_grid',
                customer_review: 'review',
                client_review: 'review',
            };
            const layout = layoutByElement[elementKey] || 'feature_grid';

            if (layout === 'gallery') {
                [[-210, -18], [0, -18], [210, -18], [-105, 162], [105, 162]].forEach((item, index) => {
                    addPhoto(`${title} ${index + 1}`, cx + item[0], cy + item[1], index < 3 ? 180 : 240, index < 3 ? 132 : 126);
                });
            } else if (layout === 'pricing') {
                ['Basic', 'Premium', 'Signature'].forEach((item, index) => {
                    addCard(item, 'Benefit utama\nHarga mulai', cx - 220 + index * 220, cy + 72, 190, 178);
                });
            } else if (layout === 'timeline') {
                ['Brief', 'Produksi', 'Delivery'].forEach((item, index) => {
                    objects.push(aaBusinessRect({
                        left: cx - 220 + index * 220,
                        top: cy + 62,
                        width: 178,
                        height: 178,
                        rx: 89,
                        ry: 89,
                        fill: index === 1 ? accent : '#ffffff',
                        stroke: accent,
                        elementKey,
                        categoryKey,
                        label: item,
                    }));
                    addText(`${index + 1}`, cx - 220 + index * 220, cy + 20, {
                        width: 90,
                        fontSize: 34,
                        fontWeight: '900',
                        fill: index === 1 ? '#ffffff' : accent,
                        label: item,
                    });
                    addText(item, cx - 220 + index * 220, cy + 74, {
                        width: 150,
                        fontSize: 22,
                        fontWeight: '900',
                        fill: index === 1 ? '#ffffff' : '#111827',
                        label: item,
                    });
                    addText('Edit tahapan', cx - 220 + index * 220, cy + 112, {
                        width: 140,
                        fontSize: 17,
                        fontWeight: '600',
                        fill: index === 1 ? '#e0f2fe' : '#64748b',
                        label: item,
                    });
                });
            } else if (layout === 'logo_grid' || layout === 'feature_grid' || layout === 'style_grid') {
                const labels = layout === 'logo_grid'
                    ? ['Partner 1', 'Partner 2', 'Partner 3', 'Partner 4', 'Partner 5', 'Partner 6']
                    : ['Item 1', 'Item 2', 'Item 3', 'Item 4', 'Item 5', 'Item 6'];
                labels.forEach((item, index) => {
                    const col = index % 3;
                    const row = Math.floor(index / 3);
                    addCard(item, layout === 'style_grid' ? 'Deskripsi style' : 'Detail singkat', cx - 220 + col * 220, cy + 18 + row * 140, 180, 106);
                });
            } else if (layout === 'team') {
                [-220, 0, 220].forEach((offset, index) => {
                    addPhoto(`Team ${index + 1}`, cx + offset, cy + 20, 170, 178);
                    addText('Role / spesialis', cx + offset, cy + 176, {
                        width: 160,
                        fontSize: 17,
                        fontWeight: '600',
                        fill: '#64748b',
                        label: `Team ${index + 1}`,
                    });
                });
            } else if (layout === 'profile') {
                addPhoto(`Foto ${category.title}`, cx - 214, cy + 58, 220, 280);
                addText(`Nama ${category.title}`, cx + 112, cy - 30, {
                    width: 330,
                    fontFamily: 'Playfair Display',
                    fontSize: 40,
                    fontWeight: '700',
                    fill: '#111827',
                    textAlign: 'left',
                });
                addText('Tulis profil singkat, pengalaman, keunggulan layanan, dan cara kerja di sini.', cx + 112, cy + 58, {
                    width: 330,
                    fontSize: 25,
                    fontWeight: '600',
                    fill: '#475569',
                    textAlign: 'left',
                    lineHeight: 1.3,
                });
                addButton('Hubungi Sekarang', cx + 112, cy + 170);
            } else if (layout === 'stats') {
                ['Kapasitas', 'Area', 'Paket'].forEach((item, index) => {
                    addCard(item, index === 0 ? '300-1000 tamu' : (index === 1 ? 'Indoor / Outdoor' : 'Mulai dari'), cx - 220 + index * 220, cy + 70, 190, 178);
                });
            } else if (layout === 'location') {
                addText('Lokasi & Area Layanan', cx, cy - 28, {
                    width: 560,
                    fontSize: 34,
                    fontWeight: '900',
                    fill: '#111827',
                });
                addText('Tambahkan alamat, kota layanan, jam operasional, dan catatan kunjungan pelanggan.', cx, cy + 52, {
                    width: 580,
                    fontSize: 25,
                    fontWeight: '600',
                    fill: '#475569',
                    lineHeight: 1.3,
                });
                addText('Buka Google Maps', cx, cy + 150, {
                    width: 330,
                    fontSize: 28,
                    fontWeight: '900',
                    fill: accent,
                    customType: 'link-text',
                    link: 'https://maps.google.com/',
                    underline: true,
                });
            } else if (layout === 'schedule') {
                ['Tanggal tersedia', 'Jadwal konsultasi', 'Booking masuk'].forEach((item, index) => {
                    addCard(item, index === 0 ? '12, 18, 25 Okt' : (index === 1 ? 'Senin-Jumat' : 'Hubungi admin'), cx - 220 + index * 220, cy + 66, 190, 162);
                });
            } else if (layout === 'cta') {
                addText('Siap diskusi kebutuhanmu?', cx, cy - 36, {
                    width: 560,
                    fontSize: 36,
                    fontWeight: '900',
                    fill: '#111827',
                });
                addText(spec?.body || 'Klik tombol di bawah untuk konsultasi dan cek ketersediaan layanan.', cx, cy + 34, {
                    width: 560,
                    fontSize: 24,
                    fontWeight: '600',
                    fill: '#475569',
                    lineHeight: 1.3,
                });
                addButton('Konsultasi via WhatsApp', cx, cy + 138);
            } else if (layout === 'promo') {
                objects.push(aaBusinessRect({
                    left: cx,
                    top: cy + 44,
                    width: 560,
                    height: 210,
                    rx: 28,
                    ry: 28,
                    fill: accent,
                    stroke: accent,
                    elementKey,
                    categoryKey,
                    label: title,
                }));
                addText('PROMO SPESIAL', cx, cy - 16, {
                    width: 500,
                    fontSize: 28,
                    fontWeight: '900',
                    fill: '#ffffff',
                });
                addText('Diskon / bundling produk', cx, cy + 42, {
                    width: 500,
                    fontSize: 42,
                    fontWeight: '900',
                    fill: '#ffffff',
                });
                addText('Edit syarat, periode promo, dan benefit utama di sini.', cx, cy + 112, {
                    width: 500,
                    fontSize: 22,
                    fontWeight: '700',
                    fill: '#fff7ed',
                    lineHeight: 1.25,
                });
            } else if (layout === 'case_study') {
                addPhoto('Project Preview', cx - 210, cy + 58, 240, 250);
                addCard('Challenge', 'Masalah klien', cx + 110, cy - 14, 300, 104);
                addCard('Solution', 'Strategi kerja', cx + 110, cy + 118, 300, 104);
                addCard('Result', 'Hasil utama', cx + 110, cy + 250, 300, 104);
            } else if (layout === 'review') {
                addCard('Review Klien', '"Pelayanannya rapi dan hasilnya sesuai ekspektasi."', cx - 160, cy + 66, 270, 184);
                addCard('Rating', '5.0 / 5\nRekomendasi pelanggan', cx + 180, cy + 66, 250, 184);
            }

            aaBusinessAddObjects(objects, title);
        }

        function addMuaBusinessProfileElement(elementKey, spec) {
            const title = spec?.title || 'MUA Element';
            const base = aaBusinessSectionBase(elementKey, title, spec?.body || '');
            const { cx, cy } = base;
            const objects = [base.bg, base.eyebrow, base.title, base.subtitle];

            const addLabel = (text, left, top, width = 220) => objects.push(aaBusinessText(text, {
                left,
                top,
                width,
                fontSize: 21,
                fontWeight: '900',
                fill: '#334155',
                textAlign: 'center',
                elementKey,
                label: title,
            }));

            const addSmallCard = (cardTitle, body, left, top, width = 200, height = 126) => {
                objects.push(aaBusinessRect({
                    left,
                    top,
                    width,
                    height,
                    fill: '#fff7fb',
                    stroke: '#ffd6e7',
                    elementKey,
                    label: cardTitle,
                }));
                objects.push(aaBusinessText(cardTitle, {
                    left,
                    top: top - 26,
                    width: width - 28,
                    fontSize: 23,
                    fontWeight: '900',
                    fill: '#be185d',
                    textAlign: 'center',
                    elementKey,
                    label: cardTitle,
                }));
                objects.push(aaBusinessText(body, {
                    left,
                    top: top + 28,
                    width: width - 30,
                    fontSize: 18,
                    fill: '#475569',
                    textAlign: 'center',
                    lineHeight: 1.2,
                    elementKey,
                    label: cardTitle,
                }));
            };
            const whatsappUrl = 'https://wa.me/6281234567890?text=Halo%20Nama%20MUA%20Studio%2C%20saya%20ingin%20booking%20makeup.';

            if (elementKey === 'makeup_portfolio') {
                [[-168, -14], [168, -14], [-168, 166], [168, 166]].forEach((item, index) => {
                    objects.push(aaBusinessPhotoFrame({
                        left: cx + item[0],
                        top: cy + item[1],
                        width: 280,
                        height: 150,
                        elementKey,
                        label: `Portfolio ${index + 1}`,
                        galleryZoom: true,
                        galleryImageSrc: '',
                        galleryImageName: `Portfolio ${index + 1}`,
                    }));
                    addLabel(`Portfolio ${index + 1}`, cx + item[0], cy + item[1] + 104, 250);
                });
            } else if (elementKey === 'before_after') {
                objects.push(aaBusinessPhotoFrame({
                    left: cx - 176,
                    top: cy + 58,
                    width: 286,
                    height: 270,
                    elementKey,
                    label: 'Before',
                    galleryZoom: true,
                    galleryImageSrc: '',
                    galleryImageName: 'Before Makeup',
                }));
                objects.push(aaBusinessPhotoFrame({
                    left: cx + 176,
                    top: cy + 58,
                    width: 286,
                    height: 270,
                    elementKey,
                    label: 'After',
                    galleryZoom: true,
                    galleryImageSrc: '',
                    galleryImageName: 'After Makeup',
                }));
                addLabel('BEFORE', cx - 176, cy + 222, 260);
                addLabel('AFTER', cx + 176, cy + 222, 260);
            } else if (elementKey === 'artist_profile') {
                objects.push(aaBusinessPhotoFrame({
                    left: cx - 214,
                    top: cy + 58,
                    width: 220,
                    height: 280,
                    elementKey,
                    label: 'Foto Artist',
                    galleryZoom: true,
                    galleryImageSrc: '',
                    galleryImageName: 'Foto Artist',
                }));
                objects.push(aaBusinessText('Nama Artist', {
                    left: cx + 112,
                    top: cy - 28,
                    width: 330,
                    fontFamily: 'Playfair Display',
                    fontSize: 42,
                    fontWeight: '700',
                    fill: '#111827',
                    elementKey,
                    label: title,
                }));
                objects.push(aaBusinessText('Make Up Artist profesional untuk akad, reception, prewedding, dan special event.', {
                    left: cx + 112,
                    top: cy + 58,
                    width: 330,
                    fontSize: 25,
                    fill: '#475569',
                    lineHeight: 1.32,
                    elementKey,
                    label: title,
                }));
                objects.push(aaBusinessText('Edit profil, pengalaman, dan kontak di sini.', {
                    left: cx + 112,
                    top: cy + 166,
                    width: 330,
                    fontSize: 21,
                    fontWeight: '800',
                    fill: '#ec4899',
                    elementKey,
                    label: title,
                }));
            } else if (elementKey === 'team_mua') {
                [-220, 0, 220].forEach((offset, index) => {
                    objects.push(aaBusinessPhotoFrame({
                        left: cx + offset,
                        top: cy + 16,
                        width: 170,
                        height: 180,
                        elementKey,
                        label: `Team ${index + 1}`,
                        galleryZoom: true,
                        galleryImageSrc: '',
                        galleryImageName: `Team MUA ${index + 1}`,
                    }));
                    addLabel(`Artist ${index + 1}`, cx + offset, cy + 144, 150);
                    objects.push(aaBusinessText('Role / spesialis', {
                        left: cx + offset,
                        top: cy + 176,
                        width: 160,
                        fontSize: 17,
                        fill: '#64748b',
                        textAlign: 'center',
                        elementKey,
                        label: title,
                    }));
                });
            } else if (elementKey === 'booking_makeup') {
                objects.push(aaBusinessRect({
                    left: cx,
                    top: cy + 60,
                    width: 560,
                    height: 180,
                    fill: '#ec4899',
                    stroke: '#ec4899',
                    elementKey,
                    label: title,
                }));
                objects.push(aaBusinessText('Booking Makeup via WhatsApp', {
                    left: cx,
                    top: cy + 22,
                    width: 500,
                    fontSize: 34,
                    fontWeight: '900',
                    fill: '#ffffff',
                    textAlign: 'center',
                    customType: 'link-text',
                    link: whatsappUrl,
                    elementKey,
                    label: title,
                }));
                objects.push(aaBusinessText('Klik untuk konsultasi tanggal, paket, dan lokasi layanan.', {
                    left: cx,
                    top: cy + 82,
                    width: 480,
                    fontSize: 23,
                    fontWeight: '700',
                    fill: '#ffe4ef',
                    textAlign: 'center',
                    customType: 'link-text',
                    link: whatsappUrl,
                    elementKey,
                    label: title,
                }));
                objects.push(aaBusinessText('6281234567890', {
                    left: cx,
                    top: cy + 158,
                    width: 300,
                    fontSize: 22,
                    fontWeight: '900',
                    fill: '#be185d',
                    textAlign: 'center',
                    customType: 'copy-text',
                    copyText: '6281234567890',
                    copyFeedback: 'Nomor WhatsApp disalin',
                    elementKey,
                    label: 'Copy WhatsApp',
                }));
            } else if (elementKey === 'book_now_whatsapp') {
                const button = aaBusinessText('Book Now via WhatsApp', {
                    left: cx,
                    top: cy,
                    width: 560,
                    fontSize: 34,
                    fontWeight: '900',
                    fill: '#ec4899',
                    textAlign: 'center',
                    customType: 'link-text',
                    link: whatsappUrl,
                    aaBusinessSection: true,
                    aaBusinessCategory: 'mua',
                    aaBusinessElement: elementKey,
                    label: 'Book Now WhatsApp',
                });
                aaBusinessAddObjects([button], title);
                return;
            } else if (elementKey === 'mua_social_media') {
                if (typeof addSocialMediaElement === 'function') {
                    addSocialMediaElement();
                    const active = state.canvas?.getActiveObject?.();
                    if (active) {
                        aaBusinessMark(active, elementKey, 'Social Media MUA');
                        snapshot();
                    }
                    return;
                }
            } else if (elementKey === 'zoomable_photo') {
                aaBusinessAddObjects([aaBusinessPhotoFrame({
                    left: cx,
                    top: cy,
                    width: 520,
                    height: 310,
                    rx: 34,
                    ry: 34,
                    elementKey,
                    label: 'Foto Zoom',
                    galleryZoom: true,
                    galleryImageSrc: '',
                    galleryImageName: 'Foto Makeup',
                })], title);
                return;
            } else if (elementKey === 'service_location') {
                objects.push(aaBusinessText('Studio / Area Layanan', {
                    left: cx,
                    top: cy - 14,
                    width: 560,
                    fontSize: 34,
                    fontWeight: '900',
                    fill: '#111827',
                    textAlign: 'center',
                    elementKey,
                    label: title,
                }));
                objects.push(aaBusinessText('Jakarta, Bogor, Depok, Tangerang, Bekasi. Tambahkan alamat studio atau area layananmu di sini.', {
                    left: cx,
                    top: cy + 58,
                    width: 560,
                    fontSize: 25,
                    fill: '#475569',
                    textAlign: 'center',
                    lineHeight: 1.32,
                    elementKey,
                    label: title,
                }));
                objects.push(aaBusinessText('Buka Google Maps', {
                    left: cx,
                    top: cy + 158,
                    width: 320,
                    fontSize: 28,
                    fontWeight: '900',
                    fill: '#ec4899',
                    textAlign: 'center',
                    underline: true,
                    customType: 'link-text',
                    link: 'https://maps.google.com/',
                    elementKey,
                    label: title,
                }));
            } else if (elementKey === 'makeup_packages' || elementKey === 'bridal_packages') {
                const labels = elementKey === 'bridal_packages'
                    ? ['Akad', 'Reception', 'Full Day']
                    : ['Basic', 'Premium', 'Signature'];
                [-220, 0, 220].forEach((offset, index) => {
                    addSmallCard(labels[index], 'Benefit utama\nHarga mulai', cx + offset, cy + 72, 190, 178);
                });
            } else if (elementKey === 'makeup_style') {
                ['Natural Look', 'Bold Glam', 'Akad Soft', 'Reception'].forEach((item, index) => {
                    const col = index % 2;
                    const row = Math.floor(index / 2);
                    addSmallCard(item, 'Deskripsi style', cx - 150 + col * 300, cy + 20 + row * 142, 248, 110);
                });
            } else if (elementKey === 'products_used') {
                ['Base', 'Complexion', 'Eyes', 'Lips', 'Tools', 'Skin Prep'].forEach((item, index) => {
                    const col = index % 3;
                    const row = Math.floor(index / 3);
                    addSmallCard(item, 'Brand produk', cx - 220 + col * 220, cy + 24 + row * 138, 180, 104);
                });
            } else if (elementKey === 'available_date') {
                ['Tanggal tersedia', 'Booking masuk', 'Slot konsultasi'].forEach((item, index) => {
                    addSmallCard(item, index === 0 ? '12, 18, 25 Okt' : (index === 1 ? 'Hubungi admin' : 'Senin-Jumat'), cx - 220 + index * 220, cy + 66, 190, 162);
                });
            } else if (elementKey === 'client_review') {
                addSmallCard('Review Klien', '"Makeup tahan lama dan hasilnya flawless."', cx - 160, cy + 66, 270, 184);
                addSmallCard('Rating', '5.0 / 5\nWedding Makeup', cx + 180, cy + 66, 250, 184);
            } else {
                addSmallCard(title, spec?.body || 'Edit isi elemen sesuai kebutuhan.', cx, cy + 70, 520, 170);
            }

            aaBusinessAddObjects(objects, title);
        }

        function renderBusinessElementCategory(categoryKey) {
            const category = aaBusinessProfileElementCatalog[categoryKey] || aaBusinessProfileElementCatalog.mua;
            const categoryView = $('aaBusinessElementCategoryView');
            const detailView = $('aaBusinessElementDetailView');
            const title = $('aaBusinessElementCategoryTitle');
            const description = $('aaBusinessElementCategoryDescription');
            const icon = $('aaBusinessElementCategoryIcon');
            const grid = $('aaBusinessElementGrid');
            const note = $('aaBusinessElementCategoryNote');
            if (!categoryView || !detailView || !grid) return;

            categoryView.hidden = true;
            detailView.hidden = false;
            if (title) title.textContent = category.title;
            if (description) description.textContent = category.description;
            if (icon) icon.setAttribute('data-lucide', category.icon || 'sparkles');
            if (note) note.textContent = category.note || '';
            grid.innerHTML = '';

            category.elements.forEach(item => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'aa-tool-btn';
                button.dataset.aaBusinessElement = item.key;
                button.dataset.aaBusinessCategory = categoryKey;

                const buttonIcon = document.createElement('i');
                buttonIcon.className = 'aa-lucide-icon';
                buttonIcon.setAttribute('data-lucide', item.icon || 'plus');
                buttonIcon.setAttribute('aria-hidden', 'true');
                const label = document.createElement('span');
                label.textContent = item.title;
                button.append(buttonIcon, label);
                grid.appendChild(button);
            });

            document.querySelectorAll('[data-aa-business-category]').forEach(button => {
                button.classList.toggle('is-active', button.dataset.aaBusinessCategory === categoryKey);
            });
            window.lucide?.createIcons?.();
        }

        function showBusinessElementCategoryList() {
            const categoryView = $('aaBusinessElementCategoryView');
            const detailView = $('aaBusinessElementDetailView');
            if (categoryView) categoryView.hidden = false;
            if (detailView) detailView.hidden = true;
            document.querySelectorAll('[data-aa-business-category]').forEach(button => {
                button.classList.remove('is-active');
            });
        }

        function setupBusinessProfileElements() {
            $('aaBusinessElementsPanel')?.addEventListener('click', event => {
                const target = event.target instanceof Element ? event.target : null;
                const elementButton = target?.closest('[data-aa-business-element]');
                if (elementButton) {
                    addBusinessProfileElement(
                        elementButton.dataset.aaBusinessCategory || 'mua',
                        elementButton.dataset.aaBusinessElement || ''
                    );
                    return;
                }

                const categoryButton = target?.closest('.aa-business-element-category-btn[data-aa-business-category]');
                if (categoryButton) {
                    renderBusinessElementCategory(categoryButton.dataset.aaBusinessCategory || 'mua');
                }
            });
            $('aaBusinessElementBackBtn')?.addEventListener('click', showBusinessElementCategoryList);
        }

        const aaTextSnippets = [{
                category: 'Pembuka',
                title: 'Syukur dan undangan',
                text: 'Dengan penuh rasa syukur, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan menjadi bagian dari hari bahagia kami.',
                style: 'paragraph'
            },
            {
                category: 'Pembuka',
                title: 'Kehormatan bagi kami',
                text: 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.',
                style: 'paragraph'
            },
            {
                category: 'Pembuka',
                title: 'Tanpa mengurangi hormat',
                text: 'Tanpa mengurangi rasa hormat, izinkan kami menyampaikan kabar bahagia sekaligus mengundang Bapak/Ibu/Saudara/i dalam acara kami.',
                style: 'paragraph'
            },
            {
                category: 'Pernikahan',
                title: 'Mohon doa restu',
                text: 'Atas rahmat Tuhan Yang Maha Esa, kami bermaksud menyelenggarakan pernikahan kami. Mohon doa restu agar langkah ini senantiasa diberkahi.',
                style: 'paragraph'
            },
            {
                category: 'Pernikahan',
                title: 'Dua hati',
                text: 'Dua hati, dua keluarga, satu janji untuk melangkah bersama dalam kasih dan kebaikan.',
                style: 'quote'
            },
            {
                category: 'Pernikahan',
                title: 'Hari bahagia',
                text: 'Kehadiran dan doa restu Bapak/Ibu/Saudara/i akan menjadi bagian terindah dalam hari bahagia kami.',
                style: 'paragraph'
            },
            {
                category: 'Doa',
                title: 'Doa kebaikan',
                text: 'Semoga acara ini berjalan lancar, penuh keberkahan, dan membawa kebahagiaan bagi kita semua.',
                style: 'paragraph'
            },
            {
                category: 'Doa',
                title: 'Doa keluarga',
                text: 'Semoga kasih, kesabaran, dan kebaikan selalu menyertai langkah kami dalam membangun keluarga yang harmonis.',
                style: 'paragraph'
            },
            {
                category: 'Doa',
                title: 'Doa umum',
                text: 'Semoga setiap niat baik dimudahkan, setiap langkah dikuatkan, dan setiap doa baik dikabulkan.',
                style: 'quote'
            },
            {
                category: 'Islam',
                title: 'Bismillah',
                text: 'Bismillahirrahmanirrahim\nDengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara kami.',
                style: 'paragraph'
            },
            {
                category: 'Islam',
                title: 'Sakinah',
                text: 'Semoga Allah SWT menjadikan pernikahan ini sebagai jalan menuju keluarga yang sakinah, mawaddah, warahmah.',
                style: 'paragraph'
            },
            {
                category: 'Kristen',
                title: 'Kasih dan berkat',
                text: 'Dengan penuh sukacita dan rasa syukur atas kasih Tuhan, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam hari bahagia kami.',
                style: 'paragraph'
            },
            {
                category: 'Kristen',
                title: 'Berkat Tuhan',
                text: 'Kiranya kasih dan berkat Tuhan senantiasa menyertai setiap langkah kami dalam perjalanan hidup yang baru.',
                style: 'paragraph'
            },
            {
                category: 'Hindu',
                title: 'Om Swastyastu',
                text: 'Om Swastyastu\nDengan penuh rasa syukur, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu pada acara kami.',
                style: 'paragraph'
            },
            {
                category: 'Universal',
                title: 'Cinta bertumbuh',
                text: 'Cinta yang baik adalah cinta yang bertumbuh bersama waktu, saling menjaga, dan saling menguatkan.',
                style: 'quote'
            },
            {
                category: 'Universal',
                title: 'Awal perjalanan',
                text: 'Hari ini bukan akhir dari cerita, melainkan awal dari perjalanan yang kami pilih untuk dijalani bersama.',
                style: 'quote'
            },
            {
                category: 'Save Date',
                title: 'Simpan tanggal',
                text: 'Save the Date\nKami menantikan kehadiran Anda di hari istimewa kami.',
                style: 'heading'
            },
            {
                category: 'Save Date',
                title: 'Jangan lewatkan',
                text: 'Simpan tanggalnya dan jadilah bagian dari momen bahagia kami.',
                style: 'paragraph'
            },
            {
                category: 'RSVP',
                title: 'Konfirmasi hadir',
                text: 'Mohon konfirmasi kehadiran Bapak/Ibu/Saudara/i melalui tombol RSVP yang tersedia.',
                style: 'paragraph'
            },
            {
                category: 'RSVP',
                title: 'Bantu persiapan',
                text: 'Konfirmasi kehadiran Anda akan sangat membantu kami dalam mempersiapkan acara dengan lebih baik.',
                style: 'paragraph'
            },
            {
                category: 'Gift',
                title: 'Doa adalah hadiah',
                text: 'Doa restu Bapak/Ibu/Saudara/i merupakan hadiah terindah bagi kami.',
                style: 'paragraph'
            },
            {
                category: 'Gift',
                title: 'Tanda kasih',
                text: 'Bagi yang ingin memberikan tanda kasih, kami telah menyediakan informasi gift pada halaman ini.',
                style: 'paragraph'
            },
            {
                category: 'Penutup',
                title: 'Terima kasih',
                text: 'Atas kehadiran dan doa restu Bapak/Ibu/Saudara/i, kami mengucapkan terima kasih.',
                style: 'paragraph'
            },
            {
                category: 'Penutup',
                title: 'Sampai jumpa',
                text: 'Sampai jumpa di hari bahagia kami. Kehadiran Anda sangat berarti bagi kami.',
                style: 'paragraph'
            },
            {
                category: 'Quotes',
                title: 'Bersama',
                text: 'Bersama bukan berarti selalu mudah, tetapi selalu ada alasan untuk saling memilih kembali.',
                style: 'quote'
            },
            {
                category: 'Quotes',
                title: 'Rumah',
                text: 'Rumah bukan hanya tempat pulang, tetapi seseorang yang membuat hati merasa tenang.',
                style: 'quote'
            },
            {
                category: 'Ulang Tahun',
                title: 'Syukur usia',
                text: 'Dengan penuh rasa syukur, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam perayaan hari istimewa ini.',
                style: 'paragraph'
            },
            {
                category: 'Aqiqah',
                title: 'Anugerah buah hati',
                text: 'Sebagai ungkapan rasa syukur atas anugerah buah hati kami, dengan bahagia kami mengundang Bapak/Ibu/Saudara/i untuk hadir.',
                style: 'paragraph'
            }
        ];

        let aaActiveSnippetCategory = 'Semua';

        function aaSnippetCategories() {
            return ['Semua', ...Array.from(new Set(aaTextSnippets.map(item => item.category)))];
        }

        function aaSnippetStyleConfig(style = 'paragraph') {
            if (style === 'heading') {
                return {
                    width: 720,
                    fontFamily: 'Playfair Display',
                    fontSize: 64,
                    fill: '#0f172a',
                    textAlign: 'center',
                    lineHeight: 1.12,
                    customType: 'heading',
                };
            }
            if (style === 'quote') {
                return {
                    width: 660,
                    fontFamily: 'Cormorant Garamond',
                    fontSize: 48,
                    fill: '#334155',
                    textAlign: 'center',
                    fontStyle: 'italic',
                    lineHeight: 1.18,
                    customType: 'text',
                };
            }

            return {
                width: 680,
                fontFamily: 'Inter',
                fontSize: 34,
                fill: '#334155',
                textAlign: 'center',
                lineHeight: 1.24,
                customType: 'text',
            };
        }

        function addTextSnippet(snippet = {}) {
            if (!state.canvas || !window.fabric) return;

            const config = aaSnippetStyleConfig(snippet.style);
            const text = new fabric.Textbox(String(snippet.text || '').trim() || 'Tulis teks di sini', {
                ...config,
                originX: 'center',
                originY: 'center',
                name: snippet.title || 'Kalimat',
                aaSource: 'snippet-library',
                objectCaching: false,
            });

            aaApplyTextboxResizeControls(text);
            centerObject(text);
            syncInspector();
            setStatus(`Kalimat "${snippet.title || 'siap pakai'}" ditambahkan`);
        }

        function renderSnippetCategories() {
            const wrap = document.getElementById('aaSnippetCategoryList');
            if (!wrap) return;
            wrap.innerHTML = '';
            aaSnippetCategories().forEach(category => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'aa-snippet-category-btn';
                button.classList.toggle('is-active', category === aaActiveSnippetCategory);
                button.textContent = category;
                button.addEventListener('click', () => {
                    aaActiveSnippetCategory = category;
                    renderSnippetCategories();
                    renderSnippetList();
                });
                wrap.appendChild(button);
            });
        }

        function renderSnippetList() {
            const list = document.getElementById('aaSnippetList');
            if (!list) return;
            const query = String(document.getElementById('aaSnippetSearchInput')?.value || '').trim().toLowerCase();
            const snippets = aaTextSnippets.filter(snippet => {
                const matchesCategory = aaActiveSnippetCategory === 'Semua' || snippet.category === aaActiveSnippetCategory;
                const haystack = `${snippet.category} ${snippet.title} ${snippet.text}`.toLowerCase();
                return matchesCategory && (!query || haystack.includes(query));
            });

            list.innerHTML = '';
            if (!snippets.length) {
                const empty = document.createElement('div');
                empty.className = 'aa-snippet-empty';
                empty.textContent = 'Kalimat tidak ditemukan.';
                list.appendChild(empty);
                return;
            }

            snippets.forEach(snippet => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'aa-snippet-item';
                button.innerHTML = `
                    <span class="aa-snippet-item-title"><span>${aaSnippetEscapeHtml(snippet.category)} - ${aaSnippetEscapeHtml(snippet.title)}</span><i class="fa fa-plus" aria-hidden="true"></i></span>
                    <span class="aa-snippet-item-text">${aaSnippetEscapeHtml(snippet.text).replace(/\n/g, '<br>')}</span>
                `;
                button.addEventListener('click', () => addTextSnippet(snippet));
                list.appendChild(button);
            });
        }

        function aaSnippetEscapeHtml(value = '') {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function bindSnippetDrawer() {
            const input = document.getElementById('aaSnippetSearchInput');
            renderSnippetCategories();
            renderSnippetList();
            input?.addEventListener('input', renderSnippetList);
        }

        function addLinkText() {
            const text = new fabric.Textbox('Buka Lokasi / Link', {
                width: 520,
                fontFamily: 'Inter',
                fontSize: 42,
                fill: '#0f766e',
                textAlign: 'center',
                underline: true,
                lineHeight: 1.14,
                customType: 'link-text',
                link: 'https://maps.google.com/',
                originX: 'center',
                originY: 'center'
            });

            aaApplyTextboxResizeControls(text);
            centerObject(text);
            syncInspector();
            setStatus('Teks link ditambahkan. Atur URL di panel Interaction.');
        }

        function addCopyText() {
            const text = new fabric.Textbox('Copy Info', {
                width: 460,
                fontFamily: 'Inter',
                fontSize: 42,
                fill: '#111827',
                textAlign: 'center',
                lineHeight: 1.14,
                customType: 'copy-text',
                copyText: 'Teks yang akan dicopy',
                copyFeedback: 'Teks berhasil dicopy',
                originX: 'center',
                originY: 'center'
            });

            aaApplyTextboxResizeControls(text);
            centerObject(text);
            syncInspector();
            setStatus('Elemen copy ditambahkan. Atur teks yang dicopy di panel Interaction.');
        }

        function guestNamePlaceholder(template) {
            const source = String(template || 'Kepada Yth.\n{{guest_name}}');
            return source.replace(/\{\{\s*guest_name\s*\}\}/gi, 'Nama Tamu');
        }

        function aaIsGuestNamePlaceholderText(value) {
            const text = String(value || '').trim();
            const normalized = text.replace(/\s+/g, ' ');
            return /\{\{\s*guest_name\s*\}\}/i.test(text) ||
                /\bNama\s+Tamu\b/i.test(text) ||
                /^(Kepada\s+(Yth\.?|Yang\s+Terhormat)\s*)?Tamu\s+Undangan$/i.test(normalized);
        }

        function aaGuestNameTemplateFromText(value) {
            const source = String(value || 'Kepada Yth.\nNama Tamu');
            const template = source
                .replace(/\{\{\s*guest_name\s*\}\}/gi, '{{guest_name}}')
                .replace(/Nama Tamu|Tamu Undangan/gi, '{{guest_name}}');
            return /\{\{\s*guest_name\s*\}\}/i.test(template) ? template : 'Kepada Yth.\n{{guest_name}}';
        }

        function isGuestNameObject(object) {
            return object && (object.isGuestName === true || object.customType === 'guest_name' || object
                .dynamicKey === 'guest_name');
        }

        function aaFindGuestNameJsonTextObject(object) {
            if (!object || typeof object !== 'object') return null;
            if (['i-text', 'textbox', 'text'].includes(object.type) && aaIsGuestNamePlaceholderText(object.text)) {
                return object;
            }

            const children = Array.isArray(object.objects) ? object.objects : [];
            return children.find(child => child?.name === 'guest-name-text' && aaIsGuestNamePlaceholderText(child.text)) ||
                children.find(child => ['i-text', 'textbox', 'text'].includes(child?.type) &&
                    aaIsGuestNamePlaceholderText(child.text)) ||
                children.map(aaFindGuestNameJsonTextObject).find(Boolean) ||
                null;
        }

        function aaRepairLegacyGuestNameJsonObject(object) {
            if (!object || typeof object !== 'object') return false;

            const target = aaFindGuestNameJsonTextObject(object);
            const shouldRepair = isGuestNameObject(object) || Boolean(target);
            if (!shouldRepair) return false;

            const textSource = object.templateText || target?.templateText || target?.text || object.text ||
                'Kepada Yth.\nNama Tamu';
            const templateText = aaGuestNameTemplateFromText(textSource);

            object.customType = 'guest_name';
            object.isGuestName = true;
            object.dynamicKey = 'guest_name';
            object.templateText = templateText;
            object.placeholder = guestNamePlaceholder(templateText);
            object.showCloseButton = false;
            object.glassCard = false;

            if (Array.isArray(object.objects)) {
                object.objects = object.objects.filter(child => !guestNameDecorativeChildNames.includes(child?.name));
            }

            if (target) {
                target.name = target.name || 'guest-name-text';
                target.customType = 'guest_name';
                target.isGuestName = true;
                target.dynamicKey = 'guest_name';
                target.templateText = templateText;
                target.placeholder = guestNamePlaceholder(templateText);
                target.showCloseButton = false;
                target.glassCard = false;
                if (target.backgroundColor) target.backgroundColor = '';
            }

            return true;
        }

        function aaFindGuestNameFabricTextObject(object) {
            if (!object) return null;
            if (isFabricTextObject(object) && aaIsGuestNamePlaceholderText(object.text)) return object;

            const children = typeof object.getObjects === 'function' ? object.getObjects() : [];
            return children.find(child => child?.name === 'guest-name-text' && aaIsGuestNamePlaceholderText(child.text)) ||
                children.find(child => isFabricTextObject(child) && aaIsGuestNamePlaceholderText(child.text)) ||
                children.map(aaFindGuestNameFabricTextObject).find(Boolean) ||
                null;
        }

        function aaRepairLegacyGuestNameObject(object) {
            if (!object) return false;

            const target = aaFindGuestNameFabricTextObject(object);
            const shouldRepair = isGuestNameObject(object) || Boolean(target);
            if (!shouldRepair) return false;

            const textSource = object.templateText || target?.templateText || target?.text || object.text ||
                'Kepada Yth.\nNama Tamu';
            const templateText = aaGuestNameTemplateFromText(textSource);

            object.set({
                customType: 'guest_name',
                isGuestName: true,
                dynamicKey: 'guest_name',
                templateText: templateText,
                placeholder: guestNamePlaceholder(templateText),
                showCloseButton: false,
                glassCard: false,
            });

            if (target) {
                target.set({
                    name: target.name || 'guest-name-text',
                    customType: 'guest_name',
                    isGuestName: true,
                    dynamicKey: 'guest_name',
                    templateText: templateText,
                    placeholder: guestNamePlaceholder(templateText),
                    showCloseButton: false,
                    glassCard: false,
                    backgroundColor: '',
                });
            }

            cleanupGuestNameObject(object);
            return true;
        }

        function aaRepairLegacyGuestNameObjects(canvas) {
            if (!canvas || typeof canvas.getObjects !== 'function') return false;

            let repaired = false;
            const visit = object => {
                if (!object) return;
                repaired = aaRepairLegacyGuestNameObject(object) || repaired;
                if (typeof object.getObjects === 'function') {
                    object.getObjects().forEach(visit);
                }
            };

            canvas.getObjects().forEach(visit);
            if (repaired) {
                canvas.requestRenderAll();
            }
            return repaired;
        }

        function getGuestNameTextObject(object) {
            if (!isGuestNameObject(object)) return null;
            if (isFabricTextObject(object)) return object;
            const children = typeof object.getObjects === 'function' ? object.getObjects() : [];
            return children.find(child => child.name === 'guest-name-text') || children.find(isFabricTextObject) ||
                null;
        }

        function getGuestNameChild(object, name) {
            const children = object && typeof object.getObjects === 'function' ? object.getObjects() : [];
            return children.find(child => child.name === name) || null;
        }

        const guestNameDecorativeChildNames = [
            'guest-name-glass-card',
            'guest-name-inner-glow',
            'guest-name-edge-reflection',
            'guest-name-top-sheen',
            'guest-name-close-circle',
            'guest-name-close-text',
        ];

        function cleanupGuestNameObject(object) {
            if (!isGuestNameObject(object)) return object;

            object.set({
                customType: 'guest_name',
                isGuestName: true,
                dynamicKey: 'guest_name',
                showCloseButton: false,
                glassCard: false,
            });

            if (typeof object.getObjects === 'function' && typeof object.remove === 'function') {
                object.getObjects().slice().forEach(child => {
                    if (guestNameDecorativeChildNames.includes(child?.name)) {
                        object.remove(child);
                    }
                });
            }

            const text = getGuestNameTextObject(object);
            if (text) {
                text.set({
                    shadow: null,
                    backgroundColor: '',
                });
                text.dirty = true;
                if (typeof text.initDimensions === 'function') {
                    text.initDimensions();
                }
            } else if (isFabricTextObject(object)) {
                object.set({
                    shadow: null,
                    backgroundColor: '',
                });
            }

            object.dirty = true;
            object.setCoords();
            return object;
        }

        function colorToRgba(color, alpha) {
            try {
                const parsed = new fabric.Color(color || '#ffffff').getSource();
                return `rgba(${parsed[0]},${parsed[1]},${parsed[2]},${alpha})`;
            } catch (error) {
                return `rgba(255,255,255,${alpha})`;
            }
        }

        function setGuestNameGlassColors(object, values) {
            if (!isGuestNameObject(object)) return;
            const next = {};
            if (Object.prototype.hasOwnProperty.call(values, 'glassBackgroundColor')) {
                next.glassBackgroundColor = values.glassBackgroundColor;
                const base = getGuestNameChild(object, 'guest-name-glass-card');
                const glow = getGuestNameChild(object, 'guest-name-inner-glow');
                const edge = getGuestNameChild(object, 'guest-name-edge-reflection');
                const sheen = getGuestNameChild(object, 'guest-name-top-sheen');
                if (base) base.set('fill', colorToRgba(values.glassBackgroundColor, 0.03));
                if (glow) glow.set('stroke', colorToRgba(values.glassBackgroundColor, 0.34));
                if (edge) edge.set('fill', colorToRgba(values.glassBackgroundColor, 0.16));
                if (sheen) sheen.set('fill', colorToRgba(values.glassBackgroundColor, 0.12));
            }
            if (Object.prototype.hasOwnProperty.call(values, 'closeButtonColor')) {
                next.closeButtonColor = values.closeButtonColor;
                const closeCircle = getGuestNameChild(object, 'guest-name-close-circle');
                const closeText = getGuestNameChild(object, 'guest-name-close-text');
                if (closeCircle) {
                    closeCircle.set({
                        fill: colorToRgba(values.closeButtonColor, 0.16),
                        stroke: colorToRgba(values.closeButtonColor, 0.42),
                    });
                }
                if (closeText) closeText.set('fill', values.closeButtonColor);
            }
            object.set(next);
            object.dirty = true;
            object.setCoords();
        }

        function setGuestNameTemplateObject(object, templateText) {
            if (!isGuestNameObject(object)) return;
            cleanupGuestNameObject(object);
            const nextText = guestNamePlaceholder(templateText);
            const text = getGuestNameTextObject(object);
            object.set({
                customType: 'guest_name',
                isGuestName: true,
                dynamicKey: 'guest_name',
                templateText: templateText,
                placeholder: nextText,
                showCloseButton: false,
                glassCard: false,
            });
            if (text) {
                text.set('text', nextText);
                text.dirty = true;
                if (typeof text.initDimensions === 'function') {
                    text.initDimensions();
                }
            } else if (isFabricTextObject(object)) {
                object.set('text', nextText);
            }
            object.dirty = true;
            object.setCoords();
        }

        function addGuestNameText() {
            const templateText = 'Kepada Yth.\n{{guest_name}}';
            const width = 400;
            const text = new fabric.Textbox(guestNamePlaceholder(templateText), {
                name: 'guest-name-text',
                width: width,
                fontFamily: 'Cormorant Garamond',
                fontSize: 54,
                fill: '#ffffff',
                fontWeight: 'bold',
                textAlign: 'center',
                lineHeight: 1.18,
                customType: 'guest_name',
                isGuestName: true,
                dynamicKey: 'guest_name',
                templateText: templateText,
                placeholder: guestNamePlaceholder(templateText),
                showCloseButton: false,
                glassCard: false,
                objectCaching: false,
            });
            aaApplyTextboxResizeControls(text);
            centerObject(text);
            syncInspector();
            setStatus('Elemen Nama Tamu ditambahkan. Atur formatnya di panel Nama Tamu Dinamis.');
        }

        function isGuestbookObject(object) {
            return object && [
                'guest-name-input',
                'guest-attendance-select',
                'guest-message-textarea',
                'guest-sticker-picker',
                'guest-submit-button',
                'guest-comment-list',
            ].includes(object.customType);
        }

        function isInteractiveObject(object) {
            return object && [
                'music-player',
                'scroll-next-button',
                'countdown-timer',
                'photo-gallery',
                'youtube-video',
                'opening-button',
            ].includes(object.customType);
        }

        function getNamedGroupText(object) {
            const children = object && object.getObjects ? object.getObjects() : [];
            return children.find(child => child.name === 'interactive-text') || children.find(child =>
                isFabricTextObject(child));
        }

        function getInteractiveBox(object) {
            const children = object && object.getObjects ? object.getObjects() : [];
            return children.find(child => child.name === 'interactive-box') || children.find(child => child.type ===
                'rect') || null;
        }

        function getInteractiveBoxes(object) {
            const children = object && object.getObjects ? object.getObjects() : [];
            return children.filter(child => child.name === 'interactive-box' || child.type === 'rect');
        }

        function getOpeningButtonParts(object) {
            return {
                box: getInteractiveBox(object),
                text: getNamedGroupText(object),
            };
        }

        function getCountdownTextObjects(object) {
            const children = object && object.getObjects ? object.getObjects() : [];
            return children.filter(child => child.name === 'countdown-value' || child.name === 'countdown-label' ||
                isFabricTextObject(child));
        }

        function getGuestbookObjectParts(object) {
            const children = object && object.getObjects ? object.getObjects() : [];
            return {
                box: children.find(child => child.name === 'guestbook-box') || children.find(child => child.type ===
                    'rect'),
                text: children.find(child => child.name === 'guestbook-text') || children.find(child =>
                    isFabricTextObject(child)),
            };
        }

        function addGuestbookElement(kind) {
            const specs = {
                name: {
                    customType: 'guest-name-input',
                    label: 'Nama',
                    placeholder: 'Nama',
                    fieldName: 'guest_name',
                    width: 640,
                    height: 92,
                    maxLength: 120,
                    required: true,
                },
                attendance: {
                    customType: 'guest-attendance-select',
                    label: 'Kehadiran',
                    placeholder: 'Pilih Kehadiran',
                    fieldName: 'attendance',
                    width: 640,
                    height: 92,
                    options: ['hadir:Hadir', 'tidak_hadir:Tidak hadir', 'ragu:Ragu'],
                    required: true,
                },
                message: {
                    customType: 'guest-message-textarea',
                    label: 'Komentar / ucapan',
                    placeholder: 'Tulis ucapan...',
                    fieldName: 'message',
                    width: 640,
                    height: 220,
                    maxLength: 800,
                    required: true,
                },
                sticker: {
                    customType: 'guest-sticker-picker',
                    label: 'Stiker',
                    placeholder: 'Pilih Stiker',
                    fieldName: 'sticker',
                    width: 320,
                    height: 82,
                    stickerSource: 'default',
                },
                submit: {
                    customType: 'guest-submit-button',
                    label: 'Kirim Ucapan',
                    placeholder: 'Kirim Ucapan',
                    buttonText: 'Kirim Ucapan',
                    width: 420,
                    height: 92,
                    fill: '#0f766e',
                    stroke: 'transparent',
                    strokeWidth: 0,
                    textFill: '#ffffff',
                },
                list: {
                    customType: 'guest-comment-list',
                    label: 'Daftar Ucapan',
                    placeholder: 'Daftar Ucapan Akan Tampil Di Sini',
                    width: 720,
                    height: 360,
                    maxLength: 380,
                },
            };
            const spec = specs[kind];
            if (!spec) return;

            const box = new fabric.Rect({
                name: 'guestbook-box',
                left: -spec.width / 2,
                top: -spec.height / 2,
                width: spec.width,
                height: spec.height,
                rx: 18,
                ry: 18,
                fill: spec.fill || '#ffffff',
                stroke: spec.stroke || '#cbd5e1',
                strokeWidth: 3,
            });
            const text = new fabric.Textbox(spec.placeholder, {
                name: 'guestbook-text',
                left: spec.customType === 'guest-submit-button' ? -spec.width / 2 : -spec.width / 2 + 26,
                top: -spec.height / 2 + Math.max(18, (spec.height - 42) / 2),
                width: spec.customType === 'guest-submit-button' ? spec.width : spec.width - 52,
                fontFamily: 'Inter',
                fontSize: spec.customType === 'guest-comment-list' ? 34 : 36,
                fontWeight: spec.customType === 'guest-submit-button' ? 'bold' : 'normal',
                fill: spec.textFill || '#334155',
                textAlign: spec.customType === 'guest-submit-button' ? 'center' : 'left',
                selectable: false,
                evented: false,
            });
            const group = new fabric.Group([box, text], {
                customType: spec.customType,
                label: spec.label,
                placeholder: spec.placeholder,
                fieldName: spec.fieldName || '',
                options: spec.options || [],
                buttonText: spec.buttonText || spec.placeholder,
                required: spec.required === true,
                stickerSource: spec.stickerSource || '',
                maxLength: spec.maxLength || 0,
                formGroupId: 'guestbook-main',
                guestbookRole: spec.customType,
            });
            centerObject(group);
            syncInspector();
            setStatus('Elemen ucapan ditambahkan');
        }

        function createLabeledBox(label, icon, options = {}) {
            const width = options.width || 460;
            const height = options.height || 110;
            const fill = options.fill || '#ffffff';
            const stroke = options.stroke || '#cbd5e1';
            const textFill = options.textFill || '#334155';
            const box = new fabric.Rect({
                name: 'interactive-box',
                left: -width / 2,
                top: -height / 2,
                width,
                height,
                rx: options.radius ?? 22,
                ry: options.radius ?? 22,
                fill,
                stroke,
                strokeWidth: 3,
            });
            const iconText = new fabric.Text(icon || '', {
                name: 'interactive-icon',
                left: -width / 2 + 34,
                top: 0,
                originY: 'center',
                fontFamily: 'Arial',
                fontSize: options.iconSize || 34,
                fill: textFill,
                selectable: false,
                evented: false,
            });
            const text = new fabric.Textbox(label, {
                name: 'interactive-text',
                left: -width / 2 + (icon ? 86 : 28),
                top: 0,
                originY: 'center',
                width: width - (icon ? 120 : 56),
                fontFamily: 'Inter',
                fontSize: options.fontSize || 34,
                fontWeight: options.fontWeight || 'bold',
                fill: textFill,
                selectable: false,
                evented: false,
            });
            return new fabric.Group(icon ? [box, iconText, text] : [box, text], {
                label,
                placeholder: label,
                objectCaching: false,
                ...options.props,
            });
        }

        function countdownPreviewValues(object) {
            const countdownTime = object?.countdownTime || '00:00';
            let target = new Date(object?.countdownTarget || ((object?.countdownDate || '') + 'T' +
                countdownTime + ':00')).getTime();
            if (!Number.isFinite(target)) target = Date.now();
            const diff = Math.max(0, target - Date.now());
            return [
                Math.floor(diff / 86400000),
                Math.floor((diff % 86400000) / 3600000),
                Math.floor((diff % 3600000) / 60000),
                Math.floor((diff % 60000) / 1000),
            ].map(value => String(value || 0).padStart(2, '0'));
        }

        function formatCountdownDateInput(value) {
            const digits = String(value || '').replace(/\D/g, '').slice(0, 8);
            if (digits.length <= 4) return digits;
            if (digits.length <= 6) return `${digits.slice(0, 4)}-${digits.slice(4)}`;
            return `${digits.slice(0, 4)}-${digits.slice(4, 6)}-${digits.slice(6)}`;
        }

        function isCompleteCountdownDate(value) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(String(value || ''))) return false;
            const [year, month, day] = String(value).split('-').map(Number);
            const date = new Date(year, month - 1, day);
            return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
        }

        function parseCountdownDate(value) {
            if (!isCompleteCountdownDate(value)) return null;
            const [year, month, day] = String(value).split('-').map(Number);
            return new Date(year, month - 1, day);
        }

        function formatCountdownDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function getCountdownPickerDate() {
            const source = state.countdownPickerAnchor === els.aaCountdownContextDateInput ?
                els.aaCountdownContextDateInput : els.aaCountdownDateInput;
            return parseCountdownDate(source?.value) || new Date();
        }

        function setActiveCountdownDate(dateValue) {
            const active = state.canvas.getActiveObject();
            if (!active || active.customType !== 'countdown-timer') return;
            const time = els.aaCountdownContextToolbar?.classList.contains('is-visible') ?
                (els.aaCountdownContextTimeInput?.value || active.countdownTime || '00:00') :
                (els.aaCountdownTimeInput?.value || active.countdownTime || '00:00');
            active.set({
                countdownDate: dateValue,
                countdownTime: time,
                countdownTarget: isCompleteCountdownDate(dateValue) ? `${dateValue}T${time}:00` : '',
            });
            refreshCountdownPreviewObject(active);
        }

        function closeCountdownDatePicker() {
            els.aaCountdownDatePicker?.classList.remove('is-open');
            state.countdownPickerAnchor = null;
        }

        function closeToolbarPopovers(except = '') {
            if (except !== 'object-context') closeObjectContextMenu();
            if (except !== 'flip') closeContextFlipPopover();
            if (except !== 'stroke') closeContextStrokePopover();
            if (except !== 'radius') closeContextRadiusPopover();
            if (except !== 'image-outline') closeImageOutlinePopover();
            if (except !== 'image-effects') closeImageEffectsPopover();
            if (except !== 'image-frame') closeImageFramePopover();
            if (except !== 'transparency') closeContextTransparencyPopover();
            if (except !== 'text-effects') closeTextEffectsPopover();
            if (except !== 'animation') closeAnimationPopover();
            if (except !== 'countdown-date') closeCountdownDatePicker();
        }

        function renderCountdownDatePicker(baseDate = null) {
            if (!els.aaCountdownDatePicker || !els.aaCountdownDatePickerGrid || !els.aaCountdownDateMonthLabel) {
                return;
            }
            const selectedDate = parseCountdownDate(state.countdownPickerAnchor === els
                .aaCountdownContextDateInput ?
                els.aaCountdownContextDateInput?.value : els.aaCountdownDateInput?.value);
            const date = baseDate || state.countdownPickerDate || selectedDate || new Date();
            state.countdownPickerDate = new Date(date.getFullYear(), date.getMonth(), 1);
            const monthDate = state.countdownPickerDate;
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                'September', 'Oktober', 'November', 'Desember'
            ];
            els.aaCountdownDateMonthLabel.textContent =
                `${monthNames[monthDate.getMonth()]} ${monthDate.getFullYear()}`;

            if (els.aaCountdownDatePickerHead && !els.aaCountdownDatePickerHead.children.length) {
                ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].forEach(day => {
                    const item = document.createElement('span');
                    item.className = 'aa-date-picker-weekday';
                    item.textContent = day;
                    els.aaCountdownDatePickerHead.appendChild(item);
                });
            }

            els.aaCountdownDatePickerGrid.innerHTML = '';
            const first = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1);
            const start = new Date(first);
            start.setDate(first.getDate() - first.getDay());

            for (let index = 0; index < 42; index++) {
                const current = new Date(start);
                current.setDate(start.getDate() + index);
                const value = formatCountdownDate(current);
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = String(current.getDate());
                button.dataset.date = value;
                button.classList.toggle('is-muted', current.getMonth() !== monthDate.getMonth());
                button.classList.toggle('is-selected', selectedDate && value === formatCountdownDate(selectedDate));
                els.aaCountdownDatePickerGrid.appendChild(button);
            }
        }

        function positionCountdownDatePicker() {
            const anchor = state.countdownPickerAnchor || els.aaCountdownDateInput;
            if (!els.aaCountdownDatePicker || !anchor) return;
            const inputRect = anchor.getBoundingClientRect();
            const pickerRect = els.aaCountdownDatePicker.getBoundingClientRect();
            const margin = 12;
            const left = Math.max(margin, Math.min(inputRect.left, window.innerWidth - pickerRect.width -
                margin));
            const top = Math.max(margin, Math.min(inputRect.bottom + 8, window.innerHeight - pickerRect.height -
                margin));
            els.aaCountdownDatePicker.style.left = `${left}px`;
            els.aaCountdownDatePicker.style.top = `${top}px`;
        }

        function openCountdownDatePicker(anchor = null) {
            closeToolbarPopovers('countdown-date');
            state.countdownPickerAnchor = anchor || els.aaCountdownDateInput;
            renderCountdownDatePicker(getCountdownPickerDate());
            els.aaCountdownDatePicker?.classList.add('is-open');
            requestAnimationFrame(positionCountdownDatePicker);
        }

        function syncCountdownContextToolbar(active = state.canvas?.getActiveObject()) {
            const toolbar = els.aaCountdownContextToolbar;
            if (!toolbar || !state.canvas) return;
            const visible = Boolean(active && active.customType === 'countdown-timer' && !state.isCropping && !
                document
                .querySelector('.aa-modal.is-open'));
            toolbar.classList.toggle('is-visible', visible);
            if (!visible) return;

            const box = getInteractiveBox(active);
            if (els.aaCountdownContextDateInput) els.aaCountdownContextDateInput.value = active.countdownDate || '';
            if (els.aaCountdownContextTimeInput) els.aaCountdownContextTimeInput.value = active.countdownTime ||
                '00:00';
            if (els.aaCountdownContextBgInput) {
                setAlphaColorInputValue(els.aaCountdownContextBgInput, active.controlBackground || box?.fill ||
                    '#f8fafc', '#f8fafc');
            }
            if (els.aaCountdownContextRadiusInput) {
                els.aaCountdownContextRadiusInput.value = Math.max(0, Math.round(active.controlRadius ?? box?.rx ??
                    24));
            }
            if (els.aaCountdownContextGapInput) {
                els.aaCountdownContextGapInput.value = Math.max(0, Math.round(active.countdownGap || 0));
            }
            if (els.aaCountdownContextFontInput) {
                els.aaCountdownContextFontInput.value = active.countdownFontFamily || 'Inter';
            }
            if (els.aaCountdownContextSizeInput) {
                els.aaCountdownContextSizeInput.value = Math.max(8, Math.round(active.countdownFontSize || 36));
            }
            if (els.aaCountdownContextColorInput) {
                els.aaCountdownContextColorInput.value = normalizeColor(active.countdownTextColor || '#0f172a');
            }

            const canvasRect = state.canvas.upperCanvasEl.getBoundingClientRect();
            const objectRect = active.getBoundingRect(true, true);
            const scaleX = canvasRect.width / Math.max(1, state.canvas.getWidth());
            const scaleY = canvasRect.height / Math.max(1, state.canvas.getHeight());
            const width = toolbar.offsetWidth || 360;
            const height = toolbar.offsetHeight || 92;
            const pad = 12;
            const gap = 14;
            const screen = {
                left: canvasRect.left + objectRect.left * scaleX,
                top: canvasRect.top + objectRect.top * scaleY,
                width: objectRect.width * scaleX,
                height: objectRect.height * scaleY,
            };
            let left = screen.left - width - gap;
            let top = screen.top + (screen.height - height) / 2;
            if (left < pad) {
                left = screen.left + screen.width + gap;
            }
            if (left + width > window.innerWidth - pad) {
                left = screen.left + (screen.width - width) / 2;
                top = screen.top + screen.height + gap;
            }
            toolbar.style.left = `${Math.max(pad, Math.min(window.innerWidth - width - pad, left))}px`;
            toolbar.style.top = `${Math.max(pad, Math.min(window.innerHeight - height - pad, top))}px`;
        }

        function hideCountdownContextToolbar() {
            els.aaCountdownContextToolbar?.classList.remove('is-visible');
        }

        function refreshCountdownPreviewObject(object = state.canvas?.getActiveObject(), shouldSnapshot = true, layoutOptions = {}) {
            if (!object || object.customType !== 'countdown-timer') return;
            const width = Math.max(80, Number(object.width) || 620);
            const gap = Math.max(0, Number(object.countdownGap) || 0);
            const radius = Math.max(0, Number(object.controlRadius ?? 24) || 0);
            const layout = aaGetCountdownLayout({
                width,
                height: object.height,
                countdownGap: gap,
                countdownCardHeight: object.countdownCardHeight,
                __aaCountdownCardHeight: object.__aaCountdownCardHeight,
                countdownFontSize: object.countdownFontSize,
                columns: layoutOptions.columns,
            });
            const values = countdownPreviewValues(object);
            const boxes = getInteractiveBoxes(object);
            const valueTexts = getCountdownTextObjects(object).filter(text => text.name === 'countdown-value');
            const labelTexts = getCountdownTextObjects(object).filter(text => text.name === 'countdown-label');
            const fontFamily = object.countdownFontFamily || 'Inter';
            const fontSize = Math.max(8, Number(object.countdownFontSize) || 36);
            const labelFontSize = Math.max(8, Math.round(fontSize * .36));
            const textColor = object.countdownTextColor || '#0f172a';

            boxes.forEach((box, index) => {
                const item = layout.items[index] || layout.items[0];
                box.set({
                    left: item.left,
                    top: item.top,
                    width: item.width,
                    height: item.height,
                    rx: Math.min(radius, item.width / 2, item.height / 2),
                    ry: Math.min(radius, item.width / 2, item.height / 2),
                    fill: object.controlBackground || '#f8fafc',
                });
                box.setCoords();
            });

            valueTexts.forEach((text, index) => {
                const item = layout.items[index] || layout.items[0];
                text.set({
                    text: values[index] || '00',
                    left: item.centerX,
                    top: item.centerY - item.height * .12,
                    fontFamily,
                    fontSize,
                    fill: textColor,
                });
                text.dirty = true;
                if (typeof text.initDimensions === 'function') text.initDimensions();
                text.setCoords();
            });

            labelTexts.forEach((text, index) => {
                const item = layout.items[index] || layout.items[0];
                text.set({
                    left: item.centerX,
                    top: item.centerY + item.height * .26,
                    fontFamily,
                    fontSize: labelFontSize,
                    fill: textColor,
                });
                text.dirty = true;
                if (typeof text.initDimensions === 'function') text.initDimensions();
                text.setCoords();
            });

            object.set({
                width: layout.width,
                height: layout.totalHeight,
                countdownCardHeight: layout.cardHeight,
            });
            object.__aaCountdownCardHeight = layout.cardHeight;
            object.dirty = true;
            object.setCoords();
            state.canvas.requestRenderAll();
            syncCountdownContextToolbar(object);
            if (shouldSnapshot) snapshot();
        }

        function applyCountdownContextValue(values = {}) {
            const active = state.canvas?.getActiveObject();
            if (!active || active.customType !== 'countdown-timer') return;
            const next = {};
            if (Object.prototype.hasOwnProperty.call(values, 'countdownDate')) {
                next.countdownDate = values.countdownDate;
            }
            if (Object.prototype.hasOwnProperty.call(values, 'countdownTime')) {
                next.countdownTime = values.countdownTime || '00:00';
            }
            if (Object.prototype.hasOwnProperty.call(values, 'controlBackground')) {
                next.controlBackground = values.controlBackground;
            }
            if (Object.prototype.hasOwnProperty.call(values, 'controlRadius')) {
                next.controlRadius = Math.max(0, Number(values.controlRadius) || 0);
            }
            if (Object.prototype.hasOwnProperty.call(values, 'countdownGap')) {
                next.countdownGap = Math.max(0, Number(values.countdownGap) || 0);
            }
            if (Object.prototype.hasOwnProperty.call(values, 'countdownFontSize')) {
                next.countdownFontSize = Math.max(8, Number(values.countdownFontSize) || 36);
            }
            if (Object.prototype.hasOwnProperty.call(values, 'countdownFontFamily')) {
                next.countdownFontFamily = values.countdownFontFamily || 'Inter';
            }
            if (Object.prototype.hasOwnProperty.call(values, 'countdownTextColor')) {
                next.countdownTextColor = values.countdownTextColor;
            }
            const date = Object.prototype.hasOwnProperty.call(next, 'countdownDate') ? next.countdownDate : active
                .countdownDate;
            const time = Object.prototype.hasOwnProperty.call(next, 'countdownTime') ? next.countdownTime : active
                .countdownTime || '00:00';
            next.countdownTarget = isCompleteCountdownDate(date) ? `${date}T${time}:00` : '';
            active.set(next);
            refreshCountdownPreviewObject(active);
            syncInspector();
        }

        function createCountdownPreviewGroup(options = {}) {
            const width = Math.max(80, Number(options.width) || 620);
            const gap = Math.max(0, Number(options.countdownGap) || 0);
            const radius = Math.max(0, Number(options.controlRadius ?? 24));
            const layout = aaGetCountdownLayout({
                ...options,
                width,
                countdownGap: gap,
            });
            const values = countdownPreviewValues(options);
            const labels = ['Hari', 'Jam', 'Menit', 'Detik'];
            const fontFamily = options.countdownFontFamily || options.fontFamily || 'Inter';
            const fontSize = Math.max(8, Number(options.countdownFontSize || options.fontSize || 36));
            const labelFontSize = Math.max(8, Math.round(fontSize * .36));
            const textColor = options.countdownTextColor || options.fill || '#0f172a';
            const objects = [];

            labels.forEach((label, index) => {
                const item = layout.items[index] || layout.items[0];
                const rect = new fabric.Rect({
                    name: 'interactive-box',
                    left: item.left,
                    top: item.top,
                    width: item.width,
                    height: item.height,
                    rx: Math.min(radius, item.width / 2, item.height / 2),
                    ry: Math.min(radius, item.width / 2, item.height / 2),
                    fill: options.controlBackground || '#f8fafc',
                    stroke: 'rgba(15,118,110,0)',
                    strokeWidth: 1,
                    selectable: false,
                    evented: false,
                });
                const valueText = new fabric.Text(values[index], {
                    name: 'countdown-value',
                    left: item.centerX,
                    top: item.centerY - item.height * .12,
                    originX: 'center',
                    originY: 'center',
                    fontFamily,
                    fontSize,
                    fontWeight: 'bold',
                    fill: textColor,
                    selectable: false,
                    evented: false,
                });
                const labelText = new fabric.Text(label.toUpperCase(), {
                    name: 'countdown-label',
                    left: item.centerX,
                    top: item.centerY + item.height * .26,
                    originX: 'center',
                    originY: 'center',
                    fontFamily,
                    fontSize: labelFontSize,
                    fontWeight: 'bold',
                    fill: textColor,
                    opacity: .72,
                    selectable: false,
                    evented: false,
                });
                objects.push(rect, valueText, labelText);
            });

            return new fabric.Group(objects, {
                customType: 'countdown-timer',
                countdownDate: options.countdownDate || '',
                countdownTime: options.countdownTime || '00:00',
                countdownTarget: options.countdownTarget || '',
                controlBackground: options.controlBackground || '#f8fafc',
                controlRadius: radius,
                countdownGap: gap,
                countdownCardHeight: layout.cardHeight,
                countdownFontFamily: fontFamily,
                countdownFontSize: fontSize,
                countdownTextColor: textColor,
                lockScalingFlip: true,
                centeredScaling: false,
                objectCaching: false,
            });
        }

        function createGalleryFallbackCell(left, top, width, height, radius, label) {
            const safeRadius = Math.min(Math.max(0, Number(radius) || 0), width / 2, height / 2);
            const rect = new fabric.Rect({
                left,
                top,
                width,
                height,
                rx: safeRadius,
                ry: safeRadius,
                fill: '#e2e8f0',
                selectable: false,
                evented: false,
            });
            const text = new fabric.Text(label, {
                left: left + width / 2,
                top: top + height / 2,
                originX: 'center',
                originY: 'center',
                fontFamily: 'Inter',
                fontSize: 18,
                fontWeight: 'bold',
                fill: '#64748b',
                selectable: false,
                evented: false,
            });
            return [rect, text];
        }

        function fitImageToGalleryCell(image, cellWidth, cellHeight) {
            const sourceWidth = Math.max(1, image.width || 1);
            const sourceHeight = Math.max(1, image.height || 1);
            const sourceRatio = sourceWidth / sourceHeight;
            const cellRatio = cellWidth / cellHeight;
            let cropWidth = sourceWidth;
            let cropHeight = sourceHeight;
            let cropX = 0;
            let cropY = 0;

            if (sourceRatio > cellRatio) {
                cropWidth = sourceHeight * cellRatio;
                cropX = (sourceWidth - cropWidth) / 2;
            } else {
                cropHeight = sourceWidth / cellRatio;
                cropY = (sourceHeight - cropHeight) / 2;
            }

            image.set({
                cropX,
                cropY,
                width: cropWidth,
                height: cropHeight,
                scaleX: cellWidth / cropWidth,
                scaleY: cellHeight / cropHeight,
            });
        }

        function normalizeGalleryItems(source = {}) {
            if (Array.isArray(source.galleryItems) && source.galleryItems.length) {
                return source.galleryItems
                    .map(item => typeof item === 'string' ? {
                        src: item
                    } : item)
                    .filter(item => item && item.src)
                    .map(normalizeGalleryItem)
                    .filter(item => item.src);
            }
            return (Array.isArray(source.galleryImages) ? source.galleryImages : [])
                .filter(Boolean)
                .map(src => normalizeGalleryItem({
                    src,
                }))
                .filter(item => item.src);
        }

        function normalizeGalleryItem(item = {}) {
            const naturalWidth = Math.max(0, Number(item.naturalWidth) || 0);
            const naturalHeight = Math.max(0, Number(item.naturalHeight) || 0);
            const aspectRatio = naturalWidth > 0 && naturalHeight > 0 ? naturalWidth / naturalHeight : Math.max(0,
                Number(item.aspectRatio) || 0);
            return {
                src: String(item.src || '').trim(),
                name: String(item.name || '').trim(),
                naturalWidth,
                naturalHeight,
                aspectRatio,
                temporary: item.temporary === true,
                orientation: item.orientation || (aspectRatio > 1 ? 'landscape' : aspectRatio > 0 ?
                    'portrait' : ''),
            };
        }

        function getImageMetaFromFabric(image) {
            const element = image?._element;
            const naturalWidth = Math.max(0, Number(element?.naturalWidth || image?.width) || 0);
            const naturalHeight = Math.max(0, Number(element?.naturalHeight || image?.height) || 0);
            return normalizeGalleryItem({
                naturalWidth,
                naturalHeight,
            });
        }

        function getImageFileMeta(file) {
            return new Promise(resolve => {
                if (!file) {
                    resolve({});
                    return;
                }
                const url = URL.createObjectURL(file);
                const image = new Image();
                image.onload = () => {
                    const meta = normalizeGalleryItem({
                        naturalWidth: image.naturalWidth,
                        naturalHeight: image.naturalHeight,
                    });
                    URL.revokeObjectURL(url);
                    resolve(meta);
                };
                image.onerror = () => {
                    URL.revokeObjectURL(url);
                    resolve({});
                };
                image.src = url;
            });
        }

        function getImageUrlMeta(src) {
            return new Promise(resolve => {
                if (!src) {
                    resolve({});
                    return;
                }
                const image = new Image();
                image.crossOrigin = 'anonymous';
                image.onload = () => resolve(normalizeGalleryItem({
                    naturalWidth: image.naturalWidth,
                    naturalHeight: image.naturalHeight,
                }));
                image.onerror = () => resolve({});
                image.src = src;
            });
        }

        async function enrichGalleryItemMeta(item = {}) {
            const normalized = normalizeGalleryItem(item);
            if (normalized.naturalWidth && normalized.naturalHeight) return normalized;
            const meta = await getImageUrlMeta(normalized.src);
            return normalizeGalleryItem({
                ...normalized,
                naturalWidth: meta.naturalWidth,
                naturalHeight: meta.naturalHeight,
                aspectRatio: meta.aspectRatio,
                orientation: meta.orientation,
            });
        }

        function syncGalleryMetadata(object, items) {
            if (!object || object.customType !== 'photo-gallery') return;
            const normalized = normalizeGalleryItems({
                galleryItems: items,
            });
            object.set({
                galleryItems: normalized,
                galleryImages: normalized.map(item => item.src),
            });
            if (els.aaGalleryImagesInput) {
                els.aaGalleryImagesInput.value = object.galleryImages.join('\n');
            }
            renderGalleryItemList(object);
        }

        async function createGalleryPreviewGroup(options = {}) {
            const items = normalizeGalleryItems(options);
            const urls = items.map(item => item.src).filter(Boolean);
            const columns = Math.max(1, Math.min(6, Number(options.galleryColumns) || 2));
            const gap = Math.max(0, Number(options.galleryGap) || 0);
            const radius = Math.max(0, Number(options.galleryRadius) || 0);
            const width = Number(options.galleryPreviewWidth || options.width || 620);
            const height = Number(options.galleryPreviewHeight || options.height || 260);
            const visibleUrls = urls;
            const rows = Math.max(1, Math.ceil((visibleUrls.length || columns) / columns));
            const cellWidth = (width - gap * (columns - 1)) / columns;
            const cellHeight = (height - gap * (rows - 1)) / rows;
            const safeRadius = Math.min(radius, cellWidth / 2, cellHeight / 2);
            const loadedImages = visibleUrls.length ? await Promise.all(visibleUrls.map(loadFabricImage)) : [];
            const objects = [];

            if (!visibleUrls.length) {
                const emptyRadius = Math.min(radius, width / 2, height / 2);
                const rect = new fabric.Rect({
                    name: 'gallery-empty-box',
                    left: -width / 2,
                    top: -height / 2,
                    width,
                    height,
                    rx: emptyRadius,
                    ry: emptyRadius,
                    fill: '#f8fafc',
                    stroke: '#cbd5e1',
                    strokeDashArray: [12, 10],
                    strokeWidth: 3,
                    selectable: false,
                    evented: false,
                });
                const icon = new fabric.Text('＋', {
                    left: 0,
                    top: -18,
                    originX: 'center',
                    originY: 'center',
                    fontFamily: 'Inter',
                    fontSize: 44,
                    fontWeight: 'bold',
                    fill: '#94a3b8',
                    selectable: false,
                    evented: false,
                });
                const text = new fabric.Text('Belum ada foto', {
                    left: 0,
                    top: 34,
                    originX: 'center',
                    originY: 'center',
                    fontFamily: 'Inter',
                    fontSize: 24,
                    fontWeight: 'bold',
                    fill: '#64748b',
                    selectable: false,
                    evented: false,
                });
                return new fabric.Group([rect, icon, text], {
                    customType: 'photo-gallery',
                    galleryImages: [],
                    galleryItems: [],
                    galleryColumns: columns,
                    galleryGap: gap,
                    galleryRadius: radius,
                    galleryPreviewWidth: width,
                    galleryPreviewHeight: height,
                    objectCaching: false,
                });
            }

            const cellCount = visibleUrls.length;
            for (let index = 0; index < cellCount; index++) {
                const col = index % columns;
                const row = Math.floor(index / columns);
                const left = -width / 2 + col * (cellWidth + gap);
                const top = -height / 2 + row * (cellHeight + gap);
                const image = loadedImages[index];
                if (image && image.width && image.height) {
                    const imageMeta = getImageMetaFromFabric(image);
                    if (items[index]) {
                        items[index] = normalizeGalleryItem({
                            ...items[index],
                            naturalWidth: items[index].naturalWidth || imageMeta.naturalWidth,
                            naturalHeight: items[index].naturalHeight || imageMeta.naturalHeight,
                            aspectRatio: items[index].aspectRatio || imageMeta.aspectRatio,
                            orientation: items[index].orientation || imageMeta.orientation,
                        });
                    }
                    fitImageToGalleryCell(image, cellWidth, cellHeight);
                    image.set({
                        left,
                        top,
                        originX: 'left',
                        originY: 'top',
                        borderRadius: safeRadius,
                        selectable: false,
                        evented: false,
                    });
                    applyImageBorderRadius(image, safeRadius);
                    objects.push(image);
                } else {
                    objects.push(...createGalleryFallbackCell(left, top, cellWidth, cellHeight, safeRadius,
                        'Gallery'));
                }
            }

            return new fabric.Group(objects, {
                customType: 'photo-gallery',
                galleryImages: urls,
                galleryItems: items,
                galleryColumns: columns,
                galleryGap: gap,
                galleryRadius: radius,
                galleryPreviewWidth: width,
                galleryPreviewHeight: height,
                objectCaching: false,
            });
        }

        function getGalleryPreviewSize(object) {
            const storedWidth = Number(object?.galleryPreviewWidth);
            const storedHeight = Number(object?.galleryPreviewHeight);
            if (storedWidth > 0 && storedHeight > 0) {
                return {
                    width: storedWidth,
                    height: storedHeight,
                };
            }

            const children = object?.getObjects ? object.getObjects() : [];
            const emptyBox = children.find(child => child.name === 'gallery-empty-box') ||
                children.find(child => child.type === 'rect' && Array.isArray(child.strokeDashArray));
            if (emptyBox?.width && emptyBox?.height) {
                return {
                    width: Number(emptyBox.width),
                    height: Number(emptyBox.height),
                };
            }

            return {
                width: Number(object?.width) || 620,
                height: Number(object?.height) || 260,
            };
        }

        function copyPreviewTransform(source, target) {
            target.set({
                left: source.left,
                top: source.top,
                scaleX: source.scaleX,
                scaleY: source.scaleY,
                angle: source.angle,
                originX: source.originX,
                originY: source.originY,
                flipX: source.flipX,
                flipY: source.flipY,
                opacity: source.opacity,
                locked: source.locked === true,
            });
            if (source.locked === true) {
                setObjectLocked(target, true);
            }
        }

        async function refreshInteractivePreviewObject(object) {
            if (!object || !state.canvas) return object;
            const canvas = state.canvas;
            let replacement = null;
            const gallerySize = object.customType === 'photo-gallery' ? getGalleryPreviewSize(object) : null;
            const width = gallerySize?.width || object.width || 620;
            const height = gallerySize?.height || object.height || (object.customType === 'photo-gallery' ?
                260 : 130);

            if (object.customType === 'countdown-timer') {
                replacement = createCountdownPreviewGroup({
                    ...object.toObject(serializedObjectProps),
                    width,
                    height,
                });
            } else if (object.customType === 'photo-gallery') {
                replacement = await createGalleryPreviewGroup({
                    ...object.toObject(serializedObjectProps),
                    width,
                    height,
                });
            }

            if (!replacement) return object;
            if (!canvas.getObjects().includes(object)) return canvas.getActiveObject();
            copyPreviewTransform(object, replacement);
            const index = Math.max(0, canvas.getObjects().indexOf(object));
            canvas.remove(object);
            canvas.insertAt(replacement, index, false);
            canvas.setActiveObject(replacement);
            replacement.setCoords();
            canvas.requestRenderAll();
            syncInspector();
            snapshot();
            return replacement;
        }

        function renderGalleryItemList(object = state.canvas?.getActiveObject()) {
            if (!els.aaGalleryItemList) return;
            if (!object || object.customType !== 'photo-gallery') {
                els.aaGalleryItemList.innerHTML = '';
                return;
            }
            const items = normalizeGalleryItems(object);
            if (!items.length) {
                els.aaGalleryItemList.innerHTML =
                    '<div class="aa-gallery-empty">Belum ada foto. Upload atau pilih foto dari Media Library.</div>';
                return;
            }
            els.aaGalleryItemList.innerHTML = '';
            items.forEach((item, index) => {
                const row = document.createElement('div');
                row.className = 'aa-gallery-item-row';
                const img = document.createElement('img');
                img.src = item.src;
                img.alt = item.name || `Foto ${index + 1}`;
                const label = document.createElement('span');
                label.textContent = item.name || item.src.split('/').pop() || `Foto ${index + 1}`;
                const actions = document.createElement('div');
                actions.className = 'aa-gallery-item-actions';

                const makeButton = (title, icon, handler, disabled = false) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.title = title;
                    button.disabled = disabled;
                    button.innerHTML = `<i class="fa ${icon}"></i>`;
                    button.addEventListener('click', event => {
                        event.preventDefault();
                        event.stopPropagation();
                        handler();
                    });
                    return button;
                };

                actions.append(
                    makeButton('Naik', 'fa-arrow-up', () => moveGalleryItem(index, -1), index === 0),
                    makeButton('Turun', 'fa-arrow-down', () => moveGalleryItem(index, 1), index ===
                        items.length -
                        1),
                    makeButton('Hapus', 'fa-trash', () => removeGalleryItem(index))
                );
                row.append(img, label, actions);
                els.aaGalleryItemList.appendChild(row);
            });
        }

        function activeGalleryObject() {
            const active = state.canvas.getActiveObject();
            return active && active.customType === 'photo-gallery' ? active : null;
        }

        async function updateGalleryItems(items) {
            const active = activeGalleryObject();
            if (!active) return;
            syncGalleryMetadata(active, items);
            await refreshInteractivePreviewObject(active);
        }

        async function addGalleryAssets(assets) {
            const active = activeGalleryObject();
            if (!active) {
                setStatus('Pilih elemen gallery terlebih dahulu.', 'error');
                return;
            }
            const current = normalizeGalleryItems(active);
            const additions = await Promise.all((Array.isArray(assets) ? assets : [assets])
                .filter(item => item && item.src)
                .map(enrichGalleryItemMeta));
            if (!additions.length) return;
            await updateGalleryItems([...current, ...additions]);
            setStatus(`${additions.length} foto ditambahkan ke gallery`);
        }

        function removeGalleryItem(index) {
            const active = activeGalleryObject();
            if (!active) return;
            const items = normalizeGalleryItems(active);
            items.splice(index, 1);
            updateGalleryItems(items);
        }

        function moveGalleryItem(index, direction) {
            const active = activeGalleryObject();
            if (!active) return;
            const items = normalizeGalleryItems(active);
            const target = index + direction;
            if (target < 0 || target >= items.length) return;
            const [item] = items.splice(index, 1);
            items.splice(target, 0, item);
            updateGalleryItems(items);
        }

        function sanitizeYoutubeVideoId(value = '') {
            const match = String(value || '').match(/[A-Za-z0-9_-]{6,20}/);
            return match ? match[0] : '';
        }

        function parseYoutubeVideoId(value = '') {
            const source = String(value || '').trim();
            if (!source) return '';
            const direct = source.match(/^[A-Za-z0-9_-]{6,20}$/);
            if (direct) return direct[0];
            try {
                const url = new URL(source);
                const host = url.hostname.replace(/^www\./, '');
                if (host === 'youtu.be') {
                    return sanitizeYoutubeVideoId(url.pathname.split('/').filter(Boolean)[0] || '');
                }
                if (host.endsWith('youtube.com') || host.endsWith('youtube-nocookie.com')) {
                    const watchId = url.searchParams.get('v');
                    if (watchId) return sanitizeYoutubeVideoId(watchId);
                    const parts = url.pathname.split('/').filter(Boolean);
                    const markerIndex = parts.findIndex(part => ['embed', 'shorts', 'live'].includes(part));
                    if (markerIndex !== -1 && parts[markerIndex + 1]) {
                        return sanitizeYoutubeVideoId(parts[markerIndex + 1]);
                    }
                }
            } catch (error) {
                return extractYoutubeIdFromText(source);
            }
            return '';
        }

        function extractYoutubeIdFromText(value = '') {
            const source = String(value || '').trim();
            const markers = ['youtu.be/', 'watch?v=', 'embed/', 'shorts/', 'live/'];
            for (const marker of markers) {
                const index = source.indexOf(marker);
                if (index === -1) continue;
                return sanitizeYoutubeVideoId(source.slice(index + marker.length));
            }
            return '';
        }

        function refreshYoutubePreviewObject(object) {
            if (!object || object.customType !== 'youtube-video') return;
            const text = getNamedGroupText(object);
            if (text) {
                text.set('text', object.youtubeVideoId ? 'Youtube Video' : 'Tempel link Youtube');
            }
            object.dirty = true;
            object.setCoords();
            state.canvas?.requestRenderAll();
        }

        function addYoutubeVideo(event) {
            if (guardPremiumFeature(event)) return;
            const group = createLabeledBox('Tempel link Youtube', '▶', {
                width: 560,
                height: 315,
                radius: 18,
                fill: '#111827',
                stroke: '#111827',
                textFill: '#ffffff',
                fontSize: 30,
                iconSize: 54,
                props: {
                    customType: 'youtube-video',
                    youtubeUrl: '',
                    youtubeVideoId: '',
                    youtubeAutoplayOnView: true,
                    youtubeLoop: true,
                    controlBackground: '#111827',
                    controlRadius: 18,
                },
            });
            centerObject(group);
            syncInspector();
            setStatus('Video Youtube ditambahkan. Tempel link di popover dekat object.');
        }

        function addMusicPlayer() {
            const radius = 54;
            const box = new fabric.Rect({
                name: 'interactive-box',
                left: -radius,
                top: -radius,
                width: radius * 2,
                height: radius * 2,
                rx: radius,
                ry: radius,
                fill: '#0f766e',
                stroke: 'transparent',
                strokeWidth: 0,
                selectable: false,
                evented: false,
            });
            const icon = new fabric.Text('♪', {
                name: 'interactive-icon',
                left: 0,
                top: -2,
                originX: 'center',
                originY: 'center',
                fontFamily: 'Arial',
                fontSize: 46,
                fontWeight: 'bold',
                fill: '#ffffff',
                selectable: false,
                evented: false,
            });
            const group = new fabric.Group([box, icon], {
                label: 'Music Player',
                placeholder: 'Music Player',
                objectCaching: false,
                customType: 'music-player',
                audioUrl: '',
                autoplayAfterInteraction: true,
                loopAudio: true,
                showPlayerButton: true,
                musicButtonShape: 'circle',
                controlBackground: '#0f766e',
                controlRadius: radius,
            });
            centerObject(group);
            syncInspector();
            setStatus('Music player ditambahkan. Atur Audio URL di panel Music.');
        }

        function addScrollNextButton() {
            const group = createLabeledBox('Scroll Down', '↓', {
                width: 360,
                height: 92,
                fill: '#0f766e',
                stroke: '#0f766e',
                textFill: '#ffffff',
                props: {
                    customType: 'scroll-next-button',
                    buttonAction: 'scroll-next',
                    scrollTarget: 'next',
                    buttonText: 'Scroll Down',
                    controlBackground: '#0f766e',
                    controlRadius: 24,
                    lockPageScroll: true,
                },
            });
            centerObject(group);
            syncInspector();
            setStatus('Scroll button ditambahkan');
        }

        function addCountdownTimer() {
            const now = new Date();
            now.setDate(now.getDate() + 30);
            const date = now.toISOString().slice(0, 10);
            const group = createCountdownPreviewGroup({
                width: 620,
                height: 130,
                countdownDate: date,
                countdownTime: '09:00',
                countdownTarget: date + 'T09:00:00',
                controlBackground: '#f8fafc',
                controlRadius: 24,
                countdownGap: 10,
            });
            centerObject(group);
            syncInspector();
            setStatus('Countdown ditambahkan. Atur tanggal dan jam di panel Properties.');
        }

        function aaDefaultSocialLinks() {
            return {
                instagram: '',
                tiktok: '',
                threads: '',
                x: '',
                facebook: '',
                youtube: '',
            };
        }

        function aaDefaultStoryItems() {
            return [{
                    title: 'Pertama Bertemu',
                    date: '',
                    description: 'Ceritakan awal pertemuan kalian di sini.',
                },
                {
                    title: 'Lamaran',
                    date: '',
                    description: 'Tambahkan momen penting berikutnya.',
                },
                {
                    title: 'Hari Bahagia',
                    date: '',
                    description: 'Tulis cerita menuju hari acara.',
                },
            ];
        }

        function aaSocialPlatformMeta(platform = 'instagram') {
            const options = {
                instagram: {
                    label: 'Instagram',
                    icon: '\uf16d',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#e11d48',
                    url: 'https://instagram.com/',
                },
                tiktok: {
                    label: 'TikTok',
                    icon: '\ue07b',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#111827',
                    url: 'https://tiktok.com/@',
                },
                youtube: {
                    label: 'YouTube',
                    icon: '\uf167',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#dc2626',
                    url: 'https://youtube.com/',
                },
                whatsapp: {
                    label: 'WhatsApp',
                    icon: '\uf232',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#16a34a',
                    url: 'https://wa.me/',
                },
                facebook: {
                    label: 'Facebook',
                    icon: '\uf39e',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#2563eb',
                    url: 'https://facebook.com/',
                },
                x: {
                    label: 'X',
                    icon: '\ue61f',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#020617',
                    url: 'https://x.com/',
                },
                threads: {
                    label: 'Threads',
                    icon: '\ue618',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#111827',
                    url: 'https://threads.net/@',
                },
                telegram: {
                    label: 'Telegram',
                    icon: '\uf2c6',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#0284c7',
                    url: 'https://t.me/',
                },
                pinterest: {
                    label: 'Pinterest',
                    icon: '\uf0d2',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#be123c',
                    url: 'https://pinterest.com/',
                },
                linkedin: {
                    label: 'LinkedIn',
                    icon: '\uf0e1',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#0369a1',
                    url: 'https://linkedin.com/',
                },
                spotify: {
                    label: 'Spotify',
                    icon: '\uf1bc',
                    fontFamily: 'Font Awesome 6 Brands',
                    fontWeight: '400',
                    color: '#16a34a',
                    url: 'https://open.spotify.com/',
                },
                shopee: {
                    label: 'Shopee',
                    icon: '\uf290',
                    fontFamily: 'Font Awesome 6 Free',
                    fontWeight: '900',
                    color: '#ea580c',
                    url: 'https://shopee.co.id/',
                },
                tokopedia: {
                    label: 'Tokopedia',
                    icon: '\uf54e',
                    fontFamily: 'Font Awesome 6 Free',
                    fontWeight: '900',
                    color: '#16a34a',
                    url: 'https://tokopedia.com/',
                },
                website: {
                    label: 'Website',
                    icon: '\uf0c1',
                    fontFamily: 'Font Awesome 6 Free',
                    fontWeight: '900',
                    color: '#0f766e',
                    url: 'https://',
                },
            };
            return options[platform] || options.instagram;
        }

        function aaSocialActiveCount(links = {}) {
            return Object.values(links || {}).filter(value => String(value || '').trim() !== '').length;
        }

        function aaLayoutSocialLinkGroup(group) {
            if (!group || group.customType !== 'social-link' || typeof group.getObjects !== 'function') return;
            const meta = aaSocialPlatformMeta(group.socialPlatform);
            const center = typeof group.getCenterPoint === 'function' ? group.getCenterPoint() : null;
            const iconBg = group.getObjects().find(child => child.name === 'social-icon-bg');
            const icon = group.getObjects().find(child => child.name === 'social-icon');
            const label = group.getObjects().find(child => child.name === 'social-label');
            if (iconBg) iconBg.set({
                radius: 24,
                left: 0,
                top: 0,
                originX: 'center',
                originY: 'center',
                fill: meta.color,
            });
            if (icon) icon.set({
                text: meta.icon,
                left: 0,
                top: 0,
                originX: 'center',
                originY: 'center',
                fontFamily: meta.fontFamily || 'Font Awesome 6 Brands',
                fontWeight: meta.fontWeight || '400',
                fontSize: 20,
                fill: '#ffffff',
            });
            if (typeof icon?.initDimensions === 'function') {
                icon.initDimensions();
            }
            if (label) label.set({
                text: group.socialLabel || meta.label,
                left: 38,
                top: 0,
                originX: 'left',
                originY: 'center',
                fontFamily: label.fontFamily || 'Inter',
                fontSize: label.fontSize || 30,
                fontWeight: label.fontWeight || '800',
                fill: label.fill || '#0f172a',
                underline: Boolean(label.underline),
            });
            if (typeof label?.initDimensions === 'function') {
                label.initDimensions();
            }
            if (document.fonts && typeof document.fonts.load === 'function') {
                document.fonts.load(
                        `normal ${meta.fontWeight || '400'} 24px "${meta.fontFamily || 'Font Awesome 6 Brands'}"`)
                    .then(() => {
                        icon?.set?.('dirty', true);
                        group.dirty = true;
                        state.canvas?.requestRenderAll?.();
                    })
                    .catch(() => {});
            }
            try {
                group._calcBounds?.();
                group._updateObjectsCoords?.();
            } catch (error) {}
            if (center && Number.isFinite(center.x) && Number.isFinite(center.y) && typeof group.setPositionByOrigin ===
                'function') {
                group.setPositionByOrigin(center, 'center', 'center');
            }
            group.setCoords?.();
        }

        function aaCreateSocialLinkGroup({
            socialPlatform = 'instagram',
            socialLabel = '',
            link = '',
        } = {}) {
            const meta = aaSocialPlatformMeta(socialPlatform);
            const labelText = socialLabel || meta.label;
            const targetLink = link || meta.url;
            const iconBg = new fabric.Circle({
                name: 'social-icon-bg',
                radius: 24,
                left: 0,
                top: 0,
                originX: 'center',
                originY: 'center',
                fill: meta.color,
                selectable: false,
                evented: false,
            });
            const icon = new fabric.Text(meta.icon, {
                name: 'social-icon',
                left: 0,
                top: 0,
                originX: 'center',
                originY: 'center',
                fontFamily: meta.fontFamily || 'Font Awesome 6 Brands',
                fontWeight: meta.fontWeight || '400',
                fontSize: 20,
                fill: '#ffffff',
                selectable: false,
                evented: false,
            });
            const label = new fabric.Text(labelText, {
                name: 'social-label',
                left: 38,
                top: 0,
                originX: 'left',
                originY: 'center',
                fontFamily: 'Inter',
                fontSize: 30,
                fontWeight: '800',
                fill: '#0f172a',
                underline: false,
                selectable: false,
                evented: false,
            });
            const group = new fabric.Group([iconBg, icon, label], {
                name: 'Social Media',
                customType: 'social-link',
                socialPlatform,
                socialLabel: labelText,
                link: targetLink,
                originX: 'center',
                originY: 'center',
                objectCaching: false,
            });
            aaLayoutSocialLinkGroup(group);
            return group;
        }

        function applySocialLinkValue(values = {}) {
            const active = state.canvas?.getActiveObject?.();
            if (!active || active.customType !== 'social-link') return;
            const nextPlatform = Object.prototype.hasOwnProperty.call(values, 'socialPlatform') ?
                values.socialPlatform : active.socialPlatform || 'instagram';
            const meta = aaSocialPlatformMeta(nextPlatform);
            active.set({
                socialPlatform: nextPlatform,
                socialLabel: Object.prototype.hasOwnProperty.call(values, 'socialLabel') ?
                    String(values.socialLabel || meta.label) : (active.socialLabel || meta.label),
                link: Object.prototype.hasOwnProperty.call(values, 'link') ? String(values.link || '').trim() :
                    (active.link || meta.url),
            });
            aaLayoutSocialLinkGroup(active);
            state.canvas?.requestRenderAll?.();
            syncInteractionUi(active);
            snapshot();
        }

        function aaStoryItemsToText(items = []) {
            return (Array.isArray(items) ? items : []).map(item => [
                item.title || '',
                item.date || '',
                item.description || '',
            ].join(' | ')).join('\n');
        }

        function aaStoryTextToItems(value = '') {
            return String(value || '').split(/\r?\n/)
                .map(line => line.trim())
                .filter(Boolean)
                .map(line => {
                    const parts = line.split('|').map(part => part.trim());
                    return {
                        title: parts[0] || 'Cerita',
                        date: parts[1] || '',
                        description: parts.slice(2).join(' | ') || '',
                    };
                });
        }

        function aaUpdateInteractivePreviewText(group, titleValue, bodyValue) {
            const title = group?.getObjects?.().find(child => child.name === 'interactive-title');
            const body = group?.getObjects?.().find(child => child.name === 'interactive-text');
            if (title) title.set('text', titleValue);
            if (body) body.set('text', bodyValue);
            group?.setCoords?.();
            state.canvas?.requestRenderAll?.();
        }

        function addSocialMediaElement() {
            const group = aaCreateSocialLinkGroup({
                socialPlatform: 'instagram',
                socialLabel: 'Instagram',
                link: 'https://instagram.com/',
            });
            centerObject(group);
            syncInspector();
            syncInteractionPopover(group);
            setStatus('Social Media ditambahkan. Atur icon, nama, dan link di popover.');
        }

        function addStoryMakerElement() {
            const centerX = state.canvas.getWidth() / 2;
            const centerY = state.canvas.getHeight() / 2;
            const blockWidth = 620;
            const imageWidth = 520;
            const imageHeight = 320;
            const top = centerY - 300;

            const photo = new fabric.Rect({
                left: centerX,
                top,
                originX: 'center',
                originY: 'top',
                width: imageWidth,
                height: imageHeight,
                rx: 28,
                ry: 28,
                fill: '#f1f5f9',
                stroke: '#cbd5e1',
                strokeWidth: 3,
                strokeDashArray: [14, 12],
                customType: 'photo-frame',
                aaSource: 'story-maker',
                aaImageFrameShape: 'rounded',
                borderRadius: 28,
                objectCaching: false,
            });

            const title = new fabric.Textbox('Judul Cerita', {
                left: centerX,
                top: top + imageHeight + 46,
                originX: 'center',
                originY: 'top',
                width: blockWidth,
                fontFamily: 'Playfair Display',
                fontSize: 58,
                fontWeight: '700',
                fill: '#0f172a',
                textAlign: 'center',
                lineHeight: 1.08,
                customType: 'story-title',
            });

            const date = new fabric.Textbox('12 Oktober 2024', {
                left: centerX,
                top: top + imageHeight + 122,
                originX: 'center',
                originY: 'top',
                width: blockWidth,
                fontFamily: 'Inter',
                fontSize: 25,
                fontWeight: '800',
                fill: '#0f766e',
                textAlign: 'center',
                charSpacing: 80,
                customType: 'story-date',
            });

            const description = new fabric.Textbox('Tulis detail cerita di sini. Ceritakan momen penting dengan singkat, hangat, dan mudah dibaca.', {
                left: centerX,
                top: top + imageHeight + 174,
                originX: 'center',
                originY: 'top',
                width: blockWidth,
                fontFamily: 'Inter',
                fontSize: 30,
                fill: '#475569',
                textAlign: 'center',
                lineHeight: 1.45,
                customType: 'story-description',
            });

            [title, date, description].forEach(object => aaApplyTextboxResizeControls(object));
            state.canvas.add(photo, title, date, description);

            const selection = new fabric.ActiveSelection([photo, title, date, description], {
                canvas: state.canvas,
            });
            state.canvas.setActiveObject(selection);
            state.canvas.requestRenderAll();
            snapshot();
            syncInspector();
            setStatus('Story Maker ditambahkan. Edit teks langsung di canvas. Drag foto dari Media Library ke area foto.');
        }

        async function addPhotoGallery() {
            if (guardPremiumFeature()) return;
            state.mediaMode = 'gallery-photo';
            setStatus('Pilih atau upload 1 foto untuk Gallery. Foto bisa resize, drag, dan klik zoom.');
            els.aaImageInput?.click();
        }

        function loadFabricImage(url) {
            return new Promise(resolve => {
                const timer = window.setTimeout(() => resolve(null), 6000);
                fabric.Image.fromURL(url, image => {
                    window.clearTimeout(timer);
                    aaApplySafeImageHitTesting(image);
                    resolve(image || null);
                }, {
                    crossOrigin: 'anonymous'
                });
            });
        }

        function aaAddCacheBustToUrl(url, key = 'aa') {
            const source = String(url || '');
            if (!source || source.startsWith('data:') || source.startsWith('blob:')) return source;
            try {
                const parsed = new URL(source, window.location.href);
                parsed.searchParams.set(key, String(Date.now()));
                return parsed.toString();
            } catch (error) {
                const glue = source.includes('?') ? '&' : '?';
                return `${source}${glue}${encodeURIComponent(key)}=${Date.now()}`;
            }
        }

        async function loadFabricImageWithRetry(url, options = {}) {
            const attempts = Math.max(1, Number(options.attempts || 3));
            const delay = Math.max(0, Number(options.delay || 450));
            for (let attempt = 0; attempt < attempts; attempt += 1) {
                const source = attempt === 0 ? url : aaAddCacheBustToUrl(url, `aa_retry_${attempt}`);
                const image = await loadFabricImage(source);
                if (image) {
                    image.set('src', url);
                    return image;
                }
                if (attempt < attempts - 1 && delay > 0) {
                    await new Promise(resolve => window.setTimeout(resolve, delay * (attempt + 1)));
                }
            }
            return null;
        }

        function aaApplyImageResizeControls(object) {
            if (!object || object.type !== 'image') return;
            if (object.selectable === false || object.evented === false || object.locked === true) return;

            object.set({
                lockScalingFlip: true,
                centeredScaling: false,
                centeredRotation: false,
            });

            if (typeof object.setControlsVisibility === 'function') {
                object.setControlsVisibility({
                    ml: true,
                    mr: true,
                    mt: true,
                    mb: true,
                    tl: true,
                    tr: true,
                    bl: true,
                    br: true,
                    mtr: true,
                });
            }
        }

        function aaPreserveImageAspectRatioOnScale(event) {
            const object = event?.target;

            if (
                !object ||
                object.type !== 'image' ||
                object.customType === 'background' ||
                object.locked === true ||
                object === state.cropBox ||
                state.isCropping
            ) {
                return false;
            }

            const corner = typeof aaGetFabricTransformCorner === 'function' ? aaGetFabricTransformCorner(event) : '';
            const currentScaleX = Number(object.scaleX) || 1;
            const currentScaleY = Number(object.scaleY) || 1;
            const absScaleX = Math.max(0.001, Math.abs(currentScaleX));
            const absScaleY = Math.max(0.001, Math.abs(currentScaleY));
            let uniformScale = Math.max(absScaleX, absScaleY);

            if (corner === 'ml' || corner === 'mr') {
                uniformScale = absScaleX;
            } else if (corner === 'mt' || corner === 'mb') {
                uniformScale = absScaleY;
            }

            if (Math.abs(absScaleX - absScaleY) < 0.001) {
                return false;
            }

            object.set({
                scaleX: uniformScale * (currentScaleX < 0 ? -1 : 1),
                scaleY: uniformScale * (currentScaleY < 0 ? -1 : 1),
            });
            object.setCoords?.();
            object.dirty = true;

            return true;
        }

        function aaApplySafeImageHitTesting(object) {
            if (!object || object.type !== 'image') return;
            if (object.selectable === false || object.evented === false || object.locked === true) return;

            aaApplyImageResizeControls(object);

            const canvas = state.canvas || object.canvas || null;
            const canvasArea = canvas ? Math.max(1, canvas.getWidth() * canvas.getHeight()) : 0;
            const objectArea = Math.max(1, (object.getScaledWidth?.() || object.width || 1) * (object.getScaledHeight?.() || object.height || 1));
            const src = String(object.aaRemovedBgSrc || object.aaOriginalImageSrc || object.getSrc?.() || object.src || '');
            const alphaLike = object.aaRemovedBg === true ||
                object.aaImageAlphaOutline === true ||
                object.aaImageOutlineAlphaEligible === true ||
                /\.(png|webp)(?:[?#].*)?$/i.test(src);
            const isLarge = canvasArea > 0 && objectArea / canvasArea >= 0.18;

            if (alphaLike || isLarge) {
                object.perPixelTargetFind = true;
                object.targetFindTolerance = Math.max(1, Math.min(4, Number(object.targetFindTolerance || 2)));
            }
        }

        function aaFabricObjectArea(object) {
            if (!object) return 0;
            return Math.max(1, (object.getScaledWidth?.() || object.width || 1) * (object.getScaledHeight?.() || object.height || 1));
        }

        function aaResolveAccidentalImageActiveSelection(event = null) {
            const canvas = state.canvas;
            const active = canvas?.getActiveObject?.();
            if (!canvas || !active || active.type !== 'activeSelection' || typeof active.getObjects !== 'function') return false;

            const sourceEvent = event?.e || null;
            if (sourceEvent?.shiftKey || sourceEvent?.metaKey || sourceEvent?.ctrlKey || sourceEvent?.altKey) return false;

            const pointer = sourceEvent ? canvas.getPointer(sourceEvent, true) : null;
            const down = state.__aaLastSelectionPointer || null;
            if (!pointer || !down || down.modifier === true) return false;

            const moved = Math.hypot((pointer.x || 0) - (down.x || 0), (pointer.y || 0) - (down.y || 0));
            if (moved > 8 || (Date.now() - down.time) > 900) return false;

            const objects = active.getObjects().filter(object => object?.selectable !== false && object?.evented !== false);
            if (objects.length < 2 || !objects.some(object => object.type === 'image')) return false;

            const areas = objects.map(aaFabricObjectArea);
            const largest = Math.max(...areas);
            const smallest = Math.min(...areas);
            if (largest < smallest * 1.8) return false;

            const stack = canvas.getObjects();
            const topMost = objects
                .slice()
                .sort((a, b) => stack.indexOf(b) - stack.indexOf(a))[0];
            if (!topMost) return false;

            canvas.discardActiveObject();
            canvas.setActiveObject(topMost);
            topMost.setCoords?.();
            canvas.requestRenderAll();

            return true;
        }

        function aaFilterLockedObjectsFromActiveSelection() {
            const canvas = state.canvas;
            const active = canvas?.getActiveObject?.();
            if (!canvas || !active || active.type !== 'activeSelection' || typeof active.getObjects !== 'function') return false;

            const objects = active.getObjects();
            const unlockedObjects = objects.filter(object => object && object.locked !== true && object.selectable !== false && object.evented !== false);
            if (unlockedObjects.length === objects.length) return false;

            canvas.discardActiveObject();

            if (unlockedObjects.length === 1) {
                canvas.setActiveObject(unlockedObjects[0]);
                unlockedObjects[0].setCoords?.();
            } else if (unlockedObjects.length > 1) {
                const selection = new fabric.ActiveSelection(unlockedObjects, {
                    canvas,
                });
                canvas.setActiveObject(selection);
                selection.setCoords?.();
            }

            canvas.requestRenderAll();
            return true;
        }

        function aaIsSelectableCanvasObject(object) {
            return !!(
                object &&
                object !== state.cropBox &&
                object.selectable !== false &&
                object.evented !== false &&
                object.locked !== true &&
                object.customType !== 'background' &&
                object.customType !== 'selection-helper'
            );
        }

        function aaIsLargeImageObject(object) {
            if (!object || object.type !== 'image') return false;
            const canvas = state.canvas || object.canvas || null;
            const canvasArea = canvas ? Math.max(1, canvas.getWidth() * canvas.getHeight()) : 0;
            return canvasArea > 0 && aaFabricObjectArea(object) / canvasArea >= 0.18;
        }

        function aaObjectContainsCanvasPoint(object, point) {
            if (!object || !point || typeof object.containsPoint !== 'function') return false;
            try {
                return object.containsPoint(point);
            } catch (error) {
                return false;
            }
        }

        function aaSelectableObjectsAtPointer(pointer) {
            if (!state.canvas || !pointer) return [];
            const point = new fabric.Point(pointer.x, pointer.y);
            return state.canvas.getObjects()
                .slice()
                .reverse()
                .filter(object => aaIsSelectableCanvasObject(object) && aaObjectContainsCanvasPoint(object, point));
        }

        function resolveLargeImageClickSelection(event = null) {
            const canvas = state.canvas;
            const sourceEvent = event?.e || null;
            if (!canvas || !sourceEvent) return false;
            if (sourceEvent.shiftKey || sourceEvent.metaKey || sourceEvent.ctrlKey || sourceEvent.altKey) return false;

            const pointer = canvas.getPointer(sourceEvent, true);
            const candidates = aaSelectableObjectsAtPointer(pointer);
            if (candidates.length < 2) return false;

            const topMost = candidates[0];
            if (!topMost || aaIsLargeImageObject(topMost)) return false;
            if (!candidates.slice(1).some(aaIsLargeImageObject)) return false;

            window.requestAnimationFrame(() => {
                if (!canvas.getObjects().includes(topMost)) return;
                if (canvas.getActiveObject?.() === topMost) return;

                canvas.discardActiveObject();
                canvas.setActiveObject(topMost);
                topMost.setCoords?.();
                canvas.requestRenderAll();
                syncInspector();
                syncObjectFloatingToolbar();
                syncCountdownContextToolbar();
                syncInteractionPopover();
            });

            return true;
        }

        async function addGallery() {
            setStatus('Membuat gallery...', 'saving');
            if (!state.mediaAssets.length) {
                await loadMedia({ force: true }).catch(() => {});
            }
            const cellWidth = 210;
            const cellHeight = 150;
            const gap = 16;
            const urls = (state.mediaAssets || []).slice(0, 4).map(item => item.src).filter(Boolean);
            const objects = [];

            if (urls.length) {
                const images = await Promise.all(urls.map(loadFabricImage));
                images.forEach((image, index) => {
                    const col = index % 2;
                    const row = Math.floor(index / 2);
                    image.scaleToWidth(cellWidth);
                    if (image.getScaledHeight() > cellHeight) {
                        image.scaleToHeight(cellHeight);
                    }
                    image.set({
                        left: col * (cellWidth + gap),
                        top: row * (cellHeight + gap),
                        originX: 'left',
                        originY: 'top',
                        customType: 'gallery-image',
                        borderRadius: 18,
                        objectCaching: false,
                    });
                    applyImageBorderRadius(image, 18);
                    objects.push(image);
                });
            } else {
                for (let index = 0; index < 4; index++) {
                    const col = index % 2;
                    const row = Math.floor(index / 2);
                    const rect = new fabric.Rect({
                        left: col * (cellWidth + gap),
                        top: row * (cellHeight + gap),
                        width: cellWidth,
                        height: cellHeight,
                        rx: 18,
                        ry: 18,
                        fill: '#e2e8f0',
                        stroke: '#cbd5e1',
                        strokeWidth: 2,
                        originX: 'left',
                        originY: 'top',
                        customType: 'gallery-placeholder',
                    });
                    const icon = new fabric.Text('Image', {
                        left: col * (cellWidth + gap) + cellWidth / 2,
                        top: row * (cellHeight + gap) + cellHeight / 2,
                        originX: 'center',
                        originY: 'center',
                        fontFamily: 'Inter',
                        fontSize: 22,
                        fill: '#64748b',
                        customType: 'gallery-placeholder-text',
                    });
                    objects.push(rect, icon);
                }
            }

            const gallery = new fabric.Group(objects, {
                customType: 'gallery',
                name: 'Gallery',
                left: state.canvas.getWidth() / 2,
                top: state.canvas.getHeight() / 2,
                originX: 'center',
                originY: 'center',
                objectCaching: false,
            });
            centerObject(gallery);
            setStatus('Gallery ditambahkan');
        }

        function addShape(type) {
            let object;
            if (type === 'circle') {
                object = new fabric.Circle({
                    radius: 110,
                    fill: '#f9a8d4',
                    stroke: '#be185d',
                    strokeWidth: 0,
                    customType: 'shape',
                });
            } else if (type === 'line') {
                object = new fabric.Line([0, 0, 320, 0], {
                    stroke: '#111827',
                    strokeWidth: 8,
                    customType: 'shape',
                });
            } else {
                object = new fabric.Rect({
                    width: 300,
                    height: 180,
                    rx: type === 'roundrect' ? 36 : 0,
                    ry: type === 'roundrect' ? 36 : 0,
                    fill: '#ccfbf1',
                    customType: 'shape',
                });
            }
            centerObject(object);
        }

        function addSticker(type) {
            const stickerMap = {
                flower: '✿',
                sparkle: '✦',
                heart: '♡',
                leaf: '❧',
            };
            const sticker = new fabric.Text(stickerMap[type] || '✦', {
                fontFamily: 'Georgia',
                fontSize: 150,
                fill: type === 'heart' ? '#be123c' : '#0f766e',
                customType: 'sticker',
            });
            centerObject(sticker);
        }

        function resetImageCropState(image) {
            if (!image) return;
            image.set({
                cropX: 0,
                cropY: 0,
                clipPath: null,
            });
            image.dirty = true;
        }

        function readImageSourceUrl(image) {
            return String(image?.getSrc?.() || image?.src || image?._element?.src || '').trim();
        }

        function ensureImageOriginalSource(image, fallbackUrl = '') {
            if (!image) return '';
            const currentSrc = readImageSourceUrl(image);
            const originalSrc = String(image.aaOriginalImageSrc || fallbackUrl || currentSrc || '').trim();
            if (originalSrc && !image.aaOriginalImageSrc) {
                image.set('aaOriginalImageSrc', originalSrc);
            }
            return originalSrc;
        }

        function applyImageCoverToFrame(image, frameWidth, frameHeight) {
            if (!image) return {
                cropWidth: Math.max(1, image?.width || frameWidth || 1),
                cropHeight: Math.max(1, image?.height || frameHeight || 1),
            };

            const element = image.getElement ? image.getElement() : null;
            const naturalWidth = Math.max(1, element?.naturalWidth || image._element?.naturalWidth || image.width ||
                1);
            const naturalHeight = Math.max(1, element?.naturalHeight || image._element?.naturalHeight || image
                .height ||
                1);
            const safeFrameWidth = Math.max(1, frameWidth || naturalWidth);
            const safeFrameHeight = Math.max(1, frameHeight || naturalHeight);
            const frameRatio = safeFrameWidth / safeFrameHeight;
            const imageRatio = naturalWidth / naturalHeight;
            let cropWidth = naturalWidth;
            let cropHeight = naturalHeight;
            let cropX = 0;
            let cropY = 0;

            if (imageRatio > frameRatio) {
                cropWidth = naturalHeight * frameRatio;
                cropX = (naturalWidth - cropWidth) / 2;
            } else if (imageRatio < frameRatio) {
                cropHeight = naturalWidth / frameRatio;
                cropY = (naturalHeight - cropHeight) / 2;
            }

            image.set({
                cropX: Math.max(0, cropX),
                cropY: Math.max(0, cropY),
                width: Math.max(1, cropWidth),
                height: Math.max(1, cropHeight),
                clipPath: null,
            });

            return {
                cropWidth: Math.max(1, cropWidth),
                cropHeight: Math.max(1, cropHeight),
            };
        }

        function copyImageReplaceStyle(source, target, options = {}) {
            if (!source || !target) return;
            const hasOption = key => Object.prototype.hasOwnProperty.call(options, key);
            const scaledWidth = Math.max(1, source.getScaledWidth ? source.getScaledWidth() : (source.width || 1) *
                Math.abs(source.scaleX || 1));
            const scaledHeight = Math.max(1, source.getScaledHeight ? source.getScaledHeight() : (source.height ||
                    1) *
                Math.abs(source.scaleY || 1));
            const preserveCrop = options.preserveCrop === true;
            const preserveOutlinePadding = options.preserveOutlinePadding === true;
            let nextScaleX;
            let nextScaleY;
            if (preserveCrop) {
                const targetNatural = getImageNaturalSize(target);
                const sourceCropX = Math.max(0, Number(source.cropX) || 0);
                const sourceCropY = Math.max(0, Number(source.cropY) || 0);
                const sourceWidth = Math.max(1, Math.min(Number(source.width) || 1, targetNatural.width - sourceCropX));
                const sourceHeight = Math.max(1, Math.min(Number(source.height) || 1, targetNatural.height - sourceCropY));
                target.set({
                    cropX: Math.min(sourceCropX, Math.max(0, targetNatural.width - 1)),
                    cropY: Math.min(sourceCropY, Math.max(0, targetNatural.height - 1)),
                    width: sourceWidth,
                    height: sourceHeight,
                    clipPath: null,
                });
                nextScaleX = Math.abs(Number(source.scaleX) || 1);
                nextScaleY = Math.abs(Number(source.scaleY) || 1);
            } else if (preserveOutlinePadding) {
                const targetNatural = getImageNaturalSize(target);
                const sourceNatural = getImageNaturalSize(source);
                const visibleSourceWidth = Math.max(1, Number(source.width) || sourceNatural.width || 1);
                const visibleSourceHeight = Math.max(1, Number(source.height) || sourceNatural.height || 1);
                const targetWidth = Math.max(1, targetNatural.width || target.width || 1);
                const targetHeight = Math.max(1, targetNatural.height || target.height || 1);
                const sourceScaleX = Math.abs(Number(source.scaleX) || 1);
                const sourceScaleY = Math.abs(Number(source.scaleY) || 1);

                target.set({
                    cropX: 0,
                    cropY: 0,
                    width: targetWidth,
                    height: targetHeight,
                    clipPath: null,
                });
                nextScaleX = (visibleSourceWidth * sourceScaleX) / targetWidth;
                nextScaleY = (visibleSourceHeight * sourceScaleY) / targetHeight;
            } else {
                const cover = applyImageCoverToFrame(target, scaledWidth, scaledHeight);
                nextScaleX = scaledWidth / cover.cropWidth;
                nextScaleY = scaledHeight / cover.cropHeight;
            }
            target.set({
                left: source.left,
                top: source.top,
                angle: source.angle,
                scaleX: (source.scaleX || 1) < 0 ? -nextScaleX : nextScaleX,
                scaleY: (source.scaleY || 1) < 0 ? -nextScaleY : nextScaleY,
                originX: source.originX,
                originY: source.originY,
                flipX: source.flipX,
                flipY: source.flipY,
                opacity: source.opacity,
                shadow: source.shadow,
                stroke: source.stroke,
                strokeWidth: source.strokeWidth,
                strokeDashArray: source.strokeDashArray,
                strokeUniform: source.strokeUniform,
                imageStrokeStyle: source.imageStrokeStyle,
                aaImageEffectPreset: source.aaImageEffectPreset || 'none',
                aaImageOverlayGradient: source.aaImageOverlayGradient || '',
                aaImageFrameShape: source.aaImageFrameShape || '',
                aaImageRemoveColor: source.aaImageRemoveColor || '',
                aaRemovedBg: options.aaRemovedBg === true || source.aaRemovedBg === true,
                aaRemovedBgSrc: hasOption('aaRemovedBgSrc') ? options.aaRemovedBgSrc : (source.aaRemovedBgSrc || ''),
                aaImageOutlineBaseSrc: hasOption('aaImageOutlineBaseSrc') ? options.aaImageOutlineBaseSrc : (source.aaImageOutlineBaseSrc || ''),
                aaImageOutlineColor: hasOption('aaImageOutlineColor') ? options.aaImageOutlineColor : (source.aaImageOutlineColor || '#ffffff'),
                aaImageOutlineWidth: Math.max(0, Number(options.aaImageOutlineWidth ?? source.aaImageOutlineWidth) || 0),
                aaImageOutlineDraftColor: hasOption('aaImageOutlineDraftColor') ? options.aaImageOutlineDraftColor : (source.aaImageOutlineDraftColor || source.aaImageOutlineColor || '#ffffff'),
                aaImageOutlineDraftWidth: Math.max(0, Number(options.aaImageOutlineDraftWidth ?? source.aaImageOutlineDraftWidth ?? source.aaImageOutlineWidth) || 0),
                aaImageOutlineAppliedSrc: hasOption('aaImageOutlineAppliedSrc') ? options.aaImageOutlineAppliedSrc : (source.aaImageOutlineAppliedSrc || ''),
                aaImageAlphaOutline: hasOption('aaImageAlphaOutline') ? options.aaImageAlphaOutline === true : source.aaImageAlphaOutline === true,
                aaImageOutlineAlphaEligible: hasOption('aaImageOutlineAlphaEligible') ? options.aaImageOutlineAlphaEligible === true : source.aaImageOutlineAlphaEligible === true,
                aaOriginalImageSrc: hasOption('aaOriginalImageSrc') ? options.aaOriginalImageSrc : (source.aaOriginalImageSrc || readImageSourceUrl(target)),
                aaOriginalImageName: hasOption('aaOriginalImageName') ? options.aaOriginalImageName : (source.aaOriginalImageName || source.galleryImageName || ''),
                aaAnimation: source.aaAnimation || source.customAnimation || source.animationPreset || 'none',
                customAnimation: source.customAnimation || source.aaAnimation || source.animationPreset || 'none',
                animationPreset: source.animationPreset || source.aaAnimation || source.customAnimation || 'none',
                borderRadius: options.borderRadius ?? source.borderRadius ?? source.rx ?? source.ry ?? 0,
                link: source.link || '',
                copyText: source.copyText || '',
                copyFeedback: source.copyFeedback || '',
                locked: source.locked === true,
                customType: options.customType || source.customType || 'image',
                isGalleryPhoto: options.isGalleryPhoto === true || source.isGalleryPhoto === true,
                galleryZoom: options.galleryZoom === true || source.galleryZoom === true,
                galleryImageSrc: hasOption('galleryImageSrc') ? options.galleryImageSrc : (source.galleryZoom ? (target.getSrc?.() ||
                    '') : (source
                    .galleryImageSrc || '')),
                galleryImageName: hasOption('galleryImageName') ? options.galleryImageName : (source.galleryImageName || ''),
            });
            if (Array.isArray(source.filters) && source.filters.length) {
                target.set('filters', source.filters.slice());
                if (typeof target.applyFilters === 'function') target.applyFilters();
            }
            applyImageBorderRadius(target, target.borderRadius || 0);
            if (target.aaImageFrameShape && target.aaImageFrameShape !== 'none' && target.aaImageFrameShape !== 'rounded' &&
                typeof createImageFrameClipPath === 'function') {
                target.set('clipPath', createImageFrameClipPath(target, target.aaImageFrameShape));
            }
            if (source.locked === true) {
                setObjectLocked(target, true);
            }
        }

        function replaceImageObject(source, replacement, options = {}) {
            if (!source || !replacement || !state.canvas) return false;
            if (source.locked === true && options.allowLockedReplace !== true) {
                setStatus('Gambar terkunci. Unlock dulu untuk mengganti gambar.', 'error');
                return false;
            }
            copyImageReplaceStyle(source, replacement, options);
            const index = Math.max(0, state.canvas.getObjects().indexOf(source));
            state.canvas.remove(source);
            state.canvas.insertAt(replacement, index, false);
            state.canvas.setActiveObject(replacement);
            replacement.setCoords();
            state.canvas.requestRenderAll();
            syncInspector();
            snapshot();
            return true;
        }

        function isRemovedBgImage(object = state.canvas?.getActiveObject()) {
            if (!object || object.type !== 'image') return false;
            if (object.aaRemovedBg === true) return true;
            const src = readImageSourceUrl(object);
            return isRemoveBgAssetSource(src);
        }

        function isRemoveBgAssetSource(src) {
            return /(?:^|\/)remove-bg-[^/?#]+\.png(?:[?#].*)?$/i.test(String(src || '').trim());
        }

        function findImageForOutline(object) {
            if (!object) return null;
            if (object.type === 'image') return object;
            if (object.type !== 'group' || typeof object.getObjects !== 'function') return null;
            const children = object.getObjects() || [];
            for (const child of children) {
                const image = findImageForOutline(child);
                if (image) return image;
            }
            return null;
        }

        function isImageOutlineCandidate(object = state.canvas?.getActiveObject()) {
            return Boolean(object && object !== state.cropBox && findImageForOutline(object));
        }

        function hasCachedImageOutlineAlpha(object = state.canvas?.getActiveObject()) {
            if (!isImageOutlineCandidate(object)) return false;
            const image = findImageForOutline(object);
            const currentSrc = String(readImageSourceUrl(image) || '').trim();
            if (
                object.aaRemovedBg === true ||
                image?.aaRemovedBg === true ||
                isRemoveBgAssetSource(currentSrc) ||
                isRemoveBgAssetSource(object.aaRemovedBgSrc || image?.aaRemovedBgSrc)
            ) {
                return true;
            }
            return object.aaImageOutlineAlphaEligible === true || image?.aaImageOutlineAlphaEligible === true;
        }

        function isImageOutlineTarget(object = state.canvas?.getActiveObject()) {
            return hasCachedImageOutlineAlpha(object);
        }

        function sourceHasTransparentAlpha(src) {
            return new Promise(resolve => {
                const source = String(src || '').trim();
                if (!source) {
                    resolve(false);
                    return;
                }
                const image = new Image();
                image.crossOrigin = 'anonymous';
                image.onload = () => {
                    try {
                        const width = Math.max(1, image.naturalWidth || image.width || 1);
                        const height = Math.max(1, image.naturalHeight || image.height || 1);
                        const maxSize = 160;
                        const scale = Math.min(1, maxSize / Math.max(width, height));
                        const sampleWidth = Math.max(1, Math.round(width * scale));
                        const sampleHeight = Math.max(1, Math.round(height * scale));
                        const canvas = document.createElement('canvas');
                        canvas.width = sampleWidth;
                        canvas.height = sampleHeight;
                        const ctx = canvas.getContext('2d', {
                            willReadFrequently: true,
                        });
                        if (!ctx) {
                            resolve(false);
                            return;
                        }
                        ctx.drawImage(image, 0, 0, sampleWidth, sampleHeight);
                        const data = ctx.getImageData(0, 0, sampleWidth, sampleHeight).data;
                        for (let index = 3; index < data.length; index += 4) {
                            if (data[index] < 250) {
                                resolve(true);
                                return;
                            }
                        }
                        resolve(false);
                    } catch (error) {
                        resolve(false);
                    }
                };
                image.onerror = () => resolve(false);
                image.src = source;
            });
        }

        async function detectImageOutlineAlpha(object = state.canvas?.getActiveObject()) {
            if (!isImageOutlineCandidate(object)) return false;
            const image = findImageForOutline(object);
            const currentSrc = String(readImageSourceUrl(image) || '').trim();
            const removedBgSrc = String(object.aaRemovedBgSrc || image?.aaRemovedBgSrc || '').trim();
            if (
                object.aaRemovedBg === true ||
                image?.aaRemovedBg === true ||
                isRemoveBgAssetSource(currentSrc) ||
                isRemoveBgAssetSource(removedBgSrc)
            ) {
                object.aaImageOutlineAlphaEligible = true;
                if (image) image.aaImageOutlineAlphaEligible = true;
                return true;
            }
            if (object.aaImageOutlineAlphaEligible === true || image?.aaImageOutlineAlphaEligible === true) return true;
            if (object.aaImageOutlineAlphaEligible === false || image?.aaImageOutlineAlphaEligible === false) return false;
            if (object.__aaImageOutlineAlphaPromise) return object.__aaImageOutlineAlphaPromise;

            const snapshotSrc = imageOutlineObjectSnapshotSource(object);
            const source = snapshotSrc || String(object.aaImageOutlineBaseSrc || image?.aaImageOutlineBaseSrc ||
                object.aaOriginalImageSrc || image?.aaOriginalImageSrc || currentSrc).trim();
            object.__aaImageOutlineAlphaPromise = sourceHasTransparentAlpha(source).then(hasAlpha => {
                object.aaImageOutlineAlphaEligible = hasAlpha;
                if (image) image.aaImageOutlineAlphaEligible = hasAlpha;
                object.__aaImageOutlineAlphaPromise = null;
                return hasAlpha;
            }).catch(() => {
                object.aaImageOutlineAlphaEligible = false;
                if (image) image.aaImageOutlineAlphaEligible = false;
                object.__aaImageOutlineAlphaPromise = null;
                return false;
            });
            return object.__aaImageOutlineAlphaPromise;
        }

        function syncImageOutlineAvailability(object = state.canvas?.getActiveObject()) {
            const active = object || state.canvas?.getActiveObject();
            if (!els.aaContextImageOutlineBtn || !isImageOutlineCandidate(active)) return;
            const token = Symbol('outlineAlphaCheck');
            active.__aaImageOutlineAlphaToken = token;
            detectImageOutlineAlpha(active).then(canOutline => {
                if (active.__aaImageOutlineAlphaToken !== token) return;
                if (state.canvas?.getActiveObject?.() !== active) return;
                els.aaContextImageOutlineBtn.hidden = !canOutline;
                els.aaContextImageOutlineBtn.disabled = !canOutline || active.__aaOutlineProcessing === true;
                if (!canOutline && typeof getActiveLeftDrawerPanelKey === 'function' &&
                    getActiveLeftDrawerPanelKey() === 'image-outline' &&
                    typeof closeLeftDrawerPanel === 'function') {
                    closeLeftDrawerPanel();
                }
            });
        }

        function imageOutlineObjectSnapshotSource(object) {
            if (!object || object.type !== 'group' || typeof object.toDataURL !== 'function') return '';
            try {
                return object.toDataURL({
                    format: 'png',
                    multiplier: 1,
                    enableRetinaScaling: false,
                }) || '';
            } catch (error) {
                return '';
            }
        }

        function imageOutlineBaseSource(object) {
            if (!isImageOutlineCandidate(object)) return '';
            const image = findImageForOutline(object);
            const storedBaseSrc = String(object.aaImageOutlineBaseSrc || image?.aaImageOutlineBaseSrc || object.aaRemovedBgSrc || image?.aaRemovedBgSrc || '').trim();
            if (storedBaseSrc) return storedBaseSrc;

            const snapshotSrc = imageOutlineObjectSnapshotSource(object);
            const currentSrc = String(readImageSourceUrl(image) || '').trim();
            const originalSrc = String(object.aaOriginalImageSrc || image?.aaOriginalImageSrc || '').trim();
            const recoverableSrc = snapshotSrc || (isRemoveBgAssetSource(currentSrc) ? currentSrc :
                (isRemoveBgAssetSource(originalSrc) ? originalSrc : (originalSrc || currentSrc)));
            if (!recoverableSrc) return '';
            const isRemoveBgSource = isRemoveBgAssetSource(recoverableSrc);

            object.set?.({
                aaRemovedBg: object.aaRemovedBg === true || image?.aaRemovedBg === true || isRemoveBgSource,
                aaRemovedBgSrc: isRemoveBgSource ? recoverableSrc : (object.aaRemovedBgSrc || image?.aaRemovedBgSrc || ''),
                aaImageOutlineBaseSrc: recoverableSrc,
                aaOriginalImageSrc: recoverableSrc,
            });
            object.aaRemovedBg = object.aaRemovedBg === true || image?.aaRemovedBg === true || isRemoveBgSource;
            object.aaRemovedBgSrc = isRemoveBgSource ? recoverableSrc : (object.aaRemovedBgSrc || image?.aaRemovedBgSrc || '');
            object.aaImageOutlineBaseSrc = recoverableSrc;
            object.aaOriginalImageSrc = recoverableSrc;
            if (image && image !== object) {
                image.aaImageOutlineBaseSrc = recoverableSrc;
                image.aaOriginalImageSrc = recoverableSrc;
                if (isRemoveBgSource) {
                    image.aaRemovedBg = true;
                    image.aaRemovedBgSrc = recoverableSrc;
                }
            }
            return recoverableSrc;
        }

        function loadImageElementForProcessing(src) {
            return new Promise((resolve, reject) => {
                const image = new Image();
                image.crossOrigin = 'anonymous';
                image.onload = () => resolve(image);
                image.onerror = () => reject(new Error('Gambar tidak bisa diproses untuk outline.'));
                image.src = src;
            });
        }

        async function aaTrimTransparentImageDataUrl(src, options = {}) {
            try {
                const image = await loadImageElementForProcessing(src);
                const width = Math.max(1, image.naturalWidth || image.width || 1);
                const height = Math.max(1, image.naturalHeight || image.height || 1);
                const maxPixels = Math.max(1, Number(options.maxPixels) || 16000000);
                if (width * height > maxPixels) {
                    return {
                        src,
                        trimmed: false,
                        trim: null,
                    };
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d', {
                    willReadFrequently: true,
                });
                if (!ctx) {
                    return {
                        src,
                        trimmed: false,
                        trim: null,
                    };
                }

                ctx.clearRect(0, 0, width, height);
                ctx.drawImage(image, 0, 0, width, height);

                const alphaThreshold = Math.max(0, Math.min(255, Number(options.alphaThreshold) || 8));
                const pixels = ctx.getImageData(0, 0, width, height).data;
                let minX = width;
                let minY = height;
                let maxX = -1;
                let maxY = -1;

                for (let y = 0; y < height; y += 1) {
                    const row = y * width * 4;
                    for (let x = 0; x < width; x += 1) {
                        if (pixels[row + x * 4 + 3] <= alphaThreshold) continue;
                        if (x < minX) minX = x;
                        if (y < minY) minY = y;
                        if (x > maxX) maxX = x;
                        if (y > maxY) maxY = y;
                    }
                }

                if (maxX < minX || maxY < minY) {
                    return {
                        src,
                        trimmed: false,
                        trim: null,
                    };
                }

                const padding = Math.max(0, Math.min(96, Math.round(Number(options.padding) || 18)));
                const cropX = Math.max(0, minX - padding);
                const cropY = Math.max(0, minY - padding);
                const cropRight = Math.min(width - 1, maxX + padding);
                const cropBottom = Math.min(height - 1, maxY + padding);
                const cropWidth = Math.max(1, cropRight - cropX + 1);
                const cropHeight = Math.max(1, cropBottom - cropY + 1);

                if (cropX === 0 && cropY === 0 && cropWidth === width && cropHeight === height) {
                    return {
                        src,
                        trimmed: false,
                        trim: null,
                    };
                }

                return {
                    src,
                    trimmed: true,
                    trim: {
                        x: cropX,
                        y: cropY,
                        width: cropWidth,
                        height: cropHeight,
                        originalWidth: width,
                        originalHeight: height,
                    },
                };
            } catch (error) {
                return {
                    src,
                    trimmed: false,
                    trim: null,
                };
            }
        }

        function aaApplyTrimmedSubjectPlacement(source, subject, trim) {
            if (!source || !subject || !trim || !window.fabric || !fabric.util || typeof source.calcTransformMatrix !==
                'function') {
                return false;
            }

            const sourceObjectWidth = Math.max(1, Number(source.width) || Number(trim.originalWidth) || 1);
            const sourceObjectHeight = Math.max(1, Number(source.height) || Number(trim.originalHeight) || 1);
            const sourceCropX = Math.max(0, Number(source.cropX) || 0);
            const sourceCropY = Math.max(0, Number(source.cropY) || 0);
            const naturalWidth = Math.max(1, Number(trim.originalWidth) || sourceObjectWidth);
            const naturalHeight = Math.max(1, Number(trim.originalHeight) || sourceObjectHeight);
            const visibleSourceWidth = Math.max(1, Math.min(naturalWidth - sourceCropX, sourceObjectWidth));
            const visibleSourceHeight = Math.max(1, Math.min(naturalHeight - sourceCropY, sourceObjectHeight));
            const centerSourceX = Number(trim.x) + Number(trim.width) / 2;
            const centerSourceY = Number(trim.y) + Number(trim.height) / 2;

            const localX = -sourceObjectWidth / 2 + ((centerSourceX - sourceCropX) / visibleSourceWidth) *
                sourceObjectWidth;
            const localY = -sourceObjectHeight / 2 + ((centerSourceY - sourceCropY) / visibleSourceHeight) *
                sourceObjectHeight;
            const center = fabric.util.transformPoint(new fabric.Point(localX, localY), source.calcTransformMatrix());
            const scaleMultiplierX = sourceObjectWidth / visibleSourceWidth;
            const scaleMultiplierY = sourceObjectHeight / visibleSourceHeight;
            const signX = (Number(source.scaleX) || 1) < 0 ? -1 : 1;
            const signY = (Number(source.scaleY) || 1) < 0 ? -1 : 1;

            subject.set({
                cropX: Math.max(0, Number(trim.x) || 0),
                cropY: Math.max(0, Number(trim.y) || 0),
                width: Math.max(1, Number(trim.width) || subject.width || 1),
                height: Math.max(1, Number(trim.height) || subject.height || 1),
                left: center.x,
                top: center.y,
                originX: 'center',
                originY: 'center',
                angle: source.angle,
                scaleX: signX * Math.abs(Number(source.scaleX) || 1) * scaleMultiplierX,
                scaleY: signY * Math.abs(Number(source.scaleY) || 1) * scaleMultiplierY,
                flipX: source.flipX,
                flipY: source.flipY,
                clipPath: null,
            });
            subject.setCoords?.();
            return true;
        }

        function aaApplyComponentSubjectPlacement(source, subject, trim) {
            if (!source || !subject || !trim || !window.fabric || !fabric.util || typeof source.calcTransformMatrix !==
                'function') {
                return false;
            }

            const sourceObjectWidth = Math.max(1, Number(source.width) || Number(trim.originalWidth) || 1);
            const sourceObjectHeight = Math.max(1, Number(source.height) || Number(trim.originalHeight) || 1);
            const sourceCropX = Math.max(0, Number(source.cropX) || 0);
            const sourceCropY = Math.max(0, Number(source.cropY) || 0);
            const naturalWidth = Math.max(1, Number(trim.originalWidth) || sourceObjectWidth);
            const naturalHeight = Math.max(1, Number(trim.originalHeight) || sourceObjectHeight);
            const visibleSourceWidth = Math.max(1, Math.min(naturalWidth - sourceCropX, sourceObjectWidth));
            const visibleSourceHeight = Math.max(1, Math.min(naturalHeight - sourceCropY, sourceObjectHeight));
            const centerSourceX = Number(trim.x) + Number(trim.width) / 2;
            const centerSourceY = Number(trim.y) + Number(trim.height) / 2;
            const localX = -sourceObjectWidth / 2 + ((centerSourceX - sourceCropX) / visibleSourceWidth) *
                sourceObjectWidth;
            const localY = -sourceObjectHeight / 2 + ((centerSourceY - sourceCropY) / visibleSourceHeight) *
                sourceObjectHeight;
            const center = fabric.util.transformPoint(new fabric.Point(localX, localY), source.calcTransformMatrix());
            const scaleMultiplierX = sourceObjectWidth / visibleSourceWidth;
            const scaleMultiplierY = sourceObjectHeight / visibleSourceHeight;
            const signX = (Number(source.scaleX) || 1) < 0 ? -1 : 1;
            const signY = (Number(source.scaleY) || 1) < 0 ? -1 : 1;

            subject.set({
                cropX: 0,
                cropY: 0,
                width: Math.max(1, Number(trim.width) || subject.width || 1),
                height: Math.max(1, Number(trim.height) || subject.height || 1),
                left: center.x,
                top: center.y,
                originX: 'center',
                originY: 'center',
                angle: source.angle,
                scaleX: signX * Math.abs(Number(source.scaleX) || 1) * scaleMultiplierX,
                scaleY: signY * Math.abs(Number(source.scaleY) || 1) * scaleMultiplierY,
                flipX: source.flipX,
                flipY: source.flipY,
                clipPath: null,
            });
            subject.setCoords?.();
            return true;
        }

        async function aaDetectTransparentImageComponents(src, options = {}) {
            try {
                const image = await loadImageElementForProcessing(src);
                const width = Math.max(1, image.naturalWidth || image.width || 1);
                const height = Math.max(1, image.naturalHeight || image.height || 1);
                const totalPixels = width * height;
                const maxPixels = Math.max(1, Number(options.maxPixels) || 6000000);
                if (totalPixels > maxPixels) return [];

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d', {
                    willReadFrequently: true,
                });
                if (!ctx) return [];

                ctx.clearRect(0, 0, width, height);
                ctx.drawImage(image, 0, 0, width, height);

                const alphaThreshold = Math.max(0, Math.min(255, Number(options.alphaThreshold) || 8));
                const padding = Math.max(0, Math.min(96, Math.round(Number(options.padding) || 18)));
                const minArea = Math.max(1200, Math.round(totalPixels * (Number(options.minAreaRatio) || 0.002)));
                const minDimension = Math.max(16, Math.round(Number(options.minDimension) || 34));
                const maxComponents = Math.max(2, Math.min(12, Math.round(Number(options.maxComponents) || 8)));
                const pixels = ctx.getImageData(0, 0, width, height).data;
                const visited = new Uint8Array(totalPixels);
                const queue = [];
                const components = [];
                let totalOpaque = 0;

                for (let index = 0; index < totalPixels; index += 1) {
                    if (pixels[index * 4 + 3] <= alphaThreshold) continue;
                    totalOpaque += 1;
                    if (visited[index]) continue;

                    visited[index] = 1;
                    queue.length = 0;
                    queue.push(index);

                    let head = 0;
                    let area = 0;
                    let minX = width;
                    let minY = height;
                    let maxX = -1;
                    let maxY = -1;

                    while (head < queue.length) {
                        const current = queue[head];
                        head += 1;
                        const x = current % width;
                        const y = (current - x) / width;

                        area += 1;
                        if (x < minX) minX = x;
                        if (y < minY) minY = y;
                        if (x > maxX) maxX = x;
                        if (y > maxY) maxY = y;

                        const enqueue = next => {
                            if (visited[next] || pixels[next * 4 + 3] <= alphaThreshold) return;
                            visited[next] = 1;
                            queue.push(next);
                        };
                        if (x > 0) enqueue(current - 1);
                        if (x < width - 1) enqueue(current + 1);
                        if (y > 0) enqueue(current - width);
                        if (y < height - 1) enqueue(current + width);
                    }

                    const boxWidth = maxX - minX + 1;
                    const boxHeight = maxY - minY + 1;
                    if (area < minArea || boxWidth < minDimension || boxHeight < minDimension) continue;

                    const cropX = Math.max(0, minX - padding);
                    const cropY = Math.max(0, minY - padding);
                    const cropRight = Math.min(width - 1, maxX + padding);
                    const cropBottom = Math.min(height - 1, maxY + padding);
                    components.push({
                        x: cropX,
                        y: cropY,
                        width: Math.max(1, cropRight - cropX + 1),
                        height: Math.max(1, cropBottom - cropY + 1),
                        originalWidth: width,
                        originalHeight: height,
                        area,
                    });
                }

                if (components.length < 2) return [];

                components.sort((a, b) => b.area - a.area);
                const selected = components.slice(0, maxComponents);
                const selectedArea = selected.reduce((sum, item) => sum + item.area, 0);
                const ignoredArea = Math.max(0, totalOpaque - selectedArea);
                if (components.length > maxComponents && ignoredArea / Math.max(1, totalOpaque) > 0.25) {
                    return [];
                }

                return selected.sort((a, b) => (a.y - b.y) || (a.x - b.x));
            } catch (error) {
                console.warn('[AdaAcara Magic Layer] Split component gagal:', error);
                return [];
            }
        }

        async function aaCreateSplitSubjectImages(src, source, baseProps = {}, options = {}) {
            const components = await aaDetectTransparentImageComponents(src, options);
            if (!components.length) return null;

            const sourceImage = await loadImageElementForProcessing(src).catch(() => null);
            if (!sourceImage) return null;

            const filteredComponents = aaFilterMagicLayerTextComponents(components, options.ocrBlueprint || null);
            if (!filteredComponents.length) {
                return null;
            }

            if (options.ocrBlueprint && aaMagicLayerSplitLooksTooThin(components, filteredComponents)) {
                return null;
            }

            const images = [];
            let totalDataUrlLength = 0;
            const maxDataUrlLength = Math.max(250000, Number(options.maxDataUrlLength) || 2800000);
            for (let index = 0; index < filteredComponents.length; index += 1) {
                const component = filteredComponents[index];
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(Number(component.width) || 1));
                canvas.height = Math.max(1, Math.round(Number(component.height) || 1));
                const ctx = canvas.getContext('2d', {
                    willReadFrequently: false,
                });
                if (!ctx) return null;

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(
                    sourceImage,
                    Math.max(0, Math.round(Number(component.x) || 0)),
                    Math.max(0, Math.round(Number(component.y) || 0)),
                    canvas.width,
                    canvas.height,
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                const componentSrc = canvas.toDataURL('image/png');
                totalDataUrlLength += componentSrc.length;
                if (totalDataUrlLength > maxDataUrlLength) return null;

                const image = await loadFabricImage(componentSrc);
                if (!image) return null;

                image.set({
                    src: componentSrc,
                    objectCaching: false,
                });
                aaApplyComponentSubjectPlacement(source, image, component);
                image.set({
                    ...baseProps,
                    src: componentSrc,
                    aaOriginalImageSrc: componentSrc,
                    aaRemovedBgSrc: componentSrc,
                    aaImageOutlineBaseSrc: componentSrc,
                    aaMagicLayerAutoTrim: true,
                    aaMagicLayerSplitComponent: true,
                    aaMagicLayerComponentIndex: index + 1,
                    galleryImageName: `magic-subject-${index + 1}.png`,
                });
                images.push(image);
            }

            return images.length >= 2 || (components.length > filteredComponents.length && options.ocrBlueprint) ? images : null;
        }

        function aaMagicLayerSplitLooksTooThin(components = [], filteredComponents = []) {
            if (!Array.isArray(components) || !Array.isArray(filteredComponents) || !components.length || !filteredComponents.length) {
                return false;
            }

            const areaOf = list => list.reduce((sum, component) => {
                const box = aaMagicLayerSourceBox(component);
                return sum + box.width * box.height;
            }, 0);
            const originalArea = Math.max(1, areaOf(components));
            const filteredArea = areaOf(filteredComponents);

            return filteredComponents.length <= 1 || filteredArea / originalArea < 0.18;
        }

        function aaFilterMagicLayerTextComponents(components = [], blueprint = null) {
            if (!blueprint || !Array.isArray(blueprint.blocks) || !blueprint.blocks.length) return components;

            return components.filter(component => !aaMagicLayerComponentLooksLikeText(component, blueprint));
        }

        function aaMagicLayerComponentLooksLikeText(component, blueprint) {
            const componentBox = aaMagicLayerSourceBox(component);
            const componentArea = Math.max(1, componentBox.width * componentBox.height);
            const scaleX = Math.max(0.0001, Number(component.originalWidth || 1) / Math.max(1, Number(blueprint.imageWidth || component.originalWidth || 1)));
            const scaleY = Math.max(0.0001, Number(component.originalHeight || 1) / Math.max(1, Number(blueprint.imageHeight || component.originalHeight || 1)));
            const componentCenter = {
                x: componentBox.x + componentBox.width / 2,
                y: componentBox.y + componentBox.height / 2,
            };

            for (const block of blueprint.blocks) {
                if (!block || (Number(block.confidence) || 0) < 0.28) continue;
                const textBox = aaInflateSourceBox({
                    x: (Number(block.x) || 0) * scaleX,
                    y: (Number(block.y) || 0) * scaleY,
                    width: Math.max(1, (Number(block.width) || 1) * scaleX),
                    height: Math.max(1, (Number(block.height) || 1) * scaleY),
                }, 0.24, 10);
                const overlap = aaSourceBoxIntersectionArea(componentBox, textBox);
                if (overlap <= 0) continue;

                const textArea = Math.max(1, textBox.width * textBox.height);
                const componentOverlap = overlap / componentArea;
                const textOverlap = overlap / textArea;
                const centerInside = componentCenter.x >= textBox.x &&
                    componentCenter.x <= textBox.x + textBox.width &&
                    componentCenter.y >= textBox.y &&
                    componentCenter.y <= textBox.y + textBox.height;

                if (componentOverlap >= 0.30 || textOverlap >= 0.20 || (centerInside && componentOverlap >= 0.08)) {
                    return true;
                }
            }

            return false;
        }

        function aaMagicLayerSourceBox(box = {}) {
            return {
                x: Math.max(0, Number(box.x) || 0),
                y: Math.max(0, Number(box.y) || 0),
                width: Math.max(1, Number(box.width) || 1),
                height: Math.max(1, Number(box.height) || 1),
            };
        }

        function aaInflateSourceBox(box = {}, ratio = 0.2, minPadding = 8) {
            const safeBox = aaMagicLayerSourceBox(box);
            const padX = Math.max(minPadding, safeBox.width * ratio);
            const padY = Math.max(minPadding, safeBox.height * ratio);

            return {
                x: safeBox.x - padX,
                y: safeBox.y - padY,
                width: safeBox.width + padX * 2,
                height: safeBox.height + padY * 2,
            };
        }

        function aaSourceBoxIntersectionArea(a = {}, b = {}) {
            const left = Math.max(Number(a.x) || 0, Number(b.x) || 0);
            const top = Math.max(Number(a.y) || 0, Number(b.y) || 0);
            const right = Math.min((Number(a.x) || 0) + Math.max(0, Number(a.width) || 0), (Number(b.x) || 0) + Math.max(0, Number(b.width) || 0));
            const bottom = Math.min((Number(a.y) || 0) + Math.max(0, Number(a.height) || 0), (Number(b.y) || 0) + Math.max(0, Number(b.height) || 0));

            return Math.max(0, right - left) * Math.max(0, bottom - top);
        }

        function createAlphaOutlineImageDataUrl(sourceImage, outlineColor, outlineWidth) {
            const width = Math.max(1, sourceImage.naturalWidth || sourceImage.width || 1);
            const height = Math.max(1, sourceImage.naturalHeight || sourceImage.height || 1);
            const radius = Math.max(0, Math.min(60, Math.round(Number(outlineWidth) || 0)));
            const padding = radius + 2;
            if ((width + padding * 2) * (height + padding * 2) > 16000000) {
                throw new Error('Gambar terlalu besar untuk outline. Perkecil gambar lalu coba lagi.');
            }
            const canvas = document.createElement('canvas');
            canvas.width = width + padding * 2;
            canvas.height = height + padding * 2;
            const ctx = canvas.getContext('2d', {
                willReadFrequently: false,
            });
            if (!ctx) {
                throw new Error('Browser belum bisa memproses outline gambar ini.');
            }

            if (radius > 0) {
                ctx.save();
                for (let x = -radius; x <= radius; x += 2) {
                    for (let y = -radius; y <= radius; y += 2) {
                        if ((x * x) + (y * y) <= radius * radius) {
                            ctx.drawImage(sourceImage, padding + x, padding + y, width, height);
                        }
                    }
                }
                ctx.globalCompositeOperation = 'source-in';
                ctx.fillStyle = outlineColor || '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.restore();
            }

            ctx.globalCompositeOperation = 'source-over';
            ctx.drawImage(sourceImage, padding, padding, width, height);
            return canvas.toDataURL('image/png');
        }

        async function applyImageAlphaOutline(options = {}) {
            const active = state.canvas?.getActiveObject();
            if (!isImageOutlineTarget(active)) {
                setStatus('Outline hanya aktif untuk gambar.', 'error');
                return;
            }
            if (guardLockedImageAction('menambah outline', active)) return;
            if (active.__aaOutlineProcessing === true) {
                state.pendingImageOutlineOptions = {
                    ...options,
                };
                return;
            }

            const color = normalizeColor(options.color || active.aaImageOutlineColor || '#ffffff');
            const width = Math.max(0, Math.min(60, Math.round(Number(options.width ?? active.aaImageOutlineWidth ?? 12) || 0)));
            const baseSrc = imageOutlineBaseSource(active);
            if (!baseSrc) {
                setStatus('Sumber gambar untuk outline tidak ditemukan.', 'error');
                return;
            }

            active.__aaOutlineProcessing = true;
            state.imageProcessTarget = active;
            const keepPopoverOpen = options.keepPopover !== false &&
                els.aaContextImageOutlinePopover?.classList.contains('is-open') === true;
            syncContextToolbar(active);
            showImageProcessOverlay(active, width > 0 ? 'Membuat outline...' : 'Reset outline...');
            setStatus(width > 0 ? 'Membuat outline gambar...' : 'Menghapus outline gambar...', 'saving');

            try {
                const sourceImage = await loadImageElementForProcessing(baseSrc);
                const outlinedSrc = width > 0 ? createAlphaOutlineImageDataUrl(sourceImage, color, width) : baseSrc;
                const replacement = await loadFabricImage(outlinedSrc);
                if (!replacement) {
                    throw new Error('Gagal memasang outline gambar.');
                }
                replacement.set({
                    src: outlinedSrc,
                    objectCaching: false,
                });
                const baseIsRemoveBg = isRemoveBgAssetSource(baseSrc);
                const replaced = replaceImageObject(active, replacement, {
                    customType: active.customType || 'image',
                    allowLockedReplace: true,
                    preserveCrop: false,
                    preserveOutlinePadding: true,
                    aaRemovedBg: active.aaRemovedBg === true || baseIsRemoveBg,
                    aaRemovedBgSrc: baseIsRemoveBg ? (active.aaRemovedBgSrc || baseSrc) : '',
                    aaImageOutlineBaseSrc: baseSrc,
                    aaImageOutlineColor: color,
                    aaImageOutlineWidth: width,
                    aaImageOutlineDraftColor: color,
                    aaImageOutlineDraftWidth: width,
                    aaImageOutlineAppliedSrc: width > 0 ? outlinedSrc : '',
                    aaImageAlphaOutline: width > 0,
                    aaImageOutlineAlphaEligible: true,
                    aaOriginalImageSrc: baseSrc,
                    aaOriginalImageName: active.aaOriginalImageName || active.galleryImageName || 'image.png',
                    galleryImageSrc: active.galleryZoom ? outlinedSrc : active.galleryImageSrc || '',
                    galleryImageName: active.galleryImageName || 'image.png',
                });
                if (!replaced) {
                    throw new Error('Gagal memasang outline gambar.');
                }
                if (width > 0 && typeof aaRememberRecentColor === 'function') {
                    aaRememberRecentColor(color);
                }
                setStatus(width > 0 ? 'Outline gambar berhasil diterapkan.' : 'Outline gambar direset.');
            } catch (error) {
                setStatus(error?.message || 'Outline gambar gagal diproses.', 'error');
            } finally {
                const pendingOptions = state.pendingImageOutlineOptions || null;
                state.pendingImageOutlineOptions = null;
                const currentOutlineTarget = state.canvas?.getActiveObject();
                if (currentOutlineTarget && currentOutlineTarget !== active && isImageOutlineTarget(currentOutlineTarget)) {
                    currentOutlineTarget.__aaOutlineProcessing = false;
                }
                active.__aaOutlineProcessing = false;
                state.imageProcessTarget = null;
                hideImageProcessOverlay();
                const nextActive = state.canvas?.getActiveObject();
                syncContextToolbar(nextActive);
                syncImageOutlineControl?.(nextActive);
                if (keepPopoverOpen && isImageOutlineTarget(nextActive)) {
                    els.aaContextImageOutlinePopover?.classList.add('is-open');
                    requestAnimationFrame(positionImageOutlinePopover);
                }
                if (pendingOptions) {
                    window.setTimeout(() => applyImageAlphaOutline(pendingOptions), 40);
                }
            }
        }

        async function applyOutlineToRemovedBgImage(options = {}) {
            return applyImageAlphaOutline(options);
        }

        function getImageObjectScreenRect(object) {
            if (!object || !state.canvas?.upperCanvasEl) return null;
            const canvasRect = state.canvas.upperCanvasEl.getBoundingClientRect();
            const objectRect = object.getBoundingRect(true, true);
            const scaleX = canvasRect.width / Math.max(1, state.canvas.getWidth());
            const scaleY = canvasRect.height / Math.max(1, state.canvas.getHeight());
            const width = Math.max(1, objectRect.width * scaleX);
            const height = Math.max(1, objectRect.height * scaleY);
            return {
                left: canvasRect.left + objectRect.left * scaleX,
                top: canvasRect.top + objectRect.top * scaleY,
                width,
                height,
            };
        }

        function ensureImageProcessOverlay() {
            if (state.imageProcessOverlay && document.body.contains(state.imageProcessOverlay)) {
                return state.imageProcessOverlay;
            }
            const overlay = document.createElement('div');
            overlay.className = 'aa-image-process-overlay';
            overlay.innerHTML = '<span class="aa-image-process-shimmer" aria-hidden="true"></span><span class="aa-image-process-card"><i class="fa fa-circle-notch" aria-hidden="true"></i><span>Remove BG...</span></span>';
            document.body.appendChild(overlay);
            state.imageProcessOverlay = overlay;
            return overlay;
        }

        function positionImageProcessOverlay(object) {
            const overlay = state.imageProcessOverlay;
            const rect = getImageObjectScreenRect(object);
            if (!overlay || !rect) return false;
            overlay.style.left = `${Math.round(rect.left)}px`;
            overlay.style.top = `${Math.round(rect.top)}px`;
            overlay.style.width = `${Math.round(rect.width)}px`;
            overlay.style.height = `${Math.round(rect.height)}px`;
            return true;
        }

        function showImageProcessOverlay(object, label = 'Remove BG...') {
            const overlay = ensureImageProcessOverlay();
            const labelEl = overlay.querySelector('.aa-image-process-card span');
            if (labelEl) labelEl.textContent = label;
            if (!positionImageProcessOverlay(object)) return;
            overlay.classList.remove('is-leaving');
            requestAnimationFrame(() => overlay.classList.add('is-visible'));
            const sync = () => {
                if (!state.imageProcessTarget || !state.imageProcessOverlay?.classList.contains('is-visible')) return;
                positionImageProcessOverlay(state.imageProcessTarget);
            };
            state.imageProcessSync = sync;
            window.addEventListener('resize', sync);
            window.addEventListener('scroll', sync, true);
            state.canvas?.on?.('after:render', sync);
        }

        function hideImageProcessOverlay() {
            const overlay = state.imageProcessOverlay;
            if (!overlay) return;
            overlay.classList.add('is-leaving');
            overlay.classList.remove('is-visible');
            const sync = state.imageProcessSync;
            if (sync) {
                window.removeEventListener('resize', sync);
                window.removeEventListener('scroll', sync, true);
                state.canvas?.off?.('after:render', sync);
            }
            state.imageProcessSync = null;
            window.setTimeout(() => {
                if (overlay.classList.contains('is-visible')) return;
                overlay.remove();
                if (state.imageProcessOverlay === overlay) {
                    state.imageProcessOverlay = null;
                }
            }, 260);
        }

        function readRemoveBgResultSrc(data) {
            return String(data?.src || data?.url || data?.image_url || data?.imageUrl || data?.asset?.src || data?.item?.src || '').trim();
        }

        async function removeBackgroundFromActiveImage() {
            const active = state.canvas?.getActiveObject();
            if (!active || active.type !== 'image') {
                setStatus('Pilih gambar terlebih dahulu.', 'error');
                return;
            }
            if (guardAiPremiumFeature(null, 'Remove BG')) return;
            if (guardLockedImageAction('remove background')) return;
            if (!config.mediaRemoveBgUrl) {
                setStatus('Service Remove BG belum dikonfigurasi.', 'error');
                return;
            }
            const src = ensureImageOriginalSource(active) || readImageSourceUrl(active);
            if (!src) {
                setStatus('Sumber gambar tidak ditemukan.', 'error');
                return;
            }
            if (active.__aaBgRemoveProcessing === true) return;

            active.__aaBgRemoveProcessing = true;
            state.imageProcessTarget = active;
            syncContextToolbar(active);
            showImageProcessOverlay(active, 'Remove BG...');
            setStatus('Memproses Remove BG...', 'saving');

            try {
                const form = new FormData();
                form.append('image_url', src);
                form.append('page_id', String(config.pageId || ''));
                const response = await fetch(config.mediaRemoveBgUrl, {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const contentType = response.headers.get('content-type') || '';
                const data = contentType.includes('application/json') ? await response.json() : {};
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Service Remove BG belum tersedia.');
                }
                const resultSrc = readRemoveBgResultSrc(data);
                if (!resultSrc) {
                    throw new Error('Hasil Remove BG tidak memiliki URL gambar.');
                }
                showImageProcessOverlay(active, 'Memasang hasil...');
                const replacement = await loadFabricImage(resultSrc);
                if (!replacement) {
                    throw new Error('Gagal memuat hasil Remove BG.');
                }
                replacement.set({
                    src: resultSrc,
                    objectCaching: false,
                });
                replaceImageObject(active, replacement, {
                    customType: active.customType || 'image',
                    allowLockedReplace: false,
                    preserveCrop: true,
                    aaRemovedBg: true,
                    aaRemovedBgSrc: resultSrc,
                    aaImageOutlineBaseSrc: resultSrc,
                    aaImageOutlineColor: active.aaImageOutlineColor || '#ffffff',
                    aaImageOutlineWidth: 0,
                    aaImageOutlineAppliedSrc: '',
                    aaOriginalImageSrc: resultSrc,
                    aaOriginalImageName: active.galleryImageName || 'remove-bg.png',
                    galleryImageSrc: active.galleryZoom ? resultSrc : active.galleryImageSrc || '',
                    galleryImageName: active.galleryImageName || 'remove-bg.png',
                });
                await loadMedia({ force: true, silent: true });
                setStatus('Background gambar berhasil dihapus.');
            } catch (error) {
                setStatus(error?.message || 'Remove BG gagal diproses.', 'error');
            } finally {
                active.__aaBgRemoveProcessing = false;
                state.imageProcessTarget = null;
                hideImageProcessOverlay();
                syncContextToolbar(state.canvas?.getActiveObject());
            }
        }

        function setMagicLayerAiStatus(message = '', tone = '') {
            if (!els.aaMagicLayerAiStatus) return;
            els.aaMagicLayerAiStatus.textContent = message;
            els.aaMagicLayerAiStatus.classList.toggle('text-rose-600', tone === 'error');
            els.aaMagicLayerAiStatus.classList.toggle('text-teal-700', tone === 'success');
            els.aaMagicLayerAiStatus.classList.toggle('text-amber-700', tone === 'warning');
        }

        function normalizeMagicLayerBlocks(data = {}) {
            const imageWidth = Math.max(1, Number(data.imageWidth || 0));
            const imageHeight = Math.max(1, Number(data.imageHeight || 0));
            const safeColor = value => /^#[0-9a-f]{6}$/i.test(String(value || '')) ? String(value) : '';
            const safeId = value => String(value || '').replace(/[^a-z0-9_-]/ig, '').slice(0, 48);
            const safeEnum = (value, allowed, fallback) => {
                const normalized = String(value || '').toLowerCase().trim();
                return allowed.includes(normalized) ? normalized : fallback;
            };
            const blocks = Array.isArray(data.blocks) ? data.blocks : [];
            const shouldTreatCenterX = magicLayerLooksLikeCenterXBlocks(blocks, imageWidth);

            return {
                imageWidth,
                imageHeight,
                backgroundColor: safeColor(data.backgroundColor) || '',
                sections: Array.isArray(data.sections) ? data.sections.slice(0, 8).map(section => ({
                    id: safeId(section?.id),
                    kind: safeEnum(section?.kind, ['hero', 'details', 'location', 'rsvp', 'footer', 'other'], 'other'),
                    confidence: Math.max(0, Math.min(1, Number(section?.confidence || 0))),
                })).filter(section => section.id) : [],
                groups: Array.isArray(data.groups) ? data.groups.slice(0, 24).map(group => ({
                    id: safeId(group?.id),
                    kind: safeEnum(group?.kind, ['title_group', 'text_group', 'date_group', 'address_group', 'media_group', 'ornament_group', 'cta_group', 'other'], 'other'),
                    confidence: Math.max(0, Math.min(1, Number(group?.confidence || 0))),
                })).filter(group => group.id) : [],
                style: {
                    alignment: safeEnum(data.style?.alignment, ['left', 'center', 'right', 'mixed'], 'mixed'),
                    spacing: safeEnum(data.style?.spacing, ['tight', 'normal', 'airy'], 'normal'),
                    tone: String(data.style?.tone || '').slice(0, 80),
                    palette: Array.isArray(data.style?.palette) ? data.style.palette.filter(safeColor).slice(0, 8) : [],
                },
                blocks: blocks
                    .map((block, index) => ({
                        id: `magic-text-${Date.now()}-${index}`,
                        text: String(block?.text || '').trim().slice(0, 500),
                        confidence: Math.max(0, Math.min(1, Number(block?.confidence || 0))),
                        x: normalizeMagicLayerBlockX(block, imageWidth, shouldTreatCenterX),
                        y: Math.max(0, Number(block?.y || 0)),
                        width: Math.max(0, Number(block?.width || 0)),
                        height: Math.max(0, Number(block?.height || 0)),
                        fontSize: Math.max(0, Number(block?.fontSize || 0)),
                        angle: Math.max(-45, Math.min(45, Number(block?.angle || 0))),
                        color: safeColor(block?.color) || '#111827',
                        align: ['left', 'center', 'right'].includes(block?.align) ? block.align : 'center',
                        weightHint: String(block?.weightHint || '600'),
                        styleHint: String(block?.styleHint || block?.fontHint || '').toLowerCase().slice(0, 80),
                        role: safeEnum(block?.role, ['heading', 'subheading', 'body', 'caption', 'button', 'date', 'name', 'location', 'other'], 'other'),
                        sectionId: safeId(block?.sectionId),
                        groupId: safeId(block?.groupId),
                        hierarchyLevel: Math.max(1, Math.min(5, Number(block?.hierarchyLevel || 3))),
                        spacingHint: safeEnum(block?.spacingHint, ['tight', 'normal', 'airy'], 'normal'),
                        italic: block?.italic === true,
                        backgroundColor: safeColor(block?.backgroundColor),
                        coverOpacity: Math.max(0.12, Math.min(0.38, Number(block?.coverOpacity || 0.22))),
                    }))
                    .filter(block => block.text && block.width >= 4 && block.height >= 4),
            };
        }

        function magicLayerLooksLikeCenterXBlocks(blocks = [], imageWidth = 1) {
            const width = Math.max(1, Number(imageWidth) || 1);
            const candidates = blocks
                .map(block => ({
                    x: Number(block?.x || 0),
                    width: Math.max(0, Number(block?.width || 0)),
                    align: String(block?.align || '').toLowerCase(),
                }))
                .filter(block => block.align === 'center' && block.width >= width * 0.08 && block.x > 0);

            if (candidates.length < 2) return false;

            const centerLine = width / 2;
            const median = values => {
                const sorted = values
                    .filter(value => Number.isFinite(value))
                    .sort((a, b) => a - b);
                if (!sorted.length) return 0;
                return sorted[Math.floor(sorted.length / 2)];
            };
            const rawXMedian = median(candidates.map(block => block.x));
            const assumedLeftCenterMedian = median(candidates.map(block => block.x + block.width / 2));
            const rawNearCanvasCenter = rawXMedian >= width * 0.40 && rawXMedian <= width * 0.60;
            const leftCentersTooRight = assumedLeftCenterMedian > width * 0.62;
            const centerXScore = median(candidates.map(block => Math.abs(block.x - centerLine)));
            const leftXScore = median(candidates.map(block => Math.abs((block.x + block.width / 2) - centerLine)));

            return rawNearCanvasCenter && leftCentersTooRight && centerXScore + width * 0.04 < leftXScore;
        }

        function normalizeMagicLayerBlockX(block, imageWidth = 1, shouldTreatCenterX = false) {
            const width = Math.max(1, Number(imageWidth) || 1);
            const rawX = Math.max(0, Number(block?.x || 0));
            const boxWidth = Math.max(0, Number(block?.width || 0));
            const align = String(block?.align || '').toLowerCase();
            const correctedX = shouldTreatCenterX && align === 'center' && boxWidth > 0
                ? rawX - boxWidth / 2
                : rawX;
            const maxX = boxWidth > 0 ? Math.max(0, width - boxWidth) : width;

            return Math.max(0, Math.min(maxX, correctedX));
        }

        async function requestMagicLayerTextBlueprint(imageSrc) {
            if (config.ocrTextEnabled !== true || !(config.magicLayerOcrUrl || config.ocrTextUrl) || !imageSrc) {
                return null;
            }

            const response = await fetch(config.magicLayerOcrUrl || config.ocrTextUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    image_src: imageSrc,
                    engine: 'magic-layer-gemini',
                }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'AdaAcara AI gagal membaca teks gambar.');
            }

            return normalizeMagicLayerBlocks(data.data || {});
        }

        function magicLayerImageGeometry(image) {
            if (!image || !window.fabric || !fabric.util || typeof image.calcTransformMatrix !== 'function') {
                return null;
            }

            return {
                image,
                matrix: image.calcTransformMatrix(),
                width: Math.max(1, Number(image.width) || 1),
                height: Math.max(1, Number(image.height) || 1),
                cropX: Math.max(0, Number(image.cropX) || 0),
                cropY: Math.max(0, Number(image.cropY) || 0),
                angle: Number(image.angle) || 0,
            };
        }

        function magicLayerMapPoint(x, y, blueprint, geometry) {
            const sourceWidth = Math.max(1, Number(blueprint.imageWidth) || geometry.width);
            const sourceHeight = Math.max(1, Number(blueprint.imageHeight) || geometry.height);
            const cropX = Math.max(0, Number(geometry.cropX) || 0);
            const cropY = Math.max(0, Number(geometry.cropY) || 0);
            const visibleSourceWidth = Math.max(1, Math.min(sourceWidth - cropX, Number(geometry.width) || sourceWidth));
            const visibleSourceHeight = Math.max(1, Math.min(sourceHeight - cropY, Number(geometry.height) || sourceHeight));
            const localX = -geometry.width / 2 + ((Number(x) - cropX) / visibleSourceWidth) * geometry.width;
            const localY = -geometry.height / 2 + ((Number(y) - cropY) / visibleSourceHeight) * geometry.height;

            return fabric.util.transformPoint(new fabric.Point(localX, localY), geometry.matrix);
        }

        function magicLayerPointDistance(a, b) {
            return Math.max(1, Math.hypot((Number(a?.x) || 0) - (Number(b?.x) || 0), (Number(a?.y) || 0) - (Number(b?.y) || 0)));
        }

        function magicLayerBlockRegion(block, blueprint, geometry) {
            const x = Number(block.x) || 0;
            const y = Number(block.y) || 0;
            const width = Math.max(1, Number(block.width) || 1);
            const height = Math.max(1, Number(block.height) || 1);
            const center = magicLayerMapPoint(x + width / 2, y + height / 2, blueprint, geometry);
            const topLeft = magicLayerMapPoint(x, y, blueprint, geometry);
            const topRight = magicLayerMapPoint(x + width, y, blueprint, geometry);
            const bottomLeft = magicLayerMapPoint(x, y + height, blueprint, geometry);
            const scaledWidth = Math.max(8, magicLayerPointDistance(topLeft, topRight));
            const scaledHeight = Math.max(8, magicLayerPointDistance(topLeft, bottomLeft));

            return {
                left: center.x,
                top: center.y,
                width: scaledWidth,
                height: scaledHeight,
                angle: (geometry.angle || 0) + (Number(block.angle) || 0),
            };
        }

        function fitMagicLayerTextbox(textbox, maxHeight) {
            if (!textbox || typeof textbox.initDimensions !== 'function') return;

            for (let index = 0; index < 8; index += 1) {
                textbox.initDimensions();
                if ((textbox.height || 0) <= maxHeight || textbox.fontSize <= 8) break;
                textbox.set('fontSize', Math.max(8, textbox.fontSize * 0.88));
            }

            textbox.setCoords();
        }

        function magicLayerFontForBlock(block) {
            const text = String(block?.text || '');
            const hint = String(block?.styleHint || '').toLowerCase();
            const role = String(block?.role || '').toLowerCase();

            if (role === 'name') return 'Great Vibes';
            if (role === 'heading') return /script|hand|callig|cursive/.test(hint) ? 'Great Vibes' : 'Cinzel';
            if (role === 'subheading' || role === 'date') return 'Cormorant Garamond';
            if (/script|hand|callig|cursive|signature|name/.test(hint)) return 'Great Vibes';
            if (/serif|classic|formal|elegant|wedding|luxury/.test(hint)) return 'Cormorant Garamond';
            if (/display|title|heading|uppercase/.test(hint) || (text === text.toUpperCase() && text.length <= 28)) return 'Cinzel';
            if (text.length <= 42 && /[A-Za-z]/.test(text)) return 'Cormorant Garamond';

            return 'Inter';
        }

        function magicLayerFontSizeForBlock(block, region, fontFamily) {
            const baseSize = block.fontSize > 0
                ? block.fontSize * Math.max(region.height / Math.max(1, block.height), 0.2)
                : region.height * (fontFamily === 'Great Vibes' ? 0.95 : 0.82);
            const role = String(block?.role || '').toLowerCase();
            const hierarchy = Math.max(1, Math.min(5, Number(block?.hierarchyLevel || 3)));
            const roleScale = {
                heading: 1.08,
                name: 1.1,
                subheading: 1.0,
                date: 1.0,
                location: 0.92,
                button: 0.88,
                body: 0.9,
                caption: 0.78,
                other: 0.92,
            }[role] || 0.92;
            const hierarchyScale = {
                1: 1.08,
                2: 1.02,
                3: 1,
                4: 0.94,
                5: 0.88,
            }[hierarchy] || 1;

            return Math.max(10, Math.min(180, baseSize * roleScale * hierarchyScale));
        }

        function magicLayerWeightForBlock(block) {
            const role = String(block?.role || '').toLowerCase();
            const numericWeight = Number(block?.weightHint || 0);
            if (numericWeight >= 100 && numericWeight <= 900) {
                if (role === 'heading' || role === 'name') return String(Math.max(600, Math.min(800, numericWeight)));
                if (role === 'caption' || role === 'body') return String(Math.max(300, Math.min(600, numericWeight)));
                return String(Math.max(300, Math.min(800, numericWeight)));
            }

            if (role === 'heading' || role === 'name') return '700';
            if (role === 'subheading' || role === 'date') return '600';
            if (role === 'caption') return '400';

            return '500';
        }

        function magicLayerLineHeightForBlock(block) {
            const role = String(block?.role || '').toLowerCase();
            const spacing = String(block?.spacingHint || '').toLowerCase();
            if (role === 'heading' || role === 'name') return spacing === 'airy' ? 1.08 : 1.02;
            if (role === 'caption') return 1.18;
            if (spacing === 'tight') return 1.04;
            if (spacing === 'airy') return 1.2;

            return 1.12;
        }

        function magicLayerNameForBlock(block) {
            const role = String(block?.role || 'text').toLowerCase();
            const label = {
                heading: 'Heading',
                subheading: 'Subheading',
                body: 'Body Text',
                caption: 'Caption',
                button: 'Button Text',
                date: 'Date Text',
                name: 'Name Text',
                location: 'Location Text',
            }[role] || 'Text';

            return `Magic Layer ${label}`;
        }

        async function createMagicLayerTextObjects(blueprint, geometry) {
            if (!blueprint || !geometry || !Array.isArray(blueprint.blocks) || !blueprint.blocks.length) {
                return { covers: [], texts: [] };
            }

            const canvasBg = /^#[0-9a-f]{6}$/i.test(String(state.canvas?.backgroundColor || ''))
                ? String(state.canvas.backgroundColor)
                : '#ffffff';
            const coverFill = blueprint.backgroundColor || canvasBg;
            const covers = [];
            const texts = [];
            const useTextMasks = config.magicLayerTextMasks === true;

            for (const block of blueprint.blocks.slice(0, 24)) {
                const region = magicLayerBlockRegion(block, blueprint, geometry);
                if (region.width < 8 || region.height < 8) continue;

                if (useTextMasks) {
                    const cover = new fabric.Rect({
                        left: region.left,
                        top: region.top,
                        width: Math.max(1, region.width * 1.08),
                        height: Math.max(1, region.height * 1.12),
                        originX: 'center',
                        originY: 'center',
                        angle: region.angle,
                        rx: Math.min(region.width, region.height) * 0.04,
                        ry: Math.min(region.width, region.height) * 0.04,
                        fill: block.backgroundColor || coverFill,
                        opacity: block.coverOpacity,
                        customType: 'shape',
                        name: 'Magic Layer Soft Mask',
                        aaSource: 'magic-layer-ai',
                        aaMagicLayerMask: true,
                        objectCaching: false,
                    });
                    cover.setCoords();
                    covers.push(cover);
                }

                const fontFamily = magicLayerFontForBlock(block);
                await Promise.all([
                    ensureGoogleFontCss(fontFamily),
                    ensureCustomFontCss(fontFamily),
                    ensureBunnyFontCss(fontFamily),
                ]).catch(() => null);

                const textbox = new fabric.Textbox(block.text, {
                    left: region.left,
                    top: region.top,
                    width: region.width,
                    originX: 'center',
                    originY: 'center',
                    angle: region.angle,
                    fontFamily,
                    fontSize: magicLayerFontSizeForBlock(block, region, fontFamily),
                    fill: block.color,
                    textAlign: block.align,
                    fontWeight: magicLayerWeightForBlock(block),
                    fontStyle: block.italic ? 'italic' : 'normal',
                    lineHeight: magicLayerLineHeightForBlock(block),
                    customType: 'text',
                    name: magicLayerNameForBlock(block),
                    aaSource: 'magic-layer-ai',
                    aaOcrConfidence: block.confidence,
                    aaMagicRole: block.role,
                    aaMagicSectionId: block.sectionId,
                    aaMagicGroupId: block.groupId,
                    aaMagicHierarchyLevel: block.hierarchyLevel,
                    aaMagicSpacingHint: block.spacingHint,
                    aaNeedsBackgroundCleanup: true,
                    objectCaching: false,
                });
                fitMagicLayerTextbox(textbox, region.height * 1.22);
                if (typeof aaApplyTextboxResizeControls === 'function') {
                    aaApplyTextboxResizeControls(textbox);
                }

                textbox.setCoords();
                texts.push(textbox);
            }

            return { covers, texts };
        }

        async function magicLayerFromActiveImage() {
            const active = state.canvas?.getActiveObject();
            if (!active || active.type !== 'image') {
                setStatus('Pilih gambar terlebih dahulu.', 'error');
                setMagicLayerAiStatus('Pilih gambar di canvas terlebih dahulu.', 'error');
                return;
            }
            if (guardAiPremiumFeature(null, 'Magic Layer')) return;
            if (guardLockedImageAction('magic layer')) return;
            if (!config.magicLayerEnabled) {
                setStatus('Magic Layer sedang nonaktif. Gunakan Remove BG untuk Poof.', 'error');
                setMagicLayerAiStatus('Magic Layer belum aktif di konfigurasi.', 'error');
                return;
            }
            if (!config.mediaMagicLayerUrl) {
                setStatus('Service Magic Layer belum dikonfigurasi.', 'error');
                setMagicLayerAiStatus('Endpoint Magic Layer belum tersedia.', 'error');
                return;
            }
            const src = ensureImageOriginalSource(active) || readImageSourceUrl(active);
            if (!src) {
                setStatus('Sumber gambar tidak ditemukan.', 'error');
                setMagicLayerAiStatus('Sumber gambar tidak ditemukan.', 'error');
                return;
            }
            if (active.__aaMagicLayerProcessing === true) return;

            active.__aaMagicLayerProcessing = true;
            state.imageProcessTarget = active;
            syncContextToolbar(active);
            showImageProcessOverlay(active, 'Magic Layer...');
            setStatus('Memproses Magic Layer...', 'saving');
            setMagicLayerAiStatus('Membaca gambar dan membuat layer...', 'success');

            try {
                const imageGeometry = magicLayerImageGeometry(active);
                const blueprintPromise = config.ocrTextEnabled === true
                    ? requestMagicLayerTextBlueprint(src).catch(error => {
                        console.warn('[AdaAcara Magic Layer] OCR gagal:', error);
                        setMagicLayerAiStatus(error?.message || 'OCR teks gagal. Layer gambar tetap dibuat.', 'error');
                        return null;
                    })
                    : Promise.resolve(null);

                const form = new FormData();
                form.append('image_url', src);
                form.append('page_id', String(config.pageId || ''));
                const response = await fetch(config.mediaMagicLayerUrl, {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const contentType = response.headers.get('content-type') || '';
                const data = contentType.includes('application/json') ? await response.json() : {};
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Service Magic Layer gagal diproses.');
                }

                const subjectSrc = data.subject?.src;
                const backgroundSrc = data.background?.src;
                if (!subjectSrc || !backgroundSrc) {
                    throw new Error('Hasil Magic Layer tidak memiliki data gambar.');
                }

                showImageProcessOverlay(active, 'Memasang layer...');

                const subjectTrimPromise = aaTrimTransparentImageDataUrl(subjectSrc, {
                    alphaThreshold: 8,
                    padding: 18,
                });
                const [subjectTrim, backgroundImg, blueprint] = await Promise.all([
                    subjectTrimPromise,
                    loadFabricImage(backgroundSrc),
                    blueprintPromise,
                ]);
                const finalSubjectSrc = subjectTrim?.src || subjectSrc;
                const subjectImg = await loadFabricImageWithRetry(finalSubjectSrc, {
                    attempts: 4,
                    delay: 550
                });

                if (!subjectImg || !backgroundImg) {
                    throw new Error('Gagal memuat hasil gambar layer.');
                }

                subjectImg.set({ src: finalSubjectSrc, objectCaching: false });
                backgroundImg.set({ src: backgroundSrc, objectCaching: false });

                copyImageReplaceStyle(active, backgroundImg, { preserveCrop: true });
                copyImageReplaceStyle(active, subjectImg, { preserveCrop: true });
                if (subjectTrim?.trim) {
                    aaApplyTrimmedSubjectPlacement(active, subjectImg, subjectTrim.trim);
                }

                const subjectProps = {
                    customType: active.customType || 'image',
                    aaOriginalImageSrc: finalSubjectSrc,
                    aaOriginalImageName: 'magic-subject.png',
                    aaRemovedBg: true,
                    aaRemovedBgSrc: finalSubjectSrc,
                    aaImageOutlineBaseSrc: finalSubjectSrc,
                    aaImageOutlineAlphaEligible: true,
                    aaMagicLayerAutoTrim: subjectTrim?.trimmed === true,
                    galleryImageName: 'magic-subject.png',
                };
                subjectImg.set(subjectProps);
                const splitSubjectImages = await aaCreateSplitSubjectImages(finalSubjectSrc, active, subjectProps, {
                    alphaThreshold: 8,
                    padding: 18,
                    maxComponents: 8,
                    ocrBlueprint: blueprint,
                });
                const subjectObjects = Array.isArray(splitSubjectImages) && splitSubjectImages.length
                    ? splitSubjectImages
                    : [subjectImg];
                backgroundImg.set({
                    customType: active.customType || 'image',
                    aaOriginalImageSrc: backgroundSrc,
                    aaOriginalImageName: 'magic-background.png',
                    galleryImageName: 'magic-background.png',
                });

                const magicObjects = await createMagicLayerTextObjects(blueprint, imageGeometry);
                const index = Math.max(0, state.canvas.getObjects().indexOf(active));

                state.canvas.remove(active);

                state.canvas.insertAt(backgroundImg, index, false);
                subjectObjects.forEach((object, offset) => {
                    state.canvas.insertAt(object, index + 1 + offset, false);
                });
                magicObjects.covers.forEach((object, offset) => {
                    state.canvas.insertAt(object, index + 1 + subjectObjects.length + offset, false);
                });
                magicObjects.texts.forEach((object, offset) => {
                    state.canvas.insertAt(object, index + 1 + subjectObjects.length + magicObjects.covers.length + offset, false);
                });

                const nextActiveObject = magicObjects.texts[0] || subjectObjects[0] || backgroundImg;
                if (nextActiveObject) {
                    state.canvas.setActiveObject(nextActiveObject);
                }

                subjectObjects.forEach(object => object.setCoords());
                backgroundImg.setCoords();
                magicObjects.covers.forEach(object => object.setCoords());
                magicObjects.texts.forEach(object => object.setCoords());

                state.canvas.requestRenderAll();
                syncInspector();
                snapshot();

                await loadMedia({ force: true, silent: true });
                if (data.background_mode === 'blurred_original') {
                    setStatus('Magic Layer AI dibuat. Background diblur ringan, teks dibuat editable.');
                } else {
                    setStatus('Magic Layer berhasil dibuat! Gambar depan dan belakang telah terpisah.');
                }
                setMagicLayerAiStatus(`${magicObjects.texts.length} teks editable dibuat. Cek ulang posisi sebelum save.`, 'success');
            } catch (error) {
                setStatus(error?.message || 'Magic Layer gagal diproses.', 'error');
                setMagicLayerAiStatus(error?.message || 'Magic Layer gagal diproses.', 'error');
            } finally {
                active.__aaMagicLayerProcessing = false;
                state.imageProcessTarget = null;
                hideImageProcessOverlay();
                syncContextToolbar(state.canvas?.getActiveObject());
            }
        }

        function placeImageAtPointer(image, pointer) {
            if (!image || !pointer) return;
            image.set({
                left: pointer.x,
                top: pointer.y,
                originX: 'center',
                originY: 'center',
            });
            image.setCoords();
        }

        function findImageAtPointer(pointer) {
            if (!state.canvas || !pointer) return null;
            const point = new fabric.Point(pointer.x, pointer.y);
            return [...state.canvas.getObjects()].reverse().find(object => object && object !== state.cropBox &&
                isImageReplaceTarget(object) && object.containsPoint(point)) || null;
        }

        function isImageReplaceTarget(object) {
            return Boolean(object && (object.type === 'image' || object.customType === 'photo-frame'));
        }

        function getCanvasDropPointer(event) {
            if (!state.canvas?.upperCanvasEl) return null;
            const rect = state.canvas.upperCanvasEl.getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event
                .clientY >
                rect.bottom) {
                return null;
            }
            return state.canvas.getPointer(event);
        }

        function mediaAssetDragPayload(asset) {
            if (!asset || !asset.src) return '';
            return JSON.stringify({
                src: asset.src,
                name: asset.name || 'media',
            });
        }

        function readDraggedMediaAsset(event) {
            const raw = event.dataTransfer?.getData('application/x-aa-media') || event.dataTransfer?.getData(
                'text/plain') || '';
            if (!raw) return null;
            try {
                const data = JSON.parse(raw);
                return data && data.src ? data : null;
            } catch (error) {
                return raw ? {
                    src: raw,
                    name: 'media',
                } : null;
            }
        }

        function hasDraggedMediaAsset(event) {
            const types = Array.from(event.dataTransfer?.types || []);
            return Boolean(state.mediaDragAsset) || types.includes('application/x-aa-media');
        }

        function hideMediaDropPreview() {
            els.aaMediaDropPreview?.classList.remove('is-visible', 'is-locked');
            if (els.aaMediaDropPreviewImage) {
                els.aaMediaDropPreviewImage.removeAttribute('src');
            }
        }

        function setMediaDropPreviewRect(target) {
            if (!target || !state.canvas?.upperCanvasEl || !els.aaMediaDropPreview) return false;
            const canvasRect = state.canvas.upperCanvasEl.getBoundingClientRect();
            const objectRect = target.getBoundingRect(true, true);
            const scaleX = canvasRect.width / Math.max(1, state.canvas.getWidth());
            const scaleY = canvasRect.height / Math.max(1, state.canvas.getHeight());
            const left = canvasRect.left + objectRect.left * scaleX;
            const top = canvasRect.top + objectRect.top * scaleY;
            const width = objectRect.width * scaleX;
            const height = objectRect.height * scaleY;

            if (width <= 0 || height <= 0) return false;
            els.aaMediaDropPreview.style.left = `${Math.round(left)}px`;
            els.aaMediaDropPreview.style.top = `${Math.round(top)}px`;
            els.aaMediaDropPreview.style.width = `${Math.round(width)}px`;
            els.aaMediaDropPreview.style.height = `${Math.round(height)}px`;
            return true;
        }

        function syncMediaDropPreview(event) {
            const asset = state.mediaDragAsset || readDraggedMediaAsset(event);
            const pointer = getCanvasDropPointer(event);
            if (!asset?.src || !pointer || state.isCropping) {
                hideMediaDropPreview();
                return;
            }

            const target = findImageAtPointer(pointer);
            if (!target || !setMediaDropPreviewRect(target)) {
                hideMediaDropPreview();
                return;
            }

            if (els.aaMediaDropPreviewImage && els.aaMediaDropPreviewImage.getAttribute('src') !== asset.src) {
                els.aaMediaDropPreviewImage.src = asset.src;
            }
            const locked = target.locked === true;
            const premiumReplaceLocked = !locked && !canUsePremiumFeature();
            if (els.aaMediaDropPreviewLabel) {
                els.aaMediaDropPreviewLabel.textContent = locked
                    ? 'Gambar terkunci'
                    : (premiumReplaceLocked ? 'Replace gambar untuk member' : 'Lepas untuk ganti gambar');
            }
            els.aaMediaDropPreview.classList.toggle('is-locked', locked || premiumReplaceLocked);
            els.aaMediaDropPreview.classList.add('is-visible');
        }

        function handleMediaDrop(event) {
            const asset = readDraggedMediaAsset(event) || state.mediaDragAsset;
            hideMediaDropPreview();
            state.mediaDragAsset = null;
            if (!asset?.src || state.isCropping) return;
            const pointer = getCanvasDropPointer(event);
            if (!pointer) return;
            event.preventDefault();
            event.stopPropagation();

            const target = findImageAtPointer(pointer);
            if (target) {
                if (!canUsePremiumFeature()) {
                    openEditorAccessModal({
                        title: 'Upgrade untuk Replace Image',
                        description: 'Mengganti gambar template dari Media Library tersedia untuk akun member. Kamu tetap bisa menambahkan gambar baru dari panel Upload.',
                    });
                    return;
                }
                if (target.locked === true) {
                    setStatus('Gambar terkunci. Unlock dulu untuk mengganti gambar.', 'error');
                    return;
                }
                insertImage(asset.src, {
                    replaceTarget: target,
                    galleryImageSrc: target.galleryZoom ? asset.src : '',
                    galleryImageName: asset.name || '',
                });
                setStatus('Gambar diganti dari Media Library');
                return;
            }

            insertImage(asset.src, {
                pointer,
            });
            setStatus('Gambar ditambahkan dari Media Library');
        }

        function handleStageDrop(event) {
            const editorAsset = readDraggedEditorAsset(event);
            if (editorAsset) {
                hideMediaDropPreview();
                if (state.isCropping) return;
                const pointer = getCanvasDropPointer(event);
                if (!pointer) return;
                event.preventDefault();
                event.stopPropagation();
                insertEditorAsset(editorAsset, {
                    pointer,
                });
                return;
            }

            handleMediaDrop(event);
        }

        function insertImage(url, options = {}) {
            fabric.Image.fromURL(url, function(image) {
                const maxWidth = state.canvas.getWidth() * 0.68;
                if (image.width > maxWidth) {
                    image.scaleToWidth(maxWidth);
                }
                image.set({
                    customType: options.customType || 'image',
                    isGalleryPhoto: options.isGalleryPhoto === true,
                    galleryZoom: options.galleryZoom === true,
                    galleryImageSrc: options.galleryImageSrc || '',
                    galleryImageName: options.galleryImageName || '',
                    aaOriginalImageSrc: options.aaOriginalImageSrc || url,
                    aaOriginalImageName: options.aaOriginalImageName || options.galleryImageName || '',
                    borderRadius: 0,
                    objectCaching: false,
                });
                resetImageCropState(image);

                const replaceTarget = options.replaceTarget || state.canvas.getActiveObject();
                if ((state.mediaMode === 'replace' || options.replaceTarget) && isImageReplaceTarget(replaceTarget)) {
                    replaceImageObject(replaceTarget, image, {
                        ...options,
                        allowLockedReplace: options.allowLockedReplace === true,
                    });
                    state.mediaMode = 'insert';
                    return;
                }

                applyImageBorderRadius(image, image.borderRadius || 0);
                if (options.pointer) {
                    placeImageAtPointer(image, options.pointer);
                    state.canvas.add(image);
                    state.canvas.setActiveObject(image);
                    state.canvas.requestRenderAll();
                    snapshot();
                } else {
                    centerObject(image);
                }
                state.mediaMode = 'insert';
            }, {
                crossOrigin: 'anonymous'
            });
        }

        function backgroundImageObject() {
            if (!state.canvas) return null;
            return state.canvas.getObjects().find(object => object && object.customType === 'background') || null;
        }

        function setBackgroundImageStatus(message = '', type = '') {
            if (!els.aaBackgroundImageStatus) return;
            els.aaBackgroundImageStatus.textContent = message ||
                'Maksimal 1MB. Gambar akan auto-cover mengikuti ukuran halaman.';
            els.aaBackgroundImageStatus.classList.toggle('is-error', type === 'error');
            els.aaBackgroundImageStatus.classList.toggle('is-success', type === 'success');
        }

        function syncBackgroundImageControls() {
            const image = backgroundImageObject();
            const opacity = image ? Math.round((Number.isFinite(Number(image.opacity)) ? Number(image.opacity) : 1) *
                100) : 100;
            const offsetX = image ? Math.round(Number(image.aaBgOffsetX || 0)) : 0;
            const offsetY = image ? Math.round(Number(image.aaBgOffsetY || 0)) : 0;
            if (els.aaBackgroundOpacityInput) {
                els.aaBackgroundOpacityInput.value = String(opacity);
                els.aaBackgroundOpacityInput.disabled = !image;
            }
            if (els.aaBackgroundOpacityValue) {
                els.aaBackgroundOpacityValue.textContent = String(opacity);
            }
            if (els.aaBackgroundPositionXInput) {
                els.aaBackgroundPositionXInput.value = String(offsetX);
                els.aaBackgroundPositionXInput.disabled = !image;
            }
            if (els.aaBackgroundPositionXValue) {
                els.aaBackgroundPositionXValue.textContent = String(offsetX);
            }
            if (els.aaBackgroundPositionYInput) {
                els.aaBackgroundPositionYInput.value = String(offsetY);
                els.aaBackgroundPositionYInput.disabled = !image;
            }
            if (els.aaBackgroundPositionYValue) {
                els.aaBackgroundPositionYValue.textContent = String(offsetY);
            }
            if (els.aaBackgroundPositionResetBtn) {
                els.aaBackgroundPositionResetBtn.disabled = !image;
            }
        }

        function applyBackgroundImageCover(image) {
            if (!state.canvas || !image) return;
            const canvasWidth = Math.max(1, state.canvas.getWidth());
            const canvasHeight = Math.max(1, state.canvas.getHeight());
            const imageWidth = Math.max(1, image.width || 1);
            const imageHeight = Math.max(1, image.height || 1);
            const scale = Math.max(canvasWidth / imageWidth, canvasHeight / imageHeight);
            const offsetX = Number(image.aaBgOffsetX || 0);
            const offsetY = Number(image.aaBgOffsetY || 0);
            const opacity = Number.isFinite(Number(image.opacity)) ? Number(image.opacity) : 1;
            image.set({
                left: (canvasWidth / 2) + ((canvasWidth * offsetX) / 100),
                top: (canvasHeight / 2) + ((canvasHeight * offsetY) / 100),
                originX: 'center',
                originY: 'center',
                scaleX: scale,
                scaleY: scale,
                opacity: Math.max(0, Math.min(1, opacity)),
                aaBgOffsetX: offsetX,
                aaBgOffsetY: offsetY,
                customType: 'background',
                name: 'Background Image',
                selectable: false,
                evented: false,
                excludeFromAnimation: true,
                locked: true,
                hasControls: false,
                hoverCursor: 'default',
                objectCaching: true,
            });
            image.setCoords();
        }

        function applyBackgroundImageControls(options = {}, shouldCommit = false) {
            const image = backgroundImageObject();
            if (!image || !state.canvas) {
                syncBackgroundImageControls();
                return;
            }
            if (Object.prototype.hasOwnProperty.call(options, 'opacity')) {
                image.set('opacity', Math.max(0, Math.min(1, Number(options.opacity) / 100)));
            }
            if (Object.prototype.hasOwnProperty.call(options, 'offsetX')) {
                image.aaBgOffsetX = Math.max(-100, Math.min(100, Number(options.offsetX) || 0));
            }
            if (Object.prototype.hasOwnProperty.call(options, 'offsetY')) {
                image.aaBgOffsetY = Math.max(-100, Math.min(100, Number(options.offsetY) || 0));
            }
            applyBackgroundImageCover(image);
            state.canvas.sendToBack(image);
            state.canvas.requestRenderAll();
            syncBackgroundImageControls();
            if (shouldCommit) {
                snapshot();
            }
        }

        function ensureBackgroundImageBack() {
            const image = backgroundImageObject();
            if (!image || !state.canvas) return;
            applyBackgroundImageCover(image);
            state.canvas.sendToBack(image);
            syncBackgroundImageControls();
        }

        function setCanvasBackgroundImage(src) {
            if (!src || !state.canvas) return;
            setBackgroundImageStatus('Memasang background...', '');
            fabric.Image.fromURL(src, image => {
                const existing = backgroundImageObject();
                if (existing) {
                    state.canvas.remove(existing);
                }
                image.set({
                    src,
                    crossOrigin: 'anonymous',
                });
                applyBackgroundImageCover(image);
                state.canvas.add(image);
                state.canvas.sendToBack(image);
                state.canvas.discardActiveObject();
                state.canvas.requestRenderAll();
                snapshot();
                setBackgroundImageStatus('Background image terpasang.', 'success');
                syncBackgroundImageControls();
            }, {
                crossOrigin: 'anonymous'
            });
        }

        function uploadCanvasBackgroundImage(file) {
            if (!file) {
                els.aaBackgroundImageInput?.click();
                return;
            }
            const maxSize = 1024 * 1024;
            if (file.size > maxSize) {
                setBackgroundImageStatus('Upload dibatalkan. Maksimal background 1MB.', 'error');
                setStatus('Maksimal background 1MB', 'error');
                return;
            }
            if (!/^image\/(png|jpe?g|webp|gif)$/i.test(file.type || '')) {
                setBackgroundImageStatus('Format background harus PNG, JPG, WEBP, atau GIF.', 'error');
                setStatus('Format background tidak valid', 'error');
                return;
            }
            const reader = new FileReader();
            reader.onload = () => setCanvasBackgroundImage(String(reader.result || ''));
            reader.onerror = () => {
                setBackgroundImageStatus('Background gagal dibaca.', 'error');
                setStatus('Background gagal dibaca', 'error');
            };
            setBackgroundImageStatus('Membaca background...', '');
            reader.readAsDataURL(file);
        }

        function removeCanvasBackgroundImage() {
            const image = backgroundImageObject();
            if (!image || !state.canvas) {
                setBackgroundImageStatus('Tidak ada background image.', '');
                return;
            }
            state.canvas.remove(image);
            state.canvas.requestRenderAll();
            snapshot();
            setBackgroundImageStatus('Background image dihapus.', 'success');
            syncBackgroundImageControls();
        }

        function insertGalleryPhoto(asset) {
            if (!asset || !asset.src) return;
            insertImage(asset.src, {
                customType: 'gallery-photo',
                isGalleryPhoto: true,
                galleryZoom: true,
                galleryImageSrc: asset.src,
                galleryImageName: asset.name || 'Gallery',
            });
            setStatus('Foto gallery ditambahkan. Bisa drag, resize, rotate, dan klik zoom saat publish.');
        }

        function addGalleryPhotoAssets(assets) {
            const list = (Array.isArray(assets) ? assets : [assets]).filter(item => item && item.src);
            if (!list.length) return;
            list.forEach(insertGalleryPhoto);
            state.mediaMode = 'insert';
        }

        async function uploadSingleImage(file) {
            if (!file) return;
            if (file.size > 3 * 1024 * 1024) {
                throw new Error(`${file.name || 'Gambar'} lebih besar dari batas 3MB.`);
            }

            const fileMeta = await getImageFileMeta(file);
            const formData = new FormData();
            formData.append('file', file);

            const response = await fetch(config.mediaUploadUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Upload gagal.');
            }

            const asset = data.data && data.data[0];
            if (asset && asset.src) {
                return normalizeGalleryItem({
                    ...asset,
                    name: asset.name || file.name || '',
                    naturalWidth: asset.naturalWidth || fileMeta.naturalWidth,
                    naturalHeight: asset.naturalHeight || fileMeta.naturalHeight,
                    aspectRatio: asset.aspectRatio || fileMeta.aspectRatio,
                    orientation: asset.orientation || fileMeta.orientation,
                });
            }
        }

        function validateMagicLayerUploadFile(file) {
            if (!file) {
                throw new Error('Pilih gambar Magic Layer terlebih dahulu.');
            }
            const maxSize = 2 * 1024 * 1024;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(String(file.type || '').toLowerCase())) {
                throw new Error('Magic Layer hanya mendukung JPG, PNG, atau WEBP.');
            }
            if (file.size > maxSize) {
                throw new Error('Ukuran gambar lebih besar dari batas 2MB.');
            }

            return true;
        }

        function formatMagicLayerFileSize(bytes) {
            const size = Math.max(0, Number(bytes) || 0);
            if (size >= 1024 * 1024) return `${(size / 1024 / 1024).toFixed(2)} MB`;
            if (size >= 1024) return `${Math.round(size / 1024)} KB`;
            return `${size} B`;
        }

        function analyzeMagicLayerImageQuality(file) {
            return new Promise(resolve => {
                const result = {
                    warnings: [],
                    naturalWidth: 0,
                    naturalHeight: 0,
                };
                if (!file || typeof document === 'undefined') {
                    resolve(result);
                    return;
                }

                const url = URL.createObjectURL(file);
                const image = new Image();
                const finish = value => {
                    URL.revokeObjectURL(url);
                    resolve(value || result);
                };

                image.onload = () => {
                    try {
                        result.naturalWidth = Math.max(0, Number(image.naturalWidth || image.width) || 0);
                        result.naturalHeight = Math.max(0, Number(image.naturalHeight || image.height) || 0);
                        if (!result.naturalWidth || !result.naturalHeight) {
                            finish(result);
                            return;
                        }

                        const shortSide = Math.min(result.naturalWidth, result.naturalHeight);
                        if (shortSide < 640) {
                            result.warnings.push('Resolusi gambar cukup kecil. Teks kecil mungkin sulit terbaca.');
                        }

                        const sampleMax = 160;
                        const scale = Math.min(1, sampleMax / Math.max(result.naturalWidth, result.naturalHeight));
                        const width = Math.max(24, Math.round(result.naturalWidth * scale));
                        const height = Math.max(24, Math.round(result.naturalHeight * scale));
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d', {
                            willReadFrequently: true,
                        });
                        if (!ctx) {
                            finish(result);
                            return;
                        }

                        ctx.drawImage(image, 0, 0, width, height);
                        const pixels = ctx.getImageData(0, 0, width, height).data;
                        const gray = new Float32Array(width * height);
                        let sum = 0;
                        let sumSq = 0;
                        for (let index = 0, point = 0; index < pixels.length; index += 4, point += 1) {
                            const value = pixels[index] * 0.299 + pixels[index + 1] * 0.587 + pixels[index + 2] * 0.114;
                            gray[point] = value;
                            sum += value;
                            sumSq += value * value;
                        }

                        const count = Math.max(1, gray.length);
                        const mean = sum / count;
                        const contrast = Math.sqrt(Math.max(0, (sumSq / count) - mean * mean));
                        let edgeSum = 0;
                        let edgeSq = 0;
                        let edgeCount = 0;
                        for (let y = 1; y < height - 1; y += 1) {
                            for (let x = 1; x < width - 1; x += 1) {
                                const point = y * width + x;
                                const edge = (gray[point - 1] + gray[point + 1] + gray[point - width] + gray[point + width]) - (gray[point] * 4);
                                edgeSum += edge;
                                edgeSq += edge * edge;
                                edgeCount += 1;
                            }
                        }

                        const edgeMean = edgeSum / Math.max(1, edgeCount);
                        const sharpness = Math.max(0, (edgeSq / Math.max(1, edgeCount)) - edgeMean * edgeMean);
                        if (contrast < 18) {
                            result.warnings.push('Kontras gambar rendah. Hasil OCR mungkin perlu dirapikan.');
                        } else if (sharpness < 34) {
                            result.warnings.push('Gambar berpotensi buram. Hasil OCR mungkin tidak membaca semua teks.');
                        }
                    } catch (error) {
                        console.warn('[AdaAcara Magic Layer] Analisis kualitas gambar gagal:', error);
                    }

                    finish(result);
                };
                image.onerror = () => finish(result);
                image.src = url;
            });
        }

        function clearMagicLayerUploadPreview(message = 'Pilih gambar terlebih dahulu. Hasil Magic Layer akan dibuat di halaman baru.') {
            if (state.magicLayerPreviewUrl) {
                URL.revokeObjectURL(state.magicLayerPreviewUrl);
            }
            state.magicLayerPreviewUrl = '';
            state.magicLayerSelectedFile = null;
            if (els.aaMagicLayerFileInput) els.aaMagicLayerFileInput.value = '';
            els.aaMagicLayerPreview?.classList.add('hidden');
            if (els.aaMagicLayerPreviewImage) {
                els.aaMagicLayerPreviewImage.removeAttribute('src');
            }
            if (els.aaMagicLayerPreviewMeta) {
                els.aaMagicLayerPreviewMeta.textContent = '';
            }
            setMagicLayerAiStatus(message);
        }

        async function setMagicLayerUploadPreview(file) {
            validateMagicLayerUploadFile(file);
            if (state.magicLayerPreviewUrl) {
                URL.revokeObjectURL(state.magicLayerPreviewUrl);
            }

            state.magicLayerSelectedFile = file;
            state.magicLayerPreviewUrl = URL.createObjectURL(file);

            if (els.aaMagicLayerPreviewImage) {
                els.aaMagicLayerPreviewImage.src = state.magicLayerPreviewUrl;
            }
            if (els.aaMagicLayerPreviewMeta) {
                els.aaMagicLayerPreviewMeta.textContent = `${file.name || 'Gambar'} - ${formatMagicLayerFileSize(file.size)}`;
            }
            els.aaMagicLayerPreview?.classList.remove('hidden');
            setMagicLayerAiStatus('Preview siap. Klik Proses Magic Layer AI untuk membuat halaman baru.', 'success');

            const quality = await analyzeMagicLayerImageQuality(file);
            if (state.magicLayerSelectedFile !== file) return;

            const dimensions = quality.naturalWidth && quality.naturalHeight ? ` - ${quality.naturalWidth}x${quality.naturalHeight}px` : '';
            if (els.aaMagicLayerPreviewMeta && dimensions) {
                els.aaMagicLayerPreviewMeta.textContent = `${file.name || 'Gambar'} - ${formatMagicLayerFileSize(file.size)}${dimensions}`;
            }

            if (quality.warnings.length) {
                setMagicLayerAiStatus(`${quality.warnings.slice(0, 2).join(' ')} Proses tetap bisa dilanjutkan.`, 'warning');
            }
        }

        async function uploadSingleMagicLayerImage(file) {
            validateMagicLayerUploadFile(file);
            if (!config.mediaMagicLayerTempUploadUrl) {
                throw new Error('Upload Magic Layer belum tersedia.');
            }

            const fileMeta = await getImageFileMeta(file);
            const formData = new FormData();
            formData.append('file', file);

            const response = await fetch(config.mediaMagicLayerTempUploadUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Upload Magic Layer gagal.');
            }

            const asset = data.data && data.data[0];
            if (!asset || !asset.src) {
                throw new Error('Upload Magic Layer tidak mengembalikan data gambar.');
            }

            return normalizeGalleryItem({
                ...asset,
                name: asset.name || file.name || '',
                naturalWidth: asset.naturalWidth || fileMeta.naturalWidth,
                naturalHeight: asset.naturalHeight || fileMeta.naturalHeight,
                aspectRatio: asset.aspectRatio || fileMeta.aspectRatio,
                orientation: asset.orientation || fileMeta.orientation,
            });
        }

        async function cleanupMagicLayerTempAsset(asset = null) {
            if (!asset?.temporary || !asset.src || !config.mediaMagicLayerTempDeleteUrl) return;
            const formData = new FormData();
            formData.append('image_url', asset.src);
            await fetch(config.mediaMagicLayerTempDeleteUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).catch(() => null);
        }

        function prepareMagicLayerUploadedSourceImage(image) {
            if (!image || !state.canvas) return image;

            const canvasWidth = Math.max(1, state.canvas.getWidth());
            const canvasHeight = Math.max(1, state.canvas.getHeight());
            const imageWidth = Math.max(1, Number(image.width) || 1);
            const imageHeight = Math.max(1, Number(image.height) || 1);
            const scale = Math.max(canvasWidth / imageWidth, canvasHeight / imageHeight);

            image.set({
                left: canvasWidth / 2,
                top: canvasHeight / 2,
                originX: 'center',
                originY: 'center',
                scaleX: scale,
                scaleY: scale,
                angle: 0,
                cropX: 0,
                cropY: 0,
                width: imageWidth,
                height: imageHeight,
                objectCaching: false,
            });
            image.setCoords();

            return image;
        }

        function showMagicLayerUploadPreview(image, message = 'Memproses Magic Layer AI...') {
            if (!image || !state.canvas) return null;

            hideMagicLayerUploadPreview();

            image.set({
                selectable: false,
                evented: false,
                opacity: 0.92,
                aaMagicLayerUploadPreview: true,
                name: 'Magic Layer Upload Preview',
                excludeFromExport: true,
            });

            state.canvas.add(image);
            if (typeof image.sendToBack === 'function') {
                image.sendToBack();
            } else if (typeof state.canvas.sendToBack === 'function') {
                state.canvas.sendToBack(image);
            }
            state.__aaMagicLayerUploadPreview = image;
            state.canvas.discardActiveObject();
            state.canvas.requestRenderAll();

            showMagicLayerCanvasLoading(message);

            return image;
        }

        function hideMagicLayerUploadPreview() {
            hideMagicLayerCanvasLoading();

            const preview = state.__aaMagicLayerUploadPreview;
            if (preview && state.canvas?.getObjects?.().includes(preview)) {
                state.canvas.remove(preview);
                state.canvas.requestRenderAll();
            }

            state.__aaMagicLayerUploadPreview = null;
        }

        function syncMagicLayerCanvasLoadingScale(overlay) {
            if (!overlay) return;
            const zoom = Math.max(0.01, Number(state.zoom) || 1);
            const cardScale = Math.max(1, Math.min(5, 1 / zoom));
            overlay.style.setProperty('--aa-magic-layer-process-card-scale', cardScale.toFixed(3));
        }

        function showMagicLayerCanvasLoading(message = 'Memproses Magic Layer AI...') {
            if (state.__aaMagicLayerProcessOverlayTimer) {
                window.clearTimeout(state.__aaMagicLayerProcessOverlayTimer);
                state.__aaMagicLayerProcessOverlayTimer = null;
            }

            const frame = els.aaActiveArtboardFrame || document.getElementById('aaActiveArtboardFrame');
            if (!frame) {
                if (typeof showCanvasLoading === 'function') showCanvasLoading(message);
                return;
            }

            let overlay = state.__aaMagicLayerProcessOverlay;
            if (!overlay || !frame.contains(overlay)) {
                overlay = document.createElement('div');
                overlay.className = 'aa-magic-layer-process-overlay';
                overlay.innerHTML = '<span class="aa-magic-layer-process-card"><i class="fa fa-circle-notch" aria-hidden="true"></i><span></span></span>';
                frame.appendChild(overlay);
                state.__aaMagicLayerProcessOverlay = overlay;
            }

            const label = overlay.querySelector('.aa-magic-layer-process-card span');
            if (label) label.textContent = String(message || 'Memproses Magic Layer AI...');
            syncMagicLayerCanvasLoadingScale(overlay);
            requestAnimationFrame(() => overlay.classList.add('is-visible'));
        }

        function hideMagicLayerCanvasLoading() {
            const overlay = state.__aaMagicLayerProcessOverlay;
            if (overlay) {
                overlay.classList.remove('is-visible');
                if (state.__aaMagicLayerProcessOverlayTimer) {
                    window.clearTimeout(state.__aaMagicLayerProcessOverlayTimer);
                }
                state.__aaMagicLayerProcessOverlayTimer = window.setTimeout(() => {
                    if (state.__aaMagicLayerProcessOverlay === overlay) {
                        overlay.remove();
                        state.__aaMagicLayerProcessOverlay = null;
                    }
                    state.__aaMagicLayerProcessOverlayTimer = null;
                }, 220);
            }
            if (typeof hideCanvasLoading === 'function') {
                hideCanvasLoading();
            }
        }

        function magicLayerUploadPageTitle(asset = {}) {
            const name = String(asset.name || 'Magic Layer').replace(/\.[a-z0-9]+$/i, '').trim();
            const safeName = name ? name.slice(0, 36) : `Magic Layer ${state.pages.length + 1}`;

            return `Magic Layer - ${safeName}`;
        }

        async function createMagicLayerUploadPage(asset = {}) {
            if (!state.canvas || !Array.isArray(state.pages)) return null;

            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }

            const previousIndex = Math.max(0, Math.min(Number(state.activePageIndex) || 0, state.pages.length - 1));
            storeCurrentPage();
            state.editMode = 'pages';
            showMagicLayerCanvasLoading('Membuat halaman Magic Layer...');

            const pageData = createBlankPageData(magicLayerUploadPageTitle(asset));
            pageData.aaMagicLayerUploadPage = true;
            pageData.aaMagicLayerSourceName = String(asset.name || '');

            state.pages.push(pageData);
            state.activePageIndex = state.pages.length - 1;
            loadPageData(pageData, {
                preserveZoom: false,
                snapshot: false,
            });

            if (state.loadPromise && typeof state.loadPromise.then === 'function') {
                await state.loadPromise;
            }
            showMagicLayerCanvasLoading('Membuat halaman Magic Layer...');

            setStatus('Halaman Magic Layer baru dibuat');

            return {
                previousIndex,
                pageIndex: state.activePageIndex,
                pageId: pageData.id,
            };
        }

        function cleanupFailedMagicLayerUploadPage(pageContext = null) {
            hideMagicLayerUploadPreview();

            if (!pageContext || !Array.isArray(state.pages) || state.pages.length <= 1) return;

            const index = state.pages.findIndex(page => page?.id === pageContext.pageId);
            if (index === -1) return;

            const page = state.pages[index] || {};
            const hasObjects = Array.isArray(page.objects) && page.objects.length > 0;
            const isCurrentEmpty = index === state.activePageIndex && state.canvas?.getObjects?.().length === 0;

            if (page.aaMagicLayerUploadPage !== true || (hasObjects && !isCurrentEmpty)) {
                return;
            }

            state.pages.splice(index, 1);
            state.activePageIndex = Math.max(0, Math.min(pageContext.previousIndex, state.pages.length - 1));
            loadPageData(state.pages[state.activePageIndex] || createBlankPageData('Halaman 1'), {
                preserveZoom: true,
                snapshot: false,
            });
            renderPageList();
        }

        async function magicLayerFromUploadedAsset(asset) {
            if (!asset || !asset.src) {
                throw new Error('Gambar Magic Layer tidak valid.');
            }
            if (guardAiPremiumFeature(null, 'Magic Layer')) return;
            if (!config.magicLayerEnabled) {
                throw new Error('Magic Layer sedang nonaktif. Gunakan Remove BG untuk Poof.');
            }
            if (!config.mediaMagicLayerUrl) {
                throw new Error('Service Magic Layer belum dikonfigurasi.');
            }

            setStatus('Memproses Magic Layer...', 'saving');
            setMagicLayerAiStatus('Membaca gambar upload dan membuat layer...', 'success');
            showMagicLayerCanvasLoading('AI memisahkan layer...');

            const sourceImg = prepareMagicLayerUploadedSourceImage(await loadFabricImage(asset.src));
            if (!sourceImg) {
                throw new Error('Gagal memuat gambar upload Magic Layer.');
            }
            sourceImg.set({
                src: asset.src,
                aaOriginalImageSrc: asset.src,
                aaOriginalImageName: asset.name || 'magic-layer-source',
                galleryImageName: asset.name || 'magic-layer-source',
            });
            showMagicLayerUploadPreview(sourceImg, 'AI memisahkan layer...');

            const imageGeometry = magicLayerImageGeometry(sourceImg);
            const blueprintPromise = config.ocrTextEnabled === true
                ? requestMagicLayerTextBlueprint(asset.src).catch(error => {
                    console.warn('[AdaAcara Magic Layer] OCR gagal:', error);
                    setMagicLayerAiStatus(error?.message || 'OCR teks gagal. Layer gambar tetap dibuat.', 'error');
                    return null;
                })
                : Promise.resolve(null);

            const form = new FormData();
            form.append('image_url', asset.src);
            form.append('page_id', String(config.pageId || ''));
            form.append('include_background', '0');
            const response = await fetch(config.mediaMagicLayerUrl, {
                method: 'POST',
                body: form,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const contentType = response.headers.get('content-type') || '';
            const data = contentType.includes('application/json') ? await response.json() : {};
            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Service Magic Layer gagal diproses.');
            }

            const subjectSrc = data.subject?.src;
            if (!subjectSrc) {
                throw new Error('Hasil Magic Layer tidak memiliki gambar subject.');
            }

            const subjectTrimPromise = aaTrimTransparentImageDataUrl(subjectSrc, {
                alphaThreshold: 8,
                padding: 18,
            }).catch(error => {
                console.warn('[AdaAcara Magic Layer] Auto trim gagal:', error);
                return {
                    src: subjectSrc,
                    trimmed: false,
                    trim: null
                };
            });

            const [subjectTrim, blueprint] = await Promise.all([
                subjectTrimPromise,
                blueprintPromise,
            ]);
            const finalSubjectSrc = subjectTrim?.src || subjectSrc;
            const subjectImg = await loadFabricImageWithRetry(finalSubjectSrc, {
                attempts: 4,
                delay: 550
            });

            if (!subjectImg) {
                throw new Error('Gagal memuat hasil gambar layer.');
            }

            subjectImg.set({ src: finalSubjectSrc, objectCaching: false });
            copyImageReplaceStyle(sourceImg, subjectImg, { preserveCrop: true });
            if (subjectTrim?.trim) {
                aaApplyTrimmedSubjectPlacement(sourceImg, subjectImg, subjectTrim.trim);
            }

            const subjectProps = {
                customType: 'image',
                aaRemovedBg: true,
                aaRemovedBgSrc: finalSubjectSrc,
                aaImageOutlineBaseSrc: finalSubjectSrc,
                aaImageOutlineAlphaEligible: true,
                aaOriginalImageSrc: finalSubjectSrc,
                aaOriginalImageName: 'magic-subject.png',
                aaMagicLayerAutoTrim: subjectTrim?.trimmed === true,
                galleryImageName: 'magic-subject.png',
            };
            subjectImg.set(subjectProps);
            const splitSubjectImages = await aaCreateSplitSubjectImages(finalSubjectSrc, sourceImg, subjectProps, {
                alphaThreshold: 8,
                padding: 18,
                maxComponents: 8,
                ocrBlueprint: blueprint,
            });
            const subjectObjects = Array.isArray(splitSubjectImages) && splitSubjectImages.length
                ? splitSubjectImages
                : [subjectImg];

            const magicObjects = await createMagicLayerTextObjects(blueprint, imageGeometry);
            hideMagicLayerUploadPreview();

            const index = state.canvas.getObjects().length;

            subjectObjects.forEach((object, offset) => {
                state.canvas.insertAt(object, index + offset, false);
            });
            magicObjects.covers.forEach((object, offset) => {
                state.canvas.insertAt(object, index + subjectObjects.length + offset, false);
            });
            magicObjects.texts.forEach((object, offset) => {
                state.canvas.insertAt(object, index + subjectObjects.length + magicObjects.covers.length + offset, false);
            });

            const nextActiveObject = magicObjects.texts[0] || subjectObjects[0] || null;
            if (nextActiveObject) {
                state.canvas.setActiveObject(nextActiveObject);
            }
            subjectObjects.forEach(object => object.setCoords());
            magicObjects.covers.forEach(object => object.setCoords());
            magicObjects.texts.forEach(object => object.setCoords());
            state.canvas.requestRenderAll();
            syncInspector();
            storeCurrentPage();
            renderPageList();
            snapshot();

            await loadMedia({ force: true, silent: true });
            setStatus('Magic Layer AI selesai. File asli dan hasil transparan tersimpan di media galeri.');
            setMagicLayerAiStatus(`${magicObjects.texts.length} teks editable dibuat. File asli dan hasil transparan tersimpan di media galeri.`, 'success');
        }

        async function magicLayerFromUploadedFile(file) {
            if (!file || state.__aaMagicLayerUploadProcessing === true) return;
            if (guardAiPremiumFeature(null, 'Magic Layer')) return;

            state.__aaMagicLayerUploadProcessing = true;
            let pageContext = null;
            let tempAsset = null;
            try {
                showMagicLayerCanvasLoading('Mengupload gambar...');
                setMediaUploadState('Mengupload gambar Magic Layer...');
                setMagicLayerAiStatus('Mengupload gambar maksimal 2MB...', 'success');
                tempAsset = await uploadSingleMagicLayerImage(file);
                await loadMedia({ force: true, silent: true });
                showMagicLayerCanvasLoading('Membuat halaman Magic Layer...');
                pageContext = await createMagicLayerUploadPage(tempAsset);
                await magicLayerFromUploadedAsset(tempAsset);
            } catch (error) {
                cleanupFailedMagicLayerUploadPage(pageContext);
                throw error;
            } finally {
                try {
                    await cleanupMagicLayerTempAsset(tempAsset);
                } finally {
                    state.__aaMagicLayerUploadProcessing = false;
                    hideMagicLayerUploadPreview();
                    setMediaUploadState('');
                }
            }
        }

        async function uploadImages(files) {
            if (guardMediaLibraryFeature()) return;
            let fileList = Array.from(files || []);
            if (state.mediaMode === 'gallery-photo') {
                fileList = fileList.slice(0, 1);
            }
            if (!fileList.length) return;

            const maxSize = 3 * 1024 * 1024;
            const oversizedFiles = fileList.filter(file => file.size > maxSize);
            if (oversizedFiles.length) {
                const names = oversizedFiles.slice(0, 3).map(file => file.name || 'Gambar').join(', ');
                const extra = oversizedFiles.length > 3 ? ` dan ${oversizedFiles.length - 3} lainnya` : '';
                const message =
                    `Upload dibatalkan. File terlalu besar: ${names}${extra}. Maksimal 3MB per gambar.`;
                setMediaUploadState(message, 'error');
                setStatus('Maksimal gambar 3MB', 'error');
                return;
            }

            let successCount = 0;
            const uploadedAssets = [];
            setMediaUploadState(`Mengupload ${fileList.length} gambar ke media library...`);
            setStatus('Upload media...', 'saving');

            for (const [index, file] of fileList.entries()) {
                setMediaUploadState(`Mengupload ${index + 1}/${fileList.length}: ${file.name || 'gambar'}`);
                const asset = await uploadSingleImage(file);
                if (asset && asset.src) {
                    successCount += 1;
                    uploadedAssets.push(asset);
                }
            }

            await loadMedia({ force: true });
            if (state.mediaMode === 'gallery-photo') {
                if (!canUsePremiumFeature()) {
                    state.mediaMode = 'insert';
                } else {
                    addGalleryPhotoAssets(uploadedAssets);
                }
            } else if (state.mediaMode === 'gallery') {
                if (!canUsePremiumFeature()) {
                    state.mediaMode = 'insert';
                } else {
                    await addGalleryAssets(uploadedAssets);
                    state.mediaMode = 'insert';
                }
            }
            const message = successCount > 1 ? `${successCount} gambar masuk media library` :
                'Gambar masuk media library';
            setMediaUploadState(message);
            setStatus(message);
            window.setTimeout(() => setMediaUploadState(''), 1800);
        }

        async function loadMedia(options = {}) {
            const force = options.force === true;
            const silent = options.silent === true;
            if (!canUseMediaLibrary()) {
                state.mediaAssets = [];
                state.mediaLibraryLoaded = false;
                setMediaUploadState('');
                if (els.aaMediaGrid) {
                    els.aaMediaGrid.innerHTML =
                        '<div class="col-span-3 rounded-xl border border-dashed border-slate-300 p-4 text-center text-xs font-bold text-slate-500">Login untuk memakai media library.</div>';
                }
                return;
            }
            if (!force && state.mediaLibraryLoaded) return;
            if (!silent) {
                setMediaUploadState('Memuat foto-foto media upload...');
            }
            if (els.aaMediaGrid && !silent) {
                els.aaMediaGrid.innerHTML =
                    '<div class="col-span-3 aa-panel-loading"><i aria-hidden="true"></i><span>Memuat foto-foto media upload...</span></div>';
            }

            let media = [];
            let mediaLoadFailed = false;
            try {
                const response = await fetch(config.mediaUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                });
                const data = await response.json().catch(() => ({
                    data: []
                }));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Media gagal dimuat.');
                }
                media = Array.isArray(data.data) ? data.data : [];
                state.mediaAssets = media;
                state.mediaLibraryLoaded = true;
            } catch (error) {
                mediaLoadFailed = true;
                state.mediaAssets = [];
                state.mediaLibraryLoaded = false;
                if (els.aaMediaGrid) {
                    els.aaMediaGrid.innerHTML =
                        '<div class="col-span-3 rounded-xl border border-dashed border-rose-200 bg-rose-50 p-4 text-center text-xs font-bold text-rose-700">Media gagal dimuat.</div>';
                }
                setStatus(error.message || 'Media gagal dimuat.', 'error');
                setMediaUploadState(error.message || 'Media gagal dimuat.', 'error');
                return;
            } finally {
                if (!mediaLoadFailed && !silent) {
                    window.setTimeout(() => setMediaUploadState(''), 350);
                }
            }

            state.selectedMediaIds = new Set(
                Array.from(state.selectedMediaIds || [])
                    .filter(id => media.some(item => String(item.id) === String(id)))
                    .map(String)
            );
            syncMediaBulkBar(media);

            if (!media.length) {
                els.aaMediaGrid.innerHTML =
                    '<div class="col-span-3 rounded-xl border border-dashed border-slate-300 p-4 text-center text-xs font-bold text-slate-500">Belum ada media.</div>';
                return;
            }

            function closeMediaMenus(exceptCard = null) {
                els.aaMediaGrid?.querySelectorAll?.('.aa-media-item.is-menu-open').forEach(card => {
                    if (card !== exceptCard) {
                        card.classList.remove('is-menu-open');
                    }
                });
            }

            function formatMediaSize(bytes) {
                bytes = Number(bytes) || 0;
                if (bytes <= 0) return '-';
                if (bytes < 1024) return `${bytes} B`;
                if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
                return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
            }

            function downloadMediaAsset(item) {
                if (!item?.src) return;
                const link = document.createElement('a');
                link.href = item.src;
                link.download = item.name || 'media';
                link.rel = 'noopener';
                document.body.appendChild(link);
                link.click();
                link.remove();
            }

            function showMediaDetail(item) {
                const message = [
                    `Nama: ${item?.name || '-'}`,
                    `Tipe: ${item?.mime || item?.type || '-'}`,
                    `Ukuran: ${formatMediaSize(item?.size)}`,
                    'Status: tersimpan aman. Jika dipindah ke trash, file tetap dipertahankan agar desain yang memakai gambar ini tidak rusak.',
                ].join('\n');

                if (typeof aaConfirm === 'function') {
                    aaConfirm(message, {
                        title: 'Detail Media',
                        okText: 'Tutup',
                        cancelText: 'Batal',
                    });
                    return;
                }

                window.alert(message);
            }

            function syncMediaMenuPlacement() {
                if (!els.aaMediaGrid) return;
                const gridRect = els.aaMediaGrid.getBoundingClientRect();
                const gridWidth = Math.max(1, gridRect.width || 1);
                const leftLimit = gridWidth / 3;
                const rightLimit = gridWidth - leftLimit;

                els.aaMediaGrid.querySelectorAll('.aa-media-item').forEach(card => {
                    const rect = card.getBoundingClientRect();
                    const center = (rect.left + rect.width / 2) - gridRect.left;
                    card.classList.remove('is-menu-left', 'is-menu-center', 'is-menu-right');
                    if (center <= leftLimit) {
                        card.classList.add('is-menu-left');
                    } else if (center >= rightLimit) {
                        card.classList.add('is-menu-right');
                    } else {
                        card.classList.add('is-menu-center');
                    }
                });
            }

            els.aaMediaGrid.innerHTML = '';
            media.forEach(item => {
                const card = document.createElement('div');
                card.className = 'aa-media-item';
                card.dataset.mediaId = String(item.id || '');
                card.classList.toggle('is-selected', state.selectedMediaIds.has(String(item.id)));

                const selectLabel = document.createElement('label');
                selectLabel.className = 'aa-media-select';
                selectLabel.title = 'Pilih media';
                const selectInput = document.createElement('input');
                selectInput.type = 'checkbox';
                selectInput.className = 'aa-media-select-input';
                selectInput.checked = state.selectedMediaIds.has(String(item.id));
                selectInput.setAttribute('aria-label', `Pilih ${item.name || 'media'}`);
                selectInput.addEventListener('change', event => {
                    event.stopPropagation();
                    toggleMediaSelection(item.id, event.currentTarget.checked);
                });
                selectLabel.addEventListener('click', event => event.stopPropagation());
                selectLabel.appendChild(selectInput);

                const pickButton = document.createElement('button');
                pickButton.type = 'button';
                pickButton.className = 'aa-media-pick';
                pickButton.draggable = true;
                pickButton.setAttribute('aria-label', item.name || 'Media');
                pickButton.innerHTML =
                    `<img src="${item.src}" alt="">`;
                pickButton.querySelector('img')?.addEventListener('load', () => {
                    requestAnimationFrame(syncMediaMenuPlacement);
                }, {
                    once: true
                });
                pickButton.addEventListener('dragstart', event => {
                    const payload = mediaAssetDragPayload(item);
                    if (!payload) return;
                    state.mediaDragAsset = {
                        src: item.src,
                        name: item.name || 'media',
                    };
                    event.dataTransfer.effectAllowed = 'copy';
                    event.dataTransfer.setData('application/x-aa-media', payload);
                    event.dataTransfer.setData('text/plain', payload);
                });
                pickButton.addEventListener('dragend', () => {
                    state.mediaDragAsset = null;
                    hideMediaDropPreview();
                });
                pickButton.addEventListener('click', () => {
                    const active = state.canvas.getActiveObject();
                    if (state.mediaMode === 'gallery-photo') {
                        if (!canUsePremiumFeature()) {
                            openEditorAccessModal();
                            state.mediaMode = 'insert';
                            return;
                        }
                        addGalleryPhotoAssets(item);
                        return;
                    }
                    if (state.mediaMode === 'gallery' || active?.customType ===
                        'photo-gallery') {
                        if (!canUsePremiumFeature()) {
                            openEditorAccessModal();
                            state.mediaMode = 'insert';
                            return;
                        }
                        addGalleryAssets(item);
                        state.mediaMode = 'insert';
                        return;
                    }
                    state.mediaMode = 'insert';
                    insertImage(item.src);
                });

                const menuButton = document.createElement('button');
                menuButton.type = 'button';
                menuButton.className = 'aa-media-more';
                menuButton.title = 'Aksi media';
                menuButton.setAttribute('aria-label', `Aksi ${item.name || 'media'}`);
                menuButton.innerHTML = '<i class="fa fa-ellipsis-vertical"></i>';
                menuButton.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    const isOpen = card.classList.contains('is-menu-open');
                    closeMediaMenus(card);
                    card.classList.toggle('is-menu-open', !isOpen);
                });

                const menu = document.createElement('div');
                menu.className = 'aa-media-menu';
                menu.setAttribute('role', 'menu');
                menu.innerHTML = `
                    <button type="button" data-aa-media-action="trash" role="menuitem"><i class="fa fa-trash-can"></i><span>Move to Trash</span></button>
                    <button type="button" data-aa-media-action="download" role="menuitem"><i class="fa fa-download"></i><span>Download</span></button>
                    <button type="button" data-aa-media-action="detail" role="menuitem"><i class="fa fa-circle-info"></i><span>Detail</span></button>
                `;
                menu.addEventListener('click', event => {
                    const button = event.target?.closest?.('[data-aa-media-action]');
                    if (!button) return;
                    event.preventDefault();
                    event.stopPropagation();
                    card.classList.remove('is-menu-open');
                    const action = button.dataset.aaMediaAction;
                    if (action === 'trash') {
                        deleteMedia(item.id);
                    } else if (action === 'download') {
                        downloadMediaAsset(item);
                    } else if (action === 'detail') {
                        showMediaDetail(item);
                    }
                });

                card.append(selectLabel, pickButton, menuButton, menu);
                els.aaMediaGrid.appendChild(card);
            });
            requestAnimationFrame(syncMediaMenuPlacement);
            syncMediaBulkBar(media);
        }

        document.addEventListener('click', event => {
            if (event.target?.closest?.('.aa-media-item')) return;
            els.aaMediaGrid?.querySelectorAll?.('.aa-media-item.is-menu-open').forEach(card => {
                card.classList.remove('is-menu-open');
            });
        });

        function toggleMediaSelection(id, selected) {
            if (!id) return;
            if (!(state.selectedMediaIds instanceof Set)) {
                state.selectedMediaIds = new Set(Array.from(state.selectedMediaIds || []).map(String));
            }
            const key = String(id);
            if (selected) {
                state.selectedMediaIds.add(key);
            } else {
                state.selectedMediaIds.delete(key);
            }
            const card = Array.from(els.aaMediaGrid?.querySelectorAll?.('.aa-media-item') || [])
                .find(item => item.dataset.mediaId === key);
            card?.classList.toggle('is-selected', selected);
            syncMediaBulkBar();
        }

        function syncMediaBulkBar(media = state.mediaAssets || []) {
            if (!els.aaMediaBulkBar) return;
            const ids = (media || []).map(item => String(item.id || '')).filter(Boolean);
            const selected = state.selectedMediaIds instanceof Set ? state.selectedMediaIds : new Set();
            const selectedCount = ids.filter(id => selected.has(id)).length;
            els.aaMediaBulkBar.classList.toggle('hidden', ids.length === 0);
            if (els.aaDeleteSelectedMediaBtn) {
                els.aaDeleteSelectedMediaBtn.disabled = selectedCount === 0;
                const label = els.aaDeleteSelectedMediaBtn.querySelector('span');
                if (label) label.textContent = selectedCount ? `Trash ${selectedCount}` : 'Trash terpilih';
            }
            if (els.aaMediaSelectAllInput) {
                els.aaMediaSelectAllInput.checked = ids.length > 0 && selectedCount === ids.length;
                els.aaMediaSelectAllInput.indeterminate = selectedCount > 0 && selectedCount < ids.length;
            }
        }

        async function deleteMediaById(id) {
            if (!id) return false;
            const response = await fetch(`${config.mediaDeleteUrl}/${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Media gagal dipindahkan ke trash');
            }
            return true;
        }

        async function deleteSelectedMedia() {
            const selectedIds = Array.from(state.selectedMediaIds || []);
            if (!selectedIds.length) return;
            if (!await aaConfirm(`Pindahkan ${selectedIds.length} gambar terpilih ke trash? File tetap disimpan agar desain yang memakai gambar ini tidak rusak.`, {
                    title: 'Move to Trash',
                    okText: 'Move to Trash',
                    cancelText: 'Batal',
                    danger: true
                })) return;

            setStatus(`Memindahkan ${selectedIds.length} media ke trash...`, 'saving');
            let deletedCount = 0;
            for (const id of selectedIds) {
                try {
                    await deleteMediaById(id);
                    deletedCount += 1;
                } catch (error) {
                    setStatus(error.message || 'Sebagian media gagal dihapus', 'error');
                    break;
                }
            }
            state.selectedMediaIds = new Set();
            setStatus(deletedCount === selectedIds.length ? `${deletedCount} media masuk trash` : `${deletedCount} media berhasil dipindahkan`);
            await loadMedia({ force: true });
        }

        async function deleteMedia(id) {
            if (!id) return;
            if (!await aaConfirm('Pindahkan gambar ini ke trash? File tetap disimpan agar desain yang memakai gambar ini tidak rusak.', {
                    title: 'Move to Trash',
                    okText: 'Move to Trash',
                    cancelText: 'Batal',
                    danger: true
                })) return;

            setStatus('Memindahkan media ke trash...', 'saving');
            try {
                await deleteMediaById(id);
            } catch (error) {
                setStatus(error.message || 'Media gagal dihapus', 'error');
                return;
            }
            state.selectedMediaIds?.delete?.(String(id));

            setStatus('Media masuk trash');
            await loadMedia({ force: true });
        }

        function duplicateActive() {
            if (!state.canvas || !window.fabric) return false;

            const active = state.canvas.getActiveObject();

            if (!active || active === state.cropBox) return false;

            const previousClipboard = state.clipboardObjectJson
                ? JSON.parse(JSON.stringify(state.clipboardObjectJson))
                : null;

            const copied = copyActiveObject?.();

            if (!copied || !state.clipboardObjectJson) {
                return false;
            }

            pasteClipboardObject?.(null);

            setTimeout(function () {
                state.clipboardObjectJson = previousClipboard;
            }, 120);

            return true;
        }
        function deleteActive() {
            const active = state.canvas.getActiveObject();
            if (!active) return;
            if (active === state.cropBox) {
                finishCropMode();
                return;
            }

            if (active.type === 'activeSelection' && typeof active.getObjects === 'function') {
                const objects = active.getObjects().filter(object => object && object !== state.cropBox);
                state.canvas.discardActiveObject();
                objects.forEach(object => {
                    if (state.canvas.getObjects().includes(object)) {
                        state.canvas.remove(object);
                    }
                });
            } else {
                state.canvas.remove(active);
                state.canvas.discardActiveObject();
            }

            state.canvas.requestRenderAll();
            syncInspector();
            if (typeof storeCurrentPage === 'function') {
                storeCurrentPage();
            }
            snapshot();
        }

        function setObjectLocked(object, locked) {
            if (!object) return;
            object.set({
                locked: Boolean(locked),
                lockMovementX: Boolean(locked),
                lockMovementY: Boolean(locked),
                lockScalingX: Boolean(locked),
                lockScalingY: Boolean(locked),
                lockRotation: Boolean(locked),
                hasControls: !locked,
                selectable: true,
                evented: true,
            });
            object.hoverCursor = locked ? 'default' : 'move';
            object.setCoords();
        }

        function getObjectToolbarTarget() {
            const active = state.canvas?.getActiveObject();
            if (!active || active === state.cropBox || state.isCropping) return null;
            return active;
        }

        <?= view('editor/partials/scripts/interactions', get_defined_vars()) ?>

        <?= view('editor/partials/scripts/crop', get_defined_vars()) ?>

        function getAnimationSnapshot(object) {
            return {
                left: object.left,
                top: object.top,
                opacity: object.opacity ?? 1,
                scaleX: object.scaleX || 1,
                scaleY: object.scaleY || 1,
                angle: object.angle || 0,
                shadow: object.shadow || null,
                clipPath: object.clipPath || null,
            };
        }

        function restoreAnimationSnapshot(object, original) {
            object.set(original);
            state.canvas.requestRenderAll();
        }

        function previewObjectAnimation(animationName, object) {
            if (!object || animationName === 'none') return;
            if (state.isPreviewingAnimation) return;

            const original = getAnimationSnapshot(object);
            state.isPreviewingAnimation = true;

            const render = () => state.canvas.requestRenderAll();
            const finish = () => {
                restoreAnimationSnapshot(object, original);
                state.isPreviewingAnimation = false;
            };

            object.set(original);

            if (animationName === 'fade-in') {
                object.set({
                    opacity: 0
                });
                object.animate('opacity', original.opacity, {
                    duration: 520,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (animationName === 'rise') {
                object.set({
                    top: original.top + 70,
                    opacity: 0
                });
                object.animate('top', original.top, {
                    duration: 560,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                });
                object.animate('opacity', original.opacity, {
                    duration: 560,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (['fade-up', 'fade-down', 'fade-left', 'fade-right'].includes(animationName)) {
                const offset = 78;
                const from = {
                    opacity: 0,
                    top: original.top,
                    left: original.left,
                };
                if (animationName === 'fade-up') from.top = original.top + offset;
                if (animationName === 'fade-down') from.top = original.top - offset;
                if (animationName === 'fade-left') from.left = original.left + offset;
                if (animationName === 'fade-right') from.left = original.left - offset;
                object.set(from);
                object.animate('left', original.left, {
                    duration: 580,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                });
                object.animate('top', original.top, {
                    duration: 580,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                });
                object.animate('opacity', original.opacity, {
                    duration: 520,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (['slide-up', 'slide-down', 'slide-left', 'slide-right'].includes(animationName)) {
                const offset = 110;
                const from = {
                    top: original.top,
                    left: original.left,
                };
                if (animationName === 'slide-up') from.top = original.top + offset;
                if (animationName === 'slide-down') from.top = original.top - offset;
                if (animationName === 'slide-left') from.left = original.left + offset;
                if (animationName === 'slide-right') from.left = original.left - offset;
                object.set(from);
                object.animate('left', original.left, {
                    duration: 620,
                    easing: fabric.util.ease.easeOutBack,
                    onChange: render,
                });
                object.animate('top', original.top, {
                    duration: 620,
                    easing: fabric.util.ease.easeOutBack,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (animationName === 'zoom-in') {
                object.set({
                    scaleX: original.scaleX * .72,
                    scaleY: original.scaleY * .72,
                    opacity: 0,
                });
                object.animate('scaleX', original.scaleX, {
                    duration: 520,
                    easing: fabric.util.ease.easeOutBack,
                    onChange: render,
                });
                object.animate('scaleY', original.scaleY, {
                    duration: 520,
                    easing: fabric.util.ease.easeOutBack,
                    onChange: render,
                });
                object.animate('opacity', original.opacity, {
                    duration: 420,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (animationName === 'zoom-out') {
                object.set({
                    scaleX: original.scaleX * 1.34,
                    scaleY: original.scaleY * 1.34,
                    opacity: 0,
                });
                object.animate('scaleX', original.scaleX, {
                    duration: 560,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                });
                object.animate('scaleY', original.scaleY, {
                    duration: 560,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                });
                object.animate('opacity', original.opacity, {
                    duration: 450,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (animationName === 'flip-in') {
                object.set({
                    scaleX: Math.max(.01, original.scaleX * .08),
                    opacity: 0,
                });
                object.animate('scaleX', original.scaleX, {
                    duration: 620,
                    easing: fabric.util.ease.easeOutBack,
                    onChange: render,
                });
                object.animate('opacity', original.opacity, {
                    duration: 420,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (animationName === 'bounce') {
                object.set({
                    top: original.top - 48
                });
                object.animate('top', original.top, {
                    duration: 620,
                    easing: fabric.util.ease.easeOutBounce,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (animationName === 'pulse') {
                object.animate('scaleX', original.scaleX * 1.14, {
                    duration: 230,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                    onComplete: () => object.animate('scaleX', original.scaleX, {
                        duration: 260,
                        easing: fabric.util.ease.easeOutCubic,
                        onChange: render,
                    }),
                });
                object.animate('scaleY', original.scaleY * 1.14, {
                    duration: 230,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                    onComplete: () => object.animate('scaleY', original.scaleY, {
                        duration: 260,
                        easing: fabric.util.ease.easeOutCubic,
                        onChange: render,
                        onComplete: finish,
                    }),
                });
                return;
            }

            if (animationName === 'swing') {
                object.set({
                    angle: original.angle - 10
                });
                object.animate('angle', original.angle + 10, {
                    duration: 240,
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: () => object.animate('angle', original.angle, {
                        duration: 260,
                        easing: fabric.util.ease.easeInOutSine,
                        onChange: render,
                        onComplete: finish,
                    }),
                });
                return;
            }

            if (animationName === 'float-loop') {
                object.animate('top', original.top - 34, {
                    duration: 520,
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: () => object.animate('top', original.top, {
                        duration: 520,
                        easing: fabric.util.ease.easeInOutSine,
                        onChange: render,
                        onComplete: finish,
                    }),
                });
                return;
            }

            if (animationName === 'sway-loop') {
                object.animate('angle', original.angle + 8, {
                    duration: 420,
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: () => object.animate('angle', original.angle - 8, {
                        duration: 420,
                        easing: fabric.util.ease.easeInOutSine,
                        onChange: render,
                        onComplete: finish,
                    }),
                });
                return;
            }

            if (animationName === 'pulse-loop' || animationName === 'heartbeat-loop') {
                const amount = animationName === 'heartbeat-loop' ? 1.18 : 1.1;
                object.animate('scaleX', original.scaleX * amount, {
                    duration: 250,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                    onComplete: () => object.animate('scaleX', original.scaleX, {
                        duration: 260,
                        easing: fabric.util.ease.easeInOutSine,
                        onChange: render,
                    }),
                });
                object.animate('scaleY', original.scaleY * amount, {
                    duration: 250,
                    easing: fabric.util.ease.easeOutCubic,
                    onChange: render,
                    onComplete: () => object.animate('scaleY', original.scaleY, {
                        duration: 260,
                        easing: fabric.util.ease.easeInOutSine,
                        onChange: render,
                        onComplete: finish,
                    }),
                });
                return;
            }

            if (animationName === 'drift-loop') {
                object.animate('left', original.left + 28, {
                    duration: 520,
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: () => object.animate('left', original.left, {
                        duration: 520,
                        easing: fabric.util.ease.easeInOutSine,
                        onChange: render,
                        onComplete: finish,
                    }),
                });
                return;
            }

            if (animationName === 'spin-loop') {
                object.animate('angle', original.angle + 360, {
                    duration: 760,
                    easing: fabric.util.ease.easeInOutCubic,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            if (animationName === 'spin') {
                object.animate('angle', original.angle + 360, {
                    duration: 760,
                    easing: fabric.util.ease.easeInOutCubic,
                    onChange: render,
                    onComplete: finish,
                });
                return;
            }

            finish();
        }

        function setActiveAnimation(animationName) {
            const active = state.canvas.getActiveObject();
            if (!active) return;

            const value = animationName === 'none' ? 'none' : animationName;
            active.set({
                aaAnimation: value,
                customAnimation: value,
                animationPreset: value
            });
            if (getContextTextTarget(active)) {
                active.set('aaTextAnimation', aaNormalizeTextAnimationConfig({
                    enabled: false,
                    type: 'none'
                }));
            }
            syncAnimationButtons(active);
            syncTextAnimationToolbar(active);
            storeCurrentPage();
            snapshot();
            syncInspector();
            setStatus('Animasi diperbarui');
            if (animationName !== 'none') {
                previewObjectAnimation(animationName, active);
            }
        }

        const aaTextAnimationTypes = new Set([
            'none',
            'typewriter',
            'letter-fade-up',
            'letter-wave',
            'word-reveal',
            'text-glow',
            'shine-text',
        ]);

        function aaClampTextAnimationTiming(value, min, max, fallback) {
            const number = Number(value);
            if (!Number.isFinite(number)) return fallback;
            return Math.max(min, Math.min(max, Math.round(number)));
        }

        function aaNormalizeTextAnimationConfig(value = null) {
            const source = value && typeof value === 'object' ? value : {};
            const type = aaTextAnimationTypes.has(source.type) ? source.type : 'none';
            const enabled = source.enabled === true && type !== 'none';
            return {
                enabled,
                type: enabled ? type : 'none',
                delay: aaClampTextAnimationTiming(source.delay, 0, 5000, 0),
                duration: aaClampTextAnimationTiming(source.duration, 200, 8000, 1200),
                stagger: aaClampTextAnimationTiming(source.stagger, 0, 300, 40),
                loop: source.loop === true || type === 'text-glow' || type === 'shine-text',
            };
        }

        function aaGetTextAnimationConfig(object) {
            return aaNormalizeTextAnimationConfig(object?.aaTextAnimation);
        }

        function syncTextAnimationToolbar(active = state.canvas?.getActiveObject()) {
            const target = getContextTextTarget(active);
            const config = aaGetTextAnimationConfig(target);
            const enabled = Boolean(target && config.enabled);
            const textSection = document.getElementById('aaTextAnimationOptionsSection');
            const staggerControl = document.getElementById('aaTextAnimationStaggerControl');

            if (textSection) textSection.hidden = !target;
            if (staggerControl) staggerControl.hidden = !target;
            document.querySelectorAll('[data-aa-text-animation]').forEach(button => {
                button.classList.toggle('is-active', button.dataset.aaTextAnimation === config.type);
            });
            if (enabled) {
                document.querySelectorAll('[data-aa-animation]').forEach(button => {
                    button.classList.remove('is-active');
                });
                document.querySelectorAll('[data-aa-animation-delay]').forEach(input => {
                    input.value = String(config.delay);
                });
                document.querySelectorAll('[data-aa-animation-delay-output]').forEach(output => {
                    output.textContent = `${config.delay}ms`;
                });
                document.querySelectorAll('[data-aa-animation-duration]').forEach(input => {
                    input.value = String(config.duration);
                });
                document.querySelectorAll('[data-aa-animation-duration-output]').forEach(output => {
                    output.textContent = `${config.duration}ms`;
                });
            }
            document.querySelectorAll('[data-aa-text-animation-stagger]').forEach(input => {
                input.value = String(config.stagger);
            });
            document.querySelectorAll('[data-aa-text-animation-stagger-output]').forEach(output => {
                output.textContent = `${config.stagger}ms`;
            });
        }

        function setActiveTextAnimation(type) {
            const active = state.canvas.getActiveObject();
            const target = getContextTextTarget(active);
            if (!target || !aaTextAnimationTypes.has(type)) return;

            const previous = aaGetTextAnimationConfig(target);
            const next = aaNormalizeTextAnimationConfig({
                ...previous,
                enabled: type !== 'none',
                type,
                loop: type === 'text-glow' || type === 'shine-text',
            });

            target.set('aaTextAnimation', next);
            if (next.enabled) {
                target.set({
                    aaAnimation: 'none',
                    customAnimation: 'none',
                    animationPreset: 'none'
                });
            }
            target.dirty = true;
            syncAnimationButtons(target);
            syncTextAnimationToolbar(target);
            storeCurrentPage();
            snapshot();
            syncInspector();
            setStatus(type === 'none' ? 'Text Animate dihapus' : 'Text Animate diperbarui');
        }

        function updateActiveTextAnimationTiming(commit = false, sourceInput = null) {
            const active = state.canvas.getActiveObject();
            const target = getContextTextTarget(active);
            if (!target) return;

            const current = aaGetTextAnimationConfig(target);
            const animationPanel = document.getElementById('aaAnimationPanel');
            const delayInput = animationPanel?.querySelector('[data-aa-animation-delay]') ||
                document.querySelector('[data-aa-animation-delay]');
            const durationInput = animationPanel?.querySelector('[data-aa-animation-duration]') ||
                document.querySelector('[data-aa-animation-duration]');
            const staggerInput = animationPanel?.querySelector('[data-aa-text-animation-stagger]') ||
                document.querySelector('[data-aa-text-animation-stagger]');
            const delaySource = sourceInput?.matches?.('[data-aa-animation-delay]') ? sourceInput : delayInput;
            const durationSource = sourceInput?.matches?.('[data-aa-animation-duration]') ? sourceInput : durationInput;
            const staggerSource = sourceInput?.matches?.('[data-aa-text-animation-stagger]') ? sourceInput : staggerInput;

            target.set('aaTextAnimation', aaNormalizeTextAnimationConfig({
                ...current,
                delay: delaySource ? delaySource.value : current.delay,
                duration: durationSource ? durationSource.value : current.duration,
                stagger: staggerSource ? staggerSource.value : current.stagger,
            }));
            target.dirty = true;
            syncTextAnimationToolbar(target);
            storeCurrentPage();
            syncInspector();
            if (commit) snapshot();
            setStatus('Timing Text Animate diperbarui');
        }

        function getTextAnimationPreviewSnapshot(object) {
            return {
                text: object.text || '',
                top: object.top,
                opacity: object.opacity ?? 1,
                fill: object.fill,
                shadow: object.shadow || null,
                charSpacing: object.charSpacing || 0,
            };
        }

        function restoreTextAnimationPreviewSnapshot(object, original) {
            object.set(original);
            if (typeof object.initDimensions === 'function') object.initDimensions();
            object.setCoords();
            state.canvas.requestRenderAll();
        }

        function previewTextAnimation(type) {
            const active = state.canvas.getActiveObject();
            const target = getContextTextTarget(active);
            if (!target || !aaTextAnimationTypes.has(type) || type === 'none') return;
            if (state.isPreviewingTextAnimation) return;

            const original = getTextAnimationPreviewSnapshot(target);
            const config = aaNormalizeTextAnimationConfig({
                ...aaGetTextAnimationConfig(target),
                enabled: true,
                type,
                duration: Math.min(900, aaGetTextAnimationConfig(target).duration || 900),
                delay: 0,
            });
            const render = () => state.canvas.requestRenderAll();
            const finish = () => {
                restoreTextAnimationPreviewSnapshot(target, original);
                state.isPreviewingTextAnimation = false;
            };
            const text = String(original.text || '');
            const useWords = type === 'word-reveal' || text.length > 140;
            const units = useWords ? text.split(/(\s+)/) : Array.from(text);
            let start = null;

            state.isPreviewingTextAnimation = true;

            if (type === 'text-glow' || type === 'shine-text') {
                target.set({
                    opacity: original.opacity,
                    shadow: new fabric.Shadow({
                        color: type === 'shine-text' ? 'rgba(255,255,255,.72)' : String(original.fill || '#111827'),
                        blur: 18,
                        offsetX: 0,
                        offsetY: 0
                    }),
                    fill: type === 'shine-text' ? '#ffffff' : original.fill
                });
                render();
                window.setTimeout(finish, 520);
                return;
            }

            if (type === 'letter-wave') {
                const stepWave = time => {
                    if (start === null) start = time;
                    const progress = Math.min(1, (time - start) / config.duration);
                    const wave = Math.sin(progress * Math.PI * 4);
                    target.set({
                        opacity: original.opacity,
                        top: original.top + wave * 7,
                        charSpacing: original.charSpacing + Math.round(wave * 18)
                    });
                    render();
                    if (progress < 1) requestAnimationFrame(stepWave);
                    else finish();
                };
                requestAnimationFrame(stepWave);
                return;
            }

            target.set({
                opacity: type === 'letter-fade-up' ? 0 : original.opacity,
                top: type === 'letter-fade-up' ? original.top + 20 : original.top,
                text: ''
            });
            render();
            const stepReveal = time => {
                if (start === null) start = time;
                const progress = Math.min(1, (time - start) / config.duration);
                const count = Math.min(units.length, Math.ceil(progress * units.length));
                target.set('text', units.slice(0, count).join(''));
                if (type === 'letter-fade-up') {
                    target.set({
                        opacity: original.opacity * progress,
                        top: original.top + (20 * (1 - progress))
                    });
                }
                if (typeof target.initDimensions === 'function') target.initDimensions();
                target.setCoords();
                render();
                if (progress < 1) requestAnimationFrame(stepReveal);
                else window.setTimeout(finish, 120);
            };
            requestAnimationFrame(stepReveal);
        }

        <?= view('editor/partials/scripts/public_preview', get_defined_vars()) ?>

        <?= view('editor/partials/scripts/save_publish', get_defined_vars()) ?>

        function openTemplatePreview(templateId, title = 'Template', publicUrl = '') {
            const id = Number(templateId) || 0;
            if (!id || !els.aaTemplatePreviewModal || !els.aaTemplatePreviewFrame) return;
            if (els.aaTemplatePreviewTitle) {
                els.aaTemplatePreviewTitle.textContent = title || 'Template';
            }
            const previewUrl = String(publicUrl || '').trim();
            els.aaTemplatePreviewFrame.src = previewUrl || `${config.templatePreviewBaseUrl}/${encodeURIComponent(id)}`;
            els.aaTemplatePreviewModal.classList.add('is-open');
        }

        function closeTemplatePreview() {
            els.aaTemplatePreviewModal?.classList.remove('is-open');
            if (els.aaTemplatePreviewFrame) {
                els.aaTemplatePreviewFrame.src = 'about:blank';
            }
        }

        function showTemplateCategoryView() {
            if (els.aaTemplateCategoryView) {
                els.aaTemplateCategoryView.hidden = false;
            }
            if (els.aaTemplateListView) {
                els.aaTemplateListView.hidden = true;
            }
            if (els.aaTemplateSearchInput) {
                els.aaTemplateSearchInput.value = '';
            }
            document.querySelectorAll('[data-aa-template-preview]').forEach(card => {
                card.hidden = true;
            });
            els.aaTemplateEmptyState?.classList.remove('is-visible');
        }

        function showTemplateListView(category, label) {
            if (!category) return;
            if (els.aaTemplateCategoryView) {
                els.aaTemplateCategoryView.hidden = true;
            }
            if (els.aaTemplateListView) {
                els.aaTemplateListView.hidden = false;
                els.aaTemplateListView.dataset.aaSelectedTemplateCategory = category;
            }
            if (els.aaTemplateCurrentCategoryTitle) {
                els.aaTemplateCurrentCategoryTitle.textContent = label || 'Template';
            }
            if (els.aaTemplateSearchInput) {
                els.aaTemplateSearchInput.value = '';
            }
            filterTemplateDrawer();
        }

        function filterTemplateDrawer() {
            const cards = Array.from(document.querySelectorAll('[data-aa-template-preview]'));
            if (!cards.length) return;

            const query = String(els.aaTemplateSearchInput?.value || '').trim().toLowerCase();
            const activeCategory = els.aaTemplateListView?.dataset?.aaSelectedTemplateCategory || '';
            let visibleCount = 0;

            cards.forEach(card => {
                const cardSearch = String(card.dataset.aaTemplateSearch || card.dataset.aaTemplateTitle ||
                        '')
                    .toLowerCase();
                const matchesQuery = !query || cardSearch.includes(query);
                const matchesCategory = activeCategory && card.dataset.aaTemplateCategory === activeCategory;
                const visible = matchesQuery && matchesCategory;
                card.hidden = !visible;
                if (visible) visibleCount++;
            });

            els.aaTemplateEmptyState?.classList.toggle('is-visible', visibleCount === 0);
        }

        function setTemplateLoadingState(message = '') {
            const box = els.aaTemplateLoadingState;
            if (!box) return;
            const text = box.querySelector('span');
            if (!message) {
                box.hidden = true;
                if (text) text.textContent = '';
                return;
            }
            if (text) text.textContent = message;
            box.hidden = false;
        }

        function waitForTemplateImages() {
            const images = Array.from((els.aaTemplateGrid || document).querySelectorAll('img'))
                .filter(image => !image.complete);
            if (!images.length) {
                setTemplateLoadingState('');
                return;
            }
            let pending = images.length;
            const done = () => {
                pending -= 1;
                if (pending <= 0) setTemplateLoadingState('');
            };
            images.forEach(image => {
                image.addEventListener('load', done, { once: true });
                image.addEventListener('error', done, { once: true });
            });
            window.setTimeout(() => setTemplateLoadingState(''), 2400);
        }

        function showTemplateDrawerLoading() {
            if (!els.aaTemplateGrid) return;
            setTemplateLoadingState('Memuat templates...');
            window.requestAnimationFrame(() => {
                filterTemplateDrawer();
                waitForTemplateImages();
            });
        }

        async function ensureEditorAssetLibrary() {
            if (state.editorAssets.length || !config.editorAssetLibraryUrl) return;
            if (els.aaEditorAssetGrid) {
                els.aaEditorAssetGrid.innerHTML =
                    '<div class="aa-panel-loading"><i aria-hidden="true"></i><span>Memuat ornament dan asset bawaan...</span></div>';
            }
            if (els.aaEditorAssetCategoryGrid) {
                els.aaEditorAssetCategoryGrid.innerHTML =
                    '<div class="aa-panel-loading"><i aria-hidden="true"></i><span>Memuat kategori ornament...</span></div>';
            }
            const response = await fetch(config.editorAssetLibraryUrl, {
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Asset gagal dimuat.');
            }
            state.editorAssets = Array.isArray(data.items) ? data.items : [];
            state.editorAssetCategories = Array.isArray(data.categories) ? data.categories : [];
            renderEditorAssetUploadCategories(state.editorAssetCategories);
        }

        function setEditorAssetPanelLoading(message = '') {
            if (!message) return;
            const loading = document.createElement('div');
            loading.className = 'aa-panel-loading';
            loading.innerHTML = '<i aria-hidden="true"></i><span></span>';
            const text = loading.querySelector('span');
            if (text) text.textContent = message;
            if (state.editorAssetCategory && els.aaEditorAssetGrid) {
                els.aaEditorAssetGrid.replaceChildren(loading);
                return;
            }
            if (els.aaEditorAssetCategoryGrid) {
                els.aaEditorAssetCategoryGrid.replaceChildren(loading);
            }
        }

        function renderEditorAssetUploadCategories(categories) {
            if (!els.aaEditorAssetUploadCategory) return;
            const selected = els.aaEditorAssetUploadCategory.value || '';
            els.aaEditorAssetUploadCategory.innerHTML = '<option value="">Pilih kategori</option>';
            categories.forEach(category => {
                const slug = category.slug || category.name || '';
                if (!slug) return;
                const option = document.createElement('option');
                option.value = slug;
                option.textContent = editorAssetDisplayCategoryName(category.name || slug, slug);
                els.aaEditorAssetUploadCategory.appendChild(option);
            });
            if (selected && [...els.aaEditorAssetUploadCategory.options].some(option => option.value ===
                    selected)) {
                els.aaEditorAssetUploadCategory.value = selected;
            }
        }

        function editorAssetDisplayCategoryName(name = '', slug = '') {
            const rawName = String(name || '').trim();
            const rawSlug = String(slug || '').trim();
            const normalizedName = rawName.toLowerCase();
            const normalizedSlug = rawSlug.toLowerCase();
            if (normalizedName === 'lainnya' || normalizedSlug === 'lainnya') {
                return 'Bank & Dompet Digital';
            }
            return rawName || rawSlug || 'Asset';
        }

        function editorAssetCategoryIcon(label = '', slug = '') {
            const text = String(label + ' ' + slug).toLowerCase();
            if (text.includes('wedding') || text.includes('nikah') || text.includes('pernikahan')) return 'fa-heart';
            if (text.includes('ultah') || text.includes('birthday') || text.includes('ulang')) return 'fa-cake-candles';
            if (text.includes('islam') || text.includes('ramadan') || text.includes('eid')) return 'fa-mosque';
            if (text.includes('aqiqah') || text.includes('baby')) return 'fa-baby';
            if (text.includes('khitan')) return 'fa-star-and-crescent';
            if (text.includes('corporate') || text.includes('bisnis')) return 'fa-briefcase';
            if (text.includes('seminar') || text.includes('webinar')) return 'fa-chalkboard-user';
            if (text.includes('wisuda')) return 'fa-graduation-cap';
            if (text.includes('luxury')) return 'fa-gem';
            if (text.includes('minimal')) return 'fa-wand-magic-sparkles';
            return 'fa-shapes';
        }

        function editorAssetCategoryPalette(index) {
            const palettes = [
                ['#ecfeff', '#0891b2'],
                ['#fdf2f8', '#db2777'],
                ['#fef3c7', '#b45309'],
                ['#eef2ff', '#4f46e5'],
                ['#ecfdf5', '#059669'],
                ['#f5f3ff', '#7c3aed'],
                ['#fff7ed', '#ea580c'],
                ['#f0f9ff', '#0284c7'],
            ];
            return palettes[index % palettes.length];
        }

        function getEditorAssetDrawerCategories() {
            const map = new Map();
            state.editorAssetCategories.forEach(category => {
                const slug = String(category.slug || category.name || '').trim();
                if (!slug) return;
                map.set(slug, editorAssetDisplayCategoryName(category.name || slug, slug));
            });
            state.editorAssets.forEach(asset => {
                const slug = String(asset.category || '').trim();
                if (!slug || map.has(slug)) return;
                map.set(slug, editorAssetDisplayCategoryName(asset.categoryName || slug, slug));
            });
            return Array.from(map.entries()).map(([slug, name]) => ({
                slug,
                name
            })).sort((a, b) => String(a.name).localeCompare(String(b.name)));
        }

        function showEditorAssetCategoryView() {
            if (els.aaEditorAssetCategoryView) els.aaEditorAssetCategoryView.hidden = false;
            if (els.aaEditorAssetListView) els.aaEditorAssetListView.hidden = true;
            state.editorAssetCategory = '';
            state.editorAssetQuery = '';
            state.editorAssetVisible = 40;
            if (els.aaEditorAssetSearchInput) els.aaEditorAssetSearchInput.value = '';
            if (els.aaEditorAssetMoreBtn) els.aaEditorAssetMoreBtn.hidden = true;
        }

        function showEditorAssetListView(category, label) {
            if (!category) return;
            state.editorAssetCategory = category;
            state.editorAssetQuery = '';
            state.editorAssetVisible = 40;
            if (els.aaEditorAssetSearchInput) els.aaEditorAssetSearchInput.value = '';
            if (els.aaEditorAssetCategoryView) els.aaEditorAssetCategoryView.hidden = true;
            if (els.aaEditorAssetListView) els.aaEditorAssetListView.hidden = false;
            if (els.aaEditorAssetCurrentCategoryTitle) {
                els.aaEditorAssetCurrentCategoryTitle.textContent = label || 'Asset';
            }
            renderEditorAssets();
        }

        function renderEditorAssetCategoryCards() {
            if (!els.aaEditorAssetCategoryGrid) return;
            const categories = getEditorAssetDrawerCategories();
            els.aaEditorAssetCategoryGrid.innerHTML = '';
            if (!categories.length) {
                els.aaEditorAssetCategoryGrid.innerHTML =
                    '<div class="aa-gallery-empty">Belum ada kategori asset.</div>';
                return;
            }
            categories.forEach((category, index) => {
                const palette = editorAssetCategoryPalette(index);
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'aa-template-category-card';
                button.dataset.aaEditorAssetCategory = category.slug;
                button.dataset.aaEditorAssetCategoryLabel = category.name;
                button.style.setProperty('--aa-template-category-bg', palette[0]);
                button.style.setProperty('--aa-template-category-fg', palette[1]);
                button.innerHTML = `
                    <span class="aa-template-category-icon">
                        <i class="fa ${editorAssetCategoryIcon(category.name, category.slug)}" aria-hidden="true"></i>
                    </span>
                    <span class="aa-template-category-name">${escapeHtml(category.name)}</span>
                `;
                els.aaEditorAssetCategoryGrid.appendChild(button);
            });
        }

        function filteredEditorAssets() {
            const query = state.editorAssetQuery.trim().toLowerCase();
            return state.editorAssets.filter(asset => {
                if (state.editorAssetType !== 'all' && asset.type !== state.editorAssetType) return false;
                if (state.editorAssetCategory && state.editorAssetCategory !== 'all' && asset.category !== state.editorAssetCategory)
                    return false;
                if (!query) return true;
                const haystack = [
                    asset.id,
                    asset.name,
                    asset.type,
                    asset.category,
                    ...(Array.isArray(asset.tags) ? asset.tags : []),
                ].join(' ').toLowerCase();
                return haystack.includes(query);
            });
        }

        function renderEditorAssets() {
            if (!els.aaEditorAssetGrid) return;
            const assets = filteredEditorAssets();
            const visible = assets.slice(0, state.editorAssetVisible);
            els.aaEditorAssetGrid.innerHTML = '';
            if (!visible.length) {
                els.aaEditorAssetGrid.innerHTML = '<div class="aa-gallery-empty">Asset tidak ditemukan.</div>';
            } else {
                visible.forEach(asset => {
                    const card = document.createElement('div');
                    card.className = 'aa-editor-asset-card';

                    const pickButton = document.createElement('button');
                    pickButton.type = 'button';
                    pickButton.className = 'aa-editor-asset-pick';
                    pickButton.dataset.aaEditorAssetId = asset.id;
                    pickButton.title = asset.name || asset.id;
                    pickButton.draggable = true;
                    pickButton.innerHTML = `<img src="${asset.src}" loading="lazy" alt="">`;
                    pickButton.addEventListener('dragstart', event => {
                        event.dataTransfer.effectAllowed = 'copy';
                        event.dataTransfer.setData('application/x-aa-editor-asset', String(asset
                            .id || ''));
                    });
                    card.appendChild(pickButton);

                    if (config.isAdmin && asset.type === 'ornament') {
                        const deleteButton = document.createElement('button');
                        deleteButton.type = 'button';
                        deleteButton.className = 'aa-editor-asset-delete';
                        deleteButton.dataset.aaEditorAssetDeleteId = asset.id;
                        deleteButton.title = 'Hapus ornament';
                        deleteButton.setAttribute('aria-label', `Hapus ${asset.name || 'ornament'}`);
                        deleteButton.innerHTML = '<i class="fa fa-trash" aria-hidden="true"></i>';
                        card.appendChild(deleteButton);
                    }
                    els.aaEditorAssetGrid.appendChild(card);
                });
            }
            if (els.aaEditorAssetMoreBtn) {
                els.aaEditorAssetMoreBtn.hidden = assets.length <= state.editorAssetVisible;
            }
        }

        async function loadAndRenderEditorAssets() {
            setEditorAssetPanelLoading('Memuat ornament dan asset bawaan...');
            await ensureEditorAssetLibrary().catch(() => {
                if (els.aaEditorAssetGrid) {
                    els.aaEditorAssetGrid.innerHTML =
                        '<div class="aa-gallery-empty">Asset gagal dimuat.</div>';
                }
            });
            renderEditorAssetCategoryCards();
            if (state.editorAssetCategory) {
                renderEditorAssets();
            } else {
                showEditorAssetCategoryView();
            }
        }

        async function deleteEditorAsset(id) {
            if (!config.isAdmin || !config.editorAssetDeleteUrl || !id) return;
            const asset = state.editorAssets.find(item => String(item.id) === String(id));
            if (!asset || asset.type !== 'ornament') {
                setStatus('Hanya ornament yang bisa dihapus dari panel ini.', 'error');
                return;
            }
            if (!await aaConfirm(`Hapus ornament "${asset.name || 'ini'}" dari library?`, {
                    title: 'Hapus Ornament',
                    okText: 'Hapus',
                    cancelText: 'Batal',
                    danger: true
                })) return;

            setStatus('Menghapus ornament...', 'saving');
            setEditorAssetPanelLoading('Menghapus ornament...');
            const form = new FormData();
            if (config.editorAssetUploadToken) {
                form.append('admin_upload_token', config.editorAssetUploadToken);
            }

            try {
                const response = await fetch(`${config.editorAssetDeleteUrl}/${encodeURIComponent(id)}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: form,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Ornament gagal dihapus.');
                }
                state.editorAssets = state.editorAssets.filter(item => String(item.id) !== String(id));
                renderEditorAssetCategoryCards();
                if (state.editorAssetCategory) {
                    renderEditorAssets();
                } else {
                    showEditorAssetCategoryView();
                }
                setStatus(data.message || 'Ornament berhasil dihapus.');
            } catch (error) {
                setStatus(error.message || 'Ornament gagal dihapus.', 'error');
                renderEditorAssets();
            }
        }

        function setEditorAssetUploadState(message = '', type = 'loading') {
            const box = els.aaEditorAssetUploadState;
            if (!box) return;
            const text = box.querySelector('span');
            if (!message) {
                box.classList.remove('is-visible', 'is-error', 'is-success');
                if (text) text.textContent = '';
                return;
            }
            box.classList.toggle('is-error', type === 'error');
            box.classList.toggle('is-success', type === 'success');
            box.classList.add('is-visible');
            if (text) text.textContent = message;
        }

        async function uploadSingleEditorAsset(file, type, category) {
            const form = new FormData();
            form.append('type', type);
            form.append('category', category);
            form.append('file', file);
            if (config.editorAssetUploadToken) {
                form.append('admin_upload_token', config.editorAssetUploadToken);
            }

            const response = await fetch(config.editorAssetUploadUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: form,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false) {
                throw new Error(data.message || `Upload gagal: ${file.name || 'asset'}`);
            }
            return data.item || null;
        }

        async function uploadEditorAsset(event) {
            event?.preventDefault();
            if (!config.isAdmin || !config.editorAssetUploadUrl || !els.aaEditorAssetUploadFile) return;

            const category = els.aaEditorAssetUploadCategory?.value || '';
            const type = els.aaEditorAssetUploadType?.value || 'ornament';
            const files = Array.from(els.aaEditorAssetUploadFile.files || []);

            if (!category) {
                setStatus('Pilih kategori asset terlebih dahulu.', 'error');
                els.aaEditorAssetUploadCategory?.focus();
                return;
            }

            if (!files.length) {
                setStatus('Pilih file asset terlebih dahulu.', 'error');
                els.aaEditorAssetUploadFile?.focus();
                return;
            }

            if (els.aaEditorAssetUploadBtn) {
                els.aaEditorAssetUploadBtn.disabled = true;
                els.aaEditorAssetUploadBtn.textContent = `Mengupload 0/${files.length}...`;
            }
            setEditorAssetUploadState(`Menyiapkan ${files.length} asset...`);
            setStatus('Mengupload asset...', 'saving');

            let successCount = 0;
            const failed = [];
            try {
                for (const [index, file] of files.entries()) {
                    const current = index + 1;
                    const label = file.name || `asset ${current}`;
                    if (els.aaEditorAssetUploadBtn) {
                        els.aaEditorAssetUploadBtn.textContent = `Mengupload ${current}/${files.length}...`;
                    }
                    setEditorAssetUploadState(`Mengupload ${current}/${files.length}: ${label}`);
                    try {
                        const item = await uploadSingleEditorAsset(file, type, category);
                        if (item) {
                            state.editorAssets.unshift(item);
                            successCount += 1;
                        }
                    } catch (error) {
                        failed.push(`${label}: ${error.message || 'gagal'}`);
                    }
                }

                if (successCount) {
                    state.editorAssetCategory = category;
                    state.editorAssetVisible = Math.max(state.editorAssetVisible, 40);
                    renderEditorAssetCategoryCards();
                    const option = els.aaEditorAssetUploadCategory?.selectedOptions?.[0];
                    showEditorAssetListView(category, option?.textContent || category);
                }

                if (els.aaEditorAssetUploadFile) {
                    els.aaEditorAssetUploadFile.value = '';
                }

                if (failed.length) {
                    const message = `${successCount} asset berhasil, ${failed.length} gagal.`;
                    setEditorAssetUploadState(`${message} ${failed.slice(0, 2).join(' | ')}`, 'error');
                    setStatus(message, successCount ? 'saving' : 'error');
                } else {
                    const message = successCount > 1 ? `${successCount} asset berhasil diupload.` :
                        'Asset berhasil diupload.';
                    setEditorAssetUploadState(message, 'success');
                    setStatus(message);
                    window.setTimeout(() => setEditorAssetUploadState(''), 2200);
                }
            } catch (error) {
                setEditorAssetUploadState(error.message || 'Upload asset gagal.', 'error');
                setStatus(error.message || 'Upload asset gagal.', 'error');
            } finally {
                if (els.aaEditorAssetUploadBtn) {
                    els.aaEditorAssetUploadBtn.disabled = false;
                    els.aaEditorAssetUploadBtn.textContent = 'Upload Asset';
                }
            }
        }

        function isSvgEditorAsset(asset) {
            const src = String(asset?.src || '').split('?')[0].toLowerCase();
            const mime = String(asset?.mimeType || '').toLowerCase();
            return mime.includes('svg') || src.endsWith('.svg');
        }

        function readDraggedEditorAsset(event) {
            const id = event.dataTransfer?.getData('application/x-aa-editor-asset') || '';
            if (!id) return null;
            return state.editorAssets.find(item => String(item.id) === String(id)) || null;
        }

        function hasDraggedEditorAsset(event) {
            const types = Array.from(event.dataTransfer?.types || []);
            return types.includes('application/x-aa-editor-asset');
        }

        function placeEditorAssetObject(asset, object, options = {}) {
            const canvasWidth = state.canvas.getWidth();
            const canvasHeight = state.canvas.getHeight();
            const boundsWidth = Math.max(1, object.width || options.width || 512);
            const boundsHeight = Math.max(1, object.height || options.height || 512);
            let targetWidth = asset.type === 'background' ? canvasWidth : Math.min(420, canvasWidth * .56);
            let targetHeight = asset.type === 'background' ? canvasHeight : Math.min(420, canvasHeight * .28);
            if (asset.type === 'pattern') {
                targetWidth = Math.min(520, canvasWidth * .62);
                targetHeight = Math.min(520, canvasHeight * .32);
            }
            const scale = Math.min(targetWidth / boundsWidth, targetHeight / boundsHeight);
            object.set({
                scaleX: scale,
                scaleY: scale,
                left: options.pointer ? options.pointer.x : canvasWidth / 2,
                top: options.pointer ? options.pointer.y : canvasHeight / 2,
            });
            state.canvas.add(object);
            if (asset.type === 'background' || asset.type === 'pattern') {
                state.canvas.sendToBack(object);
            }
            state.canvas.setActiveObject(object);
            state.canvas.requestRenderAll();
            snapshot();
            setStatus('Asset ditambahkan');
        }

        function prepareEditorAssetObject(asset, object) {
            object.set({
                customType: asset.type === 'shape' ? 'shape' : 'editor-asset',
                assetId: asset.id,
                assetType: asset.type,
                assetCategory: asset.category,
                name: asset.name || asset.id,
                originX: 'center',
                originY: 'center',
                objectCaching: false,
            });
            return object;
        }

        function insertEditorAsset(asset, options = {}) {
            if (!asset || !asset.src || !state.canvas) return;
            setStatus('Menambahkan asset...', 'saving');
            if (!isSvgEditorAsset(asset)) {
                fabric.Image.fromURL(asset.src, image => {
                    if (!image) {
                        setStatus('Asset gagal dimuat', 'error');
                        return;
                    }
                    prepareEditorAssetObject(asset, image);
                    placeEditorAssetObject(asset, image, options);
                }, {
                    crossOrigin: 'anonymous',
                });
                return;
            }

            fabric.loadSVGFromURL(asset.src, (objects, svgOptions) => {
                if (!objects || !objects.length) {
                    setStatus('Asset gagal dimuat', 'error');
                    return;
                }
                const object = prepareEditorAssetObject(asset, fabric.util.groupSVGElements(objects,
                    svgOptions));
                placeEditorAssetObject(asset, object, {
                    ...options,
                    width: svgOptions?.width,
                    height: svgOptions?.height,
                });
            });
        }

        function openExitGuard(url) {
            if (!url || !els.aaExitGuardModal) return;
            state.pendingNavigationUrl = url;
            els.aaExitGuardModal.classList.add('is-open');
        }

        function closeExitGuard() {
            state.pendingNavigationUrl = null;
            els.aaExitGuardModal?.classList.remove('is-open');
        }

        function navigateAfterGuard(url) {
            if (!url) return;
            state.allowNavigation = true;
            window.location.href = url;
        }

        function shouldGuardEditorNavigation(anchor) {
            if (!anchor || state.allowNavigation || !state.hasUnsavedChanges) return false;
            const href = anchor.getAttribute('href') || '';
            if (!href || href[0] === '#' || anchor.target === '_blank' || anchor.hasAttribute('download'))
                return false;
            try {
                const url = new URL(anchor.href, window.location.href);
                return url.href !== window.location.href;
            } catch (error) {
                return false;
            }
        }

        function normalizeProjectIntent(value) {
            value = String(value || '').toLowerCase().trim();
            if (value === 'photobooth' || value === 'digital_photobooth') return 'photobooth';
            if (value === 'business_profile' || value === 'business-profile') return 'business_profile';
            return '';
        }

        function isBusinessProfileProject() {
            return state.projectIntent === 'business_profile';
        }

        function isEditorToolLimitedMode() {
            return state.editMode === 'photobooth' || isBusinessProfileProject();
        }

        function isLeftPanelLimitedInCurrentMode(panelKey) {
            if (!isEditorToolLimitedMode()) return false;
            return ['templates', 'snippets', 'import-reference', 'magic-layer'].includes(String(panelKey || ''));
        }

        function syncEditorToolLimitedMode() {
            document.body.classList.toggle('aa-editor-tool-limited-mode', isEditorToolLimitedMode());
            const activePanelKey = document.querySelector('[data-aa-left-panel].is-active')?.dataset.aaLeftPanel || '';
            if (
                isLeftPanelLimitedInCurrentMode(activePanelKey) &&
                typeof openLeftDrawerPanel === 'function'
            ) {
                openLeftDrawerPanel('canvas');
            }
        }

        function loadInitialDesign() {
            let data = null;
            try {
                data = config.initialEditorJson ? JSON.parse(config.initialEditorJson) : null;
            } catch (error) {
                data = null;
            }

            if (data && data.renderer === 'fabric') {
                state.pages = normalizeProjectPages(data);
                state.activePageIndex = Math.max(0, Math.min(data.activePageIndex || 0, state.pages.length - 1));
                state.projectIntent = normalizeProjectIntent(data.projectIntent || data.project_intent || config.projectType || '');
                document.body.classList.toggle('aa-business-profile-editor', isBusinessProfileProject());
                state.editMode = 'pages';
                syncEditorToolLimitedMode();
                state.opening = normalizeOpeningConfig(data.opening);
                state.guestbook = defaultGuestbookConfig();
                syncGuestbookPanel();
                loadPageData(state.pages[state.activePageIndex]);
                return;
            }

            state.pages = [createBlankPageData('Halaman 1')];
            state.activePageIndex = 0;
            state.editMode = 'pages';
            state.projectIntent = '';
            document.body.classList.remove('aa-business-profile-editor');
            syncEditorToolLimitedMode();
            state.canvas.backgroundColor = '#ffffff';
            addText('heading');
            const sub = new fabric.IText('Edit desain undanganmu di sini', {
                left: state.canvas.getWidth() / 2,
                top: state.canvas.getHeight() / 2 + 110,
                originX: 'center',
                originY: 'center',
                fontFamily: 'Inter',
                fontSize: 42,
                fill: '#64748b',
                textAlign: 'center',
                customType: 'text',
            });
            state.canvas.add(sub);
            state.canvas.renderAll();
            storeCurrentPage();
            renderPageList();
            snapshot();
            fitZoom();
        }


        function aaIsDomElement(target) {
            return target instanceof Element;
        }

        function aaIsSelectionSafeEditorUi(target) {
            if (!aaIsDomElement(target)) return false;

            return Boolean(target.closest([
                '.aa-topbar',
                '.aa-leftbar',
                '.aa-rightbar',
                '.aa-left-drawer',

                '#aaContextToolbar',
                '#aaTextContextToolbar',
                '#aaCountdownContextToolbar',
                '#aaObjectFloatingToolbar',
                '#aaObjectContextMenu',
                '#aaInteractionPopover',

                '#aaContextFlipPopover',
                '#aaContextStrokePopover',
                '#aaContextRadiusPopover',
                '#aaContextImageOutlinePopover',
                '#aaContextImageEffectsPopover',
                '#aaContextImageFramePopover',
                '#aaContextTransparencyPopover',
                '#aaTextEffectsPopover',
                '#aaAnimationPopover',

                '#aaContextColorInput',
                '#aaTextContextColorInput',
                '#aaContextStrokeColorInput',
                '#aaContextImageOutlineColorInput',
                '#aaTextEffectStrokeColor',
                '#aaTextEffectShadowColor',
                '#aaColorDrawerInput',
                '#aaColorDrawerHexInput',

                '#aaCropFloatingToolbar',
                '#aaCropDomOverlay',

                '#aaCountdownDatePicker',
                '.aa-date-picker',
                '.aa-modal'
            ].join(',')));
        }

        function aaIsToolbarPopoverTarget(target) {
            if (!aaIsDomElement(target)) return false;

            return Boolean(target.closest([
                '#aaContextToolbar',
                '#aaTextContextToolbar',
                '#aaCountdownContextToolbar',
                '#aaObjectFloatingToolbar',
                '#aaObjectContextMenu',
                '#aaInteractionPopover',

                '#aaContextFlipPopover',
                '#aaContextStrokePopover',
                '#aaContextRadiusPopover',
                '#aaContextImageOutlinePopover',
                '#aaContextImageEffectsPopover',
                '#aaContextImageFramePopover',
                '#aaContextTransparencyPopover',
                '#aaTextEffectsPopover',
                '#aaAnimationPopover',

                '#aaContextColorInput',
                '#aaTextContextColorInput',
                '#aaContextStrokeColorInput',
                '#aaContextImageOutlineColorInput',
                '#aaTextEffectStrokeColor',
                '#aaTextEffectShadowColor',
                '#aaColorDrawerInput',
                '#aaColorDrawerHexInput',

                '#aaCropFloatingToolbar',
                '#aaCountdownDatePicker',
                '.aa-date-picker'
            ].join(',')));
        }

        function bindEvents() {
            bindSmartGuides();
            bindAnimationControls();
            bindAaSelectAllShortcut();
            bindCtrlWheelZoom();
            populateTextContextFontOptions();
            document.getElementById('aaEditPhotoboothBtn')?.addEventListener('click', () => {
                if (isBusinessProfileProject()) return;
                switchEditorMode('photobooth');
            });
            document.getElementById('aaEditPhotoboothLockedBtn')?.addEventListener('click', event => {
                event.preventDefault();
                if (typeof openEditorAccessModal === 'function') {
                    openEditorAccessModal({
                        title: 'Photobooth belum aktif',
                        description: 'Project ini khusus Photobooth. Aktifkan paket Plus dan minta admin mengaktifkan Guest Memories agar tab Photobooth bisa digunakan.',
                        actionLabel: 'Lihat Paket Plus',
                        actionUrl: config.plansUrl,
                    });
                    return;
                }
                setStatus('Photobooth membutuhkan paket Plus dan aktivasi admin.');
            });
            els.aaEditOpeningBtn?.addEventListener('click', () => {
                switchEditorMode('opening');
            });
            els.aaEditPagesBtn?.addEventListener('click', () => switchEditorMode('pages'));
            document.getElementById('aaEditBusinessProfilePagesBtn')?.addEventListener('click', () => switchEditorMode('pages'));
            bindImportReferenceControls?.();
            bindReferenceMapperControls?.();
            bindOcrTextControls?.();
            bindSnippetDrawer();
            $('aaAddPageBtn')?.addEventListener('click', addPage);
            $('aaDuplicatePageBtn')?.addEventListener('click', duplicatePage);
            $('aaDeletePageBtn')?.addEventListener('click', deletePage);
            document.addEventListener('click', event => {
                if (event.target.closest('.page-menu-wrap')) return;
                if (aaIsToolbarPopoverTarget(event.target)) {
                    aaRememberActiveObjectForEditorUi();
                    return;
                }
                document.querySelectorAll('.page-menu-wrap.is-open').forEach(menu => {
                    menu.classList.remove('is-open');
                });
                if (!event.target.closest('#aaObjectContextMenu')) {
                    closeObjectContextMenu();
                }
                if (!event.target.closest('#aaContextFlipPopover') && !event.target.closest(
                        '#aaContextFlipBtn')) {
                    closeContextFlipPopover();
                }
                if (!event.target.closest('#aaContextStrokePopover') && !event.target.closest(
                        '#aaContextStrokeBtn')) {
                    closeContextStrokePopover();
                }
                if (!event.target.closest('#aaContextRadiusPopover') && !event.target.closest(
                        '#aaContextRadiusBtn')) {
                    closeContextRadiusPopover();
                }
                if (!event.target.closest('#aaContextImageOutlinePopover') && !event.target.closest(
                        '#aaContextImageOutlineBtn')) {
                    closeImageOutlinePopover();
                }
                if (!event.target.closest('#aaContextImageEffectsPopover') && !event.target.closest(
                        '#aaContextImageEffectsBtn')) {
                    closeImageEffectsPopover();
                }
                if (!event.target.closest('#aaContextImageFramePopover') && !event.target.closest(
                        '#aaContextImageFrameBtn')) {
                    closeImageFramePopover();
                }
                if (!event.target.closest('#aaContextTransparencyPopover') && !event.target.closest(
                        '#aaContextOpacityBtn') && !event.target.closest('#aaTextContextOpacityBtn')) {
                    closeContextTransparencyPopover();
                }
                if (!event.target.closest('#aaTextEffectsPopover') && !event.target.closest(
                        '#aaTextContextEffectsBtn')) {
                    closeTextEffectsPopover();
                }
                if (!event.target.closest('#aaAnimationPopover') && !event.target.closest(
                        '#aaContextAnimateBtn') &&
                    !event.target.closest('#aaTextContextAnimateBtn')) {
                    closeAnimationPopover();
                }
                if (!event.target.closest('.aa-date-field') && !event.target.closest(
                        '#aaCountdownDatePicker') &&
                    !event.target.closest('#aaCountdownContextToolbar')) {
                    closeCountdownDatePicker();
                }
            });
            els.aaStageWrap?.addEventListener('scroll', () => {
                closeToolbarPopovers();
                syncObjectFloatingToolbar();
                syncInteractionPopover();
                syncCropUi();
            }, {
                passive: true,
            });
            els.aaStageWrap?.addEventListener('dragover', event => {
                const hasEditorAsset = hasDraggedEditorAsset(event);
                if ((!hasDraggedMediaAsset(event) && !hasEditorAsset) || state.isCropping || !
                    getCanvasDropPointer(event)) {
                    hideMediaDropPreview();
                    return;
                }
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy';
                if (hasEditorAsset) {
                    hideMediaDropPreview();
                    return;
                }
                syncMediaDropPreview(event);
            });
            els.aaStageWrap?.addEventListener('dragleave', event => {
                if (els.aaStageWrap.contains(event.relatedTarget)) return;
                hideMediaDropPreview();
            });
            els.aaStageWrap?.addEventListener('drop', handleStageDrop);
            els.aaObjectContextMenu?.addEventListener('click', event => {
                const button = event.target.closest('[data-aa-context-action]');
                if (!button || button.disabled) return;
                event.preventDefault();
                event.stopPropagation();
                runObjectContextAction(button.dataset.aaContextAction);
            });
            els.aaObjectFloatingToolbar?.addEventListener('pointerdown', event => event.stopPropagation());
            els.aaObjectTransformOverlay?.addEventListener('pointerdown', startObjectTransformOverlay);
            els.aaInteractionPopover?.addEventListener('pointerdown', event => event.stopPropagation());
            els.aaInteractionPopover?.addEventListener('click', event => event.stopPropagation());
            els.aaTextContextToolbar?.addEventListener('pointerdown', event => event.stopPropagation());
            els.aaTextContextToolbar?.addEventListener('click', event => event.stopPropagation());
            els.aaCountdownContextToolbar?.addEventListener('pointerdown', event => event.stopPropagation());
            els.aaCountdownContextToolbar?.addEventListener('click', event => event.stopPropagation());
            els.aaFloatingLockBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                const active = getObjectToolbarTarget();
                if (!active) return;
                setObjectLocked(active, active.locked !== true);
                state.canvas.setActiveObject(active);
                state.canvas.requestRenderAll();
                syncInspector();
                storeCurrentPage();
                snapshot();
            });
            els.aaFloatingMoreBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                const active = getObjectToolbarTarget();
                if (!active) return;
                state.canvas.setActiveObject(active);
                state.canvas.requestRenderAll();
                const rect = els.aaFloatingMoreBtn.getBoundingClientRect();
                openObjectContextMenu({
                    clientX: rect.left,
                    clientY: rect.bottom + 8,
                }, active, null);
            });

            function aaProtectToolbarDropdownElement(element) {
                if (!element || element.__aaToolbarDropdownProtected) return;

                element.__aaToolbarDropdownProtected = true;

                ['pointerdown', 'pointermove', 'mousedown', 'mouseup', 'pointerup', 'click', 'dblclick'].forEach(function(
                    eventName) {
                    element.addEventListener(eventName, function(event) {
                        event.stopPropagation();
                    });
                });
            }

            [
                els.aaObjectFloatingToolbar,
                els.aaContextToolbar,
                els.aaTextContextToolbar,
                els.aaCountdownContextToolbar,
                els.aaInteractionPopover,
                els.aaObjectContextMenu,

                els.aaContextFlipPopover,
                els.aaContextStrokePopover,
                els.aaContextRadiusPopover,
                els.aaContextImageOutlinePopover,
                els.aaContextImageEffectsPopover,
                els.aaContextImageFramePopover,
                els.aaContextTransparencyPopover,
                els.aaTextEffectsPopover,
                els.aaAnimationPopover,

                els.aaCropFloatingToolbar,
                els.aaCountdownDatePicker
            ].forEach(aaProtectToolbarDropdownElement);
            els.aaCropDomOverlay?.addEventListener('pointerdown', event => {
                if (!event.target.closest('.aa-crop-dom-box')) return;
                startCropDomDrag(event);
            });
            els.aaCropFloatingToolbar?.addEventListener('pointerdown', event => event.stopPropagation());
            els.aaCropFloatingToolbar?.addEventListener('click', event => event.stopPropagation());
            els.aaCropFloatApplyBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                applyCropBoxAndFinish();
            });
            els.aaCropFloatCancelBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                cancelCropBoxAndFinish();
            });
            els.aaCropFloatResetBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                resetActiveCropBox();
            });
            els.aaContextColorBtn?.addEventListener('click', () => {
                const active = state.canvas.getActiveObject();
                if (!active) return;
                if (typeof isSpecialContextToolbarObject === 'function' && isSpecialContextToolbarObject(active)) return;
                if (active.type === 'image') return;
                if (els.aaContextColorInput) {
                    els.aaContextColorInput.value = getActiveObjectColor(active);
                    openColorDrawer(els.aaContextColorInput, 'Object Color');
                }
            });
            els.aaContextColorInput?.addEventListener('input', event => {
                const active = state.canvas.getActiveObject();
                if (!active) return;
                if (typeof isSpecialContextToolbarObject === 'function' && isSpecialContextToolbarObject(active)) return;
                if (active.type === 'image') return;
                if (active.type === 'line') {
                    applyActiveStyle({
                        stroke: event.target.value
                    });
                } else {
                    applyActiveStyle({
                        fill: event.target.value
                    });
                }
                if (els.aaColorInput) {
                    els.aaColorInput.value = event.target.value;
                }
                syncContextToolbar();
                syncTextContextToolbar();
            });
            els.aaContextStrokeBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                toggleContextStrokePopover();
            });
            els.aaContextStrokeInput?.addEventListener('input', event => applyImageStrokeSettings({
                width: event.target.value,
            }));
            els.aaContextStrokeColorInput?.addEventListener('input', event => applyImageStrokeSettings({
                color: event.target.value,
            }));
            document.querySelectorAll('[data-aa-stroke-style]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    applyImageStrokeSettings({
                        style: button.dataset.aaStrokeStyle,
                    });
                });
            });
            els.aaContextRadiusBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                toggleContextRadiusPopover();
            });
            els.aaContextRadiusInput?.addEventListener('input', event => applyContextImageRadius(event.target
                .value));
            els.aaContextCropBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                if (guardLockedImageAction('crop gambar')) return;
                startCropMode();
            });
            els.aaContextRemoveBgBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                removeBackgroundFromActiveImage();
            });
            els.aaContextMagicLayerBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                setStatus('Magic Layer sekarang memakai upload file dari panel kiri.', 'error');
                setMagicLayerAiStatus('Klik Magic di panel kiri lalu upload gambar maksimal 2MB.', 'error');
            });
            els.aaMagicLayerAiBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                if (guardAiPremiumFeature(event, 'Magic Layer')) return;
                openLeftDrawerPanel('magic-layer');
                setMagicLayerAiStatus(state.magicLayerSelectedFile
                    ? 'Preview siap. Klik Proses Magic Layer AI untuk membuat halaman baru.'
                    : 'Pilih gambar JPG, PNG, atau WEBP maksimal 2MB.');
            });
            els.aaMagicLayerFileInput?.addEventListener('change', async event => {
                try {
                    await setMagicLayerUploadPreview(event.target.files?.[0] || null);
                } catch (error) {
                    hideEditorToast();
                    clearMagicLayerUploadPreview(error?.message || 'File Magic Layer tidak valid.');
                    setStatus(error?.message || 'File Magic Layer tidak valid.', 'error');
                    setMagicLayerAiStatus(error?.message || 'File Magic Layer tidak valid.', 'error');
                }
            });
            els.aaMagicLayerClearBtn?.addEventListener('click', event => {
                event.preventDefault();
                clearMagicLayerUploadPreview();
                els.aaMagicLayerFileInput?.click();
            });
            els.aaMagicLayerProcessBtn?.addEventListener('click', async event => {
                event.preventDefault();
                event.stopPropagation();
                try {
                    if (guardAiPremiumFeature(event, 'Magic Layer')) return;
                    if (!config.magicLayerEnabled) {
                        throw new Error('Magic Layer sedang nonaktif. Gunakan Remove BG untuk Poof.');
                    }
                    if (!config.mediaMagicLayerUrl) {
                        throw new Error('Service Magic Layer belum dikonfigurasi.');
                    }
                    setButtonLoading(els.aaMagicLayerProcessBtn, true, 'Memproses...');
                    await magicLayerFromUploadedFile(state.magicLayerSelectedFile);
                    clearMagicLayerUploadPreview('Magic Layer selesai. Pilih gambar lain untuk proses berikutnya.');
                } catch (error) {
                    hideEditorToast();
                    setMediaUploadState(error?.message || 'Magic Layer gagal.', 'error');
                    setStatus(error?.message || 'Magic Layer gagal.', 'error');
                    setMagicLayerAiStatus(error?.message || 'Magic Layer gagal.', 'error');
                } finally {
                    setButtonLoading(els.aaMagicLayerProcessBtn, false);
                }
            });
            els.aaContextImageOutlineBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                const active = state.canvas?.getActiveObject();
                if (!isImageOutlineTarget(active)) {
                    setStatus('Outline hanya aktif untuk gambar.', 'error');
                    return;
                }
                syncImageOutlineControl(active);
                window.aaSyncOutlineColorPicker?.(els.aaContextImageOutlineColorInput?.value || active.aaImageOutlineDraftColor || active.aaImageOutlineColor || '#ffffff');
                closeToolbarPopovers('image-outline-drawer');
                openLeftDrawerPanel('image-outline');
            });
            const readImageOutlineControlValues = () => {
                const active = state.canvas?.getActiveObject();
                if (!isImageOutlineTarget(active)) return null;
                const color = els.aaContextImageOutlineColorInput?.value || active.aaImageOutlineColor || '#ffffff';
                const width = Math.max(0, Math.min(60, Math.round(Number(els.aaContextImageOutlineWidthInput?.value) || 0)));
                if (els.aaContextImageOutlineWidthValue) {
                    els.aaContextImageOutlineWidthValue.value = width;
                    els.aaContextImageOutlineWidthValue.textContent = String(width);
                }
                return {
                    color,
                    width,
                };
            };
            const clampOutlineUnit = value => Math.max(0, Math.min(1, Number(value) || 0));
            const outlineRgbToHex = (red, green, blue) => '#' + [red, green, blue].map(value => {
                const safe = Math.max(0, Math.min(255, Math.round(Number(value) || 0)));
                return safe.toString(16).padStart(2, '0');
            }).join('').toUpperCase();
            const outlineHexToRgb = value => {
                const hex = normalizeColor(value || '#ffffff').replace('#', '');
                return {
                    red: parseInt(hex.slice(0, 2), 16),
                    green: parseInt(hex.slice(2, 4), 16),
                    blue: parseInt(hex.slice(4, 6), 16),
                };
            };
            const outlineHsvToHex = (hue, saturation, value) => {
                const h = ((Number(hue) || 0) % 360 + 360) % 360;
                const s = clampOutlineUnit(saturation);
                const v = clampOutlineUnit(value);
                const chroma = v * s;
                const x = chroma * (1 - Math.abs((h / 60) % 2 - 1));
                const m = v - chroma;
                let red = 0;
                let green = 0;
                let blue = 0;
                if (h < 60) {
                    red = chroma;
                    green = x;
                } else if (h < 120) {
                    red = x;
                    green = chroma;
                } else if (h < 180) {
                    green = chroma;
                    blue = x;
                } else if (h < 240) {
                    green = x;
                    blue = chroma;
                } else if (h < 300) {
                    red = x;
                    blue = chroma;
                } else {
                    red = chroma;
                    blue = x;
                }
                return outlineRgbToHex((red + m) * 255, (green + m) * 255, (blue + m) * 255);
            };
            const outlineHexToHsv = value => {
                const rgb = outlineHexToRgb(value);
                const red = rgb.red / 255;
                const green = rgb.green / 255;
                const blue = rgb.blue / 255;
                const max = Math.max(red, green, blue);
                const min = Math.min(red, green, blue);
                const delta = max - min;
                let hue = 0;
                if (delta) {
                    if (max === red) hue = 60 * (((green - blue) / delta) % 6);
                    else if (max === green) hue = 60 * ((blue - red) / delta + 2);
                    else hue = 60 * ((red - green) / delta + 4);
                }
                if (hue < 0) hue += 360;
                return {
                    hue,
                    saturation: max === 0 ? 0 : delta / max,
                    value: max,
                };
            };
            async function aaPickDrawerColor(nativeInput, applyColor) {
                const fallbackNativePicker = () => {
                    if (nativeInput && typeof nativeInput.click === 'function') {
                        nativeInput.click();
                        return true;
                    }
                    return false;
                };

                if (window.EyeDropper && typeof window.EyeDropper === 'function') {
                    try {
                        const result = await new window.EyeDropper().open();
                        const color = normalizeColor(result?.sRGBHex || '');
                        if (color && typeof applyColor === 'function') {
                            applyColor(color);
                            return true;
                        }
                    } catch (error) {
                        if (error?.name === 'AbortError') {
                            return false;
                        }
                    }
                }

                return fallbackNativePicker();
            }
            const updateOutlineColorPickerUi = color => {
                const safeColor = normalizeColor(color || '#ffffff').toUpperCase();
                const hsv = outlineHexToHsv(safeColor);
                state.outlineColorPicker = hsv;
                const hueColor = outlineHsvToHex(hsv.hue, 1, 1);
                if (els.aaContextImageOutlineColorInput) els.aaContextImageOutlineColorInput.value = safeColor;
                if (els.aaOutlineColorHexInput) els.aaOutlineColorHexInput.value = safeColor;
                if (els.aaOutlineColorPreviewText) els.aaOutlineColorPreviewText.textContent = safeColor;
                if (els.aaOutlineColorPreview) els.aaOutlineColorPreview.style.setProperty('--aa-outline-current', safeColor);
                if (els.aaOutlineHueInput) els.aaOutlineHueInput.value = String(Math.round(hsv.hue));
                if (els.aaOutlineColorField) {
                    els.aaOutlineColorField.style.setProperty('--aa-outline-hue', hueColor);
                    els.aaOutlineColorField.style.setProperty('--aa-outline-handle-x', `${hsv.saturation * 100}%`);
                    els.aaOutlineColorField.style.setProperty('--aa-outline-handle-y', `${(1 - hsv.value) * 100}%`);
                }
            };
            const setOutlineDraftColor = (color, options = {}) => {
                const safeColor = normalizeColor(color || '#ffffff').toUpperCase();
                updateOutlineColorPickerUi(safeColor);
                if (options.store !== false) {
                    storeImageOutlineDraftValues();
                }
            };
            window.aaSetOutlineDraftColor = color => setOutlineDraftColor(color);
            window.aaSyncOutlineColorPicker = color => updateOutlineColorPickerUi(color || els.aaContextImageOutlineColorInput?.value || '#ffffff');
            const storeImageOutlineDraftValues = () => {
                const active = state.canvas?.getActiveObject();
                if (!isImageOutlineTarget(active)) return;
                const values = readImageOutlineControlValues();
                if (!values) return;
                active.set({
                    aaImageOutlineDraftColor: normalizeColor(values.color || '#ffffff'),
                    aaImageOutlineDraftWidth: values.width,
                });
            };
            const updateImageOutlineWidthLabel = () => {
                const width = Math.max(0, Math.min(60, Math.round(Number(els.aaContextImageOutlineWidthInput?.value) || 0)));
                if (els.aaContextImageOutlineWidthValue) {
                    els.aaContextImageOutlineWidthValue.value = width;
                    els.aaContextImageOutlineWidthValue.textContent = String(width);
                }
            };
            const commitImageOutlineControls = () => {
                const values = readImageOutlineControlValues();
                if (!values) return;
                window.clearTimeout(state.imageOutlineApplyTimer);
                state.imageOutlineApplyTimer = window.setTimeout(() => {
                    state.imageOutlineApplyTimer = null;
                    applyImageAlphaOutline({
                        color: values.color,
                        width: values.width,
                        keepPopover: true,
                    });
                }, 80);
            };
            const scheduleImageOutlineControls = (delay = 220) => {
                const values = readImageOutlineControlValues();
                if (!values) return;
                window.clearTimeout(state.imageOutlineApplyTimer);
                state.imageOutlineApplyTimer = window.setTimeout(() => {
                    state.imageOutlineApplyTimer = null;
                    applyImageAlphaOutline({
                        color: values.color,
                        width: values.width,
                        keepPopover: true,
                    });
                }, delay);
            };
            const outlinePanel = document.querySelector('[data-aa-left-panel="image-outline"]');
            ['pointerdown', 'mousedown', 'touchstart', 'click'].forEach(eventName => {
                outlinePanel?.addEventListener(eventName, event => {
                    event.stopPropagation();
                });
                els.aaContextImageOutlineWidthInput?.addEventListener(eventName, event => {
                    event.stopPropagation();
                });
            });
            els.aaContextImageOutlineColorInput?.addEventListener('input', event => {
                event.stopPropagation();
                setOutlineDraftColor(event.target.value || '#ffffff');
                storeImageOutlineDraftValues();
                scheduleImageOutlineControls();
            });
            els.aaContextImageOutlineColorInput?.addEventListener('change', event => {
                event.stopPropagation();
                setOutlineDraftColor(event.target.value || '#ffffff');
                storeImageOutlineDraftValues();
                scheduleImageOutlineControls(80);
            });
            els.aaOutlineEyedropperBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                aaPickDrawerColor(els.aaContextImageOutlineColorInput, color => {
                    setOutlineDraftColor(color);
                    scheduleImageOutlineControls(80);
                });
            });
            const applyOutlineColorFieldPointer = event => {
                if (!els.aaOutlineColorField) return;
                event.preventDefault();
                event.stopPropagation();
                const rect = els.aaOutlineColorField.getBoundingClientRect();
                const saturation = clampOutlineUnit((event.clientX - rect.left) / Math.max(1, rect.width));
                const value = clampOutlineUnit(1 - ((event.clientY - rect.top) / Math.max(1, rect.height)));
                const picker = state.outlineColorPicker || outlineHexToHsv(els.aaContextImageOutlineColorInput?.value || '#ffffff');
                const color = outlineHsvToHex(picker.hue, saturation, value);
                setOutlineDraftColor(color);
            };
            const endOutlineColorFieldDrag = event => {
                state.outlineColorFieldPointerId = null;
                if (event?.pointerId !== undefined) {
                    try {
                        els.aaOutlineColorField?.releasePointerCapture?.(event.pointerId);
                    } catch (error) {}
                }
            };
            els.aaOutlineColorField?.addEventListener('pointerdown', event => {
                state.outlineColorFieldPointerId = event.pointerId;
                applyOutlineColorFieldPointer(event);
                els.aaOutlineColorField.setPointerCapture?.(event.pointerId);
            });
            els.aaOutlineColorField?.addEventListener('pointermove', event => {
                if (state.outlineColorFieldPointerId !== event.pointerId || event.buttons !== 1) return;
                applyOutlineColorFieldPointer(event);
            });
            els.aaOutlineColorField?.addEventListener('pointerup', endOutlineColorFieldDrag);
            els.aaOutlineColorField?.addEventListener('pointercancel', endOutlineColorFieldDrag);
            els.aaOutlineHueInput?.addEventListener('input', event => {
                event.stopPropagation();
                const picker = state.outlineColorPicker || outlineHexToHsv(els.aaContextImageOutlineColorInput?.value || '#ffffff');
                const color = outlineHsvToHex(event.target.value, picker.saturation, picker.value);
                setOutlineDraftColor(color);
            });
            els.aaOutlineColorHexInput?.addEventListener('input', event => {
                event.stopPropagation();
                const value = String(event.target.value || '').trim();
                if (/^#[0-9a-f]{6}$/i.test(value)) {
                    setOutlineDraftColor(value);
                }
            });
            els.aaOutlineColorHexInput?.addEventListener('change', event => {
                event.stopPropagation();
                setOutlineDraftColor(event.target.value || '#ffffff');
            });
            document.querySelectorAll('[data-aa-outline-color]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    setOutlineDraftColor(button.dataset.aaOutlineColor || '#ffffff');
                });
            });
            els.aaContextImageOutlineWidthInput?.addEventListener('input', event => {
                event.stopPropagation();
                updateImageOutlineWidthLabel();
                storeImageOutlineDraftValues();
            });
            els.aaContextImageOutlineWidthInput?.addEventListener('change', event => {
                event.stopPropagation();
                storeImageOutlineDraftValues();
                commitImageOutlineControls();
            });
            ['pointerup', 'mouseup', 'touchend', 'keyup'].forEach(eventName => {
                els.aaContextImageOutlineWidthInput?.addEventListener(eventName, event => {
                    event.stopPropagation();
                    storeImageOutlineDraftValues();
                    commitImageOutlineControls();
                });
            });
            els.aaContextImageOutlineApplyBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                commitImageOutlineControls();
            });
            els.aaContextImageOutlineResetBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                window.clearTimeout(state.imageOutlineApplyTimer);
                if (els.aaContextImageOutlineWidthInput) els.aaContextImageOutlineWidthInput.value = '0';
                if (els.aaContextImageOutlineWidthValue) {
                    els.aaContextImageOutlineWidthValue.value = 0;
                    els.aaContextImageOutlineWidthValue.textContent = '0';
                }
                applyImageAlphaOutline({
                    color: els.aaContextImageOutlineColorInput?.value || '#ffffff',
                    width: 0,
                    keepPopover: true,
                });
            });
            els.aaContextImageEffectsBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                toggleImageEffectsPopover();
            });
            document.querySelectorAll('[data-aa-image-effect]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    applyImageEffectPreset(button.dataset.aaImageEffect);
                });
            });
            document.querySelectorAll('[data-aa-image-overlay]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    applyImageOverlayGradient(button.dataset.aaImageOverlay);
                });
            });
            document.querySelectorAll('[data-aa-image-reset-effects]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    resetImageEffectsForActive();
                });
            });
            els.aaContextImageFrameBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                toggleImageFramePopover();
            });
            document.querySelectorAll('[data-aa-image-frame]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    applyImageFrameShape(button.dataset.aaImageFrame);
                });
            });
            els.aaContextFlipBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                toggleContextFlipPopover();
            });
            document.querySelectorAll('[data-aa-flip-axis]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    flipActiveObject(button.dataset.aaFlipAxis);
                });
            });
            els.aaContextOpacityBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                const active = state.canvas.getActiveObject();
                if (typeof isSpecialContextToolbarObject === 'function' && isSpecialContextToolbarObject(active)) return;
                toggleContextTransparencyPopover();
            });
            els.aaContextTransparencyInput?.addEventListener('input', event => applyContextTransparency(event.target
                .value));
            els.aaContextAnimateBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                if (guardPremiumFeature(event)) return;
                toggleAnimationPopover(event.currentTarget);
            });
            els.aaTextContextFont?.addEventListener('change', event => applyTextContextFontFamily(event.target
                .value));
            els.aaTextContextSizeDown?.addEventListener('click', () => stepTextContextFontSize(-1));
            els.aaTextContextSizeUp?.addEventListener('click', () => stepTextContextFontSize(1));
            els.aaTextContextColorBtn?.addEventListener('click', () => {
                const target = getContextTextTarget();
                if (!target) return;
                if (els.aaTextContextColorInput) {
                    els.aaTextContextColorInput.value = normalizeColor(target.fill || '#111827');
                    openColorDrawer(els.aaTextContextColorInput, 'Text Color');
                }
            });
            els.aaTextContextColorInput?.addEventListener('input', event => {
                if (!getContextTextTarget()) return;
                applyActiveStyle({
                    fill: event.target.value
                });
                if (els.aaColorInput) {
                    els.aaColorInput.value = event.target.value;
                }
                syncContextToolbar();
                syncTextContextToolbar();
            });
            els.aaTextContextBoldBtn?.addEventListener('click', toggleBoldStyle);
            els.aaTextContextItalicBtn?.addEventListener('click', () => toggleTextStyle('fontStyle', 'italic',
                'normal'));
            els.aaTextContextUnderlineBtn?.addEventListener('click', () => toggleTextStyle('underline', true,
                false));
            els.aaTextContextStrikeBtn?.addEventListener('click', () => toggleTextStyle('linethrough', true,
                false));
            els.aaTextContextCaseBtn?.addEventListener('click', toggleTextContextCase);
            els.aaTextContextAlignBtn?.addEventListener('click', cycleTextContextAlign);
            els.aaTextContextListBtn?.addEventListener('click', toggleTextContextList);
            els.aaTextContextOpacityBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                toggleContextTransparencyPopover();
            });
            els.aaTextContextEffectsBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                if (guardPremiumFeature(event)) return;
                toggleTextEffectsPopover();
            });
            document.querySelectorAll('[data-aa-text-effect-preset]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    applyTextEffectPreset(button.dataset.aaTextEffectPreset);
                });
            });
            const commitTextEffectChange = () => {
                if (getTextEffectTarget()) snapshot();
            };
            els.aaTextEffectStrokeColor?.addEventListener('input', event => applyTextEffectSettings({
                strokeColor: event.target.value,
            }, {
                snapshot: false,
            }));
            els.aaTextEffectStrokeColor?.addEventListener('change', commitTextEffectChange);
            els.aaTextEffectStrokeWidth?.addEventListener('input', event => applyTextEffectSettings({
                strokeWidth: event.target.value,
            }, {
                snapshot: false,
            }));
            els.aaTextEffectStrokeWidth?.addEventListener('change', commitTextEffectChange);
            els.aaTextEffectShadowColor?.addEventListener('input', event => applyTextEffectSettings({
                shadowColor: event.target.value,
            }, {
                snapshot: false,
            }));
            els.aaTextEffectShadowColor?.addEventListener('change', commitTextEffectChange);
            els.aaTextEffectShadowBlur?.addEventListener('input', event => applyTextEffectSettings({
                shadowBlur: event.target.value,
            }, {
                snapshot: false,
            }));
            els.aaTextEffectShadowBlur?.addEventListener('change', commitTextEffectChange);
            els.aaTextEffectShadowOffsetX?.addEventListener('input', event => applyTextEffectSettings({
                shadowOffsetX: event.target.value,
            }, {
                snapshot: false,
            }));
            els.aaTextEffectShadowOffsetX?.addEventListener('change', commitTextEffectChange);
            els.aaTextEffectShadowOffsetY?.addEventListener('input', event => applyTextEffectSettings({
                shadowOffsetY: event.target.value,
            }, {
                snapshot: false,
            }));
            els.aaTextEffectShadowOffsetY?.addEventListener('change', commitTextEffectChange);
            els.aaTextEffectCharSpacing?.addEventListener('input', event => applyTextEffectSettings({
                charSpacing: event.target.value,
            }, {
                snapshot: false,
            }));
            els.aaTextEffectCharSpacing?.addEventListener('change', commitTextEffectChange);
            els.aaTextEffectLineHeight?.addEventListener('input', event => applyTextEffectSettings({
                lineHeight: (Number(event.target.value) || 114) / 100,
            }, {
                snapshot: false,
            }));
            els.aaTextEffectLineHeight?.addEventListener('change', commitTextEffectChange);
            els.aaTextEffectGlowPreset?.addEventListener('click', applyTextGlowPreset);
            els.aaTextEffectReset?.addEventListener('click', resetTextEffects);
            els.aaTextContextAnimateBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                if (guardPremiumFeature(event)) return;
                toggleAnimationPopover(event.currentTarget);
            });
            const activateLeftPanel = tab => {
                const key = tab?.dataset?.aaLeftTab;
                if (!key) return;
                if (isLeftPanelLimitedInCurrentMode(key)) {
                    openLeftDrawerPanel('canvas');
                    return;
                }
                if (key === 'upload' && !canUseMediaLibrary()) return;
                openLeftDrawerPanel(key);
                if (key === 'templates') {
                    showTemplateDrawerLoading();
                }
                if (key === 'ornament') {
                    loadAndRenderEditorAssets();
                }
                if (key === 'upload') {
                    loadMedia();
                }
            };
            const closeLeftDrawer = () => {
                closeLeftDrawerPanel();
            };
            els.aaLeftDrawerCloseBtn?.addEventListener('click', event => {
                event.preventDefault();
                closeLeftDrawer();
            });
            document.querySelectorAll('[data-aa-left-close]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    closeLeftDrawer();
                });
            });
            document.querySelectorAll('[data-aa-left-tab]').forEach(tab => {
                tab.addEventListener('click', event => {
                    if (tab.dataset.aaLeftTab === 'upload' && guardMediaLibraryFeature(event)) return;
                    activateLeftPanel(tab);
                });
            });
            bindFontDrawerTrigger(els.aaTextContextFont, 'text');
            bindFontDrawerTrigger(els.aaCountdownContextFontInput, 'countdown');
            bindFontDrawerTrigger(els.aaGuestFieldPopoverFontInput, 'guest-field');
            bindFontDrawerTrigger(els.aaOpeningButtonFontInput, 'opening-button');
            bindFontDrawerTrigger(els.aaFontInput, 'panel-text');
            bindColorInputsToDrawer();
            els.aaFontDrawerCloseBtn?.addEventListener('click', event => {
                event.preventDefault();
                closeLeftDrawerPanel();
            });
            els.aaColorDrawerCloseBtn?.addEventListener('click', event => {
                event.preventDefault();
                closeLeftDrawerPanel();
            });
            const applyColorPickerFieldPointer = event => {
                if (!els.aaColorPickerField) return;
                event.preventDefault();
                event.stopPropagation();
                const rect = els.aaColorPickerField.getBoundingClientRect();
                const saturation = clampColorDrawerUnit((event.clientX - rect.left) / Math.max(1, rect.width));
                const value = clampColorDrawerUnit(1 - ((event.clientY - rect.top) / Math.max(1, rect.height)));
                const picker = state.colorDrawerPicker || colorDrawerHexToHsv(els.aaColorDrawerInput?.value || '#111827');
                applyColorDrawerValue(colorDrawerHsvToHex(picker.hue, saturation, value));
            };
            const endColorPickerFieldDrag = event => {
                state.colorPickerDragTarget = '';
                state.colorPickerPointerId = null;
                if (event?.pointerId !== undefined) {
                    try {
                        event.currentTarget?.releasePointerCapture?.(event.pointerId);
                    } catch (error) {}
                }
                if (state.colorDrawerTargetInput) {
                    applyColorDrawerValue(els.aaColorDrawerInput?.value || '#111827', true);
                }
            };
            els.aaColorPickerField?.addEventListener('pointerdown', event => {
                state.colorPickerDragTarget = 'field';
                state.colorPickerPointerId = event.pointerId;
                applyColorPickerFieldPointer(event);
                els.aaColorPickerField.setPointerCapture?.(event.pointerId);
            });
            els.aaColorPickerField?.addEventListener('pointermove', event => {
                if (state.colorPickerDragTarget !== 'field' || state.colorPickerPointerId !== event.pointerId ||
                    event.buttons !== 1) return;
                applyColorPickerFieldPointer(event);
            });
            els.aaColorPickerField?.addEventListener('pointerup', endColorPickerFieldDrag);
            els.aaColorPickerField?.addEventListener('pointercancel', endColorPickerFieldDrag);
            const applyColorPickerHuePointer = event => {
                if (!els.aaColorPickerHueBar) return;
                event.preventDefault();
                event.stopPropagation();
                const rect = els.aaColorPickerHueBar.getBoundingClientRect();
                const hue = clampColorDrawerUnit((event.clientY - rect.top) / Math.max(1, rect.height)) * 360;
                const picker = state.colorDrawerPicker || colorDrawerHexToHsv(els.aaColorDrawerInput?.value || '#111827');
                applyColorDrawerValue(colorDrawerHsvToHex(hue, picker.saturation, picker.value));
            };
            const endColorPickerHueDrag = event => {
                state.colorPickerDragTarget = '';
                state.colorPickerPointerId = null;
                if (event?.pointerId !== undefined) {
                    try {
                        event.currentTarget?.releasePointerCapture?.(event.pointerId);
                    } catch (error) {}
                }
                if (state.colorDrawerTargetInput) {
                    applyColorDrawerValue(els.aaColorDrawerInput?.value || '#111827', true);
                }
            };
            els.aaColorPickerHueBar?.addEventListener('pointerdown', event => {
                state.colorPickerDragTarget = 'hue';
                state.colorPickerPointerId = event.pointerId;
                applyColorPickerHuePointer(event);
                els.aaColorPickerHueBar.setPointerCapture?.(event.pointerId);
            });
            els.aaColorPickerHueBar?.addEventListener('pointermove', event => {
                if (state.colorPickerDragTarget !== 'hue' || state.colorPickerPointerId !== event.pointerId ||
                    event.buttons !== 1) return;
                applyColorPickerHuePointer(event);
            });
            els.aaColorPickerHueBar?.addEventListener('pointerup', endColorPickerHueDrag);
            els.aaColorPickerHueBar?.addEventListener('pointercancel', endColorPickerHueDrag);
            els.aaColorEyedropperBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                aaPickDrawerColor(els.aaColorDrawerInput, color => applyColorDrawerValue(color, true));
            });
            els.aaColorDrawerInput?.addEventListener('input', event => applyColorDrawerValue(event.target.value));
            els.aaColorDrawerInput?.addEventListener('change', event => applyColorDrawerValue(event.target.value,
                true));
            els.aaColorDrawerHexInput?.addEventListener('input', event => {
                const color = normalizeDrawerColor(event.target.value, null);
                if (color) {
                    applyColorDrawerValue(color);
                }
            });
            els.aaColorDrawerHexInput?.addEventListener('change', event => applyColorDrawerValue(event.target.value,
                true));
            els.aaColorDrawerAlphaInput?.addEventListener('input', event => {
                if (!state.colorDrawerTargetInput || typeof applyColorDrawerValue !== 'function') return;
                applyColorDrawerValue(els.aaColorDrawerInput?.value || '#111827');
            });
            els.aaColorDrawerAlphaInput?.addEventListener('change', event => {
                if (!state.colorDrawerTargetInput || typeof applyColorDrawerValue !== 'function') return;
                applyColorDrawerValue(els.aaColorDrawerInput?.value || '#111827', true);
            });
            document.querySelectorAll('[data-aa-color-preset]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    applyColorDrawerValue(button.dataset.aaColorPreset, true);
                });
            });
            els.aaFontDrawerSearch?.addEventListener('input', event => {
                state.fontDrawerQuery = event.target.value || '';
                renderFontDrawerList();
            });
            els.aaFontDrawerChips?.addEventListener('click', event => {
                const button = event.target.closest('[data-aa-font-group]');
                if (!button) return;
                event.preventDefault();
                state.fontDrawerGroup = button.dataset.aaFontGroup || 'all';
                renderFontDrawerList();
            });
            els.aaFontDrawerList?.addEventListener('click', event => {
                const toggle = event.target.closest('[data-aa-font-weight-toggle]');
                if (toggle && els.aaFontDrawerList.contains(toggle)) {
                    event.preventDefault();
                    event.stopPropagation();
                    clearFontDrawerPreview();
                    const family = toggle.dataset.aaWeightFamily || '';
                    state.fontDrawerWeightFamily = state.fontDrawerWeightFamily === family ? '' : family;
                    renderFontDrawerList();
                    return;
                }
                const weightButton = event.target.closest('[data-aa-font-weight]');
                if (weightButton && els.aaFontDrawerList.contains(weightButton)) {
                    event.preventDefault();
                    event.stopPropagation();
                    applyFontDrawerSelection(weightButton.dataset.aaFontFamily, {
                        fontWeight: weightButton.dataset.aaFontWeight,
                    });
                    return;
                }
                const button = event.target.closest('[data-aa-font-family]');
                if (!button) return;
                event.preventDefault();
                applyFontDrawerSelection(button.dataset.aaFontFamily, {
                    fontWeight: button.dataset.aaDefaultFontWeight,
                });
            });
            els.aaFontDrawerList?.addEventListener('mouseover', event => {
                const weightButton = event.target.closest('[data-aa-font-weight]');
                if (weightButton && els.aaFontDrawerList.contains(weightButton)) {
                    previewFontDrawerFamily(weightButton.dataset.aaFontFamily, {
                        fontWeight: weightButton.dataset.aaFontWeight,
                    });
                    return;
                }
                const button = event.target.closest('[data-aa-font-family]');
                if (!button || !els.aaFontDrawerList.contains(button)) return;
                previewFontDrawerFamily(button.dataset.aaFontFamily, {
                    fontWeight: button.dataset.aaDefaultFontWeight,
                });
            });
            els.aaFontDrawerList?.addEventListener('focusin', event => {
                const weightButton = event.target.closest('[data-aa-font-weight]');
                if (weightButton) {
                    previewFontDrawerFamily(weightButton.dataset.aaFontFamily, {
                        fontWeight: weightButton.dataset.aaFontWeight,
                    });
                    return;
                }
                const button = event.target.closest('[data-aa-font-family]');
                if (!button) return;
                previewFontDrawerFamily(button.dataset.aaFontFamily, {
                    fontWeight: button.dataset.aaDefaultFontWeight,
                });
            });
            els.aaFontDrawerList?.addEventListener('mouseleave', () => clearFontDrawerPreview());
            els.aaTemplateSearchInput?.addEventListener('input', filterTemplateDrawer);
            els.aaTemplateCategoryChips?.addEventListener('click', event => {
                const button = event.target.closest('[data-aa-template-category]');
                if (!button) return;
                event.preventDefault();
                showTemplateListView(button.dataset.aaTemplateCategory, button.dataset
                    .aaTemplateCategoryLabel || button.textContent.trim());
            });
            els.aaTemplateBackBtn?.addEventListener('click', event => {
                event.preventDefault();
                showTemplateCategoryView();
            });
            showTemplateCategoryView();
            document.querySelectorAll('[data-aa-template-preview]').forEach(button => {
                button.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    openTemplatePreview(button.dataset.aaTemplatePreview, button.dataset
                        .aaTemplateTitle ||
                        'Template', button.dataset.aaTemplatePublicUrl || '');
                });
            });
            els.aaCloseTemplatePreviewBtn?.addEventListener('click', closeTemplatePreview);
            els.aaTemplatePreviewModal?.addEventListener('click', event => {
                if (event.target === els.aaTemplatePreviewModal) closeTemplatePreview();
            });
            document.addEventListener('click', event => {
                const anchor = event.target.closest?.('a[href]');
                if (!shouldGuardEditorNavigation(anchor)) return;
                event.preventDefault();
                openExitGuard(anchor.href);
            }, true);
            window.addEventListener('beforeunload', event => {
                if (!state.hasUnsavedChanges || state.allowNavigation) return;
                event.preventDefault();
                event.returnValue = '';
            });
            els.aaExitGuardCancelBtn?.addEventListener('click', closeExitGuard);
            els.aaExitGuardSaveBtn?.addEventListener('click', async () => {
                const url = state.pendingNavigationUrl;
                const button = els.aaExitGuardSaveBtn;
                setButtonLoading(button, true, 'Menyimpan...');
                showEditorToast('Sedang menyimpan perubahan...', 'saving', 'Menyimpan');
                try {
                    await saveDraft(true);
                    closeExitGuard();
                    showEditorToast('Perubahan berhasil disimpan.');
                    if (url) {
                        navigateAfterGuard(url);
                    }
                } catch (error) {
                    setStatus(error.message || 'Gagal menyimpan perubahan.', 'error');
                    showEditorToast(error.message || 'Gagal menyimpan perubahan.', 'error');
                } finally {
                    setButtonLoading(button, false);
                    if (els.aaEditorToast?.classList.contains('is-saving')) hideEditorToast();
                }
            });
            els.editorAccessModal?.addEventListener('click', event => {
                if (event.target.closest('[data-access-modal-close]')) {
                    event.preventDefault();
                    closeEditorAccessModal();
                }
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && els.editorAccessModal && !els.editorAccessModal.hidden) {
                    closeEditorAccessModal();
                }
            });
            document.querySelectorAll('[data-aa-shape]').forEach(button => {
                button.addEventListener('click', () => addShape(button.dataset.aaShape));
            });
            document.querySelectorAll('[data-aa-sticker]').forEach(button => {
                button.addEventListener('click', () => addSticker(button.dataset.aaSticker));
            });
            els.aaEditorAssetUploadForm?.addEventListener('submit', uploadEditorAsset);
            els.aaEditorAssetSearchInput?.addEventListener('input', event => {
                state.editorAssetQuery = event.target.value || '';
                state.editorAssetVisible = 40;
                renderEditorAssets();
            });
            els.aaEditorAssetCategoryGrid?.addEventListener('click', event => {
                const button = event.target.closest('[data-aa-editor-asset-category]');
                if (!button) return;
                event.preventDefault();
                showEditorAssetListView(button.dataset.aaEditorAssetCategory, button.dataset
                    .aaEditorAssetCategoryLabel || button.textContent.trim());
            });
            els.aaEditorAssetBackBtn?.addEventListener('click', event => {
                event.preventDefault();
                showEditorAssetCategoryView();
            });
            els.aaEditorAssetGrid?.addEventListener('click', event => {
                const deleteButton = event.target.closest('[data-aa-editor-asset-delete-id]');
                if (deleteButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    deleteEditorAsset(deleteButton.dataset.aaEditorAssetDeleteId);
                    return;
                }
                const button = event.target.closest('[data-aa-editor-asset-id]');
                if (!button) return;
                const asset = state.editorAssets.find(item => item.id === button.dataset.aaEditorAssetId);
                insertEditorAsset(asset);
            });
            els.aaEditorAssetMoreBtn?.addEventListener('click', () => {
                state.editorAssetVisible += 40;
                renderEditorAssets();
            });
            $('aaAddHeadingBtn').addEventListener('click', () => addText('heading'));
            $('aaAddTextBtn').addEventListener('click', () => addText('text'));
            $('aaAddLinkTextBtn').addEventListener('click', addLinkText);
            $('aaAddCopyTextBtn').addEventListener('click', addCopyText);
            $('aaAddGuestNameTextBtn').addEventListener('click', addGuestNameText);
            $('aaGuestNameBtn').addEventListener('click', () => addGuestbookElement('name'));
            $('aaGuestAttendanceBtn').addEventListener('click', () => addGuestbookElement('attendance'));
            $('aaGuestMessageBtn').addEventListener('click', () => addGuestbookElement('message'));
            $('aaGuestStickerBtn').addEventListener('click', () => addGuestbookElement('sticker'));
            $('aaGuestSubmitBtn').addEventListener('click', () => addGuestbookElement('submit'));
            $('aaGuestCommentListBtn').addEventListener('click', () => addGuestbookElement('list'));
            $('aaMusicPlayerBtn').addEventListener('click', addMusicPlayer);
            $('aaScrollNextBtn').addEventListener('click', addScrollNextButton);
            $('aaCountdownBtn').addEventListener('click', addCountdownTimer);
            $('aaYoutubeVideoBtn').addEventListener('click', addYoutubeVideo);
	            $('aaPhotoGalleryBtn').addEventListener('click', addPhotoGallery);
	            $('aaSocialMediaBtn')?.addEventListener('click', addSocialMediaElement);
            $('aaStoryMakerBtn')?.addEventListener('click', addStoryMakerElement);
            setupBusinessProfileElements();
            $('aaAddImageBtn').addEventListener('click', event => {
                if (guardMediaLibraryFeature(event)) return;
                state.mediaMode = 'insert';
                els.aaImageInput.click();
            });
            $('aaRefreshMediaBtn').addEventListener('click', event => {
                if (guardMediaLibraryFeature(event)) return;
                loadMedia({ force: true });
            });
            els.aaMediaSelectAllInput?.addEventListener('change', event => {
                const checked = event.currentTarget.checked;
                state.selectedMediaIds = new Set(
                    checked ? (state.mediaAssets || []).map(item => String(item.id || '')).filter(Boolean) : []
                );
                els.aaMediaGrid?.querySelectorAll?.('.aa-media-select-input').forEach(input => {
                    input.checked = checked;
                    input.closest('.aa-media-item')?.classList.toggle('is-selected', checked);
                });
                syncMediaBulkBar();
            });
            els.aaDeleteSelectedMediaBtn?.addEventListener('click', deleteSelectedMedia);
            els.aaImageInput.addEventListener('change', async event => {
                try {
                    await uploadImages(event.target.files);
                } catch (error) {
                    setMediaUploadState(error.message || 'Upload gagal.', 'error');
                    setStatus(error.message, 'error');
                } finally {
                    event.target.value = '';
                }
            });

            document.querySelectorAll('[data-aa-align]').forEach(button => {
                button.addEventListener('click', () => applyActiveStyle({
                    textAlign: button.dataset.aaAlign
                }));
            });
            $('aaBoldBtn').addEventListener('click', toggleBoldStyle);
            $('aaItalicBtn').addEventListener('click', () => toggleTextStyle('fontStyle', 'italic', 'normal'));
            $('aaUnderlineBtn').addEventListener('click', () => toggleTextStyle('underline', true, false));
            document.querySelectorAll('[data-aa-animation]').forEach(button => {
                button.addEventListener('mouseenter', () => {
                    if (!canUsePremiumFeature()) return;
                    const active = state.canvas.getActiveObject();
                    previewObjectAnimation(button.dataset.aaAnimation, active);
                });
                button.addEventListener('click', event => {
                    if (guardPremiumFeature(event)) return;
                    setActiveAnimation(button.dataset.aaAnimation);
                    if (button.closest('#aaAnimationPopover')) {
                        closeAnimationPopover();
                    }
                });
            });
            const updateActiveAnimateTiming = (commit, input) => {
                const active = state.canvas.getActiveObject();
                const textTarget = getContextTextTarget(active);
                if (textTarget && aaGetTextAnimationConfig(textTarget).enabled) {
                    updateActiveTextAnimationTiming(commit, input);
                    return;
                }
                updateActiveAnimationTiming(commit, input);
            };
            document.querySelectorAll('[data-aa-animation-delay], [data-aa-animation-duration]').forEach(input => {
                input.addEventListener('input', event => updateActiveAnimateTiming(false, event.currentTarget));
                input.addEventListener('change', event => updateActiveAnimateTiming(true, event.currentTarget));
            });
            document.querySelectorAll('[data-aa-text-animation]').forEach(button => {
                button.addEventListener('mouseenter', () => {
                    if (!canUsePremiumFeature()) return;
                    previewTextAnimation(button.dataset.aaTextAnimation);
                });
                button.addEventListener('click', event => {
                    if (guardPremiumFeature(event)) return;
                    setActiveTextAnimation(button.dataset.aaTextAnimation);
                    if (button.closest('#aaAnimationPopover')) {
                        closeAnimationPopover();
                    }
                });
            });
            document.querySelectorAll('[data-aa-text-animation-stagger]').forEach(input => {
                input.addEventListener('input', event => updateActiveTextAnimationTiming(false, event.currentTarget));
                input.addEventListener('change', event => updateActiveTextAnimationTiming(true, event.currentTarget));
            });

            $('aaUndoBtn').addEventListener('click', undo);
            $('aaRedoBtn').addEventListener('click', redo);
            $('aaZoomOutBtn').addEventListener('click', () => {
                state.zoom = Math.max(0.15, state.zoom - 0.08);
                updateZoom();
            });
            $('aaZoomInBtn').addEventListener('click', () => {
                state.zoom = Math.min(1.5, state.zoom + 0.08);
                updateZoom();
            });
            $('aaFitBtn').addEventListener('click', fitZoom);

            $('aaDuplicateBtn').addEventListener('click', duplicateActive);
            $('aaDeleteBtn').addEventListener('click', deleteActive);
            $('aaForwardBtn').addEventListener('click', () => {
                const active = state.canvas.getActiveObject();
                if (active) {
                    state.canvas.bringForward(active);
                    snapshot();
                }
            });
            $('aaBackwardBtn').addEventListener('click', () => {
                const active = state.canvas.getActiveObject();
                if (active) {
                    state.canvas.sendBackwards(active);
                    snapshot();
                }
            });
            $('aaReplaceImageBtn').addEventListener('click', event => {
                if (guardPremiumFeature(event)) return;
                state.mediaMode = 'replace';
                els.aaImageInput.click();
            });
            els.aaStartCropBtn?.addEventListener('click', startCropMode);
            els.aaApplyCropBoxBtn?.addEventListener('click', applyCropBoxAndFinish);
            els.aaCancelCropBtn?.addEventListener('click', cancelCropBoxAndFinish);
            $('aaResetCropBtn').addEventListener('click', resetImageCrop);
            els.aaImageRadiusInput.addEventListener('input', event => {
                const active = state.isCropping && state.cropTarget ? state.cropTarget : state.canvas
                    .getActiveObject();
                if (!active || active.type !== 'image') return;
                if (els.aaImageRadiusValue) els.aaImageRadiusValue.textContent =
                    `${Math.max(0, Number(event.target.value) || 0)}px`;
                applyImageBorderRadius(active, event.target.value);
                state.canvas.requestRenderAll();
                syncContextRadiusControl(active);
                snapshot();
            });
            [els.aaCropXInput, els.aaCropYInput, els.aaCropWidthInput, els.aaCropHeightInput].forEach(input => {
                input.addEventListener('keydown', event => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        applyCropFromProperties();
                    }
                });
            });

            els.aaTextInput.addEventListener('input', event => applyActiveStyle({
                text: event.target.value
            }));
            els.aaLinkUrlInput.addEventListener('input', event => applyLinkInteractionValue(event.target.value));
            els.aaLinkPopoverUrlInput?.addEventListener('input', event => applyLinkInteractionValue(event.target
                .value));
            els.aaSocialPopoverPlatformInput?.addEventListener('change', event => {
                const platform = event.target.value || 'instagram';
                const meta = aaSocialPlatformMeta(platform);
                applySocialLinkValue({
                    socialPlatform: platform,
                    socialLabel: meta.label,
                    link: meta.url,
                });
            });
            els.aaSocialPopoverLabelInput?.addEventListener('input', event => applySocialLinkValue({
                socialLabel: event.target.value,
            }));
            els.aaSocialPopoverUrlInput?.addEventListener('input', event => applySocialLinkValue({
                link: event.target.value,
            }));
            els.aaCopyTextInput.addEventListener('input', event => applyCopyInteractionValue({
                copyText: event.target.value,
            }));
            els.aaCopyPopoverTextInput?.addEventListener('input', event => applyCopyInteractionValue({
                copyText: event.target.value,
            }));
            els.aaCopyFeedbackInput.addEventListener('input', event => applyCopyInteractionValue({
                copyFeedback: event.target.value,
            }));
            els.aaCopyPopoverFeedbackInput?.addEventListener('input', event => applyCopyInteractionValue({
                copyFeedback: event.target.value,
            }));
            const syncSocialSettings = () => {
                const active = state.canvas?.getActiveObject?.();
                if (!active || active.customType !== 'social-media') return;
                const links = {
                    instagram: els.aaSocialInstagramInput?.value || '',
                    tiktok: els.aaSocialTiktokInput?.value || '',
                    threads: els.aaSocialThreadsInput?.value || '',
                    x: els.aaSocialXInput?.value || '',
                    facebook: els.aaSocialFacebookInput?.value || '',
                    youtube: els.aaSocialYoutubeInput?.value || '',
                };
                active.set({
                    socialTitle: els.aaSocialTitleInput?.value || 'Ikuti Kami',
                    socialLinks: links,
                });
                aaUpdateInteractivePreviewText(active, active.socialTitle || 'Ikuti Kami',
                    `${aaSocialActiveCount(links)} link aktif`);
                snapshot();
            };
            [
                els.aaSocialTitleInput,
                els.aaSocialInstagramInput,
                els.aaSocialTiktokInput,
                els.aaSocialThreadsInput,
                els.aaSocialXInput,
                els.aaSocialFacebookInput,
                els.aaSocialYoutubeInput,
            ].forEach(input => input?.addEventListener('input', syncSocialSettings));
            els.aaMusicPopoverUrlInput?.addEventListener('input', event => applyMusicInteractionValue({
                audioUrl: event.target.value,
            }));
            els.aaMusicDrawerUrlInput?.addEventListener('input', event => {
                applyMusicInteractionValue({
                    audioUrl: event.target.value,
                });
                if (typeof syncMusicDrawerForSelection === 'function') syncMusicDrawerForSelection();
            });
            els.aaMusicPopoverBgInput?.addEventListener('input', event => applyMusicPopoverStyle({
                controlBackground: getAlphaColorInputValue(event.target, '#0f766e'),
            }));
            els.aaMusicDrawerBgInput?.addEventListener('input', event => {
                applyMusicPopoverStyle({
                    controlBackground: event.target.value || '#0f766e',
                });
                if (typeof syncMusicDrawerForSelection === 'function') syncMusicDrawerForSelection();
            });
            els.aaMusicPopoverRadiusInput?.addEventListener('input', event => applyMusicPopoverStyle({
                controlRadius: event.target.value,
            }));
            els.aaMusicDrawerRadiusInput?.addEventListener('input', event => {
                applyMusicPopoverStyle({
                    controlRadius: event.target.value,
                });
                if (typeof syncMusicDrawerForSelection === 'function') syncMusicDrawerForSelection();
            });
            els.aaMusicDrawerShapeInput?.addEventListener('change', event => {
                const active = state.canvas?.getActiveObject?.();
                if (!active || active.customType !== 'music-player') return;
                active.set('musicButtonShape', event.target.value === 'pill' ? 'pill' : 'circle');
                snapshot();
                if (typeof syncMusicDrawerForSelection === 'function') syncMusicDrawerForSelection();
            });
            els.aaMusicPopoverAutoplayInput?.addEventListener('change', event => applyMusicInteractionValue({
                autoplayAfterInteraction: event.target.checked,
            }));
            els.aaMusicDrawerAutoplayInput?.addEventListener('change', event => {
                applyMusicInteractionValue({
                    autoplayAfterInteraction: event.target.checked,
                });
                if (typeof syncMusicDrawerForSelection === 'function') syncMusicDrawerForSelection();
            });
            els.aaMusicPopoverLoopInput?.addEventListener('change', event => applyMusicInteractionValue({
                loopAudio: event.target.checked,
            }));
            els.aaMusicDrawerLoopInput?.addEventListener('change', event => {
                applyMusicInteractionValue({
                    loopAudio: event.target.checked,
                });
                if (typeof syncMusicDrawerForSelection === 'function') syncMusicDrawerForSelection();
            });
            els.aaMusicPopoverShowButtonInput?.addEventListener('change', event => applyMusicInteractionValue({
                showPlayerButton: event.target.checked,
            }));
            els.aaMusicDrawerShowButtonInput?.addEventListener('change', event => {
                applyMusicInteractionValue({
                    showPlayerButton: event.target.checked,
                });
                if (typeof syncMusicDrawerForSelection === 'function') syncMusicDrawerForSelection();
            });
            els.aaMusicDrawerUploadBtn?.addEventListener('click', () => {
                els.aaMusicDrawerFileInput?.click();
            });
            els.aaMusicDrawerFileInput?.addEventListener('change', event => {
                const file = event.target.files?.[0] || null;
                if (file && typeof uploadMusicDrawerFile === 'function') {
                    uploadMusicDrawerFile(file);
                }
            });
            els.aaYoutubePopoverUrlInput?.addEventListener('input', event => applyYoutubeInteractionValue({
                youtubeUrl: event.target.value,
            }));
            els.aaYoutubePopoverBgInput?.addEventListener('input', event => applyYoutubePopoverStyle({
                controlBackground: getAlphaColorInputValue(event.target, '#111827'),
            }));
            els.aaYoutubePopoverRadiusInput?.addEventListener('input', event => applyYoutubePopoverStyle({
                controlRadius: event.target.value,
            }));
            els.aaYoutubePopoverAutoplayInput?.addEventListener('change', event => applyYoutubeInteractionValue({
                youtubeAutoplayOnView: event.target.checked,
            }));
            els.aaYoutubePopoverLoopInput?.addEventListener('change', event => applyYoutubeInteractionValue({
                youtubeLoop: event.target.checked,
            }));
            els.aaOpeningButtonBgInput?.addEventListener('input', event => applyOpeningButtonInteractionValue({
                controlBackground: event.target.value,
            }));
            els.aaOpeningButtonTextColorInput?.addEventListener('input', event => applyOpeningButtonInteractionValue({
                controlTextColor: event.target.value,
            }));
            els.aaOpeningButtonFontInput?.addEventListener('change', event => {
                const fontFamily = event.target.value || 'Inter';
                loadEditorFontFamily(fontFamily, loadedFamily => applyOpeningButtonInteractionValue({
                    fontFamily: loadedFamily,
                }));
            });
            els.aaOpeningButtonRadiusInput?.addEventListener('input', event => applyOpeningButtonInteractionValue({
                controlRadius: event.target.value,
            }));
            els.aaOpeningButtonPaddingYInput?.addEventListener('input', event => applyOpeningButtonInteractionValue({
                padding: event.target.value,
            }));
            els.aaGuestFieldPopoverTextInput?.addEventListener('input', event => applyGuestFieldInteractionValue({
                text: event.target.value,
            }));
            els.aaGuestFieldPopoverBgInput?.addEventListener('input', event => applyGuestFieldInteractionValue({
                backgroundColor: event.target.value,
            }));
            els.aaGuestFieldPopoverFontInput?.addEventListener('change', event => {
                const fontFamily = event.target.value || 'Inter';
                const apply = () => applyGuestFieldInteractionValue({
                    fontFamily,
                });
                if (document.fonts?.load) {
                    Promise.all([
                            ensureBunnyFontCss(fontFamily),
                            ensureGoogleFontCss(fontFamily),
                            ensureCustomFontCss(fontFamily),
                        ])
                        .then(() => document.fonts.load('24px "' + fontFamily.replace(/"/g, '') + '"'))
                        .then(apply)
                        .catch(apply);
                    return;
                }
                apply();
            });
            els.aaGuestFieldPopoverSizeInput?.addEventListener('input', event => applyGuestFieldInteractionValue({
                fontSize: event.target.value,
            }));
            els.aaGuestFieldPopoverColorInput?.addEventListener('input', event => applyGuestFieldInteractionValue({
                fill: event.target.value,
            }));
            els.aaGuestFieldPopoverRadiusInput?.addEventListener('input', event => applyGuestFieldInteractionValue({
                borderRadius: event.target.value,
            }));
            els.aaGuestFieldPopoverRequiredInput?.addEventListener('change', event =>
                applyGuestFieldInteractionValue({
                    required: event.target.checked,
                }));
            els.aaGuestFieldPopoverMaxInput?.addEventListener('input', event => applyGuestFieldInteractionValue({
                maxLength: event.target.value,
            }));
            els.aaGuestNameFormatInput?.addEventListener('input', event => {
                const active = state.canvas.getActiveObject();
                if (!isGuestNameObject(active)) return;
                const templateText = event.target.value || 'Kepada Yth.\n{{guest_name}}';
                setGuestNameTemplateObject(active, templateText);
                state.canvas.requestRenderAll();
                snapshot();
            });
            els.aaGuestNameBgInput?.addEventListener('input', event => {
                const active = state.canvas.getActiveObject();
                if (!isGuestNameObject(active)) return;
                setGuestNameGlassColors(active, {
                    glassBackgroundColor: event.target.value,
                });
                state.canvas.requestRenderAll();
                snapshot();
            });
            els.aaGuestNameCloseInput?.addEventListener('input', event => {
                const active = state.canvas.getActiveObject();
                if (!isGuestNameObject(active)) return;
                setGuestNameGlassColors(active, {
                    closeButtonColor: event.target.value,
                });
                state.canvas.requestRenderAll();
                snapshot();
            });
            els.aaAudioUrlInput?.addEventListener('input', event => {
                const active = state.canvas.getActiveObject();
                if (!active || active.customType !== 'music-player') return;
                active.set('audioUrl', event.target.value.trim());
                snapshot();
            });
            els.aaAudioAutoplayInput?.addEventListener('change', event => {
                const active = state.canvas.getActiveObject();
                if (!active || active.customType !== 'music-player') return;
                active.set('autoplayAfterInteraction', event.target.checked);
                snapshot();
            });
            els.aaAudioLoopInput?.addEventListener('change', event => {
                const active = state.canvas.getActiveObject();
                if (!active || active.customType !== 'music-player') return;
                active.set('loopAudio', event.target.checked);
                snapshot();
            });
            els.aaAudioShowButtonInput?.addEventListener('change', event => {
                const active = state.canvas.getActiveObject();
                if (!active || active.customType !== 'music-player') return;
                active.set('showPlayerButton', event.target.checked);
                snapshot();
            });
            els.aaInteractiveBgInput?.addEventListener('input', event => {
                updateInteractiveControlStyle({
                    controlBackground: event.target.value,
                });
            });
            els.aaInteractiveRadiusInput?.addEventListener('input', event => {
                updateInteractiveControlStyle({
                    controlRadius: event.target.value,
                });
            });
            els.aaScrollLockInput?.addEventListener('change', event => {
                const active = state.canvas.getActiveObject();
                if (!active || active.customType !== 'scroll-next-button') return;
                active.set('lockPageScroll', event.target.checked);
                snapshot();
            });
            els.aaCountdownDateInput?.addEventListener('focus', event => openCountdownDatePicker(event.target));
            els.aaCountdownDateInput?.addEventListener('click', event => openCountdownDatePicker(event.target));
            els.aaCountdownDatePickerBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                openCountdownDatePicker(els.aaCountdownDateInput);
            });
            els.aaCountdownDatePrevBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                const current = state.countdownPickerDate || getCountdownPickerDate();
                renderCountdownDatePicker(new Date(current.getFullYear(), current.getMonth() - 1, 1));
                requestAnimationFrame(positionCountdownDatePicker);
            });
            els.aaCountdownDateNextBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                const current = state.countdownPickerDate || getCountdownPickerDate();
                renderCountdownDatePicker(new Date(current.getFullYear(), current.getMonth() + 1, 1));
                requestAnimationFrame(positionCountdownDatePicker);
            });
            els.aaCountdownDatePickerGrid?.addEventListener('click', event => {
                const button = event.target.closest('[data-date]');
                if (!button) return;
                event.preventDefault();
                event.stopPropagation();
                els.aaCountdownDateInput.value = button.dataset.date;
                if (els.aaCountdownContextDateInput) {
                    els.aaCountdownContextDateInput.value = button.dataset.date;
                }
                setActiveCountdownDate(button.dataset.date);
                renderCountdownDatePicker(parseCountdownDate(button.dataset.date));
                closeCountdownDatePicker();
            });
            [els.aaCountdownDateInput, els.aaCountdownTimeInput].forEach(input => {
                input?.addEventListener('input', event => {
                    const active = state.canvas.getActiveObject();
                    if (!active || active.customType !== 'countdown-timer') return;
                    if (event.target === els.aaCountdownDateInput) {
                        event.target.value = formatCountdownDateInput(event.target.value);
                    }
                    const date = els.aaCountdownDateInput.value;
                    setActiveCountdownDate(date);
                    if (event.target === els.aaCountdownDateInput) {
                        renderCountdownDatePicker(parseCountdownDate(date) || state
                            .countdownPickerDate);
                    }
                });
            });
            els.aaCountdownGapInput?.addEventListener('input', event => {
                const active = state.canvas.getActiveObject();
                if (!active || active.customType !== 'countdown-timer') return;
                active.set('countdownGap', Math.max(0, Number(event.target.value) || 0));
                refreshCountdownPreviewObject(active);
            });
            els.aaCountdownContextDateInput?.addEventListener('input', event => {
                event.target.value = formatCountdownDateInput(event.target.value);
                applyCountdownContextValue({
                    countdownDate: event.target.value,
                });
            });
            els.aaCountdownContextDateInput?.addEventListener('focus', event => openCountdownDatePicker(event
                .target));
            els.aaCountdownContextDateInput?.addEventListener('click', event => openCountdownDatePicker(event
                .target));
            els.aaCountdownContextDatePickerBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                openCountdownDatePicker(els.aaCountdownContextDateInput);
            });
            els.aaCountdownContextTimeInput?.addEventListener('input', event => {
                applyCountdownContextValue({
                    countdownTime: event.target.value || '00:00',
                });
            });
            els.aaCountdownContextBgInput?.addEventListener('input', event => {
                applyCountdownContextValue({
                    controlBackground: getAlphaColorInputValue(event.target, '#f8fafc'),
                });
            });
            els.aaCountdownContextRadiusInput?.addEventListener('input', event => {
                applyCountdownContextValue({
                    controlRadius: event.target.value,
                });
            });
            els.aaCountdownContextGapInput?.addEventListener('input', event => {
                applyCountdownContextValue({
                    countdownGap: event.target.value,
                });
            });
            els.aaCountdownContextFontInput?.addEventListener('change', event => {
                const fontFamily = event.target.value || 'Inter';
                const apply = () => applyCountdownContextValue({
                    countdownFontFamily: fontFamily,
                });
                if (document.fonts?.load) {
                    Promise.all([
                            ensureBunnyFontCss(fontFamily),
                            ensureGoogleFontCss(fontFamily),
                            ensureCustomFontCss(fontFamily),
                        ])
                        .then(() => document.fonts.load('24px "' + fontFamily.replace(/"/g, '') + '"'))
                        .then(apply)
                        .catch(apply);
                    return;
                }
                apply();
            });
            els.aaCountdownContextSizeInput?.addEventListener('input', event => {
                applyCountdownContextValue({
                    countdownFontSize: event.target.value,
                });
            });
            els.aaCountdownContextColorInput?.addEventListener('input', event => {
                applyCountdownContextValue({
                    countdownTextColor: event.target.value,
                });
            });
            els.aaGalleryUploadBtn?.addEventListener('click', event => {
                if (guardPremiumFeature(event)) return;
                if (!activeGalleryObject()) {
                    setStatus('Pilih elemen gallery terlebih dahulu.', 'error');
                    return;
                }
                state.mediaMode = 'gallery';
                els.aaImageInput.click();
            });
            els.aaGalleryPickMediaBtn?.addEventListener('click', event => {
                if (guardPremiumFeature(event)) return;
                if (!activeGalleryObject()) {
                    setStatus('Pilih elemen gallery terlebih dahulu.', 'error');
                    return;
                }
                state.mediaMode = 'gallery';
                setStatus('Pilih foto dari Media Library di panel kiri.');
            });
            [els.aaGalleryImagesInput, els.aaGalleryColumnsInput, els.aaGalleryGapInput, els.aaGalleryRadiusInput]
            .forEach(input => {
                input?.addEventListener('input', () => {
                    const active = state.canvas.getActiveObject();
                    if (!active || active.customType !== 'photo-gallery') return;
                    const items = normalizeGalleryItems(active);
                    active.set({
                        galleryItems: items,
                        galleryImages: items.map(item => item.src),
                        galleryColumns: Math.max(1, Math.min(6, Number(els
                            .aaGalleryColumnsInput.value) || 2)),
                        galleryGap: Math.max(0, Number(els.aaGalleryGapInput.value) || 0),
                        galleryRadius: Math.max(0, Number(els.aaGalleryRadiusInput.value) ||
                            0),
                    });
                    refreshInteractivePreviewObject(active);
                });
            });
            els.aaGuestbookTitleInput?.addEventListener('input', event => setGuestbookField('title', event.target
                .value));
            els.aaGuestbookSubtitleInput?.addEventListener('input', event => setGuestbookField('subtitle', event
                .target.value));
            els.aaGuestbookButtonInput?.addEventListener('input', event => setGuestbookField('buttonText', event
                .target.value));
            els.aaGuestbookBgInput?.addEventListener('input', event => setGuestbookField('backgroundColor', event
                .target.value));
            els.aaGuestbookCardInput?.addEventListener('input', event => setGuestbookField('cardColor', event.target
                .value));
            els.aaGuestbookTextInput?.addEventListener('input', event => setGuestbookField('textColor', event.target
                .value));
            els.aaGuestbookAccentInput?.addEventListener('input', event => setGuestbookField('accentColor', event
                .target.value));
            els.aaGuestbookRadiusInput?.addEventListener('input', event => setGuestbookField('borderRadius', event
                .target.value));
            els.aaGuestbookMaxHeightInput?.addEventListener('input', event => setGuestbookField('maxHeight', event
                .target.value));
            els.aaGuestbookStickerInput?.addEventListener('change', event => setGuestbookField('showSticker', event
                .target.checked));
            els.aaGuestbookAttendanceInput?.addEventListener('change', event => setGuestbookField('showAttendance',
                event.target.checked));
            els.aaGuestbookHideBtn?.addEventListener('click', () => {
                state.guestbook = normalizeGuestbookConfig({
                    ...state.guestbook,
                    enabled: false,
                });
                syncGuestbookPanel();
                snapshot();
                setStatus('Guestbook widget disembunyikan');
            });
            // els.aaFontInput.addEventListener('change', event => applyActiveStyle({
            //     fontFamily: event.target.value
            // }));
            els.aaFontInput.addEventListener('change', function() {
                const active = state.canvas.getActiveObject();

                if (!active) {
                    return;
                }

                const fontFamily = this.value;

                const isText =
                    active.type === 'i-text' ||
                    active.type === 'textbox' ||
                    active.type === 'text';

                if (!isText && !isGuestbookObject(active) && !isInteractiveObject(active)) {
                    return;
                }

                Promise.all([
                    ensureBunnyFontCss(fontFamily),
                    ensureGoogleFontCss(fontFamily),
                    ensureCustomFontCss(fontFamily),
                ]).then(function() {
                    return document.fonts.load('24px "' + fontFamily.replace(/"/g, '') + '"');
                }).then(function() {
                    if (isGuestbookObject(active)) {
                        applyActiveStyle({
                            fontFamily: fontFamily
                        });
                    } else if (isInteractiveObject(active)) {
                        applyActiveStyle({
                            fontFamily: fontFamily
                        });
                    } else {
                        active.set({
                            fontFamily: fontFamily
                        });

                        active.dirty = true;
                        state.canvas.requestRenderAll();

                        if (typeof snapshot === 'function') {
                            snapshot();
                        }
                    }
                    syncInspector();
                });
            });
            els.aaFontSizeInput.addEventListener('input', event => {
                applyActiveStyle({
                    fontSize: Number(event.target.value) || 42
                });
                syncTextContextToolbar();
            });
            els.aaGuestFieldBgInput?.addEventListener('input', event => applyActiveStyle({
                backgroundColor: event.target.value
            }));
            els.aaColorInput.addEventListener('input', event => {
                const active = state.canvas.getActiveObject();
                if (!active) return;
                if (active.type === 'line') {
                    applyActiveStyle({
                        stroke: event.target.value
                    });
                } else {
                    applyActiveStyle({
                        fill: event.target.value
                    });
                }
                syncContextToolbar();
                syncTextContextToolbar();
            });
            els.aaBackgroundInput.addEventListener('input', event => {
                state.canvas.backgroundColor = event.target.value;
                state.canvas.renderAll();
                snapshot();
            });
            els.aaUploadBackgroundBtn?.addEventListener('click', () => {
                const file = els.aaBackgroundImageInput?.files?. [0] || null;
                uploadCanvasBackgroundImage(file);
            });
            els.aaBackgroundImageInput?.addEventListener('change', event => {
                uploadCanvasBackgroundImage(event.target.files?. [0] || null);
            });
            els.aaRemoveBackgroundBtn?.addEventListener('click', removeCanvasBackgroundImage);
            els.aaBackgroundOpacityInput?.addEventListener('input', event => applyBackgroundImageControls({
                opacity: event.target.value,
            }));
            els.aaBackgroundOpacityInput?.addEventListener('change', event => applyBackgroundImageControls({
                opacity: event.target.value,
            }, true));
            els.aaBackgroundPositionXInput?.addEventListener('input', event => applyBackgroundImageControls({
                offsetX: event.target.value,
            }));
            els.aaBackgroundPositionXInput?.addEventListener('change', event => applyBackgroundImageControls({
                offsetX: event.target.value,
            }, true));
            els.aaBackgroundPositionYInput?.addEventListener('input', event => applyBackgroundImageControls({
                offsetY: event.target.value,
            }));
            els.aaBackgroundPositionYInput?.addEventListener('change', event => applyBackgroundImageControls({
                offsetY: event.target.value,
            }, true));
            els.aaBackgroundPositionResetBtn?.addEventListener('click', event => {
                event.preventDefault();
                applyBackgroundImageControls({
                    offsetX: 0,
                    offsetY: 0,
                }, true);
            });
            syncBackgroundImageControls();

            $('aaPortraitBtn').addEventListener('click', () => {
                state.canvas.setWidth(1080);
                state.canvas.setHeight(1920);
                ensureBackgroundImageBack();
                state.canvas.renderAll();
                updateCanvasRatioButtons();
                fitZoom();
                snapshot();
            });
            $('aaTallPortraitBtn').addEventListener('click', () => {
                state.canvas.setWidth(1080);
                state.canvas.setHeight(2280);
                ensureBackgroundImageBack();
                state.canvas.renderAll();
                updateCanvasRatioButtons();
                fitZoom();
                snapshot();
            });
            $('aaSquareBtn').addEventListener('click', () => {
                state.canvas.setWidth(1080);
                state.canvas.setHeight(1080);
                ensureBackgroundImageBack();
                state.canvas.renderAll();
                updateCanvasRatioButtons();
                fitZoom();
                snapshot();
            });

            els.aaEditorToastClose?.addEventListener('click', hideEditorToast);

            $('aaSaveBtn').addEventListener('click', async () => {
                const button = $('aaSaveBtn');
                setButtonLoading(button, true, 'Saving...');
                showEditorToast('Sedang menyimpan draft desain...', 'saving', 'Menyimpan');
                try {
                    await saveDraft(false);
                } catch (error) {
                    setStatus(error.message, 'error');
                    showEditorToast(error.message || 'Draft gagal disimpan.', 'error');
                } finally {
                    setButtonLoading(button, false);
                    if (els.aaEditorToast?.classList.contains('is-saving')) hideEditorToast();
                }
            });
            $('aaPreviewBtn').addEventListener('click', async () => {
                try {
                    await waitForCanvasReady();
                    hideInteractionPopover();
                    hideCountdownContextToolbar();
                    els.aaPreviewFrame.srcdoc = previewDocument();
                    els.aaPreviewModal.classList.add('is-open');
                } catch (error) {
                    setStatus(error.message, 'error');
                }
            });
            $('aaClosePreviewBtn').addEventListener('click', () => {
                els.aaPreviewModal.classList.remove('is-open');

                if (els.aaPreviewFrame) {
                    els.aaPreviewFrame.srcdoc = '';
                    els.aaPreviewFrame.src = 'about:blank';
                }
            });
            $('aaPublishBtn')?.addEventListener('click', event => {
                if (guardPublishFeature(event)) return;

                els.aaPublishTitleInput.value = els.aaPublishTitleInput.value || config.initialTitle;
                els.aaPublishSlugInput.value = normalizeSlug(els.aaPublishSlugInput.value || config
                    .initialSlug || config.initialTitle);
                checkSlug().catch(() => {});
                hideObjectFloatingToolbar();
                hideCountdownContextToolbar();
                hideInteractionPopover();
                els.aaPublishModal.classList.add('is-open');
            });
            $('aaClosePublishBtn').addEventListener('click', () => {
                els.aaPublishModal.classList.remove('is-open');
                requestAnimationFrame(syncObjectFloatingToolbar);
            });
            els.aaPublishSlugInput.addEventListener('input', () => {
                clearTimeout(state.slugTimer);
                state.slugTimer = setTimeout(() => checkSlug().catch(() => {}), 350);
            });
            $('aaConfirmPublishBtn').addEventListener('click', async () => {
                const button = $('aaConfirmPublishBtn');
                setButtonLoading(button, true, 'Publishing...');
                showEditorToast('Sedang menyimpan dan menerbitkan website...', 'saving', 'Publishing');
                try {
                    await publish();
                } catch (error) {
                    setStatus(error.message, 'error');
                    showEditorToast(error.message || 'Website gagal dipublish.', 'error');
                } finally {
                    setButtonLoading(button, false);
                    if (els.aaEditorToast?.classList.contains('is-saving')) hideEditorToast();
                }
            });
            $('aaCopyLinkBtn').addEventListener('click', async () => {
                await navigator.clipboard.writeText(state.publicUrl);
                setStatus('Link disalin');
            });
            $('aaShareWaBtn').addEventListener('click', () => {
                window.open(<?= json_encode(site_url('share-whatsapp?page_id=' . $pageId)) ?>, '_blank',
                    'noopener');
            });

            if (config.canSaveTemplate && $('saveTemplateBtn') && els.aaTemplateModal) {
                $('saveTemplateBtn').addEventListener('click', () => {
                    els.aaTemplateNameInput.value = els.aaTemplateNameInput.value || config.initialTitle;
                    els.aaTemplateSlugInput.value = normalizeSlug(els.aaTemplateSlugInput.value || config
                        .initialSlug ||
                        config.initialTitle);
                    if (typeof syncTemplateSaveMode === 'function') syncTemplateSaveMode();
                    if (typeof syncTemplateProjectCategoryVisibility === 'function') syncTemplateProjectCategoryVisibility();
                    if (typeof syncTemplateSubcategoryVisibility === 'function') syncTemplateSubcategoryVisibility();
                    els.aaTemplateModal.classList.add('is-open');
                });
                els.aaTemplateModeCreate?.addEventListener('change', () => syncTemplateSaveMode());
                els.aaTemplateModeUpdate?.addEventListener('change', () => syncTemplateSaveMode());
                els.aaTemplateCategoryInput?.addEventListener('change', () => syncTemplateSubcategoryVisibility());
                document.querySelectorAll('.aa-template-subcategory-input').forEach(input => {
                    input.addEventListener('change', () => syncTemplateSubcategoryCounts?.());
                });
                $('aaCloseTemplateBtn').addEventListener('click', () => els.aaTemplateModal.classList.remove(
                    'is-open'));
                $('aaCancelTemplateBtn').addEventListener('click', () => els.aaTemplateModal.classList.remove(
                    'is-open'));
                $('aaConfirmTemplateBtn').addEventListener('click', async () => {
                    const button = $('aaConfirmTemplateBtn');
                    setButtonLoading(button, true, 'Menyimpan...');
                    showEditorToast('Sedang menyimpan template...', 'saving', 'Menyimpan Template');
                    try {
                        await saveAsTemplate();
                    } catch (error) {
                        setStatus(error.message, 'error');
                        showEditorToast(error.message, 'error');
                    } finally {
                        setButtonLoading(button, false);
                        if (els.aaEditorToast?.classList.contains('is-saving')) hideEditorToast();
                    }
                });
            }

            function aaCloseCanvasDrawerForObjectSelection() {
                const active = state.canvas?.getActiveObject?.();
                if (!active || active.customType === 'background') return;

                const leftbar = document.querySelector('.aa-leftbar');
                const canvasPanel = document.querySelector('[data-aa-left-panel="canvas"].is-active');
                if (!leftbar?.classList.contains('is-drawer-open') || !canvasPanel) return;

                closeLeftDrawerPanel();
            }

            state.canvas.on('selection:created', function(event) {
                aaFilterLockedObjectsFromActiveSelection();
                aaResolveAccidentalImageActiveSelection(event);
                aaNormalizeActiveTextSelection();
                aaNormalizeActiveCountdownSelection();
                syncInspector();
                if (typeof syncOpenLeftDrawerForSelection === 'function') syncOpenLeftDrawerForSelection();
                aaCloseCanvasDrawerForObjectSelection();
                aaSetStaggerControlVisible(aaIsTextAnimationTarget(state.canvas.getActiveObject()));
                aaRenderRecentColors();
                aaRenderRecentFonts();
            });

            state.canvas.on('selection:updated', function(event) {
                aaFilterLockedObjectsFromActiveSelection();
                aaResolveAccidentalImageActiveSelection(event);
                aaNormalizeActiveTextSelection();
                aaNormalizeActiveCountdownSelection();
                syncInspector();
                if (typeof syncOpenLeftDrawerForSelection === 'function') syncOpenLeftDrawerForSelection();
                aaCloseCanvasDrawerForObjectSelection();
                aaSetStaggerControlVisible(aaIsTextAnimationTarget(state.canvas.getActiveObject()));
                aaRenderRecentColors();
                aaRenderRecentFonts();
            });
            state.canvas.on('selection:cleared', () => {
                if (state.isCropping && state.cropBox) {
                    state.canvas.setActiveObject(state.cropBox);
                    aaRenderRecentColors();
                    aaRenderRecentFonts();
                    aaSetStaggerControlVisible(false);
                    syncCropPanel(state.cropBox);
                    syncCropUi();
                    return;
                }

                syncInspector();
                if (typeof syncOpenLeftDrawerForSelection === 'function') syncOpenLeftDrawerForSelection();
                hideObjectFloatingToolbar();
                hideCountdownContextToolbar();
                hideInteractionPopover();
            });
            state.canvas.on('object:modified', function(event) {
                aaEndSelectionTransformUi();
                aaFinalizeTextboxResize(event);
                aaFinalizeCountdownResize(event);

                syncCropPanel(state.canvas.getActiveObject());
                syncObjectFloatingToolbar();
                syncCountdownContextToolbar();
                syncInteractionPopover();
                syncCropUi();
                snapshot();
            });
            state.canvas.on('object:moving', () => {
                aaBeginSelectionTransformUi();
                syncObjectFloatingToolbar();
                syncCountdownContextToolbar();
                syncInteractionPopover();
                syncCropUi();
            });
            state.canvas.on('object:scaling', function(event) {
                aaBeginSelectionTransformUi();
                aaRememberTextResizeCorner(event);
                aaHandleTextboxSideResizeLive(event);
                aaHandleCountdownSideResizeLive(event);
                aaPreserveImageAspectRatioOnScale(event);

                syncObjectFloatingToolbar();
                syncCountdownContextToolbar();
                syncInteractionPopover();
                syncCropUi();
            });
            state.canvas.on('object:rotating', () => {
                aaBeginSelectionTransformUi();
                syncObjectFloatingToolbar();
                syncCountdownContextToolbar();
                syncInteractionPopover();
                syncCropUi();
            });
            state.canvas.on('mouse:down', function(event) {
                const sourceEvent = event?.e || null;
                const pointer = sourceEvent ? state.canvas.getPointer(sourceEvent, true) : null;
                state.__aaLastSelectionPointer = pointer ? {
                    x: pointer.x,
                    y: pointer.y,
                    time: Date.now(),
                    modifier: Boolean(sourceEvent.shiftKey || sourceEvent.metaKey || sourceEvent.ctrlKey || sourceEvent.altKey),
                } : null;
                aaRememberTextResizeCorner(event);
                resolveLargeImageClickSelection(event);
            });
            state.canvas.on('mouse:up', () => {
                aaEndSelectionTransformUi();
                settleEditorPointerState();
            });

            document.addEventListener('pointerdown', aaClearSelectionWhenClickOutsideCanvas, true);
            state.canvas.on('mouse:over', event => {
                if (!event.target || event.target === state.cropBox || state.isCropping) return;
                state.hoverTarget = event.target;
                state.canvas.requestRenderAll();
            });
            state.canvas.on('mouse:out', event => {
                if (state.hoverTarget === event.target) {
                    state.hoverTarget = null;
                    state.canvas.requestRenderAll();
                }
            });
            state.canvas.on('after:render', drawHoverHighlight);
            function aaScheduleHistorySnapshot() {
                if (
                    state.isRestoring ||
                    state.__aaHistoryRestoring ||
                    state.__aaHistoryBatch ||
                    state.isCropping ||
                    !state.canvas
                ) {
                    return;
                }

                clearTimeout(state.__aaHistorySnapshotTimer);

                state.__aaHistorySnapshotTimer = setTimeout(function () {
                    if (
                        state.isRestoring ||
                        state.__aaHistoryRestoring ||
                        state.__aaHistoryBatch ||
                        state.isCropping ||
                        !state.canvas
                    ) {
                        return;
                    }

                    snapshot();
                }, 120);
            }

            state.canvas.on('object:added', function(event) {
                aaApplySafeImageHitTesting?.(event?.target);
                aaScheduleHistorySnapshot(event);
            });
            state.canvas.on('object:removed', aaScheduleHistorySnapshot);

            document.addEventListener('keydown', event => {
                const activeTag = document.activeElement?.tagName?.toLowerCase();
                const isTyping = ['input', 'textarea', 'select'].includes(activeTag) || document
                    .activeElement?.isContentEditable;
                const modifier = event.metaKey || event.ctrlKey;
                const shortcutKey = String(event.key || '').toLowerCase();
                const activeObject = state.canvas?.getActiveObject?.();
                const isArrowKey = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key);

                function scheduleKeyboardNudgeSnapshot() {
                    clearTimeout(state.__aaKeyboardNudgeSnapshotTimer);
                    state.__aaKeyboardNudgeSnapshotTimer = setTimeout(function () {
                        if (
                            state.isRestoring ||
                            state.__aaHistoryRestoring ||
                            state.__aaHistoryBatch ||
                            state.isCropping ||
                            !state.canvas
                        ) {
                            return;
                        }

                        snapshot();
                    }, 180);
                }

                function nudgeActiveObjectWithKeyboard() {
                    const active = state.canvas?.getActiveObject?.();

                    if (
                        !active ||
                        active === state.cropBox ||
                        active.locked === true ||
                        active.customType === 'background' ||
                        active.isEditing === true ||
                        state.isCropping ||
                        state.isRestoring ||
                        state.__aaHistoryRestoring
                    ) {
                        return false;
                    }

                    if (typeof active.getObjects === 'function' && active.type === 'activeSelection') {
                        const lockedChild = active.getObjects().some(object => object?.locked === true);
                        if (lockedChild) return false;
                    }

                    const step = event.shiftKey ? 10 : (event.altKey ? 0.5 : 1);
                    let deltaX = 0;
                    let deltaY = 0;

                    if (event.key === 'ArrowLeft') deltaX = -step;
                    if (event.key === 'ArrowRight') deltaX = step;
                    if (event.key === 'ArrowUp') deltaY = -step;
                    if (event.key === 'ArrowDown') deltaY = step;

                    if (active.lockMovementX === true) deltaX = 0;
                    if (active.lockMovementY === true) deltaY = 0;
                    if (deltaX === 0 && deltaY === 0) return false;

                    active.set({
                        left: (Number(active.left) || 0) + deltaX,
                        top: (Number(active.top) || 0) + deltaY,
                    });
                    active.setCoords?.();
                    active.dirty = true;

                    aaRememberActiveObjectForEditorUi?.(active);
                    aaBeginSelectionTransformUi?.();
                    syncObjectFloatingToolbar?.();
                    syncCountdownContextToolbar?.();
                    syncInteractionPopover?.();
                    syncCropUi?.();
                    syncInspector?.();

                    state.canvas.requestRenderAll?.();
                    requestAnimationFrame(function () {
                        aaEndSelectionTransformUi?.();
                        syncObjectFloatingToolbar?.();
                        syncCountdownContextToolbar?.();
                        syncInteractionPopover?.();
                    });
                    scheduleKeyboardNudgeSnapshot();

                    return true;
                }

                if (modifier && shortcutKey === 's') {
                    event.preventDefault();
                    saveDraft(false).catch(error => setStatus(error.message, 'error'));
                    return;
                }
                if (modifier && shortcutKey === 'z') {
                    event.preventDefault();
                    event.shiftKey ? redo() : undo();
                    return;
                }
                if (modifier && shortcutKey === 'd') {
                    event.preventDefault();
                    duplicateActive();
                    return;
                }
                if (modifier && shortcutKey === 'c' && typeof aaHasFabricTextSelection === 'function' &&
                    aaHasFabricTextSelection(activeObject)) {
                    event.preventDefault();
                    event.stopPropagation();
                    copyActiveTextSelectionAsObject?.(activeObject);
                    return;
                }
                if (modifier && shortcutKey === 'x' && typeof aaHasFabricTextSelection === 'function' &&
                    aaHasFabricTextSelection(activeObject)) {
                    event.preventDefault();
                    event.stopPropagation();
                    cutActiveTextSelectionAsObject?.(activeObject);
                    return;
                }
                if (modifier && shortcutKey === 'v' && state.clipboardObjectJson?.aaClipboardKind === 'text-selection') {
                    event.preventDefault();
                    event.stopPropagation();
                    if (activeObject?.isEditing === true && typeof activeObject.exitEditing === 'function') {
                        activeObject.exitEditing();
                    }
                    pasteClipboardObject?.();
                    return;
                }
                if (!isTyping && modifier && shortcutKey === 'c') {
                    const active = state.canvas.getActiveObject();
                    if (active) {
                        event.preventDefault();
                        copyActiveObject();
                    }
                    return;
                }
                if (!isTyping && modifier && shortcutKey === 'v') {
                    if (state.clipboardObjectJson) {
                        event.preventDefault();
                        pasteClipboardObject();
                    }
                    return;
                }
                if (event.key === 'Escape') {
                    closeToolbarPopovers();
                    return;
                }
                if (!isTyping && !modifier && isArrowKey) {
                    if (nudgeActiveObjectWithKeyboard()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    return;
                }
                if (!isTyping && (event.key === 'Delete' || event.key === 'Backspace')) {
                    event.preventDefault();
                    deleteActive();
                }
            });

            window.addEventListener('resize', () => {
                closeToolbarPopovers();
                positionCountdownDatePicker();
                fitZoom();
                syncObjectFloatingToolbar();
                syncCountdownContextToolbar();
                syncInteractionPopover();
                syncCropUi();
            });
            window.addEventListener('pointerup', settleEditorPointerState);
            window.addEventListener('mouseup', settleEditorPointerState);
        }

        async function init() {
            if (state.initializing || state.initialized) return;
            state.initializing = true;

            const isMobileViewport = window.matchMedia('(max-width: 767px)').matches;
            document.body.classList.toggle('aa-editor-mobile-mode', isMobileViewport);
            document.body.classList.remove('aa-editor-device-blocked');
            document.getElementById('aaDesktopOnlyModal')?.classList.remove('is-visible');

            if (!window.fabric) {
                aaToast('Fabric.js gagal dimuat. Cek koneksi CDN.', 'error');
                state.initializing = false;
                return;
            }

            collectElements();
            showCanvasLoading('Memuat font editor...');
            await preloadEditorFonts().catch(() => null);
            installRoundedImageRenderer();
            state.canvas = new fabric.Canvas('aaFabricCanvas', {
                preserveObjectStacking: true,
                backgroundColor: '#ffffff',
                selection: true,
            });
            state.canvas.uniScaleTransform = false;
            fabric.Object.prototype.transparentCorners = false;
            fabric.Object.prototype.cornerColor = '#ffffff';
            fabric.Object.prototype.cornerStrokeColor = '#0f766e';
            fabric.Object.prototype.borderColor = '#2563eb';
            fabric.Object.prototype.borderScaleFactor = 2.4;
            fabric.Object.prototype.cornerSize = 13;
            fabric.Object.prototype.cornerStyle = 'circle';

            bindObjectContextMenu();
            bindEvents();
            aaBindRecentFontDrawer();
            aaRenderRecentFonts();
            bindOpenCanvasDrawerFromPageMode();
            bindAaRecentColorAndFont();
            loadInitialDesign();
            state.hasUnsavedChanges = false;
            if (!state.isRestoring) {
                hideCanvasLoading();
            }
            scheduleInitialFontCanvasRefresh();
            loadMedia({ force: true }).catch(() => {});
            syncInspector();
            updateZoom();

            state.initialized = true;
            state.initializing = false;
            window.FabricWebsiteEditor.initialized = true;
            window.FabricWebsiteEditor.canvas = state.canvas;
            window.FabricWebsiteEditor.saveDraft = saveDraft;
        }

        window.FabricWebsiteEditor = {
            initialized: false,
            init,
            state,
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, {
                once: true
            });
        } else {
            init();
        }

        (function installAaSmartOverlapLayerOverride() {
            if (window.__aaSmartOverlapLayerOverrideInstalled) return;
            window.__aaSmartOverlapLayerOverrideInstalled = true;

            const LAYER_ACTIONS = [
                'bring-front',
                'bring-forward',
                'send-backward',
                'send-back'
            ];

            function getEditorState() {
                return window.FabricWebsiteEditor && window.FabricWebsiteEditor.state ?
                    window.FabricWebsiteEditor.state :
                    null;
            }

            function getCanvas() {
                const editorState = getEditorState();

                if (editorState && editorState.canvas) {
                    return editorState.canvas;
                }

                if (window.FabricWebsiteEditor && window.FabricWebsiteEditor.canvas) {
                    return window.FabricWebsiteEditor.canvas;
                }

                return null;
            }

            function getActiveObject() {
                const editorState = getEditorState();
                const canvas = getCanvas();

                if (!canvas) return null;

                return (editorState && editorState.contextMenuTarget) || canvas.getActiveObject();
            }

            function getRealObjects(canvas) {
                return canvas && Array.isArray(canvas._objects) ? canvas._objects : [];
            }

            function isLayerableObject(object) {
                if (!object) return false;

                const editorState = getEditorState();

                if (editorState && object === editorState.cropBox) return false;
                if (object.visible === false) return false;
                if (object.__aaSkipObject === true) return false;
                if (object.excludeFromLayer === true) return false;
                if (object.excludeFromLayering === true) return false;

                // Background jangan ikut dihitung sebagai layer normal.
                if (object.customType === 'background') return false;
                if (object.name === 'Background Image') return false;
                if (object.name === 'background') return false;
                if (object.id === 'background') return false;

                // Object helper tertentu jangan dihitung.
                if (object.customType === 'guide') return false;
                if (object.customType === 'helper') return false;
                if (object.customType === 'selection-helper') return false;

                return true;
            }

            function getRect(object) {
                if (!object || typeof object.getBoundingRect !== 'function') {
                    return null;
                }

                try {
                    return object.getBoundingRect(true, true);
                } catch (error) {
                    return null;
                }
            }

            function rectOverlapInfo(rectA, rectB) {
                if (!rectA || !rectB) {
                    return {
                        overlap: false,
                        area: 0
                    };
                }

                const left = Math.max(rectA.left, rectB.left);
                const right = Math.min(rectA.left + rectA.width, rectB.left + rectB.width);
                const top = Math.max(rectA.top, rectB.top);
                const bottom = Math.min(rectA.top + rectA.height, rectB.top + rectB.height);

                const width = Math.max(0, right - left);
                const height = Math.max(0, bottom - top);
                const area = width * height;

                return {
                    overlap: area > 4,
                    area: area
                };
            }

            function aaRememberImageFrameState(image) {
    if (!image || image.type !== 'image') return null;

    return {
        aaImageFrameShape: image.aaImageFrameShape || 'none',
        borderRadius: Number(image.borderRadius) || 0,
        stroke: image.stroke,
        strokeWidth: image.strokeWidth,
        strokeDashArray: image.strokeDashArray,
        strokeUniform: image.strokeUniform,
        imageStrokeStyle: image.imageStrokeStyle,
        aaImageOverlayGradient: image.aaImageOverlayGradient,
        aaImageEffectPreset: image.aaImageEffectPreset,
    };
}

function aaRestoreImageFrameState(image, frameState) {
    if (!image || image.type !== 'image' || !frameState) return;

    const shape = frameState.aaImageFrameShape || 'none';

    image.set({
        aaImageFrameShape: shape,
        borderRadius: frameState.borderRadius || 0,
        stroke: frameState.stroke,
        strokeWidth: frameState.strokeWidth,
        strokeDashArray: frameState.strokeDashArray,
        strokeUniform: frameState.strokeUniform,
        imageStrokeStyle: frameState.imageStrokeStyle,
        aaImageOverlayGradient: frameState.aaImageOverlayGradient,
        aaImageEffectPreset: frameState.aaImageEffectPreset,
    });

    if (shape === 'none') {
        image.set('clipPath', null);
        applyImageBorderRadius?.(image, 0);
    } else if (shape === 'rounded') {
        image.set('clipPath', null);
        applyImageBorderRadius?.(
            image,
            frameState.borderRadius || Math.max(24, Math.min(image.width || 1, image.height || 1) * 0.12)
        );
    } else {
        image.set({
            clipPath: createImageFrameClipPath(image, shape),
        });
    }

    image.dirty = true;
    image.setCoords?.();

    syncImageFrameButtons?.(image);
}

            function getOverlappingLayerObjects(canvas, active) {
                const objects = getRealObjects(canvas);
                const activeRect = getRect(active);

                return objects
                    .filter(function(object) {
                        if (object === active) return false;
                        if (!isLayerableObject(object)) return false;

                        const rect = getRect(object);
                        const info = rectOverlapInfo(activeRect, rect);

                        return info.overlap;
                    })
                    .map(function(object) {
                        const rect = getRect(object);
                        const info = rectOverlapInfo(activeRect, rect);

                        return {
                            object: object,
                            index: objects.indexOf(object),
                            overlapArea: info.area
                        };
                    })
                    .sort(function(a, b) {
                        return a.index - b.index;
                    });
            }

            function moveBefore(array, object, target) {
                const fromIndex = array.indexOf(object);
                let targetIndex = array.indexOf(target);

                if (fromIndex < 0 || targetIndex < 0 || fromIndex === targetIndex) return false;

                array.splice(fromIndex, 1);

                if (fromIndex < targetIndex) {
                    targetIndex -= 1;
                }

                array.splice(targetIndex, 0, object);

                return true;
            }

            function moveAfter(array, object, target) {
                const fromIndex = array.indexOf(object);
                let targetIndex = array.indexOf(target);

                if (fromIndex < 0 || targetIndex < 0 || fromIndex === targetIndex) return false;

                array.splice(fromIndex, 1);

                if (fromIndex < targetIndex) {
                    targetIndex -= 1;
                }

                array.splice(targetIndex + 1, 0, object);

                return true;
            }

            function moveToVeryBack(canvas, active) {
                const objects = getRealObjects(canvas);
                const layerable = objects.filter(isLayerableObject);

                if (!layerable.length) return false;

                const bottom = layerable[0];

                if (!bottom || bottom === active) return false;

                return moveBefore(objects, active, bottom);
            }

            function moveToVeryFront(canvas, active) {
                const objects = getRealObjects(canvas);
                const layerable = objects.filter(isLayerableObject);

                if (!layerable.length) return false;

                const top = layerable[layerable.length - 1];

                if (!top || top === active) return false;

                return moveAfter(objects, active, top);
            }

            function moveBackwardOneVisualLayer(canvas, active) {
                const objects = getRealObjects(canvas);
                const activeIndex = objects.indexOf(active);

                if (activeIndex < 0) return false;

                const overlaps = getOverlappingLayerObjects(canvas, active);

                // Cari object overlap paling dekat di bawah active.
                const below = overlaps
                    .filter(function(item) {
                        return item.index < activeIndex;
                    })
                    .sort(function(a, b) {
                        return b.index - a.index;
                    })[0];

                if (below && below.object) {
                    return moveBefore(objects, active, below.object);
                }

                // Fallback kalau tidak ada object overlap:
                // pakai layerable object terdekat di bawah.
                const layerable = objects.filter(isLayerableObject);
                const currentLayerIndex = layerable.indexOf(active);
                const previous = layerable[currentLayerIndex - 1];

                if (!previous) return false;

                return moveBefore(objects, active, previous);
            }

            function moveForwardOneVisualLayer(canvas, active) {
                const objects = getRealObjects(canvas);
                const activeIndex = objects.indexOf(active);

                if (activeIndex < 0) return false;

                const overlaps = getOverlappingLayerObjects(canvas, active);

                // Cari object overlap paling dekat di atas active.
                const above = overlaps
                    .filter(function(item) {
                        return item.index > activeIndex;
                    })
                    .sort(function(a, b) {
                        return a.index - b.index;
                    })[0];

                if (above && above.object) {
                    return moveAfter(objects, active, above.object);
                }

                // Fallback kalau tidak ada object overlap:
                // pakai layerable object terdekat di atas.
                const layerable = objects.filter(isLayerableObject);
                const currentLayerIndex = layerable.indexOf(active);
                const next = layerable[currentLayerIndex + 1];

                if (!next) return false;

                return moveAfter(objects, active, next);
            }

            function refreshLayerResult(canvas, active, message) {
                if (!canvas || !active) return;

                active.setCoords();

                getRealObjects(canvas).forEach(function(object) {
                    if (!object) return;

                    object.dirty = true;

                    if (typeof object.setCoords === 'function') {
                        object.setCoords();
                    }
                });

                canvas.setActiveObject(active);

                if (typeof canvas.requestRenderAll === 'function') {
                    canvas.requestRenderAll();
                } else {
                    canvas.renderAll();
                }

                if (typeof window.storeCurrentPage === 'function') window.storeCurrentPage();
                if (typeof window.snapshot === 'function') window.snapshot();
                if (typeof window.syncInspector === 'function') window.syncInspector();
                if (typeof window.updateObjectFloatingToolbar === 'function') window
                    .updateObjectFloatingToolbar();
                if (typeof window.syncObjectFloatingToolbar === 'function') window.syncObjectFloatingToolbar();

                if (typeof window.setStatus === 'function') {
                    window.setStatus(message || 'Layer object diperbarui');
                }

                const objects = getRealObjects(canvas);
                const overlaps = getOverlappingLayerObjects(canvas, active);

                console.log('[AA SMART LAYER]', message, {
                    activeIndex: objects.indexOf(active),
                    overlapIndexes: overlaps.map(function(item) {
                        return {
                            index: item.index,
                            type: item.object.type,
                            text: item.object.text || '',
                            name: item.object.name || '',
                            customType: item.object.customType || '',
                            overlapArea: Math.round(item.overlapArea)
                        };
                    }),
                    total: objects.length
                });
            }

            function aaSmartLayerAction(action) {
                const canvas = getCanvas();

                if (!canvas) {
                    console.warn('[AA SMART LAYER] Canvas tidak ditemukan.');
                    return false;
                }

                const active = getActiveObject();

                if (!active) {
                    console.warn('[AA SMART LAYER] Tidak ada object aktif.');
                    if (typeof window.setStatus === 'function') {
                        window.setStatus('Pilih object terlebih dahulu.', 'error');
                    }
                    return false;
                }

                if (active.type === 'activeSelection') {
                    console.warn('[AA SMART LAYER] Pilih satu object saja.');
                    if (typeof window.setStatus === 'function') {
                        window.setStatus('Pilih satu object saja untuk ubah layer.', 'error');
                    }
                    return false;
                }

                if (!isLayerableObject(active)) {
                    console.warn('[AA SMART LAYER] Object ini tidak bisa diubah layer.', active);
                    if (typeof window.setStatus === 'function') {
                        window.setStatus('Object ini tidak bisa diubah layer-nya.', 'error');
                    }
                    return false;
                }

                let moved = false;
                let message = '';

                if (action === 'bring-forward') {
                    moved = moveForwardOneVisualLayer(canvas, active);
                    message = 'Object maju ke layer visual berikutnya';
                }

                if (action === 'send-backward') {
                    moved = moveBackwardOneVisualLayer(canvas, active);
                    message = 'Object mundur ke layer visual sebelumnya';
                }

                if (action === 'bring-front') {
                    moved = moveToVeryFront(canvas, active);
                    message = 'Object dibawa ke depan';
                }

                if (action === 'send-back') {
                    moved = moveToVeryBack(canvas, active);
                    message = 'Object dikirim ke belakang';
                }

                if (!moved) {
                    console.warn('[AA SMART LAYER] Layer tidak berubah:', action);
                    if (typeof window.setStatus === 'function') {
                        window.setStatus('Layer object tidak berubah.', 'error');
                    }
                    return false;
                }

                refreshLayerResult(canvas, active, message);

                if (typeof window.closeObjectContextMenu === 'function') {
                    window.closeObjectContextMenu();
                }

                return true;
            }

            function getClickedLayerAction(event) {
                const target = event.target instanceof Element ? event.target : event.target?.parentElement;

                if (!target) return '';

                const button = target.closest(
                    '[data-aa-context-action], [data-aa-layer-action], [data-aa-object-action], #aaForwardBtn, #aaBackwardBtn'
                );

                if (!button) return '';

                let action =
                    button.dataset.aaContextAction ||
                    button.dataset.aaLayerAction ||
                    button.dataset.aaObjectAction ||
                    '';

                if (!action && button.id === 'aaForwardBtn') {
                    action = 'bring-forward';
                }

                if (!action && button.id === 'aaBackwardBtn') {
                    action = 'send-backward';
                }

                return LAYER_ACTIONS.indexOf(action) !== -1 ? action : '';
            }

            document.addEventListener('click', function(event) {
                const action = getClickedLayerAction(event);

                if (!action) return;

                event.preventDefault();
                event.stopPropagation();

                if (typeof event.stopImmediatePropagation === 'function') {
                    event.stopImmediatePropagation();
                }

                aaSmartLayerAction(action);
            }, true);

            window.aaSmartLayerAction = aaSmartLayerAction;

            console.log('[AA SMART LAYER] aktif.');
        })();

        function aaEditorFeatureReport(options = {}) {
            const enabled = options.force === true || window.AA_EDITOR_DEBUG === true;

            if (!enabled) {
                return;
            }

            const green = 'color:#16a34a;font-weight:900;';
            const red = 'color:#dc2626;font-weight:900;';
            const yellow = 'color:#d97706;font-weight:900;';
            const blue = 'color:#146cb8;font-weight:900;';
            const muted = 'color:#64748b;font-weight:700;';
            const titleStyle = [
                'background:linear-gradient(135deg,#0f766e,#146cb8)',
                'color:#ffffff',
                'font-weight:900',
                'padding:8px 12px',
                'border-radius:8px'
            ].join(';');

            function hasFunction(name) {
                try {
                    return typeof window[name] === 'function' || typeof eval(name) === 'function';
                } catch (error) {
                    return false;
                }
            }

            function hasLocalFunction(fn) {
                return typeof fn === 'function';
            }

            function hasElement(selector) {
                return !!document.querySelector(selector);
            }

            function safeCheck(callback) {
                try {
                    return callback() === true;
                } catch (error) {
                    return false;
                }
            }

            function getCanvasStatus() {
                return !!(
                    window.fabric &&
                    state &&
                    state.canvas &&
                    typeof state.canvas.getObjects === 'function'
                );
            }

            const features = [{
                    group: 'Core',
                    name: 'FabricJS Library',
                    active: safeCheck(() => !!window.fabric && typeof fabric.Canvas === 'function'),
                    detail: 'window.fabric + fabric.Canvas'
                },
                {
                    group: 'Core',
                    name: 'Canvas Engine',
                    active: safeCheck(() => getCanvasStatus()),
                    detail: 'state.canvas siap'
                },
                {
                    group: 'Core',
                    name: 'Editor State',
                    active: safeCheck(() => !!state && Array.isArray(state.pages)),
                    detail: 'state.pages tersedia'
                },
                {
                    group: 'Core',
                    name: 'Save Draft',
                    active: safeCheck(() => hasLocalFunction(saveDraft)),
                    detail: 'saveDraft()'
                },
                {
                    group: 'Core',
                    name: 'Publish',
                    active: safeCheck(() => hasLocalFunction(publishPage) || hasLocalFunction(
                        savePublishedPage)),
                    detail: 'publish handler'
                },

                {
                    group: 'Page',
                    name: 'Multi Page',
                    active: safeCheck(() => hasLocalFunction(addPage) && hasLocalFunction(loadPageData)),
                    detail: 'addPage() + loadPageData()'
                },
                {
                    group: 'Page',
                    name: 'Page Preview',
                    active: safeCheck(() => hasLocalFunction(renderPagePreview)),
                    detail: 'renderPagePreview()'
                },
                {
                    group: 'Page',
                    name: 'Hidden Page',
                    active: safeCheck(() => hasLocalFunction(togglePageVisibility) || hasLocalFunction(
                        setPageHidden)),
                    detail: 'hide/show page'
                },

                {
                    group: 'Object',
                    name: 'Object Selection',
                    active: safeCheck(() => getCanvasStatus() && typeof state.canvas.getActiveObject ===
                        'function'),
                    detail: 'canvas.getActiveObject()'
                },
                {
                    group: 'Object',
                    name: 'Object Floating Toolbar',
                    active: safeCheck(() => hasLocalFunction(syncObjectFloatingToolbar)),
                    detail: 'syncObjectFloatingToolbar()'
                },
                {
                    group: 'Object',
                    name: 'Object Transform Overlay',
                    active: safeCheck(() => hasLocalFunction(syncObjectTransformOverlay) && hasElement(
                        '.aa-object-transform-overlay')),
                    detail: '.aa-object-transform-overlay'
                },
                {
                    group: 'Object',
                    name: 'Object Overflow Overlay',
                    active: safeCheck(() => hasLocalFunction(syncObjectOverflowOverlay)),
                    detail: 'syncObjectOverflowOverlay()'
                },
                {
                    group: 'Object',
                    name: 'Copy Paste Object',
                    active: safeCheck(() => hasLocalFunction(copyActiveObject) && hasLocalFunction(
                        pasteClipboardObject)),
                    detail: 'copyActiveObject() + pasteClipboardObject()'
                },

                {
                    group: 'Text',
                    name: 'Text Tool',
                    active: safeCheck(() => hasLocalFunction(addText)),
                    detail: 'addText()'
                },
                {
                    group: 'Text',
                    name: 'Textbox Resize',
                    active: safeCheck(() => hasLocalFunction(aaFinalizeTextboxResize) && hasLocalFunction(
                        aaHandleTextboxSideResizeLive)),
                    detail: 'aaFinalizeTextboxResize()'
                },
                {
                    group: 'Text',
                    name: 'Text Context Toolbar',
                    active: safeCheck(() => hasLocalFunction(syncTextContextToolbar)),
                    detail: 'syncTextContextToolbar()'
                },
                {
                    group: 'Text',
                    name: 'Text Effects',
                    active: safeCheck(() => hasElement('.aa-text-effects-popover')),
                    detail: '.aa-text-effects-popover'
                },

                {
                    group: 'Image',
                    name: 'Image Upload',
                    active: safeCheck(() => hasLocalFunction(addImageFromUrl) || hasLocalFunction(
                        handleImageUpload)),
                    detail: 'image upload handler'
                },
                {
                    group: 'Image',
                    name: 'Crop Image',
                    active: safeCheck(() => hasLocalFunction(startCropMode) && hasLocalFunction(
                        applyCropFromBox)),
                    detail: 'startCropMode() + applyCropFromBox()'
                },
                {
                    group: 'Image',
                    name: 'Rotated Crop',
                    active: safeCheck(() => hasLocalFunction(aaCalculateRotatedCropFromBox) && hasLocalFunction(
                        aaIsRotatedCropTarget)),
                    detail: 'rotated crop helper'
                },
                {
                    group: 'Image',
                    name: 'Crop DOM Overlay',
                    active: safeCheck(() => hasLocalFunction(syncCropDomOverlay) && hasElement(
                        '.aa-crop-dom-overlay')),
                    detail: '.aa-crop-dom-overlay'
                },
                {
                    group: 'Image',
                    name: 'Border Radius Image',
                    active: safeCheck(() => hasLocalFunction(applyImageBorderRadius)),
                    detail: 'applyImageBorderRadius()'
                },

                {
                    group: 'Layer',
                    name: 'Smart Layer',
                    active: safeCheck(() => typeof window.aaSmartLayerAction === 'function'),
                    detail: 'window.aaSmartLayerAction()'
                },
                {
                    group: 'Layer',
                    name: 'Layer Forward/Backward',
                    active: safeCheck(() => hasElement('#aaForwardBtn') || hasElement('#aaBackwardBtn') ||
                        typeof window.aaSmartLayerAction === 'function'),
                    detail: 'layer action buttons'
                },

                {
                    group: 'Premium',
                    name: 'Premium Guard',
                    active: safeCheck(() => hasLocalFunction(guardPremiumFeature) && hasLocalFunction(
                        openEditorAccessModal)),
                    detail: 'guardPremiumFeature()'
                },
                {
                    group: 'Premium',
                    name: 'Access Modal',
                    active: safeCheck(() => hasElement('.editor-access-modal')),
                    detail: '.editor-access-modal'
                },

                {
                    group: 'Interactive',
                    name: 'Interaction Popover',
                    active: safeCheck(() => hasLocalFunction(syncInteractionPopover) && hasElement(
                        '.aa-interaction-popover')),
                    detail: '.aa-interaction-popover'
                },
                {
                    group: 'Interactive',
                    name: 'Countdown',
                    active: safeCheck(() => hasLocalFunction(syncCountdownContextToolbar)),
                    detail: 'syncCountdownContextToolbar()'
                },
                {
                    group: 'Interactive',
                    name: 'Gallery',
                    active: safeCheck(() => hasLocalFunction(addGalleryElement) || hasElement(
                        '.aa-gallery-toolbar')),
                    detail: 'gallery tool'
                },
                {
                    group: 'Interactive',
                    name: 'Guestbook',
                    active: safeCheck(() => hasElement('.aa-editor-guestbook-preview') || hasLocalFunction(
                        renderGuestbookPreview)),
                    detail: 'guestbook preview'
                },

                {
                    group: 'System',
                    name: 'Undo / Snapshot',
                    active: safeCheck(() => hasLocalFunction(snapshot)),
                    detail: 'snapshot()'
                },
                {
                    group: 'System',
                    name: 'Inspector Panel',
                    active: safeCheck(() => hasLocalFunction(syncInspector)),
                    detail: 'syncInspector()'
                },
                {
                    group: 'System',
                    name: 'Status Toast',
                    active: safeCheck(() => hasLocalFunction(setStatus)),
                    detail: 'setStatus()'
                }
            ];

            const activeCount = features.filter(item => item.active).length;
            const totalCount = features.length;
            const inactiveCount = totalCount - activeCount;

            const table = features.map(function(item, index) {
                return {
                    No: index + 1,
                    Status: item.active ? '✅ Aktif' : '⚠️ Cek',
                    Grup: item.group,
                    Fitur: item.name,
                    Detail: item.detail
                };
            });

            console.groupCollapsed(
                `%c✅ AdaAcara Web Maker — Feature Status %c${activeCount}/${totalCount} aktif`,
                titleStyle,
                activeCount === totalCount ? green : yellow
            );

            console.log(
                `%cCanvas:%c ${getCanvasStatus() ? '✅ siap' : '⚠️ belum siap'} %c| Page:%c ${state?.currentPageIndex ?? 0} %c| Objects:%c ${state?.canvas?.getObjects?.().length ?? 0}`,
                blue,
                getCanvasStatus() ? green : yellow,
                muted,
                blue,
                muted,
                blue
            );

            console.table(table);

            if (inactiveCount > 0) {
                console.warn(
                    `[AA FEATURE STATUS] Ada ${inactiveCount} fitur yang perlu dicek. Lihat baris ⚠️ Cek di table.`
                );
            } else {
                console.log('%c✅ Semua fitur utama terdeteksi aktif.', green);
            }

            console.groupEnd();

            return {
                total: totalCount,
                active: activeCount,
                inactive: inactiveCount,
                features: table
            };
        }

        window.aaEditorFeatureReport = aaEditorFeatureReport;
        aaEditorFeatureReport({
            force: true
        })
    })();
    </script>
</body>

</html>
