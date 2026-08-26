<?php
    helper('admin_permission');

    $adminTitle = (string) ($adminTitle ?? 'Dashboard');
    $adminKicker = (string) ($adminKicker ?? 'Admin');
    $adminIcon = (string) ($adminIcon ?? 'dashboard');
    $adminActive = (string) ($adminActive ?? 'dashboard');

    $aaAdminIcon = static function (string $name, string $class = 'h-5 w-5'): string {
        $icons = [
            'dashboard' => '<rect x="4" y="4" width="7" height="7" rx="2"/><rect x="13" y="4" width="7" height="7" rx="2"/><rect x="4" y="13" width="7" height="7" rx="2"/><rect x="13" y="13" width="7" height="7" rx="2"/>',
            'orders' => '<path d="M7 7h14l-2 8H8L6 3H3"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'pages' => '<path d="M6 2h9l5 5v15H6V2Z"/><path d="M14 2v6h6M9 13h6M9 17h6"/>',
            'book' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21V5.5Z"/><path d="M4 5.5V21"/>',
            'image' => '<rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="8.5" cy="10" r="1.7"/><path d="m21 15-4.5-4.5L8 19"/><path d="m12 15 2.5-2.5L21 19"/>',
            'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18"/><path d="M12 3a14 14 0 0 0 0 18"/>',
            'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/><path d="M10 13v2h4v-2"/>',
            'template' => '<rect x="3" y="4" width="18" height="16" rx="3"/><path d="M3 10h18M9 10v10"/>',
            'userdash' => '<path d="M4 5h16M4 12h16M4 19h16"/><circle cx="8" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="16" cy="19" r="1"/>',
            'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
            'money' => '<rect x="3" y="6" width="18" height="12" rx="3"/><circle cx="12" cy="12" r="3"/><path d="M6 9v.01M18 15v.01"/>',
            'review' => '<path d="M9 11l2 2 4-5"/><path d="M4 4h16v16H4z"/>',
            'ads' => '<path d="M4 10v4a2 2 0 0 0 2 2h2l4 4v-4h2l6 3V5l-6 3H6a2 2 0 0 0-2 2Z"/><path d="M14 8v8"/>',
            'fonts' => '<path d="M4 20h16"/><path d="M7 20 12 4l5 16"/><path d="M9 14h6"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'ai' => '<path d="M12 2l1.7 5.1L19 9l-5.3 1.9L12 16l-1.7-5.1L5 9l5.3-1.9L12 2Z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14Z"/><path d="M5 15l.7 1.8L8 17.5l-2.3.7L5 20l-.7-1.8L2 17.5l2.3-.7L5 15Z"/>',
            'legal' => '<path d="M6 3h9l5 5v13H6V3Z"/><path d="M14 3v6h6"/><path d="M9 14h6M9 18h4"/><path d="M9 10h2"/>',
            'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'logout' => '<path d="M14 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/><path d="M9 12h12m0 0-3-3m3 3-3 3"/>',
        ];

        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['dashboard']) . '</svg>';
    };

    $adminBadges = $adminBadges ?? null;
    if (! is_array($adminBadges)) {
        $adminBadges = [
            'orders' => 0,
            'users' => 0,
            'pages' => 0,
            'guestbooks' => 0,
            'guestMemories' => 0,
            'photoboothDomains' => 0,
            'businessProfileOrders' => 0,
            'publishDomains' => 0,
            'templates' => 0,
            'sellerTemplates' => 0,
            'creatorApplications' => 0,
            'withdraws' => 0,
            'creatorRoyalties' => 0,
        ];

        try {
            $db = db_connect();
            $today = date('Y-m-d 00:00:00');
            if ($db->tableExists('orders')) {
                $adminBadges['orders'] = (int) $db->table('orders')->where('status', 'waiting_approval')->countAllResults();
            }
            if ($db->tableExists('users') && in_array('created_at', $db->getFieldNames('users'), true)) {
                $adminBadges['users'] = (int) $db->table('users')->where('created_at >=', $today)->countAllResults();
            }
            if ($db->tableExists('landing_pages') && in_array('created_at', $db->getFieldNames('landing_pages'), true)) {
                $adminBadges['pages'] = (int) $db->table('landing_pages')->where('created_at >=', $today)->countAllResults();
            }
            if ($db->tableExists('guest_books')) {
                $adminBadges['guestbooks'] = (int) $db->table('guest_books')->where('created_at >=', $today)->countAllResults();
            }
            if ($db->tableExists('guest_memories')) {
                $adminBadges['guestMemories'] = (int) $db->table('guest_memories')->where('status', 'pending')->countAllResults();
            }
            if ($db->tableExists('photobooth_custom_domains')) {
                $adminBadges['photoboothDomains'] = (int) $db->table('photobooth_custom_domains')
                    ->whereIn('status', ['checking', 'available', 'waiting_payment', 'waiting_activation'])
                    ->countAllResults();
            }
            if ($db->tableExists('business_profile_orders')) {
                $adminBadges['businessProfileOrders'] = (int) $db->table('business_profile_orders')
                    ->where('status', 'waiting_approval')
                    ->countAllResults();
            }
            if ($db->tableExists('published_domains')) {
                $adminBadges['publishDomains'] = (int) $db->table('published_domains')
                    ->whereIn('status', ['pending_activation', 'activating', 'failed'])
                    ->countAllResults();
            }
            if ($db->tableExists('templates') && in_array('review_status', $db->getFieldNames('templates'), true)) {
                $adminBadges['sellerTemplates'] = (int) $db->table('templates')->where('review_status', 'pending')->countAllResults();
            }
            if ($db->tableExists('creator_applications')) {
                $adminBadges['creatorApplications'] = (int) $db->table('creator_applications')->where('status', 'pending')->countAllResults();
            }
            if ($db->tableExists('seller_withdraw_requests')) {
                $adminBadges['withdraws'] = (int) $db->table('seller_withdraw_requests')->where('status', 'pending')->countAllResults();
            }
            if ($db->tableExists('creator_template_royalties')) {
                $adminBadges['creatorRoyalties'] = (int) $db->table('creator_template_royalties')->where('status', 'pending')->countAllResults();
            }
        } catch (\Throwable $exception) {
            $adminBadges = array_map(static fn () => 0, $adminBadges);
        }
    }

    $aaAdminBadge = static function (int $count): string {
        if ($count <= 0) {
            return '';
        }

        return '<span class="ml-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 py-0.5 text-[10px] font-black leading-none text-white">' . esc((string) $count) . '</span>';
    };

    $aaAdminNavClass = static function (string $key) use ($adminActive): string {
        $base = 'inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-bold transition whitespace-nowrap';
        return $base . ($adminActive === $key
            ? ' bg-emerald-700 text-white shadow-lg shadow-emerald-700/20'
            : ' border border-emerald-100 bg-white text-slate-900 hover:border-emerald-600 hover:text-emerald-700');
    };

    $aaAdminMenuItems = [
        ['key' => 'dashboard', 'href' => site_url('admin'), 'icon' => 'dashboard', 'label' => 'Dashboard', 'badge' => (int) ($adminBadges['dashboard'] ?? 0), 'permission' => 'admin.dashboard.view', 'feature' => 'dashboard', 'mode' => 'locked'],
        ['key' => 'orders', 'href' => site_url('admin/orders'), 'icon' => 'orders', 'label' => 'Orders', 'badge' => (int) ($adminBadges['orders'] ?? 0), 'permission' => 'admin.orders.view', 'feature' => 'orders', 'mode' => 'locked'],
        ['key' => 'payment', 'href' => site_url('admin/payment-settings'), 'icon' => 'money', 'label' => 'Payment Key', 'badge' => 0, 'permission' => 'admin.payment_keys.manage', 'feature' => 'settings', 'mode' => 'hidden'],
        ['key' => 'editorAi', 'href' => site_url('admin/editor-ai-settings'), 'icon' => 'ai', 'label' => 'API Key', 'badge' => 0, 'permission' => 'admin.api_keys.manage', 'feature' => 'settings', 'mode' => 'hidden'],
        ['key' => 'legalDocuments', 'href' => site_url('admin/legal-documents'), 'icon' => 'legal', 'label' => 'Legalitas Perusahaan', 'badge' => 0, 'permission' => 'admin.settings.sensitive', 'feature' => 'settings', 'mode' => 'hidden'],
        ['key' => 'editorAds', 'href' => site_url('admin/editor-ads'), 'icon' => 'ads', 'label' => 'Iklan Editor', 'badge' => 0, 'permission' => 'admin.assets.view', 'feature' => 'assets', 'mode' => 'locked'],
        ['key' => 'indexnow', 'href' => site_url('admin/indexnow'), 'icon' => 'search', 'label' => 'System Settings', 'badge' => 0, 'permission' => 'admin.settings.sensitive', 'feature' => 'settings', 'mode' => 'hidden'],
        ['key' => 'customFonts', 'href' => site_url('admin/custom-fonts'), 'icon' => 'fonts', 'label' => 'Fonts', 'badge' => 0, 'permission' => 'admin.assets.view', 'feature' => 'assets', 'mode' => 'locked'],
        ['key' => 'users', 'href' => site_url('admin/users'), 'icon' => 'users', 'label' => 'Users', 'badge' => (int) ($adminBadges['users'] ?? 0), 'permission' => 'admin.users.view', 'feature' => 'users', 'mode' => 'locked'],
        ['key' => 'pages', 'href' => site_url('admin/pages'), 'icon' => 'pages', 'label' => 'Pages', 'badge' => (int) ($adminBadges['pages'] ?? 0), 'permission' => 'admin.pages.view', 'feature' => 'pages', 'mode' => 'locked'],
        ['key' => 'guestbooks', 'href' => site_url('admin/guestbooks'), 'icon' => 'book', 'label' => 'Guestbooks', 'badge' => (int) ($adminBadges['guestbooks'] ?? 0), 'permission' => 'admin.guestbooks.view', 'feature' => 'guestbooks', 'mode' => 'locked'],
        ['key' => 'guestMemories', 'href' => site_url('admin/guest-memories'), 'icon' => 'image', 'label' => 'Guest Memories', 'badge' => (int) ($adminBadges['guestMemories'] ?? 0), 'permission' => 'admin.guest_memories.view', 'feature' => 'guest-memories', 'mode' => 'locked'],
        ['key' => 'photoboothDomains', 'href' => site_url('admin/photobooth-domains'), 'icon' => 'globe', 'label' => 'Domain Photobooth', 'badge' => (int) ($adminBadges['photoboothDomains'] ?? 0), 'permission' => 'admin.photobooth_domains.view', 'feature' => 'photobooth-domains', 'mode' => 'locked'],
        ['key' => 'businessProfileOrders', 'href' => site_url('admin/business-profile-orders'), 'icon' => 'briefcase', 'label' => 'Order Business Profile', 'badge' => (int) ($adminBadges['businessProfileOrders'] ?? 0), 'permission' => 'admin.orders.view', 'feature' => 'orders', 'mode' => 'locked'],
        ['key' => 'publishDomains', 'href' => site_url('admin/publish-requests'), 'icon' => 'globe', 'label' => 'Publish Requests', 'badge' => (int) ($adminBadges['publishDomains'] ?? 0), 'permission' => 'admin.publish_domains.view', 'feature' => 'publish-domains', 'mode' => 'locked'],
        ['key' => 'templates', 'href' => site_url('admin/templates'), 'icon' => 'template', 'label' => 'Templates', 'badge' => (int) ($adminBadges['templates'] ?? 0), 'permission' => 'admin.templates.view', 'feature' => 'templates', 'mode' => 'locked'],
        ['key' => 'templateSubcategories', 'href' => site_url('admin/template-subcategories'), 'icon' => 'template', 'label' => 'Subkategori', 'badge' => 0, 'permission' => 'admin.templates.view', 'feature' => 'templates', 'mode' => 'locked'],
        ['key' => 'sellerTemplates', 'href' => site_url('admin/seller-templates'), 'icon' => 'review', 'label' => 'Review Seller', 'badge' => (int) ($adminBadges['sellerTemplates'] ?? 0), 'permission' => 'admin.templates.review', 'feature' => 'templates', 'mode' => 'locked'],
        ['key' => 'creatorApplications', 'href' => site_url('admin/creator-applications'), 'icon' => 'users', 'label' => 'Review Creator', 'badge' => (int) ($adminBadges['creatorApplications'] ?? 0), 'permission' => 'admin.templates.review', 'feature' => 'templates', 'mode' => 'locked'],
        ['key' => 'withdraws', 'href' => site_url('admin/seller-withdraw-requests'), 'icon' => 'money', 'label' => 'Withdraw', 'badge' => (int) ($adminBadges['withdraws'] ?? 0), 'permission' => 'admin.withdraw.view', 'feature' => 'withdraw', 'mode' => 'locked'],
        ['key' => 'creatorRoyalties', 'href' => site_url('admin/creator-royalties'), 'icon' => 'money', 'label' => 'Creator Royalty', 'badge' => (int) ($adminBadges['creatorRoyalties'] ?? 0), 'permission' => 'admin.withdraw.view', 'feature' => 'withdraw', 'mode' => 'locked'],
    ];

    $activeAdminMenuLabel = 'Menu Admin';
    foreach ($aaAdminMenuItems as $item) {
        if ($adminActive === $item['key']) {
            $activeAdminMenuLabel = $item['label'];
            break;
        }
    }

    $aaAdminMenuGroups = [
        'Overview' => ['dashboard'],
        'Operasional' => ['orders', 'businessProfileOrders', 'users', 'pages', 'guestbooks', 'guestMemories', 'photoboothDomains', 'publishDomains'],
        'Template' => ['templates', 'templateSubcategories', 'sellerTemplates', 'creatorApplications'],
        'Monetisasi' => ['withdraws', 'creatorRoyalties', 'payment'],
        'Sistem' => ['editorAi', 'customFonts', 'editorAds', 'legalDocuments', 'indexnow'],
    ];

    $aaAdminMenuByKey = [];
    foreach ($aaAdminMenuItems as $item) {
        $aaAdminMenuByKey[(string) $item['key']] = $item;
    }

    $aaRenderAdminMenuItem = static function (array $item) use ($adminActive, $aaAdminIcon): string {
        $canAccess = admin_can((string) $item['permission']);
        if (! $canAccess && ($item['mode'] ?? 'locked') === 'hidden') {
            return '';
        }

        $key = (string) $item['key'];
        $href = $canAccess ? (string) $item['href'] : admin_access_denied_url((string) $item['feature']);
        $isLocked = ! $canAccess;
        $badge = (int) ($item['badge'] ?? 0);
        $label = (string) $item['label'];
        $classes = 'aa-admin-side-link' . ($adminActive === $key ? ' is-active' : '') . ($isLocked ? ' is-locked admin-menu-locked' : '');

        $html = '<a class="' . esc($classes, 'attr') . '" href="' . esc($href, 'attr') . '" title="' . esc($isLocked ? 'Akses terbatas' : $label, 'attr') . '">';
        $html .= $aaAdminIcon((string) $item['icon'], 'h-4 w-4') . '<span class="aa-admin-side-label">' . esc($label) . '</span>';
        if ($isLocked) {
            $html .= '<span class="aa-admin-menu-lock lock-icon">' . $aaAdminIcon('lock', 'h-3 w-3') . '</span>';
        }
        if ($badge > 0) {
            $html .= '<span class="aa-admin-menu-badge">' . esc((string) $badge) . '</span>';
        }
        $html .= '</a>';

        return $html;
    };
?>
<style>
    .aa-admin-shell {
        --aa-admin-sidebar-width: 280px;
        --aa-admin-topbar-height: 72px;
        color: #0f172a;
    }

    html.aa-admin-menu-open,
    html.aa-admin-menu-open body {
        overflow: hidden;
    }

    .aa-admin-sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 50;
        width: var(--aa-admin-sidebar-width);
        border-right: 1px solid rgba(187, 247, 208, .9);
        background: rgba(255, 255, 255, .96);
        box-shadow: 24px 0 70px rgba(15, 23, 42, .08);
        transform: translateX(-104%);
        transition: transform .2s ease;
    }

    .aa-admin-shell.is-open .aa-admin-sidebar {
        transform: translateX(0);
    }

    .aa-admin-sidebar-inner {
        display: grid;
        grid-template-rows: auto 1fr auto;
        height: 100%;
        min-height: 0;
        padding: 16px;
    }

    .aa-admin-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 24px;
        background: linear-gradient(135deg, #ecfdf5, #fffbeb);
        padding: 12px;
        text-decoration: none;
    }

    .aa-admin-brand-mark,
    .aa-admin-page-icon {
        display: inline-grid;
        place-items: center;
        border-radius: 18px;
        background: #047857;
        color: #ffffff;
        box-shadow: 0 14px 34px rgba(4, 120, 87, .22);
    }

    .aa-admin-brand-mark {
        height: 44px;
        width: 44px;
    }

    .aa-admin-page-icon {
        height: 42px;
        width: 42px;
        flex-shrink: 0;
    }

    .aa-admin-side-scroll {
        min-height: 0;
        overflow-y: auto;
        padding: 14px 2px;
    }

    .aa-admin-side-group + .aa-admin-side-group {
        margin-top: 18px;
    }

    .aa-admin-side-heading {
        margin: 0 0 7px;
        padding: 0 10px;
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .aa-admin-side-list {
        display: grid;
        gap: 5px;
    }

    .aa-admin-side-link {
        display: flex;
        min-height: 43px;
        align-items: center;
        gap: 10px;
        border: 1px solid transparent;
        border-radius: 17px;
        color: #334155;
        padding: 0 11px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        transition: .16s ease;
    }

    .aa-admin-side-link:hover {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #047857;
    }

    .aa-admin-side-link.is-active {
        border-color: #047857;
        background: #047857;
        color: #ffffff;
        box-shadow: 0 15px 30px rgba(4, 120, 87, .18);
    }

    .aa-admin-side-link.is-locked,
    .admin-menu-locked {
        opacity: .56;
        cursor: pointer;
        filter: grayscale(.2);
    }

    .aa-admin-side-link svg {
        flex-shrink: 0;
    }

    .aa-admin-side-label {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-admin-menu-lock {
        margin-left: auto;
        color: currentColor;
    }

    .aa-admin-menu-badge {
        margin-left: auto;
        display: inline-flex;
        min-width: 20px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #e11d48;
        color: #ffffff;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 950;
        line-height: 1;
    }

    .aa-admin-side-link.is-active .aa-admin-menu-badge {
        background: #ffffff;
        color: #047857;
    }

    .aa-admin-sidebar-footer {
        display: grid;
        gap: 8px;
        border-top: 1px solid #dcfce7;
        padding-top: 12px;
    }

    .aa-admin-topbar {
        position: sticky;
        top: 0;
        z-index: 35;
        border-bottom: 1px solid rgba(187, 247, 208, .85);
        background: rgba(255, 255, 255, .9);
        backdrop-filter: blur(18px);
    }

    .aa-admin-topbar-inner {
        display: flex;
        min-height: var(--aa-admin-topbar-height);
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
    }

    .aa-admin-title-wrap {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 12px;
    }

    .aa-admin-kicker {
        margin: 0;
        color: #047857;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .2em;
        text-transform: uppercase;
    }

    .aa-admin-title {
        margin: 0;
        overflow: hidden;
        color: #0f172a;
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -.03em;
        line-height: 1.1;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-admin-actions {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .aa-admin-nav-button,
    .aa-admin-user-dashboard,
    .aa-admin-logout-button {
        display: inline-flex;
        height: 42px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 16px;
        background: #ffffff;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 950;
        text-decoration: none;
        transition: .16s ease;
        white-space: nowrap;
    }

    .aa-admin-nav-button,
    .aa-admin-user-dashboard {
        border: 1px solid #bbf7d0;
        color: #0f172a;
    }

    .aa-admin-nav-button:hover,
    .aa-admin-user-dashboard:hover {
        border-color: #047857;
        color: #047857;
        transform: translateY(-1px);
    }

    .aa-admin-logout-form {
        flex-shrink: 0;
    }

    .aa-admin-logout-button {
        border: 1px solid #fee2e2;
        color: #b91c1c;
    }

    .aa-admin-logout-button:hover {
        border-color: #fecaca;
        background: #fff1f2;
        transform: translateY(-1px);
    }

    .aa-admin-overlay {
        position: fixed;
        inset: 0;
        z-index: 45;
        background: rgba(15, 23, 42, .36);
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease;
    }

    .aa-admin-shell.is-open .aa-admin-overlay {
        opacity: 1;
        pointer-events: auto;
    }

    .aa-admin-desktop-only {
        display: none;
    }

    @media (min-width: 1024px) {
        body.aa-app-ui {
            padding-left: var(--aa-admin-sidebar-width, 280px);
        }

        .aa-admin-sidebar {
            inset: 0 auto 0 0;
            transform: translateX(0);
        }

        .aa-admin-overlay,
        .aa-admin-nav-button {
            display: none;
        }

        .aa-admin-desktop-only {
            display: inline;
        }

        .aa-admin-topbar {
            margin-left: 0;
        }

        body.aa-app-ui > main {
            margin-left: 0 !important;
            max-width: none !important;
            width: auto !important;
            padding-left: 28px !important;
            padding-right: 28px !important;
        }
    }

    @media (max-width: 1023px) {
        .aa-admin-sidebar {
            inset: var(--aa-admin-topbar-height) auto 0 0;
            border-top: 1px solid rgba(187, 247, 208, .9);
            border-top-right-radius: 26px;
            box-shadow: 20px 24px 70px rgba(15, 23, 42, .16);
        }

        .aa-admin-overlay {
            top: var(--aa-admin-topbar-height);
        }
    }

    @media (max-width: 640px) {
        .aa-admin-shell {
            --aa-admin-topbar-height: 66px;
        }

        .aa-admin-sidebar {
            width: min(292px, calc(100vw - 42px));
        }

        .aa-admin-topbar-inner {
            min-height: var(--aa-admin-topbar-height);
            padding-inline: 12px;
        }

        .aa-admin-page-icon {
            display: none;
        }

        .aa-admin-title {
            max-width: 38vw;
            font-size: 19px;
        }

        .aa-admin-kicker,
        .aa-admin-desktop-label {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        .aa-admin-user-dashboard,
        .aa-admin-logout-button,
        .aa-admin-nav-button {
            width: 42px;
            padding-inline: 0;
            border-radius: 999px;
        }
    }
</style>
<div class="aa-admin-shell" data-aa-admin-shell>
    <div class="aa-admin-overlay" data-aa-admin-close aria-hidden="true"></div>
    <aside class="aa-admin-sidebar" aria-label="Navigasi Admin">
        <div class="aa-admin-sidebar-inner">
            <a class="aa-admin-brand" href="<?= site_url('admin') ?>">
                <span class="aa-admin-brand-mark"><?= $aaAdminIcon('dashboard', 'h-5 w-5') ?></span>
                <span class="min-w-0">
                    <span class="block text-sm font-black text-slate-950">AdaAcara Admin</span>
                    <span class="block truncate text-xs font-bold text-emerald-700">Control Center</span>
                </span>
            </a>
            <nav class="aa-admin-side-scroll">
                <?php foreach ($aaAdminMenuGroups as $groupLabel => $keys): ?>
                    <?php $groupHtml = ''; ?>
                    <?php foreach ($keys as $key): ?>
                        <?php if (isset($aaAdminMenuByKey[$key])): ?>
                            <?php $groupHtml .= $aaRenderAdminMenuItem($aaAdminMenuByKey[$key]); ?>
                        <?php endif ?>
                    <?php endforeach ?>
                    <?php if ($groupHtml !== ''): ?>
                        <section class="aa-admin-side-group">
                            <p class="aa-admin-side-heading"><?= esc((string) $groupLabel) ?></p>
                            <div class="aa-admin-side-list"><?= $groupHtml ?></div>
                        </section>
                    <?php endif ?>
                <?php endforeach ?>
            </nav>
            <div class="aa-admin-sidebar-footer">
                <a class="aa-admin-user-dashboard" href="<?= site_url('dashboard') ?>" title="User Dashboard">
                    <?= $aaAdminIcon('dashboard', 'h-4 w-4') ?><span>User Dashboard</span>
                </a>
                <form class="aa-admin-logout-form" action="<?= site_url('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="aa-admin-logout-button w-full" type="submit" aria-label="Logout" title="Logout">
                        <?= $aaAdminIcon('logout', 'h-4 w-4') ?><span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    <header class="aa-admin-topbar">
        <div class="aa-admin-topbar-inner">
            <div class="aa-admin-title-wrap">
                <button class="aa-admin-nav-button" type="button" aria-label="Buka menu admin" aria-expanded="false" data-aa-admin-toggle>
                    <?= $aaAdminIcon('menu', 'h-4 w-4') ?><span class="aa-admin-desktop-label">Menu</span>
                </button>
                <span class="aa-admin-page-icon"><?= $aaAdminIcon($adminIcon, 'h-5 w-5') ?></span>
                <div class="min-w-0">
                    <p class="aa-admin-kicker"><?= esc($adminKicker) ?></p>
                    <h1 class="aa-admin-title"><?= esc($adminTitle) ?></h1>
                </div>
            </div>
            <nav class="aa-admin-actions">
                <a class="aa-admin-user-dashboard" href="<?= site_url('dashboard') ?>" aria-label="User Dashboard" title="User Dashboard">
                    <?= $aaAdminIcon('dashboard', 'h-4 w-4') ?><span class="aa-admin-desktop-label aa-admin-desktop-only">User Dashboard</span>
                </a>
                <form class="aa-admin-logout-form" action="<?= site_url('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="aa-admin-logout-button" type="submit" aria-label="Logout" title="Logout">
                        <?= $aaAdminIcon('logout', 'h-4 w-4') ?><span class="aa-admin-desktop-label aa-admin-desktop-only">Logout</span>
                    </button>
                </form>
            </nav>
        </div>
    </header>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-aa-admin-shell]').forEach(function (shell) {
            const toggles = shell.querySelectorAll('[data-aa-admin-toggle]');
            const closers = shell.querySelectorAll('[data-aa-admin-close]');

            function setOpen(isOpen) {
                shell.classList.toggle('is-open', isOpen);
                toggles.forEach(function (toggle) {
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
                document.documentElement.classList.toggle('aa-admin-menu-open', isOpen);
            }

            toggles.forEach(function (toggle) {
                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    setOpen(!shell.classList.contains('is-open'));
                });
            });

            closers.forEach(function (closer) {
                closer.addEventListener('click', function () {
                    setOpen(false);
                });
            });

            shell.querySelectorAll('.aa-admin-side-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    setOpen(false);
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                setOpen(false);
            });
        });
    });
</script>
