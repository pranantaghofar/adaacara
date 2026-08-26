<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductEntitlementModel extends Model
{
    protected $table = 'product_entitlements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'order_id',
        'plan_id',
        'product_type',
        'status',
        'starts_at',
        'expires_at',
        'is_lifetime',
        'quantity_total',
        'quantity_used',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function activeForUser(int $userId, string $productType): ?array
    {
        if ($userId <= 0 || trim($productType) === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('user_id', $userId)
            ->where('product_type', $productType)
            ->where('status', 'active')
            ->groupStart()
                ->where('is_lifetime', 1)
                ->orWhere('expires_at IS NULL', null, false)
                ->orWhere('expires_at >=', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->orderBy('starts_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function availableCreditForUser(int $userId, string $productType): ?array
    {
        if ($userId <= 0 || trim($productType) === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('user_id', $userId)
            ->where('product_type', $productType)
            ->where('status', 'active')
            ->groupStart()
                ->where('is_lifetime', 1)
                ->orWhere('expires_at IS NULL', null, false)
                ->orWhere('expires_at >=', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->groupStart()
                ->where('quantity_total IS NULL', null, false)
                ->orWhere('quantity_used < quantity_total', null, false)
            ->groupEnd()
            ->orderBy('starts_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();
    }
}
