<?php

namespace App\Libraries;

use App\Models\CreatorProfileModel;
use App\Models\MarketplaceTemplateActivityLogModel;
use App\Models\MarketplaceTemplateModel;
use App\Models\MarketplaceTemplateReviewModel;
use App\Models\NotificationModel;
use CodeIgniter\Database\BaseConnection;

class MarketplaceReviewService
{
    public const REVIEW_CHECKLIST = [
        'thumbnail_ok' => 'Thumbnail sesuai dan tidak rusak',
        'preview_ok' => 'Preview bisa dibuka',
        'content_safe' => 'Desain tidak mengandung konten terlarang',
        'metadata_complete' => 'Metadata lengkap',
        'category_match' => 'Kategori sesuai',
        'price_valid' => 'Harga sesuai aturan',
        'not_spam' => 'Tidak duplikat/spam',
        'quality_ok' => 'Kualitas desain layak marketplace',
        'copyright_ok' => 'Tidak melanggar hak cipta/brand pihak lain',
    ];

    private BaseConnection $db;
    private MarketplaceTemplateModel $marketplaceModel;
    private MarketplaceTemplateReviewModel $reviewModel;
    private MarketplaceTemplateActivityLogModel $logModel;
    private NotificationModel $notificationModel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->marketplaceModel = new MarketplaceTemplateModel();
        $this->reviewModel = new MarketplaceTemplateReviewModel();
        $this->logModel = new MarketplaceTemplateActivityLogModel();
        $this->notificationModel = new NotificationModel();
    }

    public function submit(array $template, int $actorId, string $creatorMessage = ''): bool
    {
        if (! in_array((string) ($template['marketplace_status'] ?? ''), ['draft', 'rejected', 'changes_requested'], true)) {
            return false;
        }

        $this->db->transStart();
        $this->marketplaceModel->update((int) $template['id'], [
            'marketplace_status' => 'submitted',
            'approval_status' => 'pending',
            'submitted_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'archived_at' => null,
        ]);
        $this->reviewModel->insert([
            'marketplace_template_id' => (int) $template['id'],
            'status' => 'pending',
            'creator_message' => $this->cleanNote($creatorMessage),
        ]);
        $this->log((int) $template['id'], $actorId, 'creator', 'submit', (string) $template['marketplace_status'], 'submitted', $creatorMessage);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function withdraw(array $template, int $actorId, string $note = ''): bool
    {
        if (($template['marketplace_status'] ?? '') !== 'submitted' || ($template['approval_status'] ?? '') !== 'pending') {
            return false;
        }

        $this->db->transStart();
        $this->marketplaceModel->update((int) $template['id'], [
            'marketplace_status' => 'draft',
            'approval_status' => 'not_submitted',
        ]);
        $this->reviewModel->insert([
            'marketplace_template_id' => (int) $template['id'],
            'status' => 'withdrawn',
            'creator_message' => $this->cleanNote($note),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log((int) $template['id'], $actorId, 'creator', 'withdraw', 'submitted', 'draft', $note);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function archiveByCreator(array $template, int $actorId): bool
    {
        if (! in_array((string) ($template['marketplace_status'] ?? ''), ['draft', 'rejected', 'changes_requested'], true)) {
            return false;
        }

        $from = (string) $template['marketplace_status'];
        $this->db->transStart();
        $this->marketplaceModel->update((int) $template['id'], [
            'marketplace_status' => 'archived',
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log((int) $template['id'], $actorId, 'creator', 'archive', $from, 'archived');
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function restoreByCreator(array $template, int $actorId): bool
    {
        if (($template['marketplace_status'] ?? '') !== 'archived') {
            return false;
        }

        $this->db->transStart();
        $this->marketplaceModel->update((int) $template['id'], [
            'marketplace_status' => 'draft',
            'approval_status' => 'not_submitted',
            'archived_at' => null,
        ]);
        $this->log((int) $template['id'], $actorId, 'creator', 'restore', 'archived', 'draft');
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function approve(array $template, int $adminId, array $checklist, string $adminNotes = ''): bool
    {
        if (! $this->canAdminReview($template) || ! $this->checklistComplete($checklist)) {
            return false;
        }

        $this->db->transStart();
        $this->marketplaceModel->update((int) $template['id'], [
            'marketplace_status' => 'approved',
            'approval_status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => $adminId,
            'rejection_reason' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'archived_at' => null,
        ]);
        $this->reviewModel->insert([
            'marketplace_template_id' => (int) $template['id'],
            'reviewer_id' => $adminId,
            'status' => 'approved',
            'checklist' => json_encode($checklist, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'admin_notes' => $this->cleanNote($adminNotes),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log((int) $template['id'], $adminId, 'admin', 'approve', 'submitted', 'approved', $adminNotes);
        $this->notifyCreator($template, 'marketplace_template_approved', 'Template Disetujui', 'Template "' . ($template['title'] ?? '-') . '" sudah disetujui admin.');
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function reject(array $template, int $adminId, string $reason, array $checklist = [], string $adminNotes = ''): bool
    {
        if (! $this->canAdminReview($template) || trim($reason) === '') {
            return false;
        }

        $cleanReason = $this->cleanNote($reason);
        $this->db->transStart();
        $this->marketplaceModel->update((int) $template['id'], [
            'marketplace_status' => 'rejected',
            'approval_status' => 'rejected',
            'rejection_reason' => $cleanReason,
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejected_by' => $adminId,
        ]);
        $this->reviewModel->insert([
            'marketplace_template_id' => (int) $template['id'],
            'reviewer_id' => $adminId,
            'status' => 'rejected',
            'checklist' => $checklist === [] ? null : json_encode($checklist, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'admin_notes' => $this->cleanNote($adminNotes),
            'rejection_reason' => $cleanReason,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log((int) $template['id'], $adminId, 'admin', 'reject', 'submitted', 'rejected', $cleanReason);
        $this->notifyCreator($template, 'marketplace_template_rejected', 'Template Ditolak', 'Template "' . ($template['title'] ?? '-') . '" ditolak: ' . $cleanReason);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function requestChanges(array $template, int $adminId, string $reason, array $checklist = [], string $adminNotes = ''): bool
    {
        if (! $this->canAdminReview($template) || trim($reason) === '') {
            return false;
        }

        $cleanReason = $this->cleanNote($reason);
        $this->db->transStart();
        $this->marketplaceModel->update((int) $template['id'], [
            'marketplace_status' => 'changes_requested',
            'approval_status' => 'rejected',
            'rejection_reason' => $cleanReason,
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejected_by' => $adminId,
        ]);
        $this->reviewModel->insert([
            'marketplace_template_id' => (int) $template['id'],
            'reviewer_id' => $adminId,
            'status' => 'changes_requested',
            'checklist' => $checklist === [] ? null : json_encode($checklist, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'admin_notes' => $this->cleanNote($adminNotes),
            'rejection_reason' => $cleanReason,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log((int) $template['id'], $adminId, 'admin', 'request_changes', 'submitted', 'changes_requested', $cleanReason);
        $this->notifyCreator($template, 'marketplace_template_changes_requested', 'Template Perlu Revisi', 'Template "' . ($template['title'] ?? '-') . '" perlu direvisi: ' . $cleanReason);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function archiveByAdmin(array $template, int $adminId, string $note = ''): bool
    {
        if (($template['marketplace_status'] ?? '') === 'archived') {
            return false;
        }

        $from = (string) $template['marketplace_status'];
        $this->db->transStart();
        $this->marketplaceModel->update((int) $template['id'], [
            'marketplace_status' => 'archived',
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        $this->log((int) $template['id'], $adminId, 'admin', 'archive', $from, 'archived', $note);
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function checklistComplete(array $checklist): bool
    {
        foreach (array_keys(self::REVIEW_CHECKLIST) as $key) {
            if (($checklist[$key] ?? '') !== '1') {
                return false;
            }
        }

        return true;
    }

    private function canAdminReview(array $template): bool
    {
        return ($template['marketplace_status'] ?? '') === 'submitted'
            && ($template['approval_status'] ?? '') === 'pending';
    }

    private function notifyCreator(array $template, string $type, string $title, string $message): void
    {
        $creatorUserId = (int) ($template['creator_user_id'] ?? 0);
        if ($creatorUserId <= 0 && ! empty($template['creator_id'])) {
            $creator = (new CreatorProfileModel())->find((int) $template['creator_id']);
            $creatorUserId = (int) ($creator['user_id'] ?? 0);
        }

        if ($creatorUserId <= 0) {
            return;
        }

        $this->notificationModel->insert([
            'user_id' => $creatorUserId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => json_encode(['marketplace_template_id' => (int) $template['id']], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function log(int $marketplaceTemplateId, int $actorId, string $actorRole, string $action, ?string $fromStatus, ?string $toStatus, string $note = '', array $metadata = []): void
    {
        $this->logModel->insert([
            'marketplace_template_id' => $marketplaceTemplateId,
            'actor_id' => $actorId > 0 ? $actorId : null,
            'actor_role' => $actorRole,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $this->cleanNote($note),
            'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function cleanNote(string $value): string
    {
        return mb_substr(trim(strip_tags($value)), 0, 1000);
    }
}
