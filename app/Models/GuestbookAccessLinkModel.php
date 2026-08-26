<?php

namespace App\Models;

use CodeIgniter\Model;

class GuestbookAccessLinkModel extends Model
{
    protected $table = 'guestbook_access_links';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'landing_page_id',
        'created_by_user_id',
        'access_token',
        'enabled',
        'last_accessed_at',
        'revoked_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function activeForPage(int $landingPageId): ?array
    {
        if ($landingPageId <= 0 || ! $this->tableReady()) {
            return null;
        }

        return $this->where('landing_page_id', $landingPageId)
            ->where('enabled', 1)
            ->where('revoked_at', null)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function activeForToken(string $token): ?array
    {
        $token = strtolower(trim($token));
        if (! preg_match('/\A[a-f0-9]{64}\z/', $token) || ! $this->tableReady()) {
            return null;
        }

        return $this->where('access_token', $token)
            ->where('enabled', 1)
            ->where('revoked_at', null)
            ->first();
    }
}
