<?php

namespace App\Commands;

use App\Models\EditorAssetModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ImportEditorAssets extends BaseCommand
{
    protected $group = 'AdaAcara';
    protected $name = 'editor-assets:import';
    protected $description = 'Import local editor ornament assets from public/assets/editor into editor_assets.';
    protected $usage = 'editor-assets:import [type] [pack_name]';

    private array $allowedTypes = ['ornament', 'shape', 'background', 'pattern'];
    private array $allowedExtensions = ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'];
    private array $metadataColumns = [
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
    ];

    public function run(array $params)
    {
        $db = Database::connect();
        if (! $db->tableExists('editor_assets')) {
            CLI::error('Tabel editor_assets belum tersedia. Jalankan database/alter_editor_assets_library.sql terlebih dahulu.');
            return;
        }

        $type = strtolower(trim((string) ($params[0] ?? 'ornament')));
        if (! in_array($type, $this->allowedTypes, true)) {
            CLI::error('Tipe asset tidak valid. Gunakan: ' . implode(', ', $this->allowedTypes));
            return;
        }

        $basePath = FCPATH . 'assets/editor/' . $type;
        if (! is_dir($basePath)) {
            CLI::error('Folder tidak ditemukan: ' . $basePath);
            return;
        }

        $packName = trim((string) ($params[1] ?? 'Local Pack'));
        $model = new EditorAssetModel();
        $columns = $db->getFieldNames('editor_assets');
        $imported = 0;
        $skipped = 0;

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (! in_array($extension, $this->allowedExtensions, true)) {
                continue;
            }

            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen(FCPATH)));
            if ($model->where('file_path', $relativePath)->first()) {
                $skipped++;
                continue;
            }

            $categorySlug = basename(dirname($file->getPathname()));
            $categoryName = $this->labelFromSlug($categorySlug);
            $title = $this->labelFromSlug(pathinfo($file->getFilename(), PATHINFO_FILENAME));
            $dimensions = $this->imageDimensions($file->getPathname());
            $payload = [
                'title' => $title,
                'type' => $type,
                'category_id' => 0,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'file_name' => $file->getFilename(),
                'file_path' => $relativePath,
                'file_url' => base_url($relativePath),
                'mime_type' => $this->mimeType($extension),
                'file_size' => $file->getSize(),
                'sort_order' => 0,
                'is_active' => 1,
                'created_by' => null,
            ];

            $payload += $this->metadataPayload([
                'tags' => implode(',', array_values(array_unique([$type, $categorySlug, $categoryName, $title]))),
                'pack_name' => $packName !== '' ? $packName : 'Local Pack',
                'source_name' => 'AdaAcara Local Asset',
                'source_url' => '',
                'license' => 'Internal/curated',
                'thumbnail_path' => '',
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'is_premium' => 0,
                'usage_count' => 0,
            ], $columns);

            if ((int) $model->insert($payload, true) > 0) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        CLI::write("Import selesai. Baru: {$imported}. Skip: {$skipped}.", 'green');
    }

    private function metadataPayload(array $payload, array $columns): array
    {
        $filtered = [];
        foreach ($payload as $key => $value) {
            if (in_array($key, $this->metadataColumns, true) && in_array($key, $columns, true)) {
                $filtered[$key] = $value === '' ? null : $value;
            }
        }

        return $filtered;
    }

    private function labelFromSlug(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?: $value;

        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    private function imageDimensions(string $path): array
    {
        if (str_ends_with(strtolower($path), '.svg')) {
            return ['width' => null, 'height' => null];
        }

        $size = @getimagesize($path);
        if (! is_array($size)) {
            return ['width' => null, 'height' => null];
        }

        return ['width' => (int) ($size[0] ?? 0), 'height' => (int) ($size[1] ?? 0)];
    }

    private function mimeType(string $extension): string
    {
        return match ($extension) {
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/png',
        };
    }
}
