<?php
    $loaderVariant = (string) ($variant ?? 'default');
    $loaderLabel = (string) ($label ?? 'Memuat halaman');
    $loaderId = 'aaPageLoader-' . bin2hex(random_bytes(4));

    $renderBars = static function (int $count, string $class = ''): string {
        $html = '';
        for ($i = 0; $i < $count; $i++) {
            $html .= '<span class="aa-page-loader-bar ' . esc($class, 'attr') . '"></span>';
        }
        return $html;
    };

    $renderCards = static function (int $count, string $class = ''): string {
        $html = '';
        for ($i = 0; $i < $count; $i++) {
            $html .= '<span class="aa-page-loader-card ' . esc($class, 'attr') . '"></span>';
        }
        return $html;
    };
?>
<noscript><style>.aa-page-loader{display:none!important}</style></noscript>
<style>
    .aa-page-loader {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: grid;
        place-items: center;
        background:
            radial-gradient(circle at 18% 0%, rgba(204, 251, 241, .86), transparent 30rem),
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 250, 252, .96));
        color: #0f172a;
        opacity: 1;
        transition: opacity .24s ease, visibility .24s ease;
    }

    .aa-page-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .aa-page-loader-shell {
        width: min(720px, calc(100% - 36px));
        border: 1px solid rgba(204, 251, 241, .92);
        border-radius: 28px;
        background: rgba(255, 255, 255, .84);
        padding: 22px;
        box-shadow: 0 30px 90px rgba(15, 23, 42, .14);
        backdrop-filter: blur(18px);
    }

    .aa-page-loader-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
    }

    .aa-page-loader-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .aa-page-loader-logo {
        width: 118px;
        height: auto;
        object-fit: contain;
    }

    .aa-page-loader-text {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .aa-page-loader-pulse {
        position: relative;
        width: 42px;
        height: 42px;
        flex: 0 0 auto;
        border-radius: 16px;
        background: #ecfdf5;
    }

    .aa-page-loader-pulse::after {
        content: "";
        position: absolute;
        inset: 11px;
        border-radius: 999px;
        background: #0f766e;
        animation: aa-page-loader-pulse 1s ease-in-out infinite;
    }

    .aa-page-loader-skeleton {
        display: grid;
        gap: 12px;
    }

    .aa-page-loader-row {
        display: grid;
        gap: 12px;
    }

    .aa-page-loader-grid {
        display: grid;
        gap: 12px;
    }

    .aa-page-loader-grid.cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .aa-page-loader-grid.cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .aa-page-loader-grid.cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .aa-page-loader-bar,
    .aa-page-loader-card {
        position: relative;
        display: block;
        overflow: hidden;
        border-radius: 18px;
        background: #e2e8f0;
    }

    .aa-page-loader-bar::after,
    .aa-page-loader-card::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .72), transparent);
        animation: aa-page-loader-shimmer 1.25s ease-in-out infinite;
    }

    .aa-page-loader-bar {
        height: 14px;
    }

    .aa-page-loader-bar.short {
        width: 42%;
    }

    .aa-page-loader-bar.medium {
        width: 68%;
    }

    .aa-page-loader-card {
        min-height: 84px;
        border: 1px solid rgba(226, 232, 240, .9);
        background: linear-gradient(180deg, #f8fafc, #eaf2ef);
    }

    .aa-page-loader-card.tall {
        min-height: 180px;
    }

    .aa-page-loader-card.template {
        aspect-ratio: 6 / 10;
        min-height: 0;
    }

    .aa-page-loader-card.table {
        min-height: 48px;
        border-radius: 14px;
    }

    .aa-page-loader-progress {
        position: relative;
        overflow: hidden;
        height: 4px;
        margin-top: 18px;
        border-radius: 999px;
        background: #dbeafe;
    }

    .aa-page-loader-progress::after {
        content: "";
        position: absolute;
        inset: 0;
        width: 42%;
        border-radius: inherit;
        background: linear-gradient(90deg, #0f766e, #14b8a6);
        animation: aa-page-loader-progress 1.1s ease-in-out infinite;
    }

    @keyframes aa-page-loader-shimmer {
        100% {
            transform: translateX(100%);
        }
    }

    @keyframes aa-page-loader-pulse {
        0%, 100% {
            transform: scale(.72);
            opacity: .68;
        }
        50% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes aa-page-loader-progress {
        0% {
            transform: translateX(-110%);
        }
        100% {
            transform: translateX(260%);
        }
    }

    @media (max-width: 680px) {
        .aa-page-loader-shell {
            width: min(420px, calc(100% - 28px));
            padding: 18px;
        }

        .aa-page-loader-grid.cols-3,
        .aa-page-loader-grid.cols-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-page-loader-grid.cols-2 {
            grid-template-columns: 1fr;
        }

        .aa-page-loader-card.hide-mobile {
            display: none;
        }
    }
</style>
<div id="<?= esc($loaderId, 'attr') ?>" class="aa-page-loader" role="status" aria-live="polite" aria-label="<?= esc($loaderLabel, 'attr') ?>">
    <div class="aa-page-loader-shell">
        <div class="aa-page-loader-head">
            <div class="aa-page-loader-brand">
                <img class="aa-page-loader-logo" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                <p class="aa-page-loader-text"><?= esc($loaderLabel) ?></p>
            </div>
            <span class="aa-page-loader-pulse" aria-hidden="true"></span>
        </div>

        <div class="aa-page-loader-skeleton" aria-hidden="true">
            <?php if ($loaderVariant === 'templates'): ?>
                <div class="aa-page-loader-grid cols-4">
                    <?= $renderCards(8, 'template') ?>
                </div>
            <?php elseif ($loaderVariant === 'dashboard'): ?>
                <div class="aa-page-loader-row">
                    <?= $renderBars(1, 'medium') ?>
                    <div class="aa-page-loader-grid cols-4"><?= $renderCards(4) ?></div>
                    <div class="aa-page-loader-grid cols-3"><?= $renderCards(3, 'tall') ?></div>
                </div>
            <?php elseif ($loaderVariant === 'seller'): ?>
                <div class="aa-page-loader-grid cols-4"><?= $renderCards(4) ?></div>
                <div class="aa-page-loader-grid cols-2"><?= $renderCards(2, 'tall') ?></div>
                <?= $renderCards(1) ?>
            <?php elseif ($loaderVariant === 'admin'): ?>
                <?= $renderBars(1, 'medium') ?>
                <div class="aa-page-loader-grid cols-4"><?= $renderCards(4) ?></div>
                <?= $renderCards(5, 'table') ?>
            <?php elseif ($loaderVariant === 'plans'): ?>
                <?= $renderBars(1, 'medium') ?>
                <div class="aa-page-loader-grid cols-3"><?= $renderCards(3, 'tall') ?></div>
            <?php elseif ($loaderVariant === 'home'): ?>
                <?= $renderBars(1, 'medium') ?>
                <?= $renderBars(1, 'short') ?>
                <div class="aa-page-loader-grid cols-3"><?= $renderCards(3) ?></div>
            <?php else: ?>
                <?= $renderBars(1, 'medium') ?>
                <?= $renderBars(1, 'short') ?>
                <div class="aa-page-loader-grid cols-3"><?= $renderCards(3) ?></div>
            <?php endif ?>
        </div>

        <div class="aa-page-loader-progress" aria-hidden="true"></div>
    </div>
</div>
<script>
    (function() {
        var loader = document.getElementById(<?= json_encode($loaderId) ?>);
        if (!loader) return;

        var shownAt = Date.now();
        var minVisible = 240;
        var hidden = false;

        function hideLoader() {
            if (hidden) return;
            hidden = true;
            var wait = Math.max(0, minVisible - (Date.now() - shownAt));
            window.setTimeout(function() {
                loader.classList.add('is-hidden');
            }, wait);
        }

        function showLoader() {
            if (!loader || !loader.parentNode) return;
            hidden = false;
            shownAt = Date.now();
            loader.classList.remove('is-hidden');
        }

        if (document.readyState === 'complete') {
            hideLoader();
        } else {
            window.addEventListener('load', hideLoader, {once: true});
            window.setTimeout(hideLoader, 1800);
        }

        document.addEventListener('click', function(event) {
            var link = event.target.closest('a[href]');
            if (!link) return;
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            if (link.target && link.target !== '_self') return;
            if (link.hasAttribute('download') || link.dataset.noPageLoader === 'true') return;

            var href = link.getAttribute('href') || '';
            if (href === '' || href.charAt(0) === '#' || /^(mailto:|tel:|javascript:)/i.test(href)) return;

            showLoader();
        });

        document.addEventListener('submit', function(event) {
            var form = event.target;
            if (!form || form.dataset.noPageLoader === 'true') return;
            showLoader();
        });
    })();
</script>
