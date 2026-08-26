<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotographerGalleryCommentModel extends Model
{
    protected $table = 'photographer_gallery_comments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'gallery_id',
        'photo_id',
        'client_name',
        'comment',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function forGallery(int $galleryId, int $limit = 100): array
    {
        if ($galleryId <= 0 || ! $this->tableReady()) {
            return [];
        }

        try {
            return $this->select('photographer_gallery_comments.*, photographer_gallery_photos.file_path, photographer_gallery_photos.thumb_path, photographer_gallery_photos.original_name, photographer_gallery_photos.album_id, photographer_gallery_photos.status AS photo_status')
                ->join('photographer_gallery_photos', 'photographer_gallery_photos.id = photographer_gallery_comments.photo_id', 'left')
                ->where('photographer_gallery_comments.gallery_id', $galleryId)
                ->orderBy('photographer_gallery_comments.created_at', 'DESC')
                ->orderBy('photographer_gallery_comments.id', 'DESC')
                ->findAll($limit);
        } catch (\Throwable $e) {
            log_message('error', 'Photographer Gallery comments load gagal: {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
