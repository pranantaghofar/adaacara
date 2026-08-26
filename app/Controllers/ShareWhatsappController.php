<?php

namespace App\Controllers;

use App\Models\LandingPageModel;

class ShareWhatsappController extends BaseController
{
    public function index(): string
    {
        $page = $this->resolvePage();
        $manualLink = trim((string) $this->request->getGet('link'));
        $manualTitle = trim((string) $this->request->getGet('title'));
        $manualDate = trim((string) $this->request->getGet('event_date'));
        $publicUrl = '';

        if ($page !== []) {
            $publicUrl = site_url('u/' . (string) ($page['slug'] ?? ''));
        } elseif ($manualLink !== '') {
            $publicUrl = $manualLink;
        }

        return view('dashboard/share_whatsapp', [
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
            'isLoggedIn' => (int) session()->get('userId') > 0,
            'page' => $page,
            'manualTitle' => $manualTitle,
            'manualDate' => $manualDate,
            'manualMode' => $page === [],
            'publicUrl' => $publicUrl,
        ]);
    }

    private function resolvePage(): array
    {
        $model = new LandingPageModel();
        $pageId = (int) $this->request->getGet('page_id');
        $userId = (int) session()->get('userId');

        if ($pageId > 0 && $userId > 0) {
            $page = $model
                ->where('id', $pageId)
                ->where('user_id', $userId)
                ->first();

            if (is_array($page)) {
                return $page;
            }
        }

        $slug = trim((string) $this->request->getGet('slug'));
        if ($slug !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $page = $model
                ->where('slug', $slug)
                ->where('status', 'published')
                ->first();

            if (is_array($page)) {
                return $page;
            }
        }

        return [];
    }
}
