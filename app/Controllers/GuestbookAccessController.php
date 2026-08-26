<?php

namespace App\Controllers;

use App\Models\GuestbookAccessLinkModel;
use App\Models\LandingPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class GuestbookAccessController extends BaseController
{
    public function show(string $token): string
    {
        $token = strtolower(trim($token));
        if (! preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            throw PageNotFoundException::forPageNotFound('Akses RSVP tidak ditemukan.');
        }

        $accessModel = new GuestbookAccessLinkModel();
        $access = $accessModel->activeForToken($token);
        if ($access === null) {
            throw PageNotFoundException::forPageNotFound('Akses RSVP tidak ditemukan.');
        }

        $page = (new LandingPageModel())->find((int) ($access['landing_page_id'] ?? 0));
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Undangan tidak ditemukan.');
        }

        $session = session();
        $sessionKey = 'rsvp_access_verified_' . hash('sha256', $token);
        $expectedCode = $this->accessCode($token);
        $isVerified = (bool) $session->get($sessionKey);
        $accessError = '';

        if (strtolower($this->request->getMethod()) === 'post') {
            $submittedCode = $this->normalizeAccessCode((string) $this->request->getPost('access_code'));

            if (hash_equals($expectedCode, $submittedCode)) {
                $session->set($sessionKey, true);
                $isVerified = true;
            } else {
                $accessError = 'Kode akses tidak sesuai. Periksa kembali kode dari pemilik undangan.';
            }
        }

        if (! $isVerified) {
            return view('dashboard/rsvp_access', [
                'page' => $page,
                'guestbookEntries' => [],
                'attendanceSummary' => [],
                'rsvpLocked' => true,
                'accessError' => $accessError,
            ]);
        }

        $db = Database::connect();
        $guestbookEntries = [];
        if ($db->tableExists('guest_books')) {
            $builder = $db->table('guest_books')
                ->where('landing_page_id', (int) $page['id']);

            $fields = $db->getFieldNames('guest_books');
            if (in_array('created_at', $fields, true)) {
                $builder->orderBy('created_at', 'DESC');
            }

            $guestbookEntries = $builder
                ->get()
                ->getResultArray();
        }

        $accessModel->update((int) $access['id'], [
            'last_accessed_at' => date('Y-m-d H:i:s'),
        ]);

        return view('dashboard/rsvp_access', [
            'page' => $page,
            'guestbookEntries' => $guestbookEntries,
            'attendanceSummary' => $this->attendanceSummary($guestbookEntries),
            'rsvpLocked' => false,
            'accessError' => '',
        ]);
    }

    private function accessCode(string $token): string
    {
        return strtoupper(substr($token, 0, 6) . substr($token, 6, 6));
    }

    private function normalizeAccessCode(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
    }

    private function attendanceSummary(array $guestbookEntries): array
    {
        $summary = [
            'hadir' => 0,
            'tidak_hadir' => 0,
            'ragu' => 0,
        ];
        $aliases = [
            'attending' => 'hadir',
            'present' => 'hadir',
            'yes' => 'hadir',
            'not_attending' => 'tidak_hadir',
            'not-attending' => 'tidak_hadir',
            'absent' => 'tidak_hadir',
            'no' => 'tidak_hadir',
            'pending' => 'ragu',
            'maybe' => 'ragu',
            'unknown' => 'ragu',
        ];

        foreach ($guestbookEntries as $entry) {
            $attendance = strtolower(trim((string) ($entry['attendance'] ?? $entry['attendance_status'] ?? 'ragu')));
            $attendance = $aliases[$attendance] ?? $attendance;

            if (! array_key_exists($attendance, $summary)) {
                $attendance = 'ragu';
            }

            $summary[$attendance]++;
        }

        return $summary;
    }
}
