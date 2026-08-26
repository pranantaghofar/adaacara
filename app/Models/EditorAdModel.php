<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class EditorAdModel extends Model
{
    public const TARGET_TYPES = ['all', 'free', 'member', 'creator', 'user_specific'];

    protected $table = 'editor_ads';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'image_path',
        'link_url',
        'target_type',
        'target_user_id',
        'priority',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function tableReady(): bool
    {
        return Database::connect()->tableExists($this->table);
    }

    public function activeForEditor(array $context, int $limit = 3): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        $userId = (int) ($context['user_id'] ?? 0);
        $targets = ['all'];

        if (! empty($context['is_creator'])) {
            $targets[] = 'creator';
        }

        $targets[] = ! empty($context['has_membership']) ? 'member' : 'free';

        $now = date('Y-m-d H:i:s');
        $builder = $this->where('is_active', 1)
            ->groupStart()
                ->where('starts_at', null)
                ->orWhere('starts_at <=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('ends_at', null)
                ->orWhere('ends_at >=', $now)
            ->groupEnd()
            ->groupStart()
                ->whereIn('target_type', array_values(array_unique($targets)));

        if ($userId > 0) {
            $builder->orGroupStart()
                ->where('target_type', 'user_specific')
                ->where('target_user_id', $userId)
            ->groupEnd();
        }

        return $builder
            ->groupEnd()
            ->orderBy('priority', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll($limit);
    }

    public function activeCount(): int
    {
        if (! $this->tableReady()) {
            return 0;
        }

        return $this->where('is_active', 1)->countAllResults();
    }
}
