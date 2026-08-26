        function bindImportReferenceControls() {
            if (config.importReferenceEnabled !== true) return;
            if (state.__aaImportReferenceBound === true) return;
            state.__aaImportReferenceBound = true;

            const allowedTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
            let selectedFile = null;
            let selectedMeta = null;
            let previewUrl = '';

            function resetImportReferencePreview(options = {}) {
                selectedFile = null;
                selectedMeta = null;
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = '';
                }
                if (els.aaImportReferenceFileInput && options.keepInput !== true) {
                    els.aaImportReferenceFileInput.value = '';
                }
                if (els.aaImportReferencePreviewImage) {
                    els.aaImportReferencePreviewImage.removeAttribute('src');
                }
                if (els.aaImportReferencePreviewMeta) {
                    els.aaImportReferencePreviewMeta.textContent = '';
                }
                els.aaImportReferencePreview?.classList.add('hidden');
            }

            function prepareImportReferencePanel() {
                if (config.importReferenceEnabled !== true) return;
                if (state.editMode === 'opening') {
                    switchEditorMode('pages');
                }
                els.aaImportReferencePageNameInput && (els.aaImportReferencePageNameInput.value =
                    `Referensi ${state.pages.length + 1}`);
            }

            function readImageDimensions(url) {
                return new Promise((resolve, reject) => {
                    const image = new Image();
                    image.onload = () => resolve({
                        width: image.naturalWidth || image.width || 0,
                        height: image.naturalHeight || image.height || 0,
                    });
                    image.onerror = () => reject(new Error('Gambar gagal dibaca.'));
                    image.src = url;
                });
            }

            async function validateImportReferenceFile(file) {
                if (!file) {
                    throw new Error('Pilih gambar referensi terlebih dahulu.');
                }
                if (!allowedTypes.has(file.type || '')) {
                    throw new Error('Format gambar harus JPG, PNG, atau WEBP.');
                }
                if (file.size > Number(config.importReferenceMaxFileSize || 0)) {
                    throw new Error('Ukuran file terlalu besar untuk upload editor.');
                }

                const objectUrl = URL.createObjectURL(file);
                try {
                    const meta = await readImageDimensions(objectUrl);
                    const maxDimension = Number(config.importReferenceMaxDimension || 6000);
                    const maxPixels = Number(config.importReferenceMaxPixels || 24000000);
                    if (!meta.width || !meta.height) {
                        throw new Error('Dimensi gambar tidak valid.');
                    }
                    if (meta.width > maxDimension || meta.height > maxDimension || (meta.width * meta.height) > maxPixels) {
                        throw new Error('Dimensi gambar terlalu besar untuk import.');
                    }
                    return {
                        objectUrl,
                        width: meta.width,
                        height: meta.height,
                    };
                } catch (error) {
                    URL.revokeObjectURL(objectUrl);
                    throw error;
                }
            }

            function pageSizeFromSelection() {
                const value = els.aaImportReferenceSizeInput?.value || 'current';
                if (value === 'portrait') return { width: 1080, height: 1920 };
                if (value === 'tall') return { width: 1080, height: 2280 };
                if (value === 'square') return { width: 1080, height: 1080 };
                return {
                    width: Math.max(1, Number(state.canvas?.getWidth?.()) || 1080),
                    height: Math.max(1, Number(state.canvas?.getHeight?.()) || 1920),
                };
            }

            function uploadImportReferenceFile(file) {
                const form = new FormData();
                form.append('asset', file);
                return fetch(config.importReferenceUploadUrl, {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.success === false) {
                        throw new Error(data.message || 'Upload gambar referensi gagal.');
                    }
                    const item = Array.isArray(data.data) ? data.data[0] : data.data;
                    const src = String(item?.src || '').trim();
                    if (!src) {
                        throw new Error('URL gambar hasil upload tidak ditemukan.');
                    }
                    return src;
                });
            }

            function loadImportReferenceFabricImage(src) {
                return new Promise((resolve, reject) => {
                    fabric.Image.fromURL(src, image => {
                        if (!image) {
                            reject(new Error('Gambar referensi gagal dimuat ke canvas.'));
                            return;
                        }
                        resolve(image);
                    }, {
                        crossOrigin: 'anonymous',
                    });
                });
            }

            function prepareImportReferenceObject(image, src, pageSize, mode) {
                const imageWidth = Math.max(1, image.width || 1);
                const imageHeight = Math.max(1, image.height || 1);
                const scale = mode === 'fit'
                    ? Math.min(pageSize.width / imageWidth, pageSize.height / imageHeight)
                    : Math.max(pageSize.width / imageWidth, pageSize.height / imageHeight);

                image.set({
                    left: pageSize.width / 2,
                    top: pageSize.height / 2,
                    originX: 'center',
                    originY: 'center',
                    scaleX: scale,
                    scaleY: scale,
                    src,
                    crossOrigin: 'anonymous',
                    customType: 'import-reference-background',
                    name: 'Import Referensi',
                    selectable: false,
                    evented: false,
                    excludeFromAnimation: true,
                    locked: true,
                    lockMovementX: true,
                    lockMovementY: true,
                    lockScalingX: true,
                    lockScalingY: true,
                    lockRotation: true,
                    hasControls: false,
                    hoverCursor: 'default',
                    objectCaching: true,
                    aaImportReference: true,
                    aaImportReferenceMode: mode,
                    aaOriginalImageSrc: src,
                    aaOriginalImageName: selectedFile?.name || 'reference-image',
                });
                image.setCoords();
                return image;
            }

            async function createImportReferencePage() {
                if (!selectedFile || !selectedMeta) {
                    if (typeof window.startGeminiAiForCurrentReference === 'function') {
                        await window.startGeminiAiForCurrentReference({
                            button: els.aaImportReferenceCreateBtn,
                            loadingText: 'Membaca...',
                        });
                        return;
                    }
                    setStatus('Pilih gambar referensi terlebih dahulu.', 'error');
                    return;
                }
                if (!state.canvas || !window.fabric) {
                    setStatus('Canvas belum siap.', 'error');
                    return;
                }

                const previousPages = JSON.stringify(state.pages || []);
                const previousActivePageIndex = state.activePageIndex;
                const previousEditMode = state.editMode;
                const createButton = els.aaImportReferenceCreateBtn;
                if (typeof setButtonLoading === 'function') {
                    setButtonLoading(createButton, true, 'Mengupload...');
                }
                setStatus('Mengupload gambar referensi...', 'saving');

                try {
                    if (state.isCropping) {
                        finishCropMode(true);
                    }
                    storeCurrentPage();

                    const src = await uploadImportReferenceFile(selectedFile);
                    const image = await loadImportReferenceFabricImage(src);
                    const pageSize = pageSizeFromSelection();
                    const mode = els.aaImportReferenceModeInput?.value === 'fit' ? 'fit' : 'cover';
                    const pageName = String(els.aaImportReferencePageNameInput?.value || '').trim() ||
                        `Referensi ${state.pages.length + 1}`;
                    const referenceObject = prepareImportReferenceObject(image, src, pageSize, mode);
                    const pageData = createBlankPageData(pageName);
                    pageData.title = pageName;
                    pageData.aaImportReferencePage = true;
                    pageData.artboard = pageSize;
                    pageData.background = '#ffffff';
                    pageData.backgroundColor = '#ffffff';
                    pageData.objects = [referenceObject.toObject(serializedObjectProps)];

                    state.editMode = 'pages';
                    state.pages.push(pageData);
                    state.activePageIndex = state.pages.length - 1;
                    loadPageData(pageData, { preserveZoom: false });
                    if (state.loadPromise && typeof state.loadPromise.then === 'function') {
                        await state.loadPromise;
                    }
                    resetImportReferencePreview();
                    if (typeof window.updateOcrTextUi === 'function') window.updateOcrTextUi();
                    setStatus('Page referensi berhasil dibuat. AdaAcara AI membaca design...', 'saving');
                    if (typeof window.startGeminiAiForCurrentReference === 'function') {
                        await window.startGeminiAiForCurrentReference({
                            button: createButton,
                            loadingText: 'Membaca...',
                            force: true,
                        });
                    }
                } catch (error) {
                    try {
                        state.pages = JSON.parse(previousPages);
                        state.activePageIndex = previousActivePageIndex;
                        state.editMode = previousEditMode === 'opening' ? 'opening' : 'pages';
                        const fallbackPage = state.pages[state.activePageIndex] || createBlankPageData('Halaman 1');
                        loadPageData(fallbackPage, { preserveZoom: true, snapshot: false });
                    } catch (restoreError) {
                        console.error('[AA IMPORT REFERENCE] Restore gagal:', restoreError);
                    }
                    setStatus(error?.message || 'Import referensi gagal.', 'error');
                } finally {
                    if (typeof setButtonLoading === 'function') {
                        setButtonLoading(createButton, false);
                    }
                }
            }

            els.aaImportReferenceTab?.addEventListener('mouseenter', prepareImportReferencePanel);
            els.aaImportReferenceTab?.addEventListener('focus', prepareImportReferencePanel);
            els.aaImportReferenceTab?.addEventListener('click', prepareImportReferencePanel);
            els.aaImportReferenceFileInput?.addEventListener('change', async event => {
                const file = event.target.files?.[0] || null;
                resetImportReferencePreview({ keepInput: true });
                if (!file) return;
                try {
                    const meta = await validateImportReferenceFile(file);
                    selectedFile = file;
                    selectedMeta = meta;
                    previewUrl = meta.objectUrl;
                    if (els.aaImportReferencePreviewImage) {
                        els.aaImportReferencePreviewImage.src = previewUrl;
                    }
                    if (els.aaImportReferencePreviewMeta) {
                        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                        els.aaImportReferencePreviewMeta.textContent =
                            `${meta.width} x ${meta.height}px · ${sizeMb}MB · ${file.type}`;
                    }
                    els.aaImportReferencePreview?.classList.remove('hidden');
                } catch (error) {
                    setStatus(error?.message || 'Gambar referensi tidak valid.', 'error');
                }
            });
            els.aaImportReferenceCreateBtn?.addEventListener('click', createImportReferencePage);
        }
