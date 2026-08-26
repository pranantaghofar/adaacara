<?php

namespace App\Models;

use CodeIgniter\Model;

class InvitationTemplateUsageModel extends Model
{
    protected $table = 'invitation_template_usages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'invitation_id',
        'template_id',
        'template_owner_user_id',
        'used_by_user_id',
        'status',
        'commission_status',
        'commission_amount',
        'published_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
