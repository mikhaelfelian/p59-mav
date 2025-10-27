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
 * Menu Kategori Model
 * Handles menu categories
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      menu_kategori
 * @version    4.3.1
 */
class MenuKategoriModel extends Model
{
    protected $table = 'menu_kategori';
    protected $primaryKey = 'id_menu_kategori';
    protected $allowedFields = ['nama_kategori', 'deskripsi', 'aktif', 'show_title', 'urut'];
    protected $useTimestamps = false;
    
    protected $validationRules = [
        'nama_kategori' => 'required|max_length[100]',
    ];
    
    protected $validationMessages = [
        'nama_kategori' => [
            'required' => 'Category name is required',
            'max_length' => 'Category name cannot exceed 100 characters'
        ],
    ];

    /**
     * Get all categories ordered by urut
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->orderBy('urut', 'ASC')->findAll();
    }

    /**
     * Get category by ID
     * 
     * @param int $id Category ID
     * @return array|null
     */
    public function getById($id)
    {
        $result = $this->find($id);
        return $result ? (array)$result : null;
    }

    /**
     * Get active categories only
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('aktif', 'Y')->orderBy('urut', 'ASC')->findAll();
    }

    /**
     * Get next sort order
     * 
     * @return int
     */
    public function getNextUrut()
    {
        $result = $this->selectMax('urut')->first();
        return ($result['urut'] ?? 0) + 1;
    }

    /**
     * Update sort order for multiple categories
     * 
     * @param array $list_kategori Array of category IDs in order
     * @return bool
     */
    public function updateUrut(array $list_kategori)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        $urut = 1;
        foreach ($list_kategori as $id_kategori) {
            $this->update($id_kategori, ['urut' => $urut]);
            $urut++;
        }
        
        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Delete category and remove references from menu table
     * 
     * @param int $id Category ID
     * @return bool
     */
    public function deleteCategory($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        // Delete category
        $this->delete($id);
        
        // Update menu items to remove category reference
        $db->table('menu')->update(
            ['id_menu_kategori' => null],
            ['id_menu_kategori' => $id]
        );
        
        $db->transComplete();
        return $db->transStatus();
    }
}

