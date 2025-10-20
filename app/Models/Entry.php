<?php

namespace App\Models;

use CodeIgniter\Model;

class Entry extends Model
{
    protected $table = 'salon_cover_entries';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['cover_id','salon_id', 'staff_id', 'payment_mode_id', 'amount', 'tip_amount', 'date', 'time', 'note', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];
}