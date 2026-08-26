<?php

namespace App\Models;

use CodeIgniter\Model;

class CreatorTemplateRoyaltyModel extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_REVERSED = 'reversed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'creator_template_royalties';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'template_id',
        'marketplace_template_id',
        'invitation_id',
        'usage_id',
        'creator_user_id',
        'buyer_user_id',
        'order_id',
        'license_value',
        'currency',
        'creator_rate',
        'creator_amount',
        'platform_amount',
        'status',
        'qualified_at',
        'available_at',
        'reversed_at',
        'cancelled_at',
        'note',
        'metadata',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findForInvitation(int $invitationId): ?array
    {
        if ($invitationId <= 0) {
            return null;
        }

        return $this->where('invitation_id', $invitationId)->first();
    }

    public function findForUsage(int $usageId): ?array
    {
        if ($usageId <= 0) {
            return null;
        }

        return $this->where('usage_id', $usageId)->first();
    }
}
