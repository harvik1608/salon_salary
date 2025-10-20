<?php

namespace App\Models;

use CodeIgniter\Model;

class Staff_note extends Model
{
    protected $table = 'salon_year_notes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['year','note','staff_id', 'created_by', 'created_at','updated_by','updated_at','deleted_at'];
}