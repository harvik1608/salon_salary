<?php

namespace App\Controllers;

use App\Models\General_setting;

class CommonController extends BaseController
{
    public function __construct()
    {
        $model = new General_setting;
        $_rows = $model->get()->getResultArray();
        if(!empty($_rows)) {
            foreach ($_rows as $row) {
                if(!defined(strtoupper($row["setting_key"]))) {
                    define(strtoupper($row["setting_key"]),$row["setting_val"]);
                }
            }
        }
    }
}
