<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\TemplateSubcategoryModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminTemplateSubcategoryController extends BaseController
{
    private TemplateSubcategoryModel $subcategoryModel;
    private CategoryModel $categoryModel;

    public function __construct()
    {
        helper(['admin_permission', 'url']);
        $this->subcategoryModel = new TemplateSubcategoryModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.templates.view', 'templates')) {
            return $deny;
        }

        return view('admin/template_subcategories/index', [
            'tableReady' => $this->subcategoryModel->tableReady(),
            'subcategories' => $this->subcategoryModel->withCategoryList(),
            'categories' => $this->categories(),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        if (! $this->subcategoryModel->tableReady()) {
            return redirect()->to(site_url('admin/template-subcategories'))
                ->with('error', 'Tabel template_subcategories belum tersedia. Jalankan SQL setup terlebih dahulu.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        $slug = $this->slugFromPost($name);

        $this->subcategoryModel->insert([
            'category_id' => (int) $this->request->getPost('category_id'),
            'name' => $name,
            'slug' => $slug,
            'group_title' => trim((string) $this->request->getPost('group_title')),
            'search_keywords' => trim((string) $this->request->getPost('search_keywords')),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
            'is_active' => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ]);

        return redirect()->to(site_url('admin/template-subcategories'))
            ->with('success', 'Subkategori template berhasil ditambahkan.');
    }

    public function update(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $subcategory = $this->findSubcategory($id);

        if (! $this->validate($this->rules($id))) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        $slug = $this->slugFromPost($name);

        $this->subcategoryModel->update((int) $subcategory['id'], [
            'category_id' => (int) $this->request->getPost('category_id'),
            'name' => $name,
            'slug' => $slug,
            'group_title' => trim((string) $this->request->getPost('group_title')),
            'search_keywords' => trim((string) $this->request->getPost('search_keywords')),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
        ]);

        return redirect()->to(site_url('admin/template-subcategories'))
            ->with('success', 'Subkategori template berhasil diupdate.');
    }

    public function toggle(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.manage')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $subcategory = $this->findSubcategory($id);
        $nextStatus = (int) ($subcategory['is_active'] ?? 0) === 1 ? 0 : 1;
        $this->subcategoryModel->update($id, ['is_active' => $nextStatus]);

        return redirect()->to(site_url('admin/template-subcategories'))
            ->with('success', $nextStatus === 1 ? 'Subkategori diaktifkan.' : 'Subkategori dinonaktifkan.');
    }

    public function delete(int $id): RedirectResponse
    {
        if (! admin_can('admin.templates.delete')) {
            return redirect()->to(admin_access_denied_url('templates'))->with('error', 'Akses terbatas.');
        }

        $this->findSubcategory($id);

        try {
            $this->subcategoryModel->delete($id);
        } catch (DatabaseException) {
            return redirect()->to(site_url('admin/template-subcategories'))
                ->with('error', 'Subkategori belum bisa dihapus.');
        }

        return redirect()->to(site_url('admin/template-subcategories'))
            ->with('success', 'Subkategori template berhasil dihapus.');
    }

    private function categories(): array
    {
        if (! db_connect()->tableExists('categories')) {
            return [];
        }

        return $this->categoryModel->templateOptions();
    }

    private function rules(?int $ignoreId = null): array
    {
        $slugRule = 'permit_empty|max_length[140]|is_unique[template_subcategories.slug]';
        if ($ignoreId !== null) {
            $slugRule = 'permit_empty|max_length[140]|is_unique[template_subcategories.slug,id,' . $ignoreId . ']';
        }

        return [
            'category_id' => 'required|is_natural_no_zero',
            'name' => 'required|min_length[2]|max_length[120]',
            'slug' => $slugRule,
            'group_title' => 'permit_empty|max_length[120]',
            'search_keywords' => 'permit_empty|max_length[1000]',
            'sort_order' => 'permit_empty|integer',
        ];
    }

    private function slugFromPost(string $fallbackName): string
    {
        $slugInput = trim((string) ($this->request->getPost('slug') ?: $fallbackName));

        return url_title($slugInput, '-', true);
    }

    private function findSubcategory(int $id): array
    {
        if (! $this->subcategoryModel->tableReady()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Subkategori template belum tersedia.');
        }

        $subcategory = $this->subcategoryModel->find($id);
        if ($subcategory === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Subkategori template tidak ditemukan.');
        }

        return $subcategory;
    }
}
