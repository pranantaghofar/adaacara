<?php

namespace App\Controllers;

use App\Libraries\SellerTemplateService;
use App\Models\SellerWithdrawRequestModel;
use App\Models\TemplateModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class AdminSellerController extends BaseController
{
    private SellerTemplateService $sellerService;

    public function __construct()
    {
        helper('admin_permission');
        $this->sellerService = new SellerTemplateService();
    }

    public function templates(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.view', 'templates')) {
            return $deny;
        }

        $db = Database::connect();
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $publicStatus = trim((string) ($this->request->getGet('public_status') ?? ''));
        $search = trim((string) ($this->request->getGet('q') ?? ''));

        $builder = $db->table('templates')
            ->select('templates.*, users.name AS owner_name, users.email AS owner_email')
            ->join('users', 'users.id = templates.owner_user_id', 'left')
            ->where('templates.owner_user_id IS NOT NULL', null, false)
            ->orderBy('templates.updated_at', 'DESC');

        if ($status !== '') {
            $builder->where('templates.review_status', $status);
        }

        if ($publicStatus !== '') {
            $builder->where('templates.public_status', $publicStatus);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('templates.name', $search)
                ->orLike('templates.slug', $search)
                ->orLike('users.name', $search)
                ->orLike('users.email', $search)
                ->groupEnd();
        }

        return view('admin/seller_templates/index', [
            'templates' => $builder->get()->getResultArray(),
            'status' => $status,
            'publicStatus' => $publicStatus,
            'search' => $search,
        ]);
    }

    public function templateDetail(int $id): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.view', 'templates')) {
            return $deny;
        }

        return view('admin/seller_templates/show', [
            'template' => $this->sellerTemplate($id),
        ]);
    }

    public function approveTemplate(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.review')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $template = $this->sellerTemplate($id);

        if (! in_array((string) ($template['review_status'] ?? ''), ['pending', 'rejected'], true)) {
            return redirect()->back()->with('error', 'Template ini tidak dalam status review.');
        }

        $limitMessage = $this->activePublicLimitMessage($template);
        if ($limitMessage !== '') {
            return redirect()->back()->with('error', $limitMessage);
        }

        (new TemplateModel())->update($id, $this->filterTemplateColumns([
            'review_status' => 'approved',
            'public_status' => 'public',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => (int) (session()->get('userId') ?? 0) ?: null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
            'status' => 'active',
            'is_active' => 1,
        ]));

        return redirect()->to(site_url('admin/seller-templates'))->with('success', 'Template seller disetujui dan tampil publik.');
    }

    public function rejectTemplate(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.review')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $this->sellerTemplate($id);
        $reason = trim((string) ($this->request->getPost('rejection_reason') ?? ''));

        if ($reason === '') {
            return redirect()->back()->with('error', 'Alasan reject wajib diisi.');
        }

        (new TemplateModel())->update($id, $this->filterTemplateColumns([
            'review_status' => 'rejected',
            'public_status' => 'private',
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejected_by' => (int) (session()->get('userId') ?? 0) ?: null,
            'rejection_reason' => $reason,
        ]));

        return redirect()->to(site_url('admin/seller-templates'))->with('success', 'Template seller ditolak.');
    }

    public function archiveTemplate(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $this->sellerTemplate($id);
        (new TemplateModel())->update($id, $this->filterTemplateColumns([
            'public_status' => 'archived',
        ]));

        return redirect()->to(site_url('admin/seller-templates'))->with('success', 'Template seller diarsipkan.');
    }

    public function withdrawRequests(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.withdraw.view', 'withdraw')) {
            return $deny;
        }

        $db = Database::connect();
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $search = trim((string) ($this->request->getGet('q') ?? ''));

        $builder = $db->table('seller_withdraw_requests')
            ->select('seller_withdraw_requests.*, users.name AS user_name, users.email AS user_email')
            ->join('users', 'users.id = seller_withdraw_requests.user_id', 'left');

        if ($status !== '') {
            $builder->where('seller_withdraw_requests.status', $status);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('users.name', $search)
                ->orLike('users.email', $search)
                ->orLike('seller_withdraw_requests.bank_name', $search)
                ->orLike('seller_withdraw_requests.account_number', $search)
                ->orLike('seller_withdraw_requests.account_holder_name', $search)
                ->groupEnd();
        }

        $withdraws = $builder
            ->orderBy('seller_withdraw_requests.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/seller_withdraws/index', [
            'withdraws' => $withdraws,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ]);
    }

    public function approveWithdraw(int $id): RedirectResponse
    {
        if (! admin_can('admin.withdraw.approve')) {
            return redirect()->to(admin_access_denied_url('withdraw'))->with('error', 'Akses terbatas.');
        }

        return $this->updateWithdraw($id, 'approve', 'Withdraw disetujui.');
    }

    public function rejectWithdraw(int $id): RedirectResponse
    {
        if (! admin_can('admin.withdraw.reject')) {
            return redirect()->to(admin_access_denied_url('withdraw'))->with('error', 'Akses terbatas.');
        }

        return $this->updateWithdraw($id, 'reject', 'Withdraw ditolak.');
    }

    public function markWithdrawPaid(int $id): RedirectResponse
    {
        if (! admin_can('admin.withdraw.manage')) {
            return redirect()->to(admin_access_denied_url('withdraw'))->with('error', 'Akses terbatas.');
        }

        return $this->updateWithdraw($id, 'paid', 'Withdraw ditandai sudah dibayar.');
    }

    private function updateWithdraw(int $id, string $action, string $success): RedirectResponse
    {
        $note = trim((string) ($this->request->getPost('admin_note') ?? ''));

        if ($action === 'reject' && $note === '') {
            return redirect()->back()->with('error', 'Catatan reject wajib diisi.');
        }

        try {
            $this->sellerService->updateWithdrawStatus((int) session()->get('userId'), $id, $action, $note);
        } catch (\Throwable $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }

        log_message('warning', 'Admin withdraw action. admin_id={admin_id} admin_role={admin_role} target_id={target_id} action={action} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'admin_role' => current_admin_role(),
            'target_id' => (string) $id,
            'action' => $action,
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to(site_url('admin/seller-withdraw-requests'))->with('success', $success);
    }

    private function sellerTemplate(int $id): array
    {
        $template = Database::connect()->table('templates')
            ->select('templates.*, users.name AS owner_name, users.email AS owner_email')
            ->join('users', 'users.id = templates.owner_user_id', 'left')
            ->where('templates.id', $id)
            ->where('templates.owner_user_id IS NOT NULL', null, false)
            ->get()
            ->getRowArray();

        if ($template === null) {
            throw PageNotFoundException::forPageNotFound('Template seller tidak ditemukan.');
        }

        return $template;
    }

    private function activePublicLimitMessage(array $template): string
    {
        $limits = $this->sellerService->planLimits($template);
        $maxPublic = $limits['max_public_templates'];
        if ($maxPublic === null) {
            return '';
        }

        $count = Database::connect()->table('templates')
            ->where('owner_user_id', (int) ($template['owner_user_id'] ?? 0))
            ->where('review_status', 'approved')
            ->where('public_status', 'public')
            ->where('id !=', (int) ($template['id'] ?? 0))
            ->countAllResults();

        if ($count >= (int) $maxPublic) {
            return 'Limit template public aktif untuk paket ini sudah tercapai.';
        }

        return '';
    }

    private function filterTemplateColumns(array $data): array
    {
        $fields = Database::connect()->getFieldNames('templates');

        return array_intersect_key($data, array_flip($fields));
    }
}
