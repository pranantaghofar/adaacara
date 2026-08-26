        function bindOcrTextControls() {
            if (config.ocrTextEnabled !== true) return;
            if (state.__aaOcrTextBound === true) return;
            state.__aaOcrTextBound = true;

            const REVIEW_TYPE = 'ocr-review-outline';
            let review = null;
            let ocrJob = null;

            function setOcrStatus(message, tone = '') {
                if (els.aaOcrTextStatus) {
                    els.aaOcrTextStatus.textContent = message;
                    els.aaOcrTextStatus.classList.toggle('text-rose-600', tone === 'error');
                    els.aaOcrTextStatus.classList.toggle('text-teal-700', tone === 'success');
                }
            }

            function activeReferencePage() {
                const page = state.pages[state.activePageIndex] || null;
                if (!page || state.editMode === 'opening') return null;
                if (page.aaImportReferencePage === true) return page;
                if ((page.objects || []).some(object => object?.aaImportReference === true)) return page;
                if (state.canvas?.getObjects?.().some(object => object?.aaImportReference === true)) return page;
                return null;
            }

            function referenceImageObject() {
                return state.canvas?.getObjects?.().find(object => object?.aaImportReference === true && object.type === 'image') || null;
            }

            function ocrAvailable() {
                return Boolean(
                    config.ocrTextEnabled === true &&
                    state.canvas &&
                    !review &&
                    !state.isRestoring &&
                    !state.__aaHistoryRestoring &&
                    !state.isCropping &&
                    !els.aaEditorToast?.classList.contains('is-saving') &&
                    activeReferencePage() &&
                    referenceImageObject()
                );
            }

            function ocrPrimaryButton() {
                return els.aaOcrTextDetectBtn || els.aaImportReferenceCreateBtn || null;
            }

            function updateOcrTextUi() {
                const primaryButton = ocrPrimaryButton();
                if (!primaryButton) return;
                const hasReview = Boolean(review || state.__aaOcrReviewActive === true || reviewObjectsFromCanvas().length);
                primaryButton.disabled = Boolean(ocrJob);
                primaryButton.classList.toggle('opacity-60', !ocrAvailable() && !ocrJob && !els.aaImportReferenceFileInput?.files?.length);
                els.aaOcrReviewPanel?.classList.toggle('hidden', !hasReview && !ocrJob);
                if (els.aaOcrApplyBtn) els.aaOcrApplyBtn.disabled = Boolean(ocrJob) || !hasReview;
                if (els.aaOcrCompareBtn) els.aaOcrCompareBtn.disabled = Boolean(ocrJob) || !hasReview;
                if (els.aaOcrReviewOnlyBtn) els.aaOcrReviewOnlyBtn.disabled = Boolean(ocrJob) || !hasReview;
                if (els.aaOcrChangeFontBtn) els.aaOcrChangeFontBtn.disabled = Boolean(ocrJob) || !hasReview;
                if (els.aaOcrCancelBtn) els.aaOcrCancelBtn.disabled = !hasReview && !ocrJob;
                if (review) {
                    setOcrStatus('Hasil AI sudah dibuat. Kamu bisa lanjut edit atau undo jika perlu.', 'success');
                } else if (ocrJob) {
                    setOcrStatus(ocrJob.message || 'AdaAcara AI berjalan...', 'success');
                } else if (ocrAvailable()) {
                    setOcrStatus('Siap menganalisis page referensi dengan AdaAcara AI.', 'success');
                } else {
                    setOcrStatus('Aktif setelah page referensi dibuat.', '');
                }
            }

            window.updateOcrTextUi = updateOcrTextUi;

            function cleanFontFamily(fontFamily) {
                if (typeof cleanFontFamilyValue === 'function') return cleanFontFamilyValue(fontFamily);
                return String(fontFamily || 'Inter').replace(/^["']|["']$/g, '').trim() || 'Inter';
            }

            function fontCatalog() {
                const custom = typeof customFontRegistry !== 'undefined' && Array.isArray(customFontRegistry) ? customFontRegistry : [];
                const defaults = [
                    'Inter', 'Poppins', 'Montserrat', 'Questrial',
                    'Playfair Display', 'Cormorant Garamond', 'Cormorant Infant', 'Cormorant Upright',
                    'Bodoni Moda', 'Libre Baskerville', 'Lora', 'Prata', 'Fraunces', 'Caudex',
                    'Cinzel', 'Marcellus', 'Forum', 'Italiana', 'Bellefair', 'Unna',
                    'DM Serif Display', 'Yeseva One', 'Abril Fatface', 'Elsie',
                    'Great Vibes', 'Dancing Script', 'Allura', 'Alex Brush', 'Parisienne',
                    'Petit Formal Script', 'Imperial Script', 'Italianno', 'Tangerine',
                    'The Nautigal', 'WindSong', 'Ephesis', 'Mea Culpa', 'Fleur De Leah',
                    'Lavishly Yours', 'Bonheur Royale', 'Monsieur La Doulaise', 'Arizonia',
                    'Aboreto', 'Poiret One', 'Viaoda Libre', 'Quintessential', 'Philosopher',
                    'Amarante', 'Amiri', 'Noto Naskh Arabic'
                ];
                const items = [];
                custom.forEach(font => {
                    const family = cleanFontFamily(font?.family || '');
                    if (family) items.push({ family, weights: font?.weights || ['400'] });
                });
                defaults.forEach(family => {
                    if (!items.some(item => item.family === family)) items.push({ family, weights: ['400', '600', '700'] });
                });
                return items;
            }

            function matchFont(block) {
                const fonts = fontCatalog();
                const hint = String(block.styleHint || '').toLowerCase();
                const text = String(block.text || '').trim();
                const looksLikeNames = /&|\bdan\b/i.test(text) || (text.length <= 42 && /^[A-Za-z\s.'-]+$/.test(text));
                const scriptFonts = looksLikeNames
                    ? ['Great Vibes', 'Allura', 'Parisienne', 'Dancing Script', 'Alex Brush', 'Imperial Script', 'Petit Formal Script', 'Tangerine', 'The Nautigal', 'WindSong']
                    : ['Dancing Script', 'Allura', 'Great Vibes', 'Parisienne', 'Alex Brush'];
                const serifFonts = ['Cormorant Garamond', 'Playfair Display', 'Cormorant Infant', 'Bodoni Moda', 'Libre Baskerville', 'Lora', 'Prata', 'Fraunces', 'Caudex', 'Forum', 'Marcellus', 'Bellefair'];
                const displayFonts = ['Cinzel', 'DM Serif Display', 'Yeseva One', 'Abril Fatface', 'Elsie', 'Aboreto', 'Italiana', 'Poiret One', 'Viaoda Libre'];
                const sansFonts = ['Montserrat', 'Poppins', 'Questrial', 'Inter'];
                const preferred = hint.includes('script') || hint.includes('hand') ? scriptFonts :
                    hint.includes('display') ? displayFonts :
                    hint.includes('serif') || hint.includes('elegant') || hint.includes('formal') ? serifFonts :
                    sansFonts;
                const found = preferred.map(cleanFontFamily).find(family => fonts.some(item => item.family === family));
                const fallback = fonts[0]?.family || 'Inter';
                return {
                    family: found || fallback,
                    confidence: found ? 0.78 : 0.45,
                    alternatives: fonts.slice(0, 5).map(item => item.family),
                };
            }

            function validateBlueprint(data) {
                const width = Number(data?.imageWidth || 0);
                const height = Number(data?.imageHeight || 0);
                if (!width || !height || width > 12000 || height > 12000 || !Array.isArray(data?.blocks)) {
                    throw new Error('Respons AdaAcara AI tidak valid.');
                }
                const safeColor = value => /^#[0-9a-f]{6}$/i.test(String(value || '')) ? String(value) : '';
                const normalizeTextBoxX = block => {
                    const rawX = Number(block?.x || 0);
                    const boxWidth = Math.max(0, Number(block?.width || 0));
                    if (boxWidth <= 0) return Math.max(0, rawX);
                    const align = String(block?.align || '').toLowerCase();
                    const looksLikeCenterX = align === 'center' &&
                        rawX > boxWidth * .35 &&
                        rawX + boxWidth > width * .72 &&
                        rawX - boxWidth / 2 >= 0 &&
                        rawX - boxWidth / 2 + boxWidth <= width;
                    return Math.max(0, looksLikeCenterX ? rawX - boxWidth / 2 : rawX);
                };
                const mapRegion = (item, index, prefix) => ({
                    id: `${prefix}-${Date.now()}-${index}`,
                    confidence: Math.max(0, Math.min(1, Number(item?.confidence || 0))),
                    x: Math.max(0, Number(item?.x || 0)),
                    y: Math.max(0, Number(item?.y || 0)),
                    width: Math.max(0, Number(item?.width || 0)),
                    height: Math.max(0, Number(item?.height || 0)),
                    angle: Math.max(-45, Math.min(45, Number(item?.angle || 0))),
                    needsReview: item?.needsReview === true,
                });
                return {
                    imageWidth: width,
                    imageHeight: height,
                    backgroundColor: safeColor(data?.backgroundColor),
                    blocks: data.blocks.map((block, index) => ({
                        id: `ocr-${Date.now()}-${index}`,
                        text: String(block?.text || '').trim().slice(0, 500),
                        confidence: Math.max(0, Math.min(1, Number(block?.confidence || 0))),
                        x: normalizeTextBoxX(block),
                        y: Math.max(0, Number(block?.y || 0)),
                        width: Math.max(0, Number(block?.width || 0)),
                        height: Math.max(0, Number(block?.height || 0)),
                        angle: Math.max(-45, Math.min(45, Number(block?.angle || 0))),
                        color: /^#[0-9a-f]{6}$/i.test(String(block?.color || '')) ? String(block.color) : '#111827',
                        align: ['left', 'center', 'right'].includes(block?.align) ? block.align : 'center',
                        styleHint: String(block?.styleHint || ''),
                        weightHint: String(block?.weightHint || '400'),
                        italic: block?.italic === true,
                        needsReview: block?.needsReview === true,
                        backgroundColor: /^#[0-9a-f]{6}$/i.test(String(block?.backgroundColor || '')) ? String(block.backgroundColor) : '',
                        coverOpacity: Math.max(0, Math.min(1, Number(block?.coverOpacity ?? 0.94))),
                    })).filter(block => block.text && block.width >= 4 && block.height >= 4),
                    frames: Array.isArray(data?.frames) ? data.frames.map((frame, index) => ({
                        id: `ai-frame-${Date.now()}-${index}`,
                        confidence: Math.max(0, Math.min(1, Number(frame?.confidence || 0))),
                        x: Math.max(0, Number(frame?.x || 0)),
                        y: Math.max(0, Number(frame?.y || 0)),
                        width: Math.max(0, Number(frame?.width || 0)),
                        height: Math.max(0, Number(frame?.height || 0)),
                        angle: Math.max(-45, Math.min(45, Number(frame?.angle || 0))),
                        shape: ['rect', 'rounded-rect', 'circle', 'arch'].includes(frame?.shape) ? frame.shape : 'rect',
                        assetSrc: String(frame?.assetSrc || ''),
                        assetName: String(frame?.assetName || 'adaacara-ai-frame.webp'),
                        needsReview: frame?.needsReview === true,
                    })).filter(frame => frame.width >= 12 && frame.height >= 12) : [],
                    decorations: Array.isArray(data?.decorations) ? data.decorations.map((item, index) => ({
                        id: `ai-decoration-${Date.now()}-${index}`,
                        kind: ['flower', 'foliage', 'ornament', 'frame', 'divider', 'illustration', 'logo', 'other'].includes(item?.kind) ? item.kind : 'other',
                        confidence: Math.max(0, Math.min(1, Number(item?.confidence || 0))),
                        x: Math.max(0, Number(item?.x || 0)),
                        y: Math.max(0, Number(item?.y || 0)),
                        width: Math.max(0, Number(item?.width || 0)),
                        height: Math.max(0, Number(item?.height || 0)),
                        angle: Math.max(-45, Math.min(45, Number(item?.angle || 0))),
                        assetSrc: String(item?.assetSrc || ''),
                        assetName: String(item?.assetName || 'adaacara-ai-decoration.webp'),
                        needsReview: item?.needsReview === true,
                        needsBackgroundRemoval: item?.needsBackgroundRemoval === true,
                        backgroundRemoved: item?.backgroundRemoved === true,
                    })).filter(item => item.assetSrc && item.width >= 12 && item.height >= 12) : [],
                    photos: Array.isArray(data?.photos) ? data.photos.map((photo, index) => ({
                        ...mapRegion(photo, index, 'ai-photo'),
                        shape: ['rect', 'rounded-rect', 'circle', 'oval', 'arch'].includes(photo?.shape) ? photo.shape : 'rect',
                        assetSrc: String(photo?.assetSrc || ''),
                        assetName: String(photo?.assetName || 'adaacara-ai-photo.webp'),
                    })).filter(photo => photo.assetSrc && photo.width >= 12 && photo.height >= 12) : [],
                    shapes: Array.isArray(data?.shapes) ? data.shapes.map((shape, index) => ({
                        ...mapRegion(shape, index, 'ai-shape'),
                        kind: ['rect', 'rounded-rect', 'circle', 'oval', 'polygon', 'line', 'divider'].includes(shape?.kind) ? shape.kind : 'rect',
                        fill: safeColor(shape?.fill) || 'rgba(0,0,0,0)',
                        stroke: safeColor(shape?.stroke) || '',
                        strokeWidth: Math.max(0, Math.min(24, Number(shape?.strokeWidth || 0))),
                        opacity: Math.max(0.05, Math.min(1, Number(shape?.opacity || 1))),
                        points: Array.isArray(shape?.points) ? shape.points.map(point => ({
                            x: Math.max(0, Number(point?.x || 0)),
                            y: Math.max(0, Number(point?.y || 0)),
                        })).filter(point => Number.isFinite(point.x) && Number.isFinite(point.y)).slice(0, 12) : [],
                    })).filter(shape => shape.width >= 2 && shape.height >= 2) : [],
                    canvasOverlay: data?.canvasOverlay?.enabled === true && data?.canvasOverlay?.assetSrc ? {
                        enabled: true,
                        confidence: Math.max(0, Math.min(1, Number(data.canvasOverlay.confidence || 0.84))),
                        x: Math.max(0, Number(data.canvasOverlay.x || 0)),
                        y: Math.max(0, Number(data.canvasOverlay.y || 0)),
                        width: Math.max(0, Number(data.canvasOverlay.width || width)),
                        height: Math.max(0, Number(data.canvasOverlay.height || height)),
                        assetSrc: String(data.canvasOverlay.assetSrc || ''),
                        assetName: String(data.canvasOverlay.assetName || 'adaacara-ai-overlay.png'),
                        needsReview: data.canvasOverlay.needsReview === true,
                    } : null,
                };
            }

            function ensureAcaraAiAttachmentInBlueprint(blueprint, attachmentSrc) {
                const src = String(attachmentSrc || '').trim();
                if (!src || !blueprint || typeof blueprint !== 'object') return blueprint;

                const sameSrc = value => String(value || '').trim() === src;
                const visualItems = [
                    blueprint.canvasOverlay,
                    ...(Array.isArray(blueprint.photos) ? blueprint.photos : []),
                    ...(Array.isArray(blueprint.decorations) ? blueprint.decorations : []),
                    ...(Array.isArray(blueprint.frames) ? blueprint.frames : []),
                ].filter(Boolean);

                const alreadyUsed = visualItems.some(item => sameSrc(item.assetSrc));
                if (alreadyUsed || blueprint.canvasOverlay) return blueprint;

                const width = Math.max(320, Number(blueprint.imageWidth || 1080));
                const height = Math.max(320, Number(blueprint.imageHeight || 1920));
                const hasTextOrDesign = Boolean(
                    (Array.isArray(blueprint.blocks) && blueprint.blocks.length) ||
                    (Array.isArray(blueprint.shapes) && blueprint.shapes.length) ||
                    (Array.isArray(blueprint.decorations) && blueprint.decorations.length) ||
                    (Array.isArray(blueprint.frames) && blueprint.frames.length)
                );
                const photoWidth = Math.round(width * 0.84);
                const photoHeight = Math.round(height * (hasTextOrDesign ? 0.42 : 0.72));
                const photoY = Math.round(height * (hasTextOrDesign ? 0.09 : 0.14));

                blueprint.photos = Array.isArray(blueprint.photos) ? blueprint.photos : [];
                blueprint.photos.unshift({
                    id: `acara-ai-attachment-${Date.now()}`,
                    confidence: 0.92,
                    x: Math.round((width - photoWidth) / 2),
                    y: photoY,
                    width: photoWidth,
                    height: photoHeight,
                    angle: 0,
                    shape: 'rounded-rect',
                    assetSrc: src,
                    assetName: 'acara-ai-attachment',
                    needsReview: false,
                });

                return blueprint;
            }

            function logOcrStage(stage, detail = {}) {
                if (window.AA_EDITOR_DEBUG === true || location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
                    console.info('[AA OCR]', stage, detail);
                }
            }

            function updateOcrProgress(stage, progress = 0) {
                const percent = Math.max(0, Math.min(100, Math.round(Number(progress || 0) * 100)));
                const message = percent > 0 ? `${stage} ${percent}%` : stage;
                if (ocrJob) ocrJob.message = message;
                setOcrStatus(message, 'success');
                if (typeof showCanvasLoading === 'function') showCanvasLoading(message);
                updateProcessingOverlay(message);
                logOcrStage(stage, { progress: percent });
            }

            function processingOverlayObjects() {
                return state.canvas?.getObjects?.().filter(object => object?.customType === 'adaacara-ai-processing-overlay') || [];
            }

            function removeProcessingOverlay() {
                if (typeof hideCanvasLoading === 'function') hideCanvasLoading();
                const overlay = state.acaraAiProcessOverlay;
                if (overlay) {
                    overlay.classList.add('is-leaving');
                    overlay.classList.remove('is-visible');
                    window.setTimeout(() => overlay.remove(), 260);
                    state.acaraAiProcessOverlay = null;
                }
                if (!state.canvas) return;
                processingOverlayObjects().forEach(object => state.canvas.remove(object));
                state.canvas.requestRenderAll();
            }

            function showProcessingOverlay(message = 'AdaAcara AI membaca design...') {
                if (!state.canvas) return;
                removeProcessingOverlay();
                const frame = document.getElementById('aaActiveArtboardFrame') || state.canvas.wrapperEl || state.canvas.upperCanvasEl?.parentElement;
                if (!frame) return;
                const overlay = document.createElement('div');
                overlay.className = 'aa-image-process-overlay';
                overlay.style.position = 'absolute';
                overlay.style.inset = '0';
                overlay.style.left = '0';
                overlay.style.top = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.zIndex = '30';
                overlay.innerHTML = '<span class="aa-image-process-shimmer" aria-hidden="true"></span><span class="aa-image-process-card"><i class="fa fa-circle-notch" aria-hidden="true"></i><span></span></span>';
                const label = overlay.querySelector('.aa-image-process-card span');
                if (label) label.textContent = String(message || 'ACARA AI sedang bekerja...');
                frame.appendChild(overlay);
                state.acaraAiProcessOverlay = overlay;
                requestAnimationFrame(() => overlay.classList.add('is-visible'));
            }

            function updateProcessingOverlay(message) {
                const label = state.acaraAiProcessOverlay?.querySelector?.('.aa-image-process-card span');
                if (label) label.textContent = String(message || 'ACARA AI sedang bekerja...');
                state.canvas?.requestRenderAll();
            }

            function loadTesseractScript() {
                if (window.Tesseract?.createWorker) return Promise.resolve();
                if (state.__aaTesseractLoadPromise) return state.__aaTesseractLoadPromise;
                state.__aaTesseractLoadPromise = new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = config.ocrTextTesseractScriptUrl;
                    script.async = true;
                    script.onload = () => window.Tesseract?.createWorker ? resolve() : reject(new Error('Tesseract.js gagal dimuat.'));
                    script.onerror = () => reject(new Error('Asset Tesseract.js tidak dapat dimuat.'));
                    document.head.appendChild(script);
                });
                return state.__aaTesseractLoadPromise;
            }

            function tesseractLogger(message) {
                if (!ocrJob || ocrJob.cancelled) return;
                const status = String(message?.status || '').toLowerCase();
                const progress = Number(message?.progress || 0);
                if (status.includes('load') && status.includes('language')) {
                    updateOcrProgress('Memuat bahasa', progress);
                } else if (status.includes('recogniz')) {
                    updateOcrProgress('Membaca gambar', progress);
                } else if (status.includes('load')) {
                    updateOcrProgress('Menyiapkan OCR', progress);
                }
            }

            async function terminateOcrWorker() {
                const worker = ocrJob?.worker;
                if (ocrJob?.abortController) {
                    try {
                        ocrJob.abortController.abort();
                    } catch (error) {
                        console.warn('[AA OCR] Abort request gagal:', error);
                    }
                    ocrJob.abortController = null;
                }
                if (worker?.terminate) {
                    try {
                        await worker.terminate();
                    } catch (error) {
                        console.warn('[AA OCR] Worker terminate gagal:', error);
                    }
                }
                if (ocrJob) ocrJob.worker = null;
            }

            async function requestOcrAsset(imageSrc) {
                const response = await fetch(config.ocrTextUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        image_src: imageSrc,
                        engine: 'asset-metadata',
                    }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Asset AdaAcara AI tidak lolos validasi server.');
                }
                const asset = data.data || {};
                if (!asset.image_src || !asset.imageWidth || !asset.imageHeight) {
                    throw new Error('Respons asset AdaAcara AI tidak valid.');
                }
                return asset;
            }

            async function requestGeminiBlueprint(imageSrc) {
                const controller = new AbortController();
                if (ocrJob) ocrJob.abortController = controller;
                updateOcrProgress('Memproses gambar', 0);
                const response = await fetch(config.ocrTextUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                    body: JSON.stringify({
                        image_src: imageSrc,
                        engine: 'gemini-vision',
                        creative_prompt: String(els.aaAcaraAiPromptInput?.value || '').trim(),
                    }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'AdaAcara AI gagal membaca gambar referensi.');
                }
                updateOcrProgress('Membuat teks', 1);
                const blueprint = validateBlueprint(data.data || {});
                logOcrStage('Blueprint AdaAcara AI dibuat', {
                    blocks: blueprint.blocks.length,
                    frames: blueprint.frames.length,
                    decorations: blueprint.decorations.length,
                    photos: blueprint.photos.length,
                    shapes: blueprint.shapes.length,
                    overlay: Boolean(blueprint.canvasOverlay),
                });
                return blueprint;
            }

            function normalizeTesseractConfidence(value) {
                const confidence = Number(value || 0);
                if (!Number.isFinite(confidence)) return 0;
                return confidence > 1 ? Math.max(0, Math.min(1, confidence / 100)) : Math.max(0, Math.min(1, confidence));
            }

            function cleanOcrText(text) {
                return String(text || '').replace(/\s+/g, ' ').trim();
            }

            function lineFromTesseractLine(line) {
                const bbox = line?.bbox || {};
                const text = cleanOcrText(line?.text || '');
                if (!text) return null;
                return {
                    text,
                    confidence: normalizeTesseractConfidence(line?.confidence),
                    x: Number(bbox.x0 || 0),
                    y: Number(bbox.y0 || 0),
                    width: Math.max(0, Number(bbox.x1 || 0) - Number(bbox.x0 || 0)),
                    height: Math.max(0, Number(bbox.y1 || 0) - Number(bbox.y0 || 0)),
                    angle: 0,
                    color: '#111827',
                    align: 'center',
                    styleHint: inferStyleHint(text),
                    weightHint: 400,
                    italic: false,
                };
            }

            function inferStyleHint(text) {
                const value = String(text || '').toLowerCase();
                if (value.includes('wedding') || value.includes('the ') || value.includes('of')) return 'elegant-serif';
                if (value.length <= 16 && /^[a-z\s&.'-]+$/i.test(value)) return 'serif';
                return 'sans-serif';
            }

            function blocksFromTesseractBlocks(blocks) {
                const rows = [];
                (blocks || []).forEach(block => {
                    (block?.paragraphs || []).forEach(paragraph => {
                        (paragraph?.lines || []).forEach(line => {
                            const row = lineFromTesseractLine(line);
                            if (row) rows.push(row);
                        });
                    });
                });
                return rows;
            }

            function blocksFromTsv(tsv) {
                const rows = String(tsv || '').trim().split(/\r?\n/);
                if (rows.length < 2) return [];
                const headers = rows.shift().split('\t');
                const index = name => headers.indexOf(name);
                const result = [];
                rows.forEach(row => {
                    const parts = row.split('\t');
                    const level = parts[index('level')];
                    if (level !== '5' && level !== '4') return;
                    const text = cleanOcrText(parts[index('text')] || '');
                    if (!text) return;
                    result.push({
                        text,
                        confidence: normalizeTesseractConfidence(parts[index('conf')]),
                        x: Number(parts[index('left')] || 0),
                        y: Number(parts[index('top')] || 0),
                        width: Number(parts[index('width')] || 0),
                        height: Number(parts[index('height')] || 0),
                        angle: 0,
                        color: '#111827',
                        align: 'center',
                        styleHint: inferStyleHint(text),
                        weightHint: 400,
                        italic: false,
                    });
                });
                if (!result.length) return [];
                result.sort((a, b) => (a.y - b.y) || (a.x - b.x));
                const lines = [];
                result.forEach(word => {
                    const current = lines[lines.length - 1];
                    const sameLine = current && Math.abs((current.y + current.height / 2) - (word.y + word.height / 2)) < Math.max(current.height, word.height) * 0.65;
                    if (!sameLine) {
                        lines.push({ ...word });
                        return;
                    }
                    const right = Math.max(current.x + current.width, word.x + word.width);
                    const bottom = Math.max(current.y + current.height, word.y + word.height);
                    current.text = cleanOcrText(`${current.text} ${word.text}`);
                    current.confidence = Math.min(current.confidence, word.confidence);
                    current.x = Math.min(current.x, word.x);
                    current.y = Math.min(current.y, word.y);
                    current.width = right - current.x;
                    current.height = bottom - current.y;
                    current.styleHint = inferStyleHint(current.text);
                });
                return lines;
            }

            function tesseractResultToBlueprint(result, asset) {
                const data = result?.data || {};
                let blocks = blocksFromTesseractBlocks(data.blocks || []);
                if (!blocks.length) {
                    blocks = blocksFromTsv(data.tsv || '');
                }
                return validateBlueprint({
                    imageWidth: Number(asset.imageWidth || 0),
                    imageHeight: Number(asset.imageHeight || 0),
                    blocks,
                });
            }

            async function runBrowserOcr(imageSrc, existingAsset = null) {
                updateOcrProgress('Menyiapkan OCR', 0);
                await loadTesseractScript();
                if (ocrJob?.cancelled) throw new Error('AdaAcara AI dibatalkan.');
                const asset = existingAsset || await requestOcrAsset(imageSrc);
                if (ocrJob?.cancelled) throw new Error('AdaAcara AI dibatalkan.');
                updateOcrProgress('Memuat bahasa', 0);
                const worker = await Tesseract.createWorker(['eng', 'ind'], 1, {
                    workerPath: config.ocrTextTesseractWorkerUrl,
                    corePath: config.ocrTextTesseractCoreUrl,
                    langPath: config.ocrTextTesseractLangUrl,
                    gzip: true,
                    logger: tesseractLogger,
                });
                if (!ocrJob) {
                    await worker.terminate();
                    throw new Error('AdaAcara AI dibatalkan.');
                }
                ocrJob.worker = worker;
                if (ocrJob.cancelled) throw new Error('AdaAcara AI dibatalkan.');
                await worker.setParameters({
                    tessedit_pageseg_mode: '3',
                    preserve_interword_spaces: '1',
                    user_defined_dpi: '300',
                });
                updateOcrProgress('Membaca gambar', 0);
                const result = await worker.recognize(asset.image_src, {}, { blocks: true, tsv: true });
                if (ocrJob.cancelled) throw new Error('AdaAcara AI dibatalkan.');
                updateOcrProgress('Membuat teks', 1);
                const blueprint = tesseractResultToBlueprint(result, asset);
                logOcrStage('Blueprint dibuat', { blocks: blueprint.blocks.length });
                return blueprint;
            }

            function normalizeGeminiBlueprint(blueprint, asset) {
                const normalized = blueprint || {};
                normalized.imageWidth = Number(normalized.imageWidth || asset?.imageWidth || 0);
                normalized.imageHeight = Number(normalized.imageHeight || asset?.imageHeight || 0);
                normalized.blocks = Array.isArray(normalized.blocks) ? normalized.blocks : [];
                normalized.frames = Array.isArray(normalized.frames) ? normalized.frames : [];
                normalized.decorations = Array.isArray(normalized.decorations) ? normalized.decorations : [];
                normalized.photos = Array.isArray(normalized.photos) ? normalized.photos : [];
                normalized.shapes = Array.isArray(normalized.shapes) ? normalized.shapes : [];
                if (normalized.canvasOverlay?.enabled === true && !normalized.canvasOverlay.assetSrc) {
                    normalized.canvasOverlay = {
                        ...normalized.canvasOverlay,
                        confidence: Math.max(0.84, Number(normalized.canvasOverlay.confidence || 0.84)),
                        x: 0,
                        y: 0,
                        width: normalized.imageWidth,
                        height: normalized.imageHeight,
                        assetSrc: String(asset?.image_src || ''),
                        assetName: 'adaacara-ai-overlay',
                        needsReview: true,
                    };
                }
                return normalized;
            }

            async function runAiBlueprintDetection(imageSrc, sourceAsset = null) {
                const asset = sourceAsset || await requestOcrAsset(imageSrc);
                updateOcrProgress('Memproses gambar', 0);
                const blueprint = await requestGeminiBlueprint(imageSrc);
                const normalized = normalizeGeminiBlueprint(blueprint, asset);
                if (
                    !normalized.blocks.length &&
                    !normalized.frames.length &&
                    !normalized.decorations.length &&
                    !normalized.photos.length &&
                    !normalized.shapes.length &&
                    !normalized.canvasOverlay
                ) {
                    throw new Error('AdaAcara AI belum menemukan elemen desain yang bisa dibuat di editor.');
                }
                return normalized;
            }

            function imageToCanvasRegion(block, blueprint, image) {
                const renderedWidth = Math.max(1, (image.width || blueprint.imageWidth) * (image.scaleX || 1));
                const renderedHeight = Math.max(1, (image.height || blueprint.imageHeight) * (image.scaleY || 1));
                const leftEdge = (image.left || 0) - renderedWidth / 2;
                const topEdge = (image.top || 0) - renderedHeight / 2;
                const scaleX = renderedWidth / Math.max(1, blueprint.imageWidth);
                const scaleY = renderedHeight / Math.max(1, blueprint.imageHeight);
                const left = Math.max(0, leftEdge + block.x * scaleX);
                const top = Math.max(0, topEdge + block.y * scaleY);
                const width = Math.min(state.canvas.getWidth() - left, block.width * scaleX);
                const height = Math.min(state.canvas.getHeight() - top, block.height * scaleY);
                return {
                    left,
                    top,
                    width: Math.max(1, width),
                    height: Math.max(1, height),
                    centerX: left + Math.max(1, width) / 2,
                    centerY: top + Math.max(1, height) / 2,
                };
            }

            async function loadFontForText(family, weight, italic) {
                const safeFamily = cleanFontFamily(family).replace(/"/g, '');
                await Promise.all([
                    typeof ensureBunnyFontCss === 'function' ? ensureBunnyFontCss(safeFamily) : Promise.resolve(),
                    typeof ensureGoogleFontCss === 'function' ? ensureGoogleFontCss(safeFamily) : Promise.resolve(),
                    typeof ensureCustomFontCss === 'function' ? ensureCustomFontCss(safeFamily) : Promise.resolve(),
                ]).catch(() => null);
                if (document.fonts?.load) {
                    await document.fonts.load(`${italic ? 'italic' : 'normal'} ${weight || 400} 32px "${safeFamily}"`).catch(() => null);
                    await document.fonts.ready.catch(() => null);
                }
            }

            function fitTextbox(textbox, target) {
                let low = 8;
                let high = Math.max(9, Math.min(240, target.height * 1.6));
                for (let i = 0; i < 9; i += 1) {
                    const mid = (low + high) / 2;
                    textbox.set({ fontSize: mid, width: target.width, lineHeight: 1.12 });
                    textbox.initDimensions?.();
                    const measuredHeight = textbox.height || mid;
                    if (measuredHeight > target.height * 1.06) high = mid;
                    else low = mid;
                }
                textbox.set({ fontSize: Math.max(8, Math.round(low)), width: target.width });
                textbox.initDimensions?.();
            }

            function createReviewOutline(region, status) {
                const outline = new fabric.Rect({
                    left: region.centerX,
                    top: region.centerY,
                    width: region.width,
                    height: region.height,
                    originX: 'center',
                    originY: 'center',
                    fill: 'rgba(0,0,0,0)',
                    stroke: status === 'review' ? '#f59e0b' : '#ef4444',
                    strokeWidth: 2,
                    strokeDashArray: [7, 5],
                    selectable: false,
                    evented: false,
                    excludeFromExport: true,
                    customType: REVIEW_TYPE,
                });
                outline.setCoords();
                return outline;
            }

            async function createTextObject(block, blueprint, image) {
                const confidenceAuto = Number(config.ocrTextConfidenceAuto || 0.9);
                const confidenceReview = Number(config.ocrTextConfidenceReview || 0.7);
                if (block.confidence < confidenceReview) return null;

                const region = imageToCanvasRegion(block, blueprint, image);
                if (region.width < 10 || region.height < 10) return null;

                const font = matchFont(block);
                const status = block.confidence >= confidenceAuto && font.confidence >= 0.7 && block.needsReview !== true ? 'ok' : 'review';
                await loadFontForText(font.family, block.weightHint, block.italic);

                const textbox = new fabric.Textbox(block.text, {
                    left: region.centerX,
                    top: region.centerY,
                    width: region.width,
                    originX: 'center',
                    originY: 'center',
                    angle: block.angle,
                    fontFamily: font.family,
                    fontSize: Math.max(8, Math.min(180, region.height * 0.8)),
                    fill: block.color,
                    textAlign: block.align,
                    fontWeight: block.weightHint || '400',
                    fontStyle: block.italic ? 'italic' : 'normal',
                    lineHeight: 1.12,
                    customType: 'text',
                    name: status === 'review' ? 'OCR Text - Periksa' : 'OCR Text',
                    aaSource: 'ocr-ai',
                    aaOcrConfidence: block.confidence,
                    aaFontMatchConfidence: font.confidence,
                    aaNeedsBackgroundCleanup: true,
                    aaOcrStatus: status,
                    aaOcrBlockId: block.id,
                    aaOcrFontAlternatives: font.alternatives,
                });
                fitTextbox(textbox, region);
                if (typeof aaApplyTextboxResizeControls === 'function') aaApplyTextboxResizeControls(textbox);
                textbox.setCoords();
                const coverFill = block.backgroundColor || String(state.canvas?.backgroundColor || '#ffffff');
                const cover = new fabric.Rect({
                    left: region.centerX,
                    top: region.centerY,
                    width: Math.max(1, region.width * 1.08),
                    height: Math.max(1, region.height * 1.16),
                    originX: 'center',
                    originY: 'center',
                    angle: block.angle,
                    rx: Math.min(region.width, region.height) * 0.02,
                    ry: Math.min(region.width, region.height) * 0.02,
                    fill: coverFill,
                    opacity: block.coverOpacity,
                    selectable: true,
                    evented: true,
                    customType: 'rect',
                    name: status === 'review' ? 'AI Penutup Teks - Periksa' : 'AI Penutup Teks',
                    aaSource: 'ocr-ai',
                    aaOcrCoverFor: block.id,
                    aaOcrStatus: status,
                    objectCaching: false,
                });
                cover.setCoords();
                return {
                    cover,
                    textbox,
                    outline: status === 'review' ? createReviewOutline(region, status) : null,
                };
            }

            function createFrameObject(frame, blueprint, image) {
                return new Promise(resolve => {
                    const confidenceReview = Number(config.ocrTextConfidenceReview || 0.7);
                    if (frame.confidence < confidenceReview) {
                        resolve(null);
                        return;
                    }

                    if (frame.assetSrc) {
                        createImageObjectFromAsset(frame, blueprint, image, {
                            name: 'AI Frame Foto',
                            reviewConfidence: 0.82,
                        }).then(resolve);
                        return;
                    }

                    const region = imageToCanvasRegion(frame, blueprint, image);
                    if (region.width < 12 || region.height < 12) {
                        resolve(null);
                        return;
                    }

                    const status = frame.needsReview === true || frame.confidence < 0.9 ? 'review' : 'ok';
                    const radius = frame.shape === 'circle' || frame.shape === 'rounded-rect'
                        ? Math.min(region.width, region.height) * 0.08
                        : 0;
                    const placeholder = new fabric.Rect({
                        left: region.centerX,
                        top: region.centerY,
                        width: region.width,
                        height: region.height,
                        originX: 'center',
                        originY: 'center',
                        angle: frame.angle,
                        rx: radius,
                        ry: radius,
                        fill: 'rgba(15, 118, 110, 0.06)',
                        stroke: '#0f766e',
                        strokeWidth: 2,
                        strokeDashArray: [10, 8],
                        customType: 'photo-frame',
                        name: status === 'review' ? 'AI Frame Foto - Periksa' : 'AI Frame Foto',
                        aaSource: 'ocr-ai',
                        aaReferenceMapped: true,
                        aaReferenceRegionKind: 'photo-frame',
                        aaReferenceRegionId: frame.id,
                        aaOcrConfidence: frame.confidence,
                        aaOcrStatus: status,
                        aaNeedsBackgroundCleanup: false,
                        objectCaching: false,
                    });
                    placeholder.setCoords();
                    resolve({
                        textbox: placeholder,
                        outline: status === 'review' ? createReviewOutline(region, status) : null,
                    });
                });
            }

            function createDecorationObject(item, blueprint, image) {
                return new Promise(resolve => {
                    const confidenceReview = Number(config.ocrTextConfidenceReview || 0.7);
                    if (item.confidence < confidenceReview || !item.assetSrc) {
                        resolve(null);
                        return;
                    }

                    const region = imageToCanvasRegion(item, blueprint, image);
                    if (region.width < 12 || region.height < 12) {
                        resolve(null);
                        return;
                    }

                    fabric.Image.fromURL(item.assetSrc, decoration => {
                        if (!decoration) {
                            resolve(null);
                            return;
                        }

                        const sourceWidth = Math.max(1, decoration.width || region.width);
                        const sourceHeight = Math.max(1, decoration.height || region.height);
                        const status = item.needsReview === true || item.needsBackgroundRemoval === true || item.confidence < 0.86 ? 'review' : 'ok';
                        decoration.set({
                            left: region.centerX,
                            top: region.centerY,
                            originX: 'center',
                            originY: 'center',
                            angle: item.angle,
                            scaleX: region.width / sourceWidth,
                            scaleY: region.height / sourceHeight,
                            customType: 'image',
                            name: status === 'review' ? 'AI Dekorasi - Periksa' : 'AI Dekorasi',
                            aaSource: 'ocr-ai',
                            aaOcrConfidence: item.confidence,
                            aaOcrStatus: status,
                            aaNeedsBackgroundCleanup: item.needsBackgroundRemoval === true,
                            aaRemovedBg: item.backgroundRemoved === true,
                            aaRemovedBgSrc: item.backgroundRemoved === true ? item.assetSrc : '',
                            aaOriginalImageSrc: item.assetSrc,
                            aaOriginalImageName: item.assetName,
                            aaGeminiDecorationKind: item.kind,
                            aaGeminiDecorationAsset: item.assetSrc,
                            objectCaching: false,
                        });
                        decoration.setCoords();
                        resolve({
                            textbox: decoration,
                            outline: status === 'review' ? createReviewOutline(region, status) : null,
                        });
                    }, { crossOrigin: 'anonymous' });
                });
            }

            function createImageObjectFromAsset(item, blueprint, image, options = {}) {
                return new Promise(resolve => {
                    const confidenceReview = Number(config.ocrTextConfidenceReview || 0.7);
                    if ((item.confidence || 0) < confidenceReview || !item.assetSrc) {
                        resolve(null);
                        return;
                    }

                    const region = imageToCanvasRegion(item, blueprint, image);
                    if (region.width < 12 || region.height < 12) {
                        resolve(null);
                        return;
                    }

                    fabric.Image.fromURL(item.assetSrc, object => {
                        if (!object) {
                            resolve(null);
                            return;
                        }

                        const sourceWidth = Math.max(1, object.width || region.width);
                        const sourceHeight = Math.max(1, object.height || region.height);
                        const status = item.needsReview === true || item.confidence < (options.reviewConfidence || 0.86) ? 'review' : 'ok';
                        object.set({
                            left: region.centerX,
                            top: region.centerY,
                            originX: 'center',
                            originY: 'center',
                            angle: item.angle || 0,
                            scaleX: region.width / sourceWidth,
                            scaleY: region.height / sourceHeight,
                            customType: 'image',
                            name: status === 'review' ? `${options.name || 'AI Image'} - Periksa` : (options.name || 'AI Image'),
                            aaSource: 'ocr-ai',
                            aaOcrConfidence: item.confidence,
                            aaOcrStatus: status,
                            aaOriginalImageSrc: item.assetSrc,
                            aaOriginalImageName: item.assetName || '',
                            objectCaching: false,
                        });
                        object.setCoords();
                        resolve({
                            textbox: object,
                            outline: status === 'review' ? createReviewOutline(region, status) : null,
                        });
                    }, { crossOrigin: 'anonymous' });
                });
            }

            function createShapeObject(shape, blueprint, image) {
                const confidenceReview = Number(config.ocrTextConfidenceReview || 0.7);
                if (shape.confidence < confidenceReview) return null;

                const region = imageToCanvasRegion(shape, blueprint, image);
                if (region.width < 2 || region.height < 2) return null;

                const status = shape.needsReview === true || shape.confidence < 0.86 ? 'review' : 'ok';
                const base = {
                    left: region.centerX,
                    top: region.centerY,
                    originX: 'center',
                    originY: 'center',
                    angle: shape.angle || 0,
                    fill: shape.fill || 'rgba(0,0,0,0)',
                    stroke: shape.stroke || undefined,
                    strokeWidth: shape.strokeWidth || 0,
                    opacity: shape.opacity,
                    name: status === 'review' ? 'AI Shape - Periksa' : 'AI Shape',
                    aaSource: 'ocr-ai',
                    aaOcrConfidence: shape.confidence,
                    aaOcrStatus: status,
                    objectCaching: false,
                };
                let object = null;

                if (shape.kind === 'line' || shape.kind === 'divider') {
                    object = new fabric.Line([
                        region.left,
                        region.top + region.height / 2,
                        region.left + region.width,
                        region.top + region.height / 2,
                    ], {
                        stroke: shape.stroke || shape.fill || '#111827',
                        strokeWidth: Math.max(1, shape.strokeWidth || Math.max(1, Math.min(6, region.height))),
                        opacity: shape.opacity,
                        name: base.name,
                        aaSource: base.aaSource,
                        aaOcrConfidence: base.aaOcrConfidence,
                        aaOcrStatus: base.aaOcrStatus,
                        objectCaching: false,
                    });
                    object.rotate(shape.angle || 0);
                } else if ((shape.kind === 'polygon' || shape.points.length >= 3) && shape.points.length >= 3) {
                    const mapped = shape.points.map(point => imageToCanvasRegion({ x: point.x, y: point.y, width: 1, height: 1 }, blueprint, image));
                    const minX = Math.min(...mapped.map(point => point.left));
                    const minY = Math.min(...mapped.map(point => point.top));
                    const points = mapped.map(point => ({ x: point.left - minX, y: point.top - minY }));
                    object = new fabric.Polygon(points, {
                        ...base,
                        left: minX,
                        top: minY,
                        originX: 'left',
                        originY: 'top',
                    });
                } else if (shape.kind === 'circle' || shape.kind === 'oval') {
                    object = new fabric.Ellipse({
                        ...base,
                        rx: region.width / 2,
                        ry: region.height / 2,
                    });
                } else {
                    const radius = shape.kind === 'rounded-rect' ? Math.min(region.width, region.height) * 0.08 : 0;
                    object = new fabric.Rect({
                        ...base,
                        width: region.width,
                        height: region.height,
                        rx: radius,
                        ry: radius,
                    });
                }

                object?.setCoords?.();
                return object ? {
                    textbox: object,
                    outline: status === 'review' ? createReviewOutline(region, status) : null,
                } : null;
            }

            function safeAcaraAiText(value) {
                return String(value || '').replace(/[&<>"']/g, character => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[character] || character));
            }

            function activeAcaraAiChatKey() {
                const page = state.pages?.[state.activePageIndex] || null;
                return page?.id || `page-index-${Math.max(0, Number(state.activePageIndex) || 0)}`;
            }

            function acaraAiActivePageLabel() {
                const index = Math.max(0, Number(state.activePageIndex) || 0);
                const page = state.pages?.[index] || null;
                const title = String(page?.title || `Halaman ${index + 1}`).replace(/\s+/g, ' ').trim();
                return `Chat Halaman ${index + 1}${title ? ` - ${title}` : ''}`;
            }

            function syncAcaraAiPageLabel() {
                if (!els.aaAcaraAiPageLabel) return;
                els.aaAcaraAiPageLabel.textContent = acaraAiActivePageLabel();
                els.aaAcaraAiPageLabel.title = acaraAiActivePageLabel();
            }

            function ensureAcaraAiMessageStore() {
                if (!state.acaraAiMessagesByPage || typeof state.acaraAiMessagesByPage !== 'object') {
                    state.acaraAiMessagesByPage = {};
                }
                const key = activeAcaraAiChatKey();
                if (!Array.isArray(state.acaraAiMessagesByPage[key])) {
                    state.acaraAiMessagesByPage[key] = [];
                }
                state.acaraAiMessages = state.acaraAiMessagesByPage[key];
                return state.acaraAiMessages;
            }

            function syncAcaraAiChatForActivePage() {
                ensureAcaraAiMessageStore();
                syncAcaraAiPageLabel();
                renderAcaraAiChat();
                syncAcaraAiPresetVisibility();
            }

            function setAcaraAiChat(message, tone = '') {
                if (!els.aaAcaraAiChatLog) return;
                const messages = ensureAcaraAiMessageStore();
                const role = tone === 'user' ? 'user' : 'assistant';
                const status = tone === 'error' ? 'error' : tone === 'success' ? 'success' : tone === 'saving' ? 'saving' : tone === 'progress' ? 'progress' : '';
                const existingIndex = messages.findIndex(item => item?.id === 'working');
                const item = {
                    id: status === 'saving' ? 'working' : `msg-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                    role,
                    status,
                    text: String(message || ''),
                };
                if (existingIndex >= 0 && role === 'assistant' && status !== 'progress') {
                    messages[existingIndex] = item;
                } else {
                    messages.push(item);
                }
                renderAcaraAiChat();
            }

            let acaraAiProgressTimer = null;
            const ACARA_AI_PROGRESS_STEPS = Object.freeze([
                'Membaca instruksi dan konteks halaman...',
                'Menentukan arah visual yang paling sesuai...',
                'Menyusun struktur halaman editable...',
                'Mengatur hierarki teks dan ruang baca...',
                'Memilih warna, elemen, dan komposisi...',
                'Menyiapkan objek canvas yang bisa diedit...',
                'Merapikan hasil sebelum dimasukkan ke editor...',
            ]);

            function stopAcaraAiProgress() {
                if (!acaraAiProgressTimer) return;
                clearInterval(acaraAiProgressTimer);
                acaraAiProgressTimer = null;
            }

            function clearAcaraAiProgressMessages() {
                const messages = ensureAcaraAiMessageStore();
                for (let index = messages.length - 1; index >= 0; index -= 1) {
                    const status = messages[index]?.status || '';
                    if (status === 'saving' || status === 'progress') {
                        messages.splice(index, 1);
                    }
                }
                renderAcaraAiChat();
            }

            function clearAcaraAiProgressMessagesForKey(key) {
                if (!key || !state.acaraAiMessagesByPage || !Array.isArray(state.acaraAiMessagesByPage[key])) return;
                const messages = state.acaraAiMessagesByPage[key];
                for (let index = messages.length - 1; index >= 0; index -= 1) {
                    const status = messages[index]?.status || '';
                    if (status === 'saving' || status === 'progress') {
                        messages.splice(index, 1);
                    }
                }
            }

            function cleanupAcaraAiDeletedPage(page = {}) {
                const pageId = String(page?.id || '').trim();
                const isAcaraAiPage = page?.aaAcaraAiPromptPage === true || page?.aaAcaraAiWorkingPage === true;
                if (!pageId && !isAcaraAiPage) return;

                if (isAcaraAiPage) {
                    stopAcaraAiProgress();
                    if (ocrJob?.acaraAi && page?.aaAcaraAiWorkingPage === true) {
                        ocrJob.cancelled = true;
                        try {
                            ocrJob.abortController?.abort?.();
                        } catch (error) {
                            console.warn('[ACARA AI] Abort halaman terhapus gagal:', error);
                        }
                    }
                }

                if (pageId && state.acaraAiMessagesByPage && typeof state.acaraAiMessagesByPage === 'object') {
                    clearAcaraAiProgressMessagesForKey(pageId);
                    if (isAcaraAiPage) {
                        delete state.acaraAiMessagesByPage[pageId];
                    }
                }
            }

            function startAcaraAiProgress(options = {}) {
                stopAcaraAiProgress();
                clearAcaraAiProgressMessages();
                const steps = ACARA_AI_PROGRESS_STEPS.slice();
                if (options.hasAttachment) {
                    steps.splice(1, 0, 'Membaca gambar referensi yang kamu lampirkan...');
                }
                if (options.intent === 'redesign_current_page') {
                    steps.splice(options.hasAttachment ? 2 : 1, 0, 'Membaca objek dan teks di halaman aktif...');
                }

                let index = 0;
                setAcaraAiChat('working', 'saving');
                const update = () => {
                    if (!ocrJob?.acaraAi || ocrJob.cancelled) {
                        stopAcaraAiProgress();
                        return;
                    }
                    if (index >= steps.length) {
                        stopAcaraAiProgress();
                        return;
                    }
                    setAcaraAiChat(steps[index], 'progress');
                    index += 1;
                };

                update();
                acaraAiProgressTimer = window.setInterval(update, 2200);
            }

            function resetAcaraAiHero() {
                if (!els.aaAcaraAiChatLog) return;
                syncAcaraAiPageLabel();
                els.aaAcaraAiChatLog.classList.remove('aa-acara-ai-status');
                els.aaAcaraAiChatLog.innerHTML = '<h2>What shall we do with this design?</h2>';
            }

            function renderAcaraAiChat() {
                if (!els.aaAcaraAiChatLog) return;
                syncAcaraAiPageLabel();
                const messages = ensureAcaraAiMessageStore();
                if (!messages.length) {
                    resetAcaraAiHero();
                    return;
                }
                els.aaAcaraAiChatLog.classList.add('aa-acara-ai-status');
                const hasActiveSaving = messages.some(item => item?.status === 'saving');
                const lastProgressIndex = hasActiveSaving ? messages.reduce((last, item, index) => item?.status === 'progress' ? index : last, -1) : -1;
                els.aaAcaraAiChatLog.innerHTML = messages.map((item, messageIndex) => {
                    const role = item.role === 'user' ? 'user' : 'assistant';
                    if (item.status === 'saving') {
                        return `
                            <div class="aa-acara-ai-message is-assistant is-saving">
                                <div class="aa-acara-ai-thinking-card">
                                    <img src="${safeAcaraAiText(config.acaraAiMascotUrl || '')}" alt="" aria-hidden="true">
                                    <div class="aa-acara-ai-thinking-copy">
                                        <strong>ACARA AI sedang bekerja</strong>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    const isActiveProgress = item.status === 'progress' && messageIndex === lastProgressIndex;
                    const icon = item.status === 'error' ? 'fa-triangle-exclamation' :
                        item.status === 'success' ? 'fa-check' :
                        item.status === 'progress' ? (isActiveProgress ? 'fa-spinner fa-spin' : 'fa-check') :
                        'fa-wand-magic-sparkles';
                    const copyButton = role === 'user' ? `
                        <button class="aa-acara-ai-copy-btn" type="button" data-aa-acara-ai-copy="${safeAcaraAiText(item.id)}" title="Copy prompt">
                            <i class="fa fa-copy" aria-hidden="true"></i><span>Copy</span>
                        </button>
                    ` : '';
                    return `
                        <div class="aa-acara-ai-message is-${role} ${item.status ? `is-${item.status}` : ''}">
                            ${role === 'assistant' ? `<i class="fa ${icon}" aria-hidden="true"></i>` : ''}
                            <p>${safeAcaraAiText(item.text)}</p>
                            ${copyButton}
                        </div>
                    `;
                }).join('');
                els.aaAcaraAiChatLog.scrollTop = els.aaAcaraAiChatLog.scrollHeight;
            }

            function syncAcaraAiPresetVisibility() {
                const presets = document.querySelector('[data-aa-acara-ai-presets]');
                if (!presets) return;
                const hasPrompt = String(els.aaAcaraAiPromptInput?.value || '').trim() !== '';
                const hasMessages = ensureAcaraAiMessageStore().length > 0;
                presets.classList.toggle('hidden', hasPrompt || hasMessages);
                if (!hasPrompt && !hasMessages) resetAcaraAiHero();
            }

            function copyTextToClipboard(text) {
                const value = String(text || '');
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(value);
                }
                const textarea = document.createElement('textarea');
                textarea.value = value;
                textarea.setAttribute('readonly', 'readonly');
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                textarea.style.top = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                textarea.remove();
                return Promise.resolve();
            }

            async function copyAcaraAiMessage(messageId) {
                const item = ensureAcaraAiMessageStore().find(message => message?.id === messageId);
                if (!item || !item.text) {
                    setStatus('Prompt tidak ditemukan.', 'error');
                    return;
                }
                try {
                    await copyTextToClipboard(item.text);
                    setStatus('Prompt ACARA AI dicopy.');
                } catch (error) {
                    setStatus('Prompt gagal dicopy.', 'error');
                }
            }

            function currentAcaraAiPageSize() {
                return {
                    width: Math.max(320, Number(state.canvas?.getWidth?.()) || 1080),
                    height: Math.max(320, Number(state.canvas?.getHeight?.()) || 1920),
                };
            }

            function validateAcaraAiAttachment(file) {
                if (!file) return;
                const allowedTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
                if (!allowedTypes.has(file.type || '')) {
                    throw new Error('Format gambar harus JPG, PNG, atau WEBP.');
                }
                if (file.size > Number(config.importReferenceMaxFileSize || 2 * 1024 * 1024)) {
                    throw new Error('Ukuran gambar maksimal 2MB.');
                }
            }

            function clearAcaraAiAttachmentPreview(options = {}) {
                if (els.aaImportReferenceFileInput) els.aaImportReferenceFileInput.value = '';
                els.aaImportReferencePreviewImage?.removeAttribute('src');
                if (els.aaImportReferencePreviewMeta) els.aaImportReferencePreviewMeta.textContent = '';
                els.aaImportReferencePreview?.classList.add('hidden');
                if (options.silent !== true) {
                    setAcaraAiChat('Attachment dihapus. Prompt tetap bisa dikirim tanpa gambar.', '');
                }
            }

            async function uploadAcaraAiAttachment(file, signal = null) {
                validateAcaraAiAttachment(file);
                const form = new FormData();
                form.append('asset', file);
                const response = await fetch(config.importReferenceUploadUrl, {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin',
                    signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Upload gambar ACARA AI gagal.');
                }
                const item = Array.isArray(data.data) ? data.data[0] : data.data;
                const src = String(item?.src || '').trim();
                if (!src) {
                    throw new Error('URL gambar hasil upload tidak ditemukan.');
                }
                return src;
            }

            function compactAcaraAiPageContext() {
                if (!state.canvas || state.editMode === 'opening') return null;
                storeCurrentPage();
                const page = state.pages[state.activePageIndex] || getCurrentPageData();
                const objects = (page.objects || []).slice(0, 80).map((object, index) => {
                    const text = String(object.text || object.placeholder || object.label || '').trim();
                    return {
                        index,
                        type: object.type || '',
                        customType: object.customType || '',
                        name: object.name || '',
                        text: text.slice(0, 220),
                        left: Math.round(Number(object.left || 0)),
                        top: Math.round(Number(object.top || 0)),
                        width: Math.round(Number(object.width || 0)),
                        height: Math.round(Number(object.height || 0)),
                        scaleX: Number(Number(object.scaleX || 1).toFixed(3)),
                        scaleY: Number(Number(object.scaleY || 1).toFixed(3)),
                        angle: Math.round(Number(object.angle || 0)),
                        fill: object.fill || '',
                        stroke: object.stroke || '',
                        fontFamily: object.fontFamily || '',
                        fontSize: Math.round(Number(object.fontSize || 0)),
                        fontWeight: object.fontWeight || '',
                        textAlign: object.textAlign || '',
                    };
                });
                return {
                    mode: 'duplicate_and_redesign_current_page',
                    title: page.title || `Halaman ${state.activePageIndex + 1}`,
                    activePageIndex: state.activePageIndex,
                    artboard: page.artboard || currentAcaraAiPageSize(),
                    backgroundColor: page.backgroundColor || page.background || '#ffffff',
                    objectCount: (page.objects || []).length,
                    objects,
                };
            }

            function acaraAiPageHasEditableContent(page = state.pages?.[state.activePageIndex]) {
                if (!page || page.aaAcaraAiWorkingPage === true) return false;
                const objects = Array.isArray(page.objects) ? page.objects : [];
                return objects.some(object => {
                    if (!object || object.customType === 'background' || object.customType === 'selection-helper') return false;
                    if (object.visible === false || object.selectable === false && object.evented === false) return false;
                    const text = String(object.text || object.placeholder || object.label || '').trim();
                    const width = Math.abs(Number(object.width || 0) * Number(object.scaleX || 1));
                    const height = Math.abs(Number(object.height || 0) * Number(object.scaleY || 1));
                    return text !== '' || (width >= 8 && height >= 8);
                });
            }

            function inferAcaraAiIntent(prompt) {
                const activePage = state.pages?.[state.activePageIndex] || null;
                if (activePage?.aaAcaraAiPromptPage === true && activePage?.aaAcaraAiWorkingPage !== true) {
                    return 'redesign_current_page';
                }
                const text = String(prompt || '').toLowerCase();
                const redesignPattern = /\b(redesign|re-?design|ubah\s+desain|desain\s+ulang|rombak|perbaiki\s+desain|change\s+style|ubah\s+style|ubah\s+gaya|ganti\s+style|ganti\s+tema|ubah\s+warna|color\s+scheme|add\s+background|tambahkan\s+background|tambah\s+background|ganti\s+background)\b/;
                const pageReferencePattern = /\b(this\s+(page|design)|current\s+(page|design)|halaman\s+ini|desain\s+ini|page\s+ini)\b/;
                const newPagePattern = /\b(new\s+(page|design)|blank\s+(page|design)|buat\s+halaman\s+baru|halaman\s+baru|desain\s+baru|buat\s+baru|from\s+scratch|mulai\s+dari\s+nol)\b/;
                if (newPagePattern.test(text)) return 'new_design';
                if (acaraAiPageHasEditableContent(activePage)) return 'redesign_current_page';
                return redesignPattern.test(text) || pageReferencePattern.test(text) ? 'redesign_current_page' : 'new_design';
            }

            function acaraAiHistoryForRequest(prompt) {
                const messages = ensureAcaraAiMessageStore();
                return messages
                    .filter(item => item && item.id !== 'working' && item.text && item.text !== prompt)
                    .slice(-8)
                    .map(item => ({
                        role: item.role === 'user' ? 'user' : 'assistant',
                        text: String(item.text || '').slice(0, 600),
                    }));
            }

            async function requestAcaraAiPromptBlueprint(prompt, imageSrc = '', pageContext = null, intent = 'new_design') {
                const controller = ocrJob?.abortController || new AbortController();
                if (ocrJob) ocrJob.abortController = controller;
                const pageSize = currentAcaraAiPageSize();
                const response = await fetch(config.acaraAiGenerateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                    body: JSON.stringify({
                        prompt,
                        image_src: imageSrc,
                        imageWidth: pageSize.width,
                        imageHeight: pageSize.height,
                        intent,
                        page_context: pageContext,
                        history: acaraAiHistoryForRequest(prompt),
                    }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'ACARA AI gagal membuat desain.');
                }
                return validateBlueprint(data.data || {});
            }

            function setAcaraAiSendBusy(isBusy) {
                const button = els.aaAcaraAiSendBtn;
                if (!button) return;
                button.classList.toggle('is-stop', isBusy);
                button.title = isBusy ? 'Stop ACARA AI' : 'Kirim prompt';
                button.setAttribute('aria-label', isBusy ? 'Stop ACARA AI' : 'Kirim prompt');
                button.innerHTML = isBusy ?
                    '<i class="fa fa-stop" aria-hidden="true"></i>' :
                    '<i class="fa fa-arrow-right" aria-hidden="true"></i>';
            }

            function cancelAcaraAiPromptComposer() {
                if (!ocrJob?.acaraAi) return false;
                ocrJob.cancelled = true;
                stopAcaraAiProgress();
                try {
                    ocrJob.abortController?.abort?.();
                } catch (error) {
                    console.warn('[ACARA AI] Abort gagal:', error);
                }
                clearAcaraAiProgressMessages();
                setAcaraAiChat('ACARA AI dihentikan.', 'error');
                setStatus('ACARA AI dihentikan.');
                return true;
            }

            function fakeAcaraAiSourceImage(blueprint) {
                const pageSize = currentAcaraAiPageSize();
                return {
                    left: pageSize.width / 2,
                    top: pageSize.height / 2,
                    width: Math.max(1, Number(blueprint.imageWidth || pageSize.width)),
                    height: Math.max(1, Number(blueprint.imageHeight || pageSize.height)),
                    scaleX: pageSize.width / Math.max(1, Number(blueprint.imageWidth || pageSize.width)),
                    scaleY: pageSize.height / Math.max(1, Number(blueprint.imageHeight || pageSize.height)),
                };
            }

            function acaraAiPageTitle(prompt) {
                const clean = String(prompt || '').replace(/\s+/g, ' ').trim();
                return clean ? `ACARA AI - ${clean.slice(0, 34)}` : `ACARA AI ${state.pages.length + 1}`;
            }

            async function createAcaraAiWorkingPage(prompt, pageSize = currentAcaraAiPageSize()) {
                storeCurrentPage();
                const pageData = createBlankPageData(acaraAiPageTitle(prompt));
                pageData.title = acaraAiPageTitle(prompt);
                pageData.artboard = pageSize;
                pageData.background = '#ffffff';
                pageData.backgroundColor = '#ffffff';
                pageData.aaAcaraAiPromptPage = true;
                pageData.aaAcaraAiWorkingPage = true;
                state.editMode = 'pages';
                state.pages.push(pageData);
                state.activePageIndex = state.pages.length - 1;
                loadPageData(pageData, { preserveZoom: false, snapshot: false });
                if (state.loadPromise && typeof state.loadPromise.then === 'function') {
                    await state.loadPromise;
                }
                if (typeof showCanvasLoading === 'function') showCanvasLoading('ACARA AI sedang bekerja...');
                showProcessingOverlay('ACARA AI sedang bekerja...');
                if (typeof renderPageList === 'function') renderPageList();
                return pageData.id;
            }

            function removeAcaraAiWorkingPage(pageId) {
                if (!pageId) return;
                const index = state.pages.findIndex(page => page?.id === pageId && page?.aaAcaraAiWorkingPage === true);
                if (index < 0) return;
                state.pages.splice(index, 1);
                if (!state.pages.length) {
                    state.pages.push(createBlankPageData('Halaman 1'));
                }
                state.activePageIndex = Math.max(0, Math.min(state.activePageIndex >= index ? state.activePageIndex - 1 : state.activePageIndex, state.pages.length - 1));
                loadPageData(state.pages[state.activePageIndex] || createBlankPageData('Halaman 1'), {
                    preserveZoom: true,
                    snapshot: false,
                });
                if (typeof renderPageList === 'function') renderPageList();
            }

            async function createAcaraAiPageFromBlueprint(blueprint, prompt, options = {}) {
                const hasObjects = Boolean(
                    blueprint.canvasOverlay ||
                    blueprint.shapes.length ||
                    blueprint.photos.length ||
                    blueprint.decorations.length ||
                    blueprint.frames.length ||
                    blueprint.blocks.length
                );
                if (!hasObjects) {
                    throw new Error('ACARA AI belum menghasilkan elemen desain.');
                }

                const pageSize = currentAcaraAiPageSize();
                const targetPageId = options.targetPageId || '';
                const targetIndex = targetPageId ? state.pages.findIndex(page => page?.id === targetPageId) : -1;
                const pageData = targetIndex >= 0 ? state.pages[targetIndex] : createBlankPageData(acaraAiPageTitle(prompt));
                if (targetPageId && targetIndex < 0) {
                    throw new DOMException('ACARA AI dibatalkan.', 'AbortError');
                }
                pageData.title = acaraAiPageTitle(prompt);
                pageData.artboard = pageData.artboard || pageSize;
                pageData.background = blueprint.backgroundColor || '#ffffff';
                pageData.backgroundColor = blueprint.backgroundColor || '#ffffff';
                pageData.objects = [];
                pageData.aaAcaraAiPromptPage = true;
                delete pageData.aaAcaraAiWorkingPage;
                state.editMode = 'pages';
                if (targetIndex >= 0) {
                    state.activePageIndex = targetIndex;
                    state.pages[targetIndex] = pageData;
                } else {
                    storeCurrentPage();
                    state.pages.push(pageData);
                    state.activePageIndex = state.pages.length - 1;
                }
                loadPageData(pageData, { preserveZoom: false, snapshot: false });
                if (state.loadPromise && typeof state.loadPromise.then === 'function') {
                    await state.loadPromise;
                }
                removeProcessingOverlay();
                if (blueprint.backgroundColor && state.canvas) {
                    state.canvas.setBackgroundColor(blueprint.backgroundColor, state.canvas.requestRenderAll.bind(state.canvas));
                }

                const sourceImage = fakeAcaraAiSourceImage(blueprint);
                const created = [];
                const outlines = [];
                const addResult = result => {
                    if (!result) return;
                    if (result.cover && Number(result.cover.opacity || 0) > 0.08) {
                        state.canvas.add(result.cover);
                        created.push(result.cover);
                    }
                    if (result.textbox) {
                        state.canvas.add(result.textbox);
                        created.push(result.textbox);
                    }
                    if (result.outline) {
                        state.canvas.add(result.outline);
                        outlines.push(result.outline);
                    }
                };

                if (blueprint.canvasOverlay) {
                    addResult(await createImageObjectFromAsset(blueprint.canvasOverlay, blueprint, sourceImage, {
                        name: 'ACARA AI Overlay',
                        reviewConfidence: 0.84,
                    }));
                }
                for (const shape of blueprint.shapes) {
                    addResult(createShapeObject(shape, blueprint, sourceImage));
                }
                for (const photo of blueprint.photos) {
                    addResult(await createImageObjectFromAsset(photo, blueprint, sourceImage, {
                        name: 'ACARA AI Foto',
                        reviewConfidence: 0.82,
                    }));
                }
                for (const decoration of blueprint.decorations) {
                    addResult(await createDecorationObject(decoration, blueprint, sourceImage));
                }
                for (const frame of blueprint.frames) {
                    addResult(await createFrameObject(frame, blueprint, sourceImage));
                }
                for (const block of blueprint.blocks) {
                    addResult(await createTextObject(block, blueprint, sourceImage));
                }

                if (!created.length) {
                    throw new Error('ACARA AI belum berhasil membuat objek editor.');
                }

                outlines.forEach(outline => state.canvas.remove(outline));
                state.canvas.discardActiveObject();
                state.canvas.requestRenderAll();
                storeCurrentPage();
                if (typeof renderPageList === 'function') renderPageList();
                snapshot();
            }

            async function runAcaraAiPromptComposer() {
                const prompt = String(els.aaAcaraAiPromptInput?.value || '').trim();
                if (!prompt) {
                    setAcaraAiChat('Tulis prompt ACARA AI terlebih dahulu.', 'error');
                    setStatus('Tulis prompt ACARA AI terlebih dahulu.', 'error');
                    return;
                }
                if (!state.canvas || !window.fabric) {
                    setStatus('Canvas belum siap.', 'error');
                    return;
                }

                const file = els.aaImportReferenceFileInput?.files?.[0] || null;
                try {
                    if (file) validateAcaraAiAttachment(file);
                } catch (error) {
                    setAcaraAiChat(error?.message || 'Gambar referensi tidak valid.', 'error');
                    setStatus(error?.message || 'Gambar referensi tidak valid.', 'error');
                    return;
                }

                if (els.aaAcaraAiPromptInput) {
                    els.aaAcaraAiPromptInput.value = '';
                }
                syncAcaraAiPresetVisibility();
                setAcaraAiChat(prompt, 'user');
                const sourcePageKey = activeAcaraAiChatKey();
                const sourceAcaraAiMessages = ensureAcaraAiMessageStore().map(item => ({ ...item }));

                const controller = new AbortController();
                const intent = inferAcaraAiIntent(prompt);
                const pageContext = intent === 'redesign_current_page' ? compactAcaraAiPageContext() : null;
                const pageSize = currentAcaraAiPageSize();
                let workingPageId = '';
                ocrJob = {
                    acaraAi: true,
                    cancelled: false,
                    worker: null,
                    abortController: controller,
                    message: 'ACARA AI berpikir...',
                };
                setAcaraAiSendBusy(true);
                setStatus('ACARA AI membuat desain...', 'saving');
                startAcaraAiProgress({
                    hasAttachment: Boolean(file),
                    intent,
                });

                try {
                    workingPageId = await createAcaraAiWorkingPage(prompt, pageSize);
                    if (workingPageId) {
                        if (!state.acaraAiMessagesByPage || typeof state.acaraAiMessagesByPage !== 'object') {
                            state.acaraAiMessagesByPage = {};
                        }
                        state.acaraAiMessagesByPage[workingPageId] = sourceAcaraAiMessages.map(item => ({ ...item }));
                        state.acaraAiMessages = state.acaraAiMessagesByPage[workingPageId];
                        renderAcaraAiChat();
                        clearAcaraAiProgressMessagesForKey(sourcePageKey);
                        startAcaraAiProgress({
                            hasAttachment: Boolean(file),
                            intent,
                        });
                    }
                    let imageSrc = '';
                    if (file) {
                        imageSrc = await uploadAcaraAiAttachment(file, controller.signal);
                        clearAcaraAiAttachmentPreview({ silent: true });
                        if (ocrJob?.cancelled) throw new DOMException('ACARA AI dibatalkan.', 'AbortError');
                    }
                    let blueprint = await requestAcaraAiPromptBlueprint(prompt, imageSrc, pageContext, intent);
                    blueprint = ensureAcaraAiAttachmentInBlueprint(blueprint, imageSrc);
                    if (ocrJob?.cancelled) throw new DOMException('ACARA AI dibatalkan.', 'AbortError');
                    await createAcaraAiPageFromBlueprint(blueprint, prompt, { targetPageId: workingPageId });
                    stopAcaraAiProgress();
                    clearAcaraAiProgressMessagesForKey(sourcePageKey);
                    clearAcaraAiProgressMessagesForKey(workingPageId);
                    clearAcaraAiProgressMessages();
                    workingPageId = '';
                    setAcaraAiChat('Selesai. Saya membuat hasilnya di halaman baru agar desain asli tetap aman.', 'success');
                    setStatus('ACARA AI berhasil membuat halaman baru.');
                } catch (error) {
                    clearAcaraAiProgressMessagesForKey(sourcePageKey);
                    if (workingPageId && state.acaraAiMessagesByPage && typeof state.acaraAiMessagesByPage === 'object') {
                        clearAcaraAiProgressMessagesForKey(workingPageId);
                        delete state.acaraAiMessagesByPage[workingPageId];
                    }
                    removeAcaraAiWorkingPage(workingPageId);
                    stopAcaraAiProgress();
                    clearAcaraAiProgressMessages();
                    setAcaraAiChat(error?.name === 'AbortError' ? 'ACARA AI dibatalkan.' : (error?.message || 'ACARA AI gagal membuat desain.'), 'error');
                    setStatus(error?.message || 'ACARA AI gagal membuat desain.', 'error');
                } finally {
                    stopAcaraAiProgress();
                    removeProcessingOverlay();
                    if (typeof hideCanvasLoading === 'function') hideCanvasLoading();
                    await terminateOcrWorker();
                    setAcaraAiSendBusy(false);
                    ocrJob = null;
                    updateOcrTextUi();
                }
            }

            async function startOcrTextDetection(options = {}) {
                if (!options.force && !ocrAvailable()) {
                    setStatus('AdaAcara AI hanya aktif pada page referensi.', 'error');
                    updateOcrTextUi();
                    return;
                }
                if (typeof window.cancelReferenceMapperMode === 'function') window.cancelReferenceMapperMode();
                const originalIndex = state.activePageIndex;
                const image = referenceImageObject();
                const imageSrc = image?.aaOriginalImageSrc || image?.src || image?.getSrc?.();
                if (!imageSrc) {
                    setStatus('Gambar referensi tidak ditemukan.', 'error');
                    return;
                }

                ocrJob = { cancelled: false, worker: null, message: 'Menyiapkan AdaAcara AI' };
                const loadingButton = options.button || ocrPrimaryButton();
                if (typeof setButtonLoading === 'function' && loadingButton) {
                    setButtonLoading(loadingButton, true, options.loadingText || 'Membaca...');
                }
                if (loadingButton) loadingButton.disabled = true;
                if (typeof showCanvasLoading === 'function') showCanvasLoading('Memproses gambar...');
                showProcessingOverlay('Memproses gambar...');
                updateOcrTextUi();
                setStatus('AdaAcara AI berjalan...', 'saving');

                try {
                    storeCurrentPage();
                    const sourceAsset = await requestOcrAsset(imageSrc);
                    const blueprint = await runAiBlueprintDetection(imageSrc, sourceAsset);
                    const hasAiObjects = Boolean(
                        blueprint.canvasOverlay ||
                        blueprint.shapes.length ||
                        blueprint.photos.length ||
                        blueprint.decorations.length ||
                        blueprint.frames.length ||
                        blueprint.blocks.length
                    );
                    if (!hasAiObjects) {
                        throw new Error('AdaAcara AI belum menemukan elemen desain yang bisa dibuat di editor.');
                    }
                    const draft = JSON.parse(JSON.stringify(state.pages[originalIndex] || getCurrentPageData()));
                    draft.id = `page-${Date.now()}-${Math.random().toString(16).slice(2)}`;
                    draft.title = `${draft.title || 'Referensi'} - AI Draft`;
                    draft.aaOcrDraftPage = true;
                    state.pages.splice(originalIndex + 1, 0, draft);
                    state.activePageIndex = originalIndex + 1;
                    state.__aaOcrReviewActive = true;
                    loadPageData(draft, { preserveZoom: true, snapshot: false });
                    if (state.loadPromise && typeof state.loadPromise.then === 'function') await state.loadPromise;
                    if (blueprint.backgroundColor && state.canvas) {
                        state.canvas.setBackgroundColor(blueprint.backgroundColor, state.canvas.requestRenderAll.bind(state.canvas));
                    }

                    const draftImage = referenceImageObject();
                    const created = [];
                    const outlines = [];
                    const sourceImage = draftImage || image;
                    if (blueprint.canvasOverlay) {
                        const result = await createImageObjectFromAsset(blueprint.canvasOverlay, blueprint, sourceImage, {
                            name: 'AI Overlay',
                            reviewConfidence: 0.84,
                        });
                        if (result) {
                            state.canvas.add(result.textbox);
                            created.push(result.textbox);
                            if (result.outline) {
                                state.canvas.add(result.outline);
                                outlines.push(result.outline);
                            }
                        }
                    }
                    for (const shape of blueprint.shapes) {
                        const result = createShapeObject(shape, blueprint, sourceImage);
                        if (!result) continue;
                        state.canvas.add(result.textbox);
                        created.push(result.textbox);
                        if (result.outline) {
                            state.canvas.add(result.outline);
                            outlines.push(result.outline);
                        }
                    }
                    for (const photo of blueprint.photos) {
                        const result = await createImageObjectFromAsset(photo, blueprint, sourceImage, {
                            name: 'AI Foto',
                            reviewConfidence: 0.82,
                        });
                        if (!result) continue;
                        state.canvas.add(result.textbox);
                        created.push(result.textbox);
                        if (result.outline) {
                            state.canvas.add(result.outline);
                            outlines.push(result.outline);
                        }
                    }
                    for (const decoration of blueprint.decorations) {
                        const result = await createDecorationObject(decoration, blueprint, sourceImage);
                        if (!result) continue;
                        state.canvas.add(result.textbox);
                        created.push(result.textbox);
                        if (result.outline) {
                            state.canvas.add(result.outline);
                            outlines.push(result.outline);
                        }
                    }
                    for (const frame of blueprint.frames) {
                        const result = await createFrameObject(frame, blueprint, sourceImage);
                        if (!result) continue;
                        state.canvas.add(result.textbox);
                        created.push(result.textbox);
                        if (result.outline) {
                            state.canvas.add(result.outline);
                            outlines.push(result.outline);
                        }
                    }
                    for (const block of blueprint.blocks) {
                        const result = await createTextObject(block, blueprint, sourceImage);
                        if (!result) continue;
                        if (result.cover) {
                            state.canvas.add(result.cover);
                            created.push(result.cover);
                        }
                        state.canvas.add(result.textbox);
                        created.push(result.textbox);
                        if (result.outline) {
                            state.canvas.add(result.outline);
                            outlines.push(result.outline);
                        }
                    }

                    if (!created.length) {
                        throw new Error('AdaAcara AI belum berhasil membuat elemen editor dari gambar ini.');
                    }

                    review = {
                        originalIndex,
                        draftIndex: state.activePageIndex,
                        objects: created,
                        outlines,
                        showOnlyReview: false,
                        compareHidden: false,
                    };
                    state.canvas.discardActiveObject();
                    state.canvas.requestRenderAll();
                    logOcrStage('Objek AI dibuat', { count: created.length });
                    applyOcrReview({ auto: true });
                } catch (error) {
                    cleanupReviewDraft(originalIndex);
                    setStatus(error?.name === 'AbortError' ? 'AdaAcara AI dibatalkan.' : (error?.message || 'AdaAcara AI gagal.'), 'error');
                } finally {
                    await terminateOcrWorker();
                    removeProcessingOverlay();
                    if (typeof setButtonLoading === 'function' && loadingButton) {
                        setButtonLoading(loadingButton, false);
                    }
                    ocrJob = null;
                    updateOcrTextUi();
                }
            }

            function cleanupReviewDraft(originalIndex = null) {
                const targetOriginalIndex = originalIndex ?? review?.originalIndex ?? Math.max(0, state.activePageIndex - 1);
                state.__aaOcrReviewActive = false;
                if (review?.draftIndex >= 0) {
                    state.pages.splice(review.draftIndex, 1);
                } else if (state.pages[state.activePageIndex]?.aaOcrDraftPage === true) {
                    state.pages.splice(state.activePageIndex, 1);
                }
                state.activePageIndex = Math.max(0, Math.min(targetOriginalIndex, state.pages.length - 1));
                review = null;
                const page = state.pages[state.activePageIndex] || createBlankPageData('Halaman 1');
                loadPageData(page, { preserveZoom: true, snapshot: false });
            }

            function reviewObjectsFromCanvas() {
                return state.canvas?.getObjects?.().filter(object => object?.aaSource === 'ocr-ai' && object?.customType !== REVIEW_TYPE) || [];
            }

            function reviewOutlinesFromCanvas() {
                return state.canvas?.getObjects?.().filter(object => object?.customType === REVIEW_TYPE) || [];
            }

            function activeReviewObjects() {
                const objects = (review?.objects || []).filter(object => state.canvas?.getObjects?.().includes(object));
                return objects.length ? objects : reviewObjectsFromCanvas();
            }

            function activeReviewOutlines() {
                const outlines = (review?.outlines || []).filter(object => state.canvas?.getObjects?.().includes(object));
                return outlines.length ? outlines : reviewOutlinesFromCanvas();
            }

            function isImportReferencePageData(page) {
                return Boolean(
                    page?.aaImportReferencePage === true ||
                    (Array.isArray(page?.objects) && page.objects.some(object => object?.aaImportReference === true))
                );
            }

            function applyOcrReview(options = {}) {
                if (!state.canvas) return;
                const objects = activeReviewObjects();
                if (!review && state.__aaOcrReviewActive !== true && !objects.length) return;
                const reviewState = review ? {
                    originalIndex: review.originalIndex,
                    draftIndex: review.draftIndex,
                } : null;
                activeReviewOutlines().forEach(outline => state.canvas.remove(outline));
                state.canvas.getObjects()
                    .filter(object => object?.aaImportReference === true)
                    .forEach(object => state.canvas.remove(object));
                objects.forEach(object => object.set({ visible: true }));
                state.canvas.requestRenderAll();
                storeCurrentPage();
                if (state.pages[state.activePageIndex]) {
                    delete state.pages[state.activePageIndex].aaOcrDraftPage;
                    delete state.pages[state.activePageIndex].aaImportReferencePage;
                }
                const originalIndex = Number(reviewState?.originalIndex);
                const canRemoveOriginalReference = Number.isInteger(originalIndex) &&
                    originalIndex >= 0 &&
                    originalIndex < state.pages.length &&
                    originalIndex !== state.activePageIndex &&
                    isImportReferencePageData(state.pages[originalIndex]);
                if (canRemoveOriginalReference) {
                    state.pages.splice(originalIndex, 1);
                    if (state.activePageIndex > originalIndex) {
                        state.activePageIndex -= 1;
                    }
                }
                state.__aaOcrReviewActive = false;
                review = null;
                if (typeof renderPageList === 'function') renderPageList();
                snapshot();
                setStatus(options.auto ? 'Design berhasil dibaca dan diterapkan.' : 'Hasil AdaAcara AI diterapkan.');
                updateOcrTextUi();
            }

            function cancelOcrReview(message = 'AdaAcara AI dibatalkan') {
                if (ocrJob) {
                    ocrJob.cancelled = true;
                    terminateOcrWorker();
                    updateOcrTextUi();
                    setStatus(message);
                    return;
                }
                if (!review && state.__aaOcrReviewActive !== true) return;
                cleanupReviewDraft();
                setStatus(message);
                updateOcrTextUi();
            }

            window.cancelOcrTextReview = cancelOcrReview;

            function toggleCompare() {
                if (!state.canvas) return;
                const objects = activeReviewObjects();
                if (!review && state.__aaOcrReviewActive !== true && !objects.length) return;
                if (!review) {
                    review = {
                        originalIndex: Math.max(0, state.activePageIndex - 1),
                        draftIndex: state.activePageIndex,
                        objects,
                        outlines: activeReviewOutlines(),
                        showOnlyReview: false,
                        compareHidden: false,
                    };
                }
                review.compareHidden = !review.compareHidden;
                objects.forEach(object => object.set({ visible: !review.compareHidden }));
                state.canvas.requestRenderAll();
                setStatus(review.compareHidden ? 'Menampilkan gambar asli.' : 'Menampilkan hasil AdaAcara AI.');
            }

            function toggleReviewOnly() {
                if (!review || !state.canvas) return;
                review.showOnlyReview = !review.showOnlyReview;
                review.objects.forEach(object => {
                    object.set({ visible: !review.showOnlyReview || object.aaOcrStatus === 'review' });
                });
                state.canvas.requestRenderAll();
            }

            function selectedOcrText() {
                const active = state.canvas?.getActiveObject?.();
                return active && active.aaSource === 'ocr-ai' && active.type === 'textbox' ? active : null;
            }

            function syncOcrSelectionStatus() {
                const active = selectedOcrText();
                if (!active || !review) return;
                const confidence = Math.round(Number(active.aaOcrConfidence || 0) * 100);
                const fontMatch = Math.round(Number(active.aaFontMatchConfidence || 0) * 100);
                const cleanup = active.aaNeedsBackgroundCleanup ? 'Background perlu dicek.' : 'Background aman.';
                setOcrStatus(`OCR ${confidence}%, font ${active.fontFamily || 'Inter'} (${fontMatch}%). ${cleanup}`, active.aaOcrStatus === 'review' ? 'error' : 'success');
            }

            async function changeSelectedOcrFont() {
                const active = selectedOcrText();
                if (!active) {
                    setStatus('Pilih objek teks hasil OCR terlebih dahulu.', 'error');
                    return;
                }
                const alternatives = Array.isArray(active.aaOcrFontAlternatives) && active.aaOcrFontAlternatives.length
                    ? active.aaOcrFontAlternatives
                    : fontCatalog().map(item => item.family);
                const next = prompt('Pilih font OCR:', alternatives.join(', '));
                const family = cleanFontFamily(next || '');
                if (!family || !fontCatalog().some(item => item.family === family)) {
                    setStatus('Font tidak tersedia di whitelist AdaAcara.', 'error');
                    return;
                }
                const target = {
                    width: Math.max(10, active.width || active.getScaledWidth?.() || 100),
                    height: Math.max(10, active.height || active.getScaledHeight?.() || active.fontSize || 40),
                };
                await loadFontForText(family, active.fontWeight || 400, active.fontStyle === 'italic');
                active.set({ fontFamily: family, aaFontMatchConfidence: 1 });
                fitTextbox(active, target);
                active.setCoords();
                state.canvas.requestRenderAll();
                syncOcrSelectionStatus();
            }

            window.startGeminiAiForCurrentReference = startOcrTextDetection;
            window.syncAcaraAiChatForActivePage = syncAcaraAiChatForActivePage;
            window.cleanupAcaraAiDeletedPage = cleanupAcaraAiDeletedPage;

            if (typeof loadPageData === 'function' && state.__aaAcaraAiLoadPageWrapped !== true) {
                const originalLoadPageData = loadPageData;
                loadPageData = function(pageData, options = {}) {
                    const result = originalLoadPageData.call(this, pageData, options);
                    const sync = () => syncAcaraAiChatForActivePage();
                    if (state.loadPromise && typeof state.loadPromise.then === 'function') {
                        state.loadPromise.then(sync).catch(sync);
                    } else {
                        window.setTimeout(sync, 0);
                    }
                    return result;
                };
                state.__aaAcaraAiLoadPageWrapped = true;
            }

            els.aaAcaraAiChatLog?.addEventListener('click', event => {
                const button = event.target?.closest?.('[data-aa-acara-ai-copy]');
                if (!button) return;
                event.preventDefault();
                event.stopPropagation();
                copyAcaraAiMessage(button.dataset.aaAcaraAiCopy || '');
            });

            els.aaAcaraAiAttachBtn?.addEventListener('click', event => {
                event.preventDefault();
                els.aaImportReferenceFileInput?.click();
            });
            els.aaAcaraAiNewChatBtn?.addEventListener('click', event => {
                event.preventDefault();
                if (cancelAcaraAiPromptComposer()) return;
                const key = activeAcaraAiChatKey();
                if (!state.acaraAiMessagesByPage || typeof state.acaraAiMessagesByPage !== 'object') {
                    state.acaraAiMessagesByPage = {};
                }
                state.acaraAiMessagesByPage[key] = [];
                state.acaraAiMessages = state.acaraAiMessagesByPage[key];
                if (els.aaAcaraAiPromptInput) els.aaAcaraAiPromptInput.value = '';
                resetAcaraAiHero();
                syncAcaraAiPresetVisibility();
            });
            document.addEventListener('click', event => {
                const preset = event.target?.closest?.('[data-aa-acara-ai-preset]');
                if (!preset) return;
                event.preventDefault();
                if (els.aaAcaraAiPromptInput) {
                    els.aaAcaraAiPromptInput.value = String(preset.dataset.aaAcaraAiPreset || '');
                    els.aaAcaraAiPromptInput.focus();
                }
                syncAcaraAiPresetVisibility();
            });
            els.aaAcaraAiAttachmentClearBtn?.addEventListener('click', event => {
                event.preventDefault();
                clearAcaraAiAttachmentPreview();
            });
            els.aaImportReferenceFileInput?.addEventListener('change', event => {
                const file = event.target.files?.[0] || null;
                if (!file) return;
                try {
                    validateAcaraAiAttachment(file);
                    setAcaraAiChat(`Gambar "${file.name}" siap dipakai sebagai referensi opsional.`, 'success');
                } catch (error) {
                    setAcaraAiChat(error?.message || 'Gambar referensi tidak valid.', 'error');
                }
            });
            els.aaAcaraAiSendBtn?.addEventListener('click', event => {
                event.preventDefault();
                if (cancelAcaraAiPromptComposer()) return;
                runAcaraAiPromptComposer();
            });
            els.aaAcaraAiPromptInput?.addEventListener('keydown', event => {
                if (event.key !== 'Enter' || event.shiftKey) return;
                event.preventDefault();
                if (ocrJob?.acaraAi) return;
                runAcaraAiPromptComposer();
            });
            els.aaAcaraAiPromptInput?.addEventListener('input', syncAcaraAiPresetVisibility);
            syncAcaraAiChatForActivePage();
            els.aaOcrTextDetectBtn?.addEventListener('click', event => {
                event.preventDefault();
                startOcrTextDetection();
            });
            els.aaOcrApplyBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                applyOcrReview();
            });
            els.aaOcrCancelBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                cancelOcrReview();
            });
            els.aaOcrCompareBtn?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                toggleCompare();
            });
            els.aaOcrReviewOnlyBtn?.addEventListener('click', toggleReviewOnly);
            els.aaOcrChangeFontBtn?.addEventListener('click', changeSelectedOcrFont);
            document.addEventListener('click', event => {
                const target = event.target?.closest?.('#aaOcrApplyBtn, #aaOcrCancelBtn, #aaOcrCompareBtn');
                if (!target) return;
                event.preventDefault();
                if (target.id === 'aaOcrApplyBtn') applyOcrReview();
                if (target.id === 'aaOcrCancelBtn') cancelOcrReview();
                if (target.id === 'aaOcrCompareBtn') toggleCompare();
            });
            state.canvas?.on?.('selection:created', syncOcrSelectionStatus);
            state.canvas?.on?.('selection:updated', syncOcrSelectionStatus);
            updateOcrTextUi();
        }
