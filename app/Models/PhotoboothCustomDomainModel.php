<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotoboothCustomDomainModel extends Model
{
    protected $table = 'photobooth_custom_domains';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'landing_page_id',
        'user_id',
        'domain',
        'extension',
        'target_type',
        'status',
        'availability_status',
        'payment_status',
        'price',
        'billing_period',
        'payment_proof',
        'payment_note',
        'payment_submitted_at',
        'paid_at',
        'active_until',
        'notes',
        'requested_at',
        'checked_at',
        'activated_at',
        'disabled_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function latestForPage(int $landingPageId): ?array
    {
        if ($landingPageId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->where('landing_page_id', $landingPageId)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
