<?php
    $googleAdsId = 'AW-18262459541';
?>
<script>
    (function () {
        'use strict';

        var googleAdsId = <?= json_encode($googleAdsId) ?>;
        if (!googleAdsId || window.__aaGoogleAdsTagLoaded) return;

        var path = window.location.pathname || '';
        var skipPrefixes = ['/admin', '/editor', '/preview', '/templates/preview', '/u/'];
        var shouldSkip = skipPrefixes.some(function (prefix) {
            return path === prefix || path.indexOf(prefix + '/') === 0;
        });
        if (shouldSkip) return;

        window.__aaGoogleAdsTagLoaded = true;
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () {
            window.dataLayer.push(arguments);
        };

        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(googleAdsId);
        document.head.appendChild(script);

        window.gtag('js', new Date());
        window.gtag('config', googleAdsId);
    })();
</script>
