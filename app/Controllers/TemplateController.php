<?php

namespace App\Controllers;

use App\Libraries\SellerTemplateService;
use App\Libraries\CreatorRoyaltyService;
use App\Models\LandingPageModel;
use App\Models\TemplateModel;
use App\Models\TemplateSubcategoryModel;
use App\Models\TemplateWishlistModel;
use App\Models\UserSubscriptionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class TemplateController extends BaseController
{
    public function index(): string
    {
        $templateModel = new TemplateModel();
        $searchQuery = trim((string) ($this->request->getGet('q') ?? ''));
        $listingSearchQuery = $searchQuery;
        $rawProductType = strtolower(trim((string) ($this->request->getGet('type') ?? '')));
        $listingProjectType = match ($rawProductType) {
            'photobooth' => 'photobooth',
            'business-profile' => 'business_profile',
            default => 'invitation',
        };
        $subcategorySlug = strtolower(trim((string) ($this->request->getGet('subcategory') ?? '')));
        $selectedSubcategory = null;
        $selectedSubcategoryId = null;
        $templateSubcategoryGroups = [];
        $subcategoryModel = null;

        try {
            $subcategoryModel = new TemplateSubcategoryModel();
            $templateSubcategoryGroups = $subcategoryModel->activeGroupedByCategorySlugWithTemplates('invitation');
        } catch (\Throwable) {
            $subcategoryModel = null;
            $templateSubcategoryGroups = [];
        }

        if ($subcategorySlug !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $subcategorySlug)) {
            $subcategoryModel ??= new TemplateSubcategoryModel();
            $selectedSubcategory = $subcategoryModel->findActiveBySlug($subcategorySlug);
            if ($selectedSubcategory !== null) {
                $selectedSubcategoryId = (int) ($selectedSubcategory['id'] ?? 0);
                if (! $subcategoryModel->assignmentTableReady()) {
                    $subcategoryKeywords = trim((string) ($selectedSubcategory['search_keywords'] ?? ''));
                    if ($subcategoryKeywords === '') {
                        $subcategoryKeywords = trim((string) ($selectedSubcategory['name'] ?? ''));
                    }
                    $listingSearchQuery = trim($listingSearchQuery . ' ' . $subcategoryKeywords);
                }
            }
        }

        if (mb_strlen($searchQuery) > 80) {
            $searchQuery = mb_substr($searchQuery, 0, 80);
        }
        if (mb_strlen($listingSearchQuery) > 160) {
            $listingSearchQuery = mb_substr($listingSearchQuery, 0, 160);
        }
        $displaySearchQuery = $selectedSubcategory !== null && $searchQuery === ''
            ? (string) ($selectedSubcategory['name'] ?? '')
            : $searchQuery;
        $currentUserId = (int) (session()->get('userId') ?? 0);
        $role = strtolower((string) (session()->get('userRole') ?? ''));
        $hasActiveMembership = $currentUserId > 0 && ($this->getActiveSubscription($currentUserId) !== null || $this->currentUserIsAdmin() || $role === 'creator');
        $wishlistTemplateIds = [];
        if ($currentUserId > 0) {
            $wishlistTemplateIds = (new TemplateWishlistModel())->templateIdsForUser($currentUserId);
        }

        return view('templates/index', [
            'title' => $listingSearchQuery !== '' ? 'Search Template - Ada Acara' : 'Pilih Template - Ada Acara',
            'templates' => $templateModel->getTemplateListingCards($listingSearchQuery, $selectedSubcategoryId, true, $listingProjectType),
            'searchQuery' => $displaySearchQuery,
            'selectedSubcategory' => $selectedSubcategory,
            'templateSubcategoryGroups' => $templateSubcategoryGroups,
            'hasActiveMembership' => $hasActiveMembership,
            'wishlistTemplateIds' => $wishlistTemplateIds,
        ]);
    }

    public function show(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw PageNotFoundException::forPageNotFound('Template tidak ditemukan.');
        }

        $template = (new TemplateModel())->getActiveTemplateBySlug($slug);
        if ($template === null) {
            throw PageNotFoundException::forPageNotFound('Template tidak ditemukan.');
        }

        $currentUserId = (int) (session()->get('userId') ?? 0);
        $role = strtolower((string) (session()->get('userRole') ?? ''));
        $hasActiveMembership = $currentUserId > 0 && ($this->getActiveSubscription($currentUserId) !== null || $this->currentUserIsAdmin() || $role === 'creator');
        $isPremiumTemplate = (int) ($template['is_premium'] ?? 0) === 1;

        return view('templates/show', [
            'template' => $template,
            'isLoggedIn' => session()->has('userId'),
            'isPremiumTemplate' => $isPremiumTemplate,
            'hasActiveMembership' => $hasActiveMembership,
            'canUseTemplate' => ! $isPremiumTemplate || $hasActiveMembership,
            'plansUrl' => site_url('plans'),
            'loginUrl' => site_url('login') . '?redirect=' . rawurlencode(site_url('templates/' . $slug)),
        ]);
    }

    public function preview(int $id): string
    {
        $template = (new TemplateModel())->getActiveTemplate($id);

        if ($template === null) {
            $template = $this->reviewableTemplate($id);
            if ($template === null) {
                throw PageNotFoundException::forPageNotFound('Template tidak ditemukan.');
            }
        }

        $currentUserId = (int) (session()->get('userId') ?? 0);
        $hasActiveMembership = $currentUserId > 0 && ($this->getActiveSubscription($currentUserId) !== null || $this->currentUserIsAdmin());
        $isPremiumTemplate = (int) ($template['is_premium'] ?? 0) === 1;
        $canUseTemplate = ! $isPremiumTemplate || $hasActiveMembership;
        $categoryName = 'Lainnya';
        $db = db_connect();
        if ($db->tableExists('categories') && ! empty($template['category_id'])) {
            $category = $db->table('categories')
                ->select('name')
                ->where('id', (int) $template['category_id'])
                ->get()
                ->getRowArray();
            $categoryName = (string) ($category['name'] ?? $categoryName);
        }

        $previewPage = [
            'id' => 0,
            'title' => (string) ($template['name'] ?? 'Preview Template'),
            'slug' => 'template-preview-' . $id,
            'html' => (string) ($template['html'] ?? ''),
            'css' => (string) ($template['css'] ?? ''),
            'js' => (string) ($template['js'] ?? ''),
            'editor_json' => (string) ($template['editor_json'] ?? $template['grapesjs_json'] ?? ''),
            'grapesjs_json' => (string) ($template['grapesjs_json'] ?? $template['editor_json'] ?? ''),
            'status' => 'preview',
            'published_html' => null,
            'published_css' => null,
            'published_js' => null,
            'published_editor_json' => null,
        ];

        $previewDocument = view('public/render', [
            'isPreview' => true,
            'guestbookEntries' => [],
            'page' => $previewPage,
        ], ['saveData' => true]);

        $useTemplateUrl = site_url('templates/create');

        return view('templates/preview', [
            'template' => $template,
            'categoryName' => $categoryName,
            'previewDocument' => $previewDocument,
            'useTemplateUrl' => $useTemplateUrl,
            'isLoggedIn' => session()->has('userId'),
            'isPremiumTemplate' => $isPremiumTemplate,
            'hasActiveMembership' => $hasActiveMembership,
            'canUseTemplate' => $canUseTemplate,
            'plansUrl' => site_url('plans'),
            'loginUrl' => site_url('login') . '?redirect=' . rawurlencode(site_url('templates/preview/' . $id)),
        ]);
    }

    private function reviewableTemplate(int $id): ?array
    {
        $template = (new TemplateModel())->find($id);
        if ($template === null) {
            return null;
        }

        $currentUserId = (int) (session()->get('userId') ?? 0);
        $isAdmin = $this->currentUserIsAdmin();
        $isOwner = $currentUserId > 0 && (int) ($template['owner_user_id'] ?? 0) === $currentUserId;

        return ($isAdmin || $isOwner) ? $template : null;
    }

    public function store(): RedirectResponse
    {
        $userId = (int) (session()->get('userId') ?? 0);
        if ($userId <= 0) {
            return redirect()
                ->to(site_url('login'))
                ->with('error', 'Silakan login terlebih dahulu untuk memakai template.');
        }
        $landingPageModel = new LandingPageModel();

        $isBlankTemplate = (string) $this->request->getPost('blank_template') === '1';
        $projectIntent = strtolower(trim((string) ($this->request->getPost('project_intent') ?? '')));
        $isBusinessProfileIntent = in_array($projectIntent, ['business_profile', 'business-profile'], true);

        $rules = [
            'template_id' => $isBlankTemplate ? 'permit_empty' : 'required|is_natural_no_zero',
            'title' => 'required|min_length[3]|max_length[180]',
            'slug' => 'permit_empty|max_length[190]',
            'event_date' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if ($isBlankTemplate) {
            $template = $this->blankTemplate();
        } else {
            $templateModel = new TemplateModel();
            $template = $templateModel->getActiveTemplate((int) $this->request->getPost('template_id'));

            if ($template === null) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', ['template_id' => 'Template tidak ditemukan atau tidak aktif.']);
            }

            if (! $isBusinessProfileIntent && (int) ($template['is_premium'] ?? 0) === 1 && ! $this->canUsePremiumTemplate($userId)) {
                return redirect()->to(site_url('plans'))
                    ->with('error', 'Template premium membutuhkan paket aktif.');
            }
        }

        $projectType = 'invitation';
        if ($projectIntent === 'photobooth') {
            $template = $this->withPhotoboothProjectIntent($template);
            $projectType = 'photobooth';
        } elseif (in_array($projectIntent, ['business_profile', 'business-profile'], true)) {
            $template = $this->withBusinessProfileProjectIntent($template);
            $projectType = 'business_profile';
        } elseif (! $isBlankTemplate) {
            $projectType = $this->normalizeProjectType((string) ($template['project_type'] ?? ''));
        }

        helper('url');

        $title = trim((string) $this->request->getPost('title'));
        $slugInput = trim((string) ($this->request->getPost('slug') ?: $title));
        $slug = url_title($slugInput, '-', true);

        if ($slug === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['slug' => 'Slug tidak valid. Gunakan huruf atau angka.']);
        }

        if ($landingPageModel->slugExists($slug)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['slug' => 'Slug sudah digunakan. Pilih slug lain.']);
        }

        $landingPageId = $landingPageModel->createFromTemplate($userId, $template, [
            'title' => $title,
            'slug' => $slug,
            'event_date' => (string) $this->request->getPost('event_date'),
            'project_type' => $projectType,
        ]);

        if (! $landingPageId) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['landing_page' => 'Undangan gagal dibuat. Coba lagi.']);
        }

        if (! $isBlankTemplate) {
            (new SellerTemplateService())->createTemplateUsage((int) $landingPageId, $template, $userId);
            try {
                (new CreatorRoyaltyService())->recordTemplateUsed((int) $landingPageId, $template, $userId, [
                    'source' => 'templates_create',
                ]);
            } catch (\Throwable $error) {
                log_message('warning', 'Creator royalty template_used event skipped. invitation={invitation} template={template} user={user} error={error}', [
                    'invitation' => (string) $landingPageId,
                    'template' => (string) ($template['id'] ?? '-'),
                    'user' => (string) $userId,
                    'error' => $error->getMessage(),
                ]);
            }
        }

        return redirect()->to('/editor/' . $landingPageId)
            ->with('success', 'Undangan berhasil dibuat dari template. Silakan edit desainnya.');
    }

    public function toggleWishlist(): ResponseInterface
    {
        $userId = (int) (session()->get('userId') ?? 0);
        if ($userId <= 0) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu untuk menyimpan wishlist.',
                    'login_url' => site_url('login') . '?redirect=' . rawurlencode(site_url('templates')),
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $templateId = (int) ($this->request->getPost('template_id') ?? 0);
        if ($templateId <= 0 || (new TemplateModel())->getActiveTemplate($templateId) === null) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Template tidak ditemukan.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $wishlistModel = new TemplateWishlistModel();
        $liked = $wishlistModel->toggle($userId, $templateId);
        if ($liked === null) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Wishlist belum siap. Jalankan SQL template_wishlists terlebih dahulu.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'liked' => $liked,
            'message' => $liked ? 'Template disimpan ke wishlist.' : 'Template dihapus dari wishlist.',
            'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
        ]);
    }

    private function getActiveSubscription(int $userId): ?array
    {
        return (new UserSubscriptionModel())->activeWithPlanByUser($userId);
    }

    private function canUsePremiumTemplate(int $userId): bool
    {
        return $this->currentUserIsAdmin() || ($userId > 0 && $this->getActiveSubscription($userId) !== null);
    }

    private function currentUserIsAdmin(): bool
    {
        return in_array(strtolower((string) (session()->get('userRole') ?? '')), ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin'], true);
    }

    private function blankTemplate(): array
    {
        $editorJson = [
            'renderer' => 'fabric',
            'mode' => 'website-pages',
            'activePageIndex' => 0,
            'pages' => [
                [
                    'id' => 'blank-page-1',
                    'title' => 'Halaman 1',
                    'objects' => [],
                    'background' => '#ffffff',
                    'backgroundColor' => '#ffffff',
                    'artboard' => [
                        'width' => 1080,
                        'height' => 1920,
                    ],
                    'hidden' => false,
                    'renderer' => 'fabric-page',
                    'version' => '5.3.0',
                ],
            ],
            'guestbook' => [
                'enabled' => false,
            ],
        ];

        return [
            'id' => null,
            'category_id' => null,
            'html' => '',
            'css' => '',
            'js' => '',
            'editor_json' => json_encode($editorJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'grapesjs_json' => null,
            'editor_type' => 'fabric',
        ];
    }

    private function withPhotoboothProjectIntent(array $template): array
    {
        $editorJson = (string) ($template['editor_json'] ?? $template['grapesjs_json'] ?? '');
        $data = json_decode($editorJson, true);

        if (! is_array($data) || ($data['renderer'] ?? '') !== 'fabric') {
            return $template;
        }

        $data['projectIntent'] = 'photobooth';
        $data['editMode'] = 'photobooth';
        $data['activePhotoboothFrameIndex'] = max(0, (int) ($data['activePhotoboothFrameIndex'] ?? 0));

        $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded !== false) {
            $template['editor_json'] = $encoded;
            if (! empty($template['grapesjs_json'])) {
                $template['grapesjs_json'] = $encoded;
            }
        }

        return $template;
    }

    private function withBusinessProfileProjectIntent(array $template): array
    {
        $editorJson = (string) ($template['editor_json'] ?? $template['grapesjs_json'] ?? '');
        $data = json_decode($editorJson, true);

        if (! is_array($data) || ($data['renderer'] ?? '') !== 'fabric') {
            return $template;
        }

        $data['projectIntent'] = 'business_profile';
        $data['editMode'] = 'pages';
        $data['guestbook'] = is_array($data['guestbook'] ?? null) ? $data['guestbook'] : [];
        $data['guestbook']['enabled'] = false;

        $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded !== false) {
            $template['editor_json'] = $encoded;
            if (! empty($template['grapesjs_json'])) {
                $template['grapesjs_json'] = $encoded;
            }
        }

        return $template;
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

    private function canCreateLandingPage(int $userId, array $subscription, ?int $usedPages = null): bool
    {
        $usedPages ??= (new LandingPageModel())->where('user_id', $userId)->countAllResults();

        return $usedPages < $this->maxPagesFromSubscription($subscription);
    }

    private function maxPagesFromSubscription(array $subscription): int
    {
        if (((int) ($subscription['is_unlimited_pages'] ?? 0)) === 1) {
            return PHP_INT_MAX;
        }

        $configuredLimit = (int) ($subscription['max_pages'] ?? 0);
        if ($configuredLimit > 0) {
            return $configuredLimit;
        }

        $planKey = strtolower((string) ($subscription['plan_slug'] ?? $subscription['plan_name'] ?? ''));

        return match ($planKey) {
            'basic', 'starter' => 1,
            'premium' => 3,
            'business', 'busseniss' => 10,
            default => max(0, (int) ($subscription['max_pages'] ?? 0)),
        };
    }
}
