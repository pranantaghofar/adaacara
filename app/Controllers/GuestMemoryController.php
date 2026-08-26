<?php

namespace App\Controllers;

use App\Libraries\ProductEntitlementService;
use App\Models\GuestMemoryModel;
use App\Models\LandingPageModel;
use App\Models\PhotoboothCustomDomainModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class GuestMemoryController extends BaseController
{
    private const PER_PAGE = 12;
    private const MAX_FINAL_KB = 1600;
    private const MAX_THUMB_KB = 400;
    private const MAX_AUDIO_KB = 3072;
    private const MAX_UPLOADS_PER_10_MINUTES = 6;

    public function index(string $slug): ResponseInterface|string
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'));
        }

        return $this->renderIndex($page, false);
    }

    public function indexCustomDomain(): ResponseInterface|string
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'));
        }

        return $this->renderIndex($page, true);
    }

    private function renderIndex(array $page, bool $useCustomDomainEndpoints): ResponseInterface|string
    {
        $isReady = $this->tablesReady();
        $isEnabled = $isReady && $this->isEnabledForUser((int) ($page['user_id'] ?? 0));
        $lang = aa_current_lang();
        $pageTitle = (string) ($page['title'] ?? 'AdaAcara');

        return view('guest_memory/index', [
            'page' => $page,
            'isReady' => $isReady,
            'isEnabled' => $isEnabled,
            'currentLang' => $lang,
            'useCustomDomainEndpoints' => $useCustomDomainEndpoints,
            'metaTitle' => aa_t('gm.meta_title', 'Kenangan Tamu - {title}', ['title' => $pageTitle], $lang),
        ]);
    }

    public function qr(string $slug): ResponseInterface|string
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(aa_t('gm_qr.error.not_found', 'Undangan tidak ditemukan.'));
        }

        return $this->renderQr($page, site_url('u/' . $slug . '/memories/qr/download'));
    }

    public function qrCustomDomain(): ResponseInterface|string
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(aa_t('gm_qr.error.not_found', 'Undangan tidak ditemukan.'));
        }

        return $this->renderQr($page, '/memories/qr/download');
    }

    private function renderQr(array $page, string $downloadBaseUrl): ResponseInterface|string
    {
        $lang = aa_current_lang();
        $selectedStyle = $this->normalizeQrPrintStyle((string) ($this->request->getGet('style') ?? 'floral-rose'));
        $memoriesUrl = aa_lang_url($this->photoboothMemoriesUrl($page), $lang);
        $downloadUrl = aa_lang_url($downloadBaseUrl, $lang);
        $pageTitle = (string) ($page['title'] ?? 'AdaAcara');

        return view('guest_memory/qr', [
            'page' => $page,
            'memoriesUrl' => $memoriesUrl,
            'qrImageUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=720x720&margin=18&data=' . rawurlencode($memoriesUrl),
            'downloadUrl' => $downloadUrl,
            'selectedStyle' => $selectedStyle,
            'currentLang' => $lang,
            'metaTitle' => aa_t('gm_qr.meta_title', 'QR Photobooth - {title}', ['title' => $pageTitle], $lang),
        ]);
    }

    public function downloadQr(string $slug): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            return $this->jsonError(aa_t('gm_qr.error.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->downloadQrForPage($page);
    }

    public function downloadQrCustomDomain(): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            return $this->jsonError(aa_t('gm_qr.error.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->downloadQrForPage($page);
    }

    private function downloadQrForPage(array $page): ResponseInterface
    {
        if (! function_exists('imagecreatetruecolor')) {
            return $this->jsonError(aa_t('gm_qr.error.generator_unavailable', 'Generator QR belum tersedia di server.'), 503);
        }

        $lang = aa_current_lang();
        $style = $this->normalizeQrPrintStyle((string) ($this->request->getGet('style') ?? 'floral-rose'));
        $isPrint = (string) ($this->request->getGet('print') ?? '') === '1';
        $memoriesUrl = aa_lang_url($this->photoboothMemoriesUrl($page), $lang);
        $qrData = $this->fetchQrImage($memoriesUrl);
        if ($qrData === '') {
            return $this->jsonError(aa_t('gm_qr.error.qr_unavailable', 'QR belum bisa dibuat. Coba lagi beberapa saat.', [], $lang), 503);
        }

        $png = $this->renderQrPrintCard($page, $memoriesUrl, $qrData, $style);
        if ($png === '') {
            return $this->jsonError(aa_t('gm_qr.error.card_unavailable', 'Kartu QR belum bisa dibuat.', [], $lang), 500);
        }

        $slug = (string) ($page['slug'] ?? 'photobooth');
        $filename = 'qr-photobooth-' . preg_replace('/[^a-z0-9-]+/i', '-', $slug) . '-' . $style . '.png';

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Content-Disposition', ($isPrint ? 'inline' : 'attachment') . '; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, max-age=300')
            ->setBody($png);
    }

    public function listMemories(string $slug): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->listMemoriesForPage($page);
    }

    public function listMemoriesCustomDomain(): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->listMemoriesForPage($page);
    }

    private function listMemoriesForPage(array $page): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError(aa_t('gm.api.not_active', 'Kenangan Tamu belum aktif.'), 503);
        }
        if (! $this->isEnabledForUser((int) ($page['user_id'] ?? 0))) {
            return $this->jsonError(aa_t('gm.api.not_enabled', 'Kenangan Tamu belum aktif untuk undangan ini.'), 403);
        }

        $pageNumber = max(1, (int) ($this->request->getGet('page') ?? 1));
        $keyword = trim(strip_tags((string) ($this->request->getGet('q') ?? '')));
        $limit = self::PER_PAGE;
        $offset = ($pageNumber - 1) * $limit;
        $memoryModel = new GuestMemoryModel();
        $items = $memoryModel->approvedForPage((int) $page['id'], $limit + 1, $offset, $keyword);
        $hasMore = count($items) > $limit;
        $items = array_slice($items, 0, $limit);

        return $this->response->setJSON([
            'success' => true,
            'items' => array_map([$this, 'normalizeMemory'], $items),
            'has_more' => $hasMore,
            'next_page' => $hasMore ? $pageNumber + 1 : null,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function frames(string $slug): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->framesForResolvedPage($page);
    }

    public function framesCustomDomain(): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->framesForResolvedPage($page);
    }

    private function framesForResolvedPage(array $page): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError(aa_t('gm.api.not_active', 'Kenangan Tamu belum aktif.'), 503);
        }
        if (! $this->isEnabledForUser((int) ($page['user_id'] ?? 0))) {
            return $this->jsonError(aa_t('gm.api.not_enabled', 'Kenangan Tamu belum aktif untuk undangan ini.'), 403);
        }

        return $this->response->setJSON([
            'success' => true,
            'frames' => $this->framesForPage($page),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function upload(string $slug): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->uploadForPage($page);
    }

    public function uploadCustomDomain(): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->uploadForPage($page);
    }

    private function uploadForPage(array $page): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError(aa_t('gm.api.not_active', 'Kenangan Tamu belum aktif.'), 503);
        }
        if (! $this->isEnabledForUser((int) ($page['user_id'] ?? 0))) {
            return $this->jsonError(aa_t('gm.api.not_enabled', 'Kenangan Tamu belum aktif untuk undangan ini.'), 403);
        }

        $guestName = trim(strip_tags((string) $this->request->getPost('guest_name')));
        if (mb_strlen($guestName) < 2 || mb_strlen($guestName) > 120) {
            return $this->jsonError(aa_t('gm.api.name_length', 'Nama wajib diisi 2 sampai 120 karakter.'), 422);
        }
        $guestEmail = strtolower(trim((string) $this->request->getPost('guest_email')));
        if ($guestEmail !== '' && ! filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError(aa_t('gm.upload.email_invalid', 'Format email belum valid.'), 422);
        }
        $wishText = $this->normalizeWishText((string) $this->request->getPost('wish_text'));

        $ipAddress = (string) $this->request->getIPAddress();
        $memoryModel = new GuestMemoryModel();
        if ($memoryModel->nameExistsForPage((int) $page['id'], $guestName)) {
            return $this->jsonError(aa_t('gm.upload.name_taken', 'Gunakan nama lain atau tambahkan inisial. Nama ini sudah dipakai.'), 422);
        }
        if ($memoryModel->recentUploadCount((int) $page['id'], $ipAddress, 10) >= self::MAX_UPLOADS_PER_10_MINUTES) {
            return $this->jsonError(aa_t('gm.api.too_many_uploads', 'Terlalu banyak upload. Coba lagi beberapa menit lagi.'), 429);
        }

        $frames = $this->framesForPage($page);
        if ($frames === []) {
            return $this->jsonError(aa_t('gm.frame.missing', 'Silahkan buat tampilan Frame di adaAcara Studio dahulu'), 422);
        }

        $frameId = $this->normalizeFrameId((int) ($this->request->getPost('frame_id') ?? 1), $frames);

        $photoFile = $this->request->getFile('photo');
        $thumbFile = $this->request->getFile('thumbnail');
        $audioFile = $this->request->getFile('audio');
        if (! $photoFile || ! $photoFile->isValid()) {
            return $this->jsonError(aa_t('gm.api.invalid_frame_photo', 'Foto hasil frame belum valid.'), 422);
        }

        try {
            $pageId = (int) $page['id'];
            $dir = $this->uploadDir($pageId);
            $photoPath = $this->storeImage($photoFile, $dir, $pageId, self::MAX_FINAL_KB);
            $thumbPath = null;
            if ($thumbFile && $thumbFile->isValid()) {
                $thumbPath = $this->storeImage($thumbFile, $dir, $pageId, self::MAX_THUMB_KB, 'thumb_');
            }
            $audioPath = null;
            $audioDuration = 0;
            if ($audioFile && $audioFile->isValid()) {
                $audioPath = $this->storeAudio($audioFile, $dir, $pageId);
                $audioDuration = max(0, min(90, (int) ($this->request->getPost('audio_duration') ?? 0)));
            }

            $payload = [
                'landing_page_id' => (int) $page['id'],
                'frame_id' => $frameId,
                'guest_name' => $guestName,
                'photo' => $photoPath,
                'thumbnail' => $thumbPath,
                'audio' => $audioPath,
                'audio_duration' => $audioDuration,
                'status' => 'approved',
                'ip_address' => $ipAddress,
                'user_agent' => mb_substr((string) $this->request->getUserAgent(), 0, 255),
            ];
            $printCode = '';
            if ($memoryModel->hasPrintCodeColumn()) {
                $printCode = $this->generatePrintCode((int) $page['id'], $guestName, $memoryModel);
                $payload['print_code'] = $printCode;
            }
            if ($guestEmail !== '' && $memoryModel->hasGuestEmailColumn()) {
                $payload['guest_email'] = $guestEmail;
            }
            if ($wishText !== '' && $memoryModel->hasWishTextColumn()) {
                $payload['wish_text'] = $wishText;
            }

            $insertId = $memoryModel->insert($payload, true);

            $memory = $memoryModel->find($insertId);
            $emailSent = false;
            if ($guestEmail !== '' && $printCode !== '') {
                $emailSent = $this->sendPrintCodeEmail($guestEmail, $guestName, $page, $printCode);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $this->uploadSuccessMessage($printCode, $guestEmail, $emailSent),
                'item' => $this->normalizeMemory($memory ?: []),
                'print_code' => $printCode,
                'print_code_email_sent' => $emailSent,
                'csrf_hash' => csrf_hash(),
            ]);
        } catch (\RuntimeException $exception) {
            log_message('warning', 'Guest Memories upload rejected: {message}', ['message' => $exception->getMessage()]);

            return $this->jsonError($exception->getMessage(), 422);
        } catch (Throwable $exception) {
            log_message('error', 'Guest Memories upload failed: {message}', ['message' => $exception->getMessage()]);

            return $this->jsonError(aa_t('gm.api.upload_small', 'Upload belum berhasil. Coba lagi dengan foto yang lebih kecil.'), 500);
        }
    }

    public function printAccess(string $slug, int $id): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->printAccessForPage($page, $id);
    }

    public function printAccessCustomDomain(int $id): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->printAccessForPage($page, $id);
    }

    private function printAccessForPage(array $page, int $id): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError(aa_t('gm.api.not_active', 'Kenangan Tamu belum aktif.'), 503);
        }
        if (! $this->isEnabledForUser((int) ($page['user_id'] ?? 0))) {
            return $this->jsonError(aa_t('gm.api.not_enabled', 'Kenangan Tamu belum aktif untuk undangan ini.'), 403);
        }

        $memoryModel = new GuestMemoryModel();
        if (! $memoryModel->hasPrintCodeColumn()) {
            return $this->jsonError(aa_t('gm.api.print_inactive', 'Kode cetak belum aktif. Jalankan update database Kenangan Tamu lebih dulu.'), 503);
        }

        $submittedCode = $this->normalizePrintCode((string) $this->request->getPost('print_code'));
        if ($submittedCode === '') {
            return $this->jsonError(aa_t('gm.api.enter_print_code', 'Masukkan kode cetak untuk foto ini.'), 422);
        }

        $memory = $memoryModel
            ->where('id', $id)
            ->where('landing_page_id', (int) $page['id'])
            ->where('status', 'approved')
            ->first();

        if (! is_array($memory)) {
            return $this->jsonError(aa_t('gm.api.photo_not_found', 'Foto memories tidak ditemukan.'), 404);
        }

        $expectedCode = $this->normalizePrintCode((string) ($memory['print_code'] ?? ''));
        if ($expectedCode === '') {
            return $this->jsonError(aa_t('gm.api.print_unavailable', 'Kode cetak belum tersedia untuk foto ini. Upload ulang foto untuk mendapatkan kode cetak.'), 409);
        }
        if (! hash_equals($expectedCode, $submittedCode)) {
            return $this->jsonError(aa_t('gm.print.wrong_code', 'Kode cetak belum sesuai.'), 403);
        }

        $photoUrl = $this->assetUrl((string) ($memory['photo'] ?? ''));

        return $this->response->setJSON([
            'success' => true,
            'photo' => $photoUrl,
            'filename' => 'photobooth-' . preg_replace('/[^\w-]+/', '-', mb_strtolower((string) ($memory['guest_name'] ?? 'memory'))) . '.jpg',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function markPrinted(string $slug, int $id): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->markPrintedForPage($page, $id);
    }

    public function markPrintedCustomDomain(int $id): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->markPrintedForPage($page, $id);
    }

    private function markPrintedForPage(array $page, int $id): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError(aa_t('gm.api.not_active', 'Kenangan Tamu belum aktif.'), 503);
        }
        if (! $this->isEnabledForUser((int) ($page['user_id'] ?? 0))) {
            return $this->jsonError(aa_t('gm.api.not_enabled', 'Kenangan Tamu belum aktif untuk undangan ini.'), 403);
        }

        $memoryModel = new GuestMemoryModel();
        if (! $memoryModel->hasPrintCodeColumn()) {
            return $this->jsonError(aa_t('gm.api.print_inactive', 'Kode cetak belum aktif. Jalankan update database Kenangan Tamu lebih dulu.'), 503);
        }
        if (! $memoryModel->hasPrintTrackingColumns()) {
            return $this->jsonError(aa_t('gm.api.print_limit_inactive', 'Limit cetak belum aktif. Jalankan update database Kenangan Tamu lebih dulu.'), 503);
        }

        $submittedCode = $this->normalizePrintCode((string) $this->request->getPost('print_code'));
        if ($submittedCode === '') {
            return $this->jsonError(aa_t('gm.api.enter_print_code', 'Masukkan kode cetak untuk foto ini.'), 422);
        }

        $memory = $memoryModel
            ->where('id', $id)
            ->where('landing_page_id', (int) $page['id'])
            ->where('status', 'approved')
            ->first();

        if (! is_array($memory)) {
            return $this->jsonError(aa_t('gm.api.photo_not_found', 'Foto memories tidak ditemukan.'), 404);
        }

        $expectedCode = $this->normalizePrintCode((string) ($memory['print_code'] ?? ''));
        if ($expectedCode === '') {
            return $this->jsonError(aa_t('gm.api.print_unavailable', 'Kode cetak belum tersedia untuk foto ini. Upload ulang foto untuk mendapatkan kode cetak.'), 409);
        }
        if (! hash_equals($expectedCode, $submittedCode)) {
            return $this->jsonError(aa_t('gm.print.wrong_code', 'Kode cetak belum sesuai.'), 403);
        }
        if (! empty($memory['print_used_at'])) {
            return $this->jsonError(aa_t('gm.api.already_printed', 'Foto ini sudah pernah dicetak.'), 409);
        }

        $db = db_connect();
        $updated = $db->table('guest_memories')
            ->where('id', (int) $memory['id'])
            ->where('landing_page_id', (int) $page['id'])
            ->where('status', 'approved')
            ->where('print_used_at', null)
            ->update([
                'print_used_at' => date('Y-m-d H:i:s'),
                'print_used_ip' => (string) $this->request->getIPAddress(),
                'print_used_user_agent' => mb_substr((string) $this->request->getUserAgent(), 0, 255),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if (! $updated || $db->affectedRows() < 1) {
            return $this->jsonError(aa_t('gm.api.already_printed', 'Foto ini sudah pernah dicetak.'), 409);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => aa_t('gm.api.print_access_approved', 'Akses cetak disetujui.'),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function delete(string $slug, int $id): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->publishedPage($slug);
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->deleteForPage($page, $id);
    }

    public function deleteCustomDomain(int $id): ResponseInterface
    {
        helper('aa_i18n');

        $page = $this->activeCustomDomainPage();
        if ($page === null) {
            return $this->jsonError(aa_t('gm.api.not_found', 'Undangan tidak ditemukan.'), 404);
        }

        return $this->deleteForPage($page, $id);
    }

    private function deleteForPage(array $page, int $id): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError(aa_t('gm.api.not_active', 'Kenangan Tamu belum aktif.'), 503);
        }
        if (! $this->isEnabledForUser((int) ($page['user_id'] ?? 0))) {
            return $this->jsonError(aa_t('gm.api.not_enabled', 'Kenangan Tamu belum aktif untuk undangan ini.'), 403);
        }

        $guestName = trim(strip_tags((string) $this->request->getPost('guest_name')));
        if ($guestName === '') {
            return $this->jsonError(aa_t('gm.api.delete_name_required', 'Tulis nama yang dipakai saat upload foto.'), 422);
        }

        $memoryModel = new GuestMemoryModel();
        $memory = $memoryModel
            ->where('id', $id)
            ->where('landing_page_id', (int) $page['id'])
            ->where('status', 'approved')
            ->first();

        if (! is_array($memory)) {
            return $this->jsonError(aa_t('gm.api.photo_not_found', 'Foto memories tidak ditemukan.'), 404);
        }

        $expectedName = mb_strtolower(trim((string) ($memory['guest_name'] ?? '')));
        $submittedName = mb_strtolower($guestName);
        if ($submittedName === '' || $submittedName !== $expectedName) {
            return $this->jsonError(aa_t('gm.delete.name_mismatch', 'Nama tidak cocok. Gunakan nama yang sama saat upload foto.'), 403);
        }

        $memoryModel->update((int) $memory['id'], ['status' => 'hidden']);

        return $this->response->setJSON([
            'success' => true,
            'message' => aa_t('gm.delete.success', 'Foto memories berhasil dihapus dari galeri.'),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    private function generatePrintCode(int $landingPageId, string $guestName, GuestMemoryModel $memoryModel): string
    {
        $asciiName = function_exists('iconv') ? (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $guestName) ?: $guestName) : $guestName;
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]+/', '', $asciiName) ?? '');
        if ($prefix === '') {
            $prefix = 'TAMU';
        }
        $prefix = substr($prefix, 0, 8);

        $db = db_connect();
        for ($attempt = 0; $attempt < 12; $attempt++) {
            $suffix = (string) random_int(1000, 9999);
            $code = $prefix . '-' . $suffix;
            $exists = $db->table('guest_memories')
                ->where('landing_page_id', $landingPageId)
                ->where('print_code', $code)
                ->countAllResults() > 0;
            if (! $exists) {
                return $code;
            }
        }

        return $prefix . '-' . (string) random_int(1000, 9999);
    }

    private function normalizePrintCode(string $code): string
    {
        $code = strtoupper(trim($code));
        $code = preg_replace('/[^A-Z0-9]/', '', $code) ?? '';
        if (strlen($code) > 8) {
            return substr($code, 0, -4) . '-' . substr($code, -4);
        }

        return $code;
    }

    private function normalizeWishText(string $wishText): string
    {
        $wishText = trim(strip_tags($wishText));
        $wishText = preg_replace('/\s+/u', ' ', $wishText) ?? '';
        if (mb_strlen($wishText) > 500) {
            $wishText = mb_substr($wishText, 0, 500);
        }

        return trim($wishText);
    }

    private function uploadSuccessMessage(string $printCode, string $guestEmail, bool $emailSent): string
    {
        if ($printCode === '') {
            return aa_t('gm.toast.success', 'Momen berhasil ditambahkan.');
        }
        if ($guestEmail !== '' && $emailSent) {
            return aa_t('gm.api.success_code_email', 'Momen berhasil ditambahkan. Kode cetak: {code}. Kode juga dikirim ke email.', ['code' => $printCode]);
        }
        if ($guestEmail !== '') {
            return aa_t('gm.api.success_code_email_failed', 'Momen berhasil ditambahkan. Kode cetak: {code}. Email kode belum terkirim, simpan kode ini.', ['code' => $printCode]);
        }

        return aa_t('gm.api.success_code', 'Momen berhasil ditambahkan. Kode cetak: {code}', ['code' => $printCode]);
    }

    private function sendPrintCodeEmail(string $email, string $guestName, array $page, string $printCode): bool
    {
        $apiKey = $this->brevoApiKey();
        $emailConfig = config('Email');
        $fromEmail = (string) ($emailConfig->fromEmail ?? '');
        $fromName = (string) (($emailConfig->fromName ?? '') ?: 'AdaAcara');
        if ($apiKey === '' || $fromEmail === '') {
            log_message('warning', 'Guest Memories print code email skipped. config_ready={ready}', [
                'ready' => $apiKey !== '' && $fromEmail !== '' ? 'yes' : 'no',
            ]);
            return false;
        }

        $memoriesUrl = $this->photoboothMemoriesUrl($page);
        $html = view('emails/guest_memory_print_code', [
            'guestName' => $guestName,
            'pageTitle' => (string) ($page['title'] ?? 'Undangan'),
            'printCode' => $printCode,
            'memoriesUrl' => $memoriesUrl,
        ]);

        try {
            $client = service('curlrequest', [
                'timeout' => 12,
                'http_errors' => false,
            ]);
            $response = $client->post('https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'accept' => 'application/json',
                    'api-key' => $apiKey,
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'name' => $fromName,
                        'email' => $fromEmail,
                    ],
                    'to' => [
                        [
                            'email' => $email,
                            'name' => $guestName,
                        ],
                    ],
                    'subject' => 'Kode Print Photobooth AdaAcara',
                    'htmlContent' => $html,
                    'textContent' => "Kode print photobooth {$printCode}\n{$memoriesUrl}",
                ],
            ]);
            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                log_message('warning', 'Guest Memories print code email rejected. status={status} body={body}', [
                    'status' => (string) $statusCode,
                    'body' => mb_substr((string) $response->getBody(), 0, 1000),
                ]);
                return false;
            }

            return true;
        } catch (Throwable $exception) {
            log_message('warning', 'Guest Memories print code email failed: {message}', [
                'message' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    private function brevoApiKey(): string
    {
        foreach (['BREVO_API_KEY', 'brevo.apiKey', 'brevo.api_key', 'email.brevoApiKey'] as $key) {
            $value = trim((string) (env($key, '') ?: getenv($key) ?: ($_ENV[$key] ?? '') ?: ($_SERVER[$key] ?? '')));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function fetchQrImage(string $url): string
    {
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=900x900&margin=18&data=' . rawurlencode($url);
        if (function_exists('curl_init')) {
            $curl = curl_init($qrUrl);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'AdaAcara QR Generator',
            ]);
            $body = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            curl_close($curl);
            if (is_string($body) && $status >= 200 && $status < 300 && strlen($body) > 1000) {
                return $body;
            }
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 12,
                'follow_location' => 1,
                'header' => "User-Agent: AdaAcara QR Generator\r\n",
            ],
        ]);
        $body = @file_get_contents($qrUrl, false, $context);

        return is_string($body) && strlen($body) > 1000 ? $body : '';
    }

    private function normalizeQrPrintStyle(string $style): string
    {
        $style = strtolower(trim($style));
        $allowed = ['floral-rose', 'luxury-navy', 'botanical-sage', 'blue-blossom'];

        return in_array($style, $allowed, true) ? $style : 'floral-rose';
    }

    private function qrPrintStyleConfig(string $style): array
    {
        $styles = [
            'floral-rose' => [
                'bgTop' => [255, 248, 244],
                'bgBottom' => [255, 244, 240],
                'paper' => [255, 253, 248],
                'ink' => [151, 80, 82],
                'muted' => [88, 84, 84],
                'accent' => [205, 145, 136],
                'accentDark' => [176, 102, 100],
                'soft' => [255, 241, 239],
                'line' => [224, 169, 160],
                'mode' => 'rose',
            ],
            'luxury-navy' => [
                'bgTop' => [6, 23, 41],
                'bgBottom' => [3, 13, 28],
                'paper' => [8, 29, 50],
                'ink' => [239, 191, 103],
                'muted' => [255, 246, 222],
                'accent' => [218, 159, 63],
                'accentDark' => [177, 119, 40],
                'soft' => [255, 246, 220],
                'line' => [214, 151, 58],
                'mode' => 'navy',
            ],
            'botanical-sage' => [
                'bgTop' => [250, 248, 239],
                'bgBottom' => [238, 244, 234],
                'paper' => [255, 253, 246],
                'ink' => [54, 80, 62],
                'muted' => [96, 74, 47],
                'accent' => [155, 137, 82],
                'accentDark' => [93, 123, 91],
                'soft' => [240, 247, 237],
                'line' => [196, 159, 87],
                'mode' => 'sage',
            ],
            'blue-blossom' => [
                'bgTop' => [224, 239, 250],
                'bgBottom' => [246, 251, 255],
                'paper' => [255, 255, 252],
                'ink' => [17, 52, 88],
                'muted' => [100, 132, 164],
                'accent' => [201, 159, 84],
                'accentDark' => [92, 124, 156],
                'soft' => [234, 246, 253],
                'line' => [204, 166, 96],
                'mode' => 'blue',
            ],
        ];

        return $styles[$this->normalizeQrPrintStyle($style)];
    }

    private function renderQrPrintCard(array $page, string $memoriesUrl, string $qrData, string $style = 'floral-rose'): string
    {
        $qr = @imagecreatefromstring($qrData);
        if (! $qr) {
            return '';
        }

        $width = 1080;
        $height = 1536;
        $config = $this->qrPrintStyleConfig($style);
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $bgTop = $config['bgTop'];
        $bgBottom = $config['bgBottom'];
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $r = (int) round($bgTop[0] + (($bgBottom[0] - $bgTop[0]) * $ratio));
            $g = (int) round($bgTop[1] + (($bgBottom[1] - $bgTop[1]) * $ratio));
            $b = (int) round($bgTop[2] + (($bgBottom[2] - $bgTop[2]) * $ratio));
            imageline($image, 0, $y, $width, $y, imagecolorallocate($image, $r, $g, $b));
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $paper = imagecolorallocate($image, ...$config['paper']);
        $ink = imagecolorallocate($image, ...$config['ink']);
        $muted = imagecolorallocate($image, ...$config['muted']);
        $accent = imagecolorallocate($image, ...$config['accent']);
        $accentDark = imagecolorallocate($image, ...$config['accentDark']);
        $soft = imagecolorallocate($image, ...$config['soft']);
        $cardShadow = imagecolorallocatealpha($image, 15, 23, 42, 112);
        $line = imagecolorallocate($image, ...$config['line']);

        $isDark = ($config['mode'] ?? '') === 'navy';
        if ($isDark) {
            $this->drawQrCornerStars($image, $accent);
            imagesetthickness($image, 3);
            imagerectangle($image, 24, 24, 1056, 1508, $line);
            imagesetthickness($image, 1);
            imagerectangle($image, 44, 44, 1036, 1488, $line);
            $this->drawQrBotanical($image, $line, 'navy');
        } else {
            $this->drawQrBotanical($image, $line, (string) $config['mode']);
            $this->roundedRect($image, 38, 38, 1042, 1312, 70, $line, 2);
            $this->roundedRect($image, 56, 56, 1024, 1294, 58, imagecolorallocatealpha($image, $config['line'][0], $config['line'][1], $config['line'][2], 72), 1);
        }

        $cardTop = $isDark ? 108 : 104;
        $cardBottom = $isDark ? 1245 : 1248;
        $this->filledRoundedRect($image, 104, $cardTop + 14, 976, $cardBottom + 16, 66, $cardShadow);
        $this->filledRoundedRect($image, 94, $cardTop, 986, $cardBottom, 66, $isDark ? imagecolorallocate($image, 4, 20, 36) : $paper);
        $this->roundedRect($image, 94, $cardTop, 986, $cardBottom, 66, $line, 2);
        if ($isDark) {
            $this->roundedRect($image, 118, $cardTop + 24, 962, $cardBottom - 24, 44, $line, 2);
        }

        $fontRegular = $this->qrFont(false);
        $fontBold = $this->qrFont(true);

        $this->drawQrDivider($image, 260, $line, $accent);
        $this->filledRoundedRect($image, 355, 318, 725, 374, 28, $isDark ? imagecolorallocate($image, 5, 31, 54) : $soft);
        $this->roundedRect($image, 355, 318, 725, 374, 28, $line, 2);
        $this->drawCenteredText($image, aa_t('gm_qr.card_title', 'QR PHOTOBOOTH'), 540, 355, 22, $isDark ? $accent : $accentDark, $fontBold, 3.2, true);

        $titleY = $isDark ? 505 : 498;
        $this->drawCenteredText($image, aa_t('gm_qr.card_title_main_1', 'Digital'), 540, $titleY, 94, $ink, $fontRegular ?: $fontBold, -3.5, true);
        $this->drawCenteredText($image, aa_t('gm_qr.card_title_main_2', 'Photobooth'), 540, $titleY + 104, 92, $ink, $fontRegular ?: $fontBold, -3.0, true);

        $pageTitle = trim((string) ($page['title'] ?? 'AdaAcara'));
        $eventDate = (string) ($page['event_date'] ?? '');
        $eventDateLabel = $eventDate !== '' && strtotime($eventDate) !== false ? date('d • m • Y', strtotime($eventDate)) : '';
        $subtitle = $pageTitle . ($eventDateLabel !== '' ? ' • ' . $eventDateLabel : '');
        $this->drawCenteredText($image, mb_strimwidth($subtitle, 0, 72, '...'), 540, $titleY + 158, 25, $muted, $fontBold, .6, true);
        $this->drawQrDivider($image, $titleY + 190, $line, $accent);

        $qrBox = 370;
        $qrX = (int) (($width - $qrBox) / 2);
        $qrY = $isDark ? 692 : 700;
        $this->filledRoundedRect($image, $qrX - 24, $qrY - 24, $qrX + $qrBox + 24, $qrY + $qrBox + 24, 38, $cardShadow);
        $this->filledRoundedRect($image, $qrX - 24, $qrY - 24, $qrX + $qrBox + 24, $qrY + $qrBox + 24, 38, $white);
        $this->roundedRect($image, $qrX - 24, $qrY - 24, $qrX + $qrBox + 24, $qrY + $qrBox + 24, 38, $line, 3);
        imagecopyresampled($image, $qr, $qrX, $qrY, 0, 0, $qrBox, $qrBox, imagesx($qr), imagesy($qr));

        $scanY = 1140;
        $this->drawCenteredText($image, aa_t('gm_qr.scan', 'Scan & capture the moment'), 540, $scanY, 43, $ink, $fontBold, -1.0, true);
        $this->drawCenteredText($image, aa_t('gm_qr.description', 'Capture your photo at our event'), 540, $scanY + 48, 23, $muted, $fontBold, 0, true);

        $this->filledRoundedRect($image, 150, 1218, 930, 1302, 22, $isDark ? imagecolorallocate($image, 5, 31, 54) : $soft);
        $this->roundedRect($image, 150, 1218, 930, 1302, 22, $line, 2);
        $this->drawQrInstructionIcons($image, $accentDark, $muted, $fontBold);
        $this->drawCenteredText($image, aa_t('gm_qr.powered', 'Powered by adaAcara.com'), 540, 1330, 20, $isDark ? $accent : $accentDark, $fontBold, 1.2, true);
        $this->drawCenteredText($image, aa_t('gm_qr.print_tip_short_1', 'Print this QR code and place it on the guest table,'), 540, 1380, 17, $muted, $fontRegular ?: $fontBold, 0, false);
        $this->drawCenteredText($image, aa_t('gm_qr.print_tip_short_2', 'entrance, photobooth, or event area.'), 540, 1406, 17, $muted, $fontRegular ?: $fontBold, 0, false);

        ob_start();
        imagepng($image, null, 8);
        $png = (string) ob_get_clean();
        @imagedestroy($qr);
        @imagedestroy($image);

        return $png;
    }

    private function drawQrDivider($image, int $y, int $lineColor, int $accentColor): void
    {
        imagesetthickness($image, 2);
        imageline($image, 315, $y, 505, $y, $lineColor);
        imageline($image, 575, $y, 765, $y, $lineColor);
        imagefilledellipse($image, 540, $y, 12, 12, $accentColor);
        imagearc($image, 520, $y, 32, 20, 300, 55, $accentColor);
        imagearc($image, 560, $y, 32, 20, 125, 240, $accentColor);
        imagesetthickness($image, 1);
    }

    private function drawQrCornerStars($image, int $color): void
    {
        $points = [[76, 82], [1004, 82], [76, 1456], [1004, 1456]];
        foreach ($points as [$x, $y]) {
            imagefilledpolygon($image, [$x, $y - 28, $x + 8, $y - 8, $x + 28, $y, $x + 8, $y + 8, $x, $y + 28, $x - 8, $y + 8, $x - 28, $y, $x - 8, $y - 8], 8, $color);
        }
    }

    private function drawQrBotanical($image, int $color, string $mode): void
    {
        $alphaColor = imagecolorallocatealpha($image, 255, 255, 255, 108);
        if ($mode !== 'navy') {
            for ($i = 0; $i < 8; $i++) {
                imagefilledellipse($image, 70 + ($i * 18), 110 + ($i * 24), 72 - ($i * 2), 34, $alphaColor);
                imagefilledellipse($image, 1010 - ($i * 18), 120 + ($i * 24), 72 - ($i * 2), 34, $alphaColor);
                imagefilledellipse($image, 108 + ($i * 20), 1400 - ($i * 18), 68 - ($i * 2), 30, $alphaColor);
                imagefilledellipse($image, 972 - ($i * 20), 1398 - ($i * 18), 68 - ($i * 2), 30, $alphaColor);
            }
        }

        $branches = [
            [150, 250, 60, 690],
            [930, 250, 1020, 690],
            [150, 1125, 70, 1430],
            [930, 1125, 1010, 1430],
        ];
        imagesetthickness($image, 2);
        foreach ($branches as [$x1, $y1, $x2, $y2]) {
            imageline($image, $x1, $y1, $x2, $y2, $color);
            for ($i = 0; $i < 7; $i++) {
                $t = $i / 7;
                $x = (int) round($x1 + (($x2 - $x1) * $t));
                $y = (int) round($y1 + (($y2 - $y1) * $t));
                $dir = $x1 < 540 ? 1 : -1;
                imagearc($image, $x + ($dir * 16), $y + 2, 34, 18, $dir > 0 ? 210 : 330, $dir > 0 ? 35 : 155, $color);
                imagearc($image, $x - ($dir * 10), $y + 20, 30, 16, $dir > 0 ? 35 : 210, $dir > 0 ? 155 : 330, $color);
            }
        }
        imagesetthickness($image, 1);
    }

    private function drawQrInstructionIcons($image, int $iconColor, int $textColor, ?string $font): void
    {
        $items = [
            [230, 'Open phone', 'camera'],
            [385, 'Choose', 'Frame'],
            [540, 'Photo', ''],
            [695, 'Upload', ''],
            [850, 'Download /', 'Print'],
        ];

        foreach ($items as $index => [$x, $labelA, $labelB]) {
            imageellipse($image, $x, 1248, 50, 50, $iconColor);
            if ($index === 0) {
                imagerectangle($image, $x - 12, 1230, $x + 12, 1266, $iconColor);
                imagefilledellipse($image, $x, 1260, 4, 4, $iconColor);
            } elseif ($index === 1) {
                imagerectangle($image, $x - 16, 1234, $x + 16, 1264, $iconColor);
                imageline($image, $x - 11, 1258, $x, 1246, $iconColor);
                imageline($image, $x, 1246, $x + 12, 1258, $iconColor);
            } elseif ($index === 2) {
                imagerectangle($image, $x - 17, 1238, $x + 17, 1263, $iconColor);
                imageellipse($image, $x, 1251, 18, 18, $iconColor);
                imageline($image, $x - 8, 1238, $x - 3, 1230, $iconColor);
                imageline($image, $x - 3, 1230, $x + 9, 1230, $iconColor);
                imageline($image, $x + 9, 1230, $x + 14, 1238, $iconColor);
            } elseif ($index === 3) {
                imagearc($image, $x - 8, 1250, 26, 24, 190, 30, $iconColor);
                imagearc($image, $x + 10, 1250, 28, 26, 150, 350, $iconColor);
                imageline($image, $x, 1265, $x, 1238, $iconColor);
                imageline($image, $x, 1238, $x - 9, 1248, $iconColor);
                imageline($image, $x, 1238, $x + 9, 1248, $iconColor);
            } else {
                imageline($image, $x, 1230, $x, 1260, $iconColor);
                imageline($image, $x, 1260, $x - 11, 1248, $iconColor);
                imageline($image, $x, 1260, $x + 11, 1248, $iconColor);
                imageline($image, $x - 18, 1266, $x + 18, 1266, $iconColor);
            }

            if ($index < 4) {
                imageline($image, $x + 52, 1248, $x + 94, 1248, $iconColor);
                imageline($image, $x + 94, 1248, $x + 84, 1239, $iconColor);
                imageline($image, $x + 94, 1248, $x + 84, 1257, $iconColor);
            }

            $this->drawCenteredText($image, $labelA, $x, 1290, 15, $textColor, $font, 0, true);
            if ($labelB !== '') {
                $this->drawCenteredText($image, $labelB, $x, 1310, 15, $textColor, $font, 0, true);
            }
        }
    }

    private function qrFont(bool $bold): ?string
    {
        if (! function_exists('imagettfbbox')) {
            return null;
        }

        $candidates = $bold ? [
            FCPATH . 'assets/fonts/PlusJakartaSans-VariableFont_wght.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
            '/Library/Fonts/Arial Bold.ttf',
        ] : [
            FCPATH . 'assets/fonts/PlusJakartaSans-VariableFont_wght.ttf',
            FCPATH . 'assets/fonts/PlusJakartaSans-Regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Regular.ttf',
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/Library/Fonts/Arial.ttf',
        ];

        $uploadedFonts = glob(FCPATH . 'uploads/fonts/*.{ttf,otf}', GLOB_BRACE) ?: [];
        $candidates = array_merge($candidates, $uploadedFonts);

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_readable($candidate) && @imagettfbbox(12, 0, $candidate, 'AdaAcara')) {
                return $candidate;
            }
        }

        return null;
    }

    private function drawCenteredText($image, string $text, int $centerX, int $baselineY, int $size, int $color, ?string $font, float $letterSpacing = 0, bool $thicken = false): void
    {
        if ($font && function_exists('imagettfbbox')) {
            $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            if ($letterSpacing !== 0 && count($chars) > 1) {
                $width = 0;
                foreach ($chars as $char) {
                    $box = imagettfbbox($size, 0, $font, $char);
                    $width += ($box[2] ?? 0) - ($box[0] ?? 0);
                }
                $width += (count($chars) - 1) * $letterSpacing;
                $x = (int) round($centerX - ($width / 2));
                foreach ($chars as $char) {
                    $this->drawTtfText($image, $size, $x, $baselineY, $color, $font, $char, $thicken);
                    $box = imagettfbbox($size, 0, $font, $char);
                    $x += (int) round((($box[2] ?? 0) - ($box[0] ?? 0)) + $letterSpacing);
                }
                return;
            }

            $box = imagettfbbox($size, 0, $font, $text);
            $textWidth = ($box[2] ?? 0) - ($box[0] ?? 0);
            $this->drawTtfText($image, $size, (int) round($centerX - ($textWidth / 2)), $baselineY, $color, $font, $text, $thicken);
            return;
        }

        $fallbackText = str_replace(['•', '→', '–', '—'], ['-', '>', '-', '-'], $text);
        $fontSize = 5;
        $scale = max(1.0, $size / 9);
        $srcWidth = max(1, imagefontwidth($fontSize) * strlen($fallbackText) + 8);
        $srcHeight = imagefontheight($fontSize) + 8;
        $dstWidth = (int) round($srcWidth * $scale);
        $dstHeight = (int) round($srcHeight * $scale);
        $dstX = (int) round($centerX - ($dstWidth / 2));
        $dstY = (int) round($baselineY - ($dstHeight * .78));

        $tmp = imagecreatetruecolor($srcWidth, $srcHeight);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
        imagefilledrectangle($tmp, 0, 0, $srcWidth, $srcHeight, $transparent);
        $channels = imagecolorsforindex($image, $color);
        $tmpColor = imagecolorallocate($tmp, (int) $channels['red'], (int) $channels['green'], (int) $channels['blue']);
        imagestring($tmp, $fontSize, 4, 4, $fallbackText, $tmpColor);
        imagecopyresampled($image, $tmp, $dstX, $dstY, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
        imagedestroy($tmp);
    }

    private function drawTtfText($image, int $size, int $x, int $baselineY, int $color, string $font, string $text, bool $thicken): void
    {
        imagettftext($image, $size, 0, $x, $baselineY, $color, $font, $text);
        if (! $thicken) {
            return;
        }

        imagettftext($image, $size, 0, $x + 1, $baselineY, $color, $font, $text);
        imagettftext($image, $size, 0, $x, $baselineY + 1, $color, $font, $text);
        if ($size >= 34) {
            imagettftext($image, $size, 0, $x + 2, $baselineY, $color, $font, $text);
            imagettftext($image, $size, 0, $x + 1, $baselineY + 1, $color, $font, $text);
        }
    }

    private function filledRoundedRect($image, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    private function roundedRect($image, int $x1, int $y1, int $x2, int $y2, int $radius, int $color, int $thickness = 1): void
    {
        imagesetthickness($image, $thickness);
        imageline($image, $x1 + $radius, $y1, $x2 - $radius, $y1, $color);
        imageline($image, $x1 + $radius, $y2, $x2 - $radius, $y2, $color);
        imageline($image, $x1, $y1 + $radius, $x1, $y2 - $radius, $color);
        imageline($image, $x2, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagearc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color);
        imagearc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color);
        imagearc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color);
        imagearc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color);
        imagesetthickness($image, 1);
    }

    private function publishedPage(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '' || ! preg_match('/^[a-z0-9][a-z0-9-]{1,180}$/i', $slug)) {
            return null;
        }

        $model = new LandingPageModel();
        $page = $model->select('id,user_id,title,slug,event_date,status,og_image,published_at,published_editor_json')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        return is_array($page) ? $page : null;
    }

    private function activeCustomDomainPage(): ?array
    {
        $host = $this->normalizedRequestHost();
        if ($host === '') {
            return null;
        }

        try {
            $domainModel = new PhotoboothCustomDomainModel();
            if (! $domainModel->tableReady()) {
                return null;
            }

            $domains = [$host];
            if (str_starts_with($host, 'www.')) {
                $domains[] = substr($host, 4);
            } else {
                $domains[] = 'www.' . $host;
            }

            $domainRequest = $domainModel
                ->whereIn('domain', array_values(array_unique($domains)))
                ->where('target_type', 'memories')
                ->where('status', 'active')
                ->orderBy('id', 'DESC')
                ->first();

            if (! is_array($domainRequest)) {
                return null;
            }

            $model = new LandingPageModel();
            $page = $model->select('id,user_id,title,slug,event_date,status,og_image,published_at,published_editor_json')
                ->where('id', (int) ($domainRequest['landing_page_id'] ?? 0))
                ->where('status', 'published')
                ->first();

            return is_array($page) ? $page : null;
        } catch (Throwable $exception) {
            log_message('warning', 'Guest Memories custom domain resolve failed for host {host}: {message}', [
                'host' => $host,
                'message' => $exception->getMessage(),
            ]);
        }

        return null;
    }

    private function normalizedRequestHost(): string
    {
        $host = strtolower(trim((string) $this->request->getServer('HTTP_HOST')));
        if ($host === '') {
            $host = strtolower(trim((string) $this->request->getServer('SERVER_NAME')));
        }
        $host = preg_replace('/:\d+$/', '', $host) ?? '';
        $host = trim($host, ". \t\n\r\0\x0B");

        if ($host === '' || strlen($host) > 253 || ! preg_match('/^[a-z0-9.-]+$/', $host)) {
            return '';
        }

        return $host;
    }

    private function photoboothMemoriesUrl(array $page): string
    {
        $slug = (string) ($page['slug'] ?? '');
        $standardUrl = site_url('u/' . $slug . '/memories');

        try {
            $domainModel = new PhotoboothCustomDomainModel();
            $domainRequest = $domainModel->latestForPage((int) ($page['id'] ?? 0));
            $domain = trim((string) ($domainRequest['domain'] ?? ''));
            if ($domain === '' || (string) ($domainRequest['status'] ?? '') !== 'active') {
                return $standardUrl;
            }

            return 'https://' . rtrim($domain, '/') . '/memories';
        } catch (Throwable $exception) {
            log_message('warning', 'Guest Memories custom domain URL fallback for page {page_id}: {message}', [
                'page_id' => (string) ($page['id'] ?? ''),
                'message' => $exception->getMessage(),
            ]);
        }

        return $standardUrl;
    }

    private function framesForPage(array $page): array
    {
        return $this->editorPhotoboothFrames($page);
    }

    private function editorPhotoboothFrames(array $page): array
    {
        $editorJson = trim((string) ($page['published_editor_json'] ?? ''));
        if ($editorJson === '' || strlen($editorJson) > 8 * 1024 * 1024) {
            return [];
        }

        try {
            $data = json_decode($editorJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            log_message('warning', 'Guest Memories photobooth frame JSON invalid for page {page_id}: {message}', [
                'page_id' => (string) ($page['id'] ?? ''),
                'message' => $exception->getMessage(),
            ]);

            return [];
        }

        $frames = $data['photoboothFrames'] ?? [];
        if (! is_array($frames)) {
            return [];
        }

        $normalized = [];
        foreach (array_values($frames) as $index => $frame) {
            if ($index >= 3 || ! is_array($frame)) {
                break;
            }
            if (($frame['hidden'] ?? false) === true) {
                continue;
            }

            $frameData = $this->normalizeEditorPhotoboothFrame($frame, $index);
            if ($frameData !== null) {
                $normalized[] = $frameData;
            }
        }

        return $normalized;
    }

    private function normalizeEditorPhotoboothFrame(array $frame, int $index): ?array
    {
        $artboard = is_array($frame['artboard'] ?? null) ? $frame['artboard'] : [];
        $width = (int) round((float) ($artboard['width'] ?? $frame['width'] ?? 1080));
        $height = (int) round((float) ($artboard['height'] ?? $frame['height'] ?? 1350));
        $width = max(320, min(2400, $width));
        $height = max(320, min(4200, $height));

        $objects = array_values(array_filter($frame['objects'] ?? [], 'is_array'));
        if ($objects === []) {
            return null;
        }

        $declaredSlotCount = max(1, min(3, (int) ($frame['slotCount'] ?? $frame['slot_count'] ?? ($index + 1))));
        $metadataSlots = $this->extractEditorPhotoSlotsFromMetadata($frame, $width, $height);
        $objectSlots = $this->extractEditorPhotoSlots($objects);
        $slots = $metadataSlots;
        if (count($objectSlots) >= $declaredSlotCount || count($objectSlots) > count($metadataSlots)) {
            $slots = $objectSlots;
        }
        if ($slots === []) {
            return null;
        }

        $slotCount = max(1, min(3, max($declaredSlotCount, count($slots))));
        $slots = array_slice($slots, 0, $slotCount);
        $title = trim((string) ($frame['title'] ?? ''));

        return [
            'id' => $index + 1,
            'source_id' => (string) ($frame['id'] ?? ''),
            'title' => $title !== '' ? $title : ($slotCount . ' Foto'),
            'ratio' => $width . ':' . $height,
            'width' => $width,
            'height' => $height,
            'slot_count' => $slotCount,
            'photo_slots' => $slots,
            'renderer' => 'fabric-page',
            'fabric' => [
                'objects' => array_slice($objects, 0, 160),
                'background' => (string) ($frame['background'] ?? $frame['backgroundColor'] ?? '#ffffff'),
                'backgroundColor' => (string) ($frame['backgroundColor'] ?? $frame['background'] ?? '#ffffff'),
                'backgroundImage' => $frame['backgroundImage'] ?? null,
                'artboard' => [
                    'width' => $width,
                    'height' => $height,
                ],
            ],
        ];
    }

    private function extractEditorPhotoSlots(array $objects): array
    {
        $slots = [];
        foreach ($objects as $object) {
            if (! is_array($object) || ($object['customType'] ?? '') !== 'photobooth-photo-slot') {
                continue;
            }

            $scaleX = max(0.01, (float) ($object['scaleX'] ?? 1));
            $scaleY = max(0.01, (float) ($object['scaleY'] ?? 1));
            $slot = [
                'index' => (int) ($object['aaPhotoboothSlotIndex'] ?? (count($slots) + 1)),
                'x' => (float) ($object['left'] ?? 0),
                'y' => (float) ($object['top'] ?? 0),
                'width' => max(40, (float) ($object['width'] ?? 0) * $scaleX),
                'height' => max(40, (float) ($object['height'] ?? 0) * $scaleY),
                'radius' => max(0, (float) ($object['rx'] ?? $object['ry'] ?? 0) * max($scaleX, $scaleY)),
            ];
            $slots[] = $slot;
        }

        usort($slots, static function (array $a, array $b): int {
            $indexCompare = ((int) ($a['index'] ?? 0)) <=> ((int) ($b['index'] ?? 0));
            if ($indexCompare !== 0) {
                return $indexCompare;
            }

            return ((float) ($a['y'] ?? 0)) <=> ((float) ($b['y'] ?? 0))
                ?: ((float) ($a['x'] ?? 0)) <=> ((float) ($b['x'] ?? 0));
        });

        return array_map(static function (array $slot, int $index): array {
            unset($slot['index']);
            $slot['index'] = $index + 1;

            return $slot;
        }, array_slice($slots, 0, 3), array_keys(array_slice($slots, 0, 3)));
    }

    private function extractEditorPhotoSlotsFromMetadata(array $frame, int $width, int $height): array
    {
        $source = $frame['photoSlots'] ?? $frame['photo_slots'] ?? null;
        if (! is_array($source)) {
            return [];
        }

        $slots = [];
        foreach (array_values($source) as $index => $slot) {
            if (! is_array($slot) || $index >= 3) {
                break;
            }

            $x = (float) ($slot['x'] ?? $slot['left'] ?? 0);
            $y = (float) ($slot['y'] ?? $slot['top'] ?? 0);
            $slotWidth = (float) ($slot['width'] ?? 0);
            $slotHeight = (float) ($slot['height'] ?? 0);

            if ($slotWidth < 40 || $slotHeight < 40) {
                continue;
            }

            $slots[] = [
                'index' => $index + 1,
                'x' => max(0, min($width - 40, $x)),
                'y' => max(0, min($height - 40, $y)),
                'width' => max(40, min($width, $slotWidth)),
                'height' => max(40, min($height, $slotHeight)),
                'radius' => max(0, (float) ($slot['radius'] ?? 0)),
            ];
        }

        return $slots;
    }

    private function tablesReady(): bool
    {
        try {
            $db = db_connect();

            return $db->tableExists('guest_memories') && $db->tableExists('guest_memory_user_settings');
        } catch (Throwable) {
            return false;
        }
    }

    private function isEnabledForUser(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        try {
            $setting = db_connect()->table('guest_memory_user_settings')
                ->select('is_enabled')
                ->where('user_id', $userId)
                ->get(1)
                ->getRowArray();

            return ((int) ($setting['is_enabled'] ?? 0)) === 1
                || (new ProductEntitlementService())->hasActive($userId, ProductEntitlementService::PHOTOBOOTH_STANDALONE);
        } catch (Throwable) {
            return false;
        }
    }

    private function uploadDir(int $pageId): string
    {
        $dir = FCPATH . 'uploads/events/' . $pageId . '/guest-memory';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    private function storeImage($file, string $dir, int $pageId, int $maxKb, string $prefix = ''): string
    {
        if ($file->getSizeByUnit('kb') > $maxKb) {
            throw new \RuntimeException(aa_t('gm.upload.file_too_large', 'File terlalu besar.'));
        }

        $imageInfo = @getimagesize($file->getTempName());
        $mime = is_array($imageInfo) ? (string) ($imageInfo['mime'] ?? '') : '';
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => '',
        };
        if ($extension === '') {
            throw new \RuntimeException(aa_t('gm.upload.file_type', 'Format file tidak didukung.'));
        }

        $filename = $prefix . bin2hex(random_bytes(16)) . '.' . $extension;
        $file->move($dir, $filename);

        return 'uploads/events/' . $pageId . '/guest-memory/' . $filename;
    }

    private function storeAudio($file, string $dir, int $pageId): string
    {
        if ($file->getSizeByUnit('kb') > self::MAX_AUDIO_KB) {
            throw new \RuntimeException(aa_t('gm.upload.audio_too_large', 'Audio terlalu besar.'));
        }

        $mime = (string) ($file->getMimeType() ?? '');
        $extension = match ($mime) {
            'audio/webm' => 'webm',
            'audio/ogg' => 'ogg',
            'audio/mpeg' => 'mp3',
            'audio/mp4', 'audio/x-m4a' => 'm4a',
            'audio/wav', 'audio/x-wav' => 'wav',
            default => '',
        };
        if ($extension === '') {
            throw new \RuntimeException(aa_t('gm.upload.audio_type', 'Format audio tidak didukung.'));
        }

        $filename = 'wish_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $file->move($dir, $filename);

        return 'uploads/events/' . $pageId . '/guest-memory/' . $filename;
    }

    private function builtInFrames(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Kisah Kekal',
                'ratio' => '4:5',
                'width' => 1080,
                'height' => 1350,
                'style' => 'classic',
                'slot_count' => 1,
                'accent' => '#31401f',
                'paper' => '#f8f4ea',
                'photo_slots' => [
                    ['x' => 165, 'y' => 150, 'width' => 750, 'height' => 560, 'radius' => 0],
                ],
            ],
            [
                'id' => 2,
                'title' => 'Garden Duo',
                'ratio' => '108:220',
                'width' => 1080,
                'height' => 2200,
                'style' => 'duo_stack',
                'slot_count' => 2,
                'accent' => '#425b33',
                'paper' => '#f4f1e7',
                'photo_slots' => [
                    ['x' => 165, 'y' => 260, 'width' => 750, 'height' => 560, 'radius' => 0],
                    ['x' => 165, 'y' => 880, 'width' => 750, 'height' => 560, 'radius' => 0],
                ],
            ],
            [
                'id' => 3,
                'title' => 'Olive Trio',
                'ratio' => '108:300',
                'width' => 1080,
                'height' => 3000,
                'style' => 'trio_stack',
                'slot_count' => 3,
                'accent' => '#34451f',
                'paper' => '#fbf7ef',
                'photo_slots' => [
                    ['x' => 165, 'y' => 300, 'width' => 750, 'height' => 560, 'radius' => 0],
                    ['x' => 165, 'y' => 920, 'width' => 750, 'height' => 560, 'radius' => 0],
                    ['x' => 165, 'y' => 1540, 'width' => 750, 'height' => 560, 'radius' => 0],
                ],
            ],
        ];
    }

    private function normalizeFrameId(int $frameId, array $frames): int
    {
        $ids = array_map(static fn (array $frame): int => (int) $frame['id'], $frames);

        return in_array($frameId, $ids, true) ? $frameId : (int) ($ids[0] ?? 1);
    }

    private function normalizeMemory(array $memory): array
    {
        $photo = (string) ($memory['photo'] ?? '');
        $thumbnail = (string) ($memory['thumbnail'] ?? '');

        return [
            'id' => (int) ($memory['id'] ?? 0),
            'guest_name' => (string) ($memory['guest_name'] ?? 'Tamu'),
            'photo' => $this->assetUrl($photo),
            'thumbnail' => $this->assetUrl($thumbnail !== '' ? $thumbnail : $photo),
            'audio' => $this->assetUrl((string) ($memory['audio'] ?? '')),
            'audio_duration' => (int) ($memory['audio_duration'] ?? 0),
            'wish_text' => $this->normalizeWishText((string) ($memory['wish_text'] ?? '')),
            'created_at' => $this->formatDate((string) ($memory['created_at'] ?? '')),
        ];
    }

    private function assetUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return base_url(ltrim($path, '/'));
    }

    private function formatDate(string $date): string
    {
        helper('aa_datetime');
        if (function_exists('aa_format_wib_datetime')) {
            return aa_format_wib_datetime($date);
        }

        $timestamp = strtotime($date);
        if ($timestamp <= 0) {
            return '';
        }

        return date('d/m/Y H:i', $timestamp);
    }

    private function jsonError(string $message, int $status = 400): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'success' => false,
            'message' => $message,
            'csrf_hash' => csrf_hash(),
        ]);
    }
}
