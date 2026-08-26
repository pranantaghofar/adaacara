<?php

namespace App\Controllers;

use App\Models\EditorAdModel;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminEditorAdController extends BaseController
{
    private EditorAdModel $adModel;

    public function __construct()
    {
        helper('admin_permission');
        $this->adModel = new EditorAdModel();
    }

    public function index(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.assets.view', 'assets')) {
            return $deny;
        }

        if (! $this->adModel->tableReady()) {
            return view('admin/editor_ads/setup');
        }

        $ads = $this->adModel
            ->orderBy('priority', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('admin/editor_ads/index', [
            'ads' => array_map(fn (array $ad): array => $this->withTargetLabel($ad), $ads),
            'targetOptions' => $this->targetOptions(),
            'users' => $this->userOptions(),
            'activeCount' => $this->adModel->activeCount(),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (! admin_can('admin.assets.manage')) {
            return redirect()->to(admin_access_denied_url('assets'))->with('error', 'Akses terbatas.');
        }

        if (! $this->adModel->tableReady()) {
            return redirect()->to('/admin/editor-ads')
                ->with('error', 'Tabel editor_ads belum tersedia. Jalankan SQL setup terlebih dahulu.');
        }

        $rules = [
            'title' => 'required|min_length[2]|max_length[120]',
            'link_url' => 'permit_empty|valid_url_strict|max_length[500]',
            'target_type' => 'required|in_list[' . implode(',', EditorAdModel::TARGET_TYPES) . ']',
            'target_user_id' => 'permit_empty|is_natural_no_zero',
            'priority' => 'permit_empty|integer',
            'sort_order' => 'permit_empty|integer',
            'starts_at' => 'permit_empty',
            'ends_at' => 'permit_empty',
            'image' => 'uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]|ext_in[image,jpg,jpeg,png,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $targetType = (string) $this->request->getPost('target_type');
        $targetUserId = $targetType === 'user_specific' ? (int) $this->request->getPost('target_user_id') : null;
        if ($targetType === 'user_specific' && $targetUserId <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['target_user_id' => 'Pilih user untuk target user tertentu.']);
        }

        $isActive = $this->request->getPost('is_active') === '1' ? 1 : 0;
        $imagePath = $this->uploadImage();
        if ($imagePath === false) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['image' => 'Gambar iklan gagal diupload.']);
        }

        $this->adModel->insert([
            'title' => trim((string) $this->request->getPost('title')),
            'image_path' => $imagePath,
            'link_url' => trim((string) $this->request->getPost('link_url')),
            'target_type' => $targetType,
            'target_user_id' => $targetUserId,
            'priority' => (int) ($this->request->getPost('priority') ?: 10),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
            'is_active' => $isActive,
            'starts_at' => $this->datetimeValue('starts_at'),
            'ends_at' => $this->datetimeValue('ends_at'),
        ]);

        return redirect()->to('/admin/editor-ads')->with('success', 'Iklan editor berhasil ditambahkan.');
    }

    public function toggle(int $id): RedirectResponse
    {
        if (! admin_can('admin.assets.manage')) {
            return redirect()->to(admin_access_denied_url('assets'))->with('error', 'Akses terbatas.');
        }

        $ad = $this->findAd($id);
        $nextStatus = (int) ($ad['is_active'] ?? 0) === 1 ? 0 : 1;

        $this->adModel->update($id, ['is_active' => $nextStatus]);

        return redirect()->to('/admin/editor-ads')
            ->with('success', $nextStatus === 1 ? 'Iklan diaktifkan.' : 'Iklan dinonaktifkan.');
    }

    public function delete(int $id): RedirectResponse
    {
        if (! admin_can('admin.assets.delete')) {
            return redirect()->to(admin_access_denied_url('assets'))->with('error', 'Akses terbatas.');
        }

        $ad = $this->findAd($id);

        try {
            $this->adModel->delete($id);
        } catch (DatabaseException) {
            return redirect()->to('/admin/editor-ads')->with('error', 'Iklan belum bisa dihapus.');
        }

        $this->deleteImage((string) ($ad['image_path'] ?? ''));

        log_message('warning', 'Admin editor ad deleted. admin_id={admin_id} admin_role={admin_role} target_id={target_id} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'admin_role' => current_admin_role(),
            'target_id' => (string) $id,
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/admin/editor-ads')->with('success', 'Iklan editor berhasil dihapus.');
    }

    private function findAd(int $id): array
    {
        if (! $this->adModel->tableReady()) {
            throw PageNotFoundException::forPageNotFound('Iklan editor belum tersedia.');
        }

        $ad = $this->adModel->find($id);
        if ($ad === null) {
            throw PageNotFoundException::forPageNotFound('Iklan editor tidak ditemukan.');
        }

        return $ad;
    }

    private function uploadImage(): string|false
    {
        $file = $this->request->getFile('image');
        if (! $file || ! $file->isValid()) {
            return false;
        }

        $uploadPath = FCPATH . 'uploads/editor-ads';
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            return false;
        }

        $fileName = $file->getRandomName();
        $file->move($uploadPath, $fileName, true);

        return 'uploads/editor-ads/' . $fileName;
    }

    private function deleteImage(string $imagePath): void
    {
        if ($imagePath === '') {
            return;
        }

        $path = FCPATH . ltrim($imagePath, '/');
        $realPath = realpath($path);
        $realBase = realpath(FCPATH . 'uploads/editor-ads');
        if ($realPath && $realBase && is_file($realPath) && str_starts_with($realPath, $realBase)) {
            @unlink($realPath);
        }
    }

    private function datetimeValue(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    private function targetOptions(): array
    {
        return [
            'all' => 'Semua user',
            'free' => 'Free user',
            'member' => 'Member aktif',
            'creator' => 'Creator aktif',
            'user_specific' => 'User tertentu',
        ];
    }

    private function userOptions(): array
    {
        if (! db_connect()->tableExists('users')) {
            return [];
        }

        return (new UserModel())
            ->select('id, name, email')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll(200);
    }

    private function withTargetLabel(array $ad): array
    {
        $options = $this->targetOptions();
        $ad['target_label'] = $options[(string) ($ad['target_type'] ?? 'all')] ?? 'Semua user';

        if (($ad['target_type'] ?? '') === 'user_specific' && ! empty($ad['target_user_id'])) {
            $user = (new UserModel())->find((int) $ad['target_user_id']);
            $ad['target_label'] = $user
                ? 'User: ' . ($user['name'] ?? $user['email'] ?? ('#' . $ad['target_user_id']))
                : 'User #' . $ad['target_user_id'];
        }

        return $ad;
    }
}
