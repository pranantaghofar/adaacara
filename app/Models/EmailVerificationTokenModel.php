<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailVerificationTokenModel extends Model
{
    protected $table = 'email_verification_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'email',
        'selector',
        'token_hash',
        'expires_at',
        'used_at',
        'created_at',
    ];

    protected $useTimestamps = false;
}
