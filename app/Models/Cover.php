<?php

namespace App\Models;

use CodeIgniter\Model;

class Cover extends Model
{
    protected $table = 'salon_covers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['salon_id', 'amount', 'tip',  'date', 'note', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];
}