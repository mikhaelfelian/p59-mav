<?php

namespace App\Models;

use CodeIgniter\Model;

class WilayahKelurahanModel extends Model
{
    protected $table = 'wilayah_kelurahan';
    protected $primaryKey = 'id_wilayah_kelurahan';
    protected $returnType = 'object';
    protected $allowedFields = ['id_wilayah_kecamatan', 'nama_kelurahan', 'kode_pos'];
}
