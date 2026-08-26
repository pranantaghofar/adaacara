<?php namespace App\Controllers\base;

use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class EditorController extends Controller
{
    private string $table = 'landing_pages';

    public function edit(int $id): string
    {
        $page = $this->findPage($id);

        return view('editor/index', [
            'title' => 'Editor - ' . ($page['title'] ?? 'Landing Page'),
            'page' => $page,
            'editorJsonColumn' => $this->editorJsonColumn(),
        ]);
    }

    public function save(int $id): ResponseInterface
    {
        $page = $this->findPage($id);
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();

        $data = [
            'html' => (string) ($payload['html'] ?? ''),
            'css' => (string) ($payload['css'] ?? ''),
            'js' => (string) ($payload['js'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $editorJson = $payload['editor_json'] ?? $payload['grapesjs_json'] ?? null;
        if ($editorJson !== null) {
            $data[$this->editorJsonColumn()] = is_string($editorJson)
                ? $editorJson
                : json_encode($editorJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        Database::connect()
            ->table($this->table)
            ->where('id', $page['id'])
            ->update($this->filterExistingColumns($data));

        return service('response')->setJSON([
            'success' => true,
            'message' => 'Perubahan berhasil disimpan.',
        ]);
    }

    public function publish(int $id): ResponseInterface
    {
        $page = $this->findPage($id);

        Database::connect()
            ->table($this->table)
            ->where('id', $page['id'])
            ->update($this->filterExistingColumns([
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));

        return service('response')->setJSON([
            'success' => true,
            'message' => 'Landing page berhasil dipublish.',
            'url' => site_url('u/' . $page['slug']),
        ]);
    }

    public function preview(int $id): string
    {
        $page = $this->findPage($id, false);

        return view('editor/render', [
            'page' => $page,
            'isPreview' => true,
            'guestbookEntries' => [],
        ]);
    }

    public function published(string $slug): string
    {
        $page = $this->findPublishedBySlug($slug);

        return view('editor/render', [
            'page' => $page,
            'isPreview' => false,
            'guestbookEntries' => $this->guestbookEntries((int) $page['id']),
        ]);
    }

    public function guestbook(string $slug): ResponseInterface
    {
        $page = $this->findPublishedBySlug($slug);

        $rules = [
            'name' => 'required|min_length[2]|max_length[120]',
            'email' => 'permit_empty|valid_email|max_length[190]',
            'message' => 'permit_empty|max_length[1000]',
            'attendance_status' => 'required|in_list[pending,attending,not_attending]',
            'guest_count' => 'required|is_natural_no_zero|less_than_equal_to[20]',
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
                'name' => trim((string) $this->request->getPost('name')),
                'email' => trim((string) $this->request->getPost('email')) ?: null,
                'message' => trim((string) $this->request->getPost('message')) ?: null,
                'attendance_status' => (string) $this->request->getPost('attendance_status'),
                'guest_count' => (int) $this->request->getPost('guest_count'),
                'is_visible' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));

        return redirect()->to(site_url('u/' . $page['slug']) . '#guestbook')
            ->with('guestbook_success', 'Terima kasih, ucapan kamu sudah tersimpan.');
    }

    private function findPage(int $id, bool $mustOwn = true): array
    {
        $builder = Database::connect()->table($this->table)->where('id', $id);
        $userId = session()->get('userId');

        if ($mustOwn && $userId !== null) {
            $builder->where('user_id', (int) $userId);
        }

        $page = $builder->get()->getRowArray();

        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Landing page tidak ditemukan.');
        }

        return $page;
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
            throw PageNotFoundException::forPageNotFound('Landing page tidak ditemukan.');
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
            ->where('is_visible', 1)
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
}
