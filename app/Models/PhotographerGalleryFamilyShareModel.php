<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotographerGalleryFamilyShareModel extends Model
{
    protected $table = 'photographer_gallery_family_shares';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'gallery_id',
        'client_token',
        'share_token',
        'privacy_mode',
        'pin_hash',
        'status',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function findByShareToken(int $galleryId, string $shareToken): ?array
    {
        if ($galleryId <= 0 || $shareToken === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('gallery_id', $galleryId)
            ->where('share_token', $shareToken)
            ->where('status', 'active')
            ->first();
    }

    public function findActiveForClient(int $galleryId, string $clientToken): ?array
    {
        if ($galleryId <= 0 || $clientToken === '' || ! $this->tableReady()) {
            return null;
        }

        return $this->where('gallery_id', $galleryId)
            ->where('client_token', $clientToken)
            ->where('status', 'active')
            ->first();
    }

    public function upsertForClient(int $galleryId, string $clientToken, string $privacyMode, ?string $pin): ?array
    {
        if ($galleryId <= 0 || $clientToken === '' || ! $this->tableReady()) {
            return null;
        }

        $privacyMode = in_array($privacyMode, ['public', 'pin'], true) ? $privacyMode : 'public';
        $existing = $this->where('gallery_id', $galleryId)
            ->where('client_token', $clientToken)
            ->first();

        $now = date('Y-m-d H:i:s');
        $data = [
            'gallery_id' => $galleryId,
            'client_token' => $clientToken,
            'privacy_mode' => $privacyMode,
            'pin_hash' => $privacyMode === 'pin' && $pin !== null && $pin !== '' ? password_hash($pin, PASSWORD_DEFAULT) : null,
            'status' => 'active',
            'updated_at' => $now,
        ];

        if ($existing !== null) {
            $this->update((int) $existing['id'], $data);

            return $this->find((int) $existing['id']);
        }

        $data['share_token'] = bin2hex(random_bytes(16));
        $data['created_at'] = $now;
        $id = $this->insert($data, true);

        return $id ? $this->find((int) $id) : null;
    }
}
