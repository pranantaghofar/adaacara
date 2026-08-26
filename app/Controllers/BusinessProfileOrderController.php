<?php

namespace App\Controllers;

use App\Libraries\BusinessProfileAccessService;
use App\Models\BusinessProfileOrderModel;
use App\Models\LandingPageModel;
use App\Models\PaymentSettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class BusinessProfileOrderController extends BaseController
{
    private array $manualPaymentMethods = [
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

    public function checkout(int $landingPageId): string|RedirectResponse
    {
        $service = new BusinessProfileAccessService();
        if (! $service->tablesReady()) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Pembayaran Business Profile belum aktif. Jalankan SQL Business Profile terlebih dahulu.');
        }

        $page = $this->payableBusinessProfilePage($landingPageId, $service);
        if ($service->hasActiveEntitlement((int) $page['id'], (int) session()->get('userId'))) {
            return redirect()->to(site_url('editor/' . (int) $page['id']))
                ->with('success', 'Business Profile ini sudah aktif.');
        }

        $settings = (new PaymentSettingModel())->getSettings();
        $paymentMethods = $this->availableManualPaymentMethods($settings);
        $order = (new BusinessProfileOrderModel())->latestPayableForPage((int) $page['id'], (int) session()->get('userId'));

        return view('payment/business_profile_checkout', [
            'page' => $page,
            'order' => $order,
            'price' => $service->price(),
            'paymentMethods' => $paymentMethods,
            'paymentMode' => $settings['payment_mode'] ?? 'manual',
        ]);
    }

    public function store(int $landingPageId): RedirectResponse
    {
        $service = new BusinessProfileAccessService();
        if (! $service->tablesReady()) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Pembayaran Business Profile belum aktif. Jalankan SQL Business Profile terlebih dahulu.');
        }

        $page = $this->payableBusinessProfilePage($landingPageId, $service);
        $settings = (new PaymentSettingModel())->getSettings();
        $paymentMethods = $this->availableManualPaymentMethods($settings);
        if ($paymentMethods === []) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['payment_method' => 'Metode pembayaran manual Business Profile belum tersedia.']);
        }

        $rules = [
            'payment_method' => 'required|in_list[' . implode(',', $paymentMethods) . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $paymentMethod = (string) $this->request->getPost('payment_method');
        $order = $service->ensurePendingOrder((int) session()->get('userId'), (int) $page['id'], $paymentMethod);
        if ($order === null) {
            return redirect()->back()->with('error', 'Invoice Business Profile gagal dibuat. Coba lagi.');
        }

        return redirect()->to(site_url('business-profile-orders/' . (int) $order['id']))
            ->with('success', 'Invoice Business Profile dibuat. Silakan upload bukti pembayaran.');
    }

    public function detail(int $id): string
    {
        $order = $this->userOrder($id);

        return view('payment/business_profile_detail', [
            'order' => $order,
        ]);
    }

    public function uploadProof(int $id): RedirectResponse
    {
        $order = $this->userOrder($id);
        if (! in_array((string) ($order['status'] ?? ''), ['pending', 'pending_payment', 'rejected'], true)) {
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

        $uploadPath = FCPATH . 'uploads/business-profile-proof';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $invoice = preg_replace('/[^A-Z0-9-]/', '', (string) ($order['invoice_number'] ?? 'AABP'));
        $fileName = $invoice . '-' . time() . '.' . $file->getExtension();
        $file->move($uploadPath, $fileName, true);

        (new BusinessProfileOrderModel())->update((int) $order['id'], [
            'payment_proof' => 'uploads/business-profile-proof/' . $fileName,
            'status' => 'waiting_approval',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('business-profile-orders/' . (int) $order['id']))
            ->with('success', 'Bukti pembayaran Business Profile berhasil diupload. Menunggu konfirmasi admin.');
    }

    private function payableBusinessProfilePage(int $landingPageId, BusinessProfileAccessService $service): array
    {
        $page = (new LandingPageModel())
            ->where('id', $landingPageId)
            ->where('user_id', (int) session()->get('userId'))
            ->first();

        if ($page === null || ! $service->isBusinessProfilePage($page)) {
            throw PageNotFoundException::forPageNotFound('Business Profile tidak ditemukan.');
        }

        return $page;
    }

    private function userOrder(int $id): array
    {
        $order = (new BusinessProfileOrderModel())->findByUser($id, (int) session()->get('userId'));
        if ($order === null) {
            throw PageNotFoundException::forPageNotFound('Invoice Business Profile tidak ditemukan.');
        }

        return $order;
    }

    private function availableManualPaymentMethods(array $settings): array
    {
        $mode = (string) ($settings['payment_mode'] ?? 'manual');
        if (! in_array($mode, ['manual', 'both', 'manual_lynk', 'all'], true)) {
            return [];
        }

        return $this->manualPaymentMethods;
    }
}
