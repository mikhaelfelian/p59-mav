<?php

namespace App\Models;

use CodeIgniter\Model;

class WilayahPropinsiModel extends Model
{
    protected $table = 'wilayah_propinsi';
    protected $primaryKey = 'id_wilayah_propinsi';
    protected $allowedFields = ['nama_propinsi', 'ibukota', 'p_bsni'];
}
