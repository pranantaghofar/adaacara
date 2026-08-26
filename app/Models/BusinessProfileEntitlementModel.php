<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessProfileEntitlementModel extends Model
{
    protected $table = 'business_profile_entitlements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'landing_page_id',
        'order_id',
        'status',
        'is_lifetime',
        'activated_at',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function activeForPage(int $landingPageId, ?int $userId = null): ?array
    {
        if ($landingPageId <= 0 || ! $this->tableReady()) {
            return null;
        }

        $builder = $this->where('landing_page_id', $landingPageId)
            ->where('status', 'active');

        if ($userId !== null && $userId > 0) {
            $builder->where('user_id', $userId);
        }

        return $builder->orderBy('id', 'DESC')->first();
    }
}
