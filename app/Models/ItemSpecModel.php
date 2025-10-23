<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Model for managing item specifications data
 * This file represents the Model.
 */
class ItemSpecModel extends Model
{
    protected $table = 'item_spec';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'name',
        'slug',
        'description',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'slug' => 'permit_empty|max_length[100]',
        'status' => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama spesifikasi harus diisi',
            'max_length' => 'Nama spesifikasi maksimal 100 karakter'
        ],
        'slug' => [
            'max_length' => 'Slug maksimal 100 karakter'
        ],
        'status' => [
            'in_list' => 'Status harus 0 atau 1'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Generate unique 6-digit numeric code for item specification
     * 
     * @param string $name The name to generate code from
     * @return string Generated code
     */
    public function generateCode($name)
    {
        // Get the first two uppercase letters from the name
        $prefix = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 2));

        // Last 2 digits of current year
        $year = date('Y');
        $yearSuffix = substr($year, -2);

        // Generate 2-digit sequence number
        $sequence = $this->getNextSequence($prefix . $yearSuffix);

        return $prefix . $yearSuffix . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get next sequence number for the given prefix
     * 
     * @param string $prefix The prefix to check
     * @return int Next sequence number
     */
    private function getNextSequence($prefix)
    {
        // Get the last record with similar prefix
        $lastRecord = $this->where('code LIKE', $prefix . '%')
                          ->orderBy('code', 'DESC')
                          ->first();

        if (!$lastRecord) {
            return 1;
        }

        // Extract sequence number from the last code
        $lastCode = $lastRecord->code;
        $sequence = (int) substr($lastCode, -2);
        
        return $sequence + 1;
    }
}
