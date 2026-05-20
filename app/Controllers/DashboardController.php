<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('dashboard/index', [
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
        ]);
    }
}
