<?php

namespace App\Controllers;

use App\Models\PublishedDomainModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminPublishRequestController extends BaseController
{
    private const STATUS_OPTIONS = [
        'pending_activation',
        'activating',
        'active',
        'failed',
        'suspended',
        'disabled',
    ];

    private const PROJECT_TYPE_OPTIONS = [
        'invitation',
        'photobooth',
        'business_profile',
    ];

    public function index(): string|RedirectResponse|ResponseInterface
    {
        helper('admin_permission');
        if ($deny = admin_require('admin.publish_domains.view', 'publish-domains')) {
            return $deny;
        }

        $model = new PublishedDomainModel();
        $isReady = $model->tableReady();
        $filters = [
            'q' => trim((string) $this->request->getGet('q')),
            'status' => trim((string) $this->request->getGet('status')),
            'project_type' => trim((string) $this->request->getGet('project_type')),
        ];

        if (! in_array($filters['status'], self::STATUS_OPTIONS, true)) {
            $filters['status'] = '';
        }
        if (! in_array($filters['project_type'], self::PROJECT_TYPE_OPTIONS, true)) {
            $filters['project_type'] = '';
        }

        return view('admin/publish_requests/index', [
            'adminTitle' => 'Publish Requests',
            'adminKicker' => 'Domain',
            'adminIcon' => 'globe',
            'adminActive' => 'publishDomains',
            'items' => $isReady ? $model->activationRequests($filters, 200) : [],
            'isReady' => $isReady,
            'filters' => $filters,
            'statusOptions' => self::STATUS_OPTIONS,
            'projectTypeOptions' => self::PROJECT_TYPE_OPTIONS,
            'documentRoot' => defined('FCPATH') ? rtrim(FCPATH, DIRECTORY_SEPARATOR) : '',
        ]);
    }

    public function quickUpdate(int $id, string $action): RedirectResponse|ResponseInterface
    {
        helper('admin_permission');
        if ($deny = admin_require('admin.publish_domains.manage', 'publish-domains')) {
            return $deny;
        }

        $status = match ($action) {
            'activating' => 'activating',
            'active' => 'active',
            'failed' => 'failed',
            'suspended' => 'suspended',
            'disabled' => 'disabled',
            default => '',
        };

        if ($status === '') {
            return redirect()->back()->with('error', 'Aksi publish request tidak valid.');
        }

        return $this->updateStatus($id, $status, trim((string) $this->request->getPost('activation_notes')));
    }

    public function update(int $id): RedirectResponse|ResponseInterface
    {
        helper('admin_permission');
        if ($deny = admin_require('admin.publish_domains.manage', 'publish-domains')) {
            return $deny;
        }

        $status = trim((string) $this->request->getPost('status'));
        if (! in_array($status, self::STATUS_OPTIONS, true)) {
            return redirect()->back()->with('error', 'Status publish request tidak valid.');
        }

        return $this->updateStatus(
            $id,
            $status,
            trim((string) $this->request->getPost('activation_notes')),
            trim((string) $this->request->getPost('admin_notes'))
        );
    }

    private function updateStatus(int $id, string $status, string $activationNotes = '', string $adminNotes = ''): RedirectResponse
    {
        $model = new PublishedDomainModel();
        if (! $model->tableReady()) {
            return redirect()->back()->with('error', 'Tabel published_domains belum tersedia.');
        }

        $row = $model->find($id);
        if (! is_array($row)) {
            return redirect()->back()->with('error', 'Publish request tidak ditemukan.');
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'status' => $status,
            'activation_notes' => $activationNotes,
            'admin_notes' => $adminNotes,
        ];

        if ($status === 'active') {
            $data['activated_at'] = $now;
            $data['failed_at'] = null;
        } elseif ($status === 'failed') {
            $data['failed_at'] = $now;
        }

        $model->update($id, $model->filterExistingFields($data));

        return redirect()->back()->with('success', 'Status publish request berhasil diperbarui.');
    }
}
