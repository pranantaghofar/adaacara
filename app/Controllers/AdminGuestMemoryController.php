<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AdminGuestMemoryController extends BaseController
{
    public function index(): ResponseInterface|string
    {
        helper('admin_permission');
        if ($access = admin_require('admin.guest_memories.view', 'guest-memories')) {
            return $access;
        }

        $items = [];
        $isReady = $this->tablesReady();
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $keyword = trim((string) ($this->request->getGet('q') ?? ''));

        if ($isReady) {
            $builder = db_connect()->table('guest_memories gm')
                ->select('gm.*, lp.title AS page_title, lp.slug AS page_slug')
                ->join('landing_pages lp', 'lp.id = gm.landing_page_id', 'left');

            if (in_array($status, ['approved', 'pending', 'hidden', 'rejected'], true)) {
                $builder->where('gm.status', $status);
            }
            if ($keyword !== '') {
                $builder->groupStart()
                    ->like('gm.guest_name', $keyword)
                    ->orLike('lp.title', $keyword)
                    ->orLike('lp.slug', $keyword)
                    ->groupEnd();
            }

            $items = $builder->orderBy('gm.created_at', 'DESC')
                ->orderBy('gm.id', 'DESC')
                ->limit(200)
                ->get()
                ->getResultArray();
        }

        return view('admin/guest_memories/index', [
            'adminTitle' => 'Guest Memories',
            'adminKicker' => 'Memories',
            'adminIcon' => 'image',
            'adminActive' => 'guestMemories',
            'items' => $items,
            'isReady' => $isReady,
            'filters' => [
                'status' => $status,
                'q' => $keyword,
            ],
        ]);
    }

    public function approve(int $id): RedirectResponse|ResponseInterface
    {
        return $this->updateStatus($id, 'approved', 'Momen ditampilkan kembali.');
    }

    public function reject(int $id): RedirectResponse|ResponseInterface
    {
        return $this->updateStatus($id, 'rejected', 'Momen ditandai ditolak.');
    }

    public function hide(int $id): RedirectResponse|ResponseInterface
    {
        return $this->updateStatus($id, 'hidden', 'Momen disembunyikan.');
    }

    public function delete(int $id): RedirectResponse|ResponseInterface
    {
        helper('admin_permission');
        if ($access = admin_require('admin.guest_memories.delete', 'guest-memories')) {
            return $access;
        }

        return $this->updateStatus($id, 'hidden', 'Momen disembunyikan tanpa menghapus file.');
    }

    private function updateStatus(int $id, string $status, string $message): RedirectResponse|ResponseInterface
    {
        helper('admin_permission');
        if ($access = admin_require('admin.guest_memories.manage', 'guest-memories')) {
            return $access;
        }

        if (! $this->tablesReady()) {
            return redirect()->to(site_url('admin/guest-memories'))->with('error', 'Tabel Guest Memories belum tersedia.');
        }

        try {
            db_connect()->table('guest_memories')
                ->where('id', max(0, $id))
                ->update([
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return redirect()->back()->with('success', $message);
        } catch (Throwable $exception) {
            log_message('error', 'Admin Guest Memories status failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->back()->with('error', 'Status belum berhasil diubah.');
        }
    }

    private function tablesReady(): bool
    {
        try {
            $db = db_connect();

            return $db->tableExists('guest_memories');
        } catch (Throwable) {
            return false;
        }
    }
}
