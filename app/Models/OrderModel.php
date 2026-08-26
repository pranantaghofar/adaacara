<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'plan_id',
        'invoice_number',
        'amount',
        'payment_method',
        'payment_provider',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_token',
        'midtrans_redirect_url',
        'midtrans_status',
        'midtrans_payload',
        'lynk_ref_id',
        'lynk_message_id',
        'lynk_status',
        'lynk_payload',
        'lynk_payment_url',
        'lynk_match_note',
        'payment_proof',
        'status',
        'admin_note',
        'created_at',
        'paid_at',
    ];

    protected $useTimestamps = false;

    private function planLifetimeSelect(): string
    {
        return $this->db->fieldExists('is_lifetime', 'plans')
            ? 'plans.is_lifetime'
            : '0 AS is_lifetime';
    }

    private function planProductTypeSelect(): string
    {
        return $this->db->fieldExists('product_type', 'plans')
            ? 'plans.product_type'
            : "'membership' AS product_type";
    }

    public function findByUser(int $orderId, int $userId): ?array
    {
        $unlimitedSelect = $this->db->fieldExists('is_unlimited_pages', 'plans')
            ? 'plans.is_unlimited_pages'
            : '0 AS is_unlimited_pages';
        $lifetimeSelect = $this->planLifetimeSelect();

        return $this->select('orders.*, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, plans.max_pages, ' . $unlimitedSelect . ', ' . $lifetimeSelect, false)
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.id', $orderId)
            ->where('orders.user_id', $userId)
            ->first();
    }

    public function getByUser(int $userId): array
    {
        return $this->select('orders.*, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect(), false)
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.user_id', $userId)
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }

    public function getAdminOrders(array $filters = []): array
    {
        $builder = $this->select('orders.*, users.name AS user_name, users.email AS user_email, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('plans', 'plans.id = orders.plan_id', 'left');

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('orders.invoice_number', $search)
                ->orLike('users.name', $search)
                ->orLike('users.email', $search)
                ->orLike('plans.name', $search)
                ->groupEnd();
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $builder->where('orders.status', $status);
        }

        $method = trim((string) ($filters['method'] ?? ''));
        if ($method !== '') {
            $builder->where('orders.payment_method', $method);
        }

        $plan = trim((string) ($filters['plan'] ?? ''));
        if ($plan !== '') {
            $builder->where('plans.slug', $plan);
        }

        return $builder
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }

    public function findAdminOrder(int $orderId): ?array
    {
        return $this->select('orders.*, users.name AS user_name, users.email AS user_email, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.id', $orderId)
            ->first();
    }

    public function findByMidtransOrderId(string $midtransOrderId): ?array
    {
        return $this->select('orders.*, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.midtrans_order_id', $midtransOrderId)
            ->first();
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?array
    {
        return $this->select('orders.*, users.email AS user_email, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.invoice_number', $invoiceNumber)
            ->first();
    }

    public function findByLynkRefId(string $refId): ?array
    {
        return $this->select('orders.*, users.email AS user_email, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.lynk_ref_id', $refId)
            ->first();
    }

    public function findByLynkMessageId(string $messageId): ?array
    {
        return $this->select('orders.*, users.email AS user_email, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.lynk_message_id', $messageId)
            ->first();
    }

    public function findPendingLynkCandidatesByEmail(string $email, int $createdWithinHours = 24): array
    {
        return $this->select('orders.*, users.email AS user_email, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('users.email', $email)
            ->whereIn('orders.status', ['pending', 'pending_payment'])
            ->where('orders.payment_method', 'Lynk')
            ->where('orders.created_at >=', date('Y-m-d H:i:s', strtotime('-' . max(1, $createdWithinHours) . ' hours')))
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }

    public function findRecentPendingLynkCandidatesByAmount(int $amount, int $createdWithinMinutes = 5): array
    {
        return $this->select('orders.*, users.email AS user_email, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.amount', $amount)
            ->whereIn('orders.status', ['pending', 'pending_payment'])
            ->where('orders.payment_method', 'Lynk')
            ->where('orders.created_at >=', date('Y-m-d H:i:s', strtotime('-' . max(1, $createdWithinMinutes) . ' minutes')))
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }

    public function findPendingLynkCandidatesByAmountNearPaymentTime(int $amount, string $paidAtUtc, int $lookbackMinutes = 90): array
    {
        $paidAt = strtotime($paidAtUtc);
        if ($paidAt === false) {
            return [];
        }

        $createdFrom = date('Y-m-d H:i:s', strtotime('-' . max(1, $lookbackMinutes) . ' minutes', $paidAt));
        $createdUntil = date('Y-m-d H:i:s', strtotime('+5 minutes', $paidAt));

        return $this->select('orders.*, users.email AS user_email, plans.name AS plan_name, plans.slug AS plan_slug, ' . $this->planProductTypeSelect() . ', plans.active_days, ' . $this->planLifetimeSelect(), false)
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.amount', $amount)
            ->whereIn('orders.status', ['pending', 'pending_payment'])
            ->where('orders.payment_method', 'Lynk')
            ->where('orders.created_at >=', $createdFrom)
            ->where('orders.created_at <=', $createdUntil)
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }

    public function findOrderDiagnosticsByAmountNearPaymentTime(int $amount, string $paidAtUtc, int $lookbackMinutes = 90): array
    {
        $paidAt = strtotime($paidAtUtc);
        if ($paidAt === false) {
            return [];
        }

        $createdFrom = date('Y-m-d H:i:s', strtotime('-' . max(1, $lookbackMinutes) . ' minutes', $paidAt));
        $createdUntil = date('Y-m-d H:i:s', strtotime('+5 minutes', $paidAt));

        return $this->select('orders.id, orders.invoice_number, orders.amount, orders.payment_method, orders.status, orders.created_at')
            ->where('orders.amount', $amount)
            ->where('orders.created_at >=', $createdFrom)
            ->where('orders.created_at <=', $createdUntil)
            ->orderBy('orders.created_at', 'DESC')
            ->limit(5)
            ->findAll();
    }

    public function makeInvoiceNumber(): string
    {
        return 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
