<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

use CodeIgniter\Model;

/**
 * Module Status Model
 * Handles module status definitions
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      module_status
 * @version    4.3.1
 */
class ModuleStatusModel extends Model
{
    protected $table = 'module_status';
    protected $primaryKey = 'id_module_status';
    protected $allowedFields = ['nama_status', 'deskripsi'];
    protected $useTimestamps = false;
    
    /**
     * Get all module statuses
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * Get status by ID
     * 
     * @param int $id Status ID
     * @return array|null
     */
    public function getById($id)
    {
        $result = $this->find($id);
        return $result ? (array)$result : null;
    }
}

