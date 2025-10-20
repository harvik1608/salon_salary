<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\Salon;
use App\Models\User;
use App\Models\Payment_mode;
use App\Models\Attendance;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Attendances extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('attendance')) {
                $data["load_ajax_url"] = base_url("load-attendances");
                $data["current_salon_id"] = $session->get("userdata")["salon_id"];

                $model = new Salon;
                $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                $model = new User;
                $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->orderBy("fname","asc")->get()->getResultArray();

                return view('attendance/list',$data);
            } else {
                $session->setFlashData("error","You don't have permission to access entry pages.");
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
        $query = $model->table("salon_checkins sc");
        $query = $query->join("salons s","s.id=sc.salon_id");
        $query = $query->join("salon_users su","su.id=sc.staff_id");
        $query = $query->select("sc.id,sc.date,sc.staff_id,sc.in_time,sc.out_time,sc.hours_diff,sc.note,sc.rate,sc.created_at,s.name AS salon,CONCAT(su.fname,' ',su.lname) AS staff,sc.break");
        $query = $query->where("sc.deleted_at IS NULL");
        $query = $query->where("sc.in_time !=","00:00:00");
        if(isset($post["salon_id"]) && $post["salon_id"] != "") {
            $query = $query->where("s.id",$post["salon_id"]);
        }
        if(isset($post["staff_id"]) && $post["staff_id"] != "") {
            $query = $query->where("su.id",$post["staff_id"]);
        }
        if(isset($post["datetime"]) && $post["datetime"] != "") {
            $query = $query->like("sc.date",$post["datetime"]);
        }
        if(isset($post["date"]) && $post["date"] != "") {
            $query = $query->where("sc.date",$post["date"]);
        }
        $totalRecords = $query->countAllResults(false);
        $query = $query->orderBy($orderBy, $orderDir)->limit($length, $start);
        $checkins = $query->get()->getResultArray();
        foreach ($checkins as $key => $val) {
            $_editUrl = base_url("attendances/" . $val["id"] . "/edit");
            $trashUrl = base_url("attendances/" . $val["id"]);

            $buttons = "";
            if(is_null($val["out_time"])) {
                $buttons .= '<a href="javascript:;" class="btn btn-sm btn-warning" onclick=checkout('.$val['id'].',"'.$val['in_time'].'","'.$val['date'].'")>Check out</a>&nbsp;';
            }
            $buttons .= '<a href="' . $_editUrl . '"><i class="bx bx-edit icon-sm"></i></a>&nbsp;';
            $buttons .= '<a href="javascript:;" onclick=remove_row("' . $trashUrl . '")><i class="icon-base bx bx-trash icon-sm"></i></a>';
            
            // if($userdata['role'] == 1) {
            //     $buttons .= '<a href="' . $_editUrl . '"><i class="bx bx-edit icon-sm"></i></a>&nbsp;';
            //     $buttons .= '<a href="javascript:;" onclick=remove_row("' . $trashUrl . '")><i class="icon-base bx bx-trash icon-sm"></i></a>';
            // }
            if($buttons == "") {
                $buttons = "-"; 
            }
            $hours = is_null($val['hours_diff']) ? "-" : $val['hours_diff']." hours"; 
            $result['data'][$key] = [
                "<small>".($key + 1)."</small>",
                "<small>".$val['salon']."</small>",
                "<small>".$val['staff']."</small>",
                "<small>".date("d M, Y",strtotime($val['date']))."</small>",
                "<small>".date("h:i A",strtotime($val['in_time']))."</small>",
                 "<small>".date("h:i A",strtotime($val['out_time']))."</small>",
                 "<small>".$val['break']." Min.</small>",
                "<small>".$hours."</small>",
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
            if(check_permission('entry')) {
                if($session->get("userdata")['role'] == 3) {
                    $data["entry"] = array();
                    $data['current_staff_id'] = $session->get('userdata')['id'];

                    $model = new Salon;
                    $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                    $model = new User;
                    $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();

                    $model = new Payment_mode;
                    $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                    return view('attendance/add_edit',$data);
                } else {
                    $model = new Salon;
                    $data["salons"] = $model->select("id,name,stime,etime")->where("is_active",1)->where(default_where())->get()->getResultArray();

                    $model = new User;
                    $data["staffs"] = $model->select("id,fname,lname,salon_id,rate")->where(array("is_active" => 1,"role" => 3))->where(default_where())->orderBy("sequence","asc")->get()->getResultArray();

                    return view('attendance/admin_add_edit',$data);
                }
            } else {
                $session->setFlashData("error","You don't have permission to access entry pages.");
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
            $model = new Attendance;
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
                $insert_data = [];
                for($i = 0; $i < count($post["staff_id"]); $i ++) {
                    $is_from_other_salon = 0;
                    if($post["salon_id"][$i] != $post["old_salon_id"][$i]) {
                        $is_from_other_salon = 1;
                    }
                    // $stime = $post["in_time"][$i];
                    // $etime = $post["out_time"][$i];
                    // $break = "00:".$post["break"][$i].":00";

                    // $start = new \DateTime($stime);
                    // $end = new \DateTime($etime);
                    // $total_duration = $start->diff($end);
                    // $break_parts = explode(":", $break);
                    // $break_in_seconds = ($break_parts[0]*3600) + ($break_parts[1]*60) + ($break_parts[2]);
                    // $total_in_time_seconds = ($total_duration->h*3600)+($total_duration->i*60)+($total_duration->s);
                    // $working_time_in_seconds = $total_in_time_seconds-$break_in_seconds;
                    // $hours = floor($working_time_in_seconds/3600);
                    // $minutes = floor(($working_time_in_seconds%3600)/60); 
                    // $hours = $hours + ($minutes/60);

                    $insert_data[] = array(
                        "salon_id" => $post["salon_id"][$i],
                        "old_salon_id" => $post["old_salon_id"][$i],
                        "staff_id" => $post["staff_id"][$i],
                        "date" => $post["date"],
                        "in_time" => $post["in_time"][$i],
                        "out_time" => $post["out_time"][$i],
                        "break" => $post["break"][$i],
                        "hours_diff" => (float) $post["hours"][$i],
                        "is_from_other_salon" => $is_from_other_salon,
                        "note" => "",
                        "rate" => $post["rate"][$i],
                        "tip" => $post["tip"][$i],
                        "created_by" => $userdata['id'],
                        "updated_by" => $userdata['id'],
                        "created_at" => date("Y-m-d H:i:s"),
                        "updated_at" => "0000-00-00 00:00:00"
                    );
                }
                if($model->insertBatch($insert_data)) {
                    $session->setFlashData('success_message',"Attendance added successfully.");
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
            if(check_permission('attendance')) {
                $model = new Attendance;
                $data["atten"] = $model->where('id',$entry_id)->where("deleted_at IS NULL")->first();
                if($data["atten"]) {
                    $model = new Salon;
                    $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                    $model = new User;
                    $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();

                    $model = new Payment_mode;
                    $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();
                    return view('attendance/add_edit',$data);
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
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['updated_by'] = $userdata["id"];
            $post['updated_at'] = date("Y-m-d H:i:s");
            $model = new Attendance;
            if($model->update($entry_id,$post)) {
                $session->setFlashData('success_message',"Attendance edited successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
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

    public function export()
    {
        $post = $this->request->getVar();
        preview($post);
    }

    public function salon_entry($salon_id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('salon')) {
                $model = new Salon;
                $data["salon"] = $model->where("id",$salon_id)->where("is_active",1)->where(default_where())->first();
                if($data["salon"]) {
                    $data["load_ajax_url"] = base_url("load-entries");

                    $model = new User;
                    $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();

                    $model = new Payment_mode;
                    $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                    return view('entry/salon_wise_entry',$data);
                } else {
                    $session->setFlashData("error-message","Salon not found.");
                    return redirect("salons");    
                }
            } else {
                $session->setFlashData("error","You don't have permission to access entry pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function salon_export_entry($salon_id)
    {
        $model = new Payment_mode;
        $modes = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

        $spreadsheet = new Spreadsheet;

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue("A1","No");
        $sheet->setCellValue("B1","Date");
        if($modes) {
            $ascii = "67";
            foreach($modes as $mode) {
                $sheet->setCellValue(chr($ascii)."1",$mode['name']);
                $ascii;
            }
        }
        $sheet->setCellValue(chr($ascii)."1","TOTAL");

        $dates = [];
        $sdate = strtotime(date("Y-m-01"));
        $edate = strtotime(date("Y-m-t"));
        $row = 2;
        while($sdate <= $edate) {
            $sheet->setCellValue("A",$row,$row-1);
            $sheet->setCellValue("B",$row,date("d M, Y",$sdate));
            if($modes) {
                $db = db_connect();
                $model = $db->table("salon_entries");

                $mode_wise_total = 0;
                $ascii = "67";
                foreach($modes as $mode) {
                    $total = 0;
                    $profit = $model->selectSum("amount")->where("mode_id",$mode['id'])->where("date",date("Y-m-d",$sdate))->get()->getRowArray();
                    if($profit & isset($profit["amount"]) && !is_null($profit["amount"])) {
                        $total = $profit["amount"];
                        $mode_wise_total = $mode_wise_total + $profit["amount"];
                    }
                    $sheet->setCellValue(chr($ascii).$row,$total);
                    $ascii++;
                }
                $sheet->setCellValue(chr($ascii).$row,$mode_wise_total);
            }
            $row++;
            $sdate = strtotime("+1 day",$sdate);

        }
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment; filename="salon.xlsx"');
        header("Cache-Control: max-age=0");

        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
        exit;
    }

    public function today_checkin()
    {
        $session = session();
        $userdata = $session->get("userdata");
        
        $insert_data = array(
            "salon_id" => $userdata["salon_id"],
            "staff_id" => $userdata["id"],
            "date" => date("Y-m-d"),
            "in_time" => date("H:i:s"),
            "note" => "",
            "rate" => $userdata["rate"],
            "created_by" => $userdata["id"],
            "updated_by" => $userdata["id"],
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        );
        $model = new Attendance;
        $model->insert($insert_data);

        $session->setFlashData("success_message","Checked In successfully.");
        return redirect("dashboard");
    }

    public function today_checkout()
    {
        $session = session();
        $userdata = $session->get("userdata");
        
        $model = new Attendance;
        $model->set("out_time",date("H:i:s"))->where(array("staff_id" => $userdata["id"],"date" => date("Y-m-d")))->update();

        $session->setFlashData("success_message","Checked out successfully.");
        return redirect("dashboard");
    }

    public function daily_checkout()
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');
            $post = $this->request->getVar();

            $update_data["out_time"]    = $post["checkout_time"].":00";
            $update_data["hours_diff"]  = hours_diff($post["checkin_time"],$post["checkout_time"].":00");
            $update_data['updated_by']  = $userdata["id"];
            $update_data['updated_at']  = date("Y-m-d H:i:s");
            $model = new Attendance;
            if($model->update($post["atten_id"],$update_data)) {
                $session->setFlashData('success_message',"Attendance updated successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function staff_attendances()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('staff_attendance')) {
                $data["load_ajax_url"] = base_url("load-staff-attendances");
                $data["current_salon_id"] = $session->get("userdata")["salon_id"];

                $model = new Salon;
                $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                $model = new User;
                $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();

                return view('attendance/staff_attendance_list',$data);
            } else {
                $session->setFlashData("error","You don't have permission to access entry pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function load_staff_attendance()
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
        $query = $model->table("salon_checkins sc");
        $query = $query->join("salons s","s.id=sc.salon_id");
        $query = $query->join("salon_users su","su.id=sc.staff_id");
        $query = $query->join("salon_users su1","su1.id=sc.created_by");
        $query = $query->select("sc.id,sc.date,sc.staff_id,sc.in_time,sc.out_time,sc.hours_diff,sc.note,sc.rate,sc.created_at,s.name AS salon,su.fname,su.lname,su1.fname");
        $query = $query->where("sc.deleted_at IS NULL");
        if(isset($post["salon_id"]) && $post["salon_id"] != "") {
            $query = $query->where("s.id",$post["salon_id"]);
        }
        if(isset($post["staff_id"]) && $post["staff_id"] != "") {
            $query = $query->where("su.id",$post["staff_id"]);
        }
        if(isset($post["datetime"]) && $post["datetime"] != "") {
            $query = $query->like("sc.date",$post["datetime"]);
        }
        if(isset($post["date"]) && $post["date"] != "") {
            $query = $query->where("sc.date",$post["date"]);
        }
        $totalRecords = $query->countAllResults(false);
        $query = $query->orderBy($orderBy, $orderDir)->limit($length, $start);
        $cities = $query->get()->getResultArray();
        foreach ($cities as $key => $val) {
            $_editUrl = base_url("attendances/" . $val["id"] . "/edit");
            $trashUrl = base_url("attendances/" . $val["id"]);

            $buttons = "";
            if(is_null($val["out_time"])) {
                $buttons .= '<a href="javascript:;" class="btn btn-sm btn-warning" onclick=checkout('.$val['id'].',"'.$val['in_time'].'","'.$val['date'].'")>Check out</a>&nbsp;';
                $out_time = "-";
            } else {
                $out_time = date("h:i A",strtotime($val['out_time']));
            }
            if($val['staff_id'] == $current_staff_id) {
                // $buttons .= '<a href="' . $_editUrl . '"><i class="bx bx-edit icon-sm"></i></a>&nbsp;';
                // $buttons .= '<a href="javascript:;" onclick=remove_row("' . $trashUrl . '")><i class="icon-base bx bx-trash icon-sm"></i></a>';
            }
            // if($userdata['role'] == 1) {
            //     $buttons .= '<a href="' . $_editUrl . '"><i class="bx bx-edit icon-sm"></i></a>&nbsp;';
            //     $buttons .= '<a href="javascript:;" onclick=remove_row("' . $trashUrl . '")><i class="icon-base bx bx-trash icon-sm"></i></a>';
            // }
            if($buttons == "") {
                $buttons = "-"; 
            }
            $hours = is_null($val['hours_diff']) ? "-" : $val['hours_diff']." hours"; 
            $result['data'][$key] = [
                "<small>".($key + 1)."</small>",
                "<small>".$val['salon']."</small>",
                "<small>".$val['fname'].' '.$val['lname']."</small>",
                "<small>".date("d M, Y",strtotime($val['date']))."</small>",
                "<small>".date("h:i A",strtotime($val['in_time']))."</small>",
                 "<small>".$out_time."</small>",
                "<small>".$hours."</small>",
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
}
