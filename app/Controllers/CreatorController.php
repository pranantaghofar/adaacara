<?php

namespace App\Controllers;

use App\Models\CreatorApplicationModel;
use App\Models\CreatorProfileModel;
use App\Models\UserSubscriptionModel;
use CodeIgniter\HTTP\RedirectResponse;

class CreatorController extends BaseController
{
    public function apply(): string
    {
        $userId = (int) session()->get('userId');
        try {
            $this->ensureCreatorTables();
        } catch (\Throwable $exception) {
            log_message('error', 'Creator tables setup failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return view('creator/apply', [
                'title' => 'Apply Creator - Ada Acara',
                'application' => null,
                'profile' => null,
                'setupError' => 'Tabel creator belum siap. Hubungi admin untuk menjalankan migrasi database.',
            ]);
        }
        $applicationModel = new CreatorApplicationModel();
        $profileModel = new CreatorProfileModel();

        return view('creator/apply', [
            'title' => 'Apply Creator - Ada Acara',
            'application' => $applicationModel->latestForUser($userId),
            'profile' => $profileModel->activeForUser($userId),
        ]);
    }

    public function myApplication(): string
    {
        return $this->apply();
    }

    public function storeApplication(): RedirectResponse
    {
        $userId = (int) session()->get('userId');
        try {
            $this->ensureCreatorTables();
        } catch (\Throwable $exception) {
            log_message('error', 'Creator tables setup failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Tabel creator belum siap. Hubungi admin untuk menjalankan migrasi database.');
        }
        $applicationModel = new CreatorApplicationModel();
        $profileModel = new CreatorProfileModel();

        if ($profileModel->activeForUser($userId) !== null) {
            return redirect()->to('/creator/apply')->with('error', 'Akun kamu sudah aktif sebagai creator.');
        }

        if ($applicationModel->pendingForUser($userId) !== null) {
            return redirect()->to('/creator/apply')->with('error', 'Aplikasi creator kamu masih menunggu review admin.');
        }

        $rules = [
            'display_name' => 'required|min_length[3]|max_length[80]',
            'bio' => 'required|max_length[1000]',
            'portfolio_url' => 'permit_empty|max_length[255]',
            'social_links' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $portfolioUrl = trim((string) $this->request->getPost('portfolio_url'));
        if ($portfolioUrl !== '' && filter_var($portfolioUrl, FILTER_VALIDATE_URL) === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Portfolio URL harus berupa URL valid.');
        }

        $socialLinks = $this->normalizeSocialLinks((string) $this->request->getPost('social_links'));
        if ($socialLinks === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Social links harus berupa JSON object yang valid.');
        }

        $created = $applicationModel->insert([
            'user_id' => $userId,
            'display_name' => trim((string) $this->request->getPost('display_name')),
            'bio' => trim((string) $this->request->getPost('bio')),
            'portfolio_url' => $portfolioUrl !== '' ? $portfolioUrl : null,
            'social_links' => $socialLinks,
            'status' => 'pending',
        ]);

        if (! $created) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aplikasi creator gagal disimpan.');
        }

        return redirect()->to('/creator/apply')->with('success', 'Aplikasi creator berhasil dikirim. Admin akan mereview pengajuan kamu.');
    }

    public function storeQuickApplication(): RedirectResponse
    {
        $userId = (int) session()->get('userId');
        $displayName = trim((string) $this->request->getPost('display_name'));

        if ($userId <= 0) {
            return redirect()->to('/login')->with('error', 'Silakan login untuk daftar creator.');
        }

        try {
            $this->ensureCreatorTables();
        } catch (\Throwable $exception) {
            log_message('error', 'Creator tables setup failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('creator_modal_error', 'Tabel creator belum siap. Hubungi admin untuk menjalankan migrasi database.');
        }
        $applicationModel = new CreatorApplicationModel();
        $profileModel = new CreatorProfileModel();

        if ($profileModel->activeForUser($userId) !== null) {
            return redirect()->to('/creator/dashboard')->with('success', 'Akun kamu sudah aktif sebagai creator.');
        }

        if ($this->hasActivePublishPlan($userId)) {
            return redirect()->back()->with('creator_modal_error', 'Akun dengan paket aktif tidak bisa mendaftar creator.');
        }

        if ($applicationModel->pendingForUser($userId) !== null) {
            return redirect()->back()->with('creator_modal_error', 'Aplikasi creator kamu masih menunggu approve admin.');
        }

        $displayNameLength = function_exists('mb_strlen') ? mb_strlen($displayName) : strlen($displayName);
        if ($displayNameLength < 3 || $displayNameLength > 80) {
            return redirect()->back()
                ->withInput()
                ->with('creator_modal_error', 'Nama creator wajib 3 sampai 80 karakter.');
        }

        if ($this->creatorNameExists($displayName, $userId)) {
            return redirect()->back()
                ->withInput()
                ->with('creator_modal_error', 'Nama creator sudah dipakai. Gunakan nama lain.');
        }

        $created = $applicationModel->insert([
            'user_id' => $userId,
            'display_name' => $displayName,
            'bio' => 'Pengajuan creator dari menu dashboard.',
            'portfolio_url' => null,
            'social_links' => null,
            'status' => 'pending',
        ]);

        if (! $created) {
            return redirect()->back()
                ->withInput()
                ->with('creator_modal_error', 'Aplikasi creator gagal dikirim.');
        }

        return redirect()->back()->with('creator_modal_success', 'Pengajuan creator berhasil dikirim. Menunggu approve admin.');
    }

    private function normalizeSocialLinks(string $raw): string|null|false
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || $this->isListArray($decoded)) {
            return false;
        }

        return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function isListArray(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private function creatorNameExists(string $displayName, int $currentUserId): bool
    {
        $normalizedName = mb_strtolower(trim(preg_replace('/\s+/', ' ', $displayName) ?? $displayName));
        $profileModel = new CreatorProfileModel();
        $applicationModel = new CreatorApplicationModel();

        foreach ($profileModel->findAll() as $profile) {
            if ((int) ($profile['user_id'] ?? 0) === $currentUserId) {
                continue;
            }

            $candidate = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) ($profile['display_name'] ?? '')) ?? ''));
            if ($candidate !== '' && $candidate === $normalizedName) {
                return true;
            }
        }

        foreach ($applicationModel->whereIn('status', ['pending', 'approved'])->findAll() as $application) {
            if ((int) ($application['user_id'] ?? 0) === $currentUserId) {
                continue;
            }

            $candidate = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) ($application['display_name'] ?? '')) ?? ''));
            if ($candidate !== '' && $candidate === $normalizedName) {
                return true;
            }
        }

        return false;
    }

    private function hasActivePublishPlan(int $userId): bool
    {
        $subscription = (new UserSubscriptionModel())->activeWithPlanByUser($userId);
        if ($subscription === null) {
            return false;
        }

        $planKey = strtolower(trim((string) ($subscription['plan_slug'] ?? $subscription['plan_name'] ?? '')));

        return $planKey !== 'creator';
    }

    private function ensureCreatorTables(): void
    {
        $db = db_connect();
        $forge = \Config\Database::forge();

        if (! $db->tableExists('creator_profiles')) {
            $forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'display_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'slug' => [
                    'type' => 'VARCHAR',
                    'constraint' => 140,
                    'null' => true,
                ],
                'bio' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'avatar_url' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'portfolio_url' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'social_links' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'pending',
                ],
                'approved_application_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addUniqueKey('user_id');
            $forge->addKey('status');
            $forge->createTable('creator_profiles', true);
        }

        if (! $db->tableExists('creator_applications')) {
            $forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                ],
                'display_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'bio' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'portfolio_url' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'social_links' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'pending',
                ],
                'reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'reviewed_by' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'reviewed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('user_id');
            $forge->addKey('status');
            $forge->createTable('creator_applications', true);
        }
    }
}
