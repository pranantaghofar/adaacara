<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateModel extends Model
{
    protected $table = 'templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'category_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'html',
        'css',
        'js',
        'grapesjs_json',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getActiveTemplates(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function getActiveTemplate(int $id): ?array
    {
        return $this->where('is_active', 1)
            ->where('id', $id)
            ->first();
    }
}
