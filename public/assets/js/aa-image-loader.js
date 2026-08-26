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
