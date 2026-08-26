<?php

namespace App\Models;

use CodeIgniter\Model;

class SellerWithdrawRequestModel extends Model
{
    protected $table = 'seller_withdraw_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'amount',
        'bank_name',
        'account_number',
        'account_holder_name',
        'status',
        'admin_id',
        'admin_note',
        'requested_at',
        'approved_at',
        'paid_at',
        'rejected_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
