<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: Model for managing customer master data with CRUD operations
 */

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'customer';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'name',
        'email',
        'phone',
        'address',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'postal_code',
        'country',
        'tax_number',
        'status',
        'plate_code',
        'plate_number',
        'plate_suffix',
        'plat_code',
        'plat_number',
        'plat_last'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'name' => 'required|max_length[255]',
        'email' => 'permit_empty|valid_email|max_length[255]',
        'phone' => 'permit_empty|max_length[20]',
        'plate_code' => 'permit_empty|max_length[10]',
        'plate_number' => 'permit_empty|max_length[10]',
        'plate_suffix' => 'permit_empty|max_length[10]',
        'plat_code' => 'permit_empty|max_length[10]',
        'plat_number' => 'permit_empty|max_length[10]',
        'plat_last' => 'permit_empty|max_length[10]',
        'status' => 'permit_empty|in_list[active,inactive]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind       = [];
    protected $beforeDelete   = [];
    protected $afterDelete     = [];

    /**
     * Find customer by plate number (using plat_code, plat_number, plat_last)
     * 
     * @param string $platCode Plate code (e.g., B, H)
     * @param string $platNumber Plate number (e.g., 4575)
     * @param string|null $platLast Plate last code (e.g., PBP) - optional
     * @return array|null Customer data or null if not found
     */
    public function findByPlate($platCode, $platNumber, $platLast = null)
    {
        $builder = $this->where('plat_code', $platCode)
                        ->where('plat_number', $platNumber);
        
        if ($platLast !== null && $platLast !== '') {
            $builder->where('plat_last', $platLast);
        } else {
            $builder->groupStart()
                    ->where('plat_last IS NULL')
                    ->orWhere('plat_last', '')
                    ->groupEnd();
        }
        
        return $builder->first();
    }
}

