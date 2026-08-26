<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?RedirectResponse
    {
        if (session()->has('userId') && session()->get('isLoggedIn')) {
            $redirect = $this->safeEditorRedirect((string) (service('request')->getGet('redirect') ?? ''));

            return redirect()->to($redirect !== '' ? $redirect : '/dashboard');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }

    private function safeEditorRedirect(string $redirect): string
    {
        $redirect = trim(rawurldecode($redirect));
        if ($redirect === '') {
            return '';
        }

        $path = parse_url($redirect, PHP_URL_PATH);
        if (! is_string($path) || ! preg_match('#^/editor/[1-9][0-9]*$#', $path)) {
            return '';
        }

        return $path;
    }
}
