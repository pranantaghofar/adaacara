<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotographerGalleryAlbumModel extends Model
{
    protected $table = 'photographer_gallery_albums';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'gallery_id',
        'name',
        'slug',
        'sort_order',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;

    public const DEFAULT_ALBUMS = [
        ['name' => 'Highlight', 'slug' => 'highlight'],
        ['name' => 'Ceremony', 'slug' => 'ceremony'],
        ['name' => 'Reception', 'slug' => 'reception'],
        ['name' => 'Family', 'slug' => 'family'],
        ['name' => 'Custom Album', 'slug' => 'custom-album'],
    ];

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function forGallery(int $galleryId): array
    {
        if ($galleryId <= 0 || ! $this->tableReady()) {
            return [];
        }

        return $this->where('gallery_id', $galleryId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function ensureDefaults(int $galleryId): void
    {
        if ($galleryId <= 0 || ! $this->tableReady()) {
            return;
        }

        $existing = $this->where('gallery_id', $galleryId)->findColumn('slug') ?? [];
        $existingLookup = array_fill_keys(array_map('strval', $existing), true);
        $now = date('Y-m-d H:i:s');

        foreach (self::DEFAULT_ALBUMS as $index => $album) {
            if (isset($existingLookup[$album['slug']])) {
                continue;
            }

            $this->insert([
                'gallery_id' => $galleryId,
                'name' => $album['name'],
                'slug' => $album['slug'],
                'sort_order' => ($index + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function createForGallery(int $galleryId, string $name): ?array
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($galleryId <= 0 || $name === '' || ! $this->tableReady()) {
            return null;
        }

        helper('url');

        $baseSlug = url_title($name, '-', true);
        $baseSlug = trim($baseSlug, '-');
        if ($baseSlug === '') {
            $baseSlug = 'album-' . date('YmdHis');
        }

        $existing = $this->where('gallery_id', $galleryId)
            ->where('slug', $baseSlug)
            ->first();
        if ($existing !== null) {
            return $existing;
        }

        $slug = $baseSlug;
        $suffix = 2;
        while ($this->where('gallery_id', $galleryId)->where('slug', $slug)->countAllResults() > 0) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $sortOrder = ((int) ($this->selectMax('sort_order', 'max_order')
            ->where('gallery_id', $galleryId)
            ->first()['max_order'] ?? 0)) + 10;
        $now = date('Y-m-d H:i:s');
        $albumId = $this->insert([
            'gallery_id' => $galleryId,
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sortOrder,
            'created_at' => $now,
            'updated_at' => $now,
        ], true);

        if (! $albumId) {
            return null;
        }

        return $this->find((int) $albumId);
    }

    public function belongsToGallery(int $albumId, int $galleryId): bool
    {
        if ($albumId <= 0 || $galleryId <= 0 || ! $this->tableReady()) {
            return false;
        }

        return $this->where('id', $albumId)
            ->where('gallery_id', $galleryId)
            ->countAllResults() > 0;
    }
}
