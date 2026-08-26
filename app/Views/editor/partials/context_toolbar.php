    <div id="aaObjectContextMenu" class="aa-object-context-menu" role="menu" aria-label="Object actions">
        <button type="button" data-aa-context-action="bring-front" role="menuitem"><i
                class="fa fa-angles-up"></i><span>Bawa ke depan</span></button>
        <button type="button" data-aa-context-action="bring-forward" role="menuitem"><i
                class="fa fa-arrow-up"></i><span>Maju satu layer</span></button>
        <button type="button" data-aa-context-action="send-backward" role="menuitem"><i
                class="fa fa-arrow-down"></i><span>Mundur satu layer</span></button>
        <button type="button" data-aa-context-action="send-back" role="menuitem"><i
                class="fa fa-angles-down"></i><span>Kirim ke belakang</span></button>
        <hr>
        <button type="button" data-aa-context-action="duplicate" role="menuitem"><i
                class="fa fa-copy"></i><span>Duplicate</span></button>
        <button type="button" data-aa-context-action="copy" role="menuitem"><i class="fa fa-clone"></i><span>Copy
                object</span></button>
        <button type="button" data-aa-context-action="paste" role="menuitem"><i class="fa fa-paste"></i><span>Paste
                object</span></button>
        <hr>
        <button type="button" data-aa-context-action="group" role="menuitem"><i
                class="fa fa-object-group"></i><span>Group</span></button>
        <button type="button" data-aa-context-action="ungroup" role="menuitem"><i
                class="fa fa-object-ungroup"></i><span>Ungroup</span></button>
        <hr>
        <button type="button" data-aa-context-action="lock-toggle" role="menuitem"><i class="fa fa-lock"></i><span>Lock
                object</span></button>
        <button type="button" data-aa-context-action="delete" class="is-danger" role="menuitem"><i
                class="fa fa-trash"></i><span>Delete object</span></button>
    </div>

    <div id="aaObjectFloatingToolbar" class="aa-object-floating-toolbar" role="toolbar"
        aria-label="Selected object actions">
        <button id="aaFloatingLockBtn" class="aa-object-floating-tool" type="button" title="Lock object">
            <i class="fa fa-lock" aria-hidden="true"></i>
        </button>
        <button id="aaFloatingDuplicateBtn" class="aa-object-floating-tool" type="button" title="Duplicate object">
            <i class="fa fa-copy" aria-hidden="true"></i>
        </button>
        <button id="aaFloatingDeleteBtn" class="aa-object-floating-tool is-danger" type="button" title="Delete object">
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
        <button id="aaFloatingInteractionBtn" class="aa-object-floating-tool is-interaction" type="button"
            title="Edit interaction" hidden>
            <i class="fa fa-gear" aria-hidden="true"></i>
        </button>
        <button id="aaFloatingMoreBtn" class="aa-object-floating-tool" type="button" title="More actions">
            <i class="fa fa-ellipsis" aria-hidden="true"></i>
        </button>
    </div>

    <?= view('editor/partials/interaction_popovers', get_defined_vars()) ?>

    <div id="aaMediaDropPreview" class="aa-media-drop-preview" aria-hidden="true">
        <img id="aaMediaDropPreviewImage" src="" alt="">
        <span id="aaMediaDropPreviewLabel">Lepas untuk ganti gambar</span>
    </div>

    <div id="aaCropDomOverlay" class="aa-crop-dom-overlay" aria-hidden="true">
        <span class="aa-crop-dom-target"></span>
        <span class="aa-crop-dom-box">
            <span class="aa-crop-dom-handle" data-crop-handle="nw"></span>
            <span class="aa-crop-dom-handle" data-crop-handle="n"></span>
            <span class="aa-crop-dom-handle" data-crop-handle="ne"></span>
            <span class="aa-crop-dom-handle" data-crop-handle="e"></span>
            <span class="aa-crop-dom-handle" data-crop-handle="se"></span>
            <span class="aa-crop-dom-handle" data-crop-handle="s"></span>
            <span class="aa-crop-dom-handle" data-crop-handle="sw"></span>
            <span class="aa-crop-dom-handle" data-crop-handle="w"></span>
        </span>
    </div>

    <div id="aaCropFloatingToolbar" class="aa-crop-floating-toolbar" role="toolbar" aria-label="Crop actions">
        <button id="aaCropFloatApplyBtn" class="aa-crop-floating-action is-primary" type="button">
            <i class="fa fa-check" aria-hidden="true"></i><span>Apply</span>
        </button>
        <button id="aaCropFloatCancelBtn" class="aa-crop-floating-action" type="button">
            <i class="fa fa-xmark" aria-hidden="true"></i><span>Cancel</span>
        </button>
        <button id="aaCropFloatResetBtn" class="aa-crop-floating-action" type="button">
            <i class="fa fa-rotate-left" aria-hidden="true"></i><span>Reset</span>
        </button>
    </div>

    <div id="aaContextToolbar" class="aa-context-toolbar" role="toolbar" aria-label="Object quick actions">
        <button id="aaContextColorBtn" class="aa-context-tool" type="button" title="Color">
            <span id="aaContextColorSwatch" class="aa-context-swatch" aria-hidden="true"></span>
        </button>
        <input id="aaContextColorInput" class="aa-toolbar-color-input" type="color" value="#111827"
            aria-label="Object color" tabindex="-1">
        <button id="aaContextStrokeBtn" class="aa-context-tool" type="button" title="Stroke weight">
            <span class="aa-stroke-ring-icon" aria-hidden="true"></span>
        </button>
        <button id="aaContextRadiusBtn" class="aa-context-tool" type="button" title="Corner rounding">
            <span class="aa-radius-corner-icon" aria-hidden="true"></span>
        </button>
        <button id="aaContextCropBtn" class="aa-context-tool" type="button" title="Crop"><i style="font-size: 20px;"
                class="fa fa-crop-simple"></i></button>
        <button id="aaContextImageOutlineBtn" class="aa-context-tool" type="button"
            title="Outline gambar">Outline</button>
        <button id="aaContextImageEffectsBtn" class="aa-context-tool" type="button" title="Image effect">Effect</button>
        <button id="aaContextImageFrameBtn" class="aa-context-tool" type="button" title="Image frame">Frame</button>
        <button id="aaContextFlipBtn" class="aa-context-tool" type="button" title="Flip">Flip</button>
        <span class="aa-context-separator" aria-hidden="true"></span>
        <button id="aaContextOpacityBtn" class="aa-context-tool" type="button" title="Opacity"><i
                style="font-size: 20px;" class="fa fa-chess-board"></i></button>
        <button id="aaContextRemoveBgBtn" class="aa-context-tool" type="button"
            title="Remove background">Remove BG<?= $aiPremiumCrownSvg ?? $premiumCrownSvg ?></button>
        <button id="aaContextAnimateBtn" class="aa-context-tool"
            type="button">Animate<?= $premiumCrownSvg ?></button>
    </div>

    <div id="aaContextImageEffectsPopover" class="aa-context-image-effects-popover" role="dialog"
        aria-label="Image effects">
        <p class="aa-context-popover-title">Image Effect</p>
        <div class="aa-image-effect-section">
            <p class="aa-image-effect-section-title">Looks</p>
            <div class="aa-image-effect-grid aa-image-look-grid">
                <button type="button" data-aa-image-effect="none" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-none"></span><span>Original</span>
                </button>
                <button type="button" data-aa-image-effect="soft-wedding" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-soft-wedding"></span><span>Soft Wedding</span>
                </button>
                <button type="button" data-aa-image-effect="clean-bright" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-clean-bright"></span><span>Clean Bright</span>
                </button>
                <button type="button" data-aa-image-effect="warm-editorial" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-warm-editorial"></span><span>Warm Editorial</span>
                </button>
                <button type="button" data-aa-image-effect="film-matte" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-film-matte"></span><span>Film Matte</span>
                </button>
                <button type="button" data-aa-image-effect="pastel-bloom" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-pastel-bloom"></span><span>Pastel Bloom</span>
                </button>
                <button type="button" data-aa-image-effect="moody-luxe" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-moody-luxe"></span><span>Moody Luxe</span>
                </button>
                <button type="button" data-aa-image-effect="classic-bw" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-classic-bw"></span><span>Classic B&W</span>
                </button>
                <button type="button" data-aa-image-effect="dreamy-soft" class="aa-image-effect-option aa-image-effect-option--look">
                    <span class="aa-effect-preview is-dreamy-soft"></span><span>Dreamy Soft</span>
                </button>
            </div>
        </div>
        <div class="aa-image-effect-section">
            <p class="aa-image-effect-section-title">Adjust</p>
            <div class="aa-image-effect-grid aa-image-compact-grid">
                <button type="button" data-aa-image-effect="brightness" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-brightness"></span><span>Brightness</span>
                </button>
                <button type="button" data-aa-image-effect="contrast" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-contrast"></span><span>Contrast</span>
                </button>
                <button type="button" data-aa-image-effect="saturation" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-saturation"></span><span>Saturation</span>
                </button>
                <button type="button" data-aa-image-effect="blur" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-blur"></span><span>Blur</span>
                </button>
                <button type="button" data-aa-image-effect="sharpen" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-sharpen"></span><span>Sharpen</span>
                </button>
                <button type="button" data-aa-image-effect="remove-color" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-remove-color"></span><span>Remove color</span>
                </button>
            </div>
        </div>
        <div class="aa-image-effect-section">
            <p class="aa-image-effect-section-title">Tone Foto</p>
            <div class="aa-image-effect-grid aa-image-compact-grid">
                <button type="button" data-aa-image-effect="recolor-white" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-recolor-white"></span><span>White Tone</span>
                </button>
                <button type="button" data-aa-image-effect="recolor-black" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-recolor-black"></span><span>Mono Dark</span>
                </button>
                <button type="button" data-aa-image-effect="recolor-gold" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-recolor-gold"></span><span>Gold Tone</span>
                </button>
                <button type="button" data-aa-image-effect="recolor-teal" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-recolor-teal"></span><span>Teal Tone</span>
                </button>
                <button type="button" data-aa-image-effect="recolor-rose" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-recolor-rose"></span><span>Rose Tone</span>
                </button>
                <button type="button" data-aa-image-effect="recolor-slate" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-recolor-slate"></span><span>Slate Tone</span>
                </button>
            </div>
        </div>
        <div class="aa-image-effect-section">
            <p class="aa-image-effect-section-title">Overlay</p>
            <div class="aa-image-effect-grid aa-image-compact-grid">
                <button type="button" data-aa-image-overlay="none" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-none"></span><span>None</span>
                </button>
                <button type="button" data-aa-image-overlay="dark-bottom" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-overlay-dark-bottom"></span><span>Dark bottom</span>
                </button>
                <button type="button" data-aa-image-overlay="dark-top" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-overlay-dark-top"></span><span>Dark top</span>
                </button>
                <button type="button" data-aa-image-overlay="vignette" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-overlay-vignette"></span><span>Vignette</span>
                </button>
                <button type="button" data-aa-image-overlay="gold" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-overlay-gold"></span><span>Gold</span>
                </button>
                <button type="button" data-aa-image-overlay="rose" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-overlay-rose"></span><span>Rose</span>
                </button>
                <button type="button" data-aa-image-overlay="ocean" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-overlay-ocean"></span><span>Ocean</span>
                </button>
                <button type="button" data-aa-image-overlay="slate" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-overlay-slate"></span><span>Slate</span>
                </button>
            </div>
        </div>
        <div class="aa-image-effect-section">
            <p class="aa-image-effect-section-title">Style</p>
            <div class="aa-image-effect-grid aa-image-compact-grid">
                <button type="button" data-aa-image-effect="opacity" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-opacity"></span><span>Opacity</span>
                </button>
                <button type="button" data-aa-image-effect="shadow" class="aa-image-effect-option">
                    <span class="aa-effect-preview is-shadow"></span><span>Shadow</span>
                </button>
                <button type="button" data-aa-image-frame="none" class="aa-image-effect-option">
                    <span class="aa-frame-preview is-none"></span><span>No frame</span>
                </button>
                <button type="button" data-aa-image-frame="rounded" class="aa-image-effect-option">
                    <span class="aa-frame-preview is-rounded"></span><span>Rounded</span>
                </button>
                <button type="button" data-aa-image-frame="circle" class="aa-image-effect-option">
                    <span class="aa-frame-preview is-circle"></span><span>Circle</span>
                </button>
                <button type="button" data-aa-image-frame="arch" class="aa-image-effect-option">
                    <span class="aa-frame-preview is-arch"></span><span>Arch</span>
                </button>
            </div>
        </div>
        <div class="aa-image-effect-reset-row">
            <button type="button" class="aa-image-effect-reset-btn" data-aa-image-reset-effects>
                <i class="fa fa-rotate-left" aria-hidden="true"></i>
                <span>Reset Effect</span>
            </button>
        </div>
    </div>

    <div id="aaContextImageFramePopover" class="aa-context-image-frame-popover" role="dialog"
        aria-label="Image frames">
        <p class="aa-context-popover-title">Image Frame</p>
        <div class="aa-image-frame-grid">
            <button type="button" data-aa-image-frame="none" class="aa-image-frame-option"><span
                    class="aa-frame-preview is-none"></span><span>None</span></button>
            <button type="button" data-aa-image-frame="rounded" class="aa-image-frame-option"><span
                    class="aa-frame-preview is-rounded"></span><span>Rounded</span></button>
            <button type="button" data-aa-image-frame="circle" class="aa-image-frame-option"><span
                    class="aa-frame-preview is-circle"></span><span>Circle</span></button>
            <button type="button" data-aa-image-frame="heart" class="aa-image-frame-option"><span
                    class="aa-frame-preview is-heart"></span><span>Love</span></button>
            <button type="button" data-aa-image-frame="arch" class="aa-image-frame-option"><span
                    class="aa-frame-preview is-arch"></span><span>Arch</span></button>
            <button type="button" data-aa-image-frame="diamond" class="aa-image-frame-option"><span
                    class="aa-frame-preview is-diamond"></span><span>Diamond</span></button>
            <button type="button" data-aa-image-frame="blob" class="aa-image-frame-option"><span
                    class="aa-frame-preview is-blob"></span><span>Blob</span></button>
            <button type="button" data-aa-image-frame="ticket" class="aa-image-frame-option"><span
                    class="aa-frame-preview is-ticket"></span><span>Ticket</span></button>
        </div>
        <p class="aa-image-frame-hint">Gunakan Crop untuk menggeser foto di dalam frame.</p>
    </div>

    <div id="aaContextFlipPopover" class="aa-context-flip-popover" role="menu" aria-label="Flip image">
        <button class="aa-context-flip-option" type="button" data-aa-flip-axis="x" role="menuitem">
            <i class="fa fa-rotate-left" aria-hidden="true"></i>
            <span>Flip horizontal</span>
        </button>
        <button class="aa-context-flip-option" type="button" data-aa-flip-axis="y" role="menuitem">
            <i class="fa fa-rotate-right" aria-hidden="true"></i>
            <span>Flip vertical</span>
        </button>
    </div>

    <div id="aaCountdownContextToolbar" class="aa-countdown-context-toolbar" role="toolbar"
        aria-label="Countdown quick actions">
        <label class="aa-countdown-context-field">Date
            <input id="aaCountdownContextDateInput" type="text" inputmode="numeric" maxlength="10"
                placeholder="YYYY-MM-DD">
            <button id="aaCountdownContextDatePickerBtn" class="aa-countdown-date-button" type="button"
                aria-label="Pilih tanggal"><i class="fa fa-calendar-days"></i></button>
        </label>
        <label class="aa-countdown-context-field">Time
            <input id="aaCountdownContextTimeInput" type="time">
        </label>
        <label class="aa-countdown-context-field">Bg
            <input id="aaCountdownContextBgInput" type="color" value="#f8fafc">
        </label>
        <label class="aa-countdown-context-field">Radius
            <input id="aaCountdownContextRadiusInput" type="range" min="0" max="120" step="1" value="24">
        </label>
        <label class="aa-countdown-context-field">Gap
            <input id="aaCountdownContextGapInput" type="range" min="0" max="80" step="1" value="10">
        </label>
        <label class="aa-countdown-context-field">Font
            <select id="aaCountdownContextFontInput"></select>
        </label>
        <label class="aa-countdown-context-field">Size
            <input id="aaCountdownContextSizeInput" type="number" min="8" max="220" step="1" value="36">
        </label>
        <label class="aa-countdown-context-field">Text
            <input id="aaCountdownContextColorInput" type="color" value="#0f172a">
        </label>
    </div>

    <div id="aaCountdownDatePicker" class="aa-date-picker" role="dialog" aria-label="Pilih tanggal acara">
        <span class="aa-date-picker-nav">
            <button id="aaCountdownDatePrevBtn" type="button" aria-label="Bulan sebelumnya"><i
                    class="fa fa-chevron-left"></i></button>
            <span id="aaCountdownDateMonthLabel" class="aa-date-picker-title"></span>
            <button id="aaCountdownDateNextBtn" type="button" aria-label="Bulan berikutnya"><i
                    class="fa fa-chevron-right"></i></button>
        </span>
        <span id="aaCountdownDatePickerHead" class="aa-date-picker-head"></span>
        <span id="aaCountdownDatePickerGrid" class="aa-date-grid"></span>
    </div>

    <div id="aaContextRadiusPopover" class="aa-context-radius-popover" role="dialog" aria-label="Corner rounding">
        <p class="aa-context-radius-title">Corner rounding</p>
        <div class="aa-context-radius-control">
            <input id="aaContextRadiusInput" type="range" min="0" max="540" step="1" value="0">
            <output id="aaContextRadiusValue" class="aa-context-radius-value">0</output>
        </div>
    </div>

    <div id="aaContextStrokePopover" class="aa-context-stroke-popover" role="dialog" aria-label="Stroke weight">
        <div class="aa-context-stroke-options" role="group" aria-label="Stroke style">
            <button class="aa-context-stroke-option" type="button" data-aa-stroke-style="none" title="No stroke">
                <span class="aa-context-stroke-sample is-none" aria-hidden="true"></span>
            </button>
            <button class="aa-context-stroke-option" type="button" data-aa-stroke-style="solid" title="Solid">
                <span class="aa-context-stroke-sample" aria-hidden="true"></span>
            </button>
            <button class="aa-context-stroke-option" type="button" data-aa-stroke-style="dashed" title="Dashed">
                <span class="aa-context-stroke-sample is-dashed" aria-hidden="true"></span>
            </button>
            <button class="aa-context-stroke-option" type="button" data-aa-stroke-style="dotted" title="Dotted">
                <span class="aa-context-stroke-sample is-dotted" aria-hidden="true"></span>
            </button>
        </div>
        <label class="aa-context-stroke-color-row">
            <span>Stroke color</span>
            <input id="aaContextStrokeColorInput" type="color" value="#111827">
        </label>
        <p class="aa-context-stroke-title">Stroke weight</p>
        <div class="aa-context-stroke-control">
            <input id="aaContextStrokeInput" type="range" min="0" max="80" step="1" value="0">
            <output id="aaContextStrokeValue" class="aa-context-stroke-value">0</output>
        </div>
    </div>

    <div id="aaContextTransparencyPopover" class="aa-context-transparency-popover" role="dialog"
        aria-label="Transparency">
        <p class="aa-context-transparency-title">Transparency</p>
        <div class="aa-context-transparency-control">
            <input id="aaContextTransparencyInput" type="range" min="0" max="100" step="1" value="100">
            <output id="aaContextTransparencyValue" class="aa-context-transparency-value">100</output>
        </div>
    </div>

    <div id="aaTextContextToolbar" class="aa-text-context-toolbar" role="toolbar" aria-label="Text quick actions">
        <button id="aaMobileTextEditBtn" class="aa-text-context-tool aa-mobile-text-edit-btn" type="button"
            title="Edit teks"><i class="fa fa-keyboard"></i><span>Edit teks</span></button>
        <select id="aaTextContextFont" class="aa-text-context-select" title="Font"></select>
        <div class="aa-text-context-size" title="Font size">
            <button id="aaTextContextSizeDown" type="button" aria-label="Kurangi ukuran font">-</button>
            <output id="aaTextContextSizeValue">42</output>
            <button id="aaTextContextSizeUp" type="button" aria-label="Tambah ukuran font">+</button>
        </div>
        <button id="aaTextContextColorBtn" class="aa-text-context-tool" type="button" title="Color">
            <span id="aaTextContextColorSwatch" class="aa-context-swatch" aria-hidden="true"></span>
        </button>
        <input id="aaTextContextColorInput" class="aa-toolbar-color-input" type="color" value="#111827"
            aria-label="Text color" tabindex="-1">
        <button id="aaTextContextBoldBtn" class="aa-text-context-tool" type="button" title="Bold"><i
                class="fa fa-bold"></i></button>
        <button id="aaTextContextItalicBtn" class="aa-text-context-tool" type="button" title="Italic"><i
                class="fa fa-italic"></i></button>
        <button id="aaTextContextUnderlineBtn" class="aa-text-context-tool" type="button" title="Underline"><i
                class="fa fa-underline"></i></button>
        <button id="aaTextContextStrikeBtn" class="aa-text-context-tool" type="button" title="Strikethrough"><i
                class="fa fa-strikethrough"></i></button>
        <button id="aaTextContextCaseBtn" class="aa-text-context-tool" type="button" title="Letter case">aA</button>
        <button id="aaTextContextAlignBtn" class="aa-text-context-tool" type="button" title="Align"><i
                class="fa fa-align-left"></i></button>
        <button id="aaTextContextListBtn" class="aa-text-context-tool" type="button" title="List"><i
                class="fa fa-list-ul"></i></button>
        <button id="aaTextContextOpacityBtn" class="aa-text-context-tool" type="button" title="Transparency"><i
                class="fa fa-chess-board"></i></button>
        <span class="aa-context-separator" aria-hidden="true"></span>
        <button id="aaTextContextEffectsBtn" class="aa-text-context-tool"
            type="button">Effects<?= $premiumCrownSvg ?></button>
        <button id="aaTextContextAnimateBtn" class="aa-text-context-tool"
            type="button">Animate<?= $premiumCrownSvg ?></button>
    </div>

    <div id="aaTextEffectsPopover" class="aa-text-effects-popover" role="dialog" aria-label="Text effects">
        <div class="aa-text-effects-grid">
            <div class="aa-text-effects-preset-grid" role="group" aria-label="Text effect presets">
                <button class="aa-text-effect-preset" type="button" data-aa-text-effect-preset="none">
                    <span class="aa-text-effect-preview">Aa</span><span>None</span>
                </button>
                <button class="aa-text-effect-preset" type="button" data-aa-text-effect-preset="soft-shadow">
                    <span class="aa-text-effect-preview">Aa</span><span>Soft Shadow</span>
                </button>
                <button class="aa-text-effect-preset" type="button" data-aa-text-effect-preset="glow">
                    <span class="aa-text-effect-preview">Aa</span><span>Glow</span>
                </button>
                <button class="aa-text-effect-preset" type="button" data-aa-text-effect-preset="outline">
                    <span class="aa-text-effect-preview">Aa</span><span>Outline</span>
                </button>
                <button class="aa-text-effect-preset" type="button" data-aa-text-effect-preset="luxury">
                    <span class="aa-text-effect-preview">Aa</span><span>Luxury</span>
                </button>
                <button class="aa-text-effect-preset" type="button" data-aa-text-effect-preset="neon">
                    <span class="aa-text-effect-preview">Aa</span><span>Neon</span>
                </button>
            </div>
            <div class="aa-text-effects-row">
                <div class="aa-text-effects-row-title">
                    <span>Outline Color</span>
                    <input id="aaTextEffectStrokeColor" type="color" value="#111827" aria-label="Outline color">
                </div>
                <input id="aaTextEffectStrokeWidth" type="hidden" value="0">
                <output id="aaTextEffectStrokeValue" hidden>0</output>
            </div>
            <div class="aa-text-effects-row">
                <div class="aa-text-effects-row-title">
                    <span>Shadow / Glow Color</span>
                    <input id="aaTextEffectShadowColor" type="color" value="#000000" aria-label="Shadow color">
                </div>
                <input id="aaTextEffectShadowBlur" type="hidden" value="0">
                <output id="aaTextEffectShadowBlurValue" hidden>0</output>
                <input id="aaTextEffectShadowOffsetX" type="hidden" value="0">
                <output id="aaTextEffectShadowOffsetXValue" hidden>0</output>
                <input id="aaTextEffectShadowOffsetY" type="hidden" value="0">
                <output id="aaTextEffectShadowOffsetYValue" hidden>0</output>
            </div>
            <div class="aa-text-effects-row">
                <div class="aa-text-effects-row-title">
                    <span>Spacing</span>
                </div>
                <label class="aa-text-effects-control">
                    <span>Letter</span>
                    <input id="aaTextEffectCharSpacing" type="range" min="-100" max="800" step="10" value="0">
                    <output id="aaTextEffectCharSpacingValue">0</output>
                </label>
                <label class="aa-text-effects-control">
                    <span>Line</span>
                    <input id="aaTextEffectLineHeight" type="range" min="80" max="240" step="1" value="114">
                    <output id="aaTextEffectLineHeightValue">1.14</output>
                </label>
            </div>
            <div class="aa-text-effects-actions">
                <button id="aaTextEffectReset" class="aa-text-effects-action" type="button">Reset</button>
            </div>
        </div>
    </div>

    <div id="aaAnimationPopover" class="aa-animation-popover" role="dialog" aria-label="Animation">
        <p class="aa-animation-popover-title">Entrance</p>
        <div class="aa-animation-popover-grid">
            <button data-aa-animation="none" class="aa-animation-option" type="button"><i
                    class="fa fa-ban"></i>None</button>
            <button data-aa-animation="fade-in" class="aa-animation-option" type="button"><i
                    class="fa fa-circle-half-stroke"></i>Fade</button>
            <button data-aa-animation="rise" class="aa-animation-option" type="button"><i
                    class="fa fa-arrow-up"></i>Rise</button>
            <button data-aa-animation="fade-up" class="aa-animation-option" type="button"><i
                    class="fa fa-arrow-up"></i>Fade Up</button>
            <button data-aa-animation="fade-down" class="aa-animation-option" type="button"><i
                    class="fa fa-arrow-down"></i>Fade Down</button>
            <button data-aa-animation="fade-left" class="aa-animation-option" type="button"><i
                    class="fa fa-arrow-left"></i>Fade Left</button>
            <button data-aa-animation="fade-right" class="aa-animation-option" type="button"><i
                    class="fa fa-arrow-right"></i>Fade Right</button>
            <button data-aa-animation="slide-up" class="aa-animation-option" type="button"><i
                    class="fa fa-up-long"></i>Slide Up</button>
            <button data-aa-animation="slide-down" class="aa-animation-option" type="button"><i
                    class="fa fa-down-long"></i>Slide Down</button>
            <button data-aa-animation="slide-left" class="aa-animation-option" type="button"><i
                    class="fa fa-left-long"></i>Slide Left</button>
            <button data-aa-animation="slide-right" class="aa-animation-option" type="button"><i
                    class="fa fa-right-long"></i>Slide Right</button>
            <button data-aa-animation="zoom-in" class="aa-animation-option" type="button"><i
                    class="fa fa-magnifying-glass-plus"></i>Zoom</button>
            <button data-aa-animation="zoom-out" class="aa-animation-option" type="button"><i
                    class="fa fa-magnifying-glass-minus"></i>Zoom Out</button>
            <button data-aa-animation="flip-in" class="aa-animation-option" type="button"><i
                    class="fa fa-retweet"></i>Flip</button>
            <button data-aa-animation="bounce" class="aa-animation-option" type="button"><i
                    class="fa fa-arrow-up-long"></i>Bounce</button>
            <button data-aa-animation="pulse" class="aa-animation-option" type="button"><i
                    class="fa fa-heart-pulse"></i>Pulse</button>
            <button data-aa-animation="swing" class="aa-animation-option" type="button"><i
                    class="fa fa-rotate"></i>Swing</button>
            <button data-aa-animation="spin" class="aa-animation-option" type="button"><i
                    class="fa fa-arrows-rotate"></i>Spin</button>
        </div>
        <p class="aa-animation-popover-title mt-3">Loop Terus</p>
        <div class="aa-animation-popover-grid">
            <button data-aa-animation="float-loop" class="aa-animation-option" type="button"><i
                    class="fa fa-water"></i>Float</button>
            <button data-aa-animation="sway-loop" class="aa-animation-option" type="button"><i
                    class="fa fa-wand-magic-sparkles"></i>Sway</button>
            <button data-aa-animation="pulse-loop" class="aa-animation-option" type="button"><i
                    class="fa fa-heart-pulse"></i>Pulse</button>
            <button data-aa-animation="spin-loop" class="aa-animation-option" type="button"><i
                    class="fa fa-arrows-rotate"></i>Spin</button>
            <button data-aa-animation="heartbeat-loop" class="aa-animation-option" type="button"><i
                    class="fa fa-heart"></i>Beat</button>
            <button data-aa-animation="drift-loop" class="aa-animation-option" type="button"><i
                    class="fa fa-wind"></i>Drift</button>
        </div>
	        <p class="aa-animation-popover-title mt-3" data-aa-animation-timing-title>Timing</p>
	        <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3" data-aa-animation-timing-wrap>
	            <label class="grid gap-1 text-xs font-black text-slate-600" data-aa-animation-timing-field="delay">
	                <span class="flex items-center justify-between gap-2">Delay <output data-aa-animation-delay-output>0ms</output></span>
	                <input data-aa-animation-delay type="range" min="0" max="5000" step="100" value="0">
	            </label>
	            <label class="grid gap-1 text-xs font-black text-slate-600" data-aa-animation-timing-field="duration">
	                <span class="flex items-center justify-between gap-2">Durasi <output data-aa-animation-duration-output>700ms</output></span>
	                <input data-aa-animation-duration type="range" min="200" max="8000" step="100" value="700">
	            </label>
        </div>
    </div>
