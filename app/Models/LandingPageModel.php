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
        'project_type',
        'category_id',
        'title',
        'slug',
        'event_date',
        'status',
        'html',
        'css',
        'js',
        'seo_title',
        'seo_description',
        'og_image',
        'editor_json',
        'editor_type',
        'grapesjs_json',
        'published_html',
        'published_css',
        'published_js',
        'published_editor_json',
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
        $data = [
            'user_id' => $userId,
            'template_id' => $template['id'],
            'project_type' => $this->normalizeProjectType((string) ($payload['project_type'] ?? $template['project_type'] ?? '')),
            'category_id' => $template['category_id'] ?? null,
            'title' => $payload['title'],
            'slug' => $payload['slug'],
            'event_date' => $payload['event_date'] ?: null,
            'status' => 'draft',
            'html' => $template['html'] ?? '',
            'css' => $template['css'] ?? '',
            'js' => $template['js'] ?? '',
            'editor_json' => $template['editor_json'] ?? $template['grapesjs_json'] ?? null,
            'grapesjs_json' => $template['grapesjs_json'] ?? $template['editor_json'] ?? null,
            'published_at' => null,
        ];

        if ($this->hasField('editor_type')) {
            $editorType = (string) ($template['editor_type'] ?? '');
            $data['editor_type'] = $editorType !== '' ? $editorType : $this->detectEditorType($data['editor_json'] ?? '');
        }

        if (! $this->hasField('project_type')) {
            unset($data['project_type']);
        }

        return $this->insert($data, true);
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $wantedFields = [
            'id',
            'user_id',
            'template_id',
            'title',
            'slug',
            'html',
            'css',
            'js',
            'published_html',
            'published_css',
            'published_js',
            'published_editor_json',
            'seo_title',
            'seo_description',
            'og_image',
            'status',
            'published_at',
        ];
        $availableFields = $this->db->getFieldNames($this->table);
        $selectFields = array_values(array_intersect($wantedFields, $availableFields));

        $page = $this->select($selectFields)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if ($page === null) {
            return null;
        }

        foreach ($wantedFields as $field) {
            $page[$field] ??= '';
        }

        return $page;
    }

    private function hasField(string $field): bool
    {
        return in_array($field, $this->db->getFieldNames($this->table), true);
    }

    private function detectEditorType(?string $editorJson): string
    {
        $data = json_decode((string) $editorJson, true);

        if (is_array($data) && ($data['renderer'] ?? '') === 'fabric') {
            return 'fabric';
        }

        return 'grapesjs';
    }

    private function normalizeProjectType(string $value): string
    {
        $value = strtolower(trim($value));

        return match ($value) {
            'photobooth', 'digital_photobooth' => 'photobooth',
            'business_profile', 'business-profile' => 'business_profile',
            default => 'invitation',
        };
    }
}
