        function bindReferenceMapperControls() {
            if (config.referenceMapperEnabled !== true) return;
            if (state.__aaReferenceMapperBound === true) return;
            state.__aaReferenceMapperBound = true;

            const MIN_REGION_SIZE = 10;
            const MAPPER_TEMP_TYPE = 'reference-mapper-temp';
            let mapper = null;

            function activeReferencePage() {
                const page = state.pages[state.activePageIndex] || null;
                if (!page || state.editMode === 'opening') return null;
                if (page.aaImportReferencePage === true) return page;
                if ((page.objects || []).some(object => object?.aaImportReference === true)) return page;
                if (state.canvas?.getObjects?.().some(object => object?.aaImportReference === true)) return page;
                return null;
            }

            function mapperAvailable() {
                return Boolean(
                    config.referenceMapperEnabled === true &&
                    state.canvas &&
                    !state.isRestoring &&
                    !state.__aaHistoryRestoring &&
                    !state.isCropping &&
                    !els.aaEditorToast?.classList.contains('is-saving') &&
                    !els.aaPreviewModal?.classList.contains('is-open') &&
                    !els.aaPublishModal?.classList.contains('is-open') &&
                    activeReferencePage()
                );
            }

            function setMapperStatus(message, tone = '') {
                if (els.aaReferenceMapperStatus) {
                    els.aaReferenceMapperStatus.textContent = message;
                    els.aaReferenceMapperStatus.classList.toggle('text-rose-600', tone === 'error');
                    els.aaReferenceMapperStatus.classList.toggle('text-teal-700', tone === 'success');
                }
            }

            function updateReferenceMapperUi() {
                if (!els.aaReferenceMapperStartBtn) return;
                const available = mapperAvailable();
                els.aaReferenceMapperStartBtn.disabled = !available;
                els.aaReferenceMapperStartBtn.classList.toggle('is-active', Boolean(mapper?.active));
                if (mapper?.active) {
                    setMapperStatus('Mode tandai aktif. Drag area di canvas, Esc untuk batal.', 'success');
                } else if (available) {
                    setMapperStatus('Siap menandai area pada page import referensi.', 'success');
                } else {
                    setMapperStatus('Aktif hanya pada page hasil Import Referensi.', '');
                }
            }

            function clamp(value, min, max) {
                return Math.max(min, Math.min(max, Number(value) || 0));
            }

            function normalizedRegion(start, end) {
                const width = Math.max(1, state.canvas.getWidth());
                const height = Math.max(1, state.canvas.getHeight());
                const x1 = clamp(start.x, 0, width);
                const y1 = clamp(start.y, 0, height);
                const x2 = clamp(end.x, 0, width);
                const y2 = clamp(end.y, 0, height);
                const left = Math.min(x1, x2);
                const top = Math.min(y1, y2);
                return {
                    left,
                    top,
                    width: Math.abs(x2 - x1),
                    height: Math.abs(y2 - y1),
                    centerX: left + Math.abs(x2 - x1) / 2,
                    centerY: top + Math.abs(y2 - y1) / 2,
                };
            }

            function hideMapperPanels() {
                els.aaReferenceMapperChoicePanel?.classList.add('hidden');
                els.aaReferenceMapperTextPanel?.classList.add('hidden');
            }

            function clearTempRect() {
                if (!mapper?.tempRect || !state.canvas) return;
                state.canvas.remove(mapper.tempRect);
                mapper.tempRect = null;
                state.canvas.requestRenderAll();
            }

            function restoreMapperInteraction() {
                if (!mapper || !state.canvas) return;
                state.canvas.selection = mapper.previousSelection;
                state.canvas.defaultCursor = mapper.previousCursor || 'default';
                state.canvas.hoverCursor = mapper.previousHoverCursor || 'move';
                (mapper.objectStates || []).forEach(item => {
                    if (!item.object) return;
                    item.object.set({
                        selectable: item.selectable,
                        evented: item.evented,
                    });
                });
                state.canvas.discardActiveObject();
                if (mapper.previousActiveObject && state.canvas.getObjects().includes(mapper.previousActiveObject)) {
                    state.canvas.setActiveObject(mapper.previousActiveObject);
                }
                state.canvas.requestRenderAll();
            }

            function cancelReferenceMapperMode(message = '') {
                if (!mapper) return;
                state.canvas?.off('mouse:down', mapper.onMouseDown);
                state.canvas?.off('mouse:move', mapper.onMouseMove);
                state.canvas?.off('mouse:up', mapper.onMouseUp);
                document.removeEventListener('keydown', mapper.onKeyDown, true);
                clearTempRect();
                restoreMapperInteraction();
                mapper = null;
                hideMapperPanels();
                if (message) setStatus(message);
                updateReferenceMapperUi();
            }

            window.cancelReferenceMapperMode = cancelReferenceMapperMode;
            window.updateReferenceMapperUi = updateReferenceMapperUi;

            function showRegionChoice(region) {
                if (!mapper) return;
                mapper.region = region;
                els.aaReferenceMapperChoicePanel?.classList.remove('hidden');
                els.aaReferenceMapperTextPanel?.classList.add('hidden');
                setMapperStatus('Pilih jenis area editable untuk region yang ditandai.', 'success');
            }

            function startReferenceMapperMode() {
                if (!mapperAvailable()) {
                    setStatus('Mapper hanya aktif pada page hasil Import Referensi.', 'error');
                    updateReferenceMapperUi();
                    return;
                }
                cancelReferenceMapperMode();
                hideMapperPanels();
                const canvas = state.canvas;
                mapper = {
                    active: true,
                    dragging: false,
                    start: null,
                    region: null,
                    tempRect: null,
                    previousSelection: canvas.selection,
                    previousCursor: canvas.defaultCursor,
                    previousHoverCursor: canvas.hoverCursor,
                    previousActiveObject: canvas.getActiveObject(),
                    objectStates: canvas.getObjects().map(object => ({
                        object,
                        selectable: object.selectable,
                        evented: object.evented,
                    })),
                };

                canvas.selection = false;
                canvas.defaultCursor = 'crosshair';
                canvas.hoverCursor = 'crosshair';
                canvas.discardActiveObject();
                canvas.getObjects().forEach(object => {
                    object.set({ selectable: false, evented: false });
                });

                mapper.onMouseDown = event => {
                    if (!mapper || !state.canvas) return;
                    const pointer = state.canvas.getPointer(event.e, false);
                    mapper.dragging = true;
                    mapper.start = {
                        x: clamp(pointer.x, 0, state.canvas.getWidth()),
                        y: clamp(pointer.y, 0, state.canvas.getHeight()),
                    };
                    clearTempRect();
                    mapper.tempRect = new fabric.Rect({
                        left: mapper.start.x,
                        top: mapper.start.y,
                        width: 1,
                        height: 1,
                        fill: 'rgba(20,184,166,.16)',
                        stroke: '#0f766e',
                        strokeWidth: 2,
                        strokeDashArray: [8, 6],
                        selectable: false,
                        evented: false,
                        excludeFromExport: true,
                        customType: MAPPER_TEMP_TYPE,
                        objectCaching: false,
                    });
                    state.canvas.add(mapper.tempRect);
                    state.canvas.requestRenderAll();
                    event.e?.preventDefault?.();
                };

                mapper.onMouseMove = event => {
                    if (!mapper?.dragging || !mapper.start || !mapper.tempRect) return;
                    const pointer = state.canvas.getPointer(event.e, false);
                    const region = normalizedRegion(mapper.start, pointer);
                    mapper.tempRect.set({
                        left: region.left,
                        top: region.top,
                        width: region.width,
                        height: region.height,
                    });
                    mapper.tempRect.setCoords();
                    state.canvas.requestRenderAll();
                    event.e?.preventDefault?.();
                };

                mapper.onMouseUp = event => {
                    if (!mapper?.dragging || !mapper.start) return;
                    mapper.dragging = false;
                    const pointer = state.canvas.getPointer(event.e, false);
                    const region = normalizedRegion(mapper.start, pointer);
                    if (region.width < MIN_REGION_SIZE || region.height < MIN_REGION_SIZE) {
                        clearTempRect();
                        setStatus('Area terlalu kecil.', 'error');
                        cancelReferenceMapperMode();
                        return;
                    }
                    showRegionChoice(region);
                    event.e?.preventDefault?.();
                };

                mapper.onKeyDown = event => {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        cancelReferenceMapperMode('Mapper dibatalkan');
                    }
                };

                canvas.on('mouse:down', mapper.onMouseDown);
                canvas.on('mouse:move', mapper.onMouseMove);
                canvas.on('mouse:up', mapper.onMouseUp);
                document.addEventListener('keydown', mapper.onKeyDown, true);
                updateReferenceMapperUi();
            }

            function finishWithObjects(objects, activeObject, message) {
                if (!mapper?.region || !state.canvas) return;
                const added = [];
                try {
                    clearTempRect();
                    objects.filter(Boolean).forEach(object => {
                        state.canvas.add(object);
                        added.push(object);
                    });
                    if (activeObject) state.canvas.setActiveObject(activeObject);
                    state.canvas.requestRenderAll();
                    storeCurrentPage();
                    snapshot();
                    if (activeObject) mapper.previousActiveObject = activeObject;
                    cancelReferenceMapperMode();
                    setStatus(message);
                } catch (error) {
                    added.forEach(object => state.canvas.remove(object));
                    cancelReferenceMapperMode();
                    setStatus(error?.message || 'Area editable gagal dibuat.', 'error');
                }
            }

            function createReferenceFrame() {
                if (!mapper?.region) return;
                const region = mapper.region;
                const frame = new fabric.Rect({
                    left: region.centerX,
                    top: region.centerY,
                    width: region.width,
                    height: region.height,
                    originX: 'center',
                    originY: 'center',
                    fill: 'rgba(15, 118, 110, 0.06)',
                    stroke: '#0f766e',
                    strokeWidth: 2,
                    strokeDashArray: [10, 8],
                    customType: 'photo-frame',
                    name: 'Frame Foto Referensi',
                    aaReferenceMapped: true,
                    aaReferenceRegionKind: 'photo-frame',
                    aaReferenceRegionId: `ref-region-${Date.now()}`,
                    objectCaching: false,
                });
                frame.setCoords();
                finishWithObjects([frame], frame, 'Frame foto editable dibuat');
            }

            function openTextMapperPanel() {
                if (!mapper?.region) return;
                els.aaReferenceMapperChoicePanel?.classList.add('hidden');
                els.aaReferenceMapperTextPanel?.classList.remove('hidden');
                if (els.aaReferenceMapperCoverColorInput) {
                    els.aaReferenceMapperCoverColorInput.value = typeof normalizeColor === 'function'
                        ? normalizeColor(state.canvas?.backgroundColor || '#ffffff')
                        : '#ffffff';
                }
            }

            function createReferenceText() {
                if (!mapper?.region || !state.canvas) return;
                const region = mapper.region;
                const regionId = `ref-region-${Date.now()}`;
                const fontSize = Math.max(8, Math.min(240, Number(els.aaReferenceMapperFontSizeInput?.value || 44)));
                const lineHeight = Math.max(0.8, Math.min(2, Number(els.aaReferenceMapperLineHeightInput?.value || 1.14)));
                const text = new fabric.Textbox(String(els.aaReferenceMapperTextInput?.value || 'Tulis teks di sini'), {
                    left: region.centerX,
                    top: region.centerY,
                    width: region.width,
                    originX: 'center',
                    originY: 'center',
                    fontFamily: String(els.aaReferenceMapperFontInput?.value || 'Inter').trim() || 'Inter',
                    fontSize,
                    fill: els.aaReferenceMapperColorInput?.value || '#111827',
                    textAlign: els.aaReferenceMapperAlignInput?.value || 'center',
                    fontWeight: els.aaReferenceMapperWeightInput?.value || '700',
                    lineHeight,
                    customType: 'text',
                    name: String(els.aaReferenceMapperNameInput?.value || 'Teks Referensi').trim() || 'Teks Referensi',
                    aaReferenceMapped: true,
                    aaReferenceRegionKind: 'text',
                    aaReferenceRegionId: regionId,
                });
                if (typeof aaApplyTextboxResizeControls === 'function') aaApplyTextboxResizeControls(text);
                text.setCoords();

                const objects = [];
                if (els.aaReferenceMapperCoverInput?.checked === true) {
                    const cover = new fabric.Rect({
                        left: region.centerX,
                        top: region.centerY,
                        width: region.width,
                        height: region.height,
                        originX: 'center',
                        originY: 'center',
                        fill: els.aaReferenceMapperCoverColorInput?.value || '#ffffff',
                        strokeWidth: 0,
                        customType: 'reference-text-cover',
                        name: 'Penutup Teks Referensi',
                        aaReferenceMapped: true,
                        aaReferenceRegionKind: 'text-cover',
                        aaReferenceRegionId: regionId,
                    });
                    cover.setCoords();
                    objects.push(cover);
                }
                objects.push(text);
                finishWithObjects(objects, text, 'Textbox editable dibuat');
            }

            els.aaReferenceMapperStartBtn?.addEventListener('click', event => {
                event.preventDefault();
                startReferenceMapperMode();
            });
            els.aaReferenceMapperFrameBtn?.addEventListener('click', createReferenceFrame);
            els.aaReferenceMapperTextBtn?.addEventListener('click', openTextMapperPanel);
            els.aaReferenceMapperCancelBtn?.addEventListener('click', () => cancelReferenceMapperMode('Mapper dibatalkan'));
            els.aaReferenceMapperBackBtn?.addEventListener('click', () => {
                els.aaReferenceMapperTextPanel?.classList.add('hidden');
                els.aaReferenceMapperChoicePanel?.classList.remove('hidden');
            });
            els.aaReferenceMapperCreateTextBtn?.addEventListener('click', createReferenceText);
            updateReferenceMapperUi();
        }
