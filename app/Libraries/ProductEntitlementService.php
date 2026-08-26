<?php

namespace App\Libraries;

use App\Models\ProductEntitlementModel;
use Config\Database;

class ProductEntitlementService
{
    public const BUSINESS_PROFILE = 'business_profile';
    public const PHOTOBOOTH_STANDALONE = 'photobooth_standalone';
    public const PHOTOGRAPHER_GALLERY = 'photographer_gallery';

    private const PRODUCT_TYPES = [
        self::BUSINESS_PROFILE,
        self::PHOTOBOOTH_STANDALONE,
        self::PHOTOGRAPHER_GALLERY,
    ];

    public function tableReady(): bool
    {
        return Database::connect()->tableExists('product_entitlements');
    }

    public function isProductPlan(array $plan): bool
    {
        return in_array($this->productType($plan), self::PRODUCT_TYPES, true);
    }

    public function productType(array $plan): string
    {
        return strtolower(trim((string) ($plan['product_type'] ?? 'membership'))) ?: 'membership';
    }

    public function hasActive(int $userId, string $productType): bool
    {
        return (new ProductEntitlementModel())->activeForUser($userId, $productType) !== null;
    }

    public function activateFromPaidOrder(array $order, ?string $activatedAt = null): bool
    {
        $productType = $this->productType($order);
        if (! in_array($productType, self::PRODUCT_TYPES, true) || ! $this->tableReady()) {
            return false;
        }

        $orderId = (int) ($order['id'] ?? 0);
        $userId = (int) ($order['user_id'] ?? 0);
        $planId = (int) ($order['plan_id'] ?? 0);
        if ($orderId <= 0 || $userId <= 0 || $planId <= 0) {
            return false;
        }

        $model = new ProductEntitlementModel();
        $now = $activatedAt ?: date('Y-m-d H:i:s');
        $existing = $model->where('order_id', $orderId)->first();
        $payload = $this->payloadForOrder($order, $productType, $now);

        if ($existing !== null) {
            $payload['updated_at'] = $now;

            return (bool) $model->update((int) $existing['id'], $payload);
        }

        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        return (bool) $model->insert($payload);
    }

    public function consumeBusinessProfileCredit(int $userId, int $landingPageId): bool
    {
        if ($userId <= 0 || $landingPageId <= 0 || ! $this->tableReady()) {
            return false;
        }

        $model = new ProductEntitlementModel();
        $credit = $model->availableCreditForUser($userId, self::BUSINESS_PROFILE);
        if ($credit === null) {
            return false;
        }

        $metadata = json_decode((string) ($credit['metadata'] ?? ''), true);
        if (! is_array($metadata)) {
            $metadata = [];
        }

        $linkedPages = $metadata['landing_page_ids'] ?? [];
        if (! is_array($linkedPages)) {
            $linkedPages = [];
        }
        if (! in_array($landingPageId, array_map('intval', $linkedPages), true)) {
            $linkedPages[] = $landingPageId;
        }
        $metadata['landing_page_ids'] = array_values(array_unique(array_map('intval', $linkedPages)));

        return (bool) $model->update((int) $credit['id'], [
            'quantity_used' => min(
                max(1, (int) ($credit['quantity_used'] ?? 0) + 1),
                max(1, (int) ($credit['quantity_total'] ?? 1))
            ),
            'metadata' => json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function payloadForOrder(array $order, string $productType, string $now): array
    {
        $activeDays = max(1, (int) ($order['active_days'] ?? 365));
        $isLifetime = in_array($productType, [self::BUSINESS_PROFILE, self::PHOTOGRAPHER_GALLERY], true)
            || ((int) ($order['is_lifetime'] ?? 0)) === 1;

        return [
            'user_id' => (int) $order['user_id'],
            'order_id' => (int) $order['id'],
            'plan_id' => (int) $order['plan_id'],
            'product_type' => $productType,
            'status' => 'active',
            'starts_at' => $now,
            'expires_at' => $isLifetime ? null : date('Y-m-d H:i:s', strtotime('+' . $activeDays . ' days', strtotime($now) ?: time())),
            'is_lifetime' => $isLifetime ? 1 : 0,
            'quantity_total' => $productType === self::BUSINESS_PROFILE ? 1 : null,
            'quantity_used' => 0,
            'metadata' => json_encode([
                'source' => 'plans_checkout',
                'invoice_number' => (string) ($order['invoice_number'] ?? ''),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];
    }
}
