<?php

namespace App\Controllers;

use App\Controllers\CommonController;

use App\Models\Attendance;
use App\Models\Salon;
use App\Models\Entry;
use App\Models\Payment_mode;
use App\Models\Cover;
use App\Models\User;

class Dashboard extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            $userdata = $session->get('userdata');
            if($userdata['role'] == 1 || $userdata['role'] == 2) {
                $data["mode_labels"] = [];
                $data["mode_values"] = [];

                $db = db_connect();
                $sql = "SELECT spm.id, spm.name,SUM(se.amount) AS total FROM  salon_payment_modes spm LEFT JOIN  salon_cover_entries se ON se.payment_mode_id = spm.id WHERE  spm.is_active = 1  AND spm.deleted_at IS NULL GROUP BY spm.id, spm.name";
                $query = $db->query($sql);
                $result = $query->getResult();
                if($result) {
                    foreach($result as $row) {
                        $data["mode_labels"][] = $row->name;
                        $data["mode_values"][] = $row->total;
                    }
                }
                $model = new Salon;
                $data["salons"] = $model->select("id,name,currency")->where("is_active",1)->where("deleted_at IS NULL")->orderBy("id","asc")->get()->getResultArray();
                return view('dashboard',$data);
            } else if($userdata['role'] == 3) {
                $data["is_checkedIn"] = 1;
                $model = new Attendance;
                $atten = $model->where("staff_id",$userdata["id"])->where("date",date("Y-m-d"))->first();
                if($atten) {
                    if(is_null($atten["out_time"])) {
                        $data["is_checkedIn"] = 2;
                    } else {
                        $data["my_hours"] = $atten["hours_diff"];
                        $data["is_checkedIn"] = 3;
                    }
                }
                return view('staff_dashboard',$data);
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function load_income_chart()
    {
        $model = new Salon;
        $salon = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();
        $labels = $data = $expenseData = [];
        if($salon) {
            $model = new Cover;
            foreach($salon as $row) {
                $labels[] = $row['name'];

                $atten = $model->selectSum("amount")->where("salon_id",$row["id"])->like("date",$this->request->getVar('year_month'))->where("deleted_at IS NULL")->get()->getRowArray();
                if($atten) {
                    if(!is_null($atten["amount"]) && $atten["amount"] != "") {
                        $data[] = $atten["amount"];
                        $expenseData = rand(30000,35000);
                    }
                }
            }
        }
        echo json_encode(array("labels" => $labels,"data" => $data,"expenseData" => $expenseData));
        exit;

    }

    public function load_monthly_summary_report()
    {
        $post = $this->request->getVar();
        $datetime = $post["year_month"]; 
        $fromdate = date('Y-m-01',strtotime($datetime));
        $__todate = date('Y-m-t',strtotime($datetime));
        $model = new Salon;
        $data["salons"] = $model->select("id,name,currency")->where("is_active",1)->where(default_where())->get()->getResultArray();
        if($data["salons"]) {
            $model = new Cover;
            foreach($data["salons"] as $key => $val) {
                $income = $model->selectSum("amount")->where("salon_id",$val["id"])->like("date",$this->request->getVar('year_month'))->where("deleted_at IS NULL")->get()->getRowArray();
                if($income) {
                    if(!is_null($income["amount"]) && $income["amount"] != "") {
                        $data["salons"][$key]["income"] = $income["amount"];
                    } else {
                        $data["salons"][$key]["income"] = 0;
                    }
                }
            }
            $model = new User;
            $data["staffs"] = $model->select("id,salon_id,fname,lname")->where("is_active",1)->where('role',3)->where("deleted_at IS NULL")->where("last_working_date IS NULL")->orderBy('fname','asc')->get()->getResultArray();

            if($data["staffs"]) {
                $model = db_connect();

                // find staff's rate & wage
                foreach($data["staffs"] as $key => $staff) {
                    $rate_query = $model->table("salon_staff_rates")->select("rate,wage")->where("staff_id",$staff["id"])->where("start_date <=",$fromdate)->where("deleted_at IS NULL")->orderBy("start_date","DESC")->get()->getRowArray();
                    if($rate_query) {
                        $data["staffs"][$key]["rate"] = $rate_query['rate'];
                        $data["staffs"][$key]["wage"] = $rate_query['wage'];
                    } else {
                        $data["staffs"][$key]["rate"] = 0;
                        $data["staffs"][$key]["wage"] = 0;
                    }
                }

                // calculate staff's total hours
                foreach($data["staffs"] as $key => $staff) {
                    $hoursQuery = $model->table("salon_checkins sc")
                    ->selectSum('sc.hours_diff', 'total_hours')
                    ->where("sc.staff_id", $staff["id"])
                    ->where("sc.date >=", $fromdate)
                    ->where("sc.date <=", $__todate)
                    ->where('sc.deleted_at', null)
                    ->get()
                    ->getRowArray();
                    $data["staffs"][$key]["total_hours"] = $hoursQuery['total_hours'] ?? 0;
                }
                foreach($data["staffs"] as $key => $staff) {
                    $staffSalons = [];
                    foreach($data["salons"] as $salon) {
                        $hoursQuery = $model->table("salon_checkins sc")
                            ->select("COALESCE(SUM(sc.hours_diff),0) as salon_hours")
                            ->where("sc.staff_id", $staff["id"])
                            ->where("sc.salon_id", $salon["id"])
                            ->where("sc.date >=", $fromdate)
                            ->where("sc.date <=", $__todate)
                            ->where("sc.deleted_at IS NULL")
                            ->get()
                            ->getRowArray();
                        $salon_hours = $hoursQuery['salon_hours'] ?? 0;

                        if($staff['wage'] == 2) {
                            $staffSalons[] = [
                                "salon_id" => $salon["id"],
                                "salon_name" => $salon["name"],
                                "currency" => $salon["currency"],
                                "total_hours" => $salon_hours*$staff["rate"]
                            ];
                        } else {
                            $total_hours = $staff['total_hours'];
                            $staff_rates = $staff['rate'];
                            if($total_hours != 0) {
                                $salon_wise_hours = ($salon_hours*$staff_rates)/$total_hours; 
                            } else {
                                $salon_wise_hours = 0;
                            }
                            $staffSalons[] = [
                                "salon_id" => $salon["id"],
                                "salon_name" => $salon["name"],
                                "currency" => $salon["currency"],
                                "total_hours" => $salon_wise_hours
                            ];
                        }
                    }
                    $data["staffs"][$key]["salons"] = $staffSalons;
                }
            }
            if($data["salons"]) {
                foreach($data["salons"] as $key => $val) {
                    $salon_total_hours = 0;
                    if($data["staffs"]) {
                        foreach($data["staffs"] as $staff) {
                            if($staff["salons"]) {
                                foreach($staff["salons"] as $staff_salon) {
                                    if($staff_salon["salon_id"] == $val["id"]) {
                                        $salon_total_hours = $salon_total_hours + $staff_salon["total_hours"];
                                    }
                                }
                            }
                        }
                    } 
                    $data["salons"][$key]["expense"] = $salon_total_hours;
                }
            }
        }
        $title = date("M Y",strtotime($datetime));
        $html = view('monthly_summary_report',$data);
        echo json_encode(["title" => $title,"html" => $html]);
        exit;
    }

    public function load_expense_chart()
    {
        $model = new Salon;
        $salon = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();
        $labels = $data = [];
        if($salon) {
            $model = db_connect();
            foreach($salon as $row) {
                $labels[] = $row['name'];
                $obj = $model->table("salon_cover_entries ce");
                $obj = $obj->join("salon_covers c","c.id=ce.cover_id","left");
                $obj = $obj->selectSum("ce.amount");
                $obj = $obj->where("c.salon_id",$row["id"])->where("YEAR(date)",$this->request->getVar("year"));
                $entry = $obj->get()->getRowArray();
                // $entry = $model->selectSum("amount")->where("salon_id",$row["id"])->where("YEAR(date)",$this->request->getVar('year'))->get()->getRowArray();
                if($entry) {
                    if(!is_null($entry["amount"]) && $entry["amount"] != "") {
                        $data[] = $entry["amount"];
                    }
                }
            }
        }
        echo json_encode(array("labels" => $labels,"data" => $data));
        exit;

    }

    public function load_salon_chart()
    {
        $post = $this->request->getVar();
        $db = db_connect();

        $mode_labels = [];
        $mode_values = [];
        for($i = 1; $i <= 12; $i ++) {
            if($i < 10) {
                $month = "0".$i;
            } else {
                $month = $i;
            }
            $mode_labels[] = \DateTime::createFromFormat('!m', $i)->format('M');

            $query = $db->table("salon_covers sc");
            $query = $query->selectSum("sc.amount");
            $query = $query->like("date",$post["year"]."-",$month,"before");
            $query = $query->where("salon_id",$post["salon_id"]);
            $count = $query->get()->getRowArray();
            array_push($mode_values, $count["amount"]);
        }
        echo json_encode(array("mode_labels" => $mode_labels,"mode_values" => $mode_values));
        exit;
    }

    public function profile()
    {
        $session = session();
        if($session->has("userdata")) {
            $userdata = $session->get('userdata');

            $model = new User;
            $data["profile"] = $model->where("id",$userdata["id"])->first(); 
            $data["page_title"] = "";
            return view('profile',$data);
        }
    }

    public function submit_profile()
    {
        try {
            $session = session();
            $userdata = $session->get('userdata');

            $model = new User;
            // $count = $model->where('email',$this->request->getVar('email'))->where(default_where())->get()->getNumRows();
            $count = 0;
            if($count == 0) {
                $post = $this->request->getVar();
                if(trim($post["password"]) != "") {
                    $post["password"] = md5($post["password"]);
                } else {
                    unset($post["password"]);
                }
                $post['updated_by'] = $userdata["id"];
                $post['updated_at'] = date("Y-m-d H:i:s");
                if($model->update($post["profile_id"],$post)) {
                    $session->setFlashData('success_message',"profile updated successfully.");
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

    public function staff_attendance($id)
    {
        $session = session();
        if($session->has("userdata")) {
            $model = new User;
            $data["profile"] = $model->where("id",$id)->first(); 
            if($data["profile"]) {
                $data["load_ajax_url"] = base_url("load-staff-attendance/".$id);

                $model = new Salon;
                $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                return view('attendance/staff_attendance',$data);
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function load_staff_attendance($id)
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
        $query = $query->select("sc.id,sc.date,sc.staff_id,sc.in_time,sc.out_time,sc.hours_diff,sc.note,sc.tip,sc.rate,sc.created_at,s.name AS salon,CONCAT(su.fname,' ',su.lname) AS staff,sc.break");
        $query = $query->where("sc.deleted_at IS NULL");
        $query = $query->where("sc.staff_id",$id);
        $query = $query->where("sc.in_time !=","00:00:00");
        if(isset($post["salon_id"]) && $post["salon_id"] != "") {
            $query = $query->where("s.id",$post["salon_id"]);
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
            $hours = is_null($val['hours_diff']) ? "-" : $val['hours_diff']." hours"; 
            $result['data'][$key] = [
                "<small>".($key + 1)."</small>",
                "<small>".date("d M, Y",strtotime($val['date']))."</small>",
                "<small>".date("l",strtotime($val['date']))."</small>",
                "<small>".date("h:i A",strtotime($val['in_time']))."</small>",
                 "<small>".date("h:i A",strtotime($val['out_time']))."</small>",
                 "<small>".$val['break']." Min.</small>",
                "<small>".$hours."</small>",
                "<small>".$val['tip']."</small>",
                "<small>".$val['salon']."</small>",
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

    public function staff_rate_history($id)
    {
        $session = session();
        if($session->has("userdata")) {
            $model = new User;
            $data["profile"] = $model->where("id",$id)->first(); 
            if($data["profile"]) {
                $data["load_ajax_url"] = base_url("load-staff-rate-history/".$id);

                $model = new Salon;
                $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                return view('staff/rate_history',$data);
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function load_staff_rate_history($id)
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
        $query = $model->table("salon_staff_rates ssr");
        $query = $query->join("salons s","s.id=ssr.salon_id");
        $query = $query->select("ssr.id,s.name AS salon,s.currency,ssr.start_date,ssr.wage,ssr.rate,ssr.created_at");
        $query = $query->where("ssr.staff_id",$id);
        $query = $query->where("ssr.deleted_at IS NULL");
        if(isset($post["salon_id"]) && $post["salon_id"] != "") {
            $query = $query->where("s.id",$post["salon_id"]);
        }
        if(isset($post["datetime"]) && $post["datetime"] != "") {
            $query = $query->like("DATE(ssr.created_at)",$post["datetime"]);
        }
        if(isset($post["date"]) && $post["date"] != "") {
            $query = $query->where("DATE(ssr.created_at)",$post["date"]);
        }
        $totalRecords = $query->countAllResults(false);
        $query = $query->orderBy($orderBy, $orderDir)->limit($length, $start);
        $checkins = $query->get()->getResultArray();
        foreach ($checkins as $key => $val) {
            $wage = $val['wage'] == 1 ? 'Monthly' : 'Hourly';
            $action = '<a href="javascript:;" onclick=remove_row("'.base_url('remove-increment/'.$val['id']).'")><i class="icon-base bx bx-trash icon-sm"></i></a>';
            $result['data'][$key] = [
                "<small>".($key + 1)."</small>",
                "<small>".$val['salon']."</small>",
                "<small>".$wage."</small>",
                "<small>".$val['currency'].' '.$val['rate']."</small>",
                "<small>".format_date($val['start_date'])."</small>",
                "<small>".format_date($val['created_at'])."</small>",
                $action
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
    
    public function submit_staff_increment()
    {
        $session = session();
        $post = $this->request->getVar();
        
        $model = db_connect();
        $model->table("salon_staff_rates")->insert([
            "rate" => $post["new_rate"],
            "wage" => $post["new_wage"],
            "staff_id" => $post["staff_id"],
            "salon_id" => $post["salon_id"],
            "start_date" => $post["new_date"],
            "created_at" => date("Y-m-d H:i:s"),
            "created_by" => $session->get("userdata")["id"]
        ]);
        $session->setFlashData('success_message',"Increment added successfully.");
        return $this->response->setJSON(['status' => 'success']);
    }
    
    public function remove_increment($id)
    {
        $session = session();
        
        $model = db_connect();
        $model->table("salon_staff_rates")->where("id",$id)->update(["deleted_at" => date("Y-m-d H:i:s")]);
        
        $session->setFlashData('success_message',"Increment removed successfully.");
        return $this->response->setJSON(['status' => 'success']);
    }

    public function load_salon_report()
    {
        $post = $this->request->getVar();
        $datetime = $post["year_month"]; 
        $fromdate = date('Y-m-01',strtotime($datetime));
        $__todate = date('Y-m-t',strtotime($datetime));

        $model = new Salon;
        $data["salons"] = $model->select("id,name,currency")->where("is_active",1)->where("deleted_at IS NULL")->orderBy("id","asc")->get()->getResultArray();

        $model = new User;
        $data["staffs"] = $model->select("id,salon_id,fname,lname")->where("is_active",1)->where('role',3)->where("deleted_at IS NULL")->where("last_working_date IS NULL")->orderBy('fname','asc')->get()->getResultArray();

        if($data["staffs"]) {
            $model = db_connect();

            // find staff's rate & wage
            foreach($data["staffs"] as $key => $staff) {
                $rate_query = $model->table("salon_staff_rates")->select("rate,wage")->where("staff_id",$staff["id"])->where("start_date <=",$fromdate)->where("deleted_at IS NULL")->orderBy("start_date","DESC")->get()->getRowArray();
                if($rate_query) {
                    $data["staffs"][$key]["rate"] = $rate_query['rate'];
                    $data["staffs"][$key]["wage"] = $rate_query['wage'];
                } else {
                    $data["staffs"][$key]["rate"] = 0;
                    $data["staffs"][$key]["wage"] = 0;
                }
            }

            // calculate staff's total hours
            foreach($data["staffs"] as $key => $staff) {
                $hoursQuery = $model->table("salon_checkins sc")
                ->selectSum('sc.hours_diff', 'total_hours')
                ->where("sc.staff_id", $staff["id"])
                ->where("sc.date >=", $fromdate)
                ->where("sc.date <=", $__todate)
                ->where('sc.deleted_at', null)
                ->get()
                ->getRowArray();
                $data["staffs"][$key]["total_hours"] = $hoursQuery['total_hours'] ?? 0;
            }
            foreach($data["staffs"] as $key => $staff) {
                $staffSalons = [];
                foreach($data["salons"] as $salon) {
                    $hoursQuery = $model->table("salon_checkins sc")
                        ->select("COALESCE(SUM(sc.hours_diff),0) as salon_hours")
                        ->where("sc.staff_id", $staff["id"])
                        ->where("sc.salon_id", $salon["id"])
                        ->where("sc.date >=", $fromdate)
                        ->where("sc.date <=", $__todate)
                        ->where("sc.deleted_at IS NULL")
                        ->get()
                        ->getRowArray();
                    $salon_hours = $hoursQuery['salon_hours'] ?? 0;

                    if($staff['wage'] == 2) {
                        $staffSalons[] = [
                            "salon_id" => $salon["id"],
                            "salon_name" => $salon["name"],
                            "currency" => $salon["currency"],
                            "total_hours" => $salon_hours*$staff["rate"]
                        ];
                    } else {
                        $total_hours = $staff['total_hours'];
                        $staff_rates = $staff['rate'];
                        if($total_hours != 0) {
                            $salon_wise_hours = ($salon_hours*$staff_rates)/$total_hours; 
                        } else {
                            $salon_wise_hours = 0;
                        }
                        $staffSalons[] = [
                            "salon_id" => $salon["id"],
                            "salon_name" => $salon["name"],
                            "currency" => $salon["currency"],
                            "total_hours" => $salon_wise_hours
                        ];
                    }
                }
                $data["staffs"][$key]["salons"] = $staffSalons;
            }
        }
        if($data["salons"]) {
            foreach($data["salons"] as $key => $val) {
                $salon_total_hours = 0;
                if($data["staffs"]) {
                    foreach($data["staffs"] as $staff) {
                        if($staff["salons"]) {
                            foreach($staff["salons"] as $staff_salon) {
                                if($staff_salon["salon_id"] == $val["id"]) {
                                    $salon_total_hours = $salon_total_hours + $staff_salon["total_hours"];
                                }
                            }
                        }
                    }
                } 
                $data["salons"][$key]["total_hours"] = $salon_total_hours;
            }
        }
        $html = view('dashboard_salon_report_backup',$data);
        echo json_encode(["html" => $html]);
        exit;
    }
}
