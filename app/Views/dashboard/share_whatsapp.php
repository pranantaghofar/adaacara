<?php
    $page = $page ?? [];
    $pageId = (int) ($page['id'] ?? 0);
    $manualMode = (bool) ($manualMode ?? $pageId <= 0);
    $pageTitle = (string) ($page['title'] ?? $manualTitle ?? '');
    $eventDate = (string) ($page['event_date'] ?? $manualDate ?? '');
    $publicUrl = (string) ($publicUrl ?? (($page['slug'] ?? '') !== '' ? site_url('u/' . ($page['slug'] ?? '')) : ''));
    $pageTitleDisplay = $pageTitle !== '' ? $pageTitle : 'Manual Share WhatsApp';
    $isLoggedIn = (bool) ($isLoggedIn ?? ! empty($userEmail));
    $aaIcon = static function (string $name, string $class = 'wa-icon'): string {
        $icons = [
            'moon' => '<path d="M21 14.5A8.5 8.5 0 0 1 9.5 3 7 7 0 1 0 21 14.5Z"/>',
            'edit' => '<path d="M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3Z"/><path d="m14 7 3 3"/>',
            'dashboard' => '<rect x="4" y="4" width="7" height="7" rx="2"/><rect x="13" y="4" width="7" height="7" rx="2"/><rect x="4" y="13" width="7" height="7" rx="2"/><rect x="13" y="13" width="7" height="7" rx="2"/>',
            'login' => '<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M15 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/>',
            'check' => '<path d="m5 13 4 4L19 7"/>',
            'save' => '<path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/>',
            'reset' => '<path d="M4 4v6h6"/><path d="M20 12a8 8 0 0 0-14.9-4M4 12a8 8 0 0 0 14.9 4"/>',
            'upload' => '<path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 20h16"/>',
            'user-plus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M19 8v6M16 11h6"/>',
            'download' => '<path d="M12 4v12"/><path d="m7 11 5 5 5-5"/><path d="M4 20h16"/>',
            'file' => '<path d="M6 2h9l5 5v15H6V2Z"/><path d="M14 2v6h6M9 13h6M9 17h6"/>',
            'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
            'sparkles' => '<path d="M12 3l1.8 4.2L18 9l-4.2 1.8L12 15l-1.8-4.2L6 9l4.2-1.8L12 3Z"/><path d="M19 14l.9 2.1L22 17l-2.1.9L19 20l-.9-2.1L16 17l2.1-.9L19 14Z"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/>',
            'copy' => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M4 16V6a2 2 0 0 1 2-2h10"/>',
            'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        ];
        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['sparkles']) . '</svg>';
    };
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Share WhatsApp - <?= esc($pageTitleDisplay) ?></title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        :root {
            --aa-bg: #eef8f5;
            --aa-card: rgba(251, 255, 253, .82);
            --aa-text: #0f1d23;
            --aa-muted: #667983;
            --aa-line: #d6e6e2;
            --aa-blue: #2f55c7;
            --aa-emerald: #008f72;
            --aa-gold: #df7400;
            --aa-danger: #d83a4e;
            --aa-shadow: 0 18px 52px rgba(20, 53, 48, .08);
        }

        [data-theme="dark"],
        html[data-aa-public-theme="dark"] .wa-shell {
            --aa-bg: #0d1726;
            --aa-card: rgba(19, 31, 48, .92);
            --aa-text: #eef6ff;
            --aa-muted: #aab9ca;
            --aa-line: rgba(255, 255, 255, .12);
            --aa-shadow: 0 24px 70px rgba(0, 0, 0, .32);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background:
                radial-gradient(circle at 16% 0%, rgba(0, 143, 114, .08), transparent 34%),
                radial-gradient(circle at 78% 10%, rgba(47, 85, 199, .08), transparent 28%),
                var(--aa-bg);
            color: var(--aa-text);
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }

        .wa-icon {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }

        .wa-shell {
            width: min(1320px, 100%);
            margin: 0 auto;
            padding: 26px 34px 44px;
        }

        .wa-topbar,
        .wa-card,
        .wa-modal-card,
        .wa-preview {
            border: 1px solid var(--aa-line);
            background: var(--aa-card);
            box-shadow: var(--aa-shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .wa-topbar {
            position: sticky;
            top: 10px;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
            border-radius: 20px;
            padding: 10px 12px;
            box-shadow: 0 10px 24px rgba(20, 53, 48, .07);
        }

        #darkToggle {
            display: none;
        }

        .wa-topbar .wa-actions {
            flex-wrap: nowrap;
            justify-content: flex-end;
        }

        .wa-topbar .wa-btn {
            min-height: 38px;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: none;
            padding: 0 16px;
        }

        .wa-brand {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 14px;
        }

        .wa-logo {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 999px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 14px 28px rgba(0, 143, 122, .18);
        }

        .wa-logo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        .wa-kicker {
            margin: 0 0 4px;
            color: var(--aa-emerald);
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        h1, h2, h3, p { margin-top: 0; }

        .wa-title {
            margin: 0;
            max-width: 680px;
            font-size: clamp(34px, 4vw, 54px);
            line-height: 1;
            letter-spacing: -.04em;
        }

        .wa-title .accent {
            color: var(--aa-emerald);
        }

        .wa-subtitle {
            max-width: 620px;
            margin: 16px 0 0;
            color: var(--aa-muted);
            font-size: 15px;
            line-height: 1.65;
        }

        .wa-actions,
        .wa-row-actions,
        .wa-filters,
        .wa-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .wa-btn {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid var(--aa-line);
            border-radius: 16px;
            background: rgba(255, 255, 255, .62);
            color: #162235;
            padding: 0 16px;
            font-weight: 850;
            font-size: 13px;
            cursor: pointer;
            transition: .16s ease;
        }

        [data-theme="dark"] .wa-btn,
        html[data-aa-public-theme="dark"] .wa-shell .wa-btn { background: rgba(255, 255, 255, .08); color: var(--aa-text); }

        .wa-btn:hover {
            border-color: rgba(0, 143, 122, .45);
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(0, 143, 122, .12);
        }

        .wa-btn.primary {
            border-color: transparent;
            background: var(--aa-blue);
            color: #fff;
            box-shadow: 0 13px 24px rgba(47, 85, 199, .18);
        }

        .wa-btn.emerald {
            border-color: transparent;
            background: linear-gradient(135deg, var(--aa-emerald), #00ad94);
            color: #fff;
        }

        .wa-btn.gold {
            border-color: transparent;
            background: var(--aa-gold);
            color: #fff;
            box-shadow: 0 13px 24px rgba(223, 116, 0, .14);
        }

        .wa-btn.danger { color: var(--aa-danger); }
        .wa-btn.small { min-height: 36px; border-radius: 999px; padding: 0 14px; font-size: 12px; }

        .wa-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 34px;
            padding: 28px 0 24px;
            align-items: center;
        }

        .wa-hero .wa-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            padding-top: 0;
        }

        .wa-hero .wa-btn {
            width: 100%;
            min-height: 48px;
            border-radius: 16px;
        }

        .wa-stats {
            display: grid;
            grid-template-columns: repeat(7, minmax(112px, 1fr));
            gap: 10px;
        }

        .wa-card > .wa-stats {
            margin: 2px 0 16px;
        }

        .wa-stat {
            border: 1px solid var(--aa-line);
            border-radius: 17px;
            background: var(--aa-card);
            padding: 14px;
            box-shadow: 0 10px 26px rgba(15, 32, 51, .05);
        }

        .wa-stat span {
            color: var(--aa-muted);
            font-size: 9px;
            font-weight: 950;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .wa-stat strong {
            display: block;
            margin-top: 8px;
            font-size: 24px;
            line-height: 1;
        }

        .wa-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 350px;
            gap: 18px;
            margin-top: 22px;
            align-items: start;
        }

        .wa-grid > main {
            display: contents !important;
        }

        .wa-grid > main > .wa-card:first-child {
            grid-column: 1;
            grid-row: 1;
        }

        .wa-grid > .wa-preview {
            grid-column: 2;
            grid-row: 1;
        }

        .wa-grid > main > .wa-card:nth-child(2) {
            grid-column: 1 / -1;
            grid-row: 2;
            margin-top: 6px;
        }

        .wa-card {
            border-radius: 20px;
            padding: 18px;
        }

        .wa-card-head {
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .wa-card h2 {
            margin-bottom: 4px;
            font-size: 18px;
        }

        .wa-card p {
            color: var(--aa-muted);
            line-height: 1.6;
        }

        .wa-invitation-card {
            margin-top: 0;
            margin-bottom: 22px;
        }

        .wa-invitation-card .wa-form-grid {
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.8fr) minmax(150px, .8fr);
        }

        .wa-form-hint {
            margin: 10px 0 0;
            color: var(--aa-muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .wa-form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .wa-field {
            display: grid;
            gap: 7px;
            color: var(--aa-muted);
            font-size: 12px;
            font-weight: 850;
        }

        .wa-input,
        .wa-select,
        .wa-textarea {
            width: 100%;
            border: 1px solid var(--aa-line);
            border-radius: 14px;
            background: rgba(255, 255, 255, .48);
            color: var(--aa-text);
            padding: 10px 12px;
            outline: none;
        }

        [data-theme="dark"] .wa-input,
        [data-theme="dark"] .wa-select,
        [data-theme="dark"] .wa-textarea,
        html[data-aa-public-theme="dark"] .wa-shell .wa-input,
        html[data-aa-public-theme="dark"] .wa-shell .wa-select,
        html[data-aa-public-theme="dark"] .wa-shell .wa-textarea {
            background: rgba(255, 255, 255, .08);
        }

        .wa-textarea {
            min-height: 270px;
            resize: vertical;
            line-height: 1.8;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 13px;
        }

        .wa-vars {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-height: 86px;
            overflow: auto;
            padding-right: 4px;
        }

        .wa-var {
            border: 1px solid rgba(0, 143, 122, .2);
            border-radius: 999px;
            background: rgba(0, 143, 122, .08);
            color: var(--aa-emerald);
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 850;
            cursor: pointer;
        }

        .wa-template-layout {
            display: grid;
            grid-template-columns: 190px minmax(0, 1fr);
            gap: 12px;
        }

        .wa-category-list {
            display: grid;
            gap: 8px;
            max-height: 398px;
            overflow: auto;
        }

        .wa-cat {
            border: 1px solid var(--aa-line);
            border-radius: 13px;
            background: rgba(255, 255, 255, .42);
            padding: 10px 12px;
            color: var(--aa-text);
            font-weight: 850;
            text-align: left;
            cursor: pointer;
        }

        .wa-cat.active {
            border-color: rgba(0, 143, 122, .42);
            background: var(--aa-emerald);
            color: #fff;
        }

        .wa-template-card {
            position: relative;
        }

        .wa-template-status {
            display: none;
            align-items: center;
            gap: 8px;
            color: var(--aa-emerald);
            font-size: 12px;
            font-weight: 900;
        }

        .wa-template-status::before {
            content: "";
            width: 12px;
            height: 12px;
            border: 2px solid rgba(0, 143, 114, .25);
            border-top-color: var(--aa-emerald);
            border-radius: 999px;
            animation: waSpin .7s linear infinite;
        }

        .wa-template-card.is-loading .wa-template-status {
            display: inline-flex;
        }

        .wa-template-card.is-loading .wa-textarea {
            opacity: .64;
        }

        .wa-ai-controls {
            display: none !important;
        }

        @keyframes waSpin {
            to { transform: rotate(360deg); }
        }

        .wa-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--aa-line);
            border-radius: 18px;
        }

        .wa-table {
            width: 100%;
            min-width: 1040px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .wa-table th {
            color: var(--aa-muted);
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .08em;
            text-align: left;
            text-transform: uppercase;
            padding: 11px 8px;
        }

        .wa-table td {
            background: rgba(255, 255, 255, .52);
            border-top: 1px solid var(--aa-line);
            padding: 9px 8px;
            vertical-align: middle;
            font-size: 12px;
        }

        [data-theme="dark"] .wa-table td,
        html[data-aa-public-theme="dark"] .wa-shell .wa-table td { background: rgba(255, 255, 255, .06); }

        .wa-table tr:nth-child(odd) td {
            background: rgba(224, 243, 239, .62);
        }

        [data-theme="dark"] .wa-table tr:nth-child(odd) td,
        html[data-aa-public-theme="dark"] .wa-shell .wa-table tr:nth-child(odd) td {
            background: rgba(30, 41, 59, .78);
        }

        .wa-table td:first-child { border-left: 0; border-radius: 0; }
        .wa-table td:last-child { border-right: 0; border-radius: 0; }

        .wa-badge {
            display: inline-flex;
            min-height: 22px;
            align-items: center;
            border-radius: 999px;
            padding: 0 8px;
            font-size: 10px;
            font-weight: 950;
            white-space: nowrap;
        }

        .wa-table .wa-row-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, auto));
            gap: 5px;
            justify-content: start;
        }

        .wa-table .wa-btn.small {
            min-height: 27px;
            border-radius: 999px;
            padding: 0 8px;
            font-size: 10px;
        }

        .gray { background: #edf2f7; color: #5f6d7c; }
        .blue { background: #e8f2ff; color: #1d5f99; }
        .green { background: #e6f8f3; color: #008f7a; }
        .yellow { background: #fff6d8; color: #9a6b08; }
        .red { background: #ffe9ec; color: #d83a4e; }

        [data-theme="dark"] .gray,
        html[data-aa-public-theme="dark"] .wa-shell .gray { background: rgba(148, 163, 184, .16); color: #cbd5e1; }
        [data-theme="dark"] .blue,
        html[data-aa-public-theme="dark"] .wa-shell .blue { background: rgba(14, 165, 233, .16); color: #7dd3fc; }
        [data-theme="dark"] .green,
        html[data-aa-public-theme="dark"] .wa-shell .green { background: rgba(16, 185, 129, .16); color: #86efac; }
        [data-theme="dark"] .yellow,
        html[data-aa-public-theme="dark"] .wa-shell .yellow { background: rgba(245, 158, 11, .18); color: #fcd34d; }
        [data-theme="dark"] .red,
        html[data-aa-public-theme="dark"] .wa-shell .red { background: rgba(244, 63, 94, .18); color: #fda4af; }

        .wa-preview {
            position: static;
            top: 76px;
            border-radius: 20px;
            padding: 18px;
        }

        .wa-phone {
            overflow: hidden;
            border: 1px solid var(--aa-line);
            border-radius: 22px;
            background: #e5f5df;
            padding: 10px;
        }

        [data-theme="dark"] .wa-phone,
        html[data-aa-public-theme="dark"] .wa-shell .wa-phone {
            background: rgba(5, 46, 22, .64);
        }

        .wa-phone-head {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 17px 17px 0 0;
            background: #009562;
            color: #fff;
            padding: 12px;
            font-weight: 900;
        }

        .wa-avatar {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
        }

        .wa-bubble {
            margin: 12px 0 0;
            border-radius: 14px;
            background: #d2f4cb;
            padding: 15px;
            color: #102033;
            white-space: pre-wrap;
            line-height: 1.5;
            font-size: 12px;
            box-shadow: 0 8px 18px rgba(15, 32, 51, .08);
        }

        [data-theme="dark"] .wa-bubble,
        html[data-aa-public-theme="dark"] .wa-shell .wa-bubble {
            background: rgba(187, 247, 208, .14);
            color: #ecfdf5;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .24);
        }

        .wa-mobile-list { display: none; }

        .wa-empty {
            display: none;
            border: 1px dashed var(--aa-line);
            border-radius: 22px;
            padding: 30px;
            text-align: center;
        }

        .wa-empty.visible { display: block; }

        .wa-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: none;
            place-items: center;
            padding: 18px;
            background: rgba(9, 18, 30, .58);
        }

        .wa-modal.open { display: grid; }

        .wa-modal-card {
            width: min(760px, 100%);
            max-height: 88vh;
            overflow: auto;
            border-radius: 28px;
            padding: 22px;
        }

        .wa-import-drop {
            display: grid;
            min-height: 160px;
            place-items: center;
            border: 1px dashed rgba(29, 95, 153, .36);
            border-radius: 22px;
            background: rgba(29, 95, 153, .07);
            padding: 18px;
            text-align: center;
        }

        .wa-toast {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 80;
            display: none;
            max-width: 360px;
            border-radius: 16px;
            background: #102033;
            color: #fff;
            padding: 14px 16px;
            box-shadow: 0 18px 44px rgba(0, 0, 0, .22);
            font-weight: 800;
        }

        .wa-toast.show { display: block; }

        .wa-bottom-bar { display: none; }

        @media (max-width: 1180px) {
            .wa-shell { padding-inline: 24px; }
            .wa-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .wa-grid { grid-template-columns: 1fr; }
            .wa-grid > main,
            .wa-grid > main > .wa-card:first-child,
            .wa-grid > .wa-preview,
            .wa-grid > main > .wa-card:nth-child(2) {
                display: grid !important;
                grid-column: auto;
                grid-row: auto;
            }
            .wa-preview { position: static; }
            .wa-hero { grid-template-columns: 1fr; }
            .wa-hero .wa-actions { max-width: 680px; }
        }

        @media (max-width: 980px) {
            .wa-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .wa-invitation-card .wa-form-grid { grid-template-columns: 1fr; }
            .wa-template-layout { grid-template-columns: 1fr; }
            .wa-category-list {
                display: flex;
                max-height: none;
                overflow-x: auto;
                max-width: 100%;
                padding-bottom: 4px;
                -webkit-overflow-scrolling: touch;
            }
            .wa-cat {
                flex: 0 0 auto;
                min-width: 118px;
                white-space: nowrap;
            }
        }

        @media (max-width: 780px) {
            html,
            body {
                max-width: 100%;
                overflow-x: hidden;
            }
            .wa-shell { padding: 12px 12px 28px; }
            .wa-topbar {
                position: static;
                width: 100%;
                padding: 10px;
                align-items: center;
            }
            .wa-hero { grid-template-columns: 1fr; flex-direction: column; align-items: stretch; }
            .wa-topbar {
                flex-direction: row;
                align-items: center;
            }
            .wa-topbar .wa-actions {
                width: auto;
            }
            .wa-topbar .wa-btn {
                width: auto;
                min-width: 76px;
                min-height: 36px;
                padding: 0 12px;
            }
            .wa-hero { display: grid; }
            .wa-title { font-size: 32px; }
            .wa-subtitle { font-size: 15px; }
            .wa-hero .wa-actions { grid-template-columns: 1fr; }
            .wa-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .wa-grid,
            .wa-grid > main,
            .wa-grid > main > .wa-card:first-child,
            .wa-grid > .wa-preview,
            .wa-grid > main > .wa-card:nth-child(2),
            .wa-template-layout,
            .wa-template-layout > *,
            .wa-card,
            .wa-preview,
            .wa-phone,
            .wa-bubble {
                width: 100%;
                max-width: 100%;
                min-width: 0;
            }
            .wa-template-layout,
            .wa-form-grid { grid-template-columns: 1fr; }
            .wa-invitation-card .wa-form-grid { grid-template-columns: 1fr; }
            .wa-table-wrap { display: none; }
            .wa-mobile-list { display: grid; gap: 12px; }
            .wa-actions { align-items: stretch; }
            .wa-btn { width: 100%; }
            .wa-card,
            .wa-preview {
                padding: 14px;
                border-radius: 18px;
                overflow: hidden;
            }
            .wa-card-head {
                display: grid;
                gap: 10px;
            }
            .wa-card-head .wa-actions {
                width: 100%;
            }
            .wa-mobile-list .wa-card {
                padding: 12px;
            }
            .wa-mobile-list .wa-row-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 7px;
            }
            .wa-mobile-list .wa-btn.small {
                min-height: 32px;
                padding: 0 8px;
                font-size: 11px;
            }
            .wa-bottom-bar {
                position: sticky;
                bottom: 12px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                border: 1px solid var(--aa-line);
                border-radius: 18px;
                background: var(--aa-card);
                padding: 8px;
                box-shadow: var(--aa-shadow);
            }
            .wa-textarea {
                min-height: 230px;
                font-size: 12px;
                line-height: 1.65;
                overflow-wrap: anywhere;
            }
            .wa-vars {
                max-height: 112px;
            }
            .wa-var,
            .wa-cat {
                max-width: 100%;
            }
        }

        @media (max-width: 460px) {
            .wa-brand {
                gap: 9px;
            }
            .wa-logo {
                width: 32px;
                height: 32px;
                font-size: 11px;
            }
            .wa-brand strong {
                display: block;
                max-width: 170px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 12px;
            }
            .wa-kicker {
                font-size: 8px;
                letter-spacing: .14em;
            }
            .wa-stats {
                gap: 8px;
            }
            .wa-stat {
                padding: 12px;
            }
            .wa-stat strong {
                font-size: 22px;
            }
        }
    </style>
</head>
<body class="aa-app-ui aa-dashboard-theme-page">
    <div class="wa-shell" data-theme="light" data-dashboard-theme-sync>
        <header class="wa-topbar">
            <div class="wa-brand">
                <a class="wa-logo" href="<?= $isLoggedIn ? site_url('dashboard') : site_url('login') ?>" aria-label="AdaAcara">
                    <img src="<?= aa_asset_url('assets/img/logo2.png') ?>" alt="AdaAcara">
                </a>
                <div>
                <p class="wa-kicker">AdaAcara Guest Manager</p>
                    <strong><?= esc($pageTitleDisplay) ?></strong>
                </div>
            </div>
            <div class="wa-actions">
                <?= view('components/public_theme_toggle') ?>
                <?php if ($isLoggedIn && $pageId > 0): ?>
                    <a class="wa-btn" href="<?= site_url('editor/' . $pageId) ?>"><?= $aaIcon('edit') ?>Editor</a>
                    <a class="wa-btn" href="<?= site_url('dashboard') ?>"><?= $aaIcon('dashboard') ?>Dashboard</a>
                <?php elseif ($isLoggedIn): ?>
                    <a class="wa-btn" href="<?= site_url('dashboard') ?>"><?= $aaIcon('dashboard') ?>Dashboard</a>
                <?php else: ?>
                    <a class="wa-btn" href="<?= site_url('login') ?>"><?= $aaIcon('login') ?>Login</a>
                <?php endif; ?>
            </div>
        </header>

        <section class="wa-hero">
            <div>
                <p class="wa-kicker">Share WhatsApp</p>
                <h1 class="wa-title">Kirim undangan personal <span class="accent">tanpa terasa rumit.</span></h1>
                <p class="wa-subtitle">Kelola daftar tamu, personalisasi pesan, buka WhatsApp satu per satu, dan catat status pengiriman serta RSVP dari satu dashboard ringkas.</p>
            </div>
        </section>

        <section class="wa-card wa-invitation-card">
            <div class="wa-card-head">
                <div>
                    <h2>Data undangan</h2>
                    <p><?= $manualMode ? 'Isi link undangan manual agar pesan WhatsApp bisa dibuat personal.' : 'Data undangan otomatis diambil dari undangan yang sedang aktif.' ?></p>
                </div>
            </div>
            <div class="wa-form-grid">
                <label class="wa-field">Nama acara
                    <input id="manualTitleInput" class="wa-input" type="text" value="<?= esc($pageTitle) ?>" placeholder="Contoh: Pernikahan Dimas + Anggi">
                </label>
                <label class="wa-field">Link undangan
                    <input id="manualLinkInput" class="wa-input" type="url" value="<?= esc($publicUrl) ?>" placeholder="https://adaacara.com/u/nama-undangan">
                </label>
                <label class="wa-field">Tanggal acara
                    <input id="manualDateInput" class="wa-input" type="date" value="<?= esc($eventDate) ?>">
                </label>
            </div>
            <p class="wa-form-hint">Jika halaman ini dibuka dari dashboard/editor, link terisi otomatis. Jika dibuka langsung dari <strong>/share-whatsapp</strong>, kamu bisa mengisi link undangan sendiri.</p>
        </section>

        <section class="wa-grid">
            <main style="display:grid;gap:18px;">
                <section id="templateCard" class="wa-card wa-template-card">
                    <div class="wa-card-head">
                        <div>
                            <h2>Template kata pengantar</h2>
                            <p>Pilih kategori untuk langsung memakai template pesan yang sesuai.</p>
                        </div>
                        <div class="wa-actions">
                            <span id="templateLoadingLabel" class="wa-template-status">Memuat template...</span>
                            <button id="useTemplateBtn" class="wa-btn small emerald" type="button"><?= $aaIcon('check') ?>Gunakan Template</button>
                            <button id="saveTemplateBtn" class="wa-btn small" type="button"><?= $aaIcon('save') ?>Simpan Template</button>
                            <button id="resetTemplateBtn" class="wa-btn small" type="button"><?= $aaIcon('reset') ?>Reset Template</button>
                        </div>
                    </div>

                    <div class="wa-template-layout">
                        <div>
                            <label class="wa-field">Kategori undangan
                                <select id="categorySelect" class="wa-select"></select>
                            </label>
                            <div id="categoryList" class="wa-category-list" style="margin-top:12px;"></div>
                        </div>
                        <div style="display:grid;gap:12px;">
                            <label class="wa-field">Editor pesan
                                <textarea id="messageTemplate" class="wa-textarea"></textarea>
                            </label>
                            <div>
                                <p class="wa-kicker">Tambah Variabel</p>
                                <div id="variableList" class="wa-vars"></div>
                            </div>
                            <div class="wa-form-grid wa-ai-controls">
                                <label class="wa-field">Tone AI
                                    <select id="aiTone" class="wa-select">
                                        <option>Formal</option><option>Santai</option><option>Islami</option><option>Elegan</option>
                                        <option>Singkat</option><option>Akrab</option><option>Bahasa Jawa halus</option>
                                        <option>Bahasa Sunda</option><option>Bahasa Inggris</option>
                                    </select>
                                </label>
                                <label class="wa-field">Bahasa template
                                    <select id="languageSelect" class="wa-select">
                                        <option>Bahasa Indonesia</option><option>English</option><option>Jawa</option><option>Sunda</option><option>Minang</option>
                                    </select>
                                </label>
                                <button id="aiGenerateBtn" class="wa-btn primary" type="button" style="align-self:end;">AI Template Generator</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="wa-card">
                    <div class="wa-card-head">
                        <div>
                            <h2>Daftar tamu</h2>
                            <p>Pengiriman tetap manual satu per satu agar aman dan sesuai penggunaan WhatsApp.</p>
                        </div>
                        <div class="wa-actions">
                            <button id="openImportBtn" class="wa-btn primary" type="button"><?= $aaIcon('upload') ?>Import Excel</button>
                            <button id="openGuestBtn" class="wa-btn" type="button"><?= $aaIcon('user-plus') ?>Tambah Tamu Manual</button>
                            <button id="exportBtn" class="wa-btn gold" type="button"><?= $aaIcon('download') ?>Export Data</button>
                            <button id="downloadTemplateBtn" class="wa-btn" type="button"><?= $aaIcon('file') ?>Download Template Excel</button>
                        </div>
                    </div>

                    <section class="wa-stats" id="stats"></section>

                    <div class="wa-filters">
                        <input id="searchInput" class="wa-input" style="max-width:260px;" type="search" placeholder="Cari nama / nomor WhatsApp">
                        <select id="sendFilter" class="wa-select" style="max-width:210px;"></select>
                        <select id="rsvpFilter" class="wa-select" style="max-width:210px;"></select>
                        <select id="groupFilter" class="wa-select" style="max-width:180px;"></select>
                        <select id="sortSelect" class="wa-select" style="max-width:180px;">
                            <option value="name">Sort nama</option>
                            <option value="last_sent">Sort tanggal kirim</option>
                            <option value="send_status">Sort status</option>
                        </select>
                    </div>

                    <div class="wa-actions" style="margin:14px 0;">
                        <button id="bulkFollowBtn" class="wa-btn small" type="button"><?= $aaIcon('filter') ?>Tandai Follow Up</button>
                        <button id="bulkValidBtn" class="wa-btn small" type="button"><?= $aaIcon('sparkles') ?>Generate ulang link personal</button>
                        <button id="bulkExportBtn" class="wa-btn small" type="button"><?= $aaIcon('download') ?>Export terpilih</button>
                        <button id="bulkDeleteBtn" class="wa-btn small danger" type="button"><?= $aaIcon('trash') ?>Hapus terpilih</button>
                    </div>

                    <div id="emptyState" class="wa-empty">
                        <h3>Belum ada daftar tamu.</h3>
                        <p>Tambahkan manual atau import dari Excel untuk mulai mengirim undangan.</p>
                    </div>

                    <div class="wa-table-wrap">
                        <table class="wa-table">
                            <thead>
                                <tr>
                                    <th><input id="selectAll" type="checkbox"></th>
                                    <th>Nama Tamu</th><th>Sapaan</th><th>Nomor WhatsApp</th><th>Grup</th>
                                    <th>Kode</th><th>Meja</th><th>Link Personal</th><th>Status Kirim</th>
                                    <th>RSVP</th><th>Terakhir Dikirim</th><th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="guestRows"></tbody>
                        </table>
                    </div>
                    <div id="guestCards" class="wa-mobile-list"></div>
                </section>
            </main>

            <aside class="wa-preview">
                <div class="wa-card-head">
                    <div>
                        <h2>Preview WhatsApp</h2>
                        <p id="selectedGuestLabel">Pilih tamu untuk melihat personalisasi.</p>
                    </div>
                </div>
                <div class="wa-phone">
                    <div class="wa-phone-head">
                        <span class="wa-avatar">WA</span>
                        <span id="previewGuestName">Nama Tamu</span>
                    </div>
                    <div id="messagePreview" class="wa-bubble"></div>
                </div>
                <div class="wa-actions" style="margin-top:14px;">
                    <button id="sendPreviewBtn" class="wa-btn emerald" type="button"><?= $aaIcon('send') ?>Tes kirim WhatsApp</button>
                    <button id="copyMessageBtn" class="wa-btn" type="button"><?= $aaIcon('copy') ?>Salin Pesan</button>
                </div>
            </aside>
        </section>

        <div class="wa-bottom-bar">
            <button class="wa-btn primary" type="button" id="mobileImportBtn"><?= $aaIcon('upload') ?>Import</button>
            <button class="wa-btn emerald" type="button" id="mobileGuestBtn"><?= $aaIcon('user-plus') ?>Tambah Tamu</button>
        </div>
    </div>

    <div id="guestModal" class="wa-modal" role="dialog" aria-modal="true">
        <div class="wa-modal-card">
            <div class="wa-card-head">
                <div><h2 id="guestModalTitle">Tambah Tamu Manual</h2><p>Lengkapi data tamu untuk personalisasi pesan.</p></div>
                <button class="wa-btn small" data-close-modal type="button"><?= $aaIcon('reset') ?>Tutup</button>
            </div>
            <div class="wa-form-grid">
                <label class="wa-field">Nama Tamu<input id="guestNameInput" class="wa-input"></label>
                <label class="wa-field">Sapaan<input id="guestGreetingInput" class="wa-input" placeholder="Bapak/Ibu/Kak"></label>
                <label class="wa-field">Nomor WhatsApp<input id="guestPhoneInput" class="wa-input" placeholder="628xxxxxxxxxx"></label>
                <label class="wa-field">Grup Tamu<input id="guestGroupInput" class="wa-input" placeholder="Keluarga / Teman"></label>
                <label class="wa-field">Nomor Meja<input id="guestTableInput" class="wa-input"></label>
                <label class="wa-field">Status awal<select id="guestStatusInput" class="wa-select"></select></label>
            </div>
            <label class="wa-field" style="margin-top:12px;">Catatan Khusus<textarea id="guestNoteInput" class="wa-textarea" style="min-height:90px;"></textarea></label>
            <div class="wa-actions" style="margin-top:16px;"><button id="saveGuestBtn" class="wa-btn emerald" type="button"><?= $aaIcon('save') ?>Simpan Tamu</button></div>
        </div>
    </div>

    <div id="importModal" class="wa-modal" role="dialog" aria-modal="true">
        <div class="wa-modal-card">
            <div class="wa-card-head">
                <div><h2>Upload file Excel daftar tamu</h2><p>Format file .xlsx atau .csv. Preview dan validasi akan tampil sebelum disimpan.</p></div>
                <button class="wa-btn small" data-close-modal type="button"><?= $aaIcon('reset') ?>Tutup</button>
            </div>
            <label class="wa-import-drop" for="excelInput">
                <span><strong>Drag & drop atau klik untuk upload</strong><br>Kolom: nama_tamu, sapaan, nomor_whatsapp, grup_tamu, kode_tamu, nomor_meja, catatan_khusus</span>
                <input id="excelInput" type="file" accept=".xlsx,.csv" hidden>
            </label>
            <div id="importProgress" style="margin:12px 0;color:var(--aa-muted);font-weight:850;"></div>
            <div class="wa-table-wrap">
                <table class="wa-table" style="min-width:720px;">
                    <thead><tr><th>Nama</th><th>Nomor</th><th>Grup</th><th>Status Validasi</th></tr></thead>
                    <tbody id="importPreviewRows"></tbody>
                </table>
            </div>
            <div class="wa-actions" style="margin-top:14px;">
                <button id="importAllBtn" class="wa-btn emerald" type="button"><?= $aaIcon('upload') ?>Import Semua</button>
                <button id="importValidBtn" class="wa-btn primary" type="button"><?= $aaIcon('check') ?>Import Data Valid Saja</button>
                <button class="wa-btn" data-close-modal type="button"><?= $aaIcon('reset') ?>Batalkan</button>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="wa-modal" role="dialog" aria-modal="true">
        <div class="wa-modal-card" style="width:min(460px,100%);">
            <h2>Apakah pesan berhasil dikirim?</h2>
            <p>Setelah WhatsApp terbuka, tandai status pengiriman tamu ini agar tracking tetap rapi.</p>
            <div class="wa-actions">
                <button id="markSentBtn" class="wa-btn emerald" type="button"><?= $aaIcon('check') ?>Tandai Sudah Dikirim</button>
                <button id="markFailedBtn" class="wa-btn danger" type="button"><?= $aaIcon('trash') ?>Tandai Gagal</button>
                <button class="wa-btn" data-close-modal type="button"><?= $aaIcon('reset') ?>Ingatkan Nanti</button>
            </div>
        </div>
    </div>

    <div id="toast" class="wa-toast"></div>

    <script>
    (() => {
        const page = {
            id: <?= json_encode($pageId) ?>,
            title: <?= json_encode($pageTitle) ?>,
            eventDate: <?= json_encode($eventDate) ?>,
            publicUrl: <?= json_encode($publicUrl) ?>,
        };
        const storageKey = `aa-wa-guests-${page.id || 'manual'}`;
        const templateKey = `aa-wa-template-${page.id || 'manual'}`;
        const sendStatuses = ['Belum Dikirim', 'Dalam Antrian', 'Sudah Dikirim', 'Terkirim', 'Dibaca', 'Gagal', 'Follow Up', 'Tidak Aktif', 'Nomor Salah'];
        const rsvpStatuses = ['Belum Konfirmasi', 'Hadir', 'Tidak Hadir', 'Ragu-ragu', 'Diwakilkan'];
        const variables = ['{nama_tamu}', '{sapaan}', '{nama_pengundang}', '{nama_acara}', '{tanggal_acara}', '{waktu_acara}', '{lokasi_acara}', '{link_undangan}', '{kode_tamu}', '{nomor_meja}', '{catatan_khusus}'];
        const categories = ['Pernikahan', 'Lamaran', 'Khitanan', 'Ulang Tahun', 'Aqiqah', 'Syukuran', 'Wisuda', 'Reuni', 'Seminar / Webinar', 'Grand Opening', 'Gathering', 'Acara Keagamaan', 'Custom Event'];
        const iconHtml = {
            send: <?= json_encode($aaIcon('send')) ?>,
            edit: <?= json_encode($aaIcon('edit')) ?>,
            copy: <?= json_encode($aaIcon('copy')) ?>,
            trash: <?= json_encode($aaIcon('trash')) ?>,
            sparkles: <?= json_encode($aaIcon('sparkles')) ?>,
        };
        const baseTemplate = `Assalamu'alaikum Wr. Wb.\n\nYth. {sapaan} {nama_tamu}\n\nDengan penuh rasa syukur, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara {nama_acara} kami yang akan diselenggarakan pada:\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nBerikut link undangan digital kami:\n{link_undangan}\n\nMerupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.\n\nHormat kami,\n{nama_pengundang}`;
        const categoryTemplates = {
            'Pernikahan': `Assalamu'alaikum Wr. Wb.\n\nYth. {sapaan} {nama_tamu}\n\nDengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara pernikahan kami:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nDetail undangan digital dapat dibuka melalui link berikut:\n{link_undangan}\n\nMerupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.\n\nHormat kami,\n{nama_pengundang}`,
            'Lamaran': `Assalamu'alaikum Wr. Wb.\n\nYth. {sapaan} {nama_tamu}\n\nDengan penuh rasa syukur, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara lamaran:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nInformasi lengkap undangan tersedia di:\n{link_undangan}\n\nKehadiran dan doa Bapak/Ibu/Saudara/i akan menjadi kebahagiaan bagi keluarga kami.\n\nHormat kami,\n{nama_pengundang}`,
            'Khitanan': `Assalamu'alaikum Wr. Wb.\n\nYth. {sapaan} {nama_tamu}\n\nDengan rasa syukur, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara khitanan:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nLink undangan digital:\n{link_undangan}\n\nSemoga kehadiran dan doa Bapak/Ibu/Saudara/i membawa keberkahan bagi keluarga kami.\n\nHormat kami,\n{nama_pengundang}`,
            'Ulang Tahun': `Halo {sapaan} {nama_tamu},\n\nDengan senang hati kami mengundang Anda untuk hadir dan merayakan acara ulang tahun:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nDetail acara dapat dilihat melalui link berikut:\n{link_undangan}\n\nKehadiran Anda akan membuat acara ini semakin berkesan.\n\nSalam hangat,\n{nama_pengundang}`,
            'Aqiqah': `Assalamu'alaikum Wr. Wb.\n\nYth. {sapaan} {nama_tamu}\n\nDengan memohon ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara aqiqah:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nUndangan digital:\n{link_undangan}\n\nKehadiran dan doa Bapak/Ibu/Saudara/i sangat berarti bagi kami sekeluarga.\n\nHormat kami,\n{nama_pengundang}`,
            'Syukuran': `Assalamu'alaikum Wr. Wb.\n\nYth. {sapaan} {nama_tamu}\n\nDengan penuh syukur, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara syukuran:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nDetail undangan:\n{link_undangan}\n\nSemoga kehadiran dan doa Bapak/Ibu/Saudara/i menambah keberkahan acara kami.\n\nHormat kami,\n{nama_pengundang}`,
            'Wisuda': `Yth. {sapaan} {nama_tamu}\n\nDengan rasa syukur dan bahagia, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara wisuda:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nLink undangan digital:\n{link_undangan}\n\nKehadiran Bapak/Ibu/Saudara/i akan menjadi kehormatan dan kebahagiaan bagi kami.\n\nHormat kami,\n{nama_pengundang}`,
            'Reuni': `Halo {sapaan} {nama_tamu},\n\nKami mengundang Anda untuk hadir dalam acara reuni:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nDetail acara dan undangan dapat dilihat di:\n{link_undangan}\n\nMari berkumpul kembali, berbagi cerita, dan merayakan kebersamaan.\n\nSalam hangat,\n{nama_pengundang}`,
            'Seminar / Webinar': `Halo {sapaan} {nama_tamu},\n\nKami mengundang Anda untuk mengikuti acara seminar/webinar:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nLokasi/Akses: {lokasi_acara}\n\nDetail undangan:\n{link_undangan}\n\nKami menantikan kehadiran Anda dalam acara ini.\n\nHormat kami,\n{nama_pengundang}`,
            'Grand Opening': `Yth. {sapaan} {nama_tamu}\n\nDengan hormat, kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara grand opening:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nLink undangan:\n{link_undangan}\n\nKehadiran Bapak/Ibu/Saudara/i merupakan kehormatan bagi kami.\n\nHormat kami,\n{nama_pengundang}`,
            'Gathering': `Halo {sapaan} {nama_tamu},\n\nKami mengundang Anda untuk hadir dalam acara gathering:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nDetail acara:\n{link_undangan}\n\nSemoga acara ini menjadi momen kebersamaan yang menyenangkan.\n\nSalam hangat,\n{nama_pengundang}`,
            'Acara Keagamaan': `Assalamu'alaikum Wr. Wb.\n\nYth. {sapaan} {nama_tamu}\n\nDengan hormat, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara keagamaan:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nInformasi lengkap:\n{link_undangan}\n\nSemoga kehadiran dan doa Bapak/Ibu/Saudara/i membawa keberkahan bagi kita semua.\n\nHormat kami,\n{nama_pengundang}`,
            'Custom Event': `Yth. {sapaan} {nama_tamu}\n\nKami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara:\n\n{nama_acara}\n\nTanggal: {tanggal_acara}\nWaktu: {waktu_acara}\nTempat: {lokasi_acara}\n\nDetail undangan digital:\n{link_undangan}\n\nKehadiran Bapak/Ibu/Saudara/i sangat berarti bagi kami.\n\nHormat kami,\n{nama_pengundang}`,
        };
        function eventTitle() {
            return page.title || 'Acara Kami';
        }
        function templateFor(category) {
            return categoryTemplates[category] || baseTemplate;
        }

        let guests = loadGuests();
        let selectedId = guests[0]?.id || null;
        let pendingSendId = null;
        let editingId = null;
        let importRows = [];

        const $ = id => document.getElementById(id);
        const toast = message => {
            $('toast').textContent = message;
            $('toast').classList.add('show');
            setTimeout(() => $('toast').classList.remove('show'), 2600);
        };

        function syncManualInvitationInputs() {
            page.title = $('manualTitleInput')?.value.trim() || '';
            page.publicUrl = $('manualLinkInput')?.value.trim() || '';
            page.eventDate = $('manualDateInput')?.value || '';
            renderPreview();
            renderGuests();
        }

        function loadGuests() {
            try {
                const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
                if (Array.isArray(saved) && saved.length) return saved;
            } catch (e) {}
            return [
                demoGuest('Afifah Kurnia', 'Ibu', '6281234567890', 'Keluarga', 'A001', 'VIP 1'),
                demoGuest('Raka Pratama', 'Bapak', '6289876543210', 'Teman', 'A002', '12'),
                demoGuest('Nadia Putri', 'Kak', '0812xx', 'Kantor', 'A003', '', 'Perlu cek nomor', 'Nomor Salah'),
            ];
        }

        function demoGuest(name, greeting, phone, group, code, table, note = '', sendStatus = 'Belum Dikirim') {
            return {
                id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now() + Math.random()),
                name, greeting, phone, group, code, table, note,
                sendStatus, rsvpStatus: 'Belum Konfirmasi',
                lastSent: '', attempts: 0, selected: false,
            };
        }

        function saveGuests() { localStorage.setItem(storageKey, JSON.stringify(guests)); }
        function activeGuest() { return guests.find(g => g.id === selectedId) || guests[0] || null; }
        function normalizePhone(phone) {
            let value = String(phone || '').replace(/[^\d+]/g, '');
            if (value.startsWith('+')) value = value.slice(1);
            if (value.startsWith('0')) value = '62' + value.slice(1);
            if (value.startsWith('8')) value = '62' + value;
            return value;
        }
        function isValidPhone(phone) { return /^62\d{8,14}$/.test(normalizePhone(phone)); }
        function personalLink(guest) {
            const baseUrl = String(page.publicUrl || '').trim();
            if (!baseUrl) return '';
            return baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'to=' + encodeURIComponent(guest.name || 'Tamu Undangan');
        }
        function badgeClass(status) {
            if (['Sudah Dikirim', 'Terkirim', 'Dibaca', 'Hadir'].includes(status)) return 'green';
            if (['Dalam Antrian'].includes(status)) return 'blue';
            if (['Follow Up', 'Ragu-ragu', 'Diwakilkan'].includes(status)) return 'yellow';
            if (['Gagal', 'Tidak Aktif', 'Nomor Salah', 'Tidak Hadir'].includes(status)) return 'red';
            return 'gray';
        }
        function formatEventDate(value) {
            if (!value) return '-';
            const date = new Date(`${value}T00:00:00`);
            if (Number.isNaN(date.getTime())) return value;
            return new Intl.DateTimeFormat('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }).format(date);
        }
        function renderMessage(guest = activeGuest()) {
            if (!guest) return '';
            const replacements = {
                '{nama_tamu}': guest.name || 'Tamu Undangan',
                '{sapaan}': guest.greeting || 'Bapak/Ibu/Saudara/i',
                '{nama_pengundang}': 'Keluarga Besar',
                '{nama_acara}': eventTitle(),
                '{tanggal_acara}': formatEventDate(page.eventDate),
                '{waktu_acara}': '09.00 WIB',
                '{lokasi_acara}': 'Lokasi acara',
                '{link_undangan}': personalLink(guest),
                '{kode_tamu}': guest.code || '-',
                '{nomor_meja}': guest.table || '-',
                '{catatan_khusus}': guest.note || '-',
            };
            return variables.reduce((text, key) => text.split(key).join(replacements[key] || ''), $('messageTemplate').value || baseTemplate);
        }
        function filteredGuests() {
            const query = $('searchInput').value.trim().toLowerCase();
            const send = $('sendFilter').value;
            const rsvp = $('rsvpFilter').value;
            const group = $('groupFilter').value;
            let rows = guests.filter(g => {
                if (send && g.sendStatus !== send) return false;
                if (rsvp && g.rsvpStatus !== rsvp) return false;
                if (group && g.group !== group) return false;
                if (!query) return true;
                return [g.name, g.phone, g.group, g.code].join(' ').toLowerCase().includes(query);
            });
            const sort = $('sortSelect').value;
            rows.sort((a, b) => String(a[sort === 'name' ? 'name' : sort] || '').localeCompare(String(b[sort === 'name' ? 'name' : sort] || '')));
            return rows;
        }
        function renderStats() {
            const data = [
                ['Total Tamu', guests.length],
                ['Belum Dikirim', guests.filter(g => g.sendStatus === 'Belum Dikirim').length],
                ['Sudah Dikirim', guests.filter(g => ['Sudah Dikirim', 'Terkirim', 'Dibaca'].includes(g.sendStatus)).length],
                ['Dibaca / Dikonfirmasi', guests.filter(g => g.sendStatus === 'Dibaca').length],
                ['Gagal Kirim', guests.filter(g => ['Gagal', 'Nomor Salah'].includes(g.sendStatus)).length],
                ['RSVP Hadir', guests.filter(g => g.rsvpStatus === 'Hadir').length],
                ['RSVP Tidak Hadir', guests.filter(g => g.rsvpStatus === 'Tidak Hadir').length],
            ];
            $('stats').innerHTML = data.map(([label, count]) => `<article class="wa-stat"><span>${label}</span><strong>${count}</strong></article>`).join('');
        }
        function renderFilters() {
            $('sendFilter').innerHTML = '<option value="">Semua status kirim</option>' + sendStatuses.map(s => `<option>${s}</option>`).join('');
            $('rsvpFilter').innerHTML = '<option value="">Semua RSVP</option>' + rsvpStatuses.map(s => `<option>${s}</option>`).join('');
            $('guestStatusInput').innerHTML = sendStatuses.map(s => `<option>${s}</option>`).join('');
            const groups = [...new Set(guests.map(g => g.group).filter(Boolean))];
            $('groupFilter').innerHTML = '<option value="">Semua grup</option>' + groups.map(g => `<option>${g}</option>`).join('');
        }
        function actionButtons(id) {
            return `<div class="wa-row-actions">
                <button class="wa-btn small emerald" data-action="send" data-id="${id}">${iconHtml.send}Kirim</button>
                <button class="wa-btn small" data-action="edit" data-id="${id}">${iconHtml.edit}Edit</button>
                <button class="wa-btn small" data-action="duplicate" data-id="${id}">${iconHtml.sparkles}Duplikat</button>
                <button class="wa-btn small" data-action="copy" data-id="${id}">${iconHtml.copy}Salin Link</button>
                <button class="wa-btn small danger" data-action="delete" data-id="${id}">${iconHtml.trash}Hapus</button>
            </div>`;
        }
        function renderGuests() {
            const rows = filteredGuests();
            $('emptyState').classList.toggle('visible', guests.length === 0);
            $('guestRows').innerHTML = rows.map(g => `
                <tr>
                    <td><input type="checkbox" data-select="${g.id}" ${g.selected ? 'checked' : ''}></td>
                    <td><strong>${escapeHtml(g.name)}</strong>${!isValidPhone(g.phone) ? '<br><span class="wa-badge red">Nomor invalid</span>' : ''}</td>
                    <td>${escapeHtml(g.greeting || '-')}</td>
                    <td>${escapeHtml(normalizePhone(g.phone) || '-')}</td>
                    <td>${escapeHtml(g.group || '-')}</td>
                    <td>${escapeHtml(g.code || '-')}</td>
                    <td>${escapeHtml(g.table || '-')}</td>
                    <td><button class="wa-btn small" data-action="copy" data-id="${g.id}">${iconHtml.copy}Salin Link</button></td>
                    <td><button class="wa-badge ${badgeClass(g.sendStatus)}" data-action="cycle-send" data-id="${g.id}">${g.sendStatus}</button></td>
                    <td><button class="wa-badge ${badgeClass(g.rsvpStatus)}" data-action="cycle-rsvp" data-id="${g.id}">${g.rsvpStatus}</button></td>
                    <td>${g.lastSent || '-'}</td>
                    <td>${actionButtons(g.id)}</td>
                </tr>`).join('');
            $('guestCards').innerHTML = rows.map(g => `<article class="wa-card"><h3>${escapeHtml(g.name)}</h3><p>${escapeHtml(normalizePhone(g.phone) || '-')} · ${escapeHtml(g.group || '-')}</p><p><span class="wa-badge ${badgeClass(g.sendStatus)}">${g.sendStatus}</span> <span class="wa-badge ${badgeClass(g.rsvpStatus)}">${g.rsvpStatus}</span></p>${actionButtons(g.id)}</article>`).join('');
            renderPreview();
        }
        function renderPreview() {
            const guest = activeGuest();
            $('previewGuestName').textContent = guest?.name || 'Nama Tamu';
            $('selectedGuestLabel').textContent = guest ? `${guest.greeting || ''} ${guest.name}` : 'Pilih tamu untuk melihat personalisasi.';
            $('messagePreview').textContent = guest ? renderMessage(guest) : 'Belum ada tamu.';
        }
        function setActiveCategory(category) {
            $('categorySelect').value = category;
            document.querySelectorAll('.wa-cat').forEach(button => {
                button.classList.toggle('active', button.dataset.category === category);
            });
        }
        function applyTemplateCategory(category, withLoading = true) {
            const selectedCategory = categories.includes(category) ? category : categories[0];
            setActiveCategory(selectedCategory);
            const apply = () => {
                $('messageTemplate').value = templateFor(selectedCategory);
                $('templateCard')?.classList.remove('is-loading');
                renderPreview();
            };

            if (!withLoading) {
                apply();
                return;
            }

            $('templateCard')?.classList.add('is-loading');
            window.setTimeout(apply, 180);
        }
        function refresh() { renderStats(); renderFilters(); renderGuests(); saveGuests(); }
        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
        }
        function openModal(id) { $(id).classList.add('open'); }
        function closeModals() { document.querySelectorAll('.wa-modal.open').forEach(m => m.classList.remove('open')); }
        function openGuestModal(guest = null) {
            editingId = guest?.id || null;
            $('guestModalTitle').textContent = guest ? 'Edit Data Tamu' : 'Tambah Tamu Manual';
            $('guestNameInput').value = guest?.name || '';
            $('guestGreetingInput').value = guest?.greeting || '';
            $('guestPhoneInput').value = guest?.phone || '';
            $('guestGroupInput').value = guest?.group || '';
            $('guestTableInput').value = guest?.table || '';
            $('guestNoteInput').value = guest?.note || '';
            $('guestStatusInput').value = guest?.sendStatus || 'Belum Dikirim';
            openModal('guestModal');
        }
        function sendGuest(guest) {
            if (!guest) return;
            selectedId = guest.id;
            const phone = normalizePhone(guest.phone);
            if (!isValidPhone(phone)) {
                toast('Nomor WhatsApp belum valid.');
                return;
            }
            pendingSendId = guest.id;
            const url = `https://wa.me/${phone}?text=${encodeURIComponent(renderMessage(guest))}`;
            window.open(url, '_blank', 'noopener');
            openModal('confirmModal');
        }
        function cycle(list, value) {
            const index = list.indexOf(value);
            return list[(index + 1) % list.length] || list[0];
        }
        function downloadCsv(name, rows) {
            const csv = rows.map(row => row.map(value => `"${String(value ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = name;
            link.click();
            URL.revokeObjectURL(link.href);
        }
        function exportRows(rows = guests) {
            downloadCsv('laporan-share-whatsapp.csv', [
                ['nama_tamu','sapaan','nomor_whatsapp','grup_tamu','kode_tamu','nomor_meja','status_kirim','status_rsvp','waktu_kirim','catatan'],
                ...rows.map(g => [g.name,g.greeting,normalizePhone(g.phone),g.group,g.code,g.table,g.sendStatus,g.rsvpStatus,g.lastSent,g.note])
            ]);
        }
        function validateImportRow(row) {
            const name = row.nama_tamu || row.nama || row.name || '';
            const phone = row.nomor_whatsapp || row.whatsapp || row.phone || '';
            const duplicate = guests.some(g => normalizePhone(g.phone) === normalizePhone(phone));
            const valid = name && isValidPhone(phone) && !duplicate;
            return { ...row, name, phone, valid, duplicate, reason: !name ? 'Nama kosong' : !isValidPhone(phone) ? 'Nomor invalid' : duplicate ? 'Duplikat' : 'Valid' };
        }
        function renderImportPreview() {
            $('importPreviewRows').innerHTML = importRows.map(row => `<tr><td>${escapeHtml(row.name)}</td><td>${escapeHtml(normalizePhone(row.phone))}</td><td>${escapeHtml(row.grup_tamu || row.group || '-')}</td><td><span class="wa-badge ${row.valid ? 'green' : 'red'}">${row.reason}</span></td></tr>`).join('');
            $('importProgress').textContent = `${importRows.length} baris terbaca, ${importRows.filter(r => r.valid).length} valid. Mapping kolom otomatis diterapkan.`;
        }
        async function parseImportFile(file) {
            $('importProgress').textContent = 'Membaca file...';
            const buffer = await file.arrayBuffer();
            const workbook = XLSX.read(buffer, {type: 'array'});
            const sheet = workbook.Sheets[workbook.SheetNames[0]];
            const rows = XLSX.utils.sheet_to_json(sheet, {defval: ''});
            importRows = rows.map(validateImportRow);
            renderImportPreview();
        }
        function importData(validOnly) {
            const rows = validOnly ? importRows.filter(r => r.valid) : importRows;
            rows.forEach(row => guests.push(demoGuest(row.name, row.sapaan || row.greeting || smartGreeting(row.name), normalizePhone(row.phone), row.grup_tamu || row.group || '', row.kode_tamu || '', row.nomor_meja || '', row.catatan_khusus || '', row.valid ? 'Belum Dikirim' : 'Nomor Salah')));
            closeModals();
            toast(`${rows.length} tamu berhasil diimport.`);
            refresh();
        }
        function smartGreeting(name) {
            const lower = String(name || '').toLowerCase();
            if (lower.includes('pak') || lower.includes('bapak')) return 'Bapak';
            if (lower.includes('bu') || lower.includes('ibu')) return 'Ibu';
            if (lower.includes('mas')) return 'Mas';
            if (lower.includes('mbak')) return 'Mbak';
            return 'Saudara/i';
        }

        categories.forEach(category => {
            $('categorySelect').insertAdjacentHTML('beforeend', `<option>${category}</option>`);
            $('categoryList').insertAdjacentHTML('beforeend', `<button class="wa-cat" type="button" data-category="${category}">${category}</button>`);
        });
        variables.forEach(variable => $('variableList').insertAdjacentHTML('beforeend', `<button class="wa-var" type="button" title="Klik untuk tambah variabel">${variable}</button>`));
        $('messageTemplate').value = localStorage.getItem(templateKey) || templateFor(categories[0]);
        setActiveCategory(categories[0]);

        $('categorySelect').addEventListener('change', e => {
            applyTemplateCategory(e.target.value);
        });
        $('categoryList').addEventListener('click', e => {
            const button = e.target.closest('[data-category]');
            if (!button) return;
            applyTemplateCategory(button.dataset.category);
        });
        $('useTemplateBtn').addEventListener('click', () => applyTemplateCategory($('categorySelect').value));
        $('saveTemplateBtn').addEventListener('click', () => { localStorage.setItem(templateKey, $('messageTemplate').value); toast('Template tersimpan.'); });
        $('resetTemplateBtn').addEventListener('click', () => { localStorage.removeItem(templateKey); $('messageTemplate').value = templateFor($('categorySelect').value); renderPreview(); });
        $('aiGenerateBtn').addEventListener('click', () => {
            const tone = $('aiTone').value;
            $('messageTemplate').value = `${tone === 'Islami' ? "Assalamu'alaikum Wr. Wb." : 'Halo'}\n\nYth. {sapaan} {nama_tamu},\n\nDengan ${tone.toLowerCase()} kami mengundang Anda untuk hadir dalam acara {nama_acara} pada {tanggal_acara} pukul {waktu_acara} di {lokasi_acara}.\n\nLink undangan personal:\n{link_undangan}\n\nKehadiran dan doa Anda sangat berarti bagi kami.\n\nHormat kami,\n{nama_pengundang}`;
            renderPreview();
            toast('Template AI siap dipakai.');
        });
        $('variableList').addEventListener('click', e => {
            const button = e.target.closest('.wa-var');
            if (!button) return;
            const input = $('messageTemplate');
            const start = input.selectionStart || input.value.length;
            input.value = input.value.slice(0, start) + button.textContent + input.value.slice(input.selectionEnd || start);
            input.focus();
            renderPreview();
        });
        $('messageTemplate').addEventListener('input', renderPreview);
        ['manualTitleInput','manualLinkInput','manualDateInput'].forEach(id => $(id)?.addEventListener('input', syncManualInvitationInputs));
        ['searchInput','sendFilter','rsvpFilter','groupFilter','sortSelect'].forEach(id => $(id).addEventListener('input', renderGuests));
        $('openGuestBtn').addEventListener('click', () => openGuestModal());
        $('mobileGuestBtn').addEventListener('click', () => openGuestModal());
        $('openImportBtn').addEventListener('click', () => openModal('importModal'));
        $('mobileImportBtn').addEventListener('click', () => openModal('importModal'));
        document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', closeModals));
        $('saveGuestBtn').addEventListener('click', () => {
            const data = {
                name: $('guestNameInput').value.trim(),
                greeting: $('guestGreetingInput').value.trim() || smartGreeting($('guestNameInput').value),
                phone: normalizePhone($('guestPhoneInput').value),
                group: $('guestGroupInput').value.trim(),
                table: $('guestTableInput').value.trim(),
                note: $('guestNoteInput').value.trim(),
                sendStatus: $('guestStatusInput').value,
            };
            if (!data.name) return toast('Nama tamu wajib diisi.');
            if (editingId) Object.assign(guests.find(g => g.id === editingId), data);
            else guests.unshift({id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now()), ...data, code: `G${guests.length + 1}`, rsvpStatus: 'Belum Konfirmasi', lastSent: '', attempts: 0, selected: false});
            closeModals();
            toast('Data tamu tersimpan.');
            refresh();
        });
        document.body.addEventListener('click', e => {
            const action = e.target.closest('[data-action]')?.dataset.action;
            const id = e.target.closest('[data-id]')?.dataset.id;
            if (!action || !id) return;
            const guest = guests.find(g => g.id === id);
            if (!guest) return;
            if (action === 'send') sendGuest(guest);
            if (action === 'preview') { selectedId = id; renderPreview(); }
            if (action === 'edit') openGuestModal(guest);
            if (action === 'duplicate') guests.unshift({...guest, id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now()), name: guest.name + ' Copy'});
            if (action === 'delete') {
                aaConfirm('Hapus data tamu ini?', {
                    title: 'Hapus Tamu',
                    okText: 'Hapus',
                    cancelText: 'Batal',
                    danger: true
                }).then(ok => {
                    if (!ok) return;
                    guests = guests.filter(g => g.id !== id);
                    refresh();
                    toast('Data tamu dihapus.');
                });
                return;
            }
            if (action === 'copy') navigator.clipboard.writeText(personalLink(guest)).then(() => toast('Link personal disalin.'));
            if (action === 'cycle-send') guest.sendStatus = cycle(sendStatuses, guest.sendStatus);
            if (action === 'cycle-rsvp') guest.rsvpStatus = cycle(rsvpStatuses, guest.rsvpStatus);
            refresh();
        });
        document.body.addEventListener('change', e => {
            if (e.target.matches('[data-select]')) {
                const guest = guests.find(g => g.id === e.target.dataset.select);
                if (guest) guest.selected = e.target.checked;
                saveGuests();
            }
        });
        $('selectAll').addEventListener('change', e => { filteredGuests().forEach(g => g.selected = e.target.checked); refresh(); });
        $('sendPreviewBtn').addEventListener('click', () => sendGuest(activeGuest()));
        $('copyMessageBtn').addEventListener('click', () => navigator.clipboard.writeText(renderMessage()).then(() => toast('Pesan disalin.')));
        $('markSentBtn').addEventListener('click', () => {
            const guest = guests.find(g => g.id === pendingSendId);
            if (guest) { guest.sendStatus = 'Sudah Dikirim'; guest.lastSent = new Date().toLocaleString('id-ID'); guest.attempts += 1; }
            closeModals(); refresh(); toast('Status ditandai sudah dikirim.');
        });
        $('markFailedBtn').addEventListener('click', () => {
            const guest = guests.find(g => g.id === pendingSendId);
            if (guest) { guest.sendStatus = 'Gagal'; guest.attempts += 1; }
            closeModals(); refresh(); toast('Status ditandai gagal.');
        });
        $('excelInput').addEventListener('change', e => { if (e.target.files[0]) parseImportFile(e.target.files[0]).catch(() => toast('File gagal dibaca.')); });
        $('importAllBtn').addEventListener('click', () => importData(false));
        $('importValidBtn').addEventListener('click', () => importData(true));
        $('exportBtn').addEventListener('click', () => exportRows());
        $('bulkExportBtn').addEventListener('click', () => exportRows(guests.filter(g => g.selected)));
        $('downloadTemplateBtn').addEventListener('click', () => downloadCsv('template-tamu-whatsapp.csv', [['nama_tamu','sapaan','nomor_whatsapp','grup_tamu','kode_tamu','nomor_meja','catatan_khusus'], ['Afifah Kurnia','Ibu','6281234567890','Keluarga','A001','VIP 1','']]));
        $('bulkFollowBtn').addEventListener('click', () => { guests.filter(g => g.selected).forEach(g => g.sendStatus = 'Follow Up'); refresh(); });
        $('bulkValidBtn').addEventListener('click', () => { guests.filter(g => g.selected).forEach((g, i) => g.code = g.code || `G${i + 1}`); refresh(); toast('Link personal siap digenerate ulang.'); });
        $('bulkDeleteBtn').addEventListener('click', () => {
            aaConfirm('Hapus semua tamu terpilih?', {
                title: 'Hapus Massal',
                okText: 'Hapus',
                cancelText: 'Batal',
                danger: true
            }).then(ok => {
                if (!ok) return;
                guests = guests.filter(g => !g.selected);
                refresh();
                toast('Tamu terpilih dihapus.');
            });
        });
        if ($('darkToggle')) {
            $('darkToggle').addEventListener('click', () => {
                const shell = document.querySelector('.wa-shell');
                shell.dataset.theme = shell.dataset.theme === 'dark' ? 'light' : 'dark';
            });
        }

        refresh();
    })();
    </script>
</body>
</html>
