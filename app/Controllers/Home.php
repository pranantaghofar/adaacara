<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\EditorAdModel;
use App\Models\LandingPageModel;
use App\Models\PlanModel;
use App\Models\PublishedDomainModel;
use App\Models\TemplateModel;
use App\Models\UserSubscriptionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Home extends BaseController
{
    public function index(): string
    {
        $domainPage = $this->publishedDomainPageFromHost();
        if ($domainPage !== null) {
            if ($this->freePageExpired($domainPage)) {
                $this->response->setStatusCode(404);

                return view('errors/public_not_found', [
                    'title' => 'Undangan sudah expired',
                    'headline' => 'Undangan sudah expired',
                    'message' => 'Link undangan free ini sudah melewati masa aktif 1 bulan.',
                    'plansUrl' => site_url('plans'),
                    'templatesUrl' => site_url('templates'),
                    'homeUrl' => site_url('/'),
                ]);
            }

            return view('public/render', [
                'page' => $domainPage,
                'guestbookEntries' => [],
            ]);
        }

        $templateModel = new TemplateModel();
        $templates = array_slice($templateModel->getTemplateListingCards('', null, true), 0, 24);
        $categories = [];
        $db = db_connect();

        if ($db->tableExists('categories')) {
            $categoryModel = new CategoryModel();
            $categoryFields = $db->getFieldNames('categories');

            if (in_array('is_active', $categoryFields, true)) {
                $categoryModel->where('is_active', 1);
            }

            foreach ($categoryModel->findAll() as $category) {
                $categories[(int) $category['id']] = $category;
            }
        }

        foreach ($templates as &$template) {
            $categoryId = (int) ($template['category_id'] ?? 0);
            $template['category_name'] = $categories[$categoryId]['name'] ?? null;
            $template['category_slug'] = $categories[$categoryId]['slug'] ?? null;
        }
        unset($template);

        $currentUserId = (int) (session()->get('userId') ?? 0);
        $role = strtolower((string) (session()->get('userRole') ?? ''));
        $hasActiveMembership = $currentUserId > 0 && ((in_array($role, ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin', 'creator'], true)) || (new UserSubscriptionModel())->activeWithPlanByUser($currentUserId) !== null);
        $homeAds = (new EditorAdModel())->activeForEditor([
            'user_id' => $currentUserId,
            'is_creator' => $role === 'creator',
            'has_membership' => $hasActiveMembership,
        ], 3);

        return view('home', [
            'templates' => $templates,
            'plans' => $db->tableExists('plans') ? (new PlanModel())->activePlans() : [],
            'isLoggedIn' => session()->has('userId'),
            'hasActiveMembership' => $hasActiveMembership,
            'homeAds' => $homeAds,
        ]);
    }

    public function v2(): string
    {
        helper('admin_permission');

        if (current_admin_role() !== 'superadmin') {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('home_v2', [
            'isLoggedIn' => session()->has('userId'),
            'hasActiveMembership' => true,
        ]);
    }

    private function balancedHomeTemplates(array $templates, int $limit = 24, int $perCategory = 2): array
    {
        if ($templates === []) {
            return [];
        }

        $buckets = [];
        $selected = [];
        $selectedIds = [];

        foreach ($templates as $template) {
            $category = $this->homeTemplateCategoryKey($template);
            $buckets[$category][] = $template;
        }

        foreach ($buckets as $categoryTemplates) {
            foreach (array_slice($categoryTemplates, 0, $perCategory) as $template) {
                $id = (int) ($template['id'] ?? 0);
                if ($id > 0) {
                    $selectedIds[$id] = true;
                }
                $selected[] = $template;

                if (count($selected) >= $limit) {
                    return $selected;
                }
            }
        }

        foreach ($templates as $template) {
            $id = (int) ($template['id'] ?? 0);
            if ($id > 0 && isset($selectedIds[$id])) {
                continue;
            }

            $selected[] = $template;

            if (count($selected) >= $limit) {
                break;
            }
        }

        return $selected;
    }

    private function homeTemplateCategoryKey(array $template): string
    {
        $source = strtolower(trim((string) (
            ($template['category_slug'] ?? '') . ' ' .
            ($template['category_name'] ?? '') . ' ' .
            ($template['category'] ?? '') . ' ' .
            ($template['jenis'] ?? '') . ' ' .
            ($template['type'] ?? '')
        )));

        if ($source === '') {
            $source = strtolower(trim((string) (($template['name'] ?? '') . ' ' . ($template['description'] ?? ''))));
        }

        $map = [
            'wedding' => ['wedding', 'nikah', 'pernikahan', 'akad', 'resepsi'],
            'lamaran' => ['lamaran', 'engagement', 'tunangan'],
            'seminar' => ['seminar', 'webinar', 'workshop', 'talkshow'],
            'bukber' => ['bukber', 'buka bersama', 'ramadhan', 'ramadan', 'iftar'],
            'halal-bihalal' => ['halal bihalal', 'halalbihalal', 'halal-bihalal'],
            'ulang-tahun' => ['ulang tahun', 'ulang-tahun', 'ultah', 'birthday'],
            'khitan' => ['khitan', 'sunat', 'khitanan'],
            'aqiqah' => ['aqiqah', 'akikah'],
            'syukuran' => ['syukuran', 'tasyakuran'],
            'wisuda' => ['wisuda', 'graduation'],
            'corporate' => ['corporate', 'company', 'kantor', 'gathering', 'launching'],
        ];

        foreach ($map as $key => $keywords) {
            foreach ($keywords as $keyword) {
                if ($source !== '' && str_contains($source, $keyword)) {
                    return $key;
                }
            }
        }

        return 'lainnya';
    }

    private function publishedDomainPageFromHost(): ?array
    {
        $host = $this->normalizedRequestHost();
        if ($host === '' || $this->hostIsMainSite($host)) {
            return null;
        }

        try {
            $domain = (new PublishedDomainModel())->activeByHost($host);
            if (! is_array($domain)) {
                return null;
            }

            $page = (new LandingPageModel())->find((int) ($domain['landing_page_id'] ?? 0));
            if (! is_array($page) || (string) ($page['status'] ?? '') !== 'published') {
                return null;
            }

            return $page;
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizedRequestHost(): string
    {
        $host = strtolower(trim((string) $this->request->getServer('HTTP_HOST')));
        if ($host === '') {
            $host = strtolower(trim((string) $this->request->getServer('SERVER_NAME')));
        }
        $host = preg_replace('/:\d+$/', '', $host) ?? '';
        $host = trim($host, ". \t\n\r\0\x0B");

        return preg_match('/^[a-z0-9.-]{1,253}$/', $host) ? $host : '';
    }

    private function hostIsMainSite(string $host): bool
    {
        $host = strtolower(trim($host));
        $baseHost = strtolower((string) parse_url(base_url('/'), PHP_URL_HOST));
        $mainHosts = array_filter(array_unique([
            $baseHost,
            'www.' . $baseHost,
            'adaacara.com',
            'www.adaacara.com',
            'localhost',
            '127.0.0.1',
        ]));

        return in_array($host, $mainHosts, true);
    }

    private function freePageExpired(array $page): bool
    {
        if (! $this->pageUsesFreeTemplate($page)) {
            return false;
        }

        $userId = (int) ($page['user_id'] ?? 0);
        if ($userId > 0 && (new UserSubscriptionModel())->activeWithPlanByUser($userId) !== null) {
            return false;
        }

        $expiresAt = $this->freePageExpiresAt($page);

        return $expiresAt !== null && strtotime($expiresAt) < time();
    }

    private function freePageExpiresAt(array $page): ?string
    {
        $db = db_connect();
        $userId = (int) ($page['user_id'] ?? 0);
        if ($userId > 0 && $db->tableExists('free_publish_entitlements')) {
            $entitlement = $db->table('free_publish_entitlements')
                ->where('user_id', $userId)
                ->get(1)
                ->getRowArray();

            if (is_array($entitlement) && ! empty($entitlement['expires_at'])) {
                return (string) $entitlement['expires_at'];
            }
        }

        $publishedAt = (string) ($page['published_at'] ?? '');
        if (strtotime($publishedAt) === false) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime('+1 month', strtotime($publishedAt)));
    }

    private function pageUsesFreeTemplate(array $page): bool
    {
        $templateId = (int) ($page['template_id'] ?? 0);
        if ($templateId <= 0) {
            return false;
        }

        $db = db_connect();
        if (! $db->tableExists('templates') || ! in_array('is_premium', $db->getFieldNames('templates'), true)) {
            return false;
        }

        $template = $db->table('templates')
            ->select('is_premium')
            ->where('id', $templateId)
            ->get()
            ->getRowArray();

        return is_array($template) && (int) ($template['is_premium'] ?? 1) === 0;
    }
}
