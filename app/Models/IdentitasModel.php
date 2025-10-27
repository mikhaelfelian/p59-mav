<?php
/**
 * Admin Template Codeigniter 4
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models;

/**
 * Identitas Model
 * Handles application identity data
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @version    4.3.1
 *
 * Modified by Mikhael Felian Waskito
 * @link       https://github.com/mikhaelfelian/p59-mav
 * @notes      Refactored to CI4-compliant structure using BaseModel inheritance
 */
class IdentitasModel extends BaseModel
{
    protected $table = 'identitas';
    protected $primaryKey = 'id_identitas';
    protected $allowedFields = [
        'nama',
        'alamat',
        'id_wilayah_kelurahan',
        'email',
        'no_telp'
    ];
    protected $useTimestamps = true;

    /**
     * Get identitas data
     * 
     * @return array|null
     */
    public function getIdentitas(): ?array
    {
        return $this->builder($this->table)
                   ->get()
                   ->getRowArray();
    }

    /**
     * Save identitas data
     * 
     * @return array
     */
    public function saveData(): array
    {
        $data = [
            'nama' => $this->request->getPost('nama'),
            'alamat' => $this->request->getPost('alamat'),
            'id_wilayah_kelurahan' => $this->request->getPost('id_wilayah_kelurahan'),
            'email' => $this->request->getPost('email'),
            'no_telp' => $this->request->getPost('no_telp')
        ];

        $updated = $this->builder($this->table)
                       ->update($data);

        if ($updated) {
            return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
        }

        return ['status' => 'error', 'message' => 'Data gagal disimpan'];
    }
}
