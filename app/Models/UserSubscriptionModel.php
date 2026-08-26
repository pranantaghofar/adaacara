<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSubscriptionModel extends Model
{
    protected $table = 'user_subscriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'plan_id',
        'order_id',
        'started_at',
        'expired_at',
        'status',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function activeWithPlanByUser(int $userId): ?array
    {
        $unlimitedSelect = $this->db->fieldExists('is_unlimited_pages', 'plans')
            ? 'plans.is_unlimited_pages'
            : '0 AS is_unlimited_pages';
        $lifetimeSelect = $this->db->fieldExists('is_lifetime', 'plans')
            ? 'plans.is_lifetime'
            : '0 AS is_lifetime';

        return $this->select('user_subscriptions.*, plans.name AS plan_name, plans.slug AS plan_slug, plans.max_pages, ' . $unlimitedSelect . ', plans.active_days, ' . $lifetimeSelect, false)
            ->join('plans', 'plans.id = user_subscriptions.plan_id')
            ->where('user_subscriptions.user_id', $userId)
            ->where('user_subscriptions.status', 'active')
            ->where('user_subscriptions.expired_at >=', date('Y-m-d H:i:s'))
            ->orderBy('user_subscriptions.started_at', 'DESC')
            ->orderBy('user_subscriptions.id', 'DESC')
            ->first();
    }
}
