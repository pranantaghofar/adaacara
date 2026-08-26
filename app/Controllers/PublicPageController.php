<?php

namespace App\Controllers;

use App\Models\GuestBookModel;
use App\Models\FreePublishEntitlementModel;
use App\Models\LandingPageModel;
use App\Models\UserSubscriptionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use Throwable;

class PublicPageController extends BaseController
{
    private const COMMENT_CACHE_LIMIT = 100;
    private const STICKER_PATTERN = '/^sticker\d{3}\.gif$/i';

    public function show(string $slug): string
    {
        try {
            $slug = $this->safeSlug($slug);
            $page = $this->publishedPage($slug);
        } catch (PageNotFoundException) {
            return $this->publicNotFound();
        } catch (FreePageExpiredException) {
            return $this->publicNotFound(
                'Undangan sudah expired',
                'Link undangan free ini sudah melewati masa aktif 1 bulan.'
            );
        }

        $guestBookModel = new GuestBookModel();
        $guestbookEntries = [];
        $this->response
            ->setHeader('Cache-Control', 'public, max-age=30, stale-while-revalidate=120');

        try {
            $guestbookEntries = $this->commentsForPage($slug, (int) $page['id'], $guestBookModel);
        } catch (DatabaseException) {
            $guestbookEntries = [];
        }

        try {
            return view('public/render', [
                'page' => $page,
                'guestbookEntries' => $guestbookEntries,
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Public render failed for slug {slug}: {message}', [
                'slug' => $slug,
                'message' => $exception->getMessage(),
            ]);

            $page['published_html'] = '';
            $page['published_css'] = '';
            $page['published_js'] = '';
            $page['published_editor_json'] = '';

            try {
                return view('public/render', [
                    'page' => $page,
                    'guestbookEntries' => $guestbookEntries,
                ]);
            } catch (Throwable $fallbackException) {
                log_message('critical', 'Public render fallback failed for slug {slug}: {message}', [
                    'slug' => $slug,
                    'message' => $fallbackException->getMessage(),
                ]);

                return '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>'
                    . htmlspecialchars((string) ($page['title'] ?? 'Ada Acara'), ENT_QUOTES, 'UTF-8')
                    . '</title></head><body style="margin:0;font-family:Inter,Arial,sans-serif;background:#f8fafc;color:#101828;"><main style="min-height:100vh;display:grid;place-items:center;padding:24px;text-align:center;"><div><h1 style="margin:0 0 12px;">Halaman sedang dipulihkan</h1><p style="margin:0;color:#667085;">Silakan coba beberapa saat lagi.</p></div></main></body></html>';
            }
        }
    }

    public function storeGuestbook(string $slug): ResponseInterface
    {
        $slug = $this->safeSlug($slug);
        try {
            $page = $this->publishedPage($slug);
        } catch (FreePageExpiredException) {
            return $this->response
                ->setStatusCode(410)
                ->setJSON([
                    'success' => false,
                    'message' => 'Link undangan free ini sudah expired.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $rateLimitResponse = $this->guestbookRateLimitResponse($slug);
        if ($rateLimitResponse !== null) {
            return $rateLimitResponse;
        }

        $rules = [
            'guest_name' => 'required|min_length[2]|max_length[120]',
            'message' => 'required|min_length[2]|max_length[800]',
            'attendance' => 'required|in_list[hadir,tidak_hadir,ragu]',
            'sticker' => 'permit_empty|max_length[120]|regex_match[/^sticker[0-9]{3}\.gif$/i]',
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Mohon periksa kembali isian guestbook.',
                    'errors' => $this->validator->getErrors(),
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        $guestName = trim(strip_tags((string) $this->request->getPost('guest_name')));
        $message = trim(strip_tags((string) $this->request->getPost('message')));
        $attendance = (string) $this->request->getPost('attendance');
        $sticker = $this->safeSticker((string) $this->request->getPost('sticker'));

        try {
            $guestBookModel = new GuestBookModel();
            $guestBookModel->insert([
                'landing_page_id' => (int) $page['id'],
                'guest_name' => $guestName,
                'message' => $message,
                'sticker' => $sticker ?: null,
                'attendance' => $attendance,
                'is_approved' => 1,
            ]);

            $comment = $this->normalizeComment([
                'id' => (int) $guestBookModel->getInsertID(),
                'guest_name' => $guestName,
                'message' => $message,
                'sticker' => $sticker,
                'attendance' => $attendance,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->prependCommentCache($slug, $comment);
        } catch (DatabaseException) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Guestbook belum siap. Silakan coba lagi nanti.',
                    'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Ucapan kamu berhasil dikirim.',
            'comment' => $comment,
            'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
        ]);
    }

    private function publishedPage(string $slug): array
    {
        $landingPageModel = new LandingPageModel();
        $page = $landingPageModel->findPublishedBySlug($slug);

        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Undangan tidak ditemukan.');
        }

        if ($this->freePageExpired($page)) {
            throw new FreePageExpiredException();
        }

        return $page;
    }

    private function publicNotFound(
        string $headline = 'Undangan tidak ditemukan',
        string $message = 'Link undangan ini belum aktif, sudah dihapus, atau belum dipublish oleh pemiliknya.'
    ): string
    {
        $this->response
            ->setStatusCode(404)
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Cache-Control', 'no-store, max-age=0, no-cache');

        return view('errors/public_not_found', [
            'title' => 'Undangan tidak ditemukan',
            'headline' => $headline,
            'message' => $message,
            'plansUrl' => site_url('plans'),
            'templatesUrl' => site_url('templates'),
            'homeUrl' => site_url('/'),
        ]);
    }

    private function freePageExpired(array $page): bool
    {
        if (! $this->pageUsesFreeTemplate($page)) {
            return false;
        }

        $userId = (int) ($page['user_id'] ?? 0);
        if ($userId > 0 && (new UserSubscriptionModel())->activeWithPlanByUser($userId) !== null) {
            return false;
        }

        $expiresAt = $this->freePageExpiresAt($page);
        if ($expiresAt === null) {
            return false;
        }

        return strtotime($expiresAt) < time();
    }

    private function freePageExpiresAt(array $page): ?string
    {
        $userId = (int) ($page['user_id'] ?? 0);
        $db = Database::connect();

        if ($userId > 0 && $db->tableExists('free_publish_entitlements')) {
            $entitlement = (new FreePublishEntitlementModel())->where('user_id', $userId)->first();
            if ($entitlement !== null && ! empty($entitlement['expires_at'])) {
                return (string) $entitlement['expires_at'];
            }
        }

        $publishedAt = strtotime((string) ($page['published_at'] ?? ''));
        if ($publishedAt === false) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime('+1 month', $publishedAt));
    }

    private function pageUsesFreeTemplate(array $page): bool
    {
        $templateId = (int) ($page['template_id'] ?? 0);
        if ($templateId <= 0) {
            return false;
        }

        $db = Database::connect();
        if (! $db->tableExists('templates') || ! in_array('is_premium', $db->getFieldNames('templates'), true)) {
            return false;
        }

        $template = $db->table('templates')
            ->select('is_premium')
            ->where('id', $templateId)
            ->get()
            ->getRowArray();

        return $template !== null && (int) ($template['is_premium'] ?? 1) === 0;
    }

    private function safeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw PageNotFoundException::forPageNotFound('Undangan tidak ditemukan.');
        }

        return $slug;
    }

    private function safeSticker(string $sticker): string
    {
        $sticker = basename(trim($sticker));

        return preg_match(self::STICKER_PATTERN, $sticker) ? $sticker : '';
    }

    private function commentsForPage(string $slug, int $landingPageId, GuestBookModel $model): array
    {
        $cached = $this->readCommentCache($slug);
        if ($cached !== null) {
            return $cached;
        }

        $comments = array_map(
            fn (array $entry): array => $this->normalizeComment($entry),
            $model->latestApprovedByLandingPage($landingPageId, self::COMMENT_CACHE_LIMIT)
        );
        $this->writeCommentCache($slug, $comments);

        return $comments;
    }

    private function normalizeComment(array $entry): array
    {
        $sticker = $this->safeSticker((string) ($entry['sticker'] ?? ''));

        return [
            'id' => (int) ($entry['id'] ?? 0),
            'guest_name' => trim(strip_tags((string) ($entry['guest_name'] ?? ''))),
            'message' => trim(strip_tags((string) ($entry['message'] ?? ''))),
            'attendance' => in_array(($entry['attendance'] ?? ''), ['hadir', 'tidak_hadir', 'ragu'], true)
                ? (string) $entry['attendance']
                : 'ragu',
            'sticker' => $sticker,
            'sticker_url' => $sticker !== '' ? aa_asset_url('assets/stiker/' . $sticker) : '',
            'created_at' => (string) ($entry['created_at'] ?? date('Y-m-d H:i:s')),
        ];
    }

    private function commentCachePath(string $slug): string
    {
        return WRITEPATH . 'comments' . DIRECTORY_SEPARATOR . $slug . '.json';
    }

    private function readCommentCache(string $slug): ?array
    {
        $path = $this->commentCachePath($slug);
        if (! is_file($path)) {
            return null;
        }

        $json = file_get_contents($path);
        $comments = json_decode((string) $json, true);

        if (! is_array($comments)) {
            return null;
        }

        return array_slice(array_map(fn (array $entry): array => $this->normalizeComment($entry), $comments), 0, self::COMMENT_CACHE_LIMIT);
    }

    private function writeCommentCache(string $slug, array $comments): void
    {
        $dir = WRITEPATH . 'comments';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->commentCachePath($slug),
            json_encode(array_slice($comments, 0, self::COMMENT_CACHE_LIMIT), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function prependCommentCache(string $slug, array $comment): void
    {
        $comments = $this->readCommentCache($slug) ?? [];
        array_unshift($comments, $comment);
        $this->writeCommentCache($slug, $comments);
    }

    private function guestbookRateLimitResponse(string $slug): ?ResponseInterface
    {
        $ip = (string) $this->request->getIPAddress();
        $now = time();
        $dir = WRITEPATH . 'guestbook-rate-limits';

        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            log_message('warning', 'Guestbook rate limit directory cannot be created: {path}', ['path' => $dir]);
            return null;
        }

        $fingerprint = hash('sha256', $ip . '|' . $slug);
        $path = $dir . DIRECTORY_SEPARATOR . $fingerprint . '.json';
        $handle = @fopen($path, 'c+');
        if (! is_resource($handle)) {
            log_message('warning', 'Guestbook rate limit file cannot be opened: {path}', ['path' => $path]);
            return null;
        }

        $blocked = false;
        $retryAfter = 0;

        try {
            if (! flock($handle, LOCK_EX)) {
                return null;
            }

            $raw = stream_get_contents($handle);
            $timestamps = json_decode((string) $raw, true);
            $timestamps = is_array($timestamps)
                ? array_values(array_filter(array_map('intval', $timestamps), static fn (int $timestamp): bool => $timestamp > 0))
                : [];

            $timestamps = array_values(array_filter($timestamps, static fn (int $timestamp): bool => ($now - $timestamp) < 3600));
            $recentTenMinutes = array_values(array_filter($timestamps, static fn (int $timestamp): bool => ($now - $timestamp) < 600));

            if (count($recentTenMinutes) >= 5 || count($timestamps) >= 15) {
                $blocked = true;
                $oldestRelevant = count($recentTenMinutes) >= 5 ? min($recentTenMinutes) : min($timestamps);
                $window = count($recentTenMinutes) >= 5 ? 600 : 3600;
                $retryAfter = max(60, ($oldestRelevant + $window) - $now);
            } else {
                $timestamps[] = $now;
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, json_encode($timestamps, JSON_UNESCAPED_SLASHES));
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        if (! $blocked) {
            return null;
        }

        return $this->response
            ->setStatusCode(429)
            ->setHeader('Retry-After', (string) $retryAfter)
            ->setJSON([
                'success' => false,
                'message' => 'Terlalu banyak ucapan dikirim dari jaringan ini. Coba lagi beberapa menit lagi.',
                'csrf_hash' => function_exists('csrf_hash') ? csrf_hash() : null,
            ]);
    }
}

class FreePageExpiredException extends \RuntimeException
{
}
