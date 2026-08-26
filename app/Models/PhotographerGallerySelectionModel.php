<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotographerGallerySelectionModel extends Model
{
    protected $table = 'photographer_gallery_selections';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'gallery_id',
        'photo_id',
        'client_name',
        'client_token',
        'selection_type',
        'submitted_at',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function selectedPhotoIds(int $galleryId, string $clientToken, string $type = 'print'): array
    {
        if ($galleryId <= 0 || $clientToken === '' || ! $this->tableReady()) {
            return [];
        }

        $builder = $this->select('photo_id')
            ->where('gallery_id', $galleryId)
            ->where('client_token', $clientToken);

        if ($this->hasColumn('selection_type')) {
            $builder->where('selection_type', $type);
        }

        $rows = $builder->findAll();

        return array_values(array_map(static fn (array $row): int => (int) ($row['photo_id'] ?? 0), $rows));
    }

    public function submittedPhotoIds(int $galleryId, string $clientToken, string $type = 'print'): array
    {
        if ($galleryId <= 0 || $clientToken === '' || ! $this->tableReady() || ! $this->hasColumn('submitted_at')) {
            return [];
        }

        $builder = $this->select('photo_id')
            ->where('gallery_id', $galleryId)
            ->where('client_token', $clientToken)
            ->where('submitted_at IS NOT NULL', null, false);

        if ($this->hasColumn('selection_type')) {
            $builder->where('selection_type', $type);
        }

        $rows = $builder->findAll();

        return array_values(array_map(static fn (array $row): int => (int) ($row['photo_id'] ?? 0), $rows));
    }

    public function countSelected(int $galleryId, string $clientToken, string $type = 'print'): int
    {
        if ($galleryId <= 0 || $clientToken === '' || ! $this->tableReady()) {
            return 0;
        }

        $builder = $this->where('gallery_id', $galleryId)
            ->where('client_token', $clientToken);

        if ($this->hasColumn('selection_type')) {
            $builder->where('selection_type', $type);
        }

        return $builder->countAllResults();
    }

    public function findSelection(int $galleryId, int $photoId, string $clientToken, string $type = 'print'): ?array
    {
        if ($galleryId <= 0 || $photoId <= 0 || $clientToken === '' || ! $this->tableReady()) {
            return null;
        }

        $builder = $this->where('gallery_id', $galleryId)
            ->where('photo_id', $photoId)
            ->where('client_token', $clientToken);

        if ($this->hasColumn('selection_type')) {
            $builder->where('selection_type', $type);
        }

        return $builder->first();
    }

    public function submitForClient(int $galleryId, string $clientToken, string $type = 'print'): int
    {
        if ($galleryId <= 0 || $clientToken === '' || ! $this->tableReady() || ! $this->hasColumn('submitted_at')) {
            return 0;
        }

        $builder = $this->where('gallery_id', $galleryId)
            ->where('client_token', $clientToken);

        if ($this->hasColumn('selection_type')) {
            $builder->where('selection_type', $type);
        }

        $builder->set(['submitted_at' => date('Y-m-d H:i:s')])->update();

        return max(0, (int) $this->db->affectedRows());
    }

    public function syncShareSelectionsToClient(int $galleryId, string $sourceClientToken, string $targetClientToken): int
    {
        if ($galleryId <= 0 || $sourceClientToken === '' || $targetClientToken === '' || $sourceClientToken === $targetClientToken || ! $this->tableReady() || ! $this->supportsSelectionType('share')) {
            return 0;
        }

        $photoIds = $this->selectedPhotoIds($galleryId, $sourceClientToken, 'share');
        $this->db->transStart();
        $this->where('gallery_id', $galleryId)
            ->where('client_token', $targetClientToken)
            ->where('selection_type', 'share')
            ->delete();

        if ($photoIds !== []) {
            $now = date('Y-m-d H:i:s');
            $hasSubmittedAt = $this->hasColumn('submitted_at');
            $rows = array_map(static fn (int $photoId): array => [
                'gallery_id' => $galleryId,
                'photo_id' => $photoId,
                'client_token' => $targetClientToken,
                'selection_type' => 'share',
                'created_at' => $now,
            ], $photoIds);
            if ($hasSubmittedAt) {
                $rows = array_map(static function (array $row) use ($now): array {
                    $row['submitted_at'] = $now;
                    return $row;
                }, $rows);
            }
            $this->insertBatch($rows);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? count($photoIds) : 0;
    }

    public function selectedForGallery(int $galleryId): array
    {
        if ($galleryId <= 0 || ! $this->tableReady()) {
            return [];
        }

        try {
            $hasSubmittedAt = $this->hasColumn('submitted_at');
            $select = 'photographer_gallery_selections.*, photographer_gallery_photos.file_path, photographer_gallery_photos.thumb_path, photographer_gallery_photos.original_name, photographer_gallery_photos.status AS photo_status, photographer_gallery_photos.album_id';
            $builder = $this->select($select)
                ->join('photographer_gallery_photos', 'photographer_gallery_photos.id = photographer_gallery_selections.photo_id', 'inner')
                ->where('photographer_gallery_selections.gallery_id', $galleryId)
                ->whereIn('photographer_gallery_photos.status', ['visible', 'uploaded', 'selected', 'delivered']);

            if ($this->hasColumn('selection_type')) {
                $builder->where('photographer_gallery_selections.selection_type', 'print');
            }

            return $builder
                ->orderBy($hasSubmittedAt ? 'photographer_gallery_selections.submitted_at' : 'photographer_gallery_selections.created_at', 'DESC')
                ->orderBy('photographer_gallery_selections.created_at', 'DESC')
                ->findAll();
        } catch (\Throwable $e) {
            log_message('error', 'Photographer Gallery selectedForGallery gagal: {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function hasColumn(string $column): bool
    {
        if (! $this->tableReady()) {
            return false;
        }

        return in_array($column, $this->db->getFieldNames($this->table), true);
    }

    public function supportsSelectionType(string $type): bool
    {
        if ($type === 'print') {
            return true;
        }
        if (! $this->tableReady() || ! $this->hasColumn('selection_type')) {
            return false;
        }

        try {
            $row = $this->db->query('SHOW COLUMNS FROM `' . $this->table . '` LIKE ?', ['selection_type'])->getRowArray();
            $columnType = strtolower((string) ($row['Type'] ?? ''));

            return str_contains($columnType, "'" . strtolower($type) . "'");
        } catch (\Throwable $e) {
            return false;
        }
    }
}
