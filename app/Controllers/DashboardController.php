<?php

namespace App\Controllers;

use App\Models\LandingPageModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $landingPageModel = new LandingPageModel();
        $userId = (int) session()->get('userId');

        return view('dashboard/index', [
            'userName' => session()->get('userName'),
            'userEmail' => session()->get('userEmail'),
            'landingPages' => $landingPageModel->getByUser($userId),
        ]);
    }
}
