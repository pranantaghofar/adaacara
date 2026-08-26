<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class EditorAudioController extends BaseController
{
    private const MAX_UPLOAD_SIZE = 4194304;
    private const MAX_CHUNK_SIZE = 1572864;
    private const ALLOWED_EXTENSIONS = ['mp3', 'm4a', 'wav', 'ogg'];
    private const ALLOWED_MIME_PREFIXES = ['audio/'];
    private const ALLOWED_MIME_TYPES = [
        'application/octet-stream',
        'application/mp4',
        'application/x-mpegURL',
        'audio/aac',
        'audio/mp4',
        'audio/x-m4a',
        'application/ogg',
        'video/ogg',
        'video/mp4',
    ];

    public function index(): ResponseInterface
    {
        $userId = (int) session()->get('userId');

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'builtin' => $this->scanAudioDirectory(FCPATH . 'assets/audio', 'assets/audio'),
                'uploaded' => $userId > 0
                    ? $this->scanAudioDirectory(FCPATH . 'uploads/audio/' . $userId, 'uploads/audio/' . $userId)
                    : [],
            ],
        ]);
    }

    public function upload(): ResponseInterface
    {
        $file = $this->request->getFile('audio');

        if (! $file || ! $file->isValid()) {
            $message = 'File audio tidak valid.';
            if ($file) {
                $message = match ($file->getError()) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran audio melebihi batas upload server. Maksimal 4MB.',
                    UPLOAD_ERR_PARTIAL => 'Upload audio belum selesai. Coba upload ulang.',
                    UPLOAD_ERR_NO_FILE => 'Pilih file audio terlebih dahulu.',
                    default => $message,
                };
            }

            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => $message,
                ]);
        }

        if ($file->getSize() > self::MAX_UPLOAD_SIZE) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Ukuran audio maksimal 4MB.',
                ]);
        }

        $extension = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
        $mimeType = strtolower($file->getMimeType() ?: '');

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true) || ! $this->isAllowedAudioMime($mimeType)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Format audio tidak sesuai. Gunakan MP3, M4A, WAV, atau OGG maksimal 4MB.',
                ]);
        }

        $userId = (int) session()->get('userId');
        $uploadPath = FCPATH . 'uploads/audio/' . $userId;

        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Folder upload audio tidak bisa dibuat.',
                ]);
        }

        $fileName = $file->getRandomName();
        if (! str_ends_with(strtolower($fileName), '.' . $extension)) {
            $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.' . $extension;
        }

        $file->move($uploadPath, $fileName);

        $relativePath = 'uploads/audio/' . $userId . '/' . $fileName;
        $item = [
            'src' => base_url($relativePath),
            'name' => $file->getClientName() ?: $fileName,
            'type' => 'audio',
            'size' => is_file($uploadPath . DIRECTORY_SEPARATOR . $fileName) ? filesize($uploadPath . DIRECTORY_SEPARATOR . $fileName) : $file->getSize(),
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => [$item],
            'message' => 'Audio berhasil diupload.',
        ]);
    }

    public function uploadChunk(): ResponseInterface
    {
        $userId = (int) session()->get('userId');
        $uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $this->request->getPost('uploadId'));
        $clientName = trim((string) $this->request->getPost('fileName'));
        $index = (int) $this->request->getPost('index');
        $total = (int) $this->request->getPost('total');
        $fileSize = (int) $this->request->getPost('fileSize');
        $extension = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
        $chunk = $this->request->getFile('chunk');

        if ($userId <= 0 || $uploadId === '' || strlen($uploadId) > 80 || $total < 1 || $total > 12 || $index < 0 || $index >= $total) {
            return $this->audioError('Data upload audio tidak valid.');
        }

        if ($fileSize < 1 || $fileSize > self::MAX_UPLOAD_SIZE) {
            return $this->audioError('Ukuran audio maksimal 4MB.');
        }

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return $this->audioError('Format audio tidak sesuai. Gunakan MP3, M4A, WAV, atau OGG maksimal 4MB.');
        }

        if (! $chunk || ! $chunk->isValid()) {
            return $this->audioError('Potongan audio gagal diupload. Coba ulangi upload.');
        }

        if ($chunk->getSize() > self::MAX_CHUNK_SIZE) {
            return $this->audioError('Potongan audio terlalu besar. Coba upload ulang.');
        }

        $chunkDir = WRITEPATH . 'uploads/audio-chunks/' . $userId . '/' . $uploadId;
        if (! is_dir($chunkDir) && ! mkdir($chunkDir, 0755, true) && ! is_dir($chunkDir)) {
            return $this->audioError('Folder upload audio sementara tidak bisa dibuat.', 500);
        }

        $chunkName = str_pad((string) $index, 4, '0', STR_PAD_LEFT) . '.part';
        if (is_file($chunkDir . DIRECTORY_SEPARATOR . $chunkName)) {
            unlink($chunkDir . DIRECTORY_SEPARATOR . $chunkName);
        }

        $chunk->move($chunkDir, $chunkName);

        for ($part = 0; $part < $total; $part++) {
            $partName = str_pad((string) $part, 4, '0', STR_PAD_LEFT) . '.part';
            if (! is_file($chunkDir . DIRECTORY_SEPARATOR . $partName)) {
                return $this->response->setJSON([
                    'success' => true,
                    'complete' => false,
                    'message' => 'Mengupload audio...',
                ]);
            }
        }

        $uploadPath = FCPATH . 'uploads/audio/' . $userId;
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            $this->deleteDirectory($chunkDir);

            return $this->audioError('Folder upload audio tidak bisa dibuat.', 500);
        }

        $fileName = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;
        $output = fopen($targetPath, 'wb');
        if (! is_resource($output)) {
            $this->deleteDirectory($chunkDir);

            return $this->audioError('File audio tidak bisa dibuat.', 500);
        }

        for ($part = 0; $part < $total; $part++) {
            $partName = $chunkDir . DIRECTORY_SEPARATOR . str_pad((string) $part, 4, '0', STR_PAD_LEFT) . '.part';
            $input = fopen($partName, 'rb');
            if (! is_resource($input)) {
                fclose($output);
                @unlink($targetPath);
                $this->deleteDirectory($chunkDir);

                return $this->audioError('Potongan audio tidak lengkap. Coba upload ulang.');
            }

            stream_copy_to_stream($input, $output);
            fclose($input);
        }

        fclose($output);
        $this->deleteDirectory($chunkDir);

        $actualSize = is_file($targetPath) ? filesize($targetPath) : 0;
        if ($actualSize < 1 || $actualSize > self::MAX_UPLOAD_SIZE) {
            @unlink($targetPath);

            return $this->audioError('Ukuran audio maksimal 4MB.');
        }

        $relativePath = 'uploads/audio/' . $userId . '/' . $fileName;
        $item = [
            'src' => base_url($relativePath),
            'name' => $clientName !== '' ? $clientName : $fileName,
            'type' => 'audio',
            'size' => $actualSize,
        ];

        return $this->response->setJSON([
            'success' => true,
            'complete' => true,
            'data' => [$item],
            'message' => 'Audio berhasil diupload.',
        ]);
    }

    private function scanAudioDirectory(string $absolutePath, string $relativeBase): array
    {
        if (! is_dir($absolutePath)) {
            return [];
        }

        $items = [];
        $files = scandir($absolutePath) ?: [];

        foreach ($files as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $path = $absolutePath . DIRECTORY_SEPARATOR . $fileName;
            if (! is_file($path)) {
                continue;
            }

            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $items[] = [
                'src' => base_url(trim($relativeBase, '/') . '/' . rawurlencode($fileName)),
                'name' => pathinfo($fileName, PATHINFO_FILENAME),
                'type' => 'audio',
                'size' => filesize($path) ?: 0,
            ];
        }

        usort($items, static fn (array $a, array $b): int => strcasecmp((string) $a['name'], (string) $b['name']));

        return $items;
    }

    private function isAllowedAudioMime(string $mimeType): bool
    {
        if ($mimeType === '') {
            return false;
        }

        foreach (self::ALLOWED_MIME_PREFIXES as $prefix) {
            if (str_starts_with($mimeType, $prefix)) {
                return true;
            }
        }

        return in_array($mimeType, self::ALLOWED_MIME_TYPES, true);
    }

    private function audioError(string $message, int $statusCode = 422): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'success' => false,
                'message' => $message,
            ]);
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $fileName;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }
}
