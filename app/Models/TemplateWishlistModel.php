<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateWishlistModel extends Model
{
    protected $table = 'template_wishlists';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'template_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function templateIdsForUser(int $userId): array
    {
        if ($userId <= 0 || ! $this->tableReady()) {
            return [];
        }

        $rows = $this->select('template_id')
            ->where('user_id', $userId)
            ->findAll();

        return array_values(array_map(static fn (array $row): int => (int) ($row['template_id'] ?? 0), $rows));
    }

    public function latestTemplatesForUser(int $userId, int $limit = 8): array
    {
        if ($userId <= 0 || ! $this->tableReady()) {
            return [];
        }

        return $this->db->table($this->table)
            ->select('template_wishlists.template_id, template_wishlists.created_at AS wished_at, templates.name, templates.slug, templates.thumbnail, templates.is_premium')
            ->join('templates', 'templates.id = template_wishlists.template_id', 'inner')
            ->where('template_wishlists.user_id', $userId)
            ->where('templates.is_active', 1)
            ->orderBy('template_wishlists.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function toggle(int $userId, int $templateId): ?bool
    {
        if ($userId <= 0 || $templateId <= 0 || ! $this->tableReady()) {
            return null;
        }

        $existing = $this->where('user_id', $userId)
            ->where('template_id', $templateId)
            ->first();

        if ($existing !== null) {
            $this->delete((int) ($existing['id'] ?? 0));
            return false;
        }

        $this->insert([
            'user_id' => $userId,
            'template_id' => $templateId,
        ]);

        return true;
    }
}
