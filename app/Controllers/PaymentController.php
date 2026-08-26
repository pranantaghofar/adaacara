<?php

namespace App\Controllers;

use App\Libraries\CreatorRoyaltyService;
use App\Libraries\ProductEntitlementService;
use App\Models\OrderModel;
use App\Models\BusinessProfileOrderModel;
use App\Models\PaymentSettingModel;
use App\Models\PhotoboothCustomDomainModel;
use App\Models\PhotoboothCustomDomainOrderModel;
use App\Models\PlanModel;
use App\Models\CreatorApplicationModel;
use App\Models\CreatorProfileModel;
use App\Models\UserSubscriptionModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class PaymentController extends BaseController
{
    private array $paymentMethods = [
        'BCA',
        'BRI',
        'Mandiri',
        'BNI',
        'QRIS',
        'DANA',
        'OVO',
        'GoPay',
        'ShopeePay',
    ];

    public function activateXendit(): string
    {
        $plans = (new PlanModel())->activePlans();

        return view('payment/activate_xendit', [
            'plans' => array_values(array_filter($plans, static function (array $plan): bool {
                return strtolower((string) ($plan['slug'] ?? '')) !== 'creator';
            })),
            'business' => [
                'brand' => 'AdaAcara.com',
                'name' => trim((string) env('XENDIT_BUSINESS_NAME', 'PT Shagania Labs Indonesia')),
                'address' => trim((string) env('XENDIT_BUSINESS_ADDRESS', 'Pulogebang, Cakung, Jakarta Timur, DKI Jakarta, Indonesia')),
                'whatsapp' => trim((string) env('XENDIT_BUSINESS_WHATSAPP', 'Nomor WhatsApp resmi AdaAcara')),
                'whatsapp_url' => trim((string) env('XENDIT_BUSINESS_WHATSAPP_URL', '')),
                'email' => trim((string) env('XENDIT_BUSINESS_EMAIL', 'hello@adaacara.com')),
            ],
        ]);
    }

    public function plans(): string
    {
        $planModel = new PlanModel();
        $orderModel = new OrderModel();
        $subscriptionModel = new UserSubscriptionModel();
        $userId = (int) (session()->get('userId') ?? 0);
        $latestOrdersByPlan = [];
        $creatorStatus = $userId > 0 ? (new CreatorProfileModel())->statusForUser($userId) : ['status' => 'none'];

        if ($userId > 0) {
            foreach ($orderModel->getByUser($userId) as $order) {
                $planId = (int) ($order['plan_id'] ?? 0);
                if ($planId > 0 && ! isset($latestOrdersByPlan[$planId])) {
                    $latestOrdersByPlan[$planId] = $order;
                }
            }
        }

        return view('payment/plans', [
            'plans' => $planModel->activePlans(),
            'activeSubscription' => $userId > 0 ? $subscriptionModel->activeWithPlanByUser($userId) : null,
            'latestOrdersByPlan' => $latestOrdersByPlan,
            'creatorStatus' => $creatorStatus,
        ]);
    }

    public function checkout(string $slug): string|RedirectResponse
    {
        $plan = $this->activePlan($slug);
        $isProductPlan = (new ProductEntitlementService())->isProductPlan($plan);
        $blocked = $this->membershipCheckoutBlockReason($plan, (int) session()->get('userId'));
        if ($blocked !== null) {
            return redirect()->to('/plans')->with('error', $blocked);
        }

        if (! $isProductPlan && ! $this->isCreatorPlan($plan) && $this->userHasCreatorFlow((int) session()->get('userId'))) {
            return redirect()->to('/plans')->with('error', 'Akun creator tidak bisa membeli paket membership. Gunakan Dashboard Creator untuk mengelola template.');
        }

        if ($this->isCreatorPlan($plan) && $this->userHasActiveCreatorProfile((int) session()->get('userId'))) {
            return redirect()->to('/creator/dashboard')->with('success', 'Akun creator kamu sudah aktif.');
        }

        $settings = (new PaymentSettingModel())->getSettings();

        return view('payment/checkout', [
            'plan' => $plan,
            'paymentMethods' => $this->availablePaymentMethods($settings, $plan),
            'paymentMode' => $settings['payment_mode'] ?? 'manual',
        ]);
    }

    public function storeCheckout(string $slug): RedirectResponse
    {
        $plan = $this->activePlan($slug);
        $isProductPlan = (new ProductEntitlementService())->isProductPlan($plan);
        $blocked = $this->membershipCheckoutBlockReason($plan, (int) session()->get('userId'));
        if ($blocked !== null) {
            return redirect()->to('/plans')->with('error', $blocked);
        }

        if (! $isProductPlan && ! $this->isCreatorPlan($plan) && $this->userHasCreatorFlow((int) session()->get('userId'))) {
            return redirect()->to('/plans')->with('error', 'Akun creator tidak bisa membeli paket membership. Gunakan Dashboard Creator untuk mengelola template.');
        }

        if ($this->isCreatorPlan($plan) && $this->userHasActiveCreatorProfile((int) session()->get('userId'))) {
            return redirect()->to('/creator/dashboard')->with('success', 'Akun creator kamu sudah aktif.');
        }

        $settings = (new PaymentSettingModel())->getSettings();
        $paymentMethods = $this->availablePaymentMethods($settings, $plan);

        $rules = [
            'payment_method' => 'required|in_list[' . implode(',', $paymentMethods) . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $orderModel = new OrderModel();
        $paymentMethod = (string) $this->request->getPost('payment_method');
        $invoiceNumber = $orderModel->makeInvoiceNumber();
        $orderPayload = [
            'user_id' => (int) session()->get('userId'),
            'plan_id' => (int) $plan['id'],
            'invoice_number' => $invoiceNumber,
            'amount' => (int) $plan['price'],
            'payment_method' => $paymentMethod,
            'midtrans_order_id' => $paymentMethod === 'Midtrans' ? $invoiceNumber : null,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($paymentMethod === 'Lynk') {
            $orderPayload['payment_provider'] = 'lynk';
            $orderPayload['lynk_ref_id'] = $invoiceNumber;
            $orderPayload['lynk_payment_url'] = $this->lynkPaymentUrl($settings, $plan);
            $orderPayload['status'] = 'pending_payment';
        }

        $orderId = $orderModel->insert($orderPayload, true);

        if (! $orderId) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['order' => 'Invoice gagal dibuat. Coba lagi.']);
        }

        if ($paymentMethod === 'Midtrans') {
            $snap = $this->createMidtransSnapTransaction($settings, $orderModel->findByUser((int) $orderId, (int) session()->get('userId')));
            if (! empty($snap['redirect_url'])) {
                $orderModel->update((int) $orderId, [
                    'midtrans_token' => $snap['token'] ?? null,
                    'midtrans_redirect_url' => $snap['redirect_url'],
                    'midtrans_status' => 'snap_created',
                    'status' => 'pending_payment',
                ]);

                return redirect()->to((string) $snap['redirect_url']);
            }

            $orderModel->update((int) $orderId, [
                'admin_note' => $snap['error'] ?? 'Pembayaran otomatis gagal dibuat. Silakan coba lagi.',
            ]);

            return redirect()->to('/orders/' . $orderId)
                ->with('error', $snap['error'] ?? 'Pembayaran otomatis gagal dibuat.');
        }

        if ($paymentMethod === 'Lynk') {
            return redirect()->to('/orders/' . $orderId)
                ->with('success', 'Invoice Lynk berhasil dibuat. Lanjutkan pembayaran melalui tombol Lynk dan isi invoice AdaAcara jika diminta.');
        }

        return redirect()->to('/orders/' . $orderId)
            ->with('success', 'Invoice berhasil dibuat. Silakan upload bukti pembayaran.');
    }

    public function orders(): string
    {
        $orderModel = new OrderModel();
        $userId = (int) session()->get('userId');

        return view('payment/orders', [
            'orders' => $orderModel->getByUser($userId),
            'photoboothDomainOrders' => (new PhotoboothCustomDomainOrderModel())->getByUser($userId),
            'businessProfileOrders' => (new BusinessProfileOrderModel())->getByUser($userId),
        ]);
    }

    public function detail(int $id): string
    {
        return view('payment/detail', [
            'order' => $this->userOrder($id),
        ]);
    }

    public function uploadProof(int $id): RedirectResponse
    {
        $order = $this->userOrder($id);

        if (! in_array($order['status'], ['pending', 'rejected'], true)) {
            return redirect()->back()
                ->with('error', 'Bukti pembayaran hanya bisa diupload untuk invoice pending atau rejected.');
        }

        $rules = [
            'payment_proof' => [
                'label' => 'Bukti pembayaran',
                'rules' => 'uploaded[payment_proof]|max_size[payment_proof,2048]|is_image[payment_proof]|mime_in[payment_proof,image/jpg,image/jpeg,image/png,image/webp]|ext_in[payment_proof,jpg,jpeg,png,webp]',
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $file = $this->request->getFile('payment_proof');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'File bukti pembayaran tidak valid.');
        }

        $uploadPath = FCPATH . 'uploads/payment-proof';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = $order['invoice_number'] . '-' . time() . '.' . $file->getExtension();
        $file->move($uploadPath, $fileName, true);

        $orderModel = new OrderModel();
        $orderModel->update((int) $order['id'], [
            'payment_proof' => 'uploads/payment-proof/' . $fileName,
            'status' => 'waiting_approval',
        ]);

        return redirect()->to('/orders/' . $order['id'])
            ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu approval admin.');
    }

    public function photoboothDomainCheckout(int $domainId): string|RedirectResponse
    {
        $domain = $this->payablePhotoboothDomain($domainId);
        if ($domain instanceof RedirectResponse) {
            return $domain;
        }

        $orderModel = new PhotoboothCustomDomainOrderModel();
        if (! $orderModel->tableReady()) {
            return redirect()->to('/dashboard')->with('error', 'Tabel order custom domain Photobooth belum tersedia.');
        }

        $existingOrder = $orderModel->latestPayableForDomain((int) $domain['id'], (int) session()->get('userId'));
        if ($existingOrder !== null && in_array((string) ($existingOrder['status'] ?? ''), ['pending', 'pending_payment', 'waiting_approval'], true)) {
            return redirect()->to('/photobooth-domain-orders/' . (int) $existingOrder['id'])
                ->with('success', 'Invoice custom domain yang masih berjalan dibuka kembali.');
        }

        $settings = (new PaymentSettingModel())->getSettings();

        return view('payment/photobooth_domain_checkout', [
            'domain' => $domain,
            'paymentMethods' => $this->availablePaymentMethods($settings, null),
            'paymentMode' => $settings['payment_mode'] ?? 'manual',
        ]);
    }

    public function storePhotoboothDomainCheckout(int $domainId): RedirectResponse
    {
        $domain = $this->payablePhotoboothDomain($domainId);
        if ($domain instanceof RedirectResponse) {
            return $domain;
        }

        $orderModel = new PhotoboothCustomDomainOrderModel();
        if (! $orderModel->tableReady()) {
            return redirect()->to('/dashboard')->with('error', 'Tabel order custom domain Photobooth belum tersedia.');
        }

        $settings = (new PaymentSettingModel())->getSettings();
        $paymentMethods = $this->availablePaymentMethods($settings, null);
        $rules = [
            'payment_method' => 'required|in_list[' . implode(',', $paymentMethods) . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $existingOrder = $orderModel->latestPayableForDomain((int) $domain['id'], (int) session()->get('userId'));
        if ($existingOrder !== null && in_array((string) ($existingOrder['status'] ?? ''), ['pending', 'pending_payment', 'waiting_approval'], true)) {
            return redirect()->to('/photobooth-domain-orders/' . (int) $existingOrder['id'])
                ->with('success', 'Invoice custom domain yang masih berjalan dibuka kembali.');
        }

        $paymentMethod = (string) $this->request->getPost('payment_method');
        $invoiceNumber = $orderModel->makeInvoiceNumber();
        $orderPayload = [
            'photobooth_custom_domain_id' => (int) $domain['id'],
            'landing_page_id' => (int) $domain['landing_page_id'],
            'user_id' => (int) session()->get('userId'),
            'invoice_number' => $invoiceNumber,
            'amount' => (int) ($domain['price'] ?? 250000),
            'payment_method' => $paymentMethod,
            'midtrans_order_id' => $paymentMethod === 'Midtrans' ? $invoiceNumber : null,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($paymentMethod === 'Lynk') {
            $orderPayload['payment_provider'] = 'lynk';
            $orderPayload['lynk_ref_id'] = $invoiceNumber;
            $orderPayload['lynk_payment_url'] = $this->lynkPaymentUrl($settings, null);
            $orderPayload['status'] = 'pending_payment';
        }

        $orderId = $orderModel->insert($orderPayload, true);
        if (! $orderId) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['order' => 'Invoice custom domain gagal dibuat. Coba lagi.']);
        }

        (new PhotoboothCustomDomainModel())->update((int) $domain['id'], [
            'status' => 'waiting_payment',
            'payment_status' => 'unpaid',
            'notes' => 'Invoice pembayaran add-on custom domain sudah dibuat. Menunggu pembayaran.',
        ]);

        $order = $orderModel->findByUser((int) $orderId, (int) session()->get('userId'));
        if ($paymentMethod === 'Midtrans') {
            $snap = $this->createMidtransSnapForPhotoboothDomain($settings, $order);
            if (! empty($snap['redirect_url'])) {
                $orderModel->update((int) $orderId, [
                    'midtrans_token' => $snap['token'] ?? null,
                    'midtrans_redirect_url' => $snap['redirect_url'],
                    'midtrans_status' => 'snap_created',
                    'status' => 'pending_payment',
                ]);

                return redirect()->to((string) $snap['redirect_url']);
            }

            $orderModel->update((int) $orderId, [
                'admin_note' => $snap['error'] ?? 'Pembayaran otomatis gagal dibuat. Silakan coba lagi.',
            ]);

            return redirect()->to('/photobooth-domain-orders/' . (int) $orderId)
                ->with('error', $snap['error'] ?? 'Pembayaran otomatis gagal dibuat.');
        }

        if ($paymentMethod === 'Lynk') {
            return redirect()->to('/photobooth-domain-orders/' . (int) $orderId)
                ->with('success', 'Invoice Lynk custom domain dibuat. Lanjutkan pembayaran melalui tombol Lynk dan isi invoice AdaAcara jika diminta.');
        }

        return redirect()->to('/photobooth-domain-orders/' . (int) $orderId)
            ->with('success', 'Invoice custom domain berhasil dibuat. Silakan upload bukti pembayaran.');
    }

    public function photoboothDomainOrderDetail(int $id): string
    {
        return view('payment/photobooth_domain_detail', [
            'order' => $this->userPhotoboothDomainOrder($id),
        ]);
    }

    public function uploadPhotoboothDomainOrderProof(int $id): RedirectResponse
    {
        $order = $this->userPhotoboothDomainOrder($id);
        if (! in_array((string) ($order['status'] ?? ''), ['pending', 'rejected'], true)) {
            return redirect()->back()
                ->with('error', 'Bukti pembayaran hanya bisa diupload untuk invoice pending atau rejected.');
        }

        $rules = [
            'payment_proof' => [
                'label' => 'Bukti pembayaran',
                'rules' => 'uploaded[payment_proof]|max_size[payment_proof,2048]|is_image[payment_proof]|mime_in[payment_proof,image/jpg,image/jpeg,image/png,image/webp]|ext_in[payment_proof,jpg,jpeg,png,webp]',
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $file = $this->request->getFile('payment_proof');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'File bukti pembayaran tidak valid.');
        }

        $uploadPath = FCPATH . 'uploads/photobooth-domain-proof';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = $order['invoice_number'] . '-' . time() . '.' . $file->getExtension();
        $file->move($uploadPath, $fileName, true);
        $proofPath = 'uploads/photobooth-domain-proof/' . $fileName;

        (new PhotoboothCustomDomainOrderModel())->update((int) $order['id'], [
            'payment_proof' => $proofPath,
            'status' => 'waiting_approval',
        ]);

        $this->updatePhotoboothDomainSafely((int) $order['photobooth_custom_domain_id'], [
            'payment_proof' => $proofPath,
            'payment_status' => 'waiting_confirmation',
            'status' => 'waiting_payment',
            'payment_submitted_at' => date('Y-m-d H:i:s'),
            'notes' => 'Bukti pembayaran add-on domain sudah dikirim. Menunggu konfirmasi admin.',
        ]);

        return redirect()->to('/photobooth-domain-orders/' . (int) $order['id'])
            ->with('success', 'Bukti pembayaran custom domain berhasil diupload. Menunggu konfirmasi admin.');
    }

    public function midtransNotification(): ResponseInterface
    {
        $payload = [];
        $rawBody = (string) $this->request->getBody();
        if ($rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }
        if ($payload === []) {
            $post = $this->request->getPost();
            $payload = is_array($post) ? $post : [];
        }

        if ($payload === []) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Payload notifikasi Midtrans kosong.',
            ]);
        }

        $midtransOrderId = (string) ($payload['order_id'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');

        if ($midtransOrderId === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'order_id kosong']);
        }

        $settings = (new PaymentSettingModel())->getSettings();
        $serverKey = (string) ($settings['midtrans_server_key'] ?? '');
        if ($serverKey !== '') {
            $expected = hash('sha512', $midtransOrderId . '200' . $grossAmount . $serverKey);
            if (! hash_equals($expected, $signature)) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'signature tidak valid']);
            }
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByMidtransOrderId($midtransOrderId);
        if ($order === null && str_starts_with(strtoupper($midtransOrderId), 'AADOM-')) {
            $domainOrder = (new PhotoboothCustomDomainOrderModel())->findByMidtransOrderId($midtransOrderId);
            if ($domainOrder === null) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'order custom domain tidak ditemukan']);
            }

            $this->applyMidtransStatusToPhotoboothDomainOrder($domainOrder, $payload);

            return $this->response->setJSON(['success' => true]);
        }
        if ($order === null) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'order tidak ditemukan']);
        }

        $status = 'pending_payment';
        if (in_array($transactionStatus, ['settlement', 'capture'], true) && ($fraudStatus === '' || $fraudStatus === 'accept')) {
            $status = 'paid';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true)) {
            $status = 'failed';
        }

        $update = [
            'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
            'midtrans_status' => $transactionStatus,
            'midtrans_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'status' => $status,
        ];

        if ($status === 'paid' && ($order['status'] ?? '') !== 'paid') {
            $update['paid_at'] = date('Y-m-d H:i:s');
        }

        $orderModel->update((int) $order['id'], $update);

        if ($status === 'paid') {
            $this->activatePaidOrderAccess((int) $order['id']);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function midtransNotificationStatus(): ResponseInterface
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Endpoint notifikasi Midtrans aktif. Midtrans harus mengirim callback dengan metode POST.',
        ]);
    }

    public function lynkNotification(): ResponseInterface
    {
        $payload = [];
        $rawBody = (string) $this->request->getBody();
        if ($rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        if ($payload === []) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Endpoint notifikasi Lynk aktif. Payload kosong diterima sebagai health check.',
            ]);
        }

        $settings = (new PaymentSettingModel())->getSettings();
        $merchantKey = trim((string) ($settings['lynk_merchant_key'] ?? ''));
        if ($merchantKey === '') {
            return $this->response->setStatusCode(503)->setJSON([
                'success' => false,
                'message' => 'Merchant key Lynk belum dikonfigurasi.',
            ]);
        }

        $messageData = $payload['data']['message_data'] ?? [];
        $messageId = (string) ($payload['data']['message_id'] ?? '');
        $refId = (string) ($messageData['refId'] ?? '');
        $grandTotal = $messageData['totals']['grandTotal'] ?? null;
        $amountForSignature = is_scalar($grandTotal) ? (string) $grandTotal : '';
        $receivedSignature = (string) ($this->request->getHeaderLine('X-Lynk-Signature') ?: $this->request->getHeaderLine('x-lynk-signature'));

        if ($messageId === '' || $refId === '' || $amountForSignature === '' || $receivedSignature === '') {
            log_message('warning', 'Lynk webhook incomplete. event={event} has_message_id={has_message_id} has_ref_id={has_ref_id} has_amount={has_amount} has_signature={has_signature}', [
                'event' => (string) ($payload['event'] ?? '-'),
                'has_message_id' => $messageId !== '' ? 'yes' : 'no',
                'has_ref_id' => $refId !== '' ? 'yes' : 'no',
                'has_amount' => $amountForSignature !== '' ? 'yes' : 'no',
                'has_signature' => $receivedSignature !== '' ? 'yes' : 'no',
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Webhook Lynk diterima sebagai test/incomplete payload. Tidak ada order yang diubah.',
            ]);
        }

        $expectedSignature = hash('sha256', $amountForSignature . $refId . $messageId . $merchantKey);
        if (! hash_equals(strtolower($expectedSignature), strtolower($receivedSignature))) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Signature Lynk tidak valid.',
            ]);
        }

        $mappedStatus = $this->lynkOrderStatusFromPayload($payload);
        $lynkStatus = $this->lynkStatusLabelFromPayload($payload);

        $orderModel = new OrderModel();
        $existingMessage = $orderModel->findByLynkMessageId($messageId);
        if ($existingMessage !== null) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notifikasi Lynk sudah pernah diproses.',
            ]);
        }

        $domainOrderModel = new PhotoboothCustomDomainOrderModel();
        $existingDomainMessage = $domainOrderModel->findByLynkMessageId($messageId);
        if ($existingDomainMessage !== null) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notifikasi Lynk custom domain sudah pernah diproses.',
            ]);
        }

        [$domainOrder, $domainMatchNote] = $this->matchLynkPhotoboothDomainOrder($payload);
        if ($domainOrder !== null) {
            if (! $this->lynkPayloadMatchesOrderAmount($payload, (int) ($domainOrder['amount'] ?? 0))) {
                log_message('warning', 'Lynk custom domain amount mismatch. order_id={order_id} invoice={invoice} ref_id={ref_id} message_id={message_id}', [
                    'order_id' => (string) ($domainOrder['id'] ?? '-'),
                    'invoice' => (string) ($domainOrder['invoice_number'] ?? '-'),
                    'ref_id' => $refId,
                    'message_id' => $messageId,
                ]);

                return $this->response->setStatusCode(202)->setJSON([
                    'success' => true,
                    'message' => 'Notifikasi Lynk custom domain diterima, tetapi nominal tidak cocok.',
                ]);
            }

            $update = [
                'payment_provider' => 'lynk',
                'payment_method' => 'Lynk',
                'lynk_ref_id' => $refId !== '' ? $refId : (string) ($domainOrder['lynk_ref_id'] ?? ''),
                'lynk_message_id' => $messageId,
                'lynk_status' => $lynkStatus,
                'lynk_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'lynk_match_note' => $domainMatchNote,
                'status' => $this->resolveLynkOrderStatus((string) ($domainOrder['status'] ?? ''), $mappedStatus),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($update['status'] === 'paid' && ($domainOrder['status'] ?? '') !== 'paid') {
                $update['paid_at'] = date('Y-m-d H:i:s');
            }

            $domainOrderModel->update((int) $domainOrder['id'], $update);
            if ($update['status'] === 'paid') {
                $this->markPhotoboothDomainOrderPaid((int) $domainOrder['id']);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $update['status'] === 'paid'
                    ? 'Pembayaran Lynk custom domain berhasil diproses.'
                    : 'Status Lynk custom domain berhasil disinkronkan.',
            ]);
        }

        [$order, $matchNote, $matchDebug] = $this->matchLynkOrder($payload);
        if ($order === null) {
            log_message('warning', 'Lynk webhook unmatched. ref_id={ref_id} message_id={message_id} email={email} amount={amount} debug={debug}', [
                'ref_id' => $refId,
                'message_id' => $messageId,
                'email' => (string) ($messageData['customer']['email'] ?? '-'),
                'amount' => $amountForSignature,
                'debug' => json_encode($matchDebug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);

            return $this->response->setStatusCode(202)->setJSON([
                'success' => true,
                'message' => 'Notifikasi Lynk diterima, tetapi order belum bisa dicocokkan otomatis.',
                'match_debug' => $matchDebug,
            ]);
        }

        if (! $this->lynkPayloadMatchesOrderAmount($payload, (int) ($order['amount'] ?? 0))) {
            log_message('warning', 'Lynk webhook amount mismatch. order_id={order_id} invoice={invoice} ref_id={ref_id} message_id={message_id}', [
                'order_id' => (string) ($order['id'] ?? '-'),
                'invoice' => (string) ($order['invoice_number'] ?? '-'),
                'ref_id' => $refId,
                'message_id' => $messageId,
            ]);

            return $this->response->setStatusCode(202)->setJSON([
                'success' => true,
                'message' => 'Notifikasi Lynk diterima, tetapi nominal tidak cocok dengan order.',
            ]);
        }

        $update = [
            'payment_provider' => 'lynk',
            'payment_method' => 'Lynk',
            'lynk_ref_id' => $refId,
            'lynk_message_id' => $messageId,
            'lynk_status' => $lynkStatus,
            'lynk_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'lynk_match_note' => $matchNote,
            'status' => $this->resolveLynkOrderStatus((string) ($order['status'] ?? ''), $mappedStatus),
        ];

        if ($update['status'] === 'paid' && ($order['status'] ?? '') !== 'paid') {
            $update['paid_at'] = date('Y-m-d H:i:s');
        }

        $orderModel->update((int) $order['id'], $update);
        if ($update['status'] === 'paid') {
            $this->activatePaidOrderAccess((int) $order['id']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $update['status'] === 'paid'
                ? 'Pembayaran Lynk berhasil diproses.'
                : 'Status Lynk berhasil disinkronkan ke order.',
        ]);
    }

    public function lynkNotificationStatus(): ResponseInterface
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Endpoint notifikasi Lynk aktif. Lynk harus mengirim callback dengan metode POST dan header X-Lynk-Signature.',
        ]);
    }

    public function midtransFinish(): RedirectResponse
    {
        $invoiceNumber = trim((string) ($this->request->getGet('order_id') ?? $this->request->getGet('orderId') ?? ''));
        if ($invoiceNumber === '') {
            return redirect()->to('/orders')->with('error', 'Data pembayaran Midtrans tidak lengkap.');
        }

        if (str_starts_with(strtoupper($invoiceNumber), 'AADOM-')) {
            $domainOrderModel = new PhotoboothCustomDomainOrderModel();
            $domainOrder = $domainOrderModel->findByMidtransOrderId($invoiceNumber);
            $userId = (int) (session()->get('userId') ?? 0);
            if ($domainOrder === null || (int) ($domainOrder['user_id'] ?? 0) !== $userId) {
                return redirect()->to('/dashboard')->with('error', 'Invoice custom domain tidak ditemukan.');
            }

            $settings = (new PaymentSettingModel())->getSettings();
            $statusPayload = $this->fetchMidtransTransactionStatus($settings, $invoiceNumber);
            if (! empty($statusPayload['payload']) && is_array($statusPayload['payload'])) {
                $this->applyMidtransStatusToPhotoboothDomainOrder($domainOrder, $statusPayload['payload']);
                $domainOrder = $domainOrderModel->findByMidtransOrderId($invoiceNumber) ?? $domainOrder;
            }

            if (($domainOrder['status'] ?? '') === 'paid') {
                $this->markPhotoboothDomainOrderPaid((int) $domainOrder['id']);

                return redirect()->to('/photobooth-domain-orders/' . (int) $domainOrder['id'])
                    ->with('success', 'Pembayaran custom domain berhasil. Menunggu aktivasi DNS/SSL oleh admin.');
            }

            if (! empty($statusPayload['error'])) {
                return redirect()->to('/photobooth-domain-orders/' . (int) $domainOrder['id'])
                    ->with('error', (string) $statusPayload['error']);
            }

            return redirect()->to('/photobooth-domain-orders/' . (int) $domainOrder['id'])
                ->with('success', 'Pembayaran custom domain sedang diproses. Status akan diperbarui otomatis setelah konfirmasi.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByMidtransOrderId($invoiceNumber);
        $userId = (int) (session()->get('userId') ?? 0);

        if ($order === null || (int) ($order['user_id'] ?? 0) !== $userId) {
            return redirect()->to('/orders')->with('error', 'Order pembayaran tidak ditemukan.');
        }

        $settings = (new PaymentSettingModel())->getSettings();
        $statusPayload = $this->fetchMidtransTransactionStatus($settings, $invoiceNumber);
        if (! empty($statusPayload['payload']) && is_array($statusPayload['payload'])) {
            $this->applyMidtransStatusToOrder($order, $statusPayload['payload']);
            $order = $orderModel->findByMidtransOrderId($invoiceNumber) ?? $order;
        }

        if (($order['status'] ?? '') === 'paid') {
            $this->activatePaidOrderAccess((int) $order['id']);
            $successMessage = (new ProductEntitlementService())->isProductPlan($order)
                ? 'Pembayaran berhasil. Akses produk kamu sudah aktif.'
                : 'Pembayaran berhasil. Paket kamu sudah aktif.';

            return redirect()->to('/dashboard')
                ->with('success', $successMessage)
                ->with('google_ads_conversion', $this->googleAdsConversionPayload($order));
        }

        if (! empty($statusPayload['error'])) {
            return redirect()->to('/orders/' . (int) $order['id'])
                ->with('error', (string) $statusPayload['error']);
        }

        return redirect()->to('/orders/' . (int) $order['id'])
            ->with('success', 'Pembayaran sedang diproses. Status akan diperbarui otomatis setelah Midtrans mengirim konfirmasi.');
    }

    private function activePlan(string $slug): array
    {
        $planModel = new PlanModel();
        $plan = $planModel->findActiveBySlug($slug);

        if ($plan === null) {
            throw PageNotFoundException::forPageNotFound('Paket tidak ditemukan.');
        }

        return $plan;
    }

    private function availablePaymentMethods(array $settings, ?array $plan = null): array
    {
        $mode = (string) ($settings['payment_mode'] ?? 'manual');
        $methods = [];
        if (in_array($mode, ['manual', 'both', 'manual_lynk', 'all'], true)) {
            $methods = array_merge($methods, $this->paymentMethods);
        }
        if (in_array($mode, ['midtrans', 'both', 'midtrans_lynk', 'all'], true) && $this->midtransConfigured($settings)) {
            $methods[] = 'Midtrans';
        }
        if (in_array($mode, ['lynk', 'manual_lynk', 'midtrans_lynk', 'all'], true) && $this->lynkConfigured($settings, $plan)) {
            $methods[] = 'Lynk';
        }

        return $methods ?: $this->paymentMethods;
    }

    private function membershipCheckoutBlockReason(array $plan, int $userId): ?string
    {
        if ($userId <= 0 || $this->isCreatorPlan($plan) || (new ProductEntitlementService())->isProductPlan($plan)) {
            return null;
        }

        $activeSubscription = (new UserSubscriptionModel())->activeWithPlanByUser($userId);
        if ($activeSubscription === null) {
            return null;
        }

        $activeRank = $this->membershipPlanRank((string) ($activeSubscription['plan_slug'] ?? $activeSubscription['plan_name'] ?? ''));
        $targetRank = $this->membershipPlanRank((string) ($plan['slug'] ?? $plan['name'] ?? ''));

        if ($activeRank <= 0 || $targetRank <= 0 || $targetRank > $activeRank) {
            return null;
        }

        return 'Akun kamu sudah memiliki paket aktif. Hanya upgrade ke paket yang lebih tinggi yang tersedia.';
    }

    private function membershipPlanRank(string $planKey): int
    {
        return match (strtolower(trim($planKey))) {
            'basic', 'starter', 'buat-pakai-sendiri', 'buat-acara-sendiri' => 1,
            'premium', 'buat-nyoba-jualan', 'buat-coba-jualan' => 2,
            'business', 'busseniss', 'buat-niat-jualan' => 3,
            default => 0,
        };
    }

    private function midtransConfigured(array $settings): bool
    {
        return trim((string) ($settings['midtrans_server_key'] ?? '')) !== '';
    }

    private function lynkConfigured(array $settings, ?array $plan = null): bool
    {
        return $this->lynkPaymentUrl($settings, $plan) !== ''
            && trim((string) ($settings['lynk_merchant_key'] ?? '')) !== '';
    }

    private function lynkPaymentUrl(array $settings, ?array $plan = null): string
    {
        $planUrl = trim((string) ($plan['lynk_payment_url'] ?? ''));
        if ($planUrl !== '' && filter_var($planUrl, FILTER_VALIDATE_URL)) {
            return $planUrl;
        }

        $url = trim((string) ($settings['lynk_payment_url'] ?? ''));
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }

    private function createMidtransSnapTransaction(array $settings, ?array $order): array
    {
        if ($order === null) {
            return ['error' => 'Order tidak ditemukan.'];
        }

        $serverKey = trim((string) ($settings['midtrans_server_key'] ?? ''));
        if ($serverKey === '') {
            return ['error' => 'Pembayaran otomatis belum dikonfigurasi admin.'];
        }

        $isProduction = (string) ($settings['midtrans_is_production'] ?? '1') === '1';
        $endpoint = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        if (! function_exists('curl_init')) {
            return ['error' => 'Ekstensi cURL PHP belum aktif di server.'];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => (string) $order['invoice_number'],
                'gross_amount' => (int) $order['amount'],
            ],
            'customer_details' => [
                'first_name' => (string) (session()->get('userName') ?? 'User'),
                'email' => (string) (session()->get('userEmail') ?? ''),
            ],
            'item_details' => [[
                'id' => (string) ($order['plan_id'] ?? 'plan'),
                'price' => (int) $order['amount'],
                'quantity' => 1,
                'name' => (string) ($order['plan_name'] ?? 'Paket AdaAcara'),
            ]],
            'callbacks' => [
                'finish' => site_url('payment/midtrans/finish') . '?order_id=' . rawurlencode((string) $order['invoice_number']),
            ],
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status < 200 || $status >= 300) {
            return ['error' => $error ?: 'Pembayaran otomatis gagal merespons. Status ' . $status];
        }

        $data = json_decode((string) $body, true);
        if (! is_array($data)) {
            return ['error' => 'Response pembayaran otomatis tidak valid.'];
        }

        return $data;
    }

    private function fetchMidtransTransactionStatus(array $settings, string $orderId): array
    {
        $serverKey = trim((string) ($settings['midtrans_server_key'] ?? ''));
        if ($serverKey === '') {
            return ['error' => 'Server Key Midtrans belum dikonfigurasi.'];
        }

        if (! function_exists('curl_init')) {
            return ['error' => 'Ekstensi cURL PHP belum aktif di server.'];
        }

        $isProduction = (string) ($settings['midtrans_is_production'] ?? '1') === '1';
        $endpoint = ($isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com')
            . '/v2/' . rawurlencode($orderId) . '/status';

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status < 200 || $status >= 300) {
            return ['error' => $error ?: 'Status pembayaran belum bisa diverifikasi.'];
        }

        $payload = json_decode((string) $body, true);
        if (! is_array($payload)) {
            return ['error' => 'Response status Midtrans tidak valid.'];
        }

        return ['payload' => $payload];
    }

    private function applyMidtransStatusToOrder(array $order, array $payload): void
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        $status = 'pending_payment';
        if (in_array($transactionStatus, ['settlement', 'capture'], true) && ($fraudStatus === '' || $fraudStatus === 'accept')) {
            $status = 'paid';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true)) {
            $status = 'failed';
        }

        $update = [
            'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
            'midtrans_status' => $transactionStatus,
            'midtrans_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'status' => $status,
        ];

        if ($status === 'paid' && ($order['status'] ?? '') !== 'paid') {
            $update['paid_at'] = date('Y-m-d H:i:s');
        }

        (new OrderModel())->update((int) $order['id'], $update);
    }

    private function lynkOrderStatusFromPayload(array $payload): string
    {
        $event = strtolower((string) ($payload['event'] ?? ''));
        $action = strtolower((string) ($payload['data']['message_action'] ?? ''));
        $code = (string) ($payload['data']['message_code'] ?? '');
        $desc = strtolower((string) ($payload['data']['message_desc'] ?? ''));
        $title = strtolower((string) ($payload['data']['message_title'] ?? ''));
        $statusText = strtolower(trim($event . ' ' . $action . ' ' . $code . ' ' . $desc . ' ' . $title));

        if ($event === 'payment.received' && strtoupper((string) ($payload['data']['message_action'] ?? '')) === 'SUCCESS' && $code === '0') {
            return 'paid';
        }

        if (preg_match('/expired|expire|kedaluwarsa|kadaluarsa/', $statusText) === 1) {
            return 'expired';
        }

        if (preg_match('/failed|failure|fail|cancelled|canceled|cancel|error|deny|denied|void|refund/', $statusText) === 1) {
            return 'failed';
        }

        return 'pending_payment';
    }

    private function lynkStatusLabelFromPayload(array $payload): string
    {
        $parts = array_values(array_filter([
            (string) ($payload['event'] ?? ''),
            (string) ($payload['data']['message_action'] ?? ''),
            (string) ($payload['data']['message_code'] ?? ''),
        ], static fn (string $part): bool => trim($part) !== ''));

        $label = $parts !== [] ? implode(':', $parts) : 'lynk.notification';

        return substr($label, 0, 80);
    }

    private function resolveLynkOrderStatus(string $currentStatus, string $mappedStatus): string
    {
        if ($currentStatus === 'paid' && $mappedStatus !== 'paid') {
            return 'paid';
        }

        if (in_array($currentStatus, ['failed', 'expired'], true) && $mappedStatus === 'pending_payment') {
            return $currentStatus;
        }

        return in_array($mappedStatus, ['paid', 'failed', 'expired', 'pending_payment'], true)
            ? $mappedStatus
            : 'pending_payment';
    }

    private function matchLynkOrder(array $payload): array
    {
        $orderModel = new OrderModel();
        $messageData = $payload['data']['message_data'] ?? [];
        $refId = (string) ($messageData['refId'] ?? '');
        $amounts = $this->lynkPayloadAmounts($payload);
        $paidAtUtc = $this->lynkPayloadPaidAtUtc($payload);
        $debug = [
            'has_ref_id' => $refId !== '',
            'invoice_extracted' => '',
            'email_present' => false,
            'payload_amounts' => $amounts,
            'paid_at_utc' => $paidAtUtc,
            'email_candidate_count' => 0,
            'email_amount_candidate_count' => 0,
            'amount_candidate_count' => 0,
            'amount_candidate_ids' => [],
            'amount_window_orders' => [],
        ];

        if ($refId !== '') {
            $order = $orderModel->findByLynkRefId($refId);
            if ($order !== null) {
                return [$order, 'matched_by_lynk_ref_id', $debug];
            }
        }

        $invoiceNumber = $this->extractLynkInvoiceNumber($payload);
        $debug['invoice_extracted'] = $invoiceNumber;
        if ($invoiceNumber !== '') {
            $order = $orderModel->findByInvoiceNumber($invoiceNumber);
            if ($order !== null && in_array((string) ($order['status'] ?? ''), ['pending', 'pending_payment'], true)) {
                return [$order, 'matched_by_invoice', $debug];
            }
        }

        $email = strtolower(trim((string) ($messageData['customer']['email'] ?? '')));
        $debug['email_present'] = $email !== '';
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $candidates = [];
            $emailCandidates = $orderModel->findPendingLynkCandidatesByEmail($email);
            $debug['email_candidate_count'] = count($emailCandidates);
            foreach ($emailCandidates as $candidate) {
                if ($this->lynkPayloadMatchesOrderAmount($payload, (int) ($candidate['amount'] ?? 0))) {
                    $candidates[] = $candidate;
                }
            }
            $debug['email_amount_candidate_count'] = count($candidates);

            if (count($candidates) === 1) {
                return [$candidates[0], 'matched_by_email_amount_unique', $debug];
            }
        }

        $recentAmountCandidates = [];
        foreach ($amounts as $amount) {
            $amountCandidates = $paidAtUtc !== ''
                ? $orderModel->findPendingLynkCandidatesByAmountNearPaymentTime($amount, $paidAtUtc)
                : $orderModel->findRecentPendingLynkCandidatesByAmount($amount);

            if ($paidAtUtc !== '') {
                $debug['amount_window_orders'] = $orderModel->findOrderDiagnosticsByAmountNearPaymentTime($amount, $paidAtUtc);
            }

            foreach ($amountCandidates as $candidate) {
                $candidateId = (int) ($candidate['id'] ?? 0);
                if ($candidateId > 0) {
                    $recentAmountCandidates[$candidateId] = $candidate;
                }
            }
        }
        $debug['amount_candidate_count'] = count($recentAmountCandidates);
        $debug['amount_candidate_ids'] = array_keys($recentAmountCandidates);

        if (count($recentAmountCandidates) === 1) {
            return [reset($recentAmountCandidates), 'matched_by_recent_amount_unique', $debug];
        }

        return [null, '', $debug];
    }

    private function extractLynkInvoiceNumber(array $payload): string
    {
        $messageData = $payload['data']['message_data'] ?? [];
        $haystacks = [
            json_encode($messageData['items'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            (string) ($messageData['shippingInfo'] ?? ''),
            (string) ($messageData['shippingAddress'] ?? ''),
        ];

        foreach ($haystacks as $haystack) {
            if (preg_match('/INV-[0-9]{8}-[A-Fa-f0-9]{8}/', (string) $haystack, $matches) === 1) {
                return strtoupper($matches[0]);
            }
        }

        return '';
    }

    private function lynkPayloadMatchesOrderAmount(array $payload, int $orderAmount): bool
    {
        if ($orderAmount < 0) {
            return false;
        }

        foreach ($this->lynkPayloadAmounts($payload) as $amount) {
            if ($amount === $orderAmount) {
                return true;
            }
        }

        return false;
    }

    private function lynkPayloadPaidAtUtc(array $payload): string
    {
        $createdAt = trim((string) ($payload['data']['message_data']['createdAt'] ?? ''));
        if ($createdAt === '') {
            return '';
        }

        try {
            $timezone = preg_match('/(?:Z|[+-][0-9]{2}:?[0-9]{2})$/', $createdAt) === 1
                ? null
                : new \DateTimeZone('Asia/Jakarta');
            $date = $timezone === null
                ? new \DateTimeImmutable($createdAt)
                : new \DateTimeImmutable($createdAt, $timezone);

            return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return '';
        }
    }

    private function lynkPayloadAmounts(array $payload): array
    {
        $totals = $payload['data']['message_data']['totals'] ?? [];
        if (! is_array($totals)) {
            return [];
        }

        $amounts = [
            $this->normalizeLynkAmount($totals['grandTotal'] ?? null),
            $this->normalizeLynkAmount($totals['totalPrice'] ?? null),
        ];

        $computedGross = $this->normalizeLynkAmount($totals['totalPrice'] ?? null)
            + $this->normalizeLynkAmount($totals['totalAddon'] ?? null)
            + $this->normalizeLynkAmount($totals['totalShipping'] ?? null)
            - $this->normalizeLynkAmount($totals['discount'] ?? null);
        $amounts[] = $computedGross;

        return array_values(array_unique(array_filter($amounts, static fn (int $amount): bool => $amount > 0)));
    }

    private function normalizeLynkAmount(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value)) {
            $normalized = preg_replace('/[^0-9-]/', '', $value);
            return $normalized === '' ? 0 : (int) $normalized;
        }

        return 0;
    }

    private function googleAdsConversionPayload(array $order): array
    {
        return [
            'send_to' => 'AW-18262459541/CbuJCLe678YcEJWJnIRE',
            'value' => (int) ($order['amount'] ?? 0),
            'currency' => 'IDR',
            'transaction_id' => (string) ($order['invoice_number'] ?? $order['midtrans_order_id'] ?? ''),
        ];
    }

    private function payablePhotoboothDomain(int $domainId): array|RedirectResponse
    {
        $domainModel = new PhotoboothCustomDomainModel();
        if (! $domainModel->tableReady()) {
            return redirect()->to('/dashboard')->with('error', 'Fitur custom domain Photobooth belum siap.');
        }

        $domain = $domainModel->find(max(0, $domainId));
        $userId = (int) session()->get('userId');
        if ($domain === null || (int) ($domain['user_id'] ?? 0) !== $userId) {
            return redirect()->to('/dashboard')->with('error', 'Request custom domain tidak ditemukan.');
        }

        if (! in_array((string) ($domain['status'] ?? ''), ['available', 'waiting_payment'], true)
            || ! in_array((string) ($domain['payment_status'] ?? ''), ['unpaid', 'expired', 'refunded'], true)
        ) {
            return redirect()->to('/dashboard')->with('error', 'Custom domain belum siap dibayar atau sudah memiliki pembayaran berjalan.');
        }

        return $domain;
    }

    private function createMidtransSnapForPhotoboothDomain(array $settings, ?array $order): array
    {
        if ($order === null) {
            return ['error' => 'Invoice custom domain tidak ditemukan.'];
        }

        $serverKey = trim((string) ($settings['midtrans_server_key'] ?? ''));
        if ($serverKey === '') {
            return ['error' => 'Pembayaran otomatis belum dikonfigurasi admin.'];
        }

        $isProduction = (string) ($settings['midtrans_is_production'] ?? '1') === '1';
        $endpoint = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        if (! function_exists('curl_init')) {
            return ['error' => 'Ekstensi cURL PHP belum aktif di server.'];
        }

        $domain = (string) ($order['domain'] ?? 'custom domain');
        $payload = [
            'transaction_details' => [
                'order_id' => (string) $order['invoice_number'],
                'gross_amount' => (int) $order['amount'],
            ],
            'customer_details' => [
                'first_name' => (string) (session()->get('userName') ?? 'User'),
                'email' => (string) (session()->get('userEmail') ?? ''),
            ],
            'item_details' => [[
                'id' => 'photobooth-domain-' . (int) ($order['photobooth_custom_domain_id'] ?? 0),
                'price' => (int) $order['amount'],
                'quantity' => 1,
                'name' => 'Custom Domain Photobooth - ' . $domain,
            ]],
            'callbacks' => [
                'finish' => site_url('payment/midtrans/finish') . '?order_id=' . rawurlencode((string) $order['invoice_number']),
            ],
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status < 200 || $status >= 300) {
            return ['error' => $error ?: 'Pembayaran otomatis gagal merespons. Status ' . $status];
        }

        $data = json_decode((string) $body, true);
        if (! is_array($data)) {
            return ['error' => 'Response pembayaran otomatis tidak valid.'];
        }

        return $data;
    }

    private function applyMidtransStatusToPhotoboothDomainOrder(array $order, array $payload): void
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        $status = 'pending_payment';
        if (in_array($transactionStatus, ['settlement', 'capture'], true) && ($fraudStatus === '' || $fraudStatus === 'accept')) {
            $status = 'paid';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true)) {
            $status = $transactionStatus === 'expire' ? 'expired' : 'failed';
        }

        $update = [
            'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
            'midtrans_status' => $transactionStatus,
            'midtrans_payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'paid' && ($order['status'] ?? '') !== 'paid') {
            $update['paid_at'] = date('Y-m-d H:i:s');
        }

        (new PhotoboothCustomDomainOrderModel())->update((int) $order['id'], $update);
        if ($status === 'paid') {
            $this->markPhotoboothDomainOrderPaid((int) $order['id']);
        }
    }

    private function markPhotoboothDomainOrderPaid(int $orderId): void
    {
        $order = (new PhotoboothCustomDomainOrderModel())->find($orderId);
        if ($order === null || (string) ($order['status'] ?? '') !== 'paid') {
            return;
        }

        $this->updatePhotoboothDomainSafely((int) $order['photobooth_custom_domain_id'], [
            'status' => 'waiting_activation',
            'availability_status' => 'available',
            'payment_status' => 'paid',
            'paid_at' => $order['paid_at'] ?? date('Y-m-d H:i:s'),
            'notes' => 'Pembayaran add-on domain sudah dikonfirmasi otomatis. Menunggu aktivasi DNS/SSL.',
        ]);
    }

    private function updatePhotoboothDomainSafely(int $domainId, array $payload): void
    {
        if ($domainId <= 0 || $payload === []) {
            return;
        }

        $model = new PhotoboothCustomDomainModel();
        if (! $model->tableReady()) {
            return;
        }

        $fields = db_connect()->getFieldNames('photobooth_custom_domains');
        $safePayload = array_intersect_key($payload, array_flip($fields));
        if ($safePayload !== []) {
            $model->update($domainId, $safePayload);
        }
    }

    private function matchLynkPhotoboothDomainOrder(array $payload): array
    {
        $messageData = $payload['data']['message_data'] ?? [];
        $refId = (string) ($messageData['refId'] ?? '');
        $model = new PhotoboothCustomDomainOrderModel();

        if ($refId !== '') {
            $order = $model->findByLynkRefId($refId);
            if ($order !== null) {
                return [$order, 'matched_by_lynk_ref_id'];
            }
        }

        $invoiceNumber = $this->extractPhotoboothDomainInvoiceNumber($payload);
        if ($invoiceNumber !== '') {
            $order = $model->findByInvoiceNumber($invoiceNumber);
            if ($order !== null && in_array((string) ($order['status'] ?? ''), ['pending', 'pending_payment'], true)) {
                return [$order, 'matched_by_invoice'];
            }
        }

        return [null, ''];
    }

    private function extractPhotoboothDomainInvoiceNumber(array $payload): string
    {
        $messageData = $payload['data']['message_data'] ?? [];
        $haystacks = [
            (string) ($messageData['refId'] ?? ''),
            json_encode($messageData['items'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            (string) ($messageData['shippingInfo'] ?? ''),
            (string) ($messageData['shippingAddress'] ?? ''),
        ];

        foreach ($haystacks as $haystack) {
            if (preg_match('/AADOM-[0-9]{8}-[A-Fa-f0-9]{8}/', (string) $haystack, $matches) === 1) {
                return strtoupper($matches[0]);
            }
        }

        return '';
    }

    private function activatePaidOrderAccess(int $orderId): void
    {
        $order = (new OrderModel())->findAdminOrder($orderId);
        if ($order === null || ($order['status'] ?? '') !== 'paid') {
            return;
        }

        $this->confirmCreatorRoyaltiesForOrder($orderId);

        $productEntitlements = new ProductEntitlementService();
        if ($productEntitlements->isProductPlan($order)) {
            $productEntitlements->activateFromPaidOrder($order);
            return;
        }

        $subscriptionModel = new UserSubscriptionModel();
        if ($subscriptionModel->where('order_id', $orderId)->first()) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        if ($this->isCreatorPlan($order)) {
            $this->activateCreatorFromOrder($order, $now);
            return;
        }

        $subscriptionModel->insert([
            'user_id' => (int) $order['user_id'],
            'plan_id' => (int) $order['plan_id'],
            'order_id' => $orderId,
            'started_at' => $now,
            'expired_at' => $this->subscriptionExpiredAtForPlan($order, $now),
            'status' => 'active',
            'created_at' => $now,
        ]);
    }

    private function activateSubscriptionForPaidOrder(int $orderId): void
    {
        $this->activatePaidOrderAccess($orderId);
    }

    private function confirmCreatorRoyaltiesForOrder(int $orderId): void
    {
        try {
            (new CreatorRoyaltyService())->confirmRoyaltyForPaidOrder($orderId);
        } catch (\Throwable $error) {
            log_message('warning', 'Creator royalty order confirmation skipped. order={order} error={error}', [
                'order' => (string) $orderId,
                'error' => $error->getMessage(),
            ]);
        }
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

    private function userHasActiveCreatorProfile(int $userId): bool
    {
        return $userId > 0 && (new CreatorProfileModel())->activeForUser($userId) !== null;
    }

    private function userHasCreatorFlow(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return in_array((string) ((new CreatorProfileModel())->statusForUser($userId)['status'] ?? 'none'), ['pending', 'active'], true);
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
        if (! in_array($currentRole, ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin', 'creator'], true)) {
            $userModel->update($userId, ['role' => 'creator']);
        }

        $application = (new CreatorApplicationModel())
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($application !== null && ($application['status'] ?? '') === 'pending') {
            (new CreatorApplicationModel())->update((int) $application['id'], [
                'status' => 'approved',
                'reason' => null,
                'reviewed_by' => null,
                'reviewed_at' => $approvedAt,
            ]);
        }
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

    private function userOrder(int $id): array
    {
        $orderModel = new OrderModel();
        $order = $orderModel->findByUser($id, (int) session()->get('userId'));

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Invoice tidak ditemukan.');
        }

        return $order;
    }

    private function userPhotoboothDomainOrder(int $id): array
    {
        $orderModel = new PhotoboothCustomDomainOrderModel();
        $order = $orderModel->findByUser($id, (int) session()->get('userId'));

        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Invoice custom domain tidak ditemukan.');
        }

        return $order;
    }
}
