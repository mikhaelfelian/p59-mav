<?php

namespace App\Models;

use CodeIgniter\Model;

class WilayahKabupatenModel extends Model
{
    protected $table = 'wilayah_kabupaten';
    protected $primaryKey = 'id_wilayah_kabupaten';
    protected $returnType = 'object';
    protected $allowedFields = ['id_wilayah_propinsi', 'nama_kabupaten', 'ibukota', 'k_bsni'];
}
