<?php

namespace App\Controllers;

use App\Models\LandingPageModel;
use App\Models\TemplateModel;
use CodeIgniter\HTTP\RedirectResponse;

class TemplateController extends BaseController
{
    public function index(): string
    {
        $templateModel = new TemplateModel();

        return view('templates/index', [
            'title' => 'Pilih Template - Ada Acara',
            'templates' => $templateModel->getActiveTemplates(),
        ]);
    }

    public function store(): RedirectResponse
    {
        $rules = [
            'template_id' => 'required|is_natural_no_zero',
            'title' => 'required|min_length[3]|max_length[180]',
            'slug' => 'permit_empty|max_length[190]',
            'event_date' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $templateModel = new TemplateModel();
        $template = $templateModel->getActiveTemplate((int) $this->request->getPost('template_id'));

        if ($template === null) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['template_id' => 'Template tidak ditemukan atau tidak aktif.']);
        }

        helper('url');

        $title = trim((string) $this->request->getPost('title'));
        $slugInput = trim((string) ($this->request->getPost('slug') ?: $title));
        $slug = url_title($slugInput, '-', true);

        if ($slug === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['slug' => 'Slug tidak valid. Gunakan huruf atau angka.']);
        }

        $landingPageModel = new LandingPageModel();

        if ($landingPageModel->slugExists($slug)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['slug' => 'Slug sudah digunakan. Pilih slug lain.']);
        }

        $landingPageId = $landingPageModel->createFromTemplate((int) session()->get('userId'), $template, [
            'title' => $title,
            'slug' => $slug,
            'event_date' => (string) $this->request->getPost('event_date'),
        ]);

        if (! $landingPageId) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['landing_page' => 'Landing page gagal dibuat. Coba lagi.']);
        }

        return redirect()->to('/dashboard')->with('success', 'Landing page berhasil dibuat dari template.');
    }
}
