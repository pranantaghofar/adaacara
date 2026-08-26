<?php

namespace App\Controllers;

use App\Libraries\SeoLandingPageCatalog;
use App\Models\TemplateModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class SeoLandingController extends BaseController
{
    public function photobooth(): string
    {
        return view('seo/photobooth');
    }

    public function photographerGallery(): string
    {
        return view('seo/photographer_gallery');
    }

    public function photographerGalleryPreview(): string
    {
        return view('seo/photographer_gallery_preview');
    }

    public function creator(): string
    {
        return view('seo/creator');
    }

    public function feature(string $slug): string
    {
        return $this->show('fitur/' . $slug);
    }

    public function show(string $path): string
    {
        $page = SeoLandingPageCatalog::find($path);
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Halaman tidak ditemukan.');
        }

        return view('seo/landing', [
            'page' => $page,
            'relatedTemplates' => (new TemplateModel())->getTemplateListingCards(),
        ]);
    }
}
