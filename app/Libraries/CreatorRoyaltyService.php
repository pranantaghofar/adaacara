<?php

namespace App\Libraries;

use App\Models\CreatorTemplateRoyaltyEventModel;
use App\Models\CreatorTemplateRoyaltyModel;
use App\Models\CreatorTemplateRoyaltyRuleModel;
use App\Models\InvitationTemplateUsageModel;
use App\Models\MarketplaceTemplateModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class CreatorRoyaltyService
{
    public const DEFAULT_INTERNAL_LICENSE_VALUE = 20000;
    public const DEFAULT_CREATOR_RATE = 0.9;
    public const DEFAULT_PLATFORM_RATE = 0.1;

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function tableReady(): bool
    {
        return $this->db->tableExists('creator_template_royalty_rules')
            && $this->db->tableExists('creator_template_royalty_events')
            && $this->db->tableExists('creator_template_royalties');
    }

    public function recordTemplateUsed(int $invitationId, array $template, int $buyerUserId, array $metadata = []): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $templateId = (int) ($template['id'] ?? 0);
        $creatorUserId = (int) ($template['owner_user_id'] ?? 0);
        if ($invitationId <= 0 || $templateId <= 0 || $buyerUserId <= 0 || $creatorUserId <= 0) {
            return;
        }

        $usage = $this->usageForInvitation($invitationId);
        $marketplace = $this->approvedMarketplaceForTemplate($templateId);

        $this->recordEvent('template_used', [
            'template_id' => $templateId,
            'marketplace_template_id' => (int) ($marketplace['id'] ?? 0) ?: null,
            'invitation_id' => $invitationId,
            'usage_id' => (int) ($usage['id'] ?? 0) ?: null,
            'creator_user_id' => $creatorUserId,
            'buyer_user_id' => $buyerUserId,
            'metadata' => $metadata,
        ]);
    }

    public function createPendingRoyaltyForPublishedUsage(int $invitationId, int $publisherUserId, ?array $order = null, array $metadata = []): ?array
    {
        if (! $this->tableReady() || ! $this->db->tableExists('invitation_template_usages')) {
            return null;
        }

        $usage = $this->usageForInvitation($invitationId);
        if ($usage === null) {
            return null;
        }

        $usageId = (int) ($usage['id'] ?? 0);
        $existing = (new CreatorTemplateRoyaltyModel())->findForUsage($usageId);
        if ($existing !== null) {
            return $existing;
        }

        $templateId = (int) ($usage['template_id'] ?? 0);
        $creatorUserId = (int) ($usage['template_owner_user_id'] ?? 0);
        $buyerUserId = (int) ($usage['used_by_user_id'] ?? $publisherUserId);
        if ($templateId <= 0 || $creatorUserId <= 0 || $buyerUserId <= 0 || $creatorUserId === $publisherUserId) {
            $this->recordCancelledUsage($usage, $publisherUserId, 'Creator memakai template sendiri atau data usage tidak valid.');
            return null;
        }

        $rule = $this->royaltyRuleForTemplate($templateId, $creatorUserId);
        if ($rule === null) {
            $this->recordCancelledUsage($usage, $publisherUserId, 'Royalty rule belum tersedia.');
            return null;
        }

        $calculation = $this->calculateRoyalty(
            (int) ($rule['license_value'] ?? 0),
            (float) ($rule['creator_rate'] ?? self::DEFAULT_CREATOR_RATE)
        );

        if ($calculation['license_value'] <= 0 || $calculation['creator_amount'] <= 0) {
            $this->recordCancelledUsage($usage, $publisherUserId, 'Nilai lisensi template belum valid.');
            return null;
        }

        $now = date('Y-m-d H:i:s');
        $orderStatus = strtolower((string) ($order['status'] ?? ''));
        $status = $orderStatus === 'paid'
            ? CreatorTemplateRoyaltyModel::STATUS_AVAILABLE
            : CreatorTemplateRoyaltyModel::STATUS_PENDING;

        $payload = [
            'template_id' => $templateId,
            'marketplace_template_id' => (int) ($rule['marketplace_template_id'] ?? 0) ?: null,
            'invitation_id' => $invitationId,
            'usage_id' => $usageId,
            'creator_user_id' => $creatorUserId,
            'buyer_user_id' => $buyerUserId,
            'order_id' => (int) ($order['id'] ?? 0) ?: null,
            'license_value' => $calculation['license_value'],
            'currency' => (string) ($rule['currency'] ?? 'IDR'),
            'creator_rate' => $calculation['creator_rate'],
            'creator_amount' => $calculation['creator_amount'],
            'platform_amount' => $calculation['platform_amount'],
            'status' => $status,
            'qualified_at' => $now,
            'available_at' => $status === CreatorTemplateRoyaltyModel::STATUS_AVAILABLE ? $now : null,
            'note' => $status === CreatorTemplateRoyaltyModel::STATUS_AVAILABLE
                ? 'Royalty creator tersedia dari qualified publish dengan order paid.'
                : 'Royalty creator menunggu konfirmasi pembayaran atau eligibility.',
            'metadata' => $this->jsonMetadata($metadata + [
                'source' => 'creator_royalty_service',
                'order_status' => $order['status'] ?? null,
                'order_invoice' => $order['invoice_number'] ?? null,
            ]),
        ];

        $royaltyModel = new CreatorTemplateRoyaltyModel();
        $this->db->transStart();
        $royaltyId = (int) $royaltyModel->insert($payload, true);
        $royalty = $royaltyId > 0 ? $royaltyModel->find($royaltyId) : null;

        $this->recordEvent('template_published', [
            'template_id' => $templateId,
            'marketplace_template_id' => (int) ($rule['marketplace_template_id'] ?? 0) ?: null,
            'invitation_id' => $invitationId,
            'usage_id' => $usageId,
            'creator_user_id' => $creatorUserId,
            'buyer_user_id' => $buyerUserId,
            'order_id' => (int) ($order['id'] ?? 0) ?: null,
            'royalty_id' => $royaltyId ?: null,
            'metadata' => $metadata,
        ]);
        $this->recordEvent('royalty_created', [
            'template_id' => $templateId,
            'marketplace_template_id' => (int) ($rule['marketplace_template_id'] ?? 0) ?: null,
            'invitation_id' => $invitationId,
            'usage_id' => $usageId,
            'creator_user_id' => $creatorUserId,
            'buyer_user_id' => $buyerUserId,
            'order_id' => (int) ($order['id'] ?? 0) ?: null,
            'royalty_id' => $royaltyId ?: null,
            'metadata' => $calculation,
        ]);
        $this->db->transComplete();

        return $royalty;
    }

    public function confirmRoyaltyForPaidOrder(int $orderId): int
    {
        if (! $this->tableReady() || $orderId <= 0) {
            return 0;
        }

        $royaltyModel = new CreatorTemplateRoyaltyModel();
        $pending = $royaltyModel
            ->where('order_id', $orderId)
            ->where('status', CreatorTemplateRoyaltyModel::STATUS_PENDING)
            ->findAll();

        $updated = 0;
        $now = date('Y-m-d H:i:s');
        foreach ($pending as $royalty) {
            $royaltyModel->update((int) $royalty['id'], [
                'status' => CreatorTemplateRoyaltyModel::STATUS_AVAILABLE,
                'available_at' => $now,
                'note' => 'Royalty creator tersedia setelah order paid.',
            ]);
            $this->recordEvent('royalty_confirmed', [
                'template_id' => (int) ($royalty['template_id'] ?? 0) ?: null,
                'marketplace_template_id' => (int) ($royalty['marketplace_template_id'] ?? 0) ?: null,
                'invitation_id' => (int) ($royalty['invitation_id'] ?? 0) ?: null,
                'usage_id' => (int) ($royalty['usage_id'] ?? 0) ?: null,
                'creator_user_id' => (int) ($royalty['creator_user_id'] ?? 0) ?: null,
                'buyer_user_id' => (int) ($royalty['buyer_user_id'] ?? 0) ?: null,
                'order_id' => $orderId,
                'royalty_id' => (int) $royalty['id'],
            ]);
            $updated++;
        }

        return $updated;
    }

    public function reverseRoyalty(int $royaltyId, string $reason = 'Royalty dibalik.'): bool
    {
        if (! $this->tableReady() || $royaltyId <= 0) {
            return false;
        }

        $royaltyModel = new CreatorTemplateRoyaltyModel();
        $royalty = $royaltyModel->find($royaltyId);
        if ($royalty === null || in_array((string) ($royalty['status'] ?? ''), [
            CreatorTemplateRoyaltyModel::STATUS_REVERSED,
            CreatorTemplateRoyaltyModel::STATUS_CANCELLED,
        ], true)) {
            return false;
        }

        $royaltyModel->update($royaltyId, [
            'status' => CreatorTemplateRoyaltyModel::STATUS_REVERSED,
            'reversed_at' => date('Y-m-d H:i:s'),
            'note' => $reason,
        ]);

        $this->recordEvent('royalty_reversed', [
            'template_id' => (int) ($royalty['template_id'] ?? 0) ?: null,
            'marketplace_template_id' => (int) ($royalty['marketplace_template_id'] ?? 0) ?: null,
            'invitation_id' => (int) ($royalty['invitation_id'] ?? 0) ?: null,
            'usage_id' => (int) ($royalty['usage_id'] ?? 0) ?: null,
            'creator_user_id' => (int) ($royalty['creator_user_id'] ?? 0) ?: null,
            'buyer_user_id' => (int) ($royalty['buyer_user_id'] ?? 0) ?: null,
            'order_id' => (int) ($royalty['order_id'] ?? 0) ?: null,
            'royalty_id' => $royaltyId,
            'metadata' => ['reason' => $reason],
        ]);

        return true;
    }

    public function creatorSummary(int $creatorUserId): array
    {
        $summary = [
            'uses' => 0,
            'published' => 0,
            'earnings_total' => 0,
            'pending' => 0,
            'available' => 0,
            'reversed' => 0,
        ];

        if (! $this->tableReady() || $creatorUserId <= 0) {
            return $summary;
        }

        if ($this->db->tableExists('templates')) {
            $stats = $this->db->table('templates')
                ->select('COALESCE(SUM(usage_count), 0) AS uses, COALESCE(SUM(publish_count), 0) AS published', false)
                ->where('owner_user_id', $creatorUserId)
                ->get()
                ->getRowArray();
            $summary['uses'] = (int) ($stats['uses'] ?? 0);
            $summary['published'] = (int) ($stats['published'] ?? 0);
        }

        $rows = $this->db->table('creator_template_royalties')
            ->select('status, COALESCE(SUM(creator_amount), 0) AS amount', false)
            ->where('creator_user_id', $creatorUserId)
            ->groupBy('status')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            $amount = (int) ($row['amount'] ?? 0);
            if ($status === CreatorTemplateRoyaltyModel::STATUS_PENDING) {
                $summary['pending'] = $amount;
            } elseif ($status === CreatorTemplateRoyaltyModel::STATUS_AVAILABLE) {
                $summary['available'] = $amount;
                $summary['earnings_total'] += $amount;
            } elseif ($status === CreatorTemplateRoyaltyModel::STATUS_REVERSED) {
                $summary['reversed'] = $amount;
            }
        }

        return $summary;
    }

    public function recentRoyaltiesForCreator(int $creatorUserId, int $limit = 50): array
    {
        if (! $this->tableReady() || $creatorUserId <= 0) {
            return [];
        }

        $builder = $this->db->table('creator_template_royalties')
            ->select('creator_template_royalties.*, templates.name AS template_name, landing_pages.title AS invitation_title, landing_pages.slug AS invitation_slug, users.name AS buyer_name, users.email AS buyer_email')
            ->join('templates', 'templates.id = creator_template_royalties.template_id', 'left')
            ->join('landing_pages', 'landing_pages.id = creator_template_royalties.invitation_id', 'left')
            ->join('users', 'users.id = creator_template_royalties.buyer_user_id', 'left')
            ->where('creator_template_royalties.creator_user_id', $creatorUserId)
            ->orderBy('creator_template_royalties.created_at', 'DESC')
            ->orderBy('creator_template_royalties.id', 'DESC')
            ->limit(max(1, min(200, $limit)));

        return $builder->get()->getResultArray();
    }

    public function adminRoyalties(array $filters = [], int $limit = 200): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        $builder = $this->db->table('creator_template_royalties')
            ->select('creator_template_royalties.*, templates.name AS template_name, landing_pages.title AS invitation_title, landing_pages.slug AS invitation_slug, creators.name AS creator_name, creators.email AS creator_email, buyers.name AS buyer_name, buyers.email AS buyer_email, orders.invoice_number, orders.status AS order_status')
            ->join('templates', 'templates.id = creator_template_royalties.template_id', 'left')
            ->join('landing_pages', 'landing_pages.id = creator_template_royalties.invitation_id', 'left')
            ->join('users AS creators', 'creators.id = creator_template_royalties.creator_user_id', 'left')
            ->join('users AS buyers', 'buyers.id = creator_template_royalties.buyer_user_id', 'left')
            ->join('orders', 'orders.id = creator_template_royalties.order_id', 'left');

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $builder->where('creator_template_royalties.status', $status);
        }

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('templates.name', $keyword)
                ->orLike('landing_pages.title', $keyword)
                ->orLike('landing_pages.slug', $keyword)
                ->orLike('creators.name', $keyword)
                ->orLike('creators.email', $keyword)
                ->orLike('buyers.name', $keyword)
                ->orLike('buyers.email', $keyword)
                ->orLike('orders.invoice_number', $keyword)
                ->groupEnd();
        }

        $creatorId = (int) ($filters['creator_user_id'] ?? 0);
        if ($creatorId > 0) {
            $builder->where('creator_template_royalties.creator_user_id', $creatorId);
        }

        $templateId = (int) ($filters['template_id'] ?? 0);
        if ($templateId > 0) {
            $builder->where('creator_template_royalties.template_id', $templateId);
        }

        return $builder
            ->orderBy('creator_template_royalties.created_at', 'DESC')
            ->orderBy('creator_template_royalties.id', 'DESC')
            ->limit(max(1, min(500, $limit)))
            ->get()
            ->getResultArray();
    }

    public function adminEvents(array $filters = [], int $limit = 200): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        $builder = $this->db->table('creator_template_royalty_events')
            ->select('creator_template_royalty_events.*, templates.name AS template_name, landing_pages.title AS invitation_title, landing_pages.slug AS invitation_slug, creators.name AS creator_name, buyers.name AS buyer_name, orders.invoice_number')
            ->join('templates', 'templates.id = creator_template_royalty_events.template_id', 'left')
            ->join('landing_pages', 'landing_pages.id = creator_template_royalty_events.invitation_id', 'left')
            ->join('users AS creators', 'creators.id = creator_template_royalty_events.creator_user_id', 'left')
            ->join('users AS buyers', 'buyers.id = creator_template_royalty_events.buyer_user_id', 'left')
            ->join('orders', 'orders.id = creator_template_royalty_events.order_id', 'left');

        $eventType = trim((string) ($filters['event_type'] ?? ''));
        if ($eventType !== '') {
            $builder->where('creator_template_royalty_events.event_type', $eventType);
        }

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('creator_template_royalty_events.event_type', $keyword)
                ->orLike('templates.name', $keyword)
                ->orLike('landing_pages.title', $keyword)
                ->orLike('landing_pages.slug', $keyword)
                ->orLike('creators.name', $keyword)
                ->orLike('buyers.name', $keyword)
                ->orLike('orders.invoice_number', $keyword)
                ->groupEnd();
        }

        return $builder
            ->orderBy('creator_template_royalty_events.created_at', 'DESC')
            ->orderBy('creator_template_royalty_events.id', 'DESC')
            ->limit(max(1, min(500, $limit)))
            ->get()
            ->getResultArray();
    }

    public function templateStats(int $templateId): array
    {
        $stats = [
            'views' => 0,
            'uses' => 0,
            'published' => 0,
            'conversion' => 0.0,
            'earnings' => 0,
        ];

        if ($templateId <= 0) {
            return $stats;
        }

        if ($this->db->tableExists('templates')) {
            $template = $this->db->table('templates')
                ->select('usage_count, publish_count')
                ->where('id', $templateId)
                ->get()
                ->getRowArray();
            $stats['uses'] = (int) ($template['usage_count'] ?? 0);
            $stats['published'] = (int) ($template['publish_count'] ?? 0);
            $stats['conversion'] = $stats['uses'] > 0
                ? round(($stats['published'] / $stats['uses']) * 100, 2)
                : 0.0;
        }

        if ($this->tableReady()) {
            $earnings = $this->db->table('creator_template_royalties')
                ->select('COALESCE(SUM(creator_amount), 0) AS earnings', false)
                ->where('template_id', $templateId)
                ->where('status', CreatorTemplateRoyaltyModel::STATUS_AVAILABLE)
                ->get()
                ->getRowArray();
            $stats['earnings'] = (int) ($earnings['earnings'] ?? 0);
        }

        return $stats;
    }

    public function royaltyRuleForTemplate(int $templateId, int $creatorUserId): ?array
    {
        if (! $this->tableReady() || $templateId <= 0 || $creatorUserId <= 0) {
            return null;
        }

        $ruleModel = new CreatorTemplateRoyaltyRuleModel();
        $existing = $ruleModel->activeForTemplate($templateId);
        if ($existing !== null) {
            return $existing;
        }

        $marketplace = $this->approvedMarketplaceForTemplate($templateId);
        $licenseValue = $this->licenseValueForMarketplace($marketplace);
        if ($licenseValue <= 0) {
            return null;
        }

        $payload = [
            'template_id' => $templateId,
            'marketplace_template_id' => (int) ($marketplace['id'] ?? 0) ?: null,
            'creator_user_id' => $creatorUserId,
            'license_value' => $licenseValue,
            'currency' => (string) ($marketplace['price_currency'] ?? 'IDR'),
            'creator_rate' => self::DEFAULT_CREATOR_RATE,
            'platform_rate' => self::DEFAULT_PLATFORM_RATE,
            'status' => 'active',
            'note' => $marketplace !== null && (int) ($marketplace['price_amount'] ?? 0) > 0
                ? 'Royalty 90% dari harga lisensi marketplace template.'
                : 'Royalty 90% dari nilai internal Premium Creator Template.',
            'metadata' => $this->jsonMetadata([
                'marketplace_status' => $marketplace['marketplace_status'] ?? null,
                'approval_status' => $marketplace['approval_status'] ?? null,
                'is_free' => $marketplace['is_free'] ?? null,
            ]),
        ];

        $ruleId = (int) $ruleModel->insert($payload, true);

        return $ruleId > 0 ? $ruleModel->find($ruleId) : null;
    }

    public function calculateRoyalty(int $licenseValue, float $creatorRate = self::DEFAULT_CREATOR_RATE): array
    {
        $licenseValue = max(0, $licenseValue);
        $creatorRate = max(0.0, min(1.0, $creatorRate));
        $creatorAmount = (int) floor($licenseValue * $creatorRate);

        return [
            'license_value' => $licenseValue,
            'creator_rate' => $creatorRate,
            'creator_amount' => $creatorAmount,
            'platform_amount' => max(0, $licenseValue - $creatorAmount),
        ];
    }

    private function usageForInvitation(int $invitationId): ?array
    {
        if ($invitationId <= 0 || ! $this->db->tableExists('invitation_template_usages')) {
            return null;
        }

        return (new InvitationTemplateUsageModel())
            ->where('invitation_id', $invitationId)
            ->first();
    }

    private function approvedMarketplaceForTemplate(int $templateId): ?array
    {
        if ($templateId <= 0 || ! $this->db->tableExists('marketplace_templates')) {
            return null;
        }

        return (new MarketplaceTemplateModel())
            ->where('template_id', $templateId)
            ->where('marketplace_status', 'approved')
            ->where('approval_status', 'approved')
            ->first();
    }

    private function licenseValueForMarketplace(?array $marketplace): int
    {
        if ($marketplace !== null && (int) ($marketplace['is_free'] ?? 1) === 0) {
            return max(0, (int) ($marketplace['price_amount'] ?? 0));
        }

        if ($marketplace !== null) {
            return self::DEFAULT_INTERNAL_LICENSE_VALUE;
        }

        return 0;
    }

    private function recordCancelledUsage(array $usage, int $publisherUserId, string $reason): void
    {
        $this->recordEvent('royalty_cancelled', [
            'template_id' => (int) ($usage['template_id'] ?? 0) ?: null,
            'invitation_id' => (int) ($usage['invitation_id'] ?? 0) ?: null,
            'usage_id' => (int) ($usage['id'] ?? 0) ?: null,
            'creator_user_id' => (int) ($usage['template_owner_user_id'] ?? 0) ?: null,
            'buyer_user_id' => (int) ($usage['used_by_user_id'] ?? $publisherUserId) ?: null,
            'metadata' => ['reason' => $reason],
        ]);
    }

    private function recordEvent(string $eventType, array $data): void
    {
        if (! $this->db->tableExists('creator_template_royalty_events')) {
            return;
        }

        try {
            (new CreatorTemplateRoyaltyEventModel())->insert([
                'event_type' => $eventType,
                'template_id' => $data['template_id'] ?? null,
                'marketplace_template_id' => $data['marketplace_template_id'] ?? null,
                'invitation_id' => $data['invitation_id'] ?? null,
                'usage_id' => $data['usage_id'] ?? null,
                'creator_user_id' => $data['creator_user_id'] ?? null,
                'buyer_user_id' => $data['buyer_user_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'royalty_id' => $data['royalty_id'] ?? null,
                'metadata' => $this->jsonMetadata((array) ($data['metadata'] ?? [])),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // Event logging must not block publish/payment flows when the service is wired later.
        }
    }

    private function jsonMetadata(array $metadata): ?string
    {
        $metadata = array_filter($metadata, static fn ($value): bool => $value !== null && $value !== '');
        if ($metadata === []) {
            return null;
        }

        return json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
