<?php

namespace App\Models;

use CodeIgniter\Model;

class EditorAssetModel extends Model
{
    protected $table = 'editor_assets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'type',
        'category_id',
        'category_name',
        'category_slug',
        'file_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'tags',
        'pack_name',
        'source_name',
        'source_url',
        'license',
        'thumbnail_path',
        'width',
        'height',
        'is_premium',
        'usage_count',
        'sort_order',
        'is_active',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
