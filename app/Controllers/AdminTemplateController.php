<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\TemplateModel;
use App\Models\TemplateSubcategoryModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminTemplateController extends BaseController
{
    private TemplateModel $templateModel;
    private CategoryModel $categoryModel;
    private TemplateSubcategoryModel $subcategoryModel;
    private array $templateFields;
    private bool $templatesReady = false;
    private bool $categoriesReady = false;

    public function __construct()
    {
        helper('admin_permission');
        $this->templateModel = new TemplateModel();
        $this->categoryModel = new CategoryModel();
        $this->subcategoryModel = new TemplateSubcategoryModel();

        $db = db_connect();
        $this->templatesReady = $db->tableExists('templates');
        $this->categoriesReady = $db->tableExists('categories');
        $this->templateFields = $this->templatesReady ? $db->getFieldNames('templates') : [];
    }

    public function index(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.view', 'templates')) {
            return $deny;
        }

        if (! $this->templatesReady) {
            return $this->setupView('Tabel templates belum tersedia.');
        }

        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $projectTypeInput = trim((string) ($this->request->getGet('project_type') ?? ''));
        $projectType = $projectTypeInput !== '' ? $this->normalizeProjectType($projectTypeInput) : '';
        $projectCategory = trim((string) ($this->request->getGet('project_category') ?? ''));
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        $premium = trim((string) ($this->request->getGet('premium') ?? ''));
        $status = trim((string) ($this->request->getGet('status') ?? ''));

        $builder = $this->templateModel
            ->orderBy('templates.updated_at', 'DESC')
            ->orderBy('templates.created_at', 'DESC');

        if ($this->categoriesReady) {
            $builder
                ->select('templates.*, categories.name AS category_name')
                ->join('categories', 'categories.id = templates.category_id', 'left');
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('templates.name', $search)
                ->orLike('templates.slug', $search);
            if ($this->categoriesReady) {
                $builder->orLike('categories.name', $search);
            }
            $builder->groupEnd();
        }

        if ($projectType !== '' && in_array('project_type', $this->templateFields, true)) {
            if ($projectType === 'invitation') {
                $builder->groupStart()
                    ->where('templates.project_type', 'invitation')
                    ->orWhere('templates.project_type', '')
                    ->orWhere('templates.project_type IS NULL', null, false)
                    ->groupEnd();
            } else {
                $builder->where('templates.project_type', $projectType);
            }
        }

        $allowInvitationCategoryFilter = $projectType === '' || $projectType === 'invitation';
        if ($category !== '' && $allowInvitationCategoryFilter && in_array('category_id', $this->templateFields, true)) {
            $builder->where('templates.category_id', (int) $category);
        }

        if ($projectCategory !== '' && in_array('tags', $this->templateFields, true)) {
            $projectCategoryLabels = $this->projectCategoryFilterLabels($projectCategory);
            $builder->groupStart();
            foreach ($projectCategoryLabels as $index => $label) {
                if ($index === 0) {
                    $builder->like('templates.tags', $label);
                    continue;
                }
                $builder->orLike('templates.tags', $label);
            }
            $builder->groupEnd();
        }

        if ($premium === 'premium' && in_array('is_premium', $this->templateFields, true)) {
            $builder->where('templates.is_premium', 1);
        } elseif ($premium === 'free' && in_array('is_premium', $this->templateFields, true)) {
            $builder->where('templates.is_premium', 0);
        }

        if ($status !== '' && in_array('status', $this->templateFields, true)) {
            $builder->where('templates.status', $status);
        }

        $templates = $builder->findAll();

        return view('admin/templates/index', [
            'templates' => array_map(fn (array $template): array => $this->normalizeTemplate($template), $templates),
            'categories' => $this->categoriesReady ? $this->categories() : [],
            'projectCategories' => $this->adminProjectCategoryOptions(),
            'templateProjectTypeReady' => in_array('project_type', $this->templateFields, true),
            'templateTagsReady' => in_array('tags', $this->templateFields, true),
            'filters' => [
                'q' => $search,
                'project_type' => $projectType,
                'project_category' => $projectCategory,
                'category' => $category,
                'premium' => $premium,
                'status' => $status,
            ],
        ]);
    }

    public function create(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.manage', 'templates')) {
            return $deny;
        }

        if (! $this->templatesReady || ! $this->categoriesReady) {
            return $this->setupView('Tabel templates atau categories belum tersedia.');
        }

        return view('admin/templates/create', [
            'categories' => $this->categories(),
            'templateSubcategories' => $this->templateSubcategories(),
            'selectedSubcategoryIds' => $this->oldSubcategoryIds(),
            'businessCategoryOptions' => $this->businessProfileCategoryOptions(),
            'selectedBusinessCategory' => $this->oldBusinessCategory(),
            'templateSubcategorySetupReady' => $this->subcategoryModel->tableReady() && $this->subcategoryModel->assignmentTableReady(),
            'templateSubcategoryTableReady' => $this->subcategoryModel->tableReady(),
            'templateProjectTypeReady' => in_array('project_type', $this->templateFields, true),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        if (! $this->templatesReady || ! $this->categoriesReady) {
            return redirect()->to('/admin/templates')
                ->with('error', 'Tabel templates atau categories belum tersedia. Jalankan SQL modul admin template terlebih dahulu.');
        }

        $rules = $this->validationRules();

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if ($error = $this->templateCategoryError()) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['category_id' => $error]);
        }

        $slug = $this->slugFromRequest();
        if ($slug === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['slug' => 'Slug tidak valid.']);
        }

        if ($this->templateModel->slugExists($slug)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['slug' => 'Slug sudah digunakan. Pilih slug lain.']);
        }

        $previewUrl = $this->templatePreviewUrlFromRequest();
        if ($previewUrl === false) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['preview_url' => 'Preview URL harus berupa link internal /u/slug.']);
        }

        $thumbnail = $this->uploadThumbnail();
        if ($thumbnail === false) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['thumbnail' => 'Thumbnail gagal diupload.']);
        }

        $data = $this->templateData($slug, $thumbnail, $previewUrl);

        $templateId = $this->templateModel->insert($this->filterTemplateColumns($data), true);
        if (! $templateId) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['template' => 'Template gagal disimpan.']);
        }

        $this->syncTemplateSubcategories((int) $templateId);

        return redirect()->to('/admin/templates')->with('success', 'Template berhasil ditambahkan.');
    }

    public function edit(int $id): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.manage', 'templates')) {
            return $deny;
        }

        if (! $this->templatesReady || ! $this->categoriesReady) {
            return $this->setupView('Tabel templates atau categories belum tersedia.');
        }

        $template = $this->normalizeTemplate($this->findTemplate($id));

        return view('admin/templates/edit', [
            'template' => $template,
            'categories' => $this->categories(),
            'templateSubcategories' => $this->templateSubcategories(),
            'selectedSubcategoryIds' => $this->oldSubcategoryIds($id),
            'businessCategoryOptions' => $this->businessProfileCategoryOptions(),
            'selectedBusinessCategory' => $this->oldBusinessCategory($template),
            'templateSubcategorySetupReady' => $this->subcategoryModel->tableReady() && $this->subcategoryModel->assignmentTableReady(),
            'templateSubcategoryTableReady' => $this->subcategoryModel->tableReady(),
            'templateProjectTypeReady' => in_array('project_type', $this->templateFields, true),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        if (! $this->templatesReady || ! $this->categoriesReady) {
            return redirect()->to('/admin/templates')
                ->with('error', 'Tabel templates atau categories belum tersedia. Jalankan SQL modul admin template terlebih dahulu.');
        }

        $template = $this->findTemplate($id);
        $rules = $this->validationRules(false);

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if ($error = $this->templateCategoryError()) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['category_id' => $error]);
        }

        $slug = $this->slugFromRequest();
        if ($slug === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['slug' => 'Slug tidak valid.']);
        }

        if ($this->templateModel->slugExists($slug, $id)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['slug' => 'Slug sudah digunakan. Pilih slug lain.']);
        }

        $previewUrl = $this->templatePreviewUrlFromRequest();
        if ($previewUrl === false) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['preview_url' => 'Preview URL harus berupa link internal /u/slug.']);
        }

        $thumbnail = $this->uploadThumbnail();
        if ($thumbnail === false) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['thumbnail' => 'Thumbnail gagal diupload.']);
        }

        $data = $this->templateData($slug, $thumbnail ?: ($template['thumbnail'] ?? null), $previewUrl);

        $this->templateModel->update($id, $this->filterTemplateColumns($data));
        $this->syncTemplateSubcategories($id);

        return redirect()->to('/admin/templates')->with('success', 'Template berhasil diupdate.');
    }

    public function storeFromEditor(): ResponseInterface
    {
        if (! in_array(current_admin_role(), ['superadmin', 'content_admin'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Save template dari editor hanya untuk super admin dan content admin.',
            ]);
        }

        if (! $this->templatesReady) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Tabel templates belum tersedia. Jalankan SQL Modul 8 terlebih dahulu.',
                ]);
        }

        $payload = $this->request->getPost();
        $name = trim((string) ($payload['name'] ?? ''));
        $slug = $this->slugFromValue((string) ($payload['slug'] ?? $name));
        $categoryId = (int) ($payload['category_id'] ?? 0);
        $projectType = $this->normalizeProjectType((string) ($payload['project_type'] ?? 'invitation'));

        if ($name === '' || mb_strlen($name) < 3) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Nama template minimal 3 karakter.',
                ]);
        }

        if ($slug === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Slug template tidak valid.',
                ]);
        }

        if ($this->templateModel->slugExists($slug)) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Slug template sudah dipakai. Pilih slug lain.',
                ]);
        }

        if ($categoryId <= 0 && $projectType === 'invitation' && $this->categoriesReady) {
            $firstCategory = $this->categoryModel
                ->orderBy('sort_order', 'ASC')
                ->orderBy('name', 'ASC')
                ->first();
            $categoryId = (int) ($firstCategory['id'] ?? 0);
        }

        $thumbnail = $this->uploadThumbnail();
        if ($thumbnail === '' || $thumbnail === false) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => $thumbnail === ''
                        ? 'Thumbnail / cover wajib diisi.'
                        : 'Thumbnail gagal diupload. Gunakan JPG, PNG, atau WEBP maksimal 2MB.',
                ]);
        }

        $editorJson = (string) ($payload['editor_json'] ?? '');
        $status = in_array((string) ($payload['status'] ?? 'active'), ['active', 'inactive'], true)
            ? (string) $payload['status']
            : 'active';

        $data = [
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'project_type' => $projectType,
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string) ($payload['description'] ?? 'Template dari editor adaAcara.com')),
            'tags' => trim((string) ($payload['tags'] ?? '')),
            'thumbnail' => $thumbnail,
            'html' => (string) ($payload['html'] ?? ''),
            'css' => (string) ($payload['css'] ?? ''),
            'js' => (string) ($payload['js'] ?? ''),
            'editor_json' => $editorJson,
            'editor_type' => 'fabric',
            'grapesjs_json' => $editorJson,
            'is_premium' => (int) ($payload['is_premium'] ?? 0),
            'status' => $status,
            'is_active' => $status === 'active' ? 1 : 0,
            'owner_user_id' => null,
            'created_by_role' => 'admin',
            'review_status' => 'not_required',
            'public_status' => $status === 'active' ? 'public' : 'private',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => (int) (session()->get('userId') ?? 0) ?: null,
        ];

        $templateId = $this->templateModel->insert($this->filterTemplateColumns($data), true);
        if (! $templateId) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Template gagal disimpan.',
                ]);
        }

        $this->syncTemplateSubcategories((int) $templateId);

        return $this->response->setJSON([
            'status' => true,
            'success' => true,
            'message' => 'Template berhasil disimpan dari editor.',
            'template_id' => $templateId,
            'edit_url' => site_url('admin/templates/edit/' . $templateId),
        ]);
    }

    public function updateFromEditor(): ResponseInterface
    {
        if (! in_array(current_admin_role(), ['superadmin', 'content_admin'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Update template dari editor hanya untuk super admin dan content admin.',
            ]);
        }

        if (! $this->templatesReady) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'status' => false,
                    'success' => false,
                    'message' => 'Tabel templates belum tersedia. Jalankan SQL Modul 8 terlebih dahulu.',
                ]);
        }

        $templateId = (int) ($this->request->getPost('template_id') ?? 0);
        if ($templateId <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Pilih template yang ingin diupdate.',
            ]);
        }

        $template = $this->findTemplate($templateId);
        $thumbnail = $this->uploadThumbnail();
        if ($thumbnail === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'success' => false,
                'message' => 'Thumbnail gagal diupload. Gunakan JPG, PNG, atau WEBP maksimal 2MB.',
            ]);
        }

        $editorJson = (string) ($this->request->getPost('editor_json') ?? '');
        $data = [
            'html' => (string) ($this->request->getPost('html') ?? ''),
            'css' => (string) ($this->request->getPost('css') ?? ''),
            'js' => (string) ($this->request->getPost('js') ?? ''),
            'editor_json' => $editorJson,
            'editor_type' => 'fabric',
            'grapesjs_json' => $editorJson,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($thumbnail !== null && $thumbnail !== '') {
            $data['thumbnail'] = $thumbnail;
        }

        $this->templateModel->update($templateId, $this->filterTemplateColumns($data));

        log_message('warning', 'Admin template content updated from editor. admin_id={admin_id} admin_role={admin_role} template_id={template_id} slug={slug} ip={ip}', [
            'admin_id' => (int) (session()->get('userId') ?? 0),
            'admin_role' => current_admin_role(),
            'template_id' => $templateId,
            'slug' => (string) ($template['slug'] ?? ''),
            'ip' => (string) $this->request->getIPAddress(),
        ]);

        return $this->response->setJSON([
            'status' => true,
            'success' => true,
            'message' => 'Template tersimpan berhasil diupdate dari editor.',
            'template_id' => $templateId,
            'edit_url' => site_url('admin/templates/edit/' . $templateId),
        ]);
    }

    public function delete(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.delete')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        if (! $this->templatesReady) {
            return redirect()->to('/admin/templates')
                ->with('error', 'Tabel templates belum tersedia. Jalankan SQL modul admin template terlebih dahulu.');
        }

        $template = $this->findTemplate($id);

        try {
            $this->templateModel->delete($id);
        } catch (DatabaseException) {
            return redirect()->to('/admin/templates')
                ->with('error', 'Template tidak bisa dihapus karena sudah digunakan undangan. Ubah status menjadi inactive.');
        }

        if (! empty($template['thumbnail'])) {
            $path = FCPATH . ltrim((string) $template['thumbnail'], '/');
            if (is_file($path) && str_starts_with(realpath($path) ?: '', realpath(FCPATH . 'uploads/templates') ?: '')) {
                @unlink($path);
            }
        }

        log_message('warning', 'Admin template deleted. admin_id={admin_id} admin_role={admin_role} target_id={target_id} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'admin_role' => current_admin_role(),
            'target_id' => (string) $id,
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/admin/templates')->with('success', 'Template berhasil dihapus.');
    }

    public function toggle(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        if (! $this->templatesReady) {
            return redirect()->to('/admin/templates')
                ->with('error', 'Tabel templates belum tersedia. Jalankan SQL modul admin template terlebih dahulu.');
        }

        $template = $this->findTemplate($id);
        $currentStatus = (string) ($template['status'] ?? (((int) ($template['is_active'] ?? 0)) === 1 ? 'active' : 'inactive'));
        $nextStatus = $currentStatus === 'active' ? 'inactive' : 'active';

        $this->templateModel->update($id, $this->filterTemplateColumns([
            'status' => $nextStatus,
            'is_active' => $nextStatus === 'active' ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]));

        return redirect()->to('/admin/templates')
            ->with('success', $nextStatus === 'active' ? 'Template berhasil ditampilkan.' : 'Template berhasil disembunyikan.');
    }

    private function findTemplate(int $id): array
    {
        $template = $this->templateModel->find($id);

        if ($template === null) {
            throw PageNotFoundException::forPageNotFound('Template tidak ditemukan.');
        }

        return $template;
    }

    private function categories(): array
    {
        if (! $this->categoriesReady) {
            return [];
        }

        return $this->categoryModel->templateOptions();
    }

    private function templateSubcategories(): array
    {
        if (! $this->subcategoryModel->tableReady()) {
            return [];
        }

        return $this->subcategoryModel->activeWithCategoryList();
    }

    private function oldSubcategoryIds(?int $templateId = null): array
    {
        $posted = $this->request->getPost('subcategory_ids');
        if (is_array($posted)) {
            return $this->normalizeSubcategoryIds($posted);
        }

        if ($templateId !== null) {
            return $this->subcategoryModel->selectedIdsForTemplate($templateId);
        }

        return [];
    }

    private function syncTemplateSubcategories(int $templateId): void
    {
        if (! $this->subcategoryModel->assignmentTableReady()) {
            return;
        }

        $this->subcategoryModel->syncTemplateAssignments($templateId, $this->normalizeSubcategoryIds((array) $this->request->getPost('subcategory_ids')));
    }

    private function normalizeSubcategoryIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }

    private function validationRules(bool $isCreate = true): array
    {
        return [
            'category_id' => 'permit_empty|is_natural_no_zero',
            'name' => 'required|min_length[3]|max_length[180]',
            'slug' => 'permit_empty|max_length[190]',
            'description' => 'permit_empty|max_length[500]',
            'preview_url' => 'permit_empty|max_length[500]',
            'html' => 'permit_empty',
            'css' => 'permit_empty',
            'js' => 'permit_empty',
            'editor_json' => 'permit_empty',
            'editor_type' => 'permit_empty|in_list[fabric,grapesjs]',
            'project_type' => 'permit_empty|in_list[invitation,photobooth,business_profile,business-profile]',
            'business_category' => 'permit_empty|max_length[80]',
            'is_premium' => 'permit_empty|in_list[0,1]',
            'status' => 'required|in_list[active,inactive]',
        ];
    }

    private function templateCategoryError(): string
    {
        $projectType = $this->normalizeProjectType((string) $this->request->getPost('project_type'));
        if ($projectType === 'invitation' && (int) $this->request->getPost('category_id') <= 0) {
            return 'Kategori undangan wajib dipilih untuk template Undangan Digital.';
        }

        if ($projectType === 'business_profile' && $this->normalizeBusinessCategory((string) $this->request->getPost('business_category')) === '') {
            return 'Subkategori Business Profile wajib dipilih.';
        }

        return '';
    }

    private function slugFromRequest(): string
    {
        $name = trim((string) $this->request->getPost('name'));
        $slugInput = trim((string) ($this->request->getPost('slug') ?: $name));

        return $this->slugFromValue($slugInput);
    }

    private function slugFromValue(string $value): string
    {
        helper('url');

        return url_title(trim($value), '-', true);
    }

    private function templatePreviewUrlFromRequest(): string|false
    {
        $value = trim((string) $this->request->getPost('preview_url'));
        if ($value === '') {
            return '';
        }

        return $this->normalizePublicPreviewUrl($value);
    }

    private function normalizePublicPreviewUrl(string $value): string|false
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $path = $value;
        if (preg_match('#^https?://#i', $value)) {
            $siteHost = strtolower((string) parse_url(site_url('/'), PHP_URL_HOST));
            $urlHost = strtolower((string) parse_url($value, PHP_URL_HOST));
            if ($siteHost === '' || $urlHost === '' || $siteHost !== $urlHost) {
                return false;
            }
            $path = (string) (parse_url($value, PHP_URL_PATH) ?? '');
        }

        if (! preg_match('#^/?u/([a-z0-9]+(?:-[a-z0-9]+)*)/?$#i', $path, $matches)) {
            return false;
        }

        return site_url('u/' . strtolower($matches[1]));
    }

    private function uploadThumbnail(): string|false|null
    {
        $file = $this->request->getFile('thumbnail');

        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        if ($this->canUploadAnimatedTemplateThumbnail()) {
            $allowedExtensions[] = 'gif';
            $allowedMimeTypes[] = 'image/gif';
        }

        if (
            ! $file->isValid()
            || $file->getSizeByUnit('kb') > 2048
            || ! in_array(strtolower($file->getExtension()), $allowedExtensions, true)
            || ! in_array((string) $file->getMimeType(), $allowedMimeTypes, true)
        ) {
            return false;
        }

        $uploadPath = FCPATH . 'uploads/templates';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = $file->getRandomName();
        $file->move($uploadPath, $fileName, true);

        return 'uploads/templates/' . $fileName;
    }

    private function canUploadAnimatedTemplateThumbnail(): bool
    {
        return in_array(current_admin_role(), ['superadmin', 'content_admin'], true);
    }

    private function templateData(string $slug, ?string $thumbnail, string $previewUrl = ''): array
    {
        $status = (string) $this->request->getPost('status');
        $editorJson = (string) $this->request->getPost('editor_json');
        $editorType = (string) ($this->request->getPost('editor_type') ?: $this->detectEditorType($editorJson));

        return [
            'category_id' => $this->templateCategoryIdFromRequest(),
            'project_type' => $this->normalizeProjectType((string) $this->request->getPost('project_type')),
            'name' => trim((string) $this->request->getPost('name')),
            'slug' => $slug,
            'description' => trim((string) $this->request->getPost('description')),
            'tags' => $this->templateTagsFromRequest(),
            'preview_url' => $previewUrl,
            'thumbnail' => $thumbnail,
            'html' => (string) $this->request->getPost('html'),
            'css' => (string) $this->request->getPost('css'),
            'js' => (string) $this->request->getPost('js'),
            'editor_json' => $editorJson,
            'editor_type' => in_array($editorType, ['fabric', 'grapesjs'], true) ? $editorType : 'grapesjs',
            'grapesjs_json' => $editorJson,
            'is_premium' => (int) ($this->request->getPost('is_premium') ?? 0),
            'status' => $status,
            'is_active' => $status === 'active' ? 1 : 0,
        ];
    }

    private function normalizeTemplate(array $template): array
    {
        $template['status'] ??= ((int) ($template['is_active'] ?? 0)) === 1 ? 'active' : 'inactive';
        $template['editor_json'] ??= $template['grapesjs_json'] ?? '';
        $template['editor_type'] ??= $this->detectEditorType((string) $template['editor_json']);
        $template['project_type'] = $this->normalizeProjectType((string) ($template['project_type'] ?? ''));
        $template['is_premium'] ??= 0;
        $template['project_type_label'] = $this->projectTypeLabel((string) $template['project_type']);
        $template['project_category_label'] = $this->templateProjectCategoryLabel($template);

        return $template;
    }

    private function templateCategoryIdFromRequest(): ?int
    {
        $projectType = $this->normalizeProjectType((string) $this->request->getPost('project_type'));
        if ($projectType !== 'invitation') {
            return null;
        }

        $categoryId = (int) $this->request->getPost('category_id');

        return $categoryId > 0 ? $categoryId : null;
    }

    private function templateTagsFromRequest(): string
    {
        $projectType = $this->normalizeProjectType((string) $this->request->getPost('project_type'));
        if ($projectType === 'photobooth') {
            return 'photobooth,digital photobooth,frame photobooth';
        }

        if ($projectType === 'business_profile') {
            $slug = $this->normalizeBusinessCategory((string) $this->request->getPost('business_category'));
            $label = $this->businessProfileCategoryOptions()[$slug] ?? 'Business Profile';

            return trim('business profile,' . $label . ',' . $slug);
        }

        return trim((string) $this->request->getPost('tags'));
    }

    private function oldBusinessCategory(array $template = []): string
    {
        $posted = $this->request->getPost('business_category');
        if (is_string($posted)) {
            return $this->normalizeBusinessCategory($posted);
        }

        return $this->businessCategoryFromTags((string) ($template['tags'] ?? ''));
    }

    private function businessCategoryFromTags(string $tags): string
    {
        $tags = strtolower($tags);
        foreach ($this->businessProfileCategoryOptions() as $slug => $label) {
            if (str_contains($tags, strtolower($slug)) || str_contains($tags, strtolower($label))) {
                return $slug;
            }
        }

        return '';
    }

    private function normalizeBusinessCategory(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace('_', '-', $value);

        return array_key_exists($value, $this->businessProfileCategoryOptions()) ? $value : '';
    }

    private function projectTypeLabel(string $projectType): string
    {
        return match ($this->normalizeProjectType($projectType)) {
            'photobooth' => 'Digital Photobooth',
            'business_profile' => 'Business Profile',
            default => 'Undangan Digital',
        };
    }

    private function templateProjectCategoryLabel(array $template): string
    {
        $categoryName = trim((string) ($template['category_name'] ?? ''));
        if ($categoryName !== '') {
            return $categoryName;
        }

        $projectType = $this->normalizeProjectType((string) ($template['project_type'] ?? ''));
        if ($projectType === 'photobooth') {
            return 'Digital Photobooth';
        }

        if ($projectType === 'business_profile') {
            $tags = strtolower((string) ($template['tags'] ?? ''));
            foreach ($this->adminProjectCategoryOptions() as $value => $label) {
                if ($value === 'digital-photobooth') {
                    continue;
                }

                if (str_contains($tags, strtolower($value)) || str_contains($tags, strtolower($label))) {
                    return $label;
                }
            }

            return 'Business Profile';
        }

        return '-';
    }

    private function adminProjectCategoryOptions(): array
    {
        return [
            'digital-photobooth' => 'Digital Photobooth',
            ...$this->businessProfileCategoryOptions(),
        ];
    }

    private function businessProfileCategoryOptions(): array
    {
        return [
            'mua' => 'MUA',
            'wedding-organizer' => 'Wedding Organizer',
            'dekorasi' => 'Dekorasi',
            'venue' => 'Venue',
            'catering' => 'Catering',
            'photographer' => 'Photographer',
            'freelancer' => 'Freelancer',
            'umkm' => 'UMKM',
            'agency' => 'Agency',
        ];
    }

    private function projectCategoryFilterLabels(string $value): array
    {
        $options = $this->adminProjectCategoryOptions();
        $labels = [$value];

        if (isset($options[$value])) {
            $labels[] = $options[$value];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (string $label): string => trim($label),
            $labels
        ))));
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

    private function detectEditorType(string $editorJson): string
    {
        $data = json_decode($editorJson, true);

        if (is_array($data) && ($data['renderer'] ?? '') === 'fabric') {
            return 'fabric';
        }

        return 'grapesjs';
    }

    private function filterTemplateColumns(array $data): array
    {
        return array_intersect_key($data, array_flip($this->templateFields));
    }

    private function setupView(string $message): string
    {
        return view('admin/templates/setup', [
            'message' => $message,
        ]);
    }
}
