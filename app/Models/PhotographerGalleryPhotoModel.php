<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotographerGalleryPhotoModel extends Model
{
    public const ADMIN_STATUSES = ['uploaded', 'hidden', 'selected', 'delivered'];
    public const PUBLIC_STATUSES = ['visible', 'uploaded', 'selected', 'delivered'];

    protected $table = 'photographer_gallery_photos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'gallery_id',
        'user_id',
        'album_id',
        'file_path',
        'thumb_path',
        'original_name',
        'file_size',
        'mime_type',
        'sort_order',
        'status',
        'uploaded_at',
        'updated_at',
    ];
    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function visibleForGallery(int $galleryId, int $limit = 240): array
    {
        if ($galleryId <= 0 || ! $this->tableReady()) {
            return [];
        }

        return $this->where('gallery_id', $galleryId)
            ->whereIn('status', self::PUBLIC_STATUSES)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->limit(max(1, min(500, $limit)))
            ->findAll();
    }

    public function adminForGallery(int $galleryId, int $limit = 240): array
    {
        if ($galleryId <= 0 || ! $this->tableReady()) {
            return [];
        }

        return $this->where('gallery_id', $galleryId)
            ->where('status !=', 'deleted')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->limit(max(1, min(500, $limit)))
            ->findAll();
    }

    public function countForGallery(int $galleryId): int
    {
        if ($galleryId <= 0 || ! $this->tableReady()) {
            return 0;
        }

        return $this->where('gallery_id', $galleryId)
            ->where('status !=', 'deleted')
            ->countAllResults();
    }

    public function findOwnedPhoto(int $photoId, int $galleryId, int $userId): ?array
    {
        if ($photoId <= 0 || $galleryId <= 0 || $userId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->where('id', $photoId)
            ->where('gallery_id', $galleryId)
            ->where('user_id', $userId)
            ->first();
    }

    public function visiblePhotoForGallery(int $photoId, int $galleryId): ?array
    {
        if ($photoId <= 0 || $galleryId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->where('id', $photoId)
            ->where('gallery_id', $galleryId)
            ->whereIn('status', self::PUBLIC_STATUSES)
            ->first();
    }
}
