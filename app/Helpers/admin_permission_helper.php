<?php

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

if (! function_exists('admin_permission_map')) {
    function admin_permission_map(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }

        $all = [
            'admin.dashboard.view',
            'admin.users.view',
            'admin.users.manage',
            'admin.users.change_role',
            'admin.users.delete',
            'admin.users.impersonate',
            'admin.orders.view',
            'admin.orders.manage',
            'admin.orders.approve',
            'admin.orders.delete',
            'admin.payments.view',
            'admin.payments.manage',
            'admin.payments.refund',
            'admin.payments.settings',
            'admin.withdraw.view',
            'admin.withdraw.approve',
            'admin.withdraw.reject',
            'admin.withdraw.manage',
            'admin.templates.view',
            'admin.templates.manage',
            'admin.templates.review',
            'admin.templates.delete',
            'admin.themes.view',
            'admin.themes.manage',
            'admin.pages.view',
            'admin.pages.manage',
            'admin.pages.delete',
            'admin.guestbooks.view',
            'admin.guestbooks.manage',
            'admin.guestbooks.delete',
            'admin.guest_memories.view',
            'admin.guest_memories.manage',
            'admin.guest_memories.delete',
            'admin.photobooth_domains.view',
            'admin.photobooth_domains.manage',
            'admin.publish_domains.view',
            'admin.publish_domains.manage',
            'admin.assets.view',
            'admin.assets.manage',
            'admin.assets.delete',
            'admin.reports.view',
            'admin.reports.finance',
            'admin.settings.view',
            'admin.settings.manage',
            'admin.settings.sensitive',
            'admin.api_keys.manage',
            'admin.payment_keys.manage',
        ];

        $map = [
            'superadmin' => array_fill_keys($all, true),
            'admin' => array_fill_keys([
                'admin.dashboard.view',
                'admin.users.view',
                'admin.users.manage',
                'admin.orders.view',
                'admin.orders.manage',
                'admin.templates.view',
                'admin.templates.manage',
                'admin.templates.review',
                'admin.themes.view',
                'admin.themes.manage',
                'admin.pages.view',
                'admin.pages.manage',
                'admin.guestbooks.view',
                'admin.guestbooks.manage',
                'admin.guest_memories.view',
                'admin.guest_memories.manage',
                'admin.guest_memories.delete',
                'admin.photobooth_domains.view',
                'admin.photobooth_domains.manage',
                'admin.publish_domains.view',
                'admin.publish_domains.manage',
                'admin.assets.view',
                'admin.assets.manage',
                'admin.reports.view',
            ], true),
            'finance_admin' => array_fill_keys([
                'admin.dashboard.view',
                'admin.orders.view',
                'admin.orders.manage',
                'admin.orders.approve',
                'admin.payments.view',
                'admin.payments.manage',
                'admin.photobooth_domains.view',
                'admin.publish_domains.view',
                'admin.withdraw.view',
                'admin.withdraw.approve',
                'admin.withdraw.reject',
                'admin.withdraw.manage',
                'admin.reports.view',
                'admin.reports.finance',
            ], true),
            'content_admin' => array_fill_keys([
                'admin.dashboard.view',
                'admin.templates.view',
                'admin.templates.manage',
                'admin.templates.review',
                'admin.themes.view',
                'admin.themes.manage',
                'admin.pages.view',
                'admin.pages.manage',
                'admin.guestbooks.view',
                'admin.guestbooks.manage',
                'admin.guest_memories.view',
                'admin.guest_memories.manage',
                'admin.guest_memories.delete',
                'admin.photobooth_domains.view',
                'admin.publish_domains.view',
                'admin.assets.view',
                'admin.assets.manage',
            ], true),
            'support_admin' => array_fill_keys([
                'admin.dashboard.view',
                'admin.users.view',
                'admin.orders.view',
                'admin.pages.view',
                'admin.guestbooks.view',
                'admin.guest_memories.view',
                'admin.photobooth_domains.view',
                'admin.publish_domains.view',
            ], true),
        ];

        return $map;
    }
}

if (! function_exists('admin_roles')) {
    function admin_roles(): array
    {
        return ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin'];
    }
}

if (! function_exists('admin_assignable_roles')) {
    function admin_assignable_roles(): array
    {
        return ['user', 'creator', 'admin', 'finance_admin', 'content_admin', 'support_admin'];
    }
}

if (! function_exists('current_admin_role')) {
    function current_admin_role(): string
    {
        $role = strtolower(trim((string) (session()->get('userRole') ?? '')));

        return $role !== '' ? $role : 'user';
    }
}

if (! function_exists('admin_is_superadmin')) {
    function admin_is_superadmin(): bool
    {
        return current_admin_role() === 'superadmin';
    }
}

if (! function_exists('admin_can')) {
    function admin_can(string $permission): bool
    {
        $permission = trim($permission);
        if ($permission === '') {
            return false;
        }

        $role = current_admin_role();
        $map = admin_permission_map();

        return ! empty($map[$role][$permission]);
    }
}

if (! function_exists('admin_allowed_roles_for_feature')) {
    function admin_allowed_roles_for_feature(string $featureKey): array
    {
        $permission = match ($featureKey) {
            'dashboard' => 'admin.dashboard.view',
            'users' => 'admin.users.view',
            'orders' => 'admin.orders.view',
            'payments' => 'admin.payments.view',
            'withdraw' => 'admin.withdraw.view',
            'templates', 'themes' => 'admin.templates.view',
            'pages' => 'admin.pages.view',
            'guestbooks' => 'admin.guestbooks.view',
            'guest-memories' => 'admin.guest_memories.view',
            'photobooth-domains' => 'admin.photobooth_domains.view',
            'publish-domains' => 'admin.publish_domains.view',
            'assets' => 'admin.assets.view',
            'reports' => 'admin.reports.view',
            'settings' => 'admin.settings.sensitive',
            default => '',
        };

        if ($permission === '') {
            return [];
        }

        return array_values(array_filter(admin_roles(), static function (string $role) use ($permission): bool {
            $map = admin_permission_map();

            return ! empty($map[$role][$permission]);
        }));
    }
}

if (! function_exists('admin_access_denied_url')) {
    function admin_access_denied_url(string $featureKey): string
    {
        $feature = preg_replace('/[^a-z0-9_-]+/i', '', strtolower($featureKey)) ?: 'dashboard';

        return site_url('admin/access-denied') . '?feature=' . rawurlencode($feature);
    }
}

if (! function_exists('admin_access_denied_response')) {
    function admin_access_denied_response(string $permission, string $featureKey = 'dashboard'): ResponseInterface|RedirectResponse
    {
        $message = 'Akses admin terbatas.';
        log_message('warning', 'Admin access denied. admin_id={admin_id} role={role} permission={permission} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'role' => current_admin_role(),
            'permission' => $permission,
            'ip' => service('request')->getIPAddress(),
        ]);

        if (service('request')->isAJAX()) {
            return service('response')->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => $message,
            ]);
        }

        return redirect()->to(admin_access_denied_url($featureKey))->with('error', $message);
    }
}

if (! function_exists('admin_require')) {
    function admin_require(string $permission, string $featureKey = 'dashboard'): ResponseInterface|RedirectResponse|null
    {
        if (admin_can($permission)) {
            return null;
        }

        return admin_access_denied_response($permission, $featureKey);
    }
}

if (! function_exists('admin_feature_messages')) {
    function admin_feature_messages(): array
    {
        return [
            'dashboard' => ['Akses Dashboard Terbatas', 'Dashboard admin hanya dapat diakses oleh role admin yang memiliki izin.'],
            'users' => ['Akses User Terbatas', 'Menu User hanya dapat diakses oleh superadmin, admin, dan support_admin dengan batasan tertentu.'],
            'orders' => ['Akses Order Terbatas', 'Menu Order hanya dapat diakses oleh superadmin, admin, finance_admin, dan support_admin untuk mode lihat.'],
            'payments' => ['Akses Pembayaran Terbatas', 'Menu Pembayaran hanya dapat diakses oleh superadmin dan finance_admin.'],
            'withdraw' => ['Akses Withdraw Terbatas', 'Menu Withdraw hanya dapat diakses oleh superadmin dan finance_admin.'],
            'templates' => ['Akses Template Terbatas', 'Menu Template hanya dapat diakses oleh superadmin, admin, dan content_admin.'],
            'pages' => ['Akses Halaman Terbatas', 'Menu Halaman hanya dapat diakses oleh superadmin, admin, content_admin, dan support_admin untuk mode lihat.'],
            'guestbooks' => ['Akses Guestbook Terbatas', 'Menu Guestbook hanya dapat diakses oleh superadmin, admin, content_admin, dan support_admin untuk mode lihat.'],
            'guest-memories' => ['Akses Guest Memories Terbatas', 'Menu Guest Memories hanya dapat diakses oleh role admin yang memiliki izin.'],
            'photobooth-domains' => ['Akses Domain Photobooth Terbatas', 'Menu Domain Photobooth hanya dapat diakses oleh role admin yang memiliki izin.'],
            'publish-domains' => ['Akses Publish Requests Terbatas', 'Menu Publish Requests hanya dapat diakses oleh role admin yang memiliki izin.'],
            'assets' => ['Akses Asset Terbatas', 'Menu Asset hanya dapat diakses oleh superadmin, admin, dan content_admin.'],
            'reports' => ['Akses Laporan Terbatas', 'Menu Laporan hanya dapat diakses oleh role yang memiliki izin laporan.'],
            'settings' => ['Akses Pengaturan Terbatas', 'Pengaturan sistem hanya dapat diakses oleh superadmin.'],
        ];
    }
}

if (! function_exists('admin_menu_item')) {
    function admin_menu_item(string $label, string $url, string $permission, string $featureKey, ?string $icon = null, string $mode = 'locked'): string
    {
        if (admin_can($permission)) {
            return '<a class="aa-admin-menu-link" href="' . esc(site_url($url), 'attr') . '" title="' . esc($label, 'attr') . '">' . esc($label) . '</a>';
        }

        if ($mode === 'hidden') {
            return '';
        }

        return '<a class="aa-admin-menu-link admin-menu-locked" href="' . esc(admin_access_denied_url($featureKey), 'attr') . '" title="Akses terbatas">' . esc($label) . '<span class="lock-icon" aria-hidden="true">Lock</span></a>';
    }
}

if (! function_exists('admin_sensitive_menu_item')) {
    function admin_sensitive_menu_item(string $label, string $url, string $permission, ?string $icon = null): string
    {
        return admin_menu_item($label, $url, $permission, 'settings', $icon, 'hidden');
    }
}
