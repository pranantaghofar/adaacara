<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentSettingModel extends Model
{
    protected $table = 'payment_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'updated_by',
        'updated_at',
    ];
    protected $useTimestamps = false;

    public function getSettings(): array
    {
        $defaults = [
            'payment_mode' => 'manual',
            'midtrans_is_production' => '1',
            'midtrans_client_key' => '',
            'midtrans_server_key' => '',
            'lynk_payment_url' => '',
            'lynk_merchant_key' => '',
        ];

        if (! db_connect()->tableExists($this->table)) {
            return $defaults;
        }

        $rows = $this->findAll();
        foreach ($rows as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if ($key !== '') {
                $defaults[$key] = (string) ($row['setting_value'] ?? '');
            }
        }

        if (! in_array($defaults['payment_mode'], ['manual', 'midtrans', 'lynk', 'both', 'manual_lynk', 'midtrans_lynk', 'all'], true)) {
            $defaults['payment_mode'] = 'manual';
        }

        return $defaults;
    }

    public function saveSettings(array $settings, int $adminId): void
    {
        foreach ($settings as $key => $value) {
            $existing = $this->where('setting_key', $key)->first();
            $payload = [
                'setting_key' => $key,
                'setting_value' => (string) $value,
                'updated_by' => $adminId,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->update((int) $existing['id'], $payload);
            } else {
                $this->insert($payload);
            }
        }
    }
}
