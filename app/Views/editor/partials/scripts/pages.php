        function createBlankPageData(title) {
            return {
                id: `page-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                title: title || `Halaman ${state.pages.length + 1}`,
                objects: [],
                background: '#ffffff',
                backgroundColor: '#ffffff',
                artboard: {
                    width: state.canvas ? state.canvas.getWidth() : 1080,
                    height: state.canvas ? state.canvas.getHeight() : 1920,
                },
                hidden: false,
                renderer: 'fabric-page',
                version: '5.3.0',
            };
        }

        function createPhotoboothSlotObjects(frameKey, slotCount) {
            const frameIndex = Math.max(0, slotCount - 1);
            const layouts = {
                1: [{ left: 165, top: 245, width: 750, height: 560 }],
                2: [
                    { left: 165, top: 250, width: 750, height: 560 },
                    { left: 165, top: 855, width: 750, height: 560 },
                ],
                3: [
                    { left: 165, top: 250, width: 750, height: 560 },
                    { left: 165, top: 855, width: 750, height: 560 },
                    { left: 165, top: 1460, width: 750, height: 560 },
                ],
            };
            const slots = layouts[slotCount] || layouts[1];

            return slots.flatMap((slot, index) => {
                const rectId = `photobooth-slot-${slotCount}-${index + 1}`;
                return [
                    {
                        id: rectId,
                        name: `Photobooth ${index + 1}`,
                        type: 'rect',
                        version: '5.3.0',
                        left: slot.left,
                        top: slot.top,
                        width: slot.width,
                        height: slot.height,
                        fill: '#e5e7eb',
                        stroke: '#cbd5e1',
                        strokeWidth: 4,
                        strokeDashArray: [18, 14],
                        rx: 18,
                        ry: 18,
                        selectable: true,
                        evented: true,
                        locked: false,
                        lockMovementX: false,
                        lockMovementY: false,
                        lockScalingX: false,
                        lockScalingY: false,
                        lockRotation: true,
                        hasControls: true,
                        hasBorders: true,
                        excludeFromAnimation: true,
                        customType: 'photobooth-photo-slot',
                        aaPhotoboothSlot: true,
                        aaPhotoboothSlotIndex: index + 1,
                        aaPhotoboothSlotCount: slotCount,
                        aaPhotoboothFrameKey: frameKey,
                        aaProtectedObject: true,
                        aaPreventDelete: true,
                    },
                    {
                        id: `${rectId}-label`,
                        name: `Label Photobooth ${index + 1}`,
                        type: 'textbox',
                        version: '5.3.0',
                        left: slot.left + (slot.width / 2),
                        top: slot.top + (slot.height / 2) - 24,
                        width: slot.width - 80,
                        text: 'Photobooth',
                        styles: {},
                        fill: '#94a3b8',
                        fontFamily: 'Inter',
                        fontSize: 46,
                        fontWeight: '800',
                        textAlign: 'center',
                        originX: 'center',
                        originY: 'center',
                        selectable: false,
                        evented: false,
                        locked: true,
                        lockMovementX: true,
                        lockMovementY: true,
                        lockScalingX: true,
                        lockScalingY: true,
                        lockRotation: true,
                        hasControls: false,
                        hasBorders: false,
                        excludeFromAnimation: true,
                        customType: 'photobooth-photo-slot-label',
                        aaPhotoboothSlot: true,
                        aaPhotoboothSlotIndex: index + 1,
                        aaPhotoboothSlotCount: slotCount,
                        aaPhotoboothFrameKey: frameKey,
                    },
                ];
            });
        }

        function aaIsPhotoboothPhotoSlot(object) {
            return Boolean(object && object.customType === 'photobooth-photo-slot');
        }

        function aaIsPhotoboothSlotObject(object) {
            return Boolean(object && (
                object.aaPhotoboothSlot === true ||
                object.customType === 'photobooth-photo-slot' ||
                object.customType === 'photobooth-photo-slot-label'
            ));
        }

        function aaIsProtectedPhotoboothObject(object) {
            if (!object) return false;
            if (aaIsPhotoboothPhotoSlot(object) || object.customType === 'photobooth-photo-slot-label') return true;
            if (object.type === 'activeSelection' && typeof object.getObjects === 'function') {
                return object.getObjects().some(child => aaIsProtectedPhotoboothObject(child));
            }
            return false;
        }

        function aaLockPhotoboothSlotObject(object, index, slotCount, frameKey) {
            if (!object || typeof object !== 'object') return object;
            const isSlot = object.customType === 'photobooth-photo-slot';
            const isLabel = object.customType === 'photobooth-photo-slot-label';
            if (!isSlot && !isLabel && object.aaPhotoboothSlot !== true) return object;

            const normalizedIndex = Math.max(1, Number(index || object.aaPhotoboothSlotIndex || 1) || 1);
            object.selectable = isSlot;
            object.evented = isSlot;
            object.locked = !isSlot;
            object.lockMovementX = !isSlot;
            object.lockMovementY = !isSlot;
            object.lockScalingX = !isSlot;
            object.lockScalingY = !isSlot;
            object.lockRotation = true;
            object.hasControls = isSlot;
            object.hasBorders = isSlot;
            object.excludeFromAnimation = true;
            object.aaPhotoboothSlot = true;
            object.aaPhotoboothSlotIndex = normalizedIndex;
            object.aaPhotoboothSlotCount = Math.max(1, Math.min(3, Number(slotCount || object.aaPhotoboothSlotCount || 1) || 1));
            object.aaPhotoboothFrameKey = frameKey || object.aaPhotoboothFrameKey || `photobooth-${object.aaPhotoboothSlotCount}`;
            object.aaProtectedObject = true;
            object.aaPreventDelete = true;
            object.hoverCursor = isSlot ? 'move' : 'default';

            return object;
        }

        function aaPhotoboothObjectBounds(object) {
            if (!object) return null;
            const scaleX = Math.max(0.01, Number(object.scaleX || 1) || 1);
            const scaleY = Math.max(0.01, Number(object.scaleY || 1) || 1);
            const left = Number(object.left || 0) || 0;
            const top = Number(object.top || 0) || 0;
            const width = Math.max(40, (Number(object.width || 0) || 0) * scaleX);
            const height = Math.max(40, (Number(object.height || 0) || 0) * scaleY);
            const radius = Math.max(0, Number(object.rx || object.ry || 0) || 0) * Math.max(scaleX, scaleY);
            return { left, top, width, height, radius };
        }

        function aaExtractPhotoboothSlotsFromObjects(objects, slotCount, frameKey) {
            const slots = [];
            (Array.isArray(objects) ? objects : []).forEach((object, rawIndex) => {
                if (!object || object.customType !== 'photobooth-photo-slot') return;
                const bounds = aaPhotoboothObjectBounds(object);
                if (!bounds) return;
                slots.push({
                    rawIndex,
                    index: Math.max(1, Number(object.aaPhotoboothSlotIndex || slots.length + 1) || slots.length + 1),
                    x: bounds.left,
                    y: bounds.top,
                    width: bounds.width,
                    height: bounds.height,
                    radius: bounds.radius,
                });
            });

            slots.sort((a, b) => {
                const indexDiff = (a.index || 0) - (b.index || 0);
                if (indexDiff !== 0 && slots.some(slot => slot.index !== a.index)) return indexDiff;
                return (a.y - b.y) || (a.x - b.x) || (a.rawIndex - b.rawIndex);
            });

            return slots.slice(0, Math.max(1, Math.min(3, Number(slotCount) || 1))).map((slot, index) => ({
                index: index + 1,
                x: slot.x,
                y: slot.y,
                width: slot.width,
                height: slot.height,
                radius: slot.radius,
            }));
        }

        function aaNormalizePhotoboothFrameSlots(frame) {
            if (!frame || typeof frame !== 'object') return frame;
            const slotCount = Math.max(1, Math.min(3, Number(frame.slotCount || frame.slot_count || 1) || 1));
            const frameKey = frame.frameKey || `photobooth-${slotCount}`;
            const objects = Array.isArray(frame.objects) ? frame.objects : [];
            let slots = aaExtractPhotoboothSlotsFromObjects(objects, slotCount, frameKey);

            if (!slots.length && Array.isArray(frame.photoSlots)) {
                slots = frame.photoSlots.map((slot, index) => ({
                    index: index + 1,
                    x: Number(slot.x || slot.left || 0) || 0,
                    y: Number(slot.y || slot.top || 0) || 0,
                    width: Math.max(40, Number(slot.width || 0) || 0),
                    height: Math.max(40, Number(slot.height || 0) || 0),
                    radius: Math.max(0, Number(slot.radius || 0) || 0),
                })).slice(0, slotCount);
            }

            objects.forEach(object => {
                if (!object || object.aaPhotoboothSlot !== true && !['photobooth-photo-slot', 'photobooth-photo-slot-label'].includes(object.customType)) {
                    return;
                }
                const visualIndex = Math.max(1, slots.findIndex(slot => {
                    const bounds = aaPhotoboothObjectBounds(object);
                    return bounds && Math.abs(bounds.left - slot.x) < 2 && Math.abs(bounds.top - slot.y) < 2;
                }) + 1);
                aaLockPhotoboothSlotObject(object, visualIndex, slotCount, frameKey);
            });

            frame.slotCount = slotCount;
            frame.photoSlots = slots.map((slot, index) => ({
                index: index + 1,
                x: slot.x,
                y: slot.y,
                width: slot.width,
                height: slot.height,
                radius: slot.radius,
            }));
            return frame;
        }

        function aaLockPhotoboothSlotsOnCanvas(canvas) {
            if (!canvas || typeof canvas.getObjects !== 'function') return;
            const slotObjects = canvas.getObjects()
                .filter(object => object && object.customType === 'photobooth-photo-slot')
                .sort((a, b) => {
                    const indexDiff = (Number(a.aaPhotoboothSlotIndex || 0) || 0) - (Number(b.aaPhotoboothSlotIndex || 0) || 0);
                    if (indexDiff !== 0 && Number(a.aaPhotoboothSlotIndex || 0) !== Number(b.aaPhotoboothSlotIndex || 0)) {
                        return indexDiff;
                    }
                    return (Number(a.top || 0) - Number(b.top || 0)) || (Number(a.left || 0) - Number(b.left || 0));
                });
            const slotCount = Math.max(1, Math.min(3, slotObjects.length || Number(state.photoboothFrames?.[state.activePhotoboothFrameIndex]?.slotCount || 1) || 1));
            const frameKey = state.photoboothFrames?.[state.activePhotoboothFrameIndex]?.frameKey || `photobooth-${slotCount}`;

            slotObjects.forEach((object, index) => aaLockPhotoboothSlotObject(object, index + 1, slotCount, frameKey));
            canvas.getObjects().forEach(object => {
                if (!object || object.customType !== 'photobooth-photo-slot-label') return;
                const nearestIndex = Math.max(1, slotObjects.findIndex(slot => {
                    const labelCenterY = Number(object.top || 0);
                    const slotTop = Number(slot.top || 0);
                    const slotHeight = Math.max(1, (Number(slot.height || 0) || 0) * (Number(slot.scaleY || 1) || 1));
                    return labelCenterY >= slotTop && labelCenterY <= slotTop + slotHeight;
                }) + 1);
                aaLockPhotoboothSlotObject(object, nearestIndex, slotCount, frameKey);
            });

            const metadata = aaExtractPhotoboothSlotsFromObjects(canvas.getObjects(), slotCount, frameKey);
            if (state.photoboothFrames?.[state.activePhotoboothFrameIndex]) {
                state.photoboothFrames[state.activePhotoboothFrameIndex].photoSlots = metadata;
                state.photoboothFrames[state.activePhotoboothFrameIndex].slotCount = slotCount;
            }
        }

        function aaValidatePhotoboothFramesForPublish() {
            const frames = normalizePhotoboothFrames(state.photoboothFrames);
            const activeFrames = frames.filter(frame => frame && frame.hidden !== true);
            if (!activeFrames.length) {
                throw new Error('Minimal 1 Frame Photobooth harus aktif sebelum dipublish.');
            }
            const invalid = activeFrames.find((frame, index) => {
                const expected = Math.max(1, Math.min(3, Number(frame.slotCount || index + 1) || index + 1));
                const objects = Array.isArray(frame.objects) ? frame.objects : [];
                const objectSlots = objects.filter(object => object && object.customType === 'photobooth-photo-slot');
                const visibleSlots = objectSlots.filter(object => object.visible !== false);
                return !Array.isArray(frame.photoSlots) ||
                    frame.photoSlots.length !== expected ||
                    objectSlots.length !== expected ||
                    visibleSlots.length !== expected;
            });
            if (invalid) {
                throw new Error('Frame Photobooth belum valid. Pastikan 1, 2, dan 3 slot foto masih tersedia.');
            }
            state.photoboothFrames = frames;
            return true;
        }

        function createPhotoboothFrameData(slotCount, index = 0) {
            const normalizedSlotCount = Math.max(1, Math.min(3, Number(slotCount) || 1));
            const heights = { 1: 1350, 2: 2200, 3: 3000 };
            const title = `${normalizedSlotCount} Foto`;
            const frameKey = `photobooth-${normalizedSlotCount}`;
            const height = heights[normalizedSlotCount] || 1350;
            const objects = [
                {
                    id: `${frameKey}-title`,
                    name: 'Judul Photobooth',
                    type: 'textbox',
                    version: '5.3.0',
                    left: 540,
                    top: 70,
                    width: 780,
                    text: 'PHOTOBOOTH MEMORIES',
                    styles: {},
                    fill: '#31401f',
                    fontFamily: 'Cinzel',
                    fontSize: 56,
                    fontWeight: '700',
                    charSpacing: 220,
                    textAlign: 'center',
                    originX: 'center',
                    originY: 'top',
                    customType: 'text',
                },
                ...createPhotoboothSlotObjects(frameKey, normalizedSlotCount),
                {
                    id: `${frameKey}-footer`,
                    name: 'Footer Photobooth',
                    type: 'textbox',
                    version: '5.3.0',
                    left: 540,
                    top: height - 160,
                    width: 780,
                    text: title.toUpperCase() + ' FRAME',
                    styles: {},
                    fill: '#31401f',
                    fontFamily: 'Cinzel',
                    fontSize: 34,
                    fontWeight: '600',
                    charSpacing: 160,
                    textAlign: 'center',
                    originX: 'center',
                    originY: 'top',
                    customType: 'text',
                },
            ];

            return aaNormalizePhotoboothFrameSlots(sanitizeFabricPageData({
                id: `photobooth-frame-${normalizedSlotCount}`,
                title,
                frameKey,
                slotCount: normalizedSlotCount,
                objects,
                background: '#f8f4ea',
                backgroundColor: '#f8f4ea',
                artboard: {
                    width: 1080,
                    height,
                },
                hidden: false,
                renderer: 'fabric-page',
                version: '5.3.0',
            }));
        }

        function aaNormalizeFabricTextStylesInData(pageData) {
            if (!pageData || !Array.isArray(pageData.objects)) return pageData;
            const walk = objects => {
                objects.forEach(object => {
                    if (!object || typeof object !== 'object') return;
                    if (['i-text', 'textbox', 'text'].includes(object.type) && (!object.styles || typeof object.styles !== 'object')) {
                        object.styles = {};
                    }
                    const children = Array.isArray(object.objects) ? object.objects : (Array.isArray(object._objects) ? object._objects : []);
                    if (children.length) walk(children);
                });
            };
            walk(pageData.objects);
            return pageData;
        }

        function aaRepairCanvasTextStylesForSnapshot(canvas) {
            if (!canvas || typeof canvas.getObjects !== 'function') return;
            const walk = objects => {
                objects.forEach(object => {
                    if (!object) return;
                    if (['i-text', 'textbox', 'text'].includes(object.type) && (!object.styles || typeof object.styles !== 'object')) {
                        object.styles = {};
                        object.dirty = true;
                    }
                    if (typeof object.getObjects === 'function') {
                        walk(object.getObjects());
                    }
                });
            };
            walk(canvas.getObjects());
        }

        function normalizePhotoboothFrames(frames) {
            const source = Array.isArray(frames) && frames.length ? frames : [1, 2, 3].map(createPhotoboothFrameData);
            const normalized = source.slice(0, 3).map((frame, index) => {
                const slotCount = Math.max(1, Math.min(3, Number(frame?.slotCount || frame?.slot_count || index + 1) || index + 1));
                return aaNormalizePhotoboothFrameSlots(aaNormalizeFabricTextStylesInData(sanitizeFabricPageData({
                    ...createPhotoboothFrameData(slotCount, index),
                    ...frame,
                    id: frame?.id || `photobooth-frame-${slotCount}`,
                    title: frame?.title || `${slotCount} Foto`,
                    frameKey: frame?.frameKey || `photobooth-${slotCount}`,
                    slotCount,
                    hidden: frame?.hidden === true,
                    renderer: 'fabric-page',
                })));
            });

            while (normalized.length < 3) {
                normalized.push(createPhotoboothFrameData(normalized.length + 1, normalized.length));
            }

            return normalized;
        }

        function getCurrentPhotoboothFrameData() {
            aaRepairCanvasTextStylesForSnapshot(state.canvas);
            aaLockPhotoboothSlotsOnCanvas(state.canvas);
            const data = state.canvas.toJSON(serializedObjectProps);
            const currentFrame = state.photoboothFrames[state.activePhotoboothFrameIndex] || createPhotoboothFrameData(1);
            data.id = currentFrame.id || `photobooth-frame-${state.activePhotoboothFrameIndex + 1}`;
            data.title = currentFrame.title || `${state.activePhotoboothFrameIndex + 1} Foto`;
            data.frameKey = currentFrame.frameKey || `photobooth-${state.activePhotoboothFrameIndex + 1}`;
            data.slotCount = Math.max(1, Math.min(3, Number(currentFrame.slotCount || state.activePhotoboothFrameIndex + 1) || 1));
            data.hidden = currentFrame.hidden === true;
            data.artboard = {
                width: state.canvas.getWidth(),
                height: state.canvas.getHeight(),
            };
            data.background = state.canvas.backgroundColor || currentFrame.background || '#f8f4ea';
            data.backgroundColor = state.canvas.backgroundColor || currentFrame.backgroundColor || '#f8f4ea';
            data.renderer = 'fabric-page';
            return aaNormalizePhotoboothFrameSlots(sanitizeFabricPageData(data));
        }

        function getCurrentPageData() {
            aaRepairCanvasTextStylesForSnapshot(state.canvas);
            aaRepairLegacyGuestNameObjects(state.canvas);
            const data = state.canvas.toJSON(serializedObjectProps);
            const currentPage = state.pages[state.activePageIndex] || {};
            data.id = state.pages[state.activePageIndex]?.id || `page-${Date.now()}`;
            data.title = state.pages[state.activePageIndex]?.title || `Halaman ${state.activePageIndex + 1}`;
            data.hidden = currentPage.hidden === true;
            data.artboard = {
                width: state.canvas.getWidth(),
                height: state.canvas.getHeight(),
            };
            data.background = state.canvas.backgroundColor || '#ffffff';
            data.backgroundColor = state.canvas.backgroundColor || '#ffffff';
            data.renderer = 'fabric-page';
            if (currentPage.aaImportReferencePage === true || (data.objects || []).some(object => object?.aaImportReference === true)) {
                data.aaImportReferencePage = true;
            }
            return sanitizeFabricPageData(data);
        }

        function getCurrentOpeningData() {
            const data = state.canvas.toJSON(serializedObjectProps);
            const currentOpening = normalizeOpeningConfig(state.opening);
            return normalizeOpeningConfig({
                ...currentOpening,
                mode: 'custom',
                objects: data.objects || [],
                artboard: {
                    width: state.canvas.getWidth(),
                    height: state.canvas.getHeight(),
                },
                background: state.canvas.backgroundColor || currentOpening.background || '#0f766e'
            });
        }

        function storeCurrentPage() {
            if (!state.canvas || !state.pages.length) return;
            if (state.editMode === 'opening') {
                state.opening = getCurrentOpeningData();
                return;
            }
            if (state.editMode === 'photobooth') {
                state.photoboothFrames = normalizePhotoboothFrames(state.photoboothFrames);
                state.photoboothFrames[state.activePhotoboothFrameIndex] = getCurrentPhotoboothFrameData();
                return;
            }
            state.pages[state.activePageIndex] = getCurrentPageData();
        }

        function renderPageList() {
            if (!els.aaPageList) return;

            if (els.aaPageCount) {
                els.aaPageCount.textContent = state.editMode === 'opening'
                    ? 'Opening'
                    : state.editMode === 'photobooth'
                    ? 'Photobooth'
                    : `${state.pages.length} page${state.pages.length > 1 ? 's' : ''}`;
            }
            const activeFrame = document.getElementById('aaActiveArtboardFrame');
            els.aaPageList.querySelectorAll('canvas').forEach(canvasEl => {
                if (canvasEl.__aaPagePreview && typeof canvasEl.__aaPagePreview.dispose === 'function') {
                    try {
                        canvasEl.__aaPagePreview.__aaDisposed = true;
                        canvasEl.__aaPagePreview.dispose();
                    } catch (error) {}
                    canvasEl.__aaPagePreview = null;
                }
            });
            els.aaPageList.innerHTML = '';

            if (state.editMode === 'opening') {
                const block = document.createElement('section');
                block.className = 'editor-page-block active';
                block.dataset.pageIndex = 'opening';

                const controls = document.createElement('div');
                controls.className = 'page-top-controls';
                const titleButton = document.createElement('button');
                titleButton.type = 'button';
                titleButton.className = 'page-title-button';
                titleButton.innerHTML = '<span>Opening / Buka Undangan</span>';
                const actions = document.createElement('span');
                actions.className = 'aa-page-actions';
                const backButton = document.createElement('button');
                backButton.type = 'button';
                backButton.className = 'aa-page-action';
                backButton.title = 'Kembali ke halaman undangan';
                backButton.innerHTML = '<i class="fa fa-arrow-right"></i>';
                backButton.addEventListener('click', event => {
                    event.stopPropagation();
                    switchEditorMode('pages');
                });
                const addButton = document.createElement('button');
                addButton.type = 'button';
                addButton.className = 'aa-page-action';
                addButton.title = 'Tambah tombol Buka Undangan';
                addButton.innerHTML = '<i class="fa fa-play"></i>';
                addButton.addEventListener('click', event => {
                    event.stopPropagation();
                    addOpeningButton();
                });
                const exitSelect = document.createElement('select');
                exitSelect.className = 'aa-opening-exit-select';
                exitSelect.title = 'Animasi keluar opening';
                [
                    ['fade', 'Fade'],
                    ['slide-up', 'Slide Up'],
                    ['zoom-out', 'Zoom Out'],
                    ['blur-fade', 'Blur Fade'],
                    ['curtain', 'Curtain'],
                    ['elegant-lift', 'Elegant Lift']
                ].forEach(([value, label]) => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    option.selected = (state.opening.exitAnimation || 'fade') === value;
                    exitSelect.appendChild(option);
                });
                exitSelect.addEventListener('change', event => {
                    state.opening = normalizeOpeningConfig({
                        ...state.opening,
                        exitAnimation: event.target.value
                    });
                    storeCurrentPage();
                    snapshot();
                    setStatus('Animasi keluar opening diperbarui');
                });
                actions.append(addButton, exitSelect, backButton);
                controls.append(titleButton, actions);

                const wrapper = document.createElement('div');
                wrapper.className = 'canvas-page-wrapper';
                if (activeFrame) wrapper.appendChild(activeFrame);
                block.append(controls, wrapper);
                els.aaPageList.appendChild(block);
                requestAnimationFrame(syncZoomViewport);
                syncEditorModeButtons();
                return;
            }

            if (state.editMode === 'photobooth') {
                state.photoboothFrames = normalizePhotoboothFrames(state.photoboothFrames);
                const grid = document.createElement('section');
                grid.className = 'aa-photobooth-frame-board';

                state.photoboothFrames.forEach((frameData, index) => {
                    const isActive = index === state.activePhotoboothFrameIndex;
                    const isHidden = frameData.hidden === true;
                    const block = document.createElement('section');
                    block.className = `editor-page-block aa-photobooth-frame-block ${isActive ? 'active' : ''} ${isHidden ? 'is-hidden-page' : ''}`;
                    block.dataset.pageIndex = `photobooth-${index}`;
                    block.addEventListener('click', event => {
                        if (event.target.closest('button, input, textarea, select, a')) return;
                        if (!isActive) switchPhotoboothFrame(index);
                    });

                    const controls = document.createElement('div');
                    controls.className = 'page-top-controls aa-photobooth-frame-controls';
                    const titleButton = document.createElement('button');
                    titleButton.type = 'button';
                    titleButton.className = 'page-title-button aa-photobooth-frame-title';
                    titleButton.title = 'Klik untuk pilih frame Photobooth ini.';
                    titleButton.innerHTML = `<span>${escapeHtml(`${index + 1}. ${isHidden ? '[Nonaktif] ' : ''}${frameData.title || `${index + 1} Foto`}`)}</span>`;
                    titleButton.addEventListener('click', () => switchPhotoboothFrame(index));
                    const actions = document.createElement('span');
                    actions.className = 'aa-page-actions';
                    const visibilityBtn = document.createElement('button');
                    visibilityBtn.type = 'button';
                    visibilityBtn.className = 'aa-page-action';
                    visibilityBtn.title = isHidden ? 'Aktifkan frame Photobooth' : 'Nonaktifkan frame Photobooth';
                    visibilityBtn.innerHTML = `<i class="fa ${isHidden ? 'fa-eye-slash' : 'fa-eye'}"></i>`;
                    visibilityBtn.addEventListener('click', event => {
                        event.stopPropagation();
                        togglePhotoboothFrameHidden(index);
                    });
                    actions.append(visibilityBtn);
                    controls.append(titleButton, actions);

                    const wrapper = document.createElement('div');
                    wrapper.className = 'canvas-page-wrapper aa-photobooth-frame-wrapper';

                    const artboard = frameData.artboard || {};
                    const width = Math.max(1, Number(artboard.width || 1080));
                    const height = Math.max(1, Number(artboard.height || 1350));
                    wrapper.style.setProperty('--aa-photobooth-frame-height', height + 'px');

                    if (isActive) {
                        if (activeFrame) wrapper.appendChild(activeFrame);
                    } else {
                        const previewFrame = document.createElement('button');
                        previewFrame.type = 'button';
                        previewFrame.className = 'aa-page-preview-frame aa-photobooth-preview-frame';
                        previewFrame.style.width = width + 'px';
                        previewFrame.style.height = height + 'px';
                        previewFrame.title = 'Klik untuk edit frame Photobooth ini';
                        const previewCanvas = document.createElement('canvas');
                        previewCanvas.width = width;
                        previewCanvas.height = height;
                        const overlay = document.createElement('span');
                        overlay.className = 'aa-page-preview-overlay';
                        overlay.textContent = 'Edit frame';
                        previewFrame.append(previewCanvas, overlay);
                        previewFrame.addEventListener('click', () => switchPhotoboothFrame(index));
                        wrapper.appendChild(previewFrame);
                        renderPagePreview(previewCanvas, frameData);
                    }

                    block.append(controls, wrapper);
                    grid.appendChild(block);
                });

                els.aaPageList.appendChild(grid);
                requestAnimationFrame(syncZoomViewport);
                syncEditorModeButtons();
                return;
            }

            state.pages.forEach((pageData, index) => {
                const isHidden = pageData.hidden === true;
                const isActive = index === state.activePageIndex;
                const block = document.createElement('section');
                block.className =
                    `editor-page-block ${isActive ? 'active' : ''} ${isHidden ? 'is-hidden-page' : ''}`;
                block.dataset.pageIndex = String(index);
                block.addEventListener('click', event => {
                    if (isActive || event.target.closest('button, input, textarea, select, a'))
                        return;
                    switchPage(index);
                });

                const wrapper = document.createElement('div');
                wrapper.className = 'canvas-page-wrapper';

                const artboard = pageData.artboard || {};
                const width = Math.max(1, Number(artboard.width || 1080));
                const height = Math.max(1, Number(artboard.height || 1920));

                if (isActive) {
                    if (activeFrame) wrapper.appendChild(activeFrame);
                } else {
                    const previewFrame = document.createElement('button');
                    previewFrame.type = 'button';
                    previewFrame.className = 'aa-page-preview-frame';
                    previewFrame.style.width = width + 'px';
                    previewFrame.style.height = height + 'px';
                    previewFrame.title = 'Klik untuk edit halaman ini';
                    const previewCanvas = document.createElement('canvas');
                    previewCanvas.width = width;
                    previewCanvas.height = height;
                    const overlay = document.createElement('span');
                    overlay.className = 'aa-page-preview-overlay';
                    overlay.textContent = 'Edit halaman ini';
                    previewFrame.append(previewCanvas, overlay);
                    previewFrame.addEventListener('click', () => switchPage(index));
                    wrapper.appendChild(previewFrame);
                    renderPagePreview(previewCanvas, pageData);
                }

                block.append(createPageControls(index), wrapper, createPageInsertRow(index));
                els.aaPageList.appendChild(block);
            });

            requestAnimationFrame(syncZoomViewport);
            scrollActivePageIntoView();
            syncEditorModeButtons();
        }

        function createPageControls(index) {
            const pageData = state.pages[index] || {};
            const isHidden = pageData.hidden === true;
            const controls = document.createElement('div');
            controls.className = 'page-top-controls';

            const titleButton = document.createElement('button');
            titleButton.type = 'button';
            titleButton.className = 'page-title-button';
            titleButton.title = 'Klik untuk pilih halaman. Double click untuk rename.';
            const title = `${index + 1}. ${isHidden ? '[Hidden] ' : ''}${pageData.title || `Halaman ${index + 1}`}`;
            titleButton.innerHTML = `<span>${escapeHtml(title)}</span>`;
            titleButton.addEventListener('click', () => switchPage(index));
            titleButton.addEventListener('dblclick', event => {
                event.stopPropagation();
                renamePage(index);
            });

            const actions = document.createElement('span');
            actions.className = 'aa-page-actions';

            const makeAction = (titleText, icon, handler, extraClass = '') => {
                const action = document.createElement('button');
                action.type = 'button';
                action.className = `aa-page-action ${extraClass}`;
                action.title = titleText;
                action.innerHTML = `<i class="fa ${icon}"></i>`;
                action.addEventListener('click', event => {
                    event.stopPropagation();
                    handler();
                });
                return action;
            };

            const renameBtn = makeAction('Rename halaman', 'fa-pen', () => renamePage(index));
            const visibilityBtn = makeAction(isHidden ? 'Show halaman' : 'Hide halaman', isHidden ?
                'fa-eye-slash' : 'fa-eye', () => togglePageHidden(index));
            const moveUpBtn = makeAction('Move up', 'fa-arrow-up', () => movePage(index, -1));
            moveUpBtn.disabled = index === 0;
            const moveDownBtn = makeAction('Move down', 'fa-arrow-down', () => movePage(index, 1));
            moveDownBtn.disabled = index === state.pages.length - 1;
            const duplicateBtn = makeAction('Duplicate halaman', 'fa-copy', () => {
                switchPage(index);
                window.setTimeout(duplicatePage, 0);
            });
            const moreWrap = document.createElement('span');
            moreWrap.className = 'page-menu-wrap';
            const moreBtn = makeAction('Menu halaman', 'fa-ellipsis', () => {
                document.querySelectorAll('.page-menu-wrap.is-open').forEach(menu => {
                    if (menu !== moreWrap) menu.classList.remove('is-open');
                });
                moreWrap.classList.toggle('is-open');
            });
            const moreMenu = document.createElement('div');
            moreMenu.className = 'page-more-menu';

            const makeMenuItem = (label, icon, handler, danger = false) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = `page-menu-item ${danger ? 'is-danger' : ''}`;
                item.innerHTML = `<i class="fa ${icon}"></i><span>${label}</span>`;
                item.addEventListener('click', event => {
                    event.stopPropagation();
                    moreWrap.classList.remove('is-open');
                    handler();
                });
                return item;
            };

            const duplicateItem = makeMenuItem('Duplicate', 'fa-copy', () => {
                switchPage(index);
                window.setTimeout(duplicatePage, 0);
            });
            const moveUpItem = makeMenuItem('Move up', 'fa-arrow-up', () => movePage(index, -1));
            moveUpItem.disabled = index === 0;
            const moveDownItem = makeMenuItem('Move down', 'fa-arrow-down', () => movePage(index, 1));
            moveDownItem.disabled = index === state.pages.length - 1;
            const deleteItem = makeMenuItem('Delete', 'fa-trash', () => {
                switchPage(index);
                window.setTimeout(deletePage, 0);
            }, true);

            moreMenu.append(duplicateItem, moveUpItem, moveDownItem, deleteItem);
            moreWrap.append(moreBtn, moreMenu);

            actions.append(moveUpBtn, moveDownBtn, duplicateBtn, renameBtn, visibilityBtn, moreWrap);
            controls.append(titleButton, actions);
            return controls;
        }

        function createPageInsertRow(index) {
            const row = document.createElement('div');
            row.className = 'page-insert-row';
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-insert-button';
            button.innerHTML = '<i class="fa fa-plus"></i><span>Tambah halaman</span>';
            button.addEventListener('click', event => {
                event.stopPropagation();
                addPageAfter(index);
            });
            row.appendChild(button);
            return row;
        }

        async function renderPagePreview(canvasEl, pageData) {
            if (!canvasEl || !window.fabric || !pageData) return;

            let previewData = sanitizeFabricPageData(JSON.parse(JSON.stringify(pageData)));
            if (typeof window.prepareFabricPageDataImages === 'function') {
                try {
                    previewData = await window.prepareFabricPageDataImages(previewData);
                } catch (error) {
                    console.warn('Preview media preflight gagal:', error);
                }
            }
            const width = previewData.artboard?.width || 1080;
            const height = previewData.artboard?.height || 1920;

            window.setTimeout(() => {
                if (!canvasEl || !canvasEl.isConnected) return;

                // Kalau preview lama masih menempel, dispose dulu secara aman.
                if (canvasEl.__aaPagePreview && typeof canvasEl.__aaPagePreview.dispose === 'function') {
                    try {
                        canvasEl.__aaPagePreview.__aaDisposed = true;
                        canvasEl.__aaPagePreview.dispose();
                    } catch (error) {}
                    canvasEl.__aaPagePreview = null;
                }

                const preview = new fabric.StaticCanvas(canvasEl, {
                    width,
                    height,
                    renderOnAddRemove: false,
                    enableRetinaScaling: false,
                });

                preview.__aaDisposed = false;
                preview.backgroundColor = previewData.background || previewData.backgroundColor ||
                    '#ffffff';
                canvasEl.__aaPagePreview = preview;

                const safeRenderPreview = () => {
                    if (!preview || preview.__aaDisposed) return;
                    if (!canvasEl || !canvasEl.isConnected) return;
                    if (!preview.contextContainer) return;

                    try {
                        preview.renderAll();
                    } catch (error) {
                        // Mencegah error clearRect ketika preview sudah terlanjur dispose.
                        if (!String(error?.message || '').includes('clearRect')) {
                            console.warn('Preview render gagal:', error);
                        }
                    }
                };

                preview.loadFromJSON(previewData, function() {
                    if (!preview || preview.__aaDisposed) return;
                    if (!canvasEl || !canvasEl.isConnected) return;

                    preview.getObjects().forEach(object => {
                        object.selectable = false;
                        object.evented = false;
                    });

                    safeRenderPreview();
                });
            }, 0);
        }

        function scrollActivePageIntoView() {
            const activeBlock = els.aaPageList?.querySelector('.editor-page-block.active');
            if (!activeBlock) return;
            window.setTimeout(() => {
                activeBlock.scrollIntoView({
                    block: 'nearest',
                    inline: 'nearest',
                });
            }, 30);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function updateCanvasRatioButtons() {
            if (!state.canvas) return;

            const width = Math.round(state.canvas.getWidth());
            const height = Math.round(state.canvas.getHeight());
            const ratios = {
                aaPortraitBtn: width === 1080 && height === 1920,
                aaTallPortraitBtn: width === 1080 && height === 2280,
                aaSquareBtn: width === 1080 && height === 1080,
            };

            Object.entries(ratios).forEach(([id, active]) => {
                const button = document.getElementById(id);
                if (button) button.classList.toggle('is-active', active);
            });
        }

        function bindCtrlWheelZoom() {
    if (state.__aaCtrlWheelZoomBound) return;
    state.__aaCtrlWheelZoomBound = true;

    const ZOOM_MIN = 0.08;
    const ZOOM_MAX = 2.5;

    function clampZoom(value) {
        value = Number(value) || 1;
        return Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, value));
    }

    function isZoomArea(target) {
        if (!target) return false;

        return !!target.closest(
            '#aaActiveArtboardFrame, ' +
            '.canvas-page-wrapper, ' +
            '.editor-page-block.active, ' +
            '#aaPageList, ' +
            '.aa-editor-stage, ' +
            '.aa-stage-wrap, ' +
            '.canvas-container'
        );
    }

    function applyWheelZoom(event) {
            if (!state.canvas) return;

            const isCtrlOrCommand = event.ctrlKey || event.metaKey;

            if (!isCtrlOrCommand) return;
            if (!isZoomArea(event.target)) return;

            event.preventDefault();
            event.stopPropagation();

            const currentZoom = Number(state.zoom) || 1;

            // deltaY negatif = scroll ke atas = zoom in
            // deltaY positif = scroll ke bawah = zoom out
            const direction = event.deltaY < 0 ? 1 : -1;

            // Step dibuat halus, tidak terlalu lompat.
            const step = event.shiftKey ? 0.02 : 0.06;
            const nextZoom = clampZoom(currentZoom + (direction * step));

            if (Math.abs(nextZoom - currentZoom) < 0.001) return;

            if (typeof setEditorZoom === 'function') {
                setEditorZoom(nextZoom, { manual: true });
            } else {
                state.zoom = nextZoom;
                state.zoomManuallyAdjusted = true;
                if (typeof updateZoom === 'function') {
                    updateZoom();
                }
            }

            if (typeof syncZoomViewport === 'function') {
                requestAnimationFrame(syncZoomViewport);
            }
        }

        document.addEventListener('wheel', applyWheelZoom, {
            passive: false,
            capture: true
        });
    }

        async function loadPageData(pageData, options = {}) {
            const shouldSnapshot = options.snapshot !== false;
            const preserveZoom = options.preserveZoom === true;
            const shouldShowLoading = options.loading !== false && options.showLoading !== false;
            if (typeof finishImageCropPanMode === 'function') {
                finishImageCropPanMode(false, false);
            }
            state.isRestoring = true;
            if (shouldShowLoading) {
                showCanvasLoading('Memuat font dan desain...');
            }
            state.loadPromise = new Promise(resolve => {
                state.resolveLoadPromise = resolve;
            });

            pageData = sanitizeFabricPageData(JSON.parse(JSON.stringify(pageData || {})));
            if (typeof window.prepareFabricPageDataImages === 'function') {
                try {
                    pageData = await window.prepareFabricPageDataImages(pageData);
                } catch (error) {
                    console.warn('Canvas media preflight gagal:', error);
                }
            }

            state.canvas.discardActiveObject();
            state.canvas.clear();
            state.canvas.setWidth(pageData.artboard?.width || 1080);
            state.canvas.setHeight(pageData.artboard?.height || 1920);
            state.canvas.backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';
            els.aaBackgroundInput.value = normalizeColor(state.canvas.backgroundColor);

            state.canvas.loadFromJSON(pageData, function() {
                state.canvas.setWidth(pageData.artboard?.width || 1080);
                state.canvas.setHeight(pageData.artboard?.height || 1920);
                state.canvas.backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';
                    loadFontsForObjects(state.canvas.getObjects()).then(() => {
                        applyStoredObjectLocks(state.canvas);
                        if (state.editMode === 'photobooth') {
                            aaLockPhotoboothSlotsOnCanvas(state.canvas);
                        }
                        aaRepairLegacyGuestNameObjects(state.canvas);
                    refreshImageBorderRadius(state.canvas);
                    if (state.editMode === 'opening') {
                        state.canvas.getObjects().forEach(object => {
                            const objects = object?.type === 'group' && object.getObjects ? object.getObjects() : [
                                object
                            ];
                            objects.forEach(item => {
                                if (!item || item.type !== 'image') return;
                                if (item.aaImageEffectPreset && Array.isArray(item.filters)) {
                                    item.set ? item.set('filters', []) : (item.filters = []);
                                    item.dirty = true;
                                }
                            });
                        });
                    }
                    ensureBackgroundImageBack();
                    if (typeof window.aaRestoreCanvasMaterials === 'function') {
                        window.aaRestoreCanvasMaterials(state.canvas);
                    }
                    recalculateTextObjects(state.canvas);
                    aaUpgradeCanvasTextObjectsToTextbox(state.canvas);
                    recalculateTextObjects(state.canvas);
                    if (typeof window.aaRestoreCanvasMaterials === 'function') {
                        window.aaRestoreCanvasMaterials(state.canvas);
                    }
                    state.canvas.renderAll();
                    state.isRestoring = false;
                    syncInspector();
                    if (typeof aaScheduleLayerPanelRender === 'function') aaScheduleLayerPanelRender();
                    renderPageList();
                    if (typeof syncBackgroundImageControls === 'function') syncBackgroundImageControls();
                    if (typeof window.updateReferenceMapperUi === 'function') window.updateReferenceMapperUi();
                    if (typeof window.updateOcrTextUi === 'function') window.updateOcrTextUi();
                    updateCanvasRatioButtons();
                    if (preserveZoom || state.zoomManuallyAdjusted === true) {
                        updateZoom();
                    } else {
                        fitZoom();
                    }
                    if (shouldShowLoading) {
                        hideCanvasLoading();
                    }
                    if (shouldSnapshot) snapshot();
                    if (typeof state.resolveLoadPromise === 'function') {
                        state.resolveLoadPromise();
                        state.resolveLoadPromise = null;
                    }
                });
            });
        }

        function syncEditorModeButtons() {
            els.aaEditOpeningBtn?.classList.toggle('is-active', state.editMode === 'opening');
            els.aaEditPhotoboothBtn?.classList.toggle('is-active', state.editMode === 'photobooth');
            els.aaEditPagesBtn?.classList.toggle('is-active', state.editMode === 'pages');
            document.getElementById('aaEditBusinessProfilePagesBtn')?.classList.toggle('is-active', state.editMode === 'pages');
            syncEditorToolLimitedMode();
            if (typeof aaSyncAnimationModeLock === 'function') {
                aaSyncAnimationModeLock(state.canvas?.getActiveObject?.());
            }
        }

        function isEditorToolLimitedMode() {
            return state.editMode === 'photobooth' ||
                (typeof isBusinessProfileProject === 'function' && isBusinessProfileProject());
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

        function switchEditorMode(mode) {
            const requestedMode = mode === 'opening' ? 'opening' : (mode === 'photobooth' ? 'photobooth' : 'pages');
            const nextMode = state.projectIntent === 'business_profile' && requestedMode === 'photobooth'
                ? 'pages'
                : requestedMode;
            if (state.editMode === nextMode) return;
            if (typeof window.cancelReferenceMapperMode === 'function') window.cancelReferenceMapperMode();
            const hadOcrReview = state.__aaOcrReviewActive === true;
            if (typeof window.cancelOcrTextReview === 'function') window.cancelOcrTextReview('Review AdaAcara AI dibatalkan');
            if (hadOcrReview) return;
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            storeCurrentPage();
            state.editMode = nextMode;
            syncEditorToolLimitedMode();
            if (nextMode === 'opening') {
                loadPageData(openingToPageData(state.opening), {
                    preserveZoom: true,
                    snapshot: false,
                    loading: false
                });
                setStatus('Mode opening aktif');
                return;
            }
            if (nextMode === 'photobooth') {
                state.photoboothFrames = normalizePhotoboothFrames(state.photoboothFrames);
                state.activePhotoboothFrameIndex = Math.max(0, Math.min(Number(state.activePhotoboothFrameIndex) || 0, state.photoboothFrames.length - 1));
                loadPageData(state.photoboothFrames[state.activePhotoboothFrameIndex], {
                    preserveZoom: true,
                    snapshot: false,
                    loading: false
                });
                setStatus('Mode Photobooth aktif');
                return;
            }
            loadPageData(state.pages[state.activePageIndex] || createBlankPageData('Halaman 1'), {
                preserveZoom: true,
                snapshot: false,
                loading: false
            });
            setStatus('Mode halaman aktif');
        }

        function switchPhotoboothFrame(index) {
            state.photoboothFrames = normalizePhotoboothFrames(state.photoboothFrames);
            if (!state.photoboothFrames[index]) return;
            if (typeof window.cancelReferenceMapperMode === 'function') window.cancelReferenceMapperMode();
            const hadOcrReview = state.__aaOcrReviewActive === true;
            if (typeof window.cancelOcrTextReview === 'function') window.cancelOcrTextReview('Review AdaAcara AI dibatalkan');
            if (hadOcrReview) return;
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            if (index === state.activePhotoboothFrameIndex && state.editMode === 'photobooth') return;
            storeCurrentPage();
            state.editMode = 'photobooth';
            state.activePhotoboothFrameIndex = index;
            loadPageData(state.photoboothFrames[index], {
                preserveZoom: true,
                loading: false
            });
        }

        function togglePhotoboothFrameHidden(index) {
            state.photoboothFrames = normalizePhotoboothFrames(state.photoboothFrames);
            const frame = state.photoboothFrames[index];
            if (!frame) return;
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            if (index === state.activePhotoboothFrameIndex && state.editMode === 'photobooth') {
                storeCurrentPage();
            }

            const nextHidden = frame.hidden !== true;
            const activeCount = state.photoboothFrames.filter((item, itemIndex) => {
                if (!item) return false;
                if (itemIndex === index) return !nextHidden;
                return item.hidden !== true;
            }).length;

            if (activeCount < 1) {
                showEditorToast('Minimal 1 Frame Photobooth harus aktif.', 'error');
                return;
            }

            state.photoboothFrames[index].hidden = nextHidden;
            renderPageList();
            snapshot();
            setStatus(nextHidden ? 'Frame Photobooth dinonaktifkan dari halaman memories' : 'Frame Photobooth diaktifkan kembali');
        }

        function switchPage(index) {
            if (!state.pages[index]) return;
            if (typeof window.cancelReferenceMapperMode === 'function') window.cancelReferenceMapperMode();
            const hadOcrReview = state.__aaOcrReviewActive === true;
            if (typeof window.cancelOcrTextReview === 'function') window.cancelOcrTextReview('Review AdaAcara AI dibatalkan');
            if (hadOcrReview) return;
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            if (state.editMode !== 'pages') {
                storeCurrentPage();
                state.editMode = 'pages';
                state.activePageIndex = index;
                loadPageData(state.pages[index], {
                    preserveZoom: true,
                    loading: false
                });
                setStatus('Mode halaman aktif');
                return;
            }
            if (index === state.activePageIndex) return;
            storeCurrentPage();
            state.activePageIndex = index;
            loadPageData(state.pages[index], {
                preserveZoom: true,
                loading: false
            });
        }

        function addPage() {
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            storeCurrentPage();
            state.editMode = 'pages';
            const pageData = createBlankPageData(`Halaman ${state.pages.length + 1}`);
            state.pages.push(pageData);
            state.activePageIndex = state.pages.length - 1;
            loadPageData(pageData);
        }

        function addPageAfter(index) {
            const insertAfterIndex = Math.max(0, Math.min(Number(index) || 0, state.pages.length - 1));
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            storeCurrentPage();
            state.editMode = 'pages';
            const pageData = createBlankPageData(`Halaman ${state.pages.length + 1}`);
            state.pages.splice(insertAfterIndex + 1, 0, pageData);
            state.activePageIndex = insertAfterIndex + 1;
            loadPageData(pageData);
        }

        function duplicatePage() {
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            storeCurrentPage();
            const source = state.pages[state.activePageIndex] || createBlankPageData('Halaman 1');
            const copy = JSON.parse(JSON.stringify(source));
            copy.id = `page-${Date.now()}-${Math.random().toString(16).slice(2)}`;
            copy.title = `${source.title || `Halaman ${state.activePageIndex + 1}`} Copy`;
            state.pages.splice(state.activePageIndex + 1, 0, copy);
            state.activePageIndex += 1;
            loadPageData(copy);
        }

        async function deletePage() {
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }
            if (state.pages.length <= 1) {
                showEditorToast('Minimal harus ada 1 halaman.', 'error');
                return;
            }

            if (!await aaConfirm('Hapus halaman ini?', {
                    title: 'Hapus Halaman',
                    okText: 'Hapus',
                    cancelText: 'Batal',
                    danger: true
                })) return;

            const deletedPage = state.pages[state.activePageIndex] || null;
            if (typeof window.cleanupAcaraAiDeletedPage === 'function') {
                window.cleanupAcaraAiDeletedPage(deletedPage);
            }
            state.pages.splice(state.activePageIndex, 1);
            state.activePageIndex = Math.max(0, Math.min(state.activePageIndex, state.pages.length - 1));
            loadPageData(state.pages[state.activePageIndex]);
        }

        function movePage(index, direction) {
            const targetIndex = index + direction;
            if (targetIndex < 0 || targetIndex >= state.pages.length) return;
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }

            storeCurrentPage();
            const [page] = state.pages.splice(index, 1);
            state.pages.splice(targetIndex, 0, page);

            if (state.activePageIndex === index) {
                state.activePageIndex = targetIndex;
            } else if (direction < 0 && state.activePageIndex === targetIndex) {
                state.activePageIndex = index;
            } else if (direction > 0 && state.activePageIndex === targetIndex) {
                state.activePageIndex = index;
            }

            renderPageList();
            snapshot();
            setStatus('Urutan halaman diperbarui');
        }

        function renamePage(index) {
            const page = state.pages[index];
            if (!page) return;
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }

            if (index === state.activePageIndex) {
                storeCurrentPage();
            }

            const currentTitle = page.title || `Halaman ${index + 1}`;
            const nextTitle = prompt('Nama halaman:', currentTitle);
            if (nextTitle === null) return;

            const cleanTitle = nextTitle.trim();
            if (!cleanTitle) {
                showEditorToast('Nama halaman tidak boleh kosong.', 'error');
                return;
            }

            state.pages[index].title = cleanTitle.slice(0, 80);
            renderPageList();
            snapshot();
            setStatus('Nama halaman diperbarui');
        }

        function togglePageHidden(index) {
            const page = state.pages[index];
            if (!page) return;
            if (state.isCropping) {
                finishCropMode(true);
                snapshot();
            }

            if (index === state.activePageIndex) {
                storeCurrentPage();
            }

            state.pages[index].hidden = page.hidden !== true;
            renderPageList();
            snapshot();
            setStatus(state.pages[index].hidden ? 'Halaman disembunyikan dari preview/public' :
                'Halaman ditampilkan kembali');
        }
