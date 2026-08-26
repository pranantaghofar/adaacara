            <aside class="aa-leftbar">
                <nav class="aa-left-rail" aria-label="Editor tools">
                    <a class="aa-left-rail-link" href="<?= site_url('dashboard') ?>">
                        <i data-lucide="house" class="aa-lucide-icon" aria-hidden="true"></i><span>Home</span>
                    </a>
                    <button class="aa-left-rail-tab aa-left-tab is-active" type="button" data-aa-left-tab="canvas">
                        <i data-lucide="panel-top" class="aa-lucide-icon" aria-hidden="true"></i><span>Cover</span>
                    </button>
                    <button class="aa-left-rail-tab aa-left-tab" type="button" data-aa-left-tab="layers">
                        <i data-lucide="layers-3" class="aa-lucide-icon" aria-hidden="true"></i><span>Layers</span>
                    </button>
                    <button class="aa-left-rail-tab aa-left-tab" type="button" data-aa-left-tab="templates" data-aa-limited-editor-tab="true">
                        <i data-lucide="layout-template" class="aa-lucide-icon" aria-hidden="true"></i><span>Templates</span>
                    </button>
                    <button class="aa-left-rail-tab aa-left-tab" type="button" data-aa-left-tab="elements">
                        <i data-lucide="shapes" class="aa-lucide-icon" aria-hidden="true"></i><span>Elements</span>
                    </button>
                    <button class="aa-left-rail-tab aa-left-tab" type="button" data-aa-left-tab="snippets" data-aa-limited-editor-tab="true">
                        <i data-lucide="quote" class="aa-lucide-icon" aria-hidden="true"></i><span>Kalimat</span>
                    </button>
                    <button class="aa-left-rail-tab aa-left-tab" type="button" data-aa-left-tab="ornament">
                        <i data-lucide="sparkles" class="aa-lucide-icon" aria-hidden="true"></i><span>Assets</span>
                    </button>
                    <button class="aa-left-rail-tab aa-left-tab" type="button" data-aa-left-tab="upload">
                        <i data-lucide="cloud-upload" class="aa-lucide-icon" aria-hidden="true"></i><span>Upload</span>
                    </button>
                    <span class="aa-left-rail-spacer" aria-hidden="true"></span>
                    <?php if (! empty($showImportReferencePanel)): ?>
                    <button id="aaImportReferenceTab" class="aa-left-rail-tab aa-left-tab" type="button" data-aa-left-tab="import-reference" data-aa-limited-editor-tab="true">
                        <img class="aa-left-rail-img-icon" src="<?= aa_asset_url('assets/img/1.png') ?>" alt="" aria-hidden="true"><span>ACARA AI<?= $aiPremiumCrownSvg ?? $premiumCrownSvg ?></span>
                    </button>
                    <?php endif ?>
                    <?php if (! empty($showMagicLayerAiPanel)): ?>
                    <button id="aaMagicLayerAiBtn" class="aa-left-rail-tab aa-left-tab" type="button" data-aa-left-tab="magic-layer" data-aa-limited-editor-tab="true" title="Magic Layer AI">
                        <i data-lucide="wand-sparkles" class="aa-lucide-icon" aria-hidden="true"></i><span>Magic<?= $aiPremiumCrownSvg ?? $premiumCrownSvg ?></span>
                    </button>
                    <?php endif ?>
                </nav>

                <div class="aa-left-drawer">
                    <button id="aaLeftDrawerCloseBtn" class="aa-left-drawer-close" type="button"
                        aria-label="Tutup drawer kiri">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <section class="aa-left-drawer-panel aa-tool-section is-active" data-aa-left-panel="canvas">
                        <div class="aa-panel-card">
                            <h2 class="aa-panel-title">Cover</h2>
                            <div class="grid gap-3">
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Background Color
                                    <input id="aaBackgroundInput" class="aa-field" type="color" value="#ffffff">
                                </label>
                                <div class="grid gap-2">
                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                        Background Image
                                        <input id="aaBackgroundImageInput" class="aa-field" type="file"
                                            accept="image/png,image/jpeg,image/webp,image/gif">
                                    </label>
                                    <div class="aa-canvas-bg-actions">
                                        <button id="aaUploadBackgroundBtn" class="aa-panel-btn" type="button">
                                            Upload Background
                                        </button>
                                        <button id="aaRemoveBackgroundBtn" class="aa-panel-btn" type="button">
                                            Remove
                                        </button>
                                    </div>
                                    <p id="aaBackgroundImageStatus" class="aa-canvas-bg-status">
                                        Maksimal 1MB. GIF maksimal 3MB. Gambar akan auto-cover mengikuti ukuran halaman.
                                    </p>
                                    <div class="aa-bg-control-grid" aria-label="Pengaturan background image">
                                        <label class="grid gap-1 text-xs font-black text-slate-600">
                                            Opacity Background
                                            <div class="aa-bg-range-row">
                                                <input id="aaBackgroundOpacityInput" type="range" min="0" max="100"
                                                    value="100">
                                                <span id="aaBackgroundOpacityValue" class="aa-bg-value">100</span>
                                            </div>
                                        </label>
                                        <label class="grid gap-1 text-xs font-black text-slate-600">
                                            Posisi Horizontal
                                            <div class="aa-bg-range-row">
                                                <input id="aaBackgroundPositionXInput" type="range" min="-100"
                                                    max="100" value="0">
                                                <span id="aaBackgroundPositionXValue" class="aa-bg-value">0</span>
                                            </div>
                                        </label>
                                        <label class="grid gap-1 text-xs font-black text-slate-600">
                                            Posisi Vertikal
                                            <div class="aa-bg-range-row">
                                                <input id="aaBackgroundPositionYInput" type="range" min="-100"
                                                    max="100" value="0">
                                                <span id="aaBackgroundPositionYValue" class="aa-bg-value">0</span>
                                            </div>
                                        </label>
                                        <button id="aaBackgroundPositionResetBtn" class="aa-panel-btn" type="button">
                                            Reset Posisi Background
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="layers">
                        <div class="aa-panel-card aa-layer-panel" aria-label="Layer halaman aktif">
                            <div class="aa-layer-panel-head">
                                <h2 class="aa-panel-title !mb-0">Layers</h2>
                                <span id="aaLayerCount" class="aa-layer-count">0</span>
                            </div>
                            <p class="aa-layer-hint">Pilih object, atur tampil/sembunyi, lock, dan urutan layer halaman aktif.</p>
                            <div id="aaLayerList" class="aa-layer-list" role="list"></div>
                        </div>
                    </section>

                    <?php if (! empty($showImportReferencePanel)): ?>
                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="import-reference">
                        <div class="aa-panel-card aa-acara-ai-card">
                            <div class="aa-acara-ai-shell">
                                <?php if (! empty($canImportReference)): ?>
                                <button id="aaAcaraAiNewChatBtn" class="aa-acara-ai-new-chat" type="button" title="Mulai chat baru" aria-label="Mulai chat baru">
                                    <i class="fa fa-pen-to-square" aria-hidden="true"></i>
                                </button>
                                <div id="aaAcaraAiPageLabel" class="aa-acara-ai-page-label" aria-live="polite"></div>
                                <div id="aaAcaraAiChatLog" class="aa-acara-ai-hero">
                                    <h2>What shall we do with this design?</h2>
                                </div>
                                <div class="aa-acara-ai-presets" data-aa-acara-ai-presets>
                                    <button class="aa-acara-ai-preset" type="button" data-aa-acara-ai-preset="Redesign this page with a fresh, modern look while keeping the same content">
                                        <i class="fa fa-wand-magic-sparkles" aria-hidden="true"></i>
                                        <span>Redesign this page</span>
                                    </button>
                                    <button class="aa-acara-ai-preset" type="button" data-aa-acara-ai-preset="Add an AI-generated background image that complements the existing content">
                                        <i class="fa fa-image" aria-hidden="true"></i>
                                        <span>Add background</span>
                                    </button>
                                    <button class="aa-acara-ai-preset" type="button" data-aa-acara-ai-preset="Change the style and color scheme of this page to make it more visually appealing">
                                        <i class="fa fa-paint-roller" aria-hidden="true"></i>
                                        <span>Change style</span>
                                    </button>
                                </div>
                                <input id="aaImportReferenceFileInput" class="hidden" type="file" accept="image/jpeg,image/png,image/webp">
                                <div id="aaImportReferencePreview" class="aa-acara-ai-attachment hidden">
                                    <img id="aaImportReferencePreviewImage" alt="Preview import referensi">
                                    <div class="aa-acara-ai-attachment-meta">
                                        <p id="aaImportReferencePreviewMeta"></p>
                                        <button id="aaAcaraAiAttachmentClearBtn" type="button" aria-label="Hapus gambar referensi">
                                            <i class="fa fa-xmark" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="aa-acara-ai-composer">
                                    <textarea id="aaAcaraAiPromptInput" rows="3" maxlength="2000" placeholder="Describe your idea"></textarea>
                                    <div class="aa-acara-ai-composer-actions">
                                        <button id="aaAcaraAiAttachBtn" class="aa-acara-ai-icon-btn" type="button" title="Tambah gambar referensi" aria-label="Tambah gambar referensi">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </button>
                                        <button id="aaAcaraAiSendBtn" class="aa-acara-ai-send-btn" type="button" title="Kirim prompt" aria-label="Kirim prompt">
                                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="aa-acara-ai-disclaimer">AI membantu membuat draft desain. Mohon cek ulang teks, posisi elemen, gambar, dan detail acara sebelum disimpan atau dipublish.</p>
                                <input id="aaImportReferencePageNameInput" type="hidden" value="ACARA AI">
                                <input id="aaImportReferenceSizeInput" type="hidden" value="current">
                                <input id="aaImportReferenceModeInput" type="hidden" value="cover">
                                <button id="aaImportReferenceCreateBtn" class="hidden" type="button">Baca Design</button>
                                <?php elseif (empty($hasAiPremiumAccess)): ?>
                                <p class="m-0 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-[11px] font-bold leading-relaxed text-amber-800">
                                    ACARA AI tersedia untuk member aktif. Upload gambar referensi, lalu sistem akan membaca design dan membuat objek editable.
                                </p>
                                <a class="aa-panel-btn aa-primary" href="<?= esc($plansUrl, 'attr') ?>" target="_blank" rel="noopener">
                                    Buka Akses Member<?= $aiPremiumCrownSvg ?? $premiumCrownSvg ?>
                                </a>
                                <?php else: ?>
                                <p class="m-0 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-[11px] font-bold leading-relaxed text-slate-500">
                                    Upload referensi sedang nonaktif. AdaAcara AI hanya dapat digunakan pada page referensi yang sudah ada.
                                </p>
                                <?php endif ?>
                                <?php if (! empty($canUseOcrTextDetection)): ?>
                                <div class="hidden">
                                    <p id="aaOcrTextStatus"></p>
                                    <div id="aaOcrReviewPanel" class="hidden"></div>
                                </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </section>
                    <?php endif ?>

                    <?php if (! empty($showMagicLayerAiPanel)): ?>
                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="magic-layer">
                        <div class="aa-panel-card">
                            <h2 class="aa-panel-title">Magic Layer AI</h2>
                            <?php if (! empty($canUseMagicLayerAi)): ?>
                            <div class="grid gap-3">
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Gambar desain
                                    <input id="aaMagicLayerFileInput" class="aa-field" type="file" accept="image/jpeg,image/png,image/webp">
                                    <span class="text-[11px] font-bold leading-relaxed text-slate-500">Upload JPG, PNG, atau WEBP maksimal 2MB. Preview dulu sebelum diproses AI.</span>
                                </label>
                                <div id="aaMagicLayerPreview" class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                    <img id="aaMagicLayerPreviewImage" class="max-h-64 w-full object-contain" alt="Preview Magic Layer">
                                    <p id="aaMagicLayerPreviewMeta" class="m-0 border-t border-slate-200 px-3 py-2 text-xs font-bold text-slate-500"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button id="aaMagicLayerProcessBtn" class="aa-panel-btn aa-primary" type="button">
                                        Proses Gambar<?= $aiPremiumCrownSvg ?? $premiumCrownSvg ?>
                                    </button>
                                    <button id="aaMagicLayerClearBtn" class="aa-panel-btn" type="button">
                                        Ganti Gambar
                                    </button>
                                </div>
                                <p id="aaMagicLayerAiStatus" class="m-0 text-[11px] font-bold leading-relaxed text-slate-500">
                                    Pilih gambar terlebih dahulu. Hasil Magic Layer akan dibuat di halaman baru.
                                </p>
                                <p class="m-0 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-[11px] font-bold leading-relaxed text-amber-800">
                                    Hasil AI bisa saja tidak sepenuhnya akurat. Mohon cek ulang posisi, teks, font, dan gambar sebelum disimpan atau dipublish.
                                </p>
                            </div>
                            <?php else: ?>
                            <div class="grid gap-3">
                                <p class="m-0 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-[11px] font-bold leading-relaxed text-amber-800">
                                    Magic Layer AI tersedia untuk member aktif. Creator tetap bisa mengedit desain, tetapi fitur AI premium membutuhkan paket aktif.
                                </p>
                                <a class="aa-panel-btn aa-primary" href="<?= esc($plansUrl, 'attr') ?>" target="_blank" rel="noopener">
                                    Buka Akses Member<?= $aiPremiumCrownSvg ?? $premiumCrownSvg ?>
                                </a>
                            </div>
                            <?php endif ?>
                        </div>
                    </section>
                    <?php endif ?>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="music">
                        <div class="aa-panel-card aa-music-drawer-card">
                            <h2 class="aa-panel-title">Music</h2>
                            <p id="aaMusicDrawerStatus" class="aa-music-drawer-status">Pilih object Music di canvas untuk mengatur audio.</p>
                            <div class="aa-music-drawer-preview">
                                <span class="aa-music-drawer-icon"><i class="fa fa-music" aria-hidden="true"></i></span>
                                <div>
                                    <strong id="aaMusicDrawerTitle">Music Player</strong>
                                    <small id="aaMusicDrawerSubtitle">Belum ada audio</small>
                                </div>
                            </div>
                            <div class="aa-music-drawer-controls">
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Audio URL
                                    <input id="aaMusicDrawerUrlInput" class="aa-field" type="url" placeholder="https://.../musik.mp3">
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                        Warna tombol
                                        <input id="aaMusicDrawerBgInput" class="aa-field" type="color" value="#0f766e">
                                    </label>
                                    <label class="grid gap-1 text-xs font-black text-slate-600">
                                        Radius
                                        <input id="aaMusicDrawerRadiusInput" class="aa-field" type="number" min="0" max="160" step="1" value="66">
                                    </label>
                                </div>
                                <label class="grid gap-1 text-xs font-black text-slate-600">
                                    Bentuk tombol
                                    <select id="aaMusicDrawerShapeInput" class="aa-field">
                                        <option value="circle">Circle</option>
                                        <option value="pill">Pill</option>
                                    </select>
                                </label>
                                <label class="aa-music-drawer-check">
                                    <input id="aaMusicDrawerAutoplayInput" type="checkbox">
                                    <span>Autoplay setelah interaksi pertama</span>
                                </label>
                                <label class="aa-music-drawer-check">
                                    <input id="aaMusicDrawerLoopInput" type="checkbox">
                                    <span>Loop audio</span>
                                </label>
                                <label class="aa-music-drawer-check">
                                    <input id="aaMusicDrawerShowButtonInput" type="checkbox">
                                    <span>Tampilkan tombol music di halaman public</span>
                                </label>
                            </div>
                            <div class="aa-music-drawer-library">
                                <div class="aa-music-drawer-library-head">
                                    <strong>Musik tersedia</strong>
                                    <small>Pilih musik yang sudah tersedia</small>
                                </div>
                                <div id="aaMusicBuiltinList" class="aa-music-drawer-list"></div>
                            </div>
                            <div class="aa-music-drawer-library">
                                <div class="aa-music-drawer-library-head">
                                    <strong>Upload saya</strong>
                                    <small>Audio yang kamu upload ke editor</small>
                                </div>
                                <div class="aa-music-drawer-upload">
                                    <button id="aaMusicDrawerUploadBtn" class="aa-panel-btn aa-primary" type="button">
                                        <i class="fa fa-upload" aria-hidden="true"></i>
                                        Upload Audio
                                    </button>
                                    <input id="aaMusicDrawerFileInput" type="file" accept="audio/mpeg,audio/mp3,audio/mp4,audio/x-m4a,audio/wav,audio/ogg,.mp3,.m4a,.wav,.ogg" hidden>
                                    <p id="aaMusicDrawerUploadStatus" class="aa-music-drawer-upload-status">MP3, M4A, WAV, atau OGG maksimal 4MB.</p>
                                </div>
                                <div id="aaMusicUploadedList" class="aa-music-drawer-list"></div>
                            </div>
                            <div class="aa-music-drawer-library" hidden>
                                <div class="aa-music-drawer-library-head">
                                    <strong>Music di desain</strong>
                                    <small>Pilih URL yang sudah pernah dipakai</small>
                                </div>
                                <div id="aaMusicDrawerList" class="aa-music-drawer-list"></div>
                            </div>
                        </div>
                    </section>

	                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="element-interaction">
	                        <div class="aa-panel-card aa-element-drawer-card">
	                            <h2 id="aaElementDrawerTitle" class="aa-panel-title">Element Settings</h2>
	                            <p id="aaElementDrawerStatus" class="aa-element-drawer-status">Pilih element interaktif di canvas untuk mengatur detailnya.</p>
	                            <div id="aaElementDrawerMount" class="aa-element-drawer-mount"></div>
	                            <div id="aaMobileInteractionDrawer" class="aa-mobile-interaction-drawer" hidden>
	                                <div id="aaMobileLinkSection" class="aa-mobile-interaction-section">
	                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
	                                        Link tujuan
	                                        <input id="aaMobileLinkUrlInput" type="url" placeholder="https://maps.google.com/...">
	                                    </label>
	                                </div>
	                                <div id="aaMobileSocialSection" class="aa-mobile-interaction-section is-compact">
	                                    <label class="aa-mobile-interaction-field">
	                                        Icon
	                                        <select id="aaMobileSocialPlatformInput">
	                                            <option value="instagram">Instagram</option>
	                                            <option value="tiktok">TikTok</option>
	                                            <option value="youtube">YouTube</option>
	                                            <option value="whatsapp">WhatsApp</option>
	                                            <option value="facebook">Facebook</option>
	                                            <option value="x">X</option>
	                                            <option value="threads">Threads</option>
	                                            <option value="telegram">Telegram</option>
	                                            <option value="pinterest">Pinterest</option>
	                                            <option value="linkedin">LinkedIn</option>
	                                            <option value="spotify">Spotify</option>
	                                            <option value="shopee">Shopee</option>
	                                            <option value="tokopedia">Tokopedia</option>
	                                            <option value="website">Website</option>
	                                        </select>
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Nama tampil
	                                        <input id="aaMobileSocialLabelInput" type="text" maxlength="70" placeholder="Instagram">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
	                                        Link tujuan
	                                        <input id="aaMobileSocialUrlInput" type="url" placeholder="https://instagram.com/...">
	                                    </label>
	                                </div>
	                                <div id="aaMobileCopySection" class="aa-mobile-interaction-section">
	                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
	                                        Teks yang dicopy
	                                        <textarea id="aaMobileCopyTextInput" rows="3" placeholder="Nomor rekening, alamat, kode voucher, atau teks lain"></textarea>
	                                    </label>
	                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
	                                        Pesan setelah dicopy
	                                        <input id="aaMobileCopyFeedbackInput" type="text" placeholder="Tersalin">
	                                    </label>
	                                </div>
	                                <div id="aaMobileYoutubeSection" class="aa-mobile-interaction-section is-compact">
	                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
	                                        Link Youtube
	                                        <input id="aaMobileYoutubeUrlInput" type="url" placeholder="https://youtu.be/...">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Background
	                                        <input id="aaMobileYoutubeBgInput" type="color" value="#111827">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Border radius
	                                        <span class="aa-mobile-interaction-range">
	                                            <input id="aaMobileYoutubeRadiusInput" type="range" min="0" max="120" step="1" value="18">
	                                            <output id="aaMobileYoutubeRadiusValue">18</output>
	                                        </span>
	                                    </label>
	                                    <label class="aa-mobile-interaction-check">
	                                        <input id="aaMobileYoutubeAutoplayInput" type="checkbox">
	                                        Autoplay saat terlihat
	                                    </label>
	                                    <label class="aa-mobile-interaction-check">
	                                        <input id="aaMobileYoutubeLoopInput" type="checkbox">
	                                        Loop video
	                                    </label>
	                                </div>
	                                <div id="aaMobileOpeningButtonSection" class="aa-mobile-interaction-section is-compact">
	                                    <label class="aa-mobile-interaction-field">
	                                        Background
	                                        <input id="aaMobileOpeningButtonBgInput" type="color" value="#0f766e">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Warna teks
	                                        <input id="aaMobileOpeningButtonTextColorInput" type="color" value="#ffffff">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
	                                        Font
	                                        <select id="aaMobileOpeningButtonFontInput"></select>
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Border radius
	                                        <span class="aa-mobile-interaction-range">
	                                            <input id="aaMobileOpeningButtonRadiusInput" type="range" min="0" max="160" step="1" value="48">
	                                            <output id="aaMobileOpeningButtonRadiusValue">48</output>
	                                        </span>
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Padding
	                                        <span class="aa-mobile-interaction-range">
	                                            <input id="aaMobileOpeningButtonPaddingYInput" type="range" min="6" max="90" step="1" value="28">
	                                            <output id="aaMobileOpeningButtonPaddingYValue">28</output>
	                                        </span>
	                                    </label>
	                                </div>
	                                <div id="aaMobileGuestFieldSection" class="aa-mobile-interaction-section is-compact">
	                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
	                                        Teks / label
	                                        <input id="aaMobileGuestFieldTextInput" type="text" placeholder="Nama field">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Background
	                                        <input id="aaMobileGuestFieldBgInput" type="color" value="#ffffff">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Font
	                                        <select id="aaMobileGuestFieldFontInput"></select>
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Ukuran teks
	                                        <input id="aaMobileGuestFieldSizeInput" type="number" min="8" max="260" step="1" value="36">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Warna teks
	                                        <input id="aaMobileGuestFieldColorInput" type="color" value="#334155">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Border radius
	                                        <span class="aa-mobile-interaction-range">
	                                            <input id="aaMobileGuestFieldRadiusInput" type="range" min="0" max="120" step="1" value="18">
	                                            <output id="aaMobileGuestFieldRadiusValue">18</output>
	                                        </span>
	                                    </label>
	                                    <label id="aaMobileGuestFieldRequiredWrap" class="aa-mobile-interaction-check">
	                                        <input id="aaMobileGuestFieldRequiredInput" type="checkbox">
	                                        Wajib diisi
	                                    </label>
	                                    <label id="aaMobileGuestFieldMaxWrap" class="aa-mobile-interaction-field">
	                                        Maksimal karakter
	                                        <input id="aaMobileGuestFieldMaxInput" type="number" min="0" max="1000" step="1" value="0">
	                                    </label>
	                                </div>
		                                <div id="aaMobileGallerySection" class="aa-mobile-interaction-section is-compact">
		                                    <div class="aa-mobile-gallery-actions aa-mobile-interaction-full">
		                                        <button id="aaMobileGalleryUploadBtn" class="aa-panel-btn" type="button">
		                                            <i class="fa fa-upload" aria-hidden="true"></i>Upload foto
	                                        </button>
	                                        <button id="aaMobileGalleryPickMediaBtn" class="aa-panel-btn" type="button">
	                                            <i class="fa fa-images" aria-hidden="true"></i>Pilih media
	                                        </button>
	                                    </div>
	                                    <div id="aaMobileGalleryItemListMount" class="aa-mobile-gallery-list-mount aa-mobile-interaction-full"></div>
	                                    <label class="aa-mobile-interaction-field">
	                                        Columns
	                                        <input id="aaMobileGalleryColumnsInput" type="number" min="1" max="6" step="1" value="2">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field">
	                                        Gap
	                                        <input id="aaMobileGalleryGapInput" type="number" min="0" max="80" step="1" value="14">
	                                    </label>
	                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
	                                        Radius
	                                        <span class="aa-mobile-interaction-range">
	                                            <input id="aaMobileGalleryRadiusInput" type="range" min="0" max="120" step="1" value="18">
		                                            <output id="aaMobileGalleryRadiusValue">18</output>
		                                        </span>
		                                    </label>
		                                </div>
			                                <div id="aaMobileGalleryPhotoSection" class="aa-mobile-interaction-section is-compact">
			                                    <div class="aa-mobile-gallery-actions aa-mobile-interaction-full">
			                                        <button id="aaMobileGalleryPhotoUploadBtn" class="aa-panel-btn" type="button">
			                                            <i class="fa fa-upload" aria-hidden="true"></i>Ganti foto
		                                        </button>
		                                        <button id="aaMobileGalleryPhotoPickMediaBtn" class="aa-panel-btn" type="button">
		                                            <i class="fa fa-images" aria-hidden="true"></i>Pilih media
		                                        </button>
		                                    </div>
		                                    <label class="aa-mobile-interaction-check aa-mobile-interaction-full">
			                                        <input id="aaMobileGalleryPhotoZoomInput" type="checkbox">
			                                        Aktifkan zoom saat publish
			                                    </label>
			                                </div>
			                                <div id="aaMobileImageSection" class="aa-mobile-interaction-section is-compact">
			                                    <div class="aa-mobile-gallery-actions aa-mobile-interaction-full">
			                                        <button id="aaMobileImageUploadBtn" class="aa-panel-btn" type="button">
			                                            <i class="fa fa-upload" aria-hidden="true"></i>Ganti gambar
			                                        </button>
			                                        <button id="aaMobileImagePickMediaBtn" class="aa-panel-btn" type="button">
			                                            <i class="fa fa-images" aria-hidden="true"></i>Pilih media
			                                        </button>
			                                    </div>
			                                    <label class="aa-mobile-interaction-field aa-mobile-interaction-full">
			                                        Radius
			                                        <span class="aa-mobile-interaction-range">
			                                            <input id="aaMobileImageRadiusInput" type="range" min="0" max="240" step="1" value="0">
			                                            <output id="aaMobileImageRadiusValue">0</output>
			                                        </span>
			                                    </label>
			                                </div>
			                                <div id="aaMobileFrameSection" class="aa-mobile-interaction-section is-compact">
			                                    <div class="aa-mobile-gallery-actions aa-mobile-interaction-full">
			                                        <button id="aaMobileFrameUploadBtn" class="aa-panel-btn" type="button">
			                                            <i class="fa fa-upload" aria-hidden="true"></i>Upload foto
			                                        </button>
			                                        <button id="aaMobileFramePickMediaBtn" class="aa-panel-btn" type="button">
			                                            <i class="fa fa-images" aria-hidden="true"></i>Pilih media
			                                        </button>
			                                    </div>
			                                    <div class="aa-mobile-frame-shapes aa-mobile-interaction-full">
			                                        <button type="button" data-aa-mobile-frame-shape="rounded" class="aa-image-frame-option"><span class="aa-frame-preview is-rounded"></span><span>Rounded</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="circle" class="aa-image-frame-option"><span class="aa-frame-preview is-circle"></span><span>Circle</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="arch" class="aa-image-frame-option"><span class="aa-frame-preview is-arch"></span><span>Arch</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="heart" class="aa-image-frame-option"><span class="aa-frame-preview is-heart"></span><span>Love</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="diamond" class="aa-image-frame-option"><span class="aa-frame-preview is-diamond"></span><span>Diamond</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="blob" class="aa-image-frame-option"><span class="aa-frame-preview is-blob"></span><span>Blob</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="ticket" class="aa-image-frame-option"><span class="aa-frame-preview is-ticket"></span><span>Ticket</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="oval" class="aa-image-frame-option"><span class="aa-frame-preview is-oval"></span><span>Oval</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="shield" class="aa-image-frame-option"><span class="aa-frame-preview is-shield"></span><span>Shield</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="hexagon" class="aa-image-frame-option"><span class="aa-frame-preview is-hexagon"></span><span>Hexagon</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="petal" class="aa-image-frame-option"><span class="aa-frame-preview is-petal"></span><span>Petal</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="wave" class="aa-image-frame-option"><span class="aa-frame-preview is-wave"></span><span>Wave</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="tag" class="aa-image-frame-option"><span class="aa-frame-preview is-tag"></span><span>Tag</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="bookmark" class="aa-image-frame-option"><span class="aa-frame-preview is-bookmark"></span><span>Bookmark</span></button>
			                                        <button type="button" data-aa-mobile-frame-shape="scallop" class="aa-image-frame-option"><span class="aa-frame-preview is-scallop"></span><span>Scallop</span></button>
			                                    </div>
			                                </div>
			                            </div>
			                        </div>
			                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="font">
                        <div class="aa-panel-card aa-font-drawer-card">
                            <div class="aa-font-drawer-head">
                                <h2 class="aa-panel-title">Font</h2>
                                <button id="aaFontDrawerCloseBtn" class="aa-font-drawer-close" type="button"
                                    aria-label="Tutup font">
                                    <i class="fa fa-xmark" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="aa-font-drawer-search-wrap">
                                <i class="fa fa-search" aria-hidden="true"></i>
                                <input id="aaFontDrawerSearch" class="aa-font-drawer-search" type="search" placeholder="Cari font...">
                            </div>

                            <div id="aaRecentFontWrap" class="aa-recent-wrap hidden">
                                <div class="aa-recent-title">Recent Font</div>
                                <div id="aaRecentFontList" class="aa-recent-list"></div>
                            </div>                         
                            <div id="aaFontDrawerChips" class="aa-font-drawer-chips" aria-label="Kategori font"></div>
                            <div id="aaFontDrawerList" class="aa-font-drawer-list" aria-live="polite"></div>
                            
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="color">
                        <div class="aa-panel-card aa-color-drawer-card">
                            <div class="aa-font-drawer-head">
                                <h2 class="aa-panel-title">Color</h2>
                                <button id="aaColorDrawerCloseBtn" class="aa-font-drawer-close" type="button"
                                    aria-label="Tutup warna">
                                    <i class="fa fa-xmark" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="aa-color-drawer-grid">
                                <div class="aa-drawer-color-picker">
                                    <div class="aa-drawer-color-title">
                                        <span>Color picker</span>
                                        <span id="aaColorPickerPreviewText">#111827</span>
                                    </div>
                                    <div class="aa-drawer-color-workspace">
                                        <button id="aaColorPickerField" class="aa-drawer-color-field" type="button"
                                            aria-label="Pilih warna">
                                            <span id="aaColorPickerHandle" class="aa-drawer-color-handle"
                                                aria-hidden="true"></span>
                                        </button>
                                        <button id="aaColorPickerHueBar" class="aa-drawer-hue-bar" type="button"
                                            aria-label="Hue warna">
                                            <span id="aaColorPickerHueHandle" class="aa-drawer-hue-handle"
                                                aria-hidden="true"></span>
                                        </button>
                                    </div>
                                    <div class="aa-color-drawer-preview">
                                        <span id="aaColorPickerPreview" class="aa-drawer-color-preview"
                                            aria-hidden="true"></span>
                                        <input id="aaColorDrawerInput" class="aa-drawer-native-color" type="color"
                                            value="#111827" aria-label="Pilih warna">
                                        <button id="aaColorEyedropperBtn" class="aa-color-eyedropper-btn" type="button"
                                            aria-label="Ambil warna dari layar" title="Ambil warna dari layar">
                                            <i class="fa fa-eye-dropper" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <input id="aaColorDrawerHexInput" class="aa-color-drawer-hex" type="text"
                                        value="#111827" maxlength="7" aria-label="Kode warna">
                                </div>
                                <div id="aaColorDrawerAlphaWrap" class="aa-color-drawer-alpha hidden">
                                    <label class="aa-color-drawer-alpha-label" for="aaColorDrawerAlphaInput">
                                        Background opacity
                                    </label>
                                    <div class="aa-color-drawer-row">
                                        <input id="aaColorDrawerAlphaInput" type="range" min="0" max="100" step="1"
                                            value="100" aria-label="Opacity background">
                                        <span id="aaColorDrawerAlphaValue" class="aa-color-drawer-value">100%</span>
                                    </div>
                                    <span id="aaColorDrawerAlphaPreview" class="aa-color-drawer-alpha-preview"
                                        aria-hidden="true"></span>
                                </div>
                                <div id="aaRecentColorWrap" class="aa-recent-wrap hidden">
                                    <div class="aa-recent-title">Recent Color</div>
                                    <div id="aaRecentColorList" class="aa-recent-list aa-recent-color-list"></div>
                                </div>
                                <div id="aaColorMaterialSection" class="aa-color-materials" aria-label="Material warna">
                                    <div class="aa-color-material-group">
                                        <div class="aa-color-material-title">Foil</div>
                                        <div class="aa-color-material-list">
                                            <?php foreach ([
                                                ['gold', 'Gold foil', 'gold'],
                                                ['copper', 'Copper foil', 'copper'],
                                                ['blue', 'Blue foil', 'blue'],
                                                ['pearl', 'Pearl foil', 'pearl'],
                                                ['red', 'Red foil', 'red'],
                                                ['rose', 'Rose foil', 'rose'],
                                                ['silver', 'Silver foil', 'silver'],
                                            ] as $material): ?>
                                            <button class="aa-color-material-swatch is-foil is-<?= esc($material[2], 'attr') ?>"
                                                type="button"
                                                data-aa-material-preset="<?= esc($material[0], 'attr') ?>"
                                                data-aa-material-type="foil"
                                                aria-label="<?= esc($material[1], 'attr') ?>"
                                                title="<?= esc($material[1], 'attr') ?>"></button>
                                            <?php endforeach ?>
                                        </div>
                                    </div>
                                    <div class="aa-color-material-group">
                                        <div class="aa-color-material-title">Glitters</div>
                                        <div class="aa-color-material-list">
                                            <?php foreach ([
                                                ['gold-glitter', 'Gold glitter', 'gold'],
                                                ['silver-glitter', 'Silver glitter', 'silver'],
                                                ['black-glitter', 'Black glitter', 'black'],
                                                ['aqua-glitter', 'Aqua glitter', 'aqua'],
                                                ['emerald-glitter', 'Emerald glitter', 'emerald'],
                                                ['rose-glitter', 'Rose glitter', 'rose'],
                                                ['pink-glitter', 'Pink glitter', 'pink'],
                                                ['purple-glitter', 'Purple glitter', 'purple'],
                                            ] as $material): ?>
                                            <button class="aa-color-material-swatch is-glitter is-<?= esc($material[2], 'attr') ?>"
                                                type="button"
                                                data-aa-material-preset="<?= esc($material[0], 'attr') ?>"
                                                data-aa-material-type="glitter"
                                                aria-label="<?= esc($material[1], 'attr') ?>"
                                                title="<?= esc($material[1], 'attr') ?>"></button>
                                            <?php endforeach ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="aa-color-drawer-swatches" aria-label="Warna cepat">
                                    <?php foreach (['#111827', '#ffffff', '#0f766e', '#14b8a6', '#7c3aed', '#dc2626', '#f59e0b', '#0ea5e9', '#64748b', '#f8fafc', '#000000', '#f43f5e'] as $drawerColor): ?>
                                    <button class="aa-color-drawer-swatch" type="button"
                                        style="--aa-swatch: <?= esc($drawerColor, 'attr') ?>"
                                        data-aa-color-preset="<?= esc($drawerColor, 'attr') ?>"
                                        aria-label="Pilih <?= esc($drawerColor, 'attr') ?>"></button>
                                    <?php endforeach ?>
                                </div>
                                <p id="aaColorDrawerHint" class="aa-canvas-bg-status">
                                    Pilih warna. Perubahan mengikuti input warna yang sedang aktif.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="image-effects">
                        <div class="aa-panel-card aa-image-drawer-card">
                            <div class="aa-font-drawer-head">
                                <h2 class="aa-panel-title">Image Effect</h2>
                                <button class="aa-font-drawer-close" type="button" data-aa-left-close
                                    aria-label="Tutup image effect">
                                    <i class="fa fa-xmark" aria-hidden="true"></i>
                                </button>
                            </div>
                            <p class="aa-canvas-bg-status">Pilih look, adjustment, overlay, atau style untuk foto aktif.</p>
                            <div class="aa-image-effect-section">
                                <p class="aa-image-effect-section-title">Looks</p>
                                <div class="aa-image-effect-grid aa-image-look-grid">
                                    <button type="button" data-aa-image-effect="none" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-none"></span><span>Original</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="soft-wedding" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-soft-wedding"></span><span>Soft Wedding</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="clean-bright" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-clean-bright"></span><span>Clean Bright</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="warm-editorial" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-warm-editorial"></span><span>Warm Editorial</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="film-matte" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-film-matte"></span><span>Film Matte</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="pastel-bloom" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-pastel-bloom"></span><span>Pastel Bloom</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="moody-luxe" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-moody-luxe"></span><span>Moody Luxe</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="classic-bw" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-classic-bw"></span><span>Classic B&W</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="dreamy-soft" class="aa-image-effect-option aa-image-effect-option--look">
                                        <span class="aa-effect-preview is-dreamy-soft"></span><span>Dreamy Soft</span>
                                    </button>
                                </div>
                            </div>
                            <div class="aa-image-effect-section">
                                <p class="aa-image-effect-section-title">Adjust</p>
                                <div class="aa-image-effect-grid aa-image-compact-grid">
                                    <button type="button" data-aa-image-effect="brightness" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-brightness"></span><span>Brightness</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="contrast" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-contrast"></span><span>Contrast</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="saturation" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-saturation"></span><span>Saturation</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="blur" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-blur"></span><span>Blur</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="sharpen" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-sharpen"></span><span>Sharpen</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="remove-color" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-remove-color"></span><span>Remove color</span>
                                    </button>
                                </div>
                            </div>
                            <div class="aa-image-effect-section">
                                <p class="aa-image-effect-section-title">Tone Foto</p>
                                <div class="aa-image-effect-grid aa-image-compact-grid">
                                    <button type="button" data-aa-image-effect="recolor-white" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-recolor-white"></span><span>White Tone</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="recolor-black" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-recolor-black"></span><span>Mono Dark</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="recolor-gold" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-recolor-gold"></span><span>Gold Tone</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="recolor-teal" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-recolor-teal"></span><span>Teal Tone</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="recolor-rose" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-recolor-rose"></span><span>Rose Tone</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="recolor-slate" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-recolor-slate"></span><span>Slate Tone</span>
                                    </button>
                                </div>
                            </div>
                            <div class="aa-image-effect-section">
                                <p class="aa-image-effect-section-title">Overlay</p>
                                <div class="aa-image-effect-grid aa-image-compact-grid">
                                    <button type="button" data-aa-image-overlay="none" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-none"></span><span>None</span>
                                    </button>
                                    <button type="button" data-aa-image-overlay="dark-bottom" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-overlay-dark-bottom"></span><span>Dark bottom</span>
                                    </button>
                                    <button type="button" data-aa-image-overlay="dark-top" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-overlay-dark-top"></span><span>Dark top</span>
                                    </button>
                                    <button type="button" data-aa-image-overlay="vignette" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-overlay-vignette"></span><span>Vignette</span>
                                    </button>
                                    <button type="button" data-aa-image-overlay="gold" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-overlay-gold"></span><span>Gold</span>
                                    </button>
                                    <button type="button" data-aa-image-overlay="rose" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-overlay-rose"></span><span>Rose</span>
                                    </button>
                                    <button type="button" data-aa-image-overlay="ocean" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-overlay-ocean"></span><span>Ocean</span>
                                    </button>
                                    <button type="button" data-aa-image-overlay="slate" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-overlay-slate"></span><span>Slate</span>
                                    </button>
                                </div>
                            </div>
                            <div class="aa-image-effect-section">
                                <p class="aa-image-effect-section-title">Style</p>
                                <div class="aa-image-effect-grid aa-image-compact-grid">
                                    <button type="button" data-aa-image-effect="opacity" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-opacity"></span><span>Opacity</span>
                                    </button>
                                    <button type="button" data-aa-image-effect="shadow" class="aa-image-effect-option">
                                        <span class="aa-effect-preview is-shadow"></span><span>Shadow</span>
                                    </button>
                                    <button type="button" data-aa-image-frame="none" class="aa-image-effect-option">
                                        <span class="aa-frame-preview is-none"></span><span>No frame</span>
                                    </button>
                                    <button type="button" data-aa-image-frame="rounded" class="aa-image-effect-option">
                                        <span class="aa-frame-preview is-rounded"></span><span>Rounded</span>
                                    </button>
                                    <button type="button" data-aa-image-frame="circle" class="aa-image-effect-option">
                                        <span class="aa-frame-preview is-circle"></span><span>Circle</span>
                                    </button>
                                    <button type="button" data-aa-image-frame="arch" class="aa-image-effect-option">
                                        <span class="aa-frame-preview is-arch"></span><span>Arch</span>
                                    </button>
                                </div>
                            </div>
                            <div class="aa-image-effect-reset-row">
                                <button type="button" class="aa-image-effect-reset-btn" data-aa-image-reset-effects>
                                    <i class="fa fa-rotate-left" aria-hidden="true"></i>
                                    <span>Reset Effect</span>
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="image-outline">
                        <div class="aa-panel-card aa-image-drawer-card">
                            <div class="aa-font-drawer-head">
                                <h2 class="aa-panel-title">Image Outline</h2>
                                <button class="aa-font-drawer-close" type="button" data-aa-left-close
                                    aria-label="Tutup image outline">
                                    <i class="fa fa-xmark" aria-hidden="true"></i>
                                </button>
                            </div>
                            <p class="aa-canvas-bg-status">
                                Atur outline untuk gambar. PNG transparan akan mengikuti bentuk objeknya.
                            </p>
                            <div class="aa-outline-color-picker">
                                <div class="aa-outline-color-title">
                                    <span>Warna outline</span>
                                    <span id="aaOutlineColorPreviewText">#FFFFFF</span>
                                </div>
                                <div class="aa-outline-color-workspace">
                                    <button id="aaOutlineColorField" class="aa-outline-color-field" type="button"
                                        aria-label="Pilih warna outline">
                                        <span id="aaOutlineColorHandle" class="aa-outline-color-handle"
                                            aria-hidden="true"></span>
                                    </button>
                                    <input id="aaOutlineHueInput" class="aa-outline-hue-input" type="range" min="0"
                                        max="360" step="1" value="0" aria-label="Hue outline">
                                </div>
                                <div class="aa-outline-color-input-row">
                                    <span id="aaOutlineColorPreview" class="aa-outline-color-preview"
                                        aria-hidden="true"></span>
                                    <input id="aaOutlineColorHexInput" class="aa-outline-color-hex" type="text"
                                        value="#FFFFFF" maxlength="7" aria-label="Kode warna outline">
                                    <input id="aaContextImageOutlineColorInput" class="aa-outline-native-color"
                                        type="color" value="#ffffff" data-aa-skip-color-drawer="1" tabindex="-1"
                                        aria-hidden="true">
                                    <button id="aaOutlineEyedropperBtn" class="aa-color-eyedropper-btn" type="button"
                                        aria-label="Ambil warna outline dari layar" title="Ambil warna dari layar">
                                        <i class="fa fa-eye-dropper" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="aa-outline-color-swatches" aria-label="Warna cepat outline">
                                    <?php foreach (['#ffffff', '#111827', '#0f766e', '#14b8a6', '#7c3aed', '#f59e0b', '#f43f5e', '#0ea5e9'] as $outlineColor): ?>
                                    <button class="aa-outline-color-swatch" type="button"
                                        style="--aa-outline-swatch: <?= esc($outlineColor, 'attr') ?>"
                                        data-aa-outline-color="<?= esc($outlineColor, 'attr') ?>"
                                        aria-label="Pilih <?= esc($outlineColor, 'attr') ?>"></button>
                                    <?php endforeach ?>
                                </div>
                                <div id="aaOutlineRecentColorWrap" class="aa-recent-wrap hidden">
                                    <div class="aa-recent-title">Recent Color</div>
                                    <div id="aaOutlineRecentColorList" class="aa-recent-list aa-recent-color-list"></div>
                                </div>
                            </div>
                            <div class="aa-context-outline-control">
                                <input id="aaContextImageOutlineWidthInput" type="range" min="1" max="60" step="1"
                                    value="12">
                                <output id="aaContextImageOutlineWidthValue" class="aa-context-outline-value">12</output>
                            </div>
                            <div class="aa-context-outline-actions">
                                <button id="aaContextImageOutlineResetBtn" type="button">Reset</button>
                            </div>
                            <p class="aa-context-outline-hint">
                                Pada foto tanpa transparansi, outline mengikuti batas kotak gambar.
                            </p>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="image-frame">
                        <div class="aa-panel-card aa-image-drawer-card">
                            <div class="aa-font-drawer-head">
                                <h2 class="aa-panel-title">Image Frame</h2>
                                <button class="aa-font-drawer-close" type="button" data-aa-left-close
                                    aria-label="Tutup image frame">
                                    <i class="fa fa-xmark" aria-hidden="true"></i>
                                </button>
                            </div>
	                            <p class="aa-canvas-bg-status">Pilih bentuk frame untuk foto aktif. Untuk menambahkan frame kosong baru, buka Assets &gt; Frame Photo.</p>
	                            <div id="aaFramePlaceholderActions" class="aa-frame-placeholder-actions hidden">
	                                <button id="aaFramePlaceholderUploadBtn" class="aa-panel-btn" type="button">
	                                    <i class="fa fa-upload" aria-hidden="true"></i>Upload foto
	                                </button>
	                                <button id="aaFramePlaceholderPickMediaBtn" class="aa-panel-btn" type="button">
	                                    <i class="fa fa-images" aria-hidden="true"></i>Pilih media
	                                </button>
	                            </div>
	                            <div class="aa-image-frame-section">
	                                <p class="aa-image-effect-section-title">Ubah foto aktif</p>
	                            <div class="aa-image-frame-grid">
	                                <button type="button" data-aa-image-frame="none" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-none"></span><span>None</span></button>
                                <button type="button" data-aa-image-frame="rounded" class="aa-image-frame-option"><span
                                        class="aa-frame-preview is-rounded"></span><span>Rounded</span></button>
                                <button type="button" data-aa-image-frame="circle" class="aa-image-frame-option"><span
                                        class="aa-frame-preview is-circle"></span><span>Circle</span></button>
                                <button type="button" data-aa-image-frame="heart" class="aa-image-frame-option"><span
                                        class="aa-frame-preview is-heart"></span><span>Love</span></button>
                                <button type="button" data-aa-image-frame="arch" class="aa-image-frame-option"><span
                                        class="aa-frame-preview is-arch"></span><span>Arch</span></button>
                                <button type="button" data-aa-image-frame="diamond" class="aa-image-frame-option"><span
                                        class="aa-frame-preview is-diamond"></span><span>Diamond</span></button>
                                <button type="button" data-aa-image-frame="blob" class="aa-image-frame-option"><span
                                        class="aa-frame-preview is-blob"></span><span>Blob</span></button>
	                                <button type="button" data-aa-image-frame="ticket" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-ticket"></span><span>Ticket</span></button>
	                                <button type="button" data-aa-image-frame="oval" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-oval"></span><span>Oval</span></button>
	                                <button type="button" data-aa-image-frame="shield" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-shield"></span><span>Shield</span></button>
	                                <button type="button" data-aa-image-frame="hexagon" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-hexagon"></span><span>Hexagon</span></button>
	                                <button type="button" data-aa-image-frame="petal" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-petal"></span><span>Petal</span></button>
	                                <button type="button" data-aa-image-frame="wave" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-wave"></span><span>Wave</span></button>
	                                <button type="button" data-aa-image-frame="tag" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-tag"></span><span>Tag</span></button>
	                                <button type="button" data-aa-image-frame="bookmark" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-bookmark"></span><span>Bookmark</span></button>
	                                <button type="button" data-aa-image-frame="scallop" class="aa-image-frame-option"><span
	                                        class="aa-frame-preview is-scallop"></span><span>Scallop</span></button>
	                            </div>
	                            </div>
	                        </div>
	                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="animation">
                        <div id="aaAnimationPanel" class="aa-panel-card">
                            <div class="aa-font-drawer-head">
                                <h2 class="aa-panel-title">Animation</h2>
                                <button class="aa-font-drawer-close" type="button" data-aa-left-close
                                    aria-label="Tutup animation">
                                    <i class="fa fa-xmark" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="aa-animation-timing-card">
                                <p class="aa-animation-section-title" data-aa-animation-timing-title>Timing</p>
                                <div class="aa-animation-timing-controls" data-aa-animation-timing-wrap>
                                    <label class="aa-animation-timing-field" data-aa-animation-timing-field="delay">
                                        <span>Delay <output data-aa-animation-delay-output>0ms</output></span>
                                        <input data-aa-animation-delay type="range" min="0" max="5000" step="100" value="0">
                                    </label>
                                    <label class="aa-animation-timing-field" data-aa-animation-timing-field="duration">
                                        <span>Durasi <output data-aa-animation-duration-output>700ms</output></span>
                                        <input data-aa-animation-duration type="range" min="200" max="8000" step="100" value="700">
                                    </label>
                                    <label id="aaTextAnimationStaggerControl" class="aa-animation-timing-field" data-aa-animation-timing-field="stagger" hidden>
                                        <span>Stagger <output data-aa-text-animation-stagger-output>40ms</output></span>
                                        <input data-aa-text-animation-stagger type="range" min="0" max="300" step="10" value="40">
                                    </label>
                                </div>
                            </div>
                            <p class="mb-2 text-[11px] font-black uppercase tracking-[.16em] text-slate-400">Entrance</p>
                            <div class="grid grid-cols-3 gap-2">
                                <button data-aa-animation="none" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-ban"></i>None</button>
                                <button data-aa-animation="fade-in" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-circle-half-stroke"></i>Fade</button>
                                <button data-aa-animation="rise" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrow-up"></i>Rise</button>
                                <button data-aa-animation="fade-up" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrow-up"></i>Fade Up</button>
                                <button data-aa-animation="fade-down" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrow-down"></i>Fade Down</button>
                                <button data-aa-animation="fade-left" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrow-left"></i>Fade Left</button>
                                <button data-aa-animation="fade-right" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrow-right"></i>Fade Right</button>
                                <button data-aa-animation="slide-up" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-up-long"></i>Slide Up</button>
                                <button data-aa-animation="slide-down" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-down-long"></i>Slide Down</button>
                                <button data-aa-animation="slide-left" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-left-long"></i>Slide Left</button>
                                <button data-aa-animation="slide-right" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-right-long"></i>Slide Right</button>
                                <button data-aa-animation="zoom-in" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-magnifying-glass-plus"></i>Zoom</button>
                                <button data-aa-animation="zoom-out" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-magnifying-glass-minus"></i>Zoom Out</button>
                                <button data-aa-animation="flip-in" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-retweet"></i>Flip</button>
                                <button data-aa-animation="bounce" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrow-up-long"></i>Bounce</button>
                                <button data-aa-animation="pulse" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-heart-pulse"></i>Pulse</button>
                                <button data-aa-animation="swing" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-rotate"></i>Swing</button>
                                <button data-aa-animation="spin" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrows-rotate"></i>Spin</button>
                            </div>
                            <p class="mb-2 mt-3 text-[11px] font-black uppercase tracking-[.16em] text-slate-400">Loop Terus</p>
                            <div class="grid grid-cols-3 gap-2">
                                <button data-aa-animation="float-loop" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-water"></i>Float</button>
                                <button data-aa-animation="sway-loop" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-wand-magic-sparkles"></i>Sway</button>
                                <button data-aa-animation="pulse-loop" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-heart-pulse"></i>Pulse</button>
                                <button data-aa-animation="spin-loop" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrows-rotate"></i>Spin</button>
                                <button data-aa-animation="heartbeat-loop" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-heart"></i>Beat</button>
                                <button data-aa-animation="drift-loop" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-wind"></i>Drift</button>
                            </div>
                            <div id="aaTextAnimationOptionsSection" hidden>
                                <p class="mb-2 mt-3 text-[11px] font-black uppercase tracking-[.16em] text-slate-400">Text</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button data-aa-text-animation="typewriter" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-keyboard"></i>Typewriter</button>
                                    <button data-aa-text-animation="letter-fade-up" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-arrow-up-a-z"></i>Letter Fade Up</button>
                                    <button data-aa-text-animation="letter-wave" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-water"></i>Letter Wave</button>
                                    <button data-aa-text-animation="word-reveal" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-align-left"></i>Word Reveal</button>
                                    <button data-aa-text-animation="text-glow" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-sun"></i>Text Glow</button>
                                    <button data-aa-text-animation="shine-text" class="aa-panel-btn aa-animation-btn" type="button"><i class="fa fa-wand-magic-sparkles"></i>Shine Text</button>
                                </div>
                            </div>
                            <p class="mt-2 text-[11px] font-bold leading-relaxed text-slate-500">Hover tombol untuk melihat preview di canvas. Klik untuk menyimpan animasi ke elemen.</p>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="templates">
                        <div class="aa-panel-card">
                            <h2 class="aa-panel-title">Templates</h2>
                            <div id="aaTemplateLoadingState" class="aa-panel-loading" role="status" aria-live="polite" hidden>
                                <i aria-hidden="true"></i><span>Memuat templates...</span>
                            </div>
                            <?php $editorTemplates = $editorTemplates ?? []; ?>
                            <?php if (! empty($editorTemplates)): ?>
                            <?php
                                $editorTemplateCategories = [];
                                foreach ($editorTemplates as $templateCategoryItem) {
                                    $categoryKey = (string) ($templateCategoryItem['category_id'] ?? '');
                                    $categoryLabel = (string) ($templateCategoryItem['category_name'] ?? 'Lainnya');
                                    if ($categoryKey === '') {
                                        $categoryKey = 'uncategorized';
                                    }
                                    if (! isset($editorTemplateCategories[$categoryKey])) {
                                        $editorTemplateCategories[$categoryKey] = $categoryLabel !== '' ? $categoryLabel : 'Lainnya';
                                    }
                                }
                                natcasesort($editorTemplateCategories);
                                $templateCategoryIconMap = [
                                    'wedding' => 'fa-heart',
                                    'pernikahan' => 'fa-heart',
                                    'nikah' => 'fa-heart',
                                    'akad' => 'fa-ring',
                                    'birthday' => 'fa-cake-candles',
                                    'ulang' => 'fa-cake-candles',
                                    'islam' => 'fa-mosque',
                                    'khitan' => 'fa-star-and-crescent',
                                    'aqiqah' => 'fa-baby',
                                    'corporate' => 'fa-briefcase',
                                    'seminar' => 'fa-chalkboard-user',
                                    'opening' => 'fa-store',
                                    'wisuda' => 'fa-graduation-cap',
                                    'syukuran' => 'fa-hands-praying',
                                    'minimal' => 'fa-wand-magic-sparkles',
                                    'luxury' => 'fa-gem',
                                ];
                                $templateCategoryPalette = [
                                    ['#ecfeff', '#0891b2'],
                                    ['#fdf2f8', '#db2777'],
                                    ['#fef3c7', '#b45309'],
                                    ['#eef2ff', '#4f46e5'],
                                    ['#ecfdf5', '#059669'],
                                    ['#f5f3ff', '#7c3aed'],
                                    ['#fff7ed', '#ea580c'],
                                    ['#f0f9ff', '#0284c7'],
                                ];
                            ?>
                            <div id="aaTemplateCategoryView" class="aa-template-category-view" hidden>
                                <div id="aaTemplateCategoryChipsLegacy" class="aa-template-category-grid"
                                    aria-label="Kategori template lama">
                                    <?php $templateCategoryIndex = 0; ?>
                                    <?php foreach ($editorTemplateCategories as $categoryKey => $categoryLabel): ?>
                                    <?php
                                        $categoryNeedle = mb_strtolower((string) $categoryLabel . ' ' . (string) $categoryKey);
                                        $categoryIcon = 'fa-layer-group';
                                        foreach ($templateCategoryIconMap as $keyword => $iconClass) {
                                            if (str_contains($categoryNeedle, $keyword)) {
                                                $categoryIcon = $iconClass;
                                                break;
                                            }
                                        }
                                        $palette = $templateCategoryPalette[$templateCategoryIndex % count($templateCategoryPalette)];
                                        $templateCategoryIndex++;
                                    ?>
                                    <button class="aa-template-category-card" type="button"
                                        style="--aa-template-category-bg: <?= esc($palette[0], 'attr') ?>; --aa-template-category-fg: <?= esc($palette[1], 'attr') ?>;"
                                        data-aa-template-category="<?= esc($categoryKey, 'attr') ?>"
                                        data-aa-template-category-label="<?= esc($categoryLabel, 'attr') ?>">
                                        <span class="aa-template-category-icon">
                                            <i class="fa <?= esc($categoryIcon, 'attr') ?>" aria-hidden="true"></i>
                                        </span>
                                        <span class="aa-template-category-name"><?= esc($categoryLabel) ?></span>
                                    </button>
                                    <?php endforeach ?>
                                </div>
                            </div>
                            <div id="aaTemplateListView" class="aa-template-list-view" hidden>
                                <div class="aa-template-list-head">
                                    <button id="aaTemplateBackBtn" class="aa-template-back-btn" type="button"
                                        aria-label="Kembali ke kategori">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                                    </button>
                                    <h3 id="aaTemplateCurrentCategoryTitle" class="aa-template-list-title">Template
                                    </h3>
                                </div>
                                <div class="aa-template-drawer-tools aa-template-discovery-tools">
                                    <label class="aa-editor-asset-search-hero aa-template-search-hero"
                                        for="aaTemplateSearchInput">
                                        <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
                                        <input id="aaTemplateSearchInput" class="aa-template-search-input"
                                            type="search" placeholder="Cari template...">
                                    </label>
                                    <button id="aaTemplateSearchBtn"
                                        class="aa-editor-asset-search-btn aa-template-search-btn" type="button">
                                        Search
                                    </button>
                                </div>
                                <div class="aa-template-chip-wrap" data-aa-template-chip-wrap>
                                    <button class="aa-template-chip-nav" type="button" data-aa-template-chip-scroll="-1"
                                        aria-label="Geser kategori template ke kiri">
                                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                                    </button>
                                    <div id="aaTemplateCategoryChips" class="aa-template-chip-row"
                                        aria-label="Filter kategori template">
                                        <button class="aa-template-filter-chip is-active" type="button"
                                            data-aa-template-category="all" data-aa-template-category-label="Semua">
                                            Semua
                                        </button>
                                        <?php foreach ($editorTemplateCategories as $categoryKey => $categoryLabel): ?>
                                        <button class="aa-template-filter-chip" type="button"
                                            data-aa-template-category="<?= esc($categoryKey, 'attr') ?>"
                                            data-aa-template-category-label="<?= esc($categoryLabel, 'attr') ?>">
                                            <?= esc($categoryLabel) ?>
                                        </button>
                                        <?php endforeach ?>
                                    </div>
                                    <button class="aa-template-chip-nav" type="button" data-aa-template-chip-scroll="1"
                                        aria-label="Geser kategori template ke kanan">
                                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div id="aaTemplateGrid" class="aa-editor-template-grid">
                                    <?php foreach ($editorTemplates as $template): ?>
                                    <?php
                                        $templateId = (int) ($template['id'] ?? 0);
                                        $templateName = (string) ($template['name'] ?? 'Template');
                                        $templateCategoryId = (string) ($template['category_id'] ?? '');
                                        $templateCategoryName = (string) ($template['category_name'] ?? 'Lainnya');
                                        $templateSearch = mb_strtolower($templateName . ' ' . $templateCategoryName);
                                        if (str_contains($templateSearch, 'wedding') || str_contains($templateSearch, 'pernikahan')) {
                                            $templateSearch .= ' pernikahan nikah akad wedding';
                                        }
                                        $thumbnail = (string) ($template['thumbnail'] ?? '');
                                        $thumbnailUrl = $thumbnail !== ''
                                            ? (preg_match('#^https?://#i', $thumbnail) ? $thumbnail : base_url($thumbnail))
                                            : '';
                                        $templatePreviewUrl = trim((string) ($template['preview_url'] ?? ''));
                                        if ($templatePreviewUrl !== '' && ! preg_match('#^https?://#i', $templatePreviewUrl)) {
                                            $templatePreviewUrl = site_url(ltrim($templatePreviewUrl, '/'));
                                        }
                                        $isPremiumTemplate = (int) ($template['is_premium'] ?? 0) === 1;
                                    ?>
                                    <button class="aa-editor-template-card" type="button"
                                        data-aa-template-preview="<?= esc((string) $templateId, 'attr') ?>"
                                        data-aa-template-public-url="<?= esc($templatePreviewUrl, 'attr') ?>"
                                        data-aa-template-premium="<?= $isPremiumTemplate ? '1' : '0' ?>"
                                        data-aa-template-category="<?= esc($templateCategoryId !== '' ? $templateCategoryId : 'uncategorized', 'attr') ?>"
                                        data-aa-template-category-name="<?= esc($templateCategoryName, 'attr') ?>"
                                        data-aa-template-title="<?= esc($templateName, 'attr') ?>"
                                        data-aa-template-search="<?= esc($templateSearch, 'attr') ?>">
                                        <span class="aa-editor-template-thumb">
                                            <?php if ($thumbnailUrl !== ''): ?>
                                            <img src="<?= esc($thumbnailUrl, 'attr') ?>"
                                                alt="<?= esc($templateName, 'attr') ?>" loading="lazy">
                                            <?php endif ?>
                                            <?php if ($isPremiumTemplate): ?>
                                            <span class="aa-editor-template-tier"><?= $premiumCrownSvg ?></span>
                                            <?php endif ?>
                                            <span class="aa-template-card-more" aria-label="Lihat detail template"
                                                data-aa-template-more="<?= esc((string) $templateId, 'attr') ?>">
                                                <i class="fa fa-ellipsis" aria-hidden="true"></i>
                                            </span>
                                        </span>
                                        <span class="aa-editor-template-meta">
                                            <strong><?= esc($templateName) ?></strong>
                                        </span>
                                    </button>
                                    <?php endforeach ?>
                                </div>
                                <div id="aaTemplateEmptyState" class="aa-template-empty-state">Template tidak
                                    ditemukan.
                                </div>
                                <div id="aaTemplateDetailPopover" class="aa-template-detail-popover" hidden></div>
                            </div>
                            <?php else: ?>
                            <div class="aa-gallery-empty">Belum ada template aktif.</div>
                            <?php endif ?>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="elements">
                        <div class="aa-panel-card">
                            <h2 class="aa-panel-title">Elements</h2>
                            <div class="aa-invitation-elements grid gap-4">
                                <div>
                                    <p class="aa-tool-section-title">Teks Undangan</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button id="aaAddHeadingBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="heading" class="aa-lucide-icon" aria-hidden="true"></i>Heading</button>
                                        <button id="aaAddTextBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="type" class="aa-lucide-icon" aria-hidden="true"></i>Text</button>
                                        <button id="aaAddLinkTextBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="link" class="aa-lucide-icon" aria-hidden="true"></i>Link Text</button>
                                        <button id="aaAddCopyTextBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="copy" class="aa-lucide-icon" aria-hidden="true"></i>Copy Text</button>
                                        <button id="aaAddGuestNameTextBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="user-round-check" class="aa-lucide-icon" aria-hidden="true"></i>Nama Tamu</button>
                                    </div>
                                </div>

                                <div>
                                    <p class="aa-tool-section-title">Elemen Interaktif</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button id="aaMusicPlayerBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="music-2" class="aa-lucide-icon" aria-hidden="true"></i>Music</button>
                                        <button id="aaScrollNextBtn" class="aa-tool-btn" type="button"
                                            style="display: none;"><i data-lucide="arrow-down" class="aa-lucide-icon" aria-hidden="true"></i>Scroll
                                            Button</button>
                                        <button id="aaCountdownBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="timer" class="aa-lucide-icon" aria-hidden="true"></i>Countdown</button>
                                        <button id="aaYoutubeVideoBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="square-play" class="aa-lucide-icon" aria-hidden="true"></i>Youtube<?= $premiumCrownSvg ?></button>
	                                        <button id="aaPhotoGalleryBtn" class="aa-tool-btn" type="button"><i
	                                                data-lucide="images" class="aa-lucide-icon" aria-hidden="true"></i>Gallery<?= $premiumCrownSvg ?></button>
	                                        <button id="aaSocialMediaBtn" class="aa-tool-btn" type="button"><i
	                                                data-lucide="share-2" class="aa-lucide-icon" aria-hidden="true"></i>Social Media</button>
                                        <button id="aaStoryMakerBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="book-open-text" class="aa-lucide-icon" aria-hidden="true"></i>Story Maker</button>
                                    </div>
                                </div>

                                <div>
                                    <p class="aa-tool-section-title">Form Buku Tamu</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button id="aaGuestNameBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="user-round" class="aa-lucide-icon" aria-hidden="true"></i>Nama Guestbook</button>
                                        <button id="aaGuestAttendanceBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="list-checks" class="aa-lucide-icon" aria-hidden="true"></i>Kehadiran</button>
                                        <button id="aaGuestMessageBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="message-circle" class="aa-lucide-icon" aria-hidden="true"></i>Komentar</button>
                                        <button id="aaGuestStickerBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="smile-plus" class="aa-lucide-icon" aria-hidden="true"></i>Stiker</button>
                                        <button id="aaGuestSubmitBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="send" class="aa-lucide-icon" aria-hidden="true"></i>Kirim Ucapan</button>
                                        <button id="aaGuestCommentListBtn" class="aa-tool-btn" type="button"><i
                                                data-lucide="messages-square" class="aa-lucide-icon" aria-hidden="true"></i>Comment List</button>
                                    </div>
                                </div>
                            </div>
                            <div id="aaBusinessElementsPanel" class="aa-business-elements">
                                <div id="aaBusinessElementCategoryView" class="aa-business-element-category-view">
                                    <p class="aa-tool-section-title">Khusus Kategori</p>
                                    <div class="aa-business-element-category-list">
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="mua">
                                            <i data-lucide="sparkles" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>MUA</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="wedding_organizer">
                                            <i data-lucide="handshake" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>Wedding Organizer</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="decor">
                                            <i data-lucide="flower-2" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>Dekorasi</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="venue">
                                            <i data-lucide="building-2" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>Venue</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="catering">
                                            <i data-lucide="utensils" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>Catering</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="photographer">
                                            <i data-lucide="camera" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>Photographer</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="freelancer">
                                            <i data-lucide="pen-tool" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>Freelancer</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="umkm">
                                            <i data-lucide="store" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>UMKM</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                        <button class="aa-business-element-category-btn" type="button"
                                            data-aa-business-category="agency">
                                            <i data-lucide="globe-2" class="aa-lucide-icon" aria-hidden="true"></i>
                                            <span>Agency</span>
                                            <i data-lucide="chevron-right" class="aa-lucide-icon" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="aaBusinessElementDetailView" class="aa-business-element-detail-view" hidden>
                                    <button id="aaBusinessElementBackBtn" class="aa-business-element-back-btn" type="button">
                                        <i data-lucide="arrow-left" class="aa-lucide-icon" aria-hidden="true"></i>
                                        Kembali ke kategori elements
                                    </button>
                                    <div class="aa-business-element-detail-head">
                                        <i id="aaBusinessElementCategoryIcon" data-lucide="sparkles"
                                            class="aa-lucide-icon" aria-hidden="true"></i>
                                        <div>
                                            <h3 id="aaBusinessElementCategoryTitle">MUA</h3>
                                            <p id="aaBusinessElementCategoryDescription">Elemen khusus untuk Make Up Artist</p>
                                        </div>
                                    </div>
                                    <div id="aaBusinessElementGrid" class="aa-business-element-grid"></div>
                                    <p id="aaBusinessElementCategoryNote" class="aa-business-element-note"></p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="snippets">
                        <div class="aa-panel-card aa-snippet-card">
                            <h2 class="aa-panel-title">Kalimat</h2>
                            <div class="aa-snippet-search-wrap">
                                <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
                                <input id="aaSnippetSearchInput" type="search" placeholder="Cari kalimat, doa, quotes...">
                            </div>
                            <div id="aaSnippetCategoryList" class="aa-snippet-category-list" aria-label="Kategori kalimat"></div>
                            <div id="aaSnippetList" class="aa-snippet-list" aria-live="polite"></div>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="ornament">
                        <div class="aa-panel-card">
                            <h2 class="aa-panel-title">Assets</h2>
                            <p class="aa-tool-section-title">Asset Bawaan</p>
                            <div class="aa-editor-asset-tools">
                                <?php if (! empty($canManageEditorAssets)): ?>
                                <form id="aaEditorAssetUploadForm" class="aa-editor-asset-admin" autocomplete="off">
                                    <p class="aa-editor-asset-admin-title">Upload Asset Admin</p>
                                    <div class="aa-editor-asset-admin-row">
                                        <select id="aaEditorAssetUploadType" name="type" aria-label="Tipe asset">
                                            <option value="ornament">Ornament</option>
                                            <option value="shape">Shape</option>
                                            <option value="background">Background</option>
                                            <option value="pattern">Pattern</option>
                                        </select>
                                        <select id="aaEditorAssetUploadCategory" name="category" required
                                            aria-label="Kategori asset">
                                            <option value="">Pilih kategori</option>
                                        </select>
                                    </div>
	                                    <input id="aaEditorAssetUploadFile" type="file" name="file" multiple
	                                        accept=".svg,.png,.jpg,.jpeg,.webp,.gif,image/svg+xml,image/png,image/jpeg,image/webp,image/gif">
	                                    <label class="aa-editor-asset-premium-check">
	                                        <input id="aaEditorAssetUploadPremium" type="checkbox" name="is_premium"
	                                            value="1">
	                                        <span><i class="fa fa-crown" aria-hidden="true"></i> Tandai sebagai Premium</span>
	                                    </label>
	                                    <div id="aaEditorAssetUploadState" class="aa-editor-asset-upload-state"
	                                        role="status" aria-live="polite">
                                        <i aria-hidden="true"></i><span></span>
                                    </div>
                                    <button id="aaEditorAssetUploadBtn" class="aa-editor-asset-upload-btn"
                                        type="submit">
                                        Upload Asset
                                    </button>
                                    <p class="aa-editor-asset-admin-note">
                                        Kategori wajib dipilih sebelum upload. User biasa hanya melihat asset yang sudah
                                        diupload admin.
                                    </p>
                                </form>
                                <?php endif; ?>
                            </div>
                            <div id="aaEditorAssetCategoryView" class="aa-template-category-view">
                                <div class="aa-editor-asset-discovery">
                                    <label class="aa-editor-asset-search-hero" for="aaEditorAssetGlobalSearchInput">
                                        <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
                                        <input id="aaEditorAssetGlobalSearchInput" type="search"
                                            placeholder="Cari ornament, floral, frame, gold...">
                                    </label>
                                    <button id="aaEditorAssetGlobalSearchBtn" class="aa-editor-asset-search-btn"
                                        type="button">
                                        Search
                                    </button>
                                    <div class="aa-editor-asset-quick-wrap" data-aa-editor-asset-quick-wrap>
                                        <button class="aa-editor-asset-quick-nav" type="button"
                                            data-aa-editor-asset-quick-scroll="-1"
                                            aria-label="Geser rekomendasi ke kiri">
                                            <i class="fa fa-chevron-left" aria-hidden="true"></i>
                                        </button>
                                        <div id="aaEditorAssetQuickChips" class="aa-editor-asset-quick-chips"
                                            aria-label="Rekomendasi pencarian cepat">
                                            <button type="button" data-aa-editor-asset-query="floral">Floral</button>
                                            <button type="button" data-aa-editor-asset-query="frame">Frame</button>
                                            <button type="button" data-aa-editor-asset-query="divider">Divider</button>
                                            <button type="button" data-aa-editor-asset-query="gold">Gold</button>
                                            <button type="button" data-aa-editor-asset-query="islamic">Islamic</button>
                                            <button type="button" data-aa-editor-asset-query="baby">Baby</button>
                                            <button type="button" data-aa-editor-asset-query="shape">Shape</button>
                                        </div>
                                        <button class="aa-editor-asset-quick-nav" type="button"
                                            data-aa-editor-asset-quick-scroll="1"
                                            aria-label="Geser rekomendasi ke kanan">
                                            <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="aaEditorAssetLandingSections" class="aa-editor-asset-landing-sections"></div>
                                <div class="aa-editor-asset-section-head">
                                    <h3>Browse categories</h3>
                                    <span>Pilih kategori asset</span>
                                </div>
                                <div id="aaEditorAssetCategoryGrid"
                                    class="aa-template-category-grid aa-editor-asset-category-grid"
                                    aria-label="Kategori asset bawaan">
                                    <div class="aa-gallery-empty">Memuat kategori asset...</div>
                                </div>
                            </div>
                            <div id="aaEditorAssetListView" class="aa-template-list-view" hidden>
                                <div class="aa-template-list-head">
                                    <button id="aaEditorAssetBackBtn" class="aa-template-back-btn" type="button"
                                        aria-label="Kembali ke kategori asset">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                                    </button>
                                    <h3 id="aaEditorAssetCurrentCategoryTitle" class="aa-template-list-title">Asset
                                    </h3>
                                </div>
                                <label class="aa-template-search-wrap" for="aaEditorAssetSearchInput">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                    <input id="aaEditorAssetSearchInput" class="aa-template-search-input" type="search"
                                        placeholder="Cari ornament, shape, background...">
                                </label>
                                <div class="aa-editor-asset-type-wrap" data-aa-editor-asset-type-wrap>
                                    <button class="aa-editor-asset-type-nav" type="button"
                                        data-aa-editor-asset-type-scroll="-1" aria-label="Geser filter asset ke kiri">
                                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                                    </button>
                                    <div id="aaEditorAssetTypeChips" class="aa-editor-asset-type-chips"
                                        aria-label="Filter tipe asset">
                                        <button class="aa-editor-asset-type-chip is-active" type="button"
                                            data-aa-editor-asset-type="all">
                                            <i class="fa fa-layer-group" aria-hidden="true"></i><span>Semua</span>
                                        </button>
                                        <button class="aa-editor-asset-type-chip" type="button"
                                            data-aa-editor-asset-type="ornament">
                                            <i class="fa fa-wand-magic-sparkles"
                                                aria-hidden="true"></i><span>Ornament</span>
                                        </button>
                                        <button class="aa-editor-asset-type-chip" type="button"
                                            data-aa-editor-asset-type="shape">
                                            <i class="fa fa-shapes" aria-hidden="true"></i><span>Shape</span>
                                        </button>
                                        <button class="aa-editor-asset-type-chip" type="button"
                                            data-aa-editor-asset-type="background">
                                            <i class="fa fa-image" aria-hidden="true"></i><span>Background</span>
                                        </button>
                                        <button class="aa-editor-asset-type-chip" type="button"
                                            data-aa-editor-asset-type="pattern">
                                            <i class="fa fa-border-all" aria-hidden="true"></i><span>Pattern</span>
                                        </button>
                                        <button class="aa-editor-asset-type-chip" type="button"
                                            data-aa-editor-asset-type="premium">
                                            <i class="fa fa-crown" aria-hidden="true"></i><span>Premium</span>
                                        </button>
                                    </div>
                                    <button class="aa-editor-asset-type-nav" type="button"
                                        data-aa-editor-asset-type-scroll="1" aria-label="Geser filter asset ke kanan">
                                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div id="aaEditorAssetGrid" class="aa-editor-asset-grid">
                                    <div class="aa-gallery-empty">Pilih kategori asset.</div>
                                </div>
                                <button id="aaEditorAssetMoreBtn" class="aa-editor-asset-more" type="button"
                                    hidden>Tampilkan
                                    lagi</button>
                            </div>
                            <div class="aa-editor-quick-elements">
                                <div class="aa-editor-asset-section-head">
                                    <h3>Quick Elements</h3>
                                    <span>Shape & sticker dasar</span>
                                </div>
                                <div class="aa-editor-inline-tool-grid">
                                    <button class="aa-tool-btn" type="button" data-aa-shape="rect"><i
                                            data-lucide="square"
                                            class="aa-lucide-icon" aria-hidden="true"></i>Rectangle</button>
                                    <button class="aa-tool-btn" type="button" data-aa-shape="roundrect"><i
                                            data-lucide="squircle"
                                            class="aa-lucide-icon" aria-hidden="true"></i>Round</button>
                                    <button class="aa-tool-btn" type="button" data-aa-shape="circle"><i
                                            data-lucide="circle"
                                            class="aa-lucide-icon" aria-hidden="true"></i>Circle</button>
                                    <button class="aa-tool-btn" type="button" data-aa-shape="line"><i
                                            data-lucide="minus"
                                            class="aa-lucide-icon" aria-hidden="true"></i>Line</button>
                                    <button class="aa-tool-btn" type="button" data-aa-sticker="flower"><span
                                            aria-hidden="true">✿</span>Flower</button>
                                    <button class="aa-tool-btn" type="button" data-aa-sticker="sparkle"><span
                                            aria-hidden="true">✦</span>Sparkle</button>
                                    <button class="aa-tool-btn" type="button" data-aa-sticker="heart"><span
                                            aria-hidden="true">♡</span>Heart</button>
                                    <button class="aa-tool-btn" type="button" data-aa-sticker="leaf"><span
                                            aria-hidden="true">❧</span>Leaf</button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="aa-left-drawer-panel aa-tool-section" data-aa-left-panel="upload">
                        <div class="aa-panel-card">
                            <h2 class="aa-panel-title">Upload</h2>
                            <div class="mb-3 grid grid-cols-2 gap-2">
                                    <button id="aaAddImageBtn" class="aa-panel-btn" type="button"><i
                                        data-lucide="image-plus" class="aa-lucide-icon" aria-hidden="true"></i>Upload</button>
                                <button id="aaRefreshMediaBtn" class="aa-panel-btn" type="button">Refresh Media</button>
                            </div>
                            <div id="aaMediaUploadState" class="aa-media-upload-state" role="status" aria-live="polite">
                                <i aria-hidden="true"></i>
                                <span>Upload media...</span>
                            </div>
                            <div id="aaMediaBulkBar" class="aa-media-bulk-bar hidden" aria-label="Aksi media terpilih">
                                <label class="aa-media-select-all">
                                    <input id="aaMediaSelectAllInput" type="checkbox">
                                    <span>Pilih semua</span>
                                </label>
                                <button id="aaDeleteSelectedMediaBtn" class="aa-media-bulk-delete" type="button" disabled>
                                    <i class="fa fa-trash-can" aria-hidden="true"></i><span>Trash terpilih</span>
                                </button>
                            </div>
                            <div id="aaMediaGrid" class="aa-media-grid">
                                <div
                                    class="col-span-3 rounded-xl border border-dashed border-slate-300 p-4 text-center text-xs font-bold text-slate-500">
                                    Belum ada media.</div>
                            </div>
                        </div>
                    </section>
                </div>
            </aside>
