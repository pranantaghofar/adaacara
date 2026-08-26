<?php

namespace App\Controllers;

use App\Libraries\BusinessProfileAccessService;
use App\Models\BusinessProfileOrderModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AdminBusinessProfileOrderController extends BaseController
{
    private const STATUS_OPTIONS = [
        'pending',
        'pending_payment',
        'waiting_approval',
        'paid',
        'rejected',
        'failed',
        'expired',
    ];

    public function index(): ResponseInterface|string
    {
        helper('admin_permission');
        if ($access = admin_require('admin.orders.view', 'orders')) {
            return $access;
        }

        $model = new BusinessProfileOrderModel();
        $filters = [
            'status' => trim((string) ($this->request->getGet('status') ?? '')),
            'q' => trim((string) ($this->request->getGet('q') ?? '')),
        ];

        if ($filters['status'] !== '' && ! in_array($filters['status'], self::STATUS_OPTIONS, true)) {
            $filters['status'] = '';
        }

        return view('admin/business_profile_orders/index', [
            'adminTitle' => 'Order Business Profile',
            'adminKicker' => 'Business Profile',
            'adminIcon' => 'briefcase',
            'adminActive' => 'businessProfileOrders',
            'items' => $model->getAdminOrders($filters),
            'isReady' => $model->tableReady() && (new BusinessProfileAccessService())->tablesReady(),
            'statusOptions' => self::STATUS_OPTIONS,
            'filters' => $filters,
        ]);
    }

    public function quickUpdate(int $id, string $action): RedirectResponse|ResponseInterface
    {
        helper('admin_permission');
        if ($access = admin_require('admin.orders.approve', 'orders')) {
            return $access;
        }

        $service = new BusinessProfileAccessService();
        if (! $service->tablesReady()) {
            return redirect()->to(site_url('admin/business-profile-orders'))
                ->with('error', 'Tabel Business Profile order belum tersedia.');
        }

        $model = new BusinessProfileOrderModel();
        $order = $model->findAdminOrder($id);
        if ($order === null) {
            return redirect()->to(site_url('admin/business-profile-orders'))
                ->with('error', 'Invoice Business Profile tidak ditemukan.');
        }

        $now = date('Y-m-d H:i:s');
        $action = strtolower(trim($action));

        try {
            if ($action === 'paid') {
                $model->update((int) $order['id'], [
                    'status' => 'paid',
                    'paid_at' => $now,
                    'updated_at' => $now,
                ]);
                $service->activatePaidOrder((int) $order['id']);

                return redirect()->to(site_url('admin/business-profile-orders'))
                    ->with('success', 'Pembayaran dikonfirmasi dan website Business Profile diaktifkan.');
            }

            if ($action === 'rejected') {
                $model->update((int) $order['id'], [
                    'status' => 'rejected',
                    'updated_at' => $now,
                ]);

                return redirect()->to(site_url('admin/business-profile-orders'))
                    ->with('success', 'Invoice Business Profile ditolak.');
            }

            if (in_array($action, ['pending', 'pending-payment'], true)) {
                $model->update((int) $order['id'], [
                    'status' => $action === 'pending-payment' ? 'pending_payment' : 'pending',
                    'updated_at' => $now,
                ]);

                return redirect()->to(site_url('admin/business-profile-orders'))
                    ->with('success', 'Status invoice Business Profile diperbarui.');
            }
        } catch (Throwable $exception) {
            log_message('error', 'Business Profile order quick update failed: {message}', ['message' => $exception->getMessage()]);
        }

        return redirect()->to(site_url('admin/business-profile-orders'))
            ->with('error', 'Aksi invoice Business Profile tidak valid.');
    }
}
