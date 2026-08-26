<?php

namespace App\Controllers;

use App\Libraries\BebekMagicAssistantService;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

class BebekMagicController extends BaseController
{
    public function chat(): ResponseInterface
    {
        if (! filter_var(env('BEBEK_MAGIC_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Bebek Magic sedang tidak aktif.',
                ]);
        }

        if (! $this->withinRateLimit()) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => 'Bebek Magic sedang ramai. Coba lagi sebentar ya Kak.',
                ]);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $message = (string) ($payload['message'] ?? '');

        try {
            $service = new BebekMagicAssistantService();
            $reply = $service->reply($message, [
                'userId' => (int) (session()->get('userId') ?? 0),
                'path' => current_url(),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => $reply,
            ]);
        } catch (RuntimeException $error) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => $error->getMessage(),
                ]);
        } catch (\Throwable $error) {
            log_message('error', 'Bebek Magic gagal: {message}', [
                'message' => $error->getMessage(),
            ]);

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Bebek Magic sedang sibuk sebentar. Coba lagi ya Kak.',
                ]);
        }
    }

    private function withinRateLimit(): bool
    {
        $limit = max(1, (int) env('BEBEK_MAGIC_RATE_LIMIT_PER_MINUTE', 12));
        $identity = sha1($this->request->getIPAddress() . '|' . (string) session_id());
        $key = 'bebek_magic_rate_' . $identity;

        try {
            $cache = service('cache');
            $count = (int) ($cache->get($key) ?? 0);
            if ($count >= $limit) {
                return false;
            }
            $cache->save($key, $count + 1, 60);
        } catch (\Throwable) {
            return true;
        }

        return true;
    }
}
