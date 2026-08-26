<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function templateDefaults(): array
    {
        return [
            ['name' => 'Wedding', 'slug' => 'wedding', 'description' => 'Template undangan pernikahan elegan.', 'sort_order' => 1],
            ['name' => 'Seminar', 'slug' => 'seminar', 'description' => 'Template seminar, webinar, workshop, dan talkshow.', 'sort_order' => 2],
            ['name' => 'Bukber', 'slug' => 'bukber', 'description' => 'Template buka bersama dan acara Ramadhan.', 'sort_order' => 3],
            ['name' => 'Halal Bihalal', 'slug' => 'halal-bihalal', 'description' => 'Template halal bihalal keluarga, komunitas, dan perusahaan.', 'sort_order' => 4],
            ['name' => 'Lamaran', 'slug' => 'lamaran', 'description' => 'Template lamaran, engagement, dan tunangan.', 'sort_order' => 5],
            ['name' => 'Ulang Tahun', 'slug' => 'ulang-tahun', 'description' => 'Template ulang tahun dan gathering personal.', 'sort_order' => 6],
            ['name' => 'Khitan', 'slug' => 'khitan', 'description' => 'Template undangan khitan modern.', 'sort_order' => 7],
            ['name' => 'Aqiqah', 'slug' => 'aqiqah', 'description' => 'Template aqiqah dan acara keluarga.', 'sort_order' => 8],
            ['name' => 'Syukuran', 'slug' => 'syukuran', 'description' => 'Template syukuran, tasyakuran, dan acara keluarga.', 'sort_order' => 9],
            ['name' => 'Wisuda', 'slug' => 'wisuda', 'description' => 'Template wisuda dan graduation.', 'sort_order' => 10],
            ['name' => 'Corporate', 'slug' => 'corporate', 'description' => 'Template company gathering, launching, dan event bisnis.', 'sort_order' => 11],
            ['name' => 'Lainnya', 'slug' => 'lainnya', 'description' => 'Template event umum lainnya.', 'sort_order' => 99],
        ];
    }

    public function ensureTemplateDefaults(): void
    {
        $fields = $this->db->getFieldNames($this->table);
        $now = date('Y-m-d H:i:s');
        $existing = $this->select('id, slug')->findAll();
        $slugToId = [];

        foreach ($existing as $category) {
            $slugToId[(string) ($category['slug'] ?? '')] = (int) ($category['id'] ?? 0);
        }

        if (! isset($slugToId['ulang-tahun']) && isset($slugToId['birthday'])) {
            $legacyId = $slugToId['birthday'];
            $legacyUpdate = [
                'name' => 'Ulang Tahun',
                'slug' => 'ulang-tahun',
            ];
            if (in_array('description', $fields, true)) {
                $legacyUpdate['description'] = 'Template ulang tahun dan gathering personal.';
            }
            if (in_array('sort_order', $fields, true)) {
                $legacyUpdate['sort_order'] = 5;
            }
            if (in_array('is_active', $fields, true)) {
                $legacyUpdate['is_active'] = 1;
            }
            if (in_array($this->updatedField, $fields, true)) {
                $legacyUpdate[$this->updatedField] = $now;
            }
            $this->update($slugToId['birthday'], $legacyUpdate);
            unset($slugToId['birthday']);
            $slugToId['ulang-tahun'] = $legacyId;
        }

        foreach ($this->templateDefaults() as $category) {
            if (isset($slugToId[$category['slug']])) {
                continue;
            }

            $insert = [
                'name' => $category['name'],
                'slug' => $category['slug'],
            ];
            foreach (['description', 'sort_order'] as $field) {
                if (in_array($field, $fields, true)) {
                    $insert[$field] = $category[$field];
                }
            }
            if (in_array('is_active', $fields, true)) {
                $insert['is_active'] = 1;
            }
            if (in_array($this->createdField, $fields, true)) {
                $insert[$this->createdField] = $now;
            }
            if (in_array($this->updatedField, $fields, true)) {
                $insert[$this->updatedField] = $now;
            }

            $this->insert($insert);
        }
    }

    public function templateOptions(): array
    {
        $this->ensureTemplateDefaults();

        $fields = $this->db->getFieldNames($this->table);
        $builder = $this;

        if (in_array('is_active', $fields, true)) {
            $builder = $builder->where('is_active', 1);
        }
        if (in_array('sort_order', $fields, true)) {
            $builder = $builder->orderBy('sort_order', 'ASC');
        }

        return $builder
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
