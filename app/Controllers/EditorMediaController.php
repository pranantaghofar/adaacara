<?php

namespace App\Controllers;

use App\Models\AppSettingModel;
use App\Models\MediaModel;
use App\Models\UserSubscriptionModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class EditorMediaController extends BaseController
{
    private ?array $editorAiSettings = null;
    private ?bool $mediaDeletedAtColumnExists = null;

    public function index(): ResponseInterface
    {
        if (! Database::connect()->tableExists('media_library')) {
            return $this->response->setJSON(['data' => []]);
        }

        $userId = (int) session()->get('userId');
        $mediaModel = new MediaModel();
        $mediaModel->where('user_id', $userId);
        if ($this->mediaLibraryHasDeletedAt()) {
            $mediaModel->where('deleted_at', null);
        }
        $media = $mediaModel
            ->orderBy('id', 'DESC')
            ->findAll(120);

        return $this->response->setJSON([
            'data' => $this->mediaAssetsForResponse($media, $userId),
        ]);
    }

    private function mediaAssetsForResponse(array $media, int $userId): array
    {
        $assets = [];
        $model = new MediaModel();
        $mediaBase = realpath(FCPATH . 'uploads/media/' . $userId);

        foreach ($media as $item) {
            $relativePath = ltrim((string) ($item['file_path'] ?? ''), '/');
            $src = trim((string) ($item['file_url'] ?? ''));
            $absolutePath = $relativePath !== '' ? FCPATH . $relativePath : '';
            $isLocalMedia = $relativePath !== '' && str_starts_with($relativePath, 'uploads/media/' . $userId . '/');

            if ($isLocalMedia) {
                $realFile = is_file($absolutePath) ? realpath($absolutePath) : false;
                if ($realFile === false || $mediaBase === false || ! str_starts_with($realFile, $mediaBase)) {
                    $model->delete((int) ($item['id'] ?? 0));
                    log_message('warning', 'Media library stale file removed: {path}', ['path' => $relativePath]);
                    continue;
                }

                $version = (string) (filemtime($realFile) ?: time());
                $src = base_url($relativePath) . '?v=' . rawurlencode($version);
            }

            if ($src === '') {
                continue;
            }

            $assets[] = [
                'id' => (int) $item['id'],
                'src' => $src,
                'name' => $item['file_name'],
                'type' => 'image',
                'mime' => (string) ($item['file_type'] ?? ''),
                'size' => (int) ($item['file_size'] ?? 0),
            ];
        }

        return $assets;
    }

    private function mediaLibraryHasDeletedAt(): bool
    {
        if ($this->mediaDeletedAtColumnExists !== null) {
            return $this->mediaDeletedAtColumnExists;
        }

        if (! Database::connect()->tableExists('media_library')) {
            return $this->mediaDeletedAtColumnExists = false;
        }

        return $this->mediaDeletedAtColumnExists = in_array('deleted_at', Database::connect()->getFieldNames('media_library'), true);
    }

    public function upload(): ResponseInterface
    {
        if (! Database::connect()->tableExists('media_library')) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'data' => [],
                    'message' => 'Tabel media_library belum tersedia. Jalankan SQL alter_editor_media_library.sql terlebih dahulu.',
                ]);
        }

        $file = $this->request->getFile('file');
        if (! $file) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'data' => [],
                    'message' => 'File gambar tidak ditemukan. Pilih file JPG, PNG, WEBP, atau GIF.',
                ]);
        }

        if (! $file->isValid()) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'data' => [],
                    'message' => $this->uploadErrorMessage($file, 'gambar', 3),
                ]);
        }

        $extension = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
        $imageInfo = @getimagesize($file->getTempName());
        $detectedMime = strtolower((string) ($imageInfo['mime'] ?? ''));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowedMimes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if ($file->getSize() > 3 * 1024 * 1024) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'data' => [],
                    'message' => 'Upload gambar maksimal 3MB.',
                ]);
        }

        if (! in_array($extension, $allowedExtensions, true) || ! in_array($detectedMime, $allowedMimes, true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'data' => [],
                    'message' => 'Format gambar tidak sesuai. Gunakan file JPG, PNG, WEBP, atau GIF.',
                ]);
        }

        $userId = (int) session()->get('userId');
        $uploadPath = FCPATH . 'uploads/media/' . $userId;

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $originalName = $file->getClientName();
        $savedName = $file->getRandomName();
        $fileSize = $file->getSize();
        $mimeType = $detectedMime ?: ($file->getMimeType() ?: $file->getClientMimeType());
        $file->move($uploadPath, $savedName);

        $absolutePath = $uploadPath . DIRECTORY_SEPARATOR . $savedName;
        $relativePath = 'uploads/media/' . $userId . '/' . $savedName;

        $webpPath = $this->convertToWebpIfPossible($absolutePath, $mimeType);
        if ($webpPath !== null) {
            @unlink($absolutePath);
            $savedName = basename($webpPath);
            $absolutePath = $webpPath;
            $relativePath = 'uploads/media/' . $userId . '/' . $savedName;
            $mimeType = 'image/webp';
            $fileSize = is_file($absolutePath) ? filesize($absolutePath) : $fileSize;
        }

        $url = base_url($relativePath);
        $displayName = $webpPath !== null
            ? pathinfo($originalName, PATHINFO_FILENAME) . '.webp'
            : $originalName;

        (new MediaModel())->insert([
            'user_id' => $userId,
            'file_name' => $displayName,
            'file_path' => $relativePath,
            'file_url' => $url,
            'file_type' => $mimeType,
            'file_size' => $fileSize,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                [
                    'src' => $url,
                    'name' => $displayName,
                    'type' => 'image',
                    'mime' => $mimeType,
                    'mimeType' => $mimeType,
                ],
            ],
        ]);
    }

    public function magicLayerTempUpload(): ResponseInterface
    {
        if (! $this->magicLayerEnabled()) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Magic Layer sedang nonaktif.',
                ]);
        }

        if (! $this->canUseRemoveBackground()) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Magic Layer hanya tersedia untuk akun membership aktif atau admin.',
                ]);
        }

        $rules = [
            'file' => [
                'label' => 'Gambar Magic Layer',
                'rules' => 'uploaded[file]|max_size[file,2048]|is_image[file]|mime_in[file,image/jpg,image/jpeg,image/png,image/webp]|ext_in[file,jpg,jpeg,png,webp]',
                'errors' => [
                    'max_size' => 'Upload Magic Layer maksimal 2MB.',
                    'is_image' => 'Format Magic Layer harus JPG, PNG, atau WEBP.',
                    'mime_in' => 'Format Magic Layer harus JPG, PNG, atau WEBP.',
                    'ext_in' => 'Format Magic Layer harus JPG, PNG, atau WEBP.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => implode(' ', $this->validator->getErrors()),
                ]);
        }

        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid()) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'File Magic Layer tidak valid.',
                ]);
        }

        $userId = (int) session()->get('userId');
        if ($userId <= 0) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Login diperlukan untuk Magic Layer.',
                ]);
        }

        $uploadPath = FCPATH . 'uploads/media/' . $userId;
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $originalName = $file->getClientName();
        $mimeType = $file->getMimeType();
        $savedName = 'magic-source-' . date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . strtolower($file->getClientExtension() ?: 'png');
        $file->move($uploadPath, $savedName);

        $absolutePath = $uploadPath . DIRECTORY_SEPARATOR . $savedName;
        @chmod($absolutePath, 0644);
        clearstatcache(true, $absolutePath);

        if (! is_file($absolutePath) || ! is_readable($absolutePath) || (filesize($absolutePath) ?: 0) <= 0) {
            @unlink($absolutePath);

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan file Magic Layer ke media library.',
                ]);
        }

        $relativePath = 'uploads/media/' . $userId . '/' . $savedName;
        $url = base_url($relativePath);
        $publicUrl = $url . '?v=' . rawurlencode((string) (filemtime($absolutePath) ?: time()));
        $fileSize = filesize($absolutePath) ?: $file->getSize();
        $displayName = $originalName ?: $savedName;

        $mediaId = (new MediaModel())->insert([
            'user_id' => $userId,
            'file_name' => $displayName,
            'file_path' => $relativePath,
            'file_url' => $url,
            'file_type' => $mimeType,
            'file_size' => $fileSize,
        ], true);

        return $this->response->setJSON([
            'success' => true,
            'data' => [[
                'id' => (int) $mediaId,
                'src' => $publicUrl,
                'name' => $displayName,
                'type' => 'image',
                'temporary' => false,
            ]],
        ]);
    }

    public function deleteMagicLayerTemp(): ResponseInterface
    {
        $imageUrl = trim((string) ($this->request->getPost('image_url') ?? ''));
        $path = $this->localImagePathFromUrl($imageUrl, true);
        $realBase = realpath(FCPATH . 'uploads/magic-layer-temp');

        if ($path !== null && $realBase !== false) {
            $realFile = realpath($path);
            if ($realFile && str_starts_with($realFile, $realBase) && is_file($realFile)) {
                @unlink($realFile);
            }
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function delete(int $id): ResponseInterface
    {
        if (! Database::connect()->tableExists('media_library')) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => 'Tabel media_library belum tersedia.']);
        }

        $model = new MediaModel();
        $media = $model
            ->where('id', $id)
            ->where('user_id', (int) session()->get('userId'))
            ->first();

        if (! $media) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => 'Media tidak ditemukan.']);
        }

        if ($this->mediaLibraryHasDeletedAt()) {
            $model->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            $model->delete($id);
        }

        return $this->response->setJSON([
            'success' => true,
            'trashed' => true,
            'message' => 'Media dipindahkan ke trash. File tetap disimpan agar desain yang memakai gambar ini tidak rusak.',
        ]);
    }

    public function removeBackground(): ResponseInterface
    {
        if (! $this->canUseRemoveBackground()) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Remove BG hanya tersedia untuk akun membership aktif atau admin.',
                ]);
        }

        if (! Database::connect()->tableExists('media_library')) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Tabel media_library belum tersedia.',
                ]);
        }

        $provider = $this->removeBgProvider();
        $providers = $this->configuredRemoveBgProviders(
            $this->removeBgProviderChain($provider, $this->removeBgFallbackProvider()),
            'Remove BG'
        );
        if ($providers === []) {
            $configError = $this->removeBgProviderConfigError($provider, 'Remove BG');
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => $configError ?? 'Provider Remove BG belum dikonfigurasi.',
                ]);
        }

        $imageUrl = trim((string) $this->request->getPost('image_url'));
        $sourcePath = $this->localImagePathFromUrl($imageUrl);
        if ($sourcePath === null || ! is_file($sourcePath)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Gambar harus berasal dari media/upload lokal AdaAcara.',
                ]);
        }

        $maxBytes = $this->removeBgMaxBytes();
        $sourceSize = filesize($sourcePath) ?: 0;
        if ($sourceSize <= 0 || $sourceSize > $maxBytes) {
            return $this->response
                ->setStatusCode(413)
                ->setJSON([
                    'success' => false,
                    'message' => 'Ukuran gambar terlalu besar untuk Remove BG.',
                ]);
        }

        $imageInfo = @getimagesize($sourcePath);
        $mimeType = strtolower((string) ($imageInfo['mime'] ?? ''));
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return $this->response
                ->setStatusCode(415)
                ->setJSON([
                    'success' => false,
                    'message' => 'Remove BG hanya mendukung JPG, PNG, atau WEBP.',
                ]);
        }

        try {
            $result = $this->callRemoveBackgroundProviderWithFallback($providers, $sourcePath, $mimeType, 'Remove BG');
            $provider = $result['provider'];
            $pngBytes = $result['bytes'];
            $asset = $this->storeRemovedBackgroundImage($pngBytes, basename($sourcePath));
        } catch (\Throwable $error) {
            log_message('error', 'Remove BG failed: {message}', ['message' => $error->getMessage()]);

            return $this->response
                ->setStatusCode(502)
                ->setJSON([
                    'success' => false,
                    'message' => $this->removeBgErrorMessage($provider, $error),
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'src' => $asset['src'],
            'name' => $asset['name'],
            'data' => [$asset],
            'provider' => $provider,
            'fallback_used' => ($providers[0] ?? $provider) !== $provider,
        ]);
    }

    private function canUseRemoveBackground(): bool
    {
        $userId = (int) session()->get('userId');
        if ($userId <= 0) {
            return false;
        }

        $role = strtolower(trim((string) (session()->get('userRole') ?? '')));
        if (in_array($role, ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin'], true)) {
            return true;
        }

        if ((new UserSubscriptionModel())->activeWithPlanByUser($userId) !== null) {
            return true;
        }

        return false;
    }

    private function removeBgProvider(): string
    {
        $provider = trim((string) ($this->editorAiSettingValue('remove_bg_provider') ?? ''));
        if ($provider === '') {
            $provider = trim((string) env('REMOVE_BG_PROVIDER', ''));
        }
        if ($provider === '') {
            $provider = 'poof';
        }

        return $this->normalizeRemoveBgProvider($provider);
    }

    private function magicLayerEnabled(): bool
    {
        return filter_var(env('MAGIC_LAYER_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function magicLayerProvider(): string
    {
        $provider = trim((string) ($this->editorAiSettingValue('magic_layer_provider') ?? ''));
        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower($provider)) ?: '';
        if ($provider === '' || in_array($normalized, ['inherit', 'same', 'follow', 'default'], true)) {
            $provider = trim((string) env('MAGIC_LAYER_PROVIDER', ''));
        }

        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower($provider)) ?: '';
        if ($provider === '' || in_array($normalized, ['inherit', 'same', 'follow', 'default'], true)) {
            return $this->removeBgProvider();
        }

        return $this->normalizeRemoveBgProvider($provider);
    }

    private function removeBgFallbackProvider(): ?string
    {
        $provider = trim((string) ($this->editorAiSettingValue('remove_bg_fallback_provider') ?? ''));
        if ($provider === '') {
            $provider = trim((string) env('REMOVE_BG_FALLBACK_PROVIDER', ''));
        }

        return $this->normalizeOptionalRemoveBgProvider($provider);
    }

    private function magicLayerFallbackProvider(): ?string
    {
        $provider = trim((string) ($this->editorAiSettingValue('magic_layer_fallback_provider') ?? ''));
        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower($provider)) ?: '';
        if ($provider === '' || in_array($normalized, ['inherit', 'same', 'follow', 'default'], true)) {
            $provider = trim((string) env('MAGIC_LAYER_FALLBACK_PROVIDER', ''));
        }

        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower($provider)) ?: '';
        if ($provider === '' || in_array($normalized, ['inherit', 'same', 'follow', 'default'], true)) {
            return $this->removeBgFallbackProvider();
        }

        return $this->normalizeOptionalRemoveBgProvider($provider);
    }

    private function editorAiSettingValue(string $key): ?string
    {
        if ($this->editorAiSettings === null) {
            try {
                $this->editorAiSettings = (new AppSettingModel())->getSettings();
            } catch (\Throwable $error) {
                log_message('warning', 'Editor AI settings unavailable: {message}', ['message' => $error->getMessage()]);
                $this->editorAiSettings = [];
            }
        }

        if (! array_key_exists($key, $this->editorAiSettings)) {
            return null;
        }

        return (string) $this->editorAiSettings[$key];
    }

    private function normalizeRemoveBgProvider(string $provider): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower(trim($provider))) ?: '';
        if (in_array($normalized, ['poof', 'poofbg'], true)) {
            return 'poof';
        }

        if (in_array($normalized, ['removebg', 'removebgapi'], true)) {
            return 'removebg';
        }

        if (in_array($normalized, ['rembg', 'local', 'localrembg', 'selfhosted', 'selfhost'], true)) {
            return 'rembg';
        }

        return 'poof';
    }

    private function normalizeOptionalRemoveBgProvider(string $provider): ?string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower(trim($provider))) ?: '';
        if (in_array($normalized, ['', 'none', 'off', 'disabled', 'disable'], true)) {
            return null;
        }

        return $this->normalizeRemoveBgProvider($provider);
    }

    private function removeBgProviderChain(string $primary, ?string $fallback): array
    {
        $providers = [$primary];
        if ($fallback !== null && $fallback !== $primary) {
            $providers[] = $fallback;
        }

        return array_values(array_unique($providers));
    }

    private function configuredRemoveBgProviders(array $providers, string $feature): array
    {
        $configured = [];
        foreach ($providers as $provider) {
            if ($this->removeBgProviderConfigError($provider, $feature) === null) {
                $configured[] = $provider;
            }
        }

        return $configured;
    }

    private function removeBgProviderConfigError(string $provider, string $feature): ?string
    {
        if ($provider === 'poof') {
            $apiKey = trim((string) env('POOF_BG_API_KEY', ''));
            $endpoint = trim((string) env('POOF_BG_ENDPOINT', 'https://api.poof.bg/v1/remove'));

            return $apiKey === '' || $endpoint === ''
                ? 'Poof ' . $feature . ' belum dikonfigurasi. Isi POOF_BG_API_KEY di .env.'
                : null;
        }

        if ($provider === 'removebg') {
            return $this->removeBgApiKey() === ''
                ? 'Remove.bg ' . $feature . ' belum dikonfigurasi. Isi REMOVE_BG_API_KEY di .env.'
                : null;
        }

        $serviceUrl = trim((string) env('REMBG_SERVICE_URL', ''));
        $serviceToken = trim((string) env('REMBG_SERVICE_TOKEN', ''));

        return $serviceUrl === '' || $serviceToken === ''
            ? 'Service ' . $feature . ' belum dikonfigurasi. Isi REMBG_SERVICE_URL dan REMBG_SERVICE_TOKEN di .env.'
            : null;
    }

    private function removeBgApiKey(): string
    {
        $apiKey = trim((string) env('REMOVE_BG_API_KEY', ''));
        if ($apiKey === '') {
            $apiKey = trim((string) env('REMOVEBG_API_KEY', ''));
        }

        return $apiKey;
    }

    private function removeBgEndpoint(): string
    {
        $endpoint = trim((string) env('REMOVE_BG_ENDPOINT', ''));
        if ($endpoint === '') {
            $endpoint = trim((string) env('REMOVEBG_ENDPOINT', ''));
        }

        return $endpoint !== '' ? $endpoint : 'https://api.remove.bg/v1.0/removebg';
    }

    private function removeBgMaxBytes(): int
    {
        $value = env('REMOVE_BG_MAX_BYTES');
        if ($value === null || $value === '') {
            $value = env('REMBG_MAX_BYTES', 5 * 1024 * 1024);
        }

        return max(1024, (int) $value);
    }

    private function removeBgTimeout(): int
    {
        $value = env('REMOVE_BG_TIMEOUT');
        if ($value === null || $value === '') {
            $value = env('REMBG_TIMEOUT', 45);
        }

        return max(5, (int) $value);
    }

    private function removeBgErrorMessage(string $provider, \Throwable $error): string
    {
        $message = strtolower($error->getMessage());
        if ($provider === 'poof') {
            if (str_contains($message, '401')) {
                return 'API key Poof Remove BG tidak valid.';
            }
            if (str_contains($message, '402')) {
                return 'Credit Poof Remove BG tidak cukup.';
            }
            if (str_contains($message, '429')) {
                return 'Poof Remove BG sedang terlalu ramai. Coba lagi sebentar.';
            }

            return 'Remove BG gagal diproses oleh Poof. Coba lagi sebentar.';
        }

        if ($provider === 'removebg') {
            if (str_contains($message, '401') || str_contains($message, '403')) {
                return 'API key Remove.bg tidak valid atau tidak punya akses.';
            }
            if (str_contains($message, '402')) {
                return 'Credit Remove.bg tidak cukup.';
            }
            if (str_contains($message, '429')) {
                return 'Remove.bg sedang terlalu ramai. Coba lagi sebentar.';
            }

            return 'Remove BG gagal diproses oleh Remove.bg. Coba lagi sebentar.';
        }

        return 'Remove BG gagal diproses. Pastikan service rembg sedang berjalan.';
    }

    private function localImagePathFromUrl(string $url, bool $allowMagicLayerTemp = false): ?string
    {
        if ($url === '') {
            return null;
        }

        $baseUrl = rtrim(base_url('/'), '/');
        if (str_starts_with($url, $baseUrl)) {
            $path = parse_url($url, PHP_URL_PATH);
        } elseif (str_starts_with($url, '/')) {
            $path = $url;
        } elseif (str_starts_with($url, 'uploads/')) {
            $path = '/' . $url;
        } else {
            return null;
        }

        $relative = ltrim(rawurldecode((string) $path), '/');
        $userId = (int) session()->get('userId');
        $role = strtolower(trim((string) (session()->get('userRole') ?? '')));
        $mediaPrefix = 'uploads/media/' . $userId . '/';
        $tempPrefix = 'uploads/magic-layer-temp/' . $userId . '/';
        $isMediaPath = str_starts_with($relative, 'uploads/media/');
        $isTempPath = $allowMagicLayerTemp && str_starts_with($relative, 'uploads/magic-layer-temp/');

        if (! $isMediaPath && ! $isTempPath) {
            return null;
        }

        if (! in_array($role, ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin'], true) && $isMediaPath && ! str_starts_with($relative, $mediaPrefix)) {
            return null;
        }

        if ($isTempPath && ! str_starts_with($relative, $tempPrefix)) {
            return null;
        }

        $absolute = FCPATH . $relative;
        $realFile = realpath($absolute);
        $realBase = realpath(FCPATH . ($isTempPath ? 'uploads/magic-layer-temp' : 'uploads/media'));
        if (! $realFile || ! $realBase || ! str_starts_with($realFile, $realBase) || ! is_file($realFile)) {
            return null;
        }

        return $realFile;
    }

    private function cleanupOldMagicLayerTempFiles(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $directory = FCPATH . 'uploads/magic-layer-temp/' . $userId;
        if (! is_dir($directory)) {
            return;
        }

        $now = time();
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
            if (! is_file($path)) {
                continue;
            }

            $age = $now - (filemtime($path) ?: $now);
            if ($age > 3600) {
                @unlink($path);
            }
        }
    }

    private function callRembgService(string $serviceUrl, string $serviceToken, string $sourcePath, string $mimeType): string
    {
        if (! function_exists('curl_init')) {
            throw new \RuntimeException('PHP cURL extension is not available.');
        }

        $timeout = $this->removeBgTimeout();
        $curl = curl_init($serviceUrl);
        if ($curl === false) {
            throw new \RuntimeException('Failed to initialize cURL.');
        }

        $postFields = [
            'file' => new \CURLFile($sourcePath, $mimeType, basename($sourcePath)),
        ];

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $serviceToken,
                'Accept: image/png, application/json',
            ],
        ]);

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false || $status < 200 || $status >= 300) {
            throw new \RuntimeException($error ?: 'rembg service returned HTTP ' . $status);
        }

        if (! is_string($body) || $body === '') {
            throw new \RuntimeException('rembg service returned empty response.');
        }

        if (! str_starts_with($body, "\x89PNG\r\n\x1a\n")) {
            throw new \RuntimeException('rembg service did not return a PNG image.');
        }

        return $body;
    }

    private function callRemoveBackgroundProvider(string $provider, string $sourcePath, string $mimeType): string
    {
        if ($provider === 'removebg') {
            return $this->callRemoveBgApiService(
                $this->removeBgEndpoint(),
                $this->removeBgApiKey(),
                $sourcePath,
                $mimeType
            );
        }

        if ($provider === 'rembg') {
            return $this->callRembgService(
                trim((string) env('REMBG_SERVICE_URL', '')),
                trim((string) env('REMBG_SERVICE_TOKEN', '')),
                $sourcePath,
                $mimeType
            );
        }

        return $this->callPoofBgService(
            trim((string) env('POOF_BG_ENDPOINT', 'https://api.poof.bg/v1/remove')),
            trim((string) env('POOF_BG_API_KEY', '')),
            $sourcePath,
            $mimeType
        );
    }

    private function callRemoveBackgroundProviderWithFallback(array $providers, string $sourcePath, string $mimeType, string $feature): array
    {
        $lastError = null;
        foreach ($providers as $index => $provider) {
            try {
                if ($index > 0) {
                    log_message('warning', '{feature} mencoba fallback provider {provider}.', [
                        'feature' => $feature,
                        'provider' => $provider,
                    ]);
                }

                return [
                    'bytes' => $this->callRemoveBackgroundProvider($provider, $sourcePath, $mimeType),
                    'provider' => $provider,
                ];
            } catch (\Throwable $error) {
                $lastError = $error;
                log_message('error', '{feature} provider {provider} gagal: {message}', [
                    'feature' => $feature,
                    'provider' => $provider,
                    'message' => $error->getMessage(),
                ]);
            }
        }

        throw $lastError ?? new \RuntimeException($feature . ' provider failed.');
    }

    private function callPoofBgService(string $endpoint, string $apiKey, string $sourcePath, string $mimeType): string
    {
        if (! function_exists('curl_init')) {
            throw new \RuntimeException('PHP cURL extension is not available.');
        }

        $curl = curl_init($endpoint);
        if ($curl === false) {
            throw new \RuntimeException('Failed to initialize cURL.');
        }

        $postFields = [
            'image_file' => new \CURLFile($sourcePath, $mimeType, basename($sourcePath)),
            'format'     => 'png',
            'channels'   => 'rgba',
            'size'       => trim((string) env('POOF_BG_SIZE', 'full')) ?: 'full',
            'crop'       => 'false',
        ];

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => $this->removeBgTimeout(),
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $apiKey,
                'Accept: image/png, application/json',
            ],
        ]);

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false || $status < 200 || $status >= 300) {
            $message = $error ?: 'Poof service returned HTTP ' . $status;
            $apiMessage = $this->extractApiErrorMessage($body);
            if ($apiMessage !== '') {
                $message .= ': ' . $apiMessage;
            }

            throw new \RuntimeException($message);
        }

        if (! is_string($body) || $body === '') {
            throw new \RuntimeException('Poof service returned empty response.');
        }

        if (! str_starts_with($body, "\x89PNG\r\n\x1a\n")) {
            throw new \RuntimeException('Poof service did not return a PNG image.');
        }

        return $body;
    }

    private function callRemoveBgApiService(string $endpoint, string $apiKey, string $sourcePath, string $mimeType): string
    {
        if (! function_exists('curl_init')) {
            throw new \RuntimeException('PHP cURL extension is not available.');
        }

        $curl = curl_init($endpoint);
        if ($curl === false) {
            throw new \RuntimeException('Failed to initialize cURL.');
        }

        $postFields = [
            'image_file' => new \CURLFile($sourcePath, $mimeType, basename($sourcePath)),
            'format' => 'png',
            'size' => trim((string) env('REMOVE_BG_SIZE', 'auto')) ?: 'auto',
        ];

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => $this->removeBgTimeout(),
            CURLOPT_HTTPHEADER => [
                'X-Api-Key: ' . $apiKey,
                'Accept: image/png, application/json',
            ],
        ]);

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false || $status < 200 || $status >= 300) {
            $message = $error ?: 'Remove.bg service returned HTTP ' . $status;
            $apiMessage = $this->extractApiErrorMessage($body);
            if ($apiMessage !== '') {
                $message .= ': ' . $apiMessage;
            }

            throw new \RuntimeException($message);
        }

        if (! is_string($body) || $body === '') {
            throw new \RuntimeException('Remove.bg service returned empty response.');
        }

        if (! str_starts_with($body, "\x89PNG\r\n\x1a\n")) {
            throw new \RuntimeException('Remove.bg service did not return a PNG image.');
        }

        return $body;
    }

    private function extractApiErrorMessage($body): string
    {
        if (! is_string($body) || $body === '') {
            return '';
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            return trim(strip_tags(substr($body, 0, 220)));
        }

        foreach (['message', 'detail', 'title'] as $key) {
            $value = $decoded[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        $error = $decoded['error'] ?? null;
        if (is_string($error) && trim($error) !== '') {
            return trim($error);
        }
        if (is_array($error)) {
            foreach (['message', 'detail', 'title', 'code'] as $key) {
                $value = $error[$key] ?? null;
                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }
            }
        }

        $errors = $decoded['errors'] ?? null;
        if (is_array($errors)) {
            $messages = [];
            foreach ($errors as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $messages[] = trim($item);
                    continue;
                }
                if (! is_array($item)) {
                    continue;
                }

                $parts = [];
                foreach (['title', 'detail', 'message', 'code'] as $key) {
                    $value = $item[$key] ?? null;
                    if (is_string($value) && trim($value) !== '') {
                        $parts[] = trim($value);
                    }
                }
                if ($parts !== []) {
                    $messages[] = implode(' - ', array_unique($parts));
                }
            }

            if ($messages !== []) {
                return implode('; ', array_unique($messages));
            }
        }

        return '';
    }

    private function storeRemovedBackgroundImage(string $pngBytes, string $sourceName): array
    {
        $userId = (int) session()->get('userId');
        $uploadPath = FCPATH . 'uploads/media/' . $userId;

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $baseName = pathinfo($sourceName, PATHINFO_FILENAME) ?: 'image';
        $safeBase = preg_replace('/[^a-z0-9_-]+/i', '-', $baseName) ?: 'image';
        $fileName = 'remove-bg-' . substr($safeBase, 0, 40) . '-' . bin2hex(random_bytes(6)) . '.png';
        $absolutePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

        if (file_put_contents($absolutePath, $pngBytes) === false) {
            throw new \RuntimeException('Failed to save remove bg result.');
        }

        $relativePath = 'uploads/media/' . $userId . '/' . $fileName;
        $url = base_url($relativePath);

        (new MediaModel())->insert([
            'user_id' => $userId,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'file_url' => $url,
            'file_type' => 'image/png',
            'file_size' => filesize($absolutePath) ?: strlen($pngBytes),
        ]);

        return [
            'src' => $url,
            'name' => $fileName,
            'type' => 'image',
        ];
    }

    private function convertToWebpIfPossible(string $absolutePath, string $mimeType): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $source = match ($mimeType) {
            'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($absolutePath) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($absolutePath) : false,
            default => false,
        };

        if (! $source) {
            return null;
        }

        if ($mimeType === 'image/png') {
            imagepalettetotruecolor($source);
            imagealphablending($source, true);
            imagesavealpha($source, true);
        }

        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $absolutePath);
        $converted = $webpPath ? imagewebp($source, $webpPath, 82) : false;
        imagedestroy($source);

        return $converted && $webpPath ? $webpPath : null;
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

    public function magicLayer(): ResponseInterface
    {
        if (! $this->magicLayerEnabled()) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Magic Layer sedang nonaktif. Remove BG tetap bisa digunakan dengan Poof.',
                ]);
        }

        if (! $this->canUseRemoveBackground()) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Magic Layer hanya tersedia untuk akun membership aktif atau admin.',
                ]);
        }

        if (! Database::connect()->tableExists('media_library')) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Tabel media_library belum tersedia.',
                ]);
        }

        $imageUrl = trim((string) $this->request->getPost('image_url'));
        $sourcePath = $this->localImagePathFromUrl($imageUrl, true);
        if ($sourcePath === null || ! is_file($sourcePath)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Gambar harus berasal dari media/upload lokal AdaAcara.',
                ]);
        }

        $maxBytes = $this->magicLayerMaxBytes();
        $sourceSize = filesize($sourcePath) ?: 0;
        if ($sourceSize <= 0 || $sourceSize > $maxBytes) {
            return $this->response
                ->setStatusCode(413)
                ->setJSON([
                    'success' => false,
                    'message' => 'Ukuran gambar terlalu besar untuk Magic Layer. Maksimal 2MB.',
                ]);
        }

        $imageInfo = @getimagesize($sourcePath);
        $mimeType = strtolower((string) ($imageInfo['mime'] ?? ''));
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return $this->response
                ->setStatusCode(415)
                ->setJSON([
                    'success' => false,
                    'message' => 'Magic Layer hanya mendukung JPG, PNG, atau WEBP.',
                ]);
        }

        $provider = $this->magicLayerProvider();
        $providers = $this->configuredRemoveBgProviders(
            $this->removeBgProviderChain($provider, $this->magicLayerFallbackProvider()),
            'Magic Layer'
        );
        if ($providers === []) {
            $configError = $this->removeBgProviderConfigError($provider, 'Magic Layer');
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => $configError ?? 'Provider Magic Layer belum dikonfigurasi.',
                ]);
        }

        try {
            $result = $this->callRemoveBackgroundProviderWithFallback($providers, $sourcePath, $mimeType, 'Magic Layer');
            $provider = $result['provider'];
            $subjectBytes = $result['bytes'];
            $subjectAsset = $this->storeMagicLayerImage($subjectBytes, basename($sourcePath), 'subject', 'png');
            $includeBackground = (string) $this->request->getPost('include_background') !== '0';
            if ($includeBackground) {
                $background = $this->storeMagicLayerOriginalBackground($sourcePath, basename($sourcePath), $mimeType);
                $backgroundAsset = $background['asset'];
                $backgroundMode = $background['mode'];
            } else {
                $backgroundAsset = null;
                $backgroundMode = 'none';
            }

        } catch (\Throwable $error) {
            log_message('error', 'Magic Layer failed: {message}', ['message' => $error->getMessage()]);

            return $this->response
                ->setStatusCode(502)
                ->setJSON([
                    'success' => false,
                    'message' => 'Magic Layer gagal diproses: ' . $this->removeBgErrorMessage($provider, $error),
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'subject' => $subjectAsset,
            'background' => $backgroundAsset,
            'background_mode' => $backgroundMode,
            'provider' => $provider,
            'fallback_used' => ($providers[0] ?? $provider) !== $provider,
        ]);
    }

    private function storeMagicLayerImage(string $bytes, string $sourceName, string $suffix, string $ext): array
    {
        $userId = (int) session()->get('userId');
        $uploadPath = FCPATH . 'uploads/media/' . $userId;

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $baseName = pathinfo($sourceName, PATHINFO_FILENAME) ?: 'image';
        $safeBase = preg_replace('/[^a-z0-9_-]+/i', '-', $baseName) ?: 'image';
        $fileName = 'magic-' . $suffix . '-' . substr((string) $safeBase, 0, 40) . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        $absolutePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

        if (file_put_contents($absolutePath, $bytes) === false) {
            throw new \RuntimeException('Failed to save magic layer asset.');
        }

        @chmod($absolutePath, 0644);
        clearstatcache(true, $absolutePath);

        if (! is_file($absolutePath) || ! is_readable($absolutePath) || (filesize($absolutePath) ?: 0) <= 0) {
            throw new \RuntimeException('Magic layer asset was saved but is not readable.');
        }

        $relativePath = 'uploads/media/' . $userId . '/' . $fileName;
        $url = base_url($relativePath);
        $publicUrl = $url . '?v=' . rawurlencode((string) (filemtime($absolutePath) ?: time()));

        $fileTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];

        (new MediaModel())->insert([
            'user_id' => $userId,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'file_url' => $url,
            'file_type' => $fileTypes[strtolower($ext)] ?? 'application/octet-stream',
            'file_size' => filesize($absolutePath) ?: strlen($bytes),
        ]);

        return [
            'src' => $publicUrl,
            'name' => $fileName,
            'type' => 'image',
        ];
    }

    private function magicLayerMaxBytes(): int
    {
        $value = env('MAGIC_LAYER_MAX_BYTES', 2 * 1024 * 1024);

        return max(1024, min($this->removeBgMaxBytes(), (int) $value));
    }

    private function storeMagicLayerOriginalBackground(string $sourcePath, string $sourceName, string $mimeType): array
    {
        $blurred = $this->createMagicLayerBlurredBackground($sourcePath, $mimeType);
        if ($blurred !== null) {
            return [
                'asset' => $this->storeMagicLayerImage($blurred['bytes'], $sourceName, 'background-blur', $blurred['ext']),
                'mode' => 'blurred_original',
            ];
        }

        $bytes = file_get_contents($sourcePath);
        if ($bytes === false || $bytes === '') {
            throw new \RuntimeException('Gagal membaca gambar original untuk background Magic Layer.');
        }

        $ext = match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        return [
            'asset' => $this->storeMagicLayerImage($bytes, $sourceName, 'background', $ext),
            'mode' => 'original',
        ];
    }

    private function createMagicLayerBlurredBackground(string $sourcePath, string $mimeType): ?array
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $image = $this->createGdImageFromPath($sourcePath, $mimeType);
        if (! $image) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);
            return null;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);

        for ($index = 0; $index < 8; $index++) {
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $wash = imagecolorallocatealpha($image, 255, 255, 255, 48);
        if ($wash !== false) {
            imagefilledrectangle($image, 0, 0, $width, $height, $wash);
        }

        ob_start();
        imagejpeg($image, null, 84);
        $bytes = (string) ob_get_clean();
        imagedestroy($image);

        if ($bytes === '') {
            return null;
        }

        return [
            'bytes' => $bytes,
            'ext' => 'jpg',
        ];
    }

    private function createGdImageFromPath(string $sourcePath, string $mimeType)
    {
        if ($mimeType === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            return @imagecreatefromjpeg($sourcePath);
        }

        if ($mimeType === 'image/png' && function_exists('imagecreatefrompng')) {
            $image = @imagecreatefrompng($sourcePath);
            if ($image) {
                $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
                if ($canvas) {
                    $white = imagecolorallocate($canvas, 255, 255, 255);
                    imagefilledrectangle($canvas, 0, 0, imagesx($image), imagesy($image), $white);
                    imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                    imagedestroy($image);
                    return $canvas;
                }
            }

            return $image;
        }

        if ($mimeType === 'image/webp' && function_exists('imagecreatefromwebp')) {
            return @imagecreatefromwebp($sourcePath);
        }

        return null;
    }
}
