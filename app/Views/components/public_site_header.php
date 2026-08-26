<?php
    helper('aa_icon');

    $active = (string) ($active ?? '');
    $homeUrl = site_url('/');
    $templatesUrl = site_url('templates');
    $plansUrl = site_url('plans');
    $photoboothUrl = site_url('fitur/photobooth-digital');
    $photographerGalleryUrl = site_url('fitur/galeri-klien-fotografer');
    $isHomeHeader = $active === 'home';
    $isLoggedIn = (bool) (session()->get('isLoggedIn') ?? session()->get('userId'));
    $accountUrl = $isLoggedIn ? site_url('dashboard') : site_url('login');
    $creatorUrl = site_url('creator');
    $categoryGroups = [
        'wedding' => [
            'label' => 'Pernikahan',
            'icon' => 'heart',
            'columns' => [
                'Undangan pernikahan' => ['Elegan', 'Rustik', 'Modern', 'Bunga dan Botani', 'Vintage', 'Pantai', 'Premium'],
                'Momen acara' => ['Akad', 'Resepsi', 'Lamaran', 'Pertunangan', 'Bridal shower'],
                'Gaya desain' => ['Minimalis', 'Foto', 'Floral', 'Dark elegant'],
            ],
        ],
        'birthday' => [
            'label' => 'Ulang Tahun',
            'icon' => 'gift',
            'columns' => [
                'Undangan ulang tahun' => ['Anak', 'Dewasa', 'Simple', 'Foto', 'Tema lucu'],
                'Pesta' => ['Dinner', 'Garden party', 'Keluarga', 'Teman dekat'],
            ],
        ],
        'kids' => [
            'label' => 'Aqiqah & Anak',
            'icon' => 'baby',
            'columns' => [
                'Acara anak' => ['Aqiqah', 'Khitanan', 'Syukuran', 'Baby shower'],
                'Gaya' => ['Pastel', 'Kartun lembut', 'Foto keluarga'],
            ],
        ],
        'party' => [
            'label' => 'Pesta',
            'icon' => 'sparkles',
            'columns' => [
                'Jenis pesta' => ['Gathering', 'Engagement', 'Graduation', 'Dinner', 'Launching'],
                'Tema' => ['Formal', 'Casual', 'Corporate', 'Premium'],
            ],
        ],
        'cards' => [
            'label' => 'Kartu ucapan',
            'icon' => 'card',
            'columns' => [
                'Ucapan' => ['Terima kasih', 'Selamat', 'Pernikahan', 'Pertunangan'],
                'Kartu digital' => ['Minimalis', 'Foto', 'Floral'],
            ],
        ],
        'trending' => [
            'label' => 'Sedang Tren',
            'icon' => 'trend',
            'columns' => [
                'Template populer' => [
                    ['label' => 'Template Premium', 'url' => $templatesUrl . '?q=premium'],
                    ['label' => 'Floral modern', 'url' => $templatesUrl . '?q=floral%20modern'],
                    ['label' => 'Clean minimal', 'url' => $templatesUrl . '?q=clean%20minimal'],
                    ['label' => 'Dark elegant', 'url' => $templatesUrl . '?q=dark%20elegant'],
                ],
                'Fitur aktif' => [
                    ['label' => 'Acara AI', 'url' => site_url('fitur/acara-ai')],
                    ['label' => 'Magic Layer', 'url' => site_url('fitur/magic-layer')],
                    ['label' => 'Remove BG', 'url' => site_url('fitur/remove-bg')],
                ],
            ],
        ],
    ];
    unset($categoryGroups['cards']); // Disembunyikan sementara sampai halaman kartu ucapan siap.

    $headerCategoryMap = [
        'wedding' => ['wedding', 'lamaran'],
        'birthday' => ['ulang-tahun'],
        'kids' => ['aqiqah', 'khitan', 'syukuran'],
        'party' => ['bukber', 'halal-bihalal', 'corporate', 'wisuda'],
    ];
    $headerItemLabel = static function (mixed $item): string {
        return is_array($item) ? (string) ($item['label'] ?? '') : (string) $item;
    };
    $headerItemUrl = static function (mixed $item) use ($templatesUrl): string {
        if (is_array($item) && ! empty($item['url'])) {
            return (string) $item['url'];
        }

        if (is_array($item) && ! empty($item['subcategory'])) {
            return $templatesUrl . '?subcategory=' . rawurlencode((string) $item['subcategory']);
        }

        return $templatesUrl . '?q=' . rawurlencode(is_array($item) ? (string) ($item['label'] ?? '') : (string) $item);
    };
    $headerItemDisabled = static function (mixed $item): bool {
        return is_array($item) && ! empty($item['disabled']);
    };

    try {
        $subcategoryModel = new \App\Models\TemplateSubcategoryModel();
        $subcategoriesByCategory = $subcategoryModel->activeGroupedByCategorySlug();

        if ($subcategoriesByCategory !== []) {
            foreach ($headerCategoryMap as $groupKey => $categorySlugs) {
                $columns = [];
                foreach ($categorySlugs as $categorySlug) {
                    foreach (($subcategoriesByCategory[$categorySlug] ?? []) as $subcategory) {
                        $columnTitle = trim((string) ($subcategory['group_title'] ?? ''));
                        if ($columnTitle === '') {
                            $columnTitle = (string) ($subcategory['category_name'] ?? $categoryGroups[$groupKey]['label'] ?? 'Template');
                        }
                        $columns[$columnTitle][] = [
                            'label' => (string) ($subcategory['name'] ?? ''),
                            'subcategory' => (string) ($subcategory['slug'] ?? ''),
                        ];
                    }
                }

                if ($columns !== [] && isset($categoryGroups[$groupKey])) {
                    $categoryGroups[$groupKey]['columns'] = $columns;
                }
            }
        }
    } catch (\Throwable) {
        // Header publik harus tetap tampil walaupun tabel subkategori belum tersedia.
    }

    $digitalColumns = [];
    foreach (['wedding', 'birthday', 'kids', 'party'] as $digitalGroupKey) {
        if (! isset($categoryGroups[$digitalGroupKey])) {
            continue;
        }

        $group = $categoryGroups[$digitalGroupKey];
        $items = [];
        foreach ((array) ($group['columns'] ?? []) as $columnItems) {
            foreach ((array) $columnItems as $item) {
                $label = trim($headerItemLabel($item));
                if ($label !== '') {
                    $items[] = $item;
                }
            }
        }

        if ($items !== []) {
            $digitalColumns[(string) ($group['label'] ?? 'Undangan')] = $items;
        }
    }

    $headerMenuGroups = [
        'product' => [
            'label' => 'Produk',
            'icon' => 'card',
            'columns' => [
                'Produk AdaAcara' => [
                    ['label' => 'Undangan Digital', 'url' => $templatesUrl . '?type=invitation'],
                    ['label' => 'Digital Photobooth', 'url' => $photoboothUrl],
                    ['label' => 'Business Profile', 'url' => $templatesUrl . '?project=business-profile'],
                    ['label' => 'Galeri Klien Fotografer', 'url' => $photographerGalleryUrl],
                ],
            ],
        ],
        'trending' => $categoryGroups['trending'],
    ];
    $headerMenuActive = static function (string $key) use ($active): bool {
        if ($key === 'product') {
            return in_array($active, ['product', 'photobooth', 'photographer-gallery', 'digital', 'templates', 'wedding', 'birthday', 'kids', 'party'], true);
        }

        return $active === $key;
    };

    $headerIcon = static function (string $name, string $class = 'h-4 w-4'): string {
        return aa_phosphor_icon($name, ['class' => $class]);
    };
?>
<style>
    .aa-public-site-header {
        position: sticky;
        top: 0;
        z-index: 40;
        background: rgba(255, 255, 255, .62);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        box-shadow: 0 10px 35px rgba(17, 24, 39, .08), inset 0 1px 0 rgba(255, 255, 255, .7);
        transition: all .35s ease;
        margin: 15px 15px -15px 15px;
        border-radius: 50px;
    }

    .aa-public-site-header,
    .aa-public-site-header * {
        box-sizing: border-box;
    }

    .aa-public-site-header-inner {
        display: grid;
        min-height: 72px;
        width: min(100% - 32px, 1850px);
        margin: 0 auto;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 18px;
    }

    .aa-public-site-logo {
        display: inline-flex;
        align-items: center;
        min-width: 0;
    }

    .aa-public-site-logo img {
        display: block;
        width: 170px;
        height: auto;
        object-fit: contain;
    }

    .aa-public-site-nav {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .aa-public-site-nav-item {
        position: relative;
    }

    .aa-public-site-nav-item::after {
        position: absolute;
        top: 100%;
        left: -18px;
        right: -18px;
        height: 18px;
        content: "";
        display: none;
    }

    .aa-public-site-nav-item:hover::after {
        display: block;
    }

    .aa-public-site-nav-link,
    .aa-public-site-promo-link,
    .aa-public-site-icon-btn,
    .aa-public-site-premium {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 900;
        transition: .16s ease;
        white-space: nowrap;
    }

    .aa-public-site-nav-link svg,
    .aa-public-site-promo-link svg,
    .aa-public-site-premium svg,
    .aa-public-site-mobile-row svg,
    .aa-public-site-mobile-category svg,
    .aa-public-site-mobile-back svg {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        stroke-width: 1.9;
    }

    .aa-public-site-nav-link {
        appearance: none;
        border: 1px solid transparent;
        background: transparent;
        color: #334155;
        cursor: default;
        font-family: inherit;
        padding: 0 15px;
    }

    a.aa-public-site-nav-link {
        cursor: pointer;
    }

    .aa-public-site-promo-link {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .48);
        background:
            linear-gradient(120deg, #dc2626 0%, #ef4444 22%, #ffffff 46%, #dc2626 68%, #f59e0b 100%);
        background-size: 240% 100%;
        color: #ffffff;
        padding: 0 16px;
        box-shadow: 0 14px 32px rgba(220, 38, 38, .22);
        animation: aaPublicPromoShift 5.8s ease-in-out infinite;
    }

    .aa-public-site-promo-link::after {
        position: absolute;
        inset: 1px;
        z-index: -1;
        border-radius: inherit;
        background: linear-gradient(135deg, rgba(185, 28, 28, .88), rgba(239, 68, 68, .68), rgba(245, 158, 11, .78));
        content: "";
    }

    .aa-public-site-promo-link:hover {
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 18px 38px rgba(220, 38, 38, .28);
    }

    .aa-public-site-promo-flag {
        width: 25px;
        height: 18px;
        flex: 0 0 25px;
        border-radius: 5px;
        object-fit: cover;
    }

    @keyframes aaPublicPromoShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .aa-public-site-nav-link:hover,
    .aa-public-site-nav-link.is-active,
    .aa-public-site-nav-item:hover .aa-public-site-nav-link {
        border-color: #eee7ef;
        background: #f6f0ff;
        color: #7550c4;
    }

    .aa-public-site-nav-link.is-disabled,
    .aa-public-site-mobile-row.is-disabled {
        cursor: not-allowed;
        opacity: .66;
        pointer-events: none;
    }

    .aa-public-site-nav-link.is-disabled:hover {
        border-color: transparent;
        background: transparent;
        color: #334155;
    }

    .aa-public-site-soon-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: linear-gradient(135deg, #7c3aed, #5b21b6);
        color: #fff;
        padding: 3px 7px;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .05em;
        line-height: 1;
        text-transform: uppercase;
    }

    .aa-public-site-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .aa-public-site-icon-btn {
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        min-width: 44px;
        min-height: 44px;
        padding: 0;
        border: 1px solid #f1f5f9;
        background: #f8fafc;
        color: #0f172a;
    }

    .aa-public-site-icon-btn svg {
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
        stroke-width: 1.9;
    }

    .aa-public-site-actions .aa-home-theme-toggle,
    .aa-public-site-mobile .aa-home-theme-toggle {
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        min-width: 44px;
        min-height: 44px;
        padding: 0;
        box-shadow: none;
    }

    .aa-public-site-actions .aa-home-theme-toggle svg,
    .aa-public-site-mobile .aa-home-theme-toggle svg {
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
        stroke-width: 1.9;
    }

    .aa-public-site-icon-btn:hover {
        border-color: #d9ccf4;
        background: #f6f0ff;
        color: #7550c4;
    }

    .aa-public-site-premium {
        border: 1px solid #d9ccf4;
        background: linear-gradient(135deg, #fbf8ff 0%, #f1e9ff 100%);
        color: #7550c4;
        padding: 0 16px;
        box-shadow: 0 10px 24px rgba(91, 67, 118, .12);
    }

    .aa-public-site-premium:hover {
        background: linear-gradient(135deg, #a878f1 0%, #8158d8 100%);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .aa-public-site-search-panel {
        position: fixed;
        top: 72px;
        right: max(18px, calc((100vw - 1850px) / 2 + 18px));
        z-index: 60;
        display: none;
        width: min(420px, calc(100vw - 32px));
        border: 1px solid rgba(217, 204, 244, .92);
        border-radius: 22px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 24px 64px rgba(15, 23, 42, .16);
        padding: 12px;
    }

    .aa-public-site-header.is-search-open .aa-public-site-search-panel {
        display: block;
    }

    .aa-public-site-search-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
    }

    .aa-public-site-search-input {
        min-height: 42px;
        border: 1px solid #e2e8f0;
        border-radius: 15px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 800;
        outline: none;
    }

    .aa-public-site-search-input:focus {
        border-color: #8f65df;
        box-shadow: 0 0 0 4px rgba(143, 101, 223, .14);
    }

    .aa-public-site-search-submit {
        min-height: 42px;
        border: 1px solid #d9ccf4;
        border-radius: 15px;
        background: linear-gradient(135deg, #fbf8ff 0%, #f1e9ff 100%);
        color: #7550c4;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 900;
    }

    .aa-public-site-search-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 10px;
    }

    .aa-public-site-search-suggestions a {
        border-radius: 999px;
        background: #f6f0ff;
        color: #7550c4;
        padding: 6px 9px;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
    }

    .aa-public-site-mega {
        position: fixed;
        top: 72px;
        left: 50%;
        z-index: 45;
        display: block;
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
        width: min(max(820px, calc((var(--aa-mega-cols, 3) * 380px) + 96px)), calc(100vw - 40px));
        border: 1px solid rgba(217, 204, 244, .92);
        border-radius: 24px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 24px 64px rgba(15, 23, 42, .16);
        transform: translateX(-50%) translateY(8px);
        overflow: hidden;
        transition: opacity .14s ease, transform .14s ease, visibility .14s ease;
    }

    .aa-public-site-mega.is-product {
        position: absolute;
        top: calc(100% + 14px);
        left: 0;
        width: min(360px, calc(100vw - 40px));
        transform: translateY(8px);
    }

    .aa-public-site-mega.is-product::before {
        left: 42px;
        transform: rotate(45deg);
    }

    .aa-public-site-mega::before {
        position: absolute;
        top: -8px;
        left: 50%;
        width: 16px;
        height: 16px;
        border-top: 1px solid rgba(217, 204, 244, .92);
        border-left: 1px solid rgba(217, 204, 244, .92);
        background: #ffffff;
        content: "";
        transform: translateX(-50%) rotate(45deg);
    }

    .aa-public-site-nav-item:hover .aa-public-site-mega {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) translateY(0);
    }

    .aa-public-site-nav-item:hover .aa-public-site-mega.is-product {
        transform: translateY(0);
    }

    .aa-public-site-nav:has(.aa-public-site-nav-item:hover) .aa-public-site-nav-item:not(:hover) .aa-public-site-mega {
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
        transform: translateX(-50%) translateY(8px);
    }

    .aa-public-site-nav:has(.aa-public-site-nav-item:hover) .aa-public-site-nav-item:not(:hover) .aa-public-site-mega.is-product {
        transform: translateY(8px);
    }

    .aa-public-site-mega-inner {
        display: grid;
        grid-template-columns: repeat(var(--aa-mega-cols, 3), minmax(0, 1fr));
        gap: 24px;
        padding: 26px;
        background: linear-gradient(180deg, #ffffff 0%, #fbf8ff 100%);
    }

    .aa-public-site-mega-col {
        display: grid;
        align-content: start;
        gap: 8px;
        min-width: 0;
    }

    .aa-public-site-mega-title {
        display: inline-flex;
        width: fit-content;
        align-items: center;
        border-radius: 999px;
        background: #f6f0ff;
        color: #7550c4;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 3px;
    }

    .aa-public-site-mega-link {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        border-radius: 11px;
        color: #334155;
        font-size: 13px;
        font-weight: 780;
        line-height: 1.35;
        padding: 4px 8px;
        text-decoration: none;
        transition: .16s ease;
    }

    .aa-public-site-mega-link:hover {
        background: #f8fafc;
        color: #8f65df;
    }

    .aa-public-site-mega-link.is-disabled,
    .aa-public-site-mobile-subitem.is-disabled {
        cursor: not-allowed;
        opacity: .68;
        pointer-events: none;
    }

    .aa-public-site-mega-link.is-disabled:hover {
        background: transparent;
        color: #334155;
    }

    .aa-public-site-mobile {
        display: none;
    }

    .aa-public-site-mobile-panel {
        position: fixed;
        inset: 73px 0 auto 0;
        display: none;
        min-height: calc(100dvh - 73px);
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
        overflow-y: auto;
        box-shadow: 0 10px 35px rgba(17, 24, 39, .08), inset 0 1px 0 rgba(255, 255, 255, .7);
        transition: all .35s ease;
        border-radius: 35px;
    }

    .aa-public-site-header.is-mobile-open .aa-public-site-mobile-panel {
        display: block;
    }

    .aa-public-site-mobile-view {
        display: none;
        padding: 8px 0 28px;
    }

    .aa-public-site-mobile-view.is-active {
        display: block;
    }

    .aa-public-site-mobile-row,
    .aa-public-site-mobile-back {
        display: flex;
        width: 100%;
        max-width: 100%;
        min-height: 52px;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border: 0;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        color: #0f172a;
        padding: 0 20px;
        font: inherit;
        font-size: 14px;
        font-weight: 900;
        text-decoration: none;
        text-align: left;
    }

    .aa-public-site-mobile-category {
        display: flex;
        min-height: 46px;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        color: #0f172a;
        padding: 0 20px;
        font-size: 14px;
        font-weight: 950;
    }

    .aa-public-site-mobile-row span {
        display: inline-flex;
        min-width: 0;
        align-items: center;
        gap: 10px;
    }

    .aa-public-site-mobile-premium {
        margin: 12px 16px 8px;
        width: calc(100% - 32px);
        min-height: 48px;
        border-radius: 18px;
        background: linear-gradient(135deg, #fbf8ff 0%, #f1e9ff 100%);
        color: #7550c4;
        box-shadow: 0 12px 26px rgba(91, 67, 118, .12);
    }

    .aa-public-site-mobile-promo {
        margin: 8px 16px;
        width: calc(100% - 32px);
        min-height: 48px;
        border-radius: 18px;
        border-bottom: 0;
        background:
            linear-gradient(120deg, #dc2626 0%, #ef4444 22%, #ffffff 46%, #dc2626 68%, #f59e0b 100%);
        background-size: 240% 100%;
        color: #ffffff;
        box-shadow: 0 14px 32px rgba(220, 38, 38, .22);
        animation: aaPublicPromoShift 5.8s ease-in-out infinite;
    }

    .aa-public-site-mobile-promo .aa-public-site-promo-flag {
        width: 27px;
        height: 20px;
        flex-basis: 27px;
    }

    .aa-public-site-mobile-auth {
        margin: 8px 16px 4px;
        width: calc(100% - 32px);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
    }

    .aa-public-site-mobile-subtitle {
        padding: 18px 34px 8px;
        text-align: center;
        color: #7f43de;
        font-size: 14px;
        font-weight: 950;
    }

    .aa-public-site-mobile-subitem {
        display: block;
        padding: 10px 34px;
        color: #334155;
        font-size: 14px;
        font-weight: 760;
        text-decoration: none;
    }

    .aa-public-site-mobile-subitem:hover {
        color: #8f65df;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-header,
    html[data-aa-public-theme="dark"] .aa-public-site-header {
        border-bottom-color: rgba(148, 163, 184, .18);
        background: rgba(7, 11, 18, .78);
        box-shadow: 0 18px 46px rgba(0, 0, 0, .18);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-logo img,
    html[data-aa-public-theme="dark"] .aa-public-site-logo img {
        filter: invert(1) brightness(2.05) contrast(.92);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-nav-link,
    html[data-aa-public-theme="dark"] .aa-public-site-nav-link {
        color: #cbd5e1;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-nav-link:hover,
    html[data-aa-home-theme="dark"] .aa-public-site-nav-link.is-active,
    html[data-aa-home-theme="dark"] .aa-public-site-nav-item:hover .aa-public-site-nav-link,
    html[data-aa-home-theme="dark"] .aa-public-site-promo-link:hover,
    html[data-aa-public-theme="dark"] .aa-public-site-nav-link:hover,
    html[data-aa-public-theme="dark"] .aa-public-site-nav-link.is-active,
    html[data-aa-public-theme="dark"] .aa-public-site-nav-item:hover .aa-public-site-nav-link,
    html[data-aa-public-theme="dark"] .aa-public-site-promo-link:hover {
        border-color: rgba(143, 101, 223, .24);
        background: rgba(143, 101, 223, .10);
        color: #ffffff;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-promo-link,
    html[data-aa-public-theme="dark"] .aa-public-site-promo-link {
        border-color: rgba(255, 255, 255, .22);
        color: #ffffff;
        box-shadow: 0 16px 38px rgba(220, 38, 38, .24);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-promo-link:hover,
    html[data-aa-public-theme="dark"] .aa-public-site-promo-link:hover {
        background:
            linear-gradient(120deg, #dc2626 0%, #ef4444 22%, #ffffff 46%, #dc2626 68%, #f59e0b 100%);
        background-size: 240% 100%;
    }

    @media (prefers-reduced-motion: reduce) {
        .aa-public-site-promo-link,
        .aa-public-site-mobile-promo {
            animation: none;
        }
    }

    html[data-aa-home-theme="dark"] .aa-public-site-icon-btn,
    html[data-aa-public-theme="dark"] .aa-public-site-icon-btn {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .82);
        color: #e2e8f0;
        box-shadow: 0 18px 42px rgba(0, 0, 0, .22);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-icon-btn:hover,
    html[data-aa-public-theme="dark"] .aa-public-site-icon-btn:hover {
        border-color: rgba(143, 101, 223, .55);
        background: rgba(143, 101, 223, .12);
        color: #d9ccf4;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-search-panel,
    html[data-aa-home-theme="dark"] .aa-public-site-mega,
    html[data-aa-public-theme="dark"] .aa-public-site-search-panel,
    html[data-aa-public-theme="dark"] .aa-public-site-mega {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .97);
        box-shadow: 0 26px 70px rgba(0, 0, 0, .34);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mega::before,
    html[data-aa-public-theme="dark"] .aa-public-site-mega::before {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .97);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mega-inner,
    html[data-aa-public-theme="dark"] .aa-public-site-mega-inner {
        background: linear-gradient(180deg, rgba(15, 23, 42, .98) 0%, rgba(7, 11, 18, .98) 100%);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mega-title,
    html[data-aa-public-theme="dark"] .aa-public-site-mega-title {
        background: rgba(143, 101, 223, .12);
        color: #d9ccf4;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mega-link,
    html[data-aa-public-theme="dark"] .aa-public-site-mega-link {
        color: #cbd5e1;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mega-link:hover,
    html[data-aa-public-theme="dark"] .aa-public-site-mega-link:hover {
        background: rgba(143, 101, 223, .10);
        color: #d9ccf4;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-search-input,
    html[data-aa-public-theme="dark"] .aa-public-site-search-input {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(7, 11, 18, .72);
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-search-input::placeholder,
    html[data-aa-public-theme="dark"] .aa-public-site-search-input::placeholder {
        color: #94a3b8;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-search-suggestions a,
    html[data-aa-public-theme="dark"] .aa-public-site-search-suggestions a {
        background: rgba(143, 101, 223, .12);
        color: #d9ccf4;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mobile-panel,
    html[data-aa-public-theme="dark"] .aa-public-site-mobile-panel {
        border-top-color: rgba(148, 163, 184, .18);
        background: rgba(7, 11, 18, .98);
        box-shadow: 0 24px 54px rgba(0, 0, 0, .34);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mobile-row,
    html[data-aa-home-theme="dark"] .aa-public-site-mobile-back,
    html[data-aa-public-theme="dark"] .aa-public-site-mobile-row,
    html[data-aa-public-theme="dark"] .aa-public-site-mobile-back {
        border-bottom-color: rgba(148, 163, 184, .14);
        background: rgba(7, 11, 18, .98);
        color: #e2e8f0;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mobile-promo,
    html[data-aa-public-theme="dark"] .aa-public-site-mobile-promo {
        background:
            linear-gradient(120deg, #dc2626 0%, #ef4444 22%, #ffffff 46%, #dc2626 68%, #f59e0b 100%);
        background-size: 240% 100%;
        color: #ffffff;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mobile-category,
    html[data-aa-public-theme="dark"] .aa-public-site-mobile-category {
        background: rgba(15, 23, 42, .92);
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mobile-auth,
    html[data-aa-public-theme="dark"] .aa-public-site-mobile-auth {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .82);
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mobile-subtitle,
    html[data-aa-public-theme="dark"] .aa-public-site-mobile-subtitle {
        color: #f8fafc;
    }

    html[data-aa-home-theme="dark"] .aa-public-site-mobile-subitem,
    html[data-aa-public-theme="dark"] .aa-public-site-mobile-subitem {
        color: #cbd5e1;
    }

    @media (max-width: 1120px) {
        .aa-public-site-header-inner {
            grid-template-columns: auto auto;
            min-height: 72px;
            justify-content: space-between;
        }

        .aa-public-site-nav,
        .aa-public-site-actions {
            display: none;
        }

        .aa-public-site-mobile {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .aa-public-site-logo img {
            width: 160px;
        }
    }

    @media (min-width: 1121px) and (max-width: 1280px) {
        .aa-public-site-nav-link {
            padding: 0 11px;
            font-size: 13px;
        }

        .aa-public-site-mega {
            width: min(max(760px, calc((var(--aa-mega-cols, 3) * 330px) + 64px)), calc(100vw - 28px));
        }

        .aa-public-site-mega-inner {
            gap: 18px;
            padding: 22px;
        }
    }

    @media (max-width: 520px) {
        .aa-public-site-header {
            margin: 12px 12px -12px;
            border-radius: 36px;
        }

        .aa-public-site-header-inner {
            width: min(100% - 22px, 1850px);
            min-height: 66px;
            gap: 8px;
        }

        .aa-public-site-logo img {
            width: 150px;
        }

        .aa-public-site-mobile {
            gap: 6px;
        }

        .aa-public-site-icon-btn,
        .aa-public-site-actions .aa-home-theme-toggle,
        .aa-public-site-mobile .aa-home-theme-toggle {
            flex-basis: 38px;
            width: 38px;
            height: 38px;
            min-width: 38px;
            min-height: 38px;
        }

        .aa-public-site-icon-btn svg,
        .aa-public-site-actions .aa-home-theme-toggle svg,
        .aa-public-site-mobile .aa-home-theme-toggle svg {
            width: 18px;
            height: 18px;
            flex-basis: 18px;
        }

        .aa-public-site-search-panel {
            top: 86px;
            right: 20px;
            left: 20px;
            width: auto;
            max-width: calc(100vw - 40px);
            max-height: calc(100dvh - 112px);
            overflow-x: hidden;
            overflow-y: auto;
            border-radius: 24px;
            padding: 10px;
        }

        .aa-public-site-search-form {
            grid-template-columns: minmax(0, 1fr) 64px;
        }

        .aa-public-site-search-input,
        .aa-public-site-search-submit {
            min-height: 42px;
            border-radius: 14px;
            font-size: 13px;
        }

        .aa-public-site-search-suggestions {
            gap: 6px;
        }

        .aa-public-site-search-suggestions a {
            padding: 6px 8px;
            font-size: 11px;
        }

        .aa-public-site-mobile-panel {
            inset: 86px 20px auto;
            min-height: 0;
            max-width: calc(100vw - 40px);
            max-height: calc(100dvh - 112px);
            border: 1px solid rgba(217, 204, 244, .72);
            border-radius: 28px;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .aa-public-site-mobile-view {
            padding: 8px 0 18px;
        }

        .aa-public-site-mobile-row,
        .aa-public-site-mobile-back {
            min-height: 50px;
            padding: 0 18px;
        }

        .aa-public-site-mobile-promo span {
            max-width: 100%;
            white-space: normal;
        }

        .aa-public-site-mobile-premium,
        .aa-public-site-mobile-promo,
        .aa-public-site-mobile-auth {
            margin-left: 14px;
            margin-right: 14px;
            width: calc(100% - 28px);
        }
    }
</style>
<header class="aa-public-site-header" data-aa-public-site-header>
    <div class="aa-public-site-header-inner">
        <a class="aa-public-site-logo" href="<?= esc($homeUrl, 'attr') ?>" aria-label="AdaAcara">
            <img class="aa-public-logo" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
        </a>

        <nav class="aa-public-site-nav" aria-label="Navigasi kategori">
            <?php foreach ($headerMenuGroups as $key => $group): ?>
                <div class="aa-public-site-nav-item">
                    <button class="aa-public-site-nav-link <?= $headerMenuActive($key) ? 'is-active' : '' ?>" type="button">
                        <?= $headerIcon((string) $group['icon']) ?>
                        <span><?= esc((string) $group['label']) ?></span>
                    </button>
                    <div class="aa-public-site-mega <?= $key === 'product' ? 'is-product' : '' ?>" style="--aa-mega-cols: <?= count($group['columns']) ?>;">
                        <div class="aa-public-site-mega-inner">
                            <?php foreach ($group['columns'] as $title => $items): ?>
                                <div class="aa-public-site-mega-col">
	                                    <div class="aa-public-site-mega-title"><?= esc((string) $title) ?></div>
	                                    <?php foreach ($items as $item): ?>
                                            <?php if ($headerItemDisabled($item)): ?>
	                                            <span class="aa-public-site-mega-link is-disabled" aria-disabled="true"><?= esc($headerItemLabel($item)) ?> <span class="aa-public-site-soon-pill"><?= esc((string) ($item['badge'] ?? 'Soon')) ?></span></span>
                                            <?php else: ?>
	                                            <a class="aa-public-site-mega-link" href="<?= esc($headerItemUrl($item), 'attr') ?>"><?= esc($headerItemLabel($item)) ?></a>
                                            <?php endif ?>
	                                    <?php endforeach ?>
	                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
            <a class="aa-public-site-nav-link <?= $active === 'plans' ? 'is-active' : '' ?>" href="<?= esc($plansUrl, 'attr') ?>">
                <?= $headerIcon('crown') ?>
                <span>Untuk Bisnis</span>
            </a>
            <a class="aa-public-site-nav-link <?= $active === 'creator' ? 'is-active' : '' ?>" href="<?= esc($creatorUrl, 'attr') ?>">
                <?= $headerIcon('user') ?>
                <span>Creator</span>
            </a>
            <a class="aa-public-site-promo-link <?= $active === 'plans' ? 'is-active' : '' ?>" href="<?= esc($plansUrl, 'attr') ?>">
                <img class="aa-public-site-promo-flag" src="<?= aa_asset_url('assets/img/animated-flag-indonesia.gif') ?>" alt="" loading="lazy" decoding="async">
                <span>PROMO KEMERDEKAAN</span>
            </a>
        </nav>

        <div class="aa-public-site-actions">
            <button class="aa-public-site-icon-btn" type="button" aria-label="Cari template" title="Cari template" aria-expanded="false" data-aa-public-search-toggle><?= $headerIcon('search', 'h-5 w-5') ?></button>
            <?php if ($isHomeHeader): ?>
                <button class="aa-public-site-icon-btn" type="button" data-home-theme-toggle aria-label="Ubah tema tampilan" title="Ubah tema">
                    <svg class="aa-home-theme-moon h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.9 14.6A8.5 8.5 0 0 1 9.4 3.1 8.5 8.5 0 1 0 20.9 14.6Z"></path></svg>
                    <svg class="aa-home-theme-sun h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path></svg>
                </button>
            <?php else: ?>
                <?= view('components/public_theme_toggle') ?>
            <?php endif ?>
            <a class="aa-public-site-premium" href="<?= esc($plansUrl, 'attr') ?>"><?= $headerIcon('crown') ?><span>Go Premium</span></a>
            <a class="aa-public-site-icon-btn" href="<?= esc($accountUrl, 'attr') ?>" aria-label="<?= $isLoggedIn ? 'Buka dashboard' : 'Login akun' ?>" title="<?= $isLoggedIn ? 'Dashboard' : 'Login' ?>"><?= $headerIcon('user', 'h-5 w-5') ?></a>
        </div>

        <div class="aa-public-site-mobile">
            <?php if ($isHomeHeader): ?>
                <button class="aa-public-site-icon-btn" type="button" data-home-theme-toggle aria-label="Ubah tema tampilan" title="Ubah tema">
                    <svg class="aa-home-theme-moon h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.9 14.6A8.5 8.5 0 0 1 9.4 3.1 8.5 8.5 0 1 0 20.9 14.6Z"></path></svg>
                    <svg class="aa-home-theme-sun h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path></svg>
                </button>
            <?php else: ?>
                <?= view('components/public_theme_toggle') ?>
            <?php endif ?>
            <a class="aa-public-site-icon-btn" href="<?= esc($accountUrl, 'attr') ?>" aria-label="<?= $isLoggedIn ? 'Buka dashboard' : 'Login akun' ?>" title="<?= $isLoggedIn ? 'Dashboard' : 'Login' ?>"><?= $headerIcon('user', 'h-5 w-5') ?></a>
            <button class="aa-public-site-icon-btn" type="button" aria-label="Cari template" title="Cari template" aria-expanded="false" data-aa-public-search-toggle><?= $headerIcon('search', 'h-5 w-5') ?></button>
            <button class="aa-public-site-icon-btn" type="button" aria-label="Buka menu" aria-expanded="false" data-aa-public-site-toggle><?= $headerIcon('menu', 'h-5 w-5') ?></button>
        </div>
    </div>

    <div class="aa-public-site-search-panel" data-aa-public-search-panel>
        <form class="aa-public-site-search-form" action="<?= esc($templatesUrl, 'attr') ?>" method="get" role="search">
            <input class="aa-public-site-search-input" type="search" name="q" maxlength="80" placeholder="Cari template acara..." data-aa-public-search-input>
            <button class="aa-public-site-search-submit" type="submit">Cari</button>
        </form>
        <div class="aa-public-site-search-suggestions">
            <a href="<?= esc($templatesUrl, 'attr') ?>?q=pernikahan%20floral">Pernikahan floral</a>
            <a href="<?= esc($templatesUrl, 'attr') ?>?q=ulang%20tahun%20anak">Ulang tahun anak</a>
            <a href="<?= esc($templatesUrl, 'attr') ?>?q=premium%20elegan">Premium elegan</a>
        </div>
    </div>

        <div class="aa-public-site-mobile-panel" data-aa-public-site-panel>
        <div class="aa-public-site-mobile-view is-active" data-aa-public-site-view="root">
            <a class="aa-public-site-mobile-row aa-public-site-mobile-premium" href="<?= esc($plansUrl, 'attr') ?>"><span><?= $headerIcon('crown') ?>Go Premium</span></a>
            <?php foreach ($headerMenuGroups as $key => $group): ?>
                <button class="aa-public-site-mobile-row" type="button" data-aa-public-site-open="<?= esc($key, 'attr') ?>">
                    <span><?= $headerIcon((string) $group['icon']) ?><?= esc((string) $group['label']) ?></span>
                    <?= $headerIcon('chevron') ?>
                </button>
            <?php endforeach ?>
            <a class="aa-public-site-mobile-row" href="<?= esc($plansUrl, 'attr') ?>"><span><?= $headerIcon('crown') ?>Untuk Bisnis</span></a>
            <a class="aa-public-site-mobile-row" href="<?= esc($creatorUrl, 'attr') ?>"><span><?= $headerIcon('user') ?>Creator</span></a>
            <a class="aa-public-site-mobile-row aa-public-site-mobile-promo" href="<?= esc($plansUrl, 'attr') ?>"><span><img class="aa-public-site-promo-flag" src="<?= aa_asset_url('assets/img/animated-flag-indonesia.gif') ?>" alt="" loading="lazy" decoding="async">PROMO KEMERDEKAAN</span></a>
            <?php if (! $isLoggedIn): ?>
                <a class="aa-public-site-mobile-row aa-public-site-mobile-auth" href="<?= site_url('login') ?>"><span><?= $headerIcon('login') ?>Login</span></a>
                <a class="aa-public-site-mobile-row aa-public-site-mobile-auth" href="<?= site_url('register') ?>"><span><?= $headerIcon('register') ?>Daftar</span></a>
            <?php endif ?>
        </div>
        <?php foreach ($headerMenuGroups as $key => $group): ?>
            <div class="aa-public-site-mobile-view" data-aa-public-site-view="<?= esc($key, 'attr') ?>">
                <button class="aa-public-site-mobile-back" type="button" data-aa-public-site-back><?= $headerIcon('back') ?><span>Semua</span></button>
                <div class="aa-public-site-mobile-category"><?= $headerIcon((string) $group['icon']) ?><span><?= esc((string) $group['label']) ?></span></div>
	                <?php foreach ($group['columns'] as $title => $items): ?>
	                    <div class="aa-public-site-mobile-subtitle"><?= esc((string) $title) ?></div>
	                    <?php foreach ($items as $item): ?>
                            <?php if ($headerItemDisabled($item)): ?>
	                            <span class="aa-public-site-mobile-subitem is-disabled" aria-disabled="true"><?= esc($headerItemLabel($item)) ?> <span class="aa-public-site-soon-pill"><?= esc((string) ($item['badge'] ?? 'Soon')) ?></span></span>
                            <?php else: ?>
	                            <a class="aa-public-site-mobile-subitem" href="<?= esc($headerItemUrl($item), 'attr') ?>"><?= esc($headerItemLabel($item)) ?></a>
                            <?php endif ?>
	                    <?php endforeach ?>
	                <?php endforeach ?>
            </div>
        <?php endforeach ?>
    </div>
</header>
<script>
    (function() {
        if (window.__aaPublicSiteHeaderReady) return;
        window.__aaPublicSiteHeaderReady = true;

        function setView(header, view) {
            header.querySelectorAll('[data-aa-public-site-view]').forEach(function(item) {
                item.classList.toggle('is-active', item.dataset.aaPublicSiteView === view);
            });
        }

        function setSearch(header, open) {
            header.classList.toggle('is-search-open', open);
            header.querySelectorAll('[data-aa-public-search-toggle]').forEach(function(button) {
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            if (open) {
                const input = header.querySelector('[data-aa-public-search-input]');
                if (input) window.setTimeout(function() { input.focus(); }, 40);
            }
        }

        document.addEventListener('click', function(event) {
            const searchToggle = event.target.closest('[data-aa-public-search-toggle]');
            if (searchToggle) {
                const header = searchToggle.closest('[data-aa-public-site-header]');
                if (!header) return;
                const open = !header.classList.contains('is-search-open');
                header.classList.remove('is-mobile-open');
                const menuButton = header.querySelector('[data-aa-public-site-toggle]');
                if (menuButton) menuButton.setAttribute('aria-expanded', 'false');
                setSearch(header, open);
                return;
            }

            const toggle = event.target.closest('[data-aa-public-site-toggle]');
            if (toggle) {
                const header = toggle.closest('[data-aa-public-site-header]');
                if (!header) return;
                const open = !header.classList.contains('is-mobile-open');
                header.classList.toggle('is-mobile-open', open);
                setSearch(header, false);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                setView(header, 'root');
                return;
            }

            const opener = event.target.closest('[data-aa-public-site-open]');
            if (opener) {
                const header = opener.closest('[data-aa-public-site-header]');
                if (header) setView(header, opener.dataset.aaPublicSiteOpen || 'root');
                return;
            }

            const back = event.target.closest('[data-aa-public-site-back]');
            if (back) {
                const header = back.closest('[data-aa-public-site-header]');
                if (header) setView(header, 'root');
                return;
            }

            document.querySelectorAll('[data-aa-public-site-header].is-mobile-open').forEach(function(header) {
                if (!event.target.closest('[data-aa-public-site-header]')) {
                    header.classList.remove('is-mobile-open');
                    const menuButton = header.querySelector('[data-aa-public-site-toggle]');
                    if (menuButton) menuButton.setAttribute('aria-expanded', 'false');
                    setView(header, 'root');
                }
            });

            document.querySelectorAll('[data-aa-public-site-header].is-search-open').forEach(function(header) {
                if (!event.target.closest('[data-aa-public-site-header]')) {
                    setSearch(header, false);
                }
            });
        });
    })();
</script>
