<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class ErrorController extends BaseController
{
    public function show404(): ResponseInterface
    {
        if ($this->wantsJson()) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'status' => false,
                    'code' => 'not_found',
                    'message' => 'Halaman tidak ditemukan.',
                ]);
        }

        return $this->response
            ->setStatusCode(404)
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody(view('errors/public_not_found', [
                'title' => 'Halaman tidak ditemukan',
                'headline' => 'Halaman tidak ditemukan',
                'message' => 'Link yang kamu buka belum aktif, sudah dihapus, atau alamatnya kurang tepat.',
                'plansUrl' => site_url('plans'),
                'templatesUrl' => site_url('templates'),
                'homeUrl' => site_url('/'),
            ]));
    }

    private function wantsJson(): bool
    {
        $method = strtoupper($this->request->getMethod());
        $accept = strtolower($this->request->getHeaderLine('Accept'));

        if ($this->request->isAJAX()) {
            return true;
        }

        if (! in_array($method, ['GET', 'HEAD'], true)) {
            return true;
        }

        return str_contains($accept, 'application/json') && ! str_contains($accept, 'text/html');
    }
}
