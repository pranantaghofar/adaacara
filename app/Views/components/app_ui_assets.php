<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
<?= view('components/google_ads_tag') ?>
<style>
    .aa-app-ui {
        background: linear-gradient(rgb(255 255 255 / 82%), rgb(255 255 255 / 81%)), url(https://adaacara.com/assets/editor/backgrounds/bg-dash-editor.png);
        background-size: 900px auto;
        background-repeat: repeat;
        background-position: center;
    }

    .aa-app-ui,
    .aa-app-ui button,
    .aa-app-ui input,
    .aa-app-ui select,
    .aa-app-ui textarea {
        font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .aa-app-ui .aa-ak-icon {
        width: 1.15em;
        height: 1.15em;
        flex: 0 0 auto;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .aa-app-ui input[type="file"] {
        width: 100%;
        min-height: 48px;
        cursor: pointer;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        color: #475569;
        padding: 7px 10px;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.25;
        outline: none;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .aa-app-ui input[type="file"]::file-selector-button {
        min-height: 34px;
        margin-right: 12px;
        border: 0;
        border-radius: 12px;
        background: #0f766e;
        color: #ffffff;
        padding: 0 14px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: background .16s ease, transform .16s ease;
    }

    .aa-app-ui input[type="file"]::-webkit-file-upload-button {
        min-height: 34px;
        margin-right: 12px;
        border: 0;
        border-radius: 12px;
        background: #0f766e;
        color: #ffffff;
        padding: 0 14px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: background .16s ease, transform .16s ease;
    }

    .aa-app-ui input[type="file"]:hover {
        border-color: #14b8a6;
        background: #ffffff;
        box-shadow: 0 12px 26px rgba(15, 118, 110, .1);
    }

    .aa-app-ui input[type="file"]:hover::file-selector-button,
    .aa-app-ui input[type="file"]:hover::-webkit-file-upload-button {
        background: #115e59;
        transform: translateY(-1px);
    }

    .aa-app-ui input[type="file"]:focus-visible {
        border-color: #0f766e;
        box-shadow: 0 0 0 4px rgba(20, 184, 166, .16);
    }

    .aa-img-wrap {
        position: relative;
        display: block;
        overflow: hidden;
        background: #f1f5f9;
    }

    .aa-img-wrap>img,
    .aa-lazy-img {
        display: block;
    }

    .aa-ratio-preview {
        aspect-ratio: 6 / 10;
    }

    .aa-img-loading::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background:
            linear-gradient(90deg, transparent, rgba(255, 255, 255, .72), transparent),
            linear-gradient(180deg, #f8fafc, #e2e8f0);
        background-size: 220% 100%, 100% 100%;
        animation: aa-img-shimmer 1.2s ease-in-out infinite;
        pointer-events: none;
    }

    .aa-img-loading>img {
        opacity: 0;
    }

    .aa-img-loaded::before {
        opacity: 0;
        visibility: hidden;
    }

    .aa-img-loaded>img {
        opacity: 1;
        transition: opacity .18s ease;
    }

    @keyframes aa-img-shimmer {
        0% {
            background-position: 180% 0, 0 0;
        }
        100% {
            background-position: -80% 0, 0 0;
        }
    }
</style>
<script>
    (function () {
        'use strict';

        if (!('serviceWorker' in navigator)) {
            return;
        }

        var cleanupKey = 'aa-sw-cleanup-v2';
        var runCleanup = function () {
            navigator.serviceWorker.getRegistrations()
                .then(function (registrations) {
                    return Promise.all(registrations.map(function (registration) {
                        return registration.unregister();
                    }));
                })
                .then(function () {
                    if (!window.caches || !caches.keys) {
                        return null;
                    }

                    return caches.keys().then(function (keys) {
                        return Promise.all(keys.map(function (key) {
                            return caches.delete(key);
                        }));
                    });
                })
                .then(function () {
                    try {
                        sessionStorage.setItem(cleanupKey, '1');
                    } catch (error) {}
                })
                .catch(function (error) {
                    console.warn('[AdaAcara] Service worker cleanup gagal:', error);
                });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runCleanup, { once: true });
        } else {
            runCleanup();
        }
    })();
</script>
<script>
    (function () {
        'use strict';

        var skipPrefixes = [
            '/editor',
            '/builder',
            '/u/',
            '/preview',
            '/publish-preview',
            '/templates/preview'
        ];

        function shouldSkipPath() {
            var path = window.location.pathname || '';
            return skipPrefixes.some(function (prefix) {
                return path === prefix || path.indexOf(prefix + '/') === 0;
            });
        }

        function markLoaded(wrapper) {
            if (!wrapper) return;
            wrapper.classList.remove('aa-img-loading');
            wrapper.classList.add('aa-img-loaded');
        }

        function isLoaderTarget(img) {
            return img.classList.contains('aa-lazy-img') ||
                img.hasAttribute('data-aa-loader') ||
                (img.parentElement && img.parentElement.classList.contains('aa-img-wrap'));
        }

        function ensureWrapper(img) {
            var parent = img.parentElement;

            if (parent && parent.classList.contains('aa-img-wrap')) {
                return parent;
            }

            if (!isLoaderTarget(img)) {
                return null;
            }

            var wrapper = document.createElement('span');
            wrapper.className = 'aa-img-wrap';

            if (img.classList.contains('aa-ratio-preview')) {
                wrapper.classList.add('aa-ratio-preview');
                img.classList.remove('aa-ratio-preview');
            }

            parent.insertBefore(wrapper, img);
            wrapper.appendChild(img);

            return wrapper;
        }

        function prepareImage(img) {
            if (!img || img.dataset.aaImageLoaderReady === '1' || img.hasAttribute('data-aa-no-loader')) {
                return;
            }

            img.dataset.aaImageLoaderReady = '1';

            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }

            if (!img.hasAttribute('decoding')) {
                img.setAttribute('decoding', 'async');
            }

            var wrapper = ensureWrapper(img);
            if (!wrapper) {
                return;
            }

            wrapper.classList.add('aa-img-wrap');

            if (img.complete) {
                markLoaded(wrapper);
                return;
            }

            wrapper.classList.add('aa-img-loading');
            img.addEventListener('load', function () {
                markLoaded(wrapper);
            }, {once: true});
            img.addEventListener('error', function () {
                markLoaded(wrapper);
            }, {once: true});
        }

        window.AAInitImageLoader = function (root) {
            if (shouldSkipPath()) {
                return;
            }

            var scope = root && root.querySelectorAll ? root : document;
            scope.querySelectorAll('img:not([data-aa-no-loader])').forEach(prepareImage);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                window.AAInitImageLoader(document);
            }, {once: true});
        } else {
            window.AAInitImageLoader(document);
        }
    })();
</script>
