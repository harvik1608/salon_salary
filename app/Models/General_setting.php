<?php

namespace App\Models;

use CodeIgniter\Model;

class General_setting extends Model
{
    protected $table = 'salon_general_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['setting_key', 'setting_val'];
}