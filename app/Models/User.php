<?php

namespace App\Models;

use CodeIgniter\Model;

class User extends Model
{
    protected $table = 'salon_users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['fname','lname','phone','email','password','role','permissions','salon_id','wage','rate','sequence','address','joining_date','last_working_date','is_active','created_by','updated_by','created_at','updated_at','deleted_at'];
}