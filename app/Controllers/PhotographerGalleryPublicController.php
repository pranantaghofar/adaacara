<?php

namespace App\Controllers;

use App\Models\PhotographerGalleryAlbumModel;
use App\Models\PhotographerGalleryFamilyShareModel;
use App\Models\PhotographerGalleryModel;
use App\Models\PhotographerGalleryPhotoModel;
use App\Models\PhotographerGallerySelectionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class PhotographerGalleryPublicController extends BaseController
{
    private const FAMILY_GALLERY_CLIENT_TOKEN = '__gallery_family__';

    private function tablesReady(bool $needSelections = false): bool
    {
        $db = Database::connect();
        $ready = $db->tableExists('photographer_galleries')
            && $db->tableExists('photographer_gallery_photos');

        if ($needSelections) {
            $ready = $ready && $db->tableExists('photographer_gallery_selections');
        }

        return $ready;
    }

    private function familySharesReady(): bool
    {
        return Database::connect()->tableExists('photographer_gallery_family_shares');
    }

    public function show(string $slug): string|RedirectResponse
    {
        $gallery = $this->galleryBySlug($slug);
        $accessError = '';

        if (strtolower($this->request->getMethod()) === 'post') {
            $pin = trim((string) $this->request->getPost('pin'));
            if ($this->verifyPin($gallery, $pin)) {
                session()->set($this->accessSessionKey((string) $gallery['slug']), true);

                return redirect()->to(site_url('gallery/' . $gallery['slug']))->with('success', 'Akses gallery dibuka.');
            }

            $accessError = 'PIN belum sesuai. Cek kembali PIN dari fotografer.';
        }

        $hasAccess = $this->hasAccess($gallery);
        $photos = [];
        $albums = [];
        $selectedPhotoIds = [];
        $sharePhotoIds = [];
        $submittedPrintPhotoIds = [];
        $submittedSharePhotoIds = [];
        $familyShareUrl = '';
        $clientToken = '';
        $selectionReady = $this->tablesReady(true);
        $shareReady = false;

        if ($hasAccess) {
            $photos = (new PhotographerGalleryPhotoModel())->visibleForGallery((int) $gallery['id'], 500);
            $albumModel = new PhotographerGalleryAlbumModel();
            $usedAlbumIds = array_fill_keys(array_values(array_unique(array_filter(array_map(
                static fn (array $photo): int => (int) ($photo['album_id'] ?? 0),
                $photos
            )))), true);
            $albums = array_values(array_filter(
                $albumModel->forGallery((int) $gallery['id']),
                static fn (array $album): bool => isset($usedAlbumIds[(int) ($album['id'] ?? 0)])
            ));
            $clientToken = $this->clientToken((int) $gallery['id']);
            if ($selectionReady) {
                $selectionModel = new PhotographerGallerySelectionModel();
                $shareReady = $selectionModel->supportsSelectionType('share');
                $selectedPhotoIds = $selectionModel->selectedPhotoIds((int) $gallery['id'], $clientToken, 'print');
                $submittedPrintPhotoIds = $selectionModel->submittedPhotoIds((int) $gallery['id'], $clientToken, 'print');
                $sharePhotoIds = $shareReady
                    ? $selectionModel->selectedPhotoIds((int) $gallery['id'], $clientToken, 'share')
                    : [];
                $submittedSharePhotoIds = $shareReady
                    ? $selectionModel->submittedPhotoIds((int) $gallery['id'], $clientToken, 'share')
                    : [];
                if ($shareReady && $this->familySharesReady()) {
                    $familyShareModel = new PhotographerGalleryFamilyShareModel();
                    $familyShare = $familyShareModel->findActiveForClient((int) $gallery['id'], self::FAMILY_GALLERY_CLIENT_TOKEN)
                        ?? $familyShareModel->findActiveForClient((int) $gallery['id'], $clientToken);
                    if ($familyShare !== null && ! empty($familyShare['share_token'])) {
                        $familyShareUrl = site_url('gallery/' . $gallery['slug'] . '/family/' . $familyShare['share_token']);
                    }
                }
            }
        }

        return view('photographer_galleries/public', [
            'gallery' => $gallery,
            'photos' => $photos,
            'albums' => $albums,
            'hasAccess' => $hasAccess,
            'accessError' => $accessError,
            'selectedPhotoIds' => $selectedPhotoIds,
            'sharePhotoIds' => $sharePhotoIds,
            'submittedPrintPhotoIds' => $submittedPrintPhotoIds,
            'submittedSharePhotoIds' => $submittedSharePhotoIds,
            'familyShareUrl' => $familyShareUrl,
            'selectionReady' => $selectionReady,
            'shareReady' => $shareReady,
        ]);
    }

    public function toggleSelection(string $slug): ResponseInterface
    {
        return $this->toggleTypedSelection($slug, 'print');
    }

    public function toggleShareSelection(string $slug): ResponseInterface
    {
        return $this->toggleTypedSelection($slug, 'share');
    }

    private function toggleTypedSelection(string $slug, string $type): ResponseInterface
    {
        $gallery = $this->galleryBySlug($slug, true);
        if (! $this->hasAccess($gallery)) {
            return $this->jsonError('Masukkan PIN gallery terlebih dahulu.', 403);
        }
        if (empty($gallery['selection_enabled'])) {
            return $this->jsonError('Client selection belum diaktifkan untuk gallery ini.', 403);
        }
        if (! $this->tablesReady(true)) {
            return $this->jsonError('Tabel pilihan foto belum siap.', 503);
        }

        $photoId = (int) $this->request->getPost('photo_id');
        $photo = (new PhotographerGalleryPhotoModel())->visiblePhotoForGallery($photoId, (int) $gallery['id']);
        if ($photo === null) {
            return $this->jsonError('Foto tidak ditemukan.', 404);
        }

        $selectionModel = new PhotographerGallerySelectionModel();
        if (! $selectionModel->supportsSelectionType($type)) {
            return $this->jsonError('Pilihan untuk disebar belum siap. Jalankan update SQL Photographer Gallery dahulu.', 503);
        }

        $clientToken = $this->clientToken((int) $gallery['id']);
        $existing = $selectionModel->findSelection((int) $gallery['id'], $photoId, $clientToken, $type);
        $selected = false;

        if ($existing !== null) {
            $selectionModel->delete((int) $existing['id']);
        } else {
            $limit = max(1, (int) ($gallery['selection_limit'] ?? 30));
            $selectedCount = $selectionModel->countSelected((int) $gallery['id'], $clientToken, $type);
            if ($selectedCount >= $limit) {
                return $this->jsonError('Batas pilihan foto sudah tercapai.', 422, [
                    'selected_count' => $selectedCount,
                    'selection_limit' => $limit,
                ]);
            }

            $insertData = [
                'gallery_id' => (int) $gallery['id'],
                'photo_id' => $photoId,
                'client_name' => null,
                'client_token' => $clientToken,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if ($selectionModel->hasColumn('selection_type')) {
                $insertData['selection_type'] = $type;
            }
            try {
                if (! $selectionModel->insert($insertData)) {
                    return $this->jsonError('Pilihan foto belum bisa disimpan. Pastikan update SQL Photographer Gallery sudah dijalankan.', 500);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Photographer Gallery selection insert gagal: {message}', [
                    'message' => $e->getMessage(),
                ]);

                return $this->jsonError('Pilihan foto belum bisa disimpan. Pastikan update SQL Photographer Gallery sudah dijalankan.', 500);
            }
            $selected = true;
        }

        $selectedCount = $selectionModel->countSelected((int) $gallery['id'], $clientToken, $type);

        return $this->response->setJSON([
            'ok' => true,
            'type' => $type,
            'selected' => $selected,
            'selected_count' => $selectedCount,
            'selection_limit' => max(1, (int) ($gallery['selection_limit'] ?? 30)),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function submitSelection(string $slug): ResponseInterface
    {
        $gallery = $this->galleryBySlug($slug, true);
        if (! $this->hasAccess($gallery)) {
            return $this->jsonError('Masukkan PIN gallery terlebih dahulu.', 403);
        }
        if (empty($gallery['selection_enabled'])) {
            return $this->jsonError('Client selection belum diaktifkan untuk gallery ini.', 403);
        }
        if (! $this->tablesReady(true)) {
            return $this->jsonError('Tabel pilihan foto belum siap.', 503);
        }

        $selectionModel = new PhotographerGallerySelectionModel();
        $clientToken = $this->clientToken((int) $gallery['id']);
        $selectedCount = $selectionModel->countSelected((int) $gallery['id'], $clientToken, 'print');
        if ($selectedCount <= 0) {
            return $this->jsonError('Pilih minimal satu foto untuk dicetak dahulu.', 422, [
                'selected_count' => 0,
                'selection_limit' => max(1, (int) ($gallery['selection_limit'] ?? 30)),
            ]);
        }

        $selectionModel->submitForClient((int) $gallery['id'], $clientToken, 'print');

        return $this->response->setJSON([
            'ok' => true,
            'message' => $selectedCount . ' foto untuk dicetak sudah dikirim ke fotografer.',
            'selected_count' => $selectedCount,
            'selection_limit' => max(1, (int) ($gallery['selection_limit'] ?? 30)),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function submitShareSelection(string $slug): ResponseInterface
    {
        $gallery = $this->galleryBySlug($slug, true);
        if (! $this->hasAccess($gallery)) {
            return $this->jsonError('Masukkan PIN gallery terlebih dahulu.', 403);
        }
        if (! $this->tablesReady(true)) {
            return $this->jsonError('Tabel pilihan foto belum siap.', 503);
        }

        $selectionModel = new PhotographerGallerySelectionModel();
        if (! $selectionModel->supportsSelectionType('share')) {
            return $this->jsonError('Pilihan untuk disebar belum siap. Jalankan update SQL Photographer Gallery dahulu.', 503);
        }

        $clientToken = $this->clientToken((int) $gallery['id']);
        $selectedCount = $selectionModel->countSelected((int) $gallery['id'], $clientToken, 'share');
        if ($selectedCount <= 0) {
            return $this->jsonError('Pilih minimal satu foto untuk disebar dahulu.', 422, [
                'selected_count' => 0,
                'selection_limit' => max(1, (int) ($gallery['selection_limit'] ?? 30)),
            ]);
        }

        $mode = (string) $this->request->getPost('share_mode');
        $pin = preg_replace('/\D+/', '', (string) $this->request->getPost('share_pin'));
        if (! in_array($mode, ['public', 'pin'], true)) {
            return $this->jsonError('Pilih tipe sebar dahulu.', 422);
        }
        if ($mode === 'pin' && strlen($pin) !== 4) {
            return $this->jsonError('PIN sebar harus tepat 4 digit.', 422);
        }
        if (! $this->familySharesReady()) {
            return $this->jsonError('Tabel halaman keluarga belum siap. Jalankan update SQL Photographer Gallery dahulu.', 503);
        }

        $galleryId = (int) $gallery['id'];
        $selectionModel->submitForClient($galleryId, $clientToken, 'share');
        $syncedCount = $selectionModel->syncShareSelectionsToClient($galleryId, $clientToken, self::FAMILY_GALLERY_CLIENT_TOKEN);
        if ($syncedCount <= 0) {
            return $this->jsonError('Foto untuk halaman keluarga belum bisa disinkronkan.', 500);
        }
        $share = (new PhotographerGalleryFamilyShareModel())->upsertForClient(
            $galleryId,
            self::FAMILY_GALLERY_CLIENT_TOKEN,
            $mode,
            $mode === 'pin' ? $pin : null
        );
        if ($share === null) {
            return $this->jsonError('Link halaman keluarga belum bisa dibuat.', 500);
        }
        $familyUrl = site_url('gallery/' . $gallery['slug'] . '/family/' . $share['share_token']);

        return $this->response->setJSON([
            'ok' => true,
            'message' => $selectedCount . ' foto untuk halaman keluarga sudah disiapkan.',
            'selected_count' => $selectedCount,
            'share_mode' => $mode,
            'family_url' => $familyUrl,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function family(string $slug, string $shareToken): string|RedirectResponse
    {
        $gallery = $this->galleryBySlug($slug);
        if (! $this->familySharesReady()) {
            throw PageNotFoundException::forPageNotFound('Halaman keluarga belum siap.');
        }

        $shareModel = new PhotographerGalleryFamilyShareModel();
        $share = $shareModel->findByShareToken((int) $gallery['id'], $shareToken);
        if ($share === null) {
            throw PageNotFoundException::forPageNotFound('Halaman keluarga tidak ditemukan.');
        }

        $accessError = '';
        if ((string) ($share['privacy_mode'] ?? 'public') === 'pin' && strtolower($this->request->getMethod()) === 'post') {
            $pin = trim((string) $this->request->getPost('pin'));
            if ($this->verifyFamilyPin($share, $pin)) {
                session()->set($this->familyAccessSessionKey((string) $share['share_token']), true);

                return redirect()->to(site_url('gallery/' . $gallery['slug'] . '/family/' . $share['share_token']))->with('success', 'Akses halaman keluarga dibuka.');
            }

            $accessError = 'PIN belum sesuai. Cek kembali PIN dari pengirim.';
        }

        $hasAccess = (string) ($share['privacy_mode'] ?? 'public') === 'public'
            || (bool) session()->get($this->familyAccessSessionKey((string) $share['share_token']));
        $photos = [];
        $albums = [];
        if ($hasAccess) {
            $photos = $this->sharePhotos((int) $gallery['id'], (string) ($share['client_token'] ?? ''));
            $usedAlbumIds = array_fill_keys(array_values(array_unique(array_filter(array_map(
                static fn (array $photo): int => (int) ($photo['album_id'] ?? 0),
                $photos
            )))), true);
            $albums = array_values(array_filter(
                (new PhotographerGalleryAlbumModel())->forGallery((int) $gallery['id']),
                static fn (array $album): bool => isset($usedAlbumIds[(int) ($album['id'] ?? 0)])
            ));
        }

        return view('photographer_galleries/family', [
            'gallery' => $gallery,
            'share' => $share,
            'photos' => $photos,
            'albums' => $albums,
            'hasAccess' => $hasAccess,
            'accessError' => $accessError,
        ]);
    }

    public function submitComment(string $slug): ResponseInterface
    {
        $gallery = $this->galleryBySlug($slug, true);
        if (! $this->hasAccess($gallery)) {
            return $this->jsonError('Masukkan PIN gallery terlebih dahulu.', 403);
        }
        $db = Database::connect();
        if (! $db->tableExists('photographer_gallery_comments')) {
            return $this->jsonError('Tabel komentar foto belum siap.', 503);
        }

        $photoId = (int) $this->request->getPost('photo_id');
        $comment = trim((string) $this->request->getPost('comment'));
        if ($comment === '' || strlen($comment) > 1000) {
            return $this->jsonError('Komentar/revisi wajib diisi maksimal 1000 karakter.', 422);
        }
        $photo = (new PhotographerGalleryPhotoModel())->visiblePhotoForGallery($photoId, (int) $gallery['id']);
        if ($photo === null) {
            return $this->jsonError('Foto tidak ditemukan.', 404);
        }

        $db->table('photographer_gallery_comments')->insert([
            'gallery_id' => (int) $gallery['id'],
            'photo_id' => $photoId,
            'client_name' => null,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Komentar/revisi foto sudah dikirim.',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function downloadPhoto(string $slug, int $photoId): ResponseInterface
    {
        $gallery = $this->galleryBySlug($slug, true);
        if (! $this->hasAccess($gallery) || empty($gallery['download_enabled'])) {
            throw PageNotFoundException::forPageNotFound('Download tidak tersedia.');
        }

        $photo = (new PhotographerGalleryPhotoModel())->visiblePhotoForGallery($photoId, (int) $gallery['id']);
        if ($photo === null) {
            throw PageNotFoundException::forPageNotFound('Foto tidak ditemukan.');
        }

        $relativePath = ltrim((string) ($photo['file_path'] ?? ''), '/');
        $realPath = realpath(FCPATH . $relativePath);
        $realBase = realpath(FCPATH . 'uploads/photographer-galleries');
        if ($realPath === false || $realBase === false || ! str_starts_with($realPath, $realBase) || ! is_file($realPath)) {
            throw PageNotFoundException::forPageNotFound('File foto tidak ditemukan.');
        }

        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) ($photo['original_name'] ?? basename($realPath))) ?: basename($realPath);

        return $this->response->download($realPath, null)->setFileName($fileName);
    }

    public function downloadFamilyPhoto(string $slug, string $shareToken, int $photoId): ResponseInterface
    {
        $gallery = $this->galleryBySlug($slug, true);
        if (! $this->familySharesReady()) {
            throw PageNotFoundException::forPageNotFound('Halaman keluarga belum siap.');
        }

        $share = (new PhotographerGalleryFamilyShareModel())->findByShareToken((int) $gallery['id'], $shareToken);
        if ($share === null) {
            throw PageNotFoundException::forPageNotFound('Halaman keluarga tidak ditemukan.');
        }

        $pin = trim((string) $this->request->getPost('download_pin'));
        if (! $this->verifyFamilyDownloadPin($share, $gallery, $pin)) {
            throw PageNotFoundException::forPageNotFound('PIN download tidak sesuai.');
        }

        $photo = $this->sharePhoto((int) $gallery['id'], (string) ($share['client_token'] ?? ''), $photoId);
        if ($photo === null) {
            throw PageNotFoundException::forPageNotFound('Foto tidak tersedia untuk halaman keluarga.');
        }

        $relativePath = ltrim((string) ($photo['file_path'] ?? ''), '/');
        $realPath = realpath(FCPATH . $relativePath);
        $realBase = realpath(FCPATH . 'uploads/photographer-galleries');
        if ($realPath === false || $realBase === false || ! str_starts_with($realPath, $realBase) || ! is_file($realPath)) {
            throw PageNotFoundException::forPageNotFound('File foto tidak ditemukan.');
        }

        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) ($photo['original_name'] ?? basename($realPath))) ?: basename($realPath);

        return $this->response->download($realPath, null)->setFileName($fileName);
    }

    private function galleryBySlug(string $slug, bool $json = false): array
    {
        if (! $this->tablesReady()) {
            if ($json) {
                throw PageNotFoundException::forPageNotFound('Gallery belum siap.');
            }
            throw PageNotFoundException::forPageNotFound('Gallery belum siap.');
        }

        $gallery = (new PhotographerGalleryModel())->findPublicBySlug($slug);
        if ($gallery === null) {
            throw PageNotFoundException::forPageNotFound('Gallery tidak ditemukan.');
        }

        return $gallery;
    }

    private function hasAccess(array $gallery): bool
    {
        if ((string) ($gallery['privacy_mode'] ?? 'pin') === 'public') {
            return true;
        }

        return (bool) session()->get($this->accessSessionKey((string) ($gallery['slug'] ?? '')));
    }

    private function verifyPin(array $gallery, string $pin): bool
    {
        $hash = (string) ($gallery['pin_hash'] ?? '');
        if ($hash === '' || $pin === '') {
            return false;
        }

        return password_verify($pin, $hash);
    }

    private function verifyFamilyPin(array $share, string $pin): bool
    {
        $hash = (string) ($share['pin_hash'] ?? '');
        if ($hash === '' || $pin === '') {
            return false;
        }

        return password_verify($pin, $hash);
    }

    private function verifyFamilyDownloadPin(array $share, array $gallery, string $pin): bool
    {
        if ($this->verifyFamilyPin($share, $pin)) {
            return true;
        }

        return $this->verifyPin($gallery, $pin);
    }

    private function accessSessionKey(string $slug): string
    {
        return 'photographer_gallery_access_' . hash('sha256', strtolower(trim($slug)));
    }

    private function familyAccessSessionKey(string $shareToken): string
    {
        return 'photographer_gallery_family_access_' . hash('sha256', strtolower(trim($shareToken)));
    }

    private function sharePhotos(int $galleryId, string $clientToken): array
    {
        if ($galleryId <= 0 || $clientToken === '' || ! $this->tablesReady(true)) {
            return [];
        }

        try {
            return Database::connect()->table('photographer_gallery_selections')
                ->select('photographer_gallery_photos.*')
                ->join('photographer_gallery_photos', 'photographer_gallery_photos.id = photographer_gallery_selections.photo_id', 'inner')
                ->where('photographer_gallery_selections.gallery_id', $galleryId)
                ->where('photographer_gallery_selections.client_token', $clientToken)
                ->where('photographer_gallery_selections.selection_type', 'share')
                ->whereIn('photographer_gallery_photos.status', PhotographerGalleryPhotoModel::PUBLIC_STATUSES)
                ->orderBy('photographer_gallery_photos.sort_order', 'ASC')
                ->orderBy('photographer_gallery_photos.id', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Photographer Gallery family photos load gagal: {message}', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function sharePhoto(int $galleryId, string $clientToken, int $photoId): ?array
    {
        if ($galleryId <= 0 || $clientToken === '' || $photoId <= 0 || ! $this->tablesReady(true)) {
            return null;
        }

        try {
            $row = Database::connect()->table('photographer_gallery_selections')
                ->select('photographer_gallery_photos.*')
                ->join('photographer_gallery_photos', 'photographer_gallery_photos.id = photographer_gallery_selections.photo_id', 'inner')
                ->where('photographer_gallery_selections.gallery_id', $galleryId)
                ->where('photographer_gallery_selections.client_token', $clientToken)
                ->where('photographer_gallery_selections.selection_type', 'share')
                ->where('photographer_gallery_photos.id', $photoId)
                ->whereIn('photographer_gallery_photos.status', PhotographerGalleryPhotoModel::PUBLIC_STATUSES)
                ->get()
                ->getRowArray();

            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            log_message('error', 'Photographer Gallery family photo load gagal: {message}', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function clientToken(int $galleryId): string
    {
        $session = session();
        $key = 'photographer_gallery_client_' . $galleryId;
        $token = (string) $session->get($key);
        if ($token === '') {
            $token = bin2hex(random_bytes(16));
            $session->set($key, $token);
        }

        return $token;
    }

    private function jsonError(string $message, int $status = 400, array $extra = []): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON(array_merge([
            'ok' => false,
            'message' => $message,
            'csrf_hash' => csrf_hash(),
        ], $extra));
    }
}
