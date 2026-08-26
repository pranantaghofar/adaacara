<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessProfileOrderModel extends Model
{
    protected $table = 'business_profile_orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'landing_page_id',
        'invoice_number',
        'amount',
        'payment_method',
        'payment_provider',
        'payment_proof',
        'status',
        'admin_note',
        'created_at',
        'paid_at',
        'updated_at',
    ];
    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function makeInvoiceNumber(): string
    {
        return 'AABP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public function findByUser(int $orderId, int $userId): ?array
    {
        if ($orderId <= 0 || $userId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->select('business_profile_orders.*, landing_pages.title AS page_title, landing_pages.slug AS page_slug', false)
            ->join('landing_pages', 'landing_pages.id = business_profile_orders.landing_page_id', 'left')
            ->where('business_profile_orders.id', $orderId)
            ->where('business_profile_orders.user_id', $userId)
            ->first();
    }

    public function latestPayableForPage(int $landingPageId, int $userId): ?array
    {
        if ($landingPageId <= 0 || $userId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->where('landing_page_id', $landingPageId)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'pending_payment', 'waiting_approval', 'rejected'])
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function getByUser(int $userId): array
    {
        if ($userId <= 0 || ! $this->tableReady()) {
            return [];
        }

        return $this->select('business_profile_orders.*, landing_pages.title AS page_title, landing_pages.slug AS page_slug', false)
            ->join('landing_pages', 'landing_pages.id = business_profile_orders.landing_page_id', 'left')
            ->where('business_profile_orders.user_id', $userId)
            ->orderBy('business_profile_orders.created_at', 'DESC')
            ->orderBy('business_profile_orders.id', 'DESC')
            ->findAll();
    }

    public function getAdminOrders(array $filters = []): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        $builder = $this->db->table($this->table . ' bpo')
            ->select('bpo.*, lp.title AS page_title, lp.slug AS page_slug, u.name AS user_name, u.email AS user_email')
            ->join('landing_pages lp', 'lp.id = bpo.landing_page_id', 'left')
            ->join('users u', 'u.id = bpo.user_id', 'left');

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $builder->where('bpo.status', $status);
        }

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('bpo.invoice_number', $keyword)
                ->orLike('lp.title', $keyword)
                ->orLike('lp.slug', $keyword)
                ->orLike('u.name', $keyword)
                ->orLike('u.email', $keyword)
                ->groupEnd();
        }

        return $builder->orderBy('bpo.created_at', 'DESC')
            ->orderBy('bpo.id', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();
    }

    public function findAdminOrder(int $orderId): ?array
    {
        if ($orderId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->db->table($this->table . ' bpo')
            ->select('bpo.*, lp.title AS page_title, lp.slug AS page_slug, u.name AS user_name, u.email AS user_email')
            ->join('landing_pages lp', 'lp.id = bpo.landing_page_id', 'left')
            ->join('users u', 'u.id = bpo.user_id', 'left')
            ->where('bpo.id', $orderId)
            ->get()
            ->getRowArray();
    }
}
