<?php

namespace App\Controllers;

use App\Libraries\MarketplaceReviewService;
use App\Models\CreatorProfileModel;
use App\Models\CreatorTemplateOwnershipModel;
use App\Models\MarketplaceTemplateActivityLogModel;
use App\Models\MarketplaceTemplateModel;
use App\Models\MarketplaceTemplateReviewModel;
use App\Models\TemplateModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminMarketplaceController extends BaseController
{
    public function __construct()
    {
        helper('admin_permission');
    }

    public function index(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.view', 'templates')) {
            return $deny;
        }

        $status = (string) $this->request->getGet('status');
        $allowedStatuses = MarketplaceTemplateModel::MARKETPLACE_STATUSES;
        $statusFilter = in_array($status, $allowedStatuses, true) ? $status : null;
        $search = trim((string) $this->request->getGet('q'));
        $category = trim((string) $this->request->getGet('category'));
        $priceType = (string) $this->request->getGet('price_type');
        $sort = (string) ($this->request->getGet('sort') ?: 'newest');

        return view('admin/marketplace_templates/index', [
            'marketplaceTemplates' => (new MarketplaceTemplateModel())->adminList($statusFilter, $search, $category, $priceType, $sort),
            'ownerships' => (new CreatorTemplateOwnershipModel())->adminList(),
            'templates' => $this->unownedTemplates(),
            'creators' => (new CreatorProfileModel())->where('status', 'active')->orderBy('display_name', 'ASC')->findAll(),
            'statusFilter' => $statusFilter,
            'search' => $search,
            'categoryFilter' => $category,
            'priceType' => $priceType,
            'sort' => $sort,
        ]);
    }

    public function summary(): ResponseInterface
    {
        if (! admin_can('admin.templates.view')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Akses terbatas.']);
        }

        $summary = [
            'total' => (new MarketplaceTemplateModel())->countAllResults(),
            'submitted' => (new MarketplaceTemplateModel())->where('marketplace_status', 'submitted')->countAllResults(),
            'approved' => (new MarketplaceTemplateModel())->where('marketplace_status', 'approved')->countAllResults(),
            'rejected' => (new MarketplaceTemplateModel())->where('marketplace_status', 'rejected')->countAllResults(),
            'changes_requested' => (new MarketplaceTemplateModel())->where('marketplace_status', 'changes_requested')->countAllResults(),
            'archived' => (new MarketplaceTemplateModel())->where('marketplace_status', 'archived')->countAllResults(),
        ];

        return $this->response->setJSON(['success' => true, 'summary' => $summary]);
    }

    public function assign(): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $templateId = (int) $this->request->getPost('template_id');
        $creatorId = (int) $this->request->getPost('creator_id');

        if ($templateId <= 0 || $creatorId <= 0) {
            return redirect()->back()->with('error', 'Template dan creator wajib dipilih.');
        }

        if ((new TemplateModel())->find($templateId) === null) {
            return redirect()->back()->with('error', 'Template tidak ditemukan.');
        }

        $creator = (new CreatorProfileModel())->where('status', 'active')->find($creatorId);
        if ($creator === null) {
            return redirect()->back()->with('error', 'Creator aktif tidak ditemukan.');
        }

        $ownershipModel = new CreatorTemplateOwnershipModel();
        if ($ownershipModel->findForTemplate($templateId) !== null) {
            return redirect()->back()->with('error', 'Template ini sudah memiliki ownership creator.');
        }

        $created = $ownershipModel->insert([
            'template_id' => $templateId,
            'creator_id' => $creatorId,
            'assigned_by' => (int) session()->get('userId'),
            'ownership_type' => 'admin_assigned',
        ]);

        if (! $created) {
            return redirect()->back()->with('error', 'Ownership template gagal disimpan.');
        }

        return redirect()->to('/admin/marketplace-templates')->with('success', 'Template berhasil diassign ke creator.');
    }

    public function show(int $id): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.view', 'templates')) {
            return $deny;
        }

        $marketplace = (new MarketplaceTemplateModel())->adminFind($id);
        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Template marketplace tidak ditemukan.');
        }

        return view('admin/marketplace_templates/show', [
            'marketplace' => $marketplace,
            'reviewChecklist' => MarketplaceReviewService::REVIEW_CHECKLIST,
            'reviews' => (new MarketplaceTemplateReviewModel())->historyForTemplate($id),
            'activityLogs' => (new MarketplaceTemplateActivityLogModel())->historyForTemplate($id),
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.review')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $marketplace = (new MarketplaceTemplateModel())->adminFind($id);

        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Template marketplace tidak ditemukan.');
        }

        $checklist = $this->reviewChecklistPayload();
        $service = new MarketplaceReviewService();

        if (! $service->checklistComplete($checklist)) {
            return redirect()->back()->with('error', 'Checklist review harus lengkap sebelum approve.');
        }

        if (! $service->approve($marketplace, (int) session()->get('userId'), $checklist, (string) $this->request->getPost('admin_notes'))) {
            return redirect()->back()->with('error', 'Approve template marketplace gagal atau status tidak valid.');
        }

        return redirect()->to('/admin/marketplace-templates/' . $id)->with('success', 'Template marketplace berhasil diapprove.');
    }

    public function reject(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.review')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $reason = trim((string) $this->request->getPost('rejection_reason'));
        if ($reason === '') {
            return redirect()->back()->withInput()->with('error', 'Alasan rejection wajib diisi.');
        }

        if (mb_strlen($reason) > 1000) {
            return redirect()->back()->withInput()->with('error', 'Alasan rejection maksimal 1000 karakter.');
        }

        $marketplace = (new MarketplaceTemplateModel())->adminFind($id);

        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Template marketplace tidak ditemukan.');
        }

        if (! (new MarketplaceReviewService())->reject($marketplace, (int) session()->get('userId'), $reason, $this->reviewChecklistPayload(), (string) $this->request->getPost('admin_notes'))) {
            return redirect()->back()->with('error', 'Reject template marketplace gagal atau status tidak valid.');
        }

        return redirect()->to('/admin/marketplace-templates/' . $id)->with('success', 'Template marketplace berhasil direject.');
    }

    public function requestChanges(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.review')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $reason = trim((string) $this->request->getPost('rejection_reason'));
        if ($reason === '') {
            return redirect()->back()->withInput()->with('error', 'Catatan perubahan wajib diisi.');
        }

        $marketplace = (new MarketplaceTemplateModel())->adminFind($id);

        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Template marketplace tidak ditemukan.');
        }

        if (! (new MarketplaceReviewService())->requestChanges($marketplace, (int) session()->get('userId'), $reason, $this->reviewChecklistPayload(), (string) $this->request->getPost('admin_notes'))) {
            return redirect()->back()->with('error', 'Request changes gagal atau status tidak valid.');
        }

        return redirect()->to('/admin/marketplace-templates/' . $id)->with('success', 'Template marketplace ditandai perlu revisi.');
    }

    public function archive(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $marketplace = (new MarketplaceTemplateModel())->adminFind($id);

        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Template marketplace tidak ditemukan.');
        }

        if (! (new MarketplaceReviewService())->archiveByAdmin($marketplace, (int) session()->get('userId'), (string) $this->request->getPost('admin_notes'))) {
            return redirect()->back()->with('error', 'Archive template marketplace gagal.');
        }

        return redirect()->to('/admin/marketplace-templates/' . $id)->with('success', 'Template marketplace berhasil diarchive.');
    }

    private function unownedTemplates(): array
    {
        $owned = array_map(
            static fn (array $row): int => (int) $row['template_id'],
            (new CreatorTemplateOwnershipModel())->select('template_id')->findAll()
        );

        $builder = (new TemplateModel())->orderBy('name', 'ASC');
        if ($owned !== []) {
            $builder->whereNotIn('id', $owned);
        }

        return $builder->findAll();
    }

    private function reviewChecklistPayload(): array
    {
        $payload = [];
        foreach (array_keys(MarketplaceReviewService::REVIEW_CHECKLIST) as $key) {
            $payload[$key] = (string) $this->request->getPost('checklist_' . $key) === '1' ? '1' : '0';
        }

        return $payload;
    }
}
