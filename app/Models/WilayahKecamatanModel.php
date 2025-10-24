<?php

namespace App\Models;

use CodeIgniter\Model;

class WilayahKecamatanModel extends Model
{
    protected $table = 'wilayah_kecamatan';
    protected $primaryKey = 'id_wilayah_kecamatan';
    protected $returnType = 'object';
    protected $allowedFields = ['id_wilayah_kabupaten', 'nama_kecamatan'];
}
