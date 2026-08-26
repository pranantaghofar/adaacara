<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    public function latestForUser(int $userId, int $limit = 20): array
    {
        if ($userId <= 0 || ! $this->tableReady()) {
            return [];
        }

        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit);
    }

    public function unreadCountForUser(int $userId): int
    {
        if ($userId <= 0 || ! $this->tableReady()) {
            return 0;
        }

        return (int) $this->where('user_id', $userId)
            ->where('read_at', null)
            ->countAllResults();
    }

    public function markAllReadForUser(int $userId): int
    {
        if ($userId <= 0 || ! $this->tableReady()) {
            return 0;
        }

        return (int) $this->db->table($this->table)
            ->where('user_id', $userId)
            ->where('read_at', null)
            ->update([
                'read_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
