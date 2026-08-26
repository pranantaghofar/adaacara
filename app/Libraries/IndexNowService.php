<?php

namespace App\Libraries;

use Config\SEO as SEOConfig;

class IndexNowService
{
    private SEOConfig $config;

    public function __construct(?SEOConfig $config = null)
    {
        $this->config = $config ?? new SEOConfig();
    }

    public function key(): string
    {
        return trim($this->config->indexNowKey);
    }

    public function keyLocation(): string
    {
        return rtrim($this->config->baseUrl, '/') . '/' . $this->key() . '.txt';
    }

    /**
     * @param list<string> $urls
     * @return array{success: bool, status: int, message: string, submitted: list<string>, rejected: list<string>, response?: string}
     */
    public function submit(array $urls): array
    {
        if (! $this->config->indexNowEnabled) {
            return [
                'success' => false,
                'status' => 0,
                'message' => 'IndexNow belum diaktifkan.',
                'submitted' => [],
                'rejected' => $urls,
            ];
        }

        if (! $this->validKey($this->key())) {
            return [
                'success' => false,
                'status' => 0,
                'message' => 'IndexNow key belum valid.',
                'submitted' => [],
                'rejected' => $urls,
            ];
        }

        [$validUrls, $rejectedUrls] = $this->prepareUrls($urls);
        if ($validUrls === []) {
            return [
                'success' => false,
                'status' => 0,
                'message' => 'Tidak ada URL valid untuk dikirim.',
                'submitted' => [],
                'rejected' => $rejectedUrls,
            ];
        }

        $payload = [
            'host' => (string) parse_url($this->config->baseUrl, PHP_URL_HOST),
            'key' => $this->key(),
            'keyLocation' => $this->keyLocation(),
            'urlList' => $validUrls,
        ];

        try {
            $response = service('curlrequest')->post($this->config->indexNowEndpoint, [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                ],
                'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'http_errors' => false,
                'timeout' => 12,
            ]);

            $status = $response->getStatusCode();
            $accepted = in_array($status, [200, 202], true);

            return [
                'success' => $accepted,
                'status' => $status,
                'message' => $accepted ? 'URL berhasil dikirim ke IndexNow.' : 'IndexNow menolak request.',
                'submitted' => $validUrls,
                'rejected' => $rejectedUrls,
                'response' => substr((string) $response->getBody(), 0, 800),
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'status' => 0,
                'message' => 'Gagal menghubungi IndexNow: ' . $exception->getMessage(),
                'submitted' => $validUrls,
                'rejected' => $rejectedUrls,
            ];
        }
    }

    /**
     * @param list<string> $urls
     * @return array{0: list<string>, 1: list<string>}
     */
    public function prepareUrls(array $urls): array
    {
        $valid = [];
        $rejected = [];

        foreach ($urls as $url) {
            $original = trim((string) $url);
            if ($original === '') {
                continue;
            }

            $normalized = $this->normalizeUrl($original);
            if ($normalized === '' || ! $this->isAllowedUrl($normalized)) {
                $rejected[] = $original;
                continue;
            }

            if (! in_array($normalized, $valid, true)) {
                $valid[] = $normalized;
            }

            if (count($valid) >= 100) {
                break;
            }
        }

        return [$valid, $rejected];
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = rtrim($this->config->baseUrl, '/') . '/' . ltrim($url, '/');
        }

        $url = str_replace(['/index.php/', '/index.php'], ['/', ''], $url);
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) $parts['host']);
        $path = '/' . ltrim((string) ($parts['path'] ?? '/'), '/');
        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

        return $scheme . '://' . $host . $path . $query;
    }

    private function isAllowedUrl(string $url): bool
    {
        $baseHost = strtolower((string) parse_url($this->config->baseUrl, PHP_URL_HOST));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($baseHost === '' || $host !== $baseHost) {
            return false;
        }

        $path = '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        foreach ($this->config->indexNowBlockedPathPrefixes as $prefix) {
            $prefix = '/' . trim((string) $prefix, '/');
            if ($prefix !== '/' && (str_starts_with($path, $prefix . '/') || $path === $prefix)) {
                return false;
            }
        }

        return true;
    }

    private function validKey(string $key): bool
    {
        return preg_match('/^[A-Za-z0-9-]{8,128}$/', $key) === 1;
    }
}
