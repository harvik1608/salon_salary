<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\Salon;
use App\Models\Payment_mode;
use App\Models\Cover_entry;
use App\Models\User;
use App\Models\Attendance;

require_once(APPPATH . 'Views/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Salons extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('salon')) {
                $db = db_connect();
                $salons = $db->table("salons s");
                $salons = $salons->join("salon_users u","u.id=s.created_by");
                $salons = $salons->select("s.id,s.name,s.currency,s.stime,s.etime,CONCAT(u.fname,' ',u.lname) as added_by,s.created_at,s.is_active");
                $salons = $salons->where("s.deleted_at IS NULL");
                $salons = $salons->orderBy("s.id","desc");
                $data["salons"] = $salons->get()->getResultArray();
                return view('salon/list',$data);
            } else {
                $session->setFlashData("error","You don't have permission to access entry pages.");
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
            if(check_permission('salon')) {
                $data["salon"] = array();
                return view('salon/add_edit',$data);
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
            $post['created_by'] = $userdata["id"];
            $post['updated_by'] = 0;
            $post['created_at'] = date("Y-m-d H:i:s");
            $post['updated_at'] = "0000-00-00 00:00:00";
            $model = new Salon;
            if($model->insert($post)) {
                $session->setFlashData('success_message',"Salon added successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error','message' => 'Failed to insert data.',]);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function edit($salon_id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('salon')) {
                $model = new Salon;
                $data["salon"] = $model->where('id',$salon_id)->where(default_where())->first();
                if($data["salon"]) {
                    return view('salon/add_edit',$data);
                } else {
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

    public function update($salon_id)
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $post = $this->request->getVar();
            $post['updated_by'] = $userdata["id"];
            $post['updated_at'] = date("Y-m-d H:i:s");
            $model = new Salon;
            if($model->update($salon_id,$post)) {
                $session->setFlashData('success_message',"Salon edited successfully.");
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
            $model = new Salon;
            if($model->update($salon_id,$post)) {
                $session->setFlashData('success_message',"Salon deleted successfully.");
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'success']);
            }
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function salon_mode_entry($id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('salon')) {
                $model = new Payment_mode;
                $data["modes"] = $model->select("id,name")->where(default_where())->get()->getResultArray();

                $model = new Salon;
                $data["salon"] = $model->select("id,name")->where(default_where())->where("id",$id)->first();

                $data["load_ajax_url"] = base_url("load-salon-mode-entry/".$id);
                $data["salon_id"] = $id;
                return view('salon/mode_wise_entry',$data);
            } else {
                $session->setFlashData("error","You don't have permission to access salon pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function load_salon_mode_entry($id)
    {
        $session = session();
        $userdata = $session->get('userdata');
        $post = $this->request->getVar();
        $result = array("data" => array());

        $model = new Salon;
        $salon = $model->select("currency")->where(default_where())->where("id",$id)->first();

        $model = new Payment_mode;
        $modes = $model->select("id,name")->where(default_where())->get()->getResultArray();

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
        $query = $query->join("salon_cover_entries sce","sc.id=sce.cover_id");
        $query = $query->select("sc.id,sc.date,sc.tip");
        $query = $query->where("sc.deleted_at IS NULL");
        $query = $query->where("sc.salon_id",$id);
        $query = $query->groupBy("sc.date");
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
            $query = $query->where("sc.tip",$post["tip"]);
        }
        $totalRecords = $query->countAllResults(false);
        $query = $query->orderBy($orderBy, $orderDir)->limit($length, $start);
        $entries = $query->get()->getResultArray();
        $model = new Cover_entry;
        foreach ($entries as $key => $val) {
            $mode_wise_total = 0;
            $result['data'][$key] = [
                "<small>".date("d M, Y",strtotime($val['date']))."</small>"
            ];
            if($modes) {
                foreach($modes as $mod_key => $mod_val) {                    
                    $amt = $model->selectSum("amount")->where("cover_id",$val["id"])->where("payment_mode_id",$mod_val['id'])->get()->getRowArray();
                    if($amt && $amt['amount'] != "") {
                        $mode_wise_total = $mode_wise_total + $amt['amount'];
                        array_push($result['data'][$key],"<small>".$salon['currency'].' '.$amt['amount']."</small>");
                    }
                }
            }
            array_push($result['data'][$key],"<small>".$salon['currency'].' '.$mode_wise_total."</small>");
            array_push($result['data'][$key],"<small>".$salon['currency'].' '.$val['tip']."</small>");
        } 
        // preview($result['data']);       

        // Add response metadata
        $result["draw"] = intval($draw);
        $result["recordsTotal"] = $totalRecords;
        $result["recordsFiltered"] = $totalRecords;

        // Output JSON
        echo json_encode($result);
        exit;
    }

    public function salon_monthly_report($id)
    {
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

    public function summary_view()
    {
        $post = $this->request->getVar();
        $datetime = $post["month_year"]; 
        $fromdate = isset($post["from_date"]) == "" ? date('Y-m-01',strtotime($datetime)) : $post["from_date"];
        $__todate = isset($post["to_date"]) == "" ? date('Y-m-t',strtotime($datetime)) : $post["to_date"];

        $model = new Payment_mode;
        $data["modes"] = $model->select("id,name")->where(default_where())->get()->getResultArray();

        $model = new Salon;
        $data['salon'] = $model->select("currency")->where(default_where())->where("id",$post["salon_id"])->first();
        $data['all_salons'] = $model->select("id,name")->where(default_where())->get()->getResultArray();

        $model = db_connect();
        $query = $model->table("salon_covers sc");
        $query = $query->join("salon_cover_entries sce","sc.id=sce.cover_id");
        $query = $query->select("sc.id,sc.date,sc.tip");
        $query = $query->where("sc.deleted_at IS NULL");
        $query = $query->where("sc.salon_id",$post["salon_id"]);
        $query = $query->where("sc.date >=",$fromdate);
        $query = $query->where("sc.date <=",$__todate);
        $query = $query->groupBy("sc.date");
        $data["entries"] = $query->get()->getResultArray();

        $model = new User;
        $data["staffs"] = $model->select("id,fname,lname,wage,rate")->where(default_where())->where("(last_working_date is null OR last_working_date >= '".$__todate."')")->where("salon_id",$post["salon_id"])->get()->getResultArray();
        if($data["staffs"]) {
            $model = new Attendance;
            foreach ($data["staffs"] as $key => $val) {
                $row = $model->selectSum("hours_diff")
                ->selectSum("tip")
                ->where("staff_id",$val["id"])
                ->where("date >=",$fromdate)
                ->where("date <=",$__todate)
                ->where("deleted_at IS NULL")
                ->get()->getRowArray();
                if($row && $row["hours_diff"] != "") {
                    $data["staffs"][$key]["hour_per_day"] = $row["hours_diff"];
                } 
                if($row && $row["tip"] != "") {
                    $data["staffs"][$key]["tip"] = $row["tip"];
                }
                $wdays = $model->where("staff_id",$val["id"])->where("date >=",$fromdate)->where("date <=",$__todate)->where("in_time !=","00:00:00")->where("deleted_at IS NULL")->get()->getNumRows();  
                $data["staffs"][$key]["working_days"] = $wdays;
            }
            $model = db_connect();
            foreach ($data["staffs"] as $key => $val) {
                $query = $model->table("salon_checkins sc");
                $query = $query->join("salons s","s.id=sc.salon_id","left");
                $query = $query->select("sc.id,sc.date,sc.in_time,sc.out_time,sc.break,sc.hours_diff,sc.is_from_other_salon,sc.rate,sc.tip,s.name AS salon,sc.salon_id");
                $query = $query->where(array("sc.staff_id" => $val["id"],"sc.date >=" => $fromdate,"sc.date <=" => $__todate));
                $query = $query->where("sc.deleted_at IS NULL");
                $query = $query->orderBy("sc.date","asc");
                $checkins = $query->get()->getResultArray();
                if($checkins) {
                    $data["staffs"][$key]["checkins"] = $checkins;
                }

                $builder = $model->table('salon_checkins');
                $builder->select('salons.name AS salon, SUM(salon_checkins.hours_diff) AS hours');
                $builder->join('salons', 'salons.id = salon_checkins.salon_id', 'left');
                $builder->where(array("salon_checkins.staff_id" => $val["id"],"salon_checkins.date >=" => $fromdate,"salon_checkins.date <=" => $__todate));
                $builder->where("salon_checkins.deleted_at IS NULL");
                $builder->groupBy('salon_checkins.salon_id');
                $query = $builder->get();
                $result = $query->getResult();
                if($result) {
                    $data["staffs"][$key]["salons"] = $result;
                }
                
                $rate_query = $model->table("salon_staff_rates")->select("rate,wage")->where("staff_id",$val["id"])->where("start_date <=",$fromdate)->where("deleted_at IS NULL")->orderBy("start_date","DESC")->get()->getRowArray();
                if($rate_query) {
                    $data["staffs"][$key]["rate"] = $rate_query['rate'];
                    $data["staffs"][$key]["wage"] = $rate_query['wage'];
                } else {
                    $data["staffs"][$key]["rate"] = 0;
                    $data["staffs"][$key]["wage"] = 0;
                }
            }
        }
        $data["sdate"] = $fromdate;
        $data["edate"] = $__todate;
        $data["salon_id"] = $post["salon_id"];
        return view('salon/summary_view',$data);
    }

    public function remove_staff_attendance()
    {
        $post = $this->request->getVar();
        $model = new Attendance;
        $model->update($post["id"],['deleted_at' => date('Y-m-d H:i:s')]);        
    }

    public function save_staff_attendance()
    {
        $post = $this->request->getVar();
        $post["is_from_other_salon"] = 0;

        $model = new Attendance;
        $atten = $model->select("staff_id")->where("id",$post["id"])->first();
        if($atten) {
            $model = new User;
            $staff = $model->select("salon_id")->where("id",$atten["staff_id"])->first();
            if($staff) {
                if($post["salon_id"] != $staff["salon_id"]) {
                    $post["is_from_other_salon"] = 1;
                }
            }
        }
        $post['updated_at'] = date("Y-m-d H:i:s");
        $model = new Attendance;
        $model->update($post["id"],$post);
        echo json_encode(array("status" => 200));    
    }

    public function export_salon_report($salon_id)
    {
        $post = $this->request->getVar();
        $datetime = $post["datetime"]; 
        $fdate = date('Y-m-01',strtotime($datetime));
        $tdate = date('Y-m-t',strtotime($datetime));
        $currency = "";

        $model = new Salon;
        $salon = $model->select("name,currency")->where(default_where())->where("id",$salon_id)->first();
        $salon_name = "";
        if($salon) {
            $salon_name = str_replace(" ", "-", $salon["name"]);
            $currency = $salon["currency"];
        }
        $filename = $salon_name."-Report";

        $other_columns = [];
        $model = new Payment_mode;
        $payment_modes = $model->select("id,name")->where(default_where())->get()->getResultArray();
        if($payment_modes) {
            foreach($payment_modes as $payment_mode) {
                $other_columns[] = $payment_mode["name"];
            }
        }
        $columns = ["Date","Day",$other_columns,"Total","Tip"];
        $columns = array_merge([$columns[0], $columns[1]], $columns[2],[$columns[3],$columns[4]]);
        // preview($columns);
        $data = implode(",", $columns)."\n";
        $exportData = array();

        $model = db_connect();
        $query = $model->table("salon_covers sc");
        $query = $query->join("salon_cover_entries sce","sc.id=sce.cover_id");
        $query = $query->select("sc.id,sc.date,sc.tip");
        $query = $query->where("sc.deleted_at IS NULL");
        $query = $query->where("sc.salon_id",$salon_id);
        $query = $query->where("sc.date >=",$fdate);
        $query = $query->where("sc.date <=",$tdate);
        $query = $query->groupBy("sc.date");
        $entries = $query->get()->getResultArray();
        $grand_total = 0;
        $grand_tip = 0;
        $mode_wise_total = 0;
        if($entries) {
            $i = 0;
            foreach($entries as $entry) {
                $row_wise_date_total = 0;
                $exportData[$i]['date'] = date('d M, Y',strtotime($entry['date']));
                $exportData[$i]['day'] = date('l',strtotime($entry['date']));
                if($payment_modes) {
                    foreach($payment_modes as $mod_key => $mod_val) {  
                        $column_name = strtolower($mod_val["name"]);

                        $query = $model->table("salon_cover_entries ce");
                        $query = $query->join("salon_covers c","c.id=ce.cover_id");
                        $query = $query->selectSum("ce.amount");
                        $query = $query->where("ce.cover_id",$entry["id"]);
                        $query = $query->where("ce.payment_mode_id",$mod_val['id']);
                        $query = $query->where("ce.deleted_at IS NULL");
                        $amt = $query->get()->getRowArray();
                        if($amt && $amt['amount'] != "") {
                            $mode_wise_total = $mode_wise_total + $amt['amount'];
                            $row_wise_date_total = $row_wise_date_total + $amt['amount'];
                            $exportData[$i][$column_name] = number_format($amt['amount'],2);
                        } else {
                            $exportData[$i][$column_name] = "0";
                        }
                    }
                }
                $exportData[$i]["total"] = number_format($row_wise_date_total,2);
                $exportData[$i]["tip"] = number_format($entry['tip']);
                $data .= '"'.implode('","',$exportData[$i]).'"'."\n";
                $i++;

                $grand_total = $grand_total + $row_wise_date_total;
                $grand_tip = $grand_tip + $entry['tip'];
            }
        }
        $exportData[$i]["date"] = "";
        $exportData[$i]["day"] = "";
        if($payment_modes) {
            foreach($payment_modes as $mod_key => $mod_val) {  
                $column_name = strtolower($mod_val["name"]);

                $query = $model->table("salon_cover_entries ce");
                $query = $query->join("salon_covers c","c.id=ce.cover_id");
                $query = $query->selectSum("ce.amount");
                $query = $query->where("c.date >=",$fdate);
                $query = $query->where("c.date <=",$tdate);
                $query = $query->where("ce.payment_mode_id",$mod_val['id']);
                $query = $query->where("c.salon_id",$salon_id);
                $amt = $query->get()->getRowArray();
                if($amt && $amt['amount'] != "") {
                    $exportData[$i][$column_name] = number_format($amt['amount'],2);
                } else {
                    $exportData[$i][$column_name] = "0";
                }
            }
        }
        $exportData[$i]["total"] = number_format($grand_total,2);
        $exportData[$i]["tip"] = number_format($grand_tip);
        $data .= '"'.implode('","',$exportData[$i]).'"'."\n\n";

        $columns = ["Staff","Wage","Rate","H/D","Working Days","Total","Tip","Grand Total"];
        $data .= implode(",", $columns)."\n";

        $model = new User;
        $staffs = $model->select("id,fname,lname,wage,rate")->where(default_where())->where("salon_id",$salon_id)->get()->getResultArray();
        if($staffs) {
            $model = new Attendance;
            $db = db_connect();
            foreach ($staffs as $key => $val) {
                $row = $model->selectSum("hours_diff")
                ->selectSum("tip")
                ->where("staff_id",$val["id"])
                ->where("date >=",$fdate)
                ->where("date <=",$tdate)
                ->where("deleted_at IS NULL")
                ->get()->getRowArray();
                if($row && $row["hours_diff"] != "") {
                    $staffs[$key]["hour_per_day"] = $row["hours_diff"];
                } 
                if($row && $row["tip"] != "") {
                    $staffs[$key]["tip"] = $row["tip"];
                }
                $wdays = $model->where("staff_id",$val["id"])->where("date >=",$fdate)->where("date <=",$tdate)->where("in_time !=","00:00:00")->where("deleted_at IS NULL")->get()->getNumRows();  
                $staffs[$key]["working_days"] = $wdays;
                
                $rate_query = $db->table("salon_staff_rates")->select("rate,wage")->where("staff_id",$val["id"])->where("start_date <=",$fdate)->where("deleted_at IS NULL")->orderBy("start_date","DESC")->get()->getRowArray();
                if($rate_query) {
                    $staffs[$key]["rate"] = $rate_query['rate'];
                    $staffs[$key]["wage"] = $rate_query['wage'];
                } else {
                    $staffs[$key]["rate"] = 0;
                    $staffs[$key]["wage"] = 0;
                }
            }
            $model = db_connect();
            foreach ($staffs as $key => $val) {
                $query = $model->table("salon_checkins sc");
                $query = $query->join("salons s","s.id=sc.salon_id","left");
                $query = $query->select("sc.id,sc.date,sc.in_time,sc.out_time,sc.break,sc.hours_diff,sc.is_from_other_salon,sc.rate,sc.tip,s.name AS salon");
                $query = $query->where(array("sc.staff_id" => $val["id"],"sc.date >=" => $fdate,"sc.date <=" => $tdate));
                $query = $query->where("sc.deleted_at IS NULL");
                $query = $query->orderBy("sc.date","asc");
                $checkins = $query->get()->getResultArray();
                if($checkins) {
                    $staffs[$key]["checkins"] = $checkins;
                } else {
                    $staffs[$key]["checkins"] = [];
                }
            }

            $total_rate = $total_hour_per_day = $row_total = $total_tip = $row_grand_total = 0;
            foreach($staffs as $staff) {
                $i++;
                $hour_per_day = 0;
                $working_days = 0;
                $total = $staff["rate"];
                $tip = 0;
                $grand_total = 0;
                if(isset($staff['hour_per_day'])) {
                    $hour_per_day = $staff['hour_per_day'];
                }
                if($staff['wage'] == 2) {
                    $total = $staff['rate']*$hour_per_day;
                }
                if(isset($staff["working_days"])) {
                    $working_days = $staff["working_days"];
                }
                if(isset($staff["tip"])) {
                    $tip = $staff["tip"];
                }
                $grand_total = $total + $tip;
                $exportData[$i]["staff"] = ucwords(strtolower($staff['fname'].' '.$staff['lname']));
                $exportData[$i]["wage"] = $staff['wage'] == 1 ? "Monthly" : "Hourly";
                $exportData[$i]["rate"] = $staff['rate'];
                $exportData[$i]["h/d"] = number_format($hour_per_day,2);
                $exportData[$i]["working_days"] = $working_days;
                $exportData[$i]["total"] = number_format($total,2);
                $exportData[$i]["tip"] = number_format($tip,2);
                $exportData[$i]["grand_total"] = number_format($grand_total,2);
                $data .= '"'.implode('","',$exportData[$i]).'"'."\n";
                $row_total = $row_total + $total;
                $total_tip = $total_tip + $tip;
                $row_grand_total = $row_grand_total + $grand_total;
            }
        }
        $exportData[$i]["staff"] = "";
        $exportData[$i]["wage"] = "";
        $exportData[$i]["rate"] = "";
        $exportData[$i]["h/d"] = "";
        $exportData[$i]["working_days"] = "";
        $exportData[$i]["total"] = number_format($row_total,2);
        $exportData[$i]["tip"] = number_format($total_tip,2);
        $exportData[$i]["grand_total"] = number_format($row_grand_total,2);
        $data .= '"'.implode('","',$exportData[$i]).'"'."\n\n";

        if($staffs) {
            foreach($staffs as $staff) {
                if(isset($staff["checkins"]) && !empty($staff["checkins"])) {
                    $staff_name = [strtoupper($staff['fname'].' '.$staff['lname'])];
                    $data .= implode(",", $staff_name)."\n";
                    $columns = ["Date","Day","Start Time","End Time","Break","Hours","Tip","Salon"];
                    $data .= implode(",", $columns)."\n";
                    $row_wise_tip = 0;
                    $row_wise_hours = 0;
                    if($staff["checkins"]) {
                        foreach($staff["checkins"] as $checkin) {
                            $row_wise_tip = $row_wise_tip + $checkin['tip'];
                            if(is_numeric($checkin['hours_diff'])) {
                                $row_wise_hours = $row_wise_hours + $checkin['hours_diff'];
                            }
                            if($checkin['in_time'] != "00:00:00") {
                                $i++;
                                $exportData[$i]["date"] = date('d M, Y',strtotime($checkin['date']));
                                $exportData[$i]["day"] = date('l',strtotime($checkin['date']));
                                $exportData[$i]["start_time"] = $checkin['in_time'] == "00:00:00" ? "" : date('H:i:s',strtotime($checkin['in_time']));
                                $exportData[$i]["end_time"] = $checkin['out_time'] == "00:00:00" ? "" : date('H:i:s',strtotime($checkin['out_time']));
                                $exportData[$i]["break"] = $checkin['break'] == 0 ? "" : $checkin['break'];
                                $exportData[$i]["hours"] = calculateWorkingHours($checkin['in_time'],$checkin['out_time'],$checkin['break']);
                                $exportData[$i]["tip"] = $checkin['tip'];
                                $exportData[$i]["salon"] = strtoupper($checkin['salon']);
                                $data .= '"'.implode('","',$exportData[$i]).'"'."\n";
                            }
                        }
                        $total_salary = $checkin['rate'];
                        if($checkin['rate'] < 100) {
                            $total_salary = ($row_wise_hours+$row_wise_tip)*$checkin['rate'];
                        }
                        $exportData[$i]["date"] = "";
                        $exportData[$i]["day"] = "";
                        $exportData[$i]["start_time"] = "";
                        $exportData[$i]["end_time"] = "";
                        $exportData[$i]["break"] = "";
                        $exportData[$i]["hours"] = $row_wise_hours;
                        $exportData[$i]["tip"] = number_format($row_wise_tip,2);
                        $exportData[$i]["salon"] = number_format($total_salary,2);
                        $data .= '"'.implode('","',$exportData[$i]).'"'."\n\n";
                    }
                }
            }
        }

        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=".$filename.".csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $data;
        exit;
    }
}
