<?php

namespace App\Models;

use CodeIgniter\Model;

class CreatorTemplateRoyaltyRuleModel extends Model
{
    protected $table = 'creator_template_royalty_rules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'template_id',
        'marketplace_template_id',
        'creator_user_id',
        'license_value',
        'currency',
        'creator_rate',
        'platform_rate',
        'status',
        'note',
        'metadata',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function activeForTemplate(int $templateId): ?array
    {
        if ($templateId <= 0) {
            return null;
        }

        return $this->where('template_id', $templateId)
            ->where('status', 'active')
            ->first();
    }
}
