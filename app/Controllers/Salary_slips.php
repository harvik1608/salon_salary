<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\Salon;
use App\Models\User;
use App\Models\Attendance;

require_once(APPPATH . 'Views/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Salary_slips extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('salary_slip')) {
                $data["load_ajax_url"] = base_url("load-salary-slips");
                $data["userdata"] = $userdata = $session->get('userdata');

                $model = new Salon;
                $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                return view('salary_slip/list',$data);
            } else {
                $session->setFlashData("error","You don't have permission to access salary slip pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function load()
    {
        $session = session();
        $userdata = $session->get('userdata');
        $current_staff_id = $userdata['id'];

        $post = $this->request->getVar();
        $result = array("data" => array());

        // Extract DataTable parameters
        $draw = $post['draw'];
        $start = (int) $post['start'];
        $length = (int) $post['length'];
        $searchValue = $post['search']['value'];
        $orderColumn = $post['order'][0]['column'];
        $orderDir = $post['order'][0]['dir'];
        
        $columns = ['id', 'name', 'is_active'];
        $orderBy = $columns[$orderColumn] ?? 'id';

        $model = db_connect();
        $query = $model->table("salon_covers sc");
        $query = $query->join("salons s","s.id=sc.salon_id");
        $query = $query->select("sc.id,s.name as salon,sc.amount,sc.tip,sc.date,s.currency");
        $query = $query->where("sc.deleted_at IS NULL");
        if(isset($post["salon_id"]) && $post["salon_id"] != "") {
            $query = $query->where("s.id",$post["salon_id"]);
        }
        // if(isset($post["staff_id"]) && $post["staff_id"] != "") {
        //     $query = $query->where("su.id",$post["staff_id"]);
        // }
        if(isset($post["payment_mode_id"]) && $post["payment_mode_id"] != "") {
            $query = $query->where("spm.id",$post["payment_mode_id"]);
        }
        if(isset($post["datetime"]) && $post["datetime"] != "") {
            $query = $query->like("sc.date",$post["datetime"]);
        }
        if(isset($post["date"]) && $post["date"] != "") {
            $query = $query->where("sc.date",$post["date"]);
        }
        if(isset($post["amount"]) && $post["amount"] != "") {
            $query = $query->where("sc.amount",$post["amount"]);
        }
        if(isset($post["tip"]) && $post["tip"] != "") {
            $query = $query->where("sc.tip_amount",$post["tip"]);
        }
        // if (!empty($searchValue)) {
        //     $query = $query->like('c1.name OR s.name OR c2.name', $searchValue);
        // }
        $totalRecords = $query->countAllResults(false);
        $query = $query->orderBy($orderBy, $orderDir)->limit($length, $start);
        $entries = $query->get()->getResultArray();
        foreach ($entries as $key => $val) {
            $_editUrl = base_url("entries/" . $val["id"] . "/edit");
            $trashUrl = base_url("entries/" . $val["id"]);

            $buttons = "";
            if($userdata['role'] == 1) {
                $buttons .= '<a href="' . $_editUrl . '"><i class="bx bx-eye icon-sm"></i></a>&nbsp;';
                $buttons .= '<a href="' . $_editUrl . '"><i class="bx bx-edit icon-sm"></i></a>&nbsp;';
                $buttons .= '<a href="javascript:;" onclick=remove_row("' . $trashUrl . '")><i class="icon-base bx bx-trash icon-sm"></i></a>';
            }
            $result['data'][$key] = [
                "<small>".($key + 1)."</small>",
                "<small>".$val['salon']."</small>",
                "<small>".date("d M, Y",strtotime($val['date']))."</small>",
                "<small>".$val['currency']." ".$val['amount']."</small>",
                "<small>".$val['currency']." ". $val['tip']."</small>",
                $buttons
            ];
        }

        // Add response metadata
        $result["draw"] = intval($draw);
        $result["recordsTotal"] = $totalRecords;
        $result["recordsFiltered"] = $totalRecords;

        // Output JSON
        echo json_encode($result);
        exit;
    }

    public function new()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('salary_slip')) {
                $userdata = $session->get('userdata');
                if(in_array($userdata["role"],[1,2])) {
                    $model = new Salon;
                    $data["salons"] = $model->select("id,name,currency")->where("is_active",1)->where(default_where())->get()->getResultArray();
                    $data["salary_slip"] = array();
                    return view('salary_slip/admin_add_edit',$data);
                }
            } else {
                $session->setFlashData("error","You don't have permission to access salary slip pages.");
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
            $model = new Entry;

            if($userdata['role'] == 3) {
                $post['created_by'] = $userdata["id"];
                $post['updated_by'] = 0;
                $post['created_at'] = date("Y-m-d H:i:s");
                $post['updated_at'] = "0000-00-00 00:00:00";
                if($model->insert($post)) {
                    $session->setFlashData('success_message',"Entry added successfully.");
                    return $this->response->setJSON(['status' => 'success']);
                } else {
                    return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
                }
            } else {
                $cover_data["salon_id"] = $post["salon_id"];
                $cover_data["amount"] = $post["total_amount"];
                $cover_data["tip"] = $post["tip"];
                $cover_data["date"] = $post["date"];
                $cover_data["note"] = $post["note"];
                $cover_data["created_by"] = $userdata["id"];
                $cover_data["updated_by"] = 0;
                $cover_data["created_at"] = date("Y-m-d H:i:s");
                $cover_data["updated_at"] = "0000-00-00 00:00:00";
                $model = new Cover;
                $model->insert($cover_data);
                $cover_id = $model->getInsertID();

                $insert_data = [];
                for($i = 0; $i < count($post["price"]); $i ++) {
                    $insert_data[] = array(
                        "cover_id" => $cover_id,
                        "payment_mode_id" => $post["mode_id"][$i],
                        "amount" => $post["price"][$i]
                    );
                }
                $model = new Cover_entry;
                if($model->insertBatch($insert_data)) {
                    $session->setFlashData('success_message',"Entry added successfully.");
                    return $this->response->setJSON(['status' => 'success']);
                } else {
                    return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
                }
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function edit($entry_id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('entry')) {
                $model = new Cover;
                $data["entry"] = $model->where('id',$entry_id)->where(default_where())->first();
                if($data["entry"]) {
                    $model = new Salon;
                    $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                    $model = new User;
                    $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();

                    $model = new Payment_mode;
                    $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();
                    
                    $model = new Cover_entry;
                    if($data["modes"]) {
                        foreach($data["modes"] as $key => $val) {
                            $mode = $model->select("amount")->where("cover_id",$entry_id)->where("payment_mode_id",$val["id"])->first();
                            if($mode) {
                                $data["modes"][$key]['price'] = $mode['amount'];
                            }
                        }
                    }
                    return view('entry/admin_add_edit',$data);
                } else {
                    return redirect("entries");
                }
            } else {
                $session->setFlashData("error","You don't have permission to access entry pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function update($entry_id)
    {
        try {
            $post = $this->request->getVar();
            
            $session = session();
            $userdata = $session->get('userdata');
            if($userdata['role'] == 3) {
                $post['updated_by'] = $userdata["id"];
                $post['updated_at'] = date("Y-m-d H:i:s");
                $model = new Entry;
                if($model->update($entry_id,$post)) {
                    $session->setFlashData('success_message',"Entry edited successfully.");
                    return $this->response->setJSON(['status' => 'success']);
                } else {
                    return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
                }
            } else {
                $cover_data["salon_id"] = $post["salon_id"];
                $cover_data["amount"] = $post["total_amount"];
                $cover_data["tip"] = $post["tip"];
                $cover_data["date"] = $post["date"];
                $cover_data["note"] = $post["note"];
                $cover_data["updated_by"] = $userdata["id"];                
                $cover_data["updated_at"] = date("Y-m-d H:i:s");
                $model = new Cover;
                $model->update($entry_id,$cover_data);
                $cover_id = $entry_id;

                $model = new Cover_entry;
                $model->where("cover_id",$cover_id)->delete();

                $insert_data = [];
                for($i = 0; $i < count($post["price"]); $i ++) {
                    $insert_data[] = array(
                        "cover_id" => $cover_id,
                        "payment_mode_id" => $post["mode_id"][$i],
                        "amount" => $post["price"][$i]
                    );
                }
                $model = new Cover_entry;
                if($model->insertBatch($insert_data)) {
                    $session->setFlashData('success_message',"Entry edited successfully.");
                    return $this->response->setJSON(['status' => 'success']);
                } else {
                    return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
                }
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function delete($salon_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['deleted_at'] = date("Y-m-d H:i:s");
            $model = new Entry;
            if($model->update($salon_id,$post)) {
                $session->setFlashData('success_message',"Entry deleted successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'success']);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function get_monthly_checkins()
    {
        $post = $this->request->getVar();
        $data["currency"] = $post["currency"];

        $model = new User;
        $data["staffs"] = $model->select("id,fname,lname,wage,rate")->where(default_where())->where("salon_id",$post["salon_id"])->get()->getResultArray();
        if($data["staffs"]) {
            $model = new Attendance;
            foreach ($data["staffs"] as $key => $val) {
                $row = $model->selectSum("hours_diff")->selectSum("tip")->where("salon_id",$post["salon_id"])->where("staff_id",$val["id"])->like("date",$post["month_year"])->get()->getRowArray();
                if($row && $row["hours_diff"] != "") {
                    $data["staffs"][$key]["hour_per_day"] = $row["hours_diff"];
                } 
                if($row && $row["tip"] != "") {
                    $data["staffs"][$key]["tip"] = $row["tip"];
                }           
            }
        }
        return view('salary_slip/ajax_get_monthly_checkins',$data);
    }
}
