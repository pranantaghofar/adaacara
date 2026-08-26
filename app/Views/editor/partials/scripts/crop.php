        function syncCropPanel(active) {
            const cropActive = state.isCropping && state.cropTarget && active === state.cropBox;
            const target = cropActive ? state.cropTarget : active;
            const isImage = target && target.type === 'image';
            els.aaImageRadiusPanel.classList.toggle('hidden', !isImage);
            els.aaCropPanel.classList.toggle('hidden', !isImage);
            if (!isImage) return;
            const radius = Math.max(0, Math.round(target.borderRadius || 0));
            els.aaImageRadiusInput.value = radius;
            if (els.aaImageRadiusValue) els.aaImageRadiusValue.textContent = `${radius}px`;
            syncCropActionButtons();

            const natural = getImageNaturalSize(target);
            const cropX = Math.max(0, Math.round(target.cropX || 0));
            const cropY = Math.max(0, Math.round(target.cropY || 0));
            const width = Math.max(1, Math.round(target.width || natural.width));
            const height = Math.max(1, Math.round(target.height || natural.height));

            els.aaCropXInput.max = Math.max(0, natural.width - 1);
            els.aaCropYInput.max = Math.max(0, natural.height - 1);
            els.aaCropWidthInput.max = Math.max(1, natural.width - cropX);
            els.aaCropHeightInput.max = Math.max(1, natural.height - cropY);
            els.aaCropXInput.value = cropX;
            els.aaCropYInput.value = cropY;
            els.aaCropWidthInput.value = width;
            els.aaCropHeightInput.value = height;
            els.aaCropHint.textContent =
                state.isCropping ? 'Geser/resize area crop di canvas, lalu klik Apply.' :
                `Ukuran asli gambar: ${Math.round(natural.width)} x ${Math.round(natural.height)} px`;
        }

        function syncCropActionButtons() {
            const cropping = Boolean(state.isCropping && state.cropBox && state.cropTarget);
            els.aaStartCropBtn?.classList.toggle('hidden', cropping);
            els.aaApplyCropBoxBtn?.classList.toggle('hidden', !cropping);
            els.aaCancelCropBtn?.classList.toggle('hidden', !cropping);
            els.aaResetCropBtn?.classList.toggle('hidden', cropping);
        }

        function restoreImageFrameAfterCrop(target) {
            if (!target || target.type !== 'image') return;
            const shape = String(target.aaImageFrameShape || 'none');
            const radius = Number(target.borderRadius) || 0;

            if (typeof applyImageBorderRadius === 'function') {
                applyImageBorderRadius(target, radius);
            }

            if (!shape || shape === 'none') {
                return;
            }

            if (shape === 'rounded') {
                target.set('clipPath', null);
                if (typeof applyImageBorderRadius === 'function') {
                    applyImageBorderRadius(target, radius || Math.max(24, Math.min(target.width || 1, target.height || 1) * 0.12));
                }
                return;
            }

            if (typeof createImageFrameClipPath === 'function') {
                target.set('clipPath', createImageFrameClipPath(target, shape));
            }
        }

        function applyCropFromProperties() {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;

            const natural = getImageNaturalSize(active);
            const cropX = Math.max(0, Math.min(Number(els.aaCropXInput.value) || 0, natural.width - 1));
            const cropY = Math.max(0, Math.min(Number(els.aaCropYInput.value) || 0, natural.height - 1));
            const width = Math.max(1, Math.min(Number(els.aaCropWidthInput.value) || natural.width, natural.width -
                cropX));
            const height = Math.max(1, Math.min(Number(els.aaCropHeightInput.value) || natural.height, natural
                .height - cropY));

            active.set({
                cropX,
                cropY,
                width,
                height,
            });
            restoreImageFrameAfterCrop(active);
            active.setCoords();
            active.dirty = true;
            state.canvas.requestRenderAll();
            syncCropPanel(active);
            snapshot();
            setStatus('Crop gambar diterapkan');
        }

        async function resetImageCrop() {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;

            const originalSrc = ensureImageOriginalSource(active);
            const currentSrc = readImageSourceUrl(active);
            if (originalSrc && currentSrc && originalSrc !== currentSrc) {
                setStatus('Mengembalikan gambar full...', 'saving');
                const replacement = await loadFabricImage(originalSrc);
                if (!replacement) {
                    setStatus('Gagal memuat gambar full untuk reset crop.', 'error');
                    return;
                }
                replacement.set({
                    src: originalSrc,
                    objectCaching: false,
                });
                resetImageCropState(replacement);
                replaceImageObject(active, replacement, {
                    customType: active.customType || 'image',
                    allowLockedReplace: true,
                    aaOriginalImageSrc: originalSrc,
                    aaOriginalImageName: active.aaOriginalImageName || active.galleryImageName || '',
                    galleryImageSrc: active.galleryZoom ? originalSrc : active.galleryImageSrc || '',
                    galleryImageName: active.galleryImageName || '',
                });
                setStatus('Crop gambar direset ke gambar full');
                return;
            }

            const natural = getImageNaturalSize(active);
            active.set({
                cropX: 0,
                cropY: 0,
                width: natural.width,
                height: natural.height,
                clipPath: null,
            });
            restoreImageFrameAfterCrop(active);
            active.setCoords();
            active.dirty = true;
            state.canvas.requestRenderAll();
            syncCropPanel(active);
            snapshot();
            setStatus('Crop gambar direset');
        }

        function syncAnimationButtons(active) {
            const selectedAnimation = active ? (active.aaAnimation || active.customAnimation || active.animationPreset || active.animation || active.animationName || 'none') : 'none';
            document.querySelectorAll('[data-aa-animation]').forEach(button => {
                button.classList.toggle('is-active', button.dataset.aaAnimation === selectedAnimation);
            });
            syncAnimationTimingControls(active);
        }

        function clampAnimationTiming(value, min, max, fallback) {
            value = Number(value);
            if (!Number.isFinite(value)) return fallback;
            return Math.max(min, Math.min(max, Math.round(value)));
        }

        function syncAnimationTimingControls(active) {
            const delay = clampAnimationTiming(active ? (active.aaAnimationDelay ?? active.animationDelay) : 0, 0, 5000, 0);
            const duration = clampAnimationTiming(active ? (active.aaAnimationDuration ?? active.animationDuration) : 700, 200, 8000, 700);
            document.querySelectorAll('[data-aa-animation-delay]').forEach(input => {
                input.value = String(delay);
            });
            document.querySelectorAll('[data-aa-animation-delay-output]').forEach(output => {
                output.textContent = `${delay}ms`;
            });
            document.querySelectorAll('[data-aa-animation-duration]').forEach(input => {
                input.value = String(duration);
            });
            document.querySelectorAll('[data-aa-animation-duration-output]').forEach(output => {
                output.textContent = `${duration}ms`;
            });
        }

        function updateActiveAnimationTiming(commit = false, sourceInput = null) {
            const active = state.canvas.getActiveObject();
            if (!active) return;
            const delayInput = document.querySelector('[data-aa-animation-delay]');
            const durationInput = document.querySelector('[data-aa-animation-duration]');
            const delaySource = sourceInput?.matches?.('[data-aa-animation-delay]') ? sourceInput : delayInput;
            const durationSource = sourceInput?.matches?.('[data-aa-animation-duration]') ? sourceInput : durationInput;
            const delay = clampAnimationTiming(delaySource ? delaySource.value : active.aaAnimationDelay, 0, 5000, 0);
            const duration = clampAnimationTiming(durationSource ? durationSource.value : active.aaAnimationDuration, 200, 8000, 700);
            active.set({
                animationDelay: delay,
                aaAnimationDelay: delay,
                animationDuration: duration,
                aaAnimationDuration: duration,
                animationOrderMode: 'manual'
            });
            syncAnimationTimingControls(active);
            storeCurrentPage();
            syncInspector();
            if (commit) snapshot();
            setStatus('Timing animasi diperbarui');
        }

        function normalizeColor(value) {
            try {
                return '#' + new fabric.Color(value || '#111827').toHex();
            } catch (error) {
                return '#111827';
            }
        }

        const aaFabricInlineTextStyleKeys = [
            'fontFamily', 'fontSize', 'fill', 'fontWeight', 'fontStyle', 'underline', 'linethrough', 'charSpacing',
            'lineHeight'
        ];

        function aaGetInlineTextStyleValue(text, property) {
            if (!isFabricTextObject(text) || !text.styles || typeof text.styles !== 'object') {
                return undefined;
            }

            let found = false;
            let value;

            Object.keys(text.styles).some(lineKey => {
                const lineStyles = text.styles[lineKey];
                if (!lineStyles || typeof lineStyles !== 'object') return false;

                return Object.keys(lineStyles).some(charKey => {
                    const charStyle = lineStyles[charKey];
                    if (!charStyle || typeof charStyle !== 'object' || !Object.prototype.hasOwnProperty.call(
                            charStyle, property)) {
                        return false;
                    }

                    value = charStyle[property];
                    found = true;
                    return true;
                });
            });

            return found ? value : undefined;
        }

        function aaGetTextStyleValue(text, property) {
            const inlineValue = aaGetInlineTextStyleValue(text, property);
            return inlineValue === undefined ? text?.[property] : inlineValue;
        }

        function aaSyncInlineTextStyles(text, style) {
            if (!isFabricTextObject(text) || !style || !text.styles || typeof text.styles !== 'object') return;

            const keys = aaFabricInlineTextStyleKeys.filter(key => Object.prototype.hasOwnProperty.call(style,
                key));
            if (!keys.length) return;

            Object.keys(text.styles).forEach(lineKey => {
                const lineStyles = text.styles[lineKey];
                if (!lineStyles || typeof lineStyles !== 'object') return;

                Object.keys(lineStyles).forEach(charKey => {
                    const charStyle = lineStyles[charKey];
                    if (!charStyle || typeof charStyle !== 'object') return;

                    keys.forEach(key => {
                        charStyle[key] = style[key];
                    });
                });
            });

            text.dirty = true;
        }

        function applyActiveStyle(style) {
            const active = state.canvas.getActiveObject();
            if (!active) return;
            if (isGuestNameObject(active)) {
                const text = getGuestNameTextObject(active);
                if (Object.prototype.hasOwnProperty.call(style, 'text')) {
                    setGuestNameTemplateObject(active, style.text);
                }
                if (text) {
                    const textStyle = {};
                    ['fontFamily', 'fontSize', 'fill', 'fontWeight', 'fontStyle', 'underline', 'linethrough',
                        'textAlign'
                    ]
                    .forEach(key => {
                        if (Object.prototype.hasOwnProperty.call(style, key)) textStyle[key] = style[key];
                    });
                    text.set(textStyle);
                    aaSyncInlineTextStyles(text, textStyle);
                    text.dirty = true;
                    if (typeof text.initDimensions === 'function') {
                        text.initDimensions();
                    }
                }
                active.dirty = true;
                active.setCoords();
                state.canvas.requestRenderAll();
                snapshot();
                return;
            }
            if (isGuestbookObject(active)) {
                const parts = getGuestbookObjectParts(active);
                if (parts.text) {
                    const textStyle = {};
                    ['fontFamily', 'fontSize', 'fill', 'fontWeight', 'fontStyle', 'underline'].forEach(key => {
                        if (Object.prototype.hasOwnProperty.call(style, key)) textStyle[key] = style[key];
                    });
                    if (Object.prototype.hasOwnProperty.call(style, 'text')) {
                        textStyle.text = style.text;
                        active.set({
                            placeholder: style.text,
                            buttonText: active.customType === 'guest-submit-button' ? style.text : active
                                .buttonText,
                        });
                    }
                    parts.text.set(textStyle);
                    aaSyncInlineTextStyles(parts.text, textStyle);
                    parts.text.dirty = true;
                    if (typeof parts.text.initDimensions === 'function') {
                        parts.text.initDimensions();
                    }
                }
                if (parts.box && Object.prototype.hasOwnProperty.call(style, 'backgroundColor')) {
                    parts.box.set('fill', style.backgroundColor);
                }
                if (parts.box && Object.prototype.hasOwnProperty.call(style, 'stroke')) {
                    parts.box.set('stroke', style.stroke);
                }
                active.dirty = true;
                active.setCoords();
                state.canvas.requestRenderAll();
                snapshot();
                return;
            }
            if (active.customType === 'countdown-timer') {
                const textStyle = {};
                if (Object.prototype.hasOwnProperty.call(style, 'fontFamily')) {
                    textStyle.fontFamily = style.fontFamily;
                    active.set('countdownFontFamily', style.fontFamily);
                }
                if (Object.prototype.hasOwnProperty.call(style, 'fontSize')) {
                    const fontSize = Math.max(8, Number(style.fontSize) || 36);
                    active.set('countdownFontSize', fontSize);
                }
                if (Object.prototype.hasOwnProperty.call(style, 'fill')) {
                    textStyle.fill = style.fill;
                    active.set('countdownTextColor', style.fill);
                }
                if (Object.prototype.hasOwnProperty.call(style, 'fontWeight')) {
                    textStyle.fontWeight = style.fontWeight;
                }
                if (Object.prototype.hasOwnProperty.call(style, 'fontStyle')) {
                    textStyle.fontStyle = style.fontStyle;
                }
                if (Object.prototype.hasOwnProperty.call(style, 'underline')) {
                    textStyle.underline = style.underline;
                }
                getCountdownTextObjects(active).forEach(text => {
                    const nextStyle = {
                        ...textStyle,
                    };
                    if (Object.prototype.hasOwnProperty.call(style, 'fontSize')) {
                        const fontSize = Math.max(8, Number(style.fontSize) || 36);
                        nextStyle.fontSize = text.name === 'countdown-label' ? Math.max(8, Math.round(
                            fontSize * .36)) : fontSize;
                    }
                    text.set(nextStyle);
                    aaSyncInlineTextStyles(text, nextStyle);
                    text.dirty = true;
                    if (typeof text.initDimensions === 'function') {
                        text.initDimensions();
                    }
                });
                active.dirty = true;
                active.setCoords();
                state.canvas.requestRenderAll();
                syncCountdownContextToolbar(active);
                snapshot();
                return;
            }
	            if ((active.customType === 'social-link' || active.customType === 'social-media') &&
	                typeof active.getObjects === 'function') {
	                const text = typeof getContextTextTarget === 'function' ?
	                    getContextTextTarget(active) :
	                    active.getObjects().find(child => child.name === 'social-label');
	                if (text) {
	                    const textStyle = {};
	                    ['fontFamily', 'fontSize', 'fill', 'fontWeight', 'fontStyle', 'underline', 'linethrough',
                        'textAlign', 'charSpacing', 'lineHeight'
                    ].forEach(key => {
                        if (Object.prototype.hasOwnProperty.call(style, key)) textStyle[key] = style[key];
                    });
	                    if (Object.prototype.hasOwnProperty.call(style, 'text')) {
	                        textStyle.text = style.text;
	                        active.set(active.customType === 'social-media' ? 'socialTitle' : 'socialLabel', style.text);
	                    }
	                    text.set(textStyle);
	                    aaSyncInlineTextStyles(text, textStyle);
                    text.dirty = true;
                    if (typeof text.initDimensions === 'function') {
                        text.initDimensions();
                    }
	                    if (active.customType === 'social-link' && typeof aaLayoutSocialLinkGroup === 'function') {
	                        aaLayoutSocialLinkGroup(active);
	                    }
	                    if (active.customType === 'social-media' && typeof aaUpdateInteractivePreviewText === 'function') {
	                        const activeSocialCount = typeof aaSocialActiveCount === 'function' ?
	                            aaSocialActiveCount(active.socialLinks || {}) :
	                            0;
	                        aaUpdateInteractivePreviewText(active, active.socialTitle || text.text || 'Ikuti Kami',
	                            `${activeSocialCount} link aktif`);
	                    }
	                }
	                active.dirty = true;
	                active.setCoords();
	                state.canvas.requestRenderAll();
	                syncInteractionUi?.(active);
	                syncInspector?.();
	                snapshot();
	                return;
	            }
            if (isInteractiveObject(active)) {
                const text = getNamedGroupText(active);
                if (text) {
                    const textStyle = {};
                    ['fontFamily', 'fontSize', 'fill', 'fontWeight', 'fontStyle', 'underline'].forEach(key => {
                        if (Object.prototype.hasOwnProperty.call(style, key)) textStyle[key] = style[key];
                    });
                    if (Object.prototype.hasOwnProperty.call(style, 'text')) {
                        textStyle.text = style.text;
                        active.set({
                            label: style.text,
                            buttonText: active.customType === 'scroll-next-button' ? style.text : active
                                .buttonText,
                        });
                    }
                    text.set(textStyle);
                    aaSyncInlineTextStyles(text, textStyle);
                    text.dirty = true;
                    if (typeof text.initDimensions === 'function') {
                        text.initDimensions();
                    }
                }
                active.dirty = true;
                active.setCoords();
                state.canvas.requestRenderAll();
                snapshot();
                return;
            }
            active.set(style);
            if (isFabricTextObject(active)) {
                if (active.isGuestName === true || active.customType === 'guest_name') {
                    if (Object.prototype.hasOwnProperty.call(style, 'text')) {
                        active.set({
                            templateText: style.text,
                            placeholder: guestNamePlaceholder(style.text),
                            text: guestNamePlaceholder(style.text),
                        });
                    }
                    active.set({
                        customType: 'guest_name',
                        isGuestName: true,
                        dynamicKey: 'guest_name',
                    });
                }
                aaSyncInlineTextStyles(active, style);
                active.dirty = true;
                if (typeof active.initDimensions === 'function') {
                    active.initDimensions();
                }
                active.setCoords();
            }
            state.canvas.requestRenderAll();
            snapshot();
        }

        function updateInteractiveControlStyle(values) {
            const active = state.canvas.getActiveObject();
            if (!isInteractiveObject(active)) return;
            const box = getInteractiveBox(active);
            const boxes = getInteractiveBoxes(active);
            const next = {};

            if (Object.prototype.hasOwnProperty.call(values, 'controlBackground')) {
                next.controlBackground = values.controlBackground;
                if (active.customType === 'countdown-timer') {
                    boxes.forEach(item => item.set('fill', values.controlBackground));
                } else if (box) {
                    box.set('fill', values.controlBackground);
                }
            }

            if (Object.prototype.hasOwnProperty.call(values, 'controlRadius')) {
                const radius = Math.max(0, Number(values.controlRadius) || 0);
                next.controlRadius = radius;
                if (active.customType === 'countdown-timer') {
                    boxes.forEach(item => item.set({
                        rx: radius,
                        ry: radius,
                    }));
                } else if (box) {
                    box.set({
                        rx: radius,
                        ry: radius,
                    });
                }
            }

            active.set(next);
            if (active.customType === 'countdown-timer') {
                refreshCountdownPreviewObject(active);
                syncInspector();
                return;
            }
            if (active.customType === 'photo-gallery') {
                refreshInteractivePreviewObject(active);
                return;
            }
            active.dirty = true;
            active.setCoords();
            state.canvas.requestRenderAll();
            snapshot();
        }

        function toggleTextStyle(property, activeValue, inactiveValue) {
            const target = getContextTextTarget();
            if (!isFabricTextObject(target)) return;
            const currentValue = aaGetTextStyleValue(target, property);
            const nextValue = currentValue === activeValue ? inactiveValue : activeValue;
            applyActiveStyle({
                [property]: nextValue,
            });
            syncInspector();
        }

        function toggleBoldStyle() {
            const target = getContextTextTarget();
            if (!isFabricTextObject(target)) return;
            const fontWeight = aaGetTextStyleValue(target, 'fontWeight');
            const isBold = fontWeight === 'bold' || Number(fontWeight) >= 700;
            applyActiveStyle({
                fontWeight: isBold ? 'normal' : 'bold',
            });
            syncInspector();
        }

        function getObjectLocalBounds(target) {
            const width = Math.max(1, target.width || target.getScaledWidth() / Math.abs(target.scaleX || 1));
            const height = Math.max(1, target.height || target.getScaledHeight() / Math.abs(target.scaleY || 1));

            return {
                left: -width / 2,
                right: width / 2,
                top: -height / 2,
                bottom: height / 2,
                width,
                height,
            };
        }

        function getCurrentCropBounds(target) {
            const bounds = getObjectLocalBounds(target);
            const clip = target.clipPath;

            if (clip && clip.type === 'rect' && !clip.absolutePositioned) {
                const width = Math.max(1, clip.width || bounds.width);
                const height = Math.max(1, clip.height || bounds.height);
                const centerX = clip.left || 0;
                const centerY = clip.top || 0;

                return {
                    left: centerX - width / 2,
                    right: centerX + width / 2,
                    top: centerY - height / 2,
                    bottom: centerY + height / 2,
                };
            }

            return {
                left: bounds.left,
                right: bounds.right,
                top: bounds.top,
                bottom: bounds.bottom,
            };
        }

        function setCropBounds(target, cropBounds) {
            const objectBounds = getObjectLocalBounds(target);
            const minSize = Math.max(8, Math.min(objectBounds.width, objectBounds.height) * .04);
            const left = Math.max(objectBounds.left, Math.min(cropBounds.left, cropBounds.right - minSize));
            const right = Math.min(objectBounds.right, Math.max(cropBounds.right, left + minSize));
            const top = Math.max(objectBounds.top, Math.min(cropBounds.top, cropBounds.bottom - minSize));
            const bottom = Math.min(objectBounds.bottom, Math.max(cropBounds.bottom, top + minSize));

            target.clipPath = new fabric.Rect({
                originX: 'center',
                originY: 'center',
                left: (left + right) / 2,
                top: (top + bottom) / 2,
                width: Math.max(minSize, right - left),
                height: Math.max(minSize, bottom - top),
                absolutePositioned: false,
            });
            target.dirty = true;
        }

        function getImageNaturalSize(target) {
            const element = target.getElement ? target.getElement() : null;
            return {
                width: element?.naturalWidth || target._element?.naturalWidth || (target.cropX || 0) + (target
                    .width || 1),
                height: element?.naturalHeight || target._element?.naturalHeight || (target.cropY || 0) + (target
                    .height || 1),
            };
        }

        function aaIsCropPanMobileViewport() {
            return Boolean(window.matchMedia && window.matchMedia('(max-width: 767px)').matches);
        }

        function aaGetCropPanSource(target) {
            if (!target) return '';
            if (typeof readImageSourceUrl === 'function') {
                return readImageSourceUrl(target) || '';
            }
            return target.aaAnimatedSrc || target.src || target._element?.src || '';
        }

        function aaImageHasVisibleCrop(target) {
            if (!target || target.type !== 'image') return false;
            const natural = getImageNaturalSize(target);
            const naturalWidth = Math.max(1, Number(natural.width) || 1);
            const naturalHeight = Math.max(1, Number(natural.height) || 1);
            const cropX = Math.max(0, Number(target.cropX) || 0);
            const cropY = Math.max(0, Number(target.cropY) || 0);
            const width = Math.max(1, Number(target.width) || naturalWidth);
            const height = Math.max(1, Number(target.height) || naturalHeight);
            const epsilon = 0.75;

            return (
                cropX > epsilon ||
                cropY > epsilon ||
                cropX + width < naturalWidth - epsilon ||
                cropY + height < naturalHeight - epsilon
            );
        }

        function aaCanStartImageCropPan(target) {
            if (!state.canvas || aaIsCropPanMobileViewport()) return false;
            if (!target || target.type !== 'image') return false;
            if (state.isCropping || target === state.cropBox || target.customType === 'crop-helper') return false;
            if (target.customType === 'crop-pan-ghost') return false;
            if (target.customType === 'background' || target.customType === 'import-reference-background') return false;
            if (target.locked === true || target.visible === false || target.aaFramePlaceholder === true) return false;

            const frameShape = String(target.aaImageFrameShape || '').trim().toLowerCase();
            if (frameShape && frameShape !== 'none') return false;

            const src = aaGetCropPanSource(target);
            if (typeof isGifSource === 'function' && (isGifSource(src, '') || isGifSource(target.aaAnimatedSrc || '', ''))) {
                return false;
            }

            return aaImageHasVisibleCrop(target);
        }

        function aaStoreCropPanObjectState(target) {
            if (!target) return;
            state.cropPanObjectStates = [{
                object: target,
                hasControls: target.hasControls,
                lockMovementX: target.lockMovementX,
                lockMovementY: target.lockMovementY,
                hoverCursor: target.hoverCursor,
                moveCursor: target.moveCursor,
            }];
        }

        function aaRestoreCropPanObjectState() {
            const states = Array.isArray(state.cropPanObjectStates) ? state.cropPanObjectStates : [];
            states.forEach(item => {
                if (!item?.object) return;
                item.object.set({
                    hasControls: item.hasControls,
                    lockMovementX: item.lockMovementX,
                    lockMovementY: item.lockMovementY,
                    hoverCursor: item.hoverCursor,
                    moveCursor: item.moveCursor,
                });
                item.object.setCoords();
            });
            state.cropPanObjectStates = null;
        }

        function aaRemoveCropPanFinishButton() {
            const button = state.cropPanFinishButton;
            if (button?.parentNode) {
                button.parentNode.removeChild(button);
            }
            state.cropPanFinishButton = null;
        }

        function aaEnsureCropPanFinishButton() {
            if (state.cropPanFinishButton) return state.cropPanFinishButton;

            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('aria-label', 'Selesai geser foto');
            button.title = 'Selesai geser foto';
            button.textContent = '✓';
            Object.assign(button.style, {
                position: 'fixed',
                zIndex: '12050',
                width: '30px',
                height: '30px',
                borderRadius: '999px',
                border: '1px solid rgba(20, 184, 166, .45)',
                background: 'linear-gradient(135deg, rgba(255,255,255,.96), rgba(236,253,245,.92))',
                color: '#0f766e',
                boxShadow: '0 12px 30px rgba(15, 118, 110, .18), 0 2px 8px rgba(15, 23, 42, .10)',
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '16px',
                fontWeight: '900',
                lineHeight: '1',
                cursor: 'pointer',
                userSelect: 'none',
                WebkitTapHighlightColor: 'transparent',
            });

            ['pointerdown', 'mousedown', 'mouseup', 'pointerup', 'click', 'dblclick'].forEach(eventName => {
                button.addEventListener(eventName, event => {
                    event.preventDefault();
                    event.stopPropagation();
                });
            });
            button.addEventListener('click', () => {
                finishImageCropPanMode(true, true);
                if (typeof aaScheduleLayerPanelRender === 'function') {
                    aaScheduleLayerPanelRender();
                }
                setStatus('Posisi foto di dalam crop disimpan');
            });

            document.body.appendChild(button);
            state.cropPanFinishButton = button;
            return button;
        }

        function aaPositionCropPanFinishButton() {
            const target = state.cropPanTarget;
            const button = state.cropPanFinishButton;
            if (!target || !button) return;

            const rect = canvasRectToScreen(target.getBoundingRect(true, true));
            if (!rect) return;

            const size = button.offsetWidth || 30;
            const left = Math.max(8, Math.min(window.innerWidth - size - 8, rect.left + rect.width - size / 2));
            const top = Math.max(8, Math.min(window.innerHeight - size - 8, rect.top - size / 2));
            button.style.left = `${Math.round(left)}px`;
            button.style.top = `${Math.round(top)}px`;
        }

        function aaRemoveCropPanGhost() {
            const ghost = state.cropPanGhost;
            state.cropPanGhost = null;
            if (ghost && state.canvas) {
                try {
                    state.canvas.remove(ghost);
                } catch (error) {}
            }
        }

        function aaGetCropPanGhostPosition(target, natural) {
            if (!target || !natural) return null;
            const cropX = Math.max(0, Number(target.cropX) || 0);
            const cropY = Math.max(0, Number(target.cropY) || 0);
            const width = Math.max(1, Number(target.width) || Number(natural.width) || 1);
            const height = Math.max(1, Number(target.height) || Number(natural.height) || 1);
            const naturalWidth = Math.max(1, Number(natural.width) || width);
            const naturalHeight = Math.max(1, Number(natural.height) || height);
            let shiftX = (naturalWidth / 2) - (cropX + width / 2);
            let shiftY = (naturalHeight / 2) - (cropY + height / 2);

            if (target.flipX) shiftX *= -1;
            if (target.flipY) shiftY *= -1;

            const delta = canvasDeltaFromCropStart({
                scaleX: Number(target.scaleX) || 1,
                scaleY: Number(target.scaleY) || 1,
                angle: Number(target.angle) || 0,
            }, shiftX, shiftY);
            const center = target.getCenterPoint();

            return {
                left: center.x + delta.x,
                top: center.y + delta.y,
                width: naturalWidth,
                height: naturalHeight,
            };
        }

        function aaSyncCropPanGhost() {
            const target = state.cropPanTarget;
            const ghost = state.cropPanGhost;
            if (!target || !ghost) return;

            const natural = getImageNaturalSize(target);
            const position = aaGetCropPanGhostPosition(target, natural);
            if (!position) return;

            ghost.set({
                left: position.left,
                top: position.top,
                width: position.width,
                height: position.height,
                cropX: 0,
                cropY: 0,
                scaleX: Number(target.scaleX) || 1,
                scaleY: Number(target.scaleY) || 1,
                angle: Number(target.angle) || 0,
                flipX: target.flipX === true,
                flipY: target.flipY === true,
                opacity: 0.5,
                visible: true,
            });
            ghost.setCoords();
            ghost.dirty = true;

            const objects = state.canvas?.getObjects?.() || [];
            const targetIndex = objects.indexOf(target);
            const ghostIndex = objects.indexOf(ghost);
            if (targetIndex >= 0 && ghostIndex >= 0 && ghostIndex !== Math.max(0, targetIndex - 1)) {
                state.canvas.moveTo(ghost, Math.max(0, targetIndex - 1));
            }
        }

        function aaCreateCropPanGhost(target) {
            aaRemoveCropPanGhost();
            if (!state.canvas || !target) return null;

            const element = target.getElement ? target.getElement() : target._element;
            if (!element) return null;

            const natural = getImageNaturalSize(target);
            const position = aaGetCropPanGhostPosition(target, natural);
            if (!position) return null;

            const ghost = new fabric.Image(element, {
                left: position.left,
                top: position.top,
                width: position.width,
                height: position.height,
                cropX: 0,
                cropY: 0,
                scaleX: Number(target.scaleX) || 1,
                scaleY: Number(target.scaleY) || 1,
                angle: Number(target.angle) || 0,
                flipX: target.flipX === true,
                flipY: target.flipY === true,
                originX: 'center',
                originY: 'center',
                opacity: 0.5,
                selectable: false,
                evented: false,
                hasControls: false,
                hasBorders: false,
                hoverCursor: 'default',
                moveCursor: 'default',
                customType: 'crop-pan-ghost',
                excludeFromExport: true,
                name: 'Original Photo Preview',
            });

            state.canvas.add(ghost);
            const objects = state.canvas.getObjects();
            const targetIndex = objects.indexOf(target);
            if (targetIndex >= 0) {
                state.canvas.moveTo(ghost, targetIndex);
                state.canvas.moveTo(target, targetIndex + 1);
            }
            state.cropPanGhost = ghost;
            aaSyncCropPanGhost();
            return ghost;
        }

        function aaSyncImageCropPanUi() {
            if (!state.cropPanTarget) {
                aaRemoveCropPanFinishButton();
                aaRemoveCropPanGhost();
                return;
            }

            if (!state.cropPanGhost) {
                aaCreateCropPanGhost(state.cropPanTarget);
            } else {
                aaSyncCropPanGhost();
            }

            aaEnsureCropPanFinishButton();
            aaPositionCropPanFinishButton();
        }

        function aaApplyCropPanPendingMove() {
            const drag = state.cropPanDrag;
            if (!drag || !drag.target || !drag.pendingPointer) {
                if (drag) drag.raf = null;
                return;
            }

            drag.raf = null;
            const localPoint = drag.target.toLocalPoint(
                new fabric.Point(drag.pendingPointer.x, drag.pendingPointer.y),
                'center',
                'center'
            );
            const localDx = localPoint.x - drag.startLocalX;
            const localDy = localPoint.y - drag.startLocalY;
            const nextCropX = aaClampCropNumber(drag.cropX - localDx, 0, drag.maxCropX);
            const nextCropY = aaClampCropNumber(drag.cropY - localDy, 0, drag.maxCropY);

            if (
                Math.abs(nextCropX - (Number(drag.target.cropX) || 0)) < 0.2 &&
                Math.abs(nextCropY - (Number(drag.target.cropY) || 0)) < 0.2
            ) {
                return;
            }

            drag.target.set({
                cropX: nextCropX,
                cropY: nextCropY,
            });
            restoreImageFrameAfterCrop(drag.target);
            drag.target.dirty = true;
            drag.target.setCoords();
            drag.changed = drag.changed ||
                Math.abs(nextCropX - drag.cropX) > 0.2 ||
                Math.abs(nextCropY - drag.cropY) > 0.2;
            state.cropPanDirty = state.cropPanDirty || drag.changed;
            aaSyncCropPanGhost();
            aaPositionCropPanFinishButton();
            state.canvas?.requestRenderAll();

            if (typeof syncCropPanel === 'function') {
                syncCropPanel(drag.target);
            }
            if (typeof syncObjectFloatingToolbar === 'function') {
                syncObjectFloatingToolbar();
            }
        }

        function startImageCropPanMode(event = null) {
            const target = event?.target || state.canvas?.getActiveObject?.();
            if (!aaCanStartImageCropPan(target)) return false;

            if (state.cropPanTarget === target) {
                aaSyncImageCropPanUi();
                return true;
            }

            finishImageCropPanMode(true, state.cropPanDirty === true);
            aaStoreCropPanObjectState(target);
            state.cropPanTarget = target;
            state.cropPanDrag = null;
            state.cropPanDirty = false;
            state.cropPanStartCrop = {
                cropX: Math.max(0, Number(target.cropX) || 0),
                cropY: Math.max(0, Number(target.cropY) || 0),
            };
            aaCreateCropPanGhost(target);
            target.set({
                hasControls: false,
                lockMovementX: true,
                lockMovementY: true,
                hoverCursor: 'grab',
                moveCursor: 'grab',
            });
            target.setCoords();
            state.canvas.setActiveObject(target);
            clearFabricActiveTransform();
            state.canvas.requestRenderAll();
            syncCropPanel(target);
            syncContextToolbar(target);
            syncTextContextToolbar(target);
            aaSyncImageCropPanUi();
            setStatus('Geser foto untuk mengatur posisi di dalam crop');
            return true;
        }

        function startImageCropPanDrag(event = null) {
            const target = state.cropPanTarget;
            if (!target || !state.canvas) return false;

            if (event?.target && event.target !== target) {
                state.canvas.setActiveObject(target);
                aaSyncImageCropPanUi();
                clearFabricActiveTransform();
                event?.e?.preventDefault?.();
                event?.e?.stopPropagation?.();
                return true;
            }

            if (!aaCanStartImageCropPan(target)) {
                finishImageCropPanMode(true, false);
                return false;
            }

            const sourceEvent = event?.e || null;
            if (!sourceEvent) return false;
            const pointer = state.canvas.getPointer(sourceEvent, true);
            const localPoint = target.toLocalPoint(new fabric.Point(pointer.x, pointer.y), 'center', 'center');
            const natural = getImageNaturalSize(target);
            const naturalWidth = Math.max(1, Number(natural.width) || 1);
            const naturalHeight = Math.max(1, Number(natural.height) || 1);
            const cropWidth = Math.max(1, Math.min(Number(target.width) || naturalWidth, naturalWidth));
            const cropHeight = Math.max(1, Math.min(Number(target.height) || naturalHeight, naturalHeight));

            state.cropPanDrag = {
                target,
                cropX: Math.max(0, Number(target.cropX) || 0),
                cropY: Math.max(0, Number(target.cropY) || 0),
                startLocalX: localPoint.x,
                startLocalY: localPoint.y,
                maxCropX: Math.max(0, naturalWidth - cropWidth),
                maxCropY: Math.max(0, naturalHeight - cropHeight),
                pendingPointer: null,
                raf: null,
                changed: false,
            };

            target.set({
                hoverCursor: 'grabbing',
                moveCursor: 'grabbing',
            });
            clearFabricActiveTransform();
            sourceEvent.preventDefault?.();
            sourceEvent.stopPropagation?.();
            state.canvas.requestRenderAll();
            return true;
        }

        function moveImageCropPanDrag(event = null) {
            const drag = state.cropPanDrag;
            if (!drag || !state.canvas) return false;
            const sourceEvent = event?.e || null;
            if (!sourceEvent) return true;

            const pointer = state.canvas.getPointer(sourceEvent, true);
            drag.pendingPointer = {
                x: pointer.x,
                y: pointer.y,
            };

            if (!drag.raf) {
                drag.raf = window.requestAnimationFrame(aaApplyCropPanPendingMove);
            }

            sourceEvent.preventDefault?.();
            sourceEvent.stopPropagation?.();
            return true;
        }

        function finishImageCropPanDrag() {
            const drag = state.cropPanDrag;
            if (!drag) return false;

            if (drag.raf) {
                window.cancelAnimationFrame(drag.raf);
                drag.raf = null;
            }
            aaApplyCropPanPendingMove();

            const changed = Boolean(drag.changed);
            const target = drag.target;
            state.cropPanDrag = null;

            if (changed) {
                state.cropPanDirty = true;
                setStatus('Posisi foto diperbarui. Klik ✓ untuk selesai.');
            }

            if (target) {
                target.set({
                    hoverCursor: 'grab',
                    moveCursor: 'grab',
                });
                target.setCoords();
                state.canvas?.setActiveObject?.(target);
            }

            aaSyncImageCropPanUi();
            state.canvas?.requestRenderAll();
            return true;
        }

        function finishImageCropPanMode(restoreSelection = true, commit = false) {
            const target = state.cropPanTarget;
            const drag = state.cropPanDrag;
            if (!target && !drag) return;

            if (drag?.raf) {
                window.cancelAnimationFrame(drag.raf);
            }
            if (drag) {
                aaApplyCropPanPendingMove();
            }

            const shouldSnapshot = commit === true && state.cropPanDirty === true;
            state.cropPanDrag = null;
            state.cropPanTarget = null;
            state.cropPanDirty = false;
            state.cropPanStartCrop = null;
            aaRemoveCropPanFinishButton();
            aaRemoveCropPanGhost();
            aaRestoreCropPanObjectState();
            clearFabricActiveTransform();

            const canvasObjects = state.canvas?.getObjects?.() || [];
            if (target && restoreSelection && canvasObjects.includes(target)) {
                state.canvas.setActiveObject(target);
            }

            state.canvas?.requestRenderAll();
            syncCropPanel(restoreSelection ? target : state.canvas?.getActiveObject?.());
            syncContextToolbar(restoreSelection ? target : state.canvas?.getActiveObject?.());
            syncTextContextToolbar(restoreSelection ? target : state.canvas?.getActiveObject?.());

            if (shouldSnapshot) {
                snapshot();
            }
        }

        function moveObjectByLocalVector(target, localX, localY) {
            const angle = fabric.util.degreesToRadians(target.angle || 0);
            const scaleX = target.scaleX || 1;
            const scaleY = target.scaleY || 1;
            const canvasX = (localX * scaleX * Math.cos(angle)) - (localY * scaleY * Math.sin(angle));
            const canvasY = (localX * scaleX * Math.sin(angle)) + (localY * scaleY * Math.cos(angle));
            target.set({
                left: (target.left || 0) + canvasX,
                top: (target.top || 0) + canvasY,
            });
        }

        function canvasDeltaFromCropStart(start, localX, localY) {
            const angle = fabric.util.degreesToRadians(start.angle || 0);
            const scaleX = start.scaleX || 1;
            const scaleY = start.scaleY || 1;
            return {
                x: (localX * scaleX * Math.cos(angle)) - (localY * scaleY * Math.sin(angle)),
                y: (localX * scaleX * Math.sin(angle)) + (localY * scaleY * Math.cos(angle)),
            };
        }

        function getCropSideTransformStart(transform, target, side) {
            if (!transform || !target) return null;
            const current = transform.__aaCropSideStart;
            if (current && current.target === target && current.side === side) return current;

            const natural = getImageNaturalSize(target);
            const matrix = typeof target.calcTransformMatrix === 'function' ? target.calcTransformMatrix() : null;
            const invertedMatrix = matrix && fabric.util?.invertTransform ? fabric.util.invertTransform(matrix) : null;
            const start = {
                target,
                side,
                naturalWidth: Math.max(1, Number(natural.width) || 1),
                naturalHeight: Math.max(1, Number(natural.height) || 1),
                cropX: Math.max(0, Number(target.cropX) || 0),
                cropY: Math.max(0, Number(target.cropY) || 0),
                width: Math.max(1, Number(target.width) || Number(natural.width) || 1),
                height: Math.max(1, Number(target.height) || Number(natural.height) || 1),
                left: Number(target.left) || 0,
                top: Number(target.top) || 0,
                scaleX: Number(target.scaleX) || 1,
                scaleY: Number(target.scaleY) || 1,
                angle: Number(target.angle) || 0,
                invertedMatrix,
            };
            transform.__aaCropSideStart = start;
            return start;
        }

        function cropImageFromSideStart(target, side, x, y, start) {
            if (!target || !start || !start.invertedMatrix) return false;

            const localPoint = fabric.util.transformPoint(new fabric.Point(x, y), start.invertedMatrix);
            const minWidth = Math.max(12, start.naturalWidth * .03);
            const minHeight = Math.max(12, start.naturalHeight * .03);
            let nextCropX = start.cropX;
            let nextCropY = start.cropY;
            let nextWidth = start.width;
            let nextHeight = start.height;
            let shiftX = 0;
            let shiftY = 0;

            if (side === 'left') {
                const oldRight = start.cropX + start.width;
                const requestedLeft = start.cropX + localPoint.x + start.width / 2;
                nextCropX = Math.max(0, Math.min(requestedLeft, oldRight - minWidth));
                nextWidth = oldRight - nextCropX;
                shiftX = (nextCropX - start.cropX) / 2;
            }

            if (side === 'right') {
                const oldRight = start.cropX + start.width;
                const requestedRight = start.cropX + localPoint.x + start.width / 2;
                const nextRight = Math.min(start.naturalWidth, Math.max(requestedRight, start.cropX + minWidth));
                nextWidth = nextRight - start.cropX;
                shiftX = (nextRight - oldRight) / 2;
            }

            if (side === 'top') {
                const oldBottom = start.cropY + start.height;
                const requestedTop = start.cropY + localPoint.y + start.height / 2;
                nextCropY = Math.max(0, Math.min(requestedTop, oldBottom - minHeight));
                nextHeight = oldBottom - nextCropY;
                shiftY = (nextCropY - start.cropY) / 2;
            }

            if (side === 'bottom') {
                const oldBottom = start.cropY + start.height;
                const requestedBottom = start.cropY + localPoint.y + start.height / 2;
                const nextBottom = Math.min(start.naturalHeight, Math.max(requestedBottom, start.cropY + minHeight));
                nextHeight = nextBottom - start.cropY;
                shiftY = (nextBottom - oldBottom) / 2;
            }

            const delta = canvasDeltaFromCropStart(start, shiftX, shiftY);
            target.set({
                cropX: nextCropX,
                cropY: nextCropY,
                width: Math.max(1, nextWidth),
                height: Math.max(1, nextHeight),
                left: start.left + delta.x,
                top: start.top + delta.y,
                scaleX: start.scaleX,
                scaleY: start.scaleY,
                clipPath: null,
            });
            restoreImageFrameAfterCrop(target);
            target.setCoords();
            target.dirty = true;
            state.canvas?.requestRenderAll();
            return true;
        }

        function cropImageFromSide(target, side, x, y) {
            const natural = getImageNaturalSize(target);
            const width = Math.max(1, target.width || natural.width);
            const height = Math.max(1, target.height || natural.height);
            const cropX = Math.max(0, target.cropX || 0);
            const cropY = Math.max(0, target.cropY || 0);
            const pointer = new fabric.Point(x, y);
            const localPoint = target.toLocalPoint(pointer, 'center', 'center');
            const minWidth = Math.max(12, natural.width * .03);
            const minHeight = Math.max(12, natural.height * .03);
            let nextCropX = cropX;
            let nextCropY = cropY;
            let nextWidth = width;
            let nextHeight = height;
            let shiftX = 0;
            let shiftY = 0;

            if (side === 'left') {
                const oldLeft = cropX;
                const oldRight = cropX + width;
                const requestedLeft = cropX + localPoint.x + width / 2;
                nextCropX = Math.max(0, Math.min(requestedLeft, oldRight - minWidth));
                nextWidth = oldRight - nextCropX;
                shiftX = (nextCropX - oldLeft) / 2;
            }

            if (side === 'right') {
                const oldRight = cropX + width;
                const requestedRight = cropX + localPoint.x + width / 2;
                const nextRight = Math.min(natural.width, Math.max(requestedRight, cropX + minWidth));
                nextWidth = nextRight - cropX;
                shiftX = (nextRight - oldRight) / 2;
            }

            if (side === 'top') {
                const oldTop = cropY;
                const oldBottom = cropY + height;
                const requestedTop = cropY + localPoint.y + height / 2;
                nextCropY = Math.max(0, Math.min(requestedTop, oldBottom - minHeight));
                nextHeight = oldBottom - nextCropY;
                shiftY = (nextCropY - oldTop) / 2;
            }

            if (side === 'bottom') {
                const oldBottom = cropY + height;
                const requestedBottom = cropY + localPoint.y + height / 2;
                const nextBottom = Math.min(natural.height, Math.max(requestedBottom, cropY + minHeight));
                nextHeight = nextBottom - cropY;
                shiftY = (nextBottom - oldBottom) / 2;
            }

            target.set({
                cropX: nextCropX,
                cropY: nextCropY,
                width: Math.max(1, nextWidth),
                height: Math.max(1, nextHeight),
                clipPath: null,
            });
            restoreImageFrameAfterCrop(target);
            moveObjectByLocalVector(target, shiftX, shiftY);
            target.setCoords();
            target.dirty = true;
            state.canvas?.requestRenderAll();
            return true;
        }

        function cropObjectFromSide(target, side, x, y) {
            if (!target || target.customType === 'crop-helper') return false;
            if (state.isCropping || target.locked === true || target.customType === 'background') return false;

            if (target.type === 'image') {
                return cropImageFromSide(target, side, x, y);
            }

            return false;
        }

        function cropControlAction(side) {
            return function(eventData, transform, x, y) {
                const target = transform.target;
                if (!target || state.isCropping || target.locked === true || target.customType === 'background') {
                    return false;
                }
                setStatus('Crop diperbarui');
                const start = target.type === 'image' ? getCropSideTransformStart(transform, target, side) : null;
                const changed = start ?
                    cropImageFromSideStart(target, side, x, y, start) :
                    cropObjectFromSide(target, side, x, y);
                if (changed) syncCropUi();
                return changed;
            };
        }

        function cropControlPosition(side) {
            return function(dim, finalMatrix, target) {
                const cropBounds = getCurrentCropBounds(target);
                const x = side === 'left' ?
                    cropBounds.left :
                    side === 'right' ?
                    cropBounds.right :
                    (cropBounds.left + cropBounds.right) / 2;
                const y = side === 'top' ?
                    cropBounds.top :
                    side === 'bottom' ?
                    cropBounds.bottom :
                    (cropBounds.top + cropBounds.bottom) / 2;

                return fabric.util.transformPoint(new fabric.Point(x, y), finalMatrix);
            };
        }

        function renderCropControlPill(ctx, width, height, radius) {
            const x = -width / 2;
            const y = -height / 2;
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

        function aaIsOutsideSelectionTransformingCropTarget(fabricObject = null) {
            const drag = state?.objectTransformOverlayDrag || null;
            if (!drag || !drag.target) return false;
            return !fabricObject || drag.target === fabricObject;
        }

        function renderCropControl(name, width, height) {
            return function(ctx, left, top, styleOverride, fabricObject) {
                if (aaIsOutsideSelectionTransformingCropTarget(fabricObject)) return;
                const activeCorner = String(state?.canvas?._currentTransform?.corner || fabricObject?.__corner || '');
                if (activeCorner && activeCorner !== name) return;
                const isActive = activeCorner === name;
                const angle = Number(fabricObject?.angle) || 0;

                ctx.save();
                ctx.translate(left, top);
                ctx.rotate(fabric.util.degreesToRadians(angle));
                ctx.shadowColor = isActive ? 'rgba(124, 58, 237, .32)' : 'rgba(15, 23, 42, .22)';
                ctx.shadowBlur = isActive ? 18 : 14;
                ctx.shadowOffsetY = isActive ? 9 : 7;
                renderCropControlPill(ctx, width, height, Math.min(width, height) / 2);
                ctx.fillStyle = isActive ? '#f4f0ff' : '#ffffff';
                ctx.fill();
                ctx.shadowColor = 'transparent';
                ctx.shadowBlur = 0;
                ctx.shadowOffsetY = 0;
                ctx.lineWidth = isActive ? 3 : 2.5;
                ctx.strokeStyle = isActive ? '#6d28d9' : '#7c3aed';
                ctx.stroke();
                ctx.restore();
            };
        }

        function renderLegacyCropControl(ctx, left, top) {
            ctx.save();
            ctx.translate(left, top);
            ctx.fillStyle = '#146cb8';
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.fillRect(-7, -7, 14, 14);
            ctx.strokeRect(-7, -7, 14, 14);
            ctx.restore();
        }

        function installIntegratedCropControls() {
            if (fabric.Image.prototype.__aaCropControlsInstalled) return;
            const sideHandleLong = 34;
            const sideHandleShort = 11;

            const controls = {
                ...fabric.Image.prototype.controls
            };
            controls.ml = new fabric.Control({
                x: -0.5,
                y: 0,
                cursorStyle: 'ew-resize',
                actionHandler: cropControlAction('left'),
                actionName: 'crop-left',
                sizeX: sideHandleShort + 10,
                sizeY: sideHandleLong + 8,
                render: renderCropControl('ml', sideHandleShort, sideHandleLong),
            });
            controls.mr = new fabric.Control({
                x: 0.5,
                y: 0,
                cursorStyle: 'ew-resize',
                actionHandler: cropControlAction('right'),
                actionName: 'crop-right',
                sizeX: sideHandleShort + 10,
                sizeY: sideHandleLong + 8,
                render: renderCropControl('mr', sideHandleShort, sideHandleLong),
            });
            controls.mt = new fabric.Control({
                x: 0,
                y: -0.5,
                cursorStyle: 'ns-resize',
                actionHandler: cropControlAction('top'),
                actionName: 'crop-top',
                sizeX: sideHandleLong + 8,
                sizeY: sideHandleShort + 10,
                render: renderCropControl('mt', sideHandleLong, sideHandleShort),
            });
            controls.mb = new fabric.Control({
                x: 0,
                y: 0.5,
                cursorStyle: 'ns-resize',
                actionHandler: cropControlAction('bottom'),
                actionName: 'crop-bottom',
                sizeX: sideHandleLong + 8,
                sizeY: sideHandleShort + 10,
                render: renderCropControl('mb', sideHandleLong, sideHandleShort),
            });

            fabric.Image.prototype.controls = controls;
            fabric.Image.prototype.__aaCropControlsInstalled = true;
        }

        function getScreenMetrics() {
            if (!state.canvas?.upperCanvasEl) return null;
            const rect = state.canvas.upperCanvasEl.getBoundingClientRect();
            return {
                rect,
                scaleX: rect.width / Math.max(1, state.canvas.getWidth()),
                scaleY: rect.height / Math.max(1, state.canvas.getHeight()),
            };
        }

        function canvasRectToScreen(rect) {
            const metrics = getScreenMetrics();
            if (!metrics) return null;
            return {
                left: metrics.rect.left + rect.left * metrics.scaleX,
                top: metrics.rect.top + rect.top * metrics.scaleY,
                width: rect.width * metrics.scaleX,
                height: rect.height * metrics.scaleY,
            };
        }

        function screenRectToCanvas(rect) {
            const metrics = getScreenMetrics();
            if (!metrics) return null;
            return {
                left: (rect.left - metrics.rect.left) / metrics.scaleX,
                top: (rect.top - metrics.rect.top) / metrics.scaleY,
                width: rect.width / metrics.scaleX,
                height: rect.height / metrics.scaleY,
            };
        }

        function setCropDomRect(element, rect) {
            element.style.left = `${Math.round(rect.left)}px`;
            element.style.top = `${Math.round(rect.top)}px`;
            element.style.width = `${Math.round(rect.width)}px`;
            element.style.height = `${Math.round(rect.height)}px`;
        }

        function hideCropDomOverlay() {
            els.aaCropDomOverlay?.classList.remove('is-visible');
            state.cropDomDrag = null;
        }

        function hideCropFloatingToolbar() {
            els.aaCropFloatingToolbar?.classList.remove('is-visible');
        }

        function getCropDomElements() {
            const overlay = els.aaCropDomOverlay;
            return {
                overlay,
                target: overlay?.querySelector('.aa-crop-dom-target'),
                box: overlay?.querySelector('.aa-crop-dom-box'),
            };
        }

        function getCropDomRects() {
            if (!state.cropTarget || !state.cropBox) return null;
            const targetBounds = state.cropTarget.getBoundingRect(true, true);
            const cropBounds = state.cropBox.getBoundingRect(true, true);
            const target = canvasRectToScreen(targetBounds);
            const crop = canvasRectToScreen(cropBounds);
            const metrics = getScreenMetrics();
            if (!target || !crop || !metrics) return null;
            return {
                target,
                crop,
                canvas: metrics.rect,
            };
        }

        function shouldShowCropDomOverlay(rects) {
            if (!rects) return false;
            const target = rects.target;
            const canvas = rects.canvas;
            return target.left < canvas.left || target.top < canvas.top || target.left + target.width > canvas
                .right ||
                target.top + target.height > canvas.bottom;
        }

        function syncCropDomOverlay() {
            const parts = getCropDomElements();
            if (!parts.overlay || !parts.target || !parts.box || !state.isCropping) {
                hideCropDomOverlay();
                return;
            }
            if (state.cropTarget && aaIsRotatedCropTarget(state.cropTarget)) {
                hideCropDomOverlay();
                return;
            }
            const rects = getCropDomRects();
            if (!shouldShowCropDomOverlay(rects)) {
                hideCropDomOverlay();
                return;
            }
            parts.overlay.classList.add('is-visible');
            setCropDomRect(parts.target, rects.target);
            setCropDomRect(parts.box, rects.crop);
        }

        function applyCropDomRect(screenRect) {
            const canvasRect = screenRectToCanvas(screenRect);
            if (!canvasRect || !state.cropBox) return;

            aaSetCropBoxPlainRect(state.cropBox, canvasRect);
            state.canvas.setActiveObject(state.cropBox);
            state.canvas.requestRenderAll();
            syncCropPanel(state.cropBox);
            syncCropUi();
        }

        function clampCropDomRect(rect, target) {
            const minSize = 24;
            let left = Math.max(target.left, rect.left);
            let top = Math.max(target.top, rect.top);
            let right = Math.min(target.left + target.width, rect.left + rect.width);
            let bottom = Math.min(target.top + target.height, rect.top + rect.height);
            if (right - left < minSize) right = Math.min(target.left + target.width, left + minSize);
            if (bottom - top < minSize) bottom = Math.min(target.top + target.height, top + minSize);
            left = Math.max(target.left, Math.min(left, right - minSize));
            top = Math.max(target.top, Math.min(top, bottom - minSize));
            return {
                left,
                top,
                width: Math.max(minSize, right - left),
                height: Math.max(minSize, bottom - top),
            };
        }

        function updateCropDomDrag(event) {
            const drag = state.cropDomDrag;

            if (!drag) return;

            event.preventDefault();

            const dx = event.clientX - drag.startX;
            const dy = event.clientY - drag.startY;
            const minSize = 24;

            const targetLeft = drag.targetRect.left;
            const targetTop = drag.targetRect.top;
            const targetRight = drag.targetRect.left + drag.targetRect.width;
            const targetBottom = drag.targetRect.top + drag.targetRect.height;

            let next = {
                left: drag.startRect.left,
                top: drag.startRect.top,
                width: drag.startRect.width,
                height: drag.startRect.height
            };

            const handle = drag.handle;

            if (handle === 'move') {
                next.left = aaClampValue(
                    drag.startRect.left + dx,
                    targetLeft,
                    targetRight - drag.startRect.width
                );

                next.top = aaClampValue(
                    drag.startRect.top + dy,
                    targetTop,
                    targetBottom - drag.startRect.height
                );

                applyCropDomRect(next);
                return;
            }

            if (handle === 'w') {
                const fixedRight = drag.startRect.left + drag.startRect.width;
                next.left = aaClampValue(
                    drag.startRect.left + dx,
                    targetLeft,
                    fixedRight - minSize
                );
                next.width = fixedRight - next.left;
            }

            if (handle === 'e') {
                next.width = aaClampValue(
                    drag.startRect.width + dx,
                    minSize,
                    targetRight - drag.startRect.left
                );
            }

            if (handle === 'n') {
                const fixedBottom = drag.startRect.top + drag.startRect.height;
                next.top = aaClampValue(
                    drag.startRect.top + dy,
                    targetTop,
                    fixedBottom - minSize
                );
                next.height = fixedBottom - next.top;
            }

            if (handle === 's') {
                next.height = aaClampValue(
                    drag.startRect.height + dy,
                    minSize,
                    targetBottom - drag.startRect.top
                );
            }

            next = aaNormalizeRectInsideTarget(next, drag.targetRect);

            applyCropDomRect(next);
        }

        function stopCropDomDrag() {
            if (!state.cropDomDrag) return;
            state.cropDomDrag = null;
            window.removeEventListener('pointermove', updateCropDomDrag);
            window.removeEventListener('pointerup', stopCropDomDrag);
            syncCropUi();
            setStatus('Crop diperbarui');
        }


        function startCropDomDrag(event) {
            if (!state.isCropping || !state.cropBox || !state.cropTarget) return;

            const rects = getCropDomRects();

            if (!rects) return;

            event.preventDefault();
            event.stopPropagation();

            let handle = event.target.closest('[data-crop-handle]')?.dataset.cropHandle || 'move';

            // Hanya izinkan move, atas, kanan, bawah, kiri.
            // Corner handle tidak dipakai lagi.
            if (!['move', 'n', 'e', 's', 'w'].includes(handle)) {
                handle = 'move';
            }

            state.cropDomDrag = {
                handle,
                startX: event.clientX,
                startY: event.clientY,
                startRect: rects.crop,
                targetRect: rects.target,
            };

            window.addEventListener('pointermove', updateCropDomDrag);
            window.addEventListener('pointerup', stopCropDomDrag);
        }

        function lockCanvasObjectsForCrop() {
            if (!state.canvas || !state.cropBox) return;
            state.cropObjectStates = new Map();
            state.canvas.getObjects().forEach(object => {
                state.cropObjectStates.set(object, {
                    selectable: object.selectable,
                    evented: object.evented,
                });

                if (object === state.cropBox) {
                    object.set({
                        selectable: true,
                        evented: true,
                    });
                    return;
                }

                object.set({
                    selectable: false,
                    evented: false,
                });
            });
        }

        function restoreCanvasObjectsAfterCrop() {
            if (!state.canvas) return;
            const objectStates = state.cropObjectStates;
            state.cropObjectStates = null;
            if (!objectStates) {
                if (state.cropTarget) {
                    state.cropTarget.set({
                        selectable: true,
                        evented: true,
                    });
                }
                return;
            }

            const objects = new Set(state.canvas.getObjects());
            objectStates.forEach((values, object) => {
                if (!objects.has(object) || object === state.cropBox) return;
                object.set({
                    selectable: values.selectable,
                    evented: values.evented,
                });
                object.setCoords();
            });
        }

        function syncCropFloatingToolbar() {
            const toolbar = els.aaCropFloatingToolbar;
            if (!toolbar || !state.canvas || !state.isCropping || !state.cropBox || !state.cropTarget) {
                hideCropFloatingToolbar();
                return;
            }

            const rects = getCropDomRects();
            const cropRect = rects?.crop || canvasRectToScreen(state.cropBox.getBoundingRect(true, true));
            if (!cropRect) {
                hideCropFloatingToolbar();
                return;
            }

            toolbar.classList.add('is-visible');
            const width = toolbar.offsetWidth || 240;
            const height = toolbar.offsetHeight || 46;
            let left = cropRect.left + cropRect.width / 2 - width / 2;
            let top = cropRect.top - height - 10;
            if (top < 10) {
                top = cropRect.top + cropRect.height + 10;
            }

            left = Math.max(10, Math.min(window.innerWidth - width - 10, left));
            top = Math.max(10, Math.min(window.innerHeight - height - 10, top));
            toolbar.style.left = `${Math.round(left)}px`;
            toolbar.style.top = `${Math.round(top)}px`;
        }

        function syncCropUi() {
            syncCropDomOverlay();
            syncCropFloatingToolbar();
        }

        function resetActiveCropBox() {
            if (!state.isCropping || !state.cropTarget || !state.cropBox) {
                resetCrop();
                return;
            }

            const target = state.cropTarget;
            const natural = getImageNaturalSize(target);
            const center = typeof target.getCenterPoint === 'function' ? target.getCenterPoint() : new fabric.Point(
                Number(target.left) || 0,
                Number(target.top) || 0
            );

            target.set({
                cropX: 0,
                cropY: 0,
                width: Math.max(1, natural.width),
                height: Math.max(1, natural.height),
                clipPath: null,
            });
            if (typeof target.setPositionByOrigin === 'function') {
                target.setPositionByOrigin(center, 'center', 'center');
            } else {
                target.set({
                    left: center.x,
                    top: center.y,
                });
            }
            restoreImageFrameAfterCrop(target);
            target.setCoords();
            target.dirty = true;

            const isRotatedCrop = aaIsRotatedCropTarget(target);
            state.cropTarget.dirty = true;

            if (isRotatedCrop) {
                const visualSize = aaGetObjectVisualSize(target);
                const targetCenter = target.getCenterPoint();
                state.cropBox.set({
                    left: targetCenter.x,
                    top: targetCenter.y,
                    width: visualSize.width,
                    height: visualSize.height,
                    scaleX: 1,
                    scaleY: 1,
                    angle: Number(target.angle) || 0,
                    originX: 'center',
                    originY: 'center'
                });
                state.cropBox.setCoords();
            } else {
                const bounds = target.getBoundingRect(true, true);
                aaSetCropBoxPlainRect(state.cropBox, bounds);
            }

            state.canvas.setActiveObject(state.cropBox);
            state.canvas.requestRenderAll();
            syncCropPanel(state.cropBox);
            syncCropUi();
            setStatus('Crop direset');
        }

        function aaClampValue(value, min, max) {
            value = Number(value);

            if (!Number.isFinite(value)) {
                value = min;
            }

            return Math.max(min, Math.min(max, value));
        }

        function aaFiniteNumber(value, fallback = 0) {
            value = Number(value);
            return Number.isFinite(value) ? value : fallback;
        }

        function aaGetCropBoxPlainRect(cropBox) {
            const left = aaFiniteNumber(cropBox.left, 0);
            const top = aaFiniteNumber(cropBox.top, 0);
            const width = Math.max(1, Math.abs(aaFiniteNumber(cropBox.width, 1) * aaFiniteNumber(cropBox.scaleX,
                1)));
            const height = Math.max(1, Math.abs(aaFiniteNumber(cropBox.height, 1) * aaFiniteNumber(cropBox.scaleY,
                1)));

            return {
                left,
                top,
                width,
                height,
                right: left + width,
                bottom: top + height
            };
        }

        function aaSetCropBoxPlainRect(cropBox, rect) {
            if (!cropBox || !rect) return;
            cropBox.set({
                left: aaFiniteNumber(rect.left, 0),
                top: aaFiniteNumber(rect.top, 0),
                width: Math.max(1, aaFiniteNumber(rect.width, 1)),
                height: Math.max(1, aaFiniteNumber(rect.height, 1)),
                scaleX: 1,
                scaleY: 1,
                angle: 0,
                originX: 'left',
                originY: 'top'
            });
            cropBox.setCoords();
        }

        function aaNormalizeRectInsideTarget(rect, targetRect) {
            const minSize = 24;

            const targetLeft = aaFiniteNumber(targetRect.left, 0);
            const targetTop = aaFiniteNumber(targetRect.top, 0);
            const targetWidth = Math.max(minSize, aaFiniteNumber(targetRect.width, minSize));
            const targetHeight = Math.max(minSize, aaFiniteNumber(targetRect.height, minSize));
            const targetRight = targetLeft + targetWidth;
            const targetBottom = targetTop + targetHeight;

            let width = Math.max(minSize, aaFiniteNumber(rect.width, minSize));
            let height = Math.max(minSize, aaFiniteNumber(rect.height, minSize));

            width = Math.min(width, targetWidth);
            height = Math.min(height, targetHeight);

            let left = aaFiniteNumber(rect.left, targetLeft);
            let top = aaFiniteNumber(rect.top, targetTop);

            left = aaClampValue(left, targetLeft, targetRight - width);
            top = aaClampValue(top, targetTop, targetBottom - height);

            return {
                left,
                top,
                width,
                height
            };
        }

        function aaClampCropBoxInsideTarget(mode = 'full') {
            if (!state.isCropping || !state.cropBox || !state.cropTarget || !state.canvas) {
                return;
            }

            if (aaIsRotatedCropTarget(state.cropTarget)) {
                aaClampRotatedCropBoxInsideTarget();
                return;
            }

            if (state.__aaCropClampRunning) {
                return;
            }

            state.__aaCropClampRunning = true;

            try {
                const targetRect = state.cropTarget.getBoundingRect(true, true);

                // Jangan pakai getBoundingRect cropBox untuk ukuran,
                // karena getBoundingRect bisa ikut stroke/transform dan membuat ukuran naik terus.
                const cropRect = aaGetCropBoxPlainRect(state.cropBox);
                const next = aaNormalizeRectInsideTarget(cropRect, targetRect);

                // Saat cropBox sedang DIGESER, jangan sentuh width/height/scale.
                // Ini kunci agar penanda crop tidak balik membesar ke ukuran asli object.
                if (mode === 'move') {
                    state.cropBox.set({
                        left: next.left,
                        top: next.top,
                        angle: 0,
                        originX: 'left',
                        originY: 'top'
                    });
                    state.cropBox.setCoords();
                } else {
                    aaSetCropBoxPlainRect(state.cropBox, next);
                }

                if (typeof state.canvas.requestRenderAll === 'function') {
                    state.canvas.requestRenderAll();
                } else {
                    state.canvas.renderAll();
                }

                if (typeof syncCropPanel === 'function') {
                    syncCropPanel(state.cropBox);
                }

                if (typeof syncCropUi === 'function') {
                    syncCropUi();
                }
            } finally {
                state.__aaCropClampRunning = false;
            }
        }

        function installCropBoxBoundaryGuards() {
            if (!state.canvas || state.__aaCropBoundaryGuardsInstalled) {
                return;
            }

            state.__aaCropBoundaryGuardsInstalled = true;

            state.canvas.on('object:moving', function(event) {
                if (!state.isCropping || !state.cropBox) return;
                if (event.target !== state.cropBox) return;

                // Saat drag, kunci posisi saja.
                aaClampCropBoxInsideTarget('move');
            });

            state.canvas.on('object:scaling', function(event) {
                if (!state.isCropping || !state.cropBox) return;
                if (event.target !== state.cropBox) return;

                // Jangan clamp saat mouse masih resize.
                // Kalau dipaksa clamp di sini, Fabric masih menyimpan scale aktif
                // dan cropBox bisa melonjak kembali membesar.
                if (typeof syncCropUi === 'function') {
                    syncCropUi();
                }
            });

            state.canvas.on('object:modified', function(event) {
                if (!state.isCropping || !state.cropBox) return;
                if (event.target !== state.cropBox) return;

                // Baru normalisasi width/height setelah resize/drag selesai.
                aaClampCropBoxInsideTarget('full');
            });
        }

        function aaIsRotatedCropTarget(target) {
            return !!target && Math.abs(Number(target.angle) || 0) > 0.01;
        }

        function aaGetObjectVisualSize(object) {
            return {
                width: Math.max(1, Math.abs((Number(object.width) || 1) * (Number(object.scaleX) || 1))),
                height: Math.max(1, Math.abs((Number(object.height) || 1) * (Number(object.scaleY) || 1)))
            };
        }

        function aaGetCropBoxVisualRectForRotated(cropBox) {
            const width = Math.max(1, Math.abs((Number(cropBox.width) || 1) * (Number(cropBox.scaleX) || 1)));
            const height = Math.max(1, Math.abs((Number(cropBox.height) || 1) * (Number(cropBox.scaleY) || 1)));
            const center = cropBox.getCenterPoint();

            return {
                width,
                height,
                center
            };
        }

        function aaClampRotatedCropBoxInsideTarget() {
            if (!state.isCropping || !state.cropBox || !state.cropTarget || !state.canvas) {
                return;
            }

            const target = state.cropTarget;
            const cropBox = state.cropBox;

            if (!aaIsRotatedCropTarget(target)) {
                return;
            }

            if (state.__aaCropClampRunning) {
                return;
            }

            state.__aaCropClampRunning = true;

            try {
                const targetSize = aaGetObjectVisualSize(target);
                const cropSize = aaGetCropBoxVisualRectForRotated(cropBox);

                const targetCenter = target.getCenterPoint();
                const localCenter = target.toLocalPoint(cropSize.center, 'center', 'center');

                const maxHalfW = targetSize.width / 2;
                const maxHalfH = targetSize.height / 2;

                const nextWidth = Math.min(Math.max(24, cropSize.width), targetSize.width);
                const nextHeight = Math.min(Math.max(24, cropSize.height), targetSize.height);

                const safeLocalX = aaClampCropNumber(
                    localCenter.x,
                    -maxHalfW + nextWidth / 2,
                    maxHalfW - nextWidth / 2
                );

                const safeLocalY = aaClampCropNumber(
                    localCenter.y,
                    -maxHalfH + nextHeight / 2,
                    maxHalfH - nextHeight / 2
                );

                const nextCenter = fabric.util.transformPoint(
                    new fabric.Point(safeLocalX, safeLocalY),
                    target.calcTransformMatrix()
                );

                cropBox.set({
                    left: nextCenter.x,
                    top: nextCenter.y,
                    width: nextWidth,
                    height: nextHeight,
                    scaleX: 1,
                    scaleY: 1,
                    angle: Number(target.angle) || 0,
                    originX: 'center',
                    originY: 'center'
                });

                cropBox.setCoords();

                if (typeof state.canvas.requestRenderAll === 'function') {
                    state.canvas.requestRenderAll();
                } else {
                    state.canvas.renderAll();
                }

                if (typeof syncCropPanel === 'function') {
                    syncCropPanel(cropBox);
                }

                if (typeof syncCropUi === 'function') {
                    syncCropUi();
                }
            } finally {
                state.__aaCropClampRunning = false;
            }
        }

        function startCropMode() {
            const target = state.canvas.getActiveObject();
            if (!target || target.type !== 'image' || target.customType === 'crop-helper') {
                return;
            }

            if (typeof finishImageCropPanMode === 'function') {
                finishImageCropPanMode(false, false);
            }
            snapshot();
            finishCropMode(false);

            const isRotatedCrop = aaIsRotatedCropTarget(target);
            const bounds = target.getBoundingRect(true, true);
            const visualSize = aaGetObjectVisualSize(target);
            const targetCenter = target.getCenterPoint();

            const cropBox = new fabric.Rect({
                left: isRotatedCrop ? targetCenter.x : bounds.left,
                top: isRotatedCrop ? targetCenter.y : bounds.top,
                width: isRotatedCrop ? visualSize.width : bounds.width,
                height: isRotatedCrop ? visualSize.height : bounds.height,
                originX: isRotatedCrop ? 'center' : 'left',
                originY: isRotatedCrop ? 'center' : 'top',
                angle: isRotatedCrop ? (Number(target.angle) || 0) : 0,
                fill: 'rgba(20, 184, 166, 0.08)',
                stroke: '#2563eb',
                strokeWidth: 3,
                strokeDashArray: [10, 6],
                cornerColor: '#ffffff',
                cornerStrokeColor: '#0f766e',
                borderColor: '#2563eb',
                borderScaleFactor: 2.4,
                cornerSize: 13,
                cornerStyle: 'circle',
                transparentCorners: false,
                hasRotatingPoint: false,
                lockRotation: true,
                hasRotatingPoint: false,
                customType: 'crop-helper',
                excludeFromExport: true,
                name: 'Crop Area',
            });

            // cropBox.setControlsVisibility({
            //     mtr: false
            // });
            cropBox.set({
                lockRotation: true,
                hasRotatingPoint: false,
                lockScalingFlip: true,
                centeredScaling: false,
                centeredRotation: false,
                originX: 'left',
                originY: 'top'
            });

            cropBox.setControlsVisibility({
                tl: false,
                tr: false,
                bl: false,
                br: false,
                mtr: false,

                mt: true,
                mr: true,
                mb: true,
                ml: true
            });
            state.isCropping = true;
            state.cropTarget = target;
            state.cropBox = cropBox;
            state.canvas.add(cropBox);
            installCropBoxBoundaryGuards();
            aaClampCropBoxInsideTarget();
            lockCanvasObjectsForCrop();
            state.canvas.setActiveObject(cropBox);
            state.canvas.bringToFront(cropBox);
            state.canvas.requestRenderAll();
            syncCropPanel(cropBox);
            syncContextToolbar(cropBox);
            syncTextContextToolbar(cropBox);
            syncCropUi();
            setStatus('Crop aktif: geser/resize area crop');
        }

        function finishCropMode(restoreSelection = true) {
            if (!state.cropBox && !state.cropTarget) return;

            settleEditorPointerState();
            const target = state.cropTarget;
            const cropBox = state.cropBox;
            state.isCropping = false;

            if (cropBox) {
                state.canvas.remove(cropBox);
            }

            restoreCanvasObjectsAfterCrop();

            if (target) {
                if (restoreSelection) {
                    state.canvas.setActiveObject(target);
                }
            }

            state.cropBox = null;
            state.cropTarget = null;
            hideCropDomOverlay();
            hideCropFloatingToolbar();
            clearFabricActiveTransform();
            state.canvas.requestRenderAll();
            syncCropPanel(restoreSelection ? target : state.canvas.getActiveObject());
            syncContextToolbar(restoreSelection ? target : state.canvas.getActiveObject());
            syncTextContextToolbar(restoreSelection ? target : state.canvas.getActiveObject());
            if (restoreSelection) {
                setStatus('Crop selesai');
            }
        }

        // function applyCropFromBox(shouldSnapshot = false) {
        //     const target = state.cropTarget;
        //     const cropBox = state.cropBox;
        //     if (!target || !cropBox) return;

        //     const cropRect = cropBox.getBoundingRect(true, true);
        //     const cropCenter = new fabric.Point(cropRect.left + cropRect.width / 2, cropRect.top + cropRect.height /
        //         2);
        //     const localCenter = target.toLocalPoint(cropCenter, 'center', 'center');
        //     const scaleX = Math.abs(target.scaleX || 1);
        //     const scaleY = Math.abs(target.scaleY || 1);
        //     const localWidth = Math.max(1, cropRect.width / scaleX);
        //     const localHeight = Math.max(1, cropRect.height / scaleY);

        //     target.clipPath = new fabric.Rect({
        //         originX: 'center',
        //         originY: 'center',
        //         left: localCenter.x,
        //         top: localCenter.y,
        //         width: localWidth,
        //         height: localHeight,
        //         absolutePositioned: false,
        //     });
        //     target.dirty = true;
        //     state.canvas.requestRenderAll();
        //     syncCropUi();
        //     if (shouldSnapshot && !state.isCropping) {
        //         applyImageBorderRadius(target, target.borderRadius || 0);
        //         snapshot();
        //         setStatus('Crop diperbarui');
        //     }
        // }

        function aaClampCropNumber(value, min, max) {
            value = Number(value);

            if (!Number.isFinite(value)) {
                value = min;
            }

            return Math.max(min, Math.min(max, value));
        }

        function aaGetCropNaturalSizeSafe(image) {
            if (typeof getImageNaturalSize === 'function') {
                return getImageNaturalSize(image);
            }

            const el = image && (image._element || image.getElement?.());

            return {
                width: Math.max(1, Number(el?.naturalWidth || el?.width || image?.width || 1)),
                height: Math.max(1, Number(el?.naturalHeight || el?.height || image?.height || 1))
            };
        }

        function aaCalculateRealCropFromBox(target, cropBox) {
            if (!target || target.type !== 'image' || !cropBox) {
                return null;
            }

            function aaCalculateRotatedCropFromBox(target, cropBox) {
                if (!target || target.type !== 'image' || !cropBox) {
                    return null;
                }

                const natural = aaGetCropNaturalSizeSafe(target);

                const currentCropX = Math.max(0, Number(target.cropX) || 0);
                const currentCropY = Math.max(0, Number(target.cropY) || 0);
                const currentWidth = Math.max(1, Number(target.width) || natural.width || 1);
                const currentHeight = Math.max(1, Number(target.height) || natural.height || 1);

                const targetScaleX = Math.max(0.0001, Math.abs(Number(target.scaleX) || 1));
                const targetScaleY = Math.max(0.0001, Math.abs(Number(target.scaleY) || 1));

                const cropCenter = cropBox.getCenterPoint();
                const cropLocalCenter = target.toLocalPoint(cropCenter, 'center', 'center');

                const cropVisual = aaGetCropBoxVisualRectForRotated(cropBox);

                let localWidth = cropVisual.width / targetScaleX;
                let localHeight = cropVisual.height / targetScaleY;

                let localLeft = cropLocalCenter.x + currentWidth / 2 - localWidth / 2;
                let localTop = cropLocalCenter.y + currentHeight / 2 - localHeight / 2;

                localLeft = aaClampCropNumber(localLeft, 0, Math.max(0, currentWidth - 1));
                localTop = aaClampCropNumber(localTop, 0, Math.max(0, currentHeight - 1));

                localWidth = aaClampCropNumber(localWidth, 1, Math.max(1, currentWidth - localLeft));
                localHeight = aaClampCropNumber(localHeight, 1, Math.max(1, currentHeight - localTop));

                let nextCropX = currentCropX + localLeft;
                let nextCropY = currentCropY + localTop;
                let nextWidth = localWidth;
                let nextHeight = localHeight;

                nextCropX = aaClampCropNumber(nextCropX, 0, Math.max(0, natural.width - 1));
                nextCropY = aaClampCropNumber(nextCropY, 0, Math.max(0, natural.height - 1));
                nextWidth = aaClampCropNumber(nextWidth, 1, Math.max(1, natural.width - nextCropX));
                nextHeight = aaClampCropNumber(nextHeight, 1, Math.max(1, natural.height - nextCropY));

                return {
                    cropX: Math.round(nextCropX),
                    cropY: Math.round(nextCropY),
                    width: Math.round(nextWidth),
                    height: Math.round(nextHeight),
                    visualWidth: Math.max(1, cropVisual.width),
                    visualHeight: Math.max(1, cropVisual.height),
                    center: cropCenter
                };
            }

            if (aaIsRotatedCropTarget(target)) {
                return aaCalculateRotatedCropFromBox(target, cropBox);
            }

            const natural = aaGetCropNaturalSizeSafe(target);

            const currentCropX = Math.max(0, Number(target.cropX) || 0);
            const currentCropY = Math.max(0, Number(target.cropY) || 0);
            const currentWidth = Math.max(1, Number(target.width) || natural.width || 1);
            const currentHeight = Math.max(1, Number(target.height) || natural.height || 1);

            const targetRect = target.getBoundingRect(true, true);

            const cropRect = typeof aaGetCropBoxPlainRect === 'function' ?
                aaGetCropBoxPlainRect(cropBox) :
                cropBox.getBoundingRect(true, true);

            const targetLeft = targetRect.left;
            const targetTop = targetRect.top;
            const targetRight = targetRect.left + targetRect.width;
            const targetBottom = targetRect.top + targetRect.height;

            let boxLeft = cropRect.left;
            let boxTop = cropRect.top;
            let boxRight = cropRect.left + cropRect.width;
            let boxBottom = cropRect.top + cropRect.height;

            boxLeft = Math.max(targetLeft, Math.min(boxLeft, targetRight - 1));
            boxTop = Math.max(targetTop, Math.min(boxTop, targetBottom - 1));
            boxRight = Math.max(boxLeft + 1, Math.min(boxRight, targetRight));
            boxBottom = Math.max(boxTop + 1, Math.min(boxBottom, targetBottom));

            const visualWidth = Math.max(1, boxRight - boxLeft);
            const visualHeight = Math.max(1, boxBottom - boxTop);

            const ratioX = currentWidth / Math.max(1, targetRect.width);
            const ratioY = currentHeight / Math.max(1, targetRect.height);

            let localLeft = (boxLeft - targetLeft) * ratioX;
            let localTop = (boxTop - targetTop) * ratioY;
            let localWidth = visualWidth * ratioX;
            let localHeight = visualHeight * ratioY;

            localLeft = aaClampCropNumber(localLeft, 0, Math.max(0, currentWidth - 1));
            localTop = aaClampCropNumber(localTop, 0, Math.max(0, currentHeight - 1));
            localWidth = aaClampCropNumber(localWidth, 1, Math.max(1, currentWidth - localLeft));
            localHeight = aaClampCropNumber(localHeight, 1, Math.max(1, currentHeight - localTop));

            let nextCropX = currentCropX + localLeft;
            let nextCropY = currentCropY + localTop;
            let nextWidth = localWidth;
            let nextHeight = localHeight;

            nextCropX = aaClampCropNumber(nextCropX, 0, Math.max(0, natural.width - 1));
            nextCropY = aaClampCropNumber(nextCropY, 0, Math.max(0, natural.height - 1));
            nextWidth = aaClampCropNumber(nextWidth, 1, Math.max(1, natural.width - nextCropX));
            nextHeight = aaClampCropNumber(nextHeight, 1, Math.max(1, natural.height - nextCropY));

            return {
                cropX: Math.round(nextCropX),
                cropY: Math.round(nextCropY),
                width: Math.round(nextWidth),
                height: Math.round(nextHeight),
                visualWidth,
                visualHeight,
                center: new fabric.Point(
                    boxLeft + visualWidth / 2,
                    boxTop + visualHeight / 2
                )
            };
        }

        function applyCropFromBox(shouldSnapshot = false) {
            const target = state.cropTarget;
            const cropBox = state.cropBox;

            if (!target || target.type !== 'image' || !cropBox) {
                return false;
            }

            if (state.isCropping) {
                aaClampCropBoxInsideTarget('full');
            }

            const crop = aaCalculateRealCropFromBox(target, cropBox);

            if (!crop) {
                return false;
            }

            const signX = (Number(target.scaleX) || 1) < 0 ? -1 : 1;
            const signY = (Number(target.scaleY) || 1) < 0 ? -1 : 1;

            const nextWidth = Math.max(1, crop.width);
            const nextHeight = Math.max(1, crop.height);

            const nextScaleX = signX * (Math.max(1, crop.visualWidth || crop.width) / nextWidth);
            const nextScaleY = signY * (Math.max(1, crop.visualHeight || crop.height) / nextHeight);

            target.set({
                cropX: crop.cropX,
                cropY: crop.cropY,
                width: nextWidth,
                height: nextHeight,
                scaleX: nextScaleX,
                scaleY: nextScaleY,
                clipPath: null,
                originX: 'center',
                originY: 'center'
            });

            if (typeof target.setPositionByOrigin === 'function') {
                target.setPositionByOrigin(crop.center, 'center', 'center');
            } else {
                target.set({
                    left: crop.center.x,
                    top: crop.center.y
                });
            }

            restoreImageFrameAfterCrop(target);

            target.dirty = true;

            if (typeof target.setCoords === 'function') {
                target.setCoords();
            }

            state.canvas.setActiveObject(target);

            if (typeof state.canvas.requestRenderAll === 'function') {
                state.canvas.requestRenderAll();
            } else {
                state.canvas.renderAll();
            }

            if (typeof syncCropPanel === 'function') {
                syncCropPanel(target);
            }

            if (typeof syncCropUi === 'function') {
                syncCropUi();
            }

            if (shouldSnapshot && !state.isCropping) {
                snapshot();
                setStatus('Crop diperbarui');
            }

            return true;
        }

        function applyCropBoxAndFinish() {
            if (!state.isCropping || !state.cropBox || !state.cropTarget) {
                return;
            }

            const target = state.cropTarget;
            const applied = applyCropFromBox(false);

            finishCropMode(true);

            if (target) {
                target.clipPath = null;

                restoreImageFrameAfterCrop(target);

                target.dirty = true;

                if (typeof target.setCoords === 'function') {
                    target.setCoords();
                }

                state.canvas.setActiveObject(target);

                if (typeof state.canvas.requestRenderAll === 'function') {
                    state.canvas.requestRenderAll();
                } else {
                    state.canvas.renderAll();
                }

                if (typeof syncCropPanel === 'function') {
                    syncCropPanel(target);
                }

                if (typeof syncContextToolbar === 'function') {
                    syncContextToolbar(target);
                }

                if (typeof syncTextContextToolbar === 'function') {
                    syncTextContextToolbar(target);
                }
            }

            if (applied) {
                if (typeof storeCurrentPage === 'function') {
                    storeCurrentPage();
                }

                snapshot();
                setStatus('Crop diperbarui');
            } else {
                setStatus('Crop tidak berubah', 'error');
            }
        }

        function cancelCropBoxAndFinish() {
            if (!state.isCropping) return;
            finishCropMode(true);
            setStatus('Crop dibatalkan');
        }

        function resetCrop() {
            const active = state.canvas.getActiveObject();
            const target = active === state.cropBox ? state.cropTarget : active;
            if (!target || target.customType === 'crop-helper') return;
            target.clipPath = null;
            restoreImageFrameAfterCrop(target);
            target.dirty = true;
            state.canvas.requestRenderAll();
            snapshot();
            setStatus('Crop direset');
        }

        function drawSelectionCropGuide() {
            const canvas = state.canvas;
            if (!canvas) return;

            const active = canvas.getActiveObject();
            if (!active) return;
            if (active.customType === 'crop-helper') return;

            const context = canvas.getSelectionContext();
            if (!context) return;

            const cropBounds = getCurrentCropBounds(active);
            const matrix = active.calcTransformMatrix();
            const topLeft = fabric.util.transformPoint(new fabric.Point(cropBounds.left, cropBounds.top), matrix);
            const topRight = fabric.util.transformPoint(new fabric.Point(cropBounds.right, cropBounds.top), matrix);
            const bottomRight = fabric.util.transformPoint(new fabric.Point(cropBounds.right, cropBounds.bottom),
                matrix);
            const bottomLeft = fabric.util.transformPoint(new fabric.Point(cropBounds.left, cropBounds.bottom),
                matrix);
            const cornerLength = Math.max(18, Math.min(44, Math.max(active.getScaledWidth(), active
                .getScaledHeight()) * .12));

            const drawEdgeTick = (start, end, length) => {
                const dx = end.x - start.x;
                const dy = end.y - start.y;
                const size = Math.hypot(dx, dy) || 1;
                const ratio = Math.min(length, size * .34) / size;
                context.moveTo(start.x, start.y);
                context.lineTo(start.x + dx * ratio, start.y + dy * ratio);
            };

            context.save();
            context.lineWidth = 2;
            context.strokeStyle = '#14b8a500';
            context.setLineDash([8, 5]);
            context.beginPath();
            context.moveTo(topLeft.x, topLeft.y);
            context.lineTo(topRight.x, topRight.y);
            context.lineTo(bottomRight.x, bottomRight.y);
            context.lineTo(bottomLeft.x, bottomLeft.y);
            context.closePath();
            context.stroke();

            context.setLineDash([]);
            context.lineWidth = 4;
            context.strokeStyle = '#ffffff00';
            context.beginPath();
            drawEdgeTick(topLeft, topRight, cornerLength);
            drawEdgeTick(topLeft, bottomLeft, cornerLength);
            drawEdgeTick(topRight, topLeft, cornerLength);
            drawEdgeTick(topRight, bottomRight, cornerLength);
            drawEdgeTick(bottomRight, topRight, cornerLength);
            drawEdgeTick(bottomRight, bottomLeft, cornerLength);
            drawEdgeTick(bottomLeft, bottomRight, cornerLength);
            drawEdgeTick(bottomLeft, topLeft, cornerLength);
            context.stroke();

            context.lineWidth = 2;
            context.strokeStyle = '#0f766d00';
            context.beginPath();
            drawEdgeTick(topLeft, topRight, cornerLength);
            drawEdgeTick(topLeft, bottomLeft, cornerLength);
            drawEdgeTick(topRight, topLeft, cornerLength);
            drawEdgeTick(topRight, bottomRight, cornerLength);
            drawEdgeTick(bottomRight, topRight, cornerLength);
            drawEdgeTick(bottomRight, bottomLeft, cornerLength);
            drawEdgeTick(bottomLeft, bottomRight, cornerLength);
            drawEdgeTick(bottomLeft, topLeft, cornerLength);
            context.stroke();

            context.restore();
        }
