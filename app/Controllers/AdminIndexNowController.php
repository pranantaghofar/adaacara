<?php

namespace App\Controllers;

use App\Libraries\IndexNowService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminIndexNowController extends BaseController
{
    private IndexNowService $indexNow;

    public function __construct()
    {
        helper('admin_permission');
        $this->indexNow = new IndexNowService();
    }

    public function index(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.settings.sensitive', 'settings')) {
            return $deny;
        }

        return view('admin/indexnow/index', [
            'key' => $this->indexNow->key(),
            'keyLocation' => $this->indexNow->keyLocation(),
            'defaultUrls' => implode("\n", [
                site_url('/'),
                site_url('templates'),
                site_url('plans'),
            ]),
        ]);
    }

    public function submit(): RedirectResponse
    {
        if (! admin_can('admin.settings.sensitive')) {
            return redirect()->to(admin_access_denied_url('settings'))->with('error', 'Akses terbatas.');
        }

        $rawUrls = (string) $this->request->getPost('urls');
        $urls = array_values(array_filter(array_map('trim', preg_split('/\R+/', $rawUrls) ?: [])));
        $result = $this->indexNow->submit($urls);

        return redirect()->to(site_url('admin/indexnow'))
            ->with($result['success'] ? 'success' : 'error', $result['message'])
            ->with('indexnow_result', $result)
            ->withInput();
    }
}
