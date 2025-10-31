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
        'rule_type',
        'min_transaction',
        'cashback_amount',
        'is_active',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'agent_id'        => 'required|integer|is_natural_no_zero',
        'rule_type'       => 'required|in_list[cashback,akumulasi]',
        'min_transaction' => 'permit_empty|decimal',
        'cashback_amount' => 'permit_empty|decimal',
        'is_active'       => 'in_list[0,1]',
        'start_date'      => 'permit_empty|valid_date',
        'end_date'        => 'permit_empty|valid_date',
    ];
}


