<?php

namespace App\Controllers;

use App\Libraries\SellerTemplateService;
use App\Libraries\CreatorRoyaltyService;
use App\Models\CategoryModel;
use App\Models\CreatorProfileModel;
use App\Models\SellerLeadModel;
use App\Models\SellerWalletLedgerModel;
use App\Models\SellerWithdrawRequestModel;
use App\Models\TemplateModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class SellerTemplateController extends BaseController
{
    private SellerTemplateService $sellerService;

    public function __construct()
    {
        $this->sellerService = new SellerTemplateService();
    }

    public function saveFromEditor(): ResponseInterface
    {
        $userId = (int) (session()->get('userId') ?? 0);

        if (! $this->sellerService->isActiveCreator($userId)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Fitur submit template hanya untuk Creator aktif.',
                    'redirect' => site_url('creator/apply'),
                ]);
        }

        $db = Database::connect();
        if (! $db->tableExists('templates')) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Tabel templates belum tersedia.',
                ]);
        }

        $requiredFields = ['owner_user_id', 'created_by_role', 'review_status', 'public_status', 'submitted_at'];
        $templateFields = $db->getFieldNames('templates');
        if (array_diff($requiredFields, $templateFields) !== []) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Modul seller template belum siap. Jalankan database/alter_seller_template_flow.sql terlebih dahulu.',
                ]);
        }

        $payload = $this->request->getPost();
        $name = trim((string) ($payload['name'] ?? ''));
        $slug = $this->slugFromValue((string) ($payload['slug'] ?? $name));
        $categoryId = (int) ($payload['category_id'] ?? 0);
        $projectType = $this->normalizeProjectType((string) ($payload['project_type'] ?? 'invitation'));

        if ($name === '' || mb_strlen($name) < 3) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Nama template minimal 3 karakter.',
            ]);
        }

        if ($slug === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Slug template tidak valid.',
            ]);
        }

        $templateModel = new TemplateModel();
        if ($templateModel->slugExists($slug)) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Slug template sudah dipakai. Pilih slug lain.',
            ]);
        }

        if ($categoryId <= 0 && $projectType === 'invitation' && $db->tableExists('categories')) {
            $category = (new CategoryModel())
                ->orderBy('sort_order', 'ASC')
                ->orderBy('name', 'ASC')
                ->first();
            $categoryId = (int) ($category['id'] ?? 0);
        }

        $thumbnail = $this->uploadThumbnail();
        if ($thumbnail === '' || $thumbnail === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'success' => false,
                'message' => $thumbnail === ''
                    ? 'Thumbnail / cover wajib diisi.'
                    : 'Thumbnail gagal diupload. Gunakan JPG, PNG, atau WEBP maksimal 2MB.',
            ]);
        }

        $editorJson = (string) ($payload['editor_json'] ?? '');
        $now = date('Y-m-d H:i:s');
        $data = [
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'project_type' => $projectType,
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string) ($payload['description'] ?? 'Template member seller')),
            'tags' => trim((string) ($payload['tags'] ?? '')),
            'thumbnail' => $thumbnail,
            'html' => (string) ($payload['html'] ?? ''),
            'css' => (string) ($payload['css'] ?? ''),
            'js' => (string) ($payload['js'] ?? ''),
            'editor_json' => $editorJson,
            'editor_type' => 'fabric',
            'grapesjs_json' => $editorJson,
            'is_premium' => (int) ($payload['is_premium'] ?? 0),
            'status' => 'active',
            'is_active' => 1,
            'owner_user_id' => $userId,
            'created_by_role' => 'creator',
            'seller_plan_name' => 'CREATOR',
            'review_status' => 'pending',
            'public_status' => 'private',
            'submitted_at' => $now,
            'source_invitation_id' => (int) ($payload['source_invitation_id'] ?? 0) ?: null,
            'usage_count' => 0,
            'publish_count' => 0,
        ];

        $templateId = $templateModel->insert($this->filterTemplateColumns($data), true);
        if (! $templateId) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Template gagal dikirim untuk review.',
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'success' => true,
            'message' => 'Template berhasil dikirim untuk direview admin. Template akan tampil publik setelah disetujui.',
            'template_id' => $templateId,
            'seller_dashboard_url' => site_url('creator/templates'),
        ]);
    }

    public function updateFromEditor(): ResponseInterface
    {
        $userId = (int) (session()->get('userId') ?? 0);

        if (! $this->sellerService->isActiveCreator($userId)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Update template hanya untuk Creator aktif.',
                    'redirect' => site_url('creator/apply'),
                ]);
        }

        $db = Database::connect();
        if (! $db->tableExists('templates')) {
            return $this->response->setStatusCode(503)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Tabel templates belum tersedia.',
            ]);
        }

        $templateFields = $db->getFieldNames('templates');
        foreach (['owner_user_id', 'review_status', 'public_status', 'submitted_at'] as $field) {
            if (! in_array($field, $templateFields, true)) {
                return $this->response->setStatusCode(503)->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Modul seller template belum siap. Jalankan database/alter_seller_template_flow.sql terlebih dahulu.',
                ]);
            }
        }

        $templateId = (int) ($this->request->getPost('template_id') ?? 0);
        if ($templateId <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Pilih template yang ingin diupdate.',
            ]);
        }

        $templateModel = new TemplateModel();
        $template = $templateModel
            ->where('owner_user_id', $userId)
            ->whereIn('review_status', ['pending', 'rejected'])
            ->find($templateId);

        if ($template === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Template tidak ditemukan, bukan milik kamu, atau sudah approved.',
            ]);
        }

        $thumbnail = $this->uploadThumbnail();
        if ($thumbnail === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Thumbnail gagal diupload. Gunakan JPG, PNG, atau WEBP maksimal 2MB.',
            ]);
        }

        $editorJson = (string) ($this->request->getPost('editor_json') ?? '');
        $now = date('Y-m-d H:i:s');
        $data = [
            'html' => (string) ($this->request->getPost('html') ?? ''),
            'css' => (string) ($this->request->getPost('css') ?? ''),
            'js' => (string) ($this->request->getPost('js') ?? ''),
            'editor_json' => $editorJson,
            'editor_type' => 'fabric',
            'grapesjs_json' => $editorJson,
            'review_status' => 'pending',
            'public_status' => 'private',
            'submitted_at' => $now,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
            'updated_at' => $now,
        ];

        if ($thumbnail !== '') {
            $data['thumbnail'] = $thumbnail;
        }

        $templateModel->update($templateId, $this->filterTemplateColumns($data));

        return $this->response->setJSON([
            'status' => true,
            'success' => true,
            'message' => 'Template berhasil diupdate dan dikirim ulang untuk review admin.',
            'template_id' => $templateId,
            'seller_dashboard_url' => site_url('creator/templates'),
        ]);
    }

    public function dashboard(): string
    {
        $this->ensureSellerAccess();
        $userId = (int) session()->get('userId');
        $this->ensureSellerCrmTables();

        return view('seller/dashboard', [
            'userName' => session()->get('userName'),
            'sellerStats' => $this->sellerLeadStats($userId),
            'recentLeads' => array_slice((new SellerLeadModel())->forSeller($userId), 0, 5),
        ]);
    }

    public function leads(): string
    {
        $this->ensureSellerAccess();
        $this->ensureSellerCrmTables();
        $userId = (int) session()->get('userId');
        $leads = (new SellerLeadModel())->forSeller($userId);

        return view('seller/leads', [
            'leads' => $leads,
            'statuses' => SellerLeadModel::STATUSES,
            'stats' => $this->sellerLeadStats($userId),
        ]);
    }

    public function storeLead(): RedirectResponse
    {
        $this->ensureSellerAccess();
        $this->ensureSellerCrmTables();

        $data = $this->leadPayload();
        if ($data['customer_name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Nama customer wajib diisi.');
        }

        (new SellerLeadModel())->insert($data + [
            'user_id' => (int) session()->get('userId'),
            'status' => 'new',
        ]);

        return redirect()->to(site_url('seller/leads'))->with('success', 'Lead baru berhasil disimpan.');
    }

    public function leadDetail(int $id): string
    {
        $this->ensureSellerAccess();
        $this->ensureSellerCrmTables();

        $lead = (new SellerLeadModel())->findForSeller($id, (int) session()->get('userId'));
        if ($lead === null) {
            throw PageNotFoundException::forPageNotFound('Lead tidak ditemukan.');
        }

        return view('seller/lead_detail', [
            'lead' => $lead,
            'statuses' => SellerLeadModel::STATUSES,
            'whatsappTemplates' => $this->sellerWhatsappTemplateList($lead),
        ]);
    }

    public function updateLead(int $id): RedirectResponse
    {
        $this->ensureSellerAccess();
        $this->ensureSellerCrmTables();

        $leadModel = new SellerLeadModel();
        $lead = $leadModel->findForSeller($id, (int) session()->get('userId'));
        if ($lead === null) {
            throw PageNotFoundException::forPageNotFound('Lead tidak ditemukan.');
        }

        $data = $this->leadPayload();
        $status = (string) $this->request->getPost('status');
        if (array_key_exists($status, SellerLeadModel::STATUSES)) {
            $data['status'] = $status;
        }

        if ((string) ($data['customer_name'] ?? '') === '') {
            return redirect()->back()->withInput()->with('error', 'Nama customer wajib diisi.');
        }

        if ((string) ($lead['status'] ?? '') !== ($data['status'] ?? $lead['status'])) {
            $data['last_follow_up_at'] = date('Y-m-d H:i:s');
        }

        $leadModel->update($id, $data);

        return redirect()->to(site_url('seller/leads/' . $id))->with('success', 'Lead berhasil diperbarui.');
    }

    public function whatsappTemplates(): string
    {
        $this->ensureSellerAccess();

        return view('seller/whatsapp_templates', [
            'templates' => $this->sellerWhatsappTemplateList(),
        ]);
    }

    public function promoAssets(): string
    {
        $this->ensureSellerAccess();

        return view('seller/promo_assets');
    }

    public function creatorDashboard(): string
    {
        $this->ensureCreatorAccess();
        $userId = (int) session()->get('userId');

        return view('seller/dashboard', [
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
            'summary' => $this->summary($userId),
            'balance' => $this->sellerService->walletBalance($userId),
            'templates' => $this->sellerTemplates($userId),
            'creatorStatus' => (new CreatorProfileModel())->statusForUser($userId),
            'isCreatorDashboard' => true,
        ]);
    }

    public function templates(): string
    {
        $this->ensureCreatorAccess();
        $userId = (int) session()->get('userId');

        return view('seller/templates', [
            'templates' => $this->sellerTemplates($userId),
        ]);
    }

    public function templateDetail(int $id): string
    {
        $this->ensureCreatorAccess();
        $template = $this->ownedTemplate($id);
        $ledger = (new SellerWalletLedgerModel())
            ->where('user_id', (int) session()->get('userId'))
            ->where('template_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll(50);

        return view('seller/template_detail', [
            'template' => $template,
            'ledger' => $ledger,
        ]);
    }

    public function resubmit(int $id): RedirectResponse
    {
        $template = $this->ownedTemplate($id);
        if (! in_array((string) ($template['review_status'] ?? ''), ['rejected', 'pending'], true)) {
            return redirect()->back()->with('error', 'Template ini belum bisa dikirim ulang.');
        }

        (new TemplateModel())->update($id, $this->filterTemplateColumns([
            'review_status' => 'pending',
            'public_status' => 'private',
            'submitted_at' => date('Y-m-d H:i:s'),
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ]));

        return redirect()->to(site_url('creator/templates'))->with('success', 'Template dikirim ulang untuk review.');
    }

    public function archive(int $id): RedirectResponse
    {
        $this->ownedTemplate($id);
        (new TemplateModel())->update($id, $this->filterTemplateColumns([
            'public_status' => 'archived',
        ]));

        return redirect()->to(site_url('creator/templates'))->with('success', 'Template diarsipkan.');
    }

    public function earnings(): string
    {
        $this->ensureCreatorAccess();
        $userId = (int) session()->get('userId');
        $royaltyService = new CreatorRoyaltyService();

        return view('seller/earnings', [
            'balance' => $this->sellerService->walletBalance($userId),
            'royaltyReady' => $royaltyService->tableReady(),
            'royaltySummary' => $royaltyService->creatorSummary($userId),
            'royalties' => $royaltyService->recentRoyaltiesForCreator($userId, 80),
            'ledger' => (new SellerWalletLedgerModel())
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->findAll(100),
            'withdraws' => (new SellerWithdrawRequestModel())
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->findAll(50),
            'limits' => $this->sellerService->planLimits(['plan_slug' => 'creator']),
            'isCreatorDashboard' => true,
        ]);
    }

    public function storeWithdrawRequest(): RedirectResponse
    {
        $this->ensureCreatorAccess();
        $userId = (int) session()->get('userId');

        $rules = [
            'amount' => 'required|is_natural_no_zero',
            'bank_name' => 'required|min_length[2]|max_length[120]',
            'account_number' => 'required|min_length[5]|max_length[80]',
            'account_holder_name' => 'required|min_length[3]|max_length[160]',
            'account_password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = (new UserModel())->find($userId);
        $password = (string) ($this->request->getPost('account_password') ?? '');
        if ($user === null || ! password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Password tidak sesuai. Request withdraw dibatalkan.');
        }

        try {
            $this->sellerService->createWithdrawRequest($userId, $this->request->getPost());
        } catch (\Throwable $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('creator/earnings'))->with('success', 'Request withdraw berhasil dibuat dan menunggu admin.');
    }

    private function ensureSellerAccess(): void
    {
        $userId = (int) session()->get('userId');
        if (! $this->sellerService->canAccessSellerDashboard($userId)) {
            throw PageNotFoundException::forPageNotFound('Dashboard penjual tidak tersedia.');
        }
    }

    private function leadPayload(): array
    {
        $eventDate = trim((string) $this->request->getPost('event_date'));
        $budget = preg_replace('/\D+/', '', (string) $this->request->getPost('budget'));

        return [
            'customer_name' => trim((string) $this->request->getPost('customer_name')),
            'whatsapp' => trim((string) $this->request->getPost('whatsapp')),
            'event_type' => trim((string) $this->request->getPost('event_type')),
            'event_date' => $eventDate !== '' ? $eventDate : null,
            'package_name' => trim((string) $this->request->getPost('package_name')),
            'budget' => (int) ($budget ?: 0),
            'source' => trim((string) $this->request->getPost('source')),
            'notes' => trim((string) $this->request->getPost('notes')),
        ];
    }

    private function sellerLeadStats(int $userId): array
    {
        $leads = (new SellerLeadModel())->forSeller($userId);
        $stats = [
            'total' => count($leads),
            'new' => 0,
            'deal' => 0,
            'production' => 0,
            'done' => 0,
            'estimated_revenue' => 0,
            'pipeline' => array_fill_keys(array_keys(SellerLeadModel::STATUSES), 0),
        ];

        foreach ($leads as $lead) {
            $status = (string) ($lead['status'] ?? 'new');
            if (isset($stats['pipeline'][$status])) {
                $stats['pipeline'][$status]++;
            }
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
            if (in_array($status, ['deal', 'production', 'done'], true)) {
                $stats['estimated_revenue'] += (int) ($lead['budget'] ?? 0);
            }
        }

        return $stats;
    }

    private function sellerWhatsappTemplateList(?array $lead = null): array
    {
        $name = (string) ($lead['customer_name'] ?? '{nama_customer}');
        $event = (string) ($lead['event_type'] ?? '{jenis_acara}');
        $date = (string) ($lead['event_date'] ?? '{tanggal_acara}');
        $package = (string) ($lead['package_name'] ?? '{paket}');

        return [
            'follow_up' => [
                'title' => 'Follow Up Pertama',
                'body' => "Halo {$name}, saya dari AdaAcara. Untuk kebutuhan undangan digital {$event}, saya bisa bantu siapkan preview dan pilihan paket yang cocok. Apakah boleh saya kirim contoh templatenya?",
            ],
            'offer' => [
                'title' => 'Penawaran Paket',
                'body' => "Halo {$name}, untuk acara {$event} tanggal {$date}, saya rekomendasikan paket {$package}. Paket ini sudah termasuk setup undangan, revisi ringan, dan link siap dibagikan.",
            ],
            'payment' => [
                'title' => 'Reminder Pembayaran',
                'body' => "Halo {$name}, desain undangan bisa mulai saya proses setelah pembayaran/deposit diterima. Setelah itu saya kirim preview untuk dicek sebelum publish.",
            ],
            'preview' => [
                'title' => 'Kirim Preview',
                'body' => "Halo {$name}, ini preview awal undangannya. Silakan dicek nama, tanggal, lokasi, dan detail acara. Kalau ada revisi, kirimkan catatannya ya.",
            ],
        ];
    }

    private function ensureSellerCrmTables(): void
    {
        $db = Database::connect();
        if ($db->tableExists('seller_leads')) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'customer_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'whatsapp' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'event_date' => ['type' => 'DATE', 'null' => true],
            'package_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'budget' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'new'],
            'source' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'last_follow_up_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->addKey('user_id');
        $forge->addKey('status');
        $forge->createTable('seller_leads', true);
    }

    private function ensureCreatorAccess(): void
    {
        $userId = (int) session()->get('userId');
        if (! $this->sellerService->canAccessCreatorDashboard($userId)) {
            throw PageNotFoundException::forPageNotFound('Dashboard creator tidak tersedia.');
        }
    }

    private function ownedTemplate(int $id): array
    {
        $template = (new TemplateModel())->where('owner_user_id', (int) session()->get('userId'))->find($id);
        if ($template === null) {
            throw PageNotFoundException::forPageNotFound('Template penjual tidak ditemukan.');
        }

        return $template;
    }

    private function sellerTemplates(int $userId): array
    {
        return (new TemplateModel())
            ->where('owner_user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    private function summary(int $userId): array
    {
        $templates = $this->sellerTemplates($userId);
        $summary = [
            'total' => count($templates),
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'usage_count' => 0,
            'publish_count' => 0,
        ];

        foreach ($templates as $template) {
            $review = (string) ($template['review_status'] ?? '');
            if (isset($summary[$review])) {
                $summary[$review]++;
            }
            $summary['usage_count'] += (int) ($template['usage_count'] ?? 0);
            $summary['publish_count'] += (int) ($template['publish_count'] ?? 0);
        }

        return $summary;
    }

    private function uploadThumbnail(): string|false
    {
        $file = $this->request->getFile('thumbnail');
        if ($file === null || ! $file->isValid()) {
            return '';
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return false;
        }

        $mime = $file->getMimeType();
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return false;
        }

        $directory = FCPATH . 'uploads/templates';
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $name = $file->getRandomName();
        $file->move($directory, $name);

        return 'uploads/templates/' . $name;
    }

    private function slugFromValue(string $value): string
    {
        helper('url');

        return url_title($value, '-', true);
    }

    private function normalizeProjectType(string $value): string
    {
        $value = strtolower(trim($value));

        return match ($value) {
            'photobooth', 'digital_photobooth' => 'photobooth',
            'business_profile', 'business-profile' => 'business_profile',
            default => 'invitation',
        };
    }

    private function filterTemplateColumns(array $data): array
    {
        $fields = Database::connect()->getFieldNames('templates');

        return array_intersect_key($data, array_flip($fields));
    }
}
