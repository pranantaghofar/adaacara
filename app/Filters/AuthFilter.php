<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): RedirectResponse|ResponseInterface|null
    {
        if (! session()->has('userId') || ! session()->get('isLoggedIn')) {
            $path = '/' . ltrim($request->getUri()->getPath(), '/');
            $loginUrl = '/login';
            $method = strtoupper($request->getMethod());

            if ($method === 'GET' && preg_match('#^/editor/[1-9][0-9]*$#', $path)) {
                $loginUrl .= '?redirect=' . rawurlencode($path);
            }

            if ($method !== 'GET') {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'status' => false,
                        'code' => 'login_required',
                        'message' => 'Silakan login terlebih dahulu.',
                        'redirect' => site_url('login'),
                    ]);
            }

            return redirect()->to($loginUrl)->with('error', 'Silakan login terlebih dahulu.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
