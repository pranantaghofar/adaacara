<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceTemplateActivityLogModel extends Model
{
    protected $table = 'marketplace_template_activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'marketplace_template_id',
        'actor_id',
        'actor_role',
        'action',
        'from_status',
        'to_status',
        'note',
        'metadata',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function historyForTemplate(int $marketplaceTemplateId): array
    {
        return $this->select('marketplace_template_activity_logs.*, users.name AS actor_name')
            ->join('users', 'users.id = marketplace_template_activity_logs.actor_id', 'left')
            ->where('marketplace_template_activity_logs.marketplace_template_id', $marketplaceTemplateId)
            ->orderBy('marketplace_template_activity_logs.created_at', 'DESC')
            ->orderBy('marketplace_template_activity_logs.id', 'DESC')
            ->findAll();
    }
}
