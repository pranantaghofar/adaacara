<?php
    helper('aa_datetime');

    $dashboardNowWib = aa_datetime_wib('now');
    $activePlanKey = strtolower((string) ($activeSubscription['plan_slug'] ?? $activeSubscription['plan_name'] ?? ''));
    $activePlanName = match ($activePlanKey) {
        'basic', 'starter' => 'Buat Acara Sendiri',
        'premium' => 'Buat Coba Jualan',
        'business', 'busseniss' => 'Buat Niat Jualan',
        default => (string) ($activeSubscription['plan_name'] ?? 'Free'),
    };
    $canUseGuestMemories = ! empty($canUseGuestMemories);
    $photoboothInactiveTitle = 'Fitur Photobooth belum aktif. Minta admin mengaktifkan Guest Memories terlebih dahulu.';
    $aaIcon = static function (string $name, string $class = 'h-5 w-5'): string {
        $icons = [
            'logout' => '<path d="M14 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/><path d="M9 12h12m0 0-3-3m3 3-3 3"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'package' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="M4 7.5 12 12l8-4.5M12 12v9"/>',
            'template' => '<rect x="3" y="4" width="18" height="16" rx="3"/><path d="M3 10h18M9 10v10"/>',
            'order' => '<path d="M7 7h14l-2 8H8L6 3H3"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>',
            'link' => '<path d="M10 13a5 5 0 0 0 7.07 0l2.12-2.12a5 5 0 0 0-7.07-7.07L10.9 5.03"/><path d="M14 11a5 5 0 0 0-7.07 0L4.81 13.12a5 5 0 0 0 7.07 7.07l1.22-1.22"/>',
            'calendar' => '<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'camera' => '<path d="M4 8a2 2 0 0 1 2-2h2l1.5-2h5L16 6h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z"/><circle cx="12" cy="13" r="4"/>',
            'edit' => '<path d="M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3Z"/><path d="m14 7 3 3"/>',
            'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
            'book' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21V5.5Z"/><path d="M4 5.5V21"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/>',
            'external' => '<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/>',
            'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
            'more' => '<circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
            'sort' => '<path d="M7 4v16m0 0-3-3m3 3 3-3M17 20V4m0 0-3 3m3-3 3 3"/>',
            'shield' => '<path d="M12 3 5 6v5c0 4.4 2.8 8.3 7 10 4.2-1.7 7-5.6 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-5"/>',
            'sparkles' => '<path d="m12 3 1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3Z"/><path d="M19 14v4M21 16h-4M5 15v3M6.5 16.5h-3"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'money' => '<rect x="3" y="6" width="18" height="12" rx="3"/><circle cx="12" cy="12" r="3"/><path d="M6 9v.01M18 15v.01"/>',
            'share' => '<path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/><path d="M16 6 12 2 8 6"/><path d="M12 2v13"/>',
            'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',
            'check' => '<path d="m5 12 4 4L19 6"/>',
            'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
        ];
        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['package']) . '</svg>';
    };
    $aaSocialIcon = static function (string $name, string $class = 'h-4 w-4'): string {
        $icons = [
            'whatsapp' => '<path d="M12.04 3.5a8.48 8.48 0 0 0-7.22 12.95L4 20.5l4.15-1.02A8.5 8.5 0 1 0 12.04 3.5Z"/><path d="M8.9 8.35c.18-.36.34-.37.5-.37h.43c.14 0 .36.05.55.27.18.22.7.86.7 2.1 0 1.22-.72 2.4-.82 2.56-.1.14-1.4 2.24-3.48 3.05-1.72.67-2.07.54-2.44.5-.38-.04-1.2-.5-1.38-.98-.17-.48-.17-.9-.12-.98.05-.09.2-.14.4-.25.2-.12 1.2-.62 1.38-.7.18-.08.32-.13.46.1.14.22.53.72.66.86.12.14.24.16.44.05.2-.1.85-.32 1.62-1.02.6-.55 1-1.22 1.12-1.42.12-.21.01-.32-.09-.43-.09-.1-.2-.25-.3-.37-.1-.13-.14-.22-.22-.37-.07-.15-.03-.29.02-.4.05-.1.47-1.18.61-1.6Z"/>',
            'instagram' => '<rect x="5" y="5" width="14" height="14" rx="4"/><circle cx="12" cy="12" r="3.2"/><circle cx="16.55" cy="7.45" r=".75"/>',
            'facebook' => '<path d="M14 8.4h2.1V5.15A14.5 14.5 0 0 0 13.15 5C10.25 5 8.3 6.75 8.3 9.9v2.7H5.1v3.65h3.2V22h3.9v-5.75h3.15l.5-3.65H12.2v-2.35c0-1.05.29-1.85 1.8-1.85Z"/>',
            'threads' => '<path d="M17.95 11.2c-.32-3.9-2.46-6.2-5.94-6.2-3.55 0-6.08 2.62-6.08 6.87 0 4.24 2.56 7.13 6.43 7.13 3.06 0 5.15-1.68 5.15-4.04 0-2.23-1.72-3.55-4.62-3.72"/><path d="M14.7 14.52c0 .95-.78 1.6-2.03 1.6-1.34 0-2.2-.7-2.2-1.7 0-.98.85-1.62 2.2-1.62 1.28 0 2.03.63 2.03 1.72Z"/><path d="M10.18 8.9c.48-.62 1.16-.94 2.05-.94 1.06 0 1.8.43 2.2 1.27"/>',
            'x' => '<path d="M4.8 5h4.05l3.28 4.45L15.98 5h3.05l-5.48 6.3L20 19h-4.05l-3.6-4.88L8.13 19H5.08l5.85-6.72L4.8 5Z"/>',
        ];

        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['x']) . '</svg>';
    };
    $aaNotificationUrl = static function (array $notification): string {
        $data = json_decode((string) ($notification['data'] ?? ''), true);
        if (is_array($data) && ! empty($data['url'])) {
            return (string) $data['url'];
        }

        return site_url('dashboard');
    };
    $aaNotificationTime = static function (array $notification): string {
        $createdAt = aa_wib_timestamp($notification['created_at'] ?? '');
        if ($createdAt <= 0) {
            return 'Baru saja';
        }

        $now = aa_wib_timestamp('now') ?: time();
        $diff = max(0, $now - $createdAt);
        if ($diff < 60) {
            return 'Baru saja';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' menit lalu';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' jam lalu';
        }

        return aa_format_wib_date($notification['created_at'] ?? '', 'd M Y', 'Baru saja');
    };
    $aaDashboardThumbnail = static function (array $page): string {
        $value = trim((string) ($page['og_image'] ?? ''));
        if ($value === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }
        return base_url(ltrim($value, '/'));
    };
    $aaDashboardProjectSurface = static function (array $page): array {
        $normalizeType = static function (string $value): string {
            $value = strtolower(trim($value));
            return match ($value) {
                'photobooth', 'digital_photobooth' => 'photobooth',
                'business_profile', 'business-profile' => 'business_profile',
                default => 'invitation',
            };
        };

        $projectType = $normalizeType((string) ($page['project_type'] ?? ''));
        $data = json_decode((string) ($page['editor_json'] ?? $page['grapesjs_json'] ?? ''), true);
        if (is_array($data)) {
            $intent = $normalizeType((string) ($data['projectIntent'] ?? $data['project_intent'] ?? ''));
            if ($projectType === 'invitation' && $intent !== 'invitation') {
                $projectType = $intent;
            }
            $hasPhotoboothFrames = is_array($data['photoboothFrames'] ?? null) && count($data['photoboothFrames']) > 0;
        } else {
            $hasPhotoboothFrames = false;
        }

        $isBusinessProfile = $projectType === 'business_profile';
        $isPurePhotobooth = ! $isBusinessProfile && $projectType === 'photobooth';
        $isHybridPhotobooth = ! $isBusinessProfile && ! $isPurePhotobooth && $hasPhotoboothFrames;
        $hasPhotobooth = $isPurePhotobooth || $isHybridPhotobooth;
        $hasInvitationTools = ! $isBusinessProfile && ! $isPurePhotobooth;

        if ($isBusinessProfile) {
            $label = 'Business Profile';
            $tone = 'bg-cyan-50 text-cyan-800 ring-1 ring-cyan-100';
        } elseif ($isPurePhotobooth) {
            $label = 'Digital Photobooth';
            $tone = 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-100';
        } elseif ($isHybridPhotobooth) {
            $label = 'Undangan + Photobooth';
            $tone = 'bg-violet-50 text-violet-800 ring-1 ring-violet-100';
        } else {
            $label = 'Undangan Digital';
            $tone = 'bg-rose-50 text-rose-800 ring-1 ring-rose-100';
        }

        return [
            'type' => $projectType,
            'label' => $label,
            'tone' => $tone,
            'has_invitation_tools' => $hasInvitationTools,
            'has_photobooth' => $hasPhotobooth,
            'is_business_profile' => $isBusinessProfile,
            'is_pure_photobooth' => $isPurePhotobooth,
            'delete_subject' => $isBusinessProfile ? 'Business Profile' : ($isPurePhotobooth ? 'project Photobooth' : 'undangan'),
            'open_label' => $isBusinessProfile ? 'Buka Website' : ($isPurePhotobooth ? 'Buka Photobooth' : 'Buka Link'),
            'copy_label' => $isBusinessProfile ? 'Copy Link Website' : ($isPurePhotobooth ? 'Copy Link Photobooth' : 'Copy Link'),
            'edit_label' => $isBusinessProfile ? 'Edit Website' : ($isPurePhotobooth ? 'Edit Photobooth' : 'Edit'),
            'primary_draft_label' => $isBusinessProfile ? 'Lanjut Edit Website' : ($isPurePhotobooth ? 'Lanjut Edit Photobooth' : 'Lanjut Edit'),
            'public_empty' => $isBusinessProfile ? 'Link website belum aktif.' : ($isPurePhotobooth ? 'Link Photobooth belum aktif.' : 'Link public belum aktif.'),
        ];
    };
    $aaCardGradients = [
        'from-rose-100 via-pink-200 to-fuchsia-300',
        'from-sky-200 via-blue-200 to-cyan-100',
        'from-violet-100 via-slate-200 to-fuchsia-100',
        'from-violet-100 via-rose-200 to-fuchsia-400',
        'from-violet-200 via-indigo-200 to-sky-200',
        'from-violet-100 via-fuchsia-100 to-violet-200',
        'from-slate-200 via-zinc-200 to-stone-300',
        'from-fuchsia-50 via-rose-100 to-pink-100',
    ];
    $dashboardNotifications = is_array($dashboardNotifications ?? null) ? $dashboardNotifications : [];
    $dashboardUnreadCount = (int) ($dashboardUnreadCount ?? 0);
    $templateWishlists = is_array($templateWishlists ?? null) ? $templateWishlists : [];
    $activeSubscription = $activeSubscription ?? null;
    $pageCount = (int) ($pageCount ?? count($landingPages));
    $pageLimit = (int) ($pageLimit ?? 0);
    $isUnlimitedPageLimit = $pageLimit >= PHP_INT_MAX;
    $pageLimitLabel = $isUnlimitedPageLimit ? 'Unlimited' : ($pageLimit > 0 ? (string) $pageLimit : '-');
    $publishedCount = (int) ($publishedCount ?? 0);
    $draftCount = (int) ($draftCount ?? 0);
    $expiredCount = (int) ($expiredCount ?? 0);
    $totalGuestbookCount = (int) ($totalGuestbookCount ?? 0);
    $isDashboardLifetimeSubscription = static function ($subscription): bool {
        if (! is_array($subscription)) {
            return false;
        }

        if (((int) ($subscription['is_lifetime'] ?? 0)) === 1) {
            return true;
        }

        $expiredAt = strtotime((string) ($subscription['expired_at'] ?? ''));
        return $expiredAt !== false && $expiredAt >= strtotime('9999-01-01 00:00:00');
    };
    $dashboardExpiredRaw = $dashboardExpiredLabel ?? ($activeSubscription['expired_at'] ?? '');
    $dashboardExpiredLabel = $isDashboardLifetimeSubscription($activeSubscription)
        ? 'Selamanya'
        : aa_format_wib_datetime($dashboardExpiredRaw);
    $creatorStatus = $creatorStatus ?? ['status' => 'none', 'display_name' => null];
    $creatorFlowStatus = (string) ($creatorStatus['status'] ?? 'none');
    $creatorDisplayName = (string) ($creatorStatus['display_name'] ?? 'Creator AdaAcara');
    $hideMembershipSummary = ! empty($hideMembershipSummary);
    $creatorStatusLabel = match ($creatorFlowStatus) {
        'active' => 'Creator Aktif',
        'pending' => 'Menunggu Approve Admin',
        'rejected' => 'Pengajuan Ditolak',
        default => 'Belum Terdaftar',
    };
    $dashboardDisplayName = trim((string) ($userName ?? ''));
    if ($dashboardDisplayName === '') {
        $dashboardDisplayName = trim((string) ($userEmail ?? ''));
    }
    $dashboardDisplayName = $dashboardDisplayName !== '' ? $dashboardDisplayName : 'Teman AdaAcara';
    $dashboardFirstName = trim(explode(' ', $dashboardDisplayName)[0] ?? $dashboardDisplayName);
    $dashboardInitial = strtoupper(substr($dashboardDisplayName, 0, 1));
    $dashboardHour = $dashboardNowWib instanceof DateTimeImmutable ? (int) $dashboardNowWib->format('G') : (int) date('G');
    $dashboardGreeting = $dashboardHour < 11 ? 'Selamat pagi' : ($dashboardHour < 15 ? 'Selamat siang' : ($dashboardHour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $dashboardGuestbookUnreadTotal = array_sum(array_map(
        static fn (array $page): int => (int) ($page['guestbook_unread_count'] ?? 0),
        is_array($landingPages ?? null) ? $landingPages : []
    ));
    $dashboardTaskItems = [
        [
            'done' => $draftCount === 0,
            'label' => $draftCount > 0 ? $draftCount . ' draft perlu dilanjutkan' : 'Semua draft aman',
            'tone' => $draftCount > 0 ? 'rose' : 'emerald',
        ],
        [
            'done' => $dashboardGuestbookUnreadTotal === 0,
            'label' => $dashboardGuestbookUnreadTotal > 0 ? $dashboardGuestbookUnreadTotal . ' guestbook baru menunggu dibaca' : 'Guestbook sudah terbaca',
            'tone' => $dashboardGuestbookUnreadTotal > 0 ? 'violet' : 'emerald',
        ],
        [
            'done' => $expiredCount === 0,
            'label' => $expiredCount > 0 ? $expiredCount . ' undangan expired perlu dicek' : 'Tidak ada undangan expired',
            'tone' => $expiredCount > 0 ? 'rose' : 'emerald',
        ],
        [
            'done' => $publishedCount > 0,
            'label' => $publishedCount > 0 ? $publishedCount . ' link sudah aktif dibagikan' : 'Publish undangan pertamamu',
            'tone' => $publishedCount > 0 ? 'emerald' : 'violet',
        ],
    ];
    $dashboardWeekLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    $dashboardChartPoints = [
        max(18, min(92, 22 + ($publishedCount * 6))),
        max(24, min(92, 34 + ($pageCount * 5))),
        max(30, min(92, 48 + ($totalGuestbookCount * 4))),
        max(28, min(88, 42 + ($draftCount * 7))),
        max(36, min(94, 58 + ($publishedCount * 5))),
        max(34, min(94, 54 + ($dashboardGuestbookUnreadTotal * 9))),
        max(38, min(96, 62 + ($pageCount * 3))),
    ];
    $dashboardCalendarNow = $dashboardNowWib instanceof DateTimeImmutable
        ? $dashboardNowWib
        : new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
    $dashboardMonthLabel = $dashboardCalendarNow->format('M Y');
    $dashboardTodayKey = $dashboardCalendarNow->format('Y-m-d');
    $dashboardCalendarMonthStart = $dashboardCalendarNow->modify('first day of this month')->setTime(0, 0, 0);
    $dashboardCalendarMonthKey = $dashboardCalendarMonthStart->format('Y-m');
    $dashboardCalendarDays = (int) $dashboardCalendarMonthStart->format('t');
    $dashboardCalendarFirstDayOffset = (int) $dashboardCalendarMonthStart->format('N') - 1;
    $dashboardCalendarCellCount = ($dashboardCalendarFirstDayOffset + $dashboardCalendarDays) > 35 ? 42 : 35;
    $dashboardCalendarGridStart = $dashboardCalendarMonthStart->modify('-' . $dashboardCalendarFirstDayOffset . ' days');
    $dashboardCalendarCells = [];
    for ($calendarIndex = 0; $calendarIndex < $dashboardCalendarCellCount; $calendarIndex++) {
        $dashboardCalendarDate = $dashboardCalendarGridStart->modify('+' . $calendarIndex . ' days');
        $dashboardCalendarDateKey = $dashboardCalendarDate->format('Y-m-d');
        $dashboardCalendarCells[] = [
            'label' => (string) (int) $dashboardCalendarDate->format('j'),
            'is_current_month' => $dashboardCalendarDate->format('Y-m') === $dashboardCalendarMonthKey,
            'is_today' => $dashboardCalendarDateKey === $dashboardTodayKey,
        ];
    }
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Ada Acara</title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <?= view('components/google_ads_conversion_event') ?>
    <?= view('components/dashboard_theme_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lenis@1.3.8/dist/lenis.min.js" defer></script>
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

        .aa-dashboard-notification {
            position: relative;
        }

        .aa-dashboard-notification-button {
            position: relative;
            display: inline-grid;
            height: 44px;
            width: 44px;
            place-items: center;
            border: 1px solid rgba(217, 204, 244, .52);
            border-radius: 999px;
            background: rgba(255, 255, 255, .9);
            color: #0f172a;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
            transition: .16s ease;
        }

        .aa-dashboard-notification-button:hover,
        .aa-dashboard-notification.is-open .aa-dashboard-notification-button {
            border-color: rgba(143, 101, 223, .42);
            color: #7550c4;
            transform: translateY(-1px);
        }

        .aa-dashboard-notification-badge {
            position: absolute;
            right: -4px;
            top: -4px;
            display: inline-flex;
            min-width: 19px;
            height: 19px;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            border-radius: 999px;
            background: #0f766e;
            color: #ffffff;
            padding: 0 5px;
            font-size: 10px;
            font-weight: 950;
            line-height: 1;
        }

        .aa-dashboard-notification-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            z-index: 40;
            width: min(390px, calc(100vw - 24px));
            overflow: hidden;
            border: 1px solid rgba(217, 204, 244, .42);
            border-radius: 28px;
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 28px 80px rgba(15, 23, 42, .18);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-8px) scale(.98);
            transform-origin: top right;
            transition: .16s ease;
        }

        .aa-dashboard-notification.is-open .aa-dashboard-notification-panel {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        @media (max-width: 520px) {
            .aa-dashboard-notification-panel {
                right: -80px;
            }
        }

        .aa-dashboard-pastel {
            background:
                radial-gradient(circle at 10% 8%, rgba(168, 132, 222, .22), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(255, 196, 205, .45), transparent 30%),
                radial-gradient(circle at 78% 88%, rgba(255, 218, 151, .34), transparent 28%),
                linear-gradient(135deg, #f6edf8 0%, #fff7ef 48%, #eef8f5 100%) !important;
            color: #352848;
        }

        .aa-dashboard-app-shell {
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr);
            gap: 18px;
            min-height: 100vh;
            padding: 18px;
        }

        .aa-dashboard-sidebar {
            position: sticky;
            top: 18px;
            display: flex;
            height: calc(100vh - 36px);
            min-height: 660px;
            flex-direction: column;
            overflow-y: auto;
            scrollbar-width: none;
            border: 1px solid rgba(168, 132, 222, .18);
            border-radius: 34px;
            background: linear-gradient(180deg, rgba(234, 218, 255, .78), rgba(255, 246, 245, .9));
            box-shadow: 0 24px 70px rgba(91, 67, 118, .14), inset 1px 1px 0 rgba(255, 255, 255, .86);
        }

        .aa-dashboard-sidebar::-webkit-scrollbar {
            display: none;
        }

        .aa-dashboard-sidebar::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 50% 9%, rgba(255, 255, 255, .8), transparent 18%),
                radial-gradient(circle at 78% 78%, rgba(255, 213, 172, .48), transparent 24%);
            pointer-events: none;
        }

        .aa-dashboard-sidebar-inner {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 100%;
            flex-direction: column;
            padding: 18px 14px;
        }

        .aa-dashboard-sidebar-logo {
            display: inline-flex;
            justify-content: center;
            margin-bottom: 16px;
        }

        .aa-dashboard-sidebar-logo img {
            width: 128px;
            height: auto;
            filter: invert(20%) sepia(18%) saturate(1230%) hue-rotate(227deg) brightness(92%) contrast(92%);
        }

        .aa-dashboard-profile {
            display: grid;
            justify-items: center;
            padding: 6px 6px 18px;
            text-align: center;
        }

        .aa-dashboard-avatar {
            display: grid;
            width: 102px;
            height: 102px;
            place-items: center;
            overflow: hidden;
            border: 7px solid rgba(255, 255, 255, .78);
            border-radius: 999px;
            background: linear-gradient(135deg, #eadcff, #fff1e7);
            box-shadow: 0 18px 34px rgba(91, 67, 118, .18);
        }

        .aa-dashboard-avatar img {
            width: 152px;
            height: 114px;
            object-fit: cover;
            object-position: 22% 44%;
        }

        .aa-dashboard-avatar span {
            color: #7550c4;
            font-size: 34px;
            font-weight: 950;
        }

        .aa-dashboard-sidebar-nav {
            display: grid;
            gap: 8px;
            margin-top: 4px;
        }

        .aa-dashboard-sidebar-link,
        .aa-dashboard-sidebar-button {
            display: flex;
            min-height: 44px;
            align-items: center;
            gap: 10px;
            border: 0;
            border-radius: 15px;
            background: transparent;
            color: #5e5370;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            transition: transform .16s ease, background .16s ease, color .16s ease, box-shadow .16s ease;
        }

        .aa-dashboard-sidebar-link:hover,
        .aa-dashboard-sidebar-button:hover,
        .aa-dashboard-sidebar-link.is-active {
            background: linear-gradient(135deg, #a878f1, #8158d8);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(129, 88, 216, .24);
        }

        .aa-dashboard-sidebar-button {
            width: 100%;
            cursor: pointer;
            font-family: inherit;
            text-align: left;
        }

        .aa-dashboard-sidebar-note {
            margin-top: auto;
            border: 1px solid rgba(255, 255, 255, .78);
            border-radius: 24px;
            background: rgba(255, 255, 255, .62);
            padding: 14px;
            text-align: center;
            box-shadow: inset 1px 1px 0 rgba(255, 255, 255, .76);
        }

        .aa-dashboard-main-shell {
            min-width: 0;
            overflow: visible;
            border: 1px solid rgba(255, 255, 255, .62);
            border-radius: 34px;
            background: rgba(255, 248, 244, .46);
            box-shadow: inset 1px 1px 0 rgba(255, 255, 255, .65);
        }

        .aa-dashboard-topbar {
            position: sticky;
            top: 0;
            border: 0 !important;
            /* background: rgba(255, 250, 247, .76) !important;
            box-shadow: 0 14px 34px rgba(91, 67, 118, .08); */
        }

        .aa-dashboard-topbar img[src*="adaacara-logo"] {
            filter: invert(20%) sepia(18%) saturate(1230%) hue-rotate(227deg) brightness(92%) contrast(92%);
        }

        .aa-dashboard-topbar-logo {
            display: none;
        }

        .aa-dashboard-topbar-search {
            display: flex;
            min-height: 46px;
            flex: 1;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(168, 132, 222, .15);
            border-radius: 18px;
            background: rgba(255, 255, 255, .62);
            color: #91879c;
            padding: 0 15px;
            font-size: 13px;
            font-weight: 850;
            box-shadow: inset 1px 1px 0 rgba(255, 255, 255, .7);
        }

        .aa-dashboard-main-content {
            width: 100%;
            max-width: 1480px;
            padding: 28px;
        }

        .aa-dashboard-fade-up {
            animation: aaDashboardFadeUp .64s cubic-bezier(.22, .8, .26, 1) both;
        }

        .aa-dashboard-stat-card,
        .aa-dashboard-panel,
        .aa-dashboard-soft-card {
            border: 1px solid rgba(255, 255, 255, .78);
            background: rgba(255, 255, 255, .62);
            box-shadow: 0 18px 48px rgba(91, 67, 118, .10), inset 1px 1px 0 rgba(255, 255, 255, .82);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .aa-dashboard-stat-card {
            min-height: 118px;
            border-radius: 24px;
            padding: 16px;
        }

        .aa-dashboard-panel {
            border-radius: 28px;
            padding: 20px;
        }

        .aa-dashboard-chart {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 12px;
            min-height: 230px;
        }

        .aa-dashboard-chart-grid {
            position: relative;
            overflow: hidden;
            min-height: 210px;
            border-left: 1px solid rgba(129, 88, 216, .12);
            border-bottom: 1px solid rgba(129, 88, 216, .12);
            background:
                linear-gradient(rgba(129, 88, 216, .08) 1px, transparent 1px) 0 0 / 100% 25%,
                linear-gradient(90deg, rgba(129, 88, 216, .05) 1px, transparent 1px) 0 0 / calc(100% / 7) 100%;
            border-radius: 0 0 18px 0;
        }

        .aa-dashboard-chart-line {
            position: absolute;
            inset: 0;
            display: block;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .aa-dashboard-chart-line .aa-dashboard-chart-area {
            opacity: .58;
        }

        .aa-dashboard-chart-line .aa-dashboard-chart-stroke {
            filter: drop-shadow(0 8px 14px rgba(129, 88, 216, .18));
        }

        .aa-dashboard-task-item {
            display: flex;
            min-height: 38px;
            align-items: center;
            gap: 10px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .72);
            padding: 8px 10px;
            color: #574b64;
            font-size: 12px;
            font-weight: 850;
        }

        .aa-dashboard-mini-calendar {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 5px;
            text-align: center;
        }

        .aa-dashboard-mini-calendar span {
            display: grid;
            min-height: 26px;
            place-items: center;
            border-radius: 10px;
            color: #6b6177;
            font-size: 11px;
            font-weight: 850;
        }

        .aa-dashboard-mini-calendar .is-head {
            color: #a59bae;
            font-size: 10px;
            text-transform: uppercase;
        }

        .aa-dashboard-mini-calendar .is-today {
            background: linear-gradient(135deg, #a878f1, #8158d8);
            color: #fff;
            box-shadow: 0 10px 22px rgba(129, 88, 216, .26);
        }

        .aa-dashboard-mini-calendar .is-muted {
            color: rgba(107, 97, 119, .34);
        }

        @keyframes aaDashboardFadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1180px) {
            .aa-dashboard-app-shell {
                display: block;
                padding: 0;
            }

            .aa-dashboard-sidebar {
                display: none;
            }

            .aa-dashboard-main-shell {
                border: 0;
                border-radius: 0;
                background: transparent;
            }

            .aa-dashboard-topbar-logo {
                display: inline-flex;
            }

            .aa-dashboard-main-content {
                padding: 22px 16px;
            }
        }

        @media (max-width: 760px) {
            .aa-dashboard-topbar-search {
                display: none;
            }

            .aa-dashboard-chart {
                grid-template-columns: 28px minmax(0, 1fr);
                min-height: 190px;
            }

            .aa-dashboard-chart-grid {
                min-height: 170px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .aa-dashboard-fade-up {
                animation: none;
            }
        }

        html[data-aa-public-theme="dark"] .aa-dashboard-pastel {
            background: linear-gradient(180deg, #071018 0%, #0b1220 52%, #070b12 100%) !important;
        }

        html[data-aa-public-theme="dark"] .aa-dashboard-sidebar,
        html[data-aa-public-theme="dark"] .aa-dashboard-main-shell,
        html[data-aa-public-theme="dark"] .aa-dashboard-stat-card,
        html[data-aa-public-theme="dark"] .aa-dashboard-panel,
        html[data-aa-public-theme="dark"] .aa-dashboard-soft-card {
            border-color: rgba(148, 163, 184, .18) !important;
            background: rgba(15, 23, 42, .72) !important;
            box-shadow: 0 18px 42px rgba(0, 0, 0, .18) !important;
        }

        html[data-aa-public-theme="dark"] .aa-dashboard-sidebar-link,
        html[data-aa-public-theme="dark"] .aa-dashboard-sidebar-button,
        html[data-aa-public-theme="dark"] .aa-dashboard-task-item,
        html[data-aa-public-theme="dark"] .aa-dashboard-mini-calendar span {
            color: #d8e0ec !important;
        }

        html[data-aa-public-theme="dark"] .aa-dashboard-mini-calendar .is-muted {
            color: rgba(216, 224, 236, .36) !important;
        }
    </style>
</head>
<body class="aa-app-ui aa-dashboard-theme-page aa-dashboard-home-page aa-dashboard-pastel min-h-screen text-slate-950 antialiased">
    <div class="aa-dashboard-app-shell">
        <aside class="aa-dashboard-sidebar" aria-label="Navigasi Dashboard">
            <div class="aa-dashboard-sidebar-inner">
                <a class="aa-dashboard-sidebar-logo" href="<?= site_url('dashboard') ?>" aria-label="AdaAcara Dashboard">
                    <img src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                </a>

                <div class="aa-dashboard-profile">
                    <div class="aa-dashboard-avatar" aria-hidden="true">
                        <img src="<?= aa_asset_url('assets/img/auth-illustration.png') ?>" alt="" loading="lazy" decoding="async">
                    </div>
                    <p class="mt-3 text-sm font-black text-[#49365f]">Hai, <?= esc($dashboardFirstName) ?>!</p>
                    <p class="mt-1 max-w-[160px] text-xs font-bold leading-5 text-[#7e728b]">Kelola undanganmu dengan tenang hari ini.</p>
                </div>

                <nav class="aa-dashboard-sidebar-nav" aria-label="Menu dashboard">
                    <a class="aa-dashboard-sidebar-link is-active" href="<?= site_url('dashboard') ?>"><?= $aaIcon('package', 'h-4 w-4') ?><span>Dashboard</span></a>
                    <a class="aa-dashboard-sidebar-link" href="<?= site_url('templates') ?>"><?= $aaIcon('plus', 'h-4 w-4') ?><span>Buat Project Baru</span></a>
                    <a class="aa-dashboard-sidebar-link" href="<?= site_url('templates') ?>"><?= $aaIcon('template', 'h-4 w-4') ?><span>Template</span></a>
                    <a class="aa-dashboard-sidebar-link" href="<?= site_url('photographer-galleries') ?>"><?= $aaIcon('camera', 'h-4 w-4') ?><span>Photo Gallery</span></a>
                    <a class="aa-dashboard-sidebar-link" href="<?= site_url('orders') ?>"><?= $aaIcon('order', 'h-4 w-4') ?><span>Order Saya</span></a>
                    <a class="aa-dashboard-sidebar-link" href="<?= site_url('plans') ?>"><?= $aaIcon('heart', 'h-4 w-4') ?><span>Wishlist</span></a>
                    <a class="aa-dashboard-sidebar-link" href="<?= site_url('creator/dashboard') ?>"><?= $aaIcon('sparkles', 'h-4 w-4') ?><span>Creator</span></a>
                </nav>

                <div class="aa-dashboard-sidebar-note">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-white text-[#8158d8] shadow-sm"><?= $aaIcon('shield', 'h-5 w-5') ?></span>
                    <p class="mt-3 text-sm font-black text-[#49365f]">Jangan lupa preview</p>
                    <p class="mt-1 text-xs font-bold leading-5 text-[#7e728b]">Cek link undangan sebelum dibagikan.</p>
                </div>

                <form class="mt-3" action="<?= site_url('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="aa-dashboard-sidebar-button" type="submit"><?= $aaIcon('logout', 'h-4 w-4') ?><span>Logout</span></button>
                </form>
            </div>
        </aside>

        <div class="aa-dashboard-main-shell">
    <header class="aa-dashboard-topbar sticky top-0 z-20 border-b border-violet-100/80 backdrop-blur-xl" aria-label="Navigasi atas dashboard" style="border-radius:35px">
        <div class="mx-auto flex min-h-16 w-full max-w-[1480px] items-center justify-between gap-4 px-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <a href="<?= site_url('') ?>" class="aa-dashboard-topbar-logo shrink-0">
                    <img class="h-10 w-auto object-contain drop-shadow-sm" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                </a>
                <div class="aa-dashboard-topbar-search">
                    <?= $aaIcon('search', 'h-4 w-4') ?>
                    <span>Gunakan pencarian undangan di bawah untuk menemukan desainmu...</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <?= view('components/public_theme_toggle') ?>
                <div
                    class="aa-dashboard-notification"
                    data-dashboard-notification
                    data-dashboard-notification-read-url="<?= esc(site_url('dashboard/notifications/read'), 'attr') ?>"
                    data-dashboard-notification-csrf-name="<?= function_exists('csrf_token') ? esc(csrf_token(), 'attr') : '' ?>"
                    data-dashboard-notification-csrf-hash="<?= function_exists('csrf_hash') ? esc(csrf_hash(), 'attr') : '' ?>"
                >
                    <button class="aa-dashboard-notification-button" type="button" aria-label="Notifikasi" aria-haspopup="true" aria-expanded="false" data-dashboard-notification-toggle>
                        <?= $aaIcon('bell', 'h-5 w-5') ?>
                        <?php if ($dashboardUnreadCount > 0): ?>
                            <span class="aa-dashboard-notification-badge" data-dashboard-notification-badge><?= esc((string) min($dashboardUnreadCount, 9)) ?><?= $dashboardUnreadCount > 9 ? '+' : '' ?></span>
                        <?php endif ?>
                    </button>
                    <div class="aa-dashboard-notification-panel" role="menu" aria-label="Notifikasi dashboard">
                        <div class="border-b border-violet-100 bg-violet-50/70 px-5 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-700">Notifikasi</p>
                                    <h3 class="mt-1 text-base font-black text-slate-950">Update Dashboard</h3>
                                </div>
                                <?php if ($dashboardUnreadCount > 0): ?>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-emerald-700 ring-1 ring-emerald-100" data-dashboard-notification-summary><?= esc((string) $dashboardUnreadCount) ?> baru</span>
                                <?php endif ?>
                            </div>
                        </div>
                        <div class="max-h-[420px] overflow-y-auto p-2">
                            <?php if ($dashboardNotifications === []): ?>
                                <div class="px-4 py-8 text-center">
                                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><?= $aaIcon('check', 'h-5 w-5') ?></span>
                                    <p class="mt-3 text-sm font-black text-slate-900">Belum ada notifikasi baru</p>
                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">Aktivitas penting akan muncul di sini.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach (array_slice($dashboardNotifications, 0, 5) as $notification): ?>
                                    <?php
                                        $isUnread = empty($notification['read_at']);
                                        $notificationUrl = $aaNotificationUrl($notification);
                                    ?>
                                    <a class="flex gap-3 rounded-2xl px-3 py-3 text-left transition hover:bg-violet-50/70" href="<?= esc($notificationUrl, 'attr') ?>" role="menuitem" data-dashboard-notification-item>
                                        <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-2xl <?= $isUnread ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>" data-dashboard-notification-icon>
                                            <?= $aaIcon($isUnread ? 'bell' : 'check', 'h-4 w-4') ?>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-black text-slate-950"><?= esc((string) ($notification['title'] ?? 'Notifikasi')) ?></span>
                                            <span class="mt-1 block line-clamp-2 text-xs font-semibold leading-5 text-slate-500"><?= esc((string) ($notification['message'] ?? '')) ?></span>
                                            <span class="mt-1 block text-[11px] font-black uppercase tracking-[0.12em] text-violet-700"><?= esc($aaNotificationTime($notification)) ?></span>
                                        </span>
                                    </a>
                                <?php endforeach ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <?= view('components/user_nav_dropdown', ['active' => 'dashboard']) ?>
            </div>
        </div>
    </header>

    <main class="aa-dashboard-main-content mx-auto flex-1">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <?php
            $activeSubscription = $activeSubscription ?? null;
            $pageCount = (int) ($pageCount ?? count($landingPages));
            $pageLimit = (int) ($pageLimit ?? 0);
            $isUnlimitedPageLimit = $pageLimit >= PHP_INT_MAX;
            $pageLimitLabel = $isUnlimitedPageLimit ? 'Unlimited' : ($pageLimit > 0 ? (string) $pageLimit : '-');
            $publishedCount = (int) ($publishedCount ?? 0);
            $draftCount = (int) ($draftCount ?? 0);
            $expiredCount = (int) ($expiredCount ?? 0);
            $totalGuestbookCount = (int) ($totalGuestbookCount ?? 0);
            $canCreatePage = (bool) ($canCreatePage ?? true);
            $dashboardExpiredRaw = $dashboardExpiredRaw ?? ($dashboardExpiredLabel ?? ($activeSubscription['expired_at'] ?? ''));
            $dashboardExpiredLabel = $isDashboardLifetimeSubscription($activeSubscription)
                ? 'Selamanya'
                : aa_format_wib_datetime($dashboardExpiredRaw);
            $creatorStatus = $creatorStatus ?? ['status' => 'none', 'display_name' => null];
            $creatorFlowStatus = (string) ($creatorStatus['status'] ?? 'none');
            $creatorDisplayName = (string) ($creatorStatus['display_name'] ?? 'Creator AdaAcara');
            $hideMembershipSummary = ! empty($hideMembershipSummary);
            $creatorStatusLabel = match ($creatorFlowStatus) {
                'active' => 'Creator Aktif',
                'pending' => 'Menunggu Approve Admin',
                'rejected' => 'Pengajuan Ditolak',
                default => 'Belum Terdaftar',
            };
            $dashboardDisplayName = trim((string) ($userName ?? ''));
            if ($dashboardDisplayName === '') {
                $dashboardDisplayName = trim((string) ($userEmail ?? ''));
            }
            $dashboardDisplayName = $dashboardDisplayName !== '' ? $dashboardDisplayName : 'Teman AdaAcara';
        ?>

        <section class="aa-dashboard-fade-up">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_310px]">
                <div class="min-w-0">
                    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-black leading-tight text-[#3f3156] sm:text-4xl"><?= esc($dashboardGreeting) ?>, <?= esc($dashboardFirstName) ?>!</h1>
                            <p class="mt-2 text-sm font-bold leading-6 text-[#7e728b]">Ini yang sedang terjadi di undangan AdaAcara kamu hari ini.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-[#8f65df] px-4 text-sm font-black text-white shadow-lg shadow-violet-300/30 transition hover:-translate-y-0.5 hover:bg-[#7550c4]" href="<?= site_url('templates') ?>"><?= $aaIcon('plus', 'h-4 w-4') ?>Buat Project Baru</a>
                            <?php if (! empty($isAdmin)): ?>
                                <a class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-white/80 bg-white/70 px-4 text-sm font-black text-[#5f4b70] shadow-sm transition hover:-translate-y-0.5 hover:text-[#8f65df]" href="<?= site_url('admin') ?>"><?= $aaIcon('more', 'h-4 w-4') ?>Admin</a>
                            <?php endif ?>
                            <?php if (! empty($canAccessSellerDashboard)): ?>
                                <a class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-white/80 bg-white/70 px-4 text-sm font-black text-[#7550c4] shadow-sm transition hover:-translate-y-0.5 hover:text-[#5f3db7]" href="<?= site_url('seller') ?>"><?= $aaIcon('money', 'h-4 w-4') ?>Seller</a>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <article class="aa-dashboard-stat-card bg-violet-100/60">
                            <div class="flex items-start justify-between gap-3">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/76 text-[#8f65df] shadow-sm"><?= $aaIcon('template', 'h-5 w-5') ?></span>
                                <span class="rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-black text-[#8f65df]">Total</span>
                            </div>
                            <p class="mt-4 text-3xl font-black text-[#3f3156]"><?= esc((string) $pageCount) ?></p>
                            <p class="mt-1 text-xs font-bold text-[#7e728b]">Undangan dibuat</p>
                        </article>
                        <article class="aa-dashboard-stat-card bg-rose-100/60">
                            <div class="flex items-start justify-between gap-3">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/76 text-rose-500 shadow-sm"><?= $aaIcon('edit', 'h-5 w-5') ?></span>
                                <span class="rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-black text-rose-500">Draft</span>
                            </div>
                            <p class="mt-4 text-3xl font-black text-[#3f3156]"><?= esc((string) $draftCount) ?></p>
                            <p class="mt-1 text-xs font-bold text-[#7e728b]">Belum dipublish</p>
                        </article>
                        <article class="aa-dashboard-stat-card bg-emerald-100/60">
                            <div class="flex items-start justify-between gap-3">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/76 text-emerald-600 shadow-sm"><?= $aaIcon('check', 'h-5 w-5') ?></span>
                                <span class="rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-black text-emerald-600">Live</span>
                            </div>
                            <p class="mt-4 text-3xl font-black text-[#3f3156]"><?= esc((string) $publishedCount) ?></p>
                            <p class="mt-1 text-xs font-bold text-[#7e728b]">Link aktif</p>
                        </article>
                        <article class="aa-dashboard-stat-card bg-violet-100/60">
                            <div class="flex items-start justify-between gap-3">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/76 text-violet-500 shadow-sm"><?= $aaIcon('book', 'h-5 w-5') ?></span>
                                <span class="rounded-full bg-white/70 px-2.5 py-1 text-[11px] font-black text-violet-600"><?= esc((string) $dashboardGuestbookUnreadTotal) ?> baru</span>
                            </div>
                            <p class="mt-4 text-3xl font-black text-[#3f3156]"><?= esc((string) $totalGuestbookCount) ?></p>
                            <p class="mt-1 text-xs font-bold text-[#7e728b]">Guestbook masuk</p>
                        </article>
                    </div>

                    <div class="aa-dashboard-panel mt-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-black text-[#3f3156]">Overview Produktivitas</h2>
                                <p class="mt-1 text-xs font-bold text-[#8b8097]">Ringkasan visual dari aktivitas undangan kamu.</p>
                            </div>
                            <span class="rounded-full bg-white/74 px-3 py-1.5 text-xs font-black text-[#6f43c3] shadow-sm">Minggu ini</span>
                        </div>
                        <div class="aa-dashboard-chart">
                            <div class="grid content-between py-1 text-right text-[11px] font-black text-[#a298ad]">
                                <span>100</span>
                                <span>75</span>
                                <span>50</span>
                                <span>25</span>
                                <span>0</span>
                            </div>
                            <div class="aa-dashboard-chart-grid">
                                <?php
                                    $chartPairs = [];
                                    foreach ($dashboardChartPoints as $pointIndex => $pointValue) {
                                        $chartX = 35 + ($pointIndex * 105);
                                        $chartY = 100 - (float) $pointValue;
                                        $chartPairs[] = $chartX . ',' . $chartY;
                                    }
                                ?>
                                <svg class="aa-dashboard-chart-line" viewBox="0 0 700 100" preserveAspectRatio="none" aria-hidden="true">
                                    <polygon class="aa-dashboard-chart-area" points="<?= esc(implode(' ', $chartPairs), 'attr') ?> 665,100 35,100" fill="rgba(156,116,230,.10)"/>
                                    <polyline class="aa-dashboard-chart-stroke" points="<?= esc(implode(' ', $chartPairs), 'attr') ?>" fill="none" stroke="#9c74e6" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"/>
                                    <?php foreach ($dashboardChartPoints as $pointIndex => $pointValue): ?>
                                        <circle cx="<?= esc((string) (35 + ($pointIndex * 105)), 'attr') ?>" cy="<?= esc((string) (100 - (float) $pointValue), 'attr') ?>" r="2.4" fill="#9c74e6"/>
                                    <?php endforeach ?>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-7 gap-1 text-center text-[11px] font-black text-[#8b8097]">
                            <?php foreach ($dashboardWeekLabels as $label): ?>
                                <span><?= esc($label) ?></span>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>

                <aside class="grid content-start gap-5">
                    <section class="aa-dashboard-panel bg-rose-50/58">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-black text-[#3f3156]">To-Do List</h2>
                            <span class="grid h-8 w-8 place-items-center rounded-xl bg-rose-400 text-white shadow-sm"><?= $aaIcon('plus', 'h-4 w-4') ?></span>
                        </div>
                        <div class="mt-4 grid gap-2">
                            <?php foreach ($dashboardTaskItems as $task): ?>
                                <div class="aa-dashboard-task-item">
                                    <span class="grid h-5 w-5 place-items-center rounded-md <?= ! empty($task['done']) ? 'bg-emerald-100 text-emerald-600' : 'bg-white text-rose-400 ring-1 ring-rose-200' ?>">
                                        <?= ! empty($task['done']) ? $aaIcon('check', 'h-3.5 w-3.5') : '' ?>
                                    </span>
                                    <span><?= esc((string) $task['label']) ?></span>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </section>

                    <section class="aa-dashboard-panel bg-sky-50/62">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-lg font-black text-[#3f3156]">Calendar</h2>
                            <span class="text-xs font-black text-[#8f65df]"><?= esc($dashboardMonthLabel) ?></span>
                        </div>
                        <div class="aa-dashboard-mini-calendar">
                            <?php foreach (['S', 'S', 'R', 'K', 'J', 'S', 'M'] as $dayHead): ?>
                                <span class="is-head"><?= esc($dayHead) ?></span>
                            <?php endforeach ?>
                            <?php foreach ($dashboardCalendarCells as $calendarCell): ?>
                                <?php
                                    $calendarCellClass = trim(
                                        (! empty($calendarCell['is_today']) ? 'is-today ' : '')
                                        . (empty($calendarCell['is_current_month']) ? 'is-muted' : '')
                                    );
                                ?>
                                <span class="<?= esc($calendarCellClass, 'attr') ?>"><?= esc((string) $calendarCell['label']) ?></span>
                            <?php endforeach ?>
                        </div>
                    </section>

                    <section class="aa-dashboard-panel relative overflow-hidden bg-violet-100/62">
                        <div class="relative z-10 max-w-[170px]">
                            <h2 class="text-lg font-black leading-tight text-[#5b3e88]">Kamu sudah hebat hari ini</h2>
                            <p class="mt-2 text-xs font-bold leading-5 text-[#7e6f95]">Satu undangan rapi bisa membuat acara terasa lebih berkesan.</p>
                        </div>
                        <img class="absolute -bottom-7 -right-7 h-36 w-36 rounded-full object-cover opacity-80" src="<?= aa_asset_url('assets/img/auth-illustration.png') ?>" alt="" loading="lazy" decoding="async">
                    </section>
                </aside>
            </div>
        </section>

        <?php if ($hideMembershipSummary): ?>
            <section class="mt-6 rounded-[28px] border border-violet-100 bg-white/85 p-6 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-700">Status Creator</p>
                <div class="mt-3 grid gap-4 md:grid-cols-[1fr_auto] md:items-center">
                    <div>
                        <h3 class="text-2xl font-black tracking-tight"><?= esc($creatorDisplayName) ?></h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Akun ini berada di flow Creator. Status paket membership dan publish tidak ditampilkan di dashboard user.</p>
                    </div>
                    <span class="inline-flex h-11 items-center justify-center rounded-2xl bg-violet-50 px-4 text-sm font-black text-violet-800 ring-1 ring-violet-100"><?= esc($creatorStatusLabel) ?></span>
                </div>
            </section>
        <?php else: ?>
            <section class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-3xl border border-emerald-100 bg-white/85 p-5 shadow-sm shadow-slate-900/5">
                    <div class="flex items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-700"><?= $aaIcon('package', 'h-5 w-5') ?></span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Paket Aktif</p>
                            <p class="mt-1 truncate text-base font-black text-slate-900"><?= esc($activePlanName) ?></p>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl border border-violet-100 bg-white/85 p-5 shadow-sm shadow-slate-900/5">
                    <div class="flex items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-violet-50 text-violet-700"><?= $aaIcon('link', 'h-5 w-5') ?></span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Batas Link Publish</p>
                            <p class="mt-1 truncate text-base font-black text-slate-900"><?= esc($pageLimitLabel) ?></p>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl border border-violet-100 bg-white/85 p-5 shadow-sm shadow-slate-900/5">
                    <div class="flex items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-violet-50 text-violet-700"><?= $aaIcon('external', 'h-5 w-5') ?></span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Link Publish Aktif</p>
                            <p class="mt-1 truncate text-base font-black text-slate-900"><?= esc((string) $publishedCount) ?><?= $pageLimit > 0 ? ' / ' . esc($isUnlimitedPageLimit ? 'Unlimited' : (string) $pageLimit) : '' ?></p>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl border border-rose-100 bg-white/85 p-5 shadow-sm shadow-slate-900/5">
                    <div class="flex items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-rose-50 text-rose-700"><?= $aaIcon('calendar', 'h-5 w-5') ?></span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Expired</p>
                            <p class="mt-1 truncate text-base font-black text-slate-900"><?= esc($dashboardExpiredLabel) ?></p>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif ?>

        <?php if ($templateWishlists !== []): ?>
            <section class="mt-6 rounded-[32px] border border-rose-100 bg-white/85 p-5 shadow-sm shadow-slate-900/5">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-600">Wishlist</p>
                        <h3 class="mt-1 text-xl font-black tracking-tight">Template yang kamu sukai</h3>
                    </div>
                    <a class="hidden text-sm font-black text-rose-600 transition hover:text-rose-700 sm:inline-flex" href="<?= site_url('templates') ?>">Cari template lain</a>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    <?php foreach ($templateWishlists as $wishlist): ?>
                        <?php
                            $wishlistThumb = trim((string) ($wishlist['thumbnail'] ?? ''));
                            $wishlistThumb = $wishlistThumb !== '' ? base_url(ltrim($wishlistThumb, '/')) : '';
                            $wishlistName = (string) ($wishlist['name'] ?? 'Template');
                            $wishlistUrl = site_url('templates/' . (string) ($wishlist['slug'] ?? ''));
                        ?>
                        <a class="group overflow-hidden rounded-3xl border border-slate-100 bg-slate-50 transition hover:-translate-y-0.5 hover:border-rose-200 hover:bg-rose-50/50" href="<?= esc($wishlistUrl, 'attr') ?>">
                            <span class="relative block aspect-[4/5] overflow-hidden bg-gradient-to-br from-rose-50 via-violet-50 to-emerald-50">
                                <?php if ($wishlistThumb !== ''): ?>
                                    <img class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" src="<?= esc($wishlistThumb, 'attr') ?>" alt="<?= esc($wishlistName, 'attr') ?>" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <span class="grid h-full place-items-center px-4 text-center text-sm font-black text-slate-400">Template</span>
                                <?php endif ?>
                                <span class="absolute right-2 top-2 grid h-8 w-8 place-items-center rounded-full bg-white/92 text-rose-500 shadow-sm ring-1 ring-white/70"><?= $aaIcon('heart', 'h-4 w-4') ?></span>
                            </span>
                            <span class="block truncate px-3 py-3 text-xs font-black text-slate-800"><?= esc($wishlistName) ?></span>
                        </a>
                    <?php endforeach ?>
                </div>
            </section>
        <?php endif ?>

        <section class="mt-8">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-xl font-semibold tracking-tight">Project Saya</h3>
                    <p class="mt-1 text-sm text-slate-600"><span data-dashboard-filter-count><?= count($landingPages) ?></span> dari <?= count($landingPages) ?> halaman ditemukan.</p>
                </div>
            </div>

            <?php if ($landingPages === []): ?>
                <div class="rounded-[28px] border border-dashed border-violet-200 bg-white/85 p-8 shadow-sm">
                    <h3 class="text-lg font-semibold tracking-tight">Belum ada project</h3>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Mulai dari template, lalu edit konten event dengan visual builder.</p>
                    <a class="mt-6 inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 text-sm font-black text-white transition hover:bg-slate-800" href="<?= site_url('templates') ?>"><?= $aaIcon('plus', 'h-4 w-4') ?>Buat Project Baru</a>
                </div>
            <?php else: ?>
                <div class="mb-5 rounded-[28px] border border-violet-100 bg-white/85 p-4 shadow-sm shadow-slate-900/5">
                    <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                        <label class="relative block">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><?= $aaIcon('search', 'h-5 w-5') ?></span>
                            <input
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                                type="search"
                                data-dashboard-search
                                placeholder="Cari judul, ID, tipe, status, atau paket"
                                autocomplete="off"
                            >
                        </label>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <label class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><?= $aaIcon('filter', 'h-4 w-4') ?></span>
                                <select class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-white pl-10 pr-9 text-sm font-black text-slate-700 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100" data-dashboard-status-filter>
                                    <option value="all">Semua Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </label>
                            <label class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><?= $aaIcon('package', 'h-4 w-4') ?></span>
                                <select class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-white pl-10 pr-9 text-sm font-black text-slate-700 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100" data-dashboard-project-filter>
                                    <option value="all">Semua Project</option>
                                    <option value="invitation">Undangan Digital</option>
                                    <option value="photobooth">Digital Photobooth</option>
                                    <option value="business_profile">Business Profile</option>
                                </select>
                            </label>
                            <label class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><?= $aaIcon('sort', 'h-4 w-4') ?></span>
                                <select class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-white pl-10 pr-9 text-sm font-black text-slate-700 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100" data-dashboard-sort>
                                    <option value="newest">Terakhir Diedit</option>
                                    <option value="oldest">Paling Lama Diedit</option>
                                    <option value="title">Judul A-Z</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="hidden rounded-[28px] border border-dashed border-violet-200 bg-white/85 p-8 text-center shadow-sm" data-dashboard-empty-filter>
                    <h3 class="text-lg font-semibold tracking-tight">Tidak ada project yang cocok</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Coba ubah kata kunci atau pilih filter yang lebih luas.</p>
                </div>

                <div class="grid grid-cols-2 gap-5 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6" data-dashboard-grid>
                    <?php foreach ($landingPages as $index => $page): ?>
                        <?php
                            $status = $page['status'] ?? 'draft';
                            $isPublished = $status === 'published';
                            $isFreeExpired = ! empty($page['free_is_expired']);
                            $thumbnail = $aaDashboardThumbnail($page);
                            $projectSurface = $aaDashboardProjectSurface($page);
                            $gradient = $aaCardGradients[$index % count($aaCardGradients)];
                            $initial = strtoupper(substr(trim((string) ($page['title'] ?? 'U')), 0, 1));
                            $pagePlanLabel = (string) ($page['plan_label'] ?? (($page['access_tier'] ?? '') === 'free' ? 'Free' : 'Premium'));
                            $pageExpiryLabel = (string) ($page['free_expires_at'] ?? '');
                            $guestbookCount = (int) ($page['guestbook_count'] ?? 0);
                            $guestbookUnreadCount = (int) ($page['guestbook_unread_count'] ?? 0);
                            $publicUrl = $isPublished && ! $isFreeExpired ? site_url('u/' . $page['slug']) : '';
                            $primaryActionUrl = $isFreeExpired ? site_url('plans') : ($isPublished && ! $isFreeExpired ? $publicUrl : site_url('editor/' . $page['id']));
                            $primaryActionLabel = $isPublished && ! $isFreeExpired ? 'Bagikan Link' : ($isFreeExpired ? 'Cek Paket' : $projectSurface['primary_draft_label']);
                            $primaryActionIcon = $isPublished && ! $isFreeExpired ? 'send' : ($isFreeExpired ? 'package' : 'edit');
                            $filterStatus = $isFreeExpired ? 'expired' : strtolower((string) $status);
                            $filterPlan = strtolower($pagePlanLabel) === 'free' ? 'free' : 'premium';
                            $filterText = trim(implode(' ', [
                                (string) ($page['title'] ?? ''),
                                'id ' . (string) ($page['id'] ?? ''),
                                '#' . (string) ($page['id'] ?? ''),
                                (string) $filterStatus,
                                (string) $filterPlan,
                                (string) $pagePlanLabel,
                                (string) $projectSurface['label'],
                                (string) $projectSurface['type'],
                            ]));
                            $updatedTimestamp = aa_wib_timestamp($page['updated_at'] ?? $page['created_at'] ?? '');
                            $updatedLabel = ! empty($page['updated_at'])
                                ? aa_format_wib_datetime($page['updated_at'] ?? '', 'd M Y H:i')
                                : '';
                        ?>
                        <article
                            class="group overflow-visible rounded-[22px] border border-violet-100 bg-white shadow-sm shadow-slate-900/5 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-900/10"
                            data-dashboard-card
                            data-title="<?= esc(strtolower((string) ($page['title'] ?? '')), 'attr') ?>"
                            data-id="<?= esc((string) ($page['id'] ?? 0), 'attr') ?>"
                            data-updated="<?= esc((string) $updatedTimestamp, 'attr') ?>"
                            data-status="<?= esc($filterStatus, 'attr') ?>"
                            data-plan="<?= esc($filterPlan, 'attr') ?>"
                            data-project-type="<?= esc((string) $projectSurface['type'], 'attr') ?>"
                            data-search="<?= esc(strtolower($filterText), 'attr') ?>"
                        >
                            <div class="relative h-72 overflow-visible rounded-t-[22px] bg-gradient-to-br <?= esc($gradient, 'attr') ?>">
                                <?php if ($thumbnail !== ''): ?>
                                    <span class="aa-img-wrap h-full w-full rounded-t-[22px]">
                                        <img class="aa-lazy-img h-full w-full rounded-t-[22px] object-cover" src="<?= esc($thumbnail, 'attr') ?>" alt="<?= esc($page['title'] ?? 'Undangan', 'attr') ?>" loading="lazy" decoding="async">
                                    </span>
                                    <div class="absolute inset-0 rounded-t-[22px] bg-gradient-to-t from-slate-950/14 via-transparent to-white/10"></div>
                                <?php else: ?>
                                    <div class="grid h-full place-items-center">
                                        <span class="font-serif text-6xl font-black italic text-white/75 drop-shadow"><?= esc($initial) ?></span>
                                    </div>
                                <?php endif ?>

                                <div class="absolute left-3 top-3">
                                    <span class="inline-flex h-7 max-w-[9.5rem] items-center truncate rounded-full bg-white/95 px-3 text-[11px] font-black shadow-sm <?= esc((string) $projectSurface['tone'], 'attr') ?>">
                                        <?= esc((string) $projectSurface['label']) ?>
                                    </span>
                                </div>

                                <div class="absolute bottom-3 left-3">
                                    <span class="inline-flex h-7 items-center rounded-full px-3 text-xs font-black shadow-sm <?= $isFreeExpired ? 'bg-rose-100 text-rose-800 ring-1 ring-rose-200' : ($isPublished ? 'bg-violet-100 text-violet-800 ring-1 ring-violet-200' : 'bg-violet-100 text-violet-800 ring-1 ring-violet-200') ?>">
                                        <?= $isFreeExpired ? 'Expired' : ($isPublished ? 'Published' : 'Draft') ?>
                                    </span>
                                </div>
                                <div class="absolute bottom-3 right-3">
                                    <span class="inline-flex h-7 items-center rounded-full bg-white/95 px-3 text-xs font-black text-slate-800 shadow-sm ring-1 ring-slate-200">
                                        <?= esc($pagePlanLabel) ?>
                                    </span>
                                </div>

                                <div class="absolute right-3 top-3">
                                    <div class="relative" data-dashboard-card-menu>
                                        <button class="inline-grid h-10 w-10 place-items-center rounded-full bg-white/95 text-slate-950 shadow-md shadow-slate-900/10 ring-1 ring-slate-200 transition hover:bg-white" type="button" aria-label="Menu project" data-dashboard-card-menu-toggle>
                                            <?= $aaIcon('more', 'h-5 w-5') ?>
                                        </button>
                                        <div class="absolute right-0 top-12 z-10 hidden w-44 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 text-sm shadow-2xl shadow-slate-900/20" data-dashboard-card-menu-panel>
                                            <a class="flex items-center gap-2 px-4 py-2.5 font-medium text-slate-800 transition hover:bg-slate-50" href="<?= site_url('editor/' . $page['id']) ?>"><?= $aaIcon('edit', 'h-4 w-4') ?><?= esc((string) $projectSurface['edit_label']) ?></a>
                                            <a class="flex items-center gap-2 px-4 py-2.5 font-medium text-slate-800 transition hover:bg-slate-50" href="<?= site_url('preview/' . $page['id']) ?>" target="_blank" rel="noopener"><?= $aaIcon('eye', 'h-4 w-4') ?>Preview</a>
                                            <?php if ($projectSurface['has_invitation_tools']): ?>
                                            <a class="flex items-center gap-2 px-4 py-2.5 font-medium text-slate-800 transition hover:bg-slate-50" href="<?= site_url('dashboard/pages/' . $page['id'] . '/guestbook') ?>"><?= $aaIcon('book', 'h-4 w-4') ?>Guestbook</a>
                                            <button class="flex w-full items-center gap-2 px-4 py-2.5 text-left font-medium text-slate-800 transition hover:bg-slate-50" type="button" data-rsvp-share-trigger data-rsvp-share-url="<?= esc(site_url('dashboard/pages/' . $page['id'] . '/rsvp-link'), 'attr') ?>" data-rsvp-share-title="<?= esc((string) ($page['title'] ?? 'Undangan'), 'attr') ?>"><?= $aaIcon('share', 'h-4 w-4') ?>Sharing RSVP</button>
                                            <?php endif ?>
                                            <?php if ($projectSurface['has_photobooth']): ?>
                                            <?php if ($canUseGuestMemories): ?>
                                                <button class="flex w-full items-center gap-2 px-4 py-2.5 text-left font-medium text-slate-800 transition hover:bg-slate-50" type="button" data-photobooth-domain-trigger data-photobooth-domain-url="<?= esc(site_url('dashboard/pages/' . $page['id'] . '/photobooth-domain'), 'attr') ?>" data-photobooth-domain-proof-url="<?= esc(site_url('dashboard/pages/' . $page['id'] . '/photobooth-domain/proof'), 'attr') ?>" data-photobooth-domain-title="<?= esc((string) ($page['title'] ?? 'Undangan'), 'attr') ?>"><?= $aaIcon('camera', 'h-4 w-4') ?>Publish Frame Photobooth</button>
                                            <?php else: ?>
                                                <button class="flex w-full cursor-not-allowed items-center gap-2 px-4 py-2.5 text-left font-medium text-slate-400 opacity-60" type="button" disabled aria-disabled="true" title="<?= esc($photoboothInactiveTitle, 'attr') ?>"><?= $aaIcon('camera', 'h-4 w-4') ?>Publish Frame Photobooth</button>
                                            <?php endif ?>
                                            <?php endif ?>
                                            <?php if ($projectSurface['has_invitation_tools']): ?>
                                            <a class="flex items-center gap-2 px-4 py-2.5 font-medium text-slate-800 transition hover:bg-slate-50" href="<?= site_url('share-whatsapp?page_id=' . (int) $page['id']) ?>"><?= $aaIcon('send', 'h-4 w-4') ?>Share WA</a>
                                            <?php endif ?>
                                            <?php if ($isPublished && ! $isFreeExpired): ?>
                                                <a class="flex items-center gap-2 px-4 py-2.5 font-medium text-slate-800 transition hover:bg-slate-50" href="<?= site_url('u/' . $page['slug']) ?>" target="_blank" rel="noopener"><?= $aaIcon('external', 'h-4 w-4') ?><?= esc((string) $projectSurface['open_label']) ?></a>
                                            <?php else: ?>
                                                <span class="flex items-center gap-2 px-4 py-2.5 font-medium text-slate-400"><?= $aaIcon('external', 'h-4 w-4') ?><?= esc((string) $projectSurface['open_label']) ?></span>
                                            <?php endif ?>
                                            <div class="my-1 border-t border-slate-200"></div>
                                            <form action="<?= site_url('dashboard/pages/delete/' . $page['id']) ?>" method="post" onsubmit="return aaConfirmSubmit(event, 'Hapus <?= esc((string) $projectSurface['delete_subject'], 'js') ?> ini? Tindakan ini tidak bisa dibatalkan.', {title: 'Hapus <?= esc((string) $projectSurface['delete_subject'], 'js') ?>', okText: 'Hapus', cancelText: 'Batal', danger: true});">
                                                <?= csrf_field() ?>
                                                <button class="flex w-full items-center gap-2 px-4 py-2.5 text-left font-medium text-rose-600 transition hover:bg-rose-50" type="submit"><?= $aaIcon('trash', 'h-4 w-4') ?>Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-b-[22px] bg-white px-4 py-4">
                                <h4 class="truncate text-sm font-black text-slate-950"><?= esc($page['title']) ?></h4>
                                <p class="mt-1 text-xs font-medium text-slate-500">ID #<?= esc((string) $page['id']) ?></p>
                                <?php if ($updatedLabel !== ''): ?>
                                    <p class="mt-1 text-xs font-medium text-slate-500">Diedit <?= esc($updatedLabel) ?></p>
                                <?php endif ?>
                                <div class="mt-3 grid gap-2 text-xs font-bold text-slate-600">
                                    <?php if ($projectSurface['has_invitation_tools']): ?>
                                    <p class="flex items-center justify-between gap-2 rounded-2xl bg-slate-50 px-3 py-2">
                                        <span class="flex min-w-0 items-center gap-1.5">
                                            <span>Guestbook</span>
                                            <?php if ($guestbookUnreadCount > 0): ?>
                                                <span class="inline-flex h-5 items-center rounded-full bg-emerald-100 px-2 text-[10px] font-black text-emerald-700 ring-1 ring-emerald-200">
                                                    <?= esc((string) min($guestbookUnreadCount, 99)) ?><?= $guestbookUnreadCount > 99 ? '+' : '' ?> baru
                                                </span>
                                            <?php endif ?>
                                        </span>
                                        <span class="font-black text-slate-950"><?= esc((string) $guestbookCount) ?></span>
                                    </p>
                                    <?php endif ?>
                                    <?php if ($publicUrl !== ''): ?>
                                        <p class="truncate rounded-2xl bg-slate-50 px-3 py-2 text-slate-500"><?= esc($publicUrl) ?></p>
                                    <?php else: ?>
                                        <p class="rounded-2xl bg-slate-50 px-3 py-2 text-slate-500"><?= esc((string) $projectSurface['public_empty']) ?></p>
                                    <?php endif ?>
                                </div>
                                <?php if ($pageExpiryLabel !== ''): ?>
                                    <p class="mt-2 flex items-center gap-1.5 text-xs font-black <?= $isFreeExpired ? 'text-rose-600' : 'text-slate-500' ?>"><?= $aaIcon('calendar', 'h-3.5 w-3.5') ?>Expired: <?= esc($pageExpiryLabel) ?></p>
                                <?php endif ?>
                                <div class="mt-4 grid gap-2">
                                    <a class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 text-xs font-black text-white transition hover:bg-slate-800" href="<?= esc($primaryActionUrl, 'attr') ?>" <?= $publicUrl !== '' ? 'target="_blank" rel="noopener"' : '' ?>>
                                        <?= $aaIcon($primaryActionIcon, 'h-4 w-4') ?><?= esc($primaryActionLabel) ?>
                                    </a>
                                    <?php if ($publicUrl !== ''): ?>
                                        <button class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-800 transition hover:border-violet-400" type="button" data-copy-public-link="<?= esc($publicUrl, 'attr') ?>">
                                            <?= $aaIcon('link', 'h-4 w-4') ?><?= esc((string) $projectSurface['copy_label']) ?>
                                        </button>
                                    <?php endif ?>
                                    <?php if ($projectSurface['has_invitation_tools']): ?>
                                    <button class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-violet-200 bg-violet-50 px-4 text-xs font-black text-violet-800 transition hover:border-violet-400 hover:bg-violet-100" type="button" data-rsvp-share-trigger data-rsvp-share-url="<?= esc(site_url('dashboard/pages/' . $page['id'] . '/rsvp-link'), 'attr') ?>" data-rsvp-share-title="<?= esc((string) ($page['title'] ?? 'Undangan'), 'attr') ?>">
                                        <?= $aaIcon('share', 'h-4 w-4') ?>Sharing RSVP
                                    </button>
                                    <?php endif ?>
                                    <?php if ($projectSurface['has_photobooth']): ?>
                                    <?php if ($canUseGuestMemories): ?>
                                        <button class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 text-xs font-black text-emerald-800 transition hover:border-emerald-400 hover:bg-emerald-100" type="button" data-photobooth-domain-trigger data-photobooth-domain-url="<?= esc(site_url('dashboard/pages/' . $page['id'] . '/photobooth-domain'), 'attr') ?>" data-photobooth-domain-proof-url="<?= esc(site_url('dashboard/pages/' . $page['id'] . '/photobooth-domain/proof'), 'attr') ?>" data-photobooth-domain-title="<?= esc((string) ($page['title'] ?? 'Undangan'), 'attr') ?>">
                                            <?= $aaIcon('camera', 'h-4 w-4') ?>Publish Frame Photobooth
                                        </button>
                                    <?php else: ?>
                                        <button class="inline-flex h-10 cursor-not-allowed items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-xs font-black text-slate-400 opacity-60" type="button" disabled aria-disabled="true" title="<?= esc($photoboothInactiveTitle, 'attr') ?>">
                                            <?= $aaIcon('camera', 'h-4 w-4') ?>Publish Frame Photobooth
                                        </button>
                                    <?php endif ?>
                                    <?php endif ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </section>

        <section class="mt-8 grid gap-3 rounded-[28px] border border-emerald-100 bg-white/80 p-4 shadow-sm shadow-slate-900/5 backdrop-blur sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center gap-3 rounded-3xl bg-emerald-50/70 px-4 py-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-emerald-700 shadow-sm"><?= $aaIcon('shield', 'h-5 w-5') ?></span>
                <div>
                    <p class="text-sm font-black text-slate-900">Aman & Terpercaya</p>
                    <p class="mt-0.5 text-xs font-medium leading-5 text-slate-500">Data undangan dan tamu tersimpan rapi.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-3xl bg-teal-50/70 px-4 py-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-teal-700 shadow-sm"><?= $aaIcon('share', 'h-5 w-5') ?></span>
                <div>
                    <p class="text-sm font-black text-slate-900">Mudah Dibagikan</p>
                    <p class="mt-0.5 text-xs font-medium leading-5 text-slate-500">Salin link untuk WhatsApp dan sosial media.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-3xl bg-violet-50/70 px-4 py-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-violet-700 shadow-sm"><?= $aaIcon('sparkles', 'h-5 w-5') ?></span>
                <div>
                    <p class="text-sm font-black text-slate-900">Fitur Lengkap</p>
                    <p class="mt-0.5 text-xs font-medium leading-5 text-slate-500">Editor, RSVP, guestbook, musik, dan story.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-3xl bg-sky-50/70 px-4 py-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-sky-700 shadow-sm"><?= $aaIcon('users', 'h-5 w-5') ?></span>
                <div>
                    <p class="text-sm font-black text-slate-900">Support Cepat</p>
                    <p class="mt-0.5 text-xs font-medium leading-5 text-slate-500">Tim kami siap membantu saat dibutuhkan.</p>
                </div>
            </div>
        </section>
    </main>

    <?= view('components/site_footer') ?>
        </div>
    </div>
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/45 px-4 py-6 backdrop-blur-sm" data-rsvp-share-modal aria-hidden="true">
        <div class="w-full max-w-lg overflow-hidden rounded-[28px] border border-white/70 bg-white shadow-2xl shadow-slate-950/20">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-700">Sharing RSVP</p>
                    <h2 class="mt-1 truncate text-xl font-black text-slate-950" data-rsvp-share-title>Undangan</h2>
                </div>
                <button class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-700 transition hover:bg-slate-200" type="button" aria-label="Tutup" data-rsvp-share-close>
                    <span class="text-2xl leading-none">⛌</span>
                </button>
            </div>

            <div class="px-5 py-5">
                <div class="rounded-3xl border border-violet-100 bg-violet-50/70 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Kode akses RSVP</p>
                    <p class="mt-2 break-all text-3xl font-black tracking-tight text-slate-950" data-rsvp-share-code>Memuat...</p>
                    <p class="mt-3 break-all rounded-2xl bg-white px-3 py-2 text-sm font-semibold leading-6 text-slate-600 ring-1 ring-violet-100" data-rsvp-share-link></p>
                </div>

                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <button class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-900 px-4 text-sm font-black text-white transition hover:bg-slate-800" type="button" data-rsvp-share-copy>Copy Akses</button>
                    <div>
                        <button class="inline-flex h-11 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-900 transition hover:border-violet-400" type="button" data-rsvp-share-dropdown-toggle>Share RSVP</button>
                        <div class="mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 text-sm shadow-lg shadow-slate-900/10" data-rsvp-share-dropdown>
                            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left font-semibold text-slate-800 transition hover:bg-emerald-50 hover:text-emerald-700" type="button" data-rsvp-share-channel="whatsapp"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-500 text-white"><?= $aaSocialIcon('whatsapp') ?></span>WhatsApp</button>
                            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left font-semibold text-slate-800 transition hover:bg-blue-50 hover:text-blue-700" type="button" data-rsvp-share-channel="facebook"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-blue-600 text-white"><?= $aaSocialIcon('facebook') ?></span>Facebook</button>
                            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left font-semibold text-slate-800 transition hover:bg-pink-50 hover:text-pink-700" type="button" data-rsvp-share-channel="instagram"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-400 via-pink-500 to-violet-600 text-white"><?= $aaSocialIcon('instagram') ?></span>Instagram</button>
                            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left font-semibold text-slate-800 transition hover:bg-slate-100" type="button" data-rsvp-share-channel="threads"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-950 text-white"><?= $aaSocialIcon('threads') ?></span>Threads</button>
                            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left font-semibold text-slate-800 transition hover:bg-slate-100" type="button" data-rsvp-share-channel="x"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-900 text-white"><?= $aaSocialIcon('x') ?></span>X</button>
                        </div>
                    </div>
                </div>

                <p class="mt-3 min-h-5 text-sm font-semibold text-slate-500" data-rsvp-share-status></p>
            </div>
        </div>
    </div>
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/45 px-4 py-6 backdrop-blur-sm" data-photobooth-domain-modal aria-hidden="true">
        <div class="w-full max-w-2xl overflow-hidden rounded-[28px] border border-white/70 bg-white shadow-2xl shadow-slate-950/20">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Publish Frame Photobooth</p>
                    <h2 class="mt-1 truncate text-xl font-black text-slate-950" data-photobooth-domain-title>Undangan</h2>
                </div>
                <button class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-700 transition hover:bg-slate-200" type="button" aria-label="Tutup" data-photobooth-domain-close>
                    <span class="text-2xl leading-none">⛌</span>
                </button>
            </div>

            <form class="px-5 py-5" data-photobooth-domain-form>
                <div class="grid gap-3 sm:grid-cols-2" data-photobooth-domain-options>
                    <label class="grid cursor-pointer gap-3 rounded-3xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-emerald-300">
                        <span class="flex items-start gap-3">
                            <input class="mt-1" type="radio" name="domain_mode" value="adaacara" checked>
                            <span>
                                <strong class="block text-sm font-black text-slate-950">Gunakan domain adaAcara.com</strong>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">Pakai link standar photobooth yang langsung aktif.</span>
                            </span>
                        </span>
                        <span class="break-all rounded-2xl bg-white px-3 py-2 text-xs font-bold leading-5 text-slate-600 ring-1 ring-slate-200" data-photobooth-standard-url>Memuat...</span>
                    </label>

                    <label class="grid cursor-pointer gap-3 rounded-3xl border border-emerald-200 bg-emerald-50/70 p-4 transition hover:border-emerald-400">
                        <span class="flex items-start gap-3">
                            <input class="mt-1" type="radio" name="domain_mode" value="custom">
                            <span>
                                <strong class="block text-sm font-black text-slate-950">Gunakan custom domain</strong>
                                <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">Rp250.000/tahun untuk domain .com atau .id. Terpisah dari membership.</span>
                            </span>
                        </span>
                        <input class="h-11 rounded-2xl border border-emerald-100 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" type="text" name="custom_domain" placeholder="contoh: namaphotobooth.com" autocomplete="off" data-photobooth-custom-domain>
                    </label>
                </div>

                <div class="mt-4 rounded-3xl border border-violet-100 bg-violet-50/70 p-4" data-photobooth-domain-status-panel>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Status domain</p>
                            <p class="mt-2 inline-flex min-h-8 items-center rounded-full bg-white px-4 py-1 text-sm font-black text-violet-900 ring-1 ring-violet-100" data-photobooth-domain-status>Domain sedang dicek</p>
                            <p class="mt-2 hidden break-all text-2xl font-black text-slate-950" data-photobooth-domain-value></p>
                        </div>
                        <span class="inline-flex h-8 items-center rounded-full bg-white px-3 text-xs font-black text-violet-800 ring-1 ring-violet-100" data-photobooth-domain-price>Rp250.000 / tahun</span>
                    </div>
                    <p class="mt-3 text-sm font-semibold leading-6 text-slate-600" data-photobooth-domain-note>Nama domain yang dipilih akan dicek ketersediaannya oleh admin. Setelah tersedia dan pembayaran dikonfirmasi, domain akan disiapkan dan dihubungkan ke Photobooth.</p>
                </div>

                <div class="mt-4 hidden rounded-3xl border border-amber-100 bg-amber-50/80 p-4" data-photobooth-domain-payment-panel>
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-amber-700">Pembayaran add-on domain</p>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-700" data-photobooth-payment-instruction>Transfer add-on custom domain Photobooth sebesar Rp250.000/tahun. Setelah transfer, upload bukti pembayaran di sini agar admin dapat mengonfirmasi dan menyiapkan aktivasi domain.</p>
                    <div class="mt-3 grid gap-2">
                        <p class="hidden rounded-2xl bg-white px-3 py-2 text-xs font-black leading-5 text-amber-800 ring-1 ring-amber-100" data-photobooth-payment-order-status></p>
                        <a class="hidden h-11 items-center justify-center rounded-2xl bg-amber-600 px-4 text-sm font-black text-white transition hover:bg-amber-700" href="#" target="_blank" rel="noopener" data-photobooth-payment-checkout>Lanjut Pembayaran</a>
                        <input class="h-11 rounded-2xl border border-amber-100 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-photobooth-payment-proof>
                        <input class="h-11 rounded-2xl border border-amber-100 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" type="text" maxlength="500" placeholder="Catatan opsional, misalnya nama rekening pengirim" data-photobooth-payment-note>
                        <button class="inline-flex h-11 items-center justify-center rounded-2xl bg-amber-600 px-4 text-sm font-black text-white transition hover:bg-amber-700" type="button" data-photobooth-payment-upload>Upload Bukti Pembayaran</button>
                        <p class="min-h-4 text-xs font-bold text-slate-500" data-photobooth-payment-status></p>
                    </div>
                </div>

                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <button class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-900 px-4 text-sm font-black text-white transition hover:bg-slate-800" type="submit" data-photobooth-domain-submit>Simpan Pilihan</button>
                    <button class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-900 transition hover:border-emerald-400" type="button" data-photobooth-domain-copy>Copy Link adaAcara</button>
                </div>
                <p class="mt-3 min-h-5 text-sm font-semibold text-slate-500" data-photobooth-domain-message></p>
            </form>
        </div>
    </div>
    <script>
        (function() {
            let aaDashboardLenis = null;
            let aaDashboardLenisRafStarted = false;

            function isDarkTheme() {
                return document.documentElement.dataset.aaPublicTheme === 'dark';
            }

            function shouldReduceMotion() {
                return window.matchMedia &&
                    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            }

            function dashboardLenisOptions() {
                if (isDarkTheme()) {
                    return {
                        duration: .72,
                        easing: function(t) {
                            return 1 - Math.pow(1 - t, 3);
                        },
                        smoothWheel: true,
                        wheelMultiplier: .78,
                        touchMultiplier: 1,
                    };
                }

                return {
                    duration: 1.05,
                    easing: function(t) {
                        return Math.min(1, 1.001 - Math.pow(2, -10 * t));
                    },
                    smoothWheel: true,
                    wheelMultiplier: .9,
                    touchMultiplier: 1.1,
                };
            }

            function destroyDashboardLenis() {
                if (!aaDashboardLenis) return;
                if (typeof aaDashboardLenis.destroy === 'function') {
                    aaDashboardLenis.destroy();
                }
                aaDashboardLenis = null;
                window.aaDashboardLenis = null;
            }

            function initDashboardLenis() {
                if (shouldReduceMotion() || isDarkTheme()) {
                    destroyDashboardLenis();
                    return;
                }

                if (aaDashboardLenis || !window.Lenis) return;

                aaDashboardLenis = new window.Lenis(dashboardLenisOptions());
                window.aaDashboardLenis = aaDashboardLenis;

                if (!aaDashboardLenisRafStarted) {
                    aaDashboardLenisRafStarted = true;

                    function raf(time) {
                        if (aaDashboardLenis) aaDashboardLenis.raf(time);
                        window.requestAnimationFrame(raf);
                    }

                    window.requestAnimationFrame(raf);
                }
            }

            initDashboardLenis();
            window.addEventListener('load', initDashboardLenis, {
                once: true,
            });
            document.addEventListener('adaacara:dashboard-theme-change', function() {
                destroyDashboardLenis();
                initDashboardLenis();
            });
        })();

        document.querySelectorAll('[data-dashboard-notification]').forEach(function(menu) {
            const toggle = menu.querySelector('[data-dashboard-notification-toggle]');
            if (!toggle) return;
            let readRequestSent = false;

            function markNotificationsRead() {
                const badge = menu.querySelector('[data-dashboard-notification-badge]');
                const summary = menu.querySelector('[data-dashboard-notification-summary]');
                if (badge) badge.remove();
                if (summary) summary.remove();

                menu.querySelectorAll('[data-dashboard-notification-icon]').forEach(function(icon) {
                    icon.classList.remove('bg-emerald-50', 'text-emerald-700');
                    icon.classList.add('bg-slate-100', 'text-slate-500');
                });

                if (readRequestSent) return;
                readRequestSent = true;

                const readUrl = menu.getAttribute('data-dashboard-notification-read-url');
                if (!readUrl || !window.fetch) return;
                const csrfName = menu.getAttribute('data-dashboard-notification-csrf-name') || '';
                const csrfHash = menu.getAttribute('data-dashboard-notification-csrf-hash') || '';
                const body = new URLSearchParams();
                if (csrfName && csrfHash) {
                    body.set(csrfName, csrfHash);
                }

                window.fetch(readUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: body.toString(),
                }).then(function(response) {
                    return response.ok ? response.json().catch(function() { return null; }) : null;
                }).then(function(payload) {
                    if (payload && payload.csrf_hash) {
                        menu.setAttribute('data-dashboard-notification-csrf-hash', payload.csrf_hash);
                    }
                }).catch(function() {
                    readRequestSent = false;
                });
            }

            toggle.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                const isOpen = menu.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                if (isOpen) {
                    markNotificationsRead();
                }
            });

            document.addEventListener('click', function(event) {
                if (menu.contains(event.target)) return;
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
            });

            document.addEventListener('keydown', function(event) {
                if (event.key !== 'Escape') return;
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', function(event) {
            const toggle = event.target.closest('[data-dashboard-card-menu-toggle]');
            document.querySelectorAll('[data-dashboard-card-menu-panel]').forEach(function(panel) {
                if (!toggle || !panel.parentElement.contains(toggle)) {
                    panel.classList.add('hidden');
                }
            });
            if (toggle) {
                event.preventDefault();
                const panel = toggle.closest('[data-dashboard-card-menu]')?.querySelector('[data-dashboard-card-menu-panel]');
                panel?.classList.toggle('hidden');
            }
        });
        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('[data-dashboard-card-menu-panel]').forEach(function(panel) {
                panel.classList.add('hidden');
            });
        });

        document.addEventListener('click', function(event) {
            const button = event.target.closest('[data-copy-public-link]');
            if (!button) return;
            event.preventDefault();

            const value = button.getAttribute('data-copy-public-link') || '';
            const originalHtml = button.innerHTML;
            const setDone = function() {
                button.textContent = 'Link Tersalin';
                window.setTimeout(function() {
                    button.innerHTML = originalHtml;
                }, 1400);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value).then(setDone).catch(setDone);
                return;
            }

            const input = document.createElement('textarea');
            input.value = value;
            input.setAttribute('readonly', 'readonly');
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            try {
                document.execCommand('copy');
            } catch (error) {
            }
            input.remove();
            setDone();
        });

        (function() {
            const modal = document.querySelector('[data-rsvp-share-modal]');
            if (!modal) return;

            const titleNode = modal.querySelector('[data-rsvp-share-title]');
            const codeNode = modal.querySelector('[data-rsvp-share-code]');
            const linkNode = modal.querySelector('[data-rsvp-share-link]');
            const copyButton = modal.querySelector('[data-rsvp-share-copy]');
            const statusNode = modal.querySelector('[data-rsvp-share-status]');
            const dropdownToggle = modal.querySelector('[data-rsvp-share-dropdown-toggle]');
            const dropdown = modal.querySelector('[data-rsvp-share-dropdown]');
            let activeUrl = '';
            let activeTitle = '';
            let activeCode = '';
            let csrfName = <?= json_encode(function_exists('csrf_token') ? csrf_token() : '') ?>;
            let csrfHash = <?= json_encode(function_exists('csrf_hash') ? csrf_hash() : '') ?>;

            function setStatus(message, tone) {
                if (!statusNode) return;
                statusNode.textContent = message || '';
                statusNode.classList.toggle('text-rose-600', tone === 'error');
                statusNode.classList.toggle('text-emerald-600', tone === 'success');
                statusNode.classList.toggle('text-slate-500', !tone);
            }

            function writeClipboard(value, done) {
                const finish = typeof done === 'function' ? done : function() {};
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(value).then(finish).catch(finish);
                    return;
                }

                const input = document.createElement('textarea');
                input.value = value;
                input.setAttribute('readonly', 'readonly');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                try {
                    document.execCommand('copy');
                } catch (error) {
                }
                input.remove();
                finish();
            }

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
                dropdown?.classList.add('hidden');
            }

            function shareText() {
                const title = activeTitle || 'undangan';
                const lines = [
                    'Akses RSVP ' + title,
                    '',
                    'Link RSVP:',
                    activeUrl,
                ];

                if (activeCode) {
                    lines.push('', 'Kode akses:', activeCode);
                }

                lines.push('', 'Masukkan kode akses setelah membuka link RSVP.');

                return lines.join('\n').trim();
            }

            function openShare(channel) {
                if (!activeUrl) return;
                const encodedUrl = encodeURIComponent(activeUrl);
                const encodedText = encodeURIComponent(shareText());
                const encodedTitle = encodeURIComponent(activeTitle || 'Dashboard RSVP');
                let url = '';

                if (channel === 'whatsapp') {
                    url = 'https://wa.me/?text=' + encodedText;
                } else if (channel === 'facebook') {
                    url = 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl;
                } else if (channel === 'threads') {
                    url = 'https://www.threads.net/intent/post?text=' + encodedText;
                } else if (channel === 'x') {
                    url = 'https://twitter.com/intent/tweet?text=' + encodedTitle + '&url=' + encodedUrl;
                } else if (channel === 'instagram') {
                    writeClipboard(shareText(), function() {
                        setStatus('Link RSVP disalin. Lanjutkan kirim via Instagram.', 'success');
                        window.open('https://www.instagram.com/', '_blank', 'noopener');
                    });
                    return;
                }

                if (url) {
                    window.open(url, '_blank', 'noopener');
                }
            }

            document.addEventListener('click', function(event) {
                const trigger = event.target.closest('[data-rsvp-share-trigger]');
                if (!trigger) return;
                event.preventDefault();

                activeUrl = '';
                activeTitle = trigger.getAttribute('data-rsvp-share-title') || 'Undangan';
                activeCode = '';
                if (titleNode) titleNode.textContent = activeTitle;
                if (codeNode) codeNode.textContent = 'Memuat...';
                if (linkNode) linkNode.textContent = '';
                setStatus('', '');
                dropdown?.classList.add('hidden');
                openModal();

                const url = trigger.getAttribute('data-rsvp-share-url') || '';
                const body = new URLSearchParams();
                if (csrfName && csrfHash) {
                    body.set(csrfName, csrfHash);
                }

                window.fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: body.toString(),
                }).then(function(response) {
                    return response.json().then(function(payload) {
                        return { ok: response.ok, payload: payload };
                    }).catch(function() {
                        return { ok: response.ok, payload: null };
                    });
                }).then(function(result) {
                    const payload = result.payload || {};
                    if (payload.csrf_hash) {
                        csrfHash = payload.csrf_hash;
                    }
                    if (!result.ok || !payload.success) {
                        throw new Error(payload.message || 'Link RSVP belum bisa dibuat.');
                    }

                    activeUrl = payload.url || '';
                    activeTitle = payload.title || activeTitle;
                    activeCode = payload.code || '';
                    if (titleNode) titleNode.textContent = activeTitle;
                    if (codeNode) codeNode.textContent = activeCode || 'RSVP';
                    if (linkNode) linkNode.textContent = activeUrl;
                    setStatus('Link RSVP siap dibagikan.', 'success');
                }).catch(function(error) {
                    if (codeNode) codeNode.textContent = 'Belum siap';
                    if (linkNode) linkNode.textContent = '';
                    setStatus(error.message || 'Link RSVP belum bisa dibuat.', 'error');
                });
            });

            copyButton?.addEventListener('click', function(event) {
                event.preventDefault();
                if (!activeUrl) return;
                writeClipboard(shareText(), function() {
                    const originalText = copyButton.textContent;
                    copyButton.textContent = 'Akses Tersalin';
                    setStatus('Link dan kode RSVP tersalin.', 'success');
                    window.setTimeout(function() {
                        copyButton.textContent = originalText;
                    }, 1400);
                });
            });

            dropdownToggle?.addEventListener('click', function(event) {
                event.preventDefault();
                dropdown?.classList.toggle('hidden');
            });

            modal.querySelectorAll('[data-rsvp-share-channel]').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    dropdown?.classList.add('hidden');
                    openShare(button.getAttribute('data-rsvp-share-channel') || '');
                });
            });

            modal.querySelectorAll('[data-rsvp-share-close]').forEach(function(button) {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function(event) {
                if (event.target === modal) closeModal();
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') closeModal();
            });
        })();

        (function() {
            const modal = document.querySelector('[data-photobooth-domain-modal]');
            if (!modal) return;

            const form = modal.querySelector('[data-photobooth-domain-form]');
            const titleNode = modal.querySelector('[data-photobooth-domain-title]');
            const standardUrlNode = modal.querySelector('[data-photobooth-standard-url]');
            const statusNode = modal.querySelector('[data-photobooth-domain-status]');
            const priceNode = modal.querySelector('[data-photobooth-domain-price]');
            const noteNode = modal.querySelector('[data-photobooth-domain-note]');
            const messageNode = modal.querySelector('[data-photobooth-domain-message]');
            const customDomainInput = modal.querySelector('[data-photobooth-custom-domain]');
            const copyButton = modal.querySelector('[data-photobooth-domain-copy]');
            const optionsPanel = modal.querySelector('[data-photobooth-domain-options]');
            const statusPanel = modal.querySelector('[data-photobooth-domain-status-panel]');
            const domainValueNode = modal.querySelector('[data-photobooth-domain-value]');
            const submitButton = modal.querySelector('[data-photobooth-domain-submit]');
            const paymentPanel = modal.querySelector('[data-photobooth-domain-payment-panel]');
            const paymentInstructionNode = modal.querySelector('[data-photobooth-payment-instruction]');
            const paymentOrderStatusNode = modal.querySelector('[data-photobooth-payment-order-status]');
            const paymentCheckoutButton = modal.querySelector('[data-photobooth-payment-checkout]');
            const paymentProofInput = modal.querySelector('[data-photobooth-payment-proof]');
            const paymentNoteInput = modal.querySelector('[data-photobooth-payment-note]');
            const paymentUploadButton = modal.querySelector('[data-photobooth-payment-upload]');
            const paymentStatusNode = modal.querySelector('[data-photobooth-payment-status]');
            let activeEndpoint = '';
            let activeProofEndpoint = '';
            let standardUrl = '';
            let csrfName = <?= json_encode(function_exists('csrf_token') ? csrf_token() : '') ?>;
            let csrfHash = <?= json_encode(function_exists('csrf_hash') ? csrf_hash() : '') ?>;

            function rupiah(value) {
                return 'Rp' + Number(value || 0).toLocaleString('id-ID') + ' / tahun';
            }

            function setMessage(message, tone) {
                if (!messageNode) return;
                messageNode.textContent = message || '';
                messageNode.classList.toggle('text-rose-600', tone === 'error');
                messageNode.classList.toggle('text-emerald-600', tone === 'success');
                messageNode.classList.toggle('text-slate-500', !tone);
            }

            function setMode(mode) {
                const input = form?.querySelector('input[name="domain_mode"][value="' + mode + '"]');
                if (input) input.checked = true;
            }

            function isDomainLocked(payload) {
                const status = String(payload?.status || '');
                return payload?.mode === 'custom' && payload?.domain && status !== 'unavailable';
            }

            function setDomainLocked(payload) {
                const locked = isDomainLocked(payload);
                const domain = String(payload?.domain || '');
                const statusHeader = statusPanel?.querySelector('.flex');
                const statusTextBlock = statusHeader?.firstElementChild;
                optionsPanel?.classList.toggle('hidden', locked);
                submitButton?.classList.toggle('hidden', locked);
                copyButton?.classList.toggle('sm:col-span-2', locked);
                copyButton?.classList.toggle('mx-auto', locked);
                copyButton?.classList.toggle('w-full', locked);
                copyButton?.classList.toggle('max-w-sm', locked);
                priceNode?.classList.toggle('hidden', locked);
                statusPanel?.classList.toggle('text-center', locked);
                statusPanel?.classList.toggle('py-8', locked);
                statusHeader?.classList.toggle('justify-center', locked);
                statusHeader?.classList.toggle('items-center', locked);
                statusHeader?.classList.toggle('flex-col', locked);
                statusTextBlock?.classList.toggle('w-full', locked);
                statusTextBlock?.classList.toggle('text-center', locked);
                if (domainValueNode) {
                    domainValueNode.textContent = domain;
                    domainValueNode.classList.toggle('hidden', !locked || domain === '');
                }
            }

            function syncPaymentPanel(payload) {
                if (!paymentPanel) return;
                const canUpload = payload?.can_upload_payment_proof === true;
                const hasSubmitted = Boolean(payload?.payment_proof || payload?.payment_submitted_at);
                const paymentStatus = String(payload?.payment_status || '');
                const shouldShow = canUpload || hasSubmitted || ['waiting_confirmation', 'paid'].includes(paymentStatus);
                paymentPanel.classList.toggle('hidden', !shouldShow);
                if (paymentInstructionNode) {
                    paymentInstructionNode.textContent = payload?.payment_instruction || 'Transfer add-on custom domain Photobooth sebesar Rp250.000/tahun. Setelah transfer, upload bukti pembayaran di sini agar admin dapat mengonfirmasi dan menyiapkan aktivasi domain.';
                }
                const paymentOrder = payload?.payment_order || null;
                if (paymentOrderStatusNode) {
                    const orderLabel = paymentOrder?.invoice_number
                        ? paymentOrder.invoice_number + ' - ' + (paymentOrder.status_label || paymentOrder.status || 'Status invoice')
                        : '';
                    paymentOrderStatusNode.textContent = orderLabel;
                    paymentOrderStatusNode.classList.toggle('hidden', orderLabel === '');
                }
                const checkoutUrl = String(payload?.payment_checkout_url || '');
                if (paymentCheckoutButton) {
                    paymentCheckoutButton.href = checkoutUrl || '#';
                    paymentCheckoutButton.textContent = paymentOrder?.detail_url ? 'Lihat Invoice' : 'Lanjut Pembayaran';
                    paymentCheckoutButton.classList.toggle('hidden', !canUpload || checkoutUrl === '');
                    paymentCheckoutButton.classList.toggle('flex', canUpload && checkoutUrl !== '');
                }
                const controlsHidden = !canUpload;
                paymentProofInput?.classList.toggle('hidden', controlsHidden);
                paymentNoteInput?.classList.toggle('hidden', controlsHidden);
                paymentUploadButton?.classList.toggle('hidden', controlsHidden);
                if (paymentStatusNode) {
                    paymentStatusNode.textContent = paymentStatus === 'paid'
                        ? 'Pembayaran sudah dikonfirmasi admin.'
                        : (hasSubmitted ? 'Bukti pembayaran sudah terkirim dan menunggu konfirmasi admin.' : '');
                }
            }

            function applyPayload(payload) {
                if (!payload) return;
                if (payload.csrf_hash) csrfHash = payload.csrf_hash;
                standardUrl = payload.standard_url || '';
                if (standardUrlNode) standardUrlNode.textContent = standardUrl || 'Link photobooth belum tersedia.';
                if (statusNode) statusNode.textContent = payload.status_label || 'Domain sedang dicek';
                if (priceNode) priceNode.textContent = rupiah(payload.price || 250000);
                if (noteNode) noteNode.textContent = payload.notes || 'Nama domain yang dipilih akan dicek ketersediaannya oleh admin. Setelah tersedia dan pembayaran dikonfirmasi, domain akan disiapkan dan dihubungkan ke Photobooth.';
                if (customDomainInput && payload.domain) customDomainInput.value = payload.domain;
                setMode(payload.mode === 'custom' ? 'custom' : 'adaacara');
                setDomainLocked(payload);
                syncPaymentPanel(payload);
            }

            function writeClipboard(value, done) {
                const finish = typeof done === 'function' ? done : function() {};
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(value).then(finish).catch(finish);
                    return;
                }

                const input = document.createElement('textarea');
                input.value = value;
                input.setAttribute('readonly', 'readonly');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                try {
                    document.execCommand('copy');
                } catch (error) {
                }
                input.remove();
                finish();
            }

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            }

            function fetchStatus(url) {
                window.fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                }).then(function(response) {
                    return response.json().then(function(payload) {
                        return { ok: response.ok, payload: payload };
                    }).catch(function() {
                        return { ok: response.ok, payload: null };
                    });
                }).then(function(result) {
                    const payload = result.payload || {};
                    if (!result.ok || !payload.success) {
                        throw new Error(payload.message || 'Status domain belum bisa dimuat.');
                    }
                    applyPayload(payload);
                    setMessage('', '');
                }).catch(function(error) {
                    setMessage(error.message || 'Status domain belum bisa dimuat.', 'error');
                });
            }

            document.addEventListener('click', function(event) {
                const trigger = event.target.closest('[data-photobooth-domain-trigger]');
                if (!trigger) return;
                if (trigger.disabled || trigger.getAttribute('aria-disabled') === 'true') return;
                event.preventDefault();

                activeEndpoint = trigger.getAttribute('data-photobooth-domain-url') || '';
                activeProofEndpoint = trigger.getAttribute('data-photobooth-domain-proof-url') || '';
                if (titleNode) titleNode.textContent = trigger.getAttribute('data-photobooth-domain-title') || 'Undangan';
                if (standardUrlNode) standardUrlNode.textContent = 'Memuat...';
                if (statusNode) statusNode.textContent = 'Memuat status...';
                if (customDomainInput) customDomainInput.value = '';
                setMode('adaacara');
                setDomainLocked({ mode: 'adaacara', status: 'standard', domain: '' });
                syncPaymentPanel({ payment_status: 'not_required', can_upload_payment_proof: false });
                setMessage('', '');
                openModal();
                if (activeEndpoint) fetchStatus(activeEndpoint);
            });

            form?.addEventListener('submit', function(event) {
                event.preventDefault();
                if (!activeEndpoint) return;

                const body = new URLSearchParams(new FormData(form));
                if (csrfName && csrfHash) {
                    body.set(csrfName, csrfHash);
                }

                setMessage('Menyimpan pilihan domain...', '');
                window.fetch(activeEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: body.toString(),
                }).then(function(response) {
                    return response.json().then(function(payload) {
                        return { ok: response.ok, payload: payload };
                    }).catch(function() {
                        return { ok: response.ok, payload: null };
                    });
                }).then(function(result) {
                    const payload = result.payload || {};
                    if (payload.csrf_hash) csrfHash = payload.csrf_hash;
                    if (!result.ok || !payload.success) {
                        throw new Error(payload.message || 'Pilihan domain belum bisa disimpan.');
                    }
                    applyPayload(payload);
                    setMessage(payload.message || 'Pilihan domain tersimpan.', 'success');
                }).catch(function(error) {
                    setMessage(error.message || 'Pilihan domain belum bisa disimpan.', 'error');
                });
            });

            copyButton?.addEventListener('click', function(event) {
                event.preventDefault();
                if (!standardUrl) {
                    setMessage('Link Photobooth adaAcara belum tersedia.', 'error');
                    return;
                }
                writeClipboard(standardUrl, function() {
                    setMessage('Link Photobooth adaAcara tersalin.', 'success');
                });
            });

            paymentUploadButton?.addEventListener('click', function(event) {
                event.preventDefault();
                if (!activeProofEndpoint) return;
                const file = paymentProofInput?.files?.[0] || null;
                if (!file) {
                    if (paymentStatusNode) paymentStatusNode.textContent = 'Pilih file bukti pembayaran terlebih dahulu.';
                    return;
                }

                const formData = new FormData();
                formData.set('payment_proof', file);
                formData.set('payment_note', paymentNoteInput?.value || '');
                if (csrfName && csrfHash) {
                    formData.set(csrfName, csrfHash);
                }

                paymentUploadButton.disabled = true;
                const originalText = paymentUploadButton.textContent;
                paymentUploadButton.textContent = 'Mengupload...';
                if (paymentStatusNode) paymentStatusNode.textContent = 'Mengupload bukti pembayaran...';

                window.fetch(activeProofEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    body: formData,
                }).then(function(response) {
                    return response.json().then(function(payload) {
                        return { ok: response.ok, payload: payload };
                    }).catch(function() {
                        return { ok: response.ok, payload: null };
                    });
                }).then(function(result) {
                    const payload = result.payload || {};
                    if (payload.csrf_hash) csrfHash = payload.csrf_hash;
                    if (!result.ok || !payload.success) {
                        throw new Error(payload.message || 'Bukti pembayaran gagal diupload.');
                    }
                    applyPayload(payload);
                    setMessage(payload.message || 'Bukti pembayaran terkirim.', 'success');
                }).catch(function(error) {
                    if (paymentStatusNode) paymentStatusNode.textContent = error.message || 'Bukti pembayaran gagal diupload.';
                    setMessage(error.message || 'Bukti pembayaran gagal diupload.', 'error');
                }).finally(function() {
                    paymentUploadButton.disabled = false;
                    paymentUploadButton.textContent = originalText || 'Upload Bukti Pembayaran';
                });
            });

            modal.querySelectorAll('[data-photobooth-domain-close]').forEach(function(button) {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function(event) {
                if (event.target === modal) closeModal();
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') closeModal();
            });
        })();

        (function() {
            const grid = document.querySelector('[data-dashboard-grid]');
            if (!grid) return;

            const cards = Array.from(grid.querySelectorAll('[data-dashboard-card]'));
            const searchInput = document.querySelector('[data-dashboard-search]');
            const statusFilter = document.querySelector('[data-dashboard-status-filter]');
            const projectFilter = document.querySelector('[data-dashboard-project-filter]');
            const sortSelect = document.querySelector('[data-dashboard-sort]');
            const countTarget = document.querySelector('[data-dashboard-filter-count]');
            const emptyState = document.querySelector('[data-dashboard-empty-filter]');

            function normalize(value) {
                return String(value || '').toLowerCase().trim();
            }

            function sortCards() {
                const mode = sortSelect?.value || 'newest';
                const sorted = cards.slice().sort(function(a, b) {
                    if (mode === 'title') {
                        return (a.dataset.title || '').localeCompare(b.dataset.title || '', 'id', {sensitivity: 'base'});
                    }

                    const firstUpdated = parseInt(a.dataset.updated || '0', 10);
                    const secondUpdated = parseInt(b.dataset.updated || '0', 10);
                    if (firstUpdated !== secondUpdated) {
                        return mode === 'oldest' ? firstUpdated - secondUpdated : secondUpdated - firstUpdated;
                    }

                    const firstId = parseInt(a.dataset.id || '0', 10);
                    const secondId = parseInt(b.dataset.id || '0', 10);
                    return mode === 'oldest' ? firstId - secondId : secondId - firstId;
                });

                sorted.forEach(function(card) {
                    grid.appendChild(card);
                });
            }

            function applyFilters() {
                const query = normalize(searchInput?.value);
                const status = statusFilter?.value || 'all';
                const project = projectFilter?.value || 'all';
                let visible = 0;

                cards.forEach(function(card) {
                    const matchesSearch = query === '' || normalize(card.dataset.search).includes(query);
                    const matchesStatus = status === 'all' || card.dataset.status === status;
                    const matchesProject = project === 'all' || card.dataset.projectType === project;
                    const shouldShow = matchesSearch && matchesStatus && matchesProject;

                    card.classList.toggle('hidden', !shouldShow);
                    if (shouldShow) visible++;
                });

                if (countTarget) {
                    countTarget.textContent = String(visible);
                }

                emptyState?.classList.toggle('hidden', visible !== 0);
                grid.classList.toggle('hidden', visible === 0);
            }

            [searchInput, statusFilter, projectFilter].forEach(function(control) {
                control?.addEventListener('input', applyFilters);
                control?.addEventListener('change', applyFilters);
            });

            sortSelect?.addEventListener('change', function() {
                sortCards();
                applyFilters();
            });

            sortCards();
            applyFilters();
        })();
    </script>
</body>
</html>
