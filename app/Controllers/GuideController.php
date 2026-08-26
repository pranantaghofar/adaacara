<?php

namespace App\Controllers;

use App\Libraries\GuideArticleCatalog;
use CodeIgniter\Exceptions\PageNotFoundException;

class GuideController extends BaseController
{
    public function index(): string
    {
        return view('guides/index', [
            'articles' => GuideArticleCatalog::all(),
        ]);
    }

    public function show(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw PageNotFoundException::forPageNotFound('Panduan tidak ditemukan.');
        }

        $article = GuideArticleCatalog::find($slug);
        if ($article === null) {
            throw PageNotFoundException::forPageNotFound('Panduan tidak ditemukan.');
        }

        return view('guides/show', [
            'article' => $article,
            'articles' => GuideArticleCatalog::all(),
        ]);
    }
}
