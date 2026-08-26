<?php

namespace App\Libraries;

use App\Models\InvitationTemplateUsageModel;
use App\Models\SellerWalletLedgerModel;
use App\Models\SellerWithdrawRequestModel;
use App\Models\TemplateModel;
use App\Models\CreatorProfileModel;
use App\Models\UserSubscriptionModel;
use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;

class SellerTemplateService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function activeSellerSubscription(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $subscription = (new UserSubscriptionModel())->activeWithPlanByUser($userId);

        return $this->isSellerSubscription($subscription) ? $subscription : null;
    }

    public function canSaveTemplate(int $userId): bool
    {
        return $this->isActiveCreator($userId);
    }

    public function canAccessSellerDashboard(int $userId): bool
    {
        return $this->activeSellerSubscription($userId) !== null;
    }

    public function canAccessCreatorDashboard(int $userId): bool
    {
        return $this->isActiveCreator($userId);
    }

    public function isActiveCreator(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $sessionUserId = (int) (session()->get('userId') ?? 0);
        $role = $sessionUserId === $userId ? strtolower(trim((string) (session()->get('userRole') ?? ''))) : '';
        if ($role === '') {
            $user = (new UserModel())->find($userId);
            $role = strtolower(trim((string) ($user['role'] ?? 'user')));
        }

        if (in_array($role, ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin'], true)) {
            return true;
        }

        if (! $this->db->tableExists('creator_profiles')) {
            return false;
        }

        return (new CreatorProfileModel())->activeForUser($userId) !== null;
    }

    public function isSellerSubscription(?array $subscription): bool
    {
        if ($subscription === null) {
            return false;
        }

        $keys = [
            $subscription['plan_slug'] ?? '',
            $subscription['plan_name'] ?? '',
        ];

        foreach ($keys as $key) {
            if (in_array($this->normalizePlanKey((string) $key), ['premium', 'business', 'busseniss', 'buat-coba-jualan', 'buat-niat-jualan'], true)) {
                return true;
            }
        }

        return false;
    }

    public function planLimits(?array $subscriptionOrTemplate = null): array
    {
        $key = $this->normalizePlanKey((string) (
            $subscriptionOrTemplate['plan_slug']
            ?? $subscriptionOrTemplate['seller_plan_name']
            ?? $subscriptionOrTemplate['plan_name']
            ?? ''
        ));

        if ($key === 'creator') {
            return [
                'plan_name' => 'CREATOR',
                'max_public_templates' => null,
                'minimum_withdraw_amount' => 25000,
                'commission_amount' => 0,
            ];
        }

        if (in_array($key, ['business', 'busseniss', 'buat-niat-jualan'], true)) {
            return [
                'plan_name' => 'BUAT NIAT JUALAN',
                'max_public_templates' => null,
                'minimum_withdraw_amount' => 25000,
                'commission_amount' => 1500,
            ];
        }

        return [
            'plan_name' => 'BUAT COBA JUALAN',
            'max_public_templates' => 5,
            'minimum_withdraw_amount' => 50000,
            'commission_amount' => 1000,
        ];
    }

    public function createTemplateUsage(int $invitationId, array $template, int $usedByUserId): void
    {
        if (! $this->db->tableExists('invitation_template_usages')) {
            return;
        }

        $templateId = (int) ($template['id'] ?? 0);
        $ownerUserId = (int) ($template['owner_user_id'] ?? 0);

        if ($invitationId <= 0 || $templateId <= 0 || $usedByUserId <= 0 || $ownerUserId <= 0) {
            return;
        }

        if (($template['review_status'] ?? '') !== 'approved' || ($template['public_status'] ?? '') !== 'public') {
            return;
        }

        $usageModel = new InvitationTemplateUsageModel();
        $exists = $usageModel->where('invitation_id', $invitationId)->first();
        if ($exists !== null) {
            return;
        }

        try {
            $usageModel->insert([
                'invitation_id' => $invitationId,
                'template_id' => $templateId,
                'template_owner_user_id' => $ownerUserId,
                'used_by_user_id' => $usedByUserId,
                'status' => 'created',
                'commission_status' => 'none',
                'commission_amount' => 0,
            ]);

            if ($this->templateHasField('usage_count')) {
                $this->db->query('UPDATE `templates` SET `usage_count` = COALESCE(`usage_count`, 0) + 1 WHERE `id` = ?', [$templateId]);
            }
        } catch (\Throwable) {
            // Unique invitation_id makes this safe under retries.
        }
    }

    public function processSellerTemplateCommission(int $invitationId, int $publisherUserId): void
    {
        if ($invitationId <= 0 || ! $this->db->tableExists('invitation_template_usages') || ! $this->db->tableExists('seller_wallet_ledger')) {
            return;
        }

        $usageModel = new InvitationTemplateUsageModel();
        $ledgerModel = new SellerWalletLedgerModel();

        $usage = $usageModel->where('invitation_id', $invitationId)->first();
        if ($usage === null) {
            return;
        }

        $ownerUserId = (int) ($usage['template_owner_user_id'] ?? 0);
        if ($ownerUserId <= 0 || $ownerUserId === $publisherUserId) {
            $this->markUsagePublished($usageModel, $usage, [
                'commission_status' => 'cancelled',
            ]);
            return;
        }

        if (! $this->isActiveCreator($ownerUserId)) {
            log_message('info', 'Template commission skipped because owner is not an active creator. owner={owner} invitation={invitation}', [
                'owner' => $ownerUserId,
                'invitation' => $invitationId,
            ]);
            $this->markUsagePublished($usageModel, $usage, [
                'commission_status' => 'owner_not_creator',
                'commission_amount' => 0,
            ]);
            return;
        }

        if (($usage['commission_status'] ?? '') === 'available') {
            return;
        }

        $existingLedger = $this->existingCommissionLedger($invitationId, (int) $usage['id']);
        if ($existingLedger !== null) {
            return;
        }

        $order = $this->getPaidBuatPakaiSendiriOrderForInvitation((int) ($usage['used_by_user_id'] ?? $publisherUserId), $invitationId);
        if ($order === null) {
            $this->markUsagePublished($usageModel, $usage, [
                'commission_status' => 'pending_payment',
            ]);
            return;
        }

        if ($this->existingCommissionForBuyerSellerOrder($ownerUserId, $publisherUserId, (int) ($order['id'] ?? 0)) !== null) {
            $this->markUsagePublished($usageModel, $usage, [
                'commission_status' => 'duplicate_order',
                'commission_amount' => 0,
            ]);
            return;
        }

        $amountBase = (int) ($order['amount'] ?? 0);
        $amount = $this->calculateSellerCommissionFromOrder($order);
        if ($amountBase <= 0 || $amount <= 0) {
            $this->markUsagePublished($usageModel, $usage, [
                'commission_status' => 'cancelled',
            ]);
            return;
        }

        $this->db->transStart();
        $this->markUsagePublished($usageModel, $usage, [
            'commission_status' => 'available',
            'commission_amount' => $amount,
        ]);

        $ledgerModel->insert($this->filterLedgerColumns($this->createSellerCommissionLedger([
            'owner_user_id' => $ownerUserId,
            'template_id' => (int) ($usage['template_id'] ?? 0) ?: null,
            'invitation_id' => $invitationId,
            'usage_id' => (int) $usage['id'],
            'order' => $order,
            'amount_base' => $amountBase,
            'commission_amount' => $amount,
            'publisher_user_id' => $publisherUserId,
        ])));

        $this->db->transComplete();
    }

    public function getPaidBuatPakaiSendiriOrderForInvitation(int $userId, int $invitationId): ?array
    {
        if ($userId <= 0 || ! $this->db->tableExists('orders') || ! $this->db->tableExists('plans')) {
            return null;
        }

        $orders = $this->db->table('orders')
            ->select('orders.*, plans.name AS plan_name, plans.slug AS plan_slug')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.user_id', $userId)
            ->where('orders.status', 'paid')
            ->where('orders.amount >', 0)
            ->orderBy('orders.paid_at', 'DESC')
            ->orderBy('orders.created_at', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($orders as $order) {
            if ($this->isBuatPakaiSendiriPlan($order)) {
                return $order;
            }
        }

        return null;
    }

    public function calculateSellerCommissionFromOrder(array $order): int
    {
        return (int) floor(((int) ($order['amount'] ?? 0)) * 0.7);
    }

    public function hasExistingCommission(int $invitationId, int $usageId): bool
    {
        return $this->existingCommissionLedger($invitationId, $usageId) !== null;
    }

    public function createSellerCommissionLedger(array $data): array
    {
        $order = $data['order'] ?? [];
        $amountBase = (int) ($data['amount_base'] ?? 0);
        $commissionAmount = (int) ($data['commission_amount'] ?? 0);

        return [
            'user_id' => (int) ($data['owner_user_id'] ?? 0),
            'template_id' => (int) ($data['template_id'] ?? 0) ?: null,
            'invitation_id' => (int) ($data['invitation_id'] ?? 0) ?: null,
            'usage_id' => (int) ($data['usage_id'] ?? 0) ?: null,
            'order_id' => (int) ($order['id'] ?? 0) ?: null,
            'payment_id' => null,
            'plan_name' => (string) ($order['plan_name'] ?? 'BUAT PAKAI SENDIRI'),
            'amount_base' => $amountBase,
            'commission_rate' => 0.7,
            'commission_amount' => $commissionAmount,
            'commission_source' => 'buat_pakai_sendiri_publish',
            'type' => 'commission',
            'direction' => 'credit',
            'amount' => $commissionAmount,
            'status' => 'available',
            'note' => 'Komisi 70% dari pembelian BUAT PAKAI SENDIRI',
            'metadata' => json_encode([
                'publisher_user_id' => (int) ($data['publisher_user_id'] ?? 0),
                'order_invoice' => $order['invoice_number'] ?? null,
                'order_status' => $order['status'] ?? null,
                'plan_slug' => $order['plan_slug'] ?? null,
                'plan_name' => $order['plan_name'] ?? null,
                'amount_paid' => $amountBase,
                'commission_rate' => 0.7,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];
    }

    public function walletBalance(int $userId): array
    {
        if (! $this->db->tableExists('seller_wallet_ledger')) {
            return [
                'available' => 0,
                'pending' => 0,
                'withdrawn' => 0,
            ];
        }

        $rows = $this->db->table('seller_wallet_ledger')
            ->select('direction, status, amount')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $available = 0;
        $pending = 0;
        $withdrawn = 0;

        foreach ($rows as $row) {
            $amount = (int) ($row['amount'] ?? 0);
            $direction = (string) ($row['direction'] ?? '');
            $status = (string) ($row['status'] ?? '');

            if ($direction === 'credit' && $status === 'available') {
                $available += $amount;
            } elseif ($direction === 'credit' && $status === 'pending') {
                $pending += $amount;
            } elseif ($direction === 'debit' && in_array($status, ['pending', 'withdrawn'], true)) {
                $available -= $amount;
                if ($status === 'withdrawn') {
                    $withdrawn += $amount;
                }
            }
        }

        return [
            'available' => max(0, $available),
            'pending' => max(0, $pending),
            'withdrawn' => max(0, $withdrawn),
        ];
    }

    public function createWithdrawRequest(int $userId, array $payload): int
    {
        if (! $this->isActiveCreator($userId)) {
            throw new RuntimeException('Earnings dan withdraw template hanya tersedia untuk Creator aktif.');
        }

        $limits = $this->planLimits(['plan_slug' => 'creator']);
        $amount = (int) ($payload['amount'] ?? 0);
        $balance = $this->walletBalance($userId);

        if ($amount < (int) $limits['minimum_withdraw_amount']) {
            throw new RuntimeException('Minimal withdraw adalah Rp ' . number_format((int) $limits['minimum_withdraw_amount'], 0, ',', '.'));
        }

        if ($amount > (int) $balance['available']) {
            throw new RuntimeException('Saldo tersedia tidak cukup untuk withdraw.');
        }

        $requestModel = new SellerWithdrawRequestModel();
        $ledgerModel = new SellerWalletLedgerModel();
        $now = date('Y-m-d H:i:s');

        $this->db->transStart();
        $requestId = (int) $requestModel->insert([
            'user_id' => $userId,
            'amount' => $amount,
            'bank_name' => trim((string) ($payload['bank_name'] ?? '')),
            'account_number' => trim((string) ($payload['account_number'] ?? '')),
            'account_holder_name' => trim((string) ($payload['account_holder_name'] ?? '')),
            'status' => 'pending',
            'requested_at' => $now,
        ], true);

        $ledgerModel->insert([
            'user_id' => $userId,
            'type' => 'withdrawal_hold',
            'direction' => 'debit',
            'amount' => $amount,
            'status' => 'pending',
            'note' => 'Hold saldo untuk request withdraw #' . $requestId,
            'metadata' => json_encode(['withdraw_request_id' => $requestId], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $this->db->transComplete();

        return $requestId;
    }

    public function updateWithdrawStatus(int $adminId, int $requestId, string $action, string $note = ''): void
    {
        $requestModel = new SellerWithdrawRequestModel();
        $ledgerModel = new SellerWalletLedgerModel();
        $request = $requestModel->find($requestId);

        if ($request === null || ! in_array((string) ($request['status'] ?? ''), ['pending', 'approved'], true)) {
            throw new RuntimeException('Request withdraw tidak valid.');
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transStart();

        if ($action === 'approve') {
            $requestModel->update($requestId, [
                'status' => 'approved',
                'admin_id' => $adminId,
                'admin_note' => $note,
                'approved_at' => $now,
            ]);
        } elseif ($action === 'reject') {
            $requestModel->update($requestId, [
                'status' => 'rejected',
                'admin_id' => $adminId,
                'admin_note' => $note,
                'rejected_at' => $now,
            ]);
            $this->cancelWithdrawHold($ledgerModel, $requestId);
        } elseif ($action === 'paid') {
            $requestModel->update($requestId, [
                'status' => 'paid',
                'admin_id' => $adminId,
                'admin_note' => $note,
                'paid_at' => $now,
            ]);
            $this->markWithdrawHoldPaid($ledgerModel, $requestId);
        }

        $this->db->transComplete();
    }

    public function normalizePlanKey(string $value): string
    {
        helper('url');

        return url_title(strtolower(trim($value)), '-', true);
    }

    private function templateHasField(string $field): bool
    {
        return in_array($field, $this->db->getFieldNames('templates'), true);
    }

    private function existingCommissionLedger(int $invitationId, int $usageId): ?array
    {
        if (! $this->db->tableExists('seller_wallet_ledger')) {
            return null;
        }

        $builder = $this->db->table('seller_wallet_ledger')
            ->where('type', 'commission')
            ->groupStart()
                ->where('invitation_id', $invitationId);

        if ($usageId > 0) {
            $builder->orWhere('usage_id', $usageId);
        }

        return $builder
            ->groupEnd()
            ->get()
            ->getRowArray();
    }

    private function existingCommissionForBuyerSellerOrder(int $sellerUserId, int $publisherUserId, int $orderId): ?array
    {
        if ($sellerUserId <= 0 || $publisherUserId <= 0 || $orderId <= 0 || ! $this->db->tableExists('seller_wallet_ledger')) {
            return null;
        }

        return $this->db->table('seller_wallet_ledger')
            ->where('user_id', $sellerUserId)
            ->where('order_id', $orderId)
            ->where('type', 'commission')
            ->where('status !=', 'cancelled')
            ->where('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.publisher_user_id")) =', (string) $publisherUserId, false)
            ->get()
            ->getRowArray();
    }

    private function markUsagePublished(InvitationTemplateUsageModel $usageModel, array $usage, array $updates): void
    {
        $wasPublished = ! empty($usage['published_at']);

        $usageModel->update((int) $usage['id'], array_merge([
            'status' => 'published',
            'published_at' => $usage['published_at'] ?: date('Y-m-d H:i:s'),
        ], $updates));

        if (! $wasPublished && $this->templateHasField('publish_count')) {
            $templateId = (int) ($usage['template_id'] ?? 0);
            if ($templateId > 0) {
                $this->db->query('UPDATE `templates` SET `publish_count` = COALESCE(`publish_count`, 0) + 1 WHERE `id` = ?', [$templateId]);
            }
        }
    }

    private function isBuatPakaiSendiriPlan(array $order): bool
    {
        $keys = [
            (string) ($order['plan_slug'] ?? ''),
            (string) ($order['plan_name'] ?? ''),
        ];

        foreach ($keys as $key) {
            if (in_array($this->normalizePlanKey($key), ['buat-pakai-sendiri', 'basic', 'starter', 'buat-acara-sendiri'], true)) {
                return true;
            }
        }

        return false;
    }

    private function filterLedgerColumns(array $data): array
    {
        if (! $this->db->tableExists('seller_wallet_ledger')) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->db->getFieldNames('seller_wallet_ledger')));
    }

    private function cancelWithdrawHold(SellerWalletLedgerModel $ledgerModel, int $requestId): void
    {
        $this->db->table('seller_wallet_ledger')
            ->where('type', 'withdrawal_hold')
            ->where('JSON_EXTRACT(metadata, "$.withdraw_request_id") =', $requestId, false)
            ->update([
                'type' => 'withdrawal_cancelled',
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function markWithdrawHoldPaid(SellerWalletLedgerModel $ledgerModel, int $requestId): void
    {
        $this->db->table('seller_wallet_ledger')
            ->where('type', 'withdrawal_hold')
            ->where('JSON_EXTRACT(metadata, "$.withdraw_request_id") =', $requestId, false)
            ->update([
                'type' => 'withdrawal_paid',
                'status' => 'withdrawn',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
