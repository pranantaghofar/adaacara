<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ResponseInterface|RedirectResponse|null
    {
        helper('admin_permission');

        if (! session()->has('userId') || ! session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $role = session()->get('userRole');
        if ($role === null || $role === '') {
            $user = (new UserModel())->find((int) session()->get('userId'));
            $role = $user['role'] ?? 'user';
            session()->set('userRole', $role);
        }

        $role = strtolower(trim((string) $role));
        if (! in_array($role, admin_roles(), true)) {
            return service('response')
                ->setStatusCode(403)
                ->setBody('Akses admin terbatas.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
