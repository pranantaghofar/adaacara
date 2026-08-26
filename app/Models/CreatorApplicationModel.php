<?php

namespace App\Models;

use CodeIgniter\Model;

class CreatorApplicationModel extends Model
{
    protected $table = 'creator_applications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'display_name',
        'bio',
        'portfolio_url',
        'social_links',
        'status',
        'reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function latestForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function pendingForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public function adminList(?string $status = null, string $search = ''): array
    {
        $builder = $this->select('creator_applications.*, users.name AS user_name, users.email AS user_email, reviewers.name AS reviewer_name')
            ->join('users', 'users.id = creator_applications.user_id', 'left')
            ->join('users AS reviewers', 'reviewers.id = creator_applications.reviewed_by', 'left');

        if ($status !== null && $status !== '') {
            $builder->where('creator_applications.status', $status);
        }

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('creator_applications.display_name', $search)
                ->orLike('users.name', $search)
                ->orLike('users.email', $search)
                ->groupEnd();
        }

        return $builder
            ->orderBy('creator_applications.created_at', 'DESC')
            ->orderBy('creator_applications.id', 'DESC')
            ->findAll();
    }

    public function adminFind(int $id): ?array
    {
        return $this->select('creator_applications.*, users.name AS user_name, users.email AS user_email, reviewers.name AS reviewer_name')
            ->join('users', 'users.id = creator_applications.user_id', 'left')
            ->join('users AS reviewers', 'reviewers.id = creator_applications.reviewed_by', 'left')
            ->where('creator_applications.id', $id)
            ->first();
    }
}
