<?php

namespace App\Models;

use CodeIgniter\Model;

class GuestBookModel extends Model
{
    protected $table = 'guest_books';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'landing_page_id',
        'guest_name',
        'message',
        'sticker',
        'attendance',
        'is_approved',
        'read_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function latestApprovedByLandingPage(int $landingPageId, int $limit = 20): array
    {
        return $this->where('landing_page_id', $landingPageId)
            ->where('is_approved', 1)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
