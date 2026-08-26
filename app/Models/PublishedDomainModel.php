<?php

namespace App\Models;

use CodeIgniter\Model;

class PublishedDomainModel extends Model
{
    protected $table = 'published_domains';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'landing_page_id',
        'user_id',
        'subdomain',
        'root_domain',
        'full_domain',
        'type',
        'project_type',
        'status',
        'is_primary',
        'reserved_at',
        'activated_at',
        'failed_at',
        'admin_notes',
        'activation_notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function activeByHost(string $host): ?array
    {
        $host = strtolower(trim($host));
        if ($host === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('full_domain', $host)
            ->where('status', 'active')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function activationRequests(array $filters = [], int $limit = 200): array
    {
        if (! $this->tableReady()) {
            return [];
        }
        $fields = $this->existingFields();

        $builder = $this->db->table($this->table . ' pd')
            ->select('pd.*, lp.title AS page_title, lp.slug AS page_slug, u.name AS user_name, u.email AS user_email')
            ->join('landing_pages lp', 'lp.id = pd.landing_page_id', 'left')
            ->join('users u', 'u.id = pd.user_id', 'left');

        $status = (string) ($filters['status'] ?? '');
        if ($status !== '') {
            $builder->where('pd.status', $status);
        }

        $projectType = (string) ($filters['project_type'] ?? '');
        if ($projectType !== '' && in_array('project_type', $fields, true)) {
            $builder->where('pd.project_type', $projectType);
        }

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('pd.full_domain', $keyword)
                ->orLike('pd.subdomain', $keyword)
                ->orLike('lp.title', $keyword)
                ->orLike('lp.slug', $keyword)
                ->orLike('u.name', $keyword)
                ->orLike('u.email', $keyword)
                ->groupEnd();
        }

        return $builder->orderBy('pd.updated_at', 'DESC')
            ->orderBy('pd.id', 'DESC')
            ->limit(max(1, min(500, $limit)))
            ->get()
            ->getResultArray();
    }

    public function primaryForPage(int $landingPageId): ?array
    {
        if ($landingPageId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->where('landing_page_id', $landingPageId)
            ->where('is_primary', 1)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function fullDomainAvailable(string $fullDomain, int $ignoreLandingPageId = 0): bool
    {
        if (! $this->tableReady()) {
            return true;
        }

        $builder = $this->where('full_domain', strtolower(trim($fullDomain)));
        if ($ignoreLandingPageId > 0) {
            $builder->where('landing_page_id !=', $ignoreLandingPageId);
        }

        return $builder->countAllResults() === 0;
    }

    public function existingFields(): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        return $this->db->getFieldNames($this->table);
    }

    public function filterExistingFields(array $data): array
    {
        $fields = $this->existingFields();
        if ($fields === []) {
            return [];
        }

        return array_intersect_key($data, array_flip($fields));
    }
}
