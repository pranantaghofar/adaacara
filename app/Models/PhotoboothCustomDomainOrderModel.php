<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotoboothCustomDomainOrderModel extends Model
{
    protected $table = 'photobooth_custom_domain_orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'photobooth_custom_domain_id',
        'landing_page_id',
        'user_id',
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
        'updated_at',
    ];

    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function makeInvoiceNumber(): string
    {
        return 'AADOM-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public function findByUser(int $orderId, int $userId): ?array
    {
        if ($orderId <= 0 || $userId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->select('photobooth_custom_domain_orders.*, photobooth_custom_domains.domain, landing_pages.title AS page_title, landing_pages.slug AS page_slug', false)
            ->join('photobooth_custom_domains', 'photobooth_custom_domains.id = photobooth_custom_domain_orders.photobooth_custom_domain_id', 'left')
            ->join('landing_pages', 'landing_pages.id = photobooth_custom_domain_orders.landing_page_id', 'left')
            ->where('photobooth_custom_domain_orders.id', $orderId)
            ->where('photobooth_custom_domain_orders.user_id', $userId)
            ->first();
    }

    public function latestPayableForDomain(int $domainId, int $userId): ?array
    {
        if ($domainId <= 0 || $userId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->where('photobooth_custom_domain_id', $domainId)
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

        return $this->select('photobooth_custom_domain_orders.*, photobooth_custom_domains.domain, landing_pages.title AS page_title, landing_pages.slug AS page_slug', false)
            ->join('photobooth_custom_domains', 'photobooth_custom_domains.id = photobooth_custom_domain_orders.photobooth_custom_domain_id', 'left')
            ->join('landing_pages', 'landing_pages.id = photobooth_custom_domain_orders.landing_page_id', 'left')
            ->where('photobooth_custom_domain_orders.user_id', $userId)
            ->orderBy('photobooth_custom_domain_orders.created_at', 'DESC')
            ->orderBy('photobooth_custom_domain_orders.id', 'DESC')
            ->findAll();
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?array
    {
        $invoiceNumber = strtoupper(trim($invoiceNumber));
        if ($invoiceNumber === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('invoice_number', $invoiceNumber)->first();
    }

    public function findByMidtransOrderId(string $midtransOrderId): ?array
    {
        $midtransOrderId = trim($midtransOrderId);
        if ($midtransOrderId === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('midtrans_order_id', $midtransOrderId)->first();
    }

    public function findByLynkRefId(string $refId): ?array
    {
        $refId = trim($refId);
        if ($refId === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('lynk_ref_id', $refId)->first();
    }

    public function findByLynkMessageId(string $messageId): ?array
    {
        $messageId = trim($messageId);
        if ($messageId === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('lynk_message_id', $messageId)->first();
    }
}
