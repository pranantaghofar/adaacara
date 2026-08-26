<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class AppSettingModel extends Model
{
    protected $table = 'app_settings';
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

    public function getSettings(array $defaults = []): array
    {
        if (! $this->db->tableExists($this->table)) {
            return $defaults;
        }

        $settings = $defaults;
        foreach ($this->findAll() as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if ($key !== '') {
                $settings[$key] = (string) ($row['setting_value'] ?? '');
            }
        }

        return $settings;
    }

    public function getValue(string $key, ?string $default = null): ?string
    {
        if (! $this->db->tableExists($this->table)) {
            return $default;
        }

        $row = $this->where('setting_key', $key)->first();
        if (! $row) {
            return $default;
        }

        return (string) ($row['setting_value'] ?? '');
    }

    public function saveSettings(array $settings, int $adminId): void
    {
        $this->ensureTable();

        foreach ($settings as $key => $value) {
            $existing = $this->where('setting_key', (string) $key)->first();
            $payload = [
                'setting_key' => (string) $key,
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

    public function ensureTable(): void
    {
        if ($this->db->tableExists($this->table)) {
            return;
        }

        $forge = Database::forge();
        $forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey('setting_key');
        $forge->createTable($this->table, true);
    }
}
