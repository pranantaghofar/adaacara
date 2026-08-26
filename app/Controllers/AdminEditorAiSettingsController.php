<?php

namespace App\Controllers;

use App\Models\AppSettingModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminEditorAiSettingsController extends BaseController
{
    private const PROVIDERS = ['poof', 'removebg', 'rembg'];

    public function __construct()
    {
        helper('admin_permission');
    }

    public function index(): string|RedirectResponse|ResponseInterface
    {
        if ($deny = admin_require('admin.api_keys.manage', 'settings')) {
            return $deny;
        }

        $settings = (new AppSettingModel())->getSettings([
            'remove_bg_provider' => $this->normalizeProvider((string) env('REMOVE_BG_PROVIDER', 'poof')),
            'magic_layer_provider' => $this->normalizeMagicLayerProvider((string) env('MAGIC_LAYER_PROVIDER', 'inherit')),
            'remove_bg_fallback_provider' => $this->normalizeFallbackProvider((string) env('REMOVE_BG_FALLBACK_PROVIDER', 'none')),
            'magic_layer_fallback_provider' => $this->normalizeMagicLayerFallbackProvider((string) env('MAGIC_LAYER_FALLBACK_PROVIDER', 'inherit')),
        ]);

        return view('admin/editor_ai_settings', [
            'settings' => [
                'remove_bg_provider' => $this->normalizeProvider((string) ($settings['remove_bg_provider'] ?? 'poof')),
                'magic_layer_provider' => $this->normalizeMagicLayerProvider((string) ($settings['magic_layer_provider'] ?? 'inherit')),
                'remove_bg_fallback_provider' => $this->normalizeFallbackProvider((string) ($settings['remove_bg_fallback_provider'] ?? 'none')),
                'magic_layer_fallback_provider' => $this->normalizeMagicLayerFallbackProvider((string) ($settings['magic_layer_fallback_provider'] ?? 'inherit')),
            ],
            'providerStatus' => $this->providerStatus(),
        ]);
    }

    public function update(): RedirectResponse
    {
        if (! admin_can('admin.api_keys.manage')) {
            return redirect()->to(admin_access_denied_url('settings'))->with('error', 'Akses terbatas.');
        }

        $password = (string) $this->request->getPost('admin_password');
        $admin = (new UserModel())->find((int) session()->get('userId'));
        if ($admin === null || ! password_verify($password, (string) ($admin['password_hash'] ?? ''))) {
            return redirect()->back()->withInput()->with('error', 'Password login admin tidak valid.');
        }

        $removeBgProvider = $this->normalizeProvider((string) $this->request->getPost('remove_bg_provider'));
        $magicLayerProvider = $this->normalizeMagicLayerProvider((string) $this->request->getPost('magic_layer_provider'));
        $removeBgFallbackProvider = $this->normalizeFallbackProvider((string) $this->request->getPost('remove_bg_fallback_provider'));
        $magicLayerFallbackProvider = $this->normalizeMagicLayerFallbackProvider((string) $this->request->getPost('magic_layer_fallback_provider'));

        if (! in_array($removeBgProvider, self::PROVIDERS, true)) {
            return redirect()->back()->withInput()->with('error', 'Provider Remove BG tidak valid.');
        }

        if ($magicLayerProvider !== 'inherit' && ! in_array($magicLayerProvider, self::PROVIDERS, true)) {
            return redirect()->back()->withInput()->with('error', 'Provider Magic Layer tidak valid.');
        }

        if ($removeBgFallbackProvider !== 'none' && ! in_array($removeBgFallbackProvider, self::PROVIDERS, true)) {
            return redirect()->back()->withInput()->with('error', 'Fallback Remove BG tidak valid.');
        }

        if (! in_array($magicLayerFallbackProvider, ['inherit', 'none'], true) && ! in_array($magicLayerFallbackProvider, self::PROVIDERS, true)) {
            return redirect()->back()->withInput()->with('error', 'Fallback Magic Layer tidak valid.');
        }

        (new AppSettingModel())->saveSettings([
            'remove_bg_provider' => $removeBgProvider,
            'magic_layer_provider' => $magicLayerProvider,
            'remove_bg_fallback_provider' => $removeBgFallbackProvider,
            'magic_layer_fallback_provider' => $magicLayerFallbackProvider,
        ], (int) session()->get('userId'));

        log_message('warning', 'Admin editor AI settings updated. admin_id={admin_id} admin_role={admin_role} ip={ip}', [
            'admin_id' => (string) (session()->get('userId') ?? '-'),
            'admin_role' => current_admin_role(),
            'ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/admin/editor-ai-settings')->with('success', 'Pengaturan provider editor berhasil disimpan.');
    }

    public function test(): ResponseInterface
    {
        if (! admin_can('admin.api_keys.manage')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Akses terbatas.']);
        }

        $settings = (new AppSettingModel())->getSettings([
            'remove_bg_provider' => $this->normalizeProvider((string) env('REMOVE_BG_PROVIDER', 'poof')),
            'magic_layer_provider' => $this->normalizeMagicLayerProvider((string) env('MAGIC_LAYER_PROVIDER', 'inherit')),
            'remove_bg_fallback_provider' => $this->normalizeFallbackProvider((string) env('REMOVE_BG_FALLBACK_PROVIDER', 'none')),
            'magic_layer_fallback_provider' => $this->normalizeMagicLayerFallbackProvider((string) env('MAGIC_LAYER_FALLBACK_PROVIDER', 'inherit')),
        ]);

        $removeBgProvider = $this->normalizeProvider((string) ($settings['remove_bg_provider'] ?? 'poof'));
        $magicLayerProvider = $this->normalizeMagicLayerProvider((string) ($settings['magic_layer_provider'] ?? 'inherit'));
        $removeBgFallback = $this->normalizeFallbackProvider((string) ($settings['remove_bg_fallback_provider'] ?? 'none'));
        $magicLayerFallback = $this->normalizeMagicLayerFallbackProvider((string) ($settings['magic_layer_fallback_provider'] ?? 'inherit'));
        $status = $this->providerStatus();

        return $this->response->setJSON([
            'success' => true,
            'csrf_hash' => csrf_hash(),
            'active' => [
                'remove_bg_provider' => $removeBgProvider,
                'magic_layer_provider' => $magicLayerProvider === 'inherit' ? $removeBgProvider : $magicLayerProvider,
                'remove_bg_fallback_provider' => $removeBgFallback,
                'magic_layer_fallback_provider' => $magicLayerFallback === 'inherit' ? $removeBgFallback : $magicLayerFallback,
            ],
            'providers' => $status,
        ]);
    }

    private function normalizeProvider(string $provider): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower(trim($provider))) ?: '';
        if (in_array($normalized, ['poof', 'poofbg'], true)) {
            return 'poof';
        }

        if (in_array($normalized, ['removebg', 'removebgapi'], true)) {
            return 'removebg';
        }

        if (in_array($normalized, ['rembg', 'local', 'localrembg', 'selfhosted', 'selfhost'], true)) {
            return 'rembg';
        }

        return 'poof';
    }

    private function normalizeMagicLayerProvider(string $provider): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower(trim($provider))) ?: '';
        if (in_array($normalized, ['inherit', 'same', 'follow', 'default', ''], true)) {
            return 'inherit';
        }

        return $this->normalizeProvider($provider);
    }

    private function normalizeFallbackProvider(string $provider): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower(trim($provider))) ?: '';
        if (in_array($normalized, ['none', 'off', 'disabled', 'disable', ''], true)) {
            return 'none';
        }

        return $this->normalizeProvider($provider);
    }

    private function normalizeMagicLayerFallbackProvider(string $provider): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '', strtolower(trim($provider))) ?: '';
        if (in_array($normalized, ['inherit', 'same', 'follow', 'default', ''], true)) {
            return 'inherit';
        }

        return $this->normalizeFallbackProvider($provider);
    }

    private function providerStatus(): array
    {
        $poofKey = trim((string) env('POOF_BG_API_KEY', ''));
        $poofEndpoint = trim((string) env('POOF_BG_ENDPOINT', 'https://api.poof.bg/v1/remove'));
        $removeBgKey = trim((string) env('REMOVE_BG_API_KEY', ''));
        if ($removeBgKey === '') {
            $removeBgKey = trim((string) env('REMOVEBG_API_KEY', ''));
        }
        $removeBgEndpoint = trim((string) env('REMOVE_BG_ENDPOINT', ''));
        if ($removeBgEndpoint === '') {
            $removeBgEndpoint = trim((string) env('REMOVEBG_ENDPOINT', 'https://api.remove.bg/v1.0/removebg'));
        }
        $rembgUrl = trim((string) env('REMBG_SERVICE_URL', ''));
        $rembgToken = trim((string) env('REMBG_SERVICE_TOKEN', ''));

        return [
            'poof' => [
                'label' => 'Poof.bg',
                'ready' => $poofKey !== '' && $poofEndpoint !== '',
                'details' => $poofKey !== '' && $poofEndpoint !== ''
                    ? 'API key dan endpoint tersedia.'
                    : 'Butuh POOF_BG_API_KEY dan POOF_BG_ENDPOINT di .env.',
            ],
            'removebg' => [
                'label' => 'Remove.bg',
                'ready' => $removeBgKey !== '' && $removeBgEndpoint !== '',
                'details' => $removeBgKey !== '' && $removeBgEndpoint !== ''
                    ? 'API key dan endpoint tersedia.'
                    : 'Butuh REMOVE_BG_API_KEY di .env.',
            ],
            'rembg' => [
                'label' => 'Self-hosted rembg',
                'ready' => $rembgUrl !== '' && $rembgToken !== '',
                'details' => $rembgUrl !== '' && $rembgToken !== ''
                    ? 'Service URL dan token tersedia.'
                    : 'Butuh REMBG_SERVICE_URL dan REMBG_SERVICE_TOKEN di .env.',
            ],
        ];
    }
}
