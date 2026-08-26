<?php

namespace App\Models;

use CodeIgniter\Model;

class FreePublishEntitlementModel extends Model
{
    protected $table = 'free_publish_entitlements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'landing_page_id',
        'template_id',
        'first_published_at',
        'expires_at',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
