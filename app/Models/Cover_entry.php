<?php

namespace App\Models;

use CodeIgniter\Model;

class Cover_entry extends Model
{
    protected $table = 'salon_cover_entries';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['cover_id', 'payment_mode_id', 'amount', 'deleted_at'];
}