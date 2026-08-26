<?php

namespace App\Controllers;

use App\Models\PhotoboothCustomDomainModel;
use App\Models\PhotoboothCustomDomainOrderModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AdminPhotoboothDomainController extends BaseController
{
    private const STATUS_OPTIONS = [
        'checking',
        'available',
        'unavailable',
        'waiting_payment',
        'waiting_activation',
        'active',
        'disabled',
    ];

    private const AVAILABILITY_OPTIONS = [
        'checking',
        'available',
        'unavailable',
        'manual_review',
    ];

    private const PAYMENT_OPTIONS = [
        'unpaid',
        'waiting_confirmation',
        'paid',
        'expired',
        'refunded',
    ];

    public function index(): ResponseInterface|string
    {
        helper('admin_permission');
        if ($access = admin_require('admin.photobooth_domains.view', 'photobooth-domains')) {
            return $access;
        }

        $items = [];
        $isReady = $this->tableReady();
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $paymentStatus = trim((string) ($this->request->getGet('payment_status') ?? ''));
        $keyword = trim((string) ($this->request->getGet('q') ?? ''));

        if ($isReady) {
            $builder = db_connect()->table('photobooth_custom_domains pcd')
                ->select('pcd.*, lp.title AS page_title, lp.slug AS page_slug, u.name AS user_name, u.email AS user_email')
                ->join('landing_pages lp', 'lp.id = pcd.landing_page_id', 'left')
                ->join('users u', 'u.id = pcd.user_id', 'left');

            if (in_array($status, self::STATUS_OPTIONS, true)) {
                $builder->where('pcd.status', $status);
            }
            if (in_array($paymentStatus, self::PAYMENT_OPTIONS, true)) {
                $builder->where('pcd.payment_status', $paymentStatus);
            }
            if ($keyword !== '') {
                $builder->groupStart()
                    ->like('pcd.domain', $keyword)
                    ->orLike('lp.title', $keyword)
                    ->orLike('lp.slug', $keyword)
                    ->orLike('u.name', $keyword)
                    ->orLike('u.email', $keyword)
                    ->groupEnd();
            }

            $items = $builder->orderBy('pcd.created_at', 'DESC')
                ->orderBy('pcd.id', 'DESC')
                ->limit(200)
                ->get()
                ->getResultArray();
        }

        return view('admin/photobooth_domains/index', [
            'adminTitle' => 'Domain Photobooth',
            'adminKicker' => 'Photobooth',
            'adminIcon' => 'globe',
            'adminActive' => 'photoboothDomains',
            'items' => $items,
            'isReady' => $isReady,
            'statusOptions' => self::STATUS_OPTIONS,
            'availabilityOptions' => self::AVAILABILITY_OPTIONS,
            'paymentOptions' => self::PAYMENT_OPTIONS,
            'filters' => [
                'status' => $status,
                'payment_status' => $paymentStatus,
                'q' => $keyword,
            ],
        ]);
    }

    public function update(int $id): RedirectResponse|ResponseInterface
    {
        helper('admin_permission');
        if ($access = admin_require('admin.photobooth_domains.manage', 'photobooth-domains')) {
            return $access;
        }

        if (! $this->tableReady()) {
            return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Tabel custom domain Photobooth belum tersedia.');
        }

        $status = trim((string) $this->request->getPost('status'));
        $availability = trim((string) $this->request->getPost('availability_status'));
        $paymentStatus = trim((string) $this->request->getPost('payment_status'));
        $activeUntil = trim((string) $this->request->getPost('active_until'));
        $notes = trim((string) $this->request->getPost('notes'));

        if (! in_array($status, self::STATUS_OPTIONS, true)
            || ! in_array($availability, self::AVAILABILITY_OPTIONS, true)
            || ! in_array($paymentStatus, self::PAYMENT_OPTIONS, true)
        ) {
            return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Status domain belum valid.');
        }

        if ($activeUntil !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $activeUntil)) {
            return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Tanggal aktif sampai harus format YYYY-MM-DD.');
        }

        try {
            $model = new PhotoboothCustomDomainModel();
            $existing = $model->find(max(0, $id));
            if ($existing === null) {
                return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Request domain tidak ditemukan.');
            }

            $now = date('Y-m-d H:i:s');
            $payload = [
                'status' => $status,
                'availability_status' => $availability,
                'payment_status' => $paymentStatus,
                'active_until' => $activeUntil !== '' ? $activeUntil : null,
                'notes' => $notes !== '' ? mb_substr($notes, 0, 500) : null,
            ];

            if (in_array($availability, ['available', 'unavailable', 'manual_review'], true)) {
                $payload['checked_at'] = $now;
            }
            if ($status === 'active') {
                $payload['activated_at'] = $now;
                $payload['disabled_at'] = null;
            }
            if ($status === 'disabled') {
                $payload['disabled_at'] = $now;
            }

            $model->update((int) $existing['id'], $payload);

            return redirect()->to(site_url('admin/photobooth-domains'))->with('success', 'Status custom domain Photobooth diperbarui.');
        } catch (Throwable $exception) {
            log_message('error', 'Admin Photobooth Domain update failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Status custom domain belum berhasil diperbarui.');
        }
    }

    public function quickUpdate(int $id, string $action): RedirectResponse|ResponseInterface
    {
        helper('admin_permission');
        if ($access = admin_require('admin.photobooth_domains.manage', 'photobooth-domains')) {
            return $access;
        }

        if (! $this->tableReady()) {
            return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Tabel custom domain Photobooth belum tersedia.');
        }

        $action = strtolower(trim($action));
        $now = date('Y-m-d H:i:s');
        $quickPayloads = [
            'checking' => [
                'payload' => [
                    'status' => 'checking',
                    'availability_status' => 'checking',
                    'payment_status' => 'unpaid',
                    'notes' => 'Nama domain yang dipilih akan dicek ketersediaannya oleh admin. Setelah tersedia dan pembayaran dikonfirmasi, domain akan disiapkan dan dihubungkan ke Photobooth.',
                    'checked_at' => null,
                ],
                'message' => 'Domain ditandai sedang dicek.',
            ],
            'available' => [
                'payload' => [
                    'status' => 'available',
                    'availability_status' => 'available',
                    'payment_status' => 'unpaid',
                    'notes' => 'Domain tersedia. Lanjutkan ke instruksi pembayaran add-on domain Photobooth.',
                    'checked_at' => $now,
                ],
                'message' => 'Domain ditandai tersedia.',
            ],
            'unavailable' => [
                'payload' => [
                    'status' => 'unavailable',
                    'availability_status' => 'unavailable',
                    'payment_status' => 'unpaid',
                    'notes' => 'Domain tidak tersedia. Silakan ajukan nama domain lain.',
                    'checked_at' => $now,
                ],
                'message' => 'Domain ditandai tidak tersedia.',
            ],
            'waiting-payment' => [
                'payload' => [
                    'status' => 'waiting_payment',
                    'availability_status' => 'available',
                    'payment_status' => 'unpaid',
                    'notes' => 'Domain tersedia. Menunggu upload bukti pembayaran add-on domain Photobooth.',
                    'checked_at' => $now,
                ],
                'message' => 'Domain ditandai menunggu upload bukti pembayaran.',
            ],
            'paid' => [
                'payload' => [
                    'status' => 'waiting_activation',
                    'availability_status' => 'available',
                    'payment_status' => 'paid',
                    'notes' => 'Pembayaran add-on domain sudah dikonfirmasi. Menunggu aktivasi DNS/SSL.',
                    'checked_at' => $now,
                    'paid_at' => $now,
                ],
                'message' => 'Pembayaran domain dikonfirmasi.',
            ],
        ];

        if (! isset($quickPayloads[$action])) {
            return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Aksi cepat domain belum valid.');
        }

        try {
            $model = new PhotoboothCustomDomainModel();
            $existing = $model->find(max(0, $id));
            if ($existing === null) {
                return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Request domain tidak ditemukan.');
            }

            $fields = db_connect()->getFieldNames('photobooth_custom_domains');
            $payload = array_intersect_key($quickPayloads[$action]['payload'], array_flip($fields));
            $model->update((int) $existing['id'], $payload);
            if ($action === 'paid') {
                $this->markLatestPaymentOrderPaid((int) $existing['id'], $now);
            }

            return redirect()->to(site_url('admin/photobooth-domains'))->with('success', $quickPayloads[$action]['message']);
        } catch (Throwable $exception) {
            log_message('error', 'Admin Photobooth Domain quick update failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to(site_url('admin/photobooth-domains'))->with('error', 'Aksi cepat domain belum berhasil dijalankan.');
        }
    }

    private function markLatestPaymentOrderPaid(int $domainRequestId, string $paidAt): void
    {
        try {
            $orderModel = new PhotoboothCustomDomainOrderModel();
            if (! $orderModel->tableReady()) {
                return;
            }

            $order = $orderModel->where('photobooth_custom_domain_id', $domainRequestId)
                ->whereIn('status', ['pending', 'pending_payment', 'waiting_approval', 'rejected'])
                ->orderBy('id', 'DESC')
                ->first();
            if ($order === null) {
                return;
            }

            $orderModel->update((int) $order['id'], [
                'status' => 'paid',
                'paid_at' => $paidAt,
                'updated_at' => $paidAt,
                'admin_note' => 'Pembayaran dikonfirmasi dari halaman admin Domain Photobooth.',
            ]);
        } catch (Throwable $exception) {
            log_message('warning', 'Photobooth domain order paid sync skipped: {message}', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function tableReady(): bool
    {
        try {
            return db_connect()->tableExists('photobooth_custom_domains');
        } catch (Throwable) {
            return false;
        }
    }
}
