    <style>
    .aa-fabric-guestbook-control {
        box-sizing: border-box;
    }

    .aa-fabric-selected-sticker {
        top: 5px !important;
        left: 0vw !important;
    }

    html,
    body {
        height: 100%;
    }

    body {
        overflow: hidden;
        background: #0f172a;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    body.aa-editor-tool-limited-mode [data-aa-limited-editor-tab="true"] {
        display: none !important;
    }

    .aa-studio-shell {
        display: grid;
        height: 100vh;
        grid-template-rows: 64px 1fr;
        background: #111827;
        color: #0f172a;
        font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .aa-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border-bottom: 1px solid rgba(148, 163, 184, .18);
        background: linear-gradient(135deg, #0f172a, #111827 55%, #134e4a);
        padding: 0 16px;
        color: #f8fafc;
    }

    .aa-workspace {
        display: grid;
        min-height: 0;
        grid-template-columns: 78px minmax(0, 1fr) 260px;
    }

    .aa-leftbar,
    .aa-rightbar {
        min-height: 0;
        overflow: auto;
        background: #f8fafc;
    }

    .aa-leftbar {
        position: relative;
        z-index: 70;
        overflow: visible;
        background: #ffffff00;
    }

    .aa-rightbar {
        border-left: 1px solid #e2e8f0;
    }

    .aa-toolbar-owned-panel {
        display: none !important;
    }

    .aa-toolbar-color-input {
        position: fixed;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .aa-left-rail {
        display: flex;
        height: 100%;
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
        overflow: visible;
        border-right: 1px solid #e5edf100;
        background: #8d828200;
        padding: 22px 10px;
    }

    .aa-left-rail-link,
    .aa-left-rail-tab {
        position: relative;
        display: grid;
        min-height: 64px;
        place-items: center;
        gap: 5px;
        border: 0;
        border-radius: 14px;
        background: transparent;
        color: #4b5563;
        padding: 7px 4px;
        font: inherit;
        font-size: 11px;
        font-weight: 850;
        line-height: 1.1;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-left-rail-link i,
    .aa-left-rail-tab i,
    .aa-left-rail-link svg,
    .aa-left-rail-tab svg {
        width: 21px;
        height: 21px;
        stroke-width: 1.9;
    }

    .aa-left-rail-img-icon {
        width: 24px;
        height: 24px;
        object-fit: contain;
        display: block;
    }

    .aa-left-rail-tab .aa-premium-crown {
        position: absolute;
        top: 7px;
        right: 7px;
        width: 16px;
        height: 16px;
    }

    .aa-left-rail-tab .aa-premium-crown svg {
        width: 11px;
        height: 11px;
    }

    .aa-left-rail-link:hover,
    .aa-left-rail-tab:hover,
    .aa-left-rail-tab.is-active {
        background: #ffffff;
        color: #0f766e;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
    }

    .aa-left-rail-spacer {
        height: 8px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 4px;
    }

    .aa-left-drawer {
        position: absolute;
        top: 0;
        left: 78px;
        display: grid;
        align-content: start;
        align-items: start;
        grid-auto-rows: max-content;
        width: 390px;
        max-width: calc(100vw - 396px);
        height: 100%;
        overflow-y: auto;
        opacity: 0;
        pointer-events: none;
        transform: translateX(-10px);
        transition: opacity .16s ease, transform .16s ease;
    }

    .aa-leftbar.is-drawer-open .aa-left-drawer,
    .aa-left-drawer.is-pinned {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0);
    }

    .aa-leftbar.is-acara-ai-drawer .aa-left-drawer {
        overflow: hidden;
    }

    .aa-left-drawer-panel {
        display: none;
        align-content: start;
        gap: 14px;
    }

    .aa-left-drawer-panel.is-active {
        display: grid;
    }

    .aa-stage-wrap {
        position: relative;
        min-width: 0;
        min-height: 0;
        overflow: auto;
        background-size: 900px auto;
        background-repeat: repeat;
        background-position: center;
        padding: 50px 42px 42px;
    }

    body.aa-editor-browser-zoom-locked .aa-studio-shell,
    body.aa-editor-browser-zoom-locked .aa-stage-wrap,
    body.aa-editor-browser-zoom-locked .canvas-container,
    body.aa-editor-browser-zoom-locked .upper-canvas,
    body.aa-editor-browser-zoom-locked .lower-canvas {
        touch-action: pan-x pan-y;
    }

    .aa-stage-viewport {
        position: relative;
        min-width: 1px;
        min-height: 1px;
        margin: 0 auto;
    }

    .aa-stage {
        position: absolute;
        top: 0;
        left: 0;
        width: max-content;
        min-width: min(100%, 980px);
        transform-origin: top left;
    }

    .editor-pages-scroll,
    .aa-page-list {
        display: grid;
        gap: 34px;
        justify-items: center;
        width: 100%;
    }

    .editor-page-block {
        position: relative;
        z-index: 1;
        display: grid;
        gap: 45px;
        justify-items: center;
        width: max-content;
        min-width: min(100%, 760px);
        max-width: none;
    }

    .editor-page-block.active {
        z-index: 20;
        color: inherit;
    }

    .editor-page-block.is-hidden-page {
        opacity: .64;
    }

    .page-top-controls {
        display: flex;
        width: clamp(520px, 68vw, 860px);
        min-height: 68px;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #ffffff;
        padding: 10px 12px 10px 20px;
        color: #334155;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .1);
    }

    .page-title-button {
        min-width: 0;
        border: 0;
        background: transparent;
        color: inherit;
        padding: 0;
        font: inherit;
        font-size: 28px;
        font-weight: 950;
        text-align: left;
        cursor: pointer;
    }

    .page-title-button span {
        display: block;
        max-width: 560px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .page-menu-wrap {
        position: relative;
        z-index: 120;
    }

    .page-more-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 1000;
        display: none;
        min-width: 250px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        padding: 6px;
        box-shadow: 0 22px 54px rgba(15, 23, 42, .18);
    }

    .page-menu-wrap.is-open .page-more-menu {
        display: grid;
        gap: 4px;
    }

    .page-menu-item {
        display: flex;
        width: 100%;
        min-height: 50px;
        align-items: center;
        gap: 9px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #334155;
        padding: 0 10px;
        font-size: 26px;
        font-weight: 850;
        text-align: left;
        cursor: pointer;
    }

    .page-menu-item:hover {
        background: #f1f5f9;
    }

    .page-menu-item:disabled {
        cursor: not-allowed;
        opacity: .42;
    }

    .page-menu-item.is-danger {
        color: #be123c;
    }

    .page-insert-row {
        display: flex;
        min-height: 58px;
        align-items: center;
        justify-content: center;
        width: clamp(520px, 68vw, 860px);
    }

    .page-insert-button {
        display: inline-flex;
        min-width: min(100%, 360px);
        min-height: 48px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px dashed #94a3b8;
        border-radius: 999px;
        background: rgba(255, 255, 255, .8);
        color: #334155;
        padding: 0 16px;
        font-size: 28px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
    }

    .page-insert-button:hover {
        border-color: #0ea5e9;
        background: #ffffff;
        color: #075985;
    }

    .canvas-page-wrapper {
        position: relative;
        z-index: 1;
        width: max-content;
        max-width: 100%;
        overflow: visible;
    }

    .aa-artboard-frame {
        position: relative;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .24);
        overflow: visible;
    }

    .aa-artboard-frame .canvas-container {
        overflow: visible !important;
    }

    .aa-stage.is-page-loading .page-top-controls,
    .aa-stage.is-page-loading .page-insert-row,
    .aa-stage.is-page-loading .aa-editor-mode-strip,
    .aa-stage.is-page-loading .aa-page-preview-frame {
        visibility: hidden;
    }

    .aa-stage.is-page-loading .aa-artboard-frame {
        background: transparent;
        box-shadow: none;
    }

    .aa-stage.is-page-loading .aa-artboard-frame > .canvas-container,
    .aa-stage.is-page-loading .aa-artboard-frame > canvas {
        opacity: 0;
    }

    .aa-stage:not(.is-page-loading) .aa-artboard-frame > .canvas-container,
    .aa-stage:not(.is-page-loading) .aa-artboard-frame > canvas {
        opacity: 1;
        transition: opacity .12s ease;
    }

    .aa-page-preview-frame {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 22px 60px rgba(15, 23, 42, .14);
        cursor: pointer;
    }

    .aa-page-preview-frame canvas {
        display: block;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

   .aa-page-preview-overlay {
    position: absolute;
    inset: 0;
    z-index: 10;
    display: grid;
    place-items: center;
    background: rgba(15, 23, 42, .42);
    color: #ffffff !important;
    font-size: 42px;
    font-weight: 500;
    line-height: 1.15;
    text-align: center;
    letter-spacing: .02em;
    opacity: 0;
    transform: scale(.98);
    transition:
        opacity .18s ease,
        transform .18s ease,
        background .18s ease;
    pointer-events: none;
    text-shadow: 0 4px 18px rgba(0, 0, 0, .35);
}   

    .aa-page-preview-frame:hover .aa-page-preview-overlay {
        opacity: 1;
    }

    .aa-editor-guestbook-preview {
        width: min(520px, 100%);
        margin: 22px auto 0;
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .16);
    }

    .aa-editor-guestbook-preview.is-hidden {
        display: none;
    }

    .aa-editor-guestbook-preview .aa-guestbook {
        padding: 42px 18px;
        background: var(--aa-gb-bg, #f8fafc);
        color: var(--aa-gb-text, #101828);
        font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .aa-editor-guestbook-preview .aa-guestbook-wrap {
        max-width: 920px;
        margin: 0 auto;
    }

    .aa-editor-guestbook-preview .aa-guestbook-head {
        margin-bottom: 24px;
        text-align: center;
    }

    .aa-editor-guestbook-preview .aa-guestbook-head p {
        margin: 0 0 8px;
        color: var(--aa-gb-accent, #0f766e);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .aa-editor-guestbook-preview .aa-guestbook-head h2 {
        margin: 0;
        font-size: 30px;
        line-height: 1.1;
    }

    .aa-editor-guestbook-preview .aa-guestbook-head .aa-guestbook-subtitle {
        margin: 12px auto 0;
        color: var(--aa-gb-muted, #667085);
        line-height: 1.6;
        text-transform: none;
        letter-spacing: 0;
        font-size: 13px;
        font-weight: 500;
    }

    .aa-editor-guestbook-preview .aa-guestbook-form {
        display: grid;
        gap: 12px;
        border: 1px solid #e4e7ec;
        border-radius: var(--aa-gb-radius, 22px);
        background: var(--aa-gb-card, #ffffff);
        padding: 18px;
        box-shadow: 0 14px 36px rgba(16, 24, 40, .06);
    }

    .aa-editor-guestbook-preview .aa-guestbook-label {
        display: grid;
        gap: 7px;
        font-size: 12px;
        font-weight: 800;
    }

    .aa-editor-guestbook-preview .aa-guestbook-input,
    .aa-editor-guestbook-preview .aa-guestbook-select,
    .aa-editor-guestbook-preview .aa-guestbook-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d0d5dd;
        border-radius: 12px;
        padding: 10px 12px;
        background: #ffffff;
        color: #101828;
        font: inherit;
        font-size: 12px;
    }

    .aa-editor-guestbook-preview .aa-guestbook-submit {
        border: 0;
        border-radius: 14px;
        background: var(--aa-gb-accent, #0f766e);
        color: #ffffff;
        padding: 12px 16px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-editor-guestbook-preview .aa-sticker-button {
        border: 0;
        border-radius: 14px;
        background: #ecfdf5;
        color: var(--aa-gb-accent, #0f766e);
        padding: 9px 12px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-editor-guestbook-preview .aa-comment-list {
        display: grid;
        gap: 10px;
        max-height: var(--aa-gb-max-height, 380px);
        overflow-y: auto;
        margin-top: 18px;
    }

    .aa-editor-guestbook-preview .aa-comment-card,
    .aa-editor-guestbook-preview .aa-comment-empty {
        border: 1px solid #e4e7ec;
        border-radius: 16px;
        background: var(--aa-gb-card, #ffffff);
        padding: 14px;
    }

    .aa-canvas-loading {
        position: absolute;
        inset: 0;
        z-index: 80;
        display: none;
        place-items: center;
        overflow: hidden;
        background: rgba(248, 250, 252, .28);
        color: #0f766e;
        font-size: 18px;
        font-weight: 950;
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
        pointer-events: none;
    }

    .aa-canvas-loading.is-visible {
        display: grid;
    }

    .aa-canvas-loading::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(110deg, transparent 0%, rgba(255, 255, 255, .52) 45%, transparent 78%);
        transform: translateX(-100%);
        animation: aaCanvasLoadingSweep 1.55s ease-in-out infinite;
    }

    .aa-canvas-loading-label {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .94);
        padding: 14px 18px;
        color: #0f766e;
        text-shadow: 0 1px 2px rgba(255, 255, 255, .72);
        box-shadow: 0 18px 46px rgba(15, 23, 42, .16);
    }

    .aa-canvas-loading i {
        animation: aaSpin .9s linear infinite;
    }

    .aa-magic-layer-process-overlay {
        position: absolute;
        inset: 0;
        z-index: 118;
        display: grid;
        place-items: center;
        overflow: hidden;
        border-radius: 18px;
        background: rgba(15, 23, 42, .34);
        opacity: 0;
        pointer-events: none;
        transition: opacity .22s ease, transform .22s ease;
        transform: scale(.985);
        backdrop-filter: blur(1.5px);
        -webkit-backdrop-filter: blur(1.5px);
    }

    .aa-magic-layer-process-overlay.is-visible {
        opacity: 1;
        transform: scale(1);
    }

    .aa-magic-layer-process-overlay::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(110deg, transparent 0%, rgba(255, 255, 255, .24) 45%, transparent 70%);
        transform: translateX(-120%);
        animation: aaCanvasLoadingSweep 1.35s ease-in-out infinite;
    }

    .aa-magic-layer-process-card {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        max-width: min(74vw, 380px);
        min-height: 44px;
        border: 1px solid rgba(255, 255, 255, .34);
        border-radius: 999px;
        background: rgba(255, 255, 255, .92);
        color: #0f172a;
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 950;
        line-height: 1.2;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .22);
        transform: scale(var(--aa-magic-layer-process-card-scale, 1));
        transform-origin: center;
    }

    .aa-magic-layer-process-card i {
        flex: 0 0 auto;
        color: #0f766e;
        font-size: 14px;
        animation: aaSpin .9s linear infinite;
    }

    .aa-magic-layer-process-card span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    @keyframes aaCanvasLoadingSweep {
        0% {
            transform: translateX(-100%);
        }

        54%,
        100% {
            transform: translateX(100%);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .aa-canvas-loading::before {
            animation-duration: 3s;
        }
    }

    .aa-image-process-overlay {
        position: fixed;
        z-index: 1200;
        display: grid;
        place-items: center;
        overflow: hidden;
        border-radius: 18px;
        background: rgba(15, 23, 42, .34);
        opacity: 0;
        transform: scale(.985);
        pointer-events: none;
        transition: opacity .22s ease, transform .22s ease, left .18s ease, top .18s ease, width .18s ease, height .18s ease;
        backdrop-filter: blur(1.5px);
        -webkit-backdrop-filter: blur(1.5px);
    }

    .aa-image-process-overlay.is-visible {
        opacity: 1;
        transform: scale(1);
    }

    .aa-image-process-overlay.is-leaving {
        opacity: 0;
        transform: scale(.99);
    }

    .aa-image-process-card {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(255, 255, 255, .34);
        border-radius: 999px;
        background: rgba(255, 255, 255, .92);
        color: #0f172a;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 950;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .22);
    }

    .aa-image-process-card i {
        color: #0f766e;
        animation: aaSpin .9s linear infinite;
    }

    .aa-image-process-shimmer {
        position: absolute;
        inset: 0;
        background: linear-gradient(110deg, transparent 0%, rgba(255, 255, 255, .24) 45%, transparent 70%);
        transform: translateX(-120%);
        animation: aaImageProcessShimmer 1.35s ease-in-out infinite;
    }

    @keyframes aaImageProcessShimmer {
        to {
            transform: translateX(120%);
        }
    }

    .aa-object-context-menu {
        position: fixed;
        z-index: 1000;
        display: none;
        min-width: 210px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 14px;
        background: #ffffff;
        padding: 6px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
    }

    .aa-object-context-menu.is-open {
        display: grid;
        gap: 3px;
    }

    .aa-object-context-menu button {
        display: flex;
        width: 100%;
        min-height: 36px;
        align-items: center;
        gap: 10px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #334155;
        padding: 0 10px;
        font: inherit;
        font-size: 12px;
        font-weight: 850;
        text-align: left;
        cursor: pointer;
    }

    .aa-object-context-menu button:hover:not(:disabled) {
        background: #f1f5f9;
        color: #0f172a;
    }

    .aa-object-context-menu button:disabled {
        cursor: not-allowed;
        opacity: .45;
    }

    .aa-object-context-menu button.is-danger {
        color: #be123c;
    }

    .aa-object-context-menu hr {
        width: 100%;
        height: 1px;
        border: 0;
        background: #e2e8f0;
        margin: 4px 0;
    }

    .aa-object-floating-toolbar {
        position: fixed;
        z-index: 65;
        display: none;
        align-items: center;
        gap: 4px;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 12px;
        background: rgba(255, 255, 255, .96);
        padding: 4px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, .16);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        transform: scale(0.85);
    }

    .aa-object-floating-toolbar.is-visible {
        display: inline-flex;
    }

    .aa-object-floating-tool {
        display: inline-grid;
        width: 32px;
        height: 30px;
        place-items: center;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #0f172a;
        cursor: pointer;
        font-size: 13px;
    }

    .aa-object-floating-tool[hidden],
    .aa-object-floating-tool.is-photobooth-hidden {
        display: none !important;
        pointer-events: none;
    }

    .aa-object-floating-tool:hover {
        background: #f1f5f9;
        color: #0f766e;
    }

    .aa-object-floating-tool.is-interaction {
        background: #ecfdf5;
        color: #0f766e;
    }

    .aa-object-floating-tool.is-interaction:hover {
        background: #ccfbf1;
        color: #0f766e;
    }

    .aa-object-floating-tool.is-danger {
        color: #be123c;
    }

    .aa-object-floating-tool.is-danger:hover {
        background: #fff1f2;
        color: #be123c;
    }

    .aa-interaction-popover {
        position: fixed;
        z-index: 78;
        display: none;
        width: min(320px, calc(100vw - 24px));
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 16px;
        background: rgba(255, 255, 255, .98);
        padding: 12px;
        color: #0f172a;
        box-shadow: 0 22px 60px rgba(15, 23, 42, .2);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .aa-interaction-popover.is-visible {
        display: block;
    }

    .aa-interaction-popover-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-interaction-popover-section {
        display: none;
    }

    .aa-interaction-popover-section.is-active {
        display: grid;
        gap: 9px;
    }

    .aa-interaction-popover-section.is-compact {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: end;
    }

    .aa-interaction-popover-section.is-compact .aa-interaction-popover-title,
    .aa-interaction-popover-section.is-compact .aa-popover-full {
        grid-column: 1 / -1;
    }

    .aa-interaction-popover label {
        display: grid;
        gap: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
    }

    .aa-interaction-popover input,
    .aa-interaction-popover select,
    .aa-interaction-popover textarea {
        width: 100%;
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 11px;
        background: #ffffff;
        padding: 8px 10px;
        color: #0f172a;
        font: inherit;
        font-size: 12px;
        font-weight: 700;
        outline: none;
    }

    .aa-interaction-popover input[type="range"] {
        min-height: 24px;
        padding: 0;
        accent-color: #0f766e;
    }

    .aa-popover-range {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 42px;
        gap: 8px;
        align-items: center;
    }

    .aa-popover-range output {
        display: inline-grid;
        min-height: 34px;
        place-items: center;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-interaction-popover textarea {
        min-height: 78px;
        resize: vertical;
    }

    .aa-interaction-popover .aa-interaction-check {
        display: flex;
        min-height: 32px;
        align-items: center;
        grid-template-columns: none;
    }

    .aa-interaction-popover .aa-interaction-check input {
        width: auto;
        min-height: 0;
        margin: 0;
    }

    @media (max-width: 680px) {
        .aa-interaction-popover-section.is-compact {
            grid-template-columns: 1fr;
        }
    }

    .aa-interaction-popover input:focus,
    .aa-interaction-popover select:focus,
    .aa-interaction-popover textarea:focus {
        border-color: #146cb8;
        box-shadow: 0 0 0 3px rgba(20, 108, 184, .14);
    }

    .aa-media-drop-preview {
        position: fixed;
        z-index: 76;
        display: none;
        overflow: hidden;
        border: 2px solid rgba(20, 108, 184, .9);
        border-radius: 14px;
        background: rgba(15, 23, 42, .18);
        box-shadow: 0 18px 50px rgba(15, 23, 42, .24), inset 0 0 0 1px rgba(255, 255, 255, .35);
        pointer-events: none;
    }

    .aa-media-drop-preview.is-visible {
        display: block;
    }

    .aa-media-drop-preview.is-locked {
        border-color: rgba(190, 18, 60, .92);
    }

    .aa-media-drop-preview img {
        display: block;
        width: 100%;
        height: 100%;
        opacity: .82;
        object-fit: cover;
    }

    .aa-media-drop-preview span {
        position: absolute;
        left: 50%;
        bottom: 10px;
        transform: translateX(-50%);
        border-radius: 999px;
        background: rgba(15, 23, 42, .82);
        color: #ffffff;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 950;
        white-space: nowrap;
    }

    .aa-media-drop-preview.is-locked span {
        background: rgba(190, 18, 60, .9);
    }

    .aa-context-toolbar,
    .aa-text-context-toolbar,
    .aa-countdown-context-toolbar {
        position: fixed;
        top: 76px;
        left: 50%;
        z-index: 60;
        display: none;
        align-items: center;
        gap: 4px;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 14px;
        background:
            radial-gradient(circle at 18% 0%, rgba(45, 212, 191, .18), transparent 30%),
            radial-gradient(circle at 78% 14%, rgba(59, 130, 246, .12), transparent 26%),
            rgba(255, 255, 255, .88);
        padding: 5px;
        color: #0f172a;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .18);
        transform: translateX(-50%);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .aa-context-toolbar.is-visible,
    .aa-text-context-toolbar.is-visible,
    .aa-countdown-context-toolbar.is-visible {
        display: inline-flex;
    }

    .aa-countdown-context-toolbar {
        flex-wrap: wrap;
        width: min(360px, calc(100vw - 24px));
        justify-content: flex-start;
        padding: 8px;
        transform: none;
    }

    .aa-countdown-context-field {
        display: inline-flex;
        height: 34px;
        align-items: center;
        gap: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: 0 8px;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
    }

    .aa-countdown-context-field input[type="text"],
    .aa-countdown-context-field input[type="time"],
    .aa-countdown-context-field input[type="number"] {
        width: 92px;
        border: 0;
        background: transparent;
        color: #0f172a;
        font: inherit;
        font-size: 12px;
        outline: none;
    }

    .aa-countdown-context-field input[type="time"] {
        width: 74px;
    }

    .aa-countdown-context-field input[type="number"] {
        width: 50px;
    }

    .aa-countdown-context-field select {
        width: 108px;
        border: 0;
        background: transparent;
        color: #0f172a;
        font: inherit;
        font-size: 12px;
        outline: none;
    }

    .aa-countdown-context-field input[type="color"] {
        width: 26px;
        height: 24px;
        border: 0;
        background: transparent;
        padding: 0;
    }

    .aa-countdown-context-field input[type="range"] {
        width: 74px;
        accent-color: #7c3aed;
    }

    .aa-countdown-date-button {
        display: inline-grid;
        width: 26px;
        height: 26px;
        place-items: center;
        border: 0;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        cursor: pointer;
        font-size: 12px;
    }

    .aa-countdown-date-button:hover {
        background: #ede9fe;
        color: #6d28d9;
    }

    .aa-context-tool,
    .aa-text-context-tool {
        display: inline-flex;
        min-width: 36px;
        height: 34px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #0f172a;
        padding: 0 9px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-context-tool:hover:not(:disabled),
    .aa-text-context-tool:hover:not(:disabled) {
        background: #f1f5f9;
        color: #0f766e;
    }

    .aa-context-tool:disabled,
    .aa-text-context-tool:disabled {
        cursor: not-allowed;
        opacity: .38;
    }

    .aa-context-tool.is-loading {
        color: #0f766e;
        background: #ecfdf5;
        opacity: 1;
    }

    .aa-text-context-tool.is-active {
        background: #f3e8ff;
        color: #6d28d9;
    }

    .aa-premium-crown {
        display: inline-grid;
        width: 16px;
        height: 16px;
        place-items: center;
        color: currentColor;
        flex: 0 0 auto;
    }

    .aa-premium-crown svg {
        width: 16px;
        height: 16px;
        display: block;
    }

.aa-premium-crown.is-locked {
    color: #facc15;
    filter: drop-shadow(0 1px 0 rgba(20, 0, 0, .0)) drop-shadow(0 0 5px rgba(20, 0, 0, .0));
}

    .aa-premium-crown.is-unlocked {
        color: rgba(100, 116, 139, .62);
        filter: drop-shadow(0 1px 0 rgba(255, 255, 255, .56));
    }

    .aa-left-drawer .aa-tool-btn,
    .aa-left-drawer .aa-panel-btn {
        position: relative;
    }

    .aa-left-drawer .aa-tool-btn .aa-premium-crown,
    .aa-left-drawer .aa-panel-btn .aa-premium-crown {
        position: absolute;
        top: 7px;
        right: 7px;
        width: 17px;
        height: 17px;
    }

    .aa-left-drawer .aa-tool-btn .aa-premium-crown svg,
    .aa-left-drawer .aa-panel-btn .aa-premium-crown svg {
        width: 12px;
        height: 12px;
    }

    .aa-business-elements {
        display: none;
        gap: 14px;
    }

    body.aa-business-profile-editor .aa-invitation-elements {
        display: none !important;
    }

    body.aa-business-profile-editor .aa-business-elements {
        display: grid;
    }

    .aa-business-element-category-view,
    .aa-business-element-detail-view {
        display: grid;
        gap: 12px;
    }

    .aa-business-element-detail-view[hidden] {
        display: none !important;
    }

    .aa-business-element-category-list {
        display: grid;
        gap: 6px;
        border: 1px solid #eef2f7;
        border-radius: 16px;
        background: #ffffff;
        overflow: hidden;
    }

    .aa-business-element-category-btn {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr) 20px;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        border: 0;
        border-bottom: 1px solid #eef2f7;
        background: #ffffff;
        color: #334155;
        padding: 0 10px;
        font: inherit;
        font-size: 13px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-business-element-category-btn:last-child {
        border-bottom: 0;
    }

    .aa-business-element-category-btn:hover,
    .aa-business-element-category-btn.is-active {
        background: #fff1f7;
        color: #db2777;
    }

    .aa-business-element-category-btn:disabled {
        cursor: not-allowed;
        color: #94a3b8;
        opacity: .82;
    }

    .aa-business-element-category-btn:disabled:hover {
        background: #ffffff;
        color: #94a3b8;
    }

    .aa-business-element-category-btn em {
        display: inline-flex;
        align-items: center;
        min-height: 18px;
        margin-left: 6px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        padding: 0 7px;
        font-size: 9px;
        font-style: normal;
        font-weight: 950;
        line-height: 1;
        vertical-align: 1px;
    }

    .aa-business-element-category-btn .aa-lucide-icon,
    .aa-business-element-back-btn .aa-lucide-icon,
    .aa-business-element-detail-head .aa-lucide-icon {
        width: 18px;
        height: 18px;
        stroke-width: 2;
    }

    .aa-business-element-category-btn span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-business-element-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        min-height: 44px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        color: #475569;
        padding: 0 12px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }

    .aa-business-element-detail-head {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        border: 1px solid #ffe4ef;
        border-radius: 16px;
        background: linear-gradient(135deg, #fff7fb, #ffffff);
        padding: 14px;
        color: #db2777;
    }

    .aa-business-element-detail-head > .aa-lucide-icon {
        width: 34px;
        height: 34px;
    }

    .aa-business-element-detail-head h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 950;
        line-height: 1.1;
    }

    .aa-business-element-detail-head p {
        margin: 5px 0 0;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.35;
    }

    .aa-business-element-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .aa-business-element-grid .aa-tool-btn {
        min-height: 94px;
        border-color: #ffd6e7;
        border-radius: 14px;
        color: #172033;
        font-size: 12px;
        line-height: 1.25;
        text-align: center;
    }

    .aa-business-element-grid .aa-tool-btn:hover {
        border-color: #fb7185;
        background: #fff1f7;
        color: #db2777;
    }

    .aa-business-element-grid .aa-tool-btn .aa-lucide-icon {
        color: #ec4899;
    }

    .aa-business-element-note {
        margin: 2px 0 0;
        border: 1px solid #ffe4ef;
        border-radius: 14px;
        background: #fff1f7;
        color: #475569;
        padding: 12px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    .aa-snippet-card {
        display: grid;
        gap: 12px;
    }

    .aa-snippet-search-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #dbe7ef;
        border-radius: 14px;
        background: #ffffff;
        padding: 9px 11px;
        color: #0f766e;
    }

    .aa-snippet-search-wrap input {
        min-width: 0;
        flex: 1;
        border: 0;
        outline: 0;
        background: transparent;
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
    }

    .aa-snippet-search-wrap input::placeholder {
        color: #94a3b8;
    }

    .aa-snippet-category-list {
        display: flex;
        gap: 7px;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: thin;
    }

    .aa-snippet-category-btn {
        flex: 0 0 auto;
        border: 1px solid #dbe7ef;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        padding: 7px 11px;
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
    }

    .aa-snippet-category-btn:hover,
    .aa-snippet-category-btn.is-active {
        border-color: #0f766e;
        background: #ecfdf5;
        color: #0f766e;
    }

    .aa-snippet-list {
        display: grid;
        gap: 9px;
        max-height: calc(100vh - 265px);
        overflow: auto;
        padding-right: 2px;
    }

    .aa-snippet-item {
        display: grid;
        gap: 6px;
        width: 100%;
        border: 1px solid #dbe7ef;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 11px;
        text-align: left;
        cursor: pointer;
    }

    .aa-snippet-item:hover {
        border-color: #14b8a6;
        box-shadow: 0 12px 28px rgba(15, 118, 110, .10);
    }

    .aa-snippet-item-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        color: #0f766e;
        font-size: 10px;
        font-weight: 1000;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .aa-snippet-item-title i {
        color: #14b8a6;
    }

    .aa-snippet-item-text {
        margin: 0;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    .aa-snippet-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 14px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-align: center;
    }

    .editor-access-modal[hidden] {
        display: none !important;
    }

    .editor-access-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: grid;
        place-items: center;
        padding: 20px;
    }

    .editor-access-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .48);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .editor-access-card {
        position: relative;
        width: min(92vw, 430px);
        border: 1px solid rgba(255, 255, 255, .62);
        border-radius: 24px;
        background: rgba(255, 255, 255, .86);
        box-shadow: 0 28px 90px rgba(15, 23, 42, .28);
        padding: 28px;
        color: #0f172a;
        text-align: center;
        transform: translateY(8px) scale(.96);
        opacity: 0;
        animation: aaAccessModalIn .18s ease forwards;
    }

    @keyframes aaAccessModalIn {
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    .editor-access-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 999px;
        background: rgba(15, 23, 42, .08);
        color: #0f172a;
        cursor: pointer;
        font-size: 24px;
        line-height: 1;
    }

    .editor-access-icon {
        display: inline-grid;
        width: 54px;
        height: 54px;
        place-items: center;
        margin-bottom: 14px;
        color: #facc15;
        filter: drop-shadow(0 1px 0 rgba(20, 108, 184, .75)) drop-shadow(0 0 8px rgba(20, 108, 184, .32));
    }

    .editor-access-icon svg {
        width: 28px;
        height: 28px;
        position: absolute;
    }

    .editor-access-card h3 {
        margin: 0 0 10px;
        font-size: 22px;
        font-weight: 900;
        letter-spacing: 0;
    }

    .editor-access-card p {
        margin: 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.6;
        font-weight: 650;
    }

    .editor-access-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 22px;
    }

    .editor-access-primary,
    .editor-access-secondary {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0 18px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
    }

    .editor-access-primary {
        border: 0;
        background: #0f172a;
        color: #fff;
    }

    .editor-access-secondary {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #334155;
    }

    .aa-text-context-select {
        width: 122px;
        height: 34px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 28px 0 10px;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        outline: none;
    }

    .aa-text-context-size {
        display: inline-flex;
        height: 34px;
        align-items: center;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
    }

    .aa-text-context-size button {
        display: inline-grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border: 0;
        background: transparent;
        color: #0f172a;
        font: inherit;
        font-size: 14px;
        font-weight: 950;
        cursor: pointer;
    }

    .aa-text-context-size button:hover {
        background: #f1f5f9;
    }

    .aa-text-context-size output {
        min-width: 44px;
        text-align: center;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-radius-corner-icon {
        display: inline-block;
        width: 18px;
        height: 18px;
        border-top: 2px solid currentColor;
        border-left: 2px solid currentColor;
        border-top-left-radius: 12px;
    }

    .aa-stroke-ring-icon {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 6px solid currentColor;
        border-radius: 999px;
    }

    .aa-context-radius-popover,
    .aa-context-transparency-popover,
    .aa-context-stroke-popover,
    .aa-context-image-outline-popover,
    .aa-context-image-effects-popover,
    .aa-context-image-frame-popover,
    .aa-context-flip-popover,
    .aa-text-effects-popover {
        position: fixed;
        top: 118px;
        left: 50%;
        z-index: 70;
        display: none;
        width: 256px;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 18px;
        background: #ffffff;
        padding: 16px;
        color: #0f172a;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .2);
        transform: translateX(-50%);
    }

    .aa-context-radius-popover.is-open,
    .aa-context-transparency-popover.is-open,
    .aa-context-stroke-popover.is-open,
    .aa-context-image-outline-popover.is-open,
    .aa-context-image-effects-popover.is-open,
    .aa-context-image-frame-popover.is-open,
    .aa-context-flip-popover.is-open,
    .aa-text-effects-popover.is-open {
        display: block;
    }

    .aa-context-image-effects-popover,
    .aa-context-image-frame-popover {
        width: 332px;
        max-width: calc(100vw - 24px);
        max-height: calc(100vh - 150px);
        overflow-y: auto;
        padding: 14px;
        border-radius: 20px;
        background: rgba(255, 255, 255, .98);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .aa-context-image-effects-popover.is-open,
    .aa-context-image-frame-popover.is-open {
        display: grid;
        gap: 12px;
    }

    .aa-context-popover-title {
        margin: 0;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .aa-image-effect-grid,
    .aa-image-frame-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

	    .aa-image-effect-section {
	        display: grid;
	        gap: 8px;
	    }

	    .aa-image-frame-section {
	        display: grid;
	        gap: 8px;
	        margin-top: 12px;
	    }

	    .aa-frame-placeholder-actions {
	        display: grid;
	        grid-template-columns: repeat(2, minmax(0, 1fr));
	        gap: 8px;
	        margin-top: 12px;
	    }

    .aa-frame-placeholder-actions.hidden {
        display: none !important;
    }

    .aa-mobile-frame-shapes .aa-frame-preview {
        width: 42px;
        max-width: 42px;
        aspect-ratio: 1;
        margin: 0 auto;
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .28), rgba(14, 165, 233, .16) 45%, rgba(244, 114, 182, .18)),
            repeating-linear-gradient(45deg, rgba(15, 23, 42, .08) 0 4px, rgba(255, 255, 255, .72) 4px 8px),
            #e2f5f1;
        border: 1px solid rgba(15, 118, 110, .24);
        box-shadow:
            inset 0 0 0 2px rgba(255, 255, 255, .7),
            0 8px 16px rgba(15, 118, 110, .12);
    }

    .aa-mobile-frame-shapes {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

	    .aa-image-effect-section + .aa-image-effect-section {
	        margin-top: 2px;
        border-top: 1px solid #eef2f7;
        padding-top: 10px;
    }

    .aa-image-effect-section-title {
        margin: 0;
        color: #64748b;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .aa-image-look-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .aa-image-compact-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
    }

    .aa-image-effect-option,
    .aa-image-frame-option {
        display: grid;
        gap: 6px;
        min-width: 0;
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        background: #f8fafc;
        padding: 7px;
        color: #334155;
        font: inherit;
        font-size: 10px;
        font-weight: 850;
        line-height: 1.1;
        text-align: center;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-image-compact-grid .aa-image-effect-option {
        gap: 5px;
        border-radius: 11px;
        padding: 5px;
        font-size: 9px;
        font-weight: 900;
    }

    .aa-image-effect-option--look {
        padding: 6px;
        border-radius: 14px;
        background: #ffffff;
    }

    .aa-image-effect-option:hover,
    .aa-image-effect-option.is-active,
    .aa-image-frame-option:hover,
    .aa-image-frame-option.is-active {
        border-color: #14b8a6;
        background: #ecfdf5;
        color: #0f766e;
        box-shadow: 0 10px 22px rgba(15, 118, 110, .11);
    }

    .aa-image-effect-reset-row {
        margin-top: 12px;
        border-top: 1px solid #eef2f7;
        padding-top: 12px;
    }

    .aa-image-effect-reset-btn {
        display: inline-flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid #cbd5e1;
        border-radius: 13px;
        background: #ffffff;
        padding: 10px 12px;
        color: #334155;
        font: inherit;
        font-size: 12px;
        font-weight: 950;
        line-height: 1;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-image-effect-reset-btn:hover {
        border-color: #14b8a6;
        background: #f0fdfa;
        color: #0f766e;
        box-shadow: 0 12px 24px rgba(15, 118, 110, .1);
    }

    .aa-effect-preview,
    .aa-frame-preview {
        display: block;
        width: 100%;
        aspect-ratio: 1.25;
        border-radius: 10px;
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .22), transparent 45%),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
        background-image:
            linear-gradient(135deg, rgba(15, 118, 110, .22), transparent 45%),
            var(--aa-effect-preview-image, linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0));
        background-size: cover;
        background-position: center;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .65);
    }

    .aa-image-effect-option--look .aa-effect-preview {
        aspect-ratio: 1.12;
        border-radius: 12px;
    }

    .aa-image-compact-grid .aa-effect-preview,
    .aa-image-compact-grid .aa-frame-preview {
        aspect-ratio: 1.2;
        border-radius: 8px;
    }

    .aa-effect-preview.is-brightness { filter: brightness(1.25); }
    .aa-effect-preview.is-contrast { filter: contrast(1.35); }
    .aa-effect-preview.is-saturation { filter: saturate(1.8); }
    .aa-effect-preview.is-grayscale { filter: grayscale(1); }
    .aa-effect-preview.is-sepia { filter: sepia(.9); }
    .aa-effect-preview.is-blur { filter: blur(1.3px); }
    .aa-effect-preview.is-sharpen { filter: contrast(1.35) saturate(1.18); }
    .aa-effect-preview.is-vintage { filter: sepia(.42) contrast(1.12) saturate(.78); }
    .aa-effect-preview.is-soft-wedding { filter: brightness(1.08) contrast(.96) saturate(1.18) sepia(.08); }
    .aa-effect-preview.is-clean-bright { filter: brightness(1.14) contrast(1.08) saturate(1.08); }
    .aa-effect-preview.is-warm-editorial { filter: sepia(.18) brightness(1.06) contrast(1.12) saturate(1.14); }
    .aa-effect-preview.is-film-matte { filter: sepia(.2) contrast(.92) saturate(.78) brightness(1.04); }
    .aa-effect-preview.is-pastel-bloom { filter: brightness(1.1) contrast(.94) saturate(1.32) hue-rotate(-6deg); }
    .aa-effect-preview.is-moody-luxe { filter: brightness(.88) contrast(1.22) saturate(.9) sepia(.08); }
    .aa-effect-preview.is-classic-bw { filter: grayscale(1) contrast(1.18) brightness(1.04); }
    .aa-effect-preview.is-dreamy-soft { filter: brightness(1.12) contrast(.9) saturate(1.12) blur(.55px); }
    .aa-effect-preview.is-opacity { opacity: .55; }
    .aa-effect-preview.is-shadow { box-shadow: 0 10px 16px rgba(15, 23, 42, .24); }
    .aa-effect-preview.is-remove-color { filter: saturate(.2) contrast(1.12); }
    .aa-effect-preview.is-recolor-white { filter: grayscale(.35) brightness(1.34) contrast(.86) saturate(.68); }
    .aa-effect-preview.is-recolor-black { filter: grayscale(1) brightness(.72) contrast(1.28); }
    .aa-effect-preview.is-recolor-gold { filter: sepia(.55) saturate(1.45) hue-rotate(4deg) brightness(1.08) contrast(1.04); }
    .aa-effect-preview.is-recolor-teal { filter: sepia(.18) saturate(1.35) hue-rotate(135deg) brightness(.96) contrast(1.06); }
    .aa-effect-preview.is-recolor-rose { filter: sepia(.22) saturate(1.35) hue-rotate(300deg) brightness(1.04) contrast(.98); }
    .aa-effect-preview.is-recolor-slate { filter: grayscale(.65) sepia(.12) saturate(.7) hue-rotate(170deg) brightness(.92) contrast(1.08); }
    .aa-image-effect-subtitle { margin-top: 14px; }
    .aa-effect-preview.is-overlay-dark-bottom {
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0) 20%, rgba(15, 23, 42, .68)),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
    }
    .aa-effect-preview.is-overlay-dark-top {
        background:
            linear-gradient(180deg, rgba(15, 23, 42, .66), rgba(15, 23, 42, 0) 78%),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
    }
    .aa-effect-preview.is-overlay-vignette {
        background:
            radial-gradient(circle at 50% 48%, rgba(15, 23, 42, 0) 34%, rgba(15, 23, 42, .68) 100%),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
    }
    .aa-effect-preview.is-overlay-gold {
        background:
            linear-gradient(135deg, rgba(180, 126, 35, .5), rgba(255, 255, 255, 0) 46%, rgba(15, 23, 42, .18)),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
    }
    .aa-effect-preview.is-overlay-sunset {
        background:
            linear-gradient(135deg, rgba(244, 114, 23, .46), rgba(236, 72, 153, .34) 48%, rgba(30, 41, 59, .34)),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
    }
    .aa-effect-preview.is-overlay-rose {
        background:
            radial-gradient(circle at 28% 24%, rgba(244, 114, 182, .54), rgba(255, 255, 255, 0) 45%),
            linear-gradient(160deg, rgba(190, 24, 93, .22), rgba(15, 23, 42, .3)),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
    }
    .aa-effect-preview.is-overlay-ocean {
        background:
            linear-gradient(135deg, rgba(14, 116, 144, .42), rgba(37, 99, 235, .38), rgba(15, 23, 42, .28)),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
    }
    .aa-effect-preview.is-overlay-slate {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, .5), rgba(71, 85, 105, .18), rgba(255, 255, 255, 0)),
            linear-gradient(45deg, #fbcfe8, #bfdbfe 48%, #bbf7d0);
    }

    .aa-frame-preview.is-none { border-radius: 4px; }
    .aa-frame-preview.is-rounded { border-radius: 18px; }
    .aa-frame-preview.is-circle { border-radius: 999px; aspect-ratio: 1; width: 76%; margin: 0 auto; }
    .aa-frame-preview.is-heart { clip-path: polygon(50% 86%, 17% 57%, 9% 35%, 15% 17%, 31% 10%, 44% 17%, 50% 28%, 56% 17%, 69% 10%, 85% 17%, 91% 35%, 83% 57%); }
    .aa-frame-preview.is-arch { border-radius: 999px 999px 14px 14px; }
    .aa-frame-preview.is-diamond { clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%); }
    .aa-frame-preview.is-blob { border-radius: 42% 58% 52% 48% / 54% 42% 58% 46%; }
    .aa-frame-preview.is-oval { border-radius: 999px / 72%; }
    .aa-frame-preview.is-shield { clip-path: polygon(50% 0, 88% 16%, 82% 58%, 50% 100%, 18% 58%, 12% 16%); }
    .aa-frame-preview.is-hexagon { clip-path: polygon(25% 0, 75% 0, 100% 50%, 75% 100%, 25% 100%, 0 50%); }
    .aa-frame-preview.is-petal { clip-path: polygon(50% 0, 88% 8%, 100% 34%, 72% 50%, 100% 66%, 88% 92%, 50% 100%, 12% 92%, 0 66%, 28% 50%, 0 34%, 12% 8%); }
    .aa-frame-preview.is-wave { clip-path: polygon(0 16%, 18% 4%, 42% 18%, 66% 5%, 100% 18%, 100% 84%, 78% 96%, 52% 82%, 25% 96%, 0 82%); }
    .aa-frame-preview.is-tag { clip-path: polygon(0 0, 72% 0, 100% 50%, 72% 100%, 0 100%); }
    .aa-frame-preview.is-bookmark { clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 72%, 0 100%); }
    .aa-frame-preview.is-scallop { clip-path: polygon(30% 0, 50% 10%, 70% 0, 86% 14%, 100% 30%, 90% 50%, 100% 70%, 86% 86%, 70% 100%, 50% 90%, 30% 100%, 14% 86%, 0 70%, 10% 50%, 0 30%, 14% 14%); }

    .aa-mobile-frame-shapes .aa-frame-preview.is-circle {
        width: 42px;
        max-width: 42px;
    }

    .aa-mobile-frame-shapes .aa-frame-preview.is-none,
    .aa-mobile-frame-shapes .aa-frame-preview.is-rounded,
    .aa-mobile-frame-shapes .aa-frame-preview.is-arch,
    .aa-mobile-frame-shapes .aa-frame-preview.is-heart,
    .aa-mobile-frame-shapes .aa-frame-preview.is-diamond,
    .aa-mobile-frame-shapes .aa-frame-preview.is-blob {
        aspect-ratio: 1;
    }
    .aa-frame-preview.is-ticket { clip-path: polygon(0 0, 100% 0, 100% 38%, 88% 50%, 100% 62%, 100% 100%, 0 100%, 0 62%, 12% 50%, 0 38%); }

    .aa-image-frame-hint {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
        line-height: 1.35;
    }

    .aa-animation-popover {
        position: fixed;
        z-index: 62;
        display: none;
        width: min(360px, calc(100vw - 24px));
        max-height: min(520px, calc(100vh - 96px));
        overflow: auto;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 16px;
        background: rgba(255, 255, 255, .98);
        padding: 10px;
        color: #0f172a;
        box-shadow: 0 22px 60px rgba(15, 23, 42, .2);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .aa-animation-popover.is-open {
        display: block;
    }

    body.aa-selection-transform-active .aa-object-floating-toolbar,
    body.aa-selection-transform-active .aa-object-context-menu {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    .aa-outside-selection-overlay {
        position: fixed;
        inset: 0;
        z-index: 64;
        display: none;
        pointer-events: none;
        --aa-outside-selection-scale: 1;
    }

    .aa-outside-selection-overlay.is-visible {
        display: block;
    }

    .aa-outside-selection-pane {
        position: fixed;
        overflow: hidden;
        pointer-events: none;
    }

    .aa-outside-selection-box {
        position: absolute;
        box-sizing: border-box;
        border: calc(4.2px * var(--aa-outside-selection-scale)) solid #7c3aed;
        filter: drop-shadow(0 calc(8px * var(--aa-outside-selection-scale)) calc(16px * var(--aa-outside-selection-scale)) rgba(15, 23, 42, .14));
        cursor: move;
        pointer-events: auto;
        touch-action: none;
        transform-origin: 0 0;
    }

    .aa-outside-selection-corner,
    .aa-outside-selection-pill,
    .aa-outside-selection-rotate {
        position: absolute;
        box-sizing: border-box;
        border: calc(3.6px * var(--aa-outside-selection-scale)) solid #7c3aed;
        background: #ffffff;
        box-shadow: 0 calc(8px * var(--aa-outside-selection-scale)) calc(16px * var(--aa-outside-selection-scale)) rgba(15, 23, 42, .18);
        pointer-events: none;
    }

    .aa-outside-selection-overlay.is-transforming .aa-outside-selection-corner,
    .aa-outside-selection-overlay.is-transforming .aa-outside-selection-pill,
    .aa-outside-selection-overlay.is-transforming .aa-outside-selection-rotate {
        opacity: 0;
        pointer-events: none;
    }

    .aa-outside-selection-overlay.is-transforming .is-active-transform-handle {
        opacity: 1;
        pointer-events: auto;
    }

    .aa-outside-selection-overlay.is-native-transforming .aa-outside-selection-corner,
    .aa-outside-selection-overlay.is-native-transforming .aa-outside-selection-pill,
    .aa-outside-selection-overlay.is-native-transforming .aa-outside-selection-rotate {
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .aa-outside-selection-corner {
        width: calc(22px * var(--aa-outside-selection-scale));
        height: calc(22px * var(--aa-outside-selection-scale));
        border-radius: 999px;
        pointer-events: auto;
    }

    .aa-outside-selection-corner.is-tl {
        left: calc(-11px * var(--aa-outside-selection-scale));
        top: calc(-11px * var(--aa-outside-selection-scale));
    }

    .aa-outside-selection-corner.is-tr {
        right: calc(-11px * var(--aa-outside-selection-scale));
        top: calc(-11px * var(--aa-outside-selection-scale));
    }

    .aa-outside-selection-corner.is-br {
        right: calc(-11px * var(--aa-outside-selection-scale));
        bottom: calc(-11px * var(--aa-outside-selection-scale));
    }

    .aa-outside-selection-corner.is-bl {
        left: calc(-11px * var(--aa-outside-selection-scale));
        bottom: calc(-11px * var(--aa-outside-selection-scale));
    }

    .aa-outside-selection-corner.is-tl,
    .aa-outside-selection-corner.is-br {
        cursor: nwse-resize;
    }

    .aa-outside-selection-corner.is-tr,
    .aa-outside-selection-corner.is-bl {
        cursor: nesw-resize;
    }

    .aa-outside-selection-pill {
        border-radius: 999px;
    }

    .aa-outside-selection-pill.is-horizontal {
        left: 50%;
        width: calc(36px * var(--aa-outside-selection-scale));
        height: calc(12px * var(--aa-outside-selection-scale));
        transform: translateX(-50%);
    }

    .aa-outside-selection-pill.is-horizontal.is-top {
        top: calc(-6px * var(--aa-outside-selection-scale));
    }

    .aa-outside-selection-pill.is-horizontal.is-bottom {
        bottom: calc(-6px * var(--aa-outside-selection-scale));
    }

    .aa-outside-selection-pill.is-vertical {
        top: 50%;
        width: calc(10px * var(--aa-outside-selection-scale));
        height: calc(30px * var(--aa-outside-selection-scale));
        transform: translateY(-50%);
    }

    .aa-outside-selection-pill.is-vertical.is-left {
        left: calc(-5px * var(--aa-outside-selection-scale));
    }

    .aa-outside-selection-pill.is-vertical.is-right {
        right: calc(-5px * var(--aa-outside-selection-scale));
    }

    .aa-outside-selection-overlay.is-image-target .aa-outside-selection-pill {
        pointer-events: auto;
    }

    .aa-outside-selection-overlay.is-image-target .aa-outside-selection-pill.is-horizontal {
        cursor: ns-resize;
    }

    .aa-outside-selection-overlay.is-image-target .aa-outside-selection-pill.is-vertical {
        cursor: ew-resize;
    }

    .aa-outside-selection-rotate {
        left: 50%;
        bottom: calc(-92px * var(--aa-outside-selection-scale));
        display: grid;
        width: calc(56px * var(--aa-outside-selection-scale));
        height: calc(56px * var(--aa-outside-selection-scale));
        place-items: center;
        border-radius: 999px;
        color: #7c3aed;
        cursor: grab;
        pointer-events: auto;
        transform: translateX(-50%);
        box-shadow: 0 calc(12px * var(--aa-outside-selection-scale)) calc(24px * var(--aa-outside-selection-scale)) rgba(124, 58, 237, .2);
    }

    .aa-outside-selection-rotate:active {
        cursor: grabbing;
    }

    .aa-outside-selection-rotate svg {
        width: calc(32px * var(--aa-outside-selection-scale));
        height: calc(32px * var(--aa-outside-selection-scale));
        display: block;
    }

    .aa-animation-popover-title {
        margin: 0 0 8px;
        color: #475569;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .aa-animation-popover-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
    }

    .aa-animation-option {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #ffffff;
        color: #334155;
        padding: 0 8px;
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
    }

    .aa-animation-option:hover,
    .aa-animation-option.is-active {
        border-color: #a78bfa;
        background: #f3e8ff;
        color: #6d28d9;
    }

    .aa-context-flip-popover {
        width: 160px;
        padding: 10px;
    }

    .aa-context-flip-option {
        display: flex;
        width: 100%;
        min-height: 38px;
        align-items: center;
        gap: 10px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #0f172a;
        padding: 0 10px;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
    }

    .aa-context-flip-option:hover {
        background: #f1f5f9;
        color: #6d28d9;
    }

    .aa-text-effects-popover {
        width: min(360px, calc(100vw - 24px));
        padding: 14px;
    }

    .aa-text-effects-grid {
        display: grid;
        gap: 12px;
    }

    .aa-text-effects-preset-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .aa-text-effect-preset {
        display: grid;
        min-height: 58px;
        align-content: center;
        gap: 4px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        color: #0f172a;
        padding: 8px 10px;
        font: inherit;
        font-size: 11px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
    }

    .aa-text-effect-preset:hover,
    .aa-text-effect-preset.is-active {
        border-color: #a78bfa;
        background: #f5f3ff;
        color: #6d28d9;
    }

    .aa-text-effect-preview {
        display: block;
        color: #ffffff;
        font-size: 18px;
        font-weight: 950;
        line-height: 1;
        -webkit-text-stroke: var(--aa-effect-preview-stroke, 0px) var(--aa-effect-preview-stroke-color, transparent);
        text-shadow: var(--aa-effect-preview-shadow, none);
    }

    .aa-text-effect-preset[data-aa-text-effect-preset="none"] .aa-text-effect-preview {
        color: #111827;
    }

    .aa-text-effect-preset[data-aa-text-effect-preset="soft-shadow"] .aa-text-effect-preview {
        --aa-effect-preview-shadow: 0 4px 10px rgba(15, 23, 42, .35);
    }

    .aa-text-effect-preset[data-aa-text-effect-preset="glow"] .aa-text-effect-preview {
        --aa-effect-preview-shadow: 0 0 10px rgba(255, 255, 255, .92), 0 0 18px rgba(20, 184, 166, .72);
    }

    .aa-text-effect-preset[data-aa-text-effect-preset="outline"] .aa-text-effect-preview {
        --aa-effect-preview-stroke: 1px;
        --aa-effect-preview-stroke-color: #111827;
    }

    .aa-text-effect-preset[data-aa-text-effect-preset="luxury"] .aa-text-effect-preview {
        color: #f8fafc;
        --aa-effect-preview-stroke: 1px;
        --aa-effect-preview-stroke-color: #8b5a2b;
        --aa-effect-preview-shadow: 0 8px 18px rgba(146, 64, 14, .34);
    }

    .aa-text-effect-preset[data-aa-text-effect-preset="neon"] .aa-text-effect-preview {
        color: #f8fafc;
        --aa-effect-preview-shadow: 0 0 8px rgba(168, 85, 247, .95), 0 0 18px rgba(14, 165, 233, .72);
    }

    .aa-text-effects-row {
        display: grid;
        gap: 7px;
    }

    .aa-text-effects-row-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-text-effects-row-title input[type="color"] {
        width: 34px;
        height: 30px;
        border: 1px solid #d6dce7;
        border-radius: 10px;
        background: #ffffff;
        padding: 3px;
    }

    .aa-text-effects-control {
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr) 42px;
        align-items: center;
        gap: 10px;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
    }

    .aa-text-effects-control input[type="range"] {
        width: 100%;
        accent-color: #9333ea;
    }

    .aa-text-effects-control output {
        display: inline-grid;
        width: 42px;
        height: 32px;
        place-items: center;
        border: 1px solid #d6dce7;
        border-radius: 10px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-text-effects-actions {
        display: flex;
        gap: 8px;
    }

    .aa-text-effects-action {
        min-height: 34px;
        flex: 1;
        border: 0;
        border-radius: 10px;
        background: #f1f5f9;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
    }

    .aa-text-effects-action:hover {
        background: #e2e8f0;
        color: #6d28d9;
    }

    .aa-crop-dom-overlay {
        position: fixed;
        z-index: 86;
        display: none;
        pointer-events: none;
    }

    .aa-crop-dom-overlay.is-visible {
        display: block;
    }

    .aa-crop-dom-target,
    .aa-crop-dom-box {
        position: fixed;
        pointer-events: none;
    }

    .aa-crop-dom-target {
        border: 1px dashed rgba(20, 108, 184, .55);
        background: rgba(20, 108, 184, .08);
    }

    .aa-crop-dom-box {
        border: 2px solid #146cb8;
        background: rgba(20, 184, 166, .08);
        box-shadow: 0 0 0 9999px rgba(15, 23, 42, .08);
        cursor: move;
        pointer-events: auto;
    }

    .aa-crop-dom-handle {
        position: absolute;
        width: 14px;
        height: 14px;
        border: 2px solid #ffffff;
        border-radius: 999px;
        background: #146cb8;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .22);
        pointer-events: auto;
    }

    .aa-crop-dom-handle[data-crop-handle="nw"],
    .aa-crop-dom-handle[data-crop-handle="ne"],
    .aa-crop-dom-handle[data-crop-handle="se"],
    .aa-crop-dom-handle[data-crop-handle="sw"] {
        display: none !important;
    }

    .aa-crop-dom-box {
        cursor: move !important;
    }

    .aa-crop-dom-handle[data-crop-handle="n"],
    .aa-crop-dom-handle[data-crop-handle="s"] {
        cursor: ns-resize !important;
    }

    .aa-crop-dom-handle[data-crop-handle="e"],
    .aa-crop-dom-handle[data-crop-handle="w"] {
        cursor: ew-resize !important;
    }

    .aa-crop-dom-handle[data-crop-handle="nw"] {
        top: -8px;
        left: -8px;
        cursor: nwse-resize;
    }

    .aa-crop-dom-handle[data-crop-handle="n"] {
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        cursor: ns-resize;
    }

    .aa-crop-dom-handle[data-crop-handle="ne"] {
        top: -8px;
        right: -8px;
        cursor: nesw-resize;
    }

    .aa-crop-dom-handle[data-crop-handle="e"] {
        top: 50%;
        right: -8px;
        transform: translateY(-50%);
        cursor: ew-resize;
    }

    .aa-crop-dom-handle[data-crop-handle="se"] {
        right: -8px;
        bottom: -8px;
        cursor: nwse-resize;
    }

    .aa-crop-dom-handle[data-crop-handle="s"] {
        left: 50%;
        bottom: -8px;
        transform: translateX(-50%);
        cursor: ns-resize;
    }

    .aa-crop-dom-handle[data-crop-handle="sw"] {
        bottom: -8px;
        left: -8px;
        cursor: nesw-resize;
    }

    .aa-crop-dom-handle[data-crop-handle="w"] {
        top: 50%;
        left: -8px;
        transform: translateY(-50%);
        cursor: ew-resize;
    }

    .aa-crop-floating-toolbar {
        position: fixed;
        z-index: 89;
        display: none;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 14px;
        background:
            radial-gradient(circle at 18% 0%, rgba(45, 212, 191, .18), transparent 30%),
            radial-gradient(circle at 78% 14%, rgba(59, 130, 246, .12), transparent 26%),
            rgba(255, 255, 255, .88);
        padding: 6px;
        color: #0f172a;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .2);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .aa-crop-floating-toolbar.is-visible {
        display: inline-flex;
    }

    .aa-crop-floating-action {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #0f172a;
        padding: 0 10px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }

    .aa-crop-floating-action:hover {
        background: #f1f5f9;
        color: #0f766e;
    }

    .aa-crop-floating-action.is-primary {
        background: #146cb8;
        color: #ffffff;
    }

    .aa-crop-floating-action.is-primary:hover {
        background: #0f5f9f;
        color: #ffffff;
    }

    .aa-context-radius-title,
    .aa-context-transparency-title,
    .aa-context-stroke-title,
    .aa-context-outline-title {
        margin: 0 0 12px;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
    }

    .aa-context-radius-control,
    .aa-context-transparency-control,
    .aa-context-stroke-control,
    .aa-context-outline-control {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 42px;
        align-items: center;
        gap: 14px;
    }

    .aa-context-radius-control input[type="range"],
    .aa-context-transparency-control input[type="range"],
    .aa-context-stroke-control input[type="range"],
    .aa-context-outline-control input[type="range"] {
        width: 100%;
        accent-color: #9333ea;
    }

    .aa-context-radius-value,
    .aa-context-transparency-value,
    .aa-context-stroke-value,
    .aa-context-outline-value {
        display: inline-grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 1px solid #d6dce7;
        border-radius: 12px;
        color: #334155;
        font-size: 14px;
        font-weight: 800;
    }

    .aa-context-stroke-options {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 9px;
        margin-bottom: 14px;
    }

    .aa-context-stroke-option {
        display: inline-grid;
        height: 40px;
        place-items: center;
        border: 1px solid #d6dce7;
        border-radius: 10px;
        background: #ffffff;
        color: #111827;
        cursor: pointer;
    }

    .aa-context-stroke-option.is-active {
        border-color: #9333ea;
        box-shadow: 0 0 0 2px rgba(147, 51, 234, .16);
    }

    .aa-context-stroke-sample {
        display: inline-block;
        width: 24px;
        border-top: 2px solid currentColor;
    }

    .aa-context-stroke-sample.is-none {
        position: relative;
        width: 19px;
        height: 19px;
        border: 1.8px solid currentColor;
        border-radius: 999px;
    }

    .aa-context-stroke-sample.is-none::after {
        content: "";
        position: absolute;
        top: 8px;
        left: 1px;
        width: 17px;
        border-top: 1.8px solid currentColor;
        transform: rotate(-42deg);
    }

    .aa-context-stroke-sample.is-dashed {
        border-top-style: dashed;
    }

    .aa-context-stroke-sample.is-dotted {
        border-top-style: dotted;
    }

    .aa-context-stroke-color-row,
    .aa-context-outline-color-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 800;
    }

    .aa-context-stroke-color-row input[type="color"],
    .aa-context-outline-color-row input[type="color"] {
        width: 42px;
        height: 36px;
        border: 1px solid #d6dce7;
        border-radius: 10px;
        background: #ffffff;
        padding: 3px;
        cursor: pointer;
    }

    .aa-outline-color-picker {
        display: grid;
        gap: 10px;
        margin-bottom: 14px;
    }

    .aa-outline-color-title,
    .aa-outline-color-input-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-outline-color-title span:last-child {
        color: #64748b;
        font-size: 11px;
        letter-spacing: .04em;
    }

    .aa-outline-color-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 24px;
        gap: 10px;
        align-items: stretch;
    }

    .aa-outline-color-field {
        position: relative;
        display: block;
        aspect-ratio: 1.12;
        min-height: 152px;
        border: 1px solid rgba(15, 23, 42, .14);
        border-radius: 14px;
        background:
            linear-gradient(to top, #000, transparent),
            linear-gradient(to right, #fff, var(--aa-outline-hue, #ff0000));
        cursor: crosshair;
        overflow: hidden;
        padding: 0;
    }

    .aa-outline-color-handle {
        position: absolute;
        left: var(--aa-outline-handle-x, 100%);
        top: var(--aa-outline-handle-y, 0%);
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 999px;
        box-shadow: 0 0 0 1px rgba(15, 23, 42, .42), 0 4px 10px rgba(15, 23, 42, .18);
        transform: translate(-50%, -50%);
        pointer-events: none;
    }

    .aa-outline-hue-input {
        width: 152px;
        height: 22px;
        align-self: center;
        transform: rotate(90deg);
        transform-origin: center;
        accent-color: #ef4444;
    }

    .aa-outline-color-preview {
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        border: 1px solid rgba(15, 23, 42, .16);
        border-radius: 10px;
        background: var(--aa-outline-current, #ffffff);
        box-shadow: inset 0 0 0 2px rgba(255, 255, 255, .78);
    }

    .aa-outline-color-hex {
        width: 100%;
        min-height: 36px;
        border: 1px solid #d6dce7;
        border-radius: 12px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        outline: none;
    }

    .aa-outline-native-color {
        position: absolute;
        width: 1px !important;
        height: 1px !important;
        opacity: 0;
        pointer-events: none;
    }

    .aa-color-eyedropper-btn {
        display: inline-flex;
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border: 1px solid #d6dce7;
        border-radius: 10px;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: border-color .16s ease, color .16s ease, background .16s ease;
    }

    .aa-color-eyedropper-btn:hover {
        border-color: #0f766e;
        background: #ecfdf5;
        color: #0f766e;
    }

    .aa-outline-color-swatches {
        display: grid;
        grid-template-columns: repeat(8, minmax(0, 1fr));
        gap: 7px;
    }

    .aa-outline-color-swatch {
        aspect-ratio: 1;
        border: 2px solid #ffffff;
        border-radius: 999px;
        background: var(--aa-outline-swatch, #ffffff);
        box-shadow: 0 0 0 1px rgba(15, 23, 42, .14);
        cursor: pointer;
        padding: 0;
    }

    .aa-outline-color-swatch:hover {
        box-shadow: 0 0 0 2px rgba(15, 118, 110, .42);
    }

    .aa-context-outline-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 12px;
    }

    .aa-context-outline-actions button {
        border: 1px solid #d6dce7;
        border-radius: 999px;
        background: #ffffff;
        color: #475569;
        padding: 8px 12px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }

    .aa-context-outline-actions button:hover {
        border-color: #0f766e;
        color: #0f766e;
    }

    .aa-context-outline-actions button:last-child {
        border-color: #0f766e;
        background: #0f766e;
        color: #ffffff;
    }

    .aa-context-outline-actions button:last-child:hover {
        background: #115e59;
        color: #ffffff;
    }

    .aa-context-outline-hint {
        margin: 10px 0 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.5;
    }

    .aa-context-swatch {
        display: inline-block;
        width: 22px;
        height: 22px;
        border: 2px solid #ffffff;
        border-radius: 999px;
        background: #8b5a3c;
        box-shadow: 0 0 0 1px rgba(15, 23, 42, .18);
    }

    .aa-context-separator {
        width: 1px;
        height: 22px;
        background: #e2e8f0;
        margin: 0 2px;
    }

    .aa-date-field {
        position: relative;
        display: flex;
        align-items: center;
    }

    .aa-date-field .aa-field {
        padding-right: 42px;
    }

    .aa-date-button {
        position: absolute;
        right: 5px;
        display: inline-grid;
        width: 30px;
        height: 30px;
        place-items: center;
        border: 0;
        border-radius: 10px;
        background: #ecfdf5;
        color: #0f766e;
        cursor: pointer;
    }

    .aa-date-button:hover {
        background: #ccfbf1;
    }

    .aa-date-picker {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 80;
        display: none;
        width: min(286px, calc(100vw - 24px));
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        padding: 10px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
    }

    .aa-date-picker.is-open {
        display: block;
    }

    .aa-date-picker-head,
    .aa-date-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 4px;
    }

    .aa-date-picker-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }

    .aa-date-picker-title {
        min-width: 0;
        color: #0f172a;
        font-size: 13px;
        font-weight: 950;
        text-align: center;
    }

    .aa-date-picker-nav button,
    .aa-date-grid button {
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #334155;
        cursor: pointer;
        font: inherit;
        font-size: 12px;
        font-weight: 850;
    }

    .aa-date-picker-nav button {
        display: inline-grid;
        width: 32px;
        height: 32px;
        place-items: center;
    }

    .aa-date-picker-nav button:hover,
    .aa-date-grid button:hover {
        background: #f1f5f9;
    }

    .aa-date-picker-weekday {
        display: grid;
        height: 26px;
        place-items: center;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
    }

    .aa-date-grid button {
        height: 34px;
    }

    .aa-date-grid button.is-muted {
        color: #cbd5e1;
    }

    .aa-date-grid button.is-selected {
        background: #0f766e;
        color: #ffffff;
    }

    .aa-gallery-toolbar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .aa-gallery-list {
        display: grid;
        gap: 8px;
        max-height: 220px;
        overflow-y: auto;
    }

    .aa-gallery-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
        padding: 14px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
    }

    .aa-panel-loading {
        display: flex;
        align-items: center;
        gap: 9px;
        border: 1px solid #bae6fd;
        border-radius: 14px;
        background: #f0f9ff;
        padding: 11px 12px;
        color: #0369a1;
        font-size: 11px;
        font-weight: 950;
        line-height: 1.35;
    }

    .aa-panel-loading[hidden] {
        display: none !important;
    }

    .aa-panel-loading i {
        width: 15px;
        height: 15px;
        flex: 0 0 auto;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 999px;
        animation: aa-spin .8s linear infinite;
    }

    .aa-template-category-grid > .aa-panel-loading,
    .aa-editor-asset-grid > .aa-panel-loading,
    .aa-media-grid > .aa-panel-loading {
        grid-column: 1 / -1;
    }

    .aa-editor-asset-grid > .aa-gallery-empty {
        grid-column: 1 / -1;
        display: grid;
        min-height: 88px;
        place-items: center;
        border: 1px dashed rgba(0, 168, 138, .28);
        border-radius: 18px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .94), rgba(240, 253, 250, .58));
        color: #64748b;
        padding: 18px 16px;
        font-size: 12px;
        font-weight: 900;
        line-height: 1.45;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .74);
    }

    .aa-gallery-item-row {
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr) auto;
        align-items: center;
        gap: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        padding: 6px;
    }

    .aa-gallery-item-row img {
        width: 46px;
        height: 46px;
        border-radius: 9px;
        object-fit: cover;
        background: #e2e8f0;
    }

    .aa-gallery-item-row span {
        overflow: hidden;
        color: #334155;
        font-size: 11px;
        font-weight: 850;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-gallery-item-actions {
        display: flex;
        gap: 4px;
    }

    .aa-gallery-item-actions button {
        display: inline-grid;
        width: 26px;
        height: 26px;
        place-items: center;
        border: 0;
        border-radius: 8px;
        background: #f1f5f9;
        color: #334155;
        cursor: pointer;
        font-size: 11px;
    }

    .aa-gallery-item-actions button:hover:not(:disabled) {
        background: #ccfbf1;
        color: #0f766e;
    }

    .aa-gallery-item-actions button:disabled {
        cursor: not-allowed;
        opacity: .42;
    }

    .aa-range-field {
        gap: 8px;
    }

    .aa-range-field input[type="range"] {
        width: 100%;
        accent-color: #0f766e;
    }

    .aa-editor-template-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .aa-editor-asset-tools {
        display: grid;
        gap: 10px;
        margin: 10px 0 12px;
    }

    .aa-editor-asset-admin {
        display: grid;
        gap: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #ffffff;
        padding: 10px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .aa-editor-asset-admin-title {
        margin: 0;
        color: #111827;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-editor-asset-admin-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 8px;
    }

    .aa-editor-asset-admin select,
    .aa-editor-asset-admin input[type="file"] {
        min-height: 36px;
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #f8fafc;
        color: #0f172a;
        font: inherit;
        font-size: 12px;
        font-weight: 750;
        padding: 0 10px;
    }

    .aa-editor-asset-admin input[type="file"] {
        padding: 7px 10px;
    }

    .aa-editor-asset-premium-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        border: 1px solid rgba(200, 135, 45, .28);
        border-radius: 999px;
        background: rgba(255, 248, 234, .78);
        padding: 8px 11px;
        color: #92400e;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        user-select: none;
    }

    .aa-editor-asset-premium-check input {
        width: 14px;
        height: 14px;
        accent-color: #c8872d;
    }

    .aa-editor-asset-premium-check span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .aa-editor-asset-upload-btn {
        min-height: 38px;
        border: 0;
        border-radius: 12px;
        background: #111827;
        color: #ffffff;
        font: inherit;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
    }

    .aa-editor-asset-upload-btn:disabled {
        cursor: not-allowed;
        opacity: .55;
    }

    .aa-editor-asset-admin-note {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        line-height: 1.45;
    }

    .aa-editor-asset-upload-state {
        display: none;
        align-items: center;
        gap: 8px;
        border: 1px solid #bae6fd;
        border-radius: 12px;
        background: #f0f9ff;
        padding: 9px 10px;
        color: #0369a1;
        font-size: 11px;
        font-weight: 900;
        line-height: 1.35;
    }

    .aa-editor-asset-upload-state.is-visible {
        display: flex;
    }

    .aa-editor-asset-upload-state.is-error {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .aa-editor-asset-upload-state.is-success {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .aa-editor-asset-upload-state i {
        width: 14px;
        height: 14px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 999px;
        animation: aa-spin .8s linear infinite;
    }

    .aa-editor-asset-upload-state.is-error i,
    .aa-editor-asset-upload-state.is-success i {
        display: none;
    }

    .aa-editor-asset-discovery {
        display: grid;
        gap: 10px;
    }

    .aa-editor-asset-search-hero {
        display: grid;
        min-height: 76px;
        grid-template-columns: auto minmax(0, 1fr);
        align-items: start;
        gap: 10px;
        border: 1.5px solid rgba(0, 168, 138, .45);
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #f0fdfa);
        padding: 15px 14px;
        color: #00a88a;
        box-shadow: 0 14px 30px rgba(0, 168, 138, .08);
    }

    .aa-editor-asset-search-hero i {
        margin-top: 2px;
        font-size: 14px;
    }

    .aa-editor-asset-search-hero input {
        width: 100%;
        border: 0;
        outline: 0;
        background: transparent;
        color: #111827;
        font: inherit;
        font-size: 13px;
        font-weight: 750;
    }

    .aa-editor-asset-search-hero input::placeholder {
        color: #94a3b8;
        font-weight: 700;
    }

    .aa-editor-asset-search-btn {
        min-height: 42px;
        border: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, #00a88a, #0f766e);
        color: #ffffff;
        font: inherit;
        font-size: 13px;
        font-weight: 950;
        cursor: pointer;
        box-shadow: 0 14px 26px rgba(0, 168, 138, .22);
        transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
    }

    .aa-editor-asset-search-btn:hover {
        filter: brightness(1.04);
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(0, 168, 138, .28);
    }

    .aa-editor-asset-quick-wrap {
        position: relative;
        min-width: 0;
    }

    .aa-editor-asset-quick-chips {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 1px 34px 5px 1px;
        scroll-behavior: smooth;
        overscroll-behavior-x: contain;
        scrollbar-width: none;
    }

    .aa-editor-asset-quick-chips::-webkit-scrollbar {
        display: none;
    }

    .aa-editor-asset-quick-chips button {
        flex: 0 0 auto;
        min-height: 34px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #ffffff;
        color: #334155;
        padding: 0 13px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 8px 16px rgba(15, 23, 42, .04);
        transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
    }

    .aa-editor-asset-quick-chips button:hover {
        border-color: rgba(0, 168, 138, .42);
        background: #ecfdf5;
        color: #007f6e;
        transform: translateY(-1px);
    }

    .aa-editor-asset-quick-nav {
        position: absolute;
        top: 1px;
        bottom: 5px;
        z-index: 3;
        display: inline-grid;
        width: 28px;
        place-items: center;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(236, 253, 245, .92));
        color: #0f766e;
        font-size: 10px;
        cursor: pointer;
        opacity: 1;
        transform: translateX(0);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
        transition: opacity .18s ease, transform .18s ease, background .16s ease, color .16s ease;
    }

    .aa-editor-asset-quick-nav:hover {
        background: #00a88a;
        color: #ffffff;
    }

    .aa-editor-asset-quick-nav[data-aa-editor-asset-quick-scroll="-1"] {
        left: 0;
    }

    .aa-editor-asset-quick-nav[data-aa-editor-asset-quick-scroll="1"] {
        right: 0;
    }

    .aa-editor-asset-quick-nav:disabled {
        pointer-events: none;
        opacity: 0;
    }

    .aa-editor-asset-quick-nav[data-aa-editor-asset-quick-scroll="-1"]:disabled {
        transform: translateX(-8px);
    }

    .aa-editor-asset-quick-nav[data-aa-editor-asset-quick-scroll="1"]:disabled {
        transform: translateX(8px);
    }

    .aa-editor-asset-section-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 10px;
        margin-top: 2px;
    }

    .aa-editor-asset-section-head h3 {
        margin: 0;
        color: #111827;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-editor-asset-section-head span {
        color: #94a3b8;
        font-size: 10px;
        font-weight: 850;
    }

    .aa-editor-asset-landing-sections {
        display: grid;
        gap: 14px;
    }

    .aa-editor-asset-preview-section {
        display: grid;
        gap: 8px;
    }

    .aa-editor-asset-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .aa-editor-asset-preview-head strong {
        color: #111827;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-editor-asset-preview-head button {
        border: 0;
        background: transparent;
        color: #64748b;
        padding: 0;
        font: inherit;
        font-size: 10px;
        font-weight: 900;
        cursor: pointer;
    }

    .aa-editor-asset-preview-head button:hover {
        color: #00a88a;
    }

    .aa-editor-asset-preview-row {
        display: flex;
        gap: 9px;
        overflow-x: auto;
        padding: 2px 1px 7px;
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 168, 138, .34) transparent;
    }

    .aa-editor-asset-preview-row::-webkit-scrollbar {
        height: 4px;
    }

    .aa-editor-asset-preview-row::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(0, 168, 138, .32);
    }

    .aa-editor-asset-preview-card {
        -webkit-appearance: none;
        appearance: none;
        position: relative;
        flex: 0 0 72px;
        display: grid;
        width: 72px;
        height: 72px;
        place-items: center;
        border: 0;
        border-radius: 14px;
        background: transparent;
        color: #111827;
        padding: 0;
        cursor: pointer;
        box-shadow: none;
        transition: transform .16s ease, filter .16s ease;
    }

    .aa-editor-asset-preview-card:hover {
        filter: drop-shadow(0 12px 16px rgba(15, 23, 42, .12));
        transform: translateY(-2px);
    }

    .aa-editor-asset-preview-card:focus-visible {
        outline: 2px solid rgba(0, 168, 138, .55);
        outline-offset: 3px;
    }

    .aa-editor-asset-preview-card img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 10px;
        pointer-events: none;
    }

    .aa-editor-asset-preview-card .aa-editor-asset-badge.is-premium {
        left: 3px;
        top: 3px;
        bottom: auto;
    }

    .aa-editor-asset-preview-empty {
        border: 1px dashed #dbe3ef;
        border-radius: 16px;
        background: #f8fafc;
        color: #94a3b8;
        padding: 12px;
        font-size: 11px;
        font-weight: 850;
    }

    .aa-editor-asset-type-wrap {
        position: relative;
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr) 28px;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(203, 213, 225, .74);
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .94), rgba(248, 250, 252, .92));
        padding: 6px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .aa-editor-asset-type-wrap::before,
    .aa-editor-asset-type-wrap::after {
        content: "";
        position: absolute;
        top: 7px;
        bottom: 7px;
        z-index: 2;
        width: 22px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .16s ease;
    }

    .aa-editor-asset-type-wrap::before {
        left: 34px;
        background: linear-gradient(90deg, rgba(248, 250, 252, .98), rgba(248, 250, 252, 0));
    }

    .aa-editor-asset-type-wrap::after {
        right: 34px;
        background: linear-gradient(270deg, rgba(248, 250, 252, .98), rgba(248, 250, 252, 0));
    }

    .aa-editor-asset-type-wrap.can-scroll-left::before,
    .aa-editor-asset-type-wrap.can-scroll-right::after {
        opacity: 1;
    }

    .aa-editor-asset-type-chips {
        display: flex;
        gap: 7px;
        min-width: 0;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        padding: 1px 0 5px;
        scroll-behavior: smooth;
        scroll-snap-type: x proximity;
        scrollbar-color: rgba(0, 168, 138, .38) transparent;
        scrollbar-width: thin;
    }

    .aa-editor-asset-type-chips::-webkit-scrollbar {
        height: 4px;
    }

    .aa-editor-asset-type-chips::-webkit-scrollbar-track {
        background: transparent;
    }

    .aa-editor-asset-type-chips::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(0, 168, 138, .34);
    }

    .aa-editor-asset-type-nav {
        position: relative;
        z-index: 3;
        display: inline-grid;
        width: 28px;
        height: 32px;
        place-items: center;
        border: 0;
        border-radius: 12px;
        background: rgba(15, 23, 42, .05);
        color: #0f766e;
        font-size: 10px;
        cursor: pointer;
        transition: background .16s ease, color .16s ease, opacity .16s ease, transform .16s ease;
    }

    .aa-editor-asset-type-nav:hover {
        background: #00a88a;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .aa-editor-asset-type-nav:disabled {
        cursor: default;
        opacity: .34;
        transform: none;
    }

    .aa-editor-asset-type-chip {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 34px;
        border: 1px solid rgba(203, 213, 225, .82);
        border-radius: 14px;
        background: rgba(255, 255, 255, .82);
        color: #334155;
        padding: 0 12px;
        font: inherit;
        font-size: 11px;
        font-weight: 950;
        cursor: pointer;
        scroll-snap-align: start;
        white-space: nowrap;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .72);
        transition: background .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .aa-editor-asset-type-chip i {
        color: #00a88a;
        font-size: 11px;
        transition: color .16s ease;
    }

    .aa-editor-asset-type-chip:hover,
    .aa-editor-asset-type-chip.is-active {
        border-color: rgba(0, 168, 138, .54);
        background: linear-gradient(135deg, #00a88a, #0f766e);
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(0, 168, 138, .22);
        transform: translateY(-1px);
    }

    .aa-editor-asset-type-chip:hover i,
    .aa-editor-asset-type-chip.is-active i {
        color: #ffffff;
    }

    .aa-canvas-size-options {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .aa-canvas-size-btn {
        min-height: 58px;
        flex-direction: column;
        gap: 2px;
        padding: 8px 6px;
        line-height: 1.15;
    }

    .aa-canvas-size-btn strong {
        display: block;
        color: inherit;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-canvas-size-btn span {
        display: block;
        color: #64748b;
        font-size: 10px;
        font-weight: 850;
    }

    .aa-canvas-bg-actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
    }

    .aa-canvas-bg-status {
        min-height: 16px;
        margin: 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 850;
        line-height: 1.35;
    }

    .aa-canvas-bg-status.is-error {
        color: #be123c;
    }

    .aa-canvas-bg-status.is-success {
        color: #15803d;
    }

    .aa-editor-asset-grid {
        position: relative;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        min-height: 120px;
        overflow: visible;
    }

    .aa-editor-asset-card {
        position: relative;
        display: grid;
        z-index: 0;
        isolation: isolate;
        min-height: 0;
        aspect-ratio: 1;
        overflow: visible;
        border: 0;
        border-radius: 12px;
        background: transparent;
        padding: 3px;
        color: #0f172a;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-editor-asset-card:hover {
        box-shadow: none;
        outline: 1px solid rgba(147, 51, 234, .24);
        outline-offset: 2px;
        transform: translateY(-1px);
    }

    .aa-editor-asset-card.is-menu-open {
        z-index: 120;
    }

    .aa-editor-asset-card.is-menu-open,
    .aa-editor-asset-card.is-menu-open:hover {
        outline: 1px solid rgba(0, 168, 138, .28);
        outline-offset: 2px;
        transform: none;
    }

    .aa-editor-asset-card img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: inherit;
        background: transparent;
    }

    .aa-editor-asset-pick {
        display: grid;
        width: 100%;
        height: 100%;
        border: 0;
        border-radius: inherit;
        background: transparent;
        padding: 0;
        cursor: pointer;
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .aa-photo-frame-preset-card,
    .aa-photo-frame-preset-pick {
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .92)),
            #ffffff;
        border: 1px solid rgba(203, 213, 225, .78);
        overflow: hidden;
        color: #0f172a;
    }

    .aa-photo-frame-preset-card {
        grid-template-rows: minmax(0, 1fr) auto;
        gap: 4px;
        height: 82px;
        padding: 7px 6px 6px;
        border-radius: 14px;
    }

    .aa-photo-frame-preset-pick {
        display: grid;
        grid-template-rows: minmax(0, 1fr) auto;
        gap: 6px;
        padding: 8px 7px 7px;
    }

    .aa-editor-asset-grid.is-photo-frame-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .aa-editor-asset-grid.is-photo-frame-grid .aa-editor-asset-card {
        aspect-ratio: auto;
        min-height: 78px;
        padding: 0;
    }

    .aa-editor-asset-grid.is-photo-frame-grid .aa-photo-frame-preset-pick {
        min-height: 78px;
        border-radius: 12px;
        padding: 7px 6px 6px;
    }

    .aa-photo-frame-preset-preview {
        display: block;
        align-self: center;
        justify-self: center;
        width: min(46px, 74%);
        aspect-ratio: 1;
        border: 1px solid rgba(15, 118, 110, .28);
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .28), rgba(14, 165, 233, .16) 45%, rgba(244, 114, 182, .18)),
            repeating-linear-gradient(45deg, rgba(15, 23, 42, .08) 0 4px, rgba(255, 255, 255, .72) 4px 8px),
            #e2f5f1;
        box-shadow:
            inset 0 0 0 2px rgba(255, 255, 255, .7),
            0 8px 16px rgba(15, 118, 110, .12);
    }

    .aa-editor-asset-grid.is-photo-frame-grid .aa-photo-frame-preset-preview {
        width: 42px;
        max-width: 76%;
    }

    .aa-photo-frame-preset-card:hover,
    .aa-photo-frame-preset-pick:hover {
        border-color: rgba(20, 184, 166, .5);
        background: #ecfdf5;
    }

    .aa-photo-frame-preset-name {
        display: block;
        min-width: 0;
        color: #334155;
        font-size: 10px;
        font-weight: 900;
        line-height: 1.1;
        overflow: hidden;
        text-align: center;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-editor-asset-grid.is-photo-frame-grid .aa-photo-frame-preset-name {
        font-size: 9.5px;
        line-height: 1.12;
        white-space: normal;
    }

    .aa-photo-frame-preset-preview.is-rounded { border-radius: 12px; }
    .aa-photo-frame-preset-preview.is-circle { border-radius: 999px; }
    .aa-photo-frame-preset-preview.is-arch { border-radius: 999px 999px 10px 10px; }
    .aa-photo-frame-preset-preview.is-heart {
        clip-path: polygon(50% 88%, 17% 58%, 8% 36%, 15% 17%, 31% 10%, 44% 18%, 50% 29%, 56% 18%, 69% 10%, 85% 17%, 92% 36%, 83% 58%);
    }
    .aa-photo-frame-preset-preview.is-diamond {
        transform: rotate(45deg) scale(.78);
    }
    .aa-photo-frame-preset-preview.is-blob {
        border-radius: 42% 58% 52% 48% / 54% 42% 58% 46%;
    }
    .aa-photo-frame-preset-preview.is-oval { border-radius: 999px / 72%; }
    .aa-photo-frame-preset-preview.is-shield { clip-path: polygon(50% 0, 88% 16%, 82% 58%, 50% 100%, 18% 58%, 12% 16%); }
    .aa-photo-frame-preset-preview.is-hexagon { clip-path: polygon(25% 0, 75% 0, 100% 50%, 75% 100%, 25% 100%, 0 50%); }
    .aa-photo-frame-preset-preview.is-petal { clip-path: polygon(50% 0, 88% 8%, 100% 34%, 72% 50%, 100% 66%, 88% 92%, 50% 100%, 12% 92%, 0 66%, 28% 50%, 0 34%, 12% 8%); }
    .aa-photo-frame-preset-preview.is-wave { clip-path: polygon(0 16%, 18% 4%, 42% 18%, 66% 5%, 100% 18%, 100% 84%, 78% 96%, 52% 82%, 25% 96%, 0 82%); }
    .aa-photo-frame-preset-preview.is-tag { clip-path: polygon(0 0, 72% 0, 100% 50%, 72% 100%, 0 100%); }
    .aa-photo-frame-preset-preview.is-bookmark { clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 72%, 0 100%); }
    .aa-photo-frame-preset-preview.is-scallop { clip-path: polygon(30% 0, 50% 10%, 70% 0, 86% 14%, 100% 30%, 90% 50%, 100% 70%, 86% 86%, 70% 100%, 50% 90%, 30% 100%, 14% 86%, 0 70%, 10% 50%, 0 30%, 14% 14%); }

    .aa-editor-asset-delete {
        position: absolute;
        top: 6px;
        right: 6px;
        z-index: 3;
        display: inline-grid;
        width: 24px;
        height: 24px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .9);
        border-radius: 999px;
        background: rgba(190, 18, 60, .94);
        color: #ffffff;
        cursor: pointer;
        font-size: 10px;
        box-shadow: 0 10px 20px rgba(15, 23, 42, .18);
        transition: transform .14s ease, background .14s ease;
    }

    .aa-editor-asset-delete:hover {
        background: #be123c;
        transform: scale(1.04);
    }

    .aa-editor-asset-menu-toggle {
        position: absolute;
        top: 6px;
        left: 6px;
        z-index: 4;
        display: inline-grid;
        width: 24px;
        height: 24px;
        place-items: center;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 999px;
        background: rgba(255, 255, 255, .92);
        color: #334155;
        cursor: pointer;
        font-size: 10px;
        box-shadow: 0 10px 20px rgba(15, 23, 42, .12);
        transition: transform .14s ease, border-color .14s ease, color .14s ease;
    }

    .aa-editor-asset-menu-toggle:hover {
        border-color: rgba(0, 168, 138, .42);
        color: #008f79;
        transform: scale(1.04);
    }

    .aa-editor-asset-menu-panel {
        position: absolute;
        top: 34px;
        left: 6px;
        width: 224px;
        max-width: calc(100vw - 40px);
        z-index: 140;
        display: grid;
        gap: 8px;
        border: 1px solid rgba(203, 213, 225, .9);
        border-radius: 14px;
        background: rgba(255, 255, 255, .96);
        padding: 10px;
        box-shadow: 0 18px 34px rgba(15, 23, 42, .16);
        cursor: default;
    }

    .aa-editor-asset-card.is-menu-open .aa-editor-asset-menu-toggle {
        z-index: 145;
    }

    .aa-editor-asset-grid:has(.aa-editor-asset-card.is-menu-open) .aa-editor-asset-card:not(.is-menu-open) .aa-editor-asset-menu-toggle,
    .aa-editor-asset-grid:has(.aa-editor-asset-card.is-menu-open) .aa-editor-asset-card:not(.is-menu-open) .aa-editor-asset-delete {
        opacity: .28;
        pointer-events: none;
    }

    .aa-editor-asset-card:nth-child(4n) .aa-editor-asset-menu-panel,
    .aa-editor-asset-card:nth-child(4n - 1) .aa-editor-asset-menu-panel {
        right: 6px;
        left: auto;
    }

    .aa-editor-asset-menu-panel label {
        display: grid;
        gap: 5px;
        color: #475569;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .aa-editor-asset-menu-panel select {
        min-width: 0;
        width: 100%;
        border: 1px solid rgba(203, 213, 225, .9);
        border-radius: 10px;
        background: #ffffff;
        padding: 7px 8px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 800;
        outline: none;
    }

    .aa-editor-asset-menu-panel select:focus {
        border-color: rgba(0, 168, 138, .56);
        box-shadow: 0 0 0 3px rgba(0, 168, 138, .12);
    }

    .aa-editor-asset-menu-check {
        display: inline-flex !important;
        grid-template-columns: none;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(200, 135, 45, .24);
        border-radius: 12px;
        background: rgba(255, 248, 234, .82);
        padding: 8px 9px;
        color: #92400e !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        cursor: pointer;
    }

    .aa-editor-asset-menu-check input {
        width: 14px;
        height: 14px;
        accent-color: #c8872d;
    }

    .aa-editor-asset-menu-check span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-editor-asset-menu-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }

    .aa-editor-asset-menu-actions button {
        border: 0;
        border-radius: 10px;
        padding: 7px 8px;
        cursor: pointer;
        font-size: 11px;
        font-weight: 900;
    }

    .aa-editor-asset-menu-actions button:first-child {
        background: #f1f5f9;
        color: #475569;
    }

    .aa-editor-asset-menu-actions button:last-child {
        background: linear-gradient(135deg, #00a88a, #0f766e);
        color: #ffffff;
    }

    .aa-editor-asset-badge {
        position: absolute;
        left: 6px;
        bottom: 6px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        max-width: calc(100% - 12px);
        border-radius: 999px;
        background: rgba(15, 23, 42, .78);
        color: #ffffff;
        padding: 4px 7px;
        font-size: 9px;
        font-weight: 950;
        line-height: 1;
        pointer-events: none;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .18);
    }

    .aa-editor-asset-badge.is-premium {
        justify-content: center;
        width: 18px;
        height: 18px;
        max-width: none;
        border: 0;
        background: transparent;
        padding: 0;
        font-size: 13px;
        box-shadow: none;
    }

    .aa-editor-asset-badge.is-premium.is-locked {
        color: #facc15;
    }

    .aa-editor-asset-badge.is-premium.is-unlocked {
        color: rgba(100, 116, 139, .62);
    }

    .aa-editor-asset-badge.is-premium i {
        line-height: 1;
    }

    .aa-editor-asset-more {
        width: 100%;
        min-height: 38px;
        border: 1px solid #0f766e;
        border-radius: 13px;
        background: #0f766e;
        color: #ffffff;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(15, 118, 110, .22);
        transition: background .16s ease, border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .aa-editor-asset-more:hover {
        border-color: #0d9488;
        background: #0d9488;
        box-shadow: 0 12px 26px rgba(13, 148, 136, .28);
        transform: translateY(-1px);
    }

    .aa-template-drawer-tools {
        display: grid;
        gap: 10px;
        margin-bottom: 12px;
    }

    .aa-template-search-wrap {
        position: relative;
    }

    .aa-template-search-wrap i {
        position: absolute;
        top: 50%;
        left: 13px;
        color: #64748b;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .aa-template-search-input {
        width: 100%;
        min-height: 40px;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        background: #ffffff;
        padding: 0 12px 0 36px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 800;
        outline: none;
    }

    .aa-template-search-input:focus {
        border-color: #14b8a6;
        box-shadow: 0 0 0 3px rgba(20, 184, 166, .12);
    }

    .aa-template-chip-row {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 1px 34px 5px 1px;
        scroll-behavior: smooth;
        overscroll-behavior-x: contain;
        scrollbar-width: none;
    }

    .aa-template-chip-row::-webkit-scrollbar {
        display: none;
    }

    .aa-template-filter-chip {
        flex: 0 0 auto;
        min-height: 34px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #ffffff;
        color: #334155;
        padding: 0 13px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
    }

    .aa-template-filter-chip:hover,
    .aa-template-filter-chip.is-active {
        border-color: rgba(0, 168, 138, .42);
        background: #ecfdf5;
        color: #007f6e;
        transform: translateY(-1px);
    }

    .aa-template-discovery-tools {
        display: grid;
        gap: 10px;
    }

    .aa-template-search-hero .aa-template-search-input {
        min-height: auto;
        border: 0;
        border-radius: 0;
        background: transparent;
        padding: 0;
        box-shadow: none;
    }

    .aa-template-search-hero .aa-template-search-input:focus {
        border-color: transparent;
        box-shadow: none;
    }

    .aa-template-chip-wrap {
        position: relative;
        min-width: 0;
    }

    .aa-template-chip-nav {
        position: absolute;
        top: 1px;
        bottom: 5px;
        z-index: 3;
        display: inline-grid;
        width: 28px;
        place-items: center;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(236, 253, 245, .92));
        color: #0f766e;
        font-size: 10px;
        cursor: pointer;
        opacity: 1;
        transform: translateX(0);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
        transition: opacity .18s ease, transform .18s ease, background .16s ease, color .16s ease;
    }

    .aa-template-chip-nav:hover {
        background: #00a88a;
        color: #ffffff;
    }

    .aa-template-chip-nav[data-aa-template-chip-scroll="-1"] {
        left: 0;
    }

    .aa-template-chip-nav[data-aa-template-chip-scroll="1"] {
        right: 0;
    }

    .aa-template-chip-nav:disabled {
        pointer-events: none;
        opacity: 0;
    }

    .aa-template-chip-nav[data-aa-template-chip-scroll="-1"]:disabled {
        transform: translateX(-8px);
    }

    .aa-template-chip-nav[data-aa-template-chip-scroll="1"]:disabled {
        transform: translateX(8px);
    }

    .aa-editor-template-card {
        display: grid;
        gap: 0;
        border: 0;
        border-radius: 12px;
        background: transparent;
        padding: 0;
        overflow: visible;
        color: #334155;
        text-align: left;
        cursor: pointer;
        transition: transform .16s ease, filter .16s ease;
    }

    .aa-editor-template-card:hover {
        filter: saturate(1.04);
        transform: translateY(-1px);
    }

    .aa-editor-template-thumb {
        position: relative;
        display: block;
        aspect-ratio: 9 / 14;
        overflow: hidden;
        border-radius: 2px;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
    }

    .aa-editor-template-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .aa-editor-template-tier {
        position: absolute;
        right: 6px;
        bottom: 6px;
        z-index: 2;
        display: inline-grid;
        width: 24px;
        height: 24px;
        place-items: center;
    }

    .aa-editor-template-tier .aa-premium-crown {
        width: 13px;
        height: 13px;
    }

    .aa-editor-template-tier .aa-premium-crown svg {
        width: 13px;
        height: 13px;
    }

    .aa-editor-template-meta {
        display: none;
    }

    .aa-editor-template-card strong {
        display: block;
        overflow: hidden;
        font-size: 11px;
        font-weight: 950;
        line-height: 1.25;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-editor-template-card span {
        color: #0f766e;
        font-size: 10px;
        font-weight: 900;
    }

    .aa-template-card-more {
        position: absolute;
        top: 6px;
        right: 6px;
        z-index: 3;
        display: inline-grid;
        width: 28px;
        height: 28px;
        place-items: center;
        border-radius: 10px;
        background: rgba(15, 23, 42, .54);
        color: #ffffff !important;
        font-size: 12px !important;
        opacity: 0;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .18);
        transition: opacity .16s ease, transform .16s ease, background .16s ease;
    }

    .aa-editor-template-card:hover .aa-template-card-more,
    .aa-template-card-more:hover {
        opacity: 1;
    }

    .aa-template-card-more:hover {
        background: rgba(15, 23, 42, .78);
        transform: translateY(-1px);
    }

    .aa-template-detail-popover {
        position: absolute;
        z-index: 30;
        display: grid;
        width: 180px;
        gap: 7px;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 16px;
        background: rgba(255, 255, 255, .98);
        padding: 12px;
        color: #0f172a;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
    }

    .aa-template-detail-popover[hidden] {
        display: none !important;
    }

    .aa-template-detail-popover strong {
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
        line-height: 1.25;
    }

    .aa-template-detail-popover span,
    .aa-template-detail-popover em {
        color: #64748b;
        font-size: 11px;
        font-style: normal;
        font-weight: 850;
        line-height: 1.25;
    }

    .aa-template-detail-popover em {
        color: #007f6e;
    }

    .aa-template-detail-popover button {
        min-height: 32px;
        border: 0;
        border-radius: 10px;
        background: #0f766e;
        color: #ffffff;
        font: inherit;
        font-size: 11px;
        font-weight: 950;
        cursor: pointer;
    }

    .aa-template-empty-state {
        display: none;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 18px 12px;
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
        text-align: center;
    }

    .aa-template-empty-state.is-visible {
        display: block;
    }

    .aa-template-preview-modal .aa-modal-card {
        width: min(96vw, 1160px);
        height: min(92vh, 820px);
        padding: 0;
        overflow: hidden;
    }

    .aa-template-preview-head,
    .aa-exit-guard-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .aa-template-preview-head {
        min-height: 58px;
        border-bottom: 1px solid #e2e8f0;
        padding: 0 14px;
    }

    .aa-template-preview-frame {
        display: block;
        width: 100%;
        height: calc(100% - 58px);
        border: 0;
        background: #f8fafc;
    }

    .aa-gallery-radius-slider {
        height: 34px;
        cursor: ew-resize;
        -webkit-appearance: none;
        appearance: none;
        background: transparent;
    }

    .aa-gallery-radius-slider::-webkit-slider-runnable-track {
        height: 8px;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .aa-gallery-radius-slider::-webkit-slider-thumb {
        width: 22px;
        height: 22px;
        margin-top: -7px;
        border: 3px solid #ffffff;
        border-radius: 999px;
        background: #0f766e;
        box-shadow: 0 6px 14px rgba(15, 23, 42, .18);
        -webkit-appearance: none;
        appearance: none;
    }

    .aa-gallery-radius-slider::-moz-range-track {
        height: 8px;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .aa-gallery-radius-slider::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border: 3px solid #ffffff;
        border-radius: 999px;
        background: #0f766e;
        box-shadow: 0 6px 14px rgba(15, 23, 42, .18);
    }

    @keyframes aaSpin {
        to {
            transform: rotate(360deg);
        }
    }

    .aa-tool-btn,
    .aa-action-btn,
    .aa-panel-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        transition: .16s ease;
    }

    .aa-tool-btn {
        min-height: 74px;
        flex-direction: column;
        padding: 10px 8px;
    }

    .aa-tool-btn .aa-lucide-icon,
    .aa-tool-btn svg.aa-lucide-icon,
    .aa-panel-btn .aa-lucide-icon,
    .aa-panel-btn svg.aa-lucide-icon {
        width: 21px;
        height: 21px;
        stroke-width: 1.9;
    }

    .aa-action-btn {
        min-height: 38px;
        border-color: rgba(255, 255, 255, .16);
        background: rgba(255, 255, 255, .08);
        color: #e2e8f0;
        padding: 0 12px;
    }

    .aa-panel-btn {
        min-height: 38px;
        padding: 0 12px;
    }

    .aa-panel-btn:disabled,
    .aa-panel-btn.is-disabled {
        cursor: not-allowed;
        opacity: .55;
        pointer-events: none;
        filter: grayscale(.15);
        box-shadow: none !important;
    }

    .aa-panel-btn.is-premium-locked {
        border-color: rgba(217, 119, 6, .24);
        background: linear-gradient(135deg, #fff7ed, #faf5ff);
        color: #7c2d12;
        box-shadow: 0 10px 22px rgba(146, 64, 14, .10);
    }

    .aa-panel-btn.is-premium-locked:hover {
        border-color: rgba(124, 58, 237, .32);
        background: linear-gradient(135deg, #fffbeb, #f3e8ff);
        color: #5b21b6;
    }

    .aa-panel-btn.is-premium-locked i {
        margin-right: 6px;
        color: #d97706;
    }

    .aa-animation-btn {
        min-height: 58px;
        flex-direction: column;
        gap: 4px;
        padding: 8px 6px;
        font-size: 11px;
    }

    #aaAnimationPanel {
        gap: 10px;
    }

    #aaAnimationPanel .aa-animation-section-title {
        margin: 0 0 8px;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #64748b;
    }

    #aaAnimationPanel .aa-animation-timing-card {
        margin: 2px 0 6px;
        border: 1px solid #dbe8ef;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        padding: 10px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .75);
    }

    #aaAnimationPanel .aa-animation-timing-controls {
        display: grid;
        gap: 9px;
    }

    #aaAnimationPanel .aa-animation-timing-field {
        display: grid;
        gap: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 850;
    }

    #aaAnimationPanel .aa-animation-timing-field span {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    #aaAnimationPanel .aa-animation-timing-field output {
        color: #0f766e;
        font-weight: 900;
    }

    #aaAnimationPanel .aa-animation-btn {
        min-height: 44px;
        border-radius: 12px;
        gap: 3px;
        padding: 6px 5px;
        font-size: 10px;
        line-height: 1.1;
    }

    #aaAnimationPanel .aa-animation-btn i {
        font-size: 13px;
    }

    .aa-animation-btn.is-active {
        border-color: #146cb8;
        background: #ccfbf1;
        color: #0f766e;
        box-shadow: inset 0 0 0 1px rgba(20, 184, 166, .24);
    }

    .aa-animation-btn.is-disabled,
    .aa-animation-option.is-disabled,
    .aa-context-tool.is-disabled,
    .aa-text-context-tool.is-disabled {
        opacity: .45;
        cursor: not-allowed;
        filter: grayscale(.2);
        box-shadow: none !important;
    }

    .aa-tool-btn:hover,
    .aa-panel-btn:not(:disabled):not(.is-disabled):hover {
        border-color: #146cb8;
        background: #ecfdf5;
        color: #0f766e;
        transform: translateY(-1px);
    }

    .aa-action-btn:hover {
        border-color: #5eead4;
        background: rgba(20, 184, 166, .2);
        color: #ffffff;
    }

    .aa-panel-btn.is-active {
        border-color: #146cb8;
        background: #ccfbf1;
        color: #0f766e;
        box-shadow: inset 0 0 0 1px rgba(20, 184, 166, .24);
    }

    .aa-primary {
        border-color: #2dd4bf;
        background: #146cb8;
        color: #042f2e;
    }

    .aa-primary:hover {
        background: #5eead4;
        color: #042f2e;
    }

    .aa-publish {
        border-color: #facc15;
        background: #facc15;
        color: #422006;
    }

    .aa-field {
        width: 100%;
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
        padding: 0 11px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 650;
    }

    .aa-field[type="color"] {
        height: 40px;
        padding: 4px;
    }

    .aa-field[type="file"],
    .aa-editor-asset-admin input[type="file"] {
        min-height: 46px;
        cursor: pointer;
        border-style: dashed;
        border-color: #cbd5e1;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        color: #475569;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.25;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .aa-field[type="file"]::file-selector-button,
    .aa-editor-asset-admin input[type="file"]::file-selector-button {
        min-height: 32px;
        margin-right: 10px;
        border: 0;
        border-radius: 11px;
        background: #0f766e;
        color: #ffffff;
        padding: 0 13px;
        font: inherit;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
        transition: background .16s ease, transform .16s ease;
    }

    .aa-field[type="file"]::-webkit-file-upload-button,
    .aa-editor-asset-admin input[type="file"]::-webkit-file-upload-button {
        min-height: 32px;
        margin-right: 10px;
        border: 0;
        border-radius: 11px;
        background: #0f766e;
        color: #ffffff;
        padding: 0 13px;
        font: inherit;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
        transition: background .16s ease, transform .16s ease;
    }

    .aa-field[type="file"]:hover,
    .aa-editor-asset-admin input[type="file"]:hover {
        border-color: #14b8a6;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 118, 110, .1);
    }

    .aa-field[type="file"]:hover::file-selector-button,
    .aa-field[type="file"]:hover::-webkit-file-upload-button,
    .aa-editor-asset-admin input[type="file"]:hover::file-selector-button,
    .aa-editor-asset-admin input[type="file"]:hover::-webkit-file-upload-button {
        background: #115e59;
        transform: translateY(-1px);
    }

    .aa-field[type="file"]:focus-visible,
    .aa-editor-asset-admin input[type="file"]:focus-visible {
        border-color: #0f766e;
        box-shadow: 0 0 0 4px rgba(20, 184, 166, .16);
    }

    .aa-panel-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        padding: 14px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
    }

    .aa-panel-title {
        margin: 0 0 10px;
        color: #334155;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .aa-leftbar.is-acara-ai-drawer [data-aa-left-panel="import-reference"] {
        height: 100%;
        min-height: 0;
    }

    .aa-panel-card.aa-acara-ai-card {
        height: calc(100vh - 130px);
        min-height: 0;
        min-width: 0;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
        position: relative;
        padding: 16px;
        border-radius: 16px;
        background: #ffffff;
    }

    .aa-acara-ai-shell {
        display: flex;
        height: 100%;
        min-height: 0;
        min-width: 0;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        flex-direction: column;
        gap: 12px;
    }

    .aa-acara-ai-new-chat {
        position: absolute;
        top: 14px;
        right: 14px;
        z-index: 2;
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #94a3b8;
        font-size: 18px;
        cursor: pointer;
    }

    .aa-acara-ai-new-chat:hover {
        background: #f0fdfa;
        color: #0f766e;
    }

    .aa-acara-ai-page-label {
        min-height: 28px;
        max-width: calc(100% - 46px);
        border: 1px solid #ccfbf1;
        border-radius: 999px;
        background: linear-gradient(135deg, #f0fdfa 0%, #eff6ff 100%);
        color: #0f766e;
        padding: 7px 12px;
        font-size: 11px;
        font-weight: 900;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .aa-acara-ai-hero {
        display: flex;
        flex: 1 1 auto;
        min-height: 0;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
        padding: clamp(18px, 6vh, 58px) 20px;
        scrollbar-width: thin;
        scrollbar-color: #99f6e4 transparent;
    }

    .aa-acara-ai-hero h2 {
        max-width: 280px;
        margin: 0;
        background: linear-gradient(105deg, #009f8f 0%, #0ea5e9 52%, #2563eb 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        font-size: 31px;
        font-weight: 800;
        line-height: 1.28;
        letter-spacing: 0;
    }

    .aa-acara-ai-status {
        display: block;
        padding-top: 64px;
    }

    .aa-acara-ai-status-card {
        width: 100%;
        border: 1px solid #c8f4ea;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #f0fdfa);
        padding: 14px;
        color: #334155;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.5;
    }

    .aa-acara-ai-message {
        position: relative;
        display: flex;
        max-width: 78%;
        gap: 9px;
        margin: 0 0 22px;
        border: 0;
        border-radius: 22px;
        background: #ffffff;
        padding: 15px 17px;
        color: #334155;
        font-size: 14px;
        font-weight: 520;
        line-height: 1.55;
        box-shadow: none;
    }

    .aa-acara-ai-message p {
        margin: 0;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .aa-acara-ai-message i {
        margin-top: 3px;
        color: #0f766e;
        font-size: 13px;
    }

    .aa-acara-ai-message.is-user {
        margin-left: auto;
        background: linear-gradient(135deg, #dffaf5 0%, #e7f4ff 48%, #eee7ff 100%);
        color: #0f172a;
        padding-bottom: 36px;
    }

    .aa-acara-ai-message.is-assistant {
        margin-right: auto;
    }

    .aa-acara-ai-copy-btn {
        position: absolute;
        right: 12px;
        bottom: 9px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 22px;
        border: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, .72);
        color: #0f766e;
        padding: 3px 8px;
        font-size: 10px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 1px 0 rgba(15, 23, 42, .05);
    }

    .aa-acara-ai-copy-btn:hover {
        background: #ffffff;
        color: #115e59;
    }

    .aa-acara-ai-copy-btn i {
        margin: 0;
        color: currentColor;
        font-size: 10px;
    }

    .aa-acara-ai-message.is-saving {
        max-width: 100%;
        display: block;
        margin-left: 0;
        background: transparent;
        padding: 0;
        color: #0f766e;
    }

    .aa-acara-ai-thinking-card {
        display: flex;
        max-height: 45px;
        align-items: center;
        gap: 12px;
        /* border: 1px solid #c8f4ea; */
        border-radius: 24px;
        /* background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); */
        padding: 16px;
        text-align: left;
        /* box-shadow: 0 18px 42px rgba(15, 118, 110, .09); */
    }

    .aa-acara-ai-thinking-card img {
        width: 52px;
        height: 52px;
        flex: 0 0 auto;
        object-fit: contain;
        filter: drop-shadow(0 8px 10px rgba(15, 23, 42, .12));
        animation: aaAcaraAiMascotFloat 1.8s ease-in-out infinite;
    }

    .aa-acara-ai-thinking-copy {
        min-width: 0;
        flex: 1 1 auto;
        font-size: 14px;
    }

    .aa-acara-ai-thinking-card strong {
        position: relative;
        display: inline-block;
        margin: 0;
        overflow: hidden;
        background: linear-gradient(105deg, #0f766e 0%, #0ea5e9 52%, #2563eb 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        font-size: 14px;
        font-weight: 900;
        line-height: 1.35;
    }

    .aa-acara-ai-message.is-progress {
        max-width: min(92%, 360px);
        margin-top: -4px;
        border: 1px solid #d8f3ed;
        background: #f0fdfa;
        color: #0f766e;
        font-size: 12px;
        font-weight: 850;
    }

    .aa-acara-ai-message.is-progress i {
        display: inline-flex;
        flex: 0 0 14px;
        align-items: center;
        justify-content: center;
        width: 14px;
        height: 14px;
        color: #14b8a6;
        font-size: 10px;
        line-height: 1;
    }

    .aa-acara-ai-thinking-card strong::after {
        content: "";
        position: absolute;
        inset: -3px 0;
        width: 38%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .95), transparent);
        mix-blend-mode: screen;
        transform: translateX(-140%) skewX(-16deg);
        animation: aaAcaraAiTextShine 1.35s ease-in-out infinite;
    }

    @keyframes aaAcaraAiMascotFloat {
        0%, 100% {
            transform: translateY(0) rotate(-1deg);
        }
        50% {
            transform: translateY(-5px) rotate(1deg);
        }
    }

    @keyframes aaAcaraAiTextShine {
        100% {
            transform: translateX(300%) skewX(-16deg);
        }
    }

    .aa-acara-ai-message.is-error {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .aa-acara-ai-message.is-success {
        background: #ecfdf5;
        color: #047857;
    }

    .aa-acara-ai-presets {
        flex: 0 0 auto;
        display: grid;
        min-width: 0;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        justify-items: start;
        gap: 10px;
    }

    .aa-acara-ai-preset {
        display: inline-flex;
        box-sizing: border-box;
        min-width: 0;
        min-height: 40px;
        max-width: 100%;
        align-items: center;
        gap: 10px;
        border: 1px solid #b9eee2;
        border-radius: 999px;
        background: #ffffff;
        padding: 8px 16px;
        color: #2f3441;
        font-size: clamp(10px, 1.6vw, 10px);
        font-weight: 600;
        line-height: 1.25;
        white-space: normal;
        box-shadow: 0 9px 24px rgba(15, 118, 110, .10), 0 0 18px rgba(14, 165, 233, .10);
        cursor: pointer;
    }

    .aa-acara-ai-preset span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .aa-acara-ai-preset:hover {
        border-color: #0f766e;
        color: #0f766e;
        transform: translateY(-1px);
    }

    .aa-acara-ai-preset i {
        width: 18px;
        color: #0f766e;
        font-size: clamp(16px, 1.6vw, 16px);
        text-align: center;
    }

    .aa-acara-ai-attachment {
        flex: 0 0 auto;
        min-width: 0;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow: hidden;
        border: 1px solid #b9eee2;
        border-radius: 18px;
        background: #ffffff;
    }

    .aa-acara-ai-attachment img {
        width: 100%;
        max-height: 150px;
        object-fit: contain;
        background: #f8fafc;
    }

    .aa-acara-ai-attachment-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-top: 1px solid #dff8f3;
        padding: 9px 12px;
    }

    .aa-acara-ai-attachment-meta p {
        min-width: 0;
        margin: 0;
        overflow: hidden;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-acara-ai-attachment-meta button {
        display: grid;
        width: 26px;
        height: 26px;
        flex: 0 0 auto;
        place-items: center;
        border: 0;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155;
        cursor: pointer;
    }

    .aa-acara-ai-composer {
        flex: 0 0 auto;
        display: grid;
        min-width: 0;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        min-height: 132px;
        border: 1.5px solid #14b8a6;
        border-radius: 18px;
        background: #ffffff;
        padding: 16px;
        box-shadow: 0 10px 26px rgba(15, 118, 110, .08);
    }

    .aa-acara-ai-composer:focus-within {
        box-shadow: 0 0 0 4px rgba(20, 184, 166, .14);
    }

    .aa-acara-ai-disclaimer {
        margin: -2px 2px 0;
        color: #64748b;
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.45;
    }

    .aa-acara-ai-composer textarea {
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        min-height: 58px;
        resize: none;
        border: 0;
        outline: 0;
        background: transparent;
        color: #20232d;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.55;
    }

    .aa-acara-ai-composer textarea::placeholder {
        color: #8b8f9a;
    }

    .aa-acara-ai-composer-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .aa-acara-ai-icon-btn,
    .aa-acara-ai-send-btn {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 0;
        border-radius: 999px;
        cursor: pointer;
    }

    .aa-acara-ai-icon-btn {
        background: transparent;
        color: #2f3441;
        font-size: 18px;
    }

    .aa-acara-ai-send-btn {
        border: 1px solid #d7f3ee;
        background: #ffffff;
        color: #0f172a;
        font-size: 14px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
    }

    .aa-acara-ai-send-btn.is-stop {
        border-color: #e2e8f0;
        background: #ffffff;
        color: #111827;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
    }

    .aa-acara-ai-send-btn:disabled,
    .aa-acara-ai-icon-btn:disabled {
        cursor: wait;
        opacity: .68;
    }

    .aa-music-drawer-card {
        display: grid;
        gap: 14px;
    }

    .aa-music-drawer-status {
        margin: 0;
        border: 1px solid #d7f3ee;
        border-radius: 16px;
        background: #f0fdfa;
        padding: 10px 12px;
        color: #0f766e;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.55;
    }

    .aa-music-drawer-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        padding: 12px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }

    .aa-music-drawer-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 999px;
        background: #0f766e;
        color: #ffffff;
        box-shadow: 0 14px 24px rgba(15, 118, 110, .18);
    }

    .aa-music-drawer-preview strong,
    .aa-music-drawer-library-head strong {
        display: block;
        color: #0f172a;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-music-drawer-preview small,
    .aa-music-drawer-library-head small {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
        line-height: 1.45;
    }

    .aa-music-drawer-controls {
        display: grid;
        gap: 10px;
    }

    .aa-music-drawer-upload {
        display: grid;
        gap: 7px;
        border: 1px dashed #99f6e4;
        border-radius: 16px;
        background: #f0fdfa;
        padding: 10px;
    }

    .aa-music-drawer-upload .aa-panel-btn {
        width: 100%;
    }

    .aa-music-drawer-upload-status {
        margin: 0;
        color: #0f766e;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.45;
    }

    .aa-music-drawer-upload-status.is-error {
        color: #be123c;
    }

    .aa-music-drawer-upload-status.is-success {
        color: #047857;
    }

    .aa-music-drawer-check {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        padding: 10px 11px;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        line-height: 1.45;
    }

    .aa-music-drawer-check input {
        margin-top: 2px;
    }

    .aa-music-drawer-library {
        display: grid;
        gap: 9px;
        border-top: 1px solid #e2e8f0;
        padding-top: 12px;
    }

    .aa-music-drawer-list {
        display: grid;
        gap: 7px;
    }

    .aa-music-drawer-item,
    .aa-music-drawer-empty {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 9px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        padding: 9px 10px;
        color: #334155;
        font-size: 11px;
        font-weight: 800;
        text-align: left;
    }

    .aa-music-drawer-item {
        cursor: pointer;
    }

    .aa-music-drawer-item:hover {
        border-color: #14b8a6;
        color: #0f766e;
        box-shadow: 0 10px 22px rgba(15, 118, 110, .08);
    }

    .aa-music-drawer-item.is-active {
        border-color: #14b8a6;
        background: #f0fdfa;
        color: #0f766e;
    }

    .aa-music-drawer-item i,
    .aa-music-drawer-empty i {
        color: #0f766e;
    }

    .aa-music-preview-btn {
        display: inline-grid;
        width: 26px;
        height: 26px;
        flex: 0 0 26px;
        place-items: center;
        border: 1px solid #ccfbf1;
        border-radius: 999px;
        background: #ffffff;
        color: #0f766e;
    }

    .aa-music-preview-btn.is-playing {
        border-color: #0f766e;
        background: #0f766e;
        color: #ffffff;
    }

    .aa-music-preview-btn.is-playing i {
        color: #ffffff;
    }

    .aa-music-drawer-item-label {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-music-active-check {
        flex: 0 0 auto;
        margin-left: auto;
        opacity: 0;
        transform: scale(.86);
    }

    .aa-music-drawer-item.is-active .aa-music-active-check {
        opacity: 1;
        transform: scale(1);
    }

    .aa-left-tabs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        margin-bottom: 10px;
        border-radius: 14px;
        background: #f1f5f9;
        padding: 5px;
    }

    .aa-left-tab {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-left-tab:hover {
        color: #0f766e;
    }

    .aa-left-tab.is-active {
        background: #ffffff;
        color: #0f766e;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
    }

    .aa-tool-section {
        display: none;
    }

    .aa-tool-section.is-active {
        display: grid;
        gap: 8px;
    }

    .aa-tool-section-title {
        margin: 2px 0 0;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .aa-left-rail .aa-left-tab {
        display: grid;
        min-height: 64px;
        place-items: center;
        gap: 5px;
        border-radius: 14px;
        padding: 7px 4px;
        font-size: 11px;
        line-height: 1.1;
    }

    .aa-left-drawer .aa-tool-section {
        display: none;
    }

    .aa-left-drawer .aa-tool-section.is-active {
        display: grid;
        gap: 14px;
    }

    .aa-font-drawer-card {
        display: grid;
        gap: 12px;
        padding: 0;
        overflow: hidden;
    }

    .aa-font-drawer-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 16px 16px 4px;
    }

    .aa-font-drawer-head .aa-panel-title {
        margin: 0;
    }

    .aa-font-drawer-close {
        display: inline-grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #334155;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-font-drawer-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .aa-font-drawer-search-wrap {
        position: relative;
        padding: 0 16px;
    }

    .aa-font-drawer-search-wrap i {
        position: absolute;
        top: 50%;
        left: 30px;
        color: #64748b;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .aa-font-drawer-search {
        width: 100%;
        min-height: 42px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        background: #ffffff;
        padding: 0 12px 0 38px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 750;
        outline: none;
    }

    .aa-font-drawer-search:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, .12);
    }

    .aa-font-drawer-chips {
        display: none;
        gap: 8px;
        overflow-x: auto;
        padding: 0 16px 2px;
        scrollbar-width: none;
    }

    .aa-font-drawer-chips::-webkit-scrollbar {
        display: none;
    }

    .aa-font-drawer-chip {
        flex: 0 0 auto;
        min-height: 34px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #ffffff;
        padding: 0 12px;
        color: #475569;
        font-size: 12px;
        font-weight: 850;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-font-drawer-chip:hover,
    .aa-font-drawer-chip.is-active {
        border-color: #c4b5fd;
        background: #f3e8ff;
        color: #6d28d9;
    }

    .aa-font-drawer-list {
        display: grid;
        max-height: calc(100vh - 260px);
        overflow-y: auto;
        border-top: 1px solid #e2e8f0;
        gap: 5px;
        padding: 12px 10px 14px;
    }

    .aa-font-drawer-group {
        display: grid;
        gap: 5px;
        padding: 8px 0;
    }

    .aa-font-drawer-group-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 0 6px 4px;
        color: #334155;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-font-drawer-item {
        display: grid;
        gap: 4px;
    }

    .aa-font-drawer-row {
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr);
        align-items: center;
        gap: 4px;
    }

    .aa-font-drawer-weight-toggle,
    .aa-font-drawer-weight-spacer {
        width: 30px;
        height: 34px;
    }

    .aa-font-drawer-weight-toggle {
        display: inline-grid;
        place-items: center;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: .14s ease;
    }

    .aa-font-drawer-weight-toggle:hover,
    .aa-font-drawer-item.is-expanded .aa-font-drawer-weight-toggle {
        background: #f1f5f9;
        color: #4c1d95;
    }

    .aa-font-drawer-weight-toggle i {
        font-size: 11px;
        transition: transform .14s ease;
    }

    .aa-font-drawer-item.is-expanded .aa-font-drawer-weight-toggle i {
        transform: rotate(90deg);
    }

    .aa-font-drawer-option {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 24px;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        border: 0;
        border-radius: 12px;
        background: transparent;
        padding: 7px 10px;
        color: #111827;
        text-align: left;
        cursor: pointer;
        transition: .14s ease;
    }

    .aa-font-drawer-option:hover {
        background: #f8fafc;
    }

    .aa-font-drawer-option.is-active {
        background: #ede9fe;
        color: #4c1d95;
    }

    .aa-font-drawer-preview {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 18px;
        line-height: 1.2;
    }

    .aa-font-drawer-option i {
        color: #4c1d95;
        opacity: 0;
    }

    .aa-font-drawer-option.is-active i {
        opacity: 1;
    }

    .aa-font-weight-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 5px;
        margin: 0 0 5px 34px;
        padding: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }

    .aa-font-weight-option {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 14px;
        align-items: center;
        gap: 6px;
        min-height: 30px;
        border: 1px solid transparent;
        border-radius: 9px;
        background: #ffffff;
        color: #334155;
        padding: 5px 8px;
        font-size: 11px;
        line-height: 1.15;
        text-align: left;
        cursor: pointer;
        transition: .14s ease;
    }

    .aa-font-weight-option span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-font-weight-option i {
        color: #4c1d95;
        font-size: 10px;
        opacity: 0;
    }

    .aa-font-weight-option:hover,
    .aa-font-weight-option.is-active {
        border-color: #c4b5fd;
        background: #ede9fe;
        color: #4c1d95;
    }

    .aa-font-weight-option.is-active i {
        opacity: 1;
    }

    .aa-font-drawer-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        margin: 8px 6px;
        padding: 18px 12px;
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
        text-align: center;
    }

    .aa-rightbar .aa-panel-card {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
    }

    .aa-rightbar #aaSelectionHint {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        padding: 10px 12px;
    }

    .aa-layer-panel {
        display: grid;
        gap: 10px;
    }

    .aa-layer-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .aa-layer-count {
        display: inline-flex;
        min-width: 26px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(20, 184, 166, .2);
        border-radius: 999px;
        background: rgba(240, 253, 250, .92);
        color: #0f766e;
        font-size: 11px;
        font-weight: 950;
    }

    .aa-layer-hint {
        margin: -4px 0 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.45;
    }

    .aa-layer-list {
        display: grid;
        gap: 7px;
        max-height: 320px;
        overflow: auto;
        padding-right: 2px;
    }

    .aa-layer-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        background: rgba(248, 250, 252, .9);
        padding: 12px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-align: center;
    }

    .aa-layer-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 15px;
        background: rgba(255, 255, 255, .92);
        padding: 7px;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .aa-layer-row.is-active {
        border-color: rgba(20, 184, 166, .55);
        background: linear-gradient(135deg, rgba(240, 253, 250, .96), rgba(239, 246, 255, .9));
        box-shadow: 0 12px 26px rgba(15, 118, 110, .08);
    }

    .aa-layer-row.is-hidden-object {
        opacity: .66;
    }

    .aa-layer-main {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr);
        align-items: center;
        gap: 8px;
        min-width: 0;
        border: 0;
        background: transparent;
        padding: 0;
        text-align: left;
        cursor: pointer;
    }

    .aa-layer-icon {
        display: inline-flex;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(15, 118, 110, .08);
        color: #0f766e;
        font-size: 13px;
    }

    .aa-layer-meta {
        display: grid;
        gap: 2px;
        min-width: 0;
    }

    .aa-layer-meta strong,
    .aa-layer-meta small {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-layer-meta strong {
        color: #172033;
        font-size: 12px;
        font-weight: 950;
        line-height: 1.2;
        text-transform: capitalize;
    }

    .aa-layer-meta small {
        color: #64748b;
        font-size: 10px;
        font-weight: 850;
    }

    .aa-layer-actions {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .aa-layer-actions button {
        display: inline-flex;
        width: 28px;
        height: 28px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(203, 213, 225, .86);
        border-radius: 10px;
        background: rgba(255, 255, 255, .9);
        color: #334155;
        font-size: 11px;
        cursor: pointer;
        transition: border-color .18s ease, color .18s ease, background .18s ease;
    }

    .aa-layer-actions button:hover {
        border-color: rgba(20, 184, 166, .42);
        background: rgba(240, 253, 250, .95);
        color: #0f766e;
    }

    /* Modern editor skin: visual-only refresh, keep existing IDs and JS handlers intact. */
    .aa-studio-shell {
        grid-template-rows: 74px 1fr;
        background: #f6faf9;
        color: #111827;
    }

    .aa-topbar {
        position: relative;
        isolation: isolate;
        z-index: 220;
        overflow: visible;
        gap: 16px;
        background:
            radial-gradient(circle at 18% 0%, rgba(45, 212, 191, .18), transparent 30%),
            radial-gradient(circle at 78% 14%, rgba(59, 130, 246, .12), transparent 26%),
            rgba(255, 255, 255, .88);
        padding: 0 22px;
        color: #111827;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    .aa-topbar::after {
        content: "";
        position: absolute;
        z-index: -1;
        height: 1px;
        border-radius: 999px;
        background: linear-gradient(90deg, transparent, rgba(20, 184, 166, .42), rgba(59, 130, 246, .24), transparent);
    }

    .aa-topbar-brand {
        display: grid;
        gap: 2px;
        min-width: 210px;
        padding-left: 2px;
    }

    .aa-editor-brand-card {
        grid-template-columns: auto auto minmax(0, 1fr);
        align-items: center;
        gap: 16px;
        min-width: 300px;
        border-radius: 0 0 20px 20px;
        background: rgba(255, 255, 255, .86);
        padding: 12px 18px 14px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
    }

    .aa-editor-brand-logo {
        display: grid;
        gap: 3px;
        min-width: 112px;
    }

    .aa-editor-brand-logo strong {
        color: #0f766e;
        font-size: 14px;
        font-weight: 950;
        letter-spacing: .20em;
        line-height: 1;
    }

    .aa-editor-brand-logo span {
        color: #94a3b8;
        font-size: 8px;
        font-weight: 950;
        letter-spacing: .32em;
        line-height: 1;
    }

    .aa-editor-brand-divider {
        width: 1px;
        height: 28px;
        border-radius: 999px;
        background: rgba(15, 23, 42, .28);
    }

    .aa-editor-brand-project {
        display: grid;
        gap: 4px;
    }

    .aa-editor-brand-project h1 {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
        line-height: 1.1;
    }

    .aa-editor-brand-live-dot {
        width: 6px;
        height: 6px;
        display: inline-block;
        flex: 0 0 auto;
        border-radius: 999px;
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .14);
    }

    .aa-editor-brand-project p {
        color: #94a3b8 !important;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: 0;
        line-height: 1.1;
        text-shadow: none;
    }

    .aa-editor-brand-project p.is-saving {
        color: #2563eb !important;
    }

    .aa-editor-brand-project p.is-error {
        color: #be123c !important;
    }

    .aa-topbar p {
        color: #007f6e !important;
        letter-spacing: .22em;
        text-shadow: 0 8px 22px rgba(0, 127, 110, .12);
    }

    .aa-topbar h1 {
        color: #0f172a;
        font-size: 17px;
        line-height: 1.25;
    }

    .aa-topbar .aa-editor-brand-project h1 {
        color: #0f172a;
        font-size: 12px;
        line-height: 1.1;
    }

    .aa-topbar .aa-editor-brand-project p {
        color: #94a3b8 !important;
        letter-spacing: 0;
        text-shadow: none;
    }

    .aa-topbar-controls {
        gap: 10px !important;
        overflow: visible;
    }

    .aa-topbar-group,
    .aa-topbar-size-controls {
        position: relative;
        display: inline-flex;
        min-height: 48px;
        align-items: center;
        gap: 5px;
        border: 0;
        border-radius: 20px;
        background: transparent;
        padding: 4px;
        box-shadow: none;
    }

    .aa-topbar-actions {
        gap: 8px !important;
    }

    .aa-tablet-action-dock {
        box-sizing: border-box;
        display: none;
    }

    .aa-topbar #aaZoomLabel {
        color: #475569 !important;
        min-width: 54px;
        border-radius: 999px;
        padding: 0 8px;
    }

    .aa-editor-status-pill {
        display: inline-flex;
        width: 100%;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        margin: 0 0 14px;
        border: 1px solid #bbf7d0;
        border-radius: 999px;
        background: #ecfdf5;
        color: #0f766e;
        padding: 8px 14px;
        text-align: center;
        font-size: 12px;
        font-weight: 950;
        line-height: 1.25;
        transform-origin: center;
        will-change: transform, box-shadow;
        white-space: normal;
    }

    .aa-topbar .aa-editor-status-pill {
        width: auto;
        min-height: 32px;
        flex: 0 1 142px;
        margin: 0;
        border-color: rgba(134, 239, 172, .78);
        background:
            linear-gradient(100deg, rgba(255, 255, 255, .24), rgba(255, 255, 255, 0) 34%),
            #ecfdf5;
        padding: 6px 12px;
        overflow: hidden;
        text-overflow: inherit;
        white-space: normal;
        font-size: 11px;
        line-height: 1;
        box-shadow: 0 10px 26px rgba(15, 118, 110, .1);
    }

    .aa-editor-status-pill.is-status-pulse {
        animation: aaStatusPulse .28s cubic-bezier(.2, .8, .2, 1);
    }

    @keyframes aaStatusPulse {
        0% {
            transform: scale(.96);
            box-shadow: 0 0 0 rgba(15, 118, 110, 0);
        }
        55% {
            transform: scale(1.035);
            box-shadow: 0 10px 26px rgba(15, 118, 110, .14);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 rgba(15, 118, 110, 0);
        }
    }

    .aa-action-btn {
        min-height: 40px;
        border-radius: 15px;
        background: transparent;
        color: #172033;
        padding: 0 15px;
        font-weight: 850;
        box-shadow: none;
        transform: translateY(0);
    }

    .aa-action-btn:hover {
        border-color: #a7d8cf;
        background: transparent;
        color: #007f6e;
        box-shadow: none;
        transform: translateY(-2px);
    }

    .aa-action-btn:active {
        transform: translateY(0);
    }

    #aaZoomOutBtn,
    #aaZoomInBtn {
        width: 42px;
        padding: 0;
        border-radius: 14px;
    }

    .aa-primary {
        border-color: #73b7ff;
        background: linear-gradient(180deg, #f5fbff, #eaf4ff);
        color: #0068d8;
        box-shadow: 0 10px 26px rgba(0, 104, 216, .13);
    }

    .aa-primary:hover {
        border-color: #3494ff;
        background: #e7f2ff;
        color: #0057b8;
    }

    .aa-action-btn.is-active {
        border-color: rgba(45, 212, 191, .9);
        background: linear-gradient(180deg, #ccfbf1, #ecfdf5);
        color: #006d5f;
        box-shadow: inset 0 0 0 1px rgba(45, 212, 191, .24), 0 10px 24px rgba(20, 184, 166, .15);
    }

    .aa-publish {
        border-color: transparent;
        background: linear-gradient(135deg, #008f7a, #00a58e);
        color: #ffffff;
        box-shadow: 0 14px 30px rgba(0, 143, 122, .25);
    }

    .aa-publish:hover {
        border-color: transparent;
        background: linear-gradient(135deg, #007f6e, #008f7a);
        color: #ffffff;
    }

    .aa-workspace {
        grid-template-columns: 90px minmax(0, 1fr) 236px;
        background: #f7fbfa;
    }

    .aa-leftbar {
        background: #ffffff00;
    }

    .aa-left-rail {
        gap: 14px;
        border-right: 1px solid #e5edf100;
        background: #8d828200;
        padding: 22px 10px;
    }

    .aa-left-rail-link,
    .aa-left-rail-tab {
        min-height: 72px;
        border: 1px solid transparent;
        border-radius: 18px;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-left-rail-link i,
    .aa-left-rail-tab i {
        font-size: 22px;
    }

    .aa-left-rail-link:hover,
    .aa-left-rail-tab:hover,
    .aa-left-rail-tab.is-active {
        border-color: #d8f3ee;
        background: #eefdf9;
        color: #007f6e;
        box-shadow: 0 16px 34px rgba(0, 143, 122, .12);
    }

    .aa-left-rail-spacer {
        display: none;
    }

    .aa-left-drawer {
        left: 90px;
        width: 390px;
        align-content: start;
        align-items: start;
        grid-auto-rows: max-content;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    .aa-left-drawer-close {
        position: sticky;
        top: 0;
        z-index: 3;
        justify-self: end;
        display: inline-grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 1px solid #e0e9ef;
        border-radius: 999px;
        background: rgba(255, 255, 255, .94);
        color: #334155;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        cursor: pointer;
    }

    .aa-left-drawer-close:hover {
        border-color: #a7d8cf;
        color: #007f6e;
    }

    .aa-panel-card {
        border-color: #e6eef3;
        border-radius: 24px;
        background: #ffffff;
        padding: 18px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .07);
    }

    .aa-panel-title {
        color: #344154;
        font-size: 12px;
        letter-spacing: .14em;
    }

    .aa-template-search-wrap,
    .aa-font-drawer-search,
    .aa-field {
        border-color: #e0e9ef;
        border-radius: 16px;
        background: #ffffff;
    }

    .aa-editor-template-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .aa-editor-template-card {
        border: 0;
        border-radius: 12px;
        background: transparent;
        box-shadow: none;
    }

    .aa-template-list-view {
        position: relative;
    }

    .aa-editor-template-card:hover {
        box-shadow: 0 18px 34px rgba(0, 143, 122, .14);
        transform: translateY(-2px);
    }

    .aa-template-category-view,
    .aa-template-list-view {
        display: grid;
        gap: 12px;
    }

    .aa-template-category-view[hidden],
    .aa-template-list-view[hidden] {
        display: none !important;
    }

    .aa-template-category-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .aa-template-category-card {
        display: grid;
        min-height: 106px;
        align-content: space-between;
        gap: 10px;
        border: 1px solid #e0e9ef;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        padding: 12px;
        color: #0f172a;
        text-align: left;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        transition: .16s ease;
    }

    .aa-template-category-card:hover {
        border-color: #a7d8cf;
        background: #effdf9;
        color: #007f6e;
        box-shadow: 0 18px 34px rgba(0, 143, 122, .13);
        transform: translateY(-1px);
    }

    .aa-template-category-icon {
        display: inline-grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 16px;
        background: var(--aa-template-category-bg, #eefdf9);
        color: var(--aa-template-category-fg, #0f766e);
        font-size: 18px;
    }

    .aa-template-category-name {
        display: block;
        overflow: hidden;
        color: inherit;
        font-size: 12px;
        font-weight: 950;
        line-height: 1.25;
        text-overflow: ellipsis;
    }

    .aa-editor-asset-category-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px 10px;
    }

    .aa-editor-asset-category-card {
        min-height: 86px;
        place-items: center;
        align-content: start;
        gap: 7px;
        border: 0;
        border-radius: 16px;
        background: transparent;
        padding: 0;
        color: #334155;
        text-align: center;
        box-shadow: none;
    }

    .aa-editor-asset-category-card:hover {
        border-color: transparent;
        background: transparent;
        color: #007f6e;
        box-shadow: none;
        transform: translateY(-1px);
    }

    .aa-editor-asset-category-card .aa-template-category-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background:
            linear-gradient(145deg, rgba(255, 255, 255, .92), rgba(236, 253, 245, .72)),
            var(--aa-template-category-bg, #eefdf9);
        color: var(--aa-template-category-fg, #0f766e);
        font-size: 18px;
        box-shadow:
            0 12px 24px rgba(15, 23, 42, .1),
            inset 0 1px 0 rgba(255, 255, 255, .86);
    }

    .aa-editor-asset-category-card:hover .aa-template-category-icon {
        box-shadow:
            0 16px 28px rgba(0, 168, 138, .16),
            inset 0 1px 0 rgba(255, 255, 255, .9);
        transform: translateY(-1px);
    }

    .aa-editor-asset-category-card .aa-template-category-name {
        width: 100%;
        font-size: 11px;
        font-weight: 850;
        line-height: 1.25;
        white-space: normal;
    }

    .aa-editor-inline-tool-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px 10px;
    }

    .aa-editor-quick-elements {
        display: grid;
        gap: 12px;
        margin-top: 16px;
        border-top: 1px solid rgba(226, 232, 240, .78);
        padding-top: 14px;
    }

    .aa-editor-inline-tool-grid .aa-tool-btn {
        min-height: 82px;
        gap: 7px;
        border: 0;
        border-radius: 16px;
        background: transparent;
        padding: 0;
        color: #334155;
        font-size: 11px;
        font-weight: 850;
        line-height: 1.2;
        text-align: center;
        box-shadow: none;
    }

    .aa-editor-inline-tool-grid .aa-tool-btn:hover {
        border-color: transparent;
        background: transparent;
        color: #007f6e;
        box-shadow: none;
        transform: translateY(-1px);
    }

    .aa-editor-inline-tool-grid .aa-tool-btn .aa-lucide-icon,
    .aa-editor-inline-tool-grid .aa-tool-btn svg.aa-lucide-icon,
    .aa-editor-inline-tool-grid .aa-tool-btn > span[aria-hidden="true"] {
        display: inline-grid;
        width: 52px;
        height: 52px;
        place-items: center;
        border-radius: 18px;
        background:
            linear-gradient(145deg, rgba(255, 255, 255, .92), rgba(236, 253, 245, .72)),
            #ecfdf5;
        color: #0f766e;
        box-shadow:
            0 12px 24px rgba(15, 23, 42, .1),
            inset 0 1px 0 rgba(255, 255, 255, .86);
        transition: transform .16s ease, box-shadow .16s ease, color .16s ease;
    }

    .aa-editor-inline-tool-grid .aa-tool-btn .aa-lucide-icon,
    .aa-editor-inline-tool-grid .aa-tool-btn svg.aa-lucide-icon {
        padding: 13px;
        stroke-width: 2.15;
    }

    .aa-editor-inline-tool-grid .aa-tool-btn > span[aria-hidden="true"] {
        font-size: 19px;
        font-weight: 950;
    }

    .aa-editor-inline-tool-grid .aa-tool-btn:hover .aa-lucide-icon,
    .aa-editor-inline-tool-grid .aa-tool-btn:hover svg.aa-lucide-icon,
    .aa-editor-inline-tool-grid .aa-tool-btn:hover > span[aria-hidden="true"] {
        color: #00a88a;
        box-shadow:
            0 16px 28px rgba(0, 168, 138, .16),
            inset 0 1px 0 rgba(255, 255, 255, .9);
        transform: translateY(-1px);
    }

    .aa-template-list-head {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .aa-template-back-btn {
        display: inline-grid;
        width: 36px;
        height: 36px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid #e0e9ef;
        border-radius: 999px;
        background: #ffffff;
        color: #334155;
        cursor: pointer;
    }

    .aa-template-back-btn:hover {
        border-color: #a7d8cf;
        background: #effdf9;
        color: #007f6e;
    }

    .aa-template-list-title {
        min-width: 0;
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 950;
    }

    .aa-topbar-size-controls {
        position: relative;
        display: inline-flex;
        min-height: 48px;
        align-items: center;
        gap: 5px;
        border: 0;
        border-radius: 20px;
        background: transparent;
        padding: 4px;
        box-shadow: none;
    }

    .aa-resize-menu-btn {
        gap: 7px;
        letter-spacing: .08em;
    }

    .aa-resize-menu-btn i {
        font-size: 10px;
        transition: transform .16s ease;
    }

    .aa-topbar-size-controls.is-open .aa-resize-menu-btn i {
        transform: rotate(180deg);
    }

    .aa-resize-menu-panel {
        position: absolute;
        top: calc(100% + 8px);
        left: 50%;
        z-index: 180;
        display: flex;
        width: max-content;
        min-width: 0;
        max-width: calc(100vw - 32px);
        transform: translateX(-50%);
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(203, 213, 225, .72);
        border-radius: 999px;
        background: rgba(255, 255, 255, .96);
        padding: 6px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .16);
        white-space: nowrap;
    }

    .aa-resize-menu-panel[hidden] {
        display: none;
    }

    .aa-resize-menu-panel .aa-action-btn {
        min-width: 64px;
        justify-content: center;
        padding: 0 12px;
        background: transparent;
        box-shadow: none;
    }

    .aa-resize-menu-panel #aaSquareBtn {
        min-width: 82px;
    }

    /* Visual polish pass: hierarchy and consistency only. */
    .aa-topbar {
        min-height: 68px;
        gap: 18px;
        padding-inline: 24px;
    }

    .aa-topbar-brand {
        min-width: 230px;
    }

    .aa-topbar-controls {
        gap: 14px !important;
    }

    .aa-topbar-history,
    .aa-topbar-zoom {
        gap: 7px;
    }

    .aa-topbar-size-controls {
        margin-inline: 2px;
    }

    .aa-topbar-history::after,
    .aa-topbar-size-controls::after {
        content: "";
        display: block;
        width: 1px;
        height: 24px;
        margin-left: 9px;
        border-radius: 999px;
        background: linear-gradient(to bottom, transparent, rgba(148, 163, 184, .42), transparent);
    }

    .aa-topbar-actions {
        gap: 10px !important;
    }

    .aa-topbar .aa-action-btn {
        min-height: 42px;
        border-radius: 16px;
        padding-inline: 14px;
    }

    .aa-topbar .aa-primary,
    .aa-topbar .aa-publish {
        padding-inline: 17px;
    }

    .aa-topbar #aaZoomOutBtn,
    .aa-topbar #aaZoomInBtn {
        width: 42px;
        min-width: 42px;
        padding-inline: 0;
    }

    .aa-topbar #aaFitBtn {
        padding-inline: 12px;
    }

    .aa-topbar .aa-editor-status-pill {
        flex-basis: 132px;
        min-height: 34px;
        font-size: 11px;
    }

    .page-top-controls {
        gap: 12px;
        border-color: rgba(205, 224, 233, .92);
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(248, 250, 252, .94));
        box-shadow: 0 16px 38px rgba(15, 23, 42, .12);
    }

    .page-title-button {
        min-width: 0;
        font-weight: 950;
    }

    .page-title-button::before {
        min-height: 26px;
        padding-inline: 9px;
        background: #ecfdf5;
        box-shadow: inset 0 0 0 1px rgba(20, 184, 166, .14);
    }

    .page-title-button span {
        max-width: 340px;
    }

    .page-top-controls .aa-page-actions {
        align-items: center;
    }

    .page-top-controls .aa-page-action {
        border-color: #dbe8ef;
        background:
            linear-gradient(180deg, #ffffff, #f8fafc);
        color: #253246;
    }

    .page-top-controls .aa-page-action:disabled {
        background: #f8fafc;
        color: #a7b2c2;
        box-shadow: none;
    }

    .aa-left-drawer .aa-panel-card,
    .aa-rightbar .aa-panel-card {
        border-color: rgba(219, 231, 239, .92);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 250, 252, .96));
        box-shadow: 0 18px 48px rgba(15, 23, 42, .065);
    }

    .aa-left-drawer .aa-panel-title,
    .aa-rightbar .aa-panel-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #27364a;
    }

    .aa-left-drawer .aa-panel-title::before,
    .aa-rightbar .aa-panel-title::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #00a88a;
        box-shadow: 0 0 0 4px rgba(0, 168, 138, .1);
    }

    .aa-premium-upgrade-card {
        display: grid;
        gap: 11px;
        margin-top: auto;
        padding: 14px;
        border: 1px solid rgba(250, 204, 21, .55);
        border-radius: 18px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(255, 251, 235, .92));
        box-shadow: 0 18px 42px rgba(146, 64, 14, .1);
    }

    .aa-premium-upgrade-card.is-active {
        border-color: rgba(20, 184, 166, .28);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(240, 253, 250, .94));
        box-shadow: 0 18px 42px rgba(15, 118, 110, .09);
    }

    .aa-premium-upgrade-card-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #172033;
        font-size: 13px;
        font-weight: 950;
        line-height: 1.2;
    }

    .aa-premium-upgrade-card-title i {
        color: #b7791f;
        font-size: 14px;
    }

    .aa-premium-upgrade-card ul {
        display: grid;
        gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .aa-premium-upgrade-card li {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        color: #475569;
        font-size: 11px;
        font-weight: 850;
        line-height: 1.3;
    }

    .aa-premium-upgrade-card li i {
        display: grid;
        flex: 0 0 16px;
        width: 16px;
        height: 16px;
        place-items: center;
        border-radius: 999px;
        background: rgba(20, 184, 166, .13);
        color: #0f766e;
        font-size: 9px;
    }

    .aa-premium-upgrade-card-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        border-radius: 12px;
        background: #101827;
        color: #ffffff;
        font-size: 12px;
        font-weight: 950;
        line-height: 1;
        text-decoration: none;
        box-shadow: 0 14px 26px rgba(15, 23, 42, .16);
        transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .aa-premium-upgrade-card-button.is-active {
        cursor: default;
        background: #0f766e;
        box-shadow: 0 14px 26px rgba(15, 118, 110, .14);
    }

    .aa-premium-upgrade-card-button:hover {
        background: #0f766e;
        color: #ffffff;
        box-shadow: 0 16px 30px rgba(15, 118, 110, .18);
        transform: translateY(-1px);
    }

    .aa-field,
    .aa-template-search-wrap,
    .aa-font-drawer-search,
    .aa-snippet-search-wrap,
    .aa-editor-asset-search-hero {
        border-color: #dce8ef;
        background: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7);
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .aa-field:hover,
    .aa-template-search-wrap:hover,
    .aa-font-drawer-search:hover,
    .aa-snippet-search-wrap:hover,
    .aa-editor-asset-search-hero:hover {
        border-color: #b7d8d2;
    }

    .aa-field:focus,
    .aa-field:focus-visible,
    .aa-template-search-input:focus,
    .aa-font-drawer-search:focus,
    .aa-snippet-search-wrap:focus-within,
    .aa-editor-asset-search-hero:focus-within {
        border-color: #00a88a;
        box-shadow: 0 0 0 4px rgba(0, 168, 138, .12);
        outline: none;
    }

    .aa-gallery-empty {
        display: grid;
        min-height: 92px;
        place-items: center;
        border-color: rgba(0, 168, 138, .28);
        border-radius: 18px;
        background:
            radial-gradient(circle at 22% 14%, rgba(0, 168, 138, .1), transparent 34%),
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(246, 249, 252, .92));
        color: #66758a;
        line-height: 1.45;
    }

    .aa-panel-loading {
        border-color: rgba(125, 211, 252, .72);
        border-radius: 18px;
        background:
            linear-gradient(90deg, rgba(240, 249, 255, .96), rgba(236, 253, 245, .96));
        color: #0f766e;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .65);
    }

    .aa-tool-section-title {
        color: #8491a4;
        letter-spacing: .13em;
    }

    .aa-editor-template-thumb {
        aspect-ratio: 9 / 13;
        border-radius: 16px;
    }

    .aa-bg-control-grid,
    .aa-color-drawer-grid {
        display: grid;
        gap: 10px;
    }

    .aa-bg-range-row,
    .aa-color-drawer-row {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 10px;
    }

    .aa-bg-range-row input[type="range"],
    .aa-color-drawer-row input[type="range"] {
        width: 100%;
        accent-color: #0f9f86;
    }

    .aa-color-drawer-alpha {
        display: grid;
        gap: 8px;
        border: 1px solid #e0e9ef;
        border-radius: 14px;
        background:
            linear-gradient(45deg, #e2e8f0 25%, transparent 25%),
            linear-gradient(-45deg, #e2e8f0 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #e2e8f0 75%),
            linear-gradient(-45deg, transparent 75%, #e2e8f0 75%),
            #ffffff;
        background-position: 0 0, 0 6px, 6px -6px, -6px 0;
        background-size: 12px 12px;
        padding: 10px;
    }

    .aa-color-drawer-alpha.hidden {
        display: none;
    }

    .aa-color-drawer-alpha-label {
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .aa-color-drawer-alpha-preview {
        display: block;
        height: 24px;
        border: 1px solid rgba(15, 23, 42, .12);
        border-radius: 10px;
        background: var(--aa-alpha-preview, #111827);
    }

    .aa-bg-value,
    .aa-color-drawer-value {
        min-width: 46px;
        border: 1px solid #e0e9ef;
        border-radius: 12px;
        background: #f8fafc;
        color: #0f172a;
        padding: 7px 8px;
        text-align: center;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-color-drawer-preview {
        display: grid;
        grid-template-columns: 34px 1fr;
        gap: 10px;
        align-items: center;
    }

    .aa-drawer-color-picker {
        display: grid;
        gap: 10px;
    }

    .aa-drawer-color-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-drawer-color-title span:last-child {
        color: #64748b;
        font-size: 11px;
        letter-spacing: .04em;
    }

    .aa-drawer-color-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 24px;
        gap: 10px;
        align-items: stretch;
    }

    .aa-drawer-color-field {
        position: relative;
        display: block;
        aspect-ratio: 1.12;
        min-height: 158px;
        border: 1px solid rgba(15, 23, 42, .14);
        border-radius: 14px;
        background:
            linear-gradient(to top, #000, transparent),
            linear-gradient(to right, #fff, var(--aa-drawer-hue, #ff0000));
        cursor: crosshair;
        overflow: hidden;
        padding: 0;
    }

    .aa-drawer-color-handle {
        position: absolute;
        left: var(--aa-drawer-handle-x, 100%);
        top: var(--aa-drawer-handle-y, 0%);
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 999px;
        box-shadow: 0 0 0 1px rgba(15, 23, 42, .42), 0 4px 10px rgba(15, 23, 42, .18);
        transform: translate(-50%, -50%);
        pointer-events: none;
    }

    .aa-drawer-hue-bar {
        position: relative;
        width: 22px;
        min-height: 158px;
        border: 1px solid rgba(15, 23, 42, .14);
        border-radius: 999px;
        background: linear-gradient(to bottom,
                #ff0000 0%,
                #ffff00 16.66%,
                #00ff00 33.33%,
                #00ffff 50%,
                #0000ff 66.66%,
                #ff00ff 83.33%,
                #ff0000 100%);
        cursor: pointer;
        padding: 0;
        box-shadow: inset 0 0 0 2px rgba(255, 255, 255, .32);
    }

    .aa-drawer-hue-handle {
        position: absolute;
        left: 50%;
        top: var(--aa-drawer-hue-y, 0%);
        width: 28px;
        height: 10px;
        border: 2px solid #ffffff;
        border-radius: 999px;
        box-shadow: 0 0 0 1px rgba(15, 23, 42, .4), 0 4px 10px rgba(15, 23, 42, .18);
        transform: translate(-50%, -50%);
        pointer-events: none;
    }

    .aa-drawer-color-preview {
        width: 34px;
        height: 34px;
        border: 1px solid rgba(15, 23, 42, .16);
        border-radius: 10px;
        background: var(--aa-drawer-current, #111827);
        box-shadow: inset 0 0 0 2px rgba(255, 255, 255, .78);
    }

    .aa-drawer-native-color {
        position: absolute;
        width: 1px !important;
        height: 1px !important;
        opacity: 0;
        pointer-events: auto;
    }

    .aa-color-drawer-preview input[type="color"] {
        width: 54px;
        height: 44px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        background: #ffffff;
        padding: 4px;
        cursor: pointer;
    }

    .aa-color-drawer-hex {
        width: 100%;
        min-height: 44px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 900;
        outline: none;
    }

    .aa-color-drawer-swatches {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 8px;
    }

    .aa-color-materials {
        display: grid;
        gap: 14px;
        border: 1px solid #e0e9ef;
        border-radius: 16px;
        background: #ffffff;
        padding: 12px;
    }

    .aa-color-material-group {
        display: grid;
        gap: 9px;
    }

    .aa-color-material-title {
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .03em;
    }

    .aa-color-material-list {
        display: flex;
        flex-wrap: nowrap;
        gap: 10px;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        padding: 2px 2px 8px;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
    }

    .aa-color-material-swatch {
        position: relative;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, .12);
        border-radius: 999px;
        background: #f8fafc;
        box-shadow: inset 0 0 0 2px rgba(255, 255, 255, .58), 0 8px 18px rgba(15, 23, 42, .08);
        cursor: pointer;
    }

    .aa-color-material-swatch:hover,
    .aa-color-material-swatch:focus-visible {
        border-color: #b9812f;
        box-shadow: 0 0 0 3px rgba(185, 129, 47, .16), inset 0 0 0 2px rgba(255, 255, 255, .58);
        outline: none;
    }

    .aa-color-material-swatch::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(115deg, transparent 0%, rgba(255,255,255,.62) 35%, transparent 49%),
            linear-gradient(72deg, transparent 8%, rgba(255,255,255,.26) 46%, transparent 65%);
        opacity: .86;
    }

    .aa-color-material-swatch.is-foil.is-gold {
        background: radial-gradient(circle at 32% 30%, #fff8c9 0%, #d4af37 38%, #8f6b12 100%);
    }

    .aa-color-material-swatch.is-foil.is-copper {
        background: radial-gradient(circle at 32% 30%, #ffd7b0 0%, #c8753f 45%, #7c341c 100%);
    }

    .aa-color-material-swatch.is-foil.is-blue {
        background: radial-gradient(circle at 32% 30%, #c8f2ff 0%, #4da3c7 45%, #1e5571 100%);
    }

    .aa-color-material-swatch.is-foil.is-pearl {
        background: conic-gradient(from 40deg, #f8fafc, #dbeafe, #f5d0fe, #ccfbf1, #f8fafc);
    }

    .aa-color-material-swatch.is-foil.is-red {
        background: radial-gradient(circle at 32% 30%, #fecaca 0%, #dc2626 45%, #7f1d1d 100%);
    }

    .aa-color-material-swatch.is-foil.is-rose {
        background: radial-gradient(circle at 32% 30%, #ffe4e6 0%, #be6b75 45%, #7f3540 100%);
    }

    .aa-color-material-swatch.is-foil.is-silver {
        background: radial-gradient(circle at 32% 30%, #ffffff 0%, #cbd5e1 45%, #64748b 100%);
    }

    .aa-color-material-swatch.is-glitter {
        background-image:
            radial-gradient(circle at 12% 18%, rgba(255,255,255,.98) 0 1.8px, transparent 2.2px),
            radial-gradient(circle at 36% 28%, rgba(255,255,255,.86) 0 1.4px, transparent 1.8px),
            radial-gradient(circle at 62% 16%, rgba(255,255,255,.9) 0 1.5px, transparent 2px),
            radial-gradient(circle at 82% 36%, rgba(255,255,255,.78) 0 1.3px, transparent 1.8px),
            radial-gradient(circle at 24% 58%, rgba(255,255,255,.72) 0 1.4px, transparent 1.9px),
            radial-gradient(circle at 52% 70%, rgba(255,255,255,.92) 0 1.7px, transparent 2.2px),
            radial-gradient(circle at 76% 78%, rgba(255,255,255,.64) 0 1.2px, transparent 1.7px),
            radial-gradient(circle at 42% 46%, rgba(0,0,0,.22) 0 1px, transparent 1.4px),
            linear-gradient(135deg, var(--aa-glitter-a, #d4af37), var(--aa-glitter-b, #8f6b12));
        background-size: 7px 7px, 9px 9px, 11px 11px, 13px 13px, 8px 8px, 12px 12px, 10px 10px, 6px 6px, 100% 100%;
    }

    .aa-color-material-swatch.is-glitter.is-gold { --aa-glitter-a: #facc15; --aa-glitter-b: #8f6b12; }
    .aa-color-material-swatch.is-glitter.is-silver { --aa-glitter-a: #f8fafc; --aa-glitter-b: #64748b; }
    .aa-color-material-swatch.is-glitter.is-black { --aa-glitter-a: #334155; --aa-glitter-b: #020617; }
    .aa-color-material-swatch.is-glitter.is-aqua { --aa-glitter-a: #22d3ee; --aa-glitter-b: #0f766e; }
    .aa-color-material-swatch.is-glitter.is-emerald { --aa-glitter-a: #10b981; --aa-glitter-b: #064e3b; }
    .aa-color-material-swatch.is-glitter.is-rose { --aa-glitter-a: #f9a8d4; --aa-glitter-b: #be185d; }
    .aa-color-material-swatch.is-glitter.is-pink { --aa-glitter-a: #f472b6; --aa-glitter-b: #db2777; }
    .aa-color-material-swatch.is-glitter.is-purple { --aa-glitter-a: #c084fc; --aa-glitter-b: #7e22ce; }

    .aa-color-drawer-swatch {
        aspect-ratio: 1;
        border: 1px solid rgba(15, 23, 42, .12);
        border-radius: 12px;
        background: var(--aa-swatch, #111827);
        cursor: pointer;
    }

    .aa-tool-btn,
    .aa-panel-btn {
        border-color: #e0e9ef;
        border-radius: 16px;
        background: #ffffff;
        color: #263348;
    }

    .aa-tool-btn:hover,
    .aa-panel-btn:hover,
    .aa-panel-btn.is-active {
        border-color: #a7d8cf;
        background: #effdf9;
        color: #007f6e;
        box-shadow: 0 12px 24px rgba(0, 143, 122, .1);
    }

    .aa-stage-wrap {
        background-size: 900px auto;
        background-repeat: repeat;
        background-position: center;
        padding: 50px 42px 42px;
    }

    .editor-pages-scroll,
    .aa-page-list {
        gap: 34px;
    }

    .aa-editor-mode-strip {
        display: flex;
        justify-content: center;
        margin: 0 0 40px;
        pointer-events: auto;
    }

    .aa-editor-mode-toggle {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid rgba(213, 226, 235, .96);
        border-radius: 999px;
        background: rgba(255, 255, 255, .98);
        padding: 5px;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .08);
        transform: scale(var(--aa-page-control-scale, 1));
        transform-origin: top center;
    }

    .aa-editor-mode-btn {
        min-height: 48px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #64748b;
        padding: 0 13px;
        font: inherit;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
    }

    .aa-editor-mode-btn.is-active {
        background: #0f766e;
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(15, 118, 110, .18);
    }

    .aa-editor-mode-btn.is-locked {
        background: #f59e0b;
        color: #fff7ed;
        cursor: help;
        box-shadow: 0 10px 22px rgba(245, 158, 11, .2);
    }

    .aa-photobooth-frame-board {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        gap: clamp(22px, 3vw, 42px);
        width: max-content;
        max-width: none;
        padding: 0 clamp(20px, 4vw, 52px) 54px;
    }

    .aa-photobooth-frame-block {
        min-width: 0;
        gap: 16px;
    }

    .aa-photobooth-frame-title {
        min-height: 40px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(203, 213, 225, .9);
        border-radius: 999px;
        background: rgba(255, 255, 255, .92);
        color: #334155;
        padding: 0 18px;
        font-size: 12px;
        font-weight: 950;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
        cursor: pointer;
        transition: transform .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease;
    }

    .aa-photobooth-frame-controls {
        margin-bottom: 8px;
    }

    .aa-photobooth-frame-block.is-hidden-page .aa-photobooth-frame-wrapper {
        opacity: .46;
    }

    .aa-photobooth-frame-block.is-hidden-page .aa-photobooth-frame-title {
        color: #64748b;
    }

    .aa-photobooth-frame-block.active .aa-photobooth-frame-title,
    .aa-photobooth-frame-title:hover {
        border-color: rgba(15, 118, 110, .34);
        color: #0f766e;
        box-shadow: 0 16px 34px rgba(15, 118, 110, .14);
        transform: translateY(-1px);
    }

    .aa-photobooth-frame-wrapper {
        transform-origin: top center;
    }

    .aa-photobooth-frame-block.active .aa-photobooth-frame-wrapper {
        transform: none;
    }

    .aa-photobooth-preview-frame {
        overflow: hidden;
    }

    .aa-artboard-frame {
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 18px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
    }

    .page-top-controls {
        position: relative;
        z-index: 120;
        width: clamp(390px, 42vw, 590px);
        min-height: 56px;
        border: 1px solid rgba(213, 226, 235, .96);
        border-radius: 999px;
        background: rgba(255, 255, 255, .98);
        padding: 8px 10px 8px 16px;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .1);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        transform: scale(var(--aa-page-control-scale, 1));
        transform-origin: top center;
    }

    .page-title-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0f172a;
        font-size: 14px;
        line-height: 1.15;
    }

    .page-title-button::before {
        content: "Page";
        display: inline-grid;
        min-height: 25px;
        place-items: center;
        border-radius: 999px;
        background: #eefdf9;
        color: #007f6e;
        padding: 0 8px;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .page-title-button span {
        max-width: 310px;
    }

    .page-top-controls .aa-page-actions {
        gap: 7px;
    }

    .page-top-controls .aa-page-action {
        width: 42px;
        height: 42px;
        border: 1px solid #e5edf1;
        border-radius: 999px;
        background: #ffffff;
        color: #334155;
        font-size: 16px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, .05);
        transition: .16s ease;
    }

    .page-top-controls .aa-page-action:hover:not(:disabled) {
        border-color: #a7d8cf;
        background: #effdf9;
        color: #007f6e;
        transform: translateY(-1px);
    }

    .aa-opening-exit-select {
        min-height: 34px;
        max-width: 132px;
        border: 1px solid #dbe7ef;
        border-radius: 999px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 9px;
        font: inherit;
        font-size: 11px;
        font-weight: 900;
        outline: none;
    }

    .page-insert-button {
        min-height: 46px;
        border: 0;
        border-radius: 18px;
        background: #ffffff;
        color: #007f6e;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
    }

    .page-insert-row {
        position: relative;
        z-index: 40;
        width: auto;
        min-height: 50px;
    }

    .page-more-menu {
        z-index: 6000;
        min-width: 180px;
        border-radius: 16px;
        box-shadow: 0 24px 58px rgba(15, 23, 42, .2);
    }

    .page-menu-item {
        min-height: 38px;
        border-radius: 11px;
        padding: 0 10px;
        font-size: 13px;
        font-weight: 800;
    }

    .page-insert-button:hover {
        background: #effdf9;
        color: #007f6e;
    }

    .aa-editor-toast {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1100;
        display: grid;
        width: min(360px, calc(100vw - 32px));
        grid-template-columns: 38px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .66);
        border-radius: 18px;
        background: rgba(15, 23, 42, .94);
        color: #ffffff;
        padding: 13px 14px;
        opacity: 0;
        pointer-events: none;
        transform: translateY(16px);
        transition: opacity .18s ease, transform .18s ease;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .32);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    .aa-editor-toast.is-visible {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    .aa-editor-toast-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 14px;
        background: rgba(20, 184, 166, .18);
        color: #5eead4;
    }

    .aa-editor-toast.is-error .aa-editor-toast-icon {
        background: rgba(244, 63, 94, .18);
        color: #fecdd3;
    }

    .aa-editor-toast.is-saving .aa-editor-toast-icon {
        background: rgba(59, 130, 246, .18);
        color: #bfdbfe;
    }

    .aa-editor-toast-body strong {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-editor-toast-body span {
        display: block;
        margin-top: 2px;
        color: rgba(255, 255, 255, .7);
        font-size: 12px;
        font-weight: 750;
    }

    .aa-editor-toast-close {
        display: grid;
        width: 30px;
        height: 30px;
        place-items: center;
        border: 0;
        border-radius: 10px;
        background: rgba(255, 255, 255, .08);
        color: #ffffff;
        cursor: pointer;
    }

    .aa-action-btn.is-loading,
    .aa-panel-btn.is-loading {
        cursor: wait;
        opacity: .78;
        pointer-events: none;
    }

    .aa-loading-dot {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 999px;
        animation: aaSpin .75s linear infinite;
    }

    .aa-rightbar {
        display: flex;
        flex-direction: column;
        gap: 12px;
        border-left: 0;
        background: transparent;
        padding: 26px 18px !important;
    }

    .aa-rightbar .aa-panel-card {
        min-height: 210px;
        border: 0;
        border-radius: 24px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 22px 58px rgba(15, 23, 42, .10);
    }

    .aa-rightbar #aaSelectionHint {
        display: grid;
        min-height: 40px;
        place-items: center;
        border: 0;
        border-radius: 16px;
        background: #f4f7fa;
        color: #64748b;
        text-align: center;
    }

    .aa-editor-ads-card {
        margin-top: 16px;
        overflow: hidden;
        border-radius: 18px;
        background: #f1f5f9;
        box-shadow: 0 18px 44px rgba(15, 23, 42, .10);
    }

    .aa-editor-ads-dots {
        position: absolute;
        right: 10px;
        bottom: 10px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        background: rgba(15, 23, 42, .46);
        padding: 5px 6px;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .aa-editor-ads-dots button {
        width: 7px;
        height: 7px;
        border: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, .58);
        padding: 0;
        cursor: pointer;
    }

    .aa-editor-ads-dots button.is-active {
        width: 18px;
        background: #ffffff;
    }

    .aa-editor-ads-slider {
        position: relative;
        display: grid;
        overflow: hidden;
        border-radius: inherit;
        background: #f1f5f9;
    }

    .aa-editor-ad-slide {
        grid-area: 1 / 1;
        opacity: 0;
        pointer-events: none;
        transform: scale(1.015);
        transition:
            opacity 1100ms ease,
            transform 2200ms ease;
        visibility: hidden;
    }

    .aa-editor-ad-slide.is-active {
        opacity: 1;
        pointer-events: auto;
        transform: scale(1);
        visibility: visible;
    }

    .aa-editor-ad-slide a,
    .aa-editor-ad-slide img {
        display: block;
        width: 100%;
    }

    .aa-editor-ad-slide img {
        height: auto;
        max-height: 1516px;
        object-fit: cover;
    }

    .aa-context-toolbar,
    .aa-text-context-toolbar,
    .aa-countdown-context-toolbar {
        border: 1px solid #e5edf1;
        border-radius: 18px;
        background:
            radial-gradient(circle at 18% 0%, rgba(45, 212, 191, .18), transparent 30%),
            radial-gradient(circle at 78% 14%, rgba(59, 130, 246, .12), transparent 26%),
            rgba(255, 255, 255, .88);
        box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .aa-context-tool,
    .aa-text-context-tool,
    .aa-text-context-select,
    .aa-text-context-size {
        border-color: #e5edf1;
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
    }

    .aa-context-tool:hover:not(:disabled),
    .aa-text-context-tool:hover:not(:disabled),
    .aa-text-context-tool.is-active {
        background: #eefdf9;
        color: #007f6e;
    }

    .aa-media-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        grid-auto-rows: 4px;
        align-items: stretch;
        gap: 8px;
        overflow: visible;
    }

    .aa-media-grid > :not(.aa-media-item) {
        grid-column: 1 / -1;
    }

    .aa-media-item {
        --aa-media-row-span: 10;
        --aa-media-preview-scale: 1.5;
        position: relative;
        display: block;
        grid-row-end: span var(--aa-media-row-span);
        align-self: stretch;
        width: 100%;
        margin: 0;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background:
            linear-gradient(45deg, rgba(148, 163, 184, .16) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(148, 163, 184, .16) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(148, 163, 184, .16) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(148, 163, 184, .16) 75%),
            #f8fafc;
        background-position: 0 0, 0 6px, 6px -6px, -6px 0;
        background-size: 12px 12px;
    }

    .aa-media-item img {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 0;
        max-height: none;
        object-fit: contain;
        object-position: center;
        transform: scale(var(--aa-media-preview-scale));
        transform-origin: center center;
        transition: transform .18s ease;
    }

    .aa-media-pick {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 0;
        background: transparent;
        padding: 0;
        cursor: pointer;
        text-align: left;
    }

    .aa-media-bulk-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        background: #f8fafc;
        padding: 8px;
    }

    .aa-media-bulk-bar.hidden {
        display: none !important;
    }

    .aa-media-select-all {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #334155;
        font-size: 10px;
        font-weight: 900;
        cursor: pointer;
    }

    .aa-media-select-all input,
    .aa-media-select-input {
        width: 14px;
        height: 14px;
        accent-color: #0f766e;
    }

    .aa-media-bulk-delete {
        display: inline-flex;
        min-height: 28px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid #fecdd3;
        border-radius: 999px;
        background: #fff1f2;
        color: #be123c;
        padding: 0 10px;
        font-size: 10px;
        font-weight: 900;
        cursor: pointer;
    }

    .aa-media-bulk-delete:disabled {
        cursor: not-allowed;
        opacity: .5;
    }

    .aa-media-select {
        position: absolute;
        top: 5px;
        left: 5px;
        z-index: 2;
        display: inline-grid;
        width: 24px;
        height: 24px;
        place-items: center;
        /* border: 1px solid rgba(255, 255, 255, .9); */
        border-radius: 999px;
        background: rgb(255 255 255 / 11%);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .14);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-4px);
        transition: opacity .16s ease, transform .16s ease;
    }

    .aa-media-item.is-selected {
        box-shadow: 0 0 0 2px #0f766e, 0 14px 28px rgba(15, 118, 110, .14);
    }

    .aa-media-item:hover .aa-media-select,
    .aa-media-item:focus-within .aa-media-select,
    .aa-media-item.is-selected .aa-media-select {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    .aa-media-more,
    .aa-media-delete {
        position: absolute;
        top: 5px;
        right: 5px;
        display: inline-grid;
        width: 24px;
        height: 24px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .84);
        border-radius: 999px;
        background: rgba(255, 255, 255, .94);
        color: #334155;
        cursor: pointer;
        font-size: 10px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .18);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-4px);
        transition: opacity .16s ease, transform .16s ease, background .16s ease;
    }

    .aa-media-delete {
        background: rgba(190, 18, 60, .92);
        color: #ffffff;
    }

    .aa-media-menu {
        position: absolute;
        top: 34px;
        right: 5px;
        z-index: 5;
        display: grid;
        min-width: 142px;
        gap: 3px;
        border: 1px solid rgba(226, 232, 240, .95);
        border-radius: 12px;
        background: rgba(255, 255, 255, .98);
        padding: 5px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .2);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-6px) scale(.98);
        transform-origin: top right;
        transition: opacity .16s ease, transform .16s ease;
    }

    .aa-media-item.is-menu-left .aa-media-menu {
        right: auto;
        left: 5px;
        transform-origin: top left;
    }

    .aa-media-item.is-menu-center .aa-media-menu {
        right: auto;
        left: 50%;
        transform: translate(-50%, -6px) scale(.98);
        transform-origin: top center;
    }

    .aa-media-menu button {
        display: flex;
        width: 100%;
        min-height: 30px;
        align-items: center;
        gap: 8px;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #334155;
        padding: 0 8px;
        font-size: 10px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
    }

    .aa-media-menu button:hover {
        background: #f1f5f9;
        color: #0f766e;
    }

    .aa-media-menu [data-aa-media-action="trash"]:hover {
        background: #fff1f2;
        color: #be123c;
    }

    .aa-media-item.is-menu-open {
        overflow: visible;
        z-index: 8;
    }

    .aa-media-item:hover .aa-media-delete,
    .aa-media-item:focus-within .aa-media-delete,
    .aa-media-item:hover .aa-media-more,
    .aa-media-item:focus-within .aa-media-more,
    .aa-media-item.is-menu-open .aa-media-more {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    .aa-media-item.is-menu-open .aa-media-menu {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .aa-media-item.is-menu-open.is-menu-center .aa-media-menu {
        transform: translate(-50%, 0) scale(1);
    }

    .aa-media-delete:hover {
        background: #be123c;
    }

    .aa-media-more:hover,
    .aa-media-item.is-menu-open .aa-media-more {
        background: #eefdf9;
        color: #0f766e;
    }

    @media (hover: none) {
        .aa-media-select,
        .aa-media-delete,
        .aa-media-more {
            opacity: 1;
            pointer-events: auto;
            transform: none;
        }
    }

    .aa-media-upload-state {
        display: none;
        align-items: center;
        gap: 8px;
        min-width: 0;
        max-width: 100%;
        margin-bottom: 10px;
        border: 1px solid #bae6fd;
        border-radius: 12px;
        background: #f0f9ff;
        padding: 9px 10px;
        color: #0369a1;
        font-size: 11px;
        font-weight: 900;
    }

    .aa-media-upload-state span {
        display: block;
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-media-upload-state.is-visible {
        display: flex;
    }

    .aa-media-upload-state.is-error {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .aa-media-upload-state i {
        width: 14px;
        height: 14px;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 999px;
        animation: aa-spin .8s linear infinite;
    }

    .aa-media-upload-state.is-error i {
        display: none;
    }

    .aa-page-actions {
        display: flex;
        gap: 4px;
        margin-left: auto;
    }

    .aa-page-action {
        display: inline-grid;
        width: 60px;
        height: 60px;
        place-items: center;
        border: none;
        border-radius: 8px;
        background: #ffffff;
        color: #64748b;
        cursor: pointer;
        font-size: 28px;
    }

    .aa-page-action:disabled {
        cursor: not-allowed;
        opacity: .42;
    }

    @keyframes aa-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .aa-page-item.is-hidden-page {
        border-style: dashed;
        background: #f1f5f9;
        color: #94a3b8;
    }

    .aa-page-list {
        display: grid;
        gap: 8px;
    }

    .aa-page-item {
        display: flex;
        min-height: 42px;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        padding: 0 11px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: .16s ease;
    }

    .aa-page-item:hover,
    .aa-page-item.is-active {
        border-color: #146cb8;
        background: #ecfdf5;
        color: #0f766e;
    }

    .aa-page-item.is-active {
        background: linear-gradient(135deg, #dbeafe 0%, #ecfdf5 100%);
        border-color: #0ea5e9;
        box-shadow: inset 4px 0 0 #0ea5e9, 0 10px 22px rgba(14, 165, 233, .14);
        color: #075985;
    }

    .aa-panel-btn.is-active {
        border-color: #0ea5e9;
        background: linear-gradient(135deg, #dbeafe 0%, #ecfdf5 100%);
        box-shadow: inset 0 0 0 1px rgba(14, 165, 233, .24), 0 8px 18px rgba(14, 165, 233, .12);
        color: #075985;
    }

    .aa-page-count {
        border-radius: 999px;
        background: #e2e8f0;
        padding: 3px 8px;
        color: #475569;
        font-size: 10px;
        font-weight: 950;
    }

    .aa-modal {
        position: fixed;
        inset: 0;
        z-index: 220;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .62);
        padding: 20px;
    }

    .aa-modal.is-open {
        display: flex;
    }

    .aa-modal-card {
        width: min(960px, 100%);
        min-height: 0vh;
        overflow: auto;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 32px 90px rgba(15, 23, 42, .28);
        transform: scale(.85);
    }

    body.aa-editor-mobile-mode .aa-modal-card {
        transform: scale(0.65);
    }

    .aa-publish-modal {
        padding: 18px;
        overscroll-behavior: contain;
    }

    .aa-publish-modal-card {
        width: min(1040px, calc(100vw - 32px));
        max-height: min(820px, calc(100vh - 32px));
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 30px;
        background:
            radial-gradient(circle at 12% 8%, rgba(124, 58, 237, .10), transparent 28%),
            radial-gradient(circle at 92% 72%, rgba(245, 158, 11, .10), transparent 24%),
            #ffffff;
        box-shadow: 0 32px 96px rgba(15, 23, 42, .30);
        transform: scale(.9);
    }

    .aa-publish-choice-view {
        position: relative;
        display: grid;
        gap: 28px;
        padding: 46px 64px 42px;
    }

    .aa-publish-choice-view.hidden,
    .aa-publish-detail-view.hidden {
        display: none;
    }

    .aa-publish-detail-view {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
    }

    .aa-publish-choice-view .aa-publish-close {
        position: absolute;
        top: 28px;
        right: 32px;
    }

    .aa-publish-choice-hero {
        display: grid;
        justify-items: center;
        gap: 12px;
        padding-top: 10px;
        text-align: center;
    }

    .aa-publish-choice-icon {
        width: 108px;
        height: 108px;
        display: inline-grid;
        place-items: center;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(124, 58, 237, .18), rgba(124, 58, 237, .07));
        color: #6d28d9;
        font-size: 46px;
    }

    .aa-publish-choice-hero h2 {
        margin: 0;
        color: #0f172a;
        font-size: 40px;
        font-weight: 950;
        line-height: 1.05;
    }

    .aa-publish-choice-hero p {
        margin: 0;
        color: #64748b;
        font-size: 20px;
        font-weight: 800;
    }

    .aa-publish-choice-list {
        display: grid;
        gap: 18px;
    }

    .aa-publish-choice-card {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 26px;
        min-height: 128px;
        border: 1px solid rgba(124, 58, 237, .34);
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(124, 58, 237, .045), rgba(255, 255, 255, .94));
        color: #0f172a;
        padding: 24px 28px;
        text-align: left;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .aa-publish-choice-card:hover {
        border-color: rgba(109, 40, 217, .62);
        box-shadow: 0 18px 38px rgba(124, 58, 237, .12);
        transform: translateY(-1px);
    }

    .aa-publish-choice-card.is-photobooth {
        border-color: rgba(59, 130, 246, .38);
        background: linear-gradient(135deg, rgba(59, 130, 246, .045), rgba(255, 255, 255, .94));
    }

    .aa-publish-choice-card.is-premium-locked {
        border-color: rgba(245, 158, 11, .48);
        background: linear-gradient(135deg, rgba(245, 158, 11, .11), rgba(255, 255, 255, .96));
    }

    .aa-publish-choice-card.is-premium-locked:hover {
        border-color: rgba(217, 119, 6, .7);
        box-shadow: 0 18px 38px rgba(245, 158, 11, .15);
    }

    .aa-publish-choice-card.is-disabled {
        border-color: rgba(203, 213, 225, .85);
        background: rgba(248, 250, 252, .86);
        color: #64748b;
        cursor: not-allowed;
        opacity: .78;
    }

    .aa-publish-choice-card.is-disabled:hover {
        box-shadow: none;
        transform: none;
    }

    .aa-publish-choice-card-icon {
        width: 88px;
        height: 88px;
        display: inline-grid;
        place-items: center;
        border-radius: 18px;
        color: #6d28d9;
        font-size: 38px;
    }

    .aa-publish-choice-card-icon.is-invitation {
        background: rgba(124, 58, 237, .11);
    }

    .aa-publish-choice-card-icon.is-photobooth {
        background: rgba(59, 130, 246, .11);
        color: #2563eb;
    }

    .aa-publish-choice-card-icon.is-disabled {
        background: rgba(148, 163, 184, .14);
        color: #64748b;
    }

    .aa-publish-choice-copy {
        display: grid;
        gap: 8px;
    }

    .aa-publish-choice-copy strong {
        color: #0f172a;
        font-size: 25px;
        font-weight: 950;
        line-height: 1.1;
    }

    .aa-publish-choice-copy span {
        color: #64748b;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.35;
    }

    .aa-publish-choice-copy em {
        display: inline-flex;
        align-items: center;
        margin-left: 8px;
        border-radius: 999px;
        background: #f1f5f9;
        padding: 4px 10px;
        color: #475569;
        font-size: 14px;
        font-style: normal;
        font-weight: 950;
        vertical-align: middle;
    }

    .aa-publish-choice-card > .fa-chevron-right {
        color: #6d28d9;
        font-size: 26px;
    }

    .aa-publish-choice-card.is-photobooth > .fa-chevron-right {
        color: #2563eb;
    }

    .aa-publish-choice-card.is-premium-locked > .fa-crown {
        color: #d97706;
        font-size: 24px;
    }

    .aa-publish-choice-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin: 0;
        color: #64748b;
        font-size: 17px;
        font-weight: 850;
        text-align: center;
    }

    .aa-publish-choice-note i {
        color: #6d28d9;
    }

    body.aa-editor-mobile-mode .aa-publish-modal-card {
        width: min(560px, calc(100vw - 18px));
        max-height: calc(100vh - 18px);
        border-radius: 24px;
        transform: scale(.88);
    }

    body.aa-editor-mobile-mode .aa-publish-choice-view {
        gap: 18px;
        padding: 28px 18px 24px;
    }

    body.aa-editor-mobile-mode .aa-publish-choice-view .aa-publish-close {
        top: 16px;
        right: 16px;
    }

    body.aa-editor-mobile-mode .aa-publish-choice-icon {
        width: 74px;
        height: 74px;
        font-size: 30px;
    }

    body.aa-editor-mobile-mode .aa-publish-choice-hero h2 {
        font-size: 27px;
    }

    body.aa-editor-mobile-mode .aa-publish-choice-hero p {
        font-size: 15px;
    }

    body.aa-editor-mobile-mode .aa-publish-choice-card {
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 14px;
        min-height: 92px;
        padding: 16px;
    }

    body.aa-editor-mobile-mode .aa-publish-choice-card-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        font-size: 24px;
    }

    body.aa-editor-mobile-mode .aa-publish-choice-copy strong {
        font-size: 18px;
    }

    body.aa-editor-mobile-mode .aa-publish-choice-copy span,
    body.aa-editor-mobile-mode .aa-publish-choice-note {
        font-size: 13px;
    }

    .aa-publish-modal-head,
    .aa-publish-modal-title,
    .aa-publish-actions,
    .aa-publish-main-actions {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .aa-publish-modal-head,
    .aa-publish-actions {
        justify-content: space-between;
    }

    .aa-publish-back-btn {
        width: 42px;
        height: 42px;
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 999px;
        background: #ffffff;
        color: #334155;
        cursor: pointer;
        font-size: 15px;
        transition: border-color .18s ease, color .18s ease, transform .18s ease;
    }

    .aa-publish-back-btn:hover {
        border-color: rgba(124, 58, 237, .45);
        color: #6d28d9;
        transform: translateX(-1px);
    }

    .aa-publish-modal-head {
        flex: 0 0 auto;
        padding: 28px 34px 18px;
        border-bottom: 1px solid rgba(226, 232, 240, .78);
        background: rgba(255, 255, 255, .76);
        backdrop-filter: blur(10px);
    }

    .aa-publish-modal-scroll {
        display: grid;
        gap: 18px;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 20px 34px 24px;
        scrollbar-color: rgba(100, 116, 139, .45) transparent;
        scrollbar-width: thin;
    }

    .aa-publish-modal-scroll::-webkit-scrollbar {
        width: 9px;
    }

    .aa-publish-modal-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .aa-publish-modal-scroll::-webkit-scrollbar-thumb {
        border: 2px solid transparent;
        border-radius: 999px;
        background: rgba(100, 116, 139, .42);
        background-clip: padding-box;
    }

    body.aa-editor-mobile-mode .aa-publish-modal-head {
        padding: 18px 18px 14px;
    }

    body.aa-editor-mobile-mode .aa-publish-modal-scroll {
        padding: 16px 18px 18px;
    }

    .aa-photobooth-domain-modal {
        padding: 18px;
        overscroll-behavior: contain;
    }

    .aa-photobooth-domain-card {
        width: min(1180px, calc(100vw - 32px));
        max-height: min(820px, calc(100vh - 32px));
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 26px;
        background:
            radial-gradient(circle at 8% 8%, rgba(16, 185, 129, .10), transparent 28%),
            radial-gradient(circle at 92% 74%, rgba(124, 58, 237, .10), transparent 24%),
            #ffffff;
        box-shadow: 0 32px 96px rgba(15, 23, 42, .30);
        transform: scale(.94);
    }

    .aa-photobooth-domain-head {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 0 0 auto;
        padding: 24px 72px 18px;
        border-bottom: 1px solid rgba(226, 232, 240, .82);
        background: rgba(255, 255, 255, .78);
        backdrop-filter: blur(10px);
    }

    .aa-photobooth-domain-head .aa-publish-back-btn,
    .aa-photobooth-domain-head .aa-publish-close {
        position: absolute;
        top: 26px;
    }

    .aa-photobooth-domain-head .aa-publish-back-btn {
        left: 28px;
    }

    .aa-photobooth-domain-head .aa-publish-close {
        right: 28px;
    }

    .aa-photobooth-domain-title {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 0;
        flex: 1 1 auto;
        gap: 14px;
        text-align: left;
    }

    .aa-photobooth-domain-icon {
        width: 54px;
        height: 54px;
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(16, 185, 129, .18), rgba(16, 185, 129, .08));
        color: #047857;
        font-size: 22px;
    }

    .aa-photobooth-domain-title p {
        margin: 0;
        color: #047857;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .aa-photobooth-domain-title small {
        display: block;
        margin-top: 7px;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.45;
    }

    .aa-photobooth-domain-title h2 {
        margin: 0;
        overflow: hidden;
        color: #0f172a;
        font-size: 25px;
        font-weight: 950;
        line-height: 1.1;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-photobooth-domain-form {
        display: grid;
        gap: 20px;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 18px 34px 28px;
        scrollbar-color: rgba(100, 116, 139, .45) transparent;
        scrollbar-width: thin;
    }

    .aa-photobooth-domain-form::-webkit-scrollbar {
        width: 9px;
    }

    .aa-photobooth-domain-form::-webkit-scrollbar-track {
        background: transparent;
    }

    .aa-photobooth-domain-form::-webkit-scrollbar-thumb {
        border: 2px solid transparent;
        border-radius: 999px;
        background: rgba(100, 116, 139, .42);
        background-clip: padding-box;
    }

    .aa-photobooth-domain-options,
    .aa-photobooth-link-grid,
    .aa-photobooth-domain-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .aa-photobooth-domain-option {
        display: grid;
        gap: 24px;
        min-height: 300px;
        align-content: start;
        border: 1px solid rgba(226, 232, 240, .95);
        border-radius: 22px;
        background: rgba(248, 250, 252, .84);
        padding: 34px;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .aa-photobooth-domain-option:hover {
        border-color: rgba(16, 185, 129, .45);
        box-shadow: 0 14px 30px rgba(15, 118, 110, .10);
        transform: translateY(-1px);
    }

    .aa-photobooth-domain-option:has(input:checked) {
        border-color: rgba(16, 185, 129, .58);
        background: linear-gradient(135deg, rgba(236, 253, 245, .82), rgba(255, 255, 255, .94));
        box-shadow: 0 20px 42px rgba(15, 118, 110, .10);
    }

    .aa-photobooth-domain-option.is-custom {
        background: rgba(255, 255, 255, .86);
    }

    .aa-photobooth-domain-choice {
        display: flex;
        align-items: flex-start;
        gap: 18px;
    }

    .aa-photobooth-domain-choice input {
        width: 22px;
        height: 22px;
        margin-top: 3px;
        accent-color: #0f766e;
        flex: 0 0 auto;
    }

    .aa-photobooth-option-icon {
        width: 72px;
        height: 72px;
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 999px;
        font-size: 30px;
    }

    .aa-photobooth-option-icon.is-standard {
        background: rgba(209, 250, 229, .86);
        color: #059669;
    }

    .aa-photobooth-option-icon.is-custom {
        background: rgba(237, 233, 254, .92);
        color: #7c3aed;
    }

    .aa-photobooth-domain-choice strong {
        display: block;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
        line-height: 1.2;
    }

    .aa-photobooth-domain-choice small {
        display: block;
        margin-top: 10px;
        color: #64748b;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.6;
    }

    .aa-photobooth-option-link-label {
        align-self: end;
        color: #047857;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-photobooth-standard-url {
        display: flex;
        align-items: center;
        gap: 14px;
        overflow-wrap: anywhere;
        min-height: 54px;
        border-radius: 15px;
        background: #ffffff;
        padding: 14px 16px;
        color: #0f172a;
        font-size: 14px;
        font-weight: 950;
        line-height: 1.55;
        box-shadow: inset 0 0 0 1px rgba(226, 232, 240, .95);
    }

    .aa-photobooth-standard-url i {
        width: 24px;
        height: 24px;
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 999px;
        background: transparent;
        color: #059669;
        font-size: 16px;
    }

    .aa-photobooth-secure-pill {
        justify-self: center;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 12px;
        background: rgba(209, 250, 229, .82);
        padding: 9px 14px;
        color: #047857;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-photobooth-status-panel {
        position: relative;
        border: 1px solid rgba(221, 214, 254, .90);
        border-radius: 18px;
        background: rgba(245, 243, 255, .72);
        padding: 26px;
    }

    .aa-photobooth-status-head {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        gap: 14px;
    }

    .aa-photobooth-status-icon {
        width: 58px;
        height: 58px;
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 999px;
        background: rgba(237, 233, 254, .92);
        color: #7c3aed;
        font-size: 24px;
    }

    .aa-photobooth-status-label {
        margin: 0;
        color: #6d28d9;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .aa-photobooth-status-pill,
    .aa-photobooth-price-pill {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        border-radius: 999px;
        background: #ffffff;
        padding: 5px 13px;
        color: #5b21b6;
        font-size: 12px;
        font-weight: 950;
        box-shadow: inset 0 0 0 1px rgba(221, 214, 254, .95);
    }

    .aa-photobooth-status-pill {
        margin: 8px 0 0;
    }

    .aa-photobooth-price-pill {
        flex: 0 0 auto;
        margin-left: auto;
    }

    .aa-photobooth-domain-value {
        margin: 10px 0 0;
        overflow-wrap: anywhere;
        color: #0f172a;
        font-size: 22px;
        font-weight: 950;
        line-height: 1.1;
    }

    .aa-photobooth-status-note,
    .aa-photobooth-domain-message {
        margin: 12px 0 0;
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.65;
    }

    .aa-photobooth-domain-message {
        margin-top: 0;
        min-height: 20px;
    }

    .aa-photobooth-ready-link-card {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        border: 1px solid rgba(16, 185, 129, .20);
        border-radius: 18px;
        background: rgba(236, 253, 245, .70);
        padding: 18px;
    }

    .aa-photobooth-ready-icon {
        width: 52px;
        height: 52px;
        display: inline-grid;
        place-items: center;
        border-radius: 999px;
        background: rgba(209, 250, 229, .88);
        color: #059669;
        font-size: 20px;
    }

    .aa-photobooth-ready-copy {
        display: grid;
        min-width: 0;
        gap: 8px;
    }

    .aa-photobooth-ready-copy strong {
        color: #059669;
        font-size: 14px;
        font-weight: 950;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .aa-photobooth-ready-url {
        width: 100%;
        min-width: 0;
        border: 0;
        background: transparent;
        color: #0f172a;
        font-size: 14px;
        font-weight: 950;
        outline: 0;
    }

    .aa-photobooth-ready-status {
        color: #047857;
        font-size: 12px;
        font-weight: 850;
        line-height: 1.45;
    }

    .aa-photobooth-ready-status.is-pending {
        color: #b45309;
    }

    .aa-publish-inline-copy.is-green {
        background: rgba(209, 250, 229, .86);
        color: #059669;
    }

    .aa-photobooth-domain-actions .aa-primary {
        border: 0;
        background: linear-gradient(135deg, #059669, #0f766e);
        color: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 118, 110, .24);
    }

    .aa-photobooth-footnote {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 0;
        color: #94a3b8;
        font-size: 13px;
        font-weight: 850;
        text-align: center;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-card {
        width: min(560px, calc(100vw - 18px));
        max-height: calc(100vh - 18px);
        border-radius: 24px;
        transform: scale(.88);
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-head {
        padding: 18px 60px 14px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-title h2 {
        font-size: 18px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-title small {
        font-size: 12px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-form {
        padding: 16px 18px 18px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-options,
    body.aa-editor-mobile-mode .aa-photobooth-link-grid,
    body.aa-editor-mobile-mode .aa-photobooth-domain-actions {
        grid-template-columns: 1fr;
    }

    body.aa-editor-mobile-mode .aa-photobooth-status-head {
        flex-direction: column;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-option {
        min-height: 0;
        padding: 18px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-option-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-choice strong {
        font-size: 15px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-domain-choice small,
    body.aa-editor-mobile-mode .aa-photobooth-standard-url {
        font-size: 12px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-status-panel {
        padding: 18px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-status-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }

    body.aa-editor-mobile-mode .aa-photobooth-ready-link-card {
        grid-template-columns: auto minmax(0, 1fr);
    }

    body.aa-editor-mobile-mode .aa-photobooth-ready-link-card .aa-publish-inline-copy {
        grid-column: 1 / -1;
        width: 100%;
    }

    .aa-publish-modal-icon {
        width: 62px;
        height: 62px;
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(124, 58, 237, .18), rgba(124, 58, 237, .08));
        color: #6d28d9;
        font-size: 24px;
    }

    .aa-publish-modal-title h2 {
        margin: 0;
        color: #0f172a;
        font-size: 26px;
        font-weight: 950;
        line-height: 1.08;
    }

    .aa-publish-modal-title p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
        font-weight: 800;
    }

    .aa-publish-close {
        width: 48px;
        height: 48px;
        display: inline-grid;
        place-items: center;
        border: 0;
        border-radius: 16px;
        background: #f1f5f9;
        color: #0f172a;
        cursor: pointer;
        font-size: 22px;
        font-weight: 950;
    }

    .aa-publish-modal-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 20px;
    }

    .aa-publish-section {
        display: grid;
        gap: 16px;
        align-content: start;
        border: 1px solid rgba(226, 232, 240, .96);
        border-radius: 18px;
        padding: 22px;
        background: rgba(255, 255, 255, .82);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .9);
    }

    .aa-publish-field {
        display: grid;
        gap: 9px;
        color: #1e293b;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-publish-help,
    .aa-publish-domain-status,
    .aa-publish-domain-preview {
        margin: 0;
        font-size: 12px;
        font-weight: 850;
        line-height: 1.55;
    }

    .aa-publish-help,
    .aa-publish-domain-preview {
        color: #64748b;
        word-break: break-word;
    }

    .aa-publish-domain-status.is-active {
        color: #047857;
    }

    .aa-publish-domain-status.is-pending {
        color: #b45309;
    }

    .aa-publish-slug-row,
    .aa-publish-link-row {
        display: grid;
        align-items: center;
        gap: 0;
    }

    .aa-publish-slug-row {
        grid-template-columns: minmax(0, 1fr) auto minmax(148px, auto);
    }

    .aa-publish-slug-row .aa-field {
        border-radius: 16px 0 0 16px;
    }

    .aa-publish-dot,
    .aa-publish-root-label {
        height: 46px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(203, 213, 225, .88);
        border-left: 0;
        background: #ffffff;
        color: #0f172a;
        font-size: 14px;
        font-weight: 950;
    }

    .aa-publish-dot {
        padding: 0 10px;
        color: #94a3b8;
    }

    .aa-publish-root-label {
        justify-content: center;
        border-radius: 0 16px 16px 0;
        padding: 0 14px;
    }

    .aa-publish-domain-box,
    .aa-publish-link-card {
        display: grid;
        gap: 12px;
    }

    .aa-publish-subdomain-card {
        border-top: 1px solid rgba(226, 232, 240, .78);
        padding-top: 14px;
    }

    .aa-publish-box-title {
        margin: 0;
        color: #1e293b;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-publish-domain-options {
        display: grid;
        gap: 10px;
    }

    .aa-publish-domain-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 58px;
        border: 1px solid rgba(124, 58, 237, .30);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(124, 58, 237, .08), rgba(255, 255, 255, .95));
        padding: 12px 14px;
        cursor: pointer;
    }

    .aa-publish-domain-option.is-disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    .aa-publish-radio-wrap {
        display: flex;
        align-items: center;
        min-width: 0;
        gap: 12px;
    }

    .aa-publish-domain-globe {
        width: 28px;
        height: 28px;
        display: inline-grid;
        place-items: center;
        border-radius: 999px;
        background: rgba(124, 58, 237, .12);
        color: #6d28d9;
        font-size: 12px;
    }

    .aa-publish-free-pill,
    .aa-publish-premium-pill {
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    .aa-publish-free-pill {
        background: #dcfce7;
        color: #047857;
    }

    .aa-publish-premium-pill {
        background: #fef3c7;
        color: #b45309;
    }

    .aa-publish-domain-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border: 1px solid rgba(16, 185, 129, .22);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(236, 253, 245, .95), rgba(240, 253, 250, .82));
        padding: 12px 14px;
        color: #047857;
        font-size: 13px;
        font-weight: 850;
        line-height: 1.55;
    }

    .aa-publish-link-row {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .aa-publish-link-row .aa-field {
        border-radius: 16px 0 0 16px;
        font-weight: 950;
        min-width: 0;
    }

    .aa-publish-inline-copy {
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid rgba(203, 213, 225, .88);
        border-left: 0;
        border-radius: 0 16px 16px 0;
        background: rgba(124, 58, 237, .08);
        color: #6d28d9;
        padding: 0 14px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-publish-og-section .aa-og-preview-field {
        background: #ffffff;
    }

    .aa-publish-wa-copy {
        display: grid;
        gap: 8px;
        min-width: 0;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.55;
    }

    .aa-publish-wa-copy strong {
        color: #0f172a;
        font-size: 15px;
        font-weight: 950;
    }

    .aa-publish-wa-copy span {
        color: #6d28d9;
        font-weight: 950;
        word-break: break-word;
    }

    .aa-publish-wa-copy p {
        margin: 0;
    }

    .aa-publish-tip {
        border-color: rgba(16, 185, 129, .25);
    }

    .aa-publish-actions {
        border-top: 1px solid rgba(226, 232, 240, .95);
        padding-top: 18px;
    }

    .aa-publish-main-actions {
        flex-wrap: wrap;
    }

    .aa-publish-main-actions .aa-panel-btn {
        min-width: 160px;
    }

    .aa-publish-more-actions {
        display: grid;
        gap: 14px;
    }

    .aa-publish-more-actions summary {
        width: max-content;
        cursor: pointer;
        color: #64748b;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-publish-extra-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .aa-publish-extra-grid .aa-panel-btn {
        min-height: 48px;
    }

    .aa-publish-footnote {
        margin: 0;
        color: #64748b;
        text-align: center;
        font-size: 13px;
        font-weight: 850;
    }

    @media (max-width: 900px) {
        .aa-publish-modal-grid,
        .aa-publish-extra-grid {
            grid-template-columns: 1fr;
        }

        .aa-publish-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .aa-publish-main-actions {
            display: grid;
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 520px) {
        .aa-publish-link-row {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .aa-publish-link-row .aa-field,
        .aa-publish-inline-copy {
            border: 1px solid rgba(203, 213, 225, .88);
            border-radius: 16px;
        }
    }

    .aa-og-preview-field {
        display: grid;
        gap: 12px;
        border: 1px solid rgba(148, 163, 184, .42);
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(248, 250, 252, .96), rgba(240, 253, 250, .78));
        padding: 14px;
    }

    .aa-og-preview-copy {
        display: grid;
        gap: 3px;
    }

    .aa-og-preview-box {
        display: grid;
        grid-template-columns: minmax(116px, 168px) 1fr;
        gap: 12px;
        align-items: center;
    }

    .aa-og-preview-thumb {
        position: relative;
        display: grid;
        min-height: 96px;
        place-items: center;
        overflow: hidden;
        border: 1px dashed rgba(15, 118, 110, .34);
        border-radius: 14px;
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .08), rgba(200, 135, 45, .12)),
            #ffffff;
        color: #0f766e;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-og-preview-thumb img {
        width: 100%;
        height: auto;
        min-height: inherit;
        max-height: 220px;
        object-fit: contain;
        background: #ffffff;
    }

    .aa-og-preview-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .aa-og-upload-btn {
        cursor: pointer;
    }

    @media (max-width: 560px) {
        .aa-og-preview-box {
            grid-template-columns: 1fr;
        }
    }

	    .aa-preview-frame {
	        width: min(420px, 100%);
	        aspect-ratio: 9 / 16;
	        border: 0;
	        border-radius: 22px;
	        background: #ffffff;
	        box-shadow: 0 22px 70px rgba(15, 23, 42, .22);
	    }

	    .aa-desktop-only-modal {
	        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        place-items: center;
        padding: 22px;
        background: linear-gradient(135deg, rgba(15, 23, 42, .94), rgba(17, 24, 39, .9));
        color: #0f172a;
    }

    .aa-desktop-only-modal.is-visible {
        display: grid;
    }

    .aa-desktop-only-card {
        width: min(420px, 100%);
        border: 1px solid rgba(255, 255, 255, .56);
        border-radius: 24px;
        background: #ffffff;
        padding: 24px;
        text-align: center;
        box-shadow: 0 30px 90px rgba(0, 0, 0, .32);
        font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .aa-desktop-only-icon {
        display: inline-grid;
        width: 54px;
        height: 54px;
        place-items: center;
        margin-bottom: 14px;
        border-radius: 18px;
        background: #ecfdf5;
        color: #0f766e;
        font-size: 24px;
    }

    .aa-desktop-only-card h2 {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 22px;
        font-weight: 950;
        line-height: 1.2;
    }

    .aa-desktop-only-card p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.7;
    }

    body.aa-editor-device-blocked .aa-studio-shell,
    body.aa-editor-orientation-blocked .aa-studio-shell {
        pointer-events: none;
        user-select: none;
        filter: blur(6px);
    }

    .aa-tablet-landscape-modal {
        background:
            radial-gradient(circle at 50% 20%, rgba(20, 184, 166, .22), transparent 34%),
            linear-gradient(135deg, rgba(15, 23, 42, .9), rgba(17, 24, 39, .86));
    }

    @media (max-width: 1180px) {
        .aa-workspace {
            grid-template-columns: 238px minmax(0, 1fr);
        }

        .aa-rightbar {
            display: none;
        }
    }

    @media (min-width: 768px) and (max-width: 1180px) {
        .aa-studio-shell {
            grid-template-rows: auto minmax(0, 1fr);
        }

        .aa-topbar {
            flex-wrap: wrap;
            align-content: center;
            min-height: 112px;
            gap: 10px 14px;
            padding: 10px 14px 12px;
        }

        .aa-topbar-brand {
            min-width: 0;
            flex: 1 1 220px;
        }

        .aa-topbar-brand h1 {
            max-width: 42vw;
        }

        .aa-topbar-controls {
            order: 3;
            flex: 1 0 100%;
            justify-content: center !important;
            overflow-x: visible;
            overflow-y: visible;
            padding-bottom: 2px;
        }

        .aa-topbar-actions {
            flex: 0 0 auto;
        }

        .aa-topbar .aa-action-btn {
            min-height: 40px;
            white-space: nowrap;
        }

        .aa-topbar .aa-editor-status-pill {
            flex: 0 0 auto;
            min-width: 104px;
        }

        .aa-workspace {
            grid-template-columns: 84px minmax(0, 1fr);
        }

        .aa-left-rail {
            padding: 14px 8px;
            gap: 8px;
        }

        .aa-left-rail-link,
        .aa-left-rail-tab {
            min-height: 62px;
            border-radius: 16px;
            font-size: 10px;
        }

        .aa-left-rail-link i,
        .aa-left-rail-tab i {
            font-size: 19px;
        }

        .aa-left-drawer {
            left: 84px;
            width: min(390px, calc(100vw - 104px));
            max-width: calc(100vw - 104px);
            box-shadow: 18px 0 50px rgba(15, 23, 42, .14);
        }

        .aa-stage-wrap {
            padding: 32px 28px 126px;
        }

        .aa-editor-mode-strip {
            margin-bottom: 24px;
        }

        .page-top-controls {
            width: min(590px, calc(100vw - 150px));
        }

        .aa-rightbar {
            display: none;
        }

        .aa-rightbar .aa-panel-card {
            min-height: 0;
            border-radius: 18px;
            box-shadow: none;
        }

        .aa-context-toolbar,
        .aa-text-context-toolbar,
        .aa-countdown-context-toolbar {
            max-width: calc(100vw - 120px);
            overflow-x: auto;
            justify-content: flex-start;
            scrollbar-width: thin;
            transform: scale(0.75);
            left: 25%;
            top: 55px;
        }

        .aa-object-floating-tool,
        .aa-context-tool,
        .aa-text-context-tool {
            min-width: 42px;
            height: 40px;
        }
    }

    body.aa-editor-tablet-mode .aa-studio-shell {
        grid-template-rows: auto minmax(0, 1fr);
    }

    body.aa-editor-tablet-mode .aa-topbar {
        flex-wrap: nowrap;
        align-content: center;
        min-height: 55px;
        gap: 8px;
        padding: 6px 10px;
    }

    body.aa-editor-tablet-mode .aa-topbar-brand {
        min-width: 0;
        flex: 0 1 190px;
    }

    body.aa-editor-tablet-mode .aa-editor-brand-card {
        min-width: 190px;
        gap: 9px;
        border-radius: 18px;
        padding: 9px 10px;
    }

    body.aa-editor-tablet-mode .aa-editor-brand-logo {
        min-width: 80px;
    }

    body.aa-editor-tablet-mode .aa-editor-brand-logo strong {
        font-size: 10px;
        letter-spacing: .18em;
    }

    body.aa-editor-tablet-mode .aa-editor-brand-logo span {
        font-size: 6px;
        letter-spacing: .24em;
    }

    body.aa-editor-tablet-mode .aa-editor-brand-divider {
        height: 22px;
    }

    body.aa-editor-tablet-mode .aa-topbar-brand h1 {
        max-width: 180px;
        font-size: 12px;
        line-height: 1.1;
    }

    body.aa-editor-tablet-mode .aa-topbar-brand p {
        font-size: 9px;
        line-height: 1.1;
        letter-spacing: .18em;
    }

    body.aa-editor-tablet-mode .aa-topbar-controls {
        order: 0;
        flex: 1 1 auto;
        justify-content: flex-end !important;
        gap: 6px !important;
        overflow: visible;
        padding-bottom: 0;
    }

    body.aa-editor-tablet-mode .aa-topbar-group,
    body.aa-editor-tablet-mode .aa-topbar-size-controls {
        min-height: 34px;
        gap: 3px;
        padding: 2px;
    }

    body.aa-editor-tablet-mode .aa-topbar .aa-action-btn {
        min-height: 32px;
        border-radius: 12px;
        padding-inline: 9px;
        font-size: 11px;
    }

    body.aa-editor-tablet-mode .aa-topbar-history .aa-topbar-action-text {
        display: none;
    }

    body.aa-editor-tablet-mode .aa-topbar-history .aa-action-btn,
    body.aa-editor-tablet-mode #aaZoomOutBtn,
    body.aa-editor-tablet-mode #aaZoomInBtn {
        width: 32px;
        min-width: 32px;
        padding-inline: 0;
    }

    body.aa-editor-tablet-mode #aaFitBtn {
        display: none !important;
    }

    body.aa-editor-tablet-mode .aa-topbar-actions {
        display: none !important;
    }

    body.aa-editor-tablet-mode .aa-tablet-action-dock {
        position: fixed;
        right: 0px;
        bottom: 12px;
        z-index: 170;
        display: flex;
        transform: scale(0.9);
        flex: 0 0 auto;
        flex-wrap: nowrap;
        justify-content: flex-end;
        width: auto;
        max-width: calc(100vw - 96px);
        gap: 6px;
        border: 1px solid rgba(213, 226, 235, .96);
        border-radius: 20px;
        background: rgba(255, 255, 255, .94);
        padding: 8px;
        box-shadow: 0 18px 54px rgba(15, 23, 42, .16);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        contain: layout paint;
        transition: none;
    }

    body.aa-editor-tablet-mode .aa-tablet-action-dock .aa-action-btn {
        flex: 0 0 auto;
        box-sizing: border-box;
        min-height: 34px;
        border-radius: 13px;
        padding-inline: 12px;
        font-size: 11px;
        white-space: nowrap;
        transition: background .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease;
    }

    body.aa-editor-tablet-mode .aa-topbar .aa-editor-status-pill {
        flex: 0 0 auto;
        min-width: 84px;
        min-height: 30px;
        padding-inline: 9px;
        font-size: 9px;
    }

    body.aa-editor-tablet-mode .aa-workspace {
        grid-template-columns: 70px minmax(0, 1fr);
    }

    body.aa-editor-tablet-mode .aa-left-rail {
        padding: 10px 6px;
        gap: 6px;
    }

    body.aa-editor-tablet-mode .aa-left-rail-link,
    body.aa-editor-tablet-mode .aa-left-rail-tab {
        min-height: 54px;
        border-radius: 14px;
        font-size: 9px;
    }

    body.aa-editor-tablet-mode .aa-left-rail-link i,
    body.aa-editor-tablet-mode .aa-left-rail-tab i {
        font-size: 17px;
    }

    body.aa-editor-tablet-mode .aa-left-drawer {
        left: 70px;
        width: min(300px, calc(100vw - 90px));
        max-width: calc(100vw - 90px);
        box-shadow: 14px 0 38px rgba(15, 23, 42, .12);
    }

    body.aa-editor-tablet-mode .aa-stage-wrap {
        padding: 22px 18px 96px;
    }

    body.aa-editor-tablet-mode .aa-editor-mode-strip {
        margin-bottom: 55px;
    }

    body.aa-editor-tablet-mode .aa-editor-mode-toggle {
        padding: 3px;
        transform: scale(2.2);
    }

    body.aa-editor-tablet-mode .aa-editor-mode-btn {
        min-height: 34px;
        padding-inline: 10px;
        font-size: 10px;
    }

    body.aa-editor-tablet-mode .page-top-controls {
        width: min(480px, calc(100vw - 120px));
        min-height: 44px;
        padding: 6px 8px 6px 12px;
        transform: scale(2.2);
        transform-origin: top center;
    }

    body.aa-editor-tablet-mode .page-top-controls .page-title-button {
        font-size: 12px;
    }

    body.aa-editor-tablet-mode .page-top-controls .aa-page-action {
        width: 28px;
        height: 28px;
        min-width: 28px;
    }

    body.aa-editor-tablet-mode .aa-left-drawer .aa-panel-card {
        border-radius: 16px;
        padding: 14px;
    }

    body.aa-editor-tablet-mode .aa-left-drawer .aa-panel-title {
        font-size: 13px;
    }

    body.aa-editor-tablet-mode .aa-left-drawer .aa-panel-btn,
    body.aa-editor-tablet-mode .aa-left-drawer .aa-tool-btn {
        min-height: 42px;
        border-radius: 14px;
        font-size: 12px;
    }

    body.aa-editor-tablet-mode .aa-left-drawer input,
    body.aa-editor-tablet-mode .aa-left-drawer textarea,
    body.aa-editor-tablet-mode .aa-left-drawer select {
        font-size: 12px;
    }

    body.aa-editor-tablet-mode .aa-left-drawer textarea {
        min-height: 92px;
    }

    body.aa-editor-tablet-mode .aa-left-drawer h2,
    body.aa-editor-tablet-mode .aa-left-drawer h3 {
        font-size: clamp(16px, 2.2vw, 16px);
        line-height: 1.05;
    }

    body.aa-editor-tablet-mode .aa-rightbar {
        display: none;
    }

    body.aa-editor-tablet-mode .aa-object-floating-tool,
    body.aa-editor-tablet-mode .aa-context-tool,
    body.aa-editor-tablet-mode .aa-text-context-tool {
        min-width: 42px;
        height: 40px;
    }

	    .aa-mobile-tools-btn,
	    .aa-mobile-save-btn,
	    .aa-mobile-share-btn,
	    .aa-mobile-text-edit-btn,
	    .aa-mobile-properties-bar {
        display: none !important;
    }

    @media (max-width: 767px) {
        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        body:not(.aa-editor-mobile-mode) .aa-left-rail,
        body:not(.aa-editor-mobile-mode) .aa-left-drawer {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        body.aa-editor-mobile-mode {
            background: #e5e7eb;
        }

        #aaDesktopOnlyModal {
            display: none !important;
        }

        .page-top-controls .aa-page-actions {
            gap: 45px;
        }

        .page-menu-item {
            font-size: 35px;
        }

        .page-menu-wrap.is-open .page-more-menu {
            display: grid;
            gap: 20px;
            width: max-content;
        }

        .page-insert-button {
            font-size: 35px;
        }

        .page-top-controls .aa-page-actions {
            gap: 45px;
            padding-right: 20px;
        }

        .aa-left-drawer-close {
            position: absolute;
            right: 5px;
            top: 5px;
        }

        .aa-left-rail-tab .aa-premium-crown {
            position: absolute;
            top: -5px;
            right: 7px;
            width: 9px;
            height: 9px;
        }
    
        body.aa-editor-device-blocked .aa-studio-shell {
            filter: none;
            pointer-events: auto;
            user-select: auto;
        }

        body.aa-editor-mobile-mode .aa-studio-shell {
            grid-template-rows: 52px minmax(0, 1fr);
            height: 100dvh;
            background: #e5e7eb;
        }

	        body.aa-editor-mobile-mode .aa-topbar {
	            min-height: 55px;
	            gap: 5px;
            justify-content: space-between;
            border-bottom: 1px solid rgba(148, 163, 184, .28);
            background: radial-gradient(circle at 18% 0%, rgba(45, 212, 191, .18), transparent 30%), radial-gradient(circle at 78% 14%, rgba(59, 130, 246, .12), transparent 26%), rgba(255, 255, 255, .88);
            color: #111827;
            padding: 8px 10px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
            border-radius: 24px;
            margin: 5px;
        }

        body.aa-editor-mobile-mode .aa-topbar-brand {
            display: none !important;
        }

	        body.aa-editor-mobile-mode .aa-mobile-tools-btn,
	        body.aa-editor-mobile-mode .aa-mobile-save-btn,
	        body.aa-editor-mobile-mode .aa-mobile-share-btn {
	            display: inline-flex !important;
	            align-items: center;
	            justify-content: center;
	            flex: 0 0 auto;
	            min-width: 40px;
	            min-height: 40px;
            border: 1px solid #dbe5ef00;
            border-radius: 999px;
            background: #ababab54;
            color: #0f172a;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
        }

	        body.aa-editor-mobile-mode .aa-mobile-tools-btn {
	            font-size: 18px;
	        }

	        body.aa-editor-mobile-mode .aa-mobile-save-btn {
	            order: 0;
	            font-size: 15px;
	        }
	
		        body.aa-editor-mobile-mode .aa-mobile-share-btn {
		            order: 3;
	            min-width: 56px;
	            padding-inline: 10px;
	            font-size: 11px;
	            font-weight: 950;
	        }

	        body.aa-editor-mobile-mode .aa-topbar-controls {
	            flex: 0 0 auto;
	            gap: 4px !important;
            justify-content: flex-end !important;
            overflow: visible;
        }

	        body.aa-editor-mobile-mode .aa-topbar-group,
	        body.aa-editor-mobile-mode .aa-topbar-size-controls,
	        body.aa-editor-mobile-mode .aa-topbar-zoom,
	        body.aa-editor-mobile-mode #aaSaveState,
	        body.aa-editor-mobile-mode #aaResizeMenuBtn {
	            display: none !important;
	        }

	        body.aa-editor-mobile-mode .aa-topbar-zoom {
	            display: inline-flex !important;
	            order: 1;
	            align-items: center;
	            gap: 2px;
	            min-height: 34px;
	            border: 0;
	            background: #f8fafc;
	            border-radius: 999px;
	            padding: 3px;
	            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .24);
	        }

	        body.aa-editor-mobile-mode .aa-topbar-zoom #aaZoomLabel {
	            display: inline-flex !important;
	            align-items: center;
	            justify-content: center;
	            min-width: 34px !important;
	            color: #334155 !important;
	            font-size: 10px;
	            font-weight: 950;
	            letter-spacing: 0;
	        }

	        body.aa-editor-mobile-mode .aa-topbar-zoom #aaFitBtn {
	            display: none !important;
	        }

	        body.aa-editor-mobile-mode .aa-topbar-zoom #aaZoomOutBtn,
	        body.aa-editor-mobile-mode .aa-topbar-zoom #aaZoomInBtn {
	            display: inline-flex !important;
	            width: 28px;
	            min-width: 28px;
	            min-height: 28px;
	            align-items: center;
	            justify-content: center;
	            border: 0;
	            border-radius: 999px;
	            background: #ffffff;
	            color: #334155;
	            padding: 0;
	            box-shadow: 0 4px 10px rgba(15, 23, 42, .08);
	        }

	        body.aa-editor-mobile-mode .aa-topbar-history {
	            display: inline-flex !important;
	            order: 2;
	            min-height: 34px;
	            gap: 5px;
	            border: 0;
            background: transparent;
            padding: 0;
        }

	        body.aa-editor-mobile-mode .aa-topbar-history .aa-action-btn {
	            width: 34px;
	            min-width: 34px;
	            min-height: 34px;
            border: 0;
            border-radius: 999px;
            /* background: #f8fafc; */
            color: #334155;
            padding: 0;
            /* box-shadow: inset 0 0 0 1px #e2e8f0; */
        }

        body.aa-editor-mobile-mode .aa-topbar-history .aa-topbar-action-text {
            display: none;
        }

        body.aa-editor-mobile-mode .aa-topbar-actions {
            display: none !important;
        }

        body.aa-editor-mobile-mode .aa-workspace {
            grid-template-columns: 1fr;
            min-height: 0;
        }

        body.aa-editor-mobile-mode .aa-leftbar {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 180;
            height: auto;
            overflow: visible;
            pointer-events: none;
            background: transparent;
        }

        body.aa-editor-mobile-mode .aa-left-rail {
            position: fixed;
            left: 10px;
            right: auto;
            top: 66px;
            bottom: auto;
            z-index: 181;
            display: grid;
            width: min(242px, calc(100vw - 20px));
            height: auto;
            max-height: min(68dvh, 470px);
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
            overflow-y: auto;
            pointer-events: auto;
            opacity: 0;
            visibility: hidden;
            border: 1px solid rgba(226, 232, 240, .96);
            border-radius: 22px;
            background: rgba(255, 255, 255, .97);
            padding: 10px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .16);
            transform: translateY(-8px) scale(.98);
            transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        body.aa-editor-mobile-mode.aa-mobile-tools-open .aa-left-rail {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        body.aa-editor-mobile-mode .aa-left-rail::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-context-toolbar::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-text-context-toolbar::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-countdown-context-toolbar::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-tablet-action-dock::-webkit-scrollbar {
            display: none;
        }

        body.aa-editor-mobile-mode .aa-left-rail-spacer {
            display: none;
        }

        body.aa-editor-mobile-mode .aa-left-rail-link,
        body.aa-editor-mobile-mode .aa-left-rail-tab {
            display: flex;
            min-width: 0;
            min-height: 48px;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
            border-radius: 15px;
            font-size: 11px;
            padding: 0 10px;
            text-align: left;
            white-space: nowrap;
        }

        body.aa-editor-mobile-mode .aa-left-rail-link i,
        body.aa-editor-mobile-mode .aa-left-rail-tab i,
        body.aa-editor-mobile-mode .aa-left-rail-link svg,
        body.aa-editor-mobile-mode .aa-left-rail-tab svg {
            width: 20px;
            height: 20px;
        }

        body.aa-editor-mobile-mode .aa-left-rail-img-icon {
            width: 22px;
            height: 22px;
        }

        body.aa-editor-mobile-mode .aa-left-drawer {
            position: fixed;
            top: auto;
            left: 8px;
            right: 8px;
            bottom: calc(14px + var(--aa-mobile-keyboard-offset, 0px));
            width: auto;
            max-width: none;
            max-height: min(54dvh, 350px, calc(var(--aa-mobile-viewport-height, 100dvh) - 88px));
            overflow-y: auto;
            pointer-events: none;
            border: 1px solid rgba(226, 232, 240, .98);
            border-radius: 24px;
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 -22px 70px rgba(15, 23, 42, .22);
            transform: translateY(18px);
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        body.aa-editor-mobile-mode .aa-left-drawer-close {
            position: sticky;
            top: 10px;
            z-index: 6;
            margin: 10px 10px -44px auto;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .16);
        }

        body.aa-editor-mobile-mode .aa-font-drawer-head .aa-font-drawer-close {
            display: none !important;
        }

        body.aa-editor-mobile-mode .aa-left-drawer::-webkit-scrollbar {
            display: none;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-drawer-open .aa-left-drawer,
        body.aa-editor-mobile-mode .aa-left-drawer.is-pinned {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }

        body.aa-editor-mobile-mode.aa-mobile-interaction-drawer-open .aa-left-drawer-close {
            display: none !important;
        }

        body.aa-editor-mobile-mode .aa-left-drawer .aa-panel-card {
            border: 0;
            border-radius: 24px;
            box-shadow: none;
            padding: 16px;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-left-drawer {
            max-height: min(62dvh, 460px, calc(var(--aa-mobile-viewport-height, 100dvh) - 88px));
            overflow-y: auto;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer [data-aa-left-panel="import-reference"] {
            height: auto;
            min-height: 0;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-panel-card.aa-acara-ai-card {
            height: auto;
            min-height: 0;
            overflow: visible;
            padding: 12px;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-shell {
            height: auto;
            gap: 10px;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-page-label {
            order: 1;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-hero {
            order: 2;
            flex: 0 0 auto;
            min-height: 76px;
            max-height: 146px;
            align-items: flex-start;
            justify-content: flex-start;
            padding: 12px 8px 8px;
            overflow-y: auto;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-hero h2 {
            max-width: 230px;
            font-size: 22px;
            line-height: 1.18;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-message {
            max-width: 100%;
            margin-bottom: 12px;
            border-radius: 16px;
            padding: 11px 12px;
            font-size: 12px;
            line-height: 1.45;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-composer {
            position: sticky;
            bottom: 0;
            z-index: 7;
            order: 3;
            min-height: 104px;
            padding: 12px;
            box-shadow: 0 -10px 22px rgba(15, 118, 110, .08), 0 0 0 1px rgba(20, 184, 166, .08);
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-composer textarea {
            min-height: 46px;
            font-size: 13px;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-composer-actions {
            margin-top: 4px;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-icon-btn,
        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-send-btn {
            width: 38px;
            height: 38px;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-presets {
            order: 4;
            gap: 8px;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-preset {
            min-height: 36px;
            padding: 7px 12px;
            font-size: 10px;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-attachment {
            order: 5;
        }

        body.aa-editor-mobile-mode .aa-leftbar.is-acara-ai-drawer .aa-acara-ai-disclaimer {
            order: 6;
            font-size: 10px;
        }

        body.aa-editor-mobile-mode .aa-left-drawer input[type="search"],
        body.aa-editor-mobile-mode .aa-left-drawer .aa-font-drawer-search-wrap,
        body.aa-editor-mobile-mode .aa-left-drawer .aa-snippet-search-wrap,
        body.aa-editor-mobile-mode .aa-left-drawer .aa-editor-asset-search-hero,
        body.aa-editor-mobile-mode .aa-left-drawer .aa-template-search-hero,
        body.aa-editor-mobile-mode .aa-left-drawer .aa-template-search-wrap,
        body.aa-editor-mobile-mode .aa-left-drawer .aa-editor-asset-search-btn,
        body.aa-editor-mobile-mode .aa-left-drawer .aa-template-search-btn {
            display: none !important;
        }

        body.aa-editor-mobile-mode .aa-rightbar {
            display: none !important;
        }

        body.aa-editor-mobile-mode.aa-mobile-properties-open .aa-rightbar {
            position: fixed;
            left: 8px;
            right: 8px;
            top: auto;
            bottom: calc(14px + var(--aa-mobile-keyboard-offset, 0px));
            z-index: 220;
            display: block !important;
            width: auto;
            max-width: none;
            max-height: min(54dvh, 430px, calc(var(--aa-mobile-viewport-height, 100dvh) - 88px));
            overflow-y: auto;
            border: 1px solid rgba(226, 232, 240, .98);
            border-radius: 24px;
            background: rgba(255, 255, 255, .99);
            box-shadow: 0 -22px 70px rgba(15, 23, 42, .24);
            padding: 0 14px 14px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        body.aa-editor-mobile-mode.aa-mobile-properties-open .aa-rightbar::-webkit-scrollbar {
            display: none;
        }

        body.aa-editor-mobile-mode .aa-mobile-properties-bar {
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 0 -14px 12px;
            border-bottom: 1px solid rgba(226, 232, 240, .9);
            border-radius: 24px 24px 0 0;
            background: rgba(255, 255, 255, .98);
            padding: 12px 14px 10px;
            color: #0f172a;
            font-size: 13px;
            font-weight: 950;
        }

        body.aa-editor-mobile-mode .aa-mobile-properties-bar > span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        body.aa-editor-mobile-mode .aa-mobile-properties-bar > div {
            display: inline-flex;
            flex: 0 0 auto;
            gap: 7px;
        }

        body.aa-editor-mobile-mode .aa-mobile-properties-action {
            min-height: 34px;
            border: 1px solid #dbe5ef;
            border-radius: 999px;
            background: #ffffff;
            color: #334155;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 950;
        }

        body.aa-editor-mobile-mode .aa-mobile-properties-action.is-primary {
            border-color: #0f766e;
            background: #0f766e;
            color: #ffffff;
        }

        body.aa-editor-mobile-mode.aa-mobile-properties-open .aa-rightbar .aa-panel-card {
            border: 0;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }

        body.aa-editor-mobile-mode .aa-premium-upgrade-card {
            display: none !important;
        }

        body.aa-editor-mobile-mode .aa-stage-wrap {
            overflow: auto;
            padding: 18px 12px 112px;
            background: #e5e7eb;
            -webkit-overflow-scrolling: touch;
        }

        body.aa-editor-mobile-mode.aa-mobile-text-editing .aa-studio-shell {
            height: 100svh;
        }

        body.aa-editor-mobile-mode.aa-mobile-text-editing .aa-stage-wrap {
            overscroll-behavior: contain;
            scroll-behavior: auto;
        }

        body.aa-editor-mobile-mode.aa-mobile-text-editing .aa-left-rail,
        body.aa-editor-mobile-mode.aa-mobile-text-editing .aa-left-drawer,
        body.aa-editor-mobile-mode.aa-mobile-text-editing .aa-tablet-action-dock,
        body.aa-editor-mobile-mode.aa-mobile-text-editing .aa-interaction-popover {
            display: none !important;
        }

        body.aa-editor-mobile-mode .aa-stage {
            min-width: 0;
        }

        body.aa-editor-mobile-mode .aa-editor-mode-strip {
            margin-bottom: 16px;
            display: inline-grid;
            margin-left: 90px;
        }

        body.aa-editor-mobile-mode .aa-editor-mode-toggle {
            transform: none;
            border-radius: 999px;
            padding: 4px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .12);
        }

        body.aa-editor-mobile-mode .aa-editor-mode-btn {
            min-height: 38px;
            padding: 50px;
            font-size: 35px;
        }

        body.aa-editor-mobile-mode .page-top-controls {
            width: min(1000px, calc(500vw - 24px));
            min-height: 95px;
            padding: 7px 8px 7px 12px;
            border-radius: 999px;
            transform: none;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
        }

        body.aa-editor-mobile-mode .page-title-button span {
            max-width: 52vw;
            font-size: 35px;
        }

        body.aa-editor-mobile-mode .page-top-controls .aa-page-action {
            width: 34px;
            min-width: 34px;
            height: 34px;
            font-size: 35px;
        }   

        body.aa-editor-mobile-mode .aa-opening-exit-select {
            max-width: max-content;
            font-size: 35px;
        }

        body.aa-editor-mobile-mode .aa-tablet-action-dock {
            position: fixed;
            left: auto;
            right: 10px;
            top: 66px;
            bottom: auto;
            z-index: 190;
            display: grid;
            width: min(224px, calc(100vw - 20px));
            max-width: none;
            gap: 8px;
            overflow: hidden;
            justify-content: stretch;
            opacity: 0;
            visibility: hidden;
            border: 1px solid rgba(226, 232, 240, .96);
            border-radius: 22px;
            background: rgba(255, 255, 255, .97);
            padding: 10px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .16);
            scrollbar-width: none;
            transform: translateY(-8px) scale(.98);
            transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
        }

        body.aa-editor-mobile-mode.aa-mobile-share-open .aa-tablet-action-dock {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        body.aa-editor-mobile-mode .aa-tablet-action-dock .aa-action-btn {
            width: 100%;
            min-height: 44px;
            border-radius: 15px;
            padding-inline: 12px;
            font-size: 12px;
            white-space: nowrap;
        }

	        body.aa-editor-mobile-mode .aa-context-toolbar,
	        body.aa-editor-mobile-mode .aa-text-context-toolbar,
	        body.aa-editor-mobile-mode .aa-countdown-context-toolbar {
            position: fixed !important;
            left: 0 !important;
            right: 0 !important;
            top: auto !important;
            bottom: 0px !important;
            z-index: 205;
            width: 100vw;
            max-width: 100vw;
            overflow-x: auto;
            overflow-y: hidden;
            justify-content: flex-start;
            gap: 9px;
            border-radius: 0;
            border-width: 1px 0 0;
            background: rgba(255, 255, 255, .98);
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            transform: none !important;
            box-shadow: 0 -18px 44px rgba(15, 23, 42, .12);
            scrollbar-width: none;
            margin: 5px;
            border-radius: 30px;
	            -webkit-overflow-scrolling: touch;
	        }

	        body.aa-editor-mobile-mode .aa-date-picker {
	            z-index: 260;
	        }

	        body.aa-editor-mobile-mode.aa-mobile-drawer-active .aa-context-toolbar,
        body.aa-editor-mobile-mode.aa-mobile-drawer-active .aa-text-context-toolbar,
        body.aa-editor-mobile-mode.aa-mobile-drawer-active .aa-countdown-context-toolbar,
        body.aa-editor-mobile-mode.aa-mobile-drawer-active .aa-object-floating-toolbar,
        body.aa-editor-mobile-mode.aa-mobile-drawer-active .aa-interaction-popover,
        body.aa-editor-mobile-mode.aa-mobile-properties-open .aa-context-toolbar,
        body.aa-editor-mobile-mode.aa-mobile-properties-open .aa-text-context-toolbar,
        body.aa-editor-mobile-mode.aa-mobile-properties-open .aa-countdown-context-toolbar,
	        body.aa-editor-mobile-mode.aa-mobile-properties-open .aa-object-floating-toolbar,
	        body.aa-editor-mobile-mode.aa-mobile-properties-open .aa-interaction-popover {
	            display: none !important;
	        }

	        body.aa-editor-mobile-mode.aa-mobile-drawer-active.aa-mobile-music-selection-active .aa-object-floating-toolbar.is-visible {
	            display: inline-flex !important;
	        }
	
	        body.aa-editor-mobile-mode.aa-mobile-interaction-drawer-open .aa-left-drawer .aa-interaction-popover.is-mobile-drawer {
	            display: none !important;
	            pointer-events: none !important;
        }

        body.aa-editor-mobile-mode .aa-element-drawer-status {
            margin: -4px 0 12px;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.45;
        }

        body.aa-editor-mobile-mode.aa-mobile-interaction-drawer-open [data-aa-left-panel="element-interaction"] .aa-panel-title,
        body.aa-editor-mobile-mode.aa-mobile-interaction-drawer-open [data-aa-left-panel="element-interaction"] .aa-element-drawer-status {
            display: none !important;
        }

        body.aa-editor-mobile-mode.aa-mobile-interaction-drawer-open [data-aa-left-panel="element-interaction"] .aa-element-drawer-card {
            padding-top: 14px;
        }

        body.aa-editor-mobile-mode .aa-element-drawer-mount {
            display: grid;
            gap: 12px;
        }

        body.aa-editor-mobile-mode.aa-mobile-interaction-drawer-open .aa-element-drawer-mount {
            display: none !important;
            pointer-events: none !important;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-drawer {
            display: grid;
            gap: 12px;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-drawer.is-floating-mobile-panel {
            position: fixed;
            left: 8px;
            right: 8px;
            bottom: calc(82px + var(--aa-mobile-keyboard-offset, 0px) + env(safe-area-inset-bottom));
            z-index: 225;
            display: grid;
            width: auto;
            max-height: min(40dvh, 340px, calc(var(--aa-mobile-viewport-height, 100dvh) - 184px));
            overflow-y: auto;
            border: 1px solid rgba(226, 232, 240, .98);
            border-radius: 22px;
            background: rgba(255, 255, 255, .99);
            padding: 14px;
            box-shadow: 0 -22px 70px rgba(15, 23, 42, .20);
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-drawer.is-floating-mobile-panel::-webkit-scrollbar {
            display: none;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-drawer[hidden] {
            display: none !important;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-section {
            display: none;
            gap: 12px;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-section.is-active {
            display: grid;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-section.is-compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: end;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-full,
        body.aa-editor-mobile-mode .aa-mobile-interaction-check {
            grid-column: 1 / -1;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-field,
        body.aa-editor-mobile-mode .aa-mobile-interaction-check {
            display: grid;
            gap: 6px;
            color: #475569;
            font-size: 11px;
            font-weight: 900;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-field input,
        body.aa-editor-mobile-mode .aa-mobile-interaction-field select,
        body.aa-editor-mobile-mode .aa-mobile-interaction-field textarea {
            width: 100%;
            min-height: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 11px;
            background: #fff;
            padding: 9px 10px;
            color: #0f172a;
            font: inherit;
            box-sizing: border-box;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-field textarea {
            min-height: 84px;
            resize: vertical;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-range {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: center;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-check {
            display: flex;
            align-items: center;
            gap: 9px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: rgba(248, 250, 252, .92);
            padding: 10px 11px;
        }

        body.aa-editor-mobile-mode .aa-mobile-interaction-check input {
            width: 16px;
            height: 16px;
        }

        body.aa-editor-mobile-mode .aa-mobile-gallery-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        body.aa-editor-mobile-mode .aa-mobile-gallery-actions .aa-panel-btn {
            min-height: 40px;
            border-radius: 12px;
            padding: 0 10px;
            font-size: 11px;
        }

        body.aa-editor-mobile-mode .aa-mobile-gallery-list-mount {
            max-height: 178px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
            padding: 8px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        body.aa-editor-mobile-mode .aa-mobile-gallery-list-mount::-webkit-scrollbar {
            display: none;
        }

        body.aa-editor-mobile-mode .aa-gallery-list.is-mobile-gallery-list {
            max-height: none;
            overflow: visible;
        }

        body.aa-editor-mobile-mode .aa-gallery-list.is-mobile-gallery-list .aa-gallery-item-row {
            grid-template-columns: 44px minmax(0, 1fr) auto;
            border-radius: 12px;
            padding: 6px;
        }

        body.aa-editor-mobile-mode .aa-gallery-list.is-mobile-gallery-list .aa-gallery-item-row img {
            width: 44px;
            height: 44px;
        }

        body.aa-editor-mobile-mode .aa-gallery-list.is-mobile-gallery-list .aa-gallery-item-actions button {
            width: 28px;
            height: 28px;
        }

        body.aa-editor-mobile-mode:not(.aa-mobile-interaction-drawer-open) .aa-interaction-popover.is-mobile-drawer .aa-interaction-popover-section.is-active {
            display: grid;
            gap: 12px;
        }

        body.aa-editor-mobile-mode:not(.aa-mobile-interaction-drawer-open) .aa-interaction-popover.is-mobile-drawer .aa-interaction-popover-section.is-compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        body.aa-editor-mobile-mode .aa-context-toolbar:not(.is-visible),
        body.aa-editor-mobile-mode .aa-text-context-toolbar:not(.is-visible),
        body.aa-editor-mobile-mode .aa-countdown-context-toolbar:not(.is-visible) {
            pointer-events: none;
        }

        body.aa-editor-mobile-mode .aa-context-flip-popover.is-open,
        body.aa-editor-mobile-mode .aa-context-stroke-popover.is-open,
        body.aa-editor-mobile-mode .aa-context-radius-popover.is-open,
        body.aa-editor-mobile-mode .aa-context-image-effects-popover.is-open,
        body.aa-editor-mobile-mode .aa-context-image-frame-popover.is-open,
        body.aa-editor-mobile-mode .aa-context-transparency-popover.is-open,
        body.aa-editor-mobile-mode .aa-text-effects-popover.is-open,
        body.aa-editor-mobile-mode .aa-animation-popover.is-open {
            position: fixed !important;
            left: 12px !important;
            right: 12px !important;
            top: auto !important;
            bottom: calc(82px + env(safe-area-inset-bottom)) !important;
            z-index: 214;
            width: auto !important;
            max-width: none !important;
            max-height: min(56dvh, 420px);
            overflow-y: auto;
            transform: none !important;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        body.aa-editor-mobile-mode .aa-context-flip-popover.is-open::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-context-stroke-popover.is-open::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-context-radius-popover.is-open::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-context-image-effects-popover.is-open::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-context-image-frame-popover.is-open::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-context-transparency-popover.is-open::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-text-effects-popover.is-open::-webkit-scrollbar,
        body.aa-editor-mobile-mode .aa-animation-popover.is-open::-webkit-scrollbar {
            display: none;
        }

        body.aa-editor-mobile-mode .aa-object-floating-tool,
        body.aa-editor-mobile-mode .aa-context-tool,
        body.aa-editor-mobile-mode .aa-text-context-tool {
            flex: 0 0 auto;
            min-width: 40px;
            height: 40px;
            border-radius: 16px;
            font-size: 12px;
        }

        body.aa-editor-mobile-mode .aa-outside-selection-overlay {
            display: none !important;
        }

        body.aa-editor-mobile-mode .aa-object-floating-toolbar {
            z-index: 209;
            gap: 6px;
            border-radius: 18px;
            padding: 6px;
            transform: none;
            box-shadow: 0 16px 42px rgba(15, 23, 42, .18);
        }

        body.aa-editor-mobile-mode .aa-mobile-text-edit-btn {
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
            min-width: max-content;
            padding: 0 13px;
            color: #0f766e;
            font-weight: 950;
        }

        body.aa-editor-mobile-mode .aa-mobile-text-edit-btn span {
            display: inline;
            white-space: nowrap;
        }

        body.aa-editor-mobile-mode .aa-interaction-popover {
            position: fixed !important;
            left: 12px !important;
            right: 12px !important;
            top: auto !important;
            bottom: 78px !important;
            z-index: 206;
            width: auto;
            max-width: none;
            max-height: min(58dvh, 420px);
            overflow-y: auto;
            border-radius: 22px;
            box-shadow: 0 -18px 54px rgba(15, 23, 42, .18);
            transform: none !important;
        }

        body.aa-editor-mobile-mode .aa-interaction-popover input,
        body.aa-editor-mobile-mode .aa-interaction-popover select,
        body.aa-editor-mobile-mode .aa-interaction-popover textarea {
            min-height: 42px;
            font-size: 13px;
        }

        body.aa-editor-mobile-mode .aa-context-separator {
            flex: 0 0 1px;
        }

        body.aa-editor-mobile-mode .aa-crop-floating-toolbar {
            transform: scale(.92);
            transform-origin: center;
        }
    }
    </style>
