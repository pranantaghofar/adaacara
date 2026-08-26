<?php

namespace App\Models;

use CodeIgniter\Model;

class CreatorTemplateRoyaltyEventModel extends Model
{
    protected $table = 'creator_template_royalty_events';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'event_type',
        'template_id',
        'marketplace_template_id',
        'invitation_id',
        'usage_id',
        'creator_user_id',
        'buyer_user_id',
        'order_id',
        'royalty_id',
        'metadata',
        'created_at',
    ];

    protected $useTimestamps = false;
}
