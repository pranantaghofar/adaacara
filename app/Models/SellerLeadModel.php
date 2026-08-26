<?php

namespace App\Models;

use CodeIgniter\Model;

class SellerLeadModel extends Model
{
    protected $table = 'seller_leads';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'customer_name',
        'whatsapp',
        'event_type',
        'event_date',
        'package_name',
        'budget',
        'status',
        'source',
        'notes',
        'last_follow_up_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public const STATUSES = [
        'new' => 'Lead Baru',
        'contacted' => 'Dihubungi',
        'negotiation' => 'Nego',
        'deal' => 'Deal',
        'production' => 'Produksi',
        'done' => 'Selesai',
        'cancelled' => 'Batal',
    ];

    public function forSeller(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function findForSeller(int $id, int $userId): ?array
    {
        return $this->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }
}
