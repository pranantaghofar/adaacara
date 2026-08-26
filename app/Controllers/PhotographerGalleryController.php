<?php

namespace App\Controllers;

use App\Libraries\ProductEntitlementService;
use App\Models\PhotographerGalleryModel;
use App\Models\PhotographerGalleryAlbumModel;
use App\Models\PhotographerGalleryCommentModel;
use App\Models\PhotographerGalleryPhotoModel;
use App\Models\PhotographerGallerySelectionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class PhotographerGalleryController extends BaseController
{
    private function userId(): int
    {
        return (int) (session()->get('userId') ?? 0);
    }

    private function tablesReady(): bool
    {
        $db = Database::connect();

        return $db->tableExists('photographer_galleries')
            && $db->tableExists('photographer_gallery_photos');
    }

    private function albumsReady(): bool
    {
        return Database::connect()->tableExists('photographer_gallery_albums');
    }

    private function requireGalleryAccess(): ?RedirectResponse
    {
        $userId = $this->userId();
        $service = new ProductEntitlementService();
        $role = strtolower((string) (session()->get('userRole') ?? session()->get('role') ?? ''));
        if (in_array($role, ['admin', 'superadmin', 'content_admin', 'finance_admin', 'support_admin'], true)) {
            return null;
        }

        if (! $service->tableReady() || $service->hasActive($userId, ProductEntitlementService::PHOTOGRAPHER_GALLERY)) {
            return null;
        }

        return redirect()->to(site_url('plans'))
            ->with('error', 'Aktifkan Galeri Klien Fotografer untuk membuka dashboard gallery.');
    }

    private function galleryAccessJson(): ?ResponseInterface
    {
        if ($this->requireGalleryAccess() === null) {
            return null;
        }

        return $this->response->setStatusCode(403)->setJSON([
            'ok' => false,
            'message' => 'Aktifkan Galeri Klien Fotografer untuk membuka fitur ini.',
            'redirect' => site_url('plans'),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function index(): string|RedirectResponse
    {
        if ($redirect = $this->requireGalleryAccess()) {
            return $redirect;
        }

        $isReady = $this->tablesReady();
        $galleries = $isReady ? (new PhotographerGalleryModel())->getByUser($this->userId()) : [];

        return view('photographer_galleries/index', [
            'title' => 'Photographer Gallery',
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
            'isReady' => $isReady,
            'galleries' => $galleries,
        ]);
    }

    public function create(): string|RedirectResponse
    {
        if ($redirect = $this->requireGalleryAccess()) {
            return $redirect;
        }

        if (! $this->tablesReady()) {
            return redirect()->to(site_url('photographer-galleries'))
                ->with('error', 'Database Photographer Gallery belum siap. Jalankan database/alter_photographer_galleries.sql dahulu.');
        }

        return view('photographer_galleries/create', [
            'title' => 'Buat Photographer Gallery',
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
        ]);
    }

    public function store(): RedirectResponse
    {
        if ($redirect = $this->requireGalleryAccess()) {
            return $redirect;
        }

        if (! $this->tablesReady()) {
            return redirect()->to(site_url('photographer-galleries'))
                ->with('error', 'Database Photographer Gallery belum siap. Jalankan database/alter_photographer_galleries.sql dahulu.');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[160]',
            'slug' => 'permit_empty|max_length[180]',
            'event_date' => 'permit_empty|valid_date[Y-m-d]',
            'studio_name' => 'permit_empty|max_length[160]',
            'privacy_mode' => 'required|in_list[public,pin]',
            'pin' => 'permit_empty|regex_match[/^[0-9]{4}$/]',
            'selection_limit' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[500]',
            'cover_photo' => 'permit_empty|max_size[cover_photo,5120]|is_image[cover_photo]|mime_in[cover_photo,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $privacyMode = (string) $this->request->getPost('privacy_mode');
        $pin = trim((string) $this->request->getPost('pin'));
        if ($privacyMode === 'pin' && $pin === '') {
            return redirect()->back()->withInput()->with('errors', ['pin' => 'PIN wajib diisi untuk gallery private.']);
        }

        $model = new PhotographerGalleryModel();
        $title = trim((string) $this->request->getPost('title'));
        $slug = $this->uniqueSlug((string) ($this->request->getPost('slug') ?: $title), $model);
        $now = date('Y-m-d H:i:s');

        $galleryId = $model->insert([
            'user_id' => $this->userId(),
            'title' => $title,
            'slug' => $slug,
            'event_date' => $this->nullableDate((string) $this->request->getPost('event_date')),
            'studio_name' => trim((string) $this->request->getPost('studio_name')),
            'cover_photo' => $this->storeCoverPhoto(),
            'privacy_mode' => $privacyMode,
            'pin_hash' => $privacyMode === 'pin' ? password_hash($pin, PASSWORD_DEFAULT) : null,
            'selection_enabled' => $this->request->getPost('selection_enabled') ? 1 : 0,
            'selection_limit' => $this->clampedInt($this->request->getPost('selection_limit'), 30, 1, 500),
            'download_enabled' => $this->request->getPost('download_enabled') ? 1 : 0,
            'status' => 'draft',
            'created_at' => $now,
            'updated_at' => $now,
        ], true);

        if (! $galleryId) {
            return redirect()->back()->withInput()->with('error', 'Gallery belum bisa dibuat. Coba lagi sebentar.');
        }

        return redirect()->to(site_url('photographer-galleries/' . $galleryId))
            ->with('success', 'Gallery dibuat. Kamu bisa mulai upload foto.');
    }

    public function show(int $id): string|RedirectResponse
    {
        if ($redirect = $this->requireGalleryAccess()) {
            return $redirect;
        }

        if (! $this->tablesReady()) {
            return view('photographer_galleries/index', [
                'title' => 'Photographer Gallery',
                'userName' => session()->get('userName'),
                'userEmail' => session()->get('userEmail'),
                'isReady' => false,
                'galleries' => [],
            ]);
        }

        $gallery = $this->ownedGallery($id);
        $photoModel = new PhotographerGalleryPhotoModel();
        $albumModel = new PhotographerGalleryAlbumModel();
        $selectionModel = new PhotographerGallerySelectionModel();
        $commentModel = new PhotographerGalleryCommentModel();
        $albums = [];
        $photos = [];
        $photoCount = 0;
        $printSelections = [];
        $comments = [];
        try {
            $photos = $photoModel->adminForGallery($id, 240);
            $photoCount = $photoModel->countForGallery($id);
        } catch (\Throwable $e) {
            log_message('error', 'Photographer Gallery photo load gagal: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
        try {
            $albums = $albumModel->forGallery((int) $gallery['id']);
        } catch (\Throwable $e) {
            log_message('error', 'Photographer Gallery album load gagal: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
        try {
            $printSelections = $selectionModel->tableReady()
                ? $selectionModel->selectedForGallery((int) $gallery['id'])
                : [];
        } catch (\Throwable $e) {
            log_message('error', 'Photographer Gallery print selection load gagal: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
        try {
            $comments = $commentModel->tableReady()
                ? $commentModel->forGallery((int) $gallery['id'], 120)
                : [];
        } catch (\Throwable $e) {
            log_message('error', 'Photographer Gallery comments load gagal: {message}', [
                'message' => $e->getMessage(),
            ]);
        }

        return view('photographer_galleries/show', [
            'title' => $gallery['title'] ?? 'Photographer Gallery',
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
            'gallery' => $gallery,
            'photos' => $photos,
            'photoCount' => $photoCount,
            'albums' => $albums,
            'albumsReady' => $this->albumsReady(),
            'photoStatuses' => PhotographerGalleryPhotoModel::ADMIN_STATUSES,
            'printSelections' => $printSelections,
            'comments' => $comments,
            'commentsReady' => $commentModel->tableReady(),
        ]);
    }

    public function updateSettings(int $id): RedirectResponse
    {
        if ($redirect = $this->requireGalleryAccess()) {
            return $redirect;
        }

        if (! $this->tablesReady()) {
            return redirect()->to(site_url('photographer-galleries'))->with('error', 'Database Photographer Gallery belum siap.');
        }

        $gallery = $this->ownedGallery($id);
        $rules = [
            'title' => 'required|min_length[3]|max_length[160]',
            'slug' => 'permit_empty|max_length[180]',
            'event_date' => 'permit_empty|valid_date[Y-m-d]',
            'studio_name' => 'permit_empty|max_length[160]',
            'privacy_mode' => 'required|in_list[public,pin]',
            'pin' => 'permit_empty|regex_match[/^[0-9]{4}$/]',
            'selection_limit' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[500]',
            'cover_photo' => 'permit_empty|max_size[cover_photo,5120]|is_image[cover_photo]|mime_in[cover_photo,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new PhotographerGalleryModel();
        $privacyMode = (string) $this->request->getPost('privacy_mode');
        $pin = trim((string) $this->request->getPost('pin'));
        $slug = $this->uniqueSlug((string) ($this->request->getPost('slug') ?: $this->request->getPost('title')), $model, $id);
        $coverPhoto = $this->storeCoverPhoto();

        $data = [
            'title' => trim((string) $this->request->getPost('title')),
            'slug' => $slug,
            'event_date' => $this->nullableDate((string) $this->request->getPost('event_date')),
            'studio_name' => trim((string) $this->request->getPost('studio_name')),
            'privacy_mode' => $privacyMode,
            'selection_enabled' => $this->request->getPost('selection_enabled') ? 1 : 0,
            'selection_limit' => $this->clampedInt($this->request->getPost('selection_limit'), 30, 1, 500),
            'download_enabled' => $this->request->getPost('download_enabled') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($coverPhoto !== null) {
            $data['cover_photo'] = $coverPhoto;
        }
        if ($privacyMode === 'pin' && $pin !== '') {
            $data['pin_hash'] = password_hash($pin, PASSWORD_DEFAULT);
        }
        if ($privacyMode === 'public') {
            $data['pin_hash'] = null;
        }

        if ($privacyMode === 'pin' && empty($gallery['pin_hash']) && $pin === '') {
            return redirect()->back()->withInput()->with('errors', ['pin' => 'PIN wajib diisi untuk gallery private.']);
        }

        $model->update($id, $data);

        return redirect()->back()->with('success', 'Pengaturan gallery diperbarui.');
    }

    public function createAlbum(int $galleryId): ResponseInterface|RedirectResponse
    {
        if ($accessDenied = $this->galleryAccessJson()) {
            return $this->request->isAJAX() ? $accessDenied : redirect()->to(site_url('plans'))->with('error', 'Aktifkan Galeri Klien Fotografer untuk membuka fitur ini.');
        }

        if (! $this->tablesReady() || ! $this->albumsReady()) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(503)->setJSON([
                    'ok' => false,
                    'message' => 'Tabel album Photographer Gallery belum siap.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->back()->with('error', 'Tabel album Photographer Gallery belum siap.');
        }

        $gallery = $this->ownedGallery($galleryId);
        $name = trim((string) $this->request->getPost('name'));
        $nameLength = strlen($name);
        if ($nameLength < 2 || $nameLength > 140) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok' => false,
                    'message' => 'Nama album harus 2-140 karakter.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->back()->with('error', 'Nama album harus 2-140 karakter.');
        }

        $album = (new PhotographerGalleryAlbumModel())->createForGallery((int) $gallery['id'], $name);
        if ($album === null) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'ok' => false,
                    'message' => 'Album belum bisa dibuat.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->back()->with('error', 'Album belum bisa dibuat.');
        }

        (new PhotographerGalleryModel())->update((int) $gallery['id'], ['updated_at' => date('Y-m-d H:i:s')]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Album dibuat.',
                'album' => [
                    'id' => (int) ($album['id'] ?? 0),
                    'name' => (string) ($album['name'] ?? $name),
                    'slug' => (string) ($album['slug'] ?? ''),
                ],
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->back()->with('success', 'Album dibuat.');
    }

    public function uploadPhoto(int $id): ResponseInterface
    {
        if ($accessDenied = $this->galleryAccessJson()) {
            return $accessDenied;
        }

        if (! $this->tablesReady()) {
            return $this->response->setStatusCode(503)->setJSON([
                'ok' => false,
                'message' => 'Database Photographer Gallery belum siap.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $gallery = $this->ownedGallery($id);
        $rules = [
            'photo' => 'uploaded[photo]|max_size[photo,20480]|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => implode(' ', $this->validator->getErrors()),
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $file = $this->request->getFile('photo');
        if (! $file || ! $file->isValid()) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'File foto tidak valid.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $albumId = $this->cleanAlbumId((int) $gallery['id'], $this->request->getPost('album_id'));

        $userId = $this->userId();
        $baseRelativeDir = 'uploads/photographer-galleries/' . $userId . '/' . (int) $gallery['id'];
        $uploadPath = FCPATH . $baseRelativeDir;
        $thumbPath = $uploadPath . '/thumbs';
        if ((! is_dir($uploadPath) && ! @mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath))
            || (! is_dir($thumbPath) && ! @mkdir($thumbPath, 0755, true) && ! is_dir($thumbPath))) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Folder upload belum bisa dibuat.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $savedName = $file->getRandomName();
        $originalName = $file->getClientName();
        $mimeType = $file->getClientMimeType();
        $fileSize = (int) $file->getSize();
        $file->move($uploadPath, $savedName);

        $relativePath = $baseRelativeDir . '/' . $savedName;
        $thumbRelativePath = $baseRelativeDir . '/thumbs/' . $savedName;
        try {
            service('image')
                ->withFile(FCPATH . $relativePath)
                ->fit(520, 520, 'center')
                ->save(FCPATH . $thumbRelativePath, 78);
        } catch (\Throwable $e) {
            $thumbRelativePath = $relativePath;
        }

        $photoModel = new PhotographerGalleryPhotoModel();
        $photoId = $photoModel->insert([
            'gallery_id' => (int) $gallery['id'],
            'user_id' => $userId,
            'album_id' => $albumId,
            'file_path' => $relativePath,
            'thumb_path' => $thumbRelativePath,
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'sort_order' => 0,
            'status' => 'uploaded',
            'uploaded_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], true);

        (new PhotographerGalleryModel())->update((int) $gallery['id'], ['updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'ok' => true,
            'csrf_hash' => csrf_hash(),
            'photo' => [
                'id' => $photoId,
                'url' => base_url($relativePath),
                'thumb_url' => base_url($thumbRelativePath),
                'name' => $originalName,
                'album_id' => $albumId,
                'status' => 'uploaded',
            ],
        ]);
    }

    public function updatePhotoMeta(int $galleryId, int $photoId): ResponseInterface
    {
        if ($accessDenied = $this->galleryAccessJson()) {
            return $accessDenied;
        }

        if (! $this->tablesReady()) {
            return $this->response->setStatusCode(503)->setJSON([
                'ok' => false,
                'message' => 'Database Photographer Gallery belum siap.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $gallery = $this->ownedGallery($galleryId);
        $photoModel = new PhotographerGalleryPhotoModel();
        $photo = $photoModel->findOwnedPhoto($photoId, (int) $gallery['id'], $this->userId());
        if ($photo === null || (string) ($photo['status'] ?? '') === 'deleted') {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Foto tidak ditemukan.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $status = strtolower(trim((string) $this->request->getPost('status')));
        if (! in_array($status, PhotographerGalleryPhotoModel::ADMIN_STATUSES, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Status foto tidak valid.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $albumId = $this->cleanAlbumId((int) $gallery['id'], $this->request->getPost('album_id'));
        $photoModel->update($photoId, [
            'album_id' => $albumId,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        (new PhotographerGalleryModel())->update((int) $gallery['id'], ['updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'ok' => true,
            'album_id' => $albumId,
            'status' => $status,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function deletePhoto(int $galleryId, int $photoId): ResponseInterface|RedirectResponse
    {
        if ($accessDenied = $this->galleryAccessJson()) {
            return $this->request->isAJAX() ? $accessDenied : redirect()->to(site_url('plans'))->with('error', 'Aktifkan Galeri Klien Fotografer untuk membuka fitur ini.');
        }

        if (! $this->tablesReady()) {
            return $this->response->setStatusCode(503)->setJSON([
                'ok' => false,
                'message' => 'Database Photographer Gallery belum siap.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $gallery = $this->ownedGallery($galleryId);
        $photoModel = new PhotographerGalleryPhotoModel();
        $photo = $photoModel->findOwnedPhoto($photoId, (int) $gallery['id'], $this->userId());
        if ($photo === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $photoModel->update($photoId, [
            'status' => 'deleted',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return redirect()->back()->with('success', 'Foto dihapus dari gallery.');
    }

    public function deleteSelectedPhotos(int $galleryId): ResponseInterface|RedirectResponse
    {
        if ($accessDenied = $this->galleryAccessJson()) {
            return $this->request->isAJAX() ? $accessDenied : redirect()->to(site_url('plans'))->with('error', 'Aktifkan Galeri Klien Fotografer untuk membuka fitur ini.');
        }

        if (! $this->tablesReady()) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(503)->setJSON([
                    'ok' => false,
                    'message' => 'Database Photographer Gallery belum siap.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->to(site_url('photographer-galleries'))->with('error', 'Database Photographer Gallery belum siap.');
        }

        $gallery = $this->ownedGallery($galleryId);
        $photoIds = $this->request->getPost('photo_ids');
        if (! is_array($photoIds)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok' => false,
                    'message' => 'Pilih minimal satu foto dahulu.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->back()->with('error', 'Pilih minimal satu foto dahulu.');
        }

        $photoIds = array_values(array_unique(array_filter(array_map(static function (mixed $value): int {
            return (int) $value;
        }, $photoIds), static fn (int $value): bool => $value > 0)));

        if ($photoIds === []) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok' => false,
                    'message' => 'Pilih minimal satu foto dahulu.',
                    'csrf_hash' => csrf_hash(),
                ]);
            }

            return redirect()->back()->with('error', 'Pilih minimal satu foto dahulu.');
        }

        $db = Database::connect();
        $db->table('photographer_gallery_photos')
            ->where('gallery_id', (int) $gallery['id'])
            ->where('user_id', $this->userId())
            ->where('status !=', 'deleted')
            ->whereIn('id', $photoIds)
            ->update([
                'status' => 'deleted',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        (new PhotographerGalleryModel())->update((int) $gallery['id'], ['updated_at' => date('Y-m-d H:i:s')]);

        $deletedCount = max(0, (int) $db->affectedRows());
        $message = $deletedCount > 0
            ? $deletedCount . ' foto dihapus dari gallery.'
            : 'Tidak ada foto yang bisa dihapus.';

        if ($this->request->isAJAX()) {
            return $this->response
                ->setStatusCode($deletedCount > 0 ? 200 : 422)
                ->setJSON([
                    'ok' => $deletedCount > 0,
                    'deleted_count' => $deletedCount,
                    'message' => $message,
                    'csrf_hash' => csrf_hash(),
                ]);
        }

        return redirect()->back()->with(
            $deletedCount > 0 ? 'success' : 'error',
            $message
        );
    }

    private function ownedGallery(int $id): array
    {
        $gallery = (new PhotographerGalleryModel())->findOwned($id, $this->userId());
        if ($gallery === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $gallery;
    }

    private function cleanAlbumId(int $galleryId, mixed $value): ?int
    {
        $albumId = (int) $value;
        if ($albumId <= 0 || ! $this->albumsReady()) {
            return null;
        }

        return (new PhotographerGalleryAlbumModel())->belongsToGallery($albumId, $galleryId)
            ? $albumId
            : null;
    }

    private function uniqueSlug(string $value, PhotographerGalleryModel $model, ?int $ignoreId = null): string
    {
        helper('url');

        $base = url_title($value, '-', true);
        $base = trim($base, '-');
        if ($base === '') {
            $base = 'gallery-' . date('YmdHis');
        }

        $slug = $base;
        $counter = 2;
        while ($model->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function nullableDate(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function clampedInt(mixed $value, int $default, int $min, int $max): int
    {
        $number = is_numeric($value) ? (int) $value : $default;

        return max($min, min($max, $number));
    }

    private function storeCoverPhoto(): ?string
    {
        $file = $this->request->getFile('cover_photo');
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $userId = $this->userId();
        $relativeDir = 'uploads/photographer-galleries/' . $userId . '/covers';
        $uploadPath = FCPATH . $relativeDir;
        if (! is_dir($uploadPath) && ! @mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            return null;
        }

        $savedName = $file->getRandomName();
        $file->move($uploadPath, $savedName);

        return $relativeDir . '/' . $savedName;
    }
}
