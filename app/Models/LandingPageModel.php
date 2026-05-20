<?php

namespace App\Models;

use CodeIgniter\Model;

class LandingPageModel extends Model
{
    protected $table = 'landing_pages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'template_id',
        'title',
        'slug',
        'event_date',
        'status',
        'html',
        'css',
        'js',
        'grapesjs_json',
        'published_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function slugExists(string $slug): bool
    {
        return $this->where('slug', $slug)->countAllResults() > 0;
    }

    public function createFromTemplate(int $userId, array $template, array $payload): int|string|false
    {
        return $this->insert([
            'user_id' => $userId,
            'template_id' => $template['id'],
            'title' => $payload['title'],
            'slug' => $payload['slug'],
            'event_date' => $payload['event_date'] ?: null,
            'status' => 'draft',
            'html' => $template['html'] ?? '',
            'css' => $template['css'] ?? '',
            'js' => $template['js'] ?? '',
            'grapesjs_json' => $template['grapesjs_json'] ?? null,
            'published_at' => null,
        ], true);
    }
}
