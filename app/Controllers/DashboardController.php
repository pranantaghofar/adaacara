<?php

namespace App\Controllers;

use App\Libraries\SellerTemplateService;
use App\Libraries\ProductEntitlementService;
use App\Models\CreatorProfileModel;
use App\Models\FreePublishEntitlementModel;
use App\Models\GuestbookAccessLinkModel;
use App\Models\LandingPageModel;
use App\Models\NotificationModel;
use App\Models\PhotoboothCustomDomainModel;
use App\Models\PhotoboothCustomDomainOrderModel;
use App\Models\TemplateWishlistModel;
use App\Models\UserSubscriptionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $landingPageModel = new LandingPageModel();
        $userId = (int) session()->get('userId');
        $landingPages = $landingPageModel->getByUser($userId);
        $subscription = (new UserSubscriptionModel())->activeWithPlanByUser($userId);
        $freeTemplateIds = $this->freeTemplateIds($landingPages);
        $freeExpiryDates = [];
        $draftCount = 0;
        $expiredCount = 0;
        $guestbookCounts = $this->guestbookCounts($landingPages);
        $guestbookUnreadCounts = $this->guestbookUnreadCounts($landingPages);
        $totalGuestbookCount = array_sum($guestbookCounts);
        $freeEntitlement = $subscription === null ? $this->freePublishEntitlement($userId) : null;
        $canUseGuestMemories = $this->canUseGuestMemories($userId);

        foreach ($landingPages as &$page) {
            $templateId = (int) ($page['template_id'] ?? 0);
            $page['access_tier'] = $templateId > 0 && in_array($templateId, $freeTemplateIds, true) ? 'free' : 'premium';
            $page['plan_label'] = $subscription === null && $page['access_tier'] === 'free' ? 'Free' : null;
            $page['free_expires_at'] = null;
            $page['free_is_expired'] = false;
            $page['guestbook_count'] = $guestbookCounts[(int) ($page['id'] ?? 0)] ?? 0;
            $page['guestbook_unread_count'] = $guestbookUnreadCounts[(int) ($page['id'] ?? 0)] ?? 0;

            if ($subscription === null && $page['access_tier'] === 'free' && ($page['status'] ?? '') === 'published') {
                $expiresAt = $this->freePublishedExpiresAt((string) ($page['published_at'] ?? ''), $freeEntitlement);
                $page['free_expires_at'] = $expiresAt;
                $page['free_is_expired'] = $expiresAt !== null && strtotime($expiresAt) < time();

                if ($expiresAt !== null && ! $page['free_is_expired']) {
                    $freeExpiryDates[] = $expiresAt;
                }
            }

            if (($page['status'] ?? 'draft') !== 'published') {
                $draftCount++;
            }

            if (! empty($page['free_is_expired'])) {
                $expiredCount++;
            }
        }
        unset($page);

        sort($freeExpiryDates);
        $dashboardExpiredLabel = $subscription !== null
            ? (string) ($subscription['expired_at'] ?? '-')
            : ($freeExpiryDates[0] ?? 'Free: 1 bulan setelah publish');

        $pageLimit = $subscription !== null ? $this->maxPublishedLinksFromSubscription($subscription) : 1;
        $pageCount = count($landingPages);
        $publishedCount = $landingPageModel
            ->where('user_id', $userId)
            ->where('status', 'published')
            ->countAllResults();
        $sellerService = new SellerTemplateService();
        $creatorStatus = (new CreatorProfileModel())->statusForUser($userId);
        $hideMembershipSummary = $subscription === null
            && in_array((string) ($creatorStatus['status'] ?? 'none'), ['pending', 'active'], true);
        $notificationModel = new NotificationModel();
        $storedNotifications = $notificationModel->latestForUser($userId, 8);
        $templateWishlists = (new TemplateWishlistModel())->latestTemplatesForUser($userId, 6);
        $smartNotifications = $this->dashboardSmartNotifications(
            $landingPages,
            $subscription,
            $publishedCount,
            $draftCount,
            $expiredCount,
            $totalGuestbookCount,
            $pageLimit,
            $creatorStatus
        );
        $smartNotificationsRead = (bool) session()->get('dashboard_smart_notifications_read');
        if ($smartNotificationsRead) {
            foreach ($smartNotifications as &$notification) {
                $notification['read_at'] = $notification['read_at'] ?: date('Y-m-d H:i:s');
            }
            unset($notification);
        }
        $dashboardNotifications = array_slice(array_merge($smartNotifications, $storedNotifications), 0, 8);
        $dashboardUnreadCount = $notificationModel->unreadCountForUser($userId)
            + ($smartNotificationsRead ? 0 : count($smartNotifications));

        return view('dashboard/index', [
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
            'landingPages' => $landingPages,
            'activeSubscription' => $subscription,
            'dashboardExpiredLabel' => $dashboardExpiredLabel,
            'pageLimit' => $pageLimit,
            'pageCount' => $pageCount,
            'publishedCount' => $publishedCount,
            'draftCount' => $draftCount,
            'expiredCount' => $expiredCount,
            'totalGuestbookCount' => $totalGuestbookCount,
            'canCreatePage' => true,
            'isAdmin' => in_array(strtolower((string) (session()->get('userRole') ?? '')), ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin'], true),
            'creatorStatus' => $creatorStatus,
            'hideMembershipSummary' => $hideMembershipSummary,
            'canUseGuestMemories' => $canUseGuestMemories,
            'canAccessSellerDashboard' => $sellerService->canAccessSellerDashboard($userId),
            'sellerBalance' => $sellerService->canAccessSellerDashboard($userId) ? $sellerService->walletBalance($userId) : null,
            'dashboardNotifications' => $dashboardNotifications,
            'dashboardUnreadCount' => $dashboardUnreadCount,
            'templateWishlists' => $templateWishlists,
        ]);
    }

    public function markNotificationsRead(): ResponseInterface
    {
        $userId = (int) session()->get('userId');

        (new NotificationModel())->markAllReadForUser($userId);
        session()->set('dashboard_smart_notifications_read', true);

        return $this->response->setJSON([
            'success' => true,
            'unread_count' => 0,
            'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
        ]);
    }

    public function rsvpLink(int $id): ResponseInterface
    {
        $page = $this->findOwnedPage($id);
        $accessModel = new GuestbookAccessLinkModel();

        if (! $accessModel->tableReady()) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Fitur Sharing RSVP belum siap. Jalankan database/alter_guestbook_access_links.sql terlebih dahulu.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $access = $accessModel->activeForPage((int) $page['id']);
        if ($access === null) {
            $accessModel->insert([
                'landing_page_id' => (int) $page['id'],
                'created_by_user_id' => (int) session()->get('userId'),
                'access_token' => bin2hex(random_bytes(32)),
                'enabled' => 1,
                'revoked_at' => null,
                'last_accessed_at' => null,
            ]);
            $access = $accessModel->find((int) $accessModel->getInsertID());
        }

        $token = (string) ($access['access_token'] ?? '');
        if (! preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Link RSVP belum bisa dibuat. Silakan coba lagi.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'title' => (string) ($page['title'] ?? 'Undangan'),
            'url' => site_url('rsvp/' . $token),
            'code' => strtoupper(substr($token, 0, 6) . '-' . substr($token, 6, 6)),
            'public_url' => ! empty($page['slug']) ? site_url('u/' . $page['slug']) : '',
            'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
        ]);
    }

    public function photoboothDomain(int $id): ResponseInterface
    {
        $page = $this->findOwnedPage($id);
        $domainModel = new PhotoboothCustomDomainModel();

        if (! $this->canUseGuestMemories((int) ($page['user_id'] ?? session()->get('userId')))) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Fitur Photobooth belum aktif untuk akun ini. Minta admin mengaktifkan Guest Memories terlebih dahulu.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        if (! $domainModel->tableReady()) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Fitur custom domain Photobooth belum siap. Jalankan database/alter_photobooth_custom_domains.sql terlebih dahulu.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        if (strtolower($this->request->getMethod()) === 'get') {
            return $this->response->setJSON($this->photoboothDomainPayload($page, $domainModel->latestForPage((int) $page['id'])));
        }

        $mode = strtolower(trim((string) $this->request->getPost('domain_mode')));
        if (! in_array($mode, ['adaacara', 'custom'], true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Pilih opsi domain Photobooth terlebih dahulu.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        if ($mode === 'adaacara') {
            $existing = $domainModel->latestForPage((int) $page['id']);
            if ($existing !== null) {
                $domainModel->delete((int) $existing['id']);
            }

            return $this->response->setJSON($this->photoboothDomainPayload($page, null, 'Link standar adaAcara.com siap digunakan.'));
        }

        $domain = $this->normalizePhotoboothDomain((string) $this->request->getPost('custom_domain'));
        $domainError = $this->validatePhotoboothDomain($domain);
        if ($domainError !== null) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => $domainError,
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $extension = substr($domain, -3) === '.id' ? 'id' : 'com';
        $existingForDomain = $domainModel->where('domain', $domain)->first();
        if ($existingForDomain !== null && (int) ($existingForDomain['landing_page_id'] ?? 0) !== (int) $page['id']) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON([
                    'success' => false,
                    'message' => 'Domain ini sudah diajukan untuk undangan lain.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $payload = [
            'landing_page_id' => (int) $page['id'],
            'user_id' => (int) session()->get('userId'),
            'domain' => $domain,
            'extension' => $extension,
            'target_type' => 'memories',
            'status' => 'checking',
            'availability_status' => 'checking',
            'payment_status' => 'unpaid',
            'price' => 250000,
            'billing_period' => 'yearly',
            'notes' => 'Nama domain yang dipilih akan dicek ketersediaannya oleh admin. Setelah tersedia dan pembayaran dikonfirmasi, domain akan disiapkan dan dihubungkan ke Photobooth.',
            'requested_at' => date('Y-m-d H:i:s'),
            'checked_at' => null,
            'activated_at' => null,
            'disabled_at' => null,
        ];

        $existing = $domainModel->latestForPage((int) $page['id']);
        if ($existing === null) {
            $domainModel->insert($payload);
            $existing = $domainModel->find((int) $domainModel->getInsertID());
        } else {
            $domainModel->update((int) $existing['id'], $payload);
            $existing = $domainModel->find((int) $existing['id']);
        }

        return $this->response->setJSON($this->photoboothDomainPayload($page, $existing, 'Request custom domain diterima. Domain sedang dicek.'));
    }

    public function photoboothDomainPaymentProof(int $id): ResponseInterface
    {
        $page = $this->findOwnedPage($id);
        $domainModel = new PhotoboothCustomDomainModel();

        if (! $this->canUseGuestMemories((int) ($page['user_id'] ?? session()->get('userId')))) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Fitur Photobooth belum aktif untuk akun ini. Minta admin mengaktifkan Guest Memories terlebih dahulu.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        if (! $domainModel->tableReady()) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Fitur pembayaran custom domain belum siap.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $db = db_connect();
        $fields = $db->getFieldNames('photobooth_custom_domains');
        foreach (['payment_proof', 'payment_note', 'payment_submitted_at', 'paid_at'] as $field) {
            if (! in_array($field, $fields, true)) {
                return $this->response
                    ->setStatusCode(503)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Kolom pembayaran custom domain belum tersedia. Jalankan database/alter_photobooth_custom_domain_payments.sql terlebih dahulu.',
                        'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                    ]);
            }
        }

        $domainRequest = $domainModel->latestForPage((int) $page['id']);
        if ($domainRequest === null || empty($domainRequest['domain'])) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Request custom domain belum ditemukan.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $status = (string) ($domainRequest['status'] ?? '');
        $paymentStatus = (string) ($domainRequest['payment_status'] ?? '');
        if (! in_array($status, ['available', 'waiting_payment'], true) || in_array($paymentStatus, ['paid', 'waiting_confirmation'], true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Bukti pembayaran belum bisa diupload untuk status domain saat ini.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $rules = [
            'payment_proof' => [
                'label' => 'Bukti pembayaran',
                'rules' => 'uploaded[payment_proof]|max_size[payment_proof,2048]|is_image[payment_proof]|mime_in[payment_proof,image/jpg,image/jpeg,image/png,image/webp]|ext_in[payment_proof,jpg,jpeg,png,webp]',
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => implode(' ', $this->validator->getErrors()),
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $file = $this->request->getFile('payment_proof');
        if (! $file || ! $file->isValid()) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'File bukti pembayaran tidak valid.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $uploadPath = FCPATH . 'uploads/photobooth-domain-proof';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = 'domain-' . (int) $domainRequest['id'] . '-' . time() . '.' . $file->getExtension();
        $file->move($uploadPath, $fileName, true);

        $domainModel->update((int) $domainRequest['id'], [
            'payment_proof' => 'uploads/photobooth-domain-proof/' . $fileName,
            'payment_note' => mb_substr(trim((string) $this->request->getPost('payment_note')), 0, 500) ?: null,
            'payment_status' => 'waiting_confirmation',
            'status' => 'waiting_payment',
            'payment_submitted_at' => date('Y-m-d H:i:s'),
            'notes' => 'Bukti pembayaran add-on domain sudah dikirim. Menunggu konfirmasi admin.',
        ]);

        $updated = $domainModel->find((int) $domainRequest['id']);

        return $this->response->setJSON($this->photoboothDomainPayload($page, $updated, 'Bukti pembayaran terkirim. Menunggu konfirmasi admin.'));
    }

    private function normalizePhotoboothDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;
        $domain = preg_replace('#/.*$#', '', $domain) ?? $domain;
        $domain = preg_replace('#:\d+$#', '', $domain) ?? $domain;
        $domain = preg_replace('/\s+/', '', $domain) ?? $domain;

        return trim($domain, ". \t\n\r\0\x0B");
    }

    private function validatePhotoboothDomain(string $domain): ?string
    {
        if ($domain === '') {
            return 'Masukkan domain custom terlebih dahulu.';
        }

        if (strlen($domain) > 190) {
            return 'Nama domain terlalu panjang.';
        }

        if (! preg_match('/\A[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+\z/', $domain)) {
            return 'Format domain belum valid. Contoh: namaphotobooth.com atau namaphotobooth.id.';
        }

        if (! preg_match('/\.(com|id)\z/', $domain)) {
            return 'Custom domain Photobooth saat ini hanya mendukung domain .com dan .id.';
        }

        if (str_ends_with($domain, '.adaacara.com') || $domain === 'adaacara.com') {
            return 'Domain adaAcara.com sudah tersedia sebagai opsi standar. Gunakan opsi domain adaAcara.com.';
        }

        return null;
    }

    private function photoboothDomainPayload(array $page, ?array $domainRequest = null, string $message = ''): array
    {
        $slug = (string) ($page['slug'] ?? '');
        $standardUrl = $slug !== '' ? site_url('u/' . $slug . '/memories') : '';
        $status = (string) ($domainRequest['status'] ?? 'standard');
        $availability = (string) ($domainRequest['availability_status'] ?? 'checking');
        $paymentStatus = (string) ($domainRequest['payment_status'] ?? ($status === 'standard' ? 'not_required' : 'unpaid'));

        $statusLabel = match ($status) {
            'available' => 'Domain tersedia',
            'unavailable' => 'Domain tidak tersedia',
            'waiting_payment' => 'Menunggu pembayaran add-on domain',
            'waiting_activation' => 'Menunggu aktivasi DNS/SSL',
            'active' => 'Custom domain aktif',
            'disabled' => 'Custom domain nonaktif',
            'checking' => 'Domain sedang dicek',
            default => 'Menggunakan domain adaAcara.com',
        };
        if ($status === 'waiting_payment' && $paymentStatus === 'waiting_confirmation') {
            $statusLabel = 'Menunggu konfirmasi pembayaran';
        }
        $defaultCheckingNote = 'Nama domain yang dipilih akan dicek ketersediaannya oleh admin. Setelah tersedia dan pembayaran dikonfirmasi, domain akan disiapkan dan dihubungkan ke Photobooth.';
        $notes = (string) ($domainRequest['notes'] ?? '');
        if ($status === 'checking' && ($notes === '' || str_contains($notes, 'Custom domain Photobooth Rp250.000/tahun'))) {
            $notes = $defaultCheckingNote;
        }
        $paymentInstruction = 'Transfer add-on custom domain Photobooth sebesar Rp250.000/tahun. Setelah transfer, upload bukti pembayaran di sini agar admin dapat mengonfirmasi dan menyiapkan aktivasi domain.';
        $canUploadPaymentProof = ! empty($domainRequest['domain'])
            && in_array($status, ['available', 'waiting_payment'], true)
            && in_array($paymentStatus, ['unpaid', 'expired', 'refunded'], true);
        $domainRequestId = (int) ($domainRequest['id'] ?? 0);
        $paymentOrder = $domainRequestId > 0 ? $this->latestPhotoboothDomainPaymentOrder($domainRequestId) : null;
        $paymentOrderPayload = null;
        if ($paymentOrder !== null) {
            $paymentOrderId = (int) ($paymentOrder['id'] ?? 0);
            $orderStatus = (string) ($paymentOrder['status'] ?? '');
            if ($paymentStatus === 'paid' && in_array($orderStatus, ['pending', 'pending_payment', 'waiting_approval', 'rejected'], true)) {
                $orderStatus = 'paid';
            }
            $paymentOrderPayload = [
                'id' => $paymentOrderId,
                'invoice_number' => (string) ($paymentOrder['invoice_number'] ?? ''),
                'status' => $orderStatus,
                'status_label' => $this->photoboothDomainOrderStatusLabel($orderStatus),
                'payment_method' => (string) ($paymentOrder['payment_method'] ?? ''),
                'amount' => (int) ($paymentOrder['amount'] ?? 0),
                'detail_url' => $paymentOrderId > 0 ? site_url('photobooth-domain-orders/' . $paymentOrderId) : '',
                'midtrans_redirect_url' => (string) ($paymentOrder['midtrans_redirect_url'] ?? ''),
                'lynk_payment_url' => (string) ($paymentOrder['lynk_payment_url'] ?? ''),
            ];
        }
        $checkoutUrl = $canUploadPaymentProof && $domainRequestId > 0
            ? site_url('photobooth-domain-orders/' . $domainRequestId . '/checkout')
            : '';
        if ($paymentOrderPayload !== null && in_array((string) ($paymentOrderPayload['status'] ?? ''), ['pending', 'pending_payment', 'waiting_approval'], true)) {
            $checkoutUrl = (string) ($paymentOrderPayload['detail_url'] ?? $checkoutUrl);
        }

        return [
            'success' => true,
            'message' => $message,
            'title' => (string) ($page['title'] ?? 'Undangan'),
            'standard_url' => $standardUrl,
            'domain_id' => $domainRequestId,
            'domain' => (string) ($domainRequest['domain'] ?? ''),
            'mode' => ! empty($domainRequest['domain']) && $status !== 'standard' ? 'custom' : 'adaacara',
            'status' => $status,
            'status_label' => $statusLabel,
            'availability_status' => $availability,
            'payment_status' => $paymentStatus,
            'price' => (int) ($domainRequest['price'] ?? 250000),
            'billing_period' => (string) ($domainRequest['billing_period'] ?? 'yearly'),
            'active_until' => (string) ($domainRequest['active_until'] ?? ''),
            'notes' => $notes,
            'payment_instruction' => $paymentInstruction,
            'can_upload_payment_proof' => $canUploadPaymentProof,
            'payment_checkout_url' => $checkoutUrl,
            'payment_order' => $paymentOrderPayload,
            'payment_proof' => (string) ($domainRequest['payment_proof'] ?? ''),
            'payment_note' => (string) ($domainRequest['payment_note'] ?? ''),
            'payment_submitted_at' => (string) ($domainRequest['payment_submitted_at'] ?? ''),
            'paid_at' => (string) ($domainRequest['paid_at'] ?? ''),
            'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
        ];
    }

    private function latestPhotoboothDomainPaymentOrder(int $domainRequestId): ?array
    {
        try {
            $model = new PhotoboothCustomDomainOrderModel();
            if (! $model->tableReady()) {
                return null;
            }

            return $model->where('photobooth_custom_domain_id', $domainRequestId)
                ->where('user_id', (int) session()->get('userId'))
                ->orderBy('id', 'DESC')
                ->first();
        } catch (\Throwable $exception) {
            log_message('warning', 'Photobooth domain payment order status skipped: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function photoboothDomainOrderStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Invoice dibuat',
            'pending_payment' => 'Menunggu pembayaran',
            'waiting_approval' => 'Menunggu konfirmasi admin',
            'paid' => 'Pembayaran terkonfirmasi',
            'rejected' => 'Bukti pembayaran ditolak',
            'failed' => 'Pembayaran gagal',
            'expired' => 'Invoice kedaluwarsa',
            'refunded' => 'Pembayaran direfund',
            default => 'Status invoice belum diketahui',
        };
    }

    private function dashboardSmartNotifications(
        array $landingPages,
        ?array $subscription,
        int $publishedCount,
        int $draftCount,
        int $expiredCount,
        int $totalGuestbookCount,
        int $pageLimit,
        array $creatorStatus
    ): array {
        $items = [];
        $now = date('Y-m-d H:i:s');

        if ($expiredCount > 0) {
            $items[] = $this->dashboardNotificationItem(
                'page_expired',
                'Ada undangan free yang expired',
                $expiredCount . ' undangan perlu dicek sebelum link dibagikan lagi.',
                site_url('dashboard'),
                $now
            );
        }

        if ($draftCount > 0) {
            $items[] = $this->dashboardNotificationItem(
                'draft_reminder',
                'Draft belum dipublish',
                $draftCount . ' undangan masih draft. Lanjutkan edit lalu publish saat sudah siap.',
                site_url('dashboard'),
                $now
            );
        }

        if ($totalGuestbookCount > 0) {
            $items[] = $this->dashboardNotificationItem(
                'guestbook_summary',
                'Guestbook sudah mulai masuk',
                $totalGuestbookCount . ' ucapan atau RSVP tercatat di undangan kamu.',
                site_url('dashboard'),
                $now
            );
        }

        if ($subscription === null && $publishedCount > 0 && $pageLimit > 0 && $publishedCount >= $pageLimit) {
            $items[] = $this->dashboardNotificationItem(
                'publish_limit',
                'Batas publish free terpakai',
                'Upgrade paket jika ingin publish lebih banyak link undangan.',
                site_url('plans'),
                $now
            );
        }

        if ($subscription !== null && ! $this->isLifetimeSubscription($subscription) && ! empty($subscription['expired_at'])) {
            $expiresAt = strtotime((string) $subscription['expired_at']);
            if ($expiresAt !== false && $expiresAt <= strtotime('+7 days') && $expiresAt >= time()) {
                $items[] = $this->dashboardNotificationItem(
                    'subscription_expiring',
                    'Paket aktif hampir expired',
                    'Paket kamu aktif sampai ' . date('d M Y', $expiresAt) . '.',
                    site_url('plans'),
                    $now
                );
            }
        }

        $creatorFlowStatus = (string) ($creatorStatus['status'] ?? 'none');
        if ($creatorFlowStatus === 'pending') {
            $items[] = $this->dashboardNotificationItem(
                'creator_pending',
                'Pengajuan creator sedang direview',
                'Admin sedang memeriksa pengajuan creator kamu.',
                site_url('creator/dashboard'),
                $now
            );
        }

        return $items;
    }

    private function isLifetimeSubscription(array $subscription): bool
    {
        if (((int) ($subscription['is_lifetime'] ?? 0)) === 1) {
            return true;
        }

        $expiredAt = strtotime((string) ($subscription['expired_at'] ?? ''));
        if ($expiredAt === false) {
            return false;
        }

        return $expiredAt >= strtotime('9999-01-01 00:00:00');
    }

    private function dashboardNotificationItem(string $type, string $title, string $message, string $url, string $createdAt): array
    {
        return [
            'id' => 0,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => json_encode(['url' => $url], JSON_UNESCAPED_SLASHES),
            'read_at' => null,
            'created_at' => $createdAt,
        ];
    }

    public function guestbook(int $id): string
    {
        $page = $this->findOwnedPage($id);
        $db = Database::connect();
        $guestbookEntries = [];

        if ($db->tableExists('guest_books')) {
            $builder = $db->table('guest_books')
                ->where('landing_page_id', (int) $page['id']);

            $fields = $db->getFieldNames('guest_books');
            if (in_array('created_at', $fields, true)) {
                $builder->orderBy('created_at', 'DESC');
            }

            $guestbookEntries = $builder
                ->get()
                ->getResultArray();

            if (in_array('read_at', $fields, true)) {
                $db->table('guest_books')
                    ->where('landing_page_id', (int) $page['id'])
                    ->where('read_at', null)
                    ->update([
                        'read_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        }

        return view('dashboard/guestbook', [
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
            'page' => $page,
            'guestbookEntries' => $guestbookEntries,
            'attendanceSummary' => $this->attendanceSummary($guestbookEntries),
        ]);
    }

    public function shareWhatsapp(int $id): RedirectResponse
    {
        $page = $this->findOwnedPage($id);

        return redirect()->to(site_url('share-whatsapp?page_id=' . (int) $page['id']));
    }

    private function canUseGuestMemories(int $ownerUserId): bool
    {
        if ($ownerUserId <= 0) {
            return false;
        }

        try {
            $db = Database::connect();
            if (! $db->tableExists('guest_memory_user_settings')) {
                return false;
            }

            $setting = $db->table('guest_memory_user_settings')
                ->select('is_enabled')
                ->where('user_id', $ownerUserId)
                ->get(1)
                ->getRowArray();

            return ((int) ($setting['is_enabled'] ?? 0)) === 1
                || (new ProductEntitlementService())->hasActive($ownerUserId, ProductEntitlementService::PHOTOBOOTH_STANDALONE);
        } catch (\Throwable) {
            return false;
        }
    }

    public function deletePage(int $id): RedirectResponse
    {
        $page = $this->findOwnedPage($id);
        $db = Database::connect();

        if ($db->tableExists('guest_books')) {
            $db->table('guest_books')
                ->where('landing_page_id', (int) $page['id'])
                ->delete();
        }

        if ($db->tableExists('guestbook_access_links')) {
            $db->table('guestbook_access_links')
                ->where('landing_page_id', (int) $page['id'])
                ->delete();
        }

        if ($db->tableExists('photobooth_custom_domains')) {
            $db->table('photobooth_custom_domains')
                ->where('landing_page_id', (int) $page['id'])
                ->delete();
        }

        $db->table('landing_pages')
            ->where('id', (int) $page['id'])
            ->where('user_id', (int) session()->get('userId'))
            ->delete();

        $this->releaseFreePublishPage((int) session()->get('userId'), (int) $page['id']);

        $this->deleteGuestbookCache((string) ($page['slug'] ?? ''));

        return redirect()
            ->to(site_url('dashboard'))
            ->with('success', 'Undangan berhasil dihapus.');
    }

    private function maxPublishedLinksFromSubscription(array $subscription): int
    {
        if (((int) ($subscription['is_unlimited_pages'] ?? 0)) === 1) {
            return PHP_INT_MAX;
        }

        $configuredLimit = (int) ($subscription['max_pages'] ?? 0);
        if ($configuredLimit > 0) {
            return $configuredLimit;
        }

        $planKey = strtolower((string) ($subscription['plan_slug'] ?? $subscription['plan_name'] ?? ''));

        return match ($planKey) {
            'basic', 'starter' => 1,
            'premium' => 3,
            'business', 'busseniss' => 10,
            default => max(0, (int) ($subscription['max_pages'] ?? 0)),
        };
    }

    private function freePublishedExpiresAt(string $publishedAt, ?array $freeEntitlement = null): ?string
    {
        if ($freeEntitlement !== null && ! empty($freeEntitlement['expires_at'])) {
            return (string) $freeEntitlement['expires_at'];
        }

        $timestamp = strtotime($publishedAt);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime('+1 month', $timestamp));
    }

    private function freePublishEntitlement(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $db = Database::connect();
        if (! $db->tableExists('free_publish_entitlements')) {
            return null;
        }

        return (new FreePublishEntitlementModel())->where('user_id', $userId)->first();
    }

    private function releaseFreePublishPage(int $userId, int $landingPageId): void
    {
        if ($userId <= 0 || $landingPageId <= 0) {
            return;
        }

        $db = Database::connect();
        if (! $db->tableExists('free_publish_entitlements')) {
            return;
        }

        $entitlement = (new FreePublishEntitlementModel())
            ->where('user_id', $userId)
            ->where('landing_page_id', $landingPageId)
            ->first();

        if ($entitlement === null) {
            return;
        }

        (new FreePublishEntitlementModel())->update((int) $entitlement['id'], [
            'landing_page_id' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function guestbookCounts(array $landingPages): array
    {
        $pageIds = array_values(array_unique(array_filter(array_map(
            static fn (array $page): int => (int) ($page['id'] ?? 0),
            $landingPages
        ))));

        if ($pageIds === []) {
            return [];
        }

        $db = Database::connect();
        if (! $db->tableExists('guest_books')) {
            return [];
        }

        $rows = $db->table('guest_books')
            ->select('landing_page_id, COUNT(*) AS total')
            ->whereIn('landing_page_id', $pageIds)
            ->groupBy('landing_page_id')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) ($row['landing_page_id'] ?? 0)] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    private function guestbookUnreadCounts(array $landingPages): array
    {
        $pageIds = array_values(array_unique(array_filter(array_map(
            static fn (array $page): int => (int) ($page['id'] ?? 0),
            $landingPages
        ))));

        if ($pageIds === []) {
            return [];
        }

        $db = Database::connect();
        if (! $db->tableExists('guest_books')) {
            return [];
        }

        $fields = $db->getFieldNames('guest_books');
        if (! in_array('read_at', $fields, true)) {
            return [];
        }

        $rows = $db->table('guest_books')
            ->select('landing_page_id, COUNT(*) AS total')
            ->whereIn('landing_page_id', $pageIds)
            ->where('read_at', null)
            ->groupBy('landing_page_id')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) ($row['landing_page_id'] ?? 0)] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    private function attendanceSummary(array $guestbookEntries): array
    {
        $summary = [
            'hadir' => 0,
            'tidak_hadir' => 0,
            'ragu' => 0,
        ];
        $aliases = [
            'attending' => 'hadir',
            'present' => 'hadir',
            'yes' => 'hadir',
            'not_attending' => 'tidak_hadir',
            'not-attending' => 'tidak_hadir',
            'absent' => 'tidak_hadir',
            'no' => 'tidak_hadir',
            'pending' => 'ragu',
            'maybe' => 'ragu',
            'unknown' => 'ragu',
        ];

        foreach ($guestbookEntries as $entry) {
            $attendance = strtolower(trim((string) ($entry['attendance'] ?? $entry['attendance_status'] ?? 'ragu')));
            $attendance = $aliases[$attendance] ?? $attendance;

            if (! array_key_exists($attendance, $summary)) {
                $attendance = 'ragu';
            }

            $summary[$attendance]++;
        }

        return $summary;
    }

    private function freeTemplateIds(array $landingPages): array
    {
        $templateIds = array_values(array_unique(array_filter(array_map(
            static fn (array $page): int => (int) ($page['template_id'] ?? 0),
            $landingPages
        ))));

        if ($templateIds === []) {
            return [];
        }

        $db = Database::connect();
        if (! $db->tableExists('templates') || ! in_array('is_premium', $db->getFieldNames('templates'), true)) {
            return [];
        }

        return array_map(
            static fn (array $row): int => (int) $row['id'],
            $db->table('templates')
                ->select('id')
                ->whereIn('id', $templateIds)
                ->where('is_premium', 0)
                ->get()
                ->getResultArray()
        );
    }

    private function findOwnedPage(int $id): array
    {
        $page = (new LandingPageModel())
            ->where('id', $id)
            ->where('user_id', (int) session()->get('userId'))
            ->first();

        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Undangan tidak ditemukan.');
        }

        return $page;
    }

    private function deleteGuestbookCache(string $slug): void
    {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return;
        }

        $path = WRITEPATH . 'comments' . DIRECTORY_SEPARATOR . $slug . '.json';
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
