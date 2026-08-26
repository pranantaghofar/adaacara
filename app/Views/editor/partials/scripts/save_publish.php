        function normalizeProjectPages(data) {
            if (data && data.renderer === 'fabric' && Array.isArray(data.pages) && data.pages.length) {
                return data.pages.map((pageData, index) => ({
                    ...sanitizeFabricPageData(pageData),
                    id: pageData.id || `page-${Date.now()}-${index}`,
                    title: pageData.title || `Halaman ${index + 1}`,
                    artboard: pageData.artboard || {
                        width: 1080,
                        height: 1920
                    },
                    background: pageData.background || pageData.backgroundColor || '#ffffff',
                    backgroundColor: pageData.background || pageData.backgroundColor || '#ffffff',
                    hidden: pageData.hidden === true,
                    aaImportReferencePage: pageData.aaImportReferencePage === true || (pageData.objects || []).some(object => object?.aaImportReference === true),
                    renderer: 'fabric-page',
                }));
            }

            if (data && data.renderer === 'fabric' && Array.isArray(data.objects)) {
                return [{
                    ...sanitizeFabricPageData(data),
                    id: data.id || `page-${Date.now()}`,
                    title: data.title || 'Halaman 1',
                    artboard: data.artboard || {
                        width: 1080,
                        height: 1920
                    },
                    background: data.background || data.backgroundColor || '#ffffff',
                    backgroundColor: data.background || data.backgroundColor || '#ffffff',
                    hidden: data.hidden === true,
                    aaImportReferencePage: data.aaImportReferencePage === true || (data.objects || []).some(object => object?.aaImportReference === true),
                    renderer: 'fabric-page',
                }];
            }

            return [createBlankPageData('Halaman 1')];
        }

        async function postJson(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: payload ? JSON.stringify(payload) : '{}',
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false || data.status === false) {
                const error = new Error(data.message || 'Request gagal.');
                error.data = data;
                throw error;
            }
            return data;
        }

        async function postForm(url, formData) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false || data.status === false) {
                const error = new Error(data.message || 'Request gagal.');
                error.data = data;
                throw error;
            }
            return data;
        }

        function canUsePremiumFeature() {
            return config.canUsePremiumFeatures === true;
        }

        function canUseAiPremiumFeature() {
            return config.canUseAiPremiumFeatures === true;
        }

        function canUseMediaLibrary() {
            return config.isLoggedIn === true;
        }

        function canPublishCurrentPage() {
            return config.canPublishCurrentPage === true;
        }

        function openEditorAccessModal(options = {}) {
            closeToolbarPopovers();
            hideInteractionPopover();
            hideObjectFloatingToolbar();
            hideCountdownContextToolbar();
            const title = options.title || 'Buka Fitur Premium';
            const description = options.description ||
                'Fitur ini tersedia untuk akun yang sudah login dan memiliki akses paket. Kamu tetap bisa melanjutkan desain undanganmu di editor.';
            if (els.editorAccessModalTitle) {
                els.editorAccessModalTitle.textContent = title;
            }
            if (els.editorAccessModalDescription) {
                els.editorAccessModalDescription.textContent = description;
            }
            if (els.editorAccessModalPrimary) {
                els.editorAccessModalPrimary.textContent = options.actionLabel || 'Lihat Paket';
                els.editorAccessModalPrimary.href = options.actionUrl || config.plansUrl || '#';
                if (options.openInSameTab) {
                    els.editorAccessModalPrimary.removeAttribute('target');
                    els.editorAccessModalPrimary.removeAttribute('rel');
                } else {
                    els.editorAccessModalPrimary.target = '_blank';
                    els.editorAccessModalPrimary.rel = 'noopener';
                }
            }
            if (els.editorAccessModal) {
                els.editorAccessModal.hidden = false;
            }
        }

        function openPublishLimitModal() {
            openEditorAccessModal({
                title: 'Limit Publish Paket Tercapai',
                description: 'Paket kamu sudah mencapai batas link publish aktif. Kamu tetap bisa lanjut mengedit draft, atau upgrade paket untuk publish undangan tambahan.',
            });
        }

        function closeEditorAccessModal() {
            if (els.editorAccessModal) {
                els.editorAccessModal.hidden = true;
            }
            requestAnimationFrame(() => {
                syncContextToolbar();
                syncTextContextToolbar();
                syncObjectFloatingToolbar();
            });
        }

        function guardPremiumFeature(event) {
            if (canUsePremiumFeature()) return false;
            event?.preventDefault?.();
            event?.stopPropagation?.();
            openEditorAccessModal();
            return true;
        }

        function guardAiPremiumFeature(event, featureName = 'Fitur AI') {
            if (canUseAiPremiumFeature()) return false;
            event?.preventDefault?.();
            event?.stopPropagation?.();
            openEditorAccessModal({
                title: `${featureName} Premium`,
                description: `${featureName} tersedia untuk akun dengan membership aktif. Creator tetap bisa mengedit desain, tetapi fitur AI premium membutuhkan paket aktif.`,
            });
            return true;
        }

        function guardMediaLibraryFeature(event) {
            if (canUseMediaLibrary()) return false;
            event?.preventDefault?.();
            event?.stopPropagation?.();
            openEditorAccessModal({
                title: 'Login untuk Upload',
                description: 'Silakan login terlebih dahulu untuk upload dan memakai media di undangan kamu.',
                actionLabel: 'Login',
                actionUrl: config.loginUrl,
                openInSameTab: true,
            });
            return true;
        }

        function guardPublishFeature(event) {
            if (config.isLoggedIn !== true) {
                event?.preventDefault?.();
                event?.stopPropagation?.();
                openEditorAccessModal({
                    title: 'Login untuk Publish',
                    description: 'Silakan login terlebih dahulu untuk publish undangan free kamu.',
                    actionLabel: 'Login',
                    actionUrl: config.loginUrl,
                    openInSameTab: true,
                });
                return true;
            }

            if (canPublishCurrentPage()) return false;
            event?.preventDefault?.();
            event?.stopPropagation?.();
            openEditorAccessModal({
                title: 'Upgrade untuk Publish',
                description: 'Template premium membutuhkan paket aktif sebelum bisa dipublish.',
            });
            return true;
        }

        async function waitForCanvasReady() {
            if (state.loadPromise) {
                await state.loadPromise;
            }

            if (state.isRestoring) {
                await new Promise(resolve => setTimeout(resolve, 80));
            }
        }

        function generateDashboardThumbnailDataUrl(pageData) {
            return new Promise(resolve => {
                if (!window.fabric || !pageData) {
                    resolve('');
                    return;
                }

                const previewData = sanitizeFabricPageData(JSON.parse(JSON.stringify(pageData)));
                const width = Math.max(1, Number(previewData.artboard?.width) || 1080);
                const height = Math.max(1, Number(previewData.artboard?.height) || 1920);
                const canvasEl = document.createElement('canvas');
                const preview = new fabric.StaticCanvas(canvasEl, {
                    width,
                    height,
                    renderOnAddRemove: false,
                    enableRetinaScaling: false,
                });
                let settled = false;
                const finish = value => {
                    if (settled) return;
                    settled = true;
                    try {
                        preview.dispose();
                    } catch (disposeError) {}
                    resolve(value || '');
                };
                const timeout = window.setTimeout(() => finish(''), 2500);

                preview.backgroundColor = previewData.background || previewData.backgroundColor || '#ffffff';
                preview.loadFromJSON(previewData, function() {
                    if (settled) return;
                    try {
                        window.clearTimeout(timeout);
                        if (typeof window.aaRestoreCanvasMaterials === 'function') {
                            window.aaRestoreCanvasMaterials(preview);
                        }
                        preview.getObjects().forEach(object => {
                            object.selectable = false;
                            object.evented = false;
                        });
                        preview.renderAll();
                        const multiplier = Math.max(0.08, Math.min(0.36, 420 / width));
                        const dataUrl = preview.toDataURL({
                            format: 'jpeg',
                            quality: 0.72,
                            multiplier,
                        });
                        finish(dataUrl);
                    } catch (error) {
                        window.clearTimeout(timeout);
                        finish('');
                    }
                });
            });
        }

        async function createDashboardThumbnailDataUrl(data) {
            try {
                const pages = Array.isArray(data?.pages) ? data.pages : [];
                const firstPage = pages.find(pageData => pageData && pageData.hidden !== true) || pages[0] || data;
                return await generateDashboardThumbnailDataUrl(firstPage);
            } catch (error) {
                return '';
            }
        }

        function renderPublishOgPreviewImage(url, alt = 'Preview gambar share') {
            if (!els.aaPublishOgPreview || !url) return false;

            els.aaPublishOgPreview.innerHTML = '';
            const image = document.createElement('img');
            image.alt = alt;
            image.src = url;
            els.aaPublishOgPreview.appendChild(image);

            return true;
        }

        function resetPublishOgPreview() {
            state.publishOgImageInvalid = false;
            if (els.aaPublishOgImageInput) els.aaPublishOgImageInput.value = '';
            const existingOgImage = String(config.initialOgImage || '').trim();
            if (existingOgImage && renderPublishOgPreviewImage(existingOgImage, 'Preview gambar share saat ini')) {
                if (els.aaPublishOgClearBtn) els.aaPublishOgClearBtn.hidden = true;
                setPublishOgStatus('Gambar share saat ini sudah tersimpan. Pilih gambar baru jika ingin mengganti.', 'ok');
                return;
            }
            if (els.aaPublishOgPreview) {
                els.aaPublishOgPreview.innerHTML = '<span>OG</span>';
            }
            if (els.aaPublishOgClearBtn) els.aaPublishOgClearBtn.hidden = true;
            if (els.aaPublishOgStatus) {
                els.aaPublishOgStatus.textContent =
                    'Jika kosong, preview link memakai thumbnail otomatis atau fallback AdaAcara.';
                els.aaPublishOgStatus.className = 'm-0 text-xs font-bold text-slate-500';
            }
        }

        function publishOgFile() {
            return els.aaPublishOgImageInput?.files?.[0] || null;
        }

        function setPublishOgStatus(message, type = 'muted') {
            if (!els.aaPublishOgStatus) return;
            const color = type === 'error' ? 'text-rose-600' : (type === 'ok' ? 'text-emerald-700' : 'text-slate-500');
            els.aaPublishOgStatus.textContent = message;
            els.aaPublishOgStatus.className = `m-0 text-xs font-bold ${color}`;
        }

        function validatePublishOgFile(file) {
            return new Promise((resolve, reject) => {
                if (!file) {
                    resolve(null);
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    reject(new Error('Format preview harus JPG, PNG, atau WEBP.'));
                    return;
                }

                if (file.size > 1024 * 1024) {
                    reject(new Error('Ukuran preview maksimal 1 MB.'));
                    return;
                }

                const url = URL.createObjectURL(file);
                const image = new Image();
                image.onload = function() {
                    const width = image.naturalWidth || 0;
                    const height = image.naturalHeight || 0;
                    URL.revokeObjectURL(url);
                    if (width < 600 || height < 315) {
                        reject(new Error('Dimensi preview minimal 600 x 315 px.'));
                        return;
                    }
                    resolve({ width, height });
                };
                image.onerror = function() {
                    URL.revokeObjectURL(url);
                    reject(new Error('Gambar preview tidak bisa dibaca.'));
                };
                image.src = url;
            });
        }

        async function refreshPublishOgPreview(file) {
            if (!file) {
                resetPublishOgPreview();
                return;
            }

            try {
                const meta = await validatePublishOgFile(file);
                state.publishOgImageInvalid = false;
                if (els.aaPublishOgPreview) {
                    const url = URL.createObjectURL(file);
                    renderPublishOgPreviewImage(url);
                    const image = els.aaPublishOgPreview.querySelector('img');
                    image.onload = () => URL.revokeObjectURL(url);
                }
                if (els.aaPublishOgClearBtn) els.aaPublishOgClearBtn.hidden = false;
                setPublishOgStatus(`Siap dipakai. ${meta.width} x ${meta.height}px akan disesuaikan otomatis ke 1200 x 630.`, 'ok');
            } catch (error) {
                state.publishOgImageInvalid = true;
                if (els.aaPublishOgImageInput) els.aaPublishOgImageInput.value = '';
                if (els.aaPublishOgPreview) {
                    els.aaPublishOgPreview.innerHTML = '<span>!</span>';
                }
                if (els.aaPublishOgClearBtn) els.aaPublishOgClearBtn.hidden = false;
                setPublishOgStatus(error.message || 'Gambar preview tidak valid.', 'error');
            }
        }

        async function publishPayloadData(canvasData, thumbnailData) {
            const payload = {
                title: els.aaPublishTitleInput.value.trim() || config.initialTitle,
                slug: normalizeSlug(els.aaPublishSlugInput.value),
                public_subdomain: normalizePublishSubdomain(els.aaPublishSubdomainInput?.value || els.aaPublishSlugInput.value),
                public_root_domain: selectedPublishRootDomain(),
                html: publicHtml(),
                css: publicCss(),
                js: publicJs(),
                editor_json: JSON.stringify(canvasData),
                thumbnail_data: thumbnailData,
            };
            if (state.publishOgImageInvalid === true) {
                throw new Error('Periksa gambar preview link atau hapus pilihan preview sebelum publish.');
            }
            const ogFile = publishOgFile();
            if (!ogFile) return postJson(config.publishUrl, payload);

            await validatePublishOgFile(ogFile);
            const formData = new FormData();
            Object.entries(payload).forEach(([key, value]) => {
                formData.set(key, value == null ? '' : String(value));
            });
            formData.set('og_image_file', ogFile);
            return postForm(config.publishUrl, formData);
        }

        function selectedPublishRootDomain() {
            const checked = document.querySelector('input[name="aa_publish_root_domain"]:checked');
            return String(checked?.value || 'adaacara.com').toLowerCase().trim();
        }

        function normalizePublishSubdomain(value) {
            return normalizeSlug(String(value || '').replace(/\..*$/, '')).replace(/^-+|-+$/g, '').slice(0, 63);
        }

        function publishDomainUrl(slug) {
            const subdomain = normalizePublishSubdomain(els.aaPublishSubdomainInput?.value || slug);
            const rootDomain = selectedPublishRootDomain();
            if (els.aaPublishSubdomainInput) {
                els.aaPublishSubdomainInput.value = subdomain;
            }
            if (els.aaPublishDomainPreview) {
                els.aaPublishDomainPreview.textContent = subdomain ? `${subdomain}.${rootDomain}` : `${slug}.adaacara.com`;
            }
            if (els.aaPublishSubdomainUrlInput) {
                els.aaPublishSubdomainUrlInput.value = subdomain ? `https://${subdomain}.${rootDomain}` : `https://${slug}.adaacara.com`;
            }
            if (els.aaPublishWaUrlText) {
                els.aaPublishWaUrlText.textContent = subdomain ? `${subdomain}.${rootDomain}` : `${slug}.adaacara.com`;
            }
            const savedDomain = config.publishedDomain || {};
            const currentFullDomain = subdomain ? `${subdomain}.${rootDomain}` : '';
            if (savedDomain.full_domain && savedDomain.full_domain === currentFullDomain) {
                renderPublishedDomainStatus(savedDomain);
                return config.publicBaseUrl + slug;
            }
            if (els.aaPublishDomainStatus) {
                const fallbackLabel = (typeof isBusinessProfileProject === 'function' && isBusinessProfileProject())
                    ? 'Link business profile utama'
                    : 'Link undangan utama';
                els.aaPublishDomainStatus.textContent = subdomain ? `Alamat subdomain akan diajukan saat publish. ${fallbackLabel} tetap bisa langsung dipakai.` : 'Nama subdomain wajib diisi.';
                els.aaPublishDomainStatus.className = `m-0 text-xs font-black ${subdomain ? 'text-emerald-700' : 'text-rose-600'}`;
            }

            return config.publicBaseUrl + slug;
        }

        function renderPublishedDomainStatus(domain) {
            if (!domain || !domain.full_domain || !els.aaPublishDomainStatus) return;
            const status = String(domain.status || 'pending_activation');
            const label = String(domain.status_label || (status === 'active' ? 'Website aktif' : 'Menunggu aktivasi'));
            els.aaPublishDomainStatus.textContent = status === 'active'
                ? `${label}. ${domain.full_domain} siap digunakan.`
                : `${label}. Alamat websitemu sedang kami aktifkan. Desain sudah tersimpan dan tidak perlu publish ulang.`;
            els.aaPublishDomainStatus.className =
                `m-0 text-xs font-black ${status === 'active' ? 'text-emerald-700' : status === 'failed' ? 'text-rose-600' : 'text-amber-700'}`;
            if (els.aaPublishDomainPreview) {
                els.aaPublishDomainPreview.textContent = domain.full_domain;
            }
            if (els.aaPublishSubdomainUrlInput) {
                els.aaPublishSubdomainUrlInput.value = domain.url || `https://${domain.full_domain}`;
            }
            if (els.aaPublishWaUrlText) {
                els.aaPublishWaUrlText.textContent = domain.full_domain;
            }
        }

        async function saveDraft(silent) {
            await waitForCanvasReady();
            if (state.__aaOcrReviewActive === true) {
                throw new Error('Selesaikan Review AdaAcara AI terlebih dahulu sebelum menyimpan.');
            }
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            setStatus('Menyimpan...', 'saving');
            const data = getCanvasData();
            const thumbnailData = await createDashboardThumbnailDataUrl(data);
            await postJson(config.saveUrl, {
                html: publicHtml(),
                css: publicCss(),
                js: publicJs(),
                editor_json: JSON.stringify(data),
                thumbnail_data: thumbnailData,
            });
            state.hasUnsavedChanges = false;
            setStatus('Tersimpan');
            if (!silent) showEditorToast('Desain berhasil disimpan sebagai draft.');
        }

        async function checkSlug() {
            const slug = normalizeSlug(els.aaPublishSlugInput.value);
            els.aaPublishSlugInput.value = slug;
            state.publicUrl = publishDomainUrl(slug);
            els.aaPublicUrlInput.value = state.publicUrl;
            els.aaOpenPublicBtn.href = state.publicUrl;
            if (els.aaOpenPhotoboothQrBtn) {
                els.aaOpenPhotoboothQrBtn.href = slug ? `${config.publicBaseUrl}${slug}/memories/qr` : '';
            }

            if (!slug) {
                state.slugAvailable = false;
                els.aaSlugStatus.textContent = 'Slug wajib diisi.';
                els.aaSlugStatus.className = 'm-0 text-xs font-black text-rose-600';
                return false;
            }

            const url =
                `${config.checkSlugUrl}?slug=${encodeURIComponent(slug)}&id=${encodeURIComponent(config.pageId)}`;
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json().catch(() => ({}));
            state.slugAvailable = Boolean(response.ok && data.available);
            els.aaSlugStatus.textContent = data.message || (state.slugAvailable ? 'Slug tersedia.' :
                'Slug tidak tersedia.');
            els.aaSlugStatus.className =
                `m-0 text-xs font-black ${state.slugAvailable ? 'text-emerald-600' : 'text-rose-600'}`;
            return state.slugAvailable;
        }

        async function publish() {
            if (config.isLoggedIn !== true) {
                openEditorAccessModal({
                    title: 'Login untuk Publish',
                    description: 'Silakan login terlebih dahulu untuk publish undangan free kamu.',
                    actionLabel: 'Login',
                    actionUrl: config.loginUrl,
                    openInSameTab: true,
                });
                return;
            }

            if (!canPublishCurrentPage()) {
                openEditorAccessModal({
                    title: 'Upgrade untuk Publish',
                    description: 'Template premium membutuhkan paket aktif sebelum bisa dipublish.',
                });
                return;
            }

            const available = await checkSlug();
            if (!available) throw new Error('Slug belum tersedia.');
            await waitForCanvasReady();
            await saveDraft(true);
            const canvasData = getCanvasData();
            const thumbnailData = await createDashboardThumbnailDataUrl(canvasData);
            let data;
            try {
                data = await publishPayloadData(canvasData, thumbnailData);
            } catch (error) {
                if (error.data && error.data.code === 'login_required') {
                    openEditorAccessModal({
                        title: 'Login untuk Publish',
                        description: error.data.message || 'Silakan login terlebih dahulu untuk publish undangan free kamu.',
                        actionLabel: 'Login',
                        actionUrl: error.data.redirect || config.loginUrl,
                        openInSameTab: true,
                    });
                    return;
                }
                if (error.data && error.data.code === 'publish_limit_reached') {
                    openPublishLimitModal();
                    return;
                }
                if (error.data && error.data.code === 'membership_required') {
                    openEditorAccessModal();
                    return;
                }
                if (error.data && error.data.code === 'business_profile_payment_required') {
                    openEditorAccessModal({
                        title: 'Aktifkan Business Profile',
                        description: error.data.message || 'Aktifkan Business Profile Rp79.000 untuk publish website ini.',
                        actionLabel: 'Aktifkan Business Profile',
                        actionUrl: error.data.checkout_url || error.data.redirect || config.plansUrl,
                        openInSameTab: true,
                    });
                    return;
                }
                throw error;
            }
            if (data && data.code === 'publish_limit_reached') {
                openPublishLimitModal();
                return;
            }
            if (data && data.code === 'login_required') {
                openEditorAccessModal({
                    title: 'Login untuk Publish',
                    description: data.message || 'Silakan login terlebih dahulu untuk publish undangan free kamu.',
                    actionLabel: 'Login',
                    actionUrl: data.redirect || config.loginUrl,
                    openInSameTab: true,
                });
                return;
            }
            if (data && data.code === 'membership_required') {
                openEditorAccessModal();
                return;
            }
            if (data && data.code === 'business_profile_payment_required') {
                openEditorAccessModal({
                    title: 'Aktifkan Business Profile',
                    description: data.message || 'Aktifkan Business Profile Rp79.000 untuk publish website ini.',
                    actionLabel: 'Aktifkan Business Profile',
                    actionUrl: data.checkout_url || data.redirect || config.plansUrl,
                    openInSameTab: true,
                });
                return;
            }
            const publishedSlug = normalizeSlug(els.aaPublishSlugInput?.value || config.initialSlug || config.initialTitle);
            state.publicUrl = publishedSlug ? `${config.publicBaseUrl}${publishedSlug}` : (data.public_url || state.publicUrl);
            els.aaPublicUrlInput.value = state.publicUrl;
            els.aaOpenPublicBtn.href = state.publicUrl;
            const publishedDomain = data.published_domain && !Array.isArray(data.published_domain)
                ? data.published_domain
                : {};
            if (publishedDomain.full_domain) {
                config.publishedDomain = publishedDomain;
                renderPublishedDomainStatus(config.publishedDomain);
            } else {
                config.publishedDomain = {};
                const slug = normalizeSlug(els.aaPublishSlugInput?.value || config.initialSlug || config.initialTitle);
                publishDomainUrl(slug);
                if (els.aaPublishDomainStatus) {
                    els.aaPublishDomainStatus.textContent = (typeof isBusinessProfileProject === 'function' && isBusinessProfileProject())
                        ? 'Business Profile terpublish lewat preview link. Link Utama belum tersimpan, gunakan preview link business profile dulu.'
                        : 'Undangan terpublish lewat preview link. Link Utama belum tersimpan, gunakan preview link undangan dulu.';
                    els.aaPublishDomainStatus.className = 'm-0 text-xs font-black text-amber-700';
                }
            }
            if (data.og_image) {
                config.initialOgImage = data.og_image;
                resetPublishOgPreview();
            }
            state.hasUnsavedChanges = false;
            setStatus('Published');
            showEditorToast(data.message || 'Website berhasil dipublish dan siap dibagikan.');
        }

        function isTemplateUpdateMode() {
            return Boolean(config.canUpdateSavedTemplate && els.aaTemplateModeUpdate?.checked);
        }

        function syncTemplateSaveMode() {
            const updateMode = isTemplateUpdateMode();
            els.aaTemplateUpdatePanel?.classList.toggle('hidden', !updateMode);
            els.aaTemplateNewFields?.classList.toggle('hidden', updateMode);
            els.aaTemplatePremiumPanel?.classList.toggle('hidden', updateMode);
            if ($('aaTemplateThumbnailInput')) {
                $('aaTemplateThumbnailInput').required = !updateMode;
            }
            if (els.aaTemplateThumbnailHint) {
                els.aaTemplateThumbnailHint.textContent = updateMode ? '(opsional)' : '*';
                els.aaTemplateThumbnailHint.classList.toggle('text-slate-400', updateMode);
                els.aaTemplateThumbnailHint.classList.toggle('text-rose-600', !updateMode);
            }
            if ($('aaConfirmTemplateBtn')) {
                $('aaConfirmTemplateBtn').textContent = updateMode ? 'Update Template' : 'Simpan Template';
            }
        }

        function normalizeTemplateProjectType(value) {
            value = String(value || '').toLowerCase().trim();
            if (value === 'photobooth' || value === 'digital_photobooth') return 'photobooth';
            if (value === 'business_profile' || value === 'business-profile') return 'business_profile';
            return 'invitation';
        }

        function currentTemplateProjectType() {
            if (typeof isBusinessProfileProject === 'function' && isBusinessProfileProject()) {
                return 'business_profile';
            }
            if (state.editMode === 'photobooth') {
                return 'photobooth';
            }
            return normalizeTemplateProjectType(config.projectType || state.projectIntent || '');
        }

        function syncTemplateProjectCategoryVisibility() {
            const projectType = currentTemplateProjectType();
            const typeInput = $('aaTemplateProjectTypeInput');
            const projectCategoryField = $('aaTemplateProjectCategoryField');
            const projectCategoryInput = $('aaTemplateProjectCategoryInput');
            const invitationCategoryField = $('aaTemplateInvitationCategoryField');
            const subcategoryPanel = $('aaTemplateSubcategoryPanel');
            const isInvitation = projectType === 'invitation';

            if (typeInput) typeInput.value = projectType;
            invitationCategoryField?.classList.toggle('hidden', !isInvitation);
            projectCategoryField?.classList.toggle('hidden', isInvitation);
            subcategoryPanel?.classList.toggle('hidden', !isInvitation);

            if (projectCategoryInput) {
                let firstVisible = '';
                Array.from(projectCategoryInput.options || []).forEach(option => {
                    const visible = String(option.dataset.aaTemplateProjectType || '') === projectType;
                    option.hidden = !visible;
                    option.disabled = !visible;
                    if (visible && firstVisible === '') firstVisible = option.value;
                });
                if (firstVisible && projectCategoryInput.selectedOptions?.[0]?.disabled) {
                    projectCategoryInput.value = firstVisible;
                }
                if (firstVisible && !projectCategoryInput.value) {
                    projectCategoryInput.value = firstVisible;
                }
            }
        }

        function syncTemplateSubcategoryVisibility() {
            if (currentTemplateProjectType() !== 'invitation') {
                $('aaTemplateSubcategoryPanel')?.classList.add('hidden');
                return;
            }
            const selectedCategory = String(els.aaTemplateCategoryInput?.value || '');
            document.querySelectorAll('.aa-template-subcategory-group').forEach(group => {
                const groupCategory = String(group.dataset.aaTemplateSubcategoryCategory || '');
                const isVisible = selectedCategory === '' || groupCategory === selectedCategory;
                group.classList.toggle('hidden', !isVisible);
                if (isVisible && selectedCategory !== '' && group instanceof HTMLDetailsElement) {
                    group.open = true;
                } else if (!isVisible && group instanceof HTMLDetailsElement) {
                    group.open = false;
                }
            });
            syncTemplateSubcategoryCounts();
        }

        function syncTemplateSubcategoryCounts() {
            document.querySelectorAll('.aa-template-subcategory-group').forEach(group => {
                const count = group.querySelectorAll('.aa-template-subcategory-input:checked').length;
                const output = group.querySelector('[data-aa-template-subcategory-count]');
                if (output) output.textContent = count > 0 ? `${count} dipilih` : '0 dipilih';
            });
        }

        function templateProjectTags(projectType, projectCategory) {
            if (projectType === 'photobooth') {
                return 'photobooth,digital photobooth,frame photobooth';
            }
            if (projectType === 'business_profile') {
                const labels = {
                    'mua': 'MUA',
                    'wedding-organizer': 'Wedding Organizer',
                    'dekorasi': 'Dekorasi',
                    'venue': 'Venue',
                    'catering': 'Catering',
                    'photographer': 'Photographer',
                    'freelancer': 'Freelancer',
                    'umkm': 'UMKM',
                    'agency': 'Agency',
                };
                const label = labels[projectCategory] || 'Business Profile';
                return `business profile,${label},${projectCategory}`;
            }
            return '';
        }

        async function saveAsTemplate() {
            if (!config.canSaveTemplate || !els.aaTemplateModal) return;

            const updateMode = isTemplateUpdateMode();
            const name = els.aaTemplateNameInput.value.trim();
            const slug = normalizeSlug(els.aaTemplateSlugInput.value || name);

            if (updateMode && !config.updateTemplateUrl) {
                throw new Error('Endpoint update template belum tersedia.');
            }

            if (updateMode && !els.aaTemplateTargetInput?.value) {
                throw new Error('Pilih template yang ingin diupdate.');
            }

            if (!updateMode && name.length < 3) {
                throw new Error('Nama template minimal 3 karakter.');
            }

            if (!updateMode && !slug) {
                throw new Error('Slug template tidak valid.');
            }

            if (!updateMode && !$('aaTemplateThumbnailInput')?.files?. [0]) {
                throw new Error('Thumbnail / cover wajib diisi.');
            }

            storeCurrentPage();
            const canvasData = getCanvasData();
            const projectType = currentTemplateProjectType();
            const projectCategory = $('aaTemplateProjectCategoryInput')?.value || '';
            const formData = new FormData();
            if (updateMode) {
                formData.set('template_id', els.aaTemplateTargetInput.value);
            } else {
                formData.set('name', name);
                formData.set('slug', slug);
                formData.set('project_type', projectType);
                formData.set('project_category', projectType === 'invitation' ? '' : projectCategory);
                formData.set('tags', templateProjectTags(projectType, projectCategory));
                if (projectType === 'invitation') {
                    formData.set('category_id', els.aaTemplateCategoryInput.value || '');
                    document.querySelectorAll('.aa-template-subcategory-input:checked').forEach(input => {
                        const group = input.closest('.aa-template-subcategory-group');
                        if (group?.classList.contains('hidden')) return;
                        formData.append('subcategory_ids[]', input.value || '');
                    });
                } else {
                    formData.set('category_id', '');
                }
                formData.set('description', els.aaTemplateDescriptionInput.value ||
                    'Template dari editor adaAcara.com');
                formData.set('is_premium', els.aaTemplatePremiumInput.value || '0');
                formData.set('status', 'active');
            }
            formData.set('source_invitation_id', String(config.pageId || ''));
            formData.set('editor_type', 'fabric');
            formData.set('html', publicHtml());
            formData.set('css', publicCss());
            formData.set('js', publicJs());
            formData.set('editor_json', JSON.stringify(canvasData));
            if ($('aaTemplateThumbnailInput')?.files?. [0]) {
                formData.set('thumbnail', $('aaTemplateThumbnailInput').files[0]);
            }

            setStatus('Menyimpan template...', 'saving');
            const data = await postForm(updateMode ? config.updateTemplateUrl : config.saveTemplateUrl, formData);
            els.aaTemplateModal.classList.remove('is-open');
            setStatus(updateMode ? 'Template diupdate' : 'Template tersimpan');
            showEditorToast(data.message || (updateMode ? 'Template berhasil diupdate.' : 'Template berhasil disimpan.'));
        }
