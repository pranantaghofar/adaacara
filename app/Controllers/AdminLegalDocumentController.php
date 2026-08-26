<?php

namespace App\Controllers;

use App\Libraries\CompanyLegalDocuments;
use App\Models\AppSettingModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminLegalDocumentController extends BaseController
{
    public function __construct()
    {
        helper('admin_permission');
    }

    public function index(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.settings.sensitive', 'settings')) {
            return $deny;
        }

        return view('admin/legal_documents', [
            'documents' => CompanyLegalDocuments::load(),
        ]);
    }

    public function upload(): RedirectResponse
    {
        if (! admin_can('admin.settings.sensitive')) {
            return redirect()->to(admin_access_denied_url('settings'))->with('error', 'Akses terbatas.');
        }

        $key = strtolower(trim((string) $this->request->getPost('document_key')));
        if (! CompanyLegalDocuments::isValidKey($key)) {
            return redirect()->back()->with('error', 'Jenis dokumen tidak valid.');
        }

        $rules = [
            'document' => 'uploaded[document]|max_size[document,4096]|is_image[document]|mime_in[document,image/png]|ext_in[document,png]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Upload gagal. Gunakan file PNG maksimal 4MB.');
        }

        $file = $this->request->getFile('document');
        if ($file === null || ! $file->isValid()) {
            return redirect()->back()->with('error', 'File dokumen tidak valid.');
        }

        $directory = FCPATH . 'uploads/legal-documents';
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return redirect()->back()->with('error', 'Folder upload dokumen tidak bisa dibuat.');
        }

        $fileName = $key . '-' . bin2hex(random_bytes(6)) . '.png';
        $file->move($directory, $fileName, true);

        $relativePath = 'uploads/legal-documents/' . $fileName;
        $settings = new AppSettingModel();
        $documents = CompanyLegalDocuments::load($settings);
        $oldPath = (string) ($documents[$key]['path'] ?? '');
        $documents[$key]['path'] = $relativePath;
        $documents[$key]['updated_at'] = date('Y-m-d H:i:s');
        CompanyLegalDocuments::save($documents, (int) session()->get('userId'), $settings);

        $this->deleteLocalDocument($oldPath);

        log_message('warning', 'Admin legal document uploaded. admin_id={admin_id} role={role} document={document} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'role' => current_admin_role(),
            'document' => $key,
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/admin/legal-documents')->with('success', 'Dokumen legal berhasil diupload.');
    }

    public function delete(string $key): RedirectResponse
    {
        if (! admin_can('admin.settings.sensitive')) {
            return redirect()->to(admin_access_denied_url('settings'))->with('error', 'Akses terbatas.');
        }

        $key = strtolower(trim($key));
        if (! CompanyLegalDocuments::isValidKey($key)) {
            return redirect()->back()->with('error', 'Jenis dokumen tidak valid.');
        }

        $settings = new AppSettingModel();
        $documents = CompanyLegalDocuments::load($settings);
        $oldPath = (string) ($documents[$key]['path'] ?? '');
        $documents[$key]['path'] = '';
        $documents[$key]['updated_at'] = '';
        CompanyLegalDocuments::save($documents, (int) session()->get('userId'), $settings);
        $this->deleteLocalDocument($oldPath);

        log_message('warning', 'Admin legal document deleted. admin_id={admin_id} role={role} document={document} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'role' => current_admin_role(),
            'document' => $key,
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/admin/legal-documents')->with('success', 'Dokumen legal berhasil dihapus.');
    }

    private function deleteLocalDocument(string $relativePath): void
    {
        $relativePath = CompanyLegalDocuments::cleanPath($relativePath);
        if ($relativePath === '') {
            return;
        }

        $path = FCPATH . $relativePath;
        $realPath = realpath($path);
        $realBase = realpath(FCPATH . 'uploads/legal-documents');
        if ($realPath !== false && $realBase !== false && str_starts_with($realPath, $realBase) && is_file($realPath)) {
            @unlink($realPath);
        }
    }
}
