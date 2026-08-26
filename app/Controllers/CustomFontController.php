<?php

namespace App\Controllers;

use App\Libraries\CustomFontService;
use CodeIgniter\HTTP\ResponseInterface;

class CustomFontController extends BaseController
{
    public function css(): ResponseInterface
    {
        $css = (new CustomFontService())->fontCss();

        return $this->response
            ->setHeader('Content-Type', 'text/css; charset=UTF-8')
            ->setHeader('Cache-Control', 'public, max-age=300')
            ->setBody($css);
    }
}
