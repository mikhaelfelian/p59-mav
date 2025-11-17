<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentCashbackRuleModel extends Model
{
    protected $table            = 'agent_cashback_rule';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'agent_id',
        'window_days',
        'min_transaction',
        'cashback_amount',
        'is_stackable',
        'is_active',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [];
}


