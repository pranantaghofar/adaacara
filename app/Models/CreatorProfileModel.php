<?php

namespace App\Models;

use CodeIgniter\Model;

class CreatorProfileModel extends Model
{
    protected $table = 'creator_profiles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'display_name',
        'slug',
        'bio',
        'avatar_url',
        'portfolio_url',
        'social_links',
        'status',
        'approved_application_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function activeForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    public function statusForUser(int $userId): array
    {
        $empty = [
            'status' => 'none',
            'display_name' => null,
            'profile' => null,
            'application' => null,
        ];

        if ($userId <= 0 || ! $this->db->tableExists($this->table)) {
            return $empty;
        }

        $profile = $this->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();

        if ($profile !== null && (string) ($profile['status'] ?? '') === 'active') {
            return [
                'status' => 'active',
                'display_name' => $profile['display_name'] ?? null,
                'profile' => $profile,
                'application' => null,
            ];
        }

        if (! $this->db->tableExists('creator_applications')) {
            return $empty;
        }

        $application = (new CreatorApplicationModel())->latestForUser($userId);
        if ($application === null) {
            return $empty;
        }

        $status = (string) ($application['status'] ?? 'none');
        if (! in_array($status, ['pending', 'active', 'rejected', 'approved'], true)) {
            $status = 'none';
        }

        return [
            'status' => $status === 'approved' ? 'active' : $status,
            'display_name' => $application['display_name'] ?? null,
            'profile' => $profile,
            'application' => $application,
        ];
    }
}
