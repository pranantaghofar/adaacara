<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\EditorAssetModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use Throwable;

class EditorAssetController extends BaseController
{
    private array $allowedTypes = ['ornament', 'shape', 'background', 'pattern'];
    private array $assetManagerRoles = ['superadmin', 'admin', 'content_admin'];
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

    private array $defaultAssetCategories = [
        ['name' => 'Wedding Floral', 'slug' => 'wedding-floral'],
        ['name' => 'Frame & Border', 'slug' => 'frame-border'],
        ['name' => 'Islamic Ornament', 'slug' => 'islamic-ornament'],
        ['name' => 'Baby & Aqiqah', 'slug' => 'baby-aqiqah'],
        ['name' => 'Birthday Cute', 'slug' => 'birthday-cute'],
        ['name' => 'Luxury Gold', 'slug' => 'luxury-gold'],
        ['name' => 'Minimal Line', 'slug' => 'minimal-line'],
        ['name' => 'Divider', 'slug' => 'divider'],
        ['name' => 'Pattern Background', 'slug' => 'pattern-background'],
        ['name' => 'Abstract Shape', 'slug' => 'abstract-shape'],
        ['name' => 'Ribbon & Badge', 'slug' => 'ribbon-badge'],
        ['name' => 'Photo Frame', 'slug' => 'photo-frame'],
        ['name' => 'Batik Nusantara', 'slug' => 'batik-nusantara'],
        ['name' => 'Ramadan & Eid', 'slug' => 'ramadan-eid'],
    ];

    public function index(): ResponseInterface
    {
        $db = Database::connect();
        $categories = $this->categories();

        if (! $db->tableExists('editor_assets')) {
            return $this->assetLibraryResponse([
                'success' => true,
                'categories' => $categories,
                'items' => [],
                'message' => 'Tabel editor_assets belum tersedia.',
            ]);
        }

        $limit = max(1, min(500, (int) ($this->request->getGet('limit') ?? 500)));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $limit;
        $type = strtolower(trim((string) ($this->request->getGet('type') ?? 'all')));
        $category = $this->slugify((string) ($this->request->getGet('category') ?? ''));
        $query = trim(strip_tags((string) ($this->request->getGet('q') ?? '')));

        $model = new EditorAssetModel();
        $builder = $model->where('is_active', 1);

        if ($type !== '' && $type !== 'all' && in_array($type, $this->allowedTypes, true)) {
            $builder->where('type', $type);
        }

        if ($category !== '' && $category !== 'all') {
            $builder->where('category_slug', $category);
        }

        if ($query !== '') {
            $builder->groupStart()
                ->like('title', $query)
                ->orLike('category_name', $query)
                ->orLike('category_slug', $query)
                ->orLike('type', $query);
            if ($this->hasEditorAssetColumn('tags')) {
                $builder->orLike('tags', $query);
            }
            if ($this->hasEditorAssetColumn('pack_name')) {
                $builder->orLike('pack_name', $query);
            }
            $builder->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);
        $items = $builder
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll($limit, $offset);

        return $this->assetLibraryResponse([
            'success' => true,
            'categories' => $categories,
            'items' => array_map(fn (array $item): array => $this->serializeAsset($item), $items),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'hasMore' => $offset + count($items) < $total,
            ],
        ]);
    }

    private function assetLibraryResponse(array $payload): ResponseInterface
    {
        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setJSON($payload);
    }

    public function uploadStatus(): ResponseInterface
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Endpoint upload asset aktif. Upload asset dilakukan lewat editor dengan metode POST.',
        ]);
    }

    public function upload(): ResponseInterface
    {
        if (! $this->canManageAssets() && ! $this->hasValidAdminUploadToken()) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Upload asset hanya tersedia untuk admin. Silakan login ulang sebagai admin jika sesi sudah berubah.',
                ]);
        }

        $db = Database::connect();
        if (! $db->tableExists('editor_assets')) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Tabel editor_assets belum tersedia. Jalankan SQL database/alter_editor_assets_library.sql terlebih dahulu.',
                ]);
        }

        $type = strtolower(trim((string) $this->request->getPost('type')));
        $categorySlug = trim((string) $this->request->getPost('category'));
        $category = $this->findCategory($categorySlug);

        if (! in_array($type, $this->allowedTypes, true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Tipe asset tidak valid.']);
        }

        if ($category === null) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Pilih kategori terlebih dahulu.']);
        }

        $file = $this->request->getFile('file');
        if (! $file) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'File asset tidak ditemukan. Pilih file SVG, PNG, JPG, WEBP, atau GIF.',
                ]);
        }

        if (! $file->isValid()) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $this->uploadErrorMessage($file, 'asset', 4)]);
        }

        $extension = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
        $uploadedMimeType = strtolower($file->getMimeType() ?: $file->getClientMimeType() ?: ($extension === 'svg' ? 'image/svg+xml' : ''));
        $allowedExtensions = ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'];
        $allowedMimes = ['image/svg+xml', 'image/png', 'image/jpg', 'image/jpeg', 'image/webp', 'image/gif', 'application/octet-stream'];

        if ($file->getSize() > 4 * 1024 * 1024) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Upload asset maksimal 4MB.']);
        }

        if (! in_array($extension, $allowedExtensions, true) || ($uploadedMimeType !== '' && ! in_array($uploadedMimeType, $allowedMimes, true))) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Format asset harus SVG, PNG, JPG, WEBP, atau GIF.']);
        }

        if ($extension === 'svg') {
            $rawSvg = file_get_contents($file->getTempName()) ?: '';
            if ($this->svgLooksUnsafe($rawSvg)) {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON(['success' => false, 'message' => 'SVG mengandung konten yang tidak aman.']);
            }
        }

        try {
            $categoryFolder = $this->slugify((string) ($category['slug'] ?? $category['name'] ?? 'asset'));
            $uploadPath = FCPATH . 'uploads/editor-assets/' . $type . '/' . $categoryFolder;
            $directoryError = $this->ensureUploadDirectory($uploadPath);
            if ($directoryError !== null) {
                return $this->response
                    ->setStatusCode(500)
                    ->setJSON(['success' => false, 'message' => $directoryError]);
            }

            $storedName = $file->getRandomName();
            $originalName = $file->getClientName() ?: $storedName;
            $uploadedFileSize = $file->getSize();
            $file->move($uploadPath, $storedName, true);

            $relativePath = 'uploads/editor-assets/' . $type . '/' . $categoryFolder . '/' . $storedName;
            $userId = (int) (session()->get('userId') ?? 0);
            $asset = [
                'title' => pathinfo($originalName, PATHINFO_FILENAME) ?: $storedName,
                'type' => $type,
                'category_id' => (int) ($category['id'] ?? 0),
                'category_name' => (string) ($category['name'] ?? $categoryFolder),
                'category_slug' => $categoryFolder,
                'file_name' => $originalName,
                'file_path' => $relativePath,
                'file_url' => base_url($relativePath),
                'mime_type' => $uploadedMimeType,
                'file_size' => is_file(FCPATH . $relativePath) ? filesize(FCPATH . $relativePath) : $uploadedFileSize,
                'sort_order' => 0,
                'is_active' => 1,
                'created_by' => $userId > 0 ? $userId : null,
            ];
            $asset += $this->optionalAssetMetadataPayload([
                'tags' => $this->normalizeTags((string) ($this->request->getPost('tags') ?? '')),
                'pack_name' => $this->cleanText((string) ($this->request->getPost('pack_name') ?? ''), 120),
                'source_name' => $this->cleanText((string) ($this->request->getPost('source_name') ?? ''), 120),
                'source_url' => $this->cleanUrl((string) ($this->request->getPost('source_url') ?? '')),
                'license' => $this->cleanText((string) ($this->request->getPost('license') ?? ''), 120),
                'thumbnail_path' => '',
                'width' => $this->imageDimension(FCPATH . $relativePath, 'width'),
                'height' => $this->imageDimension(FCPATH . $relativePath, 'height'),
                'is_premium' => $this->request->getPost('is_premium') === '1' ? 1 : 0,
                'usage_count' => 0,
            ]);

            $model = new EditorAssetModel();
            $id = (int) $model->insert($asset, true);
            if ($id <= 0) {
                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Asset sudah terupload, tetapi gagal disimpan ke database.',
                    ]);
            }
            $asset['id'] = $id;
        } catch (Throwable $error) {
            log_message('error', 'Editor asset upload failed: {message}', ['message' => $error->getMessage()]);

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Upload asset gagal diproses server: ' . $error->getMessage(),
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Asset berhasil diupload.',
            'item' => $this->serializeAsset($asset),
        ]);
    }

    public function delete(int $id): ResponseInterface
    {
        if (! $this->canManageAssets() && ! $this->hasValidAdminUploadToken()) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Hapus ornament hanya tersedia untuk admin. Silakan login ulang sebagai admin jika sesi sudah berubah.',
                ]);
        }

        if ($id <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Asset tidak valid.']);
        }

        $db = Database::connect();
        if (! $db->tableExists('editor_assets')) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'Tabel editor_assets belum tersedia.']);
        }

        $model = new EditorAssetModel();
        $asset = $model->find($id);
        if ($asset === null || (int) ($asset['is_active'] ?? 1) !== 1) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => 'Asset tidak ditemukan.']);
        }

        if (! $model->update($id, ['is_active' => 0])) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'Ornament gagal dihapus.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Asset berhasil dihapus.',
            'id' => (string) $id,
        ]);
    }

    public function updateCategory(int $id): ResponseInterface
    {
        if (! $this->canManageAssets() && ! $this->hasValidAdminUploadToken()) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Pengaturan asset hanya tersedia untuk admin.',
                ]);
        }

        if ($id <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Asset tidak valid.']);
        }

        $categorySlug = trim((string) $this->request->getPost('category'));
        $category = $this->findCategory($categorySlug);
        if ($category === null) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Kategori asset tidak valid.']);
        }
        $db = Database::connect();
        if (! $db->tableExists('editor_assets')) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'Tabel editor_assets belum tersedia.']);
        }

        $model = new EditorAssetModel();
        $asset = $model->find($id);
        if ($asset === null || (int) ($asset['is_active'] ?? 1) !== 1) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => 'Asset tidak ditemukan.']);
        }

        $type = strtolower(trim((string) ($this->request->getPost('type') ?? ($asset['type'] ?? 'ornament'))));
        if (! in_array($type, $this->allowedTypes, true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Tipe asset tidak valid.']);
        }

        $categoryFolder = $this->slugify((string) ($category['slug'] ?? $category['name'] ?? 'asset'));
        $payload = [
            'type' => $type,
            'category_id' => (int) ($category['id'] ?? 0),
            'category_name' => (string) ($category['name'] ?? $categoryFolder),
            'category_slug' => $categoryFolder,
        ];
        if ($this->hasEditorAssetColumn('is_premium')) {
            $payload['is_premium'] = $this->request->getPost('is_premium') === '1' ? 1 : 0;
        }

        if (! $model->update($id, $payload)) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'Kategori asset gagal diperbarui.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pengaturan asset diperbarui.',
            'item' => $this->serializeAsset(array_merge($asset, $payload)),
        ]);
    }

    public function trackUsage(int $id): ResponseInterface
    {
        if ($id <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => 'Asset tidak valid.']);
        }

        $db = Database::connect();
        if (! $db->tableExists('editor_assets') || ! $this->hasEditorAssetColumn('usage_count')) {
            return $this->response->setJSON(['success' => true, 'skipped' => true]);
        }

        $builder = $db->table('editor_assets');
        $updated = $builder
            ->where('id', $id)
            ->where('is_active', 1)
            ->set('usage_count', 'usage_count + 1', false)
            ->update();

        return $this->response->setJSON([
            'success' => (bool) $updated,
            'id' => (string) $id,
        ]);
    }

    private function categories(): array
    {
        $items = array_map(static fn (array $category): array => [
            'id' => 0,
            'name' => $category['name'],
            'slug' => $category['slug'],
        ], $this->defaultAssetCategories);

        if (! Database::connect()->tableExists('categories')) {
            return $items;
        }

        $templateCategories = array_map(static fn (array $category): array => [
            'id' => (int) ($category['id'] ?? 0),
            'name' => (string) ($category['name'] ?? ''),
            'slug' => (string) ($category['slug'] ?? ''),
        ], (new CategoryModel())->templateOptions());

        foreach ($templateCategories as $category) {
            $slug = $this->slugify((string) ($category['slug'] ?? $category['name'] ?? ''));
            if ($slug === '') {
                continue;
            }
            if (! array_filter($items, static fn (array $item): bool => $item['slug'] === $slug)) {
                $category['slug'] = $slug;
                $items[] = $category;
            }
        }

        return $items;
    }

    private function findCategory(string $slug): ?array
    {
        $slug = $this->slugify($slug);
        if ($slug === '') {
            return null;
        }

        foreach ($this->defaultAssetCategories as $category) {
            if ($category['slug'] === $slug) {
                return [
                    'id' => 0,
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                ];
            }
        }

        if (! Database::connect()->tableExists('categories')) {
            return null;
        }

        foreach ((new CategoryModel())->templateOptions() as $category) {
            if ($this->slugify((string) ($category['slug'] ?? $category['name'] ?? '')) === $slug) {
                return $category;
            }
        }

        return null;
    }

    private function serializeAsset(array $item): array
    {
        $src = (string) ($item['file_url'] ?? '');
        if ($src === '' && ! empty($item['file_path'])) {
            $src = base_url(ltrim((string) $item['file_path'], '/'));
        }

        return [
            'id' => (string) ($item['id'] ?? ''),
            'type' => (string) ($item['type'] ?? 'ornament'),
            'category' => (string) ($item['category_slug'] ?? $item['category_name'] ?? ''),
            'categoryName' => (string) ($item['category_name'] ?? ''),
            'name' => (string) ($item['title'] ?? $item['file_name'] ?? 'Asset'),
            'src' => $src,
            'thumbnail' => ! empty($item['thumbnail_path']) ? base_url(ltrim((string) $item['thumbnail_path'], '/')) : $src,
            'mimeType' => (string) ($item['mime_type'] ?? ''),
            'packName' => (string) ($item['pack_name'] ?? ''),
            'sourceName' => (string) ($item['source_name'] ?? ''),
            'sourceUrl' => (string) ($item['source_url'] ?? ''),
            'license' => (string) ($item['license'] ?? ''),
            'isPremium' => (int) ($item['is_premium'] ?? 0) === 1,
            'usageCount' => (int) ($item['usage_count'] ?? 0),
            'createdAt' => (string) ($item['created_at'] ?? ''),
            'width' => (int) ($item['width'] ?? 0),
            'height' => (int) ($item['height'] ?? 0),
            'tags' => array_values(array_unique(array_filter(array_merge([
                $item['type'] ?? null,
                $item['category_slug'] ?? null,
                $item['category_name'] ?? null,
                $item['title'] ?? null,
                $item['pack_name'] ?? null,
            ], $this->splitTags((string) ($item['tags'] ?? '')))))),
        ];
    }

    private function canManageAssets(): bool
    {
        return in_array($this->currentRole(), $this->assetManagerRoles, true);
    }

    private function currentRole(): string
    {
        $role = strtolower(trim((string) (session()->get('userRole') ?? '')));
        if ($role === '') {
            $userId = (int) (session()->get('userId') ?? 0);
            if ($userId <= 0) {
                return false;
            }
            $user = (new UserModel())->find($userId);
            $role = strtolower(trim((string) ($user['role'] ?? 'user')));
            session()->set('userRole', $role);
        }

        return $role;
    }

    private function hasValidAdminUploadToken(): bool
    {
        $token = trim((string) ($this->request->getPost('admin_upload_token') ?? ''));
        if ($token === '' || ! str_contains($token, '.')) {
            return false;
        }

        [$encoded, $signature] = explode('.', $token, 2);
        $expected = hash_hmac('sha256', $encoded, $this->editorAssetUploadSecret());
        if (! hash_equals($expected, $signature)) {
            return false;
        }

        $base64 = strtr($encoded, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);
        $json = base64_decode($base64, true);
        $payload = is_string($json) ? json_decode($json, true) : null;
        if (! is_array($payload) || (int) ($payload['exp'] ?? 0) < time()) {
            return false;
        }

        if (! in_array((string) ($payload['role'] ?? ''), $this->assetManagerRoles, true)) {
            return false;
        }

        $userId = (int) ($payload['uid'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        $user = (new UserModel())->find($userId);

        return in_array(strtolower(trim((string) ($user['role'] ?? 'user'))), $this->assetManagerRoles, true);
    }

    private function hasEditorAssetColumn(string $column): bool
    {
        static $columns = null;
        if ($columns === null) {
            $db = Database::connect();
            $columns = $db->tableExists('editor_assets') ? $db->getFieldNames('editor_assets') : [];
        }

        return in_array($column, $columns, true);
    }

    private function optionalAssetMetadataPayload(array $payload): array
    {
        $filtered = [];
        foreach ($payload as $key => $value) {
            if (in_array($key, $this->metadataColumns, true) && $this->hasEditorAssetColumn($key)) {
                $filtered[$key] = $value === '' ? null : $value;
            }
        }

        return $filtered;
    }

    private function cleanText(string $value, int $limit = 160): string
    {
        return mb_substr(trim(strip_tags($value)), 0, $limit);
    }

    private function cleanUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '' || ! filter_var($value, FILTER_VALIDATE_URL)) {
            return '';
        }

        return mb_substr($value, 0, 500);
    }

    private function normalizeTags(string $value): string
    {
        return implode(',', array_slice($this->splitTags($value), 0, 30));
    }

    private function splitTags(string $value): array
    {
        $parts = preg_split('/[,;\n]+/', $value) ?: [];
        $tags = [];
        foreach ($parts as $part) {
            $tag = $this->cleanText((string) $part, 40);
            if ($tag !== '') {
                $tags[] = $tag;
            }
        }

        return array_values(array_unique($tags));
    }

    private function imageDimension(string $path, string $axis): ?int
    {
        if (! is_file($path) || str_ends_with(strtolower($path), '.svg')) {
            return null;
        }

        $size = @getimagesize($path);
        if (! is_array($size)) {
            return null;
        }

        return $axis === 'height' ? (int) ($size[1] ?? 0) : (int) ($size[0] ?? 0);
    }

    private function editorAssetUploadSecret(): string
    {
        return (string) (env('encryption.key') ?: FCPATH);
    }

    private function ensureUploadDirectory(string $path): ?string
    {
        if (! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
            return 'Folder upload asset tidak bisa dibuat: ' . $path;
        }

        if (! is_writable($path)) {
            return 'Folder upload asset tidak writable: ' . $path;
        }

        return null;
    }

    private function uploadErrorMessage($file, string $label, int $maxMegabytes): string
    {
        $error = method_exists($file, 'getError') ? (int) $file->getError() : UPLOAD_ERR_NO_FILE;
        if (in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return ucfirst($label) . ' gagal diupload karena melebihi batas server. Gunakan file maksimal ' . $maxMegabytes . 'MB.';
        }

        if ($error === UPLOAD_ERR_NO_FILE) {
            return 'Pilih file ' . $label . ' terlebih dahulu.';
        }

        if ($error === UPLOAD_ERR_PARTIAL) {
            return ucfirst($label) . ' gagal diupload karena koneksi terputus. Coba upload ulang.';
        }

        $detail = method_exists($file, 'getErrorString') ? trim((string) $file->getErrorString()) : '';
        return ucfirst($label) . ' tidak valid atau gagal diupload.' . ($detail !== '' ? ' ' . $detail : '');
    }

    private function slugify(string $value): string
    {
        helper('url');

        return url_title(trim($value), '-', true);
    }

    private function svgLooksUnsafe(string $svg): bool
    {
        return preg_match('/<\s*script\b|on\w+\s*=|javascript\s*:|<\s*foreignObject\b/i', $svg) === 1;
    }
}
