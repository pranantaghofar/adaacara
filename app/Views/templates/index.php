<?php
    helper('seo');

    $isLoggedIn = (bool) (session()->get('isLoggedIn') ?? session()->get('userId'));
    $wishlistTemplateIds = array_map('intval', is_array($wishlistTemplateIds ?? null) ? $wishlistTemplateIds : []);
    $headerHomeUrl = site_url('/');
    $searchQuery = trim((string) ($searchQuery ?? ''));
    $request = service('request');
    $rawProductType = strtolower(trim((string) ($request->getGet('type') ?? '')));
    $activeProductType = in_array($rawProductType, ['invitation', 'photobooth', 'business-profile'], true) ? $rawProductType : '';

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

    $categoryStyles = [
        'all' => ['#f1e9ff', '#7550c4', '<path d="M5 5h6v6H5zM13 5h6v6h-6zM5 13h6v6H5zM13 13h6v6h-6z"/>'],
        'wedding' => ['#ffe4e6', '#be123c', '<path d="M12 21s-7-4.4-7-10a4 4 0 0 1 7-2.4A4 4 0 0 1 19 11c0 5.6-7 10-7 10Z"/>'],
        'seminar' => ['#dbeafe', '#1d4ed8', '<path d="M4 5h16v10H4z"/><path d="M8 19h8M12 15v4"/>'],
        'bukber' => ['#f1e9ff', '#7550c4', '<path d="M7 3v8M11 3v8M7 7h4M9 11v10"/><path d="M17 3v18M14 8c0-3 1-5 3-5"/>'],
        'halal-bihalal' => ['#f1e9ff', '#7550c4', '<path d="M12 3a7 7 0 1 0 7 7 5 5 0 0 1-7-7Z"/><path d="m17 14 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"/>'],
        'lamaran' => ['#fce7f3', '#be185d', '<path d="M8 12h8"/><path d="M12 8v8"/><path d="M5 12a7 7 0 0 1 14 0c0 5-7 9-7 9s-7-4-7-9Z"/>'],
        'ulang-tahun' => ['#fae8ff', '#a21caf', '<path d="M5 12h14v8H5z"/><path d="M7 12V9a2 2 0 1 1 4 0v3M13 12V9a2 2 0 1 1 4 0v3M5 16h14"/>'],
        'khitan' => ['#e0f2fe', '#0369a1', '<path d="M12 4 5 9v10h14V9z"/><path d="M9 19v-6h6v6"/>'],
        'aqiqah' => ['#fce7f3', '#be185d', '<path d="M8 11a4 4 0 1 1 8 0v6H8z"/><path d="M10 7V5M14 7V5M7 17h10"/>'],
        'syukuran' => ['#f1e9ff', '#15803d', '<path d="M4 12h16"/><path d="M12 4v16"/><path d="M7 7c2 2 8 2 10 0M7 17c2-2 8-2 10 0"/>'],
        'wisuda' => ['#ede9fe', '#6d28d9', '<path d="m3 8 9-4 9 4-9 4-9-4Z"/><path d="M7 10v5c3 2 7 2 10 0v-5"/><path d="M21 8v6"/>'],
        'corporate' => ['#e0e7ff', '#4338ca', '<path d="M4 21V7h8v14M12 21V3h8v18M8 11h.01M8 15h.01M16 7h.01M16 11h.01M16 15h.01"/>'],
        'lainnya' => ['#f1f5f9', '#475569', '<path d="M12 3l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7L6.8 19l1-5.8-4.2-4.1 5.8-.8z"/>'],
    ];

    $categoryIcon = static function (string $key) use ($categoryStyles): string {
        $style = $categoryStyles[$key] ?? $categoryStyles['lainnya'];

        return '<span class="aa-template-filter-icon" style="--aa-filter-icon-bg: ' . esc($style[0], 'attr') . '; --aa-filter-icon-fg: ' . esc($style[1], 'attr') . '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $style[2] . '</svg></span>';
    };

    $templateSubcategoryGroups = is_array($templateSubcategoryGroups ?? null) ? $templateSubcategoryGroups : [];
    $activeSubcategorySlug = strtolower(trim((string) ($selectedSubcategory['slug'] ?? '')));
    $isSubcategoryFilterActive = $activeSubcategorySlug !== '';
    if ($activeProductType === '' && ($isSubcategoryFilterActive || $searchQuery !== '')) {
        $activeProductType = 'invitation';
    }
    $isInvitationProduct = $activeProductType === 'invitation';
    $isBusinessProfileProduct = $activeProductType === 'business-profile';
    $isProductLanding = $activeProductType === '';
    $isMobileInvitationSelectionActive = $isInvitationProduct && ($isSubcategoryFilterActive || $searchQuery !== '');
    $productTemplateTypes = [
        'invitation' => [
            'label' => 'Undangan Digital',
            'description' => 'Buat website undangan interaktif.',
            'icon' => 'wedding',
            'url' => site_url('templates') . '?type=invitation',
        ],
        'photobooth' => [
            'label' => 'Digital Photobooth',
            'description' => 'Template frame dan QR photobooth.',
            'icon' => 'corporate',
            'url' => site_url('templates') . '?type=photobooth',
            'create' => true,
        ],
        'business-profile' => [
            'label' => 'Business Profile',
            'description' => 'Template profile untuk vendor dan freelancer.',
            'icon' => 'lainnya',
            'url' => site_url('templates') . '?type=business-profile',
        ],
    ];
    $businessProfileSubcategories = [
        ['slug' => 'mua', 'label' => 'MUA', 'query' => 'MUA Make Up Artist Makeup'],
        ['slug' => 'wedding-organizer', 'label' => 'Wedding Organizer', 'query' => 'Wedding Organizer WO Planner'],
        ['slug' => 'dekorasi', 'label' => 'Dekorasi', 'query' => 'Dekorasi Decoration Decor'],
        ['slug' => 'venue', 'label' => 'Venue', 'query' => 'Venue Gedung Ballroom'],
        ['slug' => 'catering', 'label' => 'Catering', 'query' => 'Catering Menu Prasmanan'],
        ['slug' => 'photographer', 'label' => 'Photographer', 'query' => 'Photographer Fotografer Photography Foto'],
        ['slug' => 'freelancer', 'label' => 'Freelancer', 'query' => 'Freelancer Freelance'],
        ['slug' => 'umkm', 'label' => 'UMKM', 'query' => 'UMKM Usaha Produk Toko'],
        ['slug' => 'agency', 'label' => 'Agency', 'query' => 'Agency Agensi Studio'],
    ];
    $businessSubcategorySlug = $isBusinessProfileProduct
        ? strtolower(trim((string) ($request->getGet('business_category') ?? '')))
        : '';
    $businessSubcategorySlugs = array_column($businessProfileSubcategories, 'slug');
    if (! in_array($businessSubcategorySlug, $businessSubcategorySlugs, true)) {
        $businessSubcategorySlug = '';
    }
    $selectedBusinessSubcategory = null;
    foreach ($businessProfileSubcategories as $businessSubcategory) {
        if ($businessSubcategory['slug'] === $businessSubcategorySlug) {
            $selectedBusinessSubcategory = $businessSubcategory;
            break;
        }
    }
    $businessProfileTemplateMatches = [];
    if ($selectedBusinessSubcategory !== null) {
        $businessKeywords = preg_split('/[\s,;|]+/', strtolower((string) ($selectedBusinessSubcategory['query'] ?? ''))) ?: [];
        $businessKeywords = array_values(array_unique(array_filter(array_map('trim', $businessKeywords), static fn (string $keyword): bool => mb_strlen($keyword) >= 3)));
        foreach ((array) ($templates ?? []) as $template) {
            $haystack = strtolower(trim(implode(' ', [
                (string) ($template['name'] ?? ''),
                (string) ($template['slug'] ?? ''),
                (string) ($template['description'] ?? ''),
                (string) ($template['tags'] ?? ''),
                (string) ($template['category_name'] ?? ''),
                (string) ($template['category_slug'] ?? ''),
            ])));
            $matchesBusinessCategory = $businessKeywords === [];
            foreach ($businessKeywords as $keyword) {
                if ($haystack !== '' && str_contains($haystack, $keyword)) {
                    $matchesBusinessCategory = true;
                    break;
                }
            }
            if ($matchesBusinessCategory) {
                $businessProfileTemplateMatches[] = $template;
            }
        }
    }
    $sidebarGroupLabels = [
        'wedding' => ['Pernikahan', 'wedding'],
        'birthday' => ['Ulang Tahun', 'ulang-tahun'],
        'kids' => ['Aqiqah & Anak', 'aqiqah'],
        'party' => ['Pesta', 'bukber'],
        'cards' => ['Kartu ucapan', 'lainnya'],
        'trending' => ['Sedang tren', 'all'],
    ];
    $sidebarCategoryMap = [
        'wedding' => ['wedding', 'lamaran'],
        'birthday' => ['ulang-tahun'],
        'kids' => ['aqiqah', 'khitan', 'syukuran'],
        'party' => ['bukber', 'halal-bihalal', 'corporate', 'wisuda'],
    ];
    $templateSidebarGroups = [];
    foreach ($sidebarGroupLabels as $groupKey => [$groupLabel, $iconKey]) {
        $items = [];
        foreach (($sidebarCategoryMap[$groupKey] ?? []) as $categorySlug) {
            foreach (($templateSubcategoryGroups[$categorySlug] ?? []) as $subcategory) {
                $slug = trim((string) ($subcategory['slug'] ?? ''));
                $label = trim((string) ($subcategory['name'] ?? ''));
                if ($slug === '' || $label === '') {
                    continue;
                }
                $items[] = [
                    'label' => $label,
                    'url' => site_url('templates') . '?type=invitation&subcategory=' . rawurlencode($slug),
                    'active' => $activeSubcategorySlug === $slug,
                ];
            }
        }

        $templateSidebarGroups[] = [
            'label' => $groupLabel,
            'icon' => $iconKey,
            'items' => $items,
        ];
    }

    $normalizeCategory = static function (array $template) use ($categoryLabels): array {
        $sourceSlug = strtolower(trim((string) (
            $template['category_slug']
            ?? ''
        )));
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
            'bukber' => ['bukber', 'buka bersama', 'ramadhan', 'iftar'],
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

    $blankTemplateUrl = $isLoggedIn ? site_url('templates') : site_url('login');
    $creatorApplyUrl = $isLoggedIn ? site_url('creator/apply') : site_url('login');
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
    $premiumCrownState = ! empty($hasActiveMembership) ? 'is-unlocked' : 'is-locked';
    $premiumCrownSvg = '<span class="aa-template-premium-badge ' . $premiumCrownState . '" aria-label="Template premium"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.91 4.91a1.91 1.91 0 0 1-1.044 1.701c.942 2.366 1.928 3.53 2.795 3.622.982.104 1.88-.323 2.76-1.377a.977.977 0 0 1 .072-.078 1.91 1.91 0 1 1 1.468.873l-1.423 5.42c-.297 1.13-1.363 1.922-2.586 1.922H8.066c-1.223 0-2.29-.792-2.586-1.922L4.063 9.675a1.91 1.91 0 1 1 1.46-.898c.03.028.059.06.086.093.837 1.048 1.727 1.471 2.748 1.363.908-.096 1.888-1.253 2.793-3.614a1.91 1.91 0 1 1 2.76-1.71ZM6.561 19.008h10.875c.518 0 .938.448.938 1s-.42 1-.938 1H6.563c-.517 0-.937-.448-.937-1s.42-1 .937-1Z" fill="currentColor"></path></svg></span>';

?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->title((string) ($title ?? 'Template Undangan Digital - AdaAcara'))
        ->description('Pilih template undangan digital AdaAcara untuk wedding, aqiqah, khitan, ulang tahun, seminar, bukber, halal bihalal, dan acara corporate.')
        ->canonical(site_url('templates'))
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Templates', 'url' => site_url('templates')],
        ])
        ->render() ?>
   <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Great+Vibes&family=Montserrat:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Aboreto&family=Abril+Fatface&family=Adamina&family=Alex+Brush&family=Allura&family=Amarante&family=Amiri:wght@400;700&family=Arizonia&family=Bellefair&family=Bodoni+Moda:wght@400;500;600;700&family=Bonheur+Royale&family=Caudex:wght@400;700&family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600;700&family=Cormorant+Infant:wght@400;500;600;700&family=Cormorant+Upright:wght@400;500;600;700&family=DM+Serif+Display&family=Dancing+Script:wght@400;500;600;700&family=Elsie:wght@400;900&family=Ephesis&family=Fleur+De+Leah&family=Forum&family=Fraunces:wght@400;500;600;700&family=Great+Vibes&family=Imperial+Script&family=Italiana&family=Italianno&family=Lavishly+Yours&family=Libre+Baskerville:wght@400;700&family=Lora:wght@400;500;600;700&family=Marcellus&family=Mea+Culpa&family=Monsieur+La+Doulaise&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Parisienne&family=Petit+Formal+Script&family=Philosopher:wght@400;700&family=Playfair+Display:wght@400;500;600;700;800;900&family=Poiret+One&family=Prata&family=Questrial&family=Quintessential&family=Sorts+Mill+Goudy&family=Tangerine:wght@400;700&family=The+Nautigal:wght@400;700&family=Unna:wght@400;700&family=Viaoda+Libre&family=WindSong:wght@400;500&family=Yeseva+One&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <script src="https://cdn.jsdelivr.net/npm/lenis@1.3.8/dist/lenis.min.js" defer></script>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <script src="https://cdn.tailwindcss.com"></script>
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

    .aa-template-project-choice {
        padding: clamp(24px, 3vw, 36px);
    }

    .aa-template-project-choice-head {
        position: relative;
        margin: 0 auto 22px;
        max-width: 640px;
        padding: 0 52px;
        text-align: center;
    }

    .aa-template-project-choice-spark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-bottom: 12px;
        color: #d99a0a;
        font-size: 30px;
        line-height: 1;
    }

    .aa-template-project-choice-spark i {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #a855f7;
        box-shadow: 15px -5px 0 rgba(216, 180, 254, .9), -12px 8px 0 rgba(245, 158, 11, .7);
    }

    .aa-template-project-choice-head h3 {
        margin: 0;
        color: #24143f;
        font-size: clamp(28px, 3vw, 38px);
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.08;
    }

    .aa-template-project-choice-head p {
        margin: 14px 0 0;
        color: #475569;
        font-size: clamp(14px, 1.35vw, 17px);
        line-height: 1.5;
    }

    .aa-template-project-choice-close {
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
    }

    .aa-template-project-choice-close svg {
        width: 24px;
        height: 24px;
    }

    .aa-template-project-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 16px;
    }

    .aa-template-project-card {
        display: grid;
        grid-column: span 2;
        grid-template-columns: 70px minmax(0, 1fr) 42px;
        min-height: 148px;
        align-items: center;
        gap: 14px;
        border: 1px solid rgba(168, 85, 247, .32);
        border-radius: 16px;
        background:
            radial-gradient(circle at 20% 32%, rgba(168, 85, 247, .12), transparent 34%),
            rgba(255, 255, 255, .82);
        padding: 18px 16px;
        color: #1f1637;
        text-decoration: none;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .aa-template-project-card:hover {
        border-color: rgba(126, 34, 206, .48);
        box-shadow: 0 22px 45px rgba(88, 28, 135, .12);
        color: #1f1637;
        transform: translateY(-3px);
    }

    .aa-template-project-card.is-disabled,
    .aa-template-product-card.is-disabled {
        cursor: not-allowed;
        opacity: .72;
        pointer-events: none;
    }

    .aa-template-project-card.is-disabled:hover,
    .aa-template-product-card.is-disabled:hover {
        transform: none;
        box-shadow: none;
    }

    .aa-template-project-card.is-gold {
        border-color: rgba(217, 153, 10, .34);
        background:
            radial-gradient(circle at 20% 32%, rgba(245, 158, 11, .16), transparent 34%),
            rgba(255, 253, 247, .9);
    }

    .aa-template-project-card.is-soft {
        border-color: rgba(236, 72, 153, .22);
        background:
            radial-gradient(circle at 20% 32%, rgba(244, 114, 182, .12), transparent 34%),
            rgba(255, 250, 252, .9);
    }

    .aa-template-project-card.is-lower-start {
        grid-column: 2 / span 2;
    }

    .aa-template-project-icon {
        display: grid;
        width: 64px;
        height: 64px;
        place-items: center;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(168, 85, 247, .12), rgba(126, 34, 206, .08));
        color: #6d28d9;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .9), 0 16px 35px rgba(88, 28, 135, .1);
    }

    .aa-template-project-card.is-gold .aa-template-project-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, .18), rgba(251, 191, 36, .08));
        color: #d97706;
    }

    .aa-template-project-card.is-soft .aa-template-project-icon {
        background: linear-gradient(135deg, rgba(244, 114, 182, .14), rgba(168, 85, 247, .08));
        color: #7e22ce;
    }

    .aa-template-project-icon svg {
        width: 34px;
        height: 34px;
    }

    .aa-template-project-copy h4 {
        margin: 0 0 8px;
        color: #4c1d95;
        font-size: clamp(18px, 1.55vw, 23px);
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.18;
    }

    .aa-template-project-copy p {
        margin: 0;
        color: #334155;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.45;
    }

    .aa-template-project-arrow {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 999px;
        background: rgba(168, 85, 247, .12);
        color: #7e22ce;
    }

    .aa-template-project-card.is-gold .aa-template-project-arrow {
        background: rgba(245, 158, 11, .12);
        color: #d97706;
    }

    .aa-template-project-card.is-soft .aa-template-project-arrow {
        background: rgba(244, 114, 182, .12);
    }

    .aa-template-project-arrow svg {
        width: 24px;
        height: 24px;
    }

    .aa-template-project-badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        border-radius: 999px;
        background: linear-gradient(135deg, #7c3aed, #5b21b6);
        padding: 4px 12px;
        color: #fff;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .aa-template-soon-pill {
        display: inline-flex;
        width: fit-content;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: linear-gradient(135deg, #7c3aed, #5b21b6);
        padding: 3px 9px;
        color: #fff;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .aa-template-project-foot {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 22px;
        color: #475569;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.5;
        text-align: center;
    }

    .aa-template-project-foot svg {
        width: 26px;
        height: 26px;
        color: #7c3aed;
        flex: 0 0 auto;
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


    .aa-home-template-preview {
        position: relative;
        display: block;
        aspect-ratio: 6 / 9.5;
        min-height: 210px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .92);
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

    .aa-template-blank-card .aa-home-template-preview {
        min-height: 0;
    }

    .aa-home-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 24px;
        background: #ffffff;
        padding: 32px;
        color: var(--aa-muted);
        text-align: center;
    }

     .aa-home-nav-links a,
    .aa-home-btn {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 800;
        transition: .18s ease;
    }

    .aa-home-nav-links a {
        color: #475569;
    }

    .aa-home-nav-links a:hover {
        background: #f1f5f9;
        color: var(--aa-teal);
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

    .aa-template-shell {
        max-width: none;
    }

    .aa-template-page.aa-app-ui {
        --aa-teal: #8f65df;
        --aa-teal-dark: #7550c4;
        background-position: center top;
        background-attachment: fixed;
    }

    .aa-template-market-layout {
        display: block;
    }

    .aa-template-sidebar {
        display: none;
    }

    .aa-template-content {
        min-width: 0;
    }

    .aa-template-sidebar-card {
        display: flex;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .88);
        border-radius: 24px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 18px 46px rgba(15, 23, 42, .08);
    }

    .aa-template-sidebar-head {
        display: grid;
        gap: 5px;
        border-bottom: 1px solid #f1f5f9;
        padding: 18px;
    }

    .aa-template-sidebar-head strong {
        color: #0f172a;
        font-size: 16px;
        font-weight: 950;
        letter-spacing: -.02em;
    }

    .aa-template-sidebar-head span {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        line-height: 1.45;
    }

    .aa-template-sidebar-scroll {
        flex: 1 1 auto;
        max-height: calc(100vh - 188px);
        min-height: 0;
        overflow: auto;
        padding: 12px;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .aa-template-sidebar-all,
    .aa-template-sidebar-link {
        display: flex;
        width: 100%;
        min-width: 0;
        align-items: center;
        gap: 10px;
        border: 1px solid transparent;
        border-radius: 16px;
        text-decoration: none;
        transition: .16s ease;
    }

    .aa-template-sidebar-all {
        min-height: 48px;
        margin-bottom: 12px;
        background: #fff9f5;
        color: #7550c4;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-template-sidebar-all.is-active,
    .aa-template-sidebar-all:hover {
        border-color: #d9ccf4;
        box-shadow: 0 10px 24px rgba(143, 101, 223, .12);
    }

    .aa-template-sidebar-group {
        display: grid;
        gap: 6px;
        border-top: 1px solid #f1f5f9;
        padding: 12px 0;
    }

    .aa-template-sidebar-group:first-of-type {
        border-top: 0;
        padding-top: 0;
    }

    .aa-template-sidebar-title {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 0 7px 4px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-template-sidebar-links {
        display: grid;
        gap: 4px;
    }

    .aa-template-sidebar-link {
        min-height: 38px;
        color: #475569;
        padding: 7px 10px 7px 14px;
        font-size: 12px;
        font-weight: 850;
    }

    .aa-template-sidebar-link:hover,
    .aa-template-sidebar-link.is-active {
        border-color: rgba(250, 204, 21, .55);
        background: #fff9f5;
        color: #7550c4;
    }

    .aa-template-sidebar-link.is-active::before {
        width: 7px;
        height: 7px;
        flex: 0 0 auto;
        border-radius: 999px;
        background: #8f65df;
        content: "";
    }

    .aa-template-product-list {
        display: grid;
        gap: 10px;
    }

    .aa-template-product-card,
    .aa-template-product-back {
        display: grid;
        min-width: 0;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 18px;
        background: #ffffff;
        color: #0f172a;
        padding: 13px;
        text-decoration: none;
        transition: .16s ease;
    }

    .aa-template-product-card {
        grid-template-columns: 36px minmax(0, 1fr) 22px;
        align-items: center;
        gap: 10px;
    }

    .aa-template-product-card:hover,
    .aa-template-product-card.is-active,
    .aa-template-product-back:hover {
        border-color: rgba(143, 101, 223, .5);
        background: #fff9f5;
        color: #7550c4;
        box-shadow: 0 12px 28px rgba(143, 101, 223, .12);
    }

    .aa-template-product-copy {
        display: grid;
        gap: 3px;
        min-width: 0;
    }

    .aa-template-product-copy strong {
        overflow: hidden;
        color: inherit;
        font-size: 13px;
        font-weight: 950;
        line-height: 1.2;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-template-product-copy span {
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
        line-height: 1.35;
    }

    .aa-template-product-card svg,
    .aa-template-product-back svg {
        width: 18px;
        height: 18px;
    }

    .aa-template-product-arrow {
        display: grid;
        width: 22px;
        height: 22px;
        place-items: center;
        border-radius: 999px;
        background: rgba(143, 101, 223, .1);
        color: #7550c4;
    }

    .aa-template-product-back {
        display: flex;
        min-height: 44px;
        align-items: center;
        gap: 9px;
        margin-bottom: 10px;
        color: #475569;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-template-mobile-active-filter {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(143, 101, 223, .24);
        border-radius: 18px;
        background: linear-gradient(135deg, #fff9f5 0%, #f8f4ff 100%);
        padding: 12px;
        color: #24143f;
    }

    .aa-template-mobile-active-filter > span {
        display: grid;
        gap: 2px;
        min-width: 0;
    }

    .aa-template-mobile-active-filter strong {
        color: #7550c4;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .aa-template-mobile-active-filter em {
        overflow: hidden;
        color: #0f172a;
        font-size: 13px;
        font-style: normal;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-template-mobile-products {
        display: grid;
        gap: 10px;
        margin-bottom: 14px;
        border: 1px solid rgba(217, 204, 244, .9);
        border-radius: 20px;
        background: rgba(255, 255, 255, .94);
        padding: 12px;
        box-shadow: 0 16px 36px rgba(143, 101, 223, .1);
    }

    .aa-template-product-empty {
        display: grid;
        min-height: 280px;
        place-items: center;
        border: 1px dashed rgba(143, 101, 223, .35);
        border-radius: 24px;
        background:
            radial-gradient(circle at 18% 14%, rgba(250, 204, 21, .12), transparent 28%),
            radial-gradient(circle at 80% 18%, rgba(143, 101, 223, .12), transparent 30%),
            rgba(255, 255, 255, .94);
        padding: 32px;
        text-align: center;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .06);
    }

    .aa-template-product-empty-inner {
        max-width: 520px;
    }

    .aa-template-product-empty-icon {
        display: grid;
        width: 58px;
        height: 58px;
        place-items: center;
        margin: 0 auto 14px;
        border-radius: 20px;
        background: #f1e9ff;
        color: #7550c4;
    }

    .aa-template-product-empty-icon svg {
        width: 28px;
        height: 28px;
    }

    .aa-template-product-empty h2 {
        margin: 0;
        color: #24143f;
        font-size: clamp(24px, 3vw, 34px);
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.12;
    }

    .aa-template-product-empty p {
        margin: 12px 0 0;
        color: #64748b;
        font-size: 15px;
        font-weight: 750;
        line-height: 1.65;
    }

    .aa-template-search {
        display: grid;
        gap: 10px;
        margin-bottom: 18px;
        margin: 0px 15px 35px 15px;
        border: 1px solid #d9ccf4;
        border-radius: 22px;
        background: rgba(255, 255, 255, .94);
        padding: 12px;
        box-shadow: 0 18px 46px rgba(143, 101, 223, .12);
    }

    .aa-template-search-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
    }

    .aa-template-search-input {
        min-height: 46px;
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 14px;
        font-size: 14px;
        font-weight: 800;
        outline: none;
    }

    .aa-template-search-input:focus {
        border-color: #8f65df;
        box-shadow: 0 0 0 4px rgba(143, 101, 223, .14);
    }

    .aa-template-search-btn,
    .aa-template-search-clear {
        display: inline-flex;
        min-height: 46px;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .aa-template-search-btn {
        border: 1px solid #d9ccf4;
        background: linear-gradient(135deg, #fff9f5 0%, #f1e9ff 100%);
        color: #7550c4;
        padding: 0 18px;
    }

    .aa-template-search-clear {
        width: fit-content;
        color: #64748b;
        padding: 0 4px;
    }

    .aa-template-search-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .aa-template-search-chip {
        border-radius: 999px;
        background: #fff9f5;
        color: #7550c4;
        padding: 5px 9px;
    }

    .aa-template-filter {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .aa-template-filter-btn {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        padding: 6px 12px;
        color: #475569;
        font-size: 12px;
        font-weight: 850;
        text-align: left;
        transition: .18s ease;
    }

    .aa-template-filter-icon {
        display: grid;
        width: 28px;
        height: 28px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 10px;
        background: var(--aa-filter-icon-bg, #f1e9ff);
        color: var(--aa-filter-icon-fg, #7550c4);
    }

    .aa-template-filter-icon svg {
        width: 15px;
        height: 15px;
    }

    .aa-template-filter-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-template-filter-btn:hover,
    .aa-template-filter-btn.is-active {
        border-color: #8f65df;
        background: #fff9f5;
        color: #8f65df;
        box-shadow: 0 10px 24px rgba(143, 101, 223, .14);
    }

    @media (max-width: 1023px) {
        .aa-template-search-form {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 767px) {
        .aa-template-search {
            display: none;
        }

        .aa-template-header-inner {
            min-height: auto;
            flex-direction: row;
            align-items: center;
            gap: 12px;
            padding-top: 12px;
            padding-bottom: 12px;
        }

        .aa-template-header-brand {
            justify-content: flex-start;
        }

        .aa-template-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 5px;
            padding-top: 2px;
        }

        .aa-template-grid > [data-template-card] {
            display: block;
            width: 100%;
            margin: 0;
            break-inside: auto;
            vertical-align: initial;
        }

        .aa-template-grid > [data-template-card].hidden {
            display: none;
        }

        .aa-template-grid .aa-template-thumb,
        .aa-template-grid .aa-home-template-preview {
            margin: 0;
            border-radius: 14px;
        }

        .aa-template-grid .aa-template-thumb {
            aspect-ratio: auto !important;
        }

        .aa-template-grid .aa-template-thumb::before {
            aspect-ratio: var(--aa-template-slot-ratio, 6 / 10);
        }

    }

    @media (min-width: 1024px) {
        .aa-template-page .aa-public-site-nav {
            display: none;
        }

        .aa-template-shell {
            --aa-template-sidebar-width: clamp(320px, 18vw, 390px);
            --aa-template-layout-gap: clamp(20px, 1.8vw, 34px);
            padding-left: clamp(16px, 2vw, 34px);
            padding-right: clamp(16px, 2vw, 34px);
        }

        .aa-template-shell > .mb-5:first-child {
            margin-left: calc(var(--aa-template-sidebar-width) + var(--aa-template-layout-gap));
        }

        .aa-template-market-layout {
            display: block;
        }

        .aa-template-sidebar {
            position: fixed;
            top: 94px;
            bottom: 24px;
            left: clamp(16px, 2vw, 34px);
            display: block;
            width: var(--aa-template-sidebar-width);
            z-index: 20;
        }

        .aa-template-content {
            margin-left: calc(var(--aa-template-sidebar-width) + var(--aa-template-layout-gap));
            position: relative;
        }

        .aa-template-sidebar-scroll {
            max-height: none;
        }

        .aa-template-inline-filter {
            display: none;
        }

        .aa-template-mobile-products {
            display: none;
        }

        .aa-template-search {
            position: sticky;
            top: 88px;
            z-index: 30;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 18px 44px rgba(15, 23, 42, .12);
        }

        .aa-template-page .aa-template-gallery-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 4px 0 10px;
        }

        .aa-template-gallery-tabs,
        .aa-template-gallery-modes {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .aa-template-gallery-tab,
        .aa-template-gallery-mode {
            appearance: none;
            border: 1px solid transparent;
            background: transparent;
            color: #64748b;
            cursor: default;
            font: inherit;
            font-size: 14px;
            font-weight: 900;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
        }

        .aa-template-gallery-tab.is-active {
            color: #0f172a;
        }

        .aa-template-gallery-mode {
            display: inline-flex;
            position: relative;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 999px;
            padding: 0 12px;
        }

        .aa-template-gallery-soon {
            position: absolute;
            top: -13px;
            right: 6px;
            display: inline-flex;
            height: 17px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(143, 101, 223, .22);
            border-radius: 999px;
            background: #f4edff;
            color: #7550c4;
            font-size: 9px;
            font-weight: 950;
            line-height: 1;
            padding: 0 6px;
            box-shadow: 0 8px 18px rgba(143, 101, 223, .14);
            pointer-events: none;
        }

        .aa-template-gallery-mode svg {
            width: 15px;
            height: 15px;
        }

        .aa-template-gallery-mode.is-active {
            border-color: rgba(143, 101, 223, .28);
            background: #fff9f5;
            color: #7550c4;
            box-shadow: 0 10px 24px rgba(143, 101, 223, .12);
        }

        .aa-template-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)) !important;
            gap: 14px;
            padding-top: 6px;
        }

        .aa-template-grid > [data-template-card] {
            display: block;
            width: 100%;
            margin: 0;
            break-inside: auto;
            vertical-align: initial;
        }

        .aa-template-grid > [data-template-card].hidden {
            display: none;
        }

        .aa-template-grid .aa-template-thumb,
        .aa-template-grid .aa-home-template-preview {
            margin: 0;
            border-radius: 18px;
        }

        .aa-template-grid .aa-template-thumb {
            aspect-ratio: 6 / 10 !important;
            border-color: rgba(15, 23, 42, .12);
        }

        .aa-template-grid .aa-template-thumb::before {
            display: block;
            aspect-ratio: var(--aa-template-slot-ratio, 6 / 10);
        }

        .aa-template-grid > [data-template-card] .aa-home-template-preview {
            aspect-ratio: 6 / 10;
        }

        .aa-template-grid .aa-template-thumb > img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .aa-template-grid .aa-template-thumb > iframe {
            position: absolute;
            aspect-ratio: 6 / 10;
            height: 100%;
        }

        .aa-template-grid .aa-template-blank-card .aa-home-template-preview {
            aspect-ratio: auto;
            min-height: 220px;
            height: 220px;
            border-radius: 18px;
        }

        .aa-template-grid .aa-template-blank-card .aa-home-template-blank-preview {
            height: 100%;
            min-height: 100%;
        }

        .aa-template-page .aa-template-end-marker {
            display: grid;
            margin: 18px auto 10px;
            max-width: 260px;
            place-items: center;
            gap: 12px;
            color: #94a3b8;
            text-align: center;
            font-size: 13px;
            font-weight: 850;
        }

        .aa-template-page .aa-template-end-marker::before {
            width: 170px;
            height: 1px;
            background: #cbd5e1;
            content: "";
        }
    }

    @media (min-width: 1180px) {
        .aa-template-market-layout .aa-template-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important;
        }
    }

    @media (min-width: 1540px) {
        .aa-template-market-layout .aa-template-grid {
            grid-template-columns: repeat(auto-fill, minmax(235px, 1fr)) !important;
        }
    }

    @media (min-width: 1860px) {
        .aa-template-market-layout .aa-template-grid {
            grid-template-columns: repeat(auto-fill, minmax(255px, 1fr)) !important;
        }
    }

    .aa-template-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
    }

    [data-template-card].is-entering {
        animation: aaTemplateCardEnter .24s ease both;
    }

    @keyframes aaTemplateCardEnter {
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
        [data-template-card].is-entering {
            animation: none;
        }
    }

    @media (min-width: 368px) {
        .aa-template-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (min-width: 820px) {
        .aa-template-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
    }

    @media (min-width: 1180px) {
        .aa-template-grid {
            grid-template-columns: repeat(6, minmax(0, 1fr)) !important ;
        }
    }

    .aa-template-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 14px 40px rgba(15, 23, 42, .06);
        transition: .18s ease;
    }

    .aa-template-card:hover {
        transform: translateY(-2px);
        border-color: #d9ccf4;
        box-shadow: 0 18px 46px rgba(15, 23, 42, .10);
    }

    .aa-template-end-marker {
        display: none;
    }

    .aa-template-gallery-nav {
        display: none;
    }

    .aa-template-thumb {
        position: relative;
        margin: 12px 12px 0;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: linear-gradient(180deg, #f8fafc, #eef2f7);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .82);
    }

    .aa-template-thumb[data-preview-open] {
        cursor: pointer;
    }

    .aa-template-thumb[data-preview-open]:hover {
        border-color: #8f65df;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, .82),
            0 16px 34px rgba(143, 101, 223, .18);
    }

    .aa-template-thumb[data-preview-open]::after {
        content: "Preview";
        position: absolute;
        left: 50%;
        bottom: 12px;
        z-index: 2;
        transform: translateX(-50%) translateY(8px);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        border-radius: 999px;
        background: rgba(15, 23, 42, .78);
        color: #ffffff;
        padding: 0 12px;
        font-size: 11px;
        font-weight: 900;
        opacity: 0;
        transition: .18s ease;
        backdrop-filter: blur(10px);
    }

    .aa-template-thumb-actions {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 12px;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        opacity: 0;
        transform: translateY(8px);
        transition: .18s ease;
        pointer-events: none;
    }

    .aa-template-thumb:hover .aa-template-thumb-actions,
    .aa-template-thumb:focus-within .aa-template-thumb-actions {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    .aa-template-thumb-action {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 999px;
        background: rgba(15, 23, 42, .58);
        color: #ffffff;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .16);
        backdrop-filter: blur(12px);
        transition: .16s ease;
    }

    .aa-template-thumb-action:hover {
        background: rgba(15, 23, 42, .74);
        transform: translateY(-1px);
    }

    .aa-template-thumb-action svg {
        width: 15px;
        height: 15px;
        flex: 0 0 auto;
    }

    .aa-template-thumb-action.love {
        width: 36px;
        padding: 0;
    }

    .aa-template-thumb-action.love.is-active {
        border-color: rgba(244, 63, 94, .4);
        background: rgba(244, 63, 94, .86);
        color: #ffffff;
    }

    .aa-template-thumb-action.love.is-active svg {
        fill: currentColor;
    }

    .aa-template-thumb-action.is-loading {
        opacity: .72;
        pointer-events: none;
    }

    @media (max-width: 767px) {
        .aa-template-thumb-actions {
            left: 7px;
            right: 7px;
            bottom: 7px;
            gap: 6px;
        }

        .aa-template-thumb-action {
            min-height: 30px;
            border-color: rgba(255, 255, 255, .44);
            background: rgba(15, 23, 42, .36);
            color: #ffffff;
            padding: 0 10px;
            font-size: 11px;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .18);
            -webkit-backdrop-filter: blur(12px) saturate(1.25);
            backdrop-filter: blur(12px) saturate(1.25);
        }

        .aa-template-thumb-action:hover {
            background: rgba(15, 23, 42, .46);
        }

        .aa-template-thumb-action svg {
            width: 13px;
            height: 13px;
        }

        .aa-template-thumb-action.love {
            width: 30px;
            min-height: 30px;
        }

        .aa-template-thumb-action.love.is-active {
            border-color: rgba(255, 255, 255, .56);
            background: rgba(244, 63, 94, .58);
        }

        .aa-template-thumb-action.love.is-active svg {
            width: 13px;
            height: 13px;
        }
    }

    @media (hover: none) {
        .aa-template-thumb-actions {
            opacity: 1;
            transform: none;
            pointer-events: auto;
        }
    }

    .aa-template-thumb[data-preview-open]:hover::after {
        opacity: 0;
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

    .aa-template-thumb[data-preview-open]>iframe {
        pointer-events: none;
    }

    .aa-template-thumb::before {
        content: "";
        display: block;
        aspect-ratio: 6 / 10;
    }

    @supports not (aspect-ratio: 1 / 1) {
        .aa-home-template-preview {
            min-height: 0;
        }

        .aa-home-template-preview::before {
            content: "";
            display: block;
            padding-top: 158.333%;
        }

        .aa-home-template-preview>img,
        .aa-home-template-preview>iframe,
        .aa-home-template-preview>.aa-home-template-blank-preview {
            position: absolute;
            inset: 0;
        }

        .aa-template-thumb::before {
            padding-top: 166.667%;
        }
    }

    .aa-template-thumb>img,
    .aa-template-thumb>iframe {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
    }

    .aa-template-thumb>img {
        object-fit: cover;
    }

    .aa-template-thumb>iframe {
        background: #ffffff;
        transform-origin: top left;
    }

    .aa-template-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 12px;
        padding: 14px;
    }

    .aa-template-title {
        display: -webkit-box;
        min-height: 40px;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        font-size: 15px;
        font-weight: 850;
        line-height: 1.25;
        letter-spacing: -.01em;
    }

    .aa-template-description {
        display: -webkit-box;
        min-height: 38px;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.55;
    }

    .aa-template-badge {
        display: inline-flex;
        height: 24px;
        align-items: center;
        border-radius: 999px;
        padding: 0 9px;
        font-size: 10px;
        font-weight: 900;
    }

    .aa-template-action {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 850;
        transition: .18s ease;
    }

    .aa-template-form {
        border-top: 1px solid #f1f5f9;
        padding-top: 12px;
    }

    .aa-template-form details>summary {
        cursor: pointer;
        list-style: none;
    }

    .aa-template-form details>summary::-webkit-details-marker {
        display: none;
    }

    .aa-template-modal {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .62);
        padding: 18px;
    }

    .aa-template-modal.is-open {
        display: flex;
    }

    .aa-template-modal-card {
        display: flex;
        flex-direction: column;
        width: min(980px, 100%);
        max-height: 92vh;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .85);
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 28px 90px rgba(15, 23, 42, .28);
    }

    .aa-template-modal-preview {
        flex: 1 1 auto;
        min-height: 0;
        max-height: min(72vh, 760px);
        background: #f8fafc;
        overflow: auto;
        overscroll-behavior: contain;
    }

    .aa-template-modal-preview img,
    .aa-template-modal-preview iframe {
        display: block;
        width: 100%;
        border: 0;
        background: #f8fafc;
    }

    .aa-template-modal-preview img {
        height: auto;
        min-height: 100%;
        object-fit: contain;
    }

    .aa-template-modal-preview iframe {
        height: 2200px;
        min-height: 100%;
    }

    .aa-template-modal-preview-light {
        display: grid;
        grid-template-columns: minmax(0, 320px) minmax(0, 1fr);
        gap: 22px;
        align-items: center;
        min-height: 100%;
        padding: 22px;
    }

    .aa-template-modal-cover {
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

    .aa-template-modal-cover img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .aa-template-modal-placeholder {
        display: grid;
        height: 100%;
        place-items: center;
        padding: 24px;
        color: #8f65df;
        text-align: center;
    }

    .aa-template-modal-placeholder strong {
        display: block;
        color: #0f172a;
        font-size: 20px;
        font-weight: 950;
        line-height: 1.2;
    }

    .aa-template-modal-copy h3 {
        margin: 0;
        color: #0f172a;
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -.03em;
        line-height: 1.16;
    }

    .aa-template-modal-copy p {
        margin: 12px 0 0;
        color: #64748b;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.7;
    }

    .aa-template-modal-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .aa-template-modal-action {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 999px;
        padding: 0 16px;
        cursor: pointer;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        font-family: inherit;
        transition: .18s ease;
    }

    .aa-template-modal-action.primary {
        background: #8f65df;
        color: #ffffff;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .20);
    }

    .aa-template-modal-action.primary:hover {
        background: #7550c4;
    }

    .aa-template-modal-action.secondary {
        border: 1px solid #dbe3ef;
        background: #ffffff;
        color: #0f172a;
    }

    .aa-template-modal-action.secondary:hover {
        border-color: #8f65df;
        color: #8f65df;
    }

    .aa-template-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 24px;
        flex-wrap: wrap;
    }

    .aa-template-pagination.is-hidden {
        display: none;
    }

    .aa-template-pagination-btn,
    .aa-template-pagination-page {
        display: inline-flex;
        min-width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        color: #334155;
        padding: 0 12px;
        font: inherit;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        transition: .18s ease;
    }

    .aa-template-pagination-btn {
        min-width: 96px;
    }

    .aa-template-pagination-btn:hover,
    .aa-template-pagination-page:hover,
    .aa-template-pagination-page.is-active {
        border-color: #8f65df;
        background: #fff9f5;
        color: #7550c4;
        transform: translateY(-1px);
    }

    .aa-template-pagination-btn:disabled {
        cursor: not-allowed;
        opacity: .48;
        transform: none;
    }

    .aa-template-pagination-pages {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }

    html[data-aa-public-theme="dark"] .aa-template-pagination-btn,
    html[data-aa-public-theme="dark"] .aa-template-pagination-page {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .9);
        color: #d6e0ee;
        box-shadow: 0 16px 38px rgba(0, 0, 0, .22);
    }

    html[data-aa-public-theme="dark"] .aa-template-pagination-btn:hover,
    html[data-aa-public-theme="dark"] .aa-template-pagination-page:hover,
    html[data-aa-public-theme="dark"] .aa-template-pagination-page.is-active {
        border-color: rgba(143, 101, 223, .55);
        background: rgba(143, 101, 223, .12);
        color: #d9ccf4;
    }

    html[data-aa-public-theme="dark"] .aa-template-sidebar-card,
    html[data-aa-public-theme="dark"] .aa-template-search {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .88);
        box-shadow: 0 18px 46px rgba(0, 0, 0, .24);
    }

    html[data-aa-public-theme="dark"] .aa-template-sidebar-head,
    html[data-aa-public-theme="dark"] .aa-template-sidebar-group {
        border-color: rgba(148, 163, 184, .18);
    }

    html[data-aa-public-theme="dark"] .aa-template-sidebar-head strong,
    html[data-aa-public-theme="dark"] .aa-template-sidebar-title {
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-template-sidebar-head span,
    html[data-aa-public-theme="dark"] .aa-template-sidebar-link,
    html[data-aa-public-theme="dark"] .aa-template-search-meta {
        color: #cbd5e1;
    }

    html[data-aa-public-theme="dark"] .aa-template-sidebar-all,
    html[data-aa-public-theme="dark"] .aa-template-sidebar-link:hover,
    html[data-aa-public-theme="dark"] .aa-template-sidebar-link.is-active {
        border-color: rgba(143, 101, 223, .42);
        background: rgba(143, 101, 223, .12);
        color: #d9ccf4;
    }

    html[data-aa-public-theme="dark"] .aa-template-gallery-tab,
    html[data-aa-public-theme="dark"] .aa-template-gallery-mode {
        color: #94a3b8;
    }

    html[data-aa-public-theme="dark"] .aa-template-gallery-tab.is-active {
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-template-gallery-mode.is-active {
        border-color: rgba(143, 101, 223, .35);
        background: rgba(143, 101, 223, .12);
        color: #d9ccf4;
    }

    html[data-aa-public-theme="dark"] .aa-template-gallery-soon {
        border-color: rgba(216, 204, 244, .22);
        background: rgba(143, 101, 223, .22);
        color: #eadfff;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .22);
    }

    html[data-aa-public-theme="dark"] .aa-template-page.aa-app-ui {
        color: #e5edf6;
    }

    html[data-aa-public-theme="dark"] .aa-home-modal-card,
    html[data-aa-public-theme="dark"] .aa-home-modal-card.project-choice,
    html[data-aa-public-theme="dark"] .aa-template-create-dropup,
    html[data-aa-public-theme="dark"] .aa-template-modal-card,
    html[data-aa-public-theme="dark"] .aa-template-modal-preview-light,
    html[data-aa-public-theme="dark"] .aa-template-product-empty,
    html[data-aa-public-theme="dark"] .aa-template-mobile-products,
    html[data-aa-public-theme="dark"] .aa-template-mobile-active-filter,
    html[data-aa-public-theme="dark"] .aa-template-card,
    html[data-aa-public-theme="dark"] .aa-home-empty {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .9);
        color: #e5edf6;
        box-shadow: 0 22px 58px rgba(0, 0, 0, .26);
    }

    html[data-aa-public-theme="dark"] .aa-home-modal-card.project-choice {
        background:
            radial-gradient(circle at 14% 12%, rgba(250, 204, 21, .10), transparent 26%),
            radial-gradient(circle at 82% 18%, rgba(168, 85, 247, .18), transparent 30%),
            rgba(15, 23, 42, .98);
    }

    html[data-aa-public-theme="dark"] .aa-template-project-card,
    html[data-aa-public-theme="dark"] .aa-template-project-card.is-gold,
    html[data-aa-public-theme="dark"] .aa-template-project-card.is-soft,
    html[data-aa-public-theme="dark"] .aa-template-product-card,
    html[data-aa-public-theme="dark"] .aa-template-product-back,
    html[data-aa-public-theme="dark"] .aa-template-search-input,
    html[data-aa-public-theme="dark"] .aa-template-filter-btn,
    html[data-aa-public-theme="dark"] .aa-template-search-chip,
    html[data-aa-public-theme="dark"] .aa-template-search-btn,
    html[data-aa-public-theme="dark"] .aa-template-modal-action.secondary,
    html[data-aa-public-theme="dark"] .aa-home-btn-secondary,
    html[data-aa-public-theme="dark"] .aa-home-modal-close,
    html[data-aa-public-theme="dark"] .aa-template-project-choice-close,
    html[data-aa-public-theme="dark"] .aa-template-create-close {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(30, 41, 59, .82);
        color: #e2e8f0;
        box-shadow: none;
    }

    html[data-aa-public-theme="dark"] .aa-template-project-card:hover,
    html[data-aa-public-theme="dark"] .aa-template-product-card:hover,
    html[data-aa-public-theme="dark"] .aa-template-product-card.is-active,
    html[data-aa-public-theme="dark"] .aa-template-product-back:hover,
    html[data-aa-public-theme="dark"] .aa-template-filter-btn:hover,
    html[data-aa-public-theme="dark"] .aa-template-filter-btn.is-active,
    html[data-aa-public-theme="dark"] .aa-template-search-btn:hover,
    html[data-aa-public-theme="dark"] .aa-template-search-clear:hover,
    html[data-aa-public-theme="dark"] .aa-template-modal-action.secondary:hover,
    html[data-aa-public-theme="dark"] .aa-home-btn-secondary:hover,
    html[data-aa-public-theme="dark"] .aa-home-modal-close:hover,
    html[data-aa-public-theme="dark"] .aa-template-project-choice-close:hover,
    html[data-aa-public-theme="dark"] .aa-template-create-close:hover {
        border-color: rgba(143, 101, 223, .52);
        background: rgba(143, 101, 223, .14);
        color: #d9ccf4;
        box-shadow: 0 16px 38px rgba(0, 0, 0, .20);
    }

    html[data-aa-public-theme="dark"] .aa-template-project-choice-head h3,
    html[data-aa-public-theme="dark"] .aa-template-project-copy h4,
    html[data-aa-public-theme="dark"] .aa-template-product-copy strong,
    html[data-aa-public-theme="dark"] .aa-template-mobile-active-filter em,
    html[data-aa-public-theme="dark"] .aa-template-product-empty h2,
    html[data-aa-public-theme="dark"] .aa-template-create-head h3,
    html[data-aa-public-theme="dark"] .aa-home-modal-head h3,
    html[data-aa-public-theme="dark"] .aa-home-field label,
    html[data-aa-public-theme="dark"] .aa-template-title,
    html[data-aa-public-theme="dark"] .aa-template-modal-placeholder strong,
    html[data-aa-public-theme="dark"] .aa-template-modal-copy h3,
    html[data-aa-public-theme="dark"] #aaTemplatePreviewTitle {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-project-choice-head p,
    html[data-aa-public-theme="dark"] .aa-template-project-copy p,
    html[data-aa-public-theme="dark"] .aa-template-product-copy span,
    html[data-aa-public-theme="dark"] .aa-template-mobile-active-filter strong,
    html[data-aa-public-theme="dark"] .aa-template-product-empty p,
    html[data-aa-public-theme="dark"] .aa-template-create-head p,
    html[data-aa-public-theme="dark"] .aa-home-modal-head p,
    html[data-aa-public-theme="dark"] .aa-template-project-foot,
    html[data-aa-public-theme="dark"] .aa-template-search-clear,
    html[data-aa-public-theme="dark"] .aa-template-description,
    html[data-aa-public-theme="dark"] .aa-template-modal-copy p,
    html[data-aa-public-theme="dark"] .aa-home-template-blank-inner span,
    html[data-aa-public-theme="dark"] .aa-home-empty {
        color: #a8b5c7 !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-project-icon,
    html[data-aa-public-theme="dark"] .aa-template-project-card.is-gold .aa-template-project-icon,
    html[data-aa-public-theme="dark"] .aa-template-project-card.is-soft .aa-template-project-icon,
    html[data-aa-public-theme="dark"] .aa-template-product-arrow,
    html[data-aa-public-theme="dark"] .aa-template-product-empty-icon,
    html[data-aa-public-theme="dark"] .aa-template-filter-icon {
        background: rgba(143, 101, 223, .16);
        color: #d9ccf4;
        box-shadow: inset 0 0 0 1px rgba(216, 204, 244, .18);
    }

    html[data-aa-public-theme="dark"] .aa-template-project-card.is-gold .aa-template-project-icon,
    html[data-aa-public-theme="dark"] .aa-template-project-card.is-gold .aa-template-project-arrow {
        background: rgba(245, 158, 11, .16);
        color: #fcd34d;
    }

    html[data-aa-public-theme="dark"] .aa-template-project-card.is-soft .aa-template-project-icon,
    html[data-aa-public-theme="dark"] .aa-template-project-card.is-soft .aa-template-project-arrow {
        background: rgba(244, 114, 182, .14);
        color: #f9a8d4;
    }

    html[data-aa-public-theme="dark"] .aa-home-modal-head,
    html[data-aa-public-theme="dark"] .aa-template-create-head,
    html[data-aa-public-theme="dark"] .aa-template-form,
    html[data-aa-public-theme="dark"] .aa-template-modal-card > .border-b {
        border-color: rgba(148, 163, 184, .18) !important;
    }

    html[data-aa-public-theme="dark"] .aa-home-field input,
    html[data-aa-public-theme="dark"] .aa-template-search-input {
        background: rgba(2, 6, 23, .56);
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-home-field input::placeholder,
    html[data-aa-public-theme="dark"] .aa-template-search-input::placeholder {
        color: #64748b;
    }

    html[data-aa-public-theme="dark"] .aa-template-thumb,
    html[data-aa-public-theme="dark"] .aa-template-modal-preview,
    html[data-aa-public-theme="dark"] .aa-template-modal-preview img,
    html[data-aa-public-theme="dark"] .aa-template-modal-preview iframe,
    html[data-aa-public-theme="dark"] .aa-template-modal-cover,
    html[data-aa-public-theme="dark"] .aa-home-template-preview {
        border-color: rgba(148, 163, 184, .22);
        background:
            linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(2, 6, 23, .86));
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04);
    }

    html[data-aa-public-theme="dark"] .aa-home-template-blank-preview,
    html[data-aa-public-theme="dark"] .aa-template-blank-preview {
        background:
            linear-gradient(90deg, rgba(143, 101, 223, .12) 1px, transparent 1px),
            linear-gradient(180deg, rgba(143, 101, 223, .12) 1px, transparent 1px),
            radial-gradient(circle at 20% 18%, rgba(143, 101, 223, .16), transparent 34%),
            rgba(15, 23, 42, .92);
    }

    html[data-aa-public-theme="dark"] .aa-home-template-blank-inner,
    html[data-aa-public-theme="dark"] .aa-template-blank-preview-inner {
        border-color: rgba(143, 101, 223, .38);
        background: rgba(30, 41, 59, .72);
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-template-page .aa-template-end-marker {
        color: #64748b;
    }

    html[data-aa-public-theme="dark"] .aa-template-page .aa-template-end-marker::before {
        background: rgba(148, 163, 184, .24);
    }

    html[data-aa-public-theme="dark"] .aa-template-floating-alert {
        border-color: rgba(251, 113, 133, .34);
        background: rgba(76, 5, 25, .92);
        color: #fecdd3;
        box-shadow: 0 22px 60px rgba(0, 0, 0, .30);
    }

    html[data-aa-public-theme="dark"] .aa-template-floating-alert-title {
        color: #ffe4e6;
    }

    html[data-aa-public-theme="dark"] .aa-template-card .bg-white,
    html[data-aa-public-theme="dark"] .aa-template-page .bg-white {
        background-color: rgba(15, 23, 42, .9) !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-page .text-slate-900,
    html[data-aa-public-theme="dark"] .aa-template-page .text-slate-800,
    html[data-aa-public-theme="dark"] .aa-template-page .text-slate-700 {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-page .text-slate-600,
    html[data-aa-public-theme="dark"] .aa-template-page .text-slate-500,
    html[data-aa-public-theme="dark"] .aa-template-page .text-slate-400 {
        color: #a8b5c7 !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-page .border-slate-200,
    html[data-aa-public-theme="dark"] .aa-template-page .border-slate-300 {
        border-color: rgba(148, 163, 184, .24) !important;
    }


    .aa-template-create-dropup {
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

    .aa-template-create-dropup.is-open {
        display: block;
    }

    .aa-template-create-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 18px;
    }

    .aa-template-create-head h3 {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
        letter-spacing: -.025em;
    }

    .aa-template-create-head p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
    }

    .aa-template-create-close {
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

    .aa-template-create-form {
        display: grid;
        gap: 12px;
        padding: 18px;
    }

    @media (max-width: 1080px) {
        .aa-template-project-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-template-project-card,
        .aa-template-project-card.is-lower-start {
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

        .aa-template-project-choice {
            padding: 28px 16px 24px;
        }

        .aa-template-project-choice-head {
            margin-bottom: 22px;
            padding: 0 42px;
        }

        .aa-template-project-choice-head h3 {
            font-size: 30px;
        }

        .aa-template-project-choice-head p {
            font-size: 15px;
        }

        .aa-template-project-choice-close {
            top: -6px;
            width: 42px;
            height: 42px;
        }

        .aa-template-project-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .aa-template-project-card {
            grid-template-columns: 64px minmax(0, 1fr) 40px;
            min-height: 136px;
            gap: 12px;
            padding: 16px 12px;
        }

        .aa-template-project-icon {
            width: 58px;
            height: 58px;
        }

        .aa-template-project-icon svg {
            width: 30px;
            height: 30px;
        }

        .aa-template-project-copy h4 {
            margin-bottom: 7px;
            font-size: 18px;
        }

        .aa-template-project-copy p {
            font-size: 13px;
            line-height: 1.45;
        }

        .aa-template-project-arrow {
            width: 38px;
            height: 38px;
        }

        .aa-template-project-arrow svg {
            width: 23px;
            height: 23px;
        }

        .aa-template-project-foot {
            margin-top: 22px;
            font-size: 13px;
        }

        .aa-template-create-dropup {
            right: 12px;
            left: 12px;
            bottom: 12px;
            width: auto;
            max-height: calc(100vh - 24px);
            overflow: auto;
        }

        .aa-template-create-form .aa-home-modal-actions {
            display: grid;
            justify-content: stretch;
        }
    }

    @media (max-width: 720px) {
        .aa-template-modal-card {
            max-height: calc(100vh - 28px);
        }

        .aa-template-modal-preview {
            max-height: none;
        }

        .aa-template-modal-preview-light {
            grid-template-columns: 1fr;
            min-height: auto;
            padding: 16px;
        }

        .aa-template-modal-cover {
            width: min(100%, 280px);
            max-height: 44vh;
            margin: 0 auto;
        }

        .aa-template-modal-actions {
            display: grid;
        }
    }

    .aa-template-blank-preview {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        background:
            linear-gradient(90deg, rgba(143, 101, 223, .12) 1px, transparent 1px),
            linear-gradient(180deg, rgba(143, 101, 223, .12) 1px, transparent 1px),
            linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        background-size: 28px 28px, 28px 28px, auto;
    }

    .aa-template-blank-preview-inner {
        display: grid;
        gap: 9px;
        place-items: center;
        border: 1px dashed #d9ccf4;
        border-radius: 18px;
        background: rgba(255, 255, 255, .88);
        padding: 18px 14px;
        text-align: center;
        box-shadow: 0 18px 44px rgba(15, 23, 42, .08);
    }

    .aa-template-floating-alert {
        position: fixed;
        top: calc(88px + env(safe-area-inset-top));
        right: calc(22px + env(safe-area-inset-right));
        z-index: 80;
        width: min(430px, calc(100vw - 32px));
        border: 1px solid #fecdd3;
        border-radius: 18px;
        background: rgba(255, 241, 242, .96);
        color: #be123c;
        padding: 14px 16px;
        box-shadow: 0 22px 60px rgba(136, 19, 55, .18), 0 10px 24px rgba(15, 23, 42, .08);
        backdrop-filter: blur(10px);
    }

    .aa-template-floating-alert-title {
        margin: 0 0 6px;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.4;
    }

    .aa-template-floating-alert-list {
        margin: 0;
        padding-left: 18px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
    }

    @media (max-width: 720px) {
        .aa-template-floating-alert {
            top: calc(76px + env(safe-area-inset-top));
            right: calc(12px + env(safe-area-inset-right));
            left: calc(12px + env(safe-area-inset-left));
            width: auto;
        }
    }

    html[data-aa-public-theme="dark"] .aa-template-create-dropup,
    html[data-aa-public-theme="dark"] .aa-template-blank-preview-inner {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .96);
        color: #e5edf6;
        box-shadow: 0 24px 64px rgba(0, 0, 0, .32);
    }

    html[data-aa-public-theme="dark"] .aa-template-create-head,
    html[data-aa-public-theme="dark"] .aa-template-create-form {
        border-color: rgba(148, 163, 184, .18);
        background: transparent;
    }

    html[data-aa-public-theme="dark"] .aa-template-create-head h3 {
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-template-create-head p {
        color: #a8b5c7;
    }

    html[data-aa-public-theme="dark"] .aa-template-create-close {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(30, 41, 59, .82);
        color: #e2e8f0;
    }

    html[data-aa-public-theme="dark"] .aa-template-blank-preview {
        background:
            linear-gradient(90deg, rgba(143, 101, 223, .12) 1px, transparent 1px),
            linear-gradient(180deg, rgba(143, 101, 223, .12) 1px, transparent 1px),
            radial-gradient(circle at 20% 18%, rgba(143, 101, 223, .16), transparent 34%),
            rgba(15, 23, 42, .94);
    }

    html[data-aa-public-theme="dark"] .aa-template-floating-alert {
        border-color: rgba(251, 113, 133, .34);
        background: rgba(76, 5, 25, .94);
        color: #fecdd3;
        box-shadow: 0 22px 60px rgba(0, 0, 0, .32);
    }

    html[data-aa-public-theme="dark"] .aa-template-floating-alert-title {
        color: #ffe4e6;
    }
    </style>
</head>

<body class="aa-app-ui aa-public-theme-page aa-template-page flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased">
    <?= view('components/public_site_header', ['active' => 'templates']) ?>

    <main class="aa-template-shell mx-auto w-full flex-1 px-4 py-8 sm:px-6">
        <div class="mb-5">
            <?= view('components/breadcrumb_pill', [
                'items' => $isSubcategoryFilterActive
                    ? [
                        ['label' => 'Home', 'url' => site_url('/')],
                        ['label' => 'Template', 'url' => site_url('templates')],
                        ['label' => (string) ($selectedSubcategory['name'] ?? 'Subkategori')],
                    ]
                    : ($searchQuery !== ''
                    ? [
                        ['label' => 'Home', 'url' => site_url('/')],
                        ['label' => 'Template', 'url' => site_url('templates')],
                        ['label' => 'Search'],
                    ]
                    : [
                        ['label' => 'Home', 'url' => site_url('/')],
                        ['label' => 'Template'],
                    ]),
            ]) ?>
        </div>

        <?php $templateErrors = session()->getFlashdata('errors'); ?>
        <?php if ($templateErrors): ?>
        <div class="aa-template-floating-alert" role="alert" aria-live="assertive">
            <p class="aa-template-floating-alert-title">Template belum bisa dibuat</p>
            <ul class="aa-template-floating-alert-list">
                <?php foreach ((array) $templateErrors as $error): ?>
                <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
        <?php endif ?>

        <div class="aa-template-market-layout">
            <aside class="aa-template-sidebar" aria-label="Kategori template">
                <nav class="aa-template-sidebar-card">
                    <div class="aa-template-sidebar-scroll" data-lenis-prevent data-lenis-prevent-wheel data-template-sidebar-scroll>
                        <?php if ($isInvitationProduct): ?>
                            <a class="aa-template-product-back" href="<?= site_url('templates') ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
                                <span>Kembali ke pilihan utama</span>
                            </a>
                            <a class="aa-template-sidebar-all <?= $activeSubcategorySlug === '' && $searchQuery === '' ? 'is-active' : '' ?>" href="<?= site_url('templates') ?>?type=invitation">
                                <?= $categoryIcon('all') ?>
                                <span>Semua Undangan</span>
                            </a>
                            <?php foreach ($templateSidebarGroups as $group): ?>
                                <?php if (($group['items'] ?? []) === []) continue; ?>
                                <section class="aa-template-sidebar-group">
                                    <div class="aa-template-sidebar-title">
                                        <?= $categoryIcon((string) ($group['icon'] ?? 'lainnya')) ?>
                                        <span><?= esc((string) ($group['label'] ?? 'Template')) ?></span>
                                    </div>
                                    <div class="aa-template-sidebar-links">
                                        <?php foreach ($group['items'] as $item): ?>
                                            <a class="aa-template-sidebar-link <?= ! empty($item['active']) ? 'is-active' : '' ?>" href="<?= esc((string) ($item['url'] ?? site_url('templates')), 'attr') ?>">
                                                <span><?= esc((string) ($item['label'] ?? 'Template')) ?></span>
                                            </a>
                                        <?php endforeach ?>
                                    </div>
                                </section>
                            <?php endforeach ?>
                        <?php elseif ($isBusinessProfileProduct): ?>
                            <a class="aa-template-product-back" href="<?= site_url('templates') ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
                                <span>Kembali ke pilihan utama</span>
                            </a>
                            <section class="aa-template-sidebar-group">
                                <div class="aa-template-sidebar-title">
                                    <?= $categoryIcon('corporate') ?>
                                    <span>Kategori Bisnis</span>
                                </div>
                                <div class="aa-template-sidebar-links">
                                    <?php foreach ($businessProfileSubcategories as $businessSubcategory): ?>
                                        <a class="aa-template-sidebar-link <?= $businessSubcategorySlug === $businessSubcategory['slug'] ? 'is-active' : '' ?>" href="<?= site_url('templates') ?>?type=business-profile&business_category=<?= rawurlencode((string) $businessSubcategory['slug']) ?>">
                                            <span><?= esc((string) $businessSubcategory['label']) ?></span>
                                        </a>
                                    <?php endforeach ?>
                                </div>
                            </section>
                        <?php else: ?>
                            <div class="aa-template-product-list">
                                <?php foreach ($productTemplateTypes as $productKey => $product): ?>
                                    <?php $productIsDisabled = ! empty($product['disabled']); ?>
                                    <?php $productCreateIntent = (string) ($product['create'] ?? ''); ?>
                                    <?php $productShouldOpenPhotobooth = ! $productIsDisabled && $productCreateIntent === '1' && $isLoggedIn; ?>
                                    <?php $productShouldOpenBusiness = false; ?>
                                    <?php if ($productIsDisabled): ?>
                                    <div class="aa-template-product-card is-disabled" aria-disabled="true">
                                    <?php else: ?>
                                    <a class="aa-template-product-card <?= $activeProductType === $productKey ? 'is-active' : '' ?>" href="<?= esc((string) $product['url'], 'attr') ?>" <?= $productShouldOpenPhotobooth ? 'data-template-open-photobooth-create' : '' ?> <?= $productShouldOpenBusiness ? 'data-template-open-business-create' : '' ?>>
                                    <?php endif ?>
                                        <?= $categoryIcon((string) $product['icon']) ?>
                                        <span class="aa-template-product-copy">
                                            <strong><?= esc((string) $product['label']) ?></strong>
                                            <span><?= esc((string) $product['description']) ?></span>
                                            <?php if ($productIsDisabled): ?>
                                                <span class="aa-template-soon-pill">Soon</span>
                                            <?php endif ?>
                                        </span>
                                        <span class="aa-template-product-arrow" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                        </span>
                                    <?= $productIsDisabled ? '</div>' : '</a>' ?>
                                <?php endforeach ?>
                            </div>
                        <?php endif ?>
                    </div>
                </nav>
            </aside>

            <div class="aa-template-content">
                <section class="aa-template-mobile-products" aria-label="Jenis template">
                    <?php if ($isInvitationProduct): ?>
                        <a class="aa-template-product-back" href="<?= site_url('templates') ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
                            <span>Kembali ke pilihan utama</span>
                        </a>
                        <?php if ($isMobileInvitationSelectionActive): ?>
                            <div class="aa-template-mobile-active-filter">
                                <?= $categoryIcon($isSubcategoryFilterActive ? 'wedding' : 'all') ?>
                                <span>
                                    <strong><?= $isSubcategoryFilterActive ? 'Subkategori aktif' : 'Search aktif' ?></strong>
                                    <em><?= esc($isSubcategoryFilterActive ? (string) ($selectedSubcategory['name'] ?? $searchQuery) : $searchQuery) ?></em>
                                </span>
                            </div>
                            <a class="aa-template-sidebar-all" href="<?= site_url('templates') ?>?type=invitation">
                                <?= $categoryIcon('all') ?>
                                <span>Ganti kategori</span>
                            </a>
                        <?php else: ?>
                            <a class="aa-template-sidebar-all <?= $activeSubcategorySlug === '' && $searchQuery === '' ? 'is-active' : '' ?>" href="<?= site_url('templates') ?>?type=invitation">
                                <?= $categoryIcon('all') ?>
                                <span>Semua Undangan</span>
                            </a>
                            <?php foreach ($templateSidebarGroups as $group): ?>
                                <?php if (($group['items'] ?? []) === []) continue; ?>
                                <section class="aa-template-sidebar-group">
                                    <div class="aa-template-sidebar-title">
                                        <?= $categoryIcon((string) ($group['icon'] ?? 'lainnya')) ?>
                                        <span><?= esc((string) ($group['label'] ?? 'Template')) ?></span>
                                    </div>
                                    <div class="aa-template-sidebar-links">
                                        <?php foreach ($group['items'] as $item): ?>
                                            <a class="aa-template-sidebar-link <?= ! empty($item['active']) ? 'is-active' : '' ?>" href="<?= esc((string) ($item['url'] ?? site_url('templates')), 'attr') ?>">
                                                <span><?= esc((string) ($item['label'] ?? 'Template')) ?></span>
                                            </a>
                                        <?php endforeach ?>
                                    </div>
                                </section>
                            <?php endforeach ?>
                        <?php endif ?>
                    <?php elseif ($isBusinessProfileProduct): ?>
                        <a class="aa-template-product-back" href="<?= site_url('templates') ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>
                            <span>Kembali ke pilihan utama</span>
                        </a>
                        <?php if ($selectedBusinessSubcategory !== null): ?>
                            <div class="aa-template-mobile-active-filter">
                                <?= $categoryIcon('corporate') ?>
                                <span>
                                    <strong>Kategori aktif</strong>
                                    <em><?= esc((string) $selectedBusinessSubcategory['label']) ?></em>
                                </span>
                            </div>
                            <a class="aa-template-sidebar-all" href="<?= site_url('templates') ?>?type=business-profile">
                                <?= $categoryIcon('corporate') ?>
                                <span>Ganti kategori</span>
                            </a>
                        <?php else: ?>
                            <section class="aa-template-sidebar-group">
                                <div class="aa-template-sidebar-title">
                                    <?= $categoryIcon('corporate') ?>
                                    <span>Kategori Bisnis</span>
                                </div>
                                <div class="aa-template-sidebar-links">
                                    <?php foreach ($businessProfileSubcategories as $businessSubcategory): ?>
                                        <a class="aa-template-sidebar-link" href="<?= site_url('templates') ?>?type=business-profile&business_category=<?= rawurlencode((string) $businessSubcategory['slug']) ?>">
                                            <span><?= esc((string) $businessSubcategory['label']) ?></span>
                                        </a>
                                    <?php endforeach ?>
                                </div>
                            </section>
                        <?php endif ?>
                    <?php else: ?>
                        <div class="aa-template-product-list">
                            <?php foreach ($productTemplateTypes as $productKey => $product): ?>
                                    <?php $productIsDisabled = ! empty($product['disabled']); ?>
                                    <?php $productCreateIntent = (string) ($product['create'] ?? ''); ?>
                                    <?php $productShouldOpenPhotobooth = ! $productIsDisabled && $productCreateIntent === '1' && $isLoggedIn; ?>
                                <?php $productShouldOpenBusiness = false; ?>
                                <?php if ($productIsDisabled): ?>
                                <div class="aa-template-product-card is-disabled" aria-disabled="true">
                                <?php else: ?>
                                <a class="aa-template-product-card <?= $activeProductType === $productKey ? 'is-active' : '' ?>" href="<?= esc((string) $product['url'], 'attr') ?>" <?= $productShouldOpenPhotobooth ? 'data-template-open-photobooth-create' : '' ?> <?= $productShouldOpenBusiness ? 'data-template-open-business-create' : '' ?>>
                                <?php endif ?>
                                    <?= $categoryIcon((string) $product['icon']) ?>
                                    <span class="aa-template-product-copy">
                                        <strong><?= esc((string) $product['label']) ?></strong>
                                        <span><?= esc((string) $product['description']) ?></span>
                                        <?php if ($productIsDisabled): ?>
                                            <span class="aa-template-soon-pill">Soon</span>
                                        <?php endif ?>
                                    </span>
                                    <span class="aa-template-product-arrow" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                    </span>
                                <?= $productIsDisabled ? '</div>' : '</a>' ?>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                </section>

                <?php if ($isBusinessProfileProduct): ?>
                    <?php if ($selectedBusinessSubcategory === null): ?>
                    <section class="aa-template-product-empty">
                        <div class="aa-template-product-empty-inner">
                            <span class="aa-template-product-empty-icon" aria-hidden="true">
                                <?= $categoryIcon('corporate') ?>
                            </span>
                            <h2>Pilih kategori Business Profile yang ingin kamu buat.</h2>
                            <p>Pilih kategori yang sesuai dengan project bisnis agar template yang disiapkan admin lebih mudah ditemukan.</p>
                        </div>
                    </section>
                    <?php else: ?>
                    <section class="aa-template-search">
                        <div class="aa-template-search-meta">
                            <span>Kategori aktif:</span>
                            <span class="aa-template-search-chip"><?= esc((string) $selectedBusinessSubcategory['label']) ?></span>
                            <a class="aa-template-search-clear" href="<?= site_url('templates') ?>?type=business-profile">Ganti kategori</a>
                        </div>
                    </section>

                    <section class="aa-template-grid" data-template-grid>
                        <article class="aa-template-blank-card" data-template-card data-template-category="all" data-template-global="true">
                            <a class="aa-home-template-preview" href="<?= esc($blankTemplateUrl, 'attr') ?>"
                                <?= $isLoggedIn ? 'data-template-open-business-create' : '' ?>
                                data-business-category="<?= esc((string) $selectedBusinessSubcategory['label'], 'attr') ?>"
                                data-business-category-slug="<?= esc((string) $selectedBusinessSubcategory['slug'], 'attr') ?>"
                                aria-label="Pakai blank template Business Profile">
                                <div class="aa-home-template-blank-preview">
                                    <div class="aa-home-template-blank-inner">
                                        <span class="aa-home-template-blank-plus">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="5"
                                                stroke="currentColor"
                                                class="h-5 w-5">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M12 4.5v15M4.5 12h15"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <strong>Blank Business Profile</strong>
                                            <span><?= $isLoggedIn ? 'Mulai website ' . esc((string) $selectedBusinessSubcategory['label']) . ' dari canvas kosong.' : 'Login untuk mulai blank.' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </article>
                        <?php foreach ($businessProfileTemplateMatches as $template): ?>
                        <?php
                            [$categoryKey, $categoryLabel] = $normalizeCategory($template);
                            $isPremium = (int) ($template['is_premium'] ?? 0) === 1;
                            $previewUrl = $templatePreviewUrl($template);
                            $previewSrc = ! empty($template['thumbnail']) ? base_url($template['thumbnail']) : '';
                            $templateId = (int) ($template['id'] ?? 0);
                            $isWishlisted = $templateId > 0 && in_array($templateId, $wishlistTemplateIds, true);
                        ?>
                        <article class="" data-template-card data-template-category="<?= esc($categoryKey, 'attr') ?>">
                            <div class="aa-template-thumb aa-img-wrap aa-ratio-preview"
                                role="button"
                                tabindex="0"
                                aria-label="Preview <?= esc($template['name'], 'attr') ?>"
                                data-preview-open
                                data-preview-title="<?= esc($template['name'], 'attr') ?>"
                                data-preview-category="<?= esc((string) $selectedBusinessSubcategory['label'], 'attr') ?>"
                                data-preview-project-type="business_profile"
                                data-preview-business-category="<?= esc((string) $selectedBusinessSubcategory['label'], 'attr') ?>"
                                data-preview-business-category-slug="<?= esc((string) $selectedBusinessSubcategory['slug'], 'attr') ?>"
                                data-preview-id="<?= esc((string) $templateId, 'attr') ?>"
                                data-preview-mode="url"
                                data-preview-url="<?= esc($previewUrl, 'attr') ?>"
                                data-preview-src="<?= esc($previewSrc, 'attr') ?>">
                                <?php if ($isPremium): ?>
                                    <?= $premiumCrownSvg ?>
                                <?php endif ?>
                                <?php if (! empty($template['thumbnail'])): ?>
                                <img class="aa-lazy-img" src="<?= esc($previewSrc, 'attr') ?>" alt="<?= esc($template['name'], 'attr') ?>"
                                    loading="lazy" decoding="async">
                                <?php else: ?>
                                <iframe title="<?= esc($template['name']) ?> preview" src="<?= esc($previewUrl, 'attr') ?>"
                                    loading="lazy"></iframe>
                                <?php endif ?>
                                <div class="aa-template-thumb-actions" aria-label="Aksi template">
                                    <button class="aa-template-thumb-action preview" type="button" data-template-action-preview>
                                        <span>Preview</span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                                    </button>
                                    <button class="aa-template-thumb-action love<?= $isWishlisted ? ' is-active' : '' ?>" type="button" data-template-wishlist-toggle data-template-id="<?= esc((string) $templateId, 'attr') ?>" aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>" aria-label="<?= $isWishlisted ? 'Hapus dari wishlist' : 'Simpan ke wishlist' ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </article>
                        <?php endforeach ?>
                    </section>
                    <?php if ($businessProfileTemplateMatches === []): ?>
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm font-semibold text-slate-600">
                        Belum ada template Business Profile pada kategori ini. Kamu tetap bisa mulai dari blank template.
                    </div>
                    <?php endif ?>
                    <?php endif ?>
                <?php elseif (! $isInvitationProduct): ?>
                    <?php
                        $emptyProduct = $productTemplateTypes[$activeProductType] ?? null;
                        $emptyTitle = $isProductLanding ? 'Pilih jenis template yang ingin kamu gunakan.' : 'Template ' . (string) ($emptyProduct['label'] ?? 'ini') . ' belum tersedia.';
                        $emptyCopy = $isProductLanding
                            ? 'Mulai dari Undangan Digital, Digital Photobooth, atau Business Profile. Template khusus Photobooth dan Business Profile akan disiapkan bertahap.'
                            : 'Kategori ini sedang disiapkan. Untuk sekarang, template yang tersedia masih berada di Undangan Digital.';
                    ?>
                    <section class="aa-template-product-empty">
                        <div class="aa-template-product-empty-inner">
                            <span class="aa-template-product-empty-icon" aria-hidden="true">
                                <?= $categoryIcon((string) (($emptyProduct['icon'] ?? 'all'))) ?>
                            </span>
                            <h2><?= esc($emptyTitle) ?></h2>
                            <p><?= esc($emptyCopy) ?></p>
                        </div>
                    </section>
                <?php else: ?>
                <section class="aa-template-search">
                    <form class="aa-template-search-form" action="<?= site_url('templates') ?>" method="get" role="search">
                        <input type="hidden" name="type" value="invitation">
                        <input class="aa-template-search-input" type="search" name="q" value="<?= esc($searchQuery, 'attr') ?>" maxlength="80" placeholder="Cari: pernikahan floral, ulang tahun anak, premium elegan">
                        <button class="aa-template-search-btn" type="submit">Cari Template</button>
                    </form>
                    <div class="aa-template-search-meta">
                        <?php if ($isSubcategoryFilterActive): ?>
                            <span>Subkategori aktif:</span>
                            <span class="aa-template-search-chip"><?= esc((string) ($selectedSubcategory['name'] ?? $searchQuery)) ?></span>
                            <a class="aa-template-search-clear" href="<?= site_url('templates') ?>?type=invitation">Reset</a>
                        <?php elseif ($searchQuery !== ''): ?>
                            <span>Search aktif:</span>
                            <span class="aa-template-search-chip"><?= esc($searchQuery) ?></span>
                            <a class="aa-template-search-clear" href="<?= site_url('templates') ?>?type=invitation">Reset</a>
                        <?php else: ?>
                            <span>Coba:</span>
                            <a class="aa-template-search-chip" href="<?= site_url('templates') ?>?type=invitation&q=pernikahan%20floral">pernikahan floral</a>
                            <a class="aa-template-search-chip" href="<?= site_url('templates') ?>?type=invitation&q=ulang%20tahun%20anak">ulang tahun anak</a>
                            <a class="aa-template-search-chip" href="<?= site_url('templates') ?>?type=invitation&q=premium%20elegan">premium elegan</a>
                        <?php endif ?>
                    </div>
                </section>

                <?php if ($templates === []): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-600">
                    <?= $searchQuery !== '' ? 'Belum ada template yang cocok. Coba keyword lain seperti pernikahan floral, ulang tahun anak, atau premium elegan.' : 'Belum ada template aktif. Tambahkan template aktif ke database terlebih dahulu.' ?>
                </div>
                <?php else: ?>
                <section class="aa-template-inline-filter mb-5">
                    <div class="aa-template-filter" data-template-filter>
                        <button class="aa-template-filter-btn is-active" type="button" data-category-filter="all">
                            <?= $categoryIcon('all') ?>
                            <span class="aa-template-filter-label">Semua</span>
                        </button>
                    </div>
                </section>

                <div class="aa-template-gallery-nav" aria-label="Pilihan tampilan template">
                    <div class="aa-template-gallery-tabs">
                        <button class="aa-template-gallery-tab is-active" type="button">Top Day</button>
                        <button class="aa-template-gallery-tab" type="button">Likes</button>
                    </div>
                    <div class="aa-template-gallery-modes">
                        <button class="aa-template-gallery-mode is-active" type="button">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
                            <span>Website</span>
                        </button>
                        <button class="aa-template-gallery-mode" type="button">
                            <span class="aa-template-gallery-soon">Segera</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"/><path d="m4 16 4.5-4.5 3.5 3.5 2-2L20 19"/><circle cx="9" cy="10" r="1.5"/></svg>
                            <span>Images</span>
                        </button>
                        <button class="aa-template-gallery-mode" type="button">
                            <span class="aa-template-gallery-soon">Segera</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"/><path d="m10 9 5 3-5 3V9Z"/></svg>
                            <span>Video</span>
                        </button>
                    </div>
                </div>

                <section class="aa-template-grid" data-template-grid>
            <?php if ($searchQuery === ''): ?>
            <article class="aa-template-blank-card" data-template-card data-template-category="all"
                data-template-global="true">
                <a class="aa-home-template-preview" href="<?= esc($blankTemplateUrl, 'attr') ?>"
                            <?= $isLoggedIn ? 'data-home-open-create' : '' ?> aria-label="Pakai blank template">
                            <div class="aa-home-template-blank-preview">
                                <div class="aa-home-template-blank-inner">
                                    <span class="aa-home-template-blank-plus">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="5"
                                            stroke="currentColor"
                                            class="h-5 w-5">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 4.5v15M4.5 12h15"/>
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
            <?php endif ?>

            <?php foreach ($templates as $template): ?>
            <?php
                        [$categoryKey, $categoryLabel] = $normalizeCategory($template);
                        $isPremium = (int) ($template['is_premium'] ?? 0) === 1;
                        $previewUrl = $templatePreviewUrl($template);
                        $previewSrc = ! empty($template['thumbnail']) ? base_url($template['thumbnail']) : '';
                        $templateId = (int) ($template['id'] ?? 0);
                        $isWishlisted = $templateId > 0 && in_array($templateId, $wishlistTemplateIds, true);
                        ?>
            <article class="" data-template-card
                data-template-category="<?= esc($categoryKey, 'attr') ?>">
                <div class="aa-template-thumb aa-img-wrap aa-ratio-preview"
                    role="button"
                    tabindex="0"
                    aria-label="Preview <?= esc($template['name'], 'attr') ?>"
                    data-preview-open
                    data-preview-title="<?= esc($template['name'], 'attr') ?>"
                    data-preview-category="<?= esc($categoryLabel, 'attr') ?>"
                    data-preview-project-type="invitation"
                    data-preview-id="<?= esc((string) $templateId, 'attr') ?>"
                    data-preview-mode="url"
                    data-preview-url="<?= esc($previewUrl, 'attr') ?>"
                    data-preview-src="<?= esc($previewSrc, 'attr') ?>">
                    <?php if ($isPremium): ?>
                        <?= $premiumCrownSvg ?>
                    <?php endif ?>
                    <?php if (! empty($template['thumbnail'])): ?>
                    <img class="aa-lazy-img" src="<?= esc($previewSrc, 'attr') ?>" alt="<?= esc($template['name'], 'attr') ?>"
                        loading="lazy" decoding="async">
                    <?php else: ?>
                    <iframe title="<?= esc($template['name']) ?> preview" src="<?= esc($previewUrl, 'attr') ?>"
                        loading="lazy"></iframe>
                    <?php endif ?>
                    <div class="aa-template-thumb-actions" aria-label="Aksi template">
                        <button class="aa-template-thumb-action preview" type="button" data-template-action-preview>
                            <span>Preview</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </button>
                        <button class="aa-template-thumb-action love<?= $isWishlisted ? ' is-active' : '' ?>" type="button" data-template-wishlist-toggle data-template-id="<?= esc((string) $templateId, 'attr') ?>" aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>" aria-label="<?= $isWishlisted ? 'Hapus dari wishlist' : 'Simpan ke wishlist' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach ?>
                </section>
                <div class="aa-template-end-marker" data-template-end-marker>Itu semua yang tersedia</div>

                <div class="mt-6 hidden rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm font-semibold text-slate-600"
                    data-template-empty>
                    Belum ada template pada kategori ini.
                </div>
                <?php endif ?>
                <?php endif ?>
            </div>
        </div>
    </main>

    <div id="aaTemplateProjectChoiceModal" class="aa-home-modal" aria-hidden="true">
        <div class="aa-home-modal-backdrop" data-template-project-choice-close></div>
        <div class="aa-home-modal-card project-choice" role="dialog" aria-modal="true" aria-labelledby="aaTemplateProjectChoiceTitle" data-lenis-prevent data-lenis-prevent-wheel>
            <div class="aa-template-project-choice">
                <div class="aa-template-project-choice-head">
                    <span class="aa-template-project-choice-spark" aria-hidden="true">✦<i></i></span>
                    <h3 id="aaTemplateProjectChoiceTitle">Apa yang ingin kamu buat?</h3>
                    <p>Pilih jenis proyek yang ingin kamu mulai.</p>
                    <button class="aa-template-project-choice-close" type="button" data-template-project-choice-close aria-label="Tutup">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <div class="aa-template-project-grid">
                    <a class="aa-template-project-card" href="<?= esc($blankTemplateUrl, 'attr') ?>" <?= $isLoggedIn ? 'data-template-project-create' : '' ?>>
                        <span class="aa-template-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><rect x="13" y="15" width="38" height="34" rx="8" fill="currentColor" opacity=".16"/><path d="M20 25h24M20 37h14" stroke="currentColor" stroke-width="4" stroke-linecap="round"/><path d="M42 34c3-4 9-2 9 3 0 6-9 11-9 11s-9-5-9-11c0-5 6-7 9-3Z" fill="currentColor"/></svg>
                        </span>
                        <span class="aa-template-project-copy">
                            <h4>Undangan Digital</h4>
                            <p>Buat website undangan interaktif.</p>
                        </span>
                        <span class="aa-template-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a class="aa-template-project-card is-gold" href="<?= esc($blankTemplateUrl, 'attr') ?>" <?= $isLoggedIn ? 'data-template-open-photobooth-create' : '' ?>>
                        <span class="aa-template-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><rect x="14" y="21" width="36" height="26" rx="8" fill="currentColor" opacity=".18"/><path d="M24 21l4-6h12l4 6" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="32" cy="34" r="9" fill="currentColor" opacity=".24"/><circle cx="32" cy="34" r="5" fill="currentColor"/><rect x="43" y="36" width="12" height="12" rx="3" fill="currentColor" opacity=".45"/></svg>
                        </span>
                        <span class="aa-template-project-copy">
                            <h4>Digital Photobooth</h4>
                            <p>Tamu foto dari HP, hasil bisa download atau dicetak.</p>
                        </span>
                        <span class="aa-template-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a class="aa-template-project-card" href="<?= site_url('templates') ?>?type=business-profile">
                        <span class="aa-template-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><rect x="13" y="16" width="38" height="32" rx="8" fill="currentColor" opacity=".16"/><circle cx="27" cy="31" r="6" fill="currentColor"/><path d="M21 43c2-5 10-5 12 0M39 27h7M39 36h7" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
                        </span>
                        <span class="aa-template-project-copy">
                            <h4>Business Profile</h4>
                            <p>Website profile untuk MUA, WO, vendor, atau freelancer.</p>
                        </span>
                        <span class="aa-template-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a class="aa-template-project-card is-gold is-lower-start" href="<?= esc($creatorApplyUrl, 'attr') ?>">
                        <span class="aa-template-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><path d="m18 45 4-24 10 11 10-11 4 24H18Z" fill="currentColor" opacity=".2"/><path d="m18 45 4-24 10 11 10-11 4 24H18Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><path d="M24 50h16" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
                        </span>
                        <span class="aa-template-project-copy">
                            <h4>Creator</h4>
                            <p>Buat template dan dapat penghasilan.</p>
                        </span>
                        <span class="aa-template-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>

                    <a class="aa-template-project-card is-soft" href="<?= site_url('plans') ?>">
                        <span class="aa-template-project-icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><path d="M15 28h34v22H15V28Z" fill="currentColor" opacity=".16"/><path d="M14 24h36l-4-10H18l-4 10Z" fill="currentColor" opacity=".26"/><path d="M15 28h34v22H15V28Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><circle cx="48" cy="47" r="8" fill="currentColor" opacity=".35"/><path d="m48 42 1.3 3.2 3.4.3-2.6 2.2.8 3.3-2.9-1.8-2.9 1.8.8-3.3-2.6-2.2 3.4-.3L48 42Z" fill="currentColor"/></svg>
                        </span>
                        <span class="aa-template-project-copy">
                            <h4>Untuk Bisnis</h4>
                            <p>Untuk jual undangan digital atau bikin photobooth sendiri pakai sistem adaAcara tinggal siapkan tempat cetak.</p>
                        </span>
                        <span class="aa-template-project-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </a>
                </div>

                <div class="aa-template-project-foot">
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
                <button class="aa-home-modal-close" type="button" data-home-modal-close
                    aria-label="Tutup">⛌</button>
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

    <div id="aaTemplatePhotoboothCreateModal" class="aa-home-modal" aria-hidden="true">
        <div class="aa-home-modal-backdrop" data-template-photobooth-close></div>
        <div class="aa-home-modal-card" role="dialog" aria-modal="true" aria-labelledby="aaTemplatePhotoboothCreateTitle">
            <div class="aa-home-modal-head">
                <div>
                    <h3 id="aaTemplatePhotoboothCreateTitle">Buat Photobooth Baru</h3>
                    <p>Isi detail dasar dulu. Setelah itu kamu masuk Studio untuk mulai menyiapkan desain frame.</p>
                </div>
                <button class="aa-home-modal-close" type="button" data-template-photobooth-close
                    aria-label="Tutup">⛌</button>
            </div>
            <div class="aa-home-modal-body">
                <form class="aa-home-create-form" action="<?= site_url('templates/create') ?>" method="post">
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <input type="hidden" name="blank_template" value="1">
                    <input type="hidden" name="project_intent" value="photobooth">
                    <div class="aa-home-field">
                        <label for="aaTemplatePhotoboothTitle">Nama Photobooth / Nama Acara</label>
                        <input id="aaTemplatePhotoboothTitle" name="title" type="text"
                            placeholder="Contoh: Photobooth Sarah & Dimas" required>
                    </div>
                    <div class="aa-home-field">
                        <label for="aaTemplatePhotoboothSlug">Slug URL</label>
                        <input id="aaTemplatePhotoboothSlug" name="slug" type="text" placeholder="contoh: photobooth-sarah-dimas">
                    </div>
                    <div class="aa-home-field">
                        <label for="aaTemplatePhotoboothDate">Tanggal Acara</label>
                        <input id="aaTemplatePhotoboothDate" name="event_date" type="date">
                    </div>
                    <div class="aa-home-modal-actions">
                        <button class="aa-home-btn aa-home-btn-secondary" type="button"
                            data-template-photobooth-close>Batal</button>
                        <button class="aa-home-btn aa-home-btn-primary" type="submit">Buat Photobooth</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="aaTemplateBusinessCreateModal" class="aa-home-modal" aria-hidden="true">
        <div class="aa-home-modal-backdrop" data-template-business-close></div>
        <div class="aa-home-modal-card" role="dialog" aria-modal="true" aria-labelledby="aaTemplateBusinessCreateTitle">
            <div class="aa-home-modal-head">
                <div>
                    <h3 id="aaTemplateBusinessCreateTitle">Buat Business Profile Baru</h3>
                    <p id="aaTemplateBusinessCreateCopy">Isi detail dasar dulu. Setelah itu kamu masuk Studio untuk mulai menyiapkan website profile bisnis.</p>
                </div>
                <button class="aa-home-modal-close" type="button" data-template-business-close
                    aria-label="Tutup">⛌</button>
            </div>
            <div class="aa-home-modal-body">
                <form class="aa-home-create-form" action="<?= site_url('templates/create') ?>" method="post">
                    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                    <input type="hidden" name="blank_template" value="1">
                    <input type="hidden" name="project_intent" value="business_profile">
                    <input id="aaTemplateBusinessCategory" type="hidden" name="business_category" value="">
                    <div class="aa-home-field">
                        <label for="aaTemplateBusinessTitle">Nama Bisnis / Brand</label>
                        <input id="aaTemplateBusinessTitle" name="title" type="text"
                            placeholder="Contoh: Sari MUA Studio" required>
                    </div>
                    <div class="aa-home-field">
                        <label for="aaTemplateBusinessSlug">Slug URL</label>
                        <input id="aaTemplateBusinessSlug" name="slug" type="text" placeholder="contoh: sari-mua-studio">
                    </div>
                    <div class="aa-home-field">
                        <label for="aaTemplateBusinessDate">Tanggal Mulai / Opsional</label>
                        <input id="aaTemplateBusinessDate" name="event_date" type="date">
                    </div>
                    <div class="aa-home-modal-actions">
                        <button class="aa-home-btn aa-home-btn-secondary" type="button"
                            data-template-business-close>Batal</button>
                        <button class="aa-home-btn aa-home-btn-primary" type="submit">Buat Business Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="aaTemplatePreviewModal" class="aa-template-modal" aria-hidden="true">
        <div class="aa-template-modal-card" role="dialog" aria-modal="true" aria-labelledby="aaTemplatePreviewTitle">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div class="min-w-0">
                    <p id="aaTemplatePreviewCategory"
                        class="text-xs font-black uppercase tracking-[0.18em] text-violet-700"></p>
                    <h2 id="aaTemplatePreviewTitle" class="truncate text-lg font-black tracking-tight text-slate-900">
                        Preview Template</h2>
                </div>
                <button
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-xl font-black text-slate-700 transition hover:border-rose-300 hover:text-rose-700"
                    type="button" data-preview-close aria-label="Tutup preview">⛌</button>
            </div>
            <div id="aaTemplatePreviewBody" class="aa-template-modal-preview"></div>
        </div>
    </div>

    <div id="aaTemplateCreateDropup" class="aa-template-create-dropup" aria-hidden="true">
        <div class="aa-template-create-head">
            <div>
                <h3 id="aaTemplateCreateHeading">Buat Undangan Baru</h3>
                <p id="aaTemplateCreateName">Isi detail dasar, lalu lanjut edit desain.</p>
            </div>
            <button class="aa-template-create-close" type="button" data-template-create-close
                aria-label="Tutup">⛌</button>
        </div>
        <form class="aa-template-create-form" action="<?= site_url('templates/create') ?>" method="post">
            <?= function_exists('csrf_field') ? csrf_field() : '' ?>
            <input id="aaTemplateCreateId" type="hidden" name="template_id" value="">
            <input id="aaTemplateCreateProjectIntent" type="hidden" name="project_intent" value="">
            <input id="aaTemplateCreateBusinessCategory" type="hidden" name="business_category" value="">
            <div class="aa-home-field">
                <label id="aaTemplateCreateTitleLabel" for="aaTemplateCreateTitleInput">Judul Acara</label>
                <input id="aaTemplateCreateTitleInput" name="title" type="text"
                    placeholder="Contoh: Wedding Sarah & Dimas" required>
            </div>
            <div class="aa-home-field">
                <label for="aaTemplateCreateSlugInput">Slug URL</label>
                <input id="aaTemplateCreateSlugInput" name="slug" type="text"
                    placeholder="contoh: wedding-sarah-dimas">
            </div>
            <div class="aa-home-field">
                <label id="aaTemplateCreateDateLabel" for="aaTemplateCreateDateInput">Tanggal Acara</label>
                <input id="aaTemplateCreateDateInput" name="event_date" type="date">
            </div>
            <div class="aa-home-modal-actions">
                <button class="aa-home-btn aa-home-btn-secondary" type="button"
                    data-template-create-close>Batal</button>
                <button id="aaTemplateCreateSubmit" class="aa-home-btn aa-home-btn-primary" type="submit">Sesuaikan Desain Ini</button>
            </div>
        </form>
    </div>

    <script>
    (function() {
        if (window.AdaAcaraTemplatePickerReady) return;
        window.AdaAcaraTemplatePickerReady = true;

        const filterWrap = document.querySelector('[data-template-filter]');
        const categorySelect = document.querySelector('[data-category-select]');
        const grid = document.querySelector('[data-template-grid]');
        const emptyState = document.querySelector('[data-template-empty]');
        const endMarker = document.querySelector('[data-template-end-marker]');
        const pagination = document.querySelector('[data-template-pagination]');
        const paginationPages = document.querySelector('[data-template-pagination-pages]');
        const paginationPrev = document.querySelector('[data-template-page-prev]');
        const paginationNext = document.querySelector('[data-template-page-next]');
        const modal = document.getElementById('aaTemplatePreviewModal');
        const createModal = document.getElementById('aaHomeCreateModal');
        const projectChoiceModal = document.getElementById('aaTemplateProjectChoiceModal');
        const photoboothCreateModal = document.getElementById('aaTemplatePhotoboothCreateModal');
        const businessCreateModal = document.getElementById('aaTemplateBusinessCreateModal');
        const modalTitle = document.getElementById('aaTemplatePreviewTitle');
        const modalCategory = document.getElementById('aaTemplatePreviewCategory');
        const modalBody = document.getElementById('aaTemplatePreviewBody');
        const templateCreateDropup = document.getElementById('aaTemplateCreateDropup');
        const templateCreateHeading = document.getElementById('aaTemplateCreateHeading');
        const templateCreateName = document.getElementById('aaTemplateCreateName');
        const templateCreateId = document.getElementById('aaTemplateCreateId');
        const templateCreateProjectIntent = document.getElementById('aaTemplateCreateProjectIntent');
        const templateCreateBusinessCategory = document.getElementById('aaTemplateCreateBusinessCategory');
        const templateCreateTitleLabel = document.getElementById('aaTemplateCreateTitleLabel');
        const templateCreateTitleInput = document.getElementById('aaTemplateCreateTitleInput');
        const templateCreateSlugInput = document.getElementById('aaTemplateCreateSlugInput');
        const templateCreateDateLabel = document.getElementById('aaTemplateCreateDateLabel');
        const templateCreateSubmit = document.getElementById('aaTemplateCreateSubmit');
        const sidebarScroll = document.querySelector('[data-template-sidebar-scroll]');
        const templatesPerPage = 10;
        let currentTemplateCategory = 'all';
        let currentTemplatePage = 1;

        const blankTitleInput = document.getElementById('aaHomeBlankTitle');
        const blankSlugInput = document.getElementById('aaHomeBlankSlug');
        const photoboothTitleInput = document.getElementById('aaTemplatePhotoboothTitle');
        const photoboothSlugInput = document.getElementById('aaTemplatePhotoboothSlug');
        const businessTitleInput = document.getElementById('aaTemplateBusinessTitle');
        const businessSlugInput = document.getElementById('aaTemplateBusinessSlug');
        const businessCategoryInput = document.getElementById('aaTemplateBusinessCategory');
        const businessCreateCopy = document.getElementById('aaTemplateBusinessCreateCopy');
        const wishlistUrl = '<?= esc(site_url('templates/wishlist/toggle'), 'js') ?>';
        const wishlistLoginUrl = '<?= esc(site_url('login') . '?redirect=' . rawurlencode(site_url('templates')), 'js') ?>';
        const wishlistUserLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
        const wishlistCsrfName = '<?= function_exists('csrf_token') ? esc(csrf_token(), 'js') : '' ?>';
        let wishlistCsrfHash = '<?= function_exists('csrf_hash') ? esc(csrf_hash(), 'js') : '' ?>';
        let aaTemplateLenis = null;

        function initTemplateLenis() {
            if (aaTemplateLenis || !window.Lenis) return;
            const reduceMotion = window.matchMedia &&
                window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduceMotion) return;

            aaTemplateLenis = new window.Lenis({
                duration: 1.05,
                easing: function(t) {
                    return Math.min(1, 1.001 - Math.pow(2, -10 * t));
                },
                smoothWheel: true,
                wheelMultiplier: .9,
                touchMultiplier: 1.1,
            });
            window.aaTemplateLenis = aaTemplateLenis;

            function raf(time) {
                if (aaTemplateLenis) aaTemplateLenis.raf(time);
                window.requestAnimationFrame(raf);
            }

            window.requestAnimationFrame(raf);
        }

        initTemplateLenis();
        window.addEventListener('load', initTemplateLenis, {
            once: true,
        });

        if (sidebarScroll) {
            sidebarScroll.addEventListener('wheel', function(event) {
                if (window.innerWidth < 1024) return;
                const canScroll = sidebarScroll.scrollHeight > sidebarScroll.clientHeight;
                if (!canScroll) return;

                const nextTop = sidebarScroll.scrollTop + event.deltaY;
                const maxTop = sidebarScroll.scrollHeight - sidebarScroll.clientHeight;
                const isAtTop = sidebarScroll.scrollTop <= 0;
                const isAtBottom = sidebarScroll.scrollTop >= maxTop - 1;
                const wantsUp = event.deltaY < 0;
                const wantsDown = event.deltaY > 0;

                if ((wantsUp && isAtTop) || (wantsDown && isAtBottom)) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                sidebarScroll.scrollTop = Math.max(0, Math.min(maxTop, nextTop));
            }, {
                passive: false,
            });
        }

        function setModalOpen(modal, open) {
            if (!modal) return;
            modal.classList.toggle('is-open', open);
            modal.setAttribute('aria-hidden', open ? 'false' : 'true');
            const hasOpenModal = !!document.querySelector('.aa-home-modal.is-open, .aa-template-modal.is-open');
            document.body.style.overflow = hasOpenModal ? 'hidden' : '';
            if (aaTemplateLenis && typeof aaTemplateLenis.stop === 'function' && typeof aaTemplateLenis.start === 'function') {
                if (hasOpenModal) {
                    aaTemplateLenis.stop();
                } else {
                    aaTemplateLenis.start();
                }
            }
        }

        function closeCreateModal() {
            setModalOpen(createModal, false);
        }

        function closePhotoboothCreateModal() {
            setModalOpen(photoboothCreateModal, false);
        }

        function closeBusinessCreateModal() {
            setModalOpen(businessCreateModal, false);
        }

        function openProjectChoiceModal() {
            setModalOpen(projectChoiceModal, true);
        }

        function closeProjectChoiceModal() {
            setModalOpen(projectChoiceModal, false);
        }

        function openCreateModal() {
            closeProjectChoiceModal();
            setModalOpen(createModal, true);
            window.setTimeout(function() {
                if (blankTitleInput) blankTitleInput.focus();
            }, 80);
        }

        function openPhotoboothCreateModal() {
            closeProjectChoiceModal();
            setModalOpen(photoboothCreateModal, true);
            window.setTimeout(function() {
                if (photoboothTitleInput) photoboothTitleInput.focus();
            }, 80);
        }

        function openBusinessCreateModal(trigger) {
            closeProjectChoiceModal();
            const businessCategory = trigger?.dataset?.businessCategory || '';
            const businessCategorySlug = trigger?.dataset?.businessCategorySlug || '';
            if (businessCategoryInput) {
                businessCategoryInput.value = businessCategorySlug;
            }
            if (businessCreateCopy) {
                businessCreateCopy.textContent = businessCategory ?
                    `Isi detail dasar untuk website ${businessCategory}. Setelah itu kamu masuk Studio untuk mulai menyiapkan website profile bisnis.` :
                    'Isi detail dasar dulu. Setelah itu kamu masuk Studio untuk mulai menyiapkan website profile bisnis.';
            }
            if (businessTitleInput && businessCategory) {
                businessTitleInput.placeholder = `Contoh: ${businessCategory} Studio`;
            }
            if (businessSlugInput && businessCategorySlug) {
                businessSlugInput.placeholder = `contoh: ${businessCategorySlug}-studio`;
            }
            setModalOpen(businessCreateModal, true);
            window.setTimeout(function() {
                if (businessTitleInput) businessTitleInput.focus();
            }, 80);
        }

        function closeTemplateCreateDropup() {
            if (!templateCreateDropup) return;
            templateCreateDropup.classList.remove('is-open');
            templateCreateDropup.setAttribute('aria-hidden', 'true');
        }

        function openTemplateCreateDropup(trigger) {
            if (!templateCreateDropup || !templateCreateId) return;
            const templateId = trigger?.dataset?.templateUse || '';
            const templateTitle = trigger?.dataset?.templateTitle || '';
            const projectType = String(trigger?.dataset?.templateProjectType || 'invitation').toLowerCase();
            const isBusinessProfileTemplate = projectType === 'business_profile' || projectType === 'business-profile';
            const businessCategory = trigger?.dataset?.templateBusinessCategory || '';
            const businessCategorySlug = trigger?.dataset?.templateBusinessCategorySlug || '';

            templateCreateId.value = templateId || '';
            if (templateCreateProjectIntent) {
                templateCreateProjectIntent.value = isBusinessProfileTemplate ? 'business_profile' : '';
            }
            if (templateCreateBusinessCategory) {
                templateCreateBusinessCategory.value = isBusinessProfileTemplate ? businessCategorySlug : '';
            }
            if (templateCreateHeading) {
                templateCreateHeading.textContent = isBusinessProfileTemplate ? 'Buat Business Profile Baru' : 'Buat Undangan Baru';
            }
            if (templateCreateName) {
                if (isBusinessProfileTemplate) {
                    templateCreateName.textContent = templateTitle ?
                        `Pakai template "${templateTitle}" sebagai desain awal website ${businessCategory || 'Business Profile'}.` :
                        'Isi detail dasar, lalu lanjut edit website Business Profile.';
                } else {
                    templateCreateName.textContent = templateTitle ?
                        `Pakai template "${templateTitle}" sebagai desain awal.` :
                        'Isi detail dasar, lalu lanjut edit desain.';
                }
            }
            if (templateCreateTitleLabel) {
                templateCreateTitleLabel.textContent = isBusinessProfileTemplate ? 'Nama Bisnis / Brand' : 'Judul Acara';
            }
            if (templateCreateTitleInput && !templateCreateTitleInput.value) {
                templateCreateTitleInput.value = '';
            }
            if (templateCreateTitleInput) {
                templateCreateTitleInput.placeholder = isBusinessProfileTemplate
                    ? `Contoh: ${businessCategory || 'Sari MUA'} Studio`
                    : 'Contoh: Wedding Sarah & Dimas';
            }
            if (templateCreateSlugInput) {
                templateCreateSlugInput.placeholder = isBusinessProfileTemplate
                    ? `contoh: ${businessCategorySlug || 'sari-mua'}-studio`
                    : 'contoh: wedding-sarah-dimas';
            }
            if (templateCreateDateLabel) {
                templateCreateDateLabel.textContent = isBusinessProfileTemplate ? 'Tanggal Mulai / Opsional' : 'Tanggal Acara';
            }
            if (templateCreateSubmit) {
                templateCreateSubmit.textContent = isBusinessProfileTemplate ? 'Buat Business Profile' : 'Sesuaikan Desain Ini';
            }
            if (templateCreateSlugInput && !templateCreateSlugInput.value) {
                templateCreateSlugInput.dataset.userEdited = '0';
            }
            templateCreateDropup.classList.add('is-open');
            templateCreateDropup.setAttribute('aria-hidden', 'false');
            window.setTimeout(function() {
                if (templateCreateTitleInput) templateCreateTitleInput.focus();
            }, 80);
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

        function setWishlistButtonState(button, liked) {
            button.classList.toggle('is-active', liked);
            button.setAttribute('aria-pressed', liked ? 'true' : 'false');
            button.setAttribute('aria-label', liked ? 'Hapus dari wishlist' : 'Simpan ke wishlist');
        }

        function toggleTemplateWishlist(button) {
            if (!button || button.classList.contains('is-loading')) return;
            const templateId = button.dataset.templateId || '';
            if (!templateId) return;
            if (!wishlistUserLoggedIn) {
                window.location.href = wishlistLoginUrl;
                return;
            }

            button.classList.add('is-loading');
            const body = new FormData();
            body.set('template_id', templateId);
            if (wishlistCsrfName && wishlistCsrfHash) {
                body.set(wishlistCsrfName, wishlistCsrfHash);
            }

            fetch(wishlistUrl, {
                method: 'POST',
                body,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(function(response) {
                return response.json().then(function(payload) {
                    return {
                        ok: response.ok,
                        status: response.status,
                        payload,
                    };
                });
            }).then(function(result) {
                const payload = result.payload || {};
                if (payload.csrf_hash) {
                    wishlistCsrfHash = payload.csrf_hash;
                }
                if (result.status === 401) {
                    window.location.href = payload.login_url || wishlistLoginUrl;
                    return;
                }
                if (!result.ok || !payload.success) {
                    if (payload.message) window.alert(payload.message);
                    return;
                }
                setWishlistButtonState(button, payload.liked === true);
            }).catch(function() {
                window.alert('Wishlist belum bisa diproses. Silakan coba lagi.');
            }).finally(function() {
                button.classList.remove('is-loading');
            });
        }

        document.addEventListener('click', function(event) {
            const wishlistButton = event.target.closest('[data-template-wishlist-toggle]');
            if (wishlistButton) {
                event.preventDefault();
                event.stopImmediatePropagation();
                toggleTemplateWishlist(wishlistButton);
                return;
            }

            const previewActionButton = event.target.closest('[data-template-action-preview]');
            if (previewActionButton) {
                event.preventDefault();
                event.stopImmediatePropagation();
                const previewTarget = previewActionButton.closest('[data-preview-open]');
                if (previewTarget) openPreview(previewTarget);
                return;
            }

            const projectChoiceTrigger = event.target.closest('[data-template-open-project-choice]');
            if (projectChoiceTrigger) {
                event.preventDefault();
                openProjectChoiceModal();
                return;
            }

            if (event.target.closest('[data-template-project-choice-close]')) {
                closeProjectChoiceModal();
                return;
            }

            const projectCreateTrigger = event.target.closest('[data-template-project-create]');
            if (projectCreateTrigger) {
                event.preventDefault();
                openCreateModal();
                return;
            }

            const photoboothCreateTrigger = event.target.closest('[data-template-open-photobooth-create]');
            if (photoboothCreateTrigger) {
                event.preventDefault();
                openPhotoboothCreateModal();
                return;
            }

            const businessCreateTrigger = event.target.closest('[data-template-open-business-create]');
            if (businessCreateTrigger) {
                event.preventDefault();
                openBusinessCreateModal(businessCreateTrigger);
                return;
            }

            const createTrigger = event.target.closest('[data-home-open-create]');
            if (createTrigger) {
                event.preventDefault();
                openCreateModal();
                return;
            }

            if (event.target.closest('[data-home-modal-close]')) {
                closeCreateModal();
                return;
            }

            if (event.target.closest('[data-template-photobooth-close]')) {
                closePhotoboothCreateModal();
                return;
            }

            if (event.target.closest('[data-template-business-close]')) {
                closeBusinessCreateModal();
                return;
            }

            if (event.target.closest('[data-template-create-close]')) {
                closeTemplateCreateDropup();
                return;
            }

            const useTemplateTrigger = event.target.closest('[data-template-use]');
            if (useTemplateTrigger) {
                event.preventDefault();
                openTemplateCreateDropup(useTemplateTrigger);
                return;
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') return;
            closeProjectChoiceModal();
            closeCreateModal();
            closePhotoboothCreateModal();
            closeBusinessCreateModal();
            closeTemplateCreateDropup();
        });

        if (blankTitleInput) blankTitleInput.addEventListener('input', function() {
            if (!blankSlugInput || blankSlugInput.dataset.userEdited === '1') return;
            blankSlugInput.value = slugifyHome(blankTitleInput.value);
        });

        if (blankSlugInput) blankSlugInput.addEventListener('input', function() {
            blankSlugInput.dataset.userEdited = blankSlugInput.value ? '1' : '0';
        });

        if (photoboothTitleInput) photoboothTitleInput.addEventListener('input', function() {
            if (!photoboothSlugInput || photoboothSlugInput.dataset.userEdited === '1') return;
            photoboothSlugInput.value = slugifyHome(photoboothTitleInput.value);
        });

        if (photoboothSlugInput) photoboothSlugInput.addEventListener('input', function() {
            photoboothSlugInput.dataset.userEdited = photoboothSlugInput.value ? '1' : '0';
        });

        if (businessTitleInput) businessTitleInput.addEventListener('input', function() {
            if (!businessSlugInput || businessSlugInput.dataset.userEdited === '1') return;
            businessSlugInput.value = slugifyHome(businessTitleInput.value);
        });

        if (businessSlugInput) businessSlugInput.addEventListener('input', function() {
            businessSlugInput.dataset.userEdited = businessSlugInput.value ? '1' : '0';
        });

        if (templateCreateTitleInput) templateCreateTitleInput.addEventListener('input', function() {
            if (!templateCreateSlugInput || templateCreateSlugInput.dataset.userEdited === '1') return;
            templateCreateSlugInput.value = slugifyHome(templateCreateTitleInput.value);
        });

        if (templateCreateSlugInput) templateCreateSlugInput.addEventListener('input', function() {
            templateCreateSlugInput.dataset.userEdited = templateCreateSlugInput.value ? '1' : '0';
        });

        function templateMatchesCategory(card, category) {
            return card.dataset.templateGlobal === 'true' || category === 'all' || card.dataset.templateCategory === category;
        }

        function renderTemplatePagination(totalPages) {
            if (!pagination || !paginationPages || !paginationPrev || !paginationNext) return;
            pagination.classList.add('is-hidden');
            paginationPages.innerHTML = '';
            paginationPrev.disabled = true;
            paginationNext.disabled = true;
            return;

            pagination.classList.toggle('is-hidden', totalPages <= 1);
            paginationPrev.disabled = currentTemplatePage <= 1;
            paginationNext.disabled = currentTemplatePage >= totalPages;
            paginationPages.innerHTML = '';

            if (totalPages <= 1) return;

            const createPageButton = page => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'aa-template-pagination-page' + (page === currentTemplatePage ? ' is-active' : '');
                button.dataset.templatePage = String(page);
                button.setAttribute('aria-label', 'Halaman template ' + page);
                button.textContent = String(page);
                paginationPages.appendChild(button);
            };

            const windowSize = 5;
            let start = Math.max(1, currentTemplatePage - 2);
            let end = Math.min(totalPages, start + windowSize - 1);
            start = Math.max(1, end - windowSize + 1);

            if (start > 1) createPageButton(1);
            if (start > 2) {
                const dots = document.createElement('span');
                dots.className = 'px-1 text-sm font-black text-slate-400';
                dots.textContent = '...';
                paginationPages.appendChild(dots);
            }

            for (let page = start; page <= end; page += 1) {
                createPageButton(page);
            }

            if (end < totalPages - 1) {
                const dots = document.createElement('span');
                dots.className = 'px-1 text-sm font-black text-slate-400';
                dots.textContent = '...';
                paginationPages.appendChild(dots);
            }
            if (end < totalPages) createPageButton(totalPages);
        }

        function filterTemplates(category, page = 1) {
            if (!grid) return;
            currentTemplateCategory = category || 'all';
            const cards = Array.from(grid.querySelectorAll('[data-template-card]'));
            const matchedCards = cards.filter(card => templateMatchesCategory(card, currentTemplateCategory));
            const totalPages = 1;
            currentTemplatePage = 1;
            const visibleCards = new Set(matchedCards);
            let visibleIndex = 0;

            cards.forEach(card => {
                const show = visibleCards.has(card);
                card.classList.toggle('hidden', !show);
                card.classList.remove('is-entering');
                card.style.animationDelay = '';
                if (show) {
                    card.style.animationDelay = Math.min(visibleIndex, 9) * 24 + 'ms';
                    requestAnimationFrame(() => {
                        card.classList.add('is-entering');
                    });
                    visibleIndex += 1;
                }
            });

            if (emptyState) emptyState.classList.toggle('hidden', matchedCards.length !== 0);
            if (endMarker) endMarker.classList.toggle('hidden', matchedCards.length === 0);
            renderTemplatePagination(totalPages);
        }

        function closePreview() {
            if (!modal) return;
            setModalOpen(modal, false);
            if (modalBody) modalBody.innerHTML = '';
            closeTemplateCreateDropup();
        }

        function openPreview(button) {
            if (!modal || !modalBody) return;

            const title = button.dataset.previewTitle || 'Preview Template';
            const category = button.dataset.previewCategory || 'Lainnya';
            const url = button.dataset.previewUrl || '';
            const src = button.dataset.previewSrc || '';
            const templateId = button.dataset.previewId || '';
            const projectType = String(button.dataset.previewProjectType || 'invitation').toLowerCase();
            const isBusinessProfileTemplate = projectType === 'business_profile' || projectType === 'business-profile';
            const businessCategory = button.dataset.previewBusinessCategory || '';
            const businessCategorySlug = button.dataset.previewBusinessCategorySlug || '';

            modalTitle.textContent = title;
            modalCategory.textContent = isBusinessProfileTemplate
                ? `Business Profile${businessCategory ? ' / ' + businessCategory : ''}`
                : category;
            modalBody.innerHTML = '';

            const lightPreview = document.createElement('div');
            lightPreview.className = 'aa-template-modal-preview-light';

            const cover = document.createElement('div');
            cover.className = 'aa-template-modal-cover';
            if (src) {
                const image = document.createElement('img');
                image.src = src;
                image.alt = title;
                image.loading = 'lazy';
                cover.appendChild(image);
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'aa-template-modal-placeholder';
                const placeholderTitle = document.createElement('strong');
                placeholderTitle.textContent = title;
                const placeholderText = document.createElement('span');
                placeholderText.textContent = 'Cover template';
                placeholder.appendChild(placeholderTitle);
                placeholder.appendChild(placeholderText);
                cover.appendChild(placeholder);
            }

            const copy = document.createElement('div');
            copy.className = 'aa-template-modal-copy';
            const copyTitle = document.createElement('h3');
            copyTitle.textContent = title;
            const copyText = document.createElement('p');
            copyText.textContent = isBusinessProfileTemplate
                ? 'Gunakan template ini untuk membuat website Business Profile dengan flow khusus bisnis, atau buka preview penuh di tab baru.'
                : 'Gunakan template ini untuk masuk ke flow pemakaian template, atau buka preview penuh di tab baru untuk melihat halaman lengkap.';
            const actions = document.createElement('div');
            actions.className = 'aa-template-modal-actions';

            const useLink = document.createElement('button');
            useLink.className = 'aa-template-modal-action primary';
            useLink.type = 'button';
            useLink.dataset.templateUse = templateId;
            useLink.dataset.templateTitle = title;
            useLink.dataset.templateProjectType = isBusinessProfileTemplate ? 'business_profile' : 'invitation';
            useLink.dataset.templateBusinessCategory = businessCategory;
            useLink.dataset.templateBusinessCategorySlug = businessCategorySlug;
            useLink.textContent = isBusinessProfileTemplate ? 'Buat Business Profile' : 'Sesuaikan Desain Ini';

            const fullPreviewLink = document.createElement('a');
            fullPreviewLink.className = 'aa-template-modal-action secondary';
            fullPreviewLink.href = url || '#';
            fullPreviewLink.target = '_blank';
            fullPreviewLink.rel = 'noopener';
            fullPreviewLink.textContent = 'Lihat Preview Penuh';

            actions.appendChild(useLink);
            actions.appendChild(fullPreviewLink);
            copy.appendChild(copyTitle);
            copy.appendChild(copyText);
            copy.appendChild(actions);
            lightPreview.appendChild(cover);
            lightPreview.appendChild(copy);
            modalBody.appendChild(lightPreview);

            setModalOpen(modal, true);
        }

        if (filterWrap) {
            filterWrap.addEventListener('click', event => {
                const button = event.target.closest('[data-category-filter]');
                if (!button) return;

                filterWrap.querySelectorAll('[data-category-filter]').forEach(item => {
                    item.classList.toggle('is-active', item === button);
                });
                const category = button.dataset.categoryFilter || 'all';
                if (categorySelect) {
                    categorySelect.value = category;
                }
                filterTemplates(category, 1);
            });
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', () => {
                const category = categorySelect.value || 'all';
                if (filterWrap) {
                    filterWrap.querySelectorAll('[data-category-filter]').forEach(item => {
                        item.classList.toggle('is-active', (item.dataset.categoryFilter || 'all') === category);
                    });
                }
                filterTemplates(category, 1);
            });
        }

        paginationPrev?.addEventListener('click', () => {
            filterTemplates(currentTemplateCategory, currentTemplatePage - 1);
        });

        paginationNext?.addEventListener('click', () => {
            filterTemplates(currentTemplateCategory, currentTemplatePage + 1);
        });

        paginationPages?.addEventListener('click', event => {
            const pageButton = event.target.closest('[data-template-page]');
            if (!pageButton) return;
            filterTemplates(currentTemplateCategory, Number(pageButton.dataset.templatePage || '1'));
        });

        filterTemplates(currentTemplateCategory, currentTemplatePage);

        document.addEventListener('click', event => {
            const previewButton = event.target.closest('[data-preview-open]');
            if (previewButton) {
                event.preventDefault();
                openPreview(previewButton);
                return;
            }

            if (event.target.closest('[data-preview-close]')) {
                closePreview();
                return;
            }

            // if (modal && event.target === modal) {
            //     closePreview();
            // }

            // if (previewTrigger && (event.key === 'Enter' || event.key === ' ')) {
            //     event.preventDefault();
            //     openPreview(previewTrigger);
            //     return;
            // }

            if (event.key === 'Escape') {
                closePreview();
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closePreview();
        });
    })();
    </script>
</body>

</html>
