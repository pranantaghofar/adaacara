<?php

namespace App\Controllers;

use App\Libraries\SellerTemplateService;
use App\Libraries\CreatorRoyaltyService;
use App\Libraries\BusinessProfileAccessService;
use App\Libraries\ProductEntitlementService;
use App\Libraries\CustomFontService;
use App\Libraries\GeminiAcaraAiPromptService;
use App\Libraries\GeminiAcaraAiFlexibleService;
use App\Libraries\GeminiVisionBlueprintService;
use App\Libraries\GeminiMagicLayerOcrService;
use App\Libraries\OcrProviderInterface;
use App\Libraries\OpenAiCompatibleVisionBlueprintService;
use App\Libraries\OcrTextDetectionService;
use App\Libraries\PollinationsAcaraAiPromptService;
use App\Models\CategoryModel;
use App\Models\EditorAdModel;
use App\Models\FreePublishEntitlementModel;
use App\Models\PublishedDomainModel;
use App\Models\UserSubscriptionModel;
use App\Models\UserModel;
use App\Models\OrderModel;
use App\Models\TemplateModel;
use App\Models\TemplateSubcategoryModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class EditorController extends BaseController
{
    private string $table = 'landing_pages';

    public function index(int $id): string|RedirectResponse
    {
        return $this->edit($id);
    }

    public function edit(int $id): string|RedirectResponse
    {
        $currentUserId = (int) (session()->get('userId') ?? 0);
        try {
            $page = $this->findPage($id);
        } catch (PageNotFoundException) {
            return redirect()
                ->to(site_url('dashboard'))
                ->with('error', 'Undangan tidak ditemukan atau bukan milik akun kamu.');
        }

        $isAdmin = $currentUserId > 0 && $this->isAdminRole($this->currentUserRole($currentUserId));
        $subscription = $currentUserId > 0
            ? (new UserSubscriptionModel())->activeWithPlanByUser($currentUserId)
            : null;
        $sellerService = new SellerTemplateService();
        $isActiveCreator = ! $isAdmin && $sellerService->isActiveCreator($currentUserId);
        $currentRole = $this->currentUserRole($currentUserId);
        $canManageTemplateContent = in_array($currentRole, ['superadmin', 'content_admin'], true);
        $hasActiveMembership = $isAdmin || $subscription !== null;
        $canUseEditorPremiumFeatures = $hasActiveMembership || $isActiveCreator;
        $canUseAiPremiumFeatures = $hasActiveMembership;
        $canUseReferenceMapper = $this->canUseReferenceMapper($isAdmin, $isActiveCreator, $subscription);
        $canUseOcrTextDetection = $this->canUseOcrTextDetection($isAdmin, $isActiveCreator, $subscription);
        $canUseGuestMemories = $this->canUseGuestMemories((int) ($page['user_id'] ?? $currentUserId));
        $isFreeTemplatePage = $this->pageUsesFreeTemplate($page);
        $canPublishCurrentPage = $this->canPublishPage($currentUserId, $page, $subscription);
        $publishedDomain = $this->publishedDomainForPage((int) ($page['id'] ?? 0));
        $businessProfileAccess = new BusinessProfileAccessService();
        $businessProfilePaymentReady = $businessProfileAccess->tablesReady();
        $hasBusinessProfileEntitlement = $businessProfilePaymentReady
            && $businessProfileAccess->isBusinessProfilePage($page)
            && $businessProfileAccess->hasActiveEntitlement((int) $page['id'], $currentUserId);
        $canSaveTemplate = $canManageTemplateContent || $sellerService->canSaveTemplate($currentUserId);
        $editorAds = (new EditorAdModel())->activeForEditor([
            'user_id' => $currentUserId,
            'has_membership' => $hasActiveMembership,
            'is_creator' => $isActiveCreator,
        ]);
        $customFontService = new CustomFontService();

        return view('editor/index', [
            'title' => 'Editor - ' . ($page['title'] ?? 'Undangan'),
            'page' => $page,
            'editorJsonColumn' => $this->editorJsonColumn(),
            'isAdmin' => $isAdmin,
            'canSaveTemplate' => $canSaveTemplate,
            'saveTemplateUrl' => $canManageTemplateContent ? site_url('admin/templates/from-editor') : site_url('templates/save-as-seller-template'),
            'updateTemplateUrl' => $canManageTemplateContent ? site_url('admin/templates/from-editor/update') : site_url('templates/update-seller-template'),
            'canUpdateSavedTemplate' => $this->canUpdateSavedTemplate($currentUserId, $currentRole, $isActiveCreator),
            'saveTemplateTargets' => $this->saveTemplateTargets($currentUserId, $currentRole, $isActiveCreator),
            'saveTemplateDescription' => $canManageTemplateContent
                ? 'Simpan desain Fabric saat ini sebagai reusable template.'
                : 'Template creator akan direview admin terlebih dahulu sebelum tampil di halaman template.',
            'editorAssetUploadToken' => $this->canManageEditorAssets($currentUserId) ? $this->editorAssetUploadToken($currentUserId) : '',
            'canManageEditorAssets' => $this->canManageEditorAssets($currentUserId),
            'isLoggedIn' => $currentUserId > 0,
            'isActiveCreator' => $isActiveCreator,
            'hasActiveMembership' => $hasActiveMembership,
            'canUseEditorPremiumFeatures' => $canUseEditorPremiumFeatures,
            'canUseAiPremiumFeatures' => $canUseAiPremiumFeatures,
            'canUseReferenceMapper' => $canUseReferenceMapper,
            'canUseOcrTextDetection' => $canUseOcrTextDetection,
            'canUseGuestMemories' => $canUseGuestMemories,
            'canPublishCurrentPage' => $canPublishCurrentPage,
            'isFreeTemplatePage' => $isFreeTemplatePage,
            'pageAccessTier' => $isFreeTemplatePage && ! $hasActiveMembership ? 'free' : 'premium',
            'publishedDomain' => $publishedDomain,
            'publishedDomainOptions' => $this->publishedDomainOptions(),
            'businessProfilePaymentReady' => $businessProfilePaymentReady,
            'hasBusinessProfileEntitlement' => $hasBusinessProfileEntitlement,
            'businessProfileCheckoutUrl' => $businessProfileAccess->checkoutUrl((int) $page['id']),
            'plansUrl' => site_url('plans'),
            'loginUrl' => site_url('login') . '?redirect=' . rawurlencode('/editor/' . $id),
            'templateCategories' => $this->templateCategories(),
            'templateSubcategories' => $canManageTemplateContent ? $this->templateSubcategories() : [],
            'editorTemplates' => $this->editorTemplates(),
            'editorAds' => $editorAds,
            'customFonts' => $customFontService->fontOptions(),
            'customFontCssUrl' => site_url('custom-fonts.css'),
        ]);
    }

    public function save(int $id): ResponseInterface
    {
        $page = $this->findPage($id);
        $payload = $this->requestPayload();
        $this->logLargeEditorPayload($payload, (int) $page['id'], 'save');

        $data = [
            'html' => (string) ($payload['html'] ?? ''),
            'css' => (string) ($payload['css'] ?? ''),
            'js' => $this->sanitizePublishedJs((string) ($payload['js'] ?? '')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $editorJson = $payload['editor_json'] ?? $payload['grapesjs_json'] ?? null;
        if ($editorJson !== null) {
            $data[$this->editorJsonColumn()] = $this->prepareEditorJsonForStorage($editorJson, $page);
        }

        $thumbnail = $this->storeEditorThumbnailImage((string) ($payload['thumbnail_data'] ?? ''), $page);
        $currentOgImage = (string) ($page['og_image'] ?? '');
        if ($thumbnail !== '' && ! $this->isManualPublishedOgImage($currentOgImage)) {
            $data['og_image'] = $thumbnail;
        }

        Database::connect()
            ->table($this->table)
            ->where('id', $page['id'])
            ->update($this->filterExistingColumns($data));

        return $this->response->setJSON([
            'status' => true,
            'success' => true,
            'message' => 'Desain berhasil disimpan',
        ]);
    }

    public function publish(int $id): ResponseInterface
    {
        $page = $this->findPage($id);
        $currentUserId = (int) (session()->get('userId') ?? 0);
        $isAdmin = $this->isAdminRole((string) session()->get('userRole'));
        $subscription = $currentUserId > 0
            ? (new UserSubscriptionModel())->activeWithPlanByUser($currentUserId)
            : null;

        if ($currentUserId <= 0) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'code' => 'login_required',
                    'message' => 'Silakan login untuk publish undangan.',
                    'redirect' => site_url('login'),
                ]);
        }

        if (! $this->canPublishPage($currentUserId, $page, $subscription)) {
            return $this->response
                ->setStatusCode(402)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'code' => 'membership_required',
                    'message' => 'Publish template premium membutuhkan paket aktif.',
                    'redirect' => site_url('plans'),
                ]);
        }

        $businessProfileAccess = new BusinessProfileAccessService();
        $isBusinessProfilePage = $businessProfileAccess->isBusinessProfilePage($page);
        if ($isBusinessProfilePage && $businessProfileAccess->tablesReady() && ! $businessProfileAccess->hasActiveEntitlement((int) $page['id'], $currentUserId)) {
            if ($businessProfileAccess->activatePageFromProductCredit((int) $page['id'], $currentUserId)) {
                $page = $this->findPage($id);
            } else {
            $businessProfileAccess->ensurePendingOrder($currentUserId, (int) $page['id']);

            return $this->response
                ->setStatusCode(402)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'code' => 'business_profile_payment_required',
                    'message' => 'Aktifkan Business Profile Rp79.000 untuk publish website ini.',
                    'redirect' => $businessProfileAccess->checkoutUrl((int) $page['id']),
                    'checkout_url' => $businessProfileAccess->checkoutUrl((int) $page['id']),
                ]);
            }
        }

        if (! $isBusinessProfilePage && ! $isAdmin && $this->publishLimitReached($currentUserId, $subscription, $page)) {
            return $this->response
                ->setStatusCode(402)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'code' => 'publish_limit_reached',
                    'message' => 'Limit publish paket kamu sudah tercapai.',
                    'redirect' => site_url('plans'),
                ]);
        }

        $payload = $this->requestPayload();
        $this->logLargeEditorPayload($payload, (int) $page['id'], 'publish');
        $title = trim((string) ($payload['title'] ?? $page['title'] ?? 'Undangan'));
        $slug = $this->normalizeSlug((string) ($payload['slug'] ?? $page['slug'] ?? ''));
        $editorJsonColumn = $this->editorJsonColumn();
        $html = (string) ($payload['html'] ?? $page['html'] ?? '');
        $css = (string) ($payload['css'] ?? $page['css'] ?? '');
        $js = $this->sanitizePublishedJs((string) ($payload['js'] ?? $page['js'] ?? ''));
        $editorJson = $payload['editor_json'] ?? $payload['grapesjs_json'] ?? ($page[$editorJsonColumn] ?? '');

        if (trim($html) === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Desain belum bisa dipublish karena HTML masih kosong. Simpan desain terlebih dahulu.',
                ]);
        }

        if ($slug === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Slug URL wajib diisi sebelum publish.',
                ]);
        }

        if (! $this->isValidSlug($slug)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Slug hanya boleh memakai huruf kecil, angka, dan strip.',
                ]);
        }

        if (! $this->isSlugAvailable($slug, (int) $page['id'])) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Slug sudah dipakai undangan lain. Pilih slug berbeda.',
                ]);
        }

        $publishedDomainPayload = $this->preparePublishedDomainPayload($payload, $slug, $page, $subscription);
        if (! ($publishedDomainPayload['ok'] ?? true)) {
            return $this->response
                ->setStatusCode((int) ($publishedDomainPayload['status'] ?? 422))
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'code' => (string) ($publishedDomainPayload['code'] ?? 'published_domain_invalid'),
                    'message' => (string) ($publishedDomainPayload['message'] ?? 'Domain publish belum valid.'),
                    'redirect' => (string) ($publishedDomainPayload['redirect'] ?? ''),
                ]);
        }

        $publishedAt = date('Y-m-d H:i:s');
        if (! $isBusinessProfilePage && ! $isAdmin && $subscription === null) {
            $freePublish = $this->reserveFreePublishEntitlement($currentUserId, $page, $publishedAt);
            if (! ($freePublish['allowed'] ?? false)) {
                return $this->response
                    ->setStatusCode(402)
                    ->setJSON([
                        'status' => false,
                        'success' => false,
                        'code' => 'free_publish_expired',
                        'message' => (string) ($freePublish['message'] ?? 'Masa aktif publish free sudah habis.'),
                        'redirect' => site_url('plans'),
                    ]);
            }

            $publishedAt = (string) ($freePublish['published_at'] ?? $publishedAt);
        }
        $publicUrl = site_url('u/' . $slug);
        $editorJson = $this->prepareEditorJsonForStorage($editorJson, $page);
        $thumbnail = $this->storeEditorThumbnailImage((string) ($payload['thumbnail_data'] ?? ''), $page);
        $manualOgImage = $this->storePublishedOgImageUploadSafely($page);
        if (! ($manualOgImage['ok'] ?? true)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => (string) ($manualOgImage['message'] ?? 'Gambar preview link tidak valid.'),
                ]);
        }
        $ogImage = (string) ($manualOgImage['url'] ?? '');
        $currentOgImage = (string) ($page['og_image'] ?? '');
        $finalOgImage = $ogImage !== ''
            ? $ogImage
            : (
                $this->isManualPublishedOgImage($currentOgImage)
                    ? $currentOgImage
                    : ($thumbnail !== '' ? $thumbnail : ($currentOgImage !== '' ? $currentOgImage : null))
            );

        Database::connect()
            ->table($this->table)
            ->where('id', $page['id'])
            ->update($this->filterExistingColumns([
                'title' => $title !== '' ? $title : ($page['title'] ?? 'Undangan'),
                'slug' => $slug,
                'status' => 'published',
                'html' => $html,
                'css' => $css,
                'js' => $js,
                'og_image' => $finalOgImage,
                $editorJsonColumn => (string) $editorJson,
                'published_html' => $html,
                'published_css' => $css,
                'published_js' => $js,
                'published_editor_json' => (string) $editorJson,
                'published_at' => $publishedAt,
                'updated_at' => $publishedAt,
            ]));

        $publishedDomain = $this->storePublishedDomainAlias((int) $page['id'], $currentUserId, $publishedDomainPayload);
        if (($publishedDomain['status'] ?? '') === 'active' && ! empty($publishedDomain['url'])) {
            $publicUrl = (string) $publishedDomain['url'];
        }

        (new SellerTemplateService())->processSellerTemplateCommission((int) $page['id'], $currentUserId);
        try {
            $royaltyOrder = null;
            $subscriptionOrderId = (int) ($subscription['order_id'] ?? 0);
            if ($subscriptionOrderId > 0) {
                $royaltyOrder = (new OrderModel())->findAdminOrder($subscriptionOrderId);
            }
            (new CreatorRoyaltyService())->createPendingRoyaltyForPublishedUsage((int) $page['id'], $currentUserId, $royaltyOrder, [
                'source' => 'editor_publish',
                'page_status' => 'published',
            ]);
        } catch (\Throwable $error) {
            log_message('warning', 'Creator royalty publish integration skipped. invitation={invitation} user={user} error={error}', [
                'invitation' => (string) ($page['id'] ?? '-'),
                'user' => (string) $currentUserId,
                'error' => $error->getMessage(),
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'success' => true,
            'message' => $publishedDomain !== []
                ? 'Website berhasil diajukan. Alamat pilihan sedang diaktifkan.'
                : 'Undangan berhasil dipublish.',
            'public_url' => $publicUrl,
            'url' => $publicUrl,
            'og_image' => $finalOgImage,
            'published_domain' => $publishedDomain,
        ]);
    }

    public function checkSlug(): ResponseInterface
    {
        $pageId = (int) ($this->request->getGet('id') ?? 0);
        if ($pageId > 0) {
            $this->findPage($pageId);
        }

        $slug = $this->normalizeSlug((string) ($this->request->getGet('slug') ?? ''));

        if ($slug === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'available' => false,
                    'message' => 'Slug URL wajib diisi.',
                ]);
        }

        if (! $this->isValidSlug($slug)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'available' => false,
                    'message' => 'Slug hanya boleh memakai huruf kecil, angka, dan strip.',
                ]);
        }

        $available = $this->isSlugAvailable($slug, $pageId);

        return $this->response->setJSON([
            'status' => true,
            'success' => true,
            'available' => $available,
            'slug' => $slug,
            'message' => $available ? 'Slug tersedia.' : 'Slug sudah dipakai.',
        ]);
    }

    public function unpublish(int $id): ResponseInterface
    {
        $page = $this->findPage($id);

        Database::connect()
            ->table($this->table)
            ->where('id', $page['id'])
            ->update($this->filterExistingColumns([
                'status' => 'draft',
                'updated_at' => date('Y-m-d H:i:s'),
            ]));

        return $this->response->setJSON([
            'status' => true,
            'success' => true,
            'message' => 'Undangan dikembalikan ke draft.',
        ]);
    }

    public function detectOcrText(int $id): ResponseInterface
    {
        $page = $this->findPage($id);
        $currentUserId = (int) (session()->get('userId') ?? 0);
        $isAdmin = $currentUserId > 0 && $this->isAdminRole($this->currentUserRole($currentUserId));
        $subscription = $currentUserId > 0
            ? (new UserSubscriptionModel())->activeWithPlanByUser($currentUserId)
            : null;
        $isActiveCreator = ! $isAdmin && (new SellerTemplateService())->isActiveCreator($currentUserId);

        if (! filter_var(env('editor_ocr_text_enabled', false), FILTER_VALIDATE_BOOLEAN)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Deteksi Teks AI belum aktif.',
                ]);
        }

        if (! $this->canUseOcrTextDetection($isAdmin, $isActiveCreator, $subscription)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Deteksi Teks AI membutuhkan akses membership aktif atau admin.',
                ]);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $imageSrc = (string) ($payload['image_src'] ?? '');
        $engine = (string) ($payload['engine'] ?? '');
        $creativePrompt = trim(mb_substr((string) ($payload['creative_prompt'] ?? ''), 0, 1200));

        if (! in_array($engine, ['asset-metadata', 'tesseract-browser'], true) && ! $this->allowOcrTextRequest($currentUserId)) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => 'Terlalu banyak request Deteksi Teks AI. Coba lagi sebentar.',
                ]);
        }

        $asset = $this->resolveOcrTextAsset($imageSrc, (int) ($page['user_id'] ?? $currentUserId));

        if ($asset === null) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Asset gambar referensi tidak valid atau bukan milik akun ini.',
                ]);
        }

        if (in_array($engine, ['asset-metadata', 'tesseract-browser'], true)) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'image_src' => base_url($asset['relative_path']),
                    'imageWidth' => $asset['width'],
                    'imageHeight' => $asset['height'],
                    'mime' => $asset['mime'],
                ],
            ]);
        }

        try {
            $provider = $engine === 'gemini-vision' ? $this->designBlueprintProvider() : null;
            $blueprint = (new OcrTextDetectionService($provider))->detect($asset['path'], [
                'page_id' => (int) ($page['id'] ?? 0),
                'user_id' => $currentUserId,
                'mime' => $asset['mime'],
                'creative_prompt' => $creativePrompt,
            ]);
            if ($engine === 'gemini-vision') {
                $blueprint = $this->attachGeminiBackgroundColor($blueprint, $asset);
                $blueprint = $this->attachGeminiCanvasOverlay($blueprint, $asset);
                $blueprint = $this->attachGeminiFrameAssets($blueprint, $asset, $currentUserId, (int) ($page['id'] ?? 0));
                $blueprint = $this->attachGeminiPhotoAssets($blueprint, $asset, $currentUserId, (int) ($page['id'] ?? 0));
                $blueprint = $this->attachGeminiDecorationAssets($blueprint, $asset, $currentUserId, (int) ($page['id'] ?? 0));
            }
        } catch (\Throwable $exception) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $blueprint,
        ]);
    }

    public function detectMagicLayerOcr(int $id): ResponseInterface
    {
        $page = $this->findPage($id);
        $currentUserId = (int) (session()->get('userId') ?? 0);
        $isAdmin = $currentUserId > 0 && $this->isAdminRole($this->currentUserRole($currentUserId));
        $subscription = $currentUserId > 0
            ? (new UserSubscriptionModel())->activeWithPlanByUser($currentUserId)
            : null;
        $isActiveCreator = ! $isAdmin && (new SellerTemplateService())->isActiveCreator($currentUserId);

        if (! filter_var(env('editor_ocr_text_enabled', false), FILTER_VALIDATE_BOOLEAN)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Magic Layer OCR belum aktif.',
                ]);
        }

        if (! $this->canUseOcrTextDetection($isAdmin, $isActiveCreator, $subscription)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Magic Layer OCR membutuhkan akses membership aktif atau admin.',
                ]);
        }

        if (! $this->allowOcrTextRequest($currentUserId)) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => 'Terlalu banyak request Magic Layer OCR. Coba lagi sebentar.',
                ]);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $imageSrc = (string) ($payload['image_src'] ?? '');
        $asset = $this->resolveOcrTextAsset($imageSrc, (int) ($page['user_id'] ?? $currentUserId));

        if ($asset === null) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Asset gambar Magic Layer tidak valid atau bukan milik akun ini.',
                ]);
        }

        try {
            $blueprint = (new OcrTextDetectionService(new GeminiMagicLayerOcrService()))->detect($asset['path'], [
                'page_id' => (int) ($page['id'] ?? 0),
                'user_id' => $currentUserId,
                'mime' => $asset['mime'],
            ]);
        } catch (\Throwable $exception) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $blueprint,
        ]);
    }

    public function generateAcaraAi(int $id): ResponseInterface
    {
        $page = $this->findPage($id);
        $currentUserId = (int) (session()->get('userId') ?? 0);
        $isAdmin = $currentUserId > 0 && $this->isAdminRole($this->currentUserRole($currentUserId));
        $subscription = $currentUserId > 0
            ? (new UserSubscriptionModel())->activeWithPlanByUser($currentUserId)
            : null;
        $isActiveCreator = ! $isAdmin && (new SellerTemplateService())->isActiveCreator($currentUserId);

        if (! filter_var(env('editor_ocr_text_enabled', false), FILTER_VALIDATE_BOOLEAN)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'ACARA AI belum aktif.',
                ]);
        }

        if (! $this->canUseOcrTextDetection($isAdmin, $isActiveCreator, $subscription)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'ACARA AI membutuhkan akses membership aktif atau admin.',
                ]);
        }

        if (! $this->allowOcrTextRequest($currentUserId)) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => 'Terlalu banyak request ACARA AI. Coba lagi sebentar.',
                ]);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $prompt = trim(mb_substr((string) ($payload['prompt'] ?? ''), 0, 2000));
        if ($prompt === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Tulis prompt ACARA AI terlebih dahulu.',
                ]);
        }

        $imageSrc = (string) ($payload['image_src'] ?? '');
        $asset = null;
        if ($imageSrc !== '') {
            $asset = $this->resolveOcrTextAsset($imageSrc, (int) ($page['user_id'] ?? $currentUserId));
            if ($asset === null) {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Gambar referensi ACARA AI tidak valid atau bukan milik akun ini.',
                    ]);
            }
        }

        $width = max(320, min(6000, (int) ($payload['imageWidth'] ?? 1080)));
        $height = max(320, min(6000, (int) ($payload['imageHeight'] ?? 1920)));
        $intent = (string) ($payload['intent'] ?? 'new_design');
        $intent = in_array($intent, ['new_design', 'redesign_current_page'], true) ? $intent : 'new_design';
        $history = is_array($payload['history'] ?? null) ? array_slice($payload['history'], -8) : [];
        $pageContext = is_array($payload['page_context'] ?? null) ? $payload['page_context'] : [];

        try {
            $raw = $this->acaraAiPromptService()->generate($prompt, [
                'imageWidth' => $width,
                'imageHeight' => $height,
                'imagePath' => $asset['path'] ?? '',
                'mime' => $asset['mime'] ?? '',
                'intent' => $intent,
                'history' => $history,
                'pageContext' => $pageContext,
                'userId' => $currentUserId,
                'pageId' => (int) ($page['id'] ?? 0),
            ]);
            $blueprint = (new OcrTextDetectionService())->normalizeGeneratedBlueprint($raw);
        } catch (\Throwable $exception) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $blueprint,
        ]);
    }

    private function acaraAiPromptService(): object
    {
        $provider = strtolower(trim((string) env('ACARA_AI_PROVIDER', 'gemini-flex')));
        $geminiModel = trim((string) env('ACARA_AI_GEMINI_MODEL', ''));
        if ($geminiModel !== '' && in_array($provider, ['pollinations', 'pollinations-ai'], true)) {
            $provider = 'gemini-flex';
        }

        return match ($provider) {
            'gemini-flex', 'gemini-flexible', 'gemini-image' => new GeminiAcaraAiFlexibleService(),
            'pollinations', 'pollinations-ai' => new PollinationsAcaraAiPromptService(),
            default => new GeminiAcaraAiPromptService(),
        };
    }

    private function designBlueprintProvider(): OcrProviderInterface
    {
        $provider = strtolower(trim((string) env('ADAACARA_AI_PROVIDER', env('EDITOR_AI_PROVIDER', 'gemini'))));

        return match ($provider) {
            'gemma', 'openai', 'openai-compatible', 'compatible' => new OpenAiCompatibleVisionBlueprintService(),
            default => new GeminiVisionBlueprintService(),
        };
    }

    public function assets(int $id): ResponseInterface
    {
        $page = $this->findPage($id);
        $db = Database::connect();

        if (! $db->tableExists('media')) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
            ]);
        }

        $userId = (int) session()->get('userId');
        $fields = $db->getFieldNames('media');
        $builder = $db->table('media')
            ->where('user_id', $userId);

        if (in_array('landing_page_id', $fields, true)) {
            $builder
                ->groupStart()
                    ->where('landing_page_id', $page['id'])
                    ->orWhere('landing_page_id', null)
                ->groupEnd();
        }

        if (in_array('file_type', $fields, true)) {
            $builder->like('file_type', 'image', 'after');
        }

        if (in_array('created_at', $fields, true)) {
            $builder->orderBy('created_at', 'DESC');
        }

        $assets = $builder
            ->limit(80)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => array_map(static fn (array $asset): array => [
                'src' => base_url(ltrim((string) $asset['file_path'], '/')),
                'name' => $asset['file_name'] ?? basename((string) $asset['file_path']),
                'type' => 'image',
                'width' => 0,
                'height' => 0,
            ], $assets),
        ]);
    }

    public function uploadAsset(?int $id = null): ResponseInterface
    {
        $page = null;
        if ($id !== null) {
            $page = $this->findPage($id);
        }

        $file = $this->request->getFile('asset');

        if (! $file || ! $file->isValid()) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'File gambar tidak valid.',
                ]);
        }

        $rules = [
            'asset' => [
                'label' => 'Gambar',
                'rules' => 'uploaded[asset]|max_size[asset,2048]|is_image[asset]|mime_in[asset,image/jpg,image/jpeg,image/png,image/webp,image/gif]|ext_in[asset,jpg,jpeg,png,webp,gif]',
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => implode(' ', $this->validator->getErrors()),
                ]);
        }

        $userId = (int) session()->get('userId');
        $uploadPath = FCPATH . 'uploads/editor/' . $userId;

        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = $file->getRandomName();
        $mimeType = $file->getMimeType();
        $file->move($uploadPath, $fileName);

        $optimized = $this->optimizeEditorImage($uploadPath, $fileName, $mimeType);
        $fileName = $optimized['file_name'];
        $mimeType = $optimized['mime_type'];
        $fileSize = $optimized['file_size'];

        $relativePath = 'uploads/editor/' . $userId . '/' . $fileName;
        $url = base_url($relativePath);

        $db = Database::connect();
        if ($db->tableExists('media')) {
            $db->table('media')->insert($this->filterMediaColumns([
                'user_id' => $userId,
                'landing_page_id' => $page['id'] ?? null,
                'file_name' => $file->getClientName() ?: $fileName,
                'file_path' => $relativePath,
                'file_type' => 'image',
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                [
                    'src' => $url,
                    'type' => 'image',
                ],
            ],
        ]);
    }

    public function preview(int $id): string
    {
        $page = $this->findPage($id);
        $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');

        return view('public/render', [
            'page' => $page,
            'isPreview' => true,
            'guestbookEntries' => [],
        ]);
    }

    public function published(string $slug): string
    {
        $page = $this->findPublishedBySlug($slug);
        $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');

        return view('public/render', [
            'page' => $page,
            'isPreview' => false,
            'guestbookEntries' => $this->guestbookEntries((int) $page['id']),
        ]);
    }

    public function guestbook(string $slug): ResponseInterface
    {
        $page = $this->findPublishedBySlug($slug);

        $rules = [
            'guest_name' => 'required|min_length[2]|max_length[120]',
            'message' => 'required|min_length[2]|max_length[1000]',
            'attendance' => 'required|in_list[hadir,tidak_hadir,ragu]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('guestbook_errors', $this->validator->getErrors());
        }

        Database::connect()
            ->table('guest_books')
            ->insert($this->filterGuestbookColumns([
                'landing_page_id' => (int) $page['id'],
                'guest_name' => trim((string) $this->request->getPost('guest_name')),
                'message' => trim((string) $this->request->getPost('message')),
                'sticker' => trim((string) $this->request->getPost('sticker')) ?: null,
                'attendance' => (string) $this->request->getPost('attendance'),
                'is_approved' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));

        return redirect()->to(site_url('u/' . $page['slug']) . '#guestbook')
            ->with('guestbook_success', 'Terima kasih, ucapan kamu sudah tersimpan.');
    }

    private function findPage(int $id, bool $mustOwn = true): array
    {
        $builder = Database::connect()->table($this->table)->where('id', $id);
        $userId = (int) (session()->get('userId') ?? 0);

        if ($mustOwn) {
            if ($userId <= 0) {
                throw PageNotFoundException::forPageNotFound('Silakan login untuk membuka editor.');
            }

            if ($this->currentUserRole($userId) !== 'admin') {
                $builder->where('user_id', $userId);
            }
        }

        $page = $builder->get()->getRowArray();

        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Undangan tidak ditemukan.');
        }

        return $page;
    }

    private function canUseGuestMemories(int $ownerUserId): bool
    {
        if ($ownerUserId <= 0) {
            return false;
        }

        try {
            $db = Database::connect();
            if (! $db->tableExists('guest_memory_user_settings')) {
                return false;
            }

            $setting = $db->table('guest_memory_user_settings')
                ->select('is_enabled')
                ->where('user_id', $ownerUserId)
                ->get(1)
                ->getRowArray();

            return ((int) ($setting['is_enabled'] ?? 0)) === 1
                || (new ProductEntitlementService())->hasActive($ownerUserId, ProductEntitlementService::PHOTOBOOTH_STANDALONE);
        } catch (\Throwable) {
            return false;
        }
    }

    private function publishedDomainOptions(): array
    {
        return [
            [
                'root_domain' => 'adaacara.com',
                'label' => 'adaacara.com',
                'type' => 'standard',
                'available' => true,
                'price_label' => 'GRATIS',
            ],
        ];
    }

    private function publishedDomainForPage(int $landingPageId): ?array
    {
        try {
            return (new PublishedDomainModel())->primaryForPage($landingPageId);
        } catch (\Throwable) {
            return null;
        }
    }

    private function preparePublishedDomainPayload(array $payload, string $slug, array $page, ?array $subscription): array
    {
        $subdomain = $this->normalizePublishedSubdomain((string) ($payload['public_subdomain'] ?? $slug));
        $rootDomain = $this->normalizePublishedRootDomain((string) ($payload['public_root_domain'] ?? 'adaacara.com'));

        if ($subdomain === '') {
            return [
                'ok' => false,
                'message' => 'Nama subdomain wajib diisi.',
            ];
        }

        if ($rootDomain === '') {
            return [
                'ok' => false,
                'message' => 'Pilihan domain belum valid.',
            ];
        }

        if ($this->publishedSubdomainReserved($subdomain)) {
            return [
                'ok' => false,
                'message' => 'Nama subdomain ini tidak bisa digunakan. Pilih nama lain.',
            ];
        }

        $options = array_column($this->publishedDomainOptions(), null, 'root_domain');
        $option = $options[$rootDomain] ?? null;
        if ($option === null) {
            return [
                'ok' => false,
                'message' => 'Domain publish belum tersedia.',
            ];
        }

        if (($option['type'] ?? '') === 'premium' && $subscription === null) {
            return [
                'ok' => false,
                'status' => 402,
                'code' => 'published_domain_premium_required',
                'message' => 'Domain premium membutuhkan paket aktif.',
                'redirect' => site_url('plans'),
            ];
        }

        if (empty($option['available'])) {
            return [
                'ok' => false,
                'message' => 'Domain ini sedang disiapkan. Gunakan adaacara.com terlebih dahulu.',
            ];
        }

        $fullDomain = $subdomain . '.' . $rootDomain;
        try {
            $model = new PublishedDomainModel();
            if ($model->tableReady() && ! $model->fullDomainAvailable($fullDomain, (int) ($page['id'] ?? 0))) {
                return [
                    'ok' => false,
                    'status' => 409,
                    'code' => 'published_domain_taken',
                    'message' => 'Subdomain ini sudah dipakai. Pilih nama lain.',
                ];
            }
        } catch (\Throwable) {
            return [
                'ok' => false,
                'message' => 'Domain publish belum bisa dicek saat ini.',
            ];
        }

        return [
            'ok' => true,
            'subdomain' => $subdomain,
            'root_domain' => $rootDomain,
            'full_domain' => $fullDomain,
            'type' => (string) ($option['type'] ?? 'standard'),
        ];
    }

    private function storePublishedDomainAlias(int $landingPageId, int $userId, array $domainPayload): array
    {
        if ($landingPageId <= 0 || $userId <= 0 || empty($domainPayload['full_domain'])) {
            return [];
        }

        try {
            $model = new PublishedDomainModel();
            if (! $model->tableReady()) {
                return [];
            }

            $existing = $model->primaryForPage($landingPageId);
            $existingStatus = is_array($existing) ? (string) ($existing['status'] ?? '') : '';
            $existingFullDomain = is_array($existing) ? (string) ($existing['full_domain'] ?? '') : '';
            $nextStatus = $existingStatus === 'active' && $existingFullDomain === (string) $domainPayload['full_domain']
                ? 'active'
                : 'pending_activation';
            $now = date('Y-m-d H:i:s');
            $data = [
                'landing_page_id' => $landingPageId,
                'user_id' => $userId,
                'subdomain' => (string) $domainPayload['subdomain'],
                'root_domain' => (string) $domainPayload['root_domain'],
                'full_domain' => (string) $domainPayload['full_domain'],
                'type' => (string) ($domainPayload['type'] ?? 'standard'),
                'project_type' => $this->publishedDomainProjectType($landingPageId),
                'status' => $nextStatus,
                'is_primary' => 1,
                'reserved_at' => $nextStatus === 'pending_activation'
                    ? (string) ($existing['reserved_at'] ?? $now)
                    : (string) ($existing['reserved_at'] ?? $now),
            ];
            if ($nextStatus !== 'active') {
                $data['activated_at'] = null;
                $data['failed_at'] = null;
            }
            $data = $model->filterExistingFields($data);

            if (is_array($existing)) {
                $model->update((int) $existing['id'], $data);
            } else {
                $model->insert($data);
            }

            return [
                'subdomain' => (string) $domainPayload['subdomain'],
                'root_domain' => (string) $domainPayload['root_domain'],
                'full_domain' => (string) $domainPayload['full_domain'],
                'url' => 'https://' . (string) $domainPayload['full_domain'],
                'status' => $nextStatus,
                'status_label' => $this->publishedDomainStatusLabel($nextStatus),
                'progress' => $this->publishedDomainProgress($nextStatus),
            ];
        } catch (\Throwable $exception) {
            log_message('warning', 'Published domain alias save failed for page {page}: {message}', [
                'page' => (string) $landingPageId,
                'message' => $exception->getMessage(),
            ]);
        }

        return [];
    }

    private function publishedDomainProjectType(int $landingPageId): string
    {
        try {
            $page = $this->findPage($landingPageId);
            $data = json_decode((string) ($page['editor_json'] ?? $page['grapesjs_json'] ?? ''), true);
            if (! is_array($data)) {
                $data = [];
            }
            $intent = strtolower(trim((string) ($data['projectIntent'] ?? $data['project_intent'] ?? '')));

            return match ($intent) {
                'photobooth', 'digital_photobooth' => 'photobooth',
                'business_profile', 'business-profile' => 'business_profile',
                default => 'invitation',
            };
        } catch (\Throwable) {
            return 'invitation';
        }
    }

    private function publishedDomainStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Website aktif',
            'activating' => 'Alamat sedang diaktifkan',
            'failed' => 'Aktivasi alamat terkendala',
            'suspended' => 'Alamat dinonaktifkan',
            'disabled' => 'Alamat nonaktif',
            default => 'Menunggu aktivasi',
        };
    }

    private function publishedDomainProgress(string $status): array
    {
        $steps = [
            'design_saved' => ['label' => 'Desain tersimpan', 'state' => 'done'],
            'address_selected' => ['label' => 'Alamat dipilih', 'state' => 'done'],
            'address_activation' => ['label' => 'Aktivasi alamat', 'state' => 'current'],
            'website_active' => ['label' => 'Website aktif', 'state' => 'pending'],
        ];

        if ($status === 'active') {
            $steps['address_activation']['state'] = 'done';
            $steps['website_active']['state'] = 'done';
        } elseif ($status === 'failed') {
            $steps['address_activation']['state'] = 'failed';
        } elseif ($status === 'suspended' || $status === 'disabled') {
            $steps['address_activation']['state'] = 'pending';
        }

        return array_values($steps);
    }

    private function normalizePublishedSubdomain(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/^https?:\/\//', '', $value) ?? $value;
        $value = preg_replace('/\..*$/', '', $value) ?? $value;
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
        $value = preg_replace('/-+/', '-', $value) ?? $value;
        $value = trim($value, '-');

        if ($value === '' || strlen($value) < 3 || strlen($value) > 63) {
            return '';
        }

        return preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])$/', $value) ? $value : '';
    }

    private function normalizePublishedRootDomain(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/^https?:\/\//', '', $value) ?? $value;
        $value = preg_replace('/\/.*$/', '', $value) ?? $value;
        $value = trim($value, '.');

        $allowed = array_column($this->publishedDomainOptions(), 'root_domain');

        return in_array($value, $allowed, true) ? $value : '';
    }

    private function publishedSubdomainReserved(string $subdomain): bool
    {
        return in_array($subdomain, [
            'www',
            'admin',
            'api',
            'app',
            'asset',
            'assets',
            'dashboard',
            'editor',
            'login',
            'logout',
            'register',
            'templates',
            'template',
            'plans',
            'checkout',
            'payment',
            'payments',
            'u',
            'user',
            'users',
            'creator',
            'seller',
            'mail',
            'email',
            'smtp',
            'ftp',
            'cpanel',
            'webmail',
            'whm',
            'static',
            'public',
            'uploads',
            'cdn',
            'support',
            'help',
            'blog',
            'docs',
            'memories',
            'rsvp',
        ], true);
    }

    private function findPublishedBySlug(string $slug): array
    {
        $page = Database::connect()
            ->table($this->table)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->get()
            ->getRowArray();

        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Undangan tidak ditemukan.');
        }

        return $page;
    }

    private function guestbookEntries(int $landingPageId): array
    {
        if (! in_array('guest_books', Database::connect()->listTables(), true)) {
            return [];
        }

        return Database::connect()
            ->table('guest_books')
            ->where('landing_page_id', $landingPageId)
            ->where('is_approved', 1)
            ->orderBy('created_at', 'DESC')
            ->limit(30)
            ->get()
            ->getResultArray();
    }

    private function filterGuestbookColumns(array $data): array
    {
        $fields = Database::connect()->getFieldNames('guest_books');

        return array_intersect_key($data, array_flip($fields));
    }

    private function editorJsonColumn(): string
    {
        $fields = Database::connect()->getFieldNames($this->table);

        if (in_array('editor_json', $fields, true)) {
            return 'editor_json';
        }

        return 'grapesjs_json';
    }

    private function filterExistingColumns(array $data): array
    {
        $fields = Database::connect()->getFieldNames($this->table);

        return array_intersect_key($data, array_flip($fields));
    }

    private function sanitizePublishedJs(string $js): string
    {
        if (! str_contains($js, 'textBaseline')) {
            return $js;
        }

        return strtr($js, [
            '"textBaseline":"alphabetical"' => '"textBaseline":"alphabetic"',
            '"textBaseline": "alphabetical"' => '"textBaseline": "alphabetic"',
            "'textBaseline':'alphabetical'" => "'textBaseline':'alphabetic'",
            "'textBaseline': 'alphabetical'" => "'textBaseline': 'alphabetic'",
            'textBaseline:"alphabetical"' => 'textBaseline:"alphabetic"',
            'textBaseline: "alphabetical"' => 'textBaseline: "alphabetic"',
            "textBaseline:'alphabetical'" => "textBaseline:'alphabetic'",
            "textBaseline: 'alphabetical'" => "textBaseline: 'alphabetic'",
        ]);
    }

    private function logLargeEditorPayload(array $payload, int $pageId, string $action): void
    {
        $editorJson = $payload['editor_json'] ?? $payload['grapesjs_json'] ?? '';
        $sizes = [
            'html' => $this->editorPayloadValueSize($payload['html'] ?? ''),
            'css' => $this->editorPayloadValueSize($payload['css'] ?? ''),
            'js' => $this->editorPayloadValueSize($payload['js'] ?? ''),
            'editor_json' => $this->editorPayloadValueSize($editorJson),
            'thumbnail_data' => $this->editorPayloadValueSize($payload['thumbnail_data'] ?? ''),
        ];

        $total = array_sum($sizes);
        if ($total < 12 * 1024 * 1024 && $sizes['js'] < 6 * 1024 * 1024 && $sizes['editor_json'] < 10 * 1024 * 1024) {
            return;
        }

        log_message('warning', 'Large editor payload detected during {action}. page_id={page_id}, total={total}, html={html}, css={css}, js={js}, editor_json={editor_json}, thumbnail={thumbnail}', [
            'action' => $action,
            'page_id' => $pageId,
            'total' => $total,
            'html' => $sizes['html'],
            'css' => $sizes['css'],
            'js' => $sizes['js'],
            'editor_json' => $sizes['editor_json'],
            'thumbnail' => $sizes['thumbnail_data'],
        ]);
    }

    private function editorPayloadValueSize(mixed $value): int
    {
        if (is_string($value)) {
            return strlen($value);
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return strlen((string) $value);
        }

        return 0;
    }

    private function sanitizeEditorJson(string $json): string
    {
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return str_replace('"textBaseline":"alphabetical"', '"textBaseline":"alphabetic"', $json);
        }

        $data = $this->sanitizeFabricData($data);

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $json;
    }

    private function prepareEditorJsonForStorage(mixed $editorJson, array $page): string
    {
        $json = is_string($editorJson)
            ? $this->sanitizeEditorJson($editorJson)
            : $this->sanitizeEditorJson(json_encode($editorJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '');

        return $this->normalizeEditorJsonImages($json, $page);
    }

    private function normalizeEditorJsonImages(string $json, array $page): string
    {
        if (! str_contains($json, 'data:image/')) {
            return $json;
        }

        $data = json_decode($json, true);
        if (! is_array($data)) {
            return $json;
        }

        $userId = (int) ($page['user_id'] ?? session()->get('userId') ?? 0);
        $pageId = (int) ($page['id'] ?? 0);
        $context = [
            'userId' => max(0, $userId),
            'pageId' => max(0, $pageId),
            'writtenBytes' => 0,
            'maxBytes' => 40 * 1024 * 1024,
            'convertedImages' => 0,
            'skippedImages' => 0,
            'failedImages' => 0,
            'skippedBytes' => 0,
        ];

        $data = $this->normalizeEditorJsonImageValue($data, $context);
        $this->logEditorInlineImageNormalization($context);

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $json;
    }

    private function normalizeEditorJsonImageValue(mixed $value, array &$context): mixed
    {
        if (is_string($value)) {
            return $this->storeEditorInlineImage($value, $context);
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeEditorJsonImageValue($item, $context);
        }

        return $value;
    }

    private function storeEditorInlineImage(string $value, array &$context): string
    {
        if (! str_starts_with($value, 'data:image/')) {
            return $value;
        }

        $commaPosition = strpos($value, ',');
        if ($commaPosition === false) {
            $context['skippedImages']++;
            return $value;
        }

        $header = substr($value, 0, $commaPosition);
        if (! preg_match('#^data:(image/(?:png|jpe?g|webp|gif));base64$#i', $header, $matches)) {
            $context['skippedImages']++;
            return $value;
        }

        $estimatedBytes = $this->estimateBase64DecodedBytes($value, $commaPosition + 1);
        if ($estimatedBytes > 8 * 1024 * 1024 || ((int) $context['writtenBytes'] + $estimatedBytes) > (int) $context['maxBytes']) {
            $context['skippedImages']++;
            $context['skippedBytes'] += max(0, $estimatedBytes);
            log_message('warning', 'Editor inline image skipped before decode because it is too large. page_id={page} estimated_bytes={bytes}', [
                'page' => $context['pageId'],
                'bytes' => $estimatedBytes,
            ]);
            return $value;
        }

        $base64 = substr($value, $commaPosition + 1);
        if (! preg_match('#^[A-Za-z0-9+/=\r\n]+$#', $base64)) {
            $context['skippedImages']++;
            return $value;
        }

        if (str_contains($base64, "\r") || str_contains($base64, "\n")) {
            $base64 = str_replace(["\r", "\n"], '', $base64);
        }

        $binary = base64_decode($base64, true);
        if ($binary === false || $binary === '') {
            $context['failedImages']++;
            return $value;
        }

        $length = strlen($binary);
        if ($length > 8 * 1024 * 1024 || ($context['writtenBytes'] + $length) > $context['maxBytes']) {
            $context['skippedImages']++;
            $context['skippedBytes'] += $length;
            log_message('warning', 'Editor inline image skipped because it is too large. page_id={page}', [
                'page' => $context['pageId'],
            ]);
            return $value;
        }

        $imageInfo = @getimagesizefromstring($binary);
        if (! is_array($imageInfo) || empty($imageInfo['mime'])) {
            $context['failedImages']++;
            return $value;
        }

        $mime = strtolower((string) $imageInfo['mime']);
        $extensions = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (! isset($extensions[$mime])) {
            $context['skippedImages']++;
            return $value;
        }

        $userFolder = $context['userId'] > 0 ? (string) $context['userId'] : 'guest';
        $pageFolder = $context['pageId'] > 0 ? (string) $context['pageId'] : 'page';
        $relativeDir = 'uploads/editor-inline/' . $userFolder . '/' . $pageFolder;
        $uploadPath = FCPATH . $relativeDir;

        if (! is_dir($uploadPath) && ! @mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            log_message('error', 'Editor inline image directory cannot be created: {path}', ['path' => $uploadPath]);
            $context['failedImages']++;
            return $value;
        }

        if (! is_writable($uploadPath)) {
            log_message('error', 'Editor inline image directory is not writable: {path}', ['path' => $uploadPath]);
            $context['failedImages']++;
            return $value;
        }

        $hash = hash('sha256', $binary);
        $fileName = substr($hash, 0, 24) . '.' . $extensions[$mime];
        $absolutePath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        if (! is_file($absolutePath)) {
            if (@file_put_contents($absolutePath, $binary, LOCK_EX) === false) {
                log_message('error', 'Editor inline image cannot be written: {path}', ['path' => $absolutePath]);
                $context['failedImages']++;
                return $value;
            }
        }

        $context['writtenBytes'] += $length;
        $context['convertedImages']++;

        return base_url($relativeDir . '/' . $fileName);
    }

    private function estimateBase64DecodedBytes(string $value, int $offset): int
    {
        $length = max(0, strlen($value) - $offset);
        if ($length === 0) {
            return 0;
        }

        $padding = 0;
        for ($index = strlen($value) - 1; $index >= $offset && $padding < 2; $index--) {
            $char = $value[$index];
            if ($char === "\r" || $char === "\n") {
                continue;
            }

            if ($char === '=') {
                $padding++;
                continue;
            }

            break;
        }

        return max(0, (int) floor($length * 3 / 4) - $padding);
    }

    private function logEditorInlineImageNormalization(array $context): void
    {
        $converted = (int) ($context['convertedImages'] ?? 0);
        $skipped = (int) ($context['skippedImages'] ?? 0);
        $failed = (int) ($context['failedImages'] ?? 0);

        if ($converted === 0 && $skipped === 0 && $failed === 0) {
            return;
        }

        log_message('info', 'Editor inline image normalization completed. page_id={page} converted={converted} skipped={skipped} failed={failed} written_bytes={written} skipped_bytes={skipped_bytes}', [
            'page' => (int) ($context['pageId'] ?? 0),
            'converted' => $converted,
            'skipped' => $skipped,
            'failed' => $failed,
            'written' => (int) ($context['writtenBytes'] ?? 0),
            'skipped_bytes' => (int) ($context['skippedBytes'] ?? 0),
        ]);
    }

    private function storeEditorThumbnailImage(string $value, array $page): string
    {
        if ($value === '' || ! str_starts_with($value, 'data:image/')) {
            return '';
        }

        if (! preg_match('#^data:(image/(?:png|jpe?g|webp));base64,([A-Za-z0-9+/=\r\n]+)$#i', $value, $matches)) {
            return '';
        }

        $binary = base64_decode(str_replace(["\r", "\n"], '', $matches[2]), true);
        if ($binary === false || $binary === '' || strlen($binary) > 2 * 1024 * 1024) {
            return '';
        }

        $imageInfo = @getimagesizefromstring($binary);
        if (! is_array($imageInfo) || empty($imageInfo['mime'])) {
            return '';
        }

        $mime = strtolower((string) $imageInfo['mime']);
        $extensions = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/webp' => 'webp',
        ];

        if (! isset($extensions[$mime])) {
            return '';
        }

        $userId = (int) ($page['user_id'] ?? session()->get('userId') ?? 0);
        $pageId = (int) ($page['id'] ?? 0);
        $userFolder = $userId > 0 ? (string) $userId : 'guest';
        $pageFolder = $pageId > 0 ? (string) $pageId : 'page';
        $relativeDir = 'uploads/editor-thumbnails/' . $userFolder . '/' . $pageFolder;
        $uploadPath = FCPATH . $relativeDir;

        if (! is_dir($uploadPath) && ! @mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            log_message('error', 'Editor thumbnail directory cannot be created: {path}', ['path' => $uploadPath]);
            return '';
        }

        if (! is_writable($uploadPath)) {
            log_message('error', 'Editor thumbnail directory is not writable: {path}', ['path' => $uploadPath]);
            return '';
        }

        $version = substr(hash('sha256', $binary), 0, 12);
        $fileName = 'thumb-dashboard.' . $extensions[$mime];
        $absolutePath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        if (@file_put_contents($absolutePath, $binary, LOCK_EX) === false) {
            log_message('error', 'Editor thumbnail cannot be written: {path}', ['path' => $absolutePath]);
            return '';
        }

        $this->cleanupEditorDashboardThumbnails($uploadPath, $fileName);

        return base_url($relativeDir . '/' . $fileName) . '?v=' . $version;
    }

    private function cleanupEditorDashboardThumbnails(string $uploadPath, string $keepFileName): void
    {
        $realUploadPath = realpath($uploadPath);
        $realBasePath = realpath(FCPATH . 'uploads/editor-thumbnails');

        if ($realUploadPath === false || $realBasePath === false || ! str_starts_with($realUploadPath, $realBasePath)) {
            return;
        }

        foreach (glob(rtrim($realUploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'thumb-*') ?: [] as $path) {
            if (! is_file($path) || basename($path) === $keepFileName) {
                continue;
            }

            @unlink($path);
        }
    }

    private function requestPayload(): array
    {
        $contentType = strtolower((string) $this->request->getHeaderLine('Content-Type'));
        if (str_contains($contentType, 'multipart/form-data') || str_contains($contentType, 'application/x-www-form-urlencoded')) {
            return $this->request->getPost();
        }

        try {
            $payload = $this->request->getJSON(true);
        } catch (Throwable) {
            $payload = null;
        }

        return is_array($payload) ? $payload : $this->request->getPost();
    }

    private function isManualPublishedOgImage(string $url): bool
    {
        return str_contains($url, '/uploads/og-previews/') || str_contains($url, 'uploads/og-previews/');
    }

    /**
     * @return array{ok: bool, url?: string, message?: string}
     */
    private function storePublishedOgImageUploadSafely(array $page): array
    {
        try {
            return $this->storePublishedOgImageUpload($page);
        } catch (Throwable $exception) {
            log_message('error', 'Published OG image upload failed. page_id={page_id} user_id={user_id} message={message}', [
                'page_id' => (string) ($page['id'] ?? ''),
                'user_id' => (string) ($page['user_id'] ?? ''),
                'message' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => 'Gambar preview link belum bisa diproses. Silakan hapus pilihan gambar atau coba file lain.',
            ];
        }
    }

    /**
     * @return array{ok: bool, url?: string, message?: string}
     */
    private function storePublishedOgImageUpload(array $page): array
    {
        $files = $this->request->getFiles();
        if (! isset($files['og_image_file'])) {
            return ['ok' => true, 'url' => ''];
        }

        $file = $this->request->getFile('og_image_file');
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'url' => ''];
        }

        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagejpeg')) {
            return ['ok' => false, 'message' => 'Server belum siap memproses gambar preview link.'];
        }

        if (! $file->isValid()) {
            return ['ok' => false, 'message' => 'Upload gambar preview gagal. Silakan pilih file lain.'];
        }

        if ($file->getSize() > 1024 * 1024) {
            return ['ok' => false, 'message' => 'Ukuran gambar preview maksimal 1 MB.'];
        }

        $clientExtension = strtolower((string) $file->getClientExtension());
        if (! in_array($clientExtension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return ['ok' => false, 'message' => 'Format gambar preview harus JPG, PNG, atau WEBP.'];
        }

        $tempPath = $file->getTempName();
        $imageInfo = @getimagesize($tempPath);
        if (! is_array($imageInfo) || empty($imageInfo['mime'])) {
            return ['ok' => false, 'message' => 'Gambar preview tidak bisa dibaca.'];
        }

        $mime = strtolower((string) $imageInfo['mime']);
        if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            return ['ok' => false, 'message' => 'Format gambar preview harus JPG, PNG, atau WEBP.'];
        }

        $sourceWidth = (int) ($imageInfo[0] ?? 0);
        $sourceHeight = (int) ($imageInfo[1] ?? 0);
        if ($sourceWidth < 600 || $sourceHeight < 315) {
            return ['ok' => false, 'message' => 'Dimensi gambar preview minimal 600 x 315 px.'];
        }
        if ($sourceWidth * $sourceHeight > 25000000) {
            return ['ok' => false, 'message' => 'Dimensi gambar preview terlalu besar. Gunakan gambar yang lebih ringan.'];
        }

        $source = $this->gdImageFromPath($tempPath, $mime);
        if (! $source) {
            return ['ok' => false, 'message' => 'Server belum bisa memproses gambar preview ini. Gunakan JPG atau PNG.'];
        }

        $targetWidth = 1200;
        $targetHeight = 630;
        $targetRatio = $targetWidth / $targetHeight;
        $sourceRatio = $sourceWidth / max(1, $sourceHeight);

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($cropHeight * $targetRatio);
            $cropX = (int) max(0, floor(($sourceWidth - $cropWidth) / 2));
            $cropY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($cropWidth / $targetRatio);
            $cropX = 0;
            $cropY = (int) max(0, floor(($sourceHeight - $cropHeight) / 2));
        }

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        if (! $target) {
            imagedestroy($source);
            return ['ok' => false, 'message' => 'Gambar preview belum bisa diproses. Silakan coba lagi.'];
        }

        $white = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $white);
        imagealphablending($target, true);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $cropX,
            $cropY,
            $targetWidth,
            $targetHeight,
            $cropWidth,
            $cropHeight
        );

        if (function_exists('imagefilter')) {
            for ($i = 0; $i < 6; $i++) {
                imagefilter($target, IMG_FILTER_GAUSSIAN_BLUR);
            }
            imagefilter($target, IMG_FILTER_BRIGHTNESS, -18);
        }

        $containScale = min($targetWidth / max(1, $sourceWidth), $targetHeight / max(1, $sourceHeight));
        $drawWidth = max(1, (int) round($sourceWidth * $containScale));
        $drawHeight = max(1, (int) round($sourceHeight * $containScale));
        $drawX = (int) floor(($targetWidth - $drawWidth) / 2);
        $drawY = (int) floor(($targetHeight - $drawHeight) / 2);

        imagecopyresampled(
            $target,
            $source,
            $drawX,
            $drawY,
            0,
            0,
            $drawWidth,
            $drawHeight,
            $sourceWidth,
            $sourceHeight
        );
        imagedestroy($source);

        $userId = (int) ($page['user_id'] ?? session()->get('userId') ?? 0);
        $pageId = (int) ($page['id'] ?? 0);
        $userFolder = $userId > 0 ? (string) $userId : 'guest';
        $pageFolder = $pageId > 0 ? (string) $pageId : 'page';
        $relativeDir = 'uploads/og-previews/' . $userFolder . '/' . $pageFolder;
        $uploadPath = FCPATH . $relativeDir;

        if (! is_dir($uploadPath) && ! @mkdir($uploadPath, 0755, true) && ! is_dir($uploadPath)) {
            imagedestroy($target);
            log_message('error', 'OG preview directory cannot be created: {path}', ['path' => $uploadPath]);
            return ['ok' => false, 'message' => 'Folder preview link belum siap. Silakan coba lagi.'];
        }

        if (! is_writable($uploadPath)) {
            imagedestroy($target);
            log_message('error', 'OG preview directory is not writable: {path}', ['path' => $uploadPath]);
            return ['ok' => false, 'message' => 'Folder preview link tidak bisa ditulis.'];
        }

        $sourceHash = substr(hash_file('sha256', $tempPath) ?: hash('sha256', uniqid('', true)), 0, 12);
        $fileName = 'og-' . date('YmdHis') . '-' . $sourceHash . '.jpg';
        $absolutePath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
        $saved = false;
        foreach ([82, 76, 70] as $quality) {
            $saved = imagejpeg($target, $absolutePath, $quality);
            if ($saved && is_file($absolutePath) && filesize($absolutePath) <= 520 * 1024) {
                break;
            }
        }
        imagedestroy($target);

        if (! $saved || ! is_file($absolutePath)) {
            return ['ok' => false, 'message' => 'Gambar preview belum bisa disimpan. Silakan coba lagi.'];
        }

        $this->cleanupPublishedOgImages($uploadPath, $fileName);
        $version = substr(hash_file('sha256', $absolutePath) ?: $sourceHash, 0, 12);

        return [
            'ok' => true,
            'url' => base_url($relativeDir . '/' . $fileName) . '?v=' . $version,
        ];
    }

    private function cleanupPublishedOgImages(string $uploadPath, string $keepFileName): void
    {
        $realUploadPath = realpath($uploadPath);
        $realBasePath = realpath(FCPATH . 'uploads/og-previews');

        if ($realUploadPath === false || $realBasePath === false || ! str_starts_with($realUploadPath, $realBasePath)) {
            return;
        }

        foreach (glob(rtrim($realUploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'og-*') ?: [] as $path) {
            if (! is_file($path) || basename($path) === $keepFileName) {
                continue;
            }

            @unlink($path);
        }
    }

    private function sanitizeFabricData(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (in_array(($value['type'] ?? null), ['i-text', 'textbox', 'text'], true)) {
            unset($value['clipPath']);
        }

        foreach ($value as $key => $item) {
            if ($key === 'textBaseline' && $item === 'alphabetical') {
                $value[$key] = 'alphabetic';
                continue;
            }

            $value[$key] = $this->sanitizeFabricData($item);
        }

        return $value;
    }

    private function filterMediaColumns(array $data): array
    {
        $fields = Database::connect()->getFieldNames('media');

        return array_intersect_key($data, array_flip($fields));
    }

    private function optimizeEditorImage(string $uploadPath, string $fileName, string $mimeType): array
    {
        $path = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
        $fallback = [
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'file_size' => is_file($path) ? (int) filesize($path) : 0,
        ];

        if (! is_file($path) || ! function_exists('imagewebp') || $mimeType === 'image/gif') {
            return $fallback;
        }

        $imageInfo = @getimagesize($path);
        if (! is_array($imageInfo)) {
            return $fallback;
        }

        [$width, $height] = $imageInfo;
        $source = match ($mimeType) {
            'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $source) {
            return $fallback;
        }

        $maxDimension = 1600;
        $scale = min(1, $maxDimension / max(1, (int) $width), $maxDimension / max(1, (int) $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, (int) $width, (int) $height);

        $webpName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
        $webpPath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $webpName;
        $saved = imagewebp($target, $webpPath, 78);

        imagedestroy($source);
        imagedestroy($target);

        if (! $saved || ! is_file($webpPath)) {
            return $fallback;
        }

        if ($webpPath !== $path && is_file($path)) {
            @unlink($path);
        }

        return [
            'file_name' => $webpName,
            'mime_type' => 'image/webp',
            'file_size' => (int) filesize($webpPath),
        ];
    }

    private function normalizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/\s+/', '-', $slug) ?? $slug;
        $slug = preg_replace('/-+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }

    private function isValidSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
    }

    private function isSlugAvailable(string $slug, int $ignorePageId = 0): bool
    {
        $builder = Database::connect()
            ->table($this->table)
            ->where('slug', $slug);

        if ($ignorePageId > 0) {
            $builder->where('id !=', $ignorePageId);
        }

        return $builder->countAllResults() === 0;
    }

    private function templateCategories(): array
    {
        $db = Database::connect();
        if (! $db->tableExists('categories')) {
            return [];
        }

        $builder = $db->table('categories');
        $fields = $db->getFieldNames('categories');

        if (class_exists(CategoryModel::class)) {
            return (new CategoryModel())->templateOptions();
        }

        if (in_array('sort_order', $fields, true)) {
            $builder->orderBy('sort_order', 'ASC');
        }

        return $builder
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function templateSubcategories(): array
    {
        $db = Database::connect();
        if (! $db->tableExists('template_subcategories')) {
            return [];
        }

        try {
            return (new TemplateSubcategoryModel())->activeWithCategoryList();
        } catch (\Throwable) {
            return [];
        }
    }

    private function editorTemplates(): array
    {
        $db = Database::connect();
        if (! $db->tableExists('templates')) {
            return [];
        }

        $categoryMap = [];
        foreach ((new CategoryModel())->templateOptions() as $category) {
            $categoryMap[(int) ($category['id'] ?? 0)] = (string) ($category['name'] ?? 'Kategori');
        }

        return array_map(static function (array $template) use ($categoryMap): array {
            $categoryId = (int) ($template['category_id'] ?? 0);

            return [
                'id' => (int) ($template['id'] ?? 0),
                'name' => (string) ($template['name'] ?? 'Template'),
                'thumbnail' => (string) ($template['thumbnail'] ?? ''),
                'category_id' => $categoryId,
                'category_name' => $categoryMap[$categoryId] ?? 'Lainnya',
                'is_premium' => (int) ($template['is_premium'] ?? 0),
            ];
        }, (new TemplateModel())->getActiveTemplates());
    }

    private function canUpdateSavedTemplate(int $userId, string $role, bool $isActiveCreator): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $role = strtolower(trim($role));
        return in_array($role, ['superadmin', 'content_admin'], true) || $isActiveCreator;
    }

    private function saveTemplateTargets(int $userId, string $role, bool $isActiveCreator): array
    {
        if (! $this->canUpdateSavedTemplate($userId, $role, $isActiveCreator)) {
            return [];
        }

        $db = Database::connect();
        if (! $db->tableExists('templates')) {
            return [];
        }

        $fields = $db->getFieldNames('templates');
        $select = array_values(array_intersect(['id', 'name', 'slug', 'status', 'review_status', 'public_status', 'updated_at'], $fields));
        if ($select === []) {
            return [];
        }

        $builder = $db->table('templates')->select(implode(', ', $select));
        $role = strtolower(trim($role));

        if ($isActiveCreator && ! in_array($role, ['superadmin', 'content_admin'], true)) {
            if (! in_array('owner_user_id', $fields, true)) {
                return [];
            }

            $builder->where('owner_user_id', $userId);
            if (in_array('review_status', $fields, true)) {
                $builder->whereIn('review_status', ['pending', 'rejected']);
            }
        }

        if (in_array('updated_at', $fields, true)) {
            $builder->orderBy('updated_at', 'DESC');
        }

        return array_map(static function (array $template): array {
            return [
                'id' => (int) ($template['id'] ?? 0),
                'name' => (string) ($template['name'] ?? 'Template'),
                'slug' => (string) ($template['slug'] ?? ''),
                'status' => (string) ($template['status'] ?? ''),
                'review_status' => (string) ($template['review_status'] ?? ''),
                'public_status' => (string) ($template['public_status'] ?? ''),
                'updated_at' => (string) ($template['updated_at'] ?? ''),
            ];
        }, $builder->limit(80)->get()->getResultArray());
    }

    private function userHasActiveMembership(int $userId): bool
    {
        if ($this->isAdminRole($this->currentUserRole($userId))) {
            return true;
        }

        if ($userId <= 0) {
            return false;
        }

        return (new UserSubscriptionModel())->activeWithPlanByUser($userId) !== null;
    }

    private function currentUserRole(int $userId): string
    {
        $role = strtolower(trim((string) (session()->get('userRole') ?? '')));
        if ($role !== '') {
            return $role;
        }

        if ($userId <= 0) {
            return 'guest';
        }

        $user = (new UserModel())->find($userId);
        $role = strtolower(trim((string) ($user['role'] ?? 'user')));
        session()->set('userRole', $role);

        return $role;
    }

    private function isAdminRole(string $role): bool
    {
        return in_array(strtolower(trim($role)), ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin'], true);
    }

    private function canManageEditorAssets(int $userId): bool
    {
        return in_array($this->currentUserRole($userId), ['superadmin', 'admin', 'content_admin'], true);
    }

    private function editorAssetUploadToken(int $userId): string
    {
        $role = $this->currentUserRole($userId);
        $payload = [
            'uid' => $userId,
            'role' => $role,
            'exp' => time() + 7200,
        ];
        $encoded = rtrim(strtr(base64_encode(json_encode($payload, JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $encoded, $this->editorAssetUploadSecret());

        return $encoded . '.' . $signature;
    }

    private function editorAssetUploadSecret(): string
    {
        return (string) (env('encryption.key') ?: FCPATH);
    }

    private function publishLimitReached(int $userId, ?array $subscription, array $page): bool
    {
        if ($this->isAdminRole($this->currentUserRole($userId))) {
            return false;
        }

        $limit = $this->maxPublishedLinksForPage($subscription, $page);
        if ($subscription !== null && $limit <= 0) {
            return false;
        }
        if ($limit <= 0) {
            return true;
        }

        if ((string) ($page['status'] ?? '') === 'published') {
            return false;
        }

        if ($subscription === null) {
            return $this->activeFreePublishedLinksCount($userId) >= $limit;
        }

        return $this->publishedLinksCount($userId) >= $limit;
    }

    private function publishedLinksCount(int $userId): int
    {
        return Database::connect()
            ->table($this->table)
            ->where('user_id', $userId)
            ->where('status', 'published')
            ->countAllResults();
    }

    private function activeFreePublishedLinksCount(int $userId): int
    {
        $db = Database::connect();
        return $db->table($this->table)
            ->where($this->table . '.user_id', $userId)
            ->where($this->table . '.status', 'published')
            ->groupStart()
                ->where($this->table . '.published_at IS NULL', null, false)
                ->orWhere($this->table . '.published_at >=', date('Y-m-d H:i:s', strtotime('-1 month')))
            ->groupEnd()
            ->countAllResults();
    }

    /**
     * @return array{allowed: bool, published_at?: string, expires_at?: string, message?: string}
     */
    private function reserveFreePublishEntitlement(int $userId, array $page, string $now): array
    {
        if ($userId <= 0) {
            return [
                'allowed' => false,
                'message' => 'Silakan login untuk publish undangan free.',
            ];
        }

        $db = Database::connect();
        if (! $db->tableExists('free_publish_entitlements')) {
            return [
                'allowed' => true,
                'published_at' => $this->existingPublishedAtOrNow($page, $now),
                'expires_at' => $this->freeExpiresAt($this->existingPublishedAtOrNow($page, $now)),
            ];
        }

        $model = new FreePublishEntitlementModel();
        $entitlement = $model->where('user_id', $userId)->first();
        $pageId = (int) ($page['id'] ?? 0);
        $templateId = (int) ($page['template_id'] ?? 0) ?: null;

        if ($entitlement !== null) {
            $expiresAt = (string) ($entitlement['expires_at'] ?? '');
            if ($this->dateIsPast($expiresAt, $now)) {
                $model->update((int) $entitlement['id'], [
                    'status' => 'expired',
                    'updated_at' => $now,
                ]);

                return [
                    'allowed' => false,
                    'published_at' => (string) ($entitlement['first_published_at'] ?? ''),
                    'expires_at' => $expiresAt,
                    'message' => 'Masa aktif publish free sudah habis. Upgrade paket untuk publish lagi.',
                ];
            }

            $firstPublishedAt = (string) ($entitlement['first_published_at'] ?? '');
            if ($firstPublishedAt === '') {
                $firstPublishedAt = $this->existingPublishedAtOrNow($page, $now);
            }

            $model->update((int) $entitlement['id'], [
                'landing_page_id' => $pageId > 0 ? $pageId : null,
                'template_id' => $templateId,
                'status' => 'active',
                'updated_at' => $now,
            ]);

            return [
                'allowed' => true,
                'published_at' => $firstPublishedAt,
                'expires_at' => $expiresAt,
            ];
        }

        $firstPublishedAt = $this->existingPublishedAtOrNow($page, $now);
        $expiresAt = $this->freeExpiresAt($firstPublishedAt);
        if ($this->dateIsPast($expiresAt, $now)) {
            $model->insert([
                'user_id' => $userId,
                'landing_page_id' => $pageId > 0 ? $pageId : null,
                'template_id' => $templateId,
                'first_published_at' => $firstPublishedAt,
                'expires_at' => $expiresAt,
                'status' => 'expired',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return [
                'allowed' => false,
                'published_at' => $firstPublishedAt,
                'expires_at' => $expiresAt,
                'message' => 'Masa aktif publish free sudah habis. Upgrade paket untuk publish lagi.',
            ];
        }

        $model->insert([
            'user_id' => $userId,
            'landing_page_id' => $pageId > 0 ? $pageId : null,
            'template_id' => $templateId,
            'first_published_at' => $firstPublishedAt,
            'expires_at' => $expiresAt,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'allowed' => true,
            'published_at' => $firstPublishedAt,
            'expires_at' => $expiresAt,
        ];
    }

    private function existingPublishedAtOrNow(array $page, string $now): string
    {
        $publishedAt = (string) ($page['published_at'] ?? '');

        return strtotime($publishedAt) !== false ? $publishedAt : $now;
    }

    private function freeExpiresAt(string $publishedAt): string
    {
        $timestamp = strtotime($publishedAt);
        if ($timestamp === false) {
            $timestamp = time();
        }

        return date('Y-m-d H:i:s', strtotime('+1 month', $timestamp));
    }

    private function dateIsPast(string $dateTime, string $now): bool
    {
        $timestamp = strtotime($dateTime);
        $nowTimestamp = strtotime($now);

        return $timestamp !== false && $nowTimestamp !== false && $timestamp < $nowTimestamp;
    }

    private function maxPublishedLinksFromSubscription(array $subscription): int
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

    private function pageUsesFreeTemplate(array $page): bool
    {
        $templateId = (int) ($page['template_id'] ?? 0);
        if ($templateId <= 0) {
            return false;
        }

        $db = Database::connect();
        if (! $db->tableExists('templates') || ! in_array('is_premium', $db->getFieldNames('templates'), true)) {
            return false;
        }

        $template = $db->table('templates')
            ->select('is_premium')
            ->where('id', $templateId)
            ->get()
            ->getRowArray();

        return $template !== null && (int) ($template['is_premium'] ?? 1) === 0;
    }

    private function canPublishPage(int $userId, array $page, ?array $subscription): bool
    {
        if ($userId <= 0) {
            return false;
        }

        if ($this->isAdminRole($this->currentUserRole($userId))) {
            return true;
        }

        if ((new SellerTemplateService())->isActiveCreator($userId)) {
            return false;
        }

        return true;
    }

    private function canUseReferenceMapper(bool $isAdmin, bool $isActiveCreator, ?array $subscription): bool
    {
        return $isAdmin || $isActiveCreator || $subscription !== null;
    }

    private function canUseOcrTextDetection(bool $isAdmin, bool $isActiveCreator, ?array $subscription): bool
    {
        return $isAdmin || $subscription !== null;
    }

    private function allowOcrTextRequest(int $userId): bool
    {
        $key = 'aa_ocr_text_last_request_' . max(0, $userId);
        $last = (int) (session()->get($key) ?? 0);
        $now = time();
        if ($last > 0 && ($now - $last) < 8) {
            return false;
        }

        session()->set($key, $now);

        return true;
    }

    private function resolveOcrTextAsset(string $imageSrc, int $ownerUserId): ?array
    {
        $relativePath = $this->publicRelativePathFromUrl($imageSrc);
        if ($relativePath === '') {
            return null;
        }

        $currentUserId = (int) (session()->get('userId') ?? 0);
        $allowedPrefixes = [
            'uploads/editor/' . max(0, $ownerUserId) . '/',
            'uploads/media/' . max(0, $ownerUserId) . '/',
            'uploads/magic-layer-temp/' . max(0, $ownerUserId) . '/',
            'uploads/magic-layer-temp/' . max(0, $currentUserId) . '/',
            'uploads/editor-assets/',
            'assets/img/',
        ];

        $allowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($relativePath, $prefix)) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            return null;
        }

        $absolutePath = realpath(FCPATH . $relativePath);
        $publicPath = realpath(FCPATH);
        if ($absolutePath === false || $publicPath === false || ! str_starts_with($absolutePath, $publicPath)) {
            return null;
        }

        if (! is_file($absolutePath) || filesize($absolutePath) === false || filesize($absolutePath) > 8 * 1024 * 1024) {
            return null;
        }

        $info = @getimagesize($absolutePath);
        if (! is_array($info) || empty($info['mime'])) {
            return null;
        }

        $mime = strtolower((string) $info['mime']);
        if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $width = (int) ($info[0] ?? 0);
        $height = (int) ($info[1] ?? 0);
        if ($width <= 0 || $height <= 0 || $width > 6000 || $height > 6000 || ($width * $height) > 24000000) {
            return null;
        }

        return [
            'path' => $absolutePath,
            'relative_path' => $relativePath,
            'mime' => $mime,
            'width' => $width,
            'height' => $height,
        ];
    }

    private function attachGeminiDecorationAssets(array $blueprint, array $asset, int $userId, int $pageId): array
    {
        if (empty($blueprint['decorations']) || ! extension_loaded('gd')) {
            return $blueprint;
        }
        $segmentationModel = trim((string) env('GEMINI_SEGMENTATION_MODEL', 'gemini-2.5-flash'));

        $source = $this->gdImageFromPath($asset['path'], $asset['mime']);
        if (! $source) {
            return $blueprint;
        }

        $targetDir = FCPATH . 'uploads/editor-ai/' . max(0, $userId) . '/' . max(0, $pageId);
        if (! is_dir($targetDir) && ! @mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            imagedestroy($source);
            return $blueprint;
        }

        $assetWidth = max(1, (int) ($asset['width'] ?? imagesx($source)));
        $assetHeight = max(1, (int) ($asset['height'] ?? imagesy($source)));
        $decorations = [];

        foreach ((array) $blueprint['decorations'] as $index => $decoration) {
            if (! is_array($decoration) || (float) ($decoration['confidence'] ?? 0) < 0.72) {
                continue;
            }

            $crop = $this->clampedCropBox($decoration, $assetWidth, $assetHeight);
            if ($crop === null) {
                continue;
            }

            $textOverlap = $this->decorationTextOverlapStats($crop, (array) ($blueprint['blocks'] ?? []), $assetWidth, $assetHeight);
            $isFullCanvasCrop = ((float) $crop['width'] / $assetWidth) > 0.88
                && ((float) $crop['height'] / $assetHeight) > 0.88;
            $containsText = (bool) ($decoration['containsText'] ?? false);
            $extractable = array_key_exists('extractable', $decoration) ? (bool) $decoration['extractable'] : true;
            $needsCarefulExtraction = (bool) ($decoration['needsSegmentation'] ?? false)
                || (bool) ($decoration['needsBackgroundRemoval'] ?? false);

            if (! $extractable || $containsText || ($isFullCanvasCrop && $textOverlap['hasText'])) {
                continue;
            }
            if ($textOverlap['blocked'] && (! $needsCarefulExtraction || $textOverlap['cropRatio'] > 0.06 || $textOverlap['textRatio'] > 0.40)) {
                continue;
            }

            $cropped = imagecrop($source, $crop);
            if (! $cropped) {
                continue;
            }

            imagesavealpha($cropped, true);
            $backgroundRemoved = $segmentationModel !== '' && $this->removeFlatBackgroundFromCrop($cropped);
            $fileName = 'decor-' . date('YmdHis') . '-' . $index . '-' . bin2hex(random_bytes(4)) . '.webp';
            $absolute = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            $written = function_exists('imagewebp') ? imagewebp($cropped, $absolute, 88) : false;
            imagedestroy($cropped);

            if (! $written || ! is_file($absolute)) {
                continue;
            }

            $relative = 'uploads/editor-ai/' . max(0, $userId) . '/' . max(0, $pageId) . '/' . $fileName;
            $decoration['assetSrc'] = base_url($relative);
            $decoration['assetName'] = $fileName;
            $decoration['mime'] = 'image/webp';
            $decoration['backgroundRemoved'] = $backgroundRemoved;
            if ($backgroundRemoved) {
                $decoration['needsBackgroundRemoval'] = false;
            }
            $decorations[] = $decoration;
        }

        imagedestroy($source);
        $blueprint['decorations'] = $decorations;

        return $blueprint;
    }

    private function attachGeminiBackgroundColor(array $blueprint, array $asset): array
    {
        if (! empty($blueprint['backgroundColor']) && is_string($blueprint['backgroundColor'])) {
            return $blueprint;
        }

        $color = $this->dominantEdgeColor((string) $asset['path'], (string) $asset['mime']);
        if ($color !== '') {
            $blueprint['backgroundColor'] = $color;
        }

        return $blueprint;
    }

    private function attachGeminiFrameAssets(array $blueprint, array $asset, int $userId, int $pageId): array
    {
        if (empty($blueprint['frames']) || ! extension_loaded('gd')) {
            return $blueprint;
        }

        $blueprint['frames'] = $this->attachCroppedAssetsToBlueprintItems(
            (array) $blueprint['frames'],
            $asset,
            $userId,
            $pageId,
            'frame',
            0.70,
            90
        );

        return $blueprint;
    }

    private function attachGeminiCanvasOverlay(array $blueprint, array $asset): array
    {
        if (! in_array((string) ($asset['mime'] ?? ''), ['image/png', 'image/webp'], true)) {
            return $blueprint;
        }

        $hasAlpha = $this->imageHasUsefulAlpha((string) $asset['path'], (string) $asset['mime']);
        if (! $hasAlpha) {
            return $blueprint;
        }

        $textAreaRatio = $this->blueprintTextAreaRatio((array) ($blueprint['blocks'] ?? []), (int) ($asset['width'] ?? 0), (int) ($asset['height'] ?? 0));
        $aiRequestedOverlay = (bool) ($blueprint['canvasOverlay']['enabled'] ?? false);
        if (! $aiRequestedOverlay && $textAreaRatio > 0.015) {
            return $blueprint;
        }

        $blueprint['canvasOverlay'] = [
            'enabled' => true,
            'confidence' => max(0.84, (float) ($blueprint['canvasOverlay']['confidence'] ?? 0)),
            'assetSrc' => base_url($asset['relative_path']),
            'assetName' => basename((string) $asset['relative_path']),
            'mime' => (string) $asset['mime'],
            'x' => 0,
            'y' => 0,
            'width' => (int) $asset['width'],
            'height' => (int) $asset['height'],
            'needsReview' => $textAreaRatio > 0,
        ];

        return $blueprint;
    }

    private function attachGeminiPhotoAssets(array $blueprint, array $asset, int $userId, int $pageId): array
    {
        if (empty($blueprint['photos']) || ! extension_loaded('gd')) {
            $blueprint['photos'] = [];
            return $blueprint;
        }

        $blueprint['photos'] = $this->attachCroppedAssetsToBlueprintItems(
            (array) $blueprint['photos'],
            $asset,
            $userId,
            $pageId,
            'photo',
            0.70,
            90
        );

        return $blueprint;
    }

    private function attachCroppedAssetsToBlueprintItems(array $items, array $asset, int $userId, int $pageId, string $prefix, float $minConfidence, int $quality): array
    {
        $source = $this->gdImageFromPath($asset['path'], $asset['mime']);
        if (! $source) {
            return [];
        }

        $targetDir = FCPATH . 'uploads/editor-ai/' . max(0, $userId) . '/' . max(0, $pageId);
        if (! is_dir($targetDir) && ! @mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            imagedestroy($source);
            return [];
        }

        $assetWidth = max(1, (int) ($asset['width'] ?? imagesx($source)));
        $assetHeight = max(1, (int) ($asset['height'] ?? imagesy($source)));
        $output = [];

        foreach ($items as $index => $item) {
            if (! is_array($item) || (float) ($item['confidence'] ?? 0) < $minConfidence) {
                continue;
            }

            $crop = $this->clampedCropBox($item, $assetWidth, $assetHeight);
            if ($crop === null || ((float) $crop['width'] * (float) $crop['height']) < 900) {
                continue;
            }

            $cropped = imagecrop($source, $crop);
            if (! $cropped) {
                continue;
            }

            imagesavealpha($cropped, true);
            $fileName = preg_replace('/[^a-z0-9_-]+/i', '-', $prefix) . '-' . date('YmdHis') . '-' . $index . '-' . bin2hex(random_bytes(4)) . '.webp';
            $absolute = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            $written = function_exists('imagewebp') ? imagewebp($cropped, $absolute, $quality) : false;
            imagedestroy($cropped);

            if (! $written || ! is_file($absolute)) {
                continue;
            }

            $relative = 'uploads/editor-ai/' . max(0, $userId) . '/' . max(0, $pageId) . '/' . $fileName;
            $item['assetSrc'] = base_url($relative);
            $item['assetName'] = $fileName;
            $item['mime'] = 'image/webp';
            $output[] = $item;
        }

        imagedestroy($source);

        return $output;
    }

    private function decorationTextOverlapStats(array $crop, array $blocks, int $imageWidth, int $imageHeight): array
    {
        $cropArea = max(1, (float) $crop['width'] * (float) $crop['height']);
        $maxTextRatio = 0.0;
        $maxCropRatio = 0.0;
        $blocked = false;
        $hasText = false;

        foreach ($blocks as $block) {
            if (! is_array($block) || trim((string) ($block['text'] ?? '')) === '') {
                continue;
            }
            $hasText = true;

            $box = $this->clampedTextBox($block, $imageWidth, $imageHeight);
            if ($box === null) {
                continue;
            }

            $overlap = $this->intersectionArea($crop, $box);
            if ($overlap <= 0) {
                continue;
            }

            $textArea = max(1, (float) $box['width'] * (float) $box['height']);
            $textRatio = $overlap / $textArea;
            $cropRatio = $overlap / $cropArea;
            $maxTextRatio = max($maxTextRatio, $textRatio);
            $maxCropRatio = max($maxCropRatio, $cropRatio);
            if ($textRatio > 0.18 || $cropRatio > 0.10) {
                $blocked = true;
            }
        }

        return [
            'hasText' => $hasText,
            'blocked' => $blocked,
            'textRatio' => $maxTextRatio,
            'cropRatio' => $maxCropRatio,
        ];
    }

    private function cropOverlapsTextBlocks(array $crop, array $blocks, int $imageWidth, int $imageHeight): bool
    {
        return $this->decorationTextOverlapStats($crop, $blocks, $imageWidth, $imageHeight)['blocked'];
    }

    private function clampedTextBox(array $item, int $imageWidth, int $imageHeight): ?array
    {
        $x = (int) floor((float) ($item['x'] ?? 0));
        $y = (int) floor((float) ($item['y'] ?? 0));
        $width = (int) ceil((float) ($item['width'] ?? 0));
        $height = (int) ceil((float) ($item['height'] ?? 0));

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $padding = (int) min(18, max(3, round(min($width, $height) * 0.12)));
        $x = max(0, $x - $padding);
        $y = max(0, $y - $padding);
        $right = min($imageWidth, $x + $width + ($padding * 2));
        $bottom = min($imageHeight, $y + $height + ($padding * 2));

        return [
            'x' => $x,
            'y' => $y,
            'width' => max(1, $right - $x),
            'height' => max(1, $bottom - $y),
        ];
    }

    private function intersectionArea(array $a, array $b): float
    {
        $left = max((float) $a['x'], (float) $b['x']);
        $top = max((float) $a['y'], (float) $b['y']);
        $right = min((float) $a['x'] + (float) $a['width'], (float) $b['x'] + (float) $b['width']);
        $bottom = min((float) $a['y'] + (float) $a['height'], (float) $b['y'] + (float) $b['height']);

        return max(0, $right - $left) * max(0, $bottom - $top);
    }

    private function removeFlatBackgroundFromCrop(\GdImage $image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0 || ($width * $height) > 2500000) {
            return false;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $cornerColor = $this->averageCornerColor($image, $width, $height);
        if ($cornerColor === null) {
            return false;
        }

        $changed = 0;
        $hardThreshold = 34;
        $softThreshold = 104;

        for ($y = 0; $y < $height; $y += 1) {
            for ($x = 0; $x < $width; $x += 1) {
                $rgba = imagecolorat($image, $x, $y);
                $color = [
                    ($rgba >> 16) & 0xFF,
                    ($rgba >> 8) & 0xFF,
                    $rgba & 0xFF,
                ];
                $distance = $this->colorDistance($color, $cornerColor);
                if ($distance > $softThreshold) {
                    continue;
                }

                $alpha = $distance <= $hardThreshold
                    ? 127
                    : (int) round(127 - (($distance - $hardThreshold) / max(1, $softThreshold - $hardThreshold) * 96));
                $alpha = max(0, min(127, $alpha));
                $newColor = imagecolorallocatealpha($image, $color[0], $color[1], $color[2], $alpha);
                if ($newColor !== false) {
                    imagesetpixel($image, $x, $y, $newColor);
                    $changed += 1;
                }
            }
        }

        return $changed > (($width * $height) * 0.04);
    }

    private function averageCornerColor(\GdImage $image, int $width, int $height): ?array
    {
        $sampleSize = max(2, min(8, (int) floor(min($width, $height) / 12)));
        $corners = [
            [0, 0],
            [max(0, $width - $sampleSize), 0],
            [0, max(0, $height - $sampleSize)],
            [max(0, $width - $sampleSize), max(0, $height - $sampleSize)],
        ];
        $colors = [];

        foreach ($corners as [$startX, $startY]) {
            $sum = [0, 0, 0];
            $count = 0;
            for ($y = $startY; $y < min($height, $startY + $sampleSize); $y += 1) {
                for ($x = $startX; $x < min($width, $startX + $sampleSize); $x += 1) {
                    $rgba = imagecolorat($image, $x, $y);
                    $sum[0] += ($rgba >> 16) & 0xFF;
                    $sum[1] += ($rgba >> 8) & 0xFF;
                    $sum[2] += $rgba & 0xFF;
                    $count += 1;
                }
            }
            if ($count > 0) {
                $colors[] = [
                    (int) round($sum[0] / $count),
                    (int) round($sum[1] / $count),
                    (int) round($sum[2] / $count),
                ];
            }
        }

        if (count($colors) < 4) {
            return null;
        }

        $base = $colors[0];
        $maxDistance = 0;
        foreach ($colors as $color) {
            $maxDistance = max($maxDistance, $this->colorDistance($base, $color));
        }
        if ($maxDistance > 58) {
            return null;
        }

        return [
            (int) round(array_sum(array_column($colors, 0)) / count($colors)),
            (int) round(array_sum(array_column($colors, 1)) / count($colors)),
            (int) round(array_sum(array_column($colors, 2)) / count($colors)),
        ];
    }

    private function colorDistance(array $a, array $b): float
    {
        return sqrt((($a[0] - $b[0]) ** 2) + (($a[1] - $b[1]) ** 2) + (($a[2] - $b[2]) ** 2));
    }

    private function dominantEdgeColor(string $path, string $mime): string
    {
        $image = $this->gdImageFromPath($path, $mime);
        if (! $image) {
            return '';
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);
            return '';
        }

        $band = max(4, (int) round(min($width, $height) * 0.06));
        $step = max(1, (int) floor(max($width, $height) / 120));
        $buckets = [];

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $isEdge = $x < $band || $y < $band || $x >= ($width - $band) || $y >= ($height - $band);
                if (! $isEdge) {
                    continue;
                }

                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                if ($alpha > 80) {
                    continue;
                }

                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $key = ((int) round($r / 16) * 16) . ',' . ((int) round($g / 16) * 16) . ',' . ((int) round($b / 16) * 16);
                if (! isset($buckets[$key])) {
                    $buckets[$key] = ['count' => 0, 'r' => 0, 'g' => 0, 'b' => 0];
                }
                $buckets[$key]['count'] += 1;
                $buckets[$key]['r'] += $r;
                $buckets[$key]['g'] += $g;
                $buckets[$key]['b'] += $b;
            }
        }

        imagedestroy($image);

        if (empty($buckets)) {
            return '';
        }

        usort($buckets, static fn (array $a, array $b): int => $b['count'] <=> $a['count']);
        $best = $buckets[0];
        $count = max(1, (int) $best['count']);

        return sprintf(
            '#%02x%02x%02x',
            max(0, min(255, (int) round($best['r'] / $count))),
            max(0, min(255, (int) round($best['g'] / $count))),
            max(0, min(255, (int) round($best['b'] / $count)))
        );
    }

    private function imageHasUsefulAlpha(string $path, string $mime): bool
    {
        $image = $this->gdImageFromPath($path, $mime);
        if (! $image) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);
            return false;
        }

        $sampleLimit = 60000;
        $step = max(1, (int) floor(sqrt(($width * $height) / $sampleLimit)));
        $transparent = 0;
        $sampled = 0;

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                if ($alpha > 8) {
                    $transparent += 1;
                }
                $sampled += 1;
            }
        }

        imagedestroy($image);

        return $sampled > 0 && ($transparent / $sampled) > 0.03;
    }

    private function blueprintTextAreaRatio(array $blocks, int $imageWidth, int $imageHeight): float
    {
        $canvasArea = max(1, $imageWidth * $imageHeight);
        $textArea = 0.0;
        foreach ($blocks as $block) {
            if (! is_array($block) || trim((string) ($block['text'] ?? '')) === '') {
                continue;
            }
            $box = $this->clampedTextBox($block, $imageWidth, $imageHeight);
            if ($box === null) {
                continue;
            }
            $textArea += (float) $box['width'] * (float) $box['height'];
        }

        return min(1.0, $textArea / $canvasArea);
    }

    private function clampedCropBox(array $item, int $imageWidth, int $imageHeight): ?array
    {
        $x = (int) floor((float) ($item['x'] ?? 0));
        $y = (int) floor((float) ($item['y'] ?? 0));
        $width = (int) ceil((float) ($item['width'] ?? 0));
        $height = (int) ceil((float) ($item['height'] ?? 0));

        if ($width < 12 || $height < 12) {
            return null;
        }

        $padding = (int) min(24, max(4, round(min($width, $height) * 0.08)));
        $x = max(0, $x - $padding);
        $y = max(0, $y - $padding);
        $right = min($imageWidth, $x + $width + ($padding * 2));
        $bottom = min($imageHeight, $y + $height + ($padding * 2));
        $width = $right - $x;
        $height = $bottom - $y;

        if ($width < 12 || $height < 12 || $width * $height > 6000000) {
            return null;
        }

        return [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ];
    }

    private function gdImageFromPath(string $path, string $mime): mixed
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function publicRelativePathFromUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, 'data:')) {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            $path = $url;
        }

        $basePath = parse_url(base_url('/'), PHP_URL_PATH);
        $basePath = is_string($basePath) ? trim($basePath, '/') : '';
        $path = ltrim($path, '/');
        if ($basePath !== '' && str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath) + 1);
        }

        $path = rawurldecode($path);
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            return '';
        }

        return $path;
    }

    private function maxPublishedLinksForPage(?array $subscription, array $page): int
    {
        if ($subscription !== null) {
            return $this->maxPublishedLinksFromSubscription($subscription);
        }

        return 1;
    }
}
