<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\Salon;
use App\Models\User;
use App\Models\Payment_mode;
use App\Models\Entry;
use App\Models\Cover;
use App\Models\Cover_entry;
use App\Models\Attendance;

require_once(APPPATH . 'Views/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Entries extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('entry')) {
                $data["load_ajax_url"] = base_url("load-entries");

                $model = new Salon;
                $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                $model = new User;
                $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->orderBy("fname","asc")->get()->getResultArray();

                $model = new Payment_mode;
                $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                return view('entry/list',$data);
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
                "<small>".ucwords(strtolower($val['salon']))."</small>",
                "<small>".date("d M, Y",strtotime($val['date']))."</small>",
                "<small>".date("l",strtotime($val['date']))."</small>",
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
            if(check_permission('entry')) {
                $data["entry"] = array();
                $data['current_staff_id'] = $session->get('userdata')['id'];
                if($session->get("userdata")['role'] == 3) {
                    $model = new Salon;
                    $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                    $model = new User;
                    $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();

                    $model = new Payment_mode;
                    $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->orderBy("sequence","ASC")->get()->getResultArray();

                    return view('entry/add_edit',$data);
                } else {
                    $data["default_date"] = date("Y-m-d");
                    $data["default_salon_id"] = 0;
                    if(isset($_GET['date']) && $_GET['date'] != "") {
                        $data["default_date"] = $_GET['date'];
                    }
                    if(isset($_GET['salon_id']) && $_GET['salon_id'] != "") {
                        $data["default_salon_id"] = $_GET['salon_id'];
                    }   
                    $model = new Salon;
                    $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                    $model = new Payment_mode;
                    $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->orderBy("sequence","ASC")->get()->getResultArray();

                    return view('entry/admin_add_edit',$data);
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
                $model = new Cover;
                $cover = $model->select("id")->where('salon_id',$post['global_salon_id'])->where('date',$post['date'])->where('deleted_at IS NULL')->first();
                if($cover) {
                    $cover_data["amount"] = $post["total_amount"];
                    $cover_data["tip"] = $post["entry_tip"];
                    $cover_data["note"] = $post["note"];
                    $cover_data["updated_by"] = $userdata["id"];
                    $cover_data["updated_at"] = date("Y-m-d H:i:s");
                    $model->update($cover["id"],$cover_data);
                    $cover_id = $cover["id"];

                    $model = new Cover_entry;
                    $model->where("cover_id",$cover_id)->delete();
                } else {
                    $cover_data["salon_id"] = $post["global_salon_id"];
                    $cover_data["amount"] = $post["total_amount"];
                    $cover_data["tip"] = $post["entry_tip"];
                    $cover_data["date"] = $post["date"];
                    $cover_data["note"] = $post["note"];
                    $cover_data["created_by"] = $userdata["id"];
                    $cover_data["updated_by"] = 0;
                    $cover_data["created_at"] = date("Y-m-d H:i:s");
                    $cover_data["updated_at"] = "0000-00-00 00:00:00";
                    $model->insert($cover_data);
                    $cover_id = $model->getInsertID();
                }
                $insert_data = [];
                for($i = 0; $i < count($post["price"]); $i ++) {
                    if($post["price"][$i] != "") {
                        $insert_data[] = array(
                            "cover_id" => $cover_id,
                            "payment_mode_id" => $post["mode_id"][$i],
                            "amount" => $post["price"][$i]
                        );
                    }
                }
                $model = new Cover_entry;
                $model->insertBatch($insert_data);

                // Staff Attendance
                $model = new Attendance;
                // if(isset($post["old_staff_id"])) {
                //     for($i = 0; $i < count($post["old_staff_id"]); $i ++) {
                //         $model->where("date",$post["date"])->where("staff_id",$post["old_staff_id"][$i])->delete();
                //     }
                // }
                $atten_data = array();
                for($i = 0; $i < count($post["staff_id"]); $i ++) {
                    if($post["in_time"][$i] != "" && $post["out_time"][$i] != "") {
                        $model->where("date",$post["date"])->where("staff_id",$post["staff_id"][$i])->where('salon_id',$post["salon_id"][$i])->delete();

                        $is_from_other_salon = 0;
                        if($post["salon_id"][$i] != $post["old_salon_id"][$i]) {
                            $is_from_other_salon = 1;
                        }

                        $atten_data[] = array(
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
                }
                if($model->insertBatch($atten_data)) {
                    // $session->setFlashData('success_message',"Entry & Attendance added successfully.");
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
                $data["entry"] = $model->where('id',$entry_id)->where("deleted_at IS NULL")->first();
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

    public function delete($cover_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['deleted_at'] = date("Y-m-d H:i:s");
            $model = new Cover;
            if($model->update($cover_id,$post)) {
                $model = new Cover_entry;
                $model->set('deleted_at',date('Y-m-d H:i:s'))->where("cover_id",$cover_id)->update();

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
        // require_once APPPATH . 'Libraries/Psr/SimpleCache/src/CacheInterface.php';
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $post = $this->request->getVar();
        
        $spreadsheet = new Spreadsheet();
        
        // Set the active sheet to the first sheet
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add data to the spreadsheet
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Email');
        
        // Example data (this can be replaced with data from your database)
        $sheet->setCellValue('A2', '1');
        $sheet->setCellValue('B2', 'John Doe');
        $sheet->setCellValue('C2', 'john@example.com');
        
        $sheet->setCellValue('A3', '2');
        $sheet->setCellValue('B3', 'Jane Smith');
        $sheet->setCellValue('C3', 'jane@example.com');
        
        // Write to the file (You can customize the filename as needed)
        $writer = new Xlsx($spreadsheet);

        // Set the file to be downloaded as an Excel file
        $filename = 'example_export.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Write the file to output
        $writer->save('php://output');
        
        exit;
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
                    $data["modes"] = $model->select("id,name")->where("is_active",1)->orderBy("sequence","ASC")->where(default_where())->get()->getResultArray();

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

    public function get_ajax_form_entry()
    {
        $post = $this->request->getVar();

        $model = new Cover;
        $data["cover"] = $model->select("id,amount,note,tip")->where('salon_id',$post['salon_id'])->where('date',$post['date'])->where('deleted_at IS NULL')->first();

        $model = new Salon;
        $data["salons"] = $model->select("id,name")->where(default_where())->get()->getResultArray();

        $model = new User;
        $data["other_staffs"] = $model->select("id,fname,lname,salon_id,rate")->where(array("role" => 3,"salon_id !=" => $post['salon_id']))->where(default_where())->where("(last_working_date IS NULL OR last_working_date >= '".$post['date']."')")->get()->getResultArray();
        $data["staffs"] = $model->select("id,fname,lname,salon_id,rate")->where(array("role" => 3,"salon_id" => $post['salon_id']))->where(default_where())->where("(last_working_date IS NULL OR last_working_date >= '".$post['date']."')")->get()->getResultArray();
        if($data["staffs"]) {
            $model = new Attendance;
            $db = db_connect();
            foreach($data["staffs"] as $key => $val) {
                // $checkin = $model->where('date',$post['date'])->where("staff_id",$val["id"])->where('deleted_at IS NULL')->first();
                $checkin = $model->where(['date' => $post['date'],'staff_id' => $val["id"],'salon_id' => $val['salon_id']])->where('deleted_at IS NULL')->first();
                if($checkin) {
                    $data["staffs"][$key]["entry_id"] = $checkin["id"];
                    $data["staffs"][$key]["in_time"] = $checkin["in_time"];
                    $data["staffs"][$key]["out_time"] = $checkin["out_time"];
                    $data["staffs"][$key]["break"] = $checkin["break"];
                    $data["staffs"][$key]["staff_hours"] = $checkin["hours_diff"];
                    $data["staffs"][$key]["staff_tip"] = $checkin["tip"];
                }
                
                $rate_query = $db->table("salon_staff_rates")->select("rate,wage")->where("staff_id",$val["id"])->where("start_date <=",$post['date'])->where("deleted_at IS NULL")->orderBy("start_date","DESC")->get()->getRowArray();
                if($rate_query) {
                    $data["staffs"][$key]["rate"] = $rate_query['rate'];
                    $data["staffs"][$key]["wage"] = $rate_query['wage'];
                } else {
                    $data["staffs"][$key]["rate"] = 0;
                    $data["staffs"][$key]["wage"] = 0;
                }
            }
        }

        $model = new Payment_mode;
        $data["modes"] = $model->select("id,name,is_deduct")->where("is_active",1)->orderBy("sequence","ASC")->where(default_where())->get()->getResultArray();
        if($data["modes"] && $data["cover"]) {
            $model = new Cover_entry;
            foreach($data["modes"] as $key => $val) {
                $mode = $model->select("amount")->where("cover_id",$data["cover"]['id'])->where("payment_mode_id",$val["id"])->first();
                if($mode) {
                    $data["modes"][$key]['price'] = $mode['amount'];
                }
            }
        }
        $model = db_connect();
        $query = $model->table("salon_checkins sc");
        $query = $query->join("salon_users su","su.id=sc.staff_id");
        $query = $query->select("sc.*,su.id AS staff_id,su.fname,su.lname");
        $query = $query->where("sc.date",$post['date']);
        $query = $query->where("sc.salon_id",$post['salon_id']); // extra put condition on 13/03/2025
        $query = $query->where("sc.in_time !=","00:00:00");
        $query = $query->where("sc.is_from_other_salon",1);
        $query = $query->where("sc.deleted_at IS NULL");
        $data["extra_staffs"] = $query->get()->getResultArray();

        // preview($data["cover"]);

        $data["selected_salon_id"] = $post['salon_id'];
        return view("entry/ajax_form",$data);
    }

    public function get_extra_salon_staff()
    {
        $post = $this->request->getVar();
        $data["salon_id"] = $post["salon_id"];
        $data["no"] = $post["no"];
        
        $model = new Salon;
        $data["salons"] = $model->select("id,name")->where(default_where())->get()->getResultArray();

        $model = new User;
        $data["staffs"] = $model->select("id,fname,lname,rate,salon_id")->where(array("is_active" => 1,"role" => 3,"salon_id !=" => $post["salon_id"]))->where(default_where())->orderBy("fname","asc")->get()->getResultArray();
        return view("entry/get_extra_salon_staff",$data);
    }

    public function remove_daily_entry()
    {
        $model = new Attendance;
        if($model->update($this->request->getVar('entry_id'),["deleted_at" => date("Y-m-d H:i:s")])) {
            echo json_encode(["status" => 200,"message" => "Entry removed successfully."]);
        } else {
            echo json_encode(["status" => 400,"message" => "Oops an error occurred."]);
        }
        exit;
    }

    public function remove_entry()
    {
        $post = $this->request->getVar();
        
        $model = new Attendance;
        $model->where(["salon_id" => $post["salon_id"],"date" => $post["date"]])->delete();

        $model = new Cover;
        $covers = $model->where(["salon_id" => $post["salon_id"],"date" => $post["date"]])->get()->getResultArray();
        if($covers) {
            $model = new Cover_entry;
            foreach($covers as $cover) {
                $model->where("id",$cover["id"])->delete();
            }
        }

        $model = new Cover;
        $model->where(["salon_id" => $post["salon_id"],"date" => $post["date"]])->delete();

        echo json_encode(["success" => true,"message" => "Entry deleted successfully."]);
        exit;
    }
}
