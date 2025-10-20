<?php

namespace App\Models;

use CodeIgniter\Model;

class Attendance extends Model
{
    protected $table = 'salon_checkins';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['salon_id','old_salon_id','staff_id','date','in_time','out_time','hours_diff','rate','wage','tip','note','break','is_from_other_salon','created_by','updated_by','created_at','updated_at','deleted_at'];
}