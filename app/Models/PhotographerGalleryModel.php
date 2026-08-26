<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotographerGalleryModel extends Model
{
    protected $table = 'photographer_galleries';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'title',
        'slug',
        'event_date',
        'studio_name',
        'cover_photo',
        'privacy_mode',
        'pin_hash',
        'selection_enabled',
        'selection_limit',
        'download_enabled',
        'status',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function getByUser(int $userId): array
    {
        if ($userId <= 0 || ! $this->tableReady()) {
            return [];
        }

        $photoTableReady = $this->db->tableExists('photographer_gallery_photos');
        $builder = $this->db->table($this->table . ' pg')
            ->select($photoTableReady ? 'pg.*, COUNT(p.id) AS photo_count' : 'pg.*, 0 AS photo_count', false)
            ->where('pg.user_id', $userId)
            ->groupBy('pg.id')
            ->orderBy('pg.updated_at', 'DESC')
            ->orderBy('pg.id', 'DESC');

        if ($photoTableReady) {
            $builder->join('photographer_gallery_photos p', 'p.gallery_id = pg.id AND p.status != "deleted"', 'left');
        }

        return $builder->get()->getResultArray();
    }

    public function findOwned(int $id, int $userId): ?array
    {
        if ($id <= 0 || $userId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function findPublicBySlug(string $slug): ?array
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('slug', $slug)
            ->whereIn('status', ['draft', 'active'])
            ->first();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || ! $this->tableReady()) {
            return false;
        }

        $builder = $this->where('slug', $slug);
        if ($ignoreId !== null && $ignoreId > 0) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->first() !== null;
    }
}
