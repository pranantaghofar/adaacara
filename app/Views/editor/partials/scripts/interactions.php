        function isLinkInteractionObject(object) {
            if (!object || object === state.cropBox || state.isCropping) return false;
            return object.customType === 'link-text' || (Boolean(object.link) && object.customType !== 'copy-text' &&
                object.customType !== 'social-link');
        }

        function isSocialLinkInteractionObject(object) {
            return Boolean(object && object !== state.cropBox && !state.isCropping && object.customType ===
                'social-link');
        }

        function isCopyInteractionObject(object) {
            if (!object || object === state.cropBox || state.isCropping) return false;
            return object.customType === 'copy-text' || (Boolean(object.copyText) && !object.link && object
                .customType !==
                'link-text');
        }

        function isMusicInteractionObject(object) {
            return Boolean(object && object !== state.cropBox && !state.isCropping && object.customType ===
                'music-player');
        }

        function isYoutubeInteractionObject(object) {
            return Boolean(object && object !== state.cropBox && !state.isCropping && object.customType ===
                'youtube-video');
        }

        function isCountdownInteractionObject(object) {
            return Boolean(object && object !== state.cropBox && !state.isCropping && object.customType ===
                'countdown-timer');
        }

        function isOpeningButtonInteractionObject(object) {
            return Boolean(object && object !== state.cropBox && !state.isCropping && object.customType ===
                'opening-button');
        }

        function isGuestFieldInteractionObject(object) {
            return Boolean(object && object !== state.cropBox && !state.isCropping && isGuestbookObject(object));
        }

	        function isGalleryInteractionObject(object) {
	            return Boolean(object && object !== state.cropBox && !state.isCropping && object.customType === 'photo-gallery');
	        }

		        function isGalleryPhotoInteractionObject(object) {
		            return Boolean(object && object !== state.cropBox && !state.isCropping && (
		                object.customType === 'gallery-photo' ||
		                object.isGalleryPhoto === true ||
		                object.galleryZoom === true
		            ));
		        }

		        function isMobileImageInteractionObject(object) {
		            return Boolean(object && object !== state.cropBox && !state.isCropping &&
		                object.type === 'image' &&
		                object.customType !== 'background' &&
		                !isGalleryPhotoInteractionObject(object) &&
		                !isFramePhotoInteractionObject(object)
		            );
		        }

		        function isFramePhotoInteractionObject(object) {
		            if (!object || object === state.cropBox || state.isCropping) return false;
		            if (typeof isFramePlaceholderObject === 'function' && isFramePlaceholderObject(object)) return true;
		            return Boolean(
		                object.type === 'image' &&
		                object.customType !== 'background' &&
		                object.aaImageFrameShape &&
		                object.aaImageFrameShape !== 'none'
		            );
		        }

        function hideInteractionPopover(options = {}) {
            if (options.preserveMobileDrawer === true && (
                isInteractionPopoverInMobileDrawer() ||
                (isMobileEditorInteractionDrawer() && els.aaMobileInteractionDrawer && !els.aaMobileInteractionDrawer.hidden)
            )) {
                return;
            }
            const keepFocusedMobileDrawer = options.forceMobileDrawer !== true &&
                typeof isMobileInteractionDrawerFocused === 'function' &&
                isMobileInteractionDrawerFocused() &&
                typeof getMobileInteractionStoredTarget === 'function' &&
                getMobileInteractionStoredTarget();
            const keepPendingMobileDrawer = options.forceMobileDrawer !== true &&
                isMobileEditorInteractionDrawer() &&
                state.__aaMobileInteractionDrawerFrame &&
                typeof getMobileInteractionStoredTarget === 'function' &&
                getMobileInteractionStoredTarget();

            if (isMobileEditorInteractionDrawer() && typeof syncMobileInteractionDrawer === 'function' && !keepFocusedMobileDrawer && !keepPendingMobileDrawer) {
                syncMobileInteractionDrawer(null);
                document.body.classList.remove('aa-mobile-interaction-drawer-open');
                if (typeof restoreMobileInteractionDrawerHome === 'function') {
                    restoreMobileInteractionDrawerHome();
                }
            }
            els.aaInteractionPopover?.classList.remove('is-visible');
            els.aaLinkPopoverSection?.classList.remove('is-active');
            els.aaSocialPopoverSection?.classList.remove('is-active');
            els.aaCopyPopoverSection?.classList.remove('is-active');
            els.aaMusicPopoverSection?.classList.remove('is-active');
            els.aaYoutubePopoverSection?.classList.remove('is-active');
            els.aaOpeningButtonPopoverSection?.classList.remove('is-active');
            els.aaGuestFieldPopoverSection?.classList.remove('is-active');
        }

        function isMobileEditorInteractionDrawer() {
            return window.matchMedia('(max-width: 767px)').matches;
        }

        function isInteractionPopoverInMobileDrawer() {
            return Boolean(
                isMobileEditorInteractionDrawer() &&
                els.aaInteractionPopover &&
                els.aaElementDrawerMount &&
                els.aaInteractionPopover.parentNode === els.aaElementDrawerMount &&
                document.body.classList.contains('aa-mobile-interaction-drawer-open')
            );
        }

        function isElementInteractionDrawerOpen() {
            return Boolean(
                document.querySelector('.aa-leftbar')?.classList.contains('is-drawer-open') &&
                getActiveLeftDrawerPanelKey?.() === 'element-interaction'
            );
        }

        function noteMobileInteractionDrawerClosed() {
            if (!isMobileEditorInteractionDrawer() || getActiveLeftDrawerPanelKey?.() !== 'element-interaction') return;
            state.__aaMobileInteractionDrawerDismissedTarget = state.canvas?.getActiveObject?.() ||
                state.__aaMobileInteractionDrawerTarget ||
                null;
        }

        function isMobileInteractionDrawerCandidate(active) {
            return Boolean(
                isLinkInteractionObject(active) ||
                isSocialLinkInteractionObject(active) ||
                isCopyInteractionObject(active) ||
	                isYoutubeInteractionObject(active) ||
	                isOpeningButtonInteractionObject(active) ||
		                isGuestFieldInteractionObject(active) ||
		                isGalleryInteractionObject(active) ||
		                isGalleryPhotoInteractionObject(active) ||
		                isFramePhotoInteractionObject(active) ||
		                isMobileImageInteractionObject(active)
		            );
		        }

        function isMobileInteractionDrawerFocused() {
            return Boolean(
                isMobileEditorInteractionDrawer() &&
                els.aaMobileInteractionDrawer &&
                els.aaMobileInteractionDrawer.contains(document.activeElement)
            );
        }

        function getMobileInteractionStoredTarget(predicate = isMobileInteractionDrawerCandidate) {
            const target = state.__aaMobileInteractionDrawerTarget || null;
            if (!target || typeof predicate !== 'function' || !predicate(target)) return null;
            if (state.canvas?.getObjects && !state.canvas.getObjects().includes(target)) return null;
            return target;
        }

        function getInteractionApplyTarget(predicate = isMobileInteractionDrawerCandidate) {
            const active = state.canvas?.getActiveObject?.() || null;
            if (active && predicate(active)) return active;
            if (!isMobileEditorInteractionDrawer()) return null;

            const stored = getMobileInteractionStoredTarget(predicate);
            if (!stored) return null;

            if (state.canvas && state.canvas.getActiveObject?.() !== stored) {
                state.canvas.setActiveObject(stored);
                state.canvas.requestRenderAll?.();
            }

            return stored;
        }

        function scheduleMobileInteractionDrawerSync(target = null) {
            if (!isMobileEditorInteractionDrawer()) return false;
            const candidate = target || state.canvas?.getActiveObject?.() || null;
            if (candidate && !isMobileInteractionDrawerCandidate(candidate)) return false;
            if (candidate) state.__aaMobileInteractionDrawerTarget = candidate;
            window.cancelAnimationFrame(state.__aaMobileInteractionDrawerFrame || 0);
            state.__aaMobileInteractionDrawerFrame = requestAnimationFrame(() => {
                state.__aaMobileInteractionDrawerFrame = 0;
                const active = state.canvas?.getActiveObject?.() || candidate || null;
                if (!isMobileInteractionDrawerCandidate(active)) return;
                state.__aaMobileInteractionDrawerDismissedTarget = null;
                syncInteractionPopover(active);
            });
            return true;
        }

        function clearMobileInteractionDrawerReturn() {
            state.__aaMobileInteractionDrawerReturn = null;
        }

        function rememberMobileInteractionDrawerReturn(nextPanel) {
            if (!isMobileEditorInteractionDrawer() || !['font', 'color'].includes(nextPanel)) return;
            const active = state.canvas?.getActiveObject?.();
            if (!isMobileInteractionDrawerCandidate(active)) return;
            state.__aaMobileInteractionDrawerReturn = {
                nextPanel,
                sourcePanel: getActiveLeftDrawerPanelKey?.() || '',
                target: active,
            };
        }

        function returnToMobileInteractionDrawer(fromPanel = '') {
            const remembered = state.__aaMobileInteractionDrawerReturn;
            if (!isMobileEditorInteractionDrawer() || !remembered) return false;
            if (fromPanel && remembered.nextPanel && remembered.nextPanel !== fromPanel) return false;
            const active = state.canvas?.getActiveObject?.();
            clearMobileInteractionDrawerReturn();
            if (!active || active !== remembered.target || !isMobileInteractionDrawerCandidate(active)) return false;
            state.__aaMobileInteractionDrawerDismissedTarget = null;
            syncInteractionPopover(active);
            return true;
        }

        function mobileInteractionDrawerMeta(active) {
            if (isLinkInteractionObject(active)) return { title: 'Link Text', status: 'Atur link tujuan element ini.' };
            if (isSocialLinkInteractionObject(active)) return { title: 'Social Media', status: 'Atur icon, label, dan link social media.' };
            if (isCopyInteractionObject(active)) return { title: 'Copy Text', status: 'Atur teks yang akan dicopy dan pesan feedback.' };
            if (isYoutubeInteractionObject(active)) return { title: 'Youtube Video', status: 'Atur link dan tampilan tombol Youtube.' };
	            if (isOpeningButtonInteractionObject(active)) return { title: 'Button Opening', status: 'Atur style tombol opening.' };
	            if (isGuestFieldInteractionObject(active)) return { title: 'Guestbook Field', status: 'Atur field guestbook yang dipilih.' };
	            if (isGalleryInteractionObject(active)) return { title: 'Gallery', status: 'Upload, pilih, urutkan, dan atur foto gallery.' };
	            if (isGalleryPhotoInteractionObject(active)) return { title: 'Gallery Photo', status: 'Ganti foto dan atur zoom publish.' };
	            if (isFramePhotoInteractionObject(active)) return { title: 'Frame Photo', status: 'Isi foto, pilih media, atau ubah bentuk frame.' };
	            if (isMobileImageInteractionObject(active)) return { title: 'Gambar', status: 'Ganti gambar dan atur radius.' };
	            return { title: 'Element Settings', status: 'Pilih element interaktif di canvas untuk mengatur detailnya.' };
	        }

        function setMobileInteractionInputValue(input, value) {
            if (!input || document.activeElement === input) return;
            input.value = value;
        }

        function setMobileInteractionSection(section, active) {
            section?.classList.toggle('is-active', Boolean(active));
        }

        function syncMobileInteractionDrawer(active = state.canvas?.getActiveObject()) {
            if (!els.aaMobileInteractionDrawer) return;
            const isLink = isLinkInteractionObject(active);
            const isSocialLink = isSocialLinkInteractionObject(active);
            const isCopy = isCopyInteractionObject(active);
            const isYoutube = isYoutubeInteractionObject(active);
	            const isOpeningButton = isOpeningButtonInteractionObject(active);
		            const isGuestField = isGuestFieldInteractionObject(active);
		            const isGallery = isGalleryInteractionObject(active);
		            const isGalleryPhoto = isGalleryPhotoInteractionObject(active);
		            const isFramePhoto = isFramePhotoInteractionObject(active);
		            const isMobileImage = isMobileImageInteractionObject(active);
		            const hasTarget = isLink || isSocialLink || isCopy || isYoutube || isOpeningButton || isGuestField || isGallery || isGalleryPhoto || isFramePhoto || isMobileImage;

            els.aaMobileInteractionDrawer.hidden = !hasTarget;
            setMobileInteractionSection(els.aaMobileLinkSection, isLink);
            setMobileInteractionSection(els.aaMobileSocialSection, isSocialLink);
            setMobileInteractionSection(els.aaMobileCopySection, isCopy);
            setMobileInteractionSection(els.aaMobileYoutubeSection, isYoutube);
	            setMobileInteractionSection(els.aaMobileOpeningButtonSection, isOpeningButton);
		            setMobileInteractionSection(els.aaMobileGuestFieldSection, isGuestField);
		            setMobileInteractionSection(els.aaMobileGallerySection, isGallery);
		            setMobileInteractionSection(els.aaMobileGalleryPhotoSection, isGalleryPhoto);
		            setMobileInteractionSection(els.aaMobileFrameSection, isFramePhoto);
		            setMobileInteractionSection(els.aaMobileImageSection, isMobileImage);
            if (!hasTarget) return;

            if (isLink) {
                setMobileInteractionInputValue(els.aaMobileLinkUrlInput, active.link || '');
            }
            if (isSocialLink) {
                setMobileInteractionInputValue(els.aaMobileSocialPlatformInput, active.socialPlatform || 'instagram');
                setMobileInteractionInputValue(els.aaMobileSocialLabelInput, active.socialLabel || 'Instagram');
                setMobileInteractionInputValue(els.aaMobileSocialUrlInput, active.link || '');
            }
            if (isCopy) {
                setMobileInteractionInputValue(els.aaMobileCopyTextInput, active.copyText || '');
                setMobileInteractionInputValue(els.aaMobileCopyFeedbackInput, active.copyFeedback || '');
            }
            if (isYoutube) {
                const youtubeBox = getInteractiveBox(active);
                const youtubeBg = active.controlBackground || youtubeBox?.fill || '#111827';
                const youtubeRadius = Math.max(0, Math.round(active.controlRadius ?? youtubeBox?.rx ?? 18));
                setMobileInteractionInputValue(els.aaMobileYoutubeUrlInput, active.youtubeUrl || '');
                setMobileInteractionInputValue(els.aaMobileYoutubeBgInput, normalizeColor(youtubeBg));
                setMobileInteractionInputValue(els.aaMobileYoutubeRadiusInput, youtubeRadius);
                if (els.aaMobileYoutubeRadiusValue) els.aaMobileYoutubeRadiusValue.textContent = youtubeRadius;
                if (els.aaMobileYoutubeAutoplayInput) els.aaMobileYoutubeAutoplayInput.checked = active.youtubeAutoplayOnView !== false;
                if (els.aaMobileYoutubeLoopInput) els.aaMobileYoutubeLoopInput.checked = active.youtubeLoop !== false;
            }
            if (isOpeningButton) {
                const parts = getOpeningButtonParts(active);
                const openingBg = normalizeColor(active.controlBackground || parts.box?.fill || '#0f766e');
                const openingTextColor = normalizeColor(active.controlTextColor || parts.text?.fill || '#ffffff');
                const openingRadius = Math.max(0, Math.round(active.controlRadius ?? parts.box?.rx ?? 48));
                const openingPadding = Math.max(6, Math.round(active.openingButtonPadding ?? active.openingButtonPaddingY ?? 28));
                setMobileInteractionInputValue(els.aaMobileOpeningButtonBgInput, openingBg);
                setMobileInteractionInputValue(els.aaMobileOpeningButtonTextColorInput, openingTextColor);
                setMobileInteractionInputValue(els.aaMobileOpeningButtonFontInput, parts.text?.fontFamily || active.openingButtonFontFamily || 'Inter');
                setMobileInteractionInputValue(els.aaMobileOpeningButtonRadiusInput, openingRadius);
                if (els.aaMobileOpeningButtonRadiusValue) els.aaMobileOpeningButtonRadiusValue.textContent = openingRadius;
                setMobileInteractionInputValue(els.aaMobileOpeningButtonPaddingYInput, openingPadding);
                if (els.aaMobileOpeningButtonPaddingYValue) els.aaMobileOpeningButtonPaddingYValue.textContent = openingPadding;
            }
            if (isGuestField) {
                const parts = getGuestbookObjectParts(active);
                setMobileInteractionInputValue(els.aaMobileGuestFieldTextInput, active.customType === 'guest-submit-button' ?
                    (active.buttonText || parts.text?.text || active.placeholder || '') :
                    (active.placeholder || parts.text?.text || active.label || ''));
                setMobileInteractionInputValue(els.aaMobileGuestFieldBgInput, normalizeColor(parts.box?.fill || '#ffffff'));
                setMobileInteractionInputValue(els.aaMobileGuestFieldFontInput, parts.text?.fontFamily || 'Inter');
                setMobileInteractionInputValue(els.aaMobileGuestFieldSizeInput, Math.round(parts.text?.fontSize || 36));
                setMobileInteractionInputValue(els.aaMobileGuestFieldColorInput, normalizeColor(parts.text?.fill || '#334155'));
                const guestRadius = Math.max(0, Math.round(active.borderRadius ?? parts.box?.rx ?? 18));
                setMobileInteractionInputValue(els.aaMobileGuestFieldRadiusInput, guestRadius);
                if (els.aaMobileGuestFieldRadiusValue) els.aaMobileGuestFieldRadiusValue.textContent = guestRadius;
                const canRequire = ['guest-name-input', 'guest-attendance-select', 'guest-message-textarea'].includes(active.customType);
                if (els.aaMobileGuestFieldRequiredWrap) els.aaMobileGuestFieldRequiredWrap.hidden = !canRequire;
                if (els.aaMobileGuestFieldRequiredInput) els.aaMobileGuestFieldRequiredInput.checked = active.required === true;
                const hasMax = ['guest-name-input', 'guest-message-textarea', 'guest-comment-list'].includes(active.customType);
                if (els.aaMobileGuestFieldMaxWrap) els.aaMobileGuestFieldMaxWrap.hidden = !hasMax;
                setMobileInteractionInputValue(els.aaMobileGuestFieldMaxInput, Math.max(0, Number(active.maxLength) || 0));
            }
	            if (isGallery) {
	                const columns = Math.max(1, Math.min(6, Number(active.galleryColumns) || 2));
	                const gap = Math.max(0, Number(active.galleryGap) || 14);
	                const radius = Math.max(0, Number(active.galleryRadius) || 18);
                setMobileInteractionInputValue(els.aaMobileGalleryColumnsInput, columns);
                setMobileInteractionInputValue(els.aaMobileGalleryGapInput, gap);
                setMobileInteractionInputValue(els.aaMobileGalleryRadiusInput, radius);
	                if (els.aaMobileGalleryRadiusValue) els.aaMobileGalleryRadiusValue.textContent = radius;
	                if (typeof renderGalleryItemList === 'function') renderGalleryItemList(active);
	            }
		            if (isGalleryPhoto && els.aaMobileGalleryPhotoZoomInput) {
		                els.aaMobileGalleryPhotoZoomInput.checked = active.galleryZoom !== false;
		            }
		            if (isMobileImage) {
		                const radius = Math.max(0, Math.round(Number(active.borderRadius) || 0));
		                setMobileInteractionInputValue(els.aaMobileImageRadiusInput, radius);
		                if (els.aaMobileImageRadiusValue) els.aaMobileImageRadiusValue.textContent = radius;
		            }
		            if (isFramePhoto) {
		                const shape = String(active.aaImageFrameShape || 'rounded');
		                document.querySelectorAll('[data-aa-mobile-frame-shape]').forEach(button => {
		                    button.classList.toggle('is-active', button.dataset.aaMobileFrameShape === shape);
		                });
		            }
		        }

        function ensureInteractionPopoverHome() {
            if (!els.aaInteractionPopover) return null;
            if (!state.__aaInteractionPopoverHome) {
                state.__aaInteractionPopoverHome = document.createComment('aa-interaction-popover-home');
            }
            if (!state.__aaInteractionPopoverHome.parentNode && els.aaInteractionPopover.parentNode) {
                els.aaInteractionPopover.parentNode.insertBefore(state.__aaInteractionPopoverHome, els.aaInteractionPopover);
            }
            return state.__aaInteractionPopoverHome;
        }

        function restoreInteractionPopoverHome() {
            const home = state.__aaInteractionPopoverHome;
            if (!els.aaInteractionPopover || !home?.parentNode) return;
            if (els.aaInteractionPopover.parentNode !== home.parentNode) {
                home.parentNode.insertBefore(els.aaInteractionPopover, home.nextSibling);
            }
            els.aaInteractionPopover.classList.remove('is-mobile-drawer');
            document.body.classList.remove('aa-mobile-interaction-drawer-open');
        }

        function ensureMobileInteractionDrawerHome() {
            if (!els.aaMobileInteractionDrawer) return null;
            if (!state.__aaMobileInteractionDrawerHome) {
                state.__aaMobileInteractionDrawerHome = document.createComment('aa-mobile-interaction-drawer-home');
            }
            if (!state.__aaMobileInteractionDrawerHome.parentNode && els.aaMobileInteractionDrawer.parentNode) {
                els.aaMobileInteractionDrawer.parentNode.insertBefore(state.__aaMobileInteractionDrawerHome, els.aaMobileInteractionDrawer);
            }
            return state.__aaMobileInteractionDrawerHome;
        }

        function moveMobileInteractionDrawerToFloatingLayer() {
            if (!els.aaMobileInteractionDrawer) return false;
            ensureMobileInteractionDrawerHome();
            if (els.aaMobileInteractionDrawer.parentNode !== document.body) {
                document.body.appendChild(els.aaMobileInteractionDrawer);
            }
            els.aaMobileInteractionDrawer.classList.add('is-floating-mobile-panel');
            return true;
        }

        function ensureGalleryItemListHome() {
            if (!els.aaGalleryItemList) return null;
            if (!state.__aaGalleryItemListHome) {
                state.__aaGalleryItemListHome = document.createComment('aa-gallery-item-list-home');
            }
            if (!state.__aaGalleryItemListHome.parentNode && els.aaGalleryItemList.parentNode) {
                els.aaGalleryItemList.parentNode.insertBefore(state.__aaGalleryItemListHome, els.aaGalleryItemList);
            }
            return state.__aaGalleryItemListHome;
        }

        function moveGalleryItemListToMobilePanel() {
            if (!els.aaGalleryItemList || !els.aaMobileGalleryItemListMount) return;
            ensureGalleryItemListHome();
            if (els.aaGalleryItemList.parentNode !== els.aaMobileGalleryItemListMount) {
                els.aaMobileGalleryItemListMount.appendChild(els.aaGalleryItemList);
            }
            els.aaGalleryItemList.classList.add('is-mobile-gallery-list');
        }

        function restoreGalleryItemListHome() {
            const home = state.__aaGalleryItemListHome;
            if (!els.aaGalleryItemList || !home?.parentNode) return;
            if (els.aaGalleryItemList.parentNode !== home.parentNode) {
                home.parentNode.insertBefore(els.aaGalleryItemList, home.nextSibling);
            }
            els.aaGalleryItemList.classList.remove('is-mobile-gallery-list');
        }

        function restoreMobileInteractionDrawerHome() {
            const home = state.__aaMobileInteractionDrawerHome;
            if (!els.aaMobileInteractionDrawer || !home?.parentNode) return;
            if (els.aaMobileInteractionDrawer.parentNode !== home.parentNode) {
                home.parentNode.insertBefore(els.aaMobileInteractionDrawer, home.nextSibling);
            }
            els.aaMobileInteractionDrawer.classList.remove('is-floating-mobile-panel');
            restoreGalleryItemListHome();
        }

        function openMobileInteractionDrawer(active) {
            if (!els.aaMobileInteractionDrawer) return false;
            const activePanel = getActiveLeftDrawerPanelKey?.() || '';
            const isReturningThroughUtilityDrawer = ['font', 'color'].includes(activePanel) &&
                state.__aaMobileInteractionDrawerReturn?.target === active;
            const drawerAlreadyOpen = document.body.classList.contains('aa-mobile-interaction-drawer-open') &&
                !els.aaMobileInteractionDrawer.hidden;
            if (state.__aaMobileInteractionDrawerDismissedTarget === active && !drawerAlreadyOpen) {
                els.aaInteractionPopover?.classList.remove('is-visible');
                return true;
            }
            state.__aaMobileInteractionDrawerTarget = active;
            const meta = mobileInteractionDrawerMeta(active);
            if (els.aaElementDrawerTitle) els.aaElementDrawerTitle.textContent = meta.title;
            if (els.aaElementDrawerStatus) els.aaElementDrawerStatus.textContent = meta.status;
            els.aaInteractionPopover?.classList.remove('is-visible', 'is-mobile-drawer');
            syncMobileInteractionDrawer(active);
            if (isGalleryInteractionObject(active)) {
                moveGalleryItemListToMobilePanel();
            } else {
                restoreGalleryItemListHome();
            }
            if (isReturningThroughUtilityDrawer) return true;
            moveMobileInteractionDrawerToFloatingLayer();
            document.querySelector('.aa-leftbar')?.classList.remove('is-drawer-open', 'is-acara-ai-drawer');
            document.body.classList.add('aa-mobile-interaction-drawer-open');
            return true;
        }

        function positionInteractionPopover(target) {
            const popover = els.aaInteractionPopover;
            if (!popover || !state.canvas || !target) return;

            const canvasRect = state.canvas.upperCanvasEl.getBoundingClientRect();
            const objectRect = target.getBoundingRect(true, true);
            const scaleX = canvasRect.width / Math.max(1, state.canvas.getWidth());
            const scaleY = canvasRect.height / Math.max(1, state.canvas.getHeight());
            const width = popover.offsetWidth || 320;
            const height = popover.offsetHeight || 150;
            let left = canvasRect.left + objectRect.left * scaleX - width - 14;
            let top = canvasRect.top + objectRect.top * scaleY;

            if (left < 12) {
                left = canvasRect.left + (objectRect.left + objectRect.width) * scaleX + 14;
            }
            if (left + width > window.innerWidth - 12) {
                left = canvasRect.left + objectRect.left * scaleX;
            }
            if (top + height > window.innerHeight - 12) {
                top = window.innerHeight - height - 12;
            }

            popover.style.left = `${Math.max(12, Math.min(window.innerWidth - width - 12, left))}px`;
            popover.style.top = `${Math.max(12, Math.min(window.innerHeight - height - 12, top))}px`;
        }

        function syncInteractionPopover(active = state.canvas?.getActiveObject()) {
            const setPopoverInputValue = (input, value) => {
                if (!input || document.activeElement === input) return;
                input.value = value;
            };
            const isMobileInteractionPopover = window.matchMedia('(max-width: 767px)').matches;
            const isEditingPopoverInput = isMobileInteractionPopover &&
                els.aaInteractionPopover?.contains(document.activeElement);
            const isLink = isLinkInteractionObject(active);
            const isSocialLink = isSocialLinkInteractionObject(active);
            const isCopy = isCopyInteractionObject(active);
            const isMusic = isMusicInteractionObject(active);
            const isYoutube = isYoutubeInteractionObject(active);
            const isOpeningButton = isOpeningButtonInteractionObject(active);
            const isGuestField = isGuestFieldInteractionObject(active);
            if (state.__aaMobileInteractionDrawerDismissedTarget &&
                state.__aaMobileInteractionDrawerDismissedTarget !== active) {
                state.__aaMobileInteractionDrawerDismissedTarget = null;
            }
            if (state.__aaMobileInteractionDrawerReturn?.target &&
                state.__aaMobileInteractionDrawerReturn.target !== active) {
                clearMobileInteractionDrawerReturn();
            }

            if (isMobileInteractionPopover) {
                const isMobileCandidate = isMobileInteractionDrawerCandidate(active);
                if (document.querySelector('.aa-modal.is-open')) {
                    hideInteractionPopover({
                        forceMobileDrawer: true,
                    });
                    state.__aaMobileInteractionDrawerTarget = null;
                    state.__aaMobileInteractionDrawerDismissedTarget = null;
                    clearMobileInteractionDrawerReturn();
                    return;
                }
                if (!isMobileCandidate) {
                    hideInteractionPopover({
                        forceMobileDrawer: true,
                    });
                    if (!isMobileInteractionDrawerFocused()) {
                        state.__aaMobileInteractionDrawerTarget = null;
                        state.__aaMobileInteractionDrawerDismissedTarget = null;
                        clearMobileInteractionDrawerReturn();
                    }
                    return;
                }
                if (typeof aaIsNativeTransformActive === 'function' && aaIsNativeTransformActive(active)) {
                    hideInteractionPopover({
                        preserveMobileDrawer: true,
                    });
                    return;
                }
                if (typeof aaIsOutsideTransformActive === 'function' && aaIsOutsideTransformActive(active)) {
                    hideInteractionPopover({
                        preserveMobileDrawer: true,
                    });
                    return;
                }
                if (document.body.classList.contains('aa-mobile-interaction-drawer-open') ||
                    isMobileInteractionDrawerFocused()) {
                    openMobileInteractionDrawer(active);
                }
                return;
            }

            if (!els.aaInteractionPopover || (!isLink && !isSocialLink && !isCopy && !isYoutube && !isOpeningButton && !
                    isGuestField) || document
                .querySelector('.aa-modal.is-open')) {
                hideInteractionPopover();
                restoreInteractionPopoverHome();
                return;
            }

            restoreInteractionPopoverHome();

            els.aaLinkPopoverSection?.classList.toggle('is-active', isLink);
            els.aaSocialPopoverSection?.classList.toggle('is-active', isSocialLink);
            els.aaCopyPopoverSection?.classList.toggle('is-active', isCopy);
            els.aaMusicPopoverSection?.classList.remove('is-active');
            els.aaYoutubePopoverSection?.classList.toggle('is-active', isYoutube);
            els.aaOpeningButtonPopoverSection?.classList.toggle('is-active', isOpeningButton);
            els.aaGuestFieldPopoverSection?.classList.toggle('is-active', isGuestField);
            if (els.aaLinkPopoverUrlInput) els.aaLinkPopoverUrlInput.value = active.link || '';
            if (els.aaSocialPopoverPlatformInput) {
                els.aaSocialPopoverPlatformInput.value = active.socialPlatform || 'instagram';
            }
            if (els.aaSocialPopoverLabelInput) {
                els.aaSocialPopoverLabelInput.value = active.socialLabel || 'Instagram';
            }
            if (els.aaSocialPopoverUrlInput) els.aaSocialPopoverUrlInput.value = active.link || '';
            if (els.aaCopyPopoverTextInput) els.aaCopyPopoverTextInput.value = active.copyText || '';
            if (els.aaCopyPopoverFeedbackInput) {
                els.aaCopyPopoverFeedbackInput.value = active.copyFeedback || '';
            }
            if (els.aaMusicPopoverUrlInput) els.aaMusicPopoverUrlInput.value = active.audioUrl || '';
            if (els.aaYoutubePopoverUrlInput) els.aaYoutubePopoverUrlInput.value = active.youtubeUrl || '';
            if (isYoutube) {
                const youtubeBox = getInteractiveBox(active);
                const youtubeBg = active.controlBackground || youtubeBox?.fill || '#111827';
                const youtubeRadius = Math.max(0, Math.round(active.controlRadius ?? youtubeBox?.rx ?? 18));
                if (els.aaYoutubePopoverBgInput) {
                    setAlphaColorInputValue(els.aaYoutubePopoverBgInput, youtubeBg, '#111827');
                }
                if (els.aaYoutubePopoverRadiusInput) {
                    els.aaYoutubePopoverRadiusInput.value = youtubeRadius;
                }
                if (els.aaYoutubePopoverRadiusValue) {
                    els.aaYoutubePopoverRadiusValue.textContent = youtubeRadius;
                }
                if (els.aaYoutubePopoverAutoplayInput) {
                    els.aaYoutubePopoverAutoplayInput.checked = active.youtubeAutoplayOnView !== false;
                }
                if (els.aaYoutubePopoverLoopInput) {
                    els.aaYoutubePopoverLoopInput.checked = active.youtubeLoop !== false;
                }
            }
            if (isMusic) {
                const musicBox = getInteractiveBox(active);
                const musicBg = active.controlBackground || musicBox?.fill || '#0f766e';
                const musicRadius = Math.max(0, Math.round(active.controlRadius ?? musicBox?.rx ?? 66));
                if (els.aaMusicPopoverBgInput) {
                    setAlphaColorInputValue(els.aaMusicPopoverBgInput, musicBg, '#0f766e');
                }
                if (els.aaMusicPopoverRadiusInput) {
                    els.aaMusicPopoverRadiusInput.value = musicRadius;
                }
                if (els.aaMusicPopoverRadiusValue) {
                    els.aaMusicPopoverRadiusValue.textContent = musicRadius;
                }
            }
            if (els.aaMusicPopoverAutoplayInput) {
                els.aaMusicPopoverAutoplayInput.checked = active.autoplayAfterInteraction !== false;
            }
            if (els.aaMusicPopoverLoopInput) els.aaMusicPopoverLoopInput.checked = active.loopAudio !== false;
            if (els.aaMusicPopoverShowButtonInput) {
                els.aaMusicPopoverShowButtonInput.checked = active.showPlayerButton !== false;
            }
            if (isOpeningButton) {
                const parts = getOpeningButtonParts(active);
                const openingBg = normalizeColor(active.controlBackground || parts.box?.fill || '#0f766e');
                const openingTextColor = normalizeColor(active.controlTextColor || parts.text?.fill || '#ffffff');
                const openingRadius = Math.max(0, Math.round(active.controlRadius ?? parts.box?.rx ?? 48));
                const openingPadding = Math.max(6, Math.round(active.openingButtonPadding ?? active.openingButtonPaddingY ?? 28));
                if (els.aaOpeningButtonBgInput) els.aaOpeningButtonBgInput.value = openingBg;
                if (els.aaOpeningButtonTextColorInput) els.aaOpeningButtonTextColorInput.value = openingTextColor;
                if (els.aaOpeningButtonFontInput) els.aaOpeningButtonFontInput.value = parts.text?.fontFamily || active.openingButtonFontFamily || 'Inter';
                if (els.aaOpeningButtonRadiusInput) els.aaOpeningButtonRadiusInput.value = openingRadius;
                if (els.aaOpeningButtonRadiusValue) els.aaOpeningButtonRadiusValue.textContent = openingRadius;
                if (els.aaOpeningButtonPaddingYInput) els.aaOpeningButtonPaddingYInput.value = openingPadding;
                if (els.aaOpeningButtonPaddingYValue) els.aaOpeningButtonPaddingYValue.textContent = openingPadding;
            }
            if (isGuestField) {
                const parts = getGuestbookObjectParts(active);
                setPopoverInputValue(els.aaGuestFieldPopoverTextInput, active.customType === 'guest-submit-button' ?
                    (active.buttonText || parts.text?.text || active.placeholder || '') :
                    (active.placeholder || parts.text?.text || active.label || ''));
                setPopoverInputValue(els.aaGuestFieldPopoverBgInput, normalizeColor(parts.box?.fill || '#ffffff'));
                setPopoverInputValue(els.aaGuestFieldPopoverFontInput, parts.text?.fontFamily || 'Inter');
                setPopoverInputValue(els.aaGuestFieldPopoverSizeInput, Math.round(parts.text?.fontSize || 36));
                setPopoverInputValue(els.aaGuestFieldPopoverColorInput, normalizeColor(parts.text?.fill || '#334155'));
                const guestRadius = Math.max(0, Math.round(active.borderRadius ?? parts.box?.rx ?? 18));
                setPopoverInputValue(els.aaGuestFieldPopoverRadiusInput, guestRadius);
                if (els.aaGuestFieldPopoverRadiusValue) {
                    els.aaGuestFieldPopoverRadiusValue.textContent = guestRadius;
                }
                const canRequire = ['guest-name-input', 'guest-attendance-select', 'guest-message-textarea']
                    .includes(
                        active.customType);
                if (els.aaGuestFieldPopoverRequiredWrap) {
                    els.aaGuestFieldPopoverRequiredWrap.hidden = !canRequire;
                }
                if (els.aaGuestFieldPopoverRequiredInput) {
                    els.aaGuestFieldPopoverRequiredInput.checked = active.required === true;
                }
                const hasMax = ['guest-name-input', 'guest-message-textarea', 'guest-comment-list'].includes(active
                    .customType);
                if (els.aaGuestFieldPopoverMaxWrap) {
                    els.aaGuestFieldPopoverMaxWrap.hidden = !hasMax;
                }
                setPopoverInputValue(els.aaGuestFieldPopoverMaxInput, Math.max(0, Number(active.maxLength) || 0));
            }
            els.aaInteractionPopover.classList.add('is-visible');
            if (isEditingPopoverInput) return;
            window.cancelAnimationFrame(state.__aaInteractionPopoverFrame || 0);
            state.__aaInteractionPopoverFrame = requestAnimationFrame(() => positionInteractionPopover(active));
        }

        function syncInteractionUi(active = state.canvas?.getActiveObject()) {
            const isLink = isLinkInteractionObject(active);
            const isSocialLink = isSocialLinkInteractionObject(active);
            const isCopy = isCopyInteractionObject(active);
            els.aaLinkPanel?.classList.toggle('hidden', !isLink);
            els.aaCopyPanel?.classList.toggle('hidden', !isCopy);
            if (els.aaLinkUrlInput) {
                els.aaLinkUrlInput.disabled = !isLink;
                els.aaLinkUrlInput.value = isLink ? (active.link || '') : '';
            }
            if (els.aaCopyTextInput) {
                els.aaCopyTextInput.disabled = !isCopy;
                els.aaCopyTextInput.value = isCopy ? (active.copyText || '') : '';
            }
            if (els.aaCopyFeedbackInput) {
                els.aaCopyFeedbackInput.disabled = !isCopy;
                els.aaCopyFeedbackInput.value = isCopy ? (active.copyFeedback || '') : '';
            }
            syncInteractionPopover(active);
        }

        function applyLinkInteractionValue(value) {
            const active = getInteractionApplyTarget(isLinkInteractionObject);
            if (!isLinkInteractionObject(active)) return;
            active.set({
                link: value.trim(),
                customType: 'link-text',
                underline: (active.type === 'i-text' || active.type === 'textbox' || active.type ===
                        'text') ?
                    true : active.underline,
            });
            state.canvas.requestRenderAll();
            syncInteractionUi(active);
            snapshot();
        }

        function applyCopyInteractionValue(values = {}) {
            const active = getInteractionApplyTarget(isCopyInteractionObject);
            if (!isCopyInteractionObject(active)) return;
            active.set({
                customType: 'copy-text',
                ...(Object.prototype.hasOwnProperty.call(values, 'copyText') ? {
                    copyText: values.copyText,
                } : {}),
                ...(Object.prototype.hasOwnProperty.call(values, 'copyFeedback') ? {
                    copyFeedback: values.copyFeedback,
                } : {}),
            });
            state.canvas.requestRenderAll();
            syncInteractionUi(active);
            snapshot();
        }

        function applyMusicInteractionValue(values = {}) {
            const active = state.canvas.getActiveObject();
            if (!isMusicInteractionObject(active)) return;
            active.set({
                ...(Object.prototype.hasOwnProperty.call(values, 'audioUrl') ? {
                    audioUrl: String(values.audioUrl || '').trim(),
                } : {}),
                ...(Object.prototype.hasOwnProperty.call(values, 'autoplayAfterInteraction') ? {
                    autoplayAfterInteraction: values.autoplayAfterInteraction === true,
                } : {}),
                ...(Object.prototype.hasOwnProperty.call(values, 'loopAudio') ? {
                    loopAudio: values.loopAudio === true,
                } : {}),
                ...(Object.prototype.hasOwnProperty.call(values, 'showPlayerButton') ? {
                    showPlayerButton: values.showPlayerButton === true,
                } : {}),
            });
            syncInteractionPopover(active);
            snapshot();
        }

        function applyMusicPopoverStyle(values = {}) {
            const active = state.canvas.getActiveObject();
            if (!isMusicInteractionObject(active)) return;
            updateInteractiveControlStyle(values);
            if (els.aaMusicPopoverRadiusValue && Object.prototype.hasOwnProperty.call(values, 'controlRadius')) {
                els.aaMusicPopoverRadiusValue.textContent = Math.max(0, Math.round(Number(values.controlRadius) ||
                    0));
            }
            syncInteractionPopover(active);
        }

        function musicUrlLabel(url = '') {
            const source = String(url || '').trim();
            if (!source) return 'Audio URL kosong';
            try {
                const parsed = new URL(source, window.location.origin);
                return decodeURIComponent(parsed.pathname.split('/').filter(Boolean).pop() || parsed.hostname || source);
            } catch (error) {
                return source.split('/').filter(Boolean).pop() || source;
            }
        }

        function musicDrawerEscape(value = '') {
            return String(value || '').replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[character] || character));
        }

        function activeMusicDrawerUrl() {
            const active = state.canvas?.getActiveObject?.();
            return isMusicInteractionObject(active) ? String(active.audioUrl || '').trim() : '';
        }

        function stopMusicDrawerPreview() {
            const preview = state.musicDrawerPreview;
            if (preview?.audio) {
                preview.audio.pause();
                preview.audio.removeAttribute('src');
                preview.audio.load?.();
            }
            state.musicDrawerPreview = null;
            document.querySelectorAll('.aa-music-preview-btn.is-playing').forEach(button => {
                button.classList.remove('is-playing');
                button.innerHTML = '<i class="fa fa-play" aria-hidden="true"></i>';
                button.setAttribute('aria-label', 'Preview musik');
            });
        }

        function toggleMusicDrawerPreview(url, button) {
            const src = String(url || '').trim();
            if (!src) return;
            const current = state.musicDrawerPreview;
            if (current?.url === src && current.audio && !current.audio.paused) {
                stopMusicDrawerPreview();
                return;
            }

            stopMusicDrawerPreview();
            const audio = new Audio(src);
            audio.preload = 'none';
            state.musicDrawerPreview = { url: src, audio };
            if (button) {
                button.classList.add('is-playing');
                button.innerHTML = '<i class="fa fa-pause" aria-hidden="true"></i>';
                button.setAttribute('aria-label', 'Pause preview musik');
            }
            audio.addEventListener('ended', stopMusicDrawerPreview, { once: true });
            audio.addEventListener('error', () => {
                stopMusicDrawerPreview();
                setStatus('Preview musik gagal diputar.', 'error');
            }, { once: true });
            audio.play().catch(() => {
                stopMusicDrawerPreview();
                setStatus('Preview musik gagal diputar.', 'error');
            });
        }

        function collectMusicDrawerUrls() {
            const urls = new Map();
            const addUrl = (url, title = '') => {
                const clean = String(url || '').trim();
                if (!clean || urls.has(clean)) return;
                urls.set(clean, title || musicUrlLabel(clean));
            };

            const active = state.canvas?.getActiveObject?.();
            if (active?.customType === 'music-player') {
                addUrl(active.audioUrl, 'Music aktif');
            }

            (state.pages || []).forEach((page, pageIndex) => {
                (page.objects || []).forEach(object => {
                    if (object?.customType !== 'music-player') return;
                    addUrl(object.audioUrl, `${page.title || `Halaman ${pageIndex + 1}`}`);
                });
            });

            return Array.from(urls, ([url, title]) => ({
                url,
                title,
                label: musicUrlLabel(url),
            }));
        }

        function setMusicDrawerUploadStatus(message = '', type = '') {
            if (!els.aaMusicDrawerUploadStatus) return;
            els.aaMusicDrawerUploadStatus.textContent = message || 'MP3, M4A, WAV, atau OGG maksimal 4MB.';
            els.aaMusicDrawerUploadStatus.classList.toggle('is-error', type === 'error');
            els.aaMusicDrawerUploadStatus.classList.toggle('is-success', type === 'success');
        }

        function musicDrawerApplyUrl(url) {
            const active = state.canvas?.getActiveObject?.();
            if (!isMusicInteractionObject(active)) {
                setStatus('Pilih object Music terlebih dahulu.', 'error');
                return;
            }

            applyMusicInteractionValue({
                audioUrl: url,
            });
            stopMusicDrawerPreview();
            syncMusicDrawerForSelection(state.canvas?.getActiveObject?.());
            setStatus('Music URL dipakai.');
        }

        function renderMusicAudioList(element, items, emptyText = 'Belum ada audio.') {
            if (!element) return;
            element.innerHTML = '';

            if (!items.length) {
                const empty = document.createElement('div');
                empty.className = 'aa-music-drawer-empty';
                empty.innerHTML = `<i class="fa fa-music" aria-hidden="true"></i><span>${musicDrawerEscape(emptyText)}</span>`;
                element.appendChild(empty);
                return;
            }

            items.forEach(item => {
                const url = String(item.url || item.src || '').trim();
                if (!url) return;
                const activeUrl = activeMusicDrawerUrl();
                const isActive = activeUrl && activeUrl === url;
                const isPreviewing = state.musicDrawerPreview?.url === url && state.musicDrawerPreview?.audio && !state.musicDrawerPreview.audio.paused;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `aa-music-drawer-item${isActive ? ' is-active' : ''}`;
                button.title = url;
                button.dataset.aaMusicUrl = url;
                const label = item.label || item.name || musicUrlLabel(url);
                const title = item.title ? ` - ${item.title}` : '';
                button.innerHTML = `
                    <span class="aa-music-preview-btn${isPreviewing ? ' is-playing' : ''}" role="button" aria-label="${isPreviewing ? 'Pause preview musik' : 'Preview musik'}" tabindex="-1">
                        <i class="fa fa-${isPreviewing ? 'pause' : 'play'}" aria-hidden="true"></i>
                    </span>
                    <span class="aa-music-drawer-item-label">${musicDrawerEscape(`${label}${title}`)}</span>
                    <i class="fa fa-check aa-music-active-check" aria-hidden="true"></i>
                `;
                button.addEventListener('click', event => {
                    event.preventDefault();
                    if (event.target?.closest?.('.aa-music-preview-btn')) {
                        event.stopPropagation();
                        toggleMusicDrawerPreview(url, button.querySelector('.aa-music-preview-btn'));
                        return;
                    }
                    musicDrawerApplyUrl(url);
                });
                element.appendChild(button);
            });
        }

        function renderMusicDrawerList() {
            renderMusicAudioList(els.aaMusicBuiltinList, state.musicBuiltinAudio || [],
                state.musicLibraryLoading ? 'Memuat musik tersedia...' : 'Belum ada musik tersedia.');
            renderMusicAudioList(els.aaMusicUploadedList, state.musicUploadedAudio || [],
                state.musicLibraryLoading ? 'Memuat upload kamu...' : 'Belum ada audio upload.');
            renderMusicAudioList(els.aaMusicDrawerList, collectMusicDrawerUrls(), 'Belum ada music yang dipakai di desain.');
        }

        async function loadMusicLibrary(options = {}) {
            if (!config.audioLibraryUrl || state.musicLibraryLoading) return;
            if (state.musicLibraryLoaded && options.force !== true) {
                renderMusicDrawerList();
                return;
            }

            state.musicLibraryLoading = true;
            renderMusicDrawerList();

            try {
                const response = await fetch(config.audioLibraryUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Music library gagal dimuat.');
                }
                state.musicBuiltinAudio = Array.isArray(data.data?.builtin) ? data.data.builtin : [];
                state.musicUploadedAudio = Array.isArray(data.data?.uploaded) ? data.data.uploaded : [];
                state.musicLibraryLoaded = true;
            } catch (error) {
                setMusicDrawerUploadStatus(error.message || 'Music library gagal dimuat.', 'error');
            } finally {
                state.musicLibraryLoading = false;
                renderMusicDrawerList();
            }
        }

        async function uploadMusicDrawerFile(file) {
            if (!file || !config.audioUploadUrl) return;

            if (file.size > (config.audioUploadMaxFileSize || 4 * 1024 * 1024)) {
                setMusicDrawerUploadStatus('Ukuran audio maksimal 4MB.', 'error');
                setStatus('Ukuran audio maksimal 4MB.', 'error');
                return;
            }

            const extension = String(file.name || '').split('.').pop().toLowerCase();
            if (!['mp3', 'm4a', 'wav', 'ogg'].includes(extension)) {
                setMusicDrawerUploadStatus('Gunakan format MP3, M4A, WAV, atau OGG.', 'error');
                setStatus('Format audio tidak sesuai.', 'error');
                return;
            }

            const chunkSize = Math.max(256 * 1024, Number(config.audioUploadChunkSize || 1024 * 1024));
            if (file.size > chunkSize && config.audioChunkUploadUrl) {
                await uploadMusicDrawerFileInChunks(file, extension, chunkSize);
                return;
            }

            const form = new FormData();
            form.append('audio', file);
            state.musicUploading = true;
            if (els.aaMusicDrawerUploadBtn) els.aaMusicDrawerUploadBtn.disabled = true;
            setMusicDrawerUploadStatus('Mengupload audio...', '');
            setStatus('Mengupload audio...', 'saving');

            try {
                const response = await fetch(config.audioUploadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: form,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Audio gagal diupload.');
                }
                const uploaded = Array.isArray(data.data) ? data.data : [];
                state.musicUploadedAudio = [...uploaded, ...(state.musicUploadedAudio || [])];
                state.musicLibraryLoaded = true;
                renderMusicDrawerList();
                if (uploaded[0]?.src) {
                    musicDrawerApplyUrl(uploaded[0].src);
                }
                setMusicDrawerUploadStatus(data.message || 'Audio berhasil diupload.', 'success');
                setStatus(data.message || 'Audio berhasil diupload.');
            } catch (error) {
                setMusicDrawerUploadStatus(error.message || 'Audio gagal diupload.', 'error');
                setStatus(error.message || 'Audio gagal diupload.', 'error');
            } finally {
                state.musicUploading = false;
                if (els.aaMusicDrawerUploadBtn) els.aaMusicDrawerUploadBtn.disabled = false;
                if (els.aaMusicDrawerFileInput) els.aaMusicDrawerFileInput.value = '';
            }
        }

        async function uploadMusicDrawerFileInChunks(file, extension, chunkSize) {
            const total = Math.ceil(file.size / chunkSize);
            const uploadId = `${Date.now()}-${Math.random().toString(36).slice(2)}-${extension}`;
            let finalData = null;

            state.musicUploading = true;
            if (els.aaMusicDrawerUploadBtn) els.aaMusicDrawerUploadBtn.disabled = true;
            setMusicDrawerUploadStatus('Mengupload audio 0%...', '');
            setStatus('Mengupload audio...', 'saving');

            try {
                for (let index = 0; index < total; index++) {
                    const start = index * chunkSize;
                    const end = Math.min(file.size, start + chunkSize);
                    const form = new FormData();
                    form.append('uploadId', uploadId);
                    form.append('fileName', file.name || `audio.${extension}`);
                    form.append('fileSize', String(file.size));
                    form.append('index', String(index));
                    form.append('total', String(total));
                    form.append('chunk', file.slice(start, end), `${index}.part`);

                    const response = await fetch(config.audioChunkUploadUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: form,
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.success === false) {
                        throw new Error(data.message || 'Audio gagal diupload.');
                    }

                    const percent = Math.round(((index + 1) / total) * 100);
                    setMusicDrawerUploadStatus(`Mengupload audio ${percent}%...`, '');
                    if (data.complete === true) {
                        finalData = data;
                    }
                }

                if (!finalData) {
                    throw new Error('Audio belum selesai diproses. Coba upload ulang.');
                }

                const uploaded = Array.isArray(finalData.data) ? finalData.data : [];
                state.musicUploadedAudio = [...uploaded, ...(state.musicUploadedAudio || [])];
                state.musicLibraryLoaded = true;
                renderMusicDrawerList();
                if (uploaded[0]?.src) {
                    musicDrawerApplyUrl(uploaded[0].src);
                }
                setMusicDrawerUploadStatus(finalData.message || 'Audio berhasil diupload.', 'success');
                setStatus(finalData.message || 'Audio berhasil diupload.');
            } catch (error) {
                setMusicDrawerUploadStatus(error.message || 'Audio gagal diupload.', 'error');
                setStatus(error.message || 'Audio gagal diupload.', 'error');
            } finally {
                state.musicUploading = false;
                if (els.aaMusicDrawerUploadBtn) els.aaMusicDrawerUploadBtn.disabled = false;
                if (els.aaMusicDrawerFileInput) els.aaMusicDrawerFileInput.value = '';
            }
        }

        function syncMusicDrawerForSelection(active = state.canvas?.getActiveObject?.()) {
            if (!els.aaMusicDrawerUrlInput) return;
            const isMusic = isMusicInteractionObject(active);
            const box = isMusic ? getInteractiveBox(active) : null;
            const audioUrl = isMusic ? String(active.audioUrl || '').trim() : '';
            const bg = normalizeColor(active?.controlBackground || box?.fill || '#0f766e');
            const radius = Math.max(0, Math.round(Number(active?.controlRadius ?? box?.rx ?? 66) || 0));

            if (els.aaMusicDrawerStatus) {
                els.aaMusicDrawerStatus.textContent = isMusic ?
                    'Atur audio, tombol, autoplay, dan loop untuk Music Player yang dipilih.' :
                    'Pilih object Music di canvas untuk mengatur audio.';
            }
            if (els.aaMusicDrawerTitle) els.aaMusicDrawerTitle.textContent = isMusic ? 'Music Player aktif' : 'Music Player';
            if (els.aaMusicDrawerSubtitle) els.aaMusicDrawerSubtitle.textContent = audioUrl ? musicUrlLabel(audioUrl) : 'Belum ada audio';

            els.aaMusicDrawerUrlInput.disabled = !isMusic;
            els.aaMusicDrawerBgInput && (els.aaMusicDrawerBgInput.disabled = !isMusic);
            els.aaMusicDrawerRadiusInput && (els.aaMusicDrawerRadiusInput.disabled = !isMusic);
            els.aaMusicDrawerShapeInput && (els.aaMusicDrawerShapeInput.disabled = !isMusic);
            els.aaMusicDrawerAutoplayInput && (els.aaMusicDrawerAutoplayInput.disabled = !isMusic);
            els.aaMusicDrawerLoopInput && (els.aaMusicDrawerLoopInput.disabled = !isMusic);
            els.aaMusicDrawerShowButtonInput && (els.aaMusicDrawerShowButtonInput.disabled = !isMusic);

            els.aaMusicDrawerUrlInput.value = audioUrl;
            if (els.aaMusicDrawerBgInput) els.aaMusicDrawerBgInput.value = bg;
            if (els.aaMusicDrawerRadiusInput) els.aaMusicDrawerRadiusInput.value = radius;
            if (els.aaMusicDrawerShapeInput) els.aaMusicDrawerShapeInput.value = active?.musicButtonShape || 'circle';
            if (els.aaMusicDrawerAutoplayInput) els.aaMusicDrawerAutoplayInput.checked = active?.autoplayAfterInteraction !== false;
            if (els.aaMusicDrawerLoopInput) els.aaMusicDrawerLoopInput.checked = active?.loopAudio !== false;
            if (els.aaMusicDrawerShowButtonInput) els.aaMusicDrawerShowButtonInput.checked = active?.showPlayerButton !== false;

            renderMusicDrawerList();
        }

        function openMusicDrawer() {
            hideInteractionPopover();
            openLeftDrawerPanel('music');
            syncMusicDrawerForSelection();
            loadMusicLibrary();
        }

        function applyYoutubeInteractionValue(values = {}) {
            const active = getInteractionApplyTarget(isYoutubeInteractionObject);
            if (!isYoutubeInteractionObject(active)) return;
            const url = Object.prototype.hasOwnProperty.call(values, 'youtubeUrl') ? String(values.youtubeUrl || '').trim() : active.youtubeUrl || '';
            active.set({
                youtubeUrl: url,
                youtubeVideoId: parseYoutubeVideoId(url),
                ...(Object.prototype.hasOwnProperty.call(values, 'youtubeAutoplayOnView') ? {
                    youtubeAutoplayOnView: values.youtubeAutoplayOnView === true,
                } : {}),
                ...(Object.prototype.hasOwnProperty.call(values, 'youtubeLoop') ? {
                    youtubeLoop: values.youtubeLoop === true,
                } : {}),
            });
            refreshYoutubePreviewObject(active);
            syncInteractionPopover(active);
            snapshot();
        }

        function applyYoutubePopoverStyle(values = {}) {
            const active = getInteractionApplyTarget(isYoutubeInteractionObject);
            if (!isYoutubeInteractionObject(active)) return;
            updateInteractiveControlStyle(values);
            if (els.aaYoutubePopoverRadiusValue && Object.prototype.hasOwnProperty.call(values, 'controlRadius')) {
                els.aaYoutubePopoverRadiusValue.textContent = Math.max(0, Math.round(Number(values.controlRadius) ||
                    0));
            }
            syncInteractionPopover(active);
        }

        function refreshOpeningButtonBounds(object) {
            if (!object || !object.getObjects) return;
            if (typeof object._calcBounds === 'function') object._calcBounds();
            if (typeof object._updateObjectsCoords === 'function') object._updateObjectsCoords();
            object.dirty = true;
            object.setCoords();
        }

        function preserveObjectCenter(object, callback) {
            if (!object || typeof callback !== 'function') return;
            const center = typeof object.getCenterPoint === 'function' ? object.getCenterPoint() : null;
            callback();
            refreshOpeningButtonBounds(object);
            if (center && typeof object.setPositionByOrigin === 'function') {
                object.setPositionByOrigin(center, 'center', 'center');
            }
            object.setCoords();
        }

        function applyOpeningButtonInteractionValue(values = {}) {
            const active = getInteractionApplyTarget(isOpeningButtonInteractionObject);
            if (!isOpeningButtonInteractionObject(active)) return;
            const parts = getOpeningButtonParts(active);
            const next = {};

            preserveObjectCenter(active, () => {
                const layoutOpeningButton = paddingValue => {
                    if (!parts.box || !parts.text) return;
                    const padding = Math.max(6, Number(paddingValue) || 28);
                    const currentWidth = Math.max(120, Number(parts.box.width) || Number(active.width) || 420);
                    const textHeight = Math.max(24, Number(parts.text.fontSize) || 34) * 1.18;
                    const nextHeight = Math.round(textHeight + padding * 2);
                    next.openingButtonPadding = padding;
                    next.openingButtonPaddingY = padding;
                    parts.box.set({
                        left: -currentWidth / 2,
                        top: -nextHeight / 2,
                        width: currentWidth,
                        height: nextHeight,
                    });
                    parts.text.set({
                        left: -currentWidth / 2 + padding,
                        top: 0,
                        width: Math.max(24, currentWidth - padding * 2),
                        originY: 'center',
                        textAlign: 'center',
                    });
                    parts.text.dirty = true;
                    if (typeof parts.text.initDimensions === 'function') {
                        parts.text.initDimensions();
                    }
                    if (els.aaOpeningButtonPaddingYValue) {
                        els.aaOpeningButtonPaddingYValue.textContent = Math.round(padding);
                    }
                };

                if (parts.box && Object.prototype.hasOwnProperty.call(values, 'controlBackground')) {
                    const background = values.controlBackground || '#0f766e';
                    next.controlBackground = background;
                    parts.box.set({
                        fill: background,
                        stroke: background,
                    });
                }

                if (parts.text && Object.prototype.hasOwnProperty.call(values, 'controlTextColor')) {
                    const color = values.controlTextColor || '#ffffff';
                    next.controlTextColor = color;
                    parts.text.set('fill', color);
                }

                if (parts.box && Object.prototype.hasOwnProperty.call(values, 'controlRadius')) {
                    const radius = Math.max(0, Number(values.controlRadius) || 0);
                    next.controlRadius = radius;
                    parts.box.set({
                        rx: radius,
                        ry: radius,
                    });
                    if (els.aaOpeningButtonRadiusValue) {
                        els.aaOpeningButtonRadiusValue.textContent = Math.round(radius);
                    }
                }

                if (parts.text && Object.prototype.hasOwnProperty.call(values, 'fontFamily')) {
                    const family = values.fontFamily || 'Inter';
                    next.openingButtonFontFamily = family;
                    parts.text.set('fontFamily', family);
                    parts.text.dirty = true;
                    if (typeof parts.text.initDimensions === 'function') {
                        parts.text.initDimensions();
                    }
                }

                if (parts.box && Object.prototype.hasOwnProperty.call(values, 'padding')) {
                    layoutOpeningButton(values.padding);
                } else if (parts.box && parts.text && Object.keys(values).length) {
                    layoutOpeningButton(active.openingButtonPadding ?? active.openingButtonPaddingY ?? 28);
                }

                active.set(next);
            });
            state.canvas.requestRenderAll();
            syncInteractionPopover(active);
            syncInspector();
            snapshot();
        }

        function applyGuestFieldInteractionValue(values = {}) {
            const active = getInteractionApplyTarget(isGuestFieldInteractionObject);
            if (!isGuestFieldInteractionObject(active)) return;
            const parts = getGuestbookObjectParts(active);

            if (Object.prototype.hasOwnProperty.call(values, 'text')) {
                const textValue = String(values.text || '');
                if (parts.text) {
                    parts.text.set('text', textValue);
                    parts.text.dirty = true;
                    if (typeof parts.text.initDimensions === 'function') {
                        parts.text.initDimensions();
                    }
                }
                active.set({
                    placeholder: textValue,
                    label: textValue,
                    buttonText: active.customType === 'guest-submit-button' ? textValue : active.buttonText,
                });
            }

            if (parts.box && Object.prototype.hasOwnProperty.call(values, 'backgroundColor')) {
                parts.box.set('fill', values.backgroundColor);
            }

            if (parts.box && Object.prototype.hasOwnProperty.call(values, 'borderRadius')) {
                const radius = Math.max(0, Number(values.borderRadius) || 0);
                active.set('borderRadius', radius);
                parts.box.set({
                    rx: radius,
                    ry: radius,
                });
                if (els.aaGuestFieldPopoverRadiusValue) {
                    els.aaGuestFieldPopoverRadiusValue.textContent = Math.round(radius);
                }
            }

            if (parts.text) {
                const textStyle = {};
                if (Object.prototype.hasOwnProperty.call(values, 'fontFamily')) {
                    textStyle.fontFamily = values.fontFamily || 'Inter';
                }
                if (Object.prototype.hasOwnProperty.call(values, 'fontSize')) {
                    textStyle.fontSize = Math.max(8, Number(values.fontSize) || 36);
                }
                if (Object.prototype.hasOwnProperty.call(values, 'fill')) {
                    textStyle.fill = values.fill || '#334155';
                }
                if (Object.keys(textStyle).length) {
                    parts.text.set(textStyle);
                    parts.text.dirty = true;
                    if (typeof parts.text.initDimensions === 'function') {
                        parts.text.initDimensions();
                    }
                }
            }

            if (Object.prototype.hasOwnProperty.call(values, 'required')) {
                active.set('required', values.required === true);
            }

            if (Object.prototype.hasOwnProperty.call(values, 'maxLength')) {
                active.set('maxLength', Math.max(0, Number(values.maxLength) || 0));
            }

            active.dirty = true;
            active.setCoords();
            state.canvas.requestRenderAll();
            syncInteractionPopover(active);
            syncInspector();
            snapshot();
        }

        function syncObjectFloatingToolbar() {
            const toolbar = els.aaObjectFloatingToolbar;
            if (!toolbar || !state.canvas) return;
            const isMobileFloatingToolbar = window.matchMedia('(max-width: 767px)').matches;
            if (document.querySelector('.aa-modal.is-open')) {
                toolbar.classList.remove('is-visible');
                hideObjectOverflowOverlay();
                return;
            }
            if (isMobileFloatingToolbar && (aaIsOutsideTransformActive() || aaIsNativeTransformActive())) {
                toolbar.classList.remove('is-visible');
                closeObjectContextMenu();
                hideObjectOverflowOverlay();
                return;
            }
            if (aaIsOutsideTransformActive()) {
                toolbar.classList.remove('is-visible');
                closeObjectContextMenu();
                syncObjectOverflowOverlay(state.objectTransformOverlayDrag.target);
                return;
            }
            if (aaIsNativeTransformActive()) {
                toolbar.classList.remove('is-visible');
                closeObjectContextMenu();
                syncObjectOverflowOverlay(aaGetNativeTransformTarget());
                return;
            }
            const target = getObjectToolbarTarget();
            toolbar.classList.toggle('is-visible', Boolean(target));
            if (isMobileFloatingToolbar) {
                hideObjectOverflowOverlay();
            } else {
                syncObjectOverflowOverlay(target);
            }
            if (!target) return;

            const protectedPhotobooth = typeof aaIsProtectedPhotoboothObject === 'function' && aaIsProtectedPhotoboothObject(target);
            const setFloatingToolHidden = function(button, hidden) {
                if (!button) return;
                button.hidden = Boolean(hidden);
                button.classList.toggle('is-photobooth-hidden', Boolean(hidden));
                button.setAttribute('aria-hidden', hidden ? 'true' : 'false');
                if (hidden) {
                    button.disabled = true;
                    button.tabIndex = -1;
                } else {
                    button.removeAttribute('tabindex');
                }
            };
            const icon = els.aaFloatingLockBtn?.querySelector('i');
            if (icon) {
                icon.className = target.locked === true ? 'fa fa-lock-open' : 'fa fa-lock';
            }
            if (els.aaFloatingLockBtn) {
                els.aaFloatingLockBtn.title = target.locked === true ? 'Unlock object' : 'Lock object';
                els.aaFloatingLockBtn.classList.toggle('is-disabled', protectedPhotobooth);
                setFloatingToolHidden(els.aaFloatingLockBtn, protectedPhotobooth);
                if (!protectedPhotobooth) {
                    els.aaFloatingLockBtn.disabled = false;
                }
            }
            if (els.aaFloatingDuplicateBtn) {
                els.aaFloatingDuplicateBtn.classList.toggle('is-disabled', protectedPhotobooth);
                els.aaFloatingDuplicateBtn.title = protectedPhotobooth ? 'Slot foto Photobooth tidak bisa diduplikasi.' : 'Duplicate';
                setFloatingToolHidden(els.aaFloatingDuplicateBtn, protectedPhotobooth);
                if (!protectedPhotobooth) {
                    els.aaFloatingDuplicateBtn.disabled = false;
                }
            }
            if (els.aaFloatingDeleteBtn) {
                els.aaFloatingDeleteBtn.classList.toggle('is-disabled', protectedPhotobooth);
                els.aaFloatingDeleteBtn.title = protectedPhotobooth ? 'Slot foto Photobooth wajib ada dan tidak bisa dihapus.' : 'Delete';
                setFloatingToolHidden(els.aaFloatingDeleteBtn, protectedPhotobooth);
                if (!protectedPhotobooth) {
                    els.aaFloatingDeleteBtn.disabled = false;
                }
            }
            if (els.aaFloatingInteractionBtn) {
                const showInteractionButton = !protectedPhotobooth && isMobileFloatingToolbar && isMobileInteractionDrawerCandidate(target);
                if (showInteractionButton) {
                    els.aaFloatingInteractionBtn.hidden = false;
                    els.aaFloatingInteractionBtn.removeAttribute('hidden');
                } else {
                    els.aaFloatingInteractionBtn.hidden = true;
                    els.aaFloatingInteractionBtn.setAttribute('hidden', 'hidden');
                }
                els.aaFloatingInteractionBtn.classList.toggle('is-visible', showInteractionButton);
                els.aaFloatingInteractionBtn.setAttribute('aria-hidden', showInteractionButton ? 'false' : 'true');
            }

            const canvasRect = state.canvas.upperCanvasEl.getBoundingClientRect();
            const objectRect = target.getBoundingRect(true, true);
            const scaleX = canvasRect.width / Math.max(1, state.canvas.getWidth());
            const scaleY = canvasRect.height / Math.max(1, state.canvas.getHeight());
            const width = toolbar.offsetWidth || (isMobileFloatingToolbar ? 170 : 72);
            const height = toolbar.offsetHeight || 38;
            const gap = 12;
            const viewportPad = 12;
            const objectScreen = {
                left: canvasRect.left + objectRect.left * scaleX,
                top: canvasRect.top + objectRect.top * scaleY,
                width: objectRect.width * scaleX,
                height: objectRect.height * scaleY,
            };
            let left = objectScreen.left + objectScreen.width + gap;
            let top = objectScreen.top + ((objectScreen.height - height) / 2);

            if (isMobileFloatingToolbar) {
                left = objectScreen.left + ((objectScreen.width - width) / 2);
                top = objectScreen.top - height - gap;
                if (top < viewportPad) {
                    top = Math.max(viewportPad, objectScreen.top + 8);
                }
                toolbar.style.left =
                    `${Math.max(viewportPad, Math.min(window.innerWidth - width - viewportPad, left))}px`;
                toolbar.style.top =
                    `${Math.max(viewportPad, Math.min(window.innerHeight - height - viewportPad, top))}px`;
                return;
            }

            if (left + width > window.innerWidth - viewportPad) {
                left = objectScreen.left - width - gap;
            }

            if (left < viewportPad) {
                left = objectScreen.left + ((objectScreen.width - width) / 2);
                top = objectScreen.top + objectScreen.height + gap;
            }

            if (top + height > window.innerHeight - viewportPad) {
                top = objectScreen.top - height - gap;
            }

            toolbar.style.left =
                `${Math.max(viewportPad, Math.min(window.innerWidth - width - viewportPad, left))}px`;
            toolbar.style.top =
                `${Math.max(viewportPad, Math.min(window.innerHeight - height - viewportPad, top))}px`;
        }

        function aaIsCropModeActive() {
            return Boolean(
                state.isCropping ||
                state.cropBox ||
                state.cropTarget ||
                state.cropDomDrag
            );
        }

        function aaIsOutsideTransformActive(target = null) {
            const drag = state.objectTransformOverlayDrag;
            if (!drag || !drag.target) return false;
            return !target || drag.target === target;
        }

        function aaGetNativeTransformTarget() {
            return state.canvas?._currentTransform?.target || null;
        }

        function aaIsNativeTransformActive(target = null) {
            const transformTarget = aaGetNativeTransformTarget();
            if (!transformTarget) return false;
            return !target || transformTarget === target;
        }

        function aaSetOutsideSelectionActiveHandle(handle = '') {
            state.outsideSelectionActiveHandle = String(handle || 'move');
        }

        function aaClearOutsideSelectionActiveHandle() {
            state.outsideSelectionActiveHandle = '';
            state.outsideSelectionOverlay?.classList.remove('is-transforming', 'is-native-transforming');
            state.outsideSelectionOverlay?.querySelectorAll('[data-aa-outside-handle]').forEach(handle => {
                handle.classList.remove('is-active-transform-handle');
            });
        }

        function aaSyncOutsideSelectionTransformState(overlay) {
            if (!overlay) return;
            const activeHandle = String(state.outsideSelectionActiveHandle || '');
            const outsideTransform = aaIsOutsideTransformActive();
            const nativeTransform = aaIsNativeTransformActive();
            overlay.classList.toggle('is-transforming', outsideTransform);
            overlay.classList.toggle('is-native-transforming', nativeTransform && !outsideTransform);
            overlay.dataset.aaOutsideActiveHandle = activeHandle;
            overlay.querySelectorAll('[data-aa-outside-handle]').forEach(handle => {
                handle.classList.toggle(
                    'is-active-transform-handle',
                    outsideTransform && activeHandle !== 'move' && handle.dataset.aaOutsideHandle === activeHandle
                );
            });
        }

        function aaBeginNativeSelectionTransformUi(target = aaGetNativeTransformTarget()) {
            if (!target || target === state.cropBox || state.isCropping) return;
            aaSetOutsideSelectionActiveHandle('native');
            state.selectionTransformUiHidden = true;
            document.body.classList.add('aa-selection-transform-active');
            els.aaObjectFloatingToolbar?.classList.remove('is-visible');
            closeObjectContextMenu();
            syncObjectOverflowOverlay(target);
        }

        function aaBeginSelectionTransformUi() {
            if (aaIsCropModeActive()) return;
            state.selectionTransformUiHidden = true;
            document.body.classList.add('aa-selection-transform-active');
        }

        function aaEndSelectionTransformUi() {
            if (!state.selectionTransformUiHidden) return;
            state.selectionTransformUiHidden = false;
            document.body.classList.remove('aa-selection-transform-active');
            aaClearOutsideSelectionActiveHandle();
        }

        function hideObjectFloatingToolbar() {
            els.aaObjectFloatingToolbar?.classList.remove('is-visible');
            hideObjectOverflowOverlay();
        }

        function hideObjectOverflowOverlay() {
            aaRestoreNativeOutsideSelection();
            state.outsideSelectionOverlay?.classList.remove('is-visible');
            state.outsideSelectionVisualTarget = null;
        }

        function hideObjectTransformOverlay() {
            aaRestoreNativeOutsideSelection();
        }

        function aaRestoreNativeOutsideSelection() {
            const target = state.outsideSelectionNativeTarget;
            const original = state.outsideSelectionNativeState;

            if (target && original) {
                target.set({
                    hasBorders: original.hasBorders,
                    hasControls: original.hasControls,
                });
                target.setCoords?.();
                if (typeof state.canvas?.requestRenderAll === 'function') {
                    state.canvas.requestRenderAll();
                } else {
                    state.canvas?.renderAll?.();
                }
            }

            state.outsideSelectionNativeTarget = null;
            state.outsideSelectionNativeState = null;
        }

        function aaHideNativeSelectionForOutsideOverlay(target) {
            if (aaIsCropModeActive()) {
                aaRestoreNativeOutsideSelection();
                return;
            }

            if (!target) return;
            aaRestoreNativeOutsideSelection();
        }

        function objectScreenMetrics(target) {
            if (!state.canvas?.upperCanvasEl || !target) return null;
            const canvasRect = state.canvas.upperCanvasEl.getBoundingClientRect();
            const objectRect = target.getBoundingRect(true, true);
            const scaleX = canvasRect.width / Math.max(1, state.canvas.getWidth());
            const scaleY = canvasRect.height / Math.max(1, state.canvas.getHeight());
            const objectBox = {
                left: canvasRect.left + objectRect.left * scaleX,
                top: canvasRect.top + objectRect.top * scaleY,
                right: canvasRect.left + (objectRect.left + objectRect.width) * scaleX,
                bottom: canvasRect.top + (objectRect.top + objectRect.height) * scaleY,
            };
            objectBox.width = objectBox.right - objectBox.left;
            objectBox.height = objectBox.bottom - objectBox.top;
            return {
                canvasRect,
                objectRect,
                scaleX,
                scaleY,
                objectBox,
                outside: objectBox.left < canvasRect.left || objectBox.top < canvasRect.top ||
                    objectBox.right > canvasRect.right || objectBox.bottom > canvasRect.bottom,
            };
        }

        function aaObjectSelectionScreenBox(target, metrics) {
            if (!target || !metrics) return null;

            const mapPoint = function(point) {
                return {
                    x: metrics.canvasRect.left + point.x * metrics.scaleX,
                    y: metrics.canvasRect.top + point.y * metrics.scaleY,
                };
            };

            const coords = typeof target.getCoords === 'function' ? target.getCoords() : null;

            if (Array.isArray(coords) && coords.length >= 4) {
                const tl = mapPoint(coords[0]);
                const tr = mapPoint(coords[1]);
                const br = mapPoint(coords[2]);
                const bl = mapPoint(coords[3]);
                const points = [tl, tr, br, bl];
                const bounds = {
                    left: Math.min(...points.map(point => point.x)),
                    top: Math.min(...points.map(point => point.y)),
                    right: Math.max(...points.map(point => point.x)),
                    bottom: Math.max(...points.map(point => point.y)),
                };
                bounds.width = bounds.right - bounds.left;
                bounds.height = bounds.bottom - bounds.top;

                return {
                    left: tl.x,
                    top: tl.y,
                    width: Math.max(2, Math.hypot(tr.x - tl.x, tr.y - tl.y)),
                    height: Math.max(2, Math.hypot(bl.x - tl.x, bl.y - tl.y)),
                    angle: Math.atan2(tr.y - tl.y, tr.x - tl.x),
                    bounds,
                };
            }

            return {
                left: metrics.objectBox.left,
                top: metrics.objectBox.top,
                width: Math.max(2, metrics.objectBox.width),
                height: Math.max(2, metrics.objectBox.height),
                angle: 0,
                bounds: metrics.objectBox,
            };
        }

        function aaClampScreenNumber(value, min, max) {
            value = Number(value);

            if (!Number.isFinite(value)) {
                value = min;
            }

            if (max < min) {
                return min;
            }

            return Math.max(min, Math.min(max, value));
        }

        function aaIntersectScreenRect(rectA, rectB) {
            if (!rectA || !rectB) return null;

            const left = Math.max(rectA.left, rectB.left);
            const top = Math.max(rectA.top, rectB.top);
            const right = Math.min(rectA.right, rectB.right);
            const bottom = Math.min(rectA.bottom, rectB.bottom);

            return {
                left,
                top,
                right,
                bottom,
                width: Math.max(0, right - left),
                height: Math.max(0, bottom - top)
            };
        }

        function aaGetVisibleCanvasScreenRect(canvasRect) {
            const pad = 14;

            const viewportRect = {
                left: pad,
                top: pad,
                right: window.innerWidth - pad,
                bottom: window.innerHeight - pad
            };

            viewportRect.width = Math.max(1, viewportRect.right - viewportRect.left);
            viewportRect.height = Math.max(1, viewportRect.bottom - viewportRect.top);

            const visibleCanvas = aaIntersectScreenRect(canvasRect, viewportRect);

            if (!visibleCanvas || visibleCanvas.width <= 1 || visibleCanvas.height <= 1) {
                return viewportRect;
            }

            return visibleCanvas;
        }

        function aaMakeSafeTransformOverlayBox(metrics) {
            if (!metrics || !metrics.objectBox || !metrics.canvasRect) {
                return null;
            }

            const objectBox = metrics.objectBox;
            const visibleCanvas = aaGetVisibleCanvasScreenRect(metrics.canvasRect);

            const objectScreenRect = {
                left: objectBox.left,
                top: objectBox.top,
                right: objectBox.right,
                bottom: objectBox.bottom,
                width: objectBox.width,
                height: objectBox.height
            };

            let safeBox = aaIntersectScreenRect(objectScreenRect, visibleCanvas);

            const minSize = 46;

            // Kalau object masih ada bagian yang terlihat di canvas,
            // pakai area yang terlihat sebagai tempat handle.
            if (safeBox && safeBox.width > 8 && safeBox.height > 8) {
                let width = Math.max(minSize, safeBox.width);
                let height = Math.max(minSize, safeBox.height);

                let left = safeBox.left;
                let top = safeBox.top;

                if (width > visibleCanvas.width) {
                    width = visibleCanvas.width;
                }

                if (height > visibleCanvas.height) {
                    height = visibleCanvas.height;
                }

                left = aaClampScreenNumber(left, visibleCanvas.left, visibleCanvas.right - width);
                top = aaClampScreenNumber(top, visibleCanvas.top, visibleCanvas.bottom - height);

                return {
                    left,
                    top,
                    width,
                    height,
                    right: left + width,
                    bottom: top + height,
                    isClamped: true
                };
            }

            // Kalau object benar-benar berada di luar area canvas,
            // munculkan kotak kontrol kecil di sisi canvas terdekat.
            const objectCenterX = objectBox.left + objectBox.width / 2;
            const objectCenterY = objectBox.top + objectBox.height / 2;

            const centerX = aaClampScreenNumber(objectCenterX, visibleCanvas.left + minSize / 2, visibleCanvas
                .right - minSize / 2);
            const centerY = aaClampScreenNumber(objectCenterY, visibleCanvas.top + minSize / 2, visibleCanvas
                .bottom - minSize / 2);

            return {
                left: centerX - minSize / 2,
                top: centerY - minSize / 2,
                width: minSize,
                height: minSize,
                right: centerX + minSize / 2,
                bottom: centerY + minSize / 2,
                isClamped: true
            };
        }

        function syncObjectTransformOverlay(target, metrics = null) {
            if (aaIsCropModeActive()) {
                hideObjectTransformOverlay();
                return;
            }

            hideObjectTransformOverlay();
        }

        function setOverflowPart(part, rect) {
            if (!part || rect.width <= 0 || rect.height <= 0) {
                part?.classList.remove('is-visible');
                return;
            }
            part.style.left = `${Math.round(rect.left)}px`;
            part.style.top = `${Math.round(rect.top)}px`;
            part.style.width = `${Math.round(rect.width)}px`;
            part.style.height = `${Math.round(rect.height)}px`;
            part.classList.add('is-visible');
        }

        function aaOutsideSelectionMarkup() {
            return [
                '<span class="aa-outside-selection-corner is-tl" data-aa-outside-handle="tl"></span>',
                '<span class="aa-outside-selection-corner is-tr" data-aa-outside-handle="tr"></span>',
                '<span class="aa-outside-selection-corner is-br" data-aa-outside-handle="br"></span>',
                '<span class="aa-outside-selection-corner is-bl" data-aa-outside-handle="bl"></span>',
                '<span class="aa-outside-selection-pill is-horizontal is-top" data-aa-outside-handle="mt"></span>',
                '<span class="aa-outside-selection-pill is-horizontal is-bottom" data-aa-outside-handle="mb"></span>',
                '<span class="aa-outside-selection-pill is-vertical is-left" data-aa-outside-handle="ml"></span>',
                '<span class="aa-outside-selection-pill is-vertical is-right" data-aa-outside-handle="mr"></span>',
                '<span class="aa-outside-selection-rotate" data-aa-outside-handle="mtr" aria-hidden="true">',
                '<svg viewBox="0 0 32 32" fill="none"><path d="M8.2 18.8a8.3 8.3 0 0 0 13.6 4.6" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/><path d="M20.2 28.2l2-4.9-5.2.2" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M23.8 13.2A8.3 8.3 0 0 0 10.2 8.6" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/><path d="M11.8 3.8l-2 4.9 5.2-.2" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                '</span>'
            ].join('');
        }

        function aaOutsideSelectionCursorForHandle(handle) {
            if (handle === 'tl' || handle === 'br') return 'nwse-resize';
            if (handle === 'tr' || handle === 'bl') return 'nesw-resize';
            if (handle === 'mtr') return 'grab';
            if (handle === 'ml' || handle === 'mr') return 'ew-resize';
            if (handle === 'mt' || handle === 'mb') return 'ns-resize';
            return 'move';
        }

        function aaOutsideSelectionCropSideForHandle(handle) {
            return {
                ml: 'left',
                mr: 'right',
                mt: 'top',
                mb: 'bottom',
            } [handle] || '';
        }

        function aaCanOutsideSelectionCropTarget(target) {
            return Boolean(
                target &&
                target.type === 'image' &&
                target.locked !== true &&
                target.customType !== 'background' &&
                target !== state.cropBox &&
                !aaIsCropModeActive() &&
                typeof getCropSideTransformStart === 'function' &&
                typeof cropImageFromSideStart === 'function'
            );
        }

        function aaScreenPointToCanvasPoint(clientX, clientY, metrics = null) {
            const screenMetrics = metrics || (state.outsideSelectionVisualTarget ? objectScreenMetrics(state.outsideSelectionVisualTarget) : null);
            if (!screenMetrics?.canvasRect) return null;
            return {
                x: (clientX - screenMetrics.canvasRect.left) / Math.max(0.001, screenMetrics.scaleX || 1),
                y: (clientY - screenMetrics.canvasRect.top) / Math.max(0.001, screenMetrics.scaleY || 1),
            };
        }

        function aaOutsideSelectionResizeCenter(box) {
            if (!box) return null;
            const cos = Math.cos(box.angle || 0);
            const sin = Math.sin(box.angle || 0);
            return {
                x: box.left + ((box.width * cos) - (box.height * sin)) / 2,
                y: box.top + ((box.width * sin) + (box.height * cos)) / 2,
            };
        }

        function aaGetOutsideSelectionOverlay() {
            if (state.outsideSelectionOverlay) return state.outsideSelectionOverlay;
            const overlay = document.createElement('div');
            overlay.className = 'aa-outside-selection-overlay';
            overlay.setAttribute('aria-hidden', 'true');
            overlay.innerHTML = ['top', 'bottom', 'left', 'right'].map(key =>
                `<div class="aa-outside-selection-pane" data-aa-outside-pane="${key}"><div class="aa-outside-selection-box">${aaOutsideSelectionMarkup()}</div></div>`
            ).join('');
            overlay.addEventListener('pointerdown', startOutsideSelectionMoveOverlay, true);
            document.body.appendChild(overlay);
            state.outsideSelectionOverlay = overlay;
            return overlay;
        }

        function startOutsideSelectionMoveOverlay(event) {
            const box = event.target?.closest?.('.aa-outside-selection-box');
            if (!box || event.button !== 0) return;
            const target = state.outsideSelectionVisualTarget || state.canvas?.getActiveObject?.();
            if (!aaIsOutsideSelectionVisualTarget(target)) return;
            const handle = String(event.target?.closest?.('[data-aa-outside-handle]')?.dataset?.aaOutsideHandle || '');
            const isCornerResize = ['tl', 'tr', 'br', 'bl'].includes(handle);
            const isRotate = handle === 'mtr';
            const cropSide = aaOutsideSelectionCropSideForHandle(handle);
            const isImageSideCrop = Boolean(cropSide && aaCanOutsideSelectionCropTarget(target));

            if (handle && !isCornerResize && !isRotate && !isImageSideCrop) {
                event.preventDefault();
                event.stopPropagation();
                if (typeof event.stopImmediatePropagation === 'function') {
                    event.stopImmediatePropagation();
                }
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }
            const captureTarget = event.target?.closest?.('[data-aa-outside-handle]') || box;
            if (typeof captureTarget.setPointerCapture === 'function' && event.pointerId !== undefined) {
                try {
                    captureTarget.setPointerCapture(event.pointerId);
                    state.objectTransformOverlayPointerBox = captureTarget;
                    state.objectTransformOverlayPointerId = event.pointerId;
                } catch (error) {
                    state.objectTransformOverlayPointerBox = null;
                    state.objectTransformOverlayPointerId = null;
                }
            }

            if (isCornerResize || isRotate) {
                const metrics = objectScreenMetrics(target);
                const selectionBox = aaObjectSelectionScreenBox(target, metrics);
                const center = aaOutsideSelectionResizeCenter(selectionBox);
                const pointerDistance = center ? Math.hypot(event.clientX - center.x, event.clientY - center.y) : 0;
                if (!metrics || !selectionBox || !center || pointerDistance < 1) {
                    if (
                        state.objectTransformOverlayPointerBox &&
                        state.objectTransformOverlayPointerId !== null &&
                        state.objectTransformOverlayPointerId !== undefined &&
                        typeof state.objectTransformOverlayPointerBox.releasePointerCapture === 'function'
                    ) {
                        try {
                            state.objectTransformOverlayPointerBox.releasePointerCapture(state.objectTransformOverlayPointerId);
                        } catch (error) {
                            // Pointer capture can already be released by the browser.
                        }
                    }
                    state.objectTransformOverlayPointerBox = null;
                    state.objectTransformOverlayPointerId = null;
                    return;
                }

                if (isRotate) {
                    state.objectTransformOverlayDrag = {
                        mode: 'rotate',
                        target,
                        startX: event.clientX,
                        startY: event.clientY,
                        center,
                        startAngle: Number(target.angle) || 0,
                        pointerAngle: Math.atan2(event.clientY - center.y, event.clientX - center.x) * 180 / Math.PI,
                    };
                } else {
                    state.objectTransformOverlayDrag = {
                        mode: handle,
                        target,
                        corner: handle,
                        startX: event.clientX,
                        startY: event.clientY,
                        center,
                        pointerDistance,
                        startScaleX: Number(target.scaleX) || 1,
                        startScaleY: Number(target.scaleY) || 1,
                    };
                }
            } else if (isImageSideCrop) {
                const cropTransform = {
                    target
                };
                const cropStart = getCropSideTransformStart(cropTransform, target, cropSide);

                if (!cropStart) {
                    if (
                        state.objectTransformOverlayPointerBox &&
                        state.objectTransformOverlayPointerId !== null &&
                        state.objectTransformOverlayPointerId !== undefined &&
                        typeof state.objectTransformOverlayPointerBox.releasePointerCapture === 'function'
                    ) {
                        try {
                            state.objectTransformOverlayPointerBox.releasePointerCapture(state.objectTransformOverlayPointerId);
                        } catch (error) {
                            // Pointer capture can already be released by the browser.
                        }
                    }
                    state.objectTransformOverlayPointerBox = null;
                    state.objectTransformOverlayPointerId = null;
                    return;
                }

                state.objectTransformOverlayDrag = {
                    mode: `crop-${cropSide}`,
                    side: cropSide,
                    target,
                    cropStart,
                    startX: event.clientX,
                    startY: event.clientY,
                };
            } else {
                state.objectTransformOverlayDrag = {
                    mode: 'move',
                    target,
                    startX: event.clientX,
                    startY: event.clientY,
                    startLeft: Number(target.left) || 0,
                    startTop: Number(target.top) || 0,
                };
            }

            aaSetOutsideSelectionActiveHandle(handle || 'move');
            aaBeginSelectionTransformUi();
            document.body.style.userSelect = 'none';
            document.body.style.cursor = aaOutsideSelectionCursorForHandle(handle);
            window.addEventListener('pointermove', moveObjectTransformOverlay, true);
            window.addEventListener('pointerup', stopObjectTransformOverlay, true);
        }

        function aaIsOutsideSelectionVisualTarget(target) {
            if (!target || !state.canvas || aaIsCropModeActive()) return false;
            const activeOutsideTransform = state.objectTransformOverlayDrag?.target === target;
            const activeNativeTransform = aaIsNativeTransformActive(target);
            if (!activeOutsideTransform && !activeNativeTransform && (state.objectTransformOverlayDrag || state.canvas._currentTransform || state.selectionTransformUiHidden)) return false;
            if (target === state.cropBox || target.visible === false || target.locked === true) return false;
            if (target.type === 'activeSelection' || target.type === 'group') return false;
            if (target.isEditing === true) return false;
            return activeNativeTransform || state.canvas.getActiveObject?.() === target;
        }

        function aaOutsideSelectionPanes(canvasRect) {
            const vw = Math.max(1, window.innerWidth || 1);
            const vh = Math.max(1, window.innerHeight || 1);
            const left = Math.max(0, Math.min(vw, canvasRect.left));
            const top = Math.max(0, Math.min(vh, canvasRect.top));
            const right = Math.max(0, Math.min(vw, canvasRect.right));
            const bottom = Math.max(0, Math.min(vh, canvasRect.bottom));

            return {
                top: { left: 0, top: 0, width: vw, height: top },
                bottom: { left: 0, top: bottom, width: vw, height: Math.max(0, vh - bottom) },
                left: { left: 0, top, width: left, height: Math.max(0, bottom - top) },
                right: { left: right, top, width: Math.max(0, vw - right), height: Math.max(0, bottom - top) },
            };
        }

        function aaMobileOutsideSelectionViewportPane(box) {
            if (!box || !window.matchMedia('(max-width: 767px)').matches) return null;

            const pad = 8;
            const vw = Math.max(1, window.innerWidth || 1);
            const vh = Math.max(1, window.innerHeight || 1);
            const boxRight = Number(box.left) + Number(box.width);
            const boxBottom = Number(box.top) + Number(box.height);
            if (
                !Number.isFinite(box.left) ||
                !Number.isFinite(box.top) ||
                !Number.isFinite(boxRight) ||
                !Number.isFinite(boxBottom)
            ) {
                return null;
            }

            if (boxRight < pad || box.left > vw - pad || boxBottom < pad || box.top > vh - pad) {
                return null;
            }

            return {
                left: 0,
                top: 0,
                width: vw,
                height: vh,
            };
        }

        function aaBoxIntersectsRect(box, rect) {
            if (!box || !rect) return false;
            return (
                box.left < rect.left + rect.width &&
                box.left + box.width > rect.left &&
                box.top < rect.top + rect.height &&
                box.top + box.height > rect.top
            );
        }

        function aaIsMobileEditorViewport() {
            return window.matchMedia('(max-width: 767px)').matches;
        }

        function aaHasEnoughVisibleCanvasArea(metrics) {
            if (!metrics?.objectBox || !metrics?.canvasRect) return false;
            const visiblePart = aaIntersectScreenRect(metrics.objectBox, metrics.canvasRect);
            if (!visiblePart) return false;
            const minVisibleSize = aaIsMobileEditorViewport() ? 28 : 8;
            return visiblePart.width >= minVisibleSize && visiblePart.height >= minVisibleSize;
        }

        function aaPlaceOutsideSelectionPane(pane, rect, box) {
            if (!pane || rect.width <= 0 || rect.height <= 0) {
                if (pane) pane.style.display = 'none';
                return;
            }

            pane.style.display = 'block';
            pane.style.left = `${Math.round(rect.left)}px`;
            pane.style.top = `${Math.round(rect.top)}px`;
            pane.style.width = `${Math.round(rect.width)}px`;
            pane.style.height = `${Math.round(rect.height)}px`;

            const selection = pane.querySelector('.aa-outside-selection-box');
            if (!selection) return;
            const visualScale = Number(state.outsideSelectionVisualScale) || 1;
            selection.style.left = `${box.left - rect.left}px`;
            selection.style.top = `${box.top - rect.top}px`;
            selection.style.width = `${box.width}px`;
            selection.style.height = `${box.height + (2 * visualScale)}px`;
            selection.style.transform = `rotate(${box.angle}rad)`;
        }

        function syncObjectOverflowOverlay(target = getObjectToolbarTarget()) {
            if (aaIsCropModeActive()) {
                hideObjectOverflowOverlay();
                return;
            }

            if (!aaIsOutsideSelectionVisualTarget(target)) {
                hideObjectOverflowOverlay();
                return;
            }

            const metrics = objectScreenMetrics(target);
            if (!metrics || !metrics.outside) {
                hideObjectOverflowOverlay();
                return;
            }

            if (aaIsMobileEditorViewport()) {
                if (aaIsNativeTransformActive(target) || state.objectTransformOverlayDrag?.target === target) {
                    hideObjectOverflowOverlay();
                    return;
                }

                if (aaHasEnoughVisibleCanvasArea(metrics)) {
                    hideObjectOverflowOverlay();
                    return;
                }
            }

            const box = aaObjectSelectionScreenBox(target, metrics);
            if (!box || !box.bounds || box.bounds.width <= 2 || box.bounds.height <= 2) {
                hideObjectOverflowOverlay();
                return;
            }

            const overlay = aaGetOutsideSelectionOverlay();
            const visualScale = Math.max(0.18, Math.min(1.5, Number(state.zoom) || 1));
            state.outsideSelectionVisualScale = visualScale;
            overlay.style.setProperty('--aa-outside-selection-scale', visualScale.toFixed(4));
            overlay.classList.toggle('is-image-target', aaCanOutsideSelectionCropTarget(target));
            aaSyncOutsideSelectionTransformState(overlay);
            const panes = aaOutsideSelectionPanes(metrics.canvasRect);
            const mobileViewportPane = aaMobileOutsideSelectionViewportPane(box);
            const shouldUseMobileViewportPane = Boolean(mobileViewportPane) &&
                !Object.values(panes).some(rect => aaBoxIntersectsRect(box, rect));
            Object.keys(panes).forEach(key => {
                const pane = overlay.querySelector(`[data-aa-outside-pane="${key}"]`);
                if (shouldUseMobileViewportPane) {
                    aaPlaceOutsideSelectionPane(pane, key === 'top' ? mobileViewportPane : { left: 0, top: 0, width: 0, height: 0 }, box);
                    return;
                }

                aaPlaceOutsideSelectionPane(pane, panes[key], box);
            });
            state.outsideSelectionVisualTarget = target;
            overlay.classList.add('is-visible');
        }

        function startObjectTransformOverlay(event) {
            aaRestoreNativeOutsideSelection();
        }

        function moveObjectTransformOverlay(event) {
            const drag = state.objectTransformOverlayDrag;
            if (!drag || !drag.target || !state.canvas) return;
            event?.preventDefault?.();
            event?.stopPropagation?.();
            const metrics = objectScreenMetrics(drag.target);
            if (!metrics) return;
            const scaleX = metrics.scaleX || 1;
            const scaleY = metrics.scaleY || 1;

            if (drag.mode === 'move') {
                drag.target.set({
                    left: drag.startLeft + ((event.clientX - drag.startX) / scaleX),
                    top: drag.startTop + ((event.clientY - drag.startY) / scaleY),
                });
            } else if (drag.mode === 'rotate') {
                const angle = Math.atan2(event.clientY - drag.center.y, event.clientX - drag.center.x) * 180 / Math
                    .PI;
                drag.target.set('angle', drag.startAngle + (angle - drag.pointerAngle));
            } else if (String(drag.mode || '').indexOf('crop-') === 0) {
                const point = aaScreenPointToCanvasPoint(event.clientX, event.clientY, metrics);
                if (!point || !drag.side || !drag.cropStart || !aaCanOutsideSelectionCropTarget(drag.target)) return;
                const changed = cropImageFromSideStart(drag.target, drag.side, point.x, point.y, drag.cropStart);
                if (changed) {
                    syncCropUi?.();
                    setStatus?.('Crop diperbarui');
                }
            } else if (drag.mode === 'e' || drag.mode === 'w') {
                const distance = Math.max(8, Math.abs(event.clientX - drag.center.x));
                const factor = Math.max(.05, Math.min(20, distance / drag.pointerDistanceX));
                drag.target.set({
                    scaleX: drag.startScaleX * factor,
                    scaleY: drag.startScaleY,
                });
            } else if (drag.mode === 'n' || drag.mode === 's') {
                const distance = Math.max(8, Math.abs(event.clientY - drag.center.y));
                const factor = Math.max(.05, Math.min(20, distance / drag.pointerDistanceY));
                drag.target.set({
                    scaleX: drag.startScaleX,
                    scaleY: drag.startScaleY * factor,
                });
            } else {
                const distance = Math.max(8, Math.hypot(event.clientX - drag.center.x, event.clientY - drag.center
                    .y));
                const factor = Math.max(.05, Math.min(20, distance / drag.pointerDistance));
                drag.target.set({
                    scaleX: drag.startScaleX * factor,
                    scaleY: drag.startScaleY * factor,
                });
            }

            drag.target.setCoords();

            if (typeof state.canvas.requestRenderAll === 'function') {
                state.canvas.requestRenderAll();
            } else {
                state.canvas.renderAll();
            }

            const isMobileOverlayDrag = window.matchMedia('(max-width: 767px)').matches;
            if (!isMobileOverlayDrag) {
                syncObjectFloatingToolbar();
                syncObjectOverflowOverlay(drag.target);
                syncCountdownContextToolbar();
                syncInteractionPopover();
            }
            syncCropUi();
        }

        function stopObjectTransformOverlay(event = null) {
            if (!state.objectTransformOverlayDrag) return;
            event?.preventDefault?.();
            event?.stopPropagation?.();
            const drag = state.objectTransformOverlayDrag;
            const target = drag.target;
            state.objectTransformOverlayDrag = null;
            const pointerBox = state.objectTransformOverlayPointerBox;
            const pointerId = state.objectTransformOverlayPointerId;
            state.objectTransformOverlayPointerBox = null;
            state.objectTransformOverlayPointerId = null;
            if (pointerBox && pointerId !== null && pointerId !== undefined && typeof pointerBox.releasePointerCapture === 'function') {
                try {
                    pointerBox.releasePointerCapture(pointerId);
                } catch (error) {
                    // Pointer capture can already be released by the browser.
                }
            }
            aaEndSelectionTransformUi();
            document.body.style.userSelect = '';
            document.body.style.cursor = '';
            window.removeEventListener('pointermove', moveObjectTransformOverlay, true);
            window.removeEventListener('pointerup', stopObjectTransformOverlay, true);
            if (target) {
                if (['tl', 'tr', 'br', 'bl'].includes(drag.mode) && typeof aaFinalizeTextboxResize === 'function' && isFabricTextObject(target)) {
                    aaFinalizeTextboxResize(target, drag.corner || drag.mode || 'br');
                }

                target.setCoords();
                state.canvas.setActiveObject(target);
                if (typeof state.canvas.requestRenderAll === 'function') {
                    state.canvas.requestRenderAll();
                } else {
                    state.canvas.renderAll();
                }

                syncInspector();
                syncObjectFloatingToolbar();
                syncObjectOverflowOverlay(target);
                storeCurrentPage();
                snapshot();
            }
        }

        function clearFabricActiveTransform() {
            if (!state.canvas) return;
            state.canvas._currentTransform = null;
            state.canvas._groupSelector = null;
            if (state.canvas.upperCanvasEl) {
                state.canvas.upperCanvasEl.style.cursor = '';
            }
        }

       function settleEditorPointerState(event) {
    if (state.cropDomDrag) {
        stopCropDomDrag();
    }

    if (state.objectTransformOverlayDrag) {
        stopObjectTransformOverlay();
        return;
    }

    const active = state.canvas?.getActiveObject?.();
    const transformTarget = state.canvas?._currentTransform?.target;
    const target = transformTarget || active;

        if (target && aaIsTextboxObject(target)) {
            const corner = state.__aaTextResizeCorner || state.canvas?._currentTransform?.corner || '';

            if (corner) {
                aaFinalizeTextboxResize(target, corner);
            } else {
                aaHardReleaseFabricTransform();
            }

        aaEndSelectionTransformUi();
        syncInspector();
        syncObjectFloatingToolbar();
        syncObjectOverflowOverlay(target);
        storeCurrentPage();
        snapshot();

        return;
    }

    document.body.style.userSelect = '';
    document.body.style.cursor = '';
    aaEndSelectionTransformUi();

    const settleNativeTransform = function () {
        clearFabricActiveTransform();
        syncObjectFloatingToolbar();
        syncObjectOverflowOverlay(target);
    };

    if (window.requestAnimationFrame) {
        requestAnimationFrame(settleNativeTransform);
    } else {
        window.setTimeout(settleNativeTransform, 16);
    }
}

        function aaIsEditorUiClick(target) {
            if (!target || typeof target.closest !== 'function') return false;

            return Boolean(target.closest([
                '.aa-topbar',
                '.aa-leftbar',
                '.aa-rightbar',
                '.aa-left-drawer',

                '.aa-object-floating-toolbar',
                '#aaObjectFloatingToolbar',

                '.aa-context-toolbar',
                '#aaContextToolbar',

                '.aa-text-context-toolbar',
                '#aaTextContextToolbar',

                '.aa-countdown-context-toolbar',
                '#aaCountdownContextToolbar',

                '.aa-interaction-popover',
                '#aaInteractionPopover',
                '.aa-mobile-interaction-drawer',
                '#aaMobileInteractionDrawer',

                '.aa-object-context-menu',
                '#aaObjectContextMenu',

                '.aa-outside-selection-overlay',

                '.aa-crop-floating-toolbar',
                '#aaCropFloatingToolbar',

                '.aa-crop-dom-overlay',
                '#aaCropDomOverlay',

                '.aa-modal',
                '.editor-access-modal',

                '.page-top-controls',
                '.page-insert-row',
                '.page-menu-wrap',
                '.page-more-menu',

                '#aaContextFlipPopover',
                '.aa-context-flip-popover',

                '#aaContextStrokePopover',
                '.aa-context-stroke-popover',

                '#aaContextRadiusPopover',
                '.aa-context-radius-popover',

                '#aaContextTransparencyPopover',
                '.aa-context-transparency-popover',

                '#aaTextEffectsPopover',
                '.aa-text-effects-popover',

                '#aaAnimationPopover',
                '.aa-animation-popover',

                '#aaCountdownDatePicker',
                '.aa-date-picker',

                '#aaContextColorInput',
                '#aaTextContextColorInput',
                '#aaContextStrokeColorInput',
                '#aaTextEffectStrokeColor',
                '#aaTextEffectShadowColor'
            ].join(',')));
        }

        function aaIsCanvasClick(target) {
            if (!target) return false;

            return Boolean(
                target.closest('.canvas-container') ||
                target.closest('.upper-canvas') ||
                target.closest('.lower-canvas') ||
                target.closest('#aaFabricCanvas') ||
                target.closest('.aa-artboard-frame')
            );
        }

        function aaClearSelectionWhenClickOutsideCanvas(event) {
            if (!state.canvas || aaIsCropModeActive()) return;
            if (state.objectTransformOverlayDrag || state.cropDomDrag) return;
            if (event.button !== undefined && event.button !== 0) return;

            const target = event.target;

            if (aaIsEditorUiClick(target)) return;
            if (aaIsCanvasClick(target)) return;

            const active = state.canvas.getActiveObject();

            if (!active) return;

            state.canvas.discardActiveObject();

            if (typeof state.canvas.requestRenderAll === 'function') {
                state.canvas.requestRenderAll();
            } else {
                state.canvas.renderAll();
            }

            syncInspector();
            hideObjectFloatingToolbar();
            hideObjectOverflowOverlay();
            hideCountdownContextToolbar();
            hideInteractionPopover();
            closeToolbarPopovers();
        }

        function isLargeImageSelectionBlocker(object) {
            if (!object || object.type !== 'image' || !state.canvas) return false;
            if (object.customType === 'background' || object.locked === true) return false;

            if (typeof aaIsLargeImageObject === 'function') {
                return aaIsLargeImageObject(object);
            }

            const objectRect = object.getBoundingRect(true, true);
            const canvasArea = Math.max(1, state.canvas.getWidth() * state.canvas.getHeight());
            const objectArea = Math.max(1, objectRect.width * objectRect.height);

            return objectArea >= canvasArea * .18 ||
                objectRect.width >= state.canvas.getWidth() * .62 ||
                objectRect.height >= state.canvas.getHeight() * .62;
        }

        function pickTopSelectableObjectAtPointer(pointer, ignoredObject = null, options = {}) {
            if (!state.canvas || !pointer) return null;
            const point = typeof fabric !== 'undefined' && fabric.Point ?
                new fabric.Point(pointer.x, pointer.y) :
                pointer;
            const objects = state.canvas.getObjects();
            for (let index = objects.length - 1; index >= 0; index -= 1) {
                const object = objects[index];
                if (!object || object === ignoredObject || object === state.cropBox || object.visible === false)
                    continue;
                if (object.selectable === false || object.evented === false) continue;
                if (options.skipLargeImages === true && isLargeImageSelectionBlocker(object)) continue;
                if (
                    typeof object.containsPoint === 'function' &&
                    object.containsPoint(point) &&
                    !aaIsTransparentTargetAtPointer(object, point)
                ) {
                    return object;
                }
            }
            return null;
        }

        function aaIsTransparentTargetAtPointer(object, pointer) {
            if (!state.canvas || !object || !pointer || object.perPixelTargetFind !== true) return false;
            if (typeof state.canvas.isTargetTransparent !== 'function') return false;
            try {
                return state.canvas.isTargetTransparent(object, pointer.x, pointer.y) === true;
            } catch (error) {
                return false;
            }
        }

        function resolveLargeImageClickSelection(event) {
            if (state.isCropping || !state.canvas || !isLargeImageSelectionBlocker(event?.target)) return;
            const pointer = state.canvas.getPointer(event.e, true);
            const topObject = pickTopSelectableObjectAtPointer(pointer, event.target, {
                skipLargeImages: true
            });
            if (!topObject) return;
            event.e?.preventDefault?.();
            state.canvas.discardActiveObject();
            state.canvas.setActiveObject(topObject);
            topObject.setCoords?.();
            state.canvas.requestRenderAll();
            syncInspector();
            syncObjectFloatingToolbar();
            syncObjectOverflowOverlay(topObject);
            syncCountdownContextToolbar();
            syncInteractionPopover();
        }

        function applyStoredObjectLocks(canvas) {
            if (!canvas || !canvas.getObjects) return;
            canvas.getObjects().forEach(object => {
                if (object && (object.customType === 'background' || object.aaImportReference === true)) {
                    object.set({
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
                    });
                    object.setCoords();
                    return;
                }
                if (object && object.locked === true) {
                    setObjectLocked(object, true);
                }
                if (typeof cleanupGuestNameObject === 'function' && isGuestNameObject(object)) {
                    cleanupGuestNameObject(object);
                }
            });
        }

        function aaGetFabricTextSelectionRange(object) {
            if (!isFabricTextObject(object) || object.isEditing !== true) return null;

            const start = Number(object.selectionStart);
            const end = Number(object.selectionEnd);
            if (!Number.isFinite(start) || !Number.isFinite(end) || start === end) return null;

            return {
                start: Math.max(0, Math.min(start, end)),
                end: Math.max(0, Math.max(start, end)),
            };
        }

        function aaHasFabricTextSelection(object = state.canvas?.getActiveObject()) {
            return aaGetFabricTextSelectionRange(object) !== null;
        }

        function aaTextSelectionBaseStyle(object, range) {
            const base = {};
            [
                'fontFamily', 'fontSize', 'fill', 'fontWeight', 'fontStyle', 'underline', 'linethrough',
                'charSpacing', 'lineHeight', 'textAlign'
            ].forEach(key => {
                if (object[key] !== undefined) base[key] = object[key];
            });

            if (typeof object.getSelectionStyles === 'function') {
                const styles = object.getSelectionStyles(range.start, Math.min(range.start + 1, range.end)) || [];
                const firstStyle = styles[0];
                if (firstStyle && typeof firstStyle === 'object') {
                    Object.assign(base, firstStyle);
                }
            }

            return base;
        }

        function aaBuildTextSelectionClipboardObject(object) {
            const range = aaGetFabricTextSelectionRange(object);
            if (!range || typeof object.text !== 'string') return null;

            const selectedText = object.text.slice(range.start, range.end);
            if (selectedText === '') return null;

            const json = object.toObject(serializedObjectProps);
            const center = typeof object.getCenterPoint === 'function' ? object.getCenterPoint() : null;
            const fontSize = Math.max(8, Number(object.fontSize) || 42);
            const estimatedWidth = Math.max(80, Math.min(Number(object.width) || 420, selectedText.length * fontSize * 0.72));

            Object.assign(json, aaTextSelectionBaseStyle(object, range), {
                aaClipboardKind: 'text-selection',
                id: `obj-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                text: selectedText,
                type: object.type === 'text' ? 'i-text' : object.type,
                styles: {},
                width: object.type === 'textbox' ? estimatedWidth : json.width,
                left: center ? center.x : object.left,
                top: center ? center.y : object.top,
                originX: 'center',
                originY: 'center',
            });

            delete json.isGuestName;
            delete json.dynamicKey;
            delete json.templateText;
            delete json.placeholder;

            return json;
        }

        function copyActiveTextSelectionAsObject(object = state.canvas?.getActiveObject()) {
            const json = aaBuildTextSelectionClipboardObject(object);
            if (!json) return false;

            state.clipboardObjectJson = json;
            setStatus('Potongan teks disalin');
            return true;
        }

        function cutActiveTextSelectionAsObject(object = state.canvas?.getActiveObject()) {
            const range = aaGetFabricTextSelectionRange(object);
            if (!range || !copyActiveTextSelectionAsObject(object)) return false;

            const text = String(object.text || '');
            object.set({
                text: text.slice(0, range.start) + text.slice(range.end),
                styles: {},
            });
            object.selectionStart = range.start;
            object.selectionEnd = range.start;
            object.dirty = true;
            if (typeof object.initDimensions === 'function') {
                object.initDimensions();
            }
            object.setCoords();
            state.canvas.requestRenderAll();
            snapshot();
            setStatus('Potongan teks dipotong');
            return true;
        }

        function aaResolveClipboardImageSrc(objectOrJson) {
            if (!objectOrJson) return '';
            const readSrc = typeof readImageSourceUrl === 'function'
                ? readImageSourceUrl(objectOrJson)
                : '';
            return String(
                readSrc ||
                objectOrJson.src ||
                objectOrJson.aaRemovedBgSrc ||
                objectOrJson.aaImageOutlineBaseSrc ||
                objectOrJson.aaOriginalImageSrc ||
                ''
            ).trim();
        }

        function aaPrepareClipboardObjectJson(object) {
            const json = object.toObject(serializedObjectProps);
            if (object?.type !== 'image' && json.type !== 'image') return json;

            const imageSrc = aaResolveClipboardImageSrc(object) || aaResolveClipboardImageSrc(json);
            if (imageSrc) {
                const isRemoveBgLike = object.aaRemovedBg === true ||
                    Boolean(object.aaRemovedBgSrc) ||
                    (typeof isRemoveBgAssetSource === 'function' && isRemoveBgAssetSource(imageSrc));

                json.src = imageSrc;
                json.crossOrigin = json.crossOrigin || 'anonymous';
                json.aaOriginalImageSrc = json.aaOriginalImageSrc || object.aaOriginalImageSrc || imageSrc;
                if (isRemoveBgLike) {
                    json.aaRemovedBg = true;
                    json.aaRemovedBgSrc = json.aaRemovedBgSrc || object.aaRemovedBgSrc || imageSrc;
                    json.aaImageOutlineBaseSrc = json.aaImageOutlineBaseSrc || object.aaImageOutlineBaseSrc || imageSrc;
                    json.aaImageOutlineAlphaEligible = json.aaImageOutlineAlphaEligible !== false;
                }
            }
            json.objectCaching = false;
            return json;
        }

        function copyActiveObject() {
            const active = state.canvas.getActiveObject();
            if (!active || active === state.cropBox) return false;
            if (typeof aaIsProtectedPhotoboothObject === 'function' && aaIsProtectedPhotoboothObject(active)) {
                setStatus('Slot foto Photobooth tidak bisa disalin agar struktur frame tetap aman.', 'error');
                return false;
            }
            if (aaHasFabricTextSelection(active)) {
                return copyActiveTextSelectionAsObject(active);
            }
            if (active.type === 'activeSelection' && typeof active.getObjects === 'function') {
                const objects = active.getObjects().filter(object => !(typeof aaIsProtectedPhotoboothObject === 'function' && aaIsProtectedPhotoboothObject(object)));
                if (!objects.length) {
                    setStatus('Slot foto Photobooth tidak bisa disalin.', 'error');
                    return false;
                }
                state.clipboardObjectJson = {
                    type: 'aa-multi-selection',
                    objects: objects.map(object => {
                        const json = aaPrepareClipboardObjectJson(object);
                        const center = typeof object.getCenterPoint === 'function' ? object.getCenterPoint() : null;
                        if (center) {
                            json.left = center.x;
                            json.top = center.y;
                            json.originX = 'center';
                            json.originY = 'center';
                        }
                        return json;
                    }),
                };
            } else {
                state.clipboardObjectJson = aaPrepareClipboardObjectJson(active);
            }
            setStatus('Object disalin');
            return true;
        }

        function clampObjectToCanvas(object) {
            if (!object || !state.canvas) return;
            const canvasWidth = state.canvas.getWidth() || 1080;
            const canvasHeight = state.canvas.getHeight() || 1920;
            const bounds = object.getBoundingRect ? object.getBoundingRect(true, true) : null;
            const width = Math.max(1, bounds?.width || object.width || 1);
            const height = Math.max(1, bounds?.height || object.height || 1);
            object.set({
                left: Math.max(0, Math.min(object.left || 0, canvasWidth - Math.min(width, canvasWidth) /
                    2)),
                top: Math.max(0, Math.min(object.top || 0, canvasHeight - Math.min(height, canvasHeight) /
                    2)),
            });
            object.setCoords();
        }

        function getObjectsBounds(objects = []) {
            return objects.filter(Boolean).reduce((box, object) => {
                object.setCoords?.();
                const rect = object.getBoundingRect ? object.getBoundingRect(true, true) : {
                    left: object.left || 0,
                    top: object.top || 0,
                    width: object.width || 1,
                    height: object.height || 1,
                };
                const next = {
                    left: rect.left,
                    top: rect.top,
                    right: rect.left + rect.width,
                    bottom: rect.top + rect.height,
                };
                if (!box) return next;
                box.left = Math.min(box.left, next.left);
                box.top = Math.min(box.top, next.top);
                box.right = Math.max(box.right, next.right);
                box.bottom = Math.max(box.bottom, next.bottom);
                return box;
            }, null);
        }

        function translateObjects(objects = [], deltaX = 0, deltaY = 0) {
            if (!deltaX && !deltaY) return;
            objects.forEach(object => {
                object.set({
                    left: (object.left || 0) + deltaX,
                    top: (object.top || 0) + deltaY,
                });
                object.setCoords?.();
            });
        }

        function clampObjectsToCanvas(objects = []) {
            if (!state.canvas || !objects.length) return;
            const canvasWidth = state.canvas.getWidth() || 1080;
            const canvasHeight = state.canvas.getHeight() || 1920;
            const bounds = getObjectsBounds(objects);
            if (!bounds) return;
            const width = bounds.right - bounds.left;
            const height = bounds.bottom - bounds.top;
            let deltaX = 0;
            let deltaY = 0;

            if (width >= canvasWidth) {
                deltaX = -bounds.left;
            } else if (bounds.left < 0) {
                deltaX = -bounds.left;
            } else if (bounds.right > canvasWidth) {
                deltaX = canvasWidth - bounds.right;
            }

            if (height >= canvasHeight) {
                deltaY = -bounds.top;
            } else if (bounds.top < 0) {
                deltaY = -bounds.top;
            } else if (bounds.bottom > canvasHeight) {
                deltaY = canvasHeight - bounds.bottom;
            }

            translateObjects(objects, deltaX, deltaY);
        }

        function aaFinalizePastedObject(clone, pointer = null) {
            clone.set({
                id: `obj-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                left: pointer ? pointer.x : (clone.left || 0) + 34,
                top: pointer ? pointer.y : (clone.top || 0) + 34,
            });
            if (clone.locked === true) {
                setObjectLocked(clone, true);
            }
            if (clone.type === 'image') {
                clone.set({
                    objectCaching: false,
                    crossOrigin: clone.crossOrigin || 'anonymous',
                });
                if (typeof aaApplySafeImageHitTesting === 'function') {
                    aaApplySafeImageHitTesting(clone);
                }
            }
            clampObjectToCanvas(clone);
            state.canvas.add(clone);
            state.canvas.setActiveObject(clone);
            state.canvas.requestRenderAll();
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    snapshot();
                    setStatus('Object ditempel');
                });
            });
        }

        async function aaPasteClipboardImageObject(objectJson, pointer = null) {
            const imageSrc = aaResolveClipboardImageSrc(objectJson);
            if (!imageSrc || typeof loadFabricImageWithRetry !== 'function') return false;

            try {
                const clone = await loadFabricImageWithRetry(imageSrc, {
                    attempts: 3,
                    delay: 450,
                });
                if (!clone) return false;

                const props = {
                    ...objectJson,
                };
                delete props.type;
                delete props.version;
                delete props.src;
                delete props.clipPath;
                clone.set(props);
                clone.set({
                    src: imageSrc,
                    objectCaching: false,
                    crossOrigin: props.crossOrigin || 'anonymous',
                });
                aaFinalizePastedObject(clone, pointer);
                return true;
            } catch (error) {
                console.warn('[AdaAcara Editor] Gagal paste image object:', error);
                setStatus('Gagal menempel object gambar');
                return false;
            }
        }

        function pasteClipboardObject(pointer = null) {
            if (!state.clipboardObjectJson || !state.canvas || !window.fabric) return false;
            const objectJson = JSON.parse(JSON.stringify(state.clipboardObjectJson));
            delete objectJson.aaClipboardKind;
            if (objectJson.type === 'aa-multi-selection' && Array.isArray(objectJson.objects)) {
                fabric.util.enlivenObjects(objectJson.objects, objects => {
                    const validObjects = objects.filter(Boolean);
                    if (!validObjects.length) return;
                    const offsetX = pointer ? 0 : 34;
                    const offsetY = pointer ? 0 : 34;
                    const bounds = getObjectsBounds(validObjects);
                    const moveX = pointer && bounds ? pointer.x - ((bounds.left + bounds.right) / 2) : offsetX;
                    const moveY = pointer && bounds ? pointer.y - ((bounds.top + bounds.bottom) / 2) : offsetY;
                    validObjects.forEach(object => {
                        object.set({
                            id: `obj-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                            left: (object.left || 0) + moveX,
                            top: (object.top || 0) + moveY,
                        });
                        if (object.locked === true) {
                            setObjectLocked(object, true);
                        }
                        object.setCoords();
                        state.canvas.add(object);
                    });
                    clampObjectsToCanvas(validObjects);
                    const selection = new fabric.ActiveSelection(validObjects, {
                        canvas: state.canvas,
                    });
                    state.canvas.setActiveObject(selection);
                    state.canvas.requestRenderAll();
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () {
                            snapshot();
                            setStatus(`${validObjects.length} object ditempel`);
                        });
                    });
                });
                return true;
            }
            if (objectJson.type === 'image') {
                aaPasteClipboardImageObject(objectJson, pointer);
                return true;
            }
            fabric.util.enlivenObjects([objectJson], objects => {
                const clone = objects[0];
                if (!clone) return;
                aaFinalizePastedObject(clone, pointer);
            });
            return true;
        }

        function aaDuplicateActiveUsingCopyPaste() {
    if (!state.canvas || !window.fabric) return false;

    const active = state.canvas.getActiveObject();

    if (!active || active === state.cropBox) return false;

    const previousClipboard = state.clipboardObjectJson
        ? JSON.parse(JSON.stringify(state.clipboardObjectJson))
        : null;

    const copied = copyActiveObject();

    if (!copied || !state.clipboardObjectJson) {
        return false;
    }

    pasteClipboardObject(null);

    // Jangan rusak clipboard user.
    // Setelah paste async selesai, balikin clipboard lama.
    window.setTimeout(function () {
        state.clipboardObjectJson = previousClipboard;
    }, 80);

    return true;
}

        function canGroupObject(object = state.canvas?.getActiveObject()) {
            return Boolean(object && object !== state.cropBox && object.type === 'activeSelection' &&
                typeof object.getObjects === 'function' && object.getObjects().length > 1 &&
                !(typeof aaIsProtectedPhotoboothObject === 'function' && aaIsProtectedPhotoboothObject(object)));
        }

        function canUngroupObject(object = state.canvas?.getActiveObject()) {
            return Boolean(object && object !== state.cropBox && object.type === 'group' &&
                typeof object.toActiveSelection === 'function');
        }

        function groupActiveSelection() {
            const active = state.canvas?.getActiveObject();
            if (!canGroupObject(active) || typeof active.toGroup !== 'function') return false;
            const group = active.toGroup();
            group.set({
                id: `obj-${Date.now()}-${Math.random().toString(16).slice(2)}`,
            });
            group.setCoords();
            state.canvas.setActiveObject(group);
            state.canvas.requestRenderAll();
            syncInspector();
            storeCurrentPage();
            snapshot();
            setStatus('Object digroup');
            return true;
        }

        function ungroupActiveGroup() {
            const active = state.canvas?.getActiveObject();
            if (!canUngroupObject(active)) return false;
            active.toActiveSelection();
            state.canvas.requestRenderAll();
            syncInspector();
            storeCurrentPage();
            snapshot();
            setStatus('Group dipisahkan');
            return true;
        }

        function closeObjectContextMenu() {
            if (!els.aaObjectContextMenu) return;
            els.aaObjectContextMenu.classList.remove('is-open');
            state.contextMenuTarget = null;
            state.contextMenuPointer = null;
        }

        function ensureObjectContextGroupActions() {
            const menu = els.aaObjectContextMenu;
            if (!menu || menu.querySelector('[data-aa-context-action="group"]')) return;

            const separator = document.createElement('hr');
            separator.dataset.aaGroupActionsSeparator = '1';

            const groupButton = document.createElement('button');
            groupButton.type = 'button';
            groupButton.dataset.aaContextAction = 'group';
            groupButton.setAttribute('role', 'menuitem');
            groupButton.innerHTML = '<i class="fa fa-object-group"></i><span>Group</span>';

            const ungroupButton = document.createElement('button');
            ungroupButton.type = 'button';
            ungroupButton.dataset.aaContextAction = 'ungroup';
            ungroupButton.setAttribute('role', 'menuitem');
            ungroupButton.innerHTML = '<i class="fa fa-object-ungroup"></i><span>Ungroup</span>';

            const lockButton = menu.querySelector('[data-aa-context-action="lock-toggle"]');
            if (lockButton) {
                menu.insertBefore(separator, lockButton);
                menu.insertBefore(groupButton, lockButton);
                menu.insertBefore(ungroupButton, lockButton);
                const lockSeparator = document.createElement('hr');
                menu.insertBefore(lockSeparator, lockButton);
                return;
            }

            menu.append(separator, groupButton, ungroupButton);
        }

        function resolveContextMenuTarget(target = null) {
            const active = state.canvas?.getActiveObject();
            if (!active || active === state.cropBox) return target;
            if (!target || target === active) return active;
            if (active.type === 'activeSelection' && typeof active.getObjects === 'function') {
                const activeObjects = active.getObjects();
                if (activeObjects.includes(target) || (typeof active.contains === 'function' && active.contains(target))) {
                    return active;
                }
            }
            return target;
        }

        function openObjectContextMenu(event, target = null, pointer = null) {
            if (!els.aaObjectContextMenu) return;
            closeToolbarPopovers('object-context');
            ensureObjectContextGroupActions();
            target = resolveContextMenuTarget(target);
            state.contextMenuTarget = target || null;
            state.contextMenuPointer = pointer || null;

            const hasTarget = Boolean(target);
            const protectedPhotobooth = typeof aaIsProtectedPhotoboothObject === 'function' && aaIsProtectedPhotoboothObject(target);
            els.aaObjectContextMenu.querySelectorAll('[data-aa-context-action]').forEach(button => {
                const action = button.dataset.aaContextAction;
                const needsTarget = action !== 'paste';
                button.disabled = (needsTarget && !hasTarget) || (action === 'paste' && !state
                    .clipboardObjectJson);
                if (protectedPhotobooth && ['copy', 'duplicate', 'delete', 'group', 'ungroup', 'lock-toggle'].includes(action)) {
                    button.disabled = true;
                }
                if (action === 'group') {
                    button.disabled = !canGroupObject(target);
                }
                if (action === 'ungroup') {
                    button.disabled = !canUngroupObject(target);
                }
                if (action === 'lock-toggle') {
                    const locked = hasTarget && target.locked === true;
                    button.innerHTML = locked ?
                        '<i class="fa fa-lock-open"></i><span>Unlock object</span>' :
                        '<i class="fa fa-lock"></i><span>Lock object</span>';
                }
            });

            els.aaObjectContextMenu.classList.add('is-open');
            const menuRect = els.aaObjectContextMenu.getBoundingClientRect();
            const left = Math.min(event.clientX, window.innerWidth - menuRect.width - 12);
            const top = Math.min(event.clientY, window.innerHeight - menuRect.height - 12);
            els.aaObjectContextMenu.style.left = `${Math.max(12, left)}px`;
            els.aaObjectContextMenu.style.top = `${Math.max(12, top)}px`;
        }

        function runObjectContextAction(action) {
            const active = state.contextMenuTarget || state.canvas.getActiveObject();
            if (active && active !== state.canvas.getActiveObject()) {
                state.canvas.setActiveObject(active);
            }

            if (action === 'paste') {
                pasteClipboardObject(state.contextMenuPointer);
                closeObjectContextMenu();
                return;
            }

            if (!active || active === state.cropBox) {
                closeObjectContextMenu();
                return;
            }

            const protectedPhotobooth = typeof aaIsProtectedPhotoboothObject === 'function' && aaIsProtectedPhotoboothObject(active);
            if (protectedPhotobooth && ['copy', 'duplicate', 'delete', 'group', 'ungroup', 'lock-toggle'].includes(action)) {
                setStatus('Slot foto Photobooth dilindungi agar frame tetap valid.', 'error');
                closeObjectContextMenu();
                return;
            }

            if (action === 'bring-front') {
                state.canvas.bringToFront(active);
            } else if (action === 'bring-forward') {
                state.canvas.bringForward(active);
            } else if (action === 'send-backward') {
                state.canvas.sendBackwards(active);
            } else if (action === 'send-back') {
                state.canvas.sendToBack(active);
            } else if (action === 'duplicate') {
            const duplicateTarget = active;

            if (duplicateTarget && duplicateTarget !== state.canvas.getActiveObject()) {
                state.canvas.setActiveObject(duplicateTarget);
                state.canvas.requestRenderAll();
            }

            closeObjectContextMenu();

            window.setTimeout(function () {
                aaDuplicateActiveUsingCopyPaste();
            }, 0);

            return;
            } else if (action === 'copy') {
                copyActiveObject();
                closeObjectContextMenu();
                return;
            } else if (action === 'group') {
                groupActiveSelection();
                closeObjectContextMenu();
                return;
            } else if (action === 'ungroup') {
                ungroupActiveGroup();
                closeObjectContextMenu();
                return;
            } else if (action === 'lock-toggle') {
                setObjectLocked(active, active.locked !== true);
            } else if (action === 'delete') {
                closeObjectContextMenu();
                deleteActive();
                return;
            }

            state.canvas.requestRenderAll();
            storeCurrentPage();
            snapshot();
            closeObjectContextMenu();
        }

        function bindObjectContextMenu() {
            if (!state.canvas?.upperCanvasEl) return;
            state.canvas.upperCanvasEl.addEventListener('contextmenu', event => {
                event.preventDefault();
                event.stopPropagation();
                if (state.isCropping) return;
                const target = state.canvas.findTarget(event, false);
                const pointer = state.canvas.getPointer(event);
                const contextTarget = resolveContextMenuTarget(target);
                if (contextTarget && contextTarget !== state.cropBox) {
                    state.canvas.setActiveObject(contextTarget);
                    state.canvas.requestRenderAll();
                    syncInspector();
                    openObjectContextMenu(event, contextTarget, pointer);
                    return;
                }
                state.canvas.discardActiveObject();
                state.canvas.requestRenderAll();
                openObjectContextMenu(event, null, pointer);
            });
        }

        function aaIsTypingTarget(target) {
    if (!target) return false;

    const tag = String(target.tagName || '').toLowerCase();

    return (
        tag === 'input' ||
        tag === 'textarea' ||
        tag === 'select' ||
        target.isContentEditable === true ||
        target.closest?.('[contenteditable="true"]')
    );
}

function aaIsEditorShortcutAllowed(event) {
    if (!state.canvas) return false;
    if (state.isCropping) return false;
    if (state.canvas.getActiveObject?.()?.isEditing === true) return false;

    const target = event.target;

    // Jangan ganggu saat user sedang mengetik input/panel/form
    if (aaIsTypingTarget(target)) return false;

    // Shortcut hanya aktif kalau sedang fokus di area editor/canvas
    return Boolean(
        target?.closest?.('.canvas-container') ||
        target?.closest?.('.upper-canvas') ||
        target?.closest?.('.lower-canvas') ||
        target?.closest?.('#aaFabricCanvas') ||
        target?.closest?.('.aa-artboard-frame') ||
        document.activeElement?.closest?.('.canvas-container') ||
        state.canvas.getActiveObject()
    );
}

function aaSelectAllObjectsOnActivePage() {
    if (!state.canvas || !window.fabric) return false;

    const selectableObjects = state.canvas.getObjects().filter(object => {
        if (!object) return false;
        if (object === state.cropBox) return false;
        if (object.visible === false) return false;
        if (object.customType === 'background') return false;
        if (object.selectable === false) return false;
        if (object.evented === false) return false;
        return true;
    });

    if (!selectableObjects.length) {
        state.canvas.discardActiveObject();
        state.canvas.requestRenderAll();
        return false;
    }

    if (selectableObjects.length === 1) {
        state.canvas.setActiveObject(selectableObjects[0]);
    } else {
        const selection = new fabric.ActiveSelection(selectableObjects, {
            canvas: state.canvas,
        });

        state.canvas.setActiveObject(selection);
    }

    state.canvas.requestRenderAll();

    syncInspector?.();
    syncObjectFloatingToolbar?.();
    closeToolbarPopovers?.();

    setStatus?.(`${selectableObjects.length} object dipilih di halaman aktif`);

    return true;
}

function bindAaSelectAllShortcut() {
    if (state.__aaSelectAllShortcutBound) return;
    state.__aaSelectAllShortcutBound = true;

    document.addEventListener('keydown', function (event) {
        const key = String(event.key || '').toLowerCase();
        const isSelectAll = (event.ctrlKey || event.metaKey) && key === 'a';

        if (!isSelectAll) return;
        if (!aaIsEditorShortcutAllowed(event)) return;

        event.preventDefault();
        event.stopPropagation();

        aaSelectAllObjectsOnActivePage();
    }, true);
}
        function drawHoverHighlight() {
            const target = state.hoverTarget;
            if (!target || target === state.cropBox || !state.canvas || state.isCropping) return;
            if (state.objectTransformOverlayDrag || state.canvas._currentTransform || state.selectionTransformUiHidden) return;
            const active = state.canvas.getActiveObject?.();
            if (active === target) return;
            if (active?.type === 'activeSelection' && typeof active.getObjects === 'function' && active.getObjects().includes(target)) return;
            const ctx = state.canvas.contextContainer;
            const rect = target.getBoundingRect(true, true);
            ctx.save();
            ctx.strokeStyle = 'rgba(124, 58, 237, .72)';
            ctx.lineWidth = 4;
            ctx.setLineDash([]);
            ctx.strokeRect(rect.left - 2, rect.top - 2, rect.width + 4, rect.height + 4);
            ctx.restore();
        }

        function aaEnsureSmartGuideState() {
    if (!state.aaSmartGuides) {
        state.aaSmartGuides = {
            enabled: true,
            threshold: 6,
            lines: [],
            color: 'rgba(14, 165, 233, .95)',
            canvasColor: 'rgba(168, 85, 247, .95)',
            lineWidth: 2.5,
            haloWidth: 6,
            haloColor: 'rgba(255, 255, 255, .72)',
            dash: [10, 5],
        };
    }

    return state.aaSmartGuides;
}

function aaObjectSnapRect(object) {
    if (!object || !state.canvas) return null;

    object.setCoords?.();

    const rect = object.getBoundingRect ? object.getBoundingRect(true, true) : {
        left: object.left || 0,
        top: object.top || 0,
        width: object.width || 1,
        height: object.height || 1,
    };

    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    return {
        left: rect.left,
        centerX,
        right: rect.left + rect.width,

        top: rect.top,
        centerY,
        bottom: rect.top + rect.height,

        width: rect.width,
        height: rect.height,
    };
}

function aaCanUseObjectForSmartGuide(object, active) {
    if (!object || object === active) return false;
    if (object === state.cropBox) return false;
    if (object.visible === false) return false;
    if (object.customType === 'background') return false;
    if (object.excludeFromSmartGuide === true) return false;

    if (active?.type === 'activeSelection' && typeof active.getObjects === 'function') {
        if (active.getObjects().includes(object)) return false;
    }

    return true;
}

function aaAddSmartGuideLine(type, value, from, to, isCanvasGuide = false) {
    const guides = aaEnsureSmartGuideState();

    guides.lines.push({
        type,
        value,
        from,
        to,
        isCanvasGuide,
    });
}

function aaClearSmartGuides(render = true) {
    const guides = aaEnsureSmartGuideState();
    guides.lines = [];

    if (render && state.canvas) {
        state.canvas.requestRenderAll?.();
    }
}

function aaApplySmartGuideSnap(active) {
    const guides = aaEnsureSmartGuideState();

    if (!guides.enabled || !state.canvas || !active || active === state.cropBox || state.isCropping) return;
    if (active.locked === true) return;

    const activeRect = aaObjectSnapRect(active);
    if (!activeRect) return;

    const threshold = Number(guides.threshold) || 8;
    const canvasWidth = state.canvas.getWidth() || 1080;
    const canvasHeight = state.canvas.getHeight() || 1920;

    guides.lines = [];

    let bestX = null;
    let bestY = null;

    const activeXPoints = [
        { name: 'left', value: activeRect.left },
        { name: 'centerX', value: activeRect.centerX },
        { name: 'right', value: activeRect.right },
    ];

    const activeYPoints = [
        { name: 'top', value: activeRect.top },
        { name: 'centerY', value: activeRect.centerY },
        { name: 'bottom', value: activeRect.bottom },
    ];

    function checkX(targetValue, activePoint, lineFrom, lineTo, isCanvasGuide = false) {
        const diff = targetValue - activePoint.value;
        const distance = Math.abs(diff);

        if (distance <= threshold && (!bestX || distance < bestX.distance)) {
            bestX = {
                diff,
                distance,
                lineValue: targetValue,
                from: lineFrom,
                to: lineTo,
                isCanvasGuide,
            };
        }
    }

    function checkY(targetValue, activePoint, lineFrom, lineTo, isCanvasGuide = false) {
        const diff = targetValue - activePoint.value;
        const distance = Math.abs(diff);

        if (distance <= threshold && (!bestY || distance < bestY.distance)) {
            bestY = {
                diff,
                distance,
                lineValue: targetValue,
                from: lineFrom,
                to: lineTo,
                isCanvasGuide,
            };
        }
    }

    // Snap ke canvas: kiri, tengah, kanan
    const canvasXTargets = [0, canvasWidth / 2, canvasWidth];
    const canvasYTargets = [0, canvasHeight / 2, canvasHeight];

    activeXPoints.forEach(point => {
        canvasXTargets.forEach(targetX => {
            checkX(targetX, point, 0, canvasHeight, true);
        });
    });

    activeYPoints.forEach(point => {
        canvasYTargets.forEach(targetY => {
            checkY(targetY, point, 0, canvasWidth, true);
        });
    });

    // Snap ke object lain
    state.canvas.getObjects().forEach(object => {
        if (!aaCanUseObjectForSmartGuide(object, active)) return;

        const targetRect = aaObjectSnapRect(object);
        if (!targetRect) return;

        const targetXPoints = [
            targetRect.left,
            targetRect.centerX,
            targetRect.right,
        ];

        const targetYPoints = [
            targetRect.top,
            targetRect.centerY,
            targetRect.bottom,
        ];

        activeXPoints.forEach(activePoint => {
            targetXPoints.forEach(targetX => {
                checkX(
                    targetX,
                    activePoint,
                    Math.min(activeRect.top, targetRect.top) - 80,
                    Math.max(activeRect.bottom, targetRect.bottom) + 80,
                    false
                );
            });
        });

        activeYPoints.forEach(activePoint => {
            targetYPoints.forEach(targetY => {
                checkY(
                    targetY,
                    activePoint,
                    Math.min(activeRect.left, targetRect.left) - 80,
                    Math.max(activeRect.right, targetRect.right) + 80,
                    false
                );
            });
        });
    });

    if (bestX) {
        active.set({
            left: (active.left || 0) + bestX.diff,
        });

        aaAddSmartGuideLine(
            'vertical',
            bestX.lineValue,
            bestX.from,
            bestX.to,
            bestX.isCanvasGuide
        );
    }

    if (bestY) {
        active.set({
            top: (active.top || 0) + bestY.diff,
        });

        aaAddSmartGuideLine(
            'horizontal',
            bestY.lineValue,
            bestY.from,
            bestY.to,
            bestY.isCanvasGuide
        );
    }

    if (bestX || bestY) {
        active.setCoords();
    }
}

function aaRememberActiveObjectForEditorUi(object) {
    if (!state || !state.canvas) return null;

    const target = object || state.canvas.getActiveObject?.();

    if (!target || target === state.cropBox) {
        state.aaLastEditorUiObject = null;
        return null;
    }

    state.aaLastEditorUiObject = target;
    return target;
}

function aaGetRememberedActiveObjectForEditorUi() {
    if (!state || !state.canvas) return null;

    const remembered = state.aaLastEditorUiObject;
    const active = state.canvas.getActiveObject?.();

    if (active && active !== state.cropBox) {
        return active;
    }

    if (
        remembered &&
        remembered !== state.cropBox &&
        state.canvas.getObjects?.().includes(remembered)
    ) {
        return remembered;
    }

    state.aaLastEditorUiObject = null;
    return null;
}

	function aaDrawSmartGuides() {
	    const guides = aaEnsureSmartGuideState();

    if (!guides.enabled || !state.canvas || !guides.lines.length) return;

    const ctx = state.canvas.contextContainer;
    const canvasWidth = state.canvas.getWidth() || 1080;
    const canvasHeight = state.canvas.getHeight() || 1920;
    const lineWidth = Math.max(1.5, Number(guides.lineWidth) || 2.5);
    const haloWidth = Math.max(lineWidth + 2, Number(guides.haloWidth) || 6);
    const dash = Array.isArray(guides.dash) && guides.dash.length ? guides.dash : [10, 5];

    function beginGuidePath(line) {
        ctx.beginPath();

        if (line.type === 'vertical') {
            const x = Math.round(line.value) + 0.5;
            ctx.moveTo(x, Math.max(0, line.from ?? 0));
            ctx.lineTo(x, Math.min(canvasHeight, line.to ?? canvasHeight));
            return true;
        }

        if (line.type === 'horizontal') {
            const y = Math.round(line.value) + 0.5;
            ctx.moveTo(Math.max(0, line.from ?? 0), y);
            ctx.lineTo(Math.min(canvasWidth, line.to ?? canvasWidth), y);
            return true;
        }

        return false;
    }

    ctx.save();
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.setLineDash(dash);

    guides.lines.forEach(line => {
        if (!beginGuidePath(line)) return;

        ctx.strokeStyle = guides.haloColor || 'rgba(255, 255, 255, .72)';
        ctx.lineWidth = haloWidth;
        ctx.stroke();

        ctx.strokeStyle = line.isCanvasGuide ? guides.canvasColor : guides.color;
        ctx.lineWidth = lineWidth;
        ctx.stroke();
    });

	    ctx.restore();
	}

	    function bindSmartGuides() {
	        if (!state.canvas || state.canvas.__aaSmartGuidesBound) return;

        state.canvas.__aaSmartGuidesBound = true;
        aaEnsureSmartGuideState();

        state.canvas.on('mouse:down', function() {
            aaBeginNativeSelectionTransformUi();
        });

        state.canvas.on('mouse:move', function() {
            aaBeginNativeSelectionTransformUi();
        });

        state.canvas.on('object:moving', function (event) {
            const target = event.target;

            aaBeginNativeSelectionTransformUi(target);

            if (state.cropPanTarget && target === state.cropPanTarget) return;
            if (!target || target === state.cropBox || state.isCropping) return;
            if (target.locked === true) return;

            aaApplySmartGuideSnap(target);
            state.canvas.requestRenderAll();
        });

        state.canvas.on('object:scaling', function(event) {
            aaBeginNativeSelectionTransformUi(event.target);
        });

        state.canvas.on('object:rotating', function(event) {
            aaBeginNativeSelectionTransformUi(event.target);
        });

	        state.canvas.on('object:modified', function (event) {
	            aaClearSmartGuides(false);
	            aaEndSelectionTransformUi();
	            syncObjectFloatingToolbar();
	            syncObjectOverflowOverlay(event.target);
	            state.canvas.requestRenderAll();
	        });

	        state.canvas.on('mouse:up', function () {
	            aaClearSmartGuides(false);
	            aaEndSelectionTransformUi();
	            syncObjectFloatingToolbar();
	            syncObjectOverflowOverlay();
	            state.canvas.requestRenderAll();
	        });

        state.canvas.on('selection:cleared', function () {
            aaClearSmartGuides(false);
            state.canvas.requestRenderAll();
        });

	        state.canvas.on('after:render', function () {
	            aaDrawSmartGuides();
	            syncObjectOverflowOverlay();
	        });
	    }
        function getActiveObjectColor(object) {
            if (!object) return '#8b5a3c';
            if (object.type === 'line' && object.stroke) return normalizeColor(object.stroke);
            if (object.fill && typeof object.fill === 'string') return normalizeColor(object.fill);
            if (object.controlBackground) return normalizeColor(object.controlBackground);
            if (object.type === 'image') return '#8b5a3c';
            const text = isGuestNameObject(object) ? getGuestNameTextObject(object) : getNamedGroupText(object);
            if (text?.fill) return normalizeColor(text.fill);
            const box = getInteractiveBox(object);
            if (box?.fill) return normalizeColor(box.fill);
            return '#8b5a3c';
        }

	        function getContextTextTarget(object = state.canvas?.getActiveObject()) {
	            if (!object || object === state.cropBox) return null;
	            if (isGuestNameObject(object)) return getGuestNameTextObject(object);
	            if ((object.customType === 'social-link' || object.customType === 'social-media') &&
	                typeof object.getObjects === 'function') {
	                const children = object.getObjects();
	                if (object.customType === 'social-link') {
	                    return children.find(child => child.name === 'social-label') ||
	                        children.find(child => isFabricTextObject(child)) ||
	                        null;
	                }
	                return children.find(child => child.name === 'interactive-title' || child.name === 'social-title' ||
	                        child.name === 'title') ||
	                    children.find(child => isFabricTextObject(child)) ||
	                    null;
	            }
	            if (isFabricTextObject(object)) return object;
	            return null;
	        }

        function isContextTextObject(object = state.canvas?.getActiveObject()) {
            return Boolean(getContextTextTarget(object));
        }

        function isSpecialContextToolbarObject(object = state.canvas?.getActiveObject()) {
            return Boolean(object && object !== state.cropBox && !isContextTextObject(object) && (
                isInteractiveObject(object) ||
                isGuestbookObject(object)
            ));
        }

        function syncContextToolbar(active = state.canvas?.getActiveObject()) {
            if (!els.aaContextToolbar) return;
            const isPhotoboothSlot = typeof aaIsPhotoboothPhotoSlot === 'function' && aaIsPhotoboothPhotoSlot(active);
            const visible = Boolean(active && active !== state.cropBox && !state.isCropping && !isContextTextObject(
                    active) &&
                active.customType !== 'opening-button' &&
                !isPhotoboothSlot);
            els.aaContextToolbar.classList.toggle('is-visible', visible);
            els.aaContextToolbar.classList.remove('is-animation-only');
            if (!visible) {
                closeContextFlipPopover();
                closeContextStrokePopover();
                closeContextRadiusPopover();
                closeImageOutlinePopover();
                closeContextTransparencyPopover();
            }
            syncContextStrokeControl(active);
            syncContextRadiusControl(active);
            syncContextTransparencyControl(active);
            syncImageOutlineControl(active);
            syncImageEffectButtons(active);
            syncImageOverlayButtons(active);
            syncImageFrameButtons(active);
            if (!visible) return;

            const hideGlobalColorTool = isSpecialContextToolbarObject(active) || active.type === 'image';
            const hideGlobalOpacityTool = isSpecialContextToolbarObject(active);
            if (hideGlobalOpacityTool) {
                closeContextTransparencyPopover();
            }

            if (els.aaContextColorBtn) {
                els.aaContextColorBtn.hidden = hideGlobalColorTool;
                els.aaContextColorBtn.disabled = hideGlobalColorTool;
                if (hideGlobalColorTool) {
                    els.aaContextColorBtn.setAttribute('aria-hidden', 'true');
                } else {
                    els.aaContextColorBtn.removeAttribute('aria-hidden');
                }
            }
            if (els.aaContextColorSwatch) {
                els.aaContextColorSwatch.style.background = getActiveObjectColor(active);
            }
            if (els.aaContextColorInput) {
                els.aaContextColorInput.value = getActiveObjectColor(active);
            }
            if (els.aaContextRadiusBtn) {
                els.aaContextRadiusBtn.hidden = active.type !== 'image';
                els.aaContextRadiusBtn.disabled = active.type !== 'image';
            }
            if (els.aaContextStrokeBtn) {
                els.aaContextStrokeBtn.hidden = active.type !== 'image';
                els.aaContextStrokeBtn.disabled = active.type !== 'image';
            }
            if (els.aaContextCropBtn) {
                els.aaContextCropBtn.disabled = active.type !== 'image';
            }
            if (els.aaContextRemoveBgBtn) {
                els.aaContextRemoveBgBtn.hidden = active.type !== 'image';
                els.aaContextRemoveBgBtn.disabled = active.type !== 'image' || active.__aaBgRemoveProcessing === true;
                els.aaContextRemoveBgBtn.classList.toggle('is-loading', active.__aaBgRemoveProcessing === true);
            }
            if (els.aaContextMagicLayerBtn) {
                const magicLayerUnavailable = !config.magicLayerEnabled || !config.mediaMagicLayerUrl;
                els.aaContextMagicLayerBtn.hidden = active.type !== 'image' || magicLayerUnavailable;
                els.aaContextMagicLayerBtn.disabled = active.type !== 'image' || magicLayerUnavailable || active.__aaMagicLayerProcessing === true;
                els.aaContextMagicLayerBtn.classList.toggle('is-loading', active.__aaMagicLayerProcessing === true);
            }
            if (els.aaContextImageOutlineBtn) {
                const outlineCandidate = typeof isImageOutlineCandidate === 'function' ?
                    isImageOutlineCandidate(active) :
                    active.type === 'image';
                const canOutline = outlineCandidate && (typeof isImageOutlineTarget === 'function' ?
                    isImageOutlineTarget(active) :
                    active.type === 'image');
                els.aaContextImageOutlineBtn.hidden = !canOutline;
                els.aaContextImageOutlineBtn.disabled = !canOutline || active.__aaOutlineProcessing === true;
                els.aaContextImageOutlineBtn.classList.toggle('is-loading', active.__aaOutlineProcessing === true);
                if (outlineCandidate && typeof syncImageOutlineAvailability === 'function') {
                    syncImageOutlineAvailability(active);
                }
            }
            if (els.aaContextImageEffectsBtn) {
                els.aaContextImageEffectsBtn.hidden = active.type !== 'image';
                els.aaContextImageEffectsBtn.disabled = active.type !== 'image';
            }
            if (els.aaContextImageFrameBtn) {
                els.aaContextImageFrameBtn.hidden = active.type !== 'image';
                els.aaContextImageFrameBtn.disabled = active.type !== 'image';
            }
            if (els.aaContextFlipBtn) {
                els.aaContextFlipBtn.disabled = active.locked === true && active.type !== 'image';
            }
            if (els.aaContextOpacityBtn) {
                els.aaContextOpacityBtn.hidden = hideGlobalOpacityTool;
                els.aaContextOpacityBtn.disabled = hideGlobalOpacityTool || (active.locked === true && active.type !== 'image');
                if (hideGlobalOpacityTool) {
                    els.aaContextOpacityBtn.setAttribute('aria-hidden', 'true');
                } else {
                    els.aaContextOpacityBtn.removeAttribute('aria-hidden');
                }
            }
        }

        function isLockedImageObject(object = state.canvas?.getActiveObject()) {
            return Boolean(object && object.type === 'image' && object.locked === true);
        }

        function showLockedImageNotice(actionText = 'mengubah gambar') {
            showEditorToast(`Gambar terkunci. Unlock dulu untuk ${actionText}.`, 'error', 'Gambar terkunci');
            clearTimeout(state.toastTimer);
            state.toastTimer = setTimeout(() => els.aaEditorToast?.classList.remove('is-visible'), 2000);
        }

        function guardLockedImageAction(actionText = 'mengubah gambar', object = state.canvas?.getActiveObject()) {
            if (!isLockedImageObject(object)) return false;
            showLockedImageNotice(actionText);
            return true;
        }

        function getImageStrokeStyle(image) {
            const width = Math.max(0, Number(image?.strokeWidth) || 0);
            if (!width || !image?.stroke || image.stroke === 'transparent') return 'none';
            if (['solid', 'dashed', 'dotted'].includes(image.imageStrokeStyle)) {
                return image.imageStrokeStyle;
            }
            const dash = Array.isArray(image?.strokeDashArray) ? image.strokeDashArray.join(',') : '';
            if (dash) return dash.includes('0,') ? 'dotted' : 'dashed';
            return 'solid';
        }

        function getImageStrokeDash(style, strokeWidth) {
            const width = Math.max(1, Number(strokeWidth) || 1);
            if (style === 'dashed') {
                return [Math.max(8, width * 2.8), Math.max(6, width * 1.6)];
            }
            if (style === 'dotted') {
                return [0, Math.max(4, width * 1.9)];
            }
            return null;
        }

        function syncContextStrokeControl(active = state.canvas?.getActiveObject()) {
            const isImage = Boolean(active && active.type === 'image' && active !== state.cropBox);
            if (!isImage) {
                closeContextStrokePopover();
                return;
            }
            const width = Math.max(0, Math.round(Number(active.strokeWidth) || 0));
            if (els.aaContextStrokeInput) {
                els.aaContextStrokeInput.value = width;
            }
            if (els.aaContextStrokeValue) {
                els.aaContextStrokeValue.value = width;
                els.aaContextStrokeValue.textContent = width;
            }
            if (els.aaContextStrokeColorInput) {
                els.aaContextStrokeColorInput.value = normalizeColor(active.stroke && active.stroke !==
                    'transparent' ? active.stroke : '#111827');
            }
            const style = getImageStrokeStyle(active);
            document.querySelectorAll('[data-aa-stroke-style]').forEach(button => {
                button.classList.toggle('is-active', button.dataset.aaStrokeStyle === style);
            });
        }

        function positionContextStrokePopover() {
            if (!els.aaContextStrokePopover || !els.aaContextStrokeBtn) return;
            const rect = els.aaContextStrokeBtn.getBoundingClientRect();
            const popoverWidth = els.aaContextStrokePopover.offsetWidth || 256;
            const left = Math.min(window.innerWidth - popoverWidth / 2 - 12, Math.max(popoverWidth / 2 + 12,
                rect.left + rect.width / 2));
            els.aaContextStrokePopover.style.left = `${left}px`;
            els.aaContextStrokePopover.style.top = `${rect.bottom + 8}px`;
        }

        function openContextStrokePopover() {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah stroke', active)) return;
            state.contextImageTarget = active;
            syncContextStrokeControl(active);
            closeToolbarPopovers('stroke');
            els.aaContextStrokePopover?.classList.add('is-open');
            requestAnimationFrame(positionContextStrokePopover);
        }

        function closeContextStrokePopover() {
            els.aaContextStrokePopover?.classList.remove('is-open');
            state.contextImageTarget = null;
        }

        function toggleContextStrokePopover() {
            if (els.aaContextStrokePopover?.classList.contains('is-open')) {
                closeContextStrokePopover();
                return;
            }
            openContextStrokePopover();
        }

        function syncImageOutlineControl(active = state.canvas?.getActiveObject()) {
            const isTarget = typeof isImageOutlineTarget === 'function' ?
                isImageOutlineTarget(active) :
                Boolean(active && active.type === 'image' && active !== state.cropBox);
            if (!isTarget) {
                closeImageOutlinePopover();
                if (typeof getActiveLeftDrawerPanelKey === 'function' &&
                    getActiveLeftDrawerPanelKey() === 'image-outline' &&
                    state?.keepImageOutlineDrawerOpen !== true &&
                    state?.imageProcessTarget == null &&
                    state?.imageOutlineApplyTimer == null &&
                    state?.pendingImageOutlineOptions == null &&
                    typeof closeLeftDrawerPanel === 'function') {
                    closeLeftDrawerPanel();
                }
                return;
            }
            const inputWidth = Math.max(0, Math.min(60, Math.round(Number(active.aaImageOutlineDraftWidth ?? active.aaImageOutlineWidth) || 0)));
            if (els.aaContextImageOutlineColorInput) {
                els.aaContextImageOutlineColorInput.value = normalizeColor(active.aaImageOutlineDraftColor || active.aaImageOutlineColor || '#ffffff');
            }
            if (els.aaContextImageOutlineWidthInput) {
                els.aaContextImageOutlineWidthInput.value = Math.max(1, inputWidth);
            }
            if (els.aaContextImageOutlineWidthValue) {
                els.aaContextImageOutlineWidthValue.value = inputWidth;
                els.aaContextImageOutlineWidthValue.textContent = String(inputWidth);
            }
            if (els.aaContextImageOutlineResetBtn) {
                els.aaContextImageOutlineResetBtn.disabled = active.__aaOutlineProcessing === true;
            }
        }

        function positionImageOutlinePopover() {
            if (!els.aaContextImageOutlinePopover || !els.aaContextImageOutlineBtn) return;
            const rect = els.aaContextImageOutlineBtn.getBoundingClientRect();
            const popoverWidth = els.aaContextImageOutlinePopover.offsetWidth || 256;
            const left = Math.min(window.innerWidth - popoverWidth / 2 - 12, Math.max(popoverWidth / 2 + 12,
                rect.left + rect.width / 2));
            els.aaContextImageOutlinePopover.style.left = `${left}px`;
            els.aaContextImageOutlinePopover.style.top = `${rect.bottom + 8}px`;
        }

        function openImageOutlinePopover() {
            const active = state.canvas.getActiveObject();
            if (!(typeof isImageOutlineTarget === 'function' ? isImageOutlineTarget(active) : active?.type === 'image')) return;
            if (guardLockedImageAction('menambah outline', active)) return;
            syncImageOutlineControl(active);
            closeToolbarPopovers('image-outline');
            els.aaContextImageOutlinePopover?.classList.add('is-open');
            requestAnimationFrame(positionImageOutlinePopover);
        }

        function closeImageOutlinePopover() {
            els.aaContextImageOutlinePopover?.classList.remove('is-open');
        }

        function toggleImageOutlinePopover() {
            if (els.aaContextImageOutlinePopover?.classList.contains('is-open')) {
                closeImageOutlinePopover();
                return;
            }
            openImageOutlinePopover();
        }

        function applyImageStrokeSettings({
            width,
            style,
            color,
        } = {}) {
            const active = state.canvas.getActiveObject() || state.contextImageTarget;
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah stroke', active)) return;

            const currentWidth = Math.max(0, Number(active.strokeWidth) || 0);
            const hasWidthInput = width !== undefined;
            const hasColorInput = color !== undefined;
            let nextStyle = style || getImageStrokeStyle(active);
            let nextWidth = Math.max(0, Math.round(Number(width ?? currentWidth) || 0));
            if (!style && hasWidthInput && nextWidth > 0 && nextStyle === 'none') {
                nextStyle = 'solid';
            }
            if (hasColorInput && nextStyle === 'none') {
                nextStyle = 'solid';
                nextWidth = Math.max(nextWidth, 20);
            }
            if (style && style !== 'none' && nextWidth <= 0) {
                nextWidth = 20;
            }
            let dash = null;
            let strokeWidth = nextWidth;
            let stroke = color || (active.stroke && active.stroke !== 'transparent' ? active.stroke : '#111827');

            if (nextStyle === 'none' || !strokeWidth) {
                stroke = 'transparent';
                strokeWidth = 0;
                dash = null;
            } else if (nextStyle === 'dashed') {
                strokeWidth = Math.max(strokeWidth, 2);
                dash = getImageStrokeDash(nextStyle, strokeWidth);
            } else if (nextStyle === 'dotted') {
                strokeWidth = Math.max(strokeWidth, 2);
                dash = getImageStrokeDash(nextStyle, strokeWidth);
            } else {
                strokeWidth = Math.max(strokeWidth, 1);
            }

            active.set({
                stroke,
                strokeWidth,
                strokeDashArray: dash,
                strokeUniform: true,
                imageStrokeStyle: strokeWidth ? nextStyle : 'none',
            });
            active.dirty = true;
            active.setCoords();
            state.canvas.setActiveObject(active);
            state.canvas.requestRenderAll();
            syncContextStrokeControl(active);
            snapshot();
        }

        function getContextTransparencyTrigger(active = state.canvas?.getActiveObject()) {
            return isContextTextObject(active) ? els.aaTextContextOpacityBtn : els.aaContextOpacityBtn;
        }

        function syncContextTransparencyControl(active = state.canvas?.getActiveObject()) {
            if (!active || active === state.cropBox) {
                closeContextTransparencyPopover();
                return;
            }
            const value = Math.max(0, Math.min(100, Math.round(Number(active.opacity ?? 1) * 100)));
            if (els.aaContextTransparencyInput) {
                els.aaContextTransparencyInput.value = value;
            }
            if (els.aaContextTransparencyValue) {
                els.aaContextTransparencyValue.value = value;
                els.aaContextTransparencyValue.textContent = value;
            }
        }

        function positionContextTransparencyPopover() {
            const active = state.canvas?.getActiveObject();
            const trigger = getContextTransparencyTrigger(active);
            if (!els.aaContextTransparencyPopover || !trigger) return;
            const rect = trigger.getBoundingClientRect();
            const popoverWidth = els.aaContextTransparencyPopover.offsetWidth || 256;
            const left = Math.min(window.innerWidth - popoverWidth / 2 - 12, Math.max(popoverWidth / 2 + 12,
                rect.left + rect.width / 2));
            els.aaContextTransparencyPopover.style.left = `${left}px`;
            els.aaContextTransparencyPopover.style.top = `${rect.bottom + 8}px`;
        }

        function openContextTransparencyPopover() {
            const active = state.canvas.getActiveObject();
            if (!active || active === state.cropBox) return;
            if (isSpecialContextToolbarObject(active)) return;
            if (guardLockedImageAction('mengubah opacity', active)) return;
            if (active.locked === true) return;
            syncContextTransparencyControl(active);
            closeToolbarPopovers('transparency');
            els.aaContextTransparencyPopover?.classList.add('is-open');
            requestAnimationFrame(positionContextTransparencyPopover);
        }

        function closeContextTransparencyPopover() {
            els.aaContextTransparencyPopover?.classList.remove('is-open');
        }

        function toggleContextTransparencyPopover() {
            if (els.aaContextTransparencyPopover?.classList.contains('is-open')) {
                closeContextTransparencyPopover();
                return;
            }
            openContextTransparencyPopover();
        }

        function applyContextTransparency(value) {
            const active = state.canvas.getActiveObject();
            if (!active || active === state.cropBox) return;
            if (isSpecialContextToolbarObject(active)) return;
            if (guardLockedImageAction('mengubah opacity', active)) return;
            if (active.locked === true) return;
            const percent = Math.max(0, Math.min(100, Math.round(Number(value) || 0)));
            active.set('opacity', percent / 100);
            active.dirty = true;
            active.setCoords();
            if (els.aaContextTransparencyValue) {
                els.aaContextTransparencyValue.value = percent;
                els.aaContextTransparencyValue.textContent = percent;
            }
            state.canvas.requestRenderAll();
            snapshot();
        }

        function syncContextRadiusControl(active = state.canvas?.getActiveObject()) {
            const isImage = Boolean(active && active.type === 'image' && active !== state.cropBox);
            if (!isImage) {
                closeContextRadiusPopover();
                return;
            }
            const radius = Math.max(0, Math.round(Number(active.borderRadius) || 0));
            if (els.aaContextRadiusInput) {
                els.aaContextRadiusInput.value = radius;
            }
            if (els.aaContextRadiusValue) {
                els.aaContextRadiusValue.value = radius;
                els.aaContextRadiusValue.textContent = radius;
            }
        }

        function positionContextRadiusPopover() {
            if (!els.aaContextRadiusPopover || !els.aaContextRadiusBtn) return;
            const rect = els.aaContextRadiusBtn.getBoundingClientRect();
            const popoverWidth = els.aaContextRadiusPopover.offsetWidth || 256;
            const left = Math.min(window.innerWidth - popoverWidth / 2 - 12, Math.max(popoverWidth / 2 + 12,
                rect.left + rect.width / 2));
            els.aaContextRadiusPopover.style.left = `${left}px`;
            els.aaContextRadiusPopover.style.top = `${rect.bottom + 8}px`;
        }

        function openContextRadiusPopover() {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah radius', active)) return;
            syncContextRadiusControl(active);
            closeToolbarPopovers('radius');
            els.aaContextRadiusPopover?.classList.add('is-open');
            requestAnimationFrame(positionContextRadiusPopover);
        }

        function closeContextRadiusPopover() {
            els.aaContextRadiusPopover?.classList.remove('is-open');
        }

        function toggleContextRadiusPopover() {
            if (els.aaContextRadiusPopover?.classList.contains('is-open')) {
                closeContextRadiusPopover();
                return;
            }
            openContextRadiusPopover();
        }

        function applyContextImageRadius(value) {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah radius', active)) return;
            const radius = Math.max(0, Number(value) || 0);
            if (els.aaContextRadiusValue) {
                els.aaContextRadiusValue.value = radius;
                els.aaContextRadiusValue.textContent = Math.round(radius);
            }
            if (els.aaImageRadiusInput) {
                els.aaImageRadiusInput.value = radius;
            }
            if (els.aaImageRadiusValue) {
                els.aaImageRadiusValue.textContent = `${Math.round(radius)}px`;
            }
            applyImageBorderRadius(active, radius);
            state.canvas.requestRenderAll();
            snapshot();
        }

        function getImageEffectFilters(presetName) {
            const filters = fabric?.Image?.filters || {};
            const preset = String(presetName || 'none');
            const list = [];

            if (preset === 'brightness' && filters.Brightness) {
                list.push(new filters.Brightness({ brightness: 0.16 }));
            } else if (preset === 'contrast' && filters.Contrast) {
                list.push(new filters.Contrast({ contrast: 0.22 }));
            } else if (preset === 'saturation' && filters.Saturation) {
                list.push(new filters.Saturation({ saturation: 0.38 }));
            } else if (preset === 'grayscale' && filters.Grayscale) {
                list.push(new filters.Grayscale());
            } else if (preset === 'sepia' && filters.Sepia) {
                list.push(new filters.Sepia());
            } else if (preset === 'blur' && filters.Blur) {
                list.push(new filters.Blur({ blur: 0.16 }));
            } else if (preset === 'sharpen' && filters.Convolute) {
                list.push(new filters.Convolute({ matrix: [0, -1, 0, -1, 5, -1, 0, -1, 0] }));
            } else if (preset === 'vintage') {
                if (filters.Sepia) list.push(new filters.Sepia());
                if (filters.Contrast) list.push(new filters.Contrast({ contrast: 0.08 }));
                if (filters.Saturation) list.push(new filters.Saturation({ saturation: -0.18 }));
            } else if (preset === 'remove-color') {
                if (filters.RemoveColor) {
                    list.push(new filters.RemoveColor({ color: '#ffffff', distance: 0.22 }));
                } else if (filters.Saturation) {
                    list.push(new filters.Saturation({ saturation: -0.8 }));
                }
            }

            return list;
        }

        function syncImageEffectButtons(active = state.canvas?.getActiveObject()) {
            const preset = active && active.type === 'image' ? (active.aaImageEffectPreset || 'none') : 'none';
            document.querySelectorAll('[data-aa-image-effect]').forEach(button => {
                button.classList.toggle('is-active', button.dataset.aaImageEffect === preset);
            });
        }

        function syncImageOverlayButtons(active = state.canvas?.getActiveObject()) {
            const preset = active && active.type === 'image' ? (active.aaImageOverlayGradient || 'none') : 'none';
            document.querySelectorAll('[data-aa-image-overlay]').forEach(button => {
                button.classList.toggle('is-active', button.dataset.aaImageOverlay === preset);
            });
        }

        function syncImageEffectPreviewSource(active = state.canvas?.getActiveObject()) {
            const previews = document.querySelectorAll('.aa-effect-preview');
            if (!previews.length) return;

            let source = '';
            if (active && active.type === 'image') {
                source = String((typeof active.getSrc === 'function' ? active.getSrc() : active.src) || '').trim();
            }

            if (source.startsWith('data:image/') && source.length > 200000) {
                source = '';
            }

            previews.forEach(preview => {
                if (source) {
                    preview.style.setProperty('--aa-effect-preview-image', `url(${JSON.stringify(source)})`);
                } else {
                    preview.style.removeProperty('--aa-effect-preview-image');
                }
            });
        }

        function positionImagePopover(popover, trigger) {
            if (!popover || !trigger) return;
            const rect = trigger.getBoundingClientRect();
            const popoverWidth = popover.offsetWidth || 314;
            const popoverHeight = popover.offsetHeight || 360;
            const left = Math.min(window.innerWidth - popoverWidth / 2 - 12, Math.max(popoverWidth / 2 + 12,
                rect.left + rect.width / 2));
            let top = rect.bottom + 8;
            if (top + popoverHeight > window.innerHeight - 12) {
                top = Math.max(12, rect.top - popoverHeight - 8);
            }
            popover.style.left = `${left}px`;
            popover.style.top = `${top}px`;
        }

        function openImageEffectsPopover() {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah effect', active)) return;
            closeToolbarPopovers('image-effects');
            syncImageEffectButtons(active);
            syncImageOverlayButtons(active);
            syncImageFrameButtons(active);
            syncImageEffectPreviewSource(active);
            openLeftDrawerPanel('image-effects');
        }

        function closeImageEffectsPopover() {
            els.aaContextImageEffectsPopover?.classList.remove('is-open');
        }

        function toggleImageEffectsPopover() {
            if (els.aaContextImageEffectsPopover?.classList.contains('is-open')) {
                closeImageEffectsPopover();
                return;
            }
            openImageEffectsPopover();
        }

        function applyImageEffectPreset(presetName) {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah effect', active)) return;
            const preset = String(presetName || 'none');
            const previousPreset = active.aaImageEffectPreset || 'none';
            const preserve = {
                left: active.left,
                top: active.top,
                width: active.width,
                height: active.height,
                scaleX: active.scaleX,
                scaleY: active.scaleY,
                cropX: active.cropX,
                cropY: active.cropY,
                angle: active.angle,
                originX: active.originX,
                originY: active.originY,
                flipX: active.flipX,
                flipY: active.flipY,
                clipPath: active.clipPath,
            };

            active.set({
                filters: [],
                aaImageEffectPreset: preset,
            });

            if (preset === 'opacity') {
                active.set({ opacity: 0.72 });
            } else if (previousPreset === 'opacity' || preset === 'none') {
                active.set({ opacity: 1 });
            }

            if (preset === 'shadow') {
                active.set('shadow', new fabric.Shadow({
                    color: 'rgba(15, 23, 42, 0.28)',
                    blur: 24,
                    offsetX: 0,
                    offsetY: 14,
                }));
            } else if (preset === 'none' || previousPreset === 'shadow') {
                active.set('shadow', null);
            }

            active.aaImageRemoveColor = preset === 'remove-color' ? '#ffffff' : '';
            active.set(preserve);
            active.dirty = true;
            active.setCoords();
            state.canvas.requestRenderAll();
            syncImageEffectButtons(active);
            snapshot();
            setStatus('Image effect diperbarui');
        }

        function applyImageOverlayGradient(presetName) {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah overlay foto', active)) return;
            const preset = String(presetName || 'none');

            active.set({
                aaImageOverlayGradient: preset === 'none' ? '' : preset,
            });
            active.dirty = true;
            active.setCoords();
            state.canvas.requestRenderAll();
            syncImageOverlayButtons(active);
            snapshot();
            setStatus(preset === 'none' ? 'Overlay foto dihapus' : 'Overlay gradasi diperbarui');
        }

        function resetImageEffectsForActive() {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mereset effect foto', active)) return;

            const preserve = {
                left: active.left,
                top: active.top,
                width: active.width,
                height: active.height,
                scaleX: active.scaleX,
                scaleY: active.scaleY,
                cropX: active.cropX,
                cropY: active.cropY,
                angle: active.angle,
                originX: active.originX,
                originY: active.originY,
                flipX: active.flipX,
                flipY: active.flipY,
                clipPath: active.clipPath,
            };

            active.set({
                filters: [],
                aaImageEffectPreset: 'none',
                aaImageOverlayGradient: '',
                opacity: 1,
                shadow: null,
            });
            active.aaImageRemoveColor = '';
            active.set(preserve);
            active.dirty = true;
            active.setCoords();
            state.canvas.requestRenderAll();
            syncImageEffectButtons(active);
            syncImageOverlayButtons(active);
            syncImageEffectPreviewSource(active);
            snapshot();
            setStatus('Effect foto direset');
        }

        function createImageFrameClipPath(image, shape) {
            const width = Math.max(1, Number(image.width) || 1);
            const height = Math.max(1, Number(image.height) || 1);
            const left = -width / 2;
            const top = -height / 2;
            const right = width / 2;
            const bottom = height / 2;
            const shapeName = String(shape || 'none');

            if (shapeName === 'circle') {
                return new fabric.Ellipse({
                    rx: width / 2,
                    ry: height / 2,
                    originX: 'center',
                    originY: 'center',
                    left: 0,
                    top: 0,
                });
            }
            if (shapeName === 'rounded') {
                return null;
            }

            const pathFor = {
                heart: [
                    `M 0 ${bottom * 0.82}`,
                    `C ${left * 0.82} ${top * -0.08} ${left} ${top * 0.06} ${left * 0.72} ${top * 0.68}`,
                    `C ${left * 0.34} ${top * 1.02} ${left * 0.06} ${top * 0.58} 0 ${top * 0.24}`,
                    `C ${right * 0.06} ${top * 0.58} ${right * 0.34} ${top * 1.02} ${right * 0.72} ${top * 0.68}`,
                    `C ${right} ${top * 0.06} ${right * 0.82} ${top * -0.08} 0 ${bottom * 0.82} Z`,
                ].join(' '),
                arch: [
                    `M ${left} ${bottom}`,
                    `L ${left} 0`,
                    `C ${left} ${top} ${right} ${top} ${right} 0`,
                    `L ${right} ${bottom}`,
                    'Z',
                ].join(' '),
                diamond: `M 0 ${top} L ${right} 0 L 0 ${bottom} L ${left} 0 Z`,
                blob: [
                    `M ${left * 0.1} ${top}`,
                    `C ${right * 0.82} ${top * 0.92} ${right} ${top * 0.14} ${right * 0.76} ${bottom * 0.36}`,
                    `C ${right * 0.42} ${bottom * 1.02} ${left * 0.34} ${bottom} ${left * 0.78} ${bottom * 0.34}`,
                    `C ${left * 1.08} ${top * -0.14} ${left * 0.78} ${top * 0.88} ${left * 0.1} ${top} Z`,
                ].join(' '),
                ticket: [
                    `M ${left} ${top} L ${right} ${top} L ${right} ${top * 0.28}`,
                    `Q ${right * 0.72} 0 ${right} ${bottom * 0.28}`,
                    `L ${right} ${bottom} L ${left} ${bottom} L ${left} ${bottom * 0.28}`,
                    `Q ${left * 0.72} 0 ${left} ${top * 0.28} Z`,
                ].join(' '),
                oval: [
                    `M 0 ${top}`,
                    `C ${right * 0.92} ${top} ${right * 0.92} ${bottom} 0 ${bottom}`,
                    `C ${left * 0.92} ${bottom} ${left * 0.92} ${top} 0 ${top} Z`,
                ].join(' '),
                shield: [
                    `M 0 ${top}`,
                    `L ${right * 0.86} ${top * 0.6}`,
                    `L ${right * 0.72} ${bottom * 0.44}`,
                    `L 0 ${bottom}`,
                    `L ${left * 0.72} ${bottom * 0.44}`,
                    `L ${left * 0.86} ${top * 0.6} Z`,
                ].join(' '),
                hexagon: `M ${left * 0.54} ${top} L ${right * 0.54} ${top} L ${right} 0 L ${right * 0.54} ${bottom} L ${left * 0.54} ${bottom} L ${left} 0 Z`,
                petal: [
                    `M 0 ${top}`,
                    `C ${right * 0.92} ${top * 0.82} ${right} ${top * -0.14} ${right * 0.38} 0`,
                    `C ${right} ${bottom * 0.14} ${right * 0.92} ${bottom * 0.82} 0 ${bottom}`,
                    `C ${left * 0.92} ${bottom * 0.82} ${left} ${bottom * 0.14} ${left * 0.38} 0`,
                    `C ${left} ${top * -0.14} ${left * 0.92} ${top * 0.82} 0 ${top} Z`,
                ].join(' '),
                wave: [
                    `M ${left} ${top * 0.72}`,
                    `C ${left * 0.48} ${top * 1.18} ${right * 0.12} ${top * 0.22} ${right} ${top * 0.72}`,
                    `L ${right} ${bottom * 0.72}`,
                    `C ${right * 0.38} ${bottom * 0.24} ${left * 0.1} ${bottom * 1.18} ${left} ${bottom * 0.72} Z`,
                ].join(' '),
                tag: [
                    `M ${left} ${top} L ${right * 0.66} ${top}`,
                    `L ${right} 0 L ${right * 0.66} ${bottom}`,
                    `L ${left} ${bottom} Z`,
                ].join(' '),
                bookmark: [
                    `M ${left} ${top} L ${right} ${top} L ${right} ${bottom}`,
                    `L 0 ${bottom * 0.52} L ${left} ${bottom} Z`,
                ].join(' '),
                scallop: [
                    `M ${left * 0.74} ${top}`,
                    `Q ${left * 0.5} ${top * 0.58} ${left * 0.18} ${top}`,
                    `Q ${right * 0.18} ${top * 0.58} ${right * 0.5} ${top}`,
                    `Q ${right * 0.78} ${top * 0.56} ${right} ${top * 0.18}`,
                    `Q ${right * 0.56} 0 ${right} ${bottom * 0.18}`,
                    `Q ${right * 0.78} ${bottom * 0.56} ${right * 0.5} ${bottom}`,
                    `Q ${right * 0.18} ${bottom * 0.58} ${left * 0.18} ${bottom}`,
                    `Q ${left * 0.5} ${bottom * 0.58} ${left * 0.74} ${bottom}`,
                    `Q ${left * 0.56} 0 ${left} ${bottom * 0.18}`,
                    `Q ${left * 0.78} ${top * 0.56} ${left * 0.74} ${top} Z`,
                ].join(' '),
            };

            if (!pathFor[shapeName]) return null;
            return new fabric.Path(pathFor[shapeName], {
                originX: 'center',
                originY: 'center',
                left: 0,
                top: 0,
            });
        }

	        function syncImageFrameButtons(active = state.canvas?.getActiveObject()) {
	            const isPlaceholder = typeof isFramePlaceholderObject === 'function' && isFramePlaceholderObject(active);
	            const shape = active && (active.type === 'image' || isPlaceholder) ? (active.aaImageFrameShape || 'none') : 'none';
	            document.querySelectorAll('[data-aa-image-frame]').forEach(button => {
	                button.classList.toggle('is-active', button.dataset.aaImageFrame === shape);
	            });
	            if (els.aaFramePlaceholderActions) {
	                els.aaFramePlaceholderActions.classList.toggle('hidden', !isPlaceholder);
	            }
	        }

        function openImageFramePopover() {
            const active = state.canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah frame', active)) return;
            closeToolbarPopovers('image-frame');
            syncImageFrameButtons(active);
            openLeftDrawerPanel('image-frame');
        }

        function closeImageFramePopover() {
            els.aaContextImageFramePopover?.classList.remove('is-open');
        }

        function toggleImageFramePopover() {
            if (els.aaContextImageFramePopover?.classList.contains('is-open')) {
                closeImageFramePopover();
                return;
            }
            openImageFramePopover();
        }

        function applyImageFrameShape(shapeName) {
            const active = state.canvas.getActiveObject();
            if (typeof isFramePlaceholderObject === 'function' && isFramePlaceholderObject(active)) {
                updateFramePlaceholderShape?.(active, shapeName);
                return;
            }
            if (!active || active.type !== 'image') return;
            if (guardLockedImageAction('mengubah frame', active)) return;
            const shape = String(shapeName || 'none');

            active.set({
                aaImageFrameShape: shape,
                clipPath: shape === 'rounded' ? null : createImageFrameClipPath(active, shape),
            });

            if (shape === 'none') {
                active.set('clipPath', null);
                applyImageBorderRadius(active, 0);
            } else if (shape === 'rounded') {
                applyImageBorderRadius(active, Math.max(24, Math.min(active.width || 1, active.height || 1) * 0.12));
            }

            active.dirty = true;
            active.setCoords();
            state.canvas.requestRenderAll();
            syncImageFrameButtons(active);
            snapshot();
            setStatus(shape === 'none' ? 'Frame image dihapus' : 'Frame image diperbarui');
        }

        function syncTextContextToolbar(active = state.canvas?.getActiveObject()) {
            if (!els.aaTextContextToolbar) return;
            const target = getContextTextTarget(active);
            const visible = Boolean(target && !state.isCropping);
            els.aaTextContextToolbar.classList.toggle('is-visible', visible);
            if (!visible) {
                closeContextTransparencyPopover();
                closeTextEffectsPopover();
            }
            syncContextTransparencyControl(active);
            syncTextEffectsControl(target);
            syncTextAnimationToolbar(target);
            if (!visible) return;

            const readTextStyle = typeof aaGetTextStyleValue === 'function'
                ? aaGetTextStyleValue
                : ((text, property) => text?.[property]);
            const fontFamily = readTextStyle(target, 'fontFamily') || 'Inter';
            const fontSize = Math.round(Number(readTextStyle(target, 'fontSize')) || 42);
            const fill = normalizeColor(readTextStyle(target, 'fill') || '#111827');
            const fontWeight = readTextStyle(target, 'fontWeight');
            const isBold = fontWeight === 'bold' || Number(fontWeight) >= 700;

            if (els.aaTextContextFont) {
                els.aaTextContextFont.value = fontFamily;
            }
            if (els.aaTextContextSizeValue) {
                els.aaTextContextSizeValue.value = fontSize;
                els.aaTextContextSizeValue.textContent = fontSize;
            }
            if (els.aaTextContextColorSwatch) {
                els.aaTextContextColorSwatch.style.background = fill;
            }
            if (els.aaTextContextColorInput) {
                els.aaTextContextColorInput.value = fill;
            }
            els.aaTextContextBoldBtn?.classList.toggle('is-active', isBold);
            els.aaTextContextItalicBtn?.classList.toggle('is-active', readTextStyle(target, 'fontStyle') === 'italic');
            els.aaTextContextUnderlineBtn?.classList.toggle('is-active', Boolean(readTextStyle(target, 'underline')));
            els.aaTextContextStrikeBtn?.classList.toggle('is-active', Boolean(readTextStyle(target, 'linethrough')));
            if (els.aaTextContextOpacityBtn) {
                els.aaTextContextOpacityBtn.disabled = active?.locked === true;
            }
            if (els.aaTextContextCaseBtn) {
                els.aaTextContextCaseBtn.disabled = isGuestNameObject(active);
            }
            if (els.aaTextContextListBtn) {
                els.aaTextContextListBtn.disabled = isGuestNameObject(active);
            }

            const alignIconMap = {
                left: 'fa-align-left',
                center: 'fa-align-center',
                right: 'fa-align-right',
                justify: 'fa-align-justify',
            };
            const align = target.textAlign || 'left';
            const alignIcon = els.aaTextContextAlignBtn?.querySelector('i');
            if (alignIcon) {
                alignIcon.className = 'fa ' + (alignIconMap[align] || 'fa-align-left');
            }
        }

        function populateTextContextFontOptions() {
            if (!els.aaTextContextFont || !els.aaFontInput) return;
            els.aaTextContextFont.innerHTML = els.aaFontInput.innerHTML;
            if (els.aaGuestFieldPopoverFontInput) {
                els.aaGuestFieldPopoverFontInput.innerHTML = els.aaFontInput.innerHTML;
            }
            if (els.aaOpeningButtonFontInput) {
                els.aaOpeningButtonFontInput.innerHTML = els.aaFontInput.innerHTML;
            }
            if (els.aaMobileGuestFieldFontInput) {
                els.aaMobileGuestFieldFontInput.innerHTML = els.aaFontInput.innerHTML;
            }
            if (els.aaMobileOpeningButtonFontInput) {
                els.aaMobileOpeningButtonFontInput.innerHTML = els.aaFontInput.innerHTML;
            }
            if (els.aaCountdownContextFontInput) {
                els.aaCountdownContextFontInput.innerHTML = els.aaFontInput.innerHTML;
            }
        }

        function applyTextContextFontFamily(fontFamily) {
            const active = state.canvas.getActiveObject();
            if (!getContextTextTarget(active)) return;
            const family = fontFamily || 'Inter';
            const finish = () => {
                applyActiveStyle({
                    fontFamily: family,
                });
                syncInspector();
                if (state.canvas) {
                    recalculateTextObjects(state.canvas);
                    state.canvas.requestRenderAll();
                }
            };
            if (document.fonts?.load) {
                Promise.all([
                        ensureBunnyFontCss(family),
                        typeof ensureGoogleFontCss === 'function' ? ensureGoogleFontCss(family) : Promise.resolve(),
                        typeof ensureCustomFontCss === 'function' ? ensureCustomFontCss(family) : Promise.resolve(),
                    ])
                    .then(() => document.fonts.load('24px "' + family.replace(/"/g, '') + '"'))
                    .then(finish)
                    .catch(finish);
                return;
            }
            finish();
        }

        function cleanFontFamilyValue(fontFamily) {
            return String(fontFamily || 'Inter').replace(/^["']|["']$/g, '').trim() || 'Inter';
        }

        function loadEditorFontFamily(fontFamily, callback) {
            const family = cleanFontFamilyValue(fontFamily);
            const finish = () => {
                if (typeof callback === 'function') callback(family);
                if (state.canvas) {
                    recalculateTextObjects(state.canvas);
                    state.canvas.requestRenderAll();
                }
            };
            if (document.fonts?.load) {
                Promise.all([
                        ensureBunnyFontCss(family),
                        typeof ensureGoogleFontCss === 'function' ? ensureGoogleFontCss(family) : Promise.resolve(),
                        typeof ensureCustomFontCss === 'function' ? ensureCustomFontCss(family) : Promise.resolve(),
                    ])
                    .then(() => document.fonts.load('24px "' + family.replace(/"/g, '') + '"'))
                    .then(finish)
                    .catch(finish);
                return;
            }
            finish();
        }

        function getEditorFontCatalog() {
            const source = els.aaFontInput;
            const catalog = [];
            const seen = new Set();
            const pushOption = (option, group) => {
                const value = cleanFontFamilyValue(option?.value || option?.textContent);
                if (!value || seen.has(value)) return;
                seen.add(value);
                catalog.push({
                    family: value,
                    label: option?.textContent?.trim() || value,
                    group: group || 'Document fonts',
                    source: option?.dataset?.fontSource || '',
                });
            };

            Array.from(source?.children || []).forEach(child => {
                if (child.tagName === 'OPTGROUP') {
                    const group = child.label || 'Document fonts';
                    Array.from(child.querySelectorAll('option')).forEach(option => pushOption(option,
                        group));
                    return;
                }
                if (child.tagName === 'OPTION') {
                    pushOption(child, 'Document fonts');
                }
            });

            if (!catalog.length) {
                ['Inter', 'Poppins', 'Montserrat', 'Playfair Display', 'Cormorant Garamond', 'Great Vibes'].forEach(
                    family => catalog.push({
                    family,
                    label: family,
                    group: 'Document fonts',
                    source: '',
                }));
            }

            return catalog;
        }

        const fontDrawerWeightLabels = {
            100: 'Thin',
            200: 'Extra Light',
            300: 'Light',
            400: 'Regular',
            500: 'Medium',
            600: 'Semi Bold',
            700: 'Bold',
            800: 'Extra Bold',
            900: 'Black',
            1000: 'Extra Black',
        };

        function getFontDrawerWeights(itemOrFamily) {
            const item = typeof itemOrFamily === 'object' && itemOrFamily ? itemOrFamily : null;
            const family = cleanFontFamilyValue(item?.family || itemOrFamily);
            if (!family || item?.source === 'bunny') return [];
            const registry = typeof googleFontWeights !== 'undefined' ? googleFontWeights : null;
            const raw = registry?.[family];
            if (!raw) return [];
            return String(raw)
                .split(';')
                .map(weight => Number(String(weight).trim()))
                .filter(weight => Number.isFinite(weight) && weight > 0)
                .filter((weight, index, list) => list.indexOf(weight) === index)
                .sort((a, b) => a - b);
        }

        function getDefaultFontDrawerWeight(itemOrFamily) {
            const weights = getFontDrawerWeights(itemOrFamily);
            if (!weights.length) return '';
            return String(weights.includes(400) ? 400 : weights[0]);
        }

        function normalizeFontDrawerWeight(weight, fallback = '400') {
            const value = String(weight || '').trim().toLowerCase();
            if (value === 'bold') return '700';
            if (value === 'normal') return '400';
            const numeric = Number(value);
            if (Number.isFinite(numeric) && numeric > 0) {
                return String(numeric);
            }
            return fallback;
        }

        function getActiveFontDrawerWeight() {
            const target = getFontDrawerPreviewTarget();
            const object = target?.objects?.[0];
            return normalizeFontDrawerWeight(object?.fontWeight, '400');
        }

        function getActiveFontDrawerObject() {
            return state.canvas?.getActiveObject() || null;
        }

        function getSelectedFontDrawerWeight(fontFamily) {
            const selected = state.fontDrawerSelectedWeight;
            const active = getActiveFontDrawerObject();
            const family = cleanFontFamilyValue(fontFamily);
            if (!selected || !active || selected.active !== active || selected.family !== family) {
                return '';
            }
            return normalizeFontDrawerWeight(selected.weight, '');
        }

        function rememberSelectedFontDrawerWeight(fontFamily, fontWeight) {
            const active = getActiveFontDrawerObject();
            const family = cleanFontFamilyValue(fontFamily);
            const weight = normalizeFontDrawerWeight(fontWeight, '');
            state.fontDrawerSelectedWeight = active && family && weight ? {
                active,
                family,
                weight,
            } : null;
        }

        function getFontDrawerPreviewWeight(fontFamily, requestedWeight = '') {
            const family = cleanFontFamilyValue(fontFamily);
            const requested = normalizeFontDrawerWeight(requestedWeight, '');
            if (requested) return requested;

            const weights = getFontDrawerWeights(family).map(weight => String(weight));
            const activeFamily = getActiveFontDrawerFamily();
            const activeWeight = getActiveFontDrawerWeight();

            if (family === activeFamily && weights.includes(activeWeight)) {
                return activeWeight;
            }

            return getDefaultFontDrawerWeight(family);
        }

        function getActiveFontDrawerFamily() {
            const active = state.canvas?.getActiveObject();
            if (state.fontDrawerTarget === 'countdown') {
                return cleanFontFamilyValue(els.aaCountdownContextFontInput?.value || active?.countdownFontFamily ||
                    'Inter');
            }
            if (state.fontDrawerTarget === 'guest-field') {
                const parts = isGuestFieldInteractionObject(active) ? getGuestbookObjectParts(active) : null;
                return cleanFontFamilyValue(els.aaMobileGuestFieldFontInput?.value || els.aaGuestFieldPopoverFontInput?.value || parts?.text?.fontFamily ||
                    'Inter');
            }
            if (state.fontDrawerTarget === 'opening-button') {
                const parts = isOpeningButtonInteractionObject(active) ? getOpeningButtonParts(active) : null;
                return cleanFontFamilyValue(els.aaMobileOpeningButtonFontInput?.value || els.aaOpeningButtonFontInput?.value || parts?.text?.fontFamily ||
                    active?.openingButtonFontFamily || 'Inter');
            }
            if (state.fontDrawerTarget === 'panel-text') {
                return cleanFontFamilyValue(els.aaFontInput?.value || getContextTextTarget(active)?.fontFamily ||
                    'Inter');
            }
            return cleanFontFamilyValue(els.aaTextContextFont?.value || getContextTextTarget(active)?.fontFamily ||
                'Inter');
        }

        function getFontDrawerPreviewTarget() {
            const active = state.canvas?.getActiveObject();
            if (!active || active === state.cropBox || state.isCropping) return null;

            if (state.fontDrawerTarget === 'countdown' && active.customType === 'countdown-timer') {
                return {
                    active,
                    prop: 'countdownFontFamily',
                    objects: getCountdownTextObjects(active),
                };
            }

            if (state.fontDrawerTarget === 'guest-field' && isGuestFieldInteractionObject(active)) {
                const parts = getGuestbookObjectParts(active);
                return parts.text ? {
                    active,
                    prop: null,
                    objects: [parts.text],
                } : null;
            }

            if (state.fontDrawerTarget === 'opening-button' && isOpeningButtonInteractionObject(active)) {
                const parts = getOpeningButtonParts(active);
                return parts.text ? {
                    active,
                    prop: 'openingButtonFontFamily',
                    objects: [parts.text],
                } : null;
            }

            const textTarget = getContextTextTarget(active);
            return textTarget ? {
                active,
                prop: null,
                objects: [textTarget],
            } : null;
        }

        function applyFontDrawerPreviewFamily(fontFamily, options = {}) {
            const family = cleanFontFamilyValue(fontFamily);
            const fontWeight = options.fontWeight ? String(options.fontWeight) : '';
            const target = getFontDrawerPreviewTarget();
            if (!target || !target.objects.length) return false;

            if (!state.fontDrawerPreview || state.fontDrawerPreview.active !== target.active) {
                state.fontDrawerPreview = {
                    active: target.active,
                    prop: target.prop,
                    propValue: target.prop ? target.active[target.prop] : undefined,
                    objects: target.objects.map(object => ({
                        object,
                        fontFamily: object.fontFamily,
                        fontWeight: object.fontWeight,
                    })),
                };
            }

            if (target.prop) {
                target.active.set ? target.active.set(target.prop, family) : (target.active[target.prop] = family);
            }

            target.objects.forEach(object => {
                object.set('fontFamily', family);
                if (fontWeight) {
                    object.set('fontWeight', fontWeight);
                }
                object.dirty = true;
                if (typeof object.initDimensions === 'function') {
                    object.initDimensions();
                }
                object.setCoords?.();
            });

            target.active.dirty = true;
            target.active.setCoords?.();

            if (typeof recalculateTextObjects === 'function') {
                recalculateTextObjects(state.canvas);
            }
            state.canvas?.requestRenderAll?.();

            if (options.sync !== false) {
                syncInspector?.();
                syncTextContextToolbar?.();
                syncInteractionPopover?.();
            }

            return true;
        }

        function clearFontDrawerPreview(options = {}) {
            const preview = state.fontDrawerPreview;
            state.fontDrawerPreviewToken = (state.fontDrawerPreviewToken || 0) + 1;
            state.fontDrawerPreviewFamily = '';
            if (state.fontDrawerHoverTimer) {
                window.clearTimeout(state.fontDrawerHoverTimer);
                state.fontDrawerHoverTimer = null;
            }
            if (!preview) return;
            state.fontDrawerPreview = null;

            if (options.restore !== false) {
                if (preview.prop && preview.active) {
                    preview.active.set ? preview.active.set(preview.prop, preview.propValue) : (preview.active[preview.prop] =
                        preview.propValue);
                }
                preview.objects.forEach(item => {
                    if (!item.object) return;
                    item.object.set('fontFamily', item.fontFamily || 'Inter');
                    item.object.set('fontWeight', item.fontWeight || 'normal');
                    item.object.dirty = true;
                    if (typeof item.object.initDimensions === 'function') {
                        item.object.initDimensions();
                    }
                    item.object.setCoords?.();
                });
                preview.active.dirty = true;
                preview.active.setCoords?.();
                if (typeof recalculateTextObjects === 'function') {
                    recalculateTextObjects(state.canvas);
                }
                state.canvas?.requestRenderAll?.();
                syncInspector?.();
                syncTextContextToolbar?.();
                syncInteractionPopover?.();
            }

        }

        function previewFontDrawerFamily(fontFamily, options = {}) {
            const family = cleanFontFamilyValue(fontFamily);
            if (!family) return;
            const fontWeight = getFontDrawerPreviewWeight(family, options.fontWeight);
            const previewKey = family + '|' + (fontWeight || '');
            if (state.fontDrawerPreviewFamily === previewKey) return;
            state.fontDrawerPreviewFamily = previewKey;

            if (state.fontDrawerHoverTimer) {
                window.clearTimeout(state.fontDrawerHoverTimer);
            }

            const token = (state.fontDrawerPreviewToken || 0) + 1;
            state.fontDrawerPreviewToken = token;
            state.fontDrawerHoverTimer = window.setTimeout(() => {
                loadEditorFontFamily(family, loadedFamily => {
                    if (state.fontDrawerPreviewToken !== token) return;
                    applyFontDrawerPreviewFamily(loadedFamily, {
                        fontWeight,
                    });
                });
            }, 90);
        }

        function renderFontDrawerChips(catalog) {
            if (!els.aaFontDrawerChips) return;
            els.aaFontDrawerChips.innerHTML = '';
        }

        function fontDrawerMixRank(item) {
            const text = String(item?.family || item?.label || '');
            let hash = 0;
            for (let index = 0; index < text.length; index++) {
                hash = ((hash << 5) - hash + text.charCodeAt(index)) | 0;
            }
            return Math.abs(hash);
        }

        function renderFontDrawerList() {
            if (!els.aaFontDrawerList) return;
            const catalog = getEditorFontCatalog();
            renderFontDrawerChips(catalog);
            const query = String(state.fontDrawerQuery || '').trim().toLowerCase();
            const activeFamily = getActiveFontDrawerFamily();
            const filtered = catalog.filter(item => {
                const queryMatch = !query || item.label.toLowerCase().includes(query) || item.family
                    .toLowerCase()
                    .includes(query);
                return queryMatch;
            }).sort((a, b) => fontDrawerMixRank(a) - fontDrawerMixRank(b));
            filtered.forEach(item => {
                ensureBunnyFontCss(item.family);
                if (typeof ensureGoogleFontCss === 'function') ensureGoogleFontCss(item.family);
                if (typeof ensureCustomFontCss === 'function') ensureCustomFontCss(item.family);
            });

            if (!filtered.length) {
                els.aaFontDrawerList.innerHTML = '<div class="aa-font-drawer-empty">Font tidak ditemukan.</div>';
                return;
            }

            els.aaFontDrawerList.innerHTML = '';
            filtered.forEach(item => {
                const active = cleanFontFamilyValue(item.family) === activeFamily;
                const weights = getFontDrawerWeights(item);
                const defaultWeight = getDefaultFontDrawerWeight(item);
                const activeWeight = getActiveFontDrawerWeight();
                const isExpanded = state.fontDrawerWeightFamily === cleanFontFamilyValue(item.family);
                const wrap = document.createElement('div');
                wrap.className = 'aa-font-drawer-item' + (isExpanded ? ' is-expanded' : '');

                const row = document.createElement('div');
                row.className = 'aa-font-drawer-row';

                if (weights.length > 1) {
                    const toggle = document.createElement('button');
                    toggle.className = 'aa-font-drawer-weight-toggle';
                    toggle.type = 'button';
                    toggle.dataset.aaFontWeightToggle = '1';
                    toggle.dataset.aaWeightFamily = item.family;
                    toggle.title = 'Pilihan weight';
                    toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                    const toggleIcon = document.createElement('i');
                    toggleIcon.className = 'fa fa-chevron-right';
                    toggleIcon.setAttribute('aria-hidden', 'true');
                    toggle.appendChild(toggleIcon);
                    row.appendChild(toggle);
                } else {
                    const spacer = document.createElement('span');
                    spacer.className = 'aa-font-drawer-weight-spacer';
                    row.appendChild(spacer);
                }

                const button = document.createElement('button');
                button.className = 'aa-font-drawer-option' + (active ? ' is-active' : '');
                button.type = 'button';
                button.dataset.aaFontFamily = item.family;
                if (defaultWeight) {
                    button.dataset.aaDefaultFontWeight = defaultWeight;
                }

                const preview = document.createElement('span');
                preview.className = 'aa-font-drawer-preview';
                preview.textContent = item.label;
                preview.style.fontFamily = '"' + cleanFontFamilyValue(item.family).replace(/"/g, '') +
                    '", Inter, sans-serif';

                const icon = document.createElement('i');
                icon.className = 'fa fa-check';
                icon.setAttribute('aria-hidden', 'true');

                button.appendChild(preview);
                button.appendChild(icon);
                row.appendChild(button);
                wrap.appendChild(row);

                if (weights.length > 1 && isExpanded) {
                    const weightList = document.createElement('div');
                    weightList.className = 'aa-font-weight-list';
                    weights.forEach(weight => {
                        const selectedWeight = getSelectedFontDrawerWeight(item.family);
                        const effectiveActiveWeight = selectedWeight || activeWeight;
                        const weightButton = document.createElement('button');
                        weightButton.type = 'button';
                        weightButton.className = 'aa-font-weight-option' + (active && String(weight) ===
                            effectiveActiveWeight ? ' is-active' : '');
                        weightButton.dataset.aaFontFamily = item.family;
                        weightButton.dataset.aaFontWeight = String(weight);
                        const weightLabel = document.createElement('span');
                        weightLabel.textContent = fontDrawerWeightLabels[weight] || String(weight);
                        const weightIcon = document.createElement('i');
                        weightIcon.className = 'fa fa-check';
                        weightIcon.setAttribute('aria-hidden', 'true');
                        weightButton.appendChild(weightLabel);
                        weightButton.appendChild(weightIcon);
                        weightButton.title = `${item.label} ${weightButton.textContent}`;
                        weightButton.style.fontFamily = '"' + cleanFontFamilyValue(item.family).replace(/"/g, '') +
                            '", Inter, sans-serif';
                        weightButton.style.fontWeight = String(weight);
                        weightList.appendChild(weightButton);
                    });
                    wrap.appendChild(weightList);
                }

                els.aaFontDrawerList.appendChild(wrap);
            });
        }

        function openLeftDrawerPanel(panelKey) {
            if (!panelKey) return;
            if (
                typeof isLeftPanelLimitedInCurrentMode === 'function' &&
                isLeftPanelLimitedInCurrentMode(panelKey)
            ) {
                panelKey = 'canvas';
            }
            if (panelKey !== 'font') {
                clearFontDrawerPreview();
            }
            if (isMobileEditorInteractionDrawer() && panelKey !== 'font' && panelKey !== 'color') {
                clearMobileInteractionDrawerReturn();
            }
            document.body.classList.toggle('aa-mobile-interaction-drawer-open', panelKey === 'element-interaction');
            if (isMobileEditorInteractionDrawer() && panelKey !== 'element-interaction' && els.aaMobileInteractionDrawer) {
                syncMobileInteractionDrawer(null);
                restoreMobileInteractionDrawerHome();
            }
            const leftbar = document.querySelector('.aa-leftbar');
            leftbar?.classList.add('is-drawer-open');
            leftbar?.classList.toggle('is-acara-ai-drawer', panelKey === 'import-reference');
            document.querySelectorAll('[data-aa-left-tab]').forEach(item => item.classList.toggle(
                'is-active', item.dataset.aaLeftTab === panelKey));
            document.querySelectorAll('[data-aa-left-panel]').forEach(panel => panel.classList.toggle(
                'is-active', panel.dataset.aaLeftPanel === panelKey));
        }

        function closeLeftDrawerPanel() {
            clearFontDrawerPreview();
            clearMobileInteractionDrawerReturn();
            document.body.classList.remove('aa-mobile-interaction-drawer-open');
            const leftbar = document.querySelector('.aa-leftbar');
            leftbar?.classList.remove('is-drawer-open');
            leftbar?.classList.remove('is-acara-ai-drawer');
        }

        function getActiveLeftDrawerPanelKey() {
            return document.querySelector('[data-aa-left-panel].is-active')?.dataset.aaLeftPanel || '';
        }

        function updateColorDrawerTargetForSelection(active) {
            const target = state.colorDrawerTargetInput;
            if (!target) return true;
            const id = target.id || '';
            const hasTextTarget = Boolean(getContextTextTarget(active));
            const isGuestField = isGuestbookObject(active);
            const isCountdown = active?.customType === 'countdown-timer';
            const isMusic = active?.customType === 'music-player';
            const isOpening = active?.customType === 'opening-button';
            const isInteractive = isInteractiveObject(active);
            const isObjectColor = active && active !== state.cropBox && !isContextTextObject(active) && !isOpening;

            if (id === 'aaTextContextColorInput') return hasTextTarget;
            if (id === 'aaContextColorInput') return Boolean(isObjectColor);
            if (id === 'aaBackgroundInput') return !active || active.customType === 'background';
            if (id === 'aaMusicPopoverBgInput' || id === 'aaMusicDrawerBgInput') return isMusic;
            if (id === 'aaCountdownContextBgInput' || id === 'aaCountdownContextColorInput') return isCountdown;
            if (id === 'aaGuestFieldPopoverBgInput' || id === 'aaGuestFieldPopoverColorInput' ||
                id === 'aaMobileGuestFieldBgInput' || id === 'aaMobileGuestFieldColorInput') return isGuestField;
            if (id === 'aaOpeningButtonBgInput' || id === 'aaOpeningButtonTextColorInput' ||
                id === 'aaMobileOpeningButtonBgInput' || id === 'aaMobileOpeningButtonTextColorInput') return isOpening;
            if (id === 'aaYoutubePopoverBgInput' || id === 'aaMobileYoutubeBgInput') return isYoutubeInteractionObject(active);
            if (id === 'aaInteractiveBgInput') return Boolean(isInteractive && !isMusic && !isOpening && !isCountdown && active?.customType !== 'photo-gallery');
            if (id === 'aaGuestNameBgInput' || id === 'aaGuestNameCloseInput') return isGuestNameObject(active);
            return true;
        }

        function syncColorDrawerForSelection(active) {
            if (getActiveLeftDrawerPanelKey() !== 'color') return;
            if (!active || !updateColorDrawerTargetForSelection(active)) {
                state.colorDrawerTargetInput = null;
                closeLeftDrawerPanel();
                return;
            }
            const target = state.colorDrawerTargetInput;
            const parsed = parseAlphaDrawerColor(target?.dataset.aaAlphaColor || target?.value || '#111827');
            const color = parsed.hex;
            syncColorDrawerPickerUi(color);
            syncColorDrawerAlphaControls(target);
            syncColorDrawerMaterialSection();
        }

        function syncFontDrawerForSelection(active) {
            if (getActiveLeftDrawerPanelKey() !== 'font') return;
            if (!active || !getFontDrawerPreviewTarget()) {
                closeLeftDrawerPanel();
                return;
            }
            clearFontDrawerPreview();
            renderFontDrawerList();
        }

        function syncOpenLeftDrawerForSelection() {
            const active = state.canvas?.getActiveObject?.();
            if (!document.querySelector('.aa-leftbar')?.classList.contains('is-drawer-open')) return;
            const panelKey = getActiveLeftDrawerPanelKey();

            if (panelKey === 'font') {
                syncFontDrawerForSelection(active);
                return;
            }
            if (panelKey === 'color') {
                syncColorDrawerForSelection(active);
                return;
            }
            if (panelKey === 'music') {
                if (!isMusicInteractionObject(active)) {
                    closeLeftDrawerPanel();
                    return;
                }
                syncMusicDrawerForSelection(active);
                return;
            }
            if (panelKey === 'element-interaction') {
                if (!isLinkInteractionObject(active) &&
                    !isSocialLinkInteractionObject(active) &&
                    !isCopyInteractionObject(active) &&
                    !isYoutubeInteractionObject(active) &&
                    !isOpeningButtonInteractionObject(active) &&
                    !isGuestFieldInteractionObject(active)) {
                    closeLeftDrawerPanel();
                    return;
                }
                syncInteractionPopover(active);
                return;
            }
            if (panelKey === 'animation') {
                if (!active || active === state.cropBox || state.isCropping ||
                    (typeof aaIsPhotoboothPhotoSlot === 'function' && aaIsPhotoboothPhotoSlot(active))) {
                    closeLeftDrawerPanel();
                    return;
                }
                syncAnimationButtons(active);
                syncTextAnimationToolbar(active);
                return;
            }
            if (panelKey === 'image-effects') {
                if (!active || active.type !== 'image') {
                    closeLeftDrawerPanel();
                    return;
                }
                syncImageEffectButtons(active);
                syncImageOverlayButtons(active);
                syncImageFrameButtons(active);
                syncImageEffectPreviewSource(active);
                return;
            }
            if (panelKey === 'image-outline') {
                const outlineCandidate = typeof isImageOutlineCandidate === 'function' ?
                    isImageOutlineCandidate(active) :
                    active?.type === 'image';
                const canOutline = outlineCandidate && (typeof isImageOutlineTarget === 'function' ?
                    isImageOutlineTarget(active) :
                    active?.type === 'image');
                const outlineBusy = Boolean(
                    state?.keepImageOutlineDrawerOpen === true ||
                    state?.imageProcessTarget != null ||
                    state?.imageOutlineApplyTimer != null ||
                    state?.pendingImageOutlineOptions != null
                );
                if (!canOutline) {
                    if (outlineCandidate && typeof syncImageOutlineAvailability === 'function') {
                        syncImageOutlineAvailability(active);
                    }
                    if (outlineBusy) {
                        return;
                    }
                    closeLeftDrawerPanel();
                    return;
                }
                syncImageOutlineControl(active);
                if (typeof window.aaSyncOutlineColorPicker === 'function') {
                    window.aaSyncOutlineColorPicker(active.aaImageOutlineDraftColor || active.aaImageOutlineColor || '#ffffff');
                }
                return;
            }
            if (panelKey === 'image-frame') {
                if (!active || active.type !== 'image') {
                    closeLeftDrawerPanel();
                    return;
                }
                syncImageFrameButtons(active);
            }
        }

        function openFontDrawer(target = 'text') {
            clearFontDrawerPreview();
            rememberMobileInteractionDrawerReturn('font');
            state.fontDrawerTarget = target;
            closeToolbarPopovers('font-drawer');
            openLeftDrawerPanel('font');
            renderFontDrawerList();
            if (window.matchMedia('(max-width: 767px)').matches) return;
            requestAnimationFrame(() => els.aaFontDrawerSearch?.focus({
                preventScroll: true,
            }));
        }

        function normalizeDrawerColor(value, fallback = '#111827') {
            const color = String(value || '').trim();
            if (/^#[0-9a-f]{6}$/i.test(color)) return color.toLowerCase();
            if (/^#[0-9a-f]{3}$/i.test(color)) {
                return ('#' + color.slice(1).split('').map(char => char + char).join('')).toLowerCase();
            }
            if (/^rgba?\(/i.test(color)) {
                return parseAlphaDrawerColor(color, fallback || '#111827').hex;
            }
            return fallback;
        }

        function clampDrawerAlpha(value) {
            const number = Number(value);
            if (!Number.isFinite(number)) return 1;
            return Math.max(0, Math.min(1, number));
        }

        function normalizeDrawerHex(value, fallback = '#111827') {
            const color = String(value || '').trim();
            if (/^#[0-9a-f]{6}$/i.test(color)) return color.toLowerCase();
            if (/^#[0-9a-f]{3}$/i.test(color)) {
                return ('#' + color.slice(1).split('').map(char => char + char).join('')).toLowerCase();
            }
            return fallback;
        }

        function parseAlphaDrawerColor(value, fallback = '#111827') {
            const raw = String(value || '').trim();
            const fallbackHex = normalizeDrawerHex(fallback || '#111827', '#111827');
            if (!raw) return {
                hex: fallbackHex,
                alpha: 1
            };
            const hexMatch = raw.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
            if (hexMatch) return {
                hex: normalizeDrawerHex(raw, fallbackHex),
                alpha: 1
            };
            const rgbaMatch = raw.match(/^rgba?\(\s*([0-9.]+)\s*,\s*([0-9.]+)\s*,\s*([0-9.]+)(?:\s*,\s*([0-9.]+)\s*)?\)$/i);
            if (rgbaMatch) {
                const red = Math.max(0, Math.min(255, Math.round(Number(rgbaMatch[1]) || 0)));
                const green = Math.max(0, Math.min(255, Math.round(Number(rgbaMatch[2]) || 0)));
                const blue = Math.max(0, Math.min(255, Math.round(Number(rgbaMatch[3]) || 0)));
                const alpha = rgbaMatch[4] == null ? 1 : clampDrawerAlpha(rgbaMatch[4]);
                return {
                    hex: '#' + [red, green, blue].map(part => part.toString(16).padStart(2, '0')).join(''),
                    alpha
                };
            }
            try {
                return {
                    hex: '#' + new fabric.Color(raw).toHex().toLowerCase(),
                    alpha: 1
                };
            } catch (error) {
                return {
                    hex: fallbackHex,
                    alpha: 1
                };
            }
        }

        function composeAlphaDrawerColor(hex, alpha = 1) {
            const safeHex = normalizeDrawerHex(hex, '#111827');
            const safeAlpha = clampDrawerAlpha(alpha);
            if (safeAlpha >= .995) return safeHex;
            const red = parseInt(safeHex.slice(1, 3), 16);
            const green = parseInt(safeHex.slice(3, 5), 16);
            const blue = parseInt(safeHex.slice(5, 7), 16);
            const alphaText = String(Math.round(safeAlpha * 100) / 100).replace(/0+$/, '').replace(/\.$/, '');
            return 'rgba(' + red + ', ' + green + ', ' + blue + ', ' + alphaText + ')';
        }

        function clampColorDrawerUnit(value) {
            return Math.max(0, Math.min(1, Number(value) || 0));
        }

        function colorDrawerRgbToHex(red, green, blue) {
            return '#' + [red, green, blue].map(value => {
                const safe = Math.max(0, Math.min(255, Math.round(Number(value) || 0)));
                return safe.toString(16).padStart(2, '0');
            }).join('').toLowerCase();
        }

        function colorDrawerHexToRgb(value) {
            const hex = normalizeDrawerHex(value || '#111827', '#111827').replace('#', '');
            return {
                red: parseInt(hex.slice(0, 2), 16),
                green: parseInt(hex.slice(2, 4), 16),
                blue: parseInt(hex.slice(4, 6), 16),
            };
        }

        function colorDrawerHsvToHex(hue, saturation, value) {
            const h = ((Number(hue) || 0) % 360 + 360) % 360;
            const s = clampColorDrawerUnit(saturation);
            const v = clampColorDrawerUnit(value);
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
            return colorDrawerRgbToHex((red + m) * 255, (green + m) * 255, (blue + m) * 255);
        }

        function colorDrawerHexToHsv(value) {
            const rgb = colorDrawerHexToRgb(value);
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
        }

        function syncColorDrawerPickerUi(color) {
            const safeColor = normalizeDrawerHex(color || '#111827', '#111827');
            const hsv = colorDrawerHexToHsv(safeColor);
            state.colorDrawerPicker = hsv;
            const hueColor = colorDrawerHsvToHex(hsv.hue, 1, 1);
            if (els.aaColorDrawerInput) els.aaColorDrawerInput.value = safeColor;
            if (els.aaColorDrawerHexInput) els.aaColorDrawerHexInput.value = safeColor.toUpperCase();
            if (els.aaColorPickerPreviewText) els.aaColorPickerPreviewText.textContent = safeColor.toUpperCase();
            if (els.aaColorPickerPreview) els.aaColorPickerPreview.style.setProperty('--aa-drawer-current', safeColor);
            if (els.aaColorPickerHueBar) {
                els.aaColorPickerHueBar.style.setProperty('--aa-drawer-hue-y', `${(hsv.hue / 360) * 100}%`);
            }
            if (els.aaColorPickerField) {
                els.aaColorPickerField.style.setProperty('--aa-drawer-hue', hueColor);
                els.aaColorPickerField.style.setProperty('--aa-drawer-handle-x', `${hsv.saturation * 100}%`);
                els.aaColorPickerField.style.setProperty('--aa-drawer-handle-y', `${(1 - hsv.value) * 100}%`);
            }
        }

        function isAlphaDrawerTarget(input) {
            if (!input) return false;
            return [
                'aaMusicPopoverBgInput',
                'aaCountdownContextBgInput'
            ].indexOf(input.id || '') !== -1;
        }

        function setAlphaColorInputValue(input, value, fallback = '#ffffff') {
            if (!input) return;
            const parsed = parseAlphaDrawerColor(value, fallback);
            const composed = composeAlphaDrawerColor(parsed.hex, parsed.alpha);
            input.value = parsed.hex;
            input.dataset.aaAlphaColor = composed;
        }

        function getAlphaColorInputValue(input, fallback = '#ffffff') {
            if (!input) return fallback;
            if (isAlphaDrawerTarget(input)) {
                const parsed = parseAlphaDrawerColor(input.dataset.aaAlphaColor || input.value || fallback, fallback);
                return composeAlphaDrawerColor(parsed.hex, parsed.alpha);
            }
            return input.value || fallback;
        }

        function syncColorDrawerAlphaControls(targetInput) {
            const supportsAlpha = isAlphaDrawerTarget(targetInput);
            els.aaColorDrawerAlphaWrap?.classList.toggle('hidden', !supportsAlpha);
            if (!supportsAlpha) return;
            const parsed = parseAlphaDrawerColor(targetInput?.dataset.aaAlphaColor || targetInput?.value || '#111827');
            const value = Math.round(parsed.alpha * 100);
            if (els.aaColorDrawerAlphaInput) els.aaColorDrawerAlphaInput.value = String(value);
            if (els.aaColorDrawerAlphaValue) els.aaColorDrawerAlphaValue.textContent = value + '%';
            if (els.aaColorDrawerAlphaPreview) {
                els.aaColorDrawerAlphaPreview.style.setProperty('--aa-alpha-preview', composeAlphaDrawerColor(parsed.hex, parsed.alpha));
            }
        }

        function openColorDrawer(targetInput, title = 'Color') {
            if (!targetInput) return;
            rememberMobileInteractionDrawerReturn('color');
            state.colorDrawerTargetInput = targetInput;
            state.colorDrawerTitle = title;
            closeToolbarPopovers('color-drawer');
            const parsed = parseAlphaDrawerColor(targetInput.dataset.aaAlphaColor || targetInput.value || '#111827');
            const color = parsed.hex;
            syncColorDrawerPickerUi(color);
            syncColorDrawerAlphaControls(targetInput);
            if (els.aaColorDrawerHint) {
                els.aaColorDrawerHint.textContent = title + ' aktif. Pilih warna dari drawer kiri.' + (isAlphaDrawerTarget(targetInput) ? ' Atur opacity untuk background transparan.' : '');
            }
            syncColorDrawerMaterialSection();
            openLeftDrawerPanel('color');
        }

        function aaGetMaterialTarget() {
            if (!state.canvas) return null;
            const active = state.canvas.getActiveObject();
            if (!active || active === state.cropBox || active.type === 'image') return null;
            const textTarget = typeof getContextTextTarget === 'function' ? getContextTextTarget(active) : null;
            if (aaIsMaterialTextObject(textTarget)) return textTarget;
            return aaIsMaterialTextObject(active) ? active : null;
        }

        function aaMaterialSpec(preset) {
            const specs = {
                gold: { type: 'foil', fallback: '#d4af37', colors: ['#fff7bf', '#d4af37', '#8f6b12', '#f8e18a'] },
                copper: { type: 'foil', fallback: '#c8753f', colors: ['#ffd7b0', '#c8753f', '#7c341c', '#f5a66d'] },
                blue: { type: 'foil', fallback: '#4da3c7', colors: ['#c8f2ff', '#4da3c7', '#1e5571', '#9bd8ef'] },
                pearl: { type: 'foil', fallback: '#dbeafe', colors: ['#f8fafc', '#dbeafe', '#f5d0fe', '#ccfbf1'] },
                red: { type: 'foil', fallback: '#dc2626', colors: ['#fecaca', '#dc2626', '#7f1d1d', '#f87171'] },
                rose: { type: 'foil', fallback: '#be6b75', colors: ['#ffe4e6', '#be6b75', '#7f3540', '#f3a6ad'] },
                silver: { type: 'foil', fallback: '#cbd5e1', colors: ['#ffffff', '#cbd5e1', '#64748b', '#e2e8f0'] },
                'gold-glitter': { type: 'glitter', fallback: '#d4af37', colors: ['#facc15', '#8f6b12', '#fff7ad'] },
                'silver-glitter': { type: 'glitter', fallback: '#cbd5e1', colors: ['#f8fafc', '#64748b', '#ffffff'] },
                'black-glitter': { type: 'glitter', fallback: '#111827', colors: ['#334155', '#020617', '#f8fafc'] },
                'aqua-glitter': { type: 'glitter', fallback: '#14b8a6', colors: ['#22d3ee', '#0f766e', '#cffafe'] },
                'emerald-glitter': { type: 'glitter', fallback: '#047857', colors: ['#10b981', '#064e3b', '#bbf7d0'] },
                'rose-glitter': { type: 'glitter', fallback: '#be185d', colors: ['#f9a8d4', '#be185d', '#fff1f2'] },
                'pink-glitter': { type: 'glitter', fallback: '#db2777', colors: ['#f472b6', '#db2777', '#fce7f3'] },
                'purple-glitter': { type: 'glitter', fallback: '#7e22ce', colors: ['#c084fc', '#7e22ce', '#f3e8ff'] },
            };
            return specs[preset] || null;
        }

        function aaIsMaterialTextObject(target) {
            return target && ['i-text', 'textbox', 'text'].includes(String(target.type || ''));
        }

        function syncColorDrawerMaterialSection() {
            if (!els.aaColorMaterialSection) return;
            const targetId = state.colorDrawerTargetInput?.id || '';
            const colorTargetIsText = targetId === 'aaTextContextColorInput' || targetId === 'aaColorInput';
            els.aaColorMaterialSection.classList.toggle('hidden', !(colorTargetIsText && aaGetMaterialTarget()));
        }

        function aaMaterialPatternSize(target = null) {
            const isText = aaIsMaterialTextObject(target);
            const padding = isText ? Math.max(24, Math.round((Number(target?.fontSize) || 32) * 0.55)) : 0;
            const width = Math.abs(Number(target?.width) || 0) || 144;
            const height = Math.abs(Number(target?.height) || 0) || 144;

            return {
                width: Math.max(144, Math.min(760, Math.ceil(width + padding * 2))),
                height: Math.max(144, Math.min(760, Math.ceil(height + padding * 2))),
                padding,
            };
        }

        function aaCreateFoilFill(target, spec) {
            const width = Math.max(1, Number(target?.width) || 1);
            const height = Math.max(1, Number(target?.height) || 1);
            return new fabric.Gradient({
                type: 'linear',
                gradientUnits: 'pixels',
                coords: { x1: -width / 2, y1: height / 2, x2: width / 2, y2: -height / 2 },
                colorStops: [
                    { offset: 0, color: spec.colors[2] },
                    { offset: .12, color: spec.colors[1] },
                    { offset: .22, color: spec.colors[0] },
                    { offset: .34, color: spec.colors[3] || spec.colors[0] },
                    { offset: .46, color: spec.colors[1] },
                    { offset: .56, color: spec.colors[2] },
                    { offset: .68, color: spec.colors[0] },
                    { offset: .82, color: spec.colors[1] },
                    { offset: 1, color: spec.colors[3] || spec.colors[0] },
                ]
            });
        }

        function aaCreateGlitterFill(spec, target = null) {
            const patternSize = aaMaterialPatternSize(target);
            const sourceWidth = patternSize.width;
            const sourceHeight = patternSize.height;
            const canvas = document.createElement('canvas');
            canvas.width = sourceWidth;
            canvas.height = sourceHeight;
            const ctx = canvas.getContext('2d');
            if (!ctx) return spec.fallback;
            const gradient = ctx.createLinearGradient(0, 0, sourceWidth, sourceHeight);
            gradient.addColorStop(0, spec.colors[0]);
            gradient.addColorStop(.52, spec.fallback || spec.colors[0]);
            gradient.addColorStop(1, spec.colors[1]);
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, sourceWidth, sourceHeight);
            const areaScale = Math.max(1, (sourceWidth * sourceHeight) / (144 * 144));
            const particleCount = Math.min(9000, Math.round(1150 * areaScale));
            for (let i = 0; i < particleCount; i += 1) {
                const x = (i * 29 + (i % 7) * 17 + (i % 11) * 5) % sourceWidth;
                const y = (i * 47 + (i % 5) * 19 + (i % 13) * 7) % sourceHeight;
                const radius = .35 + ((i * 19) % 18) / 18;
                ctx.beginPath();
                ctx.fillStyle = i % 9 === 0 ? spec.colors[2] : (i % 4 === 0 ? 'rgba(255,255,255,.9)' : (i % 3 === 0 ? 'rgba(255,255,255,.58)' : 'rgba(0,0,0,.18)'));
                ctx.arc(x, y, radius, 0, Math.PI * 2);
                ctx.fill();
            }
            ctx.globalCompositeOperation = 'screen';
            const sparkleCount = Math.min(1600, Math.round(210 * areaScale));
            for (let i = 0; i < sparkleCount; i += 1) {
                const x = (i * 41 + (i % 9) * 13) % sourceWidth;
                const y = (i * 23 + (i % 7) * 11) % sourceHeight;
                ctx.fillStyle = i % 2 === 0 ? 'rgba(255,255,255,.86)' : 'rgba(255,255,255,.52)';
                ctx.fillRect(x, y, 1.35, 1.35);
            }
            ctx.globalCompositeOperation = 'source-over';
            return new fabric.Pattern({
                source: canvas,
                repeat: 'no-repeat',
                offsetX: -patternSize.padding,
                offsetY: -patternSize.padding,
            });
        }

        function aaApplyMaterialToObject(target, preset, shouldCommit = true) {
            const spec = aaMaterialSpec(preset);
            if (!target || !spec || !window.fabric) return false;
            if (!aaIsMaterialTextObject(target)) return false;
            const fill = spec.type === 'glitter' ? aaCreateGlitterFill(spec, target) : aaCreateFoilFill(target, spec);
            const isText = aaIsMaterialTextObject(target);
            target.set({
                fill,
                aaMaterialType: spec.type,
                aaMaterialPreset: preset,
                aaMaterialFallback: spec.fallback,
                ...(isText ? {
                    objectCaching: false,
                    noScaleCache: true,
                } : {})
            });
            target.dirty = true;
            if (!isText && typeof target.initDimensions === 'function') target.initDimensions();
            target.setCoords?.();
            state.canvas?.requestRenderAll();
            syncInspector?.();
            syncContextToolbar?.();
            syncTextContextToolbar?.();
            if (shouldCommit) snapshot?.();
            return true;
        }

        window.aaRestoreCanvasMaterials = function(canvas) {
            if (!canvas || !canvas.getObjects || !window.fabric) return;
            const restoreOne = object => {
                if (!object) return;
                if (typeof object.getObjects === 'function') {
                    object.getObjects().forEach(restoreOne);
                }
                const preset = String(object.aaMaterialPreset || '');
                if (!preset || object.type === 'image') return;
                const spec = aaMaterialSpec(preset);
                if (!spec) return;
                const isText = aaIsMaterialTextObject(object);
                object.set({
                    fill: spec.type === 'glitter' ? aaCreateGlitterFill(spec, object) : aaCreateFoilFill(object, spec),
                    ...(isText ? {
                        objectCaching: false,
                        noScaleCache: true,
                    } : {})
                });
                object.aaMaterialType = spec.type;
                object.aaMaterialFallback = spec.fallback;
                object.dirty = true;
                if (!isText && typeof object.initDimensions === 'function') object.initDimensions();
                object.setCoords?.();
            };
            canvas.getObjects().forEach(restoreOne);
            canvas.requestRenderAll?.();
        };

        function aaClearMaterialMetadata(target) {
            if (!target) return;
            target.set({
                aaMaterialType: '',
                aaMaterialPreset: '',
                aaMaterialFallback: ''
            });
        }

        function applyColorDrawerValue(value, shouldCommit = false) {
            const color = normalizeDrawerColor(value, null);
            if (!color) return;
            syncColorDrawerPickerUi(color);
            const targetInput = state.colorDrawerTargetInput;
            if (!targetInput || targetInput === els.aaColorDrawerInput) return;
            const active = state.canvas?.getActiveObject?.();
            const target = typeof getContextTextTarget === 'function' ? (getContextTextTarget(active) || active) : active;
            if (target && target.type !== 'image') {
                aaClearMaterialMetadata(target);
            }
            if (isAlphaDrawerTarget(targetInput)) {
                const alpha = clampDrawerAlpha(Number(els.aaColorDrawerAlphaInput?.value || 100) / 100);
                targetInput.dataset.aaAlphaColor = composeAlphaDrawerColor(color, alpha);
                syncColorDrawerAlphaControls(targetInput);
            }
            targetInput.value = color;
            targetInput.dispatchEvent(new Event('input', {
                bubbles: true
            }));
            if (shouldCommit) {
                targetInput.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }
        }

        function bindColorInputsToDrawer() {
            document.querySelectorAll('input[type="color"]').forEach(input => {
                if (input === els.aaColorDrawerInput ||
                    input.dataset.aaSkipColorDrawer === '1' ||
                    input.dataset.aaDrawerBound === '1') return;
                input.dataset.aaDrawerBound = '1';
                input.addEventListener('pointerdown', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    const label = input.closest('label')?.textContent?.trim()?.replace(/\s+/g, ' ') ||
                        input.getAttribute('aria-label') || 'Color';
                    openColorDrawer(input, label);
                });
                input.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    openColorDrawer(input);
                });
            });
        }

        function syncFontDrawerInputs(family) {
            if (state.fontDrawerTarget === 'countdown' && els.aaCountdownContextFontInput) {
                els.aaCountdownContextFontInput.value = family;
            } else if (state.fontDrawerTarget === 'guest-field') {
                if (els.aaGuestFieldPopoverFontInput) els.aaGuestFieldPopoverFontInput.value = family;
                if (els.aaMobileGuestFieldFontInput) els.aaMobileGuestFieldFontInput.value = family;
            } else if (state.fontDrawerTarget === 'opening-button') {
                if (els.aaOpeningButtonFontInput) els.aaOpeningButtonFontInput.value = family;
                if (els.aaMobileOpeningButtonFontInput) els.aaMobileOpeningButtonFontInput.value = family;
            } else if (state.fontDrawerTarget === 'panel-text' && els.aaFontInput) {
                els.aaFontInput.value = family;
            } else if (els.aaTextContextFont) {
                els.aaTextContextFont.value = family;
            }
            if (els.aaFontInput) {
                els.aaFontInput.value = family;
            }
        }

        function applyFontDrawerSelection(fontFamily, options = {}) {
            const family = cleanFontFamilyValue(fontFamily);
            const fontWeight = normalizeFontDrawerWeight(options.fontWeight || getDefaultFontDrawerWeight(family), '');
            clearFontDrawerPreview({
                restore: false,
            });
            rememberSelectedFontDrawerWeight(family, fontWeight);
            syncFontDrawerInputs(family);
            loadEditorFontFamily(family, loadedFamily => {
                const active = state.canvas?.getActiveObject();
                if (state.fontDrawerTarget === 'opening-button' && active?.set) {
                    active.set('openingButtonFontFamily', loadedFamily);
                }
                const style = {
                    fontFamily: loadedFamily,
                };
                if (fontWeight) {
                    style.fontWeight = fontWeight;
                }
                applyActiveStyle(style);
                syncInspector();
                syncTextContextToolbar();
                syncInteractionPopover?.();
                renderFontDrawerList();
            });
        }

        function bindFontDrawerTrigger(element, target) {
            if (!element) return;
            const open = event => {
                event.preventDefault();
                event.stopPropagation();
                openFontDrawer(target);
            };
            element.addEventListener('pointerdown', open);
            element.addEventListener('keydown', event => {
                if (!['Enter', ' ', 'ArrowDown'].includes(event.key)) return;
                open(event);
            });
        }

        function stepTextContextFontSize(direction) {
            const target = getContextTextTarget();
            if (!target) return;
            const current = Math.round(Number(target.fontSize) || 42);
            const next = Math.max(8, Math.min(260, current + direction));
            applyActiveStyle({
                fontSize: next,
            });
            syncInspector();
        }

        function cycleTextContextAlign() {
            const target = getContextTextTarget();
            if (!target) return;
            const values = ['left', 'center', 'right', 'justify'];
            const current = values.indexOf(target.textAlign || 'left');
            applyActiveStyle({
                textAlign: values[(current + 1) % values.length],
            });
            syncInspector();
        }

        function toggleTextContextCase() {
            const target = getContextTextTarget();
            const active = state.canvas.getActiveObject();
            if (!target || typeof target.text !== 'string') return;
            const text = target.text;
            const next = text === text.toUpperCase() ? text.toLowerCase() : text.toUpperCase();
            applyActiveStyle({
                text: next,
            });
            if (els.aaTextInput && state.canvas.getActiveObject() === active) {
                els.aaTextInput.value = next;
            }
            syncInspector();
        }

        function toggleTextContextList() {
            const target = getContextTextTarget();
            if (!target || typeof target.text !== 'string') return;
            const lines = target.text.split('\n');
            const hasList = lines.every(line => !line.trim() || line.trim().startsWith('- '));
            const next = lines.map(line => {
                if (!line.trim()) return line;
                return hasList ? line.replace(/^\s*-\s?/, '') : '- ' + line.replace(/^\s*-\s?/, '');
            }).join('\n');
            applyActiveStyle({
                text: next,
            });
            if (els.aaTextInput) {
                els.aaTextInput.value = next;
            }
            syncInspector();
        }

        function getTextEffectTarget() {
            return getContextTextTarget();
        }

        function getTextShadowValues(target) {
            const shadow = target?.shadow;
            return {
                color: normalizeColor(shadow?.color || '#000000'),
                blur: Math.max(0, Math.round(Number(shadow?.blur) || 0)),
                offsetX: Math.round(Number(shadow?.offsetX) || 0),
                offsetY: Math.round(Number(shadow?.offsetY) || 0),
            };
        }

        const textEffectPresets = {
            none: {
                strokeWidth: 0,
                shadowBlur: 0,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
                charSpacing: 0,
                lineHeight: 1.14,
            },
            'soft-shadow': {
                strokeWidth: 0,
                shadowBlur: 14,
                shadowOffsetX: 0,
                shadowOffsetY: 8,
            },
            glow: {
                strokeWidth: 0,
                shadowBlur: 24,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
            },
            outline: {
                strokeWidth: 3,
                shadowBlur: 0,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
            },
            luxury: {
                strokeWidth: 2,
                shadowBlur: 18,
                shadowOffsetX: 0,
                shadowOffsetY: 10,
            },
            neon: {
                strokeWidth: 0,
                shadowBlur: 30,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
            },
        };

        function getTextEffectPresetName(target) {
            const name = target?.aaTextEffectPreset || 'none';
            return textEffectPresets[name] ? name : 'none';
        }

        function getTextEffectOutlineColor(target) {
            return normalizeColor(target?.aaTextEffectOutlineColor || target?.stroke || '#111827');
        }

        function getTextEffectShadowColor(target) {
            const shadow = target?.shadow;
            return normalizeColor(target?.aaTextEffectShadowColor || shadow?.color || target?.fill || '#000000');
        }

        function syncTextEffectPresetButtons(target) {
            const activePreset = getTextEffectPresetName(target);
            document.querySelectorAll('[data-aa-text-effect-preset]').forEach(button => {
                button.classList.toggle('is-active', button.dataset.aaTextEffectPreset === activePreset);
            });
        }

        function positionTextEffectsPopover() {
            if (!els.aaTextEffectsPopover || !els.aaTextContextEffectsBtn) return;
            const rect = els.aaTextContextEffectsBtn.getBoundingClientRect();
            const popoverWidth = els.aaTextEffectsPopover.offsetWidth || 360;
            const left = Math.min(window.innerWidth - popoverWidth / 2 - 12, Math.max(popoverWidth / 2 + 12,
                rect.left + rect.width / 2));
            els.aaTextEffectsPopover.style.left = `${left}px`;
            els.aaTextEffectsPopover.style.top = `${rect.bottom + 8}px`;
        }

        function syncTextEffectsControl(target = getTextEffectTarget()) {
            if (!target || !els.aaTextEffectsPopover) {
                closeTextEffectsPopover();
                return;
            }

            const strokeWidth = Math.max(0, Math.round(Number(target.strokeWidth) || 0));
            if (els.aaTextEffectStrokeColor) {
                els.aaTextEffectStrokeColor.value = getTextEffectOutlineColor(target);
            }
            if (els.aaTextEffectStrokeWidth) els.aaTextEffectStrokeWidth.value = strokeWidth;
            if (els.aaTextEffectStrokeValue) els.aaTextEffectStrokeValue.textContent = String(strokeWidth);

            const shadow = getTextShadowValues(target);
            if (els.aaTextEffectShadowColor) els.aaTextEffectShadowColor.value = getTextEffectShadowColor(target);
            if (els.aaTextEffectShadowBlur) els.aaTextEffectShadowBlur.value = shadow.blur;
            if (els.aaTextEffectShadowBlurValue) els.aaTextEffectShadowBlurValue.textContent = String(shadow.blur);
            if (els.aaTextEffectShadowOffsetX) els.aaTextEffectShadowOffsetX.value = shadow.offsetX;
            if (els.aaTextEffectShadowOffsetXValue) {
                els.aaTextEffectShadowOffsetXValue.textContent = String(shadow.offsetX);
            }
            if (els.aaTextEffectShadowOffsetY) els.aaTextEffectShadowOffsetY.value = shadow.offsetY;
            if (els.aaTextEffectShadowOffsetYValue) {
                els.aaTextEffectShadowOffsetYValue.textContent = String(shadow.offsetY);
            }

            const charSpacing = Math.round(Number(target.charSpacing) || 0);
            const lineHeight = Math.max(.8, Math.min(2.4, Number(target.lineHeight) || 1.14));
            if (els.aaTextEffectCharSpacing) els.aaTextEffectCharSpacing.value = charSpacing;
            if (els.aaTextEffectCharSpacingValue) {
                els.aaTextEffectCharSpacingValue.textContent = String(charSpacing);
            }
            if (els.aaTextEffectLineHeight) els.aaTextEffectLineHeight.value = Math.round(lineHeight * 100);
            if (els.aaTextEffectLineHeightValue) {
                els.aaTextEffectLineHeightValue.textContent = lineHeight.toFixed(2);
            }
            syncTextEffectPresetButtons(target);
        }

        function openTextEffectsPopover() {
            const target = getTextEffectTarget();
            if (!target || state.isCropping) return;
            closeToolbarPopovers('text-effects');
            syncTextEffectsControl(target);
            els.aaTextEffectsPopover?.classList.add('is-open');
            els.aaTextContextEffectsBtn?.classList.add('is-active');
            requestAnimationFrame(positionTextEffectsPopover);
        }

        function closeTextEffectsPopover() {
            els.aaTextEffectsPopover?.classList.remove('is-open');
            els.aaTextContextEffectsBtn?.classList.remove('is-active');
        }

        function toggleTextEffectsPopover() {
            if (els.aaTextEffectsPopover?.classList.contains('is-open')) {
                closeTextEffectsPopover();
                return;
            }
            openTextEffectsPopover();
        }

        function positionAnimationPopover() {
            const trigger = state.animationPopoverTrigger || els.aaContextAnimateBtn || els.aaTextContextAnimateBtn;
            if (!els.aaAnimationPopover || !trigger) return;
            const rect = trigger.getBoundingClientRect();
            const popoverWidth = els.aaAnimationPopover.offsetWidth || 360;
            const popoverHeight = els.aaAnimationPopover.offsetHeight || 420;
            const left = Math.min(window.innerWidth - popoverWidth - 12, Math.max(12, rect.left));
            let top = rect.bottom + 8;
            if (top + popoverHeight > window.innerHeight - 12) {
                top = Math.max(12, rect.top - popoverHeight - 8);
            }
            els.aaAnimationPopover.style.left = `${left}px`;
            els.aaAnimationPopover.style.top = `${top}px`;
        }
        const AA_ANIMATION_DEFAULTS = {
    name: 'none',
    duration: 900,
    delay: 0,
    stagger: 70,
};

const AA_ANIMATION_PROPS = [
    'aaAnimation',
    'aaAnimationName',
    'aaAnimationDuration',
    'aaAnimationDelay',
    'aaAnimationStagger',
    'aaAnimationOnce',

    // fallback nama lama
    'animation',
    'animationName',
    'animationDuration',
    'animationDelay',
    'animationStagger',
];

function aaNumber(value, fallback = 0, min = 0, max = 999999) {
    const number = Number(value);

    if (!Number.isFinite(number)) {
        return fallback;
    }

    return Math.max(min, Math.min(max, Math.round(number)));
}

function aaGetAnimationTarget(active = state.canvas?.getActiveObject?.()) {
    if (!active || active === state.cropBox || state.isCropping) return null;

    // Opening button harus animasi di group utama, bukan anak text/box.
    if (active.customType === 'opening-button') {
        return active;
    }

    // Text biasa tetap object text.
    if (
        active.type === 'i-text' ||
        active.type === 'textbox' ||
        active.type === 'text'
    ) {
        return active;
    }

    // Guest name group: animasi ke group utama supaya preview/publish ikut.
    if (typeof isGuestNameObject === 'function' && isGuestNameObject(active)) {
        return active;
    }

    // Interactive object lain: animasi ke group utama.
    return active;
}

function aaGetAnimationName(object) {
    return (
        object?.aaAnimationName ||
        object?.aaAnimation ||
        object?.animationName ||
        object?.animation ||
        AA_ANIMATION_DEFAULTS.name
    );
}

function aaGetAnimationDuration(object) {
    return aaNumber(
        object?.aaAnimationDuration ?? object?.animationDuration,
        AA_ANIMATION_DEFAULTS.duration,
        50,
        30000
    );
}

function aaGetAnimationDelay(object) {
    return aaNumber(
        object?.aaAnimationDelay ?? object?.animationDelay,
        AA_ANIMATION_DEFAULTS.delay,
        0,
        30000
    );
}

function aaGetAnimationStagger(object) {
    return aaNumber(
        object?.aaAnimationStagger ?? object?.animationStagger,
        AA_ANIMATION_DEFAULTS.stagger,
        0,
        3000
    );
}

const AA_TEXT_ANIMATION_TYPES = new Set([
    'none',
    'typewriter',
    'letter-fade-up',
    'letter-wave',
    'word-reveal',
    'text-glow',
    'shine-text',
]);

function aaNormalizeTextAnimationConfig(value = null) {
    const source = value && typeof value === 'object' ? value : {};
    const type = AA_TEXT_ANIMATION_TYPES.has(source.type) ? source.type : 'none';
    const enabled = source.enabled === true && type !== 'none';

    return {
        enabled,
        type: enabled ? type : 'none',
        delay: aaNumber(source.delay, 0, 0, 5000),
        duration: aaNumber(source.duration, 1200, 200, 8000),
        stagger: aaNumber(source.stagger, 40, 0, 300),
        loop: source.loop === true || type === 'text-glow' || type === 'shine-text',
    };
}

function aaGetTextAnimationConfig(object) {
    return aaNormalizeTextAnimationConfig(object?.aaTextAnimation);
}

function aaGetTextAnimationTarget(active = state.canvas?.getActiveObject?.()) {
    if (!aaIsTextAnimationTarget(active)) return null;

    if (typeof getContextTextTarget === 'function') {
        const textTarget = getContextTextTarget(active);
        if (textTarget) return textTarget;
    }

    return aaGetAnimationTarget(active);
}

function aaSetTextAnimationObjectValues(object, values = {}) {
    if (!object || object === state.cropBox) return false;

    const current = aaGetTextAnimationConfig(object);
    const nextType = AA_TEXT_ANIMATION_TYPES.has(values.type) ? values.type : current.type;
    const next = aaNormalizeTextAnimationConfig({
        ...current,
        enabled: values.enabled !== undefined ? values.enabled : nextType !== 'none',
        type: nextType,
        delay: values.delay ?? current.delay,
        duration: values.duration ?? current.duration,
        stagger: values.stagger ?? current.stagger,
        loop: values.loop ?? current.loop,
    });

    object.set({
        aaTextAnimation: next,
        aaAnimation: 'none',
        aaAnimationName: 'none',
        animation: 'none',
        animationName: 'none',
        animationPreset: 'none',
        customAnimation: 'none',
    });
    object.dirty = true;
    object.setCoords?.();

    return true;
}

function aaSetAnimationObjectValues(object, values = {}) {
    if (!object || object === state.cropBox) return false;

    const currentName = aaGetAnimationName(object);
    const nextName = String(
    values.name !== undefined ? values.name :
    values.animation !== undefined ? values.animation :
    values.aaAnimation !== undefined ? values.aaAnimation :
    currentName || 'none'
);

    const nextDuration = aaNumber(
        values.duration ?? values.aaAnimationDuration ?? aaGetAnimationDuration(object),
        AA_ANIMATION_DEFAULTS.duration,
        50,
        30000
    );

    const nextDelay = aaNumber(
        values.delay ?? values.aaAnimationDelay ?? aaGetAnimationDelay(object),
        AA_ANIMATION_DEFAULTS.delay,
        0,
        30000
    );

    const nextStagger = aaNumber(
        values.stagger ?? values.aaAnimationStagger ?? aaGetAnimationStagger(object),
        AA_ANIMATION_DEFAULTS.stagger,
        0,
        3000
    );

    object.set({
        aaAnimation: nextName,
        aaAnimationName: nextName,
        aaAnimationDuration: nextDuration,
        aaAnimationDelay: nextDelay,
        aaAnimationStagger: nextStagger,
        aaAnimationOnce: values.once ?? object.aaAnimationOnce ?? true,

        // fallback agar renderer lama tetap membaca
        animation: nextName,
        animationName: nextName,
        animationDuration: nextDuration,
        animationDelay: nextDelay,
        animationStagger: nextStagger,
    });

    object.dirty = true;
    object.setCoords?.();

    return true;
}

function aaClearTextAnimationConfig(object) {
    if (!object || !aaIsTextAnimationTarget(object)) return false;

    object.set('aaTextAnimation', aaNormalizeTextAnimationConfig({
        enabled: false,
        type: 'none',
        stagger: aaGetTextAnimationConfig(object).stagger,
    }));

    return true;
}

function aaAnimationInput(name) {
    name = String(name || '').toLowerCase();

    const directSelectorMap = {
        duration: '[data-aa-animation-duration]',
        delay: '[data-aa-animation-delay]',
        stagger: '[data-aa-text-animation-stagger], [data-aa-animation-stagger]'
    };
    const directSelector = directSelectorMap[name] || '';
    const panel = document.getElementById('aaAnimationPanel');
    const panelInput = directSelector && panel ? panel.querySelector(directSelector) : null;

    return (
        panelInput ||
        document.querySelector(directSelector) ||
        document.querySelector(`[data-aa-animation-input="${name}"]`) ||
        document.querySelector(`[name="aaAnimation${name[0].toUpperCase() + name.slice(1)}"]`) ||
        document.querySelector(`#aaAnimation${name[0].toUpperCase() + name.slice(1)}Input`) ||
        document.querySelector(`#aaAnimation${name[0].toUpperCase() + name.slice(1)}`)
    );
}

function aaSetAnimationInputValue(name, value) {
    name = String(name || '').toLowerCase();

    const input = aaAnimationInput(name);
    const safeValue = aaNumber(value, name === 'duration' ? 900 : 0, 0, 30000);

    if (input) {
        input.value = String(safeValue);
    }

    const suffix = name === 'duration' || name === 'delay' || name === 'stagger' ? 'ms' : '';

    const outputSelectors = [
        `[data-aa-animation-${name}-output]`,
        `[data-aa-animation-value="${name}"]`,
        `#aaAnimation${name[0].toUpperCase() + name.slice(1)}Value`
    ];

    outputSelectors.forEach(selector => {
        document.querySelectorAll(selector).forEach(output => {
            output.textContent = `${safeValue}${suffix}`;
        });
    });
}

function aaReadAnimationPanelValues(target = aaGetAnimationTarget()) {
    const textConfig = aaGetTextAnimationConfig(target);

    return {
        name: aaGetAnimationName(target),
        duration: aaNumber(aaAnimationInput('duration')?.value, textConfig.enabled ? textConfig.duration : aaGetAnimationDuration(target), 50, 30000),
        delay: aaNumber(aaAnimationInput('delay')?.value, textConfig.enabled ? textConfig.delay : aaGetAnimationDelay(target), 0, 30000),
        stagger: aaNumber(aaAnimationInput('stagger')?.value, textConfig.enabled ? textConfig.stagger : aaGetAnimationStagger(target), 0, 3000),
    };
}

function updateActiveAnimationTiming(commit = false, sourceInput = null) {
    const active = state.canvas?.getActiveObject?.();
    const target = aaGetAnimationTarget(active);

    if (!target || target === state.cropBox || target.locked === true) return;

    const currentName = aaGetAnimationName(target);

    const delayInput = aaAnimationInput('delay');
    const durationInput = aaAnimationInput('duration');
    const staggerInput = aaAnimationInput('stagger');

    const nextDelay = aaNumber(
        sourceInput?.matches?.('[data-aa-animation-delay]') ? sourceInput.value : delayInput?.value,
        aaGetAnimationDelay(target),
        0,
        30000
    );

    const nextDuration = aaNumber(
        sourceInput?.matches?.('[data-aa-animation-duration]') ? sourceInput.value : durationInput?.value,
        aaGetAnimationDuration(target),
        50,
        30000
    );

    const isTextTarget = aaIsTextAnimationTarget(active);

    const nextStagger = isTextTarget
        ? aaNumber(
            sourceInput?.matches?.('[data-aa-text-animation-stagger], [data-aa-animation-stagger]') ? sourceInput.value : staggerInput?.value,
            aaGetAnimationStagger(target),
            0,
            3000
        )
        : 0;

    aaSetAnimationObjectValues(target, {
        name: currentName,
        duration: nextDuration,
        delay: nextDelay,
        stagger: nextStagger
    });

    aaSetStaggerControlVisible(isTextTarget);

    if (!isTextTarget) {
        aaSetAnimationInputValue('stagger', 0);
    }

    aaSetAnimationInputValue('delay', nextDelay);
    aaSetAnimationInputValue('duration', nextDuration);
    aaSetAnimationInputValue('stagger', nextStagger);

    target.dirty = true;
    target.setCoords?.();

    state.canvas.requestRenderAll?.();

    syncAnimationButtons?.(target);
    syncInspector?.();

    if (commit) {
        storeCurrentPage?.();
        snapshot?.();
        setStatus?.('Timing animasi diperbarui');
    } else {
        setStatus?.('Mengatur timing animasi...');
    }

    if (!commit && currentName && currentName !== 'none' && typeof previewObjectAnimation === 'function') {
        clearTimeout(state.__aaAnimationTimingPreviewTimer);
        state.__aaAnimationTimingPreviewTimer = setTimeout(function () {
            previewObjectAnimation(currentName, active);
        }, 120);
    }
}

function aaIsTextAnimationTarget(object) {
    const target = aaGetAnimationTarget?.(object) || object;

    if (!target || target === state.cropBox) return false;

    if (typeof getContextTextTarget === 'function' && getContextTextTarget(object)) {
        return true;
    }

    return ['i-text', 'textbox', 'text'].includes(String(target.type || ''));
}

function aaSetStaggerControlVisible(isVisible) {
    isVisible = isVisible === true;

    const staggerWrap =
        document.getElementById('aaTextAnimationStaggerControl') ||
        document.querySelector('[data-aa-animation-stagger-wrap]') ||
        document.querySelector('.aa-animation-stagger-wrap') ||
        null;

    const staggerInput =
        document.querySelector('[data-aa-text-animation-stagger]') ||
        document.querySelector('[data-aa-animation-stagger]') ||
        aaAnimationInput?.('stagger') ||
        null;

    if (staggerWrap) {
        staggerWrap.hidden = !isVisible;
        staggerWrap.style.display = isVisible ? '' : 'none';
        staggerWrap.classList.toggle('hidden', !isVisible);
        staggerWrap.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
    }

    if (staggerInput) {
        staggerInput.disabled = !isVisible;

        if (!isVisible) {
            staggerInput.value = '0';
        }
    }

    document.querySelectorAll('[data-aa-text-animation-stagger-output], [data-aa-animation-stagger-output], [data-aa-animation-value="stagger"], #aaAnimationStaggerValue').forEach(output => {
        output.textContent = isVisible ? output.textContent : '0ms';
    });
}

function aaIsTextAnimationToolbarTrigger(trigger = state.animationPopoverTrigger) {
    if (!trigger) return false;

    return Boolean(
        trigger === els.aaTextContextAnimateBtn ||
        trigger.id === 'aaTextContextAnimateBtn' ||
        trigger.closest?.('#aaTextContextToolbar')
    );
}

function aaIsAnimationDisabledForCurrentMode() {
    return state.editMode === 'opening' || state.editMode === 'photobooth';
}

function aaSyncAnimationModeLock(active = state.canvas?.getActiveObject?.()) {
    const disabled = aaIsAnimationDisabledForCurrentMode();

    [els.aaContextAnimateBtn, els.aaTextContextAnimateBtn].forEach(button => {
        if (!button) return;
        button.disabled = disabled;
        button.classList.toggle('is-disabled', disabled);
        button.setAttribute('aria-disabled', disabled ? 'true' : 'false');
        button.title = disabled ? 'Animasi hanya tersedia di mode Halaman.' : '';
    });

    document.querySelectorAll('[data-aa-animation], [data-aa-text-animation], [data-aa-animation-delay], [data-aa-animation-duration], [data-aa-text-animation-stagger], [data-aa-animation-stagger]').forEach(control => {
        control.disabled = disabled;
        control.classList.toggle('is-disabled', disabled);
        control.setAttribute('aria-disabled', disabled ? 'true' : 'false');
    });
}

function aaSetAnimationTimingControlsVisible(isVisible, options = {}) {
    isVisible = isVisible === true;
    const showOnlyStagger = options.showOnlyStagger === true;
    const showDelayDuration = isVisible && !showOnlyStagger;
    const showStagger = showOnlyStagger;
    const showTiming = showDelayDuration || showStagger;

    document.querySelectorAll('[data-aa-animation-timing-title], [data-aa-animation-timing-wrap]').forEach(element => {
        element.hidden = !showTiming;
        element.classList.toggle('hidden', !showTiming);
        element.setAttribute('aria-hidden', showTiming ? 'false' : 'true');
    });

    document.querySelectorAll('[data-aa-animation-timing-field]').forEach(field => {
        const fieldName = String(field.getAttribute('data-aa-animation-timing-field') || '').toLowerCase();
        const shouldShow =
            (fieldName === 'stagger' && showStagger) ||
            ((fieldName === 'delay' || fieldName === 'duration') && showDelayDuration) ||
            (!['delay', 'duration', 'stagger'].includes(fieldName) && isVisible);

        field.hidden = !shouldShow;
        field.classList.toggle('hidden', !shouldShow);
        field.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
    });

    document.querySelectorAll('[data-aa-animation-delay], [data-aa-animation-duration], [data-aa-text-animation-stagger], [data-aa-animation-stagger]').forEach(input => {
        const isStaggerInput = input.matches('[data-aa-text-animation-stagger], [data-aa-animation-stagger]');
        input.disabled = isStaggerInput ? !showStagger : !showDelayDuration;
    });
}

function aaSyncAnimationTimingControlsForTrigger(trigger = state.animationPopoverTrigger) {
    const active = state.canvas?.getActiveObject?.();
    const target = aaGetAnimationTarget(active);
    const hasTextAnimation = Boolean(aaIsTextAnimationTarget(active) && aaGetTextAnimationConfig(target).enabled);

    aaSetAnimationTimingControlsVisible(true, {
        showOnlyStagger: hasTextAnimation
    });

    aaSetStaggerControlVisible(hasTextAnimation);
}

function syncAnimationButtons(active = state.canvas?.getActiveObject?.()) {
    const target = aaGetAnimationTarget(active);
    const name = aaGetAnimationName(target);

    const isTextTarget = aaIsTextAnimationTarget(active);
    const textConfig = aaGetTextAnimationConfig(target);

    aaSetStaggerControlVisible(isTextTarget && textConfig.enabled);

    if (!isTextTarget && target) {
        target.animationStagger = 0;
        target.aaAnimationStagger = 0;
        target.textAnimationStagger = 0;
        target.aaTextAnimationStagger = 0;
        target.stagger = 0;
    }

    document.querySelectorAll('[data-aa-animation]').forEach(button => {
        button.classList.toggle('is-active', !textConfig.enabled && button.dataset.aaAnimation === name);
    });

    aaSetAnimationInputValue('duration', textConfig.enabled ? textConfig.duration : aaGetAnimationDuration(target));
    aaSetAnimationInputValue('delay', textConfig.enabled ? textConfig.delay : aaGetAnimationDelay(target));
    aaSetAnimationInputValue('stagger', textConfig.enabled ? textConfig.stagger : aaGetAnimationStagger(target));
    
    if (isTextTarget && textConfig.enabled) {
        aaSetAnimationInputValue('stagger', textConfig.stagger);
    } else {
        aaSetAnimationInputValue('stagger', 0);
    }

    if (els.aaContextAnimateBtn) {
        els.aaContextAnimateBtn.classList.toggle('is-active', Boolean(name && name !== 'none'));
    }

    if (els.aaTextContextAnimateBtn) {
        els.aaTextContextAnimateBtn.classList.toggle('is-active', Boolean(name && name !== 'none'));
    }
    if (typeof aaSetStaggerControlVisible === 'function') {
        aaSetStaggerControlVisible(aaIsTextAnimationTarget?.(active) === true && textConfig.enabled);
    }
    aaSyncAnimationModeLock(active);
}

function syncTextAnimationToolbar(active = state.canvas?.getActiveObject?.()) {
    const target = aaGetTextAnimationTarget(active);
    const config = aaGetTextAnimationConfig(target);
    const textSection = document.getElementById('aaTextAnimationOptionsSection');

    if (textSection) {
        textSection.hidden = !target;
    }

    document.querySelectorAll('[data-aa-text-animation]').forEach(button => {
        button.classList.toggle('is-active', button.dataset.aaTextAnimation === config.type);
    });

    if (target && config.enabled) {
        aaSetAnimationInputValue('delay', config.delay);
        aaSetAnimationInputValue('duration', config.duration);
        aaSetAnimationInputValue('stagger', config.stagger);
    }

    if (els.aaTextContextAnimateBtn) {
        els.aaTextContextAnimateBtn.classList.toggle('is-active', Boolean(config.enabled));
    }

    if (els.aaContextAnimateBtn) {
        els.aaContextAnimateBtn.classList.toggle('is-active', Boolean(config.enabled || (aaGetAnimationName(aaGetAnimationTarget(active)) !== 'none')));
    }
    aaSyncAnimationModeLock(active);
}

function applyAnimationToActiveObject(animationName = null) {
    const active = state.canvas?.getActiveObject?.();

    if (aaIsAnimationDisabledForCurrentMode()) {
        setStatus?.('Animasi object hanya tersedia di mode Halaman.');
        closeAnimationPopover?.();
        return false;
    }

    if (active?.type === 'activeSelection') {
        setStatus?.('Jadikan grup dulu agar animasi berjalan sebagai satu object.');
        if (typeof showEditorToast === 'function') {
            showEditorToast('Multi selection belum bisa dianimasikan langsung. Jadikan grup dulu agar animasi berjalan sebagai satu object.', 'error');
        }
        return false;
    }

    const target = aaGetAnimationTarget(active);

    if (!target) {
        setStatus?.('Pilih object dulu.');
        return false;
    }

    if (target.locked === true) {
        setStatus?.('Object terkunci. Unlock dulu untuk mengatur animasi.');
        return false;
    }

    const panelValues = aaReadAnimationPanelValues(target);

    aaSetAnimationObjectValues(target, {
        ...panelValues,
        name: animationName ?? panelValues.name,
    });

    if (animationName !== null) {
        aaClearTextAnimationConfig(target);
    }

    state.canvas.requestRenderAll?.();
    syncAnimationButtons(target);
    syncTextAnimationToolbar(target);
    aaSyncAnimationTimingControlsForTrigger(state.animationPopoverTrigger);
    syncInspector?.();
    storeCurrentPage?.();
    snapshot?.();

    setStatus?.(
        aaGetAnimationName(target) === 'none'
            ? 'Animasi dihapus.'
            : `Animasi ${aaGetAnimationName(target)} disimpan.`
    );

    return true;
}

function applyTextAnimationToActiveObject(type = null) {
    const active = state.canvas?.getActiveObject?.();
    const target = aaGetTextAnimationTarget(active);

    if (aaIsAnimationDisabledForCurrentMode()) {
        setStatus?.('Text Animate hanya tersedia di mode Halaman.');
        closeAnimationPopover?.();
        return false;
    }

    if (!target) {
        setStatus?.('Pilih object teks dulu.');
        return false;
    }

    if (target.locked === true) {
        setStatus?.('Object teks terkunci. Unlock dulu untuk mengatur animasi.');
        return false;
    }

    const panelValues = aaReadAnimationPanelValues(target);
    const current = aaGetTextAnimationConfig(target);
    const nextType = type ?? current.type;

    aaSetTextAnimationObjectValues(target, {
        ...panelValues,
        type: nextType,
        enabled: nextType !== 'none',
        loop: nextType === 'text-glow' || nextType === 'shine-text',
    });

    state.canvas.requestRenderAll?.();
    syncAnimationButtons(target);
    syncTextAnimationToolbar(target);
    aaSyncAnimationTimingControlsForTrigger(state.animationPopoverTrigger);
    syncInspector?.();
    storeCurrentPage?.();
    snapshot?.();

    setStatus?.(
        aaGetTextAnimationConfig(target).enabled
            ? `Text Animate ${aaGetTextAnimationConfig(target).type} disimpan.`
            : 'Text Animate dihapus.'
    );

    return true;
}

function updateActiveTextAnimationTimingFromPanel(commit = false, sourceInput = null) {
    if (aaIsAnimationDisabledForCurrentMode()) return false;

    const active = state.canvas?.getActiveObject?.();
    const target = aaGetTextAnimationTarget(active);
    if (!target || target.locked === true) return false;

    const current = aaGetTextAnimationConfig(target);
    if (!current.enabled) return false;

    const delayInput = aaAnimationInput('delay');
    const durationInput = aaAnimationInput('duration');
    const staggerInput = aaAnimationInput('stagger');

    const nextDelay = aaNumber(
        sourceInput?.matches?.('[data-aa-animation-delay]') ? sourceInput.value : delayInput?.value,
        current.delay,
        0,
        5000
    );
    const nextDuration = aaNumber(
        sourceInput?.matches?.('[data-aa-animation-duration]') ? sourceInput.value : durationInput?.value,
        current.duration,
        200,
        8000
    );
    const nextStagger = aaNumber(
        sourceInput?.matches?.('[data-aa-text-animation-stagger], [data-aa-animation-stagger]') ? sourceInput.value : staggerInput?.value,
        current.stagger,
        0,
        300
    );

    aaSetTextAnimationObjectValues(target, {
        ...current,
        delay: nextDelay,
        duration: nextDuration,
        stagger: nextStagger,
    });

    aaSetAnimationInputValue('delay', nextDelay);
    aaSetAnimationInputValue('duration', nextDuration);
    aaSetAnimationInputValue('stagger', nextStagger);
    syncTextAnimationToolbar(target);
    syncInspector?.();
    storeCurrentPage?.();
    state.canvas.requestRenderAll?.();

    if (commit) {
        snapshot?.();
        setStatus?.('Timing Text Animate diperbarui.');
    } else {
        setStatus?.('Mengatur timing Text Animate...');
    }

    return true;
}

function bindAnimationControls() {
    if (state.__aaAnimationControlsBound) return;
    state.__aaAnimationControlsBound = true;

    document.addEventListener('click', function (event) {
        const textAnimationBtn = event.target?.closest?.('[data-aa-text-animation]');
        if (textAnimationBtn) {
            event.preventDefault();
            applyTextAnimationToActiveObject(textAnimationBtn.dataset.aaTextAnimation || 'none');
            return;
        }

        const animationBtn = event.target?.closest?.('[data-aa-animation]');
        if (!animationBtn) return;

        event.preventDefault();

        const animationName = animationBtn.dataset.aaAnimation || 'none';

        applyAnimationToActiveObject(animationName);
    });

    ['duration', 'delay', 'stagger'].forEach(name => {
        const input = aaAnimationInput(name);
        if (!input || input.__aaAnimationInputBound) return;

        input.__aaAnimationInputBound = true;

        input.addEventListener('input', function () {
            const valueEl =
                document.querySelector(`[data-aa-animation-value="${name}"]`) ||
                document.querySelector(`#aaAnimation${name[0].toUpperCase() + name.slice(1)}Value`);

            if (valueEl) {
                valueEl.textContent = String(this.value || 0);
            }

            if (!updateActiveTextAnimationTimingFromPanel(false, this)) {
                applyAnimationToActiveObject();
            }
        });

        input.addEventListener('change', function () {
            if (!updateActiveTextAnimationTimingFromPanel(true, this)) {
                applyAnimationToActiveObject();
            }
        });
    });
}
        
	        function openAnimationPopover(trigger = null) {
	            const active = state.canvas.getActiveObject();
	            if (!active || active === state.cropBox || state.isCropping) return;
	            if (typeof aaIsPhotoboothPhotoSlot === 'function' && aaIsPhotoboothPhotoSlot(active)) {
	                closeAnimationPopover();
	                setStatus?.('Animasi tidak tersedia untuk slot foto Photobooth.', 'error');
	                return;
	            }
	            if (aaIsAnimationDisabledForCurrentMode()) {
	                aaSyncAnimationModeLock(active);
	                closeAnimationPopover();
	                setStatus?.('Animasi hanya tersedia di mode Halaman.');
	                return;
	            }
	            state.animationPopoverTrigger = trigger || (isContextTextObject(active) ? els.aaTextContextAnimateBtn :
	                els.aaContextAnimateBtn);
	            closeToolbarPopovers('animation');
	            syncAnimationButtons(active);
	            syncTextAnimationToolbar(active);
	            aaSyncAnimationTimingControlsForTrigger(state.animationPopoverTrigger);
	            els.aaAnimationPopover?.classList.remove('is-open');
	            openLeftDrawerPanel('animation');
	            setStatus?.('Pilih animasi di panel kiri.');
	        }

	        function closeAnimationPopover() {
	            els.aaAnimationPopover?.classList.remove('is-open');
	            state.animationPopoverTrigger = null;
	            aaSyncAnimationTimingControlsForTrigger(null);
	        }

        function toggleAnimationPopover(trigger = null) {
            if (els.aaAnimationPopover?.classList.contains('is-open')) {
                closeAnimationPopover();
                return;
            }
            openAnimationPopover(trigger);
        }

        function applyTextEffectSettings(values = {}, options = {}) {
            const target = getTextEffectTarget();
            const active = state.canvas.getActiveObject();
            if (!target || !active) return;

            if (Object.prototype.hasOwnProperty.call(values, 'strokeColor') || Object.prototype.hasOwnProperty.call(
                    values, 'strokeWidth')) {
                const width = Math.max(0, Math.round(Number(values.strokeWidth ?? target.strokeWidth) || 0));
                const strokeColor = normalizeColor(values.strokeColor || target.aaTextEffectOutlineColor || target
                    .stroke || '#111827');
                target.aaTextEffectOutlineColor = strokeColor;
                target.set({
                    stroke: width ? strokeColor : null,
                    strokeWidth: width,
                    paintFirst: 'stroke',
                });
            }

            if (Object.prototype.hasOwnProperty.call(values, 'shadowColor') || Object.prototype.hasOwnProperty.call(
                    values, 'shadowBlur') || Object.prototype.hasOwnProperty.call(values, 'shadowOffsetX') ||
                Object.prototype.hasOwnProperty.call(values, 'shadowOffsetY')) {
                const current = getTextShadowValues(target);
                const blur = Math.max(0, Math.round(Number(values.shadowBlur ?? current.blur) || 0));
                const offsetX = Math.round(Number(values.shadowOffsetX ?? current.offsetX) || 0);
                const offsetY = Math.round(Number(values.shadowOffsetY ?? current.offsetY) || 0);
                const color = normalizeColor(values.shadowColor || target.aaTextEffectShadowColor || current
                    .color ||
                    target.fill || '#000000');
                target.aaTextEffectShadowColor = color;
                target.set('shadow', (blur || offsetX || offsetY) ? new fabric.Shadow({
                    color,
                    blur,
                    offsetX,
                    offsetY,
                }) : null);
            }

            if (Object.prototype.hasOwnProperty.call(values, 'charSpacing')) {
                target.set('charSpacing', Math.max(-100, Math.min(800, Math.round(Number(values.charSpacing) ||
                    0))));
            }
            if (Object.prototype.hasOwnProperty.call(values, 'lineHeight')) {
                target.set('lineHeight', Math.max(.8, Math.min(2.4, Number(values.lineHeight) || 1.14)));
            }

            target.dirty = true;
            if (typeof target.initDimensions === 'function') {
                target.initDimensions();
            }
            if (active?.customType === 'social-link' && typeof aaLayoutSocialLinkGroup === 'function') {
                aaLayoutSocialLinkGroup(active);
            }
            active.dirty = true;
            active.setCoords();
            state.canvas.requestRenderAll();
            syncTextEffectsControl(target);
            syncInspector();
            if (options.snapshot !== false) {
                snapshot();
            }
        }

        function applyTextEffectPreset(presetName) {
            const target = getTextEffectTarget();
            if (!target) return;
            const preset = textEffectPresets[presetName] || textEffectPresets.none;
            const outlineColor = els.aaTextEffectStrokeColor?.value || getTextEffectOutlineColor(target);
            const shadowColor = els.aaTextEffectShadowColor?.value || getTextEffectShadowColor(target);
            target.aaTextEffectPreset = textEffectPresets[presetName] ? presetName : 'none';
            applyTextEffectSettings({
                strokeColor: outlineColor,
                shadowColor,
                ...preset,
            }, {
                snapshot: false,
            });
            snapshot();
        }

        function applyTextGlowPreset() {
            const target = getTextEffectTarget();
            if (!target) return;
            const color = normalizeColor(target.fill || '#ffffff');
            target.aaTextEffectPreset = 'glow';
            applyTextEffectSettings({
                shadowColor: color,
                shadowBlur: 24,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
            });
        }

        function resetTextEffects() {
            const target = getTextEffectTarget();
            if (target) target.aaTextEffectPreset = 'none';
            applyTextEffectSettings({
                strokeWidth: 0,
                shadowBlur: 0,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
                charSpacing: 0,
                lineHeight: 1.14,
            });
        }

        function positionContextFlipPopover() {
            if (!els.aaContextFlipPopover || !els.aaContextFlipBtn) return;
            const rect = els.aaContextFlipBtn.getBoundingClientRect();
            const popoverWidth = els.aaContextFlipPopover.offsetWidth || 160;
            const left = Math.min(window.innerWidth - popoverWidth / 2 - 12, Math.max(popoverWidth / 2 + 12,
                rect.left + rect.width / 2));
            els.aaContextFlipPopover.style.left = `${left}px`;
            els.aaContextFlipPopover.style.top = `${rect.bottom + 8}px`;
        }

        function openContextFlipPopover() {
            const active = state.canvas.getActiveObject();
            if (!active || active === state.cropBox || active.locked === true) return;
            closeToolbarPopovers('flip');
            els.aaContextFlipPopover?.classList.add('is-open');
            requestAnimationFrame(positionContextFlipPopover);
        }

        function closeContextFlipPopover() {
            els.aaContextFlipPopover?.classList.remove('is-open');
        }

        function toggleContextFlipPopover() {
            if (els.aaContextFlipPopover?.classList.contains('is-open')) {
                closeContextFlipPopover();
                return;
            }
            openContextFlipPopover();
        }

        function flipActiveObject(axis = 'x') {
            const active = state.canvas.getActiveObject();
            if (!active || active === state.cropBox) return;
            if (guardLockedImageAction('flip gambar', active)) return;
            if (active.locked === true) return;
            const key = axis === 'y' ? 'flipY' : 'flipX';
            active.set(key, !active[key]);
            active.setCoords();
            state.canvas.requestRenderAll();
            snapshot();
            syncContextToolbar(active);
            closeContextFlipPopover();
        }

        function focusAnimationControls() {
            openLeftDrawerPanel('animation');
            const firstAnimation = document.querySelector('#aaAnimationPanel [data-aa-animation]') ||
                document.querySelector('[data-aa-animation]');
            if (!firstAnimation) return;
            firstAnimation.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });
            setStatus('Pilih animasi di panel kiri.');
        }

        function aaTextSelectionVisualPadding(object) {
            if (!isFabricTextObject(object)) return 0;

            const family = String(object.fontFamily || '').toLowerCase();
            const scriptFont = [
                'alex brush', 'allura', 'arizonia', 'bonheur royale', 'dancing script', 'ephesis',
                'fleur de leah', 'great vibes', 'imperial script', 'italianno', 'lavishly yours',
                'mea culpa', 'monsieur la doulaise', 'parisienne', 'petit formal script',
                'tangerine', 'the nautigal', 'windsong'
            ].some(name => family.includes(name));
            const italicLike = String(object.fontStyle || '').toLowerCase().includes('italic');
            const fontSize = Math.max(10, Number(object.fontSize) || 32);
            const lineHeight = Math.max(0.65, Math.min(2.4, Number(object.lineHeight) || 1.16));
            const textLength = String(object.text || '').trim().length;
            const isCompactText = textLength > 0 && textLength <= 8;

            if (scriptFont || italicLike) {
                return Math.max(18, Math.min(58, Math.round(fontSize * (lineHeight > 1.25 ? 0.28 : 0.34))));
            }

            if (lineHeight > 1.35) {
                return Math.max(8, Math.min(24, Math.round(fontSize * 0.12)));
            }

            return Math.max(6, Math.min(isCompactText ? 12 : 18, Math.round(fontSize * 0.075)));
        }

        function aaApplyTextSelectionVisualPadding(target = state.canvas?.getActiveObject?.()) {
            if (!target || target === state.cropBox || state.isCropping) return;

            const objects = target.type === 'activeSelection' && typeof target.getObjects === 'function' ?
                target.getObjects() : [target];
            let changed = false;

            objects.forEach(object => {
                if (!isFabricTextObject(object)) return;
                const padding = aaTextSelectionVisualPadding(object);
                if (Number(object.padding) === padding) return;
                object.set('padding', padding);
                object.setCoords?.();
                changed = true;
            });

            if (changed) {
                target.setCoords?.();
                state.canvas?.requestRenderAll?.();
            }
        }

        function syncInspector() {
            const active = state.canvas.getActiveObject();
            aaApplyTextSelectionVisualPadding(active);
            syncContextToolbar(active);
            syncTextContextToolbar(active);
            syncCountdownContextToolbar(active);
            requestAnimationFrame(syncObjectFloatingToolbar);
            els.aaObjectControls.style.opacity = active ? '1' : '.45';
            const isPhotoboothSlot = typeof aaIsPhotoboothPhotoSlot === 'function' && aaIsPhotoboothPhotoSlot(active);
            document.body.classList.toggle('aa-photobooth-slot-selection-active', Boolean(isPhotoboothSlot));
            els.aaSelectionHint.textContent = isPhotoboothSlot
                ? 'Terpilih: Slot foto Photobooth. Geser atau resize, slot tidak bisa dihapus.'
                : (active ? `Terpilih: ${active.customType || active.type}` : 'Pilih elemen di canvas untuk mengedit.');
            const duplicateButton = document.getElementById('aaDuplicateBtn');
            const deleteButton = document.getElementById('aaDeleteBtn');
            if (duplicateButton) {
                duplicateButton.disabled = !active || isPhotoboothSlot;
                duplicateButton.title = isPhotoboothSlot ? 'Slot foto Photobooth tidak bisa diduplikasi.' : '';
            }
            if (deleteButton) {
                deleteButton.disabled = !active || isPhotoboothSlot;
                deleteButton.title = isPhotoboothSlot ? 'Slot foto Photobooth wajib ada dan tidak bisa dihapus.' : '';
            }

            const isText = active && (active.type === 'i-text' || active.type === 'textbox' || active.type ===
                'text');
            const isGuestNameText = isGuestNameObject(active);
            const guestNameText = isGuestNameText ? getGuestNameTextObject(active) : null;
            const isGuestField = isGuestbookObject(active);
            const isInteractive = isInteractiveObject(active);
            const isMusicPlayer = active && active.customType === 'music-player';
            const isYoutubeVideo = active && active.customType === 'youtube-video';
		            const isSocialMedia = active && active.customType === 'social-media';
		            const socialText = active && (active.customType === 'social-link' || active.customType ===
		                'social-media') ? getContextTextTarget(active) : null;
            document.body.classList.toggle('aa-mobile-music-selection-active', Boolean(isMusicPlayer));
            const interactiveText = isInteractive ? getNamedGroupText(active) : null;
            const editableTextLike = isText || isGuestField || Boolean(interactiveText) || Boolean(guestNameText) ||
                Boolean(socialText);
            els.aaTextInput.disabled = !editableTextLike;
            els.aaFontInput.disabled = !editableTextLike;
            els.aaFontSizeInput.disabled = !editableTextLike;
	            els.aaBoldBtn.disabled = !(isText || Boolean(guestNameText) || Boolean(socialText));
	            els.aaItalicBtn.disabled = !(isText || Boolean(guestNameText) || Boolean(socialText));
	            els.aaUnderlineBtn.disabled = !(isText || Boolean(guestNameText) || Boolean(socialText));
            els.aaImageRadiusInput.disabled = !(active && active.type === 'image');
            els.aaGuestNameFormatPanel?.classList.toggle('hidden', !isGuestNameText);
            els.aaGuestFieldBgPanel?.classList.toggle('hidden', !isGuestField);
            els.aaInteractivePanel?.classList.toggle('hidden', !isInteractive || isMusicPlayer || isYoutubeVideo ||
                active?.customType === 'opening-button');
            els.aaMusicSettings?.classList.toggle('hidden', true);
            els.aaCountdownSettings?.classList.toggle('hidden', !(active && active.customType ===
                'countdown-timer'));
            els.aaGallerySettings?.classList.toggle('hidden', !(active && active.customType === 'photo-gallery'));
            els.aaSocialSettings?.classList.toggle('hidden', !isSocialMedia);
            els.aaStorySettings?.classList.add('hidden');
            els.aaInteractiveRadiusWrap?.classList.toggle('hidden', active?.customType === 'photo-gallery');
            els.aaScrollLockWrap?.classList.toggle('hidden', !(active && active.customType ===
                'scroll-next-button'));
            if (els.aaGuestFieldBgInput) {
                els.aaGuestFieldBgInput.disabled = !isGuestField;
            }

            if (isGuestNameText && guestNameText) {
                els.aaTextInput.value = active.templateText || 'Kepada Yth.\n{{guest_name}}';
                els.aaFontInput.value = guestNameText.fontFamily || 'Cormorant Garamond';
                els.aaFontSizeInput.value = Math.round(guestNameText.fontSize || 54);
                els.aaColorInput.value = normalizeColor(guestNameText.fill || '#ffffff');
                els.aaBoldBtn.classList.toggle('is-active', guestNameText.fontWeight === 'bold' || Number(
                    guestNameText.fontWeight) >= 700);
                els.aaItalicBtn.classList.toggle('is-active', guestNameText.fontStyle === 'italic');
                els.aaUnderlineBtn.classList.toggle('is-active', Boolean(guestNameText.underline));
            } else if (isGuestField) {
                const parts = getGuestbookObjectParts(active);
                els.aaTextInput.value = active.buttonText || active.placeholder || parts.text?.text || '';
                els.aaFontInput.value = parts.text?.fontFamily || 'Inter';
                els.aaFontSizeInput.value = Math.round(parts.text?.fontSize || 36);
                els.aaColorInput.value = normalizeColor(parts.text?.fill || '#334155');
                if (els.aaGuestFieldBgInput) {
                    els.aaGuestFieldBgInput.value = normalizeColor(parts.box?.fill || '#ffffff');
                }
                els.aaBoldBtn.classList.remove('is-active');
                els.aaItalicBtn.classList.remove('is-active');
                els.aaUnderlineBtn.classList.remove('is-active');
            } else if (active && active.customType === 'countdown-timer') {
                els.aaTextInput.value = '';
                els.aaFontInput.value = active.countdownFontFamily || interactiveText?.fontFamily || 'Inter';
                els.aaFontSizeInput.value = Math.round(active.countdownFontSize || interactiveText?.fontSize || 36);
                els.aaColorInput.value = normalizeColor(active.countdownTextColor || interactiveText?.fill ||
                    '#0f172a');
                els.aaBoldBtn.classList.remove('is-active');
                els.aaItalicBtn.classList.remove('is-active');
                els.aaUnderlineBtn.classList.remove('is-active');
            } else if (isInteractive && interactiveText) {
                els.aaTextInput.value = active.buttonText || active.label || interactiveText.text || '';
                els.aaFontInput.value = interactiveText.fontFamily || 'Inter';
                els.aaFontSizeInput.value = Math.round(interactiveText.fontSize || 34);
                els.aaColorInput.value = normalizeColor(interactiveText.fill || '#334155');
                els.aaBoldBtn.classList.remove('is-active');
                els.aaItalicBtn.classList.remove('is-active');
                els.aaUnderlineBtn.classList.remove('is-active');
            } else if (socialText) {
                els.aaTextInput.value = socialText.text || '';
                els.aaFontInput.value = socialText.fontFamily || 'Inter';
                els.aaFontSizeInput.value = Math.round(socialText.fontSize || 30);
                els.aaColorInput.value = normalizeColor(socialText.fill || '#0f172a');
                els.aaBoldBtn.classList.toggle('is-active', socialText.fontWeight === 'bold' || Number(socialText
                    .fontWeight) >= 700);
                els.aaItalicBtn.classList.toggle('is-active', socialText.fontStyle === 'italic');
                els.aaUnderlineBtn.classList.toggle('is-active', Boolean(socialText.underline));
            } else if (isText) {
                els.aaTextInput.value = active.text || '';
                els.aaFontInput.value = active.fontFamily || 'Inter';
                els.aaFontSizeInput.value = Math.round(active.fontSize || 42);
                els.aaColorInput.value = normalizeColor(active.fill || '#111827');
                els.aaBoldBtn.classList.toggle('is-active', active.fontWeight === 'bold' || Number(active
                    .fontWeight) >= 700);
                els.aaItalicBtn.classList.toggle('is-active', active.fontStyle === 'italic');
                els.aaUnderlineBtn.classList.toggle('is-active', Boolean(active.underline));
            } else if (active && active.fill && typeof active.fill === 'string' && active.fill[0] === '#') {
                els.aaColorInput.value = active.fill;
                els.aaTextInput.value = '';
                els.aaBoldBtn.classList.remove('is-active');
                els.aaItalicBtn.classList.remove('is-active');
                els.aaUnderlineBtn.classList.remove('is-active');
            } else {
                els.aaBoldBtn.classList.remove('is-active');
                els.aaItalicBtn.classList.remove('is-active');
                els.aaUnderlineBtn.classList.remove('is-active');
                els.aaTextInput.value = '';
            }

            syncInteractionUi(active);
            if (isMusicPlayer && typeof openMusicDrawer === 'function') {
                const leftbarOpen = document.querySelector('.aa-leftbar')?.classList.contains('is-drawer-open');
                const activeLeftPanel = typeof getActiveLeftDrawerPanelKey === 'function' ? getActiveLeftDrawerPanelKey() : '';
                if (!leftbarOpen || activeLeftPanel !== 'music') {
                    openMusicDrawer();
                } else if (typeof syncMusicDrawerForSelection === 'function') {
                    syncMusicDrawerForSelection(active);
                }
            }
            if (els.aaGuestNameFormatInput) {
                els.aaGuestNameFormatInput.value = isGuestNameText ? (active.templateText ||
                    'Kepada Yth.\n{{guest_name}}') : '';
            }
            if (els.aaGuestNameBgInput) {
                els.aaGuestNameBgInput.disabled = !isGuestNameText;
                els.aaGuestNameBgInput.value = normalizeColor(active?.glassBackgroundColor || '#ffffff');
            }
            if (els.aaGuestNameCloseInput) {
                els.aaGuestNameCloseInput.disabled = !isGuestNameText;
                els.aaGuestNameCloseInput.value = normalizeColor(active?.closeButtonColor || '#ffffff');
            }
            if (els.aaAudioUrlInput) els.aaAudioUrlInput.value = active?.audioUrl || '';
            if (els.aaAudioAutoplayInput) els.aaAudioAutoplayInput.checked = active?.autoplayAfterInteraction !==
                false;
            if (els.aaAudioLoopInput) els.aaAudioLoopInput.checked = active?.loopAudio !== false;
            if (els.aaAudioShowButtonInput) els.aaAudioShowButtonInput.checked = active?.showPlayerButton !== false;
            if (els.aaCountdownDateInput) els.aaCountdownDateInput.value = active?.countdownDate || '';
            if (els.aaCountdownTimeInput) els.aaCountdownTimeInput.value = active?.countdownTime || '';
            if (els.aaCountdownGapInput) els.aaCountdownGapInput.value = active?.countdownGap || 10;
            if (els.aaScrollLockInput) els.aaScrollLockInput.checked = active?.lockPageScroll !== false;
            if (els.aaInteractiveBgInput) {
                const interactiveBox = getInteractiveBox(active);
                els.aaInteractiveBgInput.value = normalizeColor(active?.controlBackground || interactiveBox?.fill ||
                    '#ffffff');
            }
            if (els.aaInteractiveRadiusInput) {
                const interactiveBox = getInteractiveBox(active);
                els.aaInteractiveRadiusInput.value = Math.max(0, Math.round(active?.controlRadius ?? interactiveBox
                    ?.rx ?? 22));
            }
            if (els.aaGalleryImagesInput) els.aaGalleryImagesInput.value = Array.isArray(active?.galleryImages) ?
                active.galleryImages.join('\n') : '';
            if (els.aaGalleryColumnsInput) els.aaGalleryColumnsInput.value = active?.galleryColumns || 2;
            if (els.aaGalleryGapInput) els.aaGalleryGapInput.value = active?.galleryGap || 14;
            if (els.aaGalleryRadiusInput) els.aaGalleryRadiusInput.value = active?.galleryRadius || 18;
            if (els.aaSocialTitleInput) els.aaSocialTitleInput.value = active?.socialTitle || 'Ikuti Kami';
            const socialLinks = active?.socialLinks || {};
            if (els.aaSocialInstagramInput) els.aaSocialInstagramInput.value = socialLinks.instagram || '';
            if (els.aaSocialTiktokInput) els.aaSocialTiktokInput.value = socialLinks.tiktok || '';
            if (els.aaSocialThreadsInput) els.aaSocialThreadsInput.value = socialLinks.threads || '';
            if (els.aaSocialXInput) els.aaSocialXInput.value = socialLinks.x || '';
            if (els.aaSocialFacebookInput) els.aaSocialFacebookInput.value = socialLinks.facebook || '';
            if (els.aaSocialYoutubeInput) els.aaSocialYoutubeInput.value = socialLinks.youtube || '';
            renderGalleryItemList(active);

            syncCropPanel(active);
            syncAnimationButtons(active);
        }

const AA_RECENT_COLOR_KEY = 'aa_editor_recent_colors_v1';
const AA_RECENT_FONT_KEY = 'aa_editor_recent_fonts_v1';

function aaReadRecentList(key) {
    try {
        const raw = localStorage.getItem(key);
        const list = JSON.parse(raw || '[]');
        return Array.isArray(list) ? list.filter(Boolean) : [];
    } catch (error) {
        return [];
    }
}

function aaWriteRecentList(key, list) {
    try {
        localStorage.setItem(key, JSON.stringify(list.slice(0, 12)));
    } catch (error) {
        console.warn('[AA RECENT] Gagal menyimpan recent:', error);
    }
}

function aaPushRecentValue(key, value, limit = 10) {
    value = String(value || '').trim();

    if (!value) return;

    const list = aaReadRecentList(key);
    const normalized = value.toLowerCase();

    const next = [
        value,
        ...list.filter(item => String(item || '').trim().toLowerCase() !== normalized)
    ].slice(0, limit);

    aaWriteRecentList(key, next);
}
function aaNormalizeRecentColor(value) {
    value = String(value || '').trim();

    if (/^#[0-9a-f]{6}$/i.test(value)) {
        return value.toUpperCase();
    }

    if (/^#[0-9a-f]{3}$/i.test(value)) {
        return ('#' + value.slice(1).split('').map(ch => ch + ch).join('')).toUpperCase();
    }

    return '';
}
function aaRenderRecentColors() {
    const wrap = document.getElementById('aaRecentColorWrap');
    const listEl = document.getElementById('aaRecentColorList');
    const outlineWrap = document.getElementById('aaOutlineRecentColorWrap');
    const outlineListEl = document.getElementById('aaOutlineRecentColorList');

    if ((!wrap || !listEl) && (!outlineWrap || !outlineListEl)) return;

    const colors = aaReadRecentList(AA_RECENT_COLOR_KEY)
        .map(aaNormalizeRecentColor)
        .filter(Boolean);

    if (wrap && listEl) {
        wrap.classList.toggle('hidden', colors.length === 0);
        listEl.innerHTML = '';
    }
    if (outlineWrap && outlineListEl) {
        outlineWrap.classList.toggle('hidden', colors.length === 0);
        outlineListEl.innerHTML = '';
    }

    colors.forEach(color => {
        if (listEl) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'aa-recent-color-btn';
            button.style.background = color;
            button.title = color;
            button.dataset.aaRecentColor = color;

            button.addEventListener('click', function () {
                aaApplyRecentColor(color);
            });

            listEl.appendChild(button);
        }
        if (outlineListEl) {
            const outlineButton = document.createElement('button');
            outlineButton.type = 'button';
            outlineButton.className = 'aa-recent-color-btn';
            outlineButton.style.background = color;
            outlineButton.title = color;
            outlineButton.dataset.aaRecentColor = color;

            outlineButton.addEventListener('click', function () {
                if (typeof window.aaSetOutlineDraftColor === 'function') {
                    window.aaSetOutlineDraftColor(color);
                }
            });

            outlineListEl.appendChild(outlineButton);
        }
    });
}

function aaRememberRecentColor(value) {
    const color = aaNormalizeRecentColor(value);

    if (!color) return;

    aaPushRecentValue(AA_RECENT_COLOR_KEY, color, 12);
    aaRenderRecentColors();
}
function aaRenderRecentFonts() {
    const wrap = document.getElementById('aaRecentFontWrap');
    const listEl = document.getElementById('aaRecentFontList');

    if (!wrap || !listEl) return;

    const fonts = aaReadRecentList(AA_RECENT_FONT_KEY)
        .map(font => String(font || '').replace(/^["']|["']$/g, '').trim())
        .filter(Boolean);

    listEl.innerHTML = '';

    if (!fonts.length) {
        wrap.classList.add('hidden');
        wrap.hidden = true;
        return;
    }

    wrap.classList.remove('hidden');
    wrap.hidden = false;

    fonts.forEach(font => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'aa-recent-font-btn';
        button.textContent = font;
        button.title = font;
        button.style.fontFamily = `"${font}", Inter, Arial, sans-serif`;
        button.dataset.aaRecentFont = font;

        button.addEventListener('click', function () {
            aaApplyRecentFont(font, {
                remember: false
            });
        });
        button.addEventListener('mouseover', function () {
            if (typeof previewFontDrawerFamily === 'function') {
                previewFontDrawerFamily(font);
            }
        });
        button.addEventListener('focusin', function () {
            if (typeof previewFontDrawerFamily === 'function') {
                previewFontDrawerFamily(font);
            }
        });
        button.addEventListener('mouseleave', function () {
            if (typeof clearFontDrawerPreview === 'function') {
                clearFontDrawerPreview();
            }
        });
        button.addEventListener('blur', function () {
            if (typeof clearFontDrawerPreview === 'function') {
                clearFontDrawerPreview();
            }
        });

        listEl.appendChild(button);
    });
}

function aaRememberRecentFont(value) {
    const font = String(value || '').replace(/^["']|["']$/g, '').trim();

    if (!font) return;

    aaPushRecentValue(AA_RECENT_FONT_KEY, font, 10);
    aaRenderRecentFonts();
}
function aaApplyRecentColor(color) {
    color = aaNormalizeRecentColor(color);

    if (!color || !state.canvas) return;

    const targetInput = state.colorDrawerTargetInput;
    if (
        targetInput &&
        targetInput !== els.aaColorDrawerInput &&
        targetInput !== els.aaGuestNameBgInput
    ) {
        if (typeof applyColorDrawerValue === 'function') {
            applyColorDrawerValue(color, true);
        } else {
            targetInput.value = color;
            targetInput.dispatchEvent(new Event('input', {
                bubbles: true
            }));
            targetInput.dispatchEvent(new Event('change', {
                bubbles: true
            }));
        }
        aaRememberRecentColor(color);
        setStatus?.('Warna recent diterapkan');
        return;
    }

    const activeLeftPanel = document.querySelector('[data-aa-left-panel].is-active')?.dataset.aaLeftPanel || '';
    const shouldApplyToBackground =
        state.colorDrawerTargetInput === els.aaBackgroundInput ||
        (!state.colorDrawerTargetInput && activeLeftPanel === 'canvas');

    if (shouldApplyToBackground && els.aaBackgroundInput) {
        if (typeof applyColorDrawerValue === 'function' && state.colorDrawerTargetInput === els.aaBackgroundInput) {
            applyColorDrawerValue(color, true);
        } else {
            els.aaBackgroundInput.value = color;
            els.aaBackgroundInput.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        }
        aaRememberRecentColor(color);
        setStatus?.('Warna background diterapkan');
        return;
    }

    const active = state.canvas.getActiveObject();

    if (!active || active === state.cropBox) return;

    const textTarget = typeof getContextTextTarget === 'function'
        ? getContextTextTarget(active)
        : null;

    if (textTarget) {
        if (typeof aaClearMaterialMetadata === 'function') aaClearMaterialMetadata(textTarget);
        textTarget.set('fill', color);
        textTarget.dirty = true;
    } else if (active.type === 'line') {
        active.set('stroke', color);
    } else if (active.type === 'image') {
        // Image tidak dipaksa fill agar tidak rusak.
        // Kalau ada stroke image, update stroke saja.
        if (Number(active.strokeWidth || 0) > 0) {
            active.set('stroke', color);
        }
    } else {
        if (typeof aaClearMaterialMetadata === 'function') aaClearMaterialMetadata(active);
        active.set('fill', color);
    }

    active.dirty = true;
    active.setCoords?.();

    state.canvas.requestRenderAll();

    syncInspector?.();
    syncContextToolbar?.();
    syncTextContextToolbar?.();

    aaRememberRecentColor(color);
    snapshot?.();

    setStatus?.('Warna recent diterapkan');
}
function aaApplyRecentFont(font, options = {}) {
    font = String(font || '').replace(/^["']|["']$/g, '').trim();

    if (!font || !state.canvas) return;

    if (typeof getFontDrawerPreviewTarget === 'function' &&
        typeof applyFontDrawerSelection === 'function' &&
        getFontDrawerPreviewTarget()) {
        applyFontDrawerSelection(font);
        if (options.remember !== false) {
            aaRememberRecentFont(font);
        }
        setStatus?.('Font recent diterapkan');
        return;
    }

    const active = state.canvas.getActiveObject();

    if (!active || active === state.cropBox) return;

    const textTarget = typeof getContextTextTarget === 'function'
        ? getContextTextTarget(active)
        : null;

    if (!textTarget) return;

    textTarget.set('fontFamily', font);
    textTarget.dirty = true;

    if (typeof textTarget.initDimensions === 'function') {
        textTarget.initDimensions();
    }

    textTarget.setCoords?.();

    active.dirty = true;
    active.setCoords?.();

    state.canvas.requestRenderAll();

    if (els.aaFontInput) {
        els.aaFontInput.value = font;
    }

    syncInspector?.();
    syncTextContextToolbar?.();

    if (options.remember !== false) {
        aaRememberRecentFont(font);
    }
    snapshot?.();

    setStatus?.('Font recent diterapkan');
}
function bindAaRecentColorAndFont() {
    if (state.__aaRecentColorFontBound) return;
    state.__aaRecentColorFontBound = true;

    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('change', function () {
            aaRememberRecentColor(input.value);
        });
    });

    document.querySelectorAll('select').forEach(select => {
        const id = String(select.id || '').toLowerCase();

        if (!id.includes('font')) return;

        select.addEventListener('change', function () {
            aaRememberRecentFont(select.value);
        });
    });

    aaRenderRecentColors();
    aaRenderRecentFonts();
}

function aaBindRecentFontDrawer() {
    if (state.__aaRecentFontDrawerBound) return;
    state.__aaRecentFontDrawerBound = true;

    const drawerList = document.getElementById('aaFontDrawerList');

    if (!drawerList) return;

    drawerList.addEventListener('click', function (event) {
        const item = event.target.closest('[data-font-family], [data-aa-font], button, .aa-font-drawer-item');

        if (!item) return;
        if (item.dataset.aaFontWeightToggle === '1') return;

        const font =
            item.dataset.fontFamily ||
            item.dataset.aaFontFamily ||
            item.dataset.aaFont ||
            item.dataset.font ||
            item.getAttribute('data-value') ||
            item.getAttribute('value') ||
            item.textContent ||
            '';

        const cleanFont = String(font).replace(/^["']|["']$/g, '').trim();

        if (!cleanFont) return;

        window.setTimeout(function () {
            aaRememberRecentFont(cleanFont);
            aaRenderRecentFonts();
        }, 50);
    });
}

function aaOpenCanvasLeftDrawer() {
    const canvasTab = document.querySelector('[data-aa-left-tab="canvas"]');

    if (!canvasTab) return;

    canvasTab.click();

    if (typeof syncCanvasBackgroundPanel === 'function') {
        syncCanvasBackgroundPanel();
    }
}

function bindOpenCanvasDrawerFromPageMode() {
    if (state.__aaOpenCanvasDrawerFromPageModeBound) return;
    state.__aaOpenCanvasDrawerFromPageModeBound = true;

    document.getElementById('aaEditOpeningBtn')?.addEventListener('click', function () {
        setTimeout(function () {
            aaOpenCanvasLeftDrawer();
        }, 0);
    });

    document.getElementById('aaEditPagesBtn')?.addEventListener('click', function () {
        setTimeout(function () {
            aaOpenCanvasLeftDrawer();
        }, 0);
    });

}
