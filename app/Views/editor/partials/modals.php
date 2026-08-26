    <?php
        $canUseGuestMemories = ! empty($canUseGuestMemories);
        $isBusinessProfileIntentProject = ! empty($isBusinessProfileIntentProject);
        $hasPhotoboothProjectSurface = ! $isBusinessProfileIntentProject && ! empty($hasPhotoboothProjectSurface);
        $isPurePhotoboothProject = $hasPhotoboothProjectSurface && ! empty($isPurePhotoboothProject);
        $showInvitationPublishChoice = ! $isBusinessProfileIntentProject && ! $isPurePhotoboothProject;
        $showBusinessProfileDisabledChoice = false;
        $businessProfilePaymentReady = ! empty($businessProfilePaymentReady);
        $hasBusinessProfileEntitlement = ! empty($hasBusinessProfileEntitlement);
        $businessProfileCheckoutUrl = (string) ($businessProfileCheckoutUrl ?? '');
        $businessProfilePublishLocked = $isBusinessProfileIntentProject && $businessProfilePaymentReady && ! $hasBusinessProfileEntitlement;
        $publishProductLabel = $isBusinessProfileIntentProject ? 'Business Profile' : 'Undangan';
        $publishChoiceDescription = $isBusinessProfileIntentProject
            ? 'Publish website profil bisnis kamu.'
            : 'Publish undangan digital interaktif kamu.';
        $publishDetailDescription = $isBusinessProfileIntentProject
            ? 'Simpan desain lalu terbitkan business profile ke public URL.'
            : 'Simpan desain lalu terbitkan ke public URL.';
        $publishPreviewLinkLabel = $isBusinessProfileIntentProject ? 'Preview link business profile' : 'Preview link undangan';
        $publishOpenLabel = $isBusinessProfileIntentProject ? 'Cek Business Profile' : 'Cek Undangan';
        $publishCopyLabel = $isBusinessProfileIntentProject ? 'Copy Link Business Profile' : 'Copy Link Undangan';
        $publishDomainPendingCopy = $isBusinessProfileIntentProject
            ? 'Alamat subdomain akan diajukan saat publish. Link business profile utama tetap bisa langsung dipakai.'
            : 'Alamat subdomain akan diajukan saat publish. Link undangan utama tetap bisa langsung dipakai.';
        $photoboothInactiveTitle = 'Fitur Photobooth belum aktif. Minta admin mengaktifkan Guest Memories terlebih dahulu.';
        $publishedDomainOptions = is_array($publishedDomainOptions ?? null) ? $publishedDomainOptions : [];
        $publishedDomain = is_array($publishedDomain ?? null) ? $publishedDomain : [];
        $selectedRootDomain = (string) ($publishedDomain['root_domain'] ?? 'adaacara.com');
        $selectedSubdomain = (string) ($publishedDomain['subdomain'] ?? $pageSlug);
        $publishedDomainStatus = (string) ($publishedDomain['status'] ?? '');
        $publishedDomainStatusLabels = [
            'pending_activation' => 'Menunggu aktivasi',
            'activating' => 'Alamat sedang diaktifkan',
            'active' => 'Website aktif',
            'failed' => 'Aktivasi alamat terkendala',
            'suspended' => 'Alamat dinonaktifkan',
            'disabled' => 'Alamat nonaktif',
        ];
        $publishedDomainStatusLabel = $publishedDomainStatusLabels[$publishedDomainStatus] ?? 'Tersedia setelah dicek saat publish.';
        $publishedDomainStatusClass = match ($publishedDomainStatus) {
            'active' => 'text-emerald-700',
            'failed', 'suspended', 'disabled' => 'text-rose-600',
            'pending_activation', 'activating' => 'text-amber-700',
            default => 'text-emerald-700',
        };
    ?>
    <input id="aaImageInput" class="hidden" type="file" accept="image/png,image/jpeg,image/webp,image/gif" multiple>
    <div id="aaEditorToast" class="aa-editor-toast" role="status" aria-live="polite">
        <span id="aaEditorToastIcon" class="aa-editor-toast-icon"><i class="fa fa-check"></i></span>
        <span class="aa-editor-toast-body">
            <strong id="aaEditorToastTitle">Berhasil</strong>
            <span id="aaEditorToastMessage">Perubahan tersimpan.</span>
        </span>
        <button id="aaEditorToastClose" class="aa-editor-toast-close" type="button" aria-label="Tutup notifikasi">
            <i class="fa fa-xmark"></i>
        </button>
    </div>

    <div id="aaPreviewModal" class="aa-modal">
        <div class="aa-modal-card p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="m-0 text-lg font-black">Preview</h2>
                    <p class="m-0 text-sm font-bold text-slate-500">Preview ini memakai desain terbaru dari canvas.</p>
                </div>
                <button id="aaClosePreviewBtn" class="aa-panel-btn" type="button">Close</button>
            </div>
            <div class="grid place-items-center rounded-2xl bg-slate-100 p-4">
                <iframe id="aaPreviewFrame" class="aa-preview-frame" title="Preview"></iframe>
            </div>
        </div>
    </div>

    <div id="aaPublishModal" class="aa-modal aa-publish-modal">
        <div class="aa-modal-card aa-publish-modal-card">
            <div id="aaPublishChoiceView" class="aa-publish-choice-view">
                <button id="aaClosePublishChoiceBtn" class="aa-publish-close" type="button" aria-label="Tutup">⛌</button>
                <div class="aa-publish-choice-hero">
                    <span class="aa-publish-choice-icon"><i class="fa fa-paper-plane"></i></span>
                    <h2>Publish Website</h2>
                    <p>Pilih jenis website yang ingin kamu publish.</p>
                </div>
                <div class="aa-publish-choice-list">
                    <?php if ($isBusinessProfileIntentProject): ?>
                    <button id="aaPublishChoiceBusinessProfileBtn" class="aa-publish-choice-card<?= $businessProfilePublishLocked ? ' is-premium-locked' : '' ?>" type="button"<?= $businessProfilePublishLocked ? ' data-business-profile-checkout-url="' . esc($businessProfileCheckoutUrl, 'attr') . '" title="Aktifkan Business Profile Rp79.000 untuk publish website ini."' : '' ?>>
                        <span class="aa-publish-choice-card-icon is-invitation"><i class="fa <?= $businessProfilePublishLocked ? 'fa-crown' : 'fa-store' ?>"></i></span>
                        <span class="aa-publish-choice-copy">
                            <strong>Publish Business Profile</strong>
                            <span><?= esc($businessProfilePublishLocked ? 'Aktifkan Rp79.000 untuk publish website Business Profile ini.' : $publishChoiceDescription) ?></span>
                        </span>
                        <i class="fa <?= $businessProfilePublishLocked ? 'fa-crown' : 'fa-chevron-right' ?>"></i>
                    </button>
                    <?php elseif ($showInvitationPublishChoice): ?>
                    <button id="aaPublishChoiceInvitationBtn" class="aa-publish-choice-card" type="button">
                        <span class="aa-publish-choice-card-icon is-invitation"><i class="fa fa-envelope-open-text"></i></span>
                        <span class="aa-publish-choice-copy">
                            <strong>Publish Undangan</strong>
                            <span>Publish undangan digital interaktif kamu.</span>
                        </span>
                        <i class="fa fa-chevron-right"></i>
                    </button>
                    <?php endif ?>
                    <?php if ($hasPhotoboothProjectSurface): ?>
                    <button id="aaPublishChoicePhotoboothBtn" class="aa-publish-choice-card is-photobooth" type="button">
                        <span class="aa-publish-choice-card-icon is-photobooth"><i class="fa fa-camera"></i></span>
                        <span class="aa-publish-choice-copy">
                            <strong>Publish Frame Photobooth</strong>
                            <span>Publish frame photobooth untuk tamu.</span>
                        </span>
                        <i class="fa fa-chevron-right"></i>
                    </button>
                    <?php endif ?>
                    <?php if ($showBusinessProfileDisabledChoice): ?>
                    <button class="aa-publish-choice-card is-disabled" type="button" aria-disabled="true">
                        <span class="aa-publish-choice-card-icon is-disabled"><i class="fa fa-store"></i></span>
                        <span class="aa-publish-choice-copy">
                            <strong>Publish Business Profile</strong>
                            <span>Buat website profil bisnis dari project Business Profile.</span>
                        </span>
                        <i class="fa fa-lock"></i>
                    </button>
                    <?php endif ?>
                </div>
                <p class="aa-publish-choice-note"><i class="fa fa-shield-halved"></i> Semua data aman dan hanya bisa kamu kelola.</p>
            </div>

            <div id="aaPublishDetailView" class="aa-publish-detail-view hidden">
                <div class="aa-publish-modal-head">
                    <button id="aaPublishBackBtn" class="aa-publish-back-btn" type="button" aria-label="Kembali ke pilihan publish">
                        <i class="fa fa-arrow-left"></i>
                    </button>
                    <div class="aa-publish-modal-title">
                        <span class="aa-publish-modal-icon"><i class="fa fa-paper-plane"></i></span>
                        <span>
                            <h2>Publish <?= esc($publishProductLabel) ?></h2>
                            <p><?= esc($publishDetailDescription) ?></p>
                        </span>
                    </div>
                    <button id="aaClosePublishBtn" class="aa-publish-close" type="button" aria-label="Tutup">⛌</button>
                </div>
                <div class="aa-publish-modal-scroll">
                <div class="aa-publish-modal-grid">
                    <section class="aa-publish-section">
                    <label id="aaTemplatePremiumPanel" class="aa-publish-field">
                        <span>Judul halaman</span>
                        <input id="aaPublishTitleInput" class="aa-field" type="text" value="<?= esc($pageTitle, 'attr') ?>" maxlength="60">
                    </label>
                    <label class="aa-publish-field">
                        <span>Slug URL</span>
                        <span class="aa-publish-slug-row">
                            <input id="aaPublishSlugInput" class="aa-field" type="text" value="<?= esc($pageSlug, 'attr') ?>">
                            <span class="aa-publish-dot">.</span>
                            <span class="aa-publish-root-label"><?= esc($selectedRootDomain !== '' ? $selectedRootDomain : 'adaacara.com') ?></span>
                        </span>
                    </label>
                    <p id="aaSlugStatus" class="aa-publish-help">Slug akan dicek sebelum publish.</p>

                    <label class="aa-publish-field">
                        <span>Nama Public Link</span>
                        <input id="aaPublishSubdomainInput" class="aa-field" type="text"
                            value="<?= esc($selectedSubdomain, 'attr') ?>" maxlength="63"
                            placeholder="contoh: nabilaenggarweding">
                    </label>

                    <div class="aa-publish-domain-box">
                        <p class="aa-publish-box-title">Pilih domain</p>
                        <div class="aa-publish-domain-options">
                            <?php foreach ($publishedDomainOptions as $domainOption): ?>
                                <?php
                                    $rootDomain = (string) ($domainOption['root_domain'] ?? '');
                                    $isPremiumDomain = (string) ($domainOption['type'] ?? '') === 'premium';
                                    $isAvailableDomain = ! empty($domainOption['available']);
                                    $isCheckedDomain = $rootDomain === $selectedRootDomain;
                                ?>
                                <label class="aa-publish-domain-option <?= $isAvailableDomain ? '' : 'is-disabled' ?>">
                                    <span class="aa-publish-radio-wrap">
                                        <input type="radio" name="aa_publish_root_domain" value="<?= esc($rootDomain, 'attr') ?>"
                                            <?= $isCheckedDomain ? 'checked' : '' ?> <?= $isAvailableDomain ? '' : 'disabled' ?>>
                                        <span class="aa-publish-domain-globe"><i class="fa fa-globe"></i></span>
                                        <strong><?= esc((string) ($domainOption['label'] ?? $rootDomain)) ?></strong>
                                    </span>
                                    <span class="<?= $isPremiumDomain ? 'aa-publish-premium-pill' : 'aa-publish-free-pill' ?>"><?= $isPremiumDomain ? '<i class="fa fa-crown" aria-hidden="true"></i>' : esc((string) ($domainOption['price_label'] ?? 'GRATIS')) ?></span>
                                </label>
                            <?php endforeach ?>
                        </div>
                        <div class="aa-publish-domain-note">
                            <i class="fa fa-shield-halved"></i>
                            <span>Link utama project yang dapat langsung dibagikan dan diakses oleh publik.</span>
                        </div>
                    </div>

                    <div class="aa-publish-link-card">
                        <p class="aa-publish-box-title"><?= esc($publishPreviewLinkLabel) ?></p>
                        <div class="aa-publish-link-row">
                            <input id="aaPublicUrlInput" class="aa-field" type="text" readonly>
                            <button id="aaPublicUrlCopyBtn" class="aa-publish-inline-copy" type="button">
                                <i class="fa fa-copy"></i> Salin
                            </button>
                        </div>
                        <p class="aa-publish-domain-preview">Link ini langsung aktif setelah publish.</p>
                    </div>

                    <div class="aa-publish-link-card aa-publish-subdomain-card">
                        <p class="aa-publish-box-title">Link Utama</p>
                        <div class="aa-publish-link-row">
                            <input id="aaPublishSubdomainUrlInput" class="aa-field" type="text" readonly value="<?= esc($selectedSubdomain !== '' ? 'https://' . $selectedSubdomain . '.' . $selectedRootDomain : 'https://' . $pageSlug . '.adaacara.com', 'attr') ?>">
                            <button id="aaPublishSubdomainCopyBtn" class="aa-publish-inline-copy" type="button">
                                <i class="fa fa-copy"></i> Salin
                            </button>
                        </div>
                        <p id="aaPublishDomainPreview" class="aa-publish-domain-preview">
                            <?= esc($selectedSubdomain !== '' ? $selectedSubdomain . '.' . $selectedRootDomain : $pageSlug . '.adaacara.com') ?>
                        </p>
                        <p id="aaPublishDomainStatus" class="aa-publish-domain-status <?= esc($publishedDomainStatusClass, 'attr') ?>">
                            <?= esc($publishedDomainStatus !== '' && ! empty($publishedDomain['full_domain'])
                                ? $publishedDomainStatusLabel . '. ' . (string) $publishedDomain['full_domain']
                                : $publishDomainPendingCopy) ?>
                        </p>
                    </div>
                    </section>

                    <section class="aa-publish-section aa-publish-og-section">
                    <div class="aa-og-preview-copy">
                        <p class="m-0 text-sm font-black text-slate-900">Preview WhatsApp <span class="font-semibold text-slate-500">(opsional)</span></p>
                        <p class="m-0 text-sm font-semibold leading-6 text-slate-500">Gambar akan tampil saat kamu membagikan link di WhatsApp.</p>
                    </div>
                    <div class="aa-og-preview-field">
                        <div class="aa-og-preview-box">
                            <div id="aaPublishOgPreview" class="aa-og-preview-thumb">
                                <span>OG</span>
                            </div>
                            <div class="aa-publish-wa-copy">
                                <strong><?= esc($pageTitle !== '' ? $pageTitle : 'AdaAcara') ?></strong>
                                <span id="aaPublishWaUrlText"><?= esc($selectedSubdomain !== '' ? $selectedSubdomain . '.' . $selectedRootDomain : $pageSlug . '.adaacara.com') ?></span>
                                <p>Kami mengundang Anda untuk merayakan momen spesial kami. Lihat detail acara & konfirmasi kehadiran Anda di sini.</p>
                            </div>
                        </div>
                        <div class="aa-og-preview-actions">
                            <label class="aa-panel-btn aa-og-upload-btn" for="aaPublishOgImageInput"><i class="fa fa-crop-simple"></i> Pilih gambar baru</label>
                            <button id="aaPublishOgClearBtn" class="aa-panel-btn" type="button" hidden>Hapus</button>
                            <input id="aaPublishOgImageInput" type="file" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" hidden>
                        </div>
                        <p id="aaPublishOgStatus" class="m-0 text-xs font-bold text-slate-500">PNG, JPG, atau WEBP. Sistem akan crop ke 1200x630.</p>
                    </div>
                    <div class="aa-publish-domain-note aa-publish-tip">
                        <i class="fa fa-lightbulb"></i>
                        <span>Tips: gunakan gambar vertikal rasio 2:3 agar tampil optimal di WhatsApp.</span>
                    </div>
                    </section>
                </div>

                <div class="aa-publish-actions">
                    <div class="aa-publish-main-actions">
                        <button id="aaPublishSaveDraftBtn" class="aa-panel-btn" type="button"><i class="fa fa-floppy-disk"></i> Simpan Draft</button>
                        <a id="aaOpenPublicBtn" class="aa-panel-btn" href="<?= esc(site_url('u/' . $pageSlug), 'attr') ?>" target="_blank" rel="noopener"><i class="fa fa-eye"></i> <?= esc($publishOpenLabel) ?></a>
                    </div>
                    <div class="aa-publish-main-actions">
                        <button id="aaClosePublishSecondaryBtn" class="aa-panel-btn" type="button">Batal</button>
                        <button id="aaConfirmPublishBtn" class="aa-panel-btn aa-publish" type="button"><i class="fa fa-paper-plane"></i> Publish <?= esc($publishProductLabel) ?></button>
                    </div>
                </div>

                <details class="aa-publish-more-actions" open>
                    <summary>Aksi lainnya</summary>
                    <div class="aa-publish-extra-grid">
                        <?php if ($hasPhotoboothProjectSurface): ?>
                        <button id="aaPublishPhotoboothBtn" class="aa-panel-btn<?= $canUseGuestMemories ? '' : ' is-premium-locked' ?>" type="button"<?= $canUseGuestMemories ? '' : ' data-aa-photobooth-premium-gate="1" aria-disabled="true" title="' . esc($photoboothInactiveTitle, 'attr') . '"' ?>><?= $canUseGuestMemories ? '<i class="fa fa-camera"></i> Publish Frame Photobooth' : '<i class="fa fa-crown" aria-hidden="true"></i> Publish Frame Photobooth' ?></button>
                        <a id="aaOpenPhotoboothQrBtn" class="aa-panel-btn<?= $canUseGuestMemories ? '' : ' is-premium-locked' ?>" href="<?= esc($canUseGuestMemories && $pageSlug !== '' ? site_url('u/' . $pageSlug . '/memories/qr') : '#', 'attr') ?>"<?= $canUseGuestMemories ? ' target="_blank" rel="noopener"' : ' data-aa-photobooth-premium-gate="1" aria-disabled="true" title="' . esc($photoboothInactiveTitle, 'attr') . '"' ?>><?= $canUseGuestMemories ? '<i class="fa fa-qrcode"></i> QR Photobooth' : '<i class="fa fa-crown" aria-hidden="true"></i> QR Photobooth' ?></a>
                        <?php endif ?>
                        <button id="aaShareWaBtn" class="aa-panel-btn" type="button"><i class="fa-brands fa-whatsapp"></i> Share WhatsApp</button>
                        <button id="aaCopyLinkBtn" class="aa-panel-btn" type="button"><i class="fa fa-link"></i> <?= esc($publishCopyLabel) ?></button>
                    </div>
                </details>
                <p class="aa-publish-footnote">Pastikan semua informasi sudah benar sebelum mempublish.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="aaPhotoboothDomainModal" class="aa-modal aa-photobooth-domain-modal">
        <div class="aa-modal-card aa-photobooth-domain-card">
            <div class="aa-photobooth-domain-head">
                <button id="aaPhotoboothDomainBackBtn" class="aa-publish-back-btn" type="button" aria-label="Kembali ke pilihan publish">
                    <i class="fa fa-arrow-left"></i>
                </button>
                <div class="aa-photobooth-domain-title">
                    <span class="aa-photobooth-domain-icon"><i class="fa fa-globe"></i></span>
                    <span>
                        <h2 id="aaPhotoboothDomainTitle"><?= esc($pageTitle) ?></h2>
                        <small>Pilih alamat yang akan digunakan untuk project Photobooth ini.</small>
                    </span>
                </div>
                <button class="aa-publish-close" type="button" aria-label="Tutup" data-aa-photobooth-domain-close>⛌</button>
            </div>

            <form id="aaPhotoboothDomainForm" class="aa-photobooth-domain-form">
                <div id="aaPhotoboothDomainOptions" class="aa-photobooth-domain-options">
                    <label class="aa-photobooth-domain-option">
                        <span class="aa-photobooth-domain-choice">
                            <input type="radio" name="domain_mode" value="adaacara" checked>
                            <span class="aa-photobooth-option-icon is-standard"><i class="fa fa-link"></i></span>
                            <span>
                                <strong>Gunakan domain adaAcara.com</strong>
                                <small>Pakai link standar Photobooth yang langsung aktif.</small>
                            </span>
                        </span>
                        <span class="aa-photobooth-option-link-label">Link kamu</span>
                        <span id="aaPhotoboothStandardUrl" class="aa-photobooth-standard-url">
                            <i class="fa fa-link"></i>
                            <span>Memuat...</span>
                        </span>
                    </label>

                    <label class="aa-photobooth-domain-option is-custom">
                        <span class="aa-photobooth-domain-choice">
                            <input type="radio" name="domain_mode" value="custom">
                            <span class="aa-photobooth-option-icon is-custom"><i class="fa fa-globe"></i></span>
                            <span>
                                <strong>Gunakan custom domain</strong>
                                <small>Untuk domain sendiri (.com / .id) tersedia untuk membership.</small>
                            </span>
                        </span>
                        <span class="aa-photobooth-option-link-label">Contoh domain</span>
                        <input id="aaPhotoboothCustomDomainInput" class="aa-field" type="text" name="custom_domain" placeholder="contoh: namaphotobooth.com" autocomplete="off">
                        <span class="aa-photobooth-secure-pill"><i class="fa fa-shield-halved"></i> SSL & koneksi aman disediakan</span>
                    </label>
                </div>

                <div class="aa-photobooth-link-grid">
                    <div class="aa-publish-link-card">
                        <p class="aa-publish-box-title">Preview link Photobooth</p>
                        <div class="aa-publish-link-row">
                            <input id="aaPhotoboothPreviewUrlInput" class="aa-field" type="text" readonly>
                            <button id="aaPhotoboothPreviewUrlCopyBtn" class="aa-publish-inline-copy" type="button">
                                <i class="fa fa-copy"></i> Salin
                            </button>
                        </div>
                        <p class="aa-publish-domain-preview">Link adaAcara ini langsung aktif setelah frame dipublish.</p>
                        <div class="aa-photobooth-ready-link-card">
                            <span class="aa-photobooth-ready-icon"><i class="fa fa-wand-magic-sparkles"></i></span>
                            <span class="aa-photobooth-ready-copy">
                                <strong>Public Link (Siap Dibagikan)</strong>
                                <input id="aaPhotoboothReadyUrlInput" class="aa-photobooth-ready-url" type="text" readonly>
                                <small id="aaPhotoboothReadyUrlStatus" class="aa-photobooth-ready-status">Link undangan utama siap dibagikan.</small>
                            </span>
                            <button id="aaPhotoboothReadyUrlCopyBtn" class="aa-publish-inline-copy is-green" type="button">
                                <i class="fa fa-copy"></i> Salin
                            </button>
                        </div>
                    </div>

                    <div class="aa-publish-link-card">
                        <p class="aa-publish-box-title">Public link custom domain</p>
                        <div class="aa-publish-link-row">
                            <input id="aaPhotoboothPublicUrlInput" class="aa-field" type="text" readonly>
                            <button id="aaPhotoboothPublicUrlCopyBtn" class="aa-publish-inline-copy" type="button">
                                <i class="fa fa-copy"></i> Salin
                            </button>
                        </div>
                        <p id="aaPhotoboothPublicUrlStatus" class="aa-publish-domain-status is-pending">Custom domain bisa dipakai setelah status aktif.</p>
                    </div>
                </div>

                <div id="aaPhotoboothDomainStatusPanel" class="aa-photobooth-status-panel">
                    <div class="aa-photobooth-status-head">
                        <span class="aa-photobooth-status-icon"><i class="fa fa-hourglass-half"></i></span>
                        <div>
                            <p class="aa-photobooth-status-label">Status domain</p>
                            <p id="aaPhotoboothDomainStatus" class="aa-photobooth-status-pill">Domain sedang dicek</p>
                            <p id="aaPhotoboothDomainValue" class="aa-photobooth-domain-value hidden"></p>
                        </div>
                        <span id="aaPhotoboothDomainPrice" class="aa-photobooth-price-pill">Rp250.000 / tahun</span>
                    </div>
                    <p id="aaPhotoboothDomainNote" class="aa-photobooth-status-note">Nama domain yang dipilih akan dicek ketersediaannya oleh admin. Setelah tersedia dan pembayaran dikonfirmasi, domain akan disiapkan dan dihubungkan ke Photobooth.</p>
                </div>

                <div id="aaPhotoboothDomainPaymentPanel" class="hidden rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                    <p class="m-0 text-xs font-black uppercase tracking-[0.16em] text-amber-700">Pembayaran add-on domain</p>
                    <p id="aaPhotoboothPaymentInstruction" class="m-0 mt-2 text-sm font-semibold leading-6 text-slate-700">Transfer add-on custom domain Photobooth sebesar Rp250.000/tahun. Setelah transfer, upload bukti pembayaran di sini agar admin dapat mengonfirmasi dan menyiapkan aktivasi domain.</p>
                    <div class="mt-3 grid gap-2">
                        <p id="aaPhotoboothPaymentOrderStatus" class="m-0 hidden rounded-2xl bg-white px-3 py-2 text-xs font-black leading-5 text-amber-800 ring-1 ring-amber-100"></p>
                        <a id="aaPhotoboothPaymentCheckoutBtn" class="aa-panel-btn aa-primary hidden" href="#" target="_blank" rel="noopener">Lanjut Pembayaran</a>
                        <input id="aaPhotoboothPaymentProofInput" class="aa-field" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <input id="aaPhotoboothPaymentNoteInput" class="aa-field" type="text" maxlength="500" placeholder="Catatan opsional, misalnya nama rekening pengirim">
                        <button id="aaPhotoboothPaymentUploadBtn" class="aa-panel-btn aa-primary" type="button">Upload Bukti Pembayaran</button>
                        <p id="aaPhotoboothPaymentProofStatus" class="m-0 text-xs font-bold text-slate-500"></p>
                    </div>
                </div>

                <div class="aa-photobooth-domain-actions">
                    <button id="aaPhotoboothDomainQrBtn" class="aa-panel-btn" type="button"><i class="fa fa-qrcode"></i> QR Photobooth</button>
                    <button id="aaPhotoboothDomainSubmitBtn" class="aa-panel-btn aa-primary" type="submit"><i class="fa fa-paper-plane"></i> Simpan & Publish Frame</button>
                </div>
                <p class="aa-photobooth-footnote"><i class="fa fa-circle-info"></i> Kamu bisa mengganti pilihan domain nanti di pengaturan Photobooth.</p>
                <p id="aaPhotoboothDomainMessage" class="aa-photobooth-domain-message"></p>
            </form>
        </div>
    </div>

    <?php if (! empty($canSaveTemplate)): ?>
    <div id="aaTemplateModal" class="aa-modal">
        <div class="aa-modal-card flex max-h-[calc(100vh-40px)] max-w-3xl flex-col overflow-hidden p-0">
            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="m-0 text-lg font-black">Save as Template</h2>
                    <p class="m-0 text-sm font-bold text-slate-500">
                        <?= esc($saveTemplateDescription ?? 'Simpan desain Fabric saat ini sebagai reusable template.') ?>
                    </p>
                </div>
                <button id="aaCloseTemplateBtn" class="aa-panel-btn" type="button">Close</button>
            </div>
            <div class="grid min-h-0 flex-1 gap-3 overflow-y-auto px-5 py-4">
                <?php if (! empty($canUpdateSavedTemplate) && ! empty($saveTemplateTargets)): ?>
                <div class="grid grid-cols-2 gap-2 rounded-2xl bg-slate-100 p-1 text-xs font-black text-slate-600">
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm">
                        <input id="aaTemplateModeCreate" name="aa_template_save_mode" type="radio" value="create" checked>
                        Buat baru
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2">
                        <input id="aaTemplateModeUpdate" name="aa_template_save_mode" type="radio" value="update">
                        Update tersimpan
                    </label>
                </div>
                <label id="aaTemplateUpdatePanel" class="hidden grid gap-1 text-xs font-black text-slate-600">
                    Template tersimpan
                    <select id="aaTemplateTargetInput" class="aa-field">
                        <option value="">Pilih template yang akan diupdate</option>
                        <?php foreach (($saveTemplateTargets ?? []) as $target): ?>
                        <option value="<?= esc((string) ($target['id'] ?? ''), 'attr') ?>">
                            <?= esc(($target['name'] ?? 'Template') . ' · ' . ($target['slug'] ?? '') . ' · ' . (($target['review_status'] ?? '') ?: ($target['status'] ?? ''))) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                    <span class="text-[11px] font-bold leading-relaxed text-slate-500">
                        Mode update hanya mengganti isi desain. Slug, kategori, status, premium, dan subkategori tetap mengikuti data template lama.
                    </span>
                </label>
                <?php endif ?>
                <?php
                    $templateCurrentProjectType = strtolower(trim((string) ($page['project_type'] ?? $pageProjectType ?? '')));
                    if (! in_array($templateCurrentProjectType, ['photobooth', 'business_profile'], true)) {
                        $templateCurrentProjectType = 'invitation';
                    }
                    $templateProjectTypeLabels = [
                        'invitation' => 'Undangan Digital',
                        'photobooth' => 'Digital Photobooth',
                        'business_profile' => 'Business Profile',
                    ];
                    $templateProjectCategoryOptions = [
                        'photobooth' => [
                            'digital-photobooth' => 'Digital Photobooth',
                        ],
                        'business_profile' => [
                            'mua' => 'MUA',
                            'wedding-organizer' => 'Wedding Organizer',
                            'dekorasi' => 'Dekorasi',
                            'venue' => 'Venue',
                            'catering' => 'Catering',
                            'photographer' => 'Photographer',
                            'freelancer' => 'Freelancer',
                            'umkm' => 'UMKM',
                            'agency' => 'Agency',
                        ],
                    ];
                    $templateSubcategories = is_array($templateSubcategories ?? null) ? $templateSubcategories : [];
                    $templateSubcategoriesByCategory = [];
                    foreach ($templateSubcategories as $subcategory) {
                        $categoryId = (string) ($subcategory['category_id'] ?? '');
                        if ($categoryId === '') {
                            continue;
                        }
                        $categoryName = trim((string) ($subcategory['category_name'] ?? 'Kategori'));
                        $templateSubcategoriesByCategory[$categoryId]['name'] ??= $categoryName !== '' ? $categoryName : 'Kategori';
                        $templateSubcategoriesByCategory[$categoryId]['items'][] = $subcategory;
                    }
                ?>
                <div id="aaTemplateNewFields" class="grid gap-3">
                    <label class="grid gap-1 text-xs font-black text-slate-600">
                        Nama template
                        <input id="aaTemplateNameInput" class="aa-field" type="text" value="<?= esc($pageTitle, 'attr') ?>">
                    </label>
                    <label class="grid gap-1 text-xs font-black text-slate-600">
                        Slug template
                        <input id="aaTemplateSlugInput" class="aa-field" type="text" value="<?= esc($pageSlug, 'attr') ?>">
                    </label>
                    <label class="grid gap-1 text-xs font-black text-slate-600">
                        Tipe project
                        <select id="aaTemplateProjectTypeInput" class="aa-field" disabled>
                            <?php foreach ($templateProjectTypeLabels as $value => $label): ?>
                            <option value="<?= esc($value, 'attr') ?>" <?= $templateCurrentProjectType === $value ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                            <?php endforeach ?>
                        </select>
                        <span class="text-[11px] font-bold leading-relaxed text-slate-500">
                            Mengikuti project aktif supaya template tersimpan ke katalog yang benar.
                        </span>
                    </label>
                    <label id="aaTemplateProjectCategoryField" class="hidden grid gap-1 text-xs font-black text-slate-600">
                        Kategori project
                        <select id="aaTemplateProjectCategoryInput" class="aa-field">
                            <?php foreach ($templateProjectCategoryOptions as $projectType => $options): ?>
                                <?php foreach ($options as $value => $label): ?>
                                <option value="<?= esc($value, 'attr') ?>" data-aa-template-project-type="<?= esc($projectType, 'attr') ?>">
                                    <?= esc($label) ?>
                                </option>
                                <?php endforeach ?>
                            <?php endforeach ?>
                        </select>
                        <span class="text-[11px] font-bold leading-relaxed text-slate-500">
                            Dipakai untuk filter katalog non-undangan tanpa mengubah kategori undangan lama.
                        </span>
                    </label>
                    <label id="aaTemplateInvitationCategoryField" class="grid gap-1 text-xs font-black text-slate-600">
                        Kategori
                        <select id="aaTemplateCategoryInput" class="aa-field">
                            <option value="">Auto / kategori pertama</option>
                            <?php foreach (($templateCategories ?? []) as $category): ?>
                            <option value="<?= esc((string) ($category['id'] ?? ''), 'attr') ?>">
                                <?= esc($category['name'] ?? 'Kategori') ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>
                    <?php if ($templateSubcategoriesByCategory !== []): ?>
                    <section id="aaTemplateSubcategoryPanel" class="grid gap-2 rounded-2xl border border-slate-200 bg-slate-50/80 p-2.5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="m-0 text-xs font-black text-slate-700">Subkategori Header</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-slate-500">Opsional</span>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <?php foreach ($templateSubcategoriesByCategory as $categoryId => $group): ?>
                            <details class="aa-template-subcategory-group rounded-xl border border-slate-200 bg-white p-0" data-aa-template-subcategory-category="<?= esc((string) $categoryId, 'attr') ?>">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-2.5 py-2 text-[11px] font-black text-slate-700">
                                    <span class="min-w-0">
                                        <span class="block truncate"><?= esc((string) ($group['name'] ?? 'Kategori')) ?></span>
                                        <span class="mt-0.5 block truncate text-[10px] font-bold text-slate-400">
                                            <span data-aa-template-subcategory-count>0 dipilih</span> · <?= count($group['items'] ?? []) ?> opsi
                                        </span>
                                    </span>
                                    <i class="fa fa-chevron-down text-[11px] text-slate-400" aria-hidden="true"></i>
                                </summary>
                                <div class="grid max-h-32 gap-1.5 overflow-y-auto border-t border-slate-100 p-2">
                                    <?php foreach (($group['items'] ?? []) as $subcategory): ?>
                                    <label class="flex min-h-8 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-[11px] font-black text-slate-700 transition hover:border-teal-200 hover:bg-white">
                                        <input class="aa-template-subcategory-input h-3.5 w-3.5 rounded border-slate-300 text-teal-700 focus:ring-teal-500" type="checkbox" value="<?= esc((string) ($subcategory['id'] ?? ''), 'attr') ?>">
                                        <span class="min-w-0">
                                            <span class="block truncate"><?= esc((string) ($subcategory['name'] ?? 'Subkategori')) ?></span>
                                            <?php if (! empty($subcategory['group_title'])): ?>
                                            <span class="mt-0.5 block truncate text-[10px] font-bold text-slate-400"><?= esc((string) $subcategory['group_title']) ?></span>
                                            <?php endif ?>
                                        </span>
                                    </label>
                                    <?php endforeach ?>
                                </div>
                            </details>
                            <?php endforeach ?>
                        </div>
                    </section>
                    <?php endif ?>
                    <label class="grid gap-1 text-xs font-black text-slate-600">
                        Deskripsi
                        <textarea id="aaTemplateDescriptionInput" class="aa-field min-h-20 py-2"
                            rows="3">Template dari editor adaAcara.com</textarea>
                    </label>
                </div>
                <label class="grid gap-1 text-xs font-black text-slate-600">
                    Thumbnail / cover <span id="aaTemplateThumbnailHint" class="text-rose-600">*</span>
                    <input id="aaTemplateThumbnailInput" class="aa-field" type="file"
                        accept="image/jpeg,image/png,image/webp" required>
                    <span class="text-[11px] font-bold leading-relaxed text-slate-500">
                        Wajib saat buat template baru. Saat update, isi hanya jika ingin mengganti cover template lama.
                    </span>
                </label>
                <label class="grid gap-1 text-xs font-black text-slate-600">
                    Berbayar
                    <select id="aaTemplatePremiumInput" class="aa-field">
                        <option value="1">Iya</option>
                        <option value="0">Free</option>
                    </select>
                    <span class="text-[11px] font-bold leading-relaxed text-slate-500">
                        Jika memilih Iya, kamu mendapat komisi 70% dari harga paket BUAT PAKAI SENDIRI saat template
                        dipakai user lain sampai publish.
                    </span>
                </label>
            </div>
            <div class="grid shrink-0 grid-cols-2 gap-2 border-t border-slate-100 bg-white px-5 py-4">
                <button id="aaConfirmTemplateBtn" class="aa-panel-btn aa-primary" type="button">Simpan
                    Template</button>
                <button id="aaCancelTemplateBtn" class="aa-panel-btn" type="button">Batal</button>
            </div>
        </div>
    </div>
    <?php endif ?>

    <div id="aaTemplatePreviewModal" class="aa-modal aa-template-preview-modal">
        <div class="aa-modal-card">
            <div class="aa-template-preview-head">
                <div class="min-w-0">
                    <p class="m-0 text-[11px] font-black uppercase tracking-[.16em] text-teal-600">Preview Template</p>
                    <h2 id="aaTemplatePreviewTitle" class="m-0 truncate text-base font-black text-slate-950">Template
                    </h2>
                </div>
                <button id="aaCloseTemplatePreviewBtn" class="aa-panel-btn !min-h-9 px-3" type="button">Close</button>
            </div>
            <iframe id="aaTemplatePreviewFrame" class="aa-template-preview-frame" title="Preview template"></iframe>
        </div>
    </div>

    <div id="aaExitGuardModal" class="aa-modal">
        <div class="aa-modal-card max-w-md p-5">
            <h2 class="m-0 text-lg font-black text-slate-950">Simpan perubahan?</h2>
            <p class="mt-2 text-sm font-bold leading-relaxed text-slate-500">Ada perubahan yang belum disimpan. Simpan
                perubahan sebelum keluar dari editor.</p>
            <div class="aa-exit-guard-actions mt-5">
                <button id="aaExitGuardSaveBtn" class="aa-panel-btn aa-primary" type="button">Simpan Perubahan</button>
                <button id="aaExitGuardCancelBtn" class="aa-panel-btn" type="button">Cancel</button>
            </div>
        </div>
    </div>

    <div id="editorAccessModal" class="editor-access-modal" hidden>
        <div class="editor-access-backdrop" data-access-modal-close></div>
        <div class="editor-access-card" role="dialog" aria-modal="true" aria-labelledby="editorAccessModalTitle">
            <button type="button" class="editor-access-close" data-access-modal-close aria-label="Tutup">⛌</button>
            <div class="editor-access-icon"><?= $premiumCrownSvg ?></div>
            <h3 id="editorAccessModalTitle">Buka Fitur Premium</h3>
            <p id="editorAccessModalDescription">
                Fitur ini tersedia untuk akun yang sudah login dan memiliki akses paket.
                Kamu tetap bisa melanjutkan desain undanganmu di editor.
            </p>
            <div class="editor-access-actions">
                <a id="editorAccessModalPrimary" href="<?= esc($plansUrl, 'attr') ?>" target="_blank" rel="noopener"
                    class="editor-access-primary">Lihat Paket</a>
                <button type="button" class="editor-access-secondary" data-access-modal-close>Nanti dulu</button>
            </div>
        </div>
    </div>
