<?php

namespace App\Controllers;

use App\Libraries\CompanyLegalDocuments;

class AboutController extends BaseController
{
    public function index()
    {
        return view('about_us', [
            'companyEmail' => 'hello@adaacara.com',
            'legalDocuments' => CompanyLegalDocuments::load(),
        ]);
    }
}
