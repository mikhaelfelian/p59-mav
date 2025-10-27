<?php
/**
 * Admin Template Codeigniter 4
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models;

/**
 * Wilayah Model
 * Handles geographical region data
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
class WilayahModel extends BaseModel
{
    protected $table = 'wilayah';
    protected $primaryKey = 'id_wilayah';
    protected $allowedFields = [];
    protected $useTimestamps = false;

    private $fotoPath;

    /**
     * Get all provinces
     * 
     * @return array
     */
    public function getPropinsi(): array
    {
        $query = $this->builder('wilayah_propinsi')->get()->getResultArray();
        $result = [];
        foreach ($query as $val) {
            $result[$val['id_wilayah_propinsi']] = $val['nama_propinsi'];
        }
        return $result;
    }

    /**
     * Get regencies by province
     * 
     * @param int $id_propinsi Province ID
     * @return array
     */
    public function getKabupatenByIdPropinsi(int $id_propinsi): array
    {
        $query = $this->builder('wilayah_kabupaten')
                     ->where('id_wilayah_propinsi', $id_propinsi)
                     ->get()
                     ->getResultArray();
        $result = [];
        foreach ($query as $val) {
            $result[$val['id_wilayah_kabupaten']] = $val['nama_kabupaten'];
        }
        return $result;
    }

    /**
     * Get districts by regency
     * 
     * @param int $id_kabupaten Regency ID
     * @return array
     */
    public function getKecamatanByIdKabupaten(int $id_kabupaten): array
    {
        $query = $this->builder('wilayah_kecamatan')
                     ->where('id_wilayah_kabupaten', $id_kabupaten)
                     ->get()
                     ->getResultArray();
        $result = [];
        foreach ($query as $val) {
            $result[$val['id_wilayah_kecamatan']] = $val['nama_kecamatan'];
        }
        return $result;
    }

    /**
     * Get villages by district
     * 
     * @param int $id_kecamatan District ID
     * @return array
     */
    public function getKelurahanByIdKecamatan(int $id_kecamatan): array
    {
        $query = $this->builder('wilayah_kelurahan')
                     ->where('id_wilayah_kecamatan', $id_kecamatan)
                     ->get()
                     ->getResultArray();
        $result = [];
        foreach ($query as $val) {
            $result[$val['id_wilayah_kelurahan']] = $val['nama_kelurahan'];
        }
        return $result;
    }

    /**
     * Get district by village
     * 
     * @param int|null $id_kelurahan Village ID
     * @return array|null
     */
    public function getKecamatanByIdKelurahan(?int $id_kelurahan): ?array
    {
        if (empty($id_kelurahan)) {
            $count = $this->builder('wilayah_kecamatan')->countAllResults();
            $query = $this->builder('wilayah_kecamatan')
                         ->limit(1, ceil($count / 2))
                         ->get()
                         ->getRowArray();
            return $query;
        } else {
            return $this->builder('wilayah_kecamatan')
                       ->select('wilayah_kecamatan.*')
                       ->join('wilayah_kelurahan', 'wilayah_kelurahan.id_wilayah_kecamatan = wilayah_kecamatan.id_wilayah_kecamatan', 'left')
                       ->where('wilayah_kelurahan.id_wilayah_kelurahan', $id_kelurahan)
                       ->get()
                       ->getRowArray();
        }
    }

    /**
     * Get regency by district
     * 
     * @param int $id_kecamatan District ID
     * @return array|null
     */
    public function getKabupatenByIdKecamatan(int $id_kecamatan): ?array
    {
        return $this->builder('wilayah_kabupaten')
                   ->select('wilayah_kabupaten.*')
                   ->join('wilayah_kecamatan', 'wilayah_kecamatan.id_wilayah_kabupaten = wilayah_kabupaten.id_wilayah_kabupaten', 'left')
                   ->where('wilayah_kecamatan.id_wilayah_kecamatan', $id_kecamatan)
                   ->get()
                   ->getRowArray();
    }

    /**
     * Get province by regency
     * 
     * @param int $id_kabupaten Regency ID
     * @return array|null
     */
    public function getPropinsiByIdKabupaten(int $id_kabupaten): ?array
    {
        return $this->builder('wilayah_propinsi')
                   ->select('wilayah_propinsi.*')
                   ->join('wilayah_kabupaten', 'wilayah_kabupaten.id_wilayah_propinsi = wilayah_propinsi.id_wilayah_propinsi', 'left')
                   ->where('wilayah_kabupaten.id_wilayah_kabupaten', $id_kabupaten)
                   ->get()
                   ->getRowArray();
    }
}
