(function () {
    'use strict';

    const cfg = window.AdaAcaraGuestMemory || {};
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
    const i18n = cfg && typeof cfg.i18n === 'object' && cfg.i18n ? cfg.i18n : {};
    function t(key, fallback, replace = {}) {
        let text = String(i18n[key] || fallback || '');
        Object.keys(replace || {}).forEach((name) => {
            text = text.split('{' + name + '}').join(String(replace[name]));
        });
        return text;
    }

    const missingFrameMessage = t('gm.frame.missing', 'Silahkan buat tampilan Frame di adaAcara Studio dahulu');
    const maxSourceImageBytes = 20 * 1024 * 1024;
    const maxPreparedImageSide = 2000;
    const galleryAutoRefreshMs = 12000;

    let frames = normalizeFrames(Array.isArray(cfg.frames) ? cfg.frames : []);
    const frameImageCache = new Map();
    const frameFontWaitCache = new Map();
    const framePreviewCache = new Map();
    let fabricLoaderPromise = null;
    let frameLoaderPromise = null;
    let mainPreviewRenderId = 0;
    let finalPreviewRenderId = 0;
    let framePickerRenderId = 0;
    const state = {
        panel: 'home',
        step: 'photo',
        page: 1,
        hasMore: true,
        loading: false,
        refreshing: false,
        searchQuery: '',
        frameIndex: frames.length > 1 ? 1 : 0,
        sourceImages: [],
        activeSlotIndex: 0,
        finalBlob: null,
        thumbBlob: null,
        pendingCrop: null,
        cropAutoContinue: false,
        cropFromCamera: true,
        audioBlob: null,
        audioDuration: 0,
        audioStream: null,
        mediaRecorder: null,
        recordChunks: [],
        recordStartedAt: 0,
        recordTimer: null,
        discardRecording: false,
        detailAudio: null,
        printItem: null
    };

    const els = {
        app: $('[data-gm-app]'),
        opening: $('[data-gm-screen="opening"]'),
        experience: $('[data-gm-screen="experience"]'),
        transition: $('[data-gm-transition]'),
        back: $('[data-gm-back]'),
        search: $('[data-gm-search]'),
        grid: $('[data-gm-grid]'),
        empty: $('[data-gm-empty]'),
        loader: $('[data-gm-loader]'),
        sentinel: $('[data-gm-sentinel]'),
        cameraInput: $('[data-gm-camera-input]'),
        fileInput: $('[data-gm-file-input]'),
        canvas: $('[data-gm-canvas]'),
        finalCanvas: $('[data-gm-final-canvas]'),
        framePreview: $('[data-gm-frame-preview]'),
        frameDots: $('[data-gm-frame-dots]'),
        frameInfo: $('[data-gm-frame-info]'),
        captureTitle: $('[data-gm-capture-title]'),
        slotStatus: $('[data-gm-slot-status]'),
        slotList: $('[data-gm-slot-list]'),
        nextSlot: $('[data-gm-next-slot]'),
        cameraButton: $('[data-gm-camera]'),
        cropCanvas: $('[data-gm-crop-canvas]'),
        cropStatus: $('[data-gm-crop-status]'),
        cropZoom: $('[data-gm-crop-zoom]'),
        cropUse: $('[data-gm-use-crop]'),
        retake: $('[data-gm-retake]'),
        nameInput: $('[data-gm-name]'),
        emailInput: $('[data-gm-email]'),
        wishInput: $('[data-gm-wish]'),
        wishCount: $('[data-gm-wish-count]'),
        submit: $('[data-gm-submit]'),
        progress: $('[data-gm-progress]'),
        progressBar: $('[data-gm-progress-bar]'),
        floatingWishes: $('[data-gm-floating-wishes]'),
        floatingWishesTrack: $('[data-gm-floating-wishes-track]'),
        recorder: $('[data-gm-recorder]'),
        recordTime: $('[data-gm-record-time]'),
        recordToggle: $('[data-gm-toggle-record]'),
        recordClear: $('[data-gm-clear-audio]'),
        printing: $('[data-gm-printing]'),
        printingPhoto: $('[data-gm-printing-photo]'),
        detailModal: $('[data-gm-detail-modal]'),
        printCodeModal: $('[data-gm-print-code-modal]'),
        printCodeName: $('[data-gm-print-code-name]'),
        printCodeInput: $('[data-gm-print-code-input]'),
        printCodeSubmit: $('[data-gm-print-code-submit]'),
        printCodeStatus: $('[data-gm-print-code-status]'),
        printCodeActions: $('[data-gm-print-code-actions]'),
        printCodeDownload: $('[data-gm-print-code-download]'),
        printCodePrint: $('[data-gm-print-code-print]'),
        toast: $('[data-gm-toast]')
    };

    function defaultFrames() {
        return [
            {
                id: 1,
                title: t('gm.frame.info', '1 foto', { count: 1 }),
                width: 1080,
                height: 1350,
                style: 'classic',
                slot_count: 1,
                accent: '#31401f',
                paper: '#f8f4ea',
                photo_slots: [{ index: 1, x: 165, y: 150, width: 750, height: 560, radius: 0 }]
            },
            {
                id: 2,
                title: t('gm.frame.info_plural', '2 foto', { count: 2 }),
                width: 1080,
                height: 2200,
                style: 'duo_stack',
                slot_count: 2,
                accent: '#425b33',
                paper: '#f4f1e7',
                photo_slots: [
                    { index: 1, x: 165, y: 260, width: 750, height: 560, radius: 0 },
                    { index: 2, x: 165, y: 880, width: 750, height: 560, radius: 0 }
                ]
            },
            {
                id: 3,
                title: t('gm.frame.info_plural', '3 foto', { count: 3 }),
                width: 1080,
                height: 3000,
                style: 'trio_stack',
                slot_count: 3,
                accent: '#34451f',
                paper: '#fbf7ef',
                photo_slots: [
                    { index: 1, x: 165, y: 300, width: 750, height: 560, radius: 0 },
                    { index: 2, x: 165, y: 920, width: 750, height: 560, radius: 0 },
                    { index: 3, x: 165, y: 1540, width: 750, height: 560, radius: 0 }
                ]
            }
        ];
    }

    function normalizeFrames(sourceFrames) {
        const source = Array.isArray(sourceFrames) && sourceFrames.length ? sourceFrames : [];
        return source.slice(0, 3).map((frame, index) => normalizeFrame(frame, index));
    }

    function normalizeFrame(frame, index) {
        const fallback = defaultFrames()[index] || defaultFrames()[0];
        const normalized = { ...fallback, ...(frame || {}) };
        const count = Math.max(1, Math.min(3, Number(normalized.slot_count || fallback.slot_count || index + 1) || 1));
        normalized.slot_count = count;
        const metadataSlots = normalizePhotoSlotList(normalized.photo_slots || normalized.photoSlots, count);
        const objectSlots = extractFabricPhotoSlots(normalized.fabric && normalized.fabric.objects, count);
        const defaultSlots = normalizePhotoSlotList(defaultPhotoSlots(count, normalized.style || fallback.style), count);
        let slots = metadataSlots;
        if (objectSlots.length >= count || objectSlots.length > metadataSlots.length) {
            slots = objectSlots;
        }
        if (slots.length < count) {
            slots = slots.concat(defaultSlots.slice(slots.length, count));
        }
        normalized.photo_slots = slots.slice(0, count).map((slot, slotIndex) => ({
            index: slotIndex + 1,
            x: Number(slot.x ?? slot.left ?? 165),
            y: Number(slot.y ?? slot.top ?? 150 + (slotIndex * 620)),
            width: Math.max(120, Number(slot.width || 750)),
            height: Math.max(120, Number(slot.height || 560)),
            radius: Math.max(0, Number(slot.radius || slot.rx || slot.ry || 0))
        }));
        return normalized;
    }

    function normalizePhotoSlotList(source, count) {
        return (Array.isArray(source) ? source : [])
            .slice(0, Math.max(1, Math.min(3, Number(count) || 1)))
            .map((slot, slotIndex) => ({
                index: slotIndex + 1,
                x: Number(slot?.x ?? slot?.left ?? 0) || 0,
                y: Number(slot?.y ?? slot?.top ?? 0) || 0,
                width: Math.max(40, Number(slot?.width || 0) || 0),
                height: Math.max(40, Number(slot?.height || 0) || 0),
                radius: Math.max(0, Number(slot?.radius ?? slot?.rx ?? slot?.ry ?? 0) || 0)
            }))
            .filter((slot) => slot.width >= 40 && slot.height >= 40);
    }

    function slotOrderNumber(object, fallback) {
        const value = Number(object && object.aaPhotoboothSlotIndex);
        return Number.isFinite(value) && value > 0 ? value : fallback;
    }

    function slotMetric(object, rawIndex) {
        const scaleX = Math.max(0.01, Number(object.scaleX || 1) || 1);
        const scaleY = Math.max(0.01, Number(object.scaleY || 1) || 1);
        const width = typeof object.getScaledWidth === 'function'
            ? object.getScaledWidth()
            : (Number(object.width || 0) || 0) * scaleX;
        const height = typeof object.getScaledHeight === 'function'
            ? object.getScaledHeight()
            : (Number(object.height || 0) || 0) * scaleY;
        return {
            object,
            rawIndex,
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
        const counts = {};
        slots.forEach((slot) => {
            counts[slot.orderNumber] = (counts[slot.orderNumber] || 0) + 1;
        });
        const hasDuplicateIndex = slots.some((slot) => counts[slot.orderNumber] > 1);
        slots.sort((a, b) => {
            if (!hasDuplicateIndex) {
                const indexDiff = (a.orderNumber || 0) - (b.orderNumber || 0);
                if (indexDiff !== 0) {
                    return indexDiff;
                }
            }
            return (a.y - b.y) || (a.x - b.x) || (a.rawIndex - b.rawIndex);
        });
        return slots;
    }

    function extractFabricPhotoSlots(objects, count) {
        const slots = sortSlotsByVisualPosition((Array.isArray(objects) ? objects : [])
            .filter((object) => object && object.customType === 'photobooth-photo-slot')
            .map((object, rawIndex) => slotMetric(object, rawIndex)));

        return slots.slice(0, Math.max(1, Math.min(3, Number(count) || 1))).map((slot, index) => ({
            index,
            x: slot.x,
            y: slot.y,
            width: slot.width,
            height: slot.height,
            radius: slot.radius
        }));
    }

    function defaultPhotoSlots(count, style) {
        if (count === 3 || style === 'trio_stack') {
            return [
                { index: 1, x: 165, y: 300, width: 750, height: 560, radius: 0 },
                { index: 2, x: 165, y: 920, width: 750, height: 560, radius: 0 },
                { index: 3, x: 165, y: 1540, width: 750, height: 560, radius: 0 }
            ];
        }
        if (count === 2 || style === 'duo_stack') {
            return [
                { index: 1, x: 165, y: 260, width: 750, height: 560, radius: 0 },
                { index: 2, x: 165, y: 880, width: 750, height: 560, radius: 0 }
            ];
        }
        return [{ index: 1, x: 165, y: 150, width: 750, height: 560, radius: 0 }];
    }

    function csrfFormData(formData) {
        if (cfg.csrfName && cfg.csrfHash) {
            formData.append(cfg.csrfName, cfg.csrfHash);
        }
        return formData;
    }

    function updateCsrf(hash) {
        if (!hash) {
            return;
        }
        cfg.csrfHash = hash;
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            meta.setAttribute('content', hash);
        }
    }

    function showToast(message, duration = 4600, options = {}) {
        if (!els.toast) {
            return;
        }
        const persistent = options && options.persistent === true;
        els.toast.classList.toggle('aa-gm-toast--important', persistent);
        els.toast.innerHTML = '';

        if (persistent) {
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'aa-gm-toast__close';
            closeButton.setAttribute('aria-label', t('gm.toast.close', 'Tutup pemberitahuan'));
            closeButton.textContent = '×';
            closeButton.addEventListener('click', () => {
                els.toast.hidden = true;
                els.toast.classList.remove('aa-gm-toast--important');
            });
            els.toast.appendChild(closeButton);
        }

        if (options && options.title) {
            const title = document.createElement('strong');
            title.className = 'aa-gm-toast__title';
            title.textContent = options.title;
            els.toast.appendChild(title);
        }

        if (options && options.code) {
            const code = document.createElement('span');
            code.className = 'aa-gm-toast__code';
            code.textContent = options.code;
            els.toast.appendChild(code);
        }

        const body = document.createElement('span');
        body.className = 'aa-gm-toast__body';
        body.textContent = message;
        els.toast.appendChild(body);

        els.toast.hidden = false;
        clearTimeout(showToast.timer);
        if (!persistent) {
            showToast.timer = setTimeout(() => {
                els.toast.hidden = true;
            }, duration);
        }
    }

    function showPrintCodeNotice(response) {
        const code = String(response && response.print_code || '').trim();
        if (!code) {
            showToast(response && response.message ? response.message : t('gm.toast.success', 'Momen berhasil ditambahkan.'), 5600);
            return;
        }

        const emailSent = response && response.print_code_email_sent === true;
        const message = emailSent
            ? t('gm.toast.code_email', 'Simpan kode ini untuk cetak/unduh. Kode juga sudah dikirim ke email kamu.')
            : t('gm.toast.code_manual', 'Simpan kode ini untuk cetak/unduh. Jika email diisi tetapi belum masuk, cek inbox/spam atau gunakan kode yang tampil di sini.');

        showToast(message, 0, {
            persistent: true,
            title: t('gm.toast.code_title', 'Kode Cetak Kamu'),
            code: code
        });
    }

    function showStatus(message, duration = 1800) {
        showToast(message, duration);
    }

    function openFileInput(input) {
        if (!input) {
            return;
        }
        input.value = '';
        input.click();
    }

    function pulseTransition() {
        if (!els.transition) {
            return;
        }
        els.transition.classList.add('is-active');
        clearTimeout(pulseTransition.timer);
        pulseTransition.timer = window.setTimeout(() => {
            els.transition.classList.remove('is-active');
        }, 520);
    }

    function switchScreen(name) {
        if (els.opening) {
            els.opening.hidden = name !== 'opening';
        }
        if (els.experience) {
            els.experience.hidden = name !== 'experience';
        }
        pulseTransition();
    }

    function switchPanel(name) {
        const previousPanel = state.panel;
        state.panel = name;
        $$('[data-gm-panel]').forEach((panel) => {
            panel.hidden = panel.getAttribute('data-gm-panel') !== name;
        });
        if (previousPanel !== name) {
            pulseTransition();
        }
        if (els.back) {
            els.back.hidden = name === 'home';
        }
        if (name === 'gallery') {
            loadMemories(true);
        }
        if (name === 'upload') {
            ensureFramesLoaded()
                .then(() => {
                    resetUpload();
                    switchStep('frame');
                })
                .catch((error) => {
                    showToast(error.message || missingFrameMessage, 7000);
                    switchPanel('home');
                });
        }
    }

    function switchStep(name) {
        const previousStep = state.step;
        state.step = name;
        $$('[data-gm-step]').forEach((step) => {
            step.hidden = step.getAttribute('data-gm-step') !== name;
        });
        if (previousStep !== name) {
            pulseTransition();
        }
        if (name === 'frame') {
            renderFramePicker();
        }
        if (name === 'photo') {
            renderCaptureStep();
            renderMainPreview(true);
        }
        if (name === 'crop') {
            renderCropCanvas();
        }
        if (name === 'details') {
            renderFinalPreview(true);
        }
    }

    async function fetchJson(url) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const data = await response.json().catch(() => ({}));
        updateCsrf(data.csrf_hash);
        if (!response.ok || data.success === false) {
            throw new Error(data.message || t('gm.error.request', 'Permintaan belum berhasil.'));
        }
        return data;
    }

    function memoryActionUrl(template, id) {
        return String(template || '')
            .replace('{id}', encodeURIComponent(String(id)))
            .replace('%7Bid%7D', encodeURIComponent(String(id)))
            .replace('%7bid%7d', encodeURIComponent(String(id)));
    }

    function memoryDeleteUrl(item) {
        const id = Number(item && item.id || 0);
        if (!id || !cfg.deleteUrlTemplate) {
            return '';
        }

        return memoryActionUrl(cfg.deleteUrlTemplate, id);
    }

    function memoryPrintAccessUrl(item) {
        const id = Number(item && item.id || 0);
        if (!id || !cfg.printAccessUrlTemplate) {
            return '';
        }

        return memoryActionUrl(cfg.printAccessUrlTemplate, id);
    }

    function memoryMarkPrintedUrl(item) {
        const id = Number(item && item.id || 0);
        if (!id || !cfg.markPrintedUrlTemplate) {
            return '';
        }

        return memoryActionUrl(cfg.markPrintedUrlTemplate, id);
    }

    function closePrintCodeModal() {
        state.printItem = null;
        if (els.printCodeModal) {
            els.printCodeModal.hidden = true;
        }
        if (els.printCodeInput) {
            els.printCodeInput.value = '';
        }
        if (els.printCodeStatus) {
            els.printCodeStatus.textContent = '';
        }
        if (els.printCodeActions) {
            els.printCodeActions.hidden = true;
        }
        if (els.printCodeDownload) {
            els.printCodeDownload.href = '';
            els.printCodeDownload.removeAttribute('download');
        }
        if (els.printCodePrint) {
            els.printCodePrint.dataset.photo = '';
            els.printCodePrint.dataset.filename = '';
            els.printCodePrint.dataset.code = '';
        }
    }

    function openPrintCodeModal(item) {
        if (!els.printCodeModal) {
            showToast(t('gm.error.print_form', 'Form kode cetak belum tersedia.'));
            return;
        }
        state.printItem = item;
        if (els.printCodeName) {
            els.printCodeName.textContent = item && item.guest_name ? item.guest_name : t('gm.print_access.name', 'Momen');
        }
        if (els.printCodeInput) {
            els.printCodeInput.value = '';
        }
        if (els.printCodeStatus) {
            els.printCodeStatus.textContent = '';
        }
        if (els.printCodeActions) {
            els.printCodeActions.hidden = true;
        }
        if (els.printCodeDownload) {
            els.printCodeDownload.href = '';
            els.printCodeDownload.removeAttribute('download');
        }
        if (els.printCodePrint) {
            els.printCodePrint.dataset.photo = '';
            els.printCodePrint.dataset.filename = '';
            els.printCodePrint.dataset.code = '';
        }
        els.printCodeModal.hidden = false;
        window.setTimeout(() => els.printCodeInput?.focus(), 60);
    }

    function downloadPhoto(url, filename) {
        if (!url) {
            showToast(t('gm.error.photo_unavailable', 'Foto belum tersedia.'));
            return;
        }
        const link = document.createElement('a');
        link.href = url;
        link.download = filename || 'photobooth-memory.jpg';
        document.body.appendChild(link);
        link.click();
        link.remove();
    }

    function isMobilePrintDevice() {
        const ua = navigator.userAgent || '';
        const mobileAgent = /Android|iPhone|iPod|Mobile/i.test(ua);
        const tabletAgent = /iPad|Tablet/i.test(ua);
        const iPadDesktopAgent = /Macintosh/i.test(ua) && Number(navigator.maxTouchPoints || 0) > 1;
        const screenWidth = Math.min(window.innerWidth || 0, window.screen && window.screen.width || 0);

        return mobileAgent || tabletAgent || (iPadDesktopAgent && screenWidth <= 1180);
    }

    function writePrintWindow(printWindow, url, filename, autoPrint = true) {
        if (!printWindow || !url) {
            return Promise.reject(new Error(t('gm.error.print_photo_unavailable', 'Foto belum tersedia untuk dicetak.')));
        }
        const safeTitle = String(filename || 'photobooth-memory').replace(/[<>&"']/g, '');
        const safeUrl = String(url)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        const printLayoutStatus = t('gm.print.layout', 'Menyiapkan layout cetak...');
        const layout = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' + safeTitle + '</title><style>*{box-sizing:border-box;}html,body{margin:0;min-height:100%;background:#f8fafc;color:#0f172a;font-family:Arial,sans-serif;}body{display:grid;place-items:center;min-height:100vh;padding:18px;}.aa-print-sheet{display:grid;place-items:center;width:min(100vw,900px);height:min(100vh,1200px);background:#fff;box-shadow:0 18px 50px rgba(15,23,42,.16);overflow:hidden;}.aa-print-sheet img{display:block;width:100%;height:100%;object-fit:contain;}.aa-print-status{position:fixed;left:16px;right:16px;bottom:14px;text-align:center;font-size:13px;color:#64748b;}@media print{@page{size:auto;margin:0;}html,body{width:100%;height:100%;min-height:100%;background:#fff;}body{display:block;padding:0;}.aa-print-sheet{width:100vw;height:100vh;box-shadow:none;overflow:hidden;}.aa-print-sheet img{width:100%;height:100%;object-fit:contain;}.aa-print-status{display:none;}}</style></head><body><main class="aa-print-sheet"><img id="aa-print-photo" src="' + safeUrl + '" alt="Photobooth"></main><div class="aa-print-status">' + printLayoutStatus + '</div></body></html>';
        printWindow.document.open();
        printWindow.document.write(layout);
        printWindow.document.close();

        return new Promise((resolve, reject) => {
            const finish = () => {
                try {
                    printWindow.focus();
                    if (autoPrint) {
                        printWindow.print();
                    }
                    resolve(printWindow);
                } catch (error) {
                    reject(error);
                }
            };
            const fail = () => reject(new Error(t('gm.error.print_photo_load', 'Foto belum berhasil dimuat untuk cetak.')));
            const bindImage = () => {
                const image = printWindow.document.getElementById('aa-print-photo');
                if (!image) {
                    fail();
                    return;
                }
                if (image.complete && image.naturalWidth > 0) {
                    window.setTimeout(finish, 80);
                    return;
                }
                image.addEventListener('load', () => window.setTimeout(finish, 80), { once: true });
                image.addEventListener('error', fail, { once: true });
            };

            if (printWindow.document.readyState === 'complete') {
                bindImage();
            } else {
                printWindow.addEventListener('load', bindImage, { once: true });
                window.setTimeout(bindImage, 120);
            }
        });
    }

    async function printPhoto(url, filename, existingWindow) {
        if (!url) {
            showToast(t('gm.error.print_photo_unavailable', 'Foto belum tersedia untuk dicetak.'));
            return;
        }
        if (isMobilePrintDevice()) {
            showToast(t('gm.error.mobile_print', 'Cetak foto tersedia dari komputer/meja printer. Gunakan Unduh Foto jika memakai HP.'), 7000);
            return;
        }

        const printWindow = existingWindow || window.open('', '_blank', 'width=900,height=1200');
        if (!printWindow) {
            showToast(t('gm.error.popup', 'Izinkan pop-up untuk membuka cetak foto.'), 7000);
            return;
        }

        await writePrintWindow(printWindow, url, filename);
    }

    async function markMemoryPrinted(code) {
        const url = memoryMarkPrintedUrl(state.printItem);
        if (!url) {
            throw new Error(t('gm.error.print_access', 'Akses cetak belum tersedia untuk foto ini.'));
        }

        const formData = csrfFormData(new FormData());
        formData.append('print_code', code);
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const data = await response.json().catch(() => ({}));
        updateCsrf(data.csrf_hash);
        if (!response.ok || data.success === false) {
            throw new Error(data.message || t('gm.error.print_failed', 'Foto ini belum bisa dicetak.'));
        }

        return data;
    }

    async function handlePrintCodePrint() {
        const url = els.printCodePrint?.dataset.photo || '';
        const filename = els.printCodePrint?.dataset.filename || 'photobooth-memory.jpg';
        const code = els.printCodePrint?.dataset.code || '';
        if (!url || !code) {
            showToast(t('gm.error.validate_print', 'Validasi kode cetak terlebih dahulu.'));
            return;
        }
        if (isMobilePrintDevice()) {
            showToast(t('gm.error.mobile_print', 'Cetak foto tersedia dari komputer/meja printer. Gunakan Unduh Foto jika memakai HP.'), 7000);
            return;
        }

        const printWindow = window.open('', '_blank', 'width=900,height=1200');
        if (!printWindow) {
            showToast(t('gm.error.popup', 'Izinkan pop-up untuk membuka cetak foto.'), 7000);
            return;
        }
        printWindow.document.open();
        printWindow.document.write('<!doctype html><html><head><meta charset="utf-8"><title>' + t('gm.print.prepare_title', 'Menyiapkan Cetak') + '</title></head><body style="font-family:Arial,sans-serif;display:grid;min-height:100vh;place-items:center;margin:0;">' + t('gm.print.prepare', 'Menyiapkan cetak...') + '</body></html>');
        printWindow.document.close();

        try {
            await writePrintWindow(printWindow, url, filename, false);
            await markMemoryPrinted(code);
            printWindow.focus();
            printWindow.print();
            if (els.printCodeStatus) {
                els.printCodeStatus.textContent = t('gm.print.approved', 'Cetak disetujui. Foto ini tidak bisa dicetak lagi dari kode yang sama.');
            }
        } catch (error) {
            try {
                printWindow.close();
            } catch (closeError) {
            }
            showToast(error.message || t('gm.error.print_failed', 'Foto ini belum bisa dicetak.'), 7000);
        }
    }

    async function submitPrintCode() {
        const item = state.printItem;
        const url = memoryPrintAccessUrl(item);
        const code = (els.printCodeInput && els.printCodeInput.value || '').trim();
        if (!url) {
            showToast(t('gm.error.print_access', 'Akses cetak belum tersedia untuk foto ini.'));
            return;
        }
        if (!code) {
            showToast(t('gm.print.enter_code', 'Masukkan kode cetak terlebih dahulu.'));
            return;
        }

        const formData = csrfFormData(new FormData());
        formData.append('print_code', code);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const data = await response.json().catch(() => ({}));
            updateCsrf(data.csrf_hash);
            if (!response.ok || data.success === false) {
                throw new Error(data.message || t('gm.print.wrong_code', 'Kode cetak belum sesuai.'));
            }
            if (els.printCodeStatus) {
                els.printCodeStatus.textContent = t('gm.print.code_ok', 'Kode cocok. Pilih Unduh atau Cetak Foto.');
            }
            if (els.printCodeActions) {
                els.printCodeActions.hidden = false;
            }
            if (els.printCodeDownload) {
                els.printCodeDownload.href = data.photo || '';
                els.printCodeDownload.download = data.filename || 'photobooth-memory.jpg';
            }
            if (els.printCodePrint) {
                els.printCodePrint.dataset.photo = data.photo || '';
                els.printCodePrint.dataset.filename = data.filename || 'photobooth-memory.jpg';
                els.printCodePrint.dataset.code = code;
            } else {
                downloadPhoto(data.photo, data.filename);
            }
        } catch (error) {
            if (els.printCodeStatus) {
                els.printCodeStatus.textContent = '';
            }
            if (els.printCodeActions) {
                els.printCodeActions.hidden = true;
            }
            if (els.printCodeDownload) {
                els.printCodeDownload.href = '';
                els.printCodeDownload.removeAttribute('download');
            }
            if (els.printCodePrint) {
                els.printCodePrint.dataset.photo = '';
                els.printCodePrint.dataset.filename = '';
                els.printCodePrint.dataset.code = '';
            }
            showToast(error.message || t('gm.print.wrong_code', 'Kode cetak belum sesuai.'), 7000);
        }
    }

    async function deleteMemoryItem(item, card) {
        const url = memoryDeleteUrl(item);
        if (!url) {
            showToast(t('gm.delete.unavailable', 'Foto ini belum bisa dihapus.'));
            return;
        }

        const expectedName = String(item.guest_name || '').trim();
        const inputName = window.prompt(t('gm.delete.prompt', 'Tulis nama yang kamu pakai saat upload foto ini:'));
        if (inputName === null) {
            return;
        }

        const guestName = String(inputName || '').trim();
        if (!guestName) {
            showToast(t('gm.delete.name_required', 'Nama wajib diisi untuk menghapus foto.'));
            return;
        }
        if (guestName.toLowerCase() !== expectedName.toLowerCase()) {
            showToast(t('gm.delete.name_mismatch', 'Nama tidak cocok. Gunakan nama yang sama saat upload foto.'), 7000);
            return;
        }
        if (!window.confirm(t('gm.delete.confirm', 'Apakah kamu yakin ingin menghapus foto kamu dari galeri photobooth?'))) {
            return;
        }

        const formData = csrfFormData(new FormData());
        formData.append('guest_name', guestName);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const data = await response.json().catch(() => ({}));
            updateCsrf(data.csrf_hash);
            if (!response.ok || data.success === false) {
                throw new Error(data.message || t('gm.delete.failed', 'Foto belum berhasil dihapus.'));
            }
            card?.remove();
            updateGalleryEmptyState();
            refreshFloatingWishes();
            showToast(data.message || t('gm.delete.success', 'Foto memories berhasil dihapus dari galeri.'));
        } catch (error) {
            showToast(error.message || t('gm.delete.failed', 'Foto belum berhasil dihapus.'), 7000);
        }
    }

    function createCard(item) {
        const card = document.createElement('article');
        card.className = 'aa-gm-card';
        card.dataset.memoryId = String(item.id || '');
        card.dataset.guestName = String(item.guest_name || '').toLowerCase();
        card.dataset.guestDisplay = String(item.guest_name || t('gm.card.guest', 'Tamu'));
        card.dataset.thumbnail = String(item.thumbnail || item.photo || '');
        card.dataset.wishText = normalizeWishText(item.wish_text || '');
        const button = document.createElement('button');
        button.type = 'button';

        const img = document.createElement('img');
        img.loading = 'lazy';
        img.decoding = 'async';
        img.src = item.thumbnail || item.photo;
        img.alt = t('gm.card.alt', 'Momen dari {name}', { name: item.guest_name || t('gm.card.guest', 'Tamu') });

        const parts = splitDate(item.created_at || '');
        const meta = document.createElement('div');
        meta.className = 'aa-gm-card__meta';
        meta.innerHTML = '<span class="aa-gm-card__name"></span><span class="aa-gm-card__date"><b></b><b></b></span>';
        $('.aa-gm-card__name', meta).textContent = item.guest_name || t('gm.card.guest', 'Tamu');
        $$('.aa-gm-card__date b', meta)[0].textContent = parts.date;
        $$('.aa-gm-card__date b', meta)[1].textContent = parts.time;

        button.append(img, meta);
        button.addEventListener('click', () => openDetail(item));

        const menuWrap = document.createElement('div');
        menuWrap.className = 'aa-gm-card-menu';
        const menuButton = document.createElement('button');
        menuButton.type = 'button';
        menuButton.className = 'aa-gm-card-menu__trigger';
        menuButton.setAttribute('aria-label', t('gm.card.menu', 'Menu memories'));
        menuButton.innerHTML = '<span></span><span></span><span></span>';
        const menu = document.createElement('div');
        menu.className = 'aa-gm-card-menu__panel';
        menu.hidden = true;

        const enlargeButton = document.createElement('button');
        enlargeButton.type = 'button';
        enlargeButton.textContent = t('gm.card.enlarge', 'Perbesar');
        enlargeButton.addEventListener('click', (event) => {
            event.stopPropagation();
            menu.hidden = true;
            openDetail(item);
        });

        const printButton = document.createElement('button');
        printButton.type = 'button';
        printButton.textContent = t('gm.detail.print_download', 'Cetak / Unduh');
        printButton.addEventListener('click', (event) => {
            event.stopPropagation();
            menu.hidden = true;
            openPrintCodeModal(item);
        });

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'is-danger';
        deleteButton.textContent = t('gm.card.delete', 'Hapus');
        deleteButton.addEventListener('click', (event) => {
            event.stopPropagation();
            menu.hidden = true;
            deleteMemoryItem(item, card);
        });

        menuButton.addEventListener('click', (event) => {
            event.stopPropagation();
            $$('.aa-gm-card-menu__panel').forEach((panel) => {
                if (panel !== menu) {
                    panel.hidden = true;
                }
            });
            menu.hidden = !menu.hidden;
        });

        menu.append(enlargeButton, printButton, deleteButton);
        menuWrap.append(menuButton, menu);
        card.append(button, menuWrap);
        return card;
    }

    function updateGalleryEmptyState() {
        if (!els.empty || !els.grid) {
            return;
        }
        const isEmpty = els.grid.children.length === 0;
        els.empty.hidden = !isEmpty;
        if (!isEmpty) {
            return;
        }
        const title = $('strong', els.empty);
        const text = $('span', els.empty);
        if (state.searchQuery) {
            if (title) {
                title.textContent = t('gm.gallery.not_found_title', 'Nama tersebut tidak ditemukan.');
            }
            if (text) {
                text.textContent = t('gm.gallery.not_found_text', 'Coba gunakan kata kunci nama yang lain.');
            }
            return;
        }
        if (title) {
            title.textContent = t('gm.gallery.empty_title', 'Belum ada memories.');
        }
        if (text) {
            text.textContent = t('gm.gallery.empty_text', 'Jadilah tamu pertama yang membagikan momen.');
        }
    }

    function splitDate(value) {
        const text = String(value || '').trim();
        const chunks = text.split(/\s+/);
        return {
            date: chunks[0] || '',
            time: chunks[1] ? chunks[1] + ' WIB' : ''
        };
    }

    function formatDuration(seconds) {
        const total = Math.max(0, Math.round(Number(seconds) || 0));
        const minutes = Math.floor(total / 60);
        const rest = total % 60;
        return String(minutes).padStart(2, '0') + ':' + String(rest).padStart(2, '0');
    }

    function setRecordTime(seconds) {
        if (els.recordTime) {
            els.recordTime.textContent = formatDuration(seconds);
        }
    }

    function normalizeWishText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim().slice(0, 500);
    }

    function updateWishCounter() {
        if (!els.wishInput) {
            return;
        }
        if (els.wishInput.value.length > 500) {
            els.wishInput.value = els.wishInput.value.slice(0, 500);
            showToast(t('gm.upload.wish_limit', 'Ucapan maksimal 500 karakter.'));
        }
        if (els.wishCount) {
            els.wishCount.textContent = String(els.wishInput.value.length);
        }
    }

    function recorderSupported() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && window.MediaRecorder);
    }

    function preferredAudioType() {
        if (!window.MediaRecorder || !MediaRecorder.isTypeSupported) {
            return '';
        }
        const types = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg;codecs=opus'];
        return types.find((type) => MediaRecorder.isTypeSupported(type)) || '';
    }

    async function toggleRecording() {
        if (state.mediaRecorder && state.mediaRecorder.state === 'recording') {
            stopRecording();
            return;
        }
        if (!recorderSupported()) {
            showToast(t('gm.audio.unsupported', 'Browser ini belum mendukung rekam suara.'));
            return;
        }

        try {
            clearAudio(false);
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const type = preferredAudioType();
            state.audioStream = stream;
            state.recordChunks = [];
            state.discardRecording = false;
            state.mediaRecorder = type ? new MediaRecorder(stream, { mimeType: type }) : new MediaRecorder(stream);
            state.recordStartedAt = Date.now();
            state.mediaRecorder.addEventListener('dataavailable', (event) => {
                if (event.data && event.data.size > 0) {
                    state.recordChunks.push(event.data);
                }
            });
            state.mediaRecorder.addEventListener('stop', finishRecording, { once: true });
            state.mediaRecorder.start();
            if (els.recorder) {
                els.recorder.classList.add('is-recording');
            }
            state.recordTimer = window.setInterval(() => {
                const seconds = Math.round((Date.now() - state.recordStartedAt) / 1000);
                setRecordTime(seconds);
                if (seconds >= 90) {
                    stopRecording();
                }
            }, 250);
        } catch (error) {
            showToast(t('gm.audio.permission', 'Izin microphone belum diberikan.'));
            stopAudioTracks();
        }
    }

    function stopRecording() {
        if (state.mediaRecorder && state.mediaRecorder.state === 'recording') {
            state.mediaRecorder.stop();
            return;
        }
        finishRecording();
    }

    function finishRecording() {
        window.clearInterval(state.recordTimer);
        state.recordTimer = null;
        if (els.recorder) {
            els.recorder.classList.remove('is-recording');
        }
        const duration = Math.max(0, Math.round((Date.now() - state.recordStartedAt) / 1000));
        if (state.discardRecording) {
            state.discardRecording = false;
            stopAudioTracks();
            return;
        }
        if (state.recordChunks.length > 0 && duration > 0) {
            const type = state.recordChunks[0].type || preferredAudioType() || 'audio/webm';
            state.audioBlob = new Blob(state.recordChunks, { type });
            state.audioDuration = duration;
            setRecordTime(duration);
            if (els.recorder) {
                els.recorder.classList.add('has-audio');
            }
            if (els.recordClear) {
                els.recordClear.hidden = false;
            }
        }
        stopAudioTracks();
    }

    function stopAudioTracks() {
        if (state.audioStream) {
            state.audioStream.getTracks().forEach((track) => track.stop());
        }
        state.audioStream = null;
        state.mediaRecorder = null;
    }

    function clearAudio(showMessage = true) {
        if (state.mediaRecorder && state.mediaRecorder.state === 'recording') {
            state.discardRecording = true;
            try {
                state.mediaRecorder.stop();
            } catch (error) {
                stopAudioTracks();
            }
        }
        stopAudioTracks();
        window.clearInterval(state.recordTimer);
        state.recordTimer = null;
        state.audioBlob = null;
        state.audioDuration = 0;
        state.recordChunks = [];
        setRecordTime(0);
        if (els.recorder) {
            els.recorder.classList.remove('is-recording', 'has-audio');
        }
        if (els.recordClear) {
            els.recordClear.hidden = true;
        }
        if (showMessage) {
            showToast(t('gm.audio.deleted', 'Voice wish dihapus.'));
        }
    }

    async function loadMemories(reset) {
        if (!cfg.isReady || !cfg.isEnabled || !els.grid || state.loading || (!state.hasMore && !reset)) {
            return;
        }
        state.loading = true;
        if (els.loader) {
            els.loader.hidden = false;
        }
        try {
            if (reset) {
                state.page = 1;
                state.hasMore = true;
                els.grid.innerHTML = '';
            }
            const params = new URLSearchParams({ page: String(state.page) });
            if (state.searchQuery) {
                params.set('q', state.searchQuery);
            }
            const data = await fetchJson(cfg.listUrl + '?' + params.toString());
            (data.items || []).forEach((item) => els.grid.appendChild(createCard(item)));
            state.hasMore = !!data.has_more;
            state.page = data.next_page || (state.page + 1);
            updateGalleryEmptyState();
            refreshFloatingWishes();
        } catch (error) {
            showToast(error.message);
        } finally {
            state.loading = false;
            if (els.loader) {
                els.loader.hidden = true;
            }
        }
    }

    function refreshFloatingWishes() {
        if (!els.floatingWishes || !els.floatingWishesTrack || !els.grid) {
            return;
        }
        const items = $$('.aa-gm-card', els.grid)
            .map((card) => ({
                id: card.dataset.memoryId || '',
                name: card.dataset.guestDisplay || '',
                wish: card.dataset.wishText || '',
                thumbnail: card.dataset.thumbnail || ''
            }))
            .filter((item) => item.wish)
            .filter((item, index, list) => {
                const key = item.id || [item.name, item.wish, item.thumbnail].join('|');
                return list.findIndex((entry) => (entry.id || [entry.name, entry.wish, entry.thumbnail].join('|')) === key) === index;
            })
            .slice(0, 10);

        if (items.length === 0) {
            els.floatingWishes.hidden = true;
            els.floatingWishesTrack.innerHTML = '';
            return;
        }

        els.floatingWishesTrack.innerHTML = '';
        items.forEach((item) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'aa-gm-floating-wish';
            chip.innerHTML = '<img alt=""><span><strong></strong><em></em></span>';
            const img = $('img', chip);
            const name = $('strong', chip);
            const wish = $('em', chip);
            if (img) {
                img.src = item.thumbnail;
                img.alt = t('gm.card.alt', 'Momen dari {name}', { name: item.name || t('gm.card.guest', 'Tamu') });
            }
            if (name) {
                name.textContent = item.name || t('gm.card.guest', 'Tamu');
            }
            if (wish) {
                wish.textContent = item.wish.length > 110 ? item.wish.slice(0, 107) + '...' : item.wish;
            }
            els.floatingWishesTrack.appendChild(chip);
        });
        els.floatingWishes.hidden = false;
    }

    function galleryAutoRefreshAllowed() {
        if (!cfg.isReady || !cfg.isEnabled || !els.grid) {
            return false;
        }
        if (state.panel !== 'gallery' || state.loading || state.refreshing || state.searchQuery) {
            return false;
        }
        if (document.visibilityState && document.visibilityState !== 'visible') {
            return false;
        }
        if (els.detailModal && !els.detailModal.hidden) {
            return false;
        }
        if (els.printCodeModal && !els.printCodeModal.hidden) {
            return false;
        }

        return true;
    }

    function currentGalleryIds() {
        return new Set($$('.aa-gm-card', els.grid).map((card) => String(card.dataset.memoryId || '')).filter(Boolean));
    }

    async function refreshLatestMemories() {
        if (!galleryAutoRefreshAllowed()) {
            return;
        }
        state.refreshing = true;
        try {
            const data = await fetchJson(cfg.listUrl + '?' + new URLSearchParams({ page: '1' }).toString());
            const existingIds = currentGalleryIds();
            const freshItems = (data.items || []).filter((item) => {
                const id = String(item && item.id || '');
                return id !== '' && !existingIds.has(id);
            });
            for (let index = freshItems.length - 1; index >= 0; index--) {
                els.grid.prepend(createCard(freshItems[index]));
            }
            updateGalleryEmptyState();
        } catch (error) {
            // Keep gallery standby mode quiet; the next interval can retry.
        } finally {
            state.refreshing = false;
        }
    }

    async function ensureFramesLoaded() {
        if (frames.length) {
            return frames;
        }
        if (!cfg.framesUrl) {
            return frames;
        }
        if (!frameLoaderPromise) {
            showStatus(t('gm.camera.loading_frame', 'Mohon tunggu, sedang mengakses frame...'));
            frameLoaderPromise = fetchJson(cfg.framesUrl)
                .then((data) => {
                    frames = normalizeFrames(Array.isArray(data.frames) ? data.frames : []);
                    state.frameIndex = frames.length > 1 ? 1 : 0;
                    framePreviewCache.clear();
                    return frames;
                })
                .catch((error) => {
                    frameLoaderPromise = null;
                    throw error;
                });
        }
        return frameLoaderPromise;
    }

    function currentFrame() {
        return frames[state.frameIndex] || frames[0] || null;
    }

    function currentSlotCount(frame) {
        const activeFrame = frame || currentFrame();
        if (!activeFrame) {
            return 1;
        }
        const count = Number(activeFrame.slot_count || (Array.isArray(activeFrame.photo_slots) ? activeFrame.photo_slots.length : 1));
        return Math.max(1, Math.min(3, count || 1));
    }

    function currentImages() {
        return state.sourceImages.slice(0, currentSlotCount());
    }

    function currentSlotRect() {
        const frame = currentFrame();
        const slots = Array.isArray(frame.photo_slots) ? frame.photo_slots : [];
        const slot = slots[state.activeSlotIndex] || slots[0] || { width: 750, height: 560 };
        return {
            width: Math.max(120, Number(slot.width || 750)),
            height: Math.max(120, Number(slot.height || 560))
        };
    }

    function filledSlotCount() {
        const max = currentSlotCount();
        let count = 0;
        for (let index = 0; index < max; index += 1) {
            if (state.sourceImages[index]) {
                count += 1;
            }
        }
        return count;
    }

    function firstEmptySlot() {
        const max = currentSlotCount();
        for (let index = 0; index < max; index += 1) {
            if (!state.sourceImages[index]) {
                return index;
            }
        }
        return -1;
    }

    function allSlotsFilled() {
        return filledSlotCount() >= currentSlotCount();
    }

    function setActiveSlot(index) {
        state.activeSlotIndex = Math.max(0, Math.min(currentSlotCount() - 1, Number(index) || 0));
        renderCaptureStep();
        renderMainPreview(true);
    }

    function openCameraForActiveSlot() {
        showStatus(t('gm.camera.opening', 'Mengakses kamera...'));
        if (state.sourceImages[state.activeSlotIndex]) {
            state.sourceImages[state.activeSlotIndex] = null;
            state.finalBlob = null;
            state.thumbBlob = null;
            state.pendingCrop = null;
            clearFrameRenderCache(els.canvas);
            renderCaptureStep();
            renderMainPreview(true);
            window.setTimeout(() => openFileInput(els.cameraInput), 140);
            return;
        }

        openFileInput(els.cameraInput);
    }

    function renderCaptureStep() {
        const frame = currentFrame();
        if (!frame) {
            if (els.captureTitle) {
                els.captureTitle.textContent = t('gm.camera.preparing', 'MENYIAPKAN FRAME');
            }
            if (els.slotStatus) {
                els.slotStatus.textContent = t('gm.camera.wait', 'Mohon tunggu...');
            }
            if (els.slotList) {
                els.slotList.innerHTML = '';
            }
            return;
        }
        const max = currentSlotCount(frame);
        const current = state.activeSlotIndex + 1;
        const isFilled = !!state.sourceImages[state.activeSlotIndex];
        syncLiveFrameDisplay(frame);
        if (els.captureTitle) {
            els.captureTitle.textContent = isFilled ? t('gm.photo.saved', 'FOTO TERSIMPAN') : t('gm.photo.title', 'AMBIL FOTO');
        }
        if (els.slotStatus) {
            els.slotStatus.textContent = t('gm.photo.status', 'Foto {current} dari {max}', { current, max });
        }
        if (els.cameraButton) {
            els.cameraButton.textContent = isFilled ? t('gm.photo.retake', 'AMBIL FOTO ULANG') : t('gm.photo.camera', 'AMBIL FOTO');
        }
        if (els.nextSlot) {
            const next = firstEmptySlot();
            els.nextSlot.hidden = !(isFilled && next >= 0 && next !== state.activeSlotIndex);
            els.nextSlot.textContent = next >= 0 ? t('gm.photo.next', 'LANJUT FOTO {number}', { number: next + 1 }) : t('gm.photo.next_default', 'LANJUT');
        }
        if (!els.slotList) {
            return;
        }
        els.slotList.innerHTML = '';
        for (let index = 0; index < max; index += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'aa-gm-slot-chip';
            if (index === state.activeSlotIndex) {
                button.classList.add('is-active');
            }
            if (state.sourceImages[index]) {
                button.classList.add('is-filled');
            }
            button.textContent = t('gm.photo.slot', 'Foto {number}', { number: index + 1 });
            button.addEventListener('click', () => setActiveSlot(index));
            els.slotList.appendChild(button);
        }
    }

    function syncLiveFrameDisplay(frame) {
        const activeFrame = frame || currentFrame();
        if (!activeFrame) {
            return;
        }
        const slotCount = currentSlotCount(activeFrame);
        const title = String(activeFrame.title || '');
        [els.canvas, els.finalCanvas].forEach((canvas) => {
            if (!canvas) {
                return;
            }
            const card = canvas.closest('.aa-gm-live-card');
            if (!card) {
                return;
            }
            card.dataset.gmFrameTitle = title;
            card.dataset.gmSlotCount = String(slotCount);
        });
    }

    async function handleFile(file, autoContinue) {
        if (!file) {
            return;
        }
        const mime = String(file.type || '').toLowerCase();
        const name = String(file.name || '').toLowerCase();
        const looksLikeImage = mime === '' ? /\.(jpe?g|png|webp|heic|heif)$/i.test(name) : /^image\//i.test(mime);
        if (!looksLikeImage) {
            showToast(t('gm.upload.invalid_type', 'Gunakan foto JPG, PNG, atau WEBP.'));
            return;
        }
        if (file.size > maxSourceImageBytes) {
            showToast(t('gm.upload.too_large', 'Ukuran foto terlalu besar. Gunakan foto maksimal 20MB.'));
            return;
        }

        try {
            showStatus(t('gm.upload.installing', 'Memasang foto...'));
            const image = await readImage(file);
            startCropAdjust(await prepareSourceImage(image, file), autoContinue);
        } catch (error) {
            showToast(error.message || t('gm.upload.read_failed', 'Foto tidak bisa dibaca.'));
        }
    }

    function startCropAdjust(img, autoContinue) {
        const slot = currentSlotRect();
        state.pendingCrop = {
            img,
            offsetX: 0,
            offsetY: 0,
            zoom: 1,
            dragging: false,
            lastX: 0,
            lastY: 0,
            slotWidth: slot.width,
            slotHeight: slot.height
        };
        state.cropAutoContinue = !!autoContinue;
        state.cropFromCamera = !!autoContinue;
        if (els.cropZoom) {
            els.cropZoom.value = '1';
        }
        if (els.cropStatus) {
            els.cropStatus.textContent = t('gm.photo.status', 'Foto {current} dari {max}', { current: state.activeSlotIndex + 1, max: currentSlotCount() });
        }
        switchStep('crop');
        renderCropCanvas();
    }

    function cropMetrics() {
        const crop = state.pendingCrop;
        if (!crop || !crop.img) {
            return null;
        }
        const iw = crop.img.naturalWidth || crop.img.width;
        const ih = crop.img.naturalHeight || crop.img.height;
        const baseScale = Math.max(crop.slotWidth / iw, crop.slotHeight / ih);
        const scale = baseScale * Math.max(1, Number(crop.zoom || 1));
        const drawWidth = iw * scale;
        const drawHeight = ih * scale;
        const baseX = (crop.slotWidth - drawWidth) / 2;
        const baseY = (crop.slotHeight - drawHeight) / 2;
        return { drawWidth, drawHeight, baseX, baseY };
    }

    function clampCrop() {
        const crop = state.pendingCrop;
        const metrics = cropMetrics();
        if (!crop || !metrics) {
            return;
        }
        const x = metrics.baseX + crop.offsetX;
        const y = metrics.baseY + crop.offsetY;
        const minX = crop.slotWidth - metrics.drawWidth;
        const minY = crop.slotHeight - metrics.drawHeight;
        const clampedX = Math.min(0, Math.max(minX, x));
        const clampedY = Math.min(0, Math.max(minY, y));
        crop.offsetX = clampedX - metrics.baseX;
        crop.offsetY = clampedY - metrics.baseY;
    }

    function renderCropCanvas() {
        const crop = state.pendingCrop;
        const canvas = els.cropCanvas;
        const metrics = cropMetrics();
        if (!crop || !canvas || !metrics) {
            return;
        }
        clampCrop();
        const freshMetrics = cropMetrics();
        canvas.width = crop.slotWidth;
        canvas.height = crop.slotHeight;
        const ctx = canvas.getContext('2d', { alpha: false });
        ctx.fillStyle = '#10180d';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(
            crop.img,
            freshMetrics.baseX + crop.offsetX,
            freshMetrics.baseY + crop.offsetY,
            freshMetrics.drawWidth,
            freshMetrics.drawHeight
        );
        ctx.strokeStyle = 'rgba(248,244,236,.92)';
        ctx.lineWidth = Math.max(3, Math.round(canvas.width * .005));
        ctx.strokeRect(1.5, 1.5, canvas.width - 3, canvas.height - 3);
        ctx.strokeStyle = 'rgba(248,244,236,.28)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(canvas.width / 3, 0);
        ctx.lineTo(canvas.width / 3, canvas.height);
        ctx.moveTo(canvas.width * 2 / 3, 0);
        ctx.lineTo(canvas.width * 2 / 3, canvas.height);
        ctx.moveTo(0, canvas.height / 3);
        ctx.lineTo(canvas.width, canvas.height / 3);
        ctx.moveTo(0, canvas.height * 2 / 3);
        ctx.lineTo(canvas.width, canvas.height * 2 / 3);
        ctx.stroke();
    }

    function cropToCanvas() {
        const crop = state.pendingCrop;
        const metrics = cropMetrics();
        if (!crop || !metrics) {
            throw new Error(t('gm.upload.not_ready', 'Foto belum siap.'));
        }
        clampCrop();
        const freshMetrics = cropMetrics();
        const canvas = document.createElement('canvas');
        canvas.width = crop.slotWidth;
        canvas.height = crop.slotHeight;
        const ctx = canvas.getContext('2d', { alpha: false });
        ctx.fillStyle = '#10180d';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(
            crop.img,
            freshMetrics.baseX + crop.offsetX,
            freshMetrics.baseY + crop.offsetY,
            freshMetrics.drawWidth,
            freshMetrics.drawHeight
        );
        canvas.dataset.gmImageId = String(Date.now()) + '-' + Math.random().toString(16).slice(2);
        return canvas;
    }

    function commitCrop() {
        try {
            state.sourceImages[state.activeSlotIndex] = cropToCanvas();
            state.pendingCrop = null;
            state.finalBlob = null;
            state.thumbBlob = null;
            const next = firstEmptySlot();
            if (next >= 0) {
                state.activeSlotIndex = next;
                renderCaptureStep();
                showToast(t('gm.upload.saved_next', 'Foto tersimpan. Lanjut ambil foto {number}.', { number: next + 1 }));
                switchStep('photo');
                if (state.cropAutoContinue) {
                    openFileInput(els.cameraInput);
                }
                return;
            }
            switchStep('details');
        } catch (error) {
            showToast(error.message || t('gm.upload.cannot_use', 'Foto belum bisa dipakai.'));
        }
    }

    function retakeCrop() {
        state.pendingCrop = null;
        switchStep('photo');
        window.setTimeout(() => {
            if (state.cropFromCamera) {
                openFileInput(els.cameraInput);
            } else {
                openFileInput(els.fileInput);
            }
        }, 120);
    }

    function readImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                URL.revokeObjectURL(img.src);
                resolve(img);
            };
            img.onerror = () => reject(new Error(t('gm.upload.read_failed', 'Foto tidak bisa dibaca.')));
            img.src = URL.createObjectURL(file);
        });
    }

    function imageFromCanvas(canvas, quality) {
        return new Promise((resolve, reject) => {
            canvas.toBlob((blob) => {
                if (!blob) {
                    reject(new Error(t('gm.upload.process_failed', 'Foto tidak bisa diproses.')));
                    return;
                }
                const img = new Image();
                const url = URL.createObjectURL(blob);
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    resolve(img);
                };
                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject(new Error(t('gm.upload.process_failed', 'Foto tidak bisa diproses.')));
                };
                img.src = url;
            }, 'image/jpeg', quality);
        });
    }

    async function prepareSourceImage(img, file) {
        const width = Math.max(1, img.naturalWidth || img.width || 0);
        const height = Math.max(1, img.naturalHeight || img.height || 0);
        const longestSide = Math.max(width, height);
        const shouldResize = longestSide > maxPreparedImageSide || (file && file.size > 5 * 1024 * 1024);
        if (!shouldResize) {
            return img;
        }

        const scale = Math.min(1, maxPreparedImageSide / longestSide);
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(width * scale));
        canvas.height = Math.max(1, Math.round(height * scale));
        const ctx = canvas.getContext('2d', { alpha: false });
        if (!ctx) {
            return img;
        }
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        return imageFromCanvas(canvas, .86);
    }

    function clearFrameRenderCache(canvas) {
        if (canvas && typeof window.AdaAcaraGuestMemoryClearFrameRender === 'function') {
            window.AdaAcaraGuestMemoryClearFrameRender(canvas);
        }
    }

    async function renderMainPreview(force = false) {
        if (!els.canvas) {
            return;
        }
        const renderId = ++mainPreviewRenderId;
        if (force) {
            clearFrameRenderCache(els.canvas);
        }
        syncLiveFrameDisplay(currentFrame());
        await renderFrameToCanvas(els.canvas, currentFrame(), currentImages());
        if (renderId !== mainPreviewRenderId && force) {
            clearFrameRenderCache(els.canvas);
        }
    }

    async function renderFinalPreview(force = false) {
        if (!els.finalCanvas) {
            return;
        }
        const renderId = ++finalPreviewRenderId;
        if (force) {
            clearFrameRenderCache(els.finalCanvas);
        }
        syncLiveFrameDisplay(currentFrame());
        await renderFrameToCanvas(els.finalCanvas, currentFrame(), currentImages());
        if (renderId !== finalPreviewRenderId) {
            return;
        }
        if (allSlotsFilled()) {
            state.finalBlob = await compressCanvas(els.finalCanvas, 1200 * 1024, .9);
            state.thumbBlob = await makeThumb(els.finalCanvas);
        }
    }

    function renderFramePicker() {
        if (!els.framePreview || !els.frameDots) {
            return;
        }
        if (!frames.length) {
            els.framePreview.innerHTML = '<div class="aa-gm-frame-empty">' + missingFrameMessage + '</div>';
            els.frameDots.innerHTML = '';
            if (els.frameInfo) {
                els.frameInfo.textContent = t('gm.frame.unavailable', 'belum tersedia');
            }
            return;
        }
        const slideDirection = state.frameSlideDirection || 0;
        state.frameSlideDirection = 0;
        const pickerRenderId = ++framePickerRenderId;
        let currentRenderPromise = Promise.resolve();
        els.framePreview.classList.remove('is-slide-next', 'is-slide-prev');
        const hasExistingCarousel = !!els.framePreview.querySelector('.aa-gm-frame-carousel');
        if (slideDirection !== 0) {
            els.framePreview.classList.add('is-rendering');
        }
        const carousel = document.createElement('div');
        carousel.className = 'aa-gm-frame-carousel';
        [-1, 0, 1].forEach((offset) => {
            const index = (state.frameIndex + offset + frames.length) % frames.length;
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'aa-gm-frame-card';
            item.classList.add(offset === 0 ? 'is-current' : (offset < 0 ? 'is-prev' : 'is-next'));
            item.setAttribute('aria-label', frames[index].title || 'Frame');
            const canvas = document.createElement('canvas');
            canvas.className = 'aa-gm-frame-canvas';
            canvas.style.width = 'auto';
            canvas.style.maxWidth = Number(frames[index].slot_count || 0) === 3 ? '130px' : '100%';
            canvas.style.height = 'auto';
            canvas.style.display = 'block';
            prepareFramePickerCanvas(canvas, frames[index]);
            item.appendChild(canvas);
            item.addEventListener('click', () => {
                if (offset === 0) {
                    selectFrame();
                    return;
                }
                state.frameSlideDirection = offset > 0 ? 1 : -1;
                state.frameIndex = index;
                renderFramePicker();
            });
            carousel.appendChild(item);
            const renderPromise = renderFramePickerCanvas(canvas, item, frames[index]);
            if (offset === 0) {
                currentRenderPromise = renderPromise;
            }
        });
        if (!hasExistingCarousel) {
            els.framePreview.replaceChildren(carousel);
        }
        currentRenderPromise.finally(() => {
            if (framePickerRenderId === pickerRenderId) {
                if (hasExistingCarousel) {
                    els.framePreview?.replaceChildren(carousel);
                    if (slideDirection !== 0) {
                        els.framePreview?.classList.add(slideDirection > 0 ? 'is-slide-next' : 'is-slide-prev');
                        window.setTimeout(() => {
                            els.framePreview?.classList.remove('is-slide-next', 'is-slide-prev');
                        }, 420);
                    }
                }
                els.framePreview?.classList.remove('is-rendering');
            }
        });
        if (!hasExistingCarousel && slideDirection !== 0) {
            els.framePreview.classList.add(slideDirection > 0 ? 'is-slide-next' : 'is-slide-prev');
            window.setTimeout(() => {
                els.framePreview?.classList.remove('is-slide-next', 'is-slide-prev');
            }, 420);
        }
        if (els.frameInfo) {
            const frame = currentFrame();
            const count = currentSlotCount(frame);
            els.frameInfo.textContent = t(count === 1 ? 'gm.frame.info' : 'gm.frame.info_plural', '{count} foto', { count });
        }

        els.frameDots.innerHTML = '';
        frames.forEach((frame, index) => {
            const dot = document.createElement('span');
            dot.className = index === state.frameIndex ? 'is-active' : '';
            dot.setAttribute('aria-label', frame.title || 'Frame');
            els.frameDots.appendChild(dot);
        });
    }

    function moveFrame(direction) {
        state.frameSlideDirection = direction > 0 ? 1 : -1;
        state.frameIndex = (state.frameIndex + direction + frames.length) % frames.length;
        renderFramePicker();
    }

    function framePreviewCacheKey(frame) {
        if (!frame) {
            return '';
        }
        const objects = frame.fabric && Array.isArray(frame.fabric.objects) ? frame.fabric.objects : [];
        const source = frame.source_id || frame.sourceId || frame.id || frame.title || 'frame';
        const version = frame.updated_at || frame.updatedAt || frame.version || '';
        const fabricSize = frame.fabric ? JSON.stringify(frame.fabric).length : 0;
        return [
            source,
            version,
            frame.renderer || '',
            frame.width || 1080,
            frame.height || 1350,
            objects.length,
            fabricSize
        ].join(':');
    }

    function framePickerThumbSize(frame) {
        const width = Math.max(1, Number(frame && frame.width ? frame.width : 1080));
        const height = Math.max(1, Number(frame && frame.height ? frame.height : 1350));
        const scale = Math.min(1, 560 / width, 920 / height);
        return {
            width: Math.max(1, Math.round(width * scale)),
            height: Math.max(1, Math.round(height * scale))
        };
    }

    function prepareFramePickerCanvas(canvas, frame) {
        if (!canvas) {
            return;
        }
        const size = framePickerThumbSize(frame);
        canvas.width = size.width;
        canvas.height = size.height;
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }
        ctx.clearRect(0, 0, size.width, size.height);
        ctx.fillStyle = 'rgba(248, 244, 236, .08)';
        ctx.fillRect(0, 0, size.width, size.height);
    }

    function drawCachedFramePreview(canvas, entry) {
        if (!canvas || !entry || !entry.image) {
            return false;
        }
        canvas.width = entry.width;
        canvas.height = entry.height;
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return false;
        }
        ctx.clearRect(0, 0, entry.width, entry.height);
        ctx.drawImage(entry.image, 0, 0, entry.width, entry.height);
        return true;
    }

    async function makeFramePreviewEntry(canvas) {
        const sourceWidth = canvas.width;
        const sourceHeight = canvas.height;
        const scale = Math.min(1, 560 / Math.max(1, sourceWidth), 920 / Math.max(1, sourceHeight));
        const width = Math.max(1, Math.round(sourceWidth * scale));
        const height = Math.max(1, Math.round(sourceHeight * scale));
        const thumb = document.createElement('canvas');
        thumb.width = width;
        thumb.height = height;
        const ctx = thumb.getContext('2d');
        if (!ctx) {
            return null;
        }
        ctx.clearRect(0, 0, width, height);
        ctx.drawImage(canvas, 0, 0, width, height);
        const image = new Image();
        image.decoding = 'async';
        image.src = thumb.toDataURL('image/png');
        if (typeof image.decode === 'function') {
            try {
                await image.decode();
            } catch (error) {
                // Browser tertentu tetap bisa memakai image setelah onload meski decode ditolak.
            }
        } else if (!image.complete) {
            await new Promise((resolve) => {
                image.onload = resolve;
                image.onerror = resolve;
            });
        }
        return { width, height, image };
    }

    async function renderFramePickerCanvas(canvas, item, frame) {
        if (!canvas || !frame) {
            return;
        }
        const key = framePreviewCacheKey(frame);
        canvas.dataset.gmFramePreviewKey = key;
        const cached = framePreviewCache.get(key);
        if (cached && cached.status === 'ready' && drawCachedFramePreview(canvas, cached.entry)) {
            return;
        }

        if (item) {
            item.classList.add('is-loading');
        }

        if (cached && cached.status === 'pending') {
            try {
                const entry = await cached.promise;
                if (canvas.dataset.gmFramePreviewKey === key) {
                    drawCachedFramePreview(canvas, entry);
                }
            } catch (error) {
                console.warn('[Guest Memories] Preview frame belum siap.', error);
            } finally {
                if (item) {
                    item.classList.remove('is-loading');
                }
            }
            return;
        }

        const promise = (async () => {
            const renderCanvas = document.createElement('canvas');
            await renderFrameToCanvas(renderCanvas, frame, []);
            const entry = await makeFramePreviewEntry(renderCanvas);
            if (!entry) {
                throw new Error('Preview frame tidak bisa dibuat.');
            }
            return entry;
        })();

        framePreviewCache.set(key, { status: 'pending', promise });
        try {
            const entry = await promise;
            framePreviewCache.set(key, { status: 'ready', entry });
            if (canvas.dataset.gmFramePreviewKey === key) {
                drawCachedFramePreview(canvas, entry);
            }
        } catch (error) {
            framePreviewCache.delete(key);
            console.warn('[Guest Memories] Render preview frame gagal.', error);
        } finally {
            if (item) {
                item.classList.remove('is-loading');
            }
        }
    }

    async function selectFrame() {
        try {
            await ensureFramesLoaded();
        } catch (error) {
            showToast(error.message || missingFrameMessage, 7000);
            return;
        }
        if (!frames.length) {
            showToast(missingFrameMessage);
            return;
        }
        showStatus(t('gm.camera.loading_frame', 'Mohon tunggu, sedang mengakses frame...'));
        state.sourceImages = [];
        state.activeSlotIndex = 0;
        state.finalBlob = null;
        state.thumbBlob = null;
        state.pendingCrop = null;
        state.cropAutoContinue = false;
        state.cropFromCamera = true;
        clearAudio(false);
        switchStep('photo');
        window.setTimeout(() => openFileInput(els.cameraInput), 160);
    }

    function drawMissingFrameMessage(ctx, width, height) {
        if (!ctx) {
            return;
        }
        ctx.save();
        ctx.fillStyle = '#212020';
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = 'rgba(248, 244, 236, .92)';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = '700 ' + Math.max(28, Math.round(width * .045)) + 'px "Plus Jakarta Sans", Arial, sans-serif';
        const maxWidth = width * .72;
        const words = missingFrameMessage.split(' ');
        const lines = [];
        let current = '';
        words.forEach((word) => {
            const test = current ? current + ' ' + word : word;
            if (ctx.measureText(test).width > maxWidth && current) {
                lines.push(current);
                current = word;
                return;
            }
            current = test;
        });
        if (current) {
            lines.push(current);
        }
        const lineHeight = Math.max(42, Math.round(width * .065));
        const startY = (height / 2) - ((lines.length - 1) * lineHeight / 2);
        lines.forEach((line, index) => {
            ctx.fillText(line, width / 2, startY + (index * lineHeight));
        });
        ctx.restore();
    }

    async function renderFrameToCanvas(canvas, frame, sourceImages) {
        if (!canvas || !frame) {
            return;
        }
        const width = Number(frame.width || 1080);
        const height = Number(frame.height || 1350);
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d', { alpha: false });
        const accent = frame.accent || '#31401f';
        const paper = frame.paper || '#f8f4ea';
        const images = Array.isArray(sourceImages) ? sourceImages : (sourceImages ? [sourceImages] : []);

        ctx.clearRect(0, 0, width, height);
        if (frame.renderer === 'fabric-page' && frame.fabric && Array.isArray(frame.fabric.objects)) {
            await waitForEditorFrameFonts(frame);
            if (typeof window.AdaAcaraGuestMemoryRenderFrame === 'function') {
                const rendered = await window.AdaAcaraGuestMemoryRenderFrame(canvas, frame, images);
                if (rendered) {
                    return;
                }
            }
            if (await drawFabricEditorFrameToCanvas(canvas, frame, images)) {
                return;
            }
            drawMissingFrameMessage(ctx, width, height);
            console.warn('[Guest Memories] Frame editor belum bisa dirender. Jalur frame manual dinonaktifkan.');
            return;
        }
        if (frame.style === 'duo_stack') {
            drawDuoStackFrame(ctx, width, height, accent, paper, images);
        } else if (frame.style === 'duo_horizontal') {
            drawDuoHorizontalFrame(ctx, width, height, accent, paper, images);
        } else if (frame.style === 'trio_stack') {
            drawTrioStackFrame(ctx, width, height, accent, paper, images);
        } else if (frame.style === 'trio_horizontal') {
            drawTrioHorizontalFrame(ctx, width, height, accent, paper, images);
        } else if (frame.style === 'duo') {
            drawDuoFrame(ctx, width, height, accent, paper, images[0]);
        } else if (frame.style === 'square') {
            drawSquareFrame(ctx, width, height, accent, paper, images[0]);
        } else {
            drawClassicFrame(ctx, width, height, accent, paper, images[0]);
        }
    }

    async function drawEditorFrame(ctx, frame, images) {
        const width = ctx.canvas.width;
        const height = ctx.canvas.height;
        const fabric = frame.fabric || {};
        ctx.fillStyle = safeCanvasColor(fabric.backgroundColor || fabric.background || '#ffffff', '#ffffff');
        ctx.fillRect(0, 0, width, height);

        const objects = Array.isArray(fabric.objects) ? fabric.objects.slice(0, 160) : [];
        const backgroundImage = fabric.backgroundImage || null;
        if (backgroundImage) {
            await drawEditorImage(ctx, backgroundImage, true);
        }
        const slotOrder = editorPhotoSlotOrder(objects);
        const canonicalSlots = editorFrameSlotRects(frame);
        let canonicalSlotsDrawn = false;
        for (const object of objects) {
            if (object && object.customType === 'photobooth-photo-slot' && canonicalSlots.length) {
                if (!canonicalSlotsDrawn) {
                    drawEditorCanonicalPhotoSlots(ctx, canonicalSlots, images);
                    canonicalSlotsDrawn = true;
                }
                continue;
            }
            await drawEditorObject(ctx, object, frame, images, slotOrder);
        }
        if (canonicalSlots.length && !canonicalSlotsDrawn) {
            drawEditorCanonicalPhotoSlots(ctx, canonicalSlots, images);
        }
    }

    function coverCanvasForSlotImage(img, slot) {
        const width = Math.max(1, Math.round(slot.width));
        const height = Math.max(1, Math.round(slot.height));
        const coverCanvas = document.createElement('canvas');
        const ctx = coverCanvas.getContext('2d');
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

    function staticCanvasSlotMetrics(staticCanvas, customType) {
        let rawIndex = 0;
        const objects = staticCanvas && typeof staticCanvas.getObjects === 'function' ? staticCanvas.getObjects() : [];
        return sortSlotsByVisualPosition(objects.map((object) => {
            if (!object || object.customType !== customType) {
                return null;
            }
            const metric = slotMetric(object, rawIndex);
            rawIndex += 1;
            return metric;
        }).filter(Boolean));
    }

    function canonicalFrameSlotsForStaticCanvas(frame, staticCanvas) {
        const objectSlots = staticCanvasSlotMetrics(staticCanvas, 'photobooth-photo-slot').slice(0, 3);
        const metadataSlots = Array.isArray(frame?.photo_slots) ? frame.photo_slots : [];
        if (!metadataSlots.length) {
            return objectSlots;
        }
        return metadataSlots.slice(0, 3).map((slot, index) => {
            const styleSource = objectSlots[index] || objectSlots[0] || {};
            return {
                object: styleSource.object || null,
                rawIndex: index,
                orderNumber: index + 1,
                x: Number(slot.x ?? slot.left ?? 0) || 0,
                y: Number(slot.y ?? slot.top ?? 0) || 0,
                width: Math.max(40, Number(slot.width || 0) || 0),
                height: Math.max(40, Number(slot.height || 0) || 0),
                radius: Math.max(0, Number(slot.radius ?? slot.rx ?? slot.ry ?? 0) || 0),
                angle: Number(styleSource.angle || 0) || 0,
                opacity: styleSource.opacity == null ? 1 : styleSource.opacity,
                styleSource: styleSource.object || null
            };
        });
    }

    function placeholderObjectForSlot(fabricLib, slot) {
        const source = slot.styleSource || {};
        return new fabricLib.Rect({
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

    function replaceStaticCanvasSlotsWithImages(staticCanvas, fabricLib, frame, images) {
        const sourceImages = Array.isArray(images) ? images : [];
        if (!staticCanvas || !fabricLib || !fabricLib.Image) {
            return;
        }
        const originalSlots = staticCanvasSlotMetrics(staticCanvas, 'photobooth-photo-slot').slice(0, 3);
        const slots = canonicalFrameSlotsForStaticCanvas(frame, staticCanvas).slice(0, 3);
        const labels = staticCanvasSlotMetrics(staticCanvas, 'photobooth-photo-slot-label').slice(0, 3);
        const baseIndex = originalSlots.length && originalSlots[0].object
            ? Math.max(0, staticCanvas.getObjects().indexOf(originalSlots[0].object))
            : staticCanvas.getObjects().length;
        originalSlots.forEach((slot) => {
            if (slot.object) {
                staticCanvas.remove(slot.object);
            }
        });
        labels.forEach((label) => {
            if (label.object) {
                staticCanvas.remove(label.object);
            }
        });
        slots.forEach((slot, index) => {
            const img = sourceImages[index];
            let object = null;
            if (img) {
                const slotCanvas = coverCanvasForSlotImage(img, slot);
                object = new fabricLib.Image(slotCanvas, {
                    left: slot.x,
                    top: slot.y,
                    originX: 'left',
                    originY: 'top',
                    scaleX: slot.width / Math.max(1, slotCanvas.width),
                    scaleY: slot.height / Math.max(1, slotCanvas.height),
                    angle: slot.angle,
                    opacity: slot.opacity,
                    selectable: false,
                    evented: false,
                    hasControls: false,
                    hasBorders: false,
                    customType: 'photobooth-rendered-photo'
                });
            } else {
                object = placeholderObjectForSlot(fabricLib, slot);
            }
            staticCanvas.insertAt(object, baseIndex + index, false);
            if (typeof object.setCoords === 'function') {
                object.setCoords();
            }
        });
    }

    async function drawFabricEditorFrameToCanvas(canvas, frame, images) {
        if (!canvas || !frame || !frame.fabric) {
            return false;
        }
        const fabricLib = await loadGuestMemoryFabric();
        if (!fabricLib || !fabricLib.StaticCanvas) {
            return false;
        }

        const width = canvas.width;
        const height = canvas.height;
        const fabricData = frame.fabric || {};
        const offscreen = document.createElement('canvas');
        offscreen.width = width;
        offscreen.height = height;
        const staticCanvas = new fabricLib.StaticCanvas(offscreen, {
            width,
            height,
            renderOnAddRemove: false,
            enableRetinaScaling: false
        });

        try {
            const objects = cloneFabricFrameObjectsForRender(frame);
            const payload = {
                version: fabricData.version || '5.3.0',
                objects,
                background: fabricData.backgroundColor || fabricData.background || '#ffffff',
                backgroundColor: fabricData.backgroundColor || fabricData.background || '#ffffff',
                backgroundImage: fabricData.backgroundImage || null
            };
            await loadStaticCanvasJson(staticCanvas, payload);
            staticCanvas.getObjects().forEach((object) => {
                object.selectable = false;
                object.evented = false;
                object.dirty = true;
                if (typeof object.setCoords === 'function') {
                    object.setCoords();
                }
            });
            replaceStaticCanvasSlotsWithImages(staticCanvas, fabricLib, frame, images);
            staticCanvas.renderAll();
            const ctx = canvas.getContext('2d', { alpha: false });
            ctx.clearRect(0, 0, width, height);
            ctx.drawImage(offscreen, 0, 0, width, height);
            return true;
        } catch (error) {
            return false;
        } finally {
            try {
                staticCanvas.dispose();
            } catch (error) {
                // keep fallback renderer available when Fabric cleanup is not supported
            }
        }
    }

    function loadGuestMemoryFabric() {
        if (window.fabric && window.fabric.StaticCanvas) {
            return Promise.resolve(window.fabric);
        }
        if (fabricLoaderPromise) {
            return fabricLoaderPromise;
        }
        fabricLoaderPromise = new Promise((resolve) => {
            const existing = document.querySelector('script[data-aa-gm-fabric-loader]');
            if (existing) {
                existing.addEventListener('load', () => resolve(window.fabric || null), { once: true });
                existing.addEventListener('error', () => resolve(null), { once: true });
                return;
            }
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js';
            script.async = true;
            script.defer = true;
            script.dataset.aaGmFabricLoader = '1';
            script.onload = () => resolve(window.fabric || null);
            script.onerror = () => resolve(null);
            document.head.appendChild(script);
        });
        return fabricLoaderPromise;
    }

    function loadStaticCanvasJson(staticCanvas, payload) {
        return new Promise((resolve, reject) => {
            staticCanvas.loadFromJSON(payload, resolve, (object, instance) => {
                if (instance) {
                    instance.selectable = false;
                    instance.evented = false;
                }
            });
            window.setTimeout(() => {
                if (staticCanvas && staticCanvas.getObjects) {
                    resolve();
                } else {
                    reject(new Error('Frame belum bisa dirender.'));
                }
            }, 2200);
        });
    }

    function cloneFabricFrameObjectsForRender(frame) {
        const source = frame && frame.fabric && Array.isArray(frame.fabric.objects) ? frame.fabric.objects : [];
        let objects = [];
        try {
            objects = JSON.parse(JSON.stringify(source));
        } catch (error) {
            objects = [];
        }
        return objects
            .filter(Boolean)
            .slice(0, 160)
            .map((object) => {
                normalizeFabricObjectForStaticRender(object);
                return object;
            });
    }

    function normalizeFabricObjectForStaticRender(object) {
        if (!object || typeof object !== 'object') {
            return;
        }
        const type = String(object.type || '').toLowerCase();
        if ((type === 'textbox' || type === 'i-text' || type === 'text') && Array.isArray(object.styles)) {
            object.styles = {};
        }
        if (type === 'image' && object.src && !object.crossOrigin) {
            object.crossOrigin = 'anonymous';
        }
        const children = Array.isArray(object.objects) ? object.objects : (Array.isArray(object._objects) ? object._objects : []);
        children.forEach(normalizeFabricObjectForStaticRender);
    }

    async function drawEditorObject(ctx, object, frame, images, slotOrder) {
        if (!object || object.visible === false || object.opacity === 0) {
            return;
        }
        if (object.customType === 'photobooth-photo-slot-label') {
            return;
        }
        if (object.customType === 'photobooth-photo-slot') {
            drawEditorPhotoSlot(ctx, object, images, slotOrder);
            return;
        }

        const type = String(object.type || '').toLowerCase();
        if (type === 'rect') {
            drawEditorRect(ctx, object);
            return;
        }
        if (type === 'textbox' || type === 'i-text' || type === 'text') {
            drawEditorText(ctx, object);
            return;
        }
        if (type === 'image') {
            await drawEditorImage(ctx, object);
        }
    }

    function editorPhotoSlotOrder(objects) {
        const slotObjects = objects
            .filter((object) => object && object.customType === 'photobooth-photo-slot')
            .sort((a, b) => {
                const topDiff = Number(a.top || 0) - Number(b.top || 0);
                if (Math.abs(topDiff) > 8) {
                    return topDiff;
                }
                return Number(a.left || 0) - Number(b.left || 0);
            });
        const order = new Map();
        slotObjects.slice(0, 3).forEach((object, index) => {
            order.set(object, index);
        });

        return order;
    }

    function editorFrameSlotRects(frame) {
        const slots = Array.isArray(frame?.photo_slots) ? frame.photo_slots : [];
        return slots.slice(0, currentSlotCount(frame)).map((slot, index) => {
            const width = Math.max(1, Number(slot.width || 1));
            const height = Math.max(1, Number(slot.height || 1));
            return {
                index,
                x: Number(slot.x || 0),
                y: Number(slot.y || 0),
                width,
                height,
                radius: Math.max(0, Number(slot.radius || 0)),
                fill: slot.fill || '#10180d'
            };
        }).filter((slot) => slot.width > 1 && slot.height > 1);
    }

    function drawEditorCanonicalPhotoSlots(ctx, slots, images) {
        slots.forEach((slot) => {
            drawEditorPhotoSlotRect(ctx, slot, images[slot.index]);
        });
    }

    function drawEditorPhotoImagesOnSlots(ctx, slots, images) {
        slots.forEach((slot) => {
            const img = images[slot.index];
            if (!img) {
                return;
            }
            ctx.save();
            if (slot.radius > 0) {
                roundRect(ctx, slot.x, slot.y, slot.width, slot.height, slot.radius);
                ctx.clip();
            } else {
                ctx.beginPath();
                ctx.rect(slot.x, slot.y, slot.width, slot.height);
                ctx.clip();
            }
            drawCover(ctx, img, slot.x, slot.y, slot.width, slot.height);
            ctx.restore();
        });
    }

    function drawEditorPhotoSlotRect(ctx, slot, img) {
        ctx.save();
        if (slot.radius > 0) {
            roundRect(ctx, slot.x, slot.y, slot.width, slot.height, slot.radius);
            ctx.clip();
        } else {
            ctx.beginPath();
            ctx.rect(slot.x, slot.y, slot.width, slot.height);
            ctx.clip();
        }
        ctx.fillStyle = safeCanvasColor(slot.fill || '#10180d', '#10180d');
        ctx.fillRect(slot.x, slot.y, slot.width, slot.height);
        if (img) {
            drawCover(ctx, img, slot.x, slot.y, slot.width, slot.height);
        } else {
            ctx.fillStyle = 'rgba(247,244,236,.56)';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = '600 ' + Math.max(18, Math.round(slot.width * .055)) + 'px "Plus Jakarta Sans", Arial, sans-serif';
            ctx.fillText('PHOTO ' + (slot.index + 1), slot.x + slot.width / 2, slot.y + slot.height / 2);
        }
        ctx.restore();
    }

    function editorObjectMetrics(object) {
        const rawWidth = Math.max(1, Number(object.width || 1));
        const rawHeight = Math.max(1, Number(object.height || 1));
        const scaleX = Number(object.scaleX || 1);
        const scaleY = Number(object.scaleY || 1);
        const width = rawWidth * scaleX;
        const height = rawHeight * scaleY;
        const originX = object.originX || 'left';
        const originY = object.originY || 'top';
        const x = Number(object.left || 0);
        const y = Number(object.top || 0);
        const drawX = originX === 'center' ? -width / 2 : (originX === 'right' ? -width : 0);
        const drawY = originY === 'center' ? -height / 2 : (originY === 'bottom' ? -height : 0);

        return {
            x,
            y,
            width,
            height,
            drawX,
            drawY,
            angle: Number(object.angle || 0) * Math.PI / 180,
            opacity: Math.max(0, Math.min(1, Number(object.opacity ?? 1))),
            scaleX,
            scaleY
        };
    }

    function withEditorTransform(ctx, object, callback) {
        const metrics = editorObjectMetrics(object);
        ctx.save();
        ctx.globalAlpha *= metrics.opacity;
        ctx.translate(metrics.x, metrics.y);
        if (metrics.angle) {
            ctx.rotate(metrics.angle);
        }
        callback(metrics);
        ctx.restore();
    }

    function drawEditorPhotoSlot(ctx, object, images, slotOrder) {
        const slotIndex = slotOrder && slotOrder.has(object)
            ? slotOrder.get(object)
            : Math.max(0, Number(object.aaPhotoboothSlotIndex || 1) - 1);
        const img = images[slotIndex];
        withEditorTransform(ctx, object, (metrics) => {
            const radius = Math.max(0, Number(object.rx || object.ry || 0) * Math.max(metrics.scaleX, metrics.scaleY));
            if (radius > 0) {
                roundRect(ctx, metrics.drawX, metrics.drawY, metrics.width, metrics.height, radius);
                ctx.clip();
            } else {
                ctx.beginPath();
                ctx.rect(metrics.drawX, metrics.drawY, metrics.width, metrics.height);
                ctx.clip();
            }
            ctx.fillStyle = safeCanvasColor(object.fill || '#10180d', '#10180d');
            ctx.fillRect(metrics.drawX, metrics.drawY, metrics.width, metrics.height);
            if (img) {
                drawCover(ctx, img, metrics.drawX, metrics.drawY, metrics.width, metrics.height);
            } else {
                ctx.fillStyle = 'rgba(247,244,236,.56)';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = '600 ' + Math.max(22, Math.round(metrics.width * .055)) + 'px "Plus Jakarta Sans", Arial, sans-serif';
                ctx.fillText('PHOTO ' + (slotIndex + 1), metrics.drawX + metrics.width / 2, metrics.drawY + metrics.height / 2);
            }
        });
    }

    function drawEditorRect(ctx, object) {
        withEditorTransform(ctx, object, (metrics) => {
            const radius = Math.max(0, Number(object.rx || object.ry || 0) * Math.max(metrics.scaleX, metrics.scaleY));
            const fill = safeCanvasColor(object.fill, '');
            const stroke = safeCanvasColor(object.stroke, '');
            if (fill) {
                ctx.fillStyle = fill;
                if (radius > 0) {
                    roundRect(ctx, metrics.drawX, metrics.drawY, metrics.width, metrics.height, radius);
                    ctx.fill();
                } else {
                    ctx.fillRect(metrics.drawX, metrics.drawY, metrics.width, metrics.height);
                }
            }
            if (stroke) {
                ctx.strokeStyle = stroke;
                ctx.lineWidth = Math.max(1, Number(object.strokeWidth || 1) * Math.max(metrics.scaleX, metrics.scaleY));
                if (Array.isArray(object.strokeDashArray)) {
                    ctx.setLineDash(object.strokeDashArray.map((value) => Number(value) || 0));
                }
                if (radius > 0) {
                    roundRect(ctx, metrics.drawX, metrics.drawY, metrics.width, metrics.height, radius);
                    ctx.stroke();
                } else {
                    ctx.strokeRect(metrics.drawX, metrics.drawY, metrics.width, metrics.height);
                }
                ctx.setLineDash([]);
            }
        });
    }

    function drawEditorText(ctx, object) {
        const text = String(object.text || '');
        if (!text) {
            return;
        }
        withEditorTransform(ctx, object, (metrics) => {
            const fontSize = Math.max(8, Number(object.fontSize || 32) * Math.max(metrics.scaleY, .01));
            const weight = object.fontWeight || '400';
            const family = object.fontFamily || 'Plus Jakarta Sans';
            const rawAlign = object.textAlign || 'left';
            const align = rawAlign === 'justify' ? (object.originX === 'center' ? 'center' : 'left') : rawAlign;
            const fill = safeCanvasColor(object.fill, '#111827');
            const lineHeight = fontSize * Math.max(.9, Number(object.lineHeight || 1.16));
            const maxWidth = Math.max(20, metrics.width || Number(object.width || 300));
            const charSpacing = Number(object.charSpacing || 0);
            const lines = wrapCanvasText(ctx, text, maxWidth, fontSize, weight, family);
            let startY = metrics.drawY + fontSize * .75;

            if (object.originY === 'center') {
                startY = -((lines.length - 1) * lineHeight) / 2;
            }

            ctx.fillStyle = fill;
            ctx.textAlign = align;
            ctx.textBaseline = 'alphabetic';
            ctx.font = weight + ' ' + fontSize + 'px "' + String(family).replace(/"/g, '') + '", "Plus Jakarta Sans", Arial, sans-serif';
            lines.forEach((line, index) => {
                const y = startY + (index * lineHeight);
                if (charSpacing) {
                    drawTextWithCharSpacing(ctx, line, metrics.drawX, y, maxWidth, fontSize, charSpacing, align);
                    return;
                }
                const textX = align === 'center' ? metrics.drawX + maxWidth / 2 : (align === 'right' ? metrics.drawX + maxWidth : metrics.drawX);
                ctx.fillText(line, textX, y, maxWidth);
            });
        });
    }

    function drawTextWithCharSpacing(ctx, text, x, y, maxWidth, fontSize, charSpacing, align) {
        const chars = Array.from(String(text || ''));
        if (!chars.length) {
            return;
        }
        const spacing = fontSize * charSpacing / 1000;
        const width = chars.reduce((total, char, index) => {
            return total + ctx.measureText(char).width + (index < chars.length - 1 ? spacing : 0);
        }, 0);
        let cursor = x;
        if (align === 'center') {
            cursor = x + (maxWidth - width) / 2;
        } else if (align === 'right') {
            cursor = x + maxWidth - width;
        }

        ctx.save();
        ctx.textAlign = 'left';
        chars.forEach((char, index) => {
            ctx.fillText(char, cursor, y);
            cursor += ctx.measureText(char).width + (index < chars.length - 1 ? spacing : 0);
        });
        ctx.restore();
    }

    async function drawEditorImage(ctx, object, coverCanvas) {
        const src = editorImageSource(object);
        if (!src) {
            return;
        }
        const img = await loadFrameImage(src);
        if (!img) {
            return;
        }
        if (coverCanvas) {
            drawCover(ctx, img, 0, 0, ctx.canvas.width, ctx.canvas.height);
            return;
        }
        withEditorTransform(ctx, object, (metrics) => {
            ctx.drawImage(img, metrics.drawX, metrics.drawY, metrics.width, metrics.height);
        });
    }

    function editorImageSource(object) {
        if (typeof object === 'string') {
            return object;
        }
        if (!object) {
            return '';
        }

        return object.src || object._src || object.crossOriginSrc || (object._element && object._element.src) || '';
    }

    function wrapCanvasText(ctx, text, maxWidth, fontSize, weight, family) {
        ctx.font = weight + ' ' + fontSize + 'px "' + String(family).replace(/"/g, '') + '", "Plus Jakarta Sans", Arial, sans-serif';
        const sourceLines = String(text).split(/\r?\n/);
        const lines = [];
        sourceLines.forEach((sourceLine) => {
            const words = sourceLine.split(/\s+/).filter(Boolean);
            if (!words.length) {
                lines.push('');
                return;
            }
            let line = '';
            words.forEach((word) => {
                const next = line ? line + ' ' + word : word;
                if (line && ctx.measureText(next).width > maxWidth) {
                    lines.push(line);
                    line = word;
                } else {
                    line = next;
                }
            });
            lines.push(line);
        });

        return lines.slice(0, 12);
    }

    function waitForEditorFrameFonts(frame) {
        if (!document.fonts || !document.fonts.load || !frame || !frame.fabric) {
            return Promise.resolve();
        }
        const families = collectEditorFrameFontFamilies(frame.fabric);
        if (!families.length) {
            return Promise.resolve();
        }
        const key = families.join('|');
        if (frameFontWaitCache.has(key)) {
            return frameFontWaitCache.get(key);
        }
        const promise = new Promise((resolve) => {
            let done = false;
            const finish = () => {
                if (done) {
                    return;
                }
                done = true;
                resolve();
            };
            const loads = families.map((family) => {
                const safeFamily = String(family).replace(/"/g, '');
                return document.fonts.load('400 32px "' + safeFamily + '"').catch(() => null);
            });
            Promise.all(loads)
                .then(() => document.fonts.ready)
                .then(() => window.setTimeout(finish, 80))
                .catch(finish);
            window.setTimeout(finish, 1400);
        });
        frameFontWaitCache.set(key, promise);
        return promise;
    }

    function collectEditorFrameFontFamilies(fabricData) {
        const families = new Set(['Plus Jakarta Sans', 'Inter']);
        const walk = (object) => {
            if (!object || typeof object !== 'object') {
                return;
            }
            if (object.fontFamily) {
                families.add(normalizeFontFamily(object.fontFamily));
            }
            if (object.styles && typeof object.styles === 'object') {
                Object.keys(object.styles).forEach((lineKey) => {
                    const line = object.styles[lineKey];
                    if (!line || typeof line !== 'object') {
                        return;
                    }
                    Object.keys(line).forEach((charKey) => {
                        const style = line[charKey];
                        if (style && style.fontFamily) {
                            families.add(normalizeFontFamily(style.fontFamily));
                        }
                    });
                });
            }
            const children = Array.isArray(object.objects) ? object.objects : (Array.isArray(object._objects) ? object._objects : []);
            children.forEach(walk);
        };
        (Array.isArray(fabricData.objects) ? fabricData.objects : []).forEach(walk);
        return Array.from(families).filter(Boolean).slice(0, 24);
    }

    function normalizeFontFamily(family) {
        return String(family || '').trim().replace(/^['"]|['"]$/g, '') || 'Plus Jakarta Sans';
    }

    function loadFrameImage(src) {
        const normalizedSrc = normalizeAssetUrl(src);
        if (!normalizedSrc) {
            return Promise.resolve(null);
        }
        if (frameImageCache.has(normalizedSrc)) {
            return frameImageCache.get(normalizedSrc);
        }
        const promise = new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = normalizedSrc;
        });
        frameImageCache.set(normalizedSrc, promise);
        return promise;
    }

    function normalizeAssetUrl(src) {
        const value = String(src || '').trim();
        if (!value) {
            return '';
        }
        if (/^data:image\//i.test(value)) {
            return value;
        }
        try {
            return new URL(value, window.location.origin).href;
        } catch (error) {
            return value;
        }
    }

    function safeCanvasColor(value, fallback) {
        if (typeof value !== 'string') {
            return fallback;
        }
        const color = value.trim();
        if (!color || color === 'transparent') {
            return fallback;
        }
        if (/^(#[0-9a-f]{3,8}|rgba?\([^)]+\)|hsla?\([^)]+\)|[a-z]+)$/i.test(color)) {
            return color;
        }

        return fallback;
    }

    function drawClassicFrame(ctx, width, height, accent, paper, img) {
        ctx.fillStyle = accent;
        ctx.fillRect(0, 0, width, height);
        drawSubtlePattern(ctx, width, height);
        drawText(ctx, 'Kisah Kekal', width / 2, 68, 34, 'script', 'rgba(247,244,236,.78)');
        drawPhoto(ctx, img, 165, 150, 750, 560, '#f8f4ea');
        drawText(ctx, 'THE WEDDING OF', 200, 800, 32, 'serif', 'rgba(247,244,236,.75)', 'left', .18);
        drawText(ctx, cfg.title || 'AdaAcara', 190, 895, 84, 'script', '#f8f4ea', 'left');
        drawText(ctx, cfg.eventDate || '', 198, 970, 28, 'serif', 'rgba(247,244,236,.72)', 'left', .16);
        drawFlower(ctx, 135, 1030, 86, paper);
        drawFlower(ctx, 865, 840, 76, paper);
        ctx.fillStyle = paper;
        ctx.fillRect(0, height - 72, width, 38);
        for (let x = 0; x < width; x += 42) {
            ctx.beginPath();
            ctx.arc(x, height - 72, 22, 0, Math.PI);
            ctx.strokeStyle = 'rgba(48,60,34,.55)';
            ctx.lineWidth = 4;
            ctx.stroke();
        }
    }

    function drawDuoFrame(ctx, width, height, accent, paper, img) {
        ctx.fillStyle = paper;
        ctx.fillRect(0, 0, width, height);
        drawLeaf(ctx, 72, 28, accent, .18);
        drawLeaf(ctx, width - 180, 32, accent, .16);
        drawPhoto(ctx, img, 145, 155, 790, 350, accent);
        drawPhoto(ctx, img, 145, 540, 790, 350, accent);
        drawText(ctx, 'The Wedding Of', width / 2, 960, 33, 'script', accent);
        drawText(ctx, cfg.title || 'AdaAcara', width / 2, 1035, 58, 'serif', accent, 'center', .08);
        ctx.fillStyle = accent;
        roundRect(ctx, 80, 1120, width - 160, 170, 22);
        ctx.fill();
        drawText(ctx, cfg.eventDate || '', 142, 1214, 34, 'serif', 'rgba(247,244,236,.7)', 'left', .14);
        drawText(ctx, 'MEMORY', width - 145, 1214, 34, 'serif', 'rgba(247,244,236,.7)', 'right', .14);
    }

    function drawDuoHorizontalFrame(ctx, width, height, accent, paper, images) {
        ctx.fillStyle = accent;
        ctx.fillRect(0, 0, width, height);
        drawSubtlePattern(ctx, width, height);
        drawText(ctx, 'PHOTOBOOTH MEMORIES', width / 2, 150, 38, 'serif', 'rgba(247,244,236,.76)', 'center', .18);
        drawText(ctx, cfg.title || 'AdaAcara', width / 2, 225, 74, 'script', paper);
        drawPhoto(ctx, images[0], 92, 245, 430, 520, 'rgba(9,16,12,.72)', 'PHOTO 1');
        drawPhoto(ctx, images[1], 558, 245, 430, 520, 'rgba(9,16,12,.72)', 'PHOTO 2');
        ctx.fillStyle = paper;
        roundRect(ctx, 76, 812, width - 152, 308, 8);
        ctx.fill();
        drawLeaf(ctx, 62, 778, accent, .14);
        drawLeaf(ctx, width - 218, 804, accent, .12);
        drawText(ctx, 'The Wedding Of', width / 2, 905, 34, 'script', accent);
        drawText(ctx, cfg.title || 'AdaAcara', width / 2, 990, 56, 'serif', accent, 'center', .08);
        drawText(ctx, cfg.eventDate || '', width / 2, 1080, 30, 'serif', 'rgba(48,60,34,.68)', 'center', .16);
        ctx.fillStyle = accent;
        roundRect(ctx, 94, 1164, width - 188, 112, 0);
        ctx.fill();
        drawText(ctx, 'GUEST MEMORIES', width / 2, 1220, 32, 'serif', 'rgba(247,244,236,.78)', 'center', .16);
    }

    function drawDuoStackFrame(ctx, width, height, accent, paper, images) {
        ctx.fillStyle = paper;
        ctx.fillRect(0, 0, width, height);
        drawLeaf(ctx, 46, 42, accent, .2);
        drawLeaf(ctx, width - 230, 44, accent, .16);
        drawText(ctx, 'Kisah Kekal', width / 2, 82, 34, 'script', 'rgba(48,60,34,.58)');
        drawPhoto(ctx, images[0], 165, 260, 750, 560, 'rgba(10,18,12,.92)', 'PHOTO 1');
        drawPhoto(ctx, images[1], 165, 880, 750, 560, 'rgba(10,18,12,.92)', 'PHOTO 2');
        drawLeaf(ctx, 92, 1484, accent, .16);
        drawLeaf(ctx, width - 292, 1468, accent, .14);
        drawText(ctx, 'The Wedding Of', width / 2, 1598, 34, 'script', accent);
        drawText(ctx, cfg.title || 'AdaAcara', width / 2, 1692, 62, 'serif', accent, 'center', .08);
        drawText(ctx, cfg.eventDate || '', width / 2, 1772, 30, 'serif', 'rgba(48,60,34,.64)', 'center', .16);
        ctx.fillStyle = accent;
        ctx.fillRect(0, height - 180, width, 180);
        drawText(ctx, 'GUEST MEMORIES', width / 2, height - 90, 34, 'serif', 'rgba(247,244,236,.76)', 'center', .16);
    }

    function drawTrioStackFrame(ctx, width, height, accent, paper, images) {
        ctx.fillStyle = accent;
        ctx.fillRect(0, 0, width, height);
        drawSubtlePattern(ctx, width, height);
        drawText(ctx, 'PHOTOBOOTH MEMORIES', width / 2, 118, 36, 'serif', 'rgba(247,244,236,.76)', 'center', .18);
        drawText(ctx, cfg.title || 'AdaAcara', width / 2, 190, 60, 'script', paper);
        drawPhoto(ctx, images[0], 165, 300, 750, 560, 'rgba(10,18,12,.9)', 'PHOTO 1');
        drawPhoto(ctx, images[1], 165, 920, 750, 560, 'rgba(10,18,12,.9)', 'PHOTO 2');
        drawPhoto(ctx, images[2], 165, 1540, 750, 560, 'rgba(10,18,12,.9)', 'PHOTO 3');
        ctx.fillStyle = paper;
        roundRect(ctx, 112, 2200, width - 224, 240, 4);
        ctx.fill();
        drawFlower(ctx, 174, 2280, 48, accent);
        drawFlower(ctx, width - 170, 2362, 48, accent);
        drawText(ctx, 'LET US REMEMBER', width / 2, 2298, 34, 'serif', accent, 'center', .16);
        drawText(ctx, cfg.eventDate || '', width / 2, 2378, 26, 'serif', 'rgba(48,60,34,.64)', 'center', .16);
        drawText(ctx, 'THROUGH YOUR EYES', width / 2, 2555, 30, 'serif', paper, 'center', .13);
        drawText(ctx, 'GUEST MEMORIES', width / 2, 2780, 32, 'serif', 'rgba(247,244,236,.74)', 'center', .16);
    }

    function drawTrioHorizontalFrame(ctx, width, height, accent, paper, images) {
        ctx.fillStyle = accent;
        ctx.fillRect(0, 0, width, height);
        drawSubtlePattern(ctx, width, height);
        drawText(ctx, 'PHOTOBOOTH MEMORIES', width / 2, 136, 36, 'serif', 'rgba(247,244,236,.76)', 'center', .18);
        drawText(ctx, cfg.title || 'AdaAcara', width / 2, 214, 70, 'script', paper);
        drawPhoto(ctx, images[0], 70, 270, 300, 470, 'rgba(9,16,12,.72)', 'PHOTO 1');
        drawPhoto(ctx, images[1], 390, 270, 300, 470, 'rgba(9,16,12,.72)', 'PHOTO 2');
        drawPhoto(ctx, images[2], 710, 270, 300, 470, 'rgba(9,16,12,.72)', 'PHOTO 3');
        ctx.fillStyle = paper;
        roundRect(ctx, 70, 790, width - 140, 360, 8);
        ctx.fill();
        drawFlower(ctx, 165, 850, 58, accent);
        drawFlower(ctx, width - 160, 1060, 58, accent);
        drawText(ctx, 'LET US REMEMBER', width / 2, 900, 36, 'serif', accent, 'center', .16);
        drawText(ctx, cfg.eventDate || '', width / 2, 980, 32, 'serif', 'rgba(48,60,34,.68)', 'center', .16);
        drawText(ctx, 'THROUGH YOUR EYES', width / 2, 1070, 32, 'serif', accent, 'center', .13);
        ctx.fillStyle = accent;
        ctx.fillRect(70, 1178, width - 140, 112);
        drawText(ctx, 'GUEST MEMORIES', width / 2, 1234, 32, 'serif', 'rgba(247,244,236,.78)', 'center', .16);
    }

    function drawSquareFrame(ctx, width, height, accent, paper, img) {
        ctx.fillStyle = accent;
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = paper;
        roundRect(ctx, 82, 82, width - 164, height - 164, 4);
        ctx.fill();
        drawLeaf(ctx, 55, 42, accent, .2);
        drawLeaf(ctx, width - 190, height - 230, accent, .18);
        drawPhoto(ctx, img, 145, 145, 790, 610, accent);
        drawText(ctx, 'PHOTOBOOTH MEMORIES', width / 2, 826, 34, 'serif', accent, 'center', .18);
        drawText(ctx, cfg.title || 'AdaAcara', width / 2, 905, 64, 'script', accent);
        drawText(ctx, cfg.eventDate || '', width / 2, 982, 28, 'serif', 'rgba(48,60,34,.7)', 'center', .16);
    }

    function drawPhoto(ctx, img, x, y, width, height, fallbackColor, label) {
        ctx.save();
        ctx.fillStyle = fallbackColor || '#111';
        ctx.fillRect(x, y, width, height);
        if (img) {
            ctx.beginPath();
            ctx.rect(x, y, width, height);
            ctx.clip();
            drawCover(ctx, img, x, y, width, height);
        } else if (label) {
            ctx.fillStyle = 'rgba(247,244,236,.58)';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = '400 30px Georgia, "Times New Roman", serif';
            ctx.fillText(label, x + width / 2, y + height / 2);
        }
        ctx.restore();
    }

    function drawCover(ctx, img, x, y, width, height) {
        const iw = img.naturalWidth || img.width;
        const ih = img.naturalHeight || img.height;
        const scale = Math.max(width / iw, height / ih);
        const drawWidth = iw * scale;
        const drawHeight = ih * scale;
        ctx.drawImage(img, x + (width - drawWidth) / 2, y + (height - drawHeight) / 2, drawWidth, drawHeight);
    }

    function drawText(ctx, text, x, y, size, family, color, align, spacing) {
        if (!text) {
            return;
        }
        ctx.save();
        ctx.fillStyle = color || '#fff';
        ctx.textAlign = align || 'center';
        ctx.textBaseline = 'middle';
        ctx.font = family === 'script'
            ? '400 ' + size + 'px "Alex Brush", "Brush Script MT", "Segoe Script", cursive'
            : '400 ' + size + 'px Georgia, "Times New Roman", serif';
        if (spacing && String(text).length < 32) {
            drawLetterSpacing(ctx, String(text).toUpperCase(), x, y, spacing * size, align || 'center');
        } else {
            ctx.fillText(String(text), x, y, Math.max(120, ctx.canvas.width - 150));
        }
        ctx.restore();
    }

    function drawLetterSpacing(ctx, text, x, y, spacing, align) {
        const chars = text.split('');
        const total = chars.reduce((sum, char) => sum + ctx.measureText(char).width + spacing, -spacing);
        let cursor = align === 'right' ? x - total : (align === 'left' ? x : x - total / 2);
        chars.forEach((char) => {
            ctx.fillText(char, cursor, y);
            cursor += ctx.measureText(char).width + spacing;
        });
    }

    function drawSubtlePattern(ctx, width, height) {
        ctx.save();
        ctx.strokeStyle = 'rgba(255,255,255,.035)';
        ctx.lineWidth = 2;
        for (let y = 60; y < height; y += 78) {
            ctx.beginPath();
            for (let x = 0; x < width; x += 26) {
                ctx.lineTo(x, y + Math.sin((x + y) / 60) * 12);
            }
            ctx.stroke();
        }
        ctx.restore();
    }

    function drawFlower(ctx, x, y, size, color) {
        ctx.save();
        ctx.fillStyle = color;
        for (let i = 0; i < 6; i += 1) {
            const angle = (Math.PI * 2 * i) / 6;
            ctx.beginPath();
            ctx.ellipse(x + Math.cos(angle) * size * .28, y + Math.sin(angle) * size * .28, size * .17, size * .31, angle, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.fillStyle = '#d3a75c';
        ctx.beginPath();
        ctx.arc(x, y, size * .15, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    function drawLeaf(ctx, x, y, color, alpha) {
        ctx.save();
        ctx.globalAlpha = alpha;
        ctx.strokeStyle = color;
        ctx.lineWidth = 8;
        for (let i = 0; i < 7; i += 1) {
            ctx.beginPath();
            ctx.moveTo(x + i * 20, y + i * 8);
            ctx.quadraticCurveTo(x + 80 + i * 14, y + 20 + i * 20, x + 55 + i * 18, y + 95 + i * 16);
            ctx.stroke();
        }
        ctx.restore();
    }

    function roundRect(ctx, x, y, width, height, radius) {
        const r = Math.min(radius, width / 2, height / 2);
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

    function canvasToBlob(canvas, type, quality) {
        return new Promise((resolve) => canvas.toBlob(resolve, type, quality));
    }

    async function compressCanvas(canvas, maxBytes, startQuality) {
        let quality = startQuality;
        let blob = await canvasToBlob(canvas, 'image/jpeg', quality);
        while (blob && blob.size > maxBytes && quality > .58) {
            quality = Math.max(.58, quality - .06);
            blob = await canvasToBlob(canvas, 'image/jpeg', quality);
        }
        return blob;
    }

    async function makeThumb(sourceCanvas) {
        const ratio = sourceCanvas.height / sourceCanvas.width;
        const canvas = document.createElement('canvas');
        canvas.width = 360;
        canvas.height = Math.round(360 * ratio);
        const ctx = canvas.getContext('2d', { alpha: false });
        ctx.drawImage(sourceCanvas, 0, 0, canvas.width, canvas.height);
        return compressCanvas(canvas, 260 * 1024, .82);
    }

    function setProgress(value) {
        if (!els.progress || !els.progressBar) {
            return;
        }
        els.progress.hidden = value <= 0 || value >= 100;
        els.progressBar.style.width = Math.max(0, Math.min(100, value)) + '%';
    }

    function showPrintingOverlay(dataUrl) {
        if (els.printingPhoto && dataUrl) {
            els.printingPhoto.src = dataUrl;
        }
        if (!els.printing) {
            return 0;
        }
        els.printing.hidden = false;
        els.printing.classList.remove('is-printing');
        void els.printing.offsetWidth;
        els.printing.classList.add('is-printing');
        return Date.now();
    }

    function finishPrintingOverlay(startedAt) {
        const minDuration = 4200;
        const elapsed = startedAt ? Date.now() - startedAt : minDuration;
        const wait = Math.max(0, minDuration - elapsed);

        return new Promise((resolve) => {
            window.setTimeout(() => {
                if (els.printing) {
                    els.printing.hidden = true;
                    els.printing.classList.remove('is-printing');
                }
                resolve();
            }, wait);
        });
    }

    async function uploadMemory() {
        const name = (els.nameInput && els.nameInput.value || '').trim();
        const email = (els.emailInput && els.emailInput.value || '').trim();
        const wishText = normalizeWishText(els.wishInput && els.wishInput.value || '');
        if (allSlotsFilled() && !state.finalBlob && els.finalCanvas) {
            await renderFinalPreview();
        }
        if (!allSlotsFilled() || !state.finalBlob) {
            showToast(t('gm.upload.complete_all', 'Lengkapi semua foto terlebih dahulu.'));
            return;
        }
        if (name.length < 2) {
            showToast(t('gm.upload.name_required', 'Tulis nama kamu terlebih dahulu.'));
            return;
        }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showToast(t('gm.upload.email_invalid', 'Format email belum valid.'));
            return;
        }

        const dataUrl = els.finalCanvas ? els.finalCanvas.toDataURL('image/jpeg', .72) : '';
        const printingStartedAt = showPrintingOverlay(dataUrl);

        const formData = csrfFormData(new FormData());
        formData.append('guest_name', name);
        if (email) {
            formData.append('guest_email', email);
        }
        if (wishText) {
            formData.append('wish_text', wishText);
        }
        formData.append('frame_id', String(currentFrame().id || 1));
        formData.append('photo', state.finalBlob, 'guest-memory.jpg');
        if (state.thumbBlob) {
            formData.append('thumbnail', state.thumbBlob, 'guest-memory-thumb.jpg');
        }
        if (state.audioBlob) {
            const audioExtension = state.audioBlob.type.includes('mp4') ? 'm4a' : (state.audioBlob.type.includes('ogg') ? 'ogg' : 'webm');
            formData.append('audio', state.audioBlob, 'guest-wish.' + audioExtension);
            formData.append('audio_duration', String(state.audioDuration || 0));
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', cfg.uploadUrl, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = (event) => {
            if (event.lengthComputable) {
                setProgress(Math.round((event.loaded / event.total) * 96));
            }
        };
        xhr.onload = async () => {
            let response = {};
            try {
                response = JSON.parse(xhr.responseText || '{}');
            } catch (error) {
                response = {};
            }
            updateCsrf(response.csrf_hash);
            setProgress(100);
            await finishPrintingOverlay(printingStartedAt);
            if (xhr.status >= 200 && xhr.status < 300 && response.success) {
                state.searchQuery = '';
                if (els.search) {
                    els.search.value = '';
                }
                resetUpload();
                switchPanel('gallery');
                if (response.print_code) {
                    showPrintCodeNotice(response);
                } else {
                    showToast(response.message || t('gm.toast.success', 'Momen berhasil ditambahkan.'), 5600);
                }
                return;
            }
            const message = response.message || t('gm.upload.failed', 'Upload belum berhasil.');
            if (xhr.status === 422 && /nama/i.test(message)) {
                showToast(t('gm.upload.name_taken', 'Gunakan nama lain atau tambahkan inisial. Nama ini sudah dipakai.'), 7000);
                return;
            }
            showToast(message, 5600);
        };
        xhr.onerror = async () => {
            await finishPrintingOverlay(printingStartedAt);
            setProgress(0);
            showToast(t('gm.upload.connection_lost', 'Koneksi upload terputus. Coba lagi.'));
        };
        setProgress(8);
        xhr.send(formData);
    }

    function resetUpload() {
        state.sourceImages = [];
        state.activeSlotIndex = 0;
        state.finalBlob = null;
        state.thumbBlob = null;
        clearAudio(false);
        if (els.cameraInput) {
            els.cameraInput.value = '';
        }
        if (els.fileInput) {
            els.fileInput.value = '';
        }
        if (els.nameInput) {
            els.nameInput.value = '';
        }
        if (els.emailInput) {
            els.emailInput.value = '';
        }
        if (els.wishInput) {
            els.wishInput.value = '';
        }
        updateWishCounter();
        setProgress(0);
    }

    function handleGallerySearch() {
        state.searchQuery = (els.search && els.search.value || '').trim();
        state.hasMore = true;
        state.page = 1;
        loadMemories(true);
    }

    function openDetail(item) {
        if (!els.detailModal) {
            return;
        }
        const photo = $('[data-gm-detail-photo]', els.detailModal);
        const name = $('[data-gm-detail-name]', els.detailModal);
        const date = $('[data-gm-detail-date]', els.detailModal);
        const download = $('[data-gm-detail-download]', els.detailModal);
        const wish = $('[data-gm-detail-wish]', els.detailModal);
        const audioBox = $('[data-gm-detail-audio]', els.detailModal);
        const audioPlay = $('[data-gm-detail-audio-play]', els.detailModal);
        const audioTime = audioBox ? $('span', audioBox) : null;
        if (state.detailAudio) {
            state.detailAudio.pause();
            state.detailAudio = null;
        }
        if (photo) {
            photo.src = item.photo;
            photo.alt = t('gm.card.alt', 'Momen dari {name}', { name: item.guest_name || t('gm.card.guest', 'Tamu') });
        }
        if (name) {
            name.textContent = item.guest_name || t('gm.card.guest', 'Tamu');
        }
        if (date) {
            const parts = splitDate(item.created_at || '');
            date.textContent = parts.date + (parts.time ? '     ' + parts.time : '');
        }
        if (download) {
            download.onclick = (event) => {
                event.preventDefault();
                openPrintCodeModal(item);
            };
        }
        if (wish) {
            const wishText = normalizeWishText(item.wish_text || '');
            wish.hidden = wishText === '';
            wish.textContent = wishText;
        }
        if (audioBox && audioPlay && item.audio) {
            audioBox.hidden = false;
            state.detailAudio = new Audio(item.audio);
            if (audioTime) {
                audioTime.textContent = formatDuration(item.audio_duration || 0);
            }
            audioPlay.textContent = '▶';
            audioPlay.onclick = () => {
                if (!state.detailAudio) {
                    return;
                }
                if (state.detailAudio.paused) {
                    state.detailAudio.play().then(() => {
                        audioPlay.textContent = 'Ⅱ';
                    }).catch(() => showToast(t('gm.audio.play_failed', 'Audio belum bisa diputar.')));
                } else {
                    state.detailAudio.pause();
                    audioPlay.textContent = '▶';
                }
            };
            state.detailAudio.addEventListener('ended', () => {
                audioPlay.textContent = '▶';
            }, { once: true });
        } else if (audioBox) {
            audioBox.hidden = true;
        }
        els.detailModal.hidden = false;
        document.documentElement.style.overflow = 'hidden';
    }

    function closeDetail() {
        if (state.detailAudio) {
            state.detailAudio.pause();
            state.detailAudio = null;
        }
        if (els.detailModal) {
            els.detailModal.hidden = true;
            document.documentElement.style.overflow = '';
        }
    }

    function goBack() {
        if (state.panel === 'gallery') {
            switchPanel('home');
            return;
        }
        if (state.panel === 'upload') {
            if (state.step === 'details') {
                switchStep('photo');
            } else if (state.step === 'crop') {
                switchStep('photo');
            } else if (state.step === 'photo') {
                switchStep('frame');
            } else {
                switchScreen('opening');
            }
        }
    }

    function bindEvents() {
        $('[data-gm-open-experience]')?.addEventListener('click', () => {
            switchScreen('experience');
            switchPanel('home');
        });
        $('[data-gm-close-experience]')?.addEventListener('click', () => switchScreen('opening'));
        els.back?.addEventListener('click', goBack);
        $('[data-gm-go-upload]')?.addEventListener('click', () => {
            switchPanel('upload');
        });
        $('[data-gm-go-gallery]')?.addEventListener('click', () => switchPanel('gallery'));
        $('[data-gm-camera]')?.addEventListener('click', openCameraForActiveSlot);
        $('[data-gm-gallery]')?.addEventListener('click', () => openFileInput(els.fileInput));
        $('[data-gm-next-slot]')?.addEventListener('click', () => {
            const next = firstEmptySlot();
            if (next >= 0) {
                setActiveSlot(next);
                return;
            }
            switchStep('details');
        });
        $('[data-gm-prev-frame]')?.addEventListener('click', () => moveFrame(-1));
        $('[data-gm-next-frame]')?.addEventListener('click', () => moveFrame(1));
        $('[data-gm-select-frame]')?.addEventListener('click', selectFrame);
        if (els.search) {
            let searchTimer = 0;
            els.search.addEventListener('input', () => {
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(handleGallerySearch, 260);
            });
        }
        els.cropUse?.addEventListener('click', commitCrop);
        els.retake?.addEventListener('click', retakeCrop);
        els.wishInput?.addEventListener('input', updateWishCounter);
        updateWishCounter();
        els.cropZoom?.addEventListener('input', () => {
            if (!state.pendingCrop) {
                return;
            }
            state.pendingCrop.zoom = Number(els.cropZoom.value || 1);
            renderCropCanvas();
        });
        els.cropCanvas?.addEventListener('pointerdown', (event) => {
            if (!state.pendingCrop) {
                return;
            }
            state.pendingCrop.dragging = true;
            state.pendingCrop.lastX = event.clientX;
            state.pendingCrop.lastY = event.clientY;
            els.cropCanvas.closest('.aa-gm-crop-card')?.classList.add('is-dragging');
            els.cropCanvas.setPointerCapture?.(event.pointerId);
        });
        els.cropCanvas?.addEventListener('pointermove', (event) => {
            const crop = state.pendingCrop;
            if (!crop || !crop.dragging) {
                return;
            }
            const rect = els.cropCanvas.getBoundingClientRect();
            const ratioX = els.cropCanvas.width / Math.max(1, rect.width);
            const ratioY = els.cropCanvas.height / Math.max(1, rect.height);
            crop.offsetX += (event.clientX - crop.lastX) * ratioX;
            crop.offsetY += (event.clientY - crop.lastY) * ratioY;
            crop.lastX = event.clientX;
            crop.lastY = event.clientY;
            renderCropCanvas();
        });
        ['pointerup', 'pointercancel', 'pointerleave'].forEach((eventName) => {
            els.cropCanvas?.addEventListener(eventName, () => {
                if (state.pendingCrop) {
                    state.pendingCrop.dragging = false;
                }
                els.cropCanvas.closest('.aa-gm-crop-card')?.classList.remove('is-dragging');
            });
        });
        els.cameraInput?.addEventListener('change', (event) => {
            handleFile(event.target.files && event.target.files[0], true);
            event.target.value = '';
        });
        els.fileInput?.addEventListener('change', (event) => {
            handleFile(event.target.files && event.target.files[0], false);
            event.target.value = '';
        });
        els.recordToggle?.addEventListener('click', toggleRecording);
        els.recordClear?.addEventListener('click', () => clearAudio(true));
        els.submit?.addEventListener('click', uploadMemory);
        $('[data-gm-close-detail]')?.addEventListener('click', closeDetail);
        els.printCodeSubmit?.addEventListener('click', submitPrintCode);
        els.printCodePrint?.addEventListener('click', handlePrintCodePrint);
        els.printCodeInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                submitPrintCode();
            }
        });
        $('[data-gm-print-code-close]')?.addEventListener('click', closePrintCodeModal);
        els.printCodeModal?.addEventListener('click', (event) => {
            if (event.target === els.printCodeModal) {
                closePrintCodeModal();
            }
        });
        window.setInterval(refreshLatestMemories, galleryAutoRefreshMs);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                refreshLatestMemories();
            }
        });
        document.addEventListener('click', (event) => {
            if (event.target.closest('.aa-gm-card-menu')) {
                return;
            }
            $$('.aa-gm-card-menu__panel').forEach((panel) => {
                panel.hidden = true;
            });
        });

        if ('IntersectionObserver' in window && els.sentinel) {
            const observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    loadMemories(false);
                }
            }, { rootMargin: '260px 0px' });
            observer.observe(els.sentinel);
        }
    }

    bindEvents();
})();
