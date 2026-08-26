<?php

namespace App\Models;

use CodeIgniter\Model;

class CreatorTemplateOwnershipModel extends Model
{
    protected $table = 'creator_template_ownerships';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'template_id',
        'creator_id',
        'assigned_by',
        'ownership_type',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findForTemplate(int $templateId): ?array
    {
        return $this->where('template_id', $templateId)->first();
    }

    public function creatorOwnsTemplate(int $creatorId, int $templateId): bool
    {
        return $this->where('creator_id', $creatorId)
            ->where('template_id', $templateId)
            ->countAllResults() > 0;
    }

    public function templatesForCreator(int $creatorId): array
    {
        return $this->select('creator_template_ownerships.*, templates.name AS template_name, templates.slug AS template_slug, templates.thumbnail AS template_thumbnail, templates.description AS template_description, categories.name AS category_name, marketplace_templates.id AS marketplace_id, marketplace_templates.title AS marketplace_title, marketplace_templates.marketplace_status, marketplace_templates.approval_status, marketplace_templates.is_free, marketplace_templates.price_amount, marketplace_templates.submitted_at')
            ->join('templates', 'templates.id = creator_template_ownerships.template_id', 'inner')
            ->join('categories', 'categories.id = templates.category_id', 'left')
            ->join('marketplace_templates', 'marketplace_templates.template_id = creator_template_ownerships.template_id', 'left')
            ->where('creator_template_ownerships.creator_id', $creatorId)
            ->orderBy('creator_template_ownerships.created_at', 'DESC')
            ->findAll();
    }

    public function adminList(): array
    {
        return $this->select('creator_template_ownerships.*, templates.name AS template_name, templates.slug AS template_slug, creator_profiles.display_name AS creator_name, creator_profiles.slug AS creator_slug, users.name AS assigned_by_name')
            ->join('templates', 'templates.id = creator_template_ownerships.template_id', 'inner')
            ->join('creator_profiles', 'creator_profiles.id = creator_template_ownerships.creator_id', 'inner')
            ->join('users', 'users.id = creator_template_ownerships.assigned_by', 'left')
            ->orderBy('creator_template_ownerships.created_at', 'DESC')
            ->findAll();
    }
}
