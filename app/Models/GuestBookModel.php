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
        'name',
        'email',
        'message',
        'attendance_status',
        'guest_count',
        'is_visible',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
