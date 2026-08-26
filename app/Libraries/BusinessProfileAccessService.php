<?php

namespace App\Libraries;

use App\Models\BusinessProfileEntitlementModel;
use App\Models\BusinessProfileOrderModel;
use Config\Database;

class BusinessProfileAccessService
{
    private const PRICE = 79000;

    public function price(): int
    {
        return self::PRICE;
    }

    public function tablesReady(): bool
    {
        $db = Database::connect();

        return $db->tableExists('business_profile_orders')
            && $db->tableExists('business_profile_entitlements');
    }

    public function isBusinessProfilePage(array $page): bool
    {
        $projectType = strtolower(trim((string) ($page['project_type'] ?? '')));
        if (in_array($projectType, ['business_profile', 'business-profile'], true)) {
            return true;
        }

        $editorJson = (string) ($page['editor_json'] ?? $page['grapesjs_json'] ?? '');
        if ($editorJson === '') {
            return false;
        }

        $decoded = json_decode($editorJson, true);

        return is_array($decoded)
            && in_array(strtolower(trim((string) ($decoded['projectIntent'] ?? ''))), ['business_profile', 'business-profile'], true);
    }

    public function hasActiveEntitlement(int $landingPageId, ?int $userId = null): bool
    {
        if (! $this->tablesReady()) {
            return false;
        }

        return (new BusinessProfileEntitlementModel())->activeForPage($landingPageId, $userId) !== null;
    }

    public function activatePageFromProductCredit(int $landingPageId, int $userId): bool
    {
        if ($landingPageId <= 0 || $userId <= 0 || ! $this->tablesReady()) {
            return false;
        }

        $productService = new ProductEntitlementService();
        $db = Database::connect();
        $now = date('Y-m-d H:i:s');
        $entitlementModel = new BusinessProfileEntitlementModel();
        $existing = $entitlementModel->where('landing_page_id', $landingPageId)->first();
        $payload = [
            'user_id' => $userId,
            'landing_page_id' => $landingPageId,
            'order_id' => null,
            'status' => 'active',
            'is_lifetime' => 1,
            'activated_at' => $now,
            'updated_at' => $now,
        ];

        $db->transStart();
        if (! $productService->consumeBusinessProfileCredit($userId, $landingPageId)) {
            $db->transRollback();
            return false;
        }

        if ($existing !== null) {
            $entitlementModel->update((int) $existing['id'], $payload);
            $db->transComplete();

            return $db->transStatus();
        }

        $payload['created_at'] = $now;
        $entitlementModel->insert($payload);
        $db->transComplete();

        return $db->transStatus();
    }

    public function ensurePendingOrder(int $userId, int $landingPageId, string $paymentMethod = 'Manual'): ?array
    {
        if ($userId <= 0 || $landingPageId <= 0 || ! $this->tablesReady()) {
            return null;
        }

        $model = new BusinessProfileOrderModel();
        $existing = $model->latestPayableForPage($landingPageId, $userId);
        if ($existing !== null && in_array((string) ($existing['status'] ?? ''), ['pending', 'pending_payment', 'waiting_approval'], true)) {
            if (in_array((string) ($existing['status'] ?? ''), ['pending', 'pending_payment'], true)
                && empty($existing['payment_proof'])
                && (string) ($existing['payment_method'] ?? '') !== $paymentMethod
            ) {
                $model->update((int) $existing['id'], [
                    'payment_method' => $paymentMethod,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                return $model->findByUser((int) $existing['id'], $userId);
            }

            return $existing;
        }

        $now = date('Y-m-d H:i:s');
        $orderId = $model->insert([
            'user_id' => $userId,
            'landing_page_id' => $landingPageId,
            'invoice_number' => $model->makeInvoiceNumber(),
            'amount' => self::PRICE,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ], true);

        return $orderId ? $model->findByUser((int) $orderId, $userId) : null;
    }

    public function activatePaidOrder(int $orderId): bool
    {
        if ($orderId <= 0 || ! $this->tablesReady()) {
            return false;
        }

        $orderModel = new BusinessProfileOrderModel();
        $order = $orderModel->findAdminOrder($orderId);
        if ($order === null) {
            return false;
        }

        $userId = (int) ($order['user_id'] ?? 0);
        $landingPageId = (int) ($order['landing_page_id'] ?? 0);
        if ($userId <= 0 || $landingPageId <= 0) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        if ((string) ($order['status'] ?? '') !== 'paid') {
            $orderModel->update($orderId, [
                'status' => 'paid',
                'paid_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $entitlementModel = new BusinessProfileEntitlementModel();
        $existing = $entitlementModel->where('landing_page_id', $landingPageId)->first();
        $payload = [
            'user_id' => $userId,
            'landing_page_id' => $landingPageId,
            'order_id' => $orderId,
            'status' => 'active',
            'is_lifetime' => 1,
            'activated_at' => $now,
            'updated_at' => $now,
        ];

        if ($existing !== null) {
            return (bool) $entitlementModel->update((int) $existing['id'], $payload);
        }

        $payload['created_at'] = $now;

        return (bool) $entitlementModel->insert($payload);
    }

    public function checkoutUrl(int $landingPageId): string
    {
        return site_url('business-profile/' . $landingPageId . '/checkout');
    }
}
