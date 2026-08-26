<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaModel extends Model
{
    protected $table = 'media_library';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'file_name',
        'file_path',
        'file_url',
        'file_type',
        'file_size',
        'deleted_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
}
