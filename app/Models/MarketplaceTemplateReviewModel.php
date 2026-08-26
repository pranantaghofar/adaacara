<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceTemplateReviewModel extends Model
{
    protected $table = 'marketplace_template_reviews';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'marketplace_template_id',
        'reviewer_id',
        'status',
        'checklist',
        'admin_notes',
        'rejection_reason',
        'creator_message',
        'reviewed_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function historyForTemplate(int $marketplaceTemplateId): array
    {
        return $this->select('marketplace_template_reviews.*, users.name AS reviewer_name')
            ->join('users', 'users.id = marketplace_template_reviews.reviewer_id', 'left')
            ->where('marketplace_template_reviews.marketplace_template_id', $marketplaceTemplateId)
            ->orderBy('marketplace_template_reviews.created_at', 'DESC')
            ->orderBy('marketplace_template_reviews.id', 'DESC')
            ->findAll();
    }
}
