<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateModel extends Model
{
    protected $table = 'templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'category_id',
        'project_type',
        'name',
        'slug',
        'description',
        'tags',
        'preview_url',
        'thumbnail',
        'html',
        'css',
        'js',
        'editor_json',
        'editor_type',
        'grapesjs_json',
        'is_premium',
        'status',
        'is_active',
        'owner_user_id',
        'created_by_role',
        'seller_plan_name',
        'review_status',
        'public_status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'source_invitation_id',
        'usage_count',
        'publish_count',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getActiveTemplates(): array
    {
        $builder = $this->where('templates.is_active', 1);
        $this->withCategoryFields($builder);

        if ($this->hasField('status')) {
            $builder->where('templates.status', 'active');
        }

        $this->applyPublicTemplateScope($builder);

        return $builder->orderBy('templates.name', 'ASC')->findAll();
    }

    public function getTemplateListingCards(string $search = '', ?int $subcategoryId = null, bool $newestCreatedFirst = false, string $projectType = ''): array
    {
        $select = [
            'templates.id',
            'templates.category_id',
            'templates.name',
            'templates.slug',
            'templates.description',
            'templates.thumbnail',
            'templates.is_premium',
        ];

        $fields = $this->db->getFieldNames($this->table);
        foreach (['project_type', 'preview_url', 'status', 'is_active', 'owner_user_id', 'review_status', 'public_status', 'approved_at', 'updated_at', 'tags'] as $field) {
            if (in_array($field, $fields, true)) {
                $select[] = 'templates.' . $field;
            }
        }

        $builder = $this->where('templates.is_active', 1);
        $this->withCategoryFields($builder, implode(', ', $select));

        if ($this->hasField('status')) {
            $builder->where('templates.status', 'active');
        }

        $this->applyPublicTemplateScope($builder);

        $projectType = $this->normalizeProjectType($projectType);
        if ($projectType !== '' && in_array('project_type', $fields, true)) {
            $builder->where('templates.project_type', $projectType);
        }

        if ($subcategoryId !== null && $subcategoryId > 0 && $this->db->tableExists('template_subcategory_templates')) {
            $builder
                ->join('template_subcategory_templates', 'template_subcategory_templates.template_id = templates.id', 'inner')
                ->where('template_subcategory_templates.subcategory_id', $subcategoryId);
        }

        $this->applyListingSearch($builder, $search, $fields);

        if ($newestCreatedFirst) {
            if ($this->hasField('created_at')) {
                $builder->orderBy('templates.created_at', 'DESC');
            }

            return $builder
                ->orderBy('templates.id', 'DESC')
                ->findAll();
        }

        if ($this->hasField('owner_user_id')) {
            $builder->orderBy('CASE WHEN templates.owner_user_id IS NULL THEN 1 ELSE 0 END', 'ASC', false);
        }

        if ($this->hasField('approved_at')) {
            $builder->orderBy('templates.approved_at', 'DESC');
        }

        if ($this->hasField('updated_at')) {
            $builder->orderBy('templates.updated_at', 'DESC');
        }

        return $builder
            ->orderBy('templates.name', 'ASC')
            ->findAll();
    }

    public function getHomeTemplates(int $limit = 8): array
    {
        $builder = $this->where('templates.is_active', 1);
        $this->withCategoryFields($builder);

        if ($this->hasField('status')) {
            $builder->where('templates.status', 'active');
        }

        $this->applyPublicTemplateScope($builder);

        if ($this->hasField('owner_user_id')) {
            $builder->orderBy('CASE WHEN templates.owner_user_id IS NULL THEN 1 ELSE 0 END', 'ASC', false);
        }

        if ($this->hasField('approved_at')) {
            $builder->orderBy('templates.approved_at', 'DESC');
        }

        if ($this->hasField('updated_at')) {
            $builder->orderBy('templates.updated_at', 'DESC');
        }

        return $builder
            ->orderBy('templates.name', 'ASC')
            ->findAll($limit);
    }

    public function getActiveTemplate(int $id): ?array
    {
        $builder = $this->where('templates.is_active', 1)->where('templates.id', $id);
        $this->withCategoryFields($builder);

        if ($this->hasField('status')) {
            $builder->where('templates.status', 'active');
        }

        $this->applyPublicTemplateScope($builder);

        return $builder->first();
    }

    public function getActiveTemplateBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '' || ! $this->hasField('slug')) {
            return null;
        }

        $builder = $this->where('templates.is_active', 1)->where('templates.slug', $slug);
        $this->withCategoryFields($builder);

        if ($this->hasField('status')) {
            $builder->where('templates.status', 'active');
        }

        $this->applyPublicTemplateScope($builder);

        return $builder->first();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $builder = $this->where('slug', $slug);

        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    private function hasField(string $field): bool
    {
        return in_array($field, $this->db->getFieldNames($this->table), true);
    }

    private function normalizeProjectType(string $value): string
    {
        $value = strtolower(trim($value));

        return match ($value) {
            'invitation' => 'invitation',
            'photobooth', 'digital_photobooth' => 'photobooth',
            'business_profile', 'business-profile' => 'business_profile',
            default => '',
        };
    }

    private function withCategoryFields($builder, string $templateSelect = 'templates.*'): void
    {
        $builder->select($templateSelect);

        if (! $this->hasField('category_id') || ! $this->db->tableExists('categories')) {
            return;
        }

        $categoryFields = $this->db->getFieldNames('categories');
        if (! in_array('id', $categoryFields, true)) {
            return;
        }

        $select = [];
        if (in_array('name', $categoryFields, true)) {
            $select[] = 'categories.name AS category_name';
        }
        if (in_array('slug', $categoryFields, true)) {
            $select[] = 'categories.slug AS category_slug';
        }

        if ($select === []) {
            return;
        }

        $builder
            ->select(implode(', ', $select), false)
            ->join('categories', 'categories.id = templates.category_id', 'left');
    }

    private function applyPublicTemplateScope($builder): void
    {
        if (! $this->hasField('owner_user_id')) {
            return;
        }

        if (! $this->hasField('review_status') || ! $this->hasField('public_status')) {
            $builder->where('templates.owner_user_id', null);

            return;
        }

        $builder->groupStart()
            ->where('templates.owner_user_id', null)
            ->orGroupStart()
                ->where('templates.review_status', 'approved')
                ->where('templates.public_status', 'public')
            ->groupEnd()
        ->groupEnd();
    }

    private function applyListingSearch($builder, string $search, array $templateFields): void
    {
        $terms = $this->listingSearchTerms($search);
        if ($terms === []) {
            return;
        }

        $searchFields = ['templates.name'];
        foreach (['slug', 'description', 'tags'] as $field) {
            if (in_array($field, $templateFields, true)) {
                $searchFields[] = 'templates.' . $field;
            }
        }

        if ($this->hasField('category_id') && $this->db->tableExists('categories')) {
            $categoryFields = $this->db->getFieldNames('categories');
            if (in_array('name', $categoryFields, true)) {
                $searchFields[] = 'categories.name';
            }
            if (in_array('slug', $categoryFields, true)) {
                $searchFields[] = 'categories.slug';
            }
        }

        if ($searchFields === []) {
            return;
        }

        $builder->groupStart();
        foreach ($terms as $termIndex => $term) {
            if ($termIndex === 0) {
                $builder->groupStart();
            } else {
                $builder->orGroupStart();
            }
            foreach ($searchFields as $fieldIndex => $field) {
                if ($fieldIndex === 0) {
                    $builder->like($field, $term);
                } else {
                    $builder->orLike($field, $term);
                }
            }
            $builder->groupEnd();
        }
        $builder->groupEnd();
    }

    private function listingSearchTerms(string $search): array
    {
        $search = trim(mb_strtolower(strip_tags($search)));
        if ($search === '') {
            return [];
        }

        $search = preg_replace('/\s+/', ' ', $search) ?? $search;
        $terms = [$search];
        $words = preg_split('/[\s,.;:_\-]+/', $search) ?: [];
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) >= 3) {
                $terms[] = $word;
            }
        }

        $synonyms = [
            'pernikahan' => ['nikah', 'wedding', 'akad', 'resepsi', 'married'],
            'wedding' => ['pernikahan', 'nikah', 'akad', 'resepsi'],
            'nikah' => ['pernikahan', 'wedding', 'akad', 'resepsi'],
            'ulang tahun' => ['ultah', 'birthday'],
            'ultah' => ['ulang tahun', 'birthday'],
            'birthday' => ['ulang tahun', 'ultah'],
            'aqiqah' => ['akikah', 'bayi', 'baby', 'anak'],
            'akikah' => ['aqiqah', 'bayi', 'baby', 'anak'],
            'khitan' => ['sunat', 'khitanan'],
            'sunat' => ['khitan', 'khitanan'],
            'lamaran' => ['tunangan', 'pertunangan', 'engagement'],
            'engagement' => ['lamaran', 'tunangan', 'pertunangan'],
            'bunga' => ['floral', 'botani', 'flower'],
            'floral' => ['bunga', 'botani'],
            'mewah' => ['premium', 'elegan', 'luxury'],
            'elegan' => ['premium', 'mewah', 'luxury'],
            'gold' => ['emas', 'premium', 'elegan'],
            'emas' => ['gold', 'premium', 'elegan'],
            'simple' => ['sederhana', 'minimalis', 'clean'],
            'minimalis' => ['simple', 'sederhana', 'clean'],
            'foto' => ['photo', 'gambar'],
            'ramadhan' => ['bukber', 'iftar', 'buka bersama'],
            'bukber' => ['ramadhan', 'iftar', 'buka bersama'],
        ];

        foreach ($synonyms as $key => $items) {
            if (str_contains($search, $key)) {
                array_push($terms, ...$items);
            }
        }

        return array_values(array_unique(array_slice(array_filter($terms), 0, 12)));
    }
}
