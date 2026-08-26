<?php

namespace App\Models;

use CodeIgniter\Model;

class GuestMemoryModel extends Model
{
    protected $table = 'guest_memories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'landing_page_id',
        'frame_id',
        'guest_name',
        'guest_email',
        'print_code',
        'print_used_at',
        'print_used_ip',
        'print_used_user_agent',
        'photo',
        'thumbnail',
        'audio',
        'audio_duration',
        'wish_text',
        'status',
        'ip_address',
        'user_agent',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function approvedForPage(int $landingPageId, int $limit = 12, int $offset = 0, string $keyword = ''): array
    {
        $builder = $this->where('landing_page_id', $landingPageId)
            ->where('status', 'approved')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC');

        $keyword = trim($keyword);
        if ($keyword !== '') {
            $builder->like('guest_name', mb_substr($keyword, 0, 80));
        }

        return $builder->findAll(max(1, min($limit, 24)), max(0, $offset));
    }

    public function nameExistsForPage(int $landingPageId, string $guestName): bool
    {
        $guestName = trim($guestName);
        if ($landingPageId <= 0 || $guestName === '') {
            return false;
        }

        $db = db_connect();

        return $db->table($this->table)
            ->where('landing_page_id', $landingPageId)
            ->whereIn('status', ['pending', 'approved', 'hidden'])
            ->where('LOWER(TRIM(`guest_name`)) = ' . $db->escape(mb_strtolower($guestName)), null, false)
            ->countAllResults() > 0;
    }

    public function hasPrintCodeColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $fields = $this->db->getFieldNames($this->table);
        $hasColumn = in_array('print_code', $fields, true);

        return $hasColumn;
    }

    public function hasGuestEmailColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $fields = $this->db->getFieldNames($this->table);
        $hasColumn = in_array('guest_email', $fields, true);

        return $hasColumn;
    }

    public function hasPrintTrackingColumns(): bool
    {
        static $hasColumns = null;
        if ($hasColumns !== null) {
            return $hasColumns;
        }

        $fields = $this->db->getFieldNames($this->table);
        $hasColumns = in_array('print_used_at', $fields, true)
            && in_array('print_used_ip', $fields, true)
            && in_array('print_used_user_agent', $fields, true);

        return $hasColumns;
    }

    public function hasWishTextColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $fields = $this->db->getFieldNames($this->table);
        $hasColumn = in_array('wish_text', $fields, true);

        return $hasColumn;
    }

    public function recentUploadCount(int $landingPageId, string $ipAddress, int $minutes = 10): int
    {
        if ($ipAddress === '') {
            return 0;
        }

        return (int) $this->where('landing_page_id', $landingPageId)
            ->where('ip_address', $ipAddress)
            ->where('created_at >=', date('Y-m-d H:i:s', time() - (max(1, $minutes) * 60)))
            ->countAllResults();
    }
}
