<?php

namespace App\Models;

use CodeIgniter\Model;

class Salon extends Model
{
    protected $table = 'salons';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['name','phone','email','address','currency','note','stime','etime','is_active','created_by','updated_by','created_at','updated_at','deleted_at'];
}