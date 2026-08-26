<?php

namespace App\Controllers;

use App\Libraries\MarketplaceReviewService;
use App\Models\CategoryModel;
use App\Models\CreatorProfileModel;
use App\Models\CreatorTemplateOwnershipModel;
use App\Models\MarketplaceTemplateActivityLogModel;
use App\Models\MarketplaceTemplateModel;
use App\Models\MarketplaceTemplateReviewModel;
use App\Models\NotificationModel;
use App\Models\TemplateModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class CreatorMarketplaceController extends BaseController
{
    public function index(): string|RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        return view('creator/marketplace_templates/index', [
            'creator' => $creator,
            'ownedTemplates' => (new CreatorTemplateOwnershipModel())->templatesForCreator((int) $creator['id']),
            'marketplaceTemplates' => (new MarketplaceTemplateModel())->creatorList((int) $creator['id']),
            'summary' => (new MarketplaceTemplateModel())->creatorSummary((int) $creator['id']),
            'notifications' => (new NotificationModel())->latestForUser((int) session()->get('userId'), 5),
            'profileCompletion' => $this->profileCompletion($creator),
        ]);
    }

    public function summary(): ResponseInterface|RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        return $this->response->setJSON([
            'success' => true,
            'summary' => (new MarketplaceTemplateModel())->creatorSummary((int) $creator['id']),
        ]);
    }

    public function store(): RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        $templateId = (int) $this->request->getPost('template_id');
        if ($templateId <= 0 || ! (new CreatorTemplateOwnershipModel())->creatorOwnsTemplate((int) $creator['id'], $templateId)) {
            return redirect()->back()->with('error', 'Template tidak ditemukan atau bukan milik creator ini.');
        }

        $marketplaceModel = new MarketplaceTemplateModel();
        $existing = $marketplaceModel->where('template_id', $templateId)->first();
        if ($existing !== null) {
            return redirect()->to('/creator/marketplace-templates/' . $existing['id'])
                ->with('error', 'Metadata marketplace untuk template ini sudah ada.');
        }

        $template = (new TemplateModel())->find($templateId);
        if ($template === null) {
            return redirect()->back()->with('error', 'Template tidak ditemukan.');
        }

        $title = (string) ($template['name'] ?? 'Template Creator');
        $created = $marketplaceModel->insert([
            'template_id' => $templateId,
            'creator_id' => (int) $creator['id'],
            'title' => $title,
            'slug' => $this->uniqueMarketplaceSlug($title),
            'short_description' => null,
            'description' => $this->cleanText((string) ($template['description'] ?? ''), 2000),
            'category' => $this->categoryName((int) ($template['category_id'] ?? 0)),
            'thumbnail_url' => (string) ($template['thumbnail'] ?? ''),
            'preview_url' => site_url('templates/preview/' . $templateId),
            'is_free' => 1,
            'price_amount' => 0,
            'price_currency' => 'IDR',
            'license_type' => 'single_use',
            'marketplace_status' => 'draft',
            'approval_status' => 'not_submitted',
        ], true);

        if (! $created) {
            return redirect()->back()->with('error', 'Draft metadata marketplace gagal dibuat.');
        }

        return redirect()->to('/creator/marketplace-templates/' . $created)
            ->with('success', 'Draft metadata marketplace berhasil dibuat.');
    }

    public function show(int $id): string|RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        $marketplace = (new MarketplaceTemplateModel())->findForCreator($id, (int) $creator['id']);
        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Metadata marketplace tidak ditemukan.');
        }

        return view('creator/marketplace_templates/form', [
            'creator' => $creator,
            'marketplace' => $marketplace,
            'categories' => (new CategoryModel())->templateOptions(),
            'licenseTypes' => MarketplaceTemplateModel::LICENSE_TYPES,
            'reviews' => (new MarketplaceTemplateReviewModel())->historyForTemplate($id),
            'activityLogs' => (new MarketplaceTemplateActivityLogModel())->historyForTemplate($id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        $marketplaceModel = new MarketplaceTemplateModel();
        $marketplace = $marketplaceModel->findForCreator($id, (int) $creator['id']);
        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Metadata marketplace tidak ditemukan.');
        }

        if (! in_array((string) $marketplace['marketplace_status'], ['draft', 'rejected', 'changes_requested'], true)) {
            return redirect()->back()->with('error', 'Template yang sudah submitted atau approved tidak bisa diedit langsung.');
        }

        $payload = $this->validatedMetadataPayload($id, false);
        if (isset($payload['error'])) {
            return redirect()->back()->withInput()->with('error', $payload['error']);
        }

        $marketplaceModel->update($id, $payload);

        return redirect()->to('/creator/marketplace-templates/' . $id)->with('success', 'Draft metadata berhasil disimpan.');
    }

    public function submit(int $id): RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        $marketplaceModel = new MarketplaceTemplateModel();
        $marketplace = $marketplaceModel->findForCreator($id, (int) $creator['id']);
        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Metadata marketplace tidak ditemukan.');
        }

        if (! in_array((string) $marketplace['marketplace_status'], ['draft', 'rejected', 'changes_requested'], true)) {
            return redirect()->back()->with('error', 'Hanya draft atau rejected yang bisa disubmit ulang.');
        }

        $payload = $this->validatedMetadataPayload($id, true);
        if (isset($payload['error'])) {
            return redirect()->back()->withInput()->with('error', $payload['error']);
        }

        $freshMarketplace = $marketplaceModel->findForCreator($id, (int) $creator['id']);
        if ($freshMarketplace === null || ! (new MarketplaceReviewService())->submit($freshMarketplace, (int) session()->get('userId'), (string) $this->request->getPost('creator_message'))) {
            return redirect()->back()->with('error', 'Submit marketplace gagal diproses.');
        }

        return redirect()->to('/creator/marketplace-templates/' . $id)->with('success', 'Template berhasil disubmit untuk review admin.');
    }

    public function archive(int $id): RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        $marketplaceModel = new MarketplaceTemplateModel();
        $marketplace = $marketplaceModel->findForCreator($id, (int) $creator['id']);
        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Metadata marketplace tidak ditemukan.');
        }

        if (! in_array((string) $marketplace['marketplace_status'], ['draft', 'rejected', 'changes_requested'], true)) {
            return redirect()->back()->with('error', 'Creator hanya bisa archive template draft atau rejected.');
        }

        if (! (new MarketplaceReviewService())->archiveByCreator($marketplace, (int) session()->get('userId'))) {
            return redirect()->back()->with('error', 'Archive template marketplace gagal diproses.');
        }

        return redirect()->to('/creator/marketplace-templates')->with('success', 'Template marketplace berhasil diarchive.');
    }

    public function withdraw(int $id): RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        $marketplace = (new MarketplaceTemplateModel())->findForCreator($id, (int) $creator['id']);
        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Metadata marketplace tidak ditemukan.');
        }

        if (! (new MarketplaceReviewService())->withdraw($marketplace, (int) session()->get('userId'), (string) $this->request->getPost('creator_message'))) {
            return redirect()->back()->with('error', 'Withdraw hanya bisa dilakukan saat template submitted dan belum direview.');
        }

        return redirect()->to('/creator/marketplace-templates/' . $id)->with('success', 'Submission berhasil ditarik kembali ke draft.');
    }

    public function restore(int $id): RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        $marketplace = (new MarketplaceTemplateModel())->findForCreator($id, (int) $creator['id']);
        if ($marketplace === null) {
            throw PageNotFoundException::forPageNotFound('Metadata marketplace tidak ditemukan.');
        }

        if (! (new MarketplaceReviewService())->restoreByCreator($marketplace, (int) session()->get('userId'))) {
            return redirect()->back()->with('error', 'Restore hanya bisa dilakukan dari archived.');
        }

        return redirect()->to('/creator/marketplace-templates/' . $id)->with('success', 'Template berhasil dikembalikan ke draft.');
    }

    public function feedback(): string|RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        return view('creator/marketplace_templates/feedback', [
            'creator' => $creator,
            'templates' => (new MarketplaceTemplateModel())
                ->where('creator_id', (int) $creator['id'])
                ->whereIn('marketplace_status', ['rejected', 'changes_requested'])
                ->orderBy('updated_at', 'DESC')
                ->findAll(),
        ]);
    }

    public function notifications(): string|RedirectResponse
    {
        $creator = $this->activeCreatorProfile();
        if ($creator === null) {
            return redirect()->to('/creator/apply')->with('error', 'Akses creator hanya tersedia setelah approval admin.');
        }

        return view('creator/marketplace_templates/notifications', [
            'creator' => $creator,
            'notifications' => (new NotificationModel())->latestForUser((int) session()->get('userId'), 50),
        ]);
    }

    public function readNotification(int $id): RedirectResponse
    {
        $notificationModel = new NotificationModel();
        $notification = $notificationModel
            ->where('id', $id)
            ->where('user_id', (int) session()->get('userId'))
            ->first();

        if ($notification === null) {
            throw PageNotFoundException::forPageNotFound('Notifikasi tidak ditemukan.');
        }

        $notificationModel->update($id, ['read_at' => date('Y-m-d H:i:s')]);

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    private function activeCreatorProfile(): ?array
    {
        return (new CreatorProfileModel())->activeForUser((int) session()->get('userId'));
    }

    private function profileCompletion(array $creator): array
    {
        $checks = [
            'display_name' => trim((string) ($creator['display_name'] ?? '')) !== '',
            'bio' => mb_strlen(trim((string) ($creator['bio'] ?? ''))) >= 20,
            'avatar_url' => trim((string) ($creator['avatar_url'] ?? '')) !== '',
            'portfolio_url' => trim((string) ($creator['portfolio_url'] ?? '')) !== '',
            'social_links' => trim((string) ($creator['social_links'] ?? '')) !== '',
            'template_activity' => (new MarketplaceTemplateModel())->where('creator_id', (int) $creator['id'])->whereIn('marketplace_status', ['submitted', 'approved'])->countAllResults() > 0,
        ];
        $done = count(array_filter($checks));

        return [
            'checks' => $checks,
            'percent' => (int) round(($done / count($checks)) * 100),
        ];
    }

    private function validatedMetadataPayload(?int $ignoreId, bool $forSubmit): array
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[120]',
            'short_description' => 'permit_empty|max_length[180]',
            'description' => 'permit_empty|max_length[2000]',
            'category' => $forSubmit ? 'required|max_length[120]' : 'permit_empty|max_length[120]',
            'tags' => 'permit_empty|max_length[1000]',
            'thumbnail_url' => $forSubmit ? 'required|max_length[500]' : 'permit_empty|max_length[500]',
            'preview_url' => 'permit_empty|max_length[500]',
            'is_free' => 'permit_empty|in_list[0,1]',
            'price_amount' => 'permit_empty|is_natural',
            'price_currency' => 'permit_empty|max_length[10]',
            'license_type' => 'required|in_list[' . implode(',', MarketplaceTemplateModel::LICENSE_TYPES) . ']',
        ];

        if (! $this->validate($rules)) {
            return ['error' => implode(' ', $this->validator->getErrors())];
        }

        $title = trim((string) $this->request->getPost('title'));
        $slug = $this->uniqueMarketplaceSlug($title, $ignoreId);
        $isFree = (string) $this->request->getPost('is_free') === '0' ? 0 : 1;
        $priceAmount = max(0, (int) $this->request->getPost('price_amount'));

        if ($isFree === 1) {
            $priceAmount = 0;
        } elseif ($priceAmount < 1000) {
            return ['error' => 'Template berbayar minimal Rp 1.000.'];
        }

        $tags = $this->normalizeTags((string) $this->request->getPost('tags'));
        if ($tags === false) {
            return ['error' => 'Tags maksimal 10 item, masing-masing maksimal 30 karakter.'];
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'short_description' => $this->cleanText((string) $this->request->getPost('short_description'), 180),
            'description' => $this->cleanText((string) $this->request->getPost('description'), 2000),
            'category' => trim((string) $this->request->getPost('category')),
            'tags' => $tags,
            'thumbnail_url' => trim((string) $this->request->getPost('thumbnail_url')),
            'preview_url' => trim((string) $this->request->getPost('preview_url')),
            'is_free' => $isFree,
            'price_amount' => $priceAmount,
            'price_currency' => strtoupper(trim((string) ($this->request->getPost('price_currency') ?: 'IDR'))),
            'license_type' => (string) $this->request->getPost('license_type'),
        ];
    }

    private function normalizeTags(string $raw): string|null|false
    {
        $items = array_filter(array_map('trim', explode(',', $raw)), static fn (string $item): bool => $item !== '');
        $items = array_values(array_unique($items));

        if (count($items) > 10) {
            return false;
        }

        foreach ($items as $item) {
            if (mb_strlen($item) > 30) {
                return false;
            }
        }

        return $items === [] ? null : json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function cleanText(string $value, int $maxLength): string
    {
        return mb_substr(trim(strip_tags($value)), 0, $maxLength);
    }

    private function categoryName(int $categoryId): ?string
    {
        if ($categoryId <= 0) {
            return null;
        }

        $category = (new CategoryModel())->find($categoryId);

        return $category['name'] ?? null;
    }

    private function uniqueMarketplaceSlug(string $title, ?int $ignoreId = null): string
    {
        helper('url');

        $base = url_title($title, '-', true) ?: 'template';
        $base = substr($base, 0, 110);
        $slug = $base;
        $suffix = 2;
        $marketplaceModel = new MarketplaceTemplateModel();

        while ($marketplaceModel->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
