<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\Payment_mode;
use App\Models\Salon;

class Payment_modes extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('payment_mode')) {
                $db = db_connect();
                $sql = "SELECT spm.id,spm.name,spm.is_active,SUM(se.amount) AS total FROM  salon_payment_modes spm LEFT JOIN  salon_cover_entries se ON se.payment_mode_id = spm.id WHERE spm.deleted_at IS NULL GROUP BY spm.id, spm.name";
                $query = $db->query($sql);
                $data["payment_modes"] = $query->getResultArray();
                return view('payment_mode/list',$data);
            } else {
                $session->setFlashData("success_message","You don't have permission to access entry pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function new()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('payment_mode')) {
                $data["payment_mode"] = array();
                return view('payment_mode/add_edit',$data);
            } else {
                $session->setFlashData("success_message","You don't have permission to access entry pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function create()
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['created_by'] = $userdata["id"];
            $post['updated_by'] = 0;
            $post['created_at'] = date("Y-m-d H:i:s");
            $post['updated_at'] = "0000-00-00 00:00:00";
            $model = new Payment_mode;
            if($model->insert($post)) {
                $session->setFlashData('success_message',"Payment mode added successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function edit($payment_mode_id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('payment_mode')) {
                $model = new Payment_mode;
                $data["payment_mode"] = $model->where('id',$payment_mode_id)->where(default_where())->first();
                if($data["payment_mode"]) {
                    return view('payment_mode/add_edit',$data);
                } else {
                    return redirect("payment_modes");
                }
            } else {
                $session->setFlashData("success_message","You don't have permission to access entry pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function update($payment_mode_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['updated_by'] = $userdata["id"];
            $post['updated_at'] = date("Y-m-d H:i:s");
            $model = new Payment_mode;
            if($model->update($payment_mode_id,$post)) {
                $session->setFlashData('success_message',"Payment mode edited successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function delete($payment_mode_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['deleted_at'] = date("Y-m-d H:i:s");
            $model = new Payment_mode;
            if($model->update($payment_mode_id,$post)) {
                $session->setFlashData('success_message',"Payment Mode deleted successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'success']);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function mode_wise_chart($id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('payment_mode')) {
                // $data["mode_labels"] = [];
                // $data["mode_values"] = [];
                $db = db_connect();

                $model = new Payment_mode;
                $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();
                if($data["modes"]) {
                    foreach($data["modes"] as $mod_key => $mod_val) {
                        $sql = "SELECT s.id,s.name,SUM(sce.amount) AS total FROM salons s LEFT JOIN salon_covers sc ON sc.salon_id = s.id LEFT JOIN salon_cover_entries sce ON sce.cover_id = sc.id WHERE sce.payment_mode_id = ".$mod_val['id']." AND s.deleted_at IS NULL GROUP BY s.id;";
                        $query = $db->query($sql);
                        $result = $query->getResult();
                        if($result) {
                            foreach($result as $row) {
                                $mod_val[$mod_key]["mode_labels"] = $row->name;
                                $mod_val[$mod_key]["mode_values"] = $row->total;
                            }
                        } 
                    }
                }
                preview($data["modes"]);
                return view('payment_mode/chart',$data);
            } else {
                $session->setFlashData("success_message","You don't have permission to access mode pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }
}
