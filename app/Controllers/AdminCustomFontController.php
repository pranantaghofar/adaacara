<?php

namespace App\Controllers;

use App\Models\CustomFontModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminCustomFontController extends BaseController
{
    private CustomFontModel $fontModel;

    public function __construct()
    {
        helper('admin_permission');
        $this->fontModel = new CustomFontModel();
    }

    public function index(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.assets.view', 'assets')) {
            return $deny;
        }

        if (! $this->fontModel->tableReady()) {
            return view('admin/custom_fonts/setup');
        }

        $fonts = $this->fontModel
            ->orderBy('sort_order', 'ASC')
            ->orderBy('font_family', 'ASC')
            ->orderBy('font_weight', 'ASC')
            ->findAll();

        return view('admin/custom_fonts/index', [
            'fonts' => $fonts,
            'activeCount' => (int) $this->fontModel->where('is_active', 1)->countAllResults(),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (! admin_can('admin.assets.manage')) {
            return redirect()->to(admin_access_denied_url('assets'))->with('error', 'Akses terbatas.');
        }

        if (! $this->fontModel->tableReady()) {
            return redirect()->to('/admin/custom-fonts')
                ->with('error', 'Tabel custom_fonts belum tersedia. Jalankan SQL setup terlebih dahulu.');
        }

        $rules = [
            'font_family' => 'required|min_length[2]|max_length[120]',
            'font_weight' => 'required|in_list[100,200,300,400,500,600,700,800,900]',
            'font_style' => 'required|in_list[normal,italic]',
            'sort_order' => 'permit_empty|integer',
            'font_file' => 'uploaded[font_file]|max_size[font_file,4096]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $fontPath = $this->uploadFont();
        if ($fontPath === false) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['font_file' => 'File font gagal diupload.']);
        }

        $file = $this->request->getFile('font_file');
        $this->fontModel->insert([
            'font_family' => trim((string) $this->request->getPost('font_family')),
            'font_weight' => (int) $this->request->getPost('font_weight'),
            'font_style' => (string) $this->request->getPost('font_style'),
            'file_path' => $fontPath,
            'mime_type' => $file ? (string) $file->getClientMimeType() : '',
            'original_name' => $file ? (string) $file->getClientName() : '',
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
            'is_active' => $this->request->getPost('is_active') === '0' ? 0 : 1,
        ]);

        return redirect()->to('/admin/custom-fonts')->with('success', 'Font custom berhasil ditambahkan.');
    }

    public function toggle(int $id): RedirectResponse
    {
        if (! admin_can('admin.assets.manage')) {
            return redirect()->to(admin_access_denied_url('assets'))->with('error', 'Akses terbatas.');
        }

        $font = $this->findFont($id);
        $nextStatus = (int) ($font['is_active'] ?? 0) === 1 ? 0 : 1;

        $this->fontModel->update($id, ['is_active' => $nextStatus]);

        return redirect()->to('/admin/custom-fonts')
            ->with('success', $nextStatus === 1 ? 'Font diaktifkan.' : 'Font dinonaktifkan.');
    }

    public function delete(int $id): RedirectResponse
    {
        if (! admin_can('admin.assets.delete')) {
            return redirect()->to(admin_access_denied_url('assets'))->with('error', 'Akses terbatas.');
        }

        $font = $this->findFont($id);

        try {
            $this->fontModel->delete($id);
        } catch (DatabaseException) {
            return redirect()->to('/admin/custom-fonts')->with('error', 'Font belum bisa dihapus.');
        }

        $this->deleteFontFile((string) ($font['file_path'] ?? ''));

        log_message('warning', 'Admin custom font deleted. admin_id={admin_id} admin_role={admin_role} target_id={target_id} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'admin_role' => current_admin_role(),
            'target_id' => (string) $id,
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/admin/custom-fonts')->with('success', 'Font custom berhasil dihapus.');
    }

    private function findFont(int $id): array
    {
        if (! $this->fontModel->tableReady()) {
            throw PageNotFoundException::forPageNotFound('Font custom belum tersedia.');
        }

        $font = $this->fontModel->find($id);
        if ($font === null) {
            throw PageNotFoundException::forPageNotFound('Font custom tidak ditemukan.');
        }

        return $font;
    }

    private function uploadFont(): string|false
    {
        $file = $this->request->getFile('font_file');
        if (! $file || ! $file->isValid()) {
            return false;
        }

        $extension = strtolower((string) pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = strtolower($file->getClientExtension());
        }

        if (! in_array($extension, ['woff2', 'woff', 'ttf', 'otf'], true)) {
            return false;
        }

        $uploadPath = FCPATH . 'uploads/fonts';
        if (! is_dir($uploadPath) && ! mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            return false;
        }

        try {
            $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        } catch (\Throwable) {
            $fileName = uniqid('font_', true) . '.' . $extension;
        }
        $file->move($uploadPath, $fileName, true);

        return 'uploads/fonts/' . $fileName;
    }

    private function deleteFontFile(string $fontPath): void
    {
        if ($fontPath === '') {
            return;
        }

        $path = FCPATH . ltrim($fontPath, '/');
        $realPath = realpath($path);
        $realBase = realpath(FCPATH . 'uploads/fonts');
        if ($realPath && $realBase && is_file($realPath) && str_starts_with($realPath, $realBase)) {
            @unlink($realPath);
        }
    }
}
