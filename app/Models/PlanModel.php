<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table = 'plans';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'name',
        'slug',
        'product_type',
        'price',
        'compare_at_price',
        'max_pages',
        'is_unlimited_pages',
        'active_days',
        'is_lifetime',
        'remove_branding',
        'custom_domain',
        'description',
        'lynk_payment_url',
        'status',
    ];

    protected $useTimestamps = false;

    public function activePlans(): array
    {
        $this->ensureCreatorPlan();
        $this->ensureProductPlans();

        return $this->where('status', 'active')
            ->orderBy('price', 'ASC')
            ->findAll();
    }

    public function findActiveBySlug(string $slug): ?array
    {
        $this->ensureCreatorPlan();
        $this->ensureProductPlans();

        return $this->where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }

    public function ensureProductPlans(): void
    {
        if (! $this->db->tableExists($this->table) || ! $this->db->fieldExists('product_type', $this->table)) {
            return;
        }

        foreach ($this->productPlanDefaults() as $data) {
            $existing = $this->where('slug', (string) $data['slug'])->first();
            $payload = array_intersect_key($data, array_flip($this->db->getFieldNames($this->table)));

            if ($existing !== null) {
                $this->update((int) $existing['id'], $payload);
                continue;
            }

            $this->insert($payload);
        }
    }

    public function ensureCreatorPlan(): void
    {
        if (! $this->db->tableExists($this->table)) {
            return;
        }

        if ($this->where('slug', 'creator')->countAllResults() > 0) {
            return;
        }

        $data = [
            'name' => 'Daftar Creator',
            'slug' => 'creator',
            'product_type' => 'creator',
            'price' => 0,
            'compare_at_price' => 0,
            'max_pages' => 0,
            'is_unlimited_pages' => 0,
            'active_days' => 1,
            'is_lifetime' => 0,
            'remove_branding' => 0,
            'custom_domain' => 0,
            'description' => 'Pendaftaran creator template. Setelah diapprove admin, creator aktif permanen dan bisa submit template untuk review.',
            'status' => 'active',
        ];

        $this->insert(array_intersect_key($data, array_flip($this->db->getFieldNames($this->table))));
    }

    private function productPlanDefaults(): array
    {
        return [
            [
                'name' => 'Business Profile',
                'slug' => 'business-profile-lifetime',
                'product_type' => 'business_profile',
                'price' => 79000,
                'compare_at_price' => 0,
                'max_pages' => 1,
                'is_unlimited_pages' => 0,
                'active_days' => 36500,
                'is_lifetime' => 1,
                'remove_branding' => 0,
                'custom_domain' => 0,
                'description' => 'Sekali beli untuk 1 website Business Profile aktif terus.',
                'status' => 'active',
            ],
            [
                'name' => 'Digital Photobooth',
                'slug' => 'digital-photobooth-yearly',
                'product_type' => 'photobooth_standalone',
                'price' => 79000,
                'compare_at_price' => 0,
                'max_pages' => 0,
                'is_unlimited_pages' => 0,
                'active_days' => 365,
                'is_lifetime' => 0,
                'remove_branding' => 0,
                'custom_domain' => 0,
                'description' => 'Digital Photobooth saja tanpa paket undangan digital. Aktif 1 tahun.',
                'status' => 'active',
            ],
            [
                'name' => 'Galeri Klien Fotografer',
                'slug' => 'photographer-gallery-lifetime',
                'product_type' => 'photographer_gallery',
                'price' => 79000,
                'compare_at_price' => 0,
                'max_pages' => 0,
                'is_unlimited_pages' => 0,
                'active_days' => 36500,
                'is_lifetime' => 1,
                'remove_branding' => 0,
                'custom_domain' => 0,
                'description' => 'Tools galeri klien fotografer, sekali beli dan aktif terus.',
                'status' => 'active',
            ],
        ];
    }
}
