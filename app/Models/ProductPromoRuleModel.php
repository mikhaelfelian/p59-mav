<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductPromoRuleModel extends Model
{
    protected $table            = 'product_promo_rule';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'item_id',
        'bonus_item_id',
        'min_qty',
        'bonus_qty',
        'is_multiple',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'item_id'       => 'required|integer|is_natural_no_zero',
        'bonus_item_id' => 'required|integer|is_natural_no_zero',
        'min_qty'       => 'required|integer|greater_than_equal_to[1]',
        'bonus_qty'     => 'required|integer|greater_than_equal_to[1]',
        'is_multiple'   => 'in_list[0,1]',
        'start_date'    => 'permit_empty|valid_date',
        'end_date'      => 'permit_empty|valid_date',
        'status'        => 'required|in_list[active,inactive]'
    ];
}


