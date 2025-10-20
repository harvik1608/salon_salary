<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\Salon;
use App\Models\User;
use App\Models\Payment_mode;
use App\Models\Entry;
use App\Models\Cover;
use App\Models\Cover_entry;
use App\Models\Staff_note;

require_once(APPPATH . 'Views/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reports extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('report')) {
                $data["load_ajax_url"] = base_url("load-reports");

                $model = new Salon;
                $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                $model = new User;
                $data["staffs"] = $model->select("id,fname,lname")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();

                $model = new Payment_mode;
                $data["modes"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

                return view('report/list',$data);
            } else {
                $session->setFlashData("error","You don't have permission to access report pages.");
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
            $query = $query->where("sc.tip",$post["tip"]);
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
                $buttons .= '<a href="'.base_url('reports/'.$val['id']).'">View |</a>&nbsp;';
                $buttons .= '<a href="'.base_url('download-daily-report/'.$val['id']).'">Download</a>&nbsp;';
            }
            $result['data'][$key] = [
                "<small>".($key + 1)."</small>",
                "<small>".$val['salon']."</small>",
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

    public function show($id)
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('report')) {
                $model = db_connect();
                $query = $model->table("salon_covers sc");
                $query = $query->join("salons s","s.id=sc.salon_id");
                $query = $query->select("sc.*,s.name AS salon,s.currency");
                $query = $query->where("sc.id",$id);
                $data["cover"] = $query->get()->getRowArray();
                if($data["cover"]) {
                    $query = $model->table("salon_cover_entries sce");
                    $query = $query->join("salon_payment_modes spm","spm.id=sce.payment_mode_id");
                    $query = $query->select("sce.id,sce.amount,spm.name AS mode");
                    $query = $query->where("sce.cover_id",$id);
                    $data["entries"] = $query->get()->getResultArray();

                    $query = $model->table("salon_checkins sc");
                    $query = $query->join("salon_users su","su.id=sc.staff_id");
                    $query = $query->select("sc.id,sc.in_time,sc.out_time,sc.break,sc.hours_diff,sc.tip,CONCAT(su.fname,' ',su.lname) AS staff");
                    $query = $query->where("sc.date",$data["cover"]["date"]);
                    $query = $query->where("su.deleted_at IS NULL");
                    $query = $query->where("sc.salon_id",$data["cover"]["salon_id"]);
                    $data["checkins"] = $query->get()->getResultArray();

                    return view('report/view',$data);
                } else {
                    $session->setFlashData("error","Report not found.");
                    return redirect("reports");
                }
            } else {
                $session->setFlashData("error","You don't have permission to access report pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function download_daily_report($id)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $model = db_connect();
        $query = $model->table("salon_covers sc");
        $query = $query->join("salons s","s.id=sc.salon_id");
        $query = $query->select("sc.*,s.name AS salon,s.currency");
        $query = $query->where("sc.id",$id);
        $cover = $query->get()->getRowArray();

        $query = $model->table("salon_cover_entries sce");
        $query = $query->join("salon_payment_modes spm","spm.id=sce.payment_mode_id");
        $query = $query->select("sce.id,sce.amount,spm.name AS mode");
        $query = $query->where("sce.cover_id",$id);
        $entries = $query->get()->getResultArray();

        $query = $model->table("salon_checkins sc");
        $query = $query->join("salon_users su","su.id=sc.staff_id");
        $query = $query->select("sc.id,sc.in_time,sc.out_time,sc.break,sc.hours_diff,sc.tip,CONCAT(su.fname,' ',su.lname) AS staff");
        $query = $query->where("sc.date",$cover["date"]);
        $query = $query->where("sc.salon_id",$cover["salon_id"]);
        $checkins = $query->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Payment Mode');
        $sheet->setCellValue('C1', 'Amount');

        $total = 0;
        if($entries) {
            $no = 2;
            foreach($entries as $key => $val) {
                $sheet->setCellValue('A'.$no, $key+1);
                $sheet->setCellValue('B'.$no, $val['mode']);
                $sheet->setCellValue('C'.$no, $val['amount']);
                $total = $total + $val['amount'];
                $no++;
            }
        }
        $sheet->setCellValue('A'.$no, 'TOTAL');
        $sheet->setCellValue('B'.$no, "TOTAL");
        $sheet->setCellValue('C'.$no, $total);

        $writer = new Xlsx($spreadsheet);
        $filename = 'daily_reprt.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function yearly_reports()
    {
        $session = session();
        if($session->has("userdata")) {
            if(check_permission('yearly_report')) {
                $model = new User;
                $data["staffs"] = $model->select("id,fname,lname")->where("(last_working_date is null OR last_working_date >= '".date('Y-m-d')."')")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();
                return view('report/yearly_report',$data);
            } else {
                $session->setFlashData("error","You don't have permission to access report pages.");
                return redirect("dashboard");
            }
        } else {
            return redirect("sign-in");
        }
    }

    public function load_yearly_report()
    {
        $data["year"] = $this->request->getVar("year"); 
        $data["from_month"] = $this->request->getVar("from_month"); 
        $data["to_month"] = $this->request->getVar("to_month"); 

        $model = new Salon;
        $data["salons"] = $model->select("id,name")->where("is_active",1)->where(default_where())->get()->getResultArray();

        $model = new User;
        if($this->request->getVar('staff') == 'all') {
            $data["staffs"] = $model->select("id,fname,lname")->where("(last_working_date is null OR last_working_date >= '".date('Y-m-d')."')")->where(array("is_active" => 1,"role" => 3))->where(default_where())->get()->getResultArray();
        } else {
            $data["staffs"] = $model->select("id,fname,lname")->where(array("id" => $this->request->getVar('staff')))->where(default_where())->get()->getResultArray();
        }
        if($data["staffs"]) {
            $model = new Staff_note;
            foreach ($data["staffs"] as $key => $val) {
                $note = $model->select("note")->where(["year" => $data["year"],"staff_id" => $val["id"]])->first();
                $staff_note = "";
                if($note) {
                    $staff_note = $note["note"];
                }
                $data["staffs"][$key]["yearly_note"] = $staff_note;
            }
        }
        return view('report/load_yearly_report1',$data);
    }
    
    public function save_staff_yearly_note()
    {
        try {
            $model = new Staff_note;
            $_note = $model->select("id")->where(["year" => $this->request->getVar("year"),"staff_id" => $this->request->getVar("staff_id")])->first();
            if($_note) {
                $model->update($_note["id"],["note" => $this->request->getVar("note"),"updated_at" => date("Y-m-d H:i:s")]);
                $message = "Note updated successfully.";
            } else {
                $session = session();
                $userdata = $session->get('userdata');
                
                $insert_data = array(
                    "year" => $this->request->getVar("year"),
                    "note" => $this->request->getVar("note"),
                    "staff_id" => $this->request->getVar("staff_id"),
                    "created_by" => $userdata['id'],
                    "updated_by" => $userdata['id'],
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => date("Y-m-d H:i:s")
                );
                $model->insert($insert_data);
                $message = "Note added successfully.";
            }
            return $this->response->setJSON(['status' => 'success','message' => $message]);
        } catch(Throwable $e) {
            return $this->response->setJSON(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}
