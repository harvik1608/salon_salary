<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\Salon;
use App\Models\User;
use App\Models\Staff_rate;

class Staffs extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('staff')) {
                $db = db_connect();
                $result = $db->table("salon_users su");
                $result = $result->join("salons s","s.id=su.salon_id");
                $result = $result->select("su.*,s.name AS salon,s.currency");
                $result = $result->where("su.deleted_at IS NULL");
                $result = $result->where("su.role",3);
                $result = $result->orderBy("su.id","desc");
                $data["staffs"] = $result->get()->getResultArray();
                return view('staff/list',$data);
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
            if(check_permission('staff')) {
                $data["staff"] = array();

                $model = new Salon;
                $data["salons"] = $model->select("id,name")->where(default_where())->where("is_active",1)->get()->getResultArray();
                return view('staff/add_edit',$data);
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
            // $count = $model->where('email',$this->request->getVar('email'))->where(default_where())->get()->getNumRows();
            $count = 0;
            if($count == 0) {
                $post = $this->request->getVar();
                $post["permissions"] = implode(",",$post["permission"]);

                $post["password"] = md5($post["password"]);
                $post["role"] = 3;
                $post["last_working_date"] = $post["last_working_date"] == "" ? null : $post["last_working_date"];
                $post['created_by'] = $userdata["id"];
                $post['updated_by'] = 0;
                $post['created_at'] = date("Y-m-d H:i:s");
                $post['updated_at'] = "0000-00-00 00:00:00";
                if($model->insert($post)) {
                    $staff_id = $model->getInsertID();
                    $staff_rate['salon_id'] = $post["salon_id"];
                    $staff_rate['staff_id'] = $staff_id;
                    $staff_rate['wage'] = $post["wage"];
                    $staff_rate['rate'] = $post["rate"];
                    $staff_rate['start_date'] = $post["joining_date"] != "" ? $post["joining_date"] : date("Y-m-d");
                    $staff_rate['created_by'] = $userdata["id"];
                    $staff_rate['created_at'] = date("Y-m-d H:i:s");
                    $model = new Staff_rate;
                    $model->insert($staff_rate);

                    $session->setFlashData('success_message',"Staff added successfully.");
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

    public function show($id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('staff')) {
                $userdata = $session->get('userdata');
                $data["current_user_id"] = $userdata['id'];
                $data["page_title"] = "Staffs";

                $db = db_connect();
                $result = $db->table("salon_users su");
                $result = $result->join("salons s","s.id=su.salon_id");
                $result = $result->select("su.*,s.name AS salon,s.currency");
                $result = $result->where("su.deleted_at IS NULL");
                $result = $result->where("su.id",$id);
                $data["profile"] = $result->get()->getRowArray();
                return view('profile',$data);
            } else {
                $session->setFlashData("success_message","You don't have permission to access staff pages.");
                return redirect("dashboard");
            }
        }
    }

    public function edit($staff_id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('staff')) {
                $model = new User;
                $data["staff"] = $model->where('id',$staff_id)->where(default_where())->first();
                if($data["staff"]) {
                    $model = new Salon;
                    $data["salons"] = $model->select("id,name")->where(default_where())->where("is_active",1)->get()->getResultArray();

                    return view('staff/add_edit',$data);
                } else {
                    return redirect("staffs");
                }
            } else {
                $session->setFlashData("success_message","You don't have permission to access entry pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function update($staff_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $model = new User;
            // $count = $model->where('email',$this->request->getVar('email'))->where("id !=",$staff_id)->where(default_where())->get()->getNumRows();
            $count = 0;
            if($count == 0) {
                $model = new User;
                $old_staff = $model->select("salon_id,wage,rate")->where("id",$staff_id)->first();

                $post = $this->request->getVar();
                if($post["password"] == "") {
                    unset($post["password"]);
                } else {
                    $post["password"] = md5($post["password"]);
                }
                $post["last_working_date"] = $post["last_working_date"] == "" ? null : $post["last_working_date"];
                $post["permissions"] = implode(",",$post["permission"]);
                $post['updated_by'] = $userdata["id"];
                $post['updated_at'] = date("Y-m-d H:i:s");
                if($model->update($staff_id,$post)) {
                    // if($old_staff["salon_id"] != $post["salon_id"] || $old_staff["wage"] != $post["wage"] || $old_staff["rate"] != $post["rate"]) {
                    //     $staff_rate['salon_id'] = $post["salon_id"];
                    //     $staff_rate['staff_id'] = $staff_id;
                    //     $staff_rate['wage'] = $post["wage"];
                    //     $staff_rate['rate'] = $post["rate"];
                    //     $staff_rate['created_by'] = $userdata["id"];
                    //     $staff_rate['created_at'] = date("Y-m-d H:i:s");
                    //     $model = new Staff_rate;
                    //     $model->insert($staff_rate);
                    // }
                    $session->setFlashData('success_message',"Staff edited successfully.");
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

    public function delete($staff_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['deleted_at'] = date("Y-m-d H:i:s");
            $model = new User;
            if($model->update($staff_id,$post)) {
                $session->setFlashData('success_message',"Staff deleted successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'success']);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}
