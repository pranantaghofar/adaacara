<?php

namespace App\Controllers;

use App\Libraries\CreatorRoyaltyService;
use App\Libraries\ProductEntitlementService;
use App\Models\GuestBookModel;
use App\Models\CreatorApplicationModel;
use App\Models\CreatorProfileModel;
use App\Models\LandingPageModel;
use App\Models\OrderModel;
use App\Models\PaymentSettingModel;
use App\Models\PlanModel;
use App\Models\UserModel;
use App\Models\UserSubscriptionModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class AdminController extends BaseController
{
    private BaseConnection $db;

    public function __construct()
    {
        helper('admin_permission');
        $this->db = db_connect();
    }

    public function dashboard(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.dashboard.view', 'dashboard')) {
            return $deny;
        }

        $badges = $this->adminNotificationBadges();

        return view('admin/dashboard', [
            'totalUsers' => (new UserModel())->countAllResults(),
            'totalPages' => (new LandingPageModel())->countAllResults(),
            'totalOrders' => (new OrderModel())->countAllResults(),
            'totalGuestbooks' => in_array('guest_books', $this->db->listTables(), true)
                ? (new GuestBookModel())->countAllResults()
                : 0,
            'latestOrders' => array_slice((new OrderModel())->getAdminOrders(), 0, 5),
            'adminBadges' => $badges,
        ]);
    }

    public function creatorRoyalties(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.withdraw.view', 'withdraw')) {
            return $deny;
        }

        $filters = [
            'q' => trim((string) $this->request->getGet('q')),
            'status' => trim((string) $this->request->getGet('status')),
            'event_type' => trim((string) $this->request->getGet('event_type')),
        ];
        $service = new CreatorRoyaltyService();
        $royalties = $service->adminRoyalties($filters, 200);
        $events = $service->adminEvents($filters, 200);
        $summary = [
            'total' => count($royalties),
            'pending' => 0,
            'available' => 0,
            'reversed' => 0,
            'cancelled' => 0,
            'license_value' => 0,
            'creator_amount' => 0,
            'platform_amount' => 0,
        ];

        foreach ($royalties as $row) {
            $status = (string) ($row['status'] ?? '');
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
            $summary['license_value'] += (int) ($row['license_value'] ?? 0);
            $summary['creator_amount'] += (int) ($row['creator_amount'] ?? 0);
            $summary['platform_amount'] += (int) ($row['platform_amount'] ?? 0);
        }

        return view('admin/creator_royalties/index', [
            'royaltyReady' => $service->tableReady(),
            'royalties' => $royalties,
            'events' => $events,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }

    public function accessDenied(): string
    {
        $messages = admin_feature_messages();
        $feature = strtolower(trim((string) ($this->request->getGet('feature') ?? 'dashboard')));
        $feature = preg_replace('/[^a-z0-9_-]+/i', '', $feature) ?: 'dashboard';
        [$title, $message] = $messages[$feature] ?? ['Akses Terbatas', 'Anda tidak memiliki izin untuk membuka fitur admin ini.'];

        return view('admin/access_denied', [
            'feature' => $feature,
            'title' => $title,
            'message' => $message,
        ]);
    }

    public function orders(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.orders.view', 'orders')) {
            return $deny;
        }

        $planModel = new PlanModel();
        $planModel->ensureCreatorPlan();
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $method = trim((string) ($this->request->getGet('method') ?? ''));
        $plan = trim((string) ($this->request->getGet('plan') ?? ''));
        $search = trim((string) ($this->request->getGet('q') ?? ''));

        return view('admin/orders', [
            'orders' => (new OrderModel())->getAdminOrders([
                'status' => $status,
                'method' => $method,
                'plan' => $plan,
                'q' => $search,
            ]),
            'plans' => $planModel->orderBy('price', 'ASC')->findAll(),
            'paymentSettings' => (new PaymentSettingModel())->getSettings(),
            'plansHaveLynkPaymentUrl' => $this->db->fieldExists('lynk_payment_url', 'plans'),
            'plansHaveCompareAtPrice' => $this->db->fieldExists('compare_at_price', 'plans'),
            'plansHaveLifetime' => $this->db->fieldExists('is_lifetime', 'plans'),
            'plansHaveProductType' => $this->db->fieldExists('product_type', 'plans'),
            'orderFilters' => [
                'status' => $status,
                'method' => $method,
                'plan' => $plan,
                'q' => $search,
            ],
        ]);
    }

    public function paymentSettings(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.payment_keys.manage', 'settings')) {
            return $deny;
        }

        return view('admin/payment_settings', [
            'settings' => (new PaymentSettingModel())->getSettings(),
        ]);
    }

    public function updatePaymentSettings(): RedirectResponse
    {
        if (! admin_can('admin.payment_keys.manage')) {
            return redirect()->to(admin_access_denied_url('settings'))->with('error', 'Akses terbatas.');
        }

        $password = (string) $this->request->getPost('admin_password');
        $admin = (new UserModel())->find((int) session()->get('userId'));
        if ($admin === null || ! password_verify($password, (string) ($admin['password_hash'] ?? ''))) {
            return redirect()->back()->withInput()->with('error', 'Password login admin tidak valid.');
        }

        $mode = (string) $this->request->getPost('payment_mode');
        $environment = (string) $this->request->getPost('midtrans_environment');
        $clientKey = trim((string) $this->request->getPost('midtrans_client_key'));
        $serverKey = trim((string) $this->request->getPost('midtrans_server_key'));
        $lynkPaymentUrl = trim((string) $this->request->getPost('lynk_payment_url'));
        $lynkMerchantKey = trim((string) $this->request->getPost('lynk_merchant_key'));

        if (! in_array($mode, ['manual', 'midtrans', 'lynk', 'both', 'manual_lynk', 'midtrans_lynk', 'all'], true)) {
            return redirect()->back()->withInput()->with('error', 'Mode pembayaran tidak valid.');
        }

        if (! in_array($environment, ['production', 'sandbox'], true)) {
            return redirect()->back()->withInput()->with('error', 'Environment Midtrans tidak valid.');
        }

        if (in_array($mode, ['midtrans', 'both', 'midtrans_lynk', 'all'], true) && $serverKey === '') {
            return redirect()->back()->withInput()->with('error', 'Server Key Midtrans wajib diisi jika Midtrans aktif.');
        }

        if (in_array($mode, ['lynk', 'manual_lynk', 'midtrans_lynk', 'all'], true)) {
            if ($lynkPaymentUrl !== '' && filter_var($lynkPaymentUrl, FILTER_VALIDATE_URL) === false) {
                return redirect()->back()->withInput()->with('error', 'Link pembayaran Lynk global wajib berupa URL valid.');
            }

            if ($lynkPaymentUrl === '' && ! $this->plansHaveValidLynkPaymentUrl()) {
                return redirect()->back()->withInput()->with('error', 'Isi link pembayaran Lynk global atau link checkout Lynk pada minimal satu paket jika Lynk aktif.');
            }

            if ($lynkMerchantKey === '') {
                return redirect()->back()->withInput()->with('error', 'Merchant Key Lynk wajib diisi jika Lynk aktif.');
            }
        }

        (new PaymentSettingModel())->saveSettings([
            'payment_mode' => $mode,
            'midtrans_is_production' => $environment === 'production' ? '1' : '0',
            'midtrans_client_key' => $clientKey,
            'midtrans_server_key' => $serverKey,
            'lynk_payment_url' => $lynkPaymentUrl,
            'lynk_merchant_key' => $lynkMerchantKey,
        ], (int) session()->get('userId'));

        log_message('warning', 'Admin payment settings updated. admin_id={admin_id} role={role} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'role' => current_admin_role(),
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/admin/payment-settings')->with('success', 'Pengaturan pembayaran berhasil disimpan.');
    }

    public function updatePlan(int $id): RedirectResponse
    {
        if (! admin_can('admin.orders.manage')) {
            return redirect()->to(admin_access_denied_url('orders'))->with('error', 'Akses terbatas.');
        }

        $planModel = new PlanModel();
        $plan = $planModel->find($id);

        if ($plan === null) {
            throw PageNotFoundException::forPageNotFound('Paket tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[120]',
            'price' => 'required|is_natural',
            'compare_at_price' => 'permit_empty|is_natural',
            'description' => 'permit_empty|max_length[1000]',
            'max_pages' => 'required|is_natural',
            'active_days' => 'required|is_natural_no_zero',
            'status' => 'required|in_list[active,inactive]',
            'lynk_payment_url' => 'permit_empty|valid_url_strict|max_length[700]',
        ];
        if ($this->db->fieldExists('product_type', 'plans')) {
            $rules['product_type'] = 'required|in_list[membership,business_profile,photobooth_standalone,photographer_gallery,creator]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $isUnlimitedPages = $this->request->getPost('is_unlimited_pages') === '1' ? 1 : 0;
        $hasUnlimitedPagesColumn = $this->db->fieldExists('is_unlimited_pages', 'plans');
        $hasLifetimeColumn = $this->db->fieldExists('is_lifetime', 'plans');
        $isLifetime = (
            $hasLifetimeColumn
            && $this->isLifetimeEligiblePlan($plan)
            && $this->request->getPost('is_lifetime') === '1'
        ) ? 1 : 0;

        if ($isUnlimitedPages === 1 && ! $hasUnlimitedPagesColumn) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kolom unlimited halaman belum tersedia. Jalankan database/alter_plans_unlimited_pages.sql terlebih dahulu.');
        }

        $payload = [
            'name' => trim((string) $this->request->getPost('name')),
            'price' => (int) $this->request->getPost('price'),
            'description' => trim((string) $this->request->getPost('description')),
            'max_pages' => (int) $this->request->getPost('max_pages'),
            'active_days' => (int) $this->request->getPost('active_days'),
            'remove_branding' => $this->request->getPost('remove_branding') === '1' ? 1 : 0,
            'custom_domain' => $this->request->getPost('custom_domain') === '1' ? 1 : 0,
            'status' => (string) $this->request->getPost('status'),
        ];

        if ($hasUnlimitedPagesColumn) {
            $payload['is_unlimited_pages'] = $isUnlimitedPages;
        }

        if ($hasLifetimeColumn) {
            $payload['is_lifetime'] = $isLifetime;
        }

        if ($this->db->fieldExists('compare_at_price', 'plans')) {
            $payload['compare_at_price'] = (int) ($this->request->getPost('compare_at_price') ?: 0);
        }

        if ($this->db->fieldExists('lynk_payment_url', 'plans')) {
            $payload['lynk_payment_url'] = trim((string) $this->request->getPost('lynk_payment_url'));
        }

        if ($this->db->fieldExists('product_type', 'plans')) {
            $payload['product_type'] = (string) $this->request->getPost('product_type');
        }

        $planModel->update($id, $payload);

        return redirect()->to('/admin/orders')->with('success', 'Paket berhasil diperbarui.');
    }

    private function plansHaveValidLynkPaymentUrl(): bool
    {
        if (! $this->db->tableExists('plans') || ! $this->db->fieldExists('lynk_payment_url', 'plans')) {
            return false;
        }

        $rows = $this->db->table('plans')
            ->select('lynk_payment_url')
            ->where('lynk_payment_url IS NOT NULL', null, false)
            ->where('lynk_payment_url !=', '')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $url = trim((string) ($row['lynk_payment_url'] ?? ''));
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                return true;
            }
        }

        return false;
    }

    public function togglePlan(int $id): ResponseInterface
    {
        if (! admin_can('admin.orders.manage')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Akses terbatas.']);
        }

        $planModel = new PlanModel();
        $plan = $planModel->find($id);

        if ($plan === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Paket tidak ditemukan.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['active', 'inactive'], true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Status paket tidak valid.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $planModel->update($id, ['status' => $status]);

        return $this->response->setJSON([
            'success' => true,
            'status' => $status,
            'message' => $status === 'active' ? 'Paket diaktifkan.' : 'Paket dinonaktifkan.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function approveOrder(int $id): RedirectResponse
    {
        if (! admin_can('admin.orders.approve')) {
            return redirect()->to(admin_access_denied_url('orders'))->with('error', 'Akses terbatas.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findAdminOrder($id);

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Order tidak ditemukan.');
        }

        if ($order['status'] !== 'waiting_approval') {
            return redirect()->back()->with('error', 'Hanya order waiting_approval yang bisa diapprove.');
        }

        $now = date('Y-m-d H:i:s');
        $expiredAt = $this->subscriptionExpiredAtForPlan($order, $now);
        $productEntitlements = new ProductEntitlementService();
        $isProductPlan = $productEntitlements->isProductPlan($order);

        if ($isProductPlan && ! $productEntitlements->tableReady()) {
            return redirect()->back()->with('error', 'Tabel product_entitlements belum tersedia. Jalankan database/alter_product_entitlements.sql dahulu.');
        }

        $this->db->transStart();

        $orderModel->update((int) $order['id'], [
            'status' => 'paid',
            'paid_at' => $now,
            'admin_note' => null,
        ]);

        if ($this->isCreatorPlan($order)) {
            $this->activateCreatorFromOrder($order, $now);
        } elseif ($isProductPlan) {
            $productEntitlements->activateFromPaidOrder(array_merge($order, ['status' => 'paid']), $now);
        } else {
            (new UserSubscriptionModel())->insert([
                'user_id' => (int) $order['user_id'],
                'plan_id' => (int) $order['plan_id'],
                'order_id' => (int) $order['id'],
                'started_at' => $now,
                'expired_at' => $expiredAt,
                'status' => 'active',
                'created_at' => $now,
            ]);
        }

        $this->confirmCreatorRoyaltiesForOrder((int) $order['id']);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return redirect()->back()->with('error', 'Approval gagal diproses.');
        }

        return redirect()->to('/admin/orders')->with('success', $this->isCreatorPlan($order)
            ? 'Order creator diapprove dan creator aktif permanen.'
            : ($isProductPlan ? 'Order berhasil diapprove dan akses produk aktif.' : 'Order berhasil diapprove dan subscription aktif.'));
    }

    private function isCreatorPlan(array $order): bool
    {
        helper('url');

        $keys = [
            (string) ($order['plan_slug'] ?? ''),
            (string) ($order['plan_name'] ?? ''),
        ];

        foreach ($keys as $key) {
            if (url_title(strtolower(trim($key)), '-', true) === 'creator') {
                return true;
            }
        }

        return false;
    }

    private function isLifetimeEligiblePlan(array $plan): bool
    {
        helper('url');

        $keys = [
            (string) ($plan['slug'] ?? ''),
            (string) ($plan['name'] ?? ''),
            (string) ($plan['plan_slug'] ?? ''),
            (string) ($plan['plan_name'] ?? ''),
        ];

        foreach ($keys as $key) {
            if (in_array(url_title(strtolower(trim($key)), '-', true), ['business', 'busseniss', 'buat-niat-jualan'], true)) {
                return true;
            }
        }

        return false;
    }

    private function isLifetimePlan(array $plan): bool
    {
        return $this->isLifetimeEligiblePlan($plan) && ((int) ($plan['is_lifetime'] ?? 0)) === 1;
    }

    private function subscriptionExpiredAtForPlan(array $plan, string $startedAt): string
    {
        if ($this->isLifetimePlan($plan)) {
            return '9999-12-31 23:59:59';
        }

        $activeDays = max(1, (int) ($plan['active_days'] ?? 30));
        $baseTimestamp = strtotime($startedAt);
        if ($baseTimestamp === false) {
            $baseTimestamp = time();
        }

        return date('Y-m-d H:i:s', strtotime('+' . $activeDays . ' days', $baseTimestamp));
    }

    private function activateCreatorFromOrder(array $order, string $approvedAt): void
    {
        $userId = (int) ($order['user_id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        $userModel = new UserModel();
        $profileModel = new CreatorProfileModel();
        $user = $userModel->find($userId);
        $displayName = trim((string) ($user['name'] ?? 'Creator'));
        $reviewedBy = (int) session()->get('userId');

        $profile = $profileModel->where('user_id', $userId)->first();
        if ($profile === null) {
            $profileModel->insert([
                'user_id' => $userId,
                'display_name' => $displayName,
                'slug' => $this->uniqueCreatorSlug($displayName),
                'bio' => 'Creator AdaAcara',
                'portfolio_url' => null,
                'social_links' => null,
                'status' => 'active',
                'approved_application_id' => null,
            ]);
        } else {
            $profileModel->update((int) $profile['id'], [
                'status' => 'active',
            ]);
        }

        $currentRole = strtolower(trim((string) ($user['role'] ?? 'user')));
        if (! in_array($currentRole, array_merge(admin_roles(), ['creator']), true)) {
            $userModel->update($userId, ['role' => 'creator']);
        }

        if ($this->db->tableExists('creator_applications')) {
            $application = (new CreatorApplicationModel())
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->first();

            if ($application !== null && ($application['status'] ?? '') === 'pending') {
                (new CreatorApplicationModel())->update((int) $application['id'], [
                    'status' => 'approved',
                    'reason' => null,
                    'reviewed_by' => $reviewedBy,
                    'reviewed_at' => $approvedAt,
                ]);
            }
        }
    }

    private function adminNotificationBadges(): array
    {
        $badges = [
            'orders' => (new OrderModel())->where('status', 'waiting_approval')->countAllResults(),
            'users' => (new UserModel())->where('created_at >=', date('Y-m-d 00:00:00'))->countAllResults(),
            'pages' => (new LandingPageModel())->where('created_at >=', date('Y-m-d 00:00:00'))->countAllResults(),
            'guestbooks' => 0,
            'templates' => 0,
            'sellerTemplates' => 0,
            'creatorApplications' => 0,
            'withdraws' => 0,
        ];

        if ($this->db->tableExists('guest_books')) {
            $builder = $this->db->table('guest_books');
            if (in_array('is_approved', $this->db->getFieldNames('guest_books'), true)) {
                $builder->where('is_approved', 0);
            }
            $badges['guestbooks'] = $builder->countAllResults();
        }

        if ($this->db->tableExists('templates') && in_array('review_status', $this->db->getFieldNames('templates'), true)) {
            $badges['sellerTemplates'] = $this->db->table('templates')
                ->where('review_status', 'pending')
                ->countAllResults();
        }

        if ($this->db->tableExists('seller_withdraw_requests')) {
            $badges['withdraws'] = $this->db->table('seller_withdraw_requests')
                ->where('status', 'pending')
                ->countAllResults();
        }

        if ($this->db->tableExists('creator_applications')) {
            $badges['creatorApplications'] = $this->db->table('creator_applications')
                ->where('status', 'pending')
                ->countAllResults();
        }

        return $badges;
    }

    public function rejectOrder(int $id): RedirectResponse
    {
        if (! admin_can('admin.orders.manage')) {
            return redirect()->to(admin_access_denied_url('orders'))->with('error', 'Akses terbatas.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findAdminOrder($id);

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Order tidak ditemukan.');
        }

        if ($order['status'] !== 'waiting_approval') {
            return redirect()->back()->with('error', 'Hanya order waiting_approval yang bisa direject.');
        }

        $orderModel->update((int) $order['id'], [
            'status' => 'rejected',
            'admin_note' => 'Pembayaran ditolak admin.',
        ]);

        return redirect()->to('/admin/orders')->with('success', 'Order berhasil ditolak.');
    }

    public function updateOrderStatus(int $id): RedirectResponse
    {
        if (! in_array(current_admin_role(), ['superadmin', 'finance_admin'], true)) {
            return redirect()->to(admin_access_denied_url('orders'))->with('error', 'Akses terbatas.');
        }

        $allowedStatuses = ['pending', 'pending_payment', 'waiting_approval', 'paid', 'rejected', 'failed', 'expired'];
        $newStatus = trim((string) $this->request->getPost('status'));
        if (! in_array($newStatus, $allowedStatuses, true)) {
            return redirect()->back()->with('error', 'Status order tidak valid.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findAdminOrder($id);

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Order tidak ditemukan.');
        }

        $oldStatus = (string) ($order['status'] ?? '');
        if ($oldStatus === $newStatus) {
            return redirect()->back()->with('error', 'Status order belum berubah.');
        }

        $productEntitlements = new ProductEntitlementService();
        if ($newStatus === 'paid' && $productEntitlements->isProductPlan($order) && ! $productEntitlements->tableReady()) {
            return redirect()->back()->with('error', 'Tabel product_entitlements belum tersedia. Jalankan database/alter_product_entitlements.sql dahulu.');
        }

        $now = date('Y-m-d H:i:s');
        $adminNote = trim((string) ($order['admin_note'] ?? ''));
        $manualNote = 'Status diubah manual oleh admin dari ' . ($oldStatus !== '' ? $oldStatus : '-') . ' ke ' . $newStatus . ' pada ' . $now . '.';
        $payload = [
            'status' => $newStatus,
            'admin_note' => trim($adminNote . ($adminNote !== '' ? "\n" : '') . $manualNote),
        ];

        if ($newStatus === 'paid' && empty($order['paid_at'])) {
            $payload['paid_at'] = $now;
        }

        $this->db->transStart();
        $orderModel->update((int) $order['id'], $payload);

        if ($newStatus === 'paid') {
            $this->activateOrderAccessIfMissing($order, $now);
            $this->confirmCreatorRoyaltiesForOrder((int) $order['id']);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return redirect()->back()->with('error', 'Status order gagal diperbarui.');
        }

        log_message('warning', 'Admin order status changed manually. admin_id={admin_id} role={role} order_id={order_id} invoice={invoice} from={from} to={to} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'role' => current_admin_role(),
            'order_id' => (string) ($order['id'] ?? '-'),
            'invoice' => (string) ($order['invoice_number'] ?? '-'),
            'from' => $oldStatus !== '' ? $oldStatus : '-',
            'to' => $newStatus,
            'ip' => $this->request->getIPAddress(),
        ]);

        $message = 'Status invoice berhasil diubah.';
        if ($oldStatus === 'paid' && $newStatus !== 'paid') {
            $message .= ' Periksa subscription user secara manual jika perlu.';
        }

        return redirect()->to('/admin/orders')->with('success', $message);
    }

    private function activateOrderAccessIfMissing(array $order, string $approvedAt): void
    {
        $orderId = (int) ($order['id'] ?? 0);
        if ($orderId <= 0) {
            return;
        }

        if ($this->isCreatorPlan($order)) {
            $this->activateCreatorFromOrder($order, $approvedAt);
            return;
        }

        $productEntitlements = new ProductEntitlementService();
        if ($productEntitlements->isProductPlan($order)) {
            $productEntitlements->activateFromPaidOrder(array_merge($order, ['status' => 'paid']), $approvedAt);
            return;
        }

        $subscriptionModel = new UserSubscriptionModel();
        $existingSubscription = $subscriptionModel->where('order_id', $orderId)->first();
        if ($existingSubscription !== null) {
            return;
        }

        $subscriptionModel->insert([
            'user_id' => (int) $order['user_id'],
            'plan_id' => (int) $order['plan_id'],
            'order_id' => $orderId,
            'started_at' => $approvedAt,
            'expired_at' => $this->subscriptionExpiredAtForPlan($order, $approvedAt),
            'status' => 'active',
            'created_at' => $approvedAt,
        ]);
    }

    private function confirmCreatorRoyaltiesForOrder(int $orderId): void
    {
        try {
            (new CreatorRoyaltyService())->confirmRoyaltyForPaidOrder($orderId);
        } catch (\Throwable $error) {
            log_message('warning', 'Creator royalty admin order confirmation skipped. order={order} error={error}', [
                'order' => (string) $orderId,
                'error' => $error->getMessage(),
            ]);
        }
    }

    public function deleteOrder(int $id): RedirectResponse
    {
        if (! admin_can('admin.orders.delete')) {
            return redirect()->to(admin_access_denied_url('orders'))->with('error', 'Akses terbatas.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findAdminOrder($id);

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Order tidak ditemukan.');
        }

        if ((string) ($order['status'] ?? '') === 'paid') {
            return redirect()->back()->with('error', 'Order paid tidak bisa dihapus dari daftar invoice.');
        }

        if ($this->db->tableExists('user_subscriptions')) {
            $subscriptionExists = $this->db->table('user_subscriptions')
                ->where('order_id', (int) $order['id'])
                ->countAllResults() > 0;

            if ($subscriptionExists) {
                return redirect()->back()->with('error', 'Order sudah terkait subscription dan tidak bisa dihapus.');
            }
        }

        $paymentProof = trim((string) ($order['payment_proof'] ?? ''));

        $this->db->transStart();
        $orderModel->delete((int) $order['id']);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return redirect()->back()->with('error', 'Invoice gagal dihapus.');
        }

        if ($paymentProof !== '' && ! str_contains($paymentProof, '..')) {
            $proofPath = FCPATH . ltrim($paymentProof, '/');
            if (is_file($proofPath)) {
                @unlink($proofPath);
            }
        }

        log_message('warning', 'Admin order deleted. admin_id={admin_id} order_id={order_id} invoice={invoice} status={status} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'order_id' => (string) ($order['id'] ?? '-'),
            'invoice' => (string) ($order['invoice_number'] ?? '-'),
            'status' => (string) ($order['status'] ?? '-'),
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/admin/orders')->with('success', 'Invoice berhasil dihapus.');
    }

    public function users(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.users.view', 'users')) {
            return $deny;
        }

        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $role = trim((string) ($this->request->getGet('role') ?? ''));
        $allowedRoles = array_merge(['superadmin'], admin_assignable_roles());
        $roleFilter = in_array($role, $allowedRoles, true) ? $role : '';

        $builder = (new UserModel())->orderBy('created_at', 'DESC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        if ($roleFilter !== '') {
            $builder->where('role', $roleFilter);
        }

        $users = $builder->findAll();

        return view('admin/users', [
            'users' => $users,
            'userSummaries' => $this->adminUser360Summaries($users),
            'guestMemoryReady' => $this->db->tableExists('guest_memory_user_settings'),
            'guestMemorySettings' => $this->guestMemorySettingsForUsers($users),
            'filters' => [
                'q' => $search,
                'role' => $roleFilter,
            ],
        ]);
    }

    public function updateUserRole(int $id): RedirectResponse
    {
        if (! admin_can('admin.users.change_role')) {
            return redirect()->to(admin_access_denied_url('users'))->with('error', 'Akses terbatas.');
        }

        $newRole = strtolower(trim((string) $this->request->getPost('role')));
        if (! in_array($newRole, admin_assignable_roles(), true)) {
            return redirect()->back()->with('error', 'Role target tidak valid.');
        }

        $userModel = new UserModel();
        $target = $userModel->find($id);
        if ($target === null) {
            throw PageNotFoundException::forPageNotFound('User tidak ditemukan.');
        }

        $targetRole = strtolower(trim((string) ($target['role'] ?? 'user'))) ?: 'user';
        if ($targetRole === 'superadmin') {
            return redirect()->back()->with('error', 'Role superadmin tidak bisa diubah dari UI biasa.');
        }

        if ((int) ($target['id'] ?? 0) === (int) session()->get('userId')) {
            return redirect()->back()->with('error', 'Tidak bisa mengubah role akun sendiri dari UI ini.');
        }

        $userModel->update($id, ['role' => $newRole]);

        log_message('warning', 'Admin user role changed. admin_id={admin_id} admin_role={admin_role} target_id={target_id} old_role={old_role} new_role={new_role} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'admin_role' => current_admin_role(),
            'target_id' => (string) $id,
            'old_role' => $targetRole,
            'new_role' => $newRole,
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to(site_url('admin/users'))->with('success', 'Role user berhasil diperbarui.');
    }

    public function toggleUserGuestMemories(int $id): RedirectResponse
    {
        if (! admin_can('admin.users.manage')) {
            return redirect()->to(admin_access_denied_url('users'))->with('error', 'Akses terbatas.');
        }
        if (! $this->db->tableExists('guest_memory_user_settings')) {
            return redirect()->back()->with('error', 'Tabel guest_memory_user_settings belum tersedia. Jalankan SQL Guest Memories dahulu.');
        }

        $userModel = new UserModel();
        $target = $userModel->find($id);
        if ($target === null) {
            throw PageNotFoundException::forPageNotFound('User tidak ditemukan.');
        }

        $isEnabled = (int) ($this->request->getPost('is_enabled') ?? 0) === 1 ? 1 : 0;
        $now = date('Y-m-d H:i:s');
        $existing = $this->db->table('guest_memory_user_settings')
            ->select('id')
            ->where('user_id', $id)
            ->get(1)
            ->getRowArray();

        $payload = [
            'user_id' => $id,
            'is_enabled' => $isEnabled,
            'updated_by' => (int) (session()->get('userId') ?? 0),
            'updated_at' => $now,
        ];

        if ($existing) {
            $this->db->table('guest_memory_user_settings')->where('id', (int) $existing['id'])->update($payload);
        } else {
            $payload['created_at'] = $now;
            $this->db->table('guest_memory_user_settings')->insert($payload);
        }

        log_message('warning', 'Admin toggled Guest Memories. admin_id={admin_id} target_id={target_id} enabled={enabled} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'target_id' => (string) $id,
            'enabled' => (string) $isEnabled,
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to(site_url('admin/users'))->with('success', $isEnabled === 1 ? 'Guest Memories user diaktifkan.' : 'Guest Memories user dinonaktifkan.');
    }

    private function adminUser360Summaries(array $users): array
    {
        $userIds = array_values(array_filter(array_map(
            static fn (array $user): int => (int) ($user['id'] ?? 0),
            $users
        )));

        $summaries = [];
        foreach ($userIds as $userId) {
            $summaries[$userId] = [
                'subscription' => null,
                'pages' => ['total' => 0, 'published' => 0, 'draft' => 0, 'latest' => null],
                'orders' => ['total' => 0, 'paid' => 0, 'pending' => 0, 'failed' => 0, 'amount_paid' => 0, 'latest' => null],
                'creator' => ['status' => 'none', 'display_name' => null, 'slug' => null],
                'seller' => ['status' => 'none', 'templates' => 0, 'approved_templates' => 0, 'pending_templates' => 0, 'wallet_balance' => 0, 'pending_withdraws' => 0],
                'guestbooks' => ['total' => 0],
                'media' => ['total' => 0, 'bytes' => 0],
            ];
        }

        if ($userIds === []) {
            return [];
        }

        $this->attachAdminUserSubscriptionSummaries($summaries, $userIds);
        $this->attachAdminUserPageSummaries($summaries, $userIds);
        $this->attachAdminUserOrderSummaries($summaries, $userIds);
        $this->attachAdminUserCreatorSummaries($summaries, $userIds);
        $this->attachAdminUserSellerSummaries($summaries, $userIds);
        $this->attachAdminUserGuestbookSummaries($summaries, $userIds);
        $this->attachAdminUserMediaSummaries($summaries, $userIds);

        return $summaries;
    }

    private function guestMemorySettingsForUsers(array $users): array
    {
        $userIds = array_values(array_filter(array_map(
            static fn (array $user): int => (int) ($user['id'] ?? 0),
            $users
        )));

        if ($userIds === [] || ! $this->db->tableExists('guest_memory_user_settings')) {
            return [];
        }

        $rows = $this->db->table('guest_memory_user_settings')
            ->select('user_id,is_enabled')
            ->whereIn('user_id', $userIds)
            ->get()
            ->getResultArray();

        $settings = [];
        foreach ($rows as $row) {
            $settings[(int) ($row['user_id'] ?? 0)] = ((int) ($row['is_enabled'] ?? 0)) === 1;
        }

        return $settings;
    }

    private function attachAdminUserSubscriptionSummaries(array &$summaries, array $userIds): void
    {
        if (! $this->db->tableExists('user_subscriptions')) {
            return;
        }

        $builder = $this->db->table('user_subscriptions')
            ->select('user_subscriptions.*')
            ->whereIn('user_subscriptions.user_id', $userIds)
            ->orderBy("CASE WHEN user_subscriptions.status = 'active' THEN 0 ELSE 1 END", 'ASC', false)
            ->orderBy('user_subscriptions.expired_at', 'DESC')
            ->orderBy('user_subscriptions.id', 'DESC');

        if ($this->db->tableExists('plans')) {
            $lifetimeSelect = $this->db->fieldExists('is_lifetime', 'plans')
                ? 'plans.is_lifetime'
                : '0 AS is_lifetime';

            $builder
                ->select('plans.name AS plan_name, plans.slug AS plan_slug, plans.max_pages, plans.active_days, ' . $lifetimeSelect, false)
                ->join('plans', 'plans.id = user_subscriptions.plan_id', 'left');
        }

        foreach ($builder->get()->getResultArray() as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if (! isset($summaries[$userId]) || $summaries[$userId]['subscription'] !== null) {
                continue;
            }

            $summaries[$userId]['subscription'] = $row;
        }
    }

    private function attachAdminUserPageSummaries(array &$summaries, array $userIds): void
    {
        if (! $this->db->tableExists('landing_pages')) {
            return;
        }

        $rows = $this->db->table('landing_pages')
            ->select("user_id, COUNT(*) AS total, SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published, SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft", false)
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if (! isset($summaries[$userId])) {
                continue;
            }

            $summaries[$userId]['pages']['total'] = (int) ($row['total'] ?? 0);
            $summaries[$userId]['pages']['published'] = (int) ($row['published'] ?? 0);
            $summaries[$userId]['pages']['draft'] = (int) ($row['draft'] ?? 0);
        }

        foreach ($this->db->table('landing_pages')
            ->select('id, user_id, title, slug, status, updated_at')
            ->whereIn('user_id', $userIds)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray() as $page) {
            $userId = (int) ($page['user_id'] ?? 0);
            if (isset($summaries[$userId]) && $summaries[$userId]['pages']['latest'] === null) {
                $summaries[$userId]['pages']['latest'] = $page;
            }
        }
    }

    private function attachAdminUserOrderSummaries(array &$summaries, array $userIds): void
    {
        if (! $this->db->tableExists('orders')) {
            return;
        }

        $rows = $this->db->table('orders')
            ->select("user_id, COUNT(*) AS total, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid, SUM(CASE WHEN status IN ('pending_payment', 'waiting_approval') THEN 1 ELSE 0 END) AS pending, SUM(CASE WHEN status IN ('failed', 'rejected') THEN 1 ELSE 0 END) AS failed, SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS amount_paid", false)
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if (! isset($summaries[$userId])) {
                continue;
            }

            $summaries[$userId]['orders']['total'] = (int) ($row['total'] ?? 0);
            $summaries[$userId]['orders']['paid'] = (int) ($row['paid'] ?? 0);
            $summaries[$userId]['orders']['pending'] = (int) ($row['pending'] ?? 0);
            $summaries[$userId]['orders']['failed'] = (int) ($row['failed'] ?? 0);
            $summaries[$userId]['orders']['amount_paid'] = (int) ($row['amount_paid'] ?? 0);
        }

        $builder = $this->db->table('orders')
            ->select('orders.id, orders.user_id, orders.invoice_number, orders.amount, orders.payment_method, orders.status, orders.created_at');

        if ($this->db->tableExists('plans')) {
            $builder
                ->select('plans.name AS plan_name')
                ->join('plans', 'plans.id = orders.plan_id', 'left');
        }

        foreach ($builder
            ->whereIn('orders.user_id', $userIds)
            ->orderBy('orders.created_at', 'DESC')
            ->orderBy('orders.id', 'DESC')
            ->get()
            ->getResultArray() as $order) {
            $userId = (int) ($order['user_id'] ?? 0);
            if (isset($summaries[$userId]) && $summaries[$userId]['orders']['latest'] === null) {
                $summaries[$userId]['orders']['latest'] = $order;
            }
        }
    }

    private function attachAdminUserCreatorSummaries(array &$summaries, array $userIds): void
    {
        if ($this->db->tableExists('creator_profiles')) {
            $profileFields = $this->db->getFieldNames('creator_profiles');
            $profileBuilder = $this->db->table('creator_profiles')
                ->select('user_id, display_name, slug, status')
                ->whereIn('user_id', $userIds);

            if (in_array('updated_at', $profileFields, true)) {
                $profileBuilder->orderBy('updated_at', 'DESC');
            }

            foreach ($profileBuilder
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray() as $profile) {
                $userId = (int) ($profile['user_id'] ?? 0);
                if (! isset($summaries[$userId]) || $summaries[$userId]['creator']['status'] !== 'none') {
                    continue;
                }

                $summaries[$userId]['creator'] = [
                    'status' => (string) ($profile['status'] ?? 'none'),
                    'display_name' => $profile['display_name'] ?? null,
                    'slug' => $profile['slug'] ?? null,
                ];
            }
        }

        if (! $this->db->tableExists('creator_applications')) {
            return;
        }

        $applicationFields = $this->db->getFieldNames('creator_applications');
        $applicationBuilder = $this->db->table('creator_applications')
            ->select('user_id, display_name, status')
            ->whereIn('user_id', $userIds);

        if (in_array('created_at', $applicationFields, true)) {
            $applicationBuilder->orderBy('created_at', 'DESC');
        }

        foreach ($applicationBuilder
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray() as $application) {
            $userId = (int) ($application['user_id'] ?? 0);
            if (! isset($summaries[$userId]) || $summaries[$userId]['creator']['status'] !== 'none') {
                continue;
            }

            $summaries[$userId]['creator'] = [
                'status' => (string) ($application['status'] ?? 'none'),
                'display_name' => $application['display_name'] ?? null,
                'slug' => null,
            ];
        }
    }

    private function attachAdminUserSellerSummaries(array &$summaries, array $userIds): void
    {
        if ($this->db->tableExists('templates')) {
            $templateFields = $this->db->getFieldNames('templates');
            if (in_array('owner_user_id', $templateFields, true)) {
                $templateSelect = 'owner_user_id AS user_id, COUNT(*) AS total';
                $hasReviewStatus = in_array('review_status', $templateFields, true);
                if ($hasReviewStatus) {
                    $templateSelect .= ", SUM(CASE WHEN review_status = 'approved' THEN 1 ELSE 0 END) AS approved, SUM(CASE WHEN review_status = 'pending' THEN 1 ELSE 0 END) AS pending";
                }

                $rows = $this->db->table('templates')
                    ->select($templateSelect, false)
                    ->whereIn('owner_user_id', $userIds)
                    ->groupBy('owner_user_id')
                    ->get()
                    ->getResultArray();

                foreach ($rows as $row) {
                    $userId = (int) ($row['user_id'] ?? 0);
                    if (! isset($summaries[$userId])) {
                        continue;
                    }

                    $summaries[$userId]['seller']['templates'] = (int) ($row['total'] ?? 0);
                    $summaries[$userId]['seller']['approved_templates'] = (int) ($row['approved'] ?? 0);
                    $summaries[$userId]['seller']['pending_templates'] = (int) ($row['pending'] ?? 0);
                    $summaries[$userId]['seller']['status'] = ((int) ($row['total'] ?? 0)) > 0 ? 'active' : 'none';
                }
            }
        }

        if ($this->db->tableExists('seller_wallet_ledger')) {
            foreach ($this->db->table('seller_wallet_ledger')
                ->select("user_id, SUM(CASE WHEN direction = 'credit' THEN amount WHEN direction = 'debit' THEN -amount ELSE 0 END) AS balance", false)
                ->whereIn('user_id', $userIds)
                ->groupBy('user_id')
                ->get()
                ->getResultArray() as $row) {
                $userId = (int) ($row['user_id'] ?? 0);
                if (isset($summaries[$userId])) {
                    $summaries[$userId]['seller']['wallet_balance'] = (int) ($row['balance'] ?? 0);
                    if ((int) ($row['balance'] ?? 0) !== 0) {
                        $summaries[$userId]['seller']['status'] = 'active';
                    }
                }
            }
        }

        if (! $this->db->tableExists('seller_withdraw_requests')) {
            return;
        }

        foreach ($this->db->table('seller_withdraw_requests')
            ->select("user_id, COUNT(*) AS pending", false)
            ->whereIn('user_id', $userIds)
            ->where('status', 'pending')
            ->groupBy('user_id')
            ->get()
            ->getResultArray() as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if (isset($summaries[$userId])) {
                $summaries[$userId]['seller']['pending_withdraws'] = (int) ($row['pending'] ?? 0);
                $summaries[$userId]['seller']['status'] = 'active';
            }
        }
    }

    private function attachAdminUserGuestbookSummaries(array &$summaries, array $userIds): void
    {
        if (! $this->db->tableExists('guest_books') || ! $this->db->tableExists('landing_pages')) {
            return;
        }

        foreach ($this->db->table('guest_books')
            ->select('landing_pages.user_id, COUNT(*) AS total')
            ->join('landing_pages', 'landing_pages.id = guest_books.landing_page_id', 'inner')
            ->whereIn('landing_pages.user_id', $userIds)
            ->groupBy('landing_pages.user_id')
            ->get()
            ->getResultArray() as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if (isset($summaries[$userId])) {
                $summaries[$userId]['guestbooks']['total'] = (int) ($row['total'] ?? 0);
            }
        }
    }

    private function attachAdminUserMediaSummaries(array &$summaries, array $userIds): void
    {
        if (! $this->db->tableExists('media_library')) {
            return;
        }

        $builder = $this->db->table('media_library')
            ->select('user_id, COUNT(*) AS total, SUM(file_size) AS bytes')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id');

        if (in_array('deleted_at', $this->db->getFieldNames('media_library'), true)) {
            $builder->groupStart()
                ->where('deleted_at IS NULL', null, false)
                ->orWhere('deleted_at', '')
                ->groupEnd();
        }

        foreach ($builder->get()->getResultArray() as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if (isset($summaries[$userId])) {
                $summaries[$userId]['media']['total'] = (int) ($row['total'] ?? 0);
                $summaries[$userId]['media']['bytes'] = (int) ($row['bytes'] ?? 0);
            }
        }
    }

    public function creatorApplications(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.review', 'templates')) {
            return $deny;
        }

        $status = (string) $this->request->getGet('status');
        $allowedStatuses = ['pending', 'approved', 'rejected'];
        $statusFilter = in_array($status, $allowedStatuses, true) ? $status : null;
        $search = trim((string) ($this->request->getGet('q') ?? ''));

        return view('admin/creator_applications/index', [
            'applications' => (new CreatorApplicationModel())->adminList($statusFilter, $search),
            'statusFilter' => $statusFilter,
            'search' => $search,
        ]);
    }

    public function creatorApplicationDetail(int $id): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.review', 'templates')) {
            return $deny;
        }

        $application = (new CreatorApplicationModel())->adminFind($id);

        if ($application === null) {
            throw PageNotFoundException::forPageNotFound('Aplikasi creator tidak ditemukan.');
        }

        $profile = (new CreatorProfileModel())->where('user_id', (int) $application['user_id'])->first();

        return view('admin/creator_applications/show', [
            'application' => $application,
            'profile' => $profile,
        ]);
    }

    public function approveCreatorApplication(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.review')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $applicationModel = new CreatorApplicationModel();
        $profileModel = new CreatorProfileModel();
        $userModel = new UserModel();
        $application = $applicationModel->find($id);

        if ($application === null) {
            throw PageNotFoundException::forPageNotFound('Aplikasi creator tidak ditemukan.');
        }

        if (($application['status'] ?? '') !== 'pending') {
            return redirect()->back()->with('error', 'Hanya aplikasi pending yang bisa diapprove.');
        }

        $reviewedAt = date('Y-m-d H:i:s');
        $reviewedBy = (int) session()->get('userId');
        $userId = (int) $application['user_id'];

        $this->db->transStart();

        $profile = $profileModel->where('user_id', $userId)->first();
        if ($profile === null) {
            $profileModel->insert([
                'user_id' => $userId,
                'display_name' => $application['display_name'],
                'slug' => $this->uniqueCreatorSlug((string) $application['display_name']),
                'bio' => $application['bio'],
                'portfolio_url' => $application['portfolio_url'] ?: null,
                'social_links' => $application['social_links'] ?: null,
                'status' => 'active',
                'approved_application_id' => (int) $application['id'],
            ]);
        } else {
            $profileModel->update((int) $profile['id'], [
                'display_name' => $profile['display_name'] ?: $application['display_name'],
                'bio' => $profile['bio'] ?: $application['bio'],
                'portfolio_url' => $profile['portfolio_url'] ?: ($application['portfolio_url'] ?: null),
                'social_links' => $profile['social_links'] ?: ($application['social_links'] ?: null),
                'status' => 'active',
                'approved_application_id' => (int) $application['id'],
            ]);
        }

        $applicationModel->update((int) $application['id'], [
            'status' => 'approved',
            'reason' => null,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => $reviewedAt,
        ]);

        $user = $userModel->find($userId);
        $currentRole = (string) ($user['role'] ?? 'user');
        if (! in_array($currentRole, array_merge(admin_roles(), ['creator']), true)) {
            $userModel->update($userId, ['role' => 'creator']);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return redirect()->back()->with('error', 'Approval creator gagal diproses.');
        }

        return redirect()->to('/admin/creator-applications/' . $id)->with('success', 'Aplikasi creator berhasil diapprove.');
    }

    public function rejectCreatorApplication(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.review')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            return redirect()->back()->withInput()->with('error', 'Alasan rejection wajib diisi.');
        }

        if (strlen($reason) > 1000) {
            return redirect()->back()->withInput()->with('error', 'Alasan rejection maksimal 1000 karakter.');
        }

        $applicationModel = new CreatorApplicationModel();
        $application = $applicationModel->find($id);

        if ($application === null) {
            throw PageNotFoundException::forPageNotFound('Aplikasi creator tidak ditemukan.');
        }

        if (($application['status'] ?? '') !== 'pending') {
            return redirect()->back()->with('error', 'Hanya aplikasi pending yang bisa direject.');
        }

        $this->db->transStart();

        $applicationModel->update((int) $application['id'], [
            'status' => 'rejected',
            'reason' => $reason,
            'reviewed_by' => (int) session()->get('userId'),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return redirect()->back()->with('error', 'Rejection creator gagal diproses.');
        }

        return redirect()->to('/admin/creator-applications/' . $id)->with('success', 'Aplikasi creator berhasil direject.');
    }

    public function pages(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.pages.view', 'pages')) {
            return $deny;
        }

        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $status = trim((string) ($this->request->getGet('status') ?? ''));

        if (! $this->db->tableExists('landing_pages')) {
            return view('admin/pages', [
                'pages' => [],
                'setupError' => 'Tabel landing_pages belum tersedia. Jalankan migrasi/tabel undangan terlebih dahulu.',
                'filters' => ['q' => $search, 'status' => $status],
                'projectTypeReady' => false,
            ]);
        }

        try {
            $landingPageFields = $this->db->getFieldNames('landing_pages');
            $usersTableExists = $this->db->tableExists('users');
            $userFields = $usersTableExists ? $this->db->getFieldNames('users') : [];

            $wantedFields = ['id', 'title', 'slug', 'user_id', 'project_type', 'status', 'event_date', 'created_at', 'updated_at'];
            $selectParts = [];

            foreach ($wantedFields as $field) {
                $selectParts[] = in_array($field, $landingPageFields, true)
                    ? 'landing_pages.`' . $field . '`'
                    : 'NULL AS `' . $field . '`';
            }

            $canJoinUsers = $usersTableExists
                && in_array('user_id', $landingPageFields, true)
                && in_array('id', $userFields, true);

            $selectParts[] = $canJoinUsers && in_array('name', $userFields, true)
                ? 'users.`name` AS `user_name`'
                : 'NULL AS `user_name`';
            $selectParts[] = $canJoinUsers && in_array('email', $userFields, true)
                ? 'users.`email` AS `user_email`'
                : 'NULL AS `user_email`';

            $builder = $this->db->table('landing_pages')
                ->select(implode(', ', $selectParts), false);

            if ($canJoinUsers) {
                $builder->join('users', 'users.id = landing_pages.user_id', 'left');
            }

            $canSearchPages = in_array('title', $landingPageFields, true)
                || in_array('slug', $landingPageFields, true)
                || ($canJoinUsers && (in_array('name', $userFields, true) || in_array('email', $userFields, true)));

            if ($search !== '' && $canSearchPages) {
                $builder->groupStart();
                if (in_array('title', $landingPageFields, true)) {
                    $builder->like('landing_pages.title', $search);
                }
                if (in_array('slug', $landingPageFields, true)) {
                    $builder->orLike('landing_pages.slug', $search);
                }
                if ($canJoinUsers && in_array('name', $userFields, true)) {
                    $builder->orLike('users.name', $search);
                }
                if ($canJoinUsers && in_array('email', $userFields, true)) {
                    $builder->orLike('users.email', $search);
                }
                $builder->groupEnd();
            }

            if ($status !== '' && in_array('status', $landingPageFields, true)) {
                $builder->where('landing_pages.status', $status);
            }

            if (in_array('updated_at', $landingPageFields, true)) {
                $builder->orderBy('landing_pages.updated_at', 'DESC');
            }

            if (in_array('created_at', $landingPageFields, true)) {
                $builder->orderBy('landing_pages.created_at', 'DESC');
            }

            if (! in_array('updated_at', $landingPageFields, true) && ! in_array('created_at', $landingPageFields, true) && in_array('id', $landingPageFields, true)) {
                $builder->orderBy('landing_pages.id', 'DESC');
            }

            $pages = $builder->get()->getResultArray();
        } catch (\Throwable $exception) {
            log_message('error', 'Admin pages gagal dimuat: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return view('admin/pages', [
                'pages' => [],
                'setupError' => 'Data undangan belum bisa dimuat. Periksa struktur tabel landing_pages dan users di database production.',
                'filters' => ['q' => $search, 'status' => $status],
                'projectTypeReady' => false,
            ]);
        }

        return view('admin/pages', [
            'pages' => $pages,
            'setupError' => null,
            'filters' => ['q' => $search, 'status' => $status],
            'projectTypeReady' => in_array('project_type', $landingPageFields, true),
        ]);
    }

    public function updatePageProjectType(int $id): RedirectResponse
    {
        if (! admin_can('admin.pages.manage')) {
            return redirect()->to(admin_access_denied_url('pages'))->with('error', 'Akses terbatas.');
        }

        if (! $this->db->tableExists('landing_pages') || ! $this->db->fieldExists('project_type', 'landing_pages')) {
            return redirect()->to(site_url('admin/pages'))
                ->with('error', 'Kolom project_type belum tersedia. Jalankan database/alter_business_profile_project_type.sql terlebih dahulu.');
        }

        $projectType = $this->normalizePageProjectType((string) $this->request->getPost('project_type'));
        $page = $this->db->table('landing_pages')
            ->select('id')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($page === null) {
            return redirect()->to(site_url('admin/pages'))->with('error', 'Page tidak ditemukan.');
        }

        $payload = ['project_type' => $projectType];
        if ($this->db->fieldExists('updated_at', 'landing_pages')) {
            $payload['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->table('landing_pages')
            ->where('id', $id)
            ->update($payload);

        return redirect()->to(site_url('admin/pages'))->with('success', 'Tipe project page berhasil diperbarui.');
    }

    private function normalizePageProjectType(string $value): string
    {
        $value = strtolower(trim($value));

        return match ($value) {
            'photobooth', 'digital_photobooth' => 'photobooth',
            'business_profile', 'business-profile' => 'business_profile',
            default => 'invitation',
        };
    }

    public function guestbooks(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.guestbooks.view', 'guestbooks')) {
            return $deny;
        }

        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $approval = trim((string) ($this->request->getGet('approval') ?? ''));
        $attendance = trim((string) ($this->request->getGet('attendance') ?? ''));

        if (! $this->db->tableExists('guest_books')) {
            return view('admin/guestbooks', [
                'guestbooks' => [],
                'setupError' => 'Tabel guest_books belum tersedia. Jalankan SQL modul public renderer/guestbook terlebih dahulu.',
                'filters' => ['q' => $search, 'approval' => $approval, 'attendance' => $attendance],
            ]);
        }

        $guestbookModel = new GuestBookModel();
        $guestbookFields = $this->db->getFieldNames('guest_books');

        $builder = $guestbookModel
            ->select('guest_books.*, landing_pages.title AS page_title, landing_pages.slug AS page_slug')
            ->join('landing_pages', 'landing_pages.id = guest_books.landing_page_id', 'left');

        if ($search !== '') {
            $builder->groupStart()
                ->like('guest_books.guest_name', $search)
                ->orLike('guest_books.message', $search)
                ->orLike('landing_pages.title', $search)
                ->orLike('landing_pages.slug', $search)
                ->groupEnd();
        }

        if ($approval === 'approved' && in_array('is_approved', $guestbookFields, true)) {
            $builder->where('guest_books.is_approved', 1);
        } elseif ($approval === 'pending' && in_array('is_approved', $guestbookFields, true)) {
            $builder->where('guest_books.is_approved', 0);
        }

        if ($attendance !== '' && in_array('attendance', $guestbookFields, true)) {
            $builder->where('guest_books.attendance', $attendance);
        }

        $guestbooks = $builder
            ->orderBy('guest_books.created_at', 'DESC')
            ->findAll();

        return view('admin/guestbooks', [
            'guestbooks' => array_map(
                fn (array $guestbook): array => $this->normalizeGuestbook($guestbook),
                $guestbooks
            ),
            'setupError' => null,
            'filters' => ['q' => $search, 'approval' => $approval, 'attendance' => $attendance],
            'legacyWarning' => $this->isLegacyGuestbookSchema($guestbookFields)
                ? 'Sebagian data guestbook masih memakai struktur kolom lama. Jalankan SQL alter_guest_books.sql agar schema seragam.'
                : null,
        ]);
    }

    private function normalizeGuestbook(array $guestbook): array
    {
        $guestbook['guest_name'] ??= $guestbook['name'] ?? '-';
        $guestbook['attendance'] ??= $guestbook['attendance_status'] ?? 'ragu';
        $guestbook['is_approved'] ??= $guestbook['is_visible'] ?? 0;
        $guestbook['message'] ??= '';

        $attendanceMap = [
            'attending' => 'hadir',
            'not_attending' => 'tidak_hadir',
            'pending' => 'ragu',
        ];

        if (isset($attendanceMap[$guestbook['attendance']])) {
            $guestbook['attendance'] = $attendanceMap[$guestbook['attendance']];
        }

        return $guestbook;
    }

    private function isLegacyGuestbookSchema(array $fields): bool
    {
        return ! in_array('guest_name', $fields, true)
            || ! in_array('attendance', $fields, true)
            || ! in_array('is_approved', $fields, true);
    }

    private function uniqueCreatorSlug(string $displayName): string
    {
        $base = strtolower(trim($displayName));
        $base = preg_replace('/[^a-z0-9]+/i', '-', $base) ?: 'creator';
        $base = trim($base, '-');
        $base = $base !== '' ? substr($base, 0, 90) : 'creator';

        $profileModel = new CreatorProfileModel();
        $slug = $base;
        $suffix = 2;

        while ($profileModel->where('slug', $slug)->first() !== null) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
