<?php

namespace App\Models;

use CodeIgniter\Model;

class Staff_rate extends Model
{
    protected $table = 'salon_staff_rates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['salon_id', 'staff_id', 'wage', 'rate', 'start_date', 'created_by', 'created_at', 'deleted_at'];
}