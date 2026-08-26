<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateSubcategoryModel extends Model
{
    protected $table = 'template_subcategories';
    private string $assignmentTable = 'template_subcategory_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'category_id',
        'name',
        'slug',
        'group_title',
        'search_keywords',
        'sort_order',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function assignmentTableReady(): bool
    {
        return $this->db->tableExists($this->assignmentTable);
    }

    public function withCategoryList(): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        $builder = $this->builder()
            ->select('template_subcategories.*, categories.name AS category_name, categories.slug AS category_slug')
            ->join('categories', 'categories.id = template_subcategories.category_id', 'left')
            ->orderBy('categories.sort_order', 'ASC')
            ->orderBy('template_subcategories.sort_order', 'ASC')
            ->orderBy('template_subcategories.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function activeWithCategoryList(): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        return $this->builder()
            ->select('template_subcategories.*, categories.name AS category_name, categories.slug AS category_slug')
            ->join('categories', 'categories.id = template_subcategories.category_id', 'left')
            ->where('template_subcategories.is_active', 1)
            ->orderBy('categories.sort_order', 'ASC')
            ->orderBy('template_subcategories.sort_order', 'ASC')
            ->orderBy('template_subcategories.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function activeGroupedByCategorySlug(): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        $rows = $this->builder()
            ->select('template_subcategories.*, categories.name AS category_name, categories.slug AS category_slug')
            ->join('categories', 'categories.id = template_subcategories.category_id', 'left')
            ->where('template_subcategories.is_active', 1)
            ->groupStart()
                ->where('categories.is_active', 1)
                ->orWhere('categories.is_active', null)
            ->groupEnd()
            ->orderBy('categories.sort_order', 'ASC')
            ->orderBy('template_subcategories.sort_order', 'ASC')
            ->orderBy('template_subcategories.name', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($rows as $row) {
            $categorySlug = trim((string) ($row['category_slug'] ?? ''));
            if ($categorySlug === '') {
                continue;
            }

            $grouped[$categorySlug][] = $row;
        }

        return $grouped;
    }

    public function activeGroupedByCategorySlugWithTemplates(string $projectType = 'invitation'): array
    {
        if (! $this->tableReady() || ! $this->assignmentTableReady() || ! $this->db->tableExists('templates')) {
            return $this->activeGroupedByCategorySlug();
        }

        $templateFields = $this->db->getFieldNames('templates');
        $builder = $this->builder()
            ->select('template_subcategories.*, categories.name AS category_name, categories.slug AS category_slug, COUNT(DISTINCT templates.id) AS template_count', false)
            ->join('categories', 'categories.id = template_subcategories.category_id', 'left')
            ->join($this->assignmentTable, $this->assignmentTable . '.subcategory_id = template_subcategories.id', 'inner')
            ->join('templates', 'templates.id = ' . $this->assignmentTable . '.template_id', 'inner')
            ->where('template_subcategories.is_active', 1)
            ->where('templates.is_active', 1)
            ->groupStart()
                ->where('categories.is_active', 1)
                ->orWhere('categories.is_active', null)
            ->groupEnd();

        if (in_array('status', $templateFields, true)) {
            $builder->where('templates.status', 'active');
        }

        if (in_array('project_type', $templateFields, true)) {
            $builder->where('templates.project_type', $this->normalizeTemplateProjectType($projectType));
        }

        if (in_array('owner_user_id', $templateFields, true)) {
            if (in_array('review_status', $templateFields, true) && in_array('public_status', $templateFields, true)) {
                $builder->groupStart()
                    ->where('templates.owner_user_id', null)
                    ->orGroupStart()
                        ->where('templates.review_status', 'approved')
                        ->where('templates.public_status', 'public')
                    ->groupEnd()
                ->groupEnd();
            } else {
                $builder->where('templates.owner_user_id', null);
            }
        }

        $rows = $builder
            ->groupBy('template_subcategories.id')
            ->having('template_count >', 0)
            ->orderBy('categories.sort_order', 'ASC')
            ->orderBy('template_subcategories.sort_order', 'ASC')
            ->orderBy('template_subcategories.name', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($rows as $row) {
            $categorySlug = trim((string) ($row['category_slug'] ?? ''));
            if ($categorySlug === '') {
                continue;
            }

            $grouped[$categorySlug][] = $row;
        }

        return $grouped;
    }

    private function normalizeTemplateProjectType(string $projectType): string
    {
        $projectType = strtolower(trim($projectType));

        return match ($projectType) {
            'photobooth', 'digital_photobooth' => 'photobooth',
            'business_profile', 'business-profile' => 'business_profile',
            default => 'invitation',
        };
    }

    public function findActiveBySlug(string $slug): ?array
    {
        $slug = trim(strtolower($slug));
        if ($slug === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->builder()
            ->select('template_subcategories.*, categories.name AS category_name, categories.slug AS category_slug')
            ->join('categories', 'categories.id = template_subcategories.category_id', 'left')
            ->where('template_subcategories.slug', $slug)
            ->where('template_subcategories.is_active', 1)
            ->get()
            ->getRowArray() ?: null;
    }

    public function selectedIdsForTemplate(int $templateId): array
    {
        if ($templateId <= 0 || ! $this->assignmentTableReady()) {
            return [];
        }

        $rows = $this->db->table($this->assignmentTable)
            ->select('subcategory_id')
            ->where('template_id', $templateId)
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): int => (int) ($row['subcategory_id'] ?? 0), $rows);
    }

    public function syncTemplateAssignments(int $templateId, array $subcategoryIds): void
    {
        if ($templateId <= 0 || ! $this->assignmentTableReady() || ! $this->tableReady()) {
            return;
        }

        $subcategoryIds = array_values(array_unique(array_filter(array_map('intval', $subcategoryIds), static fn (int $id): bool => $id > 0)));
        if ($subcategoryIds !== []) {
            $validRows = $this->builder()
                ->select('id')
                ->whereIn('id', $subcategoryIds)
                ->where('is_active', 1)
                ->get()
                ->getResultArray();
            $subcategoryIds = array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $validRows);
        }

        $this->db->table($this->assignmentTable)
            ->where('template_id', $templateId)
            ->delete();

        if ($subcategoryIds === []) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $rows = array_map(static fn (int $subcategoryId): array => [
            'template_id' => $templateId,
            'subcategory_id' => $subcategoryId,
            'created_at' => $now,
        ], $subcategoryIds);

        $this->db->table($this->assignmentTable)->insertBatch($rows);
    }
}
