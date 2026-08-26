<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class CustomFontModel extends Model
{
    protected $table = 'custom_fonts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'font_family',
        'font_weight',
        'font_style',
        'file_path',
        'mime_type',
        'original_name',
        'sort_order',
        'is_active',
    ];

    public function tableReady(): bool
    {
        return Database::connect()->tableExists($this->table);
    }

    public function activeFonts(): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('font_family', 'ASC')
            ->orderBy('font_weight', 'ASC')
            ->findAll();
    }
}
