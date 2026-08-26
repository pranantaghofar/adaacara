<?php

namespace App\Models;

use CodeIgniter\Model;

class SellerWalletLedgerModel extends Model
{
    protected $table = 'seller_wallet_ledger';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'template_id',
        'invitation_id',
        'usage_id',
        'order_id',
        'payment_id',
        'plan_name',
        'amount_base',
        'commission_rate',
        'commission_amount',
        'commission_source',
        'type',
        'direction',
        'amount',
        'status',
        'note',
        'metadata',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
