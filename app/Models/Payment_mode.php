<?php

namespace App\Models;

use CodeIgniter\Model;

class Payment_mode extends Model
{
    protected $table = 'salon_payment_modes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['name', 'sequence', 'is_active', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];
}