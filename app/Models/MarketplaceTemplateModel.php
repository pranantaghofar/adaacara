<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceTemplateModel extends Model
{
    public const MARKETPLACE_STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'changes_requested', 'archived'];
    public const APPROVAL_STATUSES = ['not_submitted', 'pending', 'approved', 'rejected'];
    public const LICENSE_TYPES = ['single_use', 'multi_use', 'personal', 'commercial'];

    protected $table = 'marketplace_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'template_id',
        'creator_id',
        'title',
        'slug',
        'short_description',
        'description',
        'category',
        'tags',
        'thumbnail_url',
        'preview_url',
        'is_free',
        'price_amount',
        'price_currency',
        'license_type',
        'marketplace_status',
        'approval_status',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'archived_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $builder = $this->where('slug', $slug);

        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    public function findForCreator(int $id, int $creatorId): ?array
    {
        return $this->select('marketplace_templates.*, templates.name AS template_name, templates.slug AS template_slug, templates.thumbnail AS template_thumbnail, creator_profiles.display_name AS creator_name, creator_profiles.user_id AS creator_user_id')
            ->join('templates', 'templates.id = marketplace_templates.template_id', 'left')
            ->join('creator_profiles', 'creator_profiles.id = marketplace_templates.creator_id', 'left')
            ->where('marketplace_templates.id', $id)
            ->where('marketplace_templates.creator_id', $creatorId)
            ->first();
    }

    public function creatorList(int $creatorId): array
    {
        return $this->select('marketplace_templates.*, templates.name AS template_name, templates.slug AS template_slug, templates.thumbnail AS template_thumbnail')
            ->join('templates', 'templates.id = marketplace_templates.template_id', 'left')
            ->where('marketplace_templates.creator_id', $creatorId)
            ->orderBy('marketplace_templates.updated_at', 'DESC')
            ->orderBy('marketplace_templates.created_at', 'DESC')
            ->findAll();
    }

    public function creatorSummary(int $creatorId): array
    {
        $rows = $this->select('marketplace_status, updated_at')
            ->where('creator_id', $creatorId)
            ->findAll();

        $summary = [
            'total' => count($rows),
            'draft' => 0,
            'submitted' => 0,
            'approved' => 0,
            'rejected' => 0,
            'changes_requested' => 0,
            'archived' => 0,
            'needs_revision' => 0,
            'last_updated' => null,
        ];

        foreach ($rows as $row) {
            $status = (string) ($row['marketplace_status'] ?? 'draft');
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
            if (in_array($status, ['rejected', 'changes_requested'], true)) {
                $summary['needs_revision']++;
            }
            if (($row['updated_at'] ?? null) !== null && ($summary['last_updated'] === null || $row['updated_at'] > $summary['last_updated'])) {
                $summary['last_updated'] = $row['updated_at'];
            }
        }

        return $summary;
    }

    public function adminList(?string $status = null, ?string $search = null, ?string $category = null, ?string $priceType = null, string $sort = 'newest'): array
    {
        $builder = $this->select('marketplace_templates.*, templates.name AS template_name, templates.slug AS template_slug, creator_profiles.display_name AS creator_name, creator_profiles.slug AS creator_slug, approvers.name AS approved_by_name, rejecters.name AS rejected_by_name')
            ->join('templates', 'templates.id = marketplace_templates.template_id', 'left')
            ->join('creator_profiles', 'creator_profiles.id = marketplace_templates.creator_id', 'left')
            ->join('users AS approvers', 'approvers.id = marketplace_templates.approved_by', 'left')
            ->join('users AS rejecters', 'rejecters.id = marketplace_templates.rejected_by', 'left');

        if ($status !== null && $status !== '') {
            $builder->where('marketplace_templates.marketplace_status', $status);
        }

        if ($search !== null && trim($search) !== '') {
            $keyword = trim($search);
            $builder->groupStart()
                ->like('marketplace_templates.title', $keyword)
                ->orLike('creator_profiles.display_name', $keyword)
                ->orLike('marketplace_templates.category', $keyword)
                ->groupEnd();
        }

        if ($category !== null && trim($category) !== '') {
            $builder->where('marketplace_templates.category', trim($category));
        }

        if ($priceType === 'free') {
            $builder->where('marketplace_templates.is_free', 1);
        } elseif ($priceType === 'paid') {
            $builder->where('marketplace_templates.is_free', 0);
        }

        if ($sort === 'oldest') {
            $builder->orderBy('marketplace_templates.submitted_at', 'ASC');
        } else {
            $builder->orderBy('marketplace_templates.submitted_at', 'DESC');
        }

        return $builder
            ->orderBy('marketplace_templates.updated_at', 'DESC')
            ->orderBy('marketplace_templates.created_at', 'DESC')
            ->findAll();
    }

    public function adminFind(int $id): ?array
    {
        return $this->select('marketplace_templates.*, templates.name AS template_name, templates.slug AS template_slug, templates.thumbnail AS template_thumbnail, creator_profiles.display_name AS creator_name, creator_profiles.slug AS creator_slug, creator_profiles.user_id AS creator_user_id, approvers.name AS approved_by_name, rejecters.name AS rejected_by_name')
            ->join('templates', 'templates.id = marketplace_templates.template_id', 'left')
            ->join('creator_profiles', 'creator_profiles.id = marketplace_templates.creator_id', 'left')
            ->join('users AS approvers', 'approvers.id = marketplace_templates.approved_by', 'left')
            ->join('users AS rejecters', 'rejecters.id = marketplace_templates.rejected_by', 'left')
            ->where('marketplace_templates.id', $id)
            ->first();
    }
}
