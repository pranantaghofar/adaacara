<?php
    helper(['seo', 'aa_icon']);

    $templates = $templates ?? [];
    $plans = $plans ?? [];
    $isLoggedIn = $isLoggedIn ?? false;
    $homeAds = array_values(array_filter((array) ($homeAds ?? []), static fn ($ad): bool => is_array($ad) && ! empty($ad['image_path'])));
    $homeAdSessionKey = 'aa-home-ad-closed:' . md5(implode('|', array_map(static fn (array $ad): string => (string) ($ad['id'] ?? '') . ':' . (string) ($ad['updated_at'] ?? ''), $homeAds)));
    $homeFaqs = [
        ['Saya bukan desainer, masih bisa jualan?', 'Bisa. Remix template yang sudah ada - ganti warna, font, dan ilustrasi - lalu publish sebagai versi kamu sendiri.'],
        ['Berapa komisinya?', 'Kreator dapat 70% dari setiap template yang dipublish. 30% sisanya untuk biaya hosting, pembayaran, dan operasional platform.'],
        ['Bagaimana sistem pembayaran ke kreator?', 'Saldo otomatis masuk ke dompet AdaAcara. Bisa dicairkan kapan saja ke bank lokal atau e-wallet, minimum Rp 25.000.'],
        ['Darimana awal harga untuk patokan komisi 70%?', 'Kreator dapat 70% dari harga "Plan Buat Acara Sendiri".'],
        ['Apakah desain saya aman dari plagiat?', 'Setiap desain dilindungi hak cipta kreator. Pembeli cuma dapat hak pakai untuk 1 acara - tidak bisa menjual ulang.'],
        ['Cocok untuk acara apa saja?', 'Pernikahan, aqiqah, khitanan, ulang tahun, bukber, halal bihalal, seminar, sampai acara korporat.'],
        ['Apakah bisa membuat selain undangan wedding?', 'Bisa. AdaAcara mendukung wedding, seminar, bukber, halal bihalal, ulang tahun, khitan, aqiqah, dan corporate event.'],
        ['Apakah editor bisa drag dan resize seperti Canva?', 'Ya. Editor visual mendukung object bebas seperti teks, gambar, halaman, animasi, dan style desain.'],
        ['Apakah undangan bisa dipublish sebagai website?', 'Bisa. Setelah publish, undangan bisa dibuka melalui URL public /u/slug.'],
        ['Apakah bisa menerima ucapan tamu?', 'Bisa. Guestbook mendukung nama, kehadiran, ucapan, stiker, dan daftar komentar per undangan.'],
    ];
    $categoryLabels = [
        'wedding' => 'Wedding',
        'seminar' => 'Seminar',
        'bukber' => 'Bukber',
        'halal-bihalal' => 'Halal Bihalal',
        'lamaran' => 'Lamaran',
        'ulang-tahun' => 'Ulang Tahun',
        'khitan' => 'Khitan',
        'aqiqah' => 'Aqiqah',
        'syukuran' => 'Syukuran',
        'wisuda' => 'Wisuda',
        'corporate' => 'Corporate',
        'lainnya' => 'Lainnya',
    ];

    $normalizeHomeCategory = static function (array $template) use ($categoryLabels): array {
        $sourceSlug = strtolower(trim((string) ($template['category_slug'] ?? '')));
        $sourceName = strtolower(trim((string) (
            $template['category_name']
            ?? $template['category']
            ?? $template['jenis']
            ?? $template['type']
            ?? ''
        )));

        $source = trim($sourceSlug . ' ' . $sourceName);
        if ($source === '') {
            $source = strtolower(trim((string) (($template['name'] ?? '') . ' ' . ($template['description'] ?? ''))));
        }

        $map = [
            'wedding' => ['wedding', 'nikah', 'pernikahan', 'akad', 'resepsi'],
            'lamaran' => ['lamaran', 'engagement', 'tunangan'],
            'seminar' => ['seminar', 'webinar', 'workshop', 'talkshow'],
            'bukber' => ['bukber', 'buka bersama', 'ramadhan', 'ramadan', 'iftar'],
            'halal-bihalal' => ['halal bihalal', 'halalbihalal', 'halal-bihalal'],
            'ulang-tahun' => ['ulang tahun', 'ulang-tahun', 'ultah', 'birthday'],
            'khitan' => ['khitan', 'sunat', 'khitanan'],
            'aqiqah' => ['aqiqah', 'akikah'],
            'syukuran' => ['syukuran', 'tasyakuran'],
            'wisuda' => ['wisuda', 'graduation'],
            'corporate' => ['corporate', 'company', 'kantor', 'gathering', 'launching'],
        ];

        foreach ($map as $key => $keywords) {
            foreach ($keywords as $keyword) {
                if ($source !== '' && str_contains($source, $keyword)) {
                    return [$key, $categoryLabels[$key]];
                }
            }
        }

        return ['lainnya', $categoryLabels['lainnya']];
    };

    $formatHomePrice = static function (array $template, array $plans): string {
        $price = $template['price']
            ?? $template['harga']
            ?? $template['amount']
            ?? null;

        if ($price === null && ((int) ($template['is_premium'] ?? 0)) === 1 && $plans !== []) {
            $prices = array_values(array_filter(array_map(static fn (array $plan): int => (int) ($plan['price'] ?? 0), $plans)));
            $price = $prices === [] ? null : min($prices);
        }

        $price = (int) ($price ?? 0);

        return $price > 0 ? 'Rp' . number_format($price, 0, ',', '.') : 'Gratis';
    };

    $buildTemplatePreviewFontUrl = static function (array $template): string {
        $fontWeights = [
            'Aboreto' => '400',
            'Abril Fatface' => '400',
            'Adamina' => '400',
            'Alex Brush' => '400',
            'Allura' => '400',
            'Amarante' => '400',
            'Amiri' => '400;700',
            'Arizonia' => '400',
            'Bellefair' => '400',
            'Bodoni Moda' => '400;500;600;700',
            'Bonheur Royale' => '400',
            'Caudex' => '400;700',
            'Cinzel' => '400;500;600;700',
            'Cormorant Garamond' => '300;400;500;600;700',
            'Cormorant Infant' => '400;500;600;700',
            'Cormorant Upright' => '400;500;600;700',
            'DM Serif Display' => '400',
            'Dancing Script' => '400;500;600;700',
            'Elsie' => '400;900',
            'Ephesis' => '400',
            'Fleur De Leah' => '400',
            'Forum' => '400',
            'Fraunces' => '400;500;600;700',
            'Great Vibes' => '400',
            'Imperial Script' => '400',
            'Inter' => '400;500;600;700',
            'Italiana' => '400',
            'Italianno' => '400',
            'Lavishly Yours' => '400',
            'Libre Baskerville' => '400;700',
            'Lora' => '400;500;600;700',
            'Marcellus' => '400',
            'Mea Culpa' => '400',
            'Monsieur La Doulaise' => '400',
            'Montserrat' => '400;500;600;700',
            'Noto Naskh Arabic' => '400;500;600;700',
            'Parisienne' => '400',
            'Petit Formal Script' => '400',
            'Philosopher' => '400;700',
            'Playfair Display' => '400;500;600;700;800;900',
            'Poiret One' => '400',
            'Poppins' => '400;500;600;700',
            'Prata' => '400',
            'Questrial' => '400',
            'Quintessential' => '400',
            'Sorts Mill Goudy' => '400',
            'Tangerine' => '400;700',
            'The Nautigal' => '400;700',
            'Unna' => '400;700',
            'Viaoda Libre' => '400',
            'WindSong' => '400;500',
            'Yeseva One' => '400',
        ];
        $source = implode(' ', [
            (string) ($template['html'] ?? ''),
            (string) ($template['css'] ?? ''),
            (string) ($template['js'] ?? ''),
            (string) ($template['editor_json'] ?? ''),
            (string) ($template['grapesjs_json'] ?? ''),
        ]);
        $families = ['Inter'];

        foreach ($fontWeights as $family => $weights) {
            if ($family !== 'Inter' && stripos($source, $family) !== false) {
                $families[] = $family;
            }
        }

        $families = array_values(array_unique($families));
        $parts = array_map(static function (string $family) use ($fontWeights): string {
            $weights = $fontWeights[$family] ?? '400';
            return 'family=' . str_replace('%20', '+', rawurlencode($family)) . ($weights !== '400' ? ':wght@' . $weights : '');
        }, $families);

        return 'https://fonts.googleapis.com/css2?' . implode('&', $parts) . '&display=block';
    };

    $blankTemplateUrl = $isLoggedIn ? site_url('templates') : site_url('login');
    $templatePreviewUrl = static function (array $template): string {
        $fallback = site_url('templates/preview/' . (string) ($template['id'] ?? ''));
        $url = trim((string) ($template['preview_url'] ?? ''));
        if ($url === '') {
            return $fallback;
        }

        $path = $url;
        if (preg_match('#^https?://#i', $url)) {
            $siteHost = strtolower((string) parse_url(site_url('/'), PHP_URL_HOST));
            $urlHost = strtolower((string) parse_url($url, PHP_URL_HOST));
            if ($siteHost === '' || $urlHost === '' || $siteHost !== $urlHost) {
                return $fallback;
            }
            $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');
        }

        return preg_match('#^/?u/([a-z0-9]+(?:-[a-z0-9]+)*)/?$#i', $path, $matches)
            ? site_url('u/' . strtolower($matches[1]))
            : $fallback;
    };
    $creatorApplyUrl = $isLoggedIn ? site_url('creator/apply') : site_url('login');
    $loginDashboardUrl = $isLoggedIn ? site_url('dashboard') : site_url('login');
    $loginDashboardLabel = $isLoggedIn ? 'Dashboard' : 'Login';
    $premiumCrownState = ! empty($hasActiveMembership) ? 'is-unlocked' : 'is-locked';
    $premiumCrownSvg = '<span class="aa-template-premium-badge ' . $premiumCrownState . '" aria-label="Template premium"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.91 4.91a1.91 1.91 0 0 1-1.044 1.701c.942 2.366 1.928 3.53 2.795 3.622.982.104 1.88-.323 2.76-1.377a.977.977 0 0 1 .072-.078 1.91 1.91 0 1 1 1.468.873l-1.423 5.42c-.297 1.13-1.363 1.922-2.586 1.922H8.066c-1.223 0-2.29-.792-2.586-1.922L4.063 9.675a1.91 1.91 0 1 1 1.46-.898c.03.028.059.06.086.093.837 1.048 1.727 1.471 2.748 1.363.908-.096 1.888-1.253 2.793-3.614a1.91 1.91 0 1 1 2.76-1.71ZM6.561 19.008h10.875c.518 0 .938.448.938 1s-.42 1-.938 1H6.563c-.517 0-.937-.448-.937-1s.42-1 .937-1Z" fill="currentColor"></path></svg></span>';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->website()
        ->title('Home - adaAcara')
        ->description('adaAcara adalah editor visual untuk membuat undangan digital, landing page event, RSVP, ucapan tamu, wedding gift, dan halaman publik dengan editor desain seperti Canva.')
        ->canonical('https://adaacara.com/')
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800;900&display=block"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lenis@1.3.8/dist/lenis.min.js" defer></script>
    <script>
    (function() {
        try {
            var storedTheme = localStorage.getItem('aa-home-theme');
            var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = storedTheme === 'dark' || storedTheme === 'light' ? storedTheme : (systemDark ? 'dark' :
                'light');
            document.documentElement.dataset.aaHomeTheme = theme;
        } catch (error) {
            document.documentElement.dataset.aaHomeTheme = 'light';
        }
    })();
    </script>
    <style>
    html.lenis,
    html.lenis body {
        height: auto;
    }

    .lenis.lenis-smooth {
        scroll-behavior: auto !important;
    }

    .lenis.lenis-stopped {
        overflow: hidden;
    }

    .aa-home {
        --aa-ink: #0f172a;
        --aa-muted: #717b8a;
        --aa-line: #e2e8f0;
        --aa-soft: #f8fafc;
        --aa-teal: #8f65df;
        --aa-teal-dark: #7550c4;
        --aa-gold: #8f65df;
        --aa-rose: #be5b6b;
        background:
            radial-gradient(circle at 18% 0%, rgba(143, 101, 223, .14), transparent 26rem),
            radial-gradient(circle at 88% 9%, rgba(217, 204, 244, .34), transparent 30rem),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 46%, #ffffff 100%),
            #ffffff;
        color: var(--aa-ink);
        font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .aa-home * {
        box-sizing: border-box;
    }

    .aa-home a {
        text-decoration: none;
    }

    .aa-home-shell {
        width: min(1800px, calc(100% - 70px));
        margin: 0 auto;
    }

    .aa-home-nav {
        position: sticky;
        top: 0;
        z-index: 30;
        border-bottom: 1px solid rgba(226, 232, 240, .66);
        background: rgba(255, 255, 255, .72);
        backdrop-filter: blur(22px);
    }

    .aa-home-nav-inner {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        min-height: 72px;
        align-items: center;
        gap: 18px;
    }

    .aa-home-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: var(--aa-ink);
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .aa-home-brand-logo {
        display: block;
        width: 154px;
        height: auto;
        object-fit: contain;
    }

    .aa-home-nav-links {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 0;
    }

    .aa-home-nav-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .aa-home-nav-links a,
    .aa-home-nav-actions a,
    .aa-home-btn {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 999px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 800;
        transition: .18s ease;
    }

    .aa-home-nav-links a {
        color: #475569;
    }

    .aa-home-nav-actions a:not(.aa-home-btn-primary) {
        color: #475569;
    }

    .aa-home-nav-links a:hover,
    .aa-home-nav-actions a:not(.aa-home-btn-primary):hover {
        background: #f1f5f9;
        color: var(--aa-teal);
    }

    .aa-home-nav-actions svg {
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
    }

    .aa-home-premium-link {
        border: 1px solid #d9ccf4 !important;
        background: linear-gradient(135deg, #fbf8ff 0%, #f1e9ff 100%) !important;
        color: #7550c4 !important;
        box-shadow: 0 10px 24px rgba(91, 67, 118, .10);
    }

    .aa-home-premium-link:hover {
        background: linear-gradient(135deg, #8f65df 0%, #7550c4 100%) !important;
        color: #ffffff !important;
    }

    .aa-home-premium-link svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }

    .aa-home-theme-toggle {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 999px;
        background: rgba(255, 255, 255, .82);
        color: #475569;
        cursor: pointer;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
        transition: .18s ease;
    }

    .aa-home-theme-toggle:hover {
        border-color: rgba(143, 101, 223, .42);
        color: var(--aa-teal);
        transform: translateY(-1px);
    }

    .aa-home-theme-toggle svg {
        width: 18px;
        height: 18px;
        stroke-width: 2;
    }

    [data-home-theme-toggle] .aa-home-theme-sun {
        display: none;
    }

    html[data-aa-home-theme="dark"] [data-home-theme-toggle] .aa-home-theme-moon {
        display: none;
    }

    html[data-aa-home-theme="dark"] [data-home-theme-toggle] .aa-home-theme-sun {
        display: block;
    }

    .aa-home-mobile-nav {
        position: relative;
        z-index: 35;
        display: none;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .aa-home-mobile-toggle {
        display: inline-flex;
        height: 42px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 1px solid #d9ccf4;
        border-radius: 999px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 16px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 900;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .10);
        transition: .16s ease;
    }

    .aa-home-mobile-toggle:hover,
    .aa-home-mobile-nav.is-open .aa-home-mobile-toggle {
        border-color: #8f65df;
        color: #8f65df;
        transform: translateY(-1px);
    }

    .aa-home-mobile-toggle svg {
        width: 20px;
        height: 20px;
    }

    .aa-home-mobile-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: min(260px, calc(100vw - 28px));
        overflow: hidden;
        border: 1px solid rgba(217, 204, 244, .82);
        border-radius: 22px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-6px) scale(.98);
        transform-origin: top right;
        transition: .16s ease;
    }

    .aa-home-mobile-nav.is-open .aa-home-mobile-panel {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .aa-home-mobile-list {
        display: grid;
        gap: 6px;
        padding: 9px;
    }

    .aa-home-mobile-panel a,
    .aa-home-mobile-panel button {
        display: flex;
        width: 100%;
        min-height: 42px;
        align-items: center;
        gap: 10px;
        border: 0;
        border-radius: 15px;
        background: transparent;
        color: #334155;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 850;
        text-align: left;
        transition: .16s ease;
    }

    .aa-home-mobile-panel a svg,
    .aa-home-mobile-panel button svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    .aa-home-mobile-panel a:hover,
    .aa-home-mobile-panel button:hover {
        background: #fbf8ff;
        color: #7550c4;
    }

    .aa-home-mobile-panel .is-primary {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: #ffffff;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .18);
    }

    .aa-home-mobile-panel .is-primary:hover {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: #ffffff;
    }

    .aa-home-btn-primary {
        border: 1px solid #111827;
        background: linear-gradient(135deg, #4b5563, #030712);
        color: #ffffff;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .22);
    }

    .aa-home-btn-primary:hover {
        background: linear-gradient(135deg, #374151, #000000);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .aa-home-btn-secondary {
        border: 1px solid var(--aa-line);
        background: #ffffff;
        color: var(--aa-ink);
    }

    .aa-home-btn-secondary:hover {
        border-color: #d9ccf4;
        color: var(--aa-teal);
        transform: translateY(-1px);
    }

    .aa-home-hero {
        position: relative;
        overflow: hidden;
        padding: 86px 0 56px;
    }

    .aa-home-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image: radial-gradient(circle, rgba(148, 163, 184, .38) 1px, transparent 1px);
        background-size: 18px 18px;
        mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .34), transparent 58%);
        -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .34), transparent 58%);
    }

    .aa-home-hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(340px, .95fr);
        gap: 46px;
        align-items: center;
    }

    .aa-home-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(143, 101, 223, .18);
        border-radius: 14px;
        background: rgba(255, 255, 255, .76);
        padding: 9px 13px;
        color: var(--aa-teal);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
    }

    .aa-home h1,
    .aa-home h2,
    .aa-home h3 {
        margin: 0;
        letter-spacing: -.035em;
    }

    .aa-home h1,
    .aa-home h2 {
        color: #0f172a;
        text-wrap: balance;
        transition: color .28s ease, text-shadow .28s ease;
    }

    .aa-home h1:hover,
    .aa-home h2:hover {
        color: #7550c4;
    }

    .aa-home h1 {
        margin-top: 22px;
        max-width: 760px;
        font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(42px, 7vw, 68px);
        font-weight: 950;
        line-height: 1.02;
    }

    .aa-home-heading {
        --aa-heading-delay-step: 18ms;
    }

    .aa-home-heading[data-aa-heading-level="1"] {
        font-size: clamp(42px, 7vw, 68px);
        line-height: 1.02;
        letter-spacing: -.045em;
    }

    .aa-home-heading[data-aa-heading-level="2"] {
        font-size: clamp(30px, 4vw, 48px);
        line-height: 1.05;
        letter-spacing: -.04em;
    }

    .aa-letter-word {
        display: inline-block;
        white-space: nowrap;
    }

    .aa-letter-char {
        display: inline-block;
        will-change: transform, opacity, filter;
    }

    .aa-letter-animated .aa-letter-char {
        opacity: 0;
        filter: blur(10px);
        transform: translate3d(0, 22px, 0);
    }

    .aa-letter-animated.is-visible .aa-letter-char {
        animation: aa-home-letter-in .78s cubic-bezier(.16, 1, .3, 1) both;
        animation-delay: calc(var(--aa-letter-index, 0) * var(--aa-heading-delay-step));
    }

    @keyframes aa-home-letter-in {
        0% {
            opacity: 0;
            filter: blur(10px);
            transform: translate3d(0, 22px, 0);
        }

        58% {
            opacity: 1;
            filter: blur(1.8px);
        }

        100% {
            opacity: 1;
            filter: blur(0);
            transform: translate3d(0, 0, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .aa-letter-animated .aa-letter-char,
        .aa-letter-animated.is-visible .aa-letter-char {
            opacity: 1;
            filter: none;
            transform: none;
            animation: none;
        }
    }

    .aa-viewport-animate {
        opacity: 0;
        transform: translate3d(0, 22px, 0) scale(.985);
        transition:
            opacity .68s cubic-bezier(.16, 1, .3, 1),
            transform .68s cubic-bezier(.16, 1, .3, 1);
        transition-delay: var(--aa-viewport-delay, 0ms);
        will-change: opacity, transform;
    }

    .aa-viewport-animate.is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
        will-change: auto;
    }

    @media (prefers-reduced-motion: reduce) {

        .aa-viewport-animate,
        .aa-viewport-animate.is-visible {
            opacity: 1;
            transform: none;
            transition: none;
            will-change: auto;
        }
    }

    .aa-home-lead {
        margin-top: 22px;
        max-width: 640px;
        color: var(--aa-muted);
        font-size: clamp(16px, 2vw, 19px);
        line-height: 1.8;
    }

    .aa-home-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 30px;
    }

    .aa-home-proof {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 34px;
        max-width: 620px;
    }

    .aa-home-proof-item {
        border: 1px solid rgba(226, 232, 240, .82);
        border-radius: 16px;
        background: rgba(255, 255, 255, .78);
        padding: 16px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, .06);
        backdrop-filter: blur(16px);
    }

    .aa-home-proof-item strong {
        display: block;
        font-size: 22px;
        font-weight: 950;
    }

    .aa-home-proof-item span {
        color: var(--aa-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .aa-home-preview {
        position: relative;
        min-height: 620px;
        isolation: isolate;
    }

    .aa-home-radiant-card {
        position: relative;
        width: min(480px, 92vw);
        min-height: 560px;
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .86);
        border-radius: 34px;
        background:
            radial-gradient(circle at 50% 0%, rgba(45, 212, 191, .38), transparent 32%),
            linear-gradient(180deg, rgba(255, 255, 255, .94), rgba(248, 250, 252, .92));
        box-shadow: 0 34px 100px rgba(15, 23, 42, .16);
    }

    .aa-home-ribbon-showcase {
        position: absolute;
        inset: 0;
        z-index: 0;
        opacity: .72;
        pointer-events: auto;
        overflow: hidden;
    }

    .aa-home-ribbon-showcase canvas {
        display: block;
        width: 100% !important;
        height: 100% !important;
    }

    .aa-home-hero-mascot {
        position: absolute;
        left: 50%;
        top: 51%;
        z-index: 2;
        width: min(360px, 50%);
        height: auto;
        transform: translate(-50%, -50%);
        object-fit: contain;
        filter: drop-shadow(0 28px 38px rgba(15, 23, 42, .18));
        animation: aa-home-float-centered 7s ease-in-out infinite;
    }

    .aa-home-radiant-card::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        background-image: radial-gradient(circle, rgba(148, 163, 184, .42) 1px, transparent 1px);
        background-size: 14px 14px;
        mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .3), transparent 62%);
        -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .3), transparent 62%);
    }

    .aa-home-radiant-orb {
        position: absolute;
        z-index: 1;
        border-radius: 999px;
        filter: blur(1px);
        opacity: .78;
        animation: aa-home-float 7s ease-in-out infinite;
    }

    .aa-home-radiant-orb.one {
        width: 150px;
        height: 150px;
        right: -38px;
        top: 56px;
        background: rgba(217, 204, 244, .62);
    }

    .aa-home-radiant-orb.two {
        width: 110px;
        height: 110px;
        left: -28px;
        bottom: 74px;
        background: rgba(230, 215, 255, .72);
        animation-delay: -2s;
    }

    .aa-home-animation-frame {
        position: absolute;
        left: 50%;
        top: 48%;
        z-index: 2;
        width: min(260px, 62%);
        height: min(260px, 62%);
        transform: translate(-50%, -50%);
        overflow: hidden;
        border: 10px solid #ffffff;
        border-radius: 32px;
        background: #eef6ff;
        box-shadow: 0 28px 70px rgba(15, 23, 42, .18);
    }

    .aa-home-animation-frame video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .aa-home-canvas-chip {
        position: absolute;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(226, 232, 240, .86);
        border-radius: 999px;
        background: rgba(255, 255, 255, .88);
        padding: 9px 12px;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        box-shadow: 0 14px 38px rgba(15, 23, 42, .1);
        backdrop-filter: blur(14px);
        animation: aa-home-float 6s ease-in-out infinite;
    }

    .aa-home-canvas-chip::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: var(--aa-teal);
        box-shadow: 0 0 0 5px rgba(143, 101, 223, .16);
    }

    .aa-home-canvas-chip.top {
        top: 38px;
        left: 28px;
    }

    .aa-home-canvas-chip.bottom {
        right: 24px;
        bottom: 38px;
        animation-delay: -1.8s;
    }

    .aa-home-feature-chips {
        position: absolute;
        left: 28px;
        bottom: 126px;
        z-index: 4;
        display: grid;
        gap: 9px;
        justify-items: start;
    }

    .aa-home-feature-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(20, 108, 184, .14);
        border-radius: 999px;
        background: rgba(255, 255, 255, .9);
        padding: 8px 11px;
        color: #0f172a;
        font-size: 11px;
        font-weight: 900;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .1);
        backdrop-filter: blur(14px);
        animation: aa-home-float 6.8s ease-in-out infinite;
    }

    .aa-home-feature-chip:nth-child(2) {
        animation-delay: -1.4s;
    }

    .aa-home-feature-chip:nth-child(3) {
        animation-delay: -2.6s;
    }

    .aa-home-feature-chip i {
        display: inline-block;
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: linear-gradient(135deg, #0f766e, #146cb8);
        box-shadow: 0 0 0 5px rgba(20, 108, 184, .12);
    }

    .aa-home-rsvp-feature-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .aa-home-rsvp-feature-list span {
        display: inline-flex;
        min-height: 50px;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(245, 243, 255, .92));
        box-shadow: 0 14px 32px rgba(143, 101, 223, .10);
        padding: 12px 17px;
        color: #334155;
        font-size: 14px;
        font-weight: 950;
        line-height: 1;
    }

    .aa-home-rsvp-feature-icon {
        display: inline-grid;
        flex: 0 0 28px;
        width: 28px;
        height: 28px;
        place-items: center;
        border-radius: 999px;
        background: linear-gradient(135deg, #efe7ff, #d9ccf4);
        color: #7f43de;
        line-height: 1;
    }

    .aa-home-rsvp-feature-icon svg {
        width: 16px;
        height: 16px;
        stroke-width: 2.15;
    }

    .aa-home-floating-card {
        position: absolute;
        right: -8px;
        top: 92px;
        z-index: 4;
        width: 190px;
        border: 1px solid rgba(226, 232, 240, .86);
        border-radius: 18px;
        background: rgba(255, 255, 255, .9);
        padding: 16px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .12);
        backdrop-filter: blur(14px);
        animation: aa-home-float 7.5s ease-in-out infinite;
    }

    .aa-home-floating-card.bottom {
        right: auto;
        left: 0;
        top: auto;
        bottom: 80px;
        animation-delay: -2.4s;
    }

    .aa-home-floating-card strong {
        display: block;
        font-size: 14px;
        font-weight: 950;
    }

    .aa-home-floating-card span {
        display: block;
        margin-top: 6px;
        color: var(--aa-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    @keyframes aa-home-float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-12px);
        }
    }

    @keyframes aa-home-float-centered {

        0%,
        100% {
            transform: translate(-50%, -50%);
        }

        50% {
            transform: translate(-50%, calc(-50% - 12px));
        }
    }

    .aa-home-hero.is-maker {
        min-height: calc(100vh - 72px);
        padding: clamp(48px, 5vw, 82px) 0 44px;
    }

    .aa-home-hero.is-maker::before {
        opacity: .9;
        background-size: auto;
        mask-image: none;
        -webkit-mask-image: none;
    }

    .aa-home-maker-top {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, .92fr) minmax(440px, 1.08fr);
        gap: clamp(28px, 5vw, 78px);
        align-items: center;
        min-height: 430px;
    }

    .aa-home-maker-copy {
        padding-top: 12px;
    }

    .aa-home-maker-title {
        max-width: 850px;
        margin: 0;
        color: #10031d;
        font-family: "Manrope", "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(52px, 5.45vw, 82px);
        font-weight: 950;
        line-height: 1.02;
        letter-spacing: -.055em;
    }

    .aa-home-maker-title:hover {
        color: #10031d;
    }

    .aa-home-maker-line {
        display: block;
    }

    .aa-home-maker-title .is-purple {
        color: #7c3aed;
        text-shadow: 0 14px 28px rgba(124, 58, 237, .16);
    }

    .aa-home-maker-title .is-gold {
        color: #d2a208;
        text-shadow: 0 14px 28px rgba(210, 162, 8, .16);
    }

    .aa-home-maker-lead {
        max-width: 660px;
        margin: 24px 0 0;
        color: #5d5877;
        font-size: clamp(18px, 1.55vw, 24px);
        font-weight: 50;
        line-height: 1.55;
    }

    .aa-home-maker-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 36px;
    }

    .aa-home-maker-primary {
        min-height: 66px;
        min-width: 300px;
        justify-content: center;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 58%, #e6ad21 100%);
        color: #ffffff;
        font-size: 23px;
        font-weight: 900;
        box-shadow: 0 22px 45px rgba(124, 58, 237, .24), 0 12px 22px rgba(210, 162, 8, .18);
    }

    .aa-home-maker-primary:hover {
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 26px 52px rgba(124, 58, 237, .3), 0 13px 26px rgba(210, 162, 8, .2);
    }

    .aa-home-maker-primary i {
        margin-left: 14px;
        font-size: .98em;
    }

    .aa-home-maker-visual {
        position: relative;
        min-height: 468px;
        display: grid;
        place-items: center;
        overflow: visible;
    }

    .aa-home-maker-glow {
        position: absolute;
        inset: 4% -2% 0 6%;
        border-radius: 46% 54% 42% 58%;
        background:
            radial-gradient(circle at 58% 42%, rgba(255, 255, 255, .92), transparent 34%),
            radial-gradient(circle at 68% 52%, rgba(217, 204, 244, .9), transparent 47%),
            linear-gradient(135deg, rgba(143, 101, 223, .2), rgba(250, 204, 21, .15));
    }

    .aa-home-maker-wave {
        position: absolute;
        right: -2%;
        bottom: 14%;
        width: 44%;
        height: 148px;
        border: 3px solid rgba(250, 204, 21, .3);
        border-left: 0;
        border-bottom: 0;
        border-radius: 0 90% 0 0;
        transform: rotate(-9deg);
    }

    .aa-home-maker-mascot {
        position: relative;
        z-index: 2;
        width: min(458px, 76%);
        height: auto;
        object-fit: contain;
        filter: drop-shadow(0 30px 36px rgba(77, 37, 119, .18));
        animation: aa-home-maker-float 6.5s ease-in-out infinite;
    }

    .aa-home-maker-laptop-heart {
        position: absolute;
        left: 43.5%;
        top: 55%;
        z-index: 3;
        color: #7c3aed;
        font-size: 48px;
        line-height: 1;
        transform: rotate(-5deg);
        filter: drop-shadow(0 9px 12px rgba(124, 58, 237, .25));
        pointer-events: none;
    }

    .aa-home-maker-note {
        position: absolute;
        left: 0;
        bottom: 60%;
        z-index: 3;
        color: #6d28d9;
        font-family: "Comic Sans MS", "Segoe Print", cursive;
        font-size: clamp(22px, 2vw, 34px);
        font-weight: 850;
        line-height: 1.14;
        transform: rotate(-7deg);
        text-shadow: 0 10px 22px rgba(124, 58, 237, .14);
    }

    .aa-home-maker-note span {
        color: #d19700;
    }

    .aa-home-maker-note::after {
        content: "";
        display: block;
        width: 170px;
        height: 18px;
        margin-top: 6px;
        border-bottom: 4px solid rgba(209, 151, 0, .76);
        border-radius: 50%;
        transform: rotate(-7deg);
    }

    .aa-home-maker-heart {
        position: absolute;
        right: 10%;
        top: 34%;
        z-index: 1;
        color: #8b5cf6;
        font-size: 58px;
        font-weight: 900;
        transform: rotate(-13deg);
        opacity: .74;
    }

    .aa-home-maker-sparkle {
        position: absolute;
        z-index: 3;
        width: 24px;
        height: 24px;
        color: #f4bf2a;
        pointer-events: none;
    }

    .aa-home-maker-sparkle::before,
    .aa-home-maker-sparkle::after {
        content: "";
        position: absolute;
        inset: 0;
        margin: auto;
        border-radius: 999px;
        background: currentColor;
    }

    .aa-home-maker-sparkle::before {
        width: 4px;
        height: 100%;
    }

    .aa-home-maker-sparkle::after {
        width: 100%;
        height: 4px;
    }

    .aa-home-maker-sparkle.one {
        left: 34%;
        top: 12%;
    }

    .aa-home-maker-sparkle.two {
        right: 19%;
        top: 14%;
        width: 18px;
        height: 18px;
        opacity: .72;
    }

    .aa-home-maker-sparkle.three {
        right: 8%;
        top: 26%;
        width: 32px;
        height: 32px;
        opacity: .72;
    }

    .aa-home-maker-tools {
        position: relative;
        z-index: 2;
        margin-top: 14px;
    }

    .aa-home-maker-tools-title {
        margin: 0 0 22px;
        color: #5127a8;
        text-align: center;
        font-family: "Manrope", "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(28px, 2.8vw, 42px);
        font-weight: 950;
        letter-spacing: -.035em;
    }

    .aa-home-maker-tools-title:hover {
        color: #5127a8;
    }

    .aa-home-maker-tools-title span {
        color: #d19a00;
    }

    .aa-home-maker-carousel {
        position: relative;
    }

    .aa-home-maker-track {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(146px, calc((100% - 108px) / 10));
        gap: 12px;
        overflow-x: auto;
        padding: 0 36px 20px;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }

    .aa-home-maker-track::-webkit-scrollbar {
        display: none;
    }

    .aa-home-maker-tool-card {
        padding: 0;
        overflow: hidden;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 12px 35px rgba(43, 34, 96, .08);
    }

    .aa-home-maker-tool-placeholder {
        aspect-ratio: 3/4;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: #8b5cf6;
        background: #faf8ff;
    }

    .aa-home-maker-tool-image {
        width: 100%;
        display: block;
        aspect-ratio: 3/4;
        object-fit: cover;
    }

    .aa-home-maker-tool-visual {
        position: relative;
        display: grid;
        place-items: center;
        width: 112px;
        height: 112px;
        margin: 0 auto 22px;
        border-radius: 26px;
        overflow: hidden;
        background: linear-gradient(145deg, rgba(143, 101, 223, .16), rgba(250, 204, 21, .12));
    }

    .aa-home-maker-tool-visual img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .aa-home-maker-tool-fallback {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        color: #6d28d9;
        font-size: 56px;
    }

    .aa-home-maker-tool-visual.has-image .aa-home-maker-tool-fallback {
        display: none;
    }

    .aa-home-maker-tool-visual.is-wheel {
        border-radius: 999px;
        background: conic-gradient(#ef4444, #f97316, #facc15, #22c55e, #14b8a6, #3b82f6, #8b5cf6, #ec4899, #ef4444);
    }

    .aa-home-maker-tool-visual.is-wheel .aa-home-maker-tool-fallback {
        position: relative;
    }

    .aa-home-maker-tool-visual.is-wheel .aa-home-maker-tool-fallback::before {
        content: "";
        width: 38px;
        height: 38px;
        border-radius: 999px;
        background: #ffffff;
        box-shadow: inset 0 0 0 7px rgba(143, 101, 223, .18);
    }

    .aa-home-maker-tool-visual.is-gradient {
        background: linear-gradient(135deg, #f9a8d4, #c084fc 48%, #facc15);
    }

    .aa-home-maker-tool-card h3 {
        margin: 0;
        color: #3c168a;
        font-size: 16px;
        font-weight: 950;
        line-height: 1.34;
        letter-spacing: -.02em;
        text-align: left;
    }

    .aa-home-maker-tool-card p {
        margin: 14px 0 0;
        color: #4f4b6f;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.78;
    }

    .aa-home-maker-arrow {
        position: absolute;
        top: 41%;
        z-index: 3;
        display: grid;
        place-items: center;
        width: 52px;
        height: 52px;
        border: 1px solid rgba(226, 232, 240, .8);
        border-radius: 999px;
        background: rgba(255, 255, 255, .94);
        color: #6d28d9;
        box-shadow: 0 18px 40px rgba(91, 68, 120, .14);
    }

    .aa-home-maker-arrow.is-left {
        left: -2px;
    }

    .aa-home-maker-arrow.is-right {
        right: -2px;
    }

    @keyframes aa-home-maker-float {

        0%,
        100% {
            transform: translateY(0) rotate(0deg);
        }

        50% {
            transform: translateY(-12px) rotate(1deg);
        }
    }

    html[data-aa-home-theme="dark"] .aa-home-maker-title,
    html[data-aa-home-theme="dark"] .aa-home-maker-title:hover {
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-home-maker-lead,
    html[data-aa-home-theme="dark"] .aa-home-maker-tool-card p {
        color: #c7d2e0;
    }

    html[data-aa-home-theme="dark"] .aa-home-maker-tool-card {
        border-color: rgba(148, 163, 184, .2);
        background: rgb(254 253 253);
        box-shadow: 0 24px 58px rgba(0, 0, 0, .26);
    }

    html[data-aa-home-theme="dark"] .aa-home-maker-tool-card h3,
    html[data-aa-home-theme="dark"] .aa-home-maker-tools-title {
        color: #e9ddff;
    }

    html[data-aa-home-theme="dark"] .aa-home-maker-arrow {
        border-color: rgba(148, 163, 184, .22);
        background: rgba(15, 23, 42, .86);
        color: #d9ccf4;
    }

    .aa-home-section {
        padding: 72px 0;
    }

    .aa-home-section.alt {
        background: #f8fafc;
    }

    .aa-home-section-head {
        position: relative;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 26px;
        margin-bottom: 28px;
    }

    .aa-home-section-head.has-mascot {
        padding-right: clamp(105px, 13vw, 155px);
    }

    .aa-home-section-mascot {
        position: absolute;
        right: 4px;
        bottom: -14px;
        width: clamp(86px, 10vw, 122px);
        height: auto;
        object-fit: contain;
        pointer-events: none;
        filter: drop-shadow(0 16px 24px rgba(15, 23, 42, .14));
        animation: aa-home-float 7s ease-in-out infinite;
    }

    .aa-home-section-head h2 {
        max-width: 720px;
        font-size: clamp(30px, 4vw, 48px);
        font-weight: 950;
        line-height: 1.05;
    }

    .aa-home-section-head p {
        max-width: 420px;
        margin: 0;
        color: var(--aa-muted);
        line-height: 1.7;
    }

    .aa-home-grid {
        display: grid;
        gap: 16px;
    }

    .aa-home-grid.cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .aa-home-grid.cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .aa-home-grid.cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .aa-home-card {
        border: 1px solid var(--aa-line);
        border-radius: 24px;
        background: #ffffff;
        padding: 22px;
        box-shadow: 0 14px 40px rgba(15, 23, 42, .05);
    }

    .aa-home-mascot-card {
        position: relative;
        min-height: 154px;
        overflow: hidden;
        padding-right: 112px !important;
    }

    .aa-home-card-mascot {
        position: absolute;
        right: -10px;
        bottom: -18px;
        width: 112px;
        height: auto;
        object-fit: contain;
        pointer-events: none;
        filter: drop-shadow(0 16px 24px rgba(15, 23, 42, .12));
        animation: aa-home-float 7s ease-in-out infinite;
    }

    .aa-home-card.dark {
        border-color: rgba(255, 255, 255, .12);
        background: #0f172a;
        color: #ffffff;
    }

    .aa-home-icon {
        display: grid;
        width: 46px;
        height: 46px;
        place-items: center;
        border-radius: 16px;
        background: #fbf8ff;
        color: var(--aa-teal);
        font-size: 22px;
        font-weight: 950;
    }

    .aa-home-icon svg {
        width: 22px;
        height: 22px;
        stroke-width: 2.05;
    }

    .aa-home-card h3 {
        margin-top: 16px;
        font-size: 18px;
        font-weight: 950;
    }

    .aa-home-card p {
        margin: 10px 0 0;
        color: var(--aa-muted);
        font-size: 11px;
        line-height: 1.7;
    }

    .aa-home-card.dark p {
        color: #cbd5e1;
    }

    .aa-home-card.dark h3,
    .aa-home-ai-panel h2,
    .aa-home-ai-panel h3 {
        color: inherit;
    }

    .aa-home-photobooth {
        position: relative;
        overflow: hidden;
        border-top: 1px solid rgba(217, 204, 244, .38);
        border-bottom: 1px solid rgba(217, 204, 244, .38);
        background:
            radial-gradient(circle at 12% 18%, rgba(217, 204, 244, .46), transparent 30%),
            radial-gradient(circle at 88% 10%, rgba(45, 212, 191, .16), transparent 26%),
            linear-gradient(180deg, #ffffff, #f8fafc);
    }

    .aa-home-photobooth-grid {
        display: grid;
        grid-template-columns: minmax(0, .92fr) minmax(360px, 1.08fr);
        gap: clamp(22px, 5vw, 58px);
        align-items: center;
    }

    .aa-home-photobooth-copy {
        max-width: 620px;
    }

    .aa-home-photobooth-copy h2 {
        margin-top: 14px;
        font-size: clamp(34px, 5vw, 58px);
        font-weight: 950;
        line-height: 1.02;
    }

    .aa-home-photobooth-copy p {
        margin: 18px 0 0;
        color: var(--aa-muted);
        font-size: clamp(15px, 1.8vw, 18px);
        font-weight: 700;
        line-height: 1.75;
    }

    .aa-home-photobooth-steps {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 24px;
    }

    .aa-home-photobooth-step {
        display: grid;
        align-content: start;
        gap: 10px;
        border: 1px solid rgba(217, 204, 244, .72);
        border-radius: 18px;
        background: rgba(255, 255, 255, .76);
        padding: 10px;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .06);
        backdrop-filter: blur(16px);
    }

    .aa-home-photobooth-step-media {
        position: relative;
        display: block;
        overflow: hidden;
        aspect-ratio: 4 / 5;
        border: 1px solid rgba(226, 232, 240, .84);
        border-radius: 14px;
        background:
            radial-gradient(circle at 30% 18%, rgba(217, 204, 244, .35), transparent 34%),
            linear-gradient(145deg, #f8fafc, #ffffff);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .85);
    }

    .aa-home-photobooth-step-media img,
    .aa-home-photobooth-step-media video {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .aa-home-photobooth-step-media::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(180deg, transparent 50%, rgba(15, 23, 42, .12));
    }

    .aa-home-photobooth-step-head {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .aa-home-photobooth-step-number {
        display: grid;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        place-items: center;
        border-radius: 12px;
        background: #e6fffb;
        color: #0f766e;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-home-photobooth-step strong {
        display: block;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
        line-height: 1.35;
    }

    .aa-home-photobooth-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
    }

    .aa-home-photobooth-note {
        display: inline-flex;
        max-width: 520px;
        margin-top: 18px;
        border: 1px solid #d9ccf4;
        border-radius: 18px;
        background: rgba(255, 255, 255, .72);
        padding: 12px 14px;
        color: #7550c4;
        font-size: 12px;
        font-weight: 850;
        line-height: 1.6;
    }

    .aa-home-gallery-showcase {
        display: grid;
        gap: 16px;
        height: 100%;
    }

    .aa-home-gallery-preview {
        position: relative;
        overflow: hidden;
        margin: 24px 0 0;
        border: 1px solid rgba(196, 181, 253, .24);
        border-radius: 24px;
        background: linear-gradient(135deg, rgba(143, 101, 223, .20), rgba(20, 184, 166, .10));
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12), 0 24px 52px rgba(15, 23, 42, .18);
    }

    .aa-home-gallery-preview img {
        display: block;
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
    }

    .aa-home-gallery-preview::after {
        content: "";
        position: absolute;
        inset: auto 0 0;
        height: 42%;
        background: linear-gradient(180deg, transparent, rgba(15, 23, 42, .34));
        pointer-events: none;
    }

    .aa-home-gallery-mini-grid {
        margin-top: 20px;
    }

    .aa-home-gallery-mini-card {
        display: grid;
        gap: 12px;
        min-height: 0;
        align-content: start;
        padding: 16px;
    }

    .aa-home-gallery-mini-card img {
        display: block;
        width: 100%;
        aspect-ratio: 1.68 / 1;
        border-radius: 18px;
        object-fit: cover;
        background:
            radial-gradient(circle at 20% 14%, rgba(255, 228, 236, .65), transparent 30%),
            linear-gradient(135deg, #fff7fb, #f5f3ff);
        box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
    }

    .aa-home-gallery-mini-card strong,
    .aa-home-gallery-mini-card span,
    .aa-home-gallery-mini-copy {
        max-width: none;
    }

    .aa-home-gallery-mini-copy {
        min-width: 0;
    }

    .aa-home-gallery-mini-copy strong {
        font-size: 20px;
        line-height: 1.1;
    }

    .aa-home-gallery-mini-copy>span {
        margin-top: 8px;
        font-size: 13px;
        font-weight: 850;
    }

    .aa-home-photobooth-visual {
        position: relative;
        min-height: 560px;
        border: 1px solid rgba(217, 204, 244, .58);
        border-radius: 34px;
        background:
            radial-gradient(circle at 72% 18%, rgba(143, 101, 223, .20), transparent 32%),
            radial-gradient(circle at 38% 76%, rgba(45, 212, 191, .13), transparent 30%),
            linear-gradient(145deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .88));
        box-shadow: 0 30px 82px rgba(15, 23, 42, .11), inset 0 1px 0 rgba(255, 255, 255, .85);
        overflow: hidden;
    }

    .aa-home-photobooth-visual::before {
        content: "";
        position: absolute;
        inset: 20px;
        border: 1px solid rgba(255, 255, 255, .72);
        border-radius: 28px;
        pointer-events: none;
        z-index: 0;
    }

    .aa-home-photobooth-phone {
        position: absolute;
        z-index: 1;
        left: 50%;
        top: 50%;
        width: min(286px, 63%);
        min-height: 486px;
        transform: translate(-50%, -50%) rotate(-1.8deg);
        border: 9px solid #101827;
        border-radius: 42px;
        background: #101827;
        color: #f8fafc;
        box-shadow: 0 38px 82px rgba(15, 23, 42, .30), 0 0 0 1px rgba(255, 255, 255, .08);
        overflow: hidden;
    }

    .aa-home-photobooth-phone::before {
        content: "";
        position: absolute;
        left: 50%;
        top: 8px;
        width: 76px;
        height: 18px;
        border-radius: 999px;
        background: #030712;
        transform: translateX(-50%);
        opacity: .9;
    }

    .aa-home-photobooth-screen {
        display: flex;
        min-height: 486px;
        flex-direction: column;
        align-items: center;
        padding: 52px 18px 24px;
        border-radius: 32px;
        background: #2f3d21;
        text-align: center;
    }

    .aa-home-photobooth-screen small {
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .24em;
        text-transform: uppercase;
        opacity: .78;
    }

    .aa-home-photobooth-screen h3 {
        margin-top: 10px;
        font-family: "Georgia", serif;
        font-size: 28px;
        font-weight: 500;
        letter-spacing: -.03em;
        color: #f8fafc;
    }

    .aa-home-photobooth-frame {
        display: grid;
        width: 174px;
        gap: 7px;
        margin-top: 36px;
        border: 8px solid #fffaf0;
        background: #fffaf0;
        box-shadow: 0 18px 38px rgba(0, 0, 0, .16);
    }

    .aa-home-photobooth-frame span {
        min-height: 84px;
        background:
            linear-gradient(135deg, rgba(15, 23, 42, .18), rgba(15, 23, 42, .04)),
            #dbeafe;
        border-radius: 3px;
    }

    .aa-home-photobooth-frame span:nth-child(2) {
        background:
            linear-gradient(135deg, rgba(45, 212, 191, .18), rgba(143, 101, 223, .08)),
            #f8fafc;
    }

    .aa-home-photobooth-screen b {
        margin-top: 26px;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .2em;
        text-transform: uppercase;
    }

    .aa-home-photobooth-screen video {
        display: block;
        width: calc(100% + 36px);
        min-height: 486px;
        margin: -52px -18px -24px;
        object-fit: cover;
        filter: saturate(1.04) brightness(1.16) contrast(.96);
    }

    .aa-home-photobooth-chip {
        position: absolute;
        z-index: 2;
        display: grid;
        gap: 10px;
        border: 1px solid rgba(196, 181, 253, .36);
        border-radius: 26px;
        background:
            radial-gradient(circle at 16% 12%, rgba(255, 228, 236, .56), transparent 26%),
            linear-gradient(145deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .88));
        padding: 17px;
        color: #0f172a;
        box-shadow: 0 24px 54px rgba(79, 70, 229, .13);
        backdrop-filter: blur(20px);
    }

    .aa-home-photobooth-chip strong {
        font-size: 14px;
        font-weight: 950;
        line-height: 1.15;
    }

    .aa-home-photobooth-chip span {
        color: var(--aa-muted);
        font-size: 11px;
        font-weight: 800;
    }

    .aa-home-photobooth-chip.is-qr {
        left: 30px;
        top: 36px;
        width: 188px;
    }

    .aa-home-photobooth-qr {
        display: grid;
        width: 112px;
        height: 112px;
        place-items: center;
        border: 10px solid #ffffff;
        border-radius: 24px;
        background:
            linear-gradient(135deg, rgba(143, 101, 223, .10), rgba(20, 184, 166, .08)),
            #ffffff;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .24), 0 16px 32px rgba(15, 23, 42, .10);
    }

    .aa-home-photobooth-qr img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .aa-home-photobooth-qr-meta {
        display: inline-flex;
        width: fit-content;
        align-items: center;
        gap: 7px;
        border-radius: 999px;
        background: #f5f3ff;
        padding: 7px 10px;
        color: #7f43de;
        font-size: 10px;
        font-weight: 950;
    }

    .aa-home-photobooth-chip.is-gallery {
        right: 28px;
        bottom: 32px;
        width: 230px;
    }

    .aa-home-photobooth-gallery {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 9px;
    }

    .aa-home-photobooth-gallery i {
        position: relative;
        display: block;
        aspect-ratio: 1 / 1.12;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, .88);
        border-radius: 16px;
        background:
            radial-gradient(circle at 50% 28%, rgba(255, 255, 255, .84), transparent 17%),
            linear-gradient(160deg, #d9ccf4, #e6fffb);
        box-shadow: 0 12px 24px rgba(15, 23, 42, .10);
    }

    .aa-home-photobooth-gallery i::after {
        content: "";
        position: absolute;
        right: 8px;
        bottom: 8px;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .72);
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .16);
    }

    .aa-home-photobooth-gallery i:nth-child(2) {
        background:
            radial-gradient(circle at 48% 26%, rgba(255, 255, 255, .82), transparent 18%),
            linear-gradient(160deg, #fde2f3, #e9d5ff);
    }

    .aa-home-photobooth-gallery i:nth-child(3) {
        background:
            radial-gradient(circle at 48% 24%, rgba(255, 255, 255, .82), transparent 18%),
            linear-gradient(160deg, #ccfbf1, #dbeafe);
    }

    .aa-home-photobooth-gallery-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-radius: 16px;
        background: rgba(245, 243, 255, .74);
        padding: 9px 10px;
        color: #7550c4;
        font-size: 10px;
        font-weight: 950;
    }

    .aa-home-photobooth-chip.is-link {
        right: 58px;
        top: 58px;
        width: 184px;
    }

    .aa-home-photobooth-link {
        display: inline-flex;
        width: fit-content;
        border-radius: 999px;
        background: #e6fffb;
        padding: 7px 9px;
        color: #0f766e;
        font-size: 10px;
        font-weight: 950;
    }

    .aa-home-feature-deep {
        display: grid;
        grid-template-columns: minmax(0, 1.04fr) minmax(320px, .96fr);
        gap: 18px;
        align-items: stretch;
    }

    .aa-home-ai-panel {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(143, 101, 223, .28);
        border-radius: 30px;
        background: linear-gradient(145deg, #1f2937, #0f172a 52%, #422006);
        color: #ffffff;
        padding: clamp(24px, 4vw, 34px);
        box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
    }

    .aa-home-ai-panel::before {
        content: "";
        position: absolute;
        inset: -34% -10% auto auto;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(250, 204, 21, .18);
        filter: blur(10px);
        pointer-events: none;
    }

    .aa-home-ai-panel>* {
        position: relative;
        z-index: 1;
    }

    .aa-home-ai-badge {
        display: inline-flex;
        width: fit-content;
        min-height: 34px;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .11);
        padding: 0 13px;
        color: #d9ccf4;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .aa-home-ai-panel h2 {
        max-width: 620px;
        margin-top: 18px;
        font-size: clamp(30px, 4vw, 48px);
        font-weight: 950;
        line-height: 1.05;
    }

    .aa-home-ai-panel p {
        max-width: 620px;
        margin: 16px 0 0;
        color: #e2e8f0;
        line-height: 1.75;
    }

    .aa-home-ai-steps {
        display: grid;
        gap: 10px;
        margin-top: 24px;
    }

    .aa-home-ai-step {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 18px;
        background: rgba(255, 255, 255, .07);
        padding: 13px;
    }

    .aa-home-ai-step span {
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 12px;
        background: #d9ccf4;
        color: #422006;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-home-ai-step strong {
        display: block;
        font-size: 14px;
        font-weight: 950;
    }

    .aa-home-ai-step small {
        display: block;
        margin-top: 4px;
        color: #cbd5e1;
        font-size: 11px;
        line-height: 1.6;
    }

    .aa-home-feature-list {
        display: grid;
        gap: 14px;
    }

    .aa-home-feature-detail {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr);
        gap: 14px;
        align-items: start;
        border: 1px solid var(--aa-line);
        border-radius: 24px;
        background: #ffffff;
        padding: 18px;
        box-shadow: 0 14px 40px rgba(15, 23, 42, .05);
    }

    .aa-home-feature-detail i {
        display: grid;
        width: 48px;
        height: 48px;
        place-items: center;
        border-radius: 17px;
        background: #fbf8ff;
        color: var(--aa-teal);
        font-style: normal;
        font-size: 20px;
        font-weight: 950;
    }

    .aa-home-feature-detail h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 950;
    }

    .aa-home-feature-detail p {
        margin: 7px 0 0;
        color: var(--aa-muted);
        font-size: 12px;
        line-height: 1.65;
    }

    .aa-home-cuan {
        padding: 0;
    }

    .aa-home-cuan>div {
        display: grid;
        max-width: 80rem;
        margin: 0 auto;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 24px;
        padding: 40px 24px;
    }

    .aa-home-cuan>div>div {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 12px;
    }

    .aa-home-cuan .bg-primary-soft {
        display: flex;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: #f1e9ff;
        color: var(--aa-teal);
    }

    .aa-home-cuan svg.size-5 {
        width: 20px;
        height: 20px;
    }

    .aa-home-cuan .font-display,
    .aa-home-flow .font-display {
        font-family: inherit;
    }

    .aa-home-cuan .text-xl {
        font-size: 20px;
    }

    .aa-home-cuan .font-bold {
        font-weight: 800;
    }

    .aa-home-cuan .leading-none {
        line-height: 1;
    }

    .aa-home-cuan .mt-1 {
        margin-top: 4px;
    }

    .aa-home-cuan .text-xs,
    .aa-home-flow .text-sm {
        font-size: 12px;
    }

    .aa-home-cuan .text-muted-foreground,
    .aa-home-flow .text-muted-foreground {
        color: var(--aa-muted);
    }

    .aa-home-path-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .aa-home-path-card {
        position: relative;
        display: grid;
        min-height: 260px;
        align-content: space-between;
        overflow: hidden;
        border: 1px solid var(--aa-line);
        border-radius: 28px;
        background: #ffffff;
        padding: 24px;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .06);
    }

    .aa-home-path-card.is-primary {
        border-color: rgba(143, 101, 223, .28);
        background: linear-gradient(145deg, #ffffff, #fbf8ff);
    }

    .aa-home-path-card.has-mascot {
        padding-right: clamp(24px, 12vw, 150px);
    }

    .aa-home-path-mascot {
        position: absolute;
        right: -24px;
        bottom: -28px;
        width: min(145px, 38%);
        height: auto;
        pointer-events: none;
        object-fit: contain;
        opacity: .94;
        filter: drop-shadow(0 18px 26px rgba(15, 23, 42, .14));
    }

    .aa-home-path-kicker {
        display: inline-flex;
        width: fit-content;
        min-height: 30px;
        align-items: center;
        border-radius: 999px;
        background: #f8fafc;
        padding: 0 12px;
        color: #8f65df;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .aa-home-path-card h3 {
        margin: 18px 0 0;
        color: var(--aa-ink);
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -.02em;
        line-height: 1.08;
    }

    .aa-home-path-card p {
        margin: 12px 0 0;
        color: var(--aa-muted);
        font-size: 14px;
        font-weight: 650;
        line-height: 1.7;
    }

    .aa-home-path-list {
        display: grid;
        gap: 8px;
        margin: 18px 0 0;
        padding: 0;
        list-style: none;
    }

    .aa-home-path-list li {
        display: flex;
        gap: 8px;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.45;
    }

    .aa-home-path-list li::before {
        content: "";
        width: 7px;
        height: 7px;
        flex: 0 0 7px;
        margin-top: 6px;
        border-radius: 999px;
        background: #d4a245;
    }

    .aa-home-path-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
    }

    .aa-home-studio-board {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .86);
        border-radius: 32px;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .92)),
            #ffffff;
        padding: clamp(18px, 3vw, 28px);
        box-shadow: 0 26px 80px rgba(15, 23, 42, .08);
    }

    .aa-home-studio-board::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background-image:
            linear-gradient(rgba(15, 118, 110, .08) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 118, 110, .08) 1px, transparent 1px);
        background-size: 34px 34px;
        mask-image: linear-gradient(120deg, rgba(0, 0, 0, .38), transparent 62%);
        -webkit-mask-image: linear-gradient(120deg, rgba(0, 0, 0, .38), transparent 62%);
    }

    .aa-home-studio-layout {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, .86fr) minmax(360px, 1.14fr);
        gap: clamp(18px, 3vw, 30px);
        align-items: stretch;
    }

    .aa-home-brief-panel {
        display: grid;
        align-content: space-between;
        gap: 22px;
        border: 1px solid rgba(226, 232, 240, .92);
        border-radius: 24px;
        background: rgba(255, 255, 255, .86);
        padding: clamp(18px, 3vw, 26px);
        box-shadow: 0 18px 48px rgba(15, 23, 42, .06);
        backdrop-filter: blur(16px);
    }

    .aa-home-brief-panel h2 {
        max-width: 620px;
        font-size: clamp(30px, 4vw, 48px);
        font-weight: 950;
        line-height: 1.05;
    }

    .aa-home-brief-panel p {
        max-width: 540px;
        margin: 14px 0 0;
        color: var(--aa-muted);
        line-height: 1.75;
    }

    .aa-home-brief-stack {
        display: grid;
        gap: 10px;
    }

    .aa-home-brief-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(226, 232, 240, .86);
        border-radius: 18px;
        background: #ffffff;
        padding: 12px;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .04);
    }

    .aa-home-brief-chip i {
        display: grid;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        place-items: center;
        border-radius: 12px;
        background: #f1e9ff;
        color: #7550c4;
        font-style: normal;
        font-weight: 950;
    }

    .aa-home-showcase-stack {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, .86fr);
        gap: 14px;
        align-items: stretch;
    }

    .aa-home-prompt-card {
        position: relative;
        display: grid;
        min-height: 190px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 24px;
        background: #ffffff;
        padding: 16px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, .07);
    }

    .aa-home-prompt-card.is-featured {
        min-height: 394px;
        background:
            linear-gradient(180deg, rgba(15, 23, 42, .08), rgba(15, 23, 42, .42)),
            #f8fafc;
        color: #ffffff;
    }

    .aa-home-prompt-cover {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .aa-home-prompt-card::after {
        content: "";
        position: absolute;
        inset: auto 0 0;
        height: 54%;
        background: linear-gradient(180deg, transparent, rgba(15, 23, 42, .66));
        pointer-events: none;
    }

    .aa-home-prompt-content {
        position: relative;
        z-index: 2;
        display: grid;
        align-content: end;
        gap: 10px;
        min-height: 100%;
    }

    .aa-home-prompt-kicker {
        display: inline-flex;
        width: fit-content;
        min-height: 28px;
        align-items: center;
        border-radius: 999px;
        background: rgba(255, 255, 255, .86);
        padding: 0 10px;
        color: #0f766e;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .aa-home-prompt-card h3 {
        margin: 0;
        color: inherit;
        font-size: clamp(20px, 3vw, 34px);
        font-weight: 950;
        line-height: 1.04;
        letter-spacing: -.03em;
    }

    .aa-home-prompt-card p {
        max-width: 440px;
        margin: 0;
        color: rgba(255, 255, 255, .86);
        font-size: 12px;
        font-weight: 750;
        line-height: 1.6;
    }

    .aa-home-mini-stack {
        display: grid;
        gap: 14px;
    }

    .aa-home-mini-card {
        position: relative;
        min-height: 190px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 24px;
        background:
            linear-gradient(135deg, rgba(255, 251, 235, .92), rgba(240, 253, 250, .88)),
            #ffffff;
        padding: 16px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, .06);
    }

    .aa-home-mini-card strong {
        display: block;
        max-width: 220px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
        line-height: 1.1;
    }

    .aa-home-mini-card span {
        display: block;
        max-width: 230px;
        margin-top: 8px;
        color: var(--aa-muted);
        font-size: 12px;
        font-weight: 750;
        line-height: 1.55;
    }

    .aa-home-mini-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 16px;
    }

    .aa-home-mini-badges em {
        border-radius: 999px;
        background: #ffffff;
        padding: 6px 9px;
        color: #7550c4;
        font-size: 10px;
        font-style: normal;
        font-weight: 950;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
    }

    .aa-home-lab-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .aa-home-lab-strip span {
        border: 1px solid rgba(226, 232, 240, .86);
        border-radius: 16px;
        background: rgba(255, 255, 255, .76);
        padding: 13px;
        color: #334155;
        font-size: 11px;
        font-weight: 900;
        line-height: 1.35;
    }

    .aa-home-flow {
        padding: 52px 0;
    }

    .aa-home-flow>h2 {
        max-width: 42rem;
        margin: 12px 0 0;
        font-size: clamp(30px, 4vw, 42px);
        line-height: 1.08;
        letter-spacing: 0;
        font-weight: 900;
    }

    .aa-home-flow>div:last-child {
        position: relative;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-top: 40px;
    }

    .aa-home-flow>div:last-child>div {
        position: relative;
        border: 1px solid var(--aa-line);
        border-radius: 16px;
        background: #ffffff;
        padding: 24px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .06);
    }

    .aa-home-flow .flex {
        display: flex;
    }

    .aa-home-flow .items-center {
        align-items: center;
    }

    .aa-home-flow .justify-between {
        justify-content: space-between;
    }

    .aa-home-flow .size-9 {
        width: 36px;
        height: 36px;
    }

    .aa-home-flow .size-5 {
        width: 20px;
        height: 20px;
    }

    .aa-home-flow .bg-primary {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #0f172a;
        color: #ffffff;
    }

    .aa-home-flow svg.lucide {
        color: rgba(143, 101, 223, .78);
    }

    .aa-home-flow .mt-4 {
        margin-top: 16px;
    }

    .aa-home-flow .mt-1 {
        margin-top: 4px;
    }

    .aa-home-flow .text-base {
        font-size: 16px;
    }

    .aa-home-flow .font-bold {
        font-weight: 800;
    }

    .aa-home-category {
        min-height: 150px;
        background:
            linear-gradient(145deg, rgba(255, 255, 255, .92), rgba(248, 250, 252, .82)),
            var(--aa-soft);
    }

    .aa-home-category span {
        display: inline-flex;
        border-radius: 999px;
        background: #f1f5f9;
        padding: 5px 9px;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
    }

    .aa-home-editor {
        --aa-home-editor-fade: #e8eef6;
        --aa-home-editor-fade-clear: rgba(232, 238, 246, 0);
        position: relative;
        display: block;
        overflow: hidden;
        border: 1px solid var(--aa-line);
        border-radius: 28px;
        background:
            radial-gradient(circle at 18% 12%, rgba(217, 204, 244, .42), transparent 34%),
            linear-gradient(135deg, #f8fbff, var(--aa-home-editor-fade));
        padding: 16px;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .22);
    }

    .aa-home-editor::before,
    .aa-home-editor::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        z-index: 3;
        width: min(128px, 18%);
        pointer-events: none;
    }

    .aa-home-editor::before {
        left: 0;
        background: linear-gradient(90deg, var(--aa-home-editor-fade) 0%, var(--aa-home-editor-fade-clear) 100%);
    }

    .aa-home-editor::after {
        right: 0;
        background: linear-gradient(270deg, var(--aa-home-editor-fade) 0%, var(--aa-home-editor-fade-clear) 100%);
    }

    .aa-home-editor-track {
        display: flex;
        width: max-content;
        max-height: min(58vw, 520px);
        gap: 0;
        animation: aa-home-editor-marquee 38s linear infinite;
        will-change: transform;
    }

    .aa-home-editor:hover .aa-home-editor-track {
        animation-play-state: paused;
    }

    .aa-home-editor-card {
        flex: 0 0 clamp(420px, 58vw, 760px);
        max-height: 435px;
        margin: 0 16px 0 0;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .72);
        border-radius: 22px;
        background: rgba(255, 255, 255, .82);
        box-shadow: 0 18px 42px rgba(15, 23, 42, .14);
    }

    .aa-home-editor-image {
        display: block;
        width: 100%;
        height: auto;
        object-fit: contain;
        object-position: center top;
    }

    @keyframes aa-home-editor-marquee {
        from {
            transform: translate3d(-50%, 0, 0);
        }

        to {
            transform: translate3d(0, 0, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .aa-home-editor-track {
            animation: none;
            transform: translate3d(0, 0, 0);
        }
    }

    .aa-home-editor-mascot {
        position: fixed;
        right: clamp(14px, 3vw, 34px);
        bottom: clamp(14px, 3vw, 34px);
        z-index: 2;
        display: block;
        width: min(180px, 19vw);
        height: auto;
        cursor: pointer;
        text-decoration: none;
    }

    .aa-home-editor-mascot img {
        display: block;
        width: 100%;
        height: auto;
        object-fit: contain;
        filter: drop-shadow(0 18px 28px rgba(15, 23, 42, .22));
    }

    .aa-home-editor-mascot-tooltip {
        position: absolute;
        right: 50%;
        bottom: calc(100% + 12px);
        width: max-content;
        max-width: min(260px, 76vw);
        transform: translateX(50%);
        pointer-events: none;
    }

    .aa-home-editor-mascot-tooltip span {
        position: absolute;
        right: 50%;
        bottom: 0;
        display: block;
        width: max-content;
        max-width: min(260px, 76vw);
        transform: translateX(50%) translateY(8px);
        border: 1px solid rgba(217, 204, 244, .55);
        border-radius: 16px;
        background: rgba(255, 255, 255, .96);
        padding: 10px 13px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
        line-height: 1.35;
        text-align: center;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .13);
        opacity: 0;
        animation: aa-home-mascot-tooltip 9s ease-in-out infinite;
    }

    .aa-home-editor-mascot-tooltip span:nth-child(2) {
        animation-delay: 3s;
    }

    .aa-home-editor-mascot-tooltip span:nth-child(3) {
        animation-delay: 6s;
    }

    @keyframes aa-home-mascot-tooltip {

        0%,
        5% {
            opacity: 0;
            transform: translateX(50%) translateY(8px);
        }

        8%,
        28% {
            opacity: 1;
            transform: translateX(50%) translateY(0);
        }

        33%,
        100% {
            opacity: 0;
            transform: translateX(50%) translateY(-4px);
        }
    }

    .aa-home-editor-panel,
    .aa-home-artboard {
        border: 1px solid rgba(255, 255, 255, .10);
        border-radius: 20px;
        background: rgba(255, 255, 255, .06);
        padding: 14px;
        color: #ffffff;
    }

    .aa-home-editor-panel small {
        display: block;
        margin-bottom: 12px;
        color: #94a3b8;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .aa-home-tool {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .08);
        padding: 10px 12px;
        color: #e2e8f0;
        font-size: 13px;
        font-weight: 800;
    }

    .aa-home-tool+.aa-home-tool {
        margin-top: 8px;
    }

    .aa-home-artboard {
        min-height: 460px;
        background: #e5e7eb;
        padding: 24px;
    }

    .aa-home-artboard-page {
        position: relative;
        width: min(260px, 100%);
        height: 410px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 20px;
        background:
            linear-gradient(rgba(15, 23, 42, .08), rgba(15, 23, 42, .42)),
            url("https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=700&q=80") center / cover;
        box-shadow: 0 22px 60px rgba(15, 23, 42, .22);
    }

    .aa-home-artboard-text {
        position: absolute;
        left: 22px;
        right: 22px;
        bottom: 26px;
        color: #ffffff;
        text-align: center;
    }

    .aa-home-artboard-text strong {
        display: block;
        font-size: 30px;
        line-height: 1;
    }

    .aa-home-template {
        overflow: hidden;
        padding: 0;
    }

    .aa-home-template-filter {
        position: relative;
        width: min(100%, 260px);
    }

    .aa-home-template-filter::after {
        content: "⌄";
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-56%);
        color: #64748b;
        font-size: 16px;
        font-weight: 900;
        pointer-events: none;
    }

    .aa-home-template-filter-select {
        width: 100%;
        min-height: 44px;
        border: 1px solid var(--aa-line);
        border-radius: 999px;
        background: #ffffff;
        padding: 0 42px 0 16px;
        color: #475569;
        cursor: pointer;
        font: inherit;
        font-size: 13px;
        font-weight: 900;
        outline: none;
        appearance: none;
        transition: .18s ease;
    }

    .aa-home-template-filter-select:hover,
    .aa-home-template-filter-select:focus {
        border-color: var(--aa-teal);
        background: #fbf8ff;
        color: var(--aa-teal);
        box-shadow: 0 0 0 4px rgba(15, 118, 110, .10);
    }

    .aa-home-template-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 22px;
    }

    .aa-home-template-toolbar .aa-home-btn {
        min-height: 44px;
        white-space: nowrap;
    }

    @media (max-width: 640px) {
        .aa-home-template-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .aa-home-template-filter {
            width: 100%;
        }

        .aa-home-template-toolbar .aa-home-btn {
            width: 100%;
            justify-content: center;
        }
    }

    .aa-home-template-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 10px;
    }

    .aa-home-template.is-entering {
        animation: aaHomeTemplateEnter .24s ease both;
    }

    @keyframes aaHomeTemplateEnter {
        from {
            opacity: 0;
            filter: blur(4px);
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            filter: blur(0);
            transform: translateY(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .aa-home-template.is-entering {
            animation: none;
        }
    }

    .aa-home-template-preview {
        position: relative;
        display: block;
        aspect-ratio: 6 / 10;
        overflow: hidden;
        border-radius: 16px;
        background: #f1f5f9;
        background-size: cover;
        background-position: center;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .aa-home-template-preview:hover {
        border-color: rgba(143, 101, 223, .34);
        box-shadow: 0 18px 40px rgba(15, 23, 42, .12);
        transform: translateY(-2px);
    }

    .aa-home-template-preview img,
    .aa-home-template-preview iframe {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
    }

    .aa-home-template-preview img {
        object-fit: cover;
    }

    .aa-home-template-preview iframe {
        background: #ffffff;
        pointer-events: none;
    }

    .aa-home-template-preview[data-home-preview-url]::after {
        content: "Preview";
        position: absolute;
        left: 50%;
        bottom: 12px;
        z-index: 3;
        transform: translateX(-50%) translateY(8px);
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(15, 23, 42, .82);
        color: #ffffff;
        padding: 0 13px;
        font-size: 11px;
        font-weight: 950;
        opacity: 0;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .22);
        transition: .18s ease;
        backdrop-filter: blur(10px);
    }

    .aa-home-template-preview[data-home-preview-url]:hover::after {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .aa-template-premium-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 4;
        display: grid;
        width: 28px;
        height: 28px;
        place-items: center;
        color: #FFC107;
        filter: drop-shadow(0 1px 0 rgba(20, 108, 184, .75)) drop-shadow(0 0 7px rgba(20, 108, 184, .32));
    }

    .aa-template-premium-badge.is-unlocked {
        color: rgba(100, 116, 139, .68);
        filter: drop-shadow(0 1px 0 rgba(255, 255, 255, .58));
    }

    .aa-template-premium-badge svg {
        width: 25px;
        height: 25px;
        color: inherit;
    }

    .aa-home-template-blank-preview {
        display: grid;
        height: 100%;
        min-height: 100%;
        place-items: center;
        background:
            linear-gradient(135deg, rgba(143, 101, 223, .14), transparent 34%),
            linear-gradient(315deg, rgba(143, 101, 223, .12), transparent 42%),
            #ffffff;
    }

    .aa-home-template-blank-inner {
        display: grid;
        gap: 10px;
        place-items: center;
        padding: 14px;
        text-align: center;
    }

    .aa-home-template-blank-plus {
        display: grid;
        width: 46px;
        height: 46px;
        place-items: center;
        border-radius: 16px;
        background: var(--aa-teal);
        color: #ffffff;
        font-size: 26px;
        font-weight: 950;
        box-shadow: 0 16px 36px rgba(143, 101, 223, .22);
    }

    .aa-home-template-blank-inner strong {
        display: block;
        font-size: 16px;
        font-weight: 950;
    }

    .aa-home-template-blank-inner span {
        color: var(--aa-muted);
        font-size: 11px;
        font-weight: 800;
        line-height: 1.45;
    }

    .aa-home-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 24px;
        background: #ffffff;
        padding: 32px;
        color: var(--aa-muted);
        text-align: center;
    }

    .aa-home-modal {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    .aa-home-modal.is-open {
        display: flex;
    }

    .aa-home-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .54);
        backdrop-filter: blur(10px);
    }

    .aa-home-modal-card {
        position: relative;
        z-index: 1;
        width: min(100%, 520px);
        max-height: min(88vh, 760px);
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .88);
        border-radius: 26px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 30px 90px rgba(15, 23, 42, .28);
    }

    .aa-home-modal-card.preview {
        display: flex;
        flex-direction: column;
        width: min(100%, 980px);
    }

    .aa-home-modal-card.project-choice {
        width: min(100%, 1040px);
        max-height: min(88vh, 720px);
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        border-color: rgba(168, 85, 247, .28);
        border-radius: 24px;
        background:
            radial-gradient(circle at 14% 12%, rgba(250, 204, 21, .14), transparent 26%),
            radial-gradient(circle at 82% 18%, rgba(168, 85, 247, .12), transparent 30%),
            rgba(255, 255, 255, .97);
    }

    .aa-home-project-choice {
        padding: clamp(24px, 3vw, 36px);
    }

    .aa-home-project-choice-head {
        position: relative;
        margin: 0 auto 22px;
        max-width: 640px;
        padding: 0 52px;
        text-align: center;
    }

    .aa-home-project-choice-spark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-bottom: 12px;
        color: #d99a0a;
        font-size: 30px;
        line-height: 1;
    }

    .aa-home-project-choice-spark i {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #a855f7;
        box-shadow: 15px -5px 0 rgba(216, 180, 254, .9), -12px 8px 0 rgba(245, 158, 11, .7);
    }

    .aa-home-project-choice-head h3 {
        margin: 0;
        color: #24143f;
        font-size: clamp(28px, 3vw, 38px);
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.08;
    }

    .aa-home-project-choice-head p {
        margin: 14px 0 0;
        color: #475569;
        font-size: clamp(14px, 1.35vw, 17px);
        line-height: 1.5;
    }

    .aa-home-project-choice-close {
        position: absolute;
        top: -10px;
        right: 0;
        display: grid;
        width: 50px;
        height: 50px;
        place-items: center;
        border: 1px solid rgba(168, 85, 247, .28);
        border-radius: 999px;
        background: rgba(255, 255, 255, .78);
        color: #24143f;
        cursor: pointer;
        transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease;
    }

    .aa-home-project-choice-close:hover {
        border-color: rgba(168, 85, 247, .52);
        box-shadow: 0 12px 26px rgba(88, 28, 135, .12);
        transform: translateY(-1px);
    }

    .aa-home-project-choice-close svg {
        width: 24px;
        height: 24px;
    }

    .aa-home-project-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 16px;
    }

    .aa-home-project-card {
        position: relative;
        display: grid;
        grid-column: span 2;
        grid-template-columns: 70px minmax(0, 1fr) 42px;
        min-height: 148px;
        align-items: center;
        gap: 14px;
        overflow: hidden;
        border: 1px solid rgba(168, 85, 247, .32);
        border-radius: 13px;
        background:
            radial-gradient(circle at 20% 32%, rgba(168, 85, 247, .12), transparent 34%),
            rgba(255, 255, 255, .82);
        padding: 18px 16px;
        color: #1f1637;
        text-decoration: none;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .aa-home-project-card:hover {
        border-color: rgba(126, 34, 206, .48);
        box-shadow: 0 22px 45px rgba(88, 28, 135, .12);
        color: #1f1637;
        transform: translateY(-3px);
    }

    .aa-home-project-card.is-disabled {
        cursor: not-allowed;
        opacity: .72;
        pointer-events: none;
    }

    .aa-home-project-card.is-disabled:hover {
        border-color: rgba(168, 85, 247, .32);
        box-shadow: none;
        transform: none;
    }

    .aa-home-project-card.is-gold {
        border-color: rgba(217, 153, 10, .34);
        background:
            radial-gradient(circle at 20% 32%, rgba(245, 158, 11, .16), transparent 34%),
            rgba(255, 253, 247, .9);
    }

    .aa-home-project-card.is-soft {
        border-color: rgba(236, 72, 153, .22);
        background:
            radial-gradient(circle at 20% 32%, rgba(244, 114, 182, .12), transparent 34%),
            rgba(255, 250, 252, .9);
    }

    .aa-home-project-card.is-wide {
        grid-column: span 2;
    }

    .aa-home-project-card.is-lower-start {
        grid-column: 2 / span 2;
    }

    .aa-home-project-icon {
        display: grid;
        width: 64px;
        height: 64px;
        place-items: center;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(168, 85, 247, .12), rgba(126, 34, 206, .08));
        color: #6d28d9;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .9), 0 16px 35px rgba(88, 28, 135, .1);
    }

    .aa-home-project-card.is-gold .aa-home-project-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, .18), rgba(251, 191, 36, .08));
        color: #d97706;
    }

    .aa-home-project-card.is-soft .aa-home-project-icon {
        background: linear-gradient(135deg, rgba(244, 114, 182, .14), rgba(168, 85, 247, .08));
        color: #7e22ce;
    }

    .aa-home-project-icon svg {
        width: 34px;
        height: 34px;
    }

    .aa-home-project-copy {
        min-width: 0;
    }

    .aa-home-project-copy h4 {
        margin: 0 0 8px;
        color: #4c1d95;
        font-size: clamp(18px, 1.55vw, 23px);
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.18;
    }

    .aa-home-project-copy p {
        margin: 0;
        color: #334155;
        font-size: 14px;
        line-height: 1.45;
    }

    .aa-home-project-arrow {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 999px;
        background: rgba(168, 85, 247, .12);
        color: #7e22ce;
    }

    .aa-home-project-card.is-gold .aa-home-project-arrow {
        background: rgba(245, 158, 11, .12);
        color: #d97706;
    }

    .aa-home-project-card.is-soft .aa-home-project-arrow {
        background: rgba(244, 114, 182, .12);
    }

    .aa-home-project-arrow svg {
        width: 24px;
        height: 24px;
    }

    .aa-home-project-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        border-radius: 999px;
        background: linear-gradient(135deg, #7c3aed, #5b21b6);
        padding: 5px 13px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .aa-home-project-foot {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 22px;
        color: #475569;
        font-size: 14px;
        line-height: 1.5;
        text-align: center;
    }

    .aa-home-project-foot svg {
        width: 28px;
        height: 28px;
        color: #7c3aed;
        flex: 0 0 auto;
    }

    @media (max-width: 1080px) {
        .aa-home-project-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-home-project-card,
        .aa-home-project-card.is-wide,
        .aa-home-project-card.is-lower-start {
            grid-column: auto;
        }
    }

    @media (max-width: 720px) {
        .aa-home-modal {
            padding: 12px;
        }

        .aa-home-modal-card.project-choice {
            max-height: calc(100vh - 24px);
            border-radius: 22px;
        }

        .aa-home-project-choice {
            padding: 28px 16px 24px;
        }

        .aa-home-project-choice-head {
            margin-bottom: 22px;
            padding: 0 42px;
        }

        .aa-home-project-choice-head h3 {
            font-size: 30px;
        }

        .aa-home-project-choice-head p {
            font-size: 15px;
        }

        .aa-home-project-choice-close {
            top: -6px;
            width: 42px;
            height: 42px;
        }

        .aa-home-project-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .aa-home-project-card {
            grid-template-columns: 72px minmax(0, 1fr) 42px;
            min-height: 142px;
            gap: 14px;
            border-radius: 16px;
            padding: 18px 14px;
        }

        .aa-home-project-icon {
            width: 66px;
            height: 66px;
        }

        .aa-home-project-icon svg {
            width: 34px;
            height: 34px;
        }

        .aa-home-project-copy h4 {
            margin-bottom: 7px;
            font-size: 20px;
        }

        .aa-home-project-copy p {
            font-size: 14px;
            line-height: 1.45;
        }

        .aa-home-project-arrow {
            width: 40px;
            height: 40px;
        }

        .aa-home-project-arrow svg {
            width: 24px;
            height: 24px;
        }

        .aa-home-project-foot {
            margin-top: 22px;
            font-size: 14px;
        }
    }

    @media (max-width: 430px) {
        .aa-home-project-choice-head {
            padding: 0 34px;
        }

        .aa-home-project-card {
            grid-template-columns: 58px minmax(0, 1fr) 36px;
            gap: 10px;
            padding: 16px 12px;
        }

        .aa-home-project-icon {
            width: 54px;
            height: 54px;
        }

        .aa-home-project-icon svg {
            width: 28px;
            height: 28px;
        }

        .aa-home-project-copy h4 {
            font-size: 18px;
        }

        .aa-home-project-copy p {
            font-size: 13px;
        }
    }

    .aa-home-modal-head {
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 18px;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 22px;
    }

    .aa-home-modal-head h3 {
        margin: 0;
        font-size: 21px;
        font-weight: 950;
        letter-spacing: -.03em;
    }

    .aa-home-modal-head p {
        margin: 5px 0 0;
        color: var(--aa-muted);
        font-size: 13px;
        line-height: 1.55;
    }

    .aa-home-modal-close {
        display: grid;
        width: 40px;
        height: 40px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        color: #334155;
        cursor: pointer;
        font-size: 22px;
        font-weight: 900;
        line-height: 1;
    }

    .aa-home-modal-body {
        padding: 22px;
    }

    .aa-home-ad-card {
        width: auto;
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .aa-home-ad-body {
        padding: 0;
    }

    .aa-home-ad-card .aa-home-modal-close {
        position: absolute;
        top: -14px;
        right: -14px;
        z-index: 4;
        width: 42px;
        height: 42px;
        border-color: rgb(255 255 255 / 0%);
        background: rgb(254 254 255 / 0%);
        color: #ffffff;
        box-shadow: 0 16px 42px rgba(15, 23, 42, .26);
        backdrop-filter: blur(12px);
        border-radius: 50px;
        -webkit-backdrop-filter: blur(12px);
    }

    .aa-home-ad-slider {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        background: transparent;
        box-shadow: 0 30px 90px rgba(15, 23, 42, .32);
    }

    .aa-home-ad-slide {
        display: none;
    }

    .aa-home-ad-slide.is-active {
        display: block;
    }

    .aa-home-ad-slide a,
    .aa-home-ad-slide img {
        display: block;
        width: 100%;
    }

    .aa-home-ad-slide img {
        max-height: min(68vh, 640px);
        object-fit: contain;
        background: transparent;
    }

    .aa-home-ad-dots {
        position: absolute;
        right: 14px;
        bottom: 14px;
        display: inline-flex;
        gap: 6px;
        border: 1px solid rgba(226, 232, 240, .8);
        border-radius: 999px;
        background: rgba(255, 255, 255, .82);
        padding: 6px;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .aa-home-ad-dots button {
        width: 8px;
        height: 8px;
        border: 0;
        border-radius: 999px;
        background: #cbd5e1;
        padding: 0;
        cursor: pointer;
    }

    .aa-home-ad-dots button.is-active {
        width: 20px;
        background: #00a88a;
    }

    .aa-home-create-form {
        display: grid;
        gap: 14px;
    }

    .aa-home-field {
        display: grid;
        gap: 7px;
    }

    .aa-home-field label {
        color: #334155;
        font-size: 12px;
        font-weight: 900;
    }

    .aa-home-field input {
        width: 100%;
        height: 44px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        background: #ffffff;
        padding: 0 13px;
        color: #0f172a;
        font: inherit;
        font-size: 14px;
        outline: none;
        transition: .16s ease;
    }

    .aa-home-field input:focus {
        border-color: var(--aa-teal);
        box-shadow: 0 0 0 4px rgba(143, 101, 223, .14);
    }

    .aa-home-modal-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: end;
        gap: 10px;
        margin-top: 6px;
    }

    .aa-home-preview-frame {
        width: 100%;
        height: min(72vh, 760px);
        border: 0;
        background: #ffffff;
    }

    .aa-home-preview-light {
        display: grid;
        grid-template-columns: minmax(0, 320px) minmax(0, 1fr);
        gap: 22px;
        align-items: center;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 22px;
    }

    .aa-home-preview-cover {
        position: relative;
        overflow: hidden;
        aspect-ratio: 6 / 10;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background:
            linear-gradient(135deg, rgba(143, 101, 223, .16), transparent 42%),
            linear-gradient(315deg, rgba(143, 101, 223, .14), transparent 44%),
            #f8fafc;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .12);
    }

    .aa-home-preview-cover img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .aa-home-preview-placeholder {
        display: grid;
        height: 100%;
        place-items: center;
        padding: 24px;
        color: #8f65df;
        text-align: center;
    }

    .aa-home-preview-placeholder strong {
        display: block;
        color: #0f172a;
        font-size: 20px;
        font-weight: 950;
        line-height: 1.2;
    }

    .aa-home-preview-copy h4 {
        margin: 0;
        color: #0f172a;
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -.03em;
        line-height: 1.16;
    }

    .aa-home-preview-copy p {
        margin: 12px 0 0;
        color: #64748b;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.7;
    }

    .aa-home-preview-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .aa-home-template-create-dropup {
        position: fixed;
        right: 18px;
        bottom: 18px;
        z-index: 90;
        display: none;
        width: min(420px, calc(100vw - 24px));
        border: 1px solid rgba(226, 232, 240, .92);
        border-radius: 24px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 28px 90px rgba(15, 23, 42, .28);
        overflow: hidden;
    }

    .aa-home-template-create-dropup.is-open {
        display: block;
    }

    .aa-home-template-create-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 18px;
    }

    .aa-home-template-create-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
        letter-spacing: -.025em;
    }

    .aa-home-template-create-head p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
    }

    .aa-home-template-create-close {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        background: #ffffff;
        color: #334155;
        cursor: pointer;
        font-size: 20px;
        font-weight: 900;
        line-height: 1;
    }

    .aa-home-template-create-form {
        display: grid;
        gap: 12px;
        padding: 18px;
    }

    @media (max-width: 720px) {
        .aa-home-editor-mascot-tooltip {
            right: 0;
            max-width: calc(100vw - 28px);
            transform: none;
        }

        .aa-home-editor-mascot-tooltip span {
            right: 0;
            width: max-content;
            max-width: min(240px, calc(100vw - 28px));
            transform: translateY(8px);
            font-size: 10px;
            text-align: right;
            animation-name: aa-home-mascot-tooltip-mobile;
        }

        @keyframes aa-home-mascot-tooltip-mobile {

            0%,
            5% {
                opacity: 0;
                transform: translateY(8px);
            }

            8%,
            28% {
                opacity: 1;
                transform: translateY(0);
            }

            33%,
            100% {
                opacity: 0;
                transform: translateY(-4px);
            }
        }

        .aa-home-modal-card.preview {
            max-height: calc(100vh - 28px);
        }

        .aa-home-modal-card.preview .aa-home-modal-head {
            flex: 0 0 auto;
        }

        .aa-home-preview-light {
            grid-template-columns: 1fr;
            flex: 1 1 auto;
            min-height: 0;
            padding: 16px;
        }

        .aa-home-preview-cover {
            width: min(100%, 280px);
            max-height: 44vh;
            margin: 0 auto;
        }

        .aa-home-preview-actions {
            display: grid;
        }

        .aa-home-template-create-dropup {
            right: 12px;
            left: 12px;
            bottom: 12px;
            width: auto;
            max-height: calc(100vh - 24px);
            overflow: auto;
        }

        .aa-home-template-create-form .aa-home-modal-actions {
            display: grid;
            justify-content: stretch;
        }
    }

    .aa-home-step {
        position: relative;
        padding-left: 58px;
    }

    .aa-home-step-number {
        position: absolute;
        left: 0;
        top: 0;
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 50%;
        background: var(--aa-teal);
        color: #ffffff;
        font-weight: 950;
    }

    .aa-home-pricing {
        border: 1px solid var(--aa-line);
        border-radius: 28px;
        background: #ffffff;
        padding: 28px;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .06);
    }

    .aa-home-price {
        margin-top: 14px;
        font-size: 36px;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .aa-home-list {
        display: grid;
        gap: 10px;
        margin: 18px 0 0;
        padding: 0;
        list-style: none;
        color: #475569;
        font-size: 14px;
        line-height: 1.55;
    }

    .aa-home-list li {
        display: flex;
        gap: 9px;
    }

    .aa-home-list li::before {
        content: "✓";
        color: var(--aa-teal);
        font-weight: 950;
    }

    .aa-home-faq {
        display: grid;
        gap: 12px;
    }

    .aa-home-faq-item {
        border: 1px solid var(--aa-line);
        border-radius: 18px;
        background: #ffffff;
        overflow: hidden;
    }

    .aa-home-faq-btn {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border: 0;
        background: transparent;
        padding: 18px 20px;
        color: var(--aa-ink);
        cursor: pointer;
        font: inherit;
        font-weight: 900;
        text-align: left;
    }

    .aa-home-faq-content {
        display: none;
        padding: 0 20px 18px;
        color: var(--aa-muted);
        line-height: 1.75;
    }

    .aa-home-faq-item.is-open .aa-home-faq-content {
        display: block;
    }

    .aa-home-final {
        overflow: hidden;
        border-radius: 34px;
        background:
            radial-gradient(circle at 12% 10%, rgba(20, 184, 166, .30), transparent 28rem),
            radial-gradient(circle at 86% 18%, rgba(168, 120, 241, .22), transparent 24rem),
            #0f172a;
        padding: 54px;
        color: #ffffff;
    }

    .aa-home-final h2 {
        max-width: 760px;
        color: #ffffff;
        font-size: clamp(34px, 5vw, 56px);
        font-weight: 950;
        line-height: 1.02;
    }

    .aa-home-final h2:hover {
        color: #d9ccf4;
    }

    .aa-home-final p {
        max-width: 680px;
        margin-top: 18px;
        color: #cbd5e1;
        line-height: 1.8;
    }

    .aa-home-footer {
        border-top: 1px solid var(--aa-line);
        padding: 28px 0;
        color: var(--aa-muted);
        font-size: 14px;
    }

    html[data-aa-home-theme="dark"] {
        color-scheme: dark;
    }

    html[data-aa-home-theme="dark"] .aa-home {
        --aa-ink: #f8fafc;
        --aa-muted: #aab5c4;
        --aa-line: rgba(148, 163, 184, .22);
        --aa-soft: rgba(15, 23, 42, .84);
        --aa-teal: #d9ccf4;
        --aa-teal-dark: #8f65df;
        --aa-gold: #d9ccf4;
        background:
            radial-gradient(circle at 18% 0%, rgba(143, 101, 223, .22), transparent 28rem),
            radial-gradient(circle at 88% 9%, rgba(20, 184, 166, .14), transparent 30rem),
            linear-gradient(180deg, #070b12 0%, #101827 48%, #070b12 100%),
            #070b12;
        color: var(--aa-ink);
    }

    html[data-aa-home-theme="dark"] .aa-home-nav {
        border-bottom-color: rgba(148, 163, 184, .18);
        background: rgba(7, 11, 18, .76);
    }

    html[data-aa-home-theme="dark"] .aa-home-brand,
    html[data-aa-home-theme="dark"] .aa-home h1,
    html[data-aa-home-theme="dark"] .aa-home h2,
    html[data-aa-home-theme="dark"] .aa-home h3,
    html[data-aa-home-theme="dark"] .aa-home-card h3,
    html[data-aa-home-theme="dark"] .aa-home-template-blank-inner strong,
    html[data-aa-home-theme="dark"] .aa-home-path-card h3 {
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-home-nav-links a,
    html[data-aa-home-theme="dark"] .aa-home-nav-actions a:not(.aa-home-btn-primary),
    html[data-aa-home-theme="dark"] .aa-home-mobile-panel a,
    html[data-aa-home-theme="dark"] .aa-home-mobile-panel button {
        color: #cbd5e1;
    }

    html[data-aa-home-theme="dark"] .aa-home-nav-links a:hover,
    html[data-aa-home-theme="dark"] .aa-home-nav-actions a:not(.aa-home-btn-primary):hover,
    html[data-aa-home-theme="dark"] .aa-home-mobile-panel a:hover,
    html[data-aa-home-theme="dark"] .aa-home-mobile-panel button:hover {
        background: rgba(143, 101, 223, .10);
        color: #d9ccf4;
    }

    html[data-aa-home-theme="dark"] .aa-home-theme-toggle,
    html[data-aa-home-theme="dark"] .aa-home-mobile-toggle,
    html[data-aa-home-theme="dark"] .aa-home-btn-secondary,
    html[data-aa-home-theme="dark"] .aa-home-template-filter-select {
        border-color: rgba(148, 163, 184, .28);
        background: rgba(15, 23, 42, .74);
        color: #e2e8f0;
        box-shadow: 0 16px 38px rgba(0, 0, 0, .22);
    }

    html[data-aa-home-theme="dark"] .aa-home-theme-toggle:hover,
    html[data-aa-home-theme="dark"] .aa-home-mobile-toggle:hover,
    html[data-aa-home-theme="dark"] .aa-home-mobile-nav.is-open .aa-home-mobile-toggle,
    html[data-aa-home-theme="dark"] .aa-home-btn-secondary:hover,
    html[data-aa-home-theme="dark"] .aa-home-template-filter-select:hover,
    html[data-aa-home-theme="dark"] .aa-home-template-filter-select:focus {
        border-color: rgba(143, 101, 223, .55);
        background: rgba(143, 101, 223, .12);
        color: #d9ccf4;
    }

    html[data-aa-home-theme="dark"] .aa-home-template-filter::after {
        color: #cbd5e1;
    }

    html[data-aa-home-theme="dark"] .aa-home-mobile-panel,
    html[data-aa-home-theme="dark"] .aa-home-modal-card,
    html[data-aa-home-theme="dark"] .aa-home-template-create-dropup {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .97);
        box-shadow: 0 28px 80px rgba(0, 0, 0, .42);
    }

    html[data-aa-home-theme="dark"] .aa-home-modal-card.project-choice {
        border-color: rgba(168, 85, 247, .28);
        background:
            radial-gradient(circle at 14% 12%, rgba(250, 204, 21, .10), transparent 26%),
            radial-gradient(circle at 82% 18%, rgba(168, 85, 247, .18), transparent 30%),
            rgba(15, 23, 42, .98);
        box-shadow: 0 30px 90px rgba(0, 0, 0, .42);
    }

    html[data-aa-home-theme="dark"] .aa-home-project-choice-head h3,
    html[data-aa-home-theme="dark"] .aa-home-project-copy h4 {
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-home-project-choice-head p,
    html[data-aa-home-theme="dark"] .aa-home-project-copy p,
    html[data-aa-home-theme="dark"] .aa-home-project-foot {
        color: #a8b5c7;
    }

    html[data-aa-home-theme="dark"] .aa-home-project-choice-close,
    html[data-aa-home-theme="dark"] .aa-home-project-card,
    html[data-aa-home-theme="dark"] .aa-home-project-card.is-gold,
    html[data-aa-home-theme="dark"] .aa-home-project-card.is-soft {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(30, 41, 59, .78);
        color: #e2e8f0;
        box-shadow: none;
    }

    html[data-aa-home-theme="dark"] .aa-home-project-card:hover,
    html[data-aa-home-theme="dark"] .aa-home-project-choice-close:hover {
        border-color: rgba(143, 101, 223, .52);
        background: rgba(143, 101, 223, .14);
        color: #d9ccf4;
        box-shadow: 0 18px 42px rgba(0, 0, 0, .24);
    }

    html[data-aa-home-theme="dark"] .aa-home-project-icon,
    html[data-aa-home-theme="dark"] .aa-home-project-arrow {
        background: rgba(143, 101, 223, .16);
        color: #d9ccf4;
        box-shadow: inset 0 0 0 1px rgba(216, 204, 244, .18);
    }

    html[data-aa-home-theme="dark"] .aa-home-project-card.is-gold .aa-home-project-icon,
    html[data-aa-home-theme="dark"] .aa-home-project-card.is-gold .aa-home-project-arrow {
        background: rgba(245, 158, 11, .16);
        color: #fcd34d;
    }

    html[data-aa-home-theme="dark"] .aa-home-project-card.is-soft .aa-home-project-icon,
    html[data-aa-home-theme="dark"] .aa-home-project-card.is-soft .aa-home-project-arrow {
        background: rgba(244, 114, 182, .14);
        color: #f9a8d4;
    }

    html[data-aa-home-theme="dark"] .aa-home-ad-dots {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .72);
    }

    html[data-aa-home-theme="dark"] .aa-home-eyebrow,
    html[data-aa-home-theme="dark"] .aa-home-proof-item,
    html[data-aa-home-theme="dark"] .aa-home-card,
    html[data-aa-home-theme="dark"] .aa-home-studio-board,
    html[data-aa-home-theme="dark"] .aa-home-brief-panel,
    html[data-aa-home-theme="dark"] .aa-home-brief-chip,
    html[data-aa-home-theme="dark"] .aa-home-prompt-card,
    html[data-aa-home-theme="dark"] .aa-home-mini-card,
    html[data-aa-home-theme="dark"] .aa-home-lab-strip span,
    html[data-aa-home-theme="dark"] .aa-home-flow-card,
    html[data-aa-home-theme="dark"] .aa-home-path-card,
    html[data-aa-home-theme="dark"] .aa-home-pricing,
    html[data-aa-home-theme="dark"] .aa-home-faq-item,
    html[data-aa-home-theme="dark"] .aa-home-template-preview,
    html[data-aa-home-theme="dark"] .aa-home-template-blank-preview,
    html[data-aa-home-theme="dark"] .aa-home-floating-card,
    html[data-aa-home-theme="dark"] .aa-home-canvas-chip,
    html[data-aa-home-theme="dark"] .aa-home-feature-chip,
    html[data-aa-home-theme="dark"] .aa-home-rsvp-feature-list span,
    html[data-aa-home-theme="dark"] .aa-home-editor-panel,
    html[data-aa-home-theme="dark"] .aa-home-artboard,
    html[data-aa-home-theme="dark"] .aa-home-feature-detail,
    html[data-aa-home-theme="dark"] .aa-home-ai-step {
        border-color: rgba(148, 163, 184, .22);
        background: rgba(15, 23, 42, .76);
        color: #e2e8f0;
        box-shadow: 0 22px 58px rgba(0, 0, 0, .24);
    }

    html[data-aa-home-theme="dark"] .aa-home-section.alt {
        background:
            radial-gradient(circle at 16% 8%, rgba(143, 101, 223, .08), transparent 24rem),
            rgba(15, 23, 42, .34);
    }

    html[data-aa-home-theme="dark"] .aa-home-radiant-card {
        border-color: rgba(148, 163, 184, .22);
        background:
            radial-gradient(circle at 50% 0%, rgba(20, 184, 166, .18), transparent 34%),
            linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(2, 6, 23, .88));
        box-shadow: 0 40px 110px rgba(0, 0, 0, .44);
    }

    html[data-aa-home-theme="dark"] .aa-home-radiant-card::before,
    html[data-aa-home-theme="dark"] .aa-home-hero::before {
        opacity: .34;
    }

    html[data-aa-home-theme="dark"] .aa-home-radiant-orb.one {
        background: rgba(143, 101, 223, .32);
    }

    html[data-aa-home-theme="dark"] .aa-home-radiant-orb.two {
        background: rgba(20, 184, 166, .22);
    }

    html[data-aa-home-theme="dark"] .aa-home-lead,
    html[data-aa-home-theme="dark"] .aa-home-section-head p,
    html[data-aa-home-theme="dark"] .aa-home-card p,
    html[data-aa-home-theme="dark"] .aa-home-flow-card p,
    html[data-aa-home-theme="dark"] .aa-home-path-card p,
    html[data-aa-home-theme="dark"] .aa-home-template-blank-inner span,
    html[data-aa-home-theme="dark"] .aa-home-brief-panel p,
    html[data-aa-home-theme="dark"] .aa-home-mini-card span,
    html[data-aa-home-theme="dark"] .aa-home-faq-content,
    html[data-aa-home-theme="dark"] .aa-home-list {
        color: #aab5c4;
    }

    html[data-aa-home-theme="dark"] .aa-home-mini-card strong,
    html[data-aa-home-theme="dark"] .aa-home-lab-strip span {
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-home-prompt-kicker,
    html[data-aa-home-theme="dark"] .aa-home-mini-badges em {
        background: rgba(143, 101, 223, .16);
        color: #d9ccf4;
    }

    html[data-aa-home-theme="dark"] .aa-home-flow-card .font-display {
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-home-flow-card .bg-primary {
        background: linear-gradient(135deg, #d9ccf4, #8f65df);
        color: #111827;
    }

    html[data-aa-home-theme="dark"] .aa-home-flow-card svg.text-primary\/70 {
        color: rgba(143, 101, 223, .72);
    }

    html[data-aa-home-theme="dark"] .aa-home .aa-home-flow .bg-surface,
    html[data-aa-home-theme="dark"] .aa-home .aa-home-flow .shadow-soft {
        border-color: rgba(148, 163, 184, .22) !important;
        background: rgba(15, 23, 42, .76) !important;
        color: #e2e8f0 !important;
        box-shadow: 0 22px 58px rgba(0, 0, 0, .24) !important;
    }

    html[data-aa-home-theme="dark"] .aa-home .aa-home-flow .border-border {
        border-color: rgba(148, 163, 184, .22) !important;
    }

    html[data-aa-home-theme="dark"] .aa-home .aa-home-flow .text-muted-foreground {
        color: #aab5c4 !important;
    }

    html[data-aa-home-theme="dark"] .aa-home .aa-home-flow .text-primary\/70 {
        color: rgba(143, 101, 223, .72) !important;
    }

    html[data-aa-home-theme="dark"] .aa-home-editor {
        --aa-home-editor-fade: #0f172a;
        --aa-home-editor-fade-clear: rgba(15, 23, 42, 0);
        border-color: rgba(148, 163, 184, .22);
        background:
            radial-gradient(circle at 18% 12%, rgba(143, 101, 223, .22), transparent 34%),
            linear-gradient(135deg, rgba(15, 23, 42, .92), rgba(2, 6, 23, .88));
        box-shadow: 0 34px 90px rgba(0, 0, 0, .34);
    }

    html[data-aa-home-theme="dark"] .aa-home-editor-card {
        border-color: rgba(148, 163, 184, .18);
        background: rgba(15, 23, 42, .62);
        box-shadow: 0 18px 42px rgba(0, 0, 0, .28);
    }

    html[data-aa-home-theme="dark"] .aa-home-field input {
        border-color: rgba(148, 163, 184, .28);
        background: rgba(2, 6, 23, .62);
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-home-field input::placeholder {
        color: #64748b;
    }

    html[data-aa-home-theme="dark"] .aa-home-hero-mascot,
    html[data-aa-home-theme="dark"] .aa-home-section-mascot,
    html[data-aa-home-theme="dark"] .aa-home-card-mascot,
    html[data-aa-home-theme="dark"] .aa-home-path-mascot,
    html[data-aa-home-theme="dark"] .aa-home-editor-mascot img {
        filter: drop-shadow(0 20px 34px rgba(0, 0, 0, .36));
    }

    html[data-aa-home-theme="dark"] .aa-home .aa-home-brand-logo {
        filter: invert(1) brightness(1.22) contrast(.94) drop-shadow(0 14px 24px rgba(0, 0, 0, .34));
    }

    html[data-aa-home-theme="dark"] .aa-home-photobooth {
        border-color: rgba(148, 163, 184, .18);
        background:
            radial-gradient(circle at 12% 18%, rgba(143, 101, 223, .22), transparent 30%),
            radial-gradient(circle at 88% 10%, rgba(20, 184, 166, .12), transparent 28%),
            linear-gradient(180deg, rgba(15, 23, 42, .42), rgba(2, 6, 23, .72));
    }

    html[data-aa-home-theme="dark"] .aa-home-photobooth-visual,
    html[data-aa-home-theme="dark"] .aa-home-photobooth-step,
    html[data-aa-home-theme="dark"] .aa-home-photobooth-note,
    html[data-aa-home-theme="dark"] .aa-home-photobooth-chip {
        border-color: rgba(148, 163, 184, .22);
        background: rgba(15, 23, 42, .76);
        color: #e2e8f0;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .28);
    }

    html[data-aa-home-theme="dark"] .aa-home-photobooth-copy p,
    html[data-aa-home-theme="dark"] .aa-home-photobooth-chip span {
        color: #aab5c4;
    }

    html[data-aa-home-theme="dark"] .aa-home-photobooth-step strong,
    html[data-aa-home-theme="dark"] .aa-home-photobooth-chip strong {
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-home-photobooth-note {
        color: #d9ccf4;
    }

    @media (max-width: 1020px) {
        .aa-home-nav-inner {
            grid-template-columns: auto auto;
            justify-content: space-between;
        }

        .aa-home-nav-links,
        .aa-home-nav-actions {
            display: none;
        }

        .aa-home-mobile-nav {
            display: flex;
            justify-self: end;
        }

        .aa-home-hero-grid,
        .aa-home-editor,
        .aa-home-feature-deep,
        .aa-home-photobooth-grid,
        .aa-home-studio-layout {
            grid-template-columns: 1fr;
        }

        .aa-home-preview {
            min-height: 560px;
        }

        .aa-home-floating-card {
            right: 18px;
        }

        .aa-home-floating-card.bottom {
            left: 18px;
        }

        .aa-home-radiant-card {
            min-height: 520px;
        }

        .aa-home-hero-mascot {
            width: min(330px, 70%);
        }

        .aa-home-editor-mascot {
            width: min(150px, 22vw);
        }

        .aa-home-editor {
            border-radius: 22px;
            padding: 10px;
        }

        .aa-home-editor::before,
        .aa-home-editor::after {
            width: min(74px, 20%);
        }

        .aa-home-editor-track {
            animation-duration: 32s;
        }

        .aa-home-editor-card {
            flex-basis: min(78vw, 520px);
            margin: 0 10px 0 0;
            border-radius: 18px;
        }

        .aa-home-grid.cols-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-home-grid.cols-3 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-home-cuan>div,
        .aa-home-flow>div:last-child,
        .aa-home-showcase-stack,
        .aa-home-path-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-home-template-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .aa-home-maker-top {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .aa-home-maker-copy {
            text-align: center;
        }

        .aa-home-maker-title,
        .aa-home-maker-lead {
            margin-right: auto;
            margin-left: auto;
        }

        .aa-home-maker-actions {
            justify-content: center;
        }

        .aa-home-maker-visual {
            min-height: 420px;
        }

        .aa-home-maker-note {
            left: 9%;
        }

        .aa-home-maker-track {
            grid-auto-columns: minmax(168px, 28vw);
        }

        .aa-home-maker-laptop-heart {
            left: 45.5%;
            top: 57%;
        }

        .aa-home-photobooth-visual {
            min-height: 520px;
        }
    }

    @media (max-width: 720px) {
        .aa-home-shell {
            width: min(100% - 22px, 1180px);
        }

        .aa-home-nav-inner {
            min-height: 64px;
        }

        .aa-home-hero {
            padding-top: 44px;
        }

        .aa-home-hero.is-maker {
            padding: 38px 20px 36px;
        }

        .aa-home-maker-copy {
            padding-top: 0;
            text-align: left;
        }

        .aa-home-maker-title {
            font-size: clamp(42px, 12vw, 58px);
        }

        .aa-home-maker-lead {
            font-size: 16px;
            line-height: 1.65;
        }

        .aa-home-maker-actions {
            justify-content: flex-start;
        }

        .aa-home-maker-primary {
            min-width: min(100%, 282px);
            min-height: 58px;
            font-size: 18px;
        }

        .aa-home-maker-visual {
            min-height: 330px;
            margin-top: 8px;
        }

        .aa-home-maker-mascot {
            width: min(260px, 70%);
        }

        .aa-home-maker-note {
            left: 0;
            top: 5%;
            font-size: 18px;
        }

        .aa-home-maker-note::after {
            width: 110px;
        }

        .aa-home-maker-heart {
            right: 8%;
            top: 30%;
            font-size: 36px;
        }

        .aa-home-maker-tools {
            margin-top: 18px;
        }

        .aa-home-maker-tools-title {
            margin-bottom: 16px;
            text-align: left;
        }

        .aa-home-maker-track {
            grid-auto-columns: minmax(185px, 70vw);
            gap: 10px;
            padding: 0 8px 16px;
        }

        .aa-home-maker-tool-card {
            min-height: 200px;
            border-radius: 18px;
            padding: 20px 16px;
        }

        .aa-home-maker-tool-visual {
            width: 76px;
            height: 76px;
            margin-bottom: 18px;
            border-radius: 20px;
        }

        .aa-home-maker-tool-fallback {
            font-size: 40px;
        }

        .aa-home-maker-laptop-heart {
            left: 41%;
            top: 58%;
            font-size: 32px;
        }

        .aa-home-maker-tool-card h3 {
            font-size: 14px;
        }

        .aa-home-maker-tool-card p {
            font-size: 11px;
            line-height: 1.65;
        }

        .aa-home-maker-arrow {
            display: grid;
            width: 40px;
            height: 40px;
            top: -10%;
        }

        .aa-home-photobooth-grid {
            gap: 24px;
        }

        .aa-home-photobooth-copy h2 {
            font-size: clamp(30px, 9vw, 42px);
        }

        .aa-home-photobooth-steps {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-home-photobooth-actions {
            display: grid;
        }

        .aa-home-photobooth-actions .aa-home-btn {
            width: 100%;
            justify-content: center;
        }

        .aa-home-photobooth-visual {
            min-height: 610px;
            border-radius: 28px;
        }

        .aa-home-photobooth-phone {
            top: 47%;
            width: min(238px, 74%);
            min-height: 420px;
            border-width: 8px;
            border-radius: 36px;
        }

        .aa-home-photobooth-screen {
            min-height: 420px;
            padding: 48px 16px 22px;
            border-radius: 28px;
        }

        .aa-home-photobooth-screen video {
            width: calc(100% + 32px);
            min-height: 420px;
            margin: -48px -16px -22px;
        }

        .aa-home-photobooth-chip.is-qr {
            left: 14px;
            top: 16px;
            width: 150px;
        }

        .aa-home-photobooth-chip.is-link {
            right: 14px;
            top: 26px;
            width: 146px;
        }

        .aa-home-photobooth-chip.is-gallery {
            right: 14px;
            bottom: 16px;
            width: 184px;
        }

        .aa-home-photobooth-qr {
            width: 82px;
            height: 82px;
            border-width: 7px;
            border-radius: 18px;
        }

        .aa-home-photobooth-gallery {
            gap: 6px;
        }

        .aa-home-photobooth-gallery i {
            border-width: 2px;
            border-radius: 12px;
        }

        .aa-home-proof,
        .aa-home-feature-deep,
        .aa-home-showcase-stack,
        .aa-home-lab-strip,
        .aa-home-cuan>div,
        .aa-home-flow>div:last-child,
        .aa-home-path-grid,
        .aa-home-grid.cols-4,
        .aa-home-grid.cols-3,
        .aa-home-grid.cols-2 {
            grid-template-columns: 1fr;
        }

        .aa-home-cuan>div {
            padding-right: 0;
            padding-left: 0;
        }

        .aa-home-template-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .aa-home-section {
            padding: 52px 0;
        }

        .aa-home-studio-board {
            border-radius: 24px;
            padding: 12px;
        }

        .aa-home-brief-panel,
        .aa-home-prompt-card,
        .aa-home-mini-card {
            border-radius: 20px;
        }

        .aa-home-prompt-card.is-featured {
            min-height: 360px;
        }

        .aa-home-section-head {
            display: block;
        }

        .aa-home-section-head p {
            margin-top: 12px;
        }

        .aa-home-section-head.has-mascot {
            padding-right: 96px;
        }

        .aa-home-section-mascot {
            width: 82px;
            right: 0;
            bottom: -8px;
        }

        .aa-home-mascot-card {
            padding-right: 88px !important;
        }

        .aa-home-card-mascot {
            width: 86px;
            right: -10px;
            bottom: -14px;
        }

        .aa-home-preview {
            min-height: auto;
        }

        .aa-home-radiant-card {
            width: 100%;
            min-height: 430px;
            border-radius: 26px;
        }

        .aa-home-hero-mascot {
            width: min(280px, 72%);
        }

        .aa-home-animation-frame {
            width: min(220px, 64%);
            height: min(220px, 64%);
            border-width: 8px;
            border-radius: 26px;
        }

        .aa-home-canvas-chip.top {
            top: 22px;
            left: 18px;
        }

        .aa-home-canvas-chip.bottom {
            right: 18px;
            bottom: 22px;
        }

        .aa-home-feature-chips {
            top: 72px;
            right: 16px;
            bottom: auto;
            left: auto;
            gap: 7px;
            justify-items: end;
        }

        .aa-home-feature-chip {
            padding: 7px 9px;
            font-size: 10px;
        }

        .aa-home-floating-card {
            position: relative;
            inset: auto;
            width: 100%;
            margin-top: 12px;
        }

        .aa-home-floating-card.bottom {
            inset: auto;
        }

        .aa-home-artboard {
            min-height: auto;
        }

        .aa-home-path-card.has-mascot {
            padding-right: 24px;
            padding-bottom: 120px;
        }

        .aa-home-path-mascot {
            right: 4px;
            bottom: -18px;
            width: 116px;
        }

        .aa-home-editor-mascot {
            right: 10px;
            bottom: 8px;
            width: min(118px, 28vw);
        }

        .aa-home-final {
            border-radius: 26px;
            padding: 34px 22px;
        }
    }

    @media (max-width: 460px) {
        .aa-home-template-grid {
            grid-template-columns: 1fr 1fr;
        }

    }

    .aa-stats {
        margin-bottom: 40px;
    }

    .aa-stat-card {
        padding: 30px;
        border: 1px solid #ececec;
        border-radius: 20px;
        background: #fff;
    }

    .aa-stat-card h3 {
        font-size: 34px;
        margin-bottom: 10px;
    }

    .aa-stat-card p {
        opacity: .7;
    }

    html[data-aa-home-theme="dark"] .aa-stat-card {
        border-color: rgba(148, 163, 184, .22);
        background: rgba(15, 23, 42, .76);
        color: #e2e8f0;
        box-shadow: 0 22px 58px rgba(0, 0, 0, .24);
    }

    html[data-aa-home-theme="dark"] .aa-stat-card h3 {
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-stat-card p {
        color: #aab5c4;
        opacity: 1;
    }

    .aa-footnote {
        margin-top: 35px;
        opacity: .6;
        line-height: 1.7;
        font-size: 13px;
    }

    #aaBrowserNotice {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    #aaBrowserNotice.aa-show {
        display: flex;
    }

    #aaBrowserNotice .aa-browser-card {
        position: relative;
        width: min(100%, 390px);
        background: #fff;
        border-radius: 22px;
        padding: 28px 22px 22px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .20);
        text-align: center;
        font-family: Arial, sans-serif;
        animation: aaBrowserIn .22s ease-out;
    }

    #aaBrowserNotice .aa-browser-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 14px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: #f3f4f6;
        font-size: 30px;
    }

    #aaBrowserNotice h3 {
        margin: 0 0 8px;
        font-size: 20px;
        line-height: 1.3;
        color: #111827;
    }

    #aaBrowserNotice p {
        margin: 0 0 18px;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.55;
    }

    #aaBrowserNotice .aa-open-browser {
        appearance: none;
        border: 0;
        width: 100%;
        min-height: 48px;
        border-radius: 14px;
        padding: 12px 16px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        background: #111827;
        color: #fff;
    }

    #aaBrowserNotice .aa-browser-help {
        display: none;
        margin-top: 14px;
        padding: 12px;
        background: #f9fafb;
        border-radius: 12px;
        color: #4b5563;
        font-size: 13px;
        line-height: 1.45;
    }

    #aaBrowserNotice .aa-browser-help.aa-show-help {
        display: block;
    }

    #aaBrowserNotice .aa-close-browser {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #f3f4f6;
        color: #374151;
        font-size: 20px;
        cursor: pointer;
    }

    @keyframes aaBrowserIn {
        from {
            opacity: 0;
            transform: translateY(10px) scale(.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    </style>
</head>

<body class="aa-app-ui aa-home">
    <div id="aaBrowserNotice" aria-hidden="true">
        <div class="aa-browser-card" role="dialog" aria-modal="true" aria-labelledby="aaBrowserTitle">

            <button type="button" class="aa-close-browser" id="aaBrowserClose" aria-label="Tutup">×</button>

            <div class="aa-browser-icon">🌐</div>

            <h3 id="aaBrowserTitle">
                Buka di Browser untuk Full Fitur
            </h3>

            <p>
                Agar kamera, download dan fitur Photobooth berjalan lebih maksimal,
                buka halaman ini melalui browser utama perangkatmu.
            </p>

            <button type="button" class="aa-open-browser" id="aaOpenExternalBrowser">
                Buka di Browser
            </button>

            <div class="aa-browser-help" id="aaBrowserHelp">
                Jika tidak berpindah otomatis, tekan menu
                <strong>⋮ / •••</strong> pada aplikasi lalu pilih
                <strong>Open in Browser / Buka di Browser</strong>.
            </div>

        </div>
    </div>
    <?= view('components/public_site_header', ['active' => 'home']) ?>

    <main>
        <section class="aa-home-hero is-maker">
            <div class="aa-home-shell">
                <div class="aa-home-maker-top">
                    <div class="aa-home-maker-copy">
                        <h1 class="aa-home-maker-title">
                            <span class="aa-home-maker-line">What will <span class="is-purple">you</span> <span
                                    class="is-gold">create</span> today?</span>
                        </h1>
                        <p class="aa-home-maker-lead">
                            <strong>Create every part of your event experience with AdaAcara.</strong>
                            For digital invitations and digital photobooths to more - all powered by AdaAcara Studio.
                        </p>
                        <div class="aa-home-maker-actions">
                            <a class="aa-home-btn aa-home-maker-primary"
                                href="<?= site_url('plans') ?>" data-home-open-project-choice>Create Now
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-8 h-10 ml-5">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.25 8.75 21 12m0 0-3.75 3.25M21 12H3" />

                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="aa-home-maker-visual" aria-hidden="true">
                        <span class="aa-home-maker-wave"></span>
                        <p class="aa-home-maker-note">Design with <span>love</span>,<br>share with joy.</p>
                        <img class="aa-home-maker-mascot" src="<?= aa_asset_url('assets/img/1.png') ?>"
                            alt="Maskot AdaAcara membawa laptop" loading="eager" decoding="async">
                    </div>
                </div>

                <div class="aa-home-maker-tools">
                    <h2 class="aa-home-maker-tools-title pb-8">✦ Powerful tools. <span>Easy to use.</span> ✦</h2>
                    <div class="aa-home-maker-carousel" aria-label="Fitur utama AdaAcara">
                        <button class="aa-home-maker-arrow is-left" type="button" data-aa-home-tools-prev
                            aria-label="Fitur sebelumnya">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <div class="aa-home-maker-track" data-aa-home-tools-track>
                            <?php foreach ([
                                    ['image' => 'color-picker.png'],
                                    ['image' => 'font-styling.png'],
                                    ['image' => 'image-effects.png'],
                                    ['image' => 'animation.png'],
                                    ['image' => 'remove-bg.png'],
                                    ['image' => 'magic-ai-layer.png'],
                                    ['image' => 'youtube-input.png'],
                                    ['image' => 'gallery-input.png'],
                                    ['image' => 'gallery-zoom-input.png'],
                                    ['image' => 'music-settings.png'],
                                    ['image' => 'background-settings.png'],
                                    ['image' => 'template-library.png'],
                                    ['image' => 'drag-drop-editor.png'],
                                    ['image' => 'upload-assets.png'],
                                    ['image' => 'dashboard-share.png'],
                                ] as $tool): ?>

                            <?php
                                    $toolImagePath = 'assets/img/home-tools/' . $tool['image'];
                                    $toolImageFile = ROOTPATH . $toolImagePath;
                                    $toolImageUrl  = is_file($toolImageFile)
                                        ? aa_asset_url($toolImagePath)
                                        : '';
                                ?>

                            <article class="aa-home-maker-tool-card">

                                <?php if ($toolImageUrl !== ''): ?>

                                <img class="aa-home-maker-tool-image" src="<?= esc($toolImageUrl) ?>" alt=""
                                    loading="lazy" decoding="async">

                                <?php else: ?>

                                <div class="aa-home-maker-tool-placeholder">

                                    <i class="fa fa-image"></i>

                                </div>

                                <?php endif; ?>

                            </article>

                            <?php endforeach; ?>
                        </div>
                        <button class="aa-home-maker-arrow is-right" type="button" data-aa-home-tools-next
                            aria-label="Fitur berikutnya">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <!-- <section class="aa-home-section" id="studio-brief">
            <div class="aa-home-shell">
                <div class="aa-home-studio-board">
                    <div class="aa-home-studio-layout">
                        <article class="aa-home-brief-panel">
                            <div>
                                <span class="aa-home-eyebrow">Curated design flow</span>
                                <h2>Dari brief acara menjadi halaman undangan yang terasa dibuat khusus.</h2>
                                <p>Bukan sekadar generate random. AdaAcara menaruh template, media, elemen tamu, dan
                                    publish link dalam satu alur yang tetap bisa kamu kontrol manual.</p>
                            </div>
                            <div class="aa-home-brief-stack" aria-label="Contoh brief undangan">
                                <?php foreach ([
	                                    ['01', 'Aqiqah intimate dengan warna sage, foto bayi, countdown, maps, dan ucapan tamu.'],
	                                    ['02', 'Wedding elegan warna ivory-gold, opening halus, RSVP, wedding gift, dan galeri.'],
	                                    ['03', 'Seminar korporat dengan rundown, tombol daftar, lokasi, dan link share WhatsApp.'],
	                                ] as $brief): ?>
                                <span
                                    class="aa-home-brief-chip"><i><?= esc($brief[0]) ?></i><?= esc($brief[1]) ?></span>
                                <?php endforeach ?>
                            </div>
                        </article>
                        <div>
                            <div class="aa-home-showcase-stack">
                                <?php
	                                    $showcaseTemplate = $templates[0] ?? [];
	                                    $showcaseThumb = ! empty($showcaseTemplate['thumbnail'] ?? '') ? base_url($showcaseTemplate['thumbnail']) : aa_asset_url('assets/img/adaacara-design-studio-preview.png');
	                                    $showcaseTitle = (string) ($showcaseTemplate['name'] ?? 'Template undangan pilihan');
	                                ?>
                                <article class="aa-home-prompt-card is-featured">
                                    <img class="aa-home-prompt-cover" src="<?= esc($showcaseThumb, 'attr') ?>"
                                        alt="<?= esc($showcaseTitle, 'attr') ?>" loading="lazy" decoding="async">
                                    <div class="aa-home-prompt-content">
                                        <span class="aa-home-prompt-kicker">Template preview</span>
                                        <h3><?= esc($showcaseTitle) ?></h3>
                                        <p>Pilih desain, sesuaikan nama acara, ganti foto, lalu lanjutkan menjadi
                                            halaman public yang bisa dibuka dari browser tamu.</p>
                                    </div>
                                </article>
                                <div class="aa-home-mini-stack">
                                    <article class="aa-home-mini-card">
                                        <strong>Editor terasa seperti ruang kerja, bukan form panjang.</strong>
                                        <span>Atur teks, gambar, frame foto, warna foil/glitter, animasi, dan halaman
                                            langsung dari canvas.</span>
                                        <div class="aa-home-mini-badges">
                                            <em>Canvas</em><em>Media</em><em>Frame</em>
                                        </div>
                                    </article>
                                    <article class="aa-home-mini-card">
                                        <strong>Halaman tamu tetap punya fungsi nyata.</strong>
                                        <span>RSVP, guestbook, stiker, wedding gift, maps, musik, dan tombol copy bisa
                                            ikut dalam undangan.</span>
                                        <div class="aa-home-mini-badges">
                                            <em>RSVP</em><em>Gift</em><em>Maps</em>
                                        </div>
                                    </article>
                                </div>
                            </div>
                            <div class="aa-home-lab-strip" aria-label="Area kerja AdaAcara">
                                <span>Template sebagai titik awal, bukan batas kreativitas.</span>
                                <span>ACARA AI membantu membaca referensi, tetap perlu review manusia.</span>
                                <span>Publish menghasilkan link public yang mudah dibagikan.</span>
                                <span>Creator dan seller punya jalur kerja yang berbeda.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="aa-home-section" id="pilih-jalur">
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Pilih jalur yang paling cocok untukmu.</h2>
                    <p>Satu akun bisa mulai dari kebutuhan sederhana. Kamu bisa membuat undangan sendiri, memakai tools
                        jualan, atau mengajukan diri sebagai creator template.</p>
                </div>
                <div class="aa-home-path-grid">
                    <article class="aa-home-path-card is-primary">
                        <div>
                            <span class="aa-home-path-kicker">Customer</span>
                            <h3>Saya mau buat undangan sendiri</h3>
                            <p>Pilih template, edit nama acara dan detail tamu, lalu publish menjadi website undangan
                                yang siap dibagikan.</p>
                            <ul class="aa-home-path-list">
                                <li>Cocok untuk wedding, aqiqah, ulang tahun, seminar, dan event keluarga.</li>
                                <li>Mulai dari template free atau upgrade untuk memakai desain premium.</li>
                                <li>Dashboard menampilkan status publish, link, guestbook, dan masa aktif.</li>
                            </ul>
                        </div>
                        <div class="aa-home-path-actions">
                            <a class="aa-home-btn aa-home-btn-primary" href="#photobooth">Photobooth Digital</a>
                            <a class="aa-home-btn aa-home-btn-secondary" href="<?= site_url('plans') ?>">Lihat Paket</a>
                        </div>
                    </article>
                    <article class="aa-home-path-card has-mascot">
                        <div>
                            <span class="aa-home-path-kicker">Seller</span>
                            <h3>Saya mau jual jasa undangan</h3>
                            <p>Gunakan dashboard penjual untuk mencatat lead, follow-up WhatsApp, dan menata calon
                                customer agar jualan lebih rapi.</p>
                            <ul class="aa-home-path-list">
                                <li>Fokus untuk jasa pembuatan undangan dan pengelolaan calon customer.</li>
                                <li>Punya tools lead inbox, pipeline, template follow-up, dan promo assets.</li>
                                <li>Tidak otomatis menerima komisi template creator.</li>
                            </ul>
                        </div>
                        <div class="aa-home-path-actions">
                            <a class="aa-home-btn aa-home-btn-primary" href="<?= site_url('plans') ?>">Lihat Paket
                                Seller</a>
                        </div>
                    </article>
                    <article class="aa-home-path-card">
                        <div>
                            <span class="aa-home-path-kicker">Creator</span>
                            <h3>Saya mau jadi creator template</h3>
                            <p>Ajukan nama creator, kirim template dari editor, lalu template akan direview admin
                                sebelum tampil di marketplace.</p>
                            <ul class="aa-home-path-list">
                                <li>Cocok untuk designer yang ingin membuat aset template jangka panjang.</li>
                                <li>Creator aktif bisa submit template dan melihat earnings.</li>
                                <li>Template tetap melalui approval agar kualitas marketplace terjaga.</li>
                            </ul>
                        </div>
                        <div class="aa-home-path-actions">
                            <a class="aa-home-btn aa-home-btn-primary"
                                href="<?= esc($creatorApplyUrl, 'attr') ?>">Daftar Creator</a>
                            <a class="aa-home-btn aa-home-btn-secondary" href="<?= site_url('plans') ?>#faq">Baca
                                FAQ</a>
                        </div>
                        <img class="aa-home-path-mascot" src="<?= aa_asset_url('assets/img/3.png') ?>" alt=""
                            loading="lazy" decoding="async">
                    </article>
                </div>
            </div>
        </section>
    
        <section class="aa-home-section" id="fitur-editor" hidden>
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Lebih dari template: editor lengkap untuk undangan yang siap dipakai.</h2>
                    <p>Mulai dari upload desain, edit elemen, sampai publish link undangan. Semua dibuat agar pemilik
                        acara, seller, dan creator bisa bekerja dari satu tempat.</p>
                </div>
                <div class="aa-home-feature-deep">
                    <article class="aa-home-ai-panel">
                        <span class="aa-home-ai-badge">Highlight baru: ACARA AI</span>
                        <h2>Upload gambar desain, lalu ubah menjadi halaman yang bisa diedit.</h2>
                        <p>ACARA AI membantu membaca desain referensi, mengenali teks, layout, gambar dekorasi, frame
                            foto, warna, dan posisi elemen. Hasilnya masuk ke editor sebagai objek yang bisa kamu
                            rapikan lagi sebelum disimpan atau dipublish.</p>
                        <div class="aa-home-ai-steps">
                            <div class="aa-home-ai-step">
                                <span>1</span>
                                <div>
                                    <strong>Upload gambar referensi</strong>
                                    <small>Pakai JPG, PNG, atau WEBP sebagai bahan awal desain undangan.</small>
                                </div>
                            </div>
                            <div class="aa-home-ai-step">
                                <span>2</span>
                                <div>
                                    <strong>Baca Design otomatis</strong>
                                    <small>Sistem membantu mengubah layout menjadi teks, gambar, dan frame
                                        editable.</small>
                                </div>
                            </div>
                            <div class="aa-home-ai-step">
                                <span>3</span>
                                <div>
                                    <strong>Review dan rapikan di editor</strong>
                                    <small>Hasil AI bisa tidak selalu persis, jadi kamu tetap punya kontrol penuh untuk
                                        koreksi.</small>
                                </div>
                            </div>
                        </div>
                    </article>
                    <div class="aa-home-feature-list">
                        <?php foreach ([
                            ['✦', 'Editor visual multi halaman', 'Buat opening dan beberapa halaman undangan. Atur teks, gambar, warna, background, posisi, ukuran, layer, crop, dan style langsung di canvas.'],
                            ['▣', 'Frame foto dan media editable', 'Upload gambar sendiri, replace foto, atur background image, pakai frame, dan kelola media library agar desain tetap fleksibel.'],
                            ['♪', 'Musik, galeri, countdown, dan link', 'Tambahkan audio, galeri foto, countdown acara, tombol lokasi, link, copy text, dan elemen interaktif lain tanpa coding.'],
                            ['☑', 'Guestbook, RSVP, komentar, dan stiker', 'Tamu bisa mengisi kehadiran, menulis ucapan, memakai stiker, dan melihat komentar yang masuk pada halaman undangan.'],
                            ['↗', 'Preview ringan dan publish /u/slug', 'Cek tampilan sebelum publish, simpan draft, lalu bagikan link public yang bisa dibuka tamu dari browser mobile.'],
                        ] as $feature): ?>
                        <article class="aa-home-feature-detail">
                            <i><?= esc($feature[0]) ?></i>
                            <div>
                                <h3><?= esc($feature[1]) ?></h3>
                                <p><?= esc($feature[2]) ?></p>
                            </div>
                        </article>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="aa-home-section alt" id="insight">
            <div class="aa-home-shell">

                <div class="aa-home-section-head">
                    <span class="aa-badge">Indonesia Event Insights</span>

                    <h2>Indonesia tidak pernah kehabisan momen untuk dirayakan.</h2>

                    <p>
                        Setiap bulan selalu ada jutaan acara mulai dari pernikahan,
                        kegiatan sekolah, seminar, acara keluarga hingga event perusahaan.
                        Gunakan momen terbaik untuk membuat dan membagikan undangan digital.
                    </p>
                </div>

                <div class="aa-home-grid cols-4 aa-stats">

                    <div class="aa-stat-card">
                        <h3>💍 2 Juta+</h3>
                        <p>Peristiwa Pernikahan / Tahun</p>
                    </div>

                    <div class="aa-stat-card">
                        <h3>🏫 300 Ribu+</h3>
                        <p>Kegiatan Sekolah</p>
                    </div>

                    <div class="aa-stat-card">
                        <h3>🏢 180 Ribu+</h3>
                        <p>Corporate Event</p>
                    </div>

                    <div class="aa-stat-card">
                        <h3>🎉 Jutaan</h3>
                        <p>Acara Keluarga & Sosial</p>
                    </div>

                </div>

                <div class="aa-home-grid cols-4">

                    <article class="aa-home-card">

                        <h3>Tren Event Indonesia 2026</h3>

                        <canvas id="monthlyChart" height="140"></canvas>

                    </article>

                    <article class="aa-home-card">

                        <h3>Distribusi Jenis Acara</h3>

                        <canvas id="categoryChart" height="140"></canvas>

                    </article>

                    <article class="aa-home-card">

                        <h3>Musim Ramai Setiap Kategori</h3>

                        <canvas id="heatChart" height="180"></canvas>

                    </article>

                    <article class="aa-home-card">

                        <h3>Peluang Creator Template</h3>

                        <canvas id="creatorChart" height="180"></canvas>

                    </article>

                </div>

                <div class="aa-footnote">

                    <small>

                        * Data merupakan gabungan statistik publik dan estimasi tren
                        berdasarkan kalender nasional Indonesia, data pernikahan
                        Kementerian Agama, Badan Pusat Statistik (BPS), kalender
                        pendidikan nasional, kalender MICE Kementerian Pariwisata,
                        serta pola musiman penyelenggaraan acara di Indonesia.
                        Grafik digunakan sebagai visualisasi tren, bukan jumlah
                        resmi seluruh acara nasional.

                    </small>

                </div>

            </div>
        </section> -->

        <section class="aa-home-section alt" id="editor">
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Desain dari nol atau edit template — sebebas Canva.</h2>
                    <p>Drag, drop, ganti warna, font, dan foto sesukamu. Tanpa coding, tanpa software berat. Desain bebas di AdaAcara Studio, lalu publikasikan menjadi Undangan Digital, Photobooth, dan pengalaman digital lainnya yang bisa diakses langsung melalui URL.</p>
                </div>
                <?php
                    $homeEditorShowcaseImages = [];
                    for ($homeEditorShowcaseIndex = 1; $homeEditorShowcaseIndex <= 30; $homeEditorShowcaseIndex++) {
                        $homeEditorShowcasePath = 'assets/home/editor-showcase-' . $homeEditorShowcaseIndex . '.png';
                        $homeEditorShowcaseLocalPath = str_replace('/', DIRECTORY_SEPARATOR, $homeEditorShowcasePath);
                        $homeEditorShowcaseExists = is_file(FCPATH . $homeEditorShowcaseLocalPath);
                        if (! $homeEditorShowcaseExists && defined('ROOTPATH')) {
                            $homeEditorShowcaseExists = is_file(ROOTPATH . $homeEditorShowcaseLocalPath);
                        }
                        if (! $homeEditorShowcaseExists) {
                            continue;
                        }
                        $homeEditorShowcaseImages[] = [
                            'src' => $homeEditorShowcasePath,
                            'alt' => 'Preview editor AdaAcara ' . $homeEditorShowcaseIndex,
                        ];
                    }
                    if ($homeEditorShowcaseImages === []) {
                        $homeEditorShowcaseImages[] = [
                            'src' => 'assets/img/adaacara-design-studio-preview.png',
                            'alt' => 'Preview editor AdaAcara',
                        ];
                    }
                    $homeEditorShowcaseLoop = array_merge($homeEditorShowcaseImages, $homeEditorShowcaseImages);
                    $homeEditorShowcaseCount = count($homeEditorShowcaseImages);
                ?>
                <div class="aa-home-editor" aria-label="Preview fitur editor AdaAcara">
                    <div class="aa-home-editor-track">
                        <?php foreach ($homeEditorShowcaseLoop as $editorShowcaseIndex => $editorShowcaseImage): ?>
                        <?php $isDuplicateEditorShowcase = $editorShowcaseIndex >= $homeEditorShowcaseCount; ?>
                        <figure class="aa-home-editor-card"
                            <?= $isDuplicateEditorShowcase ? 'aria-hidden="true"' : '' ?>>
                            <img class="aa-home-editor-image"
                                src="<?= esc(aa_asset_url((string) $editorShowcaseImage['src']), 'attr') ?>"
                                alt="<?= $isDuplicateEditorShowcase ? '' : esc((string) $editorShowcaseImage['alt'], 'attr') ?>"
                                loading="lazy" decoding="async">
                        </figure>
                        <?php endforeach ?>
                    </div>
                    <a class="aa-home-editor-mascot" href="<?= site_url('plans') ?>#faq"
                        aria-label="Buka pertanyaan umum">
                        <span class="aa-home-editor-mascot-tooltip" aria-hidden="true">
                            <span>Halo, Kenalin aku Bebek Assistant</span>
                            <span>Selamat Datang di adaAcara.com</span>
                            <span>Klik disini jika ada pertanyaan</span>
                        </span>
                        <img src="<?= aa_asset_url('assets/img/2.png') ?>" alt="" loading="lazy" decoding="async">
                    </a>
                </div>
            </div>
        </section>

        <section class="aa-home-section" id="rsvp">
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Bukan cuma desain. Website acaramu langsung punya fitur.</h2>
                    <p>Setelah desain selesai, halaman public bisa langsung dipakai tamu untuk RSVP, ucapan, galeri, maps, countdown, musik, gift, dan kebutuhan acara lainnya.</p>
                </div>
                <div class="aa-home-grid cols-2">
                    <article class="aa-home-card dark">
                        <div class="aa-home-icon"><?= aa_phosphor_icon('envelope', ['strokeWidth' => '2.05']) ?></div>
                        <h3>Interaksi tamu dalam satu tempat</h3>
                        <p>Kumpulkan RSVP, ucapan, stiker, foto, dan respon tamu tanpa memisahkan pengalaman ke banyak link.</p>
                    </article>
                    <article class="aa-home-card">
                        <div class="aa-home-icon"><?= aa_phosphor_icon('sparkles', ['strokeWidth' => '2.05']) ?></div>
                        <h3>Fitur mengikuti kebutuhan acara</h3>
                        <p>Tambahkan musik, gift, galeri, maps, countdown, informasi acara, tombol aksi, dan elemen lain sesuai jenis project yang kamu buat.</p>
                    </article>
                </div>
                <div class="aa-home-rsvp-feature-list" aria-label="Daftar fitur website acara">
                    <?php foreach ([
                        ['music', 'Musik'],
                        ['gift', 'Wedding Gift'],
                        ['card', 'RSVP'],
                        ['envelope', 'Ucapan Tamu'],
                        ['map-pin', 'Maps'],
                        ['clock', 'Countdown'],
                        ['camera', 'Galeri Foto'],
                        ['instagram-logo', 'Social Media'],
                        ['cursor-click', 'Tombol Aksi'],
                    ] as $rsvpFeature): ?>
                        <span><i class="aa-home-rsvp-feature-icon"><?= aa_phosphor_icon($rsvpFeature[0], ['strokeWidth' => '2.15']) ?></i><?= esc($rsvpFeature[1]) ?></span>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <section class="aa-home-section aa-home-photobooth" id="photobooth">
            <div class="aa-home-shell">
                <div class="aa-home-photobooth-grid">
                    <div class="aa-home-photobooth-copy">
                        <span class="aa-home-eyebrow">Photobooth Digital</span>
                        <h2>Photobooth digital untuk momen tamu.</h2>
                        <p>Tamu cukup scan QR, pilih frame, ambil foto, lalu hasilnya masuk ke galeri memories acara.
                        </p>
                        <div class="aa-home-photobooth-steps" aria-label="Alur Photobooth AdaAcara">
                            <?php foreach ([
                                ['01', 'Scan QR', 'assets/img/plans/showcase-1.mp4', 'video'],
                                ['02', 'Pilih frame', 'assets/img/plans/showcase-4.png', 'image'],
                                ['03', 'Ambil foto', 'assets/img/plans/showcase-5.png', 'image'],
                                ['04', 'Galeri memories', 'assets/img/plans/showcase-7.png', 'image'],
                            ] as $photoboothStep): ?>
                            <div class="aa-home-photobooth-step">
                                <span class="aa-home-photobooth-step-media" aria-hidden="true">
                                    <?php if ($photoboothStep[3] === 'video'): ?>
                                    <video src="<?= esc(aa_asset_url($photoboothStep[2]), 'attr') ?>" autoplay muted
                                        loop playsinline preload="metadata"></video>
                                    <?php else: ?>
                                    <img src="<?= esc(aa_asset_url($photoboothStep[2]), 'attr') ?>" alt=""
                                        loading="lazy" decoding="async">
                                    <?php endif ?>
                                </span>
                                <span class="aa-home-photobooth-step-head">
                                    <span class="aa-home-photobooth-step-number"><?= esc($photoboothStep[0]) ?></span>
                                    <strong><?= esc($photoboothStep[1]) ?></strong>
                                </span>
                            </div>
                            <?php endforeach ?>
                        </div>
                        <div class="aa-home-photobooth-actions">
                            <a class="aa-home-btn aa-home-btn-primary"
                                href="<?= site_url('u/garden-flyrin/memories') ?>">Coba Preview Photobooth</a>
                            <a class="aa-home-btn aa-home-btn-secondary" href="<?= site_url('templates') ?>">Lihat
                                Template</a>
                        </div>
                        <span class="aa-home-photobooth-note">Photobooth bisa dipakai sebagai pengalaman tambahan di
                            lokasi acara: cukup cetak QR, tamu buka dari HP, lalu hasilnya terkumpul otomatis.</span>
                    </div>

                    <div class="aa-home-photobooth-visual" aria-label="Preview Photobooth AdaAcara">
                        <div class="aa-home-photobooth-chip is-qr">
                            <strong>QR Photobooth</strong>
                            <div class="aa-home-photobooth-qr" aria-hidden="true">
                                <img src="<?= esc(aa_asset_url('assets/home/galeri/qr.png'), 'attr') ?>" alt="" loading="lazy" decoding="async">
                            </div>
                            <span class="aa-home-photobooth-qr-meta">Scan dari HP tamu</span>
                        </div>

                        <div class="aa-home-photobooth-phone" aria-hidden="true">
                            <div class="aa-home-photobooth-screen">
                                <video src="<?= esc(aa_asset_url('assets/img/plans/showcase-1.mp4'), 'attr') ?>"
                                    autoplay muted loop playsinline preload="metadata"></video>
                            </div>
                        </div>

                        <div class="aa-home-photobooth-chip is-link">
                            <strong>Live di undangan</strong>
                            <span class="aa-home-photobooth-link">/u/slug/memories</span>
                            <span>Tidak perlu aplikasi tambahan.</span>
                        </div>

                        <div class="aa-home-photobooth-chip is-gallery">
                            <strong>Galeri tamu</strong>
                            <div class="aa-home-photobooth-gallery" aria-hidden="true">
                                <i></i><i></i><i></i>
                            </div>
                            <div class="aa-home-photobooth-gallery-meta"><span>24 foto masuk</span><span>Live gallery</span></div>
                            <span>Hasil foto tersimpan rapi per acara.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="aa-home-section alt" id="galeri-klien-fotografer">
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <span class="aa-home-eyebrow">Untuk Usaha Fotografer</span>
                    <h2>Galeri Klien Fotografer untuk membagikan foto lebih rapi.</h2>
                    <p>Buat project client, upload foto, atur album dan PIN, lalu client bisa memilih favorit, foto untuk dicetak, memberi revisi, download, dan menyiapkan halaman keluarga.</p>
                </div>
                <div class="grid gap-5 lg:grid-cols-[1.05fr_.95fr] lg:items-stretch">
                    <article class="aa-home-card dark aa-home-gallery-showcase">
                        <div>
                            <h3>Tools khusus fotografer, tanpa masuk editor.</h3>
                            <p>Alurnya dibuat untuk pekerjaan setelah sesi foto: upload banyak foto, kelola album, terima pilihan client, dan bagikan link private.</p>
                            <div class="aa-home-photobooth-actions">
                                <a class="aa-home-btn aa-home-btn-primary" href="<?= site_url('fitur/galeri-klien-fotografer') ?>">Pelajari Fitur</a>
                                <span>
                                    <a class="aa-home-btn aa-home-btn-secondary" href="<?= site_url('gallery/kalia-juansyah-wedding') ?>" target="_blank" rel="noopener">Lihat Halaman Klien</a>
                                    <small style="display:block;margin-top:8px;color:#c4b5fd;font-size:12px;font-weight:900">*gunakan PIN : 1234</small>
                                </span>
                            </div>
                        </div>
                        <figure class="aa-home-gallery-preview">
                            <img src="<?= base_url('assets/home/galeri/gal-1.png') ?>" alt="Preview Galeri Klien Fotografer AdaAcara" loading="lazy">
                        </figure>
                    </article>
                    <article class="aa-home-card">
                        <h3>Yang bisa dilakukan client.</h3>
                        <p>Client membuka galeri private, memilih foto untuk dicetak, memberi komentar revisi, menyimpan favorit, dan membagikan foto pilihan ke keluarga.</p>
                        <div class="aa-home-gallery-mini-grid grid gap-3 sm:grid-cols-2">
                            <span class="aa-home-mini-card aa-home-gallery-mini-card">
                                <img src="<?= base_url('assets/home/galeri/gal-2.png') ?>" alt="Album galeri klien" loading="lazy">
                                <div class="aa-home-gallery-mini-copy"><strong>Album</strong><span>Highlight, Ceremony, Family, custom.</span></div>
                            </span>
                            <span class="aa-home-mini-card aa-home-gallery-mini-card">
                                <img src="<?= base_url('assets/home/galeri/gal-4.png') ?>" alt="PIN private gallery" loading="lazy">
                                <div class="aa-home-gallery-mini-copy"><strong>PIN</strong><span>Private gallery dan halaman keluarga.</span></div>
                            </span>
                            <span class="aa-home-mini-card aa-home-gallery-mini-card">
                                <img src="<?= base_url('assets/home/galeri/gal-1.png') ?>" alt="Pilihan foto client" loading="lazy">
                                <div class="aa-home-gallery-mini-copy"><strong>Pilihan Foto</strong><span>Sinkron ke dashboard fotografer.</span></div>
                            </span>
                            <span class="aa-home-mini-card aa-home-gallery-mini-card">
                                <img src="<?= base_url('assets/home/galeri/gal-3.png') ?>" alt="Komentar revisi foto" loading="lazy">
                                <div class="aa-home-gallery-mini-copy"><strong>Revisi</strong><span>Catatan client tersimpan per foto.</span></div>
                            </span>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="aa-home-section" id="template" hidden>
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Template unggulan untuk mulai lebih cepat.</h2>
                    <p>Pilih template, edit isi dan style sesukamu, lalu publish sebagai Undangan Digital, Photobooth, atau kebutuhan acara lainnya.</p>
                </div>
                <div class="aa-home-template-toolbar">
                    <label class="aa-home-template-filter">
                        <span class="sr-only">Pilih kategori template</span>
                        <select class="aa-home-template-filter-select" data-home-category-select>
                            <option value="all">Semua kategori</option>
                            <?php foreach ($categoryLabels as $key => $label): ?>
                            <option value="<?= esc($key, 'attr') ?>"><?= esc($label) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>
                    <a class="aa-home-btn aa-home-btn-secondary" href="<?= site_url('templates') ?>">Lihat Semua
                        Template</a>
                </div>

                <div class="aa-home-template-grid" data-home-template-grid>
                    <article class="aa-home-card aa-home-template" data-home-template-card data-home-category="all"
                        data-home-template-global="true">
                        <a class="aa-home-template-preview" href="<?= esc($blankTemplateUrl, 'attr') ?>"
                            <?= $isLoggedIn ? 'data-home-open-project-choice' : '' ?> aria-label="Pakai blank template">
                            <div class="aa-home-template-blank-preview">
                                <div class="aa-home-template-blank-inner">
                                    <span class="aa-home-template-blank-plus">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="5" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.5v15M4.5 12h15" />
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Blank Template</strong>
                                        <span><?= $isLoggedIn ? 'Mulai dari canvas kosong.' : 'Login untuk mulai blank.' ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>

                    <?php if ($templates !== []): ?>
                    <?php foreach ($templates as $template): ?>
                    <?php
                                [$categoryKey] = $normalizeHomeCategory($template);
                                $templateId = (int) ($template['id'] ?? 0);
                                $useTemplateUrl = $templatePreviewUrl($template);
                                $isPremium = (int) ($template['is_premium'] ?? 0) === 1;
                                $thumbnail = ! empty($template['thumbnail']) ? base_url($template['thumbnail']) : '';
                            ?>
                    <article class="aa-home-card aa-home-template" data-home-template-card
                        data-home-category="<?= esc($categoryKey, 'attr') ?>">
                        <a class="aa-home-template-preview" href="<?= esc($useTemplateUrl, 'attr') ?>"
                            data-home-preview-url="<?= esc($useTemplateUrl, 'attr') ?>"
                            data-home-preview-id="<?= esc((string) $templateId, 'attr') ?>"
                            data-home-preview-title="<?= esc($template['name'] ?? 'Template', 'attr') ?>"
                            data-home-preview-src="<?= esc($thumbnail, 'attr') ?>"
                            aria-label="Preview <?= esc($template['name'] ?? 'Template', 'attr') ?>">
                            <?php if ($isPremium): ?>
                            <?= $premiumCrownSvg ?>
                            <?php endif ?>
                            <?php if ($thumbnail !== ''): ?>
                            <span class="aa-img-wrap aa-ratio-preview">
                                <img class="aa-lazy-img" src="<?= esc($thumbnail, 'attr') ?>"
                                    alt="<?= esc($template['name'] ?? 'Template', 'attr') ?>" loading="lazy"
                                    decoding="async">
                            </span>
                            <?php else: ?>
                            <iframe title="<?= esc($template['name'] ?? 'Template') ?> preview"
                                src="<?= esc($useTemplateUrl, 'attr') ?>" loading="lazy"></iframe>
                            <?php endif ?>
                        </a>
                    </article>
                    <?php endforeach ?>
                    <?php endif ?>
                </div>
                <div class="aa-home-empty hidden" data-home-template-empty>Belum ada template pada kategori ini.</div>
            </div>
        </section>

        <!-- <section class="aa-home-section" id="fitur">
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Fitur utama untuk membuat undangan digital yang hidup.</h2>
                    <p>Semua fitur inti dirancang agar pemilik acara bisa membuat, mengedit, menyimpan, dan publish
                        halaman tanpa coding.</p>
                </div>
                <div class="aa-home-grid cols-3">
                    <?php foreach ([
                        ['✦', 'Editor visual', 'Drag, resize, rotate, crop image, edit teks, animasi, dan halaman multi-page.', 'assets/img/5.png'],
                        ['☁', 'Media library', 'Upload gambar, pilih media, replace image, dan atur background desain.'],
                        ['↗', 'Preview & publish', 'Cek draft, publish ke URL public, copy link, dan share WhatsApp.'],
                        ['☑', 'RSVP tamu', 'Form kehadiran, data tamu, dan daftar ucapan berdasarkan slug undangan.'],
                        ['♡', 'Sticker & ucapan', 'Ucapan modern dengan dukungan stiker untuk pengalaman lebih personal.'],
                        ['₿', 'Wedding gift', 'Tampilkan rekening/gift digital dengan tombol copy yang praktis.'],
                    ] as $feature): ?>
                    <article class="aa-home-card<?= ! empty($feature[3]) ? ' aa-home-mascot-card' : '' ?>">
                        <div class="aa-home-icon"><?= esc($feature[0]) ?></div>
                        <h3><?= esc($feature[1]) ?></h3>
                        <p><?= esc($feature[2]) ?></p>
                        <?php if (! empty($feature[3])): ?>
                        <img class="aa-home-card-mascot" src="<?= aa_asset_url($feature[3]) ?>" alt="" loading="lazy"
                            decoding="async">
                        <?php endif ?>
                    </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section>

        <section class="aa-home-section alt" id="cara-kerja">
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Cara kerja yang sederhana.</h2>
                    <p>Dari pilih template sampai link undangan siap dibagikan ke tamu.</p>
                </div>
                <div class="aa-home-grid cols-4">
                    <?php foreach ([
                        ['1', 'Pilih template', 'Mulai dari blank template atau desain siap pakai.'],
                        ['2', 'Edit visual', 'Atur teks, gambar, warna, background, dan halaman.'],
                        ['3', 'Preview', 'Cek desktop/mobile sebelum dibagikan.'],
                        ['4', 'Publish', 'Bagikan link public /u/slug ke tamu.'],
                    ] as $step): ?>
                    <article class="aa-home-card aa-home-step">
                        <span class="aa-home-step-number"><?= esc($step[0]) ?></span>
                        <h3><?= esc($step[1]) ?></h3>
                        <p><?= esc($step[2]) ?></p>
                    </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section> -->

        <!-- <section class="aa-home-section alt" id="pricing">
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Paket member yang fleksibel.</h2>
                    <p>Pilih paket sesuai kebutuhan jumlah undangan, masa aktif, dan fitur tambahan.</p>
                </div>
                <div class="aa-home-grid cols-3">
                    <?php foreach ([
                        ['Buat Acara Sendiri', 'Untuk acara sederhana', 'Mulai hemat', ['Editor visual', 'Template aktif', 'Public URL']],
                        ['Buat Coba Jualan', 'Untuk undangan lengkap', 'Paling populer', ['Multi halaman', 'Guestbook & sticker', 'Wedding gift']],
                        ['Buat Niat Jualan', 'Untuk event profesional', 'Skala besar', ['Banyak halaman', 'Cocok corporate', 'Skala lebih besar']],
                    ] as $plan): ?>
                        <article class="aa-home-pricing">
                            <span class="aa-home-eyebrow"><?= esc($plan[2]) ?></span>
                            <h3><?= esc($plan[0]) ?></h3>
                            <div class="aa-home-price"><?= esc($plan[1]) ?></div>
                            <ul class="aa-home-list">
                                <?php foreach ($plan[3] as $item): ?>
                                    <li><?= esc($item) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </article>
                    <?php endforeach ?>
                </div>
                <div class="mt-6">
                    <a class="aa-home-btn aa-home-btn-primary" href="<?= site_url('plans') ?>">Lihat Paket Member</a>
                </div>
            </div>
        </section> -->

        <!-- <section class="aa-home-section" id="keunggulan">
            <div class="aa-home-shell">
                <div class="aa-home-section-head">
                    <h2>Kenapa AdaAcara terasa profesional?</h2>
                    <p>Editor dibuat untuk kebutuhan nyata undangan digital: desain bebas, publish stabil, dan fitur
                        tamu yang fungsional.</p>
                </div>
                <div class="aa-home-grid cols-4">
                    <?php foreach ([
                        ['Responsive', 'Hasil public nyaman dibuka dari mobile dan desktop.', 'assets/img/6.png'],
                        ['Mudah dibagikan', 'Setiap undangan punya URL public sendiri.'],
                        ['Data terpisah', 'Guestbook dan RSVP mengikuti slug undangan.'],
                        ['Editor praktis', 'Desain visual tanpa harus edit HTML manual.'],
                    ] as $benefit): ?>
                    <article class="aa-home-card<?= ! empty($benefit[2]) ? ' aa-home-mascot-card' : '' ?>">
                        <h3><?= esc($benefit[0]) ?></h3>
                        <p><?= esc($benefit[1]) ?></p>
                        <?php if (! empty($benefit[2])): ?>
                        <img class="aa-home-card-mascot" src="<?= aa_asset_url($benefit[2]) ?>" alt="" loading="lazy"
                            decoding="async">
                        <?php endif ?>
                    </article>
                    <?php endforeach ?>
                </div>
            </div>
        </section> -->

        <!-- <section class="aa-home-shell aa-home-flow">
            <div><span class="aa-home-eyebrow mb-2">Alur Cuan</span>

            </div>

            <h2 class="mt-3 max-w-2xl font-display text-3xl font-bold tracking-tight sm:text-4xl">
                Bikin desain sendiri, validasi admin, lalu mulai jualan.
            </h2>

            <div class="relative mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="aa-home-flow-card relative rounded-2xl border border-border bg-surface p-6 shadow-soft">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex size-9 items-center justify-center rounded-full bg-primary font-display text-sm font-bold text-primary-foreground">
                            1
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-user-check size-5 text-primary/70" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <polyline points="16 11 18 13 22 9"></polyline>
                        </svg>
                    </div>

                    <div class="mt-4 font-display text-base font-bold">
                        Pilih Member
                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Tentukan opsi member jualan yang paling sesuai dengan tujuanmu.
                    </p>
                </div>

                <div class="aa-home-flow-card relative rounded-2xl border border-border bg-surface p-6 shadow-soft">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex size-9 items-center justify-center rounded-full bg-primary font-display text-sm font-bold text-primary-foreground">
                            2
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-pen-tool size-5 text-primary/70" aria-hidden="true">
                            <path
                                d="M15.707 21.293a1 1 0 0 1-1.414 0l-1.586-1.586a1 1 0 0 1 0-1.414l5.586-5.586a1 1 0 0 1 1.414 0l1.586 1.586a1 1 0 0 1 0 1.414z">
                            </path>
                            <path
                                d="m18 13-1.375-6.874a1 1 0 0 0-.746-.776L3.235 2.028a1 1 0 0 0-1.207 1.207L5.35 15.879a1 1 0 0 0 .776.746L13 18">
                            </path>
                            <path d="m2.3 2.3 7.286 7.286"></path>
                            <circle cx="11" cy="11" r="2"></circle>
                        </svg>
                    </div>

                    <div class="mt-4 font-display text-base font-bold">
                        Desain di Editor
                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Buat undangan sendiri dengan editor yang mudah digunakan.
                    </p>
                </div>

                <div class="aa-home-flow-card relative rounded-2xl border border-border bg-surface p-6 shadow-soft">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex size-9 items-center justify-center rounded-full bg-primary font-display text-sm font-bold text-primary-foreground">
                            3
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-shield-check size-5 text-primary/70" aria-hidden="true">
                            <path
                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.68-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1z">
                            </path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </div>

                    <div class="mt-4 font-display text-base font-bold">
                        Simpan & Validasi
                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Template yang kamu simpan akan diperiksa admin terlebih dahulu.
                    </p>
                </div>

                <div
                    class="aa-home-flow-card relative rounded-2xl border border-border bg-surface p-6 shadow-soft aa-home-mascot-card">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex size-9 items-center justify-center rounded-full bg-primary font-display text-sm font-bold text-primary-foreground">
                            4
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-store size-5 text-primary/70" aria-hidden="true">
                            <path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5"></path>
                            <path
                                d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244">
                            </path>
                            <path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05"></path>
                        </svg>
                    </div>

                    <div class="mt-4 font-display text-base font-bold">
                        Mulai Jualan
                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Setelah disetujui, karyamu siap tampil dan dipasarkan.
                    </p>
                    <img class="aa-home-card-mascot" src="<?= aa_asset_url('assets/img/4.png') ?>" alt="" loading="lazy"
                        decoding="async">
                </div>
            </div>
        </section> -->

        <section class="aa-home-section">
            <div class="aa-home-shell">
                <div class="aa-home-final">
                    <span class="aa-home-eyebrow">Siap mulai?</span>
                    <h2>Desain sendiri. Jadikan pengalaman digital untuk acaramu.</h2>
                    <p>Edit desain semudah Canva, pilih yang ingin dibuat - Undangan Digital, Photobooth Digital, dan lainnya - lalu publish dan bagikan.</p>
                    <div class="aa-home-hero-actions">
                        <a class="aa-home-btn aa-home-btn-primary" href="<?= site_url('register') ?>">Daftar
                            Sekarang</a>
                        <a class="aa-home-btn aa-home-btn-secondary" href="<?= site_url('login') ?>">Login</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?= view('components/site_footer') ?>

    <?php if ($homeAds !== []): ?>
    <div id="aaHomeAdModal" class="aa-home-modal aa-home-ad-modal" aria-hidden="true" data-home-ad-modal
        data-home-ad-key="<?= esc($homeAdSessionKey, 'attr') ?>">
        <div class="aa-home-modal-backdrop" data-home-ad-close></div>
        <div class="aa-home-modal-card aa-home-ad-card" role="dialog" aria-modal="true" aria-label="Info AdaAcara">
            <button class="aa-home-modal-close" type="button" data-home-ad-close aria-label="Tutup">⛌</button>
            <div class="aa-home-ad-body">
                <div class="aa-home-ad-slider" data-home-ad-slider>
                    <?php foreach ($homeAds as $index => $ad): ?>
                    <?php
                                $imageUrl = base_url(ltrim((string) ($ad['image_path'] ?? ''), '/'));
                                $title = (string) ($ad['title'] ?? 'Info AdaAcara');
                                $linkUrl = trim((string) ($ad['link_url'] ?? ''));
                            ?>
                    <article class="aa-home-ad-slide <?= $index === 0 ? 'is-active' : '' ?>"
                        data-home-ad-slide="<?= esc((string) $index, 'attr') ?>">
                        <?php if ($linkUrl !== ''): ?>
                        <a href="<?= esc($linkUrl, 'attr') ?>" target="_blank" rel="noopener"
                            aria-label="<?= esc($title, 'attr') ?>">
                            <img src="<?= esc($imageUrl, 'attr') ?>" alt="<?= esc($title, 'attr') ?>" loading="eager">
                        </a>
                        <?php else: ?>
                        <img src="<?= esc($imageUrl, 'attr') ?>" alt="<?= esc($title, 'attr') ?>" loading="eager">
                        <?php endif ?>
                    </article>
                    <?php endforeach ?>

                    <?php if (count($homeAds) > 1): ?>
                    <div class="aa-home-ad-dots" aria-label="Slide iklan home">
                        <?php foreach ($homeAds as $index => $ad): ?>
                        <button class="<?= $index === 0 ? 'is-active' : '' ?>" type="button"
                            data-home-ad-dot="<?= esc((string) $index, 'attr') ?>"
                            aria-label="Tampilkan iklan <?= esc((string) ($index + 1), 'attr') ?>"></button>
                        <?php endforeach ?>
                    </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif ?>

    <div id="aaHomeProjectChoiceModal" class="aa-home-modal" aria-hidden="true">
        <div class="aa-home-modal-backdrop" data-home-project-choice-close></div>
        <div class="aa-home-modal-card project-choice" role="dialog" aria-modal="true" aria-labelledby="aaHomeProjectChoiceTitle" data-lenis-prevent data-lenis-prevent-wheel>
            <div class="aa-home-project-choice">
                <div class="aa-home-project-choice-head">
                    <span class="aa-home-project-choice-spark" aria-hidden="true">✦<i></i></span>
                    <h3 id="aaHomeProjectChoiceTitle">Apa yang ingin kamu buat?</h3>
                    <p>Pilih jenis proyek yang ingin kamu mulai.</p>
                    <button class="aa-home-project-choice-close" type="button" data-home-project-choice-close aria-label="Tutup">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <div class="aa-home-project-grid">
                    <a class="aa-home-project-card" href="<?= site_url('templates') ?>?type=invitation">
                        <span class="aa-home-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><rect x="13" y="15" width="38" height="34" rx="8" fill="currentColor" opacity=".16"/><path d="M20 25h24M20 37h14" stroke="currentColor" stroke-width="4" stroke-linecap="round"/><path d="M42 34c3-4 9-2 9 3 0 6-9 11-9 11s-9-5-9-11c0-5 6-7 9-3Z" fill="currentColor"/></svg>
                        </span>
                        <span class="aa-home-project-copy">
                            <h4>Undangan Digital</h4>
                            <p>Buat website undangan interaktif.</p>
                        </span>
                        <span class="aa-home-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a class="aa-home-project-card is-gold" href="<?= site_url('templates') ?>" data-home-open-photobooth-create>
                        <span class="aa-home-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><rect x="14" y="21" width="36" height="26" rx="8" fill="currentColor" opacity=".18"/><path d="M24 21l4-6h12l4 6" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="32" cy="34" r="9" fill="currentColor" opacity=".24"/><circle cx="32" cy="34" r="5" fill="currentColor"/><rect x="43" y="36" width="12" height="12" rx="3" fill="currentColor" opacity=".45"/></svg>
                        </span>
                        <span class="aa-home-project-copy">
                            <h4>Digital Photobooth</h4>
                            <p>Tamu foto dari HP, hasil bisa download atau dicetak.</p>
                        </span>
                        <span class="aa-home-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <div class="aa-home-project-card is-disabled" aria-disabled="true">
                        <span class="aa-home-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><rect x="13" y="16" width="38" height="32" rx="8" fill="currentColor" opacity=".16"/><circle cx="27" cy="31" r="6" fill="currentColor"/><path d="M21 43c2-5 10-5 12 0M39 27h7M39 36h7" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
                        </span>
                        <span class="aa-home-project-copy">
                            <span class="aa-home-project-badge">Soon</span>
                            <h4>Business Profile</h4>
                            <p>Website profile untuk MUA, WO, vendor, atau freelancer.</p>
                        </span>
                        <span class="aa-home-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </div>

                    <a class="aa-home-project-card is-gold is-lower-start" href="<?= esc($creatorApplyUrl, 'attr') ?>">
                        <span class="aa-home-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><path d="m18 45 4-24 10 11 10-11 4 24H18Z" fill="currentColor" opacity=".2"/><path d="m18 45 4-24 10 11 10-11 4 24H18Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><path d="M24 50h16" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
                        </span>
                        <span class="aa-home-project-copy">
                            <h4>Creator</h4>
                            <p>Buat template dan dapat penghasilan.</p>
                        </span>
                        <span class="aa-home-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a class="aa-home-project-card is-soft is-wide" href="<?= site_url('plans') ?>">
                        <span class="aa-home-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><path d="M15 28h34v22H15V28Z" fill="currentColor" opacity=".16"/><path d="M14 24h36l-4-10H18l-4 10Z" fill="currentColor" opacity=".26"/><path d="M15 28h34v22H15V28Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><circle cx="48" cy="47" r="8" fill="currentColor" opacity=".35"/><path d="m48 42 1.3 3.2 3.4.3-2.6 2.2.8 3.3-2.9-1.8-2.9 1.8.8-3.3-2.6-2.2 3.4-.3L48 42Z" fill="currentColor"/></svg>
                        </span>
                        <span class="aa-home-project-copy">
                            <h4>Untuk Bisnis</h4>
                            <p>Untuk jual undangan digital atau bikin photobooth sendiri pakai sistem adaAcara tinggal siapkan tempat cetak.</p>
                        </span>
                        <span class="aa-home-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>
                </div>

                <div class="aa-home-project-foot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 5 6v5c0 4.5 3 8.4 7 10 4-1.6 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-5"/></svg>
                    <span>Semua proyek tersimpan otomatis di akunmu.</span>
                </div>
            </div>
        </div>
    </div>

    <div id="aaHomeCreateModal" class="aa-home-modal" aria-hidden="true">
        <div class="aa-home-modal-backdrop" data-home-modal-close></div>
        <div class="aa-home-modal-card" role="dialog" aria-modal="true" aria-labelledby="aaHomeCreateTitle">
            <div class="aa-home-modal-head">
                <div>
                    <h3 id="aaHomeCreateTitle">Buat Undangan Baru</h3>
                    <p>Mulai dari canvas kosong. Isi detail dasar dulu, setelah itu lanjut edit desain.</p>
                </div>
                <button class="aa-home-modal-close" type="button" data-home-modal-close aria-label="Tutup">⛌</button>
            </div>
            <div class="aa-home-modal-body">
                <form class="aa-home-create-form" action="<?= site_url('templates/create') ?>" method="post">
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <input type="hidden" name="blank_template" value="1">
                    <div class="aa-home-field">
                        <label for="aaHomeBlankTitle">Judul Acara</label>
                        <input id="aaHomeBlankTitle" name="title" type="text"
                            placeholder="Contoh: Wedding Sarah & Dimas" required>
                    </div>
                    <div class="aa-home-field">
                        <label for="aaHomeBlankSlug">Slug URL</label>
                        <input id="aaHomeBlankSlug" name="slug" type="text" placeholder="contoh: wedding-sarah-dimas">
                    </div>
                    <div class="aa-home-field">
                        <label for="aaHomeBlankDate">Tanggal Acara</label>
                        <input id="aaHomeBlankDate" name="event_date" type="date">
                    </div>
                    <div class="aa-home-modal-actions">
                        <button class="aa-home-btn aa-home-btn-secondary" type="button"
                            data-home-modal-close>Batal</button>
                        <button class="aa-home-btn aa-home-btn-primary" type="submit">Buat dari Awal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="aaHomePhotoboothCreateModal" class="aa-home-modal" aria-hidden="true">
        <div class="aa-home-modal-backdrop" data-home-photobooth-close></div>
        <div class="aa-home-modal-card" role="dialog" aria-modal="true" aria-labelledby="aaHomePhotoboothCreateTitle">
            <div class="aa-home-modal-head">
                <div>
                    <h3 id="aaHomePhotoboothCreateTitle">Buat Photobooth Baru</h3>
                    <p>Isi detail dasar dulu. Setelah itu kamu masuk Studio untuk mulai menyiapkan desain frame.</p>
                </div>
                <button class="aa-home-modal-close" type="button" data-home-photobooth-close aria-label="Tutup">⛌</button>
            </div>
            <div class="aa-home-modal-body">
                <form class="aa-home-create-form" action="<?= site_url('templates/create') ?>" method="post">
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <input type="hidden" name="blank_template" value="1">
                    <input type="hidden" name="project_intent" value="photobooth">
                    <div class="aa-home-field">
                        <label for="aaHomePhotoboothTitle">Nama Photobooth / Nama Acara</label>
                        <input id="aaHomePhotoboothTitle" name="title" type="text"
                            placeholder="Contoh: Photobooth Sarah & Dimas" required>
                    </div>
                    <div class="aa-home-field">
                        <label for="aaHomePhotoboothSlug">Slug URL</label>
                        <input id="aaHomePhotoboothSlug" name="slug" type="text" placeholder="contoh: photobooth-sarah-dimas">
                    </div>
                    <div class="aa-home-field">
                        <label for="aaHomePhotoboothDate">Tanggal Acara</label>
                        <input id="aaHomePhotoboothDate" name="event_date" type="date">
                    </div>
                    <div class="aa-home-modal-actions">
                        <button class="aa-home-btn aa-home-btn-secondary" type="button"
                            data-home-photobooth-close>Batal</button>
                        <button class="aa-home-btn aa-home-btn-primary" type="submit">Buat Photobooth</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="aaHomePreviewModal" class="aa-home-modal" aria-hidden="true">
        <div class="aa-home-modal-backdrop" data-home-preview-close></div>
        <div class="aa-home-modal-card preview" role="dialog" aria-modal="true" aria-labelledby="aaHomePreviewTitle">
            <div class="aa-home-modal-head">
                <div>
                    <h3 id="aaHomePreviewTitle">Preview Template</h3>
                    <p>Preview ringan agar modal tetap cepat. Buka preview penuh jika ingin melihat semua detail.</p>
                </div>
                <button class="aa-home-modal-close" type="button" data-home-preview-close aria-label="Tutup">⛌</button>
            </div>
            <div id="aaHomePreviewLight" class="aa-home-preview-light"></div>
        </div>
    </div>

    <div id="aaHomeTemplateCreateDropup" class="aa-home-template-create-dropup" aria-hidden="true">
        <div class="aa-home-template-create-head">
            <div>
                <h3>Buat Undangan Baru</h3>
                <p id="aaHomeTemplateCreateName">Isi detail dasar, lalu lanjut edit desain.</p>
            </div>
            <button class="aa-home-template-create-close" type="button" data-home-template-create-close
                aria-label="Tutup">⛌</button>
        </div>
        <form class="aa-home-template-create-form" action="<?= site_url('templates/create') ?>" method="post">
            <?= function_exists('csrf_field') ? csrf_field() : '' ?>
            <input id="aaHomeTemplateCreateId" type="hidden" name="template_id" value="">
            <div class="aa-home-field">
                <label for="aaHomeTemplateCreateTitleInput">Judul Acara</label>
                <input id="aaHomeTemplateCreateTitleInput" name="title" type="text"
                    placeholder="Contoh: Wedding Sarah & Dimas" required>
            </div>
            <div class="aa-home-field">
                <label for="aaHomeTemplateCreateSlugInput">Slug URL</label>
                <input id="aaHomeTemplateCreateSlugInput" name="slug" type="text"
                    placeholder="contoh: wedding-sarah-dimas">
            </div>
            <div class="aa-home-field">
                <label for="aaHomeTemplateCreateDateInput">Tanggal Acara</label>
                <input id="aaHomeTemplateCreateDateInput" name="event_date" type="date">
            </div>
            <div class="aa-home-modal-actions">
                <button class="aa-home-btn aa-home-btn-secondary" type="button"
                    data-home-template-create-close>Batal</button>
                <button class="aa-home-btn aa-home-btn-primary" type="submit">Sesuaikan Desain Ini</button>
            </div>
        </form>
    </div>

    <script>
    (function() {
        if (window.AdaAcaraHomeReady) return;
        window.AdaAcaraHomeReady = true;

        const filterWrap = document.querySelector('[data-home-template-filter]');
        const categorySelect = document.querySelector('[data-home-category-select]');
        const templateGrid = document.querySelector('[data-home-template-grid]');
        const templateEmpty = document.querySelector('[data-home-template-empty]');
        const projectChoiceModal = document.getElementById('aaHomeProjectChoiceModal');
        const createModal = document.getElementById('aaHomeCreateModal');
        const photoboothCreateModal = document.getElementById('aaHomePhotoboothCreateModal');
        const previewModal = document.getElementById('aaHomePreviewModal');
        const homeAdModal = document.getElementById('aaHomeAdModal');
        const previewLight = document.getElementById('aaHomePreviewLight');
        const previewTitle = document.getElementById('aaHomePreviewTitle');
        const templateCreateDropup = document.getElementById('aaHomeTemplateCreateDropup');
        const templateCreateName = document.getElementById('aaHomeTemplateCreateName');
        const templateCreateId = document.getElementById('aaHomeTemplateCreateId');
        const templateCreateTitleInput = document.getElementById('aaHomeTemplateCreateTitleInput');
        const templateCreateSlugInput = document.getElementById('aaHomeTemplateCreateSlugInput');
        const blankTitleInput = document.getElementById('aaHomeBlankTitle');
        const blankSlugInput = document.getElementById('aaHomeBlankSlug');
        const photoboothTitleInput = document.getElementById('aaHomePhotoboothTitle');
        const photoboothSlugInput = document.getElementById('aaHomePhotoboothSlug');
        const mobileNav = document.querySelector('[data-home-mobile-nav]');
        const mobileToggle = document.querySelector('[data-home-mobile-toggle]');
        const themeToggles = document.querySelectorAll('[data-home-theme-toggle]');
        const aaHomeCharts = [];
        let aaHomeChartsReady = false;
        let aaHomeLenis = null;

        function getHomeTheme() {
            const theme = document.documentElement.dataset.aaHomeTheme || 'light';
            return theme === 'dark' ? 'dark' : 'light';
        }

        function syncHomeThemeButtons() {
            const theme = getHomeTheme();
            const nextLabel = theme === 'dark' ? 'Gunakan tema terang' : 'Gunakan tema gelap';
            themeToggles.forEach(function(button) {
                button.setAttribute('aria-label', nextLabel);
                button.setAttribute('title', nextLabel);
                button.dataset.homeThemeCurrent = theme;
                const label = button.querySelector('[data-home-theme-label]');
                if (label) label.textContent = theme === 'dark' ? 'Tema terang' : 'Tema gelap';
            });
        }

        function setHomeTheme(theme) {
            const nextTheme = theme === 'dark' ? 'dark' : 'light';
            document.documentElement.dataset.aaHomeTheme = nextTheme;
            try {
                localStorage.setItem('aa-home-theme', nextTheme);
            } catch (error) {}
            syncHomeThemeButtons();
            if (aaHomeChartsReady) {
                aaRenderHomeCharts();
            }
        }

        syncHomeThemeButtons();

        function initHomeLenis() {
            if (aaHomeLenis || !window.Lenis) return;
            const reduceMotion = window.matchMedia &&
                window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduceMotion) return;

            aaHomeLenis = new window.Lenis({
                duration: 1.05,
                easing: function(t) {
                    return Math.min(1, 1.001 - Math.pow(2, -10 * t));
                },
                smoothWheel: true,
                wheelMultiplier: .9,
                touchMultiplier: 1.1,
            });
            window.aaHomeLenis = aaHomeLenis;

            function raf(time) {
                if (aaHomeLenis) aaHomeLenis.raf(time);
                window.requestAnimationFrame(raf);
            }

            window.requestAnimationFrame(raf);
        }

        initHomeLenis();
        window.addEventListener('load', initHomeLenis, {
            once: true,
        });

        function initHomeLetterHeadings() {
            const reduceMotion = window.matchMedia &&
                window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const headings = document.querySelectorAll('main h1, main h2');
            if (!headings.length) return;

            function splitTextNode(textNode, counter) {
                const text = textNode.nodeValue || '';
                const fragment = document.createDocumentFragment();
                const parts = text.split(/(\s+)/);

                parts.forEach(function(part) {
                    if (!part) return;
                    if (/^\s+$/.test(part)) {
                        fragment.appendChild(document.createTextNode(part));
                        return;
                    }

                    const word = document.createElement('span');
                    word.className = 'aa-letter-word';
                    word.setAttribute('aria-hidden', 'true');

                    Array.from(part).forEach(function(letter) {
                        const char = document.createElement('span');
                        char.className = 'aa-letter-char';
                        char.style.setProperty('--aa-letter-index', counter.value);
                        char.textContent = letter;
                        counter.value += 1;
                        word.appendChild(char);
                    });

                    fragment.appendChild(word);
                });

                return fragment;
            }

            function splitElementText(element, counter) {
                Array.from(element.childNodes).forEach(function(node) {
                    if (node.nodeType === Node.TEXT_NODE) {
                        if (!node.nodeValue || !node.nodeValue.trim()) return;
                        node.replaceWith(splitTextNode(node, counter));
                        return;
                    }

                    if (node.nodeType !== Node.ELEMENT_NODE) return;
                    const tagName = node.tagName ? node.tagName.toLowerCase() : '';
                    if (['script', 'style', 'svg', 'path'].includes(tagName)) return;
                    splitElementText(node, counter);
                });
            }

            headings.forEach(function(heading) {
                if (heading.dataset.aaLetterReady === '1') return;
                heading.dataset.aaLetterReady = '1';
                heading.dataset.aaHeadingLevel = heading.tagName.replace('H', '');
                heading.classList.add('aa-home-heading', 'font-black', 'tracking-tight',
                    'transition-colors', 'duration-300');

                const label = (heading.textContent || '').replace(/\s+/g, ' ').trim();
                if (label) heading.setAttribute('aria-label', label);

                if (!reduceMotion) {
                    splitElementText(heading, {
                        value: 0,
                    });
                    heading.classList.add('aa-letter-animated');
                }
            });

            if (reduceMotion || !('IntersectionObserver' in window)) {
                headings.forEach(function(heading) {
                    heading.classList.add('is-visible');
                });
                return;
            }

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, {
                rootMargin: '0px 0px -12% 0px',
                threshold: .16,
            });

            headings.forEach(function(heading) {
                observer.observe(heading);
            });
        }

        initHomeLetterHeadings();

        function initHomeViewportAnimations() {
            const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const targets = Array.from(document.querySelectorAll([
                '.aa-home-lead',
                '.aa-home-hero-actions',
                '.aa-home-proof-item',
                '.aa-home-preview',
                '.aa-home-section-head p',
                '.aa-home-path-card',
                '.aa-home-editor-image',
                '.aa-home-card',
                '.aa-home-flow-card',
                '.aa-home-template',
                '.aa-home-template-blank-preview',
                '.aa-home-ai-panel',
                '.aa-home-feature-deep > *',
                '.aa-home-list',
                '.aa-home-faq-item',
            ].join(','))).filter(function(element) {
                return element && !element.closest('[hidden]') && element.dataset.aaViewportReady !== '1';
            });

            if (!targets.length) return;

            targets.forEach(function(element, index) {
                element.dataset.aaViewportReady = '1';
                element.classList.add('aa-viewport-animate');
                element.style.setProperty('--aa-viewport-delay', Math.min(index % 4, 3) * 70 + 'ms');
            });

            if (reduceMotion || !('IntersectionObserver' in window)) {
                targets.forEach(function(element) {
                    element.classList.add('is-visible');
                });
                return;
            }

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, {
                rootMargin: '0px 0px -10% 0px',
                threshold: .12,
            });

            targets.forEach(function(element) {
                observer.observe(element);
            });
        }

        initHomeViewportAnimations();

        function setMobileMenuOpen(open) {
            if (!mobileNav || !mobileToggle) return;
            mobileNav.classList.toggle('is-open', open);
            mobileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        function setModalOpen(modal, open) {
            if (!modal) return;
            modal.classList.toggle('is-open', open);
            modal.setAttribute('aria-hidden', open ? 'false' : 'true');
            const hasOpenModal = !!document.querySelector('.aa-home-modal.is-open');
            document.body.style.overflow = hasOpenModal ? 'hidden' : '';
            if (aaHomeLenis && typeof aaHomeLenis.stop === 'function' && typeof aaHomeLenis.start === 'function') {
                if (hasOpenModal) {
                    aaHomeLenis.stop();
                } else {
                    aaHomeLenis.start();
                }
            }
        }

        function closeCreateModal() {
            setModalOpen(createModal, false);
        }

        function closePhotoboothCreateModal() {
            setModalOpen(photoboothCreateModal, false);
        }

        function openProjectChoiceModal() {
            setModalOpen(projectChoiceModal, true);
        }

        function closeProjectChoiceModal() {
            setModalOpen(projectChoiceModal, false);
        }

        function closeHomeAdModal() {
            if (!homeAdModal) return;
            setModalOpen(homeAdModal, false);
            try {
                sessionStorage.setItem(homeAdModal.dataset.homeAdKey || 'aa-home-ad-closed', '1');
            } catch (error) {}
        }

        function initHomeAdModal() {
            if (!homeAdModal || homeAdModal.dataset.homeAdBound === '1') return;
            homeAdModal.dataset.homeAdBound = '1';

            const key = homeAdModal.dataset.homeAdKey || 'aa-home-ad-closed';
            try {
                if (sessionStorage.getItem(key) === '1') return;
            } catch (error) {}

            const slides = Array.prototype.slice.call(homeAdModal.querySelectorAll('[data-home-ad-slide]'));
            const dots = Array.prototype.slice.call(homeAdModal.querySelectorAll('[data-home-ad-dot]'));
            let activeIndex = 0;
            let timer = null;

            function showSlide(index) {
                if (slides.length <= 1) return;
                activeIndex = (index + slides.length) % slides.length;
                slides.forEach(function(slide, slideIndex) {
                    slide.classList.toggle('is-active', slideIndex === activeIndex);
                });
                dots.forEach(function(dot, dotIndex) {
                    dot.classList.toggle('is-active', dotIndex === activeIndex);
                });
            }

            function startSlider() {
                window.clearInterval(timer);
                if (slides.length <= 1) return;
                timer = window.setInterval(function() {
                    showSlide(activeIndex + 1);
                }, 3600);
            }

            dots.forEach(function(dot, index) {
                dot.addEventListener('click', function() {
                    showSlide(index);
                    startSlider();
                });
            });

            window.setTimeout(function() {
                setModalOpen(homeAdModal, true);
                startSlider();
            }, 700);
        }

        function openCreateModal() {
            closeProjectChoiceModal();
            setModalOpen(createModal, true);
            window.setTimeout(function() {
                blankTitleInput?.focus();
            }, 80);
        }

        function openPhotoboothCreateModal() {
            closeProjectChoiceModal();
            setModalOpen(photoboothCreateModal, true);
            window.setTimeout(function() {
                photoboothTitleInput?.focus();
            }, 80);
        }

        function closePreviewModal() {
            setModalOpen(previewModal, false);
            if (previewLight) previewLight.innerHTML = '';
            closeTemplateCreateDropup();
        }

        function closeTemplateCreateDropup() {
            if (!templateCreateDropup) return;
            templateCreateDropup.classList.remove('is-open');
            templateCreateDropup.setAttribute('aria-hidden', 'true');
        }

        function openTemplateCreateDropup(templateId, templateTitle) {
            if (!templateCreateDropup || !templateCreateId) return;
            templateCreateId.value = templateId || '';
            if (templateCreateName) {
                templateCreateName.textContent = templateTitle ?
                    `Pakai template "${templateTitle}" sebagai desain awal.` :
                    'Isi detail dasar, lalu lanjut edit desain.';
            }
            if (templateCreateSlugInput && !templateCreateSlugInput.value) {
                templateCreateSlugInput.dataset.userEdited = '0';
            }
            templateCreateDropup.classList.add('is-open');
            templateCreateDropup.setAttribute('aria-hidden', 'false');
            window.setTimeout(function() {
                templateCreateTitleInput?.focus();
            }, 80);
        }

        function openPreviewModal(trigger) {
            const url = trigger.dataset.homePreviewUrl || trigger.href || '';
            if (!url || !previewModal || !previewLight) return;

            const title = trigger.dataset.homePreviewTitle || 'Preview Template';
            const src = trigger.dataset.homePreviewSrc || '';
            const templateId = trigger.dataset.homePreviewId || '';
            if (previewTitle) {
                previewTitle.textContent = title;
            }

            previewLight.innerHTML = '';
            const cover = document.createElement('div');
            cover.className = 'aa-home-preview-cover';
            if (src) {
                const image = document.createElement('img');
                image.src = src;
                image.alt = title;
                image.loading = 'lazy';
                cover.appendChild(image);
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'aa-home-preview-placeholder';
                const placeholderTitle = document.createElement('strong');
                placeholderTitle.textContent = title;
                const placeholderText = document.createElement('span');
                placeholderText.textContent = 'Cover template';
                placeholder.appendChild(placeholderTitle);
                placeholder.appendChild(placeholderText);
                cover.appendChild(placeholder);
            }

            const copy = document.createElement('div');
            copy.className = 'aa-home-preview-copy';
            const copyTitle = document.createElement('h4');
            copyTitle.textContent = title;
            const copyText = document.createElement('p');
            copyText.textContent =
                'Gunakan template ini untuk masuk ke flow pemakaian template, atau buka preview penuh di tab baru untuk melihat halaman lengkap.';
            const actions = document.createElement('div');
            actions.className = 'aa-home-preview-actions';

            const useLink = document.createElement('button');
            useLink.className = 'aa-home-btn aa-home-btn-primary';
            useLink.type = 'button';
            useLink.dataset.homeTemplateUse = templateId;
            useLink.dataset.homeTemplateTitle = title;
            useLink.textContent = 'Sesuaikan Desain Ini';

            const fullPreviewLink = document.createElement('a');
            fullPreviewLink.className = 'aa-home-btn aa-home-btn-secondary';
            fullPreviewLink.href = url;
            fullPreviewLink.target = '_blank';
            fullPreviewLink.rel = 'noopener';
            fullPreviewLink.textContent = 'Lihat Preview Penuh';

            actions.appendChild(useLink);
            actions.appendChild(fullPreviewLink);
            copy.appendChild(copyTitle);
            copy.appendChild(copyText);
            copy.appendChild(actions);

            previewLight.appendChild(cover);
            previewLight.appendChild(copy);
            setModalOpen(previewModal, true);
        }

        function slugifyHome(value) {
            return String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 190);
        }

        function filterHomeTemplates(category) {
            if (!templateGrid) return;
            let visible = 0;

            templateGrid.querySelectorAll('[data-home-template-card]').forEach(function(card) {
                const isGlobal = card.dataset.homeTemplateGlobal === 'true';
                const show = category === 'all' || (!isGlobal && card.dataset.homeCategory === category);
                card.classList.toggle('hidden', !show);
                card.classList.remove('is-entering');
                card.style.animationDelay = '';
                if (show) {
                    card.style.animationDelay = Math.min(visible, 9) * 24 + 'ms';
                    requestAnimationFrame(function() {
                        card.classList.add('is-entering');
                    });
                    if (!isGlobal) {
                        visible += 1;
                    }
                }
            });

            if (templateEmpty) {
                templateEmpty.classList.toggle('hidden', visible !== 0);
            }
        }

        document.addEventListener('click', function(event) {
            const mobileToggleTarget = event.target.closest('[data-home-mobile-toggle]');
            if (mobileToggleTarget) {
                event.preventDefault();
                setMobileMenuOpen(!mobileNav?.classList.contains('is-open'));
                return;
            }

            if (mobileNav?.classList.contains('is-open') && !event.target.closest(
                    '[data-home-mobile-nav]')) {
                setMobileMenuOpen(false);
            }

            const themeToggle = event.target.closest('[data-home-theme-toggle]');
            if (themeToggle) {
                event.preventDefault();
                setHomeTheme(getHomeTheme() === 'dark' ? 'light' : 'dark');
                return;
            }

            if (event.target.closest('.aa-home-mobile-panel a')) {
                setMobileMenuOpen(false);
            }

            const projectChoiceTrigger = event.target.closest('[data-home-open-project-choice]');
            if (projectChoiceTrigger) {
                event.preventDefault();
                openProjectChoiceModal();
                return;
            }

            if (event.target.closest('[data-home-project-choice-close]')) {
                closeProjectChoiceModal();
                return;
            }

            const photoboothCreateTrigger = event.target.closest('[data-home-open-photobooth-create]');
            if (photoboothCreateTrigger) {
                event.preventDefault();
                openPhotoboothCreateModal();
                return;
            }

            const anchorTrigger = event.target.closest('a[href^="#"]');
            if (anchorTrigger && aaHomeLenis) {
                const hash = anchorTrigger.getAttribute('href') || '';
                let target = null;
                if (hash.length > 1) {
                    try {
                        target = document.querySelector(hash);
                    } catch (error) {
                        target = null;
                    }
                }
                if (target) {
                    event.preventDefault();
                    setMobileMenuOpen(false);
                    aaHomeLenis.scrollTo(target, {
                        offset: -82,
                    });
                    if (window.history && window.history.pushState) {
                        window.history.pushState(null, '', hash);
                    }
                    return;
                }
            }

            const createTrigger = event.target.closest('[data-home-open-create]');
            if (createTrigger) {
                event.preventDefault();
                openCreateModal();
                return;
            }

            const previewTrigger = event.target.closest('[data-home-preview-url]');
            if (previewTrigger) {
                event.preventDefault();
                openPreviewModal(previewTrigger);
                return;
            }

            if (event.target.closest('[data-home-modal-close]')) {
                closeCreateModal();
                return;
            }

            if (event.target.closest('[data-home-photobooth-close]')) {
                closePhotoboothCreateModal();
                return;
            }

            if (event.target.closest('[data-home-preview-close]')) {
                closePreviewModal();
                return;
            }

            if (event.target.closest('[data-home-ad-close]')) {
                closeHomeAdModal();
                return;
            }

            if (event.target.closest('[data-home-template-create-close]')) {
                closeTemplateCreateDropup();
                return;
            }

            const useTemplateTrigger = event.target.closest('[data-home-template-use]');
            if (useTemplateTrigger) {
                event.preventDefault();
                openTemplateCreateDropup(useTemplateTrigger.dataset.homeTemplateUse, useTemplateTrigger
                    .dataset
                    .homeTemplateTitle || '');
                return;
            }

            const button = event.target.closest('.aa-home-faq-btn');
            if (button) {
                const item = button.closest('.aa-home-faq-item');
                if (!item) return;

                const isOpen = item.classList.toggle('is-open');
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                button.querySelector('span:last-child').textContent = isOpen ? '-' : '+';
                return;
            }

            const filterButton = event.target.closest('[data-home-category-filter]');
            if (filterButton && filterWrap) {
                filterWrap.querySelectorAll('[data-home-category-filter]').forEach(function(item) {
                    item.classList.toggle('is-active', item === filterButton);
                });
                filterHomeTemplates(filterButton.dataset.homeCategoryFilter || 'all');
                return;
            }
        });

        categorySelect?.addEventListener('change', function() {
            filterHomeTemplates(categorySelect.value || 'all');
        });

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') return;
            setMobileMenuOpen(false);
            closeProjectChoiceModal();
            closeCreateModal();
            closePhotoboothCreateModal();
            closePreviewModal();
            closeTemplateCreateDropup();
            closeHomeAdModal();
        });

        blankTitleInput?.addEventListener('input', function() {
            if (!blankSlugInput || blankSlugInput.dataset.userEdited === '1') return;
            blankSlugInput.value = slugifyHome(blankTitleInput.value);
        });

        blankSlugInput?.addEventListener('input', function() {
            blankSlugInput.dataset.userEdited = blankSlugInput.value ? '1' : '0';
        });

        photoboothTitleInput?.addEventListener('input', function() {
            if (!photoboothSlugInput || photoboothSlugInput.dataset.userEdited === '1') return;
            photoboothSlugInput.value = slugifyHome(photoboothTitleInput.value);
        });

        photoboothSlugInput?.addEventListener('input', function() {
            photoboothSlugInput.dataset.userEdited = photoboothSlugInput.value ? '1' : '0';
        });

        templateCreateTitleInput?.addEventListener('input', function() {
            if (!templateCreateSlugInput || templateCreateSlugInput.dataset.userEdited === '1') return;
            templateCreateSlugInput.value = slugifyHome(templateCreateTitleInput.value);
        });

        templateCreateSlugInput?.addEventListener('input', function() {
            templateCreateSlugInput.dataset.userEdited = templateCreateSlugInput.value ? '1' : '0';
        });

        initHomeAdModal();
    })();
    </script>
    <script type="module">
    (async function() {
        if (window.AdaAcaraHomeRibbonReady) return;
        window.AdaAcaraHomeRibbonReady = true;

        const container = document.querySelector('[data-home-ribbon-showcase]');
        if (!container) return;

        const reduceMotion = window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) return;

        const testCanvas = document.createElement('canvas');
        const hasWebGL = !!(testCanvas.getContext('webgl') || testCanvas.getContext('experimental-webgl'));
        if (!hasWebGL) return;

        let Renderer;
        let Transform;
        let Vec3;
        let Color;
        let Polyline;

        try {
            ({
                Renderer,
                Transform,
                Vec3,
                Color,
                Polyline
            } = await import('https://cdn.jsdelivr.net/npm/ogl@0.0.117/src/index.mjs'));
        } catch (error) {
            console.warn('[AdaAcara Home] Ribbon effect tidak dimuat:', error);
            return;
        }

        const config = {
            colors: ['#111827', '#8f65df', '#14b8a6'],
            baseSpring: 0.026,
            baseFriction: 0.91,
            baseThickness: 34,
            offsetFactor: 0.055,
            maxAge: 620,
            pointCount: 54,
            speedMultiplier: 0.52,
            enableFade: true,
            enableShaderEffect: true,
            effectAmplitude: 1.35,
            backgroundColor: [0, 0, 0, 0],
        };

        const renderer = new Renderer({
            dpr: Math.min(window.devicePixelRatio || 1, 2),
            alpha: true,
        });
        const gl = renderer.gl;
        gl.clearColor(...config.backgroundColor);
        gl.canvas.style.position = 'absolute';
        gl.canvas.style.inset = '0';
        gl.canvas.style.width = '100%';
        gl.canvas.style.height = '100%';
        container.appendChild(gl.canvas);

        const scene = new Transform();
        const lines = [];
        const mouse = new Vec3(0, 0, 0);

        const vertex = `
            precision highp float;
            attribute vec3 position;
            attribute vec3 next;
            attribute vec3 prev;
            attribute vec2 uv;
            attribute float side;
            uniform vec2 uResolution;
            uniform float uDPR;
            uniform float uThickness;
            uniform float uTime;
            uniform float uEnableShaderEffect;
            uniform float uEffectAmplitude;
            varying vec2 vUV;

            vec4 getPosition() {
                vec4 current = vec4(position, 1.0);
                vec2 aspect = vec2(uResolution.x / uResolution.y, 1.0);
                vec2 nextScreen = next.xy * aspect;
                vec2 prevScreen = prev.xy * aspect;
                vec2 tangent = normalize(nextScreen - prevScreen);
                vec2 normal = vec2(-tangent.y, tangent.x);
                normal /= aspect;
                normal *= mix(1.0, 0.1, pow(abs(uv.y - 0.5) * 2.0, 2.0));
                float dist = length(nextScreen - prevScreen);
                normal *= smoothstep(0.0, 0.02, dist);
                float pixelWidthRatio = 1.0 / (uResolution.y / uDPR);
                float pixelWidth = current.w * pixelWidthRatio;
                normal *= pixelWidth * uThickness;
                current.xy -= normal * side;
                if (uEnableShaderEffect > 0.5) {
                    current.xy += normal * sin(uTime + current.x * 10.0) * uEffectAmplitude;
                }
                return current;
            }

            void main() {
                vUV = uv;
                gl_Position = getPosition();
            }
        `;

        const fragment = `
            precision highp float;
            uniform vec3 uColor;
            uniform float uOpacity;
            uniform float uEnableFade;
            varying vec2 vUV;

            void main() {
                float fadeFactor = 1.0;
                if (uEnableFade > 0.5) {
                    fadeFactor = 1.0 - smoothstep(0.0, 1.0, vUV.y);
                }
                gl_FragColor = vec4(uColor, uOpacity * fadeFactor);
            }
        `;

        function resizeRibbon() {
            if (!container.isConnected) return;
            renderer.setSize(container.clientWidth, container.clientHeight);
            lines.forEach(function(line) {
                line.polyline.resize();
            });
        }

        const center = (config.colors.length - 1) / 2;
        config.colors.forEach(function(color, index) {
            const points = [];
            for (let pointIndex = 0; pointIndex < config.pointCount; pointIndex += 1) {
                points.push(new Vec3());
            }

            const line = {
                spring: config.baseSpring + (Math.random() - .5) * .024,
                friction: config.baseFriction + (Math.random() - .5) * .03,
                mouseVelocity: new Vec3(),
                mouseOffset: new Vec3(
                    (index - center) * config.offsetFactor + (Math.random() - .5) * .01,
                    (Math.random() - .5) * .08,
                    0
                ),
                points,
                polyline: null,
            };

            line.polyline = new Polyline(gl, {
                points,
                vertex,
                fragment,
                uniforms: {
                    uColor: {
                        value: new Color(color)
                    },
                    uThickness: {
                        value: config.baseThickness + (Math.random() - .5) * 3
                    },
                    uOpacity: {
                        value: index === 0 ? .72 : .62
                    },
                    uTime: {
                        value: 0
                    },
                    uEnableShaderEffect: {
                        value: config.enableShaderEffect ? 1 : 0
                    },
                    uEffectAmplitude: {
                        value: config.effectAmplitude
                    },
                    uEnableFade: {
                        value: config.enableFade ? 1 : 0
                    },
                },
            });
            line.polyline.mesh.setParent(scene);
            lines.push(line);
        });

        function updateMouse(event) {
            if (!container.isConnected) return;
            const rect = container.getBoundingClientRect();
            const source = event.changedTouches && event.changedTouches.length ? event.changedTouches[0] :
                event;
            const x = source.clientX - rect.left;
            const y = source.clientY - rect.top;
            mouse.set((x / rect.width) * 2 - 1, (y / rect.height) * -2 + 1, 0);
        }

        window.addEventListener('resize', resizeRibbon, {
            passive: true
        });
        container.addEventListener('mousemove', updateMouse, {
            passive: true
        });
        container.addEventListener('touchstart', updateMouse, {
            passive: true
        });
        container.addEventListener('touchmove', updateMouse, {
            passive: true
        });

        resizeRibbon();

        let frameId = 0;
        let lastTime = performance.now();

        function animateRibbon() {
            if (!container.isConnected) {
                cancelAnimationFrame(frameId);
                return;
            }

            frameId = requestAnimationFrame(animateRibbon);
            const currentTime = performance.now();
            const dt = currentTime - lastTime;
            lastTime = currentTime;

            lines.forEach(function(line) {
                const pull = new Vec3();
                pull.copy(mouse).add(line.mouseOffset).sub(line.points[0]).multiply(line.spring);
                line.mouseVelocity.add(pull).multiply(line.friction);
                line.points[0].add(line.mouseVelocity);

                for (let pointIndex = 1; pointIndex < line.points.length; pointIndex += 1) {
                    const segmentDelay = config.maxAge / (line.points.length - 1);
                    const alpha = Math.min(1, (dt * config.speedMultiplier) / segmentDelay);
                    line.points[pointIndex].lerp(line.points[pointIndex - 1], alpha);
                }

                line.polyline.mesh.program.uniforms.uTime.value = currentTime * .001;
                line.polyline.updateGeometry();
            });

            renderer.render({
                scene
            });
        }

        animateRibbon();

        window.addEventListener('pagehide', function() {
            cancelAnimationFrame(frameId);
            window.removeEventListener('resize', resizeRibbon);
            if (gl.canvas && gl.canvas.parentNode === container) {
                container.removeChild(gl.canvas);
            }
        }, {
            once: true
        });
    })();

    // function aaHomeChartPalette() {
    //     const dark = getHomeTheme() === 'dark';

    //     return {
    //         text: dark ? '#cbd5e1' : '#475569',
    //         muted: dark ? '#94a3b8' : '#64748b',
    //         grid: dark ? 'rgba(148, 163, 184, .16)' : 'rgba(148, 163, 184, .18)',
    //         border: dark ? 'rgba(148, 163, 184, .22)' : 'rgba(15, 23, 42, .10)',
    //         primary: dark ? '#d9ccf4' : '#8f65df',
    //         primaryFill: dark ? 'rgba(143, 101, 223, .22)' : 'rgba(143, 101, 223, .14)',
    //         tooltipBg: dark ? 'rgba(15, 23, 42, .96)' : 'rgba(255, 255, 255, .96)',
    //         tooltipText: dark ? '#f8fafc' : '#111827',
    //         segments: dark ? ['#d9ccf4', '#14b8a6', '#38bdf8', '#f9a8d4', '#f6bf4f', '#94a3b8'] : ['#8f65df', '#14b8a6',
    //             '#38bdf8', '#fb7185', '#f6bf4f', '#94a3b8'
    //         ],
    //     };
    // }

    function aaHomeChartFont(size, weight) {
        return {
            family: 'Geist, Manrope, Inter, sans-serif',
            size: size || 11,
            weight: weight || '700',
        };
    }

    function aaHomeChartPlugins(palette, legendOptions) {
        return {
            legend: Object.assign({
                labels: {
                    color: palette.text,
                    boxWidth: 9,
                    boxHeight: 9,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 14,
                    font: aaHomeChartFont(11, '700'),
                },
            }, legendOptions || {}),
            tooltip: {
                backgroundColor: palette.tooltipBg,
                titleColor: palette.tooltipText,
                bodyColor: palette.text,
                borderColor: palette.border,
                borderWidth: 1,
                displayColors: true,
                padding: 10,
                titleFont: aaHomeChartFont(12, '800'),
                bodyFont: aaHomeChartFont(11, '700'),
            },
        };
    }

    function aaHomeCartesianScales(palette) {
        return {
            x: {
                ticks: {
                    color: palette.muted,
                    font: aaHomeChartFont(10, '700'),
                },
                grid: {
                    color: palette.grid,
                    drawBorder: false,
                },
                border: {
                    color: palette.border,
                },
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: palette.muted,
                    font: aaHomeChartFont(10, '700'),
                },
                grid: {
                    color: palette.grid,
                    drawBorder: false,
                },
                border: {
                    color: palette.border,
                },
            },
        };
    }

    function aaDestroyHomeCharts() {
        while (aaHomeCharts.length) {
            const chart = aaHomeCharts.pop();
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        }
    }

    function aaRenderHomeCharts() {
        if (!window.Chart) return;

        const palette = aaHomeChartPalette();
        const monthlyCanvas = document.getElementById('monthlyChart');
        const categoryCanvas = document.getElementById('categoryChart');
        const heatCanvas = document.getElementById('heatChart');
        const creatorCanvas = document.getElementById('creatorChart');

        aaDestroyHomeCharts();

        if (monthlyCanvas) {
            aaHomeCharts.push(new Chart(monthlyCanvas, {
                type: 'line',
                data: {
                    labels: [
                        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                    ],
                    datasets: [{
                        label: 'Indeks Event',
                        data: [
                            82,
                            74,
                            46,
                            55,
                            73,
                            91,
                            88,
                            93,
                            86,
                            96,
                            100,
                            92
                        ],
                        fill: true,
                        tension: .4,
                        borderColor: palette.primary,
                        backgroundColor: palette.primaryFill,
                        pointBackgroundColor: palette.primary,
                        pointBorderColor: palette.tooltipBg,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    color: palette.text,
                    plugins: aaHomeChartPlugins(palette, {
                        display: false,
                    }),
                    scales: aaHomeCartesianScales(palette),
                }
            }));
        }

        if (categoryCanvas) {
            aaHomeCharts.push(new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: [
                        'Wedding',
                        'Keluarga',
                        'Corporate',
                        'Sekolah',
                        'Seminar',
                        'Lainnya'
                    ],
                    datasets: [{
                        data: [
                            41,
                            22,
                            15,
                            10,
                            7,
                            5
                        ],
                        backgroundColor: palette.segments,
                        borderColor: palette.tooltipBg,
                        borderWidth: 2,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    color: palette.text,
                    plugins: aaHomeChartPlugins(palette, {
                        position: 'bottom',
                    }),
                }
            }));
        }

        if (heatCanvas) {
            aaHomeCharts.push(new Chart(heatCanvas, {
                type: 'radar',
                data: {
                    labels: [
                        'Wedding',
                        'Sekolah',
                        'Corporate',
                        'Seminar',
                        'Keluarga'
                    ],
                    datasets: [{
                        label: 'Musim Ramai',
                        data: [
                            98,
                            65,
                            74,
                            58,
                            82
                        ],
                        borderColor: palette.primary,
                        backgroundColor: palette.primaryFill,
                        pointBackgroundColor: palette.primary,
                        pointBorderColor: palette.tooltipBg,
                    }]
                },
                options: {
                    responsive: true,
                    color: palette.text,
                    plugins: aaHomeChartPlugins(palette),
                    scales: {
                        r: {
                            angleLines: {
                                color: palette.grid,
                            },
                            grid: {
                                color: palette.grid,
                            },
                            pointLabels: {
                                color: palette.text,
                                font: aaHomeChartFont(11, '800'),
                            },
                            ticks: {
                                color: palette.muted,
                                backdropColor: 'transparent',
                                showLabelBackdrop: false,
                                font: aaHomeChartFont(10, '700'),
                            },
                        },
                    },
                }
            }));
        }

        if (creatorCanvas) {
            aaHomeCharts.push(new Chart(creatorCanvas, {
                type: 'bar',
                data: {
                    labels: [
                        'Wedding',
                        'Khitan',
                        'Ulang Tahun',
                        'Corporate',
                        'Seminar',
                        'Wisuda',
                        'Reuni'
                    ],
                    datasets: [{
                        data: [
                            100,
                            74,
                            63,
                            54,
                            48,
                            40,
                            35
                        ],
                        backgroundColor: palette.segments,
                        borderColor: palette.segments,
                        borderWidth: 1,
                        borderRadius: 8,
                        maxBarThickness: 24,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    color: palette.text,
                    plugins: aaHomeChartPlugins(palette, {
                        display: false,
                    }),
                    scales: aaHomeCartesianScales(palette),
                }
            }));
        }

        aaHomeChartsReady = true;
    }

    function initHomeMakerToolsCarousel() {
        const track = document.querySelector('[data-aa-home-tools-track]');
        if (!track) return;

        const scrollByCard = function(direction) {
            const firstCard = track.querySelector('.aa-home-maker-tool-card');
            const cardWidth = firstCard ? firstCard.getBoundingClientRect().width : 180;
            track.scrollBy({
                left: direction * (cardWidth + 16) * 2,
                behavior: 'smooth',
            });
        };

        document.querySelector('[data-aa-home-tools-prev]')?.addEventListener('click', function() {
            scrollByCard(-1);
        });
        document.querySelector('[data-aa-home-tools-next]')?.addEventListener('click', function() {
            scrollByCard(1);
        });
    }

    initHomeMakerToolsCarousel();
    // aaRenderHomeCharts();
    </script>
    <script>
    (function() {
        'use strict';

        var ua = navigator.userAgent || '';
        var notice = document.getElementById('aaBrowserNotice');
        var closeBtn = document.getElementById('aaBrowserClose');
        var openBtn = document.getElementById('aaOpenExternalBrowser');
        var help = document.getElementById('aaBrowserHelp');

        if (!notice || !closeBtn || !openBtn) {
            return;
        }

        /*
         * Deteksi beberapa in-app browser populer.
         * Sengaja dibuat konservatif agar browser normal
         * seperti Chrome / Safari tidak terkena modal.
         */
        var isInstagram = /Instagram/i.test(ua);
        var isFacebook = /FBAN|FBAV|FB_IAB/i.test(ua);
        var isThreads = /Barcelona|Threads/i.test(ua);
        var isTikTok = /TikTok|BytedanceWebview|musical_ly/i.test(ua);

        var isInAppBrowser =
            isInstagram ||
            isFacebook ||
            isThreads ||
            isTikTok;

        var isAndroid = /Android/i.test(ua);
        var isIOS = /iPhone|iPad|iPod/i.test(ua);

        /*
         * Jangan tampilkan lagi selama tab/session yang sama
         * setelah user memilih close.
         */
        var dismissed = false;

        try {
            dismissed = sessionStorage.getItem('aa_browser_notice_closed') === '1';
        } catch (e) {}

        if (isInAppBrowser && !dismissed) {
            notice.classList.add('aa-show');
            notice.setAttribute('aria-hidden', 'false');
        }

        closeBtn.addEventListener('click', function() {
            notice.classList.remove('aa-show');
            notice.setAttribute('aria-hidden', 'true');

            try {
                sessionStorage.setItem('aa_browser_notice_closed', '1');
            } catch (e) {}
        });

        openBtn.addEventListener('click', function() {
            var currentUrl = window.location.href;

            /*
             * ANDROID
             *
             * Coba serahkan URL ke Chrome lewat Android Intent.
             * Ini dijalankan hanya setelah klik user.
             */
            if (isAndroid) {
                var cleanUrl = currentUrl
                    .replace(/^https?:\/\//i, '');

                var intentUrl =
                    'intent://' + cleanUrl +
                    '#Intent;' +
                    'scheme=https;' +
                    'package=com.android.chrome;' +
                    'S.browser_fallback_url=' +
                    encodeURIComponent(currentUrl) +
                    ';end;';

                window.location.href = intentUrl;

                /*
                 * Jika browser sosial media menolak Intent,
                 * beri petunjuk manual.
                 */
                setTimeout(function() {
                    help.classList.add('aa-show-help');
                }, 1200);

                return;
            }

            /*
             * iOS
             *
             * Web biasa tidak dapat menjamin keluar dari
             * WebView Instagram/TikTok/Threads menuju Safari.
             */
            if (isIOS) {
                help.classList.add('aa-show-help');
                return;
            }

            /*
             * Device lain:
             * coba URL standar.
             */
            window.open(currentUrl, '_blank', 'noopener');

            setTimeout(function() {
                help.classList.add('aa-show-help');
            }, 800);
        });

    })();
    </script>
</body>

</html>
