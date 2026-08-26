        function defaultOpeningConfig() {
            return {
                enabled: true,
                mode: 'default',
                category: 'default',
                exitAnimation: 'fade',
                objects: [],
                artboard: {
                    width: 1080,
                    height: 1920
                },
                background: '#0f766e'
            };
        }

        function normalizeOpeningConfig(opening) {
            const defaults = defaultOpeningConfig();
            if (!opening || typeof opening !== 'object') return defaults;

            const normalized = {
                ...defaults,
                ...opening,
                enabled: opening.enabled !== false,
                mode: opening.mode || defaults.mode,
                category: opening.category || defaults.category,
                exitAnimation: opening.exitAnimation || defaults.exitAnimation,
                artboard: {
                    ...defaults.artboard,
                    ...(opening.artboard && typeof opening.artboard === 'object' ? opening.artboard : {})
                },
                objects: Array.isArray(opening.objects) ? opening.objects : []
            };

            normalized.objects.forEach(sanitizeFabricObject);
            return normalized;
        }

        function openingToPageData(opening = state.opening) {
            const normalized = normalizeOpeningConfig(opening);
            return sanitizeFabricPageData({
                id: 'opening',
                title: 'Opening / Buka Undangan',
                objects: normalized.objects || [],
                background: normalized.background || '#0f766e',
                backgroundColor: normalized.background || '#0f766e',
                artboard: normalized.artboard || {
                    width: 1080,
                    height: 1920
                },
                hidden: false,
                renderer: 'fabric-opening',
                version: '5.3.0',
            });
        }



        function addOpeningButton() {
            if (state.editMode !== 'opening') {
                switchEditorMode('opening');
                window.setTimeout(addOpeningButton, 120);
                return;
            }

            const group = createLabeledBox('Buka Undangan', '', {
                width: 420,
                height: 96,
                fill: '#0f766e',
                stroke: '#0f766e',
                textFill: '#ffffff',
                radius: 48,
                fontSize: 34,
                props: {
                    customType: 'opening-button',
                    buttonAction: 'open-invitation',
                    buttonText: 'Buka Undangan',
                    controlBackground: '#0f766e',
                    controlTextColor: '#ffffff',
                    controlRadius: 48,
                    openingButtonPadding: 28,
                    openingButtonPaddingY: 28,
                    openingButtonFontFamily: 'Inter',
                    excludeFromAnimation: true
                },
            });
            centerObject(group);
            storeCurrentPage();
            snapshot();
            syncInspector();
            setStatus('Tombol Buka Undangan ditambahkan');
        }

