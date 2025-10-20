<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\User;

class Accoutants extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('attendance')) {
                $db = db_connect();
                $result = $db->table("salon_users su");
                $result = $result->join("salon_users u1","u1.id=su.created_by");
                $result = $result->select("su.*,CONCAT(u1.fname,' ',u1.lname) AS added_by");
                $result = $result->where("su.deleted_at IS NULL");
                $result = $result->where("su.role",2);
                $result = $result->orderBy("su.id","desc");
                $data["accoutants"] = $result->get()->getResultArray();
                return view('accoutant/list',$data);
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
            if(check_permission('attendance')) {
                $data["accoutant"] = array();
                return view('accoutant/add_edit',$data);
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

            $model = new User;
            $count = $model->where('email',$this->request->getVar('email'))->where(default_where())->get()->getNumRows();
            if($count == 0) {
                $post = $this->request->getVar();
                $post["permissions"] = implode(",",$post["permission"]);

                $post["password"] = md5($post["password"]);
                $post["role"] = 2;
                $post['created_by'] = $userdata["id"];
                $post['updated_by'] = 0;
                $post['created_at'] = date("Y-m-d H:i:s");
                $post['updated_at'] = "0000-00-00 00:00:00";
                if($model->insert($post)) {
                    $session->setFlashData('success_message',"Accoutant added successfully.");
                    return $this->response->setJSON(['status' => 'success']);
                } else {
                    return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.']);
                }
            } else {
                return $this->response->setJSON(['status' => 'error','message' => 'Email already used.']);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function edit($salon_id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('attendance')) {
                $model = new User;
                $data["accoutant"] = $model->where('id',$salon_id)->where(default_where())->first();
                if($data["accoutant"]) {
                    return view('accoutant/add_edit',$data);
                } else {
                    return redirect("salons");
                }
            } else {
                $session->setFlashData("success_message","You don't have permission to access entry pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function update($accoutant_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $model = new User;
            $count = $model->where('email',$this->request->getVar('email'))->where("id !=",$accoutant_id)->where(default_where())->get()->getNumRows();
            if($count == 0) {
                $post = $this->request->getVar();
                if($post["password"] == "") {
                    unset($post["password"]);
                } else {
                    $post["password"] = md5($post["password"]);
                }
                $post["permissions"] = implode(",",$post["permission"]);
                $post['updated_by'] = $userdata["id"];
                $post['updated_at'] = date("Y-m-d H:i:s");
                $model = new User;
                if($model->update($accoutant_id,$post)) {
                    $session->setFlashData('success_message',"Accoutant edited successfully.");
                    return $this->response->setJSON(['status' => 'success']);
                } else {
                    return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
                }
            } else {
                return $this->response->setJSON(['status' => 'error','message' => 'Email already used.']);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function delete($accoutant_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['deleted_at'] = date("Y-m-d H:i:s");
            $model = new User;
            if($model->update($accoutant_id,$post)) {
                $session->setFlashData('success_message',"Accoutant deleted successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'success']);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}
